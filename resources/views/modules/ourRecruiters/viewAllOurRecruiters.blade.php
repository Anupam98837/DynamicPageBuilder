{{-- resources/views/landing/our-recruiters.blade.php --}}
@include('landing.components.header')
@include('landing.components.headermenu')

<style>
  :root {
    --brand: #8f2f2f;
    --ink: #0f172a;
    --muted: #64748b;
    --bg: #ffffff;
    --card: #ffffff;
    --line: rgba(15, 23, 42, .10);
    --shadow: 0 10px 24px rgba(2, 6, 23, .08);
  }

  :root {
    --brand: var(--primary-color, var(--brand));
    --bg: var(--page-bg, var(--bg));
    --card: var(--surface, var(--card));
    --line: var(--line-soft, var(--line));
  }

  body { background: var(--bg); }

  .rec-wrap {
    max-width: 1180px;
    margin: 18px auto 54px;
    padding: 0 12px;
  }

  .rec-head {
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: 16px;
    box-shadow: var(--shadow);
    padding: 14px 16px;
    margin-bottom: 16px;
    display: flex;
    gap: 12px;
    align-items: flex-end;
    justify-content: space-between;
    flex-wrap: wrap;
  }

  .rec-title {
    margin: 0;
    font-weight: 950;
    letter-spacing: .2px;
    color: var(--ink);
    font-size: 28px;
  }

  .rec-sub {
    margin: 6px 0 0;
    color: var(--muted);
    font-size: 14px;
  }

  .rec-tools {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
  }

  .rec-search {
    position: relative;
    min-width: 260px;
    max-width: 420px;
    flex: 1 1 260px;
  }

  .rec-search i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    opacity: .65;
    color: var(--muted);
  }

  .rec-search input {
    width: 100%;
    border-radius: 14px;
    padding: 11px 12px 11px 42px;
    border: 1px solid var(--line);
    background: var(--card);
    color: var(--ink);
    outline: none;
  }

  .rec-search input:focus {
    border-color: rgba(201, 75, 80, .55);
    box-shadow: 0 0 0 4px rgba(201, 75, 80, .18);
  }

  .rec-chip {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border-radius: 999px;
    border: 1px solid var(--line);
    background: var(--card);
    box-shadow: 0 8px 18px rgba(2, 6, 23, .06);
    color: var(--ink);
    font-size: 13px;
    font-weight: 900;
    white-space: nowrap;
  }

  /* =========================================================
   ✅ Masonry-style grid (like your reference collage)
   - No fixed tile height / width ratio
   - Items flow naturally with their image height
  ========================================================= */
  /* ✅ 12-fr width system: 12 equal columns */
/* ✅ 6 items per row, widths behave like 1fr/2fr and CONTINUE across rows */
.rec-grid{
  display: grid;
  gap: 14px;
  grid-template-columns: repeat(9, minmax(0, 1fr)); /* total “fr” = 9 each row */
  align-items: start;
}

/* Base tile */
.rec-tile{
  margin: 0;
  border-radius: 12px;
  overflow: hidden;
  background: #fff;
  border: 1px solid rgba(15, 23, 42, .06);
  box-shadow: 0 1px 3px rgba(2, 6, 23, .06), 0 6px 12px rgba(2, 6, 23, .04);
  cursor: pointer;
  transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);

  height: 110px; /* ✅ control card height here */
  grid-column: span 1; /* default = 1fr */
}

/* ✅ 12-item cycle:
   1..6  = 1,2,1,2,1,2
   7..12 = 2,1,2,1,2,1
   => so 7th becomes 2fr (continues from 6th)
*/
.rec-tile:nth-child(12n + 2),
.rec-tile:nth-child(12n + 4),
.rec-tile:nth-child(12n + 6),
.rec-tile:nth-child(12n + 7),
.rec-tile:nth-child(12n + 9),
.rec-tile:nth-child(12n + 11){
  grid-column: span 2; /* ✅ 2fr */
}

