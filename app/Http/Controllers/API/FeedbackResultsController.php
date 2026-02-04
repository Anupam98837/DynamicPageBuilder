<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FeedbackResultsController extends Controller
{
    private const POSTS     = 'feedback_posts';
    private const SUBS      = 'feedback_submissions';
    private const QUESTIONS = 'feedback_questions';
    private const USERS     = 'users';

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

    private function requireStaff(Request $r)
    {
        $role = strtolower((string)($this->actor($r)['role'] ?? ''));
        $allowed = ['admin','director','principal','hod','faculty','technical_assistant','it_person'];
        if (!in_array($role, $allowed, true)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized Access'], 403);
        }
        return null;
    }

    private function tableExists(string $t): bool
    {
        try { return Schema::hasTable($t); } catch (\Throwable $e) { return false; }
    }

    private function pickNameColumn(string $table, array $candidates, string $fallback='id'): string
    {
        foreach ($candidates as $c) {
            if (Schema::hasColumn($table, $c)) return $c;
        }
        return $fallback;
    }

    private function toInt($v): ?int
    {
        if ($v === null) return null;
        $s = trim((string)$v);
        if ($s === '') return null;
        return is_numeric($s) ? (int)$s : null;
    }

    private function initDist(): array
    {
        return [
            'counts' => ['5'=>0,'4'=>0,'3'=>0,'2'=>0,'1'=>0],
            'total'  => 0,
            'avg'    => null,
        ];
    }

    private function finalizeDist(array &$dist): void
    {
        $total = (int)($dist['total'] ?? 0);
        if ($total <= 0) {
            $dist['avg'] = null;
            return;
        }

        $sum = 0;
        foreach ([5,4,3,2,1] as $s) {
            $sum += $s * (int)($dist['counts'][(string)$s] ?? 0);
        }
        $dist['avg'] = round($sum / $total, 2);
    }

    /* =========================================================
     | GET /api/feedback-results
     | ✅ includes:
     | - attendance filter (min_attendance / attendance)
     | - responded X out of eligible Y
     |========================================================= */
    public function results(Request $r)
    {
        if ($resp = $this->requireStaff($r)) return $resp;

        // Optional filters
        $deptId     = $this->toInt($r->query('department_id'));
        $courseId   = $this->toInt($r->query('course_id'));
        $semesterId = $this->toInt($r->query('semester_id'));
        $subjectId  = $this->toInt($r->query('subject_id'));
        $sectionId  = $this->toInt($r->query('section_id'));
        $year       = $this->toInt($r->query('year'));
        $acadYear   = trim((string)$r->query('academic_year', ''));

        // Attendance filter input
        $minAttendance = $r->query('min_attendance', $r->query('attendance', null));
        $minAttendance = ($minAttendance !== null && $minAttendance !== '')
            ? max(0, min(100, (float)$minAttendance))
            : null;

        // Master tables existence
        $hasDepts   = $this->tableExists('departments');
        $hasCourses = $this->tableExists('courses');
        $hasSubsTbl = $this->tableExists('subjects');

        $hasCourseSems     = $this->tableExists('course_semesters');
        $hasCourseSections = $this->tableExists('course_semester_sections');

        $hasSemsTbl     = $this->tableExists('semesters');
        $hasSectionsTbl = $this->tableExists('sections');

        // Attendance table exists?
        $hasStudentSubjectTbl = $this->tableExists('student_subject');

        // Name columns
        $deptNameCol   = $hasDepts   ? $this->pickNameColumn('departments', ['name','title'], 'id') : null;
        $courseNameCol = $hasCourses ? $this->pickNameColumn('courses', ['title','name','course_name'], 'id') : null;
        $subNameCol    = $hasSubsTbl ? $this->pickNameColumn('subjects', ['name','title','subject_name'], 'id') : null;

        $csNameCol  = $hasCourseSems ? $this->pickNameColumn('course_semesters', ['title','name'], 'id') : null;
        $cssNameCol = $hasCourseSections ? $this->pickNameColumn('course_semester_sections', ['title','name'], 'id') : null;

        $semNameCol = $hasSemsTbl ? $this->pickNameColumn('semesters', ['name','title','semester_name'], 'id') : null;
        $secNameCol = $hasSectionsTbl ? $this->pickNameColumn('sections', ['name','title','section_name'], 'id') : null;

        // Feedback posts columns existence
        $fpHasDept   = $this->hasCol(self::POSTS, 'department_id');
        $fpHasCourse = $this->hasCol(self::POSTS, 'course_id');
        $fpHasSem    = $this->hasCol(self::POSTS, 'semester_id');
        $fpHasSub    = $this->hasCol(self::POSTS, 'subject_id');
        $fpHasSec    = $this->hasCol(self::POSTS, 'section_id');
        $fpHasAcad   = $this->hasCol(self::POSTS, 'academic_year');
        $fpHasYear   = $this->hasCol(self::POSTS, 'year');

        $fsHasStudent = $this->hasCol(self::SUBS, 'student_id');
        $fsHasStatus  = $this->hasCol(self::SUBS, 'status');

        // Can apply attendance filter?
        $canAttendanceFilter = ($minAttendance !== null)
            && $hasStudentSubjectTbl
            && $fsHasStudent
            && $fpHasSub;

        /*
         |--------------------------------------------------------------------------
         | Main query: ratings data (already respects attendance filter if active)
         |--------------------------------------------------------------------------
         */
        $sql = "
            SELECT
                ".($fpHasDept   ? "fp.department_id" : "NULL")." as department_id,
                ".($fpHasCourse ? "fp.course_id"     : "NULL")." as course_id,
                ".($fpHasSem    ? "fp.semester_id"   : "NULL")." as semester_id,
                ".($fpHasSub    ? "fp.subject_id"    : "NULL")." as subject_id,
                ".($fpHasSec    ? "fp.section_id"    : "NULL")." as section_id,

                fp.id as feedback_post_id,
                fp.uuid as feedback_post_uuid,
                fp.title as feedback_post_title,
                fp.short_title as feedback_post_short_title,
                fp.description as feedback_post_description,
                fp.publish_at as publish_at,
                fp.expire_at as expire_at,
                ".($fpHasAcad ? "fp.academic_year" : "NULL")." as academic_year,
                ".($fpHasYear ? "fp.year"          : "NULL")." as year,

                CAST(qk.qid AS UNSIGNED) as question_id,
                fq.title as question_title,
                fq.group_title as question_group_title,

                CAST(fk.fid AS UNSIGNED) as faculty_id,

                CAST(
                    JSON_UNQUOTE(
                        JSON_EXTRACT(
                            fs.answers,
                            CONCAT('$.\"', qk.qid, '\".\"', fk.fid, '\"')
                        )
                    ) AS UNSIGNED
                ) as stars,

                COUNT(*) as rating_count

            FROM ".self::SUBS." fs
            INNER JOIN ".self::POSTS." fp
                ON fp.id = fs.feedback_post_id
                AND fp.deleted_at IS NULL
            INNER JOIN ".self::QUESTIONS." fq
                ON fq.deleted_at IS NULL

            JOIN JSON_TABLE(
                JSON_KEYS(fs.answers),
                '$[*]' COLUMNS (qid VARCHAR(64) PATH '$')
            ) AS qk

            JOIN JSON_TABLE(
                JSON_KEYS(
                    JSON_EXTRACT(fs.answers, CONCAT('$.\"', qk.qid, '\"'))
                ),
                '$[*]' COLUMNS (fid VARCHAR(64) PATH '$')
            ) AS fk

            WHERE
                fs.deleted_at IS NULL
                AND (fs.status IS NULL OR fs.status = 'submitted')
                AND fs.answers IS NOT NULL
                AND JSON_TYPE(fs.answers) = 'OBJECT'
                AND CAST(qk.qid AS UNSIGNED) = fq.id
        ";

        $bindings = [];

        if ($fpHasDept && $deptId !== null)     { $sql .= " AND fp.department_id = ? "; $bindings[] = $deptId; }
        if ($fpHasCourse && $courseId !== null) { $sql .= " AND fp.course_id = ? ";     $bindings[] = $courseId; }
        if ($fpHasSem && $semesterId !== null)  { $sql .= " AND fp.semester_id = ? ";   $bindings[] = $semesterId; }
        if ($fpHasSub && $subjectId !== null)   { $sql .= " AND fp.subject_id = ? ";    $bindings[] = $subjectId; }
        if ($fpHasSec && $sectionId !== null)   { $sql .= " AND fp.section_id = ? ";    $bindings[] = $sectionId; }
        if ($fpHasAcad && $acadYear !== '')     { $sql .= " AND fp.academic_year = ? "; $bindings[] = $acadYear; }
        if ($fpHasYear && $year !== null)       { $sql .= " AND fp.year = ? ";          $bindings[] = $year; }

        // Attendance filter
        if ($canAttendanceFilter) {
            $sql .= "
                AND EXISTS (
                    SELECT 1
                    FROM student_subject ss
                    JOIN JSON_TABLE(
                        ss.subject_json,
                        '$[*]' COLUMNS (
                            student_id INT PATH '$.student_id',
                            subject_id INT PATH '$.subject_id',
                            current_attendance DECIMAL(6,2) PATH '$.current_attendance'
                        )
                    ) sj
                    WHERE ss.deleted_at IS NULL
                      AND (ss.status IS NULL OR ss.status = 'active')
                      ".($fpHasDept   ? " AND ss.department_id = fp.department_id " : "")."
                      ".($fpHasCourse ? " AND ss.course_id     = fp.course_id "     : "")."
                      ".($fpHasSem    ? " AND ss.semester_id  <=> fp.semester_id "  : "")."
                      AND sj.student_id = fs.student_id
                      AND sj.subject_id = fp.subject_id
                      AND sj.current_attendance >= ?
                )
            ";
            $bindings[] = $minAttendance;
        }

        $sql .= "
            GROUP BY
                ".($fpHasDept   ? "fp.department_id" : "NULL").",
                ".($fpHasCourse ? "fp.course_id"     : "NULL").",
                ".($fpHasSem    ? "fp.semester_id"   : "NULL").",
                ".($fpHasSub    ? "fp.subject_id"    : "NULL").",
                ".($fpHasSec    ? "fp.section_id"    : "NULL").",

                fp.id, fp.uuid, fp.title, fp.short_title, fp.description, fp.publish_at, fp.expire_at
                ".($fpHasAcad ? ", fp.academic_year" : "")."
                ".($fpHasYear ? ", fp.year"          : "").",

                CAST(qk.qid AS UNSIGNED), fq.title, fq.group_title,
                CAST(fk.fid AS UNSIGNED),
                CAST(
                    JSON_UNQUOTE(
                        JSON_EXTRACT(
                            fs.answers,
                            CONCAT('$.\"', qk.qid, '\".\"', fk.fid, '\"')
                        )
                    ) AS UNSIGNED
                )
            ORDER BY
                fp.id ASC,
                CAST(qk.qid AS UNSIGNED) ASC,
                CAST(fk.fid AS UNSIGNED) ASC
        ";

        $rows = collect(DB::select($sql, $bindings));

        if ($rows->isEmpty()) {
            return response()->json(['success' => true, 'data' => []]);
        }

        /*
         |--------------------------------------------------------------------------
         | Lookup maps
         |--------------------------------------------------------------------------
         */
        $deptMap = $hasDepts
            ? DB::table('departments')->whereNull('deleted_at')->pluck($deptNameCol, 'id')->toArray()
            : [];

        $courseMap = $hasCourses
            ? DB::table('courses')->whereNull('deleted_at')->pluck($courseNameCol, 'id')->toArray()
            : [];

        // Subjects: code + name label
        $subLabelMap = [];
        $subCodeMap  = [];

        if ($hasSubsTbl) {
            $subHasCode = Schema::hasColumn('subjects', 'subject_code');

            $subjectRows = DB::table('subjects')
                ->whereNull('deleted_at')
                ->select([
                    'id',
                    DB::raw("$subNameCol as subject_name"),
                ])
                ->when($subHasCode, function ($q) {
                    $q->addSelect('subject_code');
                })
                ->get();

            foreach ($subjectRows as $s) {
                $id   = (int) ($s->id ?? 0);
                if ($id <= 0) continue;

                $name = trim((string) ($s->subject_name ?? ''));
                $code = $subHasCode ? trim((string) ($s->subject_code ?? '')) : '';

                $subCodeMap[$id] = ($code !== '') ? $code : null;

                if ($code !== '' && $name !== '') $subLabelMap[$id] = $code . ' - ' . $name;
                elseif ($name !== '')             $subLabelMap[$id] = $name;
                elseif ($code !== '')             $subLabelMap[$id] = $code;
                else                              $subLabelMap[$id] = null;
            }
        }

        // semester map
        $semMap = [];
        if ($hasCourseSems) {
            $semMap = DB::table('course_semesters')->whereNull('deleted_at')->pluck($csNameCol, 'id')->toArray();
        } elseif ($hasSemsTbl) {
            $semMap = DB::table('semesters')->whereNull('deleted_at')->pluck($semNameCol, 'id')->toArray();
        }

        // section map
        $secMap = [];
        if ($hasCourseSections) {
            $secMap = DB::table('course_semester_sections')->whereNull('deleted_at')->pluck($cssNameCol, 'id')->toArray();
        } elseif ($hasSectionsTbl) {
            $secMap = DB::table('sections')->whereNull('deleted_at')->pluck($secNameCol, 'id')->toArray();
        }

        // Faculty IDs and info
        $facultyIds = $rows->pluck('faculty_id')
            ->filter(fn($x) => $x !== null && (int)$x > 0)
            ->map(fn($x) => (int)$x)
            ->unique()
            ->values()
            ->all();

        $facultyInfoMap = [];
        if (!empty($facultyIds) && $this->tableExists(self::USERS)) {
            $nameCol = $this->pickNameColumn(self::USERS, ['name','full_name'], 'id');

            $uHasShort = $this->hasCol(self::USERS, 'name_short_form');
            $uHasEmp   = $this->hasCol(self::USERS, 'employee_id');

            $q = DB::table(self::USERS)
                ->whereIn('id', $facultyIds)
                ->whereNull('deleted_at')
                ->select([
                    'id',
                    DB::raw("$nameCol as faculty_name"),
                ]);

            if ($uHasShort) $q->addSelect('name_short_form');
            if ($uHasEmp)   $q->addSelect('employee_id');

            foreach ($q->get() as $u) {
                $id = (int)($u->id ?? 0);
                if ($id <= 0) continue;

                $facultyInfoMap[$id] = [
                    'name'            => isset($u->faculty_name) ? (string)$u->faculty_name : ('Faculty #' . $id),
                    'name_short_form' => $uHasShort ? ((trim((string)($u->name_short_form ?? '')) !== '') ? (string)$u->name_short_form : null) : null,
                    'employee_id'     => $uHasEmp ? ((trim((string)($u->employee_id ?? '')) !== '') ? (string)$u->employee_id : null) : null,
                ];
            }
        }

        /*
         |--------------------------------------------------------------------------
         | ✅ NEW: responded/eligible per post AFTER filters
         |--------------------------------------------------------------------------
         */
        $postIds = $rows->pluck('feedback_post_id')->map(fn($x)=>(int)$x)->unique()->values()->all();

        // Responded (submitted/null) students per post (respect attendance filter if active)
        $postParticipated = [];
        if ($fsHasStudent && !empty($postIds)) {
            $psql = "
                SELECT
                    fp.id as feedback_post_id,
                    COUNT(DISTINCT fs.student_id) as cnt
                FROM ".self::POSTS." fp
                INNER JOIN ".self::SUBS." fs ON fs.feedback_post_id = fp.id
                WHERE fp.deleted_at IS NULL
                  AND fs.deleted_at IS NULL
                  AND (fs.status IS NULL OR fs.status = 'submitted')
                  AND fp.id IN (".implode(',', array_fill(0, count($postIds), '?')).")
            ";
            $pbind = $postIds;

            // apply SAME fp filters
            if ($fpHasDept && $deptId !== null)     { $psql .= " AND fp.department_id = ? "; $pbind[] = $deptId; }
            if ($fpHasCourse && $courseId !== null) { $psql .= " AND fp.course_id = ? ";     $pbind[] = $courseId; }
            if ($fpHasSem && $semesterId !== null)  { $psql .= " AND fp.semester_id = ? ";   $pbind[] = $semesterId; }
            if ($fpHasSub && $subjectId !== null)   { $psql .= " AND fp.subject_id = ? ";    $pbind[] = $subjectId; }
            if ($fpHasSec && $sectionId !== null)   { $psql .= " AND fp.section_id = ? ";    $pbind[] = $sectionId; }
            if ($fpHasAcad && $acadYear !== '')     { $psql .= " AND fp.academic_year = ? "; $pbind[] = $acadYear; }
            if ($fpHasYear && $year !== null)       { $psql .= " AND fp.year = ? ";          $pbind[] = $year; }

            // apply attendance eligibility if needed
            if ($canAttendanceFilter) {
                $psql .= "
                    AND EXISTS (
                        SELECT 1
                        FROM student_subject ss
                        JOIN JSON_TABLE(
                            ss.subject_json,
                            '$[*]' COLUMNS (
                                student_id INT PATH '$.student_id',
                                subject_id INT PATH '$.subject_id',
                                current_attendance DECIMAL(6,2) PATH '$.current_attendance'
                            )
                        ) sj
                        WHERE ss.deleted_at IS NULL
                          AND (ss.status IS NULL OR ss.status = 'active')
                          ".($fpHasDept   ? " AND ss.department_id = fp.department_id " : "")."
                          ".($fpHasCourse ? " AND ss.course_id     = fp.course_id "     : "")."
                          ".($fpHasSem    ? " AND ss.semester_id  <=> fp.semester_id "  : "")."
                          AND sj.student_id = fs.student_id
                          AND sj.subject_id = fp.subject_id
                          AND sj.current_attendance >= ?
                    )
                ";
                $pbind[] = $minAttendance;
            }

            $psql .= " GROUP BY fp.id ";

            $pRows = DB::select($psql, $pbind);
            foreach ($pRows as $pr) {
                $postParticipated[(int)$pr->feedback_post_id] = (int)$pr->cnt;
            }
        }

        // Eligible students per post:
        // - If minAttendance active: compute from attendance roster (student_subject JSON) per post.subject_id
        // - Else: compute from any submission row distinct student_id
        $postEligible = [];

        if (!empty($postIds)) {
            if ($canAttendanceFilter) {
                // Eligible from attendance roster
                $esql = "
                    SELECT
                        fp.id as feedback_post_id,
                        COUNT(DISTINCT sj.student_id) as cnt
                    FROM ".self::POSTS." fp
                    JOIN student_subject ss
                      ON ss.deleted_at IS NULL
                     AND (ss.status IS NULL OR ss.status = 'active')
                     ".($fpHasDept   ? " AND ss.department_id = fp.department_id " : "")."
                     ".($fpHasCourse ? " AND ss.course_id     = fp.course_id "     : "")."
                     ".($fpHasSem    ? " AND ss.semester_id  <=> fp.semester_id "  : "")."
                    JOIN JSON_TABLE(
                        ss.subject_json,
                        '$[*]' COLUMNS (
                            student_id INT PATH '$.student_id',
                            subject_id INT PATH '$.subject_id',
                            current_attendance DECIMAL(6,2) PATH '$.current_attendance'
                        )
                    ) sj
                      ON sj.subject_id = fp.subject_id
                     AND sj.current_attendance >= ?
                    WHERE fp.deleted_at IS NULL
                      AND fp.id IN (".implode(',', array_fill(0, count($postIds), '?')).")
                ";
                $ebind = array_merge([$minAttendance], $postIds);

                // apply SAME fp filters
                if ($fpHasDept && $deptId !== null)     { $esql .= " AND fp.department_id = ? "; $ebind[] = $deptId; }
                if ($fpHasCourse && $courseId !== null) { $esql .= " AND fp.course_id = ? ";     $ebind[] = $courseId; }
                if ($fpHasSem && $semesterId !== null)  { $esql .= " AND fp.semester_id = ? ";   $ebind[] = $semesterId; }
                if ($fpHasSub && $subjectId !== null)   { $esql .= " AND fp.subject_id = ? ";    $ebind[] = $subjectId; }
                if ($fpHasSec && $sectionId !== null)   { $esql .= " AND fp.section_id = ? ";    $ebind[] = $sectionId; }
                if ($fpHasAcad && $acadYear !== '')     { $esql .= " AND fp.academic_year = ? "; $ebind[] = $acadYear; }
                if ($fpHasYear && $year !== null)       { $esql .= " AND fp.year = ? ";          $ebind[] = $year; }

                $esql .= " GROUP BY fp.id ";

                $eRows = DB::select($esql, $ebind);
                foreach ($eRows as $er) {
                    $postEligible[(int)$er->feedback_post_id] = (int)$er->cnt;
                }
            } else {
                // Eligible from "any submission row" (best possible without roster)
                if ($fsHasStudent) {
                    $esql = "
                        SELECT
                            fp.id as feedback_post_id,
                            COUNT(DISTINCT fs.student_id) as cnt
                        FROM ".self::POSTS." fp
                        INNER JOIN ".self::SUBS." fs ON fs.feedback_post_id = fp.id
                        WHERE fp.deleted_at IS NULL
                          AND fs.deleted_at IS NULL
                          AND fp.id IN (".implode(',', array_fill(0, count($postIds), '?')).")
                    ";
                    $ebind = $postIds;

                    // apply SAME fp filters
                    if ($fpHasDept && $deptId !== null)     { $esql .= " AND fp.department_id = ? "; $ebind[] = $deptId; }
                    if ($fpHasCourse && $courseId !== null) { $esql .= " AND fp.course_id = ? ";     $ebind[] = $courseId; }
                    if ($fpHasSem && $semesterId !== null)  { $esql .= " AND fp.semester_id = ? ";   $ebind[] = $semesterId; }
                    if ($fpHasSub && $subjectId !== null)   { $esql .= " AND fp.subject_id = ? ";    $ebind[] = $subjectId; }
                    if ($fpHasSec && $sectionId !== null)   { $esql .= " AND fp.section_id = ? ";    $ebind[] = $sectionId; }
                    if ($fpHasAcad && $acadYear !== '')     { $esql .= " AND fp.academic_year = ? "; $ebind[] = $acadYear; }
                    if ($fpHasYear && $year !== null)       { $esql .= " AND fp.year = ? ";          $ebind[] = $year; }

                    $esql .= " GROUP BY fp.id ";

                    $eRows = DB::select($esql, $ebind);
                    foreach ($eRows as $er) {
                        $postEligible[(int)$er->feedback_post_id] = (int)$er->cnt;
                    }
                }
            }
        }

        /*
         |--------------------------------------------------------------------------
         | Build nested response
         |--------------------------------------------------------------------------
         */
        $out = [];

        foreach ($rows as $rr) {
            $dId   = $rr->department_id !== null ? (int)$rr->department_id : 0;
            $cId   = $rr->course_id !== null ? (int)$rr->course_id : 0;
            $semId = $rr->semester_id !== null ? (int)$rr->semester_id : 0;
            $sbId  = $rr->subject_id !== null ? (int)$rr->subject_id : 0;
            $secId = $rr->section_id !== null ? (int)$rr->section_id : 0;

            $postId = (int)$rr->feedback_post_id;
            $qId    = (int)$rr->question_id;
            $fId    = (int)$rr->faculty_id;
            $stars  = (int)($rr->stars ?? 0);
            $cnt    = (int)($rr->rating_count ?? 0);

            $deptKey   = (string)$dId;
            $courseKey = (string)$cId;
            $semKey    = (string)$semId;
            $subKey    = (string)$sbId;
            $secKey    = (string)$secId;
            $postKey   = (string)$postId;

            if (!isset($out[$deptKey])) {
                $out[$deptKey] = [
                    'department_id' => $dId ?: null,
                    'department_name' => ($dId && isset($deptMap[$dId])) ? (string)$deptMap[$dId] : null,
                    'courses' => [],
                ];
            }

            if (!isset($out[$deptKey]['courses'][$courseKey])) {
                $out[$deptKey]['courses'][$courseKey] = [
                    'course_id' => $cId ?: null,
                    'course_name' => ($cId && isset($courseMap[$cId])) ? (string)$courseMap[$cId] : null,
                    'semesters' => [],
                ];
            }

            if (!isset($out[$deptKey]['courses'][$courseKey]['semesters'][$semKey])) {
                $out[$deptKey]['courses'][$courseKey]['semesters'][$semKey] = [
                    'semester_id' => $semId ?: null,
                    'semester_name' => ($semId && isset($semMap[$semId])) ? (string)$semMap[$semId] : null,
                    'subjects' => [],
                ];
            }

            if (!isset($out[$deptKey]['courses'][$courseKey]['semesters'][$semKey]['subjects'][$subKey])) {
                $out[$deptKey]['courses'][$courseKey]['semesters'][$semKey]['subjects'][$subKey] = [
                    'subject_id'   => $sbId ?: null,
                    'subject_code' => ($sbId && array_key_exists($sbId, $subCodeMap)) ? $subCodeMap[$sbId] : null,
                    'subject_name' => ($sbId && array_key_exists($sbId, $subLabelMap)) ? $subLabelMap[$sbId] : null,
                    'sections' => [],
                ];
            }

            if (!isset($out[$deptKey]['courses'][$courseKey]['semesters'][$semKey]['subjects'][$subKey]['sections'][$secKey])) {
                $out[$deptKey]['courses'][$courseKey]['semesters'][$semKey]['subjects'][$subKey]['sections'][$secKey] = [
                    'section_id' => $secId ?: null,
                    'section_name' => ($secId && isset($secMap[$secId])) ? (string)$secMap[$secId] : null,
                    'feedback_posts' => [],
                ];
            }

            $secRef =& $out[$deptKey]['courses'][$courseKey]['semesters'][$semKey]['subjects'][$subKey]['sections'][$secKey];

            if (!isset($secRef['feedback_posts'][$postKey])) {
                $responded = $postParticipated[$postId] ?? 0;
                $eligible  = array_key_exists($postId, $postEligible) ? $postEligible[$postId] : null;

                $rate = null;
                if (is_int($eligible) && $eligible > 0) {
                    $rate = round(($responded / $eligible) * 100, 2);
                }

                $secRef['feedback_posts'][$postKey] = [
                    'feedback_post_id' => $postId,
                    'feedback_post_uuid' => (string)($rr->feedback_post_uuid ?? ''),
                    'title' => (string)($rr->feedback_post_title ?? ''),
                    'short_title' => $rr->feedback_post_short_title !== null ? (string)$rr->feedback_post_short_title : null,
                    'description' => $rr->feedback_post_description,
                    'publish_at' => $rr->publish_at,
                    'expire_at'  => $rr->expire_at,
                    'academic_year' => $rr->academic_year ?? null,
                    'year' => $rr->year ?? null,

                    // ✅ X out of Y
                    'participated_students' => $responded,
                    'eligible_students'     => $eligible,
                    'response_rate'         => $rate, // optional, can ignore

                    'questions' => [],
                ];
            }

            $postRef =& $secRef['feedback_posts'][$postKey];

            if (!isset($postRef['questions'][(string)$qId])) {
                $postRef['questions'][(string)$qId] = [
                    'question_id' => $qId,
                    'question_title' => (string)($rr->question_title ?? ''),
                    'group_title' => $rr->question_group_title !== null ? (string)$rr->question_group_title : null,
                    'distribution' => $this->initDist(),
                    'faculty' => [],
                ];
            }

            // overall distribution
            if ($stars >= 1 && $stars <= 5) {
                $postRef['questions'][(string)$qId]['distribution']['counts'][(string)$stars] += $cnt;
                $postRef['questions'][(string)$qId]['distribution']['total'] += $cnt;
            }

            // faculty distribution
            $fname = 'Overall';
            $shortForm = null;
            $empId = null;

            if ($fId > 0) {
                if (isset($facultyInfoMap[$fId])) {
                    $fname      = (string)($facultyInfoMap[$fId]['name'] ?? ('Faculty #' . $fId));
                    $shortForm  = $facultyInfoMap[$fId]['name_short_form'] ?? null;
                    $empId      = $facultyInfoMap[$fId]['employee_id'] ?? null;
                } else {
                    $fname = 'Faculty #' . $fId;
                }
            }

            if (!isset($postRef['questions'][(string)$qId]['faculty'][(string)$fId])) {
                $postRef['questions'][(string)$qId]['faculty'][(string)$fId] = [
                    'faculty_id'      => $fId <= 0 ? 0 : $fId,
                    'faculty_name'    => $fname,
                    'name_short_form' => $fId <= 0 ? null : $shortForm,
                    'employee_id'     => $fId <= 0 ? null : $empId,
                    'avg_rating'      => null,
                    'count'           => 0,
                    'out_of'          => 5,
                    'distribution'    => $this->initDist(),
                ];
            }

            if ($stars >= 1 && $stars <= 5) {
                $postRef['questions'][(string)$qId]['faculty'][(string)$fId]['distribution']['counts'][(string)$stars] += $cnt;
                $postRef['questions'][(string)$qId]['faculty'][(string)$fId]['distribution']['total'] += $cnt;
            }
        }

        // finalize avgs + force overall row
        foreach ($out as &$dept) {
            foreach ($dept['courses'] as &$course) {
                foreach ($course['semesters'] as &$sem) {
                    foreach ($sem['subjects'] as &$sub) {
                        foreach ($sub['sections'] as &$sec) {
                            foreach ($sec['feedback_posts'] as &$post) {
                                foreach ($post['questions'] as &$q) {

                                    $this->finalizeDist($q['distribution']);
                                    $overallTotal = (int)($q['distribution']['total'] ?? 0);

                                    foreach ($q['faculty'] as $fidKey => &$frow) {
                                        $this->finalizeDist($frow['distribution']);
                                        $frow['count'] = (int)($frow['distribution']['total'] ?? 0);
                                        $frow['avg_rating'] = $frow['distribution']['avg'];
                                    }
                                    unset($frow);

                                    $q['faculty']['0'] = [
                                        'faculty_id'      => 0,
                                        'faculty_name'    => 'Overall',
                                        'name_short_form' => null,
                                        'employee_id'     => null,
                                        'avg_rating'      => $q['distribution']['avg'],
                                        'count'           => $overallTotal,
                                        'out_of'          => 5,
                                        'distribution'    => $q['distribution'],
                                    ];

                                    ksort($q['faculty'], SORT_NATURAL);
                                }
                            }
                        }
                    }
                }
            }
        }
        unset($dept,$course,$sem,$sub,$sec,$post,$q);

        // convert maps to arrays
        $final = array_values(array_map(function ($dept) {
            $dept['courses'] = array_values(array_map(function ($course) {
                $course['semesters'] = array_values(array_map(function ($sem) {
                    $sem['subjects'] = array_values(array_map(function ($sub) {
                        $sub['sections'] = array_values(array_map(function ($sec) {

                            $sec['feedback_posts'] = array_values(array_map(function ($post) {
                                $post['questions'] = array_values(array_map(function ($q) {
                                    $q['faculty'] = array_values($q['faculty']);
                                    return $q;
                                }, $post['questions']));
                                return $post;
                            }, $sec['feedback_posts']));

                            return $sec;
                        }, $sub['sections']));
                        return $sub;
                    }, $sem['subjects']));
                    return $sem;
                }, $course['semesters']));
                return $course;
            }, $dept['courses']));
            return $dept;
        }, $out));

        return response()->json([
            'success' => true,
            'data' => $final,
        ]);
    }
}
