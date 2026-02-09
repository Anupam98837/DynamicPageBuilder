<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class NoticeMarqueeController extends Controller
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

    private function jsonOrNull($value): ?string
    {
        if ($value === null) return null;

        // Keep scalars allowed (store as JSON scalar)
        if (is_scalar($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        // Arrays/objects
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function diffSnapshots(array $before, array $after, array $ignoreKeys = []): array
    {
        $ignore = array_fill_keys($ignoreKeys, true);

        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        $changed = [];

        $old = [];
        $new = [];

        foreach ($keys as $k) {
            if (isset($ignore[$k])) continue;

            $b = $before[$k] ?? null;
            $a = $after[$k] ?? null;

            // Normalize arrays/objects for comparison
            $bCmp = (is_array($b) || is_object($b)) ? json_encode($b, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $b;
            $aCmp = (is_array($a) || is_object($a)) ? json_encode($a, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $a;

            if ($bCmp !== $aCmp) {
                $changed[] = $k;
                $old[$k] = $b;
                $new[$k] = $a;
            }
        }

        return [$changed, $old, $new];
    }

    private function logActivity(
        Request $r,
        string $activity,
        string $module,
        string $tableName,
        $recordId = null,
        ?array $changedFields = null,
        $oldValues = null,
        $newValues = null,
        ?string $note = null
    ): void {
        try {
            $actor = $this->actor($r);

            $role = trim((string) ($actor['role'] ?? ''));
            $role = $role !== '' ? $role : null;

            $ua = (string) ($r->userAgent() ?? $r->header('User-Agent', ''));
            $ua = substr($ua, 0, 512);

            DB::table('user_data_activity_log')->insert([
                'performed_by'       => (int) ($actor['id'] ?? 0),
                'performed_by_role'  => $role,
                'ip'                 => (string) ($r->ip() ?? ''),
                'user_agent'         => $ua !== '' ? $ua : null,

                'activity'           => substr($activity, 0, 50),
                'module'             => substr($module, 0, 100),

                'table_name'         => substr($tableName, 0, 128),
                'record_id'          => (is_numeric($recordId) ? (int) $recordId : null),

                'changed_fields'     => $changedFields ? $this->jsonOrNull(array_values($changedFields)) : null,
                'old_values'         => $oldValues !== null ? $this->jsonOrNull($oldValues) : null,
                'new_values'         => $newValues !== null ? $this->jsonOrNull($newValues) : null,

                'log_note'           => $note,

                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (Throwable $e) {
            // Never break functionality because logging failed
        }
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('notice_marquee as m')
            ->leftJoin('users as u', 'u.id', '=', 'm.created_by')
            ->select([
                'm.*',
                'u.name as created_by_name',
                'u.email as created_by_email',
            ]);

        if (! $includeDeleted) {
            $q->whereNull('m.deleted_at');
        }

        // Search: ?q= (uuid/slug/status/direction)
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($w) use ($term) {
                $w->where('m.uuid', 'like', $term)
                  ->orWhere('m.slug', 'like', $term)
                  ->orWhere('m.status', 'like', $term)
                  ->orWhere('m.direction', 'like', $term);
            });
        }

        // Filter: ?status=   (expects 0/1)
        if ($request->filled('status')) {
            $q->where('m.status', (string) $request->query('status')); // stored as "0"/"1"
        }

        // Sort
        $sort = (string) $request->query('sort', 'updated_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = [
            'id','created_at','updated_at','publish_at','expire_at','views_count',
            'scroll_speed','scroll_latency_ms','status'
        ];
        if (! in_array($sort, $allowed, true)) $sort = 'updated_at';

        $q->orderBy('m.' . $sort, $dir);

        return $q;
    }

    protected function resolveRow($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('notice_marquee as m');

        if (! $includeDeleted) $q->whereNull('m.deleted_at');

        $identifier = (string) $identifier;

        if (ctype_digit($identifier)) {
            $q->where('m.id', (int) $identifier);
        } elseif (Str::isUuid($identifier)) {
            $q->where('m.uuid', $identifier);
        } else {
            // slug
            $q->where('m.slug', $identifier);
        }

        return $q->first();
    }

    protected function normalizeRow($row): array
    {
        $arr = (array) $row;

        // decode notice_items_json
        $items = $arr['notice_items_json'] ?? null;
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            $items = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // ✅ normalize items output so frontend can use text (and backward compatible title)
        if (is_array($items)) {
            $out = [];
            foreach ($items as $it) {
                if (!is_array($it)) continue;

                $text = trim((string)($it['text'] ?? $it['title'] ?? $it['label'] ?? $it['name'] ?? $it['message'] ?? ''));
                $url  = trim((string)($it['url'] ?? $it['link'] ?? $it['href'] ?? ''));

                if ($text === '' && $url === '') continue;

                $rowIt = [
                    'text' => $text,
                    'url'  => $url,
                ];

                // keep sort_order if present
                if (array_key_exists('sort_order', $it)) $rowIt['sort_order'] = $it['sort_order'];

                // backward-compatible aliases (harmless for new frontend)
                $rowIt['title'] = $text;
                $rowIt['link']  = $url;
                $rowIt['href']  = $url;

                $out[] = $rowIt;
            }
            $arr['notice_items_json'] = $out;
        } else {
            $arr['notice_items_json'] = null;
        }

        // decode metadata
        $meta = $arr['metadata'] ?? null;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // cast booleans (handy for frontend)
        foreach (['auto_scroll','loop','pause_on_hover'] as $k) {
            if (array_key_exists($k, $arr) && $arr[$k] !== null) {
                $arr[$k] = (int) ((bool) $arr[$k]);
            }
        }

        // cast numeric
        foreach (['views_count','scroll_speed','scroll_latency_ms'] as $k) {
            if (array_key_exists($k, $arr) && $arr[$k] !== null) {
                $arr[$k] = (int) $arr[$k];
            }
        }

        // ✅ normalize status strictly to 0/1 for frontend (backward compatible)
        if (array_key_exists('status', $arr) && $arr['status'] !== null) {
            $raw = $arr['status'];

            if (is_bool($raw)) {
                $arr['status'] = $raw ? 1 : 0;
            } elseif (is_numeric($raw)) {
                $arr['status'] = ((int)$raw) === 1 ? 1 : 0;
            } else {
                $s = strtolower(trim((string)$raw));
                if (in_array($s, ['1','published','active','enabled','true','yes','on'], true)) {
                    $arr['status'] = 1;
                } elseif (in_array($s, ['0','draft','archived','inactive','disabled','false','no','off'], true)) {
                    $arr['status'] = 0;
                } else {
                    $arr['status'] = 0;
                }
            }
        }

        return $arr;
    }

    protected function normalizeItemsInput(Request $request): ?array
    {
        // preferred: notice_items_json
        $items = $request->input('notice_items_json',
            $request->input('notice_items',
                $request->input('notices_json', null) // backward compatible
            )
        );

        if (is_string($items)) {
            $decoded = json_decode($items, true);
            if (json_last_error() === JSON_ERROR_NONE) $items = $decoded;
        }

        if (!is_array($items)) return null;

        // ✅ normalize each item to {text, url} (title removed)
        $normalized = [];
        foreach ($items as $it) {
            if (!is_array($it)) continue;

            $text = trim((string)($it['text'] ?? $it['title'] ?? $it['label'] ?? $it['name'] ?? $it['message'] ?? ''));
            $url  = trim((string)($it['url'] ?? $it['link'] ?? $it['href'] ?? ''));

            if ($text === '' && $url === '') continue;

            $row = ['text' => $text, 'url' => $url];

            // keep sort_order if passed
            if (array_key_exists('sort_order', $it)) $row['sort_order'] = $it['sort_order'];

            $normalized[] = $row;
        }

        return $normalized;
    }

    protected function normalizeMetadataInput(Request $request)
    {
        $metadata = $request->input('metadata', null);

        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
        }

        return $metadata; // array/object/null
    }

    protected function safeSlug(?string $slug): string
    {
        $slug = trim((string) $slug);
        $base = Str::slug($slug, '-');

        if ($base === '') $base = 'notice-marquee';

        // keep under 160 chars
        $base = substr($base, 0, 160);

        // must be unique across whole table (unique index)
        $final = $base;
        $exists = DB::table('notice_marquee')->where('slug', $final)->exists();
        if (! $exists) return $final;

        // append random suffix
        $suffix = '-' . Str::lower(Str::random(6));
        $maxBaseLen = 160 - strlen($suffix);
        $final = substr($base, 0, max(1, $maxBaseLen)) . $suffix;

        while (DB::table('notice_marquee')->where('slug', $final)->exists()) {
            $suffix = '-' . Str::lower(Str::random(6));
            $maxBaseLen = 160 - strlen($suffix);
            $final = substr($base, 0, max(1, $maxBaseLen)) . $suffix;
        }

        return $final;
    }

    protected function visibleNowQuery()
    {
        $now = now();

        return DB::table('notice_marquee')
            ->whereNull('deleted_at')
            ->where(function ($w) {
                $w->where('status', '1')
                  ->orWhere('status', 1)
                  ->orWhere('status', 'published'); // legacy support
            })
            ->where(function ($w) use ($now) {
                $w->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
            })
            ->where(function ($w) use ($now) {
                $w->whereNull('expire_at')->orWhere('expire_at', '>', $now);
            });
    }

    /* ============================================
     | CRUD
     |============================================ */

    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $q = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        if ($onlyDeleted) $q->whereNotNull('m.deleted_at');

        $p = $q->paginate($perPage);

        $items = array_map(function ($r) {
            return $this->normalizeRow($r);
        }, $p->items());

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

    public function trash(Request $request)
    {
        $request->query->set('only_trashed', '1');
        return $this->index($request);
    }

    public function current(Request $request)
    {
        $row = $this->visibleNowQuery()
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        $item = $row ? $this->normalizeRow($row) : null;

        return response()->json([
            'success' => true,
            'item' => $item,             // keep old
            'data' => $item,             // ✅ add consistent key
            'notice_marquee' => $item,   // ✅ convenient alias for frontend
        ]);
    }

    public function show(Request $request, $identifier)
    {
        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveRow($identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Notice marquee not found'], 404);

        return response()->json([
            'success' => true,
            'item' => $this->normalizeRow($row),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->actor($request);

        try {
            $validated = $request->validate([
                'slug'              => ['nullable', 'string', 'max:160'],
                'auto_scroll'       => ['nullable', 'in:0,1', 'boolean'],
                'scroll_speed'      => ['nullable', 'integer', 'min:1', 'max:10000'],
                'scroll_latency_ms' => ['nullable', 'integer', 'min:0', 'max:600000'],
                'loop'              => ['nullable', 'in:0,1', 'boolean'],
                'pause_on_hover'    => ['nullable', 'in:0,1', 'boolean'],
                'direction'         => ['nullable', 'string', 'max:10', 'in:left,right'],
                'status'            => ['nullable', 'in:0,1', 'boolean'],
                'publish_at'        => ['nullable', 'date'],
                'expire_at'         => ['nullable', 'date'],
                'metadata'          => ['nullable'],
            ]);

            $items = $this->normalizeItemsInput($request);
            if (! is_array($items) || count($items) < 1) {
                $this->logActivity(
                    $request,
                    'create',
                    'notice_marquee',
                    'notice_marquee',
                    null,
                    ['notice_items_json'],
                    null,
                    ['notice_items_json' => $request->input('notice_items_json')],
                    'failed: notice_items_json missing/empty'
                );

                return response()->json([
                    'success' => false,
                    'message' => 'notice_items_json is required and must be a non-empty array',
                ], 422);
            }

            foreach ($items as $i => $it) {
                if (! is_array($it)) {
                    $this->logActivity(
                        $request,
                        'create',
                        'notice_marquee',
                        'notice_marquee',
                        null,
                        ['notice_items_json'],
                        null,
                        ['notice_items_json' => $items],
                        'failed: item not object at index ' . $i
                    );

                    return response()->json(['success'=>false,'message'=>"Item #".($i+1)." must be an object"], 422);
                }

                $text = trim((string) ($it['text'] ?? ''));
                $url  = trim((string) ($it['url'] ?? ''));

                // ✅ text is required (title removed)
                if ($text === '' || strlen($text) > 200) {
                    $this->logActivity(
                        $request,
                        'create',
                        'notice_marquee',
                        'notice_marquee',
                        null,
                        ['notice_items_json'],
                        null,
                        ['notice_items_json' => $items],
                        'failed: invalid text at item #' . ($i + 1)
                    );

                    return response()->json(['success'=>false,'message'=>"Item #".($i+1)." text is required (max 200)"], 422);
                }

                // ✅ url optional (only validate when present)
                if ($url !== '' && strlen($url) > 500) {
                    $this->logActivity(
                        $request,
                        'create',
                        'notice_marquee',
                        'notice_marquee',
                        null,
                        ['notice_items_json'],
                        null,
                        ['notice_items_json' => $items],
                        'failed: invalid url at item #' . ($i + 1)
                    );

                    return response()->json(['success'=>false,'message'=>"Item #".($i+1)." url is max 500 characters"], 422);
                }
            }

            $uuid = (string) Str::uuid();
            $now  = now();

            // ✅ don't depend on any "title" field anymore
            $fallbackSlugSource = $validated['slug'] ?? ($items[0]['text'] ?? 'notice-marquee');
            $slug = $this->safeSlug($fallbackSlugSource);

            $metadata = $this->normalizeMetadataInput($request);

            $insertData = [
                'uuid'              => $uuid,
                'slug'              => $slug,
                'notice_items_json' => json_encode($items),

                'auto_scroll'       => array_key_exists('auto_scroll', $validated) ? (int) $validated['auto_scroll'] : 1,
                'scroll_speed'      => array_key_exists('scroll_speed', $validated) ? (int) $validated['scroll_speed'] : 60,
                'scroll_latency_ms' => array_key_exists('scroll_latency_ms', $validated) ? (int) $validated['scroll_latency_ms'] : 0,
                'loop'              => array_key_exists('loop', $validated) ? (int) $validated['loop'] : 1,
                'pause_on_hover'    => array_key_exists('pause_on_hover', $validated) ? (int) $validated['pause_on_hover'] : 1,
                'direction'         => array_key_exists('direction', $validated) && $validated['direction'] !== null
                                        ? (string) $validated['direction'] : 'left',

                // ✅ always store "0"/"1"
                'status'            => (array_key_exists('status', $validated) && $validated['status'] !== null)
                                        ? (string) ((int) $validated['status'])
                                        : '1',

                'publish_at'        => array_key_exists('publish_at', $validated) ? $validated['publish_at'] : null,
                'expire_at'         => array_key_exists('expire_at', $validated) ? $validated['expire_at'] : null,

                // keep DB consistent (admin UI removed views field)
                'views_count'       => 0,

                'created_by'        => $actor['id'] ?: null,
                'created_at'        => $now,
                'updated_at'        => $now,
                'created_at_ip'     => $request->ip(),
                'updated_at_ip'     => $request->ip(),
                'metadata'          => $metadata !== null ? json_encode($metadata) : null,
            ];

            $id = DB::table('notice_marquee')->insertGetId($insertData);

            $row = DB::table('notice_marquee')->where('id', (int) $id)->first();
            $newSnap = $row ? $this->normalizeRow($row) : null;

            $this->logActivity(
                $request,
                'create',
                'notice_marquee',
                'notice_marquee',
                (int) $id,
                array_keys($insertData),
                null,
                $newSnap,
                'created'
            );

            return response()->json([
                'success' => true,
                'item' => $row ? $this->normalizeRow($row) : null,
            ], 201);
        } catch (Throwable $e) {
            $this->logActivity(
                $request,
                'create',
                'notice_marquee',
                'notice_marquee',
                null,
                null,
                null,
                null,
                'error: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    public function update(Request $request, $identifier)
    {
        $row = $this->resolveRow($identifier, true);
        if (! $row) {
            $this->logActivity(
                $request,
                'update',
                'notice_marquee',
                'notice_marquee',
                null,
                null,
                null,
                ['identifier' => (string) $identifier],
                'failed: not found'
            );
            return response()->json(['message' => 'Notice marquee not found'], 404);
        }

        $beforeSnap = $this->normalizeRow($row);

        try {
            $validated = $request->validate([
                'slug'              => ['nullable', 'string', 'max:160'],
                'auto_scroll'       => ['nullable', 'in:0,1', 'boolean'],
                'scroll_speed'      => ['nullable', 'integer', 'min:1', 'max:10000'],
                'scroll_latency_ms' => ['nullable', 'integer', 'min:0', 'max:600000'],
                'loop'              => ['nullable', 'in:0,1', 'boolean'],
                'pause_on_hover'    => ['nullable', 'in:0,1', 'boolean'],
                'direction'         => ['nullable', 'string', 'max:10', 'in:left,right'],
                'status'            => ['nullable', 'in:0,1', 'boolean'],
                'publish_at'        => ['nullable', 'date'],
                'expire_at'         => ['nullable', 'date'],
                'metadata'          => ['nullable'],
            ]);

            $update = [
                'updated_at'    => now(),
                'updated_at_ip' => $request->ip(),
            ];

            if ($request->has('notice_items_json') || $request->has('notice_items') || $request->has('notices_json')) {
                $items = $this->normalizeItemsInput($request);
                if (! is_array($items) || count($items) < 1) {
                    $this->logActivity(
                        $request,
                        'update',
                        'notice_marquee',
                        'notice_marquee',
                        (int) $row->id,
                        ['notice_items_json'],
                        $beforeSnap,
                        ['notice_items_json' => $request->input('notice_items_json')],
                        'failed: notice_items_json empty'
                    );

                    return response()->json([
                        'success' => false,
                        'message' => 'notice_items_json must be a non-empty array',
                    ], 422);
                }

                foreach ($items as $i => $it) {
                    if (! is_array($it)) {
                        $this->logActivity(
                            $request,
                            'update',
                            'notice_marquee',
                            'notice_marquee',
                            (int) $row->id,
                            ['notice_items_json'],
                            $beforeSnap,
                            ['notice_items_json' => $items],
                            'failed: item not object at index ' . $i
                        );

                        return response()->json(['success'=>false,'message'=>"Item #".($i+1)." must be an object"], 422);
                    }

                    $text = trim((string) ($it['text'] ?? ''));
                    $url  = trim((string) ($it['url'] ?? ''));

                    // ✅ text required (title removed)
                    if ($text === '' || strlen($text) > 200) {
                        $this->logActivity(
                            $request,
                            'update',
                            'notice_marquee',
                            'notice_marquee',
                            (int) $row->id,
                            ['notice_items_json'],
                            $beforeSnap,
                            ['notice_items_json' => $items],
                            'failed: invalid text at item #' . ($i + 1)
                        );

                        return response()->json(['success'=>false,'message'=>"Item #".($i+1)." text is required (max 200)"], 422);
                    }

                    // ✅ url optional
                    if ($url !== '' && strlen($url) > 500) {
                        $this->logActivity(
                            $request,
                            'update',
                            'notice_marquee',
                            'notice_marquee',
                            (int) $row->id,
                            ['notice_items_json'],
                            $beforeSnap,
                            ['notice_items_json' => $items],
                            'failed: invalid url at item #' . ($i + 1)
                        );

                        return response()->json(['success'=>false,'message'=>"Item #".($i+1)." url is max 500 characters"], 422);
                    }
                }

                $update['notice_items_json'] = json_encode($items);
            }

            if (array_key_exists('slug', $validated) && $validated['slug'] !== null) {
                $newSlug = Str::slug((string) $validated['slug'], '-');
                $newSlug = substr($newSlug ?: 'notice-marquee', 0, 160);

                $slugTaken = DB::table('notice_marquee')
                    ->where('slug', $newSlug)
                    ->where('id', '!=', (int) $row->id)
                    ->exists();

                if ($slugTaken) {
                    $this->logActivity(
                        $request,
                        'update',
                        'notice_marquee',
                        'notice_marquee',
                        (int) $row->id,
                        ['slug'],
                        $beforeSnap,
                        ['slug' => $newSlug],
                        'failed: slug already exists'
                    );

                    return response()->json([
                        'success' => false,
                        'message' => 'Slug already exists. Please choose a different slug.',
                    ], 422);
                }

                $update['slug'] = $newSlug;
            }

            foreach (['auto_scroll','loop','pause_on_hover'] as $k) {
                if (array_key_exists($k, $validated)) {
                    $update[$k] = $validated[$k] !== null ? (int) $validated[$k] : null;
                }
            }

            if (array_key_exists('scroll_speed', $validated)) {
                $update['scroll_speed'] = $validated['scroll_speed'] !== null ? (int) $validated['scroll_speed'] : null;
            }
            if (array_key_exists('scroll_latency_ms', $validated)) {
                $update['scroll_latency_ms'] = $validated['scroll_latency_ms'] !== null ? (int) $validated['scroll_latency_ms'] : null;
            }

            if (array_key_exists('direction', $validated)) {
                $update['direction'] = $validated['direction'] !== null ? (string) $validated['direction'] : null;
            }

            // ✅ store status strictly as "0"/"1"
            if (array_key_exists('status', $validated) && $validated['status'] !== null) {
                $update['status'] = (string) ((int) $validated['status']);
            }

            if (array_key_exists('publish_at', $validated)) $update['publish_at'] = $validated['publish_at'];
            if (array_key_exists('expire_at', $validated))  $update['expire_at']  = $validated['expire_at'];

            if (array_key_exists('metadata', $validated)) {
                $metadata = $this->normalizeMetadataInput($request);
                $update['metadata'] = $metadata !== null ? json_encode($metadata) : null;
            }

            DB::table('notice_marquee')->where('id', (int) $row->id)->update($update);

            $fresh = DB::table('notice_marquee')->where('id', (int) $row->id)->first();
            $afterSnap = $fresh ? $this->normalizeRow($fresh) : [];

            [$changed, $oldVals, $newVals] = $this->diffSnapshots(
                $beforeSnap,
                $afterSnap,
                ['updated_at', 'updated_at_ip'] // ignore noisy fields
            );

            $this->logActivity(
                $request,
                'update',
                'notice_marquee',
                'notice_marquee',
                (int) $row->id,
                $changed,
                $oldVals,
                $newVals,
                'updated'
            );

            return response()->json([
                'success' => true,
                'item' => $fresh ? $this->normalizeRow($fresh) : null,
            ]);
        } catch (Throwable $e) {
            $this->logActivity(
                $request,
                'update',
                'notice_marquee',
                'notice_marquee',
                (int) $row->id,
                null,
                $beforeSnap,
                null,
                'error: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveRow($identifier, false);
        if (! $row) {
            $this->logActivity(
                $request,
                'delete',
                'notice_marquee',
                'notice_marquee',
                null,
                null,
                null,
                ['identifier' => (string) $identifier],
                'failed: not found or already deleted'
            );
            return response()->json(['message' => 'Not found or already deleted'], 404);
        }

        $beforeSnap = $this->normalizeRow($row);

        try {
            DB::table('notice_marquee')->where('id', (int) $row->id)->update([
                'deleted_at'    => now(),
                'updated_at'    => now(),
                'updated_at_ip' => $request->ip(),
            ]);

            $fresh = DB::table('notice_marquee')->where('id', (int) $row->id)->first();
            $afterSnap = $fresh ? $this->normalizeRow($fresh) : [];

            [$changed, $oldVals, $newVals] = $this->diffSnapshots(
                $beforeSnap,
                $afterSnap,
                ['updated_at', 'updated_at_ip']
            );

            $this->logActivity(
                $request,
                'delete',
                'notice_marquee',
                'notice_marquee',
                (int) $row->id,
                $changed,
                $oldVals,
                $newVals,
                'soft deleted'
            );

            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            $this->logActivity(
                $request,
                'delete',
                'notice_marquee',
                'notice_marquee',
                (int) $row->id,
                null,
                $beforeSnap,
                null,
                'error: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveRow($identifier, true);
        if (! $row || $row->deleted_at === null) {
            $this->logActivity(
                $request,
                'restore',
                'notice_marquee',
                'notice_marquee',
                $row ? (int) $row->id : null,
                null,
                $row ? $this->normalizeRow($row) : null,
                ['identifier' => (string) $identifier],
                'failed: not found in bin'
            );
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $beforeSnap = $this->normalizeRow($row);

        try {
            DB::table('notice_marquee')->where('id', (int) $row->id)->update([
                'deleted_at'    => null,
                'updated_at'    => now(),
                'updated_at_ip' => $request->ip(),
            ]);

            $fresh = DB::table('notice_marquee')->where('id', (int) $row->id)->first();
            $afterSnap = $fresh ? $this->normalizeRow($fresh) : [];

            [$changed, $oldVals, $newVals] = $this->diffSnapshots(
                $beforeSnap,
                $afterSnap,
                ['updated_at', 'updated_at_ip']
            );

            $this->logActivity(
                $request,
                'restore',
                'notice_marquee',
                'notice_marquee',
                (int) $row->id,
                $changed,
                $oldVals,
                $newVals,
                'restored'
            );

            return response()->json([
                'success' => true,
                'item' => $fresh ? $this->normalizeRow($fresh) : null,
            ]);
        } catch (Throwable $e) {
            $this->logActivity(
                $request,
                'restore',
                'notice_marquee',
                'notice_marquee',
                (int) $row->id,
                null,
                $beforeSnap,
                null,
                'error: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveRow($identifier, true);
        if (! $row) {
            $this->logActivity(
                $request,
                'force_delete',
                'notice_marquee',
                'notice_marquee',
                null,
                null,
                null,
                ['identifier' => (string) $identifier],
                'failed: not found'
            );
            return response()->json(['message' => 'Notice marquee not found'], 404);
        }

        $beforeSnap = $this->normalizeRow($row);

        try {
            DB::table('notice_marquee')->where('id', (int) $row->id)->delete();

            $this->logActivity(
                $request,
                'force_delete',
                'notice_marquee',
                'notice_marquee',
                (int) $row->id,
                ['__deleted__'],
                $beforeSnap,
                null,
                'force deleted'
            );

            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            $this->logActivity(
                $request,
                'force_delete',
                'notice_marquee',
                'notice_marquee',
                (int) $row->id,
                null,
                $beforeSnap,
                null,
                'error: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    public function incrementViews(Request $request, $identifier)
    {
        $row = $this->resolveRow($identifier, false);
        if (! $row) {
            $this->logActivity(
                $request,
                'increment_views',
                'notice_marquee',
                'notice_marquee',
                null,
                null,
                null,
                ['identifier' => (string) $identifier],
                'failed: not found'
            );
            return response()->json(['message' => 'Not found'], 404);
        }

        $beforeSnap = $this->normalizeRow($row);

        try {
            DB::table('notice_marquee')->where('id', (int) $row->id)->increment('views_count', 1);

            $fresh = DB::table('notice_marquee')->where('id', (int) $row->id)->first();
            $afterSnap = $fresh ? $this->normalizeRow($fresh) : [];

            [$changed, $oldVals, $newVals] = $this->diffSnapshots(
                $beforeSnap,
                $afterSnap,
                ['updated_at', 'updated_at_ip']
            );

            // If only views_count changed, keep it clean
            if (!empty($changed) && count($changed) > 1) {
                // fine as-is; diffSnapshots already filtered noisy keys
            }

            $this->logActivity(
                $request,
                'increment_views',
                'notice_marquee',
                'notice_marquee',
                (int) $row->id,
                $changed,
                $oldVals,
                $newVals,
                'views incremented'
            );

            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            $this->logActivity(
                $request,
                'increment_views',
                'notice_marquee',
                'notice_marquee',
                (int) $row->id,
                null,
                $beforeSnap,
                null,
                'error: ' . $e->getMessage()
            );
            throw $e;
        }
    }
}