.rec-tile:hover{
  transform: translateY(-3px);
  box-shadow: 0 4px 6px rgba(2, 6, 23, .08), 0 16px 28px rgba(2, 6, 23, .12);
  border-color: rgba(143, 47, 47, .2);
}

/* ✅ image contain */
.rec-tile__inner{ display:block; width:100%; height:100%; background:#fff; }
.rec-tile img{
  width: 100%;
  height: 100%;
  object-fit: contain;   /* ✅ contain */
  object-position: center;
  display: block;
  padding: 8px;          /* reduce if you want bigger logo */
}

/* fallback block centered */
.rec-tile__fallback{
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 14px 12px;
  color: #64748b;
  font-weight: 900;
  font-size: 14px;
  text-align: center;
}

/* ✅ Skeleton grid matches width pattern */
.rec-skeleton{
  display: grid;
  gap: 14px;
  grid-template-columns: repeat(12, minmax(0, 1fr));
}

.sk-tile{
  --w: 2;
  grid-column: span var(--w);

  background: #fff;
  border: 1px solid var(--line);
  box-shadow: 0 10px 24px rgba(2, 6, 23, .08);
  border-radius: 18px;
  overflow: hidden;
  position: relative;
}

/* same width pattern on skeleton */
.sk-tile:nth-child(6n + 1){ --w: 1; }
.sk-tile:nth-child(6n + 2){ --w: 2; }
.sk-tile:nth-child(6n + 3){ --w: 1; }
.sk-tile:nth-child(6n + 4){ --w: 2; }
.sk-tile:nth-child(6n + 5){ --w: 3; }
.sk-tile:nth-child(6n + 6){ --w: 3; }

/* ✅ responsive: simplify on smaller screens */
@media (max-width: 992px){
  .rec-grid, .rec-skeleton{ grid-template-columns: repeat(6, minmax(0,1fr)); }
  .rec-tile, .sk-tile{ grid-column: span 3; } /* 2 per row */
}
@media (max-width: 520px){
  .rec-grid, .rec-skeleton{ grid-template-columns: repeat(2, minmax(0,1fr)); }
  .rec-tile, .sk-tile{ grid-column: span 2; } /* 1 per row */
}

  @keyframes skMove { to { transform: translateX(60%); } }

  /* Empty / error */
  .rec-state {
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: 16px;
    box-shadow: var(--shadow);
    padding: 18px;
    color: var(--muted);
    text-align: center;
  }

  /* Pagination */
  .rec-pagination { display: flex; justify-content: center; margin-top: 18px; }
  .rec-pagination .pager {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    padding: 10px;
  }

  .rec-pagebtn {
    border: 1px solid var(--line);
    background: var(--card);
    color: var(--ink);
    border-radius: 12px;
    padding: 9px 12px;
    font-size: 13px;
    font-weight: 950;
    box-shadow: 0 8px 18px rgba(2, 6, 23, .06);
    cursor: pointer;
    user-select: none;
  }
  .rec-pagebtn:hover { background: rgba(2, 6, 23, .03); }
  .rec-pagebtn[disabled] { opacity: .55; cursor: not-allowed; }
  .rec-pagebtn.active {
    background: rgba(201, 75, 80, .12);
    border-color: rgba(201, 75, 80, .35);
    color: var(--brand);
  }

  /* Responsive: reduce column count */
  @media (max-width: 1200px){ .rec-grid{ grid-template-columns: repeat(5, minmax(0,1fr)); } }
@media (max-width: 992px) { .rec-grid{ grid-template-columns: repeat(4, minmax(0,1fr)); } }
@media (max-width: 768px) { .rec-grid{ grid-template-columns: repeat(3, minmax(0,1fr)); } }
@media (max-width: 520px) { .rec-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); } }

  /* =========================
     ✅ Enhanced Modal UI
     ========================= */
  .rec-modal {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
    opacity: 0;
    transition: opacity 0.2s ease;
  }
  .rec-modal.show { display: flex; animation: modalFadeIn 0.2s ease forwards; }
  @keyframes modalFadeIn { to { opacity: 1; } }

  .rec-modal__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(2, 6, 23, 0.88);
    backdrop-filter: blur(4px);
  }

  .rec-modal__dialog {
    position: relative;
    width: min(800px, 100%);
    max-height: 90vh;
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: 20px;
    box-shadow: 0 24px 64px rgba(2, 6, 23, 0.35);
    overflow: hidden;
    transform: translateY(20px) scale(0.98);
    animation: modalSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    display: flex;
    flex-direction: column;
  }

  @keyframes modalSlideUp {
    to { transform: translateY(0) scale(1); opacity: 1; }
  }

  .rec-modal__header {
    padding: 18px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    border-bottom: 1px solid var(--line);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .rec-modal__title {
    margin: 0;
    font-size: 20px;
    font-weight: 950;
    color: var(--ink);
    letter-spacing: -0.2px;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .rec-modal__close {
    border: none;
    background: rgba(2, 6, 23, 0.04);
    width: 40px;
    height: 40px;
    border-radius: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--muted);
    transition: all 0.2s ease;
    flex-shrink: 0;
    position: relative;
    overflow: hidden;
  }

  .rec-modal__close:hover {
    background: rgba(201, 75, 80, 0.12);
    color: var(--brand);
    transform: rotate(90deg);
  }

  .rec-modal__close:hover::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(201, 75, 80, 0.08);
  }

  .rec-modal__close i { position: relative; z-index: 1; font-size: 16px; }

  .rec-modal__body {
    padding: 24px;
    display: grid;
    grid-template-columns: 140px 1fr;
    gap: 24px;
    align-items: start;
    overflow-y: auto;
    flex: 1;
  }

  .rec-modal__logo-container { display: flex; flex-direction: column; gap: 12px; }

  .rec-modal__logo {
    width: 140px;
    height: 140px;
    border: 1px solid var(--line);
    border-radius: 20px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    padding: 16px;
    box-shadow: 0 12px 32px rgba(2, 6, 23, 0.1);
    transition: all 0.3s ease;
    position: relative;
  }

  .rec-modal__logo:hover {
    transform: translateY(-2px);
    box-shadow: 0 20px 40px rgba(2, 6, 23, 0.15);
    border-color: rgba(201, 75, 80, 0.25);
  }

  .rec-modal__logo img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    display: block;
    transition: transform 0.3s ease;
  }
  .rec-modal__logo:hover img { transform: scale(1.05); }

  .rec-modal__logo-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: var(--muted);
    font-weight: 900;
    font-size: 42px;
    opacity: 0.6;
  }

  .rec-modal__details { display: flex; flex-direction: column; gap: 20px; }
  .rec-modal__section { display: flex; flex-direction: column; gap: 8px; }

  .rec-modal__section-title {
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--brand);
    opacity: 0.8;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .rec-modal__section-title i { font-size: 11px; }

  .rec-modal__description {
    color: var(--ink);
    font-size: 15px;
    line-height: 1.6;
    max-height: 300px;
    overflow-y: auto;
    padding-right: 8px;
  }
  .rec-modal__description::-webkit-scrollbar { width: 4px; }
  .rec-modal__description::-webkit-scrollbar-track { background: rgba(2, 6, 23, 0.04); border-radius: 4px; }
  .rec-modal__description::-webkit-scrollbar-thumb { background: rgba(2, 6, 23, 0.12); border-radius: 4px; }
  .rec-modal__description p { margin: 0 0 12px 0; }
  .rec-modal__description p:last-child { margin-bottom: 0; }

  .rec-modal__info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin-top: 4px;
  }

  .rec-modal__info-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: rgba(2, 6, 23, 0.02);
    border-radius: 10px;
    border: 1px solid var(--line);
  }

  .rec-modal__info-item i {
    color: var(--brand);
    opacity: 0.8;
    font-size: 14px;
    width: 16px;
  }

  .rec-modal__info-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--muted);
    white-space: nowrap;
  }

  .rec-modal__info-value {
    font-size: 13px;
    font-weight: 700;
    color: var(--ink);
    margin-left: auto;
  }

  .rec-modal__footer {
    padding: 18px 24px;
    border-top: 1px solid var(--line);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    position: sticky;
    bottom: 0;
  }

  .rec-modal__footer-left {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--muted);
    font-size: 13px;
  }

  .rec-modal__footer-right { display: flex; align-items: center; gap: 10px; }

  .rec-modal__btn {
    border: 1px solid var(--line);
    background: var(--card);
    color: var(--ink);
    border-radius: 12px;
    padding: 10px 16px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(2, 6, 23, 0.08);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    min-height: 42px;
  }

  .rec-modal__btn:hover {
    background: rgba(2, 6, 23, 0.03);
    transform: translateY(-1px);
    box-shadow: 0 12px 24px rgba(2, 6, 23, 0.12);
  }

  .rec-modal__btn.primary {
    background: linear-gradient(135deg, rgba(201, 75, 80, 0.15), rgba(201, 75, 80, 0.08));
    border-color: rgba(201, 75, 80, 0.35);
    color: var(--brand);
    font-weight: 800;
  }

  .rec-modal__btn.primary:hover {
    background: linear-gradient(135deg, rgba(201, 75, 80, 0.22), rgba(201, 75, 80, 0.15));
    border-color: rgba(201, 75, 80, 0.5);
    box-shadow: 0 12px 24px rgba(201, 75, 80, 0.15);
  }

  .rec-modal__btn.secondary {
    background: rgba(2, 6, 23, 0.02);
    border-color: rgba(2, 6, 23, 0.08);
  }

  .rec-modal__btn.secondary:hover {
    background: rgba(2, 6, 23, 0.05);
    border-color: rgba(2, 6, 23, 0.15);
  }

  .rec-modal__btn i { font-size: 13px; }

  .rec-modal__loading {
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 24px;
    gap: 16px;
  }
  .rec-modal__loading.show { display: flex; }

  .rec-modal__loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(2, 6, 23, 0.08);
    border-top-color: var(--brand);
    border-radius: 50%;
    animation: modalSpinner 0.8s linear infinite;
  }

  @keyframes modalSpinner { to { transform: rotate(360deg); } }

  @media (max-width: 768px) {
    .rec-modal__body { grid-template-columns: 1fr; gap: 20px; }
    .rec-modal__logo-container { align-items: center; }
    .rec-modal__logo { width: 160px; height: 160px; }
    .rec-modal__footer { flex-direction: column; align-items: stretch; gap: 12px; }
    .rec-modal__footer-right { width: 100%; }
    .rec-modal__btn { flex: 1; justify-content: center; min-width: 0; }
  }

  @media (max-width: 480px) {
    .rec-modal__header { padding: 16px 20px; }
    .rec-modal__body { padding: 20px; }
    .rec-modal__footer { padding: 16px 20px; }
    .rec-modal__btn { padding: 10px 14px; font-size: 13px; }
    .rec-modal__info-grid { grid-template-columns: 1fr; }
  }
