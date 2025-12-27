{{-- resources/views/modules/department/manageCurriculumSyllabuses.blade.php --}}
@section('title','Curriculum & Syllabus')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* Dropdowns inside table */
.table-wrap .dropdown{position:relative}
.dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
.dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:230px;z-index:5000}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color) !important}

/* Tabs */
.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface)
}
.tab-content,.tab-pane{overflow:visible}

/* Table Card */
.table-wrap.card{
  position:relative;
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
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

/* ✅ Slug column smaller + ellipsis (applies to all tabs) */
th.col-slug, td.col-slug{width:170px;max-width:170px}
td.col-slug{overflow:hidden}
td.col-slug code{
  display:inline-block;
  max-width:160px;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
  vertical-align:bottom;
}

/* (Optional) keep status compact when moved beside slug */
th.col-status, td.col-status{width:92px;max-width:92px}

/* Badges */
.badge-soft-primary{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  color:var(--primary-color)
}
.badge-soft-success{
  background:color-mix(in oklab, var(--success-color) 12%, transparent);
  color:var(--success-color)
}
.badge-soft-danger{
  background:color-mix(in oklab, var(--danger-color) 12%, transparent);
  color:var(--danger-color)
}
.badge-soft-muted{
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
  color:var(--muted-color)
}

/* Loading overlay */
.loading-overlay{
  position:fixed;
  top:0;left:0;width:100%;height:100%;
  background:rgba(0,0,0,0.45);
  display:flex;
  justify-content:center;
  align-items:center;
  z-index:9999;
  backdrop-filter:blur(2px)
}
.loading-spinner{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,0.3)
}
.spinner{
  width:40px;height:40px;
  border-radius:50%;
  border:4px solid rgba(148,163,184,0.3);
  border-top:4px solid var(--primary-color);
  animation:spin 1s linear infinite
}
@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

/* Button loading state */
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

/* Responsive toolbar */
@media (max-width: 768px){
  .cs-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .cs-toolbar .position-relative{min-width:100% !important}
  .toolbar-buttons{
    display:flex;
    gap:8px;
    flex-wrap:wrap
  }
  .toolbar-buttons .btn{
    flex:1;
    min-width:120px
  }
}

/* ✅ Horizontal scroll (keep) */
.table-responsive{
  display:block;
  width:100%;
  max-width:100%;
  overflow-x:auto !important;
  overflow-y:visible !important;
  -webkit-overflow-scrolling:touch;
  position:relative;
}
.table-responsive > .table{
  width:max-content;
  min-width:1150px;
}
.table-responsive th,
.table-responsive td{
  white-space:nowrap;
}
@media (max-width: 576px){
  .table-responsive > .table{ min-width:1080px; }
}

/* PDF preview box */
.pdf-box{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:var(--bg-soft, color-mix(in oklab, var(--surface) 88%, var(--bg-body)));
}
.pdf-box .pdf-top{
  display:flex;align-items:center;justify-content:space-between;
  gap:10px;
  padding:10px 12px;
  border-bottom:1px solid var(--line-soft);
}
.pdf-box iframe{
  width:100%;
  height:420px;
  border:0;
  background:#fff;
}
@media (max-width: 576px){
  .pdf-box iframe{ height:340px; }
}
</style>
@endpush

