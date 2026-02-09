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

class UserTeachingEngagementsController extends Controller
{
    private string $table = 'user_teaching_engagements';

    /* =========================
     * Helpers
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

    /**
     * ✅ DB Activity Logger (user_data_activity_log)
     * Logs only from POST/PUT/PATCH/DELETE handlers (we call it explicitly in those methods).
     */
    private function dbActivityLog(
        Request $r,
        string $activity,
        string $module,
        string $tableName,
        ?int $recordId = null,
        ?array $changedFields = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $note = null
    ): void {
        try {
            if (!Schema::hasTable('user_data_activity_log')) return;

            $a = $this->actor($r);

            // Ensure JSON columns get JSON strings (safe for query builder)
            $cf = $changedFields !== null ? json_encode($changedFields, JSON_UNESCAPED_UNICODE) : null;
            $ov = $oldValues !== null ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null;
            $nv = $newValues !== null ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null;

            $ua = (string) ($r->userAgent() ?? '');
            if ($ua !== '') $ua = mb_substr($ua, 0, 512);

            $now = Carbon::now();

            DB::table('user_data_activity_log')->insert([
                'performed_by'       => (int) ($a['id'] ?: 0),
                'performed_by_role'  => $a['role'] ?: null,
                'ip'                 => $r->ip(),
                'user_agent'         => $ua !== '' ? $ua : null,

                'activity'           => mb_substr($activity, 0, 50),
                'module'             => mb_substr($module, 0, 100),

                'table_name'         => mb_substr($tableName, 0, 128),
                'record_id'          => $recordId,

                'changed_fields'     => $cf,
                'old_values'         => $ov,
                'new_values'         => $nv,

                'log_note'           => $note,

                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        } catch (\Throwable $e) {
            // Never break functionality due to logging failure
            Log::error('user_data_activity_log.insert_failed', [
                'error' => $e->getMessage(),
                'path'  => $r->path(),
                'method'=> $r->method(),
            ]);
        }
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

    private function canAccess(Request $request, int $userId): bool
    {
        $actor = $this->actor($request);
        if (!$actor['id']) return false;
        if ($actor['id'] === $userId) return true;
        return $this->isHighRole($actor['role']);
    }

    /* =========================
     * Metadata helpers
     * ========================= */

    private function decodeMetaRow($row)
    {
        if ($row && isset($row->metadata) && is_string($row->metadata)) {
            $decoded = json_decode($row->metadata, true);
            $row->metadata = is_array($decoded) ? $decoded : null;
        }
        return $row;
    }

    private function decodeMetaCollection($rows)
    {
        foreach ($rows as $r) {
            $this->decodeMetaRow($r);
        }
        return $rows;
    }

    /**
     * ✅ Accept metadata coming from:
     * - JSON body: metadata = array
     * - FormData: metadata = stringified JSON
     */
    private function readMetadataFromRequest(Request $request): array
    {
        if (!$request->has('metadata')) return [false, null, null]; // [present?, value, error]

        $meta = $request->input('metadata');

        if (is_array($meta)) return [true, $meta, null];

        if (is_string($meta)) {
            $s = trim($meta);
            if ($s === '') return [true, null, null];

            $decoded = json_decode($s, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [true, null, 'Metadata JSON invalid: ' . json_last_error_msg()];
            }
            if ($decoded !== null && !is_array($decoded)) {
                return [true, null, 'Metadata must decode to an object/array'];
            }
            return [true, $decoded, null];
        }

        return [true, null, 'Metadata must be an array or JSON string'];
    }

    /* =========================
     * Safe column setters
     * ========================= */

    private function setIfColumn(array &$arr, string $col, $val): void
    {
        if (Schema::hasColumn($this->table, $col)) {
            $arr[$col] = $val;
        }
    }

    /* =========================
     * CRUD
     * ========================= */

    /**
     * GET /api/users/{user_uuid}/teaching-engagements
     * GET /api/me/teaching-engagements
     */
    public function index(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'],404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'],403);
        }

        $rows = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->orderBy('id','desc')
            ->get();

        $rows = $this->decodeMetaCollection($rows);

        return response()->json(['success'=>true,'data'=>$rows]);
    }

