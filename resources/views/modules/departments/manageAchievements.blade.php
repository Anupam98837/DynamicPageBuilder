{{-- resources/views/modules/achievement/manageAchievements.blade.php --}}
@section('title','Achievements')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css"/>

<style>
/* Dropdowns inside table */
.table-wrap .dropdown{position:relative}
.dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
.dropdown-menu{
  border-radius:12px;border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);min-width:220px;z-index:1085
}
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

/* tables easier to scroll on mobile */
.table{min-width:1320px}

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

/* Soft badges */
.badge-soft{
  display:inline-flex;align-items:center;gap:6px;
  padding:.35rem .55rem;border-radius:999px;font-size:12px;font-weight:600
}
.badge-soft-primary{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  color:var(--primary-color)
}
.badge-soft-muted{
  background:color-mix(in oklab, var(--muted-color) 12%, transparent);
  color:var(--muted-color)
}
.badge-soft-success{
  background:color-mix(in oklab, var(--success-color, #16a34a) 12%, transparent);
  color:var(--success-color, #16a34a)
}
.badge-soft-warning{
  background:color-mix(in oklab, var(--warning-color, #f59e0b) 14%, transparent);
  color:var(--warning-color, #f59e0b)
}
.badge-soft-danger{
  background:color-mix(in oklab, var(--danger-color) 14%, transparent);
  color:var(--danger-color)
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

.ach-toolbar.panel{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-1);
  padding:12px 12px
}
.ach-toolbar .form-select,
.ach-toolbar .form-control{border-radius:12px}

/* ✅ Tabs style */
.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface)
}
.tab-content,.tab-pane{overflow:visible}

/* Image preview in table */
.img-cell{display:flex;align-items:center;gap:10px}
.img-thumb{
  width:44px;height:44px;border-radius:10px;
  border:1px solid var(--line-soft);
  overflow:hidden;flex:0 0 44px;
  background:color-mix(in oklab, var(--muted-color) 10%, transparent)
}
.img-thumb img{width:100%;height:100%;object-fit:cover;display:block}
.img-placeholder{
  width:44px;height:44px;border-radius:10px;
  border:1px dashed var(--line-soft);
  display:flex;align-items:center;justify-content:center;
  color:var(--muted-color);
  background:transparent
}
.img-cell .img-meta{display:flex;flex-direction:column;gap:2px}
.img-cell .img-meta a{font-size:12.5px}
.img-cell .img-meta .muted{font-size:12px;color:var(--muted-color)}

/* Quill in modal */
.quill-wrap{
  border:1px solid var(--line-strong);
  border-radius:12px;
  overflow:hidden;
  background:var(--surface)
}
.quill-wrap .ql-toolbar{border-bottom:1px solid var(--line-strong)}
.quill-wrap .ql-container{border:0}
.quill-wrap .ql-editor{min-height:220px}
.quill-readonly .ql-toolbar{display:none}

@media (max-width: 768px){
  .ach-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .ach-toolbar .position-relative{min-width:100% !important}
  .toolbar-buttons{display:flex;gap:8px;flex-wrap:wrap}
  .toolbar-buttons .btn{flex:1;min-width:160px}
  .table{min-width:1200px}
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
        <i class="fa fa-trophy me-1"></i> Achievements
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
       Active Achievements
       ========================= --}}
    <div class="tab-pane fade show active" id="pane-active" role="tabpanel">

      <div class="row align-items-center g-2 mb-3 ach-toolbar panel">
        <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per Page</label>
            <select id="perPage" class="form-select" style="width:96px;">
              <option>10</option><option selected>20</option><option>50</option><option>100</option>
            </select>
          </div>

          <div class="position-relative" style="min-width:280px;">
            <input id="searchInput" type="search" class="form-control ps-5" placeholder="Search by title, department, body…">
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
              <i class="fa fa-plus me-1"></i> Add Achievement
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
                  <th style="width:360px;">Title</th>
                  <th style="width:240px;">Department</th>
                  <th style="width:140px;">Featured</th>
                  <th style="width:260px;">Published</th>
                  <th style="width:120px;">Views</th>
                  <th style="width:110px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="achTbody">
                <tr>
                  <td colspan="7" class="text-center text-muted" style="padding:38px;">Loading…</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="empty" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-trophy mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No achievements found for current filters.</div>
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

      <div class="row align-items-center g-2 mb-3 ach-toolbar panel">
        <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per Page</label>
            <select id="binPerPage" class="form-select" style="width:96px;">
              <option>10</option><option selected>20</option><option>50</option><option>100</option>
            </select>
          </div>

          <div class="position-relative" style="min-width:280px;">
            <input id="binSearchInput" type="search" class="form-control ps-5" placeholder="Search in deleted achievements…">
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>

          <button id="binReset" class="btn btn-light">
            <i class="fa fa-rotate-left me-1"></i>Reset
          </button>
        </div>

        <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
          <div class="toolbar-buttons" id="binControls" style="display:none;">
            <button type="button" class="btn btn-outline-danger" id="btnEmptyBin" title="Deletes items on current page (no bulk endpoint)">
              <i class="fa fa-trash-can me-1"></i> Empty Page
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
                  <th style="width:360px;">Title</th>
                  <th style="width:240px;">Department</th>
                  <th style="width:220px;">Deleted At</th>
                  <th style="width:140px;">Featured</th>
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
            <div>No deleted achievements in Bin.</div>
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
        <h5 class="modal-title"><i class="fa fa-sliders me-2"></i>Filter Achievements</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Department</label>
            <select id="modal_department" class="form-select">
              <option value="">All</option>
            </select>
            <div class="form-text">Filter can call department endpoints when selected.</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Featured</label>
            <select id="modal_featured" class="form-select">
              <option value="">All</option>
              <option value="1">Featured only</option>
              <option value="0">Not featured</option>
            </select>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Sort</label>
            <select id="modal_sort" class="form-select">
              <option value="created_at">Created At</option>
              <option value="published_at">Published At</option>
              <option value="title">Title</option>
              <option value="views_count">Views</option>
              <option value="id">ID</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Direction</label>
            <select id="modal_dir" class="form-select">
              <option value="desc">Desc</option>
              <option value="asc">Asc</option>
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
<div class="modal fade" id="achModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form class="modal-content" id="achForm">
      <div class="modal-header">
        <h5 class="modal-title" id="achModalTitle">Add Achievement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="achIdentifier" />

        <div class="row g-3">

          {{-- ✅ Department dropdown (title/name) for Create + Edit (and we now ALWAYS send department_id on save) --}}
          <div class="col-md-6">
            <label class="form-label">Department</label>
            <select class="form-select" id="department_id">
              <option value="">Global (no department)</option>
            </select>
            <div class="form-text">Choose a department (optional). Options show department <b>title/name</b>.</div>
          </div>

          <div class="col-md-3 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="is_featured_home">
              <label class="form-check-label" for="is_featured_home">Featured on Home</label>
            </div>
          </div>

          <div class="col-md-3">
            <label class="form-label">Published At (optional)</label>
            <input type="datetime-local" class="form-control" id="published_at">
          </div>

          <div class="col-md-8">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input class="form-control" id="title" required maxlength="255" placeholder="Achievement title">
          </div>

          <div class="col-md-4">
            <label class="form-label">Slug (optional)</label>
            <input class="form-control" id="slug" maxlength="160" placeholder="auto from title if empty">
          </div>

          <div class="col-md-6">
            <label class="form-label">Image (optional)</label>
            <input type="file" class="form-control" id="image" accept="image/*">
            <div class="form-text">Upload achievement image.</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Attachments (optional)</label>
            <input type="file" class="form-control" id="attachments" multiple>
            <div class="form-text">You can upload multiple files.</div>
          </div>

          <div class="col-md-12" id="currentImageWrap" style="display:none;">
            <label class="form-label">Current Image</label>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <div class="img-thumb" style="width:54px;height:54px;">
                <img id="currentImagePreview" src="" alt="preview">
              </div>
              <a href="#" target="_blank" rel="noopener" id="currentImageLink" class="small">Open image</a>
              <span class="text-muted small" id="currentImageText"></span>

              <div class="form-check ms-2" id="imageRemoveWrap" style="display:none;">
                <input class="form-check-input" type="checkbox" id="image_remove">
                <label class="form-check-label" for="image_remove">Remove image</label>
              </div>
            </div>
          </div>

          <div class="col-md-12" id="currentAttachmentsWrap" style="display:none;">
            <label class="form-label">Current Attachments</label>
            <div class="list-group" id="currentAttachmentsList"></div>
            <div class="d-flex align-items-center gap-2 mt-2" id="attachmentsModeWrap" style="display:none;">
              <label class="text-muted small mb-0">When uploading new attachments:</label>
              <select class="form-select" id="attachments_mode" style="max-width:180px;">
                <option value="append">Append</option>
                <option value="replace">Replace</option>
              </select>
            </div>
          </div>

          <div class="col-md-12">
            <label class="form-label">Body <span class="text-danger">*</span></label>
            <div class="quill-wrap" id="quillWrap">
              <div id="bodyEditor"></div>
            </div>
            <div class="form-text">Rich text editor. Saved as HTML.</div>
          </div>

          <div class="col-md-12">
            <label class="form-label">Metadata (JSON) (optional)</label>
            <textarea class="form-control" id="metadata" rows="4" placeholder='{"type":"award","level":"state"}'></textarea>
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
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

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

  // ✅ Achievements APIs (as you provided)
  const API = {
    me: '/api/users/me',
    departments: '/api/departments',

    list: (qs) => '/api/achievements' + (qs ? ('?' + qs) : ''),
    one: (id) => `/api/achievements/${encodeURIComponent(id)}`,

    listByDept: (dept, qs) => `/api/departments/${encodeURIComponent(dept)}/achievements` + (qs ? ('?' + qs) : ''),
    oneByDept: (dept, id) => `/api/departments/${encodeURIComponent(dept)}/achievements/${encodeURIComponent(id)}`,

    store: '/api/achievements',
    storeForDept: (dept) => `/api/departments/${encodeURIComponent(dept)}/achievements`,

    trash: (qs) => '/api/achievements-trash' + (qs ? ('?' + qs) : ''),

    update: (id) => `/api/achievements/${encodeURIComponent(id)}`,
    toggleFeatured: (id) => `/api/achievements/${encodeURIComponent(id)}/toggle-featured`,

    destroy: (id) => `/api/achievements/${encodeURIComponent(id)}`,
    restore: (id) => `/api/achievements/${encodeURIComponent(id)}/restore`,
    force: (id) => `/api/achievements/${encodeURIComponent(id)}/force`,
  };

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
    // ✅ FIX: handle object-ish / backslashes / weird values safely
    const raw = (src ?? '');
    const s = (typeof raw === 'string' ? raw : String(raw)).trim().replace(/\\/g,'/');
    if(!s) return '';
    if(/^data:/i.test(s)) return s;
    if(/^blob:/i.test(s)) return s;
    if(/^https?:\/\//i.test(s)) return s;
    if(s.startsWith('//')) return s;
    if(s.startsWith('/')) return s;
    return '/' + s;
  }

  function dtLocalToServer(v){
    const s=(v||'').toString().trim();
    if(!s) return null;
    return s.replace('T',' ') + ':00';
  }
  function serverToDtLocal(v){
    const s=(v||'').toString().trim();
    if(!s) return '';
    const iso = s.includes('T') ? s : s.replace(' ', 'T');
    return iso.slice(0,16);
  }

  const toastOk = new bootstrap.Toast(document.getElementById('toastSuccess'));
  const toastErr = new bootstrap.Toast(document.getElementById('toastError'));
  const okTxt = document.getElementById('toastSuccessText');
  const errTxt = document.getElementById('toastErrorText');
  const ok = m => { okTxt.textContent = m || 'Done'; toastOk.show(); };
  const err = m => { errTxt.textContent = m || 'Something went wrong'; toastErr.show(); };

  // Elements
  const perPageSel = document.getElementById('perPage');
  const searchInput = document.getElementById('searchInput');
  const btnReset = document.getElementById('btnReset');
  const btnApplyFilters = document.getElementById('btnApplyFilters');
  const writeControls = document.getElementById('writeControls');
  const btnAdd = document.getElementById('btnAdd');

  const tbody = document.getElementById('achTbody');
  const emptyEl = document.getElementById('empty');
  const pager = document.getElementById('pager');
  const resultsInfo = document.getElementById('resultsInfo');

  // Bin
  const binPerPageSel = document.getElementById('binPerPage');
  const binSearchInput = document.getElementById('binSearchInput');
  const binReset = document.getElementById('binReset');
  const binTbody = document.getElementById('binTbody');
  const binEmptyEl = document.getElementById('binEmpty');
  const binPager = document.getElementById('binPager');
  const binResultsInfo = document.getElementById('binResultsInfo');
  const btnEmptyBin = document.getElementById('btnEmptyBin');
  const binControls = document.getElementById('binControls');

  // Filter modal
  const filterModalEl = document.getElementById('filterModal');
  const filterModal = new bootstrap.Modal(filterModalEl);
  const modalDept = document.getElementById('modal_department');
  const modalFeatured = document.getElementById('modal_featured');
  const modalSort = document.getElementById('modal_sort');
  const modalDir = document.getElementById('modal_dir');

  // Form modal
  const achModalEl = document.getElementById('achModal');
  const achModal = new bootstrap.Modal(achModalEl);
  const achForm = document.getElementById('achForm');
  const achModalTitle = document.getElementById('achModalTitle');
  const saveBtn = document.getElementById('saveBtn');

  // Form fields
  const achIdentifier = document.getElementById('achIdentifier');
  const department_id = document.getElementById('department_id');
  const is_featured_home = document.getElementById('is_featured_home');
  const published_at = document.getElementById('published_at');
  const title = document.getElementById('title');
  const slug = document.getElementById('slug');
  const image = document.getElementById('image');
  const attachments = document.getElementById('attachments');
  const metadata = document.getElementById('metadata');

  const currentImageWrap = document.getElementById('currentImageWrap');
  const currentImagePreview = document.getElementById('currentImagePreview');
  const currentImageLink = document.getElementById('currentImageLink');
  const currentImageText = document.getElementById('currentImageText');
  const imageRemoveWrap = document.getElementById('imageRemoveWrap');
  const image_remove = document.getElementById('image_remove');

  const currentAttachmentsWrap = document.getElementById('currentAttachmentsWrap');
  const currentAttachmentsList = document.getElementById('currentAttachmentsList');
  const attachmentsModeWrap = document.getElementById('attachmentsModeWrap');
  const attachments_mode = document.getElementById('attachments_mode');

  // Quill
  const quillWrap = document.getElementById('quillWrap');

  const quill = new Quill('#bodyEditor', {
    theme: 'snow',
    modules: {
      toolbar: [
        [{ header: [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ color: [] }, { background: [] }],
        [{ list: 'ordered' }, { list: 'bullet' }, { align: [] }],
        ['link', 'blockquote', 'code-block'],
        ['clean']
      ]
    }
  });

  // ✅ FIX #2: remove duplicated/extra toolbar (keep ONLY the toolbar bound to this Quill instance)
  try{
    const boundTb = quill.getModule('toolbar')?.container || null;
    if(boundTb && quillWrap){
      quillWrap.querySelectorAll('.ql-toolbar').forEach(tb=>{
        if(tb !== boundTb) tb.remove();
      });
    }
  }catch(ex){ console.warn('Quill toolbar cleanup failed', ex); }

  function setQuillReadonly(on){
    quill.enable(!on);
    quillWrap.classList.toggle('quill-readonly', !!on);
  }

  function cleanupModalBackdrops(){
    document.querySelectorAll('.modal-backdrop').forEach(b=>b.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('padding-right');
  }
  ['hidden.bs.modal','hide.bs.modal'].forEach(ev=>{
    filterModalEl.addEventListener(ev, cleanupModalBackdrops);
    achModalEl.addEventListener(ev, cleanupModalBackdrops);
  });
  window.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') setTimeout(cleanupModalBackdrops, 0); });
  window.addEventListener('beforeunload', cleanupModalBackdrops);

  // Permissions
  const ACTOR = { id:null, uuid:'', role:'' };
  let canWrite = true;
  let binLoaded = false;

  function computePermissions(){
    // Same style as sample; server guards anyway.
    writeControls.style.display = canWrite ? 'flex' : 'none';
    binControls.style.display = canWrite ? 'flex' : 'none';
  }

  function setButtonLoading(button, loading){
    if(!button) return;
    button.disabled = !!loading;
    button.classList.toggle('btn-loading', !!loading);
  }

  // ✅ Departments index to resolve edit payload shapes (id/uuid/slug → id)
  const DEPT_INDEX = new Map(); // key:string -> id:string

  // State
  const state = {
    items: [],
    q: '',
    department: '',
    featured: '',
    sort: 'created_at',
    direction: 'desc',
    perPage: 20,
    page: 1,
    meta: { total:0, last_page:1, page:1, per_page:20 }
  };

  const binState = {
    items: [],
    q: '',
    perPage: 20,
    page: 1,
    meta: { total:0, last_page:1, page:1, per_page:20 }
  };

  // UI helpers
  function badgeFeatured(v){
    const on = ((+v) === 1);
    return on
      ? `<span class="badge-soft badge-soft-primary"><i class="fa fa-star"></i> Yes</span>`
      : `<span class="badge-soft badge-soft-muted"><i class="fa fa-star"></i> No</span>`;
  }

  function deptBadge(row){
    const name = row.department_title || row.department_name || row.department?.title || row.department?.name || '';
    if(name){
      return `<span class="badge-soft badge-soft-primary"><i class="fa fa-building"></i> ${escapeHtml(name)}</span>`;
    }
    return `<span class="badge-soft badge-soft-muted"><i class="fa fa-globe"></i> Global</span>`;
  }

  function clipText(s, n=120){
    const t = (s||'').toString().replace(/<[^>]*>/g,'').trim();
    if(!t) return '<span class="text-muted">—</span>';
    const short = t.length > n ? t.slice(0,n) + '…' : t;
    return `<span title="${escapeHtml(t)}">${escapeHtml(short)}</span>`;
  }

  function imagePreviewCell(imgUrl, titleText){
    const src = normalizeLink(imgUrl);
    if(!src){
      return `
        <div class="img-cell">
          <div class="img-placeholder"><i class="fa fa-image"></i></div>
          <div class="img-meta">
            <div class="muted">No image</div>
          </div>
        </div>`;
    }
    // ✅ FIX #1: don't rely on lazy-loading inside overflow containers (Safari/scroll containers can skip it)
    return `
      <div class="img-cell">
        <a class="img-thumb" href="${escapeHtml(src)}" target="_blank" rel="noopener" title="Open image">
          <img src="${escapeHtml(src)}" alt="${escapeHtml(titleText||'image')}" loading="eager" decoding="async">
        </a>
        <div class="img-meta">
          <a href="${escapeHtml(src)}" target="_blank" rel="noopener">Open</a>
          <div class="muted">${escapeHtml(src.split('/').slice(-1)[0] || '')}</div>
        </div>
      </div>`;
  }

  function renderInfo(meta, shown, el){
    if(!el) return;
    const total = meta?.total || 0;
    const page = meta?.page || 1;
    const per = meta?.per_page || 20;
    if(!total || !shown){ el.textContent = `0 of ${total}`; return; }
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

  function pickImageFromRow(r){
    // ✅ FIX #1: handle list endpoint shapes where image can be object/array/nested
    const tryVal = (v) => {
      if(!v) return '';
      if(typeof v === 'string') return v.trim();
      if(typeof v === 'object'){
        // common shapes: {url}, {path}, {full_url}, {src}
        const u =
          v.url || v.path || v.full_url || v.fullUrl || v.file_url || v.fileUrl || v.src || v.href || '';
        if(typeof u === 'string' && u.trim()) return u.trim();
      }
      return '';
    };

    // direct candidates
    const direct = [
      r.image_url, r.image,
      r.cover_image_url, r.cover_image,
      r.thumbnail_url, r.thumbnail,
      r.thumb_url, r.thumb,
      r.image_path, r.photo_url, r.banner_url
    ];
    for(const c of direct){
      const out = tryVal(c);
      if(out) return out;
    }

    // nested candidates
    const nestedObjs = [r.media, r.image_media, r.cover, r.thumbnail_media, r.thumb_media];
    for(const o of nestedObjs){
      const out = tryVal(o);
      if(out) return out;
    }

    // last chance: some APIs return {image:{data:{url}}}
    try{
      const deep = r?.image?.data?.url || r?.image?.data?.path || r?.media?.data?.url || '';
      if(typeof deep === 'string' && deep.trim()) return deep.trim();
    }catch(_){}

    return '';
  }

  function renderTable(items){
    if(!tbody) return;

    if(!items.length){
      tbody.innerHTML = '';
      return;
    }

    tbody.innerHTML = items.map(r=>{
      const id = r.uuid || r.id || r.slug || '';
      const tt = r.title ? String(r.title) : '';
      const img = pickImageFromRow(r);

      const published = r.published_at || r.publish_at || r.created_at || '';
      const views = r.views_count ?? r.view_count ?? 0;

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
            <li><button type="button" class="dropdown-item" data-action="toggleFeatured" ${canWrite ? '' : 'disabled'}><i class="fa fa-star"></i> Toggle Featured</button></li>
            <li><hr class="dropdown-divider"></li>
            <li><button type="button" class="dropdown-item text-danger" data-action="delete" ${canWrite ? '' : 'disabled'}><i class="fa fa-trash"></i> Delete</button></li>
          </ul>
        </div>`;

      return `
        <tr data-id="${escapeHtml(id)}">
          <td>${imagePreviewCell(img, tt)}</td>
          <td>
            <div class="fw-semibold">${escapeHtml(tt || '—')}</div>
            <div class="small text-muted">${clipText(r.body || r.description || '', 95)}</div>
          </td>
          <td>${deptBadge(r)}</td>
          <td>${badgeFeatured(r.is_featured_home)}</td>
          <td><span class="small">${escapeHtml(String(published || '—'))}</span></td>
          <td>${escapeHtml(String(views))}</td>
          <td class="text-end">${actionHtml}</td>
        </tr>`;
    }).join('');
  }

  function renderBinTable(items){
    if(!binTbody) return;

    if(!items.length){
      binTbody.innerHTML = '';
      return;
    }

    binTbody.innerHTML = items.map(r=>{
      const id = r.uuid || r.id || r.slug || '';
      const tt = r.title ? String(r.title) : '';
      const img = pickImageFromRow(r);
      const delAt = r.deleted_at || '—';

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
        <tr data-id="${escapeHtml(id)}">
          <td>${imagePreviewCell(img, tt)}</td>
          <td>
            <div class="fw-semibold">${escapeHtml(tt || '—')}</div>
            <div class="small text-muted">${escapeHtml((r.slug||'') ? ('/' + String(r.slug)) : '')}</div>
          </td>
          <td>${deptBadge(r)}</td>
          <td>${escapeHtml(String(delAt))}</td>
          <td>${badgeFeatured(r.is_featured_home)}</td>
          <td class="text-end">${actionHtml}</td>
        </tr>`;
    }).join('');
  }

  async function fetchMe(){
    const res = await fetch(API.me, { headers: authHeaders() });
    const js = await res.json().catch(()=>({}));
    if (res.status === 401) { window.location.href = '/'; return; }
    if (!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load current user');
    ACTOR.id = js.data?.id || null;
    ACTOR.uuid = js.data?.uuid || '';
    ACTOR.role = (js.data?.role || '').toLowerCase();
    computePermissions();
  }

  async function loadDepartments(){
    // uses your existing departments API in system
    const res = await fetch(API.departments, { headers: authHeaders() });
    const js = await res.json().catch(()=>({}));
    if(!res.ok) throw new Error(js.message || 'Failed to load departments');
    const rows = Array.isArray(js.data) ? js.data : (Array.isArray(js) ? js : []);
    const items = rows.filter(d => !d.deleted_at);

    // ✅ Only show title/name in dropdown text (fallback to Department #id)
    const label = (d) => {
      const t = (d?.title || '').toString().trim();
      const n = (d?.name || '').toString().trim();
      return t || n || (`Department #${d?.id ?? ''}`.trim());
    };

    // ✅ build index for edit payload shapes (id/uuid/slug → id)
    DEPT_INDEX.clear();
    items.forEach(d=>{
      const id = (d?.id ?? '').toString();
      if(id) DEPT_INDEX.set(id, id);
      const uuid = (d?.uuid ?? '').toString().trim();
      if(uuid) DEPT_INDEX.set(uuid, id);
      const slug = (d?.slug ?? '').toString().trim();
      if(slug) DEPT_INDEX.set(slug, id);
    });

    // filter modal
    modalDept.innerHTML = `<option value="">All</option>` + items.map(d =>
      `<option value="${escapeHtml(String(d.id))}">${escapeHtml(label(d))}</option>`
    ).join('');

    // form select
    department_id.innerHTML = `<option value="">Global (no department)</option>` + items.map(d =>
      `<option value="${escapeHtml(String(d.id))}">${escapeHtml(label(d))}</option>`
    ).join('');
  }

  function buildActiveQuery(){
    const p = new URLSearchParams();
    p.set('page', String(state.page || 1));
    p.set('per_page', String(state.perPage || 20));
    if(state.q) p.set('q', state.q);
    if(state.featured !== '') p.set('featured', state.featured);
    p.set('sort', state.sort || 'created_at');
    p.set('direction', state.direction || 'desc');
    return p.toString();
  }

  function buildBinQuery(){
    const p = new URLSearchParams();
    p.set('page', String(binState.page || 1));
    p.set('per_page', String(binState.perPage || 20));
    if(binState.q) p.set('q', binState.q);
    return p.toString();
  }

  async function loadActive(showLoader=true){
    try{
      if(showLoader) showInlineLoading(true);

      // ✅ If a department is selected, use department endpoints
      const dept = (state.department || '').trim();
      const url = dept ? API.listByDept(dept, buildActiveQuery()) : API.list(buildActiveQuery());

      const res = await fetch(url, { headers: authHeaders() });
      const js = await res.json().catch(()=>({}));
      if(res.status === 401) { window.location.href = '/'; return; }
      if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load achievements');

      state.items = Array.isArray(js.data) ? js.data : [];
      state.meta = js.pagination || js.meta || { total:state.items.length, last_page:1, page:state.page, per_page:state.perPage };
      state.page = state.meta.page || state.page;

      renderTable(state.items);
      renderPagerGeneric(pager, state.meta.page || state.page, state.meta.last_page || 1);
      renderInfo(state.meta, state.items.length, resultsInfo);
      emptyEl.style.display = ((state.meta.total || 0) === 0) ? '' : 'none';
    }catch(e){
      err(e.message); console.error(e);
    }finally{
      if(showLoader) showInlineLoading(false);
    }
  }

  async function loadBin(showLoader=true){
    try{
      if(showLoader) showInlineLoading(true);
      const res = await fetch(API.trash(buildBinQuery()), { headers: authHeaders() });
      const js = await res.json().catch(()=>({}));
      if(res.status === 401) { window.location.href = '/'; return; }
      if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load deleted achievements');

      binState.items = Array.isArray(js.data) ? js.data : [];
      binState.meta = js.pagination || js.meta || { total:binState.items.length, last_page:1, page:binState.page, per_page:binState.perPage };
      binState.page = binState.meta.page || binState.page;

      renderBinTable(binState.items);
      renderPagerGeneric(binPager, binState.meta.page || binState.page, binState.meta.last_page || 1);
      renderInfo(binState.meta, binState.items.length, binResultsInfo);

      binEmptyEl.style.display = ((binState.meta.total || 0) === 0) ? '' : 'none';
      binLoaded = true;
    }catch(e){
      err(e.message); console.error(e);
    }finally{
      if(showLoader) showInlineLoading(false);
    }
  }

  async function fetchOne(identifier){
    const dept = (state.department || department_id.value || '').trim();
    const url = dept ? API.oneByDept(dept, identifier) : API.one(identifier);
    const res = await fetch(url, { headers: authHeaders() });
    const js = await res.json().catch(()=>({}));
    if(res.status === 401) { window.location.href = '/'; return null; }
    if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to fetch record');
    return js.item || js.data || null;
  }

  function resetForm(){
    achForm.reset();
    achIdentifier.value = '';
    image.value = '';
    attachments.value = '';
    metadata.value = '';
    image_remove.checked = false;

    currentImageWrap.style.display = 'none';
    imageRemoveWrap.style.display = 'none';
    currentImageLink.href = '#';
    currentImageText.textContent = '';
    currentImagePreview.src = '';

    currentAttachmentsWrap.style.display = 'none';
    currentAttachmentsList.innerHTML = '';
    attachmentsModeWrap.style.display = 'none';
    attachments_mode.value = 'append';

    quill.setContents([]);
    quill.root.innerHTML = '';
    achForm.dataset.mode = 'edit';
  }

  function setFormReadonly(on){
    Array.from(achForm.querySelectorAll('input,select,textarea')).forEach(el=>{
      if(el.id==='achIdentifier') return;
      if(on){
        if(el.type === 'file') el.disabled = true;
        else if(el.type === 'checkbox') el.disabled = true;
        else if(el.tagName==='SELECT') el.disabled = true;
        else el.readOnly = true;
      } else {
        el.disabled = false;
        el.readOnly = false;
      }
    });
    setQuillReadonly(on);
  }

  function fillForm(r){
    achIdentifier.value = r.uuid || r.id || r.slug || '';

    // ✅ Robust department selection for edit/view:
    // Support shapes: department_id, department.id, department.uuid, department_slug, etc.
    const rawDept =
      (r.department_id ?? r.departmentId ?? '') ||
      (r.department?.id ?? '') ||
      (r.department_uuid ?? '') ||
      (r.department?.uuid ?? '') ||
      (r.department_slug ?? '') ||
      (r.department?.slug ?? '');

    const resolvedDeptId = rawDept ? (DEPT_INDEX.get(String(rawDept)) || String(rawDept)) : '';
    department_id.value = resolvedDeptId || '';

    is_featured_home.checked = ((+r.is_featured_home) === 1);

    title.value = r.title || '';
    slug.value = r.slug || '';
    published_at.value = serverToDtLocal(r.published_at || r.publish_at);

    // body html
    quill.root.innerHTML = (r.body || r.description || '');

    // metadata
    if(r.metadata && typeof r.metadata === 'object'){
      try{ metadata.value = JSON.stringify(r.metadata, null, 2); }catch(_){ metadata.value=''; }
    } else metadata.value = '';

    // image
    const img = normalizeLink(pickImageFromRow(r));
    if(img){
      currentImageWrap.style.display = '';
      imageRemoveWrap.style.display = '';
      currentImageLink.href = img;
      currentImageText.textContent = img;
      currentImagePreview.src = img;
    } else {
      currentImageWrap.style.display = 'none';
      imageRemoveWrap.style.display = 'none';
    }

    // attachments
    const atts = Array.isArray(r.attachments) ? r.attachments : [];
    if(atts.length){
      currentAttachmentsWrap.style.display = '';
      attachmentsModeWrap.style.display = '';
      currentAttachmentsList.innerHTML = atts.map((a, idx)=>{
        const url = normalizeLink(a.url || a.path || '');
        const name = a.name || (url ? url.split('/').pop() : ('Attachment ' + (idx+1)));
        const size = a.size ? (Math.round((+a.size)/1024) + ' KB') : '';
        const sub = [a.mime||'', size].filter(Boolean).join(' • ');
        const pathVal = (a.path || a.url || '').toString();
        return `
          <div class="list-group-item d-flex align-items-center justify-content-between gap-2">
            <div class="d-flex flex-column">
              <div class="fw-semibold small">${escapeHtml(name||'Attachment')}</div>
              <div class="text-muted small">${escapeHtml(sub || url || '')}</div>
            </div>
            <div class="d-flex align-items-center gap-2">
              ${url ? `<a class="btn btn-light btn-sm" href="${escapeHtml(url)}" target="_blank" rel="noopener"><i class="fa fa-arrow-up-right-from-square"></i></a>` : ''}
              <div class="form-check m-0">
                <input class="form-check-input att-remove" type="checkbox" value="${escapeHtml(pathVal)}" id="att_rm_${idx}">
                <label class="form-check-label small" for="att_rm_${idx}">Remove</label>
              </div>
            </div>
          </div>`;
      }).join('');
    } else {
      currentAttachmentsWrap.style.display = 'none';
      currentAttachmentsList.innerHTML = '';
      attachmentsModeWrap.style.display = 'none';
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
    loadActive(true);
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
    loadBin(true);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // Search
  const onSearch = debounce(()=>{
    state.q = searchInput.value.trim();
    state.page = 1;
    loadActive(true);
  }, 320);
  searchInput.addEventListener('input', onSearch);

  perPageSel.addEventListener('change', ()=>{
    state.perPage = parseInt(perPageSel.value, 10) || 20;
    state.page = 1;
    loadActive(true);
  });

  const onBinSearch = debounce(()=>{
    if(!binLoaded) return;
    binState.q = binSearchInput.value.trim();
    binState.page = 1;
    loadBin(true);
  }, 320);
  binSearchInput.addEventListener('input', onBinSearch);

  binPerPageSel.addEventListener('change', ()=>{
    if(!binLoaded) return;
    binState.perPage = parseInt(binPerPageSel.value, 10) || 20;
    binState.page = 1;
    loadBin(true);
  });

  // Filter modal sync
  filterModalEl.addEventListener('show.bs.modal', ()=>{
    modalDept.value = state.department || '';
    modalFeatured.value = (state.featured === '' ? '' : String(state.featured));
    modalSort.value = state.sort || 'created_at';
    modalDir.value = state.direction || 'desc';
  });

  btnApplyFilters.addEventListener('click', ()=>{
    state.department = (modalDept.value || '').trim();
    state.featured = (modalFeatured.value === '' ? '' : modalFeatured.value);
    state.sort = modalSort.value || 'created_at';
    state.direction = modalDir.value || 'desc';
    state.page = 1;
    filterModal.hide();
    loadActive(true);
  });

  btnReset.addEventListener('click', ()=>{
    state.q=''; state.department=''; state.featured='';
    state.sort='created_at'; state.direction='desc';
    state.perPage=20; state.page=1;

    searchInput.value='';
    perPageSel.value='20';
    modalDept.value=''; modalFeatured.value='';
    modalSort.value='created_at'; modalDir.value='desc';

    loadActive(true);
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
    binState.q=''; binState.perPage=20; binState.page=1;
    binSearchInput.value=''; binPerPageSel.value='20';
    loadBin(true);
  });

  // Add
  btnAdd?.addEventListener('click', ()=>{
    if(!canWrite) return;
    resetForm();
    achModalTitle.textContent = 'Add Achievement';
    achForm.dataset.mode = 'edit';
    setFormReadonly(false);
    saveBtn.style.display = '';
    achModal.show();
    setTimeout(()=>{ try{ quill.focus(); }catch(_){} }, 150);
  });

  // Actions (Active table)
  document.addEventListener('click', async (e)=>{
    const btn = e.target.closest('#pane-active button[data-action]');
    if(!btn) return;

    const tr = btn.closest('tr');
    const id = tr?.dataset?.id;
    if(!id) return;

    const act = btn.dataset.action;

    const toggle = btn.closest('.dropdown')?.querySelector('.dd-toggle');
    if(toggle){ try{ bootstrap.Dropdown.getOrCreateInstance(toggle).hide(); }catch(_){} }

    if(act === 'view' || act === 'edit'){
      showInlineLoading(true);
      try{
        const data = await fetchOne(id);
        resetForm();
        fillForm(data || {});
        const viewOnly = (act==='view');
        achModalTitle.textContent = viewOnly ? 'View Achievement' : 'Edit Achievement';
        achForm.dataset.mode = viewOnly ? 'view' : 'edit';
        setFormReadonly(viewOnly);
        saveBtn.style.display = viewOnly ? 'none' : '';
        imageRemoveWrap.style.display = viewOnly ? 'none' : (currentImageWrap.style.display === 'none' ? 'none' : '');
        currentAttachmentsList.querySelectorAll('.att-remove').forEach(x=>{
          x.closest('.form-check').style.display = viewOnly ? 'none' : '';
          x.disabled = viewOnly;
          x.checked = false;
        });
        attachmentsModeWrap.style.display = viewOnly ? 'none' : (currentAttachmentsWrap.style.display === 'none' ? 'none' : '');
        achModal.show();
      }catch(ex){ err(ex.message); }
      finally{ showInlineLoading(false); }
      return;
    }

    if(act === 'toggleFeatured'){
      if(!canWrite){ err('You do not have permission for this action'); return; }
      showInlineLoading(true);
      try{
        const res = await fetch(API.toggleFeatured(id), {
          method:'PATCH',
          headers: authHeaders({ 'Content-Type':'application/json' })
        });
        const js = await res.json().catch(()=>({}));
        if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Toggle featured failed');
        ok('Featured toggled');
        await loadActive(false);
      }catch(ex){ err(ex.message); }
      finally{ showInlineLoading(false); }
      return;
    }

    if(act === 'delete'){
      if(!canWrite){ err('You do not have permission for this action'); return; }
      const conf = await Swal.fire({
        title:'Move to Bin?',
        text:'This will soft delete the achievement.',
        icon:'warning',
        showCancelButton:true,
        confirmButtonText:'Delete',
        confirmButtonColor:'#ef4444'
      });
      if(!conf.isConfirmed) return;

      showInlineLoading(true);
      try{
        const res = await fetch(API.destroy(id), { method:'DELETE', headers: authHeaders() });
        const js = await res.json().catch(()=>({}));
        if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Delete failed');
        ok('Moved to Bin');
        await loadActive(false);
        if(binLoaded) await loadBin(false);
      }catch(ex){ err(ex.message); }
      finally{ showInlineLoading(false); }
    }
  });

  // Actions (Bin table)
  document.addEventListener('click', async (e)=>{
    const btn = e.target.closest('#pane-bin button[data-bin-action]');
    if(!btn) return;

    if(!binLoaded){ err('Bin not loaded yet. Click Bin tab once.'); return; }

    const tr = btn.closest('tr');
    const id = tr?.dataset?.id;
    if(!id) return;

    const act = btn.dataset.binAction;

    const toggle = btn.closest('.dropdown')?.querySelector('.dd-toggle');
    if(toggle){ try{ bootstrap.Dropdown.getOrCreateInstance(toggle).hide(); }catch(_){} }

    if(!canWrite){ err('You do not have permission for this action'); return; }

    if(act === 'restore'){
      const conf = await Swal.fire({
        title:'Restore achievement?',
        text:'This will restore the record from Bin.',
        icon:'question',
        showCancelButton:true,
        confirmButtonText:'Restore'
      });
      if(!conf.isConfirmed) return;

      showInlineLoading(true);
      try{
        const res = await fetch(API.restore(id), {
          method:'POST',
          headers: authHeaders({ 'Content-Type':'application/json' })
        });
        const js = await res.json().catch(()=>({}));
        if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Restore failed');
        ok('Restored');
        await loadActive(false);
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
        const res = await fetch(API.force(id), { method:'DELETE', headers: authHeaders() });
        const js = await res.json().catch(()=>({}));
        if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Permanent delete failed');
        ok('Deleted permanently');
        await loadBin(false);
        await loadActive(false);
      }catch(ex){ err(ex.message); }
      finally{ showInlineLoading(false); }
    }
  });

  // Empty Bin (current page)
  btnEmptyBin?.addEventListener('click', async ()=>{
    if(!canWrite){ err('You do not have permission for this action'); return; }
    if(!binLoaded){ err('Bin not loaded yet. Click Bin tab once.'); return; }
    if(!binState.items.length){ err('No items on this page.'); return; }

    const conf = await Swal.fire({
      title:'Delete this page permanently?',
      text:'This will permanently delete ONLY the items currently loaded on this page (no bulk endpoint).',
      icon:'warning',
      showCancelButton:true,
      confirmButtonText:'Delete Page Items',
      confirmButtonColor:'#ef4444'
    });
    if(!conf.isConfirmed) return;

    showInlineLoading(true);
    try{
      for(const r of binState.items){
        const id = r.uuid || r.id || r.slug;
        if(!id) continue;
        const res = await fetch(API.force(id), { method:'DELETE', headers: authHeaders() });
        const js = await res.json().catch(()=>({}));
        if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed while emptying page');
      }
      ok('Deleted page items');
      await loadBin(false);
      await loadActive(false);
    }catch(ex){
      err(ex.message);
    }finally{
      showInlineLoading(false);
    }
  });

  // Save (Add/Edit)
  achForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    if(achForm.dataset.mode === 'view') return;

    if(!title.value.trim()){ title.focus(); return; }

    const bodyHtml = (quill.root.innerHTML || '').trim();
    const bodyPlain = quill.getText().trim();
    if(!bodyPlain){
      err('Body is required');
      try{ quill.focus(); }catch(_){}
      return;
    }

    let metaObj = null;
    try{ metaObj = parseJsonOrThrow(metadata.value); }
    catch(ex){ err(ex.message); metadata.focus(); return; }

    const isEdit = !!achIdentifier.value;

    // ✅ selected department (id) from dropdown
    const deptId = (department_id.value || '').trim();

    const endpoint = isEdit
      ? API.update(achIdentifier.value)
      : (deptId ? API.storeForDept(deptId) : API.store);

    try{
      setButtonLoading(saveBtn, true);
      showInlineLoading(true);

      const fd = new FormData();

      // ✅ FIX: department_id was missing in payload (edit + create-by-dept).
      // Always send department_id when selected; backend can ignore if route-based.
      if(deptId) fd.append('department_id', deptId);

      fd.append('title', title.value.trim());

      const slugVal = (slug.value || '').trim();
      if(slugVal) fd.append('slug', slugVal);

      const pub = dtLocalToServer(published_at.value);
      if(pub) fd.append('published_at', pub);

      fd.append('body', bodyHtml);
      fd.append('is_featured_home', is_featured_home.checked ? '1' : '0');

      if(metaObj !== null) fd.append('metadata', JSON.stringify(metaObj));

      // image remove (edit)
      if(isEdit && image_remove.checked) fd.append('image_remove', '1');

      // image upload
      const imgFile = image?.files?.[0];
      if(imgFile) fd.append('image', imgFile);

      // attachments mode (edit)
      if(isEdit && currentAttachmentsWrap.style.display !== 'none'){
        fd.append('attachments_mode', attachments_mode.value || 'append');
      }

      // attachments remove (edit)
      if(isEdit){
        const remove = [];
        currentAttachmentsList.querySelectorAll('.att-remove:checked').forEach(x=>{
          const v = (x.value || '').toString().trim();
          if(v) remove.push(v);
        });
        remove.forEach(p => fd.append('attachments_remove[]', p));
      }

      // new attachments
      const files = Array.from(attachments?.files || []);
      if(files.length){
        for(const f of files) fd.append('attachments[]', f);
      }

      const method = 'POST';
      if(isEdit) fd.append('_method', 'PUT');

      const res = await fetch(endpoint, {
        method,
        headers: authHeaders(), // do not set content-type for FormData
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

      achModal.hide();
      ok(isEdit ? 'Achievement updated' : 'Achievement created');
      await loadActive(false);
      if(binLoaded) await loadBin(false);
    }catch(ex){
      err(ex.message);
    }finally{
      setButtonLoading(saveBtn, false);
      showInlineLoading(false);
      cleanupModalBackdrops();
    }
  });

  // init
  (async ()=>{
    showInlineLoading(true);
    try{
      await fetchMe();
      await loadDepartments();
      await loadActive(false);
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
