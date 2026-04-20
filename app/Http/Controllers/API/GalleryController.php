<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class GalleryController extends Controller
{
    use \App\Http\Controllers\API\Concerns\HasWorkflowManagement;
    /* ============================================
     | Helpers
     |============================================ */

    private function actor(Request $r): array
    {
        return [
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? $r->user()?->id ?? 0),
            'role' => (string) ($r->attributes->get('auth_role') ?? ($r->user()?->role ?? '')),
            'type' => (string) ($r->attributes->get('auth_tokenable_type') ?? ($r->user() ? get_class($r->user()) : '')),
            'uuid' => (string) ($r->attributes->get('auth_user_uuid') ?? ($r->user()?->uuid ?? '')),
        ];
    }

    private function safeJson($value): ?string
    {
        if ($value === null) return null;

        if (is_string($value)) {
            $t = trim($value);
            if ($t === '') return null;

            json_decode($t, true);
            if (json_last_error() === JSON_ERROR_NONE) return $t;

            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function clip(?string $s, int $max): ?string
    {
        $s = $s === null ? null : (string) $s;
        if ($s === null) return null;
        if (mb_strlen($s) <= $max) return $s;
        return mb_substr($s, 0, $max);
    }

    private function blankToNull($value): ?string
    {
        if ($value === null) return null;
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function normalizeEventShortcode($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') return null;

        $value = Str::lower($value);
        $value = preg_replace('/\s+/', '-', $value);
        $value = preg_replace('/[^a-z0-9\-_]/', '', $value);
        $value = trim((string) $value, '-_');

        return $value !== '' ? $value : null;
    }

    private function diffKeys(array $before, array $after, array $keys): array
    {
        $changed = [];
        $old = [];
        $new = [];

        foreach ($keys as $k) {
            $b = $before[$k] ?? null;
            $a = $after[$k] ?? null;

            if ($b != $a) {
                $changed[] = $k;
                $old[$k] = $b;
                $new[$k] = $a;
            }
        }

        return [$changed, $old, $new];
    }

    private function logActivity(
        Request $r,
        string $activity,
        string $module,
        string $tableName,
        $recordId = null,
        ?array $changedFields = null,
        $oldValues = null,
        $newValues = null,
        ?string $note = null
    ): void {
        try {
            if (!Schema::hasTable('user_data_activity_log')) return;

            $actor = $this->actor($r);

            DB::table('user_data_activity_log')->insert([
                'performed_by'      => (int) ($actor['id'] ?? 0),
                'performed_by_role' => $this->clip((string) ($actor['role'] ?? ''), 50),
                'ip'                => $this->clip($r->ip(), 45),
                'user_agent'        => $this->clip($r->userAgent(), 512),

                'activity'          => $this->clip($activity, 50),
                'module'            => $this->clip($module, 100),

                'table_name'        => $this->clip($tableName, 128),
                'record_id'         => $recordId !== null ? (int) $recordId : null,

                'changed_fields'    => $this->safeJson($changedFields),
                'old_values'        => $this->safeJson($oldValues),
                'new_values'        => $this->safeJson($newValues),

                'log_note'          => $note,

                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            // never break core functionality because of logging
        }
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

        if (!Schema::hasColumn('users', 'department_id')) {
            return ['mode' => 'not_allowed', 'department_id' => null];
        }

        $q = DB::table('users')->select(['id', 'role', 'department_id', 'status']);

        if (Schema::hasColumn('users', 'deleted_at')) {
            $q->whereNull('deleted_at');
        }

        $u = $q->where('id', $userId)->first();

        if (!$u) {
            return ['mode' => 'none', 'department_id' => null];
        }

        if (isset($u?->status) && (string) $u->status !== 'active') {
            return ['mode' => 'none', 'department_id' => null];
        }

        $role = strtolower(trim((string) ($u?->role ?? '')));
        $role = str_replace([' ', '-'], '_', $role);
        $role = preg_replace('/_+/', '_', $role) ?? $role;

        $deptId = ($u?->department_id !== null) ? (int) $u->department_id : null;
        if ($deptId !== null && $deptId <= 0) $deptId = null;

        // author added here because route block already allows author
        $allRoles  = ['admin', 'author', 'director', 'principal'];
        $deptRoles = ['hod', 'faculty', 'technical_assistant', 'it_person', 'placement_officer', 'student'];

        if (in_array($role, $allRoles, true)) {
            return ['mode' => 'all', 'department_id' => null];
        }

        if (in_array($role, $deptRoles, true)) {
            if (!$deptId) return ['mode' => 'none', 'department_id' => null];
            return ['mode' => 'department', 'department_id' => $deptId];
        }

        return ['mode' => 'not_allowed', 'department_id' => null];
    }

    protected function resolveDepartment($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('departments');
        if (!$includeDeleted && Schema::hasColumn('departments', 'deleted_at')) {
            $q->whereNull('deleted_at');
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('uuid', (string) $identifier);
        } else {
            $q->where('slug', (string) $identifier);
        }

        return $q->first();
    }

    protected function resolveEventMasterByShortcode(?string $shortcode, ?int $departmentId = null, bool $publicVisibleOnly = false)
    {
        $shortcode = $this->normalizeEventShortcode($shortcode);
        if (!$shortcode) return null;

        $q = DB::table('gallery as g')
            ->select([
                'g.event_title',
                'g.event_description',
                'g.event_date',
                'g.event_shortcode',
                'g.department_id',
                'g.image',
            ])
            ->whereNull('g.deleted_at')
            ->whereNotNull('g.event_shortcode')
            ->where('g.event_shortcode', $shortcode);

        if ($departmentId !== null) {
            $q->where(function ($w) use ($departmentId) {
                $w->where('g.department_id', $departmentId)
                  ->orWhereNull('g.department_id');
            });
        }

        if ($publicVisibleOnly) {
            $now = now();

            $q->where('g.status', 'published')
              ->where(function ($w) use ($now) {
                  $w->whereNull('g.publish_at')->orWhere('g.publish_at', '<=', $now);
              })
              ->where(function ($w) use ($now) {
                  $w->whereNull('g.expire_at')->orWhere('g.expire_at', '>', $now);
              });
        }

        return $q->orderByRaw('CASE WHEN g.event_date IS NULL THEN 1 ELSE 0 END asc')
                 ->orderBy('g.event_date', 'desc')
                 ->orderBy('g.id', 'asc')
                 ->first();
    }

    protected function buildEventPayload(Request $request, ?int $departmentId = null): array
    {
        $selectedShortcode = $this->normalizeEventShortcode($request->input('selected_event_shortcode'));

        // Existing dropdown-selected event => DB is the source of truth
        if ($selectedShortcode !== null) {
            $existing = $this->resolveEventMasterByShortcode($selectedShortcode, $departmentId, false);

            if (!$existing) {
                throw ValidationException::withMessages([
                    'selected_event_shortcode' => ['Selected event not found.'],
                ]);
            }

            return [
                'event_title'       => $this->blankToNull($existing?->event_title ?? null),
                'event_description' => $this->blankToNull($existing?->event_description ?? null),
                'event_date'        => !empty($existing?->event_date) ? Carbon::parse($existing->event_date)->toDateString() : null,
                'event_shortcode'   => $this->normalizeEventShortcode($existing?->event_shortcode ?? null),
                '_mode'             => 'existing',
            ];
        }

        // Manual event input
        return [
            'event_title'       => $this->blankToNull($request->input('event_title')),
            'event_description' => $this->blankToNull($request->input('event_description')),
            'event_date'        => $request->filled('event_date')
                ? Carbon::parse($request->input('event_date'))->toDateString()
                : null,
            'event_shortcode'   => $this->normalizeEventShortcode($request->input('event_shortcode')),
            '_mode'             => 'manual',
        ];
    }

    protected function syncAlbumEventMetadata(
        Request $request,
        string $oldShortcode,
        array $eventPayload,
        $departmentId = null
    ): int {
        $oldShortcode = $this->normalizeEventShortcode($oldShortcode);
        if (!$oldShortcode) return 0;

        $q = DB::table('gallery')
            ->whereNull('deleted_at')
            ->where('event_shortcode', $oldShortcode);

        if ($departmentId === null) {
            $q->whereNull('department_id');
        } else {
            $q->where('department_id', (int) $departmentId);
        }

        return $q->update([
            'event_title'       => $eventPayload['event_title'] ?? null,
            'event_description' => $eventPayload['event_description'] ?? null,
            'event_date'        => $eventPayload['event_date'] ?? null,
            'event_shortcode'   => $eventPayload['event_shortcode'] ?? null,
            
            // Sync workflow status to linked album items if needed
            'workflow_status'   => $request->attributes->get('workflow_status') ?? 'draft',

            'updated_at'        => now(),
            'updated_at_ip'     => $request->ip(),
        ]);
    }

    protected function toUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') return null;
        if (preg_match('~^https?://~i', $path)) return $path;
        return url('/' . ltrim($path, '/'));
    }

    protected function normalizeTagsInput($value): ?array
    {
        if ($value === null) return null;

        if (is_array($value)) {
            $out = [];
            foreach ($value as $t) {
                $t = trim((string) $t);
                if ($t !== '') $out[] = $t;
            }
            return !empty($out) ? array_values($out) : null;
        }

        if (is_string($value)) {
            $s = trim($value);
            if ($s === '') return null;

            $decoded = json_decode($s, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeTagsInput($decoded);
            }

            if (str_contains($s, ',')) {
                $parts = array_map('trim', explode(',', $s));
                $parts = array_values(array_filter($parts, fn ($x) => $x !== ''));
                return !empty($parts) ? $parts : null;
            }

            return [$s];
        }

        return null;
    }

    protected function normalizeEventOptionRow($row): array
    {
        $arr = (array) $row;

        $arr['images_count'] = (int) ($arr['images_count'] ?? 0);
        $arr['cover_image_url'] = $this->toUrl($arr['cover_image'] ?? null);

        $arr['event'] = [
            'title'       => $arr['event_title'] ?? null,
            'description' => $arr['event_description'] ?? null,
            'date'        => $arr['event_date'] ?? null,
            'shortcode'   => $arr['event_shortcode'] ?? null,
        ];

        return $arr;
    }

    protected function normalizeRow($row): array
    {
        $arr = (array) $row;

        $tags = $arr['tags_json'] ?? null;
        if (is_string($tags)) {
            $decoded = json_decode($tags, true);
            $arr['tags_json'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        $meta = $arr['metadata'] ?? null;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        $arr['image_url'] = $this->toUrl($arr['image'] ?? null);

        $arr['tags'] = [];
        if (is_array($arr['tags_json'] ?? null)) {
            $out = [];
            foreach (($arr['tags_json'] ?? []) as $t) {
                $t = trim((string) $t);
                if ($t !== '') $out[] = $t;
            }
            $arr['tags'] = array_values($out);
        }

        $arr['event'] = [
            'title'       => $arr['event_title'] ?? null,
            'description' => $arr['event_description'] ?? null,
            'date'        => $arr['event_date'] ?? null,
            'shortcode'   => $arr['event_shortcode'] ?? null,
        ];

        $arr['has_event'] = !empty($arr['event_shortcode']);

        return $arr;
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

        $filename = $prefix . '-' . Str::random(10) . '.' . $ext;
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

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('gallery as g')
            ->leftJoin('departments as d', 'd.id', '=', 'g.department_id')
            ->select([
                'g.*',
                'd.title as department_title',
                'd.slug  as department_slug',
                'd.uuid  as department_uuid',
            ]);

        if (!$includeDeleted) $q->whereNull('g.deleted_at');

        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';

            $q->where(function ($sub) use ($term) {
                $sub->where('g.title', 'like', $term)
                    ->orWhere('g.description', 'like', $term)
                    ->orWhere('g.image', 'like', $term)
                    ->orWhere('g.event_title', 'like', $term)
                    ->orWhere('g.event_description', 'like', $term)
                    ->orWhere('g.event_shortcode', 'like', $term);
            });
        }

        if ($request->filled('status')) {
            $q->where('g.status', (string) $request->query('status'));
        }

        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) $q->where('g.is_featured_home', $featured ? 1 : 0);
        }

        if ($request->filled('department')) {
            $dept = $this->resolveDepartment($request->query('department'), true);
            if ($dept) $q->where('g.department_id', (int) ($dept?->id ?? 0));
            else $q->whereRaw('1=0');
        }

        if ($request->filled('event_shortcode')) {
            $shortcode = $this->normalizeEventShortcode($request->query('event_shortcode'));
            if ($shortcode) {
                $q->where('g.event_shortcode', $shortcode);
            } else {
                $q->whereRaw('1=0');
            }
        }

        if ($request->has('has_event')) {
            $hasEvent = filter_var($request->query('has_event'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($hasEvent === true) {
                $q->whereNotNull('g.event_shortcode')->where('g.event_shortcode', '<>', '');
            } elseif ($hasEvent === false) {
                $q->where(function ($w) {
                    $w->whereNull('g.event_shortcode')->orWhere('g.event_shortcode', '');
                });
            }
        }

        if ($request->has('visible_now')) {
            $visible = filter_var($request->query('visible_now'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($visible) {
                $now = now();
                $q->where('g.status', 'published')
                  ->where(function ($w) use ($now) {
                      $w->whereNull('g.publish_at')->orWhere('g.publish_at', '<=', $now);
                  })
                  ->where(function ($w) use ($now) {
                      $w->whereNull('g.expire_at')->orWhere('g.expire_at', '>', $now);
                  });
            }
        }

        $sort = (string) $request->query('sort', 'sort_order');
        $dir  = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowed = ['sort_order', 'created_at', 'publish_at', 'expire_at', 'title', 'views_count', 'id', 'event_date', 'event_title'];
        if (!in_array($sort, $allowed, true)) $sort = 'sort_order';

        $q->orderBy('g.' . $sort, $dir);

        if ($sort !== 'created_at') $q->orderBy('g.created_at', 'desc');

        return $q;
    }

    protected function resolveGallery(Request $request, $identifier, bool $includeDeleted = false, $departmentId = null)
    {
        $q = DB::table('gallery as g');
        if (!$includeDeleted) $q->whereNull('g.deleted_at');

        if ($departmentId !== null) $q->where('g.department_id', (int) $departmentId);

        if (ctype_digit((string) $identifier)) {
            $q->where('g.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('g.uuid', (string) $identifier);
        } else {
            $q->where('g.uuid', (string) $identifier);
        }

        $row = $q->first();
        if (!$row) return null;

        if (!empty($row?->department_id)) {
            $dept = DB::table('departments')->where('id', (int) $row->department_id)->first();
            if ($row) {
                $row->department_title = $dept?->title ?? null;
                $row->department_slug  = $dept?->slug ?? null;
                $row->department_uuid  = $dept?->uuid ?? null;
            }
        } else {
            if ($row) {
                $row->department_title = null;
                $row->department_slug  = null;
                $row->department_uuid  = null;
            }
        }

        return $row;
    }

    protected function applyVisibleWindow($q): void
    {
        $now = now();

        $q->whereNull('g.deleted_at')
          ->where('g.status', 'published')
          ->where(function ($w) use ($now) {
              $w->whereNull('g.publish_at')->orWhere('g.publish_at', '<=', $now);
          })
          ->where(function ($w) use ($now) {
              $w->whereNull('g.expire_at')->orWhere('g.expire_at', '>', $now);
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

        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        if ($ac['mode'] === 'none') {
            $page = max(1, (int) $request->query('page', 1));
            return response()->json([
                'data' => [],
                'pagination' => [
                    'page'      => $page,
                    'per_page'  => $perPage,
                    'total'     => 0,
                    'last_page' => 1,
                ],
            ]);
        }

        if ($ac['mode'] === 'department') {
            $request->query->set('department', (string) ((int) $ac['department_id']));
        }

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        if ($onlyDeleted) $query->whereNotNull('g.deleted_at');

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
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);

        if ($ac['mode'] === 'none') {
            $perPage = max(1, min(200, (int) $request->query('per_page', 20)));
            $page = max(1, (int) $request->query('page', 1));
            return response()->json([
                'data' => [],
                'pagination' => [
                    'page'      => $page,
                    'per_page'  => $perPage,
                    'total'     => 0,
                    'last_page' => 1,
                ],
            ]);
        }

        $dept = $this->resolveDepartment($department, false);
        if (!$dept) return response()->json(['message' => 'Department not found'], 404);

        if ($ac['mode'] === 'department' && (int) $dept->id !== (int) $ac['department_id']) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        $request->query->set('department', (string) $dept->id);
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
        if ($ac['mode'] === 'none') return response()->json(['message' => 'Gallery item not found'], 404);

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveGallery($request, $identifier, $includeDeleted, $deptId);
        if (!$row) return response()->json(['message' => 'Gallery item not found'], 404);

        if (filter_var($request->query('inc_view', false), FILTER_VALIDATE_BOOLEAN)) {
            DB::table('gallery')->where('id', (int) ($row?->id ?? 0))->increment('views_count');
            if ($row) $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
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
        if ($ac['mode'] === 'none') return response()->json(['message' => 'Gallery item not found'], 404);

        $dept = $this->resolveDepartment($department, true);
        if (!$dept) return response()->json(['message' => 'Department not found'], 404);

        if ($ac['mode'] === 'department' && (int) $dept->id !== (int) $ac['department_id']) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveGallery($request, $identifier, $includeDeleted, (int) $dept->id);
        if (!$row) return response()->json(['message' => 'Gallery item not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    /**
     * Unique event list for dropdown
     * Optional: ?q=
     * Optional: ?department=
     */
    public function eventOptions(Request $request)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') {
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $perPage = max(1, min(500, (int) $request->query('per_page', 100)));

        if ($ac['mode'] === 'none') {
            return response()->json([
                'success' => true,
                'data' => [],
                'pagination' => [
                    'page'      => 1,
                    'per_page'  => $perPage,
                    'total'     => 0,
                    'last_page' => 1,
                ],
            ]);
        }

        $q = DB::table('gallery as g')
            ->select([
                'g.event_shortcode',
                'g.event_title',
                'g.event_description',
                'g.event_date',
                DB::raw('COUNT(*) as images_count'),
                DB::raw('MIN(g.image) as cover_image'),
            ])
            ->whereNull('g.deleted_at')
            ->whereNotNull('g.event_shortcode')
            ->where('g.event_shortcode', '<>', '');

        if ($ac['mode'] === 'department') {
            $q->where(function ($w) use ($ac) {
                $w->where('g.department_id', (int) $ac['department_id'])
                  ->orWhereNull('g.department_id');
            });
        } elseif ($request->filled('department')) {
            $dept = $this->resolveDepartment($request->query('department'), false);
            if (!$dept) {
                return response()->json(['message' => 'Department not found'], 404);
            }
            $deptId = (int) ($dept?->id ?? 0);
            $q->where(function ($w) use ($deptId) {
                $w->where('g.department_id', $deptId)
                  ->orWhereNull('g.department_id');
            });
        }

        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('g.event_title', 'like', $term)
                    ->orWhere('g.event_description', 'like', $term)
                    ->orWhere('g.event_shortcode', 'like', $term);
            });
        }

        $q->groupBy('g.event_shortcode', 'g.event_title', 'g.event_description', 'g.event_date')
          ->orderByRaw('CASE WHEN g.event_date IS NULL THEN 1 ELSE 0 END asc')
          ->orderBy('g.event_date', 'desc')
          ->orderBy('g.event_title', 'asc');

        $paginator = $q->paginate($perPage);
        $items = array_map(fn ($r) => $this->normalizeEventOptionRow($r), $paginator->items());

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

    public function store(Request $request)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'create', 'gallery', 'gallery', null, null, null, null, 'Not allowed');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $actor = $this->actor($request);

        try {
            $validated = $request->validate([
                'department_id'            => ['nullable', 'integer', 'exists:departments,id'],

                'image_file'               => ['required_without:image', 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
                'image'                    => ['required_without:image_file', 'nullable', 'string', 'max:255'],

                'title'                    => ['nullable', 'string', 'max:255'],
                'description'              => ['nullable', 'string', 'max:500'],
                'tags_json'                => ['nullable'],

                'event_title'              => ['nullable', 'string', 'max:255'],
                'event_description'        => ['nullable', 'string'],
                'event_date'               => ['nullable', 'date'],
                'event_shortcode'          => ['nullable', 'string', 'max:255'],
                'selected_event_shortcode' => ['nullable', 'string', 'max:255'],

                'is_featured_home'         => ['nullable', 'boolean'],
                'sort_order'               => ['nullable', 'integer', 'min:0'],
                'status'                   => ['nullable', 'in:draft,published,archived'],
                'publish_at'               => ['nullable', 'date'],
                'expire_at'                => ['nullable', 'date'],
                'metadata'                 => ['nullable'],
            ]);

            $eventLookupDeptId = $ac['mode'] === 'department'
                ? (int) $ac['department_id']
                : (($validated['department_id'] ?? null) !== null ? (int) $validated['department_id'] : null);

            $eventPayload = $this->buildEventPayload($request, $eventLookupDeptId);
        } catch (ValidationException $e) {
            $this->logActivity(
                $request,
                'create',
                'gallery',
                'gallery',
                null,
                array_keys($e->errors()),
                null,
                ['input' => $request->except(['image_file'])],
                'Validation failed'
            );
            throw $e;
        }

        if ($ac['mode'] === 'department') {
            $validated['department_id'] = (int) $ac['department_id'];
        }

        // Unified Workflow Status
        $workflowStatus = $this->getInitialWorkflowStatus($request);
        $featured = (int) ($validated['is_featured_home'] ?? 0);
        $requestForApproval = ($workflowStatus === 'pending_check' || $workflowStatus === 'checked') ? 1 : 0;

        $uuid = (string) Str::uuid();
        $now  = now();

        $tags = $this->normalizeTagsInput($request->input('tags_json', null));

        $metadata = $request->input('metadata', null);
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
        }

        $imagePath = null;

        if ($request->hasFile('image_file')) {
            $f = $request->file('image_file');
            if (!$f || !$f->isValid()) {
                $this->logActivity($request, 'create', 'gallery', 'gallery', null, ['image_file'], null, null, 'Image upload failed');
                return response()->json(['success' => false, 'message' => 'Image upload failed'], 422);
            }

            $deptKey = !empty($validated['department_id']) ? (string) ((int) $validated['department_id']) : 'global';
            $dirRel  = 'depy_uploads/gallery/' . $deptKey;

            $meta = $this->uploadFileToPublic($f, $dirRel, 'gallery-' . $uuid);
            $imagePath = $meta['path'];
        } else {
            $imagePath = trim((string) ($validated['image'] ?? ''));
        }

        if ($imagePath === '') {
            $this->logActivity($request, 'create', 'gallery', 'gallery', null, ['image'], null, null, 'Image is required');
            return response()->json(['success' => false, 'message' => 'Image is required'], 422);
        }

        $insert = [
            'uuid'              => $uuid,
            'department_id'     => $validated['department_id'] ?? null,
            'image'             => $imagePath,
            'title'             => $validated['title'] ?? null,
            'description'       => $validated['description'] ?? null,
            'tags_json'         => $tags !== null ? json_encode($tags) : null,

            'event_title'       => $eventPayload['event_title'] ?? null,
            'event_description' => $eventPayload['event_description'] ?? null,
            'event_date'        => $eventPayload['event_date'] ?? null,
            'event_shortcode'   => $eventPayload['event_shortcode'] ?? null,

            'is_featured_home'     => $featured,
            'sort_order'           => (int) ($validated['sort_order'] ?? 0),

            // Unified Workflow
            'workflow_status'      => $workflowStatus,
            'draft_data'           => null,

            // Legacy Approval columns
            'request_for_approval' => $requestForApproval,
            'is_approved'          => ($workflowStatus === 'approved') ? 1 : 0,
            'is_rejected'          => ($workflowStatus === 'rejected') ? 1 : 0,

            'status'               => (string) ($validated['status'] ?? ($workflowStatus === 'approved' ? 'published' : 'draft')),
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

        $id = DB::table('gallery')->insertGetId($insert);

        $row = DB::table('gallery')->where('id', $id)->first();

        $this->logActivity(
            $request,
            'create',
            'gallery',
            'gallery',
            $id,
            array_keys($insert),
            null,
            $row ? (array) $row : $insert,
            'Created gallery item'
        );

        return response()->json([
            'success' => true,
            'data'    => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    public function storeForDepartment(Request $request, $department)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'create', 'gallery', 'gallery', null, null, null, null, 'Not allowed');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $dept = $this->resolveDepartment($department, false);
        if (!$dept) {
            $this->logActivity($request, 'create', 'gallery', 'gallery', null, ['department'], null, ['department' => $department], 'Department not found');
            return response()->json(['message' => 'Department not found'], 404);
        }

        if ($ac['mode'] === 'department' && (int) $dept->id !== (int) $ac['department_id']) {
            $this->logActivity($request, 'create', 'gallery', 'gallery', null, ['department_id'], null, ['department_id' => (int) $dept->id], 'Not allowed (department mismatch)');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $request->merge(['department_id' => (int) $dept->id]);
        return $this->store($request);
    }

    public function update(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'update', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Not allowed');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveGallery($request, $identifier, true, $deptId);
        if (!$row) {
            $this->logActivity($request, 'update', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Gallery item not found');
            return response()->json(['message' => 'Gallery item not found'], 404);
        }

        $beforeRawObj = DB::table('gallery')->where('id', (int) $row->id)->first();
        $beforeRaw = $beforeRawObj ? (array) $beforeRawObj : [];
        $oldForLog = $beforeRaw;

        try {
            $validated = $request->validate([
                'department_id'            => ['nullable', 'integer', 'exists:departments,id'],

                'image_file'               => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
                'image'                    => ['nullable', 'string', 'max:255'],
                'image_remove'             => ['nullable', 'boolean'],

                'title'                    => ['nullable', 'string', 'max:255'],
                'description'              => ['nullable', 'string', 'max:500'],
                'tags_json'                => ['nullable'],

                'event_title'              => ['nullable', 'string', 'max:255'],
                'event_description'        => ['nullable', 'string'],
                'event_date'               => ['nullable', 'date'],
                'event_shortcode'          => ['nullable', 'string', 'max:255'],
                'selected_event_shortcode' => ['nullable', 'string', 'max:255'],

                'is_featured_home'         => ['nullable', 'boolean'],
                'sort_order'               => ['nullable', 'integer', 'min:0'],
                'status'                   => ['nullable', 'in:draft,published,archived'],
                'publish_at'               => ['nullable', 'date'],
                'expire_at'                => ['nullable', 'date'],
                'metadata'                 => ['nullable'],
            ]);

            $eventLookupDeptId = $ac['mode'] === 'department'
                ? (int) $ac['department_id']
                : (
                    array_key_exists('department_id', $validated)
                        ? (($validated['department_id'] !== null) ? (int) $validated['department_id'] : null)
                        : (($row->department_id !== null) ? (int) $row->department_id : null)
                );

            $eventPayload = $this->buildEventPayload($request, $eventLookupDeptId);
        } catch (ValidationException $e) {
            $this->logActivity(
                $request,
                'update',
                'gallery',
                'gallery',
                (int) $row->id,
                array_keys($e->errors()),
                $beforeRaw,
                ['input' => $request->except(['image_file'])],
                'Validation failed'
            );
            throw $e;
        }

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        if ($ac['mode'] === 'department') {
            $update['department_id'] = (int) $ac['department_id'];
        } else {
            if (array_key_exists('department_id', $validated)) {
                $update['department_id'] = $validated['department_id'] !== null ? (int) $validated['department_id'] : null;
            }
        }

        foreach (['title', 'description', 'status'] as $k) {
            if (array_key_exists($k, $validated)) $update[$k] = $validated[$k];
        }

        if (array_key_exists('is_featured_home', $validated)) $update['is_featured_home'] = (int) $validated['is_featured_home'];
        if (array_key_exists('sort_order', $validated)) $update['sort_order'] = (int) $validated['sort_order'];
        if (array_key_exists('publish_at', $validated)) $update['publish_at'] = !empty($validated['publish_at']) ? Carbon::parse($validated['publish_at']) : null;
        if (array_key_exists('expire_at', $validated))  $update['expire_at']  = !empty($validated['expire_at'])  ? Carbon::parse($validated['expire_at'])  : null;

        if (array_key_exists('tags_json', $validated)) {
            $tags = $this->normalizeTagsInput($request->input('tags_json', null));
            $update['tags_json'] = $tags !== null ? json_encode($tags) : null;
        }

        if (array_key_exists('metadata', $validated)) {
            $metadata = $request->input('metadata', null);
            if (is_string($metadata)) {
                $decoded = json_decode($metadata, true);
                if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
            }
            $update['metadata'] = $metadata !== null ? json_encode($metadata) : null;
        }

        $wantRemove = filter_var($request->input('image_remove', false), FILTER_VALIDATE_BOOLEAN);

        if ($request->hasFile('image_file')) {
            $f = $request->file('image_file');
            if (!$f || !$f->isValid()) {
                $this->logActivity($request, 'update', 'gallery', 'gallery', (int) $row->id, ['image_file'], $beforeRaw, null, 'Image upload failed');
                return response()->json(['success' => false, 'message' => 'Image upload failed'], 422);
            }

            $newDeptId = array_key_exists('department_id', $update)
                ? ($update['department_id'] !== null ? (int) $update['department_id'] : null)
                : ($row->department_id !== null ? (int) $row->department_id : null);

            $deptKey = $newDeptId ? (string) $newDeptId : 'global';
            $dirRel  = 'depy_uploads/gallery/' . $deptKey;

            $this->deletePublicPath($row->image ?? null);

            $meta = $this->uploadFileToPublic($f, $dirRel, 'gallery-' . (string) ($row->uuid ?? Str::uuid()));
            $update['image'] = $meta['path'];
        } elseif (array_key_exists('image', $validated) && trim((string) $validated['image']) !== '') {
            if ($wantRemove) {
                $this->deletePublicPath($row->image ?? null);
            }
            $update['image'] = trim((string) $validated['image']);
        } elseif ($wantRemove) {
            $this->logActivity(
                $request,
                'update',
                'gallery',
                'gallery',
                (int) $row->id,
                ['image_remove'],
                $beforeRaw,
                ['image_remove' => 1],
                'image_remove=1 requires providing a new image_file or image path (image is NOT NULL).'
            );

            return response()->json([
                'success' => false,
                'message' => 'image_remove=1 requires providing a new image_file or image path (image is NOT NULL).'
            ], 422);
        }

        $eventInputTouched =
            $request->exists('selected_event_shortcode') ||
            $request->exists('event_title') ||
            $request->exists('event_description') ||
            $request->exists('event_date') ||
            $request->exists('event_shortcode');

        $eventUpdateFields = [
            'event_title'       => $eventPayload['event_title'] ?? null,
            'event_description' => $eventPayload['event_description'] ?? null,
            'event_date'        => $eventPayload['event_date'] ?? null,
            'event_shortcode'   => $eventPayload['event_shortcode'] ?? null,
        ];

        $eventSyncCount = 0;
        $oldEventShortcode = $this->normalizeEventShortcode($row->event_shortcode ?? null);
        $oldDepartmentId   = $row->department_id !== null ? (int) $row->department_id : null;

        if ($eventInputTouched) {
            // Dropdown-selected existing event => update only current image row
            if (($eventPayload['_mode'] ?? 'manual') === 'existing') {
                $update = array_merge($update, $eventUpdateFields);
            }
            // Manual edit of an already-album-assigned event => sync whole album
            elseif ($oldEventShortcode !== null) {
                $eventSyncCount = $this->syncAlbumEventMetadata(
                    $request,
                    $oldEventShortcode,
                    $eventUpdateFields,
                    $oldDepartmentId
                );
            }
            // Manual edit on item without old event => only current row
            else {
                $update = array_merge($update, $eventUpdateFields);
            }
        }

        /* ---------------- Execution ---------------- */
        try {
            $result = $this->handleWorkflowUpdate($request, 'gallery', $row->id, $update);
            
            $fresh = DB::table('gallery')->where('id', $row->id)->first();
            
            $msg = ($result === 'drafted') 
                ? 'Your changes have been submitted for approval. The live content will remain unchanged until approved.'
                : 'Gallery item updated successfully.';

            $newForLog = $fresh ? (array) $fresh : null;
            [$changed, $old, $new] = $this->diffKeys($oldForLog, $newForLog ?: [], array_keys($update));

            $this->logActivity(
                $request,
                'update',
                'gallery',
                'gallery',
                (int) $row->id,
                $changed,
                $old,
                $new,
                $msg
            );

            return response()->json([
                'success' => true,
                'message' => $msg,
                'data'    => $fresh ? $this->normalizeRow($fresh) : null,
            ]);
        } catch (\Throwable $e) {
            $this->logActivity(
                $request,
                'update_error',
                'gallery',
                'gallery',
                (int) $row->id,
                null,
                $oldForLog,
                null,
                'Error: ' . $e->getMessage()
            );
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleFeatured(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'toggle_featured', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Not allowed');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveGallery($request, $identifier, true, $deptId);
        if (!$row) {
            $this->logActivity($request, 'toggle_featured', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Gallery item not found');
            return response()->json(['message' => 'Gallery item not found'], 404);
        }

        $beforeRawObj = DB::table('gallery')->where('id', (int) $row->id)->first();
        $beforeRaw = $beforeRawObj ? (array) $beforeRawObj : [];
        $oldFeatured = (int) ($beforeRaw['is_featured_home'] ?? ($row->is_featured_home ?? 0));

        $new = $oldFeatured ? 0 : 1;

        DB::table('gallery')->where('id', (int) $row->id)->update([
            'is_featured_home' => $new,
            'updated_at'       => now(),
            'updated_at_ip'    => $request->ip(),
        ]);

        $fresh = DB::table('gallery')->where('id', (int) $row->id)->first();
        $afterRaw = $fresh ? (array) $fresh : [];

        $this->logActivity(
            $request,
            'toggle_featured',
            'gallery',
            'gallery',
            (int) $row->id,
            ['is_featured_home'],
            ['is_featured_home' => $oldFeatured],
            ['is_featured_home' => (int) ($afterRaw['is_featured_home'] ?? $new)],
            'Toggled featured flag'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'delete', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Not allowed');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveGallery($request, $identifier, false, $deptId);
        if (!$row) {
            $this->logActivity($request, 'delete', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Not found or already deleted');
            return response()->json(['message' => 'Not found or already deleted'], 404);
        }

        $beforeRawObj = DB::table('gallery')->where('id', (int) $row->id)->first();
        $beforeRaw = $beforeRawObj ? (array) $beforeRawObj : [];

        $ts = now();

        DB::table('gallery')->where('id', (int) $row->id)->update([
            'deleted_at'    => $ts,
            'updated_at'    => $ts,
            'updated_at_ip' => $request->ip(),
        ]);

        $this->logActivity(
            $request,
            'delete',
            'gallery',
            'gallery',
            (int) $row->id,
            ['deleted_at'],
            ['deleted_at' => $beforeRaw['deleted_at'] ?? null],
            ['deleted_at' => (string) $ts],
            'Soft-deleted gallery item'
        );

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'restore', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Not allowed');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveGallery($request, $identifier, true, $deptId);
        if (!$row || $row->deleted_at === null) {
            $this->logActivity($request, 'restore', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Not found in bin');
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $beforeRawObj = DB::table('gallery')->where('id', (int) $row->id)->first();
        $beforeRaw = $beforeRawObj ? (array) $beforeRawObj : [];

        DB::table('gallery')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('gallery')->where('id', (int) $row->id)->first();

        $this->logActivity(
            $request,
            'restore',
            'gallery',
            'gallery',
            (int) $row->id,
            ['deleted_at'],
            ['deleted_at' => $beforeRaw['deleted_at'] ?? null],
            ['deleted_at' => null],
            'Restored gallery item'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed' || $ac['mode'] === 'none') {
            $this->logActivity($request, 'force_delete', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Not allowed');
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $deptId = ($ac['mode'] === 'department') ? (int) $ac['department_id'] : null;

        $row = $this->resolveGallery($request, $identifier, true, $deptId);
        if (!$row) {
            $this->logActivity($request, 'force_delete', 'gallery', 'gallery', null, null, null, ['identifier' => $identifier], 'Gallery item not found');
            return response()->json(['message' => 'Gallery item not found'], 404);
        }

        $beforeRawObj = DB::table('gallery')->where('id', (int) $row->id)->first();
        $beforeRaw = $beforeRawObj ? (array) $beforeRawObj : [];

        $this->deletePublicPath($row->image ?? null);

        DB::table('gallery')->where('id', (int) $row->id)->delete();

        $this->logActivity(
            $request,
            'force_delete',
            'gallery',
            'gallery',
            (int) $row->id,
            array_keys($beforeRaw),
            $beforeRaw,
            null,
            'Hard-deleted gallery item'
        );

        return response()->json(['success' => true]);
    }

    /* ============================================
     | Public (no auth)
     |============================================ */

    public function publicIndex(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 12)));

        $q = $this->baseQuery($request, true);
        $this->applyVisibleWindow($q);

        $q->orderBy('g.sort_order', 'asc')->orderByRaw('COALESCE(g.publish_at, g.created_at) desc');

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
        if (!$dept) return response()->json(['message' => 'Department not found'], 404);

        $request->query->set('department', $dept->id);
        return $this->publicIndex($request);
    }

    /**
     * Public unique event cards
     * Optional: ?q=
     * Optional: ?department=
     */
    public function publicEvents(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 12)));
        $now = now();
        $deptId = null;

        if ($request->filled('department')) {
            $dept = $this->resolveDepartment($request->query('department'), false);
            if ($dept) $deptId = (int) $dept->id;
        }

        // --- Part 1: Grouped Events ---
        $events = DB::table('gallery as g')
            ->select([
                'g.event_shortcode',
                DB::raw('MAX(g.event_title) as event_title'),
                DB::raw('MAX(g.event_description) as event_description'),
                DB::raw('MAX(g.event_date) as event_date'),
                DB::raw('COUNT(*) as images_count'),
                DB::raw('MIN(g.image) as cover_image'),
                DB::raw('MAX(g.created_at) as latest_created_at'),
            ])
            ->whereNull('g.deleted_at')
            ->where('g.status', 'published')
            ->whereNotNull('g.event_shortcode')
            ->where('g.event_shortcode', '<>', '')
            ->where(function ($w) use ($now) {
                $w->whereNull('g.publish_at')->orWhere('g.publish_at', '<=', $now);
            })
            ->where(function ($w) use ($now) {
                $w->whereNull('g.expire_at')->orWhere('g.expire_at', '>', $now);
            });

        if ($deptId !== null) {
            $events->where(function ($w) use ($deptId) {
                $w->where('g.department_id', $deptId)->orWhereNull('g.department_id');
            });
        }

        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $events->where(function ($sub) use ($term) {
                $sub->where('g.event_title', 'like', $term)
                    ->orWhere('g.event_description', 'like', $term)
                    ->orWhere('g.event_shortcode', 'like', $term);
            });
        }

        $events->groupBy('g.event_shortcode');

        // --- Part 2: Standalone Images ---
        $standalone = DB::table('gallery as g')
            ->select([
                DB::raw('NULL as event_shortcode'),
                'g.title as event_title',
                'g.description as event_description',
                'g.event_date',
                DB::raw('1 as images_count'),
                'g.image as cover_image',
                'g.created_at as latest_created_at',
            ])
            ->whereNull('g.deleted_at')
            ->where('g.status', 'published')
            ->where(function ($w) {
                $w->whereNull('g.event_shortcode')->orWhere('g.event_shortcode', '');
            })
            ->where(function ($w) use ($now) {
                $w->whereNull('g.publish_at')->orWhere('g.publish_at', '<=', $now);
            })
            ->where(function ($w) use ($now) {
                $w->whereNull('g.expire_at')->orWhere('g.expire_at', '>', $now);
            });

        if ($deptId !== null) {
            $standalone->where(function ($w) use ($deptId) {
                $w->where('g.department_id', $deptId)->orWhereNull('g.department_id');
            });
        }

        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $standalone->where(function ($sub) use ($term) {
                $sub->where('g.title', 'like', $term)
                    ->orWhere('g.description', 'like', $term);
            });
        }

        $union = $events->unionAll($standalone);

        $totalQuery = DB::table(DB::raw("({$union->toSql()}) as combined"))->mergeBindings($union);
        $total = $totalQuery->count();

        $page = max(1, (int) $request->query('page', 1));
        $offset = ($page - 1) * $perPage;

        $results = DB::table(DB::raw("({$union->toSql()}) as combined"))
            ->mergeBindings($union)
            ->orderByRaw('CASE WHEN event_date IS NULL THEN 1 ELSE 0 END asc')
            ->orderBy('event_date', 'desc')
            ->orderBy('latest_created_at', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        $items = array_map(fn ($r) => $this->normalizeEventOptionRow($r), $results->toArray());

        return response()->json([
            'success' => true,
            'data'    => $items,
            'pagination' => [
                'page'      => $page,
                'per_page'  => $perPage,
                'total'     => $total,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Public album by shortcode
     * Optional: ?department=
     * Optional: ?per_page=
     */
    public function publicEventShow(Request $request, $shortcode)
    {
        $shortcode = $this->normalizeEventShortcode($shortcode);
        if (!$shortcode) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $deptId = null;
        if ($request->filled('department')) {
            $dept = $this->resolveDepartment($request->query('department'), false);
            if (!$dept) return response()->json(['message' => 'Department not found'], 404);
            $deptId = (int) $dept->id;
        }

        $eventMaster = $this->resolveEventMasterByShortcode($shortcode, $deptId, true);
        if (!$eventMaster) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $perPage = max(1, min(200, (int) $request->query('per_page', 24)));

        $request->query->set('event_shortcode', $shortcode);
        if ($deptId !== null) {
            $request->query->set('department', (string) $deptId);
        }

        $q = $this->baseQuery($request, true);
        $this->applyVisibleWindow($q);

        $q->where('g.event_shortcode', $shortcode)
          ->orderBy('g.sort_order', 'asc')
          ->orderBy('g.created_at', 'desc');

        $paginator = $q->paginate($perPage);
        $items = array_map(fn ($r) => $this->normalizeRow($r), $paginator->items());

        return response()->json([
            'success' => true,
            'event'   => [
                'title'       => $eventMaster->event_title ?? null,
                'description' => $eventMaster->event_description ?? null,
                'date'        => $eventMaster->event_date ?? null,
                'shortcode'   => $eventMaster->event_shortcode ?? null,
            ],
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
        $row = $this->resolveGallery($request, $identifier, false);
        if (!$row) return response()->json(['message' => 'Gallery item not found'], 404);

        $now = now();
        $isVisible =
            ($row->status === 'published') &&
            (empty($row->publish_at) || Carbon::parse($row->publish_at)->lte($now)) &&
            (empty($row->expire_at)  || Carbon::parse($row->expire_at)->gt($now));

        if (!$isVisible) {
            return response()->json(['message' => 'Gallery item not available'], 404);
        }

        $inc = $request->has('inc_view')
            ? filter_var($request->query('inc_view'), FILTER_VALIDATE_BOOLEAN)
            : true;

        if ($inc) {
            DB::table('gallery')->where('id', (int) $row->id)->increment('views_count');
            $row->views_count = ((int) ($row->views_count ?? 0)) + 1;
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }
}