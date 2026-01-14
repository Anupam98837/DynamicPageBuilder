{{-- resources/views/createEnquiry.blade.php --}}
@section('title','Enquiry')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

@php
  use Illuminate\Support\Facades\DB;

  // ✅ read latest visibility settings (NO FK)
  $vis = DB::table('contact_us_page_visibility')->orderByDesc('id')->first();

  // ✅ defaults: show form if table has no row yet
  $show_form = (bool) ($vis->show_form ?? true);

  // ✅ Legal authority texts (exact as you asked)
  $legalText1 = 'I agree to the Terms and conditions *';
  $legalText2 = 'I agree to receive communication on newsletters-promotional content-offers an events through SMS-RCS *';
@endphp

<style>
  /* =========================
   * Enquiry (Public) – derived from Contact Us
   * Changes:
   * - home-popup-title: Contact Us -> Enquiry
   * - removed: cu-hero, cu-info-grid, cu-find
   * - added: captcha before submit
   * ========================= */

  :root{
    --contact-accent:#8f2d2f;
    --contact-accent-2:#6f2224;
    --contact-ink:#12212b;
    --contact-muted:#5b6b76;
    --contact-line:#e7eaee;
    --contact-surface:#ffffff;
  }

  .cu-wrap{ max-width: 980px; margin: 0 auto; padding: 28px 16px 44px; }

  .cu-form-wrap{
    margin-top: 0;
    background: var(--contact-surface);
    border:1px solid var(--contact-line);
    border-radius:16px;
    padding:18px;
    box-shadow: 0 14px 30px rgba(16, 24, 40, .06);
  }
  .cu-form-head{
    display:flex; align-items:flex-start; justify-content:space-between;
    gap:12px; margin-bottom:12px;
  }
  .cu-form-head h3{ margin:0; font-weight:900; color:var(--contact-ink); font-size:18px; }
  .cu-form-head p{ margin:4px 0 0; color:var(--contact-muted); font-size:13.5px; }

  .cu-form{ margin-top:10px; display:grid; grid-template-columns:1fr 1fr; gap:12px; }
  .cu-form .full{ grid-column: 1 / -1; }
  .cu-form label{ display:block; font-weight:800; color:var(--contact-ink); font-size:13px; margin:0 0 6px; }
  .cu-form input, .cu-form textarea{
    width:100%;
    border:1px solid var(--contact-line);
    border-radius:12px;
    padding:11px 12px;
    font-size:14px;
    outline:none;
    background:#fff;
  }
  .cu-form textarea{ min-height:120px; resize:vertical; }
  .cu-form input:focus, .cu-form textarea:focus{
    border-color: rgba(143,45,47,.55);
    box-shadow: 0 0 0 3px rgba(143,45,47,.15);
  }

  .cu-consent{
    grid-column: 1 / -1;
    margin-top: 6px;
    padding-top: 8px;
    border-top: 1px dashed rgba(15,23,42,.12);
    display:flex;
    flex-direction:column;
    gap:10px;
  }
  .cu-check{
    display:flex;
    gap:10px;
    align-items:flex-start;
    font-size:13.5px;
    color:#2f3d46;
    line-height:1.45;
  }
  .cu-check input{
    width:18px; height:18px;
    margin-top:2px;
    accent-color: var(--contact-accent);
    flex:0 0 auto;
  }

  /* ✅ Captcha */
  .cu-captcha{
    grid-column: 1 / -1;
    margin-top: 2px;
    padding-top: 10px;
    border-top: 1px dashed rgba(15,23,42,.12);
  }
  .cu-captcha-row{
    display:flex;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
  }
  .cu-canvas{
    width:160px;
    height:56px;
    border:1px solid var(--contact-line);
    border-radius:12px;
    overflow:hidden;
    background:#fff;
    box-shadow: 0 10px 22px rgba(16,24,40,.06);
  }
  .cu-canvas canvas{
    width:160px;
    height:56px;
    display:block;
  }
  .cu-cap-actions{
    display:flex;
    align-items:center;
    gap:10px;
  }
  .cu-cap-btn{
    border:1px solid var(--contact-line);
    background:#fff;
    border-radius:12px;
    padding:10px 12px;
    font-weight:900;
    color:var(--contact-ink);
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    gap:8px;
  }
  .cu-cap-btn:hover{
    border-color: rgba(143,45,47,.35);
    box-shadow: 0 0 0 3px rgba(143,45,47,.10);
  }
  .cu-cap-hint{
    font-size:12.5px;
    color:var(--contact-muted);
  }

  .cu-actions{
    grid-column: 1 / -1;
    display:flex; gap:10px; align-items:center; justify-content:flex-start;
    margin-top:4px;
  }
  .cu-btn{
    border:none;
    background: var(--contact-accent);
    color:#fff;
    padding:11px 18px;
    border-radius:12px;
    font-weight:900;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    gap:8px;
  }
  .cu-btn:hover{ background: var(--contact-accent-2); }
  .cu-note{ color:var(--contact-muted); font-size:13px; }

  /* ✅ Disabled submit styling */
  .cu-btn:disabled{
    opacity:.55;
    cursor:not-allowed;
    filter: grayscale(.05);
  }

  @media(max-width: 900px){
    .cu-form{ grid-template-columns:1fr; }
  }
