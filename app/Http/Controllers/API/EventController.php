<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EventController extends Controller
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
     * Activity logger (failsafe: never breaks main flow)
     */
    protected function logActivity(
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

            DB::table('user_data_activity_log')->insert([
                'performed_by'      => (int) ($actor['id'] ?? 0),
                'performed_by_role' => (string) ($actor['role'] ?? ''),
                'ip'                => (string) ($r->ip() ?? ''),
                'user_agent'        => substr((string) ($r->userAgent() ?? ''), 0, 512),

                'activity'          => $activity,
                'module'            => $module,

                'table_name'        => $tableName,
                'record_id'         => $recordId,

                'changed_fields'    => $changedFields !== null ? json_encode(array_values($changedFields)) : null,
                'old_values'        => $oldValues !== null ? json_encode($oldValues) : null,
                'new_values'        => $newValues !== null ? json_encode($newValues) : null,

                'log_note'          => $note,

                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            // never interrupt main request flow
        }
    }

    protected function eventLogKeys(): array
    {
        return [
            'uuid',
            'department_id',
            'slug',
            'title',
            'description',
            'cover_image_url',
            'gallery_images_json',
            'location',
            'event_start_date',
            'event_end_date',
            'event_start_time',
            'event_end_time',
            'is_featured_home',
            'sort_order',
            'status',
            'publish_at',
            'expire_at',
            'views_count',
            'created_by',
            'deleted_at',
            'metadata',
        ];
    }

    protected function pickEventLogRow($row): array
    {
        if (!$row) return [];

        $arr = (array) $row;
        $out = [];

        foreach ($this->eventLogKeys() as $k) {
            if (array_key_exists($k, $arr)) {
                $out[$k] = $arr[$k];
            }
        }

        return $out;
    }

    protected function diffEventRows(array $old, array $new): array
    {
        $changed = [];
        $oldOut  = [];
        $newOut  = [];

        foreach ($this->eventLogKeys() as $k) {
            $ov = $old[$k] ?? null;
            $nv = $new[$k] ?? null;

            if ($ov === $nv) continue;

            $changed[]   = $k;
            $oldOut[$k]  = $ov;
            $newOut[$k]  = $nv;
        }

        return [$changed, $oldOut, $newOut];
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

    protected function toUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') return null;
        if (preg_match('~^https?://~i', $path)) return $path;
        return url('/' . ltrim($path, '/'));
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
            'path' => $dirRel . '/' . $filename, // relative public path
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

    protected function parseTimeNullable($v): ?string
    {
        $v = trim((string) $v);
        if ($v === '') return null;

        // accept HH:MM or HH:MM:SS
        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $v)) return null;
        return $v;
    }

    protected function ensureUniqueSlug(string $slug, ?string $ignoreUuid = null): string
    {
        $base = $slug;
        $i = 2;

        while (
            DB::table('events')
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

    protected function normalizeRow($row): array
    {
        $arr = (array) $row;

        // decode gallery_images_json
        $g = $arr['gallery_images_json'] ?? null;
        if (is_string($g)) {
            $decoded = json_decode($g, true);
            $arr['gallery_images_json'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // decode metadata
        $m = $arr['metadata'] ?? null;
        if (is_string($m)) {
            $decoded = json_decode($m, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // cover url normalize
        $arr['cover_image_url'] = $this->toUrl($arr['cover_image_url'] ?? null);

        // normalized gallery_images[]
        $arr['gallery_images'] = [];
        $gallery = $arr['gallery_images_json'] ?? null;

        if (is_array($gallery)) {
            $out = [];
            foreach ($gallery as $item) {
                // supports: ["path1","path2"] OR [{url/path,caption}, ...]
                if (is_string($item)) {
                    $p = trim($item);
                    if ($p !== '') {
                        $out[] = [
                            'path'    => $p,
                            'url'     => $this->toUrl($p),
                            'caption' => null,
                        ];
                    }
                    continue;
                }

                if (is_array($item)) {
                    $p = trim((string) ($item['path'] ?? $item['url'] ?? ''));
                    if ($p !== '') {
                        $out[] = [
                            'path'    => $p,
                            'url'     => $this->toUrl($p),
                            'caption' => $item['caption'] ?? null,
                        ];
                    }
                    continue;
                }
            }
            $arr['gallery_images'] = array_values($out);
        }

        return $arr;
    }

    protected function applyVisibleWindow($q): void
    {
        $now = now();

        $q->whereNull('e.deleted_at')
          ->where('e.status', 'published')
          ->where(function ($w) use ($now) {
              $w->whereNull('e.publish_at')->orWhere('e.publish_at', '<=', $now);
          })
          ->where(function ($w) use ($now) {
              $w->whereNull('e.expire_at')->orWhere('e.expire_at', '>', $now);
          });
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('events as e')
            ->leftJoin('departments as d', 'd.id', '=', 'e.department_id')
            ->select([
                'e.*',
                'd.title as department_title',
                'd.slug  as department_slug',
                'd.uuid  as department_uuid',
            ]);

        if (! $includeDeleted) {
            $q->whereNull('e.deleted_at');
        }

        // ?q=
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('e.title', 'like', $term)
                    ->orWhere('e.slug', 'like', $term)
                    ->orWhere('e.description', 'like', $term)
                    ->orWhere('e.location', 'like', $term);
            });
        }

        // ?status=draft|published|archived
        if ($request->filled('status')) {
            $q->where('e.status', (string) $request->query('status'));
        }

        // ?featured=1/0
        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) {
                $q->where('e.is_featured_home', $featured ? 1 : 0);
            }
        }

        // ?department=id|uuid|slug
        if ($request->filled('department')) {
            $dept = $this->resolveDepartment($request->query('department'), true);
            if ($dept) {
                $q->where('e.department_id', (int) $dept->id);
            } else {
                $q->whereRaw('1=0');
            }
        }

        // ?visible_now=1
        if ($request->has('visible_now')) {
            $visible = filter_var($request->query('visible_now'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($visible) $this->applyVisibleWindow($q);
        }

        // Optional: filter by event start date range
        // ?start_from=YYYY-MM-DD&start_to=YYYY-MM-DD
        if ($request->filled('start_from')) {
            $q->whereDate('e.event_start_date', '>=', (string) $request->query('start_from'));
        }
        if ($request->filled('start_to')) {
            $q->whereDate('e.event_start_date', '<=', (string) $request->query('start_to'));
        }

        // sort
        $sort = (string) $request->query('sort', 'sort_order');
        $dir  = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowed = ['sort_order','created_at','publish_at','event_start_date','event_end_date','title','views_count','id'];
        if (! in_array($sort, $allowed, true)) $sort = 'sort_order';

        // Default: sort_order asc, then created_at desc for stability
        $q->orderBy('e.' . $sort, $dir);
        if ($sort !== 'created_at') $q->orderBy('e.created_at', 'desc');

        return $q;
    }

    protected function resolveEvent(Request $request, $identifier, bool $includeDeleted = false, $departmentId = null)
    {
        $q = DB::table('events as e');
        if (! $includeDeleted) $q->whereNull('e.deleted_at');

        if ($departmentId !== null) {
            $q->where('e.department_id', (int) $departmentId);
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('e.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('e.uuid', (string) $identifier);
        } else {
            $q->where('e.slug', (string) $identifier);
        }

        $row = $q->first();
        if (! $row) return null;

        // attach department info
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

    /* ============================================
     | CRUD (Admin / Auth side)
     |============================================ */

    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        if ($onlyDeleted) {
            $query->whereNotNull('e.deleted_at');
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

        $row = $this->resolveEvent($request, $identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Event not found'], 404);

        // optional: ?inc_view=1
        if (filter_var($request->query('inc_view', false), FILTER_VALIDATE_BOOLEAN)) {
            DB::table('events')->where('id', (int) $row->id)->increment('views_count');
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

        $row = $this->resolveEvent($request, $identifier, $includeDeleted, $dept->id);
        if (! $row) return response()->json(['message' => 'Event not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'department_id'      => ['nullable', 'integer', 'exists:departments,id'],

            'title'              => ['required', 'string', 'max:255'],
            'slug'               => ['nullable', 'string', 'max:160'],
            'description'        => ['nullable', 'string'],
            'location'           => ['nullable', 'string', 'max:255'],

            'event_start_date'   => ['nullable', 'date'],
            'event_end_date'     => ['nullable', 'date'],
            'event_start_time'   => ['nullable', 'string'],
            'event_end_time'     => ['nullable', 'string'],

            'is_featured_home'   => ['nullable', 'in:0,1', 'boolean'],
            'sort_order'         => ['nullable', 'integer', 'min:0'],

            'status'             => ['nullable', 'in:draft,published,archived'],
            'publish_at'         => ['nullable', 'date'],
            'expire_at'          => ['nullable', 'date'],

            'metadata'           => ['nullable'],

            // cover as file OR manual string (cover_image_url)
            'cover_image'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'cover_image_url'    => ['nullable', 'string', 'max:255'],

            // gallery: upload OR JSON
            'gallery_images'     => ['nullable', 'array'],
            'gallery_images.*'   => ['file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'gallery_captions'   => ['nullable', 'array'],
            'gallery_images_json'=> ['nullable'],
        ]);

        // time normalization
        $startTime = $this->parseTimeNullable($request->input('event_start_time'));
        $endTime   = $this->parseTimeNullable($request->input('event_end_time'));
        if ($request->filled('event_start_time') && $startTime === null) {
            $this->logActivity($request, 'create_failed', 'events', 'events', null, ['event_start_time'], null, null, 'Invalid event_start_time format');
            return response()->json(['success' => false, 'message' => 'Invalid event_start_time format. Use HH:MM or HH:MM:SS'], 422);
        }
        if ($request->filled('event_end_time') && $endTime === null) {
            $this->logActivity($request, 'create_failed', 'events', 'events', null, ['event_end_time'], null, null, 'Invalid event_end_time format');
            return response()->json(['success' => false, 'message' => 'Invalid event_end_time format. Use HH:MM or HH:MM:SS'], 422);
        }

        // slug
        $slug = $this->normSlug($validated['slug'] ?? '');
        if ($slug === '') $slug = Str::slug($validated['title'], '-');
        $slug = $this->ensureUniqueSlug($slug);

        $uuid = (string) Str::uuid();
        $now  = now();

        $deptKey = !empty($validated['department_id']) ? (string) ((int) $validated['department_id']) : 'global';
        $dirRel  = 'depy_uploads/events/' . $deptKey;

        // cover: prefer file, else manual cover_image_url
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $f = $request->file('cover_image');
            if (!$f || !$f->isValid()) {
                $this->logActivity($request, 'create_failed', 'events', 'events', null, ['cover_image'], null, null, 'Cover image upload failed');
                return response()->json(['success' => false, 'message' => 'Cover image upload failed'], 422);
            }
            $meta = $this->uploadFileToPublic($f, $dirRel, $slug . '-cover');
            $coverPath = $meta['path'];
        } elseif (!empty($validated['cover_image_url'])) {
            $coverPath = trim((string) $validated['cover_image_url']); // can be absolute URL or relative
        }

        // gallery from uploads
        $gallery = [];

        if ($request->hasFile('gallery_images')) {
            $caps = $request->input('gallery_captions', []);
            $i = 0;
            foreach ((array) $request->file('gallery_images') as $file) {
                if (!$file) { $i++; continue; }
                if (!$file->isValid()) {
                    $this->logActivity($request, 'create_failed', 'events', 'events', null, ['gallery_images'], null, null, 'One of the gallery images failed to upload');
                    return response()->json(['success' => false, 'message' => 'One of the gallery images failed to upload'], 422);
                }
                $meta = $this->uploadFileToPublic($file, $dirRel, $slug . '-gal');
                $caption = is_array($caps) ? ($caps[$i] ?? null) : null;

                $gallery[] = [
                    'url'     => $meta['path'], // stored path (like your note example)
                    'caption' => $caption ? (string) $caption : null,
                ];
                $i++;
            }
        }

        // gallery from manual gallery_images_json if no uploads
        if (empty($gallery) && $request->filled('gallery_images_json')) {
            $raw = $request->input('gallery_images_json');
            if (is_array($raw)) {
                $gallery = $raw;
            } elseif (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $gallery = $decoded;
            }
        }

        // metadata normalize
        $metadata = $request->input('metadata', null);
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
        }

        $id = DB::table('events')->insertGetId([
            'uuid'               => $uuid,
            'department_id'      => $validated['department_id'] ?? null,

            'slug'               => $slug,
            'title'              => $validated['title'],
            'description'        => $validated['description'] ?? null,
            'cover_image_url'    => $coverPath,
            'gallery_images_json'=> !empty($gallery) ? json_encode($gallery) : null,
            'location'           => $validated['location'] ?? null,

            'event_start_date'   => !empty($validated['event_start_date']) ? Carbon::parse($validated['event_start_date'])->toDateString() : null,
            'event_end_date'     => !empty($validated['event_end_date']) ? Carbon::parse($validated['event_end_date'])->toDateString() : null,
            'event_start_time'   => $startTime,
            'event_end_time'     => $endTime,

            'is_featured_home'   => (int) ($validated['is_featured_home'] ?? 0),
            'sort_order'         => (int) ($validated['sort_order'] ?? 0),

            'status'             => (string) ($validated['status'] ?? 'draft'),
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

        $row = DB::table('events')->where('id', $id)->first();

        // ✅ LOG: create
        $newLog = $this->pickEventLogRow($row);
        $this->logActivity(
            $request,
            'create',
            'events',
            'events',
            (int) $id,
            !empty($newLog) ? array_keys($newLog) : [],
            null,
            $newLog,
            'Event created'
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
        $row = $this->resolveEvent($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Event not found'], 404);

        // snapshot BEFORE (from actual table)
        $beforeRow = DB::table('events')->where('id', (int) $row->id)->first();
        $beforeLog = $this->pickEventLogRow($beforeRow);

        $validated = $request->validate([
            'department_id'       => ['nullable', 'integer', 'exists:departments,id'],

            'title'               => ['nullable', 'string', 'max:255'],
            'slug'                => ['nullable', 'string', 'max:160'],
            'description'         => ['nullable', 'string'],
            'location'            => ['nullable', 'string', 'max:255'],

            'event_start_date'    => ['nullable', 'date'],
            'event_end_date'      => ['nullable', 'date'],
            'event_start_time'    => ['nullable', 'string'],
            'event_end_time'      => ['nullable', 'string'],

            'is_featured_home'    => ['nullable', 'in:0,1', 'boolean'],
            'sort_order'          => ['nullable', 'integer', 'min:0'],

            'status'              => ['nullable', 'in:draft,published,archived'],
            'publish_at'          => ['nullable', 'date'],
            'expire_at'           => ['nullable', 'date'],

            'metadata'            => ['nullable'],

            'cover_image'         => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'cover_image_url'     => ['nullable', 'string', 'max:255'],
            'cover_image_remove'  => ['nullable', 'in:0,1', 'boolean'],

            // gallery controls
            'gallery_images'      => ['nullable', 'array'],
            'gallery_images.*'    => ['file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'gallery_captions'    => ['nullable', 'array'],

            'gallery_mode'        => ['nullable', 'in:append,replace'],
            'gallery_remove'      => ['nullable', 'array'], // remove by stored url/path
            'gallery_images_json' => ['nullable'],          // manual full set or append if no uploads
        ]);

        // time normalization (only if provided)
        if ($request->filled('event_start_time')) {
            $t = $this->parseTimeNullable($request->input('event_start_time'));
            if ($t === null) {
                $this->logActivity($request, 'update_failed', 'events', 'events', (int) $row->id, ['event_start_time'], null, null, 'Invalid event_start_time format');
                return response()->json(['success' => false, 'message' => 'Invalid event_start_time format. Use HH:MM or HH:MM:SS'], 422);
            }
        }
        if ($request->filled('event_end_time')) {
            $t = $this->parseTimeNullable($request->input('event_end_time'));
            if ($t === null) {
                $this->logActivity($request, 'update_failed', 'events', 'events', (int) $row->id, ['event_end_time'], null, null, 'Invalid event_end_time format');
                return response()->json(['success' => false, 'message' => 'Invalid event_end_time format. Use HH:MM or HH:MM:SS'], 422);
            }
        }

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        // dept id for directory
        $newDeptId = array_key_exists('department_id', $validated)
            ? ($validated['department_id'] !== null ? (int) $validated['department_id'] : null)
            : ($row->department_id !== null ? (int) $row->department_id : null);

        // normal fields
        foreach (['title','description','location','status'] as $k) {
            if (array_key_exists($k, $validated)) $update[$k] = $validated[$k];
        }

        if (array_key_exists('department_id', $validated)) {
            $update['department_id'] = $validated['department_id'] !== null ? (int) $validated['department_id'] : null;
        }

        if (array_key_exists('is_featured_home', $validated)) {
            $update['is_featured_home'] = (int) $validated['is_featured_home'];
        }

        if (array_key_exists('sort_order', $validated)) {
            $update['sort_order'] = (int) $validated['sort_order'];
        }

        if (array_key_exists('event_start_date', $validated)) {
            $update['event_start_date'] = !empty($validated['event_start_date'])
                ? Carbon::parse($validated['event_start_date'])->toDateString()
                : null;
        }
        if (array_key_exists('event_end_date', $validated)) {
            $update['event_end_date'] = !empty($validated['event_end_date'])
                ? Carbon::parse($validated['event_end_date'])->toDateString()
                : null;
        }

        if ($request->has('event_start_time')) {
            $update['event_start_time'] = $this->parseTimeNullable($request->input('event_start_time'));
        }
        if ($request->has('event_end_time')) {
            $update['event_end_time'] = $this->parseTimeNullable($request->input('event_end_time'));
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
        $dirRel  = 'depy_uploads/events/' . $deptKey;

        // cover remove
        if (filter_var($request->input('cover_image_remove', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->deletePublicPath($row->cover_image_url ?? null);
            $update['cover_image_url'] = null;
        }

        // cover replace with file
        if ($request->hasFile('cover_image')) {
            $f = $request->file('cover_image');
            if (!$f || !$f->isValid()) {
                $this->logActivity($request, 'update_failed', 'events', 'events', (int) $row->id, ['cover_image'], null, null, 'Cover image upload failed');
                return response()->json(['success' => false, 'message' => 'Cover image upload failed'], 422);
            }
            $this->deletePublicPath($row->cover_image_url ?? null);

            $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'event');
            $meta = $this->uploadFileToPublic($f, $dirRel, $useSlug . '-cover');
            $update['cover_image_url'] = $meta['path'];
        } elseif (array_key_exists('cover_image_url', $validated) && trim((string)$validated['cover_image_url']) !== '') {
            // manual cover url/path update
            $update['cover_image_url'] = trim((string) $validated['cover_image_url']);
        }

        // current gallery
        $existing = [];
        if (!empty($row->gallery_images_json)) {
            $decoded = json_decode((string) $row->gallery_images_json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $existing = $decoded;
        }

        // remove gallery by url/path
        if (!empty($validated['gallery_remove']) && is_array($validated['gallery_remove'])) {
            $removeList = array_map(fn($x) => (string)$x, $validated['gallery_remove']);

            $keep = [];
            foreach ($existing as $it) {
                $p = is_string($it) ? $it : (string) ($it['url'] ?? $it['path'] ?? '');
                if ($p !== '' && in_array($p, $removeList, true)) {
                    $this->deletePublicPath($p);
                    continue;
                }
                $keep[] = $it;
            }
            $existing = $keep;
        }

        // new uploads
        $mode = (string) ($validated['gallery_mode'] ?? 'append');

        if ($request->hasFile('gallery_images')) {
            $caps = $request->input('gallery_captions', []);
            $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'event');

            $new = [];
            $i = 0;
            foreach ((array) $request->file('gallery_images') as $file) {
                if (!$file) { $i++; continue; }
                if (!$file->isValid()) {
                    $this->logActivity($request, 'update_failed', 'events', 'events', (int) $row->id, ['gallery_images'], null, null, 'One of the gallery images failed to upload');
                    return response()->json(['success' => false, 'message' => 'One of the gallery images failed to upload'], 422);
                }
                $meta = $this->uploadFileToPublic($file, $dirRel, $useSlug . '-gal');
                $caption = is_array($caps) ? ($caps[$i] ?? null) : null;

                $new[] = [
                    'url'     => $meta['path'],
                    'caption' => $caption ? (string) $caption : null,
                ];
                $i++;
            }

            if ($mode === 'replace') {
                // delete old files
                foreach ($existing as $it) {
                    $p = is_string($it) ? $it : (string) ($it['url'] ?? $it['path'] ?? '');
                    if ($p !== '') $this->deletePublicPath($p);
                }
                $existing = $new;
            } else {
                $existing = array_values(array_merge($existing, $new));
            }
        }

        // manual gallery_images_json (if provided and no uploads)
        if (!$request->hasFile('gallery_images') && $request->filled('gallery_images_json')) {
            $raw = $request->input('gallery_images_json');
            $manual = null;

            if (is_array($raw)) $manual = $raw;
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $manual = $decoded;
            }

            if (is_array($manual)) {
                if ($mode === 'replace') {
                    foreach ($existing as $it) {
                        $p = is_string($it) ? $it : (string) ($it['url'] ?? $it['path'] ?? '');
                        if ($p !== '') $this->deletePublicPath($p);
                    }
                    $existing = $manual;
                } else {
                    $existing = array_values(array_merge($existing, $manual));
                }
            }
        }

        $update['gallery_images_json'] = !empty($existing) ? json_encode($existing) : null;

        DB::table('events')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('events')->where('id', (int) $row->id)->first();

        // ✅ LOG: update (diff)
        $afterLog = $this->pickEventLogRow($fresh);
        [$changedFields, $oldValues, $newValues] = $this->diffEventRows($beforeLog, $afterLog);

        $this->logActivity(
            $request,
            'update',
            'events',
            'events',
            (int) $row->id,
            $changedFields,
            $oldValues,
            $newValues,
            'Event updated'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function toggleFeatured(Request $request, $identifier)
    {
        $row = $this->resolveEvent($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Event not found'], 404);

        $beforeRow = DB::table('events')->where('id', (int) $row->id)->first();
        $beforeLog = $this->pickEventLogRow($beforeRow);

        $new = ((int) ($row->is_featured_home ?? 0)) ? 0 : 1;

        DB::table('events')->where('id', (int) $row->id)->update([
            'is_featured_home' => $new,
            'updated_at'       => now(),
            'updated_at_ip'    => $request->ip(),
        ]);

        $fresh = DB::table('events')->where('id', (int) $row->id)->first();

        // ✅ LOG: update (toggle featured)
        $afterLog = $this->pickEventLogRow($fresh);
        [$changedFields, $oldValues, $newValues] = $this->diffEventRows($beforeLog, $afterLog);

        $this->logActivity(
            $request,
            'update',
            'events',
            'events',
            (int) $row->id,
            $changedFields,
            $oldValues,
            $newValues,
            'Toggled featured on home'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveEvent($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        $beforeRow = DB::table('events')->where('id', (int) $row->id)->first();
        $beforeLog = $this->pickEventLogRow($beforeRow);

        $now = now();

        DB::table('events')->where('id', (int) $row->id)->update([
            'deleted_at'    => $now,
            'updated_at'    => $now,
            'updated_at_ip' => $request->ip(),
        ]);

        // ✅ LOG: delete (soft)
        $afterRow = DB::table('events')->where('id', (int) $row->id)->first();
        $afterLog = $this->pickEventLogRow($afterRow);
        [$changedFields, $oldValues, $newValues] = $this->diffEventRows($beforeLog, $afterLog);

        $this->logActivity(
            $request,
            'delete',
            'events',
            'events',
            (int) $row->id,
            $changedFields,
            $oldValues,
            $newValues,
            'Event soft deleted'
        );

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveEvent($request, $identifier, true);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $beforeRow = DB::table('events')->where('id', (int) $row->id)->first();
        $beforeLog = $this->pickEventLogRow($beforeRow);

        $now = now();

        DB::table('events')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => $now,
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('events')->where('id', (int) $row->id)->first();

        // ✅ LOG: restore
        $afterLog = $this->pickEventLogRow($fresh);
        [$changedFields, $oldValues, $newValues] = $this->diffEventRows($beforeLog, $afterLog);

        $this->logActivity(
            $request,
            'restore',
            'events',
            'events',
            (int) $row->id,
            $changedFields,
            $oldValues,
            $newValues,
            'Event restored from bin'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveEvent($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Event not found'], 404);

        // snapshot BEFORE delete
        $beforeRow = DB::table('events')->where('id', (int) $row->id)->first();
        $beforeLog = $this->pickEventLogRow($beforeRow);

        // delete cover
        $this->deletePublicPath($row->cover_image_url ?? null);

        // delete gallery files
        if (!empty($row->gallery_images_json)) {
            $decoded = json_decode((string) $row->gallery_images_json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                foreach ($decoded as $it) {
                    $p = is_string($it) ? $it : (string) ($it['url'] ?? $it['path'] ?? '');
                    if ($p !== '') $this->deletePublicPath($p);
                }
            }
        }

        DB::table('events')->where('id', (int) $row->id)->delete();

        // ✅ LOG: delete (force)
        $this->logActivity(
            $request,
            'delete',
            'events',
            'events',
            (int) $row->id,
            array_keys($beforeLog),
            $beforeLog,
            null,
            'Event force deleted'
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

        // public default sort: publish_at desc then sort_order
        $q->orderByRaw('COALESCE(e.publish_at, e.created_at) desc')
          ->orderBy('e.sort_order', 'asc');

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

    public function publicIndexByDepartment(Request $request, $department)
    {
        $dept = $this->resolveDepartment($department, false);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        $request->query->set('department', $dept->id);
        return $this->publicIndex($request);
    }

    public function publicShow(Request $request, $identifier)
    {
        $row = $this->resolveEvent($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Event not found'], 404);

        $now = now();
        $isVisible =
            ($row->status === 'published') &&
            (empty($row->publish_at) || Carbon::parse($row->publish_at)->lte($now)) &&
            (empty($row->expire_at)  || Carbon::parse($row->expire_at)->gt($now));

        if (! $isVisible) {
            return response()->json(['message' => 'Event not available'], 404);
        }

        // default public view increment (can disable with ?inc_view=0)
        $inc = $request->has('inc_view')
            ? filter_var($request->query('inc_view'), FILTER_VALIDATE_BOOLEAN)
            : true;

        if ($inc) {
            DB::table('events')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }
}
