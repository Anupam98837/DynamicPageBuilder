<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserPersonalInformationController extends Controller
{
    private string $table = 'user_personal_information';

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

    private function canAccessUser(Request $request, int $targetUserId): bool
    {
        $actor = $this->actor($request);
        if (!$actor['id']) return false;

        if ($actor['id'] === $targetUserId) return true;
        return $this->isHighRole($actor['role']);
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
     * Qualification helpers
     * ========================= */

    private function decodeQualificationRow($row)
    {
        if (!$row) return $row;

        $raw = null;

        if (property_exists($row, 'qualification')) {
            $raw = $row->qualification;
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $row->qualification = is_array($decoded) ? $decoded : [];
        } elseif (is_array($raw)) {
            $row->qualification = $raw;
        } else {
            $row->qualification = [];
        }

        return $row;
    }

    /**
     * Accept qualification as:
     * - JSON body array
     * - stringified JSON
     */
    private function readQualificationFromRequest(Request $request): array
    {
        if (!$request->exists('qualification')) return [false, null, null];

        $q = $request->input('qualification');

        if (is_array($q)) return [true, $q, null];

        if (is_string($q)) {
            $s = trim($q);
            if ($s === '') return [true, [], null];

            $decoded = json_decode($s, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [true, null, 'Qualification JSON invalid: ' . json_last_error_msg()];
            }
            if ($decoded !== null && !is_array($decoded)) {
                return [true, null, 'Qualification must decode to an array'];
            }
            return [true, $decoded ?? [], null];
        }

        return [true, null, 'Qualification must be an array or JSON string'];
    }

    /* =====================================================
     * CRUD ENDPOINTS
     * ===================================================== */

    public function show(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        $row = $this->decodeQualificationRow($row);

        return response()->json([
            'success' => true,
            'data'    => $row ?: [
                'user_id'          => (int) $user->id,
                'qualification'    => [],
                'affiliation'      => null,
                'specification'    => null,
                'experience'       => null,
                'interest'         => null,
                'administration'   => null,
                'research_project' => null,
            ],
        ]);
    }

    public function store(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        Log::info('PI.store.request', [
            'content_type' => $request->header('Content-Type'),
            'raw_body'     => substr($request->getContent(), 0, 2000),
            'all_keys'     => array_keys($request->all()),
            'qualification_value' => $request->input('qualification'),
            'qualification_type'  => gettype($request->input('qualification')),
        ]);

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $v = Validator::make($request->all(), [
            'affiliation'      => ['nullable', 'string'],
            'specification'    => ['nullable', 'string'],
            'experience'       => ['nullable', 'string'],
            'interest'         => ['nullable', 'string'],
            'administration'   => ['nullable', 'string'],
            'research_project' => ['nullable', 'string'],
        ]);

        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        [$qPresent, $qValue, $qErr] = $this->readQualificationFromRequest($request);

        Log::info('PI.store.qualification_parse', [
            'present' => $qPresent,
            'value'   => $qValue,
            'type'    => gettype($qValue),
            'error'   => $qErr,
        ]);

        if ($qErr) return response()->json(['success' => false, 'error' => $qErr], 422);

        $data  = $v->validated();
        $now   = Carbon::now();
        $actor = $this->actor($request);

        DB::beginTransaction();
        try {
            $existing = DB::table($this->table)
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error'   => 'Personal information already exists. Use update.',
                ], 409);
            }

            $insert = [
                'uuid'             => (string) Str::uuid(),
                'user_id'          => (int) $user->id,
                // ✅ since your column is JSON, store JSON string
                'qualification'    => json_encode($qValue ?? []),
                'affiliation'      => $data['affiliation']      ?? null,
                'specification'    => $data['specification']    ?? null,
                'experience'       => $data['experience']       ?? null,
                'interest'         => $data['interest']         ?? null,
                'administration'   => $data['administration']   ?? null,
                'research_project' => $data['research_project'] ?? null,
                'created_by'       => $actor['id'] ?: null,
                'created_at_ip'    => $request->ip(),
                'created_at'       => $now,
                'updated_at'       => $now,
            ];

            Log::info('PI.store.insert_payload', $insert);

            $id = DB::table($this->table)->insertGetId($insert);

            DB::commit();

            $this->logWithActor('user_personal_information.store.success', $request, [
                'id'      => $id,
                'user_id' => (int)$user->id,
            ]);

            // log what DB actually stored
            $rawRow = DB::table($this->table)->select('id','qualification')->where('id', $id)->first();
            Log::info('PI.store.db_after_insert', [
                'id' => $id,
                'qualification_db' => $rawRow->qualification ?? null,
                'qualification_db_type' => isset($rawRow->qualification) ? gettype($rawRow->qualification) : null,
            ]);

            $row = DB::table($this->table)->where('id', $id)->first();
            $row = $this->decodeQualificationRow($row);

            return response()->json(['success' => true, 'data' => $row], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            $this->logWithActor('user_personal_information.store.failed', $request, [
                'error'   => $e->getMessage(),
                'user_id' => (int)$user->id,
            ]);

            return response()->json(['success' => false, 'error' => 'Failed to create personal information'], 500);
        }
    }

    public function update(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        Log::info('PI.update.request', [
            'raw_body' => substr($request->getContent(), 0, 2000),
            'qualification_value' => $request->input('qualification'),
            'qualification_type'  => gettype($request->input('qualification')),
        ]);

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $v = Validator::make($request->all(), [
            'affiliation'      => ['sometimes', 'nullable', 'string'],
            'specification'    => ['sometimes', 'nullable', 'string'],
            'experience'       => ['sometimes', 'nullable', 'string'],
            'interest'         => ['sometimes', 'nullable', 'string'],
            'administration'   => ['sometimes', 'nullable', 'string'],
            'research_project' => ['sometimes', 'nullable', 'string'],
            // qualification handled separately
            'qualification_force_clear' => ['sometimes','boolean'],
        ]);

        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        [$qPresent, $qValue, $qErr] = $this->readQualificationFromRequest($request);

        Log::info('PI.update.qualification_parse', [
            'present' => $qPresent,
            'value'   => $qValue,
            'count'   => is_array($qValue) ? count($qValue) : null,
            'error'   => $qErr,
        ]);

        if ($qErr) return response()->json(['success' => false, 'error' => $qErr], 422);

        $data  = $v->validated();
        $now   = Carbon::now();

        DB::beginTransaction();
        try {
            $row = DB::table($this->table)
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error'   => 'Personal information not found. Create it first (store).',
                ], 404);
            }

            $update = [];

            // ✅ IMPORTANT: prevent accidental overwrite with empty []
            // if you truly want to clear it, send: qualification_force_clear = true
            $forceClear = (bool)($data['qualification_force_clear'] ?? false);

            if ($qPresent) {
                if (is_array($qValue) && count($qValue) === 0 && !$forceClear) {
                    Log::warning('PI.update.skip_empty_qualification', [
                        'reason' => 'qualification came as empty array - skipping to avoid accidental overwrite',
                        'user_id' => (int)$user->id,
                        'row_id' => (int)$row->id,
                    ]);
                } else {
                    $update['qualification'] = json_encode($qValue ?? []);
                }
            }

            if (array_key_exists('affiliation', $data))      $update['affiliation']      = $data['affiliation'];
            if (array_key_exists('specification', $data))    $update['specification']    = $data['specification'];
            if (array_key_exists('experience', $data))       $update['experience']       = $data['experience'];
            if (array_key_exists('interest', $data))         $update['interest']         = $data['interest'];
            if (array_key_exists('administration', $data))   $update['administration']   = $data['administration'];
            if (array_key_exists('research_project', $data)) $update['research_project'] = $data['research_project'];

            if (empty($update)) {
                DB::commit();
                return response()->json(['success' => true, 'data' => $this->decodeQualificationRow($row)]);
            }

            $update['updated_at'] = $now;

            Log::info('PI.update.update_payload', $update);

            DB::table($this->table)->where('id', $row->id)->update($update);

            DB::commit();

            $fresh = DB::table($this->table)->where('id', $row->id)->first();
            $fresh = $this->decodeQualificationRow($fresh);

            $this->logWithActor('user_personal_information.update.success', $request, [
                'id'      => $row->id,
                'user_id' => (int)$user->id,
            ]);

            return response()->json(['success' => true, 'data' => $fresh]);

        } catch (\Throwable $e) {
            DB::rollBack();

            $this->logWithActor('user_personal_information.update.failed', $request, [
                'error'   => $e->getMessage(),
                'user_id' => (int)$user->id,
            ]);

            return response()->json(['success' => false, 'error' => 'Failed to update personal information'], 500);
        }
    }

    public function destroy(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','faculty','technical_assistant','it_person','student'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        if (!$this->canAccessUser($request, (int)$user->id)) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success' => false, 'error' => 'Personal information not found'], 404);

        $now   = Carbon::now();

        DB::table($this->table)->where('id', $row->id)->update([
            'deleted_at' => $now,
            'updated_at' => $now,
        ]);

        $this->logWithActor('user_personal_information.destroy', $request, [
            'id'      => $row->id,
            'user_id' => (int)$user->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Personal information deleted']);
    }

    public function restore(Request $request, ?string $user_uuid = null)
    {
        if ($resp = $this->requireRole($request, [
            'admin','director','principal','hod','technical_assistant','it_person'
        ])) return $resp;

        $user = $this->resolveTargetUser($request, $user_uuid);
        if (!$user) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        $actor = $this->actor($request);
        if (!$this->isHighRole($actor['role']) && $actor['id'] !== (int)$user->id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized Access'], 403);
        }

        $row = DB::table($this->table)
            ->where('user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->first();

        if (!$row) return response()->json(['success' => false, 'error' => 'No deleted personal information found'], 404);

        DB::table($this->table)->where('id', $row->id)->update([
            'deleted_at' => null,
            'updated_at' => Carbon::now(),
        ]);

        $this->logWithActor('user_personal_information.restore', $request, [
            'id'      => $row->id,
            'user_id' => (int)$user->id,
        ]);

        $fresh = DB::table($this->table)->where('id', $row->id)->first();
        $fresh = $this->decodeQualificationRow($fresh);

        return response()->json(['success' => true, 'data' => $fresh]);
    }
}
