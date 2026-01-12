{{-- resources/views/public/events/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name','College Portal') }} — Events</title>

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  {{-- Common UI tokens --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    /* =========================================================
      ✅ Events (Scoped / No :root / No global body rules)
      - UI structure matches Announcements reference for consistency
      - Dept dropdown UI improved (pill, icon, caret)
      - Dept filtering (frontend filter by department_id / department_uuid)
      - Deep-link ?d-{uuid} auto-selects dept and filters
    ========================================================= */

    .evx-wrap{
      /* scoped tokens */
      --evx-brand: var(--primary-color, #9E363A);
      --evx-ink: #0f172a;
      --evx-muted: #64748b;
      --evx-bg: var(--page-bg, #ffffff);
      --evx-card: var(--surface, #ffffff);
      --evx-line: var(--line-soft, rgba(15,23,42,.10));
      --evx-shadow: 0 10px 24px rgba(2,6,23,.08);

      /* card sizing */
      --evx-card-h: 426.4px;
      --evx-media-h: 240px;

      max-width: 1320px;
      margin: 18px auto 54px;
      padding: 0 12px;
      background: transparent;
      position: relative;
      overflow: visible;
    }

    /* Header */
    .evx-head{
      background: var(--evx-card);
      border: 1px solid var(--evx-line);
      border-radius: 16px;
      box-shadow: var(--evx-shadow);
      padding: 14px 16px;
      margin-bottom: 16px;

      display:flex;
      gap: 12px;
      align-items: center;
      justify-content: space-between;

      /* ✅ one-row head (desktop) */
      flex-wrap: nowrap;
    }
    .evx-title{
      margin: 0;
      font-weight: 950;
      letter-spacing: .2px;
      color: var(--evx-ink);
      font-size: 28px;
      display:flex;
      align-items:center;
      gap: 10px;
      white-space: nowrap;
    }
    .evx-title i{ color: var(--evx-brand); }
    .evx-sub{
      margin: 6px 0 0;
      color: var(--evx-muted);
      font-size: 14px;
    }

    .evx-tools{
      display:flex;
      gap: 10px;
      align-items:center;

      /* ✅ keep tools in one row */
      flex-wrap: nowrap;
    }

    /* Search */
    .evx-search{
      position: relative;
      min-width: 260px;
      max-width: 520px;
      flex: 1 1 320px;
    }
    .evx-search i{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .65;
      color: var(--evx-muted);
      pointer-events:none;
    }
    .evx-search input{
      width:100%;
      height: 42px;
      border-radius: 999px;
      padding: 11px 12px 11px 42px;
      border: 1px solid var(--evx-line);
      background: var(--evx-card);
      color: var(--evx-ink);
      outline: none;
    }
    .evx-search input:focus{
      border-color: rgba(201,75,80,.55);
      box-shadow: 0 0 0 4px rgba(201,75,80,.18);
    }

    /* ✅ Dept dropdown (nicer UI) */
    .evx-select{
      position: relative;
      min-width: 260px;
      max-width: 360px;
      flex: 0 1 320px;
    }
    .evx-select__icon{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .70;
      color: var(--evx-muted);
      pointer-events:none;
      font-size: 14px;
    }
    .evx-select__caret{
      position:absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .70;
      color: var(--evx-muted);
      pointer-events:none;
      font-size: 12px;
    }
    .evx-select select{
      width: 100%;
      height: 42px;
      border-radius: 999px;
      padding: 10px 38px 10px 42px; /* left icon + right caret */
      border: 1px solid var(--evx-line);
      background: var(--evx-card);
      color: var(--evx-ink);
      outline: none;

      /* remove native arrow */
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
    }
    .evx-select select:focus{
      border-color: rgba(201,75,80,.55);
      box-shadow: 0 0 0 4px rgba(201,75,80,.18);
    }

    /* Grid */
    .evx-grid{
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 18px;
      align-items: stretch;
    }

    /* Card */
    .evx-card{
      width:100%;
      height: var(--evx-card-h);
      position:relative;
      display:flex;
      flex-direction:column;
      border: 1px solid rgba(2,6,23,.08);
      border-radius: 16px;
      background: #fff;
      box-shadow: var(--evx-shadow);
      overflow:hidden;
      transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
      will-change: transform;
    }
    .evx-card:hover{
      transform: translateY(-2px);
      box-shadow: 0 16px 34px rgba(2,6,23,.12);
      border-color: rgba(158,54,58,.22);
    }

    .evx-media{
      width:100%;
      height: var(--evx-media-h);
      flex: 0 0 auto;
      background: var(--evx-brand);
      position:relative;
      overflow:hidden;
      user-select:none;
    }
    .evx-media .evx-fallback{
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
      line-height:1.15;
    }
    .evx-media img{
      position:absolute;
      inset:0;
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
      z-index: 1;
    }

    .evx-body{
      padding: 16px 16px 14px;
      display:flex;
      flex-direction:column;
      flex: 1 1 auto;
      min-height: 0;
    }
    .evx-h{
      font-size: 20px;
      line-height: 1.25;
      font-weight: 950;
      margin: 0 0 10px 0;
      color: var(--evx-ink);

      display:-webkit-box;
      -webkit-line-clamp:2;
      -webkit-box-orient:vertical;
      overflow:hidden;

      overflow-wrap:anywhere;
      word-break:break-word;
    }

    .evx-meta{
      display:flex;
      flex-direction:column;
      gap:6px;
      margin: 0 0 10px 0;
      color:#334155;
      font-weight:800;
      font-size:13px;
    }
    .evx-meta .rowx{
      display:flex;
      align-items:center;
      gap:8px;
      min-height:18px;
      overflow:hidden;
    }
    .evx-meta i{
      width:16px;
      text-align:center;
      color: var(--evx-muted);
      flex:0 0 auto;
    }
    .evx-meta span{
      display:block;
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
    }

    .evx-p{
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

    .evx-date{
      margin-top:auto;
      color:#94a3b8;
      font-size: 13px;
      padding-top: 12px;
      display:flex;
      align-items:center;
      gap: 6px;
    }

    .evx-link{
      position:absolute;
      inset:0;
      z-index:2;
      border-radius: 16px;
    }

    /* State / empty */
    .evx-state{
      background: var(--evx-card);
      border: 1px solid var(--evx-line);
      border-radius: 16px;
      box-shadow: var(--evx-shadow);
      padding: 18px;
      color: var(--evx-muted);
      text-align:center;
    }

    /* Skeleton */
    .evx-skeleton{
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 18px;
    }
    .evx-sk{
      border-radius: 16px;
      border: 1px solid var(--evx-line);
      background: #fff;
      overflow:hidden;
      position:relative;
      box-shadow: 0 10px 24px rgba(2,6,23,.08);
      height: var(--evx-card-h);
    }
    .evx-sk:before{
      content:'';
      position:absolute; inset:0;
      transform: translateX(-60%);
      background: linear-gradient(90deg, transparent, rgba(148,163,184,.22), transparent);
      animation: evxSkMove 1.15s ease-in-out infinite;
    }
    @keyframes evxSkMove{ to{ transform: translateX(60%);} }

    /* Pagination */
    .evx-pagination{
      display:flex;
      justify-content:center;
      margin-top: 18px;
    }
    .evx-pagination .evx-pager{
      display:flex;
      gap: 8px;
      flex-wrap: wrap;
      align-items:center;
      justify-content:center;
      padding: 10px;
    }
    .evx-pagebtn{
      border:1px solid var(--evx-line);
      background: var(--evx-card);
      color: var(--evx-ink);
      border-radius: 12px;
      padding: 9px 12px;
      font-size: 13px;
      font-weight: 950;
      box-shadow: 0 8px 18px rgba(2,6,23,.06);
      cursor:pointer;
      user-select:none;
    }
    .evx-pagebtn:hover{ background: rgba(2,6,23,.03); }
    .evx-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
    .evx-pagebtn.active{
      background: rgba(201,75,80,.12);
      border-color: rgba(201,75,80,.35);
      color: var(--evx-brand);
    }

    @media (max-width: 640px){
      /* ✅ allow wrap on small screens so it doesn't overflow */
      .evx-head{ flex-wrap: wrap; align-items: flex-end; }
      .evx-tools{ flex-wrap: wrap; }

      .evx-title{ font-size: 24px; white-space: normal; }
      .evx-search{ min-width: 220px; flex: 1 1 240px; }
      .evx-select{ min-width: 220px; flex: 1 1 240px; }
      .evx-wrap{ --evx-media-h: 210px; }
      .evx-media .evx-fallback{ font-size: 22px; }
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
    class="evx-wrap"
    data-api="{{ url('/api/public/events') }}"
    data-view-base="{{ url('/events/view') }}"
    data-dept-api="{{ url('/api/public/departments') }}"
  >
    <div class="evx-head">
      <div>
        <h1 class="evx-title"><i class="fa-solid fa-calendar-days"></i>Events</h1>
        <div class="evx-sub" id="evxSub">Workshops, seminars, fests, and campus activities.</div>
      </div>

      <div class="evx-tools">
        <div class="evx-search">
          <i class="fa fa-magnifying-glass"></i>
          <input id="evxSearch" type="search" placeholder="Search events (title/location/description)…">
        </div>

        <div class="evx-select" title="Filter by department">
          <i class="fa-solid fa-building-columns evx-select__icon"></i>
          <select id="evxDept" aria-label="Filter by department">
            <option value="">All Departments</option>
          </select>
          <i class="fa-solid fa-chevron-down evx-select__caret"></i>
        </div>

        {{-- ✅ Count chip removed --}}
      </div>
    </div>

    <div id="evxGrid" class="evx-grid" style="display:none;"></div>

    <div id="evxSkeleton" class="evx-skeleton"></div>
    <div id="evxState" class="evx-state" style="display:none;"></div>

    <div class="evx-pagination">
      <div id="evxPager" class="evx-pager" style="display:none;"></div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  (() => {
    if (window.__PUBLIC_EVENTS_ALL__) return;
    window.__PUBLIC_EVENTS_ALL__ = true;

    const root = document.querySelector('.evx-wrap');
    if (!root) return;

    const API = root.getAttribute('data-api') || '/api/public/events';
    const VIEW_BASE = root.getAttribute('data-view-base') || '/events/view';
    const DEPT_API = root.getAttribute('data-dept-api') || '/api/public/departments';

    const $ = (id) => document.getElementById(id);

    const els = {
      grid: $('evxGrid'),
      skel: $('evxSkeleton'),
      state: $('evxState'),
      pager: $('evxPager'),
      search: $('evxSearch'),
      dept: $('evxDept'),
      sub: $('evxSub'),
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
    let allEvents = null;
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

    function fmtTime(t){
      if (!t) return '';
      const parts = String(t).split(':');
      if (parts.length < 2) return '';
      const hh = parseInt(parts[0], 10);
      const mm = parts[1];
      if (Number.isNaN(hh)) return '';
      const ampm = hh >= 12 ? 'PM' : 'AM';
      const h12 = ((hh + 11) % 12) + 1;
      return `${h12}:${mm} ${ampm}`;
    }

    function buildWhen(it){
      const sd = it?.event_start_date ? fmtDate(it.event_start_date) : '';
      const ed = it?.event_end_date ? fmtDate(it.event_end_date) : '';
      const st = it?.event_start_time ? fmtTime(it.event_start_time) : '';
      const et = it?.event_end_time ? fmtTime(it.event_end_time) : '';

      let datePart = '';
      if (sd && ed && sd !== ed) datePart = `${sd} – ${ed}`;
      else datePart = sd || ed || '';

      let timePart = '';
      if (st && et) timePart = `${st} – ${et}`;
      else timePart = st || et || '';

      if (datePart && timePart) return `${datePart} • ${timePart}`;
      return datePart || timePart || '';
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
      rootEl.querySelectorAll('img.evx-img').forEach(img => {
        const media = img.closest('.evx-media');
        const fallback = media ? media.querySelector('.evx-fallback') : null;

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

      const locRaw = (item?.location ?? '').toString().trim();
      const whenRaw = buildWhen(item);

      const descText = stripHtml(item?.description || item?.body || '');
      const MAX_CHARS = 120;

      let excerptText = descText;
      if (descText.length > MAX_CHARS){
        excerptText = descText
          .slice(0, MAX_CHARS)
          .trim()
          .replace(/[,\.;:\-\s]+$/g, '');
        excerptText += '......';
      }

      const excerpt = esc(excerptText || '');
      const created = fmtDate(item?.created_at || null);

      const uuid = item?.uuid ? String(item.uuid) : '';
      const href = uuid ? (VIEW_BASE + '/' + encodeURIComponent(uuid)) : '#';

      const cover = item?.cover_image_url || item?.cover_image || item?.image_url || '';
      const coverNorm = cover ? normalizeUrl(String(cover).trim()) : '';

      return `
        <div class="evx-card">
          <div class="evx-media">
            <div class="evx-fallback">${esc(titleRaw || 'Event')}</div>
            ${coverNorm ? `
              <img class="evx-img"
                   src="${escAttr(coverNorm)}"
                   alt="${escAttr(titleRaw)}"
                   loading="lazy" />
            ` : ``}
          </div>

          <div class="evx-body">
            <div class="evx-h">${title}</div>

            <div class="evx-meta">
              <div class="rowx" title="${escAttr(locRaw)}">
                <i class="fa-solid fa-location-dot"></i>
                <span>${locRaw ? esc(locRaw) : '—'}</span>
              </div>
              <div class="rowx" title="${escAttr(whenRaw)}">
                <i class="fa-regular fa-clock"></i>
                <span>${whenRaw ? esc(whenRaw) : '—'}</span>
              </div>
            </div>

            <p class="evx-p">${excerpt || ''}</p>

            <div class="evx-date">
              <i class="fa-regular fa-calendar"></i>
              <span>Created: ${esc(created || '—')}</span>
            </div>
          </div>

          ${uuid
            ? `<a class="evx-link" href="${href}" aria-label="Open ${escAttr(titleRaw)}"></a>`
            : `<div class="evx-link" title="Missing UUID"></div>`
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
      sk.innerHTML = Array.from({length: 6}).map(() => `<div class="evx-sk"></div>`).join('');
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
        if (els.sub) els.sub.textContent = 'Workshops, seminars, fests, and campus activities.';
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
          ? ('Events for ' + state.deptName)
          : 'Events (filtered)';
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

    function extractItemsFromResponse(js){
      // supports: {data: [...]}, or {success:true, data:[...]}
      if (Array.isArray(js?.data)) return js.data;
      if (Array.isArray(js?.data?.data)) return js.data.data; // just in case
      return [];
    }

    async function ensureEventsLoaded(force=false){
      if (allEvents && !force) return;

      showSkeleton();

      try{
        // ask for a bigger page so frontend filtering always works
        const u = new URL(API, window.location.origin);
        u.searchParams.set('page', '1');
        u.searchParams.set('per_page', '300');
        u.searchParams.set('visible_now', '1');
        u.searchParams.set('sort', 'created_at');
        u.searchParams.set('direction', 'desc');

        const js = await fetchJson(u.toString());
        allEvents = extractItemsFromResponse(js);

      } finally {
        hideSkeleton();
      }
    }

    function applyFilterAndSearch(){
      const q = (state.q || '').toString().trim().toLowerCase();

      let items = Array.isArray(allEvents) ? allEvents.slice() : [];

      // ✅ DEPT FILTER (frontend filter by department_id / department_uuid)
      if (state.deptUuid && (state.deptId !== null && state.deptId !== undefined && String(state.deptId) !== '')){
        const deptIdStr = String(state.deptId);
        const deptUuidStr = String(state.deptUuid);

        items = items.filter(it => {
          const did = (it?.department_id === null || it?.department_id === undefined) ? '' : String(it.department_id);
          const duu = (it?.department_uuid === null || it?.department_uuid === undefined) ? '' : String(it.department_uuid);
          return (did === deptIdStr) || (duu && duu === deptUuidStr);
        });
      } else if (state.deptUuid) {
        const deptUuidStr = String(state.deptUuid);
        items = items.filter(it => String(it?.department_uuid || '') === deptUuidStr);
      }

      // search on title + location + stripped description/body
      if (q){
        items = items.filter(it => {
          const t = String(it?.title || '').toLowerCase();
          const l = String(it?.location || '').toLowerCase();
          const d = stripHtml(it?.description || it?.body || '').toLowerCase();
          return (t.includes(q) || l.includes(q) || d.includes(q));
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
        const deptLine = state.deptName ? `<div style="margin-top:6px;font-size:12.5px;opacity:.95;">Department: <b>${esc(state.deptName)}</b></div>` : '';
        st.innerHTML = `
          <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
            <i class="fa-regular fa-face-frown"></i>
          </div>
          No events found.
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
        const cls = active ? 'evx-pagebtn active' : 'evx-pagebtn';
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

      // deep-link
      const deepDeptUuid = extractDeptUuidFromUrl();
      if (deepDeptUuid && deptByUuid.has(deepDeptUuid)){
        setDeptSelection(deepDeptUuid);
      } else {
        setDeptSelection('');
      }

      // load events once, then filter client-side
      await ensureEventsLoaded(false);
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
        const b = e.target.closest('button.evx-pagebtn[data-page]');
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
