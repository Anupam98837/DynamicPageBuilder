<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DepartmentPageController extends Controller
{
    /* ============================================
     | Helpers
     |============================================ */

    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
            'type' => $request->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    private function normSlug(?string $s): string
    {
        $s = (string) $s;
        $s = trim($s);
        $s = $s === '' ? '' : Str::slug($s, '-');
        return $s;
    }

    /** Ensure slug is globally unique (optionally ignoring self) */
    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base !== '' ? $base : 'page';
        $try  = $slug;
        $i    = 2;

        while (true) {
            $q = DB::table('department_pages')->where('slug', $try);
            if ($ignoreId) {
                $q->where('id', '!=', $ignoreId);
            }
            if (! $q->exists()) {
                return $try;
            }

            $try = $slug . '-' . $i;
            $i++;

            if ($i > 200) {
                $try = $slug . '-' . Str::lower(Str::random(4));
                $q = DB::table('department_pages')->where('slug', $try);
                if ($ignoreId) {
                    $q->where('id', '!=', $ignoreId);
                }
                if (! $q->exists()) {
                    return $try;
                }
            }
        }
    }

    /**
     * Resolve page by id / uuid / slug.
     */
    private function resolvePage($identifier, bool $includeDeleted = false)
    {
        $query = DB::table('department_pages');
        if (! $includeDeleted) {
            $query->whereNull('deleted_at');
        }

        if (ctype_digit((string) $identifier)) {
            $query->where('id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $query->where('uuid', (string) $identifier);
        } else {
            $query->where('slug', $this->normSlug((string) $identifier));
        }

        return $query->first();
    }

    /* ============================================
     | List / Trash / Resolve
     |============================================ */

    public function index(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));
        $per  = min(100, max(5, (int) $request->query('per_page', 20)));
        $q    = trim((string) $request->query('q', ''));

        $status         = $request->query('status', null);
        $pageType       = $request->query('page_type', null);
        $layoutKey      = $request->query('layout_key', null);
        $onlyIncludable = (int) $request->query('only_includable', 0) === 1;
        $publishedParam = $request->query('published', null); // '1' => published, '0' => not-yet

        $sort      = (string) $request->query('sort', 'created_at');
        $direction = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSort = ['created_at', 'updated_at', 'title', 'slug', 'published_at'];
        if (! in_array($sort, $allowedSort, true)) {
            $sort = 'created_at';
        }

        $base = DB::table('department_pages')
            ->whereNull('deleted_at');

        if ($q !== '') {
            $base->where(function ($x) use ($q) {
                $x->where('title', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('shortcode', 'like', "%{$q}%")
                    ->orWhere('includable_id', 'like', "%{$q}%");
            });
        }

        if ($status !== null && $status !== '') {
            $base->where('status', $status);
        }

        if ($pageType !== null && $pageType !== '') {
            $base->where('page_type', $pageType);
        }

        if ($layoutKey !== null && $layoutKey !== '') {
            $base->where('layout_key', $layoutKey);
        }

        if ($onlyIncludable) {
            $base->whereNotNull('includable_id');
        }

        $now = Carbon::now();
        if ($publishedParam !== null) {
            if ((string) $publishedParam === '1') {
                $base->whereNotNull('published_at')
                    ->where('published_at', '<=', $now);
            } elseif ((string) $publishedParam === '0') {
                $base->where(function ($q2) use ($now) {
                    $q2->whereNull('published_at')
                        ->orWhere('published_at', '>', $now);
                });
            }
        }

        $total = (clone $base)->count();
        $rows = $base->orderBy($sort, $direction)
            ->orderBy('id', 'asc')
            ->forPage($page, $per)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
            'pagination' => [
                'page'     => $page,
                'per_page' => $per,
                'total'    => $total,
            ],
        ]);
    }

    public function indexTrash(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));
        $per  = min(100, max(5, (int) $request->query('per_page', 20)));
        $q    = trim((string) $request->query('q', ''));

        $base = DB::table('department_pages')
            ->whereNotNull('deleted_at');

        if ($q !== '') {
            $base->where(function ($x) use ($q) {
                $x->where('title', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('shortcode', 'like', "%{$q}%")
                    ->orWhere('includable_id', 'like', "%{$q}%");
            });
        }

        $total = (clone $base)->count();
        $rows = $base->orderBy('deleted_at', 'desc')
            ->orderBy('id', 'asc')
            ->forPage($page, $per)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
            'pagination' => [
                'page'     => $page,
                'per_page' => $per,
                'total'    => $total,
            ],
        ]);
    }

    /**
     * Resolve by slug for frontend rendering.
     */
    public function resolve(Request $request)
    {
        $slug = $this->normSlug($request->query('slug', ''));
        if ($slug === '') {
            return response()->json(['error' => 'Missing slug'], 422);
        }

        $now = Carbon::now();

        $page = DB::table('department_pages')
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->where('status', 'Active')
            ->where(function ($q) use ($now) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', $now);
            })
            ->first();

        if (! $page) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $page,
        ]);
    }

    /* ============================================
     | CRUD
     |============================================ */

    public function show(Request $request, $identifier)
    {
        $page = $this->resolvePage($identifier, false);
        if (! $page) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $page]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'slug'             => 'sometimes|nullable|string|max:200',
            'shortcode'        => 'required|string|max:12',
            'page_type'        => 'sometimes|string|max:30',
            'content_html'     => 'sometimes|nullable|string',
            'includable_id'    => 'sometimes|nullable|string|max:120',
            'layout_key'       => 'sometimes|nullable|string|max:100',
            'meta_description' => 'sometimes|nullable|string|max:255',
            'status'           => 'sometimes|string|max:20',
            'published_at'     => 'sometimes|nullable|date',
        ]);

        $slugBase = $this->normSlug($data['slug'] ?? $data['title'] ?? '');
        $slug     = $this->uniqueSlug($slugBase);

        $includableId = array_key_exists('includable_id', $data)
            ? ($data['includable_id'] ?: null)
            : null;

        if ($includableId !== null) {
            $exists = DB::table('department_pages')
                ->where('includable_id', $includableId)
                ->exists();
            if ($exists) {
                return response()->json(['error' => 'includable_id already exists'], 422);
            }
        }

        $pageType  = $data['page_type'] ?? 'page';
        $status    = $data['status'] ?? 'Active';
        $shortcode = $data['shortcode'];

        $publishedAt = null;
        if (array_key_exists('published_at', $data) && $data['published_at']) {
            $publishedAt = Carbon::parse($data['published_at']);
        }

        $actor = $this->actor($request);
        $now   = Carbon::now();
        $ip    = $request->ip();

        $id = DB::table('department_pages')->insertGetId([
            'uuid'               => (string) Str::uuid(),
            'slug'               => $slug,
            'title'              => $data['title'],
            'shortcode'          => $shortcode,
            'page_type'          => $pageType,
            'content_html'       => $data['content_html'] ?? null,
            'includable_id'      => $includableId,
            'layout_key'         => $data['layout_key'] ?? null,
            'meta_description'   => $data['meta_description'] ?? null,
            'status'             => $status,
            'published_at'       => $publishedAt,
            'created_by_user_id' => $actor['id'] ?: null,
            'updated_by_user_id' => $actor['id'] ?: null,
            'created_at_ip'      => $ip,
            'created_at'         => $now,
            'updated_at'         => $now,
            'deleted_at'         => null,
        ]);

        $row = DB::table('department_pages')->where('id', $id)->first();

        return response()->json(['success' => true, 'data' => $row], 201);
    }

    public function update(Request $request, $identifier)
    {
        $page = $this->resolvePage($identifier, false);
        if (! $page) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $data = $request->validate([
            'title'            => 'sometimes|string|max:200',
            'slug'             => 'sometimes|nullable|string|max:200',
            'shortcode'        => 'sometimes|string|max:12',
            'page_type'        => 'sometimes|string|max:30',
            'content_html'     => 'sometimes|nullable|string',
            'includable_id'    => 'sometimes|nullable|string|max:120',
            'layout_key'       => 'sometimes|nullable|string|max:100',
            'meta_description' => 'sometimes|nullable|string|max:255',
            'status'           => 'sometimes|string|max:20',
            'published_at'     => 'sometimes|nullable|date',
            'regenerate_slug'  => 'sometimes|boolean',
        ]);

        // Slug handling
        $slug = $page->slug;
        if (array_key_exists('slug', $data)) {
            $norm = $this->normSlug($data['slug']);
            if ($norm === '' || (! empty($data['regenerate_slug']))) {
                $base = $this->normSlug($data['title'] ?? $page->title ?? 'page');
                $slug = $this->uniqueSlug($base, (int) $page->id);
            } else {
                $slug = $this->uniqueSlug($norm, (int) $page->id);
            }
        } elseif (! empty($data['regenerate_slug']) || (isset($data['title']) && $data['title'] !== $page->title)) {
            $base = $this->normSlug($data['title'] ?? $page->title ?? 'page');
            $slug = $this->uniqueSlug($base, (int) $page->id);
        }

        // Includable id handling
        $includableId = array_key_exists('includable_id', $data)
            ? ($data['includable_id'] ?: null)
            : $page->includable_id;

        if ($includableId !== null) {
            $exists = DB::table('department_pages')
                ->where('includable_id', $includableId)
                ->where('id', '!=', $page->id)
                ->exists();
            if ($exists) {
                return response()->json(['error' => 'includable_id already exists'], 422);
            }
        }

        // Published_at handling
        if (array_key_exists('published_at', $data)) {
            if ($data['published_at'] === null || $data['published_at'] === '') {
                $publishedAt = null;
            } else {
                $publishedAt = Carbon::parse($data['published_at']);
            }
        } else {
            $publishedAt = $page->published_at;
        }

        $actor = $this->actor($request);

        $update = [
            'title'            => $data['title'] ?? $page->title,
            'slug'             => $slug,
            'shortcode'        => array_key_exists('shortcode', $data) ? $data['shortcode'] : $page->shortcode,
            'page_type'        => array_key_exists('page_type', $data) ? $data['page_type'] : $page->page_type,
            'content_html'     => array_key_exists('content_html', $data) ? $data['content_html'] : $page->content_html,
            'includable_id'    => $includableId,
            'layout_key'       => array_key_exists('layout_key', $data) ? $data['layout_key'] : $page->layout_key,
            'meta_description' => array_key_exists('meta_description', $data) ? $data['meta_description'] : $page->meta_description,
            'status'           => array_key_exists('status', $data) ? $data['status'] : $page->status,
            'published_at'     => $publishedAt,
            'updated_at'       => Carbon::now(),
            'updated_by_user_id' => $actor['id'] ?: null,
        ];

        DB::table('department_pages')->where('id', $page->id)->update($update);

        $fresh = DB::table('department_pages')->where('id', $page->id)->first();

        return response()->json(['success' => true, 'data' => $fresh]);
    }

    public function destroy(Request $request, $identifier)
    {
        $page = $this->resolvePage($identifier, false);
        if (! $page) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $actor = $this->actor($request);

        DB::table('department_pages')
            ->where('id', $page->id)
            ->update([
                'deleted_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
                'updated_by_user_id'=> $actor['id'] ?: null,
            ]);

        return response()->json(['success' => true, 'message' => 'Moved to bin']);
    }

    public function restore(Request $request, $identifier)
    {
        $page = $this->resolvePage($identifier, true);
        if (! $page || $page->deleted_at === null) {
            return response()->json(['error' => 'Not found in bin'], 404);
        }

        $actor = $this->actor($request);

        DB::table('department_pages')
            ->where('id', $page->id)
            ->update([
                'deleted_at'        => null,
                'updated_at'        => Carbon::now(),
                'updated_by_user_id'=> $actor['id'] ?: null,
            ]);

        return response()->json(['success' => true, 'message' => 'Restored']);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $page = $this->resolvePage($identifier, true);
        if (! $page) {
            return response()->json(['error' => 'Not found'], 404);
        }

        DB::table('department_pages')
            ->where('id', $page->id)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Deleted permanently']);
    }

    /**
     * Quick toggle between Active / Inactive status.
     */
    public function toggleStatus(Request $request, $identifier)
    {
        $page = $this->resolvePage($identifier, false);
        if (! $page) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $actor = $this->actor($request);
        $newStatus = ($page->status === 'Active') ? 'Inactive' : 'Active';

        DB::table('department_pages')
            ->where('id', $page->id)
            ->update([
                'status'            => $newStatus,
                'updated_at'        => Carbon::now(),
                'updated_by_user_id'=> $actor['id'] ?: null,
            ]);

        return response()->json(['success' => true, 'message' => 'Status updated', 'status' => $newStatus]);
    }
}
