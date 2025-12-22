<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserConferencePublicationsController extends Controller
{
    private string $table = 'user_conference_publications';

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

        // token-only routes (/me/*) or if uuid not passed
        if (!$user_uuid || strtolower($user_uuid) === 'me') {
            $actor = $this->actor($request);
            if (!$actor['id']) return null;
            return $this->fetchUserById((int)$actor['id']);
        }

        return $this->fetchUserByUuid($user_uuid);
    }

    private function canAccess(Request $request, int $userId): bool
    {
        $actor = $this->actor($request);
        if (!$actor['id']) return false;

        // self
        if ($actor['id'] === $userId) return true;

        // privileged
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
     * - /api/users/{user_uuid}/conference-publications...
     * - /api/me/conference-publications...
     * ========================= */

    /**
     * GET /api/users/{user_uuid}/conference-publications
     * GET /api/me/conference-publications
     */
    public function index(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $rows = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->orderByRaw('publication_year IS NULL, publication_year DESC')
            ->orderBy('id','desc')
            ->get();

        $rows = $this->decodeMetadataCollection($rows);

        return response()->json(['success'=>true,'data'=>$rows]);
    }

    /**
     * GET /api/users/{user_uuid}/conference-publications/{uuid}
     * GET /api/me/conference-publications/{uuid}
     */
    public function show(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int)$user->id)) {
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
     * POST /api/users/{user_uuid}/conference-publications
     * POST /api/me/conference-publications
     */
    public function store(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $v = Validator::make($request->all(), [
            'conference_name'          => ['required','string','max:255'],
            'publication_organization' => ['nullable','string','max:255'],
            'title'                    => ['required','string','max:255'],
            'publication_year'         => ['nullable','integer','min:1900','max:'.(int)date('Y')],
            'publication_type'         => ['nullable','string','max:100'],
            'domain'                   => ['nullable','string','max:255'],
            'location'                 => ['nullable','string','max:255'],
            'description'              => ['nullable','string'],
            'url'                      => ['nullable','string','max:500'],
            'image'                    => ['nullable','string','max:255'],
            'metadata'                 => ['nullable','array'],
        ]);

        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()], 422);

        $data  = $v->validated();
        $actor = $this->actor($request);
        $now   = Carbon::now();

        $id = DB::table($this->table)->insertGetId([
            'uuid'                    => (string) Str::uuid(),
            'user_id'                 => (int) $user->id,

            'conference_name'          => $data['conference_name'],
            'publication_organization' => $data['publication_organization'] ?? null,
            'title'                    => $data['title'],

            'publication_year' => $data['publication_year'] ?? null,
            'publication_type' => $data['publication_type'] ?? null,
            'domain'           => $data['domain'] ?? null,
            'location'         => $data['location'] ?? null,

            'description' => $data['description'] ?? null,
            'url'         => $data['url'] ?? null,
            'image'       => $data['image'] ?? null,

            'metadata'      => array_key_exists('metadata', $data) ? json_encode($data['metadata']) : null,

            'created_by'    => $actor['id'] ?: null,
            'created_at_ip' => $request->ip(),
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $row = DB::table($this->table)->where('id', $id)->first();
        $row = $this->decodeMetadataRow($row);

        return response()->json(['success'=>true,'data'=>$row], 201);
    }

    /**
     * PUT/PATCH /api/users/{user_uuid}/conference-publications/{uuid}
     * PUT/PATCH /api/me/conference-publications/{uuid}
     */
    public function update(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int)$user->id)) {
            return response()->json(['success'=>false,'error'=>'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success'=>false,'error'=>'Record not found'], 404);

        $v = Validator::make($request->all(), [
            'conference_name'          => ['sometimes','required','string','max:255'],
            'publication_organization' => ['sometimes','nullable','string','max:255'],
            'title'                    => ['sometimes','required','string','max:255'],
            'publication_year'         => ['sometimes','nullable','integer','min:1900','max:'.(int)date('Y')],
            'publication_type'         => ['sometimes','nullable','string','max:100'],
            'domain'                   => ['sometimes','nullable','string','max:255'],
            'location'                 => ['sometimes','nullable','string','max:255'],
            'description'              => ['sometimes','nullable','string'],
            'url'                      => ['sometimes','nullable','string','max:500'],
            'image'                    => ['sometimes','nullable','string','max:255'],
            'metadata'                 => ['sometimes','nullable','array'],
        ]);

        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()], 422);

        $data  = $v->validated();
        $actor = $this->actor($request);
        $upd   = [];

        foreach ([
            'conference_name','publication_organization','title',
            'publication_type','domain','location','description','url','image'
        ] as $f) {
            if (array_key_exists($f, $data)) {
                $upd[$f] = $data[$f];
            }
        }

        if (array_key_exists('publication_year', $data)) {
            $upd['publication_year'] = $data['publication_year'];
        }

        if (array_key_exists('metadata', $data)) {
            $upd['metadata'] = $data['metadata'] !== null ? json_encode($data['metadata']) : null;
        }

        $upd['updated_at']    = Carbon::now();
        $upd['updated_by']    = $actor['id'] ?: null;      // remove if column not exists
        $upd['updated_at_ip'] = $request->ip();            // remove if column not exists

        DB::table($this->table)->where('id', $row->id)->update($upd);

        $fresh = DB::table($this->table)->where('id', $row->id)->first();
        $fresh = $this->decodeMetadataRow($fresh);

        return response()->json(['success'=>true,'data'=>$fresh]);
    }

    /**
     * DELETE /api/users/{user_uuid}/conference-publications/{uuid}
     * DELETE /api/me/conference-publications/{uuid}
     */
    public function destroy(Request $request, ?string $user_uuid = null, string $uuid = '')
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success'=>false,'error'=>'User not found'], 404);

        if (!$this->canAccess($request, (int)$user->id)) {
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

        return response()->json(['success'=>true,'message'=>'Conference publication deleted']);
    }
}
