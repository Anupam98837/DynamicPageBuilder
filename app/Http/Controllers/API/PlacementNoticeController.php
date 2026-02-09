<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PlacementNoticeController extends Controller
{
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
        ];
    }

    /**
     * Safe activity logger (never breaks main flow).
     */
    protected function canLogActivity(): bool
    {
        return Schema::hasTable('user_data_activity_log');
    }

    protected function sanitizeForLog($value, int $depth = 0)
    {
        if ($depth > 5) return '[max_depth]';

        if ($value === null) return null;

        if ($value instanceof \DateTimeInterface) {
            return $value->format('c');
        }

        if (is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            // prevent huge logs
            return mb_strlen($value) > 5000 ? (mb_substr($value, 0, 5000) . '...[truncated]') : $value;
        }

        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = $this->sanitizeForLog($v, $depth + 1);
            }
            return $out;
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return $this->sanitizeForLog((string) $value, $depth + 1);
            }
            // try best-effort convert object -> array
            try {
                $arr = json_decode(json_encode($value), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $this->sanitizeForLog($arr, $depth + 1);
                }
            } catch (\Throwable $e) {
                // ignore
            }
            return '[object]';
        }

        return '[unknown]';
    }

    protected function tryJsonDecode($value)
    {
        if (!is_string($value)) return $value;
        $s = trim($value);
        if ($s === '') return $value;

        // quick guard (json usually starts with { or [ or quote/number/bool/null)
        $first = $s[0] ?? '';
        if (!in_array($first, ['{', '[', '"', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 't', 'f', 'n', '-'], true)) {
            return $value;
        }

        $decoded = json_decode($s, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $value;
    }

    protected function logActivity(
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
            $now = now();

            // sanitize & json-decode some common json strings for readability
            $oldValues = $oldValues ? $this->sanitizeForLog($oldValues) : null;
            $newValues = $newValues ? $this->sanitizeForLog($newValues) : null;

            if (is_array($oldValues)) {
                foreach ($oldValues as $k => $v) $oldValues[$k] = $this->tryJsonDecode($v);
            }
            if (is_array($newValues)) {
                foreach ($newValues as $k => $v) $newValues[$k] = $this->tryJsonDecode($v);
            }

            $payload = [
                'performed_by'       => (int) ($actor['id'] ?: 0),
                'performed_by_role'  => ($actor['role'] !== '') ? $actor['role'] : null,
                'ip'                 => $request->ip(),
                'user_agent'         => mb_substr((string) $request->userAgent(), 0, 512),

                'activity'           => mb_substr($activity, 0, 50),
                'module'             => mb_substr($module, 0, 100),

                'table_name'         => mb_substr($tableName, 0, 128),
                'record_id'          => $recordId,

                'changed_fields'     => $changedFields ? json_encode(array_values(array_unique($changedFields))) : null,
                'old_values'         => $oldValues ? json_encode($oldValues) : null,
                'new_values'         => $newValues ? json_encode($newValues) : null,

                'log_note'           => $note,

                'created_at'         => $now,
                'updated_at'         => $now,
            ];

            DB::table('user_data_activity_log')->insert($payload);
        } catch (\Throwable $e) {
            // never break main request flow
        }
    }

    protected function diffForLog($beforeRow, array $update): array
    {
        // $beforeRow is stdClass from DB
        $beforeArr = $beforeRow ? (array) $beforeRow : [];

        $changed = [];
        $old = [];
        $new = [];

        foreach ($update as $k => $v) {
            // ignore technical fields unless they’re the only change
            if (in_array($k, ['updated_at', 'updated_at_ip'], true)) continue;

            $oldVal = $beforeArr[$k] ?? null;
            $newVal = $v;

            // normalize DateTime to string for comparison
            if ($oldVal instanceof \DateTimeInterface) $oldVal = $oldVal->format('c');
            if ($newVal instanceof \DateTimeInterface) $newVal = $newVal->format('c');

            // compare as string for some cases
            $isDiff = true;
            if (is_null($oldVal) && is_null($newVal)) $isDiff = false;
            elseif (is_scalar($oldVal) && is_scalar($newVal)) $isDiff = ((string) $oldVal !== (string) $newVal);
            else $isDiff = ($oldVal != $newVal);

            if ($isDiff) {
                $changed[] = $k;
                $old[$k] = $oldVal;
                $new[$k] = $newVal;
            }
        }

        return [$changed, $old, $new];
    }

    private function normSlug(?string $s): string
    {
        $s = trim((string) $s);
        return $s === '' ? '' : Str::slug($s, '-');
    }

    protected function resolveDepartment($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('departments');
        if (Schema::hasColumn('departments', 'deleted_at') && ! $includeDeleted) $q->whereNull('deleted_at');

        if (ctype_digit((string) $identifier)) {
            $q->where('id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier) && Schema::hasColumn('departments', 'uuid')) {
            $q->where('uuid', (string) $identifier);
        } elseif (Schema::hasColumn('departments', 'slug')) {
            $q->where('slug', (string) $identifier);
        } else {
            // fallback: try title/name
            if (Schema::hasColumn('departments', 'title')) $q->where('title', (string) $identifier);
            elseif (Schema::hasColumn('departments', 'name')) $q->where('name', (string) $identifier);
        }

        return $q->first();
    }

    protected function resolveRecruiter($identifier, bool $includeDeleted = false)
    {
        if (!Schema::hasTable('recruiters')) return null;

        $q = DB::table('recruiters');
        if (Schema::hasColumn('recruiters', 'deleted_at') && ! $includeDeleted) {
            $q->whereNull('deleted_at');
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier) && Schema::hasColumn('recruiters', 'uuid')) {
            $q->where('uuid', (string) $identifier);
        } elseif (Schema::hasColumn('recruiters', 'slug')) {
            $q->where('slug', (string) $identifier);
        } else {
            if (Schema::hasColumn('recruiters', 'name')) $q->where('name', (string) $identifier);
            else return null;
        }

        return $q->first();
    }

    protected function recruiterSelect(): array
    {
        if (!Schema::hasTable('recruiters')) return [];

        $cols = ['id'];
        foreach (['name','title','company_name','slug','uuid'] as $c) {
            if (Schema::hasColumn('recruiters', $c)) $cols[] = $c;
        }

        $out = [];
        foreach ($cols as $c) $out[] = "r.$c as recruiter_$c";
        return $out;
    }

    /**
     * Normalize department_ids input:
     * - Accepts array, JSON string, comma-separated string, single numeric
     * - Returns unique int[] or null
     */
    protected function normalizeDepartmentIds($input): ?array
    {
        if ($input === null || $input === '') return null;

        // If it's already an array
        if (is_array($input)) {
            $arr = $input;
        } elseif (is_string($input)) {
            $s = trim($input);

            // JSON?
            $decoded = json_decode($s, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $arr = $decoded;
            } else {
                // comma-separated
                $arr = array_map('trim', explode(',', $s));
            }
        } else {
            // single value
            $arr = [$input];
        }

        $ids = [];
        foreach ($arr as $v) {
            if ($v === null || $v === '') continue;
            if (is_numeric($v)) $ids[] = (int) $v;
        }

        $ids = array_values(array_unique(array_filter($ids, fn($x) => $x > 0)));
        return empty($ids) ? null : $ids;
    }

    /**
     * Departments list for frontend selection (id + title + optional slug/uuid)
     */
    protected function departmentsList(bool $includeDeleted = false): array
    {
        if (!Schema::hasTable('departments')) return [];

        $q = DB::table('departments');

        if (Schema::hasColumn('departments', 'deleted_at') && ! $includeDeleted) {
            $q->whereNull('deleted_at');
        }

        $select = ['id'];

        if (Schema::hasColumn('departments', 'title')) {
            $select[] = 'title';
        } elseif (Schema::hasColumn('departments', 'name')) {
            $select[] = DB::raw('name as title');
        } else {
            $select[] = DB::raw("CONCAT('Department ', id) as title");
        }

        if (Schema::hasColumn('departments', 'slug')) $select[] = 'slug';
        if (Schema::hasColumn('departments', 'uuid')) $select[] = 'uuid';

        $rows = $q->select($select)->orderBy('title')->get();

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id'    => (int) $r->id,
                'title' => (string) ($r->title ?? ''),
                'slug'  => isset($r->slug) ? (string) $r->slug : null,
                'uuid'  => isset($r->uuid) ? (string) $r->uuid : null,
            ];
        }

        return $out;
    }

    /**
     * Build a map: id => department info
     */
    protected function departmentsMap(bool $includeDeleted = false): array
    {
        $list = $this->departmentsList($includeDeleted);
        $map = [];
        foreach ($list as $d) $map[(int)$d['id']] = $d;
        return $map;
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('placement_notices as p');

        // recruiter join only if exists
        if (Schema::hasTable('recruiters')) {
            $q->leftJoin('recruiters as r', 'r.id', '=', 'p.recruiter_id');
        }

        $select = array_merge([
            'p.*',
        ], $this->recruiterSelect());

        $q->select($select);

        if (! $includeDeleted) {
            $q->whereNull('p.deleted_at');
        }

        // ?q=
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('p.title', 'like', $term)
                    ->orWhere('p.slug', 'like', $term)
                    ->orWhere('p.description', 'like', $term)
                    ->orWhere('p.eligibility', 'like', $term)
                    ->orWhere('p.role_title', 'like', $term);
            });
        }

        // ?status=draft|published|archived
        if ($request->filled('status')) {
            $q->where('p.status', (string) $request->query('status'));
        }

        // ?featured=1/0
        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) {
                $q->where('p.is_featured_home', $featured ? 1 : 0);
            }
        }

        // ✅ ?department=id|uuid|slug  (filters by JSON department_ids)
        if ($request->filled('department')) {
            $dept = $this->resolveDepartment($request->query('department'), true);
            if ($dept) $q->whereJsonContains('p.department_ids', (int) $dept->id);
            else $q->whereRaw('1=0');
        }

        // ?recruiter=id|uuid|slug
        if ($request->filled('recruiter')) {
            $rec = $this->resolveRecruiter($request->query('recruiter'), true);
            if ($rec) $q->where('p.recruiter_id', (int) $rec->id);
            else $q->whereRaw('1=0');
        }

        // ?visible_now=1 -> only published & publish/expire window
        if ($request->has('visible_now')) {
            $visible = filter_var($request->query('visible_now'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($visible) {
                $now = now();
                $q->where('p.status', 'published')
                  ->where(function ($w) use ($now) {
                      $w->whereNull('p.publish_at')->orWhere('p.publish_at', '<=', $now);
                  })
                  ->where(function ($w) use ($now) {
                      $w->whereNull('p.expire_at')->orWhere('p.expire_at', '>', $now);
                  });
            }
        }

        // sort
        $sort = (string) $request->query('sort', 'created_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['created_at','publish_at','expire_at','last_date_to_apply','title','views_count','sort_order','id'];
        if (!in_array($sort, $allowed, true)) $sort = 'created_at';

        $q->orderBy('p.' . $sort, $dir);

        return $q;
    }

    protected function resolvePlacementNotice(Request $request, $identifier, bool $includeDeleted = false, $departmentId = null)
    {
        $q = DB::table('placement_notices as p');
        if (! $includeDeleted) $q->whereNull('p.deleted_at');

        // ✅ only notices that contain this department id in JSON
        if ($departmentId !== null) {
            $q->whereJsonContains('p.department_ids', (int) $departmentId);
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('p.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('p.uuid', (string) $identifier);
        } else {
            $q->where('p.slug', (string) $identifier);
        }

        $row = $q->first();
        if (! $row) return null;

        // attach recruiter (best-effort)
        if (!empty($row->recruiter_id) && Schema::hasTable('recruiters')) {
            $rec = DB::table('recruiters')->where('id', (int) $row->recruiter_id)->first();
            if ($rec) {
                foreach (['id','name','title','company_name','slug','uuid'] as $k) {
                    if (isset($rec->$k)) $row->{'recruiter_'.$k} = $rec->$k;
                }
            }
        }

        return $row;
    }

    protected function toUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') return null;
        if (preg_match('~^https?://~i', $path)) return $path;
        return url('/' . ltrim($path, '/'));
    }

    protected function normalizeRow($row, ?array $deptMap = null): array
    {
        $arr = (array) $row;

        // decode metadata
        $meta = $arr['metadata'] ?? null;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // decode department_ids (json column)
        $deptIds = $arr['department_ids'] ?? null;
        if (is_string($deptIds)) {
            $decoded = json_decode($deptIds, true);
            $deptIds = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }
        if (is_array($deptIds)) {
            $deptIds = array_values(array_unique(array_map('intval', $deptIds)));
            $deptIds = array_values(array_filter($deptIds, fn($x) => $x > 0));
        } else {
            $deptIds = null;
        }
        $arr['department_ids'] = $deptIds;

        // attach selected departments details for UI
        $deptMap = $deptMap ?? $this->departmentsMap(false);
        $selected = [];
        if (is_array($deptIds)) {
            foreach ($deptIds as $id) {
                if (isset($deptMap[$id])) $selected[] = $deptMap[$id];
                else $selected[] = ['id' => (int)$id, 'title' => null, 'slug' => null, 'uuid' => null];
            }
        }
        $arr['departments'] = $selected;

        // banner url normalize
        $arr['banner_image_full_url'] = $this->toUrl($arr['banner_image_url'] ?? null);

        return $arr;
    }

    protected function ensureUniqueSlug(string $slug, ?string $ignoreUuid = null): string
    {
        $base = $slug;
        $i = 2;

        while (
            DB::table('placement_notices')
                ->where('slug', $slug)
                ->when($ignoreUuid, function ($q) use ($ignoreUuid) {
                    $q->where('uuid', '!=', $ignoreUuid);
                })
                ->whereNull('deleted_at')
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    protected function uploadFileToPublic($file, string $dirRel, string $prefix): array
    {
        $originalName = $file->getClientOriginalName();
        $mimeType     = $file->getClientMimeType() ?: $file->getMimeType();
        $fileSize     = (int) $file->getSize();
        $ext          = strtolower($file->getClientOriginalExtension() ?: 'bin');

        $dirRel = trim($dirRel, '/');
        $dirAbs = public_path($dirRel);
        if (!is_dir($dirAbs)) @mkdir($dirAbs, 0775, true);

        $filename = $prefix . '-' . Str::random(8) . '.' . $ext;
        $file->move($dirAbs, $filename);

        return [
            'path' => $dirRel . '/' . $filename,
            'name' => $originalName,
            'mime' => $mimeType,
            'size' => $fileSize,
        ];
    }

    protected function deletePublicPath(?string $path): void
    {
        $path = trim((string) $path);
        if ($path === '' || preg_match('~^https?://~i', $path)) return;

        $abs = public_path(ltrim($path, '/'));
        if (is_file($abs)) @unlink($abs);
    }

    protected function applyVisibleWindow($q): void
    {
        $now = now();

        $q->whereNull('p.deleted_at')
          ->where('p.status', 'published')
          ->where(function ($w) use ($now) {
              $w->whereNull('p.publish_at')->orWhere('p.publish_at', '<=', $now);
          })
          ->where(function ($w) use ($now) {
              $w->whereNull('p.expire_at')->orWhere('p.expire_at', '>', $now);
          });
    }

    /* ============================================
     | CRUD (Admin)
     |============================================ */

    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $deptMap  = $this->departmentsMap(false);
        $deptList = array_values($deptMap);

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);
        if ($onlyDeleted) $query->whereNotNull('p.deleted_at');

        $paginator = $query->paginate($perPage);
        $items = array_map(fn($r) => $this->normalizeRow($r, $deptMap), $paginator->items());

        return response()->json([
            'data' => $items,
            // ✅ for frontend dropdown selection
            'lookups' => [
                'departments' => $deptList,
            ],
            'pagination' => [
                'page'      => $paginator->currentPage(),
                'per_page'  => $paginator->perPage(),
                'total'     => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function indexByDepartment(Request $request, $department)
    {
        $dept = $this->resolveDepartment($department, false);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        $request->query->set('department', $dept->id);
        return $this->index($request);
    }

    public function trash(Request $request)
    {
        $request->query->set('only_trashed', '1');
        return $this->index($request);
    }

    public function show(Request $request, $identifier)
    {
        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $deptMap  = $this->departmentsMap(false);
        $deptList = array_values($deptMap);

        $row = $this->resolvePlacementNotice($request, $identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Placement notice not found'], 404);

        // optional: ?inc_view=1  (GET — not logging as requested)
        if (filter_var($request->query('inc_view', false), FILTER_VALIDATE_BOOLEAN)) {
            DB::table('placement_notices')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row, $deptMap),
            // ✅ for frontend dropdown selection
            'lookups' => [
                'departments' => $deptList,
            ],
        ]);
    }

    public function showByDepartment(Request $request, $department, $identifier)
    {
        $dept = $this->resolveDepartment($department, true);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $deptMap  = $this->departmentsMap(false);
        $deptList = array_values($deptMap);

        $row = $this->resolvePlacementNotice($request, $identifier, $includeDeleted, $dept->id);
        if (! $row) return response()->json(['message' => 'Placement notice not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row, $deptMap),
            'lookups' => [
                'departments' => $deptList,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->actor($request);

        // ✅ normalize department_ids so validation works even if frontend sends JSON string
        $normalizedDeptIds = $this->normalizeDepartmentIds($request->input('department_ids', null));
        if ($normalizedDeptIds !== null) $request->merge(['department_ids' => $normalizedDeptIds]);

        $validated = $request->validate([
            'department_ids'      => ['nullable', 'array'],
            'department_ids.*'    => ['integer', 'exists:departments,id'],

            'recruiter_id'        => ['nullable', 'integer', 'exists:recruiters,id'],

            'title'               => ['required', 'string', 'max:255'],
            'slug'                => ['nullable', 'string', 'max:160'],

            'description'         => ['nullable', 'string'],
            'role_title'          => ['nullable', 'string', 'max:255'],
            'ctc'                 => ['nullable', 'numeric'],
            'eligibility'         => ['nullable', 'string'],
            'apply_url'           => ['nullable', 'string', 'max:255'],
            'last_date_to_apply'  => ['nullable', 'date'],

            'is_featured_home'    => ['nullable', 'in:0,1', 'boolean'],
            'sort_order'          => ['nullable', 'integer', 'min:0'],
            'status'              => ['nullable', 'in:draft,published,archived'],
            'publish_at'          => ['nullable', 'date'],
            'expire_at'           => ['nullable', 'date'],
            'metadata'            => ['nullable'],

            'banner_image'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'banner_image_url'    => ['nullable', 'string', 'max:255'],
        ]);

        $slug = $this->normSlug($validated['slug'] ?? '');
        if ($slug === '') $slug = Str::slug($validated['title'], '-');
        $slug = $this->ensureUniqueSlug($slug);

        $uuid = (string) Str::uuid();
        $now  = now();

        // metadata normalize
        $metadata = $request->input('metadata', null);
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
        }

        // banner upload OR manual banner_image_url
        $bannerPath = null;

        if ($request->hasFile('banner_image')) {
            // ✅ folder key: first department id OR global
            $deptKey = (!empty($validated['department_ids']) && is_array($validated['department_ids']))
                ? (string) ((int) $validated['department_ids'][0])
                : 'global';

            $dirRel  = 'depy_uploads/placement_notices/' . $deptKey;

            $f = $request->file('banner_image');
            if (!$f || !$f->isValid()) {
                return response()->json(['success' => false, 'message' => 'Banner image upload failed'], 422);
            }

            $meta = $this->uploadFileToPublic($f, $dirRel, $slug . '-banner');
            $bannerPath = $meta['path'];
        } elseif (!empty($validated['banner_image_url'])) {
            $bannerPath = (string) $validated['banner_image_url'];
        }

        $deptIds = $validated['department_ids'] ?? null;
        $deptIds = $this->normalizeDepartmentIds($deptIds);

        // ✅ Authority control auto-sync:
        // if is_featured_home = 1 => request_for_approval = 1
        // if is_featured_home = 0 => request_for_approval = 0
        $featuredFlag = (int) ($validated['is_featured_home'] ?? 0);

        $insert = [
            'uuid'              => $uuid,
            'department_ids'    => $deptIds !== null ? json_encode($deptIds) : null,
            'recruiter_id'      => $validated['recruiter_id'] ?? null,
            'slug'              => $slug,
            'title'             => $validated['title'],

            'description'       => $validated['description'] ?? null,
            'banner_image_url'  => $bannerPath,
            'role_title'        => $validated['role_title'] ?? null,
            'ctc'               => array_key_exists('ctc', $validated) ? $validated['ctc'] : null,
            'eligibility'       => $validated['eligibility'] ?? null,
            'apply_url'         => $validated['apply_url'] ?? null,
            'last_date_to_apply'=> !empty($validated['last_date_to_apply']) ? Carbon::parse($validated['last_date_to_apply'])->toDateString() : null,

            'is_featured_home'  => $featuredFlag,
            'sort_order'        => (int) ($validated['sort_order'] ?? 0),
            'status'            => (string) ($validated['status'] ?? 'draft'),
            'publish_at'        => !empty($validated['publish_at']) ? Carbon::parse($validated['publish_at']) : null,
            'expire_at'         => !empty($validated['expire_at']) ? Carbon::parse($validated['expire_at']) : null,

            'views_count'       => 0,
            'created_by'        => $actor['id'] ?: null,

            'created_at'        => $now,
            'updated_at'        => $now,
            'created_at_ip'     => $request->ip(),
            'updated_at_ip'     => $request->ip(),

            'metadata'          => $metadata !== null ? json_encode($metadata) : null,
        ];

        // ✅ only set if migration column exists (safe)
        if (Schema::hasColumn('placement_notices', 'request_for_approval')) {
            $insert['request_for_approval'] = $featuredFlag ? 1 : 0;
        }

        $id = DB::table('placement_notices')->insertGetId($insert);

        $fresh = DB::table('placement_notices')->where('id', $id)->first();

        // ✅ LOG (POST)
        $this->logActivity(
            $request,
            'create',
            'placement_notices',
            'placement_notices',
            (int) $id,
            array_keys($insert),
            null,
            array_merge(['id' => (int) $id], $insert),
            'Placement notice created'
        );

        $deptMap = $this->departmentsMap(false);

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh, $deptMap) : null,
            // ✅ for frontend dropdown selection
            'lookups' => [
                'departments' => array_values($deptMap),
            ],
        ]);
    }

    public function storeForDepartment(Request $request, $department)
    {
        $dept = $this->resolveDepartment($department, false);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        // ✅ now store as department_ids JSON array
        $request->merge(['department_ids' => [(int) $dept->id]]);
        return $this->store($request);
    }

    public function update(Request $request, $identifier)
    {
        $row = $this->resolvePlacementNotice($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Placement notice not found'], 404);

        // fetch clean "before" snapshot from base table for accurate diff/log
        $beforeRow = DB::table('placement_notices')->where('id', (int) $row->id)->first();

        // ✅ normalize department_ids so validation works even if frontend sends JSON string
        $normalizedDeptIds = $this->normalizeDepartmentIds($request->input('department_ids', null));
        if ($normalizedDeptIds !== null) $request->merge(['department_ids' => $normalizedDeptIds]);

        $validated = $request->validate([
            'department_ids'      => ['nullable', 'array'],
            'department_ids.*'    => ['integer', 'exists:departments,id'],

            'recruiter_id'         => ['nullable', 'integer', 'exists:recruiters,id'],

            'title'                => ['nullable', 'string', 'max:255'],
            'slug'                 => ['nullable', 'string', 'max:160'],

            'description'          => ['nullable', 'string'],
            'role_title'           => ['nullable', 'string', 'max:255'],
            'ctc'                  => ['nullable', 'numeric'],
            'eligibility'          => ['nullable', 'string'],
            'apply_url'            => ['nullable', 'string', 'max:255'],
            'last_date_to_apply'   => ['nullable', 'date'],

            'is_featured_home'     => ['nullable', 'in:0,1', 'boolean'],
            'sort_order'           => ['nullable', 'integer', 'min:0'],
            'status'               => ['nullable', 'in:draft,published,archived'],
            'publish_at'           => ['nullable', 'date'],
            'expire_at'            => ['nullable', 'date'],
            'metadata'             => ['nullable'],

            'banner_image'         => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'banner_image_remove'  => ['nullable', 'in:0,1', 'boolean'],

            'banner_image_url'     => ['nullable', 'string', 'max:255'],
        ]);

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        // normal fields
        foreach (['title','description','role_title','eligibility','apply_url','status'] as $k) {
            if (array_key_exists($k, $validated)) $update[$k] = $validated[$k];
        }

        if (array_key_exists('recruiter_id', $validated)) {
            $update['recruiter_id'] = $validated['recruiter_id'] !== null ? (int) $validated['recruiter_id'] : null;
        }
        if (array_key_exists('ctc', $validated)) {
            $update['ctc'] = $validated['ctc'];
        }
        if (array_key_exists('sort_order', $validated)) {
            $update['sort_order'] = (int) $validated['sort_order'];
        }

        // ✅ Authority control auto-sync:
        // if is_featured_home = 1 => request_for_approval = 1
        // if is_featured_home = 0 => request_for_approval = 0
        if (array_key_exists('is_featured_home', $validated)) {
            $featuredFlag = (int) $validated['is_featured_home'];
            $update['is_featured_home'] = $featuredFlag;

            if (Schema::hasColumn('placement_notices', 'request_for_approval')) {
                $update['request_for_approval'] = $featuredFlag ? 1 : 0;
            }
        }

        if (array_key_exists('last_date_to_apply', $validated)) {
            $update['last_date_to_apply'] = !empty($validated['last_date_to_apply'])
                ? Carbon::parse($validated['last_date_to_apply'])->toDateString()
                : null;
        }
        if (array_key_exists('publish_at', $validated)) {
            $update['publish_at'] = !empty($validated['publish_at']) ? Carbon::parse($validated['publish_at']) : null;
        }
        if (array_key_exists('expire_at', $validated)) {
            $update['expire_at'] = !empty($validated['expire_at']) ? Carbon::parse($validated['expire_at']) : null;
        }

        // ✅ department_ids update
        if (array_key_exists('department_ids', $validated)) {
            $deptIds = $this->normalizeDepartmentIds($validated['department_ids'] ?? null);
            $update['department_ids'] = $deptIds !== null ? json_encode($deptIds) : null;
        }

        // slug unique
        if (array_key_exists('slug', $validated) && trim((string)$validated['slug']) !== '') {
            $slug = $this->normSlug($validated['slug']);
            if ($slug === '') $slug = (string) ($row->slug ?? '');
            $slug = $this->ensureUniqueSlug($slug, (string) $row->uuid);
            $update['slug'] = $slug;
        }

        // metadata
        if (array_key_exists('metadata', $validated)) {
            $metadata = $request->input('metadata', null);
            if (is_string($metadata)) {
                $decoded = json_decode($metadata, true);
                if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
            }
            $update['metadata'] = $metadata !== null ? json_encode($metadata) : null;
        }

        // banner remove
        if (filter_var($request->input('banner_image_remove', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->deletePublicPath($row->banner_image_url ?? null);
            $update['banner_image_url'] = null;
        }

        // manual banner override (if provided and no file upload)
        if (array_key_exists('banner_image_url', $validated) && !$request->hasFile('banner_image')) {
            $update['banner_image_url'] = $validated['banner_image_url'] !== '' ? $validated['banner_image_url'] : null;
        }

        // banner replace via upload
        if ($request->hasFile('banner_image')) {
            $existingDeptIds = $this->normalizeDepartmentIds($request->input('department_ids', null));
            if ($existingDeptIds === null) {
                // fallback: try from row
                $existingDeptIds = $this->normalizeDepartmentIds($row->department_ids ?? null);
            }

            $deptKey = (!empty($existingDeptIds) && is_array($existingDeptIds))
                ? (string) ((int) $existingDeptIds[0])
                : 'global';

            $dirRel  = 'depy_uploads/placement_notices/' . $deptKey;

            $f = $request->file('banner_image');
            if (!$f || !$f->isValid()) {
                return response()->json(['success' => false, 'message' => 'Banner image upload failed'], 422);
            }

            $this->deletePublicPath($row->banner_image_url ?? null);

            $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'placement-notice');
            $meta = $this->uploadFileToPublic($f, $dirRel, $useSlug . '-banner');
            $update['banner_image_url'] = $meta['path'];
        }

        DB::table('placement_notices')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('placement_notices')->where('id', (int) $row->id)->first();

        // ✅ LOG (PUT/PATCH)
        [$changed, $oldVals, $newVals] = $this->diffForLog($beforeRow, $update);
        $this->logActivity(
            $request,
            'update',
            'placement_notices',
            'placement_notices',
            (int) $row->id,
            $changed,
            $oldVals ?: null,
            $newVals ?: null,
            'Placement notice updated'
        );

        $deptMap = $this->departmentsMap(false);

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh, $deptMap) : null,
            // ✅ for frontend dropdown selection
            'lookups' => [
                'departments' => array_values($deptMap),
            ],
        ]);
    }

    public function toggleFeatured(Request $request, $identifier)
    {
        $row = $this->resolvePlacementNotice($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Placement notice not found'], 404);

        $beforeRow = DB::table('placement_notices')->where('id', (int) $row->id)->first();

        $new = ((int) ($row->is_featured_home ?? 0)) ? 0 : 1;

        $update = [
            'is_featured_home' => $new,
            'updated_at'       => now(),
            'updated_at_ip'    => $request->ip(),
        ];

        // ✅ Authority control auto-sync:
        // if is_featured_home = 1 => request_for_approval = 1
        // if is_featured_home = 0 => request_for_approval = 0
        if (Schema::hasColumn('placement_notices', 'request_for_approval')) {
            $update['request_for_approval'] = $new ? 1 : 0;
        }

        DB::table('placement_notices')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('placement_notices')->where('id', (int) $row->id)->first();

        // ✅ LOG (PATCH)
        [$changed, $oldVals, $newVals] = $this->diffForLog($beforeRow, $update);
        $this->logActivity(
            $request,
            'toggle_featured',
            'placement_notices',
            'placement_notices',
            (int) $row->id,
            $changed,
            $oldVals ?: null,
            $newVals ?: null,
            'Placement notice featured flag toggled'
        );

        $deptMap = $this->departmentsMap(false);

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh, $deptMap) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolvePlacementNotice($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        $beforeRow = DB::table('placement_notices')->where('id', (int) $row->id)->first();
        $now = now();

        $update = [
            'deleted_at'    => $now,
            'updated_at'    => $now,
            'updated_at_ip' => $request->ip(),
        ];

        DB::table('placement_notices')->where('id', (int) $row->id)->update($update);

        // ✅ LOG (DELETE)
        [$changed, $oldVals, $newVals] = $this->diffForLog($beforeRow, $update);
        $this->logActivity(
            $request,
            'delete',
            'placement_notices',
            'placement_notices',
            (int) $row->id,
            $changed,
            $oldVals ?: null,
            $newVals ?: null,
            'Placement notice soft-deleted'
        );

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolvePlacementNotice($request, $identifier, true);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $beforeRow = DB::table('placement_notices')->where('id', (int) $row->id)->first();
        $now = now();

        $update = [
            'deleted_at'    => null,
            'updated_at'    => $now,
            'updated_at_ip' => $request->ip(),
        ];

        DB::table('placement_notices')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('placement_notices')->where('id', (int) $row->id)->first();

        // ✅ LOG (POST/PATCH)
        [$changed, $oldVals, $newVals] = $this->diffForLog($beforeRow, $update);
        $this->logActivity(
            $request,
            'restore',
            'placement_notices',
            'placement_notices',
            (int) $row->id,
            $changed,
            $oldVals ?: null,
            $newVals ?: null,
            'Placement notice restored from bin'
        );

        $deptMap = $this->departmentsMap(false);

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh, $deptMap) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolvePlacementNotice($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Placement notice not found'], 404);

        $beforeRow = DB::table('placement_notices')->where('id', (int) $row->id)->first();

        $this->deletePublicPath($row->banner_image_url ?? null);

        DB::table('placement_notices')->where('id', (int) $row->id)->delete();

        // ✅ LOG (DELETE)
        $this->logActivity(
            $request,
            'force_delete',
            'placement_notices',
            'placement_notices',
            (int) $row->id,
            null,
            $beforeRow ? (array) $beforeRow : null,
            null,
            'Placement notice permanently deleted'
        );

        return response()->json(['success' => true]);
    }

    /* ============================================
     | Public (no auth)
     |============================================ */

    public function publicIndex(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 10)));

        $deptMap  = $this->departmentsMap(false);
        $deptList = array_values($deptMap);

        $q = $this->baseQuery($request, true);
        $this->applyVisibleWindow($q);

        $q->orderByRaw('COALESCE(p.publish_at, p.created_at) desc');

        $paginator = $q->paginate($perPage);
        $items = array_map(fn($r) => $this->normalizeRow($r, $deptMap), $paginator->items());

        return response()->json([
            'success' => true,
            'data'    => $items,
            // ✅ optional for public UI too (safe)
            'lookups' => [
                'departments' => $deptList,
            ],
            'pagination' => [
                'page'      => $paginator->currentPage(),
                'per_page'  => $paginator->perPage(),
                'total'     => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function publicIndexByDepartment(Request $request, $department)
    {
        $dept = $this->resolveDepartment($department, false);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        $request->query->set('department', $dept->id);
        return $this->publicIndex($request);
    }

    public function publicShow(Request $request, $identifier)
    {
        $row = $this->resolvePlacementNotice($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Placement notice not found'], 404);

        $now = now();
        $isVisible =
            ($row->status === 'published') &&
            (empty($row->publish_at) || Carbon::parse($row->publish_at)->lte($now)) &&
            (empty($row->expire_at)  || Carbon::parse($row->expire_at)->gt($now));

        if (! $isVisible) {
            return response()->json(['message' => 'Placement notice not available'], 404);
        }

        $inc = $request->has('inc_view')
            ? filter_var($request->query('inc_view'), FILTER_VALIDATE_BOOLEAN)
            : true;

        // GET — not logging as requested
        if ($inc) {
            DB::table('placement_notices')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        $deptMap  = $this->departmentsMap(false);
        $deptList = array_values($deptMap);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row, $deptMap),
            'lookups' => [
                'departments' => $deptList,
            ],
        ]);
    }
}
