<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Throwable;

class HeroCarouselController extends Controller
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
     * Safe activity logger (never breaks the main request).
     */
    private function logActivity(
        Request $r,
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
            $actor = $this->actor($r);

            $payload = [
                'performed_by'      => $actor['id'] ?: 0,
                'performed_by_role' => ($actor['role'] ?? '') !== '' ? (string) $actor['role'] : null,
                'ip'                => $r->ip(),
                'user_agent'        => substr((string) $r->userAgent(), 0, 512) ?: null,

                'activity'          => $activity,
                'module'            => $module,

                'table_name'        => $tableName,
                'record_id'         => $recordId,

                'changed_fields'    => $changedFields !== null ? json_encode(array_values($changedFields), JSON_UNESCAPED_UNICODE) : null,
                'old_values'        => $oldValues !== null ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values'        => $newValues !== null ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,

                'log_note'          => $note,

                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            DB::table('user_data_activity_log')->insert($payload);
        } catch (Throwable $e) {
            // Intentionally swallow logging errors to avoid hurting functionality.
        }
    }

    private function decodeMaybeJson($v)
    {
        if ($v === null) return null;
        if (is_array($v)) return $v;

        if (is_string($v)) {
            $trim = trim($v);
            if ($trim === '') return $v;

            $decoded = json_decode($v, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $v;
        }

        return $v;
    }

    private function rowSnapshot($row): array
    {
        if (!$row) return [];

        $arr = (array) $row;

        if (array_key_exists('metadata', $arr)) {
            $arr['metadata'] = $this->decodeMaybeJson($arr['metadata']);
        }

        return $arr;
    }

    private function diffRows($oldRow, $newRow, array $keys): array
    {
        $changed = [];
        $old = [];
        $new = [];

        foreach ($keys as $k) {
            $ov = $oldRow ? ($oldRow->$k ?? null) : null;
            $nv = $newRow ? ($newRow->$k ?? null) : null;

            if ($k === 'metadata') {
                $ov = $this->decodeMaybeJson($ov);
                $nv = $this->decodeMaybeJson($nv);
            }

            // Normalize DateTime-like values
            if ($ov instanceof \DateTimeInterface) $ov = $ov->format('Y-m-d H:i:s');
            if ($nv instanceof \DateTimeInterface) $nv = $nv->format('Y-m-d H:i:s');

            // Loose compare is fine for DB strings vs parsed values
            if ($ov != $nv) {
                $changed[] = $k;
                $old[$k] = $ov;
                $new[$k] = $nv;
            }
        }

        return [$changed, $old, $new];
    }

    private function normSlug(?string $s): string
    {
        $s = trim((string) $s);
        return $s === '' ? '' : Str::slug($s, '-');
    }

    protected function toUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') return null;
        if (preg_match('~^https?://~i', $path)) return $path;
        return url('/' . ltrim($path, '/'));
    }

    protected function normalizeRow($row): array
    {
        $arr = (array) $row;

        // urls
        $arr['image_url_full'] = $this->toUrl($arr['image_url'] ?? null);
        $arr['mobile_image_url_full'] = $this->toUrl($arr['mobile_image_url'] ?? null);

        // metadata decode
        $meta = $arr['metadata'] ?? null;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // basic casts (helpful for UI)
        foreach (['id','sort_order','views_count','created_by'] as $k) {
            if (array_key_exists($k, $arr) && $arr[$k] !== null) $arr[$k] = (int) $arr[$k];
        }

        return $arr;
    }

    protected function ensureUniqueSlug(string $slug, ?string $ignoreUuid = null): string
    {
        $base = $slug;
        $i = 2;

        while (
            DB::table('hero_carousel')
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
        // Read meta BEFORE move (prevents tmp stat errors)
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

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('hero_carousel as h')->select(['h.*']);

        if (!$includeDeleted) {
            $q->whereNull('h.deleted_at');
        }

        // ?q=
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('h.title', 'like', $term)
                    ->orWhere('h.slug', 'like', $term)
                    ->orWhere('h.overlay_text', 'like', $term)
                    ->orWhere('h.alt_text', 'like', $term);
            });
        }

        // ?status=draft|published|archived
        if ($request->filled('status')) {
            $q->where('h.status', (string) $request->query('status'));
        }

        // ?visible_now=1 -> published + within publish/expire window
        if ($request->has('visible_now')) {
            $visible = filter_var($request->query('visible_now'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($visible) {
                $now = now();
                $q->where('h.status', 'published')
                  ->where(function ($w) use ($now) {
                      $w->whereNull('h.publish_at')->orWhere('h.publish_at', '<=', $now);
                  })
                  ->where(function ($w) use ($now) {
                      $w->whereNull('h.expire_at')->orWhere('h.expire_at', '>', $now);
                  });
            }
        }

        // sort
        $sort = (string) $request->query('sort', 'sort_order');
        $dir  = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowed = ['sort_order','created_at','publish_at','expire_at','title','views_count','id','status'];
        if (!in_array($sort, $allowed, true)) $sort = 'sort_order';

        // common carousel ordering: sort_order asc, then id desc
        if ($sort === 'sort_order') {
            $q->orderBy('h.sort_order', $dir)->orderBy('h.id', 'desc');
        } else {
            $q->orderBy('h.' . $sort, $dir);
        }

        return $q;
    }

    protected function resolveHero($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('hero_carousel as h');
        if (!$includeDeleted) $q->whereNull('h.deleted_at');

        if (ctype_digit((string) $identifier)) {
            $q->where('h.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('h.uuid', (string) $identifier);
        } else {
            $q->where('h.slug', (string) $identifier);
        }

        return $q->first();
    }

    protected function applyVisibleWindow($q): void
    {
        $now = now();

        $q->whereNull('h.deleted_at')
          ->where('h.status', 'published')
          ->where(function ($w) use ($now) {
              $w->whereNull('h.publish_at')->orWhere('h.publish_at', '<=', $now);
          })
          ->where(function ($w) use ($now) {
              $w->whereNull('h.expire_at')->orWhere('h.expire_at', '>', $now);
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

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        if ($onlyDeleted) {
            $query->whereNotNull('h.deleted_at');
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

        $row = $this->resolveHero($identifier, $includeDeleted);
        if (!$row) return response()->json(['message' => 'Hero carousel item not found'], 404);

        // optional: ?inc_view=1
        if (filter_var($request->query('inc_view', false), FILTER_VALIDATE_BOOLEAN)) {
            DB::table('hero_carousel')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        return response()->json([
            'success' => true,
            'item' => $this->normalizeRow($row),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'title'            => ['nullable', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:160'],
            'overlay_text'     => ['nullable', 'string'],
            'alt_text'         => ['nullable', 'string', 'max:255'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
            'status'           => ['nullable', 'in:draft,published,archived'],
            'publish_at'       => ['nullable', 'date'],
            'expire_at'        => ['nullable', 'date'],
            'metadata'         => ['nullable'],

            // allow either file upload OR direct string paths
            'image_url'        => ['nullable', 'string', 'max:255'],
            'mobile_image_url' => ['nullable', 'string', 'max:255'],

            'desktop_image'    => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'mobile_image'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        // slug: prefer given slug, else from title, else random
        $slug = $this->normSlug($validated['slug'] ?? '');
        if ($slug === '') {
            $t = trim((string) ($validated['title'] ?? ''));
            $slug = $t !== '' ? Str::slug($t, '-') : ('hero-' . Str::random(8));
        }
        $slug = $this->ensureUniqueSlug($slug);

        $uuid = (string) Str::uuid();
        $now  = now();

        // metadata normalize
        $metadata = $request->input('metadata', null);
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
        }

        // uploads
        $dirRel = 'depy_uploads/hero_carousel';

        $imagePath = null;
        $mobilePath = null;

        // prefer file upload (desktop_image), else use image_url string
        if ($request->hasFile('desktop_image')) {
            $f = $request->file('desktop_image');
            if (!$f || !$f->isValid()) {
                $this->logActivity($request, 'create', 'hero_carousel', 'hero_carousel', null, null, null, null, 'Desktop image upload failed');
                return response()->json(['success' => false, 'message' => 'Desktop image upload failed'], 422);
            }
            $meta = $this->uploadFileToPublic($f, $dirRel, $slug . '-desktop');
            $imagePath = $meta['path'];
        } else {
            $imagePath = trim((string) ($validated['image_url'] ?? ''));
            $imagePath = $imagePath !== '' ? $imagePath : null;
        }

        // mobile file upload, else string
        if ($request->hasFile('mobile_image')) {
            $f = $request->file('mobile_image');
            if (!$f || !$f->isValid()) {
                $this->logActivity($request, 'create', 'hero_carousel', 'hero_carousel', null, null, null, null, 'Mobile image upload failed');
                return response()->json(['success' => false, 'message' => 'Mobile image upload failed'], 422);
            }
            $meta = $this->uploadFileToPublic($f, $dirRel, $slug . '-mobile');
            $mobilePath = $meta['path'];
        } else {
            $mobilePath = trim((string) ($validated['mobile_image_url'] ?? ''));
            $mobilePath = $mobilePath !== '' ? $mobilePath : null;
        }

        // schema: image_url is NOT NULL
        if ($imagePath === null) {
            $this->logActivity($request, 'create', 'hero_carousel', 'hero_carousel', null, ['image_url'], null, ['image_url' => null], 'image_url is required');
            return response()->json([
                'success' => false,
                'message' => 'image_url is required (provide image_url or desktop_image upload)'
            ], 422);
        }

        $insertData = [
            'uuid'            => $uuid,
            'title'           => $validated['title'] ?? null,
            'slug'            => $slug,
            'image_url'       => $imagePath,
            'mobile_image_url'=> $mobilePath,
            'overlay_text'    => $validated['overlay_text'] ?? null,
            'alt_text'        => $validated['alt_text'] ?? null,
            'sort_order'      => (int) ($validated['sort_order'] ?? 0),
            'status'          => (string) ($validated['status'] ?? 'draft'),
            'publish_at'      => !empty($validated['publish_at']) ? Carbon::parse($validated['publish_at']) : null,
            'expire_at'       => !empty($validated['expire_at']) ? Carbon::parse($validated['expire_at']) : null,
            'views_count'     => 0,
            'created_by'      => $actor['id'] ?: null,
            'created_at'      => $now,
            'updated_at'      => $now,
            'created_at_ip'   => $request->ip(),
            'updated_at_ip'   => $request->ip(),
            'metadata'        => $metadata !== null ? json_encode($metadata) : null,
        ];

        $id = DB::table('hero_carousel')->insertGetId($insertData);

        $row = DB::table('hero_carousel')->where('id', $id)->first();

        // LOG (POST)
        $logNew = $insertData;
        $logNew['id'] = (int) $id;
        // store decoded metadata in log for readability
        $logNew['metadata'] = $metadata;
        $this->logActivity(
            $request,
            'create',
            'hero_carousel',
            'hero_carousel',
            (int) $id,
            array_keys($logNew),
            null,
            $logNew,
            'Hero carousel item created'
        );

        return response()->json([
            'success' => true,
            'data'    => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    public function update(Request $request, $identifier)
    {
        $row = $this->resolveHero($identifier, true);
        if (!$row) {
            $this->logActivity($request, 'update', 'hero_carousel', 'hero_carousel', null, null, null, null, 'Update failed: item not found');
            return response()->json(['message' => 'Hero carousel item not found'], 404);
        }

        $validated = $request->validate([
            'title'                 => ['nullable', 'string', 'max:255'],
            'slug'                  => ['nullable', 'string', 'max:160'],
            'overlay_text'          => ['nullable', 'string'],
            'alt_text'              => ['nullable', 'string', 'max:255'],
            'sort_order'            => ['nullable', 'integer', 'min:0'],
            'status'                => ['nullable', 'in:draft,published,archived'],
            'publish_at'            => ['nullable', 'date'],
            'expire_at'             => ['nullable', 'date'],
            'metadata'              => ['nullable'],

            // allow either file upload OR direct string paths
            'image_url'             => ['nullable', 'string', 'max:255'],
            'mobile_image_url'      => ['nullable', 'string', 'max:255'],

            'desktop_image'         => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'mobile_image'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],

            'desktop_image_remove'  => ['nullable', 'in:0,1', 'boolean'],
            'mobile_image_remove'   => ['nullable', 'in:0,1', 'boolean'],
        ]);

        $before = $row; // snapshot object for diff

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        // normal fields
        foreach (['title','overlay_text','alt_text','status'] as $k) {
            if (array_key_exists($k, $validated)) $update[$k] = $validated[$k];
        }

        if (array_key_exists('sort_order', $validated)) {
            $update['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        }

        if (array_key_exists('publish_at', $validated)) {
            $update['publish_at'] = !empty($validated['publish_at']) ? Carbon::parse($validated['publish_at']) : null;
        }
        if (array_key_exists('expire_at', $validated)) {
            $update['expire_at'] = !empty($validated['expire_at']) ? Carbon::parse($validated['expire_at']) : null;
        }

        // slug unique (only if provided non-empty)
        if (array_key_exists('slug', $validated) && trim((string) $validated['slug']) !== '') {
            $slug = $this->normSlug($validated['slug']);
            if ($slug === '') $slug = (string) ($row->slug ?? '');
            $slug = $this->ensureUniqueSlug($slug, (string) $row->uuid);
            $update['slug'] = $slug;
        }

        // metadata normalize
        if (array_key_exists('metadata', $validated)) {
            $metadata = $request->input('metadata', null);
            if (is_string($metadata)) {
                $decoded = json_decode($metadata, true);
                if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
            }
            $update['metadata'] = $metadata !== null ? json_encode($metadata) : null;
        }

        $dirRel = 'depy_uploads/hero_carousel';
        $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'hero');

        // desktop remove
        if (filter_var($request->input('desktop_image_remove', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->deletePublicPath($row->image_url ?? null);
            // schema requires image_url NOT NULL, so only allow if a replacement is also provided
            $update['image_url'] = null;
        }

        // mobile remove
        if (filter_var($request->input('mobile_image_remove', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->deletePublicPath($row->mobile_image_url ?? null);
            $update['mobile_image_url'] = null;
        }

        // desktop replace by file upload
        if ($request->hasFile('desktop_image')) {
            $f = $request->file('desktop_image');
            if (!$f || !$f->isValid()) {
                $this->logActivity($request, 'update', 'hero_carousel', 'hero_carousel', (int) $row->id, null, null, null, 'Desktop image upload failed');
                return response()->json(['success' => false, 'message' => 'Desktop image upload failed'], 422);
            }
            $this->deletePublicPath($row->image_url ?? null);
            $meta = $this->uploadFileToPublic($f, $dirRel, $useSlug . '-desktop');
            $update['image_url'] = $meta['path'];
        } elseif (array_key_exists('image_url', $validated) && trim((string) $validated['image_url']) !== '') {
            // set from string path/url
            $update['image_url'] = trim((string) $validated['image_url']);
        }

        // mobile replace by file upload
        if ($request->hasFile('mobile_image')) {
            $f = $request->file('mobile_image');
            if (!$f || !$f->isValid()) {
                $this->logActivity($request, 'update', 'hero_carousel', 'hero_carousel', (int) $row->id, null, null, null, 'Mobile image upload failed');
                return response()->json(['success' => false, 'message' => 'Mobile image upload failed'], 422);
            }
            $this->deletePublicPath($row->mobile_image_url ?? null);
            $meta = $this->uploadFileToPublic($f, $dirRel, $useSlug . '-mobile');
            $update['mobile_image_url'] = $meta['path'];
        } elseif (array_key_exists('mobile_image_url', $validated)) {
            $v = trim((string) ($validated['mobile_image_url'] ?? ''));
            $update['mobile_image_url'] = $v !== '' ? $v : null;
        }

        // schema requires image_url NOT NULL
        $finalImageUrl = array_key_exists('image_url', $update) ? $update['image_url'] : ($row->image_url ?? null);
        if ($finalImageUrl === null || trim((string)$finalImageUrl) === '') {
            $this->logActivity(
                $request,
                'update',
                'hero_carousel',
                'hero_carousel',
                (int) $row->id,
                ['image_url'],
                ['image_url' => $row->image_url ?? null],
                ['image_url' => $finalImageUrl],
                'image_url cannot be null (schema requires it)'
            );
            return response()->json([
                'success' => false,
                'message' => 'image_url cannot be null (table schema requires it). Provide desktop_image or image_url.'
            ], 422);
        }

        DB::table('hero_carousel')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('hero_carousel')->where('id', (int) $row->id)->first();

        // LOG (PUT/PATCH)
        $keys = ['title','slug','image_url','mobile_image_url','overlay_text','alt_text','sort_order','status','publish_at','expire_at','metadata','deleted_at'];
        [$changed, $oldVals, $newVals] = $this->diffRows($before, $fresh, $keys);

        $this->logActivity(
            $request,
            'update',
            'hero_carousel',
            'hero_carousel',
            (int) $row->id,
            $changed,
            $oldVals,
            $newVals,
            empty($changed) ? 'Update called but no changes detected' : 'Hero carousel item updated'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveHero($identifier, false);
        if (!$row) {
            $this->logActivity($request, 'delete', 'hero_carousel', 'hero_carousel', null, null, null, null, 'Delete failed: not found or already deleted');
            return response()->json(['message' => 'Not found or already deleted'], 404);
        }

        $before = $row;

        DB::table('hero_carousel')->where('id', (int) $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('hero_carousel')->where('id', (int) $row->id)->first();

        // LOG (DELETE)
        $keys = ['deleted_at','status','title','slug','image_url','mobile_image_url','metadata'];
        [$changed, $oldVals, $newVals] = $this->diffRows($before, $fresh, $keys);

        $this->logActivity(
            $request,
            'delete',
            'hero_carousel',
            'hero_carousel',
            (int) $row->id,
            $changed,
            $oldVals,
            $newVals,
            'Hero carousel item soft-deleted'
        );

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveHero($identifier, true);
        if (!$row || $row->deleted_at === null) {
            $this->logActivity($request, 'restore', 'hero_carousel', 'hero_carousel', null, null, null, null, 'Restore failed: not found in bin');
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $before = $row;

        DB::table('hero_carousel')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('hero_carousel')->where('id', (int) $row->id)->first();

        // LOG (POST/PUT style action)
        $keys = ['deleted_at','status','title','slug','image_url','mobile_image_url','metadata'];
        [$changed, $oldVals, $newVals] = $this->diffRows($before, $fresh, $keys);

        $this->logActivity(
            $request,
            'restore',
            'hero_carousel',
            'hero_carousel',
            (int) $row->id,
            $changed,
            $oldVals,
            $newVals,
            'Hero carousel item restored'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveHero($identifier, true);
        if (!$row) {
            $this->logActivity($request, 'force_delete', 'hero_carousel', 'hero_carousel', null, null, null, null, 'Force delete failed: item not found');
            return response()->json(['message' => 'Hero carousel item not found'], 404);
        }

        $snapshot = $this->rowSnapshot($row);

        // delete files (if stored as local public paths)
        $this->deletePublicPath($row->image_url ?? null);
        $this->deletePublicPath($row->mobile_image_url ?? null);

        DB::table('hero_carousel')->where('id', (int) $row->id)->delete();

        // LOG (DELETE)
        $this->logActivity(
            $request,
            'force_delete',
            'hero_carousel',
            'hero_carousel',
            (int) $row->id,
            ['__force_delete'],
            $snapshot,
            null,
            'Hero carousel item permanently deleted'
        );

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $now = now();
        $ip  = $request->ip();

        $ids = array_map(fn($it) => (int) $it['id'], $validated['items']);
        $oldMap = DB::table('hero_carousel')->whereIn('id', $ids)->pluck('sort_order', 'id')->toArray();

        foreach ($validated['items'] as $it) {
            $id = (int) $it['id'];
            $newSort = (int) $it['sort_order'];
            $oldSort = array_key_exists($id, $oldMap) ? (int) $oldMap[$id] : null;

            DB::table('hero_carousel')
                ->where('id', $id)
                ->update([
                    'sort_order'     => $newSort,
                    'updated_at'     => $now,
                    'updated_at_ip'  => $ip,
                ]);

            // LOG (PATCH/POST style action)
            if ($oldSort !== $newSort) {
                $this->logActivity(
                    $request,
                    'reorder',
                    'hero_carousel',
                    'hero_carousel',
                    $id,
                    ['sort_order'],
                    ['sort_order' => $oldSort],
                    ['sort_order' => $newSort],
                    'Hero carousel sort_order updated'
                );
            } else {
                $this->logActivity(
                    $request,
                    'reorder',
                    'hero_carousel',
                    'hero_carousel',
                    $id,
                    [],
                    ['sort_order' => $oldSort],
                    ['sort_order' => $newSort],
                    'Reorder called but sort_order unchanged'
                );
            }
        }

        return response()->json(['success' => true]);
    }

    /* ============================================
     | Public (no auth)
     |============================================ */

    public function publicIndex(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 50)));

        $q = $this->baseQuery($request, true);
        $this->applyVisibleWindow($q);

        // public carousel ordering
        $q->orderBy('h.sort_order', 'asc')->orderBy('h.id', 'desc');

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
        $row = $this->resolveHero($identifier, false);
        if (!$row) return response()->json(['message' => 'Hero carousel item not found'], 404);

        $now = now();
        $isVisible =
            ($row->status === 'published') &&
            (empty($row->publish_at) || Carbon::parse($row->publish_at)->lte($now)) &&
            (empty($row->expire_at)  || Carbon::parse($row->expire_at)->gt($now));

        if (!$isVisible) {
            return response()->json(['message' => 'Hero carousel item not available'], 404);
        }

        // default public view increment (can disable with ?inc_view=0)
        $inc = $request->has('inc_view')
            ? filter_var($request->query('inc_view'), FILTER_VALIDATE_BOOLEAN)
            : true;

        if ($inc) {
            DB::table('hero_carousel')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }
}
