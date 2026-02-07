<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Throwable;

class CareerNoticeController extends Controller
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

    protected function toUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') return null;
        if (preg_match('~^https?://~i', $path)) return $path;
        return url('/' . ltrim($path, '/'));
    }

    /* -------------------------
     | Activity Log Helpers
     * ------------------------- */

    protected function jsonOrNull($v): ?string
    {
        if ($v === null) return null;
        $json = json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return (json_last_error() === JSON_ERROR_NONE) ? $json : null;
    }

    protected function logSafeRow($row): array
    {
        $a = (array) $row;

        // Keep logs useful but not huge (exclude body/content)
        return [
            'id'                  => $a['id'] ?? null,
            'uuid'                => $a['uuid'] ?? null,
            'title'               => $a['title'] ?? null,
            'slug'                => $a['slug'] ?? null,
            'status'              => $a['status'] ?? null,
            'is_featured_home'    => $a['is_featured_home'] ?? null,
            'request_for_approval'=> $a['request_for_approval'] ?? null,
            'is_approved'         => $a['is_approved'] ?? null,
            'publish_at'          => $a['publish_at'] ?? null,
            'expire_at'           => $a['expire_at'] ?? null,
            'cover_image'         => $a['cover_image'] ?? null,
            'attachments_json'    => $a['attachments_json'] ?? null,
            'metadata'            => $a['metadata'] ?? null,
            'views_count'         => $a['views_count'] ?? null,
            'deleted_at'          => $a['deleted_at'] ?? null,
            'created_by'          => $a['created_by'] ?? null,
            'created_at'          => $a['created_at'] ?? null,
            'updated_at'          => $a['updated_at'] ?? null,
            'created_at_ip'       => $a['created_at_ip'] ?? null,
            'updated_at_ip'       => $a['updated_at_ip'] ?? null,
        ];
    }

    protected function diffKeys(array $old, array $new, array $preferredKeys = []): array
    {
        $keys = !empty($preferredKeys)
            ? array_values(array_unique($preferredKeys))
            : array_values(array_unique(array_merge(array_keys($old), array_keys($new))));

        $changed = [];
        foreach ($keys as $k) {
            $ov = $old[$k] ?? null;
            $nv = $new[$k] ?? null;
            // loose compare is fine for DB scalars; if you want strict, replace with !==
            if ($ov != $nv) $changed[] = $k;
        }
        return $changed;
    }

    protected function activityLog(
        Request $r,
        string $activity,
        string $module,
        string $table,
        ?int $recordId = null,
        ?array $changedFields = null,
        $oldValues = null,
        $newValues = null,
        ?string $note = null
    ): void {
        try {
            $actor = $this->actor($r);

            DB::table('user_data_activity_log')->insert([
                'performed_by'       => (int) ($actor['id'] ?: 0),
                'performed_by_role'  => ($actor['role'] !== '' ? $actor['role'] : null),
                'ip'                 => $r->ip(),
                'user_agent'         => substr((string) $r->userAgent(), 0, 512),

                'activity'           => substr($activity, 0, 50),
                'module'             => substr($module, 0, 100),

                'table_name'         => substr($table, 0, 128),
                'record_id'          => $recordId,

                'changed_fields'     => $this->jsonOrNull($changedFields),
                'old_values'         => $this->jsonOrNull($oldValues),
                'new_values'         => $this->jsonOrNull($newValues),

                'log_note'           => $note,

                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (Throwable $e) {
            // Never break the main API flow if logging fails
            Log::warning('Activity log insert failed', [
                'module' => $module,
                'activity' => $activity,
                'table' => $table,
                'record_id' => $recordId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /* -------------------------
     | Existing helpers
     * ------------------------- */

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

        // url helpers
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

        // IMPORTANT: slug is UNIQUE in DB, so check across ALL rows (including deleted)
        while (
            DB::table('career_notices')
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
        // read meta BEFORE move
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

        $q->whereNull('deleted_at')
          ->where('status', 'published')
          ->where(function ($w) use ($now) {
              $w->whereNull('publish_at')->orWhere('publish_at', '<=', $now);
          })
          ->where(function ($w) use ($now) {
              $w->whereNull('expire_at')->orWhere('expire_at', '>', $now);
          });
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('career_notices');

        if (! $includeDeleted) {
            $q->whereNull('deleted_at');
        }

        // ?q=
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('title', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                    ->orWhere('body', 'like', $term);
            });
        }

        // ?status=draft|published|archived
        if ($request->filled('status')) {
            $q->where('status', (string) $request->query('status'));
        }

        // ?featured=1/0
        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) {
                $q->where('is_featured_home', $featured ? 1 : 0);
            }
        }

        // ?visible_now=1 -> only published and currently in window
        if ($request->has('visible_now')) {
            $visible = filter_var($request->query('visible_now'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($visible) {
                $this->applyVisibleWindow($q);
            }
        }

        // sort
        $sort = (string) $request->query('sort', 'created_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['created_at', 'publish_at', 'expire_at', 'title', 'views_count', 'id'];
        if (! in_array($sort, $allowed, true)) $sort = 'created_at';

        $q->orderBy($sort, $dir);

        return $q;
    }

    protected function resolveCareerNotice($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('career_notices');
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

    /* ============================================
     | CRUD (Authenticated)
     |============================================ */

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
        $items = array_map(fn ($r) => $this->normalizeRow($r), $paginator->items());

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

    public function trash(Request $request)
    {
        $request->query->set('only_trashed', '1');
        return $this->index($request);
    }

    public function show(Request $request, $identifier)
    {
        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveCareerNotice($identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Career notice not found'], 404);

        // optional: ?inc_view=1
        if (filter_var($request->query('inc_view', false), FILTER_VALIDATE_BOOLEAN)) {
            DB::table('career_notices')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:160'],
            'body'             => ['required', 'string'],

            'is_featured_home' => ['nullable', 'in:0,1', 'boolean'],
            'status'           => ['nullable', 'in:draft,published,archived'],
            'publish_at'       => ['nullable', 'date'],
            'expire_at'        => ['nullable', 'date'],
            'metadata'         => ['nullable'],

            'cover_image'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'attachments'      => ['nullable', 'array'],
            'attachments.*'    => ['file', 'max:20480'],
            'attachments_json' => ['nullable'],
        ]);

        $slug = $this->normSlug($validated['slug'] ?? '');
        if ($slug === '') $slug = Str::slug($validated['title'], '-');
        $slug = $this->ensureUniqueSlug($slug);

        $uuid = (string) Str::uuid();
        $now  = now();

        $dirRel = 'depy_uploads/career_notices';

        // cover upload
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $f = $request->file('cover_image');
            if (!$f || !$f->isValid()) {
                // log failed create attempt (non-blocking)
                $this->activityLog($request, 'create_failed', 'career_notices', 'career_notices', null, null, null, null, 'Cover image upload failed');
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
                    // log failed create attempt (non-blocking)
                    $this->activityLog($request, 'create_failed', 'career_notices', 'career_notices', null, null, null, null, 'One of the attachments failed to upload');
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

        // ✅ AUTHORITY CONTROL AUTO-SYNC:
        // if is_featured_home = 1 => request_for_approval = 1
        // if is_featured_home = 0 => request_for_approval = 0
        $featured = (int) ($validated['is_featured_home'] ?? 0);
        $requestForApproval = $featured === 1 ? 1 : 0;

        $id = DB::table('career_notices')->insertGetId([
            'uuid'               => $uuid,
            'title'              => $validated['title'],
            'slug'               => $slug,
            'body'               => $validated['body'],
            'cover_image'        => $coverPath,
            'attachments_json'   => !empty($attachments) ? json_encode($attachments) : null,
            'is_featured_home'   => $featured,
            'status'             => (string) ($validated['status'] ?? 'draft'),

            // ✅ new flags
            'request_for_approval' => $requestForApproval,
            'is_approved'          => 0,

            'publish_at'         => !empty($validated['publish_at']) ? Carbon::parse($validated['publish_at']) : null,
            'expire_at'          => !empty($validated['expire_at']) ? Carbon::parse($validated['expire_at']) : null,
            'views_count'        => 0,
            'created_by'         => $actor['id'] ?: null,
            'created_at'         => $now,
            'updated_at'         => $now,
            'created_at_ip'      => $request->ip(),
            'updated_at_ip'      => $request->ip(),
            'metadata'           => $metadata !== null ? json_encode($metadata) : null,
        ]);

        $row = DB::table('career_notices')->where('id', $id)->first();

        // ✅ activity log (CREATE)
        $this->activityLog(
            $request,
            'create',
            'career_notices',
            'career_notices',
            (int) $id,
            null,
            null,
            $row ? $this->logSafeRow($row) : null,
            'Career notice created'
        );

        return response()->json([
            'success' => true,
            'data'    => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    public function update(Request $request, $identifier)
    {
        $row = $this->resolveCareerNotice($identifier, true);
        if (! $row) {
            $this->activityLog($request, 'update_not_found', 'career_notices', 'career_notices', null, null, null, null, 'Identifier: ' . (string) $identifier);
            return response()->json(['message' => 'Career notice not found'], 404);
        }

        $oldForLog = $this->logSafeRow($row);

        $validated = $request->validate([
            'title'             => ['nullable', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:160'],
            'body'              => ['nullable', 'string'],

            'is_featured_home'  => ['nullable', 'in:0,1', 'boolean'],
            'status'            => ['nullable', 'in:draft,published,archived'],
            'publish_at'        => ['nullable', 'date'],
            'expire_at'         => ['nullable', 'date'],
            'metadata'          => ['nullable'],

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

        foreach (['title','body','status'] as $k) {
            if (array_key_exists($k, $validated)) $update[$k] = $validated[$k];
        }

        // ✅ AUTHORITY CONTROL AUTO-SYNC (UPDATE):
        // if is_featured_home is explicitly updated:
        //  - set request_for_approval = 1 when featured
        //  - set request_for_approval = 0 when unfeatured
        if (array_key_exists('is_featured_home', $validated)) {
            $newFeatured = (int) $validated['is_featured_home'];
            $update['is_featured_home'] = $newFeatured;
            $update['request_for_approval'] = $newFeatured === 1 ? 1 : 0;
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

        $dirRel = 'depy_uploads/career_notices';

        // cover remove
        if (filter_var($request->input('cover_image_remove', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->deletePublicPath($row->cover_image ?? null);
            $update['cover_image'] = null;
        }

        // cover replace
        if ($request->hasFile('cover_image')) {
            $f = $request->file('cover_image');
            if (!$f || !$f->isValid()) {
                $this->activityLog($request, 'update_failed', 'career_notices', 'career_notices', (int) $row->id, null, $oldForLog, null, 'Cover image upload failed');
                return response()->json(['success' => false, 'message' => 'Cover image upload failed'], 422);
            }
            $this->deletePublicPath($row->cover_image ?? null);

            $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'career-notice');
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
                    $this->activityLog($request, 'update_failed', 'career_notices', 'career_notices', (int) $row->id, null, $oldForLog, null, 'One of the attachments failed to upload');
                    return response()->json(['success' => false, 'message' => 'One of the attachments failed to upload'], 422);
                }
                $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'career-notice');
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

        DB::table('career_notices')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('career_notices')->where('id', (int) $row->id)->first();

        // ✅ activity log (UPDATE)
        $newForLog = $fresh ? $this->logSafeRow($fresh) : null;
        $changed = $newForLog ? $this->diffKeys($oldForLog, $newForLog) : null;

        $this->activityLog(
            $request,
            'update',
            'career_notices',
            'career_notices',
            (int) $row->id,
            $changed,
            $oldForLog,
            $newForLog,
            'Career notice updated'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function toggleFeatured(Request $request, $identifier)
    {
        $row = $this->resolveCareerNotice($identifier, true);
        if (! $row) {
            $this->activityLog($request, 'toggle_featured_not_found', 'career_notices', 'career_notices', null, null, null, null, 'Identifier: ' . (string) $identifier);
            return response()->json(['message' => 'Career notice not found'], 404);
        }

        $oldForLog = $this->logSafeRow($row);

        $new = ((int) ($row->is_featured_home ?? 0)) ? 0 : 1;

        // ✅ AUTHORITY CONTROL AUTO-SYNC (TOGGLE):
        // if is_featured_home becomes 1 => request_for_approval = 1
        // if becomes 0 => request_for_approval = 0
        DB::table('career_notices')->where('id', (int) $row->id)->update([
            'is_featured_home'     => $new,
            'request_for_approval' => ($new === 1 ? 1 : 0),
            'updated_at'           => now(),
            'updated_at_ip'        => $request->ip(),
        ]);

        $fresh = DB::table('career_notices')->where('id', (int) $row->id)->first();

        // ✅ activity log (TOGGLE)
        $newForLog = $fresh ? $this->logSafeRow($fresh) : null;
        $changed = $newForLog ? $this->diffKeys($oldForLog, $newForLog, ['is_featured_home','request_for_approval','updated_at','updated_at_ip']) : null;

        $this->activityLog(
            $request,
            'toggle_featured',
            'career_notices',
            'career_notices',
            (int) $row->id,
            $changed,
            $oldForLog,
            $newForLog,
            'Featured toggled'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveCareerNotice($identifier, false);
        if (! $row) {
            $this->activityLog($request, 'soft_delete_not_found', 'career_notices', 'career_notices', null, null, null, null, 'Identifier: ' . (string) $identifier);
            return response()->json(['message' => 'Not found or already deleted'], 404);
        }

        $oldForLog = $this->logSafeRow($row);

        DB::table('career_notices')->where('id', (int) $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('career_notices')->where('id', (int) $row->id)->first();
        $newForLog = $fresh ? $this->logSafeRow($fresh) : null;

        $this->activityLog(
            $request,
            'soft_delete',
            'career_notices',
            'career_notices',
            (int) $row->id,
            $newForLog ? $this->diffKeys($oldForLog, $newForLog, ['deleted_at','updated_at','updated_at_ip']) : null,
            $oldForLog,
            $newForLog,
            'Moved to bin (soft delete)'
        );

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveCareerNotice($identifier, true);
        if (! $row || $row->deleted_at === null) {
            $this->activityLog($request, 'restore_not_found', 'career_notices', 'career_notices', $row ? (int) $row->id : null, null, $row ? $this->logSafeRow($row) : null, null, 'Not found in bin');
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $oldForLog = $this->logSafeRow($row);

        DB::table('career_notices')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('career_notices')->where('id', (int) $row->id)->first();

        $newForLog = $fresh ? $this->logSafeRow($fresh) : null;

        $this->activityLog(
            $request,
            'restore',
            'career_notices',
            'career_notices',
            (int) $row->id,
            $newForLog ? $this->diffKeys($oldForLog, $newForLog, ['deleted_at','updated_at','updated_at_ip']) : null,
            $oldForLog,
            $newForLog,
            'Restored from bin'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveCareerNotice($identifier, true);
        if (! $row) {
            $this->activityLog($request, 'force_delete_not_found', 'career_notices', 'career_notices', null, null, null, null, 'Identifier: ' . (string) $identifier);
            return response()->json(['message' => 'Career notice not found'], 404);
        }

        $oldForLog = $this->logSafeRow($row);

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

        DB::table('career_notices')->where('id', (int) $row->id)->delete();

        // ✅ activity log (FORCE DELETE)
        $this->activityLog(
            $request,
            'force_delete',
            'career_notices',
            'career_notices',
            (int) $row->id,
            ['__deleted__'],
            $oldForLog,
            null,
            'Permanently deleted'
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
        $q->orderByRaw('COALESCE(publish_at, created_at) desc');

        $paginator = $q->paginate($perPage);
        $items = array_map(fn ($r) => $this->normalizeRow($r), $paginator->items());

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

    public function publicShow(Request $request, $identifier)
    {
        $row = $this->resolveCareerNotice($identifier, false);
        if (! $row) return response()->json(['message' => 'Career notice not found'], 404);

        $now = now();
        $isVisible =
            ($row->status === 'published') &&
            (empty($row->publish_at) || Carbon::parse($row->publish_at)->lte($now)) &&
            (empty($row->expire_at)  || Carbon::parse($row->expire_at)->gt($now));

        if (! $isVisible) {
            return response()->json(['message' => 'Career notice not available'], 404);
        }

        // default public view increment (can disable with ?inc_view=0)
        $inc = $request->has('inc_view')
            ? filter_var($request->query('inc_view'), FILTER_VALIDATE_BOOLEAN)
            : true;

        if ($inc) {
            DB::table('career_notices')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }
}
