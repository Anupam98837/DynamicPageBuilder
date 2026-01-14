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
     *
     * Example:
     *   safeLabelExpr('course_semesters','sem',['title','name'])
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

        // Safe fallback for departments/courses label columns
        $deptNameExpr = Schema::hasColumn('departments', 'name')
            ? 'd.name'
            : (Schema::hasColumn('departments', 'title') ? 'd.title' : 'NULL');

        $courseTitleExpr = Schema::hasColumn('courses', 'title')
            ? 'c.title'
            : (Schema::hasColumn('courses', 'name') ? 'c.name' : 'NULL');

        // ✅ FIX: sem/sec label expressions (no missing columns)
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

    $academicYear = $request->query('academic_year');
    $batch        = $request->query('batch');
    $session      = $request->query('session');

    $limit = (int) $request->query('limit', 200);
    $limit = max(1, min(500, $limit));

    // ✅ only students
    $studentRoles = ['student', 'students'];

    // dept/course labels (safe)
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

    $qb = DB::table('users as u')
        ->leftJoin($this->table . ' as sad', function ($join) {
            $join->on('sad.user_id', '=', 'u.id');
            // ignore deleted rows if column exists
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
              ->orWhereIn('u.role_short_form', ['STD','STU']); // fallback support
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

            // ✅ academic mapping
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
            'sad.status as academic_status',

            DB::raw("{$deptNameExpr} as department_name"),
            DB::raw("{$courseTitleExpr} as course_title"),
            DB::raw("{$semesterLabelExpr} as semester_title"),
            DB::raw("{$sectionLabelExpr} as section_title"),
        ]);

    // ✅ search (user + academic fields)
    if ($q !== '') {
        $like = '%' . $q . '%';
        $qb->where(function ($w) use ($like) {
            $w->where('u.name', 'like', $like)
              ->orWhere('u.email', 'like', $like)
              ->orWhere('u.phone_number', 'like', $like)
              ->orWhere('sad.roll_no', 'like', $like)
              ->orWhere('sad.registration_no', 'like', $like)
              ->orWhere('sad.admission_no', 'like', $like);
        });
    }

    // ✅ filters by academic details
    if ($departmentId) $qb->where('sad.department_id', $departmentId);
    if ($courseId)     $qb->where('sad.course_id', $courseId);
    if ($semesterId)   $qb->where('sad.semester_id', $semesterId);
    if ($sectionId)    $qb->where('sad.section_id', $sectionId);

    if ($academicYear) $qb->where('sad.academic_year', $academicYear);
    if ($batch)        $qb->where('sad.batch', $batch);
    if ($session)      $qb->where('sad.session', $session);

    // latest first (similar to users index)
    $rows = $qb->orderBy('u.id', 'desc')->limit($limit)->get();

    // ✅ normalize response like users index + "exists?" flag
    $items = $rows->map(function ($r) {
        $has = !empty($r->academic_id);

        return [
            'id'             => (int) $r->id,
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

            // ✅ this is what you asked: "if already exists then tell me"
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
                'status'          => (string) ($r->academic_status ?? ''),
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

        // ✅ FIX: sem/sec label expressions (no missing columns)
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
    // Only sync if users table actually has department_id
    if (!Schema::hasColumn('users', 'department_id')) return;

    // If departmentId is null, you can choose to skip; but your store requires it anyway
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

    $v = Validator::make($request->all(), [
        'user_id'         => ['required', 'integer', 'min:1', 'exists:users,id', Rule::unique($this->table, 'user_id')],
        'department_id'   => ['required', 'integer', 'min:1', 'exists:departments,id'],
        'course_id'       => ['required', 'integer', 'min:1', 'exists:courses,id'],
        'semester_id'     => ['nullable', 'integer', 'min:1', 'exists:course_semesters,id'],
        'section_id'      => ['nullable', 'integer', 'min:1', 'exists:course_semester_sections,id'],
        'academic_year'   => ['nullable', 'string', 'max:20'],
        'year'            => ['nullable', 'integer', 'min:1900', 'max:2200'],
        'roll_no'         => ['nullable', 'string', 'max:60', Rule::unique($this->table, 'roll_no')],
        'registration_no' => ['nullable', 'string', 'max:80', Rule::unique($this->table, 'registration_no')],
        'admission_no'    => ['nullable', 'string', 'max:80', Rule::unique($this->table, 'admission_no')],
        'admission_date'  => ['nullable', 'date'],
        'batch'           => ['nullable', 'string', 'max:40'],
        'session'         => ['nullable', 'string', 'max:40'],
        'status'          => ['nullable', 'string', 'max:20', Rule::in(['active','inactive','passed-out'])],
        'metadata'        => ['nullable'],
    ]);

    if ($v->fails()) return $this->fail($v->errors());

    try {
        $row = DB::transaction(function () use ($request, $v) {
            $actor = $this->actor($request);
            $payload = $v->validated();

            $payload['uuid'] = (string) Str::uuid();
            $payload['created_by'] = $actor['id'] ?: null;

            if (array_key_exists('metadata', $payload)) {
                $m = $payload['metadata'];
                if (is_string($m)) {
                    $decoded = json_decode($m, true);
                    $payload['metadata'] = json_last_error() === JSON_ERROR_NONE ? $decoded : ['value' => $m];
                } else {
                    $payload['metadata'] = $m;
                }
            }

            $now = now();
            $payload['created_at'] = $now;
            $payload['updated_at'] = $now;

            $id = DB::table($this->table)->insertGetId($payload);

            // ✅ Sync users.department_id with same value
            $this->syncUserDepartmentId((int)$payload['user_id'], (int)$payload['department_id']);

            return DB::table($this->table)->where('id', $id)->first();
        });

        return $this->ok($row, 'Student academic details created', 201);

    } catch (\Throwable $e) {
        return $this->fail(['server' => [$e->getMessage()]], 'Failed to create academic details', 500);
    }
}

// PUT /api/student-academic-details/{id}
public function update(Request $request, $id)
{
    if ($resp = $this->ensureTable()) return $resp;

    $id = (int) $id;

    $existing = DB::table($this->table)->where('id', $id)->first();
    if (!$existing) return $this->notFound('Student academic details not found');

    $v = Validator::make($request->all(), [
        'user_id'         => ['sometimes', 'required', 'integer', 'min:1', 'exists:users,id', Rule::unique($this->table, 'user_id')->ignore($id)],
        'department_id'   => ['sometimes', 'required', 'integer', 'min:1', 'exists:departments,id'],
        'course_id'       => ['sometimes', 'required', 'integer', 'min:1', 'exists:courses,id'],
        'semester_id'     => ['sometimes', 'nullable', 'integer', 'min:1', 'exists:course_semesters,id'],
        'section_id'      => ['sometimes', 'nullable', 'integer', 'min:1', 'exists:course_semester_sections,id'],
        'academic_year'   => ['sometimes', 'nullable', 'string', 'max:20'],
        'year'            => ['sometimes', 'nullable', 'integer', 'min:1900', 'max:2200'],
        'roll_no'         => ['sometimes', 'nullable', 'string', 'max:60', Rule::unique($this->table, 'roll_no')->ignore($id)],
        'registration_no' => ['sometimes', 'nullable', 'string', 'max:80', Rule::unique($this->table, 'registration_no')->ignore($id)],
        'admission_no'    => ['sometimes', 'nullable', 'string', 'max:80', Rule::unique($this->table, 'admission_no')->ignore($id)],
        'admission_date'  => ['sometimes', 'nullable', 'date'],
        'batch'           => ['sometimes', 'nullable', 'string', 'max:40'],
        'session'         => ['sometimes', 'nullable', 'string', 'max:40'],
        'status'          => ['sometimes', 'required', 'string', 'max:20', Rule::in(['active','inactive','passed-out'])],
        'metadata'        => ['sometimes', 'nullable'],
    ]);

    if ($v->fails()) return $this->fail($v->errors());

    try {
        $row = DB::transaction(function () use ($id, $existing, $v) {
            $payload = $v->validated();

            if (array_key_exists('metadata', $payload)) {
                $m = $payload['metadata'];
                if (is_string($m)) {
                    $decoded = json_decode($m, true);
                    $payload['metadata'] = json_last_error() === JSON_ERROR_NONE ? $decoded : ['value' => $m];
                } else {
                    $payload['metadata'] = $m;
                }
            }

            $payload['updated_at'] = now();

            DB::table($this->table)->where('id', $id)->update($payload);

            // ✅ If department_id was sent, sync users.department_id too
            if (array_key_exists('department_id', $payload)) {
                $finalUserId = (int) (array_key_exists('user_id', $payload) ? $payload['user_id'] : $existing->user_id);
                $finalDeptId = (int) $payload['department_id'];

                $this->syncUserDepartmentId($finalUserId, $finalDeptId);
            }

            return DB::table($this->table)->where('id', $id)->first();
        });

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

        if (Schema::hasColumn($this->table, 'deleted_at')) {
            DB::table($this->table)->where('id', $id)->update(['deleted_at' => now()]);
        } else {
            DB::table($this->table)->where('id', $id)->delete();
        }

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

        DB::table($this->table)->where('id', $id)->update(['deleted_at' => null, 'updated_at' => now()]);

        $fresh = DB::table($this->table)->where('id', $id)->first();
        return $this->ok($fresh, 'Student academic details restored');
    }
}
