{{-- resources/views/landing/our-recruiters.blade.php --}}
<style>
  /* =========================================================
    ✅ Recruiters (Scoped / No :root / No global body rules)
    - UI structure matches Announcements reference (header/search/dept/pager)
    - Dept dropdown UI improved (pill, icon, caret)
    - Dept filtering (frontend) + deep-link ?d-{uuid}
    - Keeps: masonry-ish logo tiles (different size boxes) + enhanced modal
    - ✅ Scoped variables applied to BOTH wrapper + modal via .orc-scope
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
    max-width: 1320px;               /* ✅ match reference width */
    margin: 18px auto 54px;
    padding: 0 12px;
    background: transparent;
    position: relative;
    overflow: visible;
  }

  /* Header */
  .orc-head{
    background: var(--orc-card);
    border: 1px solid var(--orc-line);
    border-radius: 16px;
    box-shadow: var(--orc-shadow);
    padding: 14px 16px;
    margin-bottom: 16px;

    display:flex;
    gap: 12px;
    align-items: center;
    justify-content: space-between;

    /* ✅ keep header in one row (desktop) */
    flex-wrap: nowrap;
  }

  /* keep left block stable */
  .orc-head > div:first-child{ flex: 0 0 auto; }

  .orc-title{
    margin: 0;
    font-weight: 950;
    letter-spacing: .2px;
    color: var(--orc-ink);
    font-size: 28px;
    display:flex;
    align-items:center;
    gap: 10px;
  }
  .orc-title i{ color: var(--orc-brand); }

  .orc-sub{
    margin: 6px 0 0;
    color: var(--orc-muted);
    font-size: 14px;
  }

  .orc-tools{
    display:flex;
    gap: 10px;
    align-items:center;

    /* ✅ keep tools in one row (desktop) */
    flex-wrap: nowrap;
    justify-content: flex-end;
    flex: 1 1 auto;
  }

  /* Search (pill) */
  .orc-search{
    position: relative;
    min-width: 260px;
    max-width: 520px;
    flex: 1 1 320px;
  }
  .orc-search i{
    position:absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    opacity: .65;
    color: var(--orc-muted);
    pointer-events:none;
  }
  .orc-search input{
    width:100%;
    height: 42px;
    border-radius: 999px;
    padding: 11px 12px 11px 42px;
    border: 1px solid var(--orc-line);
    background: var(--orc-card);
    color: var(--orc-ink);
    outline: none;
  }
  .orc-search input:focus{
    border-color: rgba(201,75,80,.55);
    box-shadow: 0 0 0 4px rgba(201,75,80,.18);
  }

  /* ✅ Dept dropdown (pill) */
  .orc-select{
    position: relative;
    min-width: 260px;
    max-width: 360px;
    flex: 0 1 320px;
  }
  .orc-select__icon{
    position:absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    opacity: .70;
    color: var(--orc-muted);
    pointer-events:none;
    font-size: 14px;
  }
  .orc-select__caret{
    position:absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    opacity: .70;
    color: var(--orc-muted);
    pointer-events:none;
    font-size: 12px;
  }
  .orc-select select{
    width:100%;
    height: 42px;
    border-radius: 999px;
    padding: 10px 38px 10px 42px;
    border: 1px solid var(--orc-line);
    background: var(--orc-card);
    color: var(--orc-ink);
    outline: none;

    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
  }
  .orc-select select:focus{
    border-color: rgba(201,75,80,.55);
    box-shadow: 0 0 0 4px rgba(201,75,80,.18);
  }

  /* =========================================================
     ✅ Masonry-style grid (different size boxes) — RESTORED
  ========================================================= */
  .orc-grid{
    display: grid;
    /* gap: 14px; */                 /* keep as you had */
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
    position: relative;
    outline: none;
  }

  .orc-tile:nth-child(12n + 2),
  .orc-tile:nth-child(12n + 4),
  .orc-tile:nth-child(12n + 6),
  .orc-tile:nth-child(12n + 7),
  .orc-tile:nth-child(12n + 9),
  .orc-tile:nth-child(12n + 11){
    grid-column: span 2;
  }

  .orc-tile:hover,
  .orc-tile:focus{
    transform: translateY(-3px);
    box-shadow: 0 4px 6px rgba(2, 6, 23, .08), 0 16px 28px rgba(2, 6, 23, .12);
    border-color: rgba(143, 47, 47, .20);
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

  /* Skeleton (shimmer) */
  .orc-skeleton{
    display:grid;
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
    height: 110px;
  }
  .orc-sk-tile:before{
    content:'';
    position:absolute; inset:0;
    transform: translateX(-60%);
    background: linear-gradient(90deg, transparent, rgba(148,163,184,.22), transparent);
    animation: orcSkMove 1.15s ease-in-out infinite;
  }
  @keyframes orcSkMove{ to{ transform: translateX(60%);} }

  .orc-sk-tile:nth-child(6n + 1){ --w: 1; }
  .orc-sk-tile:nth-child(6n + 2){ --w: 2; }
  .orc-sk-tile:nth-child(6n + 3){ --w: 1; }
  .orc-sk-tile:nth-child(6n + 4){ --w: 2; }
  .orc-sk-tile:nth-child(6n + 5){ --w: 3; }
  .orc-sk-tile:nth-child(6n + 6){ --w: 3; }

  @media (max-width: 992px){
    /* allow wrap on smaller screens to avoid overflow */
    .orc-head{ flex-wrap: wrap; align-items: flex-end; }
    .orc-tools{ flex-wrap: wrap; justify-content: flex-start; }

    .orc-grid, .orc-skeleton{ grid-template-columns: repeat(6, minmax(0,1fr)); }
    .orc-tile, .orc-sk-tile{ grid-column: span 3; }
  }
  @media (max-width: 520px){
    .orc-grid, .orc-skeleton{ grid-template-columns: repeat(2, minmax(0,1fr)); }
    .orc-tile, .orc-sk-tile{ grid-column: span 2; }
  }

  /* Legacy responsive overrides (kept, but scoped) */
  @media (max-width: 1200px){ .orc-grid{ grid-template-columns: repeat(5, minmax(0,1fr)); } }
  @media (max-width: 992px) { .orc-grid{ grid-template-columns: repeat(4, minmax(0,1fr)); } }
  @media (max-width: 768px) { .orc-grid{ grid-template-columns: repeat(3, minmax(0,1fr)); } }
  @media (max-width: 520px) { .orc-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); } }

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
  .orc-pagination{ display:flex; justify-content:center; margin-top: 18px; }
  .orc-pagination .orc-pager{
    display:flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items:center;
    justify-content:center;
    padding: 10px;
  }
  .orc-pagebtn{
    border:1px solid var(--orc-line);
    background: var(--orc-card);
    color: var(--orc-ink);
    border-radius: 12px;
    padding: 9px 12px;
    font-size: 13px;
    font-weight: 950;
    box-shadow: 0 8px 18px rgba(2,6,23,.06);
    cursor:pointer;
    user-select:none;
  }
  .orc-pagebtn:hover{ background: rgba(2,6,23,.03); }
  .orc-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
  .orc-pagebtn.active{
    background: rgba(201,75,80,.12);
    border-color: rgba(201,75,80,.35);
    color: var(--orc-brand);
  }

  @media (max-width: 640px){
    .orc-title{ font-size: 24px; }
    .orc-search{ min-width: 220px; flex: 1 1 240px; }
    .orc-select{ min-width: 220px; flex: 1 1 240px; }
  }

  /* =========================
     ✅ Enhanced Modal UI (scoped) — kept
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
  class="orc-wrap orc-scope"
  data-api="{{ url('/api/public/recruiters') }}"
  data-dept-api="{{ url('/api/public/departments') }}"
>
  <div class="orc-head">
    <div>
      <h1 class="orc-title"><i class="fa-solid fa-building"></i>Our Recruiters</h1>
      <div class="orc-sub" id="recSub">Companies that recruit from our campus</div>
    </div>

    <div class="orc-tools">
      <div class="orc-search">
        <i class="fa fa-magnifying-glass"></i>
        <input id="recSearch" type="search" placeholder="Search recruiters (name/industry/location)…">
      </div>

      {{-- ✅ Dept dropdown added --}}
      <div class="orc-select" title="Filter by department">
        <i class="fa-solid fa-building-columns orc-select__icon"></i>
        <select id="recDept" aria-label="Filter by department">
          <option value="">All Departments</option>
        </select>
        <i class="fa-solid fa-chevron-down orc-select__caret"></i>
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

{{-- ✅ Enhanced Modal (kept) --}}
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
          <img id="recModalLogo" src="" alt="Company Logo" style="display:none;">
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
           style="display:none;">
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
  // allow safe include: init each .orc-wrap once
  const roots = Array.from(document.querySelectorAll('.orc-wrap.orc-scope'))
    .filter(r => r.getAttribute('data-orc-inited') !== '1');

  if (!roots.length) return;

  roots.forEach((root) => {
    root.setAttribute('data-orc-inited', '1');

    const API = root.getAttribute('data-api') || '/api/public/recruiters';
    const DEPT_API = root.getAttribute('data-dept-api') || '/api/public/departments';

    const $ = (id) => document.getElementById(id);

    const els = {
      grid: $('recGrid'),
      skel: $('recSkeleton'),
      state: $('recState'),
      pager: $('recPager'),
      search: $('recSearch'),
      dept: $('recDept'),
      sub: $('recSub'),
    };

    const state = {
      page: 1,
      perPage: 24,
      lastPage: 1,
      total: 0,
      q: '',
      deptUuid: '',
      deptId: null,
      deptName: '',
    };

    let activeController = null;

    // cache
    let allRecruiters = null;          // array
    let deptByUuid = new Map();        // uuid -> {id, title, uuid}
    let itemByKey = new Map();         // key -> recruiter object for modal

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

    function esc(str){
      return (str ?? '').toString().replace(/[&<>"']/g, s => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
      }[s]));
    }
    function escAttr(str){
      return (str ?? '').toString().replace(/"/g, '&quot;');
    }

    function stripHtml(html){
      const raw = String(html || '')
        .replace(/<\s*br\s*\/?>/gi, ' ')
        .replace(/<\/\s*(p|div|li|h[1-6]|tr|td|th|section|article)\s*>/gi, '$& ')
        .replace(/<\s*(p|div|li|h[1-6]|tr|td|th|section|article)\b[^>]*>/gi, ' ');
      const div = document.createElement('div');
      div.innerHTML = raw;
      return (div.textContent || div.innerText || '').replace(/\s+/g, ' ').trim();
    }

    function normalizeUrl(url){
      const u = (url || '').toString().trim();
      if (!u) return '';
      if (/^(data:|blob:|https?:\/\/)/i.test(u)) return u;
      if (u.startsWith('//')) return 'https:' + u;
      if (u.startsWith('/')) return window.location.origin + u;

      // if looks like domain, assume https
      if (u.includes('.') && !u.includes(' ')) return 'https://' + u.replace(/^\/+/, '');
      return window.location.origin + '/' + u.replace(/^\/+/, '');
    }

    function pick(obj, keys){
      for (const k of keys){
        const v = obj?.[k];
        if (v !== null && v !== undefined && String(v).trim() !== '') return v;
      }
      return '';
    }

    function formatDate(dateString){
      if (!dateString) return '—';
      try{
        const d = new Date(dateString);
        if (Number.isNaN(d.getTime())) return String(dateString);
        return new Intl.DateTimeFormat('en-IN', { day:'2-digit', month:'short', year:'numeric' }).format(d);
      } catch {
        return String(dateString);
      }
    }

    function formatNumber(num){
      if (num === null || num === undefined || num === '') return '—';
      const n = (typeof num === 'number') ? num : parseFloat(String(num).replace(/[^\d.\-]/g,''));
      if (Number.isNaN(n)) return String(num);
      return n.toLocaleString();
    }

    function showModalLoading(show){
      if (modalLoading) modalLoading.style.display = show ? 'flex' : 'none';
      if (modalBody) modalBody.style.display = show ? 'none' : 'grid';
    }

    function setBodyScroll(lock){
      document.documentElement.style.overflow = lock ? 'hidden' : '';
      document.body.style.overflow = lock ? 'hidden' : '';
    }

    function openModalFromItem(it){
      if (!modal) return;

      showModalLoading(true);

      const name = (pick(it, ['name','title','company','label']) || 'Company').toString().trim();
      const descRaw = pick(it, ['description','about','summary','content','details','body']) || '';
      const desc = stripHtml(descRaw);

      const logoRaw =
        pick(it, ['logo_url','image_url','image_full_url','logo','image','src','url']) ||
        (it?.attachment?.url ?? '');
      const logo = logoRaw ? normalizeUrl(logoRaw) : '';

      const websiteRaw = pick(it, ['website','link','web_url','site','company_url']) || '';
      const website = websiteRaw ? normalizeUrl(websiteRaw) : '';

      const industry = pick(it, ['industry','sector','category']) || '';
      const location = pick(it, ['location','city','country','headquarters']) || '';
      const hired = pick(it, ['students_hired','hired_count','placements']) || '';

      const created = pick(it, ['created_at','date_added','joined_date']) || '';
      const updated = pick(it, ['updated_at','last_updated']) || '';

      if (modalTitle) modalTitle.textContent = name;

      if (modalDesc){
        if (desc){
          const chunks = desc.split(/\n\s*\n/g).map(s => s.trim()).filter(Boolean);
          modalDesc.innerHTML = chunks.length
            ? chunks.map(p => `<p>${esc(p)}</p>`).join('')
            : `<p style="color: var(--orc-muted); font-style: italic;">No description available.</p>`;
        } else {
          modalDesc.innerHTML = '<p style="color: var(--orc-muted); font-style: italic;">No description available.</p>';
        }
      }

      if (modalIndustry) modalIndustry.textContent = industry || '—';
      if (modalLocation) modalLocation.textContent = location || '—';
      if (modalHired) modalHired.textContent = hired !== '' ? formatNumber(hired) : '—';
      if (modalDate) modalDate.textContent = created ? formatDate(created) : '—';
      if (modalUpdated) modalUpdated.textContent = updated ? formatDate(updated) : '—';

      if (modalWebsite){
        if (website){
          modalWebsite.href = website;
          modalWebsite.style.display = 'inline-flex';
        } else {
          modalWebsite.style.display = 'none';
        }
      }

      // logo handling
      if (modalLogo && modalLogoFallback){
        modalLogo.onload = null;
        modalLogo.onerror = null;
        modalLogo.style.display = 'none';

        if (logo){
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
          modalLogo.alt = name + ' Logo';
        } else {
          modalLogoFallback.style.display = 'flex';
          modalLogoFallback.innerHTML = '<i class="fa-solid fa-building"></i>';
          showModalLoading(false);
        }
      }

      modal.classList.add('show');
      modal.setAttribute('aria-hidden', 'false');
      setBodyScroll(true);

      // safety fallback if image never triggers events
      setTimeout(() => showModalLoading(false), 400);
    }

    function closeModal(){
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

    function showSkeleton(){
      const sk = els.skel, st = els.state, grid = els.grid, pager = els.pager;

      if (grid) grid.style.display = 'none';
      if (pager) pager.style.display = 'none';
      if (st) st.style.display = 'none';

      if (!sk) return;
      sk.style.display = '';
      sk.innerHTML = Array.from({length: 18}).map(() => `<div class="orc-sk-tile"></div>`).join('');
    }

    function hideSkeleton(){
      const sk = els.skel;
      if (!sk) return;
      sk.style.display = 'none';
      sk.innerHTML = '';
    }

    async function fetchJson(url){
      if (activeController) activeController.abort();
      activeController = new AbortController();

      const res = await fetch(url, { headers: { 'Accept':'application/json' }, signal: activeController.signal });
      const js = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(js?.message || ('Request failed: ' + res.status));
      return js;
    }

    function extractDeptUuidFromUrl(){
      // matches "?d-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" anywhere in URL/search
      const hay = (window.location.search || '') + ' ' + (window.location.href || '');
      const m = hay.match(/d-([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/i);
      return m ? m[1] : '';
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
        if (els.sub) els.sub.textContent = 'Companies that recruit from our campus';
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
          ? ('Recruiters for ' + state.deptName)
          : 'Recruiters (filtered)';
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
            title: (d?.title ?? d?.name ?? '').toString().trim(),
            active: (d?.active ?? 1),
          }))
          .filter(x => x.uuid && x.title && String(x.active) === '1');

        deptByUuid = new Map(depts.map(d => [d.uuid, d]));

        depts.sort((a,b) => a.title.localeCompare(b.title));

        sel.innerHTML = `<option value="">All Departments</option>` + depts
          .map(d => `<option value="${escAttr(d.uuid)}">${esc(d.title)}</option>`)
          .join('');

        sel.value = '';
      } catch (e){
        console.warn('Departments load failed:', e);
        sel.innerHTML = `<option value="">All Departments</option>`;
        sel.value = '';
      }
    }

    async function ensureRecruitersLoaded(force=false){
      if (allRecruiters && !force) return;

      showSkeleton();

      try{
        // fetch a larger page so frontend filtering always works
        const u = new URL(API, window.location.origin);
        u.searchParams.set('page', '1');
        u.searchParams.set('per_page', '500');
        u.searchParams.set('sort', 'created_at');
        u.searchParams.set('direction', 'desc');

        const js = await fetchJson(u.toString());
        const items = Array.isArray(js?.data) ? js.data : (Array.isArray(js) ? js : []);
        allRecruiters = items;
      } finally {
        hideSkeleton();
      }
    }

    function applyFilterAndSearch(){
      const q = (state.q || '').toString().trim().toLowerCase();
      let items = Array.isArray(allRecruiters) ? allRecruiters.slice() : [];

      // ✅ Dept filter:
      // when dept selected -> show ONLY recruiters that match dept id/uuid
      if (state.deptUuid && state.deptId !== null && state.deptId !== undefined && String(state.deptId) !== ''){
        const deptIdStr = String(state.deptId);
        const deptUuidStr = String(state.deptUuid);

        items = items.filter(it => {
          const did = (it?.department_id === null || it?.department_id === undefined) ? '' : String(it.department_id);
          const duu = (it?.department_uuid === null || it?.department_uuid === undefined) ? '' : String(it.department_uuid);
          return (did === deptIdStr) || (duu && duu === deptUuidStr);
        });
      } else if (state.deptUuid){
        // if deptId missing, try uuid-only
        const deptUuidStr = String(state.deptUuid);
        items = items.filter(it => String(it?.department_uuid || '') === deptUuidStr);
      }

      // Search
      if (q){
        items = items.filter(it => {
          const name = String(pick(it, ['name','title','company','label']) || '').toLowerCase();
          const industry = String(pick(it, ['industry','sector','category']) || '').toLowerCase();
          const location = String(pick(it, ['location','city','country','headquarters']) || '').toLowerCase();
          const desc = stripHtml(pick(it, ['description','about','summary','content','details','body']) || '').toLowerCase();
          return name.includes(q) || industry.includes(q) || location.includes(q) || desc.includes(q);
        });
      }

      return items;
    }

    function bindTileImages(gridEl){
      gridEl.querySelectorAll('img.orc-logo').forEach(img => {
        const tile = img.closest('.orc-tile');
        const fallback = tile ? tile.querySelector('.orc-tile__fallback') : null;

        if (img.complete && img.naturalWidth > 0){
          if (fallback) fallback.style.display = 'none';
          return;
        }

        img.addEventListener('load', () => {
          if (fallback) fallback.style.display = 'none';
        }, { once:true });

        img.addEventListener('error', () => {
          img.remove();
          if (fallback) fallback.style.display = 'flex';
        }, { once:true });
      });
    }

    function render(items){
      const grid = els.grid, st = els.state;
      if (!grid || !st) return;

      if (!items.length){
        grid.style.display = 'none';
        st.style.display = '';
        const deptLine = state.deptName
          ? `<div style="margin-top:6px;font-size:12.5px;opacity:.95;">Department: <b>${esc(state.deptName)}</b></div>`
          : '';
        st.innerHTML = `
          <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
            <i class="fa-regular fa-face-frown"></i>
          </div>
          No recruiters found.
          ${deptLine}
        `;
        return;
      }

      st.style.display = 'none';
      grid.style.display = '';
      itemByKey = new Map();

      grid.innerHTML = items.map((it, idx) => {
        const key = String(it?.uuid || it?.id || ('idx_' + idx));
        itemByKey.set(key, it);

        const name = pick(it, ['name','title','company','label']) || 'Recruiter';
        const logoRaw =
          pick(it, ['logo_url','image_url','image_full_url','logo','image','src','url']) ||
          (it?.attachment?.url ?? '');
        const logo = logoRaw ? normalizeUrl(logoRaw) : '';

        return `
          <div class="orc-tile"
               role="button"
               tabindex="0"
               data-key="${escAttr(key)}"
               aria-label="View ${escAttr(name)} details">
            <div class="orc-tile__inner">
              <div class="orc-tile__fallback">${esc(name)}</div>
              ${logo ? `<img class="orc-logo" src="${escAttr(logo)}" alt="${escAttr(name)}" loading="lazy">` : ``}
            </div>
          </div>
        `;
      }).join('');

      bindTileImages(grid);
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
        const cls = active ? 'orc-pagebtn active' : 'orc-pagebtn';
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

      state.total = filtered.length;
      state.lastPage = Math.max(1, Math.ceil(filtered.length / state.perPage));
      if (state.page > state.lastPage) state.page = state.lastPage;

      const start = (state.page - 1) * state.perPage;
      const pageItems = filtered.slice(start, start + state.perPage);

      render(pageItems);
      renderPager();
    }

    // tile click -> modal
    function openFromTile(tile){
      const key = tile?.getAttribute('data-key') || '';
      const it = itemByKey.get(key);
      if (!it) return;
      openModalFromItem(it);
    }

    document.addEventListener('click', (e) => {
      const tile = e.target.closest('.orc-tile');
      if (!tile) return;
      openFromTile(tile);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key !== 'Enter' && e.key !== ' ') return;
      const tile = e.target.closest?.('.orc-tile');
      if (!tile) return;
      e.preventDefault();
      openFromTile(tile);
    });

    // pagination click
    document.addEventListener('click', (e) => {
      const b = e.target.closest('button.orc-pagebtn[data-page]');
      if (!b) return;
      const p = parseInt(b.dataset.page, 10);
      if (!p || Number.isNaN(p) || p === state.page) return;
      state.page = p;
      repaint();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // init
    document.addEventListener('DOMContentLoaded', async () => {
      await loadDepartments();

      // ✅ deep-link ?d-{uuid}
      const deepDeptUuid = extractDeptUuidFromUrl();
      if (deepDeptUuid && deptByUuid.has(deepDeptUuid)){
        setDeptSelection(deepDeptUuid);
      } else {
        setDeptSelection('');
      }

      // load once, filter client-side
      await ensureRecruitersLoaded(false);
      repaint();

      // search (debounced)
      let t = null;
      els.search && els.search.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(() => {
          state.q = (els.search.value || '').trim();
          state.page = 1;
          repaint();
        }, 260);
      });

      // dept change
      els.dept && els.dept.addEventListener('change', () => {
        const v = (els.dept.value || '').toString();
        if (v === '__loading') return;

        if (!v) setDeptSelection('');
        else setDeptSelection(v);

        state.page = 1;
        repaint();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    });
  });
})();
</script>
