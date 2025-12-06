<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DepartmentMenuController extends Controller
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

    /** Resolve department by numeric id or uuid */
    private function resolveDepartmentId($idOrUuid): ?int
    {
        if (is_numeric($idOrUuid)) {
            return (int) $idOrUuid;
        }
        $row = DB::table('departments')
            ->where('uuid', (string) $idOrUuid)
            ->whereNull('deleted_at')
            ->first();
        return $row ? (int) $row->id : null;
    }

    private function normSlug(?string $s): string
    {
        $s = (string) $s;
        $s = trim($s);
        $s = $s === '' ? '' : Str::slug($s, '-');
        return $s;
    }

    /** Ensure slug is unique per department (optionally ignoring self) */
    private function uniqueSlug(int $departmentId, string $base, ?int $ignoreId = null): string
    {
        $slug = $base !== '' ? $base : 'page';
        $try  = $slug;
        $i    = 2;

        while (true) {
            $q = DB::table('department_menus')
                ->where('department_id', $departmentId)
                ->where('slug', $try);
            if ($ignoreId) $q->where('id', '!=', $ignoreId);
            if (!$q->whereNull('deleted_at')->exists()) {
                return $try;
            }
            $try = $slug . '-' . $i;
            $i++;
            if ($i > 200) { // safety
                $try = $slug . '-' . Str::lower(Str::random(4));
                if (!$q->where('slug', $try)->exists()) return $try;
            }
        }
    }

    /** Generate an uppercase code if not supplied */
    private function makeCode(int $len = 10): string
    {
        return Str::upper(Str::replace(['-','_'], '', Str::uuid()->toString()));
    }

    /** Guard that parent belongs to same department and is not self */
    private function validateParent(?int $parentId, int $departmentId, ?int $selfId = null): void
    {
        if ($parentId === null) return;
        if ($selfId !== null && $parentId === $selfId) {
            abort(response()->json(['error' => 'Parent cannot be self'], 422));
        }
        $ok = DB::table('department_menus')
            ->where('id', $parentId)
            ->where('department_id', $departmentId)
            ->whereNull('deleted_at')
            ->exists();
        if (!$ok) {
            abort(response()->json(['error' => 'Invalid parent_id'], 422));
        }
    }

    /** When marking default, unset siblings in same (department_id, parent_id) */
    private function setDefaultWithinSiblings(int $departmentId, ?int $parentId, int $menuId): void
    {
        DB::table('department_menus')
            ->where('department_id', $departmentId)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($parentId) {
                if ($parentId === null) $q->whereNull('parent_id');
                else $q->where('parent_id', $parentId);
            })
            ->update(['is_default' => false, 'updated_at' => now()]);

        DB::table('department_menus')
            ->where('id', $menuId)
            ->update(['is_default' => true, 'updated_at' => now()]);
    }

    /** Next position among siblings */
    private function nextPosition(int $departmentId, ?int $parentId): int
    {
        $q = DB::table('department_menus')
            ->where('department_id', $departmentId)
            ->whereNull('deleted_at');
        if ($parentId === null) $q->whereNull('parent_id');
        else $q->where('parent_id', $parentId);
        $max = (int) $q->max('position');
        return $max + 1;
    }

    /* ============================================
     | List / Tree / Resolve
     |============================================ */

    public function index(Request $r, $department)
    {
        $deptId = $this->resolveDepartmentId($department);
        if (!$deptId) return response()->json(['error' => 'Department not found'], 404);

        $page = max(1, (int) $r->query('page', 1));
        $per  = min(100, max(5, (int) $r->query('per_page', 20)));
        $q    = trim((string) $r->query('q', ''));
        $activeParam = $r->query('active', null); // null, '0', '1'
        $parentId = $r->query('parent_id', 'any'); // 'any' | null | int
        $sort = (string) $r->query('sort', 'position'); // position|title|created_at
        $direction = strtolower((string) $r->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowedSort = ['position','title','created_at'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'position';

        $base = DB::table('department_menus')
            ->where('department_id', $deptId)
            ->whereNull('deleted_at');

        if ($q !== '') {
            $base->where(function($x) use ($q) {
                $x->where('title','like',"%{$q}%")
                  ->orWhere('slug','like',"%{$q}%")
                  ->orWhere('code','like',"%{$q}%");
            });
        }

        if ($activeParam !== null && in_array((string)$activeParam, ['0','1'], true)) {
            $base->where('active', (int) $activeParam === 1);
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
                'page' => $page, 'per_page' => $per, 'total' => $total
            ],
        ]);
    }

    public function indexTrash(Request $r, $department)
    {
        $deptId = $this->resolveDepartmentId($department);
        if (!$deptId) return response()->json(['error' => 'Department not found'], 404);

        $page = max(1, (int) $r->query('page', 1));
        $per  = min(100, max(5, (int) $r->query('per_page', 20)));

        $base = DB::table('department_menus')
            ->where('department_id', $deptId)
            ->whereNotNull('deleted_at');

        $total = (clone $base)->count();
        $rows  = $base->orderBy('deleted_at', 'desc')->forPage($page, $per)->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
            'pagination' => ['page'=>$page,'per_page'=>$per,'total'=>$total],
        ]);
    }

    public function tree(Request $r, $department)
    {
        $deptId = $this->resolveDepartmentId($department);
        if (!$deptId) return response()->json(['error' => 'Department not found'], 404);

        $onlyActive = (int) $r->query('only_active', 0) === 1;

        $q = DB::table('department_menus')
            ->where('department_id', $deptId)
            ->whereNull('deleted_at');

        if ($onlyActive) $q->where('active', true);

        $rows = $q->orderBy('position','asc')->orderBy('id','asc')->get();

        // Build tree in memory
        $byParent = [];
        foreach ($rows as $row) {
            $pid = $row->parent_id ?? 0;
            $byParent[$pid][] = $row;
        }

        $make = function($pid) use (&$make, &$byParent) {
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

    /** Resolve a slug inside a department; include default child if present */
    public function resolve(Request $r, $department)
    {
        $deptId = $this->resolveDepartmentId($department);
        if (!$deptId) return response()->json(['error' => 'Department not found'], 404);

        $slug = $this->normSlug($r->query('slug', ''));
        if ($slug === '') return response()->json(['error' => 'Missing slug'], 422);

        $menu = DB::table('department_menus')
            ->where('department_id', $deptId)
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->first();

        if (!$menu) return response()->json(['error' => 'Not found'], 404);

        // If this node has a default child, include it
        $defaultChild = DB::table('department_menus')
            ->where('department_id', $deptId)
            ->where('parent_id', $menu->id)
            ->whereNull('deleted_at')
            ->where('active', true)
            ->where('is_default', true)
            ->orderBy('position','asc')
            ->first();

        return response()->json([
            'success' => true,
            'menu' => $menu,
            'default_child' => $defaultChild,
            'redirect_slug' => $defaultChild?->slug,
        ]);
    }

    /* ============================================
     | CRUD
     |============================================ */

    public function show(Request $r, $department, $id)
    {
        $deptId = $this->resolveDepartmentId($department);
        if (!$deptId) return response()->json(['error' => 'Department not found'], 404);

        $row = DB::table('department_menus')
            ->where('id', (int) $id)
            ->where('department_id', $deptId)
            ->first();

        if (!$row) return response()->json(['error' => 'Not found'], 404);
        return response()->json(['success' => true, 'data' => $row]);
    }

    public function store(Request $r, $department)
    {
        $deptId = $this->resolveDepartmentId($department);
        if (!$deptId) return response()->json(['error' => 'Department not found'], 404);

        $data = $r->validate([
            'title'       => 'required|string|max:150',
            'description' => 'sometimes|nullable|string',
            'slug'        => 'sometimes|nullable|string|max:160',
            'code'        => 'sometimes|nullable|string|max:24',
            'parent_id'   => 'sometimes|nullable|integer',
            'is_default'  => 'sometimes|boolean',
            'position'    => 'sometimes|integer|min:0',
            'active'      => 'sometimes|boolean',
        ]);

        $parentId = array_key_exists('parent_id', $data) ? ($data['parent_id'] === null ? null : (int) $data['parent_id']) : null;
        $this->validateParent($parentId, $deptId, null);

        $slug = $this->normSlug($data['slug'] ?? $data['title'] ?? '');
        $slug = $this->uniqueSlug($deptId, $slug);

        $code = $data['code'] ?? null;
        if ($code) {
            // ensure unique globally
            $exists = DB::table('department_menus')->where('code', $code)->exists();
            if ($exists) return response()->json(['error' => 'Code already exists'], 422);
        } else {
            $code = Str::upper(Str::random(10));
            while (DB::table('department_menus')->where('code', $code)->exists()) {
                $code = Str::upper(Str::random(10));
            }
        }

        $now  = now();
        $actor = $this->actor($r);
        $position = array_key_exists('position', $data) ? (int) $data['position'] : $this->nextPosition($deptId, $parentId);
        $active = array_key_exists('active', $data) ? (bool) $data['active'] : true;

        $id = DB::table('department_menus')->insertGetId([
            'uuid'         => (string) Str::uuid(),
            'department_id'=> $deptId,
            'parent_id'    => $parentId,
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'slug'         => $slug,
            'code'         => $code,
            'is_default'   => (bool) ($data['is_default'] ?? false),
            'position'     => $position,
            'active'       => $active,
            'created_at'   => $now,
            'updated_at'   => $now,
            'created_by'   => $actor['id'] ?: null,
            'updated_by'   => $actor['id'] ?: null,
            'created_at_ip'=> $r->ip(),
            'updated_at_ip'=> $r->ip(),
        ]);

        if (!empty($data['is_default'])) {
            $this->setDefaultWithinSiblings($deptId, $parentId, $id);
        }

        $row = DB::table('department_menus')->where('id',$id)->first();
        return response()->json(['success'=>true,'data'=>$row], 201);
    }

    public function update(Request $r, $department, $id)
    {
        $deptId = $this->resolveDepartmentId($department);
        if (!$deptId) return response()->json(['error' => 'Department not found'], 404);

        $row = DB::table('department_menus')
            ->where('id', (int) $id)
            ->where('department_id', $deptId)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['error' => 'Not found'], 404);

        $data = $r->validate([
            'title'       => 'sometimes|string|max:150',
            'description' => 'sometimes|nullable|string',
            'slug'        => 'sometimes|nullable|string|max:160', // pass empty to auto-regenerate
            'code'        => 'sometimes|nullable|string|max:24',
            'parent_id'   => 'sometimes|nullable|integer',
            'is_default'  => 'sometimes|boolean',
            'position'    => 'sometimes|integer|min:0',
            'active'      => 'sometimes|boolean',
            'regenerate_slug' => 'sometimes|boolean',
        ]);

        $parentId = array_key_exists('parent_id', $data)
            ? ($data['parent_id'] === null ? null : (int) $data['parent_id'])
            : ($row->parent_id ?? null);

        $this->validateParent($parentId, $deptId, (int) $row->id);

        // slug: provided → normalize & uniquify; empty + regenerate_slug → from title; else keep old
        $slug = $row->slug;
        if (array_key_exists('slug', $data)) {
            $norm = $this->normSlug($data['slug']);
            if ($norm === '' || (!empty($data['regenerate_slug']))) {
                $base = $this->normSlug($data['title'] ?? $row->title ?? 'page');
                $slug = $this->uniqueSlug($deptId, $base, (int) $row->id);
            } else {
                $slug = $this->uniqueSlug($deptId, $norm, (int) $row->id);
            }
        } elseif (!empty($data['regenerate_slug']) || (isset($data['title']) && $data['title'] !== $row->title)) {
            $base = $this->normSlug($data['title'] ?? $row->title ?? 'page');
            $slug = $this->uniqueSlug($deptId, $base, (int) $row->id);
        }

        // code: if provided ensure unique
        if (array_key_exists('code', $data) && $data['code']) {
            $exists = DB::table('department_menus')->where('code', $data['code'])->where('id','!=',$row->id)->exists();
            if ($exists) return response()->json(['error' => 'Code already exists'], 422);
        }

        $upd = [
            'parent_id'    => $parentId,
            'title'        => $data['title'] ?? $row->title,
            'description'  => array_key_exists('description',$data) ? $data['description'] : $row->description,
            'slug'         => $slug,
            'code'         => array_key_exists('code',$data) ? ($data['code'] ?: $row->code) : $row->code,
            'position'     => array_key_exists('position',$data) ? (int) $data['position'] : $row->position,
            'active'       => array_key_exists('active',$data) ? (bool) $data['active'] : (bool) $row->active,
            'updated_at'   => now(),
            'updated_by'   => $this->actor($r)['id'] ?: null,
            'updated_at_ip'=> $r->ip(),
        ];

        DB::table('department_menus')->where('id', $row->id)->update($upd);

        if (array_key_exists('is_default', $data) && (bool)$data['is_default'] === true) {
            $this->setDefaultWithinSiblings($deptId, $parentId, (int) $row->id);
        } elseif (array_key_exists('is_default', $data) && (bool)$data['is_default'] === false) {
            DB::table('department_menus')->where('id', $row->id)->update(['is_default'=>false, 'updated_at'=>now()]);
        }

        $fresh = DB::table('department_menus')->where('id',$row->id)->first();
        return response()->json(['success'=>true, 'data'=>$fresh]);
    }

    public function destroy(Request $r, $department, $id)
    {
        $deptId = $this->resolveDepartmentId($department);
        if (!$deptId) return response()->json(['error' => 'Department not found'], 404);

        $exists = DB::table('department_menus')
            ->where('id', (int) $id)
            ->where('department_id', $deptId)
            ->whereNull('deleted_at')
            ->exists();

        if (!$exists) return response()->json(['error' => 'Not found'], 404);

        DB::table('department_menus')->where('id', (int) $id)->update([
            'deleted_at'   => now(),
            'updated_at'   => now(),
            'updated_by'   => $this->actor($r)['id'] ?: null,
            'updated_at_ip'=> $r->ip(),
        ]);

        return response()->json(['success'=>true, 'message'=>'Moved to bin']);
    }

    public function restore(Request $r, $department, $id)
    {
        $deptId = $this->resolveDepartmentId($department);
        if (!$deptId) return response()->json(['error' => 'Department not found'], 404);

        $ok = DB::table('department_menus')
            ->where('id', (int) $id)
            ->where('department_id', $deptId)
            ->whereNotNull('deleted_at')
            ->exists();

        if (!$ok) return response()->json(['error'=>'Not found in bin'],404);

        DB::table('department_menus')->where('id',(int) $id)->update([
            'deleted_at'   => null,
            'updated_at'   => now(),
            'updated_by'   => $this->actor($r)['id'] ?: null,
            'updated_at_ip'=> $r->ip(),
        ]);

        return response()->json(['success'=>true, 'message'=>'Restored']);
    }

    public function forceDelete(Request $r, $department, $id)
    {
        $deptId = $this->resolveDepartmentId($department);
        if (!$deptId) return response()->json(['error' => 'Department not found'], 404);

        $exists = DB::table('department_menus')
            ->where('id', (int) $id)
            ->where('department_id', $deptId)
            ->exists();
        if (!$exists) return response()->json(['error'=>'Not found'],404);

        DB::table('department_menus')->where('id', (int) $id)->delete();
        return response()->json(['success'=>true, 'message'=>'Deleted permanently']);
    }

    public function toggleDefault(Request $r, $department, $id)
    {
        $deptId = $this->resolveDepartmentId($department);
        if (!$deptId) return response()->json(['error' => 'Department not found'], 404);

        $row = DB::table('department_menus')
            ->where('id', (int) $id)
            ->where('department_id', $deptId)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['error' => 'Not found'], 404);

        $parentId = $row->parent_id ? (int) $row->parent_id : null;
        $this->setDefaultWithinSiblings($deptId, $parentId, (int) $row->id);

        return response()->json(['success'=>true, 'message'=>'Default set']);
    }

    public function toggleActive(Request $r, $department, $id)
    {
        $deptId = $this->resolveDepartmentId($department);
        if (!$deptId) return response()->json(['error' => 'Department not found'], 404);

        $row = DB::table('department_menus')
            ->where('id', (int) $id)
            ->where('department_id', $deptId)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['error' => 'Not found'], 404);

        DB::table('department_menus')->where('id', (int) $id)->update([
            'active'       => !$row->active,
            'updated_at'   => now(),
            'updated_by'   => $this->actor($r)['id'] ?: null,
            'updated_at_ip'=> $r->ip(),
        ]);

        return response()->json(['success'=>true, 'message'=>'Status updated']);
    }

    /** Reorder (and optionally re-parent) items.
     *  Body:
     *  {
     *    "orders": [
     *      {"id": 5, "position": 0, "parent_id": null},
     *      {"id": 6, "position": 1, "parent_id": null},
     *      {"id": 9, "position": 0, "parent_id": 5}
     *    ]
     *  }
     */
    public function reorder(Request $r, $department)
    {
        $deptId = $this->resolveDepartmentId($department);
        if (!$deptId) return response()->json(['error' => 'Department not found'], 404);

        $payload = $r->validate([
            'orders' => 'required|array|min:1',
            'orders.*.id' => 'required|integer',
            'orders.*.position' => 'required|integer|min:0',
            'orders.*.parent_id' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            foreach ($payload['orders'] as $o) {
                $id  = (int) $o['id'];
                $pos = (int) $o['position'];
                $pid = array_key_exists('parent_id',$o) ? ($o['parent_id'] === null ? null : (int) $o['parent_id']) : null;

                $row = DB::table('department_menus')
                    ->where('id', $id)
                    ->where('department_id', $deptId)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$row) continue;

                if ($pid !== null) {
                    $this->validateParent($pid, $deptId, $id);
                }

                DB::table('department_menus')->where('id', $id)->update([
                    'parent_id'  => $pid,
                    'position'   => $pos,
                    'updated_at' => now(),
                ]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error'=>'Reorder failed','details'=>$e->getMessage()], 422);
        }

        return response()->json(['success'=>true,'message'=>'Order updated']);
    }
}
