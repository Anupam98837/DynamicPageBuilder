<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserHonorsController extends Controller
{
    private string $table = 'user_honors';

    /* =========================
     * Auth helpers
     * ========================= */

    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
            'type' => $request->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    private function requireRole(Request $r, array $allowed)
    {
        $a = $this->actor($r);
        if (!$a['role'] || !in_array($a['role'], $allowed, true)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }
        return null;
    }

    private function logWithActor(string $msg, Request $r, array $extra = []): void
    {
        $a = $this->actor($r);
        Log::info($msg, array_merge([
            'actor_role' => $a['role'],
            'actor_id'   => $a['id'],
        ], $extra));
    }

    private function fetchUserByUuid(string $uuid)
    {
        return DB::table('users')
            ->where('uuid', $uuid)
            ->whereNull('deleted_at')
            ->first();
    }

    private function fetchUserById(int $id)
    {
        return DB::table('users')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Resolve target user:
     * - If user_uuid provided (and not "me") => by uuid
     * - Else => by token user (auth_tokenable_id)
     */
    private function resolveTargetUser(Request $request, ?string $user_uuid)
    {
        $user_uuid = $user_uuid !== null ? trim($user_uuid) : null;

        if (!$user_uuid || strtolower($user_uuid) === 'me') {
            $actor = $this->actor($request);
            if (!$actor['id']) return null;
            return $this->fetchUserById((int) $actor['id']);
        }

        return $this->fetchUserByUuid($user_uuid);
    }

    private function isHighRole(?string $role): bool
    {
        return in_array($role, ['admin','director','principal','hod','technical_assistant','it_person'], true);
    }

    private function canAccessUser(Request $request, int $targetUserId): bool
    {
        $actor = $this->actor($request);
        if (!$actor['id']) return false;

        if ($actor['id'] === $targetUserId) return true; // self

        return $this->isHighRole($actor['role']); // high roles
    }

    private function hasCol(string $col): bool
    {
        try { return Schema::hasColumn($this->table, $col); }
        catch (\Throwable $e) { return false; }
    }

    private function decodeMetadataRow($row)
    {
        if ($row && isset($row->metadata) && is_string($row->metadata)) {
            $decoded = json_decode($row->metadata, true);
            $row->metadata = is_array($decoded) ? $decoded : null;
        }
        return $row;
    }

    private function decodeMetadataCollection($rows)
    {
        foreach ($rows as $r) {
            if (isset($r->metadata) && is_string($r->metadata)) {
                $decoded = json_decode($r->metadata, true);
                $r->metadata = is_array($decoded) ? $decoded : null;
            }
        }
        return $rows;
    }

    private function normalizeMetadata($raw)
    {
        // Accept: null, '', array, JSON string
        if ($raw === null || $raw === '') return null;

        if (is_array($raw)) return $raw;

        if (is_string($raw)) {
            $s = trim($raw);
            if ($s === '') return null;

            $decoded = json_decode($s, true);
            if (json_last_error() !== JSON_ERROR_NONE) return '__INVALID__';

            // allow object/array
            return is_array($decoded) ? $decoded : '__INVALID__';
        }

        return '__INVALID__';
    }

    /**
     * ✅ prevent double submit duplicates (same record within N seconds)
     */
    private function findRecentDuplicate(int $userId, array $data)
    {
        $q = DB::table($this->table)
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->where('title', $data['title']);

        // optional match fields
        if (array_key_exists('honor_type', $data)) {
            $data['honor_type'] === null ? $q->whereNull('honor_type') : $q->where('honor_type', $data['honor_type']);
        }
        if (array_key_exists('honouring_organization', $data)) {
            $data['honouring_organization'] === null ? $q->whereNull('honouring_organization') : $q->where('honouring_organization', $data['honouring_organization']);
        }
        if (array_key_exists('honor_year', $data)) {
            $data['honor_year'] === null ? $q->whereNull('honor_year') : $q->where('honor_year', $data['honor_year']);
        }

        // time window
        $q->where('created_at', '>=', Carbon::now()->subSeconds(20));

        return $q->orderBy('id', 'desc')->first();
    }

    /* =========================
     * ✅ Image Upload (same location/pattern as conference publications)
     * public/assets/media/image/user/{userUuid}/{table}/{recordUuid}/image_*.ext
     * ========================= */

    private function storeImageFile(Request $request, string $userUuid, string $recordUuid, ?string $oldPath = null): ?string
    {
        $file = null;
        if ($request->hasFile('image')) $file = $request->file('image');
        elseif ($request->hasFile('image_file')) $file = $request->file('image_file');

        if (!$file) return $oldPath;

        if (!$file->isValid()) {
            throw new \Exception('Invalid image upload');
        }

        $baseDir = public_path("assets/media/image/user/{$userUuid}/{$this->table}/{$recordUuid}");
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0775, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $safeExt = preg_replace('/[^a-z0-9]/i', '', $ext) ?: 'jpg';
        $filename = 'image_' . date('Ymd_His') . '_' . Str::random(8) . '.' . $safeExt;

        $file->move($baseDir, $filename);

        // delete old (only if inside /assets/media/)
        if ($oldPath && is_string($oldPath) && str_starts_with($oldPath, '/assets/media/')) {
            $oldAbs = public_path(ltrim($oldPath, '/'));
            if (is_file($oldAbs)) @unlink($oldAbs);
        }

        return "/assets/media/image/user/{$userUuid}/{$this->table}/{$recordUuid}/{$filename}";
    }

    private function deleteStoredImageIfLocal(?string $path): void
    {
        if (!empty($path) && is_string($path) && str_starts_with($path, '/assets/media/')) {
            $abs = public_path(ltrim($path, '/'));
            if (is_file($abs)) @unlink($abs);
        }
    }

    /* =====================================================
     * CRUD (multiple honors per user)
     * Supports BOTH:
     * - /api/users/{user_uuid}/honors...
     * - /api/me/honors...
     * ===================================================== */

    public function index(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $rows = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->orderByRaw('honor_year IS NULL, honor_year DESC')
            ->orderBy('id', 'desc')
            ->get();

        $rows = $this->decodeMetadataCollection($rows);

        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function show(Request $request, ?string $user_uuid = null, string $honor_uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $honor_uuid)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success' => false, 'error' => 'Honor not found'], 404);

        $row = $this->decodeMetadataRow($row);

        return response()->json(['success' => true, 'data' => $row]);
    }

    public function store(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        // ✅ accept metadata as array OR JSON string
        $metaNorm = $this->normalizeMetadata($request->input('metadata'));
        if ($metaNorm === '__INVALID__') {
            return response()->json(['success'=>false,'errors'=>['metadata'=>['Metadata must be valid JSON array/object']]], 422);
        }

        // ✅ IMPORTANT: do NOT validate 'image' as string because it may be a file upload
        $v = Validator::make($request->all(), [
            'title'                  => ['required', 'string', 'max:255'],
            'honor_type'             => ['nullable', 'string', 'max:100'],
            'honouring_organization' => ['nullable', 'string', 'max:255'],
            'honor_year'             => ['nullable', 'integer', 'min:1900', 'max:' . (int)date('Y')],
            'description'            => ['nullable', 'string'],
            'url'                    => ['nullable', 'string', 'max:500'], // if you have this column; safe even if unused
            'image'                  => ['nullable'], // file or string path
            'image_file'             => ['nullable'], // file alternative
            'metadata'               => ['nullable'], // normalized above
        ]);

        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        $data  = $v->validated();
        $data['metadata'] = $metaNorm;

        // ✅ prevent double entry (double submit)
        $dup = $this->findRecentDuplicate((int)$user->id, [
            'title'                  => $data['title'],
            'honor_type'             => $data['honor_type'] ?? null,
            'honouring_organization' => $data['honouring_organization'] ?? null,
            'honor_year'             => $data['honor_year'] ?? null,
        ]);
        if ($dup) {
            $dup = $this->decodeMetadataRow($dup);
            return response()->json([
                'success' => true,
                'data' => $dup,
                'message' => 'Duplicate submit prevented'
            ], 200);
        }

        $actor = $this->actor($request);
        $now   = Carbon::now();

        DB::beginTransaction();
        try {
            $uuid = (string) Str::uuid();

            // ✅ image: either string path OR uploaded file
            $imagePath = null;

            // if no file provided, accept string (existing behavior)
            if (!$request->hasFile('image') && !$request->hasFile('image_file')) {
                $img = $request->input('image');
                $imagePath = (is_string($img) && trim($img) !== '') ? trim($img) : null;
            }

            // if file provided, store under same location as publications
            if ($request->hasFile('image') || $request->hasFile('image_file')) {
                $imagePath = $this->storeImageFile($request, (string)$user->uuid, $uuid, null);
            }

            $insert = [
                'uuid'                   => $uuid,
                'user_id'                => (int)$user->id,
                'title'                  => $data['title'],
                'honor_type'             => $data['honor_type'] ?? null,
                'honouring_organization' => $data['honouring_organization'] ?? null,
                'honor_year'             => $data['honor_year'] ?? null,
                'description'            => $data['description'] ?? null,
                'image'                  => $imagePath,
                'metadata'               => ($metaNorm !== null) ? json_encode($metaNorm) : null,
                'created_at'             => $now,
                'updated_at'             => $now,
            ];

            if ($this->hasCol('created_by'))    $insert['created_by'] = $actor['id'] ?: null;
            if ($this->hasCol('created_at_ip')) $insert['created_at_ip'] = $request->ip();
            if ($this->hasCol('updated_by'))    $insert['updated_by'] = $actor['id'] ?: null;
            if ($this->hasCol('updated_at_ip')) $insert['updated_at_ip'] = $request->ip();

            $id = DB::table($this->table)->insertGetId($insert);

            DB::commit();

            $this->logWithActor('user_honors.store.success', $request, [
                'id'      => $id,
                'user_id' => (int)$user->id,
            ]);

            $row = DB::table($this->table)->where('id', $id)->first();
            $row = $this->decodeMetadataRow($row);

            return response()->json(['success' => true, 'data' => $row], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            $this->logWithActor('user_honors.store.failed', $request, [
                'error'   => $e->getMessage(),
                'user_id' => (int)$user->id,
            ]);

            return response()->json(['success' => false, 'error' => 'Failed to create honor'], 500);
        }
    }

    public function update(Request $request, ?string $user_uuid = null, string $honor_uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $honor_uuid)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success' => false, 'error' => 'Honor not found'], 404);

        $metaNorm = $this->normalizeMetadata($request->input('metadata'));
        if ($metaNorm === '__INVALID__') {
            return response()->json(['success'=>false,'errors'=>['metadata'=>['Metadata must be valid JSON array/object']]], 422);
        }

        // ✅ IMPORTANT: keep 'image' as nullable (file OR string)
        $v = Validator::make($request->all(), [
            'title'                  => ['sometimes', 'required', 'string', 'max:255'],
            'honor_type'             => ['sometimes', 'nullable', 'string', 'max:100'],
            'honouring_organization' => ['sometimes', 'nullable', 'string', 'max:255'],
            'honor_year'             => ['sometimes', 'nullable', 'integer', 'min:1900', 'max:' . (int)date('Y')],
            'description'            => ['sometimes', 'nullable', 'string'],
            'url'                    => ['sometimes', 'nullable', 'string', 'max:500'],
            'image'                  => ['sometimes', 'nullable'], // file or string
            'image_file'             => ['sometimes', 'nullable'], // file alternative
            'metadata'               => ['sometimes', 'nullable'], // normalized above
        ]);

        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        $data   = $v->validated();
        $actor  = $this->actor($request);
        $now    = Carbon::now();
        $update = [];

        foreach (['title','honor_type','honouring_organization','description'] as $f) {
            if (array_key_exists($f, $data)) $update[$f] = $data[$f];
        }
        if (array_key_exists('honor_year', $data)) $update['honor_year'] = $data['honor_year'];

        // ✅ image update:
        // - if file upload => store + delete old (if local)
        // - else if 'image' key exists => treat as string path (or null to clear)
        if ($request->hasFile('image') || $request->hasFile('image_file')) {
            $update['image'] = $this->storeImageFile(
                $request,
                (string)$user->uuid,
                (string)$row->uuid,
                $row->image ?? null
            );
        } elseif (array_key_exists('image', $data)) {
            $img = $request->input('image');
            $update['image'] = (is_string($img) && trim($img) !== '') ? trim($img) : null;
        }

        if ($request->has('metadata')) {
            $update['metadata'] = ($metaNorm !== null) ? json_encode($metaNorm) : null;
        }

        if (empty($update)) {
            return response()->json(['success' => true, 'data' => $this->decodeMetadataRow($row)]);
        }

        $update['updated_at'] = $now;
        if ($this->hasCol('updated_by'))    $update['updated_by'] = $actor['id'] ?: null;
        if ($this->hasCol('updated_at_ip')) $update['updated_at_ip'] = $request->ip();

        DB::table($this->table)->where('id', $row->id)->update($update);

        $this->logWithActor('user_honors.update.success', $request, [
            'id'      => $row->id,
            'user_id' => (int)$user->id,
        ]);

        $fresh = DB::table($this->table)->where('id', $row->id)->first();
        $fresh = $this->decodeMetadataRow($fresh);

        return response()->json(['success' => true, 'data' => $fresh]);
    }

    public function destroy(Request $request, ?string $user_uuid = null, string $honor_uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $honor_uuid)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success' => false, 'error' => 'Honor not found'], 404);

        $actor = $this->actor($request);
        $now   = Carbon::now();

        $upd = [
            'deleted_at' => $now,
            'updated_at' => $now,
        ];
        if ($this->hasCol('updated_by'))    $upd['updated_by'] = $actor['id'] ?: null;
        if ($this->hasCol('updated_at_ip')) $upd['updated_at_ip'] = $request->ip();

        DB::table($this->table)->where('id', $row->id)->update($upd);

        $this->logWithActor('user_honors.destroy', $request, [
            'id'      => $row->id,
            'user_id' => (int)$user->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Honor deleted']);
    }

    /* ==========================================================
     * Trash / Restore / Hard Delete
     * ========================================================== */

    /**
     * GET /api/users/{user_uuid}/honors/deleted
     * GET /api/me/honors/deleted
     */
    public function indexDeleted(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $rows = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->orderBy('deleted_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $rows = $this->decodeMetadataCollection($rows);

        return response()->json(['success' => true, 'data' => $rows]);
    }

    /**
     * POST /api/users/{user_uuid}/honors/{honor_uuid}/restore
     * POST /api/me/honors/{honor_uuid}/restore
     */
    public function restore(Request $request, ?string $user_uuid = null, string $honor_uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $honor_uuid)
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success' => false, 'error' => 'Honor not found in trash'], 404);

        $actor = $this->actor($request);
        $now   = Carbon::now();

        $upd = [
            'deleted_at' => null,
            'updated_at' => $now,
        ];
        if ($this->hasCol('updated_by'))    $upd['updated_by'] = $actor['id'] ?: null;
        if ($this->hasCol('updated_at_ip')) $upd['updated_at_ip'] = $request->ip();

        DB::table($this->table)->where('id', $row->id)->update($upd);

        $fresh = DB::table($this->table)->where('id', $row->id)->first();
        $fresh = $this->decodeMetadataRow($fresh);

        $this->logWithActor('user_honors.restore.success', $request, [
            'id'      => $row->id,
            'user_id' => (int)$user->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Honor restored', 'data' => $fresh]);
    }

    /**
     * DELETE /api/users/{user_uuid}/honors/{honor_uuid}/force
     * DELETE /api/me/honors/{honor_uuid}/force
     */
    public function forceDelete(Request $request, ?string $user_uuid = null, string $honor_uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','technical_assistant','it_person'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $honor_uuid)
            ->where('user_id', $user->id)
            ->first();

        if (!$row) return response()->json(['success' => false, 'error' => 'Honor not found'], 404);

        // ✅ delete stored image if local
        $this->deleteStoredImageIfLocal($row->image ?? null);

        DB::table($this->table)->where('id', $row->id)->delete();

        $this->logWithActor('user_honors.forceDelete.success', $request, [
            'id'      => $row->id,
            'user_id' => (int)$user->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Honor permanently deleted']);
    }

    /**
     * DELETE /api/users/{user_uuid}/honors/deleted/force
     * DELETE /api/me/honors/deleted/force
     */
    public function forceDeleteAllDeleted(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','technical_assistant','it_person'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $rows = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->get(['id','image']);

        $deletedCount = 0;

        DB::transaction(function () use ($rows, &$deletedCount) {
            foreach ($rows as $r) {
                // ✅ delete stored image if local
                $this->deleteStoredImageIfLocal($r->image ?? null);

                $deletedCount++;
                DB::table($this->table)->where('id', $r->id)->delete();
            }
        });

        $this->logWithActor('user_honors.forceDeleteAllDeleted.success', $request, [
            'user_id' => (int)$user->id,
            'deleted' => $deletedCount,
        ]);

        return response()->json(['success' => true, 'message' => 'Trash cleared', 'deleted' => $deletedCount]);
    }
}
