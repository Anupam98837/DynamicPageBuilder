{{-- resources/views/emails/loginOtp.blade.php --}}
<!DOCTYPE html>
<html>
<body style="font-family:sans-serif;background:#f8fafc;padding:32px 0;margin:0;">
  <div style="max-width:420px;margin:0 auto;background:#fff;border-radius:16px;
              border:1px solid #e2e8f0;padding:36px 32px;box-shadow:0 4px 24px rgba(0,0,0,.07);">

    <div style="font-size:12px;color:#64748b;text-transform:uppercase;
                letter-spacing:.08em;margin-bottom:8px;">Login OTP</div>

    <h2 style="margin:0 0 20px;font-size:22px;color:#0f172a;">
      {{ config('app.name') }}
    </h2>

    <p style="color:#475569;font-size:15px;margin:0 0 8px;">
      Use the code below to log in to your account.
    </p>
    <p style="color:#94a3b8;font-size:13px;margin:0 0 28px;">
      This code expires in <strong>10 minutes</strong>.
    </p>

    <div style="background:#f1f5f9;border-radius:12px;padding:20px;
                text-align:center;letter-spacing:.25em;font-size:36px;
                font-weight:700;color:#9e363a;font-family:monospace;
                margin-bottom:28px;">
      {{ $otp }}
    </div>

    @if($userEmail)
    <p style="color:#64748b;font-size:13px;margin:0 0 16px;">
      Login email: <strong>{{ $userEmail }}</strong>
    </p>
    @endif

    <hr style="border:none;border-top:1px solid #e2e8f0;margin:0 0 16px;">

    <p style="color:#94a3b8;font-size:12px;margin:0;">
      If you did not request this, please ignore this email.
      Do not share this code with anyone.
    </p>
  </div>
</body>
</html>