<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class SuccessfulEntrepreneurController extends Controller
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

    protected function toUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') return null;
        if (preg_match('~^https?://~i', $path)) return $path;
        return url('/' . ltrim($path, '/'));
    }

    protected function deletePublicPath(?string $path): void
    {
        $path = trim((string) $path);
        if ($path === '' || preg_match('~^https?://~i', $path)) return;

        $abs = public_path(ltrim($path, '/'));
        if (is_file($abs)) @unlink($abs);
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

    protected function normalizeRow($row): array
    {
        $arr = (array) $row;

        // decode social_links_json
        $social = $arr['social_links_json'] ?? null;
        if (is_string($social)) {
            $decoded = json_decode($social, true);
            $arr['social_links_json'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // decode metadata
        $meta = $arr['metadata'] ?? null;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // URLs (for paths stored in DB)
        $arr['photo_full_url']        = $this->toUrl($arr['photo_url'] ?? null);
        $arr['company_logo_full_url'] = $this->toUrl($arr['company_logo_url'] ?? null);

        // Optional helper: website URL as-is (don’t force scheme)
        $arr['company_website_url'] = isset($arr['company_website_url'])
            ? (trim((string)$arr['company_website_url']) ?: null)
            : null;

        return $arr;
    }

    protected function ensureUniqueSlug(string $slug, ?string $ignoreUuid = null): string
    {
        $base = $slug;
        $i = 2;

        while (
            DB::table('successful_entrepreneurs')
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

    protected function applyVisibleWindow($q): void
    {
        $now = now();

        $q->whereNull('se.deleted_at')
          ->where('se.status', 'published')
          ->where(function ($w) use ($now) {
              $w->whereNull('se.publish_at')->orWhere('se.publish_at', '<=', $now);
          })
          ->where(function ($w) use ($now) {
              $w->whereNull('se.expire_at')->orWhere('se.expire_at', '>', $now);
          });
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('successful_entrepreneurs as se')
            ->leftJoin('departments as d', 'd.id', '=', 'se.department_id')
            ->select([
                'se.*',
                'd.title as department_title',
                'd.slug  as department_slug',
                'd.uuid  as department_uuid',
            ]);

        if (! $includeDeleted) {
            $q->whereNull('se.deleted_at');
        }

        // ?q=
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('se.name', 'like', $term)
                    ->orWhere('se.slug', 'like', $term)
                    ->orWhere('se.title', 'like', $term)
                    ->orWhere('se.description', 'like', $term)
                    ->orWhere('se.company_name', 'like', $term)
                    ->orWhere('se.industry', 'like', $term);
            });
        }

        // ?status=draft|published|archived
        if ($request->filled('status')) {
            $q->where('se.status', (string) $request->query('status'));
        }

        // ?featured=1/0
        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) {
                $q->where('se.is_featured_home', $featured ? 1 : 0);
            }
        }

        // ?department=id|uuid|slug
        if ($request->filled('department')) {
            $dept = $this->resolveDepartment($request->query('department'), true);
            if ($dept) {
                $q->where('se.department_id', (int) $dept->id);
            } else {
                $q->whereRaw('1=0');
            }
        }

        // ?visible_now=1
        if ($request->has('visible_now')) {
            $visible = filter_var($request->query('visible_now'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($visible) {
                $this->applyVisibleWindow($q);
            }
        }

        // sort
        $sort = (string) $request->query('sort', 'created_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = [
            'created_at', 'publish_at', 'expire_at',
            'name', 'views_count', 'sort_order', 'id',
            'founded_year', 'achievement_date'
        ];
        if (! in_array($sort, $allowed, true)) $sort = 'created_at';

        $q->orderBy('se.' . $sort, $dir);

        return $q;
    }

    protected function resolveEntrepreneur(Request $request, $identifier, bool $includeDeleted = false, $departmentId = null)
    {
        $q = DB::table('successful_entrepreneurs as se');
        if (! $includeDeleted) $q->whereNull('se.deleted_at');

        if ($departmentId !== null) {
            $q->where('se.department_id', (int) $departmentId);
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('se.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('se.uuid', (string) $identifier);
        } else {
            $q->where('se.slug', (string) $identifier);
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
            $query->whereNotNull('se.deleted_at');
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

        $row = $this->resolveEntrepreneur($request, $identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Successful Entrepreneur not found'], 404);

        // ?inc_view=1
        if (filter_var($request->query('inc_view', false), FILTER_VALIDATE_BOOLEAN)) {
            DB::table('successful_entrepreneurs')->where('id', (int) $row->id)->increment('views_count');
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

        $row = $this->resolveEntrepreneur($request, $identifier, $includeDeleted, $dept->id);
        if (! $row) return response()->json(['message' => 'Successful Entrepreneur not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'department_id'        => ['nullable', 'integer', 'exists:departments,id'],
            'user_id'              => ['nullable', 'integer', 'exists:users,id'],

            'name'                 => ['required', 'string', 'max:120'],
            'slug'                 => ['nullable', 'string', 'max:160'],
            'title'                => ['nullable', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'highlights'           => ['nullable', 'string'],

            'photo_url'            => ['nullable', 'string', 'max:255'],
            'company_name'         => ['nullable', 'string', 'max:255'],
            'company_logo_url'     => ['nullable', 'string', 'max:255'],
            'company_website_url'  => ['nullable', 'string', 'max:255'],
            'industry'             => ['nullable', 'string', 'max:120'],

            'founded_year'         => ['nullable', 'integer', 'min:1800', 'max:2500'],
            'achievement_date'     => ['nullable', 'date'],

            'social_links_json'    => ['nullable'], // array or json string
            'is_featured_home'     => ['nullable', 'in:0,1', 'boolean'],
            'sort_order'           => ['nullable', 'integer', 'min:0'],
            'status'               => ['nullable', 'in:draft,published,archived'],
            'publish_at'           => ['nullable', 'date'],
            'expire_at'            => ['nullable', 'date'],
            'metadata'             => ['nullable'],

            // uploads (stored into photo_url/company_logo_url)
            'photo'                => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'company_logo'         => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        $slug = $this->normSlug($validated['slug'] ?? '');
        if ($slug === '') $slug = Str::slug($validated['name'], '-');
        $slug = $this->ensureUniqueSlug($slug);

        $uuid = (string) Str::uuid();
        $now  = now();

        $deptKey = !empty($validated['department_id']) ? (string) ((int) $validated['department_id']) : 'global';
        $dirRel  = 'depy_uploads/successful_entrepreneurs/' . $deptKey;

        // JSON normalize: social_links_json
        $social = $request->input('social_links_json', null);
        if (is_string($social)) {
            $decoded = json_decode($social, true);
            if (json_last_error() === JSON_ERROR_NONE) $social = $decoded;
        }

        // JSON normalize: metadata
        $metadata = $request->input('metadata', null);
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
        }

        // uploads -> paths stored in *_url columns
        $photoPath = !empty($validated['photo_url']) ? trim((string)$validated['photo_url']) : null;
        if ($request->hasFile('photo')) {
            $f = $request->file('photo');
            if (!$f || !$f->isValid()) {
                return response()->json(['success' => false, 'message' => 'Photo upload failed'], 422);
            }
            $meta = $this->uploadFileToPublic($f, $dirRel, $slug . '-photo');
            $photoPath = $meta['path'];
        }

        $logoPath = !empty($validated['company_logo_url']) ? trim((string)$validated['company_logo_url']) : null;
        if ($request->hasFile('company_logo')) {
            $f = $request->file('company_logo');
            if (!$f || !$f->isValid()) {
                return response()->json(['success' => false, 'message' => 'Company logo upload failed'], 422);
            }
            $meta = $this->uploadFileToPublic($f, $dirRel, $slug . '-logo');
            $logoPath = $meta['path'];
        }

        $id = DB::table('successful_entrepreneurs')->insertGetId([
            'uuid'                => $uuid,
            'department_id'       => $validated['department_id'] ?? null,
            'user_id'             => $validated['user_id'] ?? null,

            'slug'                => $slug,
            'name'                => $validated['name'],
            'title'               => $validated['title'] ?? null,
            'description'         => $validated['description'] ?? null,
            'highlights'          => $validated['highlights'] ?? null,

            'photo_url'           => $photoPath,
            'company_name'        => $validated['company_name'] ?? null,
            'company_logo_url'    => $logoPath,
            'company_website_url' => $validated['company_website_url'] ?? null,
            'industry'            => $validated['industry'] ?? null,

            'founded_year'        => array_key_exists('founded_year', $validated) ? $validated['founded_year'] : null,
            'achievement_date'    => !empty($validated['achievement_date']) ? Carbon::parse($validated['achievement_date']) : null,

            'social_links_json'   => $social !== null ? json_encode($social) : null,
            'is_featured_home'    => (int) ($validated['is_featured_home'] ?? 0),
            'sort_order'          => (int) ($validated['sort_order'] ?? 0),
            'status'              => (string) ($validated['status'] ?? 'draft'),
            'publish_at'          => !empty($validated['publish_at']) ? Carbon::parse($validated['publish_at']) : null,
            'expire_at'           => !empty($validated['expire_at']) ? Carbon::parse($validated['expire_at']) : null,

            'views_count'         => 0,
            'created_by'          => $actor['id'] ?: null,
            'created_at'          => $now,
            'updated_at'          => $now,
            'created_at_ip'       => $request->ip(),
            'updated_at_ip'       => $request->ip(),
            'metadata'            => $metadata !== null ? json_encode($metadata) : null,
        ]);

        $row = DB::table('successful_entrepreneurs')->where('id', $id)->first();

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
        $row = $this->resolveEntrepreneur($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Successful Entrepreneur not found'], 404);

        $validated = $request->validate([
            'department_id'         => ['nullable', 'integer', 'exists:departments,id'],
            'user_id'               => ['nullable', 'integer', 'exists:users,id'],

            'name'                  => ['nullable', 'string', 'max:120'],
            'slug'                  => ['nullable', 'string', 'max:160'],
            'title'                 => ['nullable', 'string', 'max:255'],
            'description'           => ['nullable', 'string'],
            'highlights'            => ['nullable', 'string'],

            'photo_url'             => ['nullable', 'string', 'max:255'],
            'company_name'          => ['nullable', 'string', 'max:255'],
            'company_logo_url'      => ['nullable', 'string', 'max:255'],
            'company_website_url'   => ['nullable', 'string', 'max:255'],
            'industry'              => ['nullable', 'string', 'max:120'],

            'founded_year'          => ['nullable', 'integer', 'min:1800', 'max:2500'],
            'achievement_date'      => ['nullable', 'date'],

            'social_links_json'     => ['nullable'],
            'is_featured_home'      => ['nullable', 'in:0,1', 'boolean'],
            'sort_order'            => ['nullable', 'integer', 'min:0'],
            'status'                => ['nullable', 'in:draft,published,archived'],
            'publish_at'            => ['nullable', 'date'],
            'expire_at'             => ['nullable', 'date'],
            'metadata'              => ['nullable'],

            // uploads (replace)
            'photo'                 => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'company_logo'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],

            // remove flags
            'photo_remove'          => ['nullable', 'in:0,1', 'boolean'],
            'company_logo_remove'   => ['nullable', 'in:0,1', 'boolean'],
        ]);

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        // basic fields
        $fields = [
            'department_id','user_id',
            'name','title','description','highlights',
            'company_name','company_website_url','industry',
            'status'
        ];

        foreach ($fields as $k) {
            if (array_key_exists($k, $validated)) {
                $update[$k] = $validated[$k];
            }
        }

        // founded_year
        if (array_key_exists('founded_year', $validated)) {
            $update['founded_year'] = $validated['founded_year'] !== null ? (int)$validated['founded_year'] : null;
        }

        // achievement_date
        if (array_key_exists('achievement_date', $validated)) {
            $update['achievement_date'] = !empty($validated['achievement_date'])
                ? Carbon::parse($validated['achievement_date'])
                : null;
        }

        // featured / sort_order
        if (array_key_exists('is_featured_home', $validated)) {
            $update['is_featured_home'] = (int) $validated['is_featured_home'];
        }
        if (array_key_exists('sort_order', $validated)) {
            $update['sort_order'] = (int) $validated['sort_order'];
        }

        // publish/expire
        if (array_key_exists('publish_at', $validated)) {
            $update['publish_at'] = !empty($validated['publish_at']) ? Carbon::parse($validated['publish_at']) : null;
        }
        if (array_key_exists('expire_at', $validated)) {
            $update['expire_at'] = !empty($validated['expire_at']) ? Carbon::parse($validated['expire_at']) : null;
        }

        // slug
        if (array_key_exists('slug', $validated) && trim((string)$validated['slug']) !== '') {
            $slug = $this->normSlug($validated['slug']);
            if ($slug === '') $slug = (string) ($row->slug ?? '');
            $slug = $this->ensureUniqueSlug($slug, (string) $row->uuid);
            $update['slug'] = $slug;
        }

        $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'entrepreneur');

        // social_links_json
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

        // dir for uploads
        $deptId = array_key_exists('department_id', $validated)
            ? ($validated['department_id'] !== null ? (int)$validated['department_id'] : null)
            : (!empty($row->department_id) ? (int)$row->department_id : null);

        $deptKey = $deptId ? (string)$deptId : 'global';
        $dirRel  = 'depy_uploads/successful_entrepreneurs/' . $deptKey;

        // photo remove
        if (filter_var($request->input('photo_remove', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->deletePublicPath($row->photo_url ?? null);
            $update['photo_url'] = null;
        }

        // logo remove
        if (filter_var($request->input('company_logo_remove', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->deletePublicPath($row->company_logo_url ?? null);
            $update['company_logo_url'] = null;
        }

        // photo replace by URL string
        if (array_key_exists('photo_url', $validated)) {
            $update['photo_url'] = trim((string) $validated['photo_url']) ?: null;
        }

        // logo replace by URL string
        if (array_key_exists('company_logo_url', $validated)) {
            $update['company_logo_url'] = trim((string) $validated['company_logo_url']) ?: null;
        }

        // photo upload (overrides)
        if ($request->hasFile('photo')) {
            $f = $request->file('photo');
            if (!$f || !$f->isValid()) {
                return response()->json(['success' => false, 'message' => 'Photo upload failed'], 422);
            }
            $this->deletePublicPath($row->photo_url ?? null);
            $meta = $this->uploadFileToPublic($f, $dirRel, $useSlug . '-photo');
            $update['photo_url'] = $meta['path'];
        }

        // logo upload (overrides)
        if ($request->hasFile('company_logo')) {
            $f = $request->file('company_logo');
            if (!$f || !$f->isValid()) {
                return response()->json(['success' => false, 'message' => 'Company logo upload failed'], 422);
            }
            $this->deletePublicPath($row->company_logo_url ?? null);
            $meta = $this->uploadFileToPublic($f, $dirRel, $useSlug . '-logo');
            $update['company_logo_url'] = $meta['path'];
        }

        DB::table('successful_entrepreneurs')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('successful_entrepreneurs')->where('id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function toggleFeatured(Request $request, $identifier)
    {
        $row = $this->resolveEntrepreneur($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Successful Entrepreneur not found'], 404);

        $new = ((int) ($row->is_featured_home ?? 0)) ? 0 : 1;

        DB::table('successful_entrepreneurs')->where('id', (int) $row->id)->update([
            'is_featured_home' => $new,
            'updated_at'       => now(),
            'updated_at_ip'    => $request->ip(),
        ]);

        $fresh = DB::table('successful_entrepreneurs')->where('id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveEntrepreneur($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        DB::table('successful_entrepreneurs')->where('id', (int) $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveEntrepreneur($request, $identifier, true);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        DB::table('successful_entrepreneurs')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('successful_entrepreneurs')->where('id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveEntrepreneur($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Successful Entrepreneur not found'], 404);

        // delete local files (only if stored as relative paths)
        $this->deletePublicPath($row->photo_url ?? null);
        $this->deletePublicPath($row->company_logo_url ?? null);

        DB::table('successful_entrepreneurs')->where('id', (int) $row->id)->delete();

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
        $q->orderByRaw('COALESCE(se.publish_at, se.created_at) desc');

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
        $row = $this->resolveEntrepreneur($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Successful Entrepreneur not found'], 404);

        $now = now();
        $isVisible =
            ($row->status === 'published') &&
            (empty($row->publish_at) || Carbon::parse($row->publish_at)->lte($now)) &&
            (empty($row->expire_at)  || Carbon::parse($row->expire_at)->gt($now));

        if (! $isVisible) {
            return response()->json(['message' => 'Successful Entrepreneur not available'], 404);
        }

        // default public view increment (can disable with ?inc_view=0)
        $inc = $request->has('inc_view')
            ? filter_var($request->query('inc_view'), FILTER_VALIDATE_BOOLEAN)
            : true;

        if ($inc) {
            DB::table('successful_entrepreneurs')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }
}
