<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /** Token validity in minutes */
    private int $ttlMinutes = 60;

    /**
     * Safely encode arrays/objects for JSON columns
     */
    private function asJsonOrNull($value): ?string
    {
        if ($value === null) return null;
        if (is_string($value)) return $value; // assume already JSON
        try {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function clip(?string $s, int $max): ?string
    {
        if ($s === null) return null;
        return mb_substr($s, 0, $max);
    }

    /**
     * Insert activity log row (never breaks API flow)
     */
    private function logActivity(
        Request $request,
        string $activity,
        string $module,
        string $tableName,
        ?int $recordId = null,
        ?string $note = null,
        ?array $changedFields = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $performedByOverride = null,
        ?string $performedByRoleOverride = null
    ): void {
        try {
            $actor = $request->user(); // may be null for public endpoints

            $performedBy = $performedByOverride ?? ($actor->id ?? null);
            $performedByRole = $performedByRoleOverride
                ?? ($actor->role ?? $actor->user_role ?? $actor->user_type ?? null);

            // performed_by is NOT nullable in migration, so use 0 for guest/unknown
            if (!$performedBy) {
                $performedBy = 0;
                $performedByRole = $performedByRole ?? 'guest';
            }

            DB::table('user_data_activity_log')->insert([
                'performed_by'       => (int) $performedBy,
                'performed_by_role'  => $this->clip($performedByRole, 50),
                'ip'                 => $this->clip($request->ip(), 45),
                'user_agent'         => $this->clip($request->userAgent(), 512),

                'activity'           => $this->clip($activity, 50),
                'module'             => $this->clip($module, 100),

                'table_name'         => $this->clip($tableName, 128),
                'record_id'          => $recordId,

                'changed_fields'     => $this->asJsonOrNull($changedFields),
                'old_values'         => $this->asJsonOrNull($oldValues),
                'new_values'         => $this->asJsonOrNull($newValues),

                'log_note'           => $note,

                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        } catch (\Throwable $e) {
            // Never interrupt main flow
            Log::info('activity_log.skip', ['err' => $e->getMessage(), 'module' => $module, 'activity' => $activity]);
        }
    }

    /**
     * Revoke Sanctum tokens for this user (security after password reset)
     */
    private function revokeLaravelTokens(int $userId, ?Request $request = null, ?string $trace = null): void
    {
        try {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\User')
                ->where('tokenable_id', $userId)
                ->delete();

            if ($request) {
                $this->logActivity(
                    $request,
                    'delete',
                    'auth.reset_password',
                    'personal_access_tokens',
                    null,
                    'Sanctum tokens revoked'
                    . ($trace ? (' | trace=' . $trace) : '')
                    . ' | user_id=' . $userId,
                    null,
                    null,
                    null,
                    $userId,
                    'user'
                );
            }
        } catch (\Throwable $e) {
            Log::info('pwd.revokeTokens.skip', ['msg' => $e->getMessage()]);

            if ($request) {
                $this->logActivity(
                    $request,
                    'delete',
                    'auth.reset_password',
                    'personal_access_tokens',
                    null,
                    'Failed to revoke Sanctum tokens'
                    . ($trace ? (' | trace=' . $trace) : '')
                    . ' | user_id=' . $userId
                    . ' | err=' . $e->getMessage(),
                    null,
                    null,
                    null,
                    $userId,
                    'user'
                );
            }
        }
    }

    /**
     * POST /api/auth/forgot-password
     * Body: { "email": "user@example.com", "redirect": "https://msit.example/reset-password" (optional) }
     */
    public function requestLink(Request $request)
    {
        $trace = 'pwd.request.' . Str::uuid()->toString();
        Log::info('pwd.request.start', ['trace' => $trace]);

        $data = $request->validate([
            'email'    => ['required', 'email:rfc,dns'],
            'redirect' => ['nullable', 'url', 'max:2048'],
        ]);

        $email = strtolower(trim($data['email']));

        // Activity log (attempt)
        $this->logActivity(
            $request,
            'create',
            'auth.forgot_password',
            'password_reset_tokens',
            null,
            'Forgot password link requested | trace=' . $trace . ' | email=' . $email
        );

        // Find user only from MSIT Home Builder "users" table
        $user = DB::table('users')
            ->where('email', $email)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            Log::info('pwd.request.not_found', ['trace' => $trace, 'email' => $email]);

            $this->logActivity(
                $request,
                'create',
                'auth.forgot_password',
                'users',
                null,
                'Email not found for forgot password | trace=' . $trace . ' | email=' . $email,
                null,
                null,
                null,
                0,
                'guest'
            );

            return response()->json([
                'status'  => 'error',
                'message' => 'We couldn’t find an account with that email. Please check the address and try again.',
            ], 404);
        }

        $userRole = $user->role ?? $user->user_role ?? $user->user_type ?? 'user';

        // Create token
        $tokenRaw    = Str::random(64);             // plain token (sent via email)
        $tokenHashed = hash('sha256', $tokenRaw);   // stored hash

        // Ensure one active token per email
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        DB::table('password_reset_tokens')->insert([
            'email'      => $user->email,
            'token'      => $tokenHashed,
            'created_at' => now(),
        ]);

        // Log token creation (DO NOT store token values)
        $this->logActivity(
            $request,
            'create',
            'auth.forgot_password',
            'password_reset_tokens',
            null,
            'Reset token created | trace=' . $trace . ' | user_id=' . $user->id . ' | email=' . $email,
            ['email'],
            null,
            null,
            (int) $user->id,
            (string) $userRole
        );

        // Build reset URL (frontend page)
        // Example default: https://your-app-url/reset-user-password
        $base = $data['redirect'] ?? rtrim(config('app.url'), '/') . '/reset-user-password';
        $sep  = str_contains($base, '?') ? '&' : '?';

        $resetUrl = rtrim($base, '/') . $sep . 'token=' . $tokenRaw . '&email=' . urlencode($user->email);

        // Send mail via your SMTP (.env)
        try {
            Mail::to($user->email)->send(
                new ResetPasswordMail($user->email, $resetUrl, $this->ttlMinutes)
            );
        } catch (\Throwable $e) {
            Log::error('pwd.mail.fail', [
                'trace' => $trace,
                'err'   => $e->getMessage(),
            ]);

            // Activity log for mail failure (do not break)
            $this->logActivity(
                $request,
                'create',
                'auth.forgot_password',
                'password_reset_tokens',
                null,
                'Mail send failed | trace=' . $trace . ' | user_id=' . $user->id . ' | email=' . $email . ' | err=' . $e->getMessage(),
                null,
                null,
                null,
                (int) $user->id,
                (string) $userRole
            );

            // Do NOT expose internal error to user
        }

        Log::info('pwd.request.done', [
            'trace' => $trace,
            'user_id' => $user->id,
        ]);

        // Activity log (success response issued)
        $this->logActivity(
            $request,
            'create',
            'auth.forgot_password',
            'password_reset_tokens',
            null,
            'Forgot password response sent | trace=' . $trace . ' | user_id=' . $user->id . ' | email=' . $email,
            null,
            null,
            null,
            (int) $user->id,
            (string) $userRole
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Reset link sent. Please check your inbox.',
        ], 200);
    }

    /**
     * GET /api/auth/reset-password/verify?email=&token=
     * Returns { status, message, data: { valid, ttl_minutes } }
     */
    public function verify(Request $request)
    {
        $trace = 'pwd.verify.' . Str::uuid()->toString();
        Log::info('pwd.verify.start', ['trace' => $trace]);

        $data = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string', 'min:40', 'max:200'],
        ]);

        $email = strtolower(trim($data['email']));
        $hash  = hash('sha256', $data['token']);

        $row = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $hash)
            ->where('created_at', '>=', now()->subMinutes($this->ttlMinutes))
            ->first();

        $valid = (bool) $row;

        // NOTE: verify is GET -> as per requirement, no activity log here

        return response()->json([
            'status'  => $valid ? 'success' : 'error',
            'message' => $valid ? 'Valid token' : 'Invalid or expired token',
            'data'    => [
                'valid'       => $valid,
                'ttl_minutes' => $this->ttlMinutes,
            ],
        ], $valid ? 200 : 422);
    }

    /**
     * POST /api/auth/reset-password
     * Body: { email, token, password, password_confirmation }
     */
    public function reset(Request $request)
    {
        $trace = 'pwd.reset.' . Str::uuid()->toString();
        Log::info('pwd.reset.start', ['trace' => $trace]);

        $data = $request->validate([
            'email'    => ['required', 'email'],
            'token'    => ['required', 'string', 'min:40', 'max:200'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
        ]);

        $email = strtolower(trim($data['email']));
        $hash  = hash('sha256', $data['token']);

        // Activity log (attempt) - do not log password/token values
        $this->logActivity(
            $request,
            'update',
            'auth.reset_password',
            'password_reset_tokens',
            null,
            'Password reset attempt | trace=' . $trace . ' | email=' . $email
        );

        // Validate token + TTL
        $row = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $hash)
            ->where('created_at', '>=', now()->subMinutes($this->ttlMinutes))
            ->first();

        if (!$row) {
            $this->logActivity(
                $request,
                'update',
                'auth.reset_password',
                'password_reset_tokens',
                null,
                'Invalid/expired reset token | trace=' . $trace . ' | email=' . $email
            );

            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid or expired token',
            ], 422);
        }

        // Find user again
        $user = DB::table('users')
            ->where('email', $email)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            $this->logActivity(
                $request,
                'update',
                'auth.reset_password',
                'users',
                null,
                'User not found during password reset | trace=' . $trace . ' | email=' . $email
            );

            return response()->json([
                'status'  => 'error',
                'message' => 'Account not found for this email.',
            ], 422);
        }

        $userRole = $user->role ?? $user->user_role ?? $user->user_type ?? 'user';

        // Update password in "users" table
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'password'   => Hash::make($data['password']),
                'updated_at' => now(),
            ]);

        // Activity log: password updated (do NOT store old/new password)
        $this->logActivity(
            $request,
            'update',
            'auth.reset_password',
            'users',
            (int) $user->id,
            'Password updated | trace=' . $trace . ' | user_id=' . $user->id . ' | email=' . $email,
            ['password'],
            null,
            null,
            (int) $user->id,
            (string) $userRole
        );

        // Revoke existing Sanctum tokens for security
        $this->revokeLaravelTokens((int) $user->id, $request, $trace);

        // Cleanup token(s)
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();

        // Activity log: token cleanup
        $this->logActivity(
            $request,
            'delete',
            'auth.reset_password',
            'password_reset_tokens',
            null,
            'Reset token(s) deleted | trace=' . $trace . ' | user_id=' . $user->id . ' | email=' . $email,
            null,
            null,
            null,
            (int) $user->id,
            (string) $userRole
        );

        Log::info('pwd.reset.done', [
            'trace'   => $trace,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'status'   => 'success',
            'message'  => 'Password updated. You can now sign in.',
            'redirect' => url('/'),   // change if your frontend login URL is different
        ], 200);
    }
}
