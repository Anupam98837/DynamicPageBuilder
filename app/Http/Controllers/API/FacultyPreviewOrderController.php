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

class FacultyPreviewOrderController extends Controller
{
    private const TABLE = 'faculty_preview_orders';

    // Exclude these roles when loading dept users
    private const EXCLUDED_ROLES = ['super_admin', 'admin', 'director', 'student', 'students'];

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

            // ✅ Audit unauthorized attempts for non-GET only
            if (strtoupper($r->method()) !== 'GET') {
                $this->writeActivityLog(
                    $r,
                    'unauthorized',
                    'faculty_preview_orders',
                    self::TABLE,
                    null,
                    ['role'],
                    ['attempted_role' => $a['role']],
                    ['allowed_roles' => $allowed, 'path' => $r->path(), 'method' => $r->method()],
                    'Unauthorized access attempt'
                );
            }

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

    // =========================
    // Activity Log (DB) helpers
    // =========================
    private function activityLogReady(): bool
    {
        return Schema::hasTable('user_data_activity_log');
    }

    private function writeActivityLog(
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
        if (!$this->activityLogReady()) return;

        $a = $this->actor($r);

        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'       => (int)($a['id'] ?? 0),
                'performed_by_role'  => (string)($a['role'] ?? ''),
                'ip'                 => (string)($r->ip() ?? ''),
                'user_agent'         => (string)($r->userAgent() ?? ''),

                'activity'           => (string)$activity,
                'module'             => (string)$module,

                'table_name'         => (string)$tableName,
                'record_id'          => $recordId !== null ? (int)$recordId : null,

                'changed_fields'     => $changedFields !== null ? json_encode($changedFields) : null,
                'old_values'         => $oldValues !== null ? json_encode($oldValues) : null,
                'new_values'         => $newValues !== null ? json_encode($newValues) : null,

                'log_note'           => $note,

                'created_at'         => Carbon::now(),
                'updated_at'         => Carbon::now(),
            ]);
        } catch (\Throwable $e) {
            // ✅ Never break APIs due to logging failure
            Log::warning('msit.activity_log.insert_failed', [
                'error' => $e->getMessage(),
                'activity' => $activity,
                'module' => $module,
                'table_name' => $tableName,
                'record_id' => $recordId,
            ]);
        }
    }

    private function isUuid(string $v): bool
    {
        return (bool) preg_match('/^[0-9a-fA-F-]{36}$/', $v);
    }

    private function tableReady(): bool
    {
        return Schema::hasTable(self::TABLE);
    }

    /**
     * Resolve department by id|uuid|slug (like your other controllers)
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
     * Resolve department using route param first, then request body fallbacks.
     * This makes save/toggle/destroy compatible with clients that send department_id/uuid/slug in payload.
     */
    private function resolveDepartmentFromRequest(Request $request, ?string $routeDepartment = null)
    {
        $candidates = [];

        if ($routeDepartment !== null && trim((string)$routeDepartment) !== '') {
            $candidates[] = trim((string)$routeDepartment);
        }

        foreach (['department', 'department_id', 'department_uuid', 'department_slug'] as $key) {
            $val = $request->input($key);
            if ($val !== null && trim((string)$val) !== '') {
                $candidates[] = trim((string)$val);
            }
        }

        foreach ($candidates as $identifier) {
            $dept = $this->resolveDepartment((string)$identifier);
            if ($dept) return $dept;
        }

        return null;
    }

    /**
     * Pick the correct JSON column name for stored faculty IDs.
     * (Supports small naming differences without breaking.)
     */
    private function facultyJsonCol(): string
    {
        $candidates = [
            'faculty_user_ids_json',
            'faculty_json',
            'faculty_ids',
            'faculties_json',
        ];

        foreach ($candidates as $c) {
            if (Schema::hasColumn(self::TABLE, $c)) return $c;
        }

        // default
        return 'faculty_user_ids_json';
    }

    /**
     * Pick active/status column (supports 1/0 storage)
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

    /**
     * Eligible users query for department (supports dept mapping in either users.department_id or upi.department_id)
     */
    private function eligibleUsersQuery(int $deptId, string $statusFilter = 'active')
    {
        $upiHasDept  = Schema::hasTable('user_personal_information') && Schema::hasColumn('user_personal_information', 'department_id');
        $userHasDept = Schema::hasColumn('users', 'department_id');

        $q = DB::table('users as u')
            ->leftJoin('user_personal_information as upi', 'upi.user_id', '=', 'u.id')
            ->whereNull('u.deleted_at')
            ->whereNotIn('u.role', self::EXCLUDED_ROLES)
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

            // if neither exists, force empty
            if (!$applied) {
                $w->whereRaw('1=0');
            }
        });

        return $q;
    }

    /**
     * Fetch users by ids but only if they are eligible for dept
     */
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

    // =====================================================
    // API ENDPOINTS
    // =====================================================

    /**
     * GET /api/faculty-preview-order
     * List department -> order record (simple admin list)
     */
    public function index(Request $request)
    {
        if (!$this->tableReady()) {
            return response()->json(['success' => false, 'error' => 'faculty_preview_orders table not found'], 422);
        }

        if ($resp = $this->requireRole($request, ['admin','director','principal','hod'])) return $resp;

        $activeCol = $this->activeCol();
        $jsonCol   = $this->facultyJsonCol();

        $rows = DB::table(self::TABLE . ' as fpo')
            ->leftJoin('departments as d', 'd.id', '=', 'fpo.department_id')
            ->whereNull('fpo.deleted_at')
            ->select(array_filter([
                'fpo.id',
                Schema::hasColumn(self::TABLE, 'uuid') ? 'fpo.uuid' : null,
                'fpo.department_id',
                'd.uuid as department_uuid',
                'd.slug as department_slug',
                'd.title as department_title',
                $activeCol ? 'fpo.'.$activeCol.' as active' : null,
                'fpo.'.$jsonCol.' as faculty_user_ids_json',
                'fpo.created_at',
                'fpo.updated_at',
            ]))
            ->orderBy('fpo.id', 'desc')
            ->get();

        $rows->each(function ($r) {
            $arr = is_string($r->faculty_user_ids_json ?? null)
                ? json_decode($r->faculty_user_ids_json, true)
                : ($r->faculty_user_ids_json ?? []);
            $r->faculty_count = is_array($arr) ? count($arr) : 0;
        });

        return response()->json(['success' => true, 'data' => $rows]);
    }

    /**
     * GET /api/faculty-preview-order/{department}
     * Returns:
     * - department
     * - order record (if exists)
     * - assigned users (ordered)
     * - unassigned users
     */
    public function show(Request $request, string $department)
    {
        if (!$this->tableReady()) {
            return response()->json(['success' => false, 'error' => 'faculty_preview_orders table not found'], 422);
        }

        if ($resp = $this->requireRole($request, ['admin','director','principal','hod'])) return $resp;

        $dept = $this->resolveDepartment($department);
        if (!$dept) {
            return response()->json(['success' => false, 'error' => 'Department not found'], 404);
        }

        $statusFilter = strtolower(trim((string)$request->query('status', 'active'))) ?: 'active';
        if (!in_array($statusFilter, ['active','inactive','all'], true)) $statusFilter = 'active';

        $jsonCol   = $this->facultyJsonCol();
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
                $activeVal = is_numeric($raw) ? (int)$raw : (($raw === 'active') ? 1 : 0);
            }
        }

        // assigned users (filter to eligible + reorder in PHP)
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
                'exists'        => (bool)$orderRow,
                'active'        => (int)$activeVal,
                'faculty_ids'   => $assignedIds,
                'faculty_count' => count($assignedIds),
            ],
            'assigned'   => $assignedOrdered,
            'unassigned' => $unassigned,
        ]);
    }

    /**
     * POST /api/faculty-preview-order/{department}/save
     * Body:
     * {
     *   "faculty_ids": [12,5,9],   // ordered ids (can be [])
     *   "active": 1,               // optional (1/0)
     *   "department": "cse"        // optional fallback if route param missing/empty
     * }
     */
    public function save(Request $request, string $department)
    {
        if (!$this->tableReady()) {
            $this->writeActivityLog(
                $request,
                'error',
                'faculty_preview_orders',
                self::TABLE,
                null,
                null,
                null,
                ['error' => 'faculty_preview_orders table not found'],
                'Save failed: table not found'
            );

            return response()->json(['success' => false, 'error' => 'faculty_preview_orders table not found'], 422);
        }

        if ($resp = $this->requireRole($request, ['admin','director','principal','hod'])) return $resp;

        // ✅ route param first, then request fallbacks (department, department_id, department_uuid, department_slug)
        $dept = $this->resolveDepartmentFromRequest($request, $department);
        if (!$dept) {
            $this->writeActivityLog(
                $request,
                'not_found',
                'faculty_preview_orders',
                self::TABLE,
                null,
                ['department'],
                null,
                [
                    'route_department' => $department,
                    'department' => $request->input('department'),
                    'department_id' => $request->input('department_id'),
                    'department_uuid' => $request->input('department_uuid'),
                    'department_slug' => $request->input('department_slug'),
                ],
                'Save failed: department not found'
            );

            return response()->json(['success' => false, 'error' => 'Department not found'], 404);
        }

        // ✅ "present|array" allows [] when everything is unassigned
        $v = Validator::make($request->all(), [
            'faculty_ids' => ['present', 'array'],
            'faculty_ids.*' => ['integer', 'min:1'],

            // Optional fallback keys for compatibility with clients sending department in body
            'department'      => ['nullable', 'string'],
            'department_id'   => ['nullable'],
            'department_uuid' => ['nullable', 'string'],
            'department_slug' => ['nullable', 'string'],

            'active' => ['nullable'],
        ]);

        if ($v->fails()) {
            $this->writeActivityLog(
                $request,
                'validation_failed',
                'faculty_preview_orders',
                self::TABLE,
                null,
                ['faculty_ids', 'active', 'department', 'department_id', 'department_uuid', 'department_slug'],
                null,
                ['errors' => $v->errors()->toArray(), 'department_id' => (int)$dept->id],
                'Save failed: validation errors'
            );

            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $data = $v->validated();
        $facultyIds = $this->normalizeIds($data['faculty_ids'] ?? []);

        // Ensure all provided IDs belong to eligible users for this dept (and not excluded roles)
        $eligible = $this->eligibleUsersByIds((int)$dept->id, $facultyIds, 'all');
        $eligibleIds = $eligible->pluck('id')->map(fn($x) => (int)$x)->values()->all();
        $eligibleSet = array_fill_keys($eligibleIds, true);

        $final = [];
        foreach ($facultyIds as $id) {
            if (isset($eligibleSet[$id])) $final[] = $id;
        }

        // Active normalization (1/0)
        $activeCol = $this->activeCol();
        $activeVal = null;
        if ($activeCol) {
            $raw = $request->input('active', 1);
            if (is_string($raw)) {
                $raw = strtolower(trim($raw));
                $activeVal = ($raw === '1' || $raw === 'true' || $raw === 'active') ? 1 : 0;
            } else {
                $activeVal = ((int)$raw) === 1 ? 1 : 0;
            }
        }

        $jsonCol = $this->facultyJsonCol();
        $now     = Carbon::now();
        $actor   = $this->actor($request);

        DB::beginTransaction();
        try {
            $existing = DB::table(self::TABLE)
                ->where('department_id', (int)$dept->id)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();

            $oldValues = null;
            $oldActive = null;
            $oldJson   = null;

            if ($existing) {
                $oldJson = $this->toArray($existing->{$jsonCol} ?? null);
                if ($activeCol) $oldActive = $this->normalizeActive($existing->{$activeCol} ?? null);

                $oldValues = [
                    'department_id' => (int)$dept->id,
                    $jsonCol => $oldJson,
                ];
                if ($activeCol) $oldValues[$activeCol] = $oldActive;
            }

            $payload = [
                $jsonCol      => json_encode($final),
                'updated_at'  => $now,
            ];

            if ($activeCol && $activeVal !== null) {
                $payload[$activeCol] = $activeVal;
            }

            // optional audit columns
            if (Schema::hasColumn(self::TABLE, 'updated_by')) {
                $payload['updated_by'] = $actor['id'] ?: null;
            }

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
                $action = 'create';
            }

            DB::commit();

            $changed = [$jsonCol];
            if ($activeCol) $changed[] = $activeCol;

            $newValues = [
                'department_id' => (int)$dept->id,
                $jsonCol => $final,
            ];
            if ($activeCol) $newValues[$activeCol] = (int)($activeVal ?? 1);

            $this->writeActivityLog(
                $request,
                $action,
                'faculty_preview_orders',
                self::TABLE,
                $rowId,
                $changed,
                $oldValues,
                $newValues,
                $action === 'create'
                    ? 'Created faculty preview order'
                    : 'Updated faculty preview order'
            );

            $this->logWithActor('msit.faculty_preview_order.save', $request, [
                'department_id' => (int)$dept->id,
                'row_id' => $rowId,
                'count' => count($final),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Faculty preview order saved',
                'data' => [
                    'department_id' => (int)$dept->id,
                    'faculty_ids'   => $final,
                    'count'         => count($final),
                    'active'        => $activeCol ? (int)($activeVal ?? 1) : null,
                ],
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            $this->writeActivityLog(
                $request,
                'error',
                'faculty_preview_orders',
                self::TABLE,
                null,
                null,
                null,
                ['error' => $e->getMessage(), 'department_id' => (int)$dept->id],
                'Save failed: exception'
            );

            $this->logWithActor('msit.faculty_preview_order.save_failed', $request, [
                'department_id' => (int)$dept->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'error' => 'Failed to save order'], 500);
        }
    }

    /**
     * POST /api/faculty-preview-order/{department}/toggle-active
     * Body: { "active": 1 } or { "active": 0 }
     */
    public function toggleActive(Request $request, string $department)
    {
        if (!$this->tableReady()) {
            $this->writeActivityLog(
                $request,
                'error',
                'faculty_preview_orders',
                self::TABLE,
                null,
                null,
                null,
                ['error' => 'faculty_preview_orders table not found'],
                'Toggle active failed: table not found'
            );

            return response()->json(['success' => false, 'error' => 'faculty_preview_orders table not found'], 422);
        }

        if ($resp = $this->requireRole($request, ['admin','director','principal','hod'])) return $resp;

        $activeCol = $this->activeCol();
        if (!$activeCol) {
            $this->writeActivityLog(
                $request,
                'error',
                'faculty_preview_orders',
                self::TABLE,
                null,
                ['active'],
                null,
                ['error' => 'No active/status column found'],
                'Toggle active failed: active/status column missing'
            );

            return response()->json(['success' => false, 'error' => 'No active/status column found in faculty_preview_orders'], 422);
        }

        // ✅ route param first, body fallback supported
        $dept = $this->resolveDepartmentFromRequest($request, $department);
        if (!$dept) {
            $this->writeActivityLog(
                $request,
                'not_found',
                'faculty_preview_orders',
                self::TABLE,
                null,
                ['department'],
                null,
                [
                    'route_department' => $department,
                    'department' => $request->input('department'),
                    'department_id' => $request->input('department_id'),
                    'department_uuid' => $request->input('department_uuid'),
                    'department_slug' => $request->input('department_slug'),
                ],
                'Toggle active failed: department not found'
            );

            return response()->json(['success' => false, 'error' => 'Department not found'], 404);
        }

        $v = Validator::make($request->all(), [
            'active' => ['required'],
        ]);
        if ($v->fails()) {
            $this->writeActivityLog(
                $request,
                'validation_failed',
                'faculty_preview_orders',
                self::TABLE,
                null,
                ['active'],
                null,
                ['errors' => $v->errors()->toArray(), 'department_id' => (int)$dept->id],
                'Toggle active failed: validation errors'
            );

            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $raw = $request->input('active');
        $val = 0;
        if (is_string($raw)) {
            $raw = strtolower(trim($raw));
            $val = ($raw === '1' || $raw === 'true' || $raw === 'active') ? 1 : 0;
        } else {
            $val = ((int)$raw) === 1 ? 1 : 0;
        }

        $existing = DB::table(self::TABLE)
            ->where('department_id', (int)$dept->id)
            ->whereNull('deleted_at')
            ->first();

        $jsonCol = $this->facultyJsonCol();
        $oldValues = null;
        $newValues = null;
        $rowId = null;
        $action = 'update';

        if (!$existing) {
            // If no row exists yet, create one with empty array
            $now = Carbon::now();
            $insert = [
                'department_id' => (int)$dept->id,
                $jsonCol => json_encode([]),
                $activeCol => $val,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (Schema::hasColumn(self::TABLE, 'uuid')) $insert['uuid'] = (string) Str::uuid();

            $rowId = (int) DB::table(self::TABLE)->insertGetId($insert);
            $action = 'create';

            $oldValues = null;
            $newValues = [
                'department_id' => (int)$dept->id,
                $jsonCol => [],
                $activeCol => $val,
            ];
        } else {
            $rowId = (int)$existing->id;

            $oldValues = [
                'department_id' => (int)$dept->id,
                $jsonCol => $this->toArray($existing->{$jsonCol} ?? null),
                $activeCol => $this->normalizeActive($existing->{$activeCol} ?? null),
            ];

            DB::table(self::TABLE)->where('id', $existing->id)->update([
                $activeCol => $val,
                'updated_at' => Carbon::now(),
            ]);

            $newValues = [
                'department_id' => (int)$dept->id,
                $jsonCol => $oldValues[$jsonCol] ?? [],
                $activeCol => $val,
            ];
        }

        $this->writeActivityLog(
            $request,
            $action === 'create' ? 'create' : 'update',
            'faculty_preview_orders',
            self::TABLE,
            $rowId,
            [$activeCol],
            $oldValues,
            $newValues,
            'Toggled active status'
        );

        $this->logWithActor('msit.faculty_preview_order.toggle_active', $request, [
            'department_id' => (int)$dept->id,
            'active' => $val,
        ]);

        return response()->json(['success' => true, 'active' => $val]);
    }

    /**
     * DELETE /api/faculty-preview-order/{department}
     * Soft delete if deleted_at exists, else hard delete.
     */
    public function destroy(Request $request, string $department)
    {
        if (!$this->tableReady()) {
            $this->writeActivityLog(
                $request,
                'error',
                'faculty_preview_orders',
                self::TABLE,
                null,
                null,
                null,
                ['error' => 'faculty_preview_orders table not found'],
                'Destroy failed: table not found'
            );

            return response()->json(['success' => false, 'error' => 'faculty_preview_orders table not found'], 422);
        }

        if ($resp = $this->requireRole($request, ['admin','director','principal'])) return $resp;

        // ✅ route param first, body fallback supported
        $dept = $this->resolveDepartmentFromRequest($request, $department);
        if (!$dept) {
            $this->writeActivityLog(
                $request,
                'not_found',
                'faculty_preview_orders',
                self::TABLE,
                null,
                ['department'],
                null,
                [
                    'route_department' => $department,
                    'department' => $request->input('department'),
                    'department_id' => $request->input('department_id'),
                    'department_uuid' => $request->input('department_uuid'),
                    'department_slug' => $request->input('department_slug'),
                ],
                'Destroy failed: department not found'
            );

            return response()->json(['success' => false, 'error' => 'Department not found'], 404);
        }

        $row = DB::table(self::TABLE)
            ->where('department_id', (int)$dept->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            $this->writeActivityLog(
                $request,
                'not_found',
                'faculty_preview_orders',
                self::TABLE,
                null,
                ['record'],
                null,
                ['department_id' => (int)$dept->id],
                'Destroy failed: order record not found'
            );

            return response()->json(['success' => false, 'error' => 'Order record not found'], 404);
        }

        $jsonCol   = $this->facultyJsonCol();
        $activeCol = $this->activeCol();

        $oldValues = [
            'department_id' => (int)$dept->id,
            'id' => (int)$row->id,
            $jsonCol => $this->toArray($row->{$jsonCol} ?? null),
        ];
        if ($activeCol) $oldValues[$activeCol] = $this->normalizeActive($row->{$activeCol} ?? null);

        $newValues = null;
        $changedFields = [];

        if (Schema::hasColumn(self::TABLE, 'deleted_at')) {
            $ts = Carbon::now();
            DB::table(self::TABLE)->where('id', $row->id)->update([
                'deleted_at' => $ts,
                'updated_at' => $ts,
            ]);

            $changedFields = ['deleted_at'];
            $newValues = [
                'department_id' => (int)$dept->id,
                'id' => (int)$row->id,
                'deleted_at' => (string)$ts,
            ];
        } else {
            DB::table(self::TABLE)->where('id', $row->id)->delete();

            $changedFields = ['deleted'];
            $newValues = [
                'department_id' => (int)$dept->id,
                'id' => (int)$row->id,
                'deleted' => true,
            ];
        }

        $this->writeActivityLog(
            $request,
            'delete',
            'faculty_preview_orders',
            self::TABLE,
            (int)$row->id,
            $changedFields,
            $oldValues,
            $newValues,
            'Deleted faculty preview order'
        );

        $this->logWithActor('msit.faculty_preview_order.destroy', $request, [
            'department_id' => (int)$dept->id,
            'row_id' => (int)$row->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Order record removed']);
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
     * GET /api/public/faculty-preview-order
     * Returns departments that have an ACTIVE order row + faculty ids count
     */
    public function publicIndex(Request $request)
    {
        if (!$this->tableReady()) {
            return response()->json(['success' => false, 'error' => 'faculty_preview_orders table not found'], 422);
        }

        $activeCol = $this->activeCol();
        $jsonCol   = $this->facultyJsonCol();

        $rows = DB::table(self::TABLE . ' as fpo')
            ->leftJoin('departments as d', 'd.id', '=', 'fpo.department_id')
            ->whereNull('fpo.deleted_at')
            ->whereNull('d.deleted_at')
            ->select(array_filter([
                'fpo.id',
                Schema::hasColumn(self::TABLE, 'uuid') ? 'fpo.uuid' : null,
                'fpo.department_id',
                'd.uuid as department_uuid',
                'd.slug as department_slug',
                'd.title as department_title',
                $activeCol ? 'fpo.'.$activeCol.' as active_raw' : null,
                'fpo.'.$jsonCol.' as faculty_user_ids_json',
                'fpo.updated_at',
            ]))
            ->orderBy('d.title', 'asc')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $ids = $this->normalizeIds($this->toArray($r->faculty_user_ids_json ?? null));
            $active = $activeCol ? $this->normalizeActive($r->active_raw ?? null) : 1;

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
                    'active'        => 1,
                    'faculty_count' => count($ids),
                ],
            ];
        }

        return response()->json(['success' => true, 'data' => $out]);
    }

    /**
     * GET /api/public/faculty-preview-order/{department}
     * Public: returns ONLY assigned users (ordered) + order ids
     */
    public function publicShow(Request $request, string $department)
    {
        if (!$this->tableReady()) {
            return response()->json(['success' => false, 'error' => 'faculty_preview_orders table not found'], 422);
        }

        $dept = $this->resolveDepartment($department);
        if (!$dept) {
            return response()->json(['success' => false, 'error' => 'Department not found'], 404);
        }

        $statusFilter = strtolower(trim((string)$request->query('status', 'active'))) ?: 'active';
        if (!in_array($statusFilter, ['active','inactive','all'], true)) $statusFilter = 'active';

        $jsonCol   = $this->facultyJsonCol();
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

        // ✅ If no row / inactive / empty => return empty assigned (public-safe)
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
                    'exists'        => (bool)$orderRow,
                    'active'        => (int)$activeVal,
                    'faculty_ids'   => $assignedIds,
                    'faculty_count' => count($assignedIds),
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
                'exists'        => true,
                'active'        => 1,
                'faculty_ids'   => $assignedIds,
                'faculty_count' => count($assignedIds),
            ],
            'assigned' => $assignedOrdered,
        ]);
    }
}