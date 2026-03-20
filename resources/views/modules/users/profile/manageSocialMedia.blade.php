{{-- resources/views/modules/user/manageSocialMedia.blade.php --}}

@section('title','Social Media')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* Dropdowns inside table */
.table-wrap .dropdown{position:relative}
.dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
.dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:220px;
  z-index:1085
}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color) !important}

/* Table */
.table-responsive{
  overflow-x:auto !important;
  overflow-y:visible !important;
  -webkit-overflow-scrolling:touch;
}
.card-body{overflow:visible !important}
.table{min-width:980px}

.table-wrap.card{
  position:relative;
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible
}
.table-wrap .card-body{overflow:visible}
.table{--bs-table-bg:transparent}
.table thead th{
  font-weight:600;
  color:var(--muted-color);
  font-size:13px;
  border-bottom:1px solid var(--line-strong);
  background:var(--surface)
}
.table thead.sticky-top{z-index:3}
.table tbody tr{border-top:1px solid var(--line-soft)}
.table tbody tr:hover{background:var(--page-hover)}
td .fw-semibold{color:var(--ink)}
.small{font-size:12.5px}

/* Badges */
.badge-soft-primary{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  color:var(--primary-color)
}
.badge-soft-muted{
  background:color-mix(in oklab, var(--muted-color) 12%, transparent);
  color:var(--muted-color)
}
.badge-soft-success{
  background:color-mix(in oklab, #198754 12%, transparent);
  color:#198754
}

/* Loader */
.inline-loader{
  position:fixed;
  top:0;left:0;width:100%;height:100%;
  background:rgba(0,0,0,0.45);
  display:none;
  justify-content:center;
  align-items:center;
  z-index:9999;
  backdrop-filter:blur(2px)
}
.inline-loader.show{display:flex}
.inline-loader .loader-card{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,0.3)
}
.inline-loader .spinner-border{ width:1.5rem;height:1.5rem; }
.inline-loader .small{margin:0}

.btn-loading{position:relative;color:transparent !important}
.btn-loading::after{
  content:'';
  position:absolute;
  width:16px;height:16px;
  top:50%;left:50%;
  margin:-8px 0 0 -8px;
  border:2px solid transparent;
  border-top:2px solid currentColor;
  border-radius:50%;
  animation:spin 1s linear infinite
}
@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

.soc-toolbar.panel{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-1);
  padding:12px 12px
}
.soc-toolbar .form-select,
.soc-toolbar .form-control{border-radius:12px}

/* Tabs */
.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface)
}
.tab-content,.tab-pane{overflow:visible}

/* Preset logo */
.icon-cell{display:flex;align-items:center;gap:10px}
.icon-badge{
  width:44px;height:44px;border-radius:12px;
  border:1px solid var(--line-soft);
  display:flex;align-items:center;justify-content:center;
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
  flex:0 0 44px;
}
.icon-badge i{font-size:18px}
.icon-badge .tag-logo{
  min-width:26px;
  height:26px;
  border-radius:8px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:0 7px;
  font-size:11px;
  font-weight:700;
  letter-spacing:.02em;
  background:color-mix(in oklab, var(--primary-color) 14%, transparent);
  color:var(--primary-color);
  line-height:1;
}
.icon-meta{display:flex;flex-direction:column;gap:2px}
.icon-meta .muted{font-size:12px;color:var(--muted-color)}
.icon-meta a{font-size:12.5px;text-decoration:none}

.preset-link-input{
  min-width:320px;
  border-radius:12px;
}
.sort-pill{
  min-width:40px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  height:32px;
  border-radius:999px;
  border:1px solid var(--line-strong);
  background:var(--surface);
  font-weight:700;
  color:var(--ink)
}
.preset-note{
  font-size:12.5px;
  color:var(--muted-color)
}
.fixed-count-pill{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:8px 12px;
  border-radius:999px;
  background:color-mix(in oklab, var(--primary-color) 10%, transparent);
  color:var(--primary-color);
  font-size:12.5px;
  font-weight:600
}
.icon-badge img,
.icon-badge svg{
  width:22px;
  height:22px;
  object-fit:contain;
  display:block;
}
@media (max-width: 768px){
  .soc-toolbar .toolbar-main{
    flex-direction:column;
    align-items:stretch !important;
  }
  .soc-toolbar .toolbar-actions{
    width:100%;
    display:flex;
    gap:8px;
    flex-wrap:wrap;
  }
  .soc-toolbar .toolbar-actions .btn{
    flex:1;
    min-width:140px;
  }
  .table{min-width:920px}
}
</style>
@endpush

