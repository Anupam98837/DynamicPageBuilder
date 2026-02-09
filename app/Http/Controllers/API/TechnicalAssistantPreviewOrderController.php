<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TechnicalAssistantPreviewOrderController extends Controller
{
    private const TABLE = 'technical_assistant_preview_orders';
    private const LOG_TABLE = 'user_data_activity_log';
    private const LOG_MODULE = 'technical_assistant_preview_orders';

    // Exclude these roles when loading dept users (same pattern as your Faculty controller)
    private const EXCLUDED_ROLES = ['super_admin', 'admin', 'director', 'student', 'students'];

    // If your app uses a specific role value for TAs, keep it here (safe multi-variants)
    private const TA_ROLES = ['technical_assistant', 'technicalassistant', 'technical-assistant', 'ta'];

    // =========================
    // Auth / helpers (same style)
    // =========================
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

    private function isUuid(string $v): bool
    {
        return (bool) preg_match('/^[0-9a-fA-F-]{36}$/', $v);
    }

    private function tableReady(): bool
    {
        return Schema::hasTable(self::TABLE);
    }

    private function activityLogReady(): bool
    {
        return Schema::hasTable(self::LOG_TABLE);
    }

    /**
     * Insert a row into user_data_activity_log (failsafe; never breaks API)
     */
    private function writeActivityLog(
        Request $r,
        string $activity,
        string $tableName,
        ?int $recordId = null,
        ?array $changedFields = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $note = null
    ): void {
        if (!$this->activityLogReady()) return;

        $a = $this->actor($r);
        $now = Carbon::now();

        $ip = (string)($r->ip() ?? '');
        if (strlen($ip) > 45) $ip = substr($ip, 0, 45);

        $ua = (string)($r->userAgent() ?? $r->header('User-Agent') ?? '');
        if (strlen($ua) > 512) $ua = substr($ua, 0, 512);

        $role = (string)($a['role'] ?? '');
        if ($role !== '' && strlen($role) > 50) $role = substr($role, 0, 50);

        try {
            DB::table(self::LOG_TABLE)->insert([
                'performed_by'       => (int)($a['id'] ?? 0),
                'performed_by_role'  => $role !== '' ? $role : null,
                'ip'                 => $ip !== '' ? $ip : null,
                'user_agent'         => $ua !== '' ? $ua : null,

                'activity'           => (string)$activity,
                'module'             => self::LOG_MODULE,

                'table_name'         => (string)$tableName,
                'record_id'          => $recordId !== null ? (int)$recordId : null,

                'changed_fields'     => $changedFields !== null
                    ? json_encode(array_values($changedFields), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                    : null,
                'old_values'         => $oldValues !== null
                    ? json_encode($oldValues, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                    : null,
                'new_values'         => $newValues !== null
                    ? json_encode($newValues, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                    : null,

                'log_note'           => $note,

                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        } catch (\Throwable $e) {
            // Never break functionality if logging fails
            Log::warning('msit.activity_log_failed', [
                'module' => self::LOG_MODULE,
                'activity' => $activity,
                'table_name' => $tableName,
                'record_id' => $recordId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Resolve department by id|uuid|slug
     */
    private function resolveDepartment(string $identifier)
    {
        if (!Schema::hasTable('departments')) return null;

        $q = DB::table('departments')->whereNull('deleted_at');

        if (ctype_digit($identifier)) {
            $q->where('id', (int)$identifier);
        } elseif ($this->isUuid($identifier)) {
            $q->where('uuid', $identifier);
        } else {
            $q->where('slug', $identifier);
        }

        return $q->first();
    }

    /**
     * Pick the correct JSON column name for stored TA IDs.
     */
    private function technicalAssistantJsonCol(): string
    {
        $candidates = [
            'technical_assistant_user_ids_json',
            'technical_assistant_ids_json',
            'technical_assistant_json',
            'technical_assistant_ids',
            'tas_json',
        ];

        foreach ($candidates as $c) {
            if (Schema::hasColumn(self::TABLE, $c)) return $c;
        }

        return 'technical_assistant_user_ids_json';
    }

    /**
     * Pick active/status column
     */
    private function activeCol(): ?string
    {
        $candidates = ['active', 'is_active', 'status'];
        foreach ($candidates as $c) {
            if (Schema::hasColumn(self::TABLE, $c)) return $c;
        }
        return null;
    }

    /**
     * Convert active int (1/0) into correct storage for the column.
     * - status -> 'active'/'inactive'
     * - others -> 1/0
     */
    private function activeToStorage(?string $activeCol, int $val)
    {
        if (!$activeCol) return null;
        if ($activeCol === 'status') return $val === 1 ? 'active' : 'inactive';
        return $val === 1 ? 1 : 0;
    }

    /**
     * Safely decode JSON into array
     */
    private function toArray($val): array
    {
        if ($val === null) return [];
        if (is_array($val)) return $val;

        $s = trim((string)$val);
        if ($s === '') return [];

        try {
            $d = json_decode($s, true, 512, JSON_THROW_ON_ERROR);
            return is_array($d) ? $d : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function normalizeIds(array $ids): array
    {
        $out = [];
        foreach ($ids as $v) {
            $i = (int)$v;
            if ($i > 0) $out[] = $i;
        }

        // keep order, remove duplicates
        $seen = [];
        $final = [];
        foreach ($out as $i) {
            if (isset($seen[$i])) continue;
            $seen[$i] = true;
            $final[] = $i;
        }
        return $final;
    }

    /**
     * Eligible users query for department
     * (filters to technical assistant roles by default)
     */
    private function eligibleUsersQuery(int $deptId, string $statusFilter = 'active')
    {
        $upiHasDept  = Schema::hasTable('user_personal_information') && Schema::hasColumn('user_personal_information', 'department_id');
        $userHasDept = Schema::hasColumn('users', 'department_id');

        $q = DB::table('users as u')
            ->leftJoin('user_personal_information as upi', 'upi.user_id', '=', 'u.id')
            ->whereNull('u.deleted_at')
            ->whereNotIn('u.role', self::EXCLUDED_ROLES)
            ->whereIn('u.role', self::TA_ROLES)
            ->where(function ($w) {
                $w->whereNull('upi.id')->orWhereNull('upi.deleted_at');
            });

        // status filter (default active)
        if ($statusFilter !== 'all') {
            $q->where('u.status', $statusFilter === 'inactive' ? 'inactive' : 'active');
        }

        // dept filter (accept either storage)
        $q->where(function ($w) use ($deptId, $upiHasDept, $userHasDept) {
            $applied = false;

            if ($upiHasDept) {
                $w->orWhere('upi.department_id', $deptId);
                $applied = true;
            }

            if ($userHasDept) {
                $w->orWhere('u.department_id', $deptId);
                $applied = true;
            }

            if (!$applied) {
                $w->whereRaw('1=0');
            }
        });

        return $q;
    }

    private function eligibleUsersByIds(int $deptId, array $ids, string $statusFilter = 'active')
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (empty($ids)) return collect();

        return $this->eligibleUsersQuery($deptId, $statusFilter)
            ->whereIn('u.id', $ids)
            ->select([
                'u.id', 'u.uuid', 'u.slug', 'u.name', 'u.email',
                'u.image', 'u.role', 'u.role_short_form', 'u.status',
                'u.created_at', 'u.updated_at',
            ])
            ->get();
    }

    // =====================================================
    // API ENDPOINTS (ADMIN)
    // =====================================================

    /**
     * GET /api/technical-assistant-preview-order
     */
    public function index(Request $request)
    {
        if (!$this->tableReady()) {
            return response()->json(['success' => false, 'error' => 'technical_assistant_preview_orders table not found'], 422);
        }

        if ($resp = $this->requireRole($request, ['admin','director','principal','hod'])) return $resp;

        $activeCol = $this->activeCol();
        $jsonCol   = $this->technicalAssistantJsonCol();

        $rows = DB::table(self::TABLE . ' as tapo')
            ->leftJoin('departments as d', 'd.id', '=', 'tapo.department_id')
            ->whereNull('tapo.deleted_at')
            ->select(array_filter([
                'tapo.id',
                Schema::hasColumn(self::TABLE, 'uuid') ? 'tapo.uuid' : null,
                'tapo.department_id',
                'd.uuid as department_uuid',
                'd.slug as department_slug',
                'd.title as department_title',
                $activeCol ? 'tapo.'.$activeCol.' as active_raw' : null,
                'tapo.'.$jsonCol.' as technical_assistant_user_ids_json',
                'tapo.created_at',
                'tapo.updated_at',
            ]))
            ->orderBy('tapo.id', 'desc')
            ->get();

        $rows->each(function ($r) {
            $arr = is_string($r->technical_assistant_user_ids_json ?? null)
                ? json_decode($r->technical_assistant_user_ids_json, true)
                : ($r->technical_assistant_user_ids_json ?? []);
            $r->technical_assistant_count = is_array($arr) ? count($arr) : 0;
        });

        return response()->json(['success' => true, 'data' => $rows]);
    }

    /**
     * GET /api/technical-assistant-preview-order/{department}
     */
    public function show(Request $request, string $department)
    {
        if (!$this->tableReady()) {
            return response()->json(['success' => false, 'error' => 'technical_assistant_preview_orders table not found'], 422);
        }

        if ($resp = $this->requireRole($request, ['admin','director','principal','hod'])) return $resp;

        $dept = $this->resolveDepartment($department);
        if (!$dept) {
            return response()->json(['success' => false, 'error' => 'Department not found'], 404);
        }

        $statusFilter = strtolower(trim((string)$request->query('status', 'active'))) ?: 'active';
        if (!in_array($statusFilter, ['active','inactive','all'], true)) $statusFilter = 'active';

        $jsonCol   = $this->technicalAssistantJsonCol();
        $activeCol = $this->activeCol();

        $orderRow = DB::table(self::TABLE)
            ->where('department_id', (int)$dept->id)
            ->whereNull('deleted_at')
            ->first();

        $assignedIds = [];
        $activeVal   = 1;

        if ($orderRow) {
            $assignedIds = $this->normalizeIds($this->toArray($orderRow->{$jsonCol} ?? null));

            if ($activeCol) {
                $raw = $orderRow->{$activeCol};
                if (is_numeric($raw)) $activeVal = ((int)$raw) === 1 ? 1 : 0;
                else {
                    $s = strtolower(trim((string)$raw));
                    $activeVal = ($s === 'active' || $s === '1' || $s === 'true') ? 1 : 0;
                }
            }
        }

        // assigned users (filter eligible + keep order)
        $assignedRows = $this->eligibleUsersByIds((int)$dept->id, $assignedIds, $statusFilter);
        $assignedMap  = [];
        foreach ($assignedRows as $u) $assignedMap[(int)$u->id] = $u;

        $assignedOrdered = [];
        foreach ($assignedIds as $id) {
            if (isset($assignedMap[$id])) $assignedOrdered[] = $assignedMap[$id];
        }

        // unassigned users = eligible users NOT IN assigned
        $unassignedQ = $this->eligibleUsersQuery((int)$dept->id, $statusFilter)
            ->select([
                'u.id', 'u.uuid', 'u.slug', 'u.name', 'u.email',
                'u.image', 'u.role', 'u.role_short_form', 'u.status',
                'u.created_at', 'u.updated_at',
            ])
            ->orderBy('u.name', 'asc')
            ->orderBy('u.id', 'asc');

        if (!empty($assignedIds)) {
            $unassignedQ->whereNotIn('u.id', $assignedIds);
        }

        $unassigned = $unassignedQ->get();

        return response()->json([
            'success' => true,
            'department' => [
                'id'    => (int)$dept->id,
                'uuid'  => (string)($dept->uuid ?? ''),
                'slug'  => (string)($dept->slug ?? ''),
                'title' => (string)($dept->title ?? ''),
            ],
            'order' => [
                'exists'                     => (bool)$orderRow,
                'active'                     => (int)$activeVal,
                'technical_assistant_ids'    => $assignedIds,
                'technical_assistant_count'  => count($assignedIds),
            ],
            'assigned'   => $assignedOrdered,
            'unassigned' => $unassigned,
        ]);
    }

    /**
     * POST /api/technical-assistant-preview-order/{department}/save
     * Body:
     * {
     *   "technical_assistant_ids": [12,5,9],  // ordered ids
     *   "active": 1                            // optional (1/0 or 'active'/'inactive')
     * }
     */
    public function save(Request $request, string $department)
    {
        if (!$this->tableReady()) {
            $this->writeActivityLog($request, 'error', self::TABLE, null, null, null, null, 'Target table not found');
            return response()->json(['success' => false, 'error' => 'technical_assistant_preview_orders table not found'], 422);
        }

        if ($resp = $this->requireRole($request, ['admin','director','principal','hod'])) {
            $this->writeActivityLog($request, 'unauthorized', self::TABLE, null, null, null, null, 'Unauthorized access attempt to save preview order');
            return $resp;
        }

        $dept = $this->resolveDepartment($department);
        if (!$dept) {
            $this->writeActivityLog($request, 'error', self::TABLE, null, null, null, null, 'Department not found while saving preview order');
            return response()->json(['success' => false, 'error' => 'Department not found'], 404);
        }

        $v = Validator::make($request->all(), [
            'technical_assistant_ids'   => ['required', 'array'],
            'technical_assistant_ids.*' => ['required', 'integer', 'min:1'],
            'active'                   => ['nullable'],
        ]);

        if ($v->fails()) {
            $this->writeActivityLog(
                $request,
                'validation_failed',
                self::TABLE,
                null,
                array_keys($v->errors()->toArray()),
                null,
                null,
                'Validation failed while saving preview order (department_id='.$dept->id.')'
            );

            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $data = $v->validated();
        $taIds = $this->normalizeIds($data['technical_assistant_ids'] ?? []);

        // Validate: only eligible users for this dept (allow active+inactive)
        $eligible = $this->eligibleUsersByIds((int)$dept->id, $taIds, 'all');
        $eligibleIds = $eligible->pluck('id')->map(fn($x) => (int)$x)->values()->all();
        $eligibleSet = array_fill_keys($eligibleIds, true);

        $final = [];
        foreach ($taIds as $id) {
            if (isset($eligibleSet[$id])) $final[] = $id;
        }

        $activeCol = $this->activeCol();
        $activeVal = null;

        if ($activeCol) {
            $raw = $request->input('active', 1);
            if (is_string($raw)) {
                $s = strtolower(trim($raw));
                $activeVal = ($s === '1' || $s === 'true' || $s === 'active') ? 1 : 0;
            } else {
                $activeVal = ((int)$raw) === 1 ? 1 : 0;
            }
        }

        $jsonCol = $this->technicalAssistantJsonCol();
        $now     = Carbon::now();
        $actor   = $this->actor($request);

        DB::beginTransaction();
        try {
            $existing = DB::table(self::TABLE)
                ->where('department_id', (int)$dept->id)
                ->whereNull('deleted_at')
                ->first();

            // Old snapshot (for activity log)
            $oldValues = null;
            if ($existing) {
                $oldAssigned = $this->normalizeIds($this->toArray($existing->{$jsonCol} ?? null));
                $oldActive = null;
                if ($activeCol) {
                    $rawOld = $existing->{$activeCol};
                    if (is_numeric($rawOld)) $oldActive = ((int)$rawOld) === 1 ? 1 : 0;
                    else {
                        $sOld = strtolower(trim((string)$rawOld));
                        $oldActive = ($sOld === 'active' || $sOld === '1' || $sOld === 'true') ? 1 : 0;
                    }
                }

                $oldValues = [
                    'department_id' => (int)$dept->id,
                    'technical_assistant_ids' => $oldAssigned,
                    'count' => count($oldAssigned),
                    'active' => $activeCol ? (int)($oldActive ?? 1) : null,
                ];
            }

            $payload = [
                $jsonCol     => json_encode($final),
                'updated_at' => $now,
            ];

            if (Schema::hasColumn(self::TABLE, 'updated_at_ip')) {
                $payload['updated_at_ip'] = $request->ip();
            }
            if (Schema::hasColumn(self::TABLE, 'updated_by')) {
                $payload['updated_by'] = $actor['id'] ?: null;
            }
            if ($activeCol && $activeVal !== null) {
                $payload[$activeCol] = $this->activeToStorage($activeCol, $activeVal);
            }

            $action = 'create';
            if ($existing) {
                DB::table(self::TABLE)->where('id', $existing->id)->update($payload);
                $rowId = (int)$existing->id;
                $action = 'update';
            } else {
                $insert = array_merge([
                    'department_id' => (int)$dept->id,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ], $payload);

                if (Schema::hasColumn(self::TABLE, 'uuid')) {
                    $insert['uuid'] = (string) Str::uuid();
                }
                if (Schema::hasColumn(self::TABLE, 'created_by')) {
                    $insert['created_by'] = $actor['id'] ?: null;
                }
                if (Schema::hasColumn(self::TABLE, 'created_at_ip')) {
                    $insert['created_at_ip'] = $request->ip();
                }

                $rowId = (int) DB::table(self::TABLE)->insertGetId($insert);
            }

            DB::commit();

            $this->logWithActor('msit.technical_assistant_preview_order.save', $request, [
                'department_id' => (int)$dept->id,
                'row_id'        => $rowId,
                'count'         => count($final),
            ]);

            // New snapshot (for activity log)
            $newValues = [
                'department_id' => (int)$dept->id,
                'technical_assistant_ids' => $final,
                'count' => count($final),
                'active' => $activeCol ? (int)($activeVal ?? 1) : null,
            ];

            $changed = [$jsonCol];
            if ($activeCol) $changed[] = $activeCol;

            $this->writeActivityLog(
                $request,
                $action,
                self::TABLE,
                (int)$rowId,
                $changed,
                $oldValues,
                $newValues,
                'Saved technical assistant preview order (department_id='.$dept->id.', count='.count($final).')'
            );

            return response()->json([
                'success' => true,
                'message' => 'Technical assistant preview order saved',
                'data' => [
                    'department_id'              => (int)$dept->id,
                    'technical_assistant_ids'    => $final,
                    'count'                      => count($final),
                    'active'                     => $activeCol ? (int)($activeVal ?? 1) : null,
                ],
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            $this->logWithActor('msit.technical_assistant_preview_order.save_failed', $request, [
                'department_id' => (int)$dept->id,
                'error'         => $e->getMessage(),
            ]);

            $this->writeActivityLog(
                $request,
                'error',
                self::TABLE,
                null,
                null,
                null,
                null,
                'Failed to save technical assistant preview order (department_id='.$dept->id.'): '.$e->getMessage()
            );

            return response()->json(['success' => false, 'error' => 'Failed to save order'], 500);
        }
    }

    /**
     * POST /api/technical-assistant-preview-order/{department}/toggle-active
     * Body: { "active": 1 } or { "active": 0 }
     */
    public function toggleActive(Request $request, string $department)
    {
        if (!$this->tableReady()) {
            $this->writeActivityLog($request, 'error', self::TABLE, null, null, null, null, 'Target table not found');
            return response()->json(['success' => false, 'error' => 'technical_assistant_preview_orders table not found'], 422);
        }

        if ($resp = $this->requireRole($request, ['admin','director','principal','hod'])) {
            $this->writeActivityLog($request, 'unauthorized', self::TABLE, null, null, null, null, 'Unauthorized access attempt to toggle active');
            return $resp;
        }

        $activeCol = $this->activeCol();
        if (!$activeCol) {
            $this->writeActivityLog($request, 'error', self::TABLE, null, null, null, null, 'No active/status column found while toggling');
            return response()->json(['success' => false, 'error' => 'No active/status column found in technical_assistant_preview_orders'], 422);
        }

        $dept = $this->resolveDepartment($department);
        if (!$dept) {
            $this->writeActivityLog($request, 'error', self::TABLE, null, null, null, null, 'Department not found while toggling active');
            return response()->json(['success' => false, 'error' => 'Department not found'], 404);
        }

        $v = Validator::make($request->all(), [
            'active' => ['required'],
        ]);
        if ($v->fails()) {
            $this->writeActivityLog(
                $request,
                'validation_failed',
                self::TABLE,
                null,
                array_keys($v->errors()->toArray()),
                null,
                null,
                'Validation failed while toggling active (department_id='.$dept->id.')'
            );
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $raw = $request->input('active');
        $val = 0;
        if (is_string($raw)) {
            $s = strtolower(trim($raw));
            $val = ($s === '1' || $s === 'true' || $s === 'active') ? 1 : 0;
        } else {
            $val = ((int)$raw) === 1 ? 1 : 0;
        }

        $existing = DB::table(self::TABLE)
            ->where('department_id', (int)$dept->id)
            ->whereNull('deleted_at')
            ->first();

        $now = Carbon::now();

        // Old snapshot
        $jsonCol = $this->technicalAssistantJsonCol();
        $oldValues = null;
        if ($existing) {
            $oldAssigned = $this->normalizeIds($this->toArray($existing->{$jsonCol} ?? null));
            $oldActive = null;
            $rawOld = $existing->{$activeCol};
            if (is_numeric($rawOld)) $oldActive = ((int)$rawOld) === 1 ? 1 : 0;
            else {
                $sOld = strtolower(trim((string)$rawOld));
                $oldActive = ($sOld === 'active' || $sOld === '1' || $sOld === 'true') ? 1 : 0;
            }

            $oldValues = [
                'department_id' => (int)$dept->id,
                'technical_assistant_ids' => $oldAssigned,
                'count' => count($oldAssigned),
                'active' => (int)($oldActive ?? 1),
            ];
        }

        try {
            $action = 'update';
            $rowId = $existing ? (int)$existing->id : null;

            if (!$existing) {
                $insert = [
                    'department_id' => (int)$dept->id,
                    $jsonCol => json_encode([]),
                    $activeCol => $this->activeToStorage($activeCol, $val),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if (Schema::hasColumn(self::TABLE, 'uuid')) $insert['uuid'] = (string) Str::uuid();
                if (Schema::hasColumn(self::TABLE, 'created_by')) $insert['created_by'] = $this->actor($request)['id'] ?: null;
                if (Schema::hasColumn(self::TABLE, 'created_at_ip')) $insert['created_at_ip'] = $request->ip();

                $rowId = (int) DB::table(self::TABLE)->insertGetId($insert);
                $action = 'create';
            } else {
                $upd = [
                    $activeCol   => $this->activeToStorage($activeCol, $val),
                    'updated_at' => $now,
                ];
                if (Schema::hasColumn(self::TABLE, 'updated_at_ip')) $upd['updated_at_ip'] = $request->ip();
                DB::table(self::TABLE)->where('id', $existing->id)->update($upd);
            }

            $this->logWithActor('msit.technical_assistant_preview_order.toggle_active', $request, [
                'department_id' => (int)$dept->id,
                'active'        => $val,
            ]);

            // New snapshot
            $newValues = [
                'department_id' => (int)$dept->id,
                'active' => (int)$val,
            ];

            $this->writeActivityLog(
                $request,
                $action,
                self::TABLE,
                $rowId,
                [$activeCol],
                $oldValues,
                $newValues,
                'Toggled active for technical assistant preview order (department_id='.$dept->id.', active='.$val.')'
            );

            return response()->json(['success' => true, 'active' => $val]);

        } catch (\Throwable $e) {
            $this->logWithActor('msit.technical_assistant_preview_order.toggle_active_failed', $request, [
                'department_id' => (int)$dept->id,
                'error' => $e->getMessage(),
            ]);

            $this->writeActivityLog(
                $request,
                'error',
                self::TABLE,
                $existing ? (int)$existing->id : null,
                null,
                $oldValues,
                null,
                'Failed to toggle active (department_id='.$dept->id.'): '.$e->getMessage()
            );

            return response()->json(['success' => false, 'error' => 'Failed to toggle active'], 500);
        }
    }

    /**
     * DELETE /api/technical-assistant-preview-order/{department}
     */
    public function destroy(Request $request, string $department)
    {
        if (!$this->tableReady()) {
            $this->writeActivityLog($request, 'error', self::TABLE, null, null, null, null, 'Target table not found');
            return response()->json(['success' => false, 'error' => 'technical_assistant_preview_orders table not found'], 422);
        }

        if ($resp = $this->requireRole($request, ['admin','director','principal'])) {
            $this->writeActivityLog($request, 'unauthorized', self::TABLE, null, null, null, null, 'Unauthorized access attempt to delete preview order');
            return $resp;
        }

        $dept = $this->resolveDepartment($department);
        if (!$dept) {
            $this->writeActivityLog($request, 'error', self::TABLE, null, null, null, null, 'Department not found while deleting preview order');
            return response()->json(['success' => false, 'error' => 'Department not found'], 404);
        }

        $row = DB::table(self::TABLE)
            ->where('department_id', (int)$dept->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            $this->writeActivityLog(
                $request,
                'error',
                self::TABLE,
                null,
                null,
                null,
                null,
                'Order record not found for delete (department_id='.$dept->id.')'
            );
            return response()->json(['success' => false, 'error' => 'Order record not found'], 404);
        }

        $jsonCol = $this->technicalAssistantJsonCol();
        $activeCol = $this->activeCol();

        // Old snapshot
        $oldAssigned = $this->normalizeIds($this->toArray($row->{$jsonCol} ?? null));
        $oldActive = null;
        if ($activeCol) {
            $rawOld = $row->{$activeCol};
            if (is_numeric($rawOld)) $oldActive = ((int)$rawOld) === 1 ? 1 : 0;
            else {
                $sOld = strtolower(trim((string)$rawOld));
                $oldActive = ($sOld === 'active' || $sOld === '1' || $sOld === 'true') ? 1 : 0;
            }
        }

        $oldValues = [
            'department_id' => (int)$dept->id,
            'technical_assistant_ids' => $oldAssigned,
            'count' => count($oldAssigned),
            'active' => $activeCol ? (int)($oldActive ?? 1) : null,
        ];

        try {
            $now = Carbon::now();
            $newValues = null;
            $changed = null;

            if (Schema::hasColumn(self::TABLE, 'deleted_at')) {
                DB::table(self::TABLE)->where('id', $row->id)->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);

                $changed = ['deleted_at', 'updated_at'];
                $newValues = [
                    'deleted_at' => (string)$now,
                ];
            } else {
                DB::table(self::TABLE)->where('id', $row->id)->delete();
                $changed = ['delete'];
                $newValues = null;
            }

            $this->logWithActor('msit.technical_assistant_preview_order.destroy', $request, [
                'department_id' => (int)$dept->id,
                'row_id'        => (int)$row->id,
            ]);

            $this->writeActivityLog(
                $request,
                'delete',
                self::TABLE,
                (int)$row->id,
                $changed,
                $oldValues,
                $newValues,
                'Deleted technical assistant preview order (department_id='.$dept->id.', row_id='.$row->id.')'
            );

            return response()->json(['success' => true, 'message' => 'Order record removed']);

        } catch (\Throwable $e) {
            $this->logWithActor('msit.technical_assistant_preview_order.destroy_failed', $request, [
                'department_id' => (int)$dept->id,
                'row_id' => (int)$row->id,
                'error' => $e->getMessage(),
            ]);

            $this->writeActivityLog(
                $request,
                'error',
                self::TABLE,
                (int)$row->id,
                null,
                $oldValues,
                null,
                'Failed to delete technical assistant preview order (department_id='.$dept->id.', row_id='.$row->id.'): '.$e->getMessage()
            );

            return response()->json(['success' => false, 'error' => 'Failed to delete order record'], 500);
        }
    }

    // =====================================================
    // PUBLIC ENDPOINTS (NO AUTH) — for landing pages
    // =====================================================

    private function normalizeActive($raw): int
    {
        if ($raw === null) return 1;

        if (is_numeric($raw)) return ((int)$raw) === 1 ? 1 : 0;

        $s = strtolower(trim((string)$raw));
        return ($s === '1' || $s === 'true' || $s === 'active') ? 1 : 0;
    }

    /**
     * GET /api/public/technical-assistant-preview-order
     */
    public function publicIndex(Request $request)
    {
        if (!$this->tableReady()) {
            return response()->json(['success' => false, 'error' => 'technical_assistant_preview_orders table not found'], 422);
        }

        $activeCol = $this->activeCol();
        $jsonCol   = $this->technicalAssistantJsonCol();

        $rows = DB::table(self::TABLE . ' as tapo')
            ->leftJoin('departments as d', 'd.id', '=', 'tapo.department_id')
            ->whereNull('tapo.deleted_at')
            ->whereNull('d.deleted_at')
            ->select(array_filter([
                'tapo.id',
                Schema::hasColumn(self::TABLE, 'uuid') ? 'tapo.uuid' : null,
                'tapo.department_id',
                'd.uuid as department_uuid',
                'd.slug as department_slug',
                'd.title as department_title',
                $activeCol ? 'tapo.'.$activeCol.' as active_raw' : null,
                'tapo.'.$jsonCol.' as technical_assistant_user_ids_json',
                'tapo.updated_at',
            ]))
            ->orderBy('d.title', 'asc')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $ids = $this->normalizeIds($this->toArray($r->technical_assistant_user_ids_json ?? null));
            $active = $activeCol ? $this->normalizeActive($r->active_raw ?? null) : 1;

            // only ACTIVE + has some assigned ids
            if ($active !== 1) continue;
            if (count($ids) === 0) continue;

            $out[] = [
                'department' => [
                    'id'    => (int)($r->department_id ?? 0),
                    'uuid'  => (string)($r->department_uuid ?? ''),
                    'slug'  => (string)($r->department_slug ?? ''),
                    'title' => (string)($r->department_title ?? ''),
                ],
                'order' => [
                    'active'                    => 1,
                    'technical_assistant_count' => count($ids),
                ],
            ];
        }

        return response()->json(['success' => true, 'data' => $out]);
    }

    /**
     * GET /api/public/technical-assistant-preview-order/{department}
     */
    public function publicShow(Request $request, string $department)
    {
        if (!$this->tableReady()) {
            return response()->json(['success' => false, 'error' => 'technical_assistant_preview_orders table not found'], 422);
        }

        $dept = $this->resolveDepartment($department);
        if (!$dept) {
            return response()->json(['success' => false, 'error' => 'Department not found'], 404);
        }

        $statusFilter = strtolower(trim((string)$request->query('status', 'active'))) ?: 'active';
        if (!in_array($statusFilter, ['active','inactive','all'], true)) $statusFilter = 'active';

        $jsonCol   = $this->technicalAssistantJsonCol();
        $activeCol = $this->activeCol();

        $orderRow = DB::table(self::TABLE)
            ->where('department_id', (int)$dept->id)
            ->whereNull('deleted_at')
            ->first();

        $assignedIds = [];
        $activeVal = 1;

        if ($orderRow) {
            $assignedIds = $this->normalizeIds($this->toArray($orderRow->{$jsonCol} ?? null));
            $activeVal   = $activeCol ? $this->normalizeActive($orderRow->{$activeCol} ?? null) : 1;
        }

        // If no row / inactive / empty => empty (public-safe)
        if (!$orderRow || $activeVal !== 1 || empty($assignedIds)) {
            return response()->json([
                'success' => true,
                'department' => [
                    'id'    => (int)$dept->id,
                    'uuid'  => (string)($dept->uuid ?? ''),
                    'slug'  => (string)($dept->slug ?? ''),
                    'title' => (string)($dept->title ?? ''),
                ],
                'order' => [
                    'exists'                    => (bool)$orderRow,
                    'active'                    => (int)$activeVal,
                    'technical_assistant_ids'   => $assignedIds,
                    'technical_assistant_count' => count($assignedIds),
                ],
                'assigned' => [],
            ]);
        }

        // assigned users (eligible + ordered)
        $assignedRows = $this->eligibleUsersByIds((int)$dept->id, $assignedIds, $statusFilter);
        $assignedMap  = [];
        foreach ($assignedRows as $u) $assignedMap[(int)$u->id] = $u;

        $assignedOrdered = [];
        foreach ($assignedIds as $id) {
            if (isset($assignedMap[$id])) $assignedOrdered[] = $assignedMap[$id];
        }

        return response()->json([
            'success' => true,
            'department' => [
                'id'    => (int)$dept->id,
                'uuid'  => (string)($dept->uuid ?? ''),
                'slug'  => (string)($dept->slug ?? ''),
                'title' => (string)($dept->title ?? ''),
            ],
            'order' => [
                'exists'                    => true,
                'active'                    => 1,
                'technical_assistant_ids'   => $assignedIds,
                'technical_assistant_count' => count($assignedIds),
            ],
            'assigned' => $assignedOrdered,
        ]);
    }
}