</style>

<div class="rec-wrap" data-api="{{ url('/api/public/recruiters') }}">

  <div class="rec-head">
    <div>
      <h1 class="rec-title">Our Recruiters</h1>
      <div class="rec-sub" id="recSub">Companies that recruit from our campus</div>
    </div>

    <div class="rec-tools">
      <div class="rec-search">
        <i class="fa fa-magnifying-glass"></i>
        <input id="recSearch" type="search" placeholder="Search company…">
      </div>
      <div class="rec-chip" title="Total results">
        <i class="fa-solid fa-building" style="opacity:.85"></i>
        <span id="recCount">—</span>
      </div>
    </div>
  </div>

  <div id="recGrid" class="rec-grid" style="display:none;"></div>

  <div id="recSkeleton" class="rec-skeleton"></div>
  <div id="recState" class="rec-state" style="display:none;"></div>

  <div class="rec-pagination">
    <div id="recPager" class="pager" style="display:none;"></div>
  </div>
</div>

{{-- ✅ Enhanced Modal --}}
<div id="recModal" class="rec-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="recModalTitle" aria-describedby="recModalDesc">
  <div class="rec-modal__backdrop" data-close="1"></div>

  <div class="rec-modal__dialog" role="document">
    <div class="rec-modal__header">
      <h3 id="recModalTitle" class="rec-modal__title">Company Details</h3>
      <button type="button" class="rec-modal__close" id="recModalClose" aria-label="Close modal">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="rec-modal__loading" id="recModalLoading">
      <div class="rec-modal__loading-spinner"></div>
      <div style="color: var(--muted); font-size: 14px;">Loading company details...</div>
    </div>

    <div class="rec-modal__body" id="recModalBody">
      <div class="rec-modal__logo-container">
        <div class="rec-modal__logo">
          <img id="recModalLogo" src="" alt="Company Logo" style="display: none;">
          <div id="recModalLogoFallback" class="rec-modal__logo-fallback">
            <i class="fa-solid fa-building"></i>
          </div>
        </div>
        <div class="rec-modal__info-item">
          <i class="fa-solid fa-calendar"></i>
          <span class="rec-modal__info-label">Added</span>
          <span id="recModalDate" class="rec-modal__info-value">—</span>
        </div>
      </div>

      <div class="rec-modal__details">
        <div class="rec-modal__section">
          <div class="rec-modal__section-title">
            <i class="fa-solid fa-circle-info"></i>
            About Company
          </div>
          <div id="recModalDesc" class="rec-modal__description">
            <p>No description available.</p>
          </div>
        </div>

        <div class="rec-modal__info-grid">
          <div class="rec-modal__info-item">
            <i class="fa-solid fa-industry"></i>
            <span class="rec-modal__info-label">Industry</span>
            <span id="recModalIndustry" class="rec-modal__info-value">—</span>
          </div>
          <div class="rec-modal__info-item">
            <i class="fa-solid fa-location-dot"></i>
            <span class="rec-modal__info-label">Location</span>
            <span id="recModalLocation" class="rec-modal__info-value">—</span>
          </div>
          <div class="rec-modal__info-item">
            <i class="fa-solid fa-users"></i>
            <span class="rec-modal__info-label">Hired</span>
            <span id="recModalHired" class="rec-modal__info-value">—</span>
          </div>
        </div>
      </div>
    </div>

    <div class="rec-modal__footer">
      <div class="rec-modal__footer-left">
        <i class="fa-solid fa-clock"></i>
        <span>Last updated: <span id="recModalUpdated">—</span></span>
      </div>
      <div class="rec-modal__footer-right">
        <a id="recModalWebsite" class="rec-modal__btn primary" href="#" target="_blank" rel="noopener noreferrer" style="display: none;">
          <i class="fa-solid fa-external-link"></i>
          Visit Website
        </a>
        <button type="button" class="rec-modal__btn secondary" style="display:none" data-close="1">
          <i class="fa-solid fa-xmark"></i>
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

