{{-- resources/views/modules/user/manageSocialMedia.blade.php --}}

@section('title','Social Media')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* Dropdowns inside table */
.table-wrap .dropdown{position:relative}
.dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
.dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:220px;z-index:1085}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color) !important}

/* ✅ FIX: allow horizontal scroll on small screens, keep dropdowns visible vertically */
.table-responsive{
  overflow-x:auto !important;
  overflow-y:visible !important;
  -webkit-overflow-scrolling:touch;
}
.card-body{overflow:visible !important}

/* optional: makes tables easier to scroll on mobile */
.table{min-width:1120px}

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

.badge-soft-primary{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  color:var(--primary-color)
}
.badge-soft-muted{
  background:color-mix(in oklab, var(--muted-color) 12%, transparent);
  color:var(--muted-color)
}

/* ✅ centered loader overlay */
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

/* ✅ Tabs style */
.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface)
}
.tab-content,.tab-pane{overflow:visible}

/* Icon preview (first col) */
.icon-cell{display:flex;align-items:center;gap:10px}
.icon-badge{
  width:44px;height:44px;border-radius:12px;
  border:1px solid var(--line-soft);
  display:flex;align-items:center;justify-content:center;
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
  flex:0 0 44px;
}
.icon-badge i{font-size:18px}
.icon-badge img{width:26px;height:26px;object-fit:contain;display:block}
.icon-meta{display:flex;flex-direction:column;gap:2px}
.icon-meta .muted{font-size:12px;color:var(--muted-color)}
.icon-meta a{font-size:12.5px;text-decoration:none}

@media (max-width: 768px){
  .soc-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .soc-toolbar .position-relative{min-width:100% !important}
  .toolbar-buttons{display:flex;gap:8px;flex-wrap:wrap}
  .toolbar-buttons .btn{flex:1;min-width:140px}
  .table{min-width:1080px}
}
</style>
@endpush

@section('content')
<div class="crs-wrap">

  <div id="inlineLoader" class="inline-loader">
    <div class="loader-card">
      <div class="spinner-border" role="status" aria-hidden="true"></div>
      <p class="small text-muted mb-0">Loading…</p>
    </div>
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
       Active
       ========================= --}}
    <div class="tab-pane fade show active" id="pane-active" role="tabpanel">

      <div class="row align-items-center g-2 mb-3 soc-toolbar panel">
        <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per Page</label>
            <select id="perPage" class="form-select" style="width:96px;">
              <option>10</option><option>20</option><option>50</option><option>100</option>
            </select>
          </div>

          <div class="position-relative" style="min-width:280px;">
            <input id="searchInput" type="search" class="form-control ps-5" placeholder="Search platform, link, icon, metadata…">
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>

          <button id="btnReset" class="btn btn-light">
            <i class="fa fa-rotate-left me-1"></i>Reset
          </button>
        </div>

        <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
          <div class="toolbar-buttons" id="writeControls" style="display:none;">
            <button type="button" class="btn btn-primary" id="btnAdd">
              <i class="fa fa-plus me-1"></i> Add Social Link
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
                  <th style="width:120px;">Active</th>
                  <th style="width:160px;">Sort</th>
                  <th style="width:110px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="socTbody">
                <tr>
                  <td colspan="6" class="text-center text-muted" style="padding:38px;">Loading…</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="empty" class="empty p-4 text-center" style="display:none;">
            <i class="fa-brands fa-hubspot mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No social links found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo">—</div>
            <nav><ul id="pager" class="pagination mb-0"></ul></nav>
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

