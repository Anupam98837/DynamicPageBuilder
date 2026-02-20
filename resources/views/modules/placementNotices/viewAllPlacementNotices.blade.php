{{-- resources/views/public/placementNotices/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name','College Portal') }} — Placement Notices</title>

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  {{-- Common UI tokens --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    .pnx-wrap{
      /* scoped tokens */
      --pnx-brand: var(--primary-color, #9E363A);
      --pnx-ink: #0f172a;
      --pnx-muted: #64748b;
      --pnx-bg: var(--page-bg, #ffffff);
      --pnx-card: var(--surface, #ffffff);
      --pnx-line: var(--line-soft, rgba(15,23,42,.10));
      --pnx-shadow: 0 10px 24px rgba(2,6,23,.08);

      /* card sizing */
      --pnx-card-h: 426.4px;
      --pnx-media-h: 240px;

      max-width: 1320px;
      margin: 18px auto 54px;
      padding: 0 12px;
      background: transparent;
      position: relative;
      overflow: visible;
    }

    /* Header */
    .pnx-head{
      background: var(--pnx-card);
      border: 1px solid var(--pnx-line);
      border-radius: 16px;
      box-shadow: var(--pnx-shadow);
      padding: 14px 16px;
      margin-bottom: 16px;

      display:flex;
      gap: 12px;
      align-items: flex-end;
      justify-content: space-between;
      flex-wrap: wrap;
    }
    .pnx-title{
      margin: 0;
      font-weight: 950;
      letter-spacing: .2px;
      color: var(--pnx-ink);
      font-size: 28px;
      display:flex;
      align-items:center;
      gap: 10px;
    }
    .pnx-title i{ color: var(--pnx-brand); }
    .pnx-sub{
      margin: 6px 0 0;
      color: var(--pnx-muted);
      font-size: 14px;
    }

    /* ✅ FIX: keep search + dropdown side-by-side (no wrapping on desktop) */
    .pnx-tools{
      display:flex;
      gap: 10px;
      align-items:center;
      flex-wrap: nowrap;     /* ✅ was wrap */
    }

    /* Search */
    .pnx-search{
      position: relative;
      min-width: 220px;      /* ✅ was 260 */
      max-width: 360px;
      flex: 1 1 0;           /* ✅ was 0 1 320 */
      min-width: 0;          /* ✅ allow shrinking inside nowrap flex row */
    }
    .pnx-search i{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .65;
      color: var(--pnx-muted);
      pointer-events:none;
    }
    .pnx-search input{
      width:100%;
      height: 42px;
      border-radius: 999px;
      padding: 11px 12px 11px 42px;
      border: 1px solid var(--pnx-line);
      background: var(--pnx-card);
      color: var(--pnx-ink);
      outline: none;
    }
    .pnx-search input:focus{
      border-color: rgba(201,75,80,.55);
      box-shadow: 0 0 0 4px rgba(201,75,80,.18);
    }

    /* ✅ Dept dropdown */
    .pnx-select{
      position: relative;
      min-width: 220px;      /* ✅ was 260 */
      max-width: 360px;
      flex: 1 1 0;           /* ✅ was 0 1 320 */
      min-width: 0;          /* ✅ allow shrinking */
    }
    .pnx-select__icon{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .70;
      color: var(--pnx-muted);
      pointer-events:none;
      font-size: 14px;
    }
    .pnx-select__caret{
      position:absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .70;
      color: var(--pnx-muted);
      pointer-events:none;
      font-size: 12px;
    }
    .pnx-select select{
      width: 100%;
      height: 42px;
      border-radius: 999px;
      padding: 10px 38px 10px 42px;
      border: 1px solid var(--pnx-line);
      background: var(--pnx-card);
      color: var(--pnx-ink);
      outline: none;

      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
    }
    .pnx-select select:focus{
      border-color: rgba(201,75,80,.55);
      box-shadow: 0 0 0 4px rgba(201,75,80,.18);
    }

    /* Grid */
    .pnx-grid{
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 18px;
      align-items: stretch;
    }

    /* Card */
    .pnx-card {
  width: 100%;
  height: auto;          /* ✅ was: var(--pnx-card-h) — fixed px breaks mobile */
  min-height: var(--pnx-card-h);
  position: relative;
  display: flex;
  flex-direction: column;
  border: 1px solid rgba(2,6,23,.08);
  border-radius: 16px;
  background: #fff;
  box-shadow: var(--pnx-shadow);
  overflow: hidden;
  transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
  will-change: transform;
}

    .pnx-card:hover{
      transform: translateY(-2px);
      box-shadow: 0 16px 34px rgba(2,6,23,.12);
      border-color: rgba(158,54,58,.22);
    }
.pnx-media {
  width: 100%;
  height: var(--pnx-media-h);
  flex: 0 0 auto;
  background: var(--pnx-brand);
  position: relative;
  overflow: hidden;
  user-select: none;
}
    .pnx-media .pnx-fallback{
      position:absolute;
      inset:0;
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-weight:950;
      font-size: 26px;
      letter-spacing:.2px;
      z-index: 0;
      padding: 0 16px;
      text-align:center;
      line-height: 1.12;
      display:-webkit-box;
      -webkit-line-clamp:3;
      -webkit-box-orient:vertical;
      overflow:hidden;
    }
    .pnx-media img{
      position:absolute;
      inset:0;
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
      z-index: 1;
    }

    /* pill on media */
    .pnx-pill{
      position:absolute;
      left: 12px;
      top: 12px;
      z-index: 3;
      padding: 6px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 950;
      background: rgba(0,0,0,.55);
      color: #fff;
      backdrop-filter: blur(6px);
    }

    .pnx-body{
      padding: 16px 16px 14px;
      display:flex;
      flex-direction:column;
      flex: 1 1 auto;
      min-height: 0;
    }

    .pnx-h {
  font-size: 18px;             /* ✅ slightly smaller so 2 lines fit more text */
  line-height: 1.3;
  font-weight: 950;
  margin: 0 0 10px 0;
  color: var(--pnx-ink);

  display: -webkit-box;
  -webkit-line-clamp: 3;       /* ✅ was 2 — give it breathing room */
  -webkit-box-orient: vertical;
  overflow: hidden;

  overflow-wrap: anywhere;
  word-break: break-word;
}
    .pnx-meta{
      display:flex;
      flex-direction:column;
      gap: 6px;
      margin: 0 0 10px 0;
      color:#334155;
      font-weight: 800;
      font-size: 13px;
    }
    .pnx-meta .rowx{
      display:flex;
      align-items:center;
      gap: 8px;
      min-height: 18px;
      overflow:hidden;
    }
    .pnx-meta i{
      width: 16px;
      text-align:center;
      color: var(--pnx-muted);
      opacity: .95;
      flex: 0 0 auto;
    }
    .pnx-meta span{
      display:block;
      overflow:hidden;
      text-overflow:ellipsis;
      white-space:nowrap;
      min-width: 0;
    }

    .pnx-p{
      margin:0;
      color:#475569;
      font-size: 14.5px;
      line-height: 1.7;

      display:-webkit-box;
      -webkit-line-clamp:3;
      -webkit-box-orient:vertical;
      overflow:hidden;

      overflow-wrap:anywhere;
      word-break:break-word;
      hyphens:auto;
    }

    .pnx-date{
      margin-top:auto;
      color:#94a3b8;
      font-size: 13px;
      padding-top: 12px;
      display:flex;
      align-items:center;
      gap: 6px;
    }

    .pnx-link{
      position:absolute;
      inset:0;
      z-index:2;
      border-radius: 16px;
    }

    /* State / empty */
    .pnx-state{
      background: var(--pnx-card);
      border: 1px solid var(--pnx-line);
      border-radius: 16px;
      box-shadow: var(--pnx-shadow);
      padding: 18px;
      color: var(--pnx-muted);
      text-align:center;
    }

    /* Skeleton */
    .pnx-skeleton{
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 18px;
    }
   .pnx-sk {
  border-radius: 16px;
  border: 1px solid var(--pnx-line);
  background: #fff;
  overflow: hidden;
  position: relative;
  box-shadow: 0 10px 24px rgba(2,6,23,.08);
  height: var(--pnx-card-h);  /* skeleton can keep fixed, it's decorative */
}

    .pnx-sk:before{
      content:'';
      position:absolute; inset:0;
      transform: translateX(-60%);
      background: linear-gradient(90deg, transparent, rgba(148,163,184,.22), transparent);
      animation: pnxSkMove 1.15s ease-in-out infinite;
    }
    @keyframes pnxSkMove{ to{ transform: translateX(60%);} }

    /* Pagination */
    .pnx-pagination{
      display:flex;
      justify-content:center;
      margin-top: 18px;
    }
    .pnx-pagination .pnx-pager{
      display:flex;
      gap: 8px;
      flex-wrap: wrap;
      align-items:center;
      justify-content:center;
      padding: 10px;
    }
    .pnx-pagebtn{
      border:1px solid var(--pnx-line);
      background: var(--pnx-card);
      color: var(--pnx-ink);
      border-radius: 12px;
      padding: 9px 12px;
      font-size: 13px;
      font-weight: 950;
      box-shadow: 0 8px 18px rgba(2,6,23,.06);
      cursor:pointer;
      user-select:none;
    }
    .pnx-pagebtn:hover{ background: rgba(2,6,23,.03); }
    .pnx-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
    .pnx-pagebtn.active{
      background: rgba(201,75,80,.12);
      border-color: rgba(201,75,80,.35);
      color: var(--pnx-brand);
    }

    @media (max-width: 640px) {
  .pnx-title { font-size: 22px; }

  /* stack search + dept on mobile */
  .pnx-tools { flex-wrap: wrap; }
  .pnx-search { min-width: 0; flex: 1 1 100%; }
  .pnx-select { min-width: 0; flex: 1 1 100%; }

  /* shorter media on mobile so body text has room */
  .pnx-wrap { --pnx-media-h: 180px; }

  /* card min-height looser on mobile */
  .pnx-wrap { --pnx-card-h: 380px; }

  .pnx-media .pnx-fallback { font-size: 20px; }

  /* title: 3 lines on mobile */
  .pnx-h {
    font-size: 16px;
    -webkit-line-clamp: 3;
  }

  /* grid: single column on very small screens */
  .pnx-grid {
    grid-template-columns: 1fr;
  }

  /* body padding tighter */
  .pnx-body { padding: 12px 12px 10px; }
}
@media (max-width: 400px) {
  .pnx-wrap { --pnx-media-h: 160px; --pnx-card-h: 360px; }
  .pnx-h { font-size: 15px; }
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
</head>
<body>

  <div
    class="pnx-wrap"
    data-api="{{ url('/api/public/placement-notices') }}"
    data-view-base="{{ url('/placement-notices/view') }}"
    data-dept-api="{{ url('/api/public/departments') }}"
  >
    <div class="pnx-head">
      <div>
        <h1 class="pnx-title"><i class="fa-solid fa-bullhorn"></i>Placement Notices</h1>
        <div class="pnx-sub" id="pnxSub">Showing placement notices for all departments.</div>
      </div>

      <div class="pnx-tools">
        <div class="pnx-search">
          <i class="fa fa-magnifying-glass"></i>
          <input id="pnxSearch" type="search" placeholder="Search notices (title/role/eligibility)…">
        </div>

        <div class="pnx-select" title="Filter by department">
          <i class="fa-solid fa-building-columns pnx-select__icon"></i>
          <select id="pnxDept" aria-label="Filter by department">
            <option value="">All Departments</option>
          </select>
          <i class="fa-solid fa-chevron-down pnx-select__caret"></i>
        </div>
      </div>
    </div>

    <div id="pnxGrid" class="pnx-grid" style="display:none;"></div>

    <div id="pnxSkeleton" class="pnx-skeleton"></div>
    <div id="pnxState" class="pnx-state" style="display:none;"></div>

    <div class="pnx-pagination">
      <div id="pnxPager" class="pnx-pager" style="display:none;"></div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  (() => {
    if (window.__PUBLIC_PLACEMENT_NOTICES_ALL__) return;
    window.__PUBLIC_PLACEMENT_NOTICES_ALL__ = true;

    const root = document.querySelector('.pnx-wrap');
    if (!root) return;

    const API = root.getAttribute('data-api') || '/api/public/placement-notices';
    const VIEW_BASE = root.getAttribute('data-view-base') || '/placement-notices/view';
    const DEPT_API = root.getAttribute('data-dept-api') || '/api/public/departments';

    const $ = (id) => document.getElementById(id);

    const els = {
      grid: $('pnxGrid'),
      skel: $('pnxSkeleton'),
      state: $('pnxState'),
      pager: $('pnxPager'),
      search: $('pnxSearch'),
      dept: $('pnxDept'),
      sub: $('pnxSub'),
    };

    const state = {
      page: 1,
      perPage: 9,
      lastPage: 1,
      total: 0,
      q: '',
      deptUuid: '',   // empty => All Departments
      deptId: null,
      deptName: '',
    };

    let activeController = null;

    // cache
    let allNotices = null;
    let deptByUuid = new Map(); // uuid -> {id, title, uuid}
    let lookupsDepts = [];      // fallback from placement-notices API response

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

    function fmtDate(iso){
      if (!iso) return '';
      const d = new Date(iso);
      if (Number.isNaN(d.getTime())) return '';
      return new Intl.DateTimeFormat('en-IN', { day:'2-digit', month:'short', year:'numeric' }).format(d);
    }

    function fmtMoney(v){
      if (v === null || v === undefined || v === '') return '';
      const n = Number(v);
      if (Number.isNaN(n)) return String(v);
      return n.toLocaleString('en-IN');
    }

    function normalizeUrl(url){
      const u = (url || '').toString().trim();
      if (!u) return '';
      if (/^(data:|blob:|https?:\/\/)/i.test(u)) return u;
      if (u.startsWith('/')) return window.location.origin + u;
      return window.location.origin + '/' + u;
    }

    // ✅ image load/error without inline JS
    function bindCardImages(rootEl){
      rootEl.querySelectorAll('img.pnx-img').forEach(img => {
        const media = img.closest('.pnx-media');
        const fallback = media ? media.querySelector('.pnx-fallback') : null;

        if (img.complete && img.naturalWidth > 0) {
          if (fallback) fallback.style.display = 'none';
          return;
        }

        img.addEventListener('load', () => {
          if (fallback) fallback.style.display = 'none';
        }, { once: true });

        img.addEventListener('error', () => {
          img.remove();
          if (fallback) fallback.style.display = '-webkit-box';
        }, { once: true });
      });
    }

    function pillText(it){
      const last = it?.last_date_to_apply ? fmtDate(it.last_date_to_apply) : '';
      if (last) return 'Apply by ' + last;
      return it?.is_featured_home ? 'Featured' : 'Placement';
    }

    function recruiterText(it){
      const name = it?.recruiter_name || it?.recruiter_title || '';
      const comp = it?.recruiter_company_name || '';
      if (name && comp) return `${name} • ${comp}`;
      return name || comp || '';
    }

    function cardHtml(item){
      const titleRaw = item?.title || 'Placement Notice';
      const title = esc(titleRaw);

      const role  = (item?.role_title || '').toString().trim();
      const recLine = recruiterText(item);

      const ctcRaw = (item?.ctc !== null && item?.ctc !== undefined && item?.ctc !== '') ? fmtMoney(item.ctc) : '';
      const applyBy = fmtDate(item?.last_date_to_apply || null);
      const created = fmtDate(item?.created_at || null);

      const elig  = stripHtml(item?.eligibility || '');
      const desc  = stripHtml(item?.description || '');
      const text  = (desc || elig || '');

      const MAX_CHARS = 150;
      let excerptText = text;
      if (excerptText.length > MAX_CHARS){
        excerptText = excerptText
          .slice(0, MAX_CHARS)
          .trim()
          .replace(/[,\.;:\-\s]+$/g, '');
        excerptText += '......';
      }
      const excerpt = esc(excerptText || '');

      const uuid = item?.uuid ? String(item.uuid) : '';
      const href = uuid ? (VIEW_BASE + '/' + encodeURIComponent(uuid)) : '#';

      const banner = item?.banner_image_full_url || item?.banner_image_url || item?.banner_image || '';
      const bannerNorm = banner ? normalizeUrl(String(banner)) : '';

      return `
        <div class="pnx-card">
          <div class="pnx-media">
            <div class="pnx-pill">${esc(pillText(item))}</div>

            <div class="pnx-fallback">${esc(titleRaw)}</div>
            ${bannerNorm ? `
              <img class="pnx-img"
                   src="${escAttr(bannerNorm)}"
                   alt="${escAttr(titleRaw)}"
                   loading="lazy" />
            ` : ``}
          </div>

          <div class="pnx-body">
            <div class="pnx-h">${title}</div>

            <div class="pnx-meta">
              <div class="rowx">
                <i class="fa-solid fa-briefcase"></i>
                <span>${role ? esc(role) : '—'}</span>
              </div>

              <div class="rowx">
                <i class="fa-solid fa-building"></i>
                <span>${recLine ? esc(recLine) : '—'}</span>
              </div>

              <div class="rowx">
                <i class="fa-solid fa-money-bill-wave"></i>
                <span>${ctcRaw ? ('CTC: ' + esc(ctcRaw)) : '—'}</span>
              </div>

              <div class="rowx">
                <i class="fa-regular fa-calendar"></i>
                <span>${applyBy ? ('Last date: ' + esc(applyBy)) : '—'}</span>
              </div>
            </div>

            <p class="pnx-p">${excerpt}</p>

            <div class="pnx-date">
              <i class="fa-regular fa-clock"></i>
              <span>Created: ${esc(created || '—')}</span>
            </div>
          </div>

          ${uuid
            ? `<a class="pnx-link" href="${href}" aria-label="Open ${escAttr(titleRaw)}"></a>`
            : `<div class="pnx-link" title="Missing UUID"></div>`
          }
        </div>
      `;
    }

    function showSkeleton(){
      const sk = els.skel, st = els.state, grid = els.grid, pager = els.pager;
      if (grid) grid.style.display = 'none';
      if (pager) pager.style.display = 'none';
      if (st) st.style.display = 'none';

      if (!sk) return;
      sk.style.display = '';
      sk.innerHTML = Array.from({length: 6}).map(() => `<div class="pnx-sk"></div>`).join('');
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

      const res = await fetch(url, {
        headers: { 'Accept':'application/json' },
        signal: activeController.signal
      });

      const js = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(js?.message || ('Request failed: ' + res.status));
      return js;
    }

    function extractDeptUuidFromUrl(){
      // matches "?d-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" anywhere
      const hay = (window.location.search || '') + ' ' + (window.location.href || '');
      const m = hay.match(/d-([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/i);
      return m ? m[1] : '';
    }

    function setDeptSelection(uuid){
      const sel = els.dept;
      uuid = (uuid || '').toString().trim();

      if (!sel) return;

      // ✅ All Departments
      if (!uuid){
        sel.value = '';
        state.deptUuid = '';
        state.deptId = null;
        state.deptName = '';

        if (els.sub) els.sub.textContent = 'Showing placement notices for all departments.';
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
          ? ('Showing placement notices for ' + state.deptName + '.')
          : 'Showing placement notices (filtered).';
      }
    }

    function populateDeptSelectFromList(list){
      const sel = els.dept;
      if (!sel) return;

      const depts = (Array.isArray(list) ? list : [])
        .map(d => ({
          id: d?.id ?? null,
          uuid: (d?.uuid ?? '').toString().trim(),
          title: (d?.title ?? d?.name ?? '').toString().trim(),
        }))
        .filter(x => x.uuid && x.title);

      // build map
      deptByUuid = new Map(depts.map(d => [d.uuid, d]));

      // sort A-Z
      depts.sort((a,b) => a.title.localeCompare(b.title));

      sel.innerHTML =
        `<option value="">All Departments</option>` +
        depts.map(d => `<option value="${escAttr(d.uuid)}" data-id="${escAttr(d.id ?? '')}">${esc(d.title)}</option>`).join('');

      // keep current selection if still valid
      if (state.deptUuid && deptByUuid.has(state.deptUuid)){
        sel.value = state.deptUuid;
      } else {
        sel.value = '';
      }
    }

    async function loadDepartments(){
      const sel = els.dept;
      if (!sel) return;

      // ✅ Keep All Departments selected while loading
      sel.innerHTML = `
        <option value="">All Departments</option>
        <option value="__loading" disabled>Loading departments…</option>
      `;
      sel.value = '';

      try{
        const res = await fetch(DEPT_API, { headers: { 'Accept':'application/json' } });
        const js = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(js?.message || ('HTTP ' + res.status));

        const items = Array.isArray(js?.data) ? js.data : [];
        populateDeptSelectFromList(items);
      } catch (e){
        console.warn('Departments load failed, will fallback to lookups if available:', e);
        // fallback: if we already have lookups from notices API
        if (Array.isArray(lookupsDepts) && lookupsDepts.length){
          populateDeptSelectFromList(lookupsDepts);
        } else {
          sel.innerHTML = `<option value="">All Departments</option>`;
          sel.value = '';
        }
      }
    }

    async function ensureNoticesLoaded(force=false){
      if (allNotices && !force) return;

      showSkeleton();

      try{
        const u = new URL(API, window.location.origin);
        u.searchParams.set('page', '1');
        u.searchParams.set('per_page', '200');
        u.searchParams.set('visible_now', '1');
        u.searchParams.set('sort', 'created_at');
        u.searchParams.set('direction', 'desc');

        const js = await fetchJson(u.toString());
        const items = Array.isArray(js?.data) ? js.data : [];
        allNotices = items;

        // ✅ capture lookups departments from this endpoint (your response includes it)
        const look = js?.lookups?.departments;
        lookupsDepts = Array.isArray(look) ? look : [];

        // if dept list not loaded (or empty), populate from lookups as fallback
        if ((!deptByUuid || deptByUuid.size === 0) && lookupsDepts.length){
          populateDeptSelectFromList(lookupsDepts);
        }
      } finally {
        hideSkeleton();
      }
    }

    function applyFilterAndSearch(){
      const q = (state.q || '').toString().trim().toLowerCase();

      let items = Array.isArray(allNotices) ? allNotices.slice() : [];

      // ✅ Department filter only when a specific dept is selected
      const deptUuidStr = String(state.deptUuid || '').trim();
      const deptIdStr = (state.deptId === null || state.deptId === undefined || state.deptId === '')
        ? ''
        : String(state.deptId);

      if (deptUuidStr){
        // match selected dept via department_ids array OR departments[] uuid list
        items = items.filter(it => {
          const ids = Array.isArray(it?.department_ids) ? it.department_ids.map(x => String(x)) : [];
          const okById = deptIdStr ? ids.includes(deptIdStr) : false;

          const deps = Array.isArray(it?.departments) ? it.departments : [];
          const okByUuid = deptUuidStr
            ? deps.some(d => String(d?.uuid || '').trim() === deptUuidStr)
            : false;

          return okById || okByUuid;
        });
      }

      // search on title + role + recruiter + eligibility/description
      if (q){
        items = items.filter(it => {
          const t = String(it?.title || '').toLowerCase();
          const r = String(it?.role_title || '').toLowerCase();
          const rec = String(recruiterText(it) || '').toLowerCase();
          const e = stripHtml(it?.eligibility || '').toLowerCase();
          const d = stripHtml(it?.description || '').toLowerCase();
          return (t.includes(q) || r.includes(q) || rec.includes(q) || e.includes(q) || d.includes(q));
        });
      }

      return items;
    }

    function render(items){
      const grid = els.grid, st = els.state;
      if (!grid || !st) return;

      if (!items.length){
        grid.style.display = 'none';
        st.style.display = '';
        const deptLabel = state.deptUuid
          ? (state.deptName ? state.deptName : 'Selected Department')
          : 'All Departments';

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
  No Placsement Notices found.
 
          <div style="margin-top:6px;font-size:12.5px;opacity:.95;">Department: <b>${esc(deptLabel)}</b></div>
        `;
        return;
      }

      st.style.display = 'none';
      grid.style.display = '';
      grid.innerHTML = items.map(cardHtml).join('');
      bindCardImages(grid);
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
        const cls = active ? 'pnx-pagebtn active' : 'pnx-pagebtn';
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

    async function loadAndPaint(){
      if (!allNotices){
        await ensureNoticesLoaded(false);
      }
      repaint();
    }

    document.addEventListener('DOMContentLoaded', async () => {
      // load departments first (dropdown)
      await loadDepartments();

      // deep-link selection (?d-{uuid})
      const deepDeptUuid = extractDeptUuidFromUrl();
      if (deepDeptUuid && deptByUuid.has(deepDeptUuid)){
        setDeptSelection(deepDeptUuid);
      } else {
        setDeptSelection(''); // ✅ All Departments
      }

      // initial render (All Departments)
      await loadAndPaint();

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
      els.dept && els.dept.addEventListener('change', async () => {
        const v = (els.dept.value || '').toString();
        if (v === '__loading') return;

        setDeptSelection(v); // empty => All Departments

        state.page = 1;
        await loadAndPaint();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });

      // pagination click
      document.addEventListener('click', (e) => {
        const b = e.target.closest('button.pnx-pagebtn[data-page]');
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
</body>
</html>
