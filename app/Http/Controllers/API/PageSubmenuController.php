<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;


class PageSubmenuController extends Controller
{
    /** Table name (your migration log shows `pages_submenu`) */
    private string $table = 'pages_submenu';

    /* ============================================
     | Helpers
     |============================================ */

    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
            'type' => $request->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    private function normSlug(?string $s): string
    {
        $s = (string) $s;
        $s = trim($s);
        $s = $s === '' ? '' : Str::slug($s, '-');
        return $s;
    }

    /** Ensure page exists (and not soft-deleted) */
    private function validatePage(int $pageId): void
    {
        $ok = DB::table('pages')
            ->where('id', $pageId)
            ->whereNull('deleted_at')
            ->exists();

        if (!$ok) {
            abort(response()->json(['error' => 'Invalid page_id'], 422));
        }
    }

    /** Auto-generate unique submenu shortcode (alphanumeric) */
    private function generateSubmenuShortcode(?int $excludeId = null): string
    {
        $maxTries = 50;

        for ($i = 0; $i < $maxTries; $i++) {
            $code = 'PSM' . Str::upper(Str::random(6)); // e.g. PSM3K9ZAQ

            $q = DB::table($this->table)->where('shortcode', $code);
            if ($excludeId) {
                $q->where('id', '!=', $excludeId);
            }

            if (!$q->exists()) {
                return $code;
            }
        }

        return 'PSM' . time();
    }

    /**
     * Guard that parent exists, is not self,
     * AND belongs to the same page_id.
     */
    private function validateParent(?int $parentId, int $pageId, ?int $selfId = null): void
    {
        if ($parentId === null) {
            return;
        }

        if ($selfId !== null && $parentId === $selfId) {
            abort(response()->json(['error' => 'Parent cannot be self'], 422));
        }

        $ok = DB::table($this->table)
            ->where('id', $parentId)
            ->where('page_id', $pageId)
            ->whereNull('deleted_at')
            ->exists();

        if (!$ok) {
            abort(response()->json(['error' => 'Invalid parent_id for this page'], 422));
        }
    }

    /** Next position among siblings (scoped to page_id) */
    private function nextPosition(int $pageId, ?int $parentId): int
    {
        $q = DB::table($this->table)
            ->where('page_id', $pageId)
            ->whereNull('deleted_at');

        if ($parentId === null) {
            $q->whereNull('parent_id');
        } else {
            $q->where('parent_id', $parentId);
        }

        $max = (int) $q->max('position');
        return $max + 1;
    }

    /** Resolve page_id from query param page_id OR page_slug */
    private function resolvePageIdFromRequest(Request $r): int
    {
        $pageId = $r->query('page_id', null);
        $pageSlug = trim((string) $r->query('page_slug', ''));

        if ($pageId !== null && $pageId !== '') {
            return (int) $pageId;
        }

        if ($pageSlug !== '') {
            $row = DB::table('pages')
                ->where('slug', $pageSlug)
                ->whereNull('deleted_at')
                ->first();

            if ($row) {
                return (int) $row->id;
            }
        }

        return 0;
    }

    /* ============================================
     | List / Tree / Resolve
     |============================================ */

