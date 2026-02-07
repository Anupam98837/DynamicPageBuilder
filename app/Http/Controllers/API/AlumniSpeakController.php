<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AlumniSpeakController extends Controller
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
     * Write activity log into user_data_activity_log.
     * IMPORTANT: Must never break main controller flows.
     */
    private function writeActivityLog(
        Request $request,
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
            $actor = $this->actor($request);

            // performed_by is NOT NULL in migration, so fallback to 0
            $performedBy = (int) ($actor['id'] ?? 0);
            if ($performedBy < 0) $performedBy = 0;

            $ua = $request->userAgent();
            if (is_string($ua) && strlen($ua) > 512) {
                $ua = substr($ua, 0, 512);
            }

            DB::table('user_data_activity_log')->insert([
                'performed_by'       => $performedBy,
                'performed_by_role'  => ($actor['role'] !== '' ? $actor['role'] : null),
                'ip'                 => $request->ip(),
                'user_agent'         => $ua,

                'activity'           => $activity,
                'module'             => $module,

                'table_name'         => $tableName,
                'record_id'          => $recordId,

                'changed_fields'     => $changedFields !== null ? json_encode(array_values($changedFields)) : null,
                'old_values'         => $oldValues !== null ? json_encode($oldValues) : null,
                'new_values'         => $newValues !== null ? json_encode($newValues) : null,

                'log_note'           => $note,

                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (\Throwable $e) {
            // Never break core functionality if logging fails
            try {
                Log::warning('Activity log insert failed (AlumniSpeakController)', [
                    'error' => $e->getMessage(),
                ]);
            } catch (\Throwable $ignored) {}
        }
    }

    /**
     * Compute changes for a given set of keys between before/after row arrays.
     */
    private function computeChanges(array $before, array $after, array $keys, array $excludeKeys = ['updated_at','updated_at_ip']): array
    {
        $changed = [];
        $old = [];
        $new = [];

        $exclude = array_flip($excludeKeys);

        foreach ($keys as $k) {
            if (isset($exclude[$k])) continue;

            $bv = $before[$k] ?? null;
            $av = $after[$k] ?? null;

            // robust-ish comparison for scalars/nulls
            if (json_encode($bv) !== json_encode($av)) {
                $changed[] = $k;
                $old[$k] = $bv;
                $new[$k] = $av;
            }
        }

        return [$changed, $old, $new];
    }

    protected function resolveDepartmentId($department)
    {
        $q = DB::table('departments');

        if (ctype_digit((string) $department)) {
            $q->where('id', (int) $department);
        } elseif (Str::isUuid((string) $department)) {
            $q->where('uuid', (string) $department);
        } else {
            // treat as slug
            $q->where('slug', (string) $department);
        }

        $row = $q->first();
        return $row ? (int) $row->id : null;
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('alumni_speak as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.created_by')
            ->leftJoin('departments as d', 'd.id', '=', 'a.department_id')
            ->select([
                'a.*',
                'u.name as created_by_name',
                'u.email as created_by_email',
                'd.title as department_name',
                'd.slug as department_slug',
            ]);

        if (! $includeDeleted) {
            $q->whereNull('a.deleted_at');
        }

        // ?q= (search uuid/slug/title)
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($w) use ($term) {
                $w->where('a.uuid', 'like', $term)
                  ->orWhere('a.slug', 'like', $term)
                  ->orWhere('a.title', 'like', $term);
            });
        }

        // ?status=draft|published|archived
        if ($request->filled('status')) {
            $q->where('a.status', (string) $request->query('status'));
        }

        // ?department_id=#
        if ($request->filled('department_id') && ctype_digit((string) $request->query('department_id'))) {
            $q->where('a.department_id', (int) $request->query('department_id'));
        }

        // sort
        $sort = (string) $request->query('sort', 'updated_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = [
            'id','created_at','updated_at',
            'title','status','sort_order',
            'publish_at','expire_at','views_count',
            'scroll_latency_ms'
        ];
        if (! in_array($sort, $allowed, true)) $sort = 'updated_at';

        $q->orderBy('a.' . $sort, $dir)->orderBy('a.id', 'desc');

        return $q;
    }

    protected function resolveItem($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('alumni_speak as a');
        if (! $includeDeleted) $q->whereNull('a.deleted_at');

        if (ctype_digit((string) $identifier)) {
            $q->where('a.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('a.uuid', (string) $identifier);
        } else {
            // slug
            $q->where('a.slug', (string) $identifier);
        }

        return $q->first();
    }

    protected function normalizeRow($row): array
    {
        $arr = (array) $row;

        // decode iframe_urls_json
        $urls = $arr['iframe_urls_json'] ?? null;
        if (is_string($urls)) {
            $decoded = json_decode($urls, true);
            $arr['iframe_urls_json'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // decode metadata
        $meta = $arr['metadata'] ?? null;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // cast booleans
        foreach (['auto_scroll','loop','show_arrows','show_dots'] as $k) {
            if (array_key_exists($k, $arr) && $arr[$k] !== null) {
                $arr[$k] = (int) ((bool) $arr[$k]);
            }
        }

        return $arr;
    }

    protected function normalizeJsonInput($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) return $decoded;
        }
        return $value;
    }

    protected function makeUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        if ($slug === '') $slug = 'alumni-speak';

        $try = $slug;
        $i = 2;

        while (true) {
            $q = DB::table('alumni_speak')->where('slug', $try);
            if ($ignoreId) $q->where('id', '!=', $ignoreId);

            $exists = $q->exists();
            if (! $exists) return $try;

            $try = $slug . '-' . $i;
            $i++;
        }
    }

    protected function assertPublishExpireValid(array $data)
    {
        if (!empty($data['publish_at']) && !empty($data['expire_at'])) {
            $p = strtotime((string) $data['publish_at']);
            $e = strtotime((string) $data['expire_at']);
            if ($p !== false && $e !== false && $e <= $p) {
                return 'expire_at must be after publish_at';
            }
        }
        return null;
    }

    /* ============================================
     | CRUD (Admin/Auth)
     |============================================ */

    // List
    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $q = $this->baseQuery($request, $includeDeleted || $onlyDeleted);
        if ($onlyDeleted) $q->whereNotNull('a.deleted_at');

        $p = $q->paginate($perPage);

        $items = array_map(fn($r) => $this->normalizeRow($r), $p->items());

        return response()->json([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'page'      => $p->currentPage(),
                'per_page'  => $p->perPage(),
                'total'     => $p->total(),
                'last_page' => $p->lastPage(),
            ],
        ]);
    }

    // Trash list
    public function trash(Request $request)
    {
        $request->query->set('only_trashed', '1');
        return $this->index($request);
    }

    // Show by id|uuid|slug
    public function show(Request $request, $identifier)
    {
        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = DB::table('alumni_speak as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.created_by')
            ->leftJoin('departments as d', 'd.id', '=', 'a.department_id')
            ->select([
                'a.*',
                'u.name as created_by_name',
                'u.email as created_by_email',
                'd.title as department_name',
                'd.slug as department_slug',
            ]);

        if (! $includeDeleted) $row->whereNull('a.deleted_at');

        if (ctype_digit((string) $identifier)) {
            $row->where('a.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $row->where('a.uuid', (string) $identifier);
        } else {
            $row->where('a.slug', (string) $identifier);
        }

        $item = $row->first();
        if (! $item) return response()->json(['message' => 'Alumni speak not found'], 404);

        return response()->json([
            'success' => true,
            'item' => $this->normalizeRow($item),
        ]);
    }

    // Department-scoped list
    public function indexByDepartment(Request $request, $department)
    {
        $deptId = $this->resolveDepartmentId($department);
        if (! $deptId) return response()->json(['message' => 'Department not found'], 404);

        $request->query->set('department_id', (string) $deptId);
        return $this->index($request);
    }

    // Department-scoped show
    public function showByDepartment(Request $request, $department, $identifier)
    {
        $deptId = $this->resolveDepartmentId($department);
        if (! $deptId) return response()->json(['message' => 'Department not found'], 404);

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $q = DB::table('alumni_speak as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.created_by')
            ->leftJoin('departments as d', 'd.id', '=', 'a.department_id')
            ->select([
                'a.*',
                'u.name as created_by_name',
                'u.email as created_by_email',
                'd.title as department_name',
                'd.slug as department_slug',
            ])
            ->where('a.department_id', $deptId);

        if (! $includeDeleted) $q->whereNull('a.deleted_at');

        if (ctype_digit((string) $identifier)) {
            $q->where('a.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('a.uuid', (string) $identifier);
        } else {
            $q->where('a.slug', (string) $identifier);
        }

        $item = $q->first();
        if (! $item) return response()->json(['message' => 'Alumni speak not found'], 404);

        return response()->json([
            'success' => true,
            'item' => $this->normalizeRow($item),
        ]);
    }

    // Create (global)  [POST] => LOG
    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'department_id'      => ['nullable', 'integer', 'exists:departments,id'],
            'title'              => ['required', 'string', 'max:255'],
            'slug'               => ['nullable', 'string', 'max:160'],
            'description'        => ['nullable', 'string'],

            // required, NOT NULL in schema
            'iframe_urls_json'   => ['required'],

            'auto_scroll'        => ['nullable', 'in:0,1', 'boolean'],
            'scroll_latency_ms'  => ['nullable', 'integer', 'min:0', 'max:600000'],
            'loop'               => ['nullable', 'in:0,1', 'boolean'],
            'show_arrows'        => ['nullable', 'in:0,1', 'boolean'],
            'show_dots'          => ['nullable', 'in:0,1', 'boolean'],

            'sort_order'         => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'status'             => ['nullable', 'string', 'in:draft,published,archived', 'max:20'],

            'publish_at'         => ['nullable', 'date'],
            'expire_at'          => ['nullable', 'date'],

            'metadata'           => ['nullable'],
        ]);

        if ($msg = $this->assertPublishExpireValid($validated)) {
            return response()->json(['message' => $msg], 422);
        }

        $uuid = (string) Str::uuid();
        $now  = now();

        $iframe = $this->normalizeJsonInput($request->input('iframe_urls_json'));
        if (!is_array($iframe) && !is_object($iframe)) {
            // keep strict because column is NOT NULL and intended to be JSON list
            return response()->json(['message' => 'iframe_urls_json must be a JSON array/object'], 422);
        }

        $metadata = $this->normalizeJsonInput($request->input('metadata', null));

        $slugInput = $request->input('slug');
        $slug = $slugInput ? $this->makeUniqueSlug((string) $slugInput) : $this->makeUniqueSlug((string) $validated['title']);

        $payload = [
            'uuid'             => $uuid,
            'department_id'     => array_key_exists('department_id', $validated) ? $validated['department_id'] : null,
            'slug'             => $slug,
            'title'            => (string) $validated['title'],
            'description'      => array_key_exists('description', $validated) ? $validated['description'] : null,

            'iframe_urls_json'  => json_encode($iframe),

            'auto_scroll'       => array_key_exists('auto_scroll', $validated) ? (int) $validated['auto_scroll'] : 1,
            'scroll_latency_ms' => array_key_exists('scroll_latency_ms', $validated) ? (int) $validated['scroll_latency_ms'] : 3000,
            'loop'              => array_key_exists('loop', $validated) ? (int) $validated['loop'] : 1,
            'show_arrows'       => array_key_exists('show_arrows', $validated) ? (int) $validated['show_arrows'] : 1,
            'show_dots'         => array_key_exists('show_dots', $validated) ? (int) $validated['show_dots'] : 1,

            'sort_order'        => array_key_exists('sort_order', $validated) ? (int) $validated['sort_order'] : 0,
            'status'            => array_key_exists('status', $validated) && $validated['status'] !== null ? (string) $validated['status'] : 'draft',

            'publish_at'        => array_key_exists('publish_at', $validated) ? $validated['publish_at'] : null,
            'expire_at'         => array_key_exists('expire_at', $validated) ? $validated['expire_at'] : null,

            'views_count'       => 0,

            'created_by'        => $actor['id'] ?: null,
            'created_at'        => $now,
            'updated_at'        => $now,
            'created_at_ip'     => $request->ip(),
            'updated_at_ip'     => $request->ip(),
            'metadata'          => $metadata !== null ? json_encode($metadata) : null,
        ];

        $id = DB::table('alumni_speak')->insertGetId($payload);

        $fresh = DB::table('alumni_speak')->where('id', (int) $id)->first();

        // LOG: create
        $newValues = $fresh ? (array) $fresh : (array) ($payload + ['id' => (int) $id]);
        $this->writeActivityLog(
            $request,
            'create',
            'alumni_speak',
            'alumni_speak',
            (int) $id,
            array_keys($newValues),
            null,
            $newValues,
            'Created alumni_speak'
        );

        return response()->json([
            'success' => true,
            'item' => $fresh ? $this->normalizeRow($fresh) : null,
        ], 201);
    }

    // Create (department-scoped) [POST] => store() already logs (avoid double log)
    public function storeForDepartment(Request $request, $department)
    {
        $deptId = $this->resolveDepartmentId($department);
        if (! $deptId) return response()->json(['message' => 'Department not found'], 404);

        // inject department_id (but allow overriding not recommended)
        $request->merge(['department_id' => $deptId]);
        return $this->store($request);
    }

    // Update by id|uuid|slug  [PUT/PATCH] => LOG
    public function update(Request $request, $identifier)
    {
        $row = $this->resolveItem($identifier, true);
        if (! $row) return response()->json(['message' => 'Alumni speak not found'], 404);

        $beforeArr = (array) $row;

        $validated = $request->validate([
            'department_id'      => ['nullable', 'integer', 'exists:departments,id'],
            'title'              => ['nullable', 'string', 'max:255'],
            'slug'               => ['nullable', 'string', 'max:160'],
            'description'        => ['nullable', 'string'],

            'iframe_urls_json'   => ['nullable'],

            'auto_scroll'        => ['nullable', 'in:0,1', 'boolean'],
            'scroll_latency_ms'  => ['nullable', 'integer', 'min:0', 'max:600000'],
            'loop'               => ['nullable', 'in:0,1', 'boolean'],
            'show_arrows'        => ['nullable', 'in:0,1', 'boolean'],
            'show_dots'          => ['nullable', 'in:0,1', 'boolean'],

            'sort_order'         => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'status'             => ['nullable', 'string', 'in:draft,published,archived', 'max:20'],

            'publish_at'         => ['nullable', 'date'],
            'expire_at'          => ['nullable', 'date'],

            'metadata'           => ['nullable'],
        ]);

        if ($msg = $this->assertPublishExpireValid($validated)) {
            return response()->json(['message' => $msg], 422);
        }

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        if (array_key_exists('department_id', $validated)) $update['department_id'] = $validated['department_id'];
        if (array_key_exists('title', $validated)) $update['title'] = $validated['title'];
        if (array_key_exists('description', $validated)) $update['description'] = $validated['description'];

        if (array_key_exists('slug', $validated)) {
            $update['slug'] = $validated['slug'] !== null
                ? $this->makeUniqueSlug((string) $validated['slug'], (int) $row->id)
                : $row->slug;
        }

        if (array_key_exists('iframe_urls_json', $validated)) {
            $iframe = $this->normalizeJsonInput($request->input('iframe_urls_json'));
            if (!is_array($iframe) && !is_object($iframe) && $iframe !== null) {
                return response()->json(['message' => 'iframe_urls_json must be a JSON array/object'], 422);
            }
            // do NOT allow setting null because DB column is NOT NULL
            if ($iframe !== null) $update['iframe_urls_json'] = json_encode($iframe);
        }

        foreach (['auto_scroll','loop','show_arrows','show_dots'] as $k) {
            if (array_key_exists($k, $validated)) $update[$k] = $validated[$k] !== null ? (int) $validated[$k] : null;
        }
        if (array_key_exists('scroll_latency_ms', $validated)) $update['scroll_latency_ms'] = $validated['scroll_latency_ms'] !== null ? (int) $validated['scroll_latency_ms'] : null;

        if (array_key_exists('sort_order', $validated)) $update['sort_order'] = $validated['sort_order'] !== null ? (int) $validated['sort_order'] : null;
        if (array_key_exists('status', $validated)) $update['status'] = $validated['status'] !== null ? (string) $validated['status'] : null;

        if (array_key_exists('publish_at', $validated)) $update['publish_at'] = $validated['publish_at'];
        if (array_key_exists('expire_at', $validated)) $update['expire_at'] = $validated['expire_at'];

        if (array_key_exists('metadata', $validated)) {
            $metadata = $this->normalizeJsonInput($request->input('metadata', null));
            $update['metadata'] = $metadata !== null ? json_encode($metadata) : null;
        }

        DB::table('alumni_speak')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('alumni_speak')->where('id', (int) $row->id)->first();

        // LOG: update
        $afterArr = $fresh ? (array) $fresh : [];
        $logKeys = array_keys($update);
        [$changedFields, $oldValues, $newValues] = $this->computeChanges($beforeArr, $afterArr, $logKeys);

        $this->writeActivityLog(
            $request,
            'update',
            'alumni_speak',
            'alumni_speak',
            (int) $row->id,
            $changedFields,
            $oldValues,
            $newValues,
            'Updated alumni_speak'
        );

        return response()->json([
            'success' => true,
            'item' => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    // Soft delete  [DELETE] => LOG
    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveItem($identifier, false);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        $beforeArr = (array) $row;

        DB::table('alumni_speak')->where('id', (int) $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $after = DB::table('alumni_speak')->where('id', (int) $row->id)->first();
        $afterArr = $after ? (array) $after : [];

        // LOG: soft delete
        [$changedFields, $oldValues, $newValues] = $this->computeChanges(
            $beforeArr,
            $afterArr,
            ['deleted_at','updated_at','updated_at_ip']
        );

        $this->writeActivityLog(
            $request,
            'delete',
            'alumni_speak',
            'alumni_speak',
            (int) $row->id,
            $changedFields ?: ['deleted_at'],
            $oldValues ?: ['deleted_at' => ($beforeArr['deleted_at'] ?? null)],
            $newValues ?: ['deleted_at' => ($afterArr['deleted_at'] ?? null)],
            'Soft deleted alumni_speak'
        );

        return response()->json(['success' => true]);
    }

    // Restore  [POST/PUT depending on route] => LOG
    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveItem($identifier, true);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $beforeArr = (array) $row;

        DB::table('alumni_speak')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('alumni_speak')->where('id', (int) $row->id)->first();
        $afterArr = $fresh ? (array) $fresh : [];

        // LOG: restore
        [$changedFields, $oldValues, $newValues] = $this->computeChanges(
            $beforeArr,
            $afterArr,
            ['deleted_at','updated_at','updated_at_ip']
        );

        $this->writeActivityLog(
            $request,
            'restore',
            'alumni_speak',
            'alumni_speak',
            (int) $row->id,
            $changedFields ?: ['deleted_at'],
            $oldValues ?: ['deleted_at' => ($beforeArr['deleted_at'] ?? null)],
            $newValues ?: ['deleted_at' => ($afterArr['deleted_at'] ?? null)],
            'Restored alumni_speak'
        );

        return response()->json([
            'success' => true,
            'item' => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    // Hard delete  [DELETE] => LOG
    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveItem($identifier, true);
        if (! $row) return response()->json(['message' => 'Alumni speak not found'], 404);

        $beforeArr = (array) $row;

        DB::table('alumni_speak')->where('id', (int) $row->id)->delete();

        // LOG: force delete (snapshot old row)
        $this->writeActivityLog(
            $request,
            'force_delete',
            'alumni_speak',
            'alumni_speak',
            (int) $row->id,
            array_keys($beforeArr),
            $beforeArr,
            null,
            'Hard deleted alumni_speak'
        );

        return response()->json(['success' => true]);
    }

    /* ============================================
     | Public (No Auth)
     |============================================ */

    protected function publicVisibilityQuery()
    {
        $now = now();

        return DB::table('alumni_speak as a')
            ->leftJoin('departments as d', 'd.id', '=', 'a.department_id')
            ->select([
                'a.*',
                'd.title as department_name',
                'd.slug as department_slug',
            ])
            ->whereNull('a.deleted_at')
            ->where('a.status', 'published')
            ->where(function ($w) use ($now) {
                $w->whereNull('a.publish_at')->orWhere('a.publish_at', '<=', $now);
            })
            ->where(function ($w) use ($now) {
                $w->whereNull('a.expire_at')->orWhere('a.expire_at', '>', $now);
            });
    }

    // Public list
    public function publicIndex(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $q = $this->publicVisibilityQuery();

        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($w) use ($term) {
                $w->where('a.slug', 'like', $term)
                  ->orWhere('a.title', 'like', $term);
            });
        }

        if ($request->filled('department_id') && ctype_digit((string) $request->query('department_id'))) {
            $q->where('a.department_id', (int) $request->query('department_id'));
        }

        $q->orderBy('a.sort_order', 'asc')->orderBy('a.updated_at', 'desc')->orderBy('a.id', 'desc');

        $p = $q->paginate($perPage);
        $items = array_map(fn($r) => $this->normalizeRow($r), $p->items());

        return response()->json([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'page'      => $p->currentPage(),
                'per_page'  => $p->perPage(),
                'total'     => $p->total(),
                'last_page' => $p->lastPage(),
            ],
        ]);
    }

    // Public show
    public function publicShow(Request $request, $identifier)
    {
        $q = $this->publicVisibilityQuery();

        if (ctype_digit((string) $identifier)) {
            $q->where('a.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('a.uuid', (string) $identifier);
        } else {
            $q->where('a.slug', (string) $identifier);
        }

        $item = $q->first();
        if (! $item) return response()->json(['message' => 'Alumni speak not available'], 404);

        return response()->json([
            'success' => true,
            'item' => $this->normalizeRow($item),
        ]);
    }

    // Public department list
    public function publicIndexByDepartment(Request $request, $department)
    {
        $deptId = $this->resolveDepartmentId($department);
        if (! $deptId) return response()->json(['message' => 'Department not found'], 404);

        $request->query->set('department_id', (string) $deptId);
        return $this->publicIndex($request);
    }
}