{{-- Add/Edit/View Modal --}}
<div class="modal fade" id="socModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" id="socForm">
      <div class="modal-header">
        <h5 class="modal-title" id="socModalTitle">Add Social Link</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="socUuid" />

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Platform <span class="text-danger">*</span></label>
            <input class="form-control" id="platform" required maxlength="100" placeholder="e.g., LinkedIn / GitHub / X">
          </div>

          <div class="col-md-6">
            <label class="form-label">Icon (FA class OR image path)</label>
            <input class="form-control" id="icon" maxlength="100" placeholder="e.g., fa-brands fa-linkedin OR /icons/linkedin.svg">
            <div class="form-text">If it looks like an image path/url, we’ll preview as image.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Link <span class="text-danger">*</span></label>
            <input class="form-control" id="link" required maxlength="500" placeholder="https://...">
          </div>

          <div class="col-md-4">
            <label class="form-label">Sort Order</label>
            <input type="number" class="form-control" id="sort_order" placeholder="0">
          </div>

          <div class="col-md-4">
            <label class="form-label">Active</label>
            <select class="form-select" id="active">
              <option value="1">Yes</option>
              <option value="0">No</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Preview</label>
            <div class="d-flex align-items-center gap-2">
              <div class="icon-badge" style="width:46px;height:46px;">
                <i id="previewIconI" class="fa-solid fa-link"></i>
                <img id="previewIconImg" src="" alt="icon" style="display:none;">
              </div>
              <div class="small text-muted" id="previewText">—</div>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Metadata (JSON)</label>
            <textarea class="form-control" id="metadata" rows="4" placeholder='{"username":"...","verified":true}'></textarea>
            <div class="form-text">Optional. Keep it valid JSON. Empty = null.</div>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="saveBtn">
          <i class="fa fa-floppy-disk me-1"></i> Save
        </button>
      </div>
    </form>
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
// ✅ dropdown fix (safe)
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
  // ✅ prevent double-init (also prevents double-submit / double listeners)
  if (window.__SOCIAL_MANAGE_INIT__) return;
  window.__SOCIAL_MANAGE_INIT__ = true;

  const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
  if (!token) { window.location.href = '/'; return; }

  const inlineLoader = document.getElementById('inlineLoader');
  function showInlineLoading(show){
    if(!inlineLoader) return;
    inlineLoader.classList.toggle('show', !!show);
  }

  function authHeaders(extra = {}){ return Object.assign({ 'Authorization': 'Bearer ' + token }, extra); }

  function escapeHtml(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
  }
  function debounce(fn, ms=350){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; }

  function parseJsonOrThrow(txt){
    const s=(txt||'').trim(); if(!s) return null;
    try{
      const obj=JSON.parse(s);
      if(obj===null) return null;
      if(typeof obj!=='object') throw new Error('Metadata must be a JSON object/array');
      return obj;
    }catch(e){ throw new Error('Metadata JSON invalid: '+e.message); }
  }

  function normalizeLink(src){
    const s = (src ?? '').toString().trim();
    if(!s) return '';
    if(/^https?:\/\//i.test(s)) return s;
    if(s.startsWith('//')) return s;
    if(s.startsWith('/')) return s;
    return '/' + s;
  }

  function looksLikeImagePath(v){
    const s = (v||'').toString().trim();
    if(!s) return false;
    if(/^https?:\/\//i.test(s) || s.startsWith('/') || s.startsWith('//')){
      return /\.(png|jpg|jpeg|webp|gif|svg)(\?.*)?$/i.test(s);
    }
    return /\.(png|jpg|jpeg|webp|gif|svg)(\?.*)?$/i.test(s);
  }

  const toastOk = new bootstrap.Toast(document.getElementById('toastSuccess'));
  const toastErr = new bootstrap.Toast(document.getElementById('toastError'));
  const okTxt = document.getElementById('toastSuccessText');
  const errTxt = document.getElementById('toastErrorText');
  const ok = m => { okTxt.textContent = m || 'Done'; toastOk.show(); };
  const err = m => { errTxt.textContent = m || 'Something went wrong'; toastErr.show(); };

  const perPageSel = document.getElementById('perPage');
  const searchInput = document.getElementById('searchInput');
  const btnReset = document.getElementById('btnReset');
  const writeControls = document.getElementById('writeControls');
  const btnAdd = document.getElementById('btnAdd');

  const tbody = document.getElementById('socTbody');
  const emptyEl = document.getElementById('empty');
  const pager = document.getElementById('pager');
  const resultsInfo = document.getElementById('resultsInfo');

  // Bin elements
  const binPerPageSel = document.getElementById('binPerPage');
  const binSearchInput = document.getElementById('binSearchInput');
  const binReset = document.getElementById('binReset');
  const binTbody = document.getElementById('binTbody');
  const binEmptyEl = document.getElementById('binEmpty');
  const binPager = document.getElementById('binPager');
  const binResultsInfo = document.getElementById('binResultsInfo');
  const btnEmptyBin = document.getElementById('btnEmptyBin');

  const socModalEl = document.getElementById('socModal');
  const socModal = new bootstrap.Modal(socModalEl);
  const socForm = document.getElementById('socForm');
  const socModalTitle = document.getElementById('socModalTitle');
  const saveBtn = document.getElementById('saveBtn');

  function cleanupModalBackdrops(){
    document.querySelectorAll('.modal-backdrop').forEach(b=>b.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('padding-right');
  }
  ['hidden.bs.modal','hide.bs.modal'].forEach(ev=>{
    socModalEl.addEventListener(ev, cleanupModalBackdrops);
  });
  window.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') setTimeout(cleanupModalBackdrops, 0); });
  window.addEventListener('beforeunload', cleanupModalBackdrops);

  // Form fields
  const socUuid = document.getElementById('socUuid');
  const platform = document.getElementById('platform');
  const icon = document.getElementById('icon');
  const link = document.getElementById('link');
  const sort_order = document.getElementById('sort_order');
  const active = document.getElementById('active');
  const metadata = document.getElementById('metadata');

  // preview
  const previewIconI = document.getElementById('previewIconI');
  const previewIconImg = document.getElementById('previewIconImg');
  const previewText = document.getElementById('previewText');

  const ACTOR = { id:null, uuid:'', role:'' };
  let canWrite = true;
  let binLoaded = false;

  function computePermissions(){
    // you can adjust: canWrite = !['student'].includes((ACTOR.role||'').toLowerCase());
    writeControls.style.display = canWrite ? 'flex' : 'none';
    if(btnEmptyBin) btnEmptyBin.disabled = !canWrite;
  }

  function setButtonLoading(button, loading){
    if(!button) return;
    button.disabled = !!loading;
    button.classList.toggle('btn-loading', !!loading);
  }

  const state = {
    items: [],
    q: '',
    perPage: 10,
    page: 1,
    total: 0,
    totalPages: 1,
  };

  const binState = {
    items: [],
    q: '',
    perPage: 10,
    page: 1,
    total: 0,
    totalPages: 1,
  };

  async function fetchMe(){
    const res = await fetch('/api/users/me', { headers: authHeaders() });
    const js = await res.json().catch(()=>({}));
    if (!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load current user');
    if (!js.data || !js.data.uuid) throw new Error('Current user UUID missing from /api/users/me');
    ACTOR.id = js.data.id || null;
    ACTOR.uuid = js.data.uuid;
    ACTOR.role = (js.data.role || '').toLowerCase();
    computePermissions();
  }

  function renderInfo(total, shown, el, per, page){
    if(!el) return;
    if(!total || !shown){ el.textContent = `0 of ${total||0}`; return; }
    const from = (page-1)*per+1;
    const to = (page-1)*per+shown;
    el.textContent = `Showing ${from} to ${to} of ${total} entries`;
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

  function activeBadge(v){
    const on = (v === 1 || v === true || v === '1');
    if(on) return `<span class="badge badge-soft-primary">Yes</span>`;
    return `<span class="badge badge-soft-muted">No</span>`;
  }

  function iconCell(iconVal, platformVal, linkVal){
    const ic = (iconVal || '').toString().trim();
    const plat = (platformVal || '').toString().trim();
    const href = normalizeLink(linkVal);

    if(looksLikeImagePath(ic)){
      const src = normalizeLink(ic);
      return `
        <div class="icon-cell">
          <div class="icon-badge"><img src="${escapeHtml(src)}" alt="icon" loading="lazy"></div>
          <div class="icon-meta">
            <div class="fw-semibold">${escapeHtml(plat || '—')}</div>
            ${href ? `<a href="${escapeHtml(href)}" target="_blank" rel="noopener">Open link</a>` : `<span class="muted">—</span>`}
          </div>
        </div>`;
    }

    // FontAwesome class
    const faClass = ic ? ic : 'fa-solid fa-link';
    return `
      <div class="icon-cell">
        <div class="icon-badge"><i class="${escapeHtml(faClass)}"></i></div>
        <div class="icon-meta">
          <div class="fw-semibold">${escapeHtml(plat || '—')}</div>
          ${href ? `<a href="${escapeHtml(href)}" target="_blank" rel="noopener">Open link</a>` : `<span class="muted">—</span>`}
          <div class="muted">${escapeHtml(ic || 'no icon')}</div>
        </div>
      </div>`;
  }

  function applyFilters(arr){
    let out = arr.slice();
    const q = (state.q||'').toLowerCase().trim();
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

  function sortRows(arr){
    // keep server order (sort_order asc, id desc); do stable sort by sort_order then id desc
    return arr.slice().sort((a,b)=>{
      const aso = Number(a.sort_order ?? 0);
      const bso = Number(b.sort_order ?? 0);
      if(aso !== bso) return aso - bso;
      const aid = Number(a.id ?? 0);
      const bid = Number(b.id ?? 0);
      return bid - aid;
    });
  }

  function renderTable(rows){
    if(!tbody) return;

    if(!rows.length){
      tbody.innerHTML = '';
      return;
    }

    tbody.innerHTML = rows.map(r=>{
      const plat = (r.platform || '').toString();
      const lnk = (r.link || '').toString();
      const act = `
        <div class="dropdown text-end" data-bs-display="static">
          <button type="button" class="btn btn-light btn-sm dd-toggle"
                  data-bs-toggle="dropdown" data-bs-auto-close="outside"
                  data-bs-boundary="viewport" aria-expanded="false">
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>
            <li><button type="button" class="dropdown-item" data-action="edit"><i class="fa fa-pen-to-square"></i> Edit</button></li>
            <li><hr class="dropdown-divider"></li>
            <li><button type="button" class="dropdown-item text-danger" data-action="delete"><i class="fa fa-trash"></i> Delete</button></li>
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
          <td>${activeBadge(r.active)}</td>
          <td>${escapeHtml(String(r.sort_order ?? 0))}</td>
          <td class="text-end">${act}</td>
        </tr>`;
    }).join('');
  }

  function renderAll(){
    const filtered = sortRows(applyFilters(state.items));
    const total = filtered.length;
    const per = state.perPage || 10;
    const pages = Math.max(1, Math.ceil(total / per));
    state.total = total;
    state.totalPages = pages;
    if (state.page > pages) state.page = pages;

    const start = (state.page - 1) * per;
    const rows = filtered.slice(start, start + per);

    renderTable(rows);
    renderPagerGeneric(pager, state.page, state.totalPages);
    renderInfo(total, rows.length, resultsInfo, per, state.page);
    emptyEl.style.display = total === 0 ? '' : 'none';
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

  async function loadSocial(showLoader=true){
    try{
      if(showLoader) showInlineLoading(true);
      const res = await fetch(`/api/users/${encodeURIComponent(ACTOR.uuid)}/social`, { headers: authHeaders() });
      const js = await res.json().catch(()=>({}));
      if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load social links');
      state.items = Array.isArray(js.data) ? js.data : [];
      state.page = 1;
      renderAll();
    }catch(e){
      err(e.message); console.error(e);
    }finally{
      if(showLoader) showInlineLoading(false);
    }
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
      err(e.message); console.error(e);
    }finally{
      if(showLoader) showInlineLoading(false);
    }
  }

  async function fetchOne(uuid){
    // no show endpoint in controller; we can reuse index list item
    const hit = state.items.find(x => String(x.uuid) === String(uuid)) || binState.items.find(x => String(x.uuid) === String(uuid));
    return hit || null;
  }

  function resetForm(){
    socForm.reset();
    socUuid.value = '';
    metadata.value = '';
    active.value = '1';
    updatePreview();
  }

  function setFormReadonly(on){
    Array.from(socForm.querySelectorAll('input,select,textarea')).forEach(el=>{
      if(el.id==='socUuid') return;
      if(on){
        if(el.tagName==='SELECT') el.disabled=true;
        else el.readOnly=true;
      }else{
        el.disabled=false;
        el.readOnly=false;
      }
    });
  }

  function fillForm(r){
    socUuid.value = r.uuid || '';
    platform.value = r.platform || '';
    icon.value = r.icon || '';
    link.value = r.link || '';
    sort_order.value = (r.sort_order ?? 0);
    active.value = (r.active === 0 || r.active === false || r.active === '0') ? '0' : '1';

    if(r.metadata && typeof r.metadata === 'object'){
      try{ metadata.value = JSON.stringify(r.metadata, null, 2); }catch(_){ metadata.value=''; }
    } else metadata.value = '';

    updatePreview();
  }

  function updatePreview(){
    const ic = (icon.value || '').toString().trim();
    const plat = (platform.value || '').toString().trim();
    if(looksLikeImagePath(ic)){
      previewIconI.style.display = 'none';
      previewIconImg.style.display = '';
      previewIconImg.src = normalizeLink(ic);
      previewText.textContent = plat ? `Image icon for ${plat}` : 'Image icon';
      return;
    }
    previewIconImg.style.display = 'none';
    previewIconI.style.display = '';
    previewIconI.className = ic ? ic : 'fa-solid fa-link';
    previewText.textContent = plat ? `FA icon for ${plat}` : 'FA icon';
  }

  icon.addEventListener('input', debounce(updatePreview, 120));
  platform.addEventListener('input', debounce(updatePreview, 120));

  // ✅ Load Bin on first time tab click
  const binTabLink = document.querySelector('a[href="#pane-bin"][data-bs-toggle="tab"]');
  if (binTabLink) {
    binTabLink.addEventListener('shown.bs.tab', async () => {
      if (!binLoaded) await loadBin(true);
    });
  }

  // Active pager
  document.addEventListener('click', e=>{
    const a = e.target.closest('#pager a.page-link[data-page]');
    if(!a) return;
    e.preventDefault();
    const p = parseInt(a.dataset.page, 10);
    if(Number.isNaN(p) || p===state.page) return;
    state.page = p;
    renderAll();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // Bin pager
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

  const onSearch = debounce(()=>{
    state.q = searchInput.value.trim();
    state.page = 1;
    renderAll();
  }, 320);
  searchInput.addEventListener('input', onSearch);

  perPageSel.addEventListener('change', ()=>{
    state.perPage = parseInt(perPageSel.value, 10) || 10;
    state.page = 1;
    renderAll();
  });

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

  btnReset.addEventListener('click', ()=>{
    state.q=''; state.perPage=10; state.page=1;
    searchInput.value=''; perPageSel.value='10';
    renderAll();
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

  // Add
  btnAdd?.addEventListener('click', ()=>{
    if(!canWrite) return;
    resetForm();
    socModalTitle.textContent = 'Add Social Link';
    socForm.dataset.mode = 'edit';
    setFormReadonly(false);
    saveBtn.style.display = '';
    socModal.show();
  });

  // Actions (Active table)
  document.addEventListener('click', async (e)=>{
    const btn = e.target.closest('#pane-active button[data-action]');
    if(!btn) return;

    const tr = btn.closest('tr');
    const uuid = tr?.dataset?.uuid;
    if(!uuid) return;

    const act = btn.dataset.action;

    const toggle = btn.closest('.dropdown')?.querySelector('.dd-toggle');
    if(toggle){ try{ bootstrap.Dropdown.getOrCreateInstance(toggle).hide(); }catch(_){} }

    if(act === 'view' || act === 'edit'){
      showInlineLoading(true);
      try{
        const data = await fetchOne(uuid);
        if(!data) throw new Error('Record not found in current list');
        resetForm();
        fillForm(data);

        const viewOnly = (act==='view');
        socModalTitle.textContent = viewOnly ? 'View Social Link' : 'Edit Social Link';
        socForm.dataset.mode = viewOnly ? 'view' : 'edit';
        setFormReadonly(viewOnly);
        saveBtn.style.display = viewOnly ? 'none' : '';
        socModal.show();
      }catch(ex){ err(ex.message); }
      finally{ showInlineLoading(false); }
      return;
    }

    if(act === 'delete'){
      const conf = await Swal.fire({
        title:'Move to Bin?',
        text:'This will soft delete the record.',
        icon:'warning',
        showCancelButton:true,
        confirmButtonText:'Delete',
        confirmButtonColor:'#ef4444'
      });
      if(!conf.isConfirmed) return;

      showInlineLoading(true);
      try{
        const res = await fetch(`/api/users/${encodeURIComponent(ACTOR.uuid)}/social/${encodeURIComponent(uuid)}`, {
          method:'DELETE',
          headers: authHeaders()
        });
        const js = await res.json().catch(()=>({}));
        if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Delete failed');
        ok('Moved to Bin');
        await loadSocial(false);
        if(binLoaded) await loadBin(false);
      }catch(ex){ err(ex.message); }
      finally{ showInlineLoading(false); }
    }
  });

  // Actions (Bin table)
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

  // Empty Bin
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
    }
    finally{
      showInlineLoading(false);
    }
  });

  // ✅ Submit lock to prevent double entry
  let submitting = false;

  // Save (Add/Edit) - JSON (no file)
  socForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    if(socForm.dataset.mode === 'view') return;
    if(submitting) return; // ✅ prevents double submit

    if(!platform.value.trim()){ platform.focus(); return; }
    if(!link.value.trim()){ link.focus(); return; }

    let metaObj = null;
    try{ metaObj = parseJsonOrThrow(metadata.value); }
    catch(ex){ err(ex.message); metadata.focus(); return; }

    const isEdit = !!socUuid.value;
    const endpoint = isEdit
      ? `/api/users/${encodeURIComponent(ACTOR.uuid)}/social/${encodeURIComponent(socUuid.value)}`
      : `/api/users/${encodeURIComponent(ACTOR.uuid)}/social`;
    const method = isEdit ? 'PUT' : 'POST';

    const payload = {
      platform: platform.value.trim(),
      icon: (icon.value || '').trim() || null,
      link: link.value.trim(),
      sort_order: sort_order.value ? (parseInt(sort_order.value,10) || 0) : 0,
      active: active.value === '1',
    };
    if(metaObj !== null) payload.metadata = metaObj;

    submitting = true;
    try{
      setButtonLoading(saveBtn, true);
      showInlineLoading(true);

      const res = await fetch(endpoint, {
        method,
        headers: authHeaders({ 'Content-Type':'application/json' }),
        body: JSON.stringify(payload)
      });
      const js = await res.json().catch(()=>({}));

      if(!res.ok || js.success === false){
        let msg = js.error || js.message || 'Save failed';
        if(js.errors){
          const k = Object.keys(js.errors)[0];
          if(k && js.errors[k] && js.errors[k][0]) msg = js.errors[k][0];
        }
        throw new Error(msg);
      }

      socModal.hide();
      ok(isEdit ? 'Social link updated' : 'Social link created');
      await loadSocial(false);
      if(binLoaded) await loadBin(false);
    }catch(ex){
      err(ex.message);
    }finally{
      submitting = false;
      setButtonLoading(saveBtn, false);
      showInlineLoading(false);
      cleanupModalBackdrops();
    }
  });

  (async ()=>{
    showInlineLoading(true);
    try{
      await fetchMe();
      await loadSocial(false);
      // ✅ No bin load here
    }catch(ex){
      err(ex.message);
    }finally{
      showInlineLoading(false);
      cleanupModalBackdrops();
    }
  })();

});
</script>
@endpush
