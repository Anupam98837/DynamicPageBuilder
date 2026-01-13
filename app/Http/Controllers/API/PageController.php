<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PageController extends Controller
{
    /* ============================================
     | Helpers
     |============================================ */
   private function sanitizeHtml(?string $html): ?string
{
    if ($html === null) {
        return null;
    }

    // Remove script tags (minimum protection)
    return preg_replace('#<script.*?>.*?</script>#is', '', $html);
}

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
            $q = DB::table('pages')->where('slug', $try);
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
                $q = DB::table('pages')->where('slug', $try);
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
        $query = DB::table('pages');
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
$base = DB::table('pages')
    ->whereNull('deleted_at')
    ->whereIn('status', ['Active', 'Inactive']); // 🔒 block Archived

            

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

        $base = DB::table('pages')
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

        $page = DB::table('pages')
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
        'shortcode'        => 'sometimes|nullable|string|max:12',
        'page_type'        => 'sometimes|string|max:30',
        'content_html'     => 'sometimes|nullable|string',
        'includable_id'    => 'sometimes|nullable|string|max:120',
        'layout_key'       => 'sometimes|nullable|string|max:100',
        'meta_description' => 'sometimes|nullable|string|max:255',
        'status'           => 'sometimes|string|max:20',
        'published_at'     => 'sometimes|nullable|date',
        'department_id'    => 'sometimes|nullable|integer|exists:departments,id',
        'submenu_exists'   => 'sometimes|in:yes,no',
    ]);

    /* ---------------- Slug ---------------- */
    $slugBase = $this->normSlug($data['slug'] ?? $data['title'] ?? '');
    $slug     = $this->uniqueSlug($slugBase);

    /* ---------------- Shortcode (AUTO) ---------------- */
    if (!isset($data['shortcode']) || trim($data['shortcode']) === '') {
        $base = strtoupper(substr(Str::slug($data['title'], ''), 0, 6)); // from title
        $suffix = strtoupper(Str::random(3));
        $shortcode = $base . '_' . $suffix; // e.g. ABOUT_7KD
    } else {
        $shortcode = $data['shortcode'];
    }

    /* ---------------- Includable ID ---------------- */
    $includableId = array_key_exists('includable_id', $data)
        ? ($data['includable_id'] ?: null)
        : null;

    if ($includableId !== null) {
        $exists = DB::table('pages')
            ->where('includable_id', $includableId)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'includable_id already exists'], 422);
        }
    }

    /* ---------------- Defaults ---------------- */
    $pageType = $data['page_type'] ?? 'page';
    $status   = $data['status'] ?? 'Active';

    $publishedAt = null;
    if (!empty($data['published_at'])) {
        $publishedAt = Carbon::parse($data['published_at']);
    }

    $actor = $this->actor($request);
    $now   = Carbon::now();
    $ip    = $request->ip();

    /* ---------------- Insert ---------------- */
    $id = DB::table('pages')->insertGetId([
        'uuid'               => (string) Str::uuid(),
        'department_id'      => $request->input('department_id'),
        'submenu_exists'     => $request->input('submenu_exists', 'no'),
        'slug'               => $slug,
        'title'              => $data['title'],
        'shortcode'          => $shortcode,
        'page_type'          => $pageType,
        'content_html'       => $this->sanitizeHtml($data['content_html'] ?? null),
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

    $row = DB::table('pages')->where('id', $id)->first();

    return response()->json([
        'success' => true,
        'data'    => $row
    ], 201);
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
        'department_id'    => 'sometimes|nullable|integer|exists:departments,id',
        'submenu_exists'   => 'sometimes|in:yes,no',
    ]);

    /* ---------------- Slug handling ---------------- */
    $slug = $page->slug;

    if (array_key_exists('slug', $data)) {
        $norm = $this->normSlug($data['slug']);

        if ($norm === '' || !empty($data['regenerate_slug'])) {
            $base = $this->normSlug($data['title'] ?? $page->title ?? 'page');
            $slug = $this->uniqueSlug($base, (int) $page->id);
        } else {
            $slug = $this->uniqueSlug($norm, (int) $page->id);
        }
    } elseif (
        !empty($data['regenerate_slug']) ||
        (isset($data['title']) && $data['title'] !== $page->title)
    ) {
        $base = $this->normSlug($data['title'] ?? $page->title ?? 'page');
        $slug = $this->uniqueSlug($base, (int) $page->id);
    }

    /* ---------------- Shortcode (AUTO) ---------------- */
    if (array_key_exists('shortcode', $data) && trim($data['shortcode']) !== '') {
        $shortcode = $data['shortcode'];
    } elseif (isset($data['title']) && $data['title'] !== $page->title) {
        $base = strtoupper(substr(Str::slug($data['title'], ''), 0, 6));
        $suffix = strtoupper(Str::random(3));
        $shortcode = $base . '_' . $suffix; // e.g. ABOUT_X7K
    } else {
        $shortcode = $page->shortcode;
    }

    /* ---------------- Includable ID ---------------- */
    $includableId = array_key_exists('includable_id', $data)
        ? ($data['includable_id'] ?: null)
        : $page->includable_id;

    if ($includableId !== null) {
        $exists = DB::table('pages')
            ->where('includable_id', $includableId)
            ->where('id', '!=', $page->id)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'includable_id already exists'], 422);
        }
    }

    /* ---------------- Published at ---------------- */
    if (array_key_exists('published_at', $data)) {
        $publishedAt = $data['published_at']
            ? Carbon::parse($data['published_at'])
            : null;
    } else {
        $publishedAt = $page->published_at;
    }

    $actor = $this->actor($request);

    /* ---------------- Final update payload ---------------- */
    $update = [
        'title'              => $data['title'] ?? $page->title,
        'slug'               => $slug,
        'shortcode'          => $shortcode,
        'page_type'          => $data['page_type'] ?? $page->page_type,

        // Safe HTML update
        'content_html'       => (
            array_key_exists('content_html', $data) &&
            trim((string) $data['content_html']) !== ''
        )
            ? $this->sanitizeHtml($data['content_html'])
            : $page->content_html,

        'includable_id'      => $includableId,
        'layout_key'         => $data['layout_key'] ?? $page->layout_key,
        'meta_description'   => $data['meta_description'] ?? $page->meta_description,
        'status'             => $data['status'] ?? $page->status,
        'published_at'       => $publishedAt,

        // New fields
        'department_id'      => $data['department_id'] ?? $page->department_id,
        'submenu_exists'     => $data['submenu_exists'] ?? $page->submenu_exists,

        // Audit
        'updated_at'         => Carbon::now(),
        'updated_by_user_id'=> $actor['id'] ?: null,
    ];

    DB::table('pages')
        ->where('id', $page->id)
        ->update($update);

    $fresh = DB::table('pages')->where('id', $page->id)->first();

    return response()->json([
        'success' => true,
        'data'    => $fresh
    ]);
}

    public function destroy(Request $request, $identifier)
    {
        $page = $this->resolvePage($identifier, false);
        if (! $page) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $actor = $this->actor($request);

        DB::table('pages')
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

        DB::table('pages')
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

        DB::table('pages')
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

        DB::table('pages')
            ->where('id', $page->id)
            ->update([
                'status'            => $newStatus,
                'updated_at'        => Carbon::now(),
                'updated_by_user_id'=> $actor['id'] ?: null,
            ]);

        return response()->json(['success' => true, 'message' => 'Status updated', 'status' => $newStatus]);
    }
 
