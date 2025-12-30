<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PlacementNoticeController extends Controller
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

    protected function resolveRecruiter($identifier, bool $includeDeleted = false)
    {
        if (!Schema::hasTable('recruiters')) return null;

        $q = DB::table('recruiters');
        if (Schema::hasColumn('recruiters', 'deleted_at') && ! $includeDeleted) {
            $q->whereNull('deleted_at');
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier) && Schema::hasColumn('recruiters', 'uuid')) {
            $q->where('uuid', (string) $identifier);
        } elseif (Schema::hasColumn('recruiters', 'slug')) {
            $q->where('slug', (string) $identifier);
        } else {
            // fallback: try name if available
            if (Schema::hasColumn('recruiters', 'name')) $q->where('name', (string) $identifier);
            else return null;
        }

        return $q->first();
    }

    protected function recruiterSelect(): array
    {
        // Pick safe columns only if they exist
        if (!Schema::hasTable('recruiters')) return [];

        $cols = ['id'];
        foreach (['name','title','company_name','slug','uuid'] as $c) {
            if (Schema::hasColumn('recruiters', $c)) $cols[] = $c;
        }

        // always alias to avoid collisions
        $out = [];
        foreach ($cols as $c) {
            $out[] = "r.$c as recruiter_$c";
        }
        return $out;
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('placement_notices as p')
            ->leftJoin('departments as d', 'd.id', '=', 'p.department_id');

        // recruiter join only if exists
        if (Schema::hasTable('recruiters')) {
            $q->leftJoin('recruiters as r', 'r.id', '=', 'p.recruiter_id');
        }

        $select = array_merge([
            'p.*',
            'd.title as department_title',
            'd.slug  as department_slug',
            'd.uuid  as department_uuid',
        ], $this->recruiterSelect());

        $q->select($select);

        if (! $includeDeleted) {
            $q->whereNull('p.deleted_at');
        }

        // ?q=
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('p.title', 'like', $term)
                    ->orWhere('p.slug', 'like', $term)
                    ->orWhere('p.description', 'like', $term)
                    ->orWhere('p.eligibility', 'like', $term)
                    ->orWhere('p.role_title', 'like', $term);
            });
        }

        // ?status=draft|published|archived
        if ($request->filled('status')) {
            $q->where('p.status', (string) $request->query('status'));
        }

        // ?featured=1/0
        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) {
                $q->where('p.is_featured_home', $featured ? 1 : 0);
            }
        }

        // ?department=id|uuid|slug
        if ($request->filled('department')) {
            $dept = $this->resolveDepartment($request->query('department'), true);
            if ($dept) $q->where('p.department_id', (int) $dept->id);
            else $q->whereRaw('1=0');
        }

        // ?recruiter=id|uuid|slug
        if ($request->filled('recruiter')) {
            $rec = $this->resolveRecruiter($request->query('recruiter'), true);
            if ($rec) $q->where('p.recruiter_id', (int) $rec->id);
            else $q->whereRaw('1=0');
        }

        // ?visible_now=1 -> only published & publish/expire window
        if ($request->has('visible_now')) {
            $visible = filter_var($request->query('visible_now'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($visible) {
                $now = now();
                $q->where('p.status', 'published')
                  ->where(function ($w) use ($now) {
                      $w->whereNull('p.publish_at')->orWhere('p.publish_at', '<=', $now);
                  })
                  ->where(function ($w) use ($now) {
                      $w->whereNull('p.expire_at')->orWhere('p.expire_at', '>', $now);
                  });
            }
        }

        // sort
        $sort = (string) $request->query('sort', 'created_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['created_at','publish_at','expire_at','last_date_to_apply','title','views_count','sort_order','id'];
        if (!in_array($sort, $allowed, true)) $sort = 'created_at';

        $q->orderBy('p.' . $sort, $dir);

        return $q;
    }

    protected function resolvePlacementNotice(Request $request, $identifier, bool $includeDeleted = false, $departmentId = null)
    {
        $q = DB::table('placement_notices as p');
        if (! $includeDeleted) $q->whereNull('p.deleted_at');

        if ($departmentId !== null) {
            $q->where('p.department_id', (int) $departmentId);
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('p.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('p.uuid', (string) $identifier);
        } else {
            $q->where('p.slug', (string) $identifier);
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

        // attach recruiter (best-effort)
        if (!empty($row->recruiter_id) && Schema::hasTable('recruiters')) {
            $rec = DB::table('recruiters')->where('id', (int) $row->recruiter_id)->first();
            if ($rec) {
                foreach (['id','name','title','company_name','slug','uuid'] as $k) {
                    if (isset($rec->$k)) $row->{'recruiter_'.$k} = $rec->$k;
                }
            }
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

        // decode metadata
        $meta = $arr['metadata'] ?? null;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // banner url normalize
        $arr['banner_image_full_url'] = $this->toUrl($arr['banner_image_url'] ?? null);

        return $arr;
    }

    protected function ensureUniqueSlug(string $slug, ?string $ignoreUuid = null): string
    {
        $base = $slug;
        $i = 2;

        while (
            DB::table('placement_notices')
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

        $q->whereNull('p.deleted_at')
          ->where('p.status', 'published')
          ->where(function ($w) use ($now) {
              $w->whereNull('p.publish_at')->orWhere('p.publish_at', '<=', $now);
          })
          ->where(function ($w) use ($now) {
              $w->whereNull('p.expire_at')->orWhere('p.expire_at', '>', $now);
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

        if ($onlyDeleted) $query->whereNotNull('p.deleted_at');

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

        $row = $this->resolvePlacementNotice($request, $identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Placement notice not found'], 404);

        // optional: ?inc_view=1
        if (filter_var($request->query('inc_view', false), FILTER_VALIDATE_BOOLEAN)) {
            DB::table('placement_notices')->where('id', (int) $row->id)->increment('views_count');
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

        $row = $this->resolvePlacementNotice($request, $identifier, $includeDeleted, $dept->id);
        if (! $row) return response()->json(['message' => 'Placement notice not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'department_id'       => ['nullable', 'integer', 'exists:departments,id'],
            'recruiter_id'        => ['nullable', 'integer', 'exists:recruiters,id'],

            'title'               => ['required', 'string', 'max:255'],
            'slug'                => ['nullable', 'string', 'max:160'],

            'description'         => ['nullable', 'string'],
            'role_title'          => ['nullable', 'string', 'max:255'],
            'ctc'                 => ['nullable', 'numeric'],
            'eligibility'         => ['nullable', 'string'],
            'apply_url'           => ['nullable', 'string', 'max:255'],
            'last_date_to_apply'  => ['nullable', 'date'],

            'is_featured_home'    => ['nullable', 'in:0,1', 'boolean'],
            'sort_order'          => ['nullable', 'integer', 'min:0'],
            'status'              => ['nullable', 'in:draft,published,archived'],
            'publish_at'          => ['nullable', 'date'],
            'expire_at'           => ['nullable', 'date'],
            'metadata'            => ['nullable'],

            'banner_image'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'banner_image_url'    => ['nullable', 'string', 'max:255'], // if you want to set manually without upload
        ]);

        $slug = $this->normSlug($validated['slug'] ?? '');
        if ($slug === '') $slug = Str::slug($validated['title'], '-');
        $slug = $this->ensureUniqueSlug($slug);

        $uuid = (string) Str::uuid();
        $now  = now();

        // metadata normalize
        $metadata = $request->input('metadata', null);
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
        }

        // banner upload OR manual banner_image_url
        $bannerPath = null;

        if ($request->hasFile('banner_image')) {
            $deptKey = !empty($validated['department_id']) ? (string) ((int) $validated['department_id']) : 'global';
            $dirRel  = 'depy_uploads/placement_notices/' . $deptKey;

            $f = $request->file('banner_image');
            if (!$f || !$f->isValid()) {
                return response()->json(['success' => false, 'message' => 'Banner image upload failed'], 422);
            }

            $meta = $this->uploadFileToPublic($f, $dirRel, $slug . '-banner');
            $bannerPath = $meta['path'];
        } elseif (!empty($validated['banner_image_url'])) {
            $bannerPath = (string) $validated['banner_image_url'];
        }

        $id = DB::table('placement_notices')->insertGetId([
            'uuid'              => $uuid,
            'department_id'     => $validated['department_id'] ?? null,
            'recruiter_id'      => $validated['recruiter_id'] ?? null,
            'slug'              => $slug,
            'title'             => $validated['title'],

            'description'       => $validated['description'] ?? null,
            'banner_image_url'  => $bannerPath,
            'role_title'        => $validated['role_title'] ?? null,
            'ctc'               => array_key_exists('ctc', $validated) ? $validated['ctc'] : null,
            'eligibility'       => $validated['eligibility'] ?? null,
            'apply_url'         => $validated['apply_url'] ?? null,
            'last_date_to_apply'=> !empty($validated['last_date_to_apply']) ? Carbon::parse($validated['last_date_to_apply'])->toDateString() : null,

            'is_featured_home'  => (int) ($validated['is_featured_home'] ?? 0),
            'sort_order'        => (int) ($validated['sort_order'] ?? 0),
            'status'            => (string) ($validated['status'] ?? 'draft'),
            'publish_at'        => !empty($validated['publish_at']) ? Carbon::parse($validated['publish_at']) : null,
            'expire_at'         => !empty($validated['expire_at']) ? Carbon::parse($validated['expire_at']) : null,

            'views_count'       => 0,
            'created_by'        => $actor['id'] ?: null,

            'created_at'        => $now,
            'updated_at'        => $now,
            'created_at_ip'     => $request->ip(),
            'updated_at_ip'     => $request->ip(),

            'metadata'          => $metadata !== null ? json_encode($metadata) : null,
        ]);

        $row = DB::table('placement_notices')->where('id', $id)->first();

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
        $row = $this->resolvePlacementNotice($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Placement notice not found'], 404);

        $validated = $request->validate([
            'department_id'        => ['nullable', 'integer', 'exists:departments,id'],
            'recruiter_id'         => ['nullable', 'integer', 'exists:recruiters,id'],

            'title'                => ['nullable', 'string', 'max:255'],
            'slug'                 => ['nullable', 'string', 'max:160'],

            'description'          => ['nullable', 'string'],
            'role_title'           => ['nullable', 'string', 'max:255'],
            'ctc'                  => ['nullable', 'numeric'],
            'eligibility'          => ['nullable', 'string'],
            'apply_url'            => ['nullable', 'string', 'max:255'],
            'last_date_to_apply'   => ['nullable', 'date'],

            'is_featured_home'     => ['nullable', 'in:0,1', 'boolean'],
            'sort_order'           => ['nullable', 'integer', 'min:0'],
            'status'               => ['nullable', 'in:draft,published,archived'],
            'publish_at'           => ['nullable', 'date'],
            'expire_at'            => ['nullable', 'date'],
            'metadata'             => ['nullable'],

            'banner_image'         => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'banner_image_remove'  => ['nullable', 'in:0,1', 'boolean'],

            'banner_image_url'     => ['nullable', 'string', 'max:255'], // manual override
        ]);

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        // dept key for directory
        $newDeptId = array_key_exists('department_id', $validated)
            ? ($validated['department_id'] !== null ? (int) $validated['department_id'] : null)
            : ($row->department_id !== null ? (int) $row->department_id : null);

        // normal fields
        foreach ([
            'title','description','role_title','eligibility','apply_url','status'
        ] as $k) {
            if (array_key_exists($k, $validated)) $update[$k] = $validated[$k];
        }

        if (array_key_exists('department_id', $validated)) {
            $update['department_id'] = $validated['department_id'] !== null ? (int) $validated['department_id'] : null;
        }
        if (array_key_exists('recruiter_id', $validated)) {
            $update['recruiter_id'] = $validated['recruiter_id'] !== null ? (int) $validated['recruiter_id'] : null;
        }
        if (array_key_exists('ctc', $validated)) {
            $update['ctc'] = $validated['ctc'];
        }
        if (array_key_exists('sort_order', $validated)) {
            $update['sort_order'] = (int) $validated['sort_order'];
        }
        if (array_key_exists('is_featured_home', $validated)) {
            $update['is_featured_home'] = (int) $validated['is_featured_home'];
        }
        if (array_key_exists('last_date_to_apply', $validated)) {
            $update['last_date_to_apply'] = !empty($validated['last_date_to_apply'])
                ? Carbon::parse($validated['last_date_to_apply'])->toDateString()
                : null;
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

        // banner remove
        if (filter_var($request->input('banner_image_remove', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->deletePublicPath($row->banner_image_url ?? null);
            $update['banner_image_url'] = null;
        }

        // manual banner override (if provided and no file upload)
        if (array_key_exists('banner_image_url', $validated) && !$request->hasFile('banner_image')) {
            $update['banner_image_url'] = $validated['banner_image_url'] !== '' ? $validated['banner_image_url'] : null;
        }

        // banner replace via upload
        if ($request->hasFile('banner_image')) {
            $deptKey = $newDeptId ? (string) $newDeptId : 'global';
            $dirRel  = 'depy_uploads/placement_notices/' . $deptKey;

            $f = $request->file('banner_image');
            if (!$f || !$f->isValid()) {
                return response()->json(['success' => false, 'message' => 'Banner image upload failed'], 422);
            }

            $this->deletePublicPath($row->banner_image_url ?? null);

            $useSlug = (string) ($update['slug'] ?? $row->slug ?? 'placement-notice');
            $meta = $this->uploadFileToPublic($f, $dirRel, $useSlug . '-banner');
            $update['banner_image_url'] = $meta['path'];
        }

        DB::table('placement_notices')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('placement_notices')->where('id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function toggleFeatured(Request $request, $identifier)
    {
        $row = $this->resolvePlacementNotice($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Placement notice not found'], 404);

        $new = ((int) ($row->is_featured_home ?? 0)) ? 0 : 1;

        DB::table('placement_notices')->where('id', (int) $row->id)->update([
            'is_featured_home' => $new,
            'updated_at'       => now(),
            'updated_at_ip'    => $request->ip(),
        ]);

        $fresh = DB::table('placement_notices')->where('id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolvePlacementNotice($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        DB::table('placement_notices')->where('id', (int) $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolvePlacementNotice($request, $identifier, true);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        DB::table('placement_notices')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('placement_notices')->where('id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolvePlacementNotice($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Placement notice not found'], 404);

        // delete banner file (if it is a local path)
        $this->deletePublicPath($row->banner_image_url ?? null);

        DB::table('placement_notices')->where('id', (int) $row->id)->delete();

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
        $q->orderByRaw('COALESCE(p.publish_at, p.created_at) desc');

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
        $row = $this->resolvePlacementNotice($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Placement notice not found'], 404);

        $now = now();
        $isVisible =
            ($row->status === 'published') &&
            (empty($row->publish_at) || Carbon::parse($row->publish_at)->lte($now)) &&
            (empty($row->expire_at)  || Carbon::parse($row->expire_at)->gt($now));

        if (! $isVisible) {
            return response()->json(['message' => 'Placement notice not available'], 404);
        }

        // default public view increment (can disable with ?inc_view=0)
        $inc = $request->has('inc_view')
            ? filter_var($request->query('inc_view'), FILTER_VALIDATE_BOOLEAN)
            : true;

        if ($inc) {
            DB::table('placement_notices')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }
}
