<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;

class TopHeaderMenuController extends Controller
{
    private string $table = 'top_header_menus';

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

    /** Contact info table name differs across projects: contact_info vs contact_infos */
    private function contactInfoTable(): string
    {
        if (Schema::hasTable('contact_info')) return 'contact_info';
        if (Schema::hasTable('contact_infos')) return 'contact_infos';
        // fallback to your most likely one
        return 'contact_info';
    }

    private function normSlug(?string $s): string
    {
        $s = trim((string) $s);
        return $s === '' ? '' : Str::slug($s, '-');
    }

    /** Auto-generate unique menu shortcode (alphanumeric) */
    private function generateMenuShortcode(?int $excludeId = null): string
    {
        $maxTries = 60;
        for ($i = 0; $i < $maxTries; $i++) {
            $code = 'THM' . Str::upper(Str::random(6));
            $q = DB::table($this->table)->where('shortcode', $code);
            if ($excludeId) $q->where('id', '!=', $excludeId);
            if (!$q->exists()) return $code;
        }
        return 'THM' . time();
    }

    /** Find a conflicting row for a unique column (includes trashed too) */
    private function findUniqueConflict(string $column, $value, ?int $excludeId = null)
    {
        if ($value === null) return null;
        $value = is_string($value) ? trim($value) : $value;
        if ($value === '' || $value === []) return null;

        $q = DB::table($this->table)
            ->select('id', 'title', 'deleted_at', 'item_type', $column)
            ->where($column, $value);

        if ($excludeId !== null) $q->where('id', '!=', $excludeId);

        return $q->first();
    }

    /** Friendly 422 for duplicate unique constraints instead of 500 */
    private function handleUniqueException(\Throwable $e)
    {
        $msg = $e->getMessage();

        if (
            $e instanceof QueryException &&
            ((string) $e->getCode() === '23000' || str_contains($msg, 'Integrity constraint violation')) &&
            str_contains($msg, 'Duplicate entry')
        ) {
            $key = null;
            if (preg_match("/for key '([^']+)'/i", $msg, $m)) $key = $m[1];

            $field = 'unique field';
            if ($key) {
                $map = [
                    'top_header_menus_slug_unique'           => 'slug',
                    'top_header_menus_shortcode_unique'      => 'shortcode',
                    'top_header_menus_page_slug_unique'      => 'page_slug',
                    'top_header_menus_page_shortcode_unique' => 'page_shortcode',
                    'top_header_menus_uuid_unique'           => 'uuid',
                    'thm_item_type_contact_unique'           => 'contact_info_id', // ✅ composite unique
                ];
                $field = $map[$key] ?? $field;
            }

            return response()->json([
                'error' => ucfirst($field) . ' already exists',
                'field' => $field,
            ], 422);
        }

        return null;
    }

    /** Guard that department exists (if provided) */
    private function validateDepartment(?int $departmentId): void
    {
        if ($departmentId === null) return;

        $q = DB::table('departments')->where('id', $departmentId);
        if (Schema::hasColumn('departments', 'deleted_at')) $q->whereNull('deleted_at');

        if (!$q->exists()) {
            abort(response()->json(['error' => 'Invalid department_id'], 422));
        }
    }

    /** Next position among same department scope (NULL dept is its own scope) - ONLY MENU ITEMS */
    private function nextPosition(?int $departmentId): int
    {
        $q = DB::table($this->table)
            ->whereNull('deleted_at')
            ->where('item_type', 'menu');

        if ($departmentId === null) $q->whereNull('department_id');
        else $q->where('department_id', $departmentId);

        $max = (int) $q->max('position');
        return $max + 1;
    }

    /* ============================================
     | Contact Infos list (for selecting 2)
     |  GET /api/top-header-menus/contact-infos
     |============================================ */

    public function contactInfos(Request $r)
    {
        $qtxt = trim((string) $r->query('q', ''));

        $ciTable = $this->contactInfoTable();
        $q = DB::table($ciTable);

        if (Schema::hasColumn($ciTable, 'deleted_at')) $q->whereNull('deleted_at');

        if ($qtxt !== '') {
            $q->where(function ($x) use ($qtxt, $ciTable) {
                $cols = ['label', 'type', 'value', 'title', 'name'];
                $any = false;
                foreach ($cols as $col) {
                    if (Schema::hasColumn($ciTable, $col)) {
                        $any = true;
                        $x->orWhere($col, 'like', "%{$qtxt}%");
                    }
                }
                // if none of the columns exist, no filter applied
                if (!$any) $x->orWhereRaw('1=1');
            });
        }

        $rows = $q->orderBy('id', 'asc')->limit(500)->get();

        return response()->json(['success' => true, 'data' => $rows]);
    }

