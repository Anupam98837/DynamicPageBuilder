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

    /** Activity log module name */
    private string $logModule = 'page_submenus';

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

    private function trackedFields(): array
    {
        return [
            'id', 'uuid',
            'page_id', 'department_id', 'header_menu_id',
            'parent_id',
            'title', 'description',
            'slug', 'shortcode',
            'page_slug', 'page_shortcode',
            'includable_path', 'page_url',
            'position', 'active',
            'deleted_at',
        ];
    }

    private function pickFields($row, array $keys): array
    {
        $arr = is_array($row) ? $row : (array) $row;
        $out = [];
        foreach ($keys as $k) {
            if (array_key_exists($k, $arr)) {
                $out[$k] = $arr[$k];
            }
        }
        return $out;
    }

    private function computeChangedFields(array $old, array $new, array $keys): array
    {
        $changed = [];
        foreach ($keys as $k) {
            $ov = $old[$k] ?? null;
            $nv = $new[$k] ?? null;

            // non-strict comparison avoids noise from DB string/int casts
            if ($ov != $nv) {
                $changed[] = $k;
            }
        }
        return $changed;
    }

    private function toJsonOrNull($v): ?string
    {
        if ($v === null) return null;
        if (is_string($v)) return $v; // already JSON
        try {
            return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Insert activity log row (never breaks primary flow).
     */
    private function logActivity(
        Request $r,
        string $activity,
        ?string $note = null,
        ?int $recordId = null,
        ?array $changedFields = null,
        $oldValues = null,
        $newValues = null,
        ?string $tableName = null
    ): void {
        try {
            $actor = $this->actor($r);

            DB::table('user_data_activity_log')->insert([
                'performed_by'      => (int) ($actor['id'] ?: 0),
                'performed_by_role' => $actor['role'] ?: null,
                'ip'                => $r->ip(),
                'user_agent'        => mb_substr((string) ($r->userAgent() ?? ''), 0, 512),
                'activity'          => mb_substr($activity, 0, 50),
                'module'            => mb_substr($this->logModule, 0, 100),
                'table_name'        => mb_substr((string) ($tableName ?: $this->table), 0, 128),
                'record_id'         => $recordId,
                'changed_fields'    => $changedFields ? $this->toJsonOrNull(array_values($changedFields)) : null,
                'old_values'        => $oldValues !== null ? $this->toJsonOrNull($oldValues) : null,
                'new_values'        => $newValues !== null ? $this->toJsonOrNull($newValues) : null,
                'log_note'          => $note,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            // swallow to avoid breaking any existing functionality
        }
    }

    private function normSlug(?string $s): string
    {
        $s = (string) $s;
        $s = trim($s);
        $s = $s === '' ? '' : Str::slug($s, '-');
        return $s;
    }

    /** ✅ Ensure page exists (and not soft-deleted) — NOW nullable allowed */
    private function validatePage(?int $pageId): void
    {
        // ✅ nullable allowed
        if ($pageId === null || $pageId <= 0) return;

        $ok = DB::table('pages')
            ->where('id', $pageId)
            ->whereNull('deleted_at')
            ->exists();

        if (!$ok) {
            abort(response()->json(['error' => 'Invalid page_id'], 422));
        }
    }

    /** ✅ Ensure department exists (and not soft-deleted) */
    private function validateDepartment(?int $deptId): void
    {
        if ($deptId === null || $deptId <= 0) return;

        $ok = DB::table('departments')
            ->where('id', $deptId)
            ->whereNull('deleted_at')
            ->exists();

        if (!$ok) {
            abort(response()->json(['error' => 'Invalid department_id'], 422));
        }
    }

    /** ✅ Ensure header menu exists (and not soft-deleted) */
    private function validateHeaderMenu(?int $headerMenuId): void
    {
        // nullable allowed ✅
        if ($headerMenuId === null || $headerMenuId <= 0) return;

        $ok = DB::table('header_menus')
            ->where('id', $headerMenuId)
            ->whereNull('deleted_at')
            ->exists();

        if (!$ok) {
            abort(response()->json(['error' => 'Invalid header_menu_id'], 422));
        }
    }

    /** ✅ (Optional) read department_id from pages if that column exists */
    private function pageDepartmentId(int $pageId): ?int
    {
        if (!Schema::hasColumn('pages', 'department_id')) return null;

        $val = DB::table('pages')
            ->where('id', $pageId)
            ->whereNull('deleted_at')
            ->value('department_id');

        return $val ? (int) $val : null;
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
     * ✅ Guard that parent exists, is not self,
     * AND belongs to the same scope.
     * NOW supports nullable page_id + scoped by header_menu_id too.
     */
    private function validateParent(?int $parentId, ?int $pageId, ?int $headerMenuId, ?int $selfId = null): void
    {
        if ($parentId === null) {
            return;
        }

        if ($selfId !== null && $parentId === $selfId) {
            abort(response()->json(['error' => 'Parent cannot be self'], 422));
        }

        $q = DB::table($this->table)
            ->where('id', $parentId)
            ->whereNull('deleted_at');

        // ✅ page_id scope (nullable safe)
        if ($pageId !== null && $pageId > 0) {
            $q->where('page_id', $pageId);
        } else {
            $q->whereNull('page_id');
        }

        // ✅ header_menu_id scope (nullable safe)
        if (Schema::hasColumn($this->table, 'header_menu_id')) {
            if ($headerMenuId !== null && $headerMenuId > 0) {
                $q->where('header_menu_id', $headerMenuId);
            } else {
                $q->whereNull('header_menu_id');
            }
        }

        $ok = $q->exists();

        if (!$ok) {
            abort(response()->json(['error' => 'Invalid parent_id for this scope'], 422));
        }
    }

    /** ✅ Next position among siblings (scoped to page_id + header_menu_id) — nullable safe */
    private function nextPosition(?int $pageId, ?int $headerMenuId, ?int $parentId): int
    {
        $q = DB::table($this->table)
            ->whereNull('deleted_at');

        // ✅ page_id scope (nullable safe)
        if ($pageId !== null && $pageId > 0) {
            $q->where('page_id', $pageId);
        } else {
            $q->whereNull('page_id');
        }

        // ✅ header_menu_id scope (nullable safe)
        if (Schema::hasColumn($this->table, 'header_menu_id')) {
            if ($headerMenuId !== null && $headerMenuId > 0) {
                $q->where('header_menu_id', $headerMenuId);
            } else {
                $q->whereNull('header_menu_id');
            }
        }

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

    /** Resolve header_menu_id from query param */
    private function resolveHeaderMenuIdFromRequest(Request $r): int
    {
        return (int) $r->query('header_menu_id', 0);
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
     | ✅ Header Menus (Dropdown API)
     |============================================ */

    /**
     * ✅ Header Menus dropdown list
     * GET /api/page-submenus/header-menus?limit=500&q=&top_level=1&only_active=1
     */
    public function headerMenus(Request $r)
    {
        $q = trim((string) $r->query('q', ''));
        $limit = min(1000, max(10, (int) $r->query('limit', 500)));

        $onlyActive = $r->query('only_active', null) !== null
            ? ((int) $r->query('only_active') === 1)
            : false;

        // default top_level=1 (parents)
        $topLevel = (int) $r->query('top_level', 1) === 1;

        $qb = DB::table('header_menus as hm')
            ->leftJoin('header_menus as p', 'p.id', '=', 'hm.parent_id')
            ->whereNull('hm.deleted_at')
            ->select([
                'hm.id',
                'hm.uuid',
                'hm.title',
                'hm.slug',
                'hm.parent_id',
                DB::raw("COALESCE(p.title,'') as parent_title"),
                'hm.position',
                'hm.active',
            ]);

        if ($topLevel) {
            $qb->whereNull('hm.parent_id');
        }

        if ($onlyActive) {
            $qb->where('hm.active', true);
        }

        if ($q !== '') {
            $qb->where(function ($x) use ($q) {
                $x->where('hm.title', 'like', "%{$q}%")
                  ->orWhere('hm.slug', 'like', "%{$q}%")
                  ->orWhere('hm.id', (int) $q);
            });
        }

        $rows = $qb->orderBy('hm.position', 'asc')
                   ->orderBy('hm.id', 'asc')
                   ->limit($limit)
                   ->get();

        return response()->json([
            'success' => true,
            'data'    => $rows,
        ]);
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
        $pageId = $r->query('page_id', 'any'); // 'any' | null | int

        // ✅ header_menu_id filter
        $headerMenuId = $r->query('header_menu_id', 'any'); // 'any' | null | int

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

        // ✅ page_id filter (nullable supported)
        if ($pageId === null || $pageId === 'null') {
            $base->whereNull('page_id');
        } elseif ($pageId !== 'any' && $pageId !== '') {
            $base->where('page_id', (int) $pageId);
        }

        if ($parentId === null || $parentId === 'null') {
            $base->whereNull('parent_id');
        } elseif ($parentId !== 'any') {
            $base->where('parent_id', (int) $parentId);
        }

        // ✅ header_menu_id filter (nullable)
        if (Schema::hasColumn($this->table, 'header_menu_id')) {
            if ($headerMenuId === null || $headerMenuId === 'null') {
                $base->whereNull('header_menu_id');
            } elseif ($headerMenuId !== 'any' && $headerMenuId !== '') {
                $base->where('header_menu_id', (int) $headerMenuId);
            }
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

        $pageId = $this->resolvePageIdFromRequest($r);         // optional now
        $headerMenuId = $this->resolveHeaderMenuIdFromRequest($r); // ✅ NEW MAIN

        // ✅ NEW: allow header_menu_id as main scope
        if ($pageId <= 0 && $headerMenuId <= 0) {
            return response()->json([
                'error' => 'Missing header_menu_id OR page_id/page_slug'
            ], 422);
        }

        if ($pageId > 0) {
            $this->validatePage($pageId);
        }

        if (Schema::hasColumn($this->table, 'header_menu_id') && $headerMenuId > 0) {
            $this->validateHeaderMenu($headerMenuId);
        } else {
            $headerMenuId = 0;
        }

        $q = DB::table($this->table)
            ->whereNull('deleted_at');

        if ($onlyActive) {
            $q->where('active', true);
        }

        /**
         * ✅ NEW SCOPING RULE:
         * If header_menu_id exists => scope by header menu
         * Page ID is optional (if present, allow both page-specific + global(page_id NULL))
         */
        if ($headerMenuId > 0 && Schema::hasColumn($this->table, 'header_menu_id')) {

            $q->where('header_menu_id', $headerMenuId);

            if ($pageId > 0) {
                $q->where(function ($x) use ($pageId) {
                    $x->where('page_id', $pageId)
                      ->orWhereNull('page_id');
                });
            } else {
                $q->whereNull('page_id');
            }

        } else {
            // fallback: old behavior
            if ($pageId > 0) {
                $q->where('page_id', $pageId);
            } else {
                $q->whereNull('page_id');
            }
        }

        $rows = $q->orderBy('position', 'asc')
                  ->orderBy('id', 'asc')
                  ->get();

        // build tree
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
            'scope'   => [
                'page_id' => $pageId ?: null,
                'header_menu_id' => $headerMenuId ?: null,
            ],
            'data' => $make(0),
        ]);
    }

    /**
     * Resolve submenu slug (same logic as header menus):
     * - if page_url is set => redirect to that url
     * - else if page_slug  => redirect to "/{page_slug}"
     * - else               => redirect to "/{slug}"
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
            // ✅ CHANGED: page_id nullable now
            'page_id'         => 'sometimes|nullable|integer|min:1',

            // ✅ optional nullable header_menu_id
            'header_menu_id'  => 'sometimes|nullable|integer|min:1',

            'department_id'   => 'sometimes|nullable|integer|min:1|exists:departments,id',
            'title'           => 'required|string|max:150',
            'description'     => 'sometimes|nullable|string',
            'slug'            => 'sometimes|nullable|string|max:160',
            'shortcode'       => 'sometimes|nullable|string|max:100',
            'parent_id'       => 'sometimes|nullable|integer',
            'position'        => 'sometimes|integer|min:0',
            'active'          => 'sometimes|boolean',
            'page_slug'       => 'sometimes|nullable|string|max:160',
            'page_shortcode'  => 'sometimes|nullable|string|max:100',
            'page_url'        => 'sometimes|nullable|string|max:255',
            'includable_path' => 'sometimes|nullable|string|max:255',
        ]);

        // ✅ CHANGED: nullable page_id parsing
        $pageId = array_key_exists('page_id', $data)
            ? ($data['page_id'] === null ? null : (int) $data['page_id'])
            : null;

        $this->validatePage($pageId);

        // ✅ header_menu_id handling (nullable FK)
        $headerMenuId = array_key_exists('header_menu_id', $data)
            ? ($data['header_menu_id'] === null ? null : (int) $data['header_menu_id'])
            : null;

        if (Schema::hasColumn($this->table, 'header_menu_id')) {
            $this->validateHeaderMenu($headerMenuId);
        } else {
            $headerMenuId = null;
        }

        // ✅ department_id handling
        $deptId = array_key_exists('department_id', $data)
            ? ($data['department_id'] === null ? null : (int) $data['department_id'])
            : null;

        // ✅ CHANGED: only read page department when pageId exists
        $pageDeptId = ($pageId !== null && $pageId > 0) ? $this->pageDepartmentId($pageId) : null;

        if ($deptId === null && $pageDeptId !== null) {
            $deptId = $pageDeptId;
        }

        if ($deptId !== null && $pageDeptId !== null && $deptId !== $pageDeptId) {
            $this->logActivity($r, 'create_failed', 'department_id must match page.department_id', null, ['department_id'], null, [
                'page_id' => $pageId,
                'department_id' => $deptId,
                'page_department_id' => $pageDeptId,
            ]);
            return response()->json(['error' => 'department_id must match page.department_id'], 422);
        }

        $this->validateDepartment($deptId);

        $parentId = array_key_exists('parent_id', $data)
            ? ($data['parent_id'] === null ? null : (int) $data['parent_id'])
            : null;

        // ✅ CHANGED: now validate parent by scope (nullable page_id safe + header_menu_id)
        $this->validateParent($parentId, $pageId, $headerMenuId, null);

        $slug = $this->normSlug($data['slug'] ?? $data['title'] ?? '');
        if ($slug === '') {
            $this->logActivity($r, 'create_failed', 'Unable to generate slug', null, ['slug'], null, $data);
            return response()->json(['error' => 'Unable to generate slug'], 422);
        }

        // ✅ CHANGED: existing scope check (nullable page_id safe)
        $existingQ = DB::table($this->table)
            ->where('slug', $slug)
            ->whereNull('deleted_at');

        if ($pageId !== null && $pageId > 0) {
            $existingQ->where('page_id', $pageId);
        } else {
            $existingQ->whereNull('page_id');
        }

        $existing = $existingQ->first();

        if ($existing) {
            $this->logActivity(
                $r,
                'create',
                'Submenu already exists; not created again.',
                (int) $existing->id,
                null,
                null,
                $this->pickFields($existing, $this->trackedFields())
            );

            return response()->json([
                'success'         => true,
                'data'            => $existing,
                'already_existed' => true,
                'message'         => 'Submenu already exists; not created again.',
            ], 200);
        }

        $submenuShortcode = null;
        if (!empty($data['shortcode'])) {
            $submenuShortcode = strtoupper(trim($data['shortcode']));
            $shortExists = DB::table($this->table)
                ->where('shortcode', $submenuShortcode)
                ->whereNull('deleted_at')
                ->exists();

            if ($shortExists) {
                $this->logActivity($r, 'create_failed', 'Submenu shortcode already exists', null, ['shortcode'], null, ['shortcode' => $submenuShortcode]);
                return response()->json(['error' => 'Submenu shortcode already exists'], 422);
            }
        } else {
            $submenuShortcode = $this->generateSubmenuShortcode(null);
        }

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

        $singleDestErr = $this->enforceSingleDestination($pageUrl, $pageSlug, $pageShortcode, $includablePath);
        if ($singleDestErr) {
            $this->logActivity($r, 'create_failed', 'Multiple destination fields provided', null, ['page_url','page_slug','page_shortcode','includable_path'], null, $data);
            return $singleDestErr;
        }

        if ($pageShortcode) {
            $existsPageShort = DB::table($this->table)
                ->where('page_shortcode', $pageShortcode)
                ->whereNull('deleted_at')
                ->exists();

            if ($existsPageShort) {
                $this->logActivity($r, 'create_failed', 'Page shortcode already exists', null, ['page_shortcode'], null, ['page_shortcode' => $pageShortcode]);
                return response()->json(['error' => 'Page shortcode already exists'], 422);
            }
        }

        // ✅ CHANGED: trashed check (nullable page_id safe)
        $trashedQ = DB::table($this->table)
            ->where('slug', $slug)
            ->whereNotNull('deleted_at');

        if ($pageId !== null && $pageId > 0) {
            $trashedQ->where('page_id', $pageId);
        } else {
            $trashedQ->whereNull('page_id');
        }

        $trashed = $trashedQ->first();

        $now   = now();
        $actor = $this->actor($r);

        $position = array_key_exists('position', $data)
            ? (int) $data['position']
            : $this->nextPosition($pageId, $headerMenuId, $parentId);

        $active = array_key_exists('active', $data)
            ? (bool) $data['active']
            : true;

        if ($trashed) {
            $oldSnap = $this->pickFields($trashed, $this->trackedFields());

            DB::table($this->table)
                ->where('id', $trashed->id)
                ->update([
                    'page_id'          => $pageId,
                    'department_id'    => $deptId,
                    'header_menu_id'   => $headerMenuId,
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
            $newSnap = $row ? $this->pickFields($row, $this->trackedFields()) : null;
            $changed = $row ? $this->computeChangedFields($oldSnap, (array)$newSnap, array_keys($oldSnap)) : null;

            $this->logActivity(
                $r,
                'restore',
                'Restored from bin during create (slug matched trashed item).',
                (int) $trashed->id,
                $changed ?: null,
                $oldSnap,
                $newSnap
            );

            return response()->json([
                'success'  => true,
                'data'     => $row,
                'restored' => true,
            ]);
        }

        $id = DB::table($this->table)->insertGetId([
            'uuid'            => (string) Str::uuid(),
            'page_id'         => $pageId,
            'department_id'   => $deptId,
            'header_menu_id'  => $headerMenuId,
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

        $this->logActivity(
            $r,
            'create',
            'Created submenu.',
            (int) $id,
            $row ? array_keys($this->pickFields($row, $this->trackedFields())) : null,
            null,
            $row ? $this->pickFields($row, $this->trackedFields()) : null
        );

        return response()->json(['success' => true, 'data' => $row], 201);
    }

    public function update(Request $r, $id)
    {
        $row = DB::table($this->table)
            ->where('id', (int) $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            $this->logActivity($r, 'update_failed', 'Not found', (int)$id, null, null, null);
            return response()->json(['error' => 'Not found'], 404);
        }

        $data = $r->validate([
            // ✅ CHANGED: nullable allowed
            'page_id'          => 'sometimes|nullable|integer|min:1',

            // ✅ optional nullable header_menu_id
            'header_menu_id'   => 'sometimes|nullable|integer|min:1',

            'department_id'    => 'sometimes|nullable|integer|min:1|exists:departments,id',
            'title'            => 'sometimes|string|max:150',
            'description'      => 'sometimes|nullable|string',
            'slug'             => 'sometimes|nullable|string|max:160',
            'shortcode'        => 'sometimes|nullable|string|max:100',
            'parent_id'        => 'sometimes|nullable|integer',
            'position'         => 'sometimes|integer|min:0',
            'active'           => 'sometimes|boolean',
            'regenerate_slug'  => 'sometimes|boolean',
            'page_slug'        => 'sometimes|nullable|string|max:160',
            'page_shortcode'   => 'sometimes|nullable|string|max:100',
            'page_url'         => 'sometimes|nullable|string|max:255',
            'includable_path'  => 'sometimes|nullable|string|max:255',
        ]);

        $oldSnap = $this->pickFields($row, $this->trackedFields());

        // ✅ CHANGED: nullable page_id handling
        $currentPageId = $row->page_id === null ? null : (int) $row->page_id;
        $pageId = $currentPageId;

        if (array_key_exists('page_id', $data)) {
            $incoming = ($data['page_id'] === null) ? null : (int) $data['page_id'];

            // ✅ allow setting page_id only if currently null, else block change
            if ($currentPageId !== null && $incoming !== $currentPageId) {
                $this->logActivity($r, 'update_failed', 'Changing page_id is not allowed', (int)$row->id, ['page_id'], $oldSnap, ['page_id' => $incoming]);
                return response()->json(['error' => 'Changing page_id is not allowed'], 422);
            }

            $pageId = $incoming;
        }

        $this->validatePage($pageId);

        // ✅ header_menu_id update (nullable allowed)
        $headerMenuId = property_exists($row, 'header_menu_id')
            ? ($row->header_menu_id === null ? null : (int) $row->header_menu_id)
            : null;

        if (array_key_exists('header_menu_id', $data)) {
            $headerMenuId = ($data['header_menu_id'] === null) ? null : (int) $data['header_menu_id'];
        }

        if (Schema::hasColumn($this->table, 'header_menu_id')) {
            $this->validateHeaderMenu($headerMenuId);
        } else {
            $headerMenuId = null;
        }

        // ✅ department_id handling
        $deptId = $row->department_id ?? null;
        if (array_key_exists('department_id', $data)) {
            $deptId = ($data['department_id'] === null) ? null : (int) $data['department_id'];
        }

        // ✅ CHANGED: only read page department when pageId exists
        $pageDeptId = ($pageId !== null && $pageId > 0) ? $this->pageDepartmentId($pageId) : null;

        if ($deptId === null && $pageDeptId !== null) {
            $deptId = $pageDeptId;
        }

        if ($deptId !== null && $pageDeptId !== null && $deptId !== $pageDeptId) {
            $this->logActivity($r, 'update_failed', 'department_id must match page.department_id', (int)$row->id, ['department_id'], $oldSnap, [
                'department_id' => $deptId,
                'page_department_id' => $pageDeptId
            ]);
            return response()->json(['error' => 'department_id must match page.department_id'], 422);
        }

        $this->validateDepartment($deptId);

        $parentId = array_key_exists('parent_id', $data)
            ? ($data['parent_id'] === null ? null : (int) $data['parent_id'])
            : ($row->parent_id ?? null);

        // ✅ CHANGED: now validate parent by scope
        $this->validateParent($parentId, $pageId, $headerMenuId, (int) $row->id);

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
                $this->logActivity($r, 'update_failed', 'Unable to generate slug', (int)$row->id, ['slug'], $oldSnap, $data);
                return response()->json(['error' => 'Unable to generate slug'], 422);
            }

            // ✅ CHANGED: slug uniqueness check in scope (nullable page_id safe)
            $existsSlugQ = DB::table($this->table)
                ->where('slug', $slug)
                ->where('id', '!=', $row->id)
                ->whereNull('deleted_at');

            if ($pageId !== null && $pageId > 0) {
                $existsSlugQ->where('page_id', $pageId);
            } else {
                $existsSlugQ->whereNull('page_id');
            }

            $existsSlug = $existsSlugQ->exists();

            if ($existsSlug) {
                $this->logActivity($r, 'update_failed', 'Slug already in use for this scope', (int)$row->id, ['slug'], $oldSnap, ['slug' => $slug]);
                return response()->json(['error' => 'Slug already in use for this scope'], 422);
            }
        }

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
                    $this->logActivity($r, 'update_failed', 'Submenu shortcode already in use', (int)$row->id, ['shortcode'], $oldSnap, ['shortcode' => $val]);
                    return response()->json(['error' => 'Submenu shortcode already in use'], 422);
                }
                $submenuShortcode = $val;
            }
        }

        $pageSlug = $row->page_slug ?? null;
        if (array_key_exists('page_slug', $data)) {
            $norm = $this->normSlug($data['page_slug']);
            $pageSlug = $norm !== '' ? $norm : null; // ✅ allow duplicates now
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
                    $this->logActivity($r, 'update_failed', 'Page shortcode already in use', (int)$row->id, ['page_shortcode'], $oldSnap, ['page_shortcode' => $pageShortcode]);
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

        $singleDestErr = $this->enforceSingleDestination($pageUrl, $pageSlug, $pageShortcode, $includablePath);
        if ($singleDestErr) {
            $this->logActivity($r, 'update_failed', 'Multiple destination fields provided', (int)$row->id, ['page_url','page_slug','page_shortcode','includable_path'], $oldSnap, $data);
            return $singleDestErr;
        }

        $upd = [
            // ✅ CHANGED: persist nullable page_id
            'page_id'          => $pageId,

            'department_id'    => $deptId,
            'header_menu_id'   => $headerMenuId,
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

        $newSnap = $fresh ? $this->pickFields($fresh, $this->trackedFields()) : null;
        $changed = ($fresh && $newSnap) ? $this->computeChangedFields($oldSnap, $newSnap, array_keys($newSnap)) : null;

        $this->logActivity(
            $r,
            'update',
            $changed && count($changed) ? ('Updated fields: ' . implode(', ', $changed)) : 'Updated submenu.',
            (int) $row->id,
            $changed ?: null,
            $oldSnap,
            $newSnap
        );

        return response()->json(['success' => true, 'data' => $fresh]);
    }

    public function destroy(Request $r, $id)
    {
        $row = DB::table($this->table)
            ->where('id', (int) $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            $this->logActivity($r, 'delete_failed', 'Not found', (int)$id, null, null, null);
            return response()->json(['error' => 'Not found'], 404);
        }

        $oldSnap = $this->pickFields($row, $this->trackedFields());

        DB::table($this->table)
            ->where('id', (int) $id)
            ->update([
                'deleted_at'    => now(),
                'updated_at'    => now(),
                'updated_by'    => $this->actor($r)['id'] ?: null,
                'updated_at_ip' => $r->ip(),
            ]);

        $fresh = DB::table($this->table)->where('id', (int) $id)->first();
        $newSnap = $fresh ? $this->pickFields($fresh, $this->trackedFields()) : null;

        $this->logActivity(
            $r,
            'delete',
            'Moved submenu to bin (soft delete).',
            (int) $id,
            ['deleted_at'],
            $oldSnap,
            $newSnap
        );

        return response()->json(['success' => true, 'message' => 'Moved to bin']);
    }

    public function restore(Request $r, $id)
    {
        $row = DB::table($this->table)
            ->where('id', (int) $id)
            ->whereNotNull('deleted_at')
            ->first();

        if (!$row) {
            $this->logActivity($r, 'restore_failed', 'Not found in bin', (int)$id, null, null, null);
            return response()->json(['error' => 'Not found in bin'], 404);
        }

        $oldSnap = $this->pickFields($row, $this->trackedFields());

        DB::table($this->table)
            ->where('id', (int) $id)
            ->update([
                'deleted_at'    => null,
                'updated_at'    => now(),
                'updated_by'    => $this->actor($r)['id'] ?: null,
                'updated_at_ip' => $r->ip(),
            ]);

        $fresh = DB::table($this->table)->where('id', (int) $id)->first();
        $newSnap = $fresh ? $this->pickFields($fresh, $this->trackedFields()) : null;

        $this->logActivity(
            $r,
            'restore',
            'Restored submenu from bin.',
            (int) $id,
            ['deleted_at'],
            $oldSnap,
            $newSnap
        );

        return response()->json(['success' => true, 'message' => 'Restored']);
    }

    public function forceDelete(Request $r, $id)
    {
        $row = DB::table($this->table)
            ->where('id', (int) $id)
            ->first();

        if (!$row) {
            $this->logActivity($r, 'force_delete_failed', 'Not found', (int)$id, null, null, null);
            return response()->json(['error' => 'Not found'], 404);
        }

        $oldSnap = $this->pickFields($row, $this->trackedFields());

        DB::table($this->table)
            ->where('id', (int) $id)
            ->delete();

        $this->logActivity(
            $r,
            'force_delete',
            'Deleted submenu permanently.',
            (int) $id,
            null,
            $oldSnap,
            null
        );

        return response()->json(['success' => true, 'message' => 'Deleted permanently']);
    }

    public function toggleActive(Request $r, $id)
    {
        $row = DB::table($this->table)
            ->where('id', (int) $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            $this->logActivity($r, 'toggle_failed', 'Not found', (int)$id, null, null, null);
            return response()->json(['error' => 'Not found'], 404);
        }

        $oldSnap = $this->pickFields($row, $this->trackedFields());
        $newActive = !$row->active;

        DB::table($this->table)
            ->where('id', (int) $id)
            ->update([
                'active'        => $newActive,
                'updated_at'    => now(),
                'updated_by'    => $this->actor($r)['id'] ?: null,
                'updated_at_ip' => $r->ip(),
            ]);

        $fresh = DB::table($this->table)->where('id', (int) $id)->first();
        $newSnap = $fresh ? $this->pickFields($fresh, $this->trackedFields()) : null;

        $this->logActivity(
            $r,
            'toggle_active',
            $newActive ? 'Activated submenu.' : 'Deactivated submenu.',
            (int) $id,
            ['active'],
            $oldSnap,
            $newSnap
        );

        return response()->json(['success' => true, 'message' => 'Status updated']);
    }

    public function reorder(Request $r)
    {
        $payload = $r->validate([
            'orders'             => 'required|array|min:1',
            'orders.*.id'        => 'required|integer',
            'orders.*.position'  => 'required|integer|min:0',
            'orders.*.parent_id' => 'nullable|integer',
        ]);

        $changesToLog = []; // collect per-id old/new for logs after commit

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

                // store for logging
                $changesToLog[] = [
                    'id' => $id,
                    'old' => [
                        'position' => (int) $row->position,
                        'parent_id' => $currentPid,
                    ],
                    'new' => [
                        'position' => $pos,
                        'parent_id' => $currentPid,
                    ],
                ];

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

            $this->logActivity(
                $r,
                'reorder_failed',
                'Reorder failed: ' . $e->getMessage(),
                null,
                ['orders'],
                null,
                ['orders' => $payload['orders']]
            );

            return response()->json([
                'error'   => 'Reorder failed',
                'details' => $e->getMessage(),
            ], 422);
        }

        // logs after commit (so reorder logs remain even if some other later code fails)
        foreach ($changesToLog as $c) {
            $changed = ['position']; // parent is not allowed to change here
            $this->logActivity(
                $r,
                'reorder',
                'Reordered submenu position.',
                (int) $c['id'],
                $changed,
                $c['old'],
                $c['new']
            );
        }

        return response()->json(['success' => true, 'message' => 'Order updated']);
    }

    // public function publicTree(Request $r)
    // {
    //     ...
    // }
    public function publicTree(Request $r)
    {
        $onlyTopLevel = (int) $r->query('top_level', 0) === 1;

        $pageId = $this->resolvePageIdFromRequest($r);
        $requestedHeaderMenuId = $this->resolveHeaderMenuIdFromRequest($r);

        // ✅ NEW: allow header_menu_id as main scope
        if ($pageId <= 0 && $requestedHeaderMenuId <= 0) {
            return response()->json([
                'error' => 'Missing page_id/page_slug OR header_menu_id'
            ], 422);
        }

        if ($pageId > 0) {
            $this->validatePage($pageId);
        }

        $hasHeaderCol = Schema::hasColumn($this->table, 'header_menu_id');

        $headerMenuId = $requestedHeaderMenuId;
        if ($hasHeaderCol && $headerMenuId > 0) {
            $this->validateHeaderMenu($headerMenuId);
        } else {
            $headerMenuId = 0;
        }

        /**
         * Helper: build the exact query for a given header_menu_id (and current pageId rules)
         * Keeps behavior same as current code.
         */
        $buildQueryForHeader = function (int $hmId) use ($pageId, $onlyTopLevel) {
            $q = DB::table($this->table)
                ->whereNull('deleted_at')
                ->where('active', true)
                ->where('header_menu_id', $hmId);

            // ✅ If pageId exists -> allow both page-level + header-level (page_id NULL)
            if ($pageId > 0) {
                $q->where(function ($x) use ($pageId) {
                    $x->where('page_id', $pageId)
                      ->orWhereNull('page_id');
                });
            } else {
                // header-only scope
                $q->whereNull('page_id');
            }

            if ($onlyTopLevel) {
                $q->whereNull('parent_id');
            }

            return $q->orderBy('position', 'asc')
                     ->orderBy('id', 'asc');
        };

        $rows = collect();
        $resolvedHeaderMenuId = $headerMenuId;

        /**
         * ✅ NEW: header_menu_id fallback chain
         * If current header_menu_id has NO submenus (in the same scope),
         * try parent header_menu_id, then grand-parent, etc.
         */
        if ($hasHeaderCol && $headerMenuId > 0) {

            $visited = [];
            $hmId = $headerMenuId;

            while ($hmId > 0 && !isset($visited[$hmId])) {
                $visited[$hmId] = true;

                $candidate = $buildQueryForHeader($hmId)->get();

                if ($candidate->count() > 0) {
                    $rows = $candidate;
                    $resolvedHeaderMenuId = $hmId;
                    break;
                }

                // move to parent header menu
                $parentId = DB::table('header_menus')
                    ->where('id', $hmId)
                    ->whereNull('deleted_at')
                    ->value('parent_id');

                $hmId = $parentId ? (int) $parentId : 0;
            }

            // If nothing found up the chain, keep empty rows and resolvedHeaderMenuId = original headerMenuId
            if ($rows->count() === 0) {
                $resolvedHeaderMenuId = $headerMenuId;
            }

        } else {
            // ✅ fallback: page scope only (old behavior)
            $q = DB::table($this->table)
                ->whereNull('deleted_at')
                ->where('active', true);

            if ($pageId > 0) {
                $q->where('page_id', $pageId);
            } else {
                $q->whereNull('page_id');
            }

            if ($onlyTopLevel) {
                $q->whereNull('parent_id');
            }

            $rows = $q->orderBy('position', 'asc')
                      ->orderBy('id', 'asc')
                      ->get();

            $resolvedHeaderMenuId = 0;
        }

        // build tree
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
            'scope'   => [
                'page_id' => $pageId ?: null,
                // keep backward compat key (now shows the header actually used for response)
                'header_menu_id' => $resolvedHeaderMenuId ?: null,
                // helpful debug (doesn't break existing key)
                'requested_header_menu_id' => $requestedHeaderMenuId ?: null,
            ],
            'data' => $make(0),
        ]);
    }

    public function pages(Request $r)
    {
        $q = trim((string) $r->query('q', ''));
        $limit = min(1000, max(10, (int) $r->query('limit', 500)));

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
                $x->orWhere('id', (int) $q);
            });
        }

        if (Schema::hasColumn('pages', 'active') && $r->query('only_active', null) !== null) {
            $qb->where('active', (int) $r->query('only_active') === 1);
        }

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

                $excludeTopDirs = [
                    'header', 'footer', 'layouts', 'partials', 'components', 'auth', 'common', 'ui',
                ];

                foreach (File::allFiles($root) as $file) {
                    $full = $file->getPathname();

                    if (!Str::endsWith($full, '.blade.php')) continue;

                    $rel = str_replace($root . DIRECTORY_SEPARATOR, '', $full);
                    $rel = str_replace('\\', '/', $rel);

                    $firstSeg = explode('/', $rel)[0] ?? '';
                    if ($firstSeg && in_array($firstSeg, $excludeTopDirs, true)) continue;

                    $baseName = basename($rel);
                    if (Str::startsWith($baseName, '_')) continue;

                    $noExt = substr($rel, 0, -10);
                    $dot = 'modules.' . str_replace('/', '.', $noExt);

                    $list[] = $dot;
                }

                $list = array_values(array_unique($list));
                sort($list, SORT_NATURAL | SORT_FLAG_CASE);
            }

            Cache::put($cacheKey, $list, 600);
        }

        if ($q !== '') {
            $qq = mb_strtolower($q);
            $list = array_values(array_filter($list, function ($p) use ($qq) {
                return str_contains(mb_strtolower((string) $p), $qq);
            }));
        }

        if (count($list) > $limit) {
            $list = array_slice($list, 0, $limit);
        }

        return response()->json([
            'success' => true,
            'data'    => $list,
        ]);
    }

    public function renderPublic(Request $r)
    {
        $slug = $this->normSlug($r->query('slug', ''));
        if ($slug === '') {
            return response()->json(['success' => false, 'error' => 'Missing slug'], 422);
        }

        $pageId = $this->resolvePageIdFromRequest($r);

        // ✅ NEW: header_menu_id supported
        $headerMenuId = (int) $r->query('header_menu_id', 0);

        $q = DB::table($this->table)
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->where('active', true);

        /**
         * ✅ FIXED SCOPING LOGIC
         *
         * If header_menu_id exists:
         * - Scope by header_menu_id
         * - And page_id can be:
         *      ✅ page-specific OR
         *      ✅ global under that header (page_id NULL)
         *
         * Else fallback to old behavior:
         * - Scope only by page_id if present
         */

        if (Schema::hasColumn($this->table, 'header_menu_id') && $headerMenuId > 0) {

            $q->where('header_menu_id', $headerMenuId);

            if ($pageId > 0) {
                // ✅ allow both pageId + null pageId within same header scope
                $q->where(function ($x) use ($pageId) {
                    $x->where('page_id', $pageId)
                      ->orWhereNull('page_id');
                });
            } else {
                // ✅ header-only submenu scope (page_id is NULL)
                $q->whereNull('page_id');
            }

        } else {
            // ✅ fallback: old logic
            if ($pageId > 0) {
                $q->where('page_id', $pageId);
            }
        }

        $menu = $q->first();
        if (!$menu) {
            return response()->json([
                'success' => false,
                'error'   => 'Submenu not found',
                'debug'   => [
                    'slug' => $slug,
                    'page_id' => $pageId ?: null,
                    'header_menu_id' => $headerMenuId ?: null,
                ]
            ], 404);
        }

        $pageUrl        = trim((string)($menu->page_url ?? ''));
        $pageSlug       = trim((string)($menu->page_slug ?? ''));
        $pageShortcode  = trim((string)($menu->page_shortcode ?? ''));
        $includablePath = trim((string)($menu->includable_path ?? ''));

        $sameOrigin = function (string $u) use ($r): bool {
            $u = trim($u);
            if ($u === '') return false;
            if (!preg_match('/^https?:\/\//i', $u)) return true;

            $host = parse_url($u, PHP_URL_HOST);
            if (!$host) return false;

            return strtolower($host) === strtolower($r->getHost());
        };

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

                if (method_exists($vf, 'flushState')) {
                    $vf->flushState();
                }

                $viewObj = view($includablePath);
                $html = $viewObj->render();

                $styles  = method_exists($vf, 'yieldPushContent') ? $vf->yieldPushContent('styles')  : '';
                $scripts = method_exists($vf, 'yieldPushContent') ? $vf->yieldPushContent('scripts') : '';

                $pickedSection = null;
                $sections = [];

                $looksEmpty = trim(strip_tags((string)$html)) === '';
                $looksLikeFullLayout =
                    stripos((string)$html, '<html') !== false ||
                    stripos((string)$html, '<body') !== false;

                if ($looksEmpty || $looksLikeFullLayout) {
                    if (method_exists($vf, 'flushState')) {
                        $vf->flushState();
                    }

                    $sections = $viewObj->renderSections();

                    $candidates = ['content', 'page-content', 'main', 'body'];

                    foreach ($candidates as $sec) {
                        $candidateHtml = $sections[$sec] ?? '';
                        if (trim(strip_tags((string)$candidateHtml)) !== '') {
                            $html = $candidateHtml;
                            $pickedSection = $sec;
                            break;
                        }
                    }

                    $styles  = method_exists($vf, 'yieldPushContent') ? $vf->yieldPushContent('styles')  : $styles;
                    $scripts = method_exists($vf, 'yieldPushContent') ? $vf->yieldPushContent('scripts') : $scripts;

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
                        'submenu_slug'     => $menu->slug ?? null,
                        'submenu_id'       => $menu->id ?? null,
                        'header_menu_id'   => $menu->header_menu_id ?? null, // ✅ NEW
                        'page_id'          => $menu->page_id ?? null,        // ✅ NEW
                        'includable'       => $includablePath,
                        'section_used'     => $pickedSection,
                        'render_was_empty' => $looksEmpty,
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
                    'submenu_slug'    => $menu->slug ?? null,
                    'submenu_id'      => $menu->id ?? null,
                    'header_menu_id'  => $menu->header_menu_id ?? null, // ✅ NEW
                    'page_id'         => $page->id ?? null,
                    'page_slug'       => $page->slug ?? null,
                    'shortcode'       => $page->shortcode ?? null,
                ],
                'html' => (string)$html,
            ]);
        }

        if ($pageUrl !== '') {
            return response()->json([
                'success'     => true,
                'type'        => 'url',
                'title'       => $menu->title ?? 'Link',
                'meta'        => [
                    'submenu_slug'   => $menu->slug ?? null,
                    'submenu_id'     => $menu->id ?? null,
                    'header_menu_id' => $menu->header_menu_id ?? null, // ✅ NEW
                ],
                'url'         => $pageUrl,
                'same_origin' => $sameOrigin($pageUrl),
            ]);
        }

        return response()->json([
            'success' => true,
            'type'    => 'coming_soon',
            'title'   => $menu->title ?? 'Coming Soon',
            'message' => trim((string)($menu->description ?? '')) ?: 'This section will be available soon.',
            'meta'    => [
                'submenu_slug'   => $menu->slug ?? null,
                'submenu_id'     => $menu->id ?? null,
                'header_menu_id' => $menu->header_menu_id ?? null,
                'page_id'        => $menu->page_id ?? null,
            ],
        ], 200);
    }
}
