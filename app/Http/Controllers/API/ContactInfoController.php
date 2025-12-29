<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContactInfoController extends Controller
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

    protected function toUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') return null;

        if (preg_match('~^https?://~i', $path)) return $path;

        // allow protocol-relative like //example.com
        if (str_starts_with($path, '//')) return 'https:' . $path;

        return url('/' . ltrim($path, '/'));
    }

    protected function actionUrlFor(string $key, string $value): ?string
    {
        $k = strtolower(trim($key));
        $v = trim($value);
        if ($v === '') return null;

        // email
        if (in_array($k, ['email', 'mail'], true)) {
            return 'mailto:' . $v;
        }

        // phone
        if (in_array($k, ['phone', 'mobile', 'tel', 'telephone'], true)) {
            $clean = preg_replace('~\s+~', '', $v);
            return 'tel:' . $clean;
        }

        // whatsapp
        if ($k === 'whatsapp') {
            $digits = preg_replace('~\D+~', '', $v);
            $digits = ltrim($digits, '0');
            if ($digits === '') return null;
            return 'https://wa.me/' . $digits;
        }

        // address -> google maps
        if (in_array($k, ['address', 'location', 'map'], true)) {
            return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($v);
        }

        // social / website links
        if (
            in_array($k, ['website', 'site', 'url', 'linkedin', 'facebook', 'instagram', 'twitter', 'x', 'youtube'], true)
            || preg_match('~^https?://~i', $v)
            || str_starts_with($v, '/')
            || str_starts_with($v, '//')
        ) {
            return $this->toUrl($v);
        }

        return null;
    }

    protected function normalizeRow($row): array
    {
        $arr = (array) $row;

        // metadata decode
        $meta = $arr['metadata'] ?? null;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // computed
        $arr['action_url'] = $this->actionUrlFor((string)($arr['key'] ?? ''), (string)($arr['value'] ?? ''));

        return $arr;
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('contact_info as c')->select('c.*');

        if (! $includeDeleted) {
            $q->whereNull('c.deleted_at');
        }

        // ?q=
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('c.name', 'like', $term)
                    ->orWhere('c.key', 'like', $term)
                    ->orWhere('c.value', 'like', $term)
                    ->orWhere('c.type', 'like', $term);
            });
        }

        // ?type=contact|social
        if ($request->filled('type')) {
            $q->where('c.type', (string) $request->query('type'));
        }

        // ?key=email|phone|whatsapp|address|website|linkedin|etc
        if ($request->filled('key')) {
            $q->where('c.key', (string) $request->query('key'));
        }

        // ?status=active|inactive
        if ($request->filled('status')) {
            $q->where('c.status', (string) $request->query('status'));
        }

        // ?featured=1/0
        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) {
                $q->where('c.is_featured_home', $featured ? 1 : 0);
            }
        }

        // sort
        $sort = (string) $request->query('sort', 'sort_order');
        $dir  = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowed = ['sort_order', 'created_at', 'updated_at', 'name', 'key', 'type', 'status', 'id'];
        if (! in_array($sort, $allowed, true)) $sort = 'sort_order';

        $q->orderBy('c.' . $sort, $dir);

        // stable secondary sort
        if ($sort !== 'id') $q->orderBy('c.id', 'desc');

        return $q;
    }

    protected function resolveContactInfo($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('contact_info as c');
        if (! $includeDeleted) $q->whereNull('c.deleted_at');

        if (ctype_digit((string) $identifier)) {
            $q->where('c.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('c.uuid', (string) $identifier);
        } else {
            // not supported identifier
            return null;
        }

        return $q->first();
    }

    /* ============================================
     | CRUD (Admin)
     |============================================ */

    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        if ($onlyDeleted) {
            $query->whereNotNull('c.deleted_at');
        }

        $paginator = $query->paginate($perPage);
        $items = array_map(fn($r) => $this->normalizeRow($r), $paginator->items());

        return response()->json([
            'data' => $items,
            'pagination' => [
                'page'      => $paginator->currentPage(),
                'per_page'  => $paginator->perPage(),
                'total'     => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function trash(Request $request)
    {
        $request->query->set('only_trashed', '1');
        return $this->index($request);
    }

    public function show(Request $request, $identifier)
    {
        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveContactInfo($identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Contact info not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'type'             => ['nullable', 'in:contact,social'],
            'key'              => ['required', 'string', 'max:60'],
            'name'             => ['required', 'string', 'max:120'],
            'icon_class'       => ['nullable', 'string', 'max:120'],
            'value'            => ['required', 'string', 'max:255'],
            'is_featured_home' => ['nullable', 'in:0,1', 'boolean'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
            'status'           => ['nullable', 'in:active,inactive'],
            'metadata'         => ['nullable'],
        ]);

        // metadata normalize
        $metadata = $request->input('metadata', null);
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
        }

        $uuid = (string) Str::uuid();
        $now  = now();

        $id = DB::table('contact_info')->insertGetId([
            'uuid'             => $uuid,
            'type'             => (string) ($validated['type'] ?? 'contact'),
            'key'              => $validated['key'],
            'name'             => $validated['name'],
            'icon_class'       => $validated['icon_class'] ?? null,
            'value'            => $validated['value'],
            'is_featured_home' => (int) ($validated['is_featured_home'] ?? 0),
            'sort_order'       => (int) ($validated['sort_order'] ?? 0),
            'status'           => (string) ($validated['status'] ?? 'active'),
            'created_by'       => $actor['id'] ?: null,
            'created_at'       => $now,
            'updated_at'       => $now,
            'created_at_ip'    => $request->ip(),
            'updated_at_ip'    => $request->ip(),
            'metadata'         => $metadata !== null ? json_encode($metadata) : null,
        ]);

        $row = DB::table('contact_info')->where('id', (int) $id)->first();

        return response()->json([
            'success' => true,
            'data'    => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    public function update(Request $request, $identifier)
    {
        $row = $this->resolveContactInfo($identifier, true);
        if (! $row) return response()->json(['message' => 'Contact info not found'], 404);

        $validated = $request->validate([
            'type'             => ['nullable', 'in:contact,social'],
            'key'              => ['nullable', 'string', 'max:60'],
            'name'             => ['nullable', 'string', 'max:120'],
            'icon_class'       => ['nullable', 'string', 'max:120'],
            'value'            => ['nullable', 'string', 'max:255'],
            'is_featured_home' => ['nullable', 'in:0,1', 'boolean'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
            'status'           => ['nullable', 'in:active,inactive'],
            'metadata'         => ['nullable'],
        ]);

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        foreach (['type','key','name','icon_class','value','status'] as $k) {
            if (array_key_exists($k, $validated)) $update[$k] = $validated[$k];
        }

        if (array_key_exists('is_featured_home', $validated)) {
            $update['is_featured_home'] = (int) $validated['is_featured_home'];
        }

        if (array_key_exists('sort_order', $validated)) {
            $update['sort_order'] = (int) $validated['sort_order'];
        }

        if (array_key_exists('metadata', $validated)) {
            $metadata = $request->input('metadata', null);
            if (is_string($metadata)) {
                $decoded = json_decode($metadata, true);
                if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
            }
            $update['metadata'] = $metadata !== null ? json_encode($metadata) : null;
        }

        DB::table('contact_info')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('contact_info')->where('id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function toggleFeatured(Request $request, $identifier)
    {
        $row = $this->resolveContactInfo($identifier, true);
        if (! $row) return response()->json(['message' => 'Contact info not found'], 404);

        $new = ((int) ($row->is_featured_home ?? 0)) ? 0 : 1;

        DB::table('contact_info')->where('id', (int) $row->id)->update([
            'is_featured_home' => $new,
            'updated_at'       => now(),
            'updated_at_ip'    => $request->ip(),
        ]);

        $fresh = DB::table('contact_info')->where('id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveContactInfo($identifier, false);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        DB::table('contact_info')->where('id', (int) $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveContactInfo($identifier, true);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        DB::table('contact_info')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('contact_info')->where('id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveContactInfo($identifier, true);
        if (! $row) return response()->json(['message' => 'Contact info not found'], 404);

        DB::table('contact_info')->where('id', (int) $row->id)->delete();

        return response()->json(['success' => true]);
    }

    /* ============================================
     | Public (No Auth)
     |============================================ */

    public function publicIndex(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 50)));

        // public defaults: active + not deleted
        $request->query->set('status', $request->query('status', 'active'));

        $q = $this->baseQuery($request, false);

        // public default sort: featured first, then sort_order asc
        $q->orderBy('c.is_featured_home', 'desc')
          ->orderBy('c.sort_order', 'asc')
          ->orderBy('c.id', 'desc');

        $paginator = $q->paginate($perPage);
        $items = array_map(fn($r) => $this->normalizeRow($r), $paginator->items());

        return response()->json([
            'success' => true,
            'data'    => $items,
            'pagination' => [
                'page'      => $paginator->currentPage(),
                'per_page'  => $paginator->perPage(),
                'total'     => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function publicShow(Request $request, $identifier)
    {
        $row = $this->resolveContactInfo($identifier, false);
        if (! $row) return response()->json(['message' => 'Contact info not found'], 404);

        if (($row->status ?? '') !== 'active') {
            return response()->json(['message' => 'Contact info not available'], 404);
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }
}
