<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NoticeController extends Controller
{
    /* ============================================
     | Helpers
     |============================================ */

    /**
     * Write activity to user_data_activity_log.
     * Never throws (so it won't break API functionality).
     */
    private function logActivity(
        Request $r,
        string $activity,
        string $module,
        string $tableName,
        $recordId = null,
        ?string $note = null,
        ?array $changedFields = null,
        $oldValues = null,
        $newValues = null
    ): void {
        try {
            if (!Schema::hasTable('user_data_activity_log')) {
                return;
            }

            $actor = $this->actor($r);

            $role = trim((string)($actor['role'] ?? ''));
            if ($role === '') $role = null;

            $ua = (string) ($r->userAgent() ?? '');
            if (strlen($ua) > 512) $ua = substr($ua, 0, 512);

            DB::table('user_data_activity_log')->insert([
                'performed_by'       => (int)($actor['id'] ?? 0),
                'performed_by_role'  => $role,
                'ip'                 => (string) ($r->ip() ?? ''),
                'user_agent'         => $ua,

                'activity'           => substr((string)$activity, 0, 50),
                'module'             => substr((string)$module, 0, 100),

                'table_name'         => substr((string)$tableName, 0, 128),
                'record_id'          => $recordId !== null ? (int)$recordId : null,

                'changed_fields'     => $changedFields !== null ? json_encode(array_values($changedFields)) : null,
                'old_values'         => $oldValues !== null ? json_encode($oldValues) : null,
                'new_values'         => $newValues !== null ? json_encode($newValues) : null,

                'log_note'           => $note,

                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (\Throwable $e) {
            // swallow
        }
    }

    private function trunc(?string $s, int $limit = 1000): ?string
    {
        if ($s === null) return null;
        $s = (string) $s;
        if (strlen($s) <= $limit) return $s;
        return substr($s, 0, $limit) . '...';
    }

    private function noticeSnapshot($row): array
    {
        if (!$row) return [];

        $a = (array) $row;

        return [
            'id'                  => $a['id'] ?? null,
            'uuid'                => $a['uuid'] ?? null,
            'department_id'       => $a['department_id'] ?? null,
            'title'               => $this->trunc(isset($a['title']) ? (string)$a['title'] : null, 255),
            'slug'                => $a['slug'] ?? null,
            'body'                => $this->trunc(isset($a['body']) ? (string)$a['body'] : null, 1200),
            'cover_image'         => $a['cover_image'] ?? null,
            'attachments_json'    => $this->trunc(isset($a['attachments_json']) ? (string)$a['attachments_json'] : null, 2000),

            'is_featured_home'    => $a['is_featured_home'] ?? null,
            'request_for_approval'=> $a['request_for_approval'] ?? null,
            'is_approved'         => $a['is_approved'] ?? null,

            'status'              => $a['status'] ?? null,
            'publish_at'          => $a['publish_at'] ?? null,
            'expire_at'           => $a['expire_at'] ?? null,
            'views_count'         => $a['views_count'] ?? null,

            'created_by'          => $a['created_by'] ?? null,
            'created_at'          => $a['created_at'] ?? null,
            'updated_at'          => $a['updated_at'] ?? null,
            'deleted_at'          => $a['deleted_at'] ?? null,

            'metadata'            => $this->trunc(isset($a['metadata']) ? (string)$a['metadata'] : null, 2000),
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

    private function actor(Request $r): array
    {
        return [
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? optional($r->user())->id ?? 0),
            'role' => (string) ($r->attributes->get('auth_role') ?? ($r->user()->role ?? '')),
            'type' => (string) ($r->attributes->get('auth_tokenable_type') ?? ($r->user() ? get_class($r->user()) : '')),
            'uuid' => (string) ($r->attributes->get('auth_user_uuid') ?? ($r->user()->uuid ?? '')),
        ];
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
        $q = DB::table('notices as n')
            ->leftJoin('departments as d', 'd.id', '=', 'n.department_id')
            ->select([
                'n.*',
                'd.title as department_title',
                'd.slug  as department_slug',
                'd.uuid  as department_uuid',
            ]);

        if (! $includeDeleted) {
            $q->whereNull('n.deleted_at');
        }

        // ?q=
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('n.title', 'like', $term)
                    ->orWhere('n.slug', 'like', $term)
                    ->orWhere('n.body', 'like', $term);
            });
        }

        // ?status=draft|published|archived
        if ($request->filled('status')) {
            $q->where('n.status', (string) $request->query('status'));
        }

        // ?featured=1/0
        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) {
                $q->where('n.is_featured_home', $featured ? 1 : 0);
            }
        }

        // ?department=id|uuid|slug
        if ($request->filled('department')) {
            $dept = $this->resolveDepartment($request->query('department'), true);
            if ($dept) {
                $q->where('n.department_id', (int) $dept->id);
            } else {
                $q->whereRaw('1=0');
            }
        }

        // ?visible_now=1 -> only published and currently in window
        if ($request->has('visible_now')) {
            $visible = filter_var($request->query('visible_now'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($visible) {
                $now = now();
                $q->where('n.status', 'published')
                    ->where(function ($w) use ($now) {
                        $w->whereNull('n.publish_at')->orWhere('n.publish_at', '<=', $now);
                    })
                    ->where(function ($w) use ($now) {
                        $w->whereNull('n.expire_at')->orWhere('n.expire_at', '>', $now);
                    });
            }
        }

        // sort
        $sort = (string) $request->query('sort', 'created_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['created_at', 'publish_at', 'expire_at', 'title', 'views_count', 'id'];
        if (! in_array($sort, $allowed, true)) $sort = 'created_at';

        $q->orderBy('n.' . $sort, $dir);

        return $q;
    }

    protected function resolveNotice(Request $request, $identifier, bool $includeDeleted = false, $departmentId = null)
    {
        $q = DB::table('notices as n');
        if (! $includeDeleted) $q->whereNull('n.deleted_at');

        if ($departmentId !== null) {
            $q->where('n.department_id', (int) $departmentId);
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('n.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('n.uuid', (string) $identifier);
        } else {
            $q->where('n.slug', (string) $identifier);
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

        // IMPORTANT: slug is UNIQUE in DB -> check across ALL rows (including soft-deleted)
        while (
            DB::table('notices')
                ->where('slug', $slug)
                ->when($ignoreUuid, function ($q) use ($ignoreUuid) {
                    $q->where('uuid', '!=', $ignoreUuid);
                })
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
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

        $q->whereNull('n.deleted_at')
          ->where('n.status', 'published')
          ->where(function ($w) use ($now) {
              $w->whereNull('n.publish_at')->orWhere('n.publish_at', '<=', $now);
          })
          ->where(function ($w) use ($now) {
              $w->whereNull('n.expire_at')->orWhere('n.expire_at', '>', $now);
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

        // ✅ apply department restriction (authenticated)
        if ($ac['mode'] === 'department') {
            $query->where('n.department_id', (int) $ac['department_id']);
        }

        if ($onlyDeleted) {
            $query->whereNotNull('n.deleted_at');
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
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none')        return response()->json(['message' => 'Notice not found'], 404);

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveNotice($request, $identifier, $includeDeleted, $deptId);
        if (! $row) return response()->json(['message' => 'Notice not found'], 404);

        // optional: ?inc_view=1
        if (filter_var($request->query('inc_view', false), FILTER_VALIDATE_BOOLEAN)) {
            DB::table('notices')->where('id', (int) $row->id)->increment('views_count');
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
        if ($ac['mode'] === 'none')        return response()->json(['message' => 'Notice not found'], 404);

        $dept = $this->resolveDepartment($department, true);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        // ✅ if user is department-scoped, they can only access their own department
        if ($ac['mode'] === 'department' && (int)$dept->id !== (int)$ac['department_id']) {
            return response()->json(['message' => 'Notice not found'], 404);
        }

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : (int) $dept->id;

        $row = $this->resolveNotice($request, $identifier, $includeDeleted, $deptId);
        if (! $row) return response()->json(['message' => 'Notice not found'], 404);

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
            $this->logActivity($request, 'forbidden', 'notices', 'notices', null, 'Store blocked by accessControl');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $actor = $this->actor($request);

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

        // ✅ department scope enforcement
        if ($ac['mode'] === 'department') {
            $deptId = (int) $ac['department_id'];
            if (array_key_exists('department_id', $validated) && $validated['department_id'] !== null && (int)$validated['department_id'] !== $deptId) {
                $this->logActivity($request, 'forbidden', 'notices', 'notices', null, 'Store blocked: department mismatch');
                return response()->json(['error' => 'Not allowed'], 403);
            }
            // force to actor dept
            $validated['department_id'] = $deptId;
            $request->merge(['department_id' => $deptId]);
        }

        $slug = $this->normSlug($validated['slug'] ?? '');
        if ($slug === '') $slug = Str::slug($validated['title'], '-');
        $slug = $this->ensureUniqueSlug($slug);

        $uuid = (string) Str::uuid();
        $now  = now();

        $deptKey = !empty($validated['department_id']) ? (string) ((int) $validated['department_id']) : 'global';
        $dirRel  = 'depy_uploads/notices/' . $deptKey;

        // cover upload
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $f = $request->file('cover_image');
            if (!$f || !$f->isValid()) {
                $this->logActivity($request, 'error', 'notices', 'notices', null, 'Store failed: cover image upload invalid');
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
                    $this->logActivity($request, 'error', 'notices', 'notices', null, 'Store failed: one attachment upload invalid');
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

        // ✅ Authority Control:
        // if is_featured_home = 1 => request_for_approval = 1
        // if is_featured_home = 0 => request_for_approval = 0
        $featured = (int) ($validated['is_featured_home'] ?? 0);
        $requestForApproval = $featured ? 1 : 0;

        $insert = [
            'uuid'                 => $uuid,
            'department_id'        => $validated['department_id'] ?? null,
            'title'                => $validated['title'],
            'slug'                 => $slug,
            'body'                 => $validated['body'],
            'cover_image'          => $coverPath,
            'attachments_json'     => !empty($attachments) ? json_encode($attachments) : null,

            'is_featured_home'     => $featured,

            // ✅ NEW FLAGS
            'request_for_approval' => $requestForApproval,
            'is_approved'          => 0,

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
        ];

        $id = DB::table('notices')->insertGetId($insert);

        $row = DB::table('notices')->where('id', $id)->first();

        // ✅ LOG: create
        $this->logActivity(
            $request,
            'create',
            'notices',
            'notices',
            $id,
            'Created notice',
            array_keys($insert),
            null,
            $this->noticeSnapshot($row)
        );

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
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'forbidden', 'notices', 'notices', null, 'Update blocked by accessControl');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveNotice($request, $identifier, true, $deptId);
        if (! $row) {
            $this->logActivity($request, 'not_found', 'notices', 'notices', null, 'Update failed: notice not found');
            return response()->json(['message' => 'Notice not found'], 404);
        }

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

        // ✅ department scope enforcement (cannot move across departments)
        if ($ac['mode'] === 'department') {
            $forcedDeptId = (int) $ac['department_id'];

            if (array_key_exists('department_id', $validated) && $validated['department_id'] !== null && (int)$validated['department_id'] !== $forcedDeptId) {
                $this->logActivity($request, 'forbidden', 'notices', 'notices', (int)$row->id, 'Update blocked: department mismatch');
                return response()->json(['error' => 'Not allowed'], 403);
            }

            // force department_id to actor dept (even if null was sent)
            $validated['department_id'] = $forcedDeptId;
            $request->merge(['department_id' => $forcedDeptId]);
        }

        // snapshot before
        $beforeRow = DB::table('notices')->where('id', (int)$row->id)->first();

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        // dept id for directory
        $newDeptId = array_key_exists('department_id', $validated)
            ? ($validated['department_id'] !== null ? (int) $validated['department_id'] : null)
            : ($row->department_id !== null ? (int) $row->department_id : null);

        // normal fields
        foreach (['title','body','status'] as $k) {
            if (array_key_exists($k, $validated)) $update[$k] = $validated[$k];
        }

        if (array_key_exists('department_id', $validated)) {
            $update['department_id'] = $validated['department_id'] !== null ? (int) $validated['department_id'] : null;
        }

        // ✅ Authority Control change on FEATURED toggle via update request
        if (array_key_exists('is_featured_home', $validated)) {
            $featured = (int) $validated['is_featured_home'];

            $update['is_featured_home'] = $featured;

            // ✅ if featured=1 => request_for_approval=1
            // ✅ if featured=0 => request_for_approval=0
            $update['request_for_approval'] = $featured ? 1 : 0;
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
        $dirRel  = 'depy_uploads/notices/' . $deptKey;

        // cover remove
        if (filter_var($request->input('cover_image_remove', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->deletePublicPath($row->cover_image ?? null);
            $update['cover_image'] = null;
        }

        // cover replace
        if ($request->hasFile('cover_image')) {
            $f = $request->file('cover_image');
            if (!$f || !$f->isValid()) {
                $this->logActivity($request, 'error', 'notices', 'notices', (int)$row->id, 'Update failed: cover image upload invalid');
                return response()->json(['success' => false, 'message' => 'Cover image upload failed'], 422);
            }
            $this->deletePublicPath($row->cover_image ?? null);

            $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'notice');
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
                    $this->logActivity($request, 'error', 'notices', 'notices', (int)$row->id, 'Update failed: one attachment upload invalid');
                    return response()->json(['success' => false, 'message' => 'One of the attachments failed to upload'], 422);
                }
                $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'notice');
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

        DB::table('notices')->where('id', (int) $row->id)->update($update);

        $afterRow = DB::table('notices')->where('id', (int) $row->id)->first();

        // ✅ LOG: update (diff by changed keys)
        $changedKeys = array_values(array_diff(array_keys($update), ['updated_at', 'updated_at_ip']));
        $old = [];
        $new = [];
        foreach ($changedKeys as $k) {
            $old[$k] = $beforeRow->$k ?? null;
            $new[$k] = $afterRow->$k ?? null;
        }

        $this->logActivity(
            $request,
            'update',
            'notices',
            'notices',
            (int)$row->id,
            'Updated notice',
            $changedKeys,
            $old,
            $new
        );

        return response()->json([
            'success' => true,
            'data'    => $afterRow ? $this->normalizeRow($afterRow) : null,
        ]);
    }

    public function toggleFeatured(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'forbidden', 'notices', 'notices', null, 'Toggle featured blocked by accessControl');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveNotice($request, $identifier, true, $deptId);
        if (! $row) {
            $this->logActivity($request, 'not_found', 'notices', 'notices', null, 'Toggle featured failed: notice not found');
            return response()->json(['message' => 'Notice not found'], 404);
        }

        $beforeRow = DB::table('notices')->where('id', (int)$row->id)->first();

        $newFeatured = ((int) ($row->is_featured_home ?? 0)) ? 0 : 1;

        // ✅ Authority Control:
        // toggle featured => sync request_for_approval accordingly
        DB::table('notices')->where('id', (int) $row->id)->update([
            'is_featured_home'      => $newFeatured,
            'request_for_approval'  => $newFeatured ? 1 : 0,
            'updated_at'            => now(),
            'updated_at_ip'         => $request->ip(),
        ]);

        $afterRow = DB::table('notices')->where('id', (int) $row->id)->first();

        // ✅ LOG: toggle featured
        $changedKeys = ['is_featured_home', 'request_for_approval'];
        $old = [
            'is_featured_home'     => $beforeRow->is_featured_home ?? null,
            'request_for_approval' => $beforeRow->request_for_approval ?? null,
        ];
        $new = [
            'is_featured_home'     => $afterRow->is_featured_home ?? null,
            'request_for_approval' => $afterRow->request_for_approval ?? null,
        ];

        $this->logActivity(
            $request,
            'update',
            'notices',
            'notices',
            (int)$row->id,
            'Toggled featured flag',
            $changedKeys,
            $old,
            $new
        );

        return response()->json([
            'success' => true,
            'data'    => $afterRow ? $this->normalizeRow($afterRow) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'forbidden', 'notices', 'notices', null, 'Destroy blocked by accessControl');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveNotice($request, $identifier, false, $deptId);
        if (! $row) {
            $this->logActivity($request, 'not_found', 'notices', 'notices', null, 'Destroy failed: not found or already deleted');
            return response()->json(['message' => 'Not found or already deleted'], 404);
        }

        $beforeRow = DB::table('notices')->where('id', (int)$row->id)->first();

        $now = now();

        DB::table('notices')->where('id', (int) $row->id)->update([
            'deleted_at'    => $now,
            'updated_at'    => $now,
            'updated_at_ip' => $request->ip(),
        ]);

        $afterRow = DB::table('notices')->where('id', (int)$row->id)->first();

        // ✅ LOG: soft delete
        $this->logActivity(
            $request,
            'delete',
            'notices',
            'notices',
            (int)$row->id,
            'Soft deleted notice',
            ['deleted_at'],
            ['deleted_at' => $beforeRow->deleted_at ?? null],
            ['deleted_at' => $afterRow->deleted_at ?? null]
        );

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'forbidden', 'notices', 'notices', null, 'Restore blocked by accessControl');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveNotice($request, $identifier, true, $deptId);
        if (! $row || $row->deleted_at === null) {
            $this->logActivity($request, 'not_found', 'notices', 'notices', null, 'Restore failed: not found in bin');
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $beforeRow = DB::table('notices')->where('id', (int)$row->id)->first();

        DB::table('notices')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $afterRow = DB::table('notices')->where('id', (int) $row->id)->first();

        // ✅ LOG: restore
        $this->logActivity(
            $request,
            'restore',
            'notices',
            'notices',
            (int)$row->id,
            'Restored notice',
            ['deleted_at'],
            ['deleted_at' => $beforeRow->deleted_at ?? null],
            ['deleted_at' => $afterRow->deleted_at ?? null]
        );

        return response()->json([
            'success' => true,
            'data'    => $afterRow ? $this->normalizeRow($afterRow) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'forbidden', 'notices', 'notices', null, 'Force delete blocked by accessControl');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveNotice($request, $identifier, true, $deptId);
        if (! $row) {
            $this->logActivity($request, 'not_found', 'notices', 'notices', null, 'Force delete failed: notice not found');
            return response()->json(['message' => 'Notice not found'], 404);
        }

        // snapshot before permanent delete
        $beforeRow = DB::table('notices')->where('id', (int)$row->id)->first();
        $beforeSnap = $this->noticeSnapshot($beforeRow);

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

        DB::table('notices')->where('id', (int) $row->id)->delete();

        // ✅ LOG: force delete
        $this->logActivity(
            $request,
            'force_delete',
            'notices',
            'notices',
            (int)$row->id,
            'Force deleted notice (permanent)',
            array_keys($beforeSnap),
            $beforeSnap,
            null
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
        $q->orderByRaw('COALESCE(n.publish_at, n.created_at) desc');

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
        $row = $this->resolveNotice($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Notice not found'], 404);

        $now = now();
        $isVisible =
            ($row->status === 'published') &&
            (empty($row->publish_at) || Carbon::parse($row->publish_at)->lte($now)) &&
            (empty($row->expire_at)  || Carbon::parse($row->expire_at)->gt($now));

        if (! $isVisible) {
            return response()->json(['message' => 'Notice not available'], 404);
        }

        // default public view increment (can disable with ?inc_view=0)
        $inc = $request->has('inc_view')
            ? filter_var($request->query('inc_view'), FILTER_VALIDATE_BOOLEAN)
            : true;

        if ($inc) {
            DB::table('notices')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }
}
