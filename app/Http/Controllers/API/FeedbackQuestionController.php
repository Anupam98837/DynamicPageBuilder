<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FeedbackQuestionController extends Controller
{
    /* =========================================================
     | Config
     |========================================================= */
    private const TABLE = 'feedback_questions';

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

    private function normalizeIdentifier(string $idOrUuid, ?string $alias = 'fq'): array
    {
        $idOrUuid = trim($idOrUuid);
        $rawCol = $this->isNumericId($idOrUuid) ? 'id' : 'uuid';
        $val    = ($rawCol === 'id') ? (int)$idOrUuid : $idOrUuid;
        $prefix = ($alias !== null && $alias !== '') ? ($alias . '.') : '';

        return [
            'col'     => $prefix . $rawCol, // fq.uuid OR fq.id
            'raw_col' => $rawCol,           // uuid OR id
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

    private function rowToArray($row): ?array
    {
        if ($row === null) return null;
        if (is_array($row)) return $row;
        if (is_object($row)) return json_decode(json_encode($row), true);
        return ['value' => $row];
    }

    private function toJsonOrNull($v): ?string
    {
        if ($v === null) return null;
        if (is_string($v)) return $v;

        try {
            return json_encode($v, JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Best-effort activity logger (never throws; never breaks main flow)
     */
    private function logActivity(
        Request $r,
        string $activity,
        string $module,
        string $tableName,
        ?int $recordId = null,
        $changedFields = null,
        $oldValues = null,
        $newValues = null,
        ?string $note = null
    ): void {
        try {
            $actor = $this->actor($r);

            DB::table('user_data_activity_log')->insert([
                'performed_by'      => (int)($actor['id'] ?? 0),
                'performed_by_role' => (string)($actor['role'] ?? '') ?: null,
                'ip'                => $this->ip($r),
                'user_agent'        => $r->userAgent() ? (string)$r->userAgent() : null,

                'activity'   => $activity,
                'module'     => $module,

                'table_name' => $tableName,
                'record_id'  => $recordId,

                'changed_fields' => $this->toJsonOrNull($changedFields),
                'old_values'     => $this->toJsonOrNull($oldValues),
                'new_values'     => $this->toJsonOrNull($newValues),

                'log_note'   => $note,

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // swallow (logging should never affect business logic)
        }
    }

    protected function baseQuery(bool $includeDeleted = false)
    {
        $q = DB::table(self::TABLE . ' as fq')
            ->select([
                'fq.id',
                'fq.uuid',
                'fq.group_title',
                'fq.title',
                'fq.hint',
                'fq.description',
                'fq.sort_order',
                'fq.status',
                'fq.publish_at',
                'fq.expire_at',
                'fq.metadata',
                'fq.created_by',
                'fq.created_at',
                'fq.updated_at',
                'fq.created_at_ip',
                'fq.updated_at_ip',
                'fq.deleted_at',
            ]);

        if (!$includeDeleted) $q->whereNull('fq.deleted_at');
        return $q;
    }

    private function respondList(Request $r, $q)
    {
        $page = max(1, (int)$r->query('page', 1));
        $per  = min(100, max(5, (int)$r->query('per_page', 20)));

        $total = (clone $q)->count('fq.id');
        $rows  = $q->forPage($page, $per)->get();

        $data = $rows->map(function ($x) {
            $meta = $this->normalizeMetadata($x->metadata ?? null);
            $status = (string)($x->status ?? 'active');

            return [
                'id'          => (int)$x->id,
                'uuid'        => (string)$x->uuid,
                'group_title' => (string)($x->group_title ?? ''),
                'title'       => (string)($x->title ?? ''),
                'hint'        => $x->hint !== null ? (string)$x->hint : null,
                'description' => $x->description, // HTML allowed
                'sort_order'  => (int)($x->sort_order ?? 0),
                'status'      => $status,
                'is_active'   => $status === 'active',
                'publish_at'  => $x->publish_at,
                'expire_at'   => $x->expire_at,
                'metadata'    => $meta,

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
     | GET /api/feedback-questions
     |========================================================= */
    public function index(Request $r)
    {
        $qText  = trim((string)$r->query('q', ''));
        $status = trim((string)$r->query('status', '')); // active|inactive
        $group  = trim((string)$r->query('group_title', ''));

        $sort = (string)$r->query('sort', 'updated_at');
        $dir  = strtolower((string)$r->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSort = ['created_at','updated_at','group_title','title','sort_order','publish_at','status'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'updated_at';

        $q = $this->baseQuery(false);

        if ($qText !== '') {
            $q->where(function ($w) use ($qText) {
                $w->where('fq.title', 'like', "%{$qText}%")
                  ->orWhere('fq.group_title', 'like', "%{$qText}%")
                  ->orWhere('fq.hint', 'like', "%{$qText}%")
                  ->orWhere('fq.uuid', 'like', "%{$qText}%");
            });
        }

        if ($group !== '')  $q->where('fq.group_title', 'like', "%{$group}%");
        if ($status !== '') $q->where('fq.status', $status);

        // compatibility: ?active=1 / ?active=0
        if ($r->has('active')) {
            $av = (string)$r->query('active');
            if (in_array($av, ['1','true','yes'], true)) $q->where('fq.status', 'active');
            if (in_array($av, ['0','false','no'], true)) $q->where('fq.status', 'inactive');
        }

        $q->orderBy("fq.$sort", $dir)->orderBy('fq.id', 'desc');

        return $this->respondList($r, $q);
    }

    /* =========================================================
     | TRASH
     | GET /api/feedback-questions/trash
     |========================================================= */
    public function trash(Request $r)
    {
        $qText = trim((string)$r->query('q', ''));
        $sort  = (string)$r->query('sort', 'deleted_at');
        $dir   = strtolower((string)$r->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSort = ['deleted_at','updated_at','title','group_title','created_at'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'deleted_at';

        $q = $this->baseQuery(true)->whereNotNull('fq.deleted_at');

        if ($qText !== '') {
            $q->where(function ($w) use ($qText) {
                $w->where('fq.title', 'like', "%{$qText}%")
                  ->orWhere('fq.group_title', 'like', "%{$qText}%")
                  ->orWhere('fq.uuid', 'like', "%{$qText}%");
            });
        }

        $q->orderBy("fq.$sort", $dir)->orderBy('fq.id', 'desc');

        return $this->respondList($r, $q);
    }

    /* =========================================================
     | CURRENT (frontend-friendly)
     | GET /api/feedback-questions/current
     |========================================================= */
    public function current(Request $r)
    {
        $group = trim((string)$r->query('group_title', ''));

        $q = $this->baseQuery(false)
            ->where('fq.status', 'active')
            ->where(function ($w) {
                $w->whereNull('fq.publish_at')->orWhere('fq.publish_at', '<=', now());
            })
            ->where(function ($w) {
                $w->whereNull('fq.expire_at')->orWhere('fq.expire_at', '>=', now());
            })
            ->orderBy('fq.group_title', 'asc')
            ->orderBy('fq.sort_order', 'asc')
            ->orderBy('fq.id', 'asc');

        if ($group !== '') $q->where('fq.group_title', 'like', "%{$group}%");

        return $this->respondList($r, $q);
    }

    /* =========================================================
     | GROUP TITLES ONLY
     | GET /api/feedback-questions/group-titles
     |========================================================= */
    public function groupTitles(Request $r)
    {
        // returns only distinct, trimmed, non-empty group_title values
        $titles = DB::table(self::TABLE . ' as fq')
            ->whereNull('fq.deleted_at')
            ->whereNotNull('fq.group_title')
            ->whereRaw("TRIM(fq.group_title) <> ''")
            ->selectRaw("DISTINCT TRIM(fq.group_title) as group_title")
            ->orderBy('group_title', 'asc')
            ->pluck('group_title')
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $titles,
        ]);
    }

    /* =========================================================
     | SHOW
     | GET /api/feedback-questions/{id|uuid}
     |========================================================= */
    public function show(string $idOrUuid)
    {
        $w = $this->normalizeIdentifier($idOrUuid, 'fq');

        $row = $this->baseQuery(true)->where($w['col'], $w['val'])->first();
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);

        $fakeReq = request();
        $q = $this->baseQuery(true)->where($w['col'], $w['val']);
        return $this->respondList($fakeReq, $q);
    }

    /* =========================================================
     | CREATE
     | POST /api/feedback-questions
     |========================================================= */
    public function store(Request $r)
    {
        $actor = $this->actor($r);

        $r->validate([
            'group_title'  => ['required','string','max:255'],
            'title'        => ['required','string','max:255'],
            'hint'         => ['nullable','string','max:255'],
            'description'  => ['nullable','string'], // HTML allowed

            'sort_order'   => ['nullable','integer','min:0'],
            'status'       => ['nullable','string','max:20', Rule::in(['active','inactive'])],
            'publish_at'   => ['nullable','date'],
            'expire_at'    => ['nullable','date'],

            'metadata'     => ['nullable'], // array|string(json)
            'active'       => ['nullable'], // 1/0 compatibility
            'is_active'    => ['nullable'],
            'isActive'     => ['nullable'],
        ]);

        $activeFlag = $r->input('active', $r->input('is_active', $r->input('isActive')));
        $status = $this->normalizeStatusFromActiveFlag($activeFlag, $r->input('status'));

        $meta = $r->input('metadata', null);
        if (is_array($meta)) $meta = json_encode($meta);
        if (is_string($meta)) {
            json_decode($meta, true);
            if (json_last_error() !== JSON_ERROR_NONE) $meta = null;
        }

        try {
            $id = DB::table(self::TABLE)->insertGetId([
                'uuid'        => (string) Str::uuid(),

                'group_title' => (string) $r->input('group_title'),
                'title'       => (string) $r->input('title'),
                'hint'        => $r->filled('hint') ? (string)$r->input('hint') : null,
                'description' => $r->input('description'),

                'sort_order'  => (int)($r->input('sort_order', 0) ?? 0),
                'status'      => $status,
                'publish_at'  => $r->filled('publish_at') ? $r->input('publish_at') : null,
                'expire_at'   => $r->filled('expire_at') ? $r->input('expire_at') : null,

                'created_by'    => $actor['id'] ?: null,
                'created_at_ip' => $this->ip($r),
                'updated_at_ip' => $this->ip($r),

                'metadata'   => $meta,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]);

            $newRow = DB::table(self::TABLE)->where('id', $id)->first();

            $this->logActivity(
                $r,
                'create',
                'feedback_questions',
                self::TABLE,
                (int)$id,
                array_keys($this->rowToArray($newRow) ?? []),
                null,
                $this->rowToArray($newRow),
                'Created feedback question'
            );

            return response()->json([
                'success' => true,
                'message' => 'Created',
                'data'    => $newRow,
            ], 201);
        } catch (\Throwable $e) {
            $this->logActivity(
                $r,
                'create',
                'feedback_questions',
                self::TABLE,
                null,
                null,
                null,
                null,
                'Create failed: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    /* =========================================================
     | UPDATE
     | PATCH /api/feedback-questions/{id|uuid}
     |========================================================= */
    public function update(Request $r, string $idOrUuid)
    {
        $w = $this->normalizeIdentifier($idOrUuid, null);

        $exists = DB::table(self::TABLE)->where($w['raw_col'], $w['val'])->first();
        if (!$exists) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        $r->validate([
            'group_title'  => ['sometimes','required','string','max:255'],
            'title'        => ['sometimes','required','string','max:255'],
            'hint'         => ['nullable','string','max:255'],
            'description'  => ['nullable','string'],

            'sort_order'   => ['nullable','integer','min:0'],
            'status'       => ['nullable','string','max:20', Rule::in(['active','inactive'])],
            'publish_at'   => ['nullable','date'],
            'expire_at'    => ['nullable','date'],

            'metadata'     => ['nullable'],
            'active'       => ['nullable'],
            'is_active'    => ['nullable'],
            'isActive'     => ['nullable'],
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

        foreach (['group_title','title','hint','description','publish_at','expire_at'] as $k) {
            if ($r->has($k)) {
                $payload[$k] = $r->filled($k) ? $r->input($k) : null;
            }
        }

        if ($r->has('sort_order')) $payload['sort_order'] = (int)($r->input('sort_order', 0) ?? 0);
        if ($r->has('metadata'))   $payload['metadata']   = $metaToStore;

        try {
            $oldRow = DB::table(self::TABLE)->where($w['raw_col'], $w['val'])->first();

            DB::table(self::TABLE)->where($w['raw_col'], $w['val'])->update($payload);

            $newRow = DB::table(self::TABLE)->where('id', (int)$exists->id)->first();

            $oldArr = $this->rowToArray($oldRow) ?? [];
            $newArr = $this->rowToArray($newRow) ?? [];

            $changed = [];
            foreach ($payload as $k => $v) {
                // avoid noisy auto fields in changed_fields (still preserved in row snapshots if needed)
                if (in_array($k, ['updated_at','updated_at_ip'], true)) continue;

                $oldVal = $oldArr[$k] ?? null;
                $newVal = $newArr[$k] ?? $v;

                if ((string)$oldVal !== (string)$newVal) $changed[] = $k;
            }

            $oldSnap = [];
            $newSnap = [];
            foreach ($changed as $k) {
                $oldSnap[$k] = $oldArr[$k] ?? null;
                $newSnap[$k] = $newArr[$k] ?? null;
            }

            $this->logActivity(
                $r,
                'update',
                'feedback_questions',
                self::TABLE,
                (int)$exists->id,
                $changed,
                $oldSnap ?: null,
                $newSnap ?: null,
                'Updated feedback question'
            );

            return response()->json(['success' => true, 'message' => 'Updated']);
        } catch (\Throwable $e) {
            $this->logActivity(
                $r,
                'update',
                'feedback_questions',
                self::TABLE,
                (int)$exists->id,
                null,
                null,
                null,
                'Update failed: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    /* =========================================================
     | DELETE (soft)
     | DELETE /api/feedback-questions/{id|uuid}
     |========================================================= */
    public function destroy(Request $r, string $idOrUuid)
    {
        $w = $this->normalizeIdentifier($idOrUuid, null);

        $row = DB::table(self::TABLE)->where($w['raw_col'], $w['val'])->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        if ($row->deleted_at) return response()->json(['success'=>true,'message'=>'Already in trash']);

        try {
            $oldRow = $row;

            DB::table(self::TABLE)->where('id', $row->id)->update([
                'deleted_at'    => now(),
                'updated_at'    => now(),
                'updated_at_ip' => $this->ip($r),
            ]);

            $newRow = DB::table(self::TABLE)->where('id', $row->id)->first();

            $oldArr = $this->rowToArray($oldRow) ?? [];
            $newArr = $this->rowToArray($newRow) ?? [];

            $changed = ['deleted_at'];
            $oldSnap = ['deleted_at' => $oldArr['deleted_at'] ?? null];
            $newSnap = ['deleted_at' => $newArr['deleted_at'] ?? null];

            $this->logActivity(
                $r,
                'delete',
                'feedback_questions',
                self::TABLE,
                (int)$row->id,
                $changed,
                $oldSnap,
                $newSnap,
                'Moved feedback question to trash'
            );

            return response()->json(['success'=>true,'message'=>'Moved to trash']);
        } catch (\Throwable $e) {
            $this->logActivity(
                $r,
                'delete',
                'feedback_questions',
                self::TABLE,
                (int)$row->id,
                null,
                null,
                null,
                'Soft delete failed: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    /* =========================================================
     | RESTORE
     | POST /api/feedback-questions/{id|uuid}/restore
     |========================================================= */
    public function restore(Request $r, string $idOrUuid)
    {
        $w = $this->normalizeIdentifier($idOrUuid, null);

        $row = DB::table(self::TABLE)->where($w['raw_col'], $w['val'])->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        try {
            $oldRow = $row;

            DB::table(self::TABLE)->where('id', $row->id)->update([
                'deleted_at'    => null,
                'updated_at'    => now(),
                'updated_at_ip' => $this->ip($r),
            ]);

            $newRow = DB::table(self::TABLE)->where('id', $row->id)->first();

            $oldArr = $this->rowToArray($oldRow) ?? [];
            $newArr = $this->rowToArray($newRow) ?? [];

            $changed = ['deleted_at'];
            $oldSnap = ['deleted_at' => $oldArr['deleted_at'] ?? null];
            $newSnap = ['deleted_at' => $newArr['deleted_at'] ?? null];

            $this->logActivity(
                $r,
                'restore',
                'feedback_questions',
                self::TABLE,
                (int)$row->id,
                $changed,
                $oldSnap,
                $newSnap,
                'Restored feedback question from trash'
            );

            return response()->json(['success'=>true,'message'=>'Restored']);
        } catch (\Throwable $e) {
            $this->logActivity(
                $r,
                'restore',
                'feedback_questions',
                self::TABLE,
                (int)$row->id,
                null,
                null,
                null,
                'Restore failed: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    /* =========================================================
     | FORCE DELETE
     | DELETE /api/feedback-questions/{id|uuid}/force
     |========================================================= */
    public function forceDelete(Request $r, string $idOrUuid)
    {
        $w = $this->normalizeIdentifier($idOrUuid, null);

        $row = DB::table(self::TABLE)->where($w['raw_col'], $w['val'])->first();
        if (!$row) return response()->json(['success'=>false,'message'=>'Not found'], 404);

        try {
            $oldRow = $row;

            DB::table(self::TABLE)->where('id', $row->id)->delete();

            $this->logActivity(
                $r,
                'force_delete',
                'feedback_questions',
                self::TABLE,
                (int)$row->id,
                ['__force_deleted'],
                $this->rowToArray($oldRow),
                null,
                'Deleted feedback question permanently'
            );

            return response()->json(['success'=>true,'message'=>'Deleted permanently']);
        } catch (\Throwable $e) {
            $this->logActivity(
                $r,
                'force_delete',
                'feedback_questions',
                self::TABLE,
                (int)$row->id,
                null,
                null,
                null,
                'Force delete failed: ' . $e->getMessage()
            );
            throw $e;
        }
    }
}
