{{-- resources/views/modules/gallery/manageGallery.blade.php --}}
@section('title','Gallery')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
   Gallery module styling
   (structure inspired by your reference, not copied)
   ========================= */

/* Wrapper */
.gl-wrap{max-width:1140px;margin:16px auto 44px;overflow:visible}
.gl-panel{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:16px;
  box-shadow:var(--shadow-2);
  padding:14px;
}

/* Dropdowns inside table */
.table-wrap .dropdown{position:relative}
.dropdown .dd-toggle{border-radius:10px}
.dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:230px;
  z-index:5000;
}
.dropdown-menu.show{display:block !important}
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

/* Table card */
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
.small{font-size:12.5px}

/* Responsive horizontal scroll */
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
  min-width:1220px;
}
.table-responsive th,
.table-responsive td{
  white-space:nowrap;
}

/* Thumb */
.g-thumb{
  width:44px;height:34px;
  border-radius:10px;
  object-fit:cover;
  border:1px solid var(--line-soft);
  background:#fff;
}
.g-title{font-weight:700;color:var(--ink)}
.g-sub{font-size:12px;color:var(--muted-color)}

/* Badges */
.badge-soft-primary{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  color:var(--primary-color)
}
.badge-soft-success{
  background:color-mix(in oklab, var(--success-color) 12%, transparent);
  color:var(--success-color)
}
.badge-soft-muted{
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
  color:var(--muted-color)
}
.badge-soft-warning{
  background:color-mix(in oklab, var(--warning-color, #f59e0b) 14%, transparent);
  color:var(--warning-color, #f59e0b)
}

/* Loading overlay */
.loading-overlay{
  position:fixed; inset:0;
  background:rgba(0,0,0,.45);
  display:flex; align-items:center; justify-content:center;
  z-index:9999;
  backdrop-filter:blur(2px);
}
.loading-spinner{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex; flex-direction:column;
  align-items:center; gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,.3);
}
.spinner{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,.3);
  border-top:4px solid var(--primary-color);
  animation:spin 1s linear infinite;
}
@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

/* Button loading */
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
  animation:spin 1s linear infinite;
}

/* Toolbar responsiveness */
@media (max-width: 768px){
  .gl-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .gl-toolbar .position-relative{min-width:100% !important}
  .gl-toolbar .toolbar-buttons{display:flex;gap:8px;flex-wrap:wrap}
  .gl-toolbar .toolbar-buttons .btn{flex:1;min-width:120px}
}

/* Preview box */
.preview-box{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:var(--bg-soft, color-mix(in oklab, var(--surface) 88%, var(--bg-body)));
}
.preview-box .top{
  display:flex;align-items:center;justify-content:space-between;
  gap:10px;padding:10px 12px;
  border-bottom:1px solid var(--line-soft);
}
.preview-box .body{padding:12px;}
.preview-box img{
  width:100%;max-height:300px;object-fit:cover;
  border-radius:12px;border:1px solid var(--line-soft);
  background:#fff;
}
.preview-meta{font-size:12.5px;color:var(--muted-color);margin-top:10px}

