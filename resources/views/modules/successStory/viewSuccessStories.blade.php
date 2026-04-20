{{-- resources/views/modules/successStory/viewSuccessStories.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta name="csrf-token" content="{{ csrf_token() }}"/>
  <title>Success Story</title>

  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    html, body { height: 100%; margin: 0; }

    body{
      background:
        radial-gradient(1200px 400px at 10% -10%, rgba(158,54,58,.06), transparent 60%),
        radial-gradient(900px 320px at 100% 0%, rgba(201,75,80,.05), transparent 55%),
        var(--bg-body, #f8fafc);
      color: var(--ink, #0f172a);
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      line-height: 1.6;
    }

    .ss-container{
      max-width: 1320px;
      margin: 0 auto;
      padding: clamp(18px, 3vw, 36px) clamp(14px, 2.4vw, 24px) clamp(32px, 5vw, 54px);
    }

    /* ============ Shared Cards ============ */
    .ss-card{
      background: var(--surface, #ffffff);
      border: 1px solid var(--line-strong, rgba(15,23,42,.12));
      border-radius: 18px;
      box-shadow: var(--shadow-2, 0 10px 26px rgba(2,6,23,.08));
    }

    .ss-card-soft{
      background: linear-gradient(180deg, rgba(158,54,58,.03), rgba(255,255,255,.8));
      border: 1px solid rgba(158,54,58,.18);
      border-radius: 16px;
    }

    /* ============ Hero ============ */
    .ss-hero{
      display: grid;
      grid-template-columns: minmax(0, 1.35fr) minmax(310px, .65fr);
      gap: 18px;
      align-items: stretch;
      margin-bottom: 18px;
    }

    .ss-hero-main{
      padding: clamp(18px, 2.5vw, 28px);
      display: grid;
      align-content: start;
      gap: 14px;
      min-width: 0;
    }

    .ss-head-top{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 10px;
      flex-wrap: wrap;
    }

    .ss-kicker{
      display:inline-flex;
      align-items:center;
      gap: 8px;
      padding: 6px 12px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: var(--primary-color, #9E363A);
      background: rgba(158,54,58,.08);
      border: 1px solid rgba(158,54,58,.18);
      width: fit-content;
    }

    .ss-title{
      margin: 0;
      font-weight: 900;
      letter-spacing: -0.035em;
      line-height: 1.05;
      font-size: clamp(26px, 4vw, 42px);
      color: var(--ink, #0f172a);
      overflow-wrap: anywhere;
    }

    .ss-name{
      margin: 0;
      color: var(--muted-color, #64748b);
      font-weight: 700;
      font-size: clamp(14px, 1.8vw, 16px);
      display:flex;
      align-items:center;
      gap: 8px;
      flex-wrap: wrap;
    }

    .ss-name i{ color: var(--primary-color, #9E363A); }

    .ss-date-pill{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 8px 14px;
      border-radius: 999px;
      background: var(--surface-alt, #f8fafc);
      border: 1px solid var(--line-strong, rgba(15,23,42,.12));
      color: var(--ink, #0f172a);
      font-size: 13px;
      font-weight: 600;
      white-space: nowrap;
      flex-shrink: 0;
    }

    .ss-date-pill i{ color: var(--primary-color, #9E363A); opacity: .9; }

    .ss-intro{
      margin: 0;
      color: var(--ink, #0f172a);
      font-size: 14px;
      line-height: 1.7;
      background: rgba(15,23,42,.02);
      border: 1px dashed var(--line-strong, rgba(15,23,42,.12));
      border-radius: 12px;
      padding: 10px 12px;
    }

    .ss-meta{
      display:flex;
      flex-wrap:wrap;
      gap: 9px;
      align-items:center;
    }

    .ss-pill{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 7px 12px;
      border-radius: 999px;
      background: var(--surface-alt, #f8fafc);
      border: 1px solid var(--line-strong, rgba(15,23,42,.12));
      color: var(--ink, #0f172a);
      font-size: 12px;
      font-weight: 700;
      white-space: nowrap;
    }

    .ss-pill i{
      color: var(--primary-color, #9E363A);
      opacity: .85;
      font-size: 12px;
    }

    .ss-tags{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
    }

    .ss-tag{
      display:inline-flex;
      align-items:center;
      padding: 6px 11px;
      border-radius: 999px;
      background: rgba(158,54,58,.08);
      border: 1px solid rgba(158,54,58,.20);
      color: var(--ink, #0f172a);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: .04em;
      text-transform: lowercase;
    }

    .ss-actions{
      display:flex;
      flex-wrap:wrap;
      gap:10px;
      margin-top: 2px;
    }

    .ss-btn{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 9px 15px;
      border-radius: 999px;
      border: 1px solid var(--line-strong, rgba(15,23,42,.12));
      background: var(--surface, #fff);
      color: var(--ink, #0f172a);
      text-decoration: none;
      font-weight: 700;
      font-size: 13px;
      transition: all .22s ease;
      cursor: pointer;
    }

    .ss-btn:hover{
      background: var(--primary-color, #9E363A);
      color: #fff;
      border-color: var(--primary-color, #9E363A);
      transform: translateY(-1px);
      box-shadow: var(--shadow-2, 0 10px 26px rgba(2,6,23,.08));
    }

    .ss-btn.ss-btn-ghost{
      background: rgba(158,54,58,.05);
      border-color: rgba(158,54,58,.20);
      color: var(--primary-color, #9E363A);
    }
    .ss-btn.ss-btn-ghost:hover{ color:#fff; }

    /* ============ Hero Side (profile/at-a-glance) ============ */
    .ss-hero-side{
      padding: 14px;
      display: grid;
      align-content: start;
      gap: 12px;
      min-width: 0;
    }

    .ss-photo-wrap{
      background: linear-gradient(180deg, rgba(158,54,58,.08), rgba(158,54,58,.02));
      border: 1px solid rgba(158,54,58,.14);
      border-radius: 14px;
      padding: 12px;
    }

    .ss-photo-frame{
      width: 100%;
      border-radius: 14px;
      overflow: hidden;
      border: 1px solid var(--line-strong, rgba(15,23,42,.12));
      background: var(--surface-alt, #f8fafc);
      box-shadow: var(--shadow-1, 0 6px 16px rgba(2,6,23,.06));
      aspect-ratio: 1 / 1;
    }

    .ss-photo-frame img{
      width: 100%;
      height: 100%;
      object-fit: cover;
      display:block;
    }

    .ss-no-photo{
      display:flex;
      align-items:center;
      justify-content:center;
      height: 100%;
      color: var(--muted-color, #64748b);
      font-size: 42px;
    }

    .ss-side-head{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
      padding: 4px 2px 0;
    }

    .ss-side-title{
      margin: 0;
      font-size: 13px;
      font-weight: 800;
      color: var(--muted-color, #64748b);
      letter-spacing: .08em;
      text-transform: uppercase;
    }

    .ss-facts{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    .ss-fact{
      border: 1px solid var(--line-strong, rgba(15,23,42,.12));
      background: var(--surface-alt, #f8fafc);
      border-radius: 12px;
      padding: 10px 11px;
      min-width: 0;
    }

    .ss-fact-label{
      font-size: 11px;
      color: var(--muted-color, #64748b);
      text-transform: uppercase;
      letter-spacing: .06em;
      font-weight: 700;
      margin-bottom: 4px;
    }

    .ss-fact-value{
      font-size: 13px;
      font-weight: 700;
      color: var(--ink, #0f172a);
      line-height: 1.35;
      overflow-wrap: anywhere;
    }

    .ss-fact--full{ grid-column: 1 / -1; }

    /* ============ Content Grid ============ */
    .ss-grid{
      display:grid;
      grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
      gap: 18px;
      align-items:start;
    }

    .ss-main-card{
      padding: clamp(16px, 2vw, 22px);
      min-width: 0;
    }

    .ss-card-head{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
      margin-bottom: 12px;
      padding-bottom: 10px;
      border-bottom: 1px solid var(--line-light, rgba(15,23,42,.08));
    }

    .ss-card-head h2,
    .ss-card-head h3{
      margin: 0;
      font-size: clamp(16px, 2vw, 18px);
      font-weight: 800;
      letter-spacing: -.02em;
      color: var(--ink, #0f172a);
    }

    .ss-card-head .ss-mini{
      font-size: 11px;
      color: var(--muted-color, #64748b);
      text-transform: uppercase;
      letter-spacing: .07em;
      font-weight: 700;
    }

    .ss-description{
      color: var(--ink, #0f172a);
      font-size: 15px;
      line-height: 1.85;
      overflow-wrap: anywhere;
    }

    .ss-description p{ margin: 0 0 14px; }

    .ss-description h1,
    .ss-description h2,
    .ss-description h3,
    .ss-description h4{
      margin: 20px 0 10px;
      line-height: 1.3;
      letter-spacing: -0.02em;
      font-weight: 800;
      color: var(--ink, #0f172a);
    }

    .ss-description h1{ font-size: 1.9rem; }
    .ss-description h2{ font-size: 1.6rem; }
    .ss-description h3{ font-size: 1.35rem; }
    .ss-description h4{ font-size: 1.15rem; }

    .ss-description ul,
    .ss-description ol{
      padding-left: 22px;
      margin: 12px 0;
    }

    .ss-description li{ margin-bottom: 7px; }

    .ss-description a{
      color: var(--primary-color, #9E363A);
      text-decoration: underline;
      text-underline-offset: 3px;
    }

    .ss-description img{
      max-width: 100%;
      height: auto;
      border-radius: 12px;
      margin: 10px 0;
      box-shadow: var(--shadow-1, 0 6px 16px rgba(2,6,23,.06));
    }

    .ss-rail{
      display:grid;
      gap: 14px;
      position: sticky;
      top: 84px;
    }

    .ss-side-item{
      padding: 14px;
    }

    .ss-side-label{
      font-size: 12px;
      font-weight: 800;
      color: var(--muted-color, #64748b);
      text-transform: uppercase;
      letter-spacing: .07em;
      margin-bottom: 8px;
    }

    .ss-side-value{
      font-size: 14px;
      font-weight: 700;
      color: var(--ink, #0f172a);
      line-height: 1.5;
      word-break: break-word;
    }

    .ss-quote{
      border-left: 4px solid var(--primary-color, #9E363A);
      background: rgba(158,54,58,.06);
      border-radius: 10px;
      padding: 12px 12px 12px 10px;
      font-style: italic;
      color: var(--ink, #0f172a);
      line-height: 1.75;
      font-size: 14px;
    }

    .ss-socials{
      display:grid;
      gap: 10px;
    }

    .ss-social-link{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
      padding: 10px 11px;
      border-radius: 10px;
      border: 1px solid var(--line-strong, rgba(15,23,42,.12));
      background: var(--surface-alt, #f8fafc);
      color: var(--ink, #0f172a);
      text-decoration: none;
      transition: all .18s ease;
    }

    .ss-social-link:hover{
      border-color: rgba(158,54,58,.45);
      background: rgba(158,54,58,.05);
      transform: translateY(-1px);
    }

    .ss-social-left{
      display:flex;
      align-items:center;
      gap: 10px;
      min-width: 0;
      flex: 1;
    }

    .ss-social-left i.main-ico{
      color: var(--primary-color, #9E363A);
      width: 16px;
      text-align: center;
      flex-shrink: 0;
    }

    .ss-social-name{
      font-weight: 700;
      font-size: 13px;
      text-transform: capitalize;
      line-height: 1.2;
    }

    .ss-social-url{
      color: var(--muted-color, #64748b);
      font-size: 11px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 160px;
    }

    /* ============ Loading / Error ============ */
    .ss-loading{
      display:grid;
      gap: 12px;
      max-width: 100%;
      margin-top: 8px;
    }

    .ss-loading-bar{
      height: 14px;
      border-radius: 999px;
      background: var(--surface-alt, #f1f5f9);
      overflow: hidden;
      position: relative;
      border: 1px solid var(--line-light, rgba(15,23,42,.08));
    }

    .ss-loading-bar::after{
      content:"";
      position:absolute;
      inset:0;
      transform: translateX(-100%);
      background: linear-gradient(90deg, transparent, rgba(158,54,58,.18), transparent);
      animation: ssShimmer 1.25s infinite;
    }

    @keyframes ssShimmer { to { transform: translateX(100%); } }

    .ss-error{
      background: #fff5f5;
      border: 1px solid #fecaca;
      border-radius: 14px;
      padding: 14px 16px;
      color: #b91c1c;
      line-height: 1.6;
      margin-top: 8px;
      display:flex;
      align-items:flex-start;
      gap: 10px;
    }

    .ss-error i{ font-size: 18px; margin-top: 2px; }

    /* ============ Toast ============ */
    .ss-toast{
      position: fixed;
      right: 18px;
      top: 18px;
      z-index: 2000;
      background: #0f172a;
      color: #fff;
      border-radius: 12px;
      padding: 10px 12px;
      font-size: 13px;
      font-weight: 600;
      box-shadow: 0 10px 24px rgba(2,6,23,.24);
      display: none;
      align-items:center;
      gap: 8px;
      max-width: min(92vw, 340px);
    }

    .ss-toast.show{ display:flex; animation: ssFade .22s ease; }
    .ss-toast.success{ background: #065f46; }
    .ss-toast.error{ background: #991b1b; }

    @keyframes ssFade{
      from { opacity: 0; transform: translateY(-6px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* ============ Responsive ============ */
    @media (max-width: 1100px){
      .ss-hero{
        grid-template-columns: 1fr;
      }
      .ss-hero-side{
        grid-template-columns: 180px 1fr;
        gap: 12px;
        align-items: start;
      }
      .ss-photo-wrap{ height: 100%; }
      .ss-grid{
        grid-template-columns: 1fr;
      }
      .ss-rail{
        position: static;
        top: auto;
      }
      .ss-social-url{ max-width: 220px; }
    }

    @media (max-width: 700px){
      .ss-hero-side{
        grid-template-columns: 1fr;
      }
      .ss-facts{
        grid-template-columns: 1fr 1fr;
      }
      .ss-head-top{
        gap: 8px;
      }
      .ss-date-pill{
        margin-left: 0;
      }
      .ss-title{
        font-size: clamp(24px, 7vw, 32px);
      }
      .ss-actions{
        gap: 8px;
      }
      .ss-btn{
        padding: 8px 12px;
        font-size: 12px;
      }
      .ss-social-url{
        max-width: 135px;
      }
      .ss-fact-value{
        font-size: 12px;
      }
    }
    #ssDatePill, #ssFeaturedPill, #ssViewsPill { display: none !important; }
    .ss-intro p { margin-bottom: 12px; }
    .ss-intro p:last-child { margin-bottom: 0; }
    .ss-grid { grid-template-columns: 1fr !important; }
  </style>
</head>
<body>

  @include('landing.components.topHeaderMenu')
  @include('landing.components.header')
  @include('landing.components.headerMenu')

  <div class="ss-toast" id="ssToast" role="status" aria-live="polite"></div>

  <main class="ss-container">
    {{-- Loading --}}
    <section id="loadingSection" class="ss-loading" aria-live="polite">
      <div class="ss-loading-bar" style="width:68%"></div>
      <div class="ss-loading-bar" style="width:92%"></div>
      <div class="ss-loading-bar" style="width:80%"></div>
      <div class="ss-loading-bar" style="width:86%"></div>
      <div class="ss-loading-bar" style="width:60%"></div>
    </section>

    {{-- Error --}}
    <div id="errorSection" class="ss-error" style="display:none">
      <i class="fa-solid fa-circle-exclamation"></i>
      <span id="errorMessage"></span>
    </div>

    {{-- Whole page content (all details in one page) --}}
    <section id="contentSection" style="display:none">
      {{-- Hero: title + meta + actions + photo + quick facts --}}
      <section class="ss-hero">
        <div class="ss-card ss-hero-main">
          <div class="ss-head-top">
            <div class="ss-kicker">
              <i class="fa-solid fa-trophy"></i>
              Success Story
            </div>

            <span class="ss-date-pill" id="ssDatePill" style="display:none">
              <i class="fa-regular fa-calendar"></i>
              <span id="ssDateText"></span>
            </span>
          </div>

          <div>
            <h1 class="ss-title" id="ssTitle">Success Story</h1>
            <div class="ss-name" id="ssNameRow" style="display:none; margin-top:8px;">
              <i class="fa-solid fa-user-graduate"></i>
              <span id="ssName">Student Name</span>
            </div>
          </div>

          <p class="ss-intro" id="ssIntroText" style="display:none"></p>

          <div class="ss-meta" id="ssMeta" style="display:none">
            <span class="ss-pill" id="ssDeptPill" style="display:none">
              <i class="fa-solid fa-building-columns"></i>
              <span id="ssDeptText"></span>
            </span>



            <span class="ss-pill" id="ssViewsPill" style="display:none">
              <i class="fa-regular fa-eye"></i>
              <span id="ssViewsText"></span>
            </span>

            <span class="ss-pill" id="ssFeaturedPill" style="display:none">
              <i class="fa-solid fa-star"></i>
              Featured
            </span>
          </div>

          <div class="ss-tags" id="ssTags" style="display:none"></div>

          <div class="ss-actions">
            <button class="ss-btn" id="copyLinkBtn" type="button">
              <i class="fa-solid fa-link"></i>
              Copy Link
            </button>

            <button class="ss-btn ss-btn-ghost" id="shareBtn" style="display:none" type="button">
              <i class="fa-solid fa-share-nodes"></i>
              Share
            </button>
          </div>
        </div>

        <aside class="ss-card ss-hero-side">
          <div class="ss-photo-wrap">
            <div class="ss-photo-frame">
              <img id="ssPhoto" alt="Success Story Photo" style="display:none;" loading="lazy">
              <div class="ss-no-photo" id="ssNoPhoto">
                <i class="fa-solid fa-user"></i>
              </div>
            </div>
          </div>
          <div style="display:flex; justify-content:center; margin-top: 12px; width: 100%;">
            <span class="ss-pill" id="ssYearPill" style="display:none; text-align: center;">
              <i class="fa-solid fa-award"></i>
              <span id="ssYearText"></span>
            </span>
          </div>
        </aside>
      </section>

      {{-- Story + extra details --}}
      <section class="ss-grid">


        <aside class="ss-rail">
          <div class="ss-card ss-side-item" id="ssRoleCard" style="display:none">
            <div class="ss-side-label">Achievement</div>
            <div class="ss-side-value" id="ssRoleTextSide"></div>
          </div>

          <div class="ss-card ss-side-item" id="ssQuoteCard" style="display:none">
            <div class="ss-side-label">Quote</div>
            <div class="ss-quote" id="ssQuoteText"></div>
          </div>

          <div class="ss-card ss-side-item" id="ssSocialCard" style="display:none">
            <div class="ss-side-label">Links</div>
            <div class="ss-socials" id="ssSocialList"></div>
          </div>
        </aside>
      </section>
    </section>
  </main>

  @include('landing.components.footer')

  <script>
    (function () {
      const $ = (id) => document.getElementById(id);

      function getIdentifierFromUrl() {
        const parts = window.location.pathname.split('/').filter(Boolean);
        return parts[parts.length - 1] || '';
      }

      function getDepartmentFromUrl() {
        const parts = window.location.pathname.split('/').filter(Boolean);
        const deptIdx = parts.indexOf('departments');
        if (deptIdx !== -1 && parts[deptIdx + 1]) return parts[deptIdx + 1];
        return '';
      }

      function safeJson(v) {
        try {
          if (v == null) return null;
          if (typeof v === 'object') return v;
          const s = String(v).trim();
          if (!s) return null;
          return JSON.parse(s);
        } catch (e) {
          return null;
        }
      }

      function escapeHtml(value) {
        return String(value ?? '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }

      function resolveUrl(path) {
        if (!path) return '';
        const p = String(path).trim();
        if (!p) return '';

        if (/^(https?:\/\/|mailto:|tel:)/i.test(p)) return p;
        if (/^[\w.-]+\.[a-z]{2,}(\/.*)?$/i.test(p) && !p.startsWith('/')) return 'https://' + p;
        if (p.startsWith('//')) return window.location.protocol + p;

        return window.location.origin + '/' + p.replace(/^\/+/, '');
      }

      function formatDate(v) {
        if (!v) return '';
        const d = new Date(v);
        if (isNaN(d.getTime())) return String(v);
        return d.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
      }

      function numberFormat(v) {
        const n = Number(v);
        return Number.isFinite(n) ? n.toLocaleString('en-US') : String(v || '');
      }

      function setLoading(show) {
        $('loadingSection').style.display = show ? '' : 'none';
      }

      function setError(msg) {
        $('errorSection').style.display = msg ? '' : 'none';
        $('errorMessage').textContent = msg || '';
      }

      function showContent(show) {
        $('contentSection').style.display = show ? '' : 'none';
      }

      function showToast(message, type) {
        const el = $('ssToast');
        if (!el) return;

        el.className = 'ss-toast ' + (type || '');
        el.innerHTML = `<i class="fa-solid ${type === 'error' ? 'fa-circle-xmark' : 'fa-circle-check'}"></i><span>${escapeHtml(message)}</span>`;
        el.classList.add('show');

        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => {
          el.classList.remove('show');
          setTimeout(() => {
            if (!el.classList.contains('show')) {
              el.style.display = 'none';
              el.className = 'ss-toast';
              el.innerHTML = '';
            }
          }, 180);
        }, 1800);

        // make sure display:flex applies even after reset
        el.style.display = 'flex';
      }

      function findItem(payload) {
        if (!payload) return null;

        if (payload.item && typeof payload.item === 'object') return payload.item;
        if (payload.data && typeof payload.data === 'object') return payload.data;
        if (payload.success_story && typeof payload.success_story === 'object') return payload.success_story;
        if (Array.isArray(payload) && payload.length && typeof payload[0] === 'object') return payload[0];
        if (typeof payload === 'object' && (payload.title || payload.description || payload.uuid || payload.slug)) return payload;

        return null;
      }

      function socialIconFor(key, url) {
        const k = String(key || '').toLowerCase();
        const u = String(url || '').toLowerCase();

        if (k.includes('linkedin') || u.includes('linkedin.com')) return 'fa-brands fa-linkedin-in';
        if (k.includes('github') || u.includes('github.com')) return 'fa-brands fa-github';
        if (k.includes('facebook') || u.includes('facebook.com')) return 'fa-brands fa-facebook-f';
        if (k.includes('instagram') || u.includes('instagram.com')) return 'fa-brands fa-instagram';
        if (k.includes('twitter') || u.includes('twitter.com') || u.includes('x.com')) return 'fa-brands fa-x-twitter';
        if (k.includes('portfolio') || k.includes('website') || k.includes('site')) return 'fa-solid fa-globe';
        if (k.includes('mail')) return 'fa-solid fa-envelope';
        return 'fa-solid fa-link';
      }

      function hostLabel(url) {
        try {
          const u = new URL(url);
          return u.protocol.startsWith('http') ? u.hostname.replace(/^www\./, '') : u.protocol.replace(':', '');
        } catch (e) {
          return url;
        }
      }

      function plainTextFromHtml(html) {
        const div = document.createElement('div');
        div.innerHTML = html || '';
        return (div.textContent || div.innerText || '').replace(/\s+/g, ' ').trim();
      }

      function renderTags(metadata) {
        const wrap = $('ssTags');
        wrap.innerHTML = '';

        const parsed = safeJson(metadata);
        let tags = [];

        if (parsed && typeof parsed === 'object' && Array.isArray(parsed.tags)) {
          tags = parsed.tags;
        } else if (Array.isArray(metadata)) {
          tags = metadata;
        }

        tags = tags
          .map(t => String(t || '').trim())
          .filter(Boolean);

        if (!tags.length) {
          wrap.style.display = 'none';
          return;
        }

        tags.forEach(tag => {
          const el = document.createElement('span');
          el.className = 'ss-tag';
          el.textContent = tag;
          wrap.appendChild(el);
        });

        wrap.style.display = wrap.children.length ? 'flex' : 'none';
      }

      function renderSocials(social_links_json) {
        const list = $('ssSocialList');
        list.innerHTML = '';

        const links = safeJson(social_links_json) || social_links_json || {};
        if (!links || typeof links !== 'object') {
          $('ssSocialCard').style.display = 'none';
          return;
        }

        Object.entries(links).forEach(([key, rawUrl]) => {
          const url = resolveUrl(rawUrl);
          if (!url) return;

          const a = document.createElement('a');
          a.className = 'ss-social-link';
          a.href = url;
          a.target = '_blank';
          a.rel = 'noopener noreferrer';

          const icon = socialIconFor(key, url);
          const name = String(key).replace(/[_-]+/g, ' ').trim() || 'Link';
          const host = hostLabel(url);

          a.innerHTML = `
            <div class="ss-social-left">
              <i class="main-ico ${icon}"></i>
              <div style="min-width:0;">
                <div class="ss-social-name">${escapeHtml(name)}</div>
                <div class="ss-social-url" title="${escapeHtml(url)}">${escapeHtml(host)}</div>
              </div>
            </div>
            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:11px; opacity:.7;"></i>
          `;

          list.appendChild(a);
        });

        $('ssSocialCard').style.display = list.children.length ? '' : 'none';
      }

      function setFact(id, value, fallbackEmpty = true) {
        const row = $(id);
        if (!row) return;
        row.style.display = value ? '' : (fallbackEmpty ? 'none' : '');
      }

      function renderPage(item) {
        const title = item.title || 'Success Story';
        const name = item.name || '';
        const pageTitle = name ? `${name} — ${title}` : title;

        $('ssTitle').textContent = title;
        document.title = pageTitle;

        if (name) {
          $('ssName').textContent = name;
          $('ssNameRow').style.display = '';
        } else {
          $('ssNameRow').style.display = 'none';
          $('ssName').textContent = '';
        }

        const dateVal = item.date || item.publish_at || item.created_at || item.updated_at;
        const dateTxt = formatDate(dateVal);
        if (dateTxt) {
          $('ssDateText').textContent = dateTxt;
          $('ssDatePill').style.display = '';
        } else {
          $('ssDatePill').style.display = 'none';
          $('ssDateText').textContent = '';
        }

        const descHtml = item.description || '<p>No description available.</p>';
        if (descHtml) {
          $('ssIntroText').innerHTML = descHtml;
          $('ssIntroText').style.display = '';
        } else {
          $('ssIntroText').style.display = 'none';
        }

        let hasMeta = false;

        const dept = item.department_title || item.department_name || (item.department && (item.department.title || item.department.name)) || '';
        if (dept) {
          $('ssDeptText').textContent = dept;
          $('ssDeptPill').style.display = '';
          hasMeta = true;
        } else {
          $('ssDeptPill').style.display = 'none';
          $('ssDeptText').textContent = '';
        }

        if (item.year) {
          const batchText = `Batch ${item.year}`;
          $('ssYearText').textContent = batchText;
          $('ssYearPill').style.display = '';
        } else {
          $('ssYearPill').style.display = 'none';
          $('ssYearText').textContent = '';
        }

        if (item.views_count != null && item.views_count !== '') {
          const viewsText = `${numberFormat(item.views_count)} views`;
          $('ssViewsText').textContent = viewsText;
          $('ssViewsPill').style.display = '';
          hasMeta = true;
        } else {
          $('ssViewsPill').style.display = 'none';
          $('ssViewsText').textContent = '';
        }

        const featured = (item.is_featured_home === 1 || item.is_featured_home === true || String(item.is_featured_home) === '1');
        $('ssFeaturedPill').style.display = featured ? '' : 'none';

        const statusRaw = String(item.status || '').trim();
        let statusLabel = '';
        if (featured) statusLabel = 'Featured';
        else if (statusRaw) statusLabel = statusRaw.charAt(0).toUpperCase() + statusRaw.slice(1);



        if (featured || statusRaw) hasMeta = true;

        $('ssMeta').style.display = hasMeta ? '' : 'none';

        const photo = resolveUrl(item.photo_full_url || item.photo_url || item.photo || '');
        if (photo) {
          $('ssPhoto').src = photo;
          $('ssPhoto').style.display = '';
          $('ssNoPhoto').style.display = 'none';
        } else {
          $('ssPhoto').removeAttribute('src');
          $('ssPhoto').style.display = 'none';
          $('ssNoPhoto').style.display = '';
        }

        const achievement = item.title || '';
        if (achievement) {
          $('ssRoleTextSide').textContent = achievement;
          $('ssRoleCard').style.display = '';
        } else {
          $('ssRoleTextSide').textContent = '';
          $('ssRoleCard').style.display = 'none';
        }

        if (item.quote) {
          $('ssQuoteText').textContent = String(item.quote);
          $('ssQuoteCard').style.display = '';
        } else {
          $('ssQuoteCard').style.display = 'none';
          $('ssQuoteText').textContent = '';
        }

        renderSocials(item.social_links_json);
        renderTags(item.metadata);

        $('shareBtn').style.display = (navigator.share ? '' : 'none');
      }

      async function fetchJson(url) {
        const res = await fetch(url, {
          method: 'GET',
          headers: { 'Accept': 'application/json' }
        });

        let data = null;
        try { data = await res.json(); } catch (e) {}
        return { res, data };
      }

      async function load() {
        const identifier = getIdentifierFromUrl();
        if (!identifier) {
          setLoading(false);
          setError('No UUID/identifier found in the URL.');
          return;
        }

        setLoading(true);
        setError('');
        showContent(false);

        const dept = getDepartmentFromUrl();

        const candidates = [
          `/api/success-stories/${encodeURIComponent(identifier)}`,
          `/public/success-stories/${encodeURIComponent(identifier)}`,
          `/api/public/success-stories/${encodeURIComponent(identifier)}`
        ];

        if (dept) {
          candidates.unshift(`/api/departments/${encodeURIComponent(dept)}/success-stories/${encodeURIComponent(identifier)}`);
          candidates.unshift(`/api/public/departments/${encodeURIComponent(dept)}/success-stories/${encodeURIComponent(identifier)}`);
        }

        for (const url of candidates) {
          try {
            const { res, data } = await fetchJson(url);
            if (!res || !res.ok) continue;

            const item = findItem(data);
            if (item && (item.title || item.description || item.name)) {
              setLoading(false);
              showContent(true);
              renderPage(item);
              return;
            }
          } catch (e) {
            // try next candidate
          }
        }

        setLoading(false);
        setError('Success Story not found or API endpoint is not reachable. Expected API (example): /api/success-stories/{uuid}');
      }

      $('copyLinkBtn').addEventListener('click', async () => {
        try {
          await navigator.clipboard.writeText(window.location.href);
          showToast('Link copied', 'success');
        } catch (e) {
          showToast('Copy failed. Please copy from address bar.', 'error');
        }
      });

      $('shareBtn').addEventListener('click', async () => {
        if (!navigator.share) return;
        try {
          await navigator.share({
            title: document.title,
            url: window.location.href
          });
        } catch (e) {
          // user cancelled or not supported
        }
      });

      load();
    })();
  </script>
</body>
</html>