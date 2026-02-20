{{-- resources/views/landing/placement-officers.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name','College Portal') }} — Placement Officers</title>

  {{-- Bootstrap + Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

  {{-- Common UI tokens --}}
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    /* =========================================================
      ✅ Placement Officers (Scoped / No :root / No global body rules)
      - UI structure matches Announcements reference (theme consistency)
      - Dept dropdown added (nicer UI)
      - Dept filtering (frontend filter by department_id / department_uuid)
      - Deep-link ?d-{uuid} auto-selects dept and filters
      ✅ CHANGE:
      - Removed count chip from header + related code
      - Header kept in ONE ROW on desktop
    ========================================================= */

    .pox-wrap{
      /* scoped tokens */
      --pox-brand: var(--primary-color, #9E363A);
      --pox-ink: #0f172a;
      --pox-muted: #64748b;
      --pox-bg: var(--page-bg, #ffffff);
      --pox-card: var(--surface, #ffffff);
      --pox-line: var(--line-soft, rgba(15,23,42,.10));
      --pox-shadow: 0 10px 24px rgba(2,6,23,.08);

      /* card sizing */
      --pox-card-h: 426.4px;
      --pox-media-h: 240px;

      max-width: 1320px;
      margin: 18px auto 54px;
      padding: 0 12px;
      background: transparent;
      position: relative;
      overflow: visible;
    }

    /* Header */
    .pox-head{
      background: var(--pox-card);
      border: 1px solid var(--pox-line);
      border-radius: 16px;
      box-shadow: var(--pox-shadow);
      padding: 14px 16px;
      margin-bottom: 16px;

      display:flex;
      gap: 12px;
      align-items: center;
      justify-content: space-between;

      /* ✅ keep one row (desktop) */
    }
    .pox-title{
      margin: 0;
      font-weight: 950;
      letter-spacing: .2px;
      color: var(--pox-ink);
      font-size: 28px;
      display:flex;
      align-items:center;
      gap: 10px;
      white-space: nowrap;
    }
    .pox-title i{ color: var(--pox-brand); }
    .pox-sub{
      margin: 6px 0 0;
      color: var(--pox-muted);
      font-size: 14px;
    }

    .pox-tools{
      display:flex;
      gap: 10px;
      align-items:center;

      /* ✅ keep one row (desktop) */
      flex-wrap: nowrap;
    }

    /* Search */
    .pox-search{
      position: relative;
      min-width: 260px;
      max-width: 520px;
      flex: 1 1 320px;
    }
    .pox-search i{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .65;
      color: var(--pox-muted);
      pointer-events:none;
    }
    .pox-search input{
      width:100%;
      height: 42px;
      border-radius: 999px;
      padding: 11px 12px 11px 42px;
      border: 1px solid var(--pox-line);
      background: var(--pox-card);
      color: var(--pox-ink);
      outline: none;
    }
    .pox-search input:focus{
      border-color: rgba(201,75,80,.55);
      box-shadow: 0 0 0 4px rgba(201,75,80,.18);
    }

    /* ✅ Dept dropdown (nicer UI) */
    .pox-select{
      position: relative;
      min-width: 260px;
      max-width: 360px;
      flex: 0 1 320px;
    }
    .pox-select__icon{
      position:absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .70;
      color: var(--pox-muted);
      pointer-events:none;
      font-size: 14px;
    }
    .pox-select__caret{
      position:absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      opacity: .70;
      color: var(--pox-muted);
      pointer-events:none;
      font-size: 12px;
    }
    .pox-select select{
      width: 100%;
      height: 42px;
      border-radius: 999px;
      padding: 10px 38px 10px 42px; /* left icon + right caret */
      border: 1px solid var(--pox-line);
      background: var(--pox-card);
      color: var(--pox-ink);
      outline: none;

      /* remove native arrow */
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
    }
    .pox-select select:focus{
      border-color: rgba(201,75,80,.55);
      box-shadow: 0 0 0 4px rgba(201,75,80,.18);
    }

    /* Grid */
    .pox-grid{
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 18px;
      align-items: stretch;
    }

    /* Card */
    .pox-card{
      width:100%;
      height: var(--pox-card-h);
      position:relative;
      display:flex;
      flex-direction:column;
      border: 1px solid rgba(2,6,23,.08);
      border-radius: 16px;
      background: #fff;
      box-shadow: var(--pox-shadow);
      overflow:hidden;
      transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
      will-change: transform;
    }
    .pox-card:hover{
      transform: translateY(-2px);
      box-shadow: 0 16px 34px rgba(2,6,23,.12);
      border-color: rgba(158,54,58,.22);
    }

    .pox-media{
      width:100%;
      height: var(--pox-media-h);
      flex: 0 0 auto;
      background: var(--pox-brand);
      position:relative;
      overflow:hidden;
      user-select:none;
    }
    .pox-media .pox-fallback{
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
      gap:10px;
    }
    .pox-media .pox-fallback i{ opacity:.9; }
    .pox-media img{
      position:absolute;
      inset:0;
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
      z-index: 1;
    }

    .pox-body{
      padding: 16px 16px 14px;
      display:flex;
      flex-direction:column;
      flex: 1 1 auto;
      min-height: 0;
    }
    .pox-h{
      font-size: 20px;
      line-height: 1.25;
      font-weight: 950;
      margin: 0 0 10px 0;
      color: var(--pox-ink);

      display:-webkit-box;
      -webkit-line-clamp:2;
      -webkit-box-orient:vertical;
      overflow:hidden;

      overflow-wrap:anywhere;
      word-break:break-word;
    }
    .pox-p{
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

    .pox-meta{
      margin-top:auto;
      color:#94a3b8;
      font-size: 13px;
      padding-top: 12px;
      display:flex;
      align-items:center;
      gap: 10px;
      flex-wrap:wrap;
    }
    .pox-meta .it{
      display:flex;
      align-items:center;
      gap: 6px;
    }

    .pox-link{
      position:absolute;
      inset:0;
      z-index:2;
      border-radius: 16px;
    }

    /* State / empty */
    .pox-state{
      background: var(--pox-card);
      border: 1px solid var(--pox-line);
      border-radius: 16px;
      box-shadow: var(--pox-shadow);
      padding: 18px;
      color: var(--pox-muted);
      text-align:center;
    }

    /* Skeleton */
    .pox-skeleton{
      display:grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 18px;
    }
    .pox-sk{
      border-radius: 16px;
      border: 1px solid var(--pox-line);
      background: #fff;
      overflow:hidden;
      position:relative;
      box-shadow: 0 10px 24px rgba(2,6,23,.08);
      height: var(--pox-card-h);
    }
    .pox-sk:before{
      content:'';
      position:absolute; inset:0;
      transform: translateX(-60%);
      background: linear-gradient(90deg, transparent, rgba(148,163,184,.22), transparent);
      animation: poxSkMove 1.15s ease-in-out infinite;
    }
    @keyframes poxSkMove{ to{ transform: translateX(60%);} }

    /* Pagination */
    .pox-pagination{
      display:flex;
      justify-content:center;
      margin-top: 18px;
    }
    .pox-pagination .pox-pager{
      display:flex;
      gap: 8px;
      flex-wrap: wrap;
      align-items:center;
      justify-content:center;
      padding: 10px;
    }
    .pox-pagebtn{
      border:1px solid var(--pox-line);
      background: var(--pox-card);
      color: var(--pox-ink);
      border-radius: 12px;
      padding: 9px 12px;
      font-size: 13px;
      font-weight: 950;
      box-shadow: 0 8px 18px rgba(2,6,23,.06);
      cursor:pointer;
      user-select:none;
    }
    .pox-pagebtn:hover{ background: rgba(2,6,23,.03); }
    .pox-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
    .pox-pagebtn.active{
      background: rgba(201,75,80,.12);
      border-color: rgba(201,75,80,.35);
      color: var(--pox-brand);
    }

    /* ✅ allow wrapping on smaller screens (keeps desktop one-row as requested) */
    @media (max-width: 992px){
      .pox-head{ flex-wrap: wrap; align-items: flex-end; }
      .pox-tools{ flex-wrap: wrap; }
    }

    @media (max-width: 640px){
      .pox-title{ font-size: 24px; }
      .pox-search{ min-width: 220px; flex: 1 1 240px; }
      .pox-select{ min-width: 220px; flex: 1 1 240px; }
      .pox-wrap{ --pox-media-h: 210px; }
      .pox-media .pox-fallback{ font-size: 22px; }
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
    class="pox-wrap"
    data-api-1="{{ url('/api/public/placement-officers') }}"
    data-api-2="{{ url('/api/placement-officers') }}"
    data-dept-api="{{ url('/api/public/departments') }}"
    data-profile-base="{{ url('/user/profile') }}"
  >
    <div class="pox-head">
      <div>
        <h1 class="pox-title"><i class="fa-solid fa-bullhorn"></i>Placement Officers</h1>
        <div class="pox-sub" id="poxSub">Meet our Placement & Training team.</div>
      </div>

      <div class="pox-tools">
        <div class="pox-search">
          <i class="fa fa-magnifying-glass"></i>
          <input id="poxSearch" type="search" placeholder="Search placement officers (name/email/designation)…">
        </div>

        <div class="pox-select" title="Filter by department">
          <i class="fa-solid fa-building-columns pox-select__icon"></i>
          <select id="poxDept" aria-label="Filter by department">
            <option value="">All Departments</option>
          </select>
          <i class="fa-solid fa-chevron-down pox-select__caret"></i>
        </div>
      </div>
    </div>

    <div id="poxGrid" class="pox-grid" style="display:none;"></div>

    <div id="poxSkeleton" class="pox-skeleton"></div>
    <div id="poxState" class="pox-state" style="display:none;"></div>

    <div class="pox-pagination">
      <div id="poxPager" class="pox-pager" style="display:none;"></div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  (() => {
    if (window.__PUBLIC_PLACEMENT_OFFICERS_ALL__) return;
    window.__PUBLIC_PLACEMENT_OFFICERS_ALL__ = true;

    const root = document.querySelector('.pox-wrap');
    if (!root) return;

    const API_1 = root.getAttribute('data-api-1') || '/api/public/placement-officers';
    const API_2 = root.getAttribute('data-api-2') || '/api/placement-officers';
    const DEPT_API = root.getAttribute('data-dept-api') || '/api/public/departments';
    const PROFILE_BASE = root.getAttribute('data-profile-base') || '/user/profile';

    const $ = (id) => document.getElementById(id);

    const els = {
      grid: $('poxGrid'),
      skel: $('poxSkeleton'),
      state: $('poxState'),
      pager: $('poxPager'),
      search: $('poxSearch'),
      dept: $('poxDept'),
      sub: $('poxSub'),
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
    let allOfficers = null;
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

    function looksLikeUuid(v){
      return /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(String(v || '').trim());
    }

    function pick(obj, keys){
      for (const k of keys){
        const v = obj?.[k];
        if (v !== null && v !== undefined && String(v).trim() !== '') return v;
      }
      return '';
    }

    function resolveName(item){
      return String(pick(item, ['name','user_name','full_name']) || 'Placement Officer');
    }
    function resolveEmail(item){
      return String(pick(item, ['email']) || '');
    }
    function resolveDesignation(item){
      return String(pick(item, ['designation','affiliation','role_short_form','role']) || 'Placement Officer');
    }
    function resolveImage(item){
      const img =
        pick(item, ['image_full_url','image_url','photo_url','profile_image_url']) ||
        pick(item, ['image']) || '';
      return normalizeUrl(img);
    }

    function resolveDeptId(item){
      const v = pick(item, ['department_id','dept_id','departmentId','deptId','department']);
      if (v === null || v === undefined || String(v).trim() === '') return '';
      return String(v).trim();
    }
    function resolveDeptUuid(item){
      const v = pick(item, ['department_uuid','dept_uuid','departmentUuid','deptUuid']);
      return String(v || '').trim();
    }

    function resolveProfileIdentifier(item){
      const candidates = [
        pick(item, ['uuid','user_uuid']),
        (item?.id ?? '')
      ].map(v => String(v ?? '').trim()).filter(Boolean);

      const uuid = candidates.find(looksLikeUuid);
      return uuid || candidates[0] || '';
    }

    function buildProfileUrl(identifier){
      return identifier
        ? (PROFILE_BASE.replace(/\/+$/,'') + '/' + encodeURIComponent(identifier))
        : '#';
    }

    // ✅ handle image load/error without inline JS
    function bindCardImages(rootEl){
      rootEl.querySelectorAll('img.pox-img').forEach(img => {
        const media = img.closest('.pox-media');
        const fallback = media ? media.querySelector('.pox-fallback') : null;

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

    function showSkeleton(){
      const sk = els.skel, st = els.state, grid = els.grid, pager = els.pager;
      if (grid) grid.style.display = 'none';
      if (pager) pager.style.display = 'none';
      if (st) st.style.display = 'none';

      if (!sk) return;
      sk.style.display = '';
      sk.innerHTML = Array.from({length: 6}).map(() => `<div class="pox-sk"></div>`).join('');
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

    function toItems(js){
      if (Array.isArray(js?.data)) return js.data;
      if (Array.isArray(js?.items)) return js.items;
      if (Array.isArray(js)) return js;
      if (Array.isArray(js?.data?.items)) return js.data.items;
      return [];
    }

    async function tryFetchList(urls){
      let lastErr = null;
      for (const u of urls){
        try{
          const js = await fetchJson(u);
          return { ok:true, used:u, js, items: toItems(js) };
        }catch(e){
          lastErr = e;
        }
      }
      return { ok:false, used:'', js:{}, items:[], error:lastErr };
    }

    function extractDeptUuidFromUrl(){
      // matches "?d-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" anywhere in URL
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
        if (els.sub) els.sub.textContent = 'Meet our Placement & Training team.';
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
          ? ('Placement Officers for ' + state.deptName)
          : 'Placement Officers (filtered)';
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

    async function ensureOfficersLoaded(force=false){
      if (allOfficers && !force) return;

      showSkeleton();

      try{
        // ask for a bigger page so frontend filtering always works
        const urls = [API_1, API_2].map(base => {
          const u = new URL(base, window.location.origin);
          u.searchParams.set('page', '1');
          u.searchParams.set('per_page', '200');
          u.searchParams.set('status', 'active');
          u.searchParams.set('sort', 'created_at');
          u.searchParams.set('direction', 'desc');
          return u.toString();
        });

        const res = await tryFetchList(urls);
        allOfficers = res.ok ? (res.items || []) : [];
      } finally {
        hideSkeleton();
      }
    }

    function cardHtml(item){
      const nameRaw = resolveName(item);
      const desigRaw = resolveDesignation(item);
      const emailRaw = resolveEmail(item);

      const name = esc(nameRaw);
      const desig = esc(stripHtml(desigRaw || ''));
      const email = esc(emailRaw || '');

      const identifier = resolveProfileIdentifier(item);
      const href = buildProfileUrl(identifier);

      const photo = resolveImage(item);
      const photoNorm = photo ? normalizeUrl(photo) : '';

      return `
        <div class="pox-card">
          <div class="pox-media">
            <div class="pox-fallback"><i class="fa-solid fa-user-tie"></i>Placement Officer</div>
            ${photoNorm ? `
              <img class="pox-img"
                   src="${escAttr(photoNorm)}"
                   alt="${escAttr(nameRaw)}"
                   loading="lazy" />
            ` : ``}
          </div>

          <div class="pox-body">
            <div class="pox-h">${name}</div>
            <p class="pox-p">${desig || 'Placement Officer'}</p>

            <div class="pox-meta">
              <div class="it" title="Email">
                <i class="fa-regular fa-envelope"></i>
                <span>${email || '—'}</span>
              </div>
              <div class="it" title="Department">
                <i class="fa-regular fa-building"></i>
                <span>${esc(state.deptName || 'All Departments')}</span>
              </div>
            </div>
          </div>

          ${identifier
            ? `<a class="pox-link" href="${escAttr(href)}" aria-label="Open ${escAttr(nameRaw)} profile"></a>`
            : `<div class="pox-link" title="Missing profile identifier"></div>`
          }
        </div>
      `;
    }

    function applyFilterAndSearch(){
      const q = (state.q || '').toString().trim().toLowerCase();
      let items = Array.isArray(allOfficers) ? allOfficers.slice() : [];

      // ✅ Dept filter: when dept selected -> show ONLY those matched to dept
      if (state.deptUuid && (state.deptId !== null && state.deptId !== undefined && String(state.deptId) !== '')){
        const deptIdStr = String(state.deptId);
        const deptUuidStr = String(state.deptUuid);

        items = items.filter(it => {
          const did = resolveDeptId(it);
          const duu = resolveDeptUuid(it);
          return (did && did === deptIdStr) || (duu && duu === deptUuidStr);
        });
      } else if (state.deptUuid) {
        // if somehow deptId missing, try uuid-only
        const deptUuidStr = String(state.deptUuid);
        items = items.filter(it => String(resolveDeptUuid(it) || '') === deptUuidStr);
      }

      // search on name + designation + email
      if (q){
        items = items.filter(it => {
          const n = resolveName(it).toLowerCase();
          const d = stripHtml(resolveDesignation(it)).toLowerCase();
          const e = resolveEmail(it).toLowerCase();
          return (n.includes(q) || d.includes(q) || e.includes(q));
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
          No placement officers found.
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
        const cls = active ? 'pox-pagebtn active' : 'pox-pagebtn';
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

      // ✅ deep-link: ?d-{uuid}
      const deepDeptUuid = extractDeptUuidFromUrl();
      if (deepDeptUuid && deptByUuid.has(deepDeptUuid)){
        setDeptSelection(deepDeptUuid);
      } else {
        setDeptSelection('');
      }

      // load once, then filter client-side
      await ensureOfficersLoaded(false);
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
        const b = e.target.closest('button.pox-pagebtn[data-page]');
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