<script>
  (() => {
    if (window.__PUBLIC_RECRUITERS__) return;
    window.__PUBLIC_RECRUITERS__ = true;

    const root = document.querySelector('.rec-wrap');
    if (!root) return;

    const API = root.getAttribute('data-api') || '/api/public/recruiters';
    const $ = (id) => document.getElementById(id);

    const state = { page: 1, perPage: 24, lastPage: 1, total: 0, q: '' };
    let activeController = null;

    // Modal elements
    const modal = $('recModal');
    const modalTitle = $('recModalTitle');
    const modalDesc = $('recModalDesc');
    const modalLogo = $('recModalLogo');
    const modalLogoFallback = $('recModalLogoFallback');
    const modalWebsite = $('recModalWebsite');
    const modalCloseBtn = $('recModalClose');
    const modalLoading = $('recModalLoading');
    const modalBody = $('recModalBody');
    const modalIndustry = $('recModalIndustry');
    const modalLocation = $('recModalLocation');
    const modalHired = $('recModalHired');
    const modalDate = $('recModalDate');
    const modalUpdated = $('recModalUpdated');

    function esc(str) {
      return (str ?? '').toString().replace(/[&<>"']/g, s => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
      }[s]));
    }

    function normalizeUrl(url) {
      const u = (url || '').toString().trim();
      if (!u) return '';
      if (/^(data:|blob:|https?:\/\/)/i.test(u)) return u;
      if (u.startsWith('/')) return window.location.origin + u;
      return window.location.origin + '/' + u;
    }

    function pick(obj, keys) {
      for (const k of keys) {
        const v = obj?.[k];
        if (v !== null && v !== undefined && String(v).trim() !== '') return v;
      }
      return '';
    }

    function formatDate(dateString) {
      if (!dateString) return '—';
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
      } catch { return dateString; }
    }

    function formatNumber(num) {
      if (!num && num !== 0) return '—';
      return num.toLocaleString();
    }

    function showModalLoading(show) {
      if (modalLoading) modalLoading.style.display = show ? 'flex' : 'none';
      if (modalBody) modalBody.style.display = show ? 'block' : 'none';
    }

    function setBodyScroll(lock) {
      document.documentElement.style.overflow = lock ? 'hidden' : '';
      document.body.style.overflow = lock ? 'hidden' : '';
    }

    function openModal({ name, desc, logo, website, industry, location, hired, date, updated }) {
      if (!modal) return;

      showModalLoading(true);

      const n = (name || 'Company').toString().trim();
      const d = (desc || '').toString().trim();
      if (modalTitle) modalTitle.textContent = n;

      if (modalDesc) {
        if (d) {
          const paragraphs = d.split('\n\n').filter(p => p.trim());
          modalDesc.innerHTML = paragraphs.map(p => `<p>${esc(p.replace(/\n/g, '<br>'))}</p>`).join('');
        } else {
          modalDesc.innerHTML = '<p style="color: var(--muted); font-style: italic;">No description available.</p>';
        }
      }

      const hasLogo = !!(logo && logo.trim());
      if (modalLogo && modalLogoFallback) {
        if (hasLogo) {
          modalLogo.onload = () => {
            modalLogo.style.display = 'block';
            modalLogoFallback.style.display = 'none';
            showModalLoading(false);
          };
          modalLogo.onerror = () => {
            modalLogo.style.display = 'none';
            modalLogoFallback.style.display = 'flex';
            showModalLoading(false);
          };
          modalLogo.src = logo;
          modalLogo.alt = `${n} Logo`;
        } else {
          modalLogo.style.display = 'none';
          modalLogoFallback.style.display = 'flex';
          modalLogoFallback.innerHTML = '<i class="fa-solid fa-building"></i>';
          showModalLoading(false);
        }
      }

      if (modalWebsite) {
        if (website && website.trim()) {
          modalWebsite.href = website;
          modalWebsite.style.display = 'flex';
        } else {
          modalWebsite.style.display = 'none';
        }
      }

      if (modalIndustry) modalIndustry.textContent = industry || '—';
      if (modalLocation) modalLocation.textContent = location || '—';
      if (modalHired) modalHired.textContent = hired !== undefined ? formatNumber(hired) : '—';
      if (modalDate) modalDate.textContent = date ? formatDate(date) : '—';
      if (modalUpdated) modalUpdated.textContent = updated ? formatDate(updated) : '—';

      modal.classList.add('show');
      modal.setAttribute('aria-hidden', 'false');
      setBodyScroll(true);

      if (!hasLogo) setTimeout(() => showModalLoading(false), 100);
    }

    function closeModal() {
      if (!modal) return;
      modal.classList.remove('show');
      modal.setAttribute('aria-hidden', 'true');
      setBodyScroll(false);
    }

    document.addEventListener('click', (e) => {
      if (e.target.closest?.('[data-close="1"]')) closeModal();
    });
    modalCloseBtn?.addEventListener('click', closeModal);
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal?.classList.contains('show')) closeModal();
    });

    function showSkeleton() {
      const sk = $('recSkeleton');
      const st = $('recState');
      const grid = $('recGrid');
      const pager = $('recPager');

      if (grid) grid.style.display = 'none';
      if (pager) pager.style.display = 'none';
      if (st) st.style.display = 'none';

      if (!sk) return;
      sk.style.display = '';

      // ✅ varied skeleton heights (masonry feel)
      const heights = [160, 190, 220, 170, 210, 180, 240, 165, 205, 175, 230, 185];
      sk.innerHTML = heights.map(h => `<div class="sk-tile" style="height:${h}px"></div>`).join('');
    }

    function hideSkeleton() {
      const sk = $('recSkeleton');
      if (!sk) return;
      sk.style.display = 'none';
      sk.innerHTML = '';
    }

    async function fetchJson(url) {
      if (activeController) activeController.abort();
      activeController = new AbortController();

      const res = await fetch(url, { headers: { 'Accept': 'application/json' }, signal: activeController.signal });
      const js = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(js?.message || 'Request failed');
      return js;
    }

    function buildUrl() {
      const params = new URLSearchParams();
      params.set('page', String(state.page));
      params.set('per_page', String(state.perPage));
      if (state.q.trim()) params.set('q', state.q.trim());
      params.set('sort', 'created_at');
      params.set('direction', 'desc');
      return API + '?' + params.toString();
    }

    function render(items) {
      const grid = $('recGrid');
      const st = $('recState');
      const count = $('recCount');
      if (!grid || !st) return;

      if (!items.length) {
        grid.style.display = 'none';
        st.style.display = '';
        st.innerHTML = `
          <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
            <i class="fa-regular fa-face-frown"></i>
          </div>
          No recruiters found.
        `;
        if (count) count.textContent = '0';
        return;
      }

      if (count) count.textContent = String(state.total || items.length);
      st.style.display = 'none';
      grid.style.display = '';

      grid.innerHTML = items.map(it => {
        const logo =
          pick(it, ['logo_url', 'image_url', 'image_full_url', 'logo', 'image', 'src', 'url']) ||
          (it?.attachment?.url ?? '');

        const name = pick(it, ['name', 'title', 'company', 'label']) || 'Recruiter';
        const desc = pick(it, ['description', 'about', 'summary', 'content', 'details', 'body']) || '';
        const website = pick(it, ['website', 'link', 'web_url', 'site', 'company_url']) || '';
        const industry = pick(it, ['industry', 'sector', 'category']) || '';
        const location = pick(it, ['location', 'city', 'country', 'headquarters']) || '';
        const hired = pick(it, ['students_hired', 'hired_count', 'placements']) || '';
        const date = pick(it, ['created_at', 'date_added', 'joined_date']) || '';
        const updated = pick(it, ['updated_at', 'last_updated']) || '';

        const fullLogo = normalizeUrl(logo);
        const webHref = website ? normalizeUrl(website) : '';

        return `
          <div class="rec-tile"
               role="button"
               tabindex="0"
               data-name="${esc(name)}"
               data-desc="${esc(desc)}"
               data-logo="${esc(fullLogo)}"
               data-website="${esc(webHref)}"
               data-industry="${esc(industry)}"
               data-location="${esc(location)}"
               data-hired="${esc(hired)}"
               data-date="${esc(date)}"
               data-updated="${esc(updated)}"
               aria-label="View ${esc(name)} details">
            <span class="rec-tile__inner">
              ${fullLogo
                ? `<img src="${esc(fullLogo)}" alt="${esc(name)}" loading="lazy">`
                : `<div class="rec-tile__fallback">${esc(name)}</div>`
              }
            </span>
          </div>
        `;
      }).join('');
    }

    function renderPager() {
      const pager = $('recPager');
      if (!pager) return;

      const last = state.lastPage || 1;
      const cur = state.page || 1;

      if (last <= 1) {
        pager.style.display = 'none';
        pager.innerHTML = '';
        return;
      }

      const btn = (label, page, { disabled = false, active = false } = {}) => {
        const dis = disabled ? 'disabled' : '';
        const cls = active ? 'rec-pagebtn active' : 'rec-pagebtn';
        return `<button class="${cls}" ${dis} data-page="${page}">${label}</button>`;
      };

      let html = '';
      html += btn('Previous', Math.max(1, cur - 1), { disabled: cur <= 1 });

      const win = 2;
      const start = Math.max(1, cur - win);
      const end = Math.min(last, cur + win);

      if (start > 1) {
        html += btn('1', 1, { active: cur === 1 });
        if (start > 2) html += `<span style="opacity:.6;padding:0 4px;">…</span>`;
      }

      for (let p = start; p <= end; p++) html += btn(String(p), p, { active: p === cur });

      if (end < last) {
        if (end < last - 1) html += `<span style="opacity:.6;padding:0 4px;">…</span>`;
        html += btn(String(last), last, { active: cur === last });
      }

      html += btn('Next', Math.min(last, cur + 1), { disabled: cur >= last });

      pager.innerHTML = html;
      pager.style.display = 'flex';
    }

    async function load() {
      showSkeleton();

      try {
        const js = await fetchJson(buildUrl());
        const items = Array.isArray(js?.data) ? js.data : (Array.isArray(js) ? js : []);
        const p = js?.pagination || {};
        state.total = parseInt(p.total ?? items.length, 10) || items.length;
        state.lastPage = parseInt(p.last_page ?? 1, 10) || 1;
        state.page = parseInt(p.page ?? state.page, 10) || state.page;

        hideSkeleton();
        render(items);
        renderPager();
      } catch (e) {
        hideSkeleton();
        const st = $('recState');
        const grid = $('recGrid');
        const pager = $('recPager');

        if (grid) grid.style.display = 'none';
        if (pager) pager.style.display = 'none';

        if (st) {
          st.style.display = '';
          st.innerHTML = `
            <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
              <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            Could not load recruiters.
            <div style="margin-top:8px;font-size:12.5px;opacity:.9;">
              API: <b>${esc(API)}</b>
            </div>
          `;
        }
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      const search = $('recSearch');

      let t = null;
      search && search.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(() => {
          state.q = (search.value || '').trim();
          state.page = 1;
          load();
        }, 260);
      });

      document.addEventListener('click', (e) => {
        const b = e.target.closest('button.rec-pagebtn[data-page]');
        if (!b) return;
        const p = parseInt(b.dataset.page, 10);
        if (!p || Number.isNaN(p) || p === state.page) return;
        state.page = p;
        load();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });

      document.addEventListener('click', (e) => {
        const tile = e.target.closest('.rec-tile');
        if (!tile) return;

        openModal({
          name: tile.getAttribute('data-name') || 'Company',
          desc: tile.getAttribute('data-desc') || '',
          logo: tile.getAttribute('data-logo') || '',
          website: tile.getAttribute('data-website') || '',
          industry: tile.getAttribute('data-industry') || '',
          location: tile.getAttribute('data-location') || '',
          hired: tile.getAttribute('data-hired') || '',
          date: tile.getAttribute('data-date') || '',
          updated: tile.getAttribute('data-updated') || ''
        });
      });

      document.addEventListener('keydown', (e) => {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        const tile = e.target.closest?.('.rec-tile');
        if (!tile) return;
        e.preventDefault();

        openModal({
          name: tile.getAttribute('data-name') || 'Company',
          desc: tile.getAttribute('data-desc') || '',
          logo: tile.getAttribute('data-logo') || '',
          website: tile.getAttribute('data-website') || '',
          industry: tile.getAttribute('data-industry') || '',
          location: tile.getAttribute('data-location') || '',
          hired: tile.getAttribute('data-hired') || '',
          date: tile.getAttribute('data-date') || '',
          updated: tile.getAttribute('data-updated') || ''
        });
      });

      load();
    });
  })();
</script>
