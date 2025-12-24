<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class CurriculumSyllabusController extends Controller
{
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
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

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
        $dept = $this->resolveDepartment($department, false);
        if (! $dept) {
            return response()->json(['message' => 'Department not found'], 404);
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
        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveSyllabus($request, $identifier, $includeDeleted);
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
        $dept = $this->resolveDepartment($department, true);
        if (! $dept) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveSyllabus($request, $identifier, $includeDeleted, $dept->id);
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
        $v = Validator::make($request->all(), [
            'department_id' => 'nullable|integer',
            'department'    => 'nullable|string', // id|uuid|slug alternative

            'title'      => 'required|string|max:180',
            'slug'       => 'nullable|string|max:200',
            'sort_order' => 'sometimes|integer|min:0|max:1000000',
            'active'     => 'sometimes|boolean',
            'metadata'   => 'nullable|array',

            'pdf'        => 'required|file|mimes:pdf|max:20480', // 20MB
        ]);

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $data  = $v->validated();
        $actor = $this->actor($request);
        $ip    = $request->ip();

        // resolve department
        $dept = null;
        if (!empty($data['department_id'])) {
            $dept = $this->resolveDepartment((string) $data['department_id'], false);
        } elseif (!empty($data['department'])) {
            $dept = $this->resolveDepartment((string) $data['department'], false);
        }
        if (! $dept) {
            return response()->json(['message' => 'Valid department is required'], 422);
        }

        // slug generation (unique per department among non-deleted)
        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '') {
            $base = Str::slug($data['title']) ?: 'curriculum-syllabus';
            $slug = $base; $i = 1;
            while (
                DB::table('curriculum_syllabuses')
                    ->where('department_id', (int) $dept->id)
                    ->where('slug', $slug)
                    ->whereNull('deleted_at')
                    ->exists()
            ) {
                $slug = $base . '-' . $i++;
            }
        } else {
            $slug = Str::slug($slug) ?: 'curriculum-syllabus';
            $exists = DB::table('curriculum_syllabuses')
                ->where('department_id', (int) $dept->id)
                ->where('slug', $slug)
                ->whereNull('deleted_at')
                ->exists();
            if ($exists) {
                return response()->json(['errors' => ['slug' => ['Slug already exists for this department.']]], 422);
            }
        }

        // file move to /public/depy_uploads/curriculum_syllabus/dept-{id}/
        $uuid = (string) Str::uuid();
        $dirRel = 'depy_uploads/curriculum_syllabus/dept-' . (int) $dept->id;
        $dirAbs = public_path($dirRel);

        if (! File::exists($dirAbs)) {
            File::makeDirectory($dirAbs, 0755, true);
        }

        $file = $request->file('pdf');
        $fileName = $uuid . '.pdf';
        $file->move($dirAbs, $fileName);

        $pdfPath = $dirRel . '/' . $fileName;

        $payload = [
            'uuid'          => $uuid,
            'department_id' => (int) $dept->id,
            'title'         => $data['title'],
            'slug'          => $slug,

            'pdf_path'      => $pdfPath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getClientMimeType() ?: 'application/pdf',
            'file_size'     => (int) ($file->getSize() ?: 0),

            'sort_order'    => array_key_exists('sort_order', $data) ? (int) $data['sort_order'] : 0,
            'active'        => array_key_exists('active', $data) ? (bool) $data['active'] : true,

            'created_by'    => $actor['id'] ?: null,
            'created_at_ip' => $ip,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];

        if (array_key_exists('metadata', $data)) {
            $payload['metadata'] = $data['metadata'] !== null ? json_encode($data['metadata']) : null;
        }

        $id = DB::table('curriculum_syllabuses')->insertGetId($payload);

        $row = $this->resolveSyllabus($request, (string) $id, true);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ], 201);
    }

    /**
     * STORE under department (nested)
     * POST /departments/{department}/curriculum-syllabuses
     */
    public function storeForDepartment(Request $request, $department)
    {
        $dept = $this->resolveDepartment($department, false);
        if (! $dept) return response()->json(['message' => 'Department not found'], 404);

        // force department_id in request
        $request->merge(['department_id' => (int) $dept->id]);

        return $this->store($request);
    }

    /**
     * UPDATE (partial) + optional PDF replace
     */
    public function update(Request $request, $identifier)
    {
        $row = $this->resolveSyllabus($request, $identifier, true);
        if (! $row) {
            return response()->json(['message' => 'Curriculum & Syllabus not found'], 404);
        }

        $v = Validator::make($request->all(), [
            'title'      => 'sometimes|required|string|max:180',
            'slug'       => [
                'sometimes',
                'nullable',
                'string',
                'max:200',
                Rule::unique('curriculum_syllabuses', 'slug')
                    ->ignore($row->id)
                    ->where(fn ($q) => $q->where('department_id', (int) $row->department_id)->whereNull('deleted_at')),
            ],
            'sort_order' => 'sometimes|integer|min:0|max:1000000',
            'active'     => 'sometimes|boolean',
            'metadata'   => 'sometimes|nullable|array',

            'pdf'        => 'sometimes|file|mimes:pdf|max:20480',
        ]);

        if ($v->fails()) return response()->json(['errors' => $v->errors()], 422);

        $data    = $v->validated();
        $payload = [];

        if (array_key_exists('title', $data)) {
            $payload['title'] = $data['title'];
        }

        if (array_key_exists('slug', $data)) {
            $slug = trim((string) ($data['slug'] ?? ''));
            $payload['slug'] = $slug === '' ? null : (Str::slug($slug) ?: 'curriculum-syllabus');
        } elseif (array_key_exists('title', $data)) {
            // title changed but slug not provided -> regenerate slug
            $base = Str::slug($data['title']) ?: 'curriculum-syllabus';
            $slug = $base; $i = 1;
            while (
                DB::table('curriculum_syllabuses')
                    ->where('department_id', (int) $row->department_id)
                    ->where('id', '!=', (int) $row->id)
                    ->where('slug', $slug)
                    ->whereNull('deleted_at')
                    ->exists()
            ) {
                $slug = $base . '-' . $i++;
            }
            $payload['slug'] = $slug;
        }

        if (array_key_exists('sort_order', $data)) {
            $payload['sort_order'] = (int) $data['sort_order'];
        }

        if (array_key_exists('active', $data)) {
            $payload['active'] = (bool) $data['active'];
        }

        if (array_key_exists('metadata', $data)) {
            $payload['metadata'] = $data['metadata'] !== null ? json_encode($data['metadata']) : null;
        }

        // optional PDF replace
        if ($request->hasFile('pdf')) {
            $deptId = (int) $row->department_id;

            $dirRel = 'depy_uploads/curriculum_syllabus/dept-' . $deptId;
            $dirAbs = public_path($dirRel);
            if (! File::exists($dirAbs)) {
                File::makeDirectory($dirAbs, 0755, true);
            }

            $file = $request->file('pdf');
            $newName = (string) $row->uuid . '.pdf'; // keep same uuid-based filename
            $file->move($dirAbs, $newName);

            $payload['pdf_path']      = $dirRel . '/' . $newName;
            $payload['original_name'] = $file->getClientOriginalName();
            $payload['mime_type']     = $file->getClientMimeType() ?: 'application/pdf';
            $payload['file_size']     = (int) ($file->getSize() ?: 0);
        }

        // if slug became null (because user passed empty), regenerate properly
        if (array_key_exists('slug', $payload) && ($payload['slug'] === null)) {
            $base = Str::slug($payload['title'] ?? $row->title) ?: 'curriculum-syllabus';
            $slug = $base; $i = 1;
            while (
                DB::table('curriculum_syllabuses')
                    ->where('department_id', (int) $row->department_id)
                    ->where('id', '!=', (int) $row->id)
                    ->where('slug', $slug)
                    ->whereNull('deleted_at')
                    ->exists()
            ) {
                $slug = $base . '-' . $i++;
            }
            $payload['slug'] = $slug;
        }

        if (! empty($payload)) {
            $payload['updated_at'] = now();
            DB::table('curriculum_syllabuses')->where('id', (int) $row->id)->update($payload);
        }

        $fresh = $this->resolveSyllabus($request, (string) $row->id, true);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($fresh),
        ]);
    }

    /**
     * Toggle active
     */
    public function toggleActive(Request $request, $identifier)
    {
        $row = $this->resolveSyllabus($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Curriculum & Syllabus not found'], 404);

        $newActive = ! (bool) $row->active;

        DB::table('curriculum_syllabuses')
            ->where('id', (int) $row->id)
            ->update([
                'active'     => $newActive,
                'updated_at' => now(),
            ]);

        $fresh = $this->resolveSyllabus($request, (string) $row->id, true);

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
        $row = $this->resolveSyllabus($request, $identifier, false);
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
        $row = $this->resolveSyllabus($request, $identifier, true);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        DB::table('curriculum_syllabuses')
            ->where('id', (int) $row->id)
            ->update([
                'deleted_at' => null,
                'updated_at' => now(),
            ]);

        $fresh = $this->resolveSyllabus($request, (string) $row->id, true);

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
        $row = $this->resolveSyllabus($request, $identifier, true);
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
        $row = $this->resolveSyllabus($request, $identifier, false);
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
        $row = $this->resolveSyllabus($request, $identifier, false);
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
