<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class UserPrivilegeController extends Controller
{
     private function actor(Request $request): array
    {
        return [
            'role' => $request->attributes->get('auth_role'),
            'type' => $request->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($request->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    
    public function sync(Request $r)
    {
        $data = $r->validate([
            'user_id'       => 'sometimes|integer|exists:users,id',
            'user_uuid'     => 'sometimes|uuid|exists:users,uuid',
            'privileges'    => 'required|array',
            'privileges.*'  => 'integer|exists:privileges,id',
        ]);

        // Resolve numeric user id if only uuid provided
        if (empty($data['user_id']) && !empty($data['user_uuid'])) {
            $userId = (int) DB::table('users')->where('uuid', $data['user_uuid'])->value('id');
        } else {
            $userId = (int) ($data['user_id'] ?? 0);
        }

        if (! $userId) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $newPrivileges = array_values(array_unique($data['privileges']));
        $now = now();

        try {
            $result = DB::transaction(function () use ($userId, $newPrivileges, $now) {
                // current active privilege ids
                $current = DB::table('user_privileges')
                    ->where('user_id', $userId)
                    ->whereNull('deleted_at')
                    ->pluck('privilege_id')
                    ->map(fn ($v) => (int) $v)
                    ->toArray();

                // soft-deleted existing mappings for this user (privilege_id => id)
                $soft = DB::table('user_privileges')
                    ->where('user_id', $userId)
                    ->whereNotNull('deleted_at')
                    ->whereIn('privilege_id', $newPrivileges)
                    ->pluck('id', 'privilege_id') // privilege_id => id
                    ->all();

                // Calculate differences
                $toInsert = array_values(array_diff($newPrivileges, $current)); // privilege_ids to ensure active
                $toDelete = array_values(array_diff($current, $newPrivileges)); // privilege_ids to soft-delete

                $revived = [];
                $actuallyInserted = [];

                // First, revive any soft-deleted rows among $toInsert
                if (!empty($toInsert)) {
                    $toRevive = array_values(array_intersect($toInsert, array_keys($soft)));
                    if (!empty($toRevive)) {
                        foreach ($toRevive as $privId) {
                            $rowId = $soft[$privId];
                            DB::table('user_privileges')
                                ->where('id', $rowId)
                                ->update(['deleted_at' => null, 'updated_at' => $now]);
                            $revived[] = $privId;
                        }
                    }

                    // For remaining privilege ids not revived, insert new rows
                    $toActuallyInsert = array_values(array_diff($toInsert, $revived));
                    if (!empty($toActuallyInsert)) {
                        $inserts = [];
                        foreach ($toActuallyInsert as $privId) {
                            $inserts[] = [
                                'uuid'         => (string) Str::uuid(),
                                'user_id'      => $userId,
                                'privilege_id' => $privId,
                                'created_at'   => $now,
                                'updated_at'   => $now,
                            ];
                        }
                        DB::table('user_privileges')->insert($inserts);
                        $actuallyInserted = $toActuallyInsert;
                    }
                }

                // Soft delete removed privilege mappings
                if (!empty($toDelete)) {
                    DB::table('user_privileges')
                        ->where('user_id', $userId)
                        ->whereIn('privilege_id', $toDelete)
                        ->whereNull('deleted_at')
                        ->update([
                            'deleted_at' => $now,
                            'updated_at' => $now,
                        ]);
                }

                return [
                    'revived' => $revived,
                    'inserted' => $actuallyInserted,
                    'removed' => $toDelete,
                ];
            });

            // Fetch user uuid for response
            $userUuid = DB::table('users')->where('id', $userId)->value('uuid');

            return response()->json([
                'message' => 'Privileges synced successfully.',
                'user_uuid' => $userUuid,
                'revived' => $result['revived'],
                'added'   => $result['inserted'],
                'removed' => $result['removed'],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Could not sync privileges', 'detail' => $e->getMessage()], 500);
        }
    }

    /**
     * Assign a single privilege to a user.
     * Accepts:
     *   - user_id OR user_uuid
     *   - privilege_id OR privilege_uuid
     *
     * Revives a soft-deleted mapping if present, otherwise inserts new.
     */
    public function assign(Request $r)
    {
        $data = $r->validate([
            'user_id'        => 'sometimes|integer|exists:users,id',
            'user_uuid'      => 'sometimes|uuid|exists:users,uuid',
            'privilege_id'   => 'sometimes|integer|exists:privileges,id',
            'privilege_uuid' => 'sometimes|uuid|exists:privileges,uuid',
        ]);

        // Resolve user id
        if (empty($data['user_id']) && !empty($data['user_uuid'])) {
            $userId = (int) DB::table('users')->where('uuid', $data['user_uuid'])->value('id');
        } else {
            $userId = (int) ($data['user_id'] ?? 0);
        }

        if (! $userId) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Resolve privilege id
        if (!empty($data['privilege_id'])) {
            $privId = (int) $data['privilege_id'];
        } elseif (!empty($data['privilege_uuid'])) {
            $privId = DB::table('privileges')
                ->where('uuid', $data['privilege_uuid'])
                ->whereNull('deleted_at')
                ->value('id');
        } else {
            return response()->json(['message' => 'privilege_id or privilege_uuid is required'], 422);
        }

        if (! $privId) {
            return response()->json(['message' => 'Privilege not found'], 404);
        }

        $now = now();

        try {
            DB::transaction(function () use ($userId, $privId, $now) {
                // Look for any mapping (active or soft-deleted)
                $existing = DB::table('user_privileges')
                    ->where('user_id', $userId)
                    ->where('privilege_id', $privId)
                    ->first();

                if ($existing) {
                    // If soft-deleted, revive it
                    if ($existing->deleted_at !== null) {
                        DB::table('user_privileges')->where('id', $existing->id)
                            ->update(['deleted_at' => null, 'updated_at' => $now]);
                    }
                    // if already active, nothing to do
                } else {
                    // Insert new mapping
                    DB::table('user_privileges')->insert([
                        'uuid' => (string) Str::uuid(),
                        'user_id' => $userId,
                        'privilege_id' => $privId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });

            $userUuid = DB::table('users')->where('id', $userId)->value('uuid');

            return response()->json(['message' => 'Privilege assigned', 'user_uuid' => $userUuid], 201);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Could not assign privilege', 'detail' => $e->getMessage()], 500);
        }
    }

    /**
     * Soft-delete a single privilege row.
     * Accepts either:
     *   - uuid (mapping uuid)
     *   OR
     *   - user_id OR user_uuid + privilege_id
     */
    public function destroy(Request $r)
    {
        $r->validate([
            'uuid'          => 'sometimes|uuid|exists:user_privileges,uuid',
            'user_id'       => 'sometimes|integer|exists:users,id',
            'user_uuid'     => 'sometimes|uuid|exists:users,uuid',
            'privilege_id'  => 'sometimes|required_with:user_id|integer|exists:privileges,id',
        ]);

        $query = DB::table('user_privileges')->whereNull('deleted_at');
        $now = now();

        $userUuid = null;

        if ($r->filled('uuid')) {
            // Try to discover the user id for the mapping so we can return the user's uuid
            $mapping = DB::table('user_privileges')->where('uuid', $r->input('uuid'))->first();
            if ($mapping) {
                $userUuid = DB::table('users')->where('id', $mapping->user_id)->value('uuid');
            }

            $query->where('uuid', $r->input('uuid'));
        } else {
            // Resolve numeric user id either by user_id or user_uuid
            if ($r->filled('user_id')) {
                $userId = (int) $r->input('user_id');
            } elseif ($r->filled('user_uuid')) {
                $userId = (int) DB::table('users')->where('uuid', $r->input('user_uuid'))->value('id');
                if (!$userId) {
                    return response()->json(['message' => 'User not found'], 404);
                }
            } else {
                return response()->json(['message' => 'user_id or user_uuid is required'], 422);
            }

            $userUuid = DB::table('users')->where('id', $userId)->value('uuid');

            $query->where('user_id', $userId)
                  ->where('privilege_id', $r->input('privilege_id'));
        }

        $affected = $query->update([
            'deleted_at' => $now,
            'updated_at' => $now,
        ]);

        if ($affected === 0) {
            return response()->json(['message' => 'Privilege mapping not found.'], 404);
        }

        return response()->json(['message' => 'Privilege removed successfully.', 'user_uuid' => $userUuid]);
    }

    /**
     * List all active privileges for a user.
     * Returns user_privileges joined with privileges table for convenience.
     * Accepts user_id OR user_uuid
     */
    public function list(Request $r)
    {
        $r->validate([
            'user_id'   => 'sometimes|integer|exists:users,id',
            'user_uuid' => 'sometimes|uuid|exists:users,uuid',
        ]);

        if ($r->filled('user_id')) {
            $userId = (int) $r->user_id;
        } elseif ($r->filled('user_uuid')) {
            $userId = (int) DB::table('users')->where('uuid', $r->user_uuid)->value('id');
            if (!$userId) {
                return response()->json(['message' => 'User not found'], 404);
            }
        } else {
            return response()->json(['message' => 'user_id or user_uuid is required'], 422);
        }

        $rows = DB::table('user_privileges as up')
            ->join('privileges as p', 'p.id', '=', 'up.privilege_id')
            ->where('up.user_id', $userId)
            ->whereNull('up.deleted_at')
            ->whereNull('p.deleted_at')
            ->select([
                'up.uuid as mapping_uuid',
                'up.privilege_id',
                'p.uuid as privilege_uuid',
                DB::raw('p.action as privilege_name'),
                'p.action as privilege_action',
                'p.description as privilege_description',
                'up.created_at'
            ])
            ->orderBy('up.created_at', 'desc')
            ->get();

        $userUuid = DB::table('users')->where('id', $userId)->value('uuid');

        return response()->json([
            'user_uuid' => $userUuid,
            'data' => $rows,
        ]);
    }

    /**
     * Unassign (soft-delete) a single privilege from a user.
     * Accepts:
     *   - mapping_uuid
     *   OR
     *   - user_id OR user_uuid + privilege_id OR privilege_uuid
     */
    public function unassign(Request $r)
    {
        $data = $r->validate([
            'mapping_uuid'   => 'sometimes|uuid|exists:user_privileges,uuid',
            'user_id'        => 'sometimes|integer|exists:users,id',
            'user_uuid'      => 'sometimes|uuid|exists:users,uuid',
            'privilege_id'   => 'sometimes|integer|exists:privileges,id',
            'privilege_uuid' => 'sometimes|uuid|exists:privileges,uuid',
        ]);

        $now = now();

        // --- Case 1: Directly via mapping UUID ---
        if ($r->filled('mapping_uuid')) {
            $mapping = DB::table('user_privileges')->where('uuid', $r->mapping_uuid)->first();
            $userUuid = null;
            if ($mapping) {
                $userUuid = DB::table('users')->where('id', $mapping->user_id)->value('uuid');
            }

            $affected = DB::table('user_privileges')
                ->where('uuid', $r->mapping_uuid)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $now, 'updated_at' => $now]);

            return $affected
                ? response()->json(['message' => 'Privilege unassigned.', 'user_uuid' => $userUuid])
                : response()->json(['message' => 'Privilege not found.'], 404);
        }

        // --- Case 2: Using user_id/user_uuid + privilege_id/privilege_uuid ---
        if (!$r->filled('user_id') && !$r->filled('user_uuid')) {
            return response()->json(['message' => 'user_id or user_uuid is required when mapping_uuid is not provided'], 422);
        }

        // Resolve user id
        if ($r->filled('user_id')) {
            $userId = (int) $r->user_id;
        } else {
            $userId = (int) DB::table('users')->where('uuid', $r->user_uuid)->value('id');
            if (!$userId) {
                return response()->json(['message' => 'User not found'], 404);
            }
        }

        // Resolve privilege id
        if ($r->filled('privilege_id')) {
            $privId = (int) $r->privilege_id;
        } elseif ($r->filled('privilege_uuid')) {
            $privId = DB::table('privileges')
                ->where('uuid', $r->privilege_uuid)
                ->whereNull('deleted_at')
                ->value('id');

            if (!$privId) {
                return response()->json(['message' => 'Privilege not found'], 404);
            }
        } else {
            return response()->json(['message' => 'Either privilege_id or privilege_uuid is required'], 422);
        }

        $userUuid = DB::table('users')->where('id', $userId)->value('uuid');

        // Perform soft-delete
        $affected = DB::table('user_privileges')
            ->where('user_id', $userId)
            ->where('privilege_id', $privId)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => $now, 'updated_at' => $now]);

        return $affected
            ? response()->json(['message' => 'Privilege unassigned.', 'user_uuid' => $userUuid])
            : response()->json(['message' => 'Privilege mapping not found.'], 404);
    }

    /**
     * GET /api/users/{idOrUuid}
     * Accept numeric ID OR UUID
     */
     public function show($idOrUuid)
{
    if (is_numeric($idOrUuid)) {
        $user = DB::table('users')->where('id', $idOrUuid)->whereNull('deleted_at')->first();
    } else {
        $user = DB::table('users')->where('uuid', $idOrUuid)->whereNull('deleted_at')->first();
    }

    if (! $user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    return response()->json(['user' => $user]);
}

    /**
     * GET /api/users/by-uuid?uuid=...
     */
    public function byUuid(Request $request)
    {
        $request->validate(['uuid' => 'required|uuid']);

        $user = DB::table('users')
            ->where('uuid', $request->uuid)
            ->whereNull('deleted_at')
            ->first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['user' => $user]);
    }

        /** Normalize module href for UI (keeps http(s) absolute; otherwise ensures single leading slash). */
    private function normalizeHrefForResponse($href): string
    {
        $href = (string) ($href ?? '');
        if ($href === '') return '';
        if (preg_match('#^https?://#i', $href)) return $href;
        return '/' . ltrim($href, '/');
    }

    /** Simple guard: actor can view self or (admin/super_admin) can view others. Adjust roles if needed. */
    private function canViewUserModules(array $actor, int $targetUserId): bool
    {
        if ($actor['id'] === $targetUserId) return true;
        return in_array($actor['role'], ['admin','super_admin'], true);
    }

    /** Convenience: current actor’s modules */
    public function myModules(Request $r)
    {
        $actor = $this->actor($r);
        if (! $actor['id']) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        return $this->modulesFor($r, (int) $actor['id']);
    }

    /** For admins (or self): modules for a given user via query (?user_id= or ?user_uuid=) */
    public function modulesForUser(Request $r)
    {
        $r->validate([
            'user_id'         => 'sometimes|integer|exists:users,id',
            'user_uuid'       => 'sometimes|uuid|exists:users,uuid',
            'with_privileges' => 'sometimes|boolean',
            'status'          => 'sometimes|string', // 'all' | 'archived' | any other value from your data
        ]);

        $actor = $this->actor($r);

        // Resolve target user id (default to actor if nothing passed)
        if ($r->filled('user_id')) {
            $targetUserId = (int) $r->input('user_id');
        } elseif ($r->filled('user_uuid')) {
            $targetUserId = (int) DB::table('users')->where('uuid', $r->input('user_uuid'))->value('id');
        } else {
            $targetUserId = (int) $actor['id'];
        }

        if (! $targetUserId) return response()->json(['message' => 'User not found'], 404);
        if (! $this->canViewUserModules($actor, $targetUserId)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return $this->modulesFor($r, $targetUserId);
    }

    /** For admins (or self): modules for a given user via path /api/users/{idOrUuid}/modules */
    public function modulesForUserByPath(Request $r, $idOrUuid)
    {
        $actor = $this->actor($r);

        $targetUserId = ctype_digit((string) $idOrUuid)
            ? (int) $idOrUuid
            : (int) DB::table('users')->where('uuid', $idOrUuid)->value('id');

        if (! $targetUserId) return response()->json(['message' => 'User not found'], 404);
        if (! $this->canViewUserModules($actor, $targetUserId)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return $this->modulesFor($r, $targetUserId);
    }

    /** Core query used by the 3 endpoints above */
    private function modulesFor(Request $r, int $userId)
    {
        // Select columns defensively based on your migrations
        $moduleCols = ['m.id','m.uuid','m.name','m.description','m.created_at','m.updated_at'];
        if (Schema::hasColumn('modules', 'href'))    $moduleCols[] = 'm.href';
        if (Schema::hasColumn('modules', 'status'))  $moduleCols[] = 'm.status';
        if (Schema::hasColumn('modules', 'order_no')) { $orderCol = 'm.order_no'; $orderDir = 'asc'; }
        else { $orderCol = 'm.name'; $orderDir = 'asc'; }

        $q = DB::table('user_privileges as up')
            ->join('privileges as p', 'p.id', '=', 'up.privilege_id')
            ->join('modules as m', 'm.id', '=', 'p.module_id')
            ->where('up.user_id', $userId)
            ->whereNull('up.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('m.deleted_at')
            ->select($moduleCols)
            ->distinct();

        // Default: exclude archived modules (if status column exists). Allow overrides via ?status=
        if (Schema::hasColumn('modules', 'status')) {
            if ($r->filled('status')) {
                $status = (string) $r->query('status');
                if ($status === 'archived') {
                    $q->where('m.status', 'archived');
                } elseif ($status !== 'all') {
                    $q->where('m.status', $status);
                }
                // 'all' -> no filter
            } else {
                $q->where(function ($qq) {
                    $qq->whereNull('m.status')->orWhere('m.status', '!=', 'archived');
                });
            }
        }

        $modules = $q->orderBy($orderCol, $orderDir)->get();

        $withPriv = filter_var($r->query('with_privileges', false), FILTER_VALIDATE_BOOLEAN);
        $privByModule = collect();

        if ($withPriv && $modules->isNotEmpty()) {
            $modIds = $modules->pluck('id')->all();
            $privByModule = DB::table('user_privileges as up')
                ->join('privileges as p', 'p.id', '=', 'up.privilege_id')
                ->where('up.user_id', $userId)
                ->whereNull('up.deleted_at')
                ->whereNull('p.deleted_at')
                ->whereIn('p.module_id', $modIds)
                ->select(
                    'p.id','p.uuid','p.module_id',
                    DB::raw('p.action as name'), // keep UI-friendly key
                    'p.action','p.description','p.created_at'
                )
                ->orderBy('p.action','asc')
                ->get()
                ->groupBy('module_id');
        }

        // finalize output (normalize href, attach privileges if asked)
        $modules->transform(function ($m) use ($withPriv, $privByModule) {
            if (isset($m->href)) {
                $m->href = $this->normalizeHrefForResponse($m->href);
            }
            $m->privileges = $withPriv
                ? ($privByModule->has($m->id) ? $privByModule[$m->id]->values() : collect([]))
                : collect([]);
            return $m;
        });

        $userUuid = DB::table('users')->where('id', $userId)->value('uuid');

        return response()->json([
            'user_uuid' => $userUuid,
            'data'      => $modules->values(),
        ]);
    }


    
}
