{{-- resources/views/modules/success-stories/manageSuccessStories.blade.php --}}
@section('title','Success Stories')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Success Stories - Admin UI
 * ========================= */

/* Dropdowns inside table */
.ss-table-wrap .dropdown{position:relative}
.ss-table-wrap .dd-toggle{border-radius:10px}
.ss-table-wrap .dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:230px;
  z-index:5000
}
/* safety: if any global css forces dropdown-menu hidden, ensure .show wins */
.ss-table-wrap .dropdown-menu.show{display:block !important}
.ss-table-wrap .dropdown-item{display:flex;align-items:center;gap:.6rem}
.ss-table-wrap .dropdown-item i{width:16px;text-align:center}
.ss-table-wrap .dropdown-item.text-danger{color:var(--danger-color) !important}

/* Tabs */
.ss-tabs.nav-tabs{border-color:var(--line-strong)}
.ss-tabs .nav-link{color:var(--ink)}
.ss-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface)
}
.tab-content,.tab-pane{overflow:visible}

/* Card/Table */
.ss-table-wrap.card{
  position:relative;
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.ss-table-wrap .card-body{overflow:visible}
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

/* Columns */
th.col-slug, td.col-slug{width:190px;max-width:190px}
td.col-slug{overflow:hidden}
td.col-slug code{
  display:inline-block;
  max-width:180px;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
  vertical-align:bottom;
}

/* Avatar */
.ss-person{display:flex;align-items:center;gap:10px;min-width:260px}
.ss-avatar{
  width:38px;height:38px;border-radius:12px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 80%, var(--bg-body));
  display:flex;align-items:center;justify-content:center;
  overflow:hidden;flex:0 0 auto;
}
.ss-avatar img{width:100%;height:100%;object-fit:cover;display:block}
.ss-avatar i{opacity:.6}
.ss-person .meta{display:flex;flex-direction:column;line-height:1.2}
.ss-person .meta .name{font-weight:700}
.ss-person .meta .title{
  color:var(--muted-color);font-size:12.5px;
  max-width:420px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis
}

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
.ss-loading{
  position:fixed;inset:0;
  background:rgba(0,0,0,0.45);
  display:flex;justify-content:center;align-items:center;
  z-index:9999;
  backdrop-filter:blur(2px)
}
.ss-loading .box{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;flex-direction:column;align-items:center;gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,0.3)
}
.ss-loading .spinner{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,0.3);
  border-top:4px solid var(--primary-color);
  animation:sspin 1s linear infinite
}
@keyframes sspin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

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
  animation:sspin 1s linear infinite
}

/* Toolbar */
.ss-toolbar.panel{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  padding:12px 12px;
}

/* Responsive toolbar */
@media (max-width: 768px){
  .ss-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .ss-toolbar .position-relative{min-width:100% !important}
  .ss-toolbar .toolbar-buttons{display:flex;gap:8px;flex-wrap:wrap}
  .ss-toolbar .toolbar-buttons .btn{flex:1;min-width:120px}
}

/* Horizontal scroll */
.ss-table-responsive{
  display:block;width:100%;
  max-width:100%;
  overflow-x:auto !important;
  overflow-y:visible !important;
  -webkit-overflow-scrolling:touch;
  position:relative;
}
.ss-table-responsive > .table{width:max-content;min-width:1320px}
.ss-table-responsive th, .ss-table-responsive td{white-space:nowrap}
@media (max-width: 576px){
  .ss-table-responsive > .table{min-width:1260px}
}

/* Photo preview box */
.ss-photo-box{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:var(--bg-soft, color-mix(in oklab, var(--surface) 88%, var(--bg-body)));
}
.ss-photo-box .top{
  display:flex;align-items:center;justify-content:space-between;
  gap:10px;padding:10px 12px;border-bottom:1px solid var(--line-soft);
}
.ss-photo-box .body{padding:12px}
.ss-photo-box img{
  width:100%;max-height:260px;object-fit:cover;
  border-radius:12px;border:1px solid var(--line-soft);background:#fff;
}
.ss-photo-meta{font-size:12.5px;color:var(--muted-color);margin-top:10px}

/* Social links builder */
.ss-links{
  border:1px solid var(--line-strong);
  border-radius:14px;
  padding:12px;
  background:var(--surface);
}
.ss-link-row{
  display:grid;
  grid-template-columns: 1.1fr 1.6fr 1fr auto;
  gap:10px;
  align-items:center;
  margin-bottom:10px;
}
@media (max-width: 992px){
  .ss-link-row{grid-template-columns:1fr 1fr}
  .ss-link-row .span2{grid-column: span 2}
}
.ss-link-row:last-child{margin-bottom:0}
</style>
@endpush

