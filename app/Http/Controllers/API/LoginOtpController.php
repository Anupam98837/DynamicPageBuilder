<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\LoginOtpMail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LoginOtpController extends Controller
{
    private const USER_TYPE = 'App\\Models\\User';
    private const OTP_TTL = 10; // minutes
    private const MAX_ATTEMPTS = 5;

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

    private function maskEmail(?string $email): ?string
    {
        $email = strtolower(trim((string) $email));

        if (!$email || !str_contains($email, '@')) {
            return $email ?: null;
        }

        [$name, $domain] = explode('@', $email, 2);

        $visible = substr($name, 0, min(2, strlen($name)));
        $masked  = $visible . str_repeat('*', max(0, strlen($name) - strlen($visible)));

        return $masked . '@' . $domain;
    }

    private function requestMeta(Request $request, array $extra = []): array
    {
        return array_merge([
            'ip'         => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'url'        => $request->fullUrl(),
            'method'     => $request->method(),
        ], $extra);
    }

    private function logStep(string $tag, array $data = []): void
    {
        Log::info($tag, $data);
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

    private function emailValidationRules(): array
    {
        return [
            'bail',
            'required',
            'email',
            function (string $attribute, mixed $value, \Closure $fail) {
                if (!$this->isAllowedEmail((string) $value)) {
                    $fail('Your email is not allowed.');
                }
            },
        ];
    }

    private function validationMessages(): array
    {
        return [
            'email.required' => 'Email is required.',
            'email.email'    => 'Please enter a valid email address.',
            'otp.required'   => 'OTP is required.',
            'otp.size'       => 'OTP must be 6 digits.',
        ];
    }

    private function findActiveUserByEmail(string $email): ?object
    {
        $query = DB::table('users')
            ->whereRaw('LOWER(email) = ?', [strtolower(trim($email))]);

        if (Schema::hasColumn('users', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $user = $query->first();

        if (!$user) {
            return null;
        }

        if (isset($user->status) && (string) $user->status !== 'active') {
            return null;
        }

        return $user;
    }

    private function getEnvMailerConfig(): array
    {
        return [
            'mailer'       => config('mail.default', 'smtp'),
            'from_address' => config('mail.from.address'),
            'from_name'    => config('mail.from.name'),
        ];
    }

    private function getActiveMailerSetting(): ?object
    {
        try {
            if (!Schema::hasTable('mailer_settings')) {
                return null;
            }

            $smtp = DB::table('mailer_settings')
                ->where('status', 'active')
                ->where('is_default', 1)
                ->orderByDesc('id')
                ->first();

            if (!$smtp) {
                $smtp = DB::table('mailer_settings')
                    ->where('status', 'active')
                    ->orderByDesc('id')
                    ->first();
            }

            return $smtp ?: null;
        } catch (\Throwable $e) {
            $this->logStep('LOGIN_OTP:MAILER_LOOKUP_FAILED', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function sendUsingEnvMailer(string $email, Mailable $mailable): void
    {
        $env = $this->getEnvMailerConfig();

        config([
            'mail.from.address' => $env['from_address'],
            'mail.from.name'    => $env['from_name'],
        ]);

        Mail::mailer($env['mailer'])
            ->to($email)
            ->send($mailable);
    }

    private function sendOtpMail(string $email, string $otp): void
    {
        $mailable = new LoginOtpMail($otp, $email);
        $smtp     = $this->getActiveMailerSetting();

        if (!$smtp) {
            $this->sendUsingEnvMailer($email, $mailable);
            return;
        }

        try {
            $smtpPassword = !empty($smtp->password)
                ? Crypt::decryptString($smtp->password)
                : null;

            config([
                'mail.mailers.dynamic_smtp' => [
                    'transport'  => $smtp->mailer ?: 'smtp',
                    'host'       => $smtp->host,
                    'port'       => (int) $smtp->port,
                    'encryption' => $smtp->encryption ?: null,
                    'username'   => $smtp->username,
                    'password'   => $smtpPassword,
                    'timeout'    => $smtp->timeout ?: null,
                    'auth_mode'  => null,
                ],
                'mail.from.address' => $smtp->from_address,
                'mail.from.name'    => $smtp->from_name,
            ]);

            Mail::mailer('dynamic_smtp')
                ->to($email)
                ->send($mailable);
        } catch (\Throwable $e) {
            $this->logStep('LOGIN_OTP:DB_MAILER_FAILED_FALLBACK_ENV', [
                'email' => $this->maskEmail($email),
                'error' => $e->getMessage(),
            ]);

            $this->sendUsingEnvMailer($email, $mailable);
        }
    }

    /**
     * Progressive unlimited delay:
     * 1st resend -> 30s
     * 2nd resend -> 1m
     * 3rd resend -> 2m
     * 4th resend -> 3m
     * 5th resend -> 4m
     * 6th+ resend -> 5m
     */
    private function getCooldown(int $userId, string $email): ?array
    {
        $now = now();

        $rows = DB::table('user_login_otps')
            ->where('user_id', $userId)
            ->whereRaw('LOWER(email) = ?', [strtolower(trim($email))])
            ->where('created_at', '>=', $now->copy()->subDay())
            ->orderByDesc('created_at')
            ->get(['created_at']);

        $count = $rows->count();

        $delays = [0, 30, 60, 120, 180, 240, 300];
        $wait   = $delays[min($count, count($delays) - 1)];

        if ($count === 0 || $wait <= 0) {
            return null;
        }

        $lastCreatedAt = Carbon::parse($rows->first()->created_at);
        $unlocksAt     = $lastCreatedAt->copy()->addSeconds($wait);

        if ($now->lt($unlocksAt)) {
            return [
                'message'      => 'Please wait before requesting another OTP.',
                'retry_after'  => $unlocksAt->toDateTimeString(),
                'seconds_left' => max(0, $now->diffInSeconds($unlocksAt, false)),
            ];
        }

        return null;
    }

    private function issueToken(object $user, Request $request): array
    {
        $now         = Carbon::now();
        $plainToken  = Str::random(80);
        $hashedToken = hash('sha256', $plainToken);

        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => self::USER_TYPE,
            'tokenable_id'   => $user->id,
            'name'           => 'msit-api',
            'token'          => $hashedToken,
            'abilities'      => json_encode(['*']),
            'last_used_at'   => null,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'last_login_at' => $now,
                'last_login_ip' => $request->ip(),
                'updated_at'    => $now,
            ]);

        $fresh = DB::table('users')
            ->select($this->userSelectColumns())
            ->where('id', $user->id)
            ->first();

        return [
            'token' => $plainToken,
            'user'  => $fresh,
        ];
    }

    /**
     * POST /api/auth/send-login-otp
     * Body: { email }
     */
    public function sendLoginOtp(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => $this->emailValidationRules(),
            ],
            $this->validationMessages()
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => $validator->errors()->first('email') ?: 'Validation failed.',
            ], 422);
        }

        $email = strtolower(trim((string) $request->input('email')));

        if (!$this->isAllowedEmail($email)) {
            return response()->json([
                'success' => false,
                'message' => 'Your email is not allowed.',
            ], 403);
        }

        $user = $this->findActiveUserByEmail($email);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No active account found with this email.',
            ], 404);
        }

        $cooldown = $this->getCooldown((int) $user->id, $email);

        if ($cooldown) {
            return response()->json([
                'success'      => false,
                'message'      => $cooldown['message'],
                'retry_after'  => $cooldown['retry_after'],
                'seconds_left' => $cooldown['seconds_left'],
            ], 429);
        }

        DB::table('user_login_otps')
            ->where('user_id', $user->id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('is_used', 0)
            ->update([
                'is_used'    => 1,
                'updated_at' => now(),
            ]);

        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('user_login_otps')->insert([
            'user_id'       => $user->id,
            'email'         => $email,
            'otp'           => $otp,
            'attempt_count' => 0,
            'is_used'       => 0,
            'system_ip'     => $request->ip(),
            'expires_at'    => now()->addMinutes(self::OTP_TTL),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        try {
            $this->sendOtpMail($email, $otp);
        } catch (\Throwable $e) {
            DB::table('user_login_otps')
                ->where('user_id', $user->id)
                ->whereRaw('LOWER(email) = ?', [$email])
                ->where('is_used', 0)
                ->delete();

            $this->logStep('LOGIN_OTP:SEND_FAILED', $this->requestMeta($request, [
                'email' => $this->maskEmail($email),
                'error' => $e->getMessage(),
            ]));

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ], 500);
        }

        $this->logStep('LOGIN_OTP:SEND_SUCCESS', $this->requestMeta($request, [
            'user_id' => $user->id,
            'email'   => $this->maskEmail($email),
        ]));

        return response()->json([
            'success'            => true,
            'message'            => 'OTP sent successfully.',
            'expires_in_minutes' => self::OTP_TTL,
        ]);
    }

    /**
     * POST /api/auth/login-with-otp
     * Body: { email, otp }
     */
    public function loginWithOtp(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => $this->emailValidationRules(),
                'otp'   => ['bail', 'required', 'string', 'size:6'],
            ],
            $this->validationMessages()
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => $validator->errors()->first('email')
                    ?: $validator->errors()->first('otp')
                    ?: 'Validation failed.',
            ], 422);
        }

        $email = strtolower(trim((string) $request->input('email')));
        $otp   = trim((string) $request->input('otp'));

        if (!$this->isAllowedEmail($email)) {
            return response()->json([
                'success' => false,
                'message' => 'Your email is not allowed.',
            ], 403);
        }

        $user = $this->findActiveUserByEmail($email);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No active account found with this email.',
            ], 404);
        }

        $record = DB::table('user_login_otps')
            ->where('user_id', $user->id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('is_used', 0)
            ->orderByDesc('id')
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'No active OTP found. Please request a new OTP.',
            ], 404);
        }

        if (now()->gt(Carbon::parse($record->expires_at))) {
            DB::table('user_login_otps')
                ->where('id', $record->id)
                ->update([
                    'is_used'    => 1,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please request a new OTP.',
                'expired' => true,
            ], 422);
        }

        if ((int) $record->attempt_count >= self::MAX_ATTEMPTS) {
            DB::table('user_login_otps')
                ->where('id', $record->id)
                ->update([
                    'is_used'    => 1,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => false,
                'message' => 'Too many wrong attempts. Please request a new OTP.',
            ], 429);
        }

        if ($otp !== (string) $record->otp) {
            $newAttempts = (int) $record->attempt_count + 1;

            DB::table('user_login_otps')
                ->where('id', $record->id)
                ->update([
                    'attempt_count' => $newAttempts,
                    'is_used'       => $newAttempts >= self::MAX_ATTEMPTS ? 1 : 0,
                    'updated_at'    => now(),
                ]);

            if ($newAttempts >= self::MAX_ATTEMPTS) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many wrong attempts. Please request a new OTP.',
                ], 429);
            }

            return response()->json([
                'success'            => false,
                'message'            => 'Incorrect OTP.',
                'attempts_remaining' => self::MAX_ATTEMPTS - $newAttempts,
            ], 422);
        }

        DB::table('user_login_otps')
            ->where('id', $record->id)
            ->update([
                'is_used'    => 1,
                'updated_at' => now(),
            ]);

        DB::table('user_login_otps')
            ->where('user_id', $user->id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('is_used', 0)
            ->update([
                'is_used'    => 1,
                'updated_at' => now(),
            ]);

        $auth = $this->issueToken($user, $request);

        $this->logStep('LOGIN_OTP:LOGIN_SUCCESS', $this->requestMeta($request, [
            'user_id' => $user->id,
            'email'   => $this->maskEmail($email),
        ]));

        return response()->json([
            'success'    => true,
            'message'    => 'Login successful.',
            'token'      => $auth['token'],
            'token_type' => 'Bearer',
            'user'       => $auth['user'],
        ]);
    }
}