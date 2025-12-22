<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserSocialMediaController extends Controller
{
    private string $table = 'user_social_media';

    /* =========================
     * Auth helpers
     * ========================= */

    private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
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

    /* =========================
     * CRUD
     * Supports BOTH:
     * - /api/users/{user_uuid}/social...
     * - /api/me/social...
     * ========================= */

    /**
     * GET /api/users/{user_uuid}/social
     * GET /api/me/social
     */
    public function index(Request $request, ?string $user_uuid = null)
    {
        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'],404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'],403);
        }

        $rows = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->orderBy('sort_order','asc')
            ->orderBy('id','desc')
            ->get();

        foreach ($rows as $r) {
            if (is_string($r->metadata)) $r->metadata = json_decode($r->metadata, true);
        }

        return response()->json(['success'=>true,'data'=>$rows]);
    }

    /**
     * POST /api/users/{user_uuid}/social
     * POST /api/me/social
     */
    public function store(Request $request, ?string $user_uuid = null)
    {
        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'],404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'],403);
        }

        $v = Validator::make($request->all(), [
            'platform'   => ['required','string','max:100'],
            'icon'       => ['nullable','string','max:100'],
            'link'       => ['required','string','max:500'],
            'sort_order' => ['nullable','integer'],
            'active'     => ['nullable','boolean'],
            'metadata'   => ['nullable','array'],
        ]);

        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()],422);

        $data  = $v->validated();
        $actor = $this->actor($request);
        $now   = Carbon::now();

        $id = DB::table($this->table)->insertGetId([
            'uuid'         => (string) Str::uuid(),
            'user_id'      => (int)$user->id,
            'platform'     => $data['platform'],
            'icon'         => $data['icon'] ?? null,
            'link'         => $data['link'],
            'sort_order'   => $data['sort_order'] ?? 0,
            'active'       => array_key_exists('active',$data) ? (bool)$data['active'] : true,
            'metadata'     => array_key_exists('metadata',$data) ? json_encode($data['metadata']) : null,
            'created_by'   => $actor['id'] ?: null,
            'created_at_ip'=> $request->ip(),
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);

        $row = DB::table($this->table)->where('id',$id)->first();
        if ($row && is_string($row->metadata)) $row->metadata = json_decode($row->metadata, true);

        return response()->json(['success'=>true,'data'=>$row],201);
    }

    /**
     * PUT/PATCH /api/users/{user_uuid}/social/{uuid}
     * PUT/PATCH /api/me/social/{uuid}
     */
    public function update(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
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
            'platform'   => ['sometimes','required','string','max:100'],
            'icon'       => ['sometimes','nullable','string','max:100'],
            'link'       => ['sometimes','required','string','max:500'],
            'sort_order' => ['sometimes','nullable','integer'],
            'active'     => ['sometimes','nullable','boolean'],
            'metadata'   => ['sometimes','nullable','array'],
        ]);

        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()],422);

        $data = $v->validated();
        $upd  = [];

        foreach (['platform','icon','link','sort_order'] as $f) {
            if (array_key_exists($f,$data)) $upd[$f] = $data[$f];
        }
        if (array_key_exists('active',$data)) $upd['active'] = $data['active'] === null ? true : (bool)$data['active'];
        if (array_key_exists('metadata',$data)) $upd['metadata'] = $data['metadata'] !== null ? json_encode($data['metadata']) : null;

        $upd['updated_at']    = Carbon::now();
        $upd['updated_by']    = $this->actor($request)['id'] ?: null; // remove if column not exists
        $upd['updated_at_ip'] = $request->ip();                       // remove if column not exists

        DB::table($this->table)->where('id',$row->id)->update($upd);

        $fresh = DB::table($this->table)->where('id',$row->id)->first();
        if ($fresh && is_string($fresh->metadata)) $fresh->metadata = json_decode($fresh->metadata, true);

        return response()->json(['success'=>true,'data'=>$fresh]);
    }

    /**
     * DELETE /api/users/{user_uuid}/social/{uuid}
     * DELETE /api/me/social/{uuid}
     */
    public function destroy(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
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

        return response()->json(['success'=>true,'message'=>'Social link deleted']);
    }
}
