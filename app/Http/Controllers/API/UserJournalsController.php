<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserJournalsController extends Controller
{
    private string $table = 'user_journals';

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

        if ($actor['id'] === $targetUserId) return true;

        return $this->isHighRole($actor['role']);
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

    /* =========================
     * CRUD
     * Supports BOTH:
     * - /api/users/{user_uuid}/journals...
     * - /api/me/journals...
     * ========================= */

    /**
     * GET /api/users/{user_uuid}/journals
     * GET /api/me/journals
     */
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
            ->orderBy('sort_order', 'asc')
            ->orderByRaw('publication_year IS NULL, publication_year DESC')
            ->orderBy('id', 'desc')
            ->get();

        $rows = $this->decodeMetadataCollection($rows);

        return response()->json(['success' => true, 'data' => $rows]);
    }

    /**
     * GET /api/users/{user_uuid}/journals/{journal_uuid}
     * GET /api/me/journals/{journal_uuid}
     */
    public function show(Request $request, ?string $user_uuid = null, string $journal_uuid = '')
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
            ->where('uuid', $journal_uuid)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success' => false, 'error' => 'Journal not found'], 404);

        $row = $this->decodeMetadataRow($row);

        return response()->json(['success' => true, 'data' => $row]);
    }

    /**
     * POST /api/users/{user_uuid}/journals
     * POST /api/me/journals
     */
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

        $v = Validator::make($request->all(), [
            'title'                   => ['required', 'string', 'max:255'],
            'publication_organization'=> ['nullable', 'string', 'max:255'],
            'publication_year'        => ['nullable', 'integer', 'min:1900', 'max:' . (int)date('Y')],
            'description'             => ['nullable', 'string'],
            'url'                     => ['nullable', 'string', 'max:500'],
            'image'                   => ['nullable', 'string', 'max:255'],
            'sort_order'              => ['nullable', 'integer'],
            'metadata'                => ['nullable', 'array'],
        ]);

        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        $data  = $v->validated();
        $actor = $this->actor($request);
        $now   = Carbon::now();

        DB::beginTransaction();
        try {
            $uuid = (string) Str::uuid();

            $id = DB::table($this->table)->insertGetId([
                'uuid'                    => $uuid,
                'user_id'                 => (int)$user->id,

                'title'                   => $data['title'],
                'publication_organization'=> $data['publication_organization'] ?? null,
                'publication_year'        => $data['publication_year'] ?? null,
                'description'             => $data['description'] ?? null,
                'url'                     => $data['url'] ?? null,
                'image'                   => $data['image'] ?? null,
                'sort_order'              => $data['sort_order'] ?? 0,
                'metadata'                => array_key_exists('metadata', $data) ? json_encode($data['metadata']) : null,

                'created_by'              => $actor['id'] ?: null,
                'created_at_ip'           => $request->ip(),
                'created_at'              => $now,
                'updated_at'              => $now,
            ]);

            DB::commit();

            $this->logWithActor('user_journals.store.success', $request, [
                'id'      => $id,
                'user_id' => (int)$user->id,
            ]);

            $row = DB::table($this->table)->where('id', $id)->first();
            $row = $this->decodeMetadataRow($row);

            return response()->json(['success' => true, 'data' => $row], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            $this->logWithActor('user_journals.store.failed', $request, [
                'error'   => $e->getMessage(),
                'user_id' => (int)$user->id,
            ]);

            return response()->json(['success' => false, 'error' => 'Failed to create journal'], 500);
        }
    }

    /**
     * PUT/PATCH /api/users/{user_uuid}/journals/{journal_uuid}
     * PUT/PATCH /api/me/journals/{journal_uuid}
     */
    public function update(Request $request, ?string $user_uuid = null, string $journal_uuid = '')
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
            ->where('uuid', $journal_uuid)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success' => false, 'error' => 'Journal not found'], 404);

        $v = Validator::make($request->all(), [
            'title'                   => ['sometimes', 'required', 'string', 'max:255'],
            'publication_organization'=> ['sometimes', 'nullable', 'string', 'max:255'],
            'publication_year'        => ['sometimes', 'nullable', 'integer', 'min:1900', 'max:' . (int)date('Y')],
            'description'             => ['sometimes', 'nullable', 'string'],
            'url'                     => ['sometimes', 'nullable', 'string', 'max:500'],
            'image'                   => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort_order'              => ['sometimes', 'nullable', 'integer'],
            'metadata'                => ['sometimes', 'nullable', 'array'],
        ]);

        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        $data   = $v->validated();
        $actor  = $this->actor($request);
        $now    = Carbon::now();
        $update = [];

        foreach (['title','publication_organization','description','url','image'] as $f) {
            if (array_key_exists($f, $data)) $update[$f] = $data[$f];
        }
        if (array_key_exists('publication_year', $data)) $update['publication_year'] = $data['publication_year'];
        if (array_key_exists('sort_order', $data)) $update['sort_order'] = $data['sort_order'] ?? 0;
        if (array_key_exists('metadata', $data)) {
            $update['metadata'] = $data['metadata'] !== null ? json_encode($data['metadata']) : null;
        }

        if (empty($update)) return response()->json(['success' => true, 'data' => $this->decodeMetadataRow($row)]);

        $update['updated_at']    = $now;
        $update['updated_by']    = $actor['id'] ?: null; // remove if column not exists
        $update['updated_at_ip'] = $request->ip();       // remove if column not exists

        DB::table($this->table)->where('id', $row->id)->update($update);

        $this->logWithActor('user_journals.update.success', $request, [
            'id'      => $row->id,
            'user_id' => (int)$user->id,
        ]);

        $fresh = DB::table($this->table)->where('id', $row->id)->first();
        $fresh = $this->decodeMetadataRow($fresh);

        return response()->json(['success' => true, 'data' => $fresh]);
    }

    /**
     * DELETE /api/users/{user_uuid}/journals/{journal_uuid}
     * DELETE /api/me/journals/{journal_uuid}
     */
    public function destroy(Request $request, ?string $user_uuid = null, string $journal_uuid = '')
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
            ->where('uuid', $journal_uuid)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success' => false, 'error' => 'Journal not found'], 404);

        $actor = $this->actor($request);
        $now   = Carbon::now();

        DB::table($this->table)
            ->where('id', $row->id)
            ->update([
                'deleted_at'    => $now,
                'updated_at'    => $now,
                'updated_by'    => $actor['id'] ?: null, // remove if column not exists
                'updated_at_ip' => $request->ip(),       // remove if column not exists
            ]);

        $this->logWithActor('user_journals.destroy', $request, [
            'id'      => $row->id,
            'user_id' => (int)$user->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Journal deleted']);
    }
}
