{{-- resources/views/landing/placed-students.blade.php --}}

{{-- (optional) FontAwesome for icons used below; remove if already included in header --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

<style>
.psx-wrap{
  --psx-brand: var(--primary-color, #9E363A);
  --psx-ink: #0f172a;
  --psx-muted: #64748b;
  --psx-bg: var(--page-bg, #ffffff);
  --psx-card: var(--surface, #ffffff);
  --psx-line: var(--line-soft, rgba(15,23,42,.10));
  --psx-shadow: 0 10px 24px rgba(2,6,23,.08);

  --psx-card-w: 247px;
  --psx-card-h: 329px;
  --psx-radius: 18px;

  max-width: 1320px;
  margin: 18px auto 54px;
  padding: 0 12px;
  background: transparent;
  position: relative;
  overflow: visible;
}

.psx-head{
  background: var(--psx-card);
  border: 1px solid var(--psx-line);
  border-radius: 16px;
  box-shadow: var(--psx-shadow);
  padding: 14px 16px;
  margin-bottom: 16px;
  display:flex;
  gap: 12px;
  align-items:center;
  justify-content:space-between;
  flex-wrap: wrap;
}

.psx-head > div:first-child{
  min-width: 0;
  flex: 0 1 auto;
}

.psx-title{
  margin:0;
  font-weight: 950;
  letter-spacing: .2px;
  color: var(--psx-ink);
  font-size: 28px;
  display:flex;
  align-items:center;
  gap: 10px;
  white-space: nowrap;
}
.psx-title i{ color: var(--psx-brand); }

.psx-sub{
  margin: 6px 0 0;
  color: var(--psx-muted);
  font-size: 14px;
}

.psx-tools{
  display:flex;
  gap: 10px;
  align-items:center;
  flex-wrap: nowrap;
  flex: 1 1 560px;
  min-width: 0;
  justify-content: flex-end;
}

.psx-search{
  position: relative;
  min-width: 0;
  max-width: 520px;
  flex: 1 1 320px;
}
.psx-search i{
  position:absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  opacity: .65;
  color: var(--psx-muted);
  pointer-events:none;
}
.psx-search input{
  width:100%;
  height: 42px;
  border-radius: 999px;
  padding: 11px 12px 11px 42px;
  border: 1px solid var(--psx-line);
  background: var(--psx-card);
  color: var(--psx-ink);
  outline: none;
  min-width: 0;
}
.psx-search input:focus{
  border-color: rgba(201,75,80,.55);
  box-shadow: 0 0 0 4px rgba(201,75,80,.18);
}

.psx-select{
  position: relative;
  min-width: 0;
  max-width: 320px;
  flex: 0 1 280px;
}
.psx-select__icon{
  position:absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  opacity: .70;
  color: var(--psx-muted);
  pointer-events:none;
  font-size: 14px;
}
.psx-select__caret{
  position:absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  opacity: .70;
  color: var(--psx-muted);
  pointer-events:none;
  font-size: 12px;
}
.psx-select select{
  width: 100%;
  height: 42px;
  border-radius: 999px;
  padding: 10px 38px 10px 42px;
  border: 1px solid var(--psx-line);
  background: var(--psx-card);
  color: var(--psx-ink);
  outline: none;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.psx-select select:focus{
  border-color: rgba(201,75,80,.55);
  box-shadow: 0 0 0 4px rgba(201,75,80,.18);
}

.psx-grid{
  display:grid;
  grid-template-columns: repeat(auto-fill, var(--psx-card-w));
  gap: 18px;
  align-items: start;
  justify-content: center;
}

.psx-card{
  position: relative;
  width: var(--psx-card-w);
  height: var(--psx-card-h);
  border-radius: var(--psx-radius);
  overflow:hidden;
  display:block;
  text-decoration:none !important;
  color: inherit;
  background: #fff;
  border: 1px solid rgba(2,6,23,.08);
  box-shadow: 0 12px 26px rgba(0,0,0,.10);
  transform: translateZ(0);
  transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
  cursor:pointer;
}
.psx-card:hover{
  transform: translateY(-4px);
  box-shadow: 0 18px 42px rgba(0,0,0,.16);
  border-color: rgba(158,54,58,.22);
}

.psx-card .bg{
  position:absolute;
  inset:0;
  background-size: cover;
  background-position: center;
  filter: saturate(1.02);
  transform: scale(1.0001);
}

.psx-card .vignette{
  position:absolute;
  inset:0;
  background:
    radial-gradient(1200px 500px at 50% -20%, rgba(255,255,255,.10), rgba(0,0,0,0) 60%),
    linear-gradient(180deg, rgba(0,0,0,.00) 28%, rgba(0,0,0,.12) 60%, rgba(0,0,0,.62) 100%);
}

.psx-card .info{
  position:absolute;
  left: 14px;
  right: 14px;
  bottom: 14px;
  z-index: 2;
}
.psx-name{
  margin:0;
  font-size: 18px;
  font-weight: 950;
  line-height: 1.12;
  color: #fff;
  text-shadow: 0 6px 16px rgba(0,0,0,.35);
}
.psx-meta{
  margin: 7px 0 0;
  font-size: 13px;
  font-weight: 800;
  color: rgba(255,255,255,.90);
  text-shadow: 0 6px 16px rgba(0,0,0,.35);
  line-height: 1.2;
}
.psx-meta .dot{
  opacity: .85;
  padding: 0 6px;
}
.psx-submeta{
  margin: 7px 0 0;
  font-size: 12.5px;
  color: rgba(255,255,255,.82);
  text-shadow: 0 6px 16px rgba(0,0,0,.35);
  line-height: 1.2;
}

.psx-pill{
  position:absolute;
  top: 12px;
  left: 12px;
  z-index: 2;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 11.5px;
  font-weight: 900;
  letter-spacing:.15px;
  color:#fff;
  background: rgba(0,0,0,.28);
  border: 1px solid rgba(255,255,255,.20);
  backdrop-filter: blur(6px);
  max-width: calc(100% - 24px);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.psx-placeholder{
  position:absolute;
  inset:0;
  display:grid;
  place-items:center;
  background:
    radial-gradient(800px 360px at 20% 10%, rgba(158,54,58,.20), transparent 60%),
    radial-gradient(900px 400px at 80% 90%, rgba(158,54,58,.14), transparent 60%),
    linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,255,255,.82));
}
.psx-initials{
  width: 86px;
  height: 86px;
  border-radius: 24px;
  display:grid;
  place-items:center;
  font-weight: 950;
  font-size: 28px;
  color: var(--psx-brand);
  background: rgba(158,54,58,.12);
  border: 1px solid rgba(158,54,58,.25);
}

.psx-state{
  background: var(--psx-card);
  border: 1px solid var(--psx-line);
  border-radius: 16px;
  box-shadow: var(--psx-shadow);
  padding: 18px;
  color: var(--psx-muted);
  text-align:center;
}

.psx-skeleton{
  display:grid;
  grid-template-columns: repeat(auto-fill, var(--psx-card-w));
  gap: 18px;
  justify-content: center;
}
.psx-sk{
  border-radius: var(--psx-radius);
  border: 1px solid var(--psx-line);
  background: #fff;
  overflow:hidden;
  position:relative;
  box-shadow: 0 10px 24px rgba(2,6,23,.08);
  height: var(--psx-card-h);
}
.psx-sk:before{
  content:'';
  position:absolute;
  inset:0;
  transform: translateX(-60%);
  background: linear-gradient(90deg, transparent, rgba(148,163,184,.22), transparent);
  animation: psxSkMove 1.15s ease-in-out infinite;
}
@keyframes psxSkMove{
  to{ transform: translateX(60%); }
}

.psx-pagination{
  display:flex;
  justify-content:center;
  margin-top: 18px;
}
.psx-pagination .psx-pager{
  display:flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items:center;
  justify-content:center;
  padding: 10px;
}
.psx-pagebtn{
  border:1px solid var(--psx-line);
  background: var(--psx-card);
  color: var(--psx-ink);
  border-radius: 12px;
  padding: 9px 12px;
  font-size: 13px;
  font-weight: 950;
  box-shadow: 0 8px 18px rgba(2,6,23,.06);
  cursor:pointer;
  user-select:none;
}
.psx-pagebtn:hover{
  background: rgba(2,6,23,.03);
}
.psx-pagebtn[disabled]{
  opacity:.55;
  cursor:not-allowed;
}
.psx-pagebtn.active{
  background: rgba(201,75,80,.12);
  border-color: rgba(201,75,80,.35);
  color: var(--psx-brand);
}

@media (max-width: 992px){
  .psx-head{
    align-items: flex-end;
  }
  .psx-tools{
    flex-wrap: wrap;
    justify-content: flex-start;
  }
}

@media (max-width: 640px){
  .psx-title{
    font-size: 24px;
    white-space: normal;
  }
  .psx-search{
    min-width: 100%;
    max-width: 100%;
    flex: 1 1 100%;
  }
  .psx-select{
    min-width: 100%;
    max-width: 100%;
    flex: 1 1 100%;
  }
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
  class="psx-wrap"
  data-api="{{ url('/api/placed-students') }}"
  data-api-alt="{{ url('/api/placed-students/public/index') }}"
  data-dept-api="{{ url('/api/public/departments') }}"
  data-profile-base="{{ url('/user/profile') }}"
>
  <div class="psx-head">
    <div>
      <h1 class="psx-title"><i class="fa-solid fa-user-graduate"></i>Placed Students</h1>
      <div class="psx-sub" id="psxSub">Explore our recent placements.</div>
    </div>

    <div class="psx-tools">
      <div class="psx-search">
        <i class="fa fa-magnifying-glass"></i>
        <input id="psxSearch" type="search" placeholder="Search (name, department, company, role, CTC)…">
      </div>

      <div class="psx-select" title="Filter by department">
        <i class="fa-solid fa-building-columns psx-select__icon"></i>
        <select id="psxDept" aria-label="Filter by department">
          <option value="">All Departments</option>
        </select>
        <i class="fa-solid fa-chevron-down psx-select__caret"></i>
      </div>
    </div>
  </div>

  <div id="psxGrid" class="psx-grid" style="display:none;"></div>

  <div id="psxSkeleton" class="psx-skeleton"></div>
  <div id="psxState" class="psx-state" style="display:none;"></div>

  <div class="psx-pagination">
    <div id="psxPager" class="psx-pager" style="display:none;"></div>
  </div>
</div>

<script>
(() => {
  if (window.__PUBLIC_PLACED_STUDENTS_ALL__) return;
  window.__PUBLIC_PLACED_STUDENTS_ALL__ = true;

  const root = document.querySelector('.psx-wrap');
  if (!root) return;

  const API1 = root.getAttribute('data-api') || '/api/placed-students';
  const API2 = root.getAttribute('data-api-alt') || '/api/placed-students/public/index';
  const DEPT_API = root.getAttribute('data-dept-api') || '/api/public/departments';
  const PROFILE_BASE_RAW = root.getAttribute('data-profile-base') || '/user/profile';

  const APP_URL = @json(url('/'));
  const ORIGIN = (APP_URL || window.location.origin || '').toString().replace(/\/+$/,'');

  const PSX_DEBUG = new URLSearchParams(window.location.search || '').has('psx_debug');

  function debugLog(...args){
    if (PSX_DEBUG) console.log('[PlacedStudents]', ...args);
  }

  function debugWarn(...args){
    if (PSX_DEBUG) console.warn('[PlacedStudents]', ...args);
  }

  const $ = (id) => document.getElementById(id);

  const els = {
    grid: $('psxGrid'),
    skel: $('psxSkeleton'),
    state: $('psxState'),
    pager: $('psxPager'),
    search: $('psxSearch'),
    dept: $('psxDept'),
    sub: $('psxSub'),
  };

  const state = {
    page: 1,
    perPage: 12,
    lastPage: 1,
    q: '',
    deptUuid: '',
    deptId: null,
    deptName: '',
  };

  let activeController = null;
  let eventsBound = false;

  let allPlaced = null;
  let deptByUuid = new Map();
  let deptByShortcode = new Map();
  let deptBySlug = new Map();

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;',
      '<':'&lt;',
      '>':'&gt;',
      '"':'&quot;',
      "'":'&#39;'
    }[s]));
  }

  function escAttr(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;',
      '<':'&lt;',
      '>':'&gt;',
      '"':'&quot;',
      "'":'&#39;'
    }[s]));
  }

  function looksLikeUuid(v){
    return /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(String(v || '').trim());
  }

  function normalizeUrl(url){
    const u = (url || '').toString().trim();
    if (!u) return '';
    if (/^(data:|blob:|https?:\/\/)/i.test(u)) return u;
    if (u.startsWith('/')) return ORIGIN + u;
    return ORIGIN + '/' + u;
  }

  function pick(obj, keys){
    for (const k of keys){
      const v = obj?.[k];
      if (v !== null && v !== undefined && String(v).trim() !== '') return v;
    }
    return '';
  }

  function initials(name){
    const n = (name || '').trim();
    if (!n) return 'PS';

    const parts = n.split(/\s+/).filter(Boolean).slice(0,2);
    return parts.map(p => p[0].toUpperCase()).join('');
  }

  function fmtDate(d){
    const s = (d || '').toString().trim();
    if (!s) return '';

    const m = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (!m) return s;

    const dt = new Date(`${m[1]}-${m[2]}-${m[3]}T00:00:00`);

    try{
      return new Intl.DateTimeFormat('en-IN', {
        day:'2-digit',
        month:'short',
        year:'numeric'
      }).format(dt);
    }catch(_){
      return s;
    }
  }

  function resolveName(item){
    return String(
      pick(item, ['user_name','student_name','name','full_name']) ||
      pick(item?.user, ['name','full_name','username']) ||
      'Student'
    );
  }

  function resolveDepartmentName(item){
    return String(
      pick(item, ['department_title','department_name','dept_name']) ||
      pick(item?.department, ['title','name']) ||
      ''
    );
  }

  function resolveDepartmentId(item){
    const did =
      pick(item, ['department_id','dept_id']) ||
      pick(item?.department, ['id']) ||
      '';

    return (did === null || did === undefined) ? '' : String(did);
  }

  function resolveDepartmentUuid(item){
    const du =
      pick(item, ['department_uuid','dept_uuid']) ||
      pick(item?.department, ['uuid']) ||
      '';

    return (du === null || du === undefined) ? '' : String(du);
  }

  function resolveCompany(item){
    return String(
      pick(item?.metadata, ['company','company_name']) ||
      pick(item, ['company','company_name','employer']) ||
      ''
    );
  }

  function resolveRoleTitle(item){
    return String(
      pick(item, ['role_title','role','designation','job_title','position']) ||
      pick(item?.metadata, ['role_title','role','designation','job_title']) ||
      ''
    );
  }

  function resolveCTC(item){
    const c =
      pick(item, ['ctc','package','salary','offer_package']) ||
      pick(item?.metadata, ['ctc','package','salary']) ||
      '';

    return (c === null || c === undefined) ? '' : String(c);
  }

  function resolveOfferDate(item){
    return String(
      pick(item, ['offer_date','placement_date']) ||
      pick(item?.metadata, ['offer_date','placement_date']) ||
      ''
    );
  }

  function resolveJoiningDate(item){
    return String(
      pick(item, ['joining_date','date_of_joining']) ||
      pick(item?.metadata, ['joining_date','date_of_joining']) ||
      ''
    );
  }

  function resolveImage(item){
    const img =
      pick(item, ['user_image','image','user_image_url','image_url','photo_url','profile_image_url','avatar']) ||
      pick(item?.user, ['image','photo_url','image_url','profile_image_url','avatar']) ||
      '';

    return normalizeUrl(img);
  }

  function resolveUserUuid(item){
    const u =
      pick(item, ['user_uuid','uuid']) ||
      pick(item?.user, ['uuid','user_uuid']) ||
      '';

    const s = String(u || '').trim();
    return looksLikeUuid(s) ? s : '';
  }

  function buildProfileUrl(userUuid){
    const id = String(userUuid || '').trim();
    if (!id) return '#';

    let base = (PROFILE_BASE_RAW || '/user/profile').toString().trim().replace(/\/+$/,'');

    if (/^https?:\/\//i.test(base)) {
      return base + '/' + encodeURIComponent(id);
    }

    if (!base.startsWith('/')) base = '/' + base;

    return ORIGIN + base + '/' + encodeURIComponent(id);
  }

  function toItems(js){
    if (Array.isArray(js)) return js;

    if (Array.isArray(js?.data)) return js.data;
    if (Array.isArray(js?.items)) return js.items;
    if (Array.isArray(js?.results)) return js.results;
    if (Array.isArray(js?.records)) return js.records;
    if (Array.isArray(js?.students)) return js.students;
    if (Array.isArray(js?.placed_students)) return js.placed_students;

    if (Array.isArray(js?.data?.data)) return js.data.data;
    if (Array.isArray(js?.data?.items)) return js.data.items;
    if (Array.isArray(js?.data?.results)) return js.data.results;
    if (Array.isArray(js?.data?.records)) return js.data.records;
    if (Array.isArray(js?.data?.students)) return js.data.students;
    if (Array.isArray(js?.data?.placed_students)) return js.data.placed_students;

    debugWarn('Placed students response shape not recognized:', js);
    return [];
  }

  function toDepartments(js){
    if (Array.isArray(js)) return js;
    if (Array.isArray(js?.data)) return js.data;
    if (Array.isArray(js?.items)) return js.items;
    if (Array.isArray(js?.results)) return js.results;
    if (Array.isArray(js?.data?.data)) return js.data.data;
    if (Array.isArray(js?.data?.items)) return js.data.items;
    if (Array.isArray(js?.data?.results)) return js.data.results;
    return [];
  }

  function isActiveValue(v){
    if (v === null || v === undefined || v === '') return true;

    const s = String(v).trim().toLowerCase();
    return !['0','false','inactive','disabled','no'].includes(s);
  }

  function getUrlObj(){
    return new URL(window.location.href);
  }

  function syncUrl(){
    const url = getUrlObj();

    if (state.deptUuid) {
      const meta = deptByUuid.get(state.deptUuid);
      const shortcode = meta?.shortcode || meta?.slug || '';

      if (shortcode) {
        url.searchParams.set('dept', shortcode);
        url.searchParams.delete('department');
      } else {
        url.searchParams.set('department', state.deptUuid);
        url.searchParams.delete('dept');
      }
    } else {
      url.searchParams.delete('department');
      url.searchParams.delete('dept');
    }

    history.replaceState({}, '', url.pathname + url.search + url.hash);
  }

  function extractDeptParamFromUrl(){
    const url = getUrlObj();

    const directDept = (url.searchParams.get('dept') || '').trim();
    const directDepartment = (url.searchParams.get('department') || '').trim();

    if (directDept) return directDept;
    if (directDepartment) return directDepartment;

    const hay = url.search + ' ' + url.href;
    const m = hay.match(/d-([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/i);

    return m ? m[1] : '';
  }

  function resolveDeptParamToUuid(param){
    const p = (param || '').toString().trim();
    if (!p) return '';

    if (deptByUuid.has(p)) return p;

    const lower = p.toLowerCase();

    if (deptByShortcode.has(lower)) return deptByShortcode.get(lower).uuid;
    if (deptBySlug.has(lower)) return deptBySlug.get(lower);

    return p;
  }

  function readDeptSlugFromPath(){
    const parts = window.location.pathname.split('/').filter(Boolean);
    const idx = parts.findIndex(p => p.toLowerCase() === 'alumni');

    if (idx > 0) return parts[idx - 1];

    return '';
  }

  function showSkeleton(){
    const sk = els.skel;
    const st = els.state;
    const grid = els.grid;
    const pager = els.pager;

    if (grid) grid.style.display = 'none';
    if (pager) pager.style.display = 'none';
    if (st) st.style.display = 'none';

    if (!sk) return;

    sk.style.display = '';
    sk.innerHTML = Array.from({length: 12}).map(() => `<div class="psx-sk"></div>`).join('');
  }

  function hideSkeleton(){
    const sk = els.skel;
    if (!sk) return;

    sk.style.display = 'none';
    sk.innerHTML = '';
  }

  function showState(html){
    hideSkeleton();

    if (els.grid) els.grid.style.display = 'none';
    if (els.pager) els.pager.style.display = 'none';

    if (els.state){
      els.state.style.display = '';
      els.state.innerHTML = html;
    }
  }

  async function fetchJson(url){
    if (activeController) activeController.abort();

    activeController = new AbortController();

    const res = await fetch(url, {
      headers: {
        'Accept':'application/json',
        'X-Requested-With':'XMLHttpRequest'
      },
      signal: activeController.signal
    });

    const js = await res.json().catch(() => ({}));

    if (!res.ok) {
      throw new Error(js?.message || ('Request failed: ' + res.status));
    }

    return js;
  }

  async function tryFetchList(urls){
    let lastErr = null;
    let firstSuccessfulEmpty = null;

    for (const u of urls){
      try{
        const js = await fetchJson(u);
        const items = toItems(js);

        debugLog('API used:', u);
        debugLog('Raw response:', js);
        debugLog('Parsed items:', items);

        if (!firstSuccessfulEmpty) {
          firstSuccessfulEmpty = { ok:true, used:u, js, items };
        }

        if (items.length) {
          return { ok:true, used:u, js, items };
        }

        lastErr = new Error('Endpoint returned zero parsed items: ' + u);
      }catch(e){
        if (e?.name === 'AbortError') throw e;

        lastErr = e;
        debugWarn('Endpoint failed:', u, e);
      }
    }

    if (firstSuccessfulEmpty) return firstSuccessfulEmpty;

    return {
      ok:false,
      used:'',
      js:{},
      items:[],
      error:lastErr || new Error('Placed students load failed')
    };
  }

  function setDeptSelection(param){
    const sel = els.dept;
    const uuid = resolveDeptParamToUuid(param);

    if (!sel) return;

    if (!uuid){
      sel.value = '';
      state.deptUuid = '';
      state.deptId = null;
      state.deptName = '';

      if (els.sub) els.sub.textContent = 'Explore our recent placements.';

      return;
    }

    const meta = deptByUuid.get(uuid);

    if (!meta) {
      debugWarn('Department not found:', param, uuid);
      return;
    }

    sel.value = uuid;
    state.deptUuid = uuid;
    state.deptId = meta.id ?? null;
    state.deptName = meta.title ?? '';

    if (els.sub){
      els.sub.textContent = state.deptName
        ? ('Placed students for ' + state.deptName)
        : 'Placed students (filtered)';
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
      const res = await fetch(DEPT_API, {
        headers: {
          'Accept':'application/json',
          'X-Requested-With':'XMLHttpRequest'
        }
      });

      const js = await res.json().catch(() => ({}));

      if (!res.ok) {
        throw new Error(js?.message || ('HTTP ' + res.status));
      }

      const items = toDepartments(js);

      const depts = items
        .map(d => {
          const shortcode = (
            d?.short_name ??
            d?.short_code ??
            d?.shortcode ??
            d?.slug ??
            ''
          ).toString().trim().toLowerCase();

          const slug = (d?.slug ?? '').toString().trim().toLowerCase();

          return {
            id: d?.id ?? null,
            uuid: (d?.uuid ?? d?.department_uuid ?? '').toString().trim(),
            shortcode,
            slug,
            title: (d?.title ?? d?.name ?? d?.department_name ?? '').toString().trim(),
            active: d?.active ?? d?.status ?? 1,
          };
        })
        .filter(x => x.uuid && x.title && isActiveValue(x.active));

      deptByUuid = new Map(depts.map(d => [d.uuid, d]));
      deptByShortcode = new Map(depts.filter(d => d.shortcode).map(d => [d.shortcode, d]));
      deptBySlug = new Map(depts.filter(d => d.slug).map(d => [d.slug, d.uuid]));

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

  async function ensurePlacedLoaded(force=false){
    if (allPlaced && !force) return;

    showSkeleton();

    try{
      const urls = [API1, API2].filter(Boolean).map(base => {
        const u = new URL(base, window.location.origin);

        u.searchParams.set('page', '1');
        u.searchParams.set('per_page', '500');
        u.searchParams.set('status', 'active');
        u.searchParams.set('sort', 'created_at');
        u.searchParams.set('direction', 'desc');

        return u.toString();
      });

      const res = await tryFetchList(urls);

      if (!res.ok) {
        throw (res.error || new Error('Placed students load failed'));
      }

      allPlaced = Array.isArray(res.items) ? res.items : [];

      debugLog('Final endpoint:', res.used);
      debugLog('Final count:', allPlaced.length);
    } finally {
      hideSkeleton();
    }
  }

  function applyFilterAndSearch(){
    const q = (state.q || '').toString().trim().toLowerCase();
    let items = Array.isArray(allPlaced) ? allPlaced.slice() : [];

    if (
      state.deptUuid &&
      state.deptId !== null &&
      state.deptId !== undefined &&
      String(state.deptId) !== ''
    ){
      const deptIdStr = String(state.deptId);
      const deptUuidStr = String(state.deptUuid);

      items = items.filter(it => {
        const did = resolveDepartmentId(it);
        const duu = resolveDepartmentUuid(it);

        return (did && did === deptIdStr) || (duu && duu === deptUuidStr);
      });
    } else if (state.deptUuid){
      const deptUuidStr = String(state.deptUuid);

      items = items.filter(it => String(resolveDepartmentUuid(it) || '') === deptUuidStr);
    }

    if (q){
      items = items.filter(it => {
        const name = resolveName(it).toLowerCase();
        const dept = resolveDepartmentName(it).toLowerCase();
        const company = resolveCompany(it).toLowerCase();
        const role = resolveRoleTitle(it).toLowerCase();
        const ctc = resolveCTC(it).toLowerCase();
        const offer = (resolveOfferDate(it) || '').toLowerCase();
        const join = (resolveJoiningDate(it) || '').toLowerCase();

        return (
          name.includes(q) ||
          dept.includes(q) ||
          company.includes(q) ||
          role.includes(q) ||
          ctc.includes(q) ||
          offer.includes(q) ||
          join.includes(q)
        );
      });
    }

    return items;
  }

  function cardHtml(it){
    const name = resolveName(it);
    const deptName = resolveDepartmentName(it);
    const company = resolveCompany(it);
    const role = resolveRoleTitle(it);
    const ctc = resolveCTC(it);
    const offerDate = fmtDate(resolveOfferDate(it));
    const joiningDate = fmtDate(resolveJoiningDate(it));
    const img = resolveImage(it);

    const userUuid = resolveUserUuid(it);
    const href = buildProfileUrl(userUuid);

    const pill = deptName
      ? `<div class="psx-pill" title="${escAttr(deptName)}">${esc(deptName)}</div>`
      : '';

    const metaLine =
      (company || role)
        ? `<p class="psx-meta">${esc(company || 'Company')}${company && role ? `<span class="dot">•</span>` : ''}${esc(role || '')}</p>`
        : `<p class="psx-meta">${deptName ? esc(deptName) : 'Placed Student'}</p>`;

    const subMetaParts = [];

    if (ctc) subMetaParts.push(`CTC: ${esc(ctc)}`);
    if (offerDate) subMetaParts.push(`Offer: ${esc(offerDate)}`);
    if (joiningDate) subMetaParts.push(`Join: ${esc(joiningDate)}`);

    const subMetaLine = subMetaParts.length
      ? `<p class="psx-submeta">${subMetaParts.join(`<span class="dot">•</span>`)}</p>`
      : '';

    const inner = !img
      ? `
        <div class="psx-placeholder">
          <div class="psx-initials">${esc(initials(name))}</div>
        </div>
      `
      : `<div class="bg" style="background-image:url('${escAttr(img)}')"></div>`;

    return `
      <a class="psx-card"
         href="${escAttr(href)}"
         data-profile="${escAttr(href)}"
         target="_blank"
         rel="noopener noreferrer"
         aria-label="${escAttr(name)} profile (opens in new tab)">
        ${inner}
        ${pill}
        <div class="vignette"></div>
        <div class="info">
          <p class="psx-name">${esc(name)}</p>
          ${metaLine}
          ${subMetaLine}
        </div>
      </a>
    `;
  }

  function render(items){
    const grid = els.grid;
    const st = els.state;

    if (!grid || !st) return;

    if (!items.length){
      grid.style.display = 'none';
      st.style.display = '';

      const deptLine = state.deptName
        ? `<div style="margin-top:6px;font-size:12.5px;opacity:.95;">Department: <b>${esc(state.deptName)}</b></div>`
        : '';

      st.innerHTML = `
        <div aria-hidden="true" style="width:170px;max-width:100%;margin:0 auto 10px;display:block;color:var(--psx-brand);">
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
        No Placed Students found.
        ${deptLine}
      `;

      return;
    }

    st.style.display = 'none';
    grid.style.display = '';
    grid.innerHTML = items.map(cardHtml).join('');
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
      const cls = active ? 'psx-pagebtn active' : 'psx-pagebtn';

      return `<button class="${cls}" ${dis} data-page="${page}">${label}</button>`;
    };

    let html = '';

    html += btn('Previous', Math.max(1, cur - 1), { disabled: cur <= 1 });

    const win = 2;
    const start = Math.max(1, cur - win);
    const end = Math.min(last, cur + win);

    if (start > 1){
      html += btn('1', 1, { active: cur === 1 });
      if (start > 2) html += `<span style="opacity:.6;padding:0 4px;">…</span>`;
    }

    for (let p = start; p <= end; p++){
      html += btn(String(p), p, { active: p === cur });
    }

    if (end < last){
      if (end < last - 1) html += `<span style="opacity:.6;padding:0 4px;">…</span>`;
      html += btn(String(last), last, { active: cur === last });
    }

    html += btn('Next', Math.min(last, cur + 1), { disabled: cur >= last });

    pager.innerHTML = html;
    pager.style.display = 'flex';
  }

  function repaint(){
    const filtered = applyFilterAndSearch();

    state.lastPage = Math.max(1, Math.ceil(filtered.length / state.perPage));

    if (state.page > state.lastPage) {
      state.page = state.lastPage;
    }

    const start = (state.page - 1) * state.perPage;
    const pageItems = filtered.slice(start, start + state.perPage);

    render(pageItems);
    renderPager();
  }

  function bindEventsOnce(){
    if (eventsBound) return;
    eventsBound = true;

    document.addEventListener('click', (e) => {
      const card = e.target.closest('.psx-card');
      if (!card) return;

      const url = card.getAttribute('data-profile') || card.getAttribute('href') || '';
      if (!url || url === '#') return;

      if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

      e.preventDefault();
      window.open(url, '_blank', 'noopener');
    });

    let t = null;

    els.search && els.search.addEventListener('input', () => {
      clearTimeout(t);

      t = setTimeout(() => {
        state.q = (els.search.value || '').trim();
        state.page = 1;
        repaint();
      }, 260);
    });

    els.dept && els.dept.addEventListener('change', () => {
      const v = (els.dept.value || '').toString();

      if (v === '__loading') return;

      if (!v){
        setDeptSelection('');
      } else {
        setDeptSelection(v);
      }

      syncUrl();

      state.page = 1;
      repaint();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    document.addEventListener('click', (e) => {
      const b = e.target.closest('button.psx-pagebtn[data-page]');
      if (!b) return;

      const p = parseInt(b.dataset.page, 10);

      if (!p || Number.isNaN(p) || p === state.page) return;

      state.page = p;
      repaint();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  async function initPlacedStudents(){
    bindEventsOnce();

    try{
      await loadDepartments();

      const deepDeptParam = extractDeptParamFromUrl();

      if (deepDeptParam){
        setDeptSelection(deepDeptParam);
      } else {
        const slug = readDeptSlugFromPath();
        const uuidFromSlug = slug ? resolveDeptParamToUuid(slug) : '';

        if (uuidFromSlug && deptByUuid.has(uuidFromSlug)) {
          setDeptSelection(uuidFromSlug);
        } else {
          setDeptSelection('');
        }
      }

      syncUrl();

      await ensurePlacedLoaded(false);
      repaint();
    }catch(e){
      if (e?.name === 'AbortError') return;

      console.error('Placed students page failed:', e);

      allPlaced = [];

      showState(`
        Unable to load placed students right now.
        <div style="margin-top:6px;font-size:12.5px;opacity:.9;">
          Please check the API response format or open this page with <b>?psx_debug=1</b> to see debug logs in the console.
        </div>
      `);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPlacedStudents);
  } else {
    initPlacedStudents();
  }
})();
</script>