</style>

<div class="cu-wrap">

  {{-- Form (Only) --}}
  @if($show_form)
  <div class="cu-form-wrap">
    <div class="cu-form-head">
      <div>
        {{-- ✅ CHANGE #1: home-popup-title now Contact Us -> Enquiry --}}
        <h3 class="home-popup-title">Enquiry</h3>
        <p>Fill the form and we’ll get back to you as soon as possible.</p>
      </div>
    </div>

    <form id="contactForm" class="cu-form" autocomplete="off">
      <div>
        <label for="first_name">First Name *</label>
        <input id="first_name" type="text" placeholder="Your first name" required>
      </div>

      <div>
        <label for="last_name">Last Name</label>
        <input id="last_name" type="text" placeholder="Your last name (optional)">
      </div>

      <div class="full">
        <label for="email">Email *</label>
        <input id="email" type="email" placeholder="your@email.com" required>
      </div>

      <div class="full">
        <label for="phone">Phone</label>
        <input id="phone" type="text" placeholder="Your phone number (optional)">
      </div>

      <div class="full">
        <label for="message">Message *</label>
        <textarea id="message" placeholder="Write your message..." required></textarea>
      </div>

      {{-- ✅ Consent checkboxes (must be checked to enable submit) --}}
      <div class="cu-consent">
        <label class="cu-check" for="consent_terms">
          <input id="consent_terms" type="checkbox">
          <span>{{ $legalText1 }}</span>
        </label>

        <label class="cu-check" for="consent_promotions">
          <input id="consent_promotions" type="checkbox">
          <span>{{ $legalText2 }}</span>
        </label>
      </div>

      {{-- ✅ Captcha (before submit) --}}
      <div class="cu-captcha">
        <label for="captcha_input">Captcha *</label>
        <div class="cu-captcha-row">
          <div class="cu-canvas" aria-hidden="true">
            <canvas id="captchaCanvas" width="160" height="56"></canvas>
          </div>

          <div class="cu-cap-actions">
            <button id="refreshCaptcha" class="cu-cap-btn" type="button">
              <i class="fa-solid fa-rotate-right"></i> Refresh
            </button>
            {{-- ✅ CHANGE: hint clarifies uppercase requirement --}}
            <div class="cu-cap-hint">Type the code shown in the box (CAPITAL letters only).</div>
          </div>

          <div style="flex:1 1 240px; min-width:220px;">
            {{-- ✅ CHANGE: no auto-uppercasing; only enable submit when user typed ALL CAPS --}}
            <input
              id="captcha_input"
              type="text"
              inputmode="text"
              placeholder="Enter captcha (CAPITAL)"
              autocomplete="off"
              autocapitalize="characters"
              spellcheck="false"
              required
            >
          </div>
        </div>
      </div>

      <div class="cu-actions">
        <button id="submitBtn" class="cu-btn" type="submit" disabled>
          <i class="fa-solid fa-paper-plane"></i> Send Message
        </button>
        <span class="cu-note">We never share your details.</span>
      </div>
    </form>
  </div>
  @endif

</div>

{{-- Submit form --}}
@if($show_form)
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  (function(){
    const form = document.getElementById('contactForm');
    const btn  = document.getElementById('submitBtn');

    const firstNameEl = document.getElementById('first_name');
    const lastNameEl  = document.getElementById('last_name');
    const emailEl     = document.getElementById('email');
    const phoneEl     = document.getElementById('phone');
    const msgEl       = document.getElementById('message');

    const termsEl     = document.getElementById('consent_terms');
    const promoEl     = document.getElementById('consent_promotions');

    // ✅ Captcha
    const capCanvas   = document.getElementById('captchaCanvas');
    const capInputEl  = document.getElementById('captcha_input');
    const capRefresh  = document.getElementById('refreshCaptcha');
    const capCtx      = capCanvas.getContext('2d', { willReadFrequently: true });

    let CAPTCHA_CODE = '';

    // ✅ texts must be posted as discussed
    const LEGAL_TEXT_1 = @json($legalText1);
    const LEGAL_TEXT_2 = @json($legalText2);

    function rand(min, max){ return Math.floor(Math.random() * (max - min + 1)) + min; }

    function genCode(len=6){
      const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // avoid confusing chars like 0/O/1/I
      let out = '';
      for(let i=0;i<len;i++) out += chars[rand(0, chars.length-1)];
      return out;
    }

    function drawCaptcha(code){
      // background
      capCtx.clearRect(0,0,capCanvas.width,capCanvas.height);
      capCtx.fillStyle = '#ffffff';
      capCtx.fillRect(0,0,capCanvas.width,capCanvas.height);

      // light noise lines
      for(let i=0;i<6;i++){
        capCtx.beginPath();
        capCtx.moveTo(rand(0,160), rand(0,56));
        capCtx.lineTo(rand(0,160), rand(0,56));
        capCtx.strokeStyle = `rgba(143,45,47,${Math.random()*0.25 + 0.10})`;
        capCtx.lineWidth = rand(1,2);
        capCtx.stroke();
      }

      // dots
      for(let i=0;i<35;i++){
        capCtx.beginPath();
        capCtx.arc(rand(0,160), rand(0,56), rand(1,2), 0, Math.PI*2);
        capCtx.fillStyle = `rgba(16,24,40,${Math.random()*0.12 + 0.05})`;
        capCtx.fill();
      }

      // text
      capCtx.font = '900 28px system-ui, -apple-system, Segoe UI, Roboto, Arial';
      capCtx.textBaseline = 'middle';

      const startX = 18;
      const gap = 22;

      for(let i=0;i<code.length;i++){
        const ch = code[i];
        const x = startX + (i * gap);
        const y = 28;

        capCtx.save();
        capCtx.translate(x, y);
        capCtx.rotate((Math.random() - 0.5) * 0.45);

        capCtx.fillStyle = `rgba(18,33,43,${Math.random()*0.20 + 0.78})`;
        capCtx.fillText(ch, 0, 0);

        capCtx.restore();
      }

      // border stroke
      capCtx.strokeStyle = 'rgba(231,234,238,1)';
      capCtx.lineWidth = 2;
      capCtx.strokeRect(1,1,capCanvas.width-2,capCanvas.height-2);
    }

    function refreshCaptcha(){
      CAPTCHA_CODE = genCode(6);
      drawCaptcha(CAPTCHA_CODE);
      capInputEl.value = '';
    }

    // ✅ CHANGE (YOUR REQUEST):
    // - lowercase should NOT work
    // - enable submit ONLY if user typed ALL CAPS exactly
    function captchaOk(){
      const typedRaw = (capInputEl.value || '').trim();  // keep user's real case
      if(!typedRaw) return false;

      // If user used any small letters -> do NOT allow
      if(typedRaw !== typedRaw.toUpperCase()) return false;

      // Must match captcha exactly
      return typedRaw === CAPTCHA_CODE;
    }

    function canSubmit(){
      const okRequired = firstNameEl.value.trim() && emailEl.value.trim() && msgEl.value.trim();
      const okConsent  = termsEl.checked && promoEl.checked;
      const okCaptcha  = captchaOk();
      return !!(okRequired && okConsent && okCaptcha);
    }

    function syncBtn(){
      btn.disabled = !canSubmit();
    }

    // init captcha
    refreshCaptcha();

    capRefresh.addEventListener('click', function(){
      refreshCaptcha();
      syncBtn();
    });

    // enable/disable submit live
    [firstNameEl, emailEl, msgEl, termsEl, promoEl, lastNameEl, phoneEl, capInputEl].forEach(el => {
      el.addEventListener('input', syncBtn);
      el.addEventListener('change', syncBtn);
    });
    syncBtn();

    form.addEventListener('submit', async function(e){
      e.preventDefault();

      const first_name = firstNameEl.value.trim();
      const last_name  = lastNameEl.value.trim();
      const email      = emailEl.value.trim();
      const phone      = phoneEl.value.trim();
      const message    = msgEl.value.trim();

      if(!first_name || !email || !message){
        Swal.fire('Error','Please fill all required fields','error');
        return;
      }

      // ✅ Hard requirement: both must be checked
      if(!(termsEl.checked && promoEl.checked)){
        Swal.fire('Error','Please accept the required agreements to continue.','error');
        syncBtn();
        return;
      }

      // ✅ Captcha check (case-sensitive + ALL CAPS required)
      if(!captchaOk()){
        Swal.fire('Error','Captcha does not match. Use CAPITAL letters only.','error');
        refreshCaptcha();
        syncBtn();
        return;
      }

      // ✅ payload updated (first_name/last_name + legal_authority_json)
      const legal_authority_json = [
        { key: 'terms',      text: LEGAL_TEXT_1, accepted: true },
        { key: 'promotions', text: LEGAL_TEXT_2, accepted: true },
      ];

      const payload = {
        first_name,
        last_name: (last_name !== '' ? last_name : null),
        email,
        phone: (phone !== '' ? phone : null),
        message,
        legal_authority_json
      };

      try{
        btn.disabled = true;
        btn.style.opacity = '.85';

        const res = await fetch('/api/contact-us', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify(payload)
        });

        const data = await res.json().catch(() => ({}));

        if(res.ok){
          Swal.fire('Success','Message sent successfully','success');
          form.reset();

          // ✅ Refresh captcha + force-disable after reset
          refreshCaptcha();
          btn.disabled = true;

          syncBtn();
        }else{
          let msg = data.message || 'Validation failed';

          // Laravel 422 errors pretty message
          if (data.errors && typeof data.errors === 'object') {
            const k = Object.keys(data.errors)[0];
            if (k && Array.isArray(data.errors[k]) && data.errors[k][0]) {
              msg = data.errors[k][0];
            }
          }

          Swal.fire('Error', msg, 'error');
          console.error(data);
          refreshCaptcha();
          syncBtn();
        }
      }catch(err){
        console.error(err);
        Swal.fire('Error','Something went wrong. Please try again.','error');
        refreshCaptcha();
        syncBtn();
      }finally{
        btn.style.opacity = '1';
        syncBtn();
      }
    });
  })();
</script>
@endif
