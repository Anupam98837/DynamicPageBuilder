<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AlumniController extends Controller
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

    private function jsonOrNull($value): ?string
    {
        if ($value === null) return null;
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return ($json === false) ? null : $json;
    }

    private function logActivity(
        Request $r,
        string $activity,
        string $module,
        string $tableName,
        $recordId = null,
        ?array $changedFields = null,
        $oldValues = null,
        $newValues = null,
        ?string $note = null
    ): void {
        try {
            $actor = $this->actor($r);

            DB::table('user_data_activity_log')->insert([
                'performed_by'      => (int) ($actor['id'] ?? 0),
                'performed_by_role' => !empty($actor['role']) ? (string) $actor['role'] : null,
                'ip'                => $r->ip(),
                'user_agent'        => substr((string) ($r->userAgent() ?? ''), 0, 512),

                'activity'          => $activity,
                'module'            => $module,
                'table_name'        => $tableName,
                'record_id'         => $recordId !== null ? (int) $recordId : null,

                'changed_fields'    => $changedFields !== null ? $this->jsonOrNull(array_values($changedFields)) : null,
                'old_values'        => $oldValues !== null ? $this->jsonOrNull($oldValues) : null,
                'new_values'        => $newValues !== null ? $this->jsonOrNull($newValues) : null,

                'log_note'          => $note,

                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            // never block main request
        }
    }

    protected function resolveDepartment($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('departments');
        if (! $includeDeleted) $q->whereNull('deleted_at');

        if (ctype_digit((string) $identifier)) {
            $q->where('id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('uuid', (string) $identifier);
        } else {
            $q->where('slug', (string) $identifier);
        }

        return $q->first();
    }

    protected function resolveAlumni($identifier, bool $includeDeleted = false, $departmentId = null)
    {
        $q = DB::table('alumni as a')
            ->leftJoin('departments as d', 'd.id', '=', 'a.department_id')
            ->leftJoin('users as u', function ($j) {
                $j->on('u.id', '=', 'a.user_id')->whereNull('u.deleted_at');
            })
            ->select([
                'a.*',
                'd.title as department_title',
                'd.slug  as department_slug',
                'd.uuid  as department_uuid',

                // ✅ user details (only if user_id exists)
                'u.uuid as user_uuid',
                'u.slug as user_slug',
                'u.name as user_name',
                'u.role as user_role',
                'u.role_short_form as user_role_short_form',
                'u.image as user_image',
                'u.image as image',
            ]);

        if (! $includeDeleted) $q->whereNull('a.deleted_at');
        if ($departmentId !== null) $q->where('a.department_id', (int) $departmentId);

        if (ctype_digit((string) $identifier)) {
            $q->where('a.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('a.uuid', (string) $identifier);
        } else {
            return null;
        }

        return $q->first();
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

        // decode metadata json if stored as string
        $meta = $arr['metadata'] ?? null;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // normalize user image url
        if (array_key_exists('user_image', $arr)) {
            $arr['user_image'] = $this->toUrl($arr['user_image'] ?? null);
        }
        if (array_key_exists('image', $arr)) {
            $arr['image'] = $this->toUrl($arr['image'] ?? null);
        } elseif (array_key_exists('user_image', $arr)) {
            $arr['image'] = $arr['user_image'];
        }

        return $arr;
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('alumni as a')
            ->leftJoin('departments as d', 'd.id', '=', 'a.department_id')
            ->leftJoin('users as u', function ($j) {
                $j->on('u.id', '=', 'a.user_id')->whereNull('u.deleted_at');
            })
            ->select([
                'a.*',
                'd.title as department_title',
                'd.slug  as department_slug',
                'd.uuid  as department_uuid',

                'u.uuid as user_uuid',
                'u.slug as user_slug',
                'u.name as user_name',
                'u.role as user_role',
                'u.role_short_form as user_role_short_form',
                'u.image as user_image',
                'u.image as image',
            ]);

        if (! $includeDeleted) $q->whereNull('a.deleted_at');

        // q search
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('a.uuid', 'like', $term)
                    ->orWhere('a.roll_no', 'like', $term)
                    ->orWhere('a.program', 'like', $term)
                    ->orWhere('a.specialization', 'like', $term)
                    ->orWhere('a.current_company', 'like', $term)
                    ->orWhere('a.current_role_title', 'like', $term)
                    ->orWhere('a.industry', 'like', $term)
                    ->orWhere('a.city', 'like', $term)
                    ->orWhere('a.country', 'like', $term)
                    ->orWhere('a.note', 'like', $term)
                    ->orWhere('d.title', 'like', $term)
                    ->orWhere('d.slug', 'like', $term)
                    ->orWhere('u.name', 'like', $term);
            });
        }

        // filters
        if ($request->filled('status')) {
            $q->where('a.status', (string) $request->query('status'));
        }

        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) $q->where('a.is_featured_home', $featured ? 1 : 0);
        }

        if ($request->filled('department')) {
            $dept = $this->resolveDepartment($request->query('department'), true);
            if ($dept) $q->where('a.department_id', (int) $dept->id);
            else $q->whereRaw('1=0');
        }

        if ($request->filled('user_id') && ctype_digit((string) $request->query('user_id'))) {
            $q->where('a.user_id', (int) $request->query('user_id'));
        }

        if ($request->filled('program')) {
            $q->where('a.program', (string) $request->query('program'));
        }

        if ($request->filled('industry')) {
            $q->where('a.industry', (string) $request->query('industry'));
        }

        if ($request->filled('city')) {
            $q->where('a.city', (string) $request->query('city'));
        }

        if ($request->filled('country')) {
            $q->where('a.country', (string) $request->query('country'));
        }

        if ($request->filled('admission_year') && ctype_digit((string) $request->query('admission_year'))) {
            $q->where('a.admission_year', (int) $request->query('admission_year'));
        }

        if ($request->filled('passing_year') && ctype_digit((string) $request->query('passing_year'))) {
            $q->where('a.passing_year', (int) $request->query('passing_year'));
        }

        // sorting
        $sort = (string) $request->query('sort', 'created_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['created_at', 'updated_at', 'passing_year', 'admission_year', 'id', 'verified_at'];
        if (! in_array($sort, $allowed, true)) $sort = 'created_at';

        $q->orderBy('a.' . $sort, $dir);

        return $q;
    }

    /* ============================================
     | CRUD
     |============================================ */

    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);
        if ($onlyDeleted) $query->whereNotNull('a.deleted_at');

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

    public function indexByDepartment(Request $request, $department)
    {
        $dept = $this->resolveDepartment($department, false);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        $request->query->set('department', $dept->id);
        return $this->index($request);
    }

    public function trash(Request $request)
    {
        $request->query->set('only_trashed', '1');
        return $this->index($request);
    }

    public function show(Request $request, $identifier)
    {
        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveAlumni($identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Alumni not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function showByDepartment(Request $request, $department, $identifier)
    {
        $dept = $this->resolveDepartment($department, true);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveAlumni($identifier, $includeDeleted, $dept->id);
        if (! $row) return response()->json(['message' => 'Alumni not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'user_id'         => ['nullable', 'integer', 'exists:users,id'],
            'department_id'   => ['nullable', 'integer', 'exists:departments,id'],

            'program'         => ['nullable', 'string', 'max:120'],
            'specialization'  => ['nullable', 'string', 'max:120'],
            'admission_year'  => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'passing_year'    => ['nullable', 'integer', 'min:1900', 'max:2100'],

            'roll_no'         => ['nullable', 'string', 'max:60', 'unique:alumni,roll_no'],

            'current_company'    => ['nullable', 'string', 'max:160'],
            'current_role_title' => ['nullable', 'string', 'max:160'],
            'industry'           => ['nullable', 'string', 'max:120'],

            'city'            => ['nullable', 'string', 'max:120'],
            'country'         => ['nullable', 'string', 'max:120'],

            'note'            => ['nullable', 'string'],
            'is_featured_home'=> ['nullable', 'in:0,1', 'boolean'],
            'status'          => ['nullable', 'in:active,inactive'],

            'verified_at'     => ['nullable', 'date'],
            'metadata'        => ['nullable'],
        ]);

        $uuid = (string) Str::uuid();
        $now  = now();

        $metadata = $request->input('metadata', null);
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
        }

        $verifiedAt = null;
        if (!empty($validated['verified_at'])) {
            $verifiedAt = Carbon::parse($validated['verified_at'])->toDateTimeString();
        } elseif (filter_var($request->input('verify', false), FILTER_VALIDATE_BOOLEAN)) {
            $verifiedAt = $now->toDateTimeString();
        }

        $insert = [
            'uuid'              => $uuid,
            'user_id'           => $validated['user_id'] ?? null,
            'department_id'     => $validated['department_id'] ?? null,

            'program'           => $validated['program'] ?? null,
            'specialization'    => $validated['specialization'] ?? null,
            'admission_year'    => array_key_exists('admission_year', $validated) ? ($validated['admission_year'] !== null ? (int)$validated['admission_year'] : null) : null,
            'passing_year'      => array_key_exists('passing_year', $validated) ? ($validated['passing_year'] !== null ? (int)$validated['passing_year'] : null) : null,
            'roll_no'           => $validated['roll_no'] ?? null,

            'current_company'   => $validated['current_company'] ?? null,
            'current_role_title'=> $validated['current_role_title'] ?? null,
            'industry'          => $validated['industry'] ?? null,

            'city'              => $validated['city'] ?? null,
            'country'           => $validated['country'] ?? null,

            'note'              => $validated['note'] ?? null,
            'is_featured_home'  => (int) ($validated['is_featured_home'] ?? 0),
            'status'            => (string) ($validated['status'] ?? 'active'),
            'verified_at'       => $verifiedAt,

            'created_by'        => $actor['id'] ?: null,
            'created_at'        => $now,
            'updated_at'        => $now,
            'created_at_ip'     => $request->ip(),
            'updated_at_ip'     => $request->ip(),
            'deleted_at'        => null,
            'metadata'          => $metadata !== null ? json_encode($metadata) : null,
        ];

        $id = DB::table('alumni')->insertGetId($insert);

        $this->logActivity(
            $request,
            'create',
            'alumni',
            'alumni',
            $id,
            array_merge(['id'], array_keys($insert)),
            null,
            array_merge(['id' => (int)$id], $insert),
            'Alumni created'
        );

        $row = $this->resolveAlumni((string)$id, true);

        return response()->json([
            'success' => true,
            'data'    => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    public function storeForDepartment(Request $request, $department)
    {
        $dept = $this->resolveDepartment($department, false);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        $request->merge(['department_id' => (int) $dept->id]);
        return $this->store($request);
    }

    public function update(Request $request, $identifier)
    {
        $row = $this->resolveAlumni($identifier, true);
        if (! $row) return response()->json(['message' => 'Alumni not found'], 404);

        $beforeObj = DB::table('alumni')->where('id', (int) $row->id)->first();
        $before = $beforeObj ? (array) $beforeObj : (array) $row;

        $validated = $request->validate([
            'user_id'         => ['nullable', 'integer', 'exists:users,id'],
            'department_id'   => ['nullable', 'integer', 'exists:departments,id'],

            'program'         => ['nullable', 'string', 'max:120'],
            'specialization'  => ['nullable', 'string', 'max:120'],
            'admission_year'  => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'passing_year'    => ['nullable', 'integer', 'min:1900', 'max:2100'],

            'roll_no'         => [
                'nullable',
                'string',
                'max:60',
                Rule::unique('alumni', 'roll_no')->ignore((int) $row->id),
            ],

            'current_company'    => ['nullable', 'string', 'max:160'],
            'current_role_title' => ['nullable', 'string', 'max:160'],
            'industry'           => ['nullable', 'string', 'max:120'],

            'city'            => ['nullable', 'string', 'max:120'],
            'country'         => ['nullable', 'string', 'max:120'],

            'note'            => ['nullable', 'string'],
            'is_featured_home'=> ['nullable', 'in:0,1', 'boolean'],
            'status'          => ['nullable', 'in:active,inactive'],

            'verified_at'     => ['nullable', 'date'],
            'verify'          => ['nullable', 'in:0,1', 'boolean'],
            'unverify'        => ['nullable', 'in:0,1', 'boolean'],

            'metadata'        => ['nullable'],
        ]);

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        foreach ([
            'user_id','department_id','program','specialization','roll_no',
            'current_company','current_role_title','industry','city','country',
            'note','status'
        ] as $k) {
            if (array_key_exists($k, $validated)) {
                $update[$k] = $validated[$k] !== null ? $validated[$k] : null;
            }
        }

        foreach (['admission_year','passing_year'] as $k) {
            if (array_key_exists($k, $validated)) {
                $update[$k] = $validated[$k] !== null ? (int) $validated[$k] : null;
            }
        }

        if (array_key_exists('is_featured_home', $validated)) {
            $update['is_featured_home'] = (int) $validated['is_featured_home'];
        }

        // verified_at control
        if (filter_var($request->input('unverify', false), FILTER_VALIDATE_BOOLEAN)) {
            $update['verified_at'] = null;
        } elseif (filter_var($request->input('verify', false), FILTER_VALIDATE_BOOLEAN)) {
            $update['verified_at'] = now()->toDateTimeString();
        } elseif (array_key_exists('verified_at', $validated)) {
            $update['verified_at'] = !empty($validated['verified_at'])
                ? Carbon::parse($validated['verified_at'])->toDateTimeString()
                : null;
        }

        if (array_key_exists('metadata', $validated)) {
            $metadata = $request->input('metadata', null);
            if (is_string($metadata)) {
                $decoded = json_decode($metadata, true);
                if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
            }
            $update['metadata'] = $metadata !== null ? json_encode($metadata) : null;
        }

        DB::table('alumni')->where('id', (int) $row->id)->update($update);

        $freshObj = DB::table('alumni')->where('id', (int) $row->id)->first();
        $freshArr = $freshObj ? (array) $freshObj : null;

        $changed = [];
        $oldVals = [];
        $newVals = [];

        if ($freshArr) {
            foreach (array_keys($freshArr) as $k) {
                if (in_array($k, ['updated_at', 'updated_at_ip'], true)) continue;

                $old = $before[$k] ?? null;
                $new = $freshArr[$k] ?? null;

                if (is_numeric($old) && is_numeric($new)) {
                    if ((string)$old === (string)$new) continue;
                } else {
                    if ($old === $new) continue;
                    if ((string)$old === (string)$new) continue;
                }

                $changed[]   = $k;
                $oldVals[$k] = $old;
                $newVals[$k] = $new;
            }
        } else {
            foreach ($update as $k => $v) {
                if (in_array($k, ['updated_at', 'updated_at_ip'], true)) continue;
                $changed[]   = $k;
                $oldVals[$k] = $before[$k] ?? null;
                $newVals[$k] = $v;
            }
        }

        $this->logActivity(
            $request,
            'update',
            'alumni',
            'alumni',
            (int) $row->id,
            $changed ?: null,
            $oldVals ?: null,
            $newVals ?: null,
            'Alumni updated'
        );

        $joined = $this->resolveAlumni((string)$row->id, true);

        return response()->json([
            'success' => true,
            'data'    => $joined ? $this->normalizeRow($joined) : null,
        ]);
    }

    public function toggleFeatured(Request $request, $identifier)
    {
        $row = $this->resolveAlumni($identifier, true);
        if (! $row) return response()->json(['message' => 'Alumni not found'], 404);

        $oldVal = (int) ($row->is_featured_home ?? 0);
        $newVal = $oldVal ? 0 : 1;

        DB::table('alumni')->where('id', (int) $row->id)->update([
            'is_featured_home' => $newVal,
            'updated_at'       => now(),
            'updated_at_ip'    => $request->ip(),
        ]);

        $this->logActivity(
            $request,
            'update',
            'alumni',
            'alumni',
            (int) $row->id,
            ['is_featured_home'],
            ['is_featured_home' => $oldVal],
            ['is_featured_home' => $newVal],
            'Alumni featured flag toggled'
        );

        $fresh = $this->resolveAlumni((string)$row->id, true);

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveAlumni($identifier, false);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        $ts = now();

        DB::table('alumni')->where('id', (int) $row->id)->update([
            'deleted_at'    => $ts,
            'updated_at'    => $ts,
            'updated_at_ip' => $request->ip(),
        ]);

        $this->logActivity(
            $request,
            'delete',
            'alumni',
            'alumni',
            (int) $row->id,
            ['deleted_at'],
            ['deleted_at' => $row->deleted_at ?? null],
            ['deleted_at' => $ts->toDateTimeString()],
            'Alumni soft deleted'
        );

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveAlumni($identifier, true);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $ts = now();

        DB::table('alumni')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => $ts,
            'updated_at_ip' => $request->ip(),
        ]);

        $this->logActivity(
            $request,
            'restore',
            'alumni',
            'alumni',
            (int) $row->id,
            ['deleted_at'],
            ['deleted_at' => $row->deleted_at],
            ['deleted_at' => null],
            'Alumni restored from bin'
        );

        $fresh = $this->resolveAlumni((string)$row->id, true);

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveAlumni($identifier, true);
        if (! $row) return response()->json(['message' => 'Alumni not found'], 404);

        $beforeObj = DB::table('alumni')->where('id', (int) $row->id)->first();
        $before = $beforeObj ? (array) $beforeObj : (array) $row;

        DB::table('alumni')->where('id', (int) $row->id)->delete();

        $this->logActivity(
            $request,
            'force_delete',
            'alumni',
            'alumni',
            (int) $row->id,
            null,
            $before ?: null,
            null,
            'Alumni permanently deleted'
        );

        return response()->json(['success' => true]);
    }

    /* ============================================
     | Public Index
     |============================================ */

    public function publicIndex(Request $request)
    {
        $page    = max(1, (int)$request->query('page', 1));
        $perPage = (int)$request->query('per_page', 12);
        $perPage = max(6, min(60, $perPage));

        $qText  = trim((string)$request->query('q', ''));
        $status = trim((string)$request->query('status', 'active')) ?: 'active';

        $deptParam = $request->query('department', $request->query('dept', null));

        $sort = (string)$request->query('sort', 'created_at');
        $dir  = strtolower((string)$request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['created_at','updated_at','passing_year','admission_year','id','verified_at'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'created_at';

        $base = DB::table('alumni as a')
            ->leftJoin('users as u', function ($j) {
                $j->on('u.id', '=', 'a.user_id')->whereNull('u.deleted_at');
            })
            ->leftJoin('departments as d', 'd.id', '=', 'a.department_id')
            ->whereNull('a.deleted_at')
            ->where(function ($w) {
                // allow null dept OR dept not deleted
                $w->whereNull('a.department_id')
                  ->orWhereNull('d.deleted_at');
            });

        if ($status !== '') $base->where('a.status', $status);

        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) $base->where('a.is_featured_home', $featured ? 1 : 0);
        }

        if (!empty($deptParam)) {
            $dept = $this->resolveDepartment($deptParam);
            if ($dept) $base->where('a.department_id', (int)$dept->id);
            else $base->whereRaw('1=0');
        }

        if ($request->filled('passing_year') && ctype_digit((string)$request->query('passing_year'))) {
            $base->where('a.passing_year', (int)$request->query('passing_year'));
        }

        if ($request->filled('program')) {
            $base->where('a.program', (string)$request->query('program'));
        }

        if ($qText !== '') {
            $term = '%' . $qText . '%';
            $base->where(function ($w) use ($term) {
                $w->where('a.program', 'like', $term)
                  ->orWhere('a.specialization', 'like', $term)
                  ->orWhere('a.current_company', 'like', $term)
                  ->orWhere('a.current_role_title', 'like', $term)
                  ->orWhere('a.industry', 'like', $term)
                  ->orWhere('a.city', 'like', $term)
                  ->orWhere('a.country', 'like', $term)
                  ->orWhere('a.note', 'like', $term)
                  ->orWhere('a.roll_no', 'like', $term)
                  ->orWhere('d.title', 'like', $term)
                  ->orWhere('d.slug', 'like', $term)
                  ->orWhere('u.name', 'like', $term);
            });
        }

        $total    = (clone $base)->distinct('a.id')->count('a.id');
        $lastPage = max(1, (int)ceil($total / $perPage));

        $rows = (clone $base)
            ->select([
                'a.id',
                'a.uuid',
                'a.user_id',
                'a.department_id',
                'a.program',
                'a.specialization',
                'a.admission_year',
                'a.passing_year',
                'a.roll_no',
                'a.current_company',
                'a.current_role_title',
                'a.industry',
                'a.city',
                'a.country',
                'a.note',
                'a.is_featured_home',
                'a.status',
                'a.verified_at',
                'a.created_at',
                'a.updated_at',

                'd.title as department_title',
                'd.slug  as department_slug',
                'd.uuid  as department_uuid',

                'u.uuid as user_uuid',
                'u.name as user_name',
                'u.image as user_image',
            ])
            ->orderBy('a.' . $sort, $dir)
            ->orderBy('a.id', 'desc')
            ->forPage($page, $perPage)
            ->get();

        $items = $rows->map(fn($r) => $this->normalizeRow($r))->values()->all();

        return response()->json([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'page'      => $page,
                'per_page'  => $perPage,
                'total'     => $total,
                'last_page' => $lastPage,
            ],
        ]);
    }
}