@section('content')
<div class="crs-wrap">

  <div id="inlineLoader" class="inline-loader">
    @include('partials.overlay')
  </div>

  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#pane-active" role="tab" aria-selected="true">
        <i class="fa-brands fa-hubspot me-1"></i> Social Links
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#pane-bin" role="tab" aria-selected="false">
        <i class="fa fa-trash-can me-1"></i> Bin
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- =========================
       Active - Fixed Preset Rows
       ========================= --}}
    <div class="tab-pane fade show active" id="pane-active" role="tabpanel">

      <div class="row align-items-center g-2 mb-3 soc-toolbar panel">
        <div class="col-12">
          <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap toolbar-main">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="fixed-count-pill">
                <i class="fa fa-list-ol"></i>
                Fixed sequence: 6 platforms
              </span>
              <div class="preset-note">
                Platform names and sort order are locked. Only the links are editable.
              </div>
            </div>

            <div class="toolbar-actions ms-lg-auto">
              <button id="btnReloadRows" class="btn btn-light">
                <i class="fa fa-rotate-right me-1"></i> Reload
              </button>
              <button id="btnSaveAll" class="btn btn-primary">
                <i class="fa fa-floppy-disk me-1"></i> Save
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:260px;">Logo</th>
                  <th style="width:260px;">Platform</th>
                  <th style="width:460px;">Link</th>
                  <th style="width:140px;">Sort Order</th>
                  <th style="width:140px;">Status</th>
                </tr>
              </thead>
              <tbody id="socTbody">
                <tr>
                  <td colspan="5" class="text-center text-muted" style="padding:38px;">Loading…</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo">6 fixed rows</div>
            <div class="text-muted small">Blank link = not saved / removed</div>
          </div>
        </div>
      </div>

    </div>

    {{-- =========================
       Bin (Deleted)
       ========================= --}}
    <div class="tab-pane fade" id="pane-bin" role="tabpanel">

      <div class="row align-items-center g-2 mb-3 soc-toolbar panel">
        <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per Page</label>
            <select id="binPerPage" class="form-select" style="width:96px;">
              <option>10</option><option>20</option><option>50</option><option>100</option>
            </select>
          </div>

          <div class="position-relative" style="min-width:280px;">
            <input id="binSearchInput" type="search" class="form-control ps-5" placeholder="Search in deleted links…">
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>

          <button id="binReset" class="btn btn-light">
            <i class="fa fa-rotate-left me-1"></i>Reset
          </button>
        </div>

        <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
          <div class="toolbar-buttons">
            <button type="button" class="btn btn-outline-danger" id="btnEmptyBin">
              <i class="fa fa-trash-can me-1"></i> Empty Bin
            </button>
          </div>
        </div>
      </div>

      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:260px;">Icon</th>
                  <th style="width:240px;">Platform</th>
                  <th style="width:420px;">Link</th>
                  <th style="width:220px;">Deleted At</th>
                  <th style="width:140px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="binTbody">
                <tr>
                  <td colspan="5" class="text-center text-muted" style="padding:38px;">
                    Click the <b>Bin</b> tab to load deleted records.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="binEmpty" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No deleted records in Bin.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="binResultsInfo">—</div>
            <nav><ul id="binPager" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080">
  <div id="toastSuccess" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastSuccessText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="toastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="toastErrorText">Something went wrong</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// dropdown fix
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.dd-toggle');
  if (!btn) return;
  try {
    const inst = bootstrap.Dropdown.getOrCreateInstance(btn, {
      autoClose: btn.getAttribute('data-bs-auto-close') || undefined,
      boundary: btn.getAttribute('data-bs-boundary') || 'viewport'
    });
    inst.toggle();
  } catch (ex) { console.error('Dropdown toggle error', ex); }
});

