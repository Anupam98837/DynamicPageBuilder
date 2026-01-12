{{-- resources/views/landing/faculty-members.blade.php --}}

<style>
  /* =========================================================
    ✅ Faculty Members (Scoped / No :root / No global body rules)
    - Matches Announcements UI DNA
    - Dept dropdown + ?d-{uuid} deep-link
    - Loads dept-specific data from API (dept_uuid param)
  ========================================================= */

  .fmx-wrap{
    --fmx-brand: var(--primary-color, #9E363A);
    --fmx-ink: #0f172a;
    --fmx-muted: #64748b;
    --fmx-card: var(--surface, #ffffff);
    --fmx-line: var(--line-soft, rgba(15,23,42,.10));
    --fmx-shadow: 0 10px 24px rgba(2,6,23,.08);

    --fmx-card-h: 426.4px;

    max-width: 1320px;
    margin: 18px auto 54px;
    padding: 0 12px;
    background: transparent;
    position: relative;
    overflow: visible;
  }

  .fmx-head{
    background: var(--fmx-card);
    border: 1px solid var(--fmx-line);
    border-radius: 16px;
    box-shadow: var(--fmx-shadow);
    padding: 14px 16px;
    margin-bottom: 16px;

    display:flex;
    gap: 12px;
    align-items: center;
    justify-content: space-between;

    /* ✅ one-row head (desktop) */
    flex-wrap: nowrap;
  }
  .fmx-title{
    margin: 0;
    font-weight: 950;
    letter-spacing: .2px;
    color: var(--fmx-ink);
    font-size: 28px;
    display:flex;
    align-items:center;
    gap: 10px;
    white-space: nowrap;
  }
  .fmx-title i{ color: var(--fmx-brand); }
  .fmx-sub{
    margin: 6px 0 0;
    color: var(--fmx-muted);
    font-size: 14px;
  }

  .fmx-tools{
    display:flex;
    gap: 10px;
    align-items:center;

    /* ✅ keep tools in one row */
    flex-wrap: nowrap;
  }

  .fmx-search{
    position: relative;
    min-width: 260px;
    max-width: 520px;
    flex: 1 1 320px;
  }
  .fmx-search i{
    position:absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    opacity: .65;
    color: var(--fmx-muted);
    pointer-events:none;
  }
  .fmx-search input{
    width:100%;
    height: 42px;
    border-radius: 999px;
    padding: 11px 12px 11px 42px;
    border: 1px solid var(--fmx-line);
    background: var(--fmx-card);
    color: var(--fmx-ink);
    outline: none;
  }
  .fmx-search input:focus{
    border-color: rgba(201,75,80,.55);
    box-shadow: 0 0 0 4px rgba(201,75,80,.18);
  }

  .fmx-select{
    position: relative;
    min-width: 260px;
    max-width: 360px;
    flex: 0 1 320px;
  }
  .fmx-select__icon{
    position:absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    opacity: .70;
    color: var(--fmx-muted);
    pointer-events:none;
    font-size: 14px;
  }
  .fmx-select__caret{
    position:absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    opacity: .70;
    color: var(--fmx-muted);
    pointer-events:none;
    font-size: 12px;
  }
  .fmx-select select{
    width: 100%;
    height: 42px;
    border-radius: 999px;
    padding: 10px 38px 10px 42px;
    border: 1px solid var(--fmx-line);
    background: var(--fmx-card);
    color: var(--fmx-ink);
    outline: none;

    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
  }
  .fmx-select select:focus{
    border-color: rgba(201,75,80,.55);
    box-shadow: 0 0 0 4px rgba(201,75,80,.18);
  }

  .fmx-grid{
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 18px;
    align-items: stretch;
  }

  .fmx-card{
    width:100%;
    min-height: var(--fmx-card-h);
    position:relative;
    display:flex;
    flex-direction:column;
    border: 1px solid rgba(2,6,23,.08);
    border-radius: 16px;
    background: #fff;
    box-shadow: var(--fmx-shadow);
    overflow:hidden;
    transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
    will-change: transform;
    cursor: pointer;
    outline: none;
  }
  .fmx-card:hover{
    transform: translateY(-2px);
    box-shadow: 0 16px 34px rgba(2,6,23,.12);
    border-color: rgba(158,54,58,.22);
  }
  .fmx-card:focus-visible{
    box-shadow: 0 0 0 4px rgba(201,75,80,.18), 0 16px 34px rgba(2,6,23,.12);
    border-color: rgba(201,75,80,.55);
  }

  .fmx-body{
    padding: 16px 16px 14px;
    display:flex;
    flex-direction:column;
    flex: 1 1 auto;
    min-height: 0;
  }

  .fmx-top{ display:flex; gap: 12px; align-items:flex-start; }

  .fmx-avatar{
    width: 64px;
    height: 64px;
    border-radius: 999px;
    flex: 0 0 64px;
    overflow:hidden;
    border: 3px solid #fff;
    box-shadow: 0 10px 22px rgba(2,6,23,.12);
    background: radial-gradient(140px 140px at 30% 20%,
      rgba(201,75,80,.16),
      transparent 60%),
      linear-gradient(180deg, rgba(0,0,0,.03), rgba(0,0,0,.06));
    position: relative;
    display:grid;
    place-items:center;
  }
  .fmx-avatar img{ width:100%; height:100%; object-fit: cover; display:block; }
  .fmx-initial{
    position:absolute; inset:0;
    display:grid; place-items:center;
    font-weight: 950;
    color: rgba(158,54,58,.95);
    font-size: 18px;
    letter-spacing:.5px;
  }
  .fmx-avatar.has-img .fmx-initial{ opacity:0; pointer-events:none; }

  .fmx-name{
    margin: 0;
    font-weight: 950;
    color: var(--fmx-ink);
    font-size: 18px;
    line-height: 1.25;
    text-transform: uppercase;

    display:-webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow:hidden;
    overflow-wrap:anywhere;
    word-break:break-word;
  }
  .fmx-desig{
    margin-top: 6px;
    color: #334155;
    font-size: 14px;
    font-weight: 800;

    display:-webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow:hidden;
  }

  .fmx-meta{ margin-top: 12px; display:grid; gap: 6px; }
  .fmx-line{ font-size: 14px; color: #334155; line-height: 1.55; overflow-wrap:anywhere; }
  .fmx-line b{ font-weight: 950; color: var(--fmx-ink); }

  .fmx-links{
    margin-top: 12px;
    display:flex;
    flex-direction:column;
    gap: 6px;
    font-size: 14px;
  }
  .fmx-links a{
    color: #1d4ed8;
    text-decoration: none;
    font-weight: 900;
    word-break: break-word;
  }
  .fmx-links a:hover{ text-decoration: underline; }

  .fmx-social{
    margin-top: auto;
    padding-top: 14px;
    display:flex;
    gap: 10px;
    flex-wrap: wrap;
  }
  .fmx-social a{
    width: 42px;
    height: 42px;
    border-radius: 999px;
    display:grid;
    place-items:center;
    background: var(--fmx-brand);
    color:#fff;
    border: 1px solid rgba(255,255,255,.18);
    box-shadow: 0 12px 22px rgba(143,47,47,.18);
    transition: transform .14s ease, filter .14s ease;
    text-decoration:none;
  }
  .fmx-social a:hover{ transform: translateY(-1px); filter: brightness(1.06); }
  .fmx-social a i{ color:#fff; font-size: 16px; line-height: 1; }

  .fmx-state{
    background: var(--fmx-card);
    border: 1px solid var(--fmx-line);
    border-radius: 16px;
    box-shadow: var(--fmx-shadow);
    padding: 18px;
    color: var(--fmx-muted);
    text-align:center;
  }

  .fmx-skeleton{
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 18px;
  }
  .fmx-sk{
    border-radius: 16px;
    border: 1px solid var(--fmx-line);
    background: #fff;
    overflow:hidden;
    position:relative;
    box-shadow: 0 10px 24px rgba(2,6,23,.08);
    height: var(--fmx-card-h);
  }
  .fmx-sk:before{
    content:'';
    position:absolute; inset:0;
    transform: translateX(-60%);
    background: linear-gradient(90deg, transparent, rgba(148,163,184,.22), transparent);
    animation: fmxSkMove 1.15s ease-in-out infinite;
  }
  @keyframes fmxSkMove{ to{ transform: translateX(60%);} }

  .fmx-pagination{
    display:flex;
    justify-content:center;
    margin-top: 18px;
  }
  .fmx-pagination .fmx-pager{
    display:flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items:center;
    justify-content:center;
    padding: 10px;
  }
  .fmx-pagebtn{
    border:1px solid var(--fmx-line);
    background: var(--fmx-card);
    color: var(--fmx-ink);
    border-radius: 12px;
    padding: 9px 12px;
    font-size: 13px;
    font-weight: 950;
    box-shadow: 0 8px 18px rgba(2,6,23,.06);
    cursor:pointer;
    user-select:none;
  }
  .fmx-pagebtn:hover{ background: rgba(2,6,23,.03); }
  .fmx-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
  .fmx-pagebtn.active{
    background: rgba(201,75,80,.12);
    border-color: rgba(201,75,80,.35);
    color: var(--fmx-brand);
  }

  @media (max-width: 640px){
    /* ✅ allow wrap on small screens so it doesn't overflow */
    .fmx-head{ flex-wrap: wrap; align-items: flex-end; }
    .fmx-tools{ flex-wrap: wrap; }

    .fmx-title{ font-size: 24px; white-space: normal; }
    .fmx-search{ min-width: 220px; flex: 1 1 240px; }
    .fmx-select{ min-width: 220px; flex: 1 1 240px; }
  }

  .dynamic-navbar .navbar-nav .dropdown-menu{
    position: absolute !important;
    inset: auto !important;
  }
  .dynamic-navbar .dropdown-menu.is-portaled{
    position: fixed !important;
  }
</style>

<div
  class="fmx-wrap"
  data-api="{{ url('/api/public/faculty') }}"
  data-profile-base="{{ url('/user/profile') }}/"
  data-dept-api="{{ url('/api/public/departments') }}"
>
  <div class="fmx-head">
    <div>
      <h1 class="fmx-title"><i class="fa-solid fa-users"></i>Faculty Members</h1>
      <div class="fmx-sub" id="fmxSub">Select a department to view its faculty members.</div>
    </div>

    <div class="fmx-tools">
      <div class="fmx-search">
        <i class="fa fa-magnifying-glass"></i>
        <input id="fmxSearch" type="search" placeholder="Select a department to search…" disabled>
      </div>

      <div class="fmx-select" title="Filter by department">
        <i class="fa-solid fa-building-columns fmx-select__icon"></i>
        <select id="fmxDept" aria-label="Filter by department">
          <option value="">Select Department</option>
        </select>
        <i class="fa-solid fa-chevron-down fmx-select__caret"></i>
      </div>
      {{-- ✅ Count chip removed --}}
    </div>
  </div>

  <div id="fmxGrid" class="fmx-grid" style="display:none;"></div>
  <div id="fmxSkeleton" class="fmx-skeleton" style="display:none;"></div>
  <div id="fmxState" class="fmx-state"></div>

  <div class="fmx-pagination">
    <div id="fmxPager" class="fmx-pager" style="display:none;"></div>
  </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

<script>
(() => {
  if (window.__PUBLIC_FACULTY_MEMBERS_DEPT__) return;
  window.__PUBLIC_FACULTY_MEMBERS_DEPT__ = true;

  const root = document.querySelector('.fmx-wrap');
  if (!root) return;

  const API = root.getAttribute('data-api') || '/api/public/faculty';
  const PROFILE_BASE = root.getAttribute('data-profile-base') || (window.location.origin + '/user/profile/');
  const DEPT_API = root.getAttribute('data-dept-api') || '/api/public/departments';

  const $ = (id) => document.getElementById(id);

  const els = {
    grid: $('fmxGrid'),
    skel: $('fmxSkeleton'),
    state: $('fmxState'),
    pager: $('fmxPager'),
    search: $('fmxSearch'),
    dept: $('fmxDept'),
    sub: $('fmxSub'),
  };

  const state = {
    page: 1,
    perPage: 9,
    lastPage: 1,
    q: '',
    deptUuid: '',
    deptName: '',
  };

  let activeController = null;
  let deptByUuid = new Map();

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }
  function escAttr(str){
    return (str ?? '').toString().replace(/"/g, '&quot;');
  }
  function decodeMaybeJson(v){
    if (v == null) return null;
    if (Array.isArray(v) || typeof v === 'object') return v;
    try { return JSON.parse(String(v)); } catch(e){ return null; }
  }
  function pick(obj, keys){
    for (const k of keys){
      const v = obj?.[k];
      if (v !== null && v !== undefined && String(v).trim() !== '') return v;
    }
    return '';
  }
  function normalizeUrl(url){
    const u = (url || '').toString().trim();
    if (!u) return '';
    if (/^(data:|blob:|https?:\/\/)/i.test(u)) return u;
    if (u.startsWith('/')) return window.location.origin + u;
    return window.location.origin + '/' + u;
  }
  function initials(name){
    const n = (name || '').trim();
    if (!n) return 'FM';
    const parts = n.split(/\s+/).filter(Boolean).slice(0,2);
    return parts.map(p => p[0].toUpperCase()).join('');
  }
  function getProfileUrl(userUuid){
    if (!userUuid) return '#';
    return PROFILE_BASE + encodeURIComponent(userUuid);
  }
  function formatQualification(q){
    const arr = Array.isArray(q) ? q : (decodeMaybeJson(q) || null);
    if (!arr) return '';
    if (arr.every(x => typeof x === 'string')) return arr.join(', ');
    const bits = arr.map(x => x?.title || x?.degree || x?.name).filter(Boolean);
    return bits.length ? bits.join(', ') : '';
  }
  function metaLine(label, value){
    const v = (value || '').toString().trim();
    if (!v) return '';
    return `<div class="fmx-line"><b>${esc(label)}:</b> <span>${esc(v)}</span></div>`;
  }

  function iconForPlatform(platform){
    const p = (platform || '').toLowerCase().trim();
    if (p.includes('linkedin')) return 'fa-brands fa-linkedin-in';
    if (p.includes('google') || p.includes('scholar')) return 'fa-solid fa-graduation-cap';
    if (p.includes('researchgate')) return 'fa-brands fa-researchgate';
    if (p === 'facebook' || p.includes('fb')) return 'fa-brands fa-facebook-f';
    if (p.includes('instagram') || p.includes('insta')) return 'fa-brands fa-instagram';
    if (p === 'x' || p.includes('twitter')) return 'fa-brands fa-x-twitter';
    if (p.includes('github')) return 'fa-brands fa-github';
    if (p.includes('youtube')) return 'fa-brands fa-youtube';
    return 'fa-solid fa-link';
  }
  function normalizeFaIcon(icon){
    const i = (icon || '').trim();
    if (!i) return '';
    if (i.startsWith('fa-') && !i.includes('fa-solid') && !i.includes('fa-brands') && !i.includes('fa-regular')) {
      return 'fa-brands ' + i;
    }
    return i;
  }
  function buildSocialFromItem(it){
    const arr = Array.isArray(it?.socials) ? it.socials : [];
    const html = arr.map(s => {
      const url = (s?.url || '').toString().trim();
      if (!url) return '';
      const icon = normalizeFaIcon(s?.icon) || iconForPlatform(s?.platform);
      const title = (s?.platform || 'Link').toString();
      return `
        <a href="${escAttr(normalizeUrl(url))}" target="_blank" rel="noopener"
           title="${escAttr(title)}" data-stop-card="1">
          <i class="${escAttr(icon)}"></i>
        </a>
      `;
    }).join('');
    return html ? `<div class="fmx-social">${html}</div>` : '';
  }

  function bindAvatarImages(rootEl){
    rootEl.querySelectorAll('img.fmx-img').forEach(img => {
      const avatar = img.closest('.fmx-avatar');
      if (!avatar) return;

      if (img.complete && img.naturalWidth > 0) {
        avatar.classList.add('has-img');
        return;
      }

      img.addEventListener('load', () => avatar.classList.add('has-img'), { once:true });
      img.addEventListener('error', () => { img.remove(); avatar.classList.remove('has-img'); }, { once:true });
    });
  }

  function showSelectDeptState(){
    if (els.grid) els.grid.style.display = 'none';
    if (els.pager) els.pager.style.display = 'none';

    if (els.state){
      els.state.style.display = '';
      els.state.innerHTML = `
        <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
          <i class="fa-solid fa-building-columns"></i>
        </div>
        Please select a department to view faculty members.
      `;
    }
  }

  function showSkeleton(){
    if (els.grid) els.grid.style.display = 'none';
    if (els.pager) els.pager.style.display = 'none';
    if (els.state) els.state.style.display = 'none';

    if (!els.skel) return;
    els.skel.style.display = '';
    els.skel.innerHTML = Array.from({length: 6}).map(() => `<div class="fmx-sk"></div>`).join('');
  }

  function hideSkeleton(){
    if (!els.skel) return;
    els.skel.style.display = 'none';
    els.skel.innerHTML = '';
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
    const hay = (window.location.search || '') + ' ' + (window.location.href || '');
    const m = hay.match(/d-([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/i);
    return m ? m[1] : '';
  }

  async function loadDepartments(){
    const sel = els.dept;
    if (!sel) return;

    sel.innerHTML = `
      <option value="">Select Department</option>
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

      sel.innerHTML = `<option value="">Select Department</option>` + depts
        .map(d => `<option value="${escAttr(d.uuid)}">${esc(d.title)}</option>`)
        .join('');

      sel.value = '';
    } catch (e){
      console.warn('Departments load failed:', e);
      sel.innerHTML = `<option value="">Select Department</option>`;
      sel.value = '';
    }
  }

  function buildListUrl(){
    const u = new URL(API, window.location.origin);
    u.searchParams.set('page', String(state.page));
    u.searchParams.set('per_page', String(state.perPage));
    u.searchParams.set('status', 'active');
    u.searchParams.set('sort', 'created_at');
    u.searchParams.set('direction', 'desc');

    // ✅ pass dept_uuid to backend (no client-side guessing)
    if (state.deptUuid) u.searchParams.set('dept_uuid', String(state.deptUuid));
    else u.searchParams.delete('dept_uuid');

    if (state.q.trim()) u.searchParams.set('q', state.q.trim());
    return u.toString();
  }

  function cardHtml(it){
    const userUuid = pick(it, ['user_uuid','uuid']);
    const name = pick(it, ['name','user_name']) || 'Faculty';

    const desig =
      pick(it, ['designation']) ||
      (decodeMaybeJson(it?.metadata)?.designation || '') ||
      (decodeMaybeJson(it?.metadata)?.role_title || '') ||
      'Faculty Member';

    const qualification = formatQualification(it?.qualification);
    const specification = (pick(it, ['specification']) || '').toString().trim();
    const experience    = (pick(it, ['experience']) || '').toString().trim();
    const interest      = (pick(it, ['interest']) || '').toString().trim();
    const administration= (pick(it, ['administration']) || '').toString().trim();
    const research      = (pick(it, ['research_project']) || '').toString().trim();

    const meta = decodeMaybeJson(it?.metadata) || {};
    const email = (pick(it, ['email']) || meta.email || '').toString().trim();
    const website = (pick(it, ['website']) || meta.website || '').toString().trim();

    const imgRaw = pick(it, ['image_full_url','image']);
    const img = normalizeUrl(imgRaw);

    const href = getProfileUrl(userUuid);
    const ini = initials(name);

    return `
      <article class="fmx-card" tabindex="0" role="link"
               data-href="${escAttr(href)}"
               aria-label="${escAttr(name)} profile">
        <div class="fmx-body">
          <div class="fmx-top">
            <div class="fmx-avatar">
              <div class="fmx-initial">${esc(ini)}</div>
              ${img ? `<img class="fmx-img" src="${escAttr(img)}" alt="${escAttr(name)}" loading="lazy">` : ``}
            </div>

            <div style="min-width:0;flex:1;">
              <h3 class="fmx-name">${esc(name)}</h3>
              <div class="fmx-desig">${esc(desig)}</div>
            </div>
          </div>

          <div class="fmx-meta">
            ${metaLine('Qualification', qualification)}
            ${metaLine('Specification', specification)}
            ${metaLine('Experience', experience)}
            ${metaLine('Interest', interest)}
            ${metaLine('Administration', administration)}
            ${metaLine('Research Project', research)}
          </div>

          <div class="fmx-links">
            ${email ? `<div><b>Email:</b> <a data-stop-card="1" href="mailto:${escAttr(email)}">${esc(email)}</a></div>` : ``}
            ${website ? `<div><b>Website:</b> <a data-stop-card="1" href="${escAttr(normalizeUrl(website))}" target="_blank" rel="noopener">${esc(website)}</a></div>` : ``}
          </div>

          ${buildSocialFromItem(it)}
        </div>
      </article>
    `;
  }

  function render(items){
    if (!els.grid || !els.state) return;

    if (!items.length){
      els.grid.style.display = 'none';
      els.state.style.display = '';
      els.state.innerHTML = `
        <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
          <i class="fa-regular fa-face-frown"></i>
        </div>
        No faculty found for this department.
      `;
      return;
    }

    els.state.style.display = 'none';
    els.grid.style.display = '';
    els.grid.innerHTML = items.map(cardHtml).join('');
    bindAvatarImages(els.grid);
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
      const cls = active ? 'fmx-pagebtn active' : 'fmx-pagebtn';
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

  async function load(){
    showSkeleton();

    try{
      const js = await fetchJson(buildListUrl());
      const items = Array.isArray(js?.data) ? js.data : [];
      const p = js?.pagination || {};

      state.lastPage = parseInt(p.last_page ?? 1, 10) || 1;
      state.page = parseInt(p.page ?? state.page, 10) || state.page;

      hideSkeleton();

      render(items);
      renderPager();

    } catch (e){
      hideSkeleton();
      if (els.grid) els.grid.style.display = 'none';
      if (els.pager) els.pager.style.display = 'none';

      if (els.state){
        els.state.style.display = '';
        els.state.innerHTML = `
          <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
          </div>
          Could not load faculty.
          <div style="margin-top:8px;font-size:12.5px;opacity:.9;">
            API: <b>${esc(API)}</b>
          </div>
        `;
      }
    }
  }

  document.addEventListener('DOMContentLoaded', async () => {
    showSelectDeptState();

    await loadDepartments();

    // deep-link ?d-{uuid}
    const deep = extractDeptUuidFromUrl();
    if (deep && deptByUuid.has(deep)){
      els.dept.value = deep;
      state.deptUuid = deep;
      state.deptName = deptByUuid.get(deep)?.title || '';
      if (els.sub) els.sub.textContent = state.deptName ? ('Faculty members of ' + state.deptName) : 'Faculty members';

      els.search.disabled = false;
      els.search.placeholder = 'Search faculty (name/designation/qualification)…';
      await load();
    }

    // dept change
    els.dept && els.dept.addEventListener('change', async () => {
      const v = (els.dept.value || '').toString().trim();

      state.page = 1;
      state.q = '';
      if (els.search) els.search.value = '';

      if (!v){
        state.deptUuid = '';
        state.deptName = '';
        if (els.sub) els.sub.textContent = 'Select a department to view its faculty members.';
        if (els.search){
          els.search.disabled = true;
          els.search.placeholder = 'Select a department to search…';
        }
        await load();   // ✅ load all
        return;
      }

      state.deptUuid = v;
      state.deptName = deptByUuid.get(v)?.title || '';

      if (els.sub){
        els.sub.textContent = state.deptName ? ('Faculty members of ' + state.deptName) : 'Faculty members';
      }

      if (els.search){
        els.search.disabled = false;
        els.search.placeholder = 'Search faculty (name/designation/qualification)…';
      }

      await load();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // search (debounced)
    let t = null;
    els.search && els.search.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(async () => {
        state.q = (els.search.value || '').trim();
        state.page = 1;
        await load();
      }, 260);
    });

    // pagination
    document.addEventListener('click', async (e) => {
      const b = e.target.closest('button.fmx-pagebtn[data-page]');
      if (!b) return;
      const p = parseInt(b.dataset.page, 10);
      if (!p || Number.isNaN(p) || p === state.page) return;
      state.page = p;
      await load();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // card click -> profile
    document.addEventListener('click', (e) => {
      if (e.target.closest('[data-stop-card="1"]')) return;
      const card = e.target.closest('.fmx-card[data-href]');
      if (!card) return;
      const href = card.getAttribute('data-href') || '#';
      if (!href || href === '#') return;
      window.location.href = href;
    });

    // keyboard open
    document.addEventListener('keydown', (e) => {
      const card = e.target.closest?.('.fmx-card[data-href]');
      if (!card) return;
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        const href = card.getAttribute('data-href') || '#';
        if (href && href !== '#') window.location.href = href;
      }
    });
  });

})();
</script>