@section('content')
<div class="ss-wrap">

  {{-- Loading Overlay --}}
  <div id="ssLoading" class="ss-loading" style="display:none;">
    <div class="box">
      <div class="spinner"></div>
      <div class="small">Loading…</div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs ss-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#tab-published" role="tab" aria-selected="true">
        <i class="fa-solid fa-award me-2"></i>Published
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-drafts" role="tab" aria-selected="false">
        <i class="fa-solid fa-pen-to-square me-2"></i>Drafts / Archived
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-trash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Trash
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- PUBLISHED TAB --}}
    <div class="tab-pane fade show active" id="tab-published" role="tabpanel">

      {{-- Toolbar --}}
      <div class="row align-items-center g-2 mb-3 ss-toolbar panel">
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
            <input id="searchInput" type="search" class="form-control ps-5" placeholder="Search name, title, slug…">
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>

          <div class="toolbar-buttons d-flex gap-2">
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
              <i class="fa fa-plus me-1"></i> Add Success Story
            </button>
          </div>
        </div>
      </div>

      {{-- Table --}}
      <div class="card ss-table-wrap">
        <div class="card-body p-0">
          <div class="ss-table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Person</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:110px;">Year</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:120px;">Featured</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-published">
                <tr><td colspan="8" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-published" class="p-4 text-center" style="display:none;">
            <i class="fa fa-award mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No published success stories found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo-published">—</div>
            <nav><ul id="pager-published" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- DRAFTS/ARCHIVED TAB --}}
    <div class="tab-pane fade" id="tab-drafts" role="tabpanel">
      <div class="card ss-table-wrap">
        <div class="card-body p-0">
          <div class="ss-table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Person</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:110px;">Year</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:120px;">Featured</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-drafts">
                <tr><td colspan="8" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-drafts" class="p-4 text-center" style="display:none;">
            <i class="fa fa-pen-to-square mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No draft/archived stories found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo-drafts">—</div>
            <nav><ul id="pager-drafts" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- TRASH TAB --}}
    <div class="tab-pane fade" id="tab-trash" role="tabpanel">
      <div class="card ss-table-wrap">
        <div class="card-body p-0">
          <div class="ss-table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Person</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:150px;">Deleted</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-trash">
                <tr><td colspan="5" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-trash" class="p-4 text-center" style="display:none;">
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
            <label class="form-label">Status</label>
            <select id="modal_status" class="form-select">
              <option value="">Auto (per tab)</option>
              <option value="published">Published</option>
              <option value="draft">Draft</option>
              <option value="archived">Archived</option>
            </select>
            <div class="form-text">Leave “Auto” to keep the tab meaning.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Featured</label>
            <select id="modal_featured" class="form-select">
              <option value="">Any</option>
              <option value="1">Featured only</option>
              <option value="0">Not featured</option>
            </select>
          </div>

          <div class="col-6">
            <label class="form-label">Year</label>
            <input id="modal_year" type="number" class="form-control" min="1900" max="2200" placeholder="e.g., 2025">
          </div>

          <div class="col-6">
            <label class="form-label">Department (id/slug/uuid)</label>
            <input id="modal_department" type="text" class="form-control" placeholder="optional">
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="modal_sort" class="form-select">
              <option value="sort_order">Sort Order ↑</option>
              <option value="-sort_order">Sort Order ↓</option>
              <option value="-updated_at">Recently Updated</option>
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="name">Name A-Z</option>
              <option value="-name">Name Z-A</option>
              <option value="-year">Year ↓</option>
              <option value="year">Year ↑</option>
              <option value="-publish_at">Publish At ↓</option>
              <option value="publish_at">Publish At ↑</option>
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
        <h5 class="modal-title" id="itemModalTitle">Add Success Story</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="itemUuid">
        <input type="hidden" id="itemId">

        <div class="row g-3">
          <div class="col-lg-7">
            <div class="row g-3">

              <div class="col-md-6">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input class="form-control" id="name" required maxlength="120" placeholder="e.g., Debangi Kar">
              </div>

              <div class="col-md-6">
                <label class="form-label">Title (optional)</label>
                <input class="form-control" id="title" maxlength="255" placeholder="e.g., Placed at Infosys">
              </div>

              <div class="col-md-8">
                <label class="form-label">Slug (optional)</label>
                <input class="form-control" id="slug" maxlength="160" placeholder="debangikar-placed-infosys">
                <div class="form-text">Auto-generated from name/title until you edit manually.</div>
              </div>

              <div class="col-md-4">
                <label class="form-label">Sort Order</label>
                <input type="number" class="form-control" id="sort_order" min="0" max="1000000" value="0">
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
                <label class="form-label">Featured</label>
                <select class="form-select" id="is_featured_home">
                  <option value="0">No</option>
                  <option value="1">Yes</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Year</label>
                <input type="number" class="form-control" id="year" min="1900" max="2200" placeholder="e.g., 2025">
              </div>

              <div class="col-md-6">
                <label class="form-label">Date (optional)</label>
                <input type="date" class="form-control" id="date">
              </div>

              <div class="col-md-6">
                <label class="form-label">Quote (optional)</label>
                <input class="form-control" id="quote" maxlength="500" placeholder="Short quote/testimonial…">
              </div>

              <div class="col-12">
                <label class="form-label">Description (optional)</label>
                <textarea class="form-control" id="description" rows="5" placeholder="Write success story details…"></textarea>
              </div>

              <div class="col-md-6">
                <label class="form-label">Publish At</label>
                <input type="datetime-local" class="form-control" id="publish_at">
              </div>

              <div class="col-md-6">
                <label class="form-label">Expire At</label>
                <input type="datetime-local" class="form-control" id="expire_at">
              </div>

              <div class="col-md-6">
                <label class="form-label">Photo URL (optional)</label>
                <input class="form-control" id="photo_url" maxlength="255" placeholder="https://… or /depy_uploads/…">
              </div>

              <div class="col-md-6">
                <label class="form-label">Upload Photo (optional)</label>
                <input type="file" class="form-control" id="photo_file" accept="image/*">
                <div class="form-text">If uploaded, it overrides Photo URL.</div>
              </div>

              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="photo_remove">
                  <label class="form-check-label" for="photo_remove">Remove current photo</label>
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">Social Links (optional)</label>
                <div class="ss-links">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="small text-muted">Add links like LinkedIn, Company, Portfolio, etc.</div>
                    <button type="button" class="btn btn-light btn-sm" id="btnAddLink">
                      <i class="fa fa-plus me-1"></i>Add Link
                    </button>
                  </div>
                  <div id="linksWrap"></div>
                </div>
              </div>

            </div>
          </div>

          <div class="col-lg-5">
            <div class="ss-photo-box">
              <div class="top">
                <div class="fw-semibold"><i class="fa fa-image me-2"></i>Photo Preview</div>
                <div class="d-flex align-items-center gap-2">
                  <button type="button" class="btn btn-light btn-sm" id="btnOpenPhoto" style="display:none;">
                    <i class="fa fa-up-right-from-square me-1"></i>Open
                  </button>
                </div>
              </div>
              <div class="body">
                <img id="photoPreview" src="" alt="Photo preview" style="display:none;">
                <div id="photoEmpty" class="text-muted small" style="padding:12px;border:1px dashed var(--line-soft);border-radius:12px;">
                  No photo selected.
                </div>
                <div class="ss-photo-meta" id="photoMeta" style="display:none;">—</div>
              </div>
            </div>

            <div class="mt-3 small text-muted">
              Tip: Keep <b>slug</b> readable; <b>sort order</b> controls ordering when sorting by sort_order.
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
  if (window.__SUCCESS_STORIES_MODULE_INIT__) return;
  window.__SUCCESS_STORIES_MODULE_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=300) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  function slugify(s){
    return (s || '')
      .toString()
      .normalize('NFKD').replace(/[\u0300-\u036f]/g,'')
      .trim().toLowerCase()
      .replace(/['"`]/g,'')
      .replace(/[^a-z0-9]+/g,'-')
      .replace(/-+/g,'-')
      .replace(/^-|-$/g,'');
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
    try{
      return await fetch(url, { ...opts, signal: ctrl.signal });
    } finally {
      clearTimeout(t);
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const loadingEl = $('ssLoading');
    const showLoading = (v) => { if (loadingEl) loadingEl.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('toastSuccess');
    const toastErrEl = $('toastError');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { const el=$('toastSuccessText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('toastErrorText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = () => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    });

    // ===== elements
    const perPageSel = $('perPage');
    const searchInput = $('searchInput');
    const btnReset = $('btnReset');
    const btnApplyFilters = $('btnApplyFilters');
    const writeControls = $('writeControls');
    const btnAddItem = $('btnAddItem');

    const tbodyPublished = $('tbody-published');
    const tbodyDrafts = $('tbody-drafts');
    const tbodyTrash = $('tbody-trash');

    const emptyPublished = $('empty-published');
    const emptyDrafts = $('empty-drafts');
    const emptyTrash = $('empty-trash');

    const pagerPublished = $('pager-published');
    const pagerDrafts = $('pager-drafts');
    const pagerTrash = $('pager-trash');

    const infoPublished = $('resultsInfo-published');
    const infoDrafts = $('resultsInfo-drafts');
    const infoTrash = $('resultsInfo-trash');

    const filterModalEl = $('filterModal');
    const filterModal = filterModalEl ? new bootstrap.Modal(filterModalEl) : null;
    const modalStatus = $('modal_status');
    const modalSort = $('modal_sort');
    const modalFeatured = $('modal_featured');
    const modalYear = $('modal_year');
    const modalDept = $('modal_department');

    const itemModalEl = $('itemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;
    const itemModalTitle = $('itemModalTitle');
    const itemForm = $('itemForm');
    const saveBtn = $('saveBtn');

    // form fields
    const itemUuid = $('itemUuid');
    const itemId = $('itemId');

    const nameInput = $('name');
    const titleInput = $('title');
    const slugInput = $('slug');
    const sortOrderInput = $('sort_order');

    const statusSel = $('status');
    const featuredSel = $('is_featured_home');
    const yearInput = $('year');
    const dateInput = $('date');

    const quoteInput = $('quote');
    const descInput = $('description');

    const publishAtInput = $('publish_at');
    const expireAtInput = $('expire_at');

    const photoUrlInput = $('photo_url');
    const photoFileInput = $('photo_file');
    const photoRemoveChk = $('photo_remove');

    const linksWrap = $('linksWrap');
    const btnAddLink = $('btnAddLink');

    const photoPreview = $('photoPreview');
    const photoEmpty = $('photoEmpty');
    const photoMeta = $('photoMeta');
    const btnOpenPhoto = $('btnOpenPhoto');

    // ===== permissions
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

    // ===== state
    const state = {
      filters: { q:'', status:'', featured:'', sort:'sort_order', year:'', department:'' },
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      tabs: {
        published: { page:1, lastPage:1, items:[] },
        drafts:    { page:1, lastPage:1, items:[] },
        trash:     { page:1, lastPage:1, items:[] }
      }
    };

    const getTabKey = () => {
      const a = document.querySelector('.ss-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#tab-published';
      if (href === '#tab-drafts') return 'drafts';
      if (href === '#tab-trash') return 'trash';
      return 'published';
    };

    function tabDefaultStatus(tabKey){
      if (tabKey === 'published') return 'published';
      if (tabKey === 'drafts') return 'draft';
      return '';
    }

    function buildUrl(tabKey){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state.tabs[tabKey].page));

      const q = (state.filters.q || '').trim();
      if (q) params.set('q', q);

      const s = state.filters.sort || 'sort_order';
      params.set('sort', s.startsWith('-') ? s.slice(1) : s);
      params.set('direction', s.startsWith('-') ? 'desc' : 'asc');

      if (state.filters.featured !== '') params.set('featured', state.filters.featured);

      const yr = String(state.filters.year || '').trim();
      if (yr) params.set('year', yr);

      const dep = String(state.filters.department || '').trim();
      if (dep) params.set('department', dep);

      if (tabKey !== 'trash'){
        const st = (state.filters.status || '').trim() || tabDefaultStatus(tabKey);
        if (st) params.set('status', st);
      }

      if (tabKey === 'trash') return `/api/success-stories/trash?${params.toString()}`;
      return `/api/success-stories?${params.toString()}`;
    }

    function setEmpty(tabKey, show){
      const el = tabKey==='published' ? emptyPublished : (tabKey==='drafts' ? emptyDrafts : emptyTrash);
      if (el) el.style.display = show ? '' : 'none';
    }

    function statusBadge(status){
      const s = (status || '').toString().toLowerCase();
      if (s === 'published') return `<span class="badge badge-soft-success">Published</span>`;
      if (s === 'draft') return `<span class="badge badge-soft-warning">Draft</span>`;
      if (s === 'archived') return `<span class="badge badge-soft-muted">Archived</span>`;
      return `<span class="badge badge-soft-muted">${esc(s || '—')}</span>`;
    }

    function featuredBadge(v){
      return v ? `<span class="badge badge-soft-primary">Yes</span>` : `<span class="badge badge-soft-muted">No</span>`;
    }

    function renderPager(tabKey){
      const pagerEl = tabKey === 'published' ? pagerPublished : (tabKey === 'drafts' ? pagerDrafts : pagerTrash);
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

    function renderTable(tabKey){
      const tbody = tabKey==='published' ? tbodyPublished : (tabKey==='drafts' ? tbodyDrafts : tbodyTrash);
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
        const name = r.name || '—';
        const title = r.title || '';
        const slug = r.slug || '—';
        const status = r.status || '—';
        const featured = !!(r.is_featured_home ?? 0);
        const year = r.year ?? '—';
        const updated = r.updated_at || '—';
        const deleted = r.deleted_at || '—';
        const sortOrder = (r.sort_order ?? 0);

        const photo = r.photo_full_url || r.photo_url || '';
        const avatar = photo
          ? `<div class="ss-avatar"><img src="${esc(normalizeUrl(photo))}" alt="photo"></div>`
          : `<div class="ss-avatar"><i class="fa fa-user"></i></div>`;

        const personCell = `
          <div class="ss-person">
            ${avatar}
            <div class="meta">
              <div class="name">${esc(name)}</div>
              <div class="title">${esc(title || '—')}</div>
            </div>
          </div>`;

        /**
         * ✅ DROPDOWN FIX (Action button not opening)
         * We DO NOT use data-bs-toggle="dropdown" here.
         * Some projects have global click handlers that instantly close bootstrap dropdowns.
         * We manually toggle via bootstrap.Dropdown in CAPTURE phase (below).
         */
        let actions = `
          <div class="dropdown text-end">
            <button type="button"
              class="btn btn-light btn-sm dd-toggle"
              data-dd="toggle"
              aria-expanded="false" title="Actions">
              <i class="fa fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>`;

        if (canEdit && tabKey !== 'trash'){
          actions += `<li><button type="button" class="dropdown-item" data-action="edit"><i class="fa fa-pen-to-square"></i> Edit</button></li>`;
          actions += `<li><button type="button" class="dropdown-item" data-action="toggleFeatured"><i class="fa fa-star"></i> Toggle Featured</button></li>`;
        }

        if (tabKey !== 'trash'){
          if (canDelete){
            actions += `<li><hr class="dropdown-divider"></li>
              <li><button type="button" class="dropdown-item text-danger" data-action="delete"><i class="fa fa-trash"></i> Delete</button></li>`;
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
              <td>${personCell}</td>
              <td class="col-slug"><code>${esc(slug)}</code></td>
              <td>${esc(deleted)}</td>
              <td>${esc(String(sortOrder))}</td>
              <td class="text-end">${actions}</td>
            </tr>`;
        }

        return `
          <tr data-uuid="${esc(uuid)}">
            <td>${personCell}</td>
            <td class="col-slug"><code>${esc(slug)}</code></td>
            <td>${esc(String(year ?? '—'))}</td>
            <td>${statusBadge(status)}</td>
            <td>${featuredBadge(featured)}</td>
            <td>${esc(String(sortOrder))}</td>
            <td>${esc(String(updated))}</td>
            <td class="text-end">${actions}</td>
          </tr>`;
      }).join('');

      renderPager(tabKey);
    }

    async function loadTab(tabKey){
      const tbody = tabKey==='published' ? tbodyPublished : (tabKey==='drafts' ? tbodyDrafts : tbodyTrash);
      if (tbody){
        const cols = (tabKey==='trash') ? 5 : 8;
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
        state.tabs[tabKey].lastPage = parseInt(p.last_page || 1, 10) || 1;

        const info = (p.total ? `${p.total} result(s)` : '—');
        if (tabKey === 'published' && infoPublished) infoPublished.textContent = info;
        if (tabKey === 'drafts' && infoDrafts) infoDrafts.textContent = info;
        if (tabKey === 'trash' && infoTrash) infoTrash.textContent = info;

        renderTable(tabKey);
      }catch(e){
        state.tabs[tabKey].items = [];
        state.tabs[tabKey].lastPage = 1;
        renderTable(tabKey);
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    function reloadCurrent(){ loadTab(getTabKey()); }

    // ===== pager click
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

    // ===== filters
    searchInput?.addEventListener('input', debounce(() => {
      state.filters.q = (searchInput.value || '').trim();
      state.tabs.published.page = state.tabs.drafts.page = state.tabs.trash.page = 1;
      reloadCurrent();
    }, 320));

    perPageSel?.addEventListener('change', () => {
      state.perPage = parseInt(perPageSel.value, 10) || 20;
      state.tabs.published.page = state.tabs.drafts.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    filterModalEl?.addEventListener('show.bs.modal', () => {
      modalStatus.value = state.filters.status || '';
      modalSort.value = state.filters.sort || 'sort_order';
      modalFeatured.value = (state.filters.featured ?? '');
      modalYear.value = state.filters.year || '';
      modalDept.value = state.filters.department || '';
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.status = modalStatus?.value || '';
      state.filters.sort = modalSort?.value || 'sort_order';
      state.filters.featured = (modalFeatured?.value ?? '');
      state.filters.year = (modalYear?.value ?? '');
      state.filters.department = (modalDept?.value ?? '');
      state.tabs.published.page = state.tabs.drafts.page = state.tabs.trash.page = 1;
      filterModal && filterModal.hide();
      reloadCurrent();
    });

    btnReset?.addEventListener('click', () => {
      state.filters = { q:'', status:'', featured:'', sort:'sort_order', year:'', department:'' };
      state.perPage = 20;

      if (searchInput) searchInput.value = '';
      if (perPageSel) perPageSel.value = '20';
      if (modalStatus) modalStatus.value = '';
      if (modalFeatured) modalFeatured.value = '';
      if (modalSort) modalSort.value = 'sort_order';
      if (modalYear) modalYear.value = '';
      if (modalDept) modalDept.value = '';

      state.tabs.published.page = state.tabs.drafts.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    document.querySelector('a[href="#tab-published"]')?.addEventListener('shown.bs.tab', () => loadTab('published'));
    document.querySelector('a[href="#tab-drafts"]')?.addEventListener('shown.bs.tab', () => loadTab('drafts'));
    document.querySelector('a[href="#tab-trash"]')?.addEventListener('shown.bs.tab', () => loadTab('trash'));

    // =========================
    // ✅ ACTION DROPDOWN HARD FIX
    // - Manually toggles dropdown (no data-bs-toggle)
    // - Runs in CAPTURE phase + stops propagation so no “global click closer” can instantly close it
    // =========================
    function hideOtherDropdowns(exceptToggle){
      document.querySelectorAll('.ss-table-wrap .dd-toggle.show').forEach(tg => {
        if (tg === exceptToggle) return;
        try{ bootstrap.Dropdown.getOrCreateInstance(tg).hide(); }catch(_){}
      });
    }

    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('.ss-table-wrap .dd-toggle[data-dd="toggle"]');
      if (!toggle) return;

      e.preventDefault();
      e.stopPropagation();
      if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();

      hideOtherDropdowns(toggle);

      try{
        const inst = bootstrap.Dropdown.getOrCreateInstance(toggle, { autoClose: true });
        inst.toggle();
      }catch(_){}
    }, true);

    // also close any open dropdown when clicking outside (capture)
    document.addEventListener('click', (e) => {
      if (e.target.closest('.ss-table-wrap .dropdown')) return;
      hideOtherDropdowns(null);
    }, true);

    // ===== modal / photo preview
    let photoObjectUrl = null;

    function clearPhotoPreview(revoke=true){
      if (revoke && photoObjectUrl){
        try{ URL.revokeObjectURL(photoObjectUrl); }catch(_){}
      }
      photoObjectUrl = null;

      if (photoPreview){
        photoPreview.style.display = 'none';
        photoPreview.removeAttribute('src');
      }
      if (photoEmpty) photoEmpty.style.display = '';
      if (photoMeta){ photoMeta.style.display = 'none'; photoMeta.textContent = '—'; }
      if (btnOpenPhoto){ btnOpenPhoto.style.display = 'none'; btnOpenPhoto.onclick = null; }
    }

    function setPhotoPreview(url, metaText=''){
      const u = normalizeUrl(url);
      if (!u){ clearPhotoPreview(true); return; }
      if (photoPreview){
        photoPreview.style.display = '';
        photoPreview.src = u;
      }
      if (photoEmpty) photoEmpty.style.display = 'none';
      if (photoMeta){
        photoMeta.style.display = metaText ? '' : 'none';
        photoMeta.textContent = metaText || '';
      }
      if (btnOpenPhoto){
        btnOpenPhoto.style.display = '';
        btnOpenPhoto.onclick = () => window.open(u, '_blank', 'noopener');
      }
    }

    photoFileInput?.addEventListener('change', () => {
      const f = photoFileInput.files?.[0];
      if (!f){ clearPhotoPreview(true); return; }
      if (photoObjectUrl){
        try{ URL.revokeObjectURL(photoObjectUrl); }catch(_){}
      }
      photoObjectUrl = URL.createObjectURL(f);
      setPhotoPreview(photoObjectUrl, `${f.name || 'photo'} • ${bytes(f.size)}`);
    });

    photoUrlInput?.addEventListener('input', debounce(() => {
      const f = photoFileInput.files?.[0];
      if (f) return;
      const v = (photoUrlInput.value || '').trim();
      if (!v){ clearPhotoPreview(true); return; }
      setPhotoPreview(v, 'From URL');
    }, 280));

    // ===== social links builder
    function linkRowTpl(data={}, viewOnly=false){
      const label = data.label ?? data.name ?? '';
      const url = data.url ?? data.href ?? (typeof data === 'string' ? data : '');
      const icon = data.icon ?? data.fa ?? '';
      const dis = viewOnly ? 'disabled' : '';
      const ro  = viewOnly ? 'readonly' : '';

      return `
        <div class="ss-link-row" data-link-row>
          <input class="form-control" placeholder="Label (e.g., LinkedIn)" value="${esc(label)}" ${ro}>
          <input class="form-control" placeholder="URL" value="${esc(url)}" ${ro}>
          <input class="form-control span2" placeholder="Icon (optional, e.g., fa-brands fa-linkedin)" value="${esc(icon)}" ${ro}>
          <button type="button" class="btn btn-light btn-sm" data-remove-link ${dis}>
            <i class="fa fa-xmark"></i>
          </button>
        </div>
      `;
    }

    function getLinksFromUI(){
      const rows = Array.from(linksWrap?.querySelectorAll('[data-link-row]') || []);
      const out = [];
      rows.forEach(row => {
        const inputs = row.querySelectorAll('input');
        const label = (inputs[0]?.value || '').trim();
        const url = (inputs[1]?.value || '').trim();
        const icon = (inputs[2]?.value || '').trim();
        if (!label && !url) return;
        out.push({ label: label || null, url: url || null, icon: icon || null });
      });
      return out;
    }

    function setLinksUI(arr, viewOnly=false){
      if (!linksWrap) return;
      linksWrap.innerHTML = '';
      const list = Array.isArray(arr) ? arr : [];
      if (!list.length){
        linksWrap.innerHTML = linkRowTpl({}, viewOnly);
        return;
      }
      linksWrap.innerHTML = list.map(x => linkRowTpl(x, viewOnly)).join('');
    }

    btnAddLink?.addEventListener('click', () => {
      if (!linksWrap) return;
      linksWrap.insertAdjacentHTML('beforeend', linkRowTpl({}));
    });

    document.addEventListener('click', (e) => {
      const rm = e.target.closest('[data-remove-link]');
      if (!rm) return;
      const row = rm.closest('[data-link-row]');
      if (!row) return;

      const all = linksWrap?.querySelectorAll('[data-link-row]') || [];
      if (all.length <= 1){
        row.querySelectorAll('input').forEach(i => i.value = '');
        return;
      }
      row.remove();
    });

    // ===== modal helpers
    let saving = false;
    let slugDirty = false;
    let settingSlug = false;

    function setBtnLoading(btn, loading){
      if (!btn) return;
      btn.disabled = !!loading;
      btn.classList.toggle('btn-loading', !!loading);
    }

    function resetForm(){
      itemForm?.reset();
      itemUuid.value = '';
      itemId.value = '';
      slugDirty = false;
      settingSlug = false;

      setLinksUI([], false);
      clearPhotoPreview(true);

      // enable everything
      itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
        if (el.id === 'itemUuid' || el.id === 'itemId') return;
        if (el.type === 'file' || el.tagName === 'SELECT' || el.type === 'checkbox') el.disabled = false;
        else el.readOnly = false;
      });

      if (btnAddLink) btnAddLink.style.display = '';
      linksWrap?.querySelectorAll('[data-remove-link]').forEach(b => b.style.display = '');

      if (saveBtn) saveBtn.style.display = '';
      itemForm.dataset.mode = 'edit';
      itemForm.dataset.intent = 'create';
    }

    function fillFormFromRow(r, viewOnly=false){
      itemUuid.value = r.uuid || '';
      itemId.value = r.id || '';

      nameInput.value = r.name || '';
      titleInput.value = r.title || '';
      slugInput.value = r.slug || '';
      sortOrderInput.value = String(r.sort_order ?? 0);

      statusSel.value = (r.status || 'draft');
      featuredSel.value = String((r.is_featured_home ?? 0) ? 1 : 0);

      yearInput.value = (r.year ?? '') ? String(r.year) : '';
      dateInput.value = (r.date ?? '') ? String(r.date).slice(0,10) : '';

      quoteInput.value = r.quote || '';
      descInput.value = r.description || '';

      const toLocal = (s) => {
        if (!s) return '';
        const t = String(s).replace(' ', 'T');
        return t.length >= 16 ? t.slice(0,16) : t;
      };
      publishAtInput.value = toLocal(r.publish_at);
      expireAtInput.value = toLocal(r.expire_at);

      photoUrlInput.value = r.photo_url || '';
      photoRemoveChk.checked = false;
      if (photoFileInput) photoFileInput.value = '';

      const photo = r.photo_full_url || r.photo_url || '';
      if (photo) setPhotoPreview(photo, 'Current photo');

      let links = r.social_links_json ?? r.social_links ?? [];
      if (typeof links === 'string') { try{ links = JSON.parse(links); }catch(_){ links = []; } }
      setLinksUI(Array.isArray(links) ? links : [], viewOnly);

      slugDirty = true;

      itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
        if (el.id === 'itemUuid' || el.id === 'itemId') return;
        if (viewOnly){
          if (el.type === 'file' || el.tagName === 'SELECT' || el.type === 'checkbox') el.disabled = true;
          else el.readOnly = true;
        } else {
          if (el.type === 'file' || el.tagName === 'SELECT' || el.type === 'checkbox') el.disabled = false;
          else el.readOnly = false;
        }
      });

      if (btnAddLink) btnAddLink.style.display = viewOnly ? 'none' : '';
      linksWrap?.querySelectorAll('[data-remove-link]').forEach(b => b.style.display = viewOnly ? 'none' : '');

      if (saveBtn) saveBtn.style.display = viewOnly ? 'none' : '';
      itemForm.dataset.mode = viewOnly ? 'view' : 'edit';
      itemForm.dataset.intent = viewOnly ? 'view' : 'edit';
    }

    function findRowByUuid(uuid){
      const all = [
        ...(state.tabs.published.items || []),
        ...(state.tabs.drafts.items || []),
        ...(state.tabs.trash.items || []),
      ];
      return all.find(x => x?.uuid === uuid) || null;
    }

    const autoSlug = debounce(() => {
      if (itemForm?.dataset.mode === 'view') return;
      if (itemUuid.value) return;
      if (slugDirty) return;
      const base = (titleInput.value || '').trim() || (nameInput.value || '').trim();
      settingSlug = true;
      slugInput.value = slugify(base);
      settingSlug = false;
    }, 120);

    nameInput?.addEventListener('input', autoSlug);
    titleInput?.addEventListener('input', autoSlug);
    slugInput?.addEventListener('input', () => {
      if (itemUuid.value) return;
      if (settingSlug) return;
      slugDirty = !!(slugInput.value || '').trim();
    });

    btnAddItem?.addEventListener('click', () => {
      if (!canCreate) return;
      resetForm();
      if (itemModalTitle) itemModalTitle.textContent = 'Add Success Story';
      itemForm.dataset.intent = 'create';
      itemModal && itemModal.show();
    });

    itemModalEl?.addEventListener('hidden.bs.modal', () => {
      if (photoObjectUrl){ try{ URL.revokeObjectURL(photoObjectUrl); }catch(_){ } photoObjectUrl=null; }
    });

    // ===== row actions
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

      if (act === 'view' || act === 'edit'){
        if (act === 'edit' && !canEdit) return;

        resetForm();
        if (itemModalTitle) itemModalTitle.textContent = act === 'view' ? 'View Success Story' : 'Edit Success Story';
        fillFormFromRow(row || {}, act === 'view');
        itemModal && itemModal.show();
        return;
      }

      if (act === 'toggleFeatured'){
        if (!canEdit) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/success-stories/${encodeURIComponent(uuid)}/toggle-featured`, {
            method: 'PATCH',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to toggle');

          ok('Updated featured');
          await Promise.all([loadTab('published'), loadTab('drafts')]);
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'delete'){
        if (!canDelete) return;

        const conf = await Swal.fire({
          title: 'Delete this story?',
          text: 'This will move the item to Trash.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/success-stories/${encodeURIComponent(uuid)}`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Delete failed');

          ok('Moved to trash');
          await Promise.all([loadTab('published'), loadTab('drafts'), loadTab('trash')]);
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
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
          const res = await fetchWithTimeout(`/api/success-stories/${encodeURIComponent(uuid)}/restore`, {
            method: 'POST',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Restore failed');

          ok('Restored');
          await Promise.all([loadTab('trash'), loadTab('published'), loadTab('drafts')]);
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'force'){
        if (!canDelete) return;

        const conf = await Swal.fire({
          title: 'Delete permanently?',
          text: 'This cannot be undone.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete Permanently',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/success-stories/${encodeURIComponent(uuid)}/force`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Force delete failed');

          ok('Deleted permanently');
          await loadTab('trash');
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }
    });

    // ===== submit (create/edit)
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

        const name = (nameInput.value || '').trim();
        if (!name){ err('Name is required'); nameInput.focus(); return; }

        const fd = new FormData();
        fd.append('name', name);

        const title = (titleInput.value || '').trim();
        const slug = (slugInput.value || '').trim();
        const description = (descInput.value || '').trim();
        const quote = (quoteInput.value || '').trim();

        if (title) fd.append('title', title);
        if (slug) fd.append('slug', slug);
        if (description) fd.append('description', description);
        if (quote) fd.append('quote', quote);

        const status = (statusSel.value || 'draft').trim();
        fd.append('status', status);
        fd.append('is_featured_home', (featuredSel.value || '0') === '1' ? '1' : '0');
        fd.append('sort_order', String(parseInt(sortOrderInput.value || '0', 10) || 0));

        const year = (yearInput.value || '').trim();
        if (year) fd.append('year', year);

        const date = (dateInput.value || '').trim();
        if (date) fd.append('date', date);

        const pub = (publishAtInput.value || '').trim();
        const exp = (expireAtInput.value || '').trim();
        if (pub) fd.append('publish_at', pub);
        if (exp) fd.append('expire_at', exp);

        const photoUrl = (photoUrlInput.value || '').trim();
        if (photoUrl) fd.append('photo_url', photoUrl);

        if (photoRemoveChk.checked) fd.append('photo_remove', '1');

        const photoFile = photoFileInput.files?.[0] || null;
        if (photoFile) fd.append('photo_file', photoFile);

        const links = getLinksFromUI();
        if (links.length) fd.append('social_links_json', JSON.stringify(links));

        let url = `/api/success-stories`;
        if (isEdit){
          url = `/api/success-stories/${encodeURIComponent(itemUuid.value)}`;
          fd.append('_method', 'PUT');
        }

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

        state.tabs.published.page = state.tabs.drafts.page = state.tabs.trash.page = 1;
        await Promise.all([loadTab('published'), loadTab('drafts'), loadTab('trash')]);
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        saving = false;
        setBtnLoading(saveBtn, false);
        showLoading(false);
      }
    });

    // ===== init
    (async () => {
      showLoading(true);
      try{
        await fetchMe();
        await Promise.all([loadTab('published'), loadTab('drafts'), loadTab('trash')]);
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
