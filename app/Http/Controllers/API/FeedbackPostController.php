<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FeedbackPostController extends Controller
{
    private const TABLE = 'feedback_posts';

    /** cache schema checks */
    protected array $colCache = [];

    /* =========================================================
     | Helpers
     |========================================================= */

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

    private function normalizeIdentifier(string $idOrUuid, ?string $alias = 'fp'): array
    {
        $idOrUuid = trim($idOrUuid);
        $rawCol = $this->isNumericId($idOrUuid) ? 'id' : 'uuid';
        $val    = ($rawCol === 'id') ? (int)$idOrUuid : $idOrUuid;
        $prefix = ($alias !== null && $alias !== '') ? ($alias . '.') : '';

        return [
            'col'     => $prefix . $rawCol,
            'raw_col' => $rawCol,
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

    private function normalizeJson($v)
    {
        if ($v === null) return null;
        if (is_array($v)) return $v;

        if (is_string($v)) {
            $decoded = json_decode($v, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        return null;
    }

    private function encodeJsonIfValid($v): ?string
    {
        if ($v === null) return null;

        if (is_array($v)) return json_encode($v);

        if (is_string($v)) {
            json_decode($v, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $v : null;
        }

        return null;
    }

    private function isStudent(Request $r): bool
    {
        return strtolower((string)($this->actor($r)['role'] ?? '')) === 'student';
    }

    private function requireStaff(Request $r)
    {
        // staff allowed to CRUD (student cannot)
        $role = strtolower((string)($this->actor($r)['role'] ?? ''));
        $allowed = [
            'admin',
            'director',
            'principal',
            'hod',
            'faculty',
            'technical_assistant',
            'it_person',
        ];
        if (!in_array($role, $allowed, true)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized Access'], 403);
        }
        return null;
    }

    /**
     * Resolve department_id from courses table based on course_id.
     * - If department_id column missing in feedback_posts, returns null (keeps backward compatibility).
     * - If course_id empty -> returns null.
     */
    private function departmentIdFromCourse(?int $courseId): ?int
    {
        if (!$this->hasCol(self::TABLE, 'department_id')) return null;
        if (!$courseId || $courseId <= 0) return null;

        // courses.department_id
        $deptId = DB::table('courses')
            ->where('id', $courseId)
            ->whereNull('deleted_at')
            ->value('department_id');

        return $deptId !== null ? (int)$deptId : null;
    }

    /**
     * Validate that provided IDs exist + match role.
     * - faculty_ids -> users.role = faculty
     * - student_ids -> users.role = student
     */
    private function assertUsersExistWithRole(array $ids, string $role): ?string
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (empty($ids)) return null;

        $found = DB::table('users')
            ->whereIn('id', $ids)
            ->whereNull('deleted_at')
            ->where('role', $role)
            ->pluck('id')
            ->map(fn($x)=>(int)$x)
            ->all();

        sort($found);
        $missing = array_values(array_diff($ids, $found));
        if (!empty($missing)) {
            return "Invalid {$role} user IDs: " . implode(',', $missing);
        }
        return null;
    }

    private function assertQuestionsExist(array $questionIds): ?string
    {
        $questionIds = array_values(array_unique(array_map('intval', $questionIds)));
        if (empty($questionIds)) return null;

        $found = DB::table('feedback_questions')
            ->whereIn('id', $questionIds)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->map(fn($x)=>(int)$x)
            ->all();

        sort($found);
        $missing = array_values(array_diff($questionIds, $found));
        if (!empty($missing)) {
            return "Invalid question IDs: " . implode(',', $missing);
        }
        return null;
    }

    /**
     * Ensure question_faculty map is correct:
     * - Keys: question IDs (must exist AND must be inside question_ids if provided)
     * - Values: null OR {faculty_ids: [..]} OR {faculty_ids: null}
     * - faculty_ids must be faculty users
     */
    private function validateQuestionFacultyMap(?array $map, ?array $questionIds = null): ?string
    {
        if ($map === null) return null;

        $allowedQ = null;
        if (is_array($questionIds)) {
            $allowedQ = array_values(array_unique(array_map('intval', $questionIds)));
        }

        foreach ($map as $qIdKey => $val) {
            if (!preg_match('/^\d+$/', (string)$qIdKey)) {
                return "question_faculty key must be question id (numeric). Invalid key: {$qIdKey}";
            }
            $qid = (int)$qIdKey;

            if (is_array($allowedQ) && !in_array($qid, $allowedQ, true)) {
                return "question_faculty contains question_id {$qid} not present in question_ids";
            }

            if ($val === null) continue;
            if (!is_array($val)) return "question_faculty[{$qid}] must be null or object";

            if (!array_key_exists('faculty_ids', $val)) {
                return "question_faculty[{$qid}] must contain faculty_ids (array|null)";
            }

            if ($val['faculty_ids'] === null) continue;

            if (!is_array($val['faculty_ids'])) {
                return "question_faculty[{$qid}].faculty_ids must be array or null";
            }

            $ids = array_values(array_filter($val['faculty_ids'], fn($x)=>$x !== null && $x !== ''));
            $ids = array_map('intval', $ids);

            $err = $this->assertUsersExistWithRole($ids, 'faculty');
            if ($err) return "question_faculty[{$qid}]: {$err}";
        }

        return null;
    }

    protected function baseQuery(bool $includeDeleted = false)
    {
        $select = [
            'fp.id',
            'fp.uuid',

            'fp.title',
            'fp.short_title',
            'fp.description',

            'fp.course_id',
            'fp.semester_id',
            'fp.subject_id',
            'fp.section_id',
            'fp.academic_year',
            'fp.year',

            'fp.question_ids',
            'fp.faculty_ids',
            'fp.question_faculty',
            'fp.student_ids',

            'fp.sort_order',
            'fp.status',
            'fp.publish_at',
            'fp.expire_at',

            'fp.metadata',
            'fp.created_by',
            'fp.created_at',
            'fp.updated_at',
            'fp.created_at_ip',
            'fp.updated_at_ip',
            'fp.deleted_at',
        ];

        // include department_id if exists (no FE needed)
        if ($this->hasCol(self::TABLE, 'department_id')) {
            $select[] = 'fp.department_id';
        }

        $q = DB::table(self::TABLE . ' as fp')->select($select);

        if (!$includeDeleted) $q->whereNull('fp.deleted_at');
        return $q;
    }

    private function respondList(Request $r, $q)
    {
        $page = max(1, (int)$r->query('page', 1));
        $per  = min(100, max(5, (int)$r->query('per_page', 20)));

        $total = (clone $q)->count('fp.id');
        $rows  = $q->forPage($page, $per)->get();

        $hasDept = $this->hasCol(self::TABLE, 'department_id');

        $data = $rows->map(function ($x) use ($hasDept) {
            $status = (string)($x->status ?? 'active');

            $questionIds = $this->normalizeJson($x->question_ids);
            $facultyIds  = $this->normalizeJson($x->faculty_ids);
            $qfMap       = $this->normalizeJson($x->question_faculty);
            $studentIds  = $this->normalizeJson($x->student_ids);
            $meta        = $this->normalizeJson($x->metadata);

            return [
                'id'    => (int)$x->id,
                'uuid'  => (string)$x->uuid,

                'title'       => (string)($x->title ?? ''),
                'short_title' => $x->short_title !== null ? (string)$x->short_title : null,
                'description' => $x->description,

                // department_id comes from DB (derived on create/update)
                'department_id' => $hasDept ? ($x->department_id !== null ? (int)$x->department_id : null) : null,

                'course_id'   => $x->course_id !== null ? (int)$x->course_id : null,
                'semester_id' => $x->semester_id !== null ? (int)$x->semester_id : null,
                'subject_id'  => $x->subject_id !== null ? (int)$x->subject_id : null,
                'section_id'  => $x->section_id !== null ? (int)$x->section_id : null,
                'academic_year' => $x->academic_year !== null ? (string)$x->academic_year : null,
                'year'          => $x->year !== null ? (int)$x->year : null,

                'question_ids'     => is_array($questionIds) ? array_values($questionIds) : null,
                'faculty_ids'      => is_array($facultyIds) ? array_values($facultyIds) : null,
                'question_faculty' => is_array($qfMap) ? $qfMap : null,
                'student_ids'      => is_array($studentIds) ? array_values($studentIds) : null,

                'sort_order' => (int)($x->sort_order ?? 0),
                'status'     => $status,
                'is_active'  => $status === 'active',
                'publish_at' => $x->publish_at,
                'expire_at'  => $x->expire_at,

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

    /**
     * If student: only posts where student_ids contains actor id
     */
    private function applyStudentScope(Request $r, $q)
    {
        if (!$this->isStudent($r)) return $q;

        $actor = $this->actor($r);
        $sid = (int)($actor['id'] ?? 0);
        if ($sid <= 0) {
            $q->whereRaw('1=0');
            return $q;
        }

        $q->whereRaw("JSON_CONTAINS(fp.student_ids, ?, '$')", [json_encode($sid)]);
        return $q;
    }

    /* =========================================================
     | LIST
     | GET /api/feedback-posts
     |========================================================= */
    public function index(Request $r)
    {
        $qText  = trim((string)$r->query('q', ''));
        $status = trim((string)$r->query('status', '')); // active|inactive
        $sort   = (string)$r->query('sort', 'updated_at');
        $dir    = strtolower((string)$r->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        // optional filters
        $courseId   = $r->query('course_id');
        $semesterId = $r->query('semester_id');
        $subjectId  = $r->query('subject_id');
        $sectionId  = $r->query('section_id');
        $year       = $r->query('year');
        $acadYear   = trim((string)$r->query('academic_year', ''));

        // NEW: dept filter (optional) – does NOT require FE changes, but supports ?department_id=
        $deptId = $r->query('department_id');

        $allowedSort = ['created_at','updated_at','title','sort_order','publish_at','expire_at','status'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'updated_at';

        $q = $this->baseQuery(false);
        $this->applyStudentScope($r, $q);

        if ($qText !== '') {
            $q->where(function ($w) use ($qText) {
                $w->where('fp.title', 'like', "%{$qText}%")
                  ->orWhere('fp.short_title', 'like', "%{$qText}%")
                  ->orWhere('fp.uuid', 'like', "%{$qText}%");
            });
        }

        if ($status !== '') $q->where('fp.status', $status);

        if ($courseId !== null && $courseId !== '')     $q->where('fp.course_id', (int)$courseId);
        if ($semesterId !== null && $semesterId !== '') $q->where('fp.semester_id', (int)$semesterId);
        if ($subjectId !== null && $subjectId !== '')   $q->where('fp.subject_id', (int)$subjectId);
        if ($sectionId !== null && $sectionId !== '')   $q->where('fp.section_id', (int)$sectionId);

        if ($acadYear !== '') $q->where('fp.academic_year', $acadYear);
        if ($year !== null && $year !== '') $q->where('fp.year', (int)$year);

        // dept filter if column exists
        if ($this->hasCol(self::TABLE, 'department_id') && $deptId !== null && $deptId !== '') {
            $q->where('fp.department_id', (int)$deptId);
        }

        // compatibility: ?active=1 / ?active=0
        if ($r->has('active')) {
            $av = (string)$r->query('active');
            if (in_array($av, ['1','true','yes'], true)) $q->where('fp.status', 'active');
            if (in_array($av, ['0','false','no'], true)) $q->where('fp.status', 'inactive');
        }

        $q->orderBy("fp.$sort", $dir)->orderBy('fp.id', 'desc');

        return $this->respondList($r, $q);
    }

    /* =========================================================
     | TRASH
     | GET /api/feedback-posts/trash
     |========================================================= */
    public function trash(Request $r)
    {
        if ($this->isStudent($r)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized Access'], 403);
        }

        $qText = trim((string)$r->query('q', ''));
        $sort  = (string)$r->query('sort', 'deleted_at');
        $dir   = strtolower((string)$r->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSort = ['deleted_at','updated_at','title','created_at'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'deleted_at';

        $q = $this->baseQuery(true)->whereNotNull('fp.deleted_at');

        if ($qText !== '') {
            $q->where(function ($w) use ($qText) {
                $w->where('fp.title', 'like', "%{$qText}%")
                  ->orWhere('fp.uuid', 'like', "%{$qText}%");
            });
        }

        $q->orderBy("fp.$sort", $dir)->orderBy('fp.id', 'desc');

        return $this->respondList($r, $q);
    }

    /* =========================================================
     | CURRENT
     | GET /api/feedback-posts/current
     |========================================================= */
    public function current(Request $r)
    {
        $q = $this->baseQuery(false)
            ->where('fp.status', 'active')
            ->where(function ($w) {
                $w->whereNull('fp.publish_at')->orWhere('fp.publish_at', '<=', now());
            })
            ->where(function ($w) {
                $w->whereNull('fp.expire_at')->orWhere('fp.expire_at', '>=', now());
            })
            ->orderBy('fp.sort_order', 'asc')
            ->orderBy('fp.id', 'asc');

        $this->applyStudentScope($r, $q);

        if ($r->filled('course_id'))   $q->where('fp.course_id', (int)$r->query('course_id'));
        if ($r->filled('semester_id')) $q->where('fp.semester_id', (int)$r->query('semester_id'));
        if ($r->filled('subject_id'))  $q->where('fp.subject_id', (int)$r->query('subject_id'));
        if ($r->filled('section_id'))  $q->where('fp.section_id', (int)$r->query('section_id'));
        if ($r->filled('year'))        $q->where('fp.year', (int)$r->query('year'));
        if ($r->filled('academic_year')) $q->where('fp.academic_year', (string)$r->query('academic_year'));

        // optional dept filter
        if ($this->hasCol(self::TABLE, 'department_id') && $r->filled('department_id')) {
            $q->where('fp.department_id', (int)$r->query('department_id'));
        }

        return $this->respondList($r, $q);
    }

    /* =========================================================
     | SHOW
     | GET /api/feedback-posts/{id|uuid}
     |========================================================= */
    public function show(Request $r, string $idOrUuid)
    {
        $w = $this->normalizeIdentifier($idOrUuid, 'fp');

        $q = $this->baseQuery(true)->where($w['col'], $w['val']);
        $this->applyStudentScope($r, $q);

        $row = (clone $q)->first();
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        return $this->respondList($r, $q);
    }

    /* =========================================================
     | CREATE
     | POST /api/feedback-posts
     |========================================================= */
    public function store(Request $r)
    {
        if ($resp = $this->requireStaff($r)) return $resp;

        $actor = $this->actor($r);

        $r->validate([
            'title'       => ['required','string','max:255'],
            'short_title' => ['nullable','string','max:120'],
            'description' => ['nullable','string'],

            // scopes
            'course_id'   => ['required','integer'], // REQUIRED because dept_id must be derived
            'semester_id' => ['nullable','integer'],
            'subject_id'  => ['nullable','integer'],
            'section_id'  => ['nullable','integer'],
            'academic_year' => ['nullable','string','max:20'],
            'year'          => ['nullable','integer','min:1900','max:2500'],

            // json
            'question_ids'     => ['nullable'],
            'faculty_ids'      => ['nullable'],
            'question_faculty' => ['nullable'],
            'student_ids'      => ['nullable'],

            'sort_order' => ['nullable','integer','min:0'],
            'status'     => ['nullable','string','max:20', Rule::in(['active','inactive'])],
            'publish_at' => ['nullable','date'],
            'expire_at'  => ['nullable','date'],

            'metadata'   => ['nullable'],
            'active'     => ['nullable'],
            'is_active'  => ['nullable'],
            'isActive'   => ['nullable'],
        ]);

        $courseId = (int)$r->input('course_id');

        // dept_id is derived from course.department_id (no FE)
        $deptId = $this->departmentIdFromCourse($courseId);
        if ($this->hasCol(self::TABLE, 'department_id')) {
            if (!$deptId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid course_id: department not found for this course'
                ], 422);
            }
        }

        $activeFlag = $r->input('active', $r->input('is_active', $r->input('isActive')));
        $status = $this->normalizeStatusFromActiveFlag($activeFlag, $r->input('status'));

        $questionIds = $this->normalizeJson($r->input('question_ids'));
        $facultyIds  = $this->normalizeJson($r->input('faculty_ids'));
        $qfMap       = $this->normalizeJson($r->input('question_faculty'));
        $studentIds  = $this->normalizeJson($r->input('student_ids'));

        $questionIds = is_array($questionIds) ? array_values(array_unique(array_map('intval', $questionIds))) : [];
        $facultyIds  = is_array($facultyIds)  ? array_values(array_unique(array_map('intval', $facultyIds)))  : [];
        $studentIds  = is_array($studentIds)  ? array_values(array_unique(array_map('intval', $studentIds)))  : [];

        if ($err = $this->assertQuestionsExist($questionIds)) {
            return response()->json(['success'=>false,'message'=>$err], 422);
        }
        if ($err = $this->assertUsersExistWithRole($facultyIds, 'faculty')) {
            return response()->json(['success'=>false,'message'=>$err], 422);
        }
        if ($err = $this->assertUsersExistWithRole($studentIds, 'student')) {
            return response()->json(['success'=>false,'message'=>$err], 422);
        }
        if ($err = $this->validateQuestionFacultyMap(is_array($qfMap) ? $qfMap : null, $questionIds)) {
            return response()->json(['success'=>false,'message'=>$err], 422);
        }

        $meta = $this->encodeJsonIfValid($r->input('metadata'));

        $insert = [
            'uuid' => (string) Str::uuid(),

            'title'       => (string)$r->input('title'),
            'short_title' => $r->filled('short_title') ? (string)$r->input('short_title') : null,
            'description' => $r->input('description'),

            'course_id'   => $courseId,
            'semester_id' => $r->filled('semester_id') ? (int)$r->input('semester_id') : null,
            'subject_id'  => $r->filled('subject_id') ? (int)$r->input('subject_id') : null,
            'section_id'  => $r->filled('section_id') ? (int)$r->input('section_id') : null,
            'academic_year' => $r->filled('academic_year') ? (string)$r->input('academic_year') : null,
            'year'          => $r->filled('year') ? (int)$r->input('year') : null,

            'question_ids'     => !empty($questionIds) ? json_encode($questionIds) : null,
            'faculty_ids'      => !empty($facultyIds) ? json_encode($facultyIds) : null,
            'question_faculty' => is_array($qfMap) ? json_encode($qfMap) : null,
            'student_ids'      => !empty($studentIds) ? json_encode($studentIds) : null,

            'sort_order' => (int)($r->input('sort_order', 0) ?? 0),
            'status'     => $status,
            'publish_at' => $r->filled('publish_at') ? $r->input('publish_at') : null,
            'expire_at'  => $r->filled('expire_at') ? $r->input('expire_at') : null,

            'created_by'    => $actor['id'] ?: null,
            'created_at_ip' => $this->ip($r),
            'updated_at_ip' => $this->ip($r),

            'metadata'   => $meta,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];

        // store derived department_id (if column exists)
        if ($this->hasCol(self::TABLE, 'department_id')) {
            $insert['department_id'] = $deptId;
        }

        $id = DB::table(self::TABLE)->insertGetId($insert);

        return response()->json([
            'success' => true,
            'message' => 'Created',
            'data'    => DB::table(self::TABLE)->where('id', $id)->first(),
        ], 201);
    }

    /* =========================================================
     | UPDATE
     | PATCH /api/feedback-posts/{id|uuid}
     |========================================================= */
    public function update(Request $r, string $idOrUuid)
    {
        if ($resp = $this->requireStaff($r)) return $resp;

        $w = $this->normalizeIdentifier($idOrUuid, null);

        $exists = DB::table(self::TABLE)->where($w['raw_col'], $w['val'])->first();
        if (!$exists) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        $r->validate([
            'title'       => ['sometimes','required','string','max:255'],
            'short_title' => ['nullable','string','max:120'],
            'description' => ['nullable','string'],

            // course_id can be updated; dept must be re-derived if course changes
            'course_id'   => ['nullable','integer'],
            'semester_id' => ['nullable','integer'],
            'subject_id'  => ['nullable','integer'],
            'section_id'  => ['nullable','integer'],
            'academic_year' => ['nullable','string','max:20'],
            'year'          => ['nullable','integer','min:1900','max:2500'],

            'question_ids'     => ['nullable'],
            'faculty_ids'      => ['nullable'],
            'question_faculty' => ['nullable'],
            'student_ids'      => ['nullable'],

            'sort_order' => ['nullable','integer','min:0'],
            'status'     => ['nullable','string','max:20', Rule::in(['active','inactive'])],
            'publish_at' => ['nullable','date'],
            'expire_at'  => ['nullable','date'],

            'metadata'   => ['nullable'],
            'active'     => ['nullable'],
            'is_active'  => ['nullable'],
            'isActive'   => ['nullable'],
        ]);

        $activeFlag = $r->input('active', $r->input('is_active', $r->input('isActive')));
        $status = $this->normalizeStatusFromActiveFlag($activeFlag, $r->input('status', $exists->status ?? 'active'));

        $currQuestionIds = $this->normalizeJson($exists->question_ids) ?: [];
        $currFacultyIds  = $this->normalizeJson($exists->faculty_ids) ?: [];
        $currStudentIds  = $this->normalizeJson($exists->student_ids) ?: [];
        $currQfMap       = $this->normalizeJson($exists->question_faculty);

        $questionIds = $r->has('question_ids') ? $this->normalizeJson($r->input('question_ids')) : $currQuestionIds;
        $facultyIds  = $r->has('faculty_ids') ? $this->normalizeJson($r->input('faculty_ids')) : $currFacultyIds;
        $studentIds  = $r->has('student_ids') ? $this->normalizeJson($r->input('student_ids')) : $currStudentIds;
        $qfMap       = $r->has('question_faculty') ? $this->normalizeJson($r->input('question_faculty')) : $currQfMap;

        $questionIds = is_array($questionIds) ? array_values(array_unique(array_map('intval', $questionIds))) : [];
        $facultyIds  = is_array($facultyIds)  ? array_values(array_unique(array_map('intval', $facultyIds)))  : [];
        $studentIds  = is_array($studentIds)  ? array_values(array_unique(array_map('intval', $studentIds)))  : [];

        if ($err = $this->assertQuestionsExist($questionIds)) {
            return response()->json(['success'=>false,'message'=>$err], 422);
        }
        if ($err = $this->assertUsersExistWithRole($facultyIds, 'faculty')) {
            return response()->json(['success'=>false,'message'=>$err], 422);
        }
        if ($err = $this->assertUsersExistWithRole($studentIds, 'student')) {
            return response()->json(['success'=>false,'message'=>$err], 422);
        }
        if ($err = $this->validateQuestionFacultyMap(is_array($qfMap) ? $qfMap : null, $questionIds)) {
            return response()->json(['success'=>false,'message'=>$err], 422);
        }

        $metaToStore = null;
        if ($r->has('metadata')) {
            $metaToStore = $this->encodeJsonIfValid($r->input('metadata'));
        }

        $payload = [
            'updated_at'    => now(),
            'updated_at_ip' => $this->ip($r),
            'status'        => $status,
        ];

        foreach ([
            'title','short_title','description',
            'course_id','semester_id','subject_id','section_id',
            'academic_year','year',
            'publish_at','expire_at'
        ] as $k) {
            if ($r->has($k)) {
                $payload[$k] = $r->filled($k) ? $r->input($k) : null;
            }
        }

        if ($r->has('sort_order')) $payload['sort_order'] = (int)($r->input('sort_order', 0) ?? 0);

        if ($r->has('question_ids'))     $payload['question_ids'] = !empty($questionIds) ? json_encode($questionIds) : null;
        if ($r->has('faculty_ids'))      $payload['faculty_ids'] = !empty($facultyIds) ? json_encode($facultyIds) : null;
        if ($r->has('student_ids'))      $payload['student_ids'] = !empty($studentIds) ? json_encode($studentIds) : null;
        if ($r->has('question_faculty')) $payload['question_faculty'] = is_array($qfMap) ? json_encode($qfMap) : null;

        if ($r->has('metadata')) $payload['metadata'] = $metaToStore;

        // If course_id is being changed OR department_id is empty, re-derive department_id
        if ($this->hasCol(self::TABLE, 'department_id')) {
            $newCourseId = $r->has('course_id')
                ? ($r->filled('course_id') ? (int)$r->input('course_id') : 0)
                : (int)($exists->course_id ?? 0);

            $deptId = $this->departmentIdFromCourse($newCourseId);
            if (!$deptId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid course_id: department not found for this course'
                ], 422);
            }
            $payload['department_id'] = $deptId;
        }

        DB::table(self::TABLE)->where($w['raw_col'], $w['val'])->update($payload);

        return response()->json(['success' => true, 'message' => 'Updated']);
    }

    /* =========================================================
     | DELETE (soft)
     | DELETE /api/feedback-posts/{id|uuid}
     |========================================================= */
    public function destroy(Request $r, string $idOrUuid)
    {
        if ($resp = $this->requireStaff($r)) return $resp;

        $w = $this->normalizeIdentifier($idOrUuid, null);

        $row = DB::table(self::TABLE)->where($w['raw_col'], $w['val'])->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        if ($row->deleted_at) return response()->json(['success'=>true,'message'=>'Already in trash']);

        DB::table(self::TABLE)->where('id', $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $this->ip($r),
        ]);

        return response()->json(['success'=>true,'message'=>'Moved to trash']);
    }

    /* =========================================================
     | RESTORE
     | POST /api/feedback-posts/{id|uuid}/restore
     |========================================================= */
    public function restore(Request $r, string $idOrUuid)
    {
        if ($resp = $this->requireStaff($r)) return $resp;

        $w = $this->normalizeIdentifier($idOrUuid, null);

        $row = DB::table(self::TABLE)->where($w['raw_col'], $w['val'])->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        // Optional: ensure department_id exists on restore as well
        if ($this->hasCol(self::TABLE, 'department_id')) {
            $courseId = (int)($row->course_id ?? 0);
            $deptId = $this->departmentIdFromCourse($courseId);
            if ($deptId) {
                DB::table(self::TABLE)->where('id', $row->id)->update([
                    'department_id' => $deptId,
                ]);
            }
        }

        DB::table(self::TABLE)->where('id', $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $this->ip($r),
        ]);

        return response()->json(['success'=>true,'message'=>'Restored']);
    }

    /* =========================================================
     | FORCE DELETE
     | DELETE /api/feedback-posts/{id|uuid}/force
     |========================================================= */
    public function forceDelete(Request $r, string $idOrUuid)
    {
        if ($resp = $this->requireStaff($r)) return $resp;

        $w = $this->normalizeIdentifier($idOrUuid, null);

        $row = DB::table(self::TABLE)->where($w['raw_col'], $w['val'])->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        DB::table(self::TABLE)->where('id', $row->id)->delete();

        return response()->json(['success'=>true,'message'=>'Deleted permanently']);
    }
}
