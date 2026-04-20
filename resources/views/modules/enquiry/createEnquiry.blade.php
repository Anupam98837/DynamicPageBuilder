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
  :root{
    --contact-accent:#8f2d2f;
    --contact-accent-2:#6f2224;
    --contact-ink:#12212b;
    --contact-muted:#5b6b76;
    --contact-line:#e7eaee;
    --contact-surface:#ffffff;
  }

  .cu-wrap{ max-width: 980px; margin: 0 auto; }

  .cu-form-wrap{
    margin-top: 0;
    background: var(--contact-surface);
    border:1px solid var(--contact-line);
    border-radius:14px;
    padding:12px 14px;
    box-shadow: 0 14px 30px rgba(16, 24, 40, .06);
  }

  .cu-form-head{
    display:flex; align-items:flex-start; justify-content:space-between;
    gap:8px; margin-bottom:6px;
  }
  .cu-form-head h3{ margin:0; font-weight:900; color:var(--contact-ink); font-size:17px; }
  .cu-form-head p{ margin:2px 0 0; color:var(--contact-muted); font-size:12.5px; }

  /* ✅ Tighter grid gaps - updated to 3 columns */
  .cu-form{ margin-top:6px; display:grid; grid-template-columns:repeat(3, 1fr); gap:7px 12px; }
  .cu-form .full{ grid-column: 1 / -1; }
  .cu-form > div { min-width: 0; }

  .cu-form label{ display:block; font-weight:800; color:var(--contact-ink); font-size:12px; margin:0 0 3px; }

  .cu-form input, .cu-form textarea, .cu-form select{
    width:100%;
    border:1px solid var(--contact-line);
    border-radius:10px;
    padding:7px 10px;
    font-size:13px;
    outline:none;
    background:#fff;
    height:36px;
  }
  /* ✅ Shorter textarea */
  .cu-form textarea{
    min-height:58px;
    height:58px;
    resize:vertical;
    padding:8px 10px;
  }
  .cu-form input:focus, .cu-form textarea:focus, .cu-form select:focus{
    border-color: rgba(143,45,47,.55);
    box-shadow: 0 0 0 3px rgba(143,45,47,.12);
  }

  /* ✅ Compact admission toggle */
  .cu-toggle{
    grid-column: 1 / -1;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    padding:7px 12px;
    border:1px dashed rgba(15,23,42,.14);
    border-radius:12px;
    background: rgba(143,45,47,.03);
  }
  .cu-toggle .left{
    display:flex;
    flex-direction:column;
    gap:1px;
    min-width:0;
  }
  .cu-toggle .title{
    font-weight:900;
    color:var(--contact-ink);
    font-size:12.5px;
    line-height:1.2;
  }
  .cu-toggle .sub{
    color:var(--contact-muted);
    font-size:11.5px;
    line-height:1.2;
  }

  /* Switch */
  .cu-switch{
    position:relative;
    width:42px;
    height:24px;
    flex:0 0 auto;
  }
  .cu-switch input{ display:none; }
  .cu-switch span{
    position:absolute; inset:0;
    background:#e5e7eb;
    border-radius:999px;
    transition:.18s ease;
    cursor:pointer;
  }
  .cu-switch span::after{
    content:"";
    position:absolute;
    top:3px; left:3px;
    width:18px; height:18px;
    border-radius:999px;
    background:#fff;
    box-shadow:0 10px 20px rgba(16,24,40,.12);
    transition:.18s ease;
  }
  .cu-switch input:checked + span{
    background: rgba(143,45,47,.95);
    border-color: rgba(143,45,47,.35);
  }
  .cu-switch input:checked + span::after{
    transform: translateX(18px);
  }

  /* ✅ Better dropdown styling */
  .cu-form select{
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%235b6b76' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 14px;
    padding-right: 34px;
    cursor: pointer;
    color: var(--contact-ink);
    font-weight: 500;
    transition: border-color .15s, box-shadow .15s;
  }
  .cu-form select:hover{
    border-color: rgba(143,45,47,.35);
  }
  .cu-form select option{
    padding: 8px 10px;
    font-size: 13px;
  }
  .cu-form select option:checked{
    background: rgba(143,45,47,.08);
    font-weight: 600;
  }
  /* placeholder-like first option */
  .cu-form select.placeholder-shown{
    color: var(--contact-muted);
  }

  /* ✅ Dept helper text smaller */
  .cu-dept-hint{
    margin-top:3px;
    font-size:11px;
    color:var(--contact-muted);
    line-height:1.2;
  }

  /* ✅ Consent - single row, compact */
  .cu-consent{
    margin-top: 0;
    padding-top: 6px;
    display:grid;
    gap:4px 14px;
  }
  .legal-text{
    font-weight: 500;
  }
  .cu-check{
    display:flex;
    gap:7px;
    align-items:flex-start;
    font-size:11.5px;
    color:#2f3d46;
    line-height:1.3;
    margin:0;
  }
  .cu-check input{
    width:15px; height:15px;
    margin-top:1px;
    accent-color: var(--contact-accent);
    flex:0 0 auto;
  }

  /* ✅ Captcha - compact single row */
  .cu-captcha{
    margin-top: 0;
    padding-top: 6px;
    border-top: 1px dashed rgba(15,23,42,.12);
  }
  .cu-captcha > label{
    margin-bottom: 4px;
  }
  .cu-captcha-row{
    display:flex;
    align-items:center;
    gap:8px;
  }
  .cu-canvas{
    width:130px;
    height:42px;
    border:1px solid var(--contact-line);
    border-radius:10px;
    overflow:hidden;
    background:#fff;
    box-shadow: 0 6px 14px rgba(16,24,40,.05);
    flex:0 0 auto;
  }
  .cu-canvas canvas{
    width:130px;
    height:42px;
    display:block;
  }
  .cu-cap-actions{
    display:flex;
    align-items:center;
    gap:8px;
    flex:0 0 auto;
  }
  .cu-cap-btn{
    border:1px solid var(--contact-line);
    background:#fff;
    border-radius:10px;
    padding:6px 10px;
    font-weight:900;
    color:var(--contact-ink);
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    gap:6px;
    height:36px;
    font-size:12.5px;
  }
  .cu-cap-btn:hover{
    border-color: rgba(143,45,47,.35);
    box-shadow: 0 0 0 3px rgba(143,45,47,.10);
  }
  .cu-cap-hint{
    font-size:11px;
    color:var(--contact-muted);
    line-height:1.2;
    flex:0 0 auto;
    white-space:nowrap;
  }
  .cu-cap-input-wrap{
    flex:1 1 180px;
    min-width:150px;
  }
  .cu-cap-input-wrap input{
    height:36px;
  }

  .cu-actions{
    display:flex; gap:10px; align-items:center; justify-content:flex-start;
    margin-top:2px;
  }
  .cu-btn{
    border:none;
    background: var(--contact-accent);
    color:#fff;
    padding:8px 16px;
    border-radius:10px;
    font-weight:900;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    gap:8px;
    height:38px;
    font-size:13px;
  }
  .cu-btn:hover{ background: var(--contact-accent-2); }
  .cu-note{ color:var(--contact-muted); font-size:12px; }

  .cu-btn:disabled{
    opacity:.55;
    cursor:not-allowed;
    filter: grayscale(.05);
  }

  /* Toast */
  .cu-toast-wrap{
    position:fixed;
    top:16px;
    right:16px;
    z-index:200000;
    display:flex;
    flex-direction:column;
    gap:10px;
    width:min(380px, calc(100vw - 24px));
    pointer-events:none;
  }
  .cu-toast{
    pointer-events:auto;
    display:flex;
    align-items:flex-start;
    gap:10px;
    background:#fff;
    border:1px solid #e8edf2;
    border-radius:14px;
    box-shadow:0 18px 40px rgba(16,24,40,.16);
    padding:10px 12px;
    transform:translateY(-8px);
    opacity:0;
    transition:all .2s ease;
    overflow:hidden;
    position:relative;
  }
  .cu-toast.show{ transform:translateY(0); opacity:1; }
  .cu-toast::before{
    content:"";
    position:absolute;
    left:0; top:0; bottom:0;
    width:4px;
    background:#c94b50;
  }
  .cu-toast.success::before{ background:#16a34a; }
  .cu-toast.error::before{ background:#dc2626; }
  .cu-toast.info::before{ background:#2563eb; }

  .cu-toast-icon{
    width:28px; height:28px; border-radius:999px;
    display:inline-flex; align-items:center; justify-content:center;
    flex:0 0 28px; margin-top:2px;
    background:#f3f4f6; color:#111827; font-size:13px;
  }
  .cu-toast.success .cu-toast-icon{ background:#ecfdf3; color:#15803d; }
  .cu-toast.error .cu-toast-icon{ background:#fef2f2; color:#b91c1c; }
  .cu-toast.info .cu-toast-icon{ background:#eff6ff; color:#1d4ed8; }

  .cu-toast-body{ min-width:0; flex:1 1 auto; }
  .cu-toast-title{
    font-weight:900; color:#111827; font-size:13.5px; line-height:1.2; margin:0 0 2px;
  }
  .cu-toast-text{
    color:#475569; font-size:13px; line-height:1.35; margin:0;
    word-break:break-word;
  }
  .cu-toast-close{
    border:none; background:transparent; color:#64748b;
    font-size:16px; line-height:1; cursor:pointer; padding:2px;
    border-radius:6px; flex:0 0 auto;
  }
  .cu-toast-close:hover{ background:#f1f5f9; color:#0f172a; }

  .courses-grid{
    display:grid; grid-template-columns: repeat(4, 1fr); gap: 8px;margin-bottom: 5px;
  }
  .courses-grid-2{
    display:grid; grid-template-columns: repeat(2, 1fr); gap: 8px;
  }
  @media(max-width: 1024px){
    .courses-grid{ grid-template-columns: repeat(3, 1fr); }
  }
  @media(max-width: 768px){
    .courses-grid{ grid-template-columns: repeat(2, 1fr); }
  }
  @media(max-width: 480px){
    .courses-grid{ grid-template-columns: 1fr; }
    .courses-grid-2{ grid-template-columns: 1fr; }
  }

  .cu-form-bottom {
    grid-column: 1 / -1;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
  }

  .cu-actions-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    align-items: flex-end;
    margin-top: 5px;
  }

.course-name-gradient{
  display:inline-block;
  font-weight:500;
  background: linear-gradient(
    to bottom,
    #111111 0%,
    #2b1516 25%,
    #5d1f21 55%,
    var(--contact-accent) 78%,
    var(--contact-accent) 100%
  );
  -webkit-background-clip:text;
  background-clip:text;
  -webkit-text-fill-color:transparent;
  color:transparent;
}

  @media (max-width: 768px) {
    .courses-grid, .courses-grid-2 { grid-template-columns: 1fr !important; }
  }

  @media(max-width: 900px){
    .cu-form{ display: block !important; }
    .cu-form > div{ margin-bottom: 12px; }
    .cu-form-bottom { display: block !important; }
    .cu-consent, .cu-captcha { grid-column: auto !important; }
    .cu-actions-row { display: block !important; }
  }
</style>

<div class="cu-wrap">

  {{-- Form (Only) --}}
  @if($show_form)
  <div class="cu-form-wrap">
    <div class="cu-form-head">
      <div>
        <h3 class="home-popup-title">Enquiry</h3>
      </div>
    </div>

    <form id="contactForm" class="cu-form" autocomplete="off">

      <div>
        <label for="name">Name *</label>
        <input id="name" type="text" placeholder="Your full name" required>
      </div>

      {{-- ✅ UPDATED: Email nullable --}}
      <div>
        <label for="email">Email</label>
        <input id="email" type="email" placeholder="your@email.com (optional)">
      </div>

      {{-- ✅ UPDATED: Phone required --}}
      <div>
        <label for="phone">Phone *</label>
        <input id="phone" type="text" placeholder="Your phone number" required>
      </div>

      {{-- ✅ Admission toggle + Department INLINE (same row) --}}
      <div class="cu-toggle">
        <div class="left">
          <div class="title">Enquiring for Admission?</div>
          <div class="sub">Turn ON if you want admission-related help.</div>
        </div>

        <label class="cu-switch" title="Admission enquiry toggle">
          <input id="is_admission_enquiry" type="checkbox">
          <span aria-hidden="true"></span>
        </label>
      </div>

      {{-- ✅ Course Checkboxes (only visible if toggle ON) --}}
      <div id="deptWrap" class="full" style="display:none;">
        <label style="margin-bottom:6px; display:block;">Interested Course(s) *</label>
        <div id="course_checkboxes" style="border: 1px solid var(--contact-line); padding: 12px; border-radius: 10px; background: #fff;">
          <!-- Checkboxes injected here -->
        </div>
        <div class="cu-dept-hint">
          Select one or more courses you are interested in.
        </div>
      </div>

      {{-- Continuous Divider Line separates section from Courses above --}}
      <div style="grid-column: 1 / -1; border-top: 1px dashed rgba(15,23,42,.12); margin-top: 6px; padding-top: 6px;"></div>

      {{-- Row: Message (1 col) + Consent (2 cols) --}}
      <div style="grid-column: span 1;">
        <label for="message" style="margin-bottom: 3px; display:block;">Message</label>
        <textarea id="message" placeholder="Write your message..."></textarea>
      </div>

      <div style="grid-column: span 2;">
        <label style="margin-bottom: 3px; display:block;">Agreements *</label>
        <div class="cu-consent" style="border:none; padding-top:0; margin-top:0;">
          <label class="cu-check" for="consent_terms">
            <input id="consent_terms" type="checkbox">
            <span class="legal-text">{{ $legalText1 }}</span>
          </label>
          <label class="cu-check" for="consent_promotions">
            <input id="consent_promotions" type="checkbox">
            <span class="legal-text">I agree to receive communication on newsletters, promotional content, offers & events via SMS/RCS *</span>
          </label>
        </div>
      </div>

      <div class="full cu-actions-row">
        <div class="cu-captcha" style="border-top:none; padding-top:0; margin-top:0;">
          <label for="captcha_input">Captcha *</label>
          <div class="cu-captcha-row">
            <div class="cu-canvas" aria-hidden="true">
              <canvas id="captchaCanvas" width="130" height="42"></canvas>
            </div>
  
            <div class="cu-cap-actions">
              <button id="refreshCaptcha" class="cu-cap-btn" type="button">
                <i class="fa-solid fa-rotate-right"></i> Refresh
              </button>
            </div>
  
            <div class="cu-cap-input-wrap">
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
  
        <div class="cu-actions" style="margin-top:0;">
          <button id="submitBtn" class="cu-btn" type="submit" disabled style="min-width: 140px;">
            <i class="fa-solid fa-paper-plane"></i> Send Message
          </button>
          <span class="cu-note" style="font-size: 11.5px; line-height: 1.25;">We'll get back to you as soon as possible.</span>
        </div>
      </div>
    </form>
  </div>
  @endif

</div>

{{-- Submit form --}}
@if($show_form)
<script>
  (function(){
    const form = document.getElementById('contactForm');
    const btn  = document.getElementById('submitBtn');
    if(!form || !btn) return;

    const nameEl = document.getElementById('name');
    const emailEl     = document.getElementById('email');
    const phoneEl     = document.getElementById('phone');
    const msgEl       = document.getElementById('message');

    // ✅ NEW: admission + courses
    const admissionEl = document.getElementById('is_admission_enquiry');
    const deptWrapEl  = document.getElementById('deptWrap');
    const courseCheckboxesEl = document.getElementById('course_checkboxes');

    const termsEl     = document.getElementById('consent_terms');
    const promoEl     = document.getElementById('consent_promotions');

    // ✅ Captcha
    const capCanvas   = document.getElementById('captchaCanvas');
    const capInputEl  = document.getElementById('captcha_input');
    const capRefresh  = document.getElementById('refreshCaptcha');
    const capCtx      = capCanvas.getContext('2d', { willReadFrequently: true });

    let CAPTCHA_CODE = '';

    const LEGAL_TEXT_1 = @json($legalText1);
    const LEGAL_TEXT_2 = @json($legalText2);

    // Departments state
    let deptLoaded = false;
    let deptLoading = false;
    let deptLoadFailed = false;

    function ensureToastWrap(){
      let wrap = document.getElementById('cuToastWrap');
      if(!wrap){
        wrap = document.createElement('div');
        wrap.id = 'cuToastWrap';
        wrap.className = 'cu-toast-wrap';
        document.body.appendChild(wrap);
      }
      return wrap;
    }
    function toastIcon(type){
      if(type === 'success') return 'fa-solid fa-circle-check';
      if(type === 'error') return 'fa-solid fa-circle-exclamation';
      return 'fa-solid fa-circle-info';
    }
    function showToast(type, title, message){
      const wrap = ensureToastWrap();
      const el = document.createElement('div');
      el.className = `cu-toast ${type || 'info'}`;
      el.innerHTML = `
        <div class="cu-toast-icon"><i class="${toastIcon(type)}"></i></div>
        <div class="cu-toast-body">
          <p class="cu-toast-title">${title || ''}</p>
          <p class="cu-toast-text">${message || ''}</p>
        </div>
        <button type="button" class="cu-toast-close" aria-label="Close">&times;</button>
      `;
      wrap.appendChild(el);

      requestAnimationFrame(() => el.classList.add('show'));

      let t1 = null, t2 = null;
      const removeToast = () => {
        clearTimeout(t1); clearTimeout(t2);
        el.classList.remove('show');
        t2 = setTimeout(() => { try{ el.remove(); }catch(_){} }, 220);
      };

      el.querySelector('.cu-toast-close')?.addEventListener('click', removeToast);
      t1 = setTimeout(removeToast, 3500);
      return removeToast;
    }

    // Modal close helpers
    function hardHideModal(modal){
      if(!modal) return;
      modal.classList.remove('show');
      modal.style.display = 'none';
      modal.setAttribute('aria-hidden', 'true');
      modal.removeAttribute('aria-modal');
      try { modal.dispatchEvent(new Event('hidden.bs.modal', { bubbles:true })); } catch(_) {}
    }
    function cleanupModalBackdrop(){
      document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('overflow');
      document.body.style.removeProperty('padding-right');
    }
    function closeAnyParentModals(){
      const modals = new Set();
      let p = form.parentElement;
      while(p){
        if(p.classList && p.classList.contains('modal')) modals.add(p);
        p = p.parentElement;
      }
      document.querySelectorAll('.modal.show').forEach(m => modals.add(m));

      modals.forEach(modal => {
        try{
          if(window.bootstrap && window.bootstrap.Modal){
            const inst = window.bootstrap.Modal.getInstance(modal) || new window.bootstrap.Modal(modal);
            inst.hide();
          }else{
            hardHideModal(modal);
          }
        }catch(_){
          hardHideModal(modal);
        }
      });

      setTimeout(cleanupModalBackdrop, 350);
      setTimeout(cleanupModalBackdrop, 700);
    }

    // Captcha helpers
    function rand(min, max){ return Math.floor(Math.random() * (max - min + 1)) + min; }
    function genCode(len=6){
      const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
      let out = '';
      for(let i=0;i<len;i++) out += chars[rand(0, chars.length-1)];
      return out;
    }
    function drawCaptcha(code){
      capCtx.clearRect(0,0,capCanvas.width,capCanvas.height);
      capCtx.fillStyle = '#ffffff';
      capCtx.fillRect(0,0,capCanvas.width,capCanvas.height);

      for(let i=0;i<5;i++){
        capCtx.beginPath();
        capCtx.moveTo(rand(0,capCanvas.width), rand(0,capCanvas.height));
        capCtx.lineTo(rand(0,capCanvas.width), rand(0,capCanvas.height));
        capCtx.strokeStyle = `rgba(143,45,47,${Math.random()*0.25 + 0.10})`;
        capCtx.lineWidth = rand(1,2);
        capCtx.stroke();
      }
      for(let i=0;i<28;i++){
        capCtx.beginPath();
        capCtx.arc(rand(0,capCanvas.width), rand(0,capCanvas.height), rand(1,2), 0, Math.PI*2);
        capCtx.fillStyle = `rgba(16,24,40,${Math.random()*0.12 + 0.05})`;
        capCtx.fill();
      }

      capCtx.font = '900 22px system-ui, -apple-system, Segoe UI, Roboto, Arial';
      capCtx.textBaseline = 'middle';

      const startX = 10;
      const gap = 18;

      for(let i=0;i<code.length;i++){
        const ch = code[i];
        const x = startX + (i * gap);
        const y = Math.floor(capCanvas.height / 2);

        capCtx.save();
        capCtx.translate(x, y);
        capCtx.rotate((Math.random() - 0.5) * 0.45);
        capCtx.fillStyle = `rgba(18,33,43,${Math.random()*0.20 + 0.78})`;
        capCtx.fillText(ch, 0, 0);
        capCtx.restore();
      }

      capCtx.strokeStyle = 'rgba(231,234,238,1)';
      capCtx.lineWidth = 2;
      capCtx.strokeRect(1,1,capCanvas.width-2,capCanvas.height-2);
    }
    function refreshCaptcha(){
      CAPTCHA_CODE = genCode(6);
      drawCaptcha(CAPTCHA_CODE);
      capInputEl.value = '';
    }
    function captchaOk(){
      const typedRaw = (capInputEl.value || '').trim();
      if(!typedRaw) return false;
      if(typedRaw !== typedRaw.toUpperCase()) return false; // no lowercase allowed
      return typedRaw === CAPTCHA_CODE;
    }

    // ✅ Courses loader (api/public/ordered-courses)
    async function loadCourses(){
      if(deptLoaded || deptLoading) return;
      deptLoading = true;
      deptLoadFailed = false;

      try{
        courseCheckboxesEl.innerHTML = `<div>Loading courses...</div>`;

        const res = await fetch('/api/public/ordered-courses', {
          method: 'GET',
          headers: { 'Accept': 'application/json' }
        });
        const data = await res.json().catch(() => ({}));

        let list = Array.isArray(data.data) ? data.data : [];

        if(!res.ok){
          throw new Error(data.message || 'Failed to load courses');
        }

        if (list.length === 0) {
          courseCheckboxesEl.innerHTML = `<div>No courses found</div>`;
          return;
        }

        function escapeHtml(s){
          const map={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'};
          return (s==null?'':String(s)).replace(/[&<>"'`]/g,ch=>map[ch]);
        }

        // Categorization logic
        const groups = {
          'B.Tech in': [],
          'AICTE Bachelor Degree': [],
          'M.Tech in': [],
          'MCA & MBA': [],
          'Other Courses': []
        };

        list.forEach(c => {
          const t = (c.title || '').toUpperCase();
          const approvals = (c.approvals || '').toUpperCase();
          const level = (c.program_level || '').toLowerCase();

          if (t.includes('M.TECH') || t.includes('M. TECH')) {
            groups['M.Tech in'].push(c);
          } else if (t.includes('MCA') || t.includes('MBA')) {
            groups['MCA & MBA'].push(c);
          } else if (t.includes('BCA') || t.includes('BBA')) {
            groups['AICTE Bachelor Degree'].push(c);
          } else if (level === 'ug') {
            groups['B.Tech in'].push(c);
          } else {
            groups['Other Courses'].push(c);
          }
        });

        let html = '';
        for (const [title, items] of Object.entries(groups)) {
          if (items.length > 0) {
            html += `<div style="margin-bottom: 6px; font-size:13px; font-weight:600; color:var(--contact-accent); border-bottom: 1px dashed var(--contact-line); padding-bottom:3px;">${title}</div>`;
            const gridClass = (title === 'MCA & MBA') ? 'courses-grid-2' : 'courses-grid';
            html += `<div class="${gridClass}">`;
            html += items.map(c => `
  <label style="display:flex; align-items:center; gap:6px; font-size:12.5px; cursor:pointer;">
    <input type="checkbox" name="course_ids" value="${c.id}" class="course-check-input" style="accent-color:var(--contact-accent); width:15px; height:15px; cursor:pointer;">
    <span class="course-name-gradient">${escapeHtml(c.custom_name || c.title)}</span>
  </label>
`).join('');
            html += `</div>`;
          }
        }

        courseCheckboxesEl.innerHTML = html;

        // Attach event listener
        courseCheckboxesEl.querySelectorAll('.course-check-input').forEach(chk => {
          chk.addEventListener('change', syncBtn);
        });

        deptLoaded = true;
      }catch(err){
        console.error(err);
        deptLoadFailed = true;
        courseCheckboxesEl.innerHTML = `<div style="color:red; font-size:13px;">Unable to load courses</div>`;
        showToast('error', 'Error', 'Could not load courses. Please try again.');
      }finally{
        deptLoading = false;
      }
    }

    // ✅ Admission toggle behaviour
    function setDeptVisibility(){
      const on = !!admissionEl.checked;

      if(on){
        deptWrapEl.style.display = '';
        loadCourses();
      }else{
        deptWrapEl.style.display = 'none';
        // uncheck all
        courseCheckboxesEl.querySelectorAll('.course-check-input').forEach(chk => chk.checked = false);
      }
    }


    // Submit enable logic
    function canSubmit(){
      const nameOk = !!nameEl.value.trim();
      const phoneOk = !!phoneEl.value.trim();     // ✅ phone required
      const consentOk = termsEl.checked && promoEl.checked;
      const captchaIsOk = captchaOk();

      // ✅ if admission ON, at least one course checkbox must be checked
      const admissionOn = !!admissionEl.checked;
      const checkedCount = courseCheckboxesEl ? courseCheckboxesEl.querySelectorAll('.course-check-input:checked').length : 0;
      const deptOk = !admissionOn ? true : (checkedCount > 0);

      return !!(nameOk && phoneOk && consentOk && captchaIsOk && deptOk);
    }

    function syncBtn(){
      btn.disabled = !canSubmit();
    }

    // Init captcha
    refreshCaptcha();

    capRefresh.addEventListener('click', function(){
      refreshCaptcha();
      syncBtn();
    });

    // Init admission toggle
    setDeptVisibility();
    admissionEl.addEventListener('change', function(){
      setDeptVisibility();
      syncBtn();
    });

    // Live sync
    [nameEl, emailEl, phoneEl, msgEl, termsEl, promoEl, capInputEl].forEach(el => {
      el.addEventListener('input', syncBtn);
      el.addEventListener('change', syncBtn);
    });
    syncBtn();

    form.addEventListener('submit', async function(e){
      e.preventDefault();

      const name       = nameEl.value.trim();
      const email      = emailEl.value.trim();       // ✅ nullable
      const phone      = phoneEl.value.trim();       // ✅ required
      const message    = msgEl.value.trim();

      const admissionOn = !!admissionEl.checked;
      const checkedBoxes = Array.from(courseCheckboxesEl.querySelectorAll('.course-check-input:checked'));
      const courseIds = checkedBoxes.map(b => parseInt(b.value, 10));

      if(!name || !phone){
        showToast('error','Error','Please fill all required fields (Name, Phone).');
        syncBtn();
        return;
      }

      if(admissionOn){
        if(courseIds.length === 0){
          showToast('error','Error','Please select at least one course.');
          syncBtn();
          return;
        }
        if(deptLoadFailed){
          showToast('error','Error','Courses are not loaded. Please refresh and try again.');
          syncBtn();
          return;
        }
      }

      if(!(termsEl.checked && promoEl.checked)){
        showToast('error','Error','Please accept the required agreements to continue.');
        syncBtn();
        return;
      }

      if(!captchaOk()){
        showToast('error','Error','Captcha does not match. Use CAPITAL letters only.');
        refreshCaptcha();
        syncBtn();
        return;
      }

      const legal_authority_json = [
        { key: 'terms',      text: LEGAL_TEXT_1, accepted: true },
        { key: 'promotions', text: LEGAL_TEXT_2, accepted: true },
      ];

      const payload = {
        name,
        email: (email !== '' ? email : null),
        phone,
        message: (message !== '' ? message : null),
        is_admission_enquiry: admissionOn ? true : null,
        course_ids: admissionOn ? courseIds : null,
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
          closeAnyParentModals();

          showToast('success','Success','Message sent successfully');
          form.reset();

          // reset admission UI
          deptWrapEl.style.display = 'none';
          courseCheckboxesEl.querySelectorAll('.course-check-input').forEach(chk => chk.checked = false);

          refreshCaptcha();
          btn.disabled = true;
          syncBtn();
        }else{
          let msg = data.message || 'Validation failed';
          if (data.errors && typeof data.errors === 'object') {
            const k = Object.keys(data.errors)[0];
            if (k && Array.isArray(data.errors[k]) && data.errors[k][0]) {
              msg = data.errors[k][0];
            }
          }
          showToast('error','Error', msg);
          console.error(data);
          refreshCaptcha();
          syncBtn();
        }
      }catch(err){
        console.error(err);
        showToast('error','Error','Something went wrong. Please try again.');
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