{{-- resources/views/public/successStories/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name','College Portal') }} — Success Stories</title>

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  {{-- Common UI tokens --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    /* =========================================================
      ✅ Success Stories (Scoped / No :root / No global body rules)
      - UI structure matches Announcements reference
      - Dept dropdown UI improved (pill, icon, caret)
      - Dept filtering (frontend filter by department_id / department_uuid)
      - Deep-link ?d-{uuid} auto-selects dept and filters
    ========================================================= */

    .ss-wrap{
      /* scoped tokens */
      --ss-brand: var(--primary-color, #9E363A);
      --ss-ink: #0f172a;
      --ss-muted: #64748b;
      --ss-bg: var(--page-bg, #ffffff);
      --ss-card: var(--surface, #ffffff);
      --ss-line: var(--line-soft, rgba(15,23,42,.10));
      --ss-shadow: 0 10px 24px rgba(2,6,23,.08);

      /* card sizing */
      --ss-card-h: 426.4px;
      --ss-media-h: 240px;

      max-width: 1320px;
      margin: 18px auto 54px;
      padding: 0 12px;
      background: transparent;
      position: relative;
      overflow: visible;
    }

    /* Header */
    .ss-head{
      background: var(--ss-card);
      border: 1px solid var(--ss-line);
      border-radius: 16px;
      box-shadow: var(--ss-shadow);
      padding: 14px 16px;
      margin-bottom: 16px;

      display:flex;
      gap: 12px;
      align-items: flex-end;
      justify-content: space-between;
      flex-wrap: wrap;
    }
    .ss-title{
      margin: 0;
      font-weight: 950;
      letter-spacing: .2px;
      color: var(--ss-ink);
      font-size: 28px;
      display:flex;
      align-items:center;
      gap: 10px;
    }
    .ss-title i{ color: var(--ss-brand); }
    .ss-sub{
      margin: 6px 0 0;
      color: var(--ss-muted);
      font-size: 14px;
    }

    .ss-tools{
      display:flex;
      gap: 10px;
      align-items:center;
      flex-wrap: wrap;
    }

    /* Search */
    .ss-search{
      position: relative;
      min-width: 260px;
      max-width: 520px;
      flex: 1 1 320px;
    }
    .ss-search i{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .65;
      color: var(--ss-muted);
      pointer-events:none;
    }
    .ss-search input{
      width:100%;
      height: 42px;
      border-radius: 999px;
      padding: 11px 12px 11px 42px;
      border: 1px solid var(--ss-line);
      background: var(--ss-card);
      color: var(--ss-ink);
      outline: none;
    }
    .ss-search input:focus{
      border-color: rgba(201,75,80,.55);
      box-shadow: 0 0 0 4px rgba(201,75,80,.18);
    }

    /* ✅ Dept dropdown (nicer UI) */
    .ss-select{
      position: relative;
      min-width: 260px;
      max-width: 360px;
      flex: 0 1 320px;
    }
    .ss-select__icon{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .70;
      color: var(--ss-muted);
      pointer-events:none;
      font-size: 14px;
    }
    .ss-select__caret{
      position:absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .70;
      color: var(--ss-muted);
      pointer-events:none;
      font-size: 12px;
    }
    .ss-select select{
      width: 100%;
      height: 42px;
      border-radius: 999px;
      padding: 10px 38px 10px 42px; /* left icon + right caret */
      border: 1px solid var(--ss-line);
      background: var(--ss-card);
      color: var(--ss-ink);
      outline: none;

      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
    }
    .ss-select select:focus{
      border-color: rgba(201,75,80,.55);
      box-shadow: 0 0 0 4px rgba(201,75,80,.18);
    }

    /* Chip */
    .ss-chip{
      display:flex;
      align-items:center;
      gap: 8px;
      height: 42px;
      padding: 0 12px;
      border-radius: 999px;
      border: 1px solid var(--ss-line);
      background: var(--ss-card);
      box-shadow: 0 8px 18px rgba(2,6,23,.06);
      color: var(--ss-ink);
      font-size: 13px;
      font-weight: 900;
      white-space: nowrap;
    }

    /* Grid */
    .ss-grid{
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 18px;
      align-items: stretch;
    }

    /* Card */
    .ss-card{
      width:100%;
      height: var(--ss-card-h);
      position:relative;
      display:flex;
      flex-direction:column;
      border: 1px solid rgba(2,6,23,.08);
      border-radius: 16px;
      background: #fff;
      box-shadow: var(--ss-shadow);
      overflow:hidden;
      transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
      will-change: transform;
    }
    .ss-card:hover{
      transform: translateY(-2px);
      box-shadow: 0 16px 34px rgba(2,6,23,.12);
      border-color: rgba(158,54,58,.22);
    }

    .ss-media{
      width:100%;
      height: var(--ss-media-h);
      flex: 0 0 auto;
      background: var(--ss-brand);
      position:relative;
      overflow:hidden;
      user-select:none;
    }
    .ss-media .ss-fallback{
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
    }
    .ss-media img{
      position:absolute;
      inset:0;
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
      z-index: 1;
    }

    .ss-body{
      padding: 16px 16px 14px;
      display:flex;
      flex-direction:column;
      flex: 1 1 auto;
      min-height: 0;
    }
    .ss-h{
      font-size: 20px;
      line-height: 1.25;
      font-weight: 950;
      margin: 0 0 8px 0;
      color: var(--ss-ink);

      display:-webkit-box;
      -webkit-line-clamp:2;
      -webkit-box-orient:vertical;
      overflow:hidden;

      overflow-wrap:anywhere;
      word-break:break-word;
    }
    .ss-subline{
      margin: 0 0 10px 0;
      color: #334155;
      font-weight: 800;
      font-size: 13px;

      display:-webkit-box;
      -webkit-line-clamp:1;
      -webkit-box-orient:vertical;
      overflow:hidden;

      min-height: 17px;
    }
    .ss-p{
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

    .ss-date{
      margin-top:auto;
      color:#94a3b8;
      font-size: 13px;
      padding-top: 12px;
      display:flex;
      align-items:center;
      gap: 6px;
    }

    .ss-link{
      position:absolute;
      inset:0;
      z-index:2;
      border-radius: 16px;
    }

    /* State / empty */
    .ss-state{
      background: var(--ss-card);
      border: 1px solid var(--ss-line);
      border-radius: 16px;
      box-shadow: var(--ss-shadow);
      padding: 18px;
      color: var(--ss-muted);
      text-align:center;
    }

    /* Skeleton */
    .ss-skeleton{
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 18px;
    }
    .ss-sk{
      border-radius: 16px;
      border: 1px solid var(--ss-line);
      background: #fff;
      overflow:hidden;
      position:relative;
      box-shadow: 0 10px 24px rgba(2,6,23,.08);
      height: var(--ss-card-h);
    }
    .ss-sk:before{
      content:'';
      position:absolute; inset:0;
      transform: translateX(-60%);
      background: linear-gradient(90deg, transparent, rgba(148,163,184,.22), transparent);
      animation: ssSkMove 1.15s ease-in-out infinite;
    }
    @keyframes ssSkMove{ to{ transform: translateX(60%);} }

    /* Pagination */
    .ss-pagination{
      display:flex;
      justify-content:center;
      margin-top: 18px;
    }
    .ss-pagination .ss-pager{
      display:flex;
      gap: 8px;
      flex-wrap: wrap;
      align-items:center;
      justify-content:center;
      padding: 10px;
    }
    .ss-pagebtn{
      border:1px solid var(--ss-line);
      background: var(--ss-card);
      color: var(--ss-ink);
      border-radius: 12px;
      padding: 9px 12px;
      font-size: 13px;
      font-weight: 950;
      box-shadow: 0 8px 18px rgba(2,6,23,.06);
      cursor:pointer;
      user-select:none;
    }
    .ss-pagebtn:hover{ background: rgba(2,6,23,.03); }
    .ss-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
    .ss-pagebtn.active{
      background: rgba(201,75,80,.12);
      border-color: rgba(201,75,80,.35);
      color: var(--ss-brand);
    }

    @media (max-width: 640px){
      .ss-title{ font-size: 24px; }
      .ss-search{ min-width: 220px; flex: 1 1 240px; }
      .ss-select{ min-width: 220px; flex: 1 1 240px; }
      .ss-wrap{ --ss-media-h: 210px; }
      .ss-media .ss-fallback{ font-size: 22px; }
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
    class="ss-wrap"
    data-api="{{ url('/api/public/success-stories') }}"
    data-view-base="{{ url('/success-stories/view') }}"
    data-dept-api="{{ url('/api/public/departments') }}"
  >
    <div class="ss-head">
      <div>
        <h1 class="ss-title"><i class="fa-solid fa-trophy"></i>Success Stories</h1>
        <div class="ss-sub" id="ssSub">Alumni journeys, placements, and inspiring wins.</div>
      </div>

      <div class="ss-tools">
        <div class="ss-search">
          <i class="fa fa-magnifying-glass"></i>
          <input id="ssSearch" type="search" placeholder="Search success stories (name/title/quote)…">
        </div>

        <div class="ss-select" title="Filter by department">
          <i class="fa-solid fa-building-columns ss-select__icon"></i>
          <select id="ssDept" aria-label="Filter by department">
            <option value="">All Departments</option>
          </select>
          <i class="fa-solid fa-chevron-down ss-select__caret"></i>
        </div>

        <div class="ss-chip" title="Total results">
          <i class="fa-regular fa-rectangle-list" style="opacity:.85"></i>
          <span id="ssCount">—</span>
        </div>
      </div>
    </div>

    <div id="ssGrid" class="ss-grid" style="display:none;"></div>

    <div id="ssSkeleton" class="ss-skeleton"></div>
    <div id="ssState" class="ss-state" style="display:none;"></div>

    <div class="ss-pagination">
      <div id="ssPager" class="ss-pager" style="display:none;"></div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  (() => {
    if (window.__PUBLIC_SUCCESS_STORIES_ALL__) return;
    window.__PUBLIC_SUCCESS_STORIES_ALL__ = true;

    const root = document.querySelector('.ss-wrap');
    if (!root) return;

    const API = root.getAttribute('data-api') || '/api/public/success-stories';
    const VIEW_BASE = root.getAttribute('data-view-base') || '/success-stories/view';
    const DEPT_API = root.getAttribute('data-dept-api') || '/api/public/departments';

    const $ = (id) => document.getElementById(id);

    const els = {
      grid: $('ssGrid'),
      skel: $('ssSkeleton'),
      state: $('ssState'),
      pager: $('ssPager'),
      search: $('ssSearch'),
      dept: $('ssDept'),
      count: $('ssCount'),
      sub: $('ssSub'),
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
    let allStories = null;
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
      rootEl.querySelectorAll('img.ss-img').forEach(img => {
        const media = img.closest('.ss-media');
        const fallback = media ? media.querySelector('.ss-fallback') : null;

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
      const nameRaw  = item?.name || 'Student';
      const titleRaw = item?.title || '';
      const quoteRaw = item?.quote || item?.description || '';

      const name = esc(nameRaw);
      const title = esc(titleRaw);

      const quoteText = stripHtml(quoteRaw);
      const MAX_CHARS = 110;

      let excerptText = quoteText;
      if (quoteText.length > MAX_CHARS){
        excerptText = quoteText
          .slice(0, MAX_CHARS)
          .trim()
          .replace(/[,\.;:\-\s]+$/g, '');
        excerptText += '......';
      }

      const excerpt = esc(excerptText || '');
      const created = fmtDate(item?.created_at || null);

      const uuid = item?.uuid ? String(item.uuid) : '';
      const href = uuid ? (VIEW_BASE + '/' + encodeURIComponent(uuid)) : '#';

      const photo =
        item?.photo_full_url ||
        item?.photo_url ||
        item?.photo ||
        item?.cover_image_url ||
        item?.cover_image ||
        '';

      const photoNorm = photo ? normalizeUrl(String(photo).trim()) : '';

      return `
        <div class="ss-card">
          <div class="ss-media">
            <div class="ss-fallback">Success Story</div>
            ${photoNorm ? `
              <img class="ss-img"
                   src="${escAttr(photoNorm)}"
                   alt="${escAttr(nameRaw)}"
                   loading="lazy" />
            ` : ``}
          </div>

          <div class="ss-body">
            <div class="ss-h">${name}</div>
            <div class="ss-subline">${title || '&nbsp;'}</div>
            <p class="ss-p">${excerpt}</p>

            <div class="ss-date">
              <i class="fa-regular fa-calendar"></i>
              <span>Created: ${esc(created || '—')}</span>
            </div>
          </div>

          ${uuid
            ? `<a class="ss-link" href="${href}" aria-label="Open ${escAttr(nameRaw)}"></a>`
            : `<div class="ss-link" title="Missing UUID"></div>`
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
      sk.innerHTML = Array.from({length: 6}).map(() => `<div class="ss-sk"></div>`).join('');
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
        if (els.sub) els.sub.textContent = 'Alumni journeys, placements, and inspiring wins.';
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
          ? ('Success stories for ' + state.deptName)
          : 'Success stories (filtered)';
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

    async function ensureStoriesLoaded(force=false){
      if (allStories && !force) return;

      showSkeleton();

      try{
        // ask for a bigger page so frontend filtering always works
        const u = new URL(API, window.location.origin);
        u.searchParams.set('page', '1');
        u.searchParams.set('per_page', '200');
        u.searchParams.set('visible_now', '1');
        u.searchParams.set('sort', 'created_at');
        u.searchParams.set('direction', 'desc');

        const js = await fetchJson(u.toString());
        const items = Array.isArray(js?.data) ? js.data : [];
        allStories = items;

      } finally {
        hideSkeleton();
      }
    }

    function applyFilterAndSearch(){
      const q = (state.q || '').toString().trim().toLowerCase();
      let items = Array.isArray(allStories) ? allStories.slice() : [];

      // ✅ DEPT FILTER
      if (state.deptUuid && (state.deptId !== null && state.deptId !== undefined && String(state.deptId) !== '')){
        const deptIdStr = String(state.deptId);
        const deptUuidStr = String(state.deptUuid);

        items = items.filter(it => {
          const did = (it?.department_id === null || it?.department_id === undefined) ? '' : String(it.department_id);
          const duu = (it?.department_uuid === null || it?.department_uuid === undefined) ? '' : String(it.department_uuid);
          // when a dept is selected -> show ONLY those matched to dept
          return (did === deptIdStr) || (duu && duu === deptUuidStr);
        });
      } else if (state.deptUuid) {
        // if somehow deptId missing, try uuid-only
        const deptUuidStr = String(state.deptUuid);
        items = items.filter(it => String(it?.department_uuid || '') === deptUuidStr);
      }

      // search on name + title + quote/description
      if (q){
        items = items.filter(it => {
          const n = String(it?.name || '').toLowerCase();
          const t = String(it?.title || '').toLowerCase();
          const p = stripHtml(it?.quote || it?.description || '').toLowerCase();
          return (n.includes(q) || t.includes(q) || p.includes(q));
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
        const deptLine = state.deptName ? `<div style="margin-top:6px;font-size:12.5px;opacity:.95;">Department: <b>${esc(state.deptName)}</b></div>` : '';
        st.innerHTML = `
          <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
            <i class="fa-regular fa-face-frown"></i>
          </div>
          No success stories found.
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
        const cls = active ? 'ss-pagebtn active' : 'ss-pagebtn';
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

      // deep-link ?d-{uuid}
      const deepDeptUuid = extractDeptUuidFromUrl();
      if (deepDeptUuid && deptByUuid.has(deepDeptUuid)){
        setDeptSelection(deepDeptUuid);
      } else {
        setDeptSelection('');
      }

      // load once, then filter client-side
      await ensureStoriesLoaded(false);
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
        const b = e.target.closest('button.ss-pagebtn[data-page]');
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