/* Inactive selector chip */
.chip{
  display:inline-flex;align-items:center;gap:8px;
  border:1px solid var(--line-strong);
  border-radius:999px;
  padding:6px 10px;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.chip select{
  border:0;background:transparent;
  outline:none;
  color:var(--ink);
  font-weight:600;
}
</style>
@endpush

@section('content')
<div class="gl-wrap">

  {{-- Loading Overlay --}}
  <div id="globalLoading" class="loading-overlay" style="display:none;">
    @include('partials.overlay')
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#tab-active" role="tab" aria-selected="true">
        <i class="fa-solid fa-image me-2"></i>Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-inactive" role="tab" aria-selected="false">
        <i class="fa-solid fa-circle-pause me-2"></i>Inactive
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

      {{-- Toolbar (controls apply across tabs) --}}
      <div class="row align-items-center g-2 mb-3 gl-toolbar gl-panel">
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
            <input id="searchInput" type="search" class="form-control ps-5" placeholder="Search by title, description…">
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>

          <div class="toolbar-buttons d-flex align-items-center gap-2">
            <button id="btnFilter" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
              <i class="fa fa-sliders me-1"></i>Filter
            </button>

            <button id="btnReset" class="btn btn-light">
              <i class="fa fa-rotate-left me-1"></i>Reset
            </button>
          </div>
        </div>

        <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
          <div id="writeControls" style="display:none;">
            <button type="button" class="btn btn-primary" id="btnAddItem">
              <i class="fa fa-plus me-1"></i> Add Image
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
                  <th>Image</th>
                  <th>Title</th>
                  <th style="width:160px;">Department</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:110px;">Featured</th>
                  <th style="width:150px;">Publish At</th>
                  <th style="width:90px;">Sort</th>
                  <th style="width:90px;">Views</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-active">
                <tr><td colspan="10" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-active" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-image mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No active gallery items found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo-active">—</div>
            <nav><ul id="pager-active" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- INACTIVE TAB --}}
    <div class="tab-pane fade" id="tab-inactive" role="tabpanel">

      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="text-muted small">
          <i class="fa fa-circle-info me-1"></i>
          Inactive shows <b>Draft</b> / <b>Archived</b> items.
        </div>
        <div class="chip">
          <i class="fa fa-filter"></i>
          <span class="small text-muted">Show:</span>
          <select id="inactiveStatus">
            <option value="draft" selected>Draft</option>
            <option value="archived">Archived</option>
          </select>
        </div>
      </div>

      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Image</th>
                  <th>Title</th>
                  <th style="width:160px;">Department</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:110px;">Featured</th>
                  <th style="width:150px;">Publish At</th>
                  <th style="width:90px;">Sort</th>
                  <th style="width:90px;">Views</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-inactive">
                <tr><td colspan="10" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-inactive" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-circle-pause mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No inactive gallery items found.</div>
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
                  <th>Image</th>
                  <th>Title</th>
                  <th style="width:160px;">Department</th>
                  <th style="width:150px;">Deleted</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:90px;">Sort</th>
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
            <label class="form-label">Sort By</label>
            <select id="modal_sort" class="form-select">
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="title">Title A-Z</option>
              <option value="-title">Title Z-A</option>
              <option value="sort_order">Sort Order ↑</option>
              <option value="-sort_order">Sort Order ↓</option>
              <option value="-publish_at">Publish At (Desc)</option>
              <option value="publish_at">Publish At (Asc)</option>
              <option value="-views_count">Views (High to Low)</option>
              <option value="views_count">Views (Low to High)</option>
            </select>
            <div class="form-text">Allowed sort fields follow the Gallery API.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Featured</label>
            <select id="modal_featured" class="form-select">
              <option value="">Any</option>
              <option value="1">Featured only</option>
              <option value="0">Not featured</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Department</label>
            <select id="modal_department" class="form-select">
              <option value="">All</option>
              <option value="__global__">Global (No Department)</option>
            </select>
            <div class="form-text">Shows department title in dropdown.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Visible Now</label>
            <select id="modal_visible_now" class="form-select">
              <option value="">Any</option>
              <option value="1">Visible now only (Published + within time window)</option>
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
    <form class="modal-content" id="itemForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="itemModalTitle">Add Gallery Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="itemUuid">
        <input type="hidden" id="itemId">

        <div class="row g-3">
          <div class="col-lg-6">
            <div class="row g-3">

              <div class="col-12">
                <label class="form-label">Department (optional)</label>
                <select id="department_id" class="form-select">
                  <option value="">Global (No Department)</option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Title (optional)</label>
                <input class="form-control" id="title" maxlength="255" placeholder="e.g., Annual Fest 2025">
              </div>

              <div class="col-12">
                <label class="form-label">Description (optional)</label>
                <textarea class="form-control" id="description" maxlength="500" rows="3" placeholder="Short caption…"></textarea>
              </div>

              <div class="col-12">
                <label class="form-label">Tags (optional)</label>
                <input class="form-control" id="tags" placeholder="e.g., fest, sports, seminar">
                <div class="form-text">Comma-separated. Will be stored as <code>tags_json</code>.</div>
              </div>

              <div class="col-md-4">
                <label class="form-label">Status</label>
                <select class="form-select" id="status">
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="archived">Archived</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Featured on Home</label>
                <select class="form-select" id="is_featured_home">
                  <option value="0">No</option>
                  <option value="1">Yes</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Sort Order</label>
                <input type="number" class="form-control" id="sort_order" min="0" max="1000000" value="0">
              </div>

              <div class="col-md-6">
                <label class="form-label">Publish At</label>
                <input type="datetime-local" class="form-control" id="publish_at">
              </div>

              <div class="col-md-6">
                <label class="form-label">Expire At</label>
                <input type="datetime-local" class="form-control" id="expire_at">
              </div>

              <div class="col-12">
                <label class="form-label">Image Upload <span class="text-danger" id="imgRequiredStar">*</span></label>
                <input type="file" class="form-control" id="image_file" accept="image/*">
                <div class="form-text">Uploads to <code>public/depy_uploads/gallery</code> (as per your controller).</div>
              </div>

              <div class="col-12">
                <label class="form-label">OR Image Path/URL</label>
                <input class="form-control" id="image" maxlength="255" placeholder="e.g., depy_uploads/gallery/global/file.jpg or https://...">
                <div class="form-text">If you provide a path/URL, upload is optional.</div>
              </div>

              <div class="col-12">
                <label class="form-label">Metadata (optional JSON)</label>
                <textarea class="form-control" id="metadata" rows="3" placeholder='{"album":"fest","camera":"Nikon"}'></textarea>
                <div class="form-text">If filled, must be valid JSON.</div>
              </div>

            </div>
          </div>

          <div class="col-lg-6">
            <div class="preview-box">
              <div class="top">
                <div class="fw-semibold">
                  <i class="fa fa-image me-2"></i>Image Preview
                </div>
                <div class="d-flex align-items-center gap-2">
                  <button type="button" class="btn btn-light btn-sm" id="btnOpenImage" style="display:none;">
                    <i class="fa fa-up-right-from-square me-1"></i>Open
                  </button>
                </div>
              </div>
              <div class="body">
                <img id="imagePreview" src="" alt="Preview" style="display:none;">
                <div id="imageEmpty" class="text-muted small" style="padding:12px;border:1px dashed var(--line-soft);border-radius:12px;">
                  No image selected.
                </div>
                <div class="preview-meta" id="imageMeta" style="display:none;">—</div>
              </div>
            </div>

            <div class="mt-3 text-muted small">
              <i class="fa fa-circle-info me-1"></i>
              “Active” tab loads <b>published</b> items. “Inactive” tab loads <b>draft/archived</b> using the selector above.
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
(() => {
  if (window.__GALLERY_MODULE_INIT__) return;
  window.__GALLERY_MODULE_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=300) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }
  function bytes(n){
    const b = Number(n || 0);
    if (!b) return '—';
    const u = ['B','KB','MB','GB'];
    let i=0, v=b;
    while (v>=1024 && i<u.length-1){ v/=1024; i++; }
    return `${v.toFixed(i?1:0)} ${u[i]}`;
  }
  function normalizeUrl(url){
    const u = (url || '').toString().trim();
    if (!u) return '';
    if (/^(data:|blob:|https?:\/\/)/i.test(u)) return u;
    if (u.startsWith('/')) return window.location.origin + u;
    return window.location.origin + '/' + u;
  }
  async function fetchWithTimeout(url, opts={}, ms=15000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try{ return await fetch(url, { ...opts, signal: ctrl.signal }); }
    finally{ clearTimeout(t); }
  }

  function badgeStatus(status){
    const s = (status || '').toString().toLowerCase();
    if (s === 'published') return `<span class="badge badge-soft-success">Published</span>`;
    if (s === 'draft') return `<span class="badge badge-soft-warning">Draft</span>`;
    if (s === 'archived') return `<span class="badge badge-soft-muted">Archived</span>`;
    return `<span class="badge badge-soft-muted">${esc(s || '—')}</span>`;
  }
  function badgeYesNo(v){
    return v ? `<span class="badge badge-soft-primary">Yes</span>` : `<span class="badge badge-soft-muted">No</span>`;
  }
  function dtLocal(s){
    if (!s) return '';
    const t = String(s).replace(' ', 'T');
    return t.length >= 16 ? t.slice(0,16) : t;
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const authHeaders = () => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    });

    const globalLoading = $('globalLoading');
    const showLoading = (v) => { if (globalLoading) globalLoading.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('toastSuccess');
    const toastErrEl = $('toastError');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { const el=$('toastSuccessText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('toastErrorText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    // UI refs
    const perPageSel = $('perPage');
    const searchInput = $('searchInput');
    const btnReset = $('btnReset');
    const btnApplyFilters = $('btnApplyFilters');
    const writeControls = $('writeControls');
    const btnAddItem = $('btnAddItem');

    const tbodyActive = $('tbody-active');
    const tbodyInactive = $('tbody-inactive');
    const tbodyTrash = $('tbody-trash');

    const emptyActive = $('empty-active');
    const emptyInactive = $('empty-inactive');
    const emptyTrash = $('empty-trash');

    const pagerActive = $('pager-active');
    const pagerInactive = $('pager-inactive');
    const pagerTrash = $('pager-trash');

    const infoActive = $('resultsInfo-active');
    const infoInactive = $('resultsInfo-inactive');
    const infoTrash = $('resultsInfo-trash');

    const inactiveStatusSel = $('inactiveStatus');

    // Filter modal
    const filterModalEl = $('filterModal');
    const filterModal = filterModalEl ? new bootstrap.Modal(filterModalEl) : null;
    const modalSort = $('modal_sort');
    const modalFeatured = $('modal_featured');
    const modalDepartment = $('modal_department');
    const modalVisibleNow = $('modal_visible_now');

    // Item modal
    const itemModalEl = $('itemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;
    const itemModalTitle = $('itemModalTitle');
    const itemForm = $('itemForm');
    const saveBtn = $('saveBtn');

    const itemUuid = $('itemUuid');
    const itemId = $('itemId');

    const departmentSel = $('department_id');
    const titleInput = $('title');
    const descInput = $('description');
    const tagsInput = $('tags');
    const statusSel = $('status');
    const featuredSel = $('is_featured_home');
    const sortOrderInput = $('sort_order');
    const publishAtInput = $('publish_at');
    const expireAtInput = $('expire_at');
    const imageFileInput = $('image_file');
    const imagePathInput = $('image');
    const metadataInput = $('metadata');
    const imgRequiredStar = $('imgRequiredStar');

    const imagePreview = $('imagePreview');
    const imageEmpty = $('imageEmpty');
    const imageMeta = $('imageMeta');
    const btnOpenImage = $('btnOpenImage');

    // permissions
    const ACTOR = { role: '' };
    let canCreate=false, canEdit=false, canDelete=false;

    function computePermissions(){
      const r = (ACTOR.role || '').toLowerCase();
      // align with your API middleware choices
      const writeRoles = ['admin','director','principal','hod','faculty','technical_assistant','it_person','super_admin'];
      const deleteRoles = ['admin','director','principal','super_admin'];

      canCreate = writeRoles.includes(r);
      canEdit   = writeRoles.includes(r);
      canDelete = deleteRoles.includes(r);

      if (writeControls) writeControls.style.display = canCreate ? 'flex' : 'none';
    }

    async function fetchMe(){
      try{
        const res = await fetchWithTimeout('/api/users/me', { headers: authHeaders() }, 8000);
        if (res.ok){
          const js = await res.json().catch(()=> ({}));
          const role = js?.data?.role || js?.role;
          if (role) ACTOR.role = String(role).toLowerCase();
        }
      }catch(_){}
      if (!ACTOR.role){
        ACTOR.role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
      }
      computePermissions();
    }

    // state
    const state = {
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      q: '',
      filters: { sort:'-created_at', featured:'', department:'', visible_now:'' },
      tabs: {
        active:   { page:1, lastPage:1, items:[] },
        inactive: { page:1, lastPage:1, items:[] },
        trash:    { page:1, lastPage:1, items:[] }
      }
    };

    const getTabKey = () => {
      const a = document.querySelector('.nav-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#tab-active';
      if (href === '#tab-inactive') return 'inactive';
      if (href === '#tab-trash') return 'trash';
      return 'active';
    };

    function setEmpty(tabKey, show){
      const el = tabKey==='active' ? emptyActive : (tabKey==='inactive' ? emptyInactive : emptyTrash);
      if (el) el.style.display = show ? '' : 'none';
    }

    function buildUrl(tabKey){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state.tabs[tabKey].page));

      const q = (state.q || '').trim();
      if (q) params.set('q', q);

      // sort
      const s = state.filters.sort || '-created_at';
      params.set('sort', s.startsWith('-') ? s.slice(1) : s);
      params.set('direction', s.startsWith('-') ? 'desc' : 'asc');

      // featured
      if (state.filters.featured !== '') params.set('featured', state.filters.featured);

      // department filter:
      // "" => all, "__global__" => only department_id IS NULL (client-side filter fallback)
      if (state.filters.department && state.filters.department !== '__global__') {
        // controller supports department identifier (id/uuid/slug) via ?department=
        params.set('department', state.filters.department);
      }

      // visible_now (API supports it)
      if (state.filters.visible_now) params.set('visible_now', '1');

      if (tabKey === 'trash') {
        // trash endpoint exists
        return `/api/gallery-trash?${params.toString()}`;
      }

      // tab status:
      if (tabKey === 'active') {
        params.set('status', 'published');
      } else {
        params.set('status', inactiveStatusSel?.value || 'draft');
      }

      return `/api/gallery?${params.toString()}`;
    }

    function renderPager(tabKey){
      const pagerEl = tabKey === 'active' ? pagerActive : (tabKey === 'inactive' ? pagerInactive : pagerTrash);
      if (!pagerEl) return;

      const st = state.tabs[tabKey];
      const page = st.page;
      const totalPages = st.lastPage || 1;

      const item = (p, label, dis=false, act=false) => {
        if (dis) return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
        if (act) return `<li class="page-item active"><span class="page-link">${label}</span></li>`;
        return `<li class="page-item"><a class="page-link" href="#" data-page="${p}" data-tab="${tabKey}">${label}</a></li>`;
      };

      let html = '';
      html += item(Math.max(1, page-1), 'Previous', page<=1);
      const start = Math.max(1, page-2), end = Math.min(totalPages, page+2);
      for (let p=start; p<=end; p++) html += item(p, p, false, p===page);
      html += item(Math.min(totalPages, page+1), 'Next', page>=totalPages);

      pagerEl.innerHTML = html;
    }

    function deptText(r){
      return r?.department_title ? esc(r.department_title) : '<span class="text-muted">—</span>';
    }

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

      const onlyGlobal = (state.filters.department === '__global__');

      const filtered = onlyGlobal
        ? rows.filter(r => !r.department_id)
        : rows;

      if (!filtered.length){
        tbody.innerHTML = '';
        setEmpty(tabKey, true);
        renderPager(tabKey);
        return;
      }

      tbody.innerHTML = filtered.map(r => {
        const uuid = r.uuid || '';
        const title = r.title || '—';
        const img = normalizeUrl(r.image_url || r.image || '');
        const status = r.status || 'draft';
        const featured = !!(r.is_featured_home ?? 0);
        const publishAt = r.publish_at || '—';
        const updated = r.updated_at || '—';
        const deleted = r.deleted_at || '—';
        const sortOrder = (r.sort_order ?? 0);
        const views = (r.views_count ?? 0);

        const thumb = img
          ? `<img class="g-thumb" src="${esc(img)}" alt="thumb" onerror="this.style.display='none'">`
          : `<span class="text-muted">—</span>`;

        const titleCell = `
          <div class="d-flex align-items-center gap-2">
            ${thumb}
          </div>
        `;

        const titleText = `
          <div>
            <div class="g-title">${esc(title)}</div>
            ${r.description ? `<div class="g-sub">${esc(r.description)}</div>` : `<div class="g-sub">—</div>`}
          </div>
        `;

        let actions = `
          <div class="dropdown text-end">
            <button type="button"
              class="btn btn-light btn-sm dd-toggle"
              data-bs-toggle="dropdown"
              data-bs-auto-close="true"
              aria-expanded="false" title="Actions">
              <i class="fa fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>`;

        if (tabKey !== 'trash' && canEdit){
          actions += `<li><button type="button" class="dropdown-item" data-action="edit"><i class="fa fa-pen-to-square"></i> Edit</button></li>`;
          actions += `<li><button type="button" class="dropdown-item" data-action="toggleFeatured"><i class="fa fa-star"></i> Toggle Featured</button></li>`;
        }

        if (tabKey !== 'trash'){
          if (canDelete){
            actions += `<li><hr class="dropdown-divider"></li>
              <li><button type="button" class="dropdown-item text-danger" data-action="delete"><i class="fa fa-trash"></i> Move to Trash</button></li>`;
          }
        } else {
          actions += `<li><hr class="dropdown-divider"></li>
            <li><button type="button" class="dropdown-item" data-action="restore"><i class="fa fa-rotate-left"></i> Restore</button></li>`;
          if (canDelete){
            actions += `<li><button type="button" class="dropdown-item text-danger" data-action="force"><i class="fa fa-skull-crossbones"></i> Delete Permanently</button></li>`;
          }
        }

        actions += `</ul></div>`;

        if (tabKey === 'trash'){
          return `
            <tr data-uuid="${esc(uuid)}">
              <td>${titleCell}</td>
              <td>${titleText}</td>
              <td>${deptText(r)}</td>
              <td>${esc(String(deleted))}</td>
              <td>${badgeStatus(status)}</td>
              <td>${esc(String(sortOrder))}</td>
              <td class="text-end">${actions}</td>
            </tr>`;
        }

        return `
          <tr data-uuid="${esc(uuid)}">
            <td>${titleCell}</td>
            <td>${titleText}</td>
            <td>${deptText(r)}</td>
            <td>${badgeStatus(status)}</td>
            <td>${badgeYesNo(featured)}</td>
            <td>${esc(String(publishAt))}</td>
            <td>${esc(String(sortOrder))}</td>
            <td>${esc(String(views))}</td>
            <td>${esc(String(updated))}</td>
            <td class="text-end">${actions}</td>
          </tr>`;
      }).join('');

      renderPager(tabKey);
    }

    async function loadTab(tabKey){
      const tbody = tabKey==='active' ? tbodyActive : (tabKey==='inactive' ? tbodyInactive : tbodyTrash);
      if (tbody){
        const cols = (tabKey==='trash') ? 7 : 10;
        tbody.innerHTML = `<tr><td colspan="${cols}" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;
      }

      try{
        const res = await fetchWithTimeout(buildUrl(tabKey), { headers: authHeaders() }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(js?.message || 'Failed to load');

        const items = Array.isArray(js.data) ? js.data : [];
        const p = js.pagination || js.meta || {};

        state.tabs[tabKey].items = items;
        state.tabs[tabKey].lastPage = parseInt(p.last_page || p.total_pages || 1, 10) || 1;

        const info = (p.total ? `${p.total} result(s)` : '—');
        if (tabKey === 'active' && infoActive) infoActive.textContent = info;
        if (tabKey === 'inactive' && infoInactive) infoInactive.textContent = info;
        if (tabKey === 'trash' && infoTrash) infoTrash.textContent = info;

        renderTable(tabKey);
      }catch(e){
        state.tabs[tabKey].items = [];
        state.tabs[tabKey].lastPage = 1;
        renderTable(tabKey);
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    function reloadAll(){
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      return Promise.all([loadTab('active'), loadTab('inactive'), loadTab('trash')]);
    }

    // pager clicks
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a.page-link[data-page]');
      if (!a) return;
      e.preventDefault();
      const tab = a.dataset.tab;
      const p = parseInt(a.dataset.page, 10);
      if (!tab || Number.isNaN(p)) return;
      if (p === state.tabs[tab].page) return;
      state.tabs[tab].page = p;
      loadTab(tab);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // search + per page
    searchInput?.addEventListener('input', debounce(() => {
      state.q = (searchInput.value || '').trim();
      reloadAll();
    }, 320));

    perPageSel?.addEventListener('change', () => {
      state.perPage = parseInt(perPageSel.value, 10) || 20;
      reloadAll();
    });

    // filter modal prefill
    filterModalEl?.addEventListener('show.bs.modal', () => {
      if (modalSort) modalSort.value = state.filters.sort || '-created_at';
      if (modalFeatured) modalFeatured.value = (state.filters.featured ?? '');
      if (modalDepartment) modalDepartment.value = (state.filters.department ?? '');
      if (modalVisibleNow) modalVisibleNow.value = (state.filters.visible_now ?? '');
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.sort = modalSort?.value || '-created_at';
      state.filters.featured = (modalFeatured?.value ?? '');
      state.filters.department = (modalDepartment?.value ?? '');
      state.filters.visible_now = (modalVisibleNow?.value ?? '');
      filterModal && filterModal.hide();
      reloadAll();
    });

    btnReset?.addEventListener('click', () => {
      state.q = '';
      state.filters = { sort:'-created_at', featured:'', department:'', visible_now:'' };
      state.perPage = 20;

      if (searchInput) searchInput.value = '';
      if (perPageSel) perPageSel.value = '20';

      if (modalSort) modalSort.value = '-created_at';
      if (modalFeatured) modalFeatured.value = '';
      if (modalDepartment) modalDepartment.value = '';
      if (modalVisibleNow) modalVisibleNow.value = '';

      reloadAll();
    });

    // tab triggers
    document.querySelector('a[href="#tab-active"]')?.addEventListener('shown.bs.tab', () => loadTab('active'));
    document.querySelector('a[href="#tab-inactive"]')?.addEventListener('shown.bs.tab', () => loadTab('inactive'));
    document.querySelector('a[href="#tab-trash"]')?.addEventListener('shown.bs.tab', () => loadTab('trash'));

    inactiveStatusSel?.addEventListener('change', () => {
      state.tabs.inactive.page = 1;
      loadTab('inactive');
    });

    // departments load (for dropdowns)
    async function loadDepartments(){
      try{
        const res = await fetchWithTimeout('/api/departments?per_page=200', { headers: authHeaders() }, 12000);
        if (!res.ok) return;
        const js = await res.json().catch(()=> ({}));
        const rows = Array.isArray(js.data) ? js.data : [];
        if (!rows.length) return;

        // filter modal department
        if (modalDepartment){
          // keep existing 2 options
          const keep = modalDepartment.innerHTML;
          modalDepartment.innerHTML = keep + rows.map(d => {
            const id = d.id ?? '';
            const title = d.title || d.name || d.slug || ('Dept #' + id);
            // allow id-based filter (works)
            return `<option value="${esc(String(id))}">${esc(String(title))}</option>`;
          }).join('');
        }

        // form department
        if (departmentSel){
          departmentSel.innerHTML = `<option value="">Global (No Department)</option>` + rows.map(d => {
            const id = d.id ?? '';
            const title = d.title || d.name || d.slug || ('Dept #' + id);
            return `<option value="${esc(String(id))}">${esc(String(title))}</option>`;
          }).join('');
        }
      }catch(_){}
    }

    // item modal preview
    let imgObjectUrl = null;

    function clearImagePreview(revoke=true){
      if (revoke && imgObjectUrl){
        try{ URL.revokeObjectURL(imgObjectUrl); }catch(_){}
      }
      imgObjectUrl = null;

      if (imagePreview){
        imagePreview.style.display = 'none';
        imagePreview.removeAttribute('src');
      }
      if (imageEmpty) imageEmpty.style.display = '';
      if (imageMeta){ imageMeta.style.display = 'none'; imageMeta.textContent = '—'; }
      if (btnOpenImage){ btnOpenImage.style.display = 'none'; btnOpenImage.onclick = null; }
    }

    function setImagePreview(url, metaText=''){
      const u = normalizeUrl(url);
      if (!u){ clearImagePreview(true); return; }

      if (imagePreview){
        imagePreview.style.display = '';
        imagePreview.src = u;
      }
      if (imageEmpty) imageEmpty.style.display = 'none';

      if (imageMeta){
        imageMeta.style.display = metaText ? '' : 'none';
        imageMeta.textContent = metaText || '';
      }
      if (btnOpenImage){
        btnOpenImage.style.display = '';
        btnOpenImage.onclick = () => window.open(u, '_blank', 'noopener');
      }
    }

    imageFileInput?.addEventListener('change', () => {
      const f = imageFileInput.files?.[0];
      if (!f) { return; }
      // file chosen => preview and clear path input (optional)
      if (imgObjectUrl){ try{ URL.revokeObjectURL(imgObjectUrl); }catch(_){ } }
      imgObjectUrl = URL.createObjectURL(f);
      setImagePreview(imgObjectUrl, `${f.name || 'image'} • ${bytes(f.size)}`);
    });

    imagePathInput?.addEventListener('input', debounce(() => {
      const v = (imagePathInput.value || '').trim();
      if (!v) return;
      // show preview from path
      setImagePreview(v, 'Using path/URL');
    }, 250));

    function setBtnLoading(btn, loading){
      if (!btn) return;
      btn.disabled = !!loading;
      btn.classList.toggle('btn-loading', !!loading);
    }

    function resetForm(){
      itemForm?.reset();
      itemUuid.value = '';
      itemId.value = '';

      clearImagePreview(true);

      if (imgRequiredStar) imgRequiredStar.style.display = '';
      // enable fields
      itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
        if (el.id === 'itemUuid' || el.id === 'itemId') return;
        if (el.type === 'file') el.disabled = false;
        else if (el.tagName === 'SELECT') el.disabled = false;
        else el.readOnly = false;
      });

      if (saveBtn) saveBtn.style.display = '';
      itemForm.dataset.mode = 'edit';
      itemForm.dataset.intent = 'create';
    }

    async function fetchItem(uuid, withTrashed=false){
      const qs = withTrashed ? '?with_trashed=1' : '';
      const res = await fetchWithTimeout(`/api/gallery/${encodeURIComponent(uuid)}${qs}`, { headers: authHeaders() }, 12000);
      const js = await res.json().catch(()=> ({}));
      if (!res.ok || js?.success === false) throw new Error(js?.message || 'Failed to load item');
      return js?.item || js?.data || js;
    }

    function fillFormFromItem(r, viewOnly=false){
      itemUuid.value = r.uuid || '';
      itemId.value = r.id || '';

      departmentSel.value = r.department_id ? String(r.department_id) : '';
      titleInput.value = r.title || '';
      descInput.value = r.description || '';
      statusSel.value = (r.status || 'draft');
      featuredSel.value = String((r.is_featured_home ?? 0) ? 1 : 0);
      sortOrderInput.value = String(r.sort_order ?? 0);
      publishAtInput.value = dtLocal(r.publish_at);
      expireAtInput.value = dtLocal(r.expire_at);

      // tags
      const tagsArr = Array.isArray(r.tags) ? r.tags : (Array.isArray(r.tags_json) ? r.tags_json : []);
      tagsInput.value = (tagsArr && tagsArr.length) ? tagsArr.join(', ') : '';

      // metadata
      const meta = r.metadata ?? null;
      metadataInput.value = meta ? (typeof meta === 'string' ? meta : JSON.stringify(meta, null, 2)) : '';

      // image
      imagePathInput.value = (r.image || '');
      clearImagePreview(true);
      setImagePreview(r.image_url || r.image || '', 'Current image');

      // image required star
      if (imgRequiredStar) imgRequiredStar.style.display = (r.uuid ? 'none' : '');

      if (viewOnly){
        itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
          if (el.id === 'itemUuid' || el.id === 'itemId') return;
          if (el.type === 'file') el.disabled = true;
          else if (el.tagName === 'SELECT') el.disabled = true;
          else el.readOnly = true;
        });
        if (saveBtn) saveBtn.style.display = 'none';
        itemForm.dataset.mode = 'view';
        itemForm.dataset.intent = 'view';
      } else {
        if (saveBtn) saveBtn.style.display = '';
        itemForm.dataset.mode = 'edit';
        itemForm.dataset.intent = 'edit';
      }
    }

    btnAddItem?.addEventListener('click', () => {
      if (!canCreate) return;
      resetForm();
      if (itemModalTitle) itemModalTitle.textContent = 'Add Gallery Item';
      itemForm.dataset.intent = 'create';
      itemModal && itemModal.show();
    });

    itemModalEl?.addEventListener('hidden.bs.modal', () => {
      if (imgObjectUrl){ try{ URL.revokeObjectURL(imgObjectUrl); }catch(_){ } imgObjectUrl=null; }
    });

    // row actions
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;

      const tr = btn.closest('tr');
      const uuid = tr?.dataset?.uuid;
      const act = btn.dataset.action;
      if (!uuid) return;

      // close dropdown
      const toggle = btn.closest('.dropdown')?.querySelector('.dd-toggle');
      if (toggle) { try { bootstrap.Dropdown.getOrCreateInstance(toggle).hide(); } catch (_) {} }

      const currentTab = getTabKey();
      const inTrash = (currentTab === 'trash');

      if (act === 'view' || act === 'edit'){
        if (act === 'edit' && !canEdit) return;

        showLoading(true);
        try{
          const item = await fetchItem(uuid, inTrash);
          resetForm();
          if (itemModalTitle) itemModalTitle.textContent = (act === 'view') ? 'View Gallery Item' : 'Edit Gallery Item';
          fillFormFromItem(item || {}, act === 'view');
          itemModal && itemModal.show();
        }catch(ex){
          err(ex?.message || 'Failed');
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'toggleFeatured'){
        if (!canEdit) return;
        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/gallery/${encodeURIComponent(uuid)}/toggle-featured`, {
            method: 'PATCH',
            headers: authHeaders()
          }, 12000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to toggle');
          ok('Featured updated');
          await reloadAll();
        }catch(ex){
          err(ex?.message || 'Failed');
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'delete'){
        if (!canDelete) return;
        const conf = await Swal.fire({
          title: 'Move to trash?',
          text: 'This will soft-delete the item.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Move to Trash',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/gallery/${encodeURIComponent(uuid)}`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 12000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Delete failed');
          ok('Moved to trash');
          await reloadAll();
        }catch(ex){
          err(ex?.message || 'Failed');
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'restore'){
        const conf = await Swal.fire({
          title: 'Restore this item?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Restore'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/gallery/${encodeURIComponent(uuid)}/restore`, {
            method: 'POST',
            headers: authHeaders()
          }, 12000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Restore failed');
          ok('Restored');
          await reloadAll();
        }catch(ex){
          err(ex?.message || 'Failed');
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'force'){
        if (!canDelete) return;
        const conf = await Swal.fire({
          title: 'Delete permanently?',
          text: 'This cannot be undone (image file will be removed if local).',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete Permanently',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/gallery/${encodeURIComponent(uuid)}/force-delete`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 12000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Force delete failed');
          ok('Deleted permanently');
          await loadTab('trash');
        }catch(ex){
          err(ex?.message || 'Failed');
        }finally{
          showLoading(false);
        }
        return;
      }
    });

    // submit create/edit
    let saving = false;

    itemForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (saving) return;
      saving = true;

      try{
        if (itemForm.dataset.mode === 'view') return;

        const intent = itemForm.dataset.intent || 'create';
        const isEdit = intent === 'edit' && !!itemUuid.value;

        if (isEdit && !canEdit) return;
        if (!isEdit && !canCreate) return;

        // metadata JSON validate
        const metaRaw = (metadataInput.value || '').trim();
        if (metaRaw){
          try{ JSON.parse(metaRaw); }catch(_){
            err('Metadata must be valid JSON');
            metadataInput.focus();
            return;
          }
        }

        const fd = new FormData();

        // dept
        const deptVal = (departmentSel.value || '').trim();
        if (deptVal) fd.append('department_id', deptVal);

        // fields
        const t = (titleInput.value || '').trim();
        const d = (descInput.value || '').trim();
        const tags = (tagsInput.value || '').trim();
        const st = (statusSel.value || 'draft').trim();
        const feat = (featuredSel.value || '0').trim();
        const so = String(parseInt(sortOrderInput.value || '0', 10) || 0);

        if (t) fd.append('title', t);
        if (d) fd.append('description', d);
        if (tags) fd.append('tags_json', tags); // controller accepts comma or json
        fd.append('status', st);
        fd.append('is_featured_home', feat === '1' ? '1' : '0');
        fd.append('sort_order', so);

        if ((publishAtInput.value || '').trim()) fd.append('publish_at', publishAtInput.value);
        if ((expireAtInput.value || '').trim()) fd.append('expire_at', expireAtInput.value);

        if (metaRaw) fd.append('metadata', metaRaw);

        // image: either file OR path/url
        const imgFile = imageFileInput.files?.[0] || null;
        const imgPath = (imagePathInput.value || '').trim();

        if (imgFile) fd.append('image_file', imgFile);
        if (imgPath) fd.append('image', imgPath);

        if (!isEdit){
          // store requires one of them
          if (!imgFile && !imgPath){
            err('Image is required (upload a file or provide a path/URL).');
            imageFileInput.focus();
            return;
          }
        }

        const url = isEdit
          ? `/api/gallery/${encodeURIComponent(itemUuid.value)}`
          : `/api/gallery`;

        // for file uploads, we use POST + _method for edit
        if (isEdit) fd.append('_method', 'PUT');

        setBtnLoading(saveBtn, true);
        showLoading(true);

        const res = await fetchWithTimeout(url, {
          method: 'POST',
          headers: authHeaders(),
          body: fd
        }, 20000);

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
        itemModal && itemModal.hide();
        await reloadAll();
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        saving = false;
        setBtnLoading(saveBtn, false);
        showLoading(false);
      }
    });

    // init
    (async () => {
      showLoading(true);
      try{
        await fetchMe();
        await loadDepartments();
        await reloadAll();
      }catch(ex){
        err(ex?.message || 'Initialization failed');
      }finally{
        showLoading(false);
      }
    })();
  });
})();
</script>
@endpush
