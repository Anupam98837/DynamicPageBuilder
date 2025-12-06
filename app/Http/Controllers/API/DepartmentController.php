<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class DepartmentController extends Controller
{
    /**
     * Normalize actor information from request (compatible with your pattern)
     */
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
     * Base query for departments with common filters
     */
    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('departments');

        if (! $includeDeleted) {
            $q->whereNull('deleted_at');
        }

        // search by title / slug: ?q=
        if ($request->filled('q')) {
            $term = '%' . trim($request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('title', 'like', $term)
                    ->orWhere('slug', 'like', $term);
            });
        }

        // filter by active: ?active=1 / ?active=0
        if ($request->has('active')) {
            $active = filter_var(
                $request->query('active'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
            if ($active !== null) {
                $q->where('active', $active);
            }
        }

        // sort: ?sort=created_at|title|id&direction=asc|desc
        $sort = (string) $request->query('sort', 'created_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['created_at', 'title', 'id'];
        if (! in_array($sort, $allowed, true)) {
            $sort = 'created_at';
        }

        $q->orderBy($sort, $dir);

        return $q;
    }

    /**
     * Resolve a department by id | uuid | slug
     */
    protected function resolveDepartment($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('departments');

        if (! $includeDeleted) {
            $q->whereNull('deleted_at');
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('uuid', (string) $identifier);
        } else {
            // fallback to slug
            $q->where('slug', (string) $identifier);
        }

        return $q->first();
    }

    /**
     * List departments
     * Query params: per_page, page, q, active, with_trashed, only_trashed, sort, direction
     */
    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        if ($onlyDeleted) {
            $query->whereNotNull('deleted_at');
        }

        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => $paginator->items(),
            'pagination' => [
                'page'      => $paginator->currentPage(),
                'per_page'  => $paginator->perPage(),
                'total'     => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * List only trashed departments (bin)
     */
    public function trash(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $query = DB::table('departments')->whereNotNull('deleted_at');

        if ($request->filled('q')) {
            $term = '%' . trim($request->query('q')) . '%';
            $query->where(function ($sub) use ($term) {
                $sub->where('title', 'like', $term)
                    ->orWhere('slug', 'like', $term);
            });
        }

        $query->orderBy('deleted_at', 'desc');

        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => $paginator->items(),
            'pagination' => [
                'page'      => $paginator->currentPage(),
                'per_page'  => $paginator->perPage(),
                'total'     => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Create department
     */
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'title'           => 'required|string|max:150',
            'slug'            => 'nullable|string|max:160|unique:departments,slug,NULL,id,deleted_at,NULL',
            'total_semesters' => 'required|integer|min:1|max:20',
            'active'          => 'sometimes|boolean',
            'metadata'        => 'nullable|array',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $data  = $v->validated();
        $actor = $this->actor($request);
        $ip    = $request->ip();

        // Generate slug if empty
        if (empty($data['slug'])) {
            $base = Str::slug($data['title']);
            if ($base === '') {
                $base = 'department';
            }

            $slug = $base;
            $i    = 1;
            while (DB::table('departments')->where('slug', $slug)->whereNull('deleted_at')->exists()) {
                $slug = $base . '-' . $i++;
            }
            $data['slug'] = $slug;
        }

        $payload = [
            'uuid'            => (string) Str::uuid(),
            'title'           => $data['title'],
            'slug'            => $data['slug'],
            'total_semesters' => (int) $data['total_semesters'],
            'active'          => array_key_exists('active', $data) ? (bool) $data['active'] : true,
            'created_by'      => $actor['id'] ?: null,
            'created_at_ip'   => $ip,
            'created_at'      => now(),
            'updated_at'      => now(),
        ];

        if (array_key_exists('metadata', $data)) {
            $payload['metadata'] = $data['metadata'] !== null
                ? json_encode($data['metadata'])
                : null;
        }

        $id = DB::table('departments')->insertGetId($payload);

        $row = DB::table('departments')->where('id', $id)->first();

        return response()->json([
            'success'    => true,
            'department' => $row,
        ], 201);
    }

    /**
     * Show single department by id|uuid|slug
     */
    public function show(Request $request, $identifier)
    {
        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $dept = $this->resolveDepartment($identifier, $includeDeleted);
        if (! $dept) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        return response()->json(['department' => $dept]);
    }

    /**
     * Update department (partial)
     */
    public function update(Request $request, $identifier)
    {
        $dept = $this->resolveDepartment($identifier, true);
        if (! $dept) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        $v = Validator::make($request->all(), [
            'title'           => 'sometimes|required|string|max:150',
            'slug'            => [
                'sometimes',
                'nullable',
                'string',
                'max:160',
                Rule::unique('departments', 'slug')
                    ->ignore($dept->id)
                    ->whereNull('deleted_at'),
            ],
            'total_semesters' => 'sometimes|required|integer|min:1|max:20',
            'active'          => 'sometimes|boolean',
            'metadata'        => 'sometimes|nullable|array',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $data    = $v->validated();
        $payload = [];

        if (array_key_exists('title', $data)) {
            $payload['title'] = $data['title'];
        }

        if (array_key_exists('slug', $data)) {
            $payload['slug'] = $data['slug'];
        } elseif (array_key_exists('title', $data)) {
            // No slug passed but title changed => regenerate slug
            $base = Str::slug($data['title']);
            if ($base === '') {
                $base = 'department';
            }

            $slug = $base;
            $i    = 1;
            while (
                DB::table('departments')
                    ->where('slug', $slug)
                    ->where('id', '!=', $dept->id)
                    ->whereNull('deleted_at')
                    ->exists()
            ) {
                $slug = $base . '-' . $i++;
            }
            $payload['slug'] = $slug;
        }

        if (array_key_exists('total_semesters', $data)) {
            $payload['total_semesters'] = (int) $data['total_semesters'];
        }

        if (array_key_exists('active', $data)) {
            $payload['active'] = (bool) $data['active'];
        }

        if (array_key_exists('metadata', $data)) {
            $payload['metadata'] = $data['metadata'] !== null
                ? json_encode($data['metadata'])
                : null;
        }

        if (! empty($payload)) {
            $payload['updated_at'] = now();
            DB::table('departments')->where('id', $dept->id)->update($payload);
        }

        $row = DB::table('departments')->where('id', $dept->id)->first();

        return response()->json([
            'success'    => true,
            'department' => $row,
        ]);
    }

    /**
     * Toggle active flag (can be used as archive/unarchive)
     */
    public function toggleActive(Request $request, $identifier)
    {
        $dept = $this->resolveDepartment($identifier, true);
        if (! $dept) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        $newActive = ! (bool) $dept->active;

        DB::table('departments')
            ->where('id', $dept->id)
            ->update([
                'active'     => $newActive,
                'updated_at' => now(),
            ]);

        $row = DB::table('departments')->where('id', $dept->id)->first();

        return response()->json([
            'success'    => true,
            'department' => $row,
        ]);
    }

    /**
     * Soft-delete (move to bin)
     */
    public function destroy(Request $request, $identifier)
    {
        $dept = $this->resolveDepartment($identifier, false);
        if (! $dept) {
            return response()->json(['message' => 'Department not found or already deleted'], 404);
        }

        DB::table('departments')
            ->where('id', $dept->id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Restore from bin
     */
    public function restore(Request $request, $identifier)
    {
        $dept = $this->resolveDepartment($identifier, true);
        if (! $dept || $dept->deleted_at === null) {
            return response()->json(['message' => 'Department not found in bin'], 404);
        }

        DB::table('departments')
            ->where('id', $dept->id)
            ->update([
                'deleted_at' => null,
                'updated_at' => now(),
            ]);

        $row = DB::table('departments')->where('id', $dept->id)->first();

        return response()->json([
            'success'    => true,
            'department' => $row,
        ]);
    }

    /**
     * Permanent delete
     */
    public function forceDelete(Request $request, $identifier)
    {
        $dept = $this->resolveDepartment($identifier, true);
        if (! $dept) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        DB::table('departments')->where('id', $dept->id)->delete();

        return response()->json(['success' => true]);
    }
}
