{{-- resources/views/modules/user/manageJournals.blade.php --}}

@section('title','Journals')

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
.table{min-width:1180px}

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

.jrn-toolbar.panel{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-1);
  padding:12px 12px
}
.jrn-toolbar .form-select,
.jrn-toolbar .form-control{border-radius:12px}

/* ✅ Tabs style */
.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface)
}
.tab-content,.tab-pane{overflow:visible}

/* Image preview in table */
.img-cell{
  display:flex;align-items:center;gap:10px;
}
.img-thumb{
  width:44px;height:44px;border-radius:10px;
  border:1px solid var(--line-soft);
  overflow:hidden;flex:0 0 44px;
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
}
.img-thumb img{
  width:100%;height:100%;object-fit:cover;display:block;
}
.img-placeholder{
  width:44px;height:44px;border-radius:10px;
  border:1px dashed var(--line-soft);
  display:flex;align-items:center;justify-content:center;
  color:var(--muted-color);
  background:transparent;
}
.img-cell .img-meta{display:flex;flex-direction:column;gap:2px}
.img-cell .img-meta a{font-size:12.5px}
.img-cell .img-meta .muted{font-size:12px;color:var(--muted-color)}

@media (max-width: 768px){
  .jrn-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .jrn-toolbar .position-relative{min-width:100% !important}
  .toolbar-buttons{display:flex;gap:8px;flex-wrap:wrap}
  .toolbar-buttons .btn{flex:1;min-width:140px}
  .table{min-width:1100px}
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
        <i class="fa fa-book-open me-1"></i> Journals
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
       Active Journals
       ========================= --}}
    <div class="tab-pane fade show active" id="pane-active" role="tabpanel">

      <div class="row align-items-center g-2 mb-3 jrn-toolbar panel">
        <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per Page</label>
            <select id="perPage" class="form-select" style="width:96px;">
              <option>10</option><option>20</option><option>50</option><option>100</option>
            </select>
          </div>

          <div class="position-relative" style="min-width:280px;">
            <input id="searchInput" type="search" class="form-control ps-5" placeholder="Search by title, organization, year, description…">
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>

          <button id="btnFilter" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="fa fa-sliders me-1"></i>Filter
          </button>

          <button id="btnReset" class="btn btn-light">
            <i class="fa fa-rotate-left me-1"></i>Reset
          </button>
        </div>

        <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
          <div class="toolbar-buttons" id="writeControls" style="display:none;">
            <button type="button" class="btn btn-primary" id="btnAdd">
              <i class="fa fa-plus me-1"></i> Add Journal
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
                  <th style="width:240px;">Image</th>
                  <th style="width:110px;">Year</th>
                  <th style="width:320px;">Title</th>
                  <th style="width:320px;">Organization</th>
                  <th style="width:320px;">Description</th>
                  <th style="width:110px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="jrnTbody">
                <tr>
                  <td colspan="6" class="text-center text-muted" style="padding:38px;">Loading…</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="empty" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-book-open mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No journals found for current filters.</div>
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

      <div class="row align-items-center g-2 mb-3 jrn-toolbar panel">
        <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per Page</label>
            <select id="binPerPage" class="form-select" style="width:96px;">
              <option>10</option><option>20</option><option>50</option><option>100</option>
            </select>
          </div>

          <div class="position-relative" style="min-width:280px;">
            <input id="binSearchInput" type="search" class="form-control ps-5" placeholder="Search in deleted journals…">
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
                  <th style="width:240px;">Image</th>
                  <th style="width:110px;">Year</th>
                  <th style="width:320px;">Title</th>
                  <th style="width:320px;">Organization</th>
                  <th style="width:220px;">Deleted At</th>
                  <th style="width:140px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="binTbody">
                <tr>
                  <td colspan="6" class="text-center text-muted" style="padding:38px;">
                    Click the <b>Bin</b> tab to load deleted records.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="binEmpty" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No deleted journals in Bin.</div>
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

