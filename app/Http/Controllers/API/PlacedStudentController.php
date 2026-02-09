<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PlacedStudentController extends Controller
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

    private function jsonOrNull($value): ?string
    {
        if ($value === null) return null;
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return ($json === false) ? null : $json;
    }

    /**
     * Safe activity logger (never breaks main flow).
     */
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
            $actor = $this->actor($r);

            DB::table('user_data_activity_log')->insert([
                'performed_by'      => (int) ($actor['id'] ?? 0),
                'performed_by_role' => !empty($actor['role']) ? (string) $actor['role'] : null,
                'ip'                => $r->ip(),
                'user_agent'        => substr((string) ($r->userAgent() ?? ''), 0, 512),

                'activity'          => $activity,
                'module'            => $module,
                'table_name'        => $tableName,
                'record_id'         => $recordId !== null ? (int) $recordId : null,

                'changed_fields'    => $changedFields !== null ? $this->jsonOrNull(array_values($changedFields)) : null,
                'old_values'        => $oldValues !== null ? $this->jsonOrNull($oldValues) : null,
                'new_values'        => $newValues !== null ? $this->jsonOrNull($newValues) : null,

                'log_note'          => $note,

                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } catch (\Throwable $e) {
            // never block main request
        }
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

    protected function resolvePlacedStudent($identifier, bool $includeDeleted = false, $departmentId = null)
    {
        $q = DB::table('placed_students as ps');

        if (! $includeDeleted) $q->whereNull('ps.deleted_at');
        if ($departmentId !== null) $q->where('ps.department_id', (int) $departmentId);

        if (ctype_digit((string) $identifier)) {
            $q->where('ps.id', (int) $identifier);
        } elseif (Str::isUuid((string) $identifier)) {
            $q->where('ps.uuid', (string) $identifier);
        } else {
            return null;
        }

        $row = $q->first();
        if (! $row) return null;

        // attach department details (safe)
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

        // metadata json
        $meta = $arr['metadata'] ?? null;
        if (is_string($meta)) {
            $decoded = json_decode($meta, true);
            $arr['metadata'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        // offer letter url
        $arr['offer_letter_full_url'] = $this->toUrl($arr['offer_letter_url'] ?? null);

        return $arr;
    }

    protected function baseQuery(Request $request, bool $includeDeleted = false)
    {
        $q = DB::table('placed_students as ps')
            ->leftJoin('departments as d', 'd.id', '=', 'ps.department_id')
            ->select([
                'ps.*',
                'd.title as department_title',
                'd.slug  as department_slug',
                'd.uuid  as department_uuid',
            ]);

        if (! $includeDeleted) $q->whereNull('ps.deleted_at');

        // ?q= (role_title / note / uuid)
        if ($request->filled('q')) {
            $term = '%' . trim((string) $request->query('q')) . '%';
            $q->where(function ($sub) use ($term) {
                $sub->where('ps.role_title', 'like', $term)
                    ->orWhere('ps.note', 'like', $term)
                    ->orWhere('ps.uuid', 'like', $term);
            });
        }

        // ?status=active|inactive|verified
        if ($request->filled('status')) {
            $q->where('ps.status', (string) $request->query('status'));
        }

        // ?featured=1/0
        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) {
                $q->where('ps.is_featured_home', $featured ? 1 : 0);
            }
        }

        // ?department=id|uuid|slug
        if ($request->filled('department')) {
            $dept = $this->resolveDepartment($request->query('department'), true);
            if ($dept) $q->where('ps.department_id', (int) $dept->id);
            else $q->whereRaw('1=0');
        }

        // ?user_id=
        if ($request->filled('user_id') && ctype_digit((string)$request->query('user_id'))) {
            $q->where('ps.user_id', (int) $request->query('user_id'));
        }

        // ?placement_notice_id=
        if ($request->filled('placement_notice_id') && ctype_digit((string)$request->query('placement_notice_id'))) {
            $q->where('ps.placement_notice_id', (int) $request->query('placement_notice_id'));
        }

        // ?offer_date_from=YYYY-MM-DD&offer_date_to=YYYY-MM-DD
        if ($request->filled('offer_date_from')) $q->whereDate('ps.offer_date', '>=', $request->query('offer_date_from'));
        if ($request->filled('offer_date_to'))   $q->whereDate('ps.offer_date', '<=', $request->query('offer_date_to'));

        // ?joining_date_from=YYYY-MM-DD&joining_date_to=YYYY-MM-DD
        if ($request->filled('joining_date_from')) $q->whereDate('ps.joining_date', '>=', $request->query('joining_date_from'));
        if ($request->filled('joining_date_to'))   $q->whereDate('ps.joining_date', '<=', $request->query('joining_date_to'));

        // sort
        $sort = (string) $request->query('sort', 'created_at');
        $dir  = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowed = ['created_at', 'updated_at', 'offer_date', 'joining_date', 'sort_order', 'ctc', 'id'];
        if (! in_array($sort, $allowed, true)) $sort = 'created_at';

        $q->orderBy('ps.' . $sort, $dir);

        return $q;
    }

    protected function uploadOfferLetterToPublic($file, string $dirRel, string $prefix): array
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

    /* ============================================
     | CRUD
     |============================================ */

    public function index(Request $request)
    {
        $perPage = max(1, min(200, (int) $request->query('per_page', 20)));

        $includeDeleted = filter_var($request->query('with_trashed', false), FILTER_VALIDATE_BOOLEAN);
        $onlyDeleted    = filter_var($request->query('only_trashed', false), FILTER_VALIDATE_BOOLEAN);

        $query = $this->baseQuery($request, $includeDeleted || $onlyDeleted);

        if ($onlyDeleted) $query->whereNotNull('ps.deleted_at');

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

        $row = $this->resolvePlacedStudent($identifier, $includeDeleted);
        if (! $row) return response()->json(['message' => 'Placed student not found'], 404);

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

        $row = $this->resolvePlacedStudent($identifier, $includeDeleted, $dept->id);
        if (! $row) return response()->json(['message' => 'Placed student not found'], 404);

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
            'placement_notice_id'  => ['nullable', 'integer', 'exists:placement_notices,id'],
            'user_id'              => ['required', 'integer', 'exists:users,id'],

            'role_title'           => ['nullable', 'string', 'max:255'],
            'ctc'                  => ['nullable', 'numeric', 'min:0', 'max:9999.99'],

            'offer_date'           => ['nullable', 'date'],
            'joining_date'         => ['nullable', 'date'],

            'offer_letter_url'     => ['nullable', 'string', 'max:255'],
            'offer_letter_file'    => ['nullable', 'file', 'max:20480'], // 20MB

            'note'                 => ['nullable', 'string'],
            'is_featured_home'     => ['nullable', 'in:0,1', 'boolean'],
            'sort_order'           => ['nullable', 'integer', 'min:0'],
            'status'               => ['nullable', 'in:active,inactive,verified'],
            'metadata'             => ['nullable'],
        ]);

        $uuid = (string) Str::uuid();
        $now  = now();

        // metadata normalize
        $metadata = $request->input('metadata', null);
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
        }

        // offer letter: string path/url OR file upload
        $offerLetterPath = $validated['offer_letter_url'] ?? null;

        if ($request->hasFile('offer_letter_file')) {
            $f = $request->file('offer_letter_file');
            if (!$f || !$f->isValid()) {
                return response()->json(['success' => false, 'message' => 'Offer letter upload failed'], 422);
            }

            $deptKey = !empty($validated['department_id']) ? (string) ((int) $validated['department_id']) : 'global';
            $dirRel  = 'depy_uploads/placed_students/' . $deptKey;

            $meta = $this->uploadOfferLetterToPublic($f, $dirRel, 'offer-letter-' . $uuid);
            $offerLetterPath = $meta['path'];
        }

        $insert = [
            'uuid'                => $uuid,
            'department_id'        => $validated['department_id'] ?? null,
            'placement_notice_id'  => $validated['placement_notice_id'] ?? null,
            'user_id'              => (int) $validated['user_id'],

            'role_title'           => $validated['role_title'] ?? null,
            'ctc'                  => array_key_exists('ctc', $validated) ? $validated['ctc'] : null,

            'offer_date'           => !empty($validated['offer_date']) ? Carbon::parse($validated['offer_date'])->toDateString() : null,
            'joining_date'         => !empty($validated['joining_date']) ? Carbon::parse($validated['joining_date'])->toDateString() : null,

            'offer_letter_url'     => $offerLetterPath ? trim((string)$offerLetterPath) : null,
            'note'                 => $validated['note'] ?? null,

            'is_featured_home'     => (int) ($validated['is_featured_home'] ?? 0),
            'sort_order'           => (int) ($validated['sort_order'] ?? 0),
            'status'               => (string) ($validated['status'] ?? 'active'),

            'created_by'           => $actor['id'] ?: null,
            'created_at'           => $now,
            'updated_at'           => $now,
            'created_at_ip'        => $request->ip(),
            'updated_at_ip'        => $request->ip(),
            'deleted_at'           => null,
            'metadata'             => $metadata !== null ? json_encode($metadata) : null,
        ];

        $id = DB::table('placed_students')->insertGetId($insert);

        $row = DB::table('placed_students')->where('id', (int) $id)->first();

        // LOG (POST)
        $this->logActivity(
            $request,
            'create',
            'placed_students',
            'placed_students',
            $id,
            array_merge(['id'], array_keys($insert)),
            null,
            array_merge(['id' => (int) $id], $insert),
            'Placed student created'
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
        $row = $this->resolvePlacedStudent($identifier, true);
        if (! $row) return response()->json(['message' => 'Placed student not found'], 404);

        // capture before snapshot (placed_students only)
        $beforeObj = DB::table('placed_students')->where('id', (int) $row->id)->first();
        $before = $beforeObj ? (array) $beforeObj : (array) $row;

        $validated = $request->validate([
            'department_id'         => ['nullable', 'integer', 'exists:departments,id'],
            'placement_notice_id'   => ['nullable', 'integer', 'exists:placement_notices,id'],
            'user_id'               => ['nullable', 'integer', 'exists:users,id'],

            'role_title'            => ['nullable', 'string', 'max:255'],
            'ctc'                   => ['nullable', 'numeric', 'min:0', 'max:9999.99'],

            'offer_date'            => ['nullable', 'date'],
            'joining_date'          => ['nullable', 'date'],

            'offer_letter_url'      => ['nullable', 'string', 'max:255'],
            'offer_letter_file'     => ['nullable', 'file', 'max:20480'], // 20MB
            'offer_letter_remove'   => ['nullable', 'in:0,1', 'boolean'],

            'note'                  => ['nullable', 'string'],
            'is_featured_home'      => ['nullable', 'in:0,1', 'boolean'],
            'sort_order'            => ['nullable', 'integer', 'min:0'],
            'status'                => ['nullable', 'in:active,inactive,verified'],
            'metadata'              => ['nullable'],
        ]);

        $update = [
            'updated_at'    => now(),
            'updated_at_ip' => $request->ip(),
        ];

        foreach (['department_id','placement_notice_id','user_id','role_title','note','status'] as $k) {
            if (array_key_exists($k, $validated)) {
                $update[$k] = $validated[$k] !== null ? $validated[$k] : null;
            }
        }

        if (array_key_exists('ctc', $validated)) {
            $update['ctc'] = $validated['ctc'] !== null ? $validated['ctc'] : null;
        }

        if (array_key_exists('offer_date', $validated)) {
            $update['offer_date'] = !empty($validated['offer_date'])
                ? Carbon::parse($validated['offer_date'])->toDateString()
                : null;
        }

        if (array_key_exists('joining_date', $validated)) {
            $update['joining_date'] = !empty($validated['joining_date'])
                ? Carbon::parse($validated['joining_date'])->toDateString()
                : null;
        }

        if (array_key_exists('is_featured_home', $validated)) {
            $update['is_featured_home'] = (int) $validated['is_featured_home'];
        }

        if (array_key_exists('sort_order', $validated)) {
            $update['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        }

        // metadata normalize
        if (array_key_exists('metadata', $validated)) {
            $metadata = $request->input('metadata', null);
            if (is_string($metadata)) {
                $decoded = json_decode($metadata, true);
                if (json_last_error() === JSON_ERROR_NONE) $metadata = $decoded;
            }
            $update['metadata'] = $metadata !== null ? json_encode($metadata) : null;
        }

        // offer letter remove
        if (filter_var($request->input('offer_letter_remove', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->deletePublicPath($row->offer_letter_url ?? null);
            $update['offer_letter_url'] = null;
        }

        // offer letter set by url/path (string)
        if (array_key_exists('offer_letter_url', $validated) && trim((string)$validated['offer_letter_url']) !== '') {
            // if previously local file, do NOT auto delete unless remove flag is sent
            $update['offer_letter_url'] = trim((string) $validated['offer_letter_url']);
        }

        // offer letter replace by file upload (takes priority)
        if ($request->hasFile('offer_letter_file')) {
            $f = $request->file('offer_letter_file');
            if (!$f || !$f->isValid()) {
                return response()->json(['success' => false, 'message' => 'Offer letter upload failed'], 422);
            }

            // delete old local file (safe)
            $this->deletePublicPath($row->offer_letter_url ?? null);

            $newDeptId = array_key_exists('department_id', $validated)
                ? ($validated['department_id'] !== null ? (int) $validated['department_id'] : null)
                : ($row->department_id !== null ? (int) $row->department_id : null);

            $deptKey = $newDeptId ? (string) $newDeptId : 'global';
            $dirRel  = 'depy_uploads/placed_students/' . $deptKey;

            $meta = $this->uploadOfferLetterToPublic($f, $dirRel, 'offer-letter-' . (string)$row->uuid);
            $update['offer_letter_url'] = $meta['path'];
        }

        DB::table('placed_students')->where('id', (int) $row->id)->update($update);

        $freshObj = DB::table('placed_students')->where('id', (int) $row->id)->first();
        $freshArr = $freshObj ? (array) $freshObj : null;

        // LOG (PUT/PATCH)
        $changed = [];
        $oldVals = [];
        $newVals = [];
        if ($freshArr) {
            foreach (array_keys($freshArr) as $k) {
                if (in_array($k, ['updated_at', 'updated_at_ip'], true)) continue;

                $old = $before[$k] ?? null;
                $new = $freshArr[$k] ?? null;

                // normalize simple comparisons
                if (is_numeric($old) && is_numeric($new)) {
                    if ((string)$old === (string)$new) continue;
                } else {
                    if ($old === $new) continue;
                    if ((string)$old === (string)$new) continue;
                }

                $changed[]     = $k;
                $oldVals[$k]   = $old;
                $newVals[$k]   = $new;
            }
        } else {
            // fallback: log intended fields from $update
            foreach ($update as $k => $v) {
                if (in_array($k, ['updated_at', 'updated_at_ip'], true)) continue;
                $changed[]   = $k;
                $oldVals[$k] = $before[$k] ?? null;
                $newVals[$k] = $v;
            }
        }

        $this->logActivity(
            $request,
            'update',
            'placed_students',
            'placed_students',
            (int) $row->id,
            $changed,
            $oldVals ?: null,
            $newVals ?: null,
            'Placed student updated'
        );

        return response()->json([
            'success' => true,
            'data'    => $freshObj ? $this->normalizeRow($freshObj) : null,
        ]);
    }

    public function toggleFeatured(Request $request, $identifier)
    {
        $row = $this->resolvePlacedStudent($identifier, true);
        if (! $row) return response()->json(['message' => 'Placed student not found'], 404);

        $oldVal = (int) ($row->is_featured_home ?? 0);
        $newVal = $oldVal ? 0 : 1;

        DB::table('placed_students')->where('id', (int) $row->id)->update([
            'is_featured_home' => $newVal,
            'updated_at'       => now(),
            'updated_at_ip'    => $request->ip(),
        ]);

        $fresh = DB::table('placed_students')->where('id', (int) $row->id)->first();

        // LOG (PATCH)
        $this->logActivity(
            $request,
            'update',
            'placed_students',
            'placed_students',
            (int) $row->id,
            ['is_featured_home'],
            ['is_featured_home' => $oldVal],
            ['is_featured_home' => $newVal],
            'Placed student featured flag toggled'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function destroy(Request $request, $identifier)
    {
        $row = $this->resolvePlacedStudent($identifier, false);
        if (! $row) return response()->json(['message' => 'Not found or already deleted'], 404);

        $ts = now();

        DB::table('placed_students')->where('id', (int) $row->id)->update([
            'deleted_at'    => $ts,
            'updated_at'    => $ts,
            'updated_at_ip' => $request->ip(),
        ]);

        // LOG (DELETE - soft delete)
        $this->logActivity(
            $request,
            'delete',
            'placed_students',
            'placed_students',
            (int) $row->id,
            ['deleted_at'],
            ['deleted_at' => $row->deleted_at ?? null],
            ['deleted_at' => $ts->toDateTimeString()],
            'Placed student soft deleted'
        );

        return response()->json(['success' => true]);
    }

    public function restore(Request $request, $identifier)
    {
        $row = $this->resolvePlacedStudent($identifier, true);
        if (! $row || $row->deleted_at === null) {
            return response()->json(['message' => 'Not found in bin'], 404);
        }

        $ts = now();

        DB::table('placed_students')->where('id', (int) $row->id)->update([
            'deleted_at'    => null,
            'updated_at'    => $ts,
            'updated_at_ip' => $request->ip(),
        ]);

        $fresh = DB::table('placed_students')->where('id', (int) $row->id)->first();

        // LOG (POST/PATCH - restore)
        $this->logActivity(
            $request,
            'restore',
            'placed_students',
            'placed_students',
            (int) $row->id,
            ['deleted_at'],
            ['deleted_at' => $row->deleted_at],
            ['deleted_at' => null],
            'Placed student restored from bin'
        );

        return response()->json([
            'success' => true,
            'data'    => $fresh ? $this->normalizeRow($fresh) : null,
        ]);
    }

    public function forceDelete(Request $request, $identifier)
    {
        $row = $this->resolvePlacedStudent($identifier, true);
        if (! $row) return response()->json(['message' => 'Placed student not found'], 404);

        // snapshot before delete (for logs)
        $beforeObj = DB::table('placed_students')->where('id', (int) $row->id)->first();
        $before = $beforeObj ? (array) $beforeObj : (array) $row;

        // delete offer letter if local
        $this->deletePublicPath($row->offer_letter_url ?? null);

        DB::table('placed_students')->where('id', (int) $row->id)->delete();

        // LOG (DELETE - force delete)
        $this->logActivity(
            $request,
            'force_delete',
            'placed_students',
            'placed_students',
            (int) $row->id,
            null,
            $before ?: null,
            null,
            'Placed student permanently deleted'
        );

        return response()->json(['success' => true]);
    }

    /* ============================================
     | Public Index
     | GET /api/public/placed-students
     |============================================ */

    public function publicindex(Request $request)
    {
        $page    = max(1, (int)$request->query('page', 1));
        $perPage = (int)$request->query('per_page', 12);
        $perPage = max(6, min(60, $perPage));

        $qText  = trim((string)$request->query('q', ''));
        $status = trim((string)$request->query('status', 'active')) ?: 'active';

        // accept both: ?department= or ?dept=
        $deptParam = $request->query('department', $request->query('dept', null));

        $sort = (string)$request->query('sort', 'created_at');
        $dir  = strtolower((string)$request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['created_at','updated_at','offer_date','joining_date','sort_order','ctc','id'];
        if (!in_array($sort, $allowedSort, true)) $sort = 'created_at';

        $base = DB::table('placed_students as ps')
            ->leftJoin('users as u', 'u.id', '=', 'ps.user_id')
            ->leftJoin('departments as d', 'd.id', '=', 'ps.department_id')
            ->whereNull('ps.deleted_at')
            ->whereNull('u.deleted_at')
            ->where(function ($w) {
                // allow null department or non-deleted department
                $w->whereNull('ps.department_id')
                  ->orWhereNull('d.deleted_at');
            });

        // default public: active only (unless you pass another status)
        if ($status !== '') {
            $base->where('ps.status', $status);
        }

        // featured filter
        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($featured !== null) {
                $base->where('ps.is_featured_home', $featured ? 1 : 0);
            }
        }

        // department filter (id|uuid|slug|title)
        if (!empty($deptParam)) {
            $dept = $this->resolveDepartment($deptParam);
            if ($dept) $base->where('ps.department_id', (int)$dept->id);
            else $base->whereRaw('1=0');
        }

        // search (NO d.name)
        if ($qText !== '') {
            $term = '%' . $qText . '%';
            $base->where(function ($w) use ($term) {
                $w->where('u.name', 'like', $term)
                  ->orWhere('ps.role_title', 'like', $term)
                  ->orWhere('d.title', 'like', $term)
                  ->orWhere('d.slug', 'like', $term);
            });
        }

        // total + pagination
        $total    = (clone $base)->distinct('ps.id')->count('ps.id');
        $lastPage = max(1, (int)ceil($total / $perPage));

        $rows = (clone $base)
            ->select([
                'ps.id',
                'ps.uuid',
                'ps.user_id',
                'ps.department_id',
                'ps.role_title',
                'ps.ctc',
                'ps.offer_date',
                'ps.joining_date',
                'ps.note',
                'ps.is_featured_home',
                'ps.sort_order',
                'ps.status',
                'ps.created_at',
                'ps.updated_at',

                'd.title as department_title',
                'd.slug  as department_slug',
                'd.uuid  as department_uuid',

                // IMPORTANT for card click: /user/profile/{user_uuid}
                'u.uuid as user_uuid',
                'u.name as user_name',
                'u.image as user_image',
            ])
            ->orderBy('ps.' . $sort, $dir)
            ->orderBy('ps.id', 'desc')
            ->forPage($page, $perPage)
            ->get();

        $items = $rows->map(fn ($r) => $this->normalizeRow($r))->values()->all();

        return response()->json([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'page'      => $page,
                'per_page'  => $perPage,
                'total'     => $total,
                'last_page' => $lastPage,
            ],
        ]);
    }
}
