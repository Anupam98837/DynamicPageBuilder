<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;

class HeaderMenuController extends Controller
{
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

    /**
     * Safe activity logger (never breaks main flow).
     * Logs ONLY for non-GET actions (called explicitly in POST/PUT/PATCH/DELETE methods).
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

            $enc = function ($v) {
                if ($v === null) return null;
                if (is_string($v)) return $v;
                return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            };

            DB::table('user_data_activity_log')->insert([
                'performed_by'      => max(0, (int) ($actor['id'] ?? 0)),
                'performed_by_role' => $actor['role'] ?? null,
                'ip'                => $r->ip(),
                'user_agent'        => (string) ($r->userAgent() ?? ''),
                'activity'          => $activity,
                'module'            => $module,
                'table_name'        => $tableName,
                'record_id'         => $recordId,
                'changed_fields'    => $changedFields ? $enc(array_values($changedFields)) : null,
                'old_values'        => $enc($oldValues),
                'new_values'        => $enc($newValues),
                'log_note'          => $note,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            // swallow (never affect API functionality)
        }
    }

    private function normSlug(?string $s): string
    {
        $s = (string) $s;
        $s = trim($s);
        $s = $s === '' ? '' : Str::slug($s, '-');
        return $s;
    }

    /** Auto-generate unique menu shortcode (alphanumeric) */
    private function generateMenuShortcode(?int $excludeId = null): string
    {
        $maxTries = 50;
        for ($i = 0; $i < $maxTries; $i++) {
            $code = 'HM' . Str::upper(Str::random(6)); // e.g. HM3K9ZAQ

            $q = DB::table('header_menus')->where('shortcode', $code);
            if ($excludeId) {
                $q->where('id', '!=', $excludeId);
            }

            if (!$q->exists()) {
                return $code;
            }
        }

        // Fallback – extremely unlikely to reach
        return 'HM' . time();
    }

    /** Find a conflicting row for a unique column (includes trashed too) */
    private function findUniqueConflict(string $column, $value, ?int $excludeId = null)
    {
        if ($value === null) return null;
        $value = is_string($value) ? trim($value) : $value;
        if ($value === '' || $value === []) return null;

        $q = DB::table('header_menus')
            ->select('id', 'title', 'deleted_at', $column)
            ->where($column, $value)
            ->whereNull('deleted_at');

        if ($excludeId !== null) {
            $q->where('id', '!=', $excludeId);
        }

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
            if (preg_match("/for key '([^']+)'/i", $msg, $m)) {
                $key = $m[1];
            }

            $field = 'unique field';
            if ($key) {
                $map = [
                    'header_menus_slug_unique'           => 'slug',
                    'header_menus_shortcode_unique'      => 'shortcode',
                    'header_menus_page_shortcode_unique' => 'page_shortcode',
                ];
                $field = $map[$key] ?? $field;
            }

            return response()->json([
                'error' => ucfirst($field) . ' already exists',
                'field' => $field,
            ], 422);
        }