    /**
     * POST /api/users/{user_uuid}/teaching-engagements
     * POST /api/me/teaching-engagements
     *
     * ✅ Updated:
     * - supports metadata as JSON string (FormData)
     * - safe updated_by / updated_at_ip only if columns exist
     */
    public function store(Request $request, ?string $user_uuid = null)
    {
        $module = 'teaching_engagements';

        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) {
            $this->dbActivityLog($request, 'create', $module, $this->table, null, null, null, [
                'path' => $request->path(), 'method' => $request->method()
            ], 'Unauthorized access attempt');
            return $resp;
        }

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            $this->dbActivityLog($request, 'create', $module, $this->table, null, null, null, [
                'user_uuid' => $user_uuid
            ], 'User not found');
            return response()->json(['success'=>false,'error'=>'User not found'],404);
        }

        if (!$this->canAccess($request, (int)$user->id)) {
            $this->dbActivityLog($request, 'create', $module, $this->table, null, null, null, [
                'target_user_id' => (int)$user->id
            ], 'Unauthorized Access (target user)');
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'],403);
        }

        $v = Validator::make($request->all(), [
            'organization_name' => ['required','string','max:255'],
            'domain'            => ['nullable','string','max:255'],
            'description'       => ['nullable','string'],
            // metadata handled separately to allow JSON string
        ]);

        if ($v->fails()) {
            $this->dbActivityLog(
                $request,
                'create',
                $module,
                $this->table,
                null,
                array_keys($request->all()),
                null,
                ['errors' => $v->errors()->toArray()],
                'Validation failed'
            );
            return response()->json(['success'=>false,'errors'=>$v->errors()],422);
        }

        [$metaPresent, $metaValue, $metaErr] = $this->readMetadataFromRequest($request);
        if ($metaErr) {
            $this->dbActivityLog(
                $request,
                'create',
                $module,
                $this->table,
                null,
                ['metadata'],
                null,
                ['error' => $metaErr],
                'Metadata validation failed'
            );
            return response()->json(['success' => false, 'error' => $metaErr], 422);
        }

        $data  = $v->validated();
        $actor = $this->actor($request);
        $now   = Carbon::now();

        DB::beginTransaction();
        try {
            $insert = [
                'uuid'              => (string) Str::uuid(),
                'user_id'           => (int) $user->id,
                'organization_name' => $data['organization_name'],
                'domain'            => $data['domain'] ?? null,
                'description'       => $data['description'] ?? null,
                'metadata'          => $metaPresent ? ($metaValue !== null ? json_encode($metaValue) : null) : null,
                'created_by'        => $actor['id'] ?: null,
                'created_at_ip'     => $request->ip(),
                'created_at'        => $now,
                'updated_at'        => $now,
            ];

            // safe optional columns
            $this->setIfColumn($insert, 'updated_by', $actor['id'] ?: null);
            $this->setIfColumn($insert, 'updated_at_ip', $request->ip());

            $id = DB::table($this->table)->insertGetId($insert);

            DB::commit();

            $this->logWithActor('user_teaching_engagements.store.success', $request, [
                'id' => $id,
                'user_id' => (int)$user->id,
            ]);

            $newValues = [
                'uuid'              => $insert['uuid'],
                'user_id'           => (int)$user->id,
                'organization_name' => $insert['organization_name'],
                'domain'            => $insert['domain'],
                'description'       => $insert['description'],
                'metadata'          => $metaPresent ? $metaValue : null,
            ];

            $this->dbActivityLog(
                $request,
                'create',
                $module,
                $this->table,
                (int)$id,
                array_keys($newValues),
                null,
                $newValues,
                'Created teaching engagement'
            );

            $row = DB::table($this->table)->where('id',$id)->first();
            $row = $this->decodeMetaRow($row);

            return response()->json(['success'=>true,'data'=>$row],201);

        } catch (\Throwable $e) {
            DB::rollBack();

            $this->logWithActor('user_teaching_engagements.store.failed', $request, [
                'error'   => $e->getMessage(),
                'user_id' => (int)$user->id,
            ]);

            $this->dbActivityLog(
                $request,
                'create',
                $module,
                $this->table,
                null,
                array_keys($request->all()),
                null,
                ['error' => $e->getMessage(), 'target_user_id' => (int)$user->id],
                'Failed to create teaching engagement'
            );

            return response()->json(['success'=>false,'error'=>'Failed to create teaching engagement'],500);
        }
    }

    /**
     * PUT/PATCH /api/users/{user_uuid}/teaching-engagements/{uuid}
     * PUT/PATCH /api/me/teaching-engagements/{uuid}
     *
     * ✅ Updated:
     * - supports metadata as JSON string (FormData)
     * - safe updated_by / updated_at_ip only if columns exist
     */
    public function update(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        $module = 'teaching_engagements';

        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) {
            $this->dbActivityLog($request, 'update', $module, $this->table, null, null, null, [
                'path' => $request->path(), 'method' => $request->method(), 'uuid' => $uuid
            ], 'Unauthorized access attempt');
            return $resp;
        }

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            $this->dbActivityLog($request, 'update', $module, $this->table, null, ['user_uuid'], null, [
                'user_uuid' => $user_uuid, 'uuid' => $uuid
            ], 'User not found');
            return response()->json(['success'=>false,'error'=>'User not found'],404);
        }

        if (!$this->canAccess($request, (int)$user->id)) {
            $this->dbActivityLog($request, 'update', $module, $this->table, null, null, null, [
                'target_user_id' => (int)$user->id, 'uuid' => $uuid
            ], 'Unauthorized Access (target user)');
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'],403);
        }

        $row = DB::table($this->table)
            ->where('uuid',$uuid)
            ->where('user_id',$user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            $this->dbActivityLog($request, 'update', $module, $this->table, null, ['uuid'], null, [
                'uuid' => $uuid, 'target_user_id' => (int)$user->id
            ], 'Record not found');
            return response()->json(['success'=>false,'error'=>'Record not found'],404);
        }

        $v = Validator::make($request->all(), [
            'organization_name' => ['sometimes','required','string','max:255'],
            'domain'            => ['sometimes','nullable','string','max:255'],
            'description'       => ['sometimes','nullable','string'],
            // metadata handled separately
        ]);

        if ($v->fails()) {
            $this->dbActivityLog(
                $request,
                'update',
                $module,
                $this->table,
                (int)$row->id,
                array_keys($request->all()),
                null,
                ['errors' => $v->errors()->toArray()],
                'Validation failed'
            );
            return response()->json(['success'=>false,'errors'=>$v->errors()],422);
        }

        [$metaPresent, $metaValue, $metaErr] = $this->readMetadataFromRequest($request);
        if ($metaErr) {
            $this->dbActivityLog(
                $request,
                'update',
                $module,
                $this->table,
                (int)$row->id,
                ['metadata'],
                null,
                ['error' => $metaErr],
                'Metadata validation failed'
            );
            return response()->json(['success' => false, 'error' => $metaErr], 422);
        }

        $data  = $v->validated();
        $actor = $this->actor($request);

        $upd = [];

        foreach (['organization_name','domain','description'] as $f) {
            if (array_key_exists($f,$data)) $upd[$f] = $data[$f];
        }

        if ($metaPresent) {
            $upd['metadata'] = $metaValue !== null ? json_encode($metaValue) : null;
        }

        // Build old snapshot for diff logging (only relevant fields)
        $oldMeta = null;
        if (isset($row->metadata) && is_string($row->metadata) && $row->metadata !== '') {
            $tmp = json_decode($row->metadata, true);
            $oldMeta = is_array($tmp) ? $tmp : null;
        }

        $oldSnap = [
            'organization_name' => $row->organization_name ?? null,
            'domain'            => $row->domain ?? null,
            'description'       => $row->description ?? null,
            'metadata'          => $oldMeta,
        ];

        if (empty($upd)) {
            // No DB update, but still log PATCH/PUT activity
            $this->dbActivityLog(
                $request,
                'update',
                $module,
                $this->table,
                (int)$row->id,
                [],
                null,
                null,
                'No changes provided (update skipped)'
            );

            return response()->json(['success'=>true,'data'=>$this->decodeMetaRow($row)]);
        }

        $upd['updated_at'] = Carbon::now();

        // safe optional columns
        $this->setIfColumn($upd, 'updated_by', $actor['id'] ?: null);
        $this->setIfColumn($upd, 'updated_at_ip', $request->ip());

        DB::table($this->table)->where('id',$row->id)->update($upd);

        $fresh = DB::table($this->table)->where('id',$row->id)->first();
        $fresh = $this->decodeMetaRow($fresh);

        $this->logWithActor('user_teaching_engagements.update.success', $request, [
            'id' => $row->id,
            'user_id' => (int)$user->id,
        ]);

        // Diff for activity table (only main editable fields)
        $changed = [];
        $oldVals = [];
        $newVals = [];

        foreach (['organization_name','domain','description'] as $f) {
            if (array_key_exists($f, $upd)) {
                $changed[] = $f;
                $oldVals[$f] = $oldSnap[$f];
                $newVals[$f] = $upd[$f];
            }
        }

        if ($metaPresent) {
            $changed[] = 'metadata';
            $oldVals['metadata'] = $oldSnap['metadata'];
            $newVals['metadata'] = $metaValue;
        }

        $this->dbActivityLog(
            $request,
            'update',
            $module,
            $this->table,
            (int)$row->id,
            $changed,
            $oldVals,
            $newVals,
            'Updated teaching engagement'
        );

        return response()->json(['success'=>true,'data'=>$fresh]);
    }

    /**
     * DELETE /api/users/{user_uuid}/teaching-engagements/{uuid}
     * DELETE /api/me/teaching-engagements/{uuid}
     */
    public function destroy(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        $module = 'teaching_engagements';

        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) {
            $this->dbActivityLog($request, 'delete', $module, $this->table, null, null, null, [
                'path' => $request->path(), 'method' => $request->method(), 'uuid' => $uuid
            ], 'Unauthorized access attempt');
            return $resp;
        }

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            $this->dbActivityLog($request, 'delete', $module, $this->table, null, ['user_uuid'], null, [
                'user_uuid' => $user_uuid, 'uuid' => $uuid
            ], 'User not found');
            return response()->json(['success'=>false,'error'=>'User not found'],404);
        }

        if (!$this->canAccess($request, (int)$user->id)) {
            $this->dbActivityLog($request, 'delete', $module, $this->table, null, null, null, [
                'target_user_id' => (int)$user->id, 'uuid' => $uuid
            ], 'Unauthorized Access (target user)');
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'],403);
        }

        $row = DB::table($this->table)
            ->where('uuid',$uuid)
            ->where('user_id',$user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            $this->dbActivityLog($request, 'delete', $module, $this->table, null, ['uuid'], null, [
                'uuid' => $uuid, 'target_user_id' => (int)$user->id
            ], 'Record not found');
            return response()->json(['success'=>false,'error'=>'Record not found'],404);
        }

        $actor = $this->actor($request);
        $now   = Carbon::now();

        $upd = [
            'deleted_at' => $now,
            'updated_at' => $now,
        ];

        // safe optional columns
        $this->setIfColumn($upd, 'updated_by', $actor['id'] ?: null);
        $this->setIfColumn($upd, 'updated_at_ip', $request->ip());

        DB::table($this->table)->where('id',$row->id)->update($upd);

        $this->logWithActor('user_teaching_engagements.destroy', $request, [
            'id' => $row->id,
            'user_id' => (int)$user->id,
        ]);

        $this->dbActivityLog(
            $request,
            'delete',
            $module,
            $this->table,
            (int)$row->id,
            ['deleted_at'],
            ['deleted_at' => $row->deleted_at],
            ['deleted_at' => $now->toDateTimeString()],
            'Soft deleted teaching engagement'
        );

        return response()->json(['success'=>true,'message'=>'Teaching engagement deleted']);
    }

    /* =========================
     * Trash / Restore / Force delete
     * ========================= */

    /**
     * GET /api/users/{user_uuid}/teaching-engagements/deleted
     * GET /api/me/teaching-engagements/deleted
     */
    public function indexDeleted(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'],404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'],403);
        }

        $rows = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->orderBy('deleted_at','desc')
            ->orderBy('id','desc')
            ->get();

        $rows = $this->decodeMetaCollection($rows);

        return response()->json(['success'=>true,'data'=>$rows]);
    }

    /**
     * POST /api/users/{user_uuid}/teaching-engagements/{uuid}/restore
     * POST /api/me/teaching-engagements/{uuid}/restore
     */
    public function restore(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        $module = 'teaching_engagements';

        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) {
            $this->dbActivityLog($request, 'restore', $module, $this->table, null, null, null, [
                'path' => $request->path(), 'method' => $request->method(), 'uuid' => $uuid
            ], 'Unauthorized access attempt');
            return $resp;
        }

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            $this->dbActivityLog($request, 'restore', $module, $this->table, null, ['user_uuid'], null, [
                'user_uuid' => $user_uuid, 'uuid' => $uuid
            ], 'User not found');
            return response()->json(['success'=>false,'error'=>'User not found'],404);
        }

        if (!$this->canAccess($request, (int)$user->id)) {
            $this->dbActivityLog($request, 'restore', $module, $this->table, null, null, null, [
                'target_user_id' => (int)$user->id, 'uuid' => $uuid
            ], 'Unauthorized Access (target user)');
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'],403);
        }

        $row = DB::table($this->table)
            ->where('uuid',$uuid)
            ->where('user_id',$user->id)
            ->whereNotNull('deleted_at')
            ->first();

        if (!$row) {
            $this->dbActivityLog($request, 'restore', $module, $this->table, null, ['uuid'], null, [
                'uuid' => $uuid, 'target_user_id' => (int)$user->id
            ], 'Record not found in Bin');
            return response()->json(['success'=>false,'error'=>'Record not found in Bin'],404);
        }

        $actor = $this->actor($request);
        $now   = Carbon::now();

        $upd = [
            'deleted_at' => null,
            'updated_at' => $now,
        ];

        $this->setIfColumn($upd, 'updated_by', $actor['id'] ?: null);
        $this->setIfColumn($upd, 'updated_at_ip', $request->ip());

        DB::table($this->table)->where('id',$row->id)->update($upd);

        $fresh = DB::table($this->table)->where('id',$row->id)->first();
        $fresh = $this->decodeMetaRow($fresh);

        $this->logWithActor('user_teaching_engagements.restore', $request, [
            'id' => $row->id,
            'user_id' => (int)$user->id,
        ]);

        $this->dbActivityLog(
            $request,
            'restore',
            $module,
            $this->table,
            (int)$row->id,
            ['deleted_at'],
            ['deleted_at' => $row->deleted_at],
            ['deleted_at' => null],
            'Restored teaching engagement'
        );

        return response()->json(['success'=>true,'data'=>$fresh,'message'=>'Restored']);
    }

    /**
     * DELETE /api/users/{user_uuid}/teaching-engagements/{uuid}/force
     * DELETE /api/me/teaching-engagements/{uuid}/force
     */
    public function forceDelete(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        $module = 'teaching_engagements';

        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod'
        ])) {
            $this->dbActivityLog($request, 'force_delete', $module, $this->table, null, null, null, [
                'path' => $request->path(), 'method' => $request->method(), 'uuid' => $uuid
            ], 'Unauthorized access attempt');
            return $resp;
        }

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            $this->dbActivityLog($request, 'force_delete', $module, $this->table, null, ['user_uuid'], null, [
                'user_uuid' => $user_uuid, 'uuid' => $uuid
            ], 'User not found');
            return response()->json(['success'=>false,'error'=>'User not found'],404);
        }

        if (!$this->canAccess($request, (int)$user->id)) {
            $this->dbActivityLog($request, 'force_delete', $module, $this->table, null, null, null, [
                'target_user_id' => (int)$user->id, 'uuid' => $uuid
            ], 'Unauthorized Access (target user)');
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'],403);
        }

        $row = DB::table($this->table)
            ->where('uuid',$uuid)
            ->where('user_id',$user->id)
            ->whereNotNull('deleted_at')
            ->first();

        if (!$row) {
            $this->dbActivityLog($request, 'force_delete', $module, $this->table, null, ['uuid'], null, [
                'uuid' => $uuid, 'target_user_id' => (int)$user->id
            ], 'Record not found in Bin');
            return response()->json(['success'=>false,'error'=>'Record not found in Bin'],404);
        }

        // Snapshot minimal details before delete
        $oldSnap = [
            'uuid'              => $row->uuid ?? null,
            'user_id'           => (int)($row->user_id ?? 0),
            'organization_name' => $row->organization_name ?? null,
            'domain'            => $row->domain ?? null,
            'description'       => $row->description ?? null,
            'deleted_at'        => $row->deleted_at ?? null,
        ];

        DB::table($this->table)->where('id',$row->id)->delete();

        $this->logWithActor('user_teaching_engagements.force_delete', $request, [
            'id' => $row->id,
            'user_id' => (int)$user->id,
        ]);

        $this->dbActivityLog(
            $request,
            'force_delete',
            $module,
            $this->table,
            (int)$row->id,
            null,
            $oldSnap,
            null,
            'Deleted permanently (force delete)'
        );

        return response()->json(['success'=>true,'message'=>'Deleted permanently']);
    }

    /**
     * DELETE /api/users/{user_uuid}/teaching-engagements/deleted/force
     * DELETE /api/me/teaching-engagements/deleted/force
     */
    public function forceDeleteAllDeleted(Request $request, ?string $user_uuid = null)
    {
        $module = 'teaching_engagements';

        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod'
        ])) {
            $this->dbActivityLog($request, 'force_delete_all_deleted', $module, $this->table, null, null, null, [
                'path' => $request->path(), 'method' => $request->method()
            ], 'Unauthorized access attempt');
            return $resp;
        }

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            $this->dbActivityLog($request, 'force_delete_all_deleted', $module, $this->table, null, ['user_uuid'], null, [
                'user_uuid' => $user_uuid
            ], 'User not found');
            return response()->json(['success'=>false,'error'=>'User not found'],404);
        }

        if (!$this->canAccess($request, (int)$user->id)) {
            $this->dbActivityLog($request, 'force_delete_all_deleted', $module, $this->table, null, null, null, [
                'target_user_id' => (int)$user->id
            ], 'Unauthorized Access (target user)');
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'],403);
        }

        $count = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->delete();

        $this->logWithActor('user_teaching_engagements.force_delete_all_deleted', $request, [
            'user_id' => (int)$user->id,
            'count' => $count,
        ]);

        $this->dbActivityLog(
            $request,
            'force_delete_all_deleted',
            $module,
            $this->table,
            null,
            null,
            null,
            ['target_user_id' => (int)$user->id, 'deleted_count' => (int)$count],
            'Emptied bin (force delete all deleted)'
        );

        return response()->json(['success'=>true,'message'=>'Bin emptied','deleted_count'=>$count]);
    }
}
