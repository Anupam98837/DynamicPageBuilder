{{-- resources/views/public/notices/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name','College Portal') }} — Notices</title>

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  {{-- Common UI tokens --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    /* =========================================================
      ✅ Notices (Public) — SAME UI STRUCTURE as Announcements reference
      - Scoped CSS (no :root / no global body)
      - Dept dropdown UI added (pill + icon + caret)
      - Dept filtering FIXED (client-side filter by department_id / department_uuid)
      - Deep-link ?d-{uuid} auto-selects dept + filters
    ========================================================= */

    .ntx-wrap{
      /* scoped tokens */
      --ntx-brand: var(--primary-color, #9E363A);
      --ntx-ink: #0f172a;
      --ntx-muted: #64748b;
      --ntx-bg: var(--page-bg, #ffffff);
      --ntx-card: var(--surface, #ffffff);
      --ntx-line: var(--line-soft, rgba(15,23,42,.10));
      --ntx-shadow: 0 10px 24px rgba(2,6,23,.08);

      /* card sizing (match ref height) */
      --ntx-card-h: 426.4px;
      --ntx-media-h: 240px;

      max-width: 1320px;
      margin: 18px auto 54px;
      padding: 0 12px;
      background: transparent;
      position: relative;
      overflow: visible;
    }

    /* Header */
    .ntx-head{
      background: var(--ntx-card);
      border: 1px solid var(--ntx-line);
      border-radius: 16px;
      box-shadow: var(--ntx-shadow);
      padding: 14px 16px;
      margin-bottom: 16px;

      display:flex;
      gap: 12px;
      align-items: flex-end;
      justify-content: space-between;
      flex-wrap: wrap;
    }
    .ntx-title{
      margin: 0;
      font-weight: 950;
      letter-spacing: .2px;
      color: var(--ntx-ink);
      font-size: 28px;
      display:flex;
      align-items:center;
      gap: 10px;
    }
    .ntx-title i{ color: var(--ntx-brand); }
    .ntx-sub{
      margin: 6px 0 0;
      color: var(--ntx-muted);
      font-size: 14px;
    }

    .ntx-tools{
      display:flex;
      gap: 10px;
      align-items:center;
      flex-wrap: wrap;
    }

    /* Search */
    .ntx-search{
      position: relative;
      min-width: 260px;
      max-width: 520px;
      flex: 1 1 320px;
    }
    .ntx-search i{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .65;
      color: var(--ntx-muted);
      pointer-events:none;
    }
    .ntx-search input{
      width:100%;
      height: 42px;
      border-radius: 999px;
      padding: 11px 12px 11px 42px;
      border: 1px solid var(--ntx-line);
      background: var(--ntx-card);
      color: var(--ntx-ink);
      outline: none;
    }
    .ntx-search input:focus{
      border-color: rgba(201,75,80,.55);
      box-shadow: 0 0 0 4px rgba(201,75,80,.18);
    }

    /* ✅ Dept dropdown (same nice UI as ref) */
    .ntx-select{
      position: relative;
      min-width: 260px;
      max-width: 360px;
      flex: 0 1 320px;
    }
    .ntx-select__icon{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .70;
      color: var(--ntx-muted);
      pointer-events:none;
      font-size: 14px;
    }
    .ntx-select__caret{
      position:absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .70;
      color: var(--ntx-muted);
      pointer-events:none;
      font-size: 12px;
    }
    .ntx-select select{
      width: 100%;
      height: 42px;
      border-radius: 999px;
      padding: 10px 38px 10px 42px;
      border: 1px solid var(--ntx-line);
      background: var(--ntx-card);
      color: var(--ntx-ink);
      outline: none;

      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
    }
    .ntx-select select:focus{
      border-color: rgba(201,75,80,.55);
      box-shadow: 0 0 0 4px rgba(201,75,80,.18);
    }

    /* Chip */
    .ntx-chip{
      display:flex;
      align-items:center;
      gap: 8px;
      height: 42px;
      padding: 0 12px;
      border-radius: 999px;
      border: 1px solid var(--ntx-line);
      background: var(--ntx-card);
      box-shadow: 0 8px 18px rgba(2,6,23,.06);
      color: var(--ntx-ink);
      font-size: 13px;
      font-weight: 900;
      white-space: nowrap;
    }

    /* Grid */
    .ntx-grid{
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 18px;
      align-items: stretch;
    }

    /* Card */
    .ntx-card{
      width:100%;
      height: var(--ntx-card-h);
      position:relative;
      display:flex;
      flex-direction:column;
      border: 1px solid rgba(2,6,23,.08);
      border-radius: 16px;
      background: #fff;
      box-shadow: var(--ntx-shadow);
      overflow:hidden;
      transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
      will-change: transform;
    }
    .ntx-card:hover{
      transform: translateY(-2px);
      box-shadow: 0 16px 34px rgba(2,6,23,.12);
      border-color: rgba(158,54,58,.22);
    }

    .ntx-media{
      width:100%;
      height: var(--ntx-media-h);
      flex: 0 0 auto;
      background: var(--ntx-brand);
      position:relative;
      overflow:hidden;
      user-select:none;
    }
    .ntx-media .ntx-fallback{
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
      padding: 0 14px;
      text-align:center;
    }
    .ntx-media img{
      position:absolute;
      inset:0;
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
      z-index: 1;
    }

    .ntx-body{
      padding: 16px 16px 14px;
      display:flex;
      flex-direction:column;
      flex: 1 1 auto;
      min-height: 0;
    }
    .ntx-h{
      font-size: 20px;
      line-height: 1.25;
      font-weight: 950;
      margin: 0 0 10px 0;
      color: var(--ntx-ink);

      display:-webkit-box;
      -webkit-line-clamp:2;
      -webkit-box-orient:vertical;
      overflow:hidden;

      overflow-wrap:anywhere;
      word-break:break-word;
    }
    .ntx-p{
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

    .ntx-date{
      margin-top:auto;
      color:#94a3b8;
      font-size: 13px;
      padding-top: 12px;
      display:flex;
      align-items:center;
      gap: 6px;
      flex-wrap: wrap;
    }
    .ntx-date .sep{ opacity:.55; padding: 0 2px; }

    .ntx-link{
      position:absolute;
      inset:0;
      z-index:2;
      border-radius: 16px;
    }

    /* State / empty */
    .ntx-state{
      background: var(--ntx-card);
      border: 1px solid var(--ntx-line);
      border-radius: 16px;
      box-shadow: var(--ntx-shadow);
      padding: 18px;
      color: var(--ntx-muted);
      text-align:center;
    }

    /* Skeleton */
    .ntx-skeleton{
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 18px;
    }
    .ntx-sk{
      border-radius: 16px;
      border: 1px solid var(--ntx-line);
      background: #fff;
      overflow:hidden;
      position:relative;
      box-shadow: 0 10px 24px rgba(2,6,23,.08);
      height: var(--ntx-card-h);
    }
    .ntx-sk:before{
      content:'';
      position:absolute; inset:0;
      transform: translateX(-60%);
      background: linear-gradient(90deg, transparent, rgba(148,163,184,.22), transparent);
      animation: ntxSkMove 1.15s ease-in-out infinite;
    }
    @keyframes ntxSkMove{ to{ transform: translateX(60%);} }

    /* Pagination */
    .ntx-pagination{
      display:flex;
      justify-content:center;
      margin-top: 18px;
    }
    .ntx-pagination .ntx-pager{
      display:flex;
      gap: 8px;
      flex-wrap: wrap;
      align-items:center;
      justify-content:center;
      padding: 10px;
    }
    .ntx-pagebtn{
      border:1px solid var(--ntx-line);
      background: var(--ntx-card);
      color: var(--ntx-ink);
      border-radius: 12px;
      padding: 9px 12px;
      font-size: 13px;
      font-weight: 950;
      box-shadow: 0 8px 18px rgba(2,6,23,.06);
      cursor:pointer;
      user-select:none;
    }
    .ntx-pagebtn:hover{ background: rgba(2,6,23,.03); }
    .ntx-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
    .ntx-pagebtn.active{
      background: rgba(201,75,80,.12);
      border-color: rgba(201,75,80,.35);
      color: var(--ntx-brand);
    }

    @media (max-width: 640px){
      .ntx-title{ font-size: 24px; }
      .ntx-search{ min-width: 220px; flex: 1 1 240px; }
      .ntx-select{ min-width: 220px; flex: 1 1 240px; }
      .ntx-wrap{ --ntx-media-h: 210px; }
      .ntx-media .ntx-fallback{ font-size: 22px; }
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
    class="ntx-wrap"
    data-api="{{ url('/api/public/notices') }}"
    data-view-base="{{ url('/notices/view') }}"
    data-dept-api="{{ url('/api/public/departments') }}"
  >
    <div class="ntx-head">
      <div>
        <h1 class="ntx-title"><i class="fa-solid fa-bullhorn"></i>Notices</h1>
        <div class="ntx-sub" id="ntxSub">Browse all published notices.</div>
      </div>

      <div class="ntx-tools">
        <div class="ntx-search">
          <i class="fa fa-magnifying-glass"></i>
          <input id="ntxSearch" type="search" placeholder="Search notices (title/body/slug)…">
        </div>

        <div class="ntx-select" title="Filter by department">
          <i class="fa-solid fa-building-columns ntx-select__icon"></i>
          <select id="ntxDept" aria-label="Filter by department">
            <option value="">All Departments</option>
          </select>
          <i class="fa-solid fa-chevron-down ntx-select__caret"></i>
        </div>

        <div class="ntx-chip" title="Total results">
          <i class="fa-regular fa-rectangle-list" style="opacity:.85"></i>
          <span id="ntxCount">—</span>
        </div>
      </div>
    </div>

    <div id="ntxGrid" class="ntx-grid" style="display:none;"></div>

    <div id="ntxSkeleton" class="ntx-skeleton"></div>
    <div id="ntxState" class="ntx-state" style="display:none;"></div>

    <div class="ntx-pagination">
      <div id="ntxPager" class="ntx-pager" style="display:none;"></div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  (() => {
    if (window.__PUBLIC_NOTICES_ALL__) return;
    window.__PUBLIC_NOTICES_ALL__ = true;

    const root = document.querySelector('.ntx-wrap');
    if (!root) return;

    const API = root.getAttribute('data-api') || '/api/public/notices';
    const VIEW_BASE = root.getAttribute('data-view-base') || '/notices/view';
    const DEPT_API = root.getAttribute('data-dept-api') || '/api/public/departments';

    const $ = (id) => document.getElementById(id);

    const els = {
      grid: $('ntxGrid'),
      skel: $('ntxSkeleton'),
      state: $('ntxState'),
      pager: $('ntxPager'),
      search: $('ntxSearch'),
      dept: $('ntxDept'),
      count: $('ntxCount'),
      sub: $('ntxSub'),
    };

    const state = {
      page: 1,
      perPage: 9,
      lastPage: 1,
      total: 0,
      q: '',
      deptUuid: '',
      deptId: null,
      deptName: '',
    };

    let activeController = null;

    // cache
    let allNotices = null;
    let deptByUuid = new Map(); // uuid -> {id, title, uuid}

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

    function normalizeUrl(url){
      const u = (url || '').toString().trim();
      if (!u) return '';
      if (/^(data:|blob:|https?:\/\/)/i.test(u)) return u;
      if (u.startsWith('/')) return window.location.origin + u;
      return window.location.origin + '/' + u;
    }

    // ✅ handle image load/error without inline JS
    function bindCardImages(rootEl){
      rootEl.querySelectorAll('img.ntx-img').forEach(img => {
        const media = img.closest('.ntx-media');
        const fallback = media ? media.querySelector('.ntx-fallback') : null;

        if (img.complete && img.naturalWidth > 0) {
          if (fallback) fallback.style.display = 'none';
          return;
        }

        img.addEventListener('load', () => {
          if (fallback) fallback.style.display = 'none';
        }, { once: true });

        img.addEventListener('error', () => {
          img.remove();
          if (fallback) fallback.style.display = 'flex';
        }, { once: true });
      });
    }

    function cardHtml(item){
      const titleRaw = item?.title || 'Untitled';
      const title = esc(titleRaw);

      const bodyText = stripHtml(item?.body || '');
      const MAX_CHARS = 90;

      let excerptText = bodyText;
      if (bodyText.length > MAX_CHARS){
        excerptText = bodyText
          .slice(0, MAX_CHARS)
          .trim()
          .replace(/[,\.;:\-\s]+$/g, '');
        excerptText += '......';
      }

      const excerpt = esc(excerptText || '');

      // Prefer publish date; fallback created_at
      const created = fmtDate(item?.publish_at || item?.created_at || null);

      // dept (optional – shown on date line to keep same structure)
      const deptTitle = (item?.department_title || item?.department?.title || '').toString().trim();

      // open
      const uuid = item?.uuid ? String(item.uuid) : '';
      const slug = item?.slug ? String(item.slug) : '';
      const id   = (item?.id !== null && item?.id !== undefined) ? String(item.id) : '';
      const identifier = uuid || slug || id;

      const href = identifier ? (VIEW_BASE + '/' + encodeURIComponent(identifier)) : '#';

      // cover image
      const cover = item?.cover_image_url || item?.cover_image || '';
      const coverNorm = cover ? normalizeUrl(String(cover).trim()) : '';

      return `
        <div class="ntx-card">
          <div class="ntx-media">
            <div class="ntx-fallback">Notice</div>
            ${coverNorm ? `
              <img class="ntx-img"
                   src="${escAttr(coverNorm)}"
                   alt="${escAttr(titleRaw)}"
                   loading="lazy" />
            ` : ``}
          </div>

          <div class="ntx-body">
            <div class="ntx-h">${title}</div>
            <p class="ntx-p">${excerpt}</p>

            <div class="ntx-date">
              <i class="fa-regular fa-calendar"></i>
              <span>Published: ${esc(created || '—')}</span>
              ${deptTitle ? `<span class="sep">•</span><span>${esc(deptTitle)}</span>` : ``}
            </div>
          </div>

          ${identifier
            ? `<a class="ntx-link" href="${href}" aria-label="Open ${escAttr(titleRaw)}"></a>`
            : `<div class="ntx-link" title="Missing identifier"></div>`
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
      sk.innerHTML = Array.from({length: 6}).map(() => `<div class="ntx-sk"></div>`).join('');
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
        if (els.sub) els.sub.textContent = 'Browse all published notices.';
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
          ? ('Notices for ' + state.deptName)
          : 'Notices (filtered)';
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

        // sort A-Z
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

    async function ensureNoticesLoaded(force=false){
      if (allNotices && !force) return;

      showSkeleton();

      try{
        // ask for a bigger page so frontend filtering always works
        const u = new URL(API, window.location.origin);
        u.searchParams.set('page', '1');
        u.searchParams.set('per_page', '500');
        u.searchParams.set('visible_now', '1');
        u.searchParams.set('sort', 'created_at');
        u.searchParams.set('direction', 'desc');

        const js = await fetchJson(u.toString());
        const items = Array.isArray(js?.data) ? js.data : [];
        allNotices = items;
      } finally {
        hideSkeleton();
      }
    }

    function applyFilterAndSearch(){
      const q = (state.q || '').toString().trim().toLowerCase();

      let items = Array.isArray(allNotices) ? allNotices.slice() : [];

      // ✅ Dept filter: when a dept selected -> show ONLY those matching that dept
      if (state.deptUuid && (state.deptId !== null && state.deptId !== undefined && String(state.deptId) !== '')){
        const deptIdStr = String(state.deptId);
        const deptUuidStr = String(state.deptUuid);

        items = items.filter(it => {
          const did = (it?.department_id === null || it?.department_id === undefined) ? '' : String(it.department_id);
          const duu = (it?.department_uuid === null || it?.department_uuid === undefined) ? '' : String(it.department_uuid);
          return (did === deptIdStr) || (duu && duu === deptUuidStr);
        });
      } else if (state.deptUuid) {
        // if somehow deptId missing, try uuid-only
        const deptUuidStr = String(state.deptUuid);
        items = items.filter(it => String(it?.department_uuid || '') === deptUuidStr);
      }

      // search on title + slug + stripped body
      if (q){
        items = items.filter(it => {
          const t = String(it?.title || '').toLowerCase();
          const s = String(it?.slug || '').toLowerCase();
          const b = stripHtml(it?.body || '').toLowerCase();
          return (t.includes(q) || s.includes(q) || b.includes(q));
        });
      }

      return items;
    }

    function render(items){
      const grid = els.grid, st = els.state, count = els.count;
      if (!grid || !st) return;

      if (count) count.textContent = String(state.total || 0);

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
          No notices found.
          ${deptLine}
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
        const cls = active ? 'ntx-pagebtn active' : 'ntx-pagebtn';
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

    document.addEventListener('DOMContentLoaded', async () => {
      await loadDepartments();

      // deep-link: ?d-{uuid} anywhere
      const deepDeptUuid = extractDeptUuidFromUrl();
      if (deepDeptUuid && deptByUuid.has(deepDeptUuid)){
        setDeptSelection(deepDeptUuid);
      } else {
        setDeptSelection('');
      }

      // load notices once, then filter client-side (keeps dept filter reliable)
      await ensureNoticesLoaded(false);
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

        if (!v){
          setDeptSelection('');
        } else {
          setDeptSelection(v);
        }

        state.page = 1;
        repaint();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });

      // pagination click
      document.addEventListener('click', (e) => {
        const b = e.target.closest('button.ntx-pagebtn[data-page]');
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