document.addEventListener('DOMContentLoaded', function () {
  if (window.__SOCIAL_MANAGE_INIT__) return;
  window.__SOCIAL_MANAGE_INIT__ = true;

  const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
  if (!token) { window.location.href = '/'; return; }

  const PRESET_SOCIALS = [
    {
      key: 'linkedin',
      platform: 'Linked In',
      sort_order: 1,
      db_icon: @json(asset('assets/media/userSocialIcons/linkedin.png')),
      logo_type: 'img',
      logo_value: @json(asset('assets/media/userSocialIcons/linkedin.png')),
      placeholder: 'https://...'
    },
    {
      key: 'vidyan-portal',
      platform: 'Vidyan Portal',
      sort_order: 2,
      db_icon: @json(asset('assets/media/userSocialIcons/irins.jpeg')),
      logo_type: 'img',
      logo_value: @json(asset('assets/media/userSocialIcons/irins.jpeg')),
      placeholder: 'https://...'
    },
    {
      key: 'scopus',
      platform: 'Scopus',
      sort_order: 3,
      db_icon: @json(asset('assets/media/userSocialIcons/scopus.svg')),
      logo_type: 'img',
      logo_value: @json(asset('assets/media/userSocialIcons/scopus.svg')),
      placeholder: 'https://...'
    },
    {
      key: 'google-scholar',
      platform: 'Google Scholar',
      sort_order: 4,
      db_icon: @json(asset('assets/media/userSocialIcons/google.png')),
      logo_type: 'img',
      logo_value: @json(asset('assets/media/userSocialIcons/google.png')),
      placeholder: 'https://...'
    },
    {
      key: 'web-of-science',
      platform: 'Web of Science',
      sort_order: 5,
      db_icon: @json(asset('assets/media/userSocialIcons/webofscience.jpeg')),
      logo_type: 'img',
      logo_value: @json(asset('assets/media/userSocialIcons/webofscience.jpeg')),
      placeholder: 'https://...'
    },
    {
      key: 'researchgate',
      platform: 'ResearchGate',
      sort_order: 6,
      db_icon: @json(asset('assets/media/userSocialIcons/researchgate.jpeg')),
      logo_type: 'img',
      logo_value: @json(asset('assets/media/userSocialIcons/researchgate.jpeg')),
      placeholder: 'https://...'
    }
  ];

  const inlineLoader = document.getElementById('inlineLoader');
  const tbody = document.getElementById('socTbody');
  const resultsInfo = document.getElementById('resultsInfo');
  const btnSaveAll = document.getElementById('btnSaveAll');
  const btnReloadRows = document.getElementById('btnReloadRows');

  const binPerPageSel = document.getElementById('binPerPage');
  const binSearchInput = document.getElementById('binSearchInput');
  const binReset = document.getElementById('binReset');
  const binTbody = document.getElementById('binTbody');
  const binEmptyEl = document.getElementById('binEmpty');
  const binPager = document.getElementById('binPager');
  const binResultsInfo = document.getElementById('binResultsInfo');
  const btnEmptyBin = document.getElementById('btnEmptyBin');

  function showInlineLoading(show){
    if(!inlineLoader) return;
    inlineLoader.classList.toggle('show', !!show);
  }

  function authHeaders(extra = {}){ return Object.assign({ 'Authorization': 'Bearer ' + token }, extra); }

  function escapeHtml(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
  }

  function debounce(fn, ms=350){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; }

  function normalizeLink(src){
    const s = (src ?? '').toString().trim();
    if(!s) return '';
    return s;
  }

  function setButtonLoading(button, loading){
    if(!button) return;
    button.disabled = !!loading;
    button.classList.toggle('btn-loading', !!loading);
  }

  const toastOk = new bootstrap.Toast(document.getElementById('toastSuccess'));
  const toastErr = new bootstrap.Toast(document.getElementById('toastError'));
  const okTxt = document.getElementById('toastSuccessText');
  const errTxt = document.getElementById('toastErrorText');
  const ok = m => { okTxt.textContent = m || 'Done'; toastOk.show(); };
  const err = m => { errTxt.textContent = m || 'Something went wrong'; toastErr.show(); };

  const ACTOR = { id:null, uuid:'', role:'', department_id:null };
  let canWrite = true;
  let binLoaded = false;
  let currentPresetRecords = {};

  const binState = {
    items: [],
    q: '',
    perPage: 10,
    page: 1,
    total: 0,
    totalPages: 1,
  };

  function computePermissions(){
    btnSaveAll.disabled = !canWrite;
    btnReloadRows.disabled = false;
    if(btnEmptyBin) btnEmptyBin.disabled = !canWrite;
    document.querySelectorAll('.preset-link-input').forEach(inp => inp.disabled = !canWrite);
  }

  async function fetchMe(){
    const res = await fetch('/api/users/me', { headers: authHeaders() });
    const js = await res.json().catch(()=>({}));
    if (!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load current user');
    if (!js.data || !js.data.uuid) throw new Error('Current user UUID missing from /api/users/me');
    ACTOR.id = js.data.id || null;
    ACTOR.uuid = js.data.uuid;
    ACTOR.role = (js.data.role || '').toLowerCase();
    ACTOR.department_id = js.data.department_id || null;
    computePermissions();
  }

  function renderPagerGeneric(pagerEl, page, totalPages){
    if(!pagerEl) return;
    const item=(p,label,dis=false,act=false)=>{
      if(dis) return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
      if(act) return `<li class="page-item active"><span class="page-link">${label}</span></li>`;
      return `<li class="page-item"><a class="page-link" href="#" data-page="${p}">${label}</a></li>`;
    };

    let html='';
    html += item(Math.max(1,page-1),'Previous',page<=1);
    const st=Math.max(1,page-2), en=Math.min(totalPages,page+2);
    for(let p=st;p<=en;p++) html += item(p,p,false,p===page);
    html += item(Math.min(totalPages,page+1),'Next',page>=totalPages);
    pagerEl.innerHTML = html;
  }

  function renderInfo(total, shown, el, per, page){
    if(!el) return;
    if(!total || !shown){ el.textContent = `0 of ${total||0}`; return; }
    const from = (page-1)*per+1;
    const to = (page-1)*per+shown;
    el.textContent = `Showing ${from} to ${to} of ${total} entries`;
  }

  function normalizePlatform(str){
    return (str || '')
      .toString()
      .toLowerCase()
      .replace(/&/g, 'and')
      .replace(/[^a-z0-9]+/g, '');
  }

  function getPresetKeyFromRecord(rec){
    if(!rec || typeof rec !== 'object') return null;

    const metaKey = rec.metadata && typeof rec.metadata === 'object' ? (rec.metadata.preset_key || '') : '';
    if(metaKey && PRESET_SOCIALS.some(x => x.key === metaKey)) return metaKey;

    const normalized = normalizePlatform(rec.platform || '');
    const aliases = {
      'linkedin': 'linkedin',
      'linkedinprofile': 'linkedin',
      'linkedinprofileurl': 'linkedin',
      'linked in': 'linkedin',
      'vidyanportal': 'vidyan-portal',
      'vidyan': 'vidyan-portal',
      'scopus': 'scopus',
      'googlescholar': 'google-scholar',
      'scholargoogle': 'google-scholar',
      'webofscience': 'web-of-science',
      'researchgate': 'researchgate'
    };

    if(aliases[normalized]) return aliases[normalized];

    const presetBySort = PRESET_SOCIALS.find(x => Number(x.sort_order) === Number(rec.sort_order));
    if(presetBySort) return presetBySort.key;

    return null;
  }

  function mapPresetRecords(items){
    const map = {};
    (items || []).forEach(rec => {
      const key = getPresetKeyFromRecord(rec);
      if(!key) return;
      if(!map[key]) map[key] = rec;
    });
    return map;
  }

  function getLogoHtml(item){
  const type = (item.logo_type || '').toString().trim().toLowerCase();

  if(type === 'fa'){
    return `<i class="${escapeHtml(item.logo_value || '')}"></i>`;
  }

  if(type === 'img'){
    return `<img src="${escapeHtml(item.logo_value || '')}" alt="${escapeHtml(item.platform || 'Logo')}">`;
  }

  if(type === 'svg'){
    return item.logo_value || '';
  }

  return `<span class="tag-logo">${escapeHtml(item.logo_value || '')}</span>`;
}

  function presetStatusBadge(record){
    const hasLink = !!String(record?.link || '').trim();
    return hasLink
      ? `<span class="badge badge-soft-success">Saved</span>`
      : `<span class="badge badge-soft-muted">Pending</span>`;
  }

  function renderPresetRows(){
    tbody.innerHTML = PRESET_SOCIALS.map(item => {
      const record = currentPresetRecords[item.key] || null;
      const linkVal = normalizeLink(record?.link || '');

      return `
        <tr data-preset-key="${escapeHtml(item.key)}" data-uuid="${escapeHtml(record?.uuid || '')}">
          <td>
            <div class="icon-cell">
              <div class="icon-badge">${getLogoHtml(item)}</div>
              <div class="icon-meta">
                <div class="fw-semibold">${escapeHtml(item.platform)}</div>
                <div class="muted">Locked platform row</div>
              </div>
            </div>
          </td>
          <td>
            <div class="fw-semibold">${escapeHtml(item.platform)}</div>
            <div class="small text-muted">Predefined</div>
          </td>
          <td>
            <input
              type="url"
              class="form-control preset-link-input"
              placeholder="${escapeHtml(item.placeholder)}"
              value="${escapeHtml(linkVal)}"
              ${canWrite ? '' : 'disabled'}
            >
          </td>
          <td>
            <span class="sort-pill">${escapeHtml(String(item.sort_order))}</span>
          </td>
          <td>${presetStatusBadge(record)}</td>
        </tr>
      `;
    }).join('');

    resultsInfo.textContent = `${PRESET_SOCIALS.length} fixed rows`;
  }

  async function loadSocial(showLoader=true){
    try{
      if(showLoader) showInlineLoading(true);
      const res = await fetch(`/api/users/${encodeURIComponent(ACTOR.uuid)}/social`, { headers: authHeaders() });
      const js = await res.json().catch(()=>({}));
      if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load social links');

      const items = Array.isArray(js.data) ? js.data : [];
      currentPresetRecords = mapPresetRecords(items);
      renderPresetRows();
      computePermissions();
    }catch(e){
      err(e.message);
      console.error(e);
    }finally{
      if(showLoader) showInlineLoading(false);
    }
  }

  function iconCell(iconVal, platformVal, linkVal){
    const ic = (iconVal || '').toString().trim();
    const plat = (platformVal || '').toString().trim();
    const href = normalizeLink(linkVal);

    const isFA = /^fa[srlbd]?\s|^fa-/.test(ic) || ic.includes('fa-');
    if(isFA){
      return `
        <div class="icon-cell">
          <div class="icon-badge"><i class="${escapeHtml(ic || 'fa-solid fa-link')}"></i></div>
          <div class="icon-meta">
            <div class="fw-semibold">${escapeHtml(plat || '—')}</div>
            ${href ? `<a href="${escapeHtml(href)}" target="_blank" rel="noopener">Open link</a>` : `<span class="muted">—</span>`}
          </div>
        </div>`;
    }

    const tag = (plat || 'SM').slice(0,2).toUpperCase();
    return `
      <div class="icon-cell">
        <div class="icon-badge"><span class="tag-logo">${escapeHtml(tag)}</span></div>
        <div class="icon-meta">
          <div class="fw-semibold">${escapeHtml(plat || '—')}</div>
          ${href ? `<a href="${escapeHtml(href)}" target="_blank" rel="noopener">Open link</a>` : `<span class="muted">—</span>`}
        </div>
      </div>`;
  }

  function applyBinFilters(arr){
    let out = arr.slice();
    const q = (binState.q||'').toLowerCase().trim();
    if(q){
      out = out.filter(r=>{
        const meta = (r.metadata && typeof r.metadata === 'object') ? JSON.stringify(r.metadata) : (r.metadata||'');
        const hay = [r.platform, r.link, r.icon, meta]
          .map(x=>(x||'').toString().toLowerCase()).join(' | ');
        return hay.includes(q);
      });
    }
    return out;
  }

  function renderBinTable(rows){
    if(!binTbody) return;

    if(!rows.length){
      binTbody.innerHTML = '';
      return;
    }

    binTbody.innerHTML = rows.map(r=>{
      const plat = (r.platform || '').toString();
      const lnk = (r.link || '').toString();
      const delAt = r.deleted_at ? String(r.deleted_at) : '—';

      const act = `
        <div class="dropdown text-end" data-bs-display="static">
          <button type="button" class="btn btn-light btn-sm dd-toggle"
                  data-bs-toggle="dropdown" data-bs-auto-close="outside"
                  data-bs-boundary="viewport" aria-expanded="false">
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <button type="button" class="dropdown-item" data-bin-action="restore" ${canWrite ? '' : 'disabled'}>
                <i class="fa fa-rotate-left"></i> Restore
              </button>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <button type="button" class="dropdown-item text-danger" data-bin-action="force" ${canWrite ? '' : 'disabled'}>
                <i class="fa fa-trash"></i> Delete Permanently
              </button>
            </li>
          </ul>
        </div>`;

      return `
        <tr data-uuid="${escapeHtml(r.uuid)}">
          <td>${iconCell(r.icon, plat, lnk)}</td>
          <td>${escapeHtml(plat || '—')}</td>
          <td>
            ${lnk ? `<a href="${escapeHtml(normalizeLink(lnk))}" target="_blank" rel="noopener">${escapeHtml(lnk)}</a>`
                 : `<span class="text-muted">—</span>`}
          </td>
          <td>${escapeHtml(delAt)}</td>
          <td class="text-end">${act}</td>
        </tr>`;
    }).join('');
  }

  function renderBinAll(){
    const filtered = applyBinFilters(binState.items);
    const total = filtered.length;
    const per = binState.perPage || 10;
    const pages = Math.max(1, Math.ceil(total / per));
    binState.total = total;
    binState.totalPages = pages;
    if (binState.page > pages) binState.page = pages;

    const start = (binState.page - 1) * per;
    const rows = filtered.slice(start, start + per);

    renderBinTable(rows);
    renderPagerGeneric(binPager, binState.page, binState.totalPages);
    renderInfo(total, rows.length, binResultsInfo, per, binState.page);
    binEmptyEl.style.display = total === 0 ? '' : 'none';
  }

  async function loadBin(showLoader=true){
    try{
      if(showLoader) showInlineLoading(true);
      const res = await fetch(`/api/users/${encodeURIComponent(ACTOR.uuid)}/social/deleted`, { headers: authHeaders() });
      const js = await res.json().catch(()=>({}));
      if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load deleted social links');
      binState.items = Array.isArray(js.data) ? js.data : [];
      binState.page = 1;
      binLoaded = true;
      renderBinAll();
    }catch(e){
      err(e.message);
      console.error(e);
    }finally{
      if(showLoader) showInlineLoading(false);
    }
  }

  async function saveAllPresetRows(){
    if(!canWrite){
      err('You do not have permission for this action');
      return;
    }

    const rows = Array.from(document.querySelectorAll('#socTbody tr[data-preset-key]'));
    if(!rows.length){
      err('No rows available to save');
      return;
    }

    let created = 0;
    let updated = 0;
    let removed = 0;

    setButtonLoading(btnSaveAll, true);
    showInlineLoading(true);

    try{
      for(const row of rows){
        const key = row.dataset.presetKey;
        const preset = PRESET_SOCIALS.find(x => x.key === key);
        if(!preset) continue;

        const input = row.querySelector('.preset-link-input');
        const existing = currentPresetRecords[key] || null;
        const link = (input?.value || '').trim();

        if(!link){
          if(existing?.uuid){
            const delRes = await fetch(`/api/users/${encodeURIComponent(ACTOR.uuid)}/social/${encodeURIComponent(existing.uuid)}`, {
              method:'DELETE',
              headers: authHeaders()
            });
            const delJs = await delRes.json().catch(()=>({}));
            if(!delRes.ok || delJs.success === false){
              throw new Error(delJs.error || delJs.message || `Failed to remove ${preset.platform}`);
            }
            removed++;
          }
          continue;
        }

        const payload = {
          platform: preset.platform,
          icon: preset.db_icon,
          link: link,
          sort_order: preset.sort_order,
          active: true,
          metadata: {
            preset_key: preset.key,
            fixed_sequence: true
          }
        };

        if(existing?.uuid){
          const updRes = await fetch(`/api/users/${encodeURIComponent(ACTOR.uuid)}/social/${encodeURIComponent(existing.uuid)}`, {
            method:'PUT',
            headers: authHeaders({ 'Content-Type':'application/json' }),
            body: JSON.stringify(payload)
          });
          const updJs = await updRes.json().catch(()=>({}));
          if(!updRes.ok || updJs.success === false){
            throw new Error(updJs.error || updJs.message || `Failed to update ${preset.platform}`);
          }
          updated++;
        }else{
          const crtRes = await fetch(`/api/users/${encodeURIComponent(ACTOR.uuid)}/social`, {
            method:'POST',
            headers: authHeaders({ 'Content-Type':'application/json' }),
            body: JSON.stringify(payload)
          });
          const crtJs = await crtRes.json().catch(()=>({}));
          if(!crtRes.ok || crtJs.success === false){
            throw new Error(crtJs.error || crtJs.message || `Failed to create ${preset.platform}`);
          }
          created++;
        }
      }

      await loadSocial(false);
      if(binLoaded) await loadBin(false);

      const parts = [];
      if(created) parts.push(`${created} created`);
      if(updated) parts.push(`${updated} updated`);
      if(removed) parts.push(`${removed} removed`);

      ok(parts.length ? `Saved successfully (${parts.join(', ')})` : 'No changes to save');
    }catch(ex){
      err(ex.message);
      console.error(ex);
    }finally{
      setButtonLoading(btnSaveAll, false);
      showInlineLoading(false);
    }
  }

  btnSaveAll?.addEventListener('click', saveAllPresetRows);
  btnReloadRows?.addEventListener('click', () => loadSocial(true));

  const onBinSearch = debounce(()=>{
    if(!binLoaded) return;
    binState.q = binSearchInput.value.trim();
    binState.page = 1;
    renderBinAll();
  }, 320);
  binSearchInput.addEventListener('input', onBinSearch);

  binPerPageSel.addEventListener('change', ()=>{
    if(!binLoaded) return;
    binState.perPage = parseInt(binPerPageSel.value, 10) || 10;
    binState.page = 1;
    renderBinAll();
  });

  binReset.addEventListener('click', ()=>{
    if(!binLoaded){
      binResultsInfo.textContent = '—';
      binPager.innerHTML = '';
      binEmptyEl.style.display = 'none';
      binTbody.innerHTML = `
        <tr>
          <td colspan="5" class="text-center text-muted" style="padding:38px;">
            Click the <b>Bin</b> tab to load deleted records.
          </td>
        </tr>`;
      return;
    }
    binState.q=''; binState.perPage=10; binState.page=1;
    binSearchInput.value=''; binPerPageSel.value='10';
    renderBinAll();
  });

  const binTabLink = document.querySelector('a[href="#pane-bin"][data-bs-toggle="tab"]');
  if (binTabLink) {
    binTabLink.addEventListener('shown.bs.tab', async () => {
      if (!binLoaded) await loadBin(true);
    });
  }

  document.addEventListener('click', e=>{
    const a = e.target.closest('#binPager a.page-link[data-page]');
    if(!a) return;
    e.preventDefault();
    if(!binLoaded) return;
    const p = parseInt(a.dataset.page, 10);
    if(Number.isNaN(p) || p===binState.page) return;
    binState.page = p;
    renderBinAll();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  document.addEventListener('click', async (e)=>{
    const btn = e.target.closest('#pane-bin button[data-bin-action]');
    if(!btn) return;

    if(!binLoaded){
      err('Bin not loaded yet. Click Bin tab once.');
      return;
    }

    const tr = btn.closest('tr');
    const uuid = tr?.dataset?.uuid;
    if(!uuid) return;

    const act = btn.dataset.binAction;

    const toggle = btn.closest('.dropdown')?.querySelector('.dd-toggle');
    if(toggle){ try{ bootstrap.Dropdown.getOrCreateInstance(toggle).hide(); }catch(_){} }

    if(!canWrite){
      err('You do not have permission for this action');
      return;
    }

    if(act === 'restore'){
      const conf = await Swal.fire({
        title:'Restore link?',
        text:'This will restore the record from Bin.',
        icon:'question',
        showCancelButton:true,
        confirmButtonText:'Restore'
      });
      if(!conf.isConfirmed) return;

      showInlineLoading(true);
      try{
        const res = await fetch(`/api/users/${encodeURIComponent(ACTOR.uuid)}/social/${encodeURIComponent(uuid)}/restore`, {
          method:'POST',
          headers: authHeaders({ 'Content-Type':'application/json' })
        });
        const js = await res.json().catch(()=>({}));
        if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Restore failed');
        ok('Restored');
        await loadSocial(false);
        await loadBin(false);
      }catch(ex){ err(ex.message); }
      finally{ showInlineLoading(false); }
      return;
    }

    if(act === 'force'){
      const conf = await Swal.fire({
        title:'Delete permanently?',
        text:'This cannot be undone.',
        icon:'warning',
        showCancelButton:true,
        confirmButtonText:'Delete Permanently',
        confirmButtonColor:'#ef4444'
      });
      if(!conf.isConfirmed) return;

      showInlineLoading(true);
      try{
        const res = await fetch(`/api/users/${encodeURIComponent(ACTOR.uuid)}/social/${encodeURIComponent(uuid)}/force`, {
          method:'DELETE',
          headers: authHeaders()
        });
        const js = await res.json().catch(()=>({}));
        if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Permanent delete failed');
        ok('Deleted permanently');
        await loadBin(false);
        await loadSocial(false);
      }catch(ex){ err(ex.message); }
      finally{ showInlineLoading(false); }
    }
  });

  btnEmptyBin?.addEventListener('click', async ()=>{
    if(!canWrite){
      err('You do not have permission for this action');
      return;
    }
    if(!binLoaded){
      err('Bin not loaded yet. Click Bin tab once.');
      return;
    }

    const conf = await Swal.fire({
      title:'Empty Bin?',
      text:'This will permanently delete all deleted social links.',
      icon:'warning',
      showCancelButton:true,
      confirmButtonText:'Empty Bin',
      confirmButtonColor:'#ef4444'
    });
    if(!conf.isConfirmed) return;

    showInlineLoading(true);
    try{
      const res = await fetch(`/api/users/${encodeURIComponent(ACTOR.uuid)}/social/deleted/force`, {
        method:'DELETE',
        headers: authHeaders()
      });
      const js = await res.json().catch(()=>({}));
      if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Empty Bin failed');
      ok('Bin emptied');
      await loadBin(false);
      await loadSocial(false);
    }catch(ex){
      err(ex.message);
    }finally{
      showInlineLoading(false);
    }
  });

  (async ()=>{
    showInlineLoading(true);
    try{
      await fetchMe();
      await loadSocial(false);
    }catch(ex){
      err(ex.message);
    }finally{
      showInlineLoading(false);
    }
  })();
});
</script>
@endpush