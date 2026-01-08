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

    /* =========================================================
     | GET /api/feedback-results
     | dept -> course -> sem -> subject -> section -> posts -> questions -> distribution
     |========================================================= */
    public function results(Request $r)
    {
        if ($resp = $this->requireStaff($r)) return $resp;

        // Optional filters
        $deptId     = $r->query('department_id');
        $courseId   = $r->query('course_id');
        $semesterId = $r->query('semester_id');
        $subjectId  = $r->query('subject_id');
        $sectionId  = $r->query('section_id');
        $year       = $r->query('year');
        $acadYear   = trim((string)$r->query('academic_year', ''));

        // Master tables existence
        $hasDepts   = $this->tableExists('departments');
        $hasCourses = $this->tableExists('courses');
        $hasSubsTbl = $this->tableExists('subjects');

        // ✅ IMPORTANT FIX: prefer your actual semester/section tables
        $hasCourseSems     = $this->tableExists('course_semesters');          // ✅
        $hasCourseSections = $this->tableExists('course_semester_sections');  // ✅

        // Fallback tables (if exist)
        $hasSemsTbl     = $this->tableExists('semesters');
        $hasSectionsTbl = $this->tableExists('sections');

        // Name columns
        $deptNameCol   = $hasDepts   ? $this->pickNameColumn('departments', ['name','title'], 'id') : null;
        $courseNameCol = $hasCourses ? $this->pickNameColumn('courses', ['title','name','course_name'], 'id') : null;
        $subNameCol    = $hasSubsTbl ? $this->pickNameColumn('subjects', ['name','title','subject_name'], 'id') : null;

        // For course_semesters
        $csNameCol = $hasCourseSems ? $this->pickNameColumn('course_semesters', ['title','name'], 'id') : null;

        // For course_semester_sections
        $cssNameCol = $hasCourseSections ? $this->pickNameColumn('course_semester_sections', ['title','name'], 'id') : null;

        // Fallback semesters/sections
        $semNameCol = $hasSemsTbl ? $this->pickNameColumn('semesters', ['name','title','semester_name'], 'id') : null;
        $secNameCol = $hasSectionsTbl ? $this->pickNameColumn('sections', ['name','title','section_name'], 'id') : null;

        // Feedback posts columns existence
        $fpHasDept  = $this->hasCol(self::POSTS, 'department_id');
        $fpHasCourse= $this->hasCol(self::POSTS, 'course_id');
        $fpHasSem   = $this->hasCol(self::POSTS, 'semester_id');
        $fpHasSub   = $this->hasCol(self::POSTS, 'subject_id');
        $fpHasSec   = $this->hasCol(self::POSTS, 'section_id');
        $fpHasAcad  = $this->hasCol(self::POSTS, 'academic_year');
        $fpHasYear  = $this->hasCol(self::POSTS, 'year');

        $fsHasStudent = $this->hasCol(self::SUBS, 'student_id');

        /*
         |--------------------------------------------------------------------------
         | Explode JSON answers (qid -> fid -> stars)
         |--------------------------------------------------------------------------
         */
        $sql = "
            SELECT
                ".($fpHasDept  ? "fp.department_id" : "NULL")." as department_id,
                ".($fpHasCourse? "fp.course_id"     : "NULL")." as course_id,
                ".($fpHasSem   ? "fp.semester_id"   : "NULL")." as semester_id,
                ".($fpHasSub   ? "fp.subject_id"    : "NULL")." as subject_id,
                ".($fpHasSec   ? "fp.section_id"    : "NULL")." as section_id,

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

                COUNT(*) as rating_count,
                AVG(
                    CAST(
                        JSON_UNQUOTE(
                            JSON_EXTRACT(
                                fs.answers,
                                CONCAT('$.\"', qk.qid, '\".\"', fk.fid, '\"')
                            )
                        ) AS UNSIGNED
                    )
                ) as avg_rating,

                ".($fsHasStudent ? "COUNT(DISTINCT fs.student_id)" : "NULL")." as participated_students

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

        if ($fpHasDept && $deptId !== null && $deptId !== '') {
            $sql .= " AND fp.department_id = ? ";
            $bindings[] = (int)$deptId;
        }
        if ($fpHasCourse && $courseId !== null && $courseId !== '') {
            $sql .= " AND fp.course_id = ? ";
            $bindings[] = (int)$courseId;
        }
        if ($fpHasSem && $semesterId !== null && $semesterId !== '') {
            $sql .= " AND fp.semester_id = ? ";
            $bindings[] = (int)$semesterId;
        }
        if ($fpHasSub && $subjectId !== null && $subjectId !== '') {
            $sql .= " AND fp.subject_id = ? ";
            $bindings[] = (int)$subjectId;
        }
        if ($fpHasSec && $sectionId !== null && $sectionId !== '') {
            $sql .= " AND fp.section_id = ? ";
            $bindings[] = (int)$sectionId;
        }
        if ($fpHasAcad && $acadYear !== '') {
            $sql .= " AND fp.academic_year = ? ";
            $bindings[] = $acadYear;
        }
        if ($fpHasYear && $year !== null && $year !== '') {
            $sql .= " AND fp.year = ? ";
            $bindings[] = (int)$year;
        }

        $sql .= "
            GROUP BY
                ".($fpHasDept  ? "fp.department_id" : "NULL").",
                ".($fpHasCourse? "fp.course_id"     : "NULL").",
                ".($fpHasSem   ? "fp.semester_id"   : "NULL").",
                ".($fpHasSub   ? "fp.subject_id"    : "NULL").",
                ".($fpHasSec   ? "fp.section_id"    : "NULL").",

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

        // Lookups
        $deptMap = $hasDepts ? DB::table('departments')->whereNull('deleted_at')->pluck($deptNameCol, 'id')->toArray() : [];
        $courseMap = $hasCourses ? DB::table('courses')->whereNull('deleted_at')->pluck($courseNameCol, 'id')->toArray() : [];
        $subMap = $hasSubsTbl ? DB::table('subjects')->whereNull('deleted_at')->pluck($subNameCol, 'id')->toArray() : [];

        // ✅ FIX: semester map from course_semesters (fallback to semesters)
        $semMap = [];
        if ($hasCourseSems) {
            $semMap = DB::table('course_semesters')->whereNull('deleted_at')->pluck($csNameCol, 'id')->toArray();
        } elseif ($hasSemsTbl) {
            $semMap = DB::table('semesters')->whereNull('deleted_at')->pluck($semNameCol, 'id')->toArray();
        }

        // ✅ FIX: section map from course_semester_sections (fallback to sections)
        $secMap = [];
        if ($hasCourseSections) {
            $secMap = DB::table('course_semester_sections')->whereNull('deleted_at')->pluck($cssNameCol, 'id')->toArray();
        } elseif ($hasSectionsTbl) {
            $secMap = DB::table('sections')->whereNull('deleted_at')->pluck($secNameCol, 'id')->toArray();
        }

        // Faculty map
        $facultyIds = $rows->pluck('faculty_id')
            ->filter(fn($x) => $x !== null && (int)$x > 0)
            ->map(fn($x) => (int)$x)
            ->unique()
            ->values()
            ->all();

        $facultyMap = [];
        if (!empty($facultyIds) && $this->tableExists(self::USERS)) {
            $nameCol = $this->pickNameColumn(self::USERS, ['name','full_name'], 'id');
            $facultyMap = DB::table(self::USERS)
                ->whereIn('id', $facultyIds)
                ->whereNull('deleted_at')
                ->pluck($nameCol, 'id')
                ->toArray();
        }

        // Build nested response
        $out = [];
        $postParticipated = [];

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

            if (!isset($postParticipated[$postId])) {
                $postParticipated[$postId] = (int)($rr->participated_students ?? 0);
            }

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
                    'semester_name' => ($semId && isset($semMap[$semId])) ? (string)$semMap[$semId] : null, // ✅ fixed
                    'subjects' => [],
                ];
            }

            if (!isset($out[$deptKey]['courses'][$courseKey]['semesters'][$semKey]['subjects'][$subKey])) {
                $out[$deptKey]['courses'][$courseKey]['semesters'][$semKey]['subjects'][$subKey] = [
                    'subject_id' => $sbId ?: null,
                    'subject_name' => ($sbId && isset($subMap[$sbId])) ? (string)$subMap[$sbId] : null,
                    'sections' => [],
                ];
            }

            if (!isset($out[$deptKey]['courses'][$courseKey]['semesters'][$semKey]['subjects'][$subKey]['sections'][$secKey])) {
                $out[$deptKey]['courses'][$courseKey]['semesters'][$semKey]['subjects'][$subKey]['sections'][$secKey] = [
                    'section_id' => $secId ?: null,
                    'section_name' => ($secId && isset($secMap[$secId])) ? (string)$secMap[$secId] : null, // ✅ fixed
                    'feedback_posts' => [],
                ];
            }

            $secRef =& $out[$deptKey]['courses'][$courseKey]['semesters'][$semKey]['subjects'][$subKey]['sections'][$secKey];

            if (!isset($secRef['feedback_posts'][$postKey])) {
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
                    'participated_students' => $postParticipated[$postId] ?? 0,

                    // optional placeholders
                    'eligible_students' => null,

                    'questions' => [],
                ];
            }

            $postRef =& $secRef['feedback_posts'][$postKey];

            if (!isset($postRef['questions'][(string)$qId])) {
                $postRef['questions'][(string)$qId] = [
                    'question_id' => $qId,
                    'question_title' => (string)($rr->question_title ?? ''),
                    'group_title' => $rr->question_group_title !== null ? (string)$rr->question_group_title : null,

                    // distribution for screenshot table
                    'distribution' => [
                        'counts'  => [ '5'=>0,'4'=>0,'3'=>0,'2'=>0,'1'=>0 ],
                        'total'   => 0,
                        'avg'     => null,
                        'percent' => [ '5'=>0,'4'=>0,'3'=>0,'2'=>0,'1'=>0 ],
                    ],

                    // keep faculty list (unchanged)
                    'faculty' => [],
                ];
            }

            // accumulate distribution
            if ($stars >= 1 && $stars <= 5) {
                $postRef['questions'][(string)$qId]['distribution']['counts'][(string)$stars] += $cnt;
                $postRef['questions'][(string)$qId]['distribution']['total'] += $cnt;
            }

            // faculty avg
            $fname = ($fId <= 0)
                ? 'Overall'
                : (isset($facultyMap[$fId]) ? (string)$facultyMap[$fId] : ('Faculty #' . $fId));

            $postRef['questions'][(string)$qId]['faculty'][(string)$fId] = [
                'faculty_id' => $fId <= 0 ? 0 : $fId,
                'faculty_name' => $fname,
                'avg_rating' => $rr->avg_rating !== null ? round((float)$rr->avg_rating, 2) : null,
                'count' => (int)($rr->rating_count ?? 0),
                'out_of' => 5,
            ];
        }

        // finalize percent + avg
        foreach ($out as &$dept) {
            foreach ($dept['courses'] as &$course) {
                foreach ($course['semesters'] as &$sem) {
                    foreach ($sem['subjects'] as &$sub) {
                        foreach ($sub['sections'] as &$sec) {
                            foreach ($sec['feedback_posts'] as &$post) {
                                foreach ($post['questions'] as &$q) {
                                    $counts = $q['distribution']['counts'];
                                    $total  = (int)($q['distribution']['total'] ?? 0);

                                    if ($total > 0) {
                                        $sum = 0;
                                        foreach ([5,4,3,2,1] as $s) {
                                            $sum += $s * (int)($counts[(string)$s] ?? 0);
                                        }
                                        $q['distribution']['avg'] = round($sum / $total, 2);

                                        foreach ([5,4,3,2,1] as $s) {
                                            $pct = ((int)($counts[(string)$s] ?? 0) * 100) / $total;
                                            $q['distribution']['percent'][(string)$s] = (int) round($pct, 0); // screenshot-style
                                        }
                                    } else {
                                        $q['distribution']['avg'] = null;
                                        foreach ([5,4,3,2,1] as $s) $q['distribution']['percent'][(string)$s] = 0;
                                    }
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
