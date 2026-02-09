<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class FeedbackController extends Controller
{
    // ✅ Table (migration): feedbacks
    private const TABLE_FEEDBACKS       = 'feedbacks';
    private const TABLE_USERS           = 'users';
    private const TABLE_DEPTS           = 'departments';
    private const TABLE_COURSES         = 'courses';
    private const TABLE_SEMESTERS       = 'course_semesters';
    private const TABLE_SECTIONS        = 'course_semester_sections';
    private const TABLE_ACTIVITY_LOG    = 'user_data_activity_log';

    private const COL_UUID       = 'uuid';
    private const COL_DELETED_AT = 'deleted_at';

    /* ============================================
     | Helpers
     |============================================ */

    private function actor(Request $r): array
    {
        return [
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? optional($r->user())->id ?? 0),
            'role' => (string) ($r->attributes->get('auth_role') ?? ($r->user()->role ?? '')),
            'type' => (string) ($r->attributes->get('auth_tokenable_type') ?? ($r->user() ? get_class($r->user()) : '')),
            'uuid' => (string) ($r->attributes->get('auth_user_uuid') ?? ($r->user()->uuid ?? '')),
            'ip'   => (string) ($r->ip() ?? ''),
        ];
    }

    private function now(): string
    {
        return Carbon::now()->toDateTimeString();
    }

    private function rid(Request $r): string
    {
        $rid = (string) ($r->attributes->get('_rid') ?? '');
        if ($rid !== '') return $rid;

        $rid = (string) ($r->header('X-Request-Id') ?? '');
        if ($rid === '') $rid = (string) Str::uuid();

        $r->attributes->set('_rid', $rid);
        return $rid;
    }

    private function reqMeta(Request $r, array $actor = []): array
    {
        return [
            'rid'    => $this->rid($r),
            'path'   => $r->path(),
            'method' => $r->method(),
            'ip'     => $r->ip(),
            'ua'     => (string) ($r->userAgent() ?? ''),
            'actor'  => $actor ?: $this->actor($r),
            'query'  => $r->query(),
        ];
    }

    private function logInfo(string $msg, array $ctx = []): void
    {
        Log::info('[Feedback] ' . $msg, $ctx);
    }

    private function logWarn(string $msg, array $ctx = []): void
    {
        Log::warning('[Feedback] ' . $msg, $ctx);
    }

    private function logErr(string $msg, array $ctx = []): void
    {
        Log::error('[Feedback] ' . $msg, $ctx);
    }

    private function jsonSafe($data): ?string
    {
        if ($data === null) return null;

        // If it's already a JSON string, keep it
        if (is_string($data)) {
            $trim = trim($data);
            if ($trim === '') return null;
            json_decode($trim, true);
            if (json_last_error() === JSON_ERROR_NONE) return $trim;
            // Otherwise wrap it as JSON string
        }

        try {
            return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * ✅ Inserts into user_data_activity_log
     * - Never throws (won't break API functionality).
     */
    private function activityLog(
        Request $r,
        string $activity,
        string $module,
        string $tableName,
        ?int $recordId = null,
        ?array $changedFields = null,
        $oldValues = null,
        $newValues = null,
        ?string $note = null
    ): void {
        try {
            $actor = $this->actor($r);

            $performedBy = (int)($actor['id'] ?? 0);
            $role = trim((string)($actor['role'] ?? ''));
            $ip = (string)($r->ip() ?? ($actor['ip'] ?? ''));
            $ua = (string)($r->userAgent() ?? '');

            if (strlen($ua) > 512) $ua = substr($ua, 0, 512);

            DB::table(self::TABLE_ACTIVITY_LOG)->insert([
                'performed_by'      => $performedBy,
                'performed_by_role' => $role !== '' ? $role : null,
                'ip'                => $ip !== '' ? $ip : null,
                'user_agent'        => $ua !== '' ? $ua : null,

                'activity'   => $activity,
                'module'     => $module,

                'table_name' => $tableName,
                'record_id'  => $recordId,

                'changed_fields' => $this->jsonSafe($changedFields),
                'old_values'     => $this->jsonSafe($oldValues),
                'new_values'     => $this->jsonSafe($newValues),

                'log_note'   => $note,

                'created_at' => $this->now(),
                'updated_at' => $this->now(),
            ]);
        } catch (\Throwable $e) {
            // don't break anything if logging fails
            $this->logWarn('ACTIVITY_LOG: insert failed', [
                'error' => $e->getMessage(),
                'path'  => $r->path(),
                'method'=> $r->method(),
                'rid'   => $this->rid($r),
            ]);
        }
    }

    private function boolish($v, bool $default = false): bool
    {
        if ($v === null) return $default;
        if (is_bool($v)) return $v;
        $s = strtolower(trim((string)$v));
        if ($s === '') return $default;
        return in_array($s, ['1','true','yes','y','on'], true);
    }

    private function isAdminLike(string $role): bool
    {
        $r = strtolower(trim($role));
        return in_array($r, [
            'admin',
            'director',
            'principal',
            'hod',
            'technical_assistant',
            'it_person',
        ], true);
    }

    private function isNumericId($v): bool
    {
        return is_string($v) || is_int($v) ? preg_match('/^\d+$/', (string)$v) === 1 : false;
    }

    private function normalizeIdentifier(string $idOrUuid, ?string $alias = 'f'): array
    {
        $idOrUuid = trim($idOrUuid);

        $rawCol = $this->isNumericId($idOrUuid) ? 'id' : self::COL_UUID;
        $val    = ($rawCol === 'id') ? (int)$idOrUuid : $idOrUuid;

        $prefix = ($alias !== null && $alias !== '') ? ($alias . '.') : '';

        return [
            'col'     => $prefix . $rawCol, // e.g. "f.uuid" or "uuid"
            'raw_col' => $rawCol,           // e.g. "uuid"
            'val'     => $val,
        ];
    }

    private function normalizeMetadataToJson($meta): ?string
    {
        if ($meta === null) return null;

        if (is_array($meta)) {
            try {
                return json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            } catch (\Throwable $e) {
                return null;
            }
        }

        if (is_string($meta)) {
            $trim = trim($meta);
            if ($trim === '') return null;

            json_decode($trim, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $trim : null;
        }

        return null;
    }

    private function decodeMeta($meta)
    {
        if ($meta === null) return null;
        if (is_array($meta)) return $meta;

        if (is_string($meta)) {
            $d = json_decode($meta, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $d : $meta;
        }

        return $meta;
    }

    /**
     * Base query with joins so UI can render everything without extra calls.
     * NOTE: hides soft-deleted by default.
     */
    private function baseQuery(bool $includeDeleted = false)
    {
        $q = DB::table(self::TABLE_FEEDBACKS . ' as f')
            ->leftJoin(self::TABLE_DEPTS . ' as d', 'd.id', '=', 'f.department_id')
            ->leftJoin(self::TABLE_COURSES . ' as c', 'c.id', '=', 'f.course_id')
            ->leftJoin(self::TABLE_SEMESTERS . ' as cs', 'cs.id', '=', 'f.semester_id')
            ->leftJoin(self::TABLE_SECTIONS . ' as css', 'css.id', '=', 'f.section_id')
            ->leftJoin(self::TABLE_USERS . ' as uby', 'uby.id', '=', 'f.feedback_by_user_id')
            ->leftJoin(self::TABLE_USERS . ' as ufor', 'ufor.id', '=', 'f.feedback_for_user_id')
            ->select([
                'f.id',
                'f.uuid',

                'f.department_id',
                'f.course_id',
                'f.semester_id',
                'f.section_id',

                'f.feedback_by_user_id',
                'f.feedback_for_user_id',

                'f.title',
                'f.description',
                'f.rating',

                'f.is_anonymous',
                'f.status',
                'f.submitted_at',

                'f.created_by',
                'f.created_at_ip',
                'f.updated_at_ip',
                'f.metadata',

                'f.created_at',
                'f.updated_at',
                'f.deleted_at',

                'd.title as department_title',
                'c.title as course_title',
                'cs.title as semester_title',
                'css.title as section_title',

                'uby.name as feedback_by_name',
                'uby.email as feedback_by_email',
                'uby.role as feedback_by_role',

                'ufor.name as feedback_for_name',
                'ufor.email as feedback_for_email',
                'ufor.role as feedback_for_role',
            ]);

        if (!$includeDeleted) $q->whereNull('f.' . self::COL_DELETED_AT);
        return $q;
    }

    private function presentRow($row, array $viewer): array
    {
        $isAnonymous = (bool)($row->is_anonymous ?? false);
        $viewerIsAdmin = $this->isAdminLike((string)($viewer['role'] ?? ''));
        $viewerId = (int)($viewer['id'] ?? 0);

        // If anonymous and viewer is not admin and viewer isn't the author, hide author identity.
        $hideAuthor = $isAnonymous && !$viewerIsAdmin && $viewerId > 0 && $viewerId !== (int)($row->feedback_by_user_id ?? 0);

        return [
            'id'   => (int)$row->id,
            'uuid' => (string)$row->uuid,

            'department_id' => $row->department_id !== null ? (int)$row->department_id : null,
            'course_id'     => (int)$row->course_id,
            'semester_id'   => (int)$row->semester_id,
            'section_id'    => $row->section_id !== null ? (int)$row->section_id : null,

            'title'       => (string)$row->title,
            'description' => $row->description,
            'rating'      => $row->rating !== null ? (int)$row->rating : null,

            'is_anonymous' => (bool)$row->is_anonymous,
            'status'       => (string)$row->status,
            'submitted_at' => $row->submitted_at,

            'scope' => [
                'department' => $row->department_id ? ['id'=>(int)$row->department_id,'title'=>$row->department_title] : null,
                'course'     => ['id'=>(int)$row->course_id,'title'=>$row->course_title],
                'semester'   => ['id'=>(int)$row->semester_id,'title'=>$row->semester_title],
                'section'    => $row->section_id ? ['id'=>(int)$row->section_id,'title'=>$row->section_title] : null,
            ],

            'feedback_by' => $hideAuthor ? null : [
                'id'    => (int)$row->feedback_by_user_id,
                'name'  => $row->feedback_by_name,
                'email' => $row->feedback_by_email,
                'role'  => $row->feedback_by_role,
            ],

            'feedback_for' => [
                'id'    => (int)$row->feedback_for_user_id,
                'name'  => $row->feedback_for_name,
                'email' => $row->feedback_for_email,
                'role'  => $row->feedback_for_role,
            ],

            'metadata' => $this->decodeMeta($row->metadata ?? null),

            'created_by'    => $row->created_by !== null ? (int)$row->created_by : null,
            'created_at'    => $row->created_at,
            'updated_at'    => $row->updated_at,
            'created_at_ip' => $row->created_at_ip,
            'updated_at_ip' => $row->updated_at_ip,
            'deleted_at'    => $row->deleted_at,
        ];
    }

    /**
     * ✅ Permission rule you requested:
     * - Admin-like: can see all feedbacks
     * - Non-admin: can see ONLY feedbacks created by them (feedback_by_user_id = actor.id)
     */
    private function applyViewerScope($query, array $actor)
    {
        $actorId = (int)($actor['id'] ?? 0);
        $isAdmin = $this->isAdminLike((string)($actor['role'] ?? ''));

        if (!$isAdmin) {
            // if not logged in, block (consistent with your auth usage)
            if ($actorId <= 0) {
                // return a query that yields nothing; caller can also return 401 if you prefer
                $query->whereRaw('1=0');
                return $query;
            }

            $query->where('f.feedback_by_user_id', $actorId);
        }

        return $query;
    }

    /* =========================================================
     | LIST (GET)  -> no DB activity log needed (as requested)
     |========================================================= */
    public function index(Request $r)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $qText  = trim((string)$r->query('q', ''));
        $status = trim((string)$r->query('status', ''));

        $courseId     = $r->query('course_id', null);
        $semesterId   = $r->query('semester_id', null);
        $sectionId    = $r->query('section_id', null);
        $departmentId = $r->query('department_id', null);

        // NOTE: we will ignore feedback_by_user_id for non-admin (forced to actor)
        $byUserId  = $r->query('feedback_by_user_id', null);
        $forUserId = $r->query('feedback_for_user_id', null);

        $from = $r->query('from', null);
        $to   = $r->query('to', null);

        $page = max(1, (int)$r->query('page', 1));
        $per  = min(100, max(5, (int)$r->query('per_page', 20)));

        $sort = (string)$r->query('sort', 'submitted_at');
        $dir  = strtolower((string)$r->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSort = ['submitted_at','created_at','updated_at','rating','status','title','id'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'submitted_at';

        $this->logInfo('INDEX: request received', $meta + [
            'q' => $qText,
            'status' => $status,
            'page' => $page,
            'per_page' => $per,
        ]);

        try {
            $isAdmin = $this->isAdminLike((string)($actor['role'] ?? ''));
            $actorId = (int)($actor['id'] ?? 0);

            if (!$isAdmin && $actorId <= 0) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $q = $this->baseQuery(false);

            // ✅ apply requested visibility rule
            $this->applyViewerScope($q, $actor);

            if ($qText !== '') {
                $q->where(function ($w) use ($qText) {
                    $w->where('f.title', 'like', "%{$qText}%")
                      ->orWhere('f.description', 'like', "%{$qText}%")
                      ->orWhere('f.uuid', 'like', "%{$qText}%")
                      ->orWhere('ufor.name', 'like', "%{$qText}%")
                      ->orWhere('uby.name', 'like', "%{$qText}%");
                });
            }

            if ($status !== '') $q->where('f.status', $status);

            if ($courseId !== null && $courseId !== '')         $q->where('f.course_id', (int)$courseId);
            if ($semesterId !== null && $semesterId !== '')     $q->where('f.semester_id', (int)$semesterId);
            if ($sectionId !== null && $sectionId !== '')       $q->where('f.section_id', (int)$sectionId);
            if ($departmentId !== null && $departmentId !== '') $q->where('f.department_id', (int)$departmentId);

            // Only admin can freely filter by author/receiver
            if ($isAdmin) {
                if ($byUserId !== null && $byUserId !== '')  $q->where('f.feedback_by_user_id', (int)$byUserId);
                if ($forUserId !== null && $forUserId !== '') $q->where('f.feedback_for_user_id', (int)$forUserId);
            } else {
                if ($forUserId !== null && $forUserId !== '') $q->where('f.feedback_for_user_id', (int)$forUserId);
            }

            if ($from) $q->whereDate('f.submitted_at', '>=', $from);
            if ($to)   $q->whereDate('f.submitted_at', '<=', $to);

            $total = (clone $q)->count('f.id');

            $q->orderBy("f.$sort", $dir)->orderBy('f.id', 'desc');

            $rows = $q->forPage($page, $per)->get();

            $data = $rows->map(fn($row) => $this->presentRow($row, $actor))->values();

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $per,
                    'total' => $total,
                    'last_page' => (int) ceil(max(1, $total) / max(1, $per)),
                ],
                'meta' => [
                    'viewer_role' => (string)$actor['role'],
                    'viewer_id' => (int)$actor['id'],
                    'scoped' => $isAdmin ? 'all' : 'by_author',
                ],
            ]);
        } catch (\Throwable $e) {
            $this->logErr('INDEX: failed', $meta + ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load feedbacks',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     | SHOW (GET) -> no DB activity log needed (as requested)
     |========================================================= */
    public function show(Request $r, string $idOrUuid)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $this->logInfo('SHOW: request received', $meta + ['id_or_uuid' => $idOrUuid]);

        try {
            $isAdmin = $this->isAdminLike((string)($actor['role'] ?? ''));
            $actorId = (int)($actor['id'] ?? 0);

            if (!$isAdmin && $actorId <= 0) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $w = $this->normalizeIdentifier($idOrUuid, 'f');

            // ✅ enforce scope at query level
            $q = $this->baseQuery(false)->where($w['col'], $w['val']);
            $this->applyViewerScope($q, $actor);

            $row = $q->first();

            if (!$row) {
                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->presentRow($row, $actor),
            ]);
        } catch (\Throwable $e) {
            $this->logErr('SHOW: failed', $meta + ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load feedback',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     | CREATE
     | POST /api/feedbacks
     |========================================================= */
    public function store(Request $r)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $this->logInfo('STORE: request received', $meta);

        $payload = $r->all();

        $logKeys = [
            'department_id','course_id','semester_id','section_id',
            'feedback_for_user_id','feedback_by_user_id',
            'title','description','rating',
            'is_anonymous','status','submitted_at','metadata',
        ];
        $logInput = array_intersect_key($payload, array_flip($logKeys));

        $v = Validator::make($payload, [
            'department_id' => ['nullable','integer','exists:' . self::TABLE_DEPTS . ',id'],
            'course_id'     => ['required','integer','exists:' . self::TABLE_COURSES . ',id'],
            'semester_id'   => ['required','integer','exists:' . self::TABLE_SEMESTERS . ',id'],
            'section_id'    => ['nullable','integer','exists:' . self::TABLE_SECTIONS . ',id'],

            'feedback_for_user_id' => ['required','integer','exists:' . self::TABLE_USERS . ',id'],
            'feedback_by_user_id'  => ['nullable','integer','exists:' . self::TABLE_USERS . ',id'],

            'title'       => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'rating'      => ['nullable','integer','min:1','max:5'],

            'is_anonymous' => ['nullable'],
            'status'       => ['nullable','string','max:20', Rule::in(['submitted','reviewed','hidden'])],
            'submitted_at' => ['nullable','date'],

            'metadata' => ['nullable'],
        ]);

        if ($v->fails()) {
            $this->logWarn('STORE: validation failed', $meta + ['errors' => $v->errors()->toArray()]);

            // ✅ Activity log (POST) - validation fail
            $this->activityLog(
                $r,
                'create',
                'feedbacks',
                self::TABLE_FEEDBACKS,
                null,
                array_keys($logInput),
                null,
                $logInput,
                'FAILED(validation): rid=' . $this->rid($r) . ' errors=' . $this->jsonSafe($v->errors()->toArray())
            );

            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $v->errors(),
            ], 422);
        }

        try {
            $byId = (int)($actor['id'] ?? 0);
            $isAdmin = $this->isAdminLike((string)($actor['role'] ?? ''));

            if ($byId <= 0) {
                // ✅ Activity log (POST) - unauthenticated
                $this->activityLog(
                    $r,
                    'create',
                    'feedbacks',
                    self::TABLE_FEEDBACKS,
                    null,
                    array_keys($logInput),
                    null,
                    $logInput,
                    'FAILED(unauthenticated): rid=' . $this->rid($r)
                );

                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            if ($isAdmin && $r->filled('feedback_by_user_id')) {
                $byId = (int)$r->input('feedback_by_user_id');
            }

            $now = $this->now();
            $status = $r->filled('status') ? (string)$r->input('status') : 'submitted';
            $uuid = (string) Str::uuid();

            $insert = [
                'uuid' => $uuid,

                'department_id' => $r->filled('department_id') ? (int)$r->input('department_id') : null,
                'course_id'     => (int)$r->input('course_id'),
                'semester_id'   => (int)$r->input('semester_id'),
                'section_id'    => $r->filled('section_id') ? (int)$r->input('section_id') : null,

                'feedback_by_user_id'  => $byId,
                'feedback_for_user_id' => (int)$r->input('feedback_for_user_id'),

                'title'       => (string)$r->input('title'),
                'description' => $r->input('description'),
                'rating'      => $r->filled('rating') ? (int)$r->input('rating') : null,

                'is_anonymous' => $this->boolish($r->input('is_anonymous', false), false),
                'status'       => $status,
                'submitted_at' => $r->filled('submitted_at') ? $r->input('submitted_at') : $now,

                'created_by'    => $actor['id'] ?: null,
                'created_at_ip' => $actor['ip'] ?: null,
                'updated_at_ip' => $actor['ip'] ?: null,

                'metadata'   => $this->normalizeMetadataToJson($r->input('metadata', null)),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $id = DB::table(self::TABLE_FEEDBACKS)->insertGetId($insert);

            // ✅ Activity log (POST) - success
            $newSnapshot = $insert;
            $newSnapshot['id'] = (int)$id;

            $this->activityLog(
                $r,
                'create',
                'feedbacks',
                self::TABLE_FEEDBACKS,
                (int)$id,
                array_keys($insert),
                null,
                $newSnapshot,
                'OK: rid=' . $this->rid($r)
            );

            $row = $this->baseQuery(false)->where('f.id', (int)$id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Created',
                'data'    => $row ? $this->presentRow($row, $actor) : null,
            ], 201);
        } catch (\Throwable $e) {
            $this->logErr('STORE: failed', $meta + ['error' => $e->getMessage()]);

            // ✅ Activity log (POST) - exception
            $this->activityLog(
                $r,
                'create',
                'feedbacks',
                self::TABLE_FEEDBACKS,
                null,
                array_keys($logInput),
                null,
                $logInput,
                'FAILED(exception): rid=' . $this->rid($r) . ' error=' . $e->getMessage()
            );

            return response()->json([
                'success' => false,
                'message' => 'Failed to create feedback',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     | UPDATE
     | PATCH /api/feedbacks/{id|uuid}
     | (No delete in this controller)
     |========================================================= */
    public function update(Request $r, string $idOrUuid)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $this->logInfo('UPDATE: request received', $meta + ['id_or_uuid' => $idOrUuid]);

        $w = $this->normalizeIdentifier($idOrUuid, null);

        $logKeys = [
            'department_id','course_id','semester_id','section_id',
            'feedback_for_user_id',
            'title','description','rating',
            'is_anonymous','status','submitted_at','metadata',
        ];
        $logInput = array_intersect_key($r->all(), array_flip($logKeys));

        try {
            $existing = DB::table(self::TABLE_FEEDBACKS)
                ->where($w['raw_col'], $w['val'])
                ->whereNull(self::COL_DELETED_AT)
                ->first();

            if (!$existing) {
                // ✅ Activity log (PATCH) - not found
                $this->activityLog(
                    $r,
                    'update',
                    'feedbacks',
                    self::TABLE_FEEDBACKS,
                    null,
                    array_keys($logInput),
                    null,
                    $logInput,
                    'FAILED(not_found): rid=' . $this->rid($r) . ' identifier=' . $idOrUuid
                );

                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }

            $isAdmin = $this->isAdminLike((string)($actor['role'] ?? ''));
            $actorId = (int)($actor['id'] ?? 0);

            if (!$isAdmin && $actorId > 0 && $actorId !== (int)$existing->feedback_by_user_id) {
                // ✅ Activity log (PATCH) - forbidden
                $this->activityLog(
                    $r,
                    'update',
                    'feedbacks',
                    self::TABLE_FEEDBACKS,
                    (int)$existing->id,
                    array_keys($logInput),
                    ['feedback_by_user_id' => (int)$existing->feedback_by_user_id],
                    $logInput,
                    'FAILED(forbidden): rid=' . $this->rid($r)
                );

                return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
            }

            $v = Validator::make($r->all(), [
                'department_id' => ['sometimes','nullable','integer','exists:' . self::TABLE_DEPTS . ',id'],
                'course_id'     => ['sometimes','required','integer','exists:' . self::TABLE_COURSES . ',id'],
                'semester_id'   => ['sometimes','required','integer','exists:' . self::TABLE_SEMESTERS . ',id'],
                'section_id'    => ['sometimes','nullable','integer','exists:' . self::TABLE_SECTIONS . ',id'],

                'feedback_for_user_id' => ['sometimes','required','integer','exists:' . self::TABLE_USERS . ',id'],

                'title'       => ['sometimes','required','string','max:255'],
                'description' => ['sometimes','nullable','string'],
                'rating'      => ['sometimes','nullable','integer','min:1','max:5'],

                'is_anonymous' => ['sometimes'],
                'status'       => ['sometimes','nullable','string','max:20', Rule::in(['submitted','reviewed','hidden'])],
                'submitted_at' => ['sometimes','nullable','date'],

                'metadata' => ['sometimes','nullable'],
            ]);

            if ($v->fails()) {
                $this->logWarn('UPDATE: validation failed', $meta + ['errors' => $v->errors()->toArray()]);

                // ✅ Activity log (PATCH) - validation fail
                $this->activityLog(
                    $r,
                    'update',
                    'feedbacks',
                    self::TABLE_FEEDBACKS,
                    (int)$existing->id,
                    array_keys($logInput),
                    null,
                    $logInput,
                    'FAILED(validation): rid=' . $this->rid($r) . ' errors=' . $this->jsonSafe($v->errors()->toArray())
                );

                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors'  => $v->errors(),
                ], 422);
            }

            $now = $this->now();

            $upd = [
                'updated_at'    => $now,
                'updated_at_ip' => $actor['ip'] ?: null,
            ];

            $adminEditable = ['department_id','course_id','semester_id','section_id','feedback_for_user_id','status','submitted_at'];
            $userEditable  = ['title','description','rating','is_anonymous','metadata'];

            foreach ($userEditable as $k) {
                if (!$r->has($k)) continue;

                if ($k === 'is_anonymous') {
                    $upd[$k] = $this->boolish($r->input($k), (bool)$existing->is_anonymous);
                    continue;
                }

                if ($k === 'metadata') {
                    $upd[$k] = $this->normalizeMetadataToJson($r->input('metadata'));
                    continue;
                }

                $upd[$k] = $r->filled($k) ? $r->input($k) : null;
            }

            if ($isAdmin) {
                foreach ($adminEditable as $k) {
                    if (!$r->has($k)) continue;
                    $upd[$k] = $r->filled($k) ? $r->input($k) : null;
                }
            }

            DB::table(self::TABLE_FEEDBACKS)->where($w['raw_col'], $w['val'])->update($upd);

            // ✅ Compute diff for activity log (only for fields actually changed)
            $after = DB::table(self::TABLE_FEEDBACKS)->where('id', (int)$existing->id)->first();

            $changed = [];
            $oldVals = [];
            $newVals = [];

            foreach ($upd as $k => $vNew) {
                // you can skip meta fields if you don't want them; keeping them is useful
                $vOld = $existing->$k ?? null;
                $vAft = $after->$k ?? null;

                // normalize booleans and numeric strings a bit
                if ($k === 'is_anonymous') {
                    $vOld = (int)((bool)$vOld);
                    $vAft = (int)((bool)$vAft);
                }

                $same =
                    ($vOld === null && $vAft === null) ||
                    ((string)$vOld === (string)$vAft);

                if (!$same) {
                    $changed[] = $k;
                    $oldVals[$k] = $vOld;
                    $newVals[$k] = $vAft;
                }
            }

            // ✅ Activity log (PATCH) - success
            $this->activityLog(
                $r,
                'update',
                'feedbacks',
                self::TABLE_FEEDBACKS,
                (int)$existing->id,
                $changed,
                $oldVals,
                $newVals,
                'OK: rid=' . $this->rid($r)
            );

            // ✅ keep response scoped: non-admin can only ever update their own, so this is safe
            $row = $this->baseQuery(false)->where('f.id', (int)$existing->id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Updated',
                'data'    => $row ? $this->presentRow($row, $actor) : null,
            ]);
        } catch (\Throwable $e) {
            $this->logErr('UPDATE: failed', $meta + ['error' => $e->getMessage()]);

            // ✅ Activity log (PATCH) - exception
            $this->activityLog(
                $r,
                'update',
                'feedbacks',
                self::TABLE_FEEDBACKS,
                null,
                array_keys($logInput),
                null,
                $logInput,
                'FAILED(exception): rid=' . $this->rid($r) . ' identifier=' . $idOrUuid . ' error=' . $e->getMessage()
            );

            return response()->json([
                'success' => false,
                'message' => 'Failed to update feedback',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
