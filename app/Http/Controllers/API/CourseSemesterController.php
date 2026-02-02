<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CourseSemesterController extends Controller
{
    /* ============================================
     | Helpers
     |============================================ */

    /** Cache schema checks to avoid repeated queries */
    protected array $colCache = [];

    private function actor(Request $r): array
    {
        return [
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? optional($r->user())->id ?? 0),
            'role' => (string) ($r->attributes->get('auth_role') ?? ($r->user()->role ?? '')),
            'type' => (string) ($r->attributes->get('auth_tokenable_type') ?? ($r->user() ? get_class($r->user()) : '')),
            'uuid' => (string) ($r->attributes->get('auth_user_uuid') ?? ($r->user()->uuid ?? '')),
        ];
    }

    protected function hasCol(string $table, string $col): bool
    {
        $k = $table . '.' . $col;
        if (array_key_exists($k, $this->colCache)) return (bool) $this->colCache[$k];

        try {
            return $this->colCache[$k] = Schema::hasColumn($table, $col);
        } catch (\Throwable $e) {
            return $this->colCache[$k] = false;
        }
    }

    protected function toUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') return null;
        if (preg_match('~^https?://~i', $path)) return $path;
        return url('/' . ltrim($path, '/'));
    }

    protected function codeDefault(int $semesterNo): string
    {
        return 'SEM-' . $semesterNo;
    }

    protected function slugDefault(?string $title, int $semesterNo): string
    {
        $t = trim((string) $title);
        $slug = $t !== '' ? Str::slug($t) : '';
        if ($slug === '') $slug = 'semester-' . $semesterNo;
        return $slug;
    }

    protected function normalizeMetadata($meta): ?array
    {
        if ($meta === null) return null;
        if (is_array($meta)) return $meta;

        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : null;
        }

        return null;
    }

    protected function normalizeRow($row): array
    {
        $arr = (array) $row;

        // decode metadata
        $arr['metadata'] = $this->normalizeMetadata($arr['metadata'] ?? null);

        // syllabus url
        $arr['syllabus_url_full'] = $this->toUrl($arr['syllabus_url'] ?? null);

        // ✅ FIX: Ensure code/slug are ALWAYS present (generate if missing)
        $semNo = (int) ($arr['semester_no'] ?? 0);
        $title = (string) ($arr['title'] ?? '');

        $code = trim((string) ($arr['code'] ?? ''));
        $slug = trim((string) ($arr['slug'] ?? ''));

        // fallback to metadata if columns not present / empty
        $meta = $arr['metadata'] ?? [];
        if ($code === '' && is_array($meta)) $code = trim((string) ($meta['code'] ?? ''));
        if ($slug === '' && is_array($meta)) $slug = trim((string) ($meta['slug'] ?? ''));

        if ($code === '' && $semNo > 0) $code = $this->codeDefault($semNo);
        if ($slug === '' && $semNo > 0) $slug = $this->slugDefault($title, $semNo);

        $arr['code'] = $code ?: null;
        $arr['slug'] = $slug ?: null;

        return $arr;
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

    protected function resolveCourse($identifier, bool $includeDeleted = false, $departmentId = null)
    {
        $q = DB::table('courses as c');
        if (! $includeDeleted) $q->whereNull('c.deleted_at');

        if ($departmentId !== null) {
            $q->where('c.department_id', (int) $departmentId);
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('c.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('c.uuid', (string) $identifier);
        } else {
            $q->where('c.slug', (string) $identifier);
        }

        return $q->first();
    }

    protected function resolveSemester(Request $request, $identifier, bool $includeDeleted = false, $courseId = null, $departmentId = null)
    {
        $q = DB::table('course_semesters as s');
        if (! $includeDeleted) $q->whereNull('s.deleted_at');

        if ($courseId !== null) {
            $q->where('s.course_id', (int) $courseId);
        }

        if ($departmentId !== null) {
            $q->where(function ($w) use ($departmentId) {
                $w->whereNull('s.department_id')
                  ->orWhere('s.department_id', (int) $departmentId);
            });
        }

        if (ctype_digit((string) $identifier)) {
            $q->where('s.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('s.uuid', (string) $identifier);
        } else {
            $q->whereRaw('1=0');
        }

        $row = $q->first();
        if (! $row) return null;

        // attach course + dept details (for convenience)
        $course = DB::table('courses')->where('id', (int) $row->course_id)->first();
        $row->course_title = $course->title ?? null;
        $row->course_slug  = $course->slug ?? null;
        $row->course_uuid  = $course->uuid ?? null;

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

    protected function applyVisibleWindow($q): void
    {
        $now = now();

        $q->whereNull('s.deleted_at')
          ->where('s.status', 'active')
          ->where(function ($w) use ($now) {
              $w->whereNull('s.publish_at')->orWhere('s.publish_at', '<=', $now);
          })
          ->where(function ($w) use ($now) {
              $w->whereNull('s.expire_at')->orWhere('s.expire_at', '>', $now);
          });
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('course_semesters as s')
            ->join('courses as c', 'c.id', '=', 's.course_id')
            ->leftJoin('departments as d', 'd.id', '=', 's.department_id')
            ->select([
                's.*',
                'c.title as course_title',
                'c.slug  as course_slug',
                'c.uuid  as course_uuid',
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
                $sub->where('s.title', 'like', $term)
                    ->orWhere('s.description', 'like', $term);
            });
        }

        // ?status=active|inactive
        if ($request->filled('status')) {
            $q->where('s.status', (string) $request->query('status'));
        }

        // ?course=id|uuid|slug
        if ($request->filled('course')) {
            $course = $this->resolveCourse($request->query('course'), true);
            if ($course) $q->where('s.course_id', (int) $course->id);
            else $q->whereRaw('1=0');
        }

        // ?department=id|uuid|slug
        if ($request->filled('department')) {
            $dept = $this->resolveDepartment($request->query('department'), true);
            if ($dept) $q->where('s.department_id', (int) $dept->id);
            else $q->whereRaw('1=0');
        }

        // ?semester_no=1
        if ($request->filled('semester_no')) {
            $q->where('s.semester_no', (int) $request->query('semester_no'));
        }

        // ?visible_now=1
        if ($request->has('visible_now')) {
            $visible = filter_var($request->query('visible_now'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($visible) $this->applyVisibleWindow($q);
        }

        // sort
        $sort = (string) $request->query('sort', 'sort_order');
        $dir  = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        // ✅ FIX: allow updated_at (your UI uses -updated_at)
        $allowed = ['sort_order', 'semester_no', 'created_at', 'updated_at', 'publish_at', 'expire_at', 'id'];
        if (! in_array($sort, $allowed, true)) $sort = 'sort_order';

        $q->orderBy('s.' . $sort, $dir)
          ->orderBy('s.semester_no', 'asc');

        return $q;
    }

    /* ============================================
     | CRUD
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

    public function indexByCourse(Request $request, $course)
    {
        $c = $this->resolveCourse($course, false);
        if (! $c) return response()->json(['message' => 'Course not found'], 404);

        $request->query->set('course', $c->id);
        return $this->index($request);
    }

    public function indexByDepartment(Request $request, $department)
    {
        $d = $this->resolveDepartment($department, false);
        if (! $d) return response()->json(['message' => 'Department not found'], 404);

        $request->query->set('department', $d->id);
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

        $row = $this->resolveSemester($request, $identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Course semester not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function showByCourse(Request $request, $course, $identifier)
    {
        $c = $this->resolveCourse($course, true);
        if (! $c) return response()->json(['message' => 'Course not found'], 404);

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $row = $this->resolveSemester($request, $identifier, $includeDeleted, $c->id);
        if (! $row) return response()->json(['message' => 'Course semester not found'], 404);

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }

    public function store(Request $request)
    {
        $actor = $this->actor($request);

        $validated = $request->validate([
            'course_id'      => ['required', 'integer', 'exists:courses,id'],
            'department_id'  => ['nullable', 'integer', 'exists:departments,id'],

            'semester_no'    => ['required', 'integer', 'min:1', 'max:50'],
            'title'          => ['nullable', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],

            // ✅ accept code/slug from UI
            'code'           => ['nullable', 'string', 'max:80'],
            'slug'           => ['nullable', 'string', 'max:160'],

            'total_credits'  => ['nullable', 'integer', 'min:0'],
            'syllabus_url'   => ['nullable', 'string', 'max:255'],

            'syllabus_file'  => ['nullable', 'file', 'max:20480'],

            'sort_order'     => ['nullable', 'integer', 'min:0'],
            'status'         => ['nullable', 'in:active,inactive'],
            'publish_at'     => ['nullable', 'date'],
            'expire_at'      => ['nullable', 'date'],

            'metadata'       => ['nullable'],
        ]);

        $uuid = (string) Str::uuid();
        $now  = now();

        $semesterNo = (int) $validated['semester_no'];
        $title      = (string) ($validated['title'] ?? '');

        // ✅ generate if missing
        $code = trim((string) ($validated['code'] ?? ''));
        $slug = trim((string) ($validated['slug'] ?? ''));
        if ($code === '') $code = $this->codeDefault($semesterNo);
        if ($slug === '') $slug = $this->slugDefault($title, $semesterNo);

        // syllabus upload (optional)
        $syllabusPath = $validated['syllabus_url'] ?? null;
        if ($request->hasFile('syllabus_file')) {
            $f = $request->file('syllabus_file');
            if (!$f || !$f->isValid()) {
                return response()->json(['success' => false, 'message' => 'Syllabus upload failed'], 422);
            }
            $dirRel = 'depy_uploads/course_semesters/' . ((int) $validated['course_id']);
            $metaUp = $this->uploadFileToPublic($f, $dirRel, 'semester-' . $semesterNo . '-syllabus');
            $syllabusPath = $metaUp['path'];
        }

        // metadata normalize + inject code/slug if columns not present
        $metadata = $request->input('metadata', null);
        $metaArr = $this->normalizeMetadata($metadata) ?? [];

        $hasCodeCol = $this->hasCol('course_semesters', 'code');
        $hasSlugCol = $this->hasCol('course_semesters', 'slug');

        if (!$hasCodeCol) $metaArr['code'] = $code;
        if (!$hasSlugCol) $metaArr['slug'] = $slug;

        $insert = [
            'uuid'          => $uuid,
            'course_id'     => (int) $validated['course_id'],
            'department_id' => $validated['department_id'] ?? null,

            'semester_no'   => $semesterNo,
            'title'         => $validated['title'] ?? null,
            'description'   => $validated['description'] ?? null,

            'total_credits' => array_key_exists('total_credits', $validated)
                ? ($validated['total_credits'] !== null ? (int) $validated['total_credits'] : null)
                : 0,

            'syllabus_url'  => $syllabusPath,

            'sort_order'    => (int) ($validated['sort_order'] ?? 0),
            'status'        => (string) ($validated['status'] ?? 'active'),

            'publish_at'    => !empty($validated['publish_at']) ? Carbon::parse($validated['publish_at']) : null,
            'expire_at'     => !empty($validated['expire_at']) ? Carbon::parse($validated['expire_at']) : null,

            'created_by'    => $actor['id'] ?: null,

            'created_at'    => $now,
            'updated_at'    => $now,
            'created_at_ip' => $request->ip(),
            'updated_at_ip' => $request->ip(),
        ];

        // if columns exist, store there too
        if ($hasCodeCol) $insert['code'] = $code;
        if ($hasSlugCol) $insert['slug'] = $slug;

        $insert['metadata'] = !empty($metaArr) ? json_encode($metaArr) : null;

        $id = DB::table('course_semesters')->insertGetId($insert);

        $row = DB::table('course_semesters')->where('id', $id)->first();

        return response()->json([
            'success' => true,
            'data'    => $row ? $this->normalizeRow($row) : null,
        ]);
    }

    public function storeForCourse(Request $request, $course)
    {
        $c = $this->resolveCourse($course, false);
        if (! $c) return response()->json(['message' => 'Course not found'], 404);

        $request->merge(['course_id' => (int) $c->id]);
        return $this->store($request);
    }

    public function update(Request $request, $identifier)
    {
        $row = $this->resolveSemester($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Course semester not found'], 404);

        $validated = $request->validate([
            'course_id'       => ['nullable', 'integer', 'exists:courses,id'],
            'department_id'   => ['nullable', 'integer', 'exists:departments,id'],

            'semester_no'     => ['nullable', 'integer', 'min:1', 'max:50'],
            'title'           => ['nullable', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],

            // ✅ accept code/slug from UI
            'code'            => ['nullable', 'string', 'max:80'],
            'slug'            => ['nullable', 'string', 'max:160'],

            'total_credits'   => ['nullable', 'integer', 'min:0'],
            'syllabus_url'    => ['nullable', 'string', 'max:255'],

            'syllabus_file'   => ['nullable', 'file', 'max:20480'],
            'syllabus_remove' => ['nullable', 'in:0,1', 'boolean'],

            'sort_order'      => ['nullable', 'integer', 'min:0'],
            'status'          => ['nullable', 'in:active,inactive'],
            'publish_at'      => ['nullable', 'date'],
            'expire_at'       => ['nullable', 'date'],

            'metadata'        => ['nullable'],
        ]);

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        foreach ([
            'course_id','department_id','semester_no','title','description',
            'total_credits','syllabus_url','sort_order','status'
        ] as $k) {
            if (array_key_exists($k, $validated)) {
                $update[$k] = $validated[$k];
            }
        }

        if (array_key_exists('publish_at', $validated)) {
            $update['publish_at'] = !empty($validated['publish_at']) ? Carbon::parse($validated['publish_at']) : null;
        }
        if (array_key_exists('expire_at', $validated)) {
            $update['expire_at'] = !empty($validated['expire_at']) ? Carbon::parse($validated['expire_at']) : null;
        }

        $hasCodeCol = $this->hasCol('course_semesters', 'code');
        $hasSlugCol = $this->hasCol('course_semesters', 'slug');

        // ✅ compute effective semNo/title for generating code/slug when missing
        $effectiveSemNo = (int) ($update['semester_no'] ?? $row->semester_no ?? 0);
        $effectiveTitle = (string) ($update['title'] ?? $row->title ?? '');

        // requested code/slug (or generate if still missing)
        $reqCode = array_key_exists('code', $validated) ? trim((string) ($validated['code'] ?? '')) : '';
        $reqSlug = array_key_exists('slug', $validated) ? trim((string) ($validated['slug'] ?? '')) : '';

        // If client didn't send, try existing (row/metadata)
        $rowArr = (array) $row;
        $rowMeta = $this->normalizeMetadata($rowArr['metadata'] ?? null) ?? [];

        $curCode = trim((string) ($rowArr['code'] ?? ''));
        $curSlug = trim((string) ($rowArr['slug'] ?? ''));

        if ($curCode === '' && isset($rowMeta['code'])) $curCode = trim((string) $rowMeta['code']);
        if ($curSlug === '' && isset($rowMeta['slug'])) $curSlug = trim((string) $rowMeta['slug']);

        $finalCode = $reqCode !== '' ? $reqCode : $curCode;
        $finalSlug = $reqSlug !== '' ? $reqSlug : $curSlug;

        if ($finalCode === '' && $effectiveSemNo > 0) $finalCode = $this->codeDefault($effectiveSemNo);
        if ($finalSlug === '' && $effectiveSemNo > 0) $finalSlug = $this->slugDefault($effectiveTitle, $effectiveSemNo);

        // Save to columns if exist
        if ($hasCodeCol) $update['code'] = $finalCode;
        if ($hasSlugCol) $update['slug'] = $finalSlug;

        // metadata: merge if provided OR if columns don't exist (so UI still gets code/slug)
        $metaArr = null;

        if (array_key_exists('metadata', $validated)) {
            $metaArr = $this->normalizeMetadata($request->input('metadata', null)) ?? [];
        } else {
            // only touch metadata if we must store code/slug there due to missing columns
            if (!$hasCodeCol || !$hasSlugCol) {
                $metaArr = $rowMeta;
            }
        }

        if (is_array($metaArr)) {
            if (!$hasCodeCol) $metaArr['code'] = $finalCode;
            if (!$hasSlugCol) $metaArr['slug'] = $finalSlug;
            $update['metadata'] = !empty($metaArr) ? json_encode($metaArr) : null;
        }

        // syllabus remove
        if (filter_var($request->input('syllabus_remove', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->deletePublicPath($row->syllabus_url ?? null);
            $update['syllabus_url'] = null;
        }

        // syllabus replace via file
        if ($request->hasFile('syllabus_file')) {
            $f = $request->file('syllabus_file');
            if (!$f || !$f->isValid()) {
                return response()->json(['success' => false, 'message' => 'Syllabus upload failed'], 422);
            }

            $this->deletePublicPath($row->syllabus_url ?? null);

            $courseId   = (int) ($update['course_id'] ?? $row->course_id);
            $semesterNo = (int) ($update['semester_no'] ?? $row->semester_no);

            $dirRel = 'depy_uploads/course_semesters/' . $courseId;
            $metaUp = $this->uploadFileToPublic($f, $dirRel, 'semester-' . $semesterNo . '-syllabus');
            $update['syllabus_url'] = $metaUp['path'];
        }

        DB::table('course_semesters')->where('id', (int) $row->id)->update($update);

        $fresh = DB::table('course_semesters')->where('id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolveSemester($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        DB::table('course_semesters')->where('id', (int) $row->id)->update([
            'deleted_at'    => now(),
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolveSemester($request, $identifier, true);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        DB::table('course_semesters')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('course_semesters')->where('id', (int) $row->id)->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolveSemester($request, $identifier, true);
        if (! $row) return response()->json(['message' => 'Course semester not found'], 404);

        $this->deletePublicPath($row->syllabus_url ?? null);

        DB::table('course_semesters')->where('id', (int) $row->id)->delete();

        return response()->json(['success' => true]);
    }

    /* ============================================
     | Public (no auth)
     |============================================ */

    public function publicIndex(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 50)));

        $q = $this->baseQuery($request, true);
        $this->applyVisibleWindow($q);

        $q->orderBy('s.sort_order', 'asc')
          ->orderBy('s.semester_no', 'asc');

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

    public function publicIndexByCourse(Request $request, $course)
    {
        $c = $this->resolveCourse($course, false);
        if (! $c) return response()->json(['message' => 'Course not found'], 404);

        $request->query->set('course', $c->id);
        return $this->publicIndex($request);
    }

    public function publicShow(Request $request, $identifier)
    {
        $row = $this->resolveSemester($request, $identifier, false);
        if (! $row) return response()->json(['message' => 'Course semester not found'], 404);

        $now = now();
        $isVisible =
            ($row->status === 'active') &&
            (empty($row->publish_at) || Carbon::parse($row->publish_at)->lte($now)) &&
            (empty($row->expire_at)  || Carbon::parse($row->expire_at)->gt($now));

        if (! $isVisible) {
            return response()->json(['message' => 'Course semester not available'], 404);
        }

        return response()->json([
            'success' => true,
            'item'    => $this->normalizeRow($row),
        ]);
    }
    public function importCsv(Request $request)
{
    $actor = $this->actor($request);

    // Accept either "csv" or "file" as input name (frontend can use any)
    $request->validate([
        'csv'  => ['nullable', 'file', 'max:20480', 'mimes:csv,txt'],
        'file' => ['nullable', 'file', 'max:20480', 'mimes:csv,txt'],
    ]);

    $file = $request->file('csv') ?: $request->file('file');
    if (!$file || !$file->isValid()) {
        return response()->json(['success' => false, 'message' => 'CSV file is required'], 422);
    }

    $path = $file->getRealPath();
    $handle = @fopen($path, 'r');
    if (!$handle) {
        return response()->json(['success' => false, 'message' => 'Unable to read CSV file'], 422);
    }

    // ---------- helpers ----------
    $normHeader = function ($h) {
        $h = (string) $h;
        // remove UTF-8 BOM if present
        $h = preg_replace('/^\xEF\xBB\xBF/', '', $h);
        $h = trim($h);
        $h = strtolower($h);
        // normalize separators
        $h = preg_replace('/[^a-z0-9]+/', '_', $h);
        $h = trim($h, '_');
        return $h;
    };

    $pickIndex = function (array $map, array $candidates) {
        foreach ($candidates as $k) {
            if (array_key_exists($k, $map)) return $map[$k];
        }
        return null;
    };

    $cell = function (array $row, $idx) {
        if ($idx === null) return '';
        return isset($row[$idx]) ? trim((string) $row[$idx]) : '';
    };

    // ---------- read header ----------
    $header = fgetcsv($handle);
    if (!$header || !is_array($header)) {
        fclose($handle);
        return response()->json(['success' => false, 'message' => 'CSV header row is missing'], 422);
    }

    $headerMap = [];
    foreach ($header as $i => $h) {
        $headerMap[$normHeader($h)] = $i;
    }

    // Required columns (flexible matching)
    $idxDept = $pickIndex($headerMap, [
        'department', 'department_uuid', 'dept', 'dept_uuid'
    ]);
    $idxCourse = $pickIndex($headerMap, [
        'course', 'course_uuid'
    ]);
    $idxSemNo = $pickIndex($headerMap, [
        'semester_no', 'semester_no_', 'semester', 'semester_no', 'semester_number',
        'semester_no', 'sem_no', 'sem', 'semester_no', 'semester_no', 'semester_no__',
        'semester_no', 'semester_no.', 'semester_no_',
        'semester_no', 'semester_no__',
        // common "Semester No." normalizes to "semester_no"
        'semester_no'
    ]);
    // also allow "semester_no" from "Semester No."
    if ($idxSemNo === null && isset($headerMap['semester_no'])) $idxSemNo = $headerMap['semester_no'];

    $idxTitle = $pickIndex($headerMap, [
        'semester_title', 'title', 'semester', 'semester_name'
    ]);
    $idxCode = $pickIndex($headerMap, [
        'code'
    ]);
    $idxSlug = $pickIndex($headerMap, [
        'slug'
    ]);

    // Validate required columns exist
    $missing = [];
    if ($idxDept === null)   $missing[] = 'Department';
    if ($idxCourse === null) $missing[] = 'Course';
    if ($idxSemNo === null)  $missing[] = 'Semester No.';
    if ($idxTitle === null)  $missing[] = 'Semester Title';

    if (!empty($missing)) {
        fclose($handle);
        return response()->json([
            'success' => false,
            'message' => 'Missing required CSV columns: ' . implode(', ', $missing),
        ], 422);
    }

    // Column existence checks (avoid SQL errors on older schema)
    $hasCodeCol   = $this->hasCol('course_semesters', 'code');
    $hasSlugCol   = $this->hasCol('course_semesters', 'slug');
    $hasMetaCol   = $this->hasCol('course_semesters', 'metadata');
    $hasDescCol   = $this->hasCol('course_semesters', 'description');
    $hasSortCol   = $this->hasCol('course_semesters', 'sort_order');
    $hasStatusCol = $this->hasCol('course_semesters', 'status');
    $hasPubCol    = $this->hasCol('course_semesters', 'publish_at');
    $hasExpCol    = $this->hasCol('course_semesters', 'expire_at');
    $hasCByCol    = $this->hasCol('course_semesters', 'created_by');
    $hasCAipCol   = $this->hasCol('course_semesters', 'created_at_ip');
    $hasUAipCol   = $this->hasCol('course_semesters', 'updated_at_ip');
    $hasCreditsCol= $this->hasCol('course_semesters', 'total_credits');
    $hasSylCol    = $this->hasCol('course_semesters', 'syllabus_url');
    $hasDeletedAt = $this->hasCol('course_semesters', 'deleted_at');

    $now = now();
    $ip  = $request->ip();

    // Cache UUID->row for speed
    $deptCache = [];
    $courseCache = [];

    $inserted = 0;
    $skipped  = 0;
    $failed   = [];

    $rowNum = 1; // header = 1
    DB::beginTransaction();
    try {
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            // Skip empty lines
            $allEmpty = true;
            foreach ($row as $v) { if (trim((string)$v) !== '') { $allEmpty = false; break; } }
            if ($allEmpty) continue;

            $deptUuid  = $cell($row, $idxDept);
            $courseUuid= $cell($row, $idxCourse);
            $semNoRaw  = $cell($row, $idxSemNo);
            $title     = $cell($row, $idxTitle);
            $code      = $cell($row, $idxCode);
            $slug      = $cell($row, $idxSlug);

            // Basic validations
            if (!$deptUuid || !Str::isUuid($deptUuid)) {
                $failed[] = ['row' => $rowNum, 'message' => 'Invalid Department UUID'];
                continue;
            }
            if (!$courseUuid || !Str::isUuid($courseUuid)) {
                $failed[] = ['row' => $rowNum, 'message' => 'Invalid Course UUID'];
                continue;
            }

            $semNo = (int) trim((string)$semNoRaw);
            if ($semNo <= 0 || $semNo > 50) {
                $failed[] = ['row' => $rowNum, 'message' => 'Semester No. must be 1..50'];
                continue;
            }
            if (!$title || mb_strlen($title) > 255) {
                $failed[] = ['row' => $rowNum, 'message' => 'Semester Title is required (max 255)'];
                continue;
            }

            if ($code !== '' && mb_strlen($code) > 80) {
                $failed[] = ['row' => $rowNum, 'message' => 'Code max length is 80'];
                continue;
            }
            if ($slug !== '' && mb_strlen($slug) > 160) {
                $failed[] = ['row' => $rowNum, 'message' => 'Slug max length is 160'];
                continue;
            }

            // Resolve department by UUID (not deleted)
            if (!array_key_exists($deptUuid, $deptCache)) {
                $dq = DB::table('departments')->where('uuid', $deptUuid);
                if ($this->hasCol('departments', 'deleted_at')) $dq->whereNull('deleted_at');
                $deptCache[$deptUuid] = $dq->first();
            }
            $dept = $deptCache[$deptUuid];
            if (!$dept) {
                $failed[] = ['row' => $rowNum, 'message' => 'Department not found for UUID: ' . $deptUuid];
                continue;
            }

            // Resolve course by UUID (not deleted)
            if (!array_key_exists($courseUuid, $courseCache)) {
                $cq = DB::table('courses')->where('uuid', $courseUuid);
                if ($this->hasCol('courses', 'deleted_at')) $cq->whereNull('deleted_at');
                $courseCache[$courseUuid] = $cq->first();
            }
            $course = $courseCache[$courseUuid];
            if (!$course) {
                $failed[] = ['row' => $rowNum, 'message' => 'Course not found for UUID: ' . $courseUuid];
                continue;
            }

            // Ensure course belongs to department (recommended for consistency)
            if (!empty($course->department_id) && (int)$course->department_id !== (int)$dept->id) {
                $failed[] = [
                    'row' => $rowNum,
                    'message' => 'Course does not belong to given Department (course.department_id mismatch)'
                ];
                continue;
            }

            // Defaults if not provided
            if ($code === '') $code = $this->codeDefault($semNo);
            if ($slug === '') $slug = $this->slugDefault($title, $semNo);

            // Skip duplicates (same course_id + semester_no) if exists and not deleted
            $existsQ = DB::table('course_semesters')
                ->where('course_id', (int)$course->id)
                ->where('semester_no', (int)$semNo);

            if ($hasDeletedAt) $existsQ->whereNull('deleted_at');

            $exists = $existsQ->first();
            if ($exists) {
                $skipped++;
                continue;
            }

            // Build insert
            $uuid = (string) Str::uuid();
            $ins = [
                'uuid'        => $uuid,
                'course_id'   => (int) $course->id,
                'department_id' => (int) $dept->id,
                'semester_no' => (int) $semNo,
                'title'       => $title,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];

            if ($hasDescCol)   $ins['description'] = null; // CSV doesn't include description
            if ($hasSortCol)   $ins['sort_order'] = 0;
            if ($hasStatusCol) $ins['status'] = 'active';
            if ($hasPubCol)    $ins['publish_at'] = null;
            if ($hasExpCol)    $ins['expire_at'] = null;
            if ($hasCreditsCol)$ins['total_credits'] = 0;
            if ($hasSylCol)    $ins['syllabus_url'] = null;

            if ($hasCByCol)  $ins['created_by'] = $actor['id'] ?: null;
            if ($hasCAipCol) $ins['created_at_ip'] = $ip;
            if ($hasUAipCol) $ins['updated_at_ip'] = $ip;

            // Save code/slug to columns if exist
            if ($hasCodeCol) $ins['code'] = $code;
            if ($hasSlugCol) $ins['slug'] = $slug;

            // Otherwise store them in metadata (keeps UI logic consistent)
            if ($hasMetaCol && (!$hasCodeCol || !$hasSlugCol)) {
                $meta = [];
                if (!$hasCodeCol) $meta['code'] = $code;
                if (!$hasSlugCol) $meta['slug'] = $slug;
                $ins['metadata'] = json_encode($meta);
            }

            DB::table('course_semesters')->insert($ins);
            $inserted++;
        }

        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        fclose($handle);
        return response()->json([
            'success' => false,
            'message' => 'Import failed: ' . $e->getMessage(),
        ], 500);
    }

    fclose($handle);

    return response()->json([
        'success'  => true,
        'message'  => 'Import completed',
        'inserted' => $inserted,
        'skipped'  => $skipped,
        'failed'   => count($failed),
        'errors'   => array_slice($failed, 0, 50), // keep response light
    ]);
}

}