{{-- Filter Modal (Active tab only) --}}
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-sliders me-2"></i>Filter Journals</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label">Publication Year (From)</label>
            <input id="modal_year_from" type="number" class="form-control" placeholder="e.g., 2019" min="1900" max="{{ date('Y') }}">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Publication Year (To)</label>
            <input id="modal_year_to" type="number" class="form-control" placeholder="e.g., {{ date('Y') }}" min="1900" max="{{ date('Y') }}">
          </div>

          <div class="col-12">
            <label class="form-label">Organization (contains)</label>
            <input id="modal_org" class="form-control" placeholder="e.g., IEEE / Springer / Elsevier">
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="modal_sort" class="form-select">
              <option value="-publication_year">Year (Newest First)</option>
              <option value="publication_year">Year (Oldest First)</option>
              <option value="title">Title A-Z</option>
              <option value="-title">Title Z-A</option>
              <option value="-created_at">Newest Created</option>
              <option value="created_at">Oldest Created</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="btnApplyFilters" class="btn btn-primary">
          <i class="fa fa-check me-1"></i>Apply Filters
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Add/Edit/View Modal --}}
<div class="modal fade" id="jrnModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form class="modal-content" id="jrnForm">
      <div class="modal-header">
        <h5 class="modal-title" id="jrnModalTitle">Add Journal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="jrnUuid" />

        <div class="row g-3">

          <div class="col-md-8">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input class="form-control" id="title" required maxlength="255" placeholder="e.g., A Novel Approach to ...">
          </div>

          <div class="col-md-4">
            <label class="form-label">Publication Year</label>
            <input type="number" class="form-control" id="publication_year" min="1900" max="{{ date('Y') }}" placeholder="e.g., {{ date('Y') }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">Publication Organization</label>
            <input class="form-control" id="publication_organization" maxlength="255" placeholder="e.g., IEEE / Springer">
          </div>

          <div class="col-md-3">
            <label class="form-label">Sort Order</label>
            <input type="number" class="form-control" id="sort_order" placeholder="0">
          </div>

          <div class="col-md-3">
            <label class="form-label">Image Upload</label>
            <input type="file" class="form-control" id="image_file" accept="image/*">
            <div class="form-text">Optional. Upload image file.</div>
          </div>

          <div class="col-md-12" id="currentImageWrap" style="display:none;">
            <label class="form-label">Current Image</label>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <div class="img-thumb" style="width:54px;height:54px;">
                <img id="currentImagePreview" src="" alt="preview">
              </div>
              <a href="#" target="_blank" rel="noopener" id="currentImageLink" class="small">Open image</a>
              <span class="text-muted small" id="currentImageText"></span>
            </div>
          </div>

          <div class="col-md-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="description" rows="3" placeholder="Short description…"></textarea>
          </div>

          <div class="col-md-12">
            <label class="form-label">Metadata (JSON)</label>
            <textarea class="form-control" id="metadata" rows="4" placeholder='{"doi":"...","impact":"..."}'></textarea>
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
  function safeText(v){ const s=(v??'').toString().trim(); return s ? s : null; }

  function normalizeLink(src){
    const s = (src ?? '').toString().trim();
    if(!s) return '';
    if(/^https?:\/\//i.test(s)) return s;
    if(s.startsWith('//')) return s;
    if(s.startsWith('/')) return s;
    return '/' + s;
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
  const btnApplyFilters = document.getElementById('btnApplyFilters');
  const writeControls = document.getElementById('writeControls');
  const btnAdd = document.getElementById('btnAdd');

  const tbody = document.getElementById('jrnTbody');
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

  const filterModalEl = document.getElementById('filterModal');
  const filterModal = new bootstrap.Modal(filterModalEl);
  const modalYearFrom = document.getElementById('modal_year_from');
  const modalYearTo = document.getElementById('modal_year_to');
  const modalOrg = document.getElementById('modal_org');
  const modalSort = document.getElementById('modal_sort');

  const jrnModalEl = document.getElementById('jrnModal');
  const jrnModal = new bootstrap.Modal(jrnModalEl);
  const jrnForm = document.getElementById('jrnForm');
  const jrnModalTitle = document.getElementById('jrnModalTitle');
  const saveBtn = document.getElementById('saveBtn');

  function cleanupModalBackdrops(){
    document.querySelectorAll('.modal-backdrop').forEach(b=>b.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('padding-right');
  }
  ['hidden.bs.modal','hide.bs.modal'].forEach(ev=>{
    filterModalEl.addEventListener(ev, cleanupModalBackdrops);
    jrnModalEl.addEventListener(ev, cleanupModalBackdrops);
  });
  window.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') setTimeout(cleanupModalBackdrops, 0); });
  window.addEventListener('beforeunload', cleanupModalBackdrops);

  // Form fields
  const jrnUuid = document.getElementById('jrnUuid');
  const title = document.getElementById('title');
  const publication_year = document.getElementById('publication_year');
  const publication_organization = document.getElementById('publication_organization');
  const sort_order = document.getElementById('sort_order');
  const image_file = document.getElementById('image_file');
  const description = document.getElementById('description');
  const metadata = document.getElementById('metadata');

  const currentImageWrap = document.getElementById('currentImageWrap');
  const currentImagePreview = document.getElementById('currentImagePreview');
  const currentImageLink = document.getElementById('currentImageLink');
  const currentImageText = document.getElementById('currentImageText');

  const ACTOR = { id:null, uuid:'', role:'' };
  let canWrite = true;

  let binLoaded = false;

  function computePermissions(){
    // adjust if you want: canWrite = !['student'].includes((ACTOR.role||'').toLowerCase());
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
    year_from: null,
    year_to: null,
    org: '',
    sort: '-publication_year',
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

  function badgeYear(y){
    if(!y) return `<span class="badge badge-soft-muted">—</span>`;
    return `<span class="badge badge-soft-primary">${escapeHtml(y)}</span>`;
  }

  function clipText(s, n=140){
    const t = (s||'').toString().trim();
    if(!t) return '<span class="text-muted">—</span>';
    const short = t.length > n ? t.slice(0,n) + '…' : t;
    return `<span title="${escapeHtml(t)}">${escapeHtml(short)}</span>`;
  }

  function imagePreviewCell(img, titleText){
    const src = normalizeLink(img);
    if(!src){
      return `
        <div class="img-cell">
          <div class="img-placeholder"><i class="fa fa-image"></i></div>
          <div class="img-meta">
            <div class="muted">No image</div>
          </div>
        </div>`;
    }
    return `
      <div class="img-cell">
        <a class="img-thumb" href="${escapeHtml(src)}" target="_blank" rel="noopener" title="Open image">
          <img src="${escapeHtml(src)}" alt="${escapeHtml(titleText||'image')}" loading="lazy">
        </a>
        <div class="img-meta">
          <a href="${escapeHtml(src)}" target="_blank" rel="noopener">Open</a>
          <div class="muted">${escapeHtml(src.split('/').slice(-1)[0] || '')}</div>
        </div>
      </div>`;
  }

  function applyFilters(arr){
    let out = arr.slice();
    const q = (state.q||'').toLowerCase().trim();
    if(q){
      out = out.filter(r=>{
        const hay = [r.title, r.publication_organization, r.publication_year, r.description]
          .map(x=>(x||'').toString().toLowerCase()).join(' | ');
        return hay.includes(q);
      });
    }

    if(state.year_from) out = out.filter(r => (r.publication_year ?? null) !== null ? Number(r.publication_year) >= Number(state.year_from) : false);
    if(state.year_to) out = out.filter(r => (r.publication_year ?? null) !== null ? Number(r.publication_year) <= Number(state.year_to) : false);

    const o = (state.org||'').toLowerCase().trim();
    if(o) out = out.filter(r => ((r.publication_organization||'').toString().toLowerCase()).includes(o));

    return out;
  }

  function sortRows(arr){
    const key = state.sort.startsWith('-') ? state.sort.slice(1) : state.sort;
    const dir = state.sort.startsWith('-') ? -1 : 1;

    return arr.slice().sort((a,b)=>{
      let av=a[key], bv=b[key];
      if(key==='title'){ av=(av||'').toString().toLowerCase(); bv=(bv||'').toString().toLowerCase(); }
      else if(key==='publication_year'){ av=(av===null||av===undefined)?-Infinity:Number(av); bv=(bv===null||bv===undefined)?-Infinity:Number(bv); }
      else if(key==='created_at'){ av=(av||'').toString(); bv=(bv||'').toString(); }
      if(av===bv) return 0;
      return av>bv ? dir : -dir;
    });
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

  function renderTable(rows){
    if(!tbody) return;

    if(!rows.length){
      tbody.innerHTML = '';
      return;
    }

    tbody.innerHTML = rows.map(r=>{
      const yr = r.publication_year ? String(r.publication_year) : '';
      const tt = r.title ? String(r.title) : '';
      const org = r.publication_organization ? String(r.publication_organization) : '';

      const actionHtml = `
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
          <td>${imagePreviewCell(r.image, tt)}</td>
          <td>${badgeYear(yr)}</td>
          <td>
            <div class="fw-semibold">${escapeHtml(tt)}</div>
            <div class="small text-muted">${escapeHtml(org || '—')}</div>
          </td>
          <td>${org ? escapeHtml(org) : '<span class="text-muted">—</span>'}</td>
          <td>${clipText(r.description, 150)}</td>
          <td class="text-end">${actionHtml}</td>
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
        const hay = [r.title, r.publication_organization, r.publication_year, r.description]
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
      const yr = r.publication_year ? String(r.publication_year) : '';
      const tt = r.title ? String(r.title) : '';
      const org = r.publication_organization ? String(r.publication_organization) : '';
      const delAt = r.deleted_at ? String(r.deleted_at) : '—';

      const actionHtml = `
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
          <td>${imagePreviewCell(r.image, tt)}</td>
          <td>${badgeYear(yr)}</td>
          <td>
            <div class="fw-semibold">${escapeHtml(tt)}</div>
            <div class="small text-muted">${escapeHtml(org || '—')}</div>
          </td>
          <td>${org ? escapeHtml(org) : '<span class="text-muted">—</span>'}</td>
          <td>${escapeHtml(delAt)}</td>
          <td class="text-end">${actionHtml}</td>
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

  async function loadJournals(showLoader=true){
    try{
      if(showLoader) showInlineLoading(true);
      const res = await fetch(`/api/users/${encodeURIComponent(ACTOR.uuid)}/journals`, { headers: authHeaders() });
      const js = await res.json().catch(()=>({}));
      if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load journals');
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
      const res = await fetch(`/api/users/${encodeURIComponent(ACTOR.uuid)}/journals/deleted`, { headers: authHeaders() });
      const js = await res.json().catch(()=>({}));
      if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load deleted journals');
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
    const res = await fetch(`/api/users/${encodeURIComponent(ACTOR.uuid)}/journals/${encodeURIComponent(uuid)}`, { headers: authHeaders() });
    const js = await res.json().catch(()=>({}));
    if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to fetch record');
    return js.data || null;
  }

  function resetForm(){
    jrnForm.reset();
    jrnUuid.value='';
    metadata.value='';
    if (image_file) image_file.value = '';
    jrnForm.dataset.mode='edit';
    currentImageWrap.style.display = 'none';
    currentImageLink.href = '#';
    currentImageText.textContent = '';
    currentImagePreview.src = '';
  }

  function setFormReadonly(on){
    Array.from(jrnForm.querySelectorAll('input,select,textarea')).forEach(el=>{
      if(el.id==='jrnUuid') return;
      if(on){
        if(el.type === 'file') el.disabled = true;
        else if(el.tagName==='SELECT') el.disabled=true;
        else el.readOnly=true;
      }else{
        el.disabled=false;
        el.readOnly=false;
      }
    });
  }

  function fillForm(r){
    jrnUuid.value = r.uuid || '';
    title.value = r.title || '';
    publication_year.value = r.publication_year ?? '';
    publication_organization.value = r.publication_organization || '';
    sort_order.value = (r.sort_order ?? 0);
    description.value = r.description || '';

    if(r.metadata && typeof r.metadata === 'object'){
      try{ metadata.value = JSON.stringify(r.metadata, null, 2); }catch(_){ metadata.value=''; }
    }else metadata.value='';

    if (r.image) {
      const c = normalizeLink(r.image);
      currentImageWrap.style.display = '';
      currentImageLink.href = c;
      currentImageText.textContent = c;
      currentImagePreview.src = c;
    } else {
      currentImageWrap.style.display = 'none';
      currentImageLink.href = '#';
      currentImageText.textContent = '';
      currentImagePreview.src = '';
    }
  }

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

  filterModalEl.addEventListener('show.bs.modal', ()=>{
    modalYearFrom.value = state.year_from ?? '';
    modalYearTo.value = state.year_to ?? '';
    modalOrg.value = state.org || '';
    modalSort.value = state.sort || '-publication_year';
  });

  btnApplyFilters.addEventListener('click', ()=>{
    const yf = modalYearFrom.value ? parseInt(modalYearFrom.value, 10) : null;
    const yt = modalYearTo.value ? parseInt(modalYearTo.value, 10) : null;
    state.year_from = Number.isNaN(yf) ? null : yf;
    state.year_to = Number.isNaN(yt) ? null : yt;
    state.org = (modalOrg.value || '').trim();
    state.sort = modalSort.value || '-publication_year';
    state.page = 1;
    filterModal.hide();
    renderAll();
  });

  btnReset.addEventListener('click', ()=>{
    state.q=''; state.year_from=null; state.year_to=null; state.org=''; state.sort='-publication_year';
    state.perPage=10; state.page=1;
    searchInput.value=''; perPageSel.value='10';
    modalYearFrom.value=''; modalYearTo.value=''; modalOrg.value=''; modalSort.value='-publication_year';
    renderAll();
  });

  binReset.addEventListener('click', ()=>{
    if(!binLoaded){
      binResultsInfo.textContent = '—';
      binPager.innerHTML = '';
      binEmptyEl.style.display = 'none';
      binTbody.innerHTML = `
        <tr>
          <td colspan="6" class="text-center text-muted" style="padding:38px;">
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
    jrnModalTitle.textContent = 'Add Journal';
    jrnForm.dataset.mode = 'edit';
    setFormReadonly(false);
    saveBtn.style.display = '';
    jrnModal.show();
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
        resetForm();
        fillForm(data || {});
        const viewOnly = (act==='view');
        jrnModalTitle.textContent = viewOnly ? 'View Journal' : 'Edit Journal';
        jrnForm.dataset.mode = viewOnly ? 'view' : 'edit';
        setFormReadonly(viewOnly);
        saveBtn.style.display = viewOnly ? 'none' : '';
        jrnModal.show();
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
        const res = await fetch(`/api/users/${encodeURIComponent(ACTOR.uuid)}/journals/${encodeURIComponent(uuid)}`, {
          method:'DELETE',
          headers: authHeaders()
        });
        const js = await res.json().catch(()=>({}));
        if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Delete failed');
        ok('Moved to Bin');
        await loadJournals(false);
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
        title:'Restore journal?',
        text:'This will restore the record from Bin.',
        icon:'question',
        showCancelButton:true,
        confirmButtonText:'Restore'
      });
      if(!conf.isConfirmed) return;

      showInlineLoading(true);
      try{
        const res = await fetch(`/api/users/${encodeURIComponent(ACTOR.uuid)}/journals/${encodeURIComponent(uuid)}/restore`, {
          method:'POST',
          headers: authHeaders({ 'Content-Type':'application/json' })
        });
        const js = await res.json().catch(()=>({}));
        if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Restore failed');
        ok('Restored');
        await loadJournals(false);
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
        const res = await fetch(`/api/users/${encodeURIComponent(ACTOR.uuid)}/journals/${encodeURIComponent(uuid)}/force`, {
          method:'DELETE',
          headers: authHeaders()
        });
        const js = await res.json().catch(()=>({}));
        if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Permanent delete failed');
        ok('Deleted permanently');
        await loadBin(false);
        await loadJournals(false);
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
      text:'This will permanently delete all deleted journals.',
      icon:'warning',
      showCancelButton:true,
      confirmButtonText:'Empty Bin',
      confirmButtonColor:'#ef4444'
    });
    if(!conf.isConfirmed) return;

    showInlineLoading(true);
    try{
      const res = await fetch(`/api/users/${encodeURIComponent(ACTOR.uuid)}/journals/deleted/force`, {
        method:'DELETE',
        headers: authHeaders()
      });
      const js = await res.json().catch(()=>({}));
      if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Empty Bin failed');
      ok('Bin emptied');
      await loadBin(false);
      await loadJournals(false);
    }catch(ex){
      err(ex.message);
    }
    finally{
      showInlineLoading(false);
    }
  });

  // Save (Add/Edit) ✅ FormData for image upload
  jrnForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    if(jrnForm.dataset.mode === 'view') return;

    if(!title.value.trim()){ title.focus(); return; }

    let metaObj = null;
    try{ metaObj = parseJsonOrThrow(metadata.value); }
    catch(ex){ err(ex.message); metadata.focus(); return; }

    const isEdit = !!jrnUuid.value;
    const endpoint = isEdit
      ? `/api/users/${encodeURIComponent(ACTOR.uuid)}/journals/${encodeURIComponent(jrnUuid.value)}`
      : `/api/users/${encodeURIComponent(ACTOR.uuid)}/journals`;
    const method = isEdit ? 'POST' : 'POST'; // will use POST + _method for edit

    try{
      setButtonLoading(saveBtn, true);
      showInlineLoading(true);

      const fd = new FormData();
      fd.append('title', title.value.trim());

      const org = safeText(publication_organization.value);
      const yr = publication_year.value ? parseInt(publication_year.value, 10) : null;
      const so = sort_order.value ? parseInt(sort_order.value, 10) : 0;
      const desc = safeText(description.value);

      if(org !== null) fd.append('publication_organization', org);
      if(yr !== null && !Number.isNaN(yr)) fd.append('publication_year', String(yr));
      if(!Number.isNaN(so)) fd.append('sort_order', String(so));
      if(desc !== null) fd.append('description', desc);

      if(metaObj !== null) fd.append('metadata', JSON.stringify(metaObj));
      else if ((metadata.value||'').trim() === '') {
        // keep empty => not sent (server will keep null / unchanged based on your controller)
      }

      const file = image_file?.files?.[0];
      if(file) fd.append('image_file', file);

      if(isEdit){
        fd.append('_method', 'PUT'); // Laravel friendly
      }

      const res = await fetch(endpoint, {
        method,
        headers: authHeaders(), // DO NOT set content-type for FormData
        body: fd
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

      jrnModal.hide();
      ok(isEdit ? 'Journal updated' : 'Journal created');
      await loadJournals(false);
      if(binLoaded) await loadBin(false);
    }catch(ex){
      err(ex.message);
    }finally{
      setButtonLoading(saveBtn, false);
      showInlineLoading(false);
      cleanupModalBackdrops();
    }
  });

  (async ()=>{
    showInlineLoading(true);
    try{
      await fetchMe();
      await loadJournals(false);
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