    /* ============================================
     | List / Resolve (ADMIN LISTS ARE MENU ONLY)
     |============================================ */

    public function index(Request $r)
    {
        $page = max(1, (int) $r->query('page', 1));
        $per  = min(100, max(5, (int) $r->query('per_page', 20)));
        $qtxt = trim((string) $r->query('q', ''));
        $activeParam = $r->query('active', null);
        $departmentIdParam = $r->query('department_id', 'any');
        $sort = (string) $r->query('sort', 'position');
        $direction = strtolower((string) $r->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowedSort = ['position', 'title', 'created_at'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'position';

        $base = DB::table($this->table)
            ->whereNull('deleted_at')
            ->where('item_type', 'menu');

        if ($qtxt !== '') {
            $base->where(function ($x) use ($qtxt) {
                $x->where('title', 'like', "%{$qtxt}%")
                    ->orWhere('slug', 'like', "%{$qtxt}%")
                    ->orWhere('shortcode', 'like', "%{$qtxt}%")
                    ->orWhere('page_slug', 'like', "%{$qtxt}%")
                    ->orWhere('page_shortcode', 'like', "%{$qtxt}%")
                    ->orWhere('page_url', 'like', "%{$qtxt}%");
            });
        }

        if ($activeParam !== null && in_array((string) $activeParam, ['0', '1'], true)) {
            $base->where('active', (int) $activeParam === 1);
        }

        if ($departmentIdParam === null || $departmentIdParam === 'null') {
            $base->whereNull('department_id');
        } elseif ($departmentIdParam !== 'any' && $departmentIdParam !== '') {
            $base->where('department_id', (int) $departmentIdParam);
        }

        $total = (clone $base)->count();
        $rows = $base->orderBy($sort, $direction)
            ->orderBy('id', 'asc')
            ->forPage($page, $per)
            ->get();

        $this->attachContactInfosToRows($rows);

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
        $departmentIdParam = $r->query('department_id', 'any');

        $base = DB::table($this->table)
            ->whereNotNull('deleted_at')
            ->where('item_type', 'menu');

        if ($departmentIdParam === null || $departmentIdParam === 'null') {
            $base->whereNull('department_id');
        } elseif ($departmentIdParam !== 'any' && $departmentIdParam !== '') {
            $base->where('department_id', (int) $departmentIdParam);
        }

        $total = (clone $base)->count();
        $rows = $base->orderBy('deleted_at', 'desc')
            ->forPage($page, $per)
            ->get();

        $this->attachContactInfosToRows($rows);

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
     * Resolve a slug (MENU ONLY):
     * - if page_url is set => redirect to that url
     * - else if page_slug  => redirect to "/{page_slug}"
     * - else               => redirect to "/{slug}"
     */
    public function resolve(Request $r)
    {
        $slug = $this->normSlug($r->query('slug', ''));
        if ($slug === '') return response()->json(['error' => 'Missing slug'], 422);

        $menu = DB::table($this->table)
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->where('active', true)
            ->where('item_type', 'menu')
            ->first();

        if (!$menu) return response()->json(['error' => 'Not found'], 404);

        $pageUrl  = $menu->page_url ?? null;
        $pageSlug = $menu->page_slug ?? null;

        if ($pageUrl && trim($pageUrl) !== '') $redirectUrl = $pageUrl;
        elseif ($pageSlug && trim($pageSlug) !== '') $redirectUrl = '/' . ltrim($pageSlug, '/');
        else $redirectUrl = '/' . ltrim($menu->slug, '/');

        $rows = [$menu];
        $this->attachContactInfosToRows($rows);
        $menu = $rows[0];

        return response()->json([
            'success'      => true,
            'menu'         => $menu,
            'redirect_url' => $redirectUrl,
        ]);
    }

    /* ============================================
     | CRUD (MENU ONLY)
     |============================================ */

    public function show(Request $r, $id)
    {
        $row = DB::table($this->table)
            ->where('id', (int) $id)
            ->where('item_type', 'menu')
            ->first();

        if (!$row) return response()->json(['error' => 'Not found'], 404);

        $rows = [$row];
        $this->attachContactInfosToRows($rows);

        return response()->json(['success' => true, 'data' => $rows[0]]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'department_id'  => 'sometimes|nullable|integer',
            'title'          => 'required|string|max:150',
            'description'    => 'sometimes|nullable|string',
            'slug'           => 'sometimes|nullable|string|max:160',
            'shortcode'      => 'sometimes|nullable|string|max:100',
            // accept both url and page_url (UI uses url)
            'url'            => 'sometimes|nullable|string|max:255',
            'page_url'       => 'sometimes|nullable|string|max:255',
            'page_slug'      => 'sometimes|nullable|string|max:160',
            'page_shortcode' => 'sometimes|nullable|string|max:100',
            'position'       => 'sometimes|integer|min:0',
            'active'         => 'sometimes|boolean',
            'metadata'       => 'sometimes|nullable|array',
        ]);

        $departmentId = array_key_exists('department_id', $data)
            ? ($data['department_id'] === null ? null : (int) $data['department_id'])
            : null;

        $this->validateDepartment($departmentId);

        // SLUG
        $slug = $this->normSlug($data['slug'] ?? $data['title'] ?? '');
        if ($slug === '') return response()->json(['error' => 'Unable to generate slug'], 422);

        $existingAny = DB::table($this->table)->where('slug', $slug)->first();
        if ($existingAny && $existingAny->deleted_at === null) {
            // ✅ only idempotent-return if it is a MENU item
            if (($existingAny->item_type ?? 'menu') !== 'menu') {
                return response()->json(['error' => 'Slug already in use', 'field' => 'slug'], 422);
            }

            return response()->json([
                'success' => true,
                'data' => $existingAny,
                'already_existed' => true,
                'message' => 'Menu already exists; not created again.',
            ], 200);
        }

        // SHORTCODE
        if (!empty($data['shortcode'])) {
            $shortcode = strtoupper(trim((string) $data['shortcode']));
            $conf = $this->findUniqueConflict('shortcode', $shortcode, null);
            if ($conf) return response()->json(['error' => 'Shortcode already exists', 'field' => 'shortcode'], 422);
        } else {
            $shortcode = $this->generateMenuShortcode(null);
        }

        // PAGE URL (alias url)
        $pageUrl = null;
        if (array_key_exists('url', $data)) {
            $pageUrl = trim((string) $data['url']) ?: null;
        } elseif (array_key_exists('page_url', $data)) {
            $pageUrl = trim((string) $data['page_url']) ?: null;
        }

$pageSlug = null;
if (array_key_exists('page_slug', $data)) {
    $norm = $this->normSlug($data['page_slug']);
    $pageSlug = $norm !== '' ? $norm : null; // ✅ allow duplicates now
}


        $pageShortcode = null;
        if (array_key_exists('page_shortcode', $data)) {
            $val = trim((string) $data['page_shortcode']);
            $pageShortcode = $val !== '' ? $val : null;
            if ($pageShortcode) {
                $conf = $this->findUniqueConflict('page_shortcode', $pageShortcode, null);
                if ($conf) return response()->json(['error' => 'Page shortcode already exists', 'field' => 'page_shortcode'], 422);
            }
        }

        $metaJson = array_key_exists('metadata', $data) ? json_encode($data['metadata']) : null;

        $actor = $this->actor($r);
        $now = now();

        $position = array_key_exists('position', $data)
            ? (int) $data['position']
            : $this->nextPosition($departmentId);

        $active = array_key_exists('active', $data) ? (bool) $data['active'] : true;

        // If soft-deleted with same slug, revive instead of new insert (MENU ONLY)
        $trashed = DB::table($this->table)
            ->where('slug', $slug)
            ->whereNotNull('deleted_at')
            ->first();

        if ($trashed) {
            if (($trashed->item_type ?? 'menu') !== 'menu') {
                return response()->json([
                    'error' => 'Slug already exists in trash for a non-menu item. Clear/force delete it to reuse.',
                    'field' => 'slug',
                ], 422);
            }

            try {
                DB::table($this->table)->where('id', $trashed->id)->update([
                    'department_id'   => $departmentId,
                    'item_type'       => 'menu',
                    'contact_info_id' => null,

                    'title'           => $data['title'],
                    'description'     => $data['description'] ?? null,
                    'slug'            => $slug,
                    'shortcode'       => $shortcode,
                    'page_url'        => $pageUrl,
                    'page_slug'       => $pageSlug,
                    'page_shortcode'  => $pageShortcode,
                    'position'        => $position,
                    'active'          => $active,
                    'metadata'        => $metaJson,

                    'deleted_at'      => null,
                    'updated_at'      => $now,
                    'updated_by'      => $actor['id'] ?: null,
                    'updated_at_ip'   => $r->ip(),
                ]);
            } catch (\Throwable $e) {
                $handled = $this->handleUniqueException($e);
                if ($handled) return $handled;
                throw $e;
            }

            $row = DB::table($this->table)->where('id', $trashed->id)->first();
            $rows = [$row];
            $this->attachContactInfosToRows($rows);

            return response()->json(['success' => true, 'data' => $rows[0], 'restored' => true], 200);
        }

        try {
            $id = DB::table($this->table)->insertGetId([
                'uuid'           => (string) Str::uuid(),
                'department_id'  => $departmentId,

                'item_type'       => 'menu',
                'contact_info_id' => null,

                'title'          => $data['title'],
                'description'    => $data['description'] ?? null,
                'slug'           => $slug,
                'shortcode'      => $shortcode,
                'page_url'       => $pageUrl,
                'page_slug'      => $pageSlug,
                'page_shortcode' => $pageShortcode,
                'position'       => $position,
                'active'         => $active,
                'metadata'       => $metaJson,

                'created_at'     => $now,
                'updated_at'     => $now,
                'created_by'     => $actor['id'] ?: null,
                'updated_by'     => $actor['id'] ?: null,
                'created_at_ip'  => $r->ip(),
                'updated_at_ip'  => $r->ip(),
            ]);
        } catch (\Throwable $e) {
            $handled = $this->handleUniqueException($e);
            if ($handled) return $handled;
            throw $e;
        }

        $row = DB::table($this->table)->where('id', $id)->first();
        $rows = [$row];
        $this->attachContactInfosToRows($rows);

        return response()->json(['success' => true, 'data' => $rows[0]], 201);
    }

    public function update(Request $r, $id)
    {
        $row = DB::table($this->table)
            ->where('id', (int) $id)
            ->whereNull('deleted_at')
            ->where('item_type', 'menu') // ✅ menu only
            ->first();

        if (!$row) return response()->json(['error' => 'Not found'], 404);

        $data = $r->validate([
            'department_id'  => 'sometimes|nullable|integer',
            'title'          => 'sometimes|string|max:150',
            'description'    => 'sometimes|nullable|string',
            'slug'           => 'sometimes|nullable|string|max:160',
            'regenerate_slug'=> 'sometimes|boolean',
            'shortcode'      => 'sometimes|nullable|string|max:100',

            // accept both url and page_url (UI uses url)
            'url'            => 'sometimes|nullable|string|max:255',
            'page_url'       => 'sometimes|nullable|string|max:255',

            'page_slug'      => 'sometimes|nullable|string|max:160',
            'page_shortcode' => 'sometimes|nullable|string|max:100',
            'position'       => 'sometimes|integer|min:0',
            'active'         => 'sometimes|boolean',
            'metadata'       => 'sometimes|nullable|array',
        ]);

        $departmentId = array_key_exists('department_id', $data)
            ? ($data['department_id'] === null ? null : (int) $data['department_id'])
            : ($row->department_id ?? null);

        $this->validateDepartment($departmentId);

        // SLUG handling (including trash conflict)
        $slug = $row->slug;

        $shouldTouchSlug =
            array_key_exists('slug', $data) ||
            !empty($data['regenerate_slug']) ||
            (isset($data['title']) && $data['title'] !== $row->title && !array_key_exists('slug', $data));

        if ($shouldTouchSlug) {
            if (
                !empty($data['regenerate_slug']) ||
                (array_key_exists('slug', $data) && trim((string) $data['slug']) === '')
            ) {
                $slug = $this->normSlug($data['title'] ?? $row->title ?? 'menu');
            } elseif (array_key_exists('slug', $data)) {
                $slug = $this->normSlug($data['slug']);
            }

            if ($slug === '') return response()->json(['error' => 'Unable to generate slug'], 422);

            $conflict = $this->findUniqueConflict('slug', $slug, (int) $row->id);
            if ($conflict) {
                if ($conflict->deleted_at !== null) {
                    return response()->json([
                        'error' => 'Slug already exists in trash. Restore/permanently delete that item to reuse this slug.',
                        'field' => 'slug',
                        'conflict' => ['id' => $conflict->id, 'title' => $conflict->title],
                    ], 422);
                }
                return response()->json(['error' => 'Slug already in use', 'field' => 'slug'], 422);
            }
        }

        // SHORTCODE
        $shortcode = $row->shortcode;
        if (array_key_exists('shortcode', $data)) {
            $val = trim((string) $data['shortcode']);
            if ($val === '') {
                $shortcode = $this->generateMenuShortcode((int) $row->id);
            } else {
                $val = strtoupper($val);
                $conflict = $this->findUniqueConflict('shortcode', $val, (int) $row->id);
                if ($conflict) return response()->json(['error' => 'Shortcode already in use', 'field' => 'shortcode'], 422);
                $shortcode = $val;
            }
        }

        // PAGE URL (alias url)
        $pageUrl = $row->page_url ?? null;
        if (array_key_exists('url', $data)) {
            $pageUrl = trim((string) $data['url']) ?: null;
        } elseif (array_key_exists('page_url', $data)) {
            $pageUrl = trim((string) $data['page_url']) ?: null;
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
                $conflict = $this->findUniqueConflict('page_shortcode', $pageShortcode, (int) $row->id);
                if ($conflict) return response()->json(['error' => 'Page shortcode already in use', 'field' => 'page_shortcode'], 422);
            }
        }

        // METADATA JSON
        $metaJson = $row->metadata;
        if (array_key_exists('metadata', $data)) {
            $metaJson = $data['metadata'] === null ? null : json_encode($data['metadata']);
        }

        $actor = $this->actor($r);

        $upd = [
            'department_id'   => $departmentId,

            // ✅ force menu columns
            'item_type'       => 'menu',
            'contact_info_id' => null,

            'title'          => $data['title'] ?? $row->title,
            'description'    => array_key_exists('description', $data) ? $data['description'] : $row->description,
            'slug'           => $slug,
            'shortcode'      => $shortcode,
            'page_url'       => $pageUrl,
            'page_slug'      => $pageSlug,
            'page_shortcode' => $pageShortcode,
            'position'       => array_key_exists('position', $data) ? (int) $data['position'] : (int) $row->position,
            'active'         => array_key_exists('active', $data) ? (bool) $data['active'] : (bool) $row->active,
            'metadata'       => $metaJson,

            'updated_at'     => now(),
            'updated_by'     => $actor['id'] ?: null,
            'updated_at_ip'  => $r->ip(),
        ];

        try {
            DB::table($this->table)->where('id', (int) $row->id)->update($upd);
        } catch (\Throwable $e) {
            $handled = $this->handleUniqueException($e);
            if ($handled) return $handled;
            throw $e;
        }

        $fresh = DB::table($this->table)->where('id', (int) $row->id)->first();
        $rows = [$fresh];
        $this->attachContactInfosToRows($rows);

        return response()->json(['success' => true, 'data' => $rows[0]]);
    }

    public function destroy(Request $r, $id)
    {
        $ok = DB::table($this->table)
            ->where('id', (int) $id)
            ->where('item_type', 'menu')
            ->whereNull('deleted_at')
            ->exists();

        if (!$ok) return response()->json(['error' => 'Not found'], 404);

        DB::table($this->table)->where('id', (int) $id)->update([
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
            ->where('item_type', 'menu')
            ->whereNotNull('deleted_at')
            ->exists();

        if (!$ok) return response()->json(['error' => 'Not found in bin'], 404);

        DB::table($this->table)->where('id', (int) $id)->update([
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
            ->where('item_type', 'menu')
            ->exists();

        if (!$exists) return response()->json(['error' => 'Not found'], 404);

        DB::table($this->table)->where('id', (int) $id)->delete();

        return response()->json(['success' => true, 'message' => 'Deleted permanently']);
    }

    public function toggleActive(Request $r, $id)
    {
        $row = DB::table($this->table)
            ->where('id', (int) $id)
            ->where('item_type', 'menu')
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['error' => 'Not found'], 404);

        DB::table($this->table)->where('id', (int) $id)->update([
            'active'        => !$row->active,
            'updated_at'    => now(),
            'updated_by'    => $this->actor($r)['id'] ?: null,
            'updated_at_ip' => $r->ip(),
        ]);

        return response()->json(['success' => true, 'message' => 'Status updated']);
    }

    public function reorder(Request $r)
    {
        $payload = $r->validate([
            'orders'            => 'required|array|min:1',
            'orders.*.id'       => 'required|integer',
            'orders.*.position' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($payload['orders'] as $o) {
                $id  = (int) $o['id'];
                $pos = (int) $o['position'];

                DB::table($this->table)
                    ->where('id', $id)
                    ->where('item_type', 'menu') // ✅ menu only
                    ->whereNull('deleted_at')
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
            return response()->json(['error' => 'Reorder failed', 'details' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Order updated']);
    }

    /* ============================================
     | Public routes (no auth)
     |  GET /api/public/top-header-menus
     |  Returns: contact rows + menu rows
     |============================================ */

    public function publicIndex(Request $r)
    {
        $departmentIdParam = $r->query('department_id', null); // int | 'null' | null | 'any'

        $q = DB::table($this->table)
            ->whereNull('deleted_at')
            ->where('active', true);

        // Always include contacts; filter menus by department scope if given
        if ($departmentIdParam !== null && $departmentIdParam !== '' && $departmentIdParam !== 'any') {
            if ($departmentIdParam === 'null') {
                $q->where(function ($x) {
                    $x->where('item_type', 'contact')
                      ->orWhere(function ($m) {
                          $m->where('item_type', 'menu')->whereNull('department_id');
                      });
                });
            } else {
                $deptId = (int) $departmentIdParam;
                $q->where(function ($x) use ($deptId) {
                    $x->where('item_type', 'contact')
                      ->orWhere(function ($m) use ($deptId) {
                          $m->where('item_type', 'menu')
                            ->where(function ($y) use ($deptId) {
                                $y->whereNull('department_id')
                                  ->orWhere('department_id', $deptId);
                            });
                      });
                });
            }
        }

        $rows = $q->orderByRaw("CASE WHEN item_type='contact' THEN 0 ELSE 1 END")
            ->orderBy('position', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $this->attachContactInfosToRows($rows);

        return response()->json(['success' => true, 'data' => $rows]);
    }

    /* ============================================
     | Attach contact info objects (safe)
     |============================================ */

    private function attachContactInfosToRows($rows): void
    {
        if (!$rows) return;

        $ids = [];
        foreach ($rows as $r) {
            // helpful alias for UI
            if (isset($r->page_url) && !isset($r->url)) $r->url = $r->page_url;

            if (($r->item_type ?? 'menu') === 'contact' && !empty($r->contact_info_id)) {
                $ids[] = (int) $r->contact_info_id;
            }
        }

        $ids = array_values(array_unique(array_filter($ids)));
        if (!$ids) return;

        $ciTable = $this->contactInfoTable();
        $q = DB::table($ciTable)->whereIn('id', $ids);
        if (Schema::hasColumn($ciTable, 'deleted_at')) $q->whereNull('deleted_at');
        $map = $q->get()->keyBy('id');

        foreach ($rows as $r) {
            if (($r->item_type ?? 'menu') === 'contact' && !empty($r->contact_info_id)) {
                $r->contact_info = $map[(int) $r->contact_info_id] ?? null;
            }
        }
    }

    /* ============================================
     | Contact selection (GLOBAL) - GET/PUT/DELETE
     | URL: /api/top-header-menus/contact-info
     |============================================ */

    public function getContactSelection(Request $r)
    {
        $rows = DB::table($this->table)
            ->whereNull('deleted_at')
            ->where('item_type', 'contact')
            ->orderBy('position', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $this->attachContactInfosToRows($rows);

        $ids = [];
        foreach ($rows as $row) {
            if (!empty($row->contact_info_id)) $ids[] = (int) $row->contact_info_id;
        }
        $ids = array_slice(array_values(array_unique($ids)), 0, 2);

        return response()->json([
            'success' => true,
            'data' => [
                'contact_info_ids' => $ids,
                'items' => $rows,
            ],
        ]);
    }

    public function putContactSelection(Request $r)
    {
        $ids = $r->input('contact_info_ids', $r->input('ids', []));
        if (!is_array($ids)) $ids = [];
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (count($ids) !== 2) {
            return response()->json(['error' => 'Select exactly 2 contact infos.'], 422);
        }

        // validate contact infos exist
        $ciTable = $this->contactInfoTable();
        $q = DB::table($ciTable)->whereIn('id', $ids);
        if (Schema::hasColumn($ciTable, 'deleted_at')) $q->whereNull('deleted_at');
        $cis = $q->get()->keyBy('id');

        if ($cis->count() !== 2) {
            return response()->json(['error' => 'Invalid contact_info_ids'], 422);
        }

        $actor = $this->actor($r);
        $now = now();

        DB::beginTransaction();
        try {
            // soft-delete non-selected contact rows
            DB::table($this->table)
                ->where('item_type', 'contact')
                ->whereNull('deleted_at')
                ->whereNotIn('contact_info_id', $ids)
                ->update([
                    'deleted_at'    => $now,
                    'updated_at'    => $now,
                    'updated_by'    => $actor['id'] ?: null,
                    'updated_at_ip' => $r->ip(),
                ]);

            foreach ($ids as $pos => $cid) {
                $existing = DB::table($this->table)
                    ->where('item_type', 'contact')
                    ->where('contact_info_id', $cid)
                    ->first(); // includes trashed too

                $ci = $cis[$cid];

                // stable unique slug for contact row
                $baseSlug = 'top-contact-' . $cid;
                $slug = $baseSlug;
                $i = 0;

                $confQ = DB::table($this->table)->where('slug', $slug);
                if ($existing) $confQ->where('id', '!=', (int) $existing->id);

                while ($confQ->exists()) {
                    $i++;
                    $slug = $baseSlug . '-' . $i;
                    if ($i > 20) {
                        $slug = $baseSlug . '-' . Str::lower(Str::random(4));
                        break;
                    }
                    $confQ = DB::table($this->table)->where('slug', $slug);
                    if ($existing) $confQ->where('id', '!=', (int) $existing->id);
                }

                $title =
                    ($ci->label ?? null) ? (string) $ci->label :
                    (($ci->title ?? null) ? (string) $ci->title :
                    (($ci->type ?? null) ? (string) $ci->type :
                    (($ci->name ?? null) ? (string) $ci->name :
                    ('Contact #' . $cid))));

                if ($existing) {
                    DB::table($this->table)->where('id', (int) $existing->id)->update([
                        'department_id'   => null,
                        'item_type'       => 'contact',
                        'contact_info_id' => $cid,

                        'title'           => $title,
                        'description'     => null,
                        'slug'            => $slug,

                        'shortcode'       => null,
                        'page_url'        => null,
                        'page_slug'       => null,
                        'page_shortcode'  => null,

                        'position'        => (int) $pos,
                        'active'          => true,
                        'deleted_at'      => null,

                        'updated_at'      => $now,
                        'updated_by'      => $actor['id'] ?: null,
                        'updated_at_ip'   => $r->ip(),
                    ]);
                } else {
                    DB::table($this->table)->insert([
                        'uuid'            => (string) Str::uuid(),
                        'department_id'   => null,
                        'item_type'       => 'contact',
                        'contact_info_id' => $cid,

                        'title'           => $title,
                        'description'     => null,
                        'slug'            => $slug,

                        'shortcode'       => null,
                        'page_url'        => null,
                        'page_slug'       => null,
                        'page_shortcode'  => null,

                        'position'        => (int) $pos,
                        'active'          => true,
                        'metadata'        => null,

                        'created_at'      => $now,
                        'updated_at'      => $now,
                        'created_by'      => $actor['id'] ?: null,
                        'updated_by'      => $actor['id'] ?: null,
                        'created_at_ip'   => $r->ip(),
                        'updated_at_ip'   => $r->ip(),
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $handled = $this->handleUniqueException($e);
            if ($handled) return $handled;
            throw $e;
        }

        return response()->json(['success' => true, 'message' => 'Contact infos saved']);
    }

    public function deleteContactSelection(Request $r)
    {
        $actor = $this->actor($r);
        $now = now();

        DB::table($this->table)
            ->where('item_type', 'contact')
            ->whereNull('deleted_at')
            ->update([
                'deleted_at'    => $now,
                'updated_at'    => $now,
                'updated_by'    => $actor['id'] ?: null,
                'updated_at_ip' => $r->ip(),
            ]);

        return response()->json(['success' => true, 'message' => 'Contact selection cleared']);
    }
}
