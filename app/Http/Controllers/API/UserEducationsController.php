<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserEducationsController extends Controller
{
    private string $table = 'user_educations';

    /* =========================
     * Helpers (token-driven)
     * ========================= */

    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
            'type' => $request->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    private function requireRole(Request $r, array $allowed)
    {
        $a = $this->actor($r);
        if (!$a['role'] || !in_array($a['role'], $allowed, true)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }
        return null;
    }

    private function fetchUserByUuid(string $uuid)
    {
        return DB::table('users')
            ->where('uuid', $uuid)
            ->whereNull('deleted_at')
            ->first();
    }

    private function fetchUserById(int $id)
    {
        return DB::table('users')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Resolve target user:
     * - If user_uuid provided (and not "me") => by uuid
     * - Else => by token user (auth_tokenable_id)
     */
    private function resolveTargetUser(Request $request, ?string $user_uuid)
    {
        $user_uuid = $user_uuid !== null ? trim($user_uuid) : null;

        if (!$user_uuid || strtolower($user_uuid) === 'me') {
            $actor = $this->actor($request);
            if (!$actor['id']) return null;
            return $this->fetchUserById((int) $actor['id']);
        }

        return $this->fetchUserByUuid($user_uuid);
    }

    private function canAccess(Request $request, int $userId): bool
    {
        $actor = $this->actor($request);
        if (!$actor['id']) return false;
        if ($actor['id'] === $userId) return true;

        return in_array($actor['role'], [
            'admin','director','principal','hod','technical_assistant','it_person'
        ], true);
    }

    private function decodeMetadataRow($row)
    {
        if ($row && isset($row->metadata) && is_string($row->metadata)) {
            $decoded = json_decode($row->metadata, true);
            $row->metadata = is_array($decoded) ? $decoded : null;
        }
        return $row;
    }

    private function decodeMetadataCollection($rows)
    {
        foreach ($rows as $r) {
            if (isset($r->metadata) && is_string($r->metadata)) {
                $decoded = json_decode($r->metadata, true);
                $r->metadata = is_array($decoded) ? $decoded : null;
            }
        }
        return $rows;
    }

    private function normalizeMetadata($raw)
    {
        // Accept: null, '', array, JSON string
        if ($raw === null || $raw === '') return null;

        if (is_array($raw)) return $raw;

        if (is_string($raw)) {
            $s = trim($raw);
            if ($s === '') return null;

            $decoded = json_decode($s, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                // allow object too (assoc array)
                if (is_array($decoded)) return $decoded;
                return '__INVALID__';
            }
            return $decoded;
        }

        return '__INVALID__';
    }

    private function hasCol(string $col): bool
    {
        try { return Schema::hasColumn($this->table, $col); }
        catch (\Throwable $e) { return false; }
    }

    /**
     * If your education "certificate" stores a public path like:
     *  - "/assets/media/..."
     * then this safely deletes the file from public/ when hard deleting.
     */
    private function deletePublicAssetIfAny(?string $path): void
    {
        if (!$path || !is_string($path)) return;

        // only allow deleting inside public/assets/... to avoid accidents
        if (!str_starts_with($path, '/assets/')) return;

        $abs = public_path(ltrim($path, '/'));
        if (is_file($abs)) @unlink($abs);
    }

    /**
     * ✅ Prevent accidental double submit
     * Finds a very recent "same" education record for same user.
     * You can tweak which fields define "same".
     */
    private function findRecentDuplicate(int $userId, array $data)
    {
        $q = DB::table($this->table)
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->where('education_level', $data['education_level'])
            ->where('institution_name', $data['institution_name']);

        // optional match fields (only if present)
        if (array_key_exists('degree_title', $data)) {
            $data['degree_title'] === null ? $q->whereNull('degree_title') : $q->where('degree_title', $data['degree_title']);
        }
        if (array_key_exists('field_of_study', $data)) {
            $data['field_of_study'] === null ? $q->whereNull('field_of_study') : $q->where('field_of_study', $data['field_of_study']);
        }
        if (array_key_exists('passing_year', $data)) {
            $data['passing_year'] === null ? $q->whereNull('passing_year') : $q->where('passing_year', $data['passing_year']);
        }

        // window (same as your conference logic)
        $q->where('created_at', '>=', Carbon::now()->subSeconds(20));

        return $q->orderBy('id', 'desc')->first();
    }

    /* =========================
     * CRUD
     * Supports BOTH:
     * - /api/users/{user_uuid}/educations...
     * - /api/me/educations...
     * ========================= */

    public function index(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int) $user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $rows = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->orderByRaw('passing_year IS NULL, passing_year DESC')
            ->orderBy('id','desc')
            ->get();

        $rows = $this->decodeMetadataCollection($rows);

        return response()->json(['success'=>true,'data'=>$rows]);
    }

    public function show(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int) $user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success'=>false,'error'=>'Record not found'], 404);

        $row = $this->decodeMetadataRow($row);

        return response()->json(['success'=>true,'data'=>$row]);
    }

    public function store(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int) $user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        // ✅ allow metadata as array OR JSON string
        $metaNorm = $this->normalizeMetadata($request->input('metadata'));
        if ($metaNorm === '__INVALID__') {
            return response()->json(['success'=>false,'errors'=>['metadata'=>['Metadata must be valid JSON array/object']]], 422);
        }

        $v = Validator::make($request->all(), [
            'education_level'  => ['required','string','max:100'],
            'degree_title'     => ['nullable','string','max:255'],
            'field_of_study'   => ['nullable','string','max:255'],
            'institution_name' => ['required','string','max:255'],
            'university_name'  => ['nullable','string','max:255'],
            'enrollment_year'  => ['nullable','integer','min:1900','max:'.(int)date('Y')],
            'passing_year'     => ['nullable','integer','min:1900','max:'.(int)date('Y')],
            'grade_type'       => ['nullable','string','max:50'],
            'grade_value'      => ['nullable','string','max:50'],
            'location'         => ['nullable','string','max:255'],
            'description'      => ['nullable','string'],
            'certificate'      => ['nullable','string','max:255'],
            // allow any, because we normalized above
            'metadata'         => ['nullable'],
        ]);

        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()], 422);

        $data  = $v->validated();
        $data['metadata'] = $metaNorm;

        // ✅ DUPLICATE PREVENTION (recent)
        $dup = $this->findRecentDuplicate((int)$user->id, [
            'education_level'  => $data['education_level'],
            'institution_name' => $data['institution_name'],
            'degree_title'     => $data['degree_title'] ?? null,
            'field_of_study'   => $data['field_of_study'] ?? null,
            'passing_year'     => $data['passing_year'] ?? null,
        ]);
        if ($dup) {
            $dup = $this->decodeMetadataRow($dup);
            return response()->json([
                'success' => true,
                'data' => $dup,
                'message' => 'Duplicate submit prevented'
            ], 200);
        }

        $actor = $this->actor($request);
        $now   = Carbon::now();

        $insert = [
            'uuid'             => (string) Str::uuid(),
            'user_id'          => (int) $user->id,

            'education_level'  => $data['education_level'],
            'degree_title'     => $data['degree_title'] ?? null,
            'field_of_study'   => $data['field_of_study'] ?? null,
            'institution_name' => $data['institution_name'],
            'university_name'  => $data['university_name'] ?? null,
            'enrollment_year'  => $data['enrollment_year'] ?? null,
            'passing_year'     => $data['passing_year'] ?? null,
            'grade_type'       => $data['grade_type'] ?? null,
            'grade_value'      => $data['grade_value'] ?? null,
            'location'         => $data['location'] ?? null,
            'description'      => $data['description'] ?? null,
            'certificate'      => $data['certificate'] ?? null,

            'metadata'         => ($metaNorm !== null) ? json_encode($metaNorm) : null,

            'created_at'       => $now,
            'updated_at'       => $now,
        ];

        if ($this->hasCol('created_by'))    $insert['created_by'] = $actor['id'] ?: null;
        if ($this->hasCol('created_at_ip')) $insert['created_at_ip'] = $request->ip();
        if ($this->hasCol('updated_by'))    $insert['updated_by'] = $actor['id'] ?: null;
        if ($this->hasCol('updated_at_ip')) $insert['updated_at_ip'] = $request->ip();

        $id = DB::table($this->table)->insertGetId($insert);

        $row = DB::table($this->table)->where('id', $id)->first();
        $row = $this->decodeMetadataRow($row);

        return response()->json(['success'=>true,'data'=>$row], 201);
    }

    public function update(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int) $user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success'=>false,'error'=>'Record not found'], 404);

        $metaNorm = $this->normalizeMetadata($request->input('metadata'));
        if ($metaNorm === '__INVALID__') {
            return response()->json(['success'=>false,'errors'=>['metadata'=>['Metadata must be valid JSON array/object']]], 422);
        }

        $v = Validator::make($request->all(), [
            'education_level'  => ['sometimes','required','string','max:100'],
            'degree_title'     => ['sometimes','nullable','string','max:255'],
            'field_of_study'   => ['sometimes','nullable','string','max:255'],
            'institution_name' => ['sometimes','required','string','max:255'],
            'university_name'  => ['sometimes','nullable','string','max:255'],
            'enrollment_year'  => ['sometimes','nullable','integer','min:1900','max:'.(int)date('Y')],
            'passing_year'     => ['sometimes','nullable','integer','min:1900','max:'.(int)date('Y')],
            'grade_type'       => ['sometimes','nullable','string','max:50'],
            'grade_value'      => ['sometimes','nullable','string','max:50'],
            'location'         => ['sometimes','nullable','string','max:255'],
            'description'      => ['sometimes','nullable','string'],
            'certificate'      => ['sometimes','nullable','string','max:255'],
            'metadata'         => ['sometimes','nullable'],
        ]);

        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()], 422);

        $data  = $v->validated();
        $actor = $this->actor($request);
        $upd   = [];

        foreach ($data as $k => $val) {
            if ($k === 'metadata') continue; // handle below
            $upd[$k] = $val;
        }

        if ($request->has('metadata')) {
            $upd['metadata'] = ($metaNorm !== null) ? json_encode($metaNorm) : null;
        }

        $upd['updated_at'] = Carbon::now();
        if ($this->hasCol('updated_by'))    $upd['updated_by'] = $actor['id'] ?: null;
        if ($this->hasCol('updated_at_ip')) $upd['updated_at_ip'] = $request->ip();

        DB::table($this->table)->where('id', $row->id)->update($upd);

        $fresh = DB::table($this->table)->where('id', $row->id)->first();
        $fresh = $this->decodeMetadataRow($fresh);

        return response()->json(['success'=>true,'data'=>$fresh]);
    }

    /**
     * SOFT DELETE
     */
    public function destroy(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int) $user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success'=>false,'error'=>'Record not found'], 404);

        $actor = $this->actor($request);
        $now   = Carbon::now();

        $upd = [
            'deleted_at' => $now,
            'updated_at' => $now,
        ];
        if ($this->hasCol('updated_by'))    $upd['updated_by'] = $actor['id'] ?: null;
        if ($this->hasCol('updated_at_ip')) $upd['updated_at_ip'] = $request->ip();

        DB::table($this->table)->where('id', $row->id)->update($upd);

        return response()->json(['success'=>true,'message'=>'Education deleted']);
    }

    /* ==========================================================
     * Trash / Restore / Hard Delete
     * ========================================================== */

    public function indexDeleted(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int) $user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $rows = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->orderBy('deleted_at','desc')
            ->orderBy('id','desc')
            ->get();

        $rows = $this->decodeMetadataCollection($rows);

        return response()->json(['success'=>true,'data'=>$rows]);
    }

    public function restore(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int) $user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success'=>false,'error'=>'Record not found in trash'], 404);

        $actor = $this->actor($request);
        $now   = Carbon::now();

        $upd = [
            'deleted_at' => null,
            'updated_at' => $now,
        ];
        if ($this->hasCol('updated_by'))    $upd['updated_by'] = $actor['id'] ?: null;
        if ($this->hasCol('updated_at_ip')) $upd['updated_at_ip'] = $request->ip();

        DB::table($this->table)->where('id', $row->id)->update($upd);

        $fresh = DB::table($this->table)->where('id', $row->id)->first();
        $fresh = $this->decodeMetadataRow($fresh);

        return response()->json(['success'=>true,'message'=>'Education restored','data'=>$fresh]);
    }

    public function forceDelete(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','technical_assistant','it_person'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int) $user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->first();

        if (!$row) return response()->json(['success'=>false,'error'=>'Record not found'], 404);

        if (isset($row->certificate)) {
            $this->deletePublicAssetIfAny($row->certificate);
        }

        DB::table($this->table)->where('id', $row->id)->delete();

        return response()->json(['success'=>true,'message'=>'Education permanently deleted']);
    }

    public function forceDeleteAllDeleted(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','technical_assistant','it_person'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int) $user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $rows = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->get(['id','certificate']);

        $deletedCount = 0;

        DB::transaction(function () use ($rows, &$deletedCount) {
            foreach ($rows as $r) {
                if (isset($r->certificate)) {
                    $this->deletePublicAssetIfAny($r->certificate);
                }
                $deletedCount++;
                DB::table($this->table)->where('id', $r->id)->delete();
            }
        });

        return response()->json(['success'=>true,'message'=>'Trash cleared', 'deleted'=>$deletedCount]);
    }
}
