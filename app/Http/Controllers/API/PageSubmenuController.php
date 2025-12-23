<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

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

    /**
     * Enforce that ONLY ONE destination option is set:
     * - page_url OR page_slug OR page_shortcode OR includable_path
     */
    private function enforceSingleDestination(?string $pageUrl, ?string $pageSlug, ?string $pageShortcode, ?string $includablePath)
    {
        $filled = array_filter([
            'page_url'        => $pageUrl,
            'page_slug'       => $pageSlug,
            'page_shortcode'  => $pageShortcode,
            'includable_path' => $includablePath,
        ], fn ($v) => $v !== null && trim((string) $v) !== '');

        if (count($filled) > 1) {
            return response()->json([
                'message' => 'Choose only one destination option (URL OR Slug OR Shortcode OR Includable Path).',
                'errors'  => [
                    'page_url'        => ['Only one destination field is allowed.'],
                    'page_slug'       => ['Only one destination field is allowed.'],
                    'page_shortcode'  => ['Only one destination field is allowed.'],
                    'includable_path' => ['Only one destination field is allowed.'],
                ],
            ], 422);
        }

        return null;
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
                  ->orWhere('page_url', 'like', "%{$q}%")
                  ->orWhere('includable_path', 'like', "%{$q}%");
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
            'page_id'         => 'required|integer|min:1',
            'title'           => 'required|string|max:150',
            'description'     => 'sometimes|nullable|string',
            'slug'            => 'sometimes|nullable|string|max:160',
            'shortcode'       => 'sometimes|nullable|string|max:100',
            'parent_id'       => 'sometimes|nullable|integer',
            'position'        => 'sometimes|integer|min:0',
            'active'          => 'sometimes|boolean',
            // target page fields (optional)
            'page_slug'       => 'sometimes|nullable|string|max:160',
            'page_shortcode'  => 'sometimes|nullable|string|max:100',
            'page_url'        => 'sometimes|nullable|string|max:255',
            // includable path (optional)
            'includable_path' => 'sometimes|nullable|string|max:255',
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

        $includablePath = array_key_exists('includable_path', $data)
            ? (trim((string) $data['includable_path']) ?: null)
            : null;

        // ✅ only one destination allowed
        $singleDestErr = $this->enforceSingleDestination($pageUrl, $pageSlug, $pageShortcode, $includablePath);
        if ($singleDestErr) return $singleDestErr;

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
                    'page_id'          => $pageId,
                    'parent_id'        => $parentId,
                    'title'            => $data['title'],
                    'description'      => $data['description'] ?? null,
                    'slug'             => $slug,
                    'shortcode'        => $submenuShortcode,
                    'page_slug'        => $pageSlug,
                    'page_shortcode'   => $pageShortcode,
                    'includable_path'  => $includablePath,
                    'page_url'         => $pageUrl,
                    'position'         => $position,
                    'active'           => $active,
                    'deleted_at'       => null,
                    'updated_at'       => $now,
                    'updated_by'       => $actor['id'] ?: null,
                    'updated_at_ip'    => $r->ip(),
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
            'includable_path' => $includablePath,
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
            'page_id'          => 'sometimes|integer|min:1', // not allowed to change (validated below)
            'title'            => 'sometimes|string|max:150',
            'description'      => 'sometimes|nullable|string',
            'slug'             => 'sometimes|nullable|string|max:160', // pass empty + regenerate_slug to regenerate
            'shortcode'        => 'sometimes|nullable|string|max:100',
            'parent_id'        => 'sometimes|nullable|integer',
            'position'         => 'sometimes|integer|min:0',
            'active'           => 'sometimes|boolean',
            'regenerate_slug'  => 'sometimes|boolean',
            // target page fields
            'page_slug'        => 'sometimes|nullable|string|max:160',
            'page_shortcode'   => 'sometimes|nullable|string|max:100',
            'page_url'         => 'sometimes|nullable|string|max:255',
            // includable path
            'includable_path'  => 'sometimes|nullable|string|max:255',
        ]);

        // page_id cannot change (safety)
        $pageId = (int) ($row->page_id ?? 0);
        if (array_key_exists('page_id', $data) && (int) $data['page_id'] !== $pageId) {
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

        $includablePath = array_key_exists('includable_path', $data)
            ? (trim((string) $data['includable_path']) ?: null)
            : ($row->includable_path ?? null);

        // ✅ only one destination allowed
        $singleDestErr = $this->enforceSingleDestination($pageUrl, $pageSlug, $pageShortcode, $includablePath);
        if ($singleDestErr) return $singleDestErr;

        $upd = [
            'parent_id'        => $parentId,
            'title'            => $data['title'] ?? $row->title,
            'description'      => array_key_exists('description', $data) ? $data['description'] : $row->description,
            'slug'             => $slug,
            'shortcode'        => $submenuShortcode,
            'page_slug'        => $pageSlug,
            'page_shortcode'   => $pageShortcode,
            'includable_path'  => $includablePath,
            'page_url'         => $pageUrl,
            'position'         => array_key_exists('position', $data) ? (int) $data['position'] : (int) $row->position,
            'active'           => array_key_exists('active', $data) ? (bool) $data['active'] : (bool) $row->active,
            'updated_at'       => now(),
            'updated_by'       => $this->actor($r)['id'] ?: null,
            'updated_at_ip'    => $r->ip(),
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
            $qb->where('active', (int) $r->query('only_active') === 1);
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

    /**
     * ✅ Includables list for dropdown (dynamic).
     * Returns dot paths: modules.xyz.abc
     *
     * GET /api/page-submenus/includables?q=editor&limit=500&refresh=1
     */
    public function includables(Request $r)
    {
        $q = trim((string) $r->query('q', ''));
        $limit = min(2000, max(10, (int) $r->query('limit', 1000)));
        $refresh = (int) $r->query('refresh', 0) === 1;

        $cacheKey = 'page_submenus.includables.v1';

        $list = $refresh
            ? null
            : Cache::get($cacheKey);

        if (!is_array($list)) {
            $root = base_path('resources/views/modules');
            $list = [];

            if (File::exists($root)) {

                // top-level dirs under /modules to exclude from dropdown
                $excludeTopDirs = [
                    'header', 'footer', 'layouts', 'partials', 'components', 'auth', 'common', 'ui',
                ];

                foreach (File::allFiles($root) as $file) {
                    $full = $file->getPathname();

                    // only .blade.php
                    if (!Str::endsWith($full, '.blade.php')) continue;

                    // relative path from modules/
                    $rel = str_replace($root . DIRECTORY_SEPARATOR, '', $full);

                    // normalize
                    $rel = str_replace('\\', '/', $rel);

                    // exclude top-level directory buckets
                    $firstSeg = explode('/', $rel)[0] ?? '';
                    if ($firstSeg && in_array($firstSeg, $excludeTopDirs, true)) continue;

                    // exclude partial-style files like _row.blade.php
                    $baseName = basename($rel);
                    if (Str::startsWith($baseName, '_')) continue;

                    // remove ".blade.php"
                    $noExt = substr($rel, 0, -10);

                    // to dot notation and prefix with modules.
                    $dot = 'modules.' . str_replace('/', '.', $noExt);

                    $list[] = $dot;
                }

                $list = array_values(array_unique($list));
                sort($list, SORT_NATURAL | SORT_FLAG_CASE);
            }

            // cache for 10 minutes (reduce disk scan)
            Cache::put($cacheKey, $list, 600);
        }

        // filter
        if ($q !== '') {
            $qq = mb_strtolower($q);
            $list = array_values(array_filter($list, function ($p) use ($qq) {
                return str_contains(mb_strtolower((string) $p), $qq);
            }));
        }

        // limit
        if (count($list) > $limit) {
            $list = array_slice($list, 0, $limit);
        }

        return response()->json([
            'success' => true,
            'data'    => $list,
        ]);
    }

    /**
 * ✅ Render submenu destination (AJAX friendly)
 *
 * GET /api/public/page-submenus/render?slug=child-slug
 * Optional scoping:
 *  - page_id=123 OR page_slug=about-us (to ensure submenu belongs to that page)
 *
 * Returns JSON:
 *  - type: includable | page | url
 *  - html: rendered HTML fragment (for includable/page)
 *  - url: target url (for url type)
 */
public function renderPublic(Request $r)
{
    $slug = $this->normSlug($r->query('slug', ''));
    if ($slug === '') {
        return response()->json(['success' => false, 'error' => 'Missing slug'], 422);
    }

    // Optional: scope to page
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
        return response()->json(['success' => false, 'error' => 'Submenu not found'], 404);
    }

    // Destination fields
    $pageUrl        = trim((string)($menu->page_url ?? ''));
    $pageSlug       = trim((string)($menu->page_slug ?? ''));
    $pageShortcode  = trim((string)($menu->page_shortcode ?? ''));
    $includablePath = trim((string)($menu->includable_path ?? ''));

    // Helper for iframe hint (same origin)
    $sameOrigin = function (string $u) use ($r): bool {
        $u = trim($u);
        if ($u === '') return false;

        // relative URLs are same-origin
        if (!preg_match('/^https?:\/\//i', $u)) return true;

        $host = parse_url($u, PHP_URL_HOST);
        if (!$host) return false;

        return strtolower($host) === strtolower($r->getHost());
    };

    /**
     * 1) ✅ includable_path (modules.*)
     * Important:
     * - If the blade is a "partial" (plain HTML), view()->render() works.
     * - If the blade is "section-only" (defines @section('content') etc), render() will be empty,
     *   so we fall back to renderSections() and pick a section.
     */
    if ($includablePath !== '') {

        if (!Str::startsWith($includablePath, 'modules.')) {
            return response()->json([
                'success' => false,
                'error'   => 'Invalid includable_path (only modules.* allowed)'
            ], 422);
        }

        if (!View::exists($includablePath)) {
            return response()->json([
                'success' => false,
                'error'   => 'Blade view not found',
                'path'    => $includablePath
            ], 404);
        }

        try {
            $vf = app('view');

            // reset any previous sections/stacks from earlier renders in same request
            if (method_exists($vf, 'flushState')) {
                $vf->flushState();
            }

            $viewObj = view($includablePath);

            // First try: like @include (partials)
            $html = $viewObj->render();

            // Capture pushed stacks (if module uses @push)
            $styles  = method_exists($vf, 'yieldPushContent') ? $vf->yieldPushContent('styles')  : '';
            $scripts = method_exists($vf, 'yieldPushContent') ? $vf->yieldPushContent('scripts') : '';

            // Fallback: section-only blades (no visible HTML on render)
            $pickedSection = null;
            $sections = [];

            // If render() looks empty OR it looks like a full layout, try sections
            $looksEmpty = trim(strip_tags((string)$html)) === '';
            $looksLikeFullLayout =
                stripos((string)$html, '<html') !== false ||
                stripos((string)$html, '<body') !== false;

            if ($looksEmpty || $looksLikeFullLayout) {
                // flush and re-render sections cleanly (avoid mixing render()+renderSections())
                if (method_exists($vf, 'flushState')) {
                    $vf->flushState();
                }

                $sections = $viewObj->renderSections();

                // Common section keys used in your pages/modules
                $candidates = ['content', 'page-content', 'main', 'body'];

                foreach ($candidates as $sec) {
                    $candidateHtml = $sections[$sec] ?? '';
                    if (trim(strip_tags((string)$candidateHtml)) !== '') {
                        $html = $candidateHtml;
                        $pickedSection = $sec;
                        break;
                    }
                }

                // Re-capture stacks after the sections render (pushes happen during render)
                $styles  = method_exists($vf, 'yieldPushContent') ? $vf->yieldPushContent('styles')  : $styles;
                $scripts = method_exists($vf, 'yieldPushContent') ? $vf->yieldPushContent('scripts') : $scripts;

                // Optional: if module used @section('scripts') instead of @push, append it
                if (isset($sections['scripts']) && trim((string)$sections['scripts']) !== '') {
                    $scripts = (string)$scripts . "\n" . (string)$sections['scripts'];
                }
                if (isset($sections['styles']) && trim((string)$sections['styles']) !== '') {
                    $styles = (string)$styles . "\n" . (string)$sections['styles'];
                }
            }

            return response()->json([
                'success' => true,
                'type'    => 'includable',
                'title'   => $menu->title ?? 'Submenu',
                'meta'    => [
                    'submenu_slug'    => $menu->slug ?? null,
                    'submenu_id'      => $menu->id ?? null,
                    'includable'      => $includablePath,
                    'section_used'    => $pickedSection,
                    'render_was_empty'=> $looksEmpty,
                ],
                'assets'  => [
                    'styles'  => $styles ?: '',
                    'scripts' => $scripts ?: '',
                ],
                'html'    => (string)($html ?: ''),
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Failed to render includable view',
                'details' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * 2) ✅ page_slug / page_shortcode => load page content_html from pages table
     */
    if ($pageSlug !== '' || $pageShortcode !== '') {
        $p = DB::table('pages')->whereNull('deleted_at');

        if ($pageSlug !== '') {
            $p->where('slug', $this->normSlug($pageSlug));
        } else {
            $p->where('shortcode', $pageShortcode);
        }

        $page = $p->first();

        if (!$page) {
            return response()->json(['success' => false, 'error' => 'Target page not found'], 404);
        }

        $html = $page->content_html ?? '';

        return response()->json([
            'success' => true,
            'type'    => 'page',
            'title'   => $page->title ?? ($menu->title ?? 'Page'),
            'meta'    => [
                'submenu_slug' => $menu->slug ?? null,
                'submenu_id'   => $menu->id ?? null,
                'page_id'      => $page->id ?? null,
                'page_slug'    => $page->slug ?? null,
                'shortcode'    => $page->shortcode ?? null,
            ],
            'html' => (string)$html,
        ]);
    }

    /**
     * 3) ✅ page_url => return url (frontend will iframe it)
     */
    if ($pageUrl !== '') {
        return response()->json([
            'success'     => true,
            'type'        => 'url',
            'title'       => $menu->title ?? 'Link',
            'meta'        => [
                'submenu_slug' => $menu->slug ?? null,
                'submenu_id'   => $menu->id ?? null,
            ],
            'url'         => $pageUrl,
            'same_origin' => $sameOrigin($pageUrl),
        ]);
    }

    return response()->json([
        'success' => false,
        'error'   => 'No destination configured for this submenu',
    ], 422);
}


}
