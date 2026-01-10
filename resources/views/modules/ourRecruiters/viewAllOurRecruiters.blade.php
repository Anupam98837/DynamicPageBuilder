{{-- resources/views/landing/our-recruiters.blade.php --}}
<style>
  /* =========================================================
    ✅ Scoped-only variables (NO :root)
    - prevents conflicts when this view is @included anywhere
    - applied to BOTH wrapper + modal via .orc-scope class
  ========================================================= */
  .orc-scope{
    --orc-brand:  var(--primary-color, #8f2f2f);
    --orc-ink:    var(--ink, #0f172a);
    --orc-muted:  var(--muted-color, #64748b);
    --orc-bg:     var(--page-bg, #ffffff);
    --orc-card:   var(--surface, #ffffff);
    --orc-line:   var(--line-soft, rgba(15, 23, 42, .10));
    --orc-shadow: var(--shadow-2, 0 10px 24px rgba(2, 6, 23, .08));
  }

  /* Wrapper */
  .orc-wrap{
    max-width: 1180px;
    margin: 18px auto 54px;
    padding: 0 12px;
    background: transparent; /* ✅ don't touch body/page background */
  }

  .orc-head{
    background: var(--orc-card);
    border: 1px solid var(--orc-line);
    border-radius: 16px;
    box-shadow: var(--orc-shadow);
    padding: 14px 16px;
    margin-bottom: 16px;
    display: flex;
    gap: 12px;
    align-items: flex-end;
    justify-content: space-between;
    flex-wrap: wrap;
  }

  .orc-title{
    margin: 0;
    font-weight: 950;
    letter-spacing: .2px;
    color: var(--orc-ink);
    font-size: 28px;
  }

  .orc-sub{
    margin: 6px 0 0;
    color: var(--orc-muted);
    font-size: 14px;
  }

  .orc-tools{
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
  }

  .orc-search{
    position: relative;
    min-width: 260px;
    max-width: 420px;
    flex: 1 1 260px;
  }

  .orc-search i{
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    opacity: .65;
    color: var(--orc-muted);
  }

  .orc-search input{
    width: 100%;
    border-radius: 14px;
    padding: 11px 12px 11px 42px;
    border: 1px solid var(--orc-line);
    background: var(--orc-card);
    color: var(--orc-ink);
    outline: none;
  }

  .orc-search input:focus{
    border-color: rgba(201, 75, 80, .55);
    box-shadow: 0 0 0 4px rgba(201, 75, 80, .18);
  }

  .orc-chip{
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border-radius: 999px;
    border: 1px solid var(--orc-line);
    background: var(--orc-card);
    box-shadow: 0 8px 18px rgba(2, 6, 23, .06);
    color: var(--orc-ink);
    font-size: 13px;
    font-weight: 900;
    white-space: nowrap;
  }

  /* =========================================================
     ✅ Masonry-style grid
  ========================================================= */
  .orc-grid{
    display: grid;
    /* gap: 14px; */
    grid-template-columns: repeat(9, minmax(0, 1fr));
    align-items: start;
  }

  .orc-tile{
    margin: 0;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
    border: 1px solid rgba(15, 23, 42, .06);
    box-shadow: 0 1px 3px rgba(2, 6, 23, .06), 0 6px 12px rgba(2, 6, 23, .04);
    cursor: pointer;
    transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);

    height: 110px;
    grid-column: span 1;
  }

  .orc-tile:nth-child(12n + 2),
  .orc-tile:nth-child(12n + 4),
  .orc-tile:nth-child(12n + 6),
  .orc-tile:nth-child(12n + 7),
  .orc-tile:nth-child(12n + 9),
  .orc-tile:nth-child(12n + 11){
    grid-column: span 2;
  }

  .orc-tile:hover{
    transform: translateY(-3px);
    box-shadow: 0 4px 6px rgba(2, 6, 23, .08), 0 16px 28px rgba(2, 6, 23, .12);
    border-color: rgba(143, 47, 47, .2);
  }

  .orc-tile__inner{ display:block; width:100%; height:100%; background:#fff; }
  .orc-tile img{
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
    display: block;
    padding: 8px;
  }

  .orc-tile__fallback{
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

  /* Skeleton */
  .orc-skeleton{
    display: grid;
    gap: 14px;
    grid-template-columns: repeat(12, minmax(0, 1fr));
  }

  .orc-sk-tile{
    --w: 2;
    grid-column: span var(--w);

    background: #fff;
    border: 1px solid var(--orc-line);
    box-shadow: var(--orc-shadow);
    border-radius: 18px;
    overflow: hidden;
    position: relative;
  }

  .orc-sk-tile:nth-child(6n + 1){ --w: 1; }
  .orc-sk-tile:nth-child(6n + 2){ --w: 2; }
  .orc-sk-tile:nth-child(6n + 3){ --w: 1; }
  .orc-sk-tile:nth-child(6n + 4){ --w: 2; }
  .orc-sk-tile:nth-child(6n + 5){ --w: 3; }
  .orc-sk-tile:nth-child(6n + 6){ --w: 3; }

  @media (max-width: 992px){
    .orc-grid, .orc-skeleton{ grid-template-columns: repeat(6, minmax(0,1fr)); }
    .orc-tile, .orc-sk-tile{ grid-column: span 3; }
  }
  @media (max-width: 520px){
    .orc-grid, .orc-skeleton{ grid-template-columns: repeat(2, minmax(0,1fr)); }
    .orc-tile, .orc-sk-tile{ grid-column: span 2; }
  }

  /* State */
  .orc-state{
    background: var(--orc-card);
    border: 1px solid var(--orc-line);
    border-radius: 16px;
    box-shadow: var(--orc-shadow);
    padding: 18px;
    color: var(--orc-muted);
    text-align: center;
  }

  /* Pagination */
  .orc-pagination{ display: flex; justify-content: center; margin-top: 18px; }
  .orc-pagination .orc-pager{
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    padding: 10px;
  }

  .orc-pagebtn{
    border: 1px solid var(--orc-line);
    background: var(--orc-card);
    color: var(--orc-ink);
    border-radius: 12px;
    padding: 9px 12px;
    font-size: 13px;
    font-weight: 950;
    box-shadow: 0 8px 18px rgba(2, 6, 23, .06);
    cursor: pointer;
    user-select: none;
  }
  .orc-pagebtn:hover{ background: rgba(2, 6, 23, .03); }
  .orc-pagebtn[disabled]{ opacity: .55; cursor: not-allowed; }
  .orc-pagebtn.active{
    background: rgba(201, 75, 80, .12);
    border-color: rgba(201, 75, 80, .35);
    color: var(--orc-brand);
  }

  /* Legacy responsive overrides (kept, but scoped) */
  @media (max-width: 1200px){ .orc-grid{ grid-template-columns: repeat(5, minmax(0,1fr)); } }
  @media (max-width: 992px) { .orc-grid{ grid-template-columns: repeat(4, minmax(0,1fr)); } }
  @media (max-width: 768px) { .orc-grid{ grid-template-columns: repeat(3, minmax(0,1fr)); } }
  @media (max-width: 520px) { .orc-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); } }

  /* =========================
     ✅ Enhanced Modal UI (scoped)
     ========================= */
  .orc-modal{
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
  .orc-modal.show{ display: flex; animation: orcModalFadeIn 0.2s ease forwards; }
  @keyframes orcModalFadeIn{ to { opacity: 1; } }

  .orc-modal__backdrop{
    position: absolute;
    inset: 0;
    background: rgba(2, 6, 23, 0.88);
    backdrop-filter: blur(4px);
  }

  .orc-modal__dialog{
    position: relative;
    width: min(800px, 100%);
    max-height: 90vh;
    background: var(--orc-card);
    border: 1px solid var(--orc-line);
    border-radius: 20px;
    box-shadow: 0 24px 64px rgba(2, 6, 23, 0.35);
    overflow: hidden;
    transform: translateY(20px) scale(0.98);
    animation: orcModalSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    display: flex;
    flex-direction: column;
  }

  @keyframes orcModalSlideUp{
    to { transform: translateY(0) scale(1); opacity: 1; }
  }

  .orc-modal__header{
    padding: 18px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    border-bottom: 1px solid var(--orc-line);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .orc-modal__title{
    margin: 0;
    font-size: 20px;
    font-weight: 950;
    color: var(--orc-ink);
    letter-spacing: -0.2px;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .orc-modal__close{
    border: none;
    background: rgba(2, 6, 23, 0.04);
    width: 40px;
    height: 40px;
    border-radius: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--orc-muted);
    transition: all 0.2s ease;
    flex-shrink: 0;
    position: relative;
    overflow: hidden;
  }

  .orc-modal__close:hover{
    background: rgba(201, 75, 80, 0.12);
    color: var(--orc-brand);
    transform: rotate(90deg);
  }

  .orc-modal__close:hover::before{
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(201, 75, 80, 0.08);
  }

  .orc-modal__close i{ position: relative; z-index: 1; font-size: 16px; }

  .orc-modal__body{
    padding: 24px;
    display: grid;
    grid-template-columns: 140px 1fr;
    gap: 24px;
    align-items: start;
    overflow-y: auto;
    flex: 1;
  }

  .orc-modal__logo-container{ display: flex; flex-direction: column; gap: 12px; }

  .orc-modal__logo{
    width: 140px;
    height: 140px;
    border: 1px solid var(--orc-line);
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

  .orc-modal__logo:hover{
    transform: translateY(-2px);
    box-shadow: 0 20px 40px rgba(2, 6, 23, 0.15);
    border-color: rgba(201, 75, 80, 0.25);
  }

  .orc-modal__logo img{
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    display: block;
    transition: transform 0.3s ease;
  }
  .orc-modal__logo:hover img{ transform: scale(1.05); }

  .orc-modal__logo-fallback{
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    color: var(--orc-muted);
    font-weight: 900;
    font-size: 42px;
    opacity: 0.6;
  }

  .orc-modal__details{ display: flex; flex-direction: column; gap: 20px; }
  .orc-modal__section{ display: flex; flex-direction: column; gap: 8px; }

  .orc-modal__section-title{
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--orc-brand);
    opacity: 0.8;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .orc-modal__section-title i{ font-size: 11px; }

  .orc-modal__description{
    color: var(--orc-ink);
    font-size: 15px;
    line-height: 1.6;
    max-height: 300px;
    overflow-y: auto;
    padding-right: 8px;
  }
  .orc-modal__description::-webkit-scrollbar{ width: 4px; }
  .orc-modal__description::-webkit-scrollbar-track{ background: rgba(2, 6, 23, 0.04); border-radius: 4px; }
  .orc-modal__description::-webkit-scrollbar-thumb{ background: rgba(2, 6, 23, 0.12); border-radius: 4px; }
  .orc-modal__description p{ margin: 0 0 12px 0; }
  .orc-modal__description p:last-child{ margin-bottom: 0; }

  .orc-modal__info-grid{
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin-top: 4px;
  }

  .orc-modal__info-item{
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: rgba(2, 6, 23, 0.02);
    border-radius: 10px;
    border: 1px solid var(--orc-line);
  }

  .orc-modal__info-item i{
    color: var(--orc-brand);
    opacity: 0.8;
    font-size: 14px;
    width: 16px;
  }

  .orc-modal__info-label{
    font-size: 13px;
    font-weight: 600;
    color: var(--orc-muted);
    white-space: nowrap;
  }

  .orc-modal__info-value{
    font-size: 13px;
    font-weight: 700;
    color: var(--orc-ink);
    margin-left: auto;
  }

  .orc-modal__footer{
    padding: 18px 24px;
    border-top: 1px solid var(--orc-line);
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

  .orc-modal__footer-left{
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--orc-muted);
    font-size: 13px;
  }

  .orc-modal__footer-right{ display: flex; align-items: center; gap: 10px; }

  .orc-modal__btn{
    border: 1px solid var(--orc-line);
    background: var(--orc-card);
    color: var(--orc-ink);
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

  .orc-modal__btn:hover{
    background: rgba(2, 6, 23, 0.03);
    transform: translateY(-1px);
    box-shadow: 0 12px 24px rgba(2, 6, 23, 0.12);
  }

  .orc-modal__btn.primary{
    background: linear-gradient(135deg, rgba(201, 75, 80, 0.15), rgba(201, 75, 80, 0.08));
    border-color: rgba(201, 75, 80, 0.35);
    color: var(--orc-brand);
    font-weight: 800;
  }

  .orc-modal__btn.primary:hover{
    background: linear-gradient(135deg, rgba(201, 75, 80, 0.22), rgba(201, 75, 80, 0.15));
    border-color: rgba(201, 75, 80, 0.5);
    box-shadow: 0 12px 24px rgba(201, 75, 80, 0.15);
  }

  .orc-modal__btn.secondary{
    background: rgba(2, 6, 23, 0.02);
    border-color: rgba(2, 6, 23, 0.08);
  }

  .orc-modal__btn.secondary:hover{
    background: rgba(2, 6, 23, 0.05);
    border-color: rgba(2, 6, 23, 0.15);
  }

  .orc-modal__btn i{ font-size: 13px; }

  .orc-modal__loading{
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 24px;
    gap: 16px;
  }
  .orc-modal__loading.show{ display: flex; }

  .orc-modal__loading-spinner{
    width: 40px;
    height: 40px;
    border: 3px solid rgba(2, 6, 23, 0.08);
    border-top-color: var(--orc-brand);
    border-radius: 50%;
    animation: orcModalSpinner 0.8s linear infinite;
  }

  @keyframes orcModalSpinner{ to { transform: rotate(360deg); } }

  @media (max-width: 768px){
    .orc-modal__body{ grid-template-columns: 1fr; gap: 20px; }
    .orc-modal__logo-container{ align-items: center; }
    .orc-modal__logo{ width: 160px; height: 160px; }
    .orc-modal__footer{ flex-direction: column; align-items: stretch; gap: 12px; }
    .orc-modal__footer-right{ width: 100%; }
    .orc-modal__btn{ flex: 1; justify-content: center; min-width: 0; }
  }

  @media (max-width: 480px){
    .orc-modal__header{ padding: 16px 20px; }
    .orc-modal__body{ padding: 20px; }
    .orc-modal__footer{ padding: 16px 20px; }
    .orc-modal__btn{ padding: 10px 14px; font-size: 13px; }
    .orc-modal__info-grid{ grid-template-columns: 1fr; }
  }
</style>

<div class="orc-wrap orc-scope" data-api="{{ url('/api/public/recruiters') }}">
  <div class="orc-head">
    <div>
      <h1 class="orc-title">Our Recruiters</h1>
      <div class="orc-sub" id="recSub">Companies that recruit from our campus</div>
    </div>

    <div class="orc-tools">
      <div class="orc-search">
        <i class="fa fa-magnifying-glass"></i>
        <input id="recSearch" type="search" placeholder="Search company…">
      </div>
      <div class="orc-chip" title="Total results">
        <i class="fa-solid fa-building" style="opacity:.85"></i>
        <span id="recCount">—</span>
      </div>
    </div>
  </div>

  <div id="recGrid" class="orc-grid" style="display:none;"></div>

  <div id="recSkeleton" class="orc-skeleton"></div>
  <div id="recState" class="orc-state" style="display:none;"></div>

  <div class="orc-pagination">
    <div id="recPager" class="orc-pager" style="display:none;"></div>
  </div>
</div>

{{-- ✅ Enhanced Modal --}}
<div id="recModal"
     class="orc-modal orc-scope"
     aria-hidden="true"
     role="dialog"
     aria-modal="true"
     aria-labelledby="recModalTitle"
     aria-describedby="recModalDesc">
  <div class="orc-modal__backdrop" data-close="1"></div>

  <div class="orc-modal__dialog" role="document">
    <div class="orc-modal__header">
      <h3 id="recModalTitle" class="orc-modal__title">Company Details</h3>
      <button type="button" class="orc-modal__close" id="recModalClose" aria-label="Close modal">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="orc-modal__loading" id="recModalLoading">
      <div class="orc-modal__loading-spinner"></div>
      <div style="color: var(--orc-muted); font-size: 14px;">Loading company details...</div>
    </div>

    <div class="orc-modal__body" id="recModalBody">
      <div class="orc-modal__logo-container">
        <div class="orc-modal__logo">
          <img id="recModalLogo" src="" alt="Company Logo" style="display: none;">
          <div id="recModalLogoFallback" class="orc-modal__logo-fallback">
            <i class="fa-solid fa-building"></i>
          </div>
        </div>
        <div class="orc-modal__info-item">
          <i class="fa-solid fa-calendar"></i>
          <span class="orc-modal__info-label">Added</span>
          <span id="recModalDate" class="orc-modal__info-value">—</span>
        </div>
      </div>

      <div class="orc-modal__details">
        <div class="orc-modal__section">
          <div class="orc-modal__section-title">
            <i class="fa-solid fa-circle-info"></i>
            About Company
          </div>
          <div id="recModalDesc" class="orc-modal__description">
            <p>No description available.</p>
          </div>
        </div>

        <div class="orc-modal__info-grid">
          <div class="orc-modal__info-item">
            <i class="fa-solid fa-industry"></i>
            <span class="orc-modal__info-label">Industry</span>
            <span id="recModalIndustry" class="orc-modal__info-value">—</span>
          </div>
          <div class="orc-modal__info-item">
            <i class="fa-solid fa-location-dot"></i>
            <span class="orc-modal__info-label">Location</span>
            <span id="recModalLocation" class="orc-modal__info-value">—</span>
          </div>
          <div class="orc-modal__info-item">
            <i class="fa-solid fa-users"></i>
            <span class="orc-modal__info-label">Hired</span>
            <span id="recModalHired" class="orc-modal__info-value">—</span>
          </div>
        </div>
      </div>
    </div>

    <div class="orc-modal__footer">
      <div class="orc-modal__footer-left">
        <i class="fa-solid fa-clock"></i>
        <span>Last updated: <span id="recModalUpdated">—</span></span>
      </div>
      <div class="orc-modal__footer-right">
        <a id="recModalWebsite"
           class="orc-modal__btn primary"
           href="#"
           target="_blank"
           rel="noopener noreferrer"
           style="display: none;">
          <i class="fa-solid fa-external-link"></i>
          Visit Website
        </a>
        <button type="button" class="orc-modal__btn secondary" style="display:none" data-close="1">
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

    const root = document.querySelector('.orc-wrap');
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
          modalDesc.innerHTML = '<p style="color: var(--orc-muted); font-style: italic;">No description available.</p>';
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

      const heights = [160, 190, 220, 170, 210, 180, 240, 165, 205, 175, 230, 185];
      sk.innerHTML = heights.map(h => `<div class="orc-sk-tile" style="height:${h}px"></div>`).join('');
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
          <div class="orc-tile"
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
            <span class="orc-tile__inner">
              ${fullLogo
                ? `<img src="${esc(fullLogo)}" alt="${esc(name)}" loading="lazy">`
                : `<div class="orc-tile__fallback">${esc(name)}</div>`
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
        const cls = active ? 'orc-pagebtn active' : 'orc-pagebtn';
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
        const b = e.target.closest('button.orc-pagebtn[data-page]');
        if (!b) return;
        const p = parseInt(b.dataset.page, 10);
        if (!p || Number.isNaN(p) || p === state.page) return;
        state.page = p;
        load();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });

      document.addEventListener('click', (e) => {
        const tile = e.target.closest('.orc-tile');
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
        const tile = e.target.closest?.('.orc-tile');
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
