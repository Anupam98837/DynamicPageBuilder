{{-- resources/views/auth/login-otp.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Login — {{ config('app.name', 'Hallienz Home Builder') }}</title>

  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/images/favicon/msit_logo.jpg') }}">


  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="{{ asset('/assets/css/common/main.css') }}"/>

  <style>
    html, body{
      min-height:100%;
      height:auto;
    }

    body.lx-auth-body{
      min-height:100vh;
      margin:0;
      overflow-x:hidden;
      overflow-y:auto;
      background:
        radial-gradient(circle at top left, rgba(201,75,80,.10), transparent 34%),
        radial-gradient(circle at bottom right, rgba(158,54,58,.08), transparent 30%),
        linear-gradient(180deg, #fff7f7 0%, #fff 52%, #fff8f8 100%);
      color:var(--text-color);
      font-family:var(--font-sans);
      position:relative;
    }

    .lx-auth-body::before,
    .lx-auth-body::after{
      content:"";
      position:fixed;
      border-radius:50%;
      pointer-events:none;
      z-index:0;
      filter:blur(16px);
      opacity:.55;
    }

    .lx-auth-body::before{
      width:240px;
      height:240px;
      top:-70px;
      left:-80px;
      background:rgba(201,75,80,.10);
    }

    .lx-auth-body::after{
      width:260px;
      height:260px;
      right:-90px;
      bottom:-90px;
      background:rgba(158,54,58,.08);
    }

    .lx-page{
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:28px 16px;
      position:relative;
      z-index:1;
    }

    .lx-shell{
      width:100%;
      max-width:560px;
      margin:auto;
    }

    .lx-brand-block{
      display:flex;
      flex-direction:column;
      align-items:center;
      justify-content:center;
      text-align:center;
      margin-bottom:18px;
    }

    .lx-brand{
      width:74px;
      height:74px;
      border-radius:22px;
      display:grid;
      place-items:center;
      background:#fff;
      border:1px solid rgba(158,54,58,.12);
      box-shadow:0 16px 32px rgba(158,54,58,.08);
      margin-bottom:14px;
    }

    .lx-brand img{
      max-width:46px;
      max-height:46px;
      display:block;
    }

    .lx-title{
      font-family:var(--font-head);
      font-weight:800;
      color:var(--primary-color);
      font-size:clamp(1.65rem, 2.8vw, 2.2rem);
      line-height:1.15;
      margin:0 0 8px;
      text-align:center;
    }

    .lx-sub{
      text-align:center;
      color:var(--muted-color);
      margin:0 auto;
      max-width:420px;
      font-size:.96rem;
      line-height:1.6;
    }

    .lx-card{
      background:rgba(255,255,255,.92);
      backdrop-filter:blur(10px);
      -webkit-backdrop-filter:blur(10px);
      border:1px solid rgba(158,54,58,.12);
      border-radius:24px;
      padding:22px 20px 18px;
      box-shadow:
        0 22px 48px rgba(158,54,58,.10),
        0 2px 10px rgba(0,0,0,.03);
      width:100%;
      max-width:100%;
      margin:0 auto;
    }

    @media (max-width: 576px){
      .lx-page{
        padding:18px 12px;
        align-items:flex-start;
      }

      .lx-shell{
        max-width:100%;
      }

      .lx-brand{
        width:66px;
        height:66px;
        border-radius:18px;
        margin-bottom:12px;
      }

      .lx-brand img{
        max-width:40px;
        max-height:40px;
      }

      .lx-card{
        padding:16px 14px 14px;
        border-radius:18px;
      }

      .lx-title{
        font-size:1.55rem;
      }

      .lx-sub{
        font-size:.90rem;
      }
    }

    .lx-google-btn{
      width:100%;
      min-height:54px;
      border-radius:16px;
      border:1px solid rgba(158,54,58,.16);
      background:#fff;
      color:#202124;
      display:flex;
      align-items:center;
      justify-content:center;
      gap:12px;
      padding:12px 16px;
      text-decoration:none;
      font-weight:700;
      box-shadow:0 8px 22px rgba(0,0,0,.04);
      transition:all .2s ease;
      margin-bottom:14px;
    }

    .lx-google-btn:hover{
      transform:translateY(-1px);
      box-shadow:0 14px 28px rgba(0,0,0,.08);
      color:#111827;
    }

    .lx-google-btn.loading{
      pointer-events:none;
      opacity:.82;
    }

    .lx-google-icon{
      width:20px;
      height:20px;
      flex:0 0 20px;
      display:block;
    }

    .lx-divider{
      position:relative;
      text-align:center;
      margin:12px 0 16px;
    }

    .lx-divider::before{
      content:"";
      position:absolute;
      left:0;
      right:0;
      top:50%;
      transform:translateY(-50%);
      border-top:1px solid rgba(158,54,58,.14);
    }

    .lx-divider span{
      position:relative;
      z-index:1;
      display:inline-block;
      padding:0 12px;
      background:rgba(255,255,255,.92);
      color:var(--muted-color);
      font-size:.84rem;
      font-weight:700;
    }

    .lx-label{
      font-weight:700;
      color:var(--ink);
      margin-bottom:8px;
    }

    .lx-control{
      height:52px;
      border-radius:14px;
      border:1px solid var(--line-strong);
      padding:0 14px;
      font-size:.98rem;
      background:#fff;
      box-shadow:none;
    }

    .lx-control:focus,
    .lx-captcha-input:focus,
    .lx-otp-digit:focus{
      border-color:var(--primary-color);
      box-shadow:0 0 0 .2rem rgba(158,54,58,.12);
      outline:none;
    }

    .lx-meta{
      font-size:.84rem;
      color:var(--muted-color);
      margin-top:6px;
    }

    .lx-alert{
      margin-bottom:14px;
      border-radius:14px;
      font-size:.92rem;
    }

    .lx-gated-section{
      transition:opacity .18s ease, filter .18s ease;
    }

    .lx-gated-section.is-locked{
      opacity:.72;
      filter:saturate(.72);
    }

    .lx-captcha{
      margin-top:12px;
      margin-bottom:14px;
      border:1px solid rgba(158,54,58,.12);
      border-radius:18px;
      padding:14px;
      background:linear-gradient(180deg, #fff, #fffafa);
      transition:opacity .18s ease, filter .18s ease, background .18s ease;
    }

    .lx-captcha.is-disabled{
      opacity:.68;
      filter:saturate(.72);
      background:linear-gradient(180deg, #fcfcfd, #f8f9fb);
    }

    .lx-captcha-head{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:8px;
      margin-bottom:10px;
      flex-wrap:wrap;
    }

    .lx-captcha-title{
      display:flex;
      align-items:center;
      gap:8px;
      font-weight:700;
      color:var(--ink);
      font-size:.95rem;
    }

    .lx-captcha-title i{
      color:var(--primary-color);
    }

    .lx-captcha-tip{
      font-size:.78rem;
      color:var(--muted-color);
      font-weight:600;
    }

    .lx-captcha-row{
      --bs-gutter-x:.55rem;
      --bs-gutter-y:.55rem;
      align-items:stretch;
    }

    .lx-captcha-canvas-wrap{
      width:100%;
      height:52px;
      min-height:52px;
      border:1px solid var(--line-strong);
      border-radius:14px;
      background:#fff;
      display:flex;
      align-items:center;
      justify-content:center;
      overflow:hidden;
      cursor:pointer;
      transition:background .18s ease, border-color .18s ease, opacity .18s ease;
    }

    .lx-captcha-canvas-wrap.is-disabled{
      background:#f6f7fb;
      cursor:not-allowed;
    }

    .lx-captcha-canvas{
      display:block;
      width:100%;
      height:52px;
      max-width:100%;
      cursor:pointer;
      user-select:none;
    }

    .lx-captcha-canvas-wrap.is-disabled .lx-captcha-canvas{
      cursor:not-allowed;
      opacity:.7;
    }

    .lx-captcha-input{
      height:52px;
      width:100%;
      border-radius:14px;
      border:1px solid var(--line-strong);
      padding:0 14px;
      background:#fff;
      font-weight:700;
      font-size:1rem;
      letter-spacing:.08em;
      text-transform:uppercase;
      transition:background .18s ease, opacity .18s ease;
    }

    .lx-captcha-input:disabled{
      background:#f6f7fb;
      cursor:not-allowed;
      opacity:.92;
    }

    .lx-captcha-input::placeholder{
      letter-spacing:normal;
      text-transform:none;
      font-weight:400;
    }

    .lx-send-btn{
      height:52px;
      width:100%;
      border:none;
      border-radius:14px;
      font-weight:700;
      color:#fff;
      background:linear-gradient(
        180deg,
        color-mix(in oklab, var(--primary-color) 92%, #fff 8%),
        var(--primary-color)
      );
      box-shadow:0 12px 24px rgba(158,54,58,.18);
      transition:var(--transition);
      white-space:nowrap;
    }

    .lx-send-btn:hover:not(:disabled){
      filter:brightness(.98);
      transform:translateY(-1px);
    }

    .lx-send-btn:disabled{
      opacity:.68;
      cursor:not-allowed;
      box-shadow:none;
      transform:none;
    }

    .lx-captcha-hint{
      margin-top:10px;
      font-size:.82rem;
      color:var(--muted-color);
    }

    .lx-otp-panel{
      border:1px dashed rgba(158,54,58,.22);
      border-radius:18px;
      padding:14px;
      background:linear-gradient(180deg, #fffdfd, #fff7f7);
      margin-bottom:14px;
    }

    .lx-otp-head{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      flex-wrap:wrap;
      margin-bottom:10px;
    }

    .lx-otp-title{
      display:flex;
      align-items:center;
      gap:8px;
      font-weight:700;
      color:var(--ink);
    }

    .lx-otp-title i{
      color:var(--primary-color);
    }

    .lx-otp-pill{
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding:6px 10px;
      border-radius:999px;
      background:#fff;
      border:1px solid rgba(158,54,58,.14);
      font-size:.78rem;
      font-weight:700;
      color:var(--muted-color);
    }

    .lx-otp-boxes{
      display:flex;
      align-items:center;
      justify-content:center;
      gap:10px;
      margin:12px 0 10px;
      flex-wrap:nowrap;
    }

    .lx-otp-digit{
      width:50px;
      height:58px;
      border-radius:14px;
      border:1px solid var(--line-strong);
      background:#fff;
      text-align:center;
      font-size:1.45rem;
      font-weight:800;
      color:var(--ink);
      transition:var(--transition);
    }

    .lx-otp-digit.filled{
      border-color:rgba(158,54,58,.35);
      background:color-mix(in srgb, #fff 90%, var(--primary-light) 10%);
    }

    .lx-otp-digit:disabled{
      background:#f6f7fb;
      cursor:not-allowed;
      opacity:.95;
    }

    .lx-otp-divider{
      width:8px;
      height:2px;
      border-radius:999px;
      background:rgba(158,54,58,.25);
      flex:0 0 auto;
    }

    @media (max-width: 520px){
      .lx-otp-boxes{
        gap:7px;
      }

      .lx-otp-digit{
        width:42px;
        height:52px;
        font-size:1.25rem;
      }

      .lx-otp-divider{
        width:6px;
      }
    }

    .lx-otp-foot{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      flex-wrap:wrap;
      margin-top:8px;
    }

    .lx-otp-status{
      font-size:.84rem;
      color:var(--muted-color);
      margin:0;
    }

    .lx-link-btn{
      border:none;
      background:transparent;
      padding:0;
      font-size:.84rem;
      font-weight:700;
      color:var(--primary-color);
      text-decoration:none;
    }

    .lx-link-btn:disabled{
      opacity:.55;
      cursor:not-allowed;
    }

    .lx-row{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px;
    }

    .d-none-force{
      display:none !important;
    }
  </style>
</head>
<body class="lx-auth-body">

<div class="lx-page">
  <div class="lx-shell">
    <div class="lx-brand-block">
      <div class="lx-brand">
        <img src="{{ asset('/assets/media/images/web/logo.png') }}" alt="{{ config('app.name', 'Hallienz Home Builder') }}">
      </div>

      <h1 class="lx-title">MSIT LOGIN</h1>
      <p class="lx-sub">
        Enter your email, solve the captcha, send OTP, and sign in automatically.
      </p>
    </div>

    <form class="lx-card" id="lxo_form" novalidate>
      <div id="lxo_alert" class="alert d-none lx-alert" role="alert"></div>

      <a
        id="lxo_google_btn"
        class="lx-google-btn"
        href="{{ route('auth.google.redirect') }}"
      >
        <svg class="lx-google-icon" viewBox="0 0 48 48" aria-hidden="true">
          <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.654 32.657 29.195 36 24 36c-6.627 0-12-5.373-12-12S17.373 12 24 12c3.059 0 5.851 1.154 7.972 3.028l5.657-5.657C34.053 6.053 29.277 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
          <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.851 1.154 7.972 3.028l5.657-5.657C34.053 6.053 29.277 4 24 4c-7.682 0-14.347 4.337-17.694 10.691z"/>
          <path fill="#4CAF50" d="M24 44c5.176 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.141 35.091 26.715 36 24 36c-5.174 0-9.62-3.326-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
          <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.085 5.571l.003-.002 6.19 5.238C36.971 38.529 44 33 44 24c0-1.341-.138-2.65-.389-3.917z"/>
        </svg>
        <span id="lxo_google_text">Continue with Google</span>
      </a>

      <div class="lx-divider">
        <span>or continue with OTP login</span>
      </div>

      <div class="mb-2">
        <label class="lx-label form-label" for="lxo_email">Institute Email</label>
        <input id="lxo_email"
               type="email"
               class="lx-control form-control"
               placeholder="you@msit.edu.in"
               autocomplete="username"
               required>
        <div class="lx-meta" id="lxo_email_meta">
          Enter your institute email first.
        </div>
      </div>

      <div id="lxo_gated_section" class="lx-gated-section is-locked">
        <div class="lx-captcha" id="lxo_captcha_card">
          <div class="lx-captcha-head">
            <div class="lx-captcha-title">
              <i class="fa-solid fa-shield-check"></i>
              <span>Captcha</span>
            </div>
            <div class="lx-captcha-tip" id="lxo_captcha_tip">Locked until allowed domain</div>
          </div>

          <div class="row lx-captcha-row">
            <div class="col-12 col-md-4">
              <div class="lx-captcha-canvas-wrap is-disabled" id="lxo_captcha_canvas_wrap" title="Click to refresh captcha">
                <canvas id="lxo_captcha_canvas" class="lx-captcha-canvas" aria-label="Captcha image"></canvas>
              </div>
            </div>

            <div class="col-12 col-md-5">
              <input id="lxo_captcha_input"
                     type="text"
                     class="lx-captcha-input"
                     maxlength="6"
                     autocomplete="off"
                     autocapitalize="characters"
                     spellcheck="false"
                     placeholder="Enter captcha"
                     disabled>
            </div>

            <div class="col-12 col-md-3">
              <button type="button" class="lx-send-btn" id="lxo_send_btn" disabled>
                <i class="fa-solid fa-paper-plane me-1"></i> OTP
              </button>
            </div>
          </div>

          <div class="lx-captcha-hint" id="lxo_captcha_hint">
            Type an allowed email domain to unlock this section.
          </div>
        </div>

        <div id="lxo_otp_panel" class="lx-otp-panel d-none-force">
          <div class="lx-otp-head">
            <div class="lx-otp-title">
              <i class="fa-solid fa-envelope-circle-check"></i>
              <span>Enter OTP</span>
            </div>

            <div class="lx-otp-pill" id="lxo_otp_pill">
              <i class="fa-regular fa-clock"></i>
              <span>Auto login on 6th digit</span>
            </div>
          </div>

          <div class="lx-otp-boxes" id="lxo_otp_boxes_wrap">
            <input type="text" class="lx-otp-digit" maxlength="1" inputmode="numeric" autocomplete="one-time-code" data-index="0" disabled>
            <input type="text" class="lx-otp-digit" maxlength="1" inputmode="numeric" data-index="1" disabled>
            <input type="text" class="lx-otp-digit" maxlength="1" inputmode="numeric" data-index="2" disabled>
            <span class="lx-otp-divider"></span>
            <input type="text" class="lx-otp-digit" maxlength="1" inputmode="numeric" data-index="3" disabled>
            <input type="text" class="lx-otp-digit" maxlength="1" inputmode="numeric" data-index="4" disabled>
            <input type="text" class="lx-otp-digit" maxlength="1" inputmode="numeric" data-index="5" disabled>
          </div>

          <div class="lx-otp-foot">
            <p class="lx-otp-status" id="lxo_otp_meta">
              After OTP is sent, enter the 6 digits. Login will happen automatically.
            </p>

            <button type="button" class="lx-link-btn" id="lxo_resend_btn" disabled>
              Resend OTP
            </button>
          </div>
        </div>
      </div>

      <div class="lx-row">
        <div class="form-check m-0">
          <input class="form-check-input" type="checkbox" id="lxo_keep">
          <label class="form-check-label" for="lxo_keep">Keep me logged in</label>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  const SEND_LOGIN_OTP_API = "/api/auth/send-login-otp";
  const LOGIN_WITH_OTP_API = "/api/auth/login-with-otp";
  const CHECK_API          = "/api/auth/check";

  const CAPTCHA_LENGTH = 6;
  const OTP_LENGTH = 6;
  const OTP_META_PREFIX = 'login_otp_meta:';
  const OTP_PENDING_PREFIX = 'login_otp_pending:';
  const ONE_DAY_MS = 24 * 60 * 60 * 1000;
  const OTP_LIFETIME_MS = 10 * 60 * 1000;

  // front-end allowed domain variables
  const ALLOWED_DOMAIN_1 = 'msit.edu.in';
  const ALLOWED_DOMAIN_2 = 'hallienz.com';

  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  const form              = document.getElementById('lxo_form');
  const emailIn           = document.getElementById('lxo_email');
  const emailMeta         = document.getElementById('lxo_email_meta');
  const alertEl           = document.getElementById('lxo_alert');
  const keepCb            = document.getElementById('lxo_keep');

  const googleBtn         = document.getElementById('lxo_google_btn');
  const googleText        = document.getElementById('lxo_google_text');

  const gatedSection      = document.getElementById('lxo_gated_section');
  const sendBtn           = document.getElementById('lxo_send_btn');
  const resendBtn         = document.getElementById('lxo_resend_btn');

  const otpPanel          = document.getElementById('lxo_otp_panel');
  const otpMeta           = document.getElementById('lxo_otp_meta');
  const otpPill           = document.getElementById('lxo_otp_pill');
  const otpBoxesWrap      = document.getElementById('lxo_otp_boxes_wrap');
  const otpInputs         = Array.from(document.querySelectorAll('.lx-otp-digit'));

  const captchaCard       = document.getElementById('lxo_captcha_card');
  const captchaCanvas     = document.getElementById('lxo_captcha_canvas');
  const captchaCanvasWrap = document.getElementById('lxo_captcha_canvas_wrap');
  const captchaInput      = document.getElementById('lxo_captcha_input');
  const captchaHint       = document.getElementById('lxo_captcha_hint');
  const captchaTip        = document.getElementById('lxo_captcha_tip');

  const state = {
    sendingOtp: false,
    verifyingOtp: false,
    otpSent: false,
    resendSeconds: 0,
    resendTimer: null,
    captchaText: '',
    captchaSolved: false,
    autoVerifyTimer: null,
  };

  function authHeaders() {
    return {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrf
    };
  }

  function normalizeEmail(v) {
    return (v || '').trim().toLowerCase();
  }

  function validEmail(v) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
  }

  function getEmailDomain(v) {
    const email = normalizeEmail(v);
    const atPos = email.lastIndexOf('@');
    if (atPos === -1) return '';
    return email.slice(atPos + 1);
  }

  function hasAllowedDomain(v) {
    const domain = getEmailDomain(v);
    return domain === ALLOWED_DOMAIN_1 || domain === ALLOWED_DOMAIN_2;
  }

  function isAllowedInstituteEmail(v) {
    return validEmail(v) && hasAllowedDomain(v);
  }

  function canUnlockBelowSection() {
    return isAllowedInstituteEmail(currentEmail()) && !state.sendingOtp && !state.verifyingOtp;
  }

  function showAlert(kind, msg) {
    alertEl.classList.remove('d-none', 'alert-danger', 'alert-success', 'alert-warning');
    alertEl.classList.add(
      'alert',
      kind === 'error' ? 'alert-danger' : (kind === 'warn' ? 'alert-warning' : 'alert-success')
    );
    alertEl.textContent = msg;
  }

  function clearAlert() {
    alertEl.classList.add('d-none');
    alertEl.textContent = '';
  }

  function setOtpPill(iconClass, text) {
    otpPill.innerHTML = `<i class="${iconClass}"></i><span>${text}</span>`;
  }

  function rolePath(role) {
    return '/dashboard';
  }

  const authStore = {
    set(token, role, keep) {
      sessionStorage.setItem('token', token);
      sessionStorage.setItem('role', role);

      if (keep) {
        localStorage.setItem('token', token);
        localStorage.setItem('role', role);
      } else {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
      }
    },
    clear() {
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('role');
      localStorage.removeItem('token');
      localStorage.removeItem('role');
    },
    getLocal() {
      return {
        token: localStorage.getItem('token'),
        role: localStorage.getItem('role')
      };
    }
  };

  function otpMetaKey(email) {
    return OTP_META_PREFIX + email;
  }

  function otpPendingKey(email) {
    return OTP_PENDING_PREFIX + email;
  }

  function readOtpMeta(email) {
    if (!email) {
      return { count: 0, lastSentAt: 0, cooldownUntil: 0 };
    }

    try {
      const raw = localStorage.getItem(otpMetaKey(email));
      const parsed = raw ? JSON.parse(raw) : {};
      const meta = {
        count: Number(parsed.count || 0),
        lastSentAt: Number(parsed.lastSentAt || 0),
        cooldownUntil: Number(parsed.cooldownUntil || 0),
      };

      if (meta.lastSentAt && (Date.now() - meta.lastSentAt > ONE_DAY_MS)) {
        localStorage.removeItem(otpMetaKey(email));
        return { count: 0, lastSentAt: 0, cooldownUntil: 0 };
      }

      return meta;
    } catch (e) {
      return { count: 0, lastSentAt: 0, cooldownUntil: 0 };
    }
  }

  function writeOtpMeta(email, meta) {
    if (!email) return;
    localStorage.setItem(otpMetaKey(email), JSON.stringify(meta));
  }

  function readPendingOtp(email) {
    if (!email) return null;

    try {
      const raw = localStorage.getItem(otpPendingKey(email));
      if (!raw) return null;

      const parsed = JSON.parse(raw);
      const expiresAt = Number(parsed.expiresAt || 0);

      if (!expiresAt || Date.now() > expiresAt) {
        localStorage.removeItem(otpPendingKey(email));
        return null;
      }

      return parsed;
    } catch (e) {
      return null;
    }
  }

  function writePendingOtp(email) {
    if (!email) return;

    localStorage.setItem(otpPendingKey(email), JSON.stringify({
      sentAt: Date.now(),
      expiresAt: Date.now() + OTP_LIFETIME_MS
    }));
  }

  function clearPendingOtp(email) {
    if (!email) return;
    localStorage.removeItem(otpPendingKey(email));
  }

  function delayForCount(count) {
    const delays = [30, 60, 120, 180, 240, 300];
    const index = Math.max(0, Math.min((count || 1) - 1, delays.length - 1));
    return delays[index];
  }

  function stopOtpCooldown() {
    if (state.resendTimer) {
      clearInterval(state.resendTimer);
      state.resendTimer = null;
    }
    state.resendSeconds = 0;
  }

  function currentEmail() {
    return normalizeEmail(emailIn.value);
  }

  function getOtpValue() {
    return otpInputs.map(input => (input.value || '').trim()).join('');
  }

  function clearOtpBoxes() {
    otpInputs.forEach(input => {
      input.value = '';
      input.classList.remove('filled');
    });
    clearTimeout(state.autoVerifyTimer);
  }

  function focusOtpBox(index = 0) {
    const target = otpInputs[index];
    if (!target || target.disabled) return;
    target.focus();
    target.select();
  }

  function focusFirstEmptyOtpBox() {
    const firstEmptyIndex = otpInputs.findIndex(input => !(input.value || '').trim());
    focusOtpBox(firstEmptyIndex === -1 ? otpInputs.length - 1 : firstEmptyIndex);
  }

  function setOtpEnabled(enabled) {
    otpInputs.forEach(input => {
      input.disabled = !enabled;
    });
  }

  function showOtpPanel(show) {
    if (show) {
      otpPanel.classList.remove('d-none-force');
    } else {
      otpPanel.classList.add('d-none-force');
    }
  }

  function renderOtpBoxesState() {
    otpInputs.forEach(input => {
      input.classList.toggle('filled', !!(input.value || '').trim());
    });
  }

  function setBelowSectionLocked(locked) {
    gatedSection.classList.toggle('is-locked', locked);
    captchaCard.classList.toggle('is-disabled', locked);
    captchaCanvasWrap.classList.toggle('is-disabled', locked);
    captchaInput.disabled = locked;

    if (locked) {
      captchaInput.value = '';
      state.captchaSolved = false;
      setOtpEnabled(false);
      resendBtn.disabled = true;
    }

    refreshSendButton();
    refreshResendButton();
  }

  function startOtpCooldown(seconds) {
    stopOtpCooldown();

    state.resendSeconds = Math.max(0, Number(seconds) || 0);
    refreshSendButton();
    refreshResendButton();

    if (state.resendSeconds <= 0) return;

    state.resendTimer = setInterval(() => {
      state.resendSeconds -= 1;

      if (state.resendSeconds <= 0) {
        stopOtpCooldown();
      }

      refreshSendButton();
      refreshResendButton();
    }, 1000);
  }

  function applySavedCooldown() {
    const email = currentEmail();

    if (!isAllowedInstituteEmail(email)) {
      stopOtpCooldown();
      refreshSendButton();
      refreshResendButton();
      return;
    }

    const meta = readOtpMeta(email);
    const remaining = Math.ceil((meta.cooldownUntil - Date.now()) / 1000);

    if (remaining > 0) {
      startOtpCooldown(remaining);
    } else {
      stopOtpCooldown();
      refreshSendButton();
      refreshResendButton();
    }
  }

  function markOtpSentSuccess() {
    const email = currentEmail();
    const meta = readOtpMeta(email);
    const nextCount = (meta.count || 0) + 1;
    const delay = delayForCount(nextCount);

    writeOtpMeta(email, {
      count: nextCount,
      lastSentAt: Date.now(),
      cooldownUntil: Date.now() + (delay * 1000)
    });

    writePendingOtp(email);
    startOtpCooldown(delay);
  }

  function applyServerCooldown(seconds) {
    const email = currentEmail();
    const meta = readOtpMeta(email);

    writeOtpMeta(email, {
      count: Number(meta.count || 0),
      lastSentAt: Number(meta.lastSentAt || 0),
      cooldownUntil: Date.now() + ((Number(seconds) || 0) * 1000)
    });

    startOtpCooldown(seconds);
  }

  function canSendOtp() {
    return isAllowedInstituteEmail(currentEmail()) &&
           state.captchaSolved &&
           !state.sendingOtp &&
           !state.verifyingOtp &&
           state.resendSeconds <= 0;
  }

  function canVerifyOtp() {
    return state.otpSent &&
           getOtpValue().length === OTP_LENGTH &&
           !state.verifyingOtp;
  }

  function refreshSendButton() {
    if (state.sendingOtp) {
      sendBtn.disabled = true;
      sendBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Sending';
      return;
    }

    if (!isAllowedInstituteEmail(currentEmail())) {
      sendBtn.disabled = true;
      sendBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> OTP';
      return;
    }

    if (!state.captchaSolved) {
      sendBtn.disabled = true;
      sendBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> OTP';
      return;
    }

    if (state.resendSeconds > 0) {
      sendBtn.disabled = true;
      sendBtn.innerHTML = `<i class="fa-regular fa-clock me-1"></i> ${state.resendSeconds}s`;
      return;
    }

    sendBtn.disabled = false;
    sendBtn.innerHTML = state.otpSent
      ? '<i class="fa-solid fa-rotate-right me-1"></i> Resend OTP'
      : '<i class="fa-solid fa-paper-plane me-1"></i> OTP';
  }

  function refreshResendButton() {
    if (!state.otpSent && !readPendingOtp(currentEmail())) {
      resendBtn.disabled = true;
      resendBtn.textContent = 'Resend OTP';
      return;
    }

    if (state.sendingOtp || state.verifyingOtp) {
      resendBtn.disabled = true;
      resendBtn.textContent = 'Please wait...';
      return;
    }

    if (state.resendSeconds > 0) {
      resendBtn.disabled = true;
      resendBtn.textContent = `Resend in ${state.resendSeconds}s`;
      return;
    }

    resendBtn.disabled = !isAllowedInstituteEmail(currentEmail()) || !state.captchaSolved;
    resendBtn.textContent = 'Resend OTP';
  }

  function resetOtpInputState(hidePanel = true) {
    state.otpSent = false;
    state.verifyingOtp = false;
    clearOtpBoxes();
    setOtpEnabled(false);
    if (hidePanel) showOtpPanel(false);
    otpMeta.textContent = 'After OTP is sent, enter the 6 digits. Login will happen automatically.';
    setOtpPill('fa-regular fa-clock', 'Auto login on 6th digit');
    renderOtpBoxesState();
    refreshResendButton();
  }

  function prepareOtpEntryState() {
    state.otpSent = true;
    showOtpPanel(true);
    setOtpEnabled(true);
    clearOtpBoxes();
    renderOtpBoxesState();
    setOtpPill('fa-solid fa-bolt', 'Auto verify enabled');
    refreshResendButton();
    setTimeout(() => focusOtpBox(0), 80);
  }

  function hydratePendingOtpState() {
    const email = currentEmail();
    const pending = readPendingOtp(email);

    if (isAllowedInstituteEmail(email) && pending) {
      showOtpPanel(true);
      state.otpSent = true;
      setOtpEnabled(true);
      otpMeta.textContent = 'A recent OTP is still active. Enter the 6 digits to log in.';
      setOtpPill('fa-solid fa-bolt', 'Auto verify enabled');
      refreshResendButton();
      return true;
    }

    resetOtpInputState(true);
    return false;
  }

  function resetViewFromEmailChange() {
    clearAlert();
    clearOtpBoxes();
    applySavedCooldown();

    if (!emailIn.value.trim()) {
      setEmailMeta('Enter your institute email first.');
      captchaTip.textContent = 'Locked until allowed domain';
      captchaHint.textContent = 'Type an allowed email domain to unlock this section.';
      setBelowSectionLocked(true);
      resetOtpInputState(true);
      refreshSendButton();
      return;
    }

    if (!validEmail(currentEmail())) {
      setEmailMeta('Please enter a valid email address.');
      captchaTip.textContent = 'Locked until allowed domain';
      captchaHint.textContent = 'Enter a proper email format first.';
      setBelowSectionLocked(true);
      resetOtpInputState(true);
      refreshSendButton();
      return;
    }

    if (!hasAllowedDomain(currentEmail())) {
      setEmailMeta('Your email is not allowed.', 'error');
      captchaTip.textContent = 'Allowed: msit.edu.in / hallienz.com';
      captchaHint.textContent = 'Below section stays disabled for other domains.';
      setBelowSectionLocked(true);
      resetOtpInputState(true);
      refreshSendButton();
      return;
    }

    setBelowSectionLocked(false);
    captchaTip.textContent = 'Click image to refresh';

    setEmailMeta(
      state.captchaSolved
        ? 'Captcha verified. You can send OTP now.'
        : 'Allowed email detected. Enter captcha to continue.'
    );

    if (!state.captchaSolved) {
      captchaHint.textContent = 'Type the captcha correctly to enable Send OTP.';
    }

    hydratePendingOtpState();
    refreshSendButton();
    refreshResendButton();
  }

  function generateCaptchaText(length = CAPTCHA_LENGTH) {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let out = '';
    for (let i = 0; i < length; i += 1) {
      out += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return out;
  }

  function drawCaptcha(text) {
    if (!captchaCanvas) return;

    const wrapWidth = Math.max(120, Math.floor(captchaCanvasWrap?.clientWidth || 140));
    const width = wrapWidth;
    const height = 52;
    const dpr = Math.max(1, window.devicePixelRatio || 1);

    captchaCanvas.width = width * dpr;
    captchaCanvas.height = height * dpr;
    captchaCanvas.style.width = '100%';
    captchaCanvas.style.height = height + 'px';

    const ctx = captchaCanvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, width, height);

    const bg = ctx.createLinearGradient(0, 0, width, height);
    bg.addColorStop(0, '#fff9fa');
    bg.addColorStop(1, '#fbe7e8');
    ctx.fillStyle = bg;
    ctx.fillRect(0, 0, width, height);

    ctx.strokeStyle = 'rgba(158,54,58,.18)';
    ctx.lineWidth = 1;
    ctx.strokeRect(0.5, 0.5, width - 1, height - 1);

    for (let i = 0; i < 5; i += 1) {
      ctx.beginPath();
      ctx.moveTo(Math.random() * width, Math.random() * height);
      ctx.bezierCurveTo(
        Math.random() * width, Math.random() * height,
        Math.random() * width, Math.random() * height,
        Math.random() * width, Math.random() * height
      );
      ctx.strokeStyle = i % 2 === 0 ? 'rgba(158,54,58,.14)' : 'rgba(201,75,80,.12)';
      ctx.lineWidth = 1;
      ctx.stroke();
    }

    for (let i = 0; i < 14; i += 1) {
      ctx.beginPath();
      ctx.arc(Math.random() * width, Math.random() * height, 1 + Math.random(), 0, Math.PI * 2);
      ctx.fillStyle = i % 2 === 0 ? 'rgba(107,37,40,.10)' : 'rgba(201,75,80,.10)';
      ctx.fill();
    }

    const chars = String(text || '').split('');
    const startX = 10;
    const usableWidth = Math.max(80, width - 20);
    const gap = usableWidth / Math.max(chars.length, 1);
    const fontSize = Math.max(18, Math.min(22, Math.floor(width / 7.2)));

    chars.forEach((ch, idx) => {
      const x = startX + idx * gap + 2;
      const y = 33 + (idx % 2 === 0 ? -2 : 2);
      const angle = (Math.random() * 0.34) - 0.17;

      ctx.save();
      ctx.translate(x, y);
      ctx.rotate(angle);
      ctx.font = `700 ${fontSize}px Inter, Arial, sans-serif`;
      ctx.fillStyle = idx % 2 === 0 ? '#6B2528' : '#9E363A';
      ctx.fillText(ch, 0, 0);
      ctx.restore();
    });
  }

  function resetCaptcha(message = 'Type the captcha correctly to enable Send OTP.') {
    state.captchaSolved = false;
    state.captchaText = generateCaptchaText();
    captchaInput.value = '';
    drawCaptcha(state.captchaText);
    captchaHint.textContent = canUnlockBelowSection()
      ? message
      : 'Type an allowed email domain to unlock this section.';
    refreshSendButton();
    refreshResendButton();
  }

  function validateCaptchaInput() {
    if (!canUnlockBelowSection()) {
      state.captchaSolved = false;
      captchaInput.value = '';
      captchaHint.textContent = 'Captcha is locked until email domain is allowed.';
      refreshSendButton();
      refreshResendButton();
      return;
    }

    const value = (captchaInput.value || '').replace(/\s+/g, '').toUpperCase().slice(0, CAPTCHA_LENGTH);
    captchaInput.value = value;

    if (!value) {
      state.captchaSolved = false;
      captchaHint.textContent = 'Type the captcha correctly to enable Send OTP.';
      resetViewFromEmailChange();
      return;
    }

    if (value === state.captchaText) {
      state.captchaSolved = true;
      captchaHint.textContent = 'Captcha verified. You can send OTP now.';
      resetViewFromEmailChange();
      return;
    }

    state.captchaSolved = false;

    if (value.length >= CAPTCHA_LENGTH) {
      captchaHint.textContent = 'Captcha does not match. Click the image to refresh.';
    } else {
      captchaHint.textContent = 'Keep typing the captcha shown in the image.';
    }

    refreshSendButton();
    refreshResendButton();
  }

  async function sendOtp() {
    if (!canSendOtp()) {
      if (!validEmail(currentEmail())) {
        showAlert('error', 'Please enter a valid email.');
      } else if (!hasAllowedDomain(currentEmail())) {
        showAlert('error', 'Your email is not allowed.');
      } else if (!state.captchaSolved) {
        showAlert('error', 'Please enter the correct captcha first.');
      }
      return;
    }

    clearAlert();
    state.sendingOtp = true;
    setBelowSectionLocked(true);
    refreshSendButton();
    refreshResendButton();

    resetOtpInputState(true);
    otpMeta.textContent = 'Sending OTP...';
    setOtpPill('fa-solid fa-spinner fa-spin', 'Sending code');

    try {
      const res = await fetch(SEND_LOGIN_OTP_API, {
        method: 'POST',
        headers: authHeaders(),
        body: JSON.stringify({ email: currentEmail() })
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok) {
        state.sendingOtp = false;
        setBelowSectionLocked(!isAllowedInstituteEmail(currentEmail()));
        resetOtpInputState(true);

        if (res.status === 429) {
          const seconds = Number(data?.seconds_left || 0);
          if (seconds > 0) {
            applyServerCooldown(seconds);
          }
          otpMeta.textContent = data?.message || 'Please wait before requesting another OTP.';
          setOtpPill('fa-regular fa-clock', 'Cooldown active');
          showAlert('warn', data?.message || 'Please wait before requesting another OTP.');
          refreshSendButton();
          refreshResendButton();
          return;
        }

        otpMeta.textContent = data?.message || 'Failed to send OTP.';
        setOtpPill('fa-solid fa-circle-xmark', 'Unable to send');
        showAlert('error', data?.message || 'Failed to send OTP.');
        refreshSendButton();
        refreshResendButton();
        return;
      }

      state.sendingOtp = false;
      setBelowSectionLocked(false);

      prepareOtpEntryState();
      markOtpSentSuccess();

      otpMeta.textContent = data?.message || 'OTP sent successfully. Enter all 6 digits.';
      setEmailMeta('OTP sent to your email.', 'success');
      setOtpPill('fa-solid fa-bolt', 'Auto verify enabled');
      showAlert('success', data?.message || 'OTP sent successfully.');
      refreshSendButton();
      refreshResendButton();
    } catch (err) {
      state.sendingOtp = false;
      setBelowSectionLocked(!isAllowedInstituteEmail(currentEmail()));
      resetOtpInputState(true);
      otpMeta.textContent = 'Network error while sending OTP.';
      setOtpPill('fa-solid fa-triangle-exclamation', 'Network issue');
      showAlert('error', 'Network error while sending OTP.');
      refreshSendButton();
      refreshResendButton();
    }
  }

  async function loginWithOtp() {
    const email = currentEmail();
    const otp = getOtpValue().replace(/\D/g, '').slice(0, OTP_LENGTH);

    if (!validEmail(email)) {
      showAlert('error', 'Please enter a valid email.');
      return;
    }

    if (!hasAllowedDomain(email)) {
      showAlert('error', 'Your email is not allowed.');
      return;
    }

    if (!state.otpSent) {
      showAlert('error', 'Please send OTP first.');
      return;
    }

    if (otp.length !== OTP_LENGTH || state.verifyingOtp) {
      return;
    }

    clearAlert();
    state.verifyingOtp = true;
    setOtpEnabled(false);
    setBelowSectionLocked(true);
    otpMeta.textContent = 'Verifying OTP and logging you in...';
    setOtpPill('fa-solid fa-spinner fa-spin', 'Verifying');
    refreshSendButton();
    refreshResendButton();

    try {
      const res = await fetch(LOGIN_WITH_OTP_API, {
        method: 'POST',
        headers: authHeaders(),
        body: JSON.stringify({
          email: email,
          otp: otp
        })
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok) {
        state.verifyingOtp = false;
        setOtpEnabled(true);
        setBelowSectionLocked(!isAllowedInstituteEmail(currentEmail()));

        if (data?.expired || res.status === 404 || res.status === 429) {
          state.otpSent = false;
          clearPendingOtp(email);
          clearOtpBoxes();
          setOtpEnabled(false);
          otpMeta.textContent = data?.message || 'OTP expired. Please request a new OTP.';
          setOtpPill('fa-regular fa-clock', 'Request new OTP');
          showAlert('error', data?.message || 'OTP expired. Please request a new OTP.');
          refreshSendButton();
          refreshResendButton();
          return;
        }

        otpMeta.textContent = data?.message || 'OTP verification failed.';
        setOtpPill('fa-solid fa-circle-xmark', 'Incorrect OTP');
        showAlert('error', data?.message || 'OTP verification failed.');
        clearOtpBoxes();
        renderOtpBoxesState();
        focusOtpBox(0);
        refreshSendButton();
        refreshResendButton();
        return;
      }

      const token = data?.token || data?.access_token || '';
      const role = (data?.user?.role || localStorage.getItem('role') || 'student').toLowerCase();

      if (!token) {
        state.verifyingOtp = false;
        setOtpEnabled(true);
        setBelowSectionLocked(!isAllowedInstituteEmail(currentEmail()));
        otpMeta.textContent = 'No token received from server.';
        setOtpPill('fa-solid fa-circle-xmark', 'Login failed');
        showAlert('error', 'No token received from server.');
        focusOtpBox(0);
        refreshSendButton();
        refreshResendButton();
        return;
      }

      clearPendingOtp(email);
      otpMeta.textContent = data?.message || 'Login successful. Redirecting...';
      setOtpPill('fa-solid fa-circle-check', 'Verified');
      showAlert('success', data?.message || 'Login successful. Redirecting...');

      authStore.set(token, role, !!keepCb.checked);

      setTimeout(() => {
        window.location.assign(rolePath(role));
      }, 450);
    } catch (err) {
      state.verifyingOtp = false;
      setOtpEnabled(true);
      setBelowSectionLocked(!isAllowedInstituteEmail(currentEmail()));
      otpMeta.textContent = 'Network error while verifying OTP.';
      setOtpPill('fa-solid fa-triangle-exclamation', 'Network issue');
      showAlert('error', 'Network error while verifying OTP.');
      focusFirstEmptyOtpBox();
      refreshSendButton();
      refreshResendButton();
    }
  }

  async function tryAutoLoginFromLocal() {
    const { token, role } = authStore.getLocal();
    if (!token) return;

    try {
      const res = await fetch(CHECK_API, {
        headers: { 'Authorization': 'Bearer ' + token }
      });

      const data = await res.json().catch(() => ({}));

      if (res.ok && data && data.user) {
        const resolvedRole = (data.user.role || role || '').toString().toLowerCase();
        authStore.set(token, resolvedRole, true);
        window.location.replace(rolePath(resolvedRole));
      } else {
        authStore.clear();
      }
    } catch (e) {}
  }

  function setEmailMeta(message, type = 'normal') {
    emailMeta.textContent = message;
    emailMeta.classList.remove('text-danger', 'text-success');

    if (type === 'error') {
      emailMeta.classList.add('text-danger');
    } else if (type === 'success') {
      emailMeta.classList.add('text-success');
    }
  }

  function triggerOtpAutoVerify() {
    clearTimeout(state.autoVerifyTimer);

    if (!canVerifyOtp()) return;

    state.autoVerifyTimer = setTimeout(() => {
      loginWithOtp();
    }, 160);
  }

  googleBtn?.addEventListener('click', (e) => {
    e.preventDefault();

    googleBtn.classList.add('loading');
    if (googleText) {
      googleText.textContent = 'Redirecting to Google...';
    }

    const url = new URL(googleBtn.getAttribute('href'), window.location.origin);
    url.searchParams.set('keep', keepCb?.checked ? '1' : '0');

    window.location.href = url.toString();
  });

  emailIn?.addEventListener('input', () => {
    resetViewFromEmailChange();
  });

  emailIn?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      if (canSendOtp()) sendOtp();
    }
  });

  captchaInput?.addEventListener('input', validateCaptchaInput);

  captchaInput?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();

      if (!canUnlockBelowSection()) {
        return;
      }

      if (canSendOtp()) {
        sendOtp();
      } else {
        validateCaptchaInput();
      }
    }
  });

  captchaCanvas?.addEventListener('click', () => {
    if (!canUnlockBelowSection()) return;
    resetCaptcha('A new captcha has been generated.');
    captchaInput.focus();
  });

  captchaCanvasWrap?.addEventListener('click', () => {
    if (!canUnlockBelowSection()) return;
    resetCaptcha('A new captcha has been generated.');
    captchaInput.focus();
  });

  sendBtn?.addEventListener('click', sendOtp);
  resendBtn?.addEventListener('click', sendOtp);

  otpBoxesWrap?.addEventListener('click', () => {
    if (state.otpSent && !state.verifyingOtp) {
      focusFirstEmptyOtpBox();
    }
  });

  otpInputs.forEach((input, index) => {
    input.addEventListener('input', (e) => {
      let value = (e.target.value || '').replace(/\D/g, '');

      if (!value) {
        e.target.value = '';
        renderOtpBoxesState();
        return;
      }

      value = value.slice(-1);
      e.target.value = value;
      renderOtpBoxesState();

      if (index < OTP_LENGTH - 1) {
        focusOtpBox(index + 1);
      }

      triggerOtpAutoVerify();
    });

    input.addEventListener('keydown', (e) => {
      const key = e.key;

      if (key === 'Backspace') {
        if (!input.value && index > 0) {
          otpInputs[index - 1].value = '';
          renderOtpBoxesState();
          focusOtpBox(index - 1);
          e.preventDefault();
        }
        return;
      }

      if (key === 'ArrowLeft' && index > 0) {
        e.preventDefault();
        focusOtpBox(index - 1);
        return;
      }

      if (key === 'ArrowRight' && index < OTP_LENGTH - 1) {
        e.preventDefault();
        focusOtpBox(index + 1);
        return;
      }

      if (key === 'Enter') {
        e.preventDefault();
        triggerOtpAutoVerify();
      }
    });

    input.addEventListener('paste', (e) => {
      const text = (e.clipboardData || window.clipboardData).getData('text');
      const digits = (text || '').replace(/\D/g, '').slice(0, OTP_LENGTH);

      if (!digits) return;

      e.preventDefault();

      otpInputs.forEach((box, i) => {
        box.value = digits[i] || '';
      });

      renderOtpBoxesState();

      const nextEmpty = otpInputs.findIndex(box => !box.value);
      focusOtpBox(nextEmpty === -1 ? OTP_LENGTH - 1 : nextEmpty);

      triggerOtpAutoVerify();
    });
  });

  window.addEventListener('resize', () => {
    if (state.captchaText) {
      drawCaptcha(state.captchaText);
    }
  });

  form?.addEventListener('submit', (e) => {
    e.preventDefault();
  });

  document.addEventListener('DOMContentLoaded', () => {
    tryAutoLoginFromLocal();
    resetCaptcha();
    setBelowSectionLocked(true);
    resetViewFromEmailChange();
    refreshSendButton();
    refreshResendButton();
    renderOtpBoxesState();
  });
})();
</script>
</body>
</html>