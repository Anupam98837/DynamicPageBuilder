<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SubjectController extends Controller
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
     * - When you query using baseQuery() you MUST use alias 's'
     * - When you query using DB::table('subjects') you MUST NOT use alias
     */
    private function normalizeIdentifier(string $idOrUuid, ?string $alias = 's'): array
    {
        $idOrUuid = trim($idOrUuid);

        $rawCol = $this->isNumericId($idOrUuid) ? 'id' : 'uuid';
        $val    = ($rawCol === 'id') ? (int)$idOrUuid : $idOrUuid;

        $prefix = ($alias !== null && $alias !== '') ? ($alias . '.') : '';

        return [
            'col'     => $prefix . $rawCol, // e.g. "s.uuid" or "uuid"
            'raw_col' => $rawCol,           // e.g. "uuid"
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

    protected function baseQuery(bool $includeDeleted = false)
    {
        $select = [
            's.id',
            's.uuid',
            's.department_id',
            's.subject_code',
            's.title',
            's.short_title',
            's.description',
            's.subject_type',
            's.credits',
            's.lecture_hours',
            's.practical_hours',
            's.sort_order',
            's.status',
            's.publish_at',
            's.expire_at',
            's.metadata',
            's.created_by',
            's.created_at',
            's.updated_at',
            's.created_at_ip',
            's.updated_at_ip',
            's.deleted_at',

            'd.title as department_title',
        ];

        $q = DB::table('subjects as s')
            ->leftJoin('departments as d', 'd.id', '=', 's.department_id')
            ->select($select);

        if (!$includeDeleted) $q->whereNull('s.deleted_at');
        return $q;
    }

    private function respondList(Request $r, $q)
    {
        $page = max(1, (int)$r->query('page', 1));
        $per  = min(200, max(5, (int)$r->query('per_page', 20)));

        $total = (clone $q)->count('s.id');
        $rows  = $q->forPage($page, $per)->get();

        $data = $rows->map(function ($x) {
            $meta = $this->normalizeMetadata($x->metadata ?? null);
            $status = (string)($x->status ?? 'active');

            return [
                'id'            => (int)$x->id,
                'uuid'          => (string)$x->uuid,

                'department_id' => $x->department_id !== null ? (int)$x->department_id : null,

                'subject_code'  => (string)($x->subject_code ?? ''),
                'title'         => (string)($x->title ?? ''),
                'short_title'   => $x->short_title,
                'description'   => $x->description,          // HTML allowed
                'subject_type'  => $x->subject_type,         // dynamic string (no restriction)

                'credits'        => $x->credits !== null ? (int)$x->credits : null,
                'lecture_hours'  => $x->lecture_hours !== null ? (int)$x->lecture_hours : null,
                'practical_hours'=> $x->practical_hours !== null ? (int)$x->practical_hours : null,

                'sort_order'    => (int)($x->sort_order ?? 0),
                'status'        => $status,
                'publish_at'    => $x->publish_at,
                'expire_at'     => $x->expire_at,

                'is_active'     => $status === 'active',

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
     | GET /api/subjects
     |========================================================= */
    public function index(Request $r)
    {
        $qText  = trim((string)$r->query('q', ''));
        $status = trim((string)$r->query('status', '')); // active|inactive
        $deptId = $r->query('department_id', null);
        $type   = trim((string)$r->query('subject_type', ''));

        $sort = (string)$r->query('sort', 'updated_at');
        $dir  = strtolower((string)$r->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSort = ['created_at','updated_at','title','subject_code','sort_order','publish_at','status'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'updated_at';

        $q = $this->baseQuery(false);

        if ($qText !== '') {
            $q->where(function ($w) use ($qText) {
                $w->where('s.title', 'like', "%{$qText}%")
                  ->orWhere('s.subject_code', 'like', "%{$qText}%")
                  ->orWhere('s.short_title', 'like', "%{$qText}%")
                  ->orWhere('s.uuid', 'like', "%{$qText}%")
                  ->orWhere('d.title', 'like', "%{$qText}%");
            });
        }

        if ($status !== '') $q->where('s.status', $status);

        if ($deptId !== null && $deptId !== '') $q->where('s.department_id', (int)$deptId);

        // dynamic filter (still no restriction)
        if ($type !== '') $q->where('s.subject_type', $type);

        // compatibility: ?active=1 / ?active=0
        if ($r->has('active')) {
            $av = (string)$r->query('active');
            if (in_array($av, ['1','true','yes'], true)) $q->where('s.status', 'active');
            if (in_array($av, ['0','false','no'], true)) $q->where('s.status', 'inactive');
        }

        $q->orderBy("s.$sort", $dir)->orderBy('s.id', 'desc');

        return $this->respondList($r, $q);
    }

    /* =========================================================
     | TRASH
     | GET /api/subjects/trash
     |========================================================= */
    public function trash(Request $r)
    {
        $qText = trim((string)$r->query('q', ''));
        $sort  = (string)$r->query('sort', 'deleted_at');
        $dir   = strtolower((string)$r->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSort = ['deleted_at','updated_at','title','created_at','subject_code'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'deleted_at';

        $q = $this->baseQuery(true)->whereNotNull('s.deleted_at');

        if ($qText !== '') {
            $q->where(function ($w) use ($qText) {
                $w->where('s.title', 'like', "%{$qText}%")
                  ->orWhere('s.subject_code', 'like', "%{$qText}%")
                  ->orWhere('s.short_title', 'like', "%{$qText}%")
                  ->orWhere('s.uuid', 'like', "%{$qText}%")
                  ->orWhere('d.title', 'like', "%{$qText}%");
            });
        }

        $q->orderBy("s.$sort", $dir)->orderBy('s.id', 'desc');

        return $this->respondList($r, $q);
    }

    /* =========================================================
     | CURRENT (frontend-friendly)
     | GET /api/subjects/current
     |========================================================= */
    public function current(Request $r)
    {
        $deptId = $r->query('department_id', null);
        $type   = trim((string)$r->query('subject_type', ''));

        $q = $this->baseQuery(false)
            ->where('s.status', 'active')
            ->where(function ($w) {
                $w->whereNull('s.publish_at')->orWhere('s.publish_at', '<=', now());
            })
            ->where(function ($w) {
                $w->whereNull('s.expire_at')->orWhere('s.expire_at', '>', now());
            })
            ->orderBy('s.sort_order', 'asc')
            ->orderBy('s.id', 'asc');

        if ($deptId !== null && $deptId !== '') $q->where('s.department_id', (int)$deptId);
        if ($type !== '') $q->where('s.subject_type', $type);

        return $this->respondList($r, $q);
    }

    /* =========================================================
     | SHOW
     | GET /api/subjects/{id|uuid}
     |========================================================= */
    public function show(string $idOrUuid)
    {
        $w = $this->normalizeIdentifier($idOrUuid, 's');
        $row = $this->baseQuery(true)->where($w['col'], $w['val'])->first();
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        $fakeReq = request();
        $q = $this->baseQuery(true)->where($w['col'], $w['val']);
        return $this->respondList($fakeReq, $q);
    }

    /* =========================================================
     | CREATE
     | POST /api/subjects
     |========================================================= */
    public function store(Request $r)
    {
        $actor = $this->actor($r);

        $r->validate([
            'department_id'   => ['nullable','integer','exists:departments,id'],

            'subject_code'    => ['required','string','max:50'],
            'title'           => ['required','string','max:255'],
            'short_title'     => ['nullable','string','max:120'],
            'description'     => ['nullable','string'], // HTML allowed

            // ✅ no enum / rule here (dynamic string)
            'subject_type'    => ['nullable','string','max:50'],

            'credits'         => ['nullable','integer','min:0'],
            'lecture_hours'   => ['nullable','integer','min:0'],
            'practical_hours' => ['nullable','integer','min:0'],

            'sort_order'      => ['nullable','integer','min:0'],
            'status'          => ['nullable','string','max:20'], // keep dynamic if you want, but you use active/inactive
            'publish_at'      => ['nullable','date'],
            'expire_at'       => ['nullable','date'],

            'metadata'        => ['nullable'], // array|string(json)
            'active'          => ['nullable'], // 1/0 compatibility
            'is_active'       => ['nullable'],
            'isActive'        => ['nullable'],
        ]);

        $activeFlag = $r->input('active', $r->input('is_active', $r->input('isActive')));
        $status = $this->normalizeStatusFromActiveFlag($activeFlag, $r->input('status'));

        // metadata
        $meta = $r->input('metadata', null);
        if (is_array($meta)) $meta = json_encode($meta);
        if (is_string($meta)) {
            json_decode($meta, true);
            if (json_last_error() !== JSON_ERROR_NONE) $meta = null;
        }

        // Unique guard (code unique per dept)
        $deptId = $r->filled('department_id') ? (int)$r->input('department_id') : null;
        $code   = trim((string)$r->input('subject_code'));

        $exists = DB::table('subjects')
            ->where('subject_code', $code)
            ->when($deptId === null, fn($q)=>$q->whereNull('department_id'), fn($q)=>$q->where('department_id',$deptId))
            ->whereNull('deleted_at')
            ->value('id');

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Subject code already exists for this department.',
            ], 422);
        }

        $id = DB::table('subjects')->insertGetId([
            'uuid'           => (string) Str::uuid(),
            'department_id'  => $deptId,

            'subject_code'   => $code,
            'title'          => (string)$r->input('title'),
            'short_title'    => $r->input('short_title'),
            'description'    => $r->input('description'),

            'subject_type'   => $r->input('subject_type'),

            'credits'         => $r->has('credits') ? (int)($r->input('credits') ?? 0) : 0,
            'lecture_hours'   => $r->has('lecture_hours') ? (int)($r->input('lecture_hours') ?? 0) : 0,
            'practical_hours' => $r->has('practical_hours') ? (int)($r->input('practical_hours') ?? 0) : 0,

            'sort_order'     => (int)($r->input('sort_order', 0) ?? 0),
            'status'         => $status,
            'publish_at'     => $r->filled('publish_at') ? $r->input('publish_at') : null,
            'expire_at'      => $r->filled('expire_at') ? $r->input('expire_at') : null,

            'created_by'     => $actor['id'] ?: null,
            'created_at_ip'  => $this->ip($r),
            'updated_at_ip'  => $this->ip($r),

            'metadata'       => $meta,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Created',
            'data'    => DB::table('subjects')->where('id', $id)->first(),
        ], 201);
    }

    /* =========================================================
     | UPDATE
     | PATCH /api/subjects/{id|uuid}
     |========================================================= */
    public function update(Request $r, string $idOrUuid)
    {
        $w = $this->normalizeIdentifier($idOrUuid, null);

        $exists = DB::table('subjects')->where($w['raw_col'], $w['val'])->first();
        if (!$exists) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        $r->validate([
            'department_id'   => ['nullable','integer','exists:departments,id'],

            'subject_code'    => ['sometimes','required','string','max:50'],
            'title'           => ['sometimes','required','string','max:255'],
            'short_title'     => ['nullable','string','max:120'],
            'description'     => ['nullable','string'],

            'subject_type'    => ['nullable','string','max:50'],

            'credits'         => ['nullable','integer','min:0'],
            'lecture_hours'   => ['nullable','integer','min:0'],
            'practical_hours' => ['nullable','integer','min:0'],

            'sort_order'      => ['nullable','integer','min:0'],
            'status'          => ['nullable','string','max:20'],
            'publish_at'      => ['nullable','date'],
            'expire_at'       => ['nullable','date'],

            'metadata'        => ['nullable'],
            'active'          => ['nullable'],
            'is_active'       => ['nullable'],
            'isActive'        => ['nullable'],
        ]);

        $activeFlag = $r->input('active', $r->input('is_active', $r->input('isActive')));
        $status = $this->normalizeStatusFromActiveFlag($activeFlag, $r->input('status', $exists->status ?? 'active'));

        // metadata
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

        foreach ([
            'department_id','subject_code','title','short_title','description','subject_type',
            'credits','lecture_hours','practical_hours','sort_order','publish_at','expire_at'
        ] as $k) {
            if ($r->has($k)) {
                $payload[$k] = $r->filled($k) ? $r->input($k) : null;
            }
        }

        if ($r->has('credits'))         $payload['credits'] = $r->filled('credits') ? (int)$r->input('credits') : 0;
        if ($r->has('lecture_hours'))   $payload['lecture_hours'] = $r->filled('lecture_hours') ? (int)$r->input('lecture_hours') : 0;
        if ($r->has('practical_hours')) $payload['practical_hours'] = $r->filled('practical_hours') ? (int)$r->input('practical_hours') : 0;
        if ($r->has('sort_order'))      $payload['sort_order'] = (int)($r->input('sort_order', 0) ?? 0);
        if ($r->has('metadata'))        $payload['metadata'] = $metaToStore;

        // Unique guard on update (subject_code per dept)
        if ($r->has('subject_code') || $r->has('department_id')) {
            $deptId = array_key_exists('department_id', $payload)
                ? ($payload['department_id'] !== null ? (int)$payload['department_id'] : null)
                : ($exists->department_id !== null ? (int)$exists->department_id : null);

            $code = array_key_exists('subject_code', $payload)
                ? trim((string)$payload['subject_code'])
                : (string)$exists->subject_code;

            $dup = DB::table('subjects')
                ->where('subject_code', $code)
                ->when($deptId === null, fn($q)=>$q->whereNull('department_id'), fn($q)=>$q->where('department_id',$deptId))
                ->whereNull('deleted_at')
                ->where('id', '<>', $exists->id)
                ->value('id');

            if ($dup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject code already exists for this department.',
                ], 422);
            }
        }

        DB::table('subjects')->where($w['raw_col'], $w['val'])->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Updated',
        ]);
    }

    /* =========================================================
     | DELETE (soft)
     | DELETE /api/subjects/{id|uuid}
     |========================================================= */
    public function destroy(Request $r, string $idOrUuid)
    {
        $w = $this->normalizeIdentifier($idOrUuid, null);

        $row = DB::table('subjects')->where($w['raw_col'], $w['val'])->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        if ($row->deleted_at) {
            return response()->json(['success'=>true,'message'=>'Already in trash']);
        }

        DB::table('subjects')->where('id', $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $this->ip($r),
        ]);

        return response()->json(['success'=>true,'message'=>'Moved to trash']);
    }

    /* =========================================================
     | RESTORE
     | POST /api/subjects/{id|uuid}/restore
     |========================================================= */
    public function restore(Request $r, string $idOrUuid)
    {
        $w = $this->normalizeIdentifier($idOrUuid, null);

        $row = DB::table('subjects')->where($w['raw_col'], $w['val'])->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        DB::table('subjects')->where('id', $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $this->ip($r),
        ]);

        return response()->json(['success'=>true,'message'=>'Restored']);
    }

    /* =========================================================
     | FORCE DELETE
     | DELETE /api/subjects/{id|uuid}/force
     |========================================================= */
    public function forceDelete(Request $r, string $idOrUuid)
    {
        $w = $this->normalizeIdentifier($idOrUuid, null);

        $row = DB::table('subjects')->where($w['raw_col'], $w['val'])->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        DB::table('subjects')->where('id', $row->id)->delete();

        return response()->json(['success'=>true,'message'=>'Deleted permanently']);
    }
}
