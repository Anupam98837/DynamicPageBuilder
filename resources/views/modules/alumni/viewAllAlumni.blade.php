{{-- resources/views/landing/viewAllAlumni.blade.php --}}

{{-- (optional) FontAwesome for icons used below; remove if already included in header --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

<style>
.alx-wrap{
  /* scoped tokens */
  --alx-brand: var(--primary-color, #9E363A);
  --alx-ink: #0f172a;
  --alx-muted: #64748b;
  --alx-bg: var(--page-bg, #ffffff);
  --alx-card: var(--surface, #ffffff);
  --alx-line: var(--line-soft, rgba(15,23,42,.10));
  --alx-shadow: 0 10px 24px rgba(2,6,23,.08);

  /* fixed card sizing */
  --alx-card-w: 247px;
  --alx-card-h: 329px;
  --alx-radius: 18px;

  max-width: 1320px;
  margin: 18px auto 54px;
  padding: 0 12px;
  background: transparent;
  position: relative;
  overflow: visible;
}

/* Header */
.alx-head{
  background: var(--alx-card);
  border: 1px solid var(--alx-line);
  border-radius: 16px;
  box-shadow: var(--alx-shadow);
  padding: 14px 16px;
  margin-bottom: 16px;

  display:flex;
  gap: 12px;
  align-items:center;
  justify-content:space-between;

  /* ✅ keep one row (desktop) */

}
.alx-title{
  margin:0;
  font-weight: 950;
  letter-spacing: .2px;
  color: var(--alx-ink);
  font-size: 28px;
  display:flex;
  align-items:center;
  gap: 10px;
  white-space: nowrap;
}
.alx-title i{ color: var(--alx-brand); }
.alx-sub{
  margin: 6px 0 0;
  color: var(--alx-muted);
  font-size: 14px;
}

.alx-tools{
  display:flex;
  gap: 10px;
  align-items:center;

  /* ✅ keep one row (desktop) */
  flex-wrap: nowrap;
}

/* Search */
.alx-search{
  position: relative;
  min-width: 260px;
  max-width: 520px;
  flex: 1 1 320px;
}
.alx-search i{
  position:absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  opacity: .65;
  color: var(--alx-muted);
  pointer-events:none;
}
.alx-search input{
  width:100%;
  height: 42px;
  border-radius: 999px;
  padding: 11px 12px 11px 42px;
  border: 1px solid var(--alx-line);
  background: var(--alx-card);
  color: var(--alx-ink);
  outline: none;
}
.alx-search input:focus{
  border-color: rgba(201,75,80,.55);
  box-shadow: 0 0 0 4px rgba(201,75,80,.18);
}

/* Dept dropdown */
.alx-select{
  position: relative;
  min-width: 260px;
  max-width: 360px;
  flex: 0 1 320px;
}
.alx-select__icon{
  position:absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  opacity: .70;
  color: var(--alx-muted);
  pointer-events:none;
  font-size: 14px;
}
.alx-select__caret{
  position:absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  opacity: .70;
  color: var(--alx-muted);
  pointer-events:none;
  font-size: 12px;
}
.alx-select select{
  width: 100%;
  height: 42px;
  border-radius: 999px;
  padding: 10px 38px 10px 42px;
  border: 1px solid var(--alx-line);
  background: var(--alx-card);
  color: var(--alx-ink);
  outline: none;

  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
}
.alx-select select:focus{
  border-color: rgba(201,75,80,.55);
  box-shadow: 0 0 0 4px rgba(201,75,80,.18);
}

/* Grid */
.alx-grid{
  display:grid;
  grid-template-columns: repeat(auto-fill, var(--alx-card-w));
  gap: 18px;
  align-items: start;
  justify-content: center;
}

/* Card */
.alx-card{
  position: relative;
  width: var(--alx-card-w);
  height: var(--alx-card-h);
  border-radius: var(--alx-radius);
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
.alx-card:hover{
  transform: translateY(-4px);
  box-shadow: 0 18px 42px rgba(0,0,0,.16);
  border-color: rgba(158,54,58,.22);
}

/* image as bg */
.alx-card .bg{
  position:absolute; inset:0;
  background-size: cover;
  background-position: center;
  filter: saturate(1.02);
  transform: scale(1.0001);
}

/* vignette */
.alx-card .vignette{
  position:absolute; inset:0;
  background:
    radial-gradient(1200px 500px at 50% -20%, rgba(255,255,255,.10), rgba(0,0,0,0) 60%),
    linear-gradient(180deg, rgba(0,0,0,.00) 28%, rgba(0,0,0,.12) 60%, rgba(0,0,0,.62) 100%);
}

/* bottom overlay text */
.alx-card .info{
  position:absolute;
  left: 14px;
  right: 14px;
  bottom: 14px;
  z-index: 2;
}
.alx-name{
  margin:0;
  font-size: 18px;
  font-weight: 950;
  line-height: 1.12;
  color: #fff;
  text-shadow: 0 6px 16px rgba(0,0,0,.35);
}
.alx-meta{
  margin: 7px 0 0;
  font-size: 13px;
  font-weight: 800;
  color: rgba(255,255,255,.90);
  text-shadow: 0 6px 16px rgba(0,0,0,.35);
  line-height: 1.2;
}
.alx-meta .dot{
  opacity: .85;
  padding: 0 6px;
}
.alx-submeta{
  margin: 7px 0 0;
  font-size: 12.5px;
  color: rgba(255,255,255,.82);
  text-shadow: 0 6px 16px rgba(0,0,0,.35);
  line-height: 1.2;
}

/* top "pill" (department) */
.alx-pill{
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

/* Placeholder */
.alx-placeholder{
  position:absolute; inset:0;
  display:grid; place-items:center;
  background:
    radial-gradient(800px 360px at 20% 10%, rgba(158,54,58,.20), transparent 60%),
    radial-gradient(900px 400px at 80% 90%, rgba(158,54,58,.14), transparent 60%),
    linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,255,255,.82));
}
.alx-initials{
  width: 86px; height: 86px;
  border-radius: 24px;
  display:grid; place-items:center;
  font-weight: 950;
  font-size: 28px;
  color: var(--alx-brand);
  background: rgba(158,54,58,.12);
  border: 1px solid rgba(158,54,58,.25);
}

/* State / empty */
.alx-state{
  background: var(--alx-card);
  border: 1px solid var(--alx-line);
  border-radius: 16px;
  box-shadow: var(--alx-shadow);
  padding: 18px;
  color: var(--alx-muted);
  text-align:center;
}

/* Skeleton */
.alx-skeleton{
  display:grid;
  grid-template-columns: repeat(auto-fill, var(--alx-card-w));
  gap: 18px;
  justify-content: center;
}
.alx-sk{
  border-radius: var(--alx-radius);
  border: 1px solid var(--alx-line);
  background: #fff;
  overflow:hidden;
  position:relative;
  box-shadow: 0 10px 24px rgba(2,6,23,.08);
  height: var(--alx-card-h);
}
.alx-sk:before{
  content:'';
  position:absolute; inset:0;
  transform: translateX(-60%);
  background: linear-gradient(90deg, transparent, rgba(148,163,184,.22), transparent);
  animation: alxSkMove 1.15s ease-in-out infinite;
}
@keyframes alxSkMove{ to{ transform: translateX(60%);} }

/* Pagination */
.alx-pagination{
  display:flex;
  justify-content:center;
  margin-top: 18px;
}
.alx-pagination .alx-pager{
  display:flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items:center;
  justify-content:center;
  padding: 10px;
}
.alx-pagebtn{
  border:1px solid var(--alx-line);
  background: var(--alx-card);
  color: var(--alx-ink);
  border-radius: 12px;
  padding: 9px 12px;
  font-size: 13px;
  font-weight: 950;
  box-shadow: 0 8px 18px rgba(2,6,23,.06);
  cursor:pointer;
  user-select:none;
}
.alx-pagebtn:hover{ background: rgba(2,6,23,.03); }
.alx-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
.alx-pagebtn.active{
  background: rgba(201,75,80,.12);
  border-color: rgba(201,75,80,.35);
  color: var(--alx-brand);
}

/* ✅ allow wrapping on smaller screens */
@media (max-width: 992px){
  .alx-head{ flex-wrap: wrap; align-items: flex-end; }
  .alx-tools{ flex-wrap: wrap; }
}
@media (max-width: 640px){
  .alx-title{ font-size: 24px; }
  .alx-search{ min-width: 220px; flex: 1 1 240px; }
  .alx-select{ min-width: 220px; flex: 1 1 240px; }
}

/* ===== REDESIGNED Modal ===== */
.alx-modal{
  position: fixed;
  inset: 0;
  display:none;
  z-index: 9999;
}
.alx-modal.show{ display:flex; align-items:center; justify-content:center; }

.alx-modal__overlay{
  position:fixed; inset:0;
  background: rgba(0,0,0,.72);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

.alx-modal__panel{
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
.alx-modal.show .alx-modal__panel{
  opacity: 1;
  transform: translateY(0) scale(1);
}

/* Close button — floats top-right */
.alx-modal__close{
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
.alx-modal__close:hover{
  background: #fff;
  transform: scale(1.08);
}

/* ===== Left: Hero Image ===== */
.alx-modal__hero{
  flex: 0 0 420px;
  position: relative;
  background: #1a1a2e;
  overflow: hidden;
  min-height: 520px;
}
.alx-modal__hero-img{
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center top;
  display: block;
}
/* Gradient overlay on image */
.alx-modal__hero-grad{
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
/* Text overlaid on image bottom */
.alx-modal__hero-info{
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 28px 26px;
  z-index: 3;
}
.alx-modal__hero-name{
  margin: 0;
  font-size: 30px;
  font-weight: 900;
  color: #fff;
  line-height: 1.15;
  letter-spacing: -.2px;
  text-shadow: 0 2px 20px rgba(0,0,0,.4);
}
.alx-modal__hero-sub{
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
.alx-modal__hero-sub .sep{
  opacity: .5;
  font-size: 10px;
}
.alx-modal__hero-quote{
  margin-top: 14px;
  font-size: 13.5px;
  font-style: italic;
  color: rgba(255,255,255,.78);
  line-height: 1.4;
  text-shadow: 0 2px 12px rgba(0,0,0,.35);
  max-width: 360px;
}

/* Placeholder hero (no image) */
.alx-modal__hero-placeholder{
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
.alx-modal__hero-initials{
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

/* ===== Right: Details ===== */
.alx-modal__details{
  flex: 1;
  overflow-y: auto;
  padding: 0;
  display: flex;
  flex-direction: column;
  max-height: calc(100vh - 48px);
}

/* Sticky header bar on right side */
.alx-modal__dhead{
  position: sticky;
  top: 0;
  z-index: 10;
  padding: 18px 24px 14px;
  background: rgba(255,255,255,.95);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(15,23,42,.06);
}
.alx-modal__dhead-name{
  margin: 0;
  font-size: 22px;
  font-weight: 900;
  color: #0f172a;
  line-height: 1.2;
}
.alx-modal__dhead-tagline{
  margin-top: 4px;
  font-size: 13.5px;
  font-weight: 600;
  color: #64748b;
  display: flex;
  flex-wrap: wrap;
  gap: 4px 10px;
  align-items: center;
}
.alx-modal__dhead-tagline .tsep{
  opacity: .45;
  font-size: 8px;
}

/* Scrollable content */
.alx-modal__dcontent{
  flex: 1;
  padding: 16px 24px 28px;
}

/* Section titles */
.alx-msection{
  margin-top: 20px;
}
.alx-msection:first-child{ margin-top: 0; }
.alx-msection-title{
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
.alx-mrow{
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}
.alx-mfield{
  padding: 10px 12px;
  border-radius: 12px;
  background: #f8fafc;
  border: 1px solid rgba(15,23,42,.05);
  transition: background .12s;
}
.alx-mfield:hover{ background: #f1f5f9; }
.alx-mfield-label{
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
.alx-mfield-label i{
  color: var(--alx-brand, #9E363A);
  font-size: 11px;
  opacity: .85;
}
.alx-mfield-val{
  font-size: 13.5px;
  font-weight: 700;
  color: #1e293b;
  line-height: 1.3;
  word-break: break-word;
}

/* Full width field */
.alx-mfield-full{
  grid-column: 1 / -1;
}

/* Skills chips */
.alx-mchips{
  display:flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 2px;
}
.alx-mchip{
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
.alx-mchip i{
  color: var(--alx-brand, #9E363A);
  font-size: 10px;
}

/* Note block */
.alx-mnote{
  margin-top: 8px;
  padding: 14px 16px;
  border-radius: 14px;
  background: linear-gradient(135deg, rgba(158,54,58,.04), rgba(158,54,58,.08));
  border: 1px solid rgba(158,54,58,.12);
  grid-column: 1 / -1;
}
.alx-mnote-label{
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .5px;
  color: var(--alx-brand, #9E363A);
  margin-bottom: 6px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.alx-mnote-val{
  font-size: 13.5px;
  font-weight: 600;
  color: #334155;
  line-height: 1.5;
  white-space: pre-wrap;
  font-style: italic;
}

/* ===== Modal Responsive ===== */
@media (max-width: 860px){
  .alx-modal__panel{
    flex-direction: column;
    max-height: calc(100vh - 32px);
    width: min(560px, calc(100% - 24px));
  }
  .alx-modal__hero{
    flex: 0 0 auto;
    min-height: 320px;
    max-height: 380px;
  }
  .alx-modal__details{
    max-height: none;
  }
  .alx-modal__close{
    background: rgba(0,0,0,.45);
    color: #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,.25);
  }
  .alx-modal__close:hover{
    background: rgba(0,0,0,.6);
    color: #fff;
  }
}
@media (max-width: 560px){
  .alx-modal__hero{
    min-height: 260px;
    max-height: 300px;
  }
  .alx-modal__hero-name{
    font-size: 24px;
  }
  .alx-modal__dhead{
    padding: 14px 16px 12px;
  }
  .alx-modal__dhead-name{
    font-size: 18px;
  }
  .alx-modal__dcontent{
    padding: 14px 16px 24px;
  }
  .alx-mrow{
    grid-template-columns: 1fr;
  }
}

/* ===== Dark mode ===== */
html.theme-dark .alx-modal__panel{ background: #0f172a; }
html.theme-dark .alx-modal__close{
  background: rgba(15,23,42,.85);
  color: #e2e8f0;
  box-shadow: 0 4px 16px rgba(0,0,0,.35);
}
html.theme-dark .alx-modal__close:hover{ background: rgba(15,23,42,.95); }
html.theme-dark .alx-modal__dhead{
  background: rgba(15,23,42,.95);
  border-bottom-color: rgba(255,255,255,.06);
}
html.theme-dark .alx-modal__dhead-name{ color: #f1f5f9; }
html.theme-dark .alx-modal__dhead-tagline{ color: #94a3b8; }
html.theme-dark .alx-mfield{
  background: rgba(255,255,255,.04);
  border-color: rgba(255,255,255,.06);
}
html.theme-dark .alx-mfield:hover{ background: rgba(255,255,255,.07); }
html.theme-dark .alx-mfield-label{ color: #64748b; }
html.theme-dark .alx-mfield-val{ color: #e2e8f0; }
html.theme-dark .alx-msection-title{ color: #475569; border-bottom-color: rgba(255,255,255,.06); }
html.theme-dark .alx-mchip{
  background: rgba(255,255,255,.06);
  border-color: rgba(255,255,255,.08);
  color: #cbd5e1;
}
html.theme-dark .alx-mnote{
  background: linear-gradient(135deg, rgba(158,54,58,.08), rgba(158,54,58,.14));
  border-color: rgba(158,54,58,.2);
}
html.theme-dark .alx-mnote-val{ color: #cbd5e1; }

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
  class="alx-wrap"
  data-api="{{ url('/api/alumni') }}"
  data-dept-api="{{ url('/api/public/departments') }}"
>
  <div class="alx-head">
    <div>
      <h1 class="alx-title"><i class="fa-solid fa-user-group"></i>Alumni</h1>
      <div class="alx-sub" id="alxSub">Explore our alumni community.</div>
    </div>

    <div class="alx-tools">
      <div class="alx-search">
        <i class="fa fa-magnifying-glass"></i>
        <input id="alxSearch" type="search" placeholder="Search (name, department, program, company, role, year)…">
      </div>

      <div class="alx-select" title="Filter by department">
        <i class="fa-solid fa-building-columns alx-select__icon"></i>
        <select id="alxDept" aria-label="Filter by department">
          <option value="">All Departments</option>
        </select>
        <i class="fa-solid fa-chevron-down alx-select__caret"></i>
      </div>
    </div>
  </div>

  <div id="alxGrid" class="alx-grid" style="display:none;"></div>

  <div id="alxSkeleton" class="alx-skeleton"></div>
  <div id="alxState" class="alx-state" style="display:none;"></div>

  <div class="alx-pagination">
    <div id="alxPager" class="alx-pager" style="display:none;"></div>
  </div>
</div>

{{-- Redesigned Modal --}}
<div id="alxModal" class="alx-modal" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="alx-modal__overlay" data-close="1"></div>
  <div class="alx-modal__panel" role="document" aria-label="Alumni details">
    <button class="alx-modal__close" type="button" aria-label="Close" data-close="1">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <!-- Left: Hero Image -->
    <div class="alx-modal__hero" id="alxModalHero"></div>
    <!-- Right: Details -->
    <div class="alx-modal__details" id="alxModalDetails"></div>
  </div>
</div>

<script>
(() => {
  if (window.__PUBLIC_ALUMNI_VIEWALL__) return;
  window.__PUBLIC_ALUMNI_VIEWALL__ = true;

  const root = document.querySelector('.alx-wrap');
  if (!root) return;

  const API = root.getAttribute('data-api') || '/api/alumni';
  const DEPT_API = root.getAttribute('data-dept-api') || '/api/public/departments';

  const APP_URL = @json(url('/'));
  const ORIGIN = (APP_URL || window.location.origin || '').toString().replace(/\/+$/,'');

  const $ = (id) => document.getElementById(id);

  const els = {
    grid: $('alxGrid'),
    skel: $('alxSkeleton'),
    state: $('alxState'),
    pager: $('alxPager'),
    search: $('alxSearch'),
    dept: $('alxDept'),
    sub: $('alxSub'),
    modal: $('alxModal'),
    modalHero: $('alxModalHero'),
    modalDetails: $('alxModalDetails'),
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
  let allAlumni = null;
  let alumniByKey = new Map();
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
    if (!n) return 'AL';
    const parts = n.split(/\s+/).filter(Boolean).slice(0,2);
    return parts.map(p => p[0].toUpperCase()).join('');
  }

  // ====== Alumni resolvers ======
  function resolveKey(item){
    const u = String(pick(item, ['uuid','alumni_uuid']) || '').trim();
    if (looksLikeUuidLoose(u)) return u;
    const id = String(pick(item, ['id','alumni_id']) || '').trim();
    return id ? ('id:' + id) : '';
  }

  function resolveName(item){
    return String(
      pick(item, ['user_name','alumni_name','name','full_name','student_name']) ||
      pick(item?.user, ['name','full_name','username']) ||
      'Alumni'
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
    const y = pick(item, ['admission_year','start_year','joining_year','batch_start_year']) || pick(item?.metadata, ['admission_year']) || '';
    return (y === null || y === undefined) ? '' : String(y);
  }

  function resolvePassingYear(item){
    const y = pick(item, ['passing_year','passout_year','graduation_year','completion_year','batch_end_year']) || pick(item?.metadata, ['passing_year','passout_year']) || '';
    return (y === null || y === undefined) ? '' : String(y);
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
      pick(item, ['current_role_title','current_role','designation','role_title','job_title','position']) ||
      pick(item?.metadata, ['role_title','designation']) ||
      ''
    );
  }

  function resolveIndustry(item){
    return String(pick(item, ['industry','sector']) || pick(item?.metadata, ['industry']) || '');
  }

  function resolveCity(item){
    return String(pick(item, ['city','current_city']) || pick(item?.metadata, ['city']) || '');
  }

  function resolveCountry(item){
    return String(pick(item, ['country','current_country']) || pick(item?.metadata, ['country']) || '');
  }

  function resolveSkills(item){
    const s = item?.metadata?.skills;
    if (Array.isArray(s)) return s.map(x => String(x || '').trim()).filter(Boolean);
    return [];
  }

  function resolveNote(item){
    return String(
      pick(item, ['note','about','bio','summary']) ||
      pick(item?.metadata, ['note','about']) ||
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
    sk.innerHTML = Array.from({length: 12}).map(() => `<div class="alx-sk"></div>`).join('');
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
    const idx = parts.findIndex(p => p.toLowerCase() === 'alumni');
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
      if (els.sub) els.sub.textContent = 'Explore our alumni community.';
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
        ? ('Alumni of ' + state.deptName)
        : 'Alumni (filtered)';
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

  async function ensureAlumniLoaded(force=false){
    if (allAlumni && !force) return;

    showSkeleton();

    try{
      let js = null;

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

      allAlumni = Array.isArray(toItems(js)) ? toItems(js) : [];

      alumniByKey = new Map();
      for (const it of allAlumni){
        const k = resolveKey(it);
        if (k) alumniByKey.set(k, it);
      }

    } catch (e){
      console.warn('Alumni load failed:', e);
      allAlumni = [];
      alumniByKey = new Map();

      if (els.state){
        els.state.style.display = '';
        els.state.innerHTML = `
          <div style="font-size:34px;opacity:.6;margin-bottom:6px;"><i class="fa-regular fa-circle-xmark"></i></div>
          Unable to load alumni right now.
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
    let items = Array.isArray(allAlumni) ? allAlumni.slice() : [];

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
        const company = resolveCompany(it).toLowerCase();
        const role = resolveRoleTitle(it).toLowerCase();
        const industry = resolveIndustry(it).toLowerCase();
        const city = resolveCity(it).toLowerCase();
        const country = resolveCountry(it).toLowerCase();
        return (
          name.includes(q) ||
          dept.includes(q) ||
          program.includes(q) ||
          spec.includes(q) ||
          ay.includes(q) ||
          py.includes(q) ||
          company.includes(q) ||
          role.includes(q) ||
          industry.includes(q) ||
          city.includes(q) ||
          country.includes(q)
        );
      });
    }

    return items;
  }

  function cardHtml(it){
    const name = resolveName(it);
    const deptName = resolveDepartmentName(it);
    const program = resolveProgram(it);
    const spec = resolveSpecialization(it);
    const admissionYear = resolveAdmissionYear(it);
    const passingYear = resolvePassingYear(it);
    const company = resolveCompany(it);
    const role = resolveRoleTitle(it);
    const img = resolveImage(it);

    const key = resolveKey(it);

    const pill = deptName ? `<div class="alx-pill" title="${escAttr(deptName)}">${esc(deptName)}</div>` : '';

    const metaLine =
      (company || role)
        ? `<p class="alx-meta">${esc(company || 'Company')}${company && role ? `<span class="dot">•</span>` : ''}${esc(role || '')}</p>`
        : (program || spec)
          ? `<p class="alx-meta">${esc(program || 'Program')}${program && spec ? `<span class="dot">•</span>` : ''}${esc(spec || '')}</p>`
          : `<p class="alx-meta">${deptName ? esc(deptName) : 'Alumni'}</p>`;

    const subParts = [];
    if (program && !(company || role)) subParts.push(esc(program));
    if (admissionYear) subParts.push(`Batch: ${esc(admissionYear)}${passingYear ? '–' + esc(passingYear) : ''}`);
    else if (passingYear) subParts.push(`Passout: ${esc(passingYear)}`);

    const subLine = subParts.length ? `<p class="alx-submeta">${subParts.join(`<span class="dot">•</span>`)}</p>` : '';

    const inner = !img
      ? `
        <div class="alx-placeholder">
          <div class="alx-initials">${esc(initials(name))}</div>
        </div>
      `
      : `<div class="bg" style="background-image:url('${escAttr(img)}')"></div>`;

    return `
      <a class="alx-card"
         href="#"
         data-key="${escAttr(key)}"
         role="button"
         aria-label="${escAttr(name)} details (opens modal)">
        ${inner}
        ${pill}
        <div class="vignette"></div>
        <div class="info">
          <p class="alx-name">${esc(name)}</p>
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
  <div aria-hidden="true" style="width:170px;max-width:100%;margin:0 auto 10px;display:block;color:var(--anx-brand);">
    <svg viewBox="0 0 220 140" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block;width:100%;height:auto;">
      <rect x="10" y="18" width="200" height="112" rx="16" fill="white" stroke="rgba(15,23,42,0.10)"/>
      <rect x="24" y="32" width="172" height="84" rx="12" fill="rgba(148,163,184,0.08)" stroke="rgba(148,163,184,0.18)"/>
      <circle cx="70" cy="66" r="16" fill="rgba(158,54,58,0.14)" stroke="currentColor" stroke-width="2"/>
      <path d="M49 97c5-11 16-16 21-16s16 5 21 16" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
      <rect x="100" y="52" width="72" height="8" rx="4" fill="rgba(100,116,139,0.20)"/>
      <rect x="100" y="68" width="54" height="8" rx="4" fill="rgba(100,116,139,0.16)"/>
      <rect x="100" y="84" width="64" height="8" rx="4" fill="rgba(100,116,139,0.12)"/>
      <circle cx="182" cy="26" r="12" fill="rgba(158,54,58,0.10)" stroke="currentColor" stroke-width="1.8"/>
      <path d="M177.5 26h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      <path d="M182 21.5v9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    </svg>
  </div>
  No alumni found.
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
      const cls = active ? 'alx-pagebtn active' : 'alx-pagebtn';
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
      <div class="alx-mfield">
        <div class="alx-mfield-label"><i class="${escAttr(iconClass)}"></i>${esc(label)}</div>
        <div class="alx-mfield-val">${esc(v)}</div>
      </div>
    `;
  }

  function mChipsHtml(label, iconClass, arr){
    const items = Array.isArray(arr) ? arr.map(x => String(x||'').trim()).filter(Boolean) : [];
    if (!items.length) return '';
    return `
      <div class="alx-mfield alx-mfield-full">
        <div class="alx-mfield-label"><i class="${escAttr(iconClass)}"></i>${esc(label)}</div>
        <div class="alx-mfield-val">
          <div class="alx-mchips">
            ${items.map(s => `<span class="alx-mchip"><i class="fa-solid fa-check"></i>${esc(s)}</span>`).join('')}
          </div>
        </div>
      </div>
    `;
  }

  function openModalForItem(it){
    if (!els.modal || !els.modalHero || !els.modalDetails) return;

    const name = resolveName(it);
    const dept = resolveDepartmentName(it);
    const company = resolveCompany(it);
    const role = resolveRoleTitle(it);
    const industry = resolveIndustry(it);
    const city = resolveCity(it);
    const country = resolveCountry(it);
    const location = [city, country].filter(Boolean).join(', ');
    const skills = resolveSkills(it);
    const note = resolveNote(it);
    const img = resolveImage(it);

    // === BUILD HERO (left side) ===
    const heroSub = [];
    if (role && company) heroSub.push(role + ' · ' + company + (location ? ', ' + location : ''));
    else if (company) heroSub.push(company + (location ? ', ' + location : ''));
    else if (role) heroSub.push(role + (location ? ', ' + location : ''));
    else if (location) heroSub.push(location);

    const heroSubHtml = heroSub.length
      ? heroSub.map(s => `<span>${esc(s)}</span>`).join('<span class="sep">•</span>')
      : '';

    const quoteHtml = note
      ? `<div class="alx-modal__hero-quote">"${esc(note)}"</div>`
      : '';

    if (img) {
      els.modalHero.innerHTML = `
        <img class="alx-modal__hero-img" src="${escAttr(img)}" alt="${escAttr(name)} photo">
        <div class="alx-modal__hero-grad"></div>
        <div class="alx-modal__hero-info">
          <h2 class="alx-modal__hero-name">${esc(name)}</h2>
          ${heroSubHtml ? `<div class="alx-modal__hero-sub">${heroSubHtml}</div>` : ''}
          ${quoteHtml}
        </div>
      `;
    } else {
      els.modalHero.innerHTML = `
        <div class="alx-modal__hero-placeholder">
          <div class="alx-modal__hero-initials">${esc(initials(name))}</div>
        </div>
        <div class="alx-modal__hero-grad"></div>
        <div class="alx-modal__hero-info">
          <h2 class="alx-modal__hero-name">${esc(name)}</h2>
          ${heroSubHtml ? `<div class="alx-modal__hero-sub">${heroSubHtml}</div>` : ''}
          ${quoteHtml}
        </div>
      `;
    }

    // === BUILD DETAILS (right side) ===
    // (Badges removed) | (Academic removed) | (Contact removed) | (Record Info removed)
    const tagParts = [];
    if (dept) tagParts.push(dept);
    if (role || company) tagParts.push([role, company].filter(Boolean).join(' · '));
    if (location) tagParts.push(location);

    const tagHtml = tagParts.length
      ? tagParts.map(t => `<span>${esc(t)}</span>`).join('<span class="tsep">•</span>')
      : '';

    // Professional fields
    const profFields = [
      mFieldHtml('Company', 'fa-solid fa-building', company),
      mFieldHtml('Role / Designation', 'fa-solid fa-briefcase', role),
      mFieldHtml('Industry', 'fa-solid fa-layer-group', industry),
      mFieldHtml('Location', 'fa-solid fa-location-dot', location),
    ].filter(Boolean);

    // Skills
    const skillsHtml = mChipsHtml('Skills', 'fa-solid fa-wand-magic-sparkles', skills);

    // About
    const noteHtml = note ? `
      <div class="alx-mnote">
        <div class="alx-mnote-label"><i class="fa-solid fa-quote-left"></i>About</div>
        <div class="alx-mnote-val">${esc(note)}</div>
      </div>
    ` : '';

    let detailsHtml = `
      <div class="alx-modal__dhead">
        <h2 class="alx-modal__dhead-name">${esc(name)}</h2>
        ${tagHtml ? `<div class="alx-modal__dhead-tagline">${tagHtml}</div>` : ''}
      </div>
      <div class="alx-modal__dcontent">
    `;

    if (profFields.length) {
      detailsHtml += `
        <div class="alx-msection">
          <div class="alx-msection-title">Professional</div>
          <div class="alx-mrow">${profFields.join('')}</div>
        </div>
      `;
    }

    if (skillsHtml) {
      detailsHtml += `
        <div class="alx-msection">
          <div class="alx-msection-title">Skills</div>
          <div class="alx-mrow">${skillsHtml}</div>
        </div>
      `;
    }

    if (noteHtml) {
      detailsHtml += `
        <div class="alx-msection">
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

    // focus close btn
    const closeBtn = els.modal.querySelector('button[data-close="1"]');
    if (closeBtn) closeBtn.focus();
  }

  function onCardClick(e){
    const card = e.target.closest('.alx-card');
    if (!card) return;

    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

    e.preventDefault();

    const key = (card.getAttribute('data-key') || '').trim();
    if (!key) return;

    const it = alumniByKey.get(key);
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

    await ensureAlumniLoaded(false);
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
      const b = e.target.closest('button.alx-pagebtn[data-page]');
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
