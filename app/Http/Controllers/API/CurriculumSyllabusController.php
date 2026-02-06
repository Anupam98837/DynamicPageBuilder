<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class CurriculumSyllabusController extends Controller
{
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

        // Safety (if some env doesn't have dept column yet)
        if (!Schema::hasColumn('users', 'department_id')) {
            return ['mode' => 'not_allowed', 'department_id' => null];
        }

        $q = DB::table('users')->select(['id', 'role', 'department_id', 'status']);

        // your schema has deleted_at; keep it safe
        if (Schema::hasColumn('users', 'deleted_at')) {
            $q->whereNull('deleted_at');
        }

        $u = $q->where('id', $userId)->first();

        if (!$u) {
            return ['mode' => 'none', 'department_id' => null];
        }

        // optional: inactive users => none
        if (isset($u->status) && (string)$u->status !== 'active') {
            return ['mode' => 'none', 'department_id' => null];
        }

        // normalize role from users table
        $role = strtolower(trim((string)($u->role ?? '')));
        $role = str_replace([' ', '-'], '_', $role);
        $role = preg_replace('/_+/', '_', $role) ?? $role;

        $deptId = $u->department_id !== null ? (int)$u->department_id : null;
        if ($deptId !== null && $deptId <= 0) $deptId = null;

        // ✅ CONFIG: decide access by role + department_id
        $allRoles  = ['admin', 'director', 'principal']; // gets ALL even if dept null
        $deptRoles = ['hod', 'faculty', 'technical_assistant', 'it_person', 'placement_officer', 'student']; // needs dept

        if (in_array($role, $allRoles, true)) {
            return ['mode' => 'all', 'department_id' => null];
        }

        if (in_array($role, $deptRoles, true)) {
            // none is based on role + dept id (your rule)
            if (!$deptId) return ['mode' => 'none', 'department_id' => null];
            return ['mode' => 'department', 'department_id' => $deptId];
        }

        return ['mode' => 'not_allowed', 'department_id' => null];
    }

    /**
     * Normalize actor information from request (compatible with your pattern)
     */
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
     * Resolve a department by id | uuid | slug (non-deleted)
     */
    protected function resolveDepartment($identifier, bool $includeDeleted = false)
    {
        $q = DB::table('departments');

        if (! $includeDeleted) {
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

    /**
     * Base query for curriculum_syllabuses with joins + common filters
     */
    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('curriculum_syllabuses as cs')
            ->leftJoin('departments as d', 'd.id', '=', 'cs.department_id')
            ->select([
                'cs.*',
                'd.title as department_title',
                'd.slug as department_slug',
                'd.uuid as department_uuid',
            ]);

        if (! $includeDeleted) {
            $q->whereNull('cs.deleted_at');
        }

        // search: ?q=
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('cs.title', 'like', $term)
                    ->orWhere('cs.slug', 'like', $term);
            });
        }

        // filter active: ?active=1/0
        if ($request->has('active')) {
            $active = filter_var($request->query('active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($active !== null) {
                $q->where('cs.active', $active);
            }
        }

        // filter department by ?department= (id|uuid|slug)
        if ($request->filled('department')) {
            $dept = $this->resolveDepartment($request->query('department'), true);
            if ($dept) {
                $q->where('cs.department_id', (int) $dept->id);
            } else {
                // no results if invalid department filter
                $q->whereRaw('1=0');
            }
        }

        // sort
        $sort = (string) $request->query('sort', 'created_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['created_at', 'title', 'id', 'sort_order'];
        if (! in_array($sort, $allowed, true)) {
            $sort = 'created_at';
        }

        $q->orderBy('cs.' . $sort, $dir);

        return $q;
    }

    /**
     * Resolve a syllabus by id|uuid|slug (optionally within department)
     */
    protected function resolveSyllabus(Request $request, $identifier, bool $includeDeleted = false, $departmentId = null)
    {
        $q = DB::table('curriculum_syllabuses as cs');

        if (! $includeDeleted) {
            $q->whereNull('cs.deleted_at');
        }

        if ($departmentId !== null) {
            $q->where('cs.department_id', (int) $departmentId);
        } else {
            // if slug is used globally and department query is supplied -> constrain
            if (!ctype_digit((string) $identifier) && !Str::isUuid((string) $identifier) && $request->filled('department')) {
                $dept = $this->resolveDepartment($request->query('department'), true);
                if ($dept) {
                    $q->where('cs.department_id', (int) $dept->id);
                }
            }
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('cs.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('cs.uuid', (string) $identifier);
        } else {
            $q->where('cs.slug', (string) $identifier)
              ->orderBy('cs.created_at', 'desc'); // if slug not unique globally
        }

        $row = $q->first();
        if (! $row) return null;

        // join dept details
        $dept = DB::table('departments')->where('id', (int) $row->department_id)->first();
        $row->department_title = $dept->title ?? null;
        $row->department_slug  = $dept->slug ?? null;
        $row->department_uuid  = $dept->uuid ?? null;

        return $row;
    }

    /**
     * Normalize row: decode metadata + add pdf_url
     */
    protected function normalizeRow($row): array
    {
        $arr = (array) $row;

        // metadata
        $meta = $arr['metadata'] ?? null;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // pdf url
        $path = (string) ($arr['pdf_path'] ?? '');
        $path = ltrim($path, '/');
        $arr['pdf_url'] = $path ? url('/' . $path) : null;

        return $arr;
    }

    /**
     * LIST (global)
     * Query: per_page, page, q, active, department, with_trashed, only_trashed, sort, direction
     */
    public function index(Request $request)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none') {
            return response()->json([
                'data' => [],
                'pagination' => [
                    'page'      => 1,
                    'per_page'  => max(1, min(200, (int) $request->query('per_page', 20))),
                    'total'     => 0,
                    'last_page' => 1,
                ],
            ], 200);
        }

        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        // ✅ Access filter
        if ($ac['mode'] === 'department') {
            $deptId = (int) $ac['department_id'];

            // if caller also passed ?department= and it's different -> no results
            if ($request->filled('department')) {
                $dept = $this->resolveDepartment($request->query('department'), true);
                if ($dept && (int)$dept->id !== $deptId) {
                    return response()->json([
                        'data' => [],
                        'pagination' => [
                            'page'      => 1,
                            'per_page'  => $perPage,
                            'total'     => 0,
                            'last_page' => 1,
                        ],
                    ], 200);
                }
            }

            $query->where('cs.department_id', $deptId);
        }

        if ($onlyDeleted) {
            $query->whereNotNull('cs.deleted_at');
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

    /**
     * LIST by department (nested)
     */
    public function indexByDepartment(Request $request, $department)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none') {
            return response()->json([
                'data' => [],
                'pagination' => [
                    'page'      => 1,
                    'per_page'  => max(1, min(200, (int) $request->query('per_page', 20))),
                    'total'     => 0,
                    'last_page' => 1,
                ],
            ], 200);
        }

        $dept = $this->resolveDepartment($department, false);
        if (! $dept) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        // ✅ Enforce department match for dept-scoped roles
        if ($ac['mode'] === 'department' && (int)$ac['department_id'] !== (int)$dept->id) {
            return response()->json(['error' => 'Not allowed'], 403);
        }

        // inject department filter
        $request->query->set('department', $dept->id);

        return $this->index($request);
    }

    /**
     * TRASH (global)
     */
    public function trash(Request $request)
    {
        $request->query->set('only_trashed', '1');
        return $this->index($request);
    }

    /**
     * SHOW single (id|uuid|slug)
     */
    public function show(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none') return response()->json(['message' => 'Curriculum & Syllabus not found'], 404);

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $deptScope = ($ac['mode'] === 'department') ? (int)$ac['department_id'] : null;

        $row = $this->resolveSyllabus($request, $identifier, $includeDeleted, $deptScope);
        if (! $row) {
            return response()->json(['message' => 'Curriculum & Syllabus not found'], 404);
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    /**
     * SHOW by department (nested)
     */
    public function showByDepartment(Request $request, $department, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none') return response()->json(['message' => 'Curriculum & Syllabus not found'], 404);

        $dept = $this->resolveDepartment($department, true);
        if (! $dept) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        // ✅ Enforce department match for dept-scoped roles
        if ($ac['mode'] === 'department' && (int)$ac['department_id'] !== (int)$dept->id) {
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveSyllabus(
            $request,
            $identifier,
            $includeDeleted,
            ($ac['mode'] === 'department') ? (int)$ac['department_id'] : (int)$dept->id
        );

        if (! $row) {
            return response()->json(['message' => 'Curriculum & Syllabus not found'], 404);
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    /**
     * STORE (upload PDF required)
     * Accepts:
     * - department_id (required) OR department (id|uuid|slug)
     * - title (required)
     * - slug (optional)
     * - pdf (required file: pdf)
     * - sort_order, active, metadata
     */
    public function store(Request $request)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none') return response()->json(['error' => 'Not allowed'], 403);

        $validated = $request->validate([
            'department_id' => ['required','integer','exists:departments,id'],
            'title'         => ['required','string','max:180'],
            'slug'          => ['nullable','string','max:200'],
            'active'        => ['nullable','in:0,1','boolean'],
            'sort_order'    => ['nullable','integer','min:0'],
            'pdf'           => ['required','file','mimes:pdf','max:20480'], // 20MB
        ]);

        // ✅ Enforce department scope for dept-scoped roles
        if ($ac['mode'] === 'department' && (int)$ac['department_id'] !== (int)$validated['department_id']) {
            return response()->json(['error' => 'Not allowed'], 403);
        }

        if (!$request->hasFile('pdf')) {
            return response()->json(['success' => false, 'message' => 'PDF file is required'], 422);
        }

        $pdf = $request->file('pdf');
        if (!$pdf || !$pdf->isValid()) {
            $code = $pdf ? $pdf->getError() : null;
            return response()->json([
                'success' => false,
                'message' => 'Upload failed' . ($code !== null ? " (code: {$code})" : ''),
            ], 422);
        }

        // ✅ Read meta BEFORE move (prevents SplFileInfo::getSize stat failed)
        $originalName = $pdf->getClientOriginalName();
        $mimeType     = $pdf->getClientMimeType() ?: $pdf->getMimeType();
        $fileSize     = (int) $pdf->getSize();
        $ext          = strtolower($pdf->getClientOriginalExtension() ?: 'pdf');

        // slug
        $slug = trim((string)($validated['slug'] ?? ''));
        $slug = $slug !== '' ? Str::slug($slug) : Str::slug($validated['title']);

        // ✅ ensure unique slug per department (ignoring soft-deleted rows)
        $baseSlug = $slug;
        $i = 2;
        while (DB::table('curriculum_syllabuses')
            ->where('department_id', (int)$validated['department_id'])
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $i++;
        }

        // destination (public/)
        $dirRel = 'depy_uploads/curriculum_syllabuses/' . (int)$validated['department_id'];
        $dirAbs = public_path($dirRel);
        if (!is_dir($dirAbs)) {
            @mkdir($dirAbs, 0775, true);
        }

        $filename = $slug . '-' . Str::random(6) . '.' . $ext;

        // ✅ Move file (tmp disappears after this)
        $pdf->move($dirAbs, $filename);

        $pdfPathRel = $dirRel . '/' . $filename; // store in DB

        $uuid = (string) Str::uuid();
        $now  = now();

        $id = DB::table('curriculum_syllabuses')->insertGetId([
            'uuid'          => $uuid,
            'department_id' => (int)$validated['department_id'],
            'title'         => $validated['title'],
            'slug'          => $slug,
            'active'        => (int)($validated['active'] ?? 1),
            'sort_order'    => (int)($validated['sort_order'] ?? 0),

            'pdf_path'      => $pdfPathRel,
            'original_name' => $originalName,
            'mime_type'     => $mimeType,
            'file_size'     => $fileSize,

            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $row = DB::table('curriculum_syllabuses')->where('id', $id)->first();

        return response()->json([
            'success' => true,
            'data'    => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    /**
     * STORE under department (nested)
     * POST /departments/{department}/curriculum-syllabuses
     */
    public function storeForDepartment(Request $request, $department)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none') return response()->json(['error' => 'Not allowed'], 403);

        $dept = $this->resolveDepartment($department, false);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        // ✅ Enforce department match for dept-scoped roles
        if ($ac['mode'] === 'department' && (int)$ac['department_id'] !== (int)$dept->id) {
            return response()->json(['error' => 'Not allowed'], 403);
        }

        // force department_id in request
        $request->merge(['department_id' => (int) $dept->id]);

        return $this->store($request);
    }

    /**
     * UPDATE (partial) + optional PDF replace
     */
    public function update(Request $request, string $uuid)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none') return response()->json(['error' => 'Not allowed'], 403);

        $row = DB::table('curriculum_syllabuses')->where('uuid', $uuid)->first();
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        // ✅ Enforce current row department for dept-scoped roles
        if ($ac['mode'] === 'department' && (int)$ac['department_id'] !== (int)$row->department_id) {
            return response()->json(['error' => 'Not allowed'], 403);
        }

        $validated = $request->validate([
            'department_id' => ['nullable','integer','exists:departments,id'],
            'title'         => ['nullable','string','max:180'],
            'slug'          => ['nullable','string','max:200'],
            'active'        => ['nullable','in:0,1','boolean'],
            'sort_order'    => ['nullable','integer','min:0'],
            'pdf'           => ['nullable','file','mimes:pdf','max:20480'],
        ]);

        // ✅ If trying to change department, enforce scope
        if ($ac['mode'] === 'department' && array_key_exists('department_id', $validated)) {
            if ((int)$validated['department_id'] !== (int)$ac['department_id']) {
                return response()->json(['error' => 'Not allowed'], 403);
            }
        }

        $update = ['updated_at' => now()];

        // normal fields
        foreach (['department_id','title','active','sort_order'] as $k) {
            if (array_key_exists($k, $validated)) {
                $update[$k] = ($k === 'department_id' || $k === 'sort_order')
                    ? (int) $validated[$k]
                    : ($k === 'active' ? (int) $validated[$k] : $validated[$k]);
            }
        }

        // slug update (if provided)
        $newSlug = null;
        if (array_key_exists('slug', $validated) && trim((string)$validated['slug']) !== '') {
            $newSlug = Str::slug($validated['slug']);
        }

        // if title changed but slug not provided, keep existing slug (your current behavior)
        if ($newSlug !== null) {
            // ensure unique slug per department (current/updated department)
            $depId = array_key_exists('department_id', $update) ? (int)$update['department_id'] : (int)$row->department_id;

            $base = $newSlug;
            $i = 2;
            while (DB::table('curriculum_syllabuses')
                ->where('department_id', $depId)
                ->where('slug', $newSlug)
                ->whereNull('deleted_at')
                ->where('uuid', '!=', $uuid)
                ->exists()
            ) {
                $newSlug = $base . '-' . $i++;
            }

            $update['slug'] = $newSlug;
        }

        // ✅ Replace PDF if provided
        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');
            if (!$pdf || !$pdf->isValid()) {
                $code = $pdf ? $pdf->getError() : null;
                return response()->json([
                    'success' => false,
                    'message' => 'Upload failed' . ($code !== null ? " (code: {$code})" : ''),
                ], 422);
            }

            // meta BEFORE move
            $originalName = $pdf->getClientOriginalName();
            $mimeType     = $pdf->getClientMimeType() ?: $pdf->getMimeType();
            $fileSize     = (int) $pdf->getSize();
            $ext          = strtolower($pdf->getClientOriginalExtension() ?: 'pdf');

            $depId = array_key_exists('department_id', $update) ? (int)$update['department_id'] : (int)$row->department_id;

            // destination
            $dirRel = 'depy_uploads/curriculum_syllabuses/' . $depId;
            $dirAbs = public_path($dirRel);
            if (!is_dir($dirAbs)) @mkdir($dirAbs, 0775, true);

            $useSlug = $update['slug'] ?? $row->slug ?? 'syllabus';
            $filename = $useSlug . '-' . Str::random(6) . '.' . $ext;

            // delete old file (if exists)
            if (!empty($row->pdf_path)) {
                $oldAbs = public_path(ltrim((string)$row->pdf_path, '/'));
                if (is_file($oldAbs)) @unlink($oldAbs);
            }

            $pdf->move($dirAbs, $filename);

            $pdfPathRel = $dirRel . '/' . $filename;

            $update['pdf_path']      = $pdfPathRel;
            $update['original_name'] = $originalName;
            $update['mime_type']     = $mimeType;
            $update['file_size']     = $fileSize;
        }

        DB::table('curriculum_syllabuses')->where('uuid', $uuid)->update($update);

        $fresh = DB::table('curriculum_syllabuses')->where('uuid', $uuid)->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    /**
     * Toggle active
     */
    public function toggleActive(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none') return response()->json(['error' => 'Not allowed'], 403);

        $deptScope = ($ac['mode'] === 'department') ? (int)$ac['department_id'] : null;

        $row = $this->resolveSyllabus($request, $identifier, true, $deptScope);
        if (! $row) return response()->json(['message' => 'Curriculum & Syllabus not found'], 404);

        $newActive = ! (bool) $row->active;

        DB::table('curriculum_syllabuses')
            ->where('id', (int) $row->id)
            ->update([
                'active'     => $newActive,
                'updated_at' => now(),
            ]);

        $fresh = $this->resolveSyllabus($request, (string) $row->id, true, $deptScope);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($fresh),
        ]);
    }

    /**
     * Soft-delete (move to bin)
     */
    public function destroy(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none') return response()->json(['error' => 'Not allowed'], 403);

        $deptScope = ($ac['mode'] === 'department') ? (int)$ac['department_id'] : null;

        $row = $this->resolveSyllabus($request, $identifier, false, $deptScope);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        DB::table('curriculum_syllabuses')
            ->where('id', (int) $row->id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Restore from bin
     */
    public function restore(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none') return response()->json(['error' => 'Not allowed'], 403);

        $deptScope = ($ac['mode'] === 'department') ? (int)$ac['department_id'] : null;

        $row = $this->resolveSyllabus($request, $identifier, true, $deptScope);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        DB::table('curriculum_syllabuses')
            ->where('id', (int) $row->id)
            ->update([
                'deleted_at' => null,
                'updated_at' => now(),
            ]);

        $fresh = $this->resolveSyllabus($request, (string) $row->id, true, $deptScope);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($fresh),
        ]);
    }

    /**
     * Permanent delete (also removes file if exists)
     */
    public function forceDelete(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none') return response()->json(['error' => 'Not allowed'], 403);

        $deptScope = ($ac['mode'] === 'department') ? (int)$ac['department_id'] : null;

        $row = $this->resolveSyllabus($request, $identifier, true, $deptScope);
        if (! $row) return response()->json(['message' => 'Not found'], 404);

        // delete file from public if exists
        $path = (string) ($row->pdf_path ?? '');
        if ($path !== '') {
            $abs = public_path(ltrim($path, '/'));
            if (File::exists($abs)) {
                @File::delete($abs);
            }
        }

        DB::table('curriculum_syllabuses')->where('id', (int) $row->id)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * STREAM (inline preview)
     */
    public function stream(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none') return response()->json(['message' => 'Not found'], 404);

        $deptScope = ($ac['mode'] === 'department') ? (int)$ac['department_id'] : null;

        $row = $this->resolveSyllabus($request, $identifier, false, $deptScope);
        if (! $row) return response()->json(['message' => 'Not found'], 404);

        $abs = public_path(ltrim((string) $row->pdf_path, '/'));
        if (! File::exists($abs)) return response()->json(['message' => 'PDF file missing'], 404);

        return response()->file($abs, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . ($row->original_name ?: 'syllabus.pdf') . '"',
        ]);
    }

    /**
     * DOWNLOAD (force download)
     */
    public function download(Request $request, $identifier)
    {
        $actorId = (int) $request->attributes->get('auth_tokenable_id');
        $ac = $this->accessControl($actorId);

        if ($ac['mode'] === 'not_allowed') return response()->json(['error' => 'Not allowed'], 403);
        if ($ac['mode'] === 'none') return response()->json(['message' => 'Not found'], 404);

        $deptScope = ($ac['mode'] === 'department') ? (int)$ac['department_id'] : null;

        $row = $this->resolveSyllabus($request, $identifier, false, $deptScope);
        if (! $row) return response()->json(['message' => 'Not found'], 404);

        $abs = public_path(ltrim((string) $row->pdf_path, '/'));
        if (! File::exists($abs)) return response()->json(['message' => 'PDF file missing'], 404);

        $name = $row->original_name ?: ('curriculum-syllabus-' . ($row->slug ?: $row->uuid) . '.pdf');

        return response()->download($abs, $name, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * PUBLIC LIST (no auth) -> only active + not deleted
     * GET /api/public/departments/{department}/curriculum-syllabuses
     */
    public function publicIndexByDepartment(Request $request, $department)
    {
        $dept = $this->resolveDepartment($department, false);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        $q = DB::table('curriculum_syllabuses')
            ->where('department_id', (int) $dept->id)
            ->whereNull('deleted_at')
            ->where('active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $items = array_map(fn ($r) => $this->normalizeRow($r), $q->all());

        return response()->json([
            'success'    => true,
            'department' => [
                'id'    => (int) $dept->id,
                'uuid'  => $dept->uuid,
                'slug'  => $dept->slug,
                'title' => $dept->title,
            ],
            'data' => $items,
        ]);
    }

    /**
     * PUBLIC STREAM/DOWNLOAD (no auth)
     */
    public function publicStream(Request $request, $identifier)   { return $this->stream($request, $identifier); }
    public function publicDownload(Request $request, $identifier) { return $this->download($request, $identifier); }
}
