<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ScholarshipController extends Controller
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
        $q = DB::table('scholarships as s')
            ->leftJoin('departments as d', 'd.id', '=', 's.department_id')
            ->select([
                's.*',
                'd.title as department_title',
                'd.slug  as department_slug',
                'd.uuid  as department_uuid',
            ]);

        if (! $includeDeleted) {
            $q->whereNull('s.deleted_at');
        }

        // ?q=
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('s.title', 'like', $term)
                    ->orWhere('s.slug', 'like', $term)
                    ->orWhere('s.body', 'like', $term);
            });
        }

        // ?status=draft|published|archived
        if ($request->filled('status')) {
            $q->where('s.status', (string) $request->query('status'));
        }

        // ?featured=1/0
        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) {
                $q->where('s.is_featured_home', $featured ? 1 : 0);
            }
        }

        // ?department=id|uuid|slug
        if ($request->filled('department')) {
            $dept = $this->resolveDepartment($request->query('department'), true);
            if ($dept) {
                $q->where('s.department_id', (int) $dept->id);
            } else {
                $q->whereRaw('1=0');
            }
        }

        // ?visible_now=1 -> only published and currently visible window
        if ($request->has('visible_now')) {
            $visible = filter_var($request->query('visible_now'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($visible) {
                $now = now();
                $q->where('s.status', 'published')
                    ->where(function ($w) use ($now) {
                        $w->whereNull('s.publish_at')->orWhere('s.publish_at', '<=', $now);
                    })
                    ->where(function ($w) use ($now) {
                        $w->whereNull('s.expire_at')->orWhere('s.expire_at', '>', $now);
                    });
            }
        }

        // sort
        $sort = (string) $request->query('sort', 'created_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['created_at', 'publish_at', 'expire_at', 'title', 'views_count', 'id'];
        if (! in_array($sort, $allowed, true)) $sort = 'created_at';

        $q->orderBy('s.' . $sort, $dir);

        return $q;
    }

    protected function resolveScholarship(Request $request, $identifier, bool $includeDeleted = false, $departmentId = null)
    {
        $q = DB::table('scholarships as s');
        if (! $includeDeleted) $q->whereNull('s.deleted_at');

        if ($departmentId !== null) {
            $q->where('s.department_id', (int) $departmentId);
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('s.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('s.uuid', (string) $identifier);
        } else {
            $q->where('s.slug', (string) $identifier);
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

        // decode attachments_json
        $attachmentsJson = $arr['attachments_json'] ?? null;
        if (is_string($attachmentsJson)) {
            $decoded = json_decode($attachmentsJson, true);
            $arr['attachments_json'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // decode metadata
        $meta = $arr['metadata'] ?? null;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // urls
        $arr['cover_image_url'] = $this->toUrl($arr['cover_image'] ?? null);

        // normalized attachments[]
        $arr['attachments'] = [];
        $attachments = $arr['attachments_json'] ?? null;

        if (is_array($attachments)) {
            $out = [];

            foreach ($attachments as $a) {
                // supports ["path1","path2"] OR [{path,name,size,mime}, ...]
                if (is_string($a)) {
                    $p = trim($a);
                    if ($p !== '') {
                        $out[] = [
                            'path' => $p,
                            'url'  => $this->toUrl($p),
                        ];
                    }
                    continue;
                }

                if (is_array($a)) {
                    $p = trim((string) ($a['path'] ?? ''));
                    if ($p !== '') {
                        $out[] = [
                            'path' => $p,
                            'url'  => $this->toUrl($p),
                            'name' => $a['name'] ?? null,
                            'size' => $a['size'] ?? null,
                            'mime' => $a['mime'] ?? null,
                        ];
                    }
                    continue;
                }
            }

            $arr['attachments'] = array_values($out);
        }

        return $arr;
    }

    protected function ensureUniqueSlug(string $slug, ?string $ignoreUuid = null): string
    {
        $base = $slug;
        $i = 2;

        while (
            DB::table('scholarships')
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
        // Read meta BEFORE move
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

    protected function applyVisibleWindow($q): void
    {
        $now = now();

        $q->whereNull('s.deleted_at')
          ->where('s.status', 'published')
          ->where(function ($w) use ($now) {
              $w->whereNull('s.publish_at')->orWhere('s.publish_at', '<=', $now);
          })
          ->where(function ($w) use ($now) {
              $w->whereNull('s.expire_at')->orWhere('s.expire_at', '>', $now);
          });
    }

    /* ============================================
     | Activity Log Helpers (POST/PUT/PATCH/DELETE)
     |============================================ */

    private function logSnapshotScholarship($row): array
    {
        $a = (array) $row;

        // keep logs small & safe: DO NOT store full body
        $snapshot = [
            'id'                   => $a['id'] ?? null,
            'uuid'                 => $a['uuid'] ?? null,
            'department_id'        => $a['department_id'] ?? null,
            'title'                => $a['title'] ?? null,
            'slug'                 => $a['slug'] ?? null,
            'status'               => $a['status'] ?? null,
            'is_featured_home'     => $a['is_featured_home'] ?? null,
            'request_for_approval' => $a['request_for_approval'] ?? null,
            'publish_at'           => $a['publish_at'] ?? null,
            'expire_at'            => $a['expire_at'] ?? null,
            'views_count'          => $a['views_count'] ?? null,
            'cover_image'          => $a['cover_image'] ?? null,
            'attachments_json'     => $a['attachments_json'] ?? null,
            'metadata'             => $a['metadata'] ?? null,
            'created_by'           => $a['created_by'] ?? null,
            'created_at'           => $a['created_at'] ?? null,
            'updated_at'           => $a['updated_at'] ?? null,
            'deleted_at'           => $a['deleted_at'] ?? null,
        ];

        return $snapshot;
    }

    private function computeDiff(array $old, array $new): array
    {
        $changed = [];
        $oldOut  = [];
        $newOut  = [];

        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));

        foreach ($keys as $k) {
            $ov = $old[$k] ?? null;
            $nv = $new[$k] ?? null;

            // normalize arrays/objects for comparison
            $ovCmp = (is_array($ov) || is_object($ov)) ? json_encode($ov) : $ov;
            $nvCmp = (is_array($nv) || is_object($nv)) ? json_encode($nv) : $nv;

            if ($ovCmp != $nvCmp) {
                $changed[] = $k;
                $oldOut[$k] = $ov;
                $newOut[$k] = $nv;
            }
        }

        return [$changed, $oldOut, $newOut];
    }

    private function safeLogActivity(
        Request $request,
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
            if (!Schema::hasTable('user_data_activity_log')) return;

            $actor = $this->actor($request);

            $ua = (string) ($request->header('User-Agent') ?? '');
            if (strlen($ua) > 512) $ua = substr($ua, 0, 512);

            DB::table('user_data_activity_log')->insert([
                'performed_by'      => (int) ($actor['id'] ?? 0),
                'performed_by_role' => (string) ($actor['role'] ?? ''),
                'ip'                => (string) ($request->ip() ?? ''),
                'user_agent'        => $ua,

                'activity'          => substr((string) $activity, 0, 50),
                'module'            => substr((string) $module, 0, 100),

                'table_name'        => substr((string) $tableName, 0, 128),
                'record_id'         => $recordId !== null ? (int) $recordId : null,

                'changed_fields'    => $changedFields !== null ? json_encode(array_values($changedFields)) : null,
                'old_values'        => $oldValues !== null ? json_encode($oldValues) : null,
                'new_values'        => $newValues !== null ? json_encode($newValues) : null,

                'log_note'          => $note,

                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            // Never break API functionality due to logging errors.
        }
    }

    /* ============================================
     | CRUD (Authenticated)
     |============================================ */

    public function index(Request $request)
    {
        $actorId = (int) ($request->attributes->get('auth_tokenable_id') ?? 0);
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none') {
            return response()->json([
                'data' => [],
                'pagination' => [
                    'page'      => 1,
                    'per_page'  => (int) $request->query('per_page', 20),
                    'total'     => 0,
                    'last_page' => 1,
                ],
            ], 200);
        }

        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        // ✅ apply dept filter (department-mode)
        if ($ac['mode'] === 'department') {
            $query->where('s.department_id', (int) $ac['department_id']);
        }

        if ($onlyDeleted) {
            $query->whereNotNull('s.deleted_at');
        }

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
        $actorId = (int) ($request->attributes->get('auth_tokenable_id') ?? 0);
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none') {
            return response()->json([
                'data' => [],
                'pagination' => [
                    'page'      => 1,
                    'per_page'  => (int) $request->query('per_page', 20),
                    'total'     => 0,
                    'last_page' => 1,
                ],
            ], 200);
        }

        $dept = $this->resolveDepartment($department, false);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        // ✅ if dept-mode, block other department route
        if ($ac['mode'] === 'department' && (int)$dept->id !== (int)$ac['department_id']) {
            return response()->json([
                'data' => [],
                'pagination' => [
                    'page'      => 1,
                    'per_page'  => (int) $request->query('per_page', 20),
                    'total'     => 0,
                    'last_page' => 1,
                ],
            ], 200);
        }

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
        $actorId = (int) ($request->attributes->get('auth_tokenable_id') ?? 0);
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return response()->json(['message' => 'Scholarship not found'], 404);

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveScholarship($request, $identifier, $includeDeleted, $deptId);
        if (! $row) return response()->json(['message' => 'Scholarship not found'], 404);

        // optional: ?inc_view=1
        if (filter_var($request->query('inc_view', false), FILTER_VALIDATE_BOOLEAN)) {
            DB::table('scholarships')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function showByDepartment(Request $request, $department, $identifier)
    {
        $actorId = (int) ($request->attributes->get('auth_tokenable_id') ?? 0);
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return response()->json(['message' => 'Scholarship not found'], 404);

        $dept = $this->resolveDepartment($department, true);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        // ✅ if dept-mode, block other department route
        if ($ac['mode'] === 'department' && (int)$dept->id !== (int)$ac['department_id']) {
            return response()->json(['message' => 'Scholarship not found'], 404);
        }

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveScholarship($request, $identifier, $includeDeleted, $dept->id);
        if (! $row) return response()->json(['message' => 'Scholarship not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $ac = $this->accessControl((int) $actor['id']);
        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return response()->json(['error' => 'Not allowed'], 403);

        $validated = $request->validate([
            'department_id'     => ['nullable', 'integer', 'exists:departments,id'],
            'title'             => ['required', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:160'],
            'body'              => ['required', 'string'],
            'is_featured_home'  => ['nullable', 'in:0,1', 'boolean'],
            'status'            => ['nullable', 'in:draft,published,archived'],
            'publish_at'        => ['nullable', 'date'],
            'expire_at'         => ['nullable', 'date'],
            'metadata'          => ['nullable'],

            'cover_image'       => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'attachments'       => ['nullable', 'array'],
            'attachments.*'     => ['file', 'max:20480'],
            'attachments_json'  => ['nullable'],
        ]);

        // ✅ force department for department-mode users
        if ($ac['mode'] === 'department') {
            $validated['department_id'] = (int) $ac['department_id'];
        }

        $slug = $this->normSlug($validated['slug'] ?? '');
        if ($slug === '') $slug = Str::slug($validated['title'], '-');
        $slug = $this->ensureUniqueSlug($slug);

        $uuid = (string) Str::uuid();
        $now  = now();

        $deptKey = !empty($validated['department_id']) ? (string) ((int) $validated['department_id']) : 'global';
        $dirRel  = 'depy_uploads/scholarships/' . $deptKey;

        // cover upload
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $f = $request->file('cover_image');
            if (!$f || !$f->isValid()) {
                return response()->json(['success' => false, 'message' => 'Cover image upload failed'], 422);
            }
            $meta = $this->uploadFileToPublic($f, $dirRel, $slug . '-cover');
            $coverPath = $meta['path'];
        }

        // attachments upload
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ((array) $request->file('attachments') as $file) {
                if (!$file) continue;
                if (!$file->isValid()) {
                    return response()->json(['success' => false, 'message' => 'One of the attachments failed to upload'], 422);
                }
                $attachments[] = $this->uploadFileToPublic($file, $dirRel, $slug . '-att');
            }
        }

        // manual attachments_json (optional)
        if (empty($attachments) && $request->filled('attachments_json')) {
            $raw = $request->input('attachments_json');
            if (is_array($raw)) {
                $attachments = $raw;
            } elseif (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $attachments = $decoded;
                }
            }
        }

        // metadata normalize
        $metadata = $request->input('metadata', null);
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
        }

        // ✅ AUTHORITY CONTROL SYNC (FEATURED -> REQUEST FOR APPROVAL)
        $isFeatured = (int) ($validated['is_featured_home'] ?? 0);
        $requestForApproval = $isFeatured === 1 ? 1 : 0;

        $id = DB::table('scholarships')->insertGetId([
            'uuid'                 => $uuid,
            'department_id'        => $validated['department_id'] ?? null,
            'title'                => $validated['title'],
            'slug'                 => $slug,
            'body'                 => $validated['body'],
            'cover_image'          => $coverPath,
            'attachments_json'     => !empty($attachments) ? json_encode($attachments) : null,
            'is_featured_home'     => $isFeatured,

            // ✅ NEW: auto-sync
            'request_for_approval' => $requestForApproval,

            'status'               => (string) ($validated['status'] ?? 'draft'),
            'publish_at'           => !empty($validated['publish_at']) ? Carbon::parse($validated['publish_at']) : null,
            'expire_at'            => !empty($validated['expire_at']) ? Carbon::parse($validated['expire_at']) : null,
            'views_count'          => 0,
            'created_by'           => $actor['id'] ?: null,
            'created_at'           => $now,
            'updated_at'           => $now,
            'created_at_ip'        => $request->ip(),
            'updated_at_ip'        => $request->ip(),
            'metadata'             => $metadata !== null ? json_encode($metadata) : null,
        ]);

        $row = DB::table('scholarships')->where('id', $id)->first();

        // ✅ LOG (POST)
        if ($row) {
            $newSnap = $this->logSnapshotScholarship($row);
            $notePrefix = (string) ($request->attributes->get('_log_note_prefix') ?? '');
            $note = $notePrefix !== '' ? ("Created scholarship via " . $notePrefix) : "Created scholarship";
            $this->safeLogActivity(
                $request,
                'create',
                'scholarships',
                'scholarships',
                (int) $id,
                array_keys($newSnap),
                null,
                $newSnap,
                $note
            );
        }

        return response()->json([
            'success' => true,
            'data'    => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    public function storeForDepartment(Request $request, $department)
    {
        $actorId = (int) ($request->attributes->get('auth_tokenable_id') ?? 0);
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return response()->json(['error' => 'Not allowed'], 403);

        $dept = $this->resolveDepartment($department, false);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        // ✅ if dept-mode, block other department route
        if ($ac['mode'] === 'department' && (int)$dept->id !== (int)$ac['department_id']) {
            return response()->json(['error' => 'Not allowed'], 403);
        }

        // mark source endpoint for single log entry (inside store)
        $request->attributes->set('_log_note_prefix', 'storeForDepartment');

        $request->merge(['department_id' => (int) $dept->id]);
        return $this->store($request);
    }

    public function update(Request $request, $identifier)
    {
        $actorId = (int) ($request->attributes->get('auth_tokenable_id') ?? 0);
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return response()->json(['error' => 'Not allowed'], 403);

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveScholarship($request, $identifier, true, $deptId);
        if (! $row) return response()->json(['message' => 'Scholarship not found'], 404);

        $oldSnap = $this->logSnapshotScholarship($row);

        $validated = $request->validate([
            'department_id'      => ['nullable', 'integer', 'exists:departments,id'],
            'title'              => ['nullable', 'string', 'max:255'],
            'slug'               => ['nullable', 'string', 'max:160'],
            'body'               => ['nullable', 'string'],
            'is_featured_home'   => ['nullable', 'in:0,1', 'boolean'],
            'status'             => ['nullable', 'in:draft,published,archived'],
            'publish_at'         => ['nullable', 'date'],
            'expire_at'          => ['nullable', 'date'],
            'metadata'           => ['nullable'],

            'cover_image'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'cover_image_remove' => ['nullable', 'in:0,1', 'boolean'],

            'attachments'        => ['nullable', 'array'],
            'attachments.*'      => ['file', 'max:20480'],
            'attachments_mode'   => ['nullable', 'in:append,replace'],
            'attachments_remove' => ['nullable', 'array'],
        ]);

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        // normal fields
        foreach (['title','body','status'] as $k) {
            if (array_key_exists($k, $validated)) $update[$k] = $validated[$k];
        }

        // ✅ department rules
        if ($ac['mode'] === 'department') {
            // force department_id to actor dept
            $update['department_id'] = (int) $ac['department_id'];
        } else {
            // all-mode can change department_id (nullable)
            if (array_key_exists('department_id', $validated)) {
                $update['department_id'] = $validated['department_id'] !== null ? (int) $validated['department_id'] : null;
            }
        }

        // dept id for directory (based on final department)
        $newDeptId = array_key_exists('department_id', $update)
            ? ($update['department_id'] !== null ? (int) $update['department_id'] : null)
            : ($row->department_id !== null ? (int) $row->department_id : null);

        // ✅ AUTHORITY CONTROL SYNC (when is_featured_home is updated)
        if (array_key_exists('is_featured_home', $validated)) {
            $isFeatured = (int) $validated['is_featured_home'];
            $update['is_featured_home'] = $isFeatured;

            // ✅ auto-sync request_for_approval
            $update['request_for_approval'] = $isFeatured === 1 ? 1 : 0;
        }

        if (array_key_exists('publish_at', $validated)) {
            $update['publish_at'] = !empty($validated['publish_at']) ? Carbon::parse($validated['publish_at']) : null;
        }
        if (array_key_exists('expire_at', $validated)) {
            $update['expire_at'] = !empty($validated['expire_at']) ? Carbon::parse($validated['expire_at']) : null;
        }

        // slug unique
        if (array_key_exists('slug', $validated) && trim((string)$validated['slug']) !== '') {
            $slug = $this->normSlug($validated['slug']);
            if ($slug === '') $slug = (string) ($row->slug ?? '');
            $slug = $this->ensureUniqueSlug($slug, (string) $row->uuid);
            $update['slug'] = $slug;
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

        $deptKey = $newDeptId ? (string) $newDeptId : 'global';
        $dirRel  = 'depy_uploads/scholarships/' . $deptKey;

        // cover remove
        if (filter_var($request->input('cover_image_remove', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->deletePublicPath($row->cover_image ?? null);
            $update['cover_image'] = null;
        }

        // cover replace
        if ($request->hasFile('cover_image')) {
            $f = $request->file('cover_image');
            if (!$f || !$f->isValid()) {
                return response()->json(['success' => false, 'message' => 'Cover image upload failed'], 422);
            }
            $this->deletePublicPath($row->cover_image ?? null);

            $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'scholarship');
            $meta = $this->uploadFileToPublic($f, $dirRel, $useSlug . '-cover');
            $update['cover_image'] = $meta['path'];
        }

        // current attachments
        $existing = [];
        if (!empty($row->attachments_json)) {
            $decoded = json_decode((string) $row->attachments_json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $existing = $decoded;
        }

        // remove attachments by path
        if (!empty($validated['attachments_remove']) && is_array($validated['attachments_remove'])) {
            $removePaths = [];
            foreach ($validated['attachments_remove'] as $p) $removePaths[] = (string) $p;

            $keep = [];
            foreach ($existing as $a) {
                $p = is_string($a) ? $a : (string) ($a['path'] ?? '');
                if ($p !== '' && in_array($p, $removePaths, true)) {
                    $this->deletePublicPath($p);
                    continue;
                }
                $keep[] = $a;
            }
            $existing = $keep;
        }

        // new attachments upload
        $mode = (string) ($validated['attachments_mode'] ?? 'append');
        if ($request->hasFile('attachments')) {
            $new = [];
            foreach ((array) $request->file('attachments') as $file) {
                if (!$file) continue;
                if (!$file->isValid()) {
                    return response()->json(['success' => false, 'message' => 'One of the attachments failed to upload'], 422);
                }
                $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'scholarship');
                $new[] = $this->uploadFileToPublic($file, $dirRel, $useSlug . '-att');
            }

            if ($mode === 'replace') {
                foreach ($existing as $a) {
                    $p = is_string($a) ? $a : (string) ($a['path'] ?? '');
                    if ($p !== '') $this->deletePublicPath($p);
                }
                $existing = $new;
            } else {
                $existing = array_values(array_merge($existing, $new));
            }
        }

        $update['attachments_json'] = !empty($existing) ? json_encode($existing) : null;

        DB::table('scholarships')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('scholarships')->where('id', (int) $row->id)->first();

        // ✅ LOG (PUT/PATCH)
        if ($fresh) {
            $newSnap = $this->logSnapshotScholarship($fresh);
            [$changed, $oldVals, $newVals] = $this->computeDiff($oldSnap, $newSnap);

            $this->safeLogActivity(
                $request,
                'update',
                'scholarships',
                'scholarships',
                (int) $row->id,
                $changed,
                $oldVals,
                $newVals,
                'Updated scholarship'
            );
        }

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function toggleFeatured(Request $request, $identifier)
    {
        $actorId = (int) ($request->attributes->get('auth_tokenable_id') ?? 0);
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return response()->json(['error' => 'Not allowed'], 403);

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveScholarship($request, $identifier, true, $deptId);
        if (! $row) return response()->json(['message' => 'Scholarship not found'], 404);

        $oldSnap = $this->logSnapshotScholarship($row);

        $new = ((int) ($row->is_featured_home ?? 0)) ? 0 : 1;

        DB::table('scholarships')->where('id', (int) $row->id)->update([
            'is_featured_home'       => $new,

            // ✅ AUTHORITY CONTROL SYNC
            'request_for_approval'   => $new,

            'updated_at'             => now(),
            'updated_at_ip'          => $request->ip(),
        ]);

        $fresh = DB::table('scholarships')->where('id', (int) $row->id)->first();

        // ✅ LOG (PATCH)
        if ($fresh) {
            $newSnap = $this->logSnapshotScholarship($fresh);
            [$changed, $oldVals, $newVals] = $this->computeDiff($oldSnap, $newSnap);

            $this->safeLogActivity(
                $request,
                'toggle_featured',
                'scholarships',
                'scholarships',
                (int) $row->id,
                $changed,
                $oldVals,
                $newVals,
                'Toggled featured flag'
            );
        }

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $actorId = (int) ($request->attributes->get('auth_tokenable_id') ?? 0);
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return response()->json(['error' => 'Not allowed'], 403);

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveScholarship($request, $identifier, false, $deptId);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        $oldSnap = $this->logSnapshotScholarship($row);

        DB::table('scholarships')->where('id', (int) $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('scholarships')->where('id', (int) $row->id)->first();

        // ✅ LOG (DELETE - soft)
        if ($fresh) {
            $newSnap = $this->logSnapshotScholarship($fresh);
            [$changed, $oldVals, $newVals] = $this->computeDiff($oldSnap, $newSnap);

            $this->safeLogActivity(
                $request,
                'delete',
                'scholarships',
                'scholarships',
                (int) $row->id,
                $changed,
                $oldVals,
                $newVals,
                'Soft deleted scholarship'
            );
        }

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $actorId = (int) ($request->attributes->get('auth_tokenable_id') ?? 0);
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return response()->json(['error' => 'Not allowed'], 403);

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveScholarship($request, $identifier, true, $deptId);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $oldSnap = $this->logSnapshotScholarship($row);

        DB::table('scholarships')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('scholarships')->where('id', (int) $row->id)->first();

        // ✅ LOG (POST)
        if ($fresh) {
            $newSnap = $this->logSnapshotScholarship($fresh);
            [$changed, $oldVals, $newVals] = $this->computeDiff($oldSnap, $newSnap);

            $this->safeLogActivity(
                $request,
                'restore',
                'scholarships',
                'scholarships',
                (int) $row->id,
                $changed,
                $oldVals,
                $newVals,
                'Restored scholarship'
            );
        }

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $actorId = (int) ($request->attributes->get('auth_tokenable_id') ?? 0);
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return response()->json(['error' => 'Not allowed'], 403);

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveScholarship($request, $identifier, true, $deptId);
        if (! $row) return response()->json(['message' => 'Scholarship not found'], 404);

        $oldSnap = $this->logSnapshotScholarship($row);

        // delete cover
        $this->deletePublicPath($row->cover_image ?? null);

        // delete attachments
        if (!empty($row->attachments_json)) {
            $decoded = json_decode((string) $row->attachments_json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                foreach ($decoded as $a) {
                    $p = is_string($a) ? $a : (string) ($a['path'] ?? '');
                    if ($p !== '') $this->deletePublicPath($p);
                }
            }
        }

        DB::table('scholarships')->where('id', (int) $row->id)->delete();

        // ✅ LOG (DELETE - force)
        $this->safeLogActivity(
            $request,
            'force_delete',
            'scholarships',
            'scholarships',
            (int) $row->id,
            array_keys($oldSnap),
            $oldSnap,
            null,
            'Force deleted scholarship'
        );

        return response()->json(['success' => true]);
    }

    /* ============================================
     | Public (no auth)
     |============================================ */

    public function publicIndex(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 10)));

        $q = $this->baseQuery($request, true);
        $this->applyVisibleWindow($q);

        // public default sort
        $q->orderByRaw('COALESCE(s.publish_at, s.created_at) desc');

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
        $row = $this->resolveScholarship($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Scholarship not found'], 404);

        $now = now();
        $isVisible =
            ($row->status === 'published') &&
            (empty($row->publish_at) || Carbon::parse($row->publish_at)->lte($now)) &&
            (empty($row->expire_at)  || Carbon::parse($row->expire_at)->gt($now));

        if (! $isVisible) {
            return response()->json(['message' => 'Scholarship not available'], 404);
        }

        // default public view increment (can disable with ?inc_view=0)
        $inc = $request->has('inc_view')
            ? filter_var($request->query('inc_view'), FILTER_VALIDATE_BOOLEAN)
            : true;

        if ($inc) {
            DB::table('scholarships')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }
}
