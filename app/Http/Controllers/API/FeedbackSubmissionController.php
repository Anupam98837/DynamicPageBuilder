<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class FeedbackSubmissionController extends Controller
{
    private const POSTS = 'feedback_posts';
    private const SUBS  = 'feedback_submissions';

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

    private function isStudent(Request $r): bool
    {
        return strtolower((string)($this->actor($r)['role'] ?? '')) === 'student';
    }

    private function isAdminish(Request $r): bool
    {
        $role = strtolower((string)($this->actor($r)['role'] ?? ''));
        return in_array($role, ['admin','director','principal','it_person','technical_assistant'], true);
    }

    private function requireAuth(Request $r)
    {
        $a = $this->actor($r);
        if (($a['id'] ?? 0) <= 0) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        return null;
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

    private function normalizeIdentifier(string $idOrUuid, ?string $alias = null): array
    {
        $idOrUuid = trim($idOrUuid);
        $isNumeric = preg_match('/^\d+$/', $idOrUuid) === 1;

        $rawCol = $isNumeric ? 'id' : 'uuid';
        $val    = $isNumeric ? (int)$idOrUuid : $idOrUuid;

        $prefix = ($alias !== null && $alias !== '') ? ($alias . '.') : '';

        return [
            'col'     => $prefix . $rawCol,
            'raw_col' => $rawCol,
            'val'     => $val,
        ];
    }

    /* =========================================================
     | Post query helpers
     |========================================================= */

     private function basePostsQuery(bool $includeDeleted = false)
     {
         $q = DB::table(self::POSTS . ' as fp')->select([
             'fp.id','fp.uuid',
             'fp.title','fp.short_title','fp.description',
             'fp.course_id','fp.semester_id','fp.subject_id','fp.section_id',
             'fp.academic_year','fp.year',
             'fp.question_ids','fp.faculty_ids','fp.question_faculty','fp.student_ids',
             'fp.sort_order','fp.status','fp.publish_at','fp.expire_at',
             'fp.created_by','fp.created_at','fp.updated_at',
             'fp.deleted_at',
         ]);
     
         /* =========================================================
          | ✅ SEMESTER JOIN (AUTO DETECT TABLE + COLUMN)
          |========================================================= */
     
         // Try possible semester table names
         $semTable = null;
         foreach (['semesters', 'academic_semesters', 'course_semesters'] as $t) {
             if (Schema::hasTable($t)) { $semTable = $t; break; }
         }
     
         if ($semTable) {
             // ✅ detect join column in semester table
             $semJoinCol = null;
             foreach (['id', 'semester_id', 'sem_id'] as $candidate) {
                 if ($this->hasCol($semTable, $candidate)) {
                     $semJoinCol = $candidate;
                     break;
                 }
             }
             // fallback
             if (!$semJoinCol) $semJoinCol = 'id';
     
             $q->leftJoin($semTable . ' as sem', "sem.$semJoinCol", '=', 'fp.semester_id');
     
             // ✅ Add semester_name
             if ($this->hasCol($semTable, 'name')) {
                 $q->addSelect(DB::raw('sem.name as semester_name'));
             } elseif ($this->hasCol($semTable, 'title')) {
                 $q->addSelect(DB::raw('sem.title as semester_name'));
             } elseif ($this->hasCol($semTable, 'semester_name')) {
                 $q->addSelect(DB::raw('sem.semester_name as semester_name'));
             }
     
             // ✅ Add semester_no
             if ($this->hasCol($semTable, 'semester_no')) {
                 $q->addSelect(DB::raw('sem.semester_no as semester_no'));
             } elseif ($this->hasCol($semTable, 'number')) {
                 $q->addSelect(DB::raw('sem.number as semester_no'));
             } elseif ($this->hasCol($semTable, 'sem_no')) {
                 $q->addSelect(DB::raw('sem.sem_no as semester_no'));
             } elseif ($this->hasCol($semTable, 'semester_number')) {
                 $q->addSelect(DB::raw('sem.semester_number as semester_no'));
             }
     
             // ✅ debug field (optional)
             $q->addSelect(DB::raw("sem.$semJoinCol as joined_semester_id"));
         }
     
         /* =========================================================
          | ✅ SUBJECT JOIN (SAFE)
          |========================================================= */
     
         if (Schema::hasTable('subjects')) {
             $q->leftJoin('subjects as sub', 'sub.id', '=', 'fp.subject_id');
     
             if ($this->hasCol('subjects', 'name')) {
                 $q->addSelect(DB::raw('sub.name as subject_name'));
             } elseif ($this->hasCol('subjects', 'title')) {
                 $q->addSelect(DB::raw('sub.title as subject_name'));
             } elseif ($this->hasCol('subjects', 'subject_name')) {
                 $q->addSelect(DB::raw('sub.subject_name as subject_name'));
             }
         }
     
         if (!$includeDeleted) $q->whereNull('fp.deleted_at');
     
         return $q;
     }
     

    /**
     * Student sees only posts where student_ids contains them.
     */
    private function applyStudentScope(Request $r, $q)
    {
        if (!$this->isStudent($r)) return $q;

        $sid = (int)($this->actor($r)['id'] ?? 0);
        if ($sid <= 0) {
            $q->whereRaw('1=0');
            return $q;
        }

        $q->whereRaw("JSON_CONTAINS(fp.student_ids, ?, '$')", [json_encode($sid)]);
        return $q;
    }

    private function applyCurrentWindow($q)
    {
        return $q->where('fp.status', 'active')
            ->where(function ($w) {
                $w->whereNull('fp.publish_at')->orWhere('fp.publish_at', '<=', now());
            })
            ->where(function ($w) {
                $w->whereNull('fp.expire_at')->orWhere('fp.expire_at', '>=', now());
            });
    }

    private function postToArray($row): array
    {
        $questionIds = $this->normalizeJson($row->question_ids);
        $facultyIds  = $this->normalizeJson($row->faculty_ids);
        $qFaculty    = $this->normalizeJson($row->question_faculty);
        $studentIds  = $this->normalizeJson($row->student_ids);

        // semester label: prefer semester_no then fallback
        $semesterNo = null;
        if (property_exists($row, 'semester_no') && $row->semester_no !== null && $row->semester_no !== '') {
            $semesterNo = is_numeric($row->semester_no) ? (int)$row->semester_no : (string)$row->semester_no;
        }

        return [
            'id'    => (int)$row->id,
            'uuid'  => (string)$row->uuid,
            'title' => (string)($row->title ?? ''),
            'short_title' => $row->short_title !== null ? (string)$row->short_title : null,
            'description' => $row->description,

            'course_id'   => $row->course_id !== null ? (int)$row->course_id : null,
            'semester_id' => $row->semester_id !== null ? (int)$row->semester_id : null,
            'subject_id'  => $row->subject_id !== null ? (int)$row->subject_id : null,
            'section_id'  => $row->section_id !== null ? (int)$row->section_id : null,

            // ✅ extra display fields
            'semester_name' => property_exists($row, 'semester_name') ? ($row->semester_name !== null ? (string)$row->semester_name : null) : null,
            'semester_no'   => $semesterNo,
            'subject_name'  => property_exists($row, 'subject_name') ? ($row->subject_name !== null ? (string)$row->subject_name : null) : null,

            'academic_year' => $row->academic_year !== null ? (string)$row->academic_year : null,
            'year'          => $row->year !== null ? (int)$row->year : null,

            'question_ids'     => is_array($questionIds) ? array_values($questionIds) : [],
            'faculty_ids'      => is_array($facultyIds) ? array_values($facultyIds) : [],
            'question_faculty' => is_array($qFaculty) ? $qFaculty : null,
            'student_ids'      => is_array($studentIds) ? array_values($studentIds) : [],

            'sort_order' => (int)($row->sort_order ?? 0),
            'status'     => (string)($row->status ?? 'active'),
            'publish_at' => $row->publish_at,
            'expire_at'  => $row->expire_at,

            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ];
    }

    /* =========================================================
     | Validate answers for UI format:
     | answers = { "qid": { "facultyId": stars, ... }, ... }
     |========================================================= */
    private function validateAnswersAgainstPost(array $post, array $answers): ?string
    {
        $questionIds = $post['question_ids'] ?? [];
        $questionIds = is_array($questionIds) ? array_values(array_unique(array_map('intval', $questionIds))) : [];
        if (empty($questionIds)) return 'This feedback post has no questions assigned.';
        if (empty($answers)) return 'answers is required.';

        $globalFaculty = $post['faculty_ids'] ?? [];
        $globalFaculty = is_array($globalFaculty) ? array_values(array_unique(array_map('intval', $globalFaculty))) : [];

        $qf = $post['question_faculty'] ?? null;
        $qf = is_array($qf) ? $qf : null;

        foreach ($answers as $qidKey => $facStars) {
            if (!preg_match('/^\d+$/', (string)$qidKey)) return "answers key must be numeric question_id. Invalid: {$qidKey}";
            $qid = (int)$qidKey;

            if (!in_array($qid, $questionIds, true)) return "answers contains question_id {$qid} not in this post.";
            if (!is_array($facStars)) return "answers[{$qid}] must be an object of faculty_id => stars.";

            $allowedForQuestion = $globalFaculty;

            if ($qf && array_key_exists((string)$qid, $qf)) {
                $val = $qf[(string)$qid];

                if ($val === null) {
                    $allowedForQuestion = [];
                } elseif (is_array($val) && array_key_exists('faculty_ids', $val)) {
                    if ($val['faculty_ids'] === null) {
                        $allowedForQuestion = $globalFaculty;
                    } elseif (is_array($val['faculty_ids'])) {
                        $allowedForQuestion = array_values(array_unique(array_map('intval', $val['faculty_ids'])));
                    }
                }
            }

            // No faculty allowed => only "0" overall allowed if provided
            if (empty($allowedForQuestion)) {
                if (count($facStars) === 0) continue;

                $keys = array_keys($facStars);
                if (count($keys) !== 1 || (string)$keys[0] !== '0') {
                    return "Question {$qid} does not allow faculty ratings.";
                }

                $stars = $facStars['0'];
                if (!is_numeric($stars)) return "answers[{$qid}][0] stars must be numeric.";
                $stars = (int)$stars;
                if ($stars < 1 || $stars > 5) return "answers[{$qid}][0] stars must be between 1 and 5.";
                continue;
            }

            foreach ($facStars as $fidKey => $stars) {
                if (!preg_match('/^\d+$/', (string)$fidKey)) return "answers[{$qid}] faculty key must be numeric faculty_id. Invalid: {$fidKey}";
                $fid = (int)$fidKey;

                if (!empty($allowedForQuestion) && !in_array($fid, $allowedForQuestion, true)) {
                    return "Faculty {$fid} is not allowed for question {$qid}.";
                }

                if (!is_numeric($stars)) return "answers[{$qid}][{$fid}] stars must be numeric.";
                $stars = (int)$stars;
                if ($stars < 1 || $stars > 5) return "answers[{$qid}][{$fid}] stars must be between 1 and 5.";

                $exists = DB::table('users')
                    ->where('id', $fid)
                    ->whereNull('deleted_at')
                    ->where('role', 'faculty')
                    ->exists();

                if (!$exists) return "Faculty id {$fid} is not a valid faculty user.";
            }
        }

        // require all questions present
        $keys = array_map('intval', array_keys($answers));
        sort($keys);
        $q2 = $questionIds;
        sort($q2);

        if ($keys !== $q2) {
            return "Please submit ratings for all questions.";
        }

        return null;
    }

    /* =========================================================
     | 1) LIST available posts for current user
     | GET /api/feedback-posts/available
     | includes:
     | - is_submitted
     | - submission (if submitted): answers + submitted_at
     |========================================================= */
    public function available(Request $r)
    {
        if ($resp = $this->requireAuth($r)) return $resp;

        $a = $this->actor($r);

        $q = $this->basePostsQuery(false);
        $this->applyCurrentWindow($q);
        $this->applyStudentScope($r, $q);

        // optional filters
        if ($r->filled('course_id'))   $q->where('fp.course_id', (int)$r->query('course_id'));
        if ($r->filled('semester_id')) $q->where('fp.semester_id', (int)$r->query('semester_id'));
        if ($r->filled('subject_id'))  $q->where('fp.subject_id', (int)$r->query('subject_id'));
        if ($r->filled('section_id'))  $q->where('fp.section_id', (int)$r->query('section_id'));
        if ($r->filled('year'))        $q->where('fp.year', (int)$r->query('year'));
        if ($r->filled('academic_year')) $q->where('fp.academic_year', (string)$r->query('academic_year'));

        $q->orderBy('fp.sort_order', 'asc')->orderBy('fp.id', 'asc');
        $posts = $q->get();

        $postIds = $posts->pluck('id')->map(fn($x)=>(int)$x)->values()->all();

        $subByPost = [];
        if (!empty($postIds)) {
            // Student: include their submission
            if ($this->isStudent($r)) {
                $rows = DB::table(self::SUBS)
                    ->whereIn('feedback_post_id', $postIds)
                    ->where('student_id', (int)$a['id'])
                    ->whereNull('deleted_at')
                    ->select(['id','uuid','feedback_post_id','student_id','status','submitted_at','answers','metadata','created_at','updated_at'])
                    ->get();

                foreach ($rows as $s) {
                    $subByPost[(int)$s->feedback_post_id] = [
                        'id' => (int)$s->id,
                        'uuid' => (string)$s->uuid,
                        'feedback_post_id' => (int)$s->feedback_post_id,
                        'student_id' => (int)$s->student_id,
                        'status' => (string)($s->status ?? 'submitted'),
                        'submitted_at' => $s->submitted_at,
                        'answers' => $this->normalizeJson($s->answers),
                        'metadata' => $this->normalizeJson($s->metadata),
                        'updated_at' => $s->updated_at,
                        'created_at' => $s->created_at,
                    ];
                }
            }
        }

        $data = $posts->map(function ($row) use ($subByPost) {
            $arr = $this->postToArray($row);
            $pid = (int)$arr['id'];
            $arr['is_submitted'] = isset($subByPost[$pid]);
            $arr['submission'] = $subByPost[$pid] ?? null;
            return $arr;
        })->values();

        return response()->json(['success' => true, 'data' => $data]);
    }

    /* =========================================================
     | 2) SUBMIT / UPDATE feedback (UPSERT)
     | POST /api/feedback-posts/{id|uuid}/submit
     | - If already submitted => update answers (editable)
     |========================================================= */
    public function submit(Request $r, string $idOrUuid)
    {
        if ($resp = $this->requireAuth($r)) return $resp;

        $a = $this->actor($r);

        $r->validate([
            'answers'  => ['required'],
            'metadata' => ['nullable'],
        ]);

        $w = $this->normalizeIdentifier($idOrUuid, 'fp');

        $pq = $this->basePostsQuery(false)->where($w['col'], $w['val']);
        $this->applyCurrentWindow($pq);
        $this->applyStudentScope($r, $pq);

        $postRow = $pq->first();
        if (!$postRow) {
            return response()->json(['success' => false, 'message' => 'Feedback post not found or not available'], 404);
        }

        $post = $this->postToArray($postRow);

        $answers = $this->normalizeJson($r->input('answers'));
        if (!is_array($answers)) {
            return response()->json(['success'=>false,'message'=>'answers must be valid JSON object'], 422);
        }

        if ($err = $this->validateAnswersAgainstPost($post, $answers)) {
            return response()->json(['success'=>false,'message'=>$err], 422);
        }

        $meta = $this->normalizeJson($r->input('metadata'));
        $metaStr = is_array($meta) ? json_encode($meta) : null;

        $postId = (int)$post['id'];
        $userId = (int)$a['id'];

        try {
            return DB::transaction(function () use ($r, $postId, $userId, $answers, $metaStr) {

                $existing = DB::table(self::SUBS)
                    ->where('feedback_post_id', $postId)
                    ->where('student_id', $userId)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->first();

                // UPDATE
                if ($existing) {
                    DB::table(self::SUBS)->where('id', (int)$existing->id)->update([
                        'answers'       => json_encode($answers),
                        'metadata'      => $metaStr,
                        'status'        => 'submitted',
                        'updated_at'    => now(),
                        'updated_at_ip' => $this->ip($r),
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Updated',
                        'data'    => [
                            'id' => (int)$existing->id,
                            'feedback_post_id' => $postId,
                            'student_id' => $userId,
                        ],
                    ], 200);
                }

                // INSERT (first time)
                $id = DB::table(self::SUBS)->insertGetId([
                    'uuid' => (string) Str::uuid(),
                    'feedback_post_id' => $postId,
                    'student_id'   => $userId,
                    'answers'      => json_encode($answers),
                    'status'       => 'submitted',
                    'submitted_at' => now(),
                    'metadata'     => $metaStr,
                    'created_by'       => $userId,
                    'created_at_ip'    => $this->ip($r),
                    'updated_at_ip'    => $this->ip($r),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                    'deleted_at'       => null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Submitted',
                    'data'    => [
                        'id' => (int)$id,
                        'feedback_post_id' => $postId,
                        'student_id' => $userId,
                    ],
                ], 201);
            });
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Submit failed'], 500);
        }
    }

    /* =========================================================
     | 3) VIEW submissions
     |========================================================= */
    public function index(Request $r)
    {
        if ($resp = $this->requireAuth($r)) return $resp;

        $a = $this->actor($r);

        $q = DB::table(self::SUBS . ' as fs')
            ->select([
                'fs.id','fs.uuid',
                'fs.feedback_post_id',
                'fs.student_id',
                'fs.faculty_id',
                'fs.status',
                'fs.submitted_at',
                'fs.answers',
                'fs.metadata',
                'fs.created_at','fs.updated_at',
                'fs.deleted_at',
            ])
            ->whereNull('fs.deleted_at');

        if ($this->isStudent($r)) {
            $q->where('fs.student_id', (int)$a['id']);
        } else {
            if ($r->filled('student_id')) $q->where('fs.student_id', (int)$r->query('student_id'));
        }

        if ($r->filled('post_id')) $q->where('fs.feedback_post_id', (int)$r->query('post_id'));

        $q->orderBy('fs.id', 'desc');

        $rows = $q->limit(200)->get();

        $data = $rows->map(function ($x) {
            $answers = $this->normalizeJson($x->answers);
            $meta    = $this->normalizeJson($x->metadata);

            return [
                'id'   => (int)$x->id,
                'uuid' => (string)$x->uuid,
                'feedback_post_id' => (int)$x->feedback_post_id,
                'student_id'       => $x->student_id !== null ? (int)$x->student_id : null,
                'faculty_id'       => $x->faculty_id !== null ? (int)$x->faculty_id : null,
                'status'           => (string)($x->status ?? 'submitted'),
                'submitted_at'     => $x->submitted_at,
                'answers'          => is_array($answers) ? $answers : null,
                'metadata'         => is_array($meta) ? $meta : $meta,
                'created_at'       => $x->created_at,
                'updated_at'       => $x->updated_at,
            ];
        })->values();

        return response()->json(['success' => true, 'data' => $data]);
    }

    /* =========================================================
     | 4) SHOW one submission
     |========================================================= */
    public function show(Request $r, string $idOrUuid)
    {
        if ($resp = $this->requireAuth($r)) return $resp;

        $a = $this->actor($r);
        $w = $this->normalizeIdentifier($idOrUuid, 'fs');

        $q = DB::table(self::SUBS . ' as fs')
            ->select([
                'fs.id','fs.uuid',
                'fs.feedback_post_id',
                'fs.student_id',
                'fs.faculty_id',
                'fs.status',
                'fs.submitted_at',
                'fs.answers',
                'fs.metadata',
                'fs.created_at','fs.updated_at',
                'fs.deleted_at',
            ])
            ->whereNull('fs.deleted_at')
            ->where($w['col'], $w['val']);

        if ($this->isStudent($r)) {
            $q->where('fs.student_id', (int)$a['id']);
        }

        $row = $q->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        $answers = $this->normalizeJson($row->answers);
        $meta    = $this->normalizeJson($row->metadata);

        return response()->json([
            'success' => true,
            'data' => [
                'id'   => (int)$row->id,
                'uuid' => (string)$row->uuid,
                'feedback_post_id' => (int)$row->feedback_post_id,
                'student_id'       => $row->student_id !== null ? (int)$row->student_id : null,
                'faculty_id'       => $row->faculty_id !== null ? (int)$row->faculty_id : null,
                'status'           => (string)($row->status ?? 'submitted'),
                'submitted_at'     => $row->submitted_at,
                'answers'          => is_array($answers) ? $answers : null,
                'metadata'         => is_array($meta) ? $meta : $meta,
                'created_at'       => $row->created_at,
                'updated_at'       => $row->updated_at,
            ],
        ]);
    }

    /* =========================================================
     | 5) Admin delete submission (soft)
     |========================================================= */
    public function destroy(Request $r, string $idOrUuid)
    {
        if ($resp = $this->requireAuth($r)) return $resp;
        if (!$this->isAdminish($r)) {
            return response()->json(['success'=>false,'message'=>'Unauthorized Access'], 403);
        }

        $w = $this->normalizeIdentifier($idOrUuid, null);

        $row = DB::table(self::SUBS)->where($w['raw_col'], $w['val'])->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        if ($row->deleted_at) return response()->json(['success'=>true,'message'=>'Already deleted']);

        DB::table(self::SUBS)->where('id', $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $this->ip($r),
        ]);

        return response()->json(['success'=>true,'message'=>'Deleted']);
    }
}