public function publicApi(string $identifier)
{
    $page = DB::table('pages')
        ->where(function ($q) use ($identifier) {
            $q->where('slug', $identifier)
              ->orWhere('shortcode', $identifier);
        })
        ->where('status', 'Active')
        ->whereNull('deleted_at')
        ->first([
            'title',
            'meta_description',
            'content_html'
        ]);

    if (!$page) {
        abort(404);
    }

    return response()->json($page);
}
public function archive(Request $request, $identifier)
{
    $page = $this->resolvePage($identifier, false);
    if (!$page) {
        return response()->json(['error' => 'Not found'], 404);
    }

    $actor = $this->actor($request);

    DB::table('pages')
        ->where('id', $page->id)
        ->update([
            'status' => 'Archived',
            'updated_at' => Carbon::now(),
            'updated_by_user_id' => $actor['id'] ?: null,
        ]);

    return response()->json([
        'success' => true,
        'message' => 'Page archived successfully'
    ]);
}
public function archivedIndex(Request $request)
{
    $page = max(1, (int) $request->query('page', 1));
    $per  = min(100, max(5, (int) $request->query('per_page', 20)));

    $query = DB::table('pages')
        ->whereNull('deleted_at')
        ->where('status', 'Archived')
        ->orderBy('updated_at', 'desc');

    $total = (clone $query)->count();

    $rows = $query
        ->forPage($page, $per)
        ->get();

    return response()->json([
        'success' => true,
        'data' => $rows,
        'pagination' => [
            'page' => $page,
            'per_page' => $per,
            'total' => $total,
        ]
    ]);
}
public function restorePage(Request $request, $identifier)
{
    $page = $this->resolvePage($identifier, true);
    if (!$page) {
        return response()->json(['error' => 'Not found'], 404);
    }

    $actor = $this->actor($request);

    DB::table('pages')
        ->where('id', $page->id)
        ->update([
            'deleted_at' => null,
            'status' => 'Active',
            'updated_at' => Carbon::now(),
            'updated_by_user_id' => $actor['id'] ?: null,
        ]);

    return response()->json([
        'success' => true,
        'message' => 'Page restored successfully'
    ]);
}
public function hardDelete(Request $request, $identifier)
{
    $page = $this->resolvePage($identifier, true);
    if (!$page) {
        return response()->json(['error' => 'Not found'], 404);
    }

    DB::table('pages')
        ->where('id', $page->id)
        ->delete();

    return response()->json([
        'success' => true,
        'message' => 'Page permanently deleted'
    ]);
}

}
