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

class PlacementOfficerPreviewOrderController extends Controller
{
    private const TABLE = 'placement_officer_preview_orders';

    // Exclude these roles when loading dept users (same pattern)
    private const EXCLUDED_ROLES = ['super_admin', 'admin', 'director', 'student', 'students'];

    // Placement officer role variants (safe multi-variants)
    private const PO_ROLES = ['placement_officer', 'placementofficer', 'placement-officer', 'po'];

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
     * Pick correct JSON column for stored placement officer IDs.
     */
    private function placementOfficerJsonCol(): string
    {
        $candidates = [
            'placement_officer_user_ids_json',   // your migration column
            'placement_officer_ids_json',
            'placement_officers_user_ids_json',
            'placement_officer_json',
            'placement_officers_json',
            'placement_officer_ids',
        ];

        foreach ($candidates as $c) {
            if (Schema::hasColumn(self::TABLE, $c)) return $c;
        }

        return 'placement_officer_user_ids_json';
    }

    /**
     * Pick active/status column (supports string status or 1/0)
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
     * Normalize active value to 1/0 from any stored form
     */
    private function normalizeActive($raw): int
    {
        if ($raw === null) return 1;

        if (is_numeric($raw)) return ((int)$raw) === 1 ? 1 : 0;

        $s = strtolower(trim((string)$raw));
        return ($s === '1' || $s === 'true' || $s === 'active') ? 1 : 0;
    }

