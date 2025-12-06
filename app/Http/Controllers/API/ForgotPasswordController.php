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
     * Revoke Sanctum tokens for this user (security after password reset)
     */
    private function revokeLaravelTokens(int $userId): void
    {
        try {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', 'App\\Models\\User')
                ->where('tokenable_id', $userId)
                ->delete();
        } catch (\Throwable $e) {
            Log::info('pwd.revokeTokens.skip', ['msg' => $e->getMessage()]);
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

        // Find user only from MSIT Home Builder "users" table
        $user = DB::table('users')
            ->where('email', $email)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            Log::info('pwd.request.not_found', ['trace' => $trace, 'email' => $email]);

            return response()->json([
                'status'  => 'error',
                'message' => 'We couldn’t find an account with that email. Please check the address and try again.',
            ], 404);
        }

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
            // Do NOT expose internal error to user
        }

        Log::info('pwd.request.done', [
            'trace' => $trace,
            'user_id' => $user->id,
        ]);

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

        // Validate token + TTL
        $row = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $hash)
            ->where('created_at', '>=', now()->subMinutes($this->ttlMinutes))
            ->first();

        if (!$row) {
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
            return response()->json([
                'status'  => 'error',
                'message' => 'Account not found for this email.',
            ], 422);
        }

        // Update password in "users" table
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'password'   => Hash::make($data['password']),
                'updated_at' => now(),
            ]);

        // Revoke existing Sanctum tokens for security
        $this->revokeLaravelTokens($user->id);

        // Cleanup token(s)
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();

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
