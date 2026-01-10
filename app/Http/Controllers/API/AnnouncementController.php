<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class AnnouncementController extends Controller
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
    $q = DB::table('announcements as a')
        ->leftJoin('departments as d', 'd.id', '=', 'a.department_id')
        ->select([
            'a.*',
            'd.title as department_title',
            'd.slug  as department_slug',
            'd.uuid  as department_uuid',
        ]);

    if (! $includeDeleted) {
        $q->whereNull('a.deleted_at');
    }

    // ?q=
    if ($request->filled('q')) {
        $term = '%' . trim((string) $request->query('q')) . '%';
        $q->where(function ($sub) use ($term) {
            $sub->where('a.title', 'like', $term)
                ->orWhere('a.slug', 'like', $term)
                ->orWhere('a.body', 'like', $term);
        });
    }

    // ✅ FIX: Handle multiple statuses (comma-separated or array)
    // ?status=draft,archived OR ?status[]=draft&status[]=archived
    if ($request->filled('status')) {
        $status = $request->query('status');
        
        // If it's an array, use whereIn
        if (is_array($status)) {
            $q->whereIn('a.status', $status);
        } 
        // If it's a comma-separated string, split it
        elseif (str_contains($status, ',')) {
            $statuses = array_map('trim', explode(',', $status));
            $q->whereIn('a.status', $statuses);
        }
        // Single status value
        else {
            $q->where('a.status', $status);
        }
    }

    // ✅ ADD: Support for inactive flag (shows draft + archived)
    if ($request->has('inactive') && $request->boolean('inactive')) {
        $q->whereIn('a.status', ['draft', 'archived']);
    }

    // ?featured=1/0
    if ($request->has('featured')) {
        $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($featured !== null) {
            $q->where('a.is_featured_home', $featured ? 1 : 0);
        }
    }

    // ?department=id|uuid|slug
    if ($request->filled('department')) {
        $dept = $this->resolveDepartment($request->query('department'), true);
        if ($dept) {
            $q->where('a.department_id', (int) $dept->id);
        } else {
            $q->whereRaw('1=0');
        }
    }

    // ?visible_now=1 -> only published and currently in window
    if ($request->has('visible_now')) {
        $visible = filter_var($request->query('visible_now'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($visible) {
            $now = now();
            $q->where('a.status', 'published')
                ->where(function ($w) use ($now) {
                    $w->whereNull('a.publish_at')->orWhere('a.publish_at', '<=', $now);
                })
                ->where(function ($w) use ($now) {
                    $w->whereNull('a.expire_at')->orWhere('a.expire_at', '>', $now);
                });
        }
    }

    // sort
    $sort = (string) $request->query('sort', 'created_at');
    $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

    $allowed = ['created_at', 'publish_at', 'expire_at', 'title', 'views_count', 'id'];
    if (! in_array($sort, $allowed, true)) $sort = 'created_at';

    $q->orderBy('a.' . $sort, $dir);

    return $q;
}
    protected function resolveAnnouncement(Request $request, $identifier, bool $includeDeleted = false, $departmentId = null)
    {
        $q = DB::table('announcements as a');
        if (! $includeDeleted) $q->whereNull('a.deleted_at');

        if ($departmentId !== null) {
            $q->where('a.department_id', (int) $departmentId);
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('a.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('a.uuid', (string) $identifier);
        } else {
            $q->where('a.slug', (string) $identifier);
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
            DB::table('announcements')
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

        $q->whereNull('a.deleted_at')
          ->where('a.status', 'published')
          ->where(function ($w) use ($now) {
              $w->whereNull('a.publish_at')->orWhere('a.publish_at', '<=', $now);
          })
          ->where(function ($w) use ($now) {
              $w->whereNull('a.expire_at')->orWhere('a.expire_at', '>', $now);
          });
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

        if ($onlyDeleted) {
            $query->whereNotNull('a.deleted_at');
        }

        $paginator = $query->paginate($perPage);
        $items = array_map(function ($r) { return $this->normalizeRow($r); }, $paginator->items());

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

        $row = $this->resolveAnnouncement($request, $identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Announcement not found'], 404);

        // optional: ?inc_view=1
        if (filter_var($request->query('inc_view', false), FILTER_VALIDATE_BOOLEAN)) {
            DB::table('announcements')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

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

        $row = $this->resolveAnnouncement($request, $identifier, $includeDeleted, $dept->id);
        if (! $row) return response()->json(['message' => 'Announcement not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function store(Request $request)
    {
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

        $slug = $this->normSlug($validated['slug'] ?? '');
        if ($slug === '') $slug = Str::slug($validated['title'], '-');
        $slug = $this->ensureUniqueSlug($slug);

        $uuid = (string) Str::uuid();
        $now  = now();

        $deptKey = !empty($validated['department_id']) ? (string) ((int) $validated['department_id']) : 'global';
        $dirRel  = 'depy_uploads/announcements/' . $deptKey;

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

        $id = DB::table('announcements')->insertGetId([
            'uuid'             => $uuid,
            'department_id'    => $validated['department_id'] ?? null,
            'title'            => $validated['title'],
            'slug'             => $slug,
            'body'             => $validated['body'],
            'cover_image'      => $coverPath,
            'attachments_json' => !empty($attachments) ? json_encode($attachments) : null,
            'is_featured_home' => (int) ($validated['is_featured_home'] ?? 0),
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
        ]);

        $row = DB::table('announcements')->where('id', $id)->first();

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
        $row = $this->resolveAnnouncement($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Announcement not found'], 404);

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
        if (array_key_exists('is_featured_home', $validated)) {
            $update['is_featured_home'] = (int) $validated['is_featured_home'];
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
        $dirRel  = 'depy_uploads/announcements/' . $deptKey;

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

            $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'announcement');
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
                $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'announcement');
                $new[] = $this->uploadFileToPublic($file, $dirRel, $useSlug . '-att');
            }

            if ($mode === 'replace') {
                // delete old files
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

        DB::table('announcements')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('announcements')->where('id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function toggleFeatured(Request $request, $identifier)
    {
        $row = $this->resolveAnnouncement($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Announcement not found'], 404);

        $new = ((int) ($row->is_featured_home ?? 0)) ? 0 : 1;

        DB::table('announcements')->where('id', (int) $row->id)->update([
            'is_featured_home' => $new,
            'updated_at'       => now(),
            'updated_at_ip'    => $request->ip(),
        ]);

        $fresh = DB::table('announcements')->where('id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveAnnouncement($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        DB::table('announcements')->where('id', (int) $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveAnnouncement($request, $identifier, true);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        DB::table('announcements')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('announcements')->where('id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveAnnouncement($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Announcement not found'], 404);

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

        DB::table('announcements')->where('id', (int) $row->id)->delete();

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
        $q->orderByRaw('COALESCE(a.publish_at, a.created_at) desc');

        $paginator = $q->paginate($perPage);
        $items = array_map(function ($r) { return $this->normalizeRow($r); }, $paginator->items());

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
        $row = $this->resolveAnnouncement($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Announcement not found'], 404);

        $now = now();
        $isVisible =
            ($row->status === 'published') &&
            (empty($row->publish_at) || Carbon::parse($row->publish_at)->lte($now)) &&
            (empty($row->expire_at)  || Carbon::parse($row->expire_at)->gt($now));

        if (! $isVisible) {
            return response()->json(['message' => 'Announcement not available'], 404);
        }

        // default public view increment (can disable with ?inc_view=0)
        $inc = $request->has('inc_view')
            ? filter_var($request->query('inc_view'), FILTER_VALIDATE_BOOLEAN)
            : true;

        if ($inc) {
            DB::table('announcements')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }
}
