<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class GalleryController extends Controller
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

    private function safeJson($value): ?string
    {
        if ($value === null) return null;

        // If already string and looks like json, keep as-is? (still store as json string)
        // But safest: always encode arrays/objects.
        if (is_string($value)) {
            $t = trim($value);
            if ($t === '') return null;

            // if it's valid JSON already, keep it
            json_decode($t, true);
            if (json_last_error() === JSON_ERROR_NONE) return $t;

            // else wrap it
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function clip(?string $s, int $max): ?string
    {
        $s = $s === null ? null : (string) $s;
        if ($s === null) return null;
        if (mb_strlen($s) <= $max) return $s;
        return mb_substr($s, 0, $max);
    }

    private function diffKeys(array $before, array $after, array $keys): array
    {
        $changed = [];
        $old = [];
        $new = [];

        foreach ($keys as $k) {
            $b = $before[$k] ?? null;
            $a = $after[$k] ?? null;

            // loose compare is fine for DB scalar strings/ints
            if ($b != $a) {
                $changed[] = $k;
                $old[$k] = $b;
                $new[$k] = $a;
            }
        }

        return [$changed, $old, $new];
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
            // safety if migration not run in some env
            if (!Schema::hasTable('user_data_activity_log')) return;

            $actor = $this->actor($r);

            DB::table('user_data_activity_log')->insert([
                'performed_by'      => (int) ($actor['id'] ?? 0),
                'performed_by_role' => $this->clip((string) ($actor['role'] ?? ''), 50),
                'ip'                => $this->clip($r->ip(), 45),
                'user_agent'        => $this->clip($r->userAgent(), 512),

                'activity'          => $this->clip($activity, 50),
                'module'            => $this->clip($module, 100),

                'table_name'        => $this->clip($tableName, 128),
                'record_id'         => $recordId !== null ? (int) $recordId : null,

                'changed_fields'    => $this->safeJson($changedFields),
                'old_values'        => $this->safeJson($oldValues),
                'new_values'        => $this->safeJson($newValues),

                'log_note'          => $note,

                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            // never break core functionality because of logging
        }
    }

    /**
     * accessControl (ONLY users table)
     *
     * Returns ONLY:
     *  - ['mode' => 'all',         'department_id' => null]
     *  - ['mode' => 'department',  'department_id' => <int>]
     *  - ['mode' => 'none',        'department_id' => null]
     *  - ['mode' => 'not_allowed', 'department_id' => null]
     */
    private function accessControl(int $userId): array
    {
        if ($userId <= 0) {
            return ['mode' => 'none', 'department_id' => null];
        }

        // Safety (if some env doesn't have dept column yet)
        if (!Schema::hasColumn('users', 'department_id')) {
            return ['mode' => 'not_allowed', 'department_id' => null];
        }

        $q = DB::table('users')->select(['id', 'role', 'department_id', 'status']);

        // your schema has deleted_at; keep it safe
        if (Schema::hasColumn('users', 'deleted_at')) {
            $q->whereNull('deleted_at');
        }

        $u = $q->where('id', $userId)->first();

        if (!$u) {
            return ['mode' => 'none', 'department_id' => null];
        }

        // optional: inactive users => none
        if (isset($u->status) && (string)$u->status !== 'active') {
            return ['mode' => 'none', 'department_id' => null];
        }

        // normalize role from users table
        $role = strtolower(trim((string)($u->role ?? '')));
        $role = str_replace([' ', '-'], '_', $role);
        $role = preg_replace('/_+/', '_', $role) ?? $role;

        $deptId = $u->department_id !== null ? (int)$u->department_id : null;
        if ($deptId !== null && $deptId <= 0) $deptId = null;

        // ✅ CONFIG: decide access by role + department_id
        $allRoles  = ['admin', 'director', 'principal']; // gets ALL even if dept null
        $deptRoles = ['hod', 'faculty', 'technical_assistant', 'it_person', 'placement_officer', 'student']; // needs dept

        if (in_array($role, $allRoles, true)) {
            return ['mode' => 'all', 'department_id' => null];
        }

        if (in_array($role, $deptRoles, true)) {
            // none is based on role + dept id (your rule)
            if (!$deptId) return ['mode' => 'none', 'department_id' => null];
            return ['mode' => 'department', 'department_id' => $deptId];
        }

        return ['mode' => 'not_allowed', 'department_id' => null];
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

    protected function toUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') return null;
        if (preg_match('~^https?://~i', $path)) return $path;
        return url('/' . ltrim($path, '/'));
    }

    protected function normalizeTagsInput($value): ?array
    {
        if ($value === null) return null;

        // already array
        if (is_array($value)) {
            $out = [];
            foreach ($value as $t) {
                $t = trim((string) $t);
                if ($t !== '') $out[] = $t;
            }
            return !empty($out) ? array_values($out) : null;
        }

        // json string
        if (is_string($value)) {
            $s = trim($value);
            if ($s === '') return null;

            $decoded = json_decode($s, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeTagsInput($decoded);
            }

            // fallback: comma separated
            if (str_contains($s, ',')) {
                $parts = array_map('trim', explode(',', $s));
                $parts = array_values(array_filter($parts, fn ($x) => $x !== ''));
                return !empty($parts) ? $parts : null;
            }

            return [$s];
        }

        return null;
    }

    protected function normalizeRow($row): array
    {
        $arr = (array) $row;

        // tags_json
        $tags = $arr['tags_json'] ?? null;
        if (is_string($tags)) {
            $decoded = json_decode($tags, true);
            $arr['tags_json'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // metadata
        $meta = $arr['metadata'] ?? null;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // image url
        $arr['image_url'] = $this->toUrl($arr['image'] ?? null);

        // normalized tags array (always)
        $arr['tags'] = [];
        if (is_array($arr['tags_json'] ?? null)) {
            $out = [];
            foreach (($arr['tags_json'] ?? []) as $t) {
                $t = trim((string) $t);
                if ($t !== '') $out[] = $t;
            }
            $arr['tags'] = array_values($out);
        }

        return $arr;
    }

    protected function uploadFileToPublic($file, string $dirRel, string $prefix): array
    {
        // Read meta BEFORE move (prevents tmp stat errors)
        $originalName = $file->getClientOriginalName();
        $mimeType     = $file->getClientMimeType() ?: $file->getMimeType();
        $fileSize     = (int) $file->getSize();
        $ext          = strtolower($file->getClientOriginalExtension() ?: 'bin');

        $dirRel = trim($dirRel, '/');
        $dirAbs = public_path($dirRel);
        if (!is_dir($dirAbs)) @mkdir($dirAbs, 0775, true);

        $filename = $prefix . '-' . Str::random(10) . '.' . $ext;
        $file->move($dirAbs, $filename);

        return [
            'path' => $dirRel . '/' . $filename,
            'name' => $originalName,
            'mime' => $mimeType,
            'size' => $fileSize,
        ];
    }

    protected function deletePublicPath(?string $path): void
    {
        $path = trim((string) $path);
        if ($path === '' || preg_match('~^https?://~i', $path)) return;

        $abs = public_path(ltrim($path, '/'));
        if (is_file($abs)) @unlink($abs);
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('gallery as g')
            ->leftJoin('departments as d', 'd.id', '=', 'g.department_id')
            ->select([
                'g.*',
                'd.title as department_title',
                'd.slug  as department_slug',
                'd.uuid  as department_uuid',
            ]);

        if (! $includeDeleted) $q->whereNull('g.deleted_at');

        // ?q=
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('g.title', 'like', $term)
                    ->orWhere('g.description', 'like', $term)
                    ->orWhere('g.image', 'like', $term);
            });
        }

        // ?status=draft|published|archived
        if ($request->filled('status')) {
            $q->where('g.status', (string) $request->query('status'));
        }

        // ?featured=1/0
        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) $q->where('g.is_featured_home', $featured ? 1 : 0);
        }

        // ?department=id|uuid|slug
        if ($request->filled('department')) {
            $dept = $this->resolveDepartment($request->query('department'), true);
            if ($dept) $q->where('g.department_id', (int) $dept->id);
            else $q->whereRaw('1=0');
        }

        // ?visible_now=1 -> only published and currently in window
        if ($request->has('visible_now')) {
            $visible = filter_var($request->query('visible_now'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($visible) {
                $now = now();
                $q->where('g.status', 'published')
                  ->where(function ($w) use ($now) {
                      $w->whereNull('g.publish_at')->orWhere('g.publish_at', '<=', $now);
                  })
                  ->where(function ($w) use ($now) {
                      $w->whereNull('g.expire_at')->orWhere('g.expire_at', '>', $now);
                  });
            }
        }

        // Sorting
        $sort = (string) $request->query('sort', 'sort_order');
        $dir  = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowed = ['sort_order', 'created_at', 'publish_at', 'expire_at', 'title', 'views_count', 'id'];
        if (!in_array($sort, $allowed, true)) $sort = 'sort_order';

        $q->orderBy('g.' . $sort, $dir);

        // Stable secondary sort for consistent lists
        if ($sort !== 'created_at') $q->orderBy('g.created_at', 'desc');

        return $q;
    }

    protected function resolveGallery(Request $request, $identifier, bool $includeDeleted = false, $departmentId = null)
    {
        $q = DB::table('gallery as g');
        if (! $includeDeleted) $q->whereNull('g.deleted_at');

        if ($departmentId !== null) $q->where('g.department_id', (int) $departmentId);

        if (ctype_digit((string) $identifier)) {
            $q->where('g.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('g.uuid', (string) $identifier);
        } else {
            // no slug in schema; treat as uuid-like only
            $q->where('g.uuid', (string) $identifier);
        }

        $row = $q->first();
        if (! $row) return null;

        // attach department details
        if (!empty($row->department_id)) {
            $dept = DB::table('departments')->where('id', (int) $row->department_id)->first();
            $row->department_title = $dept->title ?? null;
            $row->department_slug  = $dept->slug ?? null;
            $row->department_uuid  = $dept->uuid ?? null;
        } else {
            $row->department_title = null;
            $row->department_slug  = null;
            $row->department_uuid  = null;
        }

        return $row;
    }

    protected function applyVisibleWindow($q): void
    {
        $now = now();

        $q->whereNull('g.deleted_at')
          ->where('g.status', 'published')
          ->where(function ($w) use ($now) {
              $w->whereNull('g.publish_at')->orWhere('g.publish_at', '<=', $now);
          })
          ->where(function ($w) use ($now) {
              $w->whereNull('g.expire_at')->orWhere('g.expire_at', '>', $now);
          });
    }

    /* ============================================
     | CRUD (Authenticated)
     |============================================ */

    public function index(Request $request)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);

        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        // silent empty list for "none"
        if ($ac['mode'] === 'none') {
            $page = max(1, (int) $request->query('page', 1));
            return response()->json([
                'data' => [],
                'pagination' => [
                    'page'      => $page,
                    'per_page'  => $perPage,
                    'total'     => 0,
                    'last_page' => 1,
                ],
            ]);
        }

        // force department scope for dept roles
        if ($ac['mode'] === 'department') {
            $request->query->set('department', (string) ((int) $ac['department_id']));
        }

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        if ($onlyDeleted) $query->whereNotNull('g.deleted_at');

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
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);

        // "none" => empty list
        if ($ac['mode'] === 'none') {
            $perPage = max(1, min(200, (int) $request->query('per_page', 20)));
            $page = max(1, (int) $request->query('page', 1));
            return response()->json([
                'data' => [],
                'pagination' => [
                    'page'      => $page,
                    'per_page'  => $perPage,
                    'total'     => 0,
                    'last_page' => 1,
                ],
            ]);
        }

        $dept = $this->resolveDepartment($department, false);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        // dept roles can ONLY access their own department
        if ($ac['mode'] === 'department' && (int)$dept->id !== (int)$ac['department_id']) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        $request->query->set('department', (string) $dept->id);
        return $this->index($request);
    }

    public function trash(Request $request)
    {
        $request->query->set('only_trashed', '1');
        return $this->index($request);
    }

    public function show(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none') return response()->json(['message' => 'Gallery item not found'], 404);

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveGallery($request, $identifier, $includeDeleted, $deptId);
        if (! $row) return response()->json(['message' => 'Gallery item not found'], 404);

        // optional: ?inc_view=1
        if (filter_var($request->query('inc_view', false), FILTER_VALIDATE_BOOLEAN)) {
            DB::table('gallery')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function showByDepartment(Request $request, $department, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none') return response()->json(['message' => 'Gallery item not found'], 404);

        $dept = $this->resolveDepartment($department, true);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        // dept roles can ONLY access their own department
        if ($ac['mode'] === 'department' && (int)$dept->id !== (int)$ac['department_id']) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveGallery($request, $identifier, $includeDeleted, (int) $dept->id);
        if (! $row) return response()->json(['message' => 'Gallery item not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function store(Request $request)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'create', 'gallery', 'gallery', null, null, null, null, 'Not allowed');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $actor = $this->actor($request);

        try {
            $validated = $request->validate([
                'department_id'     => ['nullable', 'integer', 'exists:departments,id'],

                // either upload OR provide existing path/url
                'image_file'        => ['required_without:image', 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
                'image'             => ['required_without:image_file', 'nullable', 'string', 'max:255'],

                'title'             => ['nullable', 'string', 'max:255'],
                'description'       => ['nullable', 'string', 'max:500'],
                'tags_json'         => ['nullable'], // array or json string or comma string accepted

                'is_featured_home'  => ['nullable', 'in:0,1', 'boolean'],
                'sort_order'        => ['nullable', 'integer', 'min:0'],
                'status'            => ['nullable', 'in:draft,published,archived'],
                'publish_at'        => ['nullable', 'date'],
                'expire_at'         => ['nullable', 'date'],
                'metadata'          => ['nullable'],
            ]);
        } catch (ValidationException $e) {
            $this->logActivity(
                $request,
                'create',
                'gallery',
                'gallery',
                null,
                array_keys($e->errors()),
                null,
                ['input' => $request->except(['image_file'])],
                'Validation failed'
            );
            throw $e;
        }

        // force department for dept roles (ignore incoming department_id)
        if ($ac['mode'] === 'department') {
            $validated['department_id'] = (int) $ac['department_id'];
        }

        $uuid = (string) Str::uuid();
        $now  = now();

        // tags normalize
        $tags = $this->normalizeTagsInput($request->input('tags_json', null));

        // metadata normalize
        $metadata = $request->input('metadata', null);
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
        }

        // image path
        $imagePath = null;

        // upload if provided
        if ($request->hasFile('image_file')) {
            $f = $request->file('image_file');
            if (!$f || !$f->isValid()) {
                $this->logActivity($request, 'create', 'gallery', 'gallery', null, ['image_file'], null, null, 'Image upload failed');
                return response()->json(['success' => false, 'message' => 'Image upload failed'], 422);
            }

            $deptKey = !empty($validated['department_id']) ? (string) ((int) $validated['department_id']) : 'global';
            $dirRel  = 'depy_uploads/gallery/' . $deptKey;

            $meta = $this->uploadFileToPublic($f, $dirRel, 'gallery-' . $uuid);
            $imagePath = $meta['path'];
        } else {
            $imagePath = trim((string) ($validated['image'] ?? ''));
        }

        if ($imagePath === '') {
            $this->logActivity($request, 'create', 'gallery', 'gallery', null, ['image'], null, null, 'Image is required');
            return response()->json(['success' => false, 'message' => 'Image is required'], 422);
        }

        $insert = [
            'uuid'             => $uuid,
            'department_id'    => $validated['department_id'] ?? null,
            'image'            => $imagePath,
            'title'            => $validated['title'] ?? null,
            'description'      => $validated['description'] ?? null,
            'tags_json'        => $tags !== null ? json_encode($tags) : null,
            'is_featured_home' => (int) ($validated['is_featured_home'] ?? 0),
            'sort_order'       => (int) ($validated['sort_order'] ?? 0),
            'status'           => (string) ($validated['status'] ?? 'draft'),
            'publish_at'       => !empty($validated['publish_at']) ? Carbon::parse($validated['publish_at']) : null,
            'expire_at'        => !empty($validated['expire_at']) ? Carbon::parse($validated['expire_at']) : null,
            'views_count'      => 0,
            'created_by'       => $actor['id'] ?: null,
            'created_at'       => $now,
            'updated_at'       => $now,
            'created_at_ip'    => $request->ip(),
            'updated_at_ip'    => $request->ip(),
            'metadata'         => $metadata !== null ? json_encode($metadata) : null,
        ];

        $id = DB::table('gallery')->insertGetId($insert);

        $row = DB::table('gallery')->where('id', $id)->first();

        $this->logActivity(
            $request,
            'create',
            'gallery',
            'gallery',
            $id,
            array_keys($insert),
            null,
            $row ? (array) $row : $insert,
            'Created gallery item'
        );

        return response()->json([
            'success' => true,
            'data'    => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    public function storeForDepartment(Request $request, $department)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'create', 'gallery', 'gallery', null, null, null, null, 'Not allowed');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $dept = $this->resolveDepartment($department, false);
        if (! $dept) {
            $this->logActivity($request, 'create', 'gallery', 'gallery', null, ['department'], null, ['department' => $department], 'Department not found');
            return response()->json(['message' => 'Department not found'], 404);
        }

        // dept roles can ONLY create in their own department
        if ($ac['mode'] === 'department' && (int)$dept->id !== (int)$ac['department_id']) {
            $this->logActivity($request, 'create', 'gallery', 'gallery', null, ['department_id'], null, ['department_id' => (int)$dept->id], 'Not allowed (department mismatch)');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $request->merge(['department_id' => (int) $dept->id]);
        return $this->store($request);
    }

    public function update(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'update', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Not allowed');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveGallery($request, $identifier, true, $deptId);
        if (! $row) {
            $this->logActivity($request, 'update', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Gallery item not found');
            return response()->json(['message' => 'Gallery item not found'], 404);
        }

        $beforeRawObj = DB::table('gallery')->where('id', (int) $row->id)->first();
        $beforeRaw = $beforeRawObj ? (array) $beforeRawObj : [];

        try {
            $validated = $request->validate([
                'department_id'     => ['nullable', 'integer', 'exists:departments,id'],

                'image_file'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
                'image'             => ['nullable', 'string', 'max:255'],
                'image_remove'      => ['nullable', 'in:0,1', 'boolean'], // only removes if you provide new image or image path after

                'title'             => ['nullable', 'string', 'max:255'],
                'description'       => ['nullable', 'string', 'max:500'],
                'tags_json'         => ['nullable'],

                'is_featured_home'  => ['nullable', 'in:0,1', 'boolean'],
                'sort_order'        => ['nullable', 'integer', 'min:0'],
                'status'            => ['nullable', 'in:draft,published,archived'],
                'publish_at'        => ['nullable', 'date'],
                'expire_at'         => ['nullable', 'date'],
                'metadata'          => ['nullable'],
            ]);
        } catch (ValidationException $e) {
            $this->logActivity(
                $request,
                'update',
                'gallery',
                'gallery',
                (int) $row->id,
                array_keys($e->errors()),
                $beforeRaw,
                ['input' => $request->except(['image_file'])],
                'Validation failed'
            );
            throw $e;
        }

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        // department (admins can change; dept roles are forced to their own)
        if ($ac['mode'] === 'department') {
            $update['department_id'] = (int) $ac['department_id'];
        } else {
            if (array_key_exists('department_id', $validated)) {
                $update['department_id'] = $validated['department_id'] !== null ? (int) $validated['department_id'] : null;
            }
        }

        // simple fields
        foreach (['title','description','status'] as $k) {
            if (array_key_exists($k, $validated)) $update[$k] = $validated[$k];
        }
        if (array_key_exists('is_featured_home', $validated)) $update['is_featured_home'] = (int) $validated['is_featured_home'];
        if (array_key_exists('sort_order', $validated)) $update['sort_order'] = (int) $validated['sort_order'];
        if (array_key_exists('publish_at', $validated)) $update['publish_at'] = !empty($validated['publish_at']) ? Carbon::parse($validated['publish_at']) : null;
        if (array_key_exists('expire_at', $validated))  $update['expire_at']  = !empty($validated['expire_at'])  ? Carbon::parse($validated['expire_at'])  : null;

        // tags
        if (array_key_exists('tags_json', $validated)) {
            $tags = $this->normalizeTagsInput($request->input('tags_json', null));
            $update['tags_json'] = $tags !== null ? json_encode($tags) : null;
        }

        // metadata
        if (array_key_exists('metadata', $validated)) {
            $metadata = $request->input('metadata', null);
            if (is_string($metadata)) {
                $decoded = json_decode($metadata, true);
                if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
            }
            $update['metadata'] = $metadata !== null ? json_encode($metadata) : null;
        }

        // image removal request (soft)
        $wantRemove = filter_var($request->input('image_remove', false), FILTER_VALIDATE_BOOLEAN);

        // new image (file)
        if ($request->hasFile('image_file')) {
            $f = $request->file('image_file');
            if (!$f || !$f->isValid()) {
                $this->logActivity($request, 'update', 'gallery', 'gallery', (int)$row->id, ['image_file'], $beforeRaw, null, 'Image upload failed');
                return response()->json(['success' => false, 'message' => 'Image upload failed'], 422);
            }

            $newDeptId = array_key_exists('department_id', $update)
                ? ($update['department_id'] !== null ? (int) $update['department_id'] : null)
                : ($row->department_id !== null ? (int) $row->department_id : null);

            $deptKey = $newDeptId ? (string) $newDeptId : 'global';
            $dirRel  = 'depy_uploads/gallery/' . $deptKey;

            // delete old if it was a local public path
            $this->deletePublicPath($row->image ?? null);

            $meta = $this->uploadFileToPublic($f, $dirRel, 'gallery-' . (string)($row->uuid ?? Str::uuid()));
            $update['image'] = $meta['path'];
        }
        // new image path string
        elseif (array_key_exists('image', $validated) && trim((string)$validated['image']) !== '') {
            if ($wantRemove) {
                $this->deletePublicPath($row->image ?? null);
            }
            $update['image'] = trim((string) $validated['image']);
        }
        // remove without replacement is not allowed because image is NOT NULL
        elseif ($wantRemove) {
            $this->logActivity(
                $request,
                'update',
                'gallery',
                'gallery',
                (int) $row->id,
                ['image_remove'],
                $beforeRaw,
                ['image_remove' => 1],
                'image_remove=1 requires providing a new image_file or image path (image is NOT NULL).'
            );

            return response()->json([
                'success' => false,
                'message' => 'image_remove=1 requires providing a new image_file or image path (image is NOT NULL).'
            ], 422);
        }

        DB::table('gallery')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('gallery')->where('id', (int) $row->id)->first();
        $afterRaw = $fresh ? (array) $fresh : [];

        // log only meaningful changes (exclude updated_at, updated_at_ip noise)
        $keysForDiff = array_keys($update);
        $keysForDiff = array_values(array_filter($keysForDiff, fn($k) => !in_array($k, ['updated_at','updated_at_ip'], true)));

        [$changed, $oldVals, $newVals] = $this->diffKeys($beforeRaw, $afterRaw, $keysForDiff);

        $this->logActivity(
            $request,
            'update',
            'gallery',
            'gallery',
            (int) $row->id,
            $changed,
            $oldVals,
            $newVals,
            'Updated gallery item'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function toggleFeatured(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'toggle_featured', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Not allowed');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveGallery($request, $identifier, true, $deptId);
        if (! $row) {
            $this->logActivity($request, 'toggle_featured', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Gallery item not found');
            return response()->json(['message' => 'Gallery item not found'], 404);
        }

        $beforeRawObj = DB::table('gallery')->where('id', (int) $row->id)->first();
        $beforeRaw = $beforeRawObj ? (array) $beforeRawObj : [];
        $oldFeatured = (int) ($beforeRaw['is_featured_home'] ?? ($row->is_featured_home ?? 0));

        $new = $oldFeatured ? 0 : 1;

        DB::table('gallery')->where('id', (int) $row->id)->update([
            'is_featured_home' => $new,
            'updated_at'       => now(),
            'updated_at_ip'    => $request->ip(),
        ]);

        $fresh = DB::table('gallery')->where('id', (int) $row->id)->first();
        $afterRaw = $fresh ? (array) $fresh : [];

        $this->logActivity(
            $request,
            'toggle_featured',
            'gallery',
            'gallery',
            (int) $row->id,
            ['is_featured_home'],
            ['is_featured_home' => $oldFeatured],
            ['is_featured_home' => (int) ($afterRaw['is_featured_home'] ?? $new)],
            'Toggled featured flag'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'delete', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Not allowed');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveGallery($request, $identifier, false, $deptId);
        if (! $row) {
            $this->logActivity($request, 'delete', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Not found or already deleted');
            return response()->json(['message' => 'Not found or already deleted'], 404);
        }

        $beforeRawObj = DB::table('gallery')->where('id', (int) $row->id)->first();
        $beforeRaw = $beforeRawObj ? (array) $beforeRawObj : [];

        $ts = now();

        DB::table('gallery')->where('id', (int) $row->id)->update([
            'deleted_at'    => $ts,
            'updated_at'    => $ts,
            'updated_at_ip' => $request->ip(),
        ]);

        $this->logActivity(
            $request,
            'delete',
            'gallery',
            'gallery',
            (int) $row->id,
            ['deleted_at'],
            ['deleted_at' => $beforeRaw['deleted_at'] ?? null],
            ['deleted_at' => (string) $ts],
            'Soft-deleted gallery item'
        );

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'restore', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Not allowed');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveGallery($request, $identifier, true, $deptId);
        if (! $row || $row->deleted_at === null) {
            $this->logActivity($request, 'restore', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Not found in bin');
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $beforeRawObj = DB::table('gallery')->where('id', (int) $row->id)->first();
        $beforeRaw = $beforeRawObj ? (array) $beforeRawObj : [];

        DB::table('gallery')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('gallery')->where('id', (int) $row->id)->first();

        $this->logActivity(
            $request,
            'restore',
            'gallery',
            'gallery',
            (int) $row->id,
            ['deleted_at'],
            ['deleted_at' => $beforeRaw['deleted_at'] ?? null],
            ['deleted_at' => null],
            'Restored gallery item'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'force_delete', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Not allowed');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveGallery($request, $identifier, true, $deptId);
        if (! $row) {
            $this->logActivity($request, 'force_delete', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Gallery item not found');
            return response()->json(['message' => 'Gallery item not found'], 404);
        }

        $beforeRawObj = DB::table('gallery')->where('id', (int) $row->id)->first();
        $beforeRaw = $beforeRawObj ? (array) $beforeRawObj : [];

        // delete image file if local
        $this->deletePublicPath($row->image ?? null);

        DB::table('gallery')->where('id', (int) $row->id)->delete();

        $this->logActivity(
            $request,
            'force_delete',
            'gallery',
            'gallery',
            (int) $row->id,
            array_keys($beforeRaw),
            $beforeRaw,
            null,
            'Hard-deleted gallery item'
        );

        return response()->json(['success' => true]);
    }

    /* ============================================
     | Public (no auth)
     |============================================ */

    public function publicIndex(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 12)));

        $q = $this->baseQuery($request, true);
        $this->applyVisibleWindow($q);

        // public-friendly default order: sort_order then latest
        $q->orderBy('g.sort_order', 'asc')->orderByRaw('COALESCE(g.publish_at, g.created_at) desc');

        $paginator = $q->paginate($perPage);
        $items = array_map(fn($r) => $this->normalizeRow($r), $paginator->items());

        return response()->json([
            'success' => true,
            'data'    => $items,
            'pagination' => [
                'page'      => $paginator->currentPage(),
                'per_page'  => $paginator->perPage(),
                'total'     => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function publicIndexByDepartment(Request $request, $department)
    {
        $dept = $this->resolveDepartment($department, false);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        $request->query->set('department', $dept->id);
        return $this->publicIndex($request);
    }

    public function publicShow(Request $request, $identifier)
    {
        $row = $this->resolveGallery($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Gallery item not found'], 404);

        $now = now();
        $isVisible =
            ($row->status === 'published') &&
            (empty($row->publish_at) || Carbon::parse($row->publish_at)->lte($now)) &&
            (empty($row->expire_at)  || Carbon::parse($row->expire_at)->gt($now));

        if (! $isVisible) {
            return response()->json(['message' => 'Gallery item not available'], 404);
        }

        // default public view increment (can disable with ?inc_view=0)
        $inc = $request->has('inc_view')
            ? filter_var($request->query('inc_view'), FILTER_VALIDATE_BOOLEAN)
            : true;

        if ($inc) {
            DB::table('gallery')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }
}
