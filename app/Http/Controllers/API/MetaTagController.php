<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MetaTagController extends Controller
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

    private function safeJson($v): ?string
    {
        if ($v === null) return null;
        try {
            return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Normalize link/path/url => canonical path like "/about-us" (no query, no trailing slash) */
    private function normalizePageLink(?string $raw): ?string
    {
        if ($raw === null) return null;

        $raw = trim($raw);
        if ($raw === '') return null;

        // if user mistakenly types "/http://..." remove leading slash
        if (Str::startsWith($raw, ['/http://', '/https://'])) {
            $raw = ltrim($raw, '/');
        }

        // full URL -> path only
        $path = parse_url($raw, PHP_URL_PATH);
        $path = $path ?: $raw;

        // strip query/hash if raw includes them
        $path = explode('?', $path, 2)[0];
        $path = explode('#', $path, 2)[0];

        $path = '/' . ltrim($path, '/');
        $path = rtrim($path, '/'); // canonical

        return $path === '' ? '/' : $path;
    }

    /**
     * Resolve pages.id by a link
     * IMPORTANT: pages table might NOT have page_link column in your DB.
     * So we only query columns that actually exist.
     */
    private function resolvePageIdByLink(?string $link): ?int
    {
        $link = $this->normalizePageLink($link);
        if (!$link) return null;
        if (!Schema::hasTable('pages')) return null;

        $hasPageLinkCol = Schema::hasColumn('pages', 'page_link');
        $hasPageUrlCol  = Schema::hasColumn('pages', 'page_url');
        $hasSlugCol     = Schema::hasColumn('pages', 'slug');

        if (!$hasPageLinkCol && !$hasPageUrlCol && !$hasSlugCol) {
            return null;
        }

        $candidates = array_values(array_unique([
            $link,
            ltrim($link, '/'),
            $link . '/',
            ltrim($link, '/') . '/',
        ]));

        $slug = trim(basename($link), '/');

        $q = DB::table('pages')->select('id');

        $added = false;

        if ($hasPageLinkCol) {
            $q->whereIn('page_link', $candidates);
            $added = true;
        }

        if ($hasPageUrlCol) {
            if ($added) $q->orWhereIn('page_url', $candidates);
            else { $q->whereIn('page_url', $candidates); $added = true; }
        }

        if ($hasSlugCol && $slug !== '') {
            if ($added) $q->orWhere('slug', $slug);
            else { $q->where('slug', $slug); $added = true; }
        }

        $id = $q->value('id');

        return $id ? (int) $id : null;
    }

    /**
     * Resolve scope for meta tags:
     * - If pages table has a match => use page_id (preferred) and keep meta_tags.page_link = ''
     * - If no match (static/non-pages URL) => store/scope by meta_tags.page_link with page_id = null
     *
     * Returns: [pageId|null, pageLinkScope|null]
     */
    private function resolveScopeFromRequest(Request $r): array
    {
        $hasPageIdCol = Schema::hasColumn('meta_tags', 'page_id') && Schema::hasTable('pages');

        $pageId = $r->filled('page_id') ? (int) $r->input('page_id') : null;

        $rawLink = $r->input('page_link')
            ?? $r->input('url')
            ?? $r->input('path');

        $link = $this->normalizePageLink(is_string($rawLink) ? $rawLink : null);

        // resolve link -> page_id when possible
        if ($hasPageIdCol && !$pageId && $link) {
            $pageId = $this->resolvePageIdByLink($link); // may be null (static page)
        }

        $pageLinkScope = $pageId ? null : $link;

        return [$pageId ?: null, $pageLinkScope ?: null];
    }

    /** Returns: [changed_fields[], old_values{}, new_values{}] */
    private function computeDiff(?array $before, ?array $after, ?array $onlyKeys = null): array
    {
        $before = $before ?? [];
        $after  = $after ?? [];

        $keys = $onlyKeys ?: array_values(array_unique(array_merge(array_keys($before), array_keys($after))));

        $changed = [];
        $oldOut  = [];
        $newOut  = [];

        foreach ($keys as $k) {
            $b = $before[$k] ?? null;
            $a = $after[$k] ?? null;

            $bCmp = is_scalar($b) || $b === null ? $b : json_encode($b);
            $aCmp = is_scalar($a) || $a === null ? $a : json_encode($a);

            if ($bCmp !== $aCmp) {
                $changed[]  = $k;
                $oldOut[$k] = $b;
                $newOut[$k] = $a;
            }
        }

        return [$changed, $oldOut, $newOut];
    }

    /** Best-effort insert into user_data_activity_log (never breaks main flow) */
    private function logActivity(
        Request $r,
        string $activity,
        string $module,
        string $tableName,
        ?int $recordId = null,
        ?array $changedFields = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $note = null
    ): void {
        try {
            if (!Schema::hasTable('user_data_activity_log')) return;

            $actor = $this->actor($r);
            $now   = now();

            DB::table('user_data_activity_log')->insert([
                'performed_by'      => (int) ($actor['id'] ?? 0),
                'performed_by_role' => ($actor['role'] ?? null) ?: null,
                'ip'                => $r->ip(),
                'user_agent'        => substr((string) ($r->userAgent() ?? ''), 0, 512),

                'activity'          => substr($activity, 0, 50),
                'module'            => substr($module, 0, 100),

                'table_name'        => substr($tableName, 0, 128),
                'record_id'         => $recordId !== null ? (int) $recordId : null,

                'changed_fields'    => $this->safeJson($changedFields),
                'old_values'        => $this->safeJson($oldValues),
                'new_values'        => $this->safeJson($newValues),

                'log_note'          => $note,

                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        } catch (\Throwable $e) {
            // never break API flow
        }
    }

    private function normalizeRow($row): array
    {
        $arr = (array) $row;

        $meta = $arr['metadata'] ?? null;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        return $arr;
    }

    /**
     * NOTE:
     * - For pages that exist in `pages` table => we store ONLY page_id (FK) and keep meta_tags.page_link = ''.
     * - For static URLs that do NOT exist in `pages` => we store page_id = null and keep meta_tags.page_link = "/your-path".
     *
     * This allows:
     * - page_id-based editor (dynamic pages) ✅
     * - page_link-based manager (static pages) ✅
     */

    private function baseQuery(Request $r, bool $includeDeleted = false)
    {
        $hasPageId     = Schema::hasColumn('meta_tags', 'page_id') && Schema::hasTable('pages');
        $hasMtPageLink = Schema::hasColumn('meta_tags', 'page_link');

        // pages columns are not guaranteed in your DB (your error proves that)
        $hasPTitle   = Schema::hasTable('pages') && Schema::hasColumn('pages', 'title');
        $hasPSlug    = Schema::hasTable('pages') && Schema::hasColumn('pages', 'slug');
        $hasPPageUrl = Schema::hasTable('pages') && Schema::hasColumn('pages', 'page_url');

        $q = DB::table('meta_tags as mt')->select(['mt.*']);

        // Optional join to allow searching/filtering by page fields
        if ($hasPageId) {
            $q->leftJoin('pages as p', 'p.id', '=', 'mt.page_id');

            $selects = [];
            if ($hasPTitle)   $selects[] = DB::raw('p.title as page_title_ref');
            if ($hasPSlug)    $selects[] = DB::raw('p.slug as page_slug_ref');
            if ($hasPPageUrl) $selects[] = DB::raw('p.page_url as page_url_ref');

            if (count($selects)) $q->addSelect($selects);
        }

        if (!$includeDeleted) $q->whereNull('mt.deleted_at');

        // ?q=
        if ($r->filled('q')) {
            $term = '%' . trim((string) $r->query('q')) . '%';
            $q->where(function ($w) use ($term, $hasPageId, $hasMtPageLink, $hasPTitle, $hasPSlug, $hasPPageUrl) {
                $w->where('mt.tag_type', 'like', $term)
                  ->orWhere('mt.tag_attribute', 'like', $term)
                  ->orWhere('mt.tag_attribute_value', 'like', $term);

                if ($hasMtPageLink) {
                    $w->orWhere('mt.page_link', 'like', $term);
                }

                if ($hasPageId) {
                    if ($hasPTitle)   $w->orWhere('p.title', 'like', $term);
                    if ($hasPSlug)    $w->orWhere('p.slug', 'like', $term);
                    if ($hasPPageUrl) $w->orWhere('p.page_url', 'like', $term);
                }
            });
        }

        // ?tag_type=
        if ($r->filled('tag_type')) {
            $q->where('mt.tag_type', (string) $r->query('tag_type'));
        }

        // ?page_id=
        if ($hasPageId && $r->filled('page_id')) {
            $q->where('mt.page_id', (int) $r->query('page_id'));
        }

        // ?page_link=  (supports dynamic pages via resolving to page_id, and static pages via meta_tags.page_link)
        if ($r->filled('page_link')) {
            $link = $this->normalizePageLink((string) $r->query('page_link'));

            if ($hasPageId) {
                $pid = $this->resolvePageIdByLink($link);

                if ($pid) {
                    // dynamic page => filter by page_id (because stored mt.page_link = '')
                    $q->where('mt.page_id', $pid);
                } else {
                    // static page => filter by mt.page_link
                    if ($hasMtPageLink && $link !== null) $q->where('mt.page_link', $link);
                }
            } else {
                // legacy schema => page_link is the scope key
                if ($hasMtPageLink && $link !== null) $q->where('mt.page_link', $link);
            }
        }

        // sort
        $sort = (string) $r->query('sort', 'created_at');
        $dir  = strtolower((string) $r->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['created_at', 'updated_at', 'tag_type', 'id'];
        if ($hasMtPageLink) $allowed[] = 'page_link';
        if ($hasPageId)     $allowed[] = 'page_id';

        if (!in_array($sort, $allowed, true)) $sort = 'created_at';

        // prefix always mt.
        $q->orderBy('mt.' . $sort, $dir);

        return $q;
    }

    private function resolveMetaTag($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('meta_tags as mt');
        if (!$includeDeleted) $q->whereNull('mt.deleted_at');

        if (ctype_digit((string) $identifier)) {
            $q->where('mt.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('mt.uuid', (string) $identifier);
        } else {
            return null;
        }

        return $q->first();
    }

    private function normalizeMetadataFromRequest(Request $r)
    {
        $metadata = $r->input('metadata', null);

        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
        }

        return $metadata;
    }

    /** Scope selector for page (prefer page_id; otherwise fallback to page_link (static pages)) */
    private function applyPageScope($q, ?int $pageId, ?string $pageLink)
    {
        $hasPageId     = Schema::hasColumn('meta_tags', 'page_id');
        $hasMtPageLink = Schema::hasColumn('meta_tags', 'page_link');

        if ($hasPageId && $pageId) {
            $q->where('page_id', (int) $pageId);
            return;
        }

        if ($hasMtPageLink && $pageLink !== null && $pageLink !== '') {
            $q->where('page_link', $pageLink);
        }
    }

    /* ============================================
     | CRUD (Admin/Auth side)
     |============================================ */

    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $q = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        if ($onlyDeleted) $q->whereNotNull('mt.deleted_at');

        $p = $q->paginate($perPage);
        $items = array_map(fn($r) => $this->normalizeRow($r), $p->items());

        return response()->json([
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

    /**
     * Resolve by:
     * - page_id OR page_link/url/path
     * - if link matches a record in pages => it resolves to page_id internally
     * - else (static) uses page_link scope
     */
    public function resolve(Request $request)
    {
        $hasPageId = Schema::hasColumn('meta_tags', 'page_id') && Schema::hasTable('pages');

        if ($hasPageId) {
            $hasAnyScope = $request->filled('page_id') || $request->filled('page_link') || $request->filled('url') || $request->filled('path');
            if (!$hasAnyScope) {
                return response()->json(['success' => false, 'message' => 'page_id or page_link/url/path is required'], 422);
            }

            [$pageId, $pageLinkScope] = $this->resolveScopeFromRequest($request);
            if (!$pageId && !$pageLinkScope) {
                return response()->json(['success' => false, 'message' => 'Invalid page scope'], 422);
            }

            if ($pageId) $request->merge(['page_id' => $pageId]);
            if ($pageLinkScope) $request->merge(['page_link' => $pageLinkScope]);
        } else {
            $raw = $request->query('page_link') ?? $request->query('url') ?? $request->query('path');
            $link = $this->normalizePageLink(is_string($raw) ? $raw : null);
            if (!$link) {
                return response()->json(['success' => false, 'message' => 'page_link/url/path is required'], 422);
            }
            $request->merge(['page_link' => $link]);
        }

        $q = $this->baseQuery($request, false);
        $rows = $q->limit(500)->get();

        return response()->json([
            'success' => true,
            'data'    => array_map(fn($r) => $this->normalizeRow($r), $rows->all()),
        ]);
    }

    public function show(Request $request, $identifier)
    {
        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveMetaTag($identifier, $includeDeleted);
        if (!$row) return response()->json(['message' => 'Meta tag not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $hasPageId   = Schema::hasColumn('meta_tags', 'page_id');
        $hasPageLink = Schema::hasColumn('meta_tags', 'page_link');

        $validated = $request->validate([
            'tag_type'            => ['required', 'string', 'max:255'],
            'tag_attribute'       => ['nullable', 'string', 'max:255'],
            'tag_attribute_value' => ['required', 'string', 'max:255'],

            // scope (accept either)
            'page_id'             => ($hasPageId && Schema::hasTable('pages')) ? ['nullable', 'integer', 'exists:pages,id'] : ['nullable'],
            'page_link'           => ['nullable', 'string', 'max:255'],
            'url'                 => ['nullable', 'string', 'max:2048'],
            'path'                => ['nullable', 'string', 'max:2048'],

            'metadata'            => ['nullable'],
        ]);

        [$pageId, $pageLinkScope] = $this->resolveScopeFromRequest($request);

        if ($hasPageId) {
            if (!$pageId && !$pageLinkScope) {
                return response()->json(['success' => false, 'message' => 'page_id or page_link/url/path is required'], 422);
            }
            // if static (no pageId) but DB has no page_link column, cannot store
            if (!$pageId && !$hasPageLink) {
                return response()->json(['success' => false, 'message' => 'Static pages require meta_tags.page_link column'], 422);
            }
        } else {
            if (!$pageLinkScope) {
                return response()->json(['success' => false, 'message' => 'page_link/url/path is required'], 422);
            }
        }

        $now  = now();
        $uuid = (string) Str::uuid();

        $metadata = $this->normalizeMetadataFromRequest($request);

        $insert = [
            'uuid'                => $uuid,
            'tag_type'            => trim((string) $validated['tag_type']),
            'tag_attribute'       => $validated['tag_attribute'] !== null ? trim((string) $validated['tag_attribute']) : null,
            'tag_attribute_value' => trim((string) $validated['tag_attribute_value']),

            // scope
            'page_id'             => $hasPageId ? ($pageId ? (int) $pageId : null) : null,

            // dynamic => '', static => store link (if column exists)
            'page_link'           => $hasPageLink
                ? (($hasPageId && $pageId) ? '' : (($pageLinkScope ?? '') ?: ''))
                : null,

            'created_by'          => $actor['id'] ?: null,
            'created_at_ip'       => $request->ip(),
            'updated_at_ip'       => $request->ip(),

            'created_at'          => $now,
            'updated_at'          => $now,
            'deleted_at'          => null,

            'metadata'            => $metadata !== null ? json_encode($metadata) : null,
        ];

        if (!$hasPageLink) unset($insert['page_link']);

        $id  = DB::table('meta_tags')->insertGetId($insert);
        $row = DB::table('meta_tags')->where('id', (int) $id)->first();

        $newVals = $row ? (array) $row : (['id' => $id] + $insert);
        [$changedFields] = $this->computeDiff(null, $newVals, array_keys($insert));

        $this->logActivity(
            $request,
            'create',
            'meta_tags',
            'meta_tags',
            (int) $id,
            $changedFields,
            null,
            $newVals,
            'Meta tag created'
        );

        return response()->json([
            'success' => true,
            'data'    => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    public function update(Request $request, $identifier)
    {
        $row = $this->resolveMetaTag($identifier, true);
        if (!$row) {
            $this->logActivity($request, 'update_failed', 'meta_tags', 'meta_tags', null, null, null, null, 'Meta tag not found');
            return response()->json(['message' => 'Meta tag not found'], 404);
        }

        $hasPageId   = Schema::hasColumn('meta_tags', 'page_id');
        $hasPageLink = Schema::hasColumn('meta_tags', 'page_link');

        $beforeRow = DB::table('meta_tags')->where('id', (int) $row->id)->first();
        $before = $beforeRow ? (array) $beforeRow : (array) $row;

        $validated = $request->validate([
            'tag_type'            => ['nullable', 'string', 'max:255'],
            'tag_attribute'       => ['nullable', 'string', 'max:255'],
            'tag_attribute_value' => ['nullable', 'string', 'max:255'],

            // scope (optional)
            'page_id'             => ($hasPageId && Schema::hasTable('pages')) ? ['nullable', 'integer', 'exists:pages,id'] : ['nullable'],
            'page_link'           => ['nullable', 'string', 'max:255'],
            'url'                 => ['nullable', 'string', 'max:2048'],
            'path'                => ['nullable', 'string', 'max:2048'],

            'metadata'            => ['nullable'],
        ]);

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        foreach (['tag_type', 'tag_attribute', 'tag_attribute_value'] as $k) {
            if (array_key_exists($k, $validated)) {
                $v = $validated[$k];
                $update[$k] = ($v === null) ? null : trim((string) $v);
            }
        }

        // Only change scope if caller supplied any scope fields
        $scopeTouched = $request->filled('page_id') || $request->filled('page_link') || $request->filled('url') || $request->filled('path');

        if ($scopeTouched) {
            [$pageId, $pageLinkScope] = $this->resolveScopeFromRequest($request);

            if ($hasPageId) {
                if (!$pageId && !$pageLinkScope) {
                    return response()->json(['success' => false, 'message' => 'page_id or page_link/url/path is required'], 422);
                }
                if (!$pageId && !$hasPageLink) {
                    return response()->json(['success' => false, 'message' => 'Static pages require meta_tags.page_link column'], 422);
                }

                $update['page_id'] = $pageId ? (int) $pageId : null;

                if ($hasPageLink) {
                    $update['page_link'] = ($hasPageId && $pageId) ? '' : (($pageLinkScope ?? '') ?: '');
                }
            } else {
                $raw = $request->input('page_link') ?? $request->input('url') ?? $request->input('path');
                $link = $this->normalizePageLink(is_string($raw) ? $raw : null);
                if (!$link) return response()->json(['success' => false, 'message' => 'page_link/url/path is required'], 422);

                if ($hasPageLink) $update['page_link'] = $link;
            }
        }

        if (array_key_exists('metadata', $validated)) {
            $metadata = $this->normalizeMetadataFromRequest($request);
            $update['metadata'] = $metadata !== null ? json_encode($metadata) : null;
        }

        DB::table('meta_tags')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('meta_tags')->where('id', (int) $row->id)->first();

        $after = $fresh ? (array) $fresh : null;
        [$changedFields, $oldVals, $newVals] = $this->computeDiff($before, $after, array_keys($update));

        $this->logActivity(
            $request,
            'update',
            'meta_tags',
            'meta_tags',
            (int) $row->id,
            $changedFields,
            $oldVals,
            $newVals,
            'Meta tag updated'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveMetaTag($identifier, false);
        if (!$row) {
            $this->logActivity($request, 'delete_failed', 'meta_tags', 'meta_tags', null, null, null, null, 'Not found or already deleted');
            return response()->json(['message' => 'Not found or already deleted'], 404);
        }

        $before = DB::table('meta_tags')->where('id', (int) $row->id)->first();
        $beforeArr = $before ? (array) $before : (array) $row;

        DB::table('meta_tags')->where('id', (int) $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('meta_tags')->where('id', (int) $row->id)->first();

        [$changedFields, $oldVals, $newVals] = $this->computeDiff(
            $beforeArr,
            $fresh ? (array)$fresh : null,
            ['deleted_at', 'updated_at', 'updated_at_ip']
        );

        $this->logActivity(
            $request,
            'delete',
            'meta_tags',
            'meta_tags',
            (int) $row->id,
            $changedFields,
            $oldVals,
            $newVals,
            'Meta tag moved to bin'
        );

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveMetaTag($identifier, true);
        if (!$row || $row->deleted_at === null) {
            $this->logActivity($request, 'restore_failed', 'meta_tags', 'meta_tags', null, null, null, null, 'Not found in bin');
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $before = DB::table('meta_tags')->where('id', (int) $row->id)->first();
        $beforeArr = $before ? (array) $before : (array) $row;

        DB::table('meta_tags')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('meta_tags')->where('id', (int) $row->id)->first();

        [$changedFields, $oldVals, $newVals] = $this->computeDiff(
            $beforeArr,
            $fresh ? (array)$fresh : null,
            ['deleted_at', 'updated_at', 'updated_at_ip']
        );

        $this->logActivity(
            $request,
            'restore',
            'meta_tags',
            'meta_tags',
            (int) $row->id,
            $changedFields,
            $oldVals,
            $newVals,
            'Meta tag restored from bin'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveMetaTag($identifier, true);
        if (!$row) {
            $this->logActivity($request, 'force_delete_failed', 'meta_tags', 'meta_tags', null, null, null, null, 'Meta tag not found');
            return response()->json(['message' => 'Meta tag not found'], 404);
        }

        $before = DB::table('meta_tags')->where('id', (int) $row->id)->first();
        $beforeArr = $before ? (array) $before : (array) $row;

        DB::table('meta_tags')->where('id', (int) $row->id)->delete();

        $this->logActivity(
            $request,
            'force_delete',
            'meta_tags',
            'meta_tags',
            (int) $row->id,
            ['__deleted__'],
            $beforeArr,
            null,
            'Meta tag permanently deleted'
        );

        return response()->json(['success' => true]);
    }

    /* ============================================
     | Public (no auth)
     |============================================ */

    /**
     * GET /api/public/meta-tags
     * Supports:
     * - ?page_id=...
     * - OR ?page_link=/path (if it matches pages, auto-resolves to page_id; else static link scope)
     * - OR ?url=... / ?path=...
     */
    public function publicIndex(Request $request)
    {
        $hasPageId = Schema::hasColumn('meta_tags', 'page_id') && Schema::hasTable('pages');

        if ($hasPageId) {
            $hasAnyScope = $request->filled('page_id') || $request->filled('page_link') || $request->filled('url') || $request->filled('path');
            if (!$hasAnyScope) {
                return response()->json(['success' => false, 'message' => 'page_id or page_link/url/path is required'], 422);
            }

            [$pageId, $pageLinkScope] = $this->resolveScopeFromRequest($request);
            if (!$pageId && !$pageLinkScope) {
                return response()->json(['success' => false, 'message' => 'Invalid page scope'], 422);
            }

            if ($pageId) $request->merge(['page_id' => $pageId]);
            if ($pageLinkScope) $request->merge(['page_link' => $pageLinkScope]);
        } else {
            $raw = $request->query('page_link') ?? $request->query('url') ?? $request->query('path');
            $link = $this->normalizePageLink(is_string($raw) ? $raw : null);
            if (!$link) {
                return response()->json(['success' => false, 'message' => 'page_link/url/path is required'], 422);
            }
            $request->merge(['page_link' => $link]);
        }

        $perPage = max(1, min(200, (int) $request->query('per_page', 200)));
        $q = $this->baseQuery($request, false);

        $p = $q->paginate($perPage);
        $items = array_map(fn($r) => $this->normalizeRow($r), $p->items());

        return response()->json([
            'success' => true,
            'data'    => $items,
            'pagination' => [
                'page'      => $p->currentPage(),
                'per_page'  => $p->perPage(),
                'total'     => $p->total(),
                'last_page' => $p->lastPage(),
            ],
        ]);
    }

    /* ============================================
     | Bulk Save (UI-friendly)
     |============================================ */

    /**
     * POST /api/meta-tags/bulk
     * Body:
     *  - page_id OR page_link/url/path
     *  - tags: [{id?, tag_type, attribute?, content}]
     *
     * Behavior:
     * - if link matches pages => stores by page_id (page_link is '')
     * - else static => stores by page_link (page_id = null)
     */
    public function bulk(Request $request)
    {
        $actor = $this->actor($request);

        $hasPageId   = Schema::hasColumn('meta_tags', 'page_id');
        $hasPageLink = Schema::hasColumn('meta_tags', 'page_link');

        $validated = $request->validate([
            'page_id'   => ($hasPageId && Schema::hasTable('pages')) ? ['nullable', 'integer', 'exists:pages,id'] : ['nullable'],
            'page_link' => ['nullable', 'string', 'max:255'],
            'url'       => ['nullable', 'string', 'max:2048'],
            'path'      => ['nullable', 'string', 'max:2048'],

            'tags'              => ['required', 'array', 'min:1', 'max:500'],
            'tags.*.id'         => ['nullable'],
            'tags.*.tag_type'   => ['required', 'string', 'max:255'],
            'tags.*.attribute'  => ['nullable', 'string', 'max:255'],
            'tags.*.content'    => ['required', 'string', 'max:255'],
        ]);

        [$pageId, $pageLinkScope] = $this->resolveScopeFromRequest($request);

        if ($hasPageId) {
            if (!$pageId && !$pageLinkScope) {
                return response()->json(['success' => false, 'message' => 'page_id or page_link/url/path is required'], 422);
            }
            if (!$pageId && !$hasPageLink) {
                return response()->json(['success' => false, 'message' => 'Static pages require meta_tags.page_link column'], 422);
            }
        } else {
            if (!$pageLinkScope) {
                return response()->json(['success' => false, 'message' => 'page_link/url/path is required'], 422);
            }
        }

        $tagsIn  = $validated['tags'];
        $keepIds = [];

        DB::beginTransaction();
        try {
            foreach ($tagsIn as $t) {
                $id      = $t['id'] ?? null;
                $tagType = trim((string) $t['tag_type']);
                $attr    = array_key_exists('attribute', $t) ? $t['attribute'] : null;
                $attr    = ($attr === null) ? null : trim((string) $attr);
                $content = trim((string) ($t['content'] ?? ''));

                // charset rule
                if ($tagType === 'charset') {
                    $attr = null;
                    if ($content === '') $content = 'UTF-8';
                }

                $now = now();

                // Update if valid numeric ID exists, else insert
                if ($id !== null && ctype_digit((string) $id)) {
                    $existing = DB::table('meta_tags')->where('id', (int) $id)->first();

                    if ($existing) {
                        $payload = [
                            'tag_type'            => $tagType,
                            'tag_attribute'       => $attr,
                            'tag_attribute_value' => $content,

                            'page_id'             => $hasPageId ? ($pageId ? (int) $pageId : null) : null,

                            'deleted_at'          => null,
                            'updated_at'          => $now,
                            'updated_at_ip'       => $request->ip(),
                        ];

                        if ($hasPageLink) {
                            $payload['page_link'] = ($hasPageId && $pageId) ? '' : (($pageLinkScope ?? '') ?: '');
                        }

                        DB::table('meta_tags')->where('id', (int) $id)->update($payload);

                        $keepIds[] = (int) $id;
                        continue;
                    }
                }

                $insert = [
                    'uuid'                => (string) Str::uuid(),
                    'tag_type'            => $tagType,
                    'tag_attribute'       => $attr,
                    'tag_attribute_value' => $content,

                    'page_id'             => $hasPageId ? ($pageId ? (int) $pageId : null) : null,

                    'metadata'            => null,

                    'created_by'          => ($actor['id'] ?? 0) ? (int) $actor['id'] : null,
                    'created_at_ip'       => $request->ip(),
                    'updated_at_ip'       => $request->ip(),

                    'created_at'          => $now,
                    'updated_at'          => $now,
                    'deleted_at'          => null,
                ];

                if ($hasPageLink) {
                    $insert['page_link'] = ($hasPageId && $pageId) ? '' : (($pageLinkScope ?? '') ?: '');
                }

                $newId = DB::table('meta_tags')->insertGetId($insert);
                $keepIds[] = (int) $newId;
            }

            // Sync behavior: soft-delete tags not in UI list (scoped)
            $delQ = DB::table('meta_tags')->whereNull('deleted_at');
            $this->applyPageScope($delQ, $pageId, $pageLinkScope);

            if (count($keepIds)) {
                $delQ->whereNotIn('id', $keepIds);
            }

            $delQ->update([
                'deleted_at'    => now(),
                'updated_at'    => now(),
                'updated_at_ip' => $request->ip(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            $this->logActivity(
                $request,
                'bulk_save_failed',
                'meta_tags',
                'meta_tags',
                null,
                null,
                null,
                ['page_id' => $pageId, 'page_link' => $pageLinkScope],
                'Bulk save failed'
            );

            return response()->json(['success' => false, 'message' => 'Bulk save failed'], 500);
        }

        // Return rows for this scope (UI-friendly keys: attribute/content)
        $rowsQ = DB::table('meta_tags')->whereNull('deleted_at')->orderBy('id', 'asc');
        $this->applyPageScope($rowsQ, $pageId, $pageLinkScope);

        $rows = $rowsQ->get();

        $data = array_map(function ($r) {
            $arr = $this->normalizeRow($r);
            $arr['attribute'] = $arr['tag_attribute'] ?? null;
            $arr['content']   = $arr['tag_attribute_value'] ?? null;
            return $arr;
        }, $rows->all());

        $this->logActivity(
            $request,
            'bulk_save',
            'meta_tags',
            'meta_tags',
            null,
            ['bulk'],
            null,
            ['page_id' => $pageId, 'page_link' => $pageLinkScope, 'kept_ids' => $keepIds],
            'Bulk meta tags saved'
        );

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}