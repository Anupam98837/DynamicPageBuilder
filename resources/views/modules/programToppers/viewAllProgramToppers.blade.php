{{-- resources/views/landing/viewAllProgramToppers.blade.php --}}

{{-- (optional) FontAwesome for icons used below; remove if already included in header --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

<style>
.ptp-wrap{
  /* scoped tokens */
  --ptp-brand: var(--primary-color, #9E363A);
  --ptp-ink: #0f172a;
  --ptp-muted: #64748b;
  --ptp-bg: var(--page-bg, #ffffff);
  --ptp-card: var(--surface, #ffffff);
  --ptp-line: var(--line-soft, rgba(15,23,42,.10));
  --ptp-shadow: 0 10px 24px rgba(2,6,23,.08);

  /* fixed card sizing (same as Alumni) */
  --ptp-card-w: 247px;
  --ptp-card-h: 329px;
  --ptp-radius: 18px;

  max-width: 1320px;
  margin: 18px auto 54px;
  padding: 0 12px;
  background: transparent;
  position: relative;
  overflow: visible;
}

/* Header */
.ptp-head{
  background: var(--ptp-card);
  border: 1px solid var(--ptp-line);
  border-radius: 16px;
  box-shadow: var(--ptp-shadow);
  padding: 14px 16px;
  margin-bottom: 16px;

  display:flex;
  gap: 12px;
  align-items:center;
  justify-content:space-between;

  /* ✅ keep one row (desktop) */
  flex-wrap: nowrap;
}
.ptp-title{
  margin:0;
  font-weight: 950;
  letter-spacing: .2px;
  color: var(--ptp-ink);
  font-size: 28px;
  display:flex;
  align-items:center;
  gap: 10px;
  white-space: nowrap;
}
.ptp-title i{ color: var(--ptp-brand); }
.ptp-sub{
  margin: 6px 0 0;
  color: var(--ptp-muted);
  font-size: 14px;
}

.ptp-tools{
  display:flex;
  gap: 10px;
  align-items:center;

  /* ✅ keep one row (desktop) */
  flex-wrap: nowrap;
}

/* Search */
.ptp-search{
  position: relative;
  min-width: 260px;
  max-width: 520px;
  flex: 1 1 320px;
}
.ptp-search i{
  position:absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  opacity: .65;
  color: var(--ptp-muted);
  pointer-events:none;
}
.ptp-search input{
  width:100%;
  height: 42px;
  border-radius: 999px;
  padding: 11px 12px 11px 42px;
  border: 1px solid var(--ptp-line);
  background: var(--ptp-card);
  color: var(--ptp-ink);
  outline: none;
}
.ptp-search input:focus{
  border-color: rgba(201,75,80,.55);
  box-shadow: 0 0 0 4px rgba(201,75,80,.18);
}

/* Dept dropdown */
.ptp-select{
  position: relative;
  min-width: 260px;
  max-width: 360px;
  flex: 0 1 320px;
}
.ptp-select__icon{
  position:absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  opacity: .70;
  color: var(--ptp-muted);
  pointer-events:none;
  font-size: 14px;
}
.ptp-select__caret{
  position:absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  opacity: .70;
  color: var(--ptp-muted);
  pointer-events:none;
  font-size: 12px;
}
.ptp-select select{
  width: 100%;
  height: 42px;
  border-radius: 999px;
  padding: 10px 38px 10px 42px;
  border: 1px solid var(--ptp-line);
  background: var(--ptp-card);
  color: var(--ptp-ink);
  outline: none;

  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
}
.ptp-select select:focus{
  border-color: rgba(201,75,80,.55);
  box-shadow: 0 0 0 4px rgba(201,75,80,.18);
}

/* Grid */
.ptp-grid{
  display:grid;
  grid-template-columns: repeat(auto-fill, var(--ptp-card-w));
  gap: 18px;
  align-items: start;
  justify-content: center;
}

/* Card */
.ptp-card{
  position: relative;
  width: var(--ptp-card-w);
  height: var(--ptp-card-h);
  border-radius: var(--ptp-radius);
  overflow:hidden;
  display:block;
  text-decoration:none !important;
  color: inherit;
  background: #fff;
  border: 1px solid rgba(2,6,23,.08);
  box-shadow: 0 12px 26px rgba(0,0,0,.10);
  transform: translateZ(0);
  transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
  cursor:pointer;
}
.ptp-card:hover{
  transform: translateY(-4px);
  box-shadow: 0 18px 42px rgba(0,0,0,.16);
  border-color: rgba(158,54,58,.22);
}

/* image as bg */
.ptp-card .bg{
  position:absolute; inset:0;
  background-size: cover;
  background-position: center;
  filter: saturate(1.02);
  transform: scale(1.0001);
}

/* vignette */
.ptp-card .vignette{
  position:absolute; inset:0;
  background:
    radial-gradient(1200px 500px at 50% -20%, rgba(255,255,255,.10), rgba(0,0,0,0) 60%),
    linear-gradient(180deg, rgba(0,0,0,.00) 28%, rgba(0,0,0,.12) 60%, rgba(0,0,0,.62) 100%);
}

/* bottom overlay text */
.ptp-card .info{
  position:absolute;
  left: 14px;
  right: 14px;
  bottom: 14px;
  z-index: 2;
}
.ptp-name{
  margin:0;
  font-size: 18px;
  font-weight: 950;
  line-height: 1.12;
  color: #fff;
  text-shadow: 0 6px 16px rgba(0,0,0,.35);
}
.ptp-meta{
  margin: 7px 0 0;
  font-size: 13px;
  font-weight: 800;
  color: rgba(255,255,255,.90);
  text-shadow: 0 6px 16px rgba(0,0,0,.35);
  line-height: 1.2;
}
.ptp-meta .dot{
  opacity: .85;
  padding: 0 6px;
}
.ptp-submeta{
  margin: 7px 0 0;
  font-size: 12.5px;
  color: rgba(255,255,255,.82);
  text-shadow: 0 6px 16px rgba(0,0,0,.35);
  line-height: 1.2;
}

/* top pills */
.ptp-pill{
  position:absolute;
  top: 12px;
  left: 12px;
  z-index: 2;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 11.5px;
  font-weight: 900;
  letter-spacing:.15px;
  color:#fff;
  background: rgba(0,0,0,.28);
  border: 1px solid rgba(255,255,255,.20);
  backdrop-filter: blur(6px);

  max-width: calc(100% - 24px);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.ptp-rank{
  position:absolute;
  top: 12px;
  right: 12px;
  z-index: 2;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 11.5px;
  font-weight: 950;
  letter-spacing:.15px;
  color:#1a1a2e;
  background: rgba(255,255,255,.92);
  border: 1px solid rgba(255,255,255,.35);
  backdrop-filter: blur(6px);
  box-shadow: 0 8px 18px rgba(0,0,0,.14);
}

/* Placeholder */
.ptp-placeholder{
  position:absolute; inset:0;
  display:grid; place-items:center;
  background:
    radial-gradient(800px 360px at 20% 10%, rgba(158,54,58,.20), transparent 60%),
    radial-gradient(900px 400px at 80% 90%, rgba(158,54,58,.14), transparent 60%),
    linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,255,255,.82));
}
.ptp-initials{
  width: 86px; height: 86px;
  border-radius: 24px;
  display:grid; place-items:center;
  font-weight: 950;
  font-size: 28px;
  color: var(--ptp-brand);
  background: rgba(158,54,58,.12);
  border: 1px solid rgba(158,54,58,.25);
}

/* State / empty */
.ptp-state{
  background: var(--ptp-card);
  border: 1px solid var(--ptp-line);
  border-radius: 16px;
  box-shadow: var(--ptp-shadow);
  padding: 18px;
  color: var(--ptp-muted);
  text-align:center;
}

/* Skeleton */
.ptp-skeleton{
  display:grid;
  grid-template-columns: repeat(auto-fill, var(--ptp-card-w));
  gap: 18px;
  justify-content: center;
}
.ptp-sk{
  border-radius: var(--ptp-radius);
  border: 1px solid var(--ptp-line);
  background: #fff;
  overflow:hidden;
  position:relative;
  box-shadow: 0 10px 24px rgba(2,6,23,.08);
  height: var(--ptp-card-h);
}
.ptp-sk:before{
  content:'';
  position:absolute; inset:0;
  transform: translateX(-60%);
  background: linear-gradient(90deg, transparent, rgba(148,163,184,.22), transparent);
  animation: ptpSkMove 1.15s ease-in-out infinite;
}
@keyframes ptpSkMove{ to{ transform: translateX(60%);} }

/* Pagination */
.ptp-pagination{
  display:flex;
  justify-content:center;
  margin-top: 18px;
}
.ptp-pagination .ptp-pager{
  display:flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items:center;
  justify-content:center;
  padding: 10px;
}
.ptp-pagebtn{
  border:1px solid var(--ptp-line);
  background: var(--ptp-card);
  color: var(--ptp-ink);
  border-radius: 12px;
  padding: 9px 12px;
  font-size: 13px;
  font-weight: 950;
  box-shadow: 0 8px 18px rgba(2,6,23,.06);
  cursor:pointer;
  user-select:none;
}
.ptp-pagebtn:hover{ background: rgba(2,6,23,.03); }
.ptp-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
.ptp-pagebtn.active{
  background: rgba(201,75,80,.12);
  border-color: rgba(201,75,80,.35);
  color: var(--ptp-brand);
}

/* ✅ allow wrapping on smaller screens */
@media (max-width: 992px){
  .ptp-head{ flex-wrap: wrap; align-items: flex-end; }
  .ptp-tools{ flex-wrap: wrap; }
}
@media (max-width: 640px){
  .ptp-title{ font-size: 24px; }
  .ptp-search{ min-width: 220px; flex: 1 1 240px; }
  .ptp-select{ min-width: 220px; flex: 1 1 240px; }
}

/* ===== Modal (same structure as Alumni) ===== */
.ptp-modal{
  position: fixed;
  inset: 0;
  display:none;
  z-index: 9999;
}
.ptp-modal.show{ display:flex; align-items:center; justify-content:center; }

.ptp-modal__overlay{
  position:fixed; inset:0;
  background: rgba(0,0,0,.72);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

.ptp-modal__panel{
  position:relative;
  width: min(1060px, calc(100% - 32px));
  max-height: calc(100vh - 48px);
  background: #fff;
  border-radius: 24px;
  box-shadow: 0 32px 80px rgba(0,0,0,.35), 0 0 0 1px rgba(255,255,255,.08);
  overflow:hidden;

  opacity: 0;
  transform: translateY(16px) scale(.97);
  transition: transform .28s cubic-bezier(.22,1,.36,1), opacity .22s ease;

  display: flex;
  flex-direction: row;
}
.ptp-modal.show .ptp-modal__panel{
  opacity: 1;
  transform: translateY(0) scale(1);
}

/* Close button */
.ptp-modal__close{
  position: absolute;
  top: 14px;
  right: 14px;
  z-index: 20;
  width: 42px; height: 42px;
  border-radius: 50%;
  border: none;
  background: rgba(255,255,255,.92);
  color: #1e293b;
  cursor:pointer;
  box-shadow: 0 4px 16px rgba(0,0,0,.18);
  display:grid;
  place-items:center;
  font-size: 16px;
  transition: background .15s, transform .15s;
}
.ptp-modal__close:hover{
  background: #fff;
  transform: scale(1.08);
}

/* Left: Hero */
.ptp-modal__hero{
  flex: 0 0 420px;
  position: relative;
  background: #1a1a2e;
  overflow: hidden;
  min-height: 520px;
}
.ptp-modal__hero-img{
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center top;
  display: block;
}
.ptp-modal__hero-grad{
  position: absolute;
  inset: 0;
  background:
    linear-gradient(180deg,
      rgba(0,0,0,0) 0%,
      rgba(0,0,0,0) 40%,
      rgba(0,0,0,.55) 75%,
      rgba(0,0,0,.82) 100%
    );
  pointer-events: none;
}
.ptp-modal__hero-info{
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 28px 26px;
  z-index: 3;
}
.ptp-modal__hero-name{
  margin: 0;
  font-size: 30px;
  font-weight: 900;
  color: #fff;
  line-height: 1.15;
  letter-spacing: -.2px;
  text-shadow: 0 2px 20px rgba(0,0,0,.4);
}
.ptp-modal__hero-sub{
  margin-top: 8px;
  display: flex;
  flex-wrap: wrap;
  gap: 6px 16px;
  align-items: center;
  font-size: 14px;
  font-weight: 600;
  color: rgba(255,255,255,.88);
  text-shadow: 0 2px 12px rgba(0,0,0,.35);
}
.ptp-modal__hero-sub .sep{
  opacity: .5;
  font-size: 10px;
}
.ptp-modal__hero-quote{
  margin-top: 14px;
  font-size: 13.5px;
  font-style: italic;
  color: rgba(255,255,255,.78);
  line-height: 1.4;
  text-shadow: 0 2px 12px rgba(0,0,0,.35);
  max-width: 360px;
}

.ptp-modal__hero-placeholder{
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background:
    radial-gradient(ellipse at 30% 20%, rgba(158,54,58,.25), transparent 60%),
    radial-gradient(ellipse at 70% 80%, rgba(158,54,58,.18), transparent 60%),
    linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #1a1a2e 100%);
}
.ptp-modal__hero-initials{
  width: 120px; height: 120px;
  border-radius: 32px;
  display:grid; place-items:center;
  font-weight: 950;
  font-size: 44px;
  color: rgba(255,255,255,.85);
  background: rgba(255,255,255,.08);
  border: 2px solid rgba(255,255,255,.12);
  backdrop-filter: blur(4px);
}

/* Right: Details */
.ptp-modal__details{
  flex: 1;
  overflow-y: auto;
  padding: 0;
  display: flex;
  flex-direction: column;
  max-height: calc(100vh - 48px);
}
.ptp-modal__dhead{
  position: sticky;
  top: 0;
  z-index: 10;
  padding: 18px 24px 14px;
  background: rgba(255,255,255,.95);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(15,23,42,.06);
}
.ptp-modal__dhead-name{
  margin: 0;
  font-size: 22px;
  font-weight: 900;
  color: #0f172a;
  line-height: 1.2;
}
.ptp-modal__dhead-tagline{
  margin-top: 4px;
  font-size: 13.5px;
  font-weight: 600;
  color: #64748b;
  display: flex;
  flex-wrap: wrap;
  gap: 4px 10px;
  align-items: center;
}
.ptp-modal__dhead-tagline .tsep{
  opacity: .45;
  font-size: 8px;
}
.ptp-modal__dcontent{
  flex: 1;
  padding: 16px 24px 28px;
}

/* Section titles */
.ptp-msection{ margin-top: 20px; }
.ptp-msection:first-child{ margin-top: 0; }
.ptp-msection-title{
  font-size: 11.5px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .8px;
  color: #94a3b8;
  margin: 0 0 10px;
  padding-bottom: 6px;
  border-bottom: 1px solid rgba(15,23,42,.06);
}

/* Field rows */
.ptp-mrow{
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}
.ptp-mfield{
  padding: 10px 12px;
  border-radius: 12px;
  background: #f8fafc;
  border: 1px solid rgba(15,23,42,.05);
  transition: background .12s;
}
.ptp-mfield:hover{ background: #f1f5f9; }
.ptp-mfield-label{
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .5px;
  color: #94a3b8;
  margin-bottom: 4px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.ptp-mfield-label i{
  color: var(--ptp-brand, #9E363A);
  font-size: 11px;
  opacity: .85;
}
.ptp-mfield-val{
  font-size: 13.5px;
  font-weight: 700;
  color: #1e293b;
  line-height: 1.3;
  word-break: break-word;
}
.ptp-mfield-full{ grid-column: 1 / -1; }

/* Chips */
.ptp-mchips{ display:flex; flex-wrap: wrap; gap: 6px; margin-top: 2px; }
.ptp-mchip{
  display:inline-flex;
  align-items:center;
  gap: 5px;
  padding: 5px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  color: #475569;
  background: #fff;
  border: 1px solid #e2e8f0;
}
.ptp-mchip i{ color: var(--ptp-brand, #9E363A); font-size: 10px; }

/* Note block */
.ptp-mnote{
  margin-top: 8px;
  padding: 14px 16px;
  border-radius: 14px;
  background: linear-gradient(135deg, rgba(158,54,58,.04), rgba(158,54,58,.08));
  border: 1px solid rgba(158,54,58,.12);
  grid-column: 1 / -1;
}
.ptp-mnote-label{
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .5px;
  color: var(--ptp-brand, #9E363A);
  margin-bottom: 6px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.ptp-mnote-val{
  font-size: 13.5px;
  font-weight: 600;
  color: #334155;
  line-height: 1.5;
  white-space: pre-wrap;
  font-style: italic;
}

/* Modal Responsive */
@media (max-width: 860px){
  .ptp-modal__panel{
    flex-direction: column;
    max-height: calc(100vh - 32px);
    width: min(560px, calc(100% - 24px));
  }
  .ptp-modal__hero{
    flex: 0 0 auto;
    min-height: 320px;
    max-height: 380px;
  }
  .ptp-modal__details{ max-height: none; }
  .ptp-modal__close{
    background: rgba(0,0,0,.45);
    color: #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,.25);
  }
  .ptp-modal__close:hover{
    background: rgba(0,0,0,.6);
    color: #fff;
  }
}
@media (max-width: 560px){
  .ptp-modal__hero{ min-height: 260px; max-height: 300px; }
  .ptp-modal__hero-name{ font-size: 24px; }
  .ptp-modal__dhead{ padding: 14px 16px 12px; }
  .ptp-modal__dhead-name{ font-size: 18px; }
  .ptp-modal__dcontent{ padding: 14px 16px 24px; }
  .ptp-mrow{ grid-template-columns: 1fr; }
}

/* Dark mode */
html.theme-dark .ptp-modal__panel{ background: #0f172a; }
html.theme-dark .ptp-modal__close{
  background: rgba(15,23,42,.85);
  color: #e2e8f0;
  box-shadow: 0 4px 16px rgba(0,0,0,.35);
}
html.theme-dark .ptp-modal__close:hover{ background: rgba(15,23,42,.95); }
html.theme-dark .ptp-modal__dhead{
  background: rgba(15,23,42,.95);
  border-bottom-color: rgba(255,255,255,.06);
}
html.theme-dark .ptp-modal__dhead-name{ color: #f1f5f9; }
html.theme-dark .ptp-modal__dhead-tagline{ color: #94a3b8; }
html.theme-dark .ptp-mfield{
  background: rgba(255,255,255,.04);
  border-color: rgba(255,255,255,.06);
}
html.theme-dark .ptp-mfield:hover{ background: rgba(255,255,255,.07); }
html.theme-dark .ptp-mfield-label{ color: #64748b; }
html.theme-dark .ptp-mfield-val{ color: #e2e8f0; }
html.theme-dark .ptp-msection-title{ color: #475569; border-bottom-color: rgba(255,255,255,.06); }
html.theme-dark .ptp-mchip{
  background: rgba(255,255,255,.06);
  border-color: rgba(255,255,255,.08);
  color: #cbd5e1;
}
html.theme-dark .ptp-mnote{
  background: linear-gradient(135deg, rgba(158,54,58,.08), rgba(158,54,58,.14));
  border-color: rgba(158,54,58,.2);
}
html.theme-dark .ptp-mnote-val{ color: #cbd5e1; }

/* ✅ Guard against Bootstrap overriding mega menu dropdown positioning */
.dynamic-navbar .navbar-nav .dropdown-menu{
  position: absolute !important;
  inset: auto !important;
}
.dynamic-navbar .dropdown-menu.is-portaled{
  position: fixed !important;
}
</style>

<div
  class="ptp-wrap"
  data-api="{{ url('/api/program-toppers') }}"
  data-dept-api="{{ url('/api/public/departments') }}"
>
  <div class="ptp-head">
    <div>
      <h1 class="ptp-title"><i class="fa-solid fa-award"></i>Program Toppers</h1>
      <div class="ptp-sub" id="ptpSub">Meet our top achievers.</div>
    </div>

    <div class="ptp-tools">
      <div class="ptp-search">
        <i class="fa fa-magnifying-glass"></i>
        <input id="ptpSearch" type="search" placeholder="Search (name, department, program, year, rank, score)…">
      </div>

      <div class="ptp-select" title="Filter by department">
        <i class="fa-solid fa-building-columns ptp-select__icon"></i>
        <select id="ptpDept" aria-label="Filter by department">
          <option value="">All Departments</option>
        </select>
        <i class="fa-solid fa-chevron-down ptp-select__caret"></i>
      </div>
    </div>
  </div>

  <div id="ptpGrid" class="ptp-grid" style="display:none;"></div>

  <div id="ptpSkeleton" class="ptp-skeleton"></div>
  <div id="ptpState" class="ptp-state" style="display:none;"></div>

  <div class="ptp-pagination">
    <div id="ptpPager" class="ptp-pager" style="display:none;"></div>
  </div>
</div>

{{-- Modal --}}
<div id="ptpModal" class="ptp-modal" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="ptp-modal__overlay" data-close="1"></div>
  <div class="ptp-modal__panel" role="document" aria-label="Program topper details">
    <button class="ptp-modal__close" type="button" aria-label="Close" data-close="1">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <div class="ptp-modal__hero" id="ptpModalHero"></div>
    <div class="ptp-modal__details" id="ptpModalDetails"></div>
  </div>
</div>

<script>
(() => {
  if (window.__PUBLIC_PROGRAM_TOPPERS_VIEWALL__) return;
  window.__PUBLIC_PROGRAM_TOPPERS_VIEWALL__ = true;

  const root = document.querySelector('.ptp-wrap');
  if (!root) return;

  const API = root.getAttribute('data-api') || '/api/program-toppers';
  const DEPT_API = root.getAttribute('data-dept-api') || '/api/public/departments';

  const APP_URL = @json(url('/'));
  const ORIGIN = (APP_URL || window.location.origin || '').toString().replace(/\/+$/,'');

  const $ = (id) => document.getElementById(id);

  const els = {
    grid: $('ptpGrid'),
    skel: $('ptpSkeleton'),
    state: $('ptpState'),
    pager: $('ptpPager'),
    search: $('ptpSearch'),
    dept: $('ptpDept'),
    sub: $('ptpSub'),
    modal: $('ptpModal'),
    modalHero: $('ptpModalHero'),
    modalDetails: $('ptpModalDetails'),
  };

  const state = {
    page: 1,
    perPage: 12,
    lastPage: 1,
    q: '',
    deptUuid: '',
    deptId: null,
    deptName: '',
  };

  // cache
  let allToppers = null;
  let topperByKey = new Map();
  let deptByUuid = new Map();
  let deptBySlug = new Map();

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }
  function escAttr(str){
    return (str ?? '').toString().replace(/"/g, '&quot;');
  }

  function looksLikeUuidLoose(v){
    return /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(String(v || '').trim());
  }

  function normalizeUrl(url){
    const u = (url || '').toString().trim();
    if (!u) return '';
    if (/^(data:|blob:|https?:\/\/)/i.test(u)) return u;
    if (u.startsWith('/')) return ORIGIN + u;
    return ORIGIN + '/' + u;
  }

  function pick(obj, keys){
    for (const k of keys){
      const v = obj?.[k];
      if (v !== null && v !== undefined && String(v).trim() !== '') return v;
    }
    return '';
  }

  function initials(name){
    const n = (name || '').trim();
    if (!n) return 'TP';
    const parts = n.split(/\s+/).filter(Boolean).slice(0,2);
    return parts.map(p => p[0].toUpperCase()).join('');
  }

  // ====== Resolvers (very tolerant) ======
  function resolveKey(item){
    const u = String(pick(item, ['uuid','topper_uuid','program_topper_uuid']) || '').trim();
    if (looksLikeUuidLoose(u)) return u;
    const id = String(pick(item, ['id','topper_id','program_topper_id']) || '').trim();
    return id ? ('id:' + id) : '';
  }

  function resolveName(item){
    return String(
      pick(item, ['user_name','topper_name','name','full_name','student_name']) ||
      pick(item?.user, ['name','full_name','username']) ||
      'Topper'
    );
  }

  function resolveDepartmentName(item){
    return String(
      pick(item, ['department_title','department_name']) ||
      pick(item?.department, ['title','name']) ||
      ''
    );
  }
  function resolveDepartmentId(item){
    const did =
      pick(item, ['department_id','dept_id']) ||
      pick(item?.department, ['id']) || '';
    return (did === null || did === undefined) ? '' : String(did);
  }
  function resolveDepartmentUuid(item){
    const du =
      pick(item, ['department_uuid','dept_uuid']) ||
      pick(item?.department, ['uuid']) || '';
    return (du === null || du === undefined) ? '' : String(du);
  }

  function resolveProgram(item){
    return String(
      pick(item, ['program','degree','course']) ||
      pick(item?.metadata, ['program','degree']) ||
      ''
    );
  }
  function resolveSpecialization(item){
    return String(
      pick(item, ['specialization','branch','stream']) ||
      pick(item?.metadata, ['specialization','branch']) ||
      ''
    );
  }
  function resolveAdmissionYear(item){
    const y = pick(item, ['admission_year','start_year','joining_year','batch_start_year']) ||
              pick(item?.metadata, ['admission_year','start_year']) || '';
    return (y === null || y === undefined) ? '' : String(y);
  }
  function resolvePassingYear(item){
    const y = pick(item, ['passing_year','passout_year','graduation_year','completion_year','batch_end_year']) ||
              pick(item?.metadata, ['passing_year','passout_year']) || '';
    return (y === null || y === undefined) ? '' : String(y);
  }

  function resolveRank(item){
    const r = pick(item, ['rank','position','top_rank','merit_rank']) || pick(item?.metadata, ['rank','position']) || '';
    return (r === null || r === undefined) ? '' : String(r);
  }

  function resolveScore(item){
    return String(
      pick(item, ['cgpa','gpa','percentage','score','marks','result']) ||
      pick(item?.metadata, ['cgpa','percentage','score']) ||
      ''
    );
  }

  function resolveAchievement(item){
    return String(
      pick(item, ['achievement','achievements','award','title','note','about','bio','summary']) ||
      pick(item?.metadata, ['achievement','achievements','award','note','about']) ||
      ''
    );
  }

  function resolveSkills(item){
    const s = item?.metadata?.skills;
    if (Array.isArray(s)) return s.map(x => String(x || '').trim()).filter(Boolean);
    const s2 = pick(item, ['skills']) || pick(item?.metadata, ['skills']) || '';
    if (Array.isArray(s2)) return s2.map(x => String(x || '').trim()).filter(Boolean);
    if (typeof s2 === 'string' && s2.trim()){
      return s2.split(',').map(x => x.trim()).filter(Boolean);
    }
    return [];
  }

  function resolveCompany(item){
    return String(
      pick(item, ['current_company','company','company_name']) ||
      pick(item?.metadata, ['company','current_company']) ||
      ''
    );
  }
  function resolveRoleTitle(item){
    return String(
      pick(item, ['current_role_title','current_role','designation','role_title','job_title','position_title']) ||
      pick(item?.metadata, ['role_title','designation']) ||
      ''
    );
  }

  function resolveImage(item){
    const img =
      pick(item, ['user_image','image','image_url','photo_url','profile_image_url','avatar_url']) ||
      pick(item?.user, ['image','photo_url','image_url']) ||
      '';
    return normalizeUrl(img);
  }

  function toItems(js){
    if (Array.isArray(js?.data)) return js.data;
    if (Array.isArray(js?.items)) return js.items;
    if (Array.isArray(js)) return js;
    if (Array.isArray(js?.data?.items)) return js.data.items;
    if (Array.isArray(js?.data?.data)) return js.data.data;
    return [];
  }

  function showSkeleton(){
    const sk = els.skel, st = els.state, grid = els.grid, pager = els.pager;
    if (grid) grid.style.display = 'none';
    if (pager) pager.style.display = 'none';
    if (st) st.style.display = 'none';

    if (!sk) return;
    sk.style.display = '';
    sk.innerHTML = Array.from({length: 12}).map(() => `<div class="ptp-sk"></div>`).join('');
  }
  function hideSkeleton(){
    const sk = els.skel;
    if (!sk) return;
    sk.style.display = 'none';
    sk.innerHTML = '';
  }

  function extractDeptUuidFromUrl(){
    const hay = (window.location.search || '') + ' ' + (window.location.href || '');
    const m = hay.match(/d-([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/i);
    return m ? m[1] : '';
  }
  function readDeptSlugFromPath(){
    const parts = window.location.pathname.split('/').filter(Boolean);
    const idx = parts.findIndex(p => p.toLowerCase() === 'program-toppers' || p.toLowerCase() === 'toppers');
    if (idx > 0) return parts[idx - 1];
    return '';
  }

  function setDeptSelection(uuid){
    const sel = els.dept;
    uuid = (uuid || '').toString().trim();
    if (!sel) return;

    if (!uuid){
      sel.value = '';
      state.deptUuid = '';
      state.deptId = null;
      state.deptName = '';
      if (els.sub) els.sub.textContent = 'Meet our top achievers.';
      return;
    }

    const meta = deptByUuid.get(uuid);
    if (!meta) return;

    sel.value = uuid;
    state.deptUuid = uuid;
    state.deptId = meta.id ?? null;
    state.deptName = meta.title ?? '';

    if (els.sub){
      els.sub.textContent = state.deptName
        ? ('Program toppers of ' + state.deptName)
        : 'Program toppers (filtered)';
    }
  }

  async function loadDepartments(){
    const sel = els.dept;
    if (!sel) return;

    sel.innerHTML = `
      <option value="">All Departments</option>
      <option value="__loading" disabled>Loading departments…</option>
    `;
    sel.value = '__loading';

    try{
      const res = await fetch(DEPT_API, { headers: { 'Accept':'application/json' } });
      const js = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(js?.message || ('HTTP ' + res.status));

      const items = Array.isArray(js?.data) ? js.data : [];
      const depts = items
        .map(d => ({
          id: d?.id ?? null,
          uuid: (d?.uuid ?? '').toString().trim(),
          slug: (d?.slug ?? '').toString().trim(),
          title: (d?.title ?? d?.name ?? '').toString().trim(),
          active: (d?.active ?? 1),
        }))
        .filter(x => x.uuid && x.title && String(x.active) === '1');

      deptByUuid = new Map(depts.map(d => [d.uuid, d]));
      deptBySlug = new Map(depts.filter(d => d.slug).map(d => [d.slug, d.uuid]));

      depts.sort((a,b) => a.title.localeCompare(b.title));

      sel.innerHTML = `<option value="">All Departments</option>` + depts
        .map(d => `<option value="${escAttr(d.uuid)}" data-id="${escAttr(d.id ?? '')}">${esc(d.title)}</option>`)
        .join('');

      sel.value = '';
    } catch (e){
      console.warn('Departments load failed:', e);
      sel.innerHTML = `<option value="">All Departments</option>`;
      sel.value = '';
    }
  }

  async function fetchJson(url){
    const res = await fetch(url, { headers: { 'Accept':'application/json' } });
    const txt = await res.text();
    let js = {};
    try{ js = txt ? JSON.parse(txt) : {}; }catch(_){ js = {}; }
    if (!res.ok) throw new Error(js?.message || ('Request failed: ' + res.status));
    return js;
  }

  async function ensureToppersLoaded(force=false){
    if (allToppers && !force) return;

    showSkeleton();

    try{
      let js = null;

      // best-effort query params (safe if backend ignores)
      try{
        const u = new URL(API, window.location.origin);
        u.searchParams.set('status', 'active');
        u.searchParams.set('per_page', '500');
        u.searchParams.set('sort', 'created_at');
        u.searchParams.set('direction', 'desc');
        js = await fetchJson(u.toString());
      }catch(_){
        js = await fetchJson(API);
      }

      allToppers = Array.isArray(toItems(js)) ? toItems(js) : [];

      topperByKey = new Map();
      for (const it of allToppers){
        const k = resolveKey(it);
        if (k) topperByKey.set(k, it);
      }

    } catch (e){
      console.warn('Program toppers load failed:', e);
      allToppers = [];
      topperByKey = new Map();

      if (els.state){
        els.state.style.display = '';
        els.state.innerHTML = `
          <div style="font-size:34px;opacity:.6;margin-bottom:6px;"><i class="fa-regular fa-circle-xmark"></i></div>
          Unable to load program toppers right now.
          <div style="margin-top:8px;font-size:12.5px;opacity:.95;">
            <b>Error:</b> ${esc(e?.message || 'Unknown error')}
          </div>
        `;
      }
    } finally {
      hideSkeleton();
    }
  }

  function applyFilterAndSearch(){
    const q = (state.q || '').toString().trim().toLowerCase();
    let items = Array.isArray(allToppers) ? allToppers.slice() : [];

    // department filter (by id or uuid)
    if (state.deptUuid && state.deptId !== null && state.deptId !== undefined && String(state.deptId) !== ''){
      const deptIdStr = String(state.deptId);
      const deptUuidStr = String(state.deptUuid);

      items = items.filter(it => {
        const did = resolveDepartmentId(it);
        const duu = resolveDepartmentUuid(it);
        return (did && did === deptIdStr) || (duu && duu === deptUuidStr);
      });
    } else if (state.deptUuid){
      const deptUuidStr = String(state.deptUuid);
      items = items.filter(it => String(resolveDepartmentUuid(it) || '') === deptUuidStr);
    }

    if (q){
      items = items.filter(it => {
        const name = resolveName(it).toLowerCase();
        const dept = resolveDepartmentName(it).toLowerCase();
        const program = resolveProgram(it).toLowerCase();
        const spec = resolveSpecialization(it).toLowerCase();
        const ay = resolveAdmissionYear(it).toLowerCase();
        const py = resolvePassingYear(it).toLowerCase();
        const rank = resolveRank(it).toLowerCase();
        const score = resolveScore(it).toLowerCase();
        const ach = resolveAchievement(it).toLowerCase();
        const company = resolveCompany(it).toLowerCase();
        const role = resolveRoleTitle(it).toLowerCase();
        return (
          name.includes(q) ||
          dept.includes(q) ||
          program.includes(q) ||
          spec.includes(q) ||
          ay.includes(q) ||
          py.includes(q) ||
          rank.includes(q) ||
          score.includes(q) ||
          ach.includes(q) ||
          company.includes(q) ||
          role.includes(q)
        );
      });
    }

    // optional: prefer rank ascending if available
    items.sort((a,b) => {
      const ra = parseFloat(resolveRank(a));
      const rb = parseFloat(resolveRank(b));
      const aHas = Number.isFinite(ra);
      const bHas = Number.isFinite(rb);
      if (aHas && bHas) return ra - rb;
      if (aHas && !bHas) return -1;
      if (!aHas && bHas) return 1;
      return 0;
    });

    return items;
  }

  function cardHtml(it){
    const name = resolveName(it);
    const deptName = resolveDepartmentName(it);
    const program = resolveProgram(it);
    const spec = resolveSpecialization(it);
    const admissionYear = resolveAdmissionYear(it);
    const passingYear = resolvePassingYear(it);
    const rank = resolveRank(it);
    const score = resolveScore(it);
    const img = resolveImage(it);

    const key = resolveKey(it);

    const deptPill = deptName ? `<div class="ptp-pill" title="${escAttr(deptName)}">${esc(deptName)}</div>` : '';
    const rankPill = rank ? `<div class="ptp-rank" title="Rank">${esc('Rank ' + rank)}</div>` : '';

    const metaLine =
      (program || spec)
        ? `<p class="ptp-meta">${esc(program || 'Program')}${program && spec ? `<span class="dot">•</span>` : ''}${esc(spec || '')}</p>`
        : deptName
          ? `<p class="ptp-meta">${esc(deptName)}</p>`
          : `<p class="ptp-meta">Program Topper</p>`;

    const subParts = [];
    if (admissionYear) subParts.push(`Batch: ${esc(admissionYear)}${passingYear ? '–' + esc(passingYear) : ''}`);
    else if (passingYear) subParts.push(`Passout: ${esc(passingYear)}`);
    if (score) subParts.push(`${esc(score)}`);
    const subLine = subParts.length ? `<p class="ptp-submeta">${subParts.join(`<span class="dot">•</span>`)}</p>` : '';

    const inner = !img
      ? `
        <div class="ptp-placeholder">
          <div class="ptp-initials">${esc(initials(name))}</div>
        </div>
      `
      : `<div class="bg" style="background-image:url('${escAttr(img)}')"></div>`;

    return `
      <a class="ptp-card"
         href="#"
         data-key="${escAttr(key)}"
         role="button"
         aria-label="${escAttr(name)} details (opens modal)">
        ${inner}
        ${deptPill}
        ${rankPill}
        <div class="vignette"></div>
        <div class="info">
          <p class="ptp-name">${esc(name)}</p>
          ${metaLine}
          ${subLine}
        </div>
      </a>
    `;
  }

  function render(items){
    const grid = els.grid, st = els.state;
    if (!grid || !st) return;

    if (!items.length){
      grid.style.display = 'none';
      st.style.display = '';
      const deptLine = state.deptName ? `<div style="margin-top:6px;font-size:12.5px;opacity:.95;">Department: <b>${esc(state.deptName)}</b></div>` : '';
      st.innerHTML = `
        <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
          <i class="fa-regular fa-face-frown"></i>
        </div>
        No program toppers found.
        ${deptLine}
      `;
      return;
    }

    st.style.display = 'none';
    grid.style.display = '';
    grid.innerHTML = items.map(cardHtml).join('');
  }

  function renderPager(){
    const pager = els.pager;
    if (!pager) return;

    const last = state.lastPage || 1;
    const cur  = state.page || 1;

    if (last <= 1){
      pager.style.display = 'none';
      pager.innerHTML = '';
      return;
    }

    const btn = (label, page, {disabled=false, active=false}={}) => {
      const dis = disabled ? 'disabled' : '';
      const cls = active ? 'ptp-pagebtn active' : 'ptp-pagebtn';
      return `<button class="${cls}" ${dis} data-page="${page}">${label}</button>`;
    };

    let html = '';
    html += btn('Previous', Math.max(1, cur-1), { disabled: cur<=1 });

    const win = 2;
    const start = Math.max(1, cur - win);
    const end   = Math.min(last, cur + win);

    if (start > 1){
      html += btn('1', 1, { active: cur===1 });
      if (start > 2) html += `<span style="opacity:.6;padding:0 4px;">…</span>`;
    }

    for (let p=start; p<=end; p++){
      html += btn(String(p), p, { active: p===cur });
    }

    if (end < last){
      if (end < last - 1) html += `<span style="opacity:.6;padding:0 4px;">…</span>`;
      html += btn(String(last), last, { active: cur===last });
    }

    html += btn('Next', Math.min(last, cur+1), { disabled: cur>=last });

    pager.innerHTML = html;
    pager.style.display = 'flex';
  }

  function repaint(){
    const filtered = applyFilterAndSearch();

    state.lastPage = Math.max(1, Math.ceil(filtered.length / state.perPage));
    if (state.page > state.lastPage) state.page = state.lastPage;

    const start = (state.page - 1) * state.perPage;
    const pageItems = filtered.slice(start, start + state.perPage);

    render(pageItems);
    renderPager();
  }

  // ===== Modal helpers =====
  let lastFocusEl = null;

  function closeModal(){
    const m = els.modal;
    if (!m) return;
    m.classList.remove('show');
    m.setAttribute('aria-hidden','true');
    document.documentElement.style.overflow = '';
    document.body.style.overflow = '';

    if (lastFocusEl && typeof lastFocusEl.focus === 'function') {
      try{ lastFocusEl.focus(); }catch(_){}
    }
    lastFocusEl = null;
  }

  function mFieldHtml(label, iconClass, value){
    const v = (value ?? '').toString().trim();
    if (!v) return '';
    return `
      <div class="ptp-mfield">
        <div class="ptp-mfield-label"><i class="${escAttr(iconClass)}"></i>${esc(label)}</div>
        <div class="ptp-mfield-val">${esc(v)}</div>
      </div>
    `;
  }

  function mChipsHtml(label, iconClass, arr){
    const items = Array.isArray(arr) ? arr.map(x => String(x||'').trim()).filter(Boolean) : [];
    if (!items.length) return '';
    return `
      <div class="ptp-mfield ptp-mfield-full">
        <div class="ptp-mfield-label"><i class="${escAttr(iconClass)}"></i>${esc(label)}</div>
        <div class="ptp-mfield-val">
          <div class="ptp-mchips">
            ${items.map(s => `<span class="ptp-mchip"><i class="fa-solid fa-check"></i>${esc(s)}</span>`).join('')}
          </div>
        </div>
      </div>
    `;
  }

  function openModalForItem(it){
    if (!els.modal || !els.modalHero || !els.modalDetails) return;

    const name = resolveName(it);
    const dept = resolveDepartmentName(it);
    const program = resolveProgram(it);
    const spec = resolveSpecialization(it);
    const admissionYear = resolveAdmissionYear(it);
    const passingYear = resolvePassingYear(it);
    const rank = resolveRank(it);
    const score = resolveScore(it);
    const ach = resolveAchievement(it);
    const skills = resolveSkills(it);

    const company = resolveCompany(it);
    const role = resolveRoleTitle(it);

    const img = resolveImage(it);

    // HERO subtitle
    const heroSub = [];
    if (rank) heroSub.push('Rank ' + rank);
    if (program) heroSub.push(program + (spec ? (' · ' + spec) : ''));
    if (dept) heroSub.push(dept);
    const batch = admissionYear ? (admissionYear + (passingYear ? ('–' + passingYear) : '')) : (passingYear || '');
    if (batch) heroSub.push('Batch ' + batch);

    const heroSubHtml = heroSub.length
      ? heroSub.map(s => `<span>${esc(s)}</span>`).join('<span class="sep">•</span>')
      : '';

    const quoteText = ach || (score ? ('Score: ' + score) : '');
    const quoteHtml = quoteText
      ? `<div class="ptp-modal__hero-quote">"${esc(quoteText)}"</div>`
      : '';

    if (img) {
      els.modalHero.innerHTML = `
        <img class="ptp-modal__hero-img" src="${escAttr(img)}" alt="${escAttr(name)} photo">
        <div class="ptp-modal__hero-grad"></div>
        <div class="ptp-modal__hero-info">
          <h2 class="ptp-modal__hero-name">${esc(name)}</h2>
          ${heroSubHtml ? `<div class="ptp-modal__hero-sub">${heroSubHtml}</div>` : ''}
          ${quoteHtml}
        </div>
      `;
    } else {
      els.modalHero.innerHTML = `
        <div class="ptp-modal__hero-placeholder">
          <div class="ptp-modal__hero-initials">${esc(initials(name))}</div>
        </div>
        <div class="ptp-modal__hero-grad"></div>
        <div class="ptp-modal__hero-info">
          <h2 class="ptp-modal__hero-name">${esc(name)}</h2>
          ${heroSubHtml ? `<div class="ptp-modal__hero-sub">${heroSubHtml}</div>` : ''}
          ${quoteHtml}
        </div>
      `;
    }

    // right header tagline
    const tagParts = [];
    if (dept) tagParts.push(dept);
    if (program) tagParts.push(program + (spec ? (' · ' + spec) : ''));
    if (rank) tagParts.push('Rank ' + rank);
    if (score) tagParts.push(score);

    const tagHtml = tagParts.length
      ? tagParts.map(t => `<span>${esc(t)}</span>`).join('<span class="tsep">•</span>')
      : '';

    // Academic fields (main for toppers)
    const acadFields = [
      mFieldHtml('Program', 'fa-solid fa-graduation-cap', program),
      mFieldHtml('Specialization', 'fa-solid fa-diagram-project', spec),
      mFieldHtml('Batch Start', 'fa-solid fa-calendar-day', admissionYear),
      mFieldHtml('Batch End', 'fa-solid fa-calendar-check', passingYear),
      mFieldHtml('Rank', 'fa-solid fa-award', rank ? ('Rank ' + rank) : ''),
      mFieldHtml('Score', 'fa-solid fa-chart-line', score),
    ].filter(Boolean);

    // Professional (optional)
    const profFields = [
      mFieldHtml('Company', 'fa-solid fa-building', company),
      mFieldHtml('Role / Designation', 'fa-solid fa-briefcase', role),
    ].filter(Boolean);

    const skillsHtml = mChipsHtml('Skills', 'fa-solid fa-wand-magic-sparkles', skills);

    const noteHtml = ach ? `
      <div class="ptp-mnote">
        <div class="ptp-mnote-label"><i class="fa-solid fa-star"></i>Highlight</div>
        <div class="ptp-mnote-val">${esc(ach)}</div>
      </div>
    ` : '';

    let detailsHtml = `
      <div class="ptp-modal__dhead">
        <h2 class="ptp-modal__dhead-name">${esc(name)}</h2>
        ${tagHtml ? `<div class="ptp-modal__dhead-tagline">${tagHtml}</div>` : ''}
      </div>
      <div class="ptp-modal__dcontent">
    `;

    if (acadFields.length){
      detailsHtml += `
        <div class="ptp-msection">
          <div class="ptp-msection-title">Academic</div>
          <div class="ptp-mrow">${acadFields.join('')}</div>
        </div>
      `;
    }

    if (skillsHtml){
      detailsHtml += `
        <div class="ptp-msection">
          <div class="ptp-msection-title">Skills</div>
          <div class="ptp-mrow">${skillsHtml}</div>
        </div>
      `;
    }

    if (profFields.length){
      detailsHtml += `
        <div class="ptp-msection">
          <div class="ptp-msection-title">Professional</div>
          <div class="ptp-mrow">${profFields.join('')}</div>
        </div>
      `;
    }

    if (noteHtml){
      detailsHtml += `
        <div class="ptp-msection">
          ${noteHtml}
        </div>
      `;
    }

    detailsHtml += `</div>`; // close dcontent
    els.modalDetails.innerHTML = detailsHtml;

    // show modal
    lastFocusEl = document.activeElement;
    els.modal.classList.add('show');
    els.modal.setAttribute('aria-hidden','false');
    document.documentElement.style.overflow = 'hidden';
    document.body.style.overflow = 'hidden';

    const closeBtn = els.modal.querySelector('button[data-close="1"]');
    if (closeBtn) closeBtn.focus();
  }

  function onCardClick(e){
    const card = e.target.closest('.ptp-card');
    if (!card) return;

    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

    e.preventDefault();

    const key = (card.getAttribute('data-key') || '').trim();
    if (!key) return;

    const it = topperByKey.get(key);
    if (!it) return;

    openModalForItem(it);
  }

  function onModalClick(e){
    const close = e.target.closest('[data-close="1"]');
    if (!close) return;
    e.preventDefault();
    closeModal();
  }

  function onEsc(e){
    if (e.key === 'Escape' && els.modal && els.modal.classList.contains('show')){
      e.preventDefault();
      closeModal();
    }
  }

  document.addEventListener('DOMContentLoaded', async () => {
    await loadDepartments();

    // dept deep-link: ?d-<uuid>  OR slug in path (same pattern as alumni page)
    const deepDeptUuid = extractDeptUuidFromUrl();
    if (deepDeptUuid && deptByUuid.has(deepDeptUuid)){
      setDeptSelection(deepDeptUuid);
    } else {
      const slug = readDeptSlugFromPath();
      const uuidFromSlug = slug ? (deptBySlug.get(slug) || '') : '';
      if (uuidFromSlug && deptByUuid.has(uuidFromSlug)){
        setDeptSelection(uuidFromSlug);
      } else {
        setDeptSelection('');
      }
    }

    await ensureToppersLoaded(false);
    repaint();

    document.addEventListener('click', onCardClick);

    els.modal && els.modal.addEventListener('click', onModalClick);
    document.addEventListener('keydown', onEsc);

    let t = null;
    els.search && els.search.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(() => {
        state.q = (els.search.value || '').trim();
        state.page = 1;
        repaint();
      }, 260);
    });

    els.dept && els.dept.addEventListener('change', () => {
      const v = (els.dept.value || '').toString();
      if (v === '__loading') return;

      if (!v){
        setDeptSelection('');
      } else {
        setDeptSelection(v);
      }

      state.page = 1;
      repaint();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    document.addEventListener('click', (e) => {
      const b = e.target.closest('button.ptp-pagebtn[data-page]');
      if (!b) return;
      const p = parseInt(b.dataset.page, 10);
      if (!p || Number.isNaN(p) || p === state.page) return;
      state.page = p;
      repaint();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

})();
</script>
