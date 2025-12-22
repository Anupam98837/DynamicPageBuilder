<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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

    /* =========================
     * CRUD
     * Supports BOTH:
     * - /api/users/{user_uuid}/educations...
     * - /api/me/educations...
     * ========================= */

    /**
     * GET /api/users/{user_uuid}/educations
     * GET /api/me/educations
     */
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

    /**
     * GET /api/users/{user_uuid}/educations/{uuid}
     * GET /api/me/educations/{uuid}
     */
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

    /**
     * POST /api/users/{user_uuid}/educations
     * POST /api/me/educations
     */
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
            'metadata'         => ['nullable','array'],
        ]);

        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()], 422);

        $data  = $v->validated();
        $actor = $this->actor($request);
        $now   = Carbon::now();

        $id = DB::table($this->table)->insertGetId([
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

            'metadata'        => array_key_exists('metadata', $data) ? json_encode($data['metadata']) : null,

            'created_by'      => $actor['id'] ?: null,
            'created_at_ip'   => $request->ip(),
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        $row = DB::table($this->table)->where('id', $id)->first();
        $row = $this->decodeMetadataRow($row);

        return response()->json(['success'=>true,'data'=>$row], 201);
    }

    /**
     * PUT/PATCH /api/users/{user_uuid}/educations/{uuid}
     * PUT/PATCH /api/me/educations/{uuid}
     */
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
            'metadata'         => ['sometimes','nullable','array'],
        ]);

        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()], 422);

        $data  = $v->validated();
        $actor = $this->actor($request);
        $upd   = [];

        foreach ($data as $k => $val) {
            if ($k === 'metadata') {
                $upd[$k] = $val !== null ? json_encode($val) : null;
            } else {
                $upd[$k] = $val;
            }
        }

        $upd['updated_at']    = Carbon::now();
        $upd['updated_by']    = $actor['id'] ?: null;  // remove if column not exists
        $upd['updated_at_ip'] = $request->ip();        // remove if column not exists

        DB::table($this->table)->where('id', $row->id)->update($upd);

        $fresh = DB::table($this->table)->where('id', $row->id)->first();
        $fresh = $this->decodeMetadataRow($fresh);

        return response()->json(['success'=>true,'data'=>$fresh]);
    }

    /**
     * DELETE /api/users/{user_uuid}/educations/{uuid}
     * DELETE /api/me/educations/{uuid}
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

        DB::table($this->table)
            ->where('id', $row->id)
            ->update([
                'deleted_at'    => $now,
                'updated_at'    => $now,
                'updated_by'    => $actor['id'] ?: null, // remove if column not exists
                'updated_at_ip' => $request->ip(),       // remove if column not exists
            ]);

        return response()->json(['success'=>true,'message'=>'Education deleted']);
    }
}
