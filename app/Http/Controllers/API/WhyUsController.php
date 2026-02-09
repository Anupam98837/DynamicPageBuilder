<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WhyUsController extends Controller
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

    private function strLimit(?string $s, int $max): ?string
    {
        $s = $s === null ? null : (string) $s;
        if ($s === null) return null;
        if (function_exists('mb_substr')) return mb_substr($s, 0, $max);
        return substr($s, 0, $max);
    }

    private function jsonOrNull($v): ?string
    {
        if ($v === null) return null;
        // ensure valid JSON even for strings/scalars
        return json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function rowForLog($row): ?array
    {
        if (!$row) return null;

        $arr = (array) $row;

        // decode attachments_json
        if (isset($arr['attachments_json']) && is_string($arr['attachments_json'])) {
            $decoded = json_decode($arr['attachments_json'], true);
            if (json_last_error() === JSON_ERROR_NONE) $arr['attachments_json'] = $decoded;
        }

        // decode metadata
        if (isset($arr['metadata']) && is_string($arr['metadata'])) {
            $decoded = json_decode($arr['metadata'], true);
            if (json_last_error() === JSON_ERROR_NONE) $arr['metadata'] = $decoded;
        }

        // pick a stable subset (avoid storing derived urls)
        $keys = [
            'id','uuid','title','slug','body',
            'cover_image','attachments_json',
            'is_featured_home','status','request_for_approval','is_approved',
            'publish_at','expire_at','views_count',
            'created_by','deleted_at',
            'metadata',
            'created_at','updated_at',
            'created_at_ip','updated_at_ip',
        ];

        $out = [];
        foreach ($keys as $k) {
            $out[$k] = $arr[$k] ?? null;
        }

        return $out;
    }

    private function diffKeys(?array $old, ?array $new): array
    {
        $old = $old ?? [];
        $new = $new ?? [];

        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
        $changed = [];

        foreach ($keys as $k) {
            $ov = $old[$k] ?? null;
            $nv = $new[$k] ?? null;

            // normalize arrays/objects for comparison
            $ovc = is_array($ov) || is_object($ov) ? $this->jsonOrNull($ov) : $ov;
            $nvc = is_array($nv) || is_object($nv) ? $this->jsonOrNull($nv) : $nv;

            if ($ovc !== $nvc) $changed[] = $k;
        }

        return array_values($changed);
    }

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
            $now = now();

            DB::table('user_data_activity_log')->insert([
                'performed_by'       => (int) ($actor['id'] ?: 0),
                'performed_by_role'  => $this->strLimit($actor['role'] !== '' ? $actor['role'] : null, 50),
                'ip'                 => $this->strLimit($r->ip(), 45),
                'user_agent'         => $this->strLimit((string) $r->userAgent(), 512),

                'activity'           => $this->strLimit($activity, 50),
                'module'             => $this->strLimit($module, 100),

                'table_name'         => $this->strLimit($tableName, 128),
                'record_id'          => $recordId,

                'changed_fields'     => $changedFields ? $this->jsonOrNull($changedFields) : null,
                'old_values'         => $oldValues !== null ? $this->jsonOrNull($oldValues) : null,
                'new_values'         => $newValues !== null ? $this->jsonOrNull($newValues) : null,

                'log_note'           => $note,

                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        } catch (\Throwable $e) {
            // never break main functionality because of logging
        }
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
                        $out[] = ['path' => $p, 'url' => $this->toUrl($p)];
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
            DB::table('why_us')
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

        $q->whereNull('w.deleted_at')
          ->where('w.status', 'published')
          ->where(function ($wq) use ($now) {
              $wq->whereNull('w.publish_at')->orWhere('w.publish_at', '<=', $now);
          })
          ->where(function ($wq) use ($now) {
              $wq->whereNull('w.expire_at')->orWhere('w.expire_at', '>', $now);
          });
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('why_us as w')->select('w.*');

        if (! $includeDeleted) {
            $q->whereNull('w.deleted_at');
        }

        // ?q=
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('w.title', 'like', $term)
                    ->orWhere('w.slug', 'like', $term)
                    ->orWhere('w.body', 'like', $term);
            });
        }

        // ?status=draft|published|archived
        if ($request->filled('status')) {
            $q->where('w.status', (string) $request->query('status'));
        }

        // ?featured=1/0
        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) {
                $q->where('w.is_featured_home', $featured ? 1 : 0);
            }
        }

        // ?visible_now=1 -> only published and currently in window
        if ($request->has('visible_now')) {
            $visible = filter_var($request->query('visible_now'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($visible) $this->applyVisibleWindow($q);
        }

        // sort
        $sort = (string) $request->query('sort', 'created_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['created_at', 'publish_at', 'expire_at', 'title', 'views_count', 'id'];
        if (! in_array($sort, $allowed, true)) $sort = 'created_at';

        $q->orderBy('w.' . $sort, $dir);

        return $q;
    }

    protected function resolveWhyUs(Request $request, $identifier, bool $includeDeleted = false)
    {
        $q = DB::table('why_us as w');
        if (! $includeDeleted) $q->whereNull('w.deleted_at');

        if (ctype_digit((string) $identifier)) {
            $q->where('w.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('w.uuid', (string) $identifier);
        } else {
            $q->where('w.slug', (string) $identifier);
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
            $query->whereNotNull('w.deleted_at');
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

    public function trash(Request $request)
    {
        $request->query->set('only_trashed', '1');
        return $this->index($request);
    }

    public function show(Request $request, $identifier)
    {
        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveWhyUs($request, $identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Why Us item not found'], 404);

        // optional: ?inc_view=1
        if (filter_var($request->query('inc_view', false), FILTER_VALIDATE_BOOLEAN)) {
            DB::table('why_us')->where('id', (int) $row->id)->increment('views_count');
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
            'cover_image'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],

            'attachments'      => ['nullable', 'array'],
            'attachments.*'    => ['file', 'max:20480'],
            'attachments_json' => ['nullable'],

            'is_featured_home' => ['nullable', 'in:0,1', 'boolean'],
            'status'           => ['nullable', 'in:draft,published,archived'],
            'publish_at'       => ['nullable', 'date'],
            'expire_at'        => ['nullable', 'date'],
            'metadata'         => ['nullable'],
        ]);

        $slug = $this->normSlug($validated['slug'] ?? '');
        if ($slug === '') $slug = Str::slug($validated['title'], '-');
        $slug = $this->ensureUniqueSlug($slug);

        $uuid = (string) Str::uuid();
        $now  = now();

        $dirRel = 'depy_uploads/why_us';

        try {
            // cover upload
            $coverPath = null;
            if ($request->hasFile('cover_image')) {
                $f = $request->file('cover_image');
                if (!$f || !$f->isValid()) {
                    $this->logActivity(
                        $request,
                        'create',
                        'why_us',
                        'why_us',
                        null,
                        ['cover_image'],
                        null,
                        null,
                        'Cover image upload failed'
                    );
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
                        $this->logActivity(
                            $request,
                            'create',
                            'why_us',
                            'why_us',
                            null,
                            ['attachments'],
                            null,
                            null,
                            'One of the attachments failed to upload'
                        );
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

            // ✅ Authority Control Sync:
            // When is_featured_home = 1 => request_for_approval = 1
            // When is_featured_home = 0 => request_for_approval = 0
            $featured = (int) ($validated['is_featured_home'] ?? 0);
            $requestForApproval = $featured ? 1 : 0;

            $insert = [
                'uuid'             => $uuid,
                'title'            => $validated['title'],
                'slug'             => $slug,
                'body'             => $validated['body'],
                'cover_image'      => $coverPath,
                'attachments_json' => !empty($attachments) ? json_encode($attachments) : null,

                'is_featured_home'     => $featured,
                'status'               => (string) ($validated['status'] ?? 'draft'),
                'request_for_approval' => $requestForApproval, // ✅ NEW
                // 'is_approved'        => 0, // default 0 in DB, no need to set

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

            $id = DB::table('why_us')->insertGetId($insert);
            $row = DB::table('why_us')->where('id', $id)->first();

            // ✅ LOG: create
            $newArr = $this->rowForLog($row);
            $this->logActivity(
                $request,
                'create',
                'why_us',
                'why_us',
                (int) $id,
                array_keys($newArr ?? []),
                null,
                $newArr,
                'Created Why Us item'
            );

            return response()->json([
                'success' => true,
                'data'    => $row ? $this->normalizeRow($row) : null,
            ]);
        } catch (\Throwable $e) {
            $this->logActivity(
                $request,
                'create',
                'why_us',
                'why_us',
                null,
                null,
                null,
                null,
                'Create failed: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    public function update(Request $request, $identifier)
    {
        $row = $this->resolveWhyUs($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Why Us item not found'], 404);

        $oldArr = $this->rowForLog($row);

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

        if (array_key_exists('is_featured_home', $validated)) {
            $newFeatured = (int) $validated['is_featured_home'];
            $update['is_featured_home'] = $newFeatured;

            // ✅ Authority Control Sync:
            // Whenever is_featured_home is updated, auto sync request_for_approval
            $update['request_for_approval'] = $newFeatured ? 1 : 0;
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

        $dirRel = 'depy_uploads/why_us';

        try {
            // cover remove
            if (filter_var($request->input('cover_image_remove', false), FILTER_VALIDATE_BOOLEAN)) {
                $this->deletePublicPath($row->cover_image ?? null);
                $update['cover_image'] = null;
            }

            // cover replace
            if ($request->hasFile('cover_image')) {
                $f = $request->file('cover_image');
                if (!$f || !$f->isValid()) {
                    $this->logActivity(
                        $request,
                        'update',
                        'why_us',
                        'why_us',
                        (int) $row->id,
                        ['cover_image'],
                        $oldArr,
                        null,
                        'Cover image upload failed'
                    );
                    return response()->json(['success' => false, 'message' => 'Cover image upload failed'], 422);
                }

                $this->deletePublicPath($row->cover_image ?? null);

                $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'why-us');
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
                        $this->logActivity(
                            $request,
                            'update',
                            'why_us',
                            'why_us',
                            (int) $row->id,
                            ['attachments_json'],
                            $oldArr,
                            null,
                            'One of the attachments failed to upload'
                        );
                        return response()->json(['success' => false, 'message' => 'One of the attachments failed to upload'], 422);
                    }
                    $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'why-us');
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

            DB::table('why_us')->where('id', (int) $row->id)->update($update);

            $fresh = DB::table('why_us')->where('id', (int) $row->id)->first();
            $newArr = $this->rowForLog($fresh);
            $changed = $this->diffKeys($oldArr, $newArr);

            // ✅ LOG: update
            $this->logActivity(
                $request,
                'update',
                'why_us',
                'why_us',
                (int) $row->id,
                $changed,
                $oldArr,
                $newArr,
                'Updated Why Us item'
            );

            return response()->json([
                'success' => true,
                'data'    => $fresh ? $this->normalizeRow($fresh) : null,
            ]);
        } catch (\Throwable $e) {
            $this->logActivity(
                $request,
                'update',
                'why_us',
                'why_us',
                (int) $row->id,
                null,
                $oldArr,
                null,
                'Update failed: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    public function toggleFeatured(Request $request, $identifier)
    {
        $row = $this->resolveWhyUs($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Why Us item not found'], 404);

        $oldArr = $this->rowForLog($row);

        try {
            $new = ((int) ($row->is_featured_home ?? 0)) ? 0 : 1;

            DB::table('why_us')->where('id', (int) $row->id)->update([
                'is_featured_home'     => $new,
                'request_for_approval' => $new ? 1 : 0, // ✅ NEW: auto sync
                'updated_at'           => now(),
                'updated_at_ip'        => $request->ip(),
            ]);

            $fresh = DB::table('why_us')->where('id', (int) $row->id)->first();
            $newArr = $this->rowForLog($fresh);
            $changed = $this->diffKeys($oldArr, $newArr);

            // ✅ LOG: update (toggle)
            $this->logActivity(
                $request,
                'update',
                'why_us',
                'why_us',
                (int) $row->id,
                $changed,
                $oldArr,
                $newArr,
                'Toggled is_featured_home'
            );

            return response()->json([
                'success' => true,
                'data'    => $fresh ? $this->normalizeRow($fresh) : null,
            ]);
        } catch (\Throwable $e) {
            $this->logActivity(
                $request,
                'update',
                'why_us',
                'why_us',
                (int) $row->id,
                null,
                $oldArr,
                null,
                'Toggle featured failed: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveWhyUs($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        $oldArr = $this->rowForLog($row);

        try {
            DB::table('why_us')->where('id', (int) $row->id)->update([
                'deleted_at'    => now(),
                'updated_at'    => now(),
                'updated_at_ip' => $request->ip(),
            ]);

            $fresh = DB::table('why_us')->where('id', (int) $row->id)->first();
            $newArr = $this->rowForLog($fresh);
            $changed = $this->diffKeys($oldArr, $newArr);

            // ✅ LOG: delete (soft)
            $this->logActivity(
                $request,
                'delete',
                'why_us',
                'why_us',
                (int) $row->id,
                $changed,
                $oldArr,
                $newArr,
                'Soft deleted Why Us item'
            );

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            $this->logActivity(
                $request,
                'delete',
                'why_us',
                'why_us',
                (int) $row->id,
                null,
                $oldArr,
                null,
                'Soft delete failed: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveWhyUs($request, $identifier, true);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $oldArr = $this->rowForLog($row);

        try {
            DB::table('why_us')->where('id', (int) $row->id)->update([
                'deleted_at'    => null,
                'updated_at'    => now(),
                'updated_at_ip' => $request->ip(),
            ]);

            $fresh = DB::table('why_us')->where('id', (int) $row->id)->first();
            $newArr = $this->rowForLog($fresh);
            $changed = $this->diffKeys($oldArr, $newArr);

            // ✅ LOG: restore
            $this->logActivity(
                $request,
                'restore',
                'why_us',
                'why_us',
                (int) $row->id,
                $changed,
                $oldArr,
                $newArr,
                'Restored Why Us item'
            );

            return response()->json([
                'success' => true,
                'data'    => $fresh ? $this->normalizeRow($fresh) : null,
            ]);
        } catch (\Throwable $e) {
            $this->logActivity(
                $request,
                'restore',
                'why_us',
                'why_us',
                (int) $row->id,
                null,
                $oldArr,
                null,
                'Restore failed: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveWhyUs($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Why Us item not found'], 404);

        $oldArr = $this->rowForLog($row);

        try {
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

            DB::table('why_us')->where('id', (int) $row->id)->delete();

            // ✅ LOG: force delete (hard)
            $this->logActivity(
                $request,
                'force_delete',
                'why_us',
                'why_us',
                (int) $row->id,
                ['force_delete'],
                $oldArr,
                null,
                'Hard deleted Why Us item'
            );

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            $this->logActivity(
                $request,
                'force_delete',
                'why_us',
                'why_us',
                (int) $row->id,
                null,
                $oldArr,
                null,
                'Force delete failed: ' . $e->getMessage()
            );
            throw $e;
        }
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
        $q->orderByRaw('COALESCE(w.publish_at, w.created_at) desc');

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

    public function publicShow(Request $request, $identifier)
    {
        $row = $this->resolveWhyUs($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Why Us item not found'], 404);

        $now = now();
        $isVisible =
            ($row->status === 'published') &&
            (empty($row->publish_at) || Carbon::parse($row->publish_at)->lte($now)) &&
            (empty($row->expire_at)  || Carbon::parse($row->expire_at)->gt($now));

        if (! $isVisible) {
            return response()->json(['message' => 'Why Us item not available'], 404);
        }

        // default public view increment (can disable with ?inc_view=0)
        $inc = $request->has('inc_view')
            ? filter_var($request->query('inc_view'), FILTER_VALIDATE_BOOLEAN)
            : true;

        if ($inc) {
            DB::table('why_us')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }
}
