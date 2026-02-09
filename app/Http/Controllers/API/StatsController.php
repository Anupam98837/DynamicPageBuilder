<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StatsController extends Controller
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

    private function toJsonOrNull($val): ?string
    {
        if ($val === null) return null;
        $json = json_encode($val, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return ($json === false) ? null : $json;
    }

    /**
     * Safe activity logger (never breaks API flow if logging fails)
     */
    private function logActivity(
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
        $actor = $this->actor($request);

        $ua = (string) $request->userAgent();
        if ($ua !== '' && strlen($ua) > 512) {
            $ua = substr($ua, 0, 512);
        }

        try {
            DB::table('user_data_activity_log')->insert([
                'performed_by'      => (int) ($actor['id'] ?? 0),
                'performed_by_role' => (($actor['role'] ?? '') !== '') ? (string) $actor['role'] : null,
                'ip'                => $request->ip(),
                'user_agent'        => $ua !== '' ? $ua : null,

                'activity'          => $activity,
                'module'            => $module,

                'table_name'        => $tableName,
                'record_id'         => $recordId,

                'changed_fields'    => $this->toJsonOrNull($changedFields !== null ? array_values($changedFields) : null),
                'old_values'        => $this->toJsonOrNull($oldValues),
                'new_values'        => $this->toJsonOrNull($newValues),

                'log_note'          => $note,

                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            // Do nothing - never hurt functionality because of logging.
        }
    }

    /**
     * ✅ Save uploaded background image into: public/depy_uploads/stats/
     * Returns DB-storable relative path like: depy_uploads/stats/xxxxx.jpg
     */
    private function saveBackgroundImage(Request $request): ?string
    {
        if (! $request->hasFile('background_image_file')) return null;

        $file = $request->file('background_image_file');
        if (! $file || ! $file->isValid()) return null;

        $dir = public_path('depy_uploads/stats');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $origName = Str::slug($origName) ?: 'background';

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = $origName . '-' . Str::random(10) . '.' . $ext;

        // move into public/
        $file->move($dir, $name);

        return 'depy_uploads/stats/' . $name;
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('stats as s')
            ->leftJoin('users as u', 'u.id', '=', 's.created_by')
            ->select([
                's.*',
                'u.name as created_by_name',
                'u.email as created_by_email',
            ]);

        if (! $includeDeleted) {
            $q->whereNull('s.deleted_at');
        }

        // ?q= (search uuid/slug/status)
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($w) use ($term) {
                $w->where('s.uuid', 'like', $term)
                  ->orWhere('s.slug', 'like', $term)
                  ->orWhere('s.status', 'like', $term);
            });
        }

        // ?status=draft|published|archived
        if ($request->filled('status')) {
            $status = (string) $request->query('status');
            $q->where('s.status', $status);
        }

        // sort
        $sort = (string) $request->query('sort', 'updated_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['id','created_at','updated_at','status','publish_at','expire_at','views_count','scroll_latency_ms'];
        if (! in_array($sort, $allowed, true)) $sort = 'updated_at';

        $q->orderBy('s.' . $sort, $dir);

        return $q;
    }

    protected function resolveStat($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('stats as s');

        if (! $includeDeleted) $q->whereNull('s.deleted_at');

        $identifier = (string) $identifier;

        if (ctype_digit($identifier)) {
            $q->where('s.id', (int) $identifier);
        } elseif (Str::isUuid($identifier)) {
            $q->where('s.uuid', $identifier);
        } else {
            // treat as slug
            $q->where('s.slug', $identifier);
        }

        return $q->first();
    }

    protected function normalizeRow($row): array
    {
        $arr = (array) $row;

        // decode JSON columns
        foreach (['stats_items_json', 'metadata'] as $k) {
            if (array_key_exists($k, $arr) && is_string($arr[$k])) {
                $decoded = json_decode($arr[$k], true);
                $arr[$k] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
            }
        }

        // cast booleans to int-friendly values
        foreach (['auto_scroll','loop','show_arrows','show_dots'] as $k) {
            if (array_key_exists($k, $arr) && $arr[$k] !== null) {
                $arr[$k] = (int) ((bool) $arr[$k]);
            }
        }

        return $arr;
    }

    protected function normalizeJsonInput(Request $request, string $key)
    {
        $val = $request->input($key, null);

        if (is_string($val)) {
            $decoded = json_decode($val, true);
            if (json_last_error() === JSON_ERROR_NONE) $val = $decoded;
        }

        return $val;
    }

    protected function visibilityQuery()
    {
        // "visible" = published + within publish/expire window + not deleted
        $now = now();

        return DB::table('stats')
            ->whereNull('deleted_at')
            ->where('status', 'published')
            ->where(function ($w) use ($now) {
                $w->whereNull('publish_at')
                  ->orWhere('publish_at', '<=', $now);
            })
            ->where(function ($w) use ($now) {
                $w->whereNull('expire_at')
                  ->orWhere('expire_at', '>', $now);
            });
    }

    /* ============================================
     | CRUD (Admin/Auth)
     |============================================ */

    // List (Admin)
    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $q = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        if ($onlyDeleted) $q->whereNotNull('s.deleted_at');

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

    // Latest row (not deleted) - useful for admin UI
    public function current(Request $request)
    {
        $row = DB::table('stats')
            ->whereNull('deleted_at')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'success' => true,
            'item' => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    // Show by id|uuid|slug
    public function show(Request $request, $identifier)
    {
        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveStat($identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Stats not found'], 404);

        return response()->json([
            'success' => true,
            'item' => $this->normalizeRow($row),
        ]);
    }

    // Create new stats row
    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'slug'                 => ['required','string','max:160', Rule::unique('stats', 'slug')],

            // ✅ allow either url/path OR file (but at least one must exist)
            'background_image_url' => ['nullable','string','max:160','required_without:background_image_file'],
            'background_image_file'=> ['nullable','image','max:6144','required_without:background_image_url'],

            // allow array or json string; we validate lightly here
            'stats_items_json'     => ['required'],

            'auto_scroll'          => ['nullable','in:0,1','boolean'],
            'scroll_latency_ms'    => ['nullable','integer','min:0','max:600000'],
            'loop'                 => ['nullable','in:0,1','boolean'],
            'show_arrows'          => ['nullable','in:0,1','boolean'],
            'show_dots'            => ['nullable','in:0,1','boolean'],

            'status'               => ['nullable','string','max:20','in:draft,published,archived'],
            'publish_at'           => ['nullable','date'],
            'expire_at'            => ['nullable','date','after_or_equal:publish_at'],

            'views_count'          => ['nullable','integer','min:0'],

            'metadata'             => ['nullable'],
        ]);

        // ✅ if file uploaded, store it and override DB path
        $uploadedPath = $this->saveBackgroundImage($request);
        if ($uploadedPath) {
            $validated['background_image_url'] = $uploadedPath;
        }

        $uuid = (string) Str::uuid();
        $now  = now();

        $items = $this->normalizeJsonInput($request, 'stats_items_json');
        $meta  = $this->normalizeJsonInput($request, 'metadata');

        // stats_items_json must be storable json
        if (!is_array($items) && !is_object($items)) {
            return response()->json([
                'message' => 'stats_items_json must be a JSON array/object (or a valid JSON string).'
            ], 422);
        }

        $id = DB::table('stats')->insertGetId([
            'uuid'                 => $uuid,
            'slug'                 => (string) $validated['slug'],
            'background_image_url' => (string) ($validated['background_image_url'] ?? ''),

            'stats_items_json'     => json_encode($items),

            'auto_scroll'          => array_key_exists('auto_scroll', $validated) ? (int) $validated['auto_scroll'] : 1,
            'scroll_latency_ms'    => array_key_exists('scroll_latency_ms', $validated) ? (int) $validated['scroll_latency_ms'] : 3000,
            'loop'                 => array_key_exists('loop', $validated) ? (int) $validated['loop'] : 1,
            'show_arrows'          => array_key_exists('show_arrows', $validated) ? (int) $validated['show_arrows'] : 1,
            'show_dots'            => array_key_exists('show_dots', $validated) ? (int) $validated['show_dots'] : 0,

            'status'               => array_key_exists('status', $validated) && $validated['status'] !== null
                                        ? (string) $validated['status'] : 'draft',
            'publish_at'           => $validated['publish_at'] ?? null,
            'expire_at'            => $validated['expire_at'] ?? null,

            'views_count'          => array_key_exists('views_count', $validated) && $validated['views_count'] !== null
                                        ? (int) $validated['views_count'] : 0,

            'created_by'           => $actor['id'] ?: null,
            'created_at'           => $now,
            'updated_at'           => $now,
            'created_at_ip'        => $request->ip(),
            'updated_at_ip'        => $request->ip(),
            'metadata'             => $meta !== null ? json_encode($meta) : null,
        ]);

        $row = DB::table('stats')->where('id', (int) $id)->first();

        // ✅ LOG: create
        if ($row) {
            $this->logActivity(
                $request,
                'create',
                'stats',
                'stats',
                (int) $id,
                ['uuid','slug','background_image_url','stats_items_json','auto_scroll','scroll_latency_ms','loop','show_arrows','show_dots','status','publish_at','expire_at','views_count','metadata'],
                null,
                $this->normalizeRow($row),
                'Stats created'
            );
        }

        return response()->json([
            'success' => true,
            'item' => $row ? $this->normalizeRow($row) : null,
        ], 201);
    }

    // Upsert current (update latest active; else create)
    public function upsertCurrent(Request $request)
    {
        $row = DB::table('stats')
            ->whereNull('deleted_at')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if (! $row) return $this->store($request);

        // update by uuid for safety
        return $this->update($request, $row->uuid);
    }

    // Update by id|uuid|slug
    public function update(Request $request, $identifier)
    {
        $row = $this->resolveStat($identifier, true);
        if (! $row) return response()->json(['message' => 'Stats not found'], 404);

        $before = $this->normalizeRow($row);

        $validated = $request->validate([
            'slug'                 => ['nullable','string','max:160', Rule::unique('stats', 'slug')->ignore((int) $row->id)],
            'background_image_url' => ['nullable','string','max:160'],
            'background_image_file'=> ['nullable','image','max:6144'], // ✅ add this
            'stats_items_json'     => ['nullable'],

            'auto_scroll'          => ['nullable','in:0,1','boolean'],
            'scroll_latency_ms'    => ['nullable','integer','min:0','max:600000'],
            'loop'                 => ['nullable','in:0,1','boolean'],
            'show_arrows'          => ['nullable','in:0,1','boolean'],
            'show_dots'            => ['nullable','in:0,1','boolean'],

            'status'               => ['nullable','string','max:20','in:draft,published,archived'],
            'publish_at'           => ['nullable','date'],
            'expire_at'            => ['nullable','date','after_or_equal:publish_at'],

            'views_count'          => ['nullable','integer','min:0'],

            'metadata'             => ['nullable'],
        ]);

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        foreach (['slug','background_image_url','status','publish_at','expire_at'] as $k) {
            if (array_key_exists($k, $validated)) {
                $update[$k] = $validated[$k];
            }
        }

        foreach (['auto_scroll','loop','show_arrows','show_dots'] as $k) {
            if (array_key_exists($k, $validated)) {
                $update[$k] = $validated[$k] !== null ? (int) $validated[$k] : null;
            }
        }

        if (array_key_exists('scroll_latency_ms', $validated)) {
            $update['scroll_latency_ms'] = $validated['scroll_latency_ms'] !== null ? (int) $validated['scroll_latency_ms'] : null;
        }

        if (array_key_exists('views_count', $validated)) {
            $update['views_count'] = $validated['views_count'] !== null ? (int) $validated['views_count'] : null;
        }

        if (array_key_exists('stats_items_json', $validated)) {
            $items = $this->normalizeJsonInput($request, 'stats_items_json');
            if ($items !== null && !is_array($items) && !is_object($items)) {
                return response()->json([
                    'message' => 'stats_items_json must be a JSON array/object (or a valid JSON string).'
                ], 422);
            }
            $update['stats_items_json'] = $items !== null ? json_encode($items) : null;
        }

        if (array_key_exists('metadata', $validated)) {
            $meta = $this->normalizeJsonInput($request, 'metadata');
            $update['metadata'] = $meta !== null ? json_encode($meta) : null;
        }

        // ✅ if file uploaded, store it and override background_image_url
        $uploadedPath = $this->saveBackgroundImage($request);
        if ($uploadedPath) {
            $update['background_image_url'] = $uploadedPath;
        }

        DB::table('stats')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('stats')->where('id', (int) $row->id)->first();
        $after = $fresh ? $this->normalizeRow($fresh) : null;

        // ✅ LOG: update
        $changed = array_values(array_diff(array_keys($update), ['updated_at','updated_at_ip']));
        $this->logActivity(
            $request,
            'update',
            'stats',
            'stats',
            (int) $row->id,
            $changed ?: null,
            $before,
            $after,
            'Stats updated'
        );

        return response()->json([
            'success' => true,
            'item' => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    // Soft delete
    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveStat($identifier, false);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        $before = $this->normalizeRow($row);

        DB::table('stats')->where('id', (int) $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('stats')->where('id', (int) $row->id)->first();
        $after = $fresh ? $this->normalizeRow($fresh) : null;

        // ✅ LOG: soft delete
        $this->logActivity(
            $request,
            'delete',
            'stats',
            'stats',
            (int) $row->id,
            ['deleted_at'],
            $before,
            $after,
            'Stats soft deleted'
        );

        return response()->json(['success' => true]);
    }

    // Restore from trash
    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveStat($identifier, true);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $before = $this->normalizeRow($row);

        DB::table('stats')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('stats')->where('id', (int) $row->id)->first();
        $after = $fresh ? $this->normalizeRow($fresh) : null;

        // ✅ LOG: restore
        $this->logActivity(
            $request,
            'restore',
            'stats',
            'stats',
            (int) $row->id,
            ['deleted_at'],
            $before,
            $after,
            'Stats restored from trash'
        );

        return response()->json([
            'success' => true,
            'item' => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    // Hard delete
    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveStat($identifier, true);
        if (! $row) return response()->json(['message' => 'Stats not found'], 404);

        $before = $this->normalizeRow($row);

        DB::table('stats')->where('id', (int) $row->id)->delete();

        // ✅ LOG: force delete (permanent)
        $this->logActivity(
            $request,
            'force_delete',
            'stats',
            'stats',
            (int) $row->id,
            null,
            $before,
            null,
            'Stats permanently deleted'
        );

        return response()->json(['success' => true]);
    }

    /* ============================================
     | Public (No Auth)
     |============================================ */

    public function publicIndex(Request $request)
    {
        $q = $this->visibilityQuery()
            ->orderByDesc('updated_at')
            ->orderByDesc('id');

        $items = $q->get()->map(fn($r) => $this->normalizeRow($r))->values();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function publicCurrent(Request $request)
    {
        $row = $this->visibilityQuery()
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'success' => true,
            'item' => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    public function publicShow(Request $request, $identifier)
    {
        $identifier = (string) $identifier;

        $q = $this->visibilityQuery();

        if (ctype_digit($identifier)) {
            $q->where('id', (int) $identifier);
        } elseif (Str::isUuid($identifier)) {
            $q->where('uuid', $identifier);
        } else {
            $q->where('slug', $identifier);
        }

        $row = $q->first();

        if (! $row) return response()->json(['message' => 'Stats not found'], 404);

        return response()->json([
            'success' => true,
            'item' => $this->normalizeRow($row),
        ]);
    }
}
