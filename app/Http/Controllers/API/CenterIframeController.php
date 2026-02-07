<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CenterIframeController extends Controller
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

    private function normalizeForLog($v)
    {
        if ($v instanceof \DateTimeInterface) {
            return $v->format('Y-m-d H:i:s');
        }

        if (is_array($v)) {
            $out = [];
            foreach ($v as $k => $val) $out[$k] = $this->normalizeForLog($val);
            return $out;
        }

        if (is_object($v)) {
            return $this->normalizeForLog((array) $v);
        }

        return $v;
    }

    private function safeJson($v): ?string
    {
        if ($v === null) return null;
        return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Write a row into user_data_activity_log
     * (wrapped in try/catch so it never breaks existing functionality)
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
        try {
            $a = $this->actor($request);

            DB::table('user_data_activity_log')->insert([
                'performed_by'       => (int) ($a['id'] ?: 0),
                'performed_by_role'  => $a['role'] !== '' ? $a['role'] : null,
                'ip'                 => $request->ip(),
                'user_agent'         => substr((string) $request->userAgent(), 0, 512),

                'activity'           => $activity,
                'module'             => $module,

                'table_name'         => $tableName,
                'record_id'          => $recordId,

                'changed_fields'     => $this->safeJson($this->normalizeForLog($changedFields)),
                'old_values'         => $this->safeJson($this->normalizeForLog($oldValues)),
                'new_values'         => $this->safeJson($this->normalizeForLog($newValues)),

                'log_note'           => $note,

                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (\Throwable $e) {
            // Never break the API flow because of log failure
            Log::warning('Activity log insert failed (center_iframes)', [
                'err' => $e->getMessage(),
            ]);
        }
    }

    protected function normalizeJsonInput($value)
    {
        if ($value === null) return null;

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        if (is_array($value) || is_object($value)) return $value;

        return null;
    }

    protected function normalizeRow($row): array
    {
        $arr = (array) $row;

        foreach (['buttons_json', 'metadata'] as $k) {
            if (array_key_exists($k, $arr) && is_string($arr[$k])) {
                $decoded = json_decode($arr[$k], true);
                $arr[$k] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
            }
        }

        return $arr;
    }

    protected function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $base = trim($base);
        $slug = Str::slug($base);

        if ($slug === '') $slug = Str::random(10);

        // enforce max 160
        $slug = Str::limit($slug, 160, '');

        $try = $slug;
        $i = 2;

        while (true) {
            $q = DB::table('center_iframes')->where('slug', $try);
            if ($ignoreId) $q->where('id', '!=', (int) $ignoreId);

            $exists = $q->exists();
            if (! $exists) return $try;

            $suffix = '-' . $i;
            $trimLen = 160 - strlen($suffix);
            $try = Str::limit($slug, max(1, $trimLen), '') . $suffix;
            $i++;
        }
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('center_iframes as c')
            ->leftJoin('users as u', 'u.id', '=', 'c.created_by')
            ->select([
                'c.*',
                'u.name as created_by_name',
                'u.email as created_by_email',
            ]);

        if (! $includeDeleted) {
            $q->whereNull('c.deleted_at');
        }

        // search ?q= (uuid/slug/title)
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($w) use ($term) {
                $w->where('c.uuid', 'like', $term)
                  ->orWhere('c.slug', 'like', $term)
                  ->orWhere('c.title', 'like', $term);
            });
        }

        // filter ?status=active|inactive
        if ($request->filled('status')) {
            $q->where('c.status', (string) $request->query('status'));
        }

        // sorting
        $sort = (string) $request->query('sort', 'updated_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['id','created_at','updated_at','title','status','publish_at','expire_at'];
        if (! in_array($sort, $allowed, true)) $sort = 'updated_at';

        $q->orderBy('c.' . $sort, $dir)->orderByDesc('c.id');

        return $q;
    }

    protected function resolveIframe($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('center_iframes as c');
        if (! $includeDeleted) $q->whereNull('c.deleted_at');

        if (ctype_digit((string) $identifier)) {
            $q->where('c.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('c.uuid', (string) $identifier);
        } else {
            // fallback: slug
            $q->where('c.slug', (string) $identifier);
        }

        return $q->first();
    }

    protected function visibleQuery()
    {
        $now = now();

        return DB::table('center_iframes')
            ->whereNull('deleted_at')
            ->where('status', 'active')
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

    // List (supports: ?q= ?status= ?per_page= ?with_trashed=1 ?only_trashed=1)
    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $q = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        if ($onlyDeleted) $q->whereNotNull('c.deleted_at');

        $p = $q->paginate($perPage);

        $items = array_map(fn ($r) => $this->normalizeRow($r), $p->items());

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

    // Show by id|uuid|slug
    public function show(Request $request, $identifier)
    {
        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveIframe($identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Center iframe not found'], 404);

        $full = $this->baseQuery(new Request(), true)->where('c.id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'item' => $full ? $this->normalizeRow($full) : $this->normalizeRow($row),
        ]);
    }

    // Create
    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'iframe_url'  => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:160'],
            'status'      => ['nullable', 'string', 'max:20', 'in:active,inactive'],
            'publish_at'  => ['nullable', 'date'],
            'expire_at'   => ['nullable', 'date', 'after_or_equal:publish_at'],
            'buttons_json'=> ['nullable'], // array or JSON string
            'metadata'    => ['nullable'], // array or JSON string
        ]);

        $uuid = (string) Str::uuid();
        $now  = now();

        $title = (string) $validated['title'];

        $slug = $validated['slug'] ?? null;
        $slug = $this->uniqueSlug($slug !== null && trim($slug) !== '' ? $slug : $title);

        $buttons  = $this->normalizeJsonInput($request->input('buttons_json', null));
        $metadata = $this->normalizeJsonInput($request->input('metadata', null));

        $publishAt = array_key_exists('publish_at', $validated) && $validated['publish_at']
            ? Carbon::parse($validated['publish_at']) : null;

        $expireAt = array_key_exists('expire_at', $validated) && $validated['expire_at']
            ? Carbon::parse($validated['expire_at']) : null;

        $payload = [
            'uuid'          => $uuid,
            'slug'          => $slug,
            'title'         => $title,
            'iframe_url'    => (string) $validated['iframe_url'],
            'buttons_json'  => $buttons !== null ? json_encode($buttons) : null,
            'status'        => array_key_exists('status', $validated) && $validated['status'] !== null
                                ? (string) $validated['status'] : 'active',
            'publish_at'    => $publishAt,
            'expire_at'     => $expireAt,

            'created_by'    => $actor['id'] ?: null,

            'created_at'    => $now,
            'updated_at'    => $now,
            'created_at_ip' => $request->ip(),
            'updated_at_ip' => $request->ip(),

            'metadata'      => $metadata !== null ? json_encode($metadata) : null,
        ];

        $id = DB::table('center_iframes')->insertGetId($payload);

        // Activity log (POST)
        $this->logActivity(
            $request,
            'create',
            'center_iframes',
            'center_iframes',
            (int) $id,
            array_keys($payload),
            null,
            array_merge(['id' => (int) $id], $this->normalizeRow((object) $payload)),
            'Center iframe created'
        );

        $row = $this->baseQuery(new Request(), true)->where('c.id', (int) $id)->first();

        return response()->json([
            'success' => true,
            'item' => $row ? $this->normalizeRow($row) : null,
        ], 201);
    }

    // Update by id|uuid|slug
    public function update(Request $request, $identifier)
    {
        $row = $this->resolveIframe($identifier, true);
        if (! $row) return response()->json(['message' => 'Center iframe not found'], 404);

        $beforeRow = DB::table('center_iframes')->where('id', (int) $row->id)->first();
        $before    = $beforeRow ? $this->normalizeRow($beforeRow) : null;

        $validated = $request->validate([
            'title'       => ['nullable', 'string', 'max:255'],
            'iframe_url'  => ['nullable', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:160'],
            'status'      => ['nullable', 'string', 'max:20', 'in:active,inactive'],
            'publish_at'  => ['nullable', 'date'],
            'expire_at'   => ['nullable', 'date', 'after_or_equal:publish_at'],
            'buttons_json'=> ['nullable'],
            'metadata'    => ['nullable'],
        ]);

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        if (array_key_exists('title', $validated))      $update['title'] = $validated['title'];
        if (array_key_exists('iframe_url', $validated)) $update['iframe_url'] = $validated['iframe_url'];
        if (array_key_exists('status', $validated))     $update['status'] = $validated['status'];

        if (array_key_exists('publish_at', $validated)) {
            $update['publish_at'] = $validated['publish_at'] ? Carbon::parse($validated['publish_at']) : null;
        }
        if (array_key_exists('expire_at', $validated)) {
            $update['expire_at'] = $validated['expire_at'] ? Carbon::parse($validated['expire_at']) : null;
        }

        // slug only changes if explicitly provided
        if (array_key_exists('slug', $validated)) {
            $slugIn = $validated['slug'];
            if ($slugIn === null || trim((string) $slugIn) === '') {
                // if they send empty, re-generate from current/new title
                $base = array_key_exists('title', $validated) && $validated['title']
                    ? (string) $validated['title']
                    : (string) $row->title;
                $update['slug'] = $this->uniqueSlug($base, (int) $row->id);
            } else {
                $update['slug'] = $this->uniqueSlug((string) $slugIn, (int) $row->id);
            }
        }

        if (array_key_exists('buttons_json', $validated)) {
            $buttons = $this->normalizeJsonInput($request->input('buttons_json', null));
            $update['buttons_json'] = $buttons !== null ? json_encode($buttons) : null;
        }

        if (array_key_exists('metadata', $validated)) {
            $metadata = $this->normalizeJsonInput($request->input('metadata', null));
            $update['metadata'] = $metadata !== null ? json_encode($metadata) : null;
        }

        DB::table('center_iframes')->where('id', (int) $row->id)->update($update);

        $afterRow = DB::table('center_iframes')->where('id', (int) $row->id)->first();
        $after    = $afterRow ? $this->normalizeRow($afterRow) : null;

        // Activity log (PUT/PATCH)
        $candidates = array_values(array_diff(array_keys($update), ['updated_at', 'updated_at_ip']));
        $changed = [];
        if (is_array($before) && is_array($after)) {
            foreach ($candidates as $k) {
                $ov = $before[$k] ?? null;
                $nv = $after[$k] ?? null;
                if (json_encode($ov) !== json_encode($nv)) $changed[] = $k;
            }
        } else {
            $changed = $candidates;
        }

        $oldSnap = is_array($before) ? array_intersect_key($before, array_flip($changed)) : $before;
        $newSnap = is_array($after)  ? array_intersect_key($after,  array_flip($changed)) : $after;

        $this->logActivity(
            $request,
            'update',
            'center_iframes',
            'center_iframes',
            (int) $row->id,
            $changed,
            $oldSnap,
            $newSnap,
            'Center iframe updated'
        );

        $fresh = $this->baseQuery(new Request(), true)->where('c.id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'item' => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    // Soft delete
    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveIframe($identifier, false);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        $beforeRow = DB::table('center_iframes')->where('id', (int) $row->id)->first();
        $before    = $beforeRow ? $this->normalizeRow($beforeRow) : null;

        DB::table('center_iframes')->where('id', (int) $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $afterRow = DB::table('center_iframes')->where('id', (int) $row->id)->first();
        $after    = $afterRow ? $this->normalizeRow($afterRow) : null;

        $this->logActivity(
            $request,
            'delete',
            'center_iframes',
            'center_iframes',
            (int) $row->id,
            ['deleted_at'],
            ['deleted_at' => $before['deleted_at'] ?? null],
            ['deleted_at' => $after['deleted_at'] ?? null],
            'Center iframe soft deleted'
        );

        return response()->json(['success' => true]);
    }

    // Restore
    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveIframe($identifier, true);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $beforeRow = DB::table('center_iframes')->where('id', (int) $row->id)->first();
        $before    = $beforeRow ? $this->normalizeRow($beforeRow) : null;

        DB::table('center_iframes')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $afterRow = DB::table('center_iframes')->where('id', (int) $row->id)->first();
        $after    = $afterRow ? $this->normalizeRow($afterRow) : null;

        $this->logActivity(
            $request,
            'restore',
            'center_iframes',
            'center_iframes',
            (int) $row->id,
            ['deleted_at'],
            ['deleted_at' => $before['deleted_at'] ?? null],
            ['deleted_at' => $after['deleted_at'] ?? null],
            'Center iframe restored'
        );

        $fresh = $this->baseQuery(new Request(), true)->where('c.id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'item' => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    // Hard delete
    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveIframe($identifier, true);
        if (! $row) return response()->json(['message' => 'Center iframe not found'], 404);

        $beforeRow = DB::table('center_iframes')->where('id', (int) $row->id)->first();
        $before    = $beforeRow ? $this->normalizeRow($beforeRow) : null;

        DB::table('center_iframes')->where('id', (int) $row->id)->delete();

        $this->logActivity(
            $request,
            'force_delete',
            'center_iframes',
            'center_iframes',
            (int) $row->id,
            null,
            $before,
            null,
            'Center iframe permanently deleted'
        );

        return response()->json(['success' => true]);
    }

    /* ============================================
     | Public (Website)
     |============================================ */

    // Visible list (status=active, publish_at ok, expire_at ok)
    public function publicIndex(Request $request)
    {
        $items = $this->visibleQuery()
            ->orderByDesc('publish_at')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        $data = $items->map(fn ($r) => $this->normalizeRow($r))->values();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    // Visible show by id|uuid|slug
    public function publicShow(Request $request, $identifier)
    {
        $q = $this->visibleQuery();

        if (ctype_digit((string) $identifier)) {
            $q->where('id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('uuid', (string) $identifier);
        } else {
            $q->where('slug', (string) $identifier);
        }

        $row = $q->first();
        if (! $row) return response()->json(['message' => 'Not found'], 404);

        return response()->json([
            'success' => true,
            'item' => $this->normalizeRow($row),
        ]);
    }
}
