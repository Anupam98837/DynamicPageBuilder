<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SubjectController extends Controller
{
    /* =========================================================
     | Helpers
     |========================================================= */

    /** cache schema checks */
    protected array $colCache = [];

    private function actor(Request $r): array
    {
        return [
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? optional($r->user())->id ?? 0),
            'role' => (string) ($r->attributes->get('auth_role') ?? ($r->user()->role ?? '')),
            'type' => (string) ($r->attributes->get('auth_tokenable_type') ?? ($r->user() ? get_class($r->user()) : '')),
            'uuid' => (string) ($r->attributes->get('auth_user_uuid') ?? ($r->user()->uuid ?? '')),
        ];
    }

    private function ip(Request $r): ?string
    {
        $ip = $r->ip();
        return $ip ? (string) $ip : null;
    }

    private function hasCol(string $table, string $col): bool
    {
        $k = $table . '.' . $col;
        if (array_key_exists($k, $this->colCache)) return (bool) $this->colCache[$k];

        try {
            return $this->colCache[$k] = Schema::hasColumn($table, $col);
        } catch (\Throwable $e) {
            return $this->colCache[$k] = false;
        }
    }

    private function isNumericId($v): bool
    {
        return is_string($v) || is_int($v) ? preg_match('/^\d+$/', (string)$v) === 1 : false;
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

    private function respondEmptyList(Request $r)
    {
        $page = max(1, (int)$r->query('page', 1));
        $per  = min(200, max(5, (int)$r->query('per_page', 20)));

        return response()->json([
            'success'    => true,
            'data'       => [],
            'pagination' => [
                'page'      => $page,
                'per_page'  => $per,
                'total'     => 0,
                'last_page' => 1,
            ],
        ]);
    }

    private function denyWriteForNoneOrNotAllowed(array $ac)
    {
        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            return response()->json(['error' => 'Not allowed'], 403);
        }
        return null;
    }

    /**
     * Normalize identifier for WHERE clauses.
     * - When you query using baseQuery() you MUST use alias 's'
     * - When you query using DB::table('subjects') you MUST NOT use alias
     */
    private function normalizeIdentifier(string $idOrUuid, ?string $alias = 's'): array
    {
        $idOrUuid = trim($idOrUuid);

        $rawCol = $this->isNumericId($idOrUuid) ? 'id' : 'uuid';
        $val    = ($rawCol === 'id') ? (int)$idOrUuid : $idOrUuid;

        $prefix = ($alias !== null && $alias !== '') ? ($alias . '.') : '';

        return [
            'col'     => $prefix . $rawCol, // e.g. "s.uuid" or "uuid"
            'raw_col' => $rawCol,           // e.g. "uuid"
            'val'     => $val,
        ];
    }

    private function normalizeStatusFromActiveFlag($active, ?string $status): string
    {
        if ($active !== null && $active !== '') {
            $v = (string)$active;
            if (in_array($v, ['1','true','yes'], true)) return 'active';
            if (in_array($v, ['0','false','no'], true)) return 'inactive';
        }
        $s = strtolower(trim((string)$status));
        return $s ?: 'active';
    }

    private function normalizeMetadata($meta)
    {
        if ($meta === null) return null;
        if (is_array($meta)) return $meta;

        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $meta;
        }

        return $meta;
    }

    protected function baseQuery(bool $includeDeleted = false)
    {
        $select = [
            's.id',
            's.uuid',
            's.department_id',

            // ✅ optional course mapping (added)
            // Will be appended dynamically if columns exist

            's.subject_code',
            's.title',
            's.short_title',
            's.description',
            's.subject_type',
            's.credits',
            's.lecture_hours',
            's.practical_hours',
            's.sort_order',
            's.status',
            's.publish_at',
            's.expire_at',
            's.metadata',
            's.created_by',
            's.created_at',
            's.updated_at',
            's.created_at_ip',
            's.updated_at_ip',
            's.deleted_at',

            'd.title as department_title',
        ];

        $q = DB::table('subjects as s')
            ->leftJoin('departments as d', 'd.id', '=', 's.department_id');

        // ✅ Optional Course join
        if ($this->hasCol('subjects', 'course_id')) {
            $select[] = 's.course_id';
            $select[] = 'c.title as course_title';

            $q->leftJoin('courses as c', 'c.id', '=', 's.course_id');
        }

        // ✅ Optional Course Semester join
        if ($this->hasCol('subjects', 'course_semester_id')) {
            $select[] = 's.course_semester_id';
            $select[] = 'cs.semester_no as course_semester_no';
            $select[] = 'cs.title as course_semester_title';

            $q->leftJoin('course_semesters as cs', 'cs.id', '=', 's.course_semester_id');
        }

        $q->select($select);

        if (!$includeDeleted) $q->whereNull('s.deleted_at');
        return $q;
    }

    private function respondList(Request $r, $q)
    {
        $page = max(1, (int)$r->query('page', 1));
        $per  = min(200, max(5, (int)$r->query('per_page', 20)));

        $total = (clone $q)->count('s.id');
        $rows  = $q->forPage($page, $per)->get();

        $data = $rows->map(function ($x) {
            $meta = $this->normalizeMetadata($x->metadata ?? null);
            $status = (string)($x->status ?? 'active');

            $courseId = property_exists($x, 'course_id') ? $x->course_id : null;
            $courseSemesterId = property_exists($x, 'course_semester_id') ? $x->course_semester_id : null;

            return [
                'id'            => (int)$x->id,
                'uuid'          => (string)$x->uuid,

                'department_id' => $x->department_id !== null ? (int)$x->department_id : null,

                // ✅ optional course mapping
                'course_id' => $courseId !== null ? (int)$courseId : null,
                'course_semester_id' => $courseSemesterId !== null ? (int)$courseSemesterId : null,

                'subject_code'  => (string)($x->subject_code ?? ''),
                'title'         => (string)($x->title ?? ''),
                'short_title'   => $x->short_title,
                'description'   => $x->description,          // HTML allowed
                'subject_type'  => $x->subject_type,         // dynamic string (no restriction)

                'credits'         => $x->credits !== null ? (int)$x->credits : null,
                'lecture_hours'   => $x->lecture_hours !== null ? (int)$x->lecture_hours : null,
                'practical_hours' => $x->practical_hours !== null ? (int)$x->practical_hours : null,

                'sort_order'    => (int)($x->sort_order ?? 0),
                'status'        => $status,
                'publish_at'    => $x->publish_at,
                'expire_at'     => $x->expire_at,

                'is_active'     => $status === 'active',

                'department' => $x->department_id ? [
                    'id'    => (int)$x->department_id,
                    'title' => $x->department_title,
                ] : null,

                // ✅ course info (only if joined)
                'course' => $courseId ? [
                    'id'    => (int)$courseId,
                    'title' => property_exists($x, 'course_title') ? $x->course_title : null,
                ] : null,

                // ✅ semester info (only if joined)
                'course_semester' => $courseSemesterId ? [
                    'id'          => (int)$courseSemesterId,
                    'semester_no' => property_exists($x, 'course_semester_no') ? $x->course_semester_no : null,
                    'title'       => property_exists($x, 'course_semester_title') ? $x->course_semester_title : null,
                ] : null,

                'metadata' => $meta,

                'created_by'    => $x->created_by !== null ? (int)$x->created_by : null,
                'created_at'    => $x->created_at,
                'updated_at'    => $x->updated_at,
                'created_at_ip' => $x->created_at_ip,
                'updated_at_ip' => $x->updated_at_ip,
                'deleted_at'    => $x->deleted_at,
            ];
        })->values();

        return response()->json([
            'success'    => true,
            'data'       => $data,
            'pagination' => [
                'page'      => $page,
                'per_page'  => $per,
                'total'     => $total,
                'last_page' => (int) ceil(max(1, $total) / max(1, $per)),
            ],
        ]);
    }

    /* =========================================================
     | LIST
     | GET /api/subjects
     |========================================================= */
    public function index(Request $r)
    {
        // ✅ access control
        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? $this->actor($r)['id'] ?? 0);
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return $this->respondEmptyList($r);

        $qText  = trim((string)$r->query('q', ''));
        $status = trim((string)$r->query('status', '')); // active|inactive
        $deptId = $r->query('department_id', null);
        $type   = trim((string)$r->query('subject_type', ''));

        // ✅ Optional course filters
        $courseId = $r->query('course_id', null);
        $courseSemesterId = $r->query('course_semester_id', $r->query('semester_id', null)); // backward compat

        $sort = (string)$r->query('sort', 'updated_at');
        $dir  = strtolower((string)$r->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSort = ['created_at','updated_at','title','subject_code','sort_order','publish_at','status'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'updated_at';

        $q = $this->baseQuery(false);

        // ✅ department mode forces department filter (ignore request deptId)
        if ($ac['mode'] === 'department') {
            $q->where('s.department_id', (int)$ac['department_id']);
        }

        if ($qText !== '') {
            $q->where(function ($w) use ($qText) {
                $w->where('s.title', 'like', "%{$qText}%")
                  ->orWhere('s.subject_code', 'like', "%{$qText}%")
                  ->orWhere('s.short_title', 'like', "%{$qText}%")
                  ->orWhere('s.uuid', 'like', "%{$qText}%")
                  ->orWhere('d.title', 'like', "%{$qText}%");
            });
        }

        if ($status !== '') $q->where('s.status', $status);

        // only allow dept filter for ALL mode
        if ($ac['mode'] === 'all' && $deptId !== null && $deptId !== '') {
            $q->where('s.department_id', (int)$deptId);
        }

        // dynamic filter (still no restriction)
        if ($type !== '') $q->where('s.subject_type', $type);

        // ✅ course-based filters (optional)
        if ($this->hasCol('subjects', 'course_id') && $courseId !== null && $courseId !== '') {
            $q->where('s.course_id', (int)$courseId);
        }

        if ($this->hasCol('subjects', 'course_semester_id') && $courseSemesterId !== null && $courseSemesterId !== '') {
            $q->where('s.course_semester_id', (int)$courseSemesterId);
        }

        // compatibility: ?active=1 / ?active=0
        if ($r->has('active')) {
            $av = (string)$r->query('active');
            if (in_array($av, ['1','true','yes'], true)) $q->where('s.status', 'active');
            if (in_array($av, ['0','false','no'], true)) $q->where('s.status', 'inactive');
        }

        $q->orderBy("s.$sort", $dir)->orderBy('s.id', 'desc');

        return $this->respondList($r, $q);
    }

    /* =========================================================
     | TRASH
     | GET /api/subjects/trash
     |========================================================= */
    public function trash(Request $r)
    {
        // ✅ access control
        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? $this->actor($r)['id'] ?? 0);
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return $this->respondEmptyList($r);

        $qText = trim((string)$r->query('q', ''));
        $sort  = (string)$r->query('sort', 'deleted_at');
        $dir   = strtolower((string)$r->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSort = ['deleted_at','updated_at','title','created_at','subject_code'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'deleted_at';

        $q = $this->baseQuery(true)->whereNotNull('s.deleted_at');

        // ✅ department mode forces department filter
        if ($ac['mode'] === 'department') {
            $q->where('s.department_id', (int)$ac['department_id']);
        }

        if ($qText !== '') {
            $q->where(function ($w) use ($qText) {
                $w->where('s.title', 'like', "%{$qText}%")
                  ->orWhere('s.subject_code', 'like', "%{$qText}%")
                  ->orWhere('s.short_title', 'like', "%{$qText}%")
                  ->orWhere('s.uuid', 'like', "%{$qText}%")
                  ->orWhere('d.title', 'like', "%{$qText}%");
            });
        }

        $q->orderBy("s.$sort", $dir)->orderBy('s.id', 'desc');

        return $this->respondList($r, $q);
    }

    /* =========================================================
     | CURRENT (frontend-friendly)
     | GET /api/subjects/current
     |========================================================= */
    public function current(Request $request)
    {
        // ✅ access control
        $actorId = (int) ($request->attributes->get('auth_tokenable_id') ?? $this->actor($request)['id'] ?? 0);
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return response()->json(['success' => true, 'data' => []]);

        // ✅ Keep DB direct + optional joins for convenience
        $query = DB::table('subjects');

        // ✅ department mode forces department filter
        if ($ac['mode'] === 'department') {
            if (Schema::hasColumn('subjects', 'department_id')) {
                $query->where('department_id', (int)$ac['department_id']);
            }
        }

        // ✅ Only active (if column exists)
        if (Schema::hasColumn('subjects', 'status')) {
            $query->where('status', 'active');
        }

        // ✅ Optional course filter
        if ($request->filled('course_id') && Schema::hasColumn('subjects', 'course_id')) {
            $query->where('course_id', (int)$request->course_id);
        }

        // ✅ Optional semester filter
        // supports both: course_semester_id and semester_id (old)
        $semVal = $request->input('course_semester_id', $request->input('semester_id'));
        if ($semVal !== null && $semVal !== '' && Schema::hasColumn('subjects', 'course_semester_id')) {
            $query->where('course_semester_id', (int)$semVal);
        }

        $rows = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $rows
        ]);
    }

    /* =========================================================
     | SHOW
     | GET /api/subjects/{id|uuid}
     |========================================================= */
    public function show(string $idOrUuid)
    {
        $req = request();

        // ✅ access control
        $actorId = (int) ($req->attributes->get('auth_tokenable_id') ?? (int)($req->attributes->get('auth_tokenable_id') ?? 0));
        if ($actorId <= 0) {
            // fallback to actor() if available
            try {
                $actorId = (int) ($this->actor($req)['id'] ?? 0);
            } catch (\Throwable $e) {
                $actorId = 0;
            }
        }
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return response()->json(['success' => true, 'data' => []], 200);

        $w = $this->normalizeIdentifier($idOrUuid, 's');

        $q = $this->baseQuery(true)->where($w['col'], $w['val']);

        // ✅ department mode forces department filter
        if ($ac['mode'] === 'department') {
            $q->where('s.department_id', (int)$ac['department_id']);
        }

        $row = (clone $q)->first();
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        return $this->respondList($req, $q);
    }

    /* =========================================================
     | CREATE
     | POST /api/subjects
     |========================================================= */
    public function store(Request $r)
    {
        $actor = $this->actor($r);

        // ✅ access control
        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? $actor['id'] ?? 0);
        $ac = $this->accessControl($actorId);

        if ($deny = $this->denyWriteForNoneOrNotAllowed($ac)) return $deny;

        $r->validate([
            'department_id'   => ['nullable','integer','exists:departments,id'],

            // ✅ optional course mapping
            'course_id'            => ['nullable','integer','exists:courses,id'],
            'course_semester_id'   => ['nullable','integer','exists:course_semesters,id'],
            'semester_id'          => ['nullable','integer','exists:course_semesters,id'], // backward compat

            'subject_code'    => ['required','string','max:50'],
            'title'           => ['required','string','max:255'],
            'short_title'     => ['nullable','string','max:120'],
            'description'     => ['nullable','string'], // HTML allowed

            // ✅ no enum / rule here (dynamic string)
            'subject_type'    => ['nullable','string','max:50'],

            'credits'         => ['nullable','integer','min:0'],
            'lecture_hours'   => ['nullable','integer','min:0'],
            'practical_hours' => ['nullable','integer','min:0'],

            'sort_order'      => ['nullable','integer','min:0'],
            'status'          => ['nullable','string','max:20'],
            'publish_at'      => ['nullable','date'],
            'expire_at'       => ['nullable','date'],

            'metadata'        => ['nullable'],
            'active'          => ['nullable'],
            'is_active'       => ['nullable'],
            'isActive'        => ['nullable'],
        ]);

        $activeFlag = $r->input('active', $r->input('is_active', $r->input('isActive')));
        $status = $this->normalizeStatusFromActiveFlag($activeFlag, $r->input('status'));

        // metadata
        $meta = $r->input('metadata', null);
        if (is_array($meta)) $meta = json_encode($meta);
        if (is_string($meta)) {
            json_decode($meta, true);
            if (json_last_error() !== JSON_ERROR_NONE) $meta = null;
        }

        // ✅ department_id handling based on access control
        $deptId = $r->filled('department_id') ? (int)$r->input('department_id') : null;
        if ($ac['mode'] === 'department') {
            $forced = (int)$ac['department_id'];
            if ($deptId !== null && $deptId !== $forced) {
                return response()->json(['error' => 'Not allowed'], 403);
            }
            $deptId = $forced; // default/force to actor dept
        }

        // Unique guard (code unique per dept)
        $code   = trim((string)$r->input('subject_code'));

        $exists = DB::table('subjects')
            ->where('subject_code', $code)
            ->when($deptId === null, fn($q)=>$q->whereNull('department_id'), fn($q)=>$q->where('department_id',$deptId))
            ->whereNull('deleted_at')
            ->value('id');

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Subject code already exists for this department.',
            ], 422);
        }

        // ✅ course/semester values (optional)
        $courseId = $r->filled('course_id') ? (int)$r->input('course_id') : null;
        $courseSemesterId = $r->filled('course_semester_id')
            ? (int)$r->input('course_semester_id')
            : ($r->filled('semester_id') ? (int)$r->input('semester_id') : null);

        $payload = [
            'uuid'           => (string) Str::uuid(),
            'department_id'  => $deptId,

            'subject_code'   => $code,
            'title'          => (string)$r->input('title'),
            'short_title'    => $r->input('short_title'),
            'description'    => $r->input('description'),

            'subject_type'   => $r->input('subject_type'),

            'credits'         => $r->has('credits') ? (int)($r->input('credits') ?? 0) : 0,
            'lecture_hours'   => $r->has('lecture_hours') ? (int)($r->input('lecture_hours') ?? 0) : 0,
            'practical_hours' => $r->has('practical_hours') ? (int)($r->input('practical_hours') ?? 0) : 0,

            'sort_order'     => (int)($r->input('sort_order', 0) ?? 0),
            'status'         => $status,
            'publish_at'     => $r->filled('publish_at') ? $r->input('publish_at') : null,
            'expire_at'      => $r->filled('expire_at') ? $r->input('expire_at') : null,

            'created_by'     => $actor['id'] ?: null,
            'created_at_ip'  => $this->ip($r),
            'updated_at_ip'  => $this->ip($r),

            'metadata'       => $meta,
            'created_at'     => now(),
            'updated_at'     => now(),
        ];

        // ✅ only set if columns exist in DB
        if ($this->hasCol('subjects', 'course_id')) {
            $payload['course_id'] = $courseId;
        }
        if ($this->hasCol('subjects', 'course_semester_id')) {
            $payload['course_semester_id'] = $courseSemesterId;
        }

        $id = DB::table('subjects')->insertGetId($payload);

        return response()->json([
            'success' => true,
            'message' => 'Created',
            'data'    => DB::table('subjects')->where('id', $id)->first(),
        ], 201);
    }

    /* =========================================================
     | UPDATE
     | PATCH /api/subjects/{id|uuid}
     |========================================================= */
    public function update(Request $r, string $idOrUuid)
    {
        $actor = $this->actor($r);

        // ✅ access control
        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? $actor['id'] ?? 0);
        $ac = $this->accessControl($actorId);

        if ($deny = $this->denyWriteForNoneOrNotAllowed($ac)) return $deny;

        $w = $this->normalizeIdentifier($idOrUuid, null);

        $exists = DB::table('subjects')->where($w['raw_col'], $w['val'])->first();
        if (!$exists) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        // ✅ department mode: can only touch rows in their department
        if ($ac['mode'] === 'department') {
            $rowDept = $exists->department_id !== null ? (int)$exists->department_id : null;
            if ($rowDept !== (int)$ac['department_id']) {
                return response()->json(['error' => 'Not allowed'], 403);
            }
        }

        $r->validate([
            'department_id'   => ['nullable','integer','exists:departments,id'],

            // ✅ optional course mapping
            'course_id'            => ['nullable','integer','exists:courses,id'],
            'course_semester_id'   => ['nullable','integer','exists:course_semesters,id'],
            'semester_id'          => ['nullable','integer','exists:course_semesters,id'], // backward compat

            'subject_code'    => ['sometimes','required','string','max:50'],
            'title'           => ['sometimes','required','string','max:255'],
            'short_title'     => ['nullable','string','max:120'],
            'description'     => ['nullable','string'],

            'subject_type'    => ['nullable','string','max:50'],

            'credits'         => ['nullable','integer','min:0'],
            'lecture_hours'   => ['nullable','integer','min:0'],
            'practical_hours' => ['nullable','integer','min:0'],

            'sort_order'      => ['nullable','integer','min:0'],
            'status'          => ['nullable','string','max:20'],
            'publish_at'      => ['nullable','date'],
            'expire_at'       => ['nullable','date'],

            'metadata'        => ['nullable'],
            'active'          => ['nullable'],
            'is_active'       => ['nullable'],
            'isActive'        => ['nullable'],
        ]);

        $activeFlag = $r->input('active', $r->input('is_active', $r->input('isActive')));
        $status = $this->normalizeStatusFromActiveFlag($activeFlag, $r->input('status', $exists->status ?? 'active'));

        // metadata
        $metaToStore = null;
        if ($r->has('metadata')) {
            $meta = $r->input('metadata');
            if (is_array($meta)) $metaToStore = json_encode($meta);
            else if (is_string($meta)) {
                json_decode($meta, true);
                $metaToStore = (json_last_error() === JSON_ERROR_NONE) ? $meta : null;
            } else {
                $metaToStore = null;
            }
        }

        $payload = [
            'updated_at'    => now(),
            'updated_at_ip' => $this->ip($r),
            'status'        => $status,
        ];

        foreach ([
            'department_id','subject_code','title','short_title','description','subject_type',
            'credits','lecture_hours','practical_hours','sort_order','publish_at','expire_at'
        ] as $k) {
            if ($r->has($k)) {
                $payload[$k] = $r->filled($k) ? $r->input($k) : null;
            }
        }

        if ($r->has('credits'))         $payload['credits'] = $r->filled('credits') ? (int)$r->input('credits') : 0;
        if ($r->has('lecture_hours'))   $payload['lecture_hours'] = $r->filled('lecture_hours') ? (int)$r->input('lecture_hours') : 0;
        if ($r->has('practical_hours')) $payload['practical_hours'] = $r->filled('practical_hours') ? (int)$r->input('practical_hours') : 0;
        if ($r->has('sort_order'))      $payload['sort_order'] = (int)($r->input('sort_order', 0) ?? 0);
        if ($r->has('metadata'))        $payload['metadata'] = $metaToStore;

        // ✅ optional course mapping update
        if ($this->hasCol('subjects', 'course_id') && $r->has('course_id')) {
            $payload['course_id'] = $r->filled('course_id') ? (int)$r->input('course_id') : null;
        }

        if ($this->hasCol('subjects', 'course_semester_id') && ($r->has('course_semester_id') || $r->has('semester_id'))) {
            $val = $r->has('course_semester_id') ? $r->input('course_semester_id') : $r->input('semester_id');
            $payload['course_semester_id'] = ($val !== null && $val !== '') ? (int)$val : null;
        }

        // ✅ department mode: department_id cannot be changed to a different department
        if ($ac['mode'] === 'department') {
            $forced = (int)$ac['department_id'];
            if (array_key_exists('department_id', $payload)) {
                $newDept = $payload['department_id'] !== null ? (int)$payload['department_id'] : null;
                if ($newDept !== $forced) {
                    return response()->json(['error' => 'Not allowed'], 403);
                }
            } else {
                // keep it safe anyway
                $payload['department_id'] = $forced;
            }
        }

        // Unique guard on update (subject_code per dept)
        if ($r->has('subject_code') || $r->has('department_id')) {
            $deptId = array_key_exists('department_id', $payload)
                ? ($payload['department_id'] !== null ? (int)$payload['department_id'] : null)
                : ($exists->department_id !== null ? (int)$exists->department_id : null);

            $code = array_key_exists('subject_code', $payload)
                ? trim((string)$payload['subject_code'])
                : (string)$exists->subject_code;

            $dup = DB::table('subjects')
                ->where('subject_code', $code)
                ->when($deptId === null, fn($q)=>$q->whereNull('department_id'), fn($q)=>$q->where('department_id',$deptId))
                ->whereNull('deleted_at')
                ->where('id', '<>', $exists->id)
                ->value('id');

            if ($dup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject code already exists for this department.',
                ], 422);
            }
        }

        DB::table('subjects')->where($w['raw_col'], $w['val'])->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Updated',
        ]);
    }

    /* =========================================================
     | DELETE (soft)
     | DELETE /api/subjects/{id|uuid}
     |========================================================= */
    public function destroy(Request $r, string $idOrUuid)
    {
        $actor = $this->actor($r);

        // ✅ access control
        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? $actor['id'] ?? 0);
        $ac = $this->accessControl($actorId);

        if ($deny = $this->denyWriteForNoneOrNotAllowed($ac)) return $deny;

        $w = $this->normalizeIdentifier($idOrUuid, null);

        $row = DB::table('subjects')->where($w['raw_col'], $w['val'])->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        // ✅ department mode: can only touch rows in their department
        if ($ac['mode'] === 'department') {
            $rowDept = $row->department_id !== null ? (int)$row->department_id : null;
            if ($rowDept !== (int)$ac['department_id']) {
                return response()->json(['error' => 'Not allowed'], 403);
            }
        }

        if ($row->deleted_at) {
            return response()->json(['success'=>true,'message'=>'Already in trash']);
        }

        DB::table('subjects')->where('id', $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $this->ip($r),
        ]);

        return response()->json(['success'=>true,'message'=>'Moved to trash']);
    }

    /* =========================================================
     | RESTORE
     | POST /api/subjects/{id|uuid}/restore
     |========================================================= */
    public function restore(Request $r, string $idOrUuid)
    {
        $actor = $this->actor($r);

        // ✅ access control
        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? $actor['id'] ?? 0);
        $ac = $this->accessControl($actorId);

        if ($deny = $this->denyWriteForNoneOrNotAllowed($ac)) return $deny;

        $w = $this->normalizeIdentifier($idOrUuid, null);

        $row = DB::table('subjects')->where($w['raw_col'], $w['val'])->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        // ✅ department mode: can only touch rows in their department
        if ($ac['mode'] === 'department') {
            $rowDept = $row->department_id !== null ? (int)$row->department_id : null;
            if ($rowDept !== (int)$ac['department_id']) {
                return response()->json(['error' => 'Not allowed'], 403);
            }
        }

        DB::table('subjects')->where('id', $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $this->ip($r),
        ]);

        return response()->json(['success'=>true,'message'=>'Restored']);
    }

    /* =========================================================
     | FORCE DELETE
     | DELETE /api/subjects/{id|uuid}/force
     |========================================================= */
    public function forceDelete(Request $r, string $idOrUuid)
    {
        $actor = $this->actor($r);

        // ✅ access control
        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? $actor['id'] ?? 0);
        $ac = $this->accessControl($actorId);

        if ($deny = $this->denyWriteForNoneOrNotAllowed($ac)) return $deny;

        $w = $this->normalizeIdentifier($idOrUuid, null);

        $row = DB::table('subjects')->where($w['raw_col'], $w['val'])->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        // ✅ department mode: can only touch rows in their department
        if ($ac['mode'] === 'department') {
            $rowDept = $row->department_id !== null ? (int)$row->department_id : null;
            if ($rowDept !== (int)$ac['department_id']) {
                return response()->json(['error' => 'Not allowed'], 403);
            }
        }

        DB::table('subjects')->where('id', $row->id)->delete();

        return response()->json(['success'=>true,'message'=>'Deleted permanently']);
    }
}
