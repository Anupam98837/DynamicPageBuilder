{{-- resources/views/modules/gallery/manageGallery.blade.php --}}
@section('title','Gallery')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* Wrapper */
.gl-wrap{max-width:1140px;margin:16px auto 44px;overflow:visible}
.gl-panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2);padding:14px}

/* Dropdowns inside table */
.table-responsive .dropdown{position:relative}
.gl-dd-toggle{border-radius:10px}
.dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:230px;z-index:99999}
.dropdown-menu.show{display:block !important}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color) !important}

/* Tabs */
.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{background:var(--surface);border-color:var(--line-strong) var(--line-strong) var(--surface)}
.tab-content,.tab-pane{overflow:visible}

/* Table card */
.table-wrap.card{position:relative;border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:visible}
.table-wrap .card-body{overflow:visible}
.table{--bs-table-bg:transparent}
.table thead th{font-weight:600;color:var(--muted-color);font-size:13px;border-bottom:1px solid var(--line-strong);background:var(--surface)}
.table thead.sticky-top{z-index:3}
.table tbody tr{border-top:1px solid var(--line-soft)}
.table tbody tr:hover{background:var(--page-hover)}
.small{font-size:12.5px}

/* Responsive horizontal scroll */
.table-responsive{display:block;width:100%;max-width:100%;overflow-x:auto !important;overflow-y:visible !important;-webkit-overflow-scrolling:touch;position:relative}
.table-responsive > .table{width:max-content;min-width:1260px}
.table-responsive th,
.table-responsive td{white-space:nowrap}

/* Thumb */
.g-thumb{width:44px;height:34px;border-radius:10px;object-fit:cover;border:1px solid var(--line-soft);background:#fff}
.g-title{font-weight:700;color:var(--ink)}
.g-sub{font-size:12px;color:var(--muted-color)}
.g-meta-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:6px}
.g-event-chip{display:inline-flex;align-items:center;gap:6px;padding:4px 8px;border-radius:999px;border:1px solid var(--line-strong);background:color-mix(in oklab, var(--surface) 92%, transparent);font-size:11.5px;color:var(--ink)}
.g-event-chip i{opacity:.8}
.g-event-code{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace}

