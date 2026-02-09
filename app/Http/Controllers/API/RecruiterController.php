<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecruiterController extends Controller
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

    /**
     * Central logger (best-effort; never breaks main flow)
     */
    private function logActivity(
        Request $request,
        string $activity,
        string $module,
        string $tableName,
        $recordId = null,
        ?array $changedFields = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $note = null
    ): void {
        try {
            $actor = $this->actor($request);

            DB::table('user_data_activity_log')->insert([
                'performed_by'       => (int) ($actor['id'] ?: 0),
                'performed_by_role'  => ($actor['role'] !== '') ? $actor['role'] : null,
                'ip'                 => $request->ip(),
                'user_agent'         => substr((string) $request->userAgent(), 0, 512),

                'activity'           => $activity,
                'module'             => $module,

                'table_name'         => $tableName,
                'record_id'          => $recordId !== null ? (int) $recordId : null,

                'changed_fields'     => $changedFields !== null ? json_encode($changedFields) : null,
                'old_values'         => $oldValues !== null ? json_encode($oldValues) : null,
                'new_values'         => $newValues !== null ? json_encode($newValues) : null,

                'log_note'           => $note,

                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (\Throwable $e) {
            // swallow (must never affect API functionality)
        }
    }

    private function normSlug(?string $s): string
    {
        $s = trim((string) $s);
        return $s === '' ? '' : Str::slug($s, '-');
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

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('recruiters as r')
            ->leftJoin('departments as d', 'd.id', '=', 'r.department_id')
            ->select([
                'r.*',
                'd.title as department_title',
                'd.slug  as department_slug',
                'd.uuid  as department_uuid',
            ]);

        if (! $includeDeleted) $q->whereNull('r.deleted_at');

        // ?q=
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('r.title', 'like', $term)
                    ->orWhere('r.slug', 'like', $term)
                    ->orWhere('r.description', 'like', $term);
            });
        }

        // ?status=active|inactive
        if ($request->filled('status')) {
            $q->where('r.status', (string) $request->query('status'));
        }

        // ?featured=1/0
        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) {
                $q->where('r.is_featured_home', $featured ? 1 : 0);
            }
        }

        // ?department=id|uuid|slug
        if ($request->filled('department')) {
            $dept = $this->resolveDepartment($request->query('department'), true);
            if ($dept) $q->where('r.department_id', (int) $dept->id);
            else $q->whereRaw('1=0');
        }

        // sort
        $sort = (string) $request->query('sort', 'sort_order');
        $dir  = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowed = ['sort_order', 'created_at', 'updated_at', 'title', 'status', 'id'];
        if (! in_array($sort, $allowed, true)) $sort = 'sort_order';

        $q->orderBy('r.' . $sort, $dir)->orderBy('r.id', 'desc');

        return $q;
    }

    protected function resolveRecruiter(Request $request, $identifier, bool $includeDeleted = false, $departmentId = null)
    {
        $q = DB::table('recruiters as r');
        if (! $includeDeleted) $q->whereNull('r.deleted_at');

        if ($departmentId !== null) {
            $q->where('r.department_id', (int) $departmentId);
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('r.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('r.uuid', (string) $identifier);
        } else {
            $q->where('r.slug', (string) $identifier);
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

        // decode job_roles_json
        $jr = $arr['job_roles_json'] ?? null;
        if (is_string($jr)) {
            $decoded = json_decode($jr, true);
            $arr['job_roles_json'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // decode metadata
        $meta = $arr['metadata'] ?? null;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // logo url normalize
        $arr['logo_url_full'] = $this->toUrl($arr['logo_url'] ?? null);

        return $arr;
    }

    protected function ensureUniqueSlug(string $slug, ?string $ignoreUuid = null): string
    {
        $base = $slug;
        $i = 2;

        while (
            DB::table('recruiters')
                ->where('slug', $slug)
                ->when($ignoreUuid, function ($q) use ($ignoreUuid) {
                    $q->where('uuid', '!=', $ignoreUuid);
                })
                ->whereNull('deleted_at')
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    protected function uploadFileToPublic($file, string $dirRel, string $prefix): array
    {
        $originalName = $file->getClientOriginalName();
        $mimeType     = $file->getClientMimeType() ?: $file->getMimeType();
        $fileSize     = (int) $file->getSize();
        $ext          = strtolower($file->getClientOriginalExtension() ?: 'bin');

        $dirRel = trim($dirRel, '/');
        $dirAbs = public_path($dirRel);
        if (!is_dir($dirAbs)) @mkdir($dirAbs, 0775, true);

        $filename = $prefix . '-' . Str::random(8) . '.' . $ext;
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

    protected function normalizeJsonInput($val)
    {
        if ($val === null) return null;

        if (is_array($val)) return $val;

        if (is_string($val)) {
            $val = trim($val);
            if ($val === '') return null;

            $decoded = json_decode($val, true);
            if (json_last_error() === JSON_ERROR_NONE) return $decoded;
        }

        return null;
    }

    /* ============================================
     | CRUD (Admin)
     |============================================ */

    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        if ($onlyDeleted) $query->whereNotNull('r.deleted_at');

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

        $row = $this->resolveRecruiter($request, $identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Recruiter not found'], 404);

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

        $row = $this->resolveRecruiter($request, $identifier, $includeDeleted, $dept->id);
        if (! $row) return response()->json(['message' => 'Recruiter not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'department_id'    => ['nullable', 'integer', 'exists:departments,id'],
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:160'],
            'description'      => ['nullable', 'string'],
            'status'           => ['nullable', 'in:active,inactive'],
            'is_featured_home' => ['nullable', 'in:0,1', 'boolean'],
            'sort_order'       => ['nullable', 'integer', 'min:0'],
            'job_roles_json'   => ['nullable'],
            'metadata'         => ['nullable'],

            // logo can be a direct path/url OR uploaded file
            'logo_url'         => ['nullable', 'string', 'max:255'],
            'logo'             => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
        ]);

        $slug = $this->normSlug($validated['slug'] ?? '');
        if ($slug === '') $slug = Str::slug($validated['title'], '-');
        $slug = $this->ensureUniqueSlug($slug);

        $uuid = (string) Str::uuid();
        $now  = now();

        // directory based on department (or global)
        $deptKey = !empty($validated['department_id']) ? (string) ((int) $validated['department_id']) : 'global';
        $dirRel  = 'depy_uploads/recruiters/' . $deptKey;

        // logo upload (if provided)
        $logoPath = !empty($validated['logo_url']) ? trim((string) $validated['logo_url']) : null;

        if ($request->hasFile('logo')) {
            $f = $request->file('logo');
            if (!$f || !$f->isValid()) {
                $this->logActivity(
                    $request,
                    'create_failed',
                    'recruiters',
                    'recruiters',
                    null,
                    null,
                    null,
                    null,
                    'Logo upload failed while creating recruiter'
                );

                return response()->json(['success' => false, 'message' => 'Logo upload failed'], 422);
            }
            $meta = $this->uploadFileToPublic($f, $dirRel, $slug . '-logo');
            $logoPath = $meta['path'];
        }

        $jobRoles = $this->normalizeJsonInput($request->input('job_roles_json', null));
        $metadata = $this->normalizeJsonInput($request->input('metadata', null));

        $insertData = [
            'uuid'             => $uuid,
            'department_id'    => $validated['department_id'] ?? null,
            'slug'             => $slug,
            'title'            => $validated['title'],
            'description'      => $validated['description'] ?? null,
            'logo_url'         => $logoPath,
            'job_roles_json'   => $jobRoles !== null ? json_encode($jobRoles) : null,
            'is_featured_home' => (int) ($validated['is_featured_home'] ?? 0),
            'sort_order'       => (int) ($validated['sort_order'] ?? 0),
            'status'           => (string) ($validated['status'] ?? 'active'),
            'created_by'       => $actor['id'] ?: null,
            'created_at'       => $now,
            'updated_at'       => $now,
            'created_at_ip'    => $request->ip(),
            'updated_at_ip'    => $request->ip(),
            'metadata'         => $metadata !== null ? json_encode($metadata) : null,
        ];

        $id = DB::table('recruiters')->insertGetId($insertData);

        $row = DB::table('recruiters')->where('id', $id)->first();

        // LOG: create
        $this->logActivity(
            $request,
            'create',
            'recruiters',
            'recruiters',
            $id,
            array_keys($insertData),
            null,
            $row ? $this->normalizeRow($row) : ['id' => $id],
            'Recruiter created'
        );

        return response()->json([
            'success' => true,
            'data'    => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    public function storeForDepartment(Request $request, $department)
    {
        $dept = $this->resolveDepartment($department, false);
        if (! $dept) {
            $this->logActivity(
                $request,
                'create_failed',
                'recruiters',
                'recruiters',
                null,
                null,
                null,
                null,
                'Department not found while creating recruiter for department identifier: ' . (string) $department
            );

            return response()->json(['message' => 'Department not found'], 404);
        }

        $request->merge(['department_id' => (int) $dept->id]);
        return $this->store($request);
    }

    public function update(Request $request, $identifier)
    {
        $row = $this->resolveRecruiter($request, $identifier, true);
        if (! $row) {
            $this->logActivity(
                $request,
                'update_failed',
                'recruiters',
                'recruiters',
                null,
                null,
                null,
                null,
                'Recruiter not found for update identifier: ' . (string) $identifier
            );

            return response()->json(['message' => 'Recruiter not found'], 404);
        }

        $oldSnapshot = (array) $row;

        $validated = $request->validate([
            'department_id'     => ['nullable', 'integer', 'exists:departments,id'],
            'title'             => ['nullable', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:160'],
            'description'       => ['nullable', 'string'],
            'status'            => ['nullable', 'in:active,inactive'],
            'is_featured_home'  => ['nullable', 'in:0,1', 'boolean'],
            'sort_order'        => ['nullable', 'integer', 'min:0'],
            'job_roles_json'    => ['nullable'],
            'metadata'          => ['nullable'],

            'logo_url'          => ['nullable', 'string', 'max:255'],
            'logo'              => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
            'logo_remove'       => ['nullable', 'in:0,1', 'boolean'],
        ]);

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        // department id for directory
        $newDeptId = array_key_exists('department_id', $validated)
            ? ($validated['department_id'] !== null ? (int) $validated['department_id'] : null)
            : ($row->department_id !== null ? (int) $row->department_id : null);

        if (array_key_exists('department_id', $validated)) {
            $update['department_id'] = $validated['department_id'] !== null ? (int) $validated['department_id'] : null;
        }

        foreach (['title','description','status','sort_order'] as $k) {
            if (array_key_exists($k, $validated)) $update[$k] = $validated[$k];
        }

        if (array_key_exists('is_featured_home', $validated)) {
            $update['is_featured_home'] = (int) $validated['is_featured_home'];
        }

        // slug unique
        if (array_key_exists('slug', $validated) && trim((string)$validated['slug']) !== '') {
            $slug = $this->normSlug($validated['slug']);
            if ($slug === '') $slug = (string) ($row->slug ?? '');
            $slug = $this->ensureUniqueSlug($slug, (string) $row->uuid);
            $update['slug'] = $slug;
        }

        // json fields
        if (array_key_exists('job_roles_json', $validated)) {
            $jr = $this->normalizeJsonInput($request->input('job_roles_json', null));
            $update['job_roles_json'] = $jr !== null ? json_encode($jr) : null;
        }

        if (array_key_exists('metadata', $validated)) {
            $meta = $this->normalizeJsonInput($request->input('metadata', null));
            $update['metadata'] = $meta !== null ? json_encode($meta) : null;
        }

        // logo remove
        if (filter_var($request->input('logo_remove', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->deletePublicPath($row->logo_url ?? null);
            $update['logo_url'] = null;
        }

        // logo set by direct path/url
        if (array_key_exists('logo_url', $validated) && trim((string)$validated['logo_url']) !== '') {
            // if existing is local path, delete it before overriding
            $this->deletePublicPath($row->logo_url ?? null);
            $update['logo_url'] = trim((string) $validated['logo_url']);
        }

        // logo replace by upload
        if ($request->hasFile('logo')) {
            $f = $request->file('logo');
            if (!$f || !$f->isValid()) {
                $this->logActivity(
                    $request,
                    'update_failed',
                    'recruiters',
                    'recruiters',
                    (int) $row->id,
                    null,
                    null,
                    null,
                    'Logo upload failed while updating recruiter'
                );

                return response()->json(['success' => false, 'message' => 'Logo upload failed'], 422);
            }

            $this->deletePublicPath($row->logo_url ?? null);

            $deptKey = $newDeptId ? (string) $newDeptId : 'global';
            $dirRel  = 'depy_uploads/recruiters/' . $deptKey;

            $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'recruiter');
            $meta = $this->uploadFileToPublic($f, $dirRel, $useSlug . '-logo');
            $update['logo_url'] = $meta['path'];
        }

        DB::table('recruiters')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('recruiters')->where('id', (int) $row->id)->first();

        // LOG: update (only meaningful fields; exclude updated_at & updated_at_ip from diff)
        $meaningful = $update;
        unset($meaningful['updated_at'], $meaningful['updated_at_ip']);

        $changedFields = [];
        $oldValues = [];
        $newValues = [];

        foreach ($meaningful as $k => $v) {
            $old = $oldSnapshot[$k] ?? null;
            if ($old != $v) {
                $changedFields[] = $k;
                $oldValues[$k] = $old;
                $newValues[$k] = $v;
            }
        }

        $this->logActivity(
            $request,
            'update',
            'recruiters',
            'recruiters',
            (int) $row->id,
            $changedFields ?: null,
            $oldValues ?: null,
            $newValues ?: ($fresh ? $this->normalizeRow($fresh) : null),
            $changedFields ? 'Recruiter updated' : 'Recruiter update called (no meaningful field changed)'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function toggleFeatured(Request $request, $identifier)
    {
        $row = $this->resolveRecruiter($request, $identifier, true);
        if (! $row) {
            $this->logActivity(
                $request,
                'toggle_featured_failed',
                'recruiters',
                'recruiters',
                null,
                null,
                null,
                null,
                'Recruiter not found for toggleFeatured identifier: ' . (string) $identifier
            );

            return response()->json(['message' => 'Recruiter not found'], 404);
        }

        $old = (int) ($row->is_featured_home ?? 0);
        $new = $old ? 0 : 1;

        DB::table('recruiters')->where('id', (int) $row->id)->update([
            'is_featured_home' => $new,
            'updated_at'       => now(),
            'updated_at_ip'    => $request->ip(),
        ]);

        $fresh = DB::table('recruiters')->where('id', (int) $row->id)->first();

        // LOG: toggle featured
        $this->logActivity(
            $request,
            'toggle_featured',
            'recruiters',
            'recruiters',
            (int) $row->id,
            ['is_featured_home'],
            ['is_featured_home' => $old],
            ['is_featured_home' => $new],
            'Recruiter featured toggled'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveRecruiter($request, $identifier, false);
        if (! $row) {
            $this->logActivity(
                $request,
                'delete_failed',
                'recruiters',
                'recruiters',
                null,
                null,
                null,
                null,
                'Not found or already deleted for destroy identifier: ' . (string) $identifier
            );

            return response()->json(['message' => 'Not found or already deleted'], 404);
        }

        DB::table('recruiters')->where('id', (int) $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        // LOG: soft delete
        $this->logActivity(
            $request,
            'delete',
            'recruiters',
            'recruiters',
            (int) $row->id,
            ['deleted_at'],
            ['deleted_at' => $row->deleted_at],
            ['deleted_at' => 'NOW()'],
            'Recruiter soft-deleted'
        );

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveRecruiter($request, $identifier, true);
        if (! $row || $row->deleted_at === null) {
            $this->logActivity(
                $request,
                'restore_failed',
                'recruiters',
                'recruiters',
                $row ? (int) $row->id : null,
                null,
                null,
                null,
                'Not found in bin for restore identifier: ' . (string) $identifier
            );

            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $oldDeletedAt = $row->deleted_at;

        DB::table('recruiters')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('recruiters')->where('id', (int) $row->id)->first();

        // LOG: restore
        $this->logActivity(
            $request,
            'restore',
            'recruiters',
            'recruiters',
            (int) $row->id,
            ['deleted_at'],
            ['deleted_at' => $oldDeletedAt],
            ['deleted_at' => null],
            'Recruiter restored from bin'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveRecruiter($request, $identifier, true);
        if (! $row) {
            $this->logActivity(
                $request,
                'force_delete_failed',
                'recruiters',
                'recruiters',
                null,
                null,
                null,
                null,
                'Recruiter not found for forceDelete identifier: ' . (string) $identifier
            );

            return response()->json(['message' => 'Recruiter not found'], 404);
        }

        $oldSnapshot = $this->normalizeRow($row);

        // delete local logo file if applicable
        $this->deletePublicPath($row->logo_url ?? null);

        DB::table('recruiters')->where('id', (int) $row->id)->delete();

        // LOG: force delete
        $this->logActivity(
            $request,
            'force_delete',
            'recruiters',
            'recruiters',
            (int) $row->id,
            null,
            $oldSnapshot,
            null,
            'Recruiter permanently deleted'
        );

        return response()->json(['success' => true]);
    }

    /* ============================================
     | Public (no auth)
     |============================================ */

    public function publicIndex(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 12)));

        // public: only non-deleted + status active by default
        if (! $request->filled('status')) {
            $request->query->set('status', 'active');
        }

        $q = $this->baseQuery($request, false);

        // public default sort: featured first, then sort_order
        $q->orderBy('r.is_featured_home', 'desc')
          ->orderBy('r.sort_order', 'asc')
          ->orderBy('r.id', 'desc');

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
        $row = $this->resolveRecruiter($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Recruiter not found'], 404);

        if (($row->status ?? '') !== 'active') {
            return response()->json(['message' => 'Recruiter not available'], 404);
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }
}
