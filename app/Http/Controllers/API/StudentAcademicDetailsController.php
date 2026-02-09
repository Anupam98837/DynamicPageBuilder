<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class StudentAcademicDetailsController extends Controller
{
    private string $table = 'student_academic_details';
    private string $activityLogTable = 'user_data_activity_log';

    /* ============================================
     | Helpers
     |============================================ */

    private function actor(Request $request): array
    {
        return [
            'role' => (string) $request->attributes->get('auth_role'),
            'type' => (string) $request->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    private function ok($data = null, string $message = 'OK', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    private function fail($errors, string $message = 'Validation failed', int $code = 422)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }

    private function notFound(string $message = 'Not found', int $code = 404)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }

    private function ensureTable(): ?\Illuminate\Http\JsonResponse
    {
        if (!Schema::hasTable($this->table)) {
            return response()->json([
                'success' => false,
                'message' => "Table '{$this->table}' not found. Run migrations first.",
            ], 500);
        }
        return null;
    }

    private function canLogActivity(): bool
    {
        return Schema::hasTable($this->activityLogTable);
    }

    /**
     * Write activity log safely (never breaks main flow).
     */
    private function logActivity(
        Request $request,
        string $activity,
        string $module,
        string $tableName,
        ?int $recordId = null,
        ?array $changedFields = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $note = null
    ): void {
        if (!$this->canLogActivity()) return;

        try {
            $actor = $this->actor($request);

            $ua = (string) ($request->userAgent() ?? '');
            if (strlen($ua) > 512) {
                $ua = substr($ua, 0, 512);
            }

            DB::table($this->activityLogTable)->insert([
                'performed_by'      => (int) ($actor['id'] ?? 0),
                'performed_by_role' => (string) ($actor['role'] ?? ''),
                'ip'                => (string) ($request->ip() ?? ''),
                'user_agent'        => $ua,

                'activity'          => $activity,
                'module'            => $module,

                'table_name'        => $tableName,
                'record_id'         => $recordId,

                'changed_fields'    => $changedFields ? array_values($changedFields) : null,
                'old_values'        => $oldValues,
                'new_values'        => $newValues,

                'log_note'          => $note,

                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            // swallow logging errors to avoid hurting main functionality
        }
    }

    /**
     * Resolve UUID inputs into ID payloads for accurate logging / updates.
     */
    private function resolveIdsFromPayload(array $payload): array
    {
        // user_uuid -> user_id
        if (empty($payload['user_id']) && !empty($payload['user_uuid'])) {
            $uid = $this->idFromUuid('users', $payload['user_uuid']);
            if ($uid) $payload['user_id'] = $uid;
        }

        // course_uuid -> course_id
        if (empty($payload['course_id']) && !empty($payload['course_uuid'])) {
            $cid = $this->idFromUuid('courses', $payload['course_uuid']);
            if ($cid) $payload['course_id'] = $cid;
        }

        // semester_uuid -> semester_id (optional)
        if (empty($payload['semester_id']) && !empty($payload['semester_uuid'])) {
            $sid = $this->idFromUuid('course_semesters', $payload['semester_uuid']);
            if ($sid) $payload['semester_id'] = $sid;
        }

        // section_uuid -> section_id (optional)
        if (empty($payload['section_id']) && !empty($payload['section_uuid'])) {
            $secId = $this->idFromUuid('course_semester_sections', $payload['section_uuid']);
            if ($secId) $payload['section_id'] = $secId;
        }

        // remove uuid-only keys (db columns don’t exist)
        unset($payload['user_uuid'], $payload['course_uuid'], $payload['semester_uuid'], $payload['section_uuid']);

        return $payload;
    }

    /**
     * Compute diffs only for fields that truly changed.
     */
    private function computeChangedFields(array $oldSubset, array $newSubset): array
    {
        $changed = [];
        foreach ($newSubset as $k => $newVal) {
            $oldVal = $oldSubset[$k] ?? null;

            // normalize dates/objects to strings
            if ($oldVal instanceof \DateTimeInterface) $oldVal = $oldVal->format('Y-m-d H:i:s');
            if ($newVal instanceof \DateTimeInterface) $newVal = $newVal->format('Y-m-d H:i:s');

            // normalize booleans
            if (is_bool($oldVal)) $oldVal = $oldVal ? 1 : 0;
            if (is_bool($newVal)) $newVal = $newVal ? 1 : 0;

            // compare
            if ($oldVal !== $newVal) {
                $changed[] = $k;
            }
        }
        return $changed;
    }

    /**
     * Build a COALESCE expression for "student_name"
     * that only references columns that actually exist.
     */
    private function studentNameExpr(): string
    {
        $parts = [];

        if (Schema::hasColumn('users', 'name')) $parts[] = 'u.name';
        if (Schema::hasColumn('users', 'full_name')) $parts[] = 'u.full_name';
        if (Schema::hasColumn('users', 'username'))  $parts[] = 'u.username';

        $parts[] = 'u.email';

        return 'COALESCE(' . implode(', ', $parts) . ')';
    }

    /**
     * Build a safe label expression for any joined table alias
     * using only columns that exist in DB.
     */
    private function safeLabelExpr(string $table, string $alias, array $columns, string $fallback = 'NULL'): string
    {
        if (!Schema::hasTable($table)) return $fallback;

        $parts = [];
        foreach ($columns as $col) {
            if (Schema::hasColumn($table, $col)) {
                $parts[] = "{$alias}.{$col}";
            }
        }

        if (count($parts) === 0) return $fallback;
        if (count($parts) === 1) return $parts[0];

        return 'COALESCE(' . implode(', ', $parts) . ')';
    }

    /**
     * Apply search across academic + user fields safely.
     */
    private function applySearch($qb, string $q)
    {
        $like = '%' . $q . '%';
        $hasFullName = Schema::hasColumn('users', 'full_name');
        $hasUsername = Schema::hasColumn('users', 'username');

        return $qb->where(function ($w) use ($like, $hasFullName, $hasUsername) {
            $w->where('sad.roll_no', 'like', $like)
              ->orWhere('sad.registration_no', 'like', $like)
              ->orWhere('sad.admission_no', 'like', $like)
              ->orWhere('sad.academic_year', 'like', $like)
              ->orWhere('sad.batch', 'like', $like)
              ->orWhere('sad.session', 'like', $like)
              ->orWhere('u.email', 'like', $like)
              ->orWhere('u.name', 'like', $like);

            if (Schema::hasColumn('student_academic_details', 'attendance_percentage')) {
                $w->orWhere('sad.attendance_percentage', 'like', $like);
            }

            if ($hasFullName) $w->orWhere('u.full_name', 'like', $like);
            if ($hasUsername) $w->orWhere('u.username', 'like', $like);
        });
    }

    /* ============================================
     | CRUD
     |============================================ */

    // GET /api/student-academic-details
    public function index(Request $request)
    {
        if ($resp = $this->ensureTable()) return $resp;

        $q            = trim((string) $request->query('q', ''));
        $status       = $request->query('status');
        $departmentId = $request->query('department_id');
        $courseId     = $request->query('course_id');
        $semesterId   = $request->query('semester_id');
        $sectionId    = $request->query('section_id');
        $academicYear = $request->query('academic_year');
        $batch        = $request->query('batch');
        $session      = $request->query('session');

        $deptNameExpr = Schema::hasColumn('departments', 'name')
            ? 'd.name'
            : (Schema::hasColumn('departments', 'title') ? 'd.title' : 'NULL');

        $courseTitleExpr = Schema::hasColumn('courses', 'title')
            ? 'c.title'
            : (Schema::hasColumn('courses', 'name') ? 'c.name' : 'NULL');

        $semesterLabelExpr = $this->safeLabelExpr('course_semesters', 'sem', [
            'title', 'name', 'semester_title', 'semester_name', 'label'
        ]);

        $sectionLabelExpr = $this->safeLabelExpr('course_semester_sections', 'sec', [
            'title', 'name', 'section_title', 'section_name', 'label'
        ]);

        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min(200, $perPage));

        $qb = DB::table($this->table . ' as sad')
            ->leftJoin('users as u', 'u.id', '=', 'sad.user_id')
            ->leftJoin('departments as d', 'd.id', '=', 'sad.department_id')
            ->leftJoin('courses as c', 'c.id', '=', 'sad.course_id')
            ->leftJoin('course_semesters as sem', 'sem.id', '=', 'sad.semester_id')
            ->leftJoin('course_semester_sections as sec', 'sec.id', '=', 'sad.section_id')
            ->select([
                'sad.*',
                DB::raw($this->studentNameExpr() . " as student_name"),
                'u.email as student_email',
                DB::raw("{$deptNameExpr} as department_name"),
                DB::raw("{$courseTitleExpr} as course_title"),
                DB::raw("{$semesterLabelExpr} as semester_title"),
                DB::raw("{$sectionLabelExpr} as section_title"),
            ]);

        if ($q !== '') {
            $this->applySearch($qb, $q);
        }

        $qb->when($status !== null && $status !== '', fn($x) => $x->where('sad.status', $status))
           ->when($departmentId, fn($x) => $x->where('sad.department_id', $departmentId))
           ->when($courseId, fn($x) => $x->where('sad.course_id', $courseId))
           ->when($semesterId, fn($x) => $x->where('sad.semester_id', $semesterId))
           ->when($sectionId, fn($x) => $x->where('sad.section_id', $sectionId))
           ->when($academicYear, fn($x) => $x->where('sad.academic_year', $academicYear))
           ->when($batch, fn($x) => $x->where('sad.batch', $batch))
           ->when($session, fn($x) => $x->where('sad.session', $session))
           ->orderByDesc('sad.id');

        $rows = $qb->paginate($perPage);

        return $this->ok($rows, 'Student academic details fetched');
    }

    // GET /api/students-by-academics
    public function studentsByAcademics(Request $request)
    {
        if ($resp = $this->ensureTable()) return $resp;

        $q            = trim((string) $request->query('q', ''));
        $status       = trim((string) $request->query('status', 'active')) ?: 'active';

        $departmentId = $request->query('department_id');
        $courseId     = $request->query('course_id');
        $semesterId   = $request->query('semester_id');
        $sectionId    = $request->query('section_id');

        $subjectId    = $request->query('subject_id');

        $academicYear = $request->query('academic_year');
        $batch        = $request->query('batch');
        $session      = $request->query('session');

        $limit = (int) ($request->query('limit', $request->query('per_page', 200)));
        $limit = max(1, min(500, $limit));

        $studentRoles = ['student', 'students'];

        $deptNameExpr = Schema::hasColumn('departments', 'name')
            ? 'd.name'
            : (Schema::hasColumn('departments', 'title') ? 'd.title' : 'NULL');

        $courseTitleExpr = Schema::hasColumn('courses', 'title')
            ? 'c.title'
            : (Schema::hasColumn('courses', 'name') ? 'c.name' : 'NULL');

        $semesterLabelExpr = $this->safeLabelExpr('course_semesters', 'sem', [
            'title', 'name', 'semester_title', 'semester_name', 'label'
        ]);

        $sectionLabelExpr = $this->safeLabelExpr('course_semester_sections', 'sec', [
            'title', 'name', 'section_title', 'section_name', 'label'
        ]);

        $subjectAttendanceMap = [];
        $subjectIdInt = null;

        if ($subjectId !== null && $subjectId !== '' && preg_match('/^\d+$/', (string)$subjectId)) {
            $subjectIdInt = (int) $subjectId;

            $ss = DB::table('student_subject')
                ->whereNull('deleted_at')
                ->where('status', 'active');

            if ($departmentId) $ss->where('department_id', $departmentId);
            if ($courseId)     $ss->where('course_id', $courseId);
            if ($semesterId)   $ss->where('semester_id', $semesterId);

            $ssRows = $ss->select(['id','subject_json'])->orderBy('id', 'desc')->limit(50)->get();

            foreach ($ssRows as $row) {
                $json = $row->subject_json;

                $arr = null;
                if (is_array($json)) {
                    $arr = $json;
                } elseif (is_string($json)) {
                    $arr = json_decode($json, true);
                    if (json_last_error() !== JSON_ERROR_NONE) $arr = null;
                }

                if (!is_array($arr)) continue;

                foreach ($arr as $it) {
                    $sid = isset($it['student_id']) ? (int)$it['student_id'] : 0;
                    $sub = isset($it['subject_id']) ? (int)$it['subject_id'] : 0;

                    if ($sid <= 0 || $sub !== $subjectIdInt) continue;

                    $att = $it['current_attendance'] ?? null;
                    $att = ($att === null || $att === '') ? null : (float)$att;

                    $subjectAttendanceMap[$sid] = $att;
                }
            }

            if (empty($subjectAttendanceMap)) {
                return response()->json([
                    'success' => true,
                    'data'    => [],
                ]);
            }
        }

        $qb = DB::table('users as u')
            ->leftJoin($this->table . ' as sad', function ($join) {
                $join->on('sad.user_id', '=', 'u.id');
                if (Schema::hasColumn('student_academic_details', 'deleted_at')) {
                    $join->whereNull('sad.deleted_at');
                }
            })
            ->leftJoin('departments as d', 'd.id', '=', 'sad.department_id')
            ->leftJoin('courses as c', 'c.id', '=', 'sad.course_id')
            ->leftJoin('course_semesters as sem', 'sem.id', '=', 'sad.semester_id')
            ->leftJoin('course_semester_sections as sec', 'sec.id', '=', 'sad.section_id')
            ->whereNull('u.deleted_at')
            ->where('u.status', $status)
            ->where(function ($w) use ($studentRoles) {
                $w->whereIn('u.role', $studentRoles)
                  ->orWhereIn('u.role_short_form', ['STD','STU']);
            })
            ->select([
                'u.id',
                'u.uuid',
                'u.slug',
                'u.name',
                'u.email',
                'u.phone_number',
                'u.image',
                'u.role',
                'u.role_short_form',
                'u.status',
                'u.created_at',
                'u.updated_at',

                'sad.id as academic_id',
                'sad.uuid as academic_uuid',
                'sad.department_id',
                'sad.course_id',
                'sad.semester_id',
                'sad.section_id',
                'sad.academic_year',
                'sad.year',
                'sad.roll_no',
                'sad.registration_no',
                'sad.admission_no',
                'sad.admission_date',
                'sad.batch',
                'sad.session',

                'sad.attendance_percentage',

                'sad.status as academic_status',

                DB::raw("{$deptNameExpr} as department_name"),
                DB::raw("{$courseTitleExpr} as course_title"),
                DB::raw("{$semesterLabelExpr} as semester_title"),
                DB::raw("{$sectionLabelExpr} as section_title"),
            ]);

        if ($q !== '') {
            $like = '%' . $q . '%';
            $qb->where(function ($w) use ($like) {
                $w->where('u.name', 'like', $like)
                  ->orWhere('u.email', 'like', $like)
                  ->orWhere('u.phone_number', 'like', $like)
                  ->orWhere('sad.roll_no', 'like', $like)
                  ->orWhere('sad.registration_no', 'like', $like)
                  ->orWhere('sad.admission_no', 'like', $like);

                if (Schema::hasColumn('student_academic_details', 'attendance_percentage')) {
                    $w->orWhere('sad.attendance_percentage', 'like', $like);
                }
            });
        }

        if ($departmentId) $qb->where('sad.department_id', $departmentId);
        if ($courseId)     $qb->where('sad.course_id', $courseId);
        if ($semesterId)   $qb->where('sad.semester_id', $semesterId);
        if ($sectionId)    $qb->where('sad.section_id', $sectionId);

        if ($academicYear) $qb->where('sad.academic_year', $academicYear);
        if ($batch)        $qb->where('sad.batch', $batch);
        if ($session)      $qb->where('sad.session', $session);

        $rows = $qb->orderBy('u.id', 'desc')->limit($limit)->get();

        if ($subjectIdInt !== null) {
            $allowedIds = array_keys($subjectAttendanceMap);
            $rows = $rows->filter(fn($r) => in_array((int)$r->id, $allowedIds, true))->values();
        }

        $items = $rows->map(function ($r) use ($subjectIdInt, $subjectAttendanceMap) {
            $has = !empty($r->academic_id);
            $uid = (int) $r->id;

            $subjectAttendance = null;
            if ($subjectIdInt !== null) {
                $subjectAttendance = array_key_exists($uid, $subjectAttendanceMap)
                    ? $subjectAttendanceMap[$uid]
                    : null;
            }

            return [
                'id'             => $uid,
                'uuid'           => (string) $r->uuid,
                'slug'           => (string) ($r->slug ?? ''),
                'name'           => (string) ($r->name ?? ''),
                'email'          => (string) ($r->email ?? ''),
                'phone_number'   => (string) ($r->phone_number ?? ''),
                'image'          => (string) ($r->image ?? ''),
                'role'           => (string) ($r->role ?? ''),
                'role_short_form'=> (string) ($r->role_short_form ?? ''),
                'status'         => (string) ($r->status ?? ''),
                'created_at'     => $r->created_at,
                'updated_at'     => $r->updated_at,

                'has_academic_details' => $has,

                'academic_details' => $has ? [
                    'id'              => (int) $r->academic_id,
                    'uuid'            => (string) ($r->academic_uuid ?? ''),
                    'department_id'   => $r->department_id ? (int) $r->department_id : null,
                    'department_name' => (string) ($r->department_name ?? ''),
                    'course_id'       => $r->course_id ? (int) $r->course_id : null,
                    'course_title'    => (string) ($r->course_title ?? ''),
                    'semester_id'     => $r->semester_id ? (int) $r->semester_id : null,
                    'semester_title'  => (string) ($r->semester_title ?? ''),
                    'section_id'      => $r->section_id ? (int) $r->section_id : null,
                    'section_title'   => (string) ($r->section_title ?? ''),

                    'academic_year'   => (string) ($r->academic_year ?? ''),
                    'year'            => $r->year !== null ? (int) $r->year : null,
                    'roll_no'         => (string) ($r->roll_no ?? ''),
                    'registration_no' => (string) ($r->registration_no ?? ''),
                    'admission_no'    => (string) ($r->admission_no ?? ''),
                    'admission_date'  => $r->admission_date,
                    'batch'           => (string) ($r->batch ?? ''),
                    'session'         => (string) ($r->session ?? ''),

                    'attendance_percentage' => $r->attendance_percentage !== null
                        ? (float) $r->attendance_percentage
                        : null,

                    'status'          => (string) ($r->academic_status ?? ''),

                    'subject_id'         => $subjectIdInt,
                    'subject_attendance' => $subjectAttendance,
                ] : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data'    => $items,
        ]);
    }

    // GET /api/student-academic-details/{id}
    public function show(Request $request, $id)
    {
        if ($resp = $this->ensureTable()) return $resp;

        $deptNameExpr = Schema::hasColumn('departments', 'name')
            ? 'd.name'
            : (Schema::hasColumn('departments', 'title') ? 'd.title' : 'NULL');

        $courseTitleExpr = Schema::hasColumn('courses', 'title')
            ? 'c.title'
            : (Schema::hasColumn('courses', 'name') ? 'c.name' : 'NULL');

        $semesterLabelExpr = $this->safeLabelExpr('course_semesters', 'sem', [
            'title', 'name', 'semester_title', 'semester_name', 'label'
        ]);

        $sectionLabelExpr = $this->safeLabelExpr('course_semester_sections', 'sec', [
            'title', 'name', 'section_title', 'section_name', 'label'
        ]);

        $row = DB::table($this->table . ' as sad')
            ->leftJoin('users as u', 'u.id', '=', 'sad.user_id')
            ->leftJoin('departments as d', 'd.id', '=', 'sad.department_id')
            ->leftJoin('courses as c', 'c.id', '=', 'sad.course_id')
            ->leftJoin('course_semesters as sem', 'sem.id', '=', 'sad.semester_id')
            ->leftJoin('course_semester_sections as sec', 'sec.id', '=', 'sad.section_id')
            ->select([
                'sad.*',
                DB::raw($this->studentNameExpr() . " as student_name"),
                'u.email as student_email',
                DB::raw("{$deptNameExpr} as department_name"),
                DB::raw("{$courseTitleExpr} as course_title"),
                DB::raw("{$semesterLabelExpr} as semester_title"),
                DB::raw("{$sectionLabelExpr} as section_title"),
            ])
            ->where('sad.id', (int) $id)
            ->first();

        if (!$row) return $this->notFound('Student academic details not found');
        return $this->ok($row, 'Student academic details found');
    }

    private function syncUserDepartmentId(int $userId, ?int $departmentId): void
    {
        if (!Schema::hasColumn('users', 'department_id')) return;
        if (!$departmentId) return;

        DB::table('users')
            ->where('id', $userId)
            ->update([
                'department_id' => $departmentId,
                'updated_at'    => now(),
            ]);
    }

    // POST /api/student-academic-details
    public function store(Request $request)
    {
        if ($resp = $this->ensureTable()) return $resp;

        $input = $this->normalizeInput($request->all(), [
            'semester_id','section_id',
            'semester_uuid','section_uuid',
            'academic_year','roll_no','registration_no','admission_no','batch','session','status'
        ]);

        $v = Validator::make($input, [
            'user_id'       => ['required_without:user_uuid', 'integer', 'min:1', 'exists:users,id', Rule::unique($this->table, 'user_id')],
            'user_uuid'     => ['required_without:user_id', 'uuid', 'exists:users,uuid'],

            'department_id' => ['required', 'integer', 'min:1', 'exists:departments,id'],

            'course_id'     => ['required_without:course_uuid', 'integer', 'min:1', 'exists:courses,id'],
            'course_uuid'   => ['required_without:course_id', 'uuid', 'exists:courses,uuid'],

            'semester_id'   => ['nullable', 'integer', 'min:1', 'exists:course_semesters,id'],
            'semester_uuid' => ['nullable', 'uuid', 'exists:course_semesters,uuid'],

            'section_id'    => ['nullable', 'integer', 'min:1', 'exists:course_semester_sections,id'],
            'section_uuid'  => ['nullable', 'uuid', 'exists:course_semester_sections,uuid'],

            'academic_year'   => ['nullable', 'string', 'max:20'],
            'year'            => ['nullable', 'integer', 'min:1900', 'max:2200'],

            'roll_no'         => ['nullable', 'string', 'max:60', Rule::unique($this->table, 'roll_no')],
            'registration_no' => ['nullable', 'string', 'max:80', Rule::unique($this->table, 'registration_no')],
            'admission_no'    => ['nullable', 'string', 'max:80', Rule::unique($this->table, 'admission_no')],

            'admission_date'  => ['nullable', 'date'],
            'batch'           => ['nullable', 'string', 'max:40'],
            'session'         => ['nullable', 'string', 'max:40'],

            'attendance_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'status' => ['nullable', 'string', 'max:20', Rule::in(['active','inactive','passed-out'])],
        ]);

        if ($v->fails()) return $this->fail($v->errors());

        // prepare log payload (resolved to ids) without breaking DB logic
        $logPayload = $this->resolveIdsFromPayload($v->validated());

        try {
            $row = DB::transaction(function () use ($request, $v) {
                $actor   = $this->actor($request);
                $payload = $v->validated();

                if (empty($payload['user_id']) && !empty($payload['user_uuid'])) {
                    $uid = $this->idFromUuid('users', $payload['user_uuid']);
                    if (!$uid) throw new \Exception("Invalid user_uuid (not found).");
                    $payload['user_id'] = $uid;
                }

                if (empty($payload['course_id']) && !empty($payload['course_uuid'])) {
                    $cid = $this->idFromUuid('courses', $payload['course_uuid']);
                    if (!$cid) throw new \Exception("Invalid course_uuid (not found).");
                    $payload['course_id'] = $cid;
                }

                if (empty($payload['semester_id']) && !empty($payload['semester_uuid'])) {
                    $payload['semester_id'] = $this->idFromUuid('course_semesters', $payload['semester_uuid']);
                }

                if (empty($payload['section_id']) && !empty($payload['section_uuid'])) {
                    $payload['section_id'] = $this->idFromUuid('course_semester_sections', $payload['section_uuid']);
                }

                unset($payload['user_uuid'], $payload['course_uuid'], $payload['semester_uuid'], $payload['section_uuid']);

                $payload['uuid']       = (string) Str::uuid();
                $payload['created_by'] = $actor['id'] ?: null;

                $now = now();
                $payload['created_at'] = $now;
                $payload['updated_at'] = $now;

                $id = DB::table($this->table)->insertGetId($payload);

                $this->syncUserDepartmentId((int)$payload['user_id'], (int)$payload['department_id']);

                return DB::table($this->table)->where('id', $id)->first();
            });

            // ✅ activity log (create)
            $newValues = $logPayload;
            $newValues['id']   = isset($row->id) ? (int) $row->id : null;
            $newValues['uuid'] = isset($row->uuid) ? (string) $row->uuid : null;

            $this->logActivity(
                $request,
                'create',
                'student_academic_details',
                $this->table,
                isset($row->id) ? (int) $row->id : null,
                array_keys($newValues),
                null,
                $newValues,
                'Created student academic details'
            );

            return $this->ok($row, 'Student academic details created', 201);

        } catch (\Throwable $e) {
            return $this->fail(['server' => [$e->getMessage()]], 'Failed to create academic details', 500);
        }
    }

    // PUT/PATCH /api/student-academic-details/{id}
    public function update(Request $request, $id)
    {
        if ($resp = $this->ensureTable()) return $resp;

        $id = (int) $id;

        $existing = DB::table($this->table)->where('id', $id)->first();
        if (!$existing) return $this->notFound('Student academic details not found');

        $input = $this->normalizeInput($request->all(), [
            'semester_id','section_id',
            'semester_uuid','section_uuid',
            'academic_year','roll_no','registration_no','admission_no','batch','session','status'
        ]);

        $v = Validator::make($input, [
            'user_id'       => ['sometimes', 'required_without:user_uuid', 'integer', 'min:1', 'exists:users,id', Rule::unique($this->table, 'user_id')->ignore($id)],
            'user_uuid'     => ['sometimes', 'required_without:user_id', 'uuid', 'exists:users,uuid'],

            'department_id' => ['sometimes', 'required', 'integer', 'min:1', 'exists:departments,id'],

            'course_id'     => ['sometimes', 'required_without:course_uuid', 'integer', 'min:1', 'exists:courses,id'],
            'course_uuid'   => ['sometimes', 'required_without:course_id', 'uuid', 'exists:courses,uuid'],

            'semester_id'   => ['sometimes', 'nullable', 'integer', 'min:1', 'exists:course_semesters,id'],
            'semester_uuid' => ['sometimes', 'nullable', 'uuid', 'exists:course_semesters,uuid'],

            'section_id'    => ['sometimes', 'nullable', 'integer', 'min:1', 'exists:course_semester_sections,id'],
            'section_uuid'  => ['sometimes', 'nullable', 'uuid', 'exists:course_semester_sections,uuid'],

            'academic_year'   => ['sometimes', 'nullable', 'string', 'max:20'],
            'year'            => ['sometimes', 'nullable', 'integer', 'min:1900', 'max:2200'],

            'roll_no'         => ['sometimes', 'nullable', 'string', 'max:60', Rule::unique($this->table, 'roll_no')->ignore($id)],
            'registration_no' => ['sometimes', 'nullable', 'string', 'max:80', Rule::unique($this->table, 'registration_no')->ignore($id)],
            'admission_no'    => ['sometimes', 'nullable', 'string', 'max:80', Rule::unique($this->table, 'admission_no')->ignore($id)],

            'admission_date'  => ['sometimes', 'nullable', 'date'],
            'batch'           => ['sometimes', 'nullable', 'string', 'max:40'],
            'session'         => ['sometimes', 'nullable', 'string', 'max:40'],

            'attendance_percentage' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],

            'status' => ['sometimes', 'nullable', 'string', 'max:20', Rule::in(['active','inactive','passed-out'])],
        ]);

        if ($v->fails()) return $this->fail($v->errors());

        // resolved payload for logging
        $logPayload = $this->resolveIdsFromPayload($v->validated());

        try {
            $row = DB::transaction(function () use ($id, $existing, $v) {
                $payload = $v->validated();

                if (array_key_exists('user_uuid', $payload) && empty($payload['user_id'])) {
                    $uid = $this->idFromUuid('users', $payload['user_uuid']);
                    if (!$uid) throw new \Exception("Invalid user_uuid (not found).");
                    $payload['user_id'] = $uid;
                }

                if (array_key_exists('course_uuid', $payload) && empty($payload['course_id'])) {
                    $cid = $this->idFromUuid('courses', $payload['course_uuid']);
                    if (!$cid) throw new \Exception("Invalid course_uuid (not found).");
                    $payload['course_id'] = $cid;
                }

                if (array_key_exists('semester_uuid', $payload) && empty($payload['semester_id'])) {
                    $payload['semester_id'] = $this->idFromUuid('course_semesters', $payload['semester_uuid']);
                }

                if (array_key_exists('section_uuid', $payload) && empty($payload['section_id'])) {
                    $payload['section_id'] = $this->idFromUuid('course_semester_sections', $payload['section_uuid']);
                }

                unset($payload['user_uuid'], $payload['course_uuid'], $payload['semester_uuid'], $payload['section_uuid']);

                $payload['updated_at'] = now();

                DB::table($this->table)->where('id', $id)->update($payload);

                if (array_key_exists('department_id', $payload)) {
                    $finalUserId = (int) (array_key_exists('user_id', $payload) ? $payload['user_id'] : $existing->user_id);
                    $finalDeptId = (int) $payload['department_id'];
                    $this->syncUserDepartmentId($finalUserId, $finalDeptId);
                }

                return DB::table($this->table)->where('id', $id)->first();
            });

            // ✅ activity log (update) - store only actually changed fields
            $oldArr = (array) $existing;
            $newArr = (array) $row;

            $oldSubset = array_intersect_key($oldArr, $logPayload);
            $newSubset = array_intersect_key($newArr, $logPayload);

            $changedFields = $this->computeChangedFields($oldSubset, $newSubset);

            $this->logActivity(
                $request,
                'update',
                'student_academic_details',
                $this->table,
                $id,
                $changedFields,
                $changedFields ? array_intersect_key($oldSubset, array_flip($changedFields)) : null,
                $changedFields ? array_intersect_key($newSubset, array_flip($changedFields)) : null,
                'Updated student academic details'
            );

            return $this->ok($row, 'Student academic details updated');

        } catch (\Throwable $e) {
            return $this->fail(['server' => [$e->getMessage()]], 'Failed to update academic details', 500);
        }
    }

    // DELETE /api/student-academic-details/{id}
    public function destroy(Request $request, $id)
    {
        if ($resp = $this->ensureTable()) return $resp;

        $id = (int) $id;

        $existing = DB::table($this->table)->where('id', $id)->first();
        if (!$existing) return $this->notFound('Student academic details not found');

        $soft = false;
        if (Schema::hasColumn($this->table, 'deleted_at')) {
            $soft = true;
            DB::table($this->table)->where('id', $id)->update(['deleted_at' => now()]);
        } else {
            DB::table($this->table)->where('id', $id)->delete();
        }

        // ✅ activity log (delete)
        $note = $soft ? 'Soft deleted student academic details' : 'Hard deleted student academic details';

        $oldValues = (array) $existing;
        $newValues = null;

        if ($soft) {
            $fresh = DB::table($this->table)->where('id', $id)->first();
            $freshArr = $fresh ? (array) $fresh : [];
            $newValues = [
                'deleted_at' => $freshArr['deleted_at'] ?? null,
            ];
        }

        $this->logActivity(
            $request,
            'delete',
            'student_academic_details',
            $this->table,
            $id,
            $soft ? ['deleted_at'] : ['*'],
            $soft ? ['deleted_at' => $oldValues['deleted_at'] ?? null] : $oldValues,
            $newValues,
            $note
        );

        return $this->ok(['id' => $id], 'Student academic details deleted');
    }

    // POST /api/student-academic-details/{id}/restore
    public function restore(Request $request, $id)
    {
        if ($resp = $this->ensureTable()) return $resp;

        $id = (int) $id;

        if (!Schema::hasColumn($this->table, 'deleted_at')) {
            return response()->json([
                'success' => false,
                'message' => "Restore not supported because '{$this->table}' has no deleted_at column.",
            ], 400);
        }

        $row = DB::table($this->table)->where('id', $id)->first();
        if (!$row) return $this->notFound('Student academic details not found');

        $old = (array) $row;

        DB::table($this->table)->where('id', $id)->update(['deleted_at' => null, 'updated_at' => now()]);

        $fresh = DB::table($this->table)->where('id', $id)->first();

        // ✅ activity log (restore)
        $this->logActivity(
            $request,
            'restore',
            'student_academic_details',
            $this->table,
            $id,
            ['deleted_at'],
            ['deleted_at' => $old['deleted_at'] ?? null],
            ['deleted_at' => null],
            'Restored student academic details'
        );

        return $this->ok($fresh, 'Student academic details restored');
    }

    /**
     * ✅ normalize CSV/API input:
     * - trim strings
     * - convert "" -> null for given keys
     * - supports acad_status -> status
     */
    private function normalizeInput(array $data, array $nullables = []): array
    {
        foreach ($data as $k => $v) {
            if (is_string($v)) {
                $data[$k] = trim($v);
            }
        }

        foreach ($nullables as $key) {
            if (array_key_exists($key, $data)) {
                $val = $data[$key];
                if ($val === '' || $val === 'null' || $val === 'NULL' || $val === 'undefined') {
                    $data[$key] = null;
                }
            }
        }

        if (!isset($data['status']) && isset($data['acad_status'])) {
            $data['status'] = $data['acad_status'];
        }

        return $data;
    }

    /**
     * ✅ Resolve FK id by uuid (e.g courses.uuid -> courses.id)
     */
    private function idFromUuid(string $table, ?string $uuid): ?int
    {
        if (!$uuid) return null;
        if (!Schema::hasTable($table)) return null;
        if (!Schema::hasColumn($table, 'uuid')) return null;

        $id = DB::table($table)->where('uuid', $uuid)->value('id');
        return $id ? (int) $id : null;
    }
}
