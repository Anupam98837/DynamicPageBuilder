<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
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

    /* =========================
     * Metadata helpers
     * ========================= */

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

    /**
     * ✅ Accept metadata coming from:
     * - JSON body: metadata = array
     * - FormData: metadata = stringified JSON
     */
    private function readMetadataFromRequest(Request $request): array
    {
        if (!$request->has('metadata')) return [false, null, null]; // [present?, value, error]

        $meta = $request->input('metadata');

        if (is_array($meta)) return [true, $meta, null];

        if (is_string($meta)) {
            $s = trim($meta);
            if ($s === '') return [true, null, null];

            $decoded = json_decode($s, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [true, null, 'Metadata JSON invalid: ' . json_last_error_msg()];
            }
            if ($decoded !== null && !is_array($decoded)) {
                return [true, null, 'Metadata must decode to an object/array'];
            }
            return [true, $decoded, null];
        }

        return [true, null, 'Metadata must be an array or JSON string'];
    }

    /* =========================
     * Safe column setters
     * ========================= */

    private function setIfColumn(array &$arr, string $col, $val): void
    {
        if (Schema::hasColumn($this->table, $col)) {
            $arr[$col] = $val;
        }
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
            ->orderBy('sort_order','asc')
            ->orderBy('id','desc')
            ->get();

        $rows = $this->decodeMetadataCollection($rows);

        return response()->json(['success'=>true,'data'=>$rows]);
    }

    /**
     * POST /api/users/{user_uuid}/social
     * POST /api/me/social
     *
     * ✅ Updated:
     * - supports metadata as JSON string (FormData)
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
            'platform'   => ['required','string','max:100'],
            'icon'       => ['nullable','string','max:100'],
            'link'       => ['required','string','max:500'],
            'sort_order' => ['nullable','integer'],
            'active'     => ['nullable','boolean'],
            // metadata handled separately to allow JSON string
        ]);

        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()],422);

        [$metaPresent, $metaValue, $metaErr] = $this->readMetadataFromRequest($request);
        if ($metaErr) return response()->json(['success' => false, 'error' => $metaErr], 422);

        $data  = $v->validated();
        $actor = $this->actor($request);
        $now   = Carbon::now();

        $insert = [
            'uuid'         => (string) Str::uuid(),
            'user_id'      => (int)$user->id,
            'platform'     => $data['platform'],
            'icon'         => $data['icon'] ?? null,
            'link'         => $data['link'],
            'sort_order'   => $data['sort_order'] ?? 0,
            'active'       => array_key_exists('active',$data) ? (bool)$data['active'] : true,
            'metadata'     => $metaPresent ? ($metaValue !== null ? json_encode($metaValue) : null) : null,
            'created_by'   => $actor['id'] ?: null,
            'created_at_ip'=> $request->ip(),
            'created_at'   => $now,
            'updated_at'   => $now,
        ];

        // only set if columns exist
        $this->setIfColumn($insert, 'updated_by', $actor['id'] ?: null);
        $this->setIfColumn($insert, 'updated_at_ip', $request->ip());

        $id = DB::table($this->table)->insertGetId($insert);

        $row = DB::table($this->table)->where('id',$id)->first();
        $row = $this->decodeMetadataRow($row);

        $this->logWithActor('user_social_media.store.success', $request, [
            'id' => $id,
            'user_id' => (int)$user->id,
        ]);

        return response()->json(['success'=>true,'data'=>$row],201);
    }

    /**
     * PUT/PATCH /api/users/{user_uuid}/social/{uuid}
     * PUT/PATCH /api/me/social/{uuid}
     *
     * ✅ Updated:
     * - supports metadata as JSON string (FormData)
     * - safe updated_by / updated_at_ip (only if columns exist)
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
            'platform'   => ['sometimes','required','string','max:100'],
            'icon'       => ['sometimes','nullable','string','max:100'],
            'link'       => ['sometimes','required','string','max:500'],
            'sort_order' => ['sometimes','nullable','integer'],
            'active'     => ['sometimes','nullable','boolean'],
            // metadata handled separately
        ]);

        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()],422);

        [$metaPresent, $metaValue, $metaErr] = $this->readMetadataFromRequest($request);
        if ($metaErr) return response()->json(['success' => false, 'error' => $metaErr], 422);

        $data  = $v->validated();
        $actor = $this->actor($request);

        $upd = [];

        foreach (['platform','icon','link','sort_order'] as $f) {
            if (array_key_exists($f,$data)) $upd[$f] = $data[$f];
        }

        if (array_key_exists('active',$data)) {
            // if null sent, treat as true (same as your prior logic)
            $upd['active'] = $data['active'] === null ? true : (bool)$data['active'];
        }

        if ($metaPresent) {
            $upd['metadata'] = $metaValue !== null ? json_encode($metaValue) : null;
        }

        if (empty($upd)) {
            return response()->json(['success' => true, 'data' => $this->decodeMetadataRow($row)]);
        }

        $upd['updated_at'] = Carbon::now();

        // only set if columns exist
        $this->setIfColumn($upd, 'updated_by', $actor['id'] ?: null);
        $this->setIfColumn($upd, 'updated_at_ip', $request->ip());

        DB::table($this->table)->where('id',$row->id)->update($upd);

        $fresh = DB::table($this->table)->where('id',$row->id)->first();
        $fresh = $this->decodeMetadataRow($fresh);

        $this->logWithActor('user_social_media.update.success', $request, [
            'id' => $row->id,
            'user_id' => (int)$user->id,
        ]);

        return response()->json(['success'=>true,'data'=>$fresh]);
    }

    /**
     * DELETE /api/users/{user_uuid}/social/{uuid}
     * DELETE /api/me/social/{uuid}
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

        $upd = [
            'deleted_at' => $now,
            'updated_at' => $now,
        ];

        // only set if columns exist
        $this->setIfColumn($upd, 'updated_by', $actor['id'] ?: null);
        $this->setIfColumn($upd, 'updated_at_ip', $request->ip());

        DB::table($this->table)
            ->where('id',$row->id)
            ->update($upd);

        $this->logWithActor('user_social_media.destroy', $request, [
            'id' => $row->id,
            'user_id' => (int)$user->id,
        ]);

        return response()->json(['success'=>true,'message'=>'Social link deleted']);
    }

    /* =========================
     * Trash / Restore / Force delete
     * ========================= */

    /**
     * GET /api/users/{user_uuid}/social/deleted
     * GET /api/me/social/deleted
     */
    public function indexDeleted(Request $request, ?string $user_uuid = null)
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
            ->whereNotNull('deleted_at')
            ->orderBy('deleted_at','desc')
            ->orderBy('id','desc')
            ->get();

        $rows = $this->decodeMetadataCollection($rows);

        return response()->json(['success'=>true,'data'=>$rows]);
    }

    /**
     * POST /api/users/{user_uuid}/social/{uuid}/restore
     * POST /api/me/social/{uuid}/restore
     */
    public function restore(Request $request, ?string $user_uuid = null, string $uuid = '')
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
            ->whereNotNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success'=>false,'error'=>'Record not found in Bin'],404);

        $actor = $this->actor($request);
        $now = Carbon::now();

        $upd = [
            'deleted_at' => null,
            'updated_at' => $now,
        ];

        $this->setIfColumn($upd, 'updated_by', $actor['id'] ?: null);
        $this->setIfColumn($upd, 'updated_at_ip', $request->ip());

        DB::table($this->table)->where('id',$row->id)->update($upd);

        $fresh = DB::table($this->table)->where('id',$row->id)->first();
        $fresh = $this->decodeMetadataRow($fresh);

        $this->logWithActor('user_social_media.restore', $request, [
            'id' => $row->id,
            'user_id' => (int)$user->id,
        ]);

        return response()->json(['success'=>true,'data'=>$fresh,'message'=>'Restored']);
    }

    /**
     * DELETE /api/users/{user_uuid}/social/{uuid}/force
     * DELETE /api/me/social/{uuid}/force
     */
    public function forceDelete(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'],404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'],403);
        }

        $row = DB::table($this->table)
            ->where('uuid',$uuid)
            ->where('user_id',$user->id)
            ->whereNotNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success'=>false,'error'=>'Record not found in Bin'],404);

        DB::table($this->table)->where('id',$row->id)->delete();

        $this->logWithActor('user_social_media.force_delete', $request, [
            'id' => $row->id,
            'user_id' => (int)$user->id,
        ]);

        return response()->json(['success'=>true,'message'=>'Deleted permanently']);
    }

    /**
     * DELETE /api/users/{user_uuid}/social/deleted/force
     * DELETE /api/me/social/deleted/force
     */
    public function forceDeleteAllDeleted(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'],404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'],403);
        }

        $count = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->delete();

        $this->logWithActor('user_social_media.force_delete_all_deleted', $request, [
            'user_id' => (int)$user->id,
            'count' => $count,
        ]);

        return response()->json(['success'=>true,'message'=>'Bin emptied','deleted_count'=>$count]);
    }
}
