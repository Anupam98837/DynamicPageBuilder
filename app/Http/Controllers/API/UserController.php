<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserController extends Controller
{
    /** tokenable_type stored in personal_access_tokens (Sanctum) */
    private const USER_TYPE = 'App\\Models\\User';

    /**
     * MSIT Home Builder roles:
     * director, principal, hod, faculty, technical_assistant, it_person, student
     */
    private const ALLOWED_ROLES = [
        'admin',
        'director',
        'principal',
        'hod',
        'faculty',
        'technical_assistant',
        'it_person',
        'placement_officer',
        'student',
    ];

    private const ROLE_SHORT_MAP = [
    'admin'               => 'adm',
    'director'            => 'DIR',
    'principal'           => 'PRI',
    'hod'                 => 'HOD',
    'faculty'             => 'FAC',
    'technical_assistant' => 'TA',
    'it_person'           => 'IT',
    'placement_officer'   => 'TPO',   // ✅ added
    'student'             => 'STD',
];


    private const SELECT_COLUMNS = [
        'id',
        'uuid',
        'slug',
        'name',
        'email',
        'phone_number',
        'alternative_email',
        'alternative_phone_number',
        'whatsapp_number',
        'image',
        'address',
        'role',
        'role_short_form',
        'status',
        'last_login_at',
        'last_login_ip',
        'created_by',
        'created_at_ip',
        'metadata',
        'created_at',
        'updated_at',
    ];

    /* =========================
     * Auth / helpers
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
            return response()->json(['error' => 'Unauthorized Access'], 403);
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

    private function extractToken(Request $request): ?string
    {
        $header = (string) $request->header('Authorization', '');
        if (stripos($header, 'Bearer ') === 0) {
            $token = trim(substr($header, 7));
        } else {
            $token = trim($header);
        }
        return $token !== '' ? $token : null;
    }

    /**
     * Normalize a role + derive short form.
     * If invalid/missing, default to "faculty" + "FAC".
     */
    /**
 * Normalize a role + derive short form.
 * If invalid/missing, default to "faculty" + "FAC".
 */
private function normalizeRole(?string $role): array
{
    $role = $role !== null ? strtolower(trim($role)) : '';

    // normalize separators
    $role = str_replace([' ', '-'], '_', $role);
    $role = preg_replace('/_+/', '_', $role) ?? $role;
    $role = trim($role, '_');

    // aliases/synonyms
    if ($role === 'tech_assistant' || $role === 'techassistant') {
        $role = 'technical_assistant';
    }

    // ✅ placement officer aliases
    if (in_array($role, [
        'po',
        'tpo',
        'placement',
        'placementofficer',
        'placement_officer',
        'training_placement_officer',
        'trainingplacementofficer',
        'trainingandplacementofficer',
        'training_and_placement_officer',
        'placement_cell',
        'placementcell',
    ], true)) {
        $role = 'placement_officer';
    }

    if (!in_array($role, self::ALLOWED_ROLES, true)) {
        $role = 'faculty';
    }

    $short = self::ROLE_SHORT_MAP[$role] ?? strtoupper(substr($role, 0, 3));

    return [$role, $short];
}


    /**
     * Generate unique slug from name.
     */
    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'user';
        }

        $slug = $base;
        $i    = 1;

        while (true) {
            $q = DB::table('users')->where('slug', $slug);
            if ($ignoreId) {
                $q->where('id', '!=', $ignoreId);
            }

            if (!$q->exists()) {
                return $slug;
            }

            $slug = $base . '-' . $i;
            $i++;
        }
    }

    /* =====================================================
     * AUTH ENDPOINTS
     * ===================================================== */

    /**
     * POST /api/auth/login
     * Issue Sanctum token for MSIT Home Builder.
     */
    public function login(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $v->errors(),
            ], 422);
        }

        $data = $v->validated();

        $user = DB::table('users')
            ->where('email', $data['email'])
            ->whereNull('deleted_at')
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'error'   => 'Invalid credentials',
            ], 401);
        }

        if (isset($user->status) && $user->status !== 'active') {
            return response()->json([
                'success' => false,
                'error'   => 'Account is not active',
            ], 403);
        }

        $now         = Carbon::now();
        $plainToken  = Str::random(80);
        $hashedToken = hash('sha256', $plainToken);

        // Insert Sanctum token (manual, like Unzip Exam)
        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => self::USER_TYPE,
            'tokenable_id'   => $user->id,
            'name'           => 'msit-api',
            'token'          => $hashedToken,
            'abilities'      => json_encode(['*']),
            'last_used_at'   => null,
            'created_at'     => $now,
            'updated_at'     => $now,
            // 'expires_at'   => $now->copy()->addDays(7), // optional expiry if you want
        ]);

        // Track last login
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'last_login_at' => $now,
                'last_login_ip' => $request->ip(),
            ]);

        $fresh = DB::table('users')
            ->select(self::SELECT_COLUMNS)
            ->where('id', $user->id)
            ->first();

        Log::info('msit.auth.login.success', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'ip'      => $request->ip(),
        ]);

        return response()->json([
            'success'    => true,
            'token'      => $plainToken,
            'token_type' => 'Bearer',
            'user'       => $fresh,
        ]);
    }

    /**
     * POST /api/auth/logout
     * Invalidate current token (route is protected by checkRole).
     */
    public function logout(Request $request)
    {
        $token = $this->extractToken($request);
        if (!$token) {
            return response()->json([
                'success' => false,
                'error'   => 'No token provided',
            ], 401);
        }

        $hashed = hash('sha256', $token);

        $pat = DB::table('personal_access_tokens')
            ->where('token', $hashed)
            ->where('tokenable_type', self::USER_TYPE)
            ->first();

        if ($pat) {
            DB::table('personal_access_tokens')
                ->where('id', $pat->id)
                ->delete();
        }

        $this->logWithActor('msit.auth.logout', $request, [
            'token_id' => $pat->id ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * GET /api/auth/check
     * Validate token & return user if valid (for SPA "already logged in?" check).
     */
    public function authenticateToken(Request $request)
    {
        $token = $this->extractToken($request);
        if (!$token) {
            return response()->json([
                'success' => false,
                'error'   => 'No token provided',
            ], 401);
        }

        $hashed = hash('sha256', $token);

        $pat = DB::table('personal_access_tokens')
            ->where('token', $hashed)
            ->where('tokenable_type', self::USER_TYPE)
            ->first();

        if (!$pat) {
            return response()->json([
                'success' => false,
                'error'   => 'Invalid token',
            ], 401);
        }

        // Optional expiry guard (same style as CheckRole)
        if (isset($pat->expires_at) && $pat->expires_at !== null) {
            try {
                if (Carbon::now()->greaterThan(Carbon::parse($pat->expires_at))) {
                    DB::table('personal_access_tokens')->where('id', $pat->id)->delete();
                    return response()->json([
                        'success' => false,
                        'error'   => 'Token expired',
                    ], 401);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Invalid token',
                ], 401);
            }
        }

        $user = DB::table('users')
            ->select(self::SELECT_COLUMNS)
            ->where('id', $pat->tokenable_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error'   => 'User not found',
            ], 404);
        }

        if (isset($user->status) && $user->status !== 'active') {
            return response()->json([
                'success' => false,
                'error'   => 'Account is not active',
            ], 403);
        }

        // Optionally update last_used_at
        DB::table('personal_access_tokens')
            ->where('id', $pat->id)
            ->update([
                'last_used_at' => Carbon::now(),
                'updated_at'   => Carbon::now(),
            ]);

        return response()->json([
            'success' => true,
            'user'    => $user,
        ]);
    }

    /* =====================================================
     * USER MANAGEMENT ENDPOINTS
     * ===================================================== */

    /**
     * GET /api/users
     * List users with optional filters (role, search).
     * Allowed: director, principal, hod, technical_assistant, it_person
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $role   = $request->query('role');

        $query = DB::table('users')
            ->select(self::SELECT_COLUMNS)
            ->whereNull('deleted_at');

        if ($role) {
            $query->where('role', $role);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone_number', 'like', '%' . $search . '%');
            });
        }

        $users = $query
            ->orderBy('id', 'desc')
            ->limit(200)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }

    /**
     * POST /api/users
     * Create a new user.
     * Allowed: director, principal, it_person
     */
    public function store(Request $request)
    {

        $v = Validator::make($request->all(), [
            'name'                      => ['required', 'string', 'max:190'],
            'email'                     => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'                  => ['required', 'string', 'min:8'],
            'phone_number'              => ['nullable', 'string', 'max:32', 'unique:users,phone_number'],
            'alternative_email'         => ['nullable', 'email', 'max:255'],
            'alternative_phone_number'  => ['nullable', 'string', 'max:32'],
            'whatsapp_number'           => ['nullable', 'string', 'max:32'],
            'image'                     => ['nullable', 'string', 'max:255'],
            'address'                   => ['nullable', 'string'],
            'role'                      => ['nullable', 'string'],
            'status'                    => ['nullable', 'string', 'max:20'],
            'metadata'                  => ['nullable', 'array'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $v->errors(),
            ], 422);
        }

        $data  = $v->validated();
        [$role, $roleShort] = $this->normalizeRole($data['role'] ?? null);

        $now   = Carbon::now();
        $actor = $this->actor($request);

        DB::beginTransaction();

        try {
            $uuid = (string) Str::uuid();
            $slug = $this->generateUniqueSlug($data['name']);

            $id = DB::table('users')->insertGetId([
                'name'                     => $data['name'],
                'email'                    => $data['email'],
                'password'                 => Hash::make($data['password']),
                'uuid'                     => $uuid,
                'slug'                     => $slug,
                'phone_number'             => $data['phone_number']             ?? null,
                'alternative_email'        => $data['alternative_email']        ?? null,
                'alternative_phone_number' => $data['alternative_phone_number'] ?? null,
                'whatsapp_number'          => $data['whatsapp_number']          ?? null,
                'image'                    => $data['image']                    ?? null,
                'address'                  => $data['address']                  ?? null,
                'role'                     => $role,
                'role_short_form'          => $roleShort,
                'status'                   => $data['status']                   ?? 'active',
                'metadata'                 => array_key_exists('metadata', $data)
                    ? json_encode($data['metadata'])
                    : null,
                'created_by'               => $actor['id'] ?: null,
                'created_at_ip'            => $request->ip(),
                'created_at'               => $now,
                'updated_at'               => $now,
            ]);

            DB::commit();

            $this->logWithActor('msit.users.store.success', $request, ['user_id' => $id]);

            $user = DB::table('users')
                ->select(self::SELECT_COLUMNS)
                ->where('id', $id)
                ->first();

            return response()->json([
                'success' => true,
                'data'    => $user,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->logWithActor('msit.users.store.failed', $request, [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => 'Failed to create user',
            ], 500);
        }
    }

    /**
     * GET /api/users/{uuid}
     * Show a single user.
     * High roles can view anyone; others can only see themselves.
     */
    public function show(Request $request, string $uuid)
    {
        if ($resp = $this->requireRole($request, self::ALLOWED_ROLES)) {
            return $resp;
        }

        $user = DB::table('users')
            ->select(self::SELECT_COLUMNS)
            ->where('uuid', $uuid)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error'   => 'User not found',
            ], 404);
        }

        $actor      = $this->actor($request);
        $highRoles  = ['director', 'principal', 'hod', 'technical_assistant', 'it_person'];

        if (!in_array($actor['role'], $highRoles, true) && $actor['id'] !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'error'   => 'Unauthorized Access',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $user,
        ]);
    }

    /**
     * PUT/PATCH /api/users/{uuid}
     * Update profile (name, contact, image, address, role/status for high roles).
     */
    public function update(Request $request, string $uuid)
    {
        if ($resp = $this->requireRole($request, self::ALLOWED_ROLES)) {
            return $resp;
        }

        $user = DB::table('users')
            ->where('uuid', $uuid)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error'   => 'User not found',
            ], 404);
        }

        $actor     = $this->actor($request);
        $highRoles = ['director', 'principal', 'hod', 'technical_assistant', 'it_person'];

        $isSelf = $actor['id'] === (int) $user->id;
        $isHigh = in_array($actor['role'], $highRoles, true);

        if (!$isHigh && !$isSelf) {
            return response()->json([
                'success' => false,
                'error'   => 'Unauthorized Access',
            ], 403);
        }

        $v = Validator::make($request->all(), [
            'name'                     => ['sometimes', 'required', 'string', 'max:190'],
            'email'                    => [
                'sometimes', 'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password'                 => ['sometimes', 'nullable', 'string', 'min:8'],
            'phone_number'             => [
                'sometimes', 'nullable', 'string', 'max:32',
                Rule::unique('users', 'phone_number')->ignore($user->id),
            ],
            'alternative_email'        => ['sometimes', 'nullable', 'email', 'max:255'],
            'alternative_phone_number' => ['sometimes', 'nullable', 'string', 'max:32'],
            'whatsapp_number'          => ['sometimes', 'nullable', 'string', 'max:32'],
            'image'                    => ['sometimes', 'nullable', 'string', 'max:255'],
            'address'                  => ['sometimes', 'nullable', 'string'],
            'role'                     => ['sometimes', 'nullable', 'string'],
            'status'                   => ['sometimes', 'nullable', 'string', 'max:20'],
            'metadata'                 => ['sometimes', 'nullable', 'array'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $v->errors(),
            ], 422);
        }

        $data   = $v->validated();
        $update = [];
        $now    = Carbon::now();

        if (array_key_exists('name', $data)) {
            $update['name'] = $data['name'];
            $update['slug'] = $this->generateUniqueSlug($data['name'], (int) $user->id);
        }

        if (array_key_exists('email', $data)) {
            $update['email'] = $data['email'];
        }

        if ($isHigh && array_key_exists('password', $data) && $data['password']) {
            $update['password'] = Hash::make($data['password']);
        }

        if (array_key_exists('phone_number', $data)) {
            $update['phone_number'] = $data['phone_number'] ?? null;
        }
        if (array_key_exists('alternative_email', $data)) {
            $update['alternative_email'] = $data['alternative_email'] ?? null;
        }
        if (array_key_exists('alternative_phone_number', $data)) {
            $update['alternative_phone_number'] = $data['alternative_phone_number'] ?? null;
        }
        if (array_key_exists('whatsapp_number', $data)) {
            $update['whatsapp_number'] = $data['whatsapp_number'] ?? null;
        }
        if (array_key_exists('image', $data)) {
            $update['image'] = $data['image'] ?? null;
        }
        if (array_key_exists('address', $data)) {
            $update['address'] = $data['address'] ?? null;
        }

        // Only high roles can change role/status
        if ($isHigh && array_key_exists('role', $data)) {
            [$role, $short] = $this->normalizeRole($data['role']);
            $update['role']            = $role;
            $update['role_short_form'] = $short;
        }

        if ($isHigh && array_key_exists('status', $data)) {
            $update['status'] = $data['status'] ?? 'active';
        }

        if (array_key_exists('metadata', $data)) {
            $update['metadata'] = $data['metadata'] !== null
                ? json_encode($data['metadata'])
                : null;
        }

        if (empty($update)) {
            return response()->json([
                'success' => true,
                'data'    => $user, // nothing changed
            ]);
        }

        $update['updated_at'] = $now;

        DB::table('users')
            ->where('id', $user->id)
            ->update($update);

        $this->logWithActor('msit.users.update', $request, [
            'user_id' => $user->id,
        ]);

        $fresh = DB::table('users')
            ->select(self::SELECT_COLUMNS)
            ->where('id', $user->id)
            ->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh,
        ]);
    }

    /**
     * PATCH /api/users/{uuid}/password
     * Update password separately.
     */
    public function updatePassword(Request $request, string $uuid)
    {
        if ($resp = $this->requireRole($request, self::ALLOWED_ROLES)) {
            return $resp;
        }

        $user = DB::table('users')
            ->where('uuid', $uuid)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error'   => 'User not found',
            ], 404);
        }

        $actor     = $this->actor($request);
        $highRoles = ['director', 'principal', 'hod', 'technical_assistant', 'it_person'];

        $isSelf = $actor['id'] === (int) $user->id;
        $isHigh = in_array($actor['role'], $highRoles, true);

        if (!$isHigh && !$isSelf) {
            return response()->json([
                'success' => false,
                'error'   => 'Unauthorized Access',
            ], 403);
        }

        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if (!$isHigh || $isSelf) {
            $rules['current_password'] = ['required', 'string'];
        }

        $v = Validator::make($request->all(), $rules);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $v->errors(),
            ], 422);
        }

        $data = $v->validated();

        if (array_key_exists('current_password', $rules)) {
            if (!Hash::check($data['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'errors'  => [
                        'current_password' => ['Current password is incorrect'],
                    ],
                ], 422);
            }
        }

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'password'   => Hash::make($data['password']),
                'updated_at' => Carbon::now(),
            ]);

        $this->logWithActor('msit.users.update_password', $request, [
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ]);
    }

    /**
     * PATCH /api/users/{uuid}/image
     * Update profile image path.
     */
    public function updateImage(Request $request, string $uuid)
    {
        if ($resp = $this->requireRole($request, self::ALLOWED_ROLES)) {
            return $resp;
        }

        $user = DB::table('users')
            ->where('uuid', $uuid)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error'   => 'User not found',
            ], 404);
        }

        $actor     = $this->actor($request);
        $highRoles = ['director', 'principal', 'hod', 'technical_assistant', 'it_person'];

        $isSelf = $actor['id'] === (int) $user->id;
        $isHigh = in_array($actor['role'], $highRoles, true);

        if (!$isHigh && !$isSelf) {
            return response()->json([
                'success' => false,
                'error'   => 'Unauthorized Access',
            ], 403);
        }

        $v = Validator::make($request->all(), [
            'image' => ['required', 'string', 'max:255'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $v->errors(),
            ], 422);
        }

        $data = $v->validated();

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'image'      => $data['image'],
                'updated_at' => Carbon::now(),
            ]);

        $this->logWithActor('msit.users.update_image', $request, [
            'user_id' => $user->id,
        ]);

        $fresh = DB::table('users')
            ->select(self::SELECT_COLUMNS)
            ->where('id', $user->id)
            ->first();

        return response()->json([
            'success' => true,
            'data'    => $fresh,
        ]);
    }

    /**
     * DELETE /api/users/{uuid}
     * Soft delete a user.
     * Allowed: director, principal, it_person
     */
    public function destroy(Request $request, string $uuid)
    {
        if ($resp = $this->requireRole($request, [
            'director',
            'principal',
            'it_person',
        ])) {
            return $resp;
        }

        $user = DB::table('users')
            ->where('uuid', $uuid)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error'   => 'User not found',
            ], 404);
        }

        $actor = $this->actor($request);

        // Don't allow deleting yourself
        if ($actor['id'] === (int) $user->id) {
            return response()->json([
                'success' => false,
                'error'   => 'You cannot delete your own account',
            ], 422);
        }

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'deleted_at' => Carbon::now(),
                'status'     => 'inactive',
            ]);

        $this->logWithActor('msit.users.destroy', $request, [
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User deleted',
        ]);
    }

    /**
     * GET /api/me
     * View logged-in user's own profile (for all roles).
     */
    public function me(Request $request)
    {
        $actor = $this->actor($request);
        if (!$actor['id']) {
            return response()->json([
                'success' => false,
                'error'   => 'Unauthenticated',
            ], 401);
        }

        $user = DB::table('users')
            ->select(self::SELECT_COLUMNS)
            ->where('id', $actor['id'])
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error'   => 'User not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $user,
        ]);
    }
 /* ============================================
 | PUBLIC: Faculty Index
 | GET /api/public/faculty
 |============================================ */
