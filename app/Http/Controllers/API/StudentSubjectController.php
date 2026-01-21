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

class StudentSubjectController extends Controller
{
    // ✅ Table: student_subject
    private const TABLE            = 'student_subject';
    private const TABLE_USERS      = 'users';
    private const TABLE_DEPTS      = 'departments';
    private const TABLE_COURSES    = 'courses';
    private const TABLE_SEMESTERS  = 'course_semesters';

    private const COL_UUID         = 'uuid';
    private const COL_DELETED_AT   = 'deleted_at';

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
        Log::info('[StudentSubject] ' . $msg, $ctx);
    }

    private function logWarn(string $msg, array $ctx = []): void
    {
        Log::warning('[StudentSubject] ' . $msg, $ctx);
    }

    private function logErr(string $msg, array $ctx = []): void
    {
        Log::error('[StudentSubject] ' . $msg, $ctx);
    }

    private function isAdminLike(string $role): bool
    {
        $r = strtolower(trim($role));
        return in_array($r, [
            'admin',
            'director',
            'principal',
            'hod',
            'faculty',
            'technical_assistant',
            'it_person',
        ], true);
    }

    private function isNumericId($v): bool
    {
        return is_string($v) || is_int($v) ? preg_match('/^\d+$/', (string)$v) === 1 : false;
    }

    private function normalizeIdentifier(string $idOrUuid, ?string $alias = 'ss'): array
    {
        $idOrUuid = trim($idOrUuid);

        $rawCol = $this->isNumericId($idOrUuid) ? 'id' : self::COL_UUID;
        $val    = ($rawCol === 'id') ? (int)$idOrUuid : $idOrUuid;

        $prefix = ($alias !== null && $alias !== '') ? ($alias . '.') : '';

        return [
            'col'     => $prefix . $rawCol, // e.g. "ss.uuid" or "uuid"
            'raw_col' => $rawCol,           // e.g. "uuid"
            'val'     => $val,
        ];
    }

    private function normalizeJsonToString($value): ?string
    {
        if ($value === null) return null;

        // If array => encode
        if (is_array($value)) {
            try {
                return json_encode($value, JSON_UNESCAPED_UNICODE);
            } catch (\Throwable $e) {
                return null;
            }
        }

        // If string => must be valid JSON
        if (is_string($value)) {
            $trim = trim($value);
            if ($trim === '') return null;

            json_decode($trim, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $trim : null;
        }

        return null;
    }

    private function decodeJson($value)
    {
        if ($value === null) return null;
        if (is_array($value)) return $value;

        if (is_string($value)) {
            $d = json_decode($value, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $d : $value;
        }

        return $value;
    }

    /**
     * Base query with joins
     * NOTE: hides soft-deleted by default.
     */
    private function baseQuery(bool $includeDeleted = false)
    {
        $q = DB::table(self::TABLE . ' as ss')
            ->leftJoin(self::TABLE_DEPTS . ' as d', 'd.id', '=', 'ss.department_id')
            ->leftJoin(self::TABLE_COURSES . ' as c', 'c.id', '=', 'ss.course_id')
            ->leftJoin(self::TABLE_SEMESTERS . ' as cs', 'cs.id', '=', 'ss.semester_id')
            ->leftJoin(self::TABLE_USERS . ' as u', 'u.id', '=', 'ss.created_by')
            ->select([
                'ss.id',
                'ss.uuid',
                'ss.department_id',
                'ss.course_id',
                'ss.semester_id',

                'ss.subject_json',
                'ss.status',

                'ss.created_by',
                'ss.created_at_ip',
                'ss.updated_at_ip',
                'ss.metadata',

                'ss.created_at',
                'ss.updated_at',
                'ss.deleted_at',

                'd.title as department_title',
                'c.title as course_title',
                'cs.title as semester_title',

                'u.name as created_by_name',
                'u.email as created_by_email',
                'u.role as created_by_role',
            ]);

        if (!$includeDeleted) {
            $q->whereNull('ss.' . self::COL_DELETED_AT);
        }

        return $q;
    }

    private function presentRow($row): array
    {
        return [
            'id'   => (int) $row->id,
            'uuid' => (string) $row->uuid,

            'department_id' => (int) $row->department_id,
            'course_id'     => (int) $row->course_id,
            'semester_id'   => $row->semester_id !== null ? (int) $row->semester_id : null,

            'subject_json' => $this->decodeJson($row->subject_json),

            'status' => (string) $row->status,

            'scope' => [
                'department' => $row->department_id ? ['id' => (int)$row->department_id, 'title' => $row->department_title] : null,
                'course'     => $row->course_id ? ['id' => (int)$row->course_id, 'title' => $row->course_title] : null,
                'semester'   => $row->semester_id ? ['id' => (int)$row->semester_id, 'title' => $row->semester_title] : null,
            ],

            'metadata' => $this->decodeJson($row->metadata),

            'created_by' => $row->created_by !== null ? [
                'id'    => (int) $row->created_by,
                'name'  => $row->created_by_name,
                'email' => $row->created_by_email,
                'role'  => $row->created_by_role,
            ] : null,

            'created_at'    => $row->created_at,
            'updated_at'    => $row->updated_at,
            'deleted_at'    => $row->deleted_at,
            'created_at_ip' => $row->created_at_ip,
            'updated_at_ip' => $row->updated_at_ip,
        ];
    }

    /* =========================================================
     | LIST
     | GET /api/student-subjects
     |========================================================= */
    public function index(Request $r)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $qText  = trim((string)$r->query('q', ''));
        $status = trim((string)$r->query('status', ''));

        $departmentId = $r->query('department_id', null);
        $courseId     = $r->query('course_id', null);
        $semesterId   = $r->query('semester_id', null);

        $page = max(1, (int)$r->query('page', 1));
        $per  = min(100, max(5, (int)$r->query('per_page', 20)));

        $sort = (string)$r->query('sort', 'created_at');
        $dir  = strtolower((string)$r->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSort = ['created_at','updated_at','status','id'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'created_at';

        $this->logInfo('INDEX: request received', $meta + [
            'q' => $qText,
            'status' => $status,
            'page' => $page,
            'per_page' => $per,
        ]);

        try {
            $q = $this->baseQuery(false);

            if ($qText !== '') {
                $q->where(function ($w) use ($qText) {
                    $w->where('ss.uuid', 'like', "%{$qText}%")
                      ->orWhere('d.title', 'like', "%{$qText}%")
                      ->orWhere('c.title', 'like', "%{$qText}%")
                      ->orWhere('cs.title', 'like', "%{$qText}%");
                });
            }

            if ($status !== '') $q->where('ss.status', $status);
            if ($departmentId !== null && $departmentId !== '') $q->where('ss.department_id', (int)$departmentId);
            if ($courseId !== null && $courseId !== '')         $q->where('ss.course_id', (int)$courseId);
            if ($semesterId !== null && $semesterId !== '')     $q->where('ss.semester_id', (int)$semesterId);

            $total = (clone $q)->count('ss.id');

            $q->orderBy("ss.$sort", $dir)->orderBy('ss.id', 'desc');

            $rows = $q->forPage($page, $per)->get();
            $data = $rows->map(fn($row) => $this->presentRow($row))->values();

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $per,
                    'total' => $total,
                    'last_page' => (int) ceil(max(1, $total) / max(1, $per)),
                ],
            ]);
        } catch (\Throwable $e) {
            $this->logErr('INDEX: failed', $meta + ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load student subjects',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     | CURRENT (Active only)
     | GET /api/student-subjects/current
     |========================================================= */
    public function current(Request $r)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $departmentId = $r->query('department_id', null);
        $courseId     = $r->query('course_id', null);
        $semesterId   = $r->query('semester_id', null);

        $this->logInfo('CURRENT: request received', $meta);

        try {
            $q = $this->baseQuery(false)->where('ss.status', 'active');

            if ($departmentId !== null && $departmentId !== '') $q->where('ss.department_id', (int)$departmentId);
            if ($courseId !== null && $courseId !== '')         $q->where('ss.course_id', (int)$courseId);
            if ($semesterId !== null && $semesterId !== '')     $q->where('ss.semester_id', (int)$semesterId);

            $rows = $q->orderBy('ss.id', 'desc')->get();
            $data = $rows->map(fn($row) => $this->presentRow($row))->values();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            $this->logErr('CURRENT: failed', $meta + ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load current student subjects',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     | TRASH
     | GET /api/student-subjects/trash
     |========================================================= */
    public function trash(Request $r)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $this->logInfo('TRASH: request received', $meta);

        try {
            $rows = $this->baseQuery(true)
                ->whereNotNull('ss.deleted_at')
                ->orderBy('ss.deleted_at', 'desc')
                ->get();

            $data = $rows->map(fn($row) => $this->presentRow($row))->values();

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            $this->logErr('TRASH: failed', $meta + ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load trash',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     | SHOW
     | GET /api/student-subjects/{id|uuid}
     |========================================================= */
    public function show(Request $r, string $idOrUuid)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $this->logInfo('SHOW: request received', $meta + ['id_or_uuid' => $idOrUuid]);

        try {
            $w = $this->normalizeIdentifier($idOrUuid, 'ss');

            $row = $this->baseQuery(false)->where($w['col'], $w['val'])->first();

            if (!$row) {
                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->presentRow($row),
            ]);
        } catch (\Throwable $e) {
            $this->logErr('SHOW: failed', $meta + ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load record',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     | CREATE
     | POST /api/student-subjects
     |========================================================= */
    public function store(Request $r)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $this->logInfo('STORE: request received', $meta);

        $v = Validator::make($r->all(), [
            'department_id' => ['required','integer','exists:' . self::TABLE_DEPTS . ',id'],
            'course_id'     => ['required','integer','exists:' . self::TABLE_COURSES . ',id'],
            'semester_id'   => ['nullable','integer','exists:' . self::TABLE_SEMESTERS . ',id'],

            // ✅ required JSON structure (array of objects)
            'subject_json'                      => ['required'],
            'subject_json.*.student_id'         => ['required','integer','min:1'],
            'subject_json.*.subject_id'         => ['required','integer','min:1'],
            'subject_json.*.current_attendance' => ['required','numeric','min:0','max:100'],

            'status'   => ['nullable','string','max:20', Rule::in(['active','inactive'])],
            'metadata' => ['nullable'],
        ]);

        if ($v->fails()) {
            $this->logWarn('STORE: validation failed', $meta + ['errors' => $v->errors()->toArray()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $v->errors(),
            ], 422);
        }

        try {
            // ✅ auth check (same style)
            if ((int)$actor['id'] <= 0) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $now = $this->now();

            $subjectJsonString = $this->normalizeJsonToString($r->input('subject_json'));
            if (!$subjectJsonString) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid subject_json format',
                ], 422);
            }

            $id = DB::table(self::TABLE)->insertGetId([
                'uuid' => (string) Str::uuid(),

                'department_id' => (int) $r->input('department_id'),
                'course_id'     => (int) $r->input('course_id'),
                'semester_id'   => $r->filled('semester_id') ? (int)$r->input('semester_id') : null,

                'subject_json' => $subjectJsonString,
                'status'       => $r->filled('status') ? (string)$r->input('status') : 'active',

                'created_by'    => (int) ($actor['id'] ?: null),
                'created_at_ip' => $actor['ip'] ?: null,
                'updated_at_ip' => $actor['ip'] ?: null,

                'metadata' => $this->normalizeJsonToString($r->input('metadata', null)),

                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $row = $this->baseQuery(false)->where('ss.id', (int)$id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Created',
                'data'    => $row ? $this->presentRow($row) : null,
            ], 201);
        } catch (\Throwable $e) {
            $this->logErr('STORE: failed', $meta + ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create record',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     | UPDATE
     | PATCH/PUT /api/student-subjects/{id|uuid}
     |========================================================= */
    public function update(Request $r, string $idOrUuid)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $this->logInfo('UPDATE: request received', $meta + ['id_or_uuid' => $idOrUuid]);

        try {
            if ((int)$actor['id'] <= 0) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $w = $this->normalizeIdentifier($idOrUuid, null);

            $existing = DB::table(self::TABLE)
                ->where($w['raw_col'], $w['val'])
                ->whereNull(self::COL_DELETED_AT)
                ->first();

            if (!$existing) {
                return response()->json(['success' => false, 'message' => 'Not found'], 404);
            }

            $v = Validator::make($r->all(), [
                'department_id' => ['sometimes','required','integer','exists:' . self::TABLE_DEPTS . ',id'],
                'course_id'     => ['sometimes','required','integer','exists:' . self::TABLE_COURSES . ',id'],
                'semester_id'   => ['sometimes','nullable','integer','exists:' . self::TABLE_SEMESTERS . ',id'],

                'subject_json'                      => ['sometimes','required'],
                'subject_json.*.student_id'         => ['required_with:subject_json','integer','min:1'],
                'subject_json.*.subject_id'         => ['required_with:subject_json','integer','min:1'],
                'subject_json.*.current_attendance' => ['required_with:subject_json','numeric','min:0','max:100'],

                'status'   => ['sometimes','nullable','string','max:20', Rule::in(['active','inactive'])],
                'metadata' => ['sometimes','nullable'],
            ]);

            if ($v->fails()) {
                $this->logWarn('UPDATE: validation failed', $meta + ['errors' => $v->errors()->toArray()]);
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

            // scope fields
            if ($r->has('department_id')) $upd['department_id'] = (int) $r->input('department_id');
            if ($r->has('course_id'))     $upd['course_id']     = (int) $r->input('course_id');
            if ($r->has('semester_id'))   $upd['semester_id']   = $r->filled('semester_id') ? (int)$r->input('semester_id') : null;

            // subject_json
            if ($r->has('subject_json')) {
                $subjectJsonString = $this->normalizeJsonToString($r->input('subject_json'));
                if (!$subjectJsonString) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid subject_json format',
                    ], 422);
                }
                $upd['subject_json'] = $subjectJsonString;
            }

            if ($r->has('status')) {
                $upd['status'] = $r->filled('status') ? (string)$r->input('status') : (string)($existing->status ?? 'active');
            }

            if ($r->has('metadata')) {
                $upd['metadata'] = $this->normalizeJsonToString($r->input('metadata', null));
            }

            DB::table(self::TABLE)->where($w['raw_col'], $w['val'])->update($upd);

            $row = $this->baseQuery(false)->where('ss.id', (int)$existing->id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Updated',
                'data'    => $row ? $this->presentRow($row) : null,
            ]);
        } catch (\Throwable $e) {
            $this->logErr('UPDATE: failed', $meta + ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update record',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     | SOFT DELETE
     | DELETE /api/student-subjects/{id|uuid}
     |========================================================= */
    public function destroy(Request $r, string $idOrUuid)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $this->logInfo('DESTROY: request received', $meta + ['id_or_uuid' => $idOrUuid]);

        try {
            if ((int)$actor['id'] <= 0) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $w = $this->normalizeIdentifier($idOrUuid, null);

            $existing = DB::table(self::TABLE)
                ->where($w['raw_col'], $w['val'])
                ->whereNull(self::COL_DELETED_AT)
                ->first();

            if (!$existing) return response()->json(['success' => false, 'message' => 'Not found'], 404);

            $now = $this->now();

            DB::table(self::TABLE)
                ->where('id', (int)$existing->id)
                ->update([
                    'deleted_at'    => $now,
                    'updated_at'    => $now,
                    'updated_at_ip' => $actor['ip'] ?: null,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Moved to trash',
            ]);
        } catch (\Throwable $e) {
            $this->logErr('DESTROY: failed', $meta + ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete record',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     | RESTORE
     | POST /api/student-subjects/{id|uuid}/restore
     |========================================================= */
    public function restore(Request $r, string $idOrUuid)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $this->logInfo('RESTORE: request received', $meta + ['id_or_uuid' => $idOrUuid]);

        try {
            if ((int)$actor['id'] <= 0) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $w = $this->normalizeIdentifier($idOrUuid, null);

            $existing = DB::table(self::TABLE)
                ->where($w['raw_col'], $w['val'])
                ->whereNotNull(self::COL_DELETED_AT)
                ->first();

            if (!$existing) return response()->json(['success' => false, 'message' => 'Not found in trash'], 404);

            $now = $this->now();

            DB::table(self::TABLE)
                ->where('id', (int)$existing->id)
                ->update([
                    'deleted_at'    => null,
                    'updated_at'    => $now,
                    'updated_at_ip' => $actor['ip'] ?: null,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Restored',
            ]);
        } catch (\Throwable $e) {
            $this->logErr('RESTORE: failed', $meta + ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore record',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     | FORCE DELETE
     | DELETE /api/student-subjects/{id|uuid}/force
     |========================================================= */
    public function forceDelete(Request $r, string $idOrUuid)
    {
        $actor = $this->actor($r);
        $meta  = $this->reqMeta($r, $actor);

        $this->logInfo('FORCE DELETE: request received', $meta + ['id_or_uuid' => $idOrUuid]);

        try {
            if ((int)$actor['id'] <= 0) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $w = $this->normalizeIdentifier($idOrUuid, null);

            $existing = DB::table(self::TABLE)
                ->where($w['raw_col'], $w['val'])
                ->first();

            if (!$existing) return response()->json(['success' => false, 'message' => 'Not found'], 404);

            DB::table(self::TABLE)->where('id', (int)$existing->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permanently deleted',
            ]);
        } catch (\Throwable $e) {
            $this->logErr('FORCE DELETE: failed', $meta + ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to force delete record',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
