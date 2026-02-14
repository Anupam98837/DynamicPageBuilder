<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class UserProfileController extends Controller
{
    /**
     * =========================================================
     * AUTH (✅ aligned with your attribute-based auth)
     * =========================================================
     * Your other controller works because it reads:
     *   auth_role, auth_tokenable_id, auth_tokenable_type
     * This controller was using $request->user() which stays null
     * in your setup -> 401.
     */

    private function actor(Request $r): array
    {
        return [
            'role' => $r->attributes->get('auth_role'),
            'type' => $r->attributes->get('auth_tokenable_type'),
            'id'   => (int) ($r->attributes->get('auth_tokenable_id') ?? 0),
        ];
    }

    /**
     * Returns auth user object (Laravel guard user if available, else DB user by actor id).
     * NOTE: may return Eloquent model OR stdClass; both are OK for property access.
     */
    private function authUserRow(Request $r): ?object
    {
        $u = $r->user();
        if ($u && !empty($u->id)) return $u;

        $a = $this->actor($r);
        if (empty($a['id'])) return null;

        return DB::table('users')
            ->where('id', $a['id'])
            ->whereNull('deleted_at')
            ->first();
    }

    private function requireAuthUser(Request $r): object
    {
        $auth = $this->authUserRow($r);
        if (!$auth || empty($auth->id)) {
            $this->throwJson(401, 'Unauthenticated');
        }
        return $auth;
    }

    private function privilegedRoles(): array
    {
        return ['admin','director','principal','hod','it_person','placement_officer'];
    }

    /* =========================================================
     * ROUTES
     * ========================================================= */

    /**
     * GET /api/users/{user_uuid}/profile
     * -----------------------------------
     * PUBLIC SAFE PROFILE API
     * - UUID based
     * - No auth inside controller
     * (IMPORTANT: route must NOT be inside auth middleware group)
     */
    public function show(Request $request, string $user_uuid)
    {
        $user = DB::table('users')
            ->where('uuid', $user_uuid)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error'   => 'User not found'
            ], 404);
        }

        // Public profile -> keep only active social links
        return response()->json([
            'success' => true,
            'data'    => $this->buildProfile($user, false),
        ]);
    }

    /** GET /api/me/profile (protected) */
    public function me(Request $request)
    {
        $auth = $this->requireAuthUser($request);

        $user = DB::table('users')
            ->where('id', $auth->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        }

        // Editing self -> include inactive social links so UI doesn't look "not saved"
        return response()->json([
            'success' => true,
            'data'    => $this->buildProfile($user, true),
        ]);
    }

    /** POST /api/users/{user_uuid}/profile (protected) */
    public function store(Request $request, string $user_uuid)
    {
        $target = $this->findUserOr404($user_uuid);
        $this->assertCanEditTarget($request, $target);

        return $this->persistProfile($request, $target, 'store');
    }

    /** PUT/PATCH /api/users/{user_uuid}/profile (protected) */
    public function update(Request $request, string $user_uuid)
    {
        $target = $this->findUserOr404($user_uuid);
        $this->assertCanEditTarget($request, $target);

        return $this->persistProfile($request, $target, 'update');
    }

    /** POST /api/me/profile (protected) */
    public function storeMe(Request $request)
    {
        $auth = $this->requireAuthUser($request);

        $target = DB::table('users')->where('id', $auth->id)->whereNull('deleted_at')->first();
        if (!$target) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        return $this->persistProfile($request, $target, 'store');
    }

    /** PUT/PATCH /api/me/profile (protected) */
    public function updateMe(Request $request)
    {
        $auth = $this->requireAuthUser($request);

        $target = DB::table('users')->where('id', $auth->id)->whereNull('deleted_at')->first();
        if (!$target) return response()->json(['success' => false, 'error' => 'User not found'], 404);

        return $this->persistProfile($request, $target, 'update');
    }

    /* =========================================================
     * CORE PERSIST
     * ========================================================= */

    private function persistProfile(Request $request, object $user, string $mode)
    {
        $validator = Validator::make($request->all(), $this->rules((int)$user->id));
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error'   => 'Validation failed',
                'details' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();

        // unique checks (only if provided)
        if (isset($payload['basic']['email'])) {
            $email = $payload['basic']['email'];
            if ($email !== null && DB::table('users')->where('email', $email)->where('id', '<>', $user->id)->exists()) {
                return response()->json(['success' => false, 'error' => 'Email already in use'], 422);
            }
        }
        if (isset($payload['basic']['slug'])) {
            $slug = $payload['basic']['slug'];
            if ($slug !== null && DB::table('users')->where('slug', $slug)->where('id', '<>', $user->id)->exists()) {
                return response()->json(['success' => false, 'error' => 'Slug already in use'], 422);
            }
        }

        DB::beginTransaction();
        try {
            $noteBase = "Target user_uuid={$user->uuid}";

            /* ---------- BASIC (users table) ---------- */
            if (array_key_exists('basic', $payload)) {
                $this->persistBasic($request, $user, (array)$payload['basic'], $mode, $noteBase);
            }

            /* ---------- PERSONAL (single row) ---------- */
            if (array_key_exists('personal', $payload)) {
                $this->persistPersonal($request, $user, (array)($payload['personal'] ?? []), $mode, $noteBase);
            }

            /* ---------- LISTS ---------- */
            if (array_key_exists('educations', $payload)) {
                $this->persistList($request, 'user_educations', $user, (array)$payload['educations'], [
                    'education_level','degree_title','field_of_study','institution_name','university_name',
                    'enrollment_year','passing_year','grade_type','grade_value','location','certificate','description',
                ], $mode, $noteBase);
            }

            if (array_key_exists('honors', $payload)) {
                $this->persistList($request, 'user_honors', $user, (array)$payload['honors'], [
                    'title','honor_type','honouring_organization','honor_year','image','description',
                ], $mode, $noteBase);
            }

            if (array_key_exists('journals', $payload)) {
                $this->persistList($request, 'user_journals', $user, (array)$payload['journals'], [
                    'title','publication_organization','publication_year','url','image','description',
                ], $mode, $noteBase);
            }

            if (array_key_exists('conference_publications', $payload)) {
                $this->persistList($request, 'user_conference_publications', $user, (array)$payload['conference_publications'], [
                    'conference_name','title','publication_organization','publication_year','publication_type',
                    'domain','location','url','image','description',
                ], $mode, $noteBase);
            }

            if (array_key_exists('teaching_engagements', $payload)) {
                $this->persistList($request, 'user_teaching_engagements', $user, (array)$payload['teaching_engagements'], [
                    'organization_name','domain','description',
                ], $mode, $noteBase);
            }

            if (array_key_exists('social_media', $payload)) {
                $this->persistList($request, 'user_social_media', $user, (array)$payload['social_media'], [
                    'platform','icon','link','active','sort_order',
                ], $mode, $noteBase);
            }

            /* ---------- REMOVALS (soft-delete) ---------- */
            $this->applyRemovals($request, $user, $noteBase);

            DB::commit();

            $freshUser = DB::table('users')->where('id', $user->id)->first();

            return response()->json([
                'success' => true,
                'message' => $mode === 'store' ? 'Profile saved' : 'Profile updated',
                // include inactive socials for edit flows
                'data'    => $this->buildProfile($freshUser, true),
            ]);

        } catch (ValidationException $ve) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error'   => 'Validation failed',
                'details' => $ve->errors(),
            ], 422);

        } catch (HttpResponseException $he) {
            DB::rollBack();
            throw $he;

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error'   => 'Profile save failed',
                'details' => app()->environment('production') ? null : $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     * PERSIST HELPERS + LOGGING
     * ========================================================= */

    private function persistBasic(Request $request, object $user, array $basic, string $mode, string $noteBase)
    {
        $allowed = [
            'name','slug','email','phone_number','alternative_email','alternative_phone_number',
            'whatsapp_number','image','address',
        ];

        $adminOnly = ['role','role_short_form','status'];

        // ✅ use attribute-based auth if guard user missing
        $auth = $this->authUserRow($request);
        $actor = $this->actor($request);

        $role = $auth->role ?? $actor['role'] ?? null;
        $canEditAdminFields = $role && in_array($role, $this->privilegedRoles(), true);

        $data = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $basic)) $data[$f] = $basic[$f];
        }
        if ($canEditAdminFields) {
            foreach ($adminOnly as $f) {
                if (array_key_exists($f, $basic)) $data[$f] = $basic[$f];
            }
        }

        if (empty($data)) return;

        $before = DB::table('users')->where('id', $user->id)->first();

        // IMPORTANT: Query Builder does NOT auto-touch updated_at
        $updateData = $data;
        $updateData['updated_at'] = now();

        DB::table('users')->where('id', $user->id)->update($updateData);
        $after = DB::table('users')->where('id', $user->id)->first();

        // log only real fields, not updated_at
        $diff = $this->diffRow($before, $after, array_keys($data));
        if (!empty($diff['changed_fields'])) {
            $this->writeActivityLog(
                $request,
                'update',
                'user_profile',
                'users',
                (int)$user->id,
                $diff,
                "Basic updated. {$noteBase}"
            );
        }
    }

    private function persistPersonal(Request $request, object $user, array $personal, string $mode, string $noteBase)
    {
        $allowed = [
            'qualification','affiliation','specification','experience','interest','administration','research_project',
        ];

        $data = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $personal)) $data[$f] = $personal[$f];
        }

        if (array_key_exists('qualification', $data)) {
            $data['qualification'] = is_array($data['qualification'])
                ? json_encode($data['qualification'])
                : (is_string($data['qualification']) ? $data['qualification'] : json_encode([]));
        }

        if (empty($data)) return;

        $existing = DB::table('user_personal_information')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        // ✅ use attribute-based auth if guard user missing
        $auth = $this->authUserRow($request);
        $actor = $this->actor($request);

        $now  = now();

        if ($existing) {
            $before = $existing;

            $updateData = $data;
            $updateData['updated_at'] = $now;

            DB::table('user_personal_information')->where('id', $existing->id)->update($updateData);
            $after = DB::table('user_personal_information')->where('id', $existing->id)->first();

            $diff = $this->diffRow($before, $after, array_keys($data));
            if (!empty($diff['changed_fields'])) {
                $this->writeActivityLog(
                    $request,
                    'update',
                    'user_profile',
                    'user_personal_information',
                    (int)$existing->id,
                    $diff,
                    "Personal updated. {$noteBase}"
                );
            }
        } else {
            $insert = $data;
            $insert['uuid']          = (string) Str::uuid();
            $insert['user_id']       = $user->id;

            $actorId = (int)($auth->id ?? $actor['id'] ?? 0);
            $insert['created_by']    = $actorId ?: null;

            $insert['created_at_ip'] = $request->ip();
            $insert['created_at']    = $now;
            $insert['updated_at']    = $now;

            $newId = DB::table('user_personal_information')->insertGetId($insert);
            $after = DB::table('user_personal_information')->where('id', $newId)->first();

            $diff = [
                'changed_fields' => array_keys($data),
                'old_values'     => [],
                'new_values'     => $this->pickRow($after, array_keys($data)),
            ];

            $this->writeActivityLog(
                $request,
                'create',
                'user_profile',
                'user_personal_information',
                (int)$newId,
                $diff,
                "Personal created. {$noteBase}"
            );
        }
    }

    private function persistList(
        Request $request,
        string $table,
        object $user,
        array $items,
        array $allowedFields,
        string $mode,
        string $noteBase
    ) {
        // ✅ use attribute-based auth if guard user missing
        $auth = $this->authUserRow($request);
        $actor = $this->actor($request);

        $actorId = (int)($auth->id ?? $actor['id'] ?? 0);
        $now  = now();

        foreach ($items as $item) {
            if (!is_array($item)) continue;

            $uuid = $item['uuid'] ?? null;

            $data = [];
            foreach ($allowedFields as $f) {
                if (array_key_exists($f, $item)) $data[$f] = $item[$f];
            }

            // FIX: (bool)"false" becomes true, so use filter_var
            if ($table === 'user_social_media' && array_key_exists('active', $data)) {
                $parsed = filter_var($data['active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($parsed === null) {
                    unset($data['active']);
                } else {
                    $data['active'] = $parsed;
                }
            }

            if (empty($data)) continue;

            if ($uuid) {
                $before = DB::table($table)
                    ->where('user_id', $user->id)
                    ->where('uuid', $uuid)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$before) {
                    // if uuid sent but not found, ignore silently (safe)
                    continue;
                }

                $updateData = $data;
                $updateData['updated_at'] = $now;

                DB::table($table)
                    ->where('id', $before->id)
                    ->update($updateData);

                $after = DB::table($table)->where('id', $before->id)->first();

                $diff = $this->diffRow($before, $after, array_keys($data));
                if (!empty($diff['changed_fields'])) {
                    $this->writeActivityLog(
                        $request,
                        'update',
                        'user_profile',
                        $table,
                        (int)$before->id,
                        $diff,
                        "Item updated (uuid={$uuid}). {$noteBase}"
                    );
                }
            } else {
                // IMPORTANT: enforce DB-required fields on create (uuid missing)
                $this->requireOnCreate($table, $data);

                $insert = $data;
                $insert['uuid']          = (string) Str::uuid();
                $insert['user_id']       = $user->id;
                $insert['created_by']    = $actorId ?: null;
                $insert['created_at_ip'] = $request->ip();
                $insert['created_at']    = $now;
                $insert['updated_at']    = $now;

                $newId = DB::table($table)->insertGetId($insert);
                $after = DB::table($table)->where('id', $newId)->first();

                $diff = [
                    'changed_fields' => array_keys($data),
                    'old_values'     => [],
                    'new_values'     => $this->pickRow($after, array_keys($data)),
                ];

                $this->writeActivityLog(
                    $request,
                    'create',
                    'user_profile',
                    $table,
                    (int)$newId,
                    $diff,
                    "Item created (uuid={$after->uuid}). {$noteBase}"
                );
            }
        }
    }

    private function applyRemovals(Request $request, object $user, string $noteBase)
    {
        $map = [
            'educations_remove'              => 'user_educations',
            'honors_remove'                  => 'user_honors',
            'journals_remove'                => 'user_journals',
            'conference_publications_remove' => 'user_conference_publications',
            'teaching_engagements_remove'    => 'user_teaching_engagements',
            'social_media_remove'            => 'user_social_media',
        ];

        $now = now();

        foreach ($map as $key => $table) {
            $uuids = $request->input($key);
            if (!is_array($uuids) || empty($uuids)) continue;

            $rows = DB::table($table)
                ->where('user_id', $user->id)
                ->whereIn('uuid', $uuids)
                ->whereNull('deleted_at')
                ->get();

            if ($rows->isEmpty()) continue;

            DB::table($table)
                ->where('user_id', $user->id)
                ->whereIn('uuid', $uuids)
                ->whereNull('deleted_at')
                ->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);

            foreach ($rows as $r) {
                $diff = [
                    'changed_fields' => ['deleted_at'],
                    'old_values'     => ['deleted_at' => null],
                    'new_values'     => ['deleted_at' => (string) $now],
                ];

                $this->writeActivityLog(
                    $request,
                    'delete',
                    'user_profile',
                    $table,
                    $r->id ?? null,
                    $diff,
                    "Item soft-deleted (uuid={$r->uuid}). {$noteBase}"
                );
            }
        }
    }

    /**
     * Enforce DB non-null requirements for create (when uuid is missing)
     * Keeps partial updates possible (uuid present) without forcing required fields.
     */
    private function requireOnCreate(string $table, array $data): void
    {
        $requiredByTable = [
            'user_educations'              => ['education_level', 'institution_name'],
            'user_honors'                  => ['title'],
            'user_journals'                => ['title'],
            'user_conference_publications' => ['conference_name', 'title'],
            'user_teaching_engagements'    => ['organization_name'],
            'user_social_media'            => ['platform', 'link'],
        ];

        $req = $requiredByTable[$table] ?? [];
        if (!$req) return;

        $missing = [];
        foreach ($req as $f) {
            if (!array_key_exists($f, $data) || $data[$f] === null || trim((string)$data[$f]) === '') {
                $missing[] = $f;
            }
        }

        if ($missing) {
            throw ValidationException::withMessages([
                $table => ['Missing required fields for create: ' . implode(', ', $missing)]
            ]);
        }
    }

    /**
     * Insert into user_data_activity_log (✅ uses actor attributes if guard user missing)
     */
    private function writeActivityLog(
        Request $request,
        string $activity,
        string $module,
        string $tableName,
        ?int $recordId,
        array $diff,
        ?string $note = null
    ) {
        $auth  = $this->authUserRow($request);
        $actor = $this->actor($request);

        $actorId   = (int)($auth->id ?? $actor['id'] ?? 0);
        $actorRole = (string)($auth->role ?? $actor['role'] ?? null);

        // if somehow called without auth, skip logging safely
        if (!$actorId) return;

        DB::table('user_data_activity_log')->insert([
            'performed_by'      => $actorId,
            'performed_by_role' => $actorRole ?: null,
            'ip'                => (string) ($request->ip() ?? null),
            'user_agent'        => substr((string) ($request->userAgent() ?? ''), 0, 512),

            'activity'          => $activity,
            'module'            => $module,

            'table_name'        => $tableName,
            'record_id'         => $recordId,

            'changed_fields'    => !empty($diff['changed_fields']) ? json_encode(array_values($diff['changed_fields'])) : null,
            'old_values'        => !empty($diff['old_values']) ? json_encode($diff['old_values']) : null,
            'new_values'        => !empty($diff['new_values']) ? json_encode($diff['new_values']) : null,

            'log_note'          => $note,

            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    /**
     * Compute diff for given fields between two DB rows (stdClass)
     */
    private function diffRow($before, $after, array $fields): array
    {
        $changed = [];
        $old = [];
        $new = [];

        foreach ($fields as $f) {
            $b = $before ? ($before->{$f} ?? null) : null;
            $a = $after ? ($after->{$f} ?? null) : null;

            if (is_string($b)) $b = trim($b);
            if (is_string($a)) $a = trim($a);

            if ($b != $a) {
                $changed[] = $f;
                $old[$f] = $b;
                $new[$f] = $a;
            }
        }

        return [
            'changed_fields' => $changed,
            'old_values'     => $old,
            'new_values'     => $new,
        ];
    }

    private function pickRow($row, array $fields): array
    {
        $out = [];
        foreach ($fields as $f) {
            $out[$f] = $row ? ($row->{$f} ?? null) : null;
        }
        return $out;
    }

    /* =========================================================
     * BUILD RESPONSE (same as show)
     * ========================================================= */

    private function buildProfile(object $user, bool $includeInactiveSocial = false): array
    {
        $safe = function ($row, array $fields) {
            if (!$row) return null;
            return collect($row)->only($fields)->toArray();
        };

        $safeCollection = function ($rows, array $fields) {
            return collect($rows)->map(function ($r) use ($fields) {
                return collect($r)->only($fields)->toArray();
            })->values();
        };

        $decodeJson = function ($value) {
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [];
            }
            return is_array($value) ? $value : [];
        };

        return [
            'basic' => $safe($user, [
                'uuid',
                'name',
                'slug',
                'role',
                'role_short_form',
                'email',
                'phone_number',
                'alternative_email',
                'alternative_phone_number',
                'whatsapp_number',
                'image',
                'address',
                'status',
                'created_at'
            ]),

            'personal' => (function () use ($user, $safe, $decodeJson) {
                $row = DB::table('user_personal_information')
                    ->where('user_id', $user->id)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$row) return null;

                $data = $safe($row, [
                    'uuid',
                    'qualification',
                    'affiliation',
                    'specification',
                    'experience',
                    'interest',
                    'administration',
                    'research_project'
                ]);

                $data['qualification'] = $decodeJson($data['qualification'] ?? null);

                return $data;
            })(),

            'educations' => $safeCollection(
                DB::table('user_educations')
                    ->where('user_id', $user->id)
                    ->whereNull('deleted_at')
                    ->orderBy('passing_year', 'desc')
                    ->orderBy('id', 'desc')
                    ->get(),
                [
                    'uuid',
                    'education_level',
                    'degree_title',
                    'field_of_study',
                    'institution_name',
                    'university_name',
                    'enrollment_year',
                    'passing_year',
                    'grade_type',
                    'grade_value',
                    'location',
                    'certificate',
                    'description'
                ]
            ),

            'honors' => $safeCollection(
                DB::table('user_honors')
                    ->where('user_id', $user->id)
                    ->whereNull('deleted_at')
                    ->orderBy('honor_year', 'desc')
                    ->orderBy('id', 'desc')
                    ->get(),
                [
                    'uuid',
                    'title',
                    'honor_type',
                    'honouring_organization',
                    'honor_year',
                    'image',
                    'description'
                ]
            ),

            'journals' => $safeCollection(
                DB::table('user_journals')
                    ->where('user_id', $user->id)
                    ->whereNull('deleted_at')
                    ->orderByRaw('publication_year IS NULL, publication_year DESC')
                    ->orderBy('id', 'desc')
                    ->get(),
                [
                    'uuid',
                    'title',
                    'publication_organization',
                    'publication_year',
                    'url',
                    'image',
                    'description'
                ]
            ),

            'conference_publications' => $safeCollection(
                DB::table('user_conference_publications')
                    ->where('user_id', $user->id)
                    ->whereNull('deleted_at')
                    ->orderByRaw('publication_year IS NULL, publication_year DESC')
                    ->orderBy('id', 'desc')
                    ->get(),
                [
                    'uuid',
                    'conference_name',
                    'title',
                    'publication_organization',
                    'publication_year',
                    'publication_type',
                    'domain',
                    'location',
                    'url',
                    'image',
                    'description'
                ]
            ),

            'teaching_engagements' => $safeCollection(
                DB::table('user_teaching_engagements')
                    ->where('user_id', $user->id)
                    ->whereNull('deleted_at')
                    ->orderBy('id', 'desc')
                    ->get(),
                [
                    'uuid',
                    'organization_name',
                    'domain',
                    'description'
                ]
            ),

            'social_media' => (function () use ($user, $safeCollection, $includeInactiveSocial) {
                $q = DB::table('user_social_media')
                    ->where('user_id', $user->id)
                    ->whereNull('deleted_at')
                    ->orderBy('sort_order', 'asc');

                if (!$includeInactiveSocial) {
                    $q->where('active', true);
                    return $safeCollection($q->get(), ['uuid','platform','icon','link']);
                }

                return $safeCollection($q->get(), ['uuid','platform','icon','link','active','sort_order']);
            })(),
        ];
    }

    /* =========================================================
     * AUTHZ HELPERS
     * ========================================================= */

    private function throwJson(int $status, string $error, array $details = []): void
    {
        $payload = ['success' => false, 'error' => $error];
        if (!empty($details)) $payload['details'] = $details;

        throw new HttpResponseException(response()->json($payload, $status));
    }

    private function assertCanEditTarget(Request $request, object $targetUser): void
    {
        // ✅ now works with your attribute-based auth
        $auth  = $this->requireAuthUser($request);
        $actor = $this->actor($request);

        $authUuid = $auth->uuid ?? null;
        $role     = $auth->role ?? $actor['role'] ?? null;

        $isSelf = $authUuid && ($authUuid === $targetUser->uuid);
        $isPrivileged = $role && in_array($role, $this->privilegedRoles(), true);

        if (!$isSelf && !$isPrivileged) {
            $this->throwJson(403, 'Forbidden: you can update only your own profile');
        }
    }

    private function findUserOr404(string $user_uuid): object
    {
        $user = DB::table('users')
            ->where('uuid', $user_uuid)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            $this->throwJson(404, 'User not found');
        }

        return $user;
    }

    /* =========================================================
     * VALIDATION RULES (aligned with DB sizes)
     * ========================================================= */

    private function rules(int $userId): array
    {
        return [
            'basic' => 'sometimes|array',
            'basic.name' => 'sometimes|nullable|string|max:255',
            'basic.slug' => 'sometimes|nullable|string|max:255',
            'basic.role' => 'sometimes|nullable|string|max:120',
            'basic.role_short_form' => 'sometimes|nullable|string|max:50',
            'basic.email' => 'sometimes|nullable|email|max:255',
            'basic.phone_number' => 'sometimes|nullable|string|max:30',
            'basic.alternative_email' => 'sometimes|nullable|email|max:255',
            'basic.alternative_phone_number' => 'sometimes|nullable|string|max:30',
            'basic.whatsapp_number' => 'sometimes|nullable|string|max:30',
            'basic.image' => 'sometimes|nullable|string|max:2048',
            'basic.address' => 'sometimes|nullable|string|max:1000',
            'basic.status' => 'sometimes|nullable|string|max:50',

            'personal' => 'sometimes|nullable|array',
            'personal.qualification' => 'sometimes|nullable|array',
            'personal.affiliation' => 'sometimes|nullable|string|max:255',
            'personal.specification' => 'sometimes|nullable|string|max:255',
            'personal.experience' => 'sometimes|nullable|string|max:255',
            'personal.interest' => 'sometimes|nullable|string|max:1000',
            'personal.administration' => 'sometimes|nullable|string|max:1000',
            'personal.research_project' => 'sometimes|nullable|string|max:2000',

            'educations' => 'sometimes|array',
            'educations.*.uuid' => 'sometimes|nullable|string|max:50',
            'educations.*.education_level' => 'sometimes|nullable|string|max:120',
            'educations.*.degree_title' => 'sometimes|nullable|string|max:255',
            'educations.*.field_of_study' => 'sometimes|nullable|string|max:255',
            'educations.*.institution_name' => 'sometimes|nullable|string|max:255',
            'educations.*.university_name' => 'sometimes|nullable|string|max:255',
            'educations.*.enrollment_year' => 'sometimes|nullable|integer|min:1900|max:2100',
            'educations.*.passing_year' => 'sometimes|nullable|integer|min:1900|max:2100',
            'educations.*.grade_type' => 'sometimes|nullable|string|max:50',
            'educations.*.grade_value' => 'sometimes|nullable|string|max:50',
            'educations.*.location' => 'sometimes|nullable|string|max:255',
            'educations.*.certificate' => 'sometimes|nullable|string|max:255',
            'educations.*.description' => 'sometimes|nullable|string|max:4000',

            'honors' => 'sometimes|array',
            'honors.*.uuid' => 'sometimes|nullable|string|max:50',
            'honors.*.title' => 'sometimes|nullable|string|max:255',
            'honors.*.honor_type' => 'sometimes|nullable|string|max:120',
            'honors.*.honouring_organization' => 'sometimes|nullable|string|max:255',
            'honors.*.honor_year' => 'sometimes|nullable|integer|min:1900|max:2100',
            'honors.*.image' => 'sometimes|nullable|string|max:255',
            'honors.*.description' => 'sometimes|nullable|string|max:4000',

            'journals' => 'sometimes|array',
            'journals.*.uuid' => 'sometimes|nullable|string|max:50',
            'journals.*.title' => 'sometimes|nullable|string|max:255',
            'journals.*.publication_organization' => 'sometimes|nullable|string|max:255',
            'journals.*.publication_year' => 'sometimes|nullable|integer|min:1900|max:2100',
            'journals.*.url' => 'sometimes|nullable|string|max:500',
            'journals.*.image' => 'sometimes|nullable|string|max:255',
            'journals.*.description' => 'sometimes|nullable|string|max:4000',

            'conference_publications' => 'sometimes|array',
            'conference_publications.*.uuid' => 'sometimes|nullable|string|max:50',
            'conference_publications.*.conference_name' => 'sometimes|nullable|string|max:255',
            'conference_publications.*.title' => 'sometimes|nullable|string|max:255',
            'conference_publications.*.publication_organization' => 'sometimes|nullable|string|max:255',
            'conference_publications.*.publication_year' => 'sometimes|nullable|integer|min:1900|max:2100',
            'conference_publications.*.publication_type' => 'sometimes|nullable|string|max:120',
            'conference_publications.*.domain' => 'sometimes|nullable|string|max:255',
            'conference_publications.*.location' => 'sometimes|nullable|string|max:255',
            'conference_publications.*.url' => 'sometimes|nullable|string|max:500',
            'conference_publications.*.image' => 'sometimes|nullable|string|max:255',
            'conference_publications.*.description' => 'sometimes|nullable|string|max:4000',

            'teaching_engagements' => 'sometimes|array',
            'teaching_engagements.*.uuid' => 'sometimes|nullable|string|max:50',
            'teaching_engagements.*.organization_name' => 'sometimes|nullable|string|max:255',
            'teaching_engagements.*.domain' => 'sometimes|nullable|string|max:255',
            'teaching_engagements.*.description' => 'sometimes|nullable|string|max:4000',

            'social_media' => 'sometimes|array',
            'social_media.*.uuid' => 'sometimes|nullable|string|max:50',
            'social_media.*.platform' => 'sometimes|nullable|string|max:120',
            'social_media.*.icon' => 'sometimes|nullable|string|max:100',
            'social_media.*.link' => 'sometimes|nullable|string|max:500',
            'social_media.*.active' => 'sometimes|boolean',
            'social_media.*.sort_order' => 'sometimes|nullable|integer|min:0|max:999999',
        ];
    }
}