    public function index(Request $r)
    {
        $page = max(1, (int) $r->query('page', 1));
        $per  = min(100, max(5, (int) $r->query('per_page', 20)));
        $q    = trim((string) $r->query('q', ''));
        $activeParam = $r->query('active', null); // null, '0', '1'
        $parentId = $r->query('parent_id', 'any'); // 'any' | null | int
        $pageId = $r->query('page_id', 'any'); // 'any' | int
        $sort = (string) $r->query('sort', 'position'); // position|title|created_at
        $direction = strtolower((string) $r->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowedSort = ['position', 'title', 'created_at'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'position';
        }

        $base = DB::table($this->table)
            ->whereNull('deleted_at');

        if ($q !== '') {
            $base->where(function ($x) use ($q) {
                $x->where('title', 'like', "%{$q}%")
                  ->orWhere('slug', 'like', "%{$q}%")
                  ->orWhere('shortcode', 'like', "%{$q}%")
                  ->orWhere('page_slug', 'like', "%{$q}%")
                  ->orWhere('page_shortcode', 'like', "%{$q}%")
                  ->orWhere('page_url', 'like', "%{$q}%");
            });
        }

        if ($activeParam !== null && in_array((string) $activeParam, ['0', '1'], true)) {
            $base->where('active', (int) $activeParam === 1);
        }

        if ($pageId !== 'any' && $pageId !== null && $pageId !== '') {
            $base->where('page_id', (int) $pageId);
        }

        if ($parentId === null || $parentId === 'null') {
            $base->whereNull('parent_id');
        } elseif ($parentId !== 'any') {
            $base->where('parent_id', (int) $parentId);
        }

        $total = (clone $base)->count();
        $rows  = $base->orderBy($sort, $direction)
                      ->orderBy('id', 'asc')
                      ->forPage($page, $per)
                      ->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
            'pagination' => [
                'page' => $page,
                'per_page' => $per,
                'total' => $total,
            ],
        ]);
    }

    public function indexTrash(Request $r)
    {
        $page = max(1, (int) $r->query('page', 1));
        $per  = min(100, max(5, (int) $r->query('per_page', 20)));

        $base = DB::table($this->table)
            ->whereNotNull('deleted_at');

        $total = (clone $base)->count();
        $rows  = $base->orderBy('deleted_at', 'desc')
                      ->forPage($page, $per)
                      ->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
            'pagination' => [
                'page' => $page,
                'per_page' => $per,
                'total' => $total,
            ],
        ]);
    }

    /**
     * Tree for a page.
     * Query:
     * - page_id=123 OR page_slug=about-us
     * - only_active=1
     */
    public function tree(Request $r)
    {
        $onlyActive = (int) $r->query('only_active', 0) === 1;
        $pageId = $this->resolvePageIdFromRequest($r);

        if ($pageId <= 0) {
            return response()->json(['error' => 'Missing page_id or page_slug'], 422);
        }

        $this->validatePage($pageId);

        $q = DB::table($this->table)
            ->where('page_id', $pageId)
            ->whereNull('deleted_at');

        if ($onlyActive) {
            $q->where('active', true);
        }

        $rows = $q->orderBy('position', 'asc')
                  ->orderBy('id', 'asc')
                  ->get();

        $byParent = [];
        foreach ($rows as $row) {
            $pid = $row->parent_id ?? 0;
            $byParent[$pid][] = $row;
        }

        $make = function ($pid) use (&$make, &$byParent) {
            $nodes = $byParent[$pid] ?? [];
            foreach ($nodes as $n) {
                $n->children = $make($n->id);
            }
            return $nodes;
        };

        return response()->json([
            'success' => true,
            'page_id' => $pageId,
            'data' => $make(0),
        ]);
    }

    /**
     * Resolve submenu slug (same logic as header menus):
     * - if page_url is set => redirect to that url
     * - else if page_slug  => redirect to "/{page_slug}"
     * - else               => redirect to "/{slug}"
     *
     * Query:
     * - slug=xyz
     * - (optional) page_id OR page_slug (if you want to scope / validate)
     */
    public function resolve(Request $r)
    {
        $slug = $this->normSlug($r->query('slug', ''));
        if ($slug === '') {
            return response()->json(['error' => 'Missing slug'], 422);
        }

        $pageId = $this->resolvePageIdFromRequest($r);

        $q = DB::table($this->table)
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->where('active', true);

        if ($pageId > 0) {
            $q->where('page_id', $pageId);
        }

        $menu = $q->first();

        if (!$menu) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $pageUrl  = $menu->page_url ?? null;
        $pageSlug = $menu->page_slug ?? null;

        if ($pageUrl && trim($pageUrl) !== '') {
            $redirectUrl = $pageUrl;
        } elseif ($pageSlug && trim($pageSlug) !== '') {
            $redirectUrl = '/' . ltrim($pageSlug, '/');
        } else {
            $redirectUrl = '/' . ltrim($menu->slug, '/');
        }

        return response()->json([
            'success'      => true,
            'submenu'      => $menu,
            'redirect_url' => $redirectUrl,
        ]);
    }

    /* ============================================
     | CRUD
     |============================================ */

    public function show(Request $r, $id)
    {
        $row = DB::table($this->table)
            ->where('id', (int) $id)
            ->first();

        if (!$row) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $row]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'page_id'        => 'required|integer|min:1',
            'title'          => 'required|string|max:150',
            'description'    => 'sometimes|nullable|string',
            'slug'           => 'sometimes|nullable|string|max:160',
            'shortcode'      => 'sometimes|nullable|string|max:100',
            'parent_id'      => 'sometimes|nullable|integer',
            'position'       => 'sometimes|integer|min:0',
            'active'         => 'sometimes|boolean',
            // target page fields (optional)
            'page_slug'      => 'sometimes|nullable|string|max:160',
            'page_shortcode' => 'sometimes|nullable|string|max:100',
            'page_url'       => 'sometimes|nullable|string|max:255',
        ]);

        $pageId = (int) $data['page_id'];
        $this->validatePage($pageId);

        $parentId = array_key_exists('parent_id', $data)
            ? ($data['parent_id'] === null ? null : (int) $data['parent_id'])
            : null;

        $this->validateParent($parentId, $pageId, null);

        // SUBMENU SLUG (auto from title if not passed)
        $slug = $this->normSlug($data['slug'] ?? $data['title'] ?? '');
        if ($slug === '') {
            return response()->json(['error' => 'Unable to generate slug'], 422);
        }

        // Idempotency: same slug (within same page) => return existing
        $existing = DB::table($this->table)
            ->where('page_id', $pageId)
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            return response()->json([
                'success'         => true,
                'data'            => $existing,
                'already_existed' => true,
                'message'         => 'Submenu already exists; not created again.',
            ], 200);
        }

        // SHORTCODE
        $submenuShortcode = null;
        if (!empty($data['shortcode'])) {
            $submenuShortcode = strtoupper(trim($data['shortcode']));
            $shortExists = DB::table($this->table)
                ->where('shortcode', $submenuShortcode)
                ->whereNull('deleted_at')
                ->exists();
            if ($shortExists) {
                return response()->json(['error' => 'Submenu shortcode already exists'], 422);
            }
        } else {
            $submenuShortcode = $this->generateSubmenuShortcode(null);
        }

        // PAGE FIELDS (optional)
        $pageSlug = null;
        if (array_key_exists('page_slug', $data)) {
            $norm = $this->normSlug($data['page_slug']);
            $pageSlug = $norm !== '' ? $norm : null;
        }

        $pageShortcode = null;
        if (array_key_exists('page_shortcode', $data)) {
            $val = trim((string) $data['page_shortcode']);
            $pageShortcode = $val !== '' ? $val : null;
        }

        $pageUrl = array_key_exists('page_url', $data)
            ? (trim((string) $data['page_url']) ?: null)
            : null;

        // Uniqueness for page_slug / page_shortcode (global, like header menus)
        if ($pageSlug) {
            $existsPageSlug = DB::table($this->table)
                ->where('page_slug', $pageSlug)
                ->whereNull('deleted_at')
                ->exists();
            if ($existsPageSlug) {
                return response()->json(['error' => 'Page slug already exists'], 422);
            }
        }

        if ($pageShortcode) {
            $existsPageShort = DB::table($this->table)
                ->where('page_shortcode', $pageShortcode)
                ->whereNull('deleted_at')
                ->exists();
            if ($existsPageShort) {
                return response()->json(['error' => 'Page shortcode already exists'], 422);
            }
        }

        // If soft-deleted with same (page_id + slug), revive
        $trashed = DB::table($this->table)
            ->where('page_id', $pageId)
            ->where('slug', $slug)
            ->whereNotNull('deleted_at')
            ->first();

        $now   = now();
        $actor = $this->actor($r);

        $position = array_key_exists('position', $data)
            ? (int) $data['position']
            : $this->nextPosition($pageId, $parentId);

        $active = array_key_exists('active', $data)
            ? (bool) $data['active']
            : true;

        if ($trashed) {
            DB::table($this->table)
                ->where('id', $trashed->id)
                ->update([
                    'page_id'         => $pageId,
                    'parent_id'       => $parentId,
                    'title'           => $data['title'],
                    'description'     => $data['description'] ?? null,
                    'slug'            => $slug,
                    'shortcode'       => $submenuShortcode,
                    'page_slug'       => $pageSlug,
                    'page_shortcode'  => $pageShortcode,
                    'page_url'        => $pageUrl,
                    'position'        => $position,
                    'active'          => $active,
                    'deleted_at'      => null,
                    'updated_at'      => $now,
                    'updated_by'      => $actor['id'] ?: null,
                    'updated_at_ip'   => $r->ip(),
                ]);

            $row = DB::table($this->table)->where('id', $trashed->id)->first();

            return response()->json([
                'success'  => true,
                'data'     => $row,
                'restored' => true,
            ]);
        }

        $id = DB::table($this->table)->insertGetId([
            'uuid'            => (string) Str::uuid(),
            'page_id'         => $pageId,
            'parent_id'       => $parentId,
            'title'           => $data['title'],
            'description'     => $data['description'] ?? null,
            'slug'            => $slug,
            'shortcode'       => $submenuShortcode,
            'page_slug'       => $pageSlug,
            'page_shortcode'  => $pageShortcode,
            'page_url'        => $pageUrl,
            'position'        => $position,
            'active'          => $active,
            'created_at'      => $now,
            'updated_at'      => $now,
            'created_by'      => $actor['id'] ?: null,
            'updated_by'      => $actor['id'] ?: null,
            'created_at_ip'   => $r->ip(),
            'updated_at_ip'   => $r->ip(),
        ]);

        $row = DB::table($this->table)->where('id', $id)->first();

        return response()->json(['success' => true, 'data' => $row], 201);
    }

    public function update(Request $r, $id)
    {
        $row = DB::table($this->table)
            ->where('id', (int) $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $data = $r->validate([
            'page_id'         => 'sometimes|integer|min:1', // not allowed to change (validated below)
            'title'           => 'sometimes|string|max:150',
            'description'     => 'sometimes|nullable|string',
            'slug'            => 'sometimes|nullable|string|max:160', // pass empty + regenerate_slug to regenerate
            'shortcode'       => 'sometimes|nullable|string|max:100',
            'parent_id'       => 'sometimes|nullable|integer',
            'position'        => 'sometimes|integer|min:0',
            'active'          => 'sometimes|boolean',
            'regenerate_slug' => 'sometimes|boolean',
            // target page fields
            'page_slug'       => 'sometimes|nullable|string|max:160',
            'page_shortcode'  => 'sometimes|nullable|string|max:100',
            'page_url'        => 'sometimes|nullable|string|max:255',
        ]);

        // page_id cannot change (safety)
        $pageId = (int) ($row->page_id ?? 0);
        if (array_key_exists('page_id', $data) && (int)$data['page_id'] !== $pageId) {
            return response()->json(['error' => 'Changing page_id is not allowed'], 422);
        }

        $this->validatePage($pageId);

        $parentId = array_key_exists('parent_id', $data)
            ? ($data['parent_id'] === null ? null : (int) $data['parent_id'])
            : ($row->parent_id ?? null);

        $this->validateParent($parentId, $pageId, (int) $row->id);

        // Handle submenu slug (strict, no "-2" auto)
        $slug = $row->slug;

        if (
            array_key_exists('slug', $data) ||
            !empty($data['regenerate_slug']) ||
            (isset($data['title']) && $data['title'] !== $row->title && !array_key_exists('slug', $data))
        ) {
            if (
                !empty($data['regenerate_slug']) ||
                (array_key_exists('slug', $data) && trim((string) $data['slug']) === '')
            ) {
                $base = $this->normSlug($data['title'] ?? $row->title ?? 'submenu');
                $slug = $base;
            } elseif (array_key_exists('slug', $data)) {
                $slug = $this->normSlug($data['slug']);
            }

            if ($slug === '') {
                return response()->json(['error' => 'Unable to generate slug'], 422);
            }

            $existsSlug = DB::table($this->table)
                ->where('page_id', $pageId)
                ->where('slug', $slug)
                ->where('id', '!=', $row->id)
                ->whereNull('deleted_at')
                ->exists();

            if ($existsSlug) {
                return response()->json(['error' => 'Slug already in use for this page'], 422);
            }
        }

        // SHORTCODE
        $submenuShortcode = $row->shortcode;
        if (array_key_exists('shortcode', $data)) {
            $val = trim((string) $data['shortcode']);
            if ($val === '') {
                $submenuShortcode = $this->generateSubmenuShortcode((int) $row->id);
            } else {
                $val = strtoupper($val);
                $existsShort = DB::table($this->table)
                    ->where('shortcode', $val)
                    ->where('id', '!=', $row->id)
                    ->whereNull('deleted_at')
                    ->exists();
                if ($existsShort) {
                    return response()->json(['error' => 'Submenu shortcode already in use'], 422);
                }
                $submenuShortcode = $val;
            }
        }

        // PAGE FIELDS
        $pageSlug = $row->page_slug ?? null;
        if (array_key_exists('page_slug', $data)) {
            $norm = $this->normSlug($data['page_slug']);
            $pageSlug = $norm !== '' ? $norm : null;

            if ($pageSlug) {
                $existsPageSlug = DB::table($this->table)
                    ->where('page_slug', $pageSlug)
                    ->where('id', '!=', $row->id)
                    ->whereNull('deleted_at')
                    ->exists();
                if ($existsPageSlug) {
                    return response()->json(['error' => 'Page slug already in use'], 422);
                }
            }
        }

        $pageShortcode = $row->page_shortcode ?? null;
        if (array_key_exists('page_shortcode', $data)) {
            $val = trim((string) $data['page_shortcode']);
            $pageShortcode = $val !== '' ? $val : null;

            if ($pageShortcode) {
                $existsPageShort = DB::table($this->table)
                    ->where('page_shortcode', $pageShortcode)
                    ->where('id', '!=', $row->id)
                    ->whereNull('deleted_at')
                    ->exists();
                if ($existsPageShort) {
                    return response()->json(['error' => 'Page shortcode already in use'], 422);
                }
            }
        }

        $pageUrl = array_key_exists('page_url', $data)
            ? (trim((string) $data['page_url']) ?: null)
            : ($row->page_url ?? null);

        $upd = [
            'parent_id'       => $parentId,
            'title'           => $data['title'] ?? $row->title,
            'description'     => array_key_exists('description', $data) ? $data['description'] : $row->description,
            'slug'            => $slug,
            'shortcode'       => $submenuShortcode,
            'page_slug'       => $pageSlug,
            'page_shortcode'  => $pageShortcode,
            'page_url'        => $pageUrl,
            'position'        => array_key_exists('position', $data) ? (int) $data['position'] : (int) $row->position,
            'active'          => array_key_exists('active', $data) ? (bool) $data['active'] : (bool) $row->active,
            'updated_at'      => now(),
            'updated_by'      => $this->actor($r)['id'] ?: null,
            'updated_at_ip'   => $r->ip(),
        ];

        DB::table($this->table)
            ->where('id', $row->id)
            ->update($upd);

        $fresh = DB::table($this->table)
            ->where('id', $row->id)
            ->first();

        return response()->json(['success' => true, 'data' => $fresh]);
    }

    public function destroy(Request $r, $id)
    {
        $exists = DB::table($this->table)
            ->where('id', (int) $id)
            ->whereNull('deleted_at')
            ->exists();

        if (!$exists) {
            return response()->json(['error' => 'Not found'], 404);
        }

        DB::table($this->table)
            ->where('id', (int) $id)
            ->update([
                'deleted_at'    => now(),
                'updated_at'    => now(),
                'updated_by'    => $this->actor($r)['id'] ?: null,
                'updated_at_ip' => $r->ip(),
            ]);

        return response()->json(['success' => true, 'message' => 'Moved to bin']);
    }

    public function restore(Request $r, $id)
    {
        $ok = DB::table($this->table)
            ->where('id', (int) $id)
            ->whereNotNull('deleted_at')
            ->exists();

        if (!$ok) {
            return response()->json(['error' => 'Not found in bin'], 404);
        }

        DB::table($this->table)
            ->where('id', (int) $id)
            ->update([
                'deleted_at'    => null,
                'updated_at'    => now(),
                'updated_by'    => $this->actor($r)['id'] ?: null,
                'updated_at_ip' => $r->ip(),
            ]);

        return response()->json(['success' => true, 'message' => 'Restored']);
    }

    public function forceDelete(Request $r, $id)
    {
        $exists = DB::table($this->table)
            ->where('id', (int) $id)
            ->exists();

        if (!$exists) {
            return response()->json(['error' => 'Not found'], 404);
        }

        DB::table($this->table)
            ->where('id', (int) $id)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Deleted permanently']);
    }

    public function toggleActive(Request $r, $id)
    {
        $row = DB::table($this->table)
            ->where('id', (int) $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            return response()->json(['error' => 'Not found'], 404);
        }

        DB::table($this->table)
            ->where('id', (int) $id)
            ->update([
                'active'        => !$row->active,
                'updated_at'    => now(),
                'updated_by'    => $this->actor($r)['id'] ?: null,
                'updated_at_ip' => $r->ip(),
            ]);

        return response()->json(['success' => true, 'message' => 'Status updated']);
    }

    /**
     * Reorder items (no parent change allowed).
     * Body:
     * {
     *   "orders": [
     *     {"id": 5, "position": 0, "parent_id": null},
     *     {"id": 6, "position": 1, "parent_id": null}
     *   ]
     * }
     */
    public function reorder(Request $r)
    {
        $payload = $r->validate([
            'orders'             => 'required|array|min:1',
            'orders.*.id'        => 'required|integer',
            'orders.*.position'  => 'required|integer|min:0',
            'orders.*.parent_id' => 'nullable|integer',
        ]);

        DB::beginTransaction();

        try {
            foreach ($payload['orders'] as $o) {
                $id  = (int) $o['id'];
                $pos = (int) $o['position'];

                $row = DB::table($this->table)
                    ->where('id', $id)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$row) {
                    continue;
                }

                $currentPid = $row->parent_id === null ? null : (int) $row->parent_id;

                $incomingPid = array_key_exists('parent_id', $o)
                    ? ($o['parent_id'] === null ? null : (int) $o['parent_id'])
                    : $currentPid;

                if ($incomingPid !== $currentPid) {
                    throw new \RuntimeException("Parent change not allowed for id {$id}");
                }

                DB::table($this->table)
                    ->where('id', $id)
                    ->update([
                        'position'      => $pos,
                        'updated_at'    => now(),
                        'updated_by'    => $this->actor($r)['id'] ?: null,
                        'updated_at_ip' => $r->ip(),
                    ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Reorder failed',
                'details' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['success' => true, 'message' => 'Order updated']);
    }

    /**
     * Public tree:
     * - page_id=123 OR page_slug=about-us
     * - top_level=1 (optional)
     */
    public function publicTree(Request $r)
    {
        $onlyTopLevel = (int) $r->query('top_level', 0) === 1;

        $pageId = $this->resolvePageIdFromRequest($r);
        if ($pageId <= 0) {
            return response()->json(['error' => 'Missing page_id or page_slug'], 422);
        }

        $this->validatePage($pageId);

        $q = DB::table($this->table)
            ->where('page_id', $pageId)
            ->whereNull('deleted_at')
            ->where('active', true);

        if ($onlyTopLevel) {
            $q->whereNull('parent_id');
        }

        $rows = $q->orderBy('position', 'asc')
                  ->orderBy('id', 'asc')
                  ->get();

        $byParent = [];
        foreach ($rows as $row) {
            $pid = $row->parent_id ?? 0;
            $byParent[$pid][] = $row;
        }

        $make = function ($pid) use (&$make, &$byParent) {
            $nodes = $byParent[$pid] ?? [];
            foreach ($nodes as $n) {
                $n->children = $make($n->id);
            }
            return $nodes;
        };

        return response()->json([
            'success' => true,
            'page_id' => $pageId,
            'data' => $make(0),
        ]);
    }

    /**
 * Pages list for dropdowns (id + title + slug).
 * GET /api/page-submenus/pages?limit=500&q=...
 */
public function pages(Request $r)
{
    $q = trim((string) $r->query('q', ''));
    $limit = min(1000, max(10, (int) $r->query('limit', 500)));

    // Be defensive in case your pages table uses different column names
    $titleCol = Schema::hasColumn('pages', 'title') ? 'title'
              : (Schema::hasColumn('pages', 'name') ? 'name' : null);

    $slugCol  = Schema::hasColumn('pages', 'slug') ? 'slug'
              : (Schema::hasColumn('pages', 'page_slug') ? 'page_slug' : null);

    $qb = DB::table('pages')
        ->whereNull('deleted_at')
        ->select([
            'id',
            DB::raw(($titleCol ? $titleCol : "''") . " as title"),
            DB::raw(($slugCol  ? $slugCol  : "''") . " as slug"),
        ]);

    if ($q !== '') {
        $qb->where(function ($x) use ($q, $titleCol, $slugCol) {
            if ($titleCol) $x->orWhere($titleCol, 'like', "%{$q}%");
            if ($slugCol)  $x->orWhere($slugCol,  'like', "%{$q}%");
            $x->orWhere('id', (int) $q); // allows searching by id
        });
    }

    // Optional: filter active pages if column exists and query param used
    if (Schema::hasColumn('pages', 'active') && $r->query('only_active', null) !== null) {
        $qb->where('active', (int)$r->query('only_active') === 1);
    }

    // Ordering
    if ($titleCol) {
        $qb->orderBy($titleCol, 'asc');
    } else {
        $qb->orderBy('id', 'asc');
    }

    $rows = $qb->limit($limit)->get();

    return response()->json([
        'success' => true,
        'data'    => $rows,
    ]);
}

}
