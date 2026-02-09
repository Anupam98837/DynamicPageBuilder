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

    /* =========================
     * Activity Log (DB table: user_data_activity_log)
     * ========================= */

    private function jsonOrNull($val): ?string
    {
        if ($val === null) return null;
        return json_encode($val, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Insert into user_data_activity_log.
     * Never throws (logging failures must not break API).
     */
    private function logActivity(
        Request $r,
        string $activity,            // create|update|delete|restore|...
        string $module,              // e.g. user_personal_information
        string $tableName,           // e.g. user_personal_information
        ?int $recordId = null,       // primary key id (if known)
        ?array $changedFields = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $note = null
    ): void {
        try {
            $a = $this->actor($r);
            $now = Carbon::now();

            DB::table('user_data_activity_log')->insert([
                'performed_by'       => (int) ($a['id'] ?? 0),
                'performed_by_role'  => $a['role'] ?? null,
                'ip'                 => $r->ip(),
                'user_agent'         => (string) ($r->header('User-Agent') ?? ''),
                'activity'           => $activity,
                'module'             => $module,
                'table_name'         => $tableName,
                'record_id'          => $recordId,
                'changed_fields'     => $changedFields ? $this->jsonOrNull($changedFields) : null,
                'old_values'         => $oldValues ? $this->jsonOrNull($oldValues) : null,
                'new_values'         => $newValues ? $this->jsonOrNull($newValues) : null,
                'log_note'           => $note,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        } catch (\Throwable $e) {
            // Must not affect core flow
            Log::warning('activity_log.insert_failed', [
                'error'   => $e->getMessage(),
                'module'  => $module,
                'table'   => $tableName,
                'action'  => $activity,
            ]);
        }
    }

    /**
     * Build diff arrays between old row and fresh row for selected fields.
     */
    private function buildDiff(?object $old, ?object $fresh, array $fields): array
    {
        $changed = [];
        $oldVals = [];
        $newVals = [];

        foreach ($fields as $f) {
            $ov = $old ? ($old->$f ?? null) : null;
            $nv = $fresh ? ($fresh->$f ?? null) : null;

            // Normalize arrays for comparison (qualification)
            if (is_array($ov) || is_array($nv)) {
                $ovNorm = is_array($ov) ? $ov : [];
                $nvNorm = is_array($nv) ? $nv : [];
                if ($ovNorm !== $nvNorm) {
                    $changed[] = $f;
                    $oldVals[$f] = $ovNorm;
                    $newVals[$f] = $nvNorm;
                }
                continue;
            }

            if ($ov !== $nv) {
                $changed[] = $f;
                $oldVals[$f] = $ov;
                $newVals[$f] = $nv;
            }
        }

        return [$changed, $oldVals, $newVals];
    }

    /* =========================
     * Qualification helpers
     * ========================= */

    private function decodeQualificationRow($row)
    {
        if (!$row) return $row;

        $raw = null;
        if (property_exists($row, 'qualification')) {
            $raw = $row->qualification;
        }

        // MariaDB/MySQL JSON columns usually come as string
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $row->qualification = is_array($decoded) ? $decoded : [];
        } elseif (is_array($raw)) {
            $row->qualification = $raw;
        } else {
            $row->qualification = [];
        }

        return $row;
    }

    /**
     * Decode raw JSON body safely.
     */
    private function decodedJsonBody(Request $request): ?array
    {
        $raw = (string) $request->getContent();
        $rawTrim = trim($raw);
        if ($rawTrim === '') return null;

        $decoded = json_decode($rawTrim, true);
        if (json_last_error() !== JSON_ERROR_NONE) return null;
        if (!is_array($decoded)) return null;

        return $decoded;
    }

    /**
     * Normalize list of strings:
     * - trims
     * - collapses spaces
     * - dedupes (case-insensitive)
     */
    private function normalizeStringList(array $arr): array
    {
        $clean = [];
        $seen = [];
        foreach ($arr as $item) {
            if (!is_string($item)) continue;
            $t = trim(preg_replace('/\s+/', ' ', $item));
            if ($t === '') continue;
            $k = mb_strtolower($t);
            if (isset($seen[$k])) continue;
            $seen[$k] = true;
            $clean[] = $t;
        }
        return $clean;
    }

    /**
     * ✅ FIXED: reliable qualification read for PUT/PATCH JSON + form-data.
     * Returns: [present(bool), value(?array), error(?string)]
     */
    private function readQualificationFromRequest(Request $request): array
    {
        $present = false;
        $q = null;

        /**
         * 1) Most reliable: exists() checks presence of key even if null/empty
         * (works across JSON/form-data/query).
         */
        if (method_exists($request, 'exists') && $request->exists('qualification')) {
            $present = true;
            $q = $request->input('qualification'); // could be array|string|null
        } else {
            // fallback for older versions: check keys
            $all = $request->all();
            if (is_array($all) && array_key_exists('qualification', $all)) {
                $present = true;
                $q = $all['qualification'];
            }
        }

        /**
         * 2) Fallback: raw JSON decode (some PUT/PATCH clients can be weird)
         */
        if (!$present) {
            $decoded = $this->decodedJsonBody($request);
            if (is_array($decoded) && array_key_exists('qualification', $decoded)) {
                $present = true;
                $q = $decoded['qualification'];
            }
        }

        if (!$present) return [false, null, null];

        // Explicit null means "empty list"
        if ($q === null) return [true, [], null];

        // Array payload
        if (is_array($q)) {
            return [true, $this->normalizeStringList($q), null];
        }

        // String payload: can be JSON string OR comma-separated OR single value
        if (is_string($q)) {
            $s = trim($q);
            if ($s === '') return [true, [], null];

            // If it looks like JSON, decode it
            $first = substr($s, 0, 1);
            if ($first === '[' || $first === '{' || $first === '"') {
                $decoded = json_decode($s, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return [true, null, 'Qualification JSON invalid: ' . json_last_error_msg()];
                }

                // allow decoded string like "a,b"
                if (is_string($decoded)) {
                    $parts = array_map('trim', explode(',', $decoded));
                    return [true, $this->normalizeStringList($parts), null];
                }

                if ($decoded === null) return [true, [], null];
                if (!is_array($decoded)) return [true, null, 'Qualification must decode to an array'];

                return [true, $this->normalizeStringList($decoded), null];
            }

            // comma-separated fallback
            if (str_contains($s, ',')) {
                $parts = array_map('trim', explode(',', $s));
                return [true, $this->normalizeStringList($parts), null];
            }

            // single value fallback
            return [true, $this->normalizeStringList([$s]), null];
        }

        return [true, null, 'Qualification must be an array or string'];
    }

    /* =====================================================
     * CRUD ENDPOINTS
     * ===================================================== */

    public function show(Request $request, ?string $user_uuid = null)
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

    public function store(Request $request, ?string $user_uuid = null)
    {
        // ---- activity log: attempt (POST) ----
        $this->logActivity($request, 'create', 'user_personal_information', $this->table, null, null, null, null, 'attempt');

        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) {
            $this->logActivity($request, 'create', 'user_personal_information', $this->table, null, null, null, null, 'unauthorized');
            return $resp;
        }

        Log::info('PI.store.request', [
            'method'       => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'raw_body'     => substr($request->getContent(), 0, 2000),
            'all_keys'     => array_keys($request->all()),
            'qualification_value' => $request->input('qualification'),
            'qualification_type'  => gettype($request->input('qualification')),
        ]);

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            $this->logActivity($request, 'create', 'user_personal_information', $this->table, null, null, null, null, 'user_not_found');
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        }

        if (!$this->canAccessUser($request, (int)$user->id)) {
            $this->logActivity($request, 'create', 'user_personal_information', $this->table, null, null, null, null, 'unauthorized_access_target_user');
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $v = Validator::make($request->all(), [
            'affiliation'      => ['nullable', 'string'],
            'specification'    => ['nullable', 'string'],
            'experience'       => ['nullable', 'string'],
            'interest'         => ['nullable', 'string'],
            'administration'   => ['nullable', 'string'],
            'research_project' => ['nullable', 'string'],
        ]);

        if ($v->fails()) {
            $this->logActivity(
                $request,
                'create',
                'user_personal_information',
                $this->table,
                null,
                null,
                null,
                null,
                'validation_failed: ' . $v->errors()->toJson()
            );
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        [$qPresent, $qValue, $qErr] = $this->readQualificationFromRequest($request);

        Log::info('PI.store.qualification_parse', [
            'present' => $qPresent,
            'value'   => $qValue,
            'type'    => gettype($qValue),
            'count'   => is_array($qValue) ? count($qValue) : null,
            'error'   => $qErr,
        ]);

        if ($qErr) {
            $this->logActivity($request, 'create', 'user_personal_information', $this->table, null, ['qualification'], null, null, 'qualification_error: '.$qErr);
            return response()->json(['success' => false, 'error' => $qErr], 422);
        }

        $data  = $v->validated();
        $now   = Carbon::now();
        $actor = $this->actor($request);

        DB::beginTransaction();
        try {
            $existing = DB::table($this->table)
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                DB::rollBack();

                $this->logActivity(
                    $request,
                    'create',
                    'user_personal_information',
                    $this->table,
                    (int)($existing->id ?? 0),
                    null,
                    null,
                    null,
                    'conflict: already_exists'
                );

                return response()->json([
                    'success' => false,
                    'error'   => 'Personal information already exists. Use update.',
                ], 409);
            }

            $insert = [
                'uuid'             => (string) Str::uuid(),
                'user_id'          => (int) $user->id,
                'qualification'    => json_encode($qValue ?? [], JSON_UNESCAPED_UNICODE),
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
            ];

            $id = DB::table($this->table)->insertGetId($insert);
            DB::commit();

            $this->logWithActor('user_personal_information.store.success', $request, [
                'id'      => $id,
                'user_id' => (int)$user->id,
            ]);

            $row = DB::table($this->table)->where('id', $id)->first();
            $row = $this->decodeQualificationRow($row);

            // ---- activity log: success (create) ----
            $fields = ['uuid','user_id','qualification','affiliation','specification','experience','interest','administration','research_project'];
            $changedFields = $fields;
            $newValues = [];
            foreach ($fields as $f) $newValues[$f] = $row->$f ?? null;

            $this->logActivity(
                $request,
                'create',
                'user_personal_information',
                $this->table,
                (int)$id,
                $changedFields,
                null,
                $newValues,
                'success'
            );

            return response()->json(['success' => true, 'data' => $row], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            $this->logWithActor('user_personal_information.store.failed', $request, [
                'error'   => $e->getMessage(),
                'user_id' => (int)$user->id,
            ]);

            $this->logActivity(
                $request,
                'create',
                'user_personal_information',
                $this->table,
                null,
                null,
                null,
                null,
                'failed: '.$e->getMessage()
            );

            return response()->json(['success' => false, 'error' => 'Failed to create personal information'], 500);
        }
    }

    public function update(Request $request, ?string $user_uuid = null)
    {
        // ---- activity log: attempt (PUT/PATCH) ----
        $this->logActivity($request, 'update', 'user_personal_information', $this->table, null, null, null, null, 'attempt');

        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) {
            $this->logActivity($request, 'update', 'user_personal_information', $this->table, null, null, null, null, 'unauthorized');
            return $resp;
        }

        Log::info('PI.update.request', [
            'method'       => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'raw_body'     => substr($request->getContent(), 0, 2000),
            'all_keys'     => array_keys($request->all()),
            'qualification_value' => $request->input('qualification'),
            'qualification_type'  => gettype($request->input('qualification')),
        ]);

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            $this->logActivity($request, 'update', 'user_personal_information', $this->table, null, null, null, null, 'user_not_found');
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        }

        if (!$this->canAccessUser($request, (int)$user->id)) {
            $this->logActivity($request, 'update', 'user_personal_information', $this->table, null, null, null, null, 'unauthorized_access_target_user');
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $v = Validator::make($request->all(), [
            'affiliation'      => ['sometimes', 'nullable', 'string'],
            'specification'    => ['sometimes', 'nullable', 'string'],
            'experience'       => ['sometimes', 'nullable', 'string'],
            'interest'         => ['sometimes', 'nullable', 'string'],
            'administration'   => ['sometimes', 'nullable', 'string'],
            'research_project' => ['sometimes', 'nullable', 'string'],
            'qualification_force_clear' => ['sometimes','boolean'],
        ]);

        if ($v->fails()) {
            $this->logActivity(
                $request,
                'update',
                'user_personal_information',
                $this->table,
                null,
                null,
                null,
                null,
                'validation_failed: ' . $v->errors()->toJson()
            );
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        [$qPresent, $qValue, $qErr] = $this->readQualificationFromRequest($request);

        Log::info('PI.update.qualification_parse', [
            'present' => $qPresent,
            'value'   => $qValue,
            'type'    => gettype($qValue),
            'count'   => is_array($qValue) ? count($qValue) : null,
            'error'   => $qErr,
        ]);

        if ($qErr) {
            $this->logActivity($request, 'update', 'user_personal_information', $this->table, null, ['qualification'], null, null, 'qualification_error: '.$qErr);
            return response()->json(['success' => false, 'error' => $qErr], 422);
        }

        $data  = $v->validated();
        $now   = Carbon::now();

        DB::beginTransaction();
        try {
            $row = DB::table($this->table)
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::rollBack();

                $this->logActivity(
                    $request,
                    'update',
                    'user_personal_information',
                    $this->table,
                    null,
                    null,
                    null,
                    null,
                    'not_found: personal_information_missing'
                );

                return response()->json([
                    'success' => false,
                    'error'   => 'Personal information not found. Create it first (store).',
                ], 404);
            }

            $oldRow = $this->decodeQualificationRow(clone $row);

            $update = [];

            // UI sends qualification_force_clear=true when user intentionally clears all tags
            $forceClear = (bool)($data['qualification_force_clear'] ?? false);

            /**
             * ✅ IMPORTANT FIX:
             * - if qualification key is present -> we consider updating it
             * - update even when client changes size (4 -> 3 etc.)
             * - prevent accidental overwrite to [] unless forceClear=true
             */
            if ($qPresent) {
                if (is_array($qValue) && count($qValue) === 0 && !$forceClear) {
                    Log::warning('PI.update.skip_empty_qualification', [
                        'reason'  => 'qualification present but empty - skipping unless qualification_force_clear=true',
                        'user_id' => (int)$user->id,
                        'row_id'  => (int)($row->id ?? 0),
                    ]);
                } else {
                    $update['qualification'] = json_encode($qValue ?? [], JSON_UNESCAPED_UNICODE);
                }
            }

            if (array_key_exists('affiliation', $data))      $update['affiliation']      = $data['affiliation'];
            if (array_key_exists('specification', $data))    $update['specification']    = $data['specification'];
            if (array_key_exists('experience', $data))       $update['experience']       = $data['experience'];
            if (array_key_exists('interest', $data))         $update['interest']         = $data['interest'];
            if (array_key_exists('administration', $data))   $update['administration']   = $data['administration'];
            if (array_key_exists('research_project', $data)) $update['research_project'] = $data['research_project'];

            if (empty($update)) {
                DB::commit();

                // ---- activity log: no-op update (still a PUT/PATCH call) ----
                $this->logActivity(
                    $request,
                    'update',
                    'user_personal_information',
                    $this->table,
                    (int)($row->id ?? 0),
                    [],
                    [],
                    [],
                    'no_changes'
                );

                return response()->json(['success' => true, 'data' => $this->decodeQualificationRow($row)]);
            }

            $update['updated_at'] = $now;

            /**
             * ✅ BIG FIX:
             * Update by user_id (not only by id) so it can’t silently fail
             * if primary key assumptions differ.
             */
            $affected = DB::table($this->table)
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->update($update);

            DB::commit();

            $fresh = DB::table($this->table)
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->first();

            $fresh = $this->decodeQualificationRow($fresh);

            $this->logWithActor('user_personal_information.update.success', $request, [
                'row_id'      => (int)($row->id ?? 0),
                'user_id'     => (int)$user->id,
                'affected'    => (int)$affected,
                'q_present'   => (bool)$qPresent,
                'q_count'     => is_array($qValue) ? count($qValue) : null,
            ]);

            // ---- activity log: success (update) with diffs ----
            $diffFields = ['qualification','affiliation','specification','experience','interest','administration','research_project'];
            [$changedFields, $oldValues, $newValues] = $this->buildDiff($oldRow, $fresh, $diffFields);

            $this->logActivity(
                $request,
                'update',
                'user_personal_information',
                $this->table,
                (int)($row->id ?? 0),
                $changedFields,
                $oldValues,
                $newValues,
                'success'
            );

            return response()->json(['success' => true, 'data' => $fresh]);

        } catch (\Throwable $e) {
            DB::rollBack();

            $this->logWithActor('user_personal_information.update.failed', $request, [
                'error'   => $e->getMessage(),
                'user_id' => (int)$user->id,
            ]);

            $this->logActivity(
                $request,
                'update',
                'user_personal_information',
                $this->table,
                null,
                null,
                null,
                null,
                'failed: '.$e->getMessage()
            );

            return response()->json(['success' => false, 'error' => 'Failed to update personal information'], 500);
        }
    }

    public function destroy(Request $request, ?string $user_uuid = null)
    {
        // ---- activity log: attempt (DELETE) ----
        $this->logActivity($request, 'delete', 'user_personal_information', $this->table, null, null, null, null, 'attempt');

        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) {
            $this->logActivity($request, 'delete', 'user_personal_information', $this->table, null, null, null, null, 'unauthorized');
            return $resp;
        }

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            $this->logActivity($request, 'delete', 'user_personal_information', $this->table, null, null, null, null, 'user_not_found');
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        }

        if (!$this->canAccessUser($request, (int)$user->id)) {
            $this->logActivity($request, 'delete', 'user_personal_information', $this->table, null, null, null, null, 'unauthorized_access_target_user');
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            $this->logActivity($request, 'delete', 'user_personal_information', $this->table, null, null, null, null, 'not_found');
            return response()->json(['success' => false, 'error' => 'Personal information not found'], 404);
        }

        $oldRow = $this->decodeQualificationRow(clone $row);

        $now = Carbon::now();

        DB::table($this->table)->where('user_id', $user->id)->update([
            'deleted_at' => $now,
            'updated_at' => $now,
        ]);

        $this->logWithActor('user_personal_information.destroy', $request, [
            'row_id'  => (int)($row->id ?? 0),
            'user_id' => (int)$user->id,
        ]);

        // ---- activity log: success (delete) ----
        $this->logActivity(
            $request,
            'delete',
            'user_personal_information',
            $this->table,
            (int)($row->id ?? 0),
            ['deleted_at'],
            ['deleted_at' => $oldRow->deleted_at ?? null],
            ['deleted_at' => $now->toDateTimeString()],
            'success'
        );

        return response()->json(['success' => true, 'message' => 'Personal information deleted']);
    }

    public function restore(Request $request, ?string $user_uuid = null)
    {
        // ---- activity log: attempt (POST/PATCH restore) ----
        $this->logActivity($request, 'restore', 'user_personal_information', $this->table, null, null, null, null, 'attempt');

        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','technical_assistant','it_person'
        ])) {
            $this->logActivity($request, 'restore', 'user_personal_information', $this->table, null, null, null, null, 'unauthorized');
            return $resp;
        }

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) {
            $this->logActivity($request, 'restore', 'user_personal_information', $this->table, null, null, null, null, 'user_not_found');
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        }

        $actor = $this->actor($request);
        if (!$this->isHighRole($actor['role']) && $actor['id'] !== (int)$user->id) {
            $this->logActivity($request, 'restore', 'user_personal_information', $this->table, null, null, null, null, 'unauthorized_access_target_user');
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->first();

        if (!$row) {
            $this->logActivity($request, 'restore', 'user_personal_information', $this->table, null, null, null, null, 'not_found_deleted_row');
            return response()->json(['success' => false, 'error' => 'No deleted personal information found'], 404);
        }

        $oldDeletedAt = $row->deleted_at ?? null;

        DB::table($this->table)->where('user_id', $user->id)->update([
            'deleted_at' => null,
            'updated_at' => Carbon::now(),
        ]);

        $this->logWithActor('user_personal_information.restore', $request, [
            'row_id'  => (int)($row->id ?? 0),
            'user_id' => (int)$user->id,
        ]);

        $fresh = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        $fresh = $this->decodeQualificationRow($fresh);

        // ---- activity log: success (restore) ----
        $this->logActivity(
            $request,
            'restore',
            'user_personal_information',
            $this->table,
            (int)($row->id ?? 0),
            ['deleted_at'],
            ['deleted_at' => $oldDeletedAt],
            ['deleted_at' => null],
            'success'
        );

        return response()->json(['success' => true, 'data' => $fresh]);
    }
}
