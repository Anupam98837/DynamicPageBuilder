{{-- resources/views/public/courses/index.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name','College Portal') }} — Courses</title>

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  {{-- Common UI tokens --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    /* =========================================================
      ✅ Courses (Scoped / No :root / No global body rules)
      - UI structure matches Announcements reference
      - Dept dropdown + deep-link ?d-{uuid} supported
      - Dept filtering FIXED (frontend filter by department_id / department_uuid)
      - ✅ Count chip removed + head kept in one row (desktop)
      - ✅ NEW: Level filter (program_level)
      - ✅ Removed: Created text/date from cards
    ========================================================= */

    .csx-wrap{
      /* scoped tokens */
      --csx-brand: var(--primary-color, #9E363A);
      --csx-ink: #0f172a;
      --csx-muted: #64748b;
      --csx-bg: var(--page-bg, #ffffff);
      --csx-card: var(--surface, #ffffff);
      --csx-line: var(--line-soft, rgba(15,23,42,.10));
      --csx-shadow: 0 10px 24px rgba(2,6,23,.08);

      /* card sizing (same as reference) */
      --csx-card-h: 426.4px;
      --csx-media-h: 240px;

      max-width: 1320px;
      margin: 18px auto 54px;
      padding: 0 12px;
      background: transparent;
      position: relative;
      overflow: visible;
    }

    /* Header */
    .csx-head{
      background: var(--csx-card);
      border: 1px solid var(--csx-line);
      border-radius: 16px;
      box-shadow: var(--csx-shadow);
      padding: 14px 16px;
      margin-bottom: 16px;

      display:flex;
      gap: 12px;
      align-items: center;
      justify-content: space-between;

      /* ✅ keep in one row on desktop */
      flex-wrap: nowrap;
    }
    .csx-title{
      margin: 0;
      font-weight: 950;
      letter-spacing: .2px;
      color: var(--csx-ink);
      font-size: 28px;
      display:flex;
      align-items:center;
      gap: 10px;
      white-space: nowrap;
    }
    .csx-title i{ color: var(--csx-brand); }
    .csx-sub{
      margin: 6px 0 0;
      color: var(--csx-muted);
      font-size: 14px;
    }

    .csx-tools{
      display:flex;
      gap: 10px;
      align-items:center;

      /* ✅ keep tools in one row on desktop */
      flex-wrap: nowrap;
    }

    /* Search */
    .csx-search{
      position: relative;
      min-width: 260px;
      max-width: 520px;
      flex: 1 1 320px;
    }
    .csx-search i{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .65;
      color: var(--csx-muted);
      pointer-events:none;
    }
    .csx-search input{
      width:100%;
      height: 42px;
      border-radius: 999px;
      padding: 11px 12px 11px 42px;
      border: 1px solid var(--csx-line);
      background: var(--csx-card);
      color: var(--csx-ink);
      outline: none;
    }
    .csx-search input:focus{
      border-color: rgba(201,75,80,.55);
      box-shadow: 0 0 0 4px rgba(201,75,80,.18);
    }

    /* Select dropdown (shared) */
    .csx-select{
      position: relative;
      min-width: 260px;
      max-width: 360px;
      flex: 0 1 320px;
    }
    /* ✅ Level select slightly smaller so head row still fits */
    .csx-select.csx-level{
      min-width: 210px;
      max-width: 260px;
      flex: 0 1 260px;
    }

    .csx-select__icon{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .70;
      color: var(--csx-muted);
      pointer-events:none;
      font-size: 14px;
    }
    .csx-select__caret{
      position:absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .70;
      color: var(--csx-muted);
      pointer-events:none;
      font-size: 12px;
    }
    .csx-select select{
      width: 100%;
      height: 42px;
      border-radius: 999px;
      padding: 10px 38px 10px 42px;
      border: 1px solid var(--csx-line);
      background: var(--csx-card);
      color: var(--csx-ink);
      outline: none;

      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
    }
    .csx-select select:focus{
      border-color: rgba(201,75,80,.55);
      box-shadow: 0 0 0 4px rgba(201,75,80,.18);
    }

    /* Grid */
    .csx-grid{
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 18px;
      align-items: stretch;
    }

    /* Card */
    .csx-card{
      width:100%;
      height: var(--csx-card-h);
      position:relative;
      display:flex;
      flex-direction:column;
      border: 1px solid rgba(2,6,23,.08);
      border-radius: 16px;
      background: #fff;
      box-shadow: var(--csx-shadow);
      overflow:hidden;
      transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
      will-change: transform;
    }
    .csx-card:hover{
      transform: translateY(-2px);
      box-shadow: 0 16px 34px rgba(2,6,23,.12);
      border-color: rgba(158,54,58,.22);
    }

    .csx-media{
      width:100%;
      height: var(--csx-media-h);
      flex: 0 0 auto;
      background: var(--csx-brand);
      position:relative;
      overflow:hidden;
      user-select:none;
    }
    .csx-media .csx-fallback{
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
      line-height: 1.15;
    }
    .csx-media img{
      position:absolute;
      inset:0;
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
      z-index: 1;
    }

    .csx-body{
      padding: 16px 16px 14px;
      display:flex;
      flex-direction:column;
      flex: 1 1 auto;
      min-height: 0;
    }
    .csx-h{
      font-size: 20px;
      line-height: 1.25;
      font-weight: 950;
      margin: 0 0 10px 0;
      color: var(--csx-ink);

      display:-webkit-box;
      -webkit-line-clamp:2;
      -webkit-box-orient:vertical;
      overflow:hidden;

      overflow-wrap:anywhere;
      word-break:break-word;
    }
    .csx-p{
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

    .csx-link{
      position:absolute;
      inset:0;
      z-index:2;
      border-radius: 16px;
    }

    /* State / empty */
    .csx-state{
      background: var(--csx-card);
      border: 1px solid var(--csx-line);
      border-radius: 16px;
      box-shadow: var(--csx-shadow);
      padding: 18px;
      color: var(--csx-muted);
      text-align:center;
    }

    /* Skeleton */
    .csx-skeleton{
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 18px;
    }
    .csx-sk{
      border-radius: 16px;
      border: 1px solid var(--csx-line);
      background: #fff;
      overflow:hidden;
      position:relative;
      box-shadow: 0 10px 24px rgba(2,6,23,.08);
      height: var(--csx-card-h);
    }
    .csx-sk:before{
      content:'';
      position:absolute; inset:0;
      transform: translateX(-60%);
      background: linear-gradient(90deg, transparent, rgba(148,163,184,.22), transparent);
      animation: csxSkMove 1.15s ease-in-out infinite;
    }
    @keyframes csxSkMove{ to{ transform: translateX(60%);} }

    /* Pagination */
    .csx-pagination{
      display:flex;
      justify-content:center;
      margin-top: 18px;
    }
    .csx-pagination .csx-pager{
      display:flex;
      gap: 8px;
      flex-wrap: wrap;
      align-items:center;
      justify-content:center;
      padding: 10px;
    }
    .csx-pagebtn{
      border:1px solid var(--csx-line);
      background: var(--csx-card);
      color: var(--csx-ink);
      border-radius: 12px;
      padding: 9px 12px;
      font-size: 13px;
      font-weight: 950;
      box-shadow: 0 8px 18px rgba(2,6,23,.06);
      cursor:pointer;
      user-select:none;
    }
    .csx-pagebtn:hover{ background: rgba(2,6,23,.03); }
    .csx-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
    .csx-pagebtn.active{
      background: rgba(201,75,80,.12);
      border-color: rgba(201,75,80,.35);
      color: var(--csx-brand);
    }

    @media (max-width: 640px){
      /* ✅ allow wrap on small screens */
      .csx-head{ flex-wrap: wrap; align-items: flex-end; }
      .csx-tools{ flex-wrap: wrap; }

      .csx-title{ font-size: 24px; white-space: normal; }
      .csx-search{ min-width: 220px; flex: 1 1 240px; }
      .csx-select{ min-width: 220px; flex: 1 1 240px; }
      .csx-select.csx-level{ min-width: 220px; flex: 1 1 240px; }
      .csx-wrap{ --csx-media-h: 210px; }
      .csx-media .csx-fallback{ font-size: 22px; }
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
    class="csx-wrap"
    data-api="{{ url('/api/public/courses') }}"
    data-view-base="{{ url('/courses/view') }}"
    data-dept-api="{{ url('/api/public/departments') }}"
  >
    <div class="csx-head">
      <div>
        <h1 class="csx-title"><i class="fa-solid fa-graduation-cap"></i>Courses</h1>
        <div class="csx-sub" id="csxSub">Browse all published programs & courses.</div>
      </div>

      <div class="csx-tools">
        <div class="csx-search">
          <i class="fa fa-magnifying-glass"></i>
          <input id="csxSearch" type="search" placeholder="Search courses (title/summary/body/career scope)…">
        </div>

        {{-- ✅ NEW: Level filter (program_level) --}}
        <div class="csx-select csx-level" title="Filter by level">
          <i class="fa-solid fa-layer-group csx-select__icon"></i>
          <select id="csxLevel" aria-label="Filter by level">
            <option value="">All Levels</option>
          </select>
          <i class="fa-solid fa-chevron-down csx-select__caret"></i>
        </div>

        <div class="csx-select" title="Filter by department">
          <i class="fa-solid fa-building-columns csx-select__icon"></i>
          <select id="csxDept" aria-label="Filter by department">
            <option value="">All Departments</option>
          </select>
          <i class="fa-solid fa-chevron-down csx-select__caret"></i>
        </div>

        {{-- ✅ Count chip removed --}}
      </div>
    </div>

    <div id="csxGrid" class="csx-grid" style="display:none;"></div>

    <div id="csxSkeleton" class="csx-skeleton"></div>
    <div id="csxState" class="csx-state" style="display:none;"></div>

    <div class="csx-pagination">
      <div id="csxPager" class="csx-pager" style="display:none;"></div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  (() => {
    if (window.__PUBLIC_COURSES_ALL__) return;
    window.__PUBLIC_COURSES_ALL__ = true;

    const root = document.querySelector('.csx-wrap');
    if (!root) return;

    const API = root.getAttribute('data-api') || '/api/public/courses';
    const VIEW_BASE = root.getAttribute('data-view-base') || '/courses/view';
    const DEPT_API = root.getAttribute('data-dept-api') || '/api/public/departments';

    const $ = (id) => document.getElementById(id);

    const els = {
      grid: $('csxGrid'),
      skel: $('csxSkeleton'),
      state: $('csxState'),
      pager: $('csxPager'),
      search: $('csxSearch'),
      dept: $('csxDept'),
      level: $('csxLevel'), // ✅ NEW
      sub: $('csxSub'),
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
      level: '', // ✅ NEW: normalized program_level (e.g. "ug", "pg")
    };

    let activeController = null;

    // cache
    let allCourses = null;
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

    function normalizeUrl(url){
      const u = (url || '').toString().trim();
      if (!u) return '';
      if (/^(data:|blob:|https?:\/\/)/i.test(u)) return u;
      if (u.startsWith('/')) return window.location.origin + u;
      return window.location.origin + '/' + u;
    }

    // ✅ handle image load/error without inline JS
    function bindCardImages(rootEl){
      rootEl.querySelectorAll('img.csx-img').forEach(img => {
        const media = img.closest('.csx-media');
        const fallback = media ? media.querySelector('.csx-fallback') : null;

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

    // ✅ Extract items from multiple possible API shapes
    function extractItems(json){
      if (!json) return [];
      if (Array.isArray(json.data)) return json.data;
      if (json.data && Array.isArray(json.data.data)) return json.data.data;
      if (Array.isArray(json.items)) return json.items;
      if (Array.isArray(json)) return json;
      return [];
    }

    // ✅ NEW: program_level helpers
    function normalizeLevel(v){
      return (v ?? '').toString().trim().toLowerCase();
    }
    function levelLabel(v){
      const s = (v ?? '').toString().trim();
      if (!s) return '';
      const low = s.toLowerCase();
      if (low === 'ug') return 'UG';
      if (low === 'pg') return 'PG';
      return s
        .replace(/[_-]+/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
        .replace(/\b\w/g, c => c.toUpperCase());
    }

    function updateSubline(){
      const base = 'Browse all published programs & courses.';
      if (!els.sub) return;

      const parts = [];
      if (state.deptName) parts.push(`Courses for ${state.deptName}`);
      if (state.level) parts.push(`Level: ${levelLabel(state.level)}`);

      els.sub.textContent = parts.length ? parts.join(' · ') : base;
    }

    function hydrateLevelOptions(){
      const sel = els.level;
      if (!sel) return;

      const levels = new Map(); // norm -> label
      (Array.isArray(allCourses) ? allCourses : []).forEach(it => {
        const norm = normalizeLevel(it?.program_level || '');
        if (!norm) return;
        levels.set(norm, levelLabel(norm));
      });

      const list = Array.from(levels.entries())
        .map(([value, label]) => ({ value, label }))
        .sort((a,b) => a.label.localeCompare(b.label));

      sel.innerHTML =
        `<option value="">All Levels</option>` +
        list.map(x => `<option value="${escAttr(x.value)}">${esc(x.label)}</option>`).join('');

      // keep current selection if still valid
      if (state.level && levels.has(state.level)){
        sel.value = state.level;
      } else {
        state.level = '';
        sel.value = '';
      }
    }

    function cardHtml(item){
      const titleRaw = item?.title || 'Course';
      const title = esc(titleRaw);

      const summary = stripHtml(item?.summary || '');
      const bodyTxt = stripHtml(item?.body || '');
      const career  = stripHtml(item?.career_scope || '');
      const fullText = summary || career || bodyTxt;

      const MAX_CHARS = 90;
      let excerptText = fullText;
      if (fullText.length > MAX_CHARS){
        excerptText = fullText
          .slice(0, MAX_CHARS)
          .trim()
          .replace(/[,\.;:\-\s]+$/g, '');
        excerptText += '......';
      }

      const excerpt = esc(excerptText || '');

      const uuid = item?.uuid ? String(item.uuid) : '';
      const href = uuid ? (VIEW_BASE + '/' + encodeURIComponent(uuid)) : '#';

      const cover = item?.cover_image_url || item?.cover_image || item?.image_url || '';
      const coverNorm = cover ? normalizeUrl(String(cover).trim()) : '';

      return `
        <div class="csx-card">
          <div class="csx-media">
            <div class="csx-fallback">${esc('Course')}</div>
            ${coverNorm ? `
              <img class="csx-img"
                   src="${escAttr(coverNorm)}"
                   alt="${escAttr(titleRaw)}"
                   loading="lazy" />
            ` : ``}
          </div>

          <div class="csx-body">
            <div class="csx-h">${title}</div>
            <p class="csx-p">${excerpt}</p>
          </div>

          ${uuid
            ? `<a class="csx-link" href="${href}" aria-label="Open ${escAttr(titleRaw)}"></a>`
            : `<div class="csx-link" title="Missing UUID"></div>`
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
      sk.innerHTML = Array.from({length: 6}).map(() => `<div class="csx-sk"></div>`).join('');
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
        updateSubline();
        return;
      }

      const meta = deptByUuid.get(uuid);
      if (!meta) return;

      sel.value = uuid;
      state.deptUuid = uuid;
      state.deptId = meta.id ?? null;
      state.deptName = meta.title ?? '';
      updateSubline();
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

    async function ensureCoursesLoaded(force=false){
      if (allCourses && !force) return;

      showSkeleton();

      try{
        // request a bigger page so frontend filtering always works
        const u = new URL(API, window.location.origin);
        u.searchParams.set('page', '1');
        u.searchParams.set('per_page', '200');
        u.searchParams.set('sort', 'created_at');
        u.searchParams.set('direction', 'desc');

        const js = await fetchJson(u.toString());
        allCourses = extractItems(js);

      } finally {
        hideSkeleton();
      }
    }

    function applyFilterAndSearch(){
      const q = (state.q || '').toString().trim().toLowerCase();
      let items = Array.isArray(allCourses) ? allCourses.slice() : [];

      // ✅ Dept filter (frontend) by department_id / department_uuid
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

      // ✅ NEW: Level filter by program_level (e.g. "ug", "pg")
      if (state.level){
        const lvl = String(state.level);
        items = items.filter(it => normalizeLevel(it?.program_level || '') === lvl);
      }

      // search on title + summary + career_scope + body
      if (q){
        items = items.filter(it => {
          const t = String(it?.title || '').toLowerCase();
          const s = stripHtml(it?.summary || '').toLowerCase();
          const c = stripHtml(it?.career_scope || '').toLowerCase();
          const b = stripHtml(it?.body || '').toLowerCase();
          return (t.includes(q) || s.includes(q) || c.includes(q) || b.includes(q));
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

        const deptLine = state.deptName
          ? `<div style="margin-top:6px;font-size:12.5px;opacity:.95;">Department: <b>${esc(state.deptName)}</b></div>`
          : '';
        const levelLine = state.level
          ? `<div style="margin-top:4px;font-size:12.5px;opacity:.95;">Level: <b>${esc(levelLabel(state.level))}</b></div>`
          : '';

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
  No courses found.
  ${deptLine}
          ${levelLine}
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
        const cls = active ? 'csx-pagebtn active' : 'csx-pagebtn';
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

      // ✅ deep-link ?d-{uuid}
      const deepDeptUuid = extractDeptUuidFromUrl();
      if (deepDeptUuid && deptByUuid.has(deepDeptUuid)){
        setDeptSelection(deepDeptUuid);
      } else {
        setDeptSelection('');
      }

      // load once, then filter client-side
      await ensureCoursesLoaded(false);

      // ✅ NEW: build level filter options from loaded courses
      hydrateLevelOptions();
      updateSubline();

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

      // ✅ NEW: level change
      els.level && els.level.addEventListener('change', () => {
        const v = normalizeLevel(els.level.value || '');
        state.level = v;
        state.page = 1;
        updateSubline();
        repaint();
        window.scrollTo({ top: 0, behavior: 'smooth' });
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
        const b = e.target.closest('button.csx-pagebtn[data-page]');
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