public function facultyindex(Request $request)
{
    $page    = max(1, (int)$request->query('page', 1));
    $perPage = (int)$request->query('per_page', 12);
    $perPage = max(6, min(60, $perPage));

    $qText  = trim((string)$request->query('q', ''));
    $status = trim((string)$request->query('status', 'active')) ?: 'active';

    $sort = (string)$request->query('sort', 'created_at');
    $dir  = strtolower((string)$request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
    $allowedSort = ['created_at','updated_at','name','id'];
    if (!in_array($sort, $allowedSort, true)) $sort = 'created_at';

    // ✅ exclude roles
    $excludedRoles = ['super_admin', 'admin', 'student', 'students'];

    $base = DB::table('users as u')
        ->leftJoin('user_personal_information as upi', 'upi.user_id', '=', 'u.id')
        ->whereNull('u.deleted_at')
        ->whereNotIn('u.role', $excludedRoles)
        ->where('u.status', $status)
        ->where(function ($w) {
            $w->whereNull('upi.id')->orWhereNull('upi.deleted_at');
        });

    if ($qText !== '') {
        $term = '%' . $qText . '%';
        $base->where(function ($w) use ($term) {
            $w->where('u.name', 'like', $term)
              ->orWhere('u.email', 'like', $term)
              ->orWhere('upi.affiliation', 'like', $term)
              ->orWhere('upi.specification', 'like', $term)
              ->orWhere('upi.experience', 'like', $term)
              ->orWhere('upi.interest', 'like', $term)
              ->orWhere('upi.administration', 'like', $term)
              ->orWhere('upi.research_project', 'like', $term);
        });
    }

    $total    = (clone $base)->distinct('u.id')->count('u.id');
    $lastPage = max(1, (int)ceil($total / $perPage));

    $rows = (clone $base)
        ->select([
            'u.id',
            'u.uuid',
            'u.slug',
            'u.name',
            'u.email',            // ✅ include email
            'u.image',
            'u.role',
            'u.role_short_form',
            'u.status',
            'u.created_at',
            'u.updated_at',

            'upi.uuid as personal_info_uuid',
            'upi.qualification',
            'upi.affiliation',
            'upi.specification',
            'upi.experience',
            'upi.interest',
            'upi.administration',
            'upi.research_project',
        ])
        ->orderBy($sort === 'name' ? 'u.name' : 'u.' . $sort, $dir)
        ->orderBy('u.id', 'desc')
        ->forPage($page, $perPage)
        ->get();

    // ✅ fetch socials for these users
    $ids = $rows->pluck('id')->filter()->values()->all();
    $socialsByUserId = [];

    if (!empty($ids)) {
        $socialRows = DB::table('user_social_media as usm')
            ->select([
                'usm.user_id',
                'usm.platform',
                'usm.icon',
                'usm.link',
                'usm.sort_order',
                'usm.metadata',
            ])
            ->whereIn('usm.user_id', $ids)
            ->whereNull('usm.deleted_at')
            ->where('usm.active', 1)
            ->orderBy('usm.sort_order', 'asc')
            ->orderBy('usm.id', 'asc')
            ->get();

        foreach ($socialRows as $s) {
            $platform = strtolower(trim((string)$s->platform));
            $socialsByUserId[(int)$s->user_id][] = [
                'platform'   => $platform,
                'icon'       => (string)($s->icon ?? ''),
                'url'        => (string)($s->link ?? ''),
                'sort_order' => (int)($s->sort_order ?? 0),
                'metadata'   => $this->maybeJson($s->metadata),
            ];
        }
    }

    // attach socials onto each row object
    $rows->each(function ($r) use ($socialsByUserId) {
        $r->socials = $socialsByUserId[(int)$r->id] ?? [];
    });

    $items = $rows->map(fn($r) => $this->normalizeRow($r))->values()->all();

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

/* =========================
 | Helpers for Faculty API
 * ========================= */

protected function maybeJson($v)
{
    if ($v === null) return null;
    if (is_array($v) || is_object($v)) return $v;
    $s = trim((string)$v);
    if ($s === '') return null;
    try { return json_decode($s, true, 512, JSON_THROW_ON_ERROR); }
    catch (\Throwable $e) { return $v; }
}

protected function toUrl(?string $path): ?string
{
    $path = trim((string)$path);
    if ($path === '') return null;

    // already absolute
    if (preg_match('~^https?://~i', $path)) return $path;

    // normalize to /...
    $path = '/' . ltrim($path, '/');

    return rtrim(config('app.url'), '/') . $path;
}

protected function normalizeRow($r): array
{
    $qualification = $this->maybeJson($r->qualification);
    if (is_array($qualification)) {
        // keep array -> frontend can join
    } elseif ($qualification === null) {
        $qualification = null;
    } else {
        $qualification = (string)$qualification;
    }

    // website can also be stored as a "website" platform row (optional)
    $website = null;
    $socials = [];
    $rawSocials = is_array($r->socials ?? null) ? $r->socials : [];

    foreach ($rawSocials as $s) {
        $plat = strtolower(trim((string)($s['platform'] ?? '')));
        $url  = trim((string)($s['url'] ?? ''));

        if ($plat === 'website' || $plat === 'site' || $plat === 'web' || $plat === 'personal_website') {
            if ($website === null && $url !== '') $website = $url;
            continue; // don’t show website as icon
        }

        $socials[] = [
            'platform'   => $plat,
            'icon'       => (string)($s['icon'] ?? ''),
            'url'        => $url,
            'sort_order' => (int)($s['sort_order'] ?? 0),
        ];
    }

    return [
        'id' => (int)$r->id,
        'uuid' => (string)$r->uuid,
        'slug' => (string)($r->slug ?? ''),
        'name' => (string)($r->name ?? ''),
        'email' => (string)($r->email ?? ''),

        'image' => (string)($r->image ?? ''),
        'image_full_url' => $this->toUrl($r->image),

        // this line in your screenshot is basically "designation"
        'designation' => (string)($r->affiliation ?? ''),

        'qualification' => $qualification,
        'specification' => (string)($r->specification ?? ''),
        'experience' => (string)($r->experience ?? ''),

        'website' => $website,

        // ✅ socials come from user_social_media
        'socials' => $socials,
    ];
}

/* ============================================
 | PUBLIC: Placement Officer Index
 | GET /api/public/placement-officers
 |============================================ */
public function placementOfficerIndex(Request $request)
{
    $page    = max(1, (int)$request->query('page', 1));
    $perPage = (int)$request->query('per_page', 12);
    $perPage = max(6, min(60, $perPage));

    $qText  = trim((string)$request->query('q', ''));
    $status = trim((string)$request->query('status', 'active')) ?: 'active';

    $sort = (string)$request->query('sort', 'created_at');
    $dir  = strtolower((string)$request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
    $allowedSort = ['created_at','updated_at','name','id'];
    if (!in_array($sort, $allowedSort, true)) $sort = 'created_at';

    // ✅ Only placement roles (edit/add as per your project roles)
    $placementRoles = [
        'placement_officer',
        'placement_officer_admin',
        'tpo',
        'training_placement_officer',
        'placement',
        'placement_cell',
    ];

    $base = DB::table('users as u')
        ->leftJoin('user_personal_information as upi', 'upi.user_id', '=', 'u.id')
        ->whereNull('u.deleted_at')
        ->whereIn('u.role', $placementRoles)
        ->where('u.status', $status)
        ->where(function ($w) {
            $w->whereNull('upi.id')->orWhereNull('upi.deleted_at');
        });

    if ($qText !== '') {
        $term = '%' . $qText . '%';
        $base->where(function ($w) use ($term) {
            $w->where('u.name', 'like', $term)
              ->orWhere('u.email', 'like', $term)
              ->orWhere('upi.affiliation', 'like', $term)
              ->orWhere('upi.specification', 'like', $term)
              ->orWhere('upi.experience', 'like', $term)
              ->orWhere('upi.interest', 'like', $term)
              ->orWhere('upi.administration', 'like', $term)
              ->orWhere('upi.research_project', 'like', $term);
        });
    }

    $total    = (clone $base)->distinct('u.id')->count('u.id');
    $lastPage = max(1, (int)ceil($total / $perPage));

    $rows = (clone $base)
        ->select([
            'u.id',
            'u.uuid',
            'u.slug',
            'u.name',
            'u.email',            // ✅ include email
            'u.image',
            'u.role',
            'u.role_short_form',
            'u.status',
            'u.created_at',
            'u.updated_at',

            'upi.uuid as personal_info_uuid',
            'upi.qualification',
            'upi.affiliation',
            'upi.specification',
            'upi.experience',
            'upi.interest',
            'upi.administration',
            'upi.research_project',
        ])
        ->orderBy($sort === 'name' ? 'u.name' : 'u.' . $sort, $dir)
        ->orderBy('u.id', 'desc')
        ->forPage($page, $perPage)
        ->get();

    // ✅ fetch socials for these users
    $ids = $rows->pluck('id')->filter()->values()->all();
    $socialsByUserId = [];

    if (!empty($ids)) {
        $socialRows = DB::table('user_social_media as usm')
            ->select([
                'usm.user_id',
                'usm.platform',
                'usm.icon',
                'usm.link',
                'usm.sort_order',
                'usm.metadata',
            ])
            ->whereIn('usm.user_id', $ids)
            ->whereNull('usm.deleted_at')
            ->where('usm.active', 1)
            ->orderBy('usm.sort_order', 'asc')
            ->orderBy('usm.id', 'asc')
            ->get();

        foreach ($socialRows as $s) {
            $platform = strtolower(trim((string)$s->platform));
            $socialsByUserId[(int)$s->user_id][] = [
                'platform'   => $platform,
                'icon'       => (string)($s->icon ?? ''),
                'url'        => (string)($s->link ?? ''),
                'sort_order' => (int)($s->sort_order ?? 0),
                'metadata'   => $this->maybeJson($s->metadata),
            ];
        }
    }

    // attach socials onto each row object
    $rows->each(function ($r) use ($socialsByUserId) {
        $r->socials = $socialsByUserId[(int)$r->id] ?? [];
    });

    $items = $rows->map(fn($r) => $this->normalizeRow($r))->values()->all();

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
