<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SuccessStoryController extends Controller
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

    private function jsonOrNull($v): ?string
    {
        if ($v === null) return null;
        return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Activity logger (safe: never breaks core flow)
     */
    private function logActivity(
        Request $r,
        string $activity,
        string $module,
        string $tableName,
        ?int $recordId = null,
        $changedFields = null,
        $oldValues = null,
        $newValues = null,
        ?string $note = null
    ): void {
        try {
            $actor = $this->actor($r);

            DB::table('user_data_activity_log')->insert([
                'performed_by'       => (int) ($actor['id'] ?? 0),
                'performed_by_role'  => (string) ($actor['role'] ?? null),
                'ip'                 => (string) ($r->ip() ?? null),
                'user_agent'         => (string) ($r->userAgent() ?? null),

                'activity'           => $activity,
                'module'             => $module,

                'table_name'         => $tableName,
                'record_id'          => $recordId,

                'changed_fields'     => $this->jsonOrNull($changedFields),
                'old_values'         => $this->jsonOrNull($oldValues),
                'new_values'         => $this->jsonOrNull($newValues),

                'log_note'           => $note,

                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (\Throwable $e) {
            // Intentionally swallow: logging must never affect API functionality.
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
        $q = DB::table('success_stories as s')
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
                $sub->where('s.name', 'like', $term)
                    ->orWhere('s.title', 'like', $term)
                    ->orWhere('s.slug', 'like', $term)
                    ->orWhere('s.description', 'like', $term)
                    ->orWhere('s.quote', 'like', $term);
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

        // ?year=2025
        if ($request->filled('year')) {
            $yr = (int) $request->query('year');
            if ($yr > 0) $q->where('s.year', $yr);
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

        // ?visible_now=1 -> only published and currently in window
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
                    })
                    ->whereNull('s.deleted_at');
            }
        }

        // sort
        $sort = (string) $request->query('sort', 'sort_order');
        $dir  = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowed = ['sort_order','created_at','publish_at','expire_at','name','title','views_count','year','date','id'];
        if (! in_array($sort, $allowed, true)) $sort = 'sort_order';

        $q->orderBy('s.' . $sort, $dir);

        // stable secondary
        if ($sort !== 'created_at') $q->orderBy('s.created_at', 'desc');

        return $q;
    }

    protected function resolveStory(Request $request, $identifier, bool $includeDeleted = false, $departmentId = null)
    {
        $q = DB::table('success_stories as s');
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

        // decode social_links_json
        $sl = $arr['social_links_json'] ?? null;
        if (is_string($sl)) {
            $decoded = json_decode($sl, true);
            $arr['social_links_json'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // decode metadata
        $meta = $arr['metadata'] ?? null;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // urls
        $arr['photo_full_url'] = $this->toUrl($arr['photo_url'] ?? null);

        // normalize bool-ish
        if (array_key_exists('is_featured_home', $arr)) {
            $arr['is_featured_home'] = (int) $arr['is_featured_home'] ? 1 : 0;
        }

        return $arr;
    }

    protected function ensureUniqueSlug(string $slug, ?string $ignoreUuid = null): string
    {
        $base = $slug;
        $i = 2;

        while (
            DB::table('success_stories')
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
     | CRUD (Admin)
     |============================================ */

    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

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

        $row = $this->resolveStory($request, $identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Success story not found'], 404);

        // optional: ?inc_view=1
        if (filter_var($request->query('inc_view', false), FILTER_VALIDATE_BOOLEAN)) {
            DB::table('success_stories')->where('id', (int) $row->id)->increment('views_count');
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

        $row = $this->resolveStory($request, $identifier, $includeDeleted, $dept->id);
        if (! $row) return response()->json(['message' => 'Success story not found'], 404);

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

            'name'              => ['required', 'string', 'max:120'],
            'title'             => ['nullable', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:160'],

            'description'       => ['nullable', 'string'],
            'quote'             => ['nullable', 'string', 'max:500'],

            'date'              => ['nullable', 'date'],
            'year'              => ['nullable', 'integer', 'min:1900', 'max:2200'],

            'social_links_json' => ['nullable'], // array or json string ok
            'photo_url'         => ['nullable', 'string', 'max:255'],

            'photo_file'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],

            'is_featured_home'  => ['nullable', 'in:0,1', 'boolean'],
            'sort_order'        => ['nullable', 'integer', 'min:0'],
            'status'            => ['nullable', 'in:draft,published,archived'],
            'publish_at'        => ['nullable', 'date'],
            'expire_at'         => ['nullable', 'date'],
            'metadata'          => ['nullable'],
        ]);

        // slug
        $slug = $this->normSlug($validated['slug'] ?? '');
        if ($slug === '') {
            $base = trim((string) ($validated['title'] ?? ''));
            $slug = $base !== '' ? Str::slug($base, '-') : Str::slug($validated['name'], '-');
        }
        $slug = $this->ensureUniqueSlug($slug);

        // social links normalize
        $social = $request->input('social_links_json', null);
        if (is_string($social)) {
            $decoded = json_decode($social, true);
            if (json_last_error() === JSON_ERROR_NONE) $social = $decoded;
        }

        // metadata normalize
        $metadata = $request->input('metadata', null);
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
        }

        // date/year normalize
        $date = !empty($validated['date']) ? Carbon::parse($validated['date'])->toDateString() : null;
        $year = array_key_exists('year', $validated) && !empty($validated['year'])
            ? (int) $validated['year']
            : ($date ? (int) Carbon::parse($date)->format('Y') : null);

        // photo upload OR keep provided photo_url
        $photoPath = !empty($validated['photo_url']) ? trim((string) $validated['photo_url']) : null;

        $deptKey = !empty($validated['department_id']) ? (string) ((int) $validated['department_id']) : 'global';
        $dirRel  = 'depy_uploads/success_stories/' . $deptKey;

        if ($request->hasFile('photo_file')) {
            $f = $request->file('photo_file');
            if (!$f || !$f->isValid()) {
                $this->logActivity(
                    $request,
                    'create',
                    'success_stories',
                    'success_stories',
                    null,
                    ['photo_file'],
                    null,
                    null,
                    'Create failed: photo upload invalid'
                );
                return response()->json(['success' => false, 'message' => 'Photo upload failed'], 422);
            }
            $meta = $this->uploadFileToPublic($f, $dirRel, $slug . '-photo');
            $photoPath = $meta['path'];
        }

        $uuid = (string) Str::uuid();
        $now  = now();

        $id = DB::table('success_stories')->insertGetId([
            'uuid'             => $uuid,
            'department_id'    => $validated['department_id'] ?? null,
            'slug'             => $slug,

            'name'             => $validated['name'],
            'title'            => $validated['title'] ?? null,
            'description'      => $validated['description'] ?? null,
            'photo_url'        => $photoPath,

            'date'             => $date,
            'year'             => $year,

            'quote'            => $validated['quote'] ?? null,
            'social_links_json'=> $social !== null ? json_encode($social) : null,

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
        ]);

        $row = DB::table('success_stories')->where('id', $id)->first();

        // LOG: create
        $note = 'Created success story';
        $src  = (string) ($request->attributes->get('log_source') ?? '');
        if ($src !== '') $note .= ' (source: ' . $src . ')';

        $newVals = $row ? (array) $row : ['id' => $id, 'uuid' => $uuid, 'slug' => $slug];
        $this->logActivity(
            $request,
            'create',
            'success_stories',
            'success_stories',
            (int) $id,
            array_keys($newVals),
            null,
            $newVals,
            $note
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

        // mark source (avoid duplicate logs; store() will log)
        $request->attributes->set('log_source', 'storeForDepartment');

        $request->merge(['department_id' => (int) $dept->id]);
        return $this->store($request);
    }

    public function update(Request $request, $identifier)
    {
        $row = $this->resolveStory($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Success story not found'], 404);

        $before = (array) $row;

        $validated = $request->validate([
            'department_id'     => ['nullable', 'integer', 'exists:departments,id'],

            'name'              => ['nullable', 'string', 'max:120'],
            'title'             => ['nullable', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:160'],

            'description'       => ['nullable', 'string'],
            'quote'             => ['nullable', 'string', 'max:500'],

            'date'              => ['nullable', 'date'],
            'year'              => ['nullable', 'integer', 'min:1900', 'max:2200'],

            'social_links_json' => ['nullable'],
            'photo_url'         => ['nullable', 'string', 'max:255'],

            'photo_file'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'photo_remove'      => ['nullable', 'in:0,1', 'boolean'],

            'is_featured_home'  => ['nullable', 'in:0,1', 'boolean'],
            'sort_order'        => ['nullable', 'integer', 'min:0'],
            'status'            => ['nullable', 'in:draft,published,archived'],
            'publish_at'        => ['nullable', 'date'],
            'expire_at'         => ['nullable', 'date'],
            'metadata'          => ['nullable'],
        ]);

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        // department id for folder
        $newDeptId = array_key_exists('department_id', $validated)
            ? ($validated['department_id'] !== null ? (int) $validated['department_id'] : null)
            : ($row->department_id !== null ? (int) $row->department_id : null);

        // normal fields
        foreach (['name','title','description','quote','status'] as $k) {
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

        // slug unique
        if (array_key_exists('slug', $validated) && trim((string)$validated['slug']) !== '') {
            $slug = $this->normSlug($validated['slug']);
            if ($slug === '') $slug = (string) ($row->slug ?? '');
            $slug = $this->ensureUniqueSlug($slug, (string) $row->uuid);
            $update['slug'] = $slug;
        }

        // date/year
        if (array_key_exists('date', $validated)) {
            $update['date'] = !empty($validated['date']) ? Carbon::parse($validated['date'])->toDateString() : null;
            // if year not explicitly provided, derive from date (if date is set)
            if (!array_key_exists('year', $validated) && !empty($update['date'])) {
                $update['year'] = (int) Carbon::parse($update['date'])->format('Y');
            }
        }
        if (array_key_exists('year', $validated)) {
            $update['year'] = !empty($validated['year']) ? (int) $validated['year'] : null;
        }

        if (array_key_exists('publish_at', $validated)) {
            $update['publish_at'] = !empty($validated['publish_at']) ? Carbon::parse($validated['publish_at']) : null;
        }
        if (array_key_exists('expire_at', $validated)) {
            $update['expire_at'] = !empty($validated['expire_at']) ? Carbon::parse($validated['expire_at']) : null;
        }

        // social links
        if (array_key_exists('social_links_json', $validated)) {
            $social = $request->input('social_links_json', null);
            if (is_string($social)) {
                $decoded = json_decode($social, true);
                if (json_last_error() === JSON_ERROR_NONE) $social = $decoded;
            }
            $update['social_links_json'] = $social !== null ? json_encode($social) : null;
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
        $dirRel  = 'depy_uploads/success_stories/' . $deptKey;

        // photo remove
        if (filter_var($request->input('photo_remove', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->deletePublicPath($row->photo_url ?? null);
            $update['photo_url'] = null;
        }

        // set photo_url directly
        if (array_key_exists('photo_url', $validated)) {
            $update['photo_url'] = !empty($validated['photo_url']) ? trim((string) $validated['photo_url']) : null;
        }

        // photo replace with upload
        if ($request->hasFile('photo_file')) {
            $f = $request->file('photo_file');
            if (!$f || !$f->isValid()) {
                $this->logActivity(
                    $request,
                    'update',
                    'success_stories',
                    'success_stories',
                    (int) $row->id,
                    ['photo_file'],
                    ['photo_url' => $row->photo_url ?? null],
                    null,
                    'Update failed: photo upload invalid'
                );
                return response()->json(['success' => false, 'message' => 'Photo upload failed'], 422);
            }
            // delete old if local path
            $this->deletePublicPath($row->photo_url ?? null);

            $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'success-story');
            $meta = $this->uploadFileToPublic($f, $dirRel, $useSlug . '-photo');
            $update['photo_url'] = $meta['path'];
        }

        DB::table('success_stories')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('success_stories')->where('id', (int) $row->id)->first();

        // LOG: update (diff based on intended update keys; skip audit keys)
        if ($fresh) {
            $after = (array) $fresh;

            $diffKeys = array_values(array_diff(array_keys($update), ['updated_at', 'updated_at_ip']));
            $changed = [];
            $oldVals = [];
            $newVals = [];

            foreach ($diffKeys as $k) {
                $old = $before[$k] ?? null;
                $new = $after[$k] ?? null;

                // compare as strings for stability (DB values usually strings)
                if ((string) $old !== (string) $new) {
                    $changed[] = $k;
                    $oldVals[$k] = $old;
                    $newVals[$k] = $new;
                }
            }

            // also consider photo_remove flag even if photo_url set to null already handled by above
            if (!empty($changed)) {
                $this->logActivity(
                    $request,
                    'update',
                    'success_stories',
                    'success_stories',
                    (int) $row->id,
                    $changed,
                    $oldVals,
                    $newVals,
                    'Updated success story'
                );
            } else {
                $this->logActivity(
                    $request,
                    'update',
                    'success_stories',
                    'success_stories',
                    (int) $row->id,
                    [],
                    [],
                    [],
                    'Update called but no effective field changes detected'
                );
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function toggleFeatured(Request $request, $identifier)
    {
        $row = $this->resolveStory($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Success story not found'], 404);

        $old = (int) ($row->is_featured_home ?? 0);
        $new = $old ? 0 : 1;

        DB::table('success_stories')->where('id', (int) $row->id)->update([
            'is_featured_home' => $new,
            'updated_at'       => now(),
            'updated_at_ip'    => $request->ip(),
        ]);

        $fresh = DB::table('success_stories')->where('id', (int) $row->id)->first();

        // LOG: update (toggle featured)
        $this->logActivity(
            $request,
            'update',
            'success_stories',
            'success_stories',
            (int) $row->id,
            ['is_featured_home'],
            ['is_featured_home' => $old],
            ['is_featured_home' => $new],
            'Toggled featured flag'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveStory($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        $ts = now();

        DB::table('success_stories')->where('id', (int) $row->id)->update([
            'deleted_at'    => $ts,
            'updated_at'    => $ts,
            'updated_at_ip' => $request->ip(),
        ]);

        // LOG: delete (soft delete)
        $this->logActivity(
            $request,
            'delete',
            'success_stories',
            'success_stories',
            (int) $row->id,
            ['deleted_at'],
            ['deleted_at' => null],
            ['deleted_at' => (string) $ts],
            'Soft deleted success story'
        );

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveStory($request, $identifier, true);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $oldDeleted = (string) $row->deleted_at;

        DB::table('success_stories')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('success_stories')->where('id', (int) $row->id)->first();

        // LOG: restore
        $this->logActivity(
            $request,
            'restore',
            'success_stories',
            'success_stories',
            (int) $row->id,
            ['deleted_at'],
            ['deleted_at' => $oldDeleted],
            ['deleted_at' => null],
            'Restored success story'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveStory($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Success story not found'], 404);

        $before = (array) $row;

        // delete local photo file (if stored as local path)
        $this->deletePublicPath($row->photo_url ?? null);

        DB::table('success_stories')->where('id', (int) $row->id)->delete();

        // LOG: delete (force)
        $this->logActivity(
            $request,
            'delete',
            'success_stories',
            'success_stories',
            (int) $row->id,
            null,
            $before,
            null,
            'Force deleted success story'
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

        // public default sort (featured first + sort_order + latest)
        $q->orderBy('s.is_featured_home', 'desc')
          ->orderBy('s.sort_order', 'asc')
          ->orderByRaw('COALESCE(s.publish_at, s.created_at) desc');

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
        $row = $this->resolveStory($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Success story not found'], 404);

        $now = now();
        $isVisible =
            ($row->status === 'published') &&
            (empty($row->publish_at) || Carbon::parse($row->publish_at)->lte($now)) &&
            (empty($row->expire_at)  || Carbon::parse($row->expire_at)->gt($now));

        if (! $isVisible) {
            return response()->json(['message' => 'Success story not available'], 404);
        }

        // default public view increment (can disable with ?inc_view=0)
        $inc = $request->has('inc_view')
            ? filter_var($request->query('inc_view'), FILTER_VALIDATE_BOOLEAN)
            : true;

        if ($inc) {
            DB::table('success_stories')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }
}
