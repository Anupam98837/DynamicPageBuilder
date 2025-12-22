<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserConferencePublicationsController extends Controller
{
    private string $table = 'user_conference_publications';

    /* =========================
     * Helpers (token-driven)
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

    private function canAccess(Request $request, int $userId): bool
    {
        $actor = $this->actor($request);
        if (!$actor['id']) return false;

        if ($actor['id'] === $userId) return true;

        return in_array($actor['role'], [
            'admin','director','principal','hod','technical_assistant','it_person'
        ], true);
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
        if ($raw === null || $raw === '') return null;

        if (is_array($raw)) return $raw;

        if (is_string($raw)) {
            $s = trim($raw);
            if ($s === '') return null;
            $decoded = json_decode($s, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                return '__INVALID__';
            }
            return $decoded;
        }

        return '__INVALID__';
    }

    private function hasCol(string $col): bool
    {
        try { return Schema::hasColumn($this->table, $col); }
        catch (\Throwable $e) { return false; }
    }

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

        if ($oldPath && is_string($oldPath) && str_starts_with($oldPath, '/assets/media/')) {
            $oldAbs = public_path(ltrim($oldPath, '/'));
            if (is_file($oldAbs)) @unlink($oldAbs);
        }

        return "/assets/media/image/user/{$userUuid}/{$this->table}/{$recordUuid}/{$filename}";
    }

    private function findRecentDuplicate(int $userId, array $data)
    {
        $q = DB::table($this->table)
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->where('conference_name', $data['conference_name'])
            ->where('title', $data['title']);

        if (array_key_exists('publication_year', $data)) {
            if ($data['publication_year'] === null) $q->whereNull('publication_year');
            else $q->where('publication_year', $data['publication_year']);
        }

        if (array_key_exists('publication_organization', $data)) {
            if ($data['publication_organization'] === null) $q->whereNull('publication_organization');
            else $q->where('publication_organization', $data['publication_organization']);
        }

        $q->where('created_at', '>=', Carbon::now()->subSeconds(20));

        return $q->orderBy('id', 'desc')->first();
    }

    /* =========================
     * CRUD (Active)
     * ========================= */

    public function index(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $rows = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->orderByRaw('publication_year IS NULL, publication_year DESC')
            ->orderBy('id','desc')
            ->get();

        $rows = $this->decodeMetadataCollection($rows);

        return response()->json(['success'=>true,'data'=>$rows]);
    }

    public function show(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success'=>false,'error'=>'Record not found'], 404);

        $row = $this->decodeMetadataRow($row);

        return response()->json(['success'=>true,'data'=>$row]);
    }

    public function store(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $metaNorm = $this->normalizeMetadata($request->input('metadata'));
        if ($metaNorm === '__INVALID__') {
            return response()->json(['success'=>false,'errors'=>['metadata'=>['Metadata must be valid JSON array/object']]], 422);
        }

        $v = Validator::make($request->all(), [
            'conference_name'          => ['required','string','max:255'],
            'publication_organization' => ['nullable','string','max:255'],
            'title'                    => ['required','string','max:255'],
            'publication_year'         => ['nullable','integer','min:1900','max:'.(int)date('Y')],
            'publication_type'         => ['nullable','string','max:100'],
            'domain'                   => ['nullable','string','max:255'],
            'location'                 => ['nullable','string','max:255'],
            'description'              => ['nullable','string'],
            'url'                      => ['nullable','string','max:500'],
            'image'                    => ['nullable'],
            'image_file'               => ['nullable'],
        ]);

        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()], 422);

        $data = $v->validated();
        $data['metadata'] = $metaNorm;

        $dup = $this->findRecentDuplicate((int)$user->id, [
            'conference_name' => $data['conference_name'],
            'title' => $data['title'],
            'publication_year' => $data['publication_year'] ?? null,
            'publication_organization' => $data['publication_organization'] ?? null,
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

        return DB::transaction(function () use ($request, $user, $actor, $now, $data) {
            $recordUuid = (string) Str::uuid();

            $imagePath = null;
            if (!$request->hasFile('image') && !$request->hasFile('image_file')) {
                $img = $request->input('image');
                $imagePath = is_string($img) && trim($img) !== '' ? trim($img) : null;
            }

            if ($request->hasFile('image') || $request->hasFile('image_file')) {
                $imagePath = $this->storeImageFile($request, (string)$user->uuid, $recordUuid, null);
            }

            $insert = [
                'uuid'     => $recordUuid,
                'user_id'  => (int) $user->id,

                'conference_name'          => $data['conference_name'],
                'publication_organization' => $data['publication_organization'] ?? null,
                'title'                    => $data['title'],

                'publication_year' => $data['publication_year'] ?? null,
                'publication_type' => $data['publication_type'] ?? null,
                'domain'           => $data['domain'] ?? null,
                'location'         => $data['location'] ?? null,

                'description' => $data['description'] ?? null,
                'url'         => $data['url'] ?? null,
                'image'       => $imagePath,

                'metadata' => array_key_exists('metadata', $data) && $data['metadata'] !== null
                    ? json_encode($data['metadata'])
                    : null,

                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($this->hasCol('created_by'))    $insert['created_by'] = $actor['id'] ?: null;
            if ($this->hasCol('created_at_ip')) $insert['created_at_ip'] = $request->ip();
            if ($this->hasCol('updated_by'))    $insert['updated_by'] = $actor['id'] ?: null;
            if ($this->hasCol('updated_at_ip')) $insert['updated_at_ip'] = $request->ip();

            $id = DB::table($this->table)->insertGetId($insert);

            $row = DB::table($this->table)->where('id', $id)->first();
            $row = $this->decodeMetadataRow($row);

            return response()->json(['success'=>true,'data'=>$row], 201);
        });
    }

    public function update(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success'=>false,'error'=>'Record not found'], 404);

        $metaNorm = $this->normalizeMetadata($request->input('metadata'));
        if ($metaNorm === '__INVALID__') {
            return response()->json(['success'=>false,'errors'=>['metadata'=>['Metadata must be valid JSON array/object']]], 422);
        }

        $v = Validator::make($request->all(), [
            'conference_name'          => ['sometimes','required','string','max:255'],
            'publication_organization' => ['sometimes','nullable','string','max:255'],
            'title'                    => ['sometimes','required','string','max:255'],
            'publication_year'         => ['sometimes','nullable','integer','min:1900','max:'.(int)date('Y')],
            'publication_type'         => ['sometimes','nullable','string','max:100'],
            'domain'                   => ['sometimes','nullable','string','max:255'],
            'location'                 => ['sometimes','nullable','string','max:255'],
            'description'              => ['sometimes','nullable','string'],
            'url'                      => ['sometimes','nullable','string','max:500'],
            'image'                    => ['sometimes','nullable'],
            'image_file'               => ['sometimes','nullable'],
        ]);

        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()], 422);

        $data  = $v->validated();
        $actor = $this->actor($request);

        return DB::transaction(function () use ($request, $user, $row, $data, $actor, $metaNorm) {
            $upd = [];

            foreach ([
                'conference_name','publication_organization','title',
                'publication_type','domain','location','description','url'
            ] as $f) {
                if (array_key_exists($f, $data)) $upd[$f] = $data[$f];
            }

            if (array_key_exists('publication_year', $data)) {
                $upd['publication_year'] = $data['publication_year'];
            }

            if ($request->hasFile('image') || $request->hasFile('image_file')) {
                $upd['image'] = $this->storeImageFile($request, (string)$user->uuid, (string)$row->uuid, $row->image ?? null);
            } elseif (array_key_exists('image', $data)) {
                $img = $request->input('image');
                $upd['image'] = (is_string($img) && trim($img) !== '') ? trim($img) : null;
            }

            if ($request->has('metadata')) {
                $upd['metadata'] = $metaNorm !== null ? json_encode($metaNorm) : null;
            }

            $upd['updated_at'] = Carbon::now();

            if ($this->hasCol('updated_by'))    $upd['updated_by'] = $actor['id'] ?: null;
            if ($this->hasCol('updated_at_ip')) $upd['updated_at_ip'] = $request->ip();

            DB::table($this->table)->where('id', $row->id)->update($upd);

            $fresh = DB::table($this->table)->where('id', $row->id)->first();
            $fresh = $this->decodeMetadataRow($fresh);

            return response()->json(['success'=>true,'data'=>$fresh]);
        });
    }

    /**
     * SOFT DELETE (existing)
     */
    public function destroy(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success'=>false,'error'=>'Record not found'], 404);

        $actor = $this->actor($request);
        $now   = Carbon::now();

        $upd = [
            'deleted_at' => $now,
            'updated_at' => $now,
        ];

        if ($this->hasCol('updated_by'))    $upd['updated_by'] = $actor['id'] ?: null;
        if ($this->hasCol('updated_at_ip')) $upd['updated_at_ip'] = $request->ip();

        DB::table($this->table)->where('id', $row->id)->update($upd);

        return response()->json(['success'=>true,'message'=>'Conference publication deleted']);
    }

    /* ==========================================================
     * NEW APIs (Trash / Restore / Hard Delete)
     * ========================================================== */

    /**
     * LIST DELETED (Trash)
     * GET /api/users/{user_uuid}/conference-publications/deleted
     */
    public function indexDeleted(Request $request, ?string $user_uuid = null)
{
    // ✅ LOG (request + actor) - add this line
    \Log::info('UserConferencePublications:indexDeleted', [
        'ip'         => $request->ip(),
        'user_agent' => (string) $request->userAgent(),
        'user_uuid'  => $user_uuid,
        'actor'      => $this->actor($request),
    ]);

    if ($resp = $this->requireRole($request, [
        'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
    ])) return $resp;

    $user = $this->resolveTargetUser($request, $user_uuid);
    if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

    if (!$this->canAccess($request, (int)$user->id)) {
        return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
    }

    $rows = DB::table($this->table)
        ->where('user_id', $user->id)
        ->whereNotNull('deleted_at')
        ->orderBy('deleted_at','desc')
        ->orderBy('id','desc')
        ->get();

    $rows = $this->decodeMetadataCollection($rows);

    // ✅ LOG (result count) - add this line
    \Log::info('UserConferencePublications:indexDeleted:done', [
        'target_user_id' => (int) $user->id,
        'count'          => is_countable($rows) ? count($rows) : 0,
    ]);

    return response()->json(['success'=>true,'data'=>$rows]);
}


    /**
     * RESTORE (undo soft delete)
     * POST /api/users/{user_uuid}/conference-publications/{uuid}/restore
     */
    public function restore(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success'=>false,'error'=>'Record not found in trash'], 404);

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

        return response()->json(['success'=>true,'message'=>'Conference publication restored','data'=>$fresh]);
    }

    /**
     * HARD DELETE (permanent)
     * DELETE /api/users/{user_uuid}/conference-publications/{uuid}/force
     *
     * NOTE:
     * - recommends only privileged roles, because it is irreversible
     */
    public function forceDelete(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        // stricter role list for permanent deletion
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','technical_assistant','it_person'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        // allow force delete whether active or deleted (both)
        $row = DB::table($this->table)
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->first();

        if (!$row) return response()->json(['success'=>false,'error'=>'Record not found'], 404);

        // Remove stored image if inside public/assets/media...
        if (!empty($row->image) && is_string($row->image) && str_starts_with($row->image, '/assets/media/')) {
            $abs = public_path(ltrim($row->image, '/'));
            if (is_file($abs)) @unlink($abs);
        }

        DB::table($this->table)->where('id', $row->id)->delete();

        return response()->json(['success'=>true,'message'=>'Conference publication permanently deleted']);
    }

    /**
     * (Optional) HARD DELETE ALL DELETED for this user
     * DELETE /api/users/{user_uuid}/conference-publications/deleted/force
     */
    public function forceDeleteAllDeleted(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','technical_assistant','it_person'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $rows = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->get(['id','image']);

        $deletedCount = 0;

        DB::transaction(function() use ($rows, &$deletedCount){
            foreach ($rows as $r) {
                if (!empty($r->image) && is_string($r->image) && str_starts_with($r->image, '/assets/media/')) {
                    $abs = public_path(ltrim($r->image, '/'));
                    if (is_file($abs)) @unlink($abs);
                }
                $deletedCount++;
                DB::table($this->table)->where('id', $r->id)->delete();
            }
        });

        return response()->json(['success'=>true,'message'=>"Trash cleared", 'deleted' => $deletedCount]);
    }
}
