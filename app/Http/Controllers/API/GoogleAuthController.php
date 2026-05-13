<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    private const USER_TYPE = 'App\\Models\\User';

    private const ALLOWED_EMAIL_DOMAINS = [
        'msit.edu.in',
        'hallienz.com',
    ];

    private const SELECT_COLUMNS = [
        'id',
        'uuid',
        'slug',
        'name',
        'name_short_form',
        'email',
        'google_id',
        'auth_provider',
        'google_avatar',
        'email_verified_at',
        'phone_number',
        'alternative_email',
        'alternative_phone_number',
        'whatsapp_number',
        'image',
        'address',
        'role',
        'role_short_form',
        'employee_id',
        'department_id',
        'status',
        'last_login_at',
        'last_login_ip',
        'created_by',
        'created_at_ip',
        'metadata',
        'created_at',
        'updated_at',
    ];

    protected ?array $selectColsCache = null;

    private function userSelectColumns(): array
    {
        if ($this->selectColsCache !== null) {
            return $this->selectColsCache;
        }

        $cols = [];
        foreach (self::SELECT_COLUMNS as $column) {
            if (Schema::hasColumn('users', $column)) {
                $cols[] = $column;
            }
        }

        $this->selectColsCache = $cols;

        return $cols;
    }

    private function loginPageUrl(): string
    {
        // change this if your login page path is different
        return url('/');
    }

    private function isAllowedEmail(string $email): bool
    {
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $atPos = strrpos($email, '@');
        if ($atPos === false) {
            return false;
        }

        $domain = substr($email, $atPos + 1);

        return in_array($domain, self::ALLOWED_EMAIL_DOMAINS, true);
    }

    private function baseUsersQuery()
    {
        $query = DB::table('users');

        if (Schema::hasColumn('users', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        return $query;
    }

    private function findUserByGoogleId(string $googleId): ?object
    {
        if (!Schema::hasColumn('users', 'google_id')) {
            return null;
        }

        $user = $this->baseUsersQuery()
            ->where('google_id', $googleId)
            ->first();

        return $user ?: null;
    }

    private function findUserByEmail(string $email): ?object
    {
        $user = $this->baseUsersQuery()
            ->whereRaw('LOWER(email) = ?', [strtolower(trim($email))])
            ->first();

        return $user ?: null;
    }

    private function isUserActive(object $user): bool
    {
        if (isset($user->status) && (string) $user->status !== 'active') {
            return false;
        }

        return true;
    }

    private function linkGoogleToUser(int $userId, string $googleId, string $avatar = ''): void
    {
        $update = [];

        if (Schema::hasColumn('users', 'google_id')) {
            $update['google_id'] = $googleId;
        }

        if (Schema::hasColumn('users', 'auth_provider')) {
            $update['auth_provider'] = 'google';
        }

        if (Schema::hasColumn('users', 'google_avatar')) {
            $update['google_avatar'] = $avatar !== '' ? $avatar : null;
        }

        if (Schema::hasColumn('users', 'email_verified_at')) {
            $existing = DB::table('users')->where('id', $userId)->value('email_verified_at');
            if (empty($existing)) {
                $update['email_verified_at'] = now();
            }
        }

        if (Schema::hasColumn('users', 'image') && $avatar !== '') {
            $existingImage = DB::table('users')->where('id', $userId)->value('image');
            if (empty($existingImage)) {
                $update['image'] = $avatar;
            }
        }

        if (Schema::hasColumn('users', 'updated_at')) {
            $update['updated_at'] = now();
        }

        if (!empty($update)) {
            DB::table('users')
                ->where('id', $userId)
                ->update($update);
        }
    }

    private function issueAccessToken(int $userId, Request $request): array
    {
        $now = Carbon::now();

        $plainToken = Str::random(80);
        $hashedToken = hash('sha256', $plainToken);

        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => self::USER_TYPE,
            'tokenable_id'   => $userId,
            'name'           => 'msit-api',
            'token'          => $hashedToken,
            'abilities'      => json_encode(['*']),
            'last_used_at'   => null,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        $update = [
            'last_login_at' => $now,
            'last_login_ip' => $request->ip(),
        ];

        if (Schema::hasColumn('users', 'updated_at')) {
            $update['updated_at'] = $now;
        }

        DB::table('users')
            ->where('id', $userId)
            ->update($update);

        $freshUser = DB::table('users')
            ->select($this->userSelectColumns())
            ->where('id', $userId)
            ->when(
                Schema::hasColumn('users', 'deleted_at'),
                fn ($q) => $q->whereNull('deleted_at')
            )
            ->first();

        return [
            'plain_token' => $plainToken,
            'user'        => $freshUser,
            'role'        => strtolower((string) ($freshUser->role ?? 'student')),
        ];
    }

    private function errorResponse(string $message, int $seconds = 4)
    {
        return response()->view('partials.google-error', [
            'message'      => $message,
            'loginUrl'     => $this->loginPageUrl(),
            'redirectAfter'=> $seconds,
        ]);
    }

    public function redirectToGoogle(Request $request)
    {
        session([
            'google_keep_login' => $request->boolean('keep'),
        ]);

        return Socialite::driver('google')
            ->stateless()
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();
        } catch (Throwable $e) {
            Log::error('auth.google.callback_failed', [
                'error' => $e->getMessage(),
                'ip'    => $request->ip(),
            ]);

            return $this->errorResponse('Google authentication failed. Please try again.');
        }

        $googleId = trim((string) ($googleUser->getId() ?? $googleUser->id ?? ''));
        $email    = strtolower(trim((string) ($googleUser->getEmail() ?? $googleUser->email ?? '')));
        $avatar   = trim((string) ($googleUser->getAvatar() ?? $googleUser->avatar ?? ''));

        if ($googleId === '' || $email === '') {
            return $this->errorResponse('Google account email or ID not found.');
        }

        // Restrict even after successful Google auth
        if (!$this->isAllowedEmail($email)) {
            return $this->errorResponse('Your email is not allowed. Please use your institute-approved email.');
        }

        DB::beginTransaction();

        try {
            $action = 'google_login';
            $user = $this->findUserByGoogleId($googleId);

            if ($user) {
                if (!$this->isUserActive($user)) {
                    DB::rollBack();
                    return $this->errorResponse('Your account is not active.');
                }

                // Safety check again if user was found by google_id
                if (!$this->isAllowedEmail((string) ($user->email ?? ''))) {
                    DB::rollBack();
                    return $this->errorResponse('Your email is not allowed. Please use your institute-approved email.');
                }

                $this->linkGoogleToUser((int) $user->id, $googleId, $avatar);
            } else {
                // Same protocol as OTP: only existing user is allowed
                $user = $this->findUserByEmail($email);

                if (!$user) {
                    DB::rollBack();
                    return $this->errorResponse('No active account found with this email.');
                }

                if (!$this->isUserActive($user)) {
                    DB::rollBack();
                    return $this->errorResponse('Your account is not active.');
                }

                if (!$this->isAllowedEmail((string) ($user->email ?? ''))) {
                    DB::rollBack();
                    return $this->errorResponse('Your email is not allowed. Please use your institute-approved email.');
                }

                $action = 'linked_login';
                $this->linkGoogleToUser((int) $user->id, $googleId, $avatar);
            }

            DB::commit();

            $tokenData = $this->issueAccessToken((int) $user->id, $request);
            $keep = (bool) session()->pull('google_keep_login', false);

            Log::info('auth.google.success', [
                'action'  => $action,
                'user_id' => $user->id,
                'email'   => $email,
                'role'    => $tokenData['role'],
                'ip'      => $request->ip(),
            ]);

            return response()->view('partials.google-success', [
                'token'       => $tokenData['plain_token'],
                'user'        => $tokenData['user'],
                'role'        => $tokenData['role'],
                'keep'        => $keep,
                'redirectUrl' => url('/dashboard'),
                'message'     => $action === 'linked_login'
                    ? 'Existing account linked with Google and logged in'
                    : 'Google login successful',
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('auth.google.failed', [
                'error' => $e->getMessage(),
                'email' => $email,
                'ip'    => $request->ip(),
            ]);

            return $this->errorResponse('Google authentication failed. Please try again.');
        }
    }
}