<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class DepartmentEnquirySettingsController extends Controller
{
    private string $deptTable     = 'departments';
    private string $settingsTable = 'department_enquiry_settings';
    private string $logModule     = 'department_enquiry_settings';

    /**
     * Normalize actor information from request (compatible with your pattern)
     */
    private function actor(Request $r): array
    {
        return [
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? optional($r->user())->id ?? 0),
            'role' => (string) ($r->attributes->get('auth_role') ?? ($r->user()->role ?? '')),
            'type' => (string) ($r->attributes->get('auth_tokenable_type') ?? ($r->user() ? get_class($r->user()) : '')),
            'uuid' => (string) ($r->attributes->get('auth_user_uuid') ?? ($r->user()->uuid ?? '')),
        ];
    }

    /* =========================
     * Activity Log helpers (user_data_activity_log)
     * ========================= */

    private function normalizeForJson($value)
    {
        if ($value === null) return null;

        if (is_object($value) || is_array($value)) {
            $arr = json_decode(json_encode($value), true);
            return $arr === null ? (array) $value : $arr;
        }

        return $value;
    }

    private function jsonOrNull($value): ?string
    {
        if ($value === null) return null;

        $norm = $this->normalizeForJson($value);

        if (is_string($norm)) {
            $t = trim($norm);
            if ($t !== '' && (($t[0] === '{' && substr($t, -1) === '}') || ($t[0] === '[' && substr($t, -1) === ']'))) {
                return $norm;
            }
        }

        return json_encode($norm, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function logActivity(
        Request $r,
        string $activity,
        string $module,
        string $tableName,
        ?int $recordId = null,
        $changedFields = null,
        $oldValues = null,
        $newValues = null,
        ?string $note = null
    ): void {
        try {
            if (!Schema::hasTable('user_data_activity_log')) return;

            $actor = $this->actor($r);
            if (($actor['id'] ?? 0) <= 0) return;

            $ua = (string) ($r->userAgent() ?? '');
            if (strlen($ua) > 512) $ua = substr($ua, 0, 512);

            DB::table('user_data_activity_log')->insert([
                'performed_by'      => (int) $actor['id'],
                'performed_by_role' => $actor['role'] !== '' ? (string) $actor['role'] : null,
                'ip'                => $r->ip(),
                'user_agent'        => $ua !== '' ? $ua : null,

                'activity'          => substr((string) $activity, 0, 50),
                'module'            => substr((string) $module, 0, 100),

                'table_name'        => substr((string) $tableName, 0, 128),
                'record_id'         => $recordId,

                'changed_fields'    => $this->jsonOrNull($changedFields),
                'old_values'        => $this->jsonOrNull($oldValues),
                'new_values'        => $this->jsonOrNull($newValues),

                'log_note'          => $note,

                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            // never break main flow
        }
    }

    /**
     * accessControl (ONLY users table) - same as your reference
     */
    private function accessControl(int $userId): array
    {
        if ($userId <= 0) {
            return ['mode' => 'none', 'department_id' => null];
        }

        if (!Schema::hasColumn('users', 'department_id')) {
            return ['mode' => 'not_allowed', 'department_id' => null];
        }

        $q = DB::table('users')->select(['id', 'role', 'department_id', 'status']);

        if (Schema::hasColumn('users', 'deleted_at')) {
            $q->whereNull('deleted_at');
        }

        $u = $q->where('id', $userId)->first();
        if (!$u) {
            return ['mode' => 'none', 'department_id' => null];
        }

        if (isset($u->status) && (string)$u->status !== 'active') {
            return ['mode' => 'none', 'department_id' => null];
        }

        $role = strtolower(trim((string)($u->role ?? '')));
        $role = str_replace([' ', '-'], '_', $role);
        $role = preg_replace('/_+/', '_', $role) ?? $role;

        $deptId = $u->department_id !== null ? (int)$u->department_id : null;
        if ($deptId !== null && $deptId <= 0) $deptId = null;

        $allRoles  = ['admin', 'director', 'principal', 'author']; // can manage all
        $deptRoles = ['hod', 'faculty', 'technical_assistant', 'it_person', 'placement_officer', 'student'];

        if (in_array($role, $allRoles, true)) {
            return ['mode' => 'all', 'department_id' => null];
        }

        if (in_array($role, $deptRoles, true)) {
            if (!$deptId) return ['mode' => 'none', 'department_id' => null];
            return ['mode' => 'department', 'department_id' => $deptId];
        }

        return ['mode' => 'not_allowed', 'department_id' => null];
    }

    /**
     * Resolve department by id|uuid|slug (safe)
     */
    private function resolveDepartment($identifier)
    {
        $q = DB::table($this->deptTable);

        // departments usually have deleted_at
        if (Schema::hasColumn($this->deptTable, 'deleted_at')) {
            $q->whereNull('deleted_at');
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('uuid', (string) $identifier);
        } else {
            $q->where('slug', (string) $identifier);
        }

        return $q->first();
    }

    /* ============================================================
     * ADMIN: View list (departments + settings)
     * ============================================================ */
    public function index(Request $request)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac      = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] !== 'all')         return response()->json(['error' => 'Not allowed'], 403);

        $includeInactive = filter_var($request->query('include_inactive', true), FILTER_VALIDATE_BOOLEAN);
        $onlyFeatured    = filter_var($request->query('only_featured', false), FILTER_VALIDATE_BOOLEAN);

        $q = DB::table($this->deptTable . ' as d')
            ->leftJoin($this->settingsTable . ' as s', 's.department_id', '=', 'd.id')
            ->select([
                'd.*',
                's.id as setting_id',
                's.uuid as setting_uuid',
                DB::raw('COALESCE(s.sort_order, 999999) as sort_order'),
                DB::raw('COALESCE(s.featured, 0) as featured'),
            ]);

        if (Schema::hasColumn($this->deptTable, 'deleted_at')) {
            $q->whereNull('d.deleted_at');
        }

        // default: active only unless include_inactive=true and column exists
        if (!$includeInactive && Schema::hasColumn($this->deptTable, 'active')) {
            $q->where('d.active', true);
        }

        if ($onlyFeatured) {
            $q->where('s.featured', true);
        }

        if ($request->filled('q')) {
            $term = '%' . trim($request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('d.title', 'like', $term)
                    ->orWhere('d.slug', 'like', $term)
                    ->orWhere('d.short_name', 'like', $term)
                    ->orWhere('d.department_type', 'like', $term);
            });
        }

        // Admin ordering view
        $q->orderByRaw('COALESCE(s.featured, 0) DESC')
          ->orderByRaw('COALESCE(s.sort_order, 999999) ASC')
          ->orderBy('d.title', 'asc');

        return response()->json([
            'data' => $q->get(),
        ]);
    }

    /* ============================================================
     * ADMIN: Upsert single department setting
     * body: { department: <id|uuid|slug>, sort_order: int, featured: bool }
     * ============================================================ */
    public function upsert(Request $request)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac      = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') {
            $this->logActivity($request, 'forbidden', $this->logModule, $this->settingsTable, null, null, null, null, 'Upsert: not allowed');
            return response()->json(['error' => 'Not allowed'], 403);
        }
        if ($ac['mode'] !== 'all') {
            $this->logActivity($request, 'forbidden', $this->logModule, $this->settingsTable, null, null, null, null, 'Upsert: not allowed (mode!=' . ($ac['mode'] ?? '') . ')');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $v = Validator::make($request->all(), [
            'department' => 'required', // id|uuid|slug
            'sort_order' => 'required|integer|min:0|max:1000000',
            'featured'   => 'required|boolean',
        ]);

        if ($v->fails()) {
            $this->logActivity(
                $request,
                'validation_failed',
                $this->logModule,
                $this->settingsTable,
                null,
                array_keys((array) $request->all()),
                null,
                ['errors' => $v->errors()->toArray()],
                'Upsert: validation failed'
            );
            return response()->json(['errors' => $v->errors()], 422);
        }

        $data  = $v->validated();
        $actor = $this->actor($request);

        $dept = $this->resolveDepartment($data['department']);
        if (!$dept) {
            $this->logActivity($request, 'not_found', $this->logModule, $this->settingsTable, null, null, null, null, 'Upsert: department not found');
            return response()->json(['message' => 'Department not found'], 404);
        }

        // Find existing setting row (unique department_id)
        $existing = DB::table($this->settingsTable)->where('department_id', (int)$dept->id)->first();

        if ($existing) {
            $old = $existing;

            DB::table($this->settingsTable)
                ->where('id', $existing->id)
                ->update([
                    'sort_order' => (int) $data['sort_order'],
                    'featured'   => (bool) $data['featured'],
                    'updated_at' => now(),
                ]);

            $row = DB::table($this->settingsTable)->where('id', $existing->id)->first();

            $this->logActivity(
                $request,
                'update',
                $this->logModule,
                $this->settingsTable,
                (int) $existing->id,
                ['sort_order', 'featured'],
                $old,
                $row,
                'Department enquiry settings updated'
            );

            return response()->json([
                'success'  => true,
                'setting'  => $row,
                'department' => $dept,
            ]);
        }

        // Insert
        $payload = [
            'uuid'          => (string) Str::uuid(),
            'department_id' => (int) $dept->id,
            'sort_order'    => (int) $data['sort_order'],
            'featured'      => (bool) $data['featured'],
            'created_by'    => $actor['id'] ?: null,
            'created_at_ip' => $request->ip(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ];

        $id  = DB::table($this->settingsTable)->insertGetId($payload);
        $row = DB::table($this->settingsTable)->where('id', $id)->first();

        $this->logActivity(
            $request,
            'create',
            $this->logModule,
            $this->settingsTable,
            (int) $id,
            array_keys($payload),
            null,
            $row,
            'Department enquiry settings created'
        );

        return response()->json([
            'success'    => true,
            'setting'    => $row,
            'department' => $dept,
        ], 201);
    }

    /* ============================================================
     * ADMIN: Bulk save ordering & featured flags
     * body: { items: [ {department:..., sort_order:..., featured:...}, ... ] }
     * ============================================================ */
    public function bulkUpsert(Request $request)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac      = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] !== 'all') {
            $this->logActivity($request, 'forbidden', $this->logModule, $this->settingsTable, null, null, null, null, 'Bulk upsert: not allowed');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $v = Validator::make($request->all(), [
            'items'                 => 'required|array|min:1|max:500',
            'items.*.department'    => 'required',
            'items.*.sort_order'    => 'required|integer|min:0|max:1000000',
            'items.*.featured'      => 'required|boolean',
        ]);

        if ($v->fails()) {
            $this->logActivity(
                $request,
                'validation_failed',
                $this->logModule,
                $this->settingsTable,
                null,
                array_keys((array) $request->all()),
                null,
                ['errors' => $v->errors()->toArray()],
                'Bulk upsert: validation failed'
            );
            return response()->json(['errors' => $v->errors()], 422);
        }

        $actor = $this->actor($request);
        $items = $v->validated()['items'];

        $result = [
            'updated' => 0,
            'created' => 0,
            'errors'  => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($items as $idx => $it) {
                $dept = $this->resolveDepartment($it['department']);
                if (!$dept) {
                    $result['errors'][] = [
                        'index' => $idx,
                        'department' => $it['department'],
                        'message' => 'Department not found',
                    ];
                    continue;
                }

                $existing = DB::table($this->settingsTable)->where('department_id', (int)$dept->id)->first();

                if ($existing) {
                    DB::table($this->settingsTable)
                        ->where('id', $existing->id)
                        ->update([
                            'sort_order' => (int) $it['sort_order'],
                            'featured'   => (bool) $it['featured'],
                            'updated_at' => now(),
                        ]);
                    $result['updated']++;
                } else {
                    DB::table($this->settingsTable)->insert([
                        'uuid'          => (string) Str::uuid(),
                        'department_id' => (int) $dept->id,
                        'sort_order'    => (int) $it['sort_order'],
                        'featured'      => (bool) $it['featured'],
                        'created_by'    => $actor['id'] ?: null,
                        'created_at_ip' => $request->ip(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                    $result['created']++;
                }
            }

            DB::commit();

            $this->logActivity(
                $request,
                'bulk_upsert',
                $this->logModule,
                $this->settingsTable,
                null,
                ['items_count' => count($items)],
                null,
                $result,
                'Bulk upsert completed'
            );

            return response()->json([
                'success' => true,
                'result'  => $result,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->logActivity(
                $request,
                'error',
                $this->logModule,
                $this->settingsTable,
                null,
                null,
                null,
                ['exception' => $e->getMessage()],
                'Bulk upsert failed'
            );

            return response()->json(['error' => 'Bulk save failed'], 500);
        }
    }

    /* ============================================================
     * PUBLIC: Departments for enquiry form (ordered by featured + sort_order)
     * GET: /api/public/departments  (you can map this method)
     * query:
     *   per_page (optional)
     *   q (optional)
     *   active (optional; default true for public)
     *   only_featured (optional)
     * ============================================================ */
    public function publicDepartments(Request $request)
{
    $perPage = max(1, min(500, (int) $request->query('per_page', 200)));

    $q = DB::table($this->deptTable . ' as d')
        ->join($this->settingsTable . ' as s', 's.department_id', '=', 'd.id') // ✅ JOIN (only configured rows)
        ->select([
            'd.*',
            's.uuid as enquiry_setting_uuid',
            's.sort_order',
            's.featured',
        ])
        ->where('s.featured', true); // ✅ ONLY featured

    // departments deleted_at safe
    if (Schema::hasColumn($this->deptTable, 'deleted_at')) {
        $q->whereNull('d.deleted_at');
    }

    // public default: active=true (if column exists)
    if (Schema::hasColumn($this->deptTable, 'active')) {
        if ($request->has('active')) {
            $active = filter_var($request->query('active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($active !== null) $q->where('d.active', $active);
        } else {
            $q->where('d.active', true);
        }
    }

    // optional search
    if ($request->filled('q')) {
        $term = '%' . trim($request->query('q')) . '%';
        $q->where(function ($sub) use ($term) {
            $sub->where('d.title', 'like', $term)
                ->orWhere('d.slug', 'like', $term)
                ->orWhere('d.short_name', 'like', $term)
                ->orWhere('d.department_type', 'like', $term);
        });
    }

    // ✅ EXACT order as managed
    $q->orderBy('s.sort_order', 'asc');

    $p = $q->paginate($perPage);

    return response()->json([
        'data' => $p->items(),
        'pagination' => [
            'page'      => $p->currentPage(),
            'per_page'  => $p->perPage(),
            'total'     => $p->total(),
            'last_page' => $p->lastPage(),
        ],
    ]);
}
}