@section('content')
<div class="crs-wrap">

  {{-- Loading Overlay --}}
  <div id="globalLoading" class="loading-overlay" style="display:none;">
    @include('partials.overlay')
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#tab-active" role="tab" aria-selected="true">
        <i class="fa-solid fa-file-circle-check me-2"></i>Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-inactive" role="tab" aria-selected="false">
        <i class="fa-solid fa-file-circle-xmark me-2"></i>Inactive
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-trash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Trash
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- ACTIVE TAB --}}
    <div class="tab-pane fade show active" id="tab-active" role="tabpanel">

      {{-- Toolbar --}}
      <div class="row align-items-center g-2 mb-3 cs-toolbar panel">
        <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per Page</label>
            <select id="perPage" class="form-select" style="width:96px;">
              <option>10</option>
              <option selected>20</option>
              <option>50</option>
              <option>100</option>
            </select>
          </div>

          <div class="position-relative" style="min-width:280px;">
            <input id="searchInput" type="search" class="form-control ps-5" placeholder="Search by title or slug…">
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
          <div id="writeControls" style="display:none;">
            <button type="button" class="btn btn-primary" id="btnAddItem">
              <i class="fa fa-plus me-1"></i> Add PDF
            </button>
          </div>
        </div>
      </div>

      {{-- Table --}}
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:220px;">Department</th>
                  <th>Heading / Title</th>
                  <th class="col-slug">Slug</th>
                  <th class="col-status">Status</th>
                  <th style="width:170px;">PDF</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-active">
                <tr><td colspan="8" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          {{-- Empty --}}
          <div id="empty-active" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-file-pdf mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No active curriculum/syllabus found.</div>
          </div>

          {{-- Footer --}}
          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo-active">—</div>
            <nav><ul id="pager-active" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- INACTIVE TAB --}}
    <div class="tab-pane fade" id="tab-inactive" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:220px;">Department</th>
                  <th>Heading / Title</th>
                  <th class="col-slug">Slug</th>
                  <th class="col-status">Status</th>
                  <th style="width:170px;">PDF</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-inactive">
                <tr><td colspan="8" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-inactive" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-file-circle-xmark mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No inactive curriculum/syllabus found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo-inactive">—</div>
            <nav><ul id="pager-inactive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- TRASH TAB --}}
    <div class="tab-pane fade" id="tab-trash" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:220px;">Department</th>
                  <th>Heading / Title</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:170px;">PDF</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:170px;">Deleted</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-trash">
                <tr><td colspan="7" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-trash" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>Trash is empty.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo-trash">—</div>
            <nav><ul id="pager-trash" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-sliders me-2"></i>Filter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Department</label>
            <select id="modal_department" class="form-select">
              <option value="">All Departments</option>
            </select>
            <div class="form-text">Loaded from <code>/api/departments</code></div>
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="modal_sort" class="form-select">
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="title">Title A-Z</option>
              <option value="-title">Title Z-A</option>
              <option value="sort_order">Sort Order ↑</option>
              <option value="-sort_order">Sort Order ↓</option>
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
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form class="modal-content" id="itemForm">
      <div class="modal-header">
        <h5 class="modal-title" id="itemModalTitle">Add PDF</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="itemUuid">
        <input type="hidden" id="itemId">

        <div class="row g-3">
          <div class="col-lg-6">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Department <span class="text-danger">*</span></label>
                <select class="form-select" id="department_id" required>
                  <option value="">Select Department</option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Heading / Title <span class="text-danger">*</span></label>
                <input class="form-control" id="title" required maxlength="180" placeholder="e.g., B.Tech Syllabus">
                <div class="form-text">This is what appears as heading on the page.</div>
              </div>

              <div class="col-md-8">
                <label class="form-label">Slug (optional)</label>
                <input class="form-control" id="slug" maxlength="200" placeholder="b-tech-syllabus">
                <div class="form-text">Unique per department. Auto-generated from title if left empty.</div>
              </div>

              <div class="col-md-4">
                <label class="form-label">Sort Order</label>
                <input type="number" class="form-control" id="sort_order" min="0" max="1000000" value="0">
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select" id="active">
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">PDF File <span class="text-danger" id="pdfRequired">*</span></label>
                <input type="file" class="form-control" id="pdf" accept="application/pdf">
                <div class="form-text" id="pdfHelp">Upload a PDF (max 20MB).</div>
                <div class="small text-muted mt-2" id="currentPdfInfo" style="display:none;">
                  <i class="fa fa-paperclip me-1"></i>
                  <span id="currentPdfName">—</span>
                  <span class="mx-2">•</span>
                  <span id="currentPdfSize">—</span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="pdf-box">
              <div class="pdf-top">
                <div class="fw-semibold">
                  <i class="fa fa-file-pdf me-2"></i>Preview
                </div>
                <div class="d-flex align-items-center gap-2">
                  <button type="button" class="btn btn-light btn-sm" id="btnOpenPdf" style="display:none;">
                    <i class="fa fa-up-right-from-square me-1"></i>Open
                  </button>
                  <button type="button" class="btn btn-outline-primary btn-sm" id="btnDownloadPdf" style="display:none;">
                    <i class="fa fa-download me-1"></i>Download
                  </button>
                </div>
              </div>
              <iframe id="pdfFrame" src=""></iframe>
            </div>
            <div class="form-text mt-2">
              Preview uses the public file URL saved in <code>pdf_path</code> (same as your website preview).
            </div>
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
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
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
/**
 * ✅ FIX #1: Dropdown not showing
 * Root cause: we were toggling via custom handler AND Bootstrap was also toggling => immediate open+close.
 * Fix: keep a delegated toggler, but stopPropagation/preventDefault so Bootstrap doesn't toggle twice,
 * and force Popper to use fixed strategy so menus aren't clipped by overflow-x containers.
 */
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.dd-toggle');
  if (!btn) return;

  e.preventDefault();
  e.stopPropagation();

  try {
    const inst = bootstrap.Dropdown.getOrCreateInstance(btn, {
      autoClose: btn.getAttribute('data-bs-auto-close') || true,
      boundary: 'viewport',
      popperConfig: {
        strategy: 'fixed',
        modifiers: [
          { name: 'preventOverflow', options: { boundary: 'viewport' } },
          { name: 'flip', options: { boundary: 'viewport' } }
        ]
      }
    });
    inst.toggle();
  } catch (ex) {
    console.error('Dropdown toggle error', ex);
  }
});