    /**
     * Convert 1/0 -> DB value depending on column naming
     * - if column is "status" => 'active'/'inactive'
     * - else => 1/0
     */
    private function activeDbValue(string $activeCol, int $val)
    {
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
     * Eligible placement officers query for department
     * - dept mapping supported via users.department_id OR upi.department_id
     * - filters to placement officer roles only
     */
    private function eligibleUsersQuery(int $deptId, string $statusFilter = 'active')
    {
        $upiHasDept  = Schema::hasTable('user_personal_information') && Schema::hasColumn('user_personal_information', 'department_id');
        $userHasDept = Schema::hasColumn('users', 'department_id');

        $q = DB::table('users as u')
            ->leftJoin('user_personal_information as upi', 'upi.user_id', '=', 'u.id')
            ->whereNull('u.deleted_at')
            ->whereNotIn('u.role', self::EXCLUDED_ROLES)
            ->whereIn('u.role', self::PO_ROLES)
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

    /**
     * Fetch placement officers by ids but only if they are eligible for dept
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

    // =====================================================
    // API ENDPOINTS (ADMIN)
    // =====================================================

    /**
     * GET /api/placement-officer-preview-order
     */
    public function index(Request $request)
    {
        if (!$this->tableReady()) {
            return response()->json(['success' => false, 'error' => 'placement_officer_preview_orders table not found'], 422);
        }

        if ($resp = $this->requireRole($request, ['admin','director','principal','hod'])) return $resp;

        $activeCol = $this->activeCol();
        $jsonCol   = $this->placementOfficerJsonCol();

        $rows = DB::table(self::TABLE . ' as popo')
            ->leftJoin('departments as d', 'd.id', '=', 'popo.department_id')
            ->whereNull('popo.deleted_at')
            ->select(array_filter([
                'popo.id',
                Schema::hasColumn(self::TABLE, 'uuid') ? 'popo.uuid' : null,
                'popo.department_id',
                'd.uuid as department_uuid',
                'd.slug as department_slug',
                'd.title as department_title',
                $activeCol ? 'popo.'.$activeCol.' as active_raw' : null,
                'popo.'.$jsonCol.' as placement_officer_user_ids_json',
                'popo.created_at',
                'popo.updated_at',
            ]))
            ->orderBy('popo.id', 'desc')
            ->get();

        $rows->each(function ($r) use ($activeCol) {
            $arr = is_string($r->placement_officer_user_ids_json ?? null)
                ? json_decode($r->placement_officer_user_ids_json, true)
                : ($r->placement_officer_user_ids_json ?? []);
            $r->placement_officer_count = is_array($arr) ? count($arr) : 0;
            $r->active = $activeCol ? $this->normalizeActive($r->active_raw ?? null) : 1;
        });

        return response()->json(['success' => true, 'data' => $rows]);
    }

    /**
     * GET /api/placement-officer-preview-order/{department}
     */
    public function show(Request $request, string $department)
    {
        if (!$this->tableReady()) {
            return response()->json(['success' => false, 'error' => 'placement_officer_preview_orders table not found'], 422);
        }

        if ($resp = $this->requireRole($request, ['admin','director','principal','hod'])) return $resp;

        $dept = $this->resolveDepartment($department);
        if (!$dept) {
            return response()->json(['success' => false, 'error' => 'Department not found'], 404);
        }

        $statusFilter = strtolower(trim((string)$request->query('status', 'active'))) ?: 'active';
        if (!in_array($statusFilter, ['active','inactive','all'], true)) $statusFilter = 'active';

        $jsonCol   = $this->placementOfficerJsonCol();
        $activeCol = $this->activeCol();

        $orderRow = DB::table(self::TABLE)
            ->where('department_id', (int)$dept->id)
            ->whereNull('deleted_at')
            ->first();

        $assignedIds = [];
        $activeVal   = 1;

        if ($orderRow) {
            $assignedIds = $this->normalizeIds($this->toArray($orderRow->{$jsonCol} ?? null));
            if ($activeCol) $activeVal = $this->normalizeActive($orderRow->{$activeCol} ?? null);
        }

        // assigned users (eligible + reorder in PHP)
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
                'exists'                 => (bool)$orderRow,
                'active'                 => (int)$activeVal,
                'placement_officer_ids'  => $assignedIds,
                'placement_officer_count'=> count($assignedIds),
            ],
            'assigned'   => $assignedOrdered,
            'unassigned' => $unassigned,
        ]);
    }

    /**
     * POST /api/placement-officer-preview-order/{department}/save
     * Body:
     * {
     *   "placement_officer_ids": [12,5,9],
     *   "active": 1
     * }
     */
    public function save(Request $request, string $department)
    {
        if (!$this->tableReady()) {
            return response()->json(['success' => false, 'error' => 'placement_officer_preview_orders table not found'], 422);
        }

        if ($resp = $this->requireRole($request, ['admin','director','principal','hod'])) return $resp;

        $dept = $this->resolveDepartment($department);
        if (!$dept) {
            return response()->json(['success' => false, 'error' => 'Department not found'], 404);
        }

        $v = Validator::make($request->all(), [
            'placement_officer_ids' => ['required', 'array'],
            'placement_officer_ids.*' => ['required', 'integer', 'min:1'],
            'active' => ['nullable'],
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $data = $v->validated();
        $ids  = $this->normalizeIds($data['placement_officer_ids'] ?? []);

        // Validate: IDs must be eligible placement officers for this dept
        $eligible = $this->eligibleUsersByIds((int)$dept->id, $ids, 'all');
        $eligibleIds = $eligible->pluck('id')->map(fn($x) => (int)$x)->values()->all();
        $eligibleSet = array_fill_keys($eligibleIds, true);

        $final = [];
        foreach ($ids as $id) {
            if (isset($eligibleSet[$id])) $final[] = $id;
        }

        $activeCol = $this->activeCol();
        $activeInt = null;

        if ($activeCol) {
            $raw = $request->input('active', 1);
            if (is_string($raw)) {
                $raw = strtolower(trim($raw));
                $activeInt = ($raw === '1' || $raw === 'true' || $raw === 'active') ? 1 : 0;
            } else {
                $activeInt = ((int)$raw) === 1 ? 1 : 0;
            }
        }

        $jsonCol = $this->placementOfficerJsonCol();
        $now     = Carbon::now();
        $actor   = $this->actor($request);

        DB::beginTransaction();
        try {
            $existing = DB::table(self::TABLE)
                ->where('department_id', (int)$dept->id)
                ->whereNull('deleted_at')
                ->first();

            $payload = [
                $jsonCol      => json_encode($final),
                'updated_at'  => $now,
            ];

            if (Schema::hasColumn(self::TABLE, 'updated_at_ip')) {
                $payload['updated_at_ip'] = $request->ip();
            }

            if ($activeCol && $activeInt !== null) {
                $payload[$activeCol] = $this->activeDbValue($activeCol, $activeInt);
            }

            if (Schema::hasColumn(self::TABLE, 'updated_by')) {
                $payload['updated_by'] = $actor['id'] ?: null;
            }

            if ($existing) {
                DB::table(self::TABLE)->where('id', $existing->id)->update($payload);
                $rowId = (int)$existing->id;
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

            $this->logWithActor('msit.placement_officer_preview_order.save', $request, [
                'department_id' => (int)$dept->id,
                'row_id' => $rowId,
                'count' => count($final),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Placement officer preview order saved',
                'data' => [
                    'department_id'          => (int)$dept->id,
                    'placement_officer_ids'  => $final,
                    'count'                  => count($final),
                    'active'                 => $activeCol ? (int)($activeInt ?? 1) : null,
                ],
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            $this->logWithActor('msit.placement_officer_preview_order.save_failed', $request, [
                'department_id' => (int)$dept->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'error' => 'Failed to save order'], 500);
        }
    }

    /**
     * POST /api/placement-officer-preview-order/{department}/toggle-active
     * Body: { "active": 1 } or { "active": 0 }
     */
    public function toggleActive(Request $request, string $department)
    {
        if (!$this->tableReady()) {
            return response()->json(['success' => false, 'error' => 'placement_officer_preview_orders table not found'], 422);
        }

        if ($resp = $this->requireRole($request, ['admin','director','principal','hod'])) return $resp;

        $activeCol = $this->activeCol();
        if (!$activeCol) {
            return response()->json(['success' => false, 'error' => 'No active/status column found in placement_officer_preview_orders'], 422);
        }

        $dept = $this->resolveDepartment($department);
        if (!$dept) return response()->json(['success' => false, 'error' => 'Department not found'], 404);

        $v = Validator::make($request->all(), [
            'active' => ['required'],
        ]);
        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

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

        $now = Carbon::now();

        if (!$existing) {
            $insert = [
                'department_id' => (int)$dept->id,
                $this->placementOfficerJsonCol() => json_encode([]),
                $activeCol => $this->activeDbValue($activeCol, $val),
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (Schema::hasColumn(self::TABLE, 'uuid')) $insert['uuid'] = (string) Str::uuid();
            if (Schema::hasColumn(self::TABLE, 'created_at_ip')) $insert['created_at_ip'] = $request->ip();
            if (Schema::hasColumn(self::TABLE, 'updated_at_ip')) $insert['updated_at_ip'] = $request->ip();
            DB::table(self::TABLE)->insert($insert);
        } else {
            $upd = [
                $activeCol => $this->activeDbValue($activeCol, $val),
                'updated_at' => $now,
            ];
            if (Schema::hasColumn(self::TABLE, 'updated_at_ip')) $upd['updated_at_ip'] = $request->ip();

            DB::table(self::TABLE)->where('id', $existing->id)->update($upd);
        }

        $this->logWithActor('msit.placement_officer_preview_order.toggle_active', $request, [
            'department_id' => (int)$dept->id,
            'active' => $val,
        ]);

        return response()->json(['success' => true, 'active' => $val]);
    }

    /**
     * DELETE /api/placement-officer-preview-order/{department}
     * Soft delete if deleted_at exists, else hard delete.
     */
    public function destroy(Request $request, string $department)
    {
        if (!$this->tableReady()) {
            return response()->json(['success' => false, 'error' => 'placement_officer_preview_orders table not found'], 422);
        }

        if ($resp = $this->requireRole($request, ['admin','director','principal'])) return $resp;

        $dept = $this->resolveDepartment($department);
        if (!$dept) return response()->json(['success' => false, 'error' => 'Department not found'], 404);

        $row = DB::table(self::TABLE)
            ->where('department_id', (int)$dept->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            return response()->json(['success' => false, 'error' => 'Order record not found'], 404);
        }

        $now = Carbon::now();

        if (Schema::hasColumn(self::TABLE, 'deleted_at')) {
            $upd = [
                'deleted_at' => $now,
                'updated_at' => $now,
            ];
            if (Schema::hasColumn(self::TABLE, 'updated_at_ip')) $upd['updated_at_ip'] = $request->ip();

            DB::table(self::TABLE)->where('id', $row->id)->update($upd);
        } else {
            DB::table(self::TABLE)->where('id', $row->id)->delete();
        }

        $this->logWithActor('msit.placement_officer_preview_order.destroy', $request, [
            'department_id' => (int)$dept->id,
            'row_id' => (int)$row->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Order record removed']);
    }

    // =====================================================
    // PUBLIC ENDPOINTS (NO AUTH) — for landing pages
    // =====================================================

    /**
     * GET /api/public/placement-officer-preview-order
     * Returns departments that have ACTIVE row + count > 0
     */
    public function publicIndex(Request $request)
    {
        if (!$this->tableReady()) {
            return response()->json(['success' => false, 'error' => 'placement_officer_preview_orders table not found'], 422);
        }

        $activeCol = $this->activeCol();
        $jsonCol   = $this->placementOfficerJsonCol();

        $rows = DB::table(self::TABLE . ' as popo')
            ->leftJoin('departments as d', 'd.id', '=', 'popo.department_id')
            ->whereNull('popo.deleted_at')
            ->whereNull('d.deleted_at')
            ->select(array_filter([
                'popo.id',
                Schema::hasColumn(self::TABLE, 'uuid') ? 'popo.uuid' : null,
                'popo.department_id',
                'd.uuid as department_uuid',
                'd.slug as department_slug',
                'd.title as department_title',
                $activeCol ? 'popo.'.$activeCol.' as active_raw' : null,
                'popo.'.$jsonCol.' as placement_officer_user_ids_json',
                'popo.updated_at',
            ]))
            ->orderBy('d.title', 'asc')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $ids = $this->normalizeIds($this->toArray($r->placement_officer_user_ids_json ?? null));
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
                    'active'                  => 1,
                    'placement_officer_count' => count($ids),
                ],
            ];
        }

        return response()->json(['success' => true, 'data' => $out]);
    }

    /**
     * GET /api/public/placement-officer-preview-order/{department}
     * Public: returns ONLY assigned users (ordered) + ids
     */
    public function publicShow(Request $request, string $department)
    {
        if (!$this->tableReady()) {
            return response()->json(['success' => false, 'error' => 'placement_officer_preview_orders table not found'], 422);
        }

        $dept = $this->resolveDepartment($department);
        if (!$dept) {
            return response()->json(['success' => false, 'error' => 'Department not found'], 404);
        }

        $statusFilter = strtolower(trim((string)$request->query('status', 'active'))) ?: 'active';
        if (!in_array($statusFilter, ['active','inactive','all'], true)) $statusFilter = 'active';

        $jsonCol   = $this->placementOfficerJsonCol();
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
                    'exists'                 => (bool)$orderRow,
                    'active'                 => (int)$activeVal,
                    'placement_officer_ids'  => $assignedIds,
                    'placement_officer_count'=> count($assignedIds),
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
                'exists'                 => true,
                'active'                 => 1,
                'placement_officer_ids'  => $assignedIds,
                'placement_officer_count'=> count($assignedIds),
            ],
            'assigned' => $assignedOrdered,
        ]);
    }
}
