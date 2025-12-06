<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="x-apple-disable-message-reformatting">
  <title>{{ $brand }} • Password reset</title>

  <style>
    body {
      margin: 0;
      padding: 0;
      background: #f3f4f6;
      -webkit-text-size-adjust: 100%;
      -ms-text-size-adjust: 100%;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
    }

    .mail-shell {
      max-width: 640px;
      width: 100%;
      background: #ffffff;
      border-radius: 16px;
      overflow: hidden;
      border: 1px solid #e5e7eb;
      box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
      margin: 0 auto;
    }

    .mail-header {
      padding: 16px 20px;
      border-bottom: 1px solid #f3f4f6;
      background: #ffffff;
    }

    .mail-logo {
      display: block;
      max-width: 150px;
      width: 140px;
      height: auto;
      border: 0;
      outline: 0;
    }

    .mail-header-right {
      text-align: right;
      vertical-align: middle;
    }

    .mail-subtitle {
      text-transform: uppercase;
      letter-spacing: .08em;
      font: 600 11px/1 system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
      color: #9ca3af;
      margin: 0 0 2px;
    }

    .brand-name {
      font: 700 16px/1.3 system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
      color: #111827;
      margin: 0;
    }

    .mail-body {
      padding: 20px 20px 18px;
      background: #ffffff;
    }

    .mail-title {
      font: 600 18px/1.4 system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
      color: #111827;
      margin: 0 0 10px;
    }

    .mail-text {
      font: 14px/1.7 system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
      color: #374151;
      margin: 0 0 12px;
    }

    .mail-btn-wrap {
      text-align: center;
      margin: 18px 0 20px;
    }

    .mail-btn {
      display: inline-block;
      padding: 11px 18px;
      border-radius: 999px;
      text-decoration: none;
      font: 600 14px/1 system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
      background: #7f1d1d;                  /* maroon */
      color: #f9fafb !important;
      border: 1px solid #7f1d1d;
      white-space: nowrap;
    }

    .mail-card {
      border-radius: 12px;
      border: 1px solid #e5e7eb;
      background: #f9fafb;
      padding: 10px 12px;
      margin: 0 0 10px;
    }

    .mail-card-label {
      font: 13px/1.6 system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
      color: #4b5563;
      margin: 0 0 4px;
    }

    .mail-code {
      font: 12px/1.5 ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      color: #111827;
      word-break: break-all;
    }

    .mail-muted {
      font: 12px/1.6 system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
      color: #6b7280;
      margin: 4px 0 0;
    }

    .mail-footer {
      padding: 12px 20px;
      background: #f9fafb;
      font: 12px/1.5 system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
      color: #6b7280;
    }
  </style>
</head>

<body>

  <!-- Preheader (hidden in inbox preview) -->
  <div style="display:none;max-height:0;overflow:hidden;opacity:0;">
    Reset your {{ $brand }} password. This link expires in {{ $ttlMinutes }} minutes.
  </div>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;">
    <tr>
      <td align="center" style="padding:20px 12px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="mail-shell">
          <!-- Header -->
          <tr>
            <td class="mail-header">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="vertical-align:middle;">
                    <a href="{{ url('/') }}" target="_blank" rel="noopener" style="text-decoration:none;border:0;outline:0;display:inline-block;">
                      <img src="{{ asset('assets/images/logo.png') }}" alt="{{ $brand }} logo" class="mail-logo">
                    </a>
                  </td>
                  <td class="mail-header-right">
                    <p class="mail-subtitle">Password reset</p>
                    <p class="brand-name">{{ $brand }}</p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td class="mail-body">
              <h1 class="mail-title">Reset your password</h1>

              <p class="mail-text">
                You requested to reset the password for <strong>{{ $email }}</strong>.
                Use the button below to set a new password. This link will be valid for
                <strong>{{ $ttlMinutes }} minutes</strong>.
              </p>

              <div class="mail-btn-wrap">
                <a href="{{ $resetUrl }}" class="mail-btn" target="_blank" rel="noopener">
                  Reset password
                </a>
              </div>

              <div class="mail-card">
                <p class="mail-card-label">
                  If the button doesn’t work, copy and paste this URL into your browser:
                </p>
                <span class="mail-code">{{ $resetUrl }}</span>
              </div>

              <p class="mail-muted">
                If you didn’t request a password reset, you can safely ignore this email.
                Your account password will remain unchanged.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td class="mail-footer">
              © {{ date('Y') }} {{ $brand }}. All rights reserved.
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