/* Badges */
.badge-soft-primary{background:color-mix(in oklab, var(--primary-color) 12%, transparent);color:var(--primary-color)}
.badge-soft-success{background:color-mix(in oklab, var(--success-color) 12%, transparent);color:var(--success-color)}
.badge-soft-muted{background:color-mix(in oklab, var(--muted-color) 10%, transparent);color:var(--muted-color)}
.badge-soft-warning{background:color-mix(in oklab, var(--warning-color, #f59e0b) 14%, transparent);color:var(--warning-color, #f59e0b)}
.badge-soft-info{background:color-mix(in oklab, #0ea5e9 14%, transparent);color:#0ea5e9}
.badge-soft-danger{background:color-mix(in oklab, var(--danger-color) 12%, transparent);color:var(--danger-color)}

/* Timeline Styles */
.timeline {
  position: relative;
  padding: 0;
  list-style: none;
}
.timeline:before {
  content: '';
  position: absolute;
  top: 0;
  bottom: 0;
  left: 31px;
  width: 2px;
  background: var(--line-soft);
}
.timeline-item {
  position: relative;
  margin-bottom: 20px;
}
.timeline-marker {
  position: absolute;
  top: 0;
  left: 20px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: var(--surface);
  border: 2px solid var(--primary-color);
  z-index: 10;
}
.timeline-content {
  margin-left: 60px;
  padding: 12px 16px;
  background: color-mix(in oklab, var(--surface) 95%, var(--bg-body));
  border: 1px solid var(--line-soft);
  border-radius: 12px;
}
.timeline-date {
  font-size: 11px;
  color: var(--muted-color);
  margin-bottom: 4px;
}
.timeline-title {
  font-weight: 600;
  font-size: 13.5px;
  margin-bottom: 4px;
}
.timeline-author {
  font-size: 12px;
  font-weight: 500;
  color: var(--ink);
}
.timeline-comment {
  font-size: 12.5px;
  color: var(--muted-color);
  margin-top: 6px;
  padding: 6px 10px;
  background: rgba(0,0,0,0.03);
  border-left: 2px solid var(--line-strong);
  font-style: italic;
}
.badge-pending-draft {
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 6px;
  background: var(--warning-color);
  color: #fff;
  vertical-align: middle;
  margin-left: 4px;
  text-transform: uppercase;
  font-weight: 700;
}

/* Loading overlay */
.loading-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;z-index:9999;backdrop-filter:blur(2px)}
.loading-spinner{background:var(--surface);padding:20px 22px;border-radius:14px;display:flex;flex-direction:column;align-items:center;gap:10px;box-shadow:0 10px 26px rgba(0,0,0,.3)}
.spinner{width:40px;height:40px;border-radius:50%;border:4px solid rgba(148,163,184,.3);border-top:4px solid var(--primary-color);animation:spin 1s linear infinite}
@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

/* Button loading */
.btn-loading{position:relative;color:transparent !important}
.btn-loading::after{content:'';position:absolute;width:16px;height:16px;top:50%;left:50%;margin:-8px 0 0 -8px;border:2px solid transparent;border-top:2px solid currentColor;border-radius:50%;animation:spin 1s linear infinite}

/* Toolbar responsiveness */
@media (max-width: 768px){
  .gl-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .gl-toolbar .position-relative{min-width:100% !important}
  .gl-toolbar .toolbar-buttons{display:flex;gap:8px;flex-wrap:wrap}
  .gl-toolbar .toolbar-buttons .btn{flex:1;min-width:120px}
}

/* Preview box */
.preview-box{border:1px solid var(--line-strong);border-radius:14px;overflow:hidden;background:var(--bg-soft, color-mix(in oklab, var(--surface) 88%, var(--bg-body)))}
.preview-box .top{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;border-bottom:1px solid var(--line-soft)}
.preview-box .body{padding:12px}
.preview-box img{width:100%;max-height:300px;object-fit:cover;border-radius:12px;border:1px solid var(--line-soft);background:#fff}
.preview-meta{font-size:12.5px;color:var(--muted-color);margin-top:10px}

/* Inactive selector chip */
.chip{display:inline-flex;align-items:center;gap:8px;border:1px solid var(--line-strong);border-radius:999px;padding:6px 10px;background:color-mix(in oklab, var(--surface) 92%, transparent)}
.chip select{border:0;background:transparent;outline:none;color:var(--ink);font-weight:600}

/* Event section */
.event-block{border:1px solid var(--line-strong);border-radius:14px;padding:14px;background:color-mix(in oklab, var(--surface) 92%, transparent)}
.event-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px}
.event-head .title{font-weight:700;color:var(--ink)}
.event-head .sub{font-size:12px;color:var(--muted-color)}
.freeze-note{display:none;margin-top:8px;padding:9px 11px;border-radius:12px;border:1px dashed color-mix(in oklab, var(--primary-color) 24%, var(--line-strong));background:color-mix(in oklab, var(--primary-color) 8%, transparent);font-size:12px;color:var(--ink)}
.event-readonly{background:color-mix(in oklab, var(--surface) 92%, var(--bg-body)) !important}
.section-note{font-size:12px;color:var(--muted-color)}

/* Modal layout improvements */
.gl-item-modal{max-width:1240px}
.item-modal-body{max-height:min(82vh,920px);overflow:auto}
.item-modal-grid > [class*="col-"]{display:flex;flex-direction:column}
.form-section{border:1px solid var(--line-strong);border-radius:16px;background:color-mix(in oklab, var(--surface) 95%, transparent);box-shadow:var(--shadow-1, none);padding:14px}
.form-section-title{display:flex;align-items:center;gap:8px;font-weight:700;color:var(--ink);margin-bottom:4px}
.form-section-sub{font-size:12px;color:var(--muted-color);margin-bottom:12px}
.preview-sticky{position:sticky;top:2px}

@media (max-width:1199.98px){
  .preview-sticky{position:static}
}

@media (max-width:575.98px){
  .item-modal-body{padding:12px}
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

      {{-- Toolbar --}}
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
            <input id="searchInput" type="search" class="form-control ps-5" placeholder="Search by title, description, event…">
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
                  <th>Title / Event</th>
                  <th style="width:160px;">Department</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:110px;">Featured</th>
                  <th style="width:150px;">Publish At</th>
                  <th style="width:90px;">Sort</th>
                  <th style="width:90px;">Views</th>
                  <th style="width:150px;">Workflow</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-active">
                <tr><td colspan="11" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
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
                  <th>Title / Event</th>
                  <th style="width:160px;">Department</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:110px;">Featured</th>
                  <th style="width:150px;">Publish At</th>
                  <th style="width:90px;">Sort</th>
                  <th style="width:90px;">Views</th>
                  <th style="width:150px;">Workflow</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-inactive">
                <tr><td colspan="11" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
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
                  <th>Title / Event</th>
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
              <option value="-event_date">Event Date (Newest)</option>
              <option value="event_date">Event Date (Oldest)</option>
              <option value="event_title">Event Title A-Z</option>
              <option value="-event_title">Event Title Z-A</option>
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
            <label class="form-label">Has Event</label>
            <select id="modal_has_event" class="form-select">
              <option value="">Any</option>
              <option value="1">Only items linked to an event</option>
              <option value="0">Only items without event</option>
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
  <div class="modal-dialog modal-xl modal-dialog-centered gl-item-modal">
    <form class="modal-content" id="itemForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="itemModalTitle">Add Gallery Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body item-modal-body">
        <input type="hidden" id="itemUuid">
        <input type="hidden" id="itemId">

        {{-- Rejection Alert --}}
        <div id="glRejectionAlert" class="alert alert-danger mb-3" style="display:none;">
          <div class="d-flex align-items-center gap-2 mb-1">
            <i class="fa fa-circle-exclamation fs-5"></i>
            <h6 class="mb-0 fw-bold">Rejected by Authority</h6>
          </div>
          <div id="glRejectionReasonText" class="ms-4 small" style="white-space: pre-wrap;">Reason...</div>
          <div class="mt-2 ms-4">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="viewGalleryHistoryFromAlert()">
              <i class="fa fa-clock-rotate-left me-1"></i>View Full History
            </button>
          </div>
        </div>

        {{-- Pending Draft Alert --}}
        <div id="glDraftAlert" class="alert alert-warning mb-3" style="display:none;">
          <div class="d-flex align-items-center gap-2">
            <i class="fa fa-pen-nib fs-5"></i>
            <h6 class="mb-0 fw-bold">Pending Changes</h6>
          </div>
          <div class="ms-4 small">This item has updates waiting for approval. Editing now will replace those pending changes.</div>
        </div>

        <div class="row g-3 item-modal-grid">
          {{-- LEFT --}}
          <div class="col-12 col-xl-7">
            <div class="form-section mb-3">
              <div class="form-section-title">
                <i class="fa fa-pen-to-square"></i> Basic Details
              </div>
              <div class="form-section-sub">Main image information and grouping details.</div>

              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Department (optional)</label>
                  <select id="department_id" class="form-select">
                    <option value="">Global (No Department)</option>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">Image Title (optional)</label>
                  <input class="form-control" id="title" maxlength="255" placeholder="e.g., Stage Performance">
                </div>

                <div class="col-12">
                  <label class="form-label">Image Description / Caption (optional)</label>
                  <textarea class="form-control" id="description" maxlength="500" rows="3" placeholder="Short caption…"></textarea>
                </div>

                <div class="col-12">
                  <label class="form-label">Tags (optional)</label>
                  <input class="form-control" id="tags" placeholder="e.g., fest, sports, seminar">
                  <div class="form-text">Comma-separated. Will be stored as <code>tags_json</code>.</div>
                </div>
              </div>
            </div>

            <div class="form-section mb-3">
              <div class="event-block">
                <div class="event-head">
                  <div>
                    <div class="title">
                      <i class="fa fa-calendar-days me-2"></i>Album Event
                    </div>
                    <div class="sub">Use an existing event or keep a custom event for this album item.</div>
                  </div>
                </div>

                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label">Event Mode</label>
                    <select id="event_mode" class="form-select">
                      <option value="none" selected>No Event</option>
                      <option value="manual">Create / Edit Custom Event</option>
                      <option value="existing">Use Existing Event</option>
                    </select>
                  </div>

                  <div class="col-md-8" id="existingEventWrap" style="display:none;">
                    <label class="form-label">Existing Event</label>
                    <select id="selected_event_shortcode" class="form-select">
                      <option value="">Select an event</option>
                    </select>
                    <div class="form-text">Selecting an event will auto-populate and lock the event fields below.</div>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Event Title</label>
                    <input class="form-control" id="event_title" maxlength="255" placeholder="e.g., Annual Fest 2026">
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Event Date</label>
                    <input type="date" class="form-control" id="event_date">
                  </div>

                  <div class="col-12">
                    <label class="form-label">Event Description</label>
                    <textarea class="form-control" id="event_description" rows="3" placeholder="Event description for album cards…"></textarea>
                  </div>

                  <div class="col-12">
                    <label class="form-label">Event Shortcode</label>
                    <input class="form-control" id="event_shortcode" maxlength="255" placeholder="e.g., annual-fest-2026">
                    <div class="form-text">Recommended for album cards and event-wise gallery listing.</div>
                  </div>

                  <div class="col-12">
                    <div class="freeze-note" id="eventFreezeHint">
                      <i class="fa fa-lock me-1"></i>
                      These event fields are being used from the selected existing event and are locked.
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <div class="form-section-title">
                <i class="fa fa-code"></i> Metadata
              </div>
              <div class="form-section-sub">Optional structured data for internal use.</div>

              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Metadata (optional JSON)</label>
                  <textarea class="form-control" id="metadata" rows="5" placeholder='{"album":"fest","camera":"Nikon"}'></textarea>
                  <div class="form-text">If filled, must be valid JSON.</div>
                </div>
              </div>
            </div>
          </div>

          {{-- RIGHT --}}
          <div class="col-12 col-xl-5">
            <div class="preview-sticky d-flex flex-column gap-3">
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

              <div class="form-section">
                <div class="form-section-title">
                  <i class="fa fa-bullhorn"></i> Publishing & Visibility
                </div>
                <div class="form-section-sub">Move these controls to the right for a shorter and cleaner form flow.</div>

                <div class="row g-3">
                  <div class="col-md-4 col-xl-12 col-xxl-4">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="status">
                      <option value="draft">Draft</option>
                      <option value="published">Published</option>
                      <option value="archived">Archived</option>
                    </select>
                  </div>

                  <div class="col-md-4 col-xl-12 col-xxl-4">
                    <label class="form-label">Featured on Home</label>
                    <select class="form-select" id="is_featured_home">
                      <option value="0">No</option>
                      <option value="1">Yes</option>
                    </select>
                  </div>

                  <div class="col-md-4 col-xl-12 col-xxl-4">
                    <label class="form-label">Sort Order</label>
                    <input type="number" class="form-control" id="sort_order" min="0" max="1000000" value="0">
                  </div>

                  <div class="col-12">
                    <label class="form-label">Publish At</label>
                    <input type="datetime-local" class="form-control" id="publish_at">
                  </div>

                  <div class="col-12">
                    <label class="form-label">Expire At</label>
                    <input type="datetime-local" class="form-control" id="expire_at">
                  </div>
                </div>
              </div>

              <div class="form-section">
                <div class="form-section-title">
                  <i class="fa fa-upload"></i> Image Source
                </div>
                <div class="form-section-sub">Upload a new image or keep an existing path/URL.</div>

                <div class="row g-3">
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
                </div>
              </div>

              <div class="gl-panel">
                <div class="fw-semibold mb-2">
                  <i class="fa fa-circle-info me-1"></i>Notes
                </div>
                <div class="text-muted small mb-2">
                  “Active” tab loads <b>published</b> items. “Inactive” tab loads <b>draft/archived</b>.
                </div>
                <div class="text-muted small">
                  When you choose <b>Use Existing Event</b>, the form sends <code>selected_event_shortcode</code>. When you choose <b>Create / Edit Custom Event</b>, it sends the event fields directly.
                </div>
              </div>
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

{{-- Workflow History Modal --}}
<div class="modal fade" id="glHistoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-clock-rotate-left me-2"></i>Workflow History</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="glHistoryLoading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
          <div class="mt-2 text-muted">Loading history…</div>
        </div>
        <div id="glHistoryContent" style="display:none;">
          <ul class="timeline" id="glHistoryTimeline"></ul>
        </div>
        <div id="glHistoryEmpty" class="text-center py-4 text-muted" style="display:none;">
          <i class="fa fa-history mb-2 fs-3 opacity-50"></i>
          <div>No history found for this item.</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

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

  function badgeStatus(status, hasDraft){
    const s = (status || '').toString().toLowerCase();
    let html = '';
    if (s === 'published') html = `<span class="badge badge-soft-success">Published</span>`;
    else if (s === 'draft') html = `<span class="badge badge-soft-warning">Draft</span>`;
    else if (s === 'archived') html = `<span class="badge badge-soft-muted">Archived</span>`;
    else html = `<span class="badge badge-soft-muted">${esc(s || '—')}</span>`;

    if (hasDraft) {
      html += `<span class="badge-pending-draft" title="Pending Changes">Draft</span>`;
    }
    return html;
  }

  function workflowBadge(ws){
    const s = (ws || '').toString().toLowerCase();
    if (s === 'pending_check') return `<span class="badge-soft-warning p-1 px-2 rounded-pill small"><i class="fa fa-hourglass-start me-1"></i>Pending Check</span>`;
    if (s === 'checked') return `<span class="badge-soft-info p-1 px-2 rounded-pill small"><i class="fa fa-check-double me-1"></i>Checked</span>`;
    if (s === 'approved') return `<span class="badge-soft-success p-1 px-2 rounded-pill small"><i class="fa fa-circle-check me-1"></i>Approved</span>`;
    if (s === 'rejected') return `<span class="badge-soft-danger p-1 px-2 rounded-pill small"><i class="fa fa-circle-xmark me-1"></i>Rejected</span>`;
    return `<span class="badge-soft-muted p-1 px-2 rounded-pill small">${esc(s || '—')}</span>`;
  }

  function badgeYesNo(v){
    return v ? `<span class="badge badge-soft-primary">Yes</span>` : `<span class="badge badge-soft-muted">No</span>`;
  }

  function dtLocal(s){
    if (!s) return '';
    const t = String(s).replace(' ', 'T');
    return t.length >= 16 ? t.slice(0,16) : t;
  }

  function slugify(v){
    return String(v || '')
      .trim()
      .toLowerCase()
      .replace(/\s+/g, '-')
      .replace(/[^a-z0-9\-_]/g, '')
      .replace(/-+/g, '-')
      .replace(/^[-_]+|[-_]+$/g, '');
  }

  function extractEventFromItem(r = {}){
    const ev = (r?.event && typeof r.event === 'object') ? r.event : {};
    return {
      title: ev?.title || r?.event_title || '',
      description: ev?.description || r?.event_description || '',
      date: ev?.date || r?.event_date || '',
      shortcode: ev?.shortcode || r?.event_shortcode || ''
    };
  }

  function parseTagsValue(r = {}){
    const raw = r.tags_json ?? r.tags ?? [];
    if (Array.isArray(raw)) return raw.filter(Boolean);
    if (typeof raw === 'string'){
      try{
        const parsed = JSON.parse(raw);
        if (Array.isArray(parsed)) return parsed.filter(Boolean);
      }catch(_){}
      return raw.split(',').map(x => x.trim()).filter(Boolean);
    }
    return [];
  }

  function dedupeEvents(list = []){
    const seen = new Set();
    return list.filter(item => {
      const code = String(item?.event?.shortcode || '').trim();
      if (!code || seen.has(code)) return false;
      seen.add(code);
      return true;
    });
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

    function cleanupBackdropsIfNoModal(){
      if (document.querySelector('.modal.show')) return;
      document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('overflow');
      document.body.style.removeProperty('padding-right');
    }

    // Filter modal
    const filterModalEl = $('filterModal');
    const filterModal = filterModalEl ? bootstrap.Modal.getOrCreateInstance(filterModalEl) : null;
    filterModalEl?.addEventListener('hidden.bs.modal', () => cleanupBackdropsIfNoModal());

    // Item modal
    const itemModalEl = $('itemModal');
    const itemModal = itemModalEl ? bootstrap.Modal.getOrCreateInstance(itemModalEl) : null;
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

    const eventModeSel = $('event_mode');
    const existingEventWrap = $('existingEventWrap');
    const existingEventSel = $('selected_event_shortcode');
    const eventTitleInput = $('event_title');
    const eventDescInput = $('event_description');
    const eventDateInput = $('event_date');
    const eventShortcodeInput = $('event_shortcode');
    const eventFreezeHint = $('eventFreezeHint');

    const imagePreview = $('imagePreview');
    const imageEmpty = $('imageEmpty');
    const imageMeta = $('imageMeta');
    const btnOpenImage = $('btnOpenImage');

    const modalSort = $('modal_sort');
    const modalFeatured = $('modal_featured');
    const modalDepartment = $('modal_department');
    const modalVisibleNow = $('modal_visible_now');
    const modalHasEvent = $('modal_has_event');

    // permissions
    const ACTOR = { role: '' };
    let canCreate=false, canEdit=false, canDelete=false;

    function computePermissions(){
      const r = (ACTOR.role || '').toLowerCase();
      const writeRoles = ['admin','author','director','principal','hod','faculty','technical_assistant','it_person','super_admin'];
      const deleteRoles = ['admin','author','director','principal','super_admin'];

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
      filters: { sort:'-created_at', featured:'', department:'', visible_now:'', has_event:'' },
      tabs: {
        active:   { page:1, lastPage:1, items:[] },
        inactive: { page:1, lastPage:1, items:[] },
        trash:    { page:1, lastPage:1, items:[] }
      }
    };

    // event state
    let eventOptionsCache = [];
    let currentEventScopeKey = '';
    let manualEventDraft = { title:'', description:'', date:'', shortcode:'' };

    function getTabKey(){
      const a = document.querySelector('.nav-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#tab-active';
      if (href === '#tab-inactive') return 'inactive';
      if (href === '#tab-trash') return 'trash';
      return 'active';
    }

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

      const s = state.filters.sort || '-created_at';
      params.set('sort', s.startsWith('-') ? s.slice(1) : s);
      params.set('direction', s.startsWith('-') ? 'desc' : 'asc');

      if (state.filters.featured !== '') params.set('featured', state.filters.featured);
      if (state.filters.has_event !== '') params.set('has_event', state.filters.has_event);

      if (state.filters.department && state.filters.department !== '__global__') {
        params.set('department', state.filters.department);
      }

      if (state.filters.visible_now) params.set('visible_now', '1');

      if (tabKey === 'trash') {
        return `/api/gallery-trash?${params.toString()}`;
      }

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

    function eventText(r){
      const ev = extractEventFromItem(r);
      if (!ev.shortcode && !ev.title && !ev.date) {
        return `<div class="g-meta-row"><span class="g-event-chip"><i class="fa fa-calendar-xmark"></i>No event</span></div>`;
      }

      const title = ev.title ? esc(ev.title) : 'Untitled Event';
      const date = ev.date ? esc(ev.date) : 'No date';
      const code = ev.shortcode ? esc(ev.shortcode) : '—';

      return `
        <div class="g-meta-row">
          <span class="g-event-chip"><i class="fa fa-calendar-days"></i>${title}</span>
          <span class="g-event-chip"><i class="fa fa-clock"></i>${date}</span>
          <span class="g-event-chip g-event-code"><i class="fa fa-link"></i>${code}</span>
        </div>
      `;
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
      const filtered = onlyGlobal ? rows.filter(r => !r.department_id) : rows;

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
        const sortV = String(sortOrder);
        const dtStr = publishAt && publishAt !== '—' ? dtLocal(publishAt).replace('T', ' ') : '—';
        const updatedAt = updated && updated !== '—' ? dtLocal(updated).replace('T', ' ') : '—';
        const deletedAt = deleted && deleted !== '—' ? dtLocal(deleted).replace('T', ' ') : '—';

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
            ${eventText(r)}
          </div>
        `;

        let actions = `
          <div class="dropdown text-end">
            <button type="button" class="btn btn-light btn-sm gl-dd-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
              <i class="fa fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><button type="button" class="dropdown-item" onclick="galleryModule.openModal('view', ${JSON.stringify(r).replace(/"/g, '&quot;')})"><i class="fa fa-eye"></i> View</button></li>
              <li><button type="button" class="dropdown-item" onclick="galleryModule.showHistory('gallery', ${r.id})"><i class="fa fa-clock-rotate-left"></i> Workflow History</button></li>`;

        if (tabKey !== 'trash' && canEdit){
          actions += `<li><button type="button" class="dropdown-item" onclick="galleryModule.openModal('edit', ${JSON.stringify(r).replace(/"/g, '&quot;')})"><i class="fa fa-pen-to-square"></i> Edit</button></li>`;
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
              <td>${esc(deletedAt)}</td>
              <td>${badgeStatus(status)}</td>
              <td><span class="badge badge-soft-muted font-monospace">${esc(sortV)}</span></td>
              <td class="text-end">${actions}</td>
            </tr>`;
        }

        return `
          <tr data-uuid="${esc(uuid)}">
            <td>${titleCell}</td>
            <td>${titleText}</td>
            <td>${deptText(r)}</td>
            <td>${badgeStatus(status, !!r.draft_data)}</td>
            <td>${badgeYesNo(featured)}</td>
            <td>${esc(dtStr)}</td>
            <td><span class="badge badge-soft-muted font-monospace">${esc(sortV)}</span></td>
            <td><span class="badge badge-soft-muted"><i class="fa fa-eye me-1"></i> ${views}</span></td>
            <td>${workflowBadge(r.workflow_status)}</td>
            <td>${esc(updatedAt)}</td>
            <td class="text-end">${actions}</td>
          </tr>`;
      }).join('');

      renderPager(tabKey);
    }

    async function loadTab(tabKey){
      const tbody = (tabKey==='active' ? tbodyActive : (tabKey==='inactive' ? tbodyInactive : tbodyTrash));
      if (tbody){
        const cols = (tabKey==='trash' ? 7 : 11);
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
      if (modalHasEvent) modalHasEvent.value = (state.filters.has_event ?? '');
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.sort = modalSort?.value || '-created_at';
      state.filters.featured = (modalFeatured?.value ?? '');
      state.filters.department = (modalDepartment?.value ?? '');
      state.filters.visible_now = (modalVisibleNow?.value ?? '');
      state.filters.has_event = (modalHasEvent?.value ?? '');

      filterModal && filterModal.hide();
      setTimeout(() => cleanupBackdropsIfNoModal(), 0);

      reloadAll();
    });

    btnReset?.addEventListener('click', () => {
      state.q = '';
      state.filters = { sort:'-created_at', featured:'', department:'', visible_now:'', has_event:'' };
      state.perPage = 20;

      if (searchInput) searchInput.value = '';
      if (perPageSel) perPageSel.value = '20';

      if (modalSort) modalSort.value = '-created_at';
      if (modalFeatured) modalFeatured.value = '';
      if (modalDepartment) modalDepartment.value = '';
      if (modalVisibleNow) modalVisibleNow.value = '';
      if (modalHasEvent) modalHasEvent.value = '';

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

    async function loadDepartments(){
      try{
        const res = await fetchWithTimeout('/api/departments?per_page=200', { headers: authHeaders() }, 12000);
        if (!res.ok) return;
        const js = await res.json().catch(()=> ({}));
        const rows = Array.isArray(js.data) ? js.data : [];
        if (!rows.length) return;

        if (modalDepartment){
          const keep = `<option value="">All</option><option value="__global__">Global (No Department)</option>`;
          modalDepartment.innerHTML = keep + rows.map(d => {
            const id = d.id ?? '';
            const title = d.title || d.name || d.slug || ('Dept #' + id);
            return `<option value="${esc(String(id))}">${esc(String(title))}</option>`;
          }).join('');
        }

        if (departmentSel){
          departmentSel.innerHTML = `<option value="">Global (No Department)</option>` + rows.map(d => {
            const id = d.id ?? '';
            const title = d.title || d.name || d.slug || ('Dept #' + id);
            return `<option value="${esc(String(id))}">${esc(String(title))}</option>`;
          }).join('');
        }
      }catch(_){}
    }

    function getEventScopeKey(){
      const dept = (departmentSel?.value || '').trim();
      return dept ? `dept:${dept}` : 'all';
    }

    function currentEventFields(){
      return {
        title: (eventTitleInput?.value || '').trim(),
        description: (eventDescInput?.value || '').trim(),
        date: (eventDateInput?.value || '').trim(),
        shortcode: (eventShortcodeInput?.value || '').trim()
      };
    }

    function setEventFieldsReadonly(flag){
      [eventTitleInput, eventDescInput, eventDateInput, eventShortcodeInput].forEach(el => {
        if (!el) return;
        el.readOnly = !!flag;
        el.classList.toggle('event-readonly', !!flag);
      });
      if (eventFreezeHint) eventFreezeHint.style.display = flag ? '' : 'none';
    }

    function clearEventFields(){
      if (eventTitleInput) eventTitleInput.value = '';
      if (eventDescInput) eventDescInput.value = '';
      if (eventDateInput) eventDateInput.value = '';
      if (eventShortcodeInput) eventShortcodeInput.value = '';
    }

    function fillEventFields(ev = {}){
      if (eventTitleInput) eventTitleInput.value = ev.title || '';
      if (eventDescInput) eventDescInput.value = ev.description || '';
      if (eventDateInput) eventDateInput.value = ev.date || '';
      if (eventShortcodeInput) eventShortcodeInput.value = ev.shortcode || '';
    }

    function renderEventOptions(selected=''){
      if (!existingEventSel) return;
      const base = [`<option value="">Select an event</option>`];
      base.push(...eventOptionsCache.map(item => {
        const ev = item.event || {};
        const label = [
          ev.title || 'Untitled Event',
          ev.date ? `(${ev.date})` : '',
          ev.shortcode ? `— ${ev.shortcode}` : '',
          item.source === 'current' ? '• current item' : ''
        ].filter(Boolean).join(' ');
        return `<option value="${esc(ev.shortcode || '')}">${esc(label)}</option>`;
      }));
      existingEventSel.innerHTML = base.join('');
      existingEventSel.value = selected || '';
    }

    async function loadEventOptions(force=false, selected='', fallbackEvent=null){
      const scopeKey = getEventScopeKey();

      if (!force && eventOptionsCache.length && currentEventScopeKey === scopeKey){
        let cached = eventOptionsCache.slice();
        if (fallbackEvent?.shortcode && !cached.some(x => (x?.event?.shortcode || '') === fallbackEvent.shortcode)) {
          cached.unshift({
            event: { ...fallbackEvent },
            images_count: 0,
            source: 'current'
          });
        }
        eventOptionsCache = dedupeEvents(cached);
        renderEventOptions(selected || fallbackEvent?.shortcode || '');
        return;
      }

      try{
        const params = new URLSearchParams();
        params.set('per_page', '500');
        const dept = (departmentSel?.value || '').trim();
        if (dept) params.set('department', dept);

        const res = await fetchWithTimeout(`/api/gallery-events?${params.toString()}`, {
          headers: authHeaders()
        }, 12000);

        let normalized = [];

        if (res.ok){
          const js = await res.json().catch(()=> ({}));
          const rows = Array.isArray(js.data) ? js.data : [];
          normalized = rows.map(r => ({
            event: {
              title: r?.event?.title || r.event_title || '',
              description: r?.event?.description || r.event_description || '',
              date: r?.event?.date || r.event_date || '',
              shortcode: r?.event?.shortcode || r.event_shortcode || ''
            },
            images_count: r.images_count || 0,
            source: 'api'
          })).filter(x => x.event.shortcode);
        }

        if (fallbackEvent?.shortcode && !normalized.some(x => (x?.event?.shortcode || '') === fallbackEvent.shortcode)) {
          normalized.unshift({
            event: { ...fallbackEvent },
            images_count: 0,
            source: 'current'
          });
        }

        eventOptionsCache = dedupeEvents(normalized);
        currentEventScopeKey = scopeKey;
        renderEventOptions(selected || fallbackEvent?.shortcode || '');
      }catch(_){
        const fallbackList = [];
        if (fallbackEvent?.shortcode){
          fallbackList.push({
            event: { ...fallbackEvent },
            images_count: 0,
            source: 'current'
          });
        }
        eventOptionsCache = dedupeEvents(fallbackList);
        currentEventScopeKey = scopeKey;
        renderEventOptions(selected || fallbackEvent?.shortcode || '');
      }
    }

    function findEventOption(shortcode){
      const code = String(shortcode || '').trim();
      return eventOptionsCache.find(x => (x?.event?.shortcode || '') === code) || null;
    }

    function applyEventMode(mode, opts={}){
      const { keepSelection=false, restoreDraft=false } = opts;

      if (mode === 'existing'){
        manualEventDraft = currentEventFields();
      }

      if (existingEventWrap) existingEventWrap.style.display = (mode === 'existing') ? '' : 'none';

      if (mode === 'none'){
        if (!keepSelection && existingEventSel) existingEventSel.value = '';
        clearEventFields();
        setEventFieldsReadonly(true);
        return;
      }

      if (mode === 'manual'){
        if (!keepSelection && existingEventSel) existingEventSel.value = '';
        if (restoreDraft) fillEventFields(manualEventDraft);
        setEventFieldsReadonly(false);
        return;
      }

      if (mode === 'existing'){
        setEventFieldsReadonly(true);
        const selected = existingEventSel?.value || '';
        if (!selected){
          clearEventFields();
          return;
        }
        const hit = findEventOption(selected);
        if (hit?.event) fillEventFields(hit.event);
      }
    }

    eventModeSel?.addEventListener('change', async () => {
      const mode = eventModeSel.value || 'none';
      if (mode === 'existing') {
        const fallback = currentEventFields();
        await loadEventOptions(false, existingEventSel?.value || fallback.shortcode || '', fallback.shortcode ? fallback : null);
        applyEventMode('existing', { keepSelection:true });
      } else if (mode === 'manual') {
        applyEventMode('manual', { restoreDraft:true });
      } else {
        applyEventMode('none');
      }
    });

    existingEventSel?.addEventListener('change', () => {
      if ((eventModeSel?.value || '') !== 'existing') return;
      applyEventMode('existing', { keepSelection:true });
    });

    departmentSel?.addEventListener('change', async () => {
      const currentEv = currentEventFields();
      const selectedCode = (existingEventSel?.value || currentEv.shortcode || '').trim();
      await loadEventOptions(true, selectedCode, currentEv.shortcode ? currentEv : null);

      if ((eventModeSel?.value || '') === 'existing') {
        if (existingEventSel && !findEventOption(existingEventSel.value) && selectedCode) {
          existingEventSel.value = selectedCode;
        }
        applyEventMode('existing', { keepSelection:true });
      }
    });

    eventTitleInput?.addEventListener('input', debounce(() => {
      if ((eventModeSel?.value || '') !== 'manual') return;
      const code = (eventShortcodeInput?.value || '').trim();
      if (!code && eventTitleInput) {
        eventShortcodeInput.value = slugify(eventTitleInput.value);
      }
    }, 180));

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
      if (!f) return;
      if (imgObjectUrl){ try{ URL.revokeObjectURL(imgObjectUrl); }catch(_){ } }
      imgObjectUrl = URL.createObjectURL(f);
      setImagePreview(imgObjectUrl, `${f.name || 'image'} • ${bytes(f.size)}`);
    });

    imagePathInput?.addEventListener('input', debounce(() => {
      const v = (imagePathInput.value || '').trim();
      if (!v) return;
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
      manualEventDraft = { title:'', description:'', date:'', shortcode:'' };

      clearImagePreview(true);

      if (imgRequiredStar) imgRequiredStar.style.display = '';
      itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
        if (el.id === 'itemUuid' || el.id === 'itemId') return;
        if (el.type === 'file') el.disabled = false;
        else if (el.tagName === 'SELECT') el.disabled = false;
        else el.readOnly = false;
        el.classList.remove('event-readonly');
      });

      if (saveBtn) saveBtn.style.display = '';
      itemForm.dataset.mode = 'edit';
      itemForm.dataset.intent = 'create';

      if (eventModeSel) eventModeSel.value = 'none';
      if (existingEventSel) existingEventSel.value = '';
      clearEventFields();
      setEventFieldsReadonly(true);
      if (existingEventWrap) existingEventWrap.style.display = 'none';
    }

    async function fetchItem(uuid, withTrashed=false){
      const qs = withTrashed ? '?with_trashed=1' : '';
      const res = await fetchWithTimeout(`/api/gallery/${encodeURIComponent(uuid)}${qs}`, { headers: authHeaders() }, 12000);
      const js = await res.json().catch(()=> ({}));
      if (!res.ok || js?.success === false) throw new Error(js?.message || 'Failed to load item');
      return js?.item || js?.data || js;
    }

    async function fillFormFromItem(r, viewOnly=false){
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

      const tagsArr = parseTagsValue(r);
      tagsInput.value = tagsArr.length ? tagsArr.join(', ') : '';

      const meta = r.metadata ?? null;
      metadataInput.value = meta ? (typeof meta === 'string' ? meta : JSON.stringify(meta, null, 2)) : '';

      imagePathInput.value = (r.image || '');
      clearImagePreview(true);
      setImagePreview(r.image_url || r.image || '', 'Current image');

      if (imgRequiredStar) imgRequiredStar.style.display = (r.uuid ? 'none' : '');

      const ev = extractEventFromItem(r);
      const hasEvent = !!(ev.title || ev.description || ev.date || ev.shortcode);

      manualEventDraft = {
        title: ev.title || '',
        description: ev.description || '',
        date: ev.date || '',
        shortcode: ev.shortcode || ''
      };

      await loadEventOptions(true, ev.shortcode || '', ev.shortcode ? ev : null);

      const matchedEvent = ev.shortcode ? findEventOption(ev.shortcode) : null;
      const shouldUseExisting = !!(matchedEvent && matchedEvent.source === 'api' && ev.shortcode);

      if (hasEvent){
        if (shouldUseExisting){
          if (eventModeSel) eventModeSel.value = 'existing';
          if (existingEventSel) existingEventSel.value = ev.shortcode || '';
          applyEventMode('existing', { keepSelection:true });
        } else {
          if (eventModeSel) eventModeSel.value = 'manual';
          if (existingEventSel) existingEventSel.value = '';
          applyEventMode('manual', { restoreDraft:true });
          fillEventFields(ev);
        }
      } else {
        if (eventModeSel) eventModeSel.value = 'none';
        applyEventMode('none');
      }

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

    let currentGalleryForHistory = null;
    function openModal(mode, r = null){
      resetForm();
      const title = (mode === 'view') ? 'View Gallery Item' : (mode === 'edit' ? 'Edit Gallery Item' : 'Add Gallery Item');
      if (itemModalTitle) itemModalTitle.textContent = title;

      // Reset Workflow Alerts
      $('glRejectionAlert').style.display = 'none';
      $('glDraftAlert').style.display = 'none';

      if (r) {
        currentGalleryForHistory = { table: 'gallery', id: r.id };
        fillFormFromItem(r, mode === 'view');
        
        // Workflow Alert Logic
        if (r.workflow_status === 'rejected') {
          $('glRejectionAlert').style.display = 'block';
          $('glRejectionReasonText').textContent = r.rejected_reason || r.rejection_reason || 'No reason provided.';
        }
        if (r.draft_data) {
          $('glDraftAlert').style.display = 'block';
        }
      }
      itemModal && itemModal.show();
    }

    const glHistoryModal = new bootstrap.Modal($('glHistoryModal'));
    async function showHistory(table, id) {
      glHistoryModal.show();
      $('glHistoryLoading').style.display = 'block';
      $('glHistoryContent').style.display = 'none';
      $('glHistoryEmpty').style.display = 'none';
      $('glHistoryTimeline').innerHTML = '';

      try {
        const res = await fetchWithTimeout(`/api/master-approval/history/${table}/${id}`, { headers: authHeaders() });
        const js = await res.json();
        $('glHistoryLoading').style.display = 'none';

        if (js.success && js.data && js.data.length) {
          $('glHistoryTimeline').innerHTML = js.data.map(log => `
            <li class="timeline-item">
              <div class="timeline-marker"></div>
              <div class="timeline-content">
                <div class="timeline-date">${new Date(log.created_at).toLocaleString()}</div>
                <div class="timeline-title">
                  Status changed to <span class="badge ${getStatusClass(log.to_status)}">${log.to_status.replace('_', ' ')}</span>
                </div>
                <div class="timeline-author">Action by: ${esc(log.user_name || 'System')} (${esc(log.user_role || 'unknown')})</div>
                ${log.comment ? `<div class="timeline-comment">${esc(log.comment)}</div>` : ''}
              </div>
            </li>
          `).join('');
          $('glHistoryContent').style.display = 'block';
        } else {
          $('glHistoryEmpty').style.display = 'block';
        }
      } catch (err) {
        $('glHistoryLoading').style.display = 'none';
        $('glHistoryEmpty').style.display = 'block';
      }
    }

    function getStatusClass(s) {
      s = s.toLowerCase();
      if (s === 'approved') return 'badge-soft-success text-success';
      if (s === 'rejected') return 'badge-soft-danger text-danger';
      if (s === 'checked') return 'badge-soft-info text-info';
      if (s === 'pending_check') return 'badge-soft-warning text-warning';
      return 'badge-soft-muted text-muted';
    }

    window.viewGalleryHistoryFromAlert = () => {
      if (currentGalleryForHistory) {
        showHistory(currentGalleryForHistory.table, currentGalleryForHistory.id);
      }
    };

    window.galleryModule = {
  openModal,
  showHistory
};

    btnAddItem?.addEventListener('click', async () => {
      if (!canCreate) return;
      openModal('create');
      await loadEventOptions(true);
    });

    itemModalEl?.addEventListener('hidden.bs.modal', () => {
      if (imgObjectUrl){ try{ URL.revokeObjectURL(imgObjectUrl); }catch(_){ } imgObjectUrl=null; }
    });

    // dropdown safety
    function closeAllDropdownsExcept(exceptToggle){
      document.querySelectorAll('.gl-dd-toggle').forEach(t => {
        if (t === exceptToggle) return;
        try{
          const inst = bootstrap.Dropdown.getInstance(t);
          inst && inst.hide();
        }catch(_){}
      });
    }

    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('.gl-dd-toggle');
      if (!toggle) return;

      e.preventDefault();
      e.stopPropagation();

      closeAllDropdownsExcept(toggle);

      try{
        const inst = bootstrap.Dropdown.getOrCreateInstance(toggle, {
          autoClose: true,
          popperConfig: (def) => {
            const base = def || {};
            const mods = Array.isArray(base.modifiers) ? base.modifiers.slice() : [];
            mods.push({ name:'preventOverflow', options:{ boundary:'viewport', padding:8 } });
            mods.push({ name:'flip', options:{ boundary:'viewport', padding:8 } });
            return { ...base, strategy:'fixed', modifiers: mods };
          }
        });
        inst.toggle();
      }catch(_){}
    });

    document.addEventListener('click', () => {
      closeAllDropdownsExcept(null);
    }, { capture:true });

    // row actions
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;

      const tr = btn.closest('tr');
      const uuid = tr?.dataset?.uuid;
      const act = btn.dataset.action;
      if (!uuid) return;

      const toggle = btn.closest('.dropdown')?.querySelector('.gl-dd-toggle');
      if (toggle) { try { bootstrap.Dropdown.getInstance(toggle)?.hide(); } catch (_) {} }

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

        const metaRaw = (metadataInput.value || '').trim();
        if (metaRaw){
          try{ JSON.parse(metaRaw); }catch(_){
            err('Metadata must be valid JSON');
            metadataInput.focus();
            return;
          }
        }

        const fd = new FormData();

        const deptVal = (departmentSel.value || '').trim();
        fd.append('department_id', deptVal);

        const t = (titleInput.value || '').trim();
        const d = (descInput.value || '').trim();
        const tags = (tagsInput.value || '').trim();
        const st = (statusSel.value || 'draft').trim();
        const feat = (featuredSel.value || '0').trim();
        const so = String(parseInt(sortOrderInput.value || '0', 10) || 0);

        if (t) fd.append('title', t);
        if (d) fd.append('description', d);
        if (tags) fd.append('tags_json', tags);
        fd.append('status', st);
        fd.append('is_featured_home', feat === '1' ? '1' : '0');
        fd.append('sort_order', so);

        if ((publishAtInput.value || '').trim()) fd.append('publish_at', publishAtInput.value);
        if ((expireAtInput.value || '').trim()) fd.append('expire_at', expireAtInput.value);

        if (metaRaw) fd.append('metadata', metaRaw);

        const imgFile = imageFileInput.files?.[0] || null;
        const imgPath = (imagePathInput.value || '').trim();

        if (imgFile) fd.append('image_file', imgFile);
        if (imgPath) fd.append('image', imgPath);

        if (!isEdit){
          if (!imgFile && !imgPath){
            err('Image is required (upload a file or provide a path/URL).');
            imageFileInput.focus();
            return;
          }
        }

        // Event payload
        const eventMode = eventModeSel?.value || 'none';

        if (eventMode === 'existing'){
          const selectedCode = (existingEventSel?.value || '').trim();
          if (!selectedCode){
            err('Please select an existing event.');
            existingEventSel?.focus();
            return;
          }
          fd.append('selected_event_shortcode', selectedCode);
        } else if (eventMode === 'manual'){
          const evTitle = (eventTitleInput?.value || '').trim();
          const evDesc = (eventDescInput?.value || '').trim();
          const evDate = (eventDateInput?.value || '').trim();
          let evCode = (eventShortcodeInput?.value || '').trim();

          if (!evCode && evTitle) {
            evCode = slugify(evTitle);
            if (eventShortcodeInput) eventShortcodeInput.value = evCode;
          }

          const hasAnyEventValue = !!(evTitle || evDesc || evDate || evCode || isEdit);

          if (hasAnyEventValue){
            fd.append('event_title', evTitle);
            fd.append('event_description', evDesc);
            fd.append('event_date', evDate);
            fd.append('event_shortcode', evCode);
          }
        } else {
          fd.append('event_title', '');
          fd.append('event_description', '');
          fd.append('event_date', '');
          fd.append('event_shortcode', '');
        }

        const url = isEdit
          ? `/api/gallery/${encodeURIComponent(itemUuid.value)}`
          : `/api/gallery`;

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
        await loadEventOptions(true);
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