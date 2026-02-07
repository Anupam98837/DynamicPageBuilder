<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class DepartmentController extends Controller
{
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
     * - Safe: never breaks main flow if logging fails
     * - Logs all non-GET controller actions (POST/PUT/PATCH/DELETE)
     * ========================= */

    private function normalizeForJson($value)
    {
        if ($value === null) return null;

        // Convert stdClass / objects to array safely
        if (is_object($value) || is_array($value)) {
            $arr = json_decode(json_encode($value), true);
            return $arr === null ? (array) $value : $arr;
        }

        // scalar
        return $value;
    }

    private function jsonOrNull($value): ?string
    {
        if ($value === null) return null;

        $norm = $this->normalizeForJson($value);

        // If it is already a JSON string, we still keep it as-is only if it looks like JSON
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
            // If migration not run yet, do nothing
            if (!Schema::hasTable('user_data_activity_log')) return;

            $actor = $this->actor($r);
            if (($actor['id'] ?? 0) <= 0) return; // performed_by is required

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
            // Never break main functionality because of logging
        }
    }

    /**
     * accessControl (ONLY users table)
     *
     * Returns ONLY:
     *  - ['mode' => 'all',         'department_id' => null]
     *  - ['mode' => 'department',  'department_id' => <int>]
     *  - ['mode' => 'none',        'department_id' => null]
     *  - ['mode' => 'not_allowed', 'department_id' => null]
     */
    private function accessControl(int $userId): array
    {
        if ($userId <= 0) {
            return ['mode' => 'none', 'department_id' => null];
        }

        // Safety (if some env doesn't have dept column yet)
        if (!Schema::hasColumn('users', 'department_id')) {
            return ['mode' => 'not_allowed', 'department_id' => null];
        }

        $q = DB::table('users')->select(['id', 'role', 'department_id', 'status']);

        // your schema has deleted_at; keep it safe
        if (Schema::hasColumn('users', 'deleted_at')) {
            $q->whereNull('deleted_at');
        }

        $u = $q->where('id', $userId)->first();

        if (!$u) {
            return ['mode' => 'none', 'department_id' => null];
        }

        // optional: inactive users => none
        if (isset($u->status) && (string)$u->status !== 'active') {
            return ['mode' => 'none', 'department_id' => null];
        }

        // normalize role from users table
        $role = strtolower(trim((string)($u->role ?? '')));
        $role = str_replace([' ', '-'], '_', $role);
        $role = preg_replace('/_+/', '_', $role) ?? $role;

        $deptId = $u->department_id !== null ? (int)$u->department_id : null;
        if ($deptId !== null && $deptId <= 0) $deptId = null;

        // ✅ CONFIG: decide access by role + department_id
        $allRoles  = ['admin', 'director', 'principal']; // gets ALL even if dept null
        $deptRoles = ['hod', 'faculty', 'technical_assistant', 'it_person', 'placement_officer', 'student']; // needs dept

        if (in_array($role, $allRoles, true)) {
            return ['mode' => 'all', 'department_id' => null];
        }

        if (in_array($role, $deptRoles, true)) {
            // none is based on role + dept id (your rule)
            if (!$deptId) return ['mode' => 'none', 'department_id' => null];
            return ['mode' => 'department', 'department_id' => $deptId];
        }

        return ['mode' => 'not_allowed', 'department_id' => null];
    }

    /**
     * Base query for departments with common filters
     */
    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('departments');

        if (! $includeDeleted) {
            $q->whereNull('deleted_at');
        }

        // search by title / slug / short_name / department_type: ?q=
        if ($request->filled('q')) {
            $term = '%' . trim($request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('title', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                    ->orWhere('short_name', 'like', $term)
                    ->orWhere('department_type', 'like', $term);
            });
        }

        // filter by active: ?active=1 / ?active=0
        if ($request->has('active')) {
            $active = filter_var(
                $request->query('active'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
            if ($active !== null) {
                $q->where('active', $active);
            }
        }

        // sort: ?sort=created_at|title|id|short_name&direction=asc|desc
        $sort = (string) $request->query('sort', 'created_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['created_at', 'title', 'id', 'short_name'];
        if (! in_array($sort, $allowed, true)) {
            $sort = 'created_at';
        }

        $q->orderBy($sort, $dir);

        return $q;
    }

    /**
     * Resolve a department by id | uuid | slug
     */
    protected function resolveDepartment($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('departments');

        if (! $includeDeleted) {
            $q->whereNull('deleted_at');
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('uuid', (string) $identifier);
        } else {
            // fallback to slug
            $q->where('slug', (string) $identifier);
        }

        return $q->first();
    }

    /**
     * List departments
     * Query params: per_page, page, q, active, with_trashed, only_trashed, sort, direction
     */
    public function index(Request $request)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);

        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        // mode none => empty but keep same response shape
        if ($ac['mode'] === 'none') {
            return response()->json([
                'data' => [],
                'pagination' => [
                    'page'      => 1,
                    'per_page'  => $perPage,
                    'total'     => 0,
                    'last_page' => 1,
                ],
            ]);
        }

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        if ($onlyDeleted) {
            $query->whereNotNull('deleted_at');
        }

        // department mode => only their department row
        if ($ac['mode'] === 'department') {
            $deptId = (int) $ac['department_id'];
            $query->where('id', $deptId);
        }

        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => $paginator->items(),
            'pagination' => [
                'page'      => $paginator->currentPage(),
                'per_page'  => $paginator->perPage(),
                'total'     => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * List only trashed departments (bin)
     */
    public function trash(Request $request)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);

        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        if ($ac['mode'] === 'none') {
            return response()->json([
                'data' => [],
                'pagination' => [
                    'page'      => 1,
                    'per_page'  => $perPage,
                    'total'     => 0,
                    'last_page' => 1,
                ],
            ]);
        }

        $query = DB::table('departments')->whereNotNull('deleted_at');

        // department mode => only their department row (even if deleted)
        if ($ac['mode'] === 'department') {
            $deptId = (int) $ac['department_id'];
            $query->where('id', $deptId);
        }

        if ($request->filled('q')) {
            $term = '%' . trim($request->query('q')) . '%';
            $query->where(function ($sub) use ($term) {
                $sub->where('title', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                    ->orWhere('short_name', 'like', $term)
                    ->orWhere('department_type', 'like', $term);
            });
        }

        $query->orderBy('deleted_at', 'desc');

        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => $paginator->items(),
            'pagination' => [
                'page'      => $paginator->currentPage(),
                'per_page'  => $paginator->perPage(),
                'total'     => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Create department (POST)
     */
    public function store(Request $request)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') {
            $this->logActivity($request, 'forbidden', 'departments', 'departments', null, null, null, null, 'Create department: not allowed (mode=not_allowed)');
            return response()->json(['error' => 'Not allowed'], 403);
        }
        if ($ac['mode'] !== 'all') {
            $this->logActivity($request, 'forbidden', 'departments', 'departments', null, null, null, null, 'Create department: not allowed (mode!=' . ($ac['mode'] ?? '') . ')');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $v = Validator::make($request->all(), [
            'title'           => 'required|string|max:150',
            'slug'            => 'nullable|string|max:160|unique:departments,slug,NULL,id,deleted_at,NULL',

            // ✅ NEW FIELDS
            'short_name'      => 'nullable|string|max:60',
            'department_type' => 'nullable|string|max:60',
            'description'     => 'nullable|string',

            'active'          => 'sometimes|boolean',
            'metadata'        => 'nullable|array',
        ]);

        if ($v->fails()) {
            $this->logActivity(
                $request,
                'validation_failed',
                'departments',
                'departments',
                null,
                array_keys((array) $request->all()),
                null,
                ['errors' => $v->errors()->toArray()],
                'Create department: validation failed'
            );
            return response()->json(['errors' => $v->errors()], 422);
        }

        $data  = $v->validated();
        $actor = $this->actor($request);
        $ip    = $request->ip();

        // Generate slug if empty
        if (empty($data['slug'])) {
            $base = Str::slug($data['title']);
            if ($base === '') {
                $base = 'department';
            }

            $slug = $base;
            $i    = 1;
            while (DB::table('departments')->where('slug', $slug)->whereNull('deleted_at')->exists()) {
                $slug = $base . '-' . $i++;
            }
            $data['slug'] = $slug;
        }

        $payload = [
            'uuid'            => (string) Str::uuid(),
            'title'           => $data['title'],
            'slug'            => $data['slug'],

            // ✅ NEW FIELDS
            'short_name'      => array_key_exists('short_name', $data) ? $data['short_name'] : null,
            'department_type' => array_key_exists('department_type', $data) ? $data['department_type'] : null,
            'description'     => array_key_exists('description', $data) ? $data['description'] : null,

            'active'          => array_key_exists('active', $data) ? (bool) $data['active'] : true,
            'created_by'      => $actor['id'] ?: null,
            'created_at_ip'   => $ip,
            'created_at'      => now(),
            'updated_at'      => now(),
        ];

        if (array_key_exists('metadata', $data)) {
            $payload['metadata'] = $data['metadata'] !== null
                ? json_encode($data['metadata'])
                : null;
        }

        $id  = DB::table('departments')->insertGetId($payload);
        $row = DB::table('departments')->where('id', $id)->first();

        // ✅ LOG (success)
        $changed = array_keys($payload);
        $this->logActivity(
            $request,
            'create',
            'departments',
            'departments',
            (int) $id,
            $changed,
            null,
            $row,
            'Department created'
        );

        return response()->json([
            'success'    => true,
            'department' => $row,
        ], 201);
    }

    /**
     * Show single department by id|uuid|slug
     */
    public function show(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return response()->json(['department' => null], 200);

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $dept = $this->resolveDepartment($identifier, $includeDeleted);
        if (! $dept) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        // department mode => only allow own department
        if ($ac['mode'] === 'department') {
            $deptId = (int) $ac['department_id'];
            if ((int) $dept->id !== $deptId) {
                return response()->json(['message' => 'Department not found'], 404);
            }
        }

        return response()->json(['department' => $dept]);
    }

    /**
     * Update department (partial) (PUT/PATCH)
     */
    public function update(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') {
            $this->logActivity($request, 'forbidden', 'departments', 'departments', null, null, null, null, 'Update department: not allowed (mode=not_allowed)');
            return response()->json(['error' => 'Not allowed'], 403);
        }
        if ($ac['mode'] !== 'all') {
            $this->logActivity($request, 'forbidden', 'departments', 'departments', null, null, null, null, 'Update department: not allowed (mode!=' . ($ac['mode'] ?? '') . ')');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $dept = $this->resolveDepartment($identifier, true);
        if (! $dept) {
            $this->logActivity($request, 'not_found', 'departments', 'departments', null, null, null, null, 'Update department: not found (identifier=' . (string)$identifier . ')');
            return response()->json(['message' => 'Department not found'], 404);
        }

        $oldSnapshot = $dept;

        $v = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:150',
            'slug'  => [
                'sometimes',
                'nullable',
                'string',
                'max:160',
                Rule::unique('departments', 'slug')
                    ->ignore($dept->id)
                    ->whereNull('deleted_at'),
            ],

            // ✅ NEW FIELDS
            'short_name'      => 'sometimes|nullable|string|max:60',
            'department_type' => 'sometimes|nullable|string|max:60',
            'description'     => 'sometimes|nullable|string',

            'active'          => 'sometimes|boolean',
            'metadata'        => 'sometimes|nullable|array',
        ]);

        if ($v->fails()) {
            $this->logActivity(
                $request,
                'validation_failed',
                'departments',
                'departments',
                (int) $dept->id,
                array_keys((array) $request->all()),
                $oldSnapshot,
                ['errors' => $v->errors()->toArray()],
                'Update department: validation failed'
            );
            return response()->json(['errors' => $v->errors()], 422);
        }

        $data    = $v->validated();
        $payload = [];

        if (array_key_exists('title', $data)) {
            $payload['title'] = $data['title'];
        }

        if (array_key_exists('slug', $data)) {
            $payload['slug'] = $data['slug'];
        } elseif (array_key_exists('title', $data)) {
            // No slug passed but title changed => regenerate slug
            $base = Str::slug($data['title']);
            if ($base === '') {
                $base = 'department';
            }

            $slug = $base;
            $i    = 1;
            while (
                DB::table('departments')
                    ->where('slug', $slug)
                    ->where('id', '!=', $dept->id)
                    ->whereNull('deleted_at')
                    ->exists()
            ) {
                $slug = $base . '-' . $i++;
            }
            $payload['slug'] = $slug;
        }

        // ✅ NEW FIELDS
        if (array_key_exists('short_name', $data)) {
            $payload['short_name'] = $data['short_name'];
        }

        if (array_key_exists('department_type', $data)) {
            $payload['department_type'] = $data['department_type'];
        }

        if (array_key_exists('description', $data)) {
            $payload['description'] = $data['description'];
        }

        if (array_key_exists('active', $data)) {
            $payload['active'] = (bool) $data['active'];
        }

        if (array_key_exists('metadata', $data)) {
            $payload['metadata'] = $data['metadata'] !== null
                ? json_encode($data['metadata'])
                : null;
        }

        if (! empty($payload)) {
            $payload['updated_at'] = now();
            DB::table('departments')->where('id', $dept->id)->update($payload);
        }

        $row = DB::table('departments')->where('id', $dept->id)->first();

        // ✅ LOG (success / noop)
        $changedFields = array_keys($payload);
        $changedFields = array_values(array_filter($changedFields, fn($f) => $f !== 'updated_at'));

        $this->logActivity(
            $request,
            empty($changedFields) ? 'update_noop' : 'update',
            'departments',
            'departments',
            (int) $dept->id,
            $changedFields,
            $oldSnapshot,
            $row,
            empty($changedFields) ? 'Update department: no changes applied' : 'Department updated'
        );

        return response()->json([
            'success'    => true,
            'department' => $row,
        ]);
    }

    /**
     * Toggle active flag (can be used as archive/unarchive) (PATCH/PUT)
     */
    public function toggleActive(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') {
            $this->logActivity($request, 'forbidden', 'departments', 'departments', null, null, null, null, 'Toggle active: not allowed (mode=not_allowed)');
            return response()->json(['error' => 'Not allowed'], 403);
        }
        if ($ac['mode'] !== 'all') {
            $this->logActivity($request, 'forbidden', 'departments', 'departments', null, null, null, null, 'Toggle active: not allowed (mode!=' . ($ac['mode'] ?? '') . ')');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $dept = $this->resolveDepartment($identifier, true);
        if (! $dept) {
            $this->logActivity($request, 'not_found', 'departments', 'departments', null, null, null, null, 'Toggle active: department not found (identifier=' . (string)$identifier . ')');
            return response()->json(['message' => 'Department not found'], 404);
        }

        $old = $dept;
        $newActive = ! (bool) $dept->active;

        DB::table('departments')
            ->where('id', $dept->id)
            ->update([
                'active'     => $newActive,
                'updated_at' => now(),
            ]);

        $row = DB::table('departments')->where('id', $dept->id)->first();

        // ✅ LOG
        $this->logActivity(
            $request,
            'toggle_active',
            'departments',
            'departments',
            (int) $dept->id,
            ['active'],
            $old,
            $row,
            'Department active toggled'
        );

        return response()->json([
            'success'    => true,
            'department' => $row,
        ]);
    }

    /**
     * Soft-delete (move to bin) (DELETE)
     */
    public function destroy(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') {
            $this->logActivity($request, 'forbidden', 'departments', 'departments', null, null, null, null, 'Soft delete: not allowed (mode=not_allowed)');
            return response()->json(['error' => 'Not allowed'], 403);
        }
        if ($ac['mode'] !== 'all') {
            $this->logActivity($request, 'forbidden', 'departments', 'departments', null, null, null, null, 'Soft delete: not allowed (mode!=' . ($ac['mode'] ?? '') . ')');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $dept = $this->resolveDepartment($identifier, false);
        if (! $dept) {
            $this->logActivity($request, 'not_found', 'departments', 'departments', null, null, null, null, 'Soft delete: not found or already deleted (identifier=' . (string)$identifier . ')');
            return response()->json(['message' => 'Department not found or already deleted'], 404);
        }

        $old = $dept;

        DB::table('departments')
            ->where('id', $dept->id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        $row = DB::table('departments')->where('id', $dept->id)->first();

        // ✅ LOG
        $this->logActivity(
            $request,
            'delete',
            'departments',
            'departments',
            (int) $dept->id,
            ['deleted_at'],
            $old,
            $row,
            'Department moved to bin (soft delete)'
        );

        return response()->json(['success' => true]);
    }

    /**
     * Restore from bin (POST/PATCH)
     */
    public function restore(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') {
            $this->logActivity($request, 'forbidden', 'departments', 'departments', null, null, null, null, 'Restore: not allowed (mode=not_allowed)');
            return response()->json(['error' => 'Not allowed'], 403);
        }
        if ($ac['mode'] !== 'all') {
            $this->logActivity($request, 'forbidden', 'departments', 'departments', null, null, null, null, 'Restore: not allowed (mode!=' . ($ac['mode'] ?? '') . ')');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $dept = $this->resolveDepartment($identifier, true);
        if (! $dept || $dept->deleted_at === null) {
            $this->logActivity($request, 'not_found', 'departments', 'departments', null, null, null, null, 'Restore: department not found in bin (identifier=' . (string)$identifier . ')');
            return response()->json(['message' => 'Department not found in bin'], 404);
        }

        $old = $dept;

        DB::table('departments')
            ->where('id', $dept->id)
            ->update([
                'deleted_at' => null,
                'updated_at' => now(),
            ]);

        $row = DB::table('departments')->where('id', $dept->id)->first();

        // ✅ LOG
        $this->logActivity(
            $request,
            'restore',
            'departments',
            'departments',
            (int) $dept->id,
            ['deleted_at'],
            $old,
            $row,
            'Department restored from bin'
        );

        return response()->json([
            'success'    => true,
            'department' => $row,
        ]);
    }

    /**
     * Permanent delete (DELETE)
     */
    public function forceDelete(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') {
            $this->logActivity($request, 'forbidden', 'departments', 'departments', null, null, null, null, 'Force delete: not allowed (mode=not_allowed)');
            return response()->json(['error' => 'Not allowed'], 403);
        }
        if ($ac['mode'] !== 'all') {
            $this->logActivity($request, 'forbidden', 'departments', 'departments', null, null, null, null, 'Force delete: not allowed (mode!=' . ($ac['mode'] ?? '') . ')');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $dept = $this->resolveDepartment($identifier, true);
        if (! $dept) {
            $this->logActivity($request, 'not_found', 'departments', 'departments', null, null, null, null, 'Force delete: department not found (identifier=' . (string)$identifier . ')');
            return response()->json(['message' => 'Department not found'], 404);
        }

        $old = $dept;

        DB::table('departments')->where('id', $dept->id)->delete();

        // ✅ LOG
        $this->logActivity(
            $request,
            'force_delete',
            'departments',
            'departments',
            (int) $dept->id,
            null,
            $old,
            null,
            'Department permanently deleted'
        );

        return response()->json(['success' => true]);
    }

    /**
     * Public list departments (NO accessControl, open endpoint)
     * Same as index() behavior: per_page, page, q, active, with_trashed, only_trashed, sort, direction
     */
    public function publicIndex(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        if ($onlyDeleted) {
            $query->whereNotNull('deleted_at');
        }

        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => $paginator->items(),
            'pagination' => [
                'page'      => $paginator->currentPage(),
                'per_page'  => $paginator->perPage(),
                'total'     => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }
}