        return null; // not handled
    }

    /** Guard that department exists (if provided) */
    private function validateDepartment(?int $departmentId): void
    {
        if ($departmentId === null) {
            return;
        }

        $ok = DB::table('departments')
            ->where('id', $departmentId)
            ->whereNull('deleted_at')
            ->exists();

        if (!$ok) {
            abort(response()->json(['error' => 'Invalid department_id'], 422));
        }
    }

    /** Guard that parent exists and is not self; and respects department rules */
    private function validateParent(?int $parentId, ?int $selfId = null, ?int $childDepartmentId = null): void
    {
        if ($parentId === null) {
            return;
        }

        if ($selfId !== null && $parentId === $selfId) {
            abort(response()->json(['error' => 'Parent cannot be self'], 422));
        }

        $parent = DB::table('header_menus')
            ->select('id', 'department_id')
            ->where('id', $parentId)
            ->whereNull('deleted_at')
            ->first();

        if (!$parent) {
            abort(response()->json(['error' => 'Invalid parent_id'], 422));
        }

        /**
         * Department compatibility:
         * - If parent is department-specific => child must be same department
         * - If parent is global (NULL) => child can be global or department-specific
         */
        if ($parent->department_id !== null) {
            if ($childDepartmentId === null || (int) $childDepartmentId !== (int) $parent->department_id) {
                abort(response()->json(['error' => 'Parent belongs to a different department'], 422));
            }
        }
    }

    /** Next position among siblings */
    private function nextPosition(?int $parentId): int
    {
        $q = DB::table('header_menus as hm')
            ->leftJoin('departments as d', function ($j) {
                $j->on('d.id', '=', 'hm.department_id')
                    ->whereNull('d.deleted_at');
            })
            ->select('hm.*', 'd.uuid as department_uuid')
            ->whereNull('hm.deleted_at');

        if ($parentId === null) {
            $q->whereNull('parent_id');
        } else {
            $q->where('parent_id', $parentId);
        }

        $max = (int) $q->max('position');
        return $max + 1;
    }

    /* ============================================
     | List / Tree / Resolve (GET only - no logs)
     |============================================ */

    public function index(Request $r)
    {
        $page = max(1, (int) $r->query('page', 1));
        $per  = min(100, max(5, (int) $r->query('per_page', 20)));
        $q    = trim((string) $r->query('q', ''));
        $activeParam = $r->query('active', null); // null, '0', '1'
        $parentId = $r->query('parent_id', 'any'); // 'any' | null | int
        $departmentIdParam = $r->query('department_id', 'any'); // 'any' | null | 'null' | int
        $sort = (string) $r->query('sort', 'position'); // position|title|created_at
        $direction = strtolower((string) $r->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowedSort = ['position', 'title', 'created_at'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'position';
        }

        $base = DB::table('header_menus')
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

        if ($parentId === null || $parentId === 'null') {
            $base->whereNull('parent_id');
        } elseif ($parentId !== 'any') {
            $base->where('parent_id', (int) $parentId);
        }

        // ✅ Department filter (optional)
        if ($departmentIdParam === null || $departmentIdParam === 'null') {
            $base->whereNull('department_id');
        } elseif ($departmentIdParam !== 'any' && $departmentIdParam !== '') {
            $base->where('department_id', (int) $departmentIdParam);
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
        $departmentIdParam = $r->query('department_id', 'any'); // optional

        $base = DB::table('header_menus')
            ->whereNotNull('deleted_at');

        // ✅ Optional department filter in trash too
        if ($departmentIdParam === null || $departmentIdParam === 'null') {
            $base->whereNull('department_id');
        } elseif ($departmentIdParam !== 'any' && $departmentIdParam !== '') {
            $base->where('department_id', (int) $departmentIdParam);
        }

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

    public function tree(Request $r)
    {
        $onlyActive = (int) $r->query('only_active', 0) === 1;
        $departmentIdParam = $r->query('department_id', 'any'); // optional

        $q = DB::table('header_menus as hm')
            ->leftJoin('departments as d', function ($j) {
                $j->on('d.id', '=', 'hm.department_id')
                    ->whereNull('d.deleted_at');
            })
            ->select('hm.*', 'd.uuid as department_uuid')
            ->whereNull('hm.deleted_at');

        if ($onlyActive) {
            $q->where('hm.active', true);
        }

        // ✅ Optional department filter
        if ($departmentIdParam === null || $departmentIdParam === 'null') {
            $q->whereNull('hm.department_id');
        } elseif ($departmentIdParam !== 'any' && $departmentIdParam !== '') {
            $q->where('hm.department_id', (int) $departmentIdParam);
        }

        $rows = $q->orderBy('hm.position', 'asc')
            ->orderBy('hm.id', 'asc')
            ->get();

        // Build tree in memory
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
            'data' => $make(0),
        ]);
    }

    public function resolve(Request $r)
    {
        $slug = $this->normSlug($r->query('slug', ''));
        if ($slug === '') {
            return response()->json(['error' => 'Missing slug'], 422);
        }

        $menu = DB::table('header_menus')
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->where('active', true)
            ->first();

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
            'success'       => true,
            'menu'          => $menu,
            'redirect_url'  => $redirectUrl,
        ]);
    }

    /* ============================================
     | CRUD
     |============================================ */

    public function show(Request $r, $id)
    {
        $row = DB::table('header_menus')
            ->where('id', (int) $id)
            ->first();

        if (!$row) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $row]);
    }

    public function store(Request $r)
    {
        $module = 'header_menus';
        $table  = 'header_menus';

        try {
            $data = $r->validate([
                'title'          => 'required|string|max:150',
                'description'    => 'sometimes|nullable|string',
                'slug'           => 'sometimes|nullable|string|max:160',
                'shortcode'      => 'sometimes|nullable|string|max:100',
                'parent_id'      => 'sometimes|nullable|integer',
                'department_id'  => 'sometimes|nullable|integer',
                'position'       => 'sometimes|integer|min:0',
                'active'         => 'sometimes|boolean',
                'page_slug'      => 'sometimes|nullable|string|max:160',
                'page_shortcode' => 'sometimes|nullable|string|max:100',
                'page_url'       => 'sometimes|nullable|string|max:255',
            ]);

            $departmentId = array_key_exists('department_id', $data)
                ? ($data['department_id'] === null ? null : (int) $data['department_id'])
                : null;

            $this->validateDepartment($departmentId);

            $parentId = array_key_exists('parent_id', $data)
                ? ($data['parent_id'] === null ? null : (int) $data['parent_id'])
                : null;

            $this->validateParent($parentId, null, $departmentId);

            // MENU SLUG
            $slug = $this->normSlug($data['slug'] ?? $data['title'] ?? '');
            if ($slug === '') {
                $this->logActivity($r, 'create_failed', $module, $table, null, ['slug'], null, $data, 'Unable to generate slug');
                return response()->json(['error' => 'Unable to generate slug'], 422);
            }

            // If exists (including trash), follow your existing behavior:
            // - if non-deleted exists => idempotent return
            // - if deleted exists => restore that trashed row
            $existingAny = DB::table('header_menus')->where('slug', $slug)->first();
            if ($existingAny && $existingAny->deleted_at === null) {
                $this->logActivity(
                    $r,
                    'create',
                    $module,
                    $table,
                    (int) $existingAny->id,
                    [],
                    null,
                    ['slug' => $slug],
                    'Menu already exists; not created again.'
                );

                return response()->json([
                    'success'         => true,
                    'data'            => $existingAny,
                    'already_existed' => true,
                    'message'         => 'Menu already exists; not created again.',
                ], 200);
            }

            // MENU SHORTCODE
            $menuShortcode = null;
            if (!empty($data['shortcode'])) {
                $menuShortcode = strtoupper(trim($data['shortcode']));
                $conf = $this->findUniqueConflict('shortcode', $menuShortcode, null);
                if ($conf) {
                    $this->logActivity(
                        $r,
                        'create_failed',
                        $module,
                        $table,
                        null,
                        ['shortcode'],
                        null,
                        ['shortcode' => $menuShortcode],
                        'Menu shortcode already exists'
                    );

                    return response()->json([
                        'error' => 'Menu shortcode already exists',
                        'field' => 'shortcode',
                    ], 422);
                }
            } else {
                $menuShortcode = $this->generateMenuShortcode(null);
            }

            // PAGE FIELDS
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

            if ($pageShortcode) {
                $conf = $this->findUniqueConflict('page_shortcode', $pageShortcode, null);
                if ($conf) {
                    $this->logActivity(
                        $r,
                        'create_failed',
                        $module,
                        $table,
                        null,
                        ['page_shortcode'],
                        null,
                        ['page_shortcode' => $pageShortcode],
                        'Page shortcode already exists'
                    );

                    return response()->json(['error' => 'Page shortcode already exists', 'field' => 'page_shortcode'], 422);
                }
            }

            // If soft-deleted with same slug, revive instead of new insert
            $trashed = DB::table('header_menus')
                ->where('slug', $slug)
                ->whereNotNull('deleted_at')
                ->first();

            $now   = now();
            $actor = $this->actor($r);
            $position = array_key_exists('position', $data)
                ? (int) $data['position']
                : $this->nextPosition($parentId);
            $active = array_key_exists('active', $data)
                ? (bool) $data['active']
                : true;

            if ($trashed) {
                $before = (array) $trashed;

                try {
                    DB::table('header_menus')
                        ->where('id', $trashed->id)
                        ->update([
                            'parent_id'       => $parentId,
                            'department_id'   => $departmentId,
                            'title'           => $data['title'],
                            'description'     => $data['description'] ?? null,
                            'slug'            => $slug,
                            'shortcode'       => $menuShortcode,
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
                } catch (\Throwable $e) {
                    $handled = $this->handleUniqueException($e);
                    if ($handled) {
                        $this->logActivity($r, 'restore_failed', $module, $table, (int) $trashed->id, [], $before, $data, 'Unique constraint / restore failed');
                        return $handled;
                    }
                    $this->logActivity($r, 'restore_failed', $module, $table, (int) $trashed->id, [], $before, $data, $e->getMessage());
                    throw $e;
                }

                $row = DB::table('header_menus')->where('id', $trashed->id)->first();

                $this->logActivity(
                    $r,
                    'restore',
                    $module,
                    $table,
                    (int) $trashed->id,
                    ['deleted_at', 'parent_id', 'department_id', 'title', 'description', 'slug', 'shortcode', 'page_slug', 'page_shortcode', 'page_url', 'position', 'active'],
                    ['before' => $before],
                    ['after' => (array) $row],
                    'Trashed row revived via store()'
                );

                return response()->json([
                    'success'  => true,
                    'data'     => $row,
                    'restored' => true,
                ]);
            }

            try {
                $id = DB::table('header_menus')->insertGetId([
                    'uuid'            => (string) Str::uuid(),
                    'parent_id'       => $parentId,
                    'department_id'   => $departmentId,
                    'title'           => $data['title'],
                    'description'     => $data['description'] ?? null,
                    'slug'            => $slug,
                    'shortcode'       => $menuShortcode,
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
            } catch (\Throwable $e) {
                $handled = $this->handleUniqueException($e);
                if ($handled) {
                    $this->logActivity($r, 'create_failed', $module, $table, null, [], null, $data, 'Unique constraint / insert failed');
                    return $handled;
                }
                $this->logActivity($r, 'create_failed', $module, $table, null, [], null, $data, $e->getMessage());
                throw $e;
            }

            $row = DB::table('header_menus')->where('id', $id)->first();

            $this->logActivity(
                $r,
                'create',
                $module,
                $table,
                (int) $id,
                ['parent_id', 'department_id', 'title', 'description', 'slug', 'shortcode', 'page_slug', 'page_shortcode', 'page_url', 'position', 'active'],
                null,
                (array) $row,
                'Menu created'
            );

            return response()->json(['success' => true, 'data' => $row], 201);
        } catch (ValidationException $e) {
            $this->logActivity($r, 'create_failed', $module, $table, null, ['validation'], null, $e->errors(), 'Validation failed');
            throw $e;
        } catch (HttpResponseException $e) {
            // abort(...) in helpers lands here
            $this->logActivity($r, 'create_failed', $module, $table, null, ['abort'], null, null, 'Request aborted');
            throw $e;
        } catch (\Throwable $e) {
            $this->logActivity($r, 'create_failed', $module, $table, null, ['exception'], null, null, $e->getMessage());
            throw $e;
        }
    }

    public function update(Request $r, $id)
    {
        $module = 'header_menus';
        $table  = 'header_menus';

        try {
            $row = DB::table('header_menus')
                ->where('id', (int) $id)
                ->whereNull('deleted_at')
                ->first();

            if (!$row) {
                $this->logActivity($r, 'update_not_found', $module, $table, (int) $id, [], null, null, 'Not found');
                return response()->json(['error' => 'Not found'], 404);
            }

            $before = (array) $row;

            $data = $r->validate([
                'title'           => 'sometimes|string|max:150',
                'description'     => 'sometimes|nullable|string',
                'slug'            => 'sometimes|nullable|string|max:160',
                'shortcode'       => 'sometimes|nullable|string|max:100',
                'parent_id'       => 'sometimes|nullable|integer',
                'department_id'   => 'sometimes|nullable|integer',
                'position'        => 'sometimes|integer|min:0',
                'active'          => 'sometimes|boolean',
                'regenerate_slug' => 'sometimes|boolean',
                'page_slug'       => 'sometimes|nullable|string|max:160',
                'page_shortcode'  => 'sometimes|nullable|string|max:100',
                'page_url'        => 'sometimes|nullable|string|max:255',
            ]);

            $departmentId = array_key_exists('department_id', $data)
                ? ($data['department_id'] === null ? null : (int) $data['department_id'])
                : ($row->department_id ?? null);

            $this->validateDepartment($departmentId);

            $parentId = array_key_exists('parent_id', $data)
                ? ($data['parent_id'] === null ? null : (int) $data['parent_id'])
                : ($row->parent_id ?? null);

            $this->validateParent($parentId, (int) $row->id, $departmentId);

            /* ================================
             | SLUG (FIXED: checks trash too)
             |================================ */
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
                    $base = $this->normSlug($data['title'] ?? $row->title ?? 'page');
                    $slug = $base;
                } elseif (array_key_exists('slug', $data)) {
                    $slug = $this->normSlug($data['slug']);
                }

                if ($slug === '') {
                    $this->logActivity($r, 'update_failed', $module, $table, (int) $row->id, ['slug'], $before, $data, 'Unable to generate slug');
                    return response()->json(['error' => 'Unable to generate slug'], 422);
                }

                // ✅ IMPORTANT: check including trashed rows (matches DB unique index)
                $conflict = $this->findUniqueConflict('slug', $slug, (int) $row->id);
                if ($conflict) {
                    $this->logActivity($r, 'update_failed', $module, $table, (int) $row->id, ['slug'], $before, ['slug' => $slug], 'Slug conflict');

                    if ($conflict->deleted_at !== null) {
                        return response()->json([
                            'error'   => 'Slug already exists in trash. Permanently delete/restore that item to reuse this slug.',
                            'field'   => 'slug',
                            'conflict' => ['id' => $conflict->id, 'title' => $conflict->title],
                        ], 422);
                    }

                    return response()->json(['error' => 'Slug already in use', 'field' => 'slug'], 422);
                }
            }

            /* ================================
             | SHORTCODE (also check trash)
             |================================ */
            $menuShortcode = $row->shortcode;
            if (array_key_exists('shortcode', $data)) {
                $val = trim((string) $data['shortcode']);
                if ($val === '') {
                    $menuShortcode = $this->generateMenuShortcode((int) $row->id);
                } else {
                    $val = strtoupper($val);
                    $conflict = $this->findUniqueConflict('shortcode', $val, (int) $row->id);
                    if ($conflict) {
                        $this->logActivity($r, 'update_failed', $module, $table, (int) $row->id, ['shortcode'], $before, ['shortcode' => $val], 'Menu shortcode already in use');

                        return response()->json([
                            'error' => 'Menu shortcode already in use',
                            'field' => 'shortcode',
                        ], 422);
                    }
                    $menuShortcode = $val;
                }
            }

            /* ================================
             | PAGE FIELDS (check trash too)
             |================================ */
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
                    if ($conflict) {
                        $this->logActivity($r, 'update_failed', $module, $table, (int) $row->id, ['page_shortcode'], $before, ['page_shortcode' => $pageShortcode], 'Page shortcode already in use');
                        return response()->json(['error' => 'Page shortcode already in use', 'field' => 'page_shortcode'], 422);
                    }
                }
            }

            $pageUrl = array_key_exists('page_url', $data)
                ? (trim((string) $data['page_url']) ?: null)
                : ($row->page_url ?? null);

            $upd = [
                'parent_id'       => $parentId,
                'department_id'   => $departmentId,
                'title'           => $data['title'] ?? $row->title,
                'description'     => array_key_exists('description', $data) ? $data['description'] : $row->description,
                'slug'            => $slug,
                'shortcode'       => $menuShortcode,
                'page_slug'       => $pageSlug,
                'page_shortcode'  => $pageShortcode,
                'page_url'        => $pageUrl,
                'position'        => array_key_exists('position', $data) ? (int) $data['position'] : $row->position,
                'active'          => array_key_exists('active', $data) ? (bool) $data['active'] : (bool) $row->active,
                'updated_at'      => now(),
                'updated_by'      => $this->actor($r)['id'] ?: null,
                'updated_at_ip'   => $r->ip(),
            ];

            try {
                DB::table('header_menus')
                    ->where('id', $row->id)
                    ->update($upd);
            } catch (\Throwable $e) {
                $handled = $this->handleUniqueException($e);
                if ($handled) {
                    $this->logActivity($r, 'update_failed', $module, $table, (int) $row->id, [], $before, $upd, 'Unique constraint / update failed');
                    return $handled;
                }
                $this->logActivity($r, 'update_failed', $module, $table, (int) $row->id, [], $before, $upd, $e->getMessage());
                throw $e;
            }

            $fresh = DB::table('header_menus')
                ->where('id', $row->id)
                ->first();

            // Diff for changed_fields + old/new snapshots
            $track = ['parent_id','department_id','title','description','slug','shortcode','page_slug','page_shortcode','page_url','position','active'];
            $changed = [];
            $oldVals = [];
            $newVals = [];

            foreach ($track as $k) {
                $ov = $row->{$k} ?? null;
                $nv = $fresh->{$k} ?? null;

                // normalize a bit (booleans/ints)
                if (is_bool($ov) || is_bool($nv)) {
                    $ov = (bool) $ov;
                    $nv = (bool) $nv;
                }

                if ((string)($ov ?? '') !== (string)($nv ?? '')) {
                    $changed[] = $k;
                    $oldVals[$k] = $ov;
                    $newVals[$k] = $nv;
                }
            }

            $this->logActivity(
                $r,
                'update',
                $module,
                $table,
                (int) $row->id,
                $changed,
                $oldVals,
                $newVals,
                $changed ? 'Menu updated' : 'Update called but no tracked fields changed'
            );

            return response()->json(['success' => true, 'data' => $fresh]);
        } catch (ValidationException $e) {
            $this->logActivity($r, 'update_failed', $module, $table, (int) $id, ['validation'], null, $e->errors(), 'Validation failed');
            throw $e;
        } catch (HttpResponseException $e) {
            $this->logActivity($r, 'update_failed', $module, $table, (int) $id, ['abort'], null, null, 'Request aborted');
            throw $e;
        } catch (\Throwable $e) {
            $this->logActivity($r, 'update_failed', $module, $table, (int) $id, ['exception'], null, null, $e->getMessage());
            throw $e;
        }
    }

    public function destroy(Request $r, $id)
    {
        $module = 'header_menus';
        $table  = 'header_menus';

        $row = DB::table('header_menus')
            ->where('id', (int) $id)
            ->first();

        $exists = $row && $row->deleted_at === null;

        if (!$exists) {
            $this->logActivity($r, 'delete_not_found', $module, $table, (int) $id, [], null, null, 'Not found');
            return response()->json(['error' => 'Not found'], 404);
        }

        $before = (array) $row;

        DB::table('header_menus')
            ->where('id', (int) $id)
            ->update([
                'deleted_at'    => now(),
                'updated_at'    => now(),
                'updated_by'    => $this->actor($r)['id'] ?: null,
                'updated_at_ip' => $r->ip(),
            ]);

        $this->logActivity(
            $r,
            'delete',
            $module,
            $table,
            (int) $id,
            ['deleted_at'],
            ['deleted_at' => $before['deleted_at'] ?? null],
            ['deleted_at' => now()->toDateTimeString()],
            'Moved to bin'
        );

        return response()->json(['success' => true, 'message' => 'Moved to bin']);
    }

    public function restore(Request $r, $id)
    {
        $module = 'header_menus';
        $table  = 'header_menus';

        $row = DB::table('header_menus')
            ->where('id', (int) $id)
            ->first();

        $ok = $row && $row->deleted_at !== null;

        if (!$ok) {
            $this->logActivity($r, 'restore_not_found', $module, $table, (int) $id, [], null, null, 'Not found in bin');
            return response()->json(['error' => 'Not found in bin'], 404);
        }

        $before = (array) $row;

        DB::table('header_menus')
            ->where('id', (int) $id)
            ->update([
                'deleted_at'    => null,
                'updated_at'    => now(),
                'updated_by'    => $this->actor($r)['id'] ?: null,
                'updated_at_ip' => $r->ip(),
            ]);

        $this->logActivity(
            $r,
            'restore',
            $module,
            $table,
            (int) $id,
            ['deleted_at'],
            ['deleted_at' => $before['deleted_at'] ?? null],
            ['deleted_at' => null],
            'Restored'
        );

        return response()->json(['success' => true, 'message' => 'Restored']);
    }

    public function forceDelete(Request $r, $id)
    {
        $module = 'header_menus';
        $table  = 'header_menus';

        $row = DB::table('header_menus')
            ->where('id', (int) $id)
            ->first();

        if (!$row) {
            $this->logActivity($r, 'force_delete_not_found', $module, $table, (int) $id, [], null, null, 'Not found');
            return response()->json(['error' => 'Not found'], 404);
        }

        $before = (array) $row;

        DB::table('header_menus')
            ->where('id', (int) $id)
            ->delete();

        $this->logActivity(
            $r,
            'force_delete',
            $module,
            $table,
            (int) $id,
            [],
            $before,
            null,
            'Deleted permanently'
        );

        return response()->json(['success' => true, 'message' => 'Deleted permanently']);
    }

    public function toggleActive(Request $r, $id)
    {
        $module = 'header_menus';
        $table  = 'header_menus';

        $row = DB::table('header_menus')
            ->where('id', (int) $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) {
            $this->logActivity($r, 'toggle_active_not_found', $module, $table, (int) $id, [], null, null, 'Not found');
            return response()->json(['error' => 'Not found'], 404);
        }

        $old = (bool) $row->active;
        $new = !$old;

        DB::table('header_menus')
            ->where('id', (int) $id)
            ->update([
                'active'        => $new,
                'updated_at'    => now(),
                'updated_by'    => $this->actor($r)['id'] ?: null,
                'updated_at_ip' => $r->ip(),
            ]);

        $this->logActivity(
            $r,
            'toggle_active',
            $module,
            $table,
            (int) $id,
            ['active'],
            ['active' => $old],
            ['active' => $new],
            'Status updated'
        );

        return response()->json(['success' => true, 'message' => 'Status updated']);
    }

    public function reorder(Request $r)
    {
        $module = 'header_menus';
        $table  = 'header_menus';

        try {
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
                    $pid = array_key_exists('parent_id', $o)
                        ? ($o['parent_id'] === null ? null : (int) $o['parent_id'])
                        : null;

                    $row = DB::table('header_menus')
                        ->where('id', $id)
                        ->whereNull('deleted_at')
                        ->first();

                    if (!$row) {
                        continue;
                    }

                    if ($pid !== null) {
                        $childDept = $row->department_id === null ? null : (int) $row->department_id;
                        $this->validateParent($pid, $id, $childDept);
                    }

                    // protect parent change if needed
                    $currentPid = $row->parent_id === null ? null : (int) $row->parent_id;
                    $incomingPid = array_key_exists('parent_id', $o)
                        ? ($o['parent_id'] === null ? null : (int) $o['parent_id'])
                        : $currentPid;

                    if ($incomingPid !== $currentPid) {
                        throw new \RuntimeException("Parent change not allowed for id {$id}");
                    }

                    DB::table('header_menus')->where('id', $id)->update([
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
                    $module,
                    $table,
                    null,
                    ['orders'],
                    null,
                    $payload,
                    $e->getMessage()
                );

                return response()->json([
                    'error'   => 'Reorder failed',
                    'details' => $e->getMessage(),
                ], 422);
            }

            $this->logActivity(
                $r,
                'reorder',
                $module,
                $table,
                null,
                ['orders'],
                null,
                $payload,
                'Order updated'
            );

            return response()->json(['success' => true, 'message' => 'Order updated']);
        } catch (ValidationException $e) {
            $this->logActivity($r, 'reorder_failed', $module, $table, null, ['validation'], null, $e->errors(), 'Validation failed');
            throw $e;
        } catch (HttpResponseException $e) {
            $this->logActivity($r, 'reorder_failed', $module, $table, null, ['abort'], null, null, 'Request aborted');
            throw $e;
        }
    }

    public function publicTree(Request $r)
    {
        $onlyActive = true;
        $onlyTopLevel = (int) $r->query('top_level', 0) === 1;

        $departmentIdParam = $r->query('department_id', null);

        $q = DB::table('header_menus as hm')
            ->leftJoin('departments as d', function ($j) {
                $j->on('d.id', '=', 'hm.department_id')
                    ->whereNull('d.deleted_at');
            })
            ->select('hm.*', 'd.uuid as department_uuid')
            ->whereNull('hm.deleted_at')
            ->where('hm.active', true);

        if ($departmentIdParam !== null && $departmentIdParam !== '' && $departmentIdParam !== 'any') {
            if ($departmentIdParam === 'null') {
                $q->whereNull('department_id');
            } else {
                $deptId = (int) $departmentIdParam;
                $q->where(function ($x) use ($deptId) {
                    $x->whereNull('hm.department_id')
                        ->orWhere('hm.department_id', $deptId);
                });
            }
        }

        if ($onlyTopLevel) {
            $q->whereNull('hm.parent_id');
        }

        $rows = $q->orderBy('hm.position', 'asc')
            ->orderBy('hm.id', 'asc')
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
            'data' => $make(0),
        ]);
    }
}
