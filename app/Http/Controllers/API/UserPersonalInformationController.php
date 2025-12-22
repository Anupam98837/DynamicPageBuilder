<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserPersonalInformationController extends Controller
{
    private string $table = 'user_personal_information';

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

    /**
     * Who can edit someone else's personal info?
     * Keep consistent with your UserController "highRoles".
     */
    private function isHighRole(?string $role): bool
    {
        return in_array($role, ['admin','director', 'principal', 'hod', 'technical_assistant', 'it_person'], true);
    }

    private function canAccessUser(Request $request, int $targetUserId): bool
    {
        $actor = $this->actor($request);

        if (!$actor['id']) return false;

        if ($actor['id'] === $targetUserId) return true; // self

        return $this->isHighRole($actor['role']); // high roles
    }

    private function decodeQualificationRow($row)
    {
        if ($row && isset($row->qualification) && is_string($row->qualification)) {
            $decoded = json_decode($row->qualification, true);
            $row->qualification = is_array($decoded) ? $decoded : [];
        }
        return $row;
    }

    /* =====================================================
     * CRUD ENDPOINTS for user_personal_information
     * Supports BOTH:
     * - /api/users/{user_uuid}/personal-info
     * - /api/me/personal-info
     * ===================================================== */

    /**
     * GET /api/users/{user_uuid}/personal-info
     * GET /api/me/personal-info
     * Show personal info for a user (or empty default if none).
     */
    public function show(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) {
            return $resp;
        }

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        }

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        $row = $this->decodeQualificationRow($row);

        return response()->json([
            'success' => true,
            'data'    => $row ?: [
                'user_id'          => (int) $user->id,
                'qualification'    => [],
                'affiliation'      => null,
                'specification'    => null,
                'experience'       => null,
                'interest'         => null,
                'administration'   => null,
                'research_project' => null,
            ],
        ]);
    }

    /**
     * POST /api/users/{user_uuid}/personal-info
     * POST /api/me/personal-info
     * Create personal info row (1:1) for a user.
     * If already exists, returns 409.
     */
    public function store(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) {
            return $resp;
        }

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        }

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $exists = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'error'   => 'Personal information already exists. Use update.',
            ], 409);
        }

        $v = Validator::make($request->all(), [
            'qualification'    => ['nullable', 'array'],
            'affiliation'      => ['nullable', 'string'],
            'specification'    => ['nullable', 'string'],
            'experience'       => ['nullable', 'string'],
            'interest'         => ['nullable', 'string'],
            'administration'   => ['nullable', 'string'],
            'research_project' => ['nullable', 'string'],
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $data  = $v->validated();
        $now   = Carbon::now();
        $actor = $this->actor($request);

        $uuid = (string) Str::uuid();

        DB::beginTransaction();
        try {
            $id = DB::table($this->table)->insertGetId([
                'uuid'             => $uuid,
                'user_id'          => (int) $user->id,
                'qualification'    => array_key_exists('qualification', $data) ? json_encode($data['qualification']) : null,
                'affiliation'      => $data['affiliation']      ?? null,
                'specification'    => $data['specification']    ?? null,
                'experience'       => $data['experience']       ?? null,
                'interest'         => $data['interest']         ?? null,
                'administration'   => $data['administration']   ?? null,
                'research_project' => $data['research_project'] ?? null,
                'created_by'       => $actor['id'] ?: null,
                'created_at_ip'    => $request->ip(),
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

            DB::commit();

            $this->logWithActor('user_personal_information.store.success', $request, [
                'id'      => $id,
                'user_id' => (int)$user->id,
            ]);

            $row = DB::table($this->table)->where('id', $id)->first();
            $row = $this->decodeQualificationRow($row);

            return response()->json(['success' => true, 'data' => $row], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            $this->logWithActor('user_personal_information.store.failed', $request, [
                'error'   => $e->getMessage(),
                'user_id' => (int)$user->id,
            ]);

            return response()->json(['success' => false, 'error' => 'Failed to create personal information'], 500);
        }
    }

    /**
     * PUT/PATCH /api/users/{user_uuid}/personal-info
     * PUT/PATCH /api/me/personal-info
     * Upsert-style update (if not exists, it creates).
     */
    public function update(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) {
            return $resp;
        }

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        }

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $v = Validator::make($request->all(), [
            'qualification'    => ['sometimes', 'nullable', 'array'],
            'affiliation'      => ['sometimes', 'nullable', 'string'],
            'specification'    => ['sometimes', 'nullable', 'string'],
            'experience'       => ['sometimes', 'nullable', 'string'],
            'interest'         => ['sometimes', 'nullable', 'string'],
            'administration'   => ['sometimes', 'nullable', 'string'],
            'research_project' => ['sometimes', 'nullable', 'string'],
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $data = $v->validated();
        $now  = Carbon::now();

        $row = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        DB::beginTransaction();
        try {
            if (!$row) {
                $uuid  = (string) Str::uuid();
                $actor = $this->actor($request);

                $id = DB::table($this->table)->insertGetId([
                    'uuid'             => $uuid,
                    'user_id'          => (int) $user->id,
                    'qualification'    => array_key_exists('qualification', $data) ? json_encode($data['qualification']) : null,
                    'affiliation'      => $data['affiliation']      ?? null,
                    'specification'    => $data['specification']    ?? null,
                    'experience'       => $data['experience']       ?? null,
                    'interest'         => $data['interest']         ?? null,
                    'administration'   => $data['administration']   ?? null,
                    'research_project' => $data['research_project'] ?? null,
                    'created_by'       => $actor['id'] ?: null,
                    'created_at_ip'    => $request->ip(),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);

                DB::commit();

                $fresh = DB::table($this->table)->where('id', $id)->first();
                $fresh = $this->decodeQualificationRow($fresh);

                $this->logWithActor('user_personal_information.update.created', $request, [
                    'id'      => $id,
                    'user_id' => (int)$user->id,
                ]);

                return response()->json(['success' => true, 'data' => $fresh], 201);
            }

            $update = [];

            if (array_key_exists('qualification', $data)) {
                $update['qualification'] = $data['qualification'] !== null
                    ? json_encode($data['qualification'])
                    : null;
            }
            if (array_key_exists('affiliation', $data))      $update['affiliation']      = $data['affiliation'];
            if (array_key_exists('specification', $data))    $update['specification']    = $data['specification'];
            if (array_key_exists('experience', $data))       $update['experience']       = $data['experience'];
            if (array_key_exists('interest', $data))         $update['interest']         = $data['interest'];
            if (array_key_exists('administration', $data))   $update['administration']   = $data['administration'];
            if (array_key_exists('research_project', $data)) $update['research_project'] = $data['research_project'];

            if (empty($update)) {
                $row = $this->decodeQualificationRow($row);
                return response()->json(['success' => true, 'data' => $row]);
            }

            $update['updated_at']    = $now;
            $update['updated_by']    = $this->actor($request)['id'] ?: null; // remove if column not exists
            $update['updated_at_ip'] = $request->ip();                       // remove if column not exists

            DB::table($this->table)
                ->where('id', $row->id)
                ->update($update);

            DB::commit();

            $fresh = DB::table($this->table)
                ->where('id', $row->id)
                ->first();

            $fresh = $this->decodeQualificationRow($fresh);

            $this->logWithActor('user_personal_information.update.success', $request, [
                'id'      => $row->id,
                'user_id' => (int)$user->id,
            ]);

            return response()->json(['success' => true, 'data' => $fresh]);

        } catch (\Throwable $e) {
            DB::rollBack();

            $this->logWithActor('user_personal_information.update.failed', $request, [
                'error'   => $e->getMessage(),
                'user_id' => (int)$user->id,
            ]);

            return response()->json(['success' => false, 'error' => 'Failed to update personal information'], 500);
        }
    }

    /**
     * DELETE /api/users/{user_uuid}/personal-info
     * DELETE /api/me/personal-info
     * Soft delete the personal info row.
     */
    public function destroy(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) {
            return $resp;
        }

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        }

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            return response()->json(['success' => false, 'error' => 'Personal information not found'], 404);
        }

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

        $this->logWithActor('user_personal_information.destroy', $request, [
            'id'      => $row->id,
            'user_id' => (int)$user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Personal information deleted',
        ]);
    }

    /**
     * GET /api/users/{user_uuid}/personal-info/restore
     * GET /api/me/personal-info/restore   (still requires high roles)
     * Restore soft-deleted row (high roles only).
     */
    public function restore(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','technical_assistant','it_person'
        ])) {
            return $resp;
        }

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        }

        // only high roles can restore someone else's data
        $actor = $this->actor($request);
        if (!$this->isHighRole($actor['role']) && $actor['id'] !== (int)$user->id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->first();

        if (!$row) {
            return response()->json(['success' => false, 'error' => 'No deleted personal information found'], 404);
        }

        DB::table($this->table)
            ->where('id', $row->id)
            ->update([
                'deleted_at'    => null,
                'updated_at'    => Carbon::now(),
                'updated_by'    => $actor['id'] ?: null, // remove if column not exists
                'updated_at_ip' => $request->ip(),       // remove if column not exists
            ]);

        $this->logWithActor('user_personal_information.restore', $request, [
            'id'      => $row->id,
            'user_id' => (int)$user->id,
        ]);

        $fresh = DB::table($this->table)->where('id', $row->id)->first();
        $fresh = $this->decodeQualificationRow($fresh);

        return response()->json(['success' => true, 'data' => $fresh]);
    }
}