// click outside should close (bootstrap will handle). also close on escape.
document.addEventListener('keydown', (e) => {
  if (e.key !== 'Escape') return;
  document.querySelectorAll('.dropdown-toggle.show').forEach(t => {
    try { bootstrap.Dropdown.getOrCreateInstance(t).hide(); } catch(_){}
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
  if (!token) { window.location.href = '/'; return; }

  const globalLoading = document.getElementById('globalLoading');
  const showGlobalLoading = (show) => { if (globalLoading) globalLoading.style.display = show ? 'flex' : 'none'; };

  function authHeaders(extra = {}) { return Object.assign({ 'Authorization': 'Bearer ' + token }, extra); }

  function escapeHtml(str) {
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[s]));
  }

  function debounce(fn, ms = 350) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }

  function bytes(n){
    const b = Number(n || 0);
    if (!b) return '—';
    const u = ['B','KB','MB','GB'];
    let i=0, v=b;
    while (v>=1024 && i<u.length-1){ v/=1024; i++; }
    return `${v.toFixed(i?1:0)} ${u[i]}`;
  }

  function slugify(s){
    return (s || '')
      .toString()
      .trim()
      .toLowerCase()
      .replace(/['"]/g,'')
      .replace(/[^a-z0-9]+/g,'-')
      .replace(/-+/g,'-')
      .replace(/^-|-$/g,'');
  }

  // Toasts
  const toastOk = new bootstrap.Toast(document.getElementById('toastSuccess'));
  const toastErr = new bootstrap.Toast(document.getElementById('toastError'));
  const okTxt = document.getElementById('toastSuccessText');
  const errTxt = document.getElementById('toastErrorText');
  const ok = m => { errTxt.textContent = ''; okTxt.textContent = m || 'Done'; toastOk.show(); };
  const err = m => { okTxt.textContent = ''; errTxt.textContent = m || 'Something went wrong'; toastErr.show(); };

  // Modal backdrop cleanup
  function cleanupModalBackdrops() {
    if (!document.querySelector('.modal.show')) {
      document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('padding-right');
      document.body.style.removeProperty('overflow');
    }
  }
  document.addEventListener('hidden.bs.modal', () => setTimeout(cleanupModalBackdrops, 80));

  // DOM refs
  const perPageSel = document.getElementById('perPage');
  const searchInput = document.getElementById('searchInput');
  const btnReset = document.getElementById('btnReset');
  const btnApplyFilters = document.getElementById('btnApplyFilters');
  const writeControls = document.getElementById('writeControls');
  const btnAddItem = document.getElementById('btnAddItem');

  const tbodyActive = document.getElementById('tbody-active');
  const tbodyInactive = document.getElementById('tbody-inactive');
  const tbodyTrash = document.getElementById('tbody-trash');

  const emptyActive = document.getElementById('empty-active');
  const emptyInactive = document.getElementById('empty-inactive');
  const emptyTrash = document.getElementById('empty-trash');

  const pagerActive = document.getElementById('pager-active');
  const pagerInactive = document.getElementById('pager-inactive');
  const pagerTrash = document.getElementById('pager-trash');

  // resultsInfo stays "—"
  document.getElementById('resultsInfo-active').textContent = '—';
  document.getElementById('resultsInfo-inactive').textContent = '—';
  document.getElementById('resultsInfo-trash').textContent = '—';

  // Filter modal
  const filterModalEl = document.getElementById('filterModal');
  const filterModal = new bootstrap.Modal(filterModalEl);
  const modalDepartment = document.getElementById('modal_department');
  const modalSort = document.getElementById('modal_sort');

  // Add/Edit modal
  const itemModalEl = document.getElementById('itemModal');
  const itemModal = new bootstrap.Modal(itemModalEl);
  const itemModalTitle = document.getElementById('itemModalTitle');
  const itemForm = document.getElementById('itemForm');
  const saveBtn = document.getElementById('saveBtn');

  const itemUuid = document.getElementById('itemUuid');
  const itemId = document.getElementById('itemId');
  const departmentIdSel = document.getElementById('department_id');
  const titleInput = document.getElementById('title');
  const slugInput = document.getElementById('slug');
  const sortOrderInput = document.getElementById('sort_order');
  const activeSel = document.getElementById('active');
  const pdfInput = document.getElementById('pdf');
  const pdfRequired = document.getElementById('pdfRequired');
  const pdfHelp = document.getElementById('pdfHelp');
  const currentPdfInfo = document.getElementById('currentPdfInfo');
  const currentPdfName = document.getElementById('currentPdfName');
  const currentPdfSize = document.getElementById('currentPdfSize');

  const pdfFrame = document.getElementById('pdfFrame');
  const btnOpenPdf = document.getElementById('btnOpenPdf');
  const btnDownloadPdf = document.getElementById('btnDownloadPdf');

  // Permissions
  const ACTOR = { role: '' };
  let canCreate=false, canEdit=false, canDelete=false;
  function computePermissions(){
    const r = (ACTOR.role || '').toLowerCase();
    const createDeleteRoles = ['admin','super_admin','director','principal'];
    const writeRoles = ['admin','super_admin','director','principal','hod','faculty','technical_assistant','it_person'];

    canCreate = createDeleteRoles.includes(r);
    canDelete = createDeleteRoles.includes(r);
    canEdit   = writeRoles.includes(r);

    if (writeControls) writeControls.style.display = canCreate ? 'flex' : 'none';
  }

  async function fetchMe(){
    try{
      const res = await fetch('/api/users/me', { headers: authHeaders() });
      if (res.ok){
        const js = await res.json().catch(()=> ({}));
        if (js?.success && js?.data?.role) ACTOR.role = String(js.data.role).toLowerCase();
      }
      if (!ACTOR.role){
        ACTOR.role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
      }
    }catch(_){
      ACTOR.role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
    }finally{
      computePermissions();
    }
  }

  // State
  const state = {
    departments: [],
    filters: { q: '', department_id: '', sort: '-created_at' },
    perPage: parseInt(perPageSel.value, 10) || 20,
    tabs: {
      active:   { page: 1, lastPage: 1, items: [] },
      inactive: { page: 1, lastPage: 1, items: [] },
      trash:    { page: 1, lastPage: 1, items: [] },
    }
  };

  function deptLabel(d){
    return d?.title || d?.name || d?.slug || (d?.id ? `Department #${d.id}` : 'Department');
  }

  function renderDepartmentOptions(){
    const opts = [`<option value="">All Departments</option>`]
      .concat(state.departments.map(d => {
        const id = d?.id;
        if (id === undefined || id === null) return '';
        return `<option value="${escapeHtml(String(id))}">${escapeHtml(deptLabel(d))}</option>`;
      })).join('');

    modalDepartment.innerHTML = opts;

    let f = `<option value="">Select Department</option>`;
    state.departments.forEach(d => {
      const id = d?.id;
      if (id === undefined || id === null) return;
      f += `<option value="${escapeHtml(String(id))}">${escapeHtml(deptLabel(d))}</option>`;
    });
    departmentIdSel.innerHTML = f;
  }

  async function loadDepartments(){
    try{
      const res = await fetch('/api/departments?per_page=200', { headers: authHeaders() });
      if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }
      const js = await res.json().catch(()=> ({}));
      const arr = Array.isArray(js.data) ? js.data : (Array.isArray(js.departments) ? js.departments : []);
      state.departments = arr || [];
      renderDepartmentOptions();
    }catch(e){
      console.error(e);
      state.departments = [];
      renderDepartmentOptions();
    }
  }

  const getActiveTabKey = () => {
    const activeLink = document.querySelector('.nav-tabs .nav-link.active');
    const href = activeLink?.getAttribute('href') || '#tab-active';
    if (href === '#tab-inactive') return 'inactive';
    if (href === '#tab-trash') return 'trash';
    return 'active';
  };

  function buildUrl(tabKey){
    const params = new URLSearchParams();
    params.set('per_page', String(state.perPage));
    params.set('page', String(state.tabs[tabKey].page));

    const q = state.filters.q.trim();
    if (q) params.set('q', q);

    const dept = (state.filters.department_id || '').toString().trim();
    if (dept) params.set('department', dept);

    const s = state.filters.sort || '-created_at';
    const dir = s.startsWith('-') ? 'desc' : 'asc';
    const sort = s.startsWith('-') ? s.slice(1) : s;
    params.set('sort', sort);
    params.set('direction', dir);

    if (tabKey === 'active') params.set('active', '1');
    if (tabKey === 'inactive') params.set('active', '0');
    if (tabKey === 'trash') params.set('only_trashed', '1');

    return `/api/curriculum-syllabuses?${params.toString()}`;
  }

  async function downloadViaApi(uuid, fallbackName='curriculum-syllabus.pdf'){
    showGlobalLoading(true);
    try{
      const res = await fetch(`/api/curriculum-syllabuses/${encodeURIComponent(uuid)}/download`, {
        headers: authHeaders()
      });
      if (!res.ok) {
        const js = await res.json().catch(()=> ({}));
        throw new Error(js?.message || 'Download failed');
      }
      const blob = await res.blob();
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = fallbackName;
      document.body.appendChild(a);
      a.click();
      a.remove();
      setTimeout(()=> URL.revokeObjectURL(url), 1200);
    }finally{
      showGlobalLoading(false);
    }
  }

  function renderPager(tabKey){
    const pagerEl = tabKey === 'active' ? pagerActive : (tabKey === 'inactive' ? pagerInactive : pagerTrash);
    const st = state.tabs[tabKey];
    const page = st.page;
    const totalPages = st.lastPage || 1;

    const item = (p, label, dis=false, act=false) => {
      if (dis) return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
      if (act) return `<li class="page-item active"><span class="page-link">${label}</span></li>`;
      return `<li class="page-item"><a class="page-link" href="#" data-page="${p}" data-tab="${tabKey}">${label}</a></li>`;
    };

    let html = '';
    html += item(Math.max(1, page - 1), 'Previous', page <= 1);
    const start = Math.max(1, page - 2);
    const end = Math.min(totalPages, page + 2);
    for (let p = start; p <= end; p++) html += item(p, p, false, p === page);
    html += item(Math.min(totalPages, page + 1), 'Next', page >= totalPages);

    pagerEl.innerHTML = html;
  }

  function setEmpty(tabKey, show){
    const el = tabKey==='active' ? emptyActive : (tabKey==='inactive' ? emptyInactive : emptyTrash);
    if (el) el.style.display = show ? '' : 'none';
  }

  function formatDate(s){
    if (!s) return '—';
    return String(s);
  }

  function rowDepartment(r){
    return r?.department_title || r?.department_slug || (r?.department_id ? `Department #${r.department_id}` : '—');
  }

  /**
   * ✅ Status column is beside Slug now + Slug is compact with ellipsis (layout-only change)
   * ✅ Status toggle remains inside dropdown actions (no behavior change)
   */
  function renderTable(tabKey){
    const tbody = tabKey==='active' ? tbodyActive : (tabKey==='inactive' ? tbodyInactive : tbodyTrash);
    const rows = state.tabs[tabKey].items || [];
    if (!tbody) return;

    if (!rows.length){
      tbody.innerHTML = '';
      setEmpty(tabKey, true);
      renderPager(tabKey);
      return;
    }
    setEmpty(tabKey, false);

    tbody.innerHTML = rows.map(r => {
      const uuid = r.uuid || '';
      const title = r.title || '—';
      const slug = r.slug || '—';
      const pdfUrl = r.pdf_url || '';
      const sortOrder = (r.sort_order ?? 0);
      const updated = formatDate(r.updated_at);
      const deleted = formatDate(r.deleted_at);

      const statusCell = (tabKey === 'trash')
        ? `<span class="badge badge-soft-muted">Deleted</span>`
        : `<span class="badge ${r.active ? 'badge-soft-success' : 'badge-soft-danger'}">${r.active ? 'Active' : 'Inactive'}</span>`;

      const pdfCell = pdfUrl
        ? `<div class="d-flex align-items-center gap-2">
             <button type="button" class="btn btn-light btn-sm" data-action="preview" title="Preview">
               <i class="fa fa-eye"></i>
             </button>
             <button type="button" class="btn btn-outline-primary btn-sm" data-action="download" title="Download">
               <i class="fa fa-download"></i>
             </button>
           </div>`
        : `<span class="text-muted">—</span>`;

      const isActive = !!r.active;
      const toggleAction = isActive ? 'deactivate' : 'activate';
      const toggleIcon = isActive ? 'fa-toggle-off' : 'fa-toggle-on';
      const toggleLabel = isActive ? 'Mark Inactive' : 'Mark Active';

      let actions = `
        <div class="dropdown text-end">
          <button type="button" class="btn btn-light btn-sm dd-toggle"
                  data-bs-toggle="dropdown"
                  data-bs-auto-close="true"
                  aria-expanded="false" title="Actions">
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button type="button" class="dropdown-item" data-action="view">
              <i class="fa fa-eye"></i> View
            </button></li>`;

      if (canEdit && tabKey !== 'trash'){
        actions += `<li><button type="button" class="dropdown-item" data-action="edit">
          <i class="fa fa-pen-to-square"></i> Edit
        </button></li>`;
      }

      if (pdfUrl){
        actions += `<li><button type="button" class="dropdown-item" data-action="open">
          <i class="fa fa-up-right-from-square"></i> Open PDF
        </button></li>
        <li><button type="button" class="dropdown-item" data-action="download">
          <i class="fa fa-download"></i> Download PDF
        </button></li>`;
      }

      // ✅ status toggle INSIDE actions dropdown
      if (canEdit && tabKey !== 'trash'){
        actions += `<li><hr class="dropdown-divider"></li>
          <li><button type="button" class="dropdown-item" data-action="${toggleAction}">
            <i class="fa ${toggleIcon}"></i> ${toggleLabel}
          </button></li>`;
      }

      if (tabKey !== 'trash'){
        if (canDelete){
          actions += `<li><button type="button" class="dropdown-item text-danger" data-action="delete">
            <i class="fa fa-trash"></i> Delete
          </button></li>`;
        }
      } else {
        actions += `<li><hr class="dropdown-divider"></li>
          <li><button type="button" class="dropdown-item" data-action="restore">
            <i class="fa fa-rotate-left"></i> Restore
          </button></li>`;
        if (canDelete){
          actions += `<li><button type="button" class="dropdown-item text-danger" data-action="force">
            <i class="fa fa-skull-crossbones"></i> Delete Permanently
          </button></li>`;
        }
      }

      actions += `</ul></div>`;

      if (tabKey === 'trash'){
        return `
          <tr data-uuid="${escapeHtml(uuid)}">
            <td>${escapeHtml(rowDepartment(r))}</td>
            <td class="fw-semibold">${escapeHtml(title)}</td>
            <td class="col-slug"><code>${escapeHtml(slug)}</code></td>
            <td>${pdfCell}</td>
            <td>${escapeHtml(String(sortOrder))}</td>
            <td>${escapeHtml(deleted)}</td>
            <td class="text-end">${actions}</td>
          </tr>`;
      }

      return `
        <tr data-uuid="${escapeHtml(uuid)}">
          <td>${escapeHtml(rowDepartment(r))}</td>
          <td class="fw-semibold">${escapeHtml(title)}</td>
          <td class="col-slug"><code>${escapeHtml(slug)}</code></td>
          <td class="col-status">${statusCell}</td>
          <td>${pdfCell}</td>
          <td>${escapeHtml(String(sortOrder))}</td>
          <td>${escapeHtml(updated)}</td>
          <td class="text-end">${actions}</td>
        </tr>`;
    }).join('');

    renderPager(tabKey);
  }

  async function loadTab(tabKey, showOverlay=true){
    try{
      if (showOverlay) showGlobalLoading(true);

      const url = buildUrl(tabKey);
      const res = await fetch(url, { headers: authHeaders() });

      if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

      const js = await res.json().catch(()=> ({}));
      if (!res.ok) throw new Error(js?.message || 'Failed to load list');

      const items = Array.isArray(js.data) ? js.data : [];
      const p = js.pagination || {};
      state.tabs[tabKey].items = items;
      state.tabs[tabKey].lastPage = parseInt(p.last_page || 1, 10) || 1;

      renderTable(tabKey);
    }catch(e){
      console.error(e);
      state.tabs[tabKey].items = [];
      state.tabs[tabKey].lastPage = 1;
      renderTable(tabKey);
      err(e.message || 'Failed');
    }finally{
      if (showOverlay) showGlobalLoading(false);
    }
  }

  function reloadCurrent(){
    const tabKey = getActiveTabKey();
    loadTab(tabKey, true);
  }

  // Pager click
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a.page-link[data-page]');
    if (!a) return;
    e.preventDefault();
    const tab = a.dataset.tab;
    const p = parseInt(a.dataset.page, 10);
    if (!tab || Number.isNaN(p)) return;
    if (p === state.tabs[tab].page) return;
    state.tabs[tab].page = p;
    loadTab(tab, true);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // Search
  const onSearch = debounce(() => {
    state.filters.q = (searchInput.value || '').trim();
    state.tabs.active.page = 1;
    state.tabs.inactive.page = 1;
    state.tabs.trash.page = 1;
    reloadCurrent();
  }, 320);
  searchInput.addEventListener('input', onSearch);

  // Per page
  perPageSel.addEventListener('change', () => {
    state.perPage = parseInt(perPageSel.value, 10) || 20;
    state.tabs.active.page = 1;
    state.tabs.inactive.page = 1;
    state.tabs.trash.page = 1;
    reloadCurrent();
  });

  // Filter modal open -> sync
  filterModalEl.addEventListener('show.bs.modal', () => {
    modalDepartment.value = state.filters.department_id || '';
    modalSort.value = state.filters.sort || '-created_at';
  });

  // Apply filters
  btnApplyFilters.addEventListener('click', () => {
    state.filters.department_id = modalDepartment.value || '';
    state.filters.sort = modalSort.value || '-created_at';
    state.tabs.active.page = 1;
    state.tabs.inactive.page = 1;
    state.tabs.trash.page = 1;
    filterModal.hide();
    reloadCurrent();
  });

  // Reset filters
  btnReset.addEventListener('click', () => {
    state.filters.q = '';
    state.filters.department_id = '';
    state.filters.sort = '-created_at';
    state.perPage = 20;

    searchInput.value = '';
    perPageSel.value = '20';
    modalDepartment.value = '';
    modalSort.value = '-created_at';

    state.tabs.active.page = 1;
    state.tabs.inactive.page = 1;
    state.tabs.trash.page = 1;

    reloadCurrent();
  });

  // Tab switch -> load relevant list
  document.querySelector('a[href="#tab-active"]')?.addEventListener('shown.bs.tab', () => loadTab('active', true));
  document.querySelector('a[href="#tab-inactive"]')?.addEventListener('shown.bs.tab', () => loadTab('inactive', true));
  document.querySelector('a[href="#tab-trash"]')?.addEventListener('shown.bs.tab', () => loadTab('trash', true));

  // Add/Edit/View modal helpers
  let filePreviewUrl = null;

  function setBtnLoading(btn, loading){
    if (!btn) return;
    if (loading){ btn.disabled = true; btn.classList.add('btn-loading'); }
    else { btn.disabled = false; btn.classList.remove('btn-loading'); }
  }

  function resetForm(){
    itemForm.reset();
    itemUuid.value = '';
    itemId.value = '';
    pdfFrame.src = '';
    btnOpenPdf.style.display = 'none';
    btnDownloadPdf.style.display = 'none';
    currentPdfInfo.style.display = 'none';
    currentPdfName.textContent = '—';
    currentPdfSize.textContent = '—';

    if (filePreviewUrl) { try { URL.revokeObjectURL(filePreviewUrl); } catch(_){} }
    filePreviewUrl = null;

    activeSel.value = '1';
    sortOrderInput.value = '0';

    Array.from(itemForm.querySelectorAll('input,select,textarea,button')).forEach(el => {
      if (el.id === 'itemUuid' || el.id === 'itemId') return;
      if (el.id === 'btnOpenPdf' || el.id === 'btnDownloadPdf') return;
      el.disabled = false;
      el.readOnly = false;
    });

    saveBtn.style.display = '';
    itemForm.dataset.mode = 'edit';

    pdfRequired.style.display = 'inline';
    pdfHelp.textContent = 'Upload a PDF (max 20MB).';
  }

  function fillFormFromRow(r, viewOnly=false){
    itemUuid.value = r.uuid || '';
    itemId.value = r.id || '';
    departmentIdSel.value = (r.department_id ?? '') ? String(r.department_id) : '';
    titleInput.value = r.title || '';
    slugInput.value = r.slug || '';
    sortOrderInput.value = String(r.sort_order ?? 0);
    activeSel.value = r.active ? '1' : '0';

    if (r.pdf_url){
      pdfFrame.src = r.pdf_url;
      btnOpenPdf.style.display = '';
      btnDownloadPdf.style.display = '';
      btnOpenPdf.onclick = () => window.open(r.pdf_url, '_blank', 'noopener');

      const name = r.original_name || (r.slug ? (r.slug + '.pdf') : 'syllabus.pdf');
      btnDownloadPdf.onclick = () => downloadViaApi(r.uuid, name);

      currentPdfInfo.style.display = '';
      currentPdfName.textContent = r.original_name || '—';
      currentPdfSize.textContent = bytes(r.file_size);
    } else {
      pdfFrame.src = '';
      btnOpenPdf.style.display = 'none';
      btnDownloadPdf.style.display = 'none';
      currentPdfInfo.style.display = 'none';
    }

    pdfRequired.style.display = 'none';
    pdfHelp.textContent = 'Choose a PDF only if you want to replace the existing file.';

    if (viewOnly){
      Array.from(itemForm.querySelectorAll('input,select,textarea')).forEach(el => {
        if (el.id === 'itemUuid' || el.id === 'itemId') return;
        if (el.type === 'file') el.disabled = true;
        else if (el.tagName === 'SELECT') el.disabled = true;
        else el.readOnly = true;
      });
      saveBtn.style.display = 'none';
      itemForm.dataset.mode = 'view';
    } else {
      saveBtn.style.display = '';
      itemForm.dataset.mode = 'edit';
    }
  }

  function findRowByUuid(uuid){
    const all = [
      ...(state.tabs.active.items || []),
      ...(state.tabs.inactive.items || []),
      ...(state.tabs.trash.items || []),
    ];
    return all.find(x => x?.uuid === uuid) || null;
  }

  // Auto slug from title when creating
  titleInput.addEventListener('input', debounce(() => {
    if (itemForm.dataset.mode !== 'edit') return;
    if (itemUuid.value) return;
    if (!slugInput.value.trim()){
      slugInput.value = slugify(titleInput.value);
    }
  }, 180));

  // Preview selected file instantly
  pdfInput.addEventListener('change', () => {
    const f = pdfInput.files?.[0];
    if (!f) return;

    if (filePreviewUrl) { try { URL.revokeObjectURL(filePreviewUrl); } catch(_){} }
    filePreviewUrl = URL.createObjectURL(f);
    pdfFrame.src = filePreviewUrl;
    btnOpenPdf.style.display = '';
    btnOpenPdf.onclick = () => window.open(filePreviewUrl, '_blank', 'noopener');

    btnDownloadPdf.style.display = 'none';

    currentPdfInfo.style.display = '';
    currentPdfName.textContent = f.name || 'selected.pdf';
    currentPdfSize.textContent = bytes(f.size);
  });

  // Add button
  btnAddItem?.addEventListener('click', () => {
    if (!canCreate) return;
    resetForm();
    itemModalTitle.textContent = 'Add PDF';
    itemForm.dataset.intent = 'create';
    itemModal.show();
  });

  // Row actions
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;

    const tr = btn.closest('tr');
    const uuid = tr?.dataset?.uuid;
    const act = btn.dataset.action;
    if (!uuid) return;

    const row = findRowByUuid(uuid);

    // close dropdown
    const toggle = btn.closest('.dropdown')?.querySelector('.dd-toggle');
    if (toggle) { try { bootstrap.Dropdown.getOrCreateInstance(toggle).hide(); } catch (_) {} }

    // Preview button in PDF column -> open modal view
    if (act === 'preview'){
      if (!row?.pdf_url) return;
      resetForm();
      itemModalTitle.textContent = 'View PDF';
      fillFormFromRow(row, true);
      itemModal.show();
      return;
    }

    // View/Edit
    if (act === 'view' || act === 'edit'){
      if (act === 'edit' && !canEdit) return;
      resetForm();
      itemModalTitle.textContent = act === 'view' ? 'View Curriculum & Syllabus' : 'Edit Curriculum & Syllabus';
      fillFormFromRow(row || {}, act === 'view');
      itemForm.dataset.intent = act === 'view' ? 'view' : 'edit';
      itemModal.show();
      return;
    }

    // Open PDF in new tab
    if (act === 'open'){
      if (row?.pdf_url) window.open(row.pdf_url, '_blank', 'noopener');
      return;
    }

    // Download
    if (act === 'download'){
      const name = row?.original_name || (row?.slug ? (row.slug + '.pdf') : 'curriculum-syllabus.pdf');
      await downloadViaApi(uuid, name);
      return;
    }

    // ✅ Mark Active/Inactive (toggle via dropdown)
    if (act === 'activate' || act === 'deactivate'){
      if (!canEdit) return;

      const toActive = (act === 'activate');
      const conf = await Swal.fire({
        title: 'Confirm',
        text: toActive ? 'Mark this as Active?' : 'Mark this as Inactive?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes'
      });
      if (!conf.isConfirmed) return;

      showGlobalLoading(true);
      try{
        const res = await fetch(`/api/curriculum-syllabuses/${encodeURIComponent(uuid)}/toggle-active`, {
          method: 'PATCH',
          headers: authHeaders()
        });
        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Toggle failed');

        ok('Status updated');

        // ensure row moves across tabs immediately
        state.tabs.active.page = 1;
        state.tabs.inactive.page = 1;
        await Promise.all([loadTab('active', false), loadTab('inactive', false)]);
      }catch(ex){
        err(ex.message || 'Failed');
      }finally{
        showGlobalLoading(false);
      }
      return;
    }

    // Delete (soft)
    if (act === 'delete'){
      if (!canDelete) return;
      const conf = await Swal.fire({
        title: 'Delete this PDF?',
        text: 'This will move the item to Trash.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#ef4444'
      });
      if (!conf.isConfirmed) return;

      showGlobalLoading(true);
      try{
        const res = await fetch(`/api/curriculum-syllabuses/${encodeURIComponent(uuid)}`, {
          method: 'DELETE',
          headers: authHeaders()
        });
        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Delete failed');

        ok('Moved to trash');
        await Promise.all([loadTab('active', false), loadTab('inactive', false), loadTab('trash', false)]);
      }catch(ex){
        err(ex.message || 'Failed');
      }finally{
        showGlobalLoading(false);
      }
      return;
    }

    // Restore
    if (act === 'restore'){
      const conf = await Swal.fire({
        title: 'Restore this item?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Restore'
      });
      if (!conf.isConfirmed) return;

      showGlobalLoading(true);
      try{
        const res = await fetch(`/api/curriculum-syllabuses/${encodeURIComponent(uuid)}/restore`, {
          method: 'POST',
          headers: authHeaders()
        });
        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Restore failed');

        ok('Restored');
        await Promise.all([loadTab('trash', false), loadTab('active', false), loadTab('inactive', false)]);
      }catch(ex){
        err(ex.message || 'Failed');
      }finally{
        showGlobalLoading(false);
      }
      return;
    }

    // Force delete
    if (act === 'force'){
      if (!canDelete) return;
      const conf = await Swal.fire({
        title: 'Delete permanently?',
        text: 'This cannot be undone (file will be removed).',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete Permanently',
        confirmButtonColor: '#ef4444'
      });
      if (!conf.isConfirmed) return;

      showGlobalLoading(true);
      try{
        const res = await fetch(`/api/curriculum-syllabuses/${encodeURIComponent(uuid)}/force`, {
          method: 'DELETE',
          headers: authHeaders()
        });
        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Force delete failed');

        ok('Deleted permanently');
        await loadTab('trash', false);
      }catch(ex){
        err(ex.message || 'Failed');
      }finally{
        showGlobalLoading(false);
      }
      return;
    }
  });

  // Form submit (create / edit)
  itemForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (itemForm.dataset.mode === 'view') return;

    const intent = itemForm.dataset.intent || 'create';
    const isEdit = intent === 'edit' && !!itemUuid.value;

    if (isEdit && !canEdit) return;
    if (!isEdit && !canCreate) return;

    const dept = (departmentIdSel.value || '').trim();
    const title = (titleInput.value || '').trim();
    const slug = (slugInput.value || '').trim();
    const active = (activeSel.value || '1');
    const sortOrder = (sortOrderInput.value || '0');

    if (!dept) { err('Department is required'); departmentIdSel.focus(); return; }
    if (!title) { err('Title is required'); titleInput.focus(); return; }

    const file = pdfInput.files?.[0] || null;
    if (!isEdit && !file) { err('PDF is required'); pdfInput.focus(); return; }

    const fd = new FormData();
    fd.append('department_id', dept);
    fd.append('title', title);
    if (slug) fd.append('slug', slug);
    fd.append('active', active === '1' ? '1' : '0');
    fd.append('sort_order', String(parseInt(sortOrder, 10) || 0));
    if (file) fd.append('pdf', file);

    const url = isEdit
      ? `/api/curriculum-syllabuses/${encodeURIComponent(itemUuid.value)}`
      : `/api/curriculum-syllabuses`;

    const method = 'POST';
    if (isEdit) fd.append('_method', 'PATCH');

    try{
      setBtnLoading(saveBtn, true);
      showGlobalLoading(true);

      const res = await fetch(url, {
        method,
        headers: authHeaders(), // DON'T set Content-Type for FormData
        body: fd
      });

      const js = await res.json().catch(()=> ({}));
      if (!res.ok || js.success === false){
        let msg = js?.message || 'Save failed';
        if (js?.errors){
          const k = Object.keys(js.errors)[0];
          if (k && js.errors[k] && js.errors[k][0]) msg = js.errors[k][0];
        }
        throw new Error(msg);
      }

      ok(isEdit ? 'Updated' : 'Created');
      itemModal.hide();

      state.tabs.active.page = 1;
      state.tabs.inactive.page = 1;
      state.tabs.trash.page = 1;
      await Promise.all([loadTab('active', false), loadTab('inactive', false), loadTab('trash', false)]);
    }catch(ex){
      err(ex.message || 'Failed');
    }finally{
      setBtnLoading(saveBtn, false);
      showGlobalLoading(false);
    }
  });

  // Init
  (async () => {
    showGlobalLoading(true);
    await fetchMe();
    await loadDepartments();
    await Promise.all([loadTab('active', false), loadTab('inactive', false), loadTab('trash', false)]);
    showGlobalLoading(false);
  })();
});
</script>
@endpush
