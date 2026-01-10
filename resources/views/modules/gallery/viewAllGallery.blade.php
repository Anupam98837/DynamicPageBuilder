{{-- resources/views/landing/gallery-all.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name','College Portal') }} — Gallery</title>

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  {{-- Common UI tokens --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    /* =========================================================
      ✅ Gallery All (Scoped / No :root / No global body rules)
      - UI structure matches your Announcements reference page
      - Dept dropdown added (pill UI + icon + caret)
      - Dept filtering FIXED (frontend filter by department_id / department_uuid)
      - Deep-link ?d-{uuid} auto-selects dept and filters
      - Pinterest-style masonry kept (CSS Grid + JS row-span)
      - Lightbox kept (renamed ids/classes already consistent)
    ========================================================= */

    .gxa-wrap{
      /* ✅ scoped design tokens (NO :root) */
      --gxa-brand: var(--primary-color, #9E363A);
      --gxa-ink: #0f172a;
      --gxa-muted: #64748b;
      --gxa-bg: var(--page-bg, #ffffff);
      --gxa-card: var(--surface, #ffffff);
      --gxa-line: var(--line-soft, rgba(15,23,42,.10));
      --gxa-shadow: 0 10px 24px rgba(2,6,23,.08);

      max-width: 1320px;
      margin: 18px auto 54px;
      padding: 0 12px;
      background: transparent;
      position: relative;
      overflow: visible;
    }

    /* Header */
    .gxa-head{
      background: var(--gxa-card);
      border: 1px solid var(--gxa-line);
      border-radius: 16px;
      box-shadow: var(--gxa-shadow);
      padding: 14px 16px;
      margin-bottom: 16px;

      display:flex;
      gap: 12px;
      align-items: flex-end;
      justify-content: space-between;
      flex-wrap: wrap;
    }

    .gxa-title{
      margin: 0;
      font-weight: 950;
      letter-spacing: .2px;
      color: var(--gxa-ink);
      font-size: 28px;
      display:flex;
      align-items:center;
      gap: 10px;
    }
    .gxa-title i{ color: var(--gxa-brand); }

    .gxa-sub{
      margin: 6px 0 0;
      color: var(--gxa-muted);
      font-size: 14px;
    }

    .gxa-tools{
      display:flex;
      gap: 10px;
      align-items:center;
      flex-wrap: wrap;
    }

    /* Search (pill like reference) */
    .gxa-search{
      position: relative;
      min-width: 260px;
      max-width: 520px;
      flex: 1 1 320px;
    }
    .gxa-search i{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .65;
      color: var(--gxa-muted);
      pointer-events:none;
    }
    .gxa-search input{
      width:100%;
      height: 42px;
      border-radius: 999px;
      padding: 11px 12px 11px 42px;
      border: 1px solid var(--gxa-line);
      background: var(--gxa-card);
      color: var(--gxa-ink);
      outline: none;
    }
    .gxa-search input:focus{
      border-color: rgba(201,75,80,.55);
      box-shadow: 0 0 0 4px rgba(201,75,80,.18);
    }

    /* ✅ Dept dropdown (pill UI, like reference) */
    .gxa-select{
      position: relative;
      min-width: 260px;
      max-width: 360px;
      flex: 0 1 320px;
    }
    .gxa-select__icon{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .70;
      color: var(--gxa-muted);
      pointer-events:none;
      font-size: 14px;
    }
    .gxa-select__caret{
      position:absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .70;
      color: var(--gxa-muted);
      pointer-events:none;
      font-size: 12px;
    }
    .gxa-select select{
      width: 100%;
      height: 42px;
      border-radius: 999px;
      padding: 10px 38px 10px 42px;
      border: 1px solid var(--gxa-line);
      background: var(--gxa-card);
      color: var(--gxa-ink);
      outline: none;

      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
    }
    .gxa-select select:focus{
      border-color: rgba(201,75,80,.55);
      box-shadow: 0 0 0 4px rgba(201,75,80,.18);
    }

    /* Chip (pill like reference) */
    .gxa-chip{
      display:flex;
      align-items:center;
      gap: 8px;
      height: 42px;
      padding: 0 12px;
      border-radius: 999px;
      border: 1px solid var(--gxa-line);
      background: var(--gxa-card);
      box-shadow: 0 8px 18px rgba(2,6,23,.06);
      color: var(--gxa-ink);
      font-size: 13px;
      font-weight: 900;
      white-space: nowrap;
    }

    /* =========================================================
      ✅ Pinterest-style Masonry
      - Grid with tiny auto-rows
      - JS computes grid-row-end span per item after images load
    ========================================================= */
    .gxa-grid{
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      grid-auto-rows: 10px;
      gap: 18px;
      align-items: start;
    }

    .gxa-item{
      position: relative;
      overflow: hidden;
      border-radius: 16px;
      background: #fff;
      border: 1px solid rgba(2,6,23,.08);
      box-shadow: 0 10px 24px rgba(2,6,23,.08);
      cursor: pointer;
      user-select: none;
      transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
      will-change: transform;
    }
    .gxa-item:hover{
      transform: translateY(-2px);
      box-shadow: 0 16px 34px rgba(2,6,23,.12);
      border-color: rgba(158,54,58,.22);
    }

    .gxa-item img{
      width: 100%;
      height: auto;
      display:block;
    }

    /* overlay meta (always visible) */
    .gxa-meta{
      position:absolute;
      left:0; right:0; bottom:0;
      padding: 10px 10px 9px;
      color: #fff;
      background: linear-gradient(180deg, rgba(2,6,23,0) 0%, rgba(2,6,23,.55) 28%, rgba(2,6,23,.82) 100%);
      pointer-events: none;
    }
    .gxa-meta__title{
      font-weight: 950;
      font-size: 13px;
      letter-spacing: .2px;
      line-height: 1.18;
      text-shadow: 0 2px 10px rgba(0,0,0,.35);
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
    }
    .gxa-meta__desc{
      margin-top: 4px;
      font-size: 12px;
      opacity: .92;
      line-height: 1.25;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      text-shadow: 0 2px 10px rgba(0,0,0,.35);
    }
    .gxa-meta__tags{
      margin-top: 6px;
      display:flex;
      gap: 6px;
      flex-wrap: wrap;
    }
    .gxa-tag{
      font-size: 11px;
      font-weight: 950;
      padding: 5px 8px;
      border-radius: 999px;
      background: rgba(255,255,255,.14);
      border: 1px solid rgba(255,255,255,.18);
      backdrop-filter: blur(6px);
      max-width: 100%;
      overflow:hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .gxa-tag.more{
      background: rgba(201,75,80,.22);
      border-color: rgba(201,75,80,.35);
    }

    /* Loading / empty */
    .gxa-state{
      background: var(--gxa-card);
      border: 1px solid var(--gxa-line);
      border-radius: 16px;
      box-shadow: var(--gxa-shadow);
      padding: 18px;
      color: var(--gxa-muted);
      text-align:center;
    }

    /* Skeleton (also masonry) */
    .gxa-skeleton{
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      grid-auto-rows: 10px;
      gap: 18px;
    }
    .gxa-sk{
      border-radius: 16px;
      border: 1px solid var(--gxa-line);
      background: #fff;
      overflow:hidden;
      position:relative;
      box-shadow: 0 10px 24px rgba(2,6,23,.08);
    }
    .gxa-sk:before{
      content:'';
      position:absolute; inset:0;
      transform: translateX(-60%);
      background: linear-gradient(90deg, transparent, rgba(148,163,184,.22), transparent);
      animation: gxaSkMove 1.15s ease-in-out infinite;
    }
    @keyframes gxaSkMove{ to{ transform: translateX(60%);} }

    /* Pagination */
    .gxa-pagination{
      display:flex;
      justify-content:center;
      margin-top: 18px;
    }
    .gxa-pagination .gxa-pager{
      display:flex;
      gap: 8px;
      flex-wrap: wrap;
      align-items:center;
      justify-content:center;
      padding: 10px;
    }
    .gxa-pagebtn{
      border:1px solid var(--gxa-line);
      background: var(--gxa-card);
      color: var(--gxa-ink);
      border-radius: 12px;
      padding: 9px 12px;
      font-size: 13px;
      font-weight: 950;
      box-shadow: 0 8px 18px rgba(2,6,23,.06);
      cursor:pointer;
      user-select:none;
    }
    .gxa-pagebtn:hover{ background: rgba(2,6,23,.03); }
    .gxa-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
    .gxa-pagebtn.active{
      background: rgba(201,75,80,.12);
      border-color: rgba(201,75,80,.35);
      color: var(--gxa-brand);
    }

    /* Lightbox */
    .gxa-lb{
      position: fixed;
      inset: 0;
      background: rgba(2,6,23,.72);
      display:none;
      align-items:center;
      justify-content:center;
      z-index: 9999;
      padding: 18px;
    }
    .gxa-lb.show{ display:flex; }

    .gxa-lb__inner{
      max-width: min(1100px, 96vw);
      max-height: min(86vh, 900px);
      background: #0b1220;
      border: 1px solid rgba(255,255,255,.12);
      box-shadow: 0 22px 60px rgba(0,0,0,.45);
      position: relative;
      display:flex;
      flex-direction: column;
      overflow:hidden;
      border-radius: 14px;
    }
    .gxa-lb__img{
      max-width: min(1100px, 96vw);
      max-height: min(72vh, 820px);
      display:block;
      object-fit: contain;
    }

    .gxa-lb__meta{
      border-top: 1px solid rgba(255,255,255,.10);
      padding: 12px 14px 14px;
      color: rgba(255,255,255,.92);
      background: rgba(255,255,255,.02);
    }
    .gxa-lb__title{
      font-weight: 950;
      font-size: 15px;
      letter-spacing: .2px;
      color:#fff;
      margin: 0 0 6px;
    }
    .gxa-lb__desc{
      margin: 0 0 10px;
      font-size: 13px;
      line-height: 1.35;
      color: rgba(255,255,255,.86);
      white-space: pre-wrap;
    }
    .gxa-lb__tags{
      display:flex;
      gap: 8px;
      flex-wrap: wrap;
    }
    .gxa-lb__tag{
      font-size: 12px;
      font-weight: 900;
      padding: 7px 10px;
      border-radius: 999px;
      background: rgba(255,255,255,.10);
      border: 1px solid rgba(255,255,255,.14);
    }

    .gxa-lb__close{
      position:absolute;
      top: 10px;
      right: 10px;
      width: 40px;
      height: 40px;
      border-radius: 999px;
      border: 1px solid rgba(255,255,255,.18);
      background: rgba(0,0,0,.35);
      color:#fff;
      cursor:pointer;
      display:flex;
      align-items:center;
      justify-content:center;
      z-index: 5;
    }
    .gxa-lb__close:hover{ background: rgba(0,0,0,.55); }

    @media (max-width: 640px){
      .gxa-title{ font-size: 24px; }
      .gxa-search{ min-width: 220px; flex: 1 1 240px; }
      .gxa-select{ min-width: 220px; flex: 1 1 240px; }
      .gxa-lb__img{ max-height: min(66vh, 760px); }
    }

    /* ✅ Guard against Bootstrap overriding mega dropdown positioning */
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
    class="gxa-wrap"
    data-api="{{ url('/api/public/gallery') }}"
    data-dept-api="{{ url('/api/public/departments') }}"
  >
    <div class="gxa-head">
      <div>
        <h1 class="gxa-title"><i class="fa-regular fa-images"></i>Gallery</h1>
        <div class="gxa-sub" id="gxaSub">View all photos</div>
      </div>

      <div class="gxa-tools">
        <div class="gxa-search">
          <i class="fa fa-magnifying-glass"></i>
          <input id="gxaSearch" type="search" placeholder="Search by caption / tag / title…">
        </div>

        {{-- ✅ Department dropdown --}}
        <div class="gxa-select" title="Filter by department">
          <i class="fa-solid fa-building-columns gxa-select__icon"></i>
          <select id="gxaDept" aria-label="Filter by department">
            <option value="">All Departments</option>
          </select>
          <i class="fa-solid fa-chevron-down gxa-select__caret"></i>
        </div>

        <div class="gxa-chip" title="Total results">
          <i class="fa-solid fa-images" style="opacity:.85"></i>
          <span id="gxaCount">—</span>
        </div>
      </div>
    </div>

    <div id="gxaGrid" class="gxa-grid" style="display:none;"></div>

    <div id="gxaSkeleton" class="gxa-skeleton"></div>
    <div id="gxaState" class="gxa-state" style="display:none;"></div>

    <div class="gxa-pagination">
      <div id="gxaPager" class="gxa-pager" style="display:none;"></div>
    </div>
  </div>

  {{-- Lightbox --}}
  <div id="gxaLb" class="gxa-lb" aria-hidden="true">
    <div class="gxa-lb__inner">
      <button class="gxa-lb__close" id="gxaLbClose" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>

      <img id="gxaLbImg" class="gxa-lb__img" alt="Gallery image">

      <div class="gxa-lb__meta" id="gxaLbMeta" style="display:none;">
        <div class="gxa-lb__title" id="gxaLbTitle"></div>
        <div class="gxa-lb__desc" id="gxaLbDesc"></div>
        <div class="gxa-lb__tags" id="gxaLbTags"></div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  (() => {
    if (window.__LANDING_GALLERY_ALL__) return;
    window.__LANDING_GALLERY_ALL__ = true;

    const root = document.querySelector('.gxa-wrap');
    if (!root) return;

    const API = root.getAttribute('data-api') || '/api/public/gallery';
    const DEPT_API = root.getAttribute('data-dept-api') || '/api/public/departments';

    const $ = (id) => document.getElementById(id);

    const els = {
      grid: $('gxaGrid'),
      skel: $('gxaSkeleton'),
      state: $('gxaState'),
      pager: $('gxaPager'),
      search: $('gxaSearch'),
      dept: $('gxaDept'),
      count: $('gxaCount'),
      sub: $('gxaSub'),

      lb: $('gxaLb'),
      lbImg: $('gxaLbImg'),
      lbClose: $('gxaLbClose'),
    };

    const state = {
      page: 1,
      perPage: 12,
      lastPage: 1,
      total: 0,
      q: '',
      deptUuid: '',
      deptId: null,
      deptName: '',
    };

    let activeController = null;

    // cache
    let allGallery = null;
    let deptByUuid = new Map(); // uuid -> {id, title, uuid}

    function esc(str){
      return (str ?? '').toString().replace(/[&<>"']/g, s => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
      }[s]));
    }
    function escAttr(str){
      return (str ?? '').toString().replace(/"/g, '&quot;');
    }

    function normalizeUrl(url){
      const u = (url || '').toString().trim();
      if (!u) return '';
      if (/^(data:|blob:|https?:\/\/)/i.test(u)) return u;
      if (u.startsWith('/')) return window.location.origin + u;
      return window.location.origin + '/' + u;
    }

    function pick(obj, keys){
      for (const k of keys){
        const v = obj?.[k];
        if (v !== null && v !== undefined && String(v).trim() !== '') return v;
      }
      return '';
    }

    function normalizeTags(raw){
      let arr = [];
      if (Array.isArray(raw)){
        arr = raw.map(x => (x ?? '').toString().trim()).filter(Boolean);
      } else {
        const s = (raw ?? '').toString().trim();
        if (s){
          if (s.includes('|')) arr = s.split('|');
          else if (s.includes(',')) arr = s.split(',');
          else arr = s.split(/\s+/);
          arr = arr.map(x => x.replace(/^#+/,'').trim()).filter(Boolean);
        }
      }
      const seen = new Set();
      const out = [];
      for (const t of arr){
        const key = t.toLowerCase();
        if (seen.has(key)) continue;
        seen.add(key);
        out.push(t);
      }
      return out;
    }

    function tagsFromItem(it){
      const raw =
        it?.tags ??
        it?.tag_list ??
        it?.keywords ??
        it?.categories ??
        it?.tag ??
        it?.meta?.tags ??
        it?.attachment?.tags;

      return normalizeTags(raw);
    }

    function renderTagChips(tags, max=3){
      const t = Array.isArray(tags) ? tags.filter(Boolean) : [];
      if (!t.length) return '';
      const shown = t.slice(0, max);
      const more = t.length - shown.length;

      let html = shown.map(x => `<span class="gxa-tag">${esc(x)}</span>`).join('');
      if (more > 0) html += `<span class="gxa-tag more">+${more}</span>`;
      return html;
    }

    /* =========================================================
      ✅ Masonry helper
    ========================================================= */
    function applyMasonry(){
      const grid = els.grid;
      if (!grid) return;

      const style = window.getComputedStyle(grid);
      const rowH = parseInt(style.getPropertyValue('grid-auto-rows'), 10) || 10;
      const gap  = parseInt(style.getPropertyValue('grid-row-gap'), 10) || 18;

      const items = grid.querySelectorAll('.gxa-item');
      items.forEach((item) => {
        item.style.gridRowEnd = 'auto';
        const h = item.getBoundingClientRect().height;
        const span = Math.ceil((h + gap) / (rowH + gap));
        item.style.gridRowEnd = `span ${Math.max(1, span)}`;
      });
    }

    function showSkeleton(){
      const sk = els.skel, st = els.state, grid = els.grid, pager = els.pager;

      if (grid) grid.style.display = 'none';
      if (pager) pager.style.display = 'none';
      if (st) st.style.display = 'none';

      if (!sk) return;
      sk.style.display = '';

      // ✅ varied skeleton heights for masonry feel
      const heights = [170, 260, 210, 320, 190, 280, 240, 360, 200, 300, 230, 340];
      sk.innerHTML = heights.map(h => `<div class="gxa-sk" style="height:${h}px"></div>`).join('');
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

      if (!uuid){
        sel.value = '';
        state.deptUuid = '';
        state.deptId = null;
        state.deptName = '';
        if (els.sub) els.sub.textContent = 'View all photos';
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
          ? ('Gallery for ' + state.deptName)
          : 'Gallery (filtered)';
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
          .filter(x => x.uuid && x.title && String(x.active) === '1'); // ✅ only active

        deptByUuid = new Map(depts.map(d => [d.uuid, d]));
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

    async function ensureGalleryLoaded(force=false){
      if (allGallery && !force) return;

      showSkeleton();

      try{
        // ✅ fetch big once so frontend dept filter always works
        const u = new URL(API, window.location.origin);
        u.searchParams.set('page', '1');
        u.searchParams.set('per_page', '400');
        u.searchParams.set('sort', 'created_at');
        u.searchParams.set('direction', 'desc');

        const js = await fetchJson(u.toString());
        const items = Array.isArray(js?.data) ? js.data : (Array.isArray(js) ? js : []);
        allGallery = items;
      } finally {
        hideSkeleton();
      }
    }

    function getItemDept(it){
      // support multiple possible API shapes
      const did =
        it?.department_id ??
        it?.dept_id ??
        it?.departmentId ??
        it?.department?.id ??
        it?.dept?.id;

      const duu =
        it?.department_uuid ??
        it?.dept_uuid ??
        it?.departmentUuid ??
        it?.department?.uuid ??
        it?.dept?.uuid;

      return {
        id: (did === null || did === undefined) ? '' : String(did),
        uuid: (duu === null || duu === undefined) ? '' : String(duu),
      };
    }

    function applyFilterAndSearch(){
      const q = (state.q || '').toString().trim().toLowerCase();
      let items = Array.isArray(allGallery) ? allGallery.slice() : [];

      // ✅ dept filter (when selected -> show only matching items)
      if (state.deptUuid && (state.deptId !== null && state.deptId !== undefined && String(state.deptId) !== '')){
        const deptIdStr = String(state.deptId);
        const deptUuidStr = String(state.deptUuid);

        items = items.filter(it => {
          const d = getItemDept(it);
          return (d.id && d.id === deptIdStr) || (d.uuid && d.uuid === deptUuidStr);
        });
      } else if (state.deptUuid) {
        // uuid-only fallback
        const deptUuidStr = String(state.deptUuid);
        items = items.filter(it => getItemDept(it).uuid === deptUuidStr);
      }

      // search (title + desc + tags)
      if (q){
        items = items.filter(it => {
          const title = String(pick(it, ['title','name','alt','caption']) || '').toLowerCase();
          const desc  = String(pick(it, ['description','desc','summary','details']) || (it?.meta?.description ?? '') || '').toLowerCase();
          const tags  = tagsFromItem(it).join(' ').toLowerCase();
          return title.includes(q) || desc.includes(q) || tags.includes(q);
        });
      }

      return items;
    }

    function render(items){
      const grid = els.grid;
      const st = els.state;
      const count = els.count;

      if (!grid || !st) return;

      if (!items.length){
        grid.style.display = 'none';
        st.style.display = '';
        const deptLine = state.deptName ? `<div style="margin-top:6px;font-size:12.5px;opacity:.95;">Department: <b>${esc(state.deptName)}</b></div>` : '';
        st.innerHTML = `
          <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
            <i class="fa-regular fa-face-frown"></i>
          </div>
          No images found.
          ${deptLine}
        `;
        if (count) count.textContent = '0';
        return;
      }

      if (count) count.textContent = String(state.total || items.length);

      st.style.display = 'none';
      grid.style.display = '';

      grid.innerHTML = items.map(it => {
        const img =
          pick(it, ['image_url','image_full_url','url','src','image']) ||
          (it?.attachment?.url ?? '');

        const title =
          pick(it, ['title','name','alt']) ||
          pick(it, ['caption']) ||
          'Gallery Image';

        const description =
          pick(it, ['description','desc','summary','details']) ||
          (it?.meta?.description ?? '') ||
          '';

        const tags = tagsFromItem(it);
        const tagsStr = tags.join('|');
        const full = normalizeUrl(img);

        const descHtml = description
          ? `<div class="gxa-meta__desc">${esc(description)}</div>`
          : `<div class="gxa-meta__desc" style="opacity:0;"></div>`;

        const tagsHtml = tags.length
          ? `<div class="gxa-meta__tags">${renderTagChips(tags, 3)}</div>`
          : `<div class="gxa-meta__tags" style="display:none;"></div>`;

        return `
          <div class="gxa-item"
               data-full="${esc(full)}"
               data-title="${esc(title)}"
               data-desc="${esc(description)}"
               data-tags="${esc(tagsStr)}"
               role="button"
               tabindex="0"
               aria-label="${esc(title)}">
            <img src="${esc(full)}" alt="${esc(title)}" loading="lazy">
            <div class="gxa-meta">
              <div class="gxa-meta__title">${esc(title)}</div>
              ${descHtml}
              ${tagsHtml}
            </div>
          </div>
        `;
      }).join('');

      // ✅ masonry after paint
      requestAnimationFrame(() => applyMasonry());

      // ✅ masonry again as images load
      const imgs = grid.querySelectorAll('img');
      imgs.forEach(img => {
        if (img.complete) return;
        img.addEventListener('load', () => applyMasonry(), { once: true });
        img.addEventListener('error', () => applyMasonry(), { once: true });
      });
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
        const cls = active ? 'gxa-pagebtn active' : 'gxa-pagebtn';
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

    // Lightbox helpers
    function setLightboxMeta({title='', desc='', tags=[]}){
      const meta = $('gxaLbMeta');
      const t = $('gxaLbTitle');
      const d = $('gxaLbDesc');
      const tg = $('gxaLbTags');

      if (!meta || !t || !d || !tg) return;

      const hasTitle = (title || '').trim().length > 0;
      const hasDesc  = (desc || '').trim().length > 0;
      const hasTags  = Array.isArray(tags) && tags.length > 0;

      if (!hasTitle && !hasDesc && !hasTags){
        meta.style.display = 'none';
        t.textContent = '';
        d.textContent = '';
        tg.innerHTML = '';
        return;
      }

      meta.style.display = '';
      t.textContent = (title || '').trim();
      d.textContent = (desc || '').trim();

      if (hasTags){
        tg.innerHTML = tags.map(x => `<span class="gxa-lb__tag">${esc(x)}</span>`).join('');
        tg.style.display = 'flex';
      } else {
        tg.innerHTML = '';
        tg.style.display = 'none';
      }

      d.style.display = hasDesc ? '' : 'none';
    }

    function parseTagsStr(s){
      const raw = (s || '').toString().trim();
      if (!raw) return [];
      return raw.split('|').map(x => (x || '').trim()).filter(Boolean);
    }

    function openLB(src, meta){
      if (!els.lb || !els.lbImg) return;
      els.lbImg.src = src;
      setLightboxMeta(meta || {});
      els.lb.classList.add('show');
      els.lb.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }

    function closeLB(){
      if (!els.lb || !els.lbImg) return;
      els.lb.classList.remove('show');
      els.lb.setAttribute('aria-hidden', 'true');
      els.lbImg.src = '';
      setLightboxMeta({ title:'', desc:'', tags:[] });
      document.body.style.overflow = '';
    }

    document.addEventListener('DOMContentLoaded', async () => {
      // load departments first (for deep-link + selection)
      await loadDepartments();

      // deep-link ?d-{uuid}
      const deepDeptUuid = extractDeptUuidFromUrl();
      if (deepDeptUuid && deptByUuid.has(deepDeptUuid)){
        setDeptSelection(deepDeptUuid);
      } else {
        setDeptSelection('');
      }

      // load gallery once, filter client-side
      await ensureGalleryLoaded(false);
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

      // pagination click
      document.addEventListener('click', (e) => {
        const b = e.target.closest('button.gxa-pagebtn[data-page]');
        if (!b) return;
        const p = parseInt(b.dataset.page, 10);
        if (!p || Number.isNaN(p) || p === state.page) return;
        state.page = p;
        repaint();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });

      // open lightbox
      document.addEventListener('click', (e) => {
        const tile = e.target.closest('.gxa-item[data-full]');
        if (!tile) return;

        const src   = tile.getAttribute('data-full') || '';
        const title = tile.getAttribute('data-title') || '';
        const desc  = tile.getAttribute('data-desc') || '';
        const tags  = parseTagsStr(tile.getAttribute('data-tags') || '');

        if (src) openLB(src, { title, desc, tags });
      });

      // keyboard open / close
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeLB();

        const tile = e.target.closest?.('.gxa-item[data-full]');
        if (!tile) return;

        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          const src   = tile.getAttribute('data-full') || '';
          const title = tile.getAttribute('data-title') || '';
          const desc  = tile.getAttribute('data-desc') || '';
          const tags  = parseTagsStr(tile.getAttribute('data-tags') || '');
          if (src) openLB(src, { title, desc, tags });
        }
      });

      // close by clicking backdrop
      els.lb && els.lb.addEventListener('click', (e) => {
        if (e.target === els.lb) closeLB();
      });
      els.lbClose && els.lbClose.addEventListener('click', closeLB);

      // keep masonry responsive
      window.addEventListener('resize', () => {
        clearTimeout(window.__gxaResizeT);
        window.__gxaResizeT = setTimeout(() => applyMasonry(), 80);
      });
    });
  })();
  </script>
</body>
</html>
