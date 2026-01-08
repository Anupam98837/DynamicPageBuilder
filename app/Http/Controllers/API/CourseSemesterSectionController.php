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
        $qText = trim((string)$r->query('q', ''));
        $sort  = (string)$r->query('sort', 'deleted_at');
        $dir   = strtolower((string)$r->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSort = ['deleted_at','updated_at','title','created_at'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'deleted_at';

        $q = $this->baseQuery(true)->whereNotNull('css.deleted_at');

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
        $semesterId = $r->query('semester_id', null);
        $courseId   = $r->query('course_id', null);

        $q = $this->baseQuery(false)
            ->where('css.status', 'active')
            ->where(function ($w) {
                $w->whereNull('css.publish_at')->orWhere('css.publish_at', '<=', now());
            })
            ->orderBy('css.sort_order', 'asc')
            ->orderBy('css.id', 'asc');

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
        // baseQuery uses alias "css"
        $w = $this->normalizeIdentifier($idOrUuid, 'css');

        $row = $this->baseQuery(true)->where($w['col'], $w['val'])->first();
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        // keep your existing behavior (returns list-style response)
        $fakeReq = request();
        $q = $this->baseQuery(true)->where($w['col'], $w['val']);
        return $this->respondList($fakeReq, $q);
    }

    /* =========================================================
     | CREATE
     | POST /api/course-semester-sections
     |========================================================= */
    public function store(Request $r)
    {
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

        $activeFlag = $r->input('active', $r->input('is_active', $r->input('isActive')));
        $status = $this->normalizeStatusFromActiveFlag($activeFlag, $r->input('status'));

        $meta = $r->input('metadata', null);
        if (is_array($meta)) $meta = json_encode($meta);
        if (is_string($meta)) {
            json_decode($meta, true);
            if (json_last_error() !== JSON_ERROR_NONE) $meta = null;
        }

        $id = DB::table('course_semester_sections')->insertGetId([
            'uuid'          => (string) Str::uuid(),
            'semester_id'   => (int) $r->input('semester_id'),
            'course_id'     => $r->filled('course_id') ? (int)$r->input('course_id') : null,
            'department_id' => $r->filled('department_id') ? (int)$r->input('department_id') : null,

            'title'       => (string)$r->input('title'),
            'description' => $r->input('description'),
            'sort_order'  => (int)($r->input('sort_order', 0) ?? 0),
            'status'      => $status,
            'publish_at'  => $r->filled('publish_at') ? $r->input('publish_at') : null,

            'created_by'    => $actor['id'] ?: null,
            'created_at_ip' => $this->ip($r),
            'updated_at_ip' => $this->ip($r),

            'metadata'   => $meta,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Created',
            'data'    => DB::table('course_semester_sections')->where('id', $id)->first(),
        ], 201);
    }

    /* =========================================================
     | UPDATE
     | PATCH /api/course-semester-sections/{id|uuid}
     |========================================================= */
    public function update(Request $r, string $idOrUuid)
    {
        // IMPORTANT: no alias for direct table queries
        $w = $this->normalizeIdentifier($idOrUuid, null);

        $exists = DB::table('course_semester_sections')->where($w['raw_col'], $w['val'])->first();
        if (!$exists) return response()->json(['success'=>false,'message'=>'Not found'], 404);

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

        $payload = [
            'updated_at'    => now(),
            'updated_at_ip' => $this->ip($r),
            'status'        => $status,
        ];

        foreach (['semester_id','course_id','department_id','title','description','sort_order','publish_at'] as $k) {
            if ($r->has($k)) {
                $payload[$k] = $r->filled($k) ? $r->input($k) : null;
            }
        }

        if ($r->has('sort_order')) $payload['sort_order'] = (int)($r->input('sort_order', 0) ?? 0);
        if ($r->has('metadata'))   $payload['metadata']   = $metaToStore;

        DB::table('course_semester_sections')->where($w['raw_col'], $w['val'])->update($payload);

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
        // IMPORTANT: no alias for direct table queries
        $w = $this->normalizeIdentifier($idOrUuid, null);

        $row = DB::table('course_semester_sections')->where($w['raw_col'], $w['val'])->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        if ($row->deleted_at) {
            return response()->json(['success'=>true,'message'=>'Already in trash']);
        }

        DB::table('course_semester_sections')->where('id', $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $this->ip($r),
        ]);

        return response()->json(['success'=>true,'message'=>'Moved to trash']);
    }

    /* =========================================================
     | RESTORE
     | POST /api/course-semester-sections/{id|uuid}/restore
     |========================================================= */
    public function restore(Request $r, string $idOrUuid)
    {
        // IMPORTANT: no alias for direct table queries
        $w = $this->normalizeIdentifier($idOrUuid, null);

        $row = DB::table('course_semester_sections')->where($w['raw_col'], $w['val'])->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        DB::table('course_semester_sections')->where('id', $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $this->ip($r),
        ]);

        return response()->json(['success'=>true,'message'=>'Restored']);
    }

    /* =========================================================
     | FORCE DELETE
     | DELETE /api/course-semester-sections/{id|uuid}/force
     |========================================================= */
    public function forceDelete(Request $r, string $idOrUuid)
    {
        // IMPORTANT: no alias for direct table queries
        $w = $this->normalizeIdentifier($idOrUuid, null);

        $row = DB::table('course_semester_sections')->where($w['raw_col'], $w['val'])->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        DB::table('course_semester_sections')->where('id', $row->id)->delete();

        return response()->json(['success'=>true,'message'=>'Deleted permanently']);
    }
}
