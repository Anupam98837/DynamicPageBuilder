<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CourseSemesterSectionController extends Controller
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

    /* =========================================================
     | Activity Log Helpers (NON-BREAKING)
     |========================================================= */

    private function jsonOrNull($v): ?string
    {
        if ($v === null) return null;

        // already JSON string?
        if (is_string($v)) {
            $trim = trim($v);
            if ($trim === '') return null;

            if (($trim[0] === '{' || $trim[0] === '[' || $trim[0] === '"')) {
                json_decode($trim, true);
                if (json_last_error() === JSON_ERROR_NONE) return $trim;
            }

            return json_encode($v, JSON_UNESCAPED_UNICODE);
        }

        return json_encode($v, JSON_UNESCAPED_UNICODE);
    }

    private function normalizeComparable($v)
    {
        if ($v === null) return null;

        if (is_string($v)) {
            $t = trim($v);
            if ($t === '') return '';

            // try json decode for fair comparison
            if ($t !== '' && ($t[0] === '{' || $t[0] === '[' || $t[0] === '"')) {
                $d = json_decode($t, true);
                if (json_last_error() === JSON_ERROR_NONE) return $d;
            }

            return $t;
        }

        return $v;
    }

    private function valuesEqual($a, $b): bool
    {
        $na = $this->normalizeComparable($a);
        $nb = $this->normalizeComparable($b);

        // treat null and empty string as different (safer)
        if (is_array($na) || is_array($nb)) return $na == $nb;

        // numeric compare for numeric-ish values
        if (is_numeric($na) && is_numeric($nb)) {
            return (string)$na === (string)$nb;
        }

        return $na === $nb;
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
            // Never break main functionality if logs table not migrated yet
            if (!Schema::hasTable('user_data_activity_log')) return;

            $a  = $this->actor($r);
            $ua = $r->userAgent();
            if ($ua !== null) $ua = mb_substr((string)$ua, 0, 512);

            $now = now();

            DB::table('user_data_activity_log')->insert([
                'performed_by'       => (int)($a['id'] ?? 0),
                'performed_by_role'  => trim((string)($a['role'] ?? '')) !== '' ? (string)$a['role'] : null,
                'ip'                 => $this->ip($r),
                'user_agent'         => $ua,

                'activity'           => $activity,
                'module'             => $module,

                'table_name'         => $tableName,
                'record_id'          => $recordId !== null ? (int)$recordId : null,

                'changed_fields'     => $this->jsonOrNull($changedFields),
                'old_values'         => $this->jsonOrNull($oldValues),
                'new_values'         => $this->jsonOrNull($newValues),

                'log_note'           => $note,

                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        } catch (\Throwable $e) {
            // swallow: logging must NEVER affect API behavior
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

    private function respondEmptyList(Request $r)
    {
        $page = max(1, (int)$r->query('page', 1));
        $per  = min(100, max(5, (int)$r->query('per_page', 20)));

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

    /**
     * Normalize identifier for WHERE clauses.
     * - When you query using baseQuery() you MUST use alias 'css'
     * - When you query using DB::table('course_semester_sections') you MUST NOT use alias
     */
    private function normalizeIdentifier(string $idOrUuid, ?string $alias = 'css'): array
    {
        $idOrUuid = trim($idOrUuid);

        $rawCol = $this->isNumericId($idOrUuid) ? 'id' : 'uuid';
        $val    = ($rawCol === 'id') ? (int)$idOrUuid : $idOrUuid;

        $prefix = ($alias !== null && $alias !== '') ? ($alias . '.') : '';

        return [
            'col'     => $prefix . $rawCol, // e.g. "css.uuid" or "uuid"
            'raw_col' => $rawCol,           // e.g. "uuid" (safe for DB::table without alias)
            'val'     => $val,
        ];
    }

    private function normalizeStatusFromActiveFlag($active, ?string $status): string
    {
        // If active/is_active/isActive is provided as 1/0, prefer it.
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

    private function semesterSlugFallback(?string $semesterTitle, ?int $semesterNo): string
    {
        $t = trim((string)$semesterTitle);
        $slug = $t !== '' ? Str::slug($t) : '';
        if ($slug === '') {
            $n = (int)($semesterNo ?? 0);
            $slug = $n > 0 ? ('semester-' . $n) : 'semester';
        }
        return $slug;
    }

    private function semesterCodeFallback(?int $semesterNo): string
    {
        $n = (int)($semesterNo ?? 0);
        return $n > 0 ? ('SEM-' . $n) : 'SEM';
    }

    protected function baseQuery(bool $includeDeleted = false)
    {
        // Optional columns on course_semesters (avoid query crash if missing)
        $hasCsSlug = $this->hasCol('course_semesters', 'slug');
        $hasCsCode = $this->hasCol('course_semesters', 'code');
        $hasCsMeta = $this->hasCol('course_semesters', 'metadata');

        $select = [
            'css.id',
            'css.uuid',
            'css.semester_id',
            'css.course_id',
            'css.department_id',
            'css.title',
            'css.description',
            'css.sort_order',
            'css.status',
            'css.publish_at',
            'css.metadata',
            'css.created_by',
            'css.created_at',
            'css.updated_at',
            'css.created_at_ip',
            'css.updated_at_ip',
            'css.deleted_at',

            // helpful denorm fields
            'cs.title as semester_title',
            'cs.semester_no as semester_no',

            'c.title as course_title',
            'd.title as department_title',
        ];

        if ($hasCsSlug) $select[] = 'cs.slug as semester_slug';
        else $select[] = DB::raw('NULL as semester_slug');

        if ($hasCsCode) $select[] = 'cs.code as semester_code';
        else $select[] = DB::raw('NULL as semester_code');

        if ($hasCsMeta) $select[] = 'cs.metadata as semester_metadata';
        else $select[] = DB::raw('NULL as semester_metadata');

        $q = DB::table('course_semester_sections as css')
            ->leftJoin('course_semesters as cs', 'cs.id', '=', 'css.semester_id')
            ->leftJoin('courses as c', 'c.id', '=', 'css.course_id')
            ->leftJoin('departments as d', 'd.id', '=', 'css.department_id')
            ->select($select);

        if (!$includeDeleted) $q->whereNull('css.deleted_at');
        return $q;
    }

    private function respondList(Request $r, $q)
    {
        $page = max(1, (int)$r->query('page', 1));
        $per  = min(100, max(5, (int)$r->query('per_page', 20)));

        $total = (clone $q)->count('css.id');
        $rows  = $q->forPage($page, $per)->get();

        // normalize output to be frontend-friendly
        $data = $rows->map(function ($x) {

            $meta = $this->normalizeMetadata($x->metadata ?? null);
            $semMeta = $this->normalizeMetadata($x->semester_metadata ?? null);

            // Always provide semester.slug (and semester.code) even if column empty
            $semesterSlug = trim((string)($x->semester_slug ?? ''));
            $semesterCode = trim((string)($x->semester_code ?? ''));

            if ($semesterSlug === '' && is_array($semMeta)) $semesterSlug = trim((string)($semMeta['slug'] ?? ''));
            if ($semesterCode === '' && is_array($semMeta)) $semesterCode = trim((string)($semMeta['code'] ?? ''));

            if ($semesterSlug === '') $semesterSlug = $this->semesterSlugFallback($x->semester_title ?? null, $x->semester_no ?? null);
            if ($semesterCode === '') $semesterCode = $this->semesterCodeFallback($x->semester_no ?? null);

            $status = (string)($x->status ?? 'active');

            return [
                'id'            => (int)$x->id,
                'uuid'          => (string)$x->uuid,
                'semester_id'   => (int)$x->semester_id,
                'course_id'     => $x->course_id !== null ? (int)$x->course_id : null,
                'department_id' => $x->department_id !== null ? (int)$x->department_id : null,

                'title'       => (string)$x->title,
                'description' => $x->description, // HTML allowed
                'sort_order'  => (int)($x->sort_order ?? 0),
                'status'      => $status,
                'publish_at'  => $x->publish_at,

                // convenient flags
                'is_active' => $status === 'active',

                // nested helpers for your Blade JS
                'semester' => [
                    'id'          => (int)$x->semester_id,
                    'title'       => $x->semester_title,
                    'slug'        => $semesterSlug,
                    'code'        => $semesterCode,
                    'semester_no' => $x->semester_no,
                ],
                'course' => $x->course_id ? [
                    'id'    => (int)$x->course_id,
                    'title' => $x->course_title,
                ] : null,
                'department' => $x->department_id ? [
                    'id'    => (int)$x->department_id,
                    'title' => $x->department_title,
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
     | GET /api/course-semester-sections
     |========================================================= */
    public function index(Request $r)
    {
        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);
        $ac      = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return $this->respondEmptyList($r);

        $qText      = trim((string)$r->query('q', ''));
        $status     = trim((string)$r->query('status', '')); // active|inactive
        $semesterId = $r->query('semester_id', null);
        $courseId   = $r->query('course_id', null);
        $deptId     = $r->query('department_id', null);

        $sort = (string)$r->query('sort', 'updated_at');
        $dir  = strtolower((string)$r->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSort = ['created_at','updated_at','title','sort_order','publish_at','status'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'updated_at';

        $q = $this->baseQuery(false);

        // ✅ Department access
        if ($ac['mode'] === 'department') {
            $q->where('css.department_id', (int)$ac['department_id']);
        }

        if ($qText !== '') {
            $q->where(function ($w) use ($qText) {
                $w->where('css.title', 'like', "%{$qText}%")
                  ->orWhere('css.uuid', 'like', "%{$qText}%")
                  ->orWhere('cs.title', 'like', "%{$qText}%")
                  ->orWhere('c.title', 'like', "%{$qText}%")
                  ->orWhere('d.title', 'like', "%{$qText}%");
            });
        }

        if ($status !== '') $q->where('css.status', $status);

        if ($semesterId !== null && $semesterId !== '') $q->where('css.semester_id', (int)$semesterId);
        if ($courseId !== null && $courseId !== '')     $q->where('css.course_id', (int)$courseId);

        // optional filter (still limited by accessControl when dept-mode)
        if ($deptId !== null && $deptId !== '')         $q->where('css.department_id', (int)$deptId);

        // tab compatibility: ?active=1 / ?active=0
        if ($r->has('active')) {
            $av = (string)$r->query('active');
            if (in_array($av, ['1','true','yes'], true)) $q->where('css.status', 'active');
            if (in_array($av, ['0','false','no'], true)) $q->where('css.status', 'inactive');
        }

        // ordering
        $q->orderBy("css.$sort", $dir)->orderBy('css.id', 'desc');

        return $this->respondList($r, $q);
    }

    /* =========================================================
     | TRASH
     | GET /api/course-semester-sections/trash
     |========================================================= */
    public function trash(Request $r)
    {
        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);
        $ac      = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return $this->respondEmptyList($r);

        $qText = trim((string)$r->query('q', ''));
        $sort  = (string)$r->query('sort', 'deleted_at');
        $dir   = strtolower((string)$r->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSort = ['deleted_at','updated_at','title','created_at'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'deleted_at';

        $q = $this->baseQuery(true)->whereNotNull('css.deleted_at');

        // ✅ Department access
        if ($ac['mode'] === 'department') {
            $q->where('css.department_id', (int)$ac['department_id']);
        }

        if ($qText !== '') {
            $q->where(function ($w) use ($qText) {
                $w->where('css.title', 'like', "%{$qText}%")
                  ->orWhere('css.uuid', 'like', "%{$qText}%")
                  ->orWhere('cs.title', 'like', "%{$qText}%")
                  ->orWhere('c.title', 'like', "%{$qText}%")
                  ->orWhere('d.title', 'like', "%{$qText}%");
            });
        }

        $q->orderBy("css.$sort", $dir)->orderBy('css.id', 'desc');

        return $this->respondList($r, $q);
    }

    /* =========================================================
     | CURRENT (frontend-friendly)
     | GET /api/course-semester-sections/current
     |========================================================= */
    public function current(Request $r)
    {
        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);
        $ac      = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return $this->respondEmptyList($r);

        $semesterId = $r->query('semester_id', null);
        $courseId   = $r->query('course_id', null);

        $q = $this->baseQuery(false)
            ->where('css.status', 'active')
            ->where(function ($w) {
                $w->whereNull('css.publish_at')->orWhere('css.publish_at', '<=', now());
            })
            ->orderBy('css.sort_order', 'asc')
            ->orderBy('css.id', 'asc');

        // ✅ Department access
        if ($ac['mode'] === 'department') {
            $q->where('css.department_id', (int)$ac['department_id']);
        }

        if ($semesterId !== null && $semesterId !== '') $q->where('css.semester_id', (int)$semesterId);
        if ($courseId !== null && $courseId !== '')     $q->where('css.course_id', (int)$courseId);

        return $this->respondList($r, $q);
    }

    /* =========================================================
     | SHOW
     | GET /api/course-semester-sections/{id|uuid}
     |========================================================= */
    public function show(string $idOrUuid)
    {
        $r = request();

        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);
        $ac      = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return $this->respondEmptyList($r);

        // baseQuery uses alias "css"
        $w = $this->normalizeIdentifier($idOrUuid, 'css');

        $q = $this->baseQuery(true)->where($w['col'], $w['val']);

        // ✅ Department access
        if ($ac['mode'] === 'department') {
            $q->where('css.department_id', (int)$ac['department_id']);
        }

        $row = (clone $q)->first();
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        // keep your existing behavior (returns list-style response)
        return $this->respondList($r, $q);
    }

    /* =========================================================
     | CREATE
     | POST /api/course-semester-sections
     |========================================================= */
    public function store(Request $r)
    {
        $module = 'course_semester_sections';

        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);
        $ac      = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($r, 'create_denied', $module, 'course_semester_sections', null, null, null, null, 'Create denied (accessControl).');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $actor = $this->actor($r);

        $r->validate([
            'semester_id'   => ['required','integer', 'exists:course_semesters,id'],
            'course_id'     => ['nullable','integer', 'exists:courses,id'],
            'department_id' => ['nullable','integer', 'exists:departments,id'],

            'title'         => ['required','string','max:255'],
            'description'   => ['nullable','string'], // HTML allowed
            'sort_order'    => ['nullable','integer','min:0'],
            'status'        => ['nullable','string','max:20', Rule::in(['active','inactive'])],
            'publish_at'    => ['nullable','date'],

            'metadata'      => ['nullable'], // array|string(json)
            'active'        => ['nullable'], // 1/0 compatibility
            'is_active'     => ['nullable'],
            'isActive'      => ['nullable'],
        ]);

        // ✅ Department enforcement on writes
        $deptToStore = $r->filled('department_id') ? (int)$r->input('department_id') : null;
        if ($ac['mode'] === 'department') {
            $forcedDept = (int)$ac['department_id'];
            if ($r->filled('department_id') && (int)$r->input('department_id') !== $forcedDept) {
                $this->logActivity(
                    $r,
                    'create_denied',
                    $module,
                    'course_semester_sections',
                    null,
                    ['department_id'],
                    ['department_id' => (int)$r->input('department_id')],
                    ['department_id' => $forcedDept],
                    'Create denied (department mismatch).'
                );
                return response()->json(['error' => 'Not allowed'], 403);
            }
            $deptToStore = $forcedDept; // force
        }

        $activeFlag = $r->input('active', $r->input('is_active', $r->input('isActive')));
        $status = $this->normalizeStatusFromActiveFlag($activeFlag, $r->input('status'));

        $meta = $r->input('metadata', null);
        if (is_array($meta)) $meta = json_encode($meta);
        if (is_string($meta)) {
            json_decode($meta, true);
            if (json_last_error() !== JSON_ERROR_NONE) $meta = null;
        }

        $ts = now();

        $id = DB::table('course_semester_sections')->insertGetId([
            'uuid'          => (string) Str::uuid(),
            'semester_id'   => (int) $r->input('semester_id'),
            'course_id'     => $r->filled('course_id') ? (int)$r->input('course_id') : null,
            'department_id' => $deptToStore,

            'title'       => (string)$r->input('title'),
            'description' => $r->input('description'),
            'sort_order'  => (int)($r->input('sort_order', 0) ?? 0),
            'status'      => $status,
            'publish_at'  => $r->filled('publish_at') ? $r->input('publish_at') : null,

            'created_by'    => $actor['id'] ?: null,
            'created_at_ip' => $this->ip($r),
            'updated_at_ip' => $this->ip($r),

            'metadata'   => $meta,
            'created_at' => $ts,
            'updated_at' => $ts,
        ]);

        $created = DB::table('course_semester_sections')->where('id', $id)->first();

        $newValues = [
            'id'            => $id,
            'uuid'          => $created->uuid ?? null,
            'semester_id'   => $created->semester_id ?? null,
            'course_id'     => $created->course_id ?? null,
            'department_id' => $created->department_id ?? null,
            'title'         => $created->title ?? null,
            'description'   => $created->description ?? null,
            'sort_order'    => $created->sort_order ?? null,
            'status'        => $created->status ?? null,
            'publish_at'    => $created->publish_at ?? null,
            'metadata'      => $created->metadata ?? null,
        ];

        $this->logActivity(
            $r,
            'create',
            $module,
            'course_semester_sections',
            (int)$id,
            array_keys($newValues),
            null,
            $newValues,
            'Created course semester section.'
        );

        return response()->json([
            'success' => true,
            'message' => 'Created',
            'data'    => $created,
        ], 201);
    }

    /* =========================================================
     | UPDATE
     | PATCH /api/course-semester-sections/{id|uuid}
     |========================================================= */
    public function update(Request $r, string $idOrUuid)
    {
        $module = 'course_semester_sections';

        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);
        $ac      = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $rid = $this->isNumericId($idOrUuid) ? (int)$idOrUuid : null;
            $this->logActivity($r, 'update_denied', $module, 'course_semester_sections', $rid, null, null, null, 'Update denied (accessControl).');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        // IMPORTANT: no alias for direct table queries
        $w = $this->normalizeIdentifier($idOrUuid, null);

        $existsQ = DB::table('course_semester_sections')->where($w['raw_col'], $w['val']);

        // ✅ Department access
        if ($ac['mode'] === 'department') {
            $existsQ->where('department_id', (int)$ac['department_id']);
        }

        $exists = $existsQ->first();
        if (!$exists) {
            $this->logActivity($r, 'update_not_found', $module, 'course_semester_sections', null, null, null, null, 'Update failed: record not found.');
            return response()->json(['success'=>false,'message'=>'Not found'], 404);
        }

        $r->validate([
            'semester_id'   => ['sometimes','required','integer', 'exists:course_semesters,id'],
            'course_id'     => ['nullable','integer', 'exists:courses,id'],
            'department_id' => ['nullable','integer', 'exists:departments,id'],

            'title'         => ['sometimes','required','string','max:255'],
            'description'   => ['nullable','string'],
            'sort_order'    => ['nullable','integer','min:0'],
            'status'        => ['nullable','string','max:20', Rule::in(['active','inactive'])],
            'publish_at'    => ['nullable','date'],

            'metadata'      => ['nullable'],
            'active'        => ['nullable'],
            'is_active'     => ['nullable'],
            'isActive'      => ['nullable'],
        ]);

        // ✅ Department enforcement on writes
        if ($ac['mode'] === 'department' && $r->has('department_id')) {
            if (!$r->filled('department_id')) {
                $this->logActivity($r, 'update_denied', $module, 'course_semester_sections', (int)$exists->id, ['department_id'], ['department_id' => $exists->department_id], null, 'Update denied: empty department_id in dept-mode.');
                return response()->json(['error' => 'Not allowed'], 403);
            }
            if ((int)$r->input('department_id') !== (int)$ac['department_id']) {
                $this->logActivity(
                    $r,
                    'update_denied',
                    $module,
                    'course_semester_sections',
                    (int)$exists->id,
                    ['department_id'],
                    ['department_id' => $exists->department_id],
                    ['department_id' => (int)$r->input('department_id')],
                    'Update denied: department mismatch in dept-mode.'
                );
                return response()->json(['error' => 'Not allowed'], 403);
            }
        }

        $activeFlag = $r->input('active', $r->input('is_active', $r->input('isActive')));
        $status = $this->normalizeStatusFromActiveFlag($activeFlag, $r->input('status', $exists->status ?? 'active'));

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

        $ts = now();

        $payload = [
            'updated_at'    => $ts,
            'updated_at_ip' => $this->ip($r),
            'status'        => $status,
        ];

        foreach (['semester_id','course_id','department_id','title','description','sort_order','publish_at'] as $k) {
            if ($r->has($k)) {
                $payload[$k] = $r->filled($k) ? $r->input($k) : null;
            }
        }

        // If department-mode: never allow changing department_id away from forced dept
        if ($ac['mode'] === 'department') {
            $payload['department_id'] = (int)$ac['department_id'];
        }

        if ($r->has('sort_order')) $payload['sort_order'] = (int)($r->input('sort_order', 0) ?? 0);
        if ($r->has('metadata'))   $payload['metadata']   = $metaToStore;

        $updQ = DB::table('course_semester_sections')->where($w['raw_col'], $w['val']);
        if ($ac['mode'] === 'department') $updQ->where('department_id', (int)$ac['department_id']);
        $updQ->update($payload);

        // build change snapshot
        $changed = [];
        $oldVals = [];
        $newVals = [];

        foreach ($payload as $k => $v) {
            if (in_array($k, ['updated_at','updated_at_ip'], true)) continue;

            $before = $exists->$k ?? null;
            if (!$this->valuesEqual($before, $v)) {
                $changed[]     = $k;
                $oldVals[$k]   = $before;
                $newVals[$k]   = $v;
            }
        }

        $this->logActivity(
            $r,
            'update',
            $module,
            'course_semester_sections',
            (int)$exists->id,
            $changed ?: null,
            $oldVals ?: null,
            $newVals ?: null,
            'Updated course semester section.'
        );

        return response()->json([
            'success' => true,
            'message' => 'Updated',
        ]);
    }

    /* =========================================================
     | DELETE (soft)
     | DELETE /api/course-semester-sections/{id|uuid}
     |========================================================= */
    public function destroy(Request $r, string $idOrUuid)
    {
        $module = 'course_semester_sections';

        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);
        $ac      = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $rid = $this->isNumericId($idOrUuid) ? (int)$idOrUuid : null;
            $this->logActivity($r, 'delete_denied', $module, 'course_semester_sections', $rid, null, null, null, 'Delete denied (accessControl).');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        // IMPORTANT: no alias for direct table queries
        $w = $this->normalizeIdentifier($idOrUuid, null);

        $rowQ = DB::table('course_semester_sections')->where($w['raw_col'], $w['val']);

        // ✅ Department access
        if ($ac['mode'] === 'department') {
            $rowQ->where('department_id', (int)$ac['department_id']);
        }

        $row = $rowQ->first();
        if (!$row) {
            $this->logActivity($r, 'delete_not_found', $module, 'course_semester_sections', null, null, null, null, 'Delete failed: record not found.');
            return response()->json(['success'=>false,'message'=>'Not found'], 404);
        }

        if ($row->deleted_at) {
            $this->logActivity(
                $r,
                'delete_skip',
                $module,
                'course_semester_sections',
                (int)$row->id,
                null,
                ['deleted_at' => $row->deleted_at],
                null,
                'Already in trash.'
            );
            return response()->json(['success'=>true,'message'=>'Already in trash']);
        }

        $ts = now();

        $updQ = DB::table('course_semester_sections')->where('id', $row->id);
        if ($ac['mode'] === 'department') $updQ->where('department_id', (int)$ac['department_id']);

        $updQ->update([
            'deleted_at'    => $ts,
            'updated_at'    => $ts,
            'updated_at_ip' => $this->ip($r),
        ]);

        $this->logActivity(
            $r,
            'delete',
            $module,
            'course_semester_sections',
            (int)$row->id,
            ['deleted_at'],
            ['deleted_at' => $row->deleted_at],
            ['deleted_at' => $ts],
            'Moved to trash (soft delete).'
        );

        return response()->json(['success'=>true,'message'=>'Moved to trash']);
    }

    /* =========================================================
     | RESTORE
     | POST /api/course-semester-sections/{id|uuid}/restore
     |========================================================= */
    public function restore(Request $r, string $idOrUuid)
    {
        $module = 'course_semester_sections';

        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);
        $ac      = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $rid = $this->isNumericId($idOrUuid) ? (int)$idOrUuid : null;
            $this->logActivity($r, 'restore_denied', $module, 'course_semester_sections', $rid, null, null, null, 'Restore denied (accessControl).');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        // IMPORTANT: no alias for direct table queries
        $w = $this->normalizeIdentifier($idOrUuid, null);

        $rowQ = DB::table('course_semester_sections')->where($w['raw_col'], $w['val']);

        // ✅ Department access
        if ($ac['mode'] === 'department') {
            $rowQ->where('department_id', (int)$ac['department_id']);
        }

        $row = $rowQ->first();
        if (!$row) {
            $this->logActivity($r, 'restore_not_found', $module, 'course_semester_sections', null, null, null, null, 'Restore failed: record not found.');
            return response()->json(['success'=>false,'message'=>'Not found'], 404);
        }

        $ts = now();

        $updQ = DB::table('course_semester_sections')->where('id', $row->id);
        if ($ac['mode'] === 'department') $updQ->where('department_id', (int)$ac['department_id']);

        $updQ->update([
            'deleted_at'    => null,
            'updated_at'    => $ts,
            'updated_at_ip' => $this->ip($r),
        ]);

        $this->logActivity(
            $r,
            'restore',
            $module,
            'course_semester_sections',
            (int)$row->id,
            ['deleted_at'],
            ['deleted_at' => $row->deleted_at],
            ['deleted_at' => null],
            'Restored from trash.'
        );

        return response()->json(['success'=>true,'message'=>'Restored']);
    }

    /* =========================================================
     | FORCE DELETE
     | DELETE /api/course-semester-sections/{id|uuid}/force
     |========================================================= */
    public function forceDelete(Request $r, string $idOrUuid)
    {
        $module = 'course_semester_sections';

        $actorId = (int) ($r->attributes->get('auth_tokenable_id') ?? 0);
        $ac      = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $rid = $this->isNumericId($idOrUuid) ? (int)$idOrUuid : null;
            $this->logActivity($r, 'force_delete_denied', $module, 'course_semester_sections', $rid, null, null, null, 'Force delete denied (accessControl).');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        // IMPORTANT: no alias for direct table queries
        $w = $this->normalizeIdentifier($idOrUuid, null);

        $rowQ = DB::table('course_semester_sections')->where($w['raw_col'], $w['val']);

        // ✅ Department access
        if ($ac['mode'] === 'department') {
            $rowQ->where('department_id', (int)$ac['department_id']);
        }

        $row = $rowQ->first();
        if (!$row) {
            $this->logActivity($r, 'force_delete_not_found', $module, 'course_semester_sections', null, null, null, null, 'Force delete failed: record not found.');
            return response()->json(['success'=>false,'message'=>'Not found'], 404);
        }

        // snapshot (small + useful)
        $oldSnapshot = [
            'id'            => $row->id ?? null,
            'uuid'          => $row->uuid ?? null,
            'semester_id'   => $row->semester_id ?? null,
            'course_id'     => $row->course_id ?? null,
            'department_id' => $row->department_id ?? null,
            'title'         => $row->title ?? null,
            'status'        => $row->status ?? null,
            'deleted_at'    => $row->deleted_at ?? null,
        ];

        $delQ = DB::table('course_semester_sections')->where('id', $row->id);
        if ($ac['mode'] === 'department') $delQ->where('department_id', (int)$ac['department_id']);
        $delQ->delete();

        $this->logActivity(
            $r,
            'force_delete',
            $module,
            'course_semester_sections',
            (int)$row->id,
            null,
            $oldSnapshot,
            null,
            'Deleted permanently (force delete).'
        );

        return response()->json(['success'=>true,'message'=>'Deleted permanently']);
    }
}
