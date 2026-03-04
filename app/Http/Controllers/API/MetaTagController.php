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
     * page_link is DEPRECATED for new saves.
     * We only use page_id for scoping when available.
     * For safety/backward compatibility, we keep read/filter support for page_link,
     * but we DO NOT write/auto-derive/store it anymore (we clear it to '').
     */

    private function baseQuery(Request $r, bool $includeDeleted = false)
    {
        $hasPageId = Schema::hasColumn('meta_tags', 'page_id') && Schema::hasTable('pages');

        $q = DB::table('meta_tags as mt')->select(['mt.*']);

        // Optional join to allow searching/filtering by page fields
        if ($hasPageId) {
            $q->leftJoin('pages as p', 'p.id', '=', 'mt.page_id');
            $q->addSelect([
                DB::raw('p.title as page_title_ref'),
                DB::raw('p.slug as page_slug_ref'),
                DB::raw('p.page_url as page_url_ref'),
            ]);
        }

        if (!$includeDeleted) $q->whereNull('mt.deleted_at');

        // ?q=
        if ($r->filled('q')) {
            $term = '%' . trim((string) $r->query('q')) . '%';
            $q->where(function ($w) use ($term, $hasPageId) {
                $w->where('mt.tag_type', 'like', $term)
                  ->orWhere('mt.tag_attribute', 'like', $term)
                  ->orWhere('mt.tag_attribute_value', 'like', $term)
                  ->orWhere('mt.page_link', 'like', $term); // legacy support (read/search only)

                if ($hasPageId) {
                    $w->orWhere('p.title', 'like', $term)
                      ->orWhere('p.slug', 'like', $term)
                      ->orWhere('p.page_url', 'like', $term);
                }
            });
        }

        // ?tag_type=
        if ($r->filled('tag_type')) {
            $q->where('mt.tag_type', (string) $r->query('tag_type'));
        }

        // ?page_link= (legacy filter)
        if ($r->filled('page_link')) {
            $q->where('mt.page_link', (string) $r->query('page_link'));
        }

        // ?page_id=
        if ($hasPageId && $r->filled('page_id')) {
            $q->where('mt.page_id', (int) $r->query('page_id'));
        }

        // sort
        $sort = (string) $r->query('sort', 'created_at');
        $dir  = strtolower((string) $r->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['created_at', 'updated_at', 'tag_type', 'page_link', 'id'];
        if ($hasPageId) $allowed[] = 'page_id';

        if (!in_array($sort, $allowed, true)) $sort = 'created_at';

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

    /** Scope selector for page (prefer page_id, fallback page_link for legacy) */
    private function applyPageScope($q, ?int $pageId, ?string $pageLink)
    {
        $hasPageId = Schema::hasColumn('meta_tags', 'page_id');

        if ($hasPageId && $pageId) {
            $q->where('page_id', (int) $pageId);
            return;
        }

        // legacy fallback only
        if ($pageLink !== null && $pageLink !== '') {
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

    /** convenience: resolve by page_id (preferred) and optional tag_type; page_link supported only for legacy */
    public function resolve(Request $request)
    {
        $hasPageId = Schema::hasColumn('meta_tags', 'page_id');

        if ($hasPageId) {
            if (!$request->filled('page_id')) {
                return response()->json(['success' => false, 'message' => 'page_id is required'], 422);
            }
        } else {
            if (!$request->filled('page_link')) {
                return response()->json(['success' => false, 'message' => 'page_link is required'], 422);
            }
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
        $hasPageLink = Schema::hasColumn('meta_tags', 'page_link'); // may exist, but we won't store real link

        $validated = $request->validate([
            'tag_type'            => ['required', 'string', 'max:255'],
            'tag_attribute'       => ['nullable', 'string', 'max:255'],
            'tag_attribute_value' => ['required', 'string', 'max:255'],

            // ✅ NEW RULE: if page_id exists, it is REQUIRED (page_link no longer used for new saves)
            'page_id'             => ($hasPageId && Schema::hasTable('pages')) ? ['required', 'integer', 'exists:pages,id'] : ['nullable'],

            // legacy only (ignored when page_id exists)
            'page_link'           => $hasPageId ? ['nullable'] : ['required', 'string', 'max:255'],

            'metadata'            => ['nullable'],
        ]);

        $now  = now();
        $uuid = (string) Str::uuid();

        $metadata = $this->normalizeMetadataFromRequest($request);

        $pageId = ($hasPageId && !empty($validated['page_id'])) ? (int) $validated['page_id'] : null;

        $insert = [
            'uuid'                => $uuid,
            'tag_type'            => trim((string) $validated['tag_type']),
            'tag_attribute'       => $validated['tag_attribute'] !== null ? trim((string) $validated['tag_attribute']) : null,
            'tag_attribute_value' => trim((string) $validated['tag_attribute_value']),

            // ✅ page linkage
            'page_id'             => $hasPageId ? $pageId : null,

            // ✅ IMPORTANT FIX: DO NOT STORE page_link anymore
            // keep it empty string to satisfy NOT NULL schemas too
            'page_link'           => $hasPageLink ? '' : null,

            'created_by'          => $actor['id'] ?: null,
            'created_at_ip'       => $request->ip(),
            'updated_at_ip'       => $request->ip(),

            'created_at'          => $now,
            'updated_at'          => $now,
            'deleted_at'          => null,

            'metadata'            => $metadata !== null ? json_encode($metadata) : null,
        ];

        // If page_link column does NOT exist, remove from insert payload
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

            // page linkage
            'page_id'             => ($hasPageId && Schema::hasTable('pages')) ? ['nullable', 'integer', 'exists:pages,id'] : ['nullable'],

            // legacy only; ignored for new behavior
            'page_link'           => ['nullable', 'string', 'max:255'],

            'metadata'            => ['nullable'],
        ]);

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        // NOTE: page_link removed from update writes (we clear it instead)
        foreach (['tag_type', 'tag_attribute', 'tag_attribute_value'] as $k) {
            if (array_key_exists($k, $validated)) {
                $v = $validated[$k];
                $update[$k] = ($v === null) ? null : trim((string) $v);
            }
        }

        if ($hasPageId && array_key_exists('page_id', $validated)) {
            $update['page_id'] = $validated['page_id'] ? (int) $validated['page_id'] : null;
        }

        // ✅ IMPORTANT FIX: Always clear page_link in DB when column exists
        // (this is why you were seeing it stored: old code was auto-deriving / keeping old value)
        if ($hasPageLink) {
            $update['page_link'] = '';
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

    /** GET /api/public/meta-tags?page_id=... (preferred) OR legacy ?page_link=... */
    public function publicIndex(Request $request)
    {
        $hasPageId = Schema::hasColumn('meta_tags', 'page_id');

        if ($hasPageId) {
            if (!$request->filled('page_id')) {
                return response()->json(['success' => false, 'message' => 'page_id is required'], 422);
            }
        } else {
            if (!$request->filled('page_link')) {
                return response()->json(['success' => false, 'message' => 'page_link is required'], 422);
            }
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
     *  - page_id (REQUIRED when meta_tags.page_id exists)
     *  - tags: [{id?, tag_type, attribute?, content}]
     */
    public function bulk(Request $request)
    {
        $actor = $this->actor($request);

        $hasPageId   = Schema::hasColumn('meta_tags', 'page_id');
        $hasPageLink = Schema::hasColumn('meta_tags', 'page_link'); // will be cleared

        $validated = $request->validate([
            // ✅ page_id required when available
            'page_id'           => ($hasPageId && Schema::hasTable('pages')) ? ['required', 'integer', 'exists:pages,id'] : ['nullable'],

            // legacy only (ignored for new behavior)
            'page_link'         => $hasPageId ? ['nullable'] : ['required', 'string', 'max:255'],

            'tags'              => ['required', 'array', 'min:1', 'max:500'],
            'tags.*.id'         => ['nullable'],
            'tags.*.tag_type'   => ['required', 'string', 'max:255'], // charset/standard/opengraph/twitter/http etc
            'tags.*.attribute'  => ['nullable', 'string', 'max:255'], // description, og:title, http-equiv, ...
            'tags.*.content'    => ['required', 'string', 'max:255'], // meta content (or charset value)
        ]);

        $pageId = ($hasPageId && !empty($validated['page_id'])) ? (int) $validated['page_id'] : null;
        $pageLinkLegacy = (!$hasPageId && isset($validated['page_link'])) ? trim((string) $validated['page_link']) : null;

        $tagsIn = $validated['tags'];
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

                            // ✅ page linkage
                            'page_id'             => $hasPageId ? $pageId : null,

                            // ✅ IMPORTANT FIX: clear page_link always
                            'deleted_at'          => null,
                            'updated_at'          => $now,
                            'updated_at_ip'       => $request->ip(),
                        ];

                        if ($hasPageLink) {
                            $payload['page_link'] = ''; // never store the actual link
                        } elseif (!$hasPageId) {
                            // legacy schema: must keep page_link to scope records
                            $payload['page_link'] = $pageLinkLegacy;
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

                    // ✅ page linkage
                    'page_id'             => $hasPageId ? $pageId : null,

                    'metadata'            => null,

                    'created_by'          => ($actor['id'] ?? 0) ? (int) $actor['id'] : null,
                    'created_at_ip'       => $request->ip(),
                    'updated_at_ip'       => $request->ip(),

                    'created_at'          => $now,
                    'updated_at'          => $now,
                    'deleted_at'          => null,
                ];

                if ($hasPageLink) {
                    $insert['page_link'] = ''; // ✅ IMPORTANT FIX
                } else {
                    // legacy schema requires page_link
                    $insert['page_link'] = $pageLinkLegacy;
                }

                $newId = DB::table('meta_tags')->insertGetId($insert);
                $keepIds[] = (int) $newId;
            }

            // ✅ Sync behavior: soft-delete tags not in UI list (scoped by page_id preferred)
            $delQ = DB::table('meta_tags')->whereNull('deleted_at');
            $this->applyPageScope($delQ, $pageId, $pageLinkLegacy);

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
                ['page_id' => $pageId, 'page_link' => $pageLinkLegacy],
                'Bulk save failed'
            );

            return response()->json(['success' => false, 'message' => 'Bulk save failed'], 500);
        }

        // Return rows for this page (UI-friendly keys: attribute/content)
        $rowsQ = DB::table('meta_tags')->whereNull('deleted_at')->orderBy('id', 'asc');
        $this->applyPageScope($rowsQ, $pageId, $pageLinkLegacy);

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
            ['page_id' => $pageId, 'kept_ids' => $keepIds],
            'Bulk meta tags saved'
        );

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}