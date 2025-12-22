<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserTeachingEngagementsController extends Controller
{
    private string $table = 'user_teaching_engagements';

    /* =========================
     * Helpers
     * ========================= */

    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
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

    private function logWithActor(string $msg, Request $r, array $extra = []): void
    {
        $a = $this->actor($r);
        Log::info($msg, array_merge([
            'actor_role' => $a['role'],
            'actor_id'   => $a['id'],
        ], $extra));
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

    private function isHighRole(?string $role): bool
    {
        return in_array($role, ['admin','director','principal','hod','technical_assistant','it_person'], true);
    }

    private function canAccess(Request $request, int $userId): bool
    {
        $actor = $this->actor($request);
        if (!$actor['id']) return false;
        if ($actor['id'] === $userId) return true;

        return $this->isHighRole($actor['role']);
    }

    private function decodeMetaRow($row)
    {
        if ($row && isset($row->metadata) && is_string($row->metadata)) {
            $decoded = json_decode($row->metadata, true);
            $row->metadata = is_array($decoded) ? $decoded : null;
        }
        return $row;
    }

    /* =========================
     * CRUD
     * Supports BOTH:
     * - /api/users/{user_uuid}/teaching-engagements...
     * - /api/me/teaching-engagements...
     * ========================= */

    /**
     * GET /api/users/{user_uuid}/teaching-engagements
     * GET /api/me/teaching-engagements
     */
    public function index(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'],404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'],403);
        }

        $rows = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->orderBy('id','desc')
            ->get();

        foreach ($rows as $r) {
            $this->decodeMetaRow($r);
        }

        return response()->json(['success'=>true,'data'=>$rows]);
    }

    /**
     * POST /api/users/{user_uuid}/teaching-engagements
     * POST /api/me/teaching-engagements
     */
    public function store(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'],404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'],403);
        }

        $v = Validator::make($request->all(), [
            'organization_name' => ['required','string','max:255'],
            'domain'            => ['nullable','string','max:255'],
            'description'       => ['nullable','string'],
            'metadata'          => ['nullable','array'],
        ]);

        if ($v->fails()) {
            return response()->json(['success'=>false,'errors'=>$v->errors()],422);
        }

        $data  = $v->validated();
        $actor = $this->actor($request);
        $now   = Carbon::now();

        DB::beginTransaction();
        try {
            $id = DB::table($this->table)->insertGetId([
                'uuid'              => (string) Str::uuid(),
                'user_id'           => (int)$user->id,
                'organization_name' => $data['organization_name'],
                'domain'            => $data['domain'] ?? null,
                'description'       => $data['description'] ?? null,
                'metadata'          => array_key_exists('metadata',$data) ? json_encode($data['metadata']) : null,
                'created_by'        => $actor['id'] ?: null,
                'created_at_ip'     => $request->ip(),
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            DB::commit();

            $this->logWithActor('user_teaching_engagements.store.success', $request, [
                'id' => $id,
                'user_id' => (int)$user->id,
            ]);

            $row = DB::table($this->table)->where('id',$id)->first();
            $row = $this->decodeMetaRow($row);

            return response()->json(['success'=>true,'data'=>$row],201);

        } catch (\Throwable $e) {
            DB::rollBack();

            $this->logWithActor('user_teaching_engagements.store.failed', $request, [
                'error'   => $e->getMessage(),
                'user_id' => (int)$user->id,
            ]);

            return response()->json(['success'=>false,'error'=>'Failed to create teaching engagement'],500);
        }
    }

    /**
     * PUT/PATCH /api/users/{user_uuid}/teaching-engagements/{uuid}
     * PUT/PATCH /api/me/teaching-engagements/{uuid}
     */
    public function update(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'],404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'],403);
        }

        $row = DB::table($this->table)
            ->where('uuid',$uuid)
            ->where('user_id',$user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success'=>false,'error'=>'Record not found'],404);

        $v = Validator::make($request->all(), [
            'organization_name' => ['sometimes','required','string','max:255'],
            'domain'            => ['sometimes','nullable','string','max:255'],
            'description'       => ['sometimes','nullable','string'],
            'metadata'          => ['sometimes','nullable','array'],
        ]);

        if ($v->fails()) {
            return response()->json(['success'=>false,'errors'=>$v->errors()],422);
        }

        $data = $v->validated();
        $upd  = [];

        foreach (['organization_name','domain','description'] as $f) {
            if (array_key_exists($f,$data)) $upd[$f] = $data[$f];
        }
        if (array_key_exists('metadata',$data)) {
            $upd['metadata'] = $data['metadata'] !== null ? json_encode($data['metadata']) : null;
        }

        if (empty($upd)) {
            $row = $this->decodeMetaRow($row);
            return response()->json(['success'=>true,'data'=>$row]);
        }

        $upd['updated_at']    = Carbon::now();
        $upd['updated_by']    = $this->actor($request)['id'] ?: null; // remove if column not exists
        $upd['updated_at_ip'] = $request->ip();                       // remove if column not exists

        DB::table($this->table)->where('id',$row->id)->update($upd);

        $fresh = DB::table($this->table)->where('id',$row->id)->first();
        $fresh = $this->decodeMetaRow($fresh);

        $this->logWithActor('user_teaching_engagements.update.success', $request, [
            'id' => $row->id,
            'user_id' => (int)$user->id,
        ]);

        return response()->json(['success'=>true,'data'=>$fresh]);
    }

    /**
     * DELETE /api/users/{user_uuid}/teaching-engagements/{uuid}
     * DELETE /api/me/teaching-engagements/{uuid}
     */
    public function destroy(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'],404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'],403);
        }

        $row = DB::table($this->table)
            ->where('uuid',$uuid)
            ->where('user_id',$user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success'=>false,'error'=>'Record not found'],404);

        $actor = $this->actor($request);
        $now   = Carbon::now();

        DB::table($this->table)
            ->where('id',$row->id)
            ->update([
                'deleted_at'    => $now,
                'updated_at'    => $now,
                'updated_by'    => $actor['id'] ?: null, // remove if column not exists
                'updated_at_ip' => $request->ip(),       // remove if column not exists
            ]);

        $this->logWithActor('user_teaching_engagements.destroy', $request, [
            'id' => $row->id,
            'user_id' => (int)$user->id,
        ]);

        return response()->json(['success'=>true,'message'=>'Teaching engagement deleted']);
    }
}
