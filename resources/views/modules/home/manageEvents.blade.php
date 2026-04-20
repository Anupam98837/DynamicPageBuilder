{{-- resources/views/modules/event/manageEvents.blade.php --}}
@section('title','Events')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Events (Admin) - UI Shell
 * ========================= */

.ev-wrap{max-width:1200px;margin:16px auto 40px;padding:0 6px;overflow:visible}

/* Tabs */
.ev-tabs.nav-tabs{border-color:var(--line-strong)}
.ev-tabs .nav-link{color:var(--ink)}
.ev-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface);
}
.tab-content,.tab-pane{overflow:visible}

/* Toolbar */
.ev-toolbar.panel{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:16px;
  box-shadow:var(--shadow-2);
  padding:12px 12px;
}

/* Card/Table */
.ev-card{
  position:relative;
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible !important;
}
.ev-card .card-body{overflow:visible !important}

/* Horizontal scroll */
.table-responsive{
  display:block;width:100%;
  overflow-x:auto !important;
  overflow-y:visible !important;
  -webkit-overflow-scrolling:touch;
  position:relative;
  z-index:1; /* keep low */
}
.table-responsive > .table{width:max-content;min-width:1160px}
.table-responsive th,.table-responsive td{white-space:nowrap}

/* Footer bar (pagination row) MUST stay low */
.ev-footerbar{
  position:relative;
  z-index:0 !important; /* ✅ important: keep it under portal menu */
}

/* Table */
.table{--bs-table-bg:transparent}
.table thead th{
  font-weight:600;
  color:var(--muted-color);
  font-size:13px;
  border-bottom:1px solid var(--line-strong);
  background:var(--surface);
}
.table thead.sticky-top{z-index:3}
.table tbody tr{border-top:1px solid var(--line-soft)}
.table tbody tr:hover{background:var(--page-hover)}
.small{font-size:12.5px}

/* Slug col */
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

/* Badges */
.badge-soft-success{
  background:color-mix(in oklab, var(--success-color) 12%, transparent);
  color:var(--success-color);
}
.badge-soft-warning{
  background:color-mix(in oklab, var(--warning-color, #f59e0b) 14%, transparent);
  color:var(--warning-color, #f59e0b);
}
.badge-soft-muted{
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
  color:var(--muted-color);
}
.badge-soft-primary{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  color:var(--primary-color);
}
.badge-soft-danger{background:color-mix(in oklab, var(--danger-color) 12%, transparent);color:var(--danger-color)}
.badge-soft-info{background:color-mix(in oklab, #0ea5e9 14%, transparent);color:#0ea5e9}

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

/* Dropdown toggle */
.ev-dd-toggle{
  position:relative;
  z-index:2;
  border-radius:10px;
}

/* Dropdown menu base */
.dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:230px;
}
.dropdown-menu.show{display:block !important}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color) !important}

/* ✅ PORTAL MENU (moves to <body>) */
.ev-dd-portal{
  position:fixed !important;
  z-index:2147483647 !important; /* max-ish */
}

/* Loading overlay */
.ev-loading{
  position:fixed;inset:0;
  background:rgba(0,0,0,.45);
  display:none;
  align-items:center;justify-content:center;
  z-index:9999;
  backdrop-filter:blur(2px);
}
.ev-loading .box{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,0.3);
}
.ev-loading .spin{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,.3);
  border-top:4px solid var(--primary-color);
  animation:evspin 1s linear infinite;
}
@keyframes evspin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

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
  animation:evspin 1s linear infinite;
}

/* Empty */
.ev-empty{color:var(--muted-color)}

/* Responsive toolbar */
@media (max-width: 768px){
  .ev-toolbar .ev-row{flex-direction:column;gap:12px !important}
  .ev-toolbar .ev-search{min-width:100% !important}
  .ev-actions{display:flex;gap:8px;flex-wrap:wrap}
  .ev-actions .btn{flex:1;min-width:140px}
}

/* =========================
 * Mini RTE
 * ========================= */
.ev-rte-wrap{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:var(--surface);
}
.ev-rte-toolbar{
  display:flex;align-items:center;gap:6px;flex-wrap:wrap;
  padding:8px;
  border-bottom:1px solid var(--line-strong);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.ev-rte-btn{
  border:1px solid var(--line-soft);
  background:transparent;
  color:var(--ink);
  padding:7px 9px;
  border-radius:10px;
  line-height:1;
  cursor:pointer;
  display:inline-flex;
  align-items:center;justify-content:center;
  user-select:none;
}
.ev-rte-btn:hover{background:var(--page-hover)}
.ev-rte-btn.active{
  background:color-mix(in oklab, var(--primary-color) 14%, transparent);
  border-color:color-mix(in oklab, var(--primary-color) 35%, var(--line-soft));
}
.ev-rte-sep{width:1px;height:24px;background:var(--line-soft);margin:0 4px}
.ev-rte-tabs{
  margin-left:auto;
  display:flex;
  border:1px solid var(--line-soft);
  border-radius:0;
  overflow:hidden;
}
.ev-rte-tabs .tab{
  border:0;border-right:1px solid var(--line-soft);
  border-radius:0;
  padding:7px 12px;
  font-size:12px;
  cursor:pointer;
  background:transparent;
  color:var(--ink);
  line-height:1;
  user-select:none;
}
.ev-rte-tabs .tab:last-child{border-right:0}
.ev-rte-tabs .tab.active{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  font-weight:700;
}
.ev-rte-area{position:relative}
.ev-rte-editor{
  min-height:200px;
  padding:12px 12px;
  outline:none;
}
.ev-rte-editor:empty:before{content:attr(data-placeholder);color:var(--muted-color);}
.ev-rte-code{
  display:none;
  width:100%;
  min-height:200px;
  padding:12px 12px;
  border:0;
  outline:none;
  resize:vertical;
  background:transparent;
  color:var(--ink);
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  font-size:12.5px;
  line-height:1.45;
}
.ev-rte-wrap.mode-code .ev-rte-editor{display:none}
.ev-rte-wrap.mode-code .ev-rte-code{display:block}

/* Cover preview */
.ev-cover{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:var(--bg-soft, color-mix(in oklab, var(--surface) 88%, var(--bg-body)));
}
.ev-cover .top{
  display:flex;align-items:center;justify-content:space-between;gap:10px;
  padding:10px 12px;border-bottom:1px solid var(--line-soft);
}
.ev-cover .body{padding:12px}
.ev-cover img{
  width:100%;max-height:260px;object-fit:cover;
  border-radius:12px;border:1px solid var(--line-soft);background:#fff;
}
.ev-cover .meta{font-size:12.5px;color:var(--muted-color);margin-top:10px}
</style>
@endpush

@section('content')
<div class="ev-wrap">

  {{-- Global Loading --}}
  <div id="evLoading" class="ev-loading" aria-live="polite" aria-busy="true">
    <div class="box">
      <div class="spin"></div>
      <div class="small">Loading…</div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav ev-tabs nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#ev-tab-active" role="tab" aria-selected="true">
        <i class="fa-solid fa-calendar-check me-2"></i>Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#ev-tab-inactive" role="tab" aria-selected="false">
        <i class="fa-solid fa-calendar-xmark me-2"></i>Inactive
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#ev-tab-trash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Trash
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- ACTIVE --}}
    <div class="tab-pane fade show active" id="ev-tab-active" role="tabpanel">

      {{-- Toolbar --}}
      <div class="ev-toolbar panel mb-3">
        <div class="d-flex align-items-center justify-content-between gap-2 ev-row flex-wrap">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="d-flex align-items-center gap-2">
              <label class="text-muted small mb-0">Per Page</label>
              <select id="evPerPage" class="form-select" style="width:96px;">
                <option>10</option>
                <option selected>20</option>
                <option>50</option>
                <option>100</option>
              </select>
            </div>

            <div class="position-relative ev-search" style="min-width:300px;">
              <input id="evSearch" type="search" class="form-control ps-5" placeholder="Search events…">
              <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
            </div>

            <button class="btn btn-outline-primary" id="evBtnFilter" data-bs-toggle="modal" data-bs-target="#evFilterModal">
              <i class="fa fa-sliders me-1"></i>Filter
            </button>

            <button class="btn btn-light" id="evBtnReset">
              <i class="fa fa-rotate-left me-1"></i>Reset
            </button>
          </div>

          <div class="ev-actions" id="evWriteControls" style="display:none;">
            <button type="button" class="btn btn-primary" id="evBtnAdd">
              <i class="fa fa-plus me-1"></i> Add Event
            </button>
          </div>
        </div>
      </div>

      {{-- Table --}}
      <div class="card ev-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:170px;">When</th>
                  <th style="width:240px;">Location</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:120px;">Featured</th>
                  <th style="width:170px;">Workflow</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="evTbodyActive">
                <tr><td colspan="9" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="evEmptyActive" class="p-4 text-center ev-empty" style="display:none;">
            <i class="fa-solid fa-calendar-check mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No active events found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2 ev-footerbar">
            <div class="text-muted small" id="evInfoActive">—</div>
            <nav><ul id="evPagerActive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- INACTIVE --}}
    <div class="tab-pane fade" id="ev-tab-inactive" role="tabpanel">
      <div class="card ev-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:170px;">When</th>
                  <th style="width:240px;">Location</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:120px;">Featured</th>
                  <th style="width:170px;">Workflow</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="evTbodyInactive">
                <tr><td colspan="9" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="evEmptyInactive" class="p-4 text-center ev-empty" style="display:none;">
            <i class="fa-solid fa-calendar-xmark mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No inactive events found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2 ev-footerbar">
            <div class="text-muted small" id="evInfoInactive">—</div>
            <nav><ul id="evPagerInactive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- TRASH --}}
    <div class="tab-pane fade" id="ev-tab-trash" role="tabpanel">
      <div class="card ev-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:170px;">Deleted</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="evTbodyTrash">
                <tr><td colspan="4" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="evEmptyTrash" class="p-4 text-center ev-empty" style="display:none;">
            <i class="fa-solid fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>Trash is empty.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2 ev-footerbar">
            <div class="text-muted small" id="evInfoTrash">—</div>
            <nav><ul id="evPagerTrash" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="evFilterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-sliders me-2"></i>Filter Events</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Status</label>
            <select id="evFStatus" class="form-select">
              <option value="">All</option>
              <option value="published">Published</option>
              <option value="draft">Draft</option>
              <option value="archived">Archived</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Featured</label>
            <select id="evFFeatured" class="form-select">
              <option value="">Any</option>
              <option value="1">Featured only</option>
              <option value="0">Not featured</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="evFSort" class="form-select">
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="-updated_at">Recently Updated</option>
              <option value="title">Title A-Z</option>
              <option value="-title">Title Z-A</option>
              <option value="-event_date">Event Date (Desc)</option>
              <option value="event_date">Event Date (Asc)</option>
              <option value="-starts_at">Start (Desc)</option>
              <option value="starts_at">Start (Asc)</option>
              <option value="sort_order">Sort Order ↑</option>
              <option value="-sort_order">Sort Order ↓</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">From (optional)</label>
            <input type="date" id="evFDateFrom" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">To (optional)</label>
            <input type="date" id="evFDateTo" class="form-control">
          </div>

          <div class="col-12">
            <div class="form-text">
              Note: date filters will be sent as <code>date_from</code> and <code>date_to</code>.
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="evBtnApplyFilters" class="btn btn-primary">
          <i class="fa fa-check me-1"></i>Apply
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Add/Edit/View Modal --}}
<div class="modal fade" id="evItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form class="modal-content" id="evItemForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="evItemTitle">Add Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="evItemKey">
        <input type="hidden" id="evItemId">

        {{-- Rejection Alert --}}
        <div id="evRejectionAlert" class="alert alert-danger mb-3" style="display:none;">
          <div class="d-flex align-items-center gap-2 mb-1">
            <i class="fa fa-circle-exclamation fs-5"></i>
            <h6 class="mb-0 fw-bold">Rejected by Authority</h6>
          </div>
          <div id="evRejectionReasonText" class="ms-4 small" style="white-space: pre-wrap;">Reason...</div>
          <div class="mt-2 ms-4">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="viewEventHistoryFromAlert()">
              <i class="fa fa-clock-rotate-left me-1"></i>View Full History
            </button>
          </div>
        </div>

        {{-- Pending Draft Alert --}}
        <div id="evDraftAlert" class="alert alert-warning mb-3" style="display:none;">
          <div class="d-flex align-items-center gap-2">
            <i class="fa fa-pen-nib fs-5"></i>
            <h6 class="mb-0 fw-bold">Pending Changes</h6>
          </div>
          <div class="ms-4 small">This event has updates waiting for approval. Editing now will replace those pending changes.</div>
        </div>

        <div class="row g-3">
          {{-- Left --}}
          <div class="col-lg-6">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input id="evTitle" class="form-control" required maxlength="255" placeholder="e.g., Footprint 2024">
              </div>

              <div class="col-md-8">
                <label class="form-label">Slug (optional)</label>
                <input id="evSlug" class="form-control" maxlength="160" placeholder="footprint-2024">
                <div class="form-text">Auto-generated from title until you edit it manually.</div>
              </div>

              <div class="col-md-4">
                <label class="form-label">Sort Order</label>
                <input id="evSortOrder" type="number" class="form-control" min="0" max="1000000" value="0">
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select id="evStatus" class="form-select">
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="archived">Archived</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Featured on Home</label>
                <select id="evFeatured" class="form-select">
                  <option value="0">No</option>
                  <option value="1">Yes</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Event Date</label>
                <input id="evDate" type="date" class="form-control">
              </div>

              <div class="col-md-3">
                <label class="form-label">Start</label>
                <input id="evStart" type="time" class="form-control">
              </div>

              <div class="col-md-3">
                <label class="form-label">End</label>
                <input id="evEnd" type="time" class="form-control">
              </div>

              <div class="col-12">
                <label class="form-label">Location</label>
                <input id="evLocation" class="form-control" maxlength="255" placeholder="e.g., MSIT Campus Auditorium">
              </div>

              <div class="col-12">
                <label class="form-label">Registration Link (optional)</label>
                <input id="evRegLink" class="form-control" placeholder="https://...">
              </div>

              <div class="col-12">
                <label class="form-label">Cover Image (optional)</label>
                <input id="evCover" type="file" class="form-control" accept="image/*">
              </div>

              <div class="col-12">
                <label class="form-label">Attachments (optional)</label>
                <input id="evAttachments" type="file" class="form-control" multiple>
                <div class="small text-muted mt-2" id="evAttInfo" style="display:none;">
                  <i class="fa fa-paperclip me-1"></i><span id="evAttText">—</span>
                </div>
              </div>
            </div>
          </div>

          {{-- Right --}}
          <div class="col-lg-6">
            <div>
              <label class="form-label">Description (HTML allowed) <span class="text-danger">*</span></label>

              <div class="ev-rte-wrap" id="evRteWrap">
                <div class="ev-rte-toolbar" data-for="evBody">
                  <button type="button" class="ev-rte-btn" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
                  <button type="button" class="ev-rte-btn" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
                  <button type="button" class="ev-rte-btn" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>

                  <span class="ev-rte-sep"></span>

                  <button type="button" class="ev-rte-btn" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
                  <button type="button" class="ev-rte-btn" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>

                  <span class="ev-rte-sep"></span>

                  <button type="button" class="ev-rte-btn" data-block="h2" title="Heading 2">H2</button>
                  <button type="button" class="ev-rte-btn" data-block="h3" title="Heading 3">H3</button>

                  <span class="ev-rte-sep"></span>

                  <button type="button" class="ev-rte-btn" data-insert="pre" title="Code Block"><i class="fa fa-code"></i></button>
                  <button type="button" class="ev-rte-btn" data-insert="code" title="Inline Code"><i class="fa fa-terminal"></i></button>

                  <span class="ev-rte-sep"></span>

                  <button type="button" class="ev-rte-btn" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>

                  <div class="ev-rte-tabs">
                    <button type="button" class="tab active" data-mode="text">Text</button>
                    <button type="button" class="tab" data-mode="code">Code</button>
                  </div>
                </div>

                <div class="ev-rte-area">
                  <div id="evBodyEditor" class="ev-rte-editor" contenteditable="true" data-placeholder="Write event details…"></div>
                  <textarea id="evBodyCode" class="ev-rte-code" spellcheck="false" placeholder="HTML code…"></textarea>
                </div>
              </div>

              <input type="hidden" id="evBodyHidden">
            </div>

            <div class="ev-cover mt-3">
              <div class="top">
                <div class="fw-semibold"><i class="fa fa-image me-2"></i>Cover Preview</div>
                <div>
                  <button type="button" class="btn btn-light btn-sm" id="evOpenCover" style="display:none;">
                    <i class="fa fa-up-right-from-square me-1"></i>Open
                  </button>
                </div>
              </div>
              <div class="body">
                <img id="evCoverPreview" src="" alt="Cover preview" style="display:none;">
                <div id="evCoverEmpty" class="text-muted small" style="padding:12px;border:1px dashed var(--line-soft);border-radius:12px;">
                  No cover selected.
                </div>
                <div class="meta" id="evCoverMeta" style="display:none;">—</div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="evSaveBtn">
          <i class="fa fa-floppy-disk me-1"></i> Save
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="evToastOk" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="evToastOkText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="evToastErr" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="evToastErrText">Something went wrong</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection

{{-- Workflow History Modal --}}
<div class="modal fade" id="evHistoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-clock-rotate-left me-2"></i>Workflow History</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="evHistoryLoading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
          <div class="mt-2 text-muted">Loading history…</div>
        </div>
        <div id="evHistoryContent" style="display:none;">
          <ul class="timeline" id="evHistoryTimeline"></ul>
        </div>
        <div id="evHistoryEmpty" class="text-center py-4 text-muted" style="display:none;">
          <i class="fa fa-history mb-2 fs-3 opacity-50"></i>
          <div>No history found for this event.</div>
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
  if (window.__EVENTS_MODULE_INIT__) return;
  window.__EVENTS_MODULE_INIT__ = true;

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

  function coalesce(...vals){
    for (const v of vals){
      if (v === 0) return 0;
      if (v !== null && v !== undefined && String(v).trim() !== '') return v;
    }
    return '';
  }

  function statusBadge(status, hasDraft){
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

  function featuredBadge(v){
    return v ? `<span class="badge badge-soft-primary">Yes</span>` : `<span class="badge badge-soft-muted">No</span>`;
  }

  function toLocalDate(d){
    if (!d) return '';
    const s = String(d).trim();
    if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;
    const iso = s.replace(' ', 'T');
    const dt = new Date(iso);
    if (isNaN(dt.getTime())) return '';
    const y = dt.getFullYear();
    const m = String(dt.getMonth()+1).padStart(2,'0');
    const day = String(dt.getDate()).padStart(2,'0');
    return `${y}-${m}-${day}`;
  }

  function toLocalTime(d){
    if (!d) return '';
    const s = String(d).trim();
    if (/^\d{2}:\d{2}$/.test(s)) return s;
    const iso = s.replace(' ', 'T');
    const dt = new Date(iso);
    if (isNaN(dt.getTime())) return '';
    const h = String(dt.getHours()).padStart(2,'0');
    const m = String(dt.getMinutes()).padStart(2,'0');
    return `${h}:${m}`;
  }

  function formatWhen(row){
    const date = coalesce(row.event_date, row.date, row.event_day, toLocalDate(row.starts_at), toLocalDate(row.start_at), toLocalDate(row.event_at));
    const st   = coalesce(row.start_time, toLocalTime(row.starts_at), toLocalTime(row.start_at));
    const en   = coalesce(row.end_time, toLocalTime(row.ends_at), toLocalTime(row.end_at));
    if (date && (st || en)){
      if (st && en) return `${date} • ${st}-${en}`;
      return `${date} • ${st || en}`;
    }
    return date || coalesce(row.starts_at, row.start_at, row.event_at) || '—';
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const loadingEl = $('evLoading');
    const showLoading = (v) => { if (loadingEl) loadingEl.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('evToastOk');
    const toastErrEl = $('evToastErr');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { const el=$('evToastOkText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('evToastErrText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = () => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    });

    const perPageSel = $('evPerPage');
    const searchInput = $('evSearch');
    const btnReset = $('evBtnReset');

    const writeControls = $('evWriteControls');
    const btnAdd = $('evBtnAdd');

    const tbodyActive = $('evTbodyActive');
    const tbodyInactive = $('evTbodyInactive');
    const tbodyTrash = $('evTbodyTrash');

    const emptyActive = $('evEmptyActive');
    const emptyInactive = $('evEmptyInactive');
    const emptyTrash = $('evEmptyTrash');

    const pagerActive = $('evPagerActive');
    const pagerInactive = $('evPagerInactive');
    const pagerTrash = $('evPagerTrash');

    const infoActive = $('evInfoActive');
    const infoInactive = $('evInfoInactive');
    const infoTrash = $('evInfoTrash');

    const filterModalEl = $('evFilterModal');
    const filterModal = filterModalEl ? new bootstrap.Modal(filterModalEl) : null;
    const fStatus = $('evFStatus');
    const fFeatured = $('evFFeatured');
    const fSort = $('evFSort');
    const fDateFrom = $('evFDateFrom');
    const fDateTo = $('evFDateTo');
    const btnApplyFilters = $('evBtnApplyFilters');

    const itemModalEl = $('evItemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;
    const itemTitle = $('evItemTitle');
    const itemForm = $('evItemForm');
    const saveBtn = $('evSaveBtn');

    const itemKey = $('evItemKey');
    const itemId = $('evItemId');

    const inTitle = $('evTitle');
    const inSlug = $('evSlug');
    const inSortOrder = $('evSortOrder');
    const inStatus = $('evStatus');
    const inFeatured = $('evFeatured');
    const inDate = $('evDate');
    const inStart = $('evStart');
    const inEnd = $('evEnd');
    const inLocation = $('evLocation');
    const inRegLink = $('evRegLink');
    const inCover = $('evCover');
    const inAttachments = $('evAttachments');
    const attInfo = $('evAttInfo');
    const attText = $('evAttText');

    const coverPreview = $('evCoverPreview');
    const coverEmpty = $('evCoverEmpty');
    const coverMeta = $('evCoverMeta');
    const btnOpenCover = $('evOpenCover');

    const rejectionAlert = $('evRejectionAlert');
    const rejectionReasonText = $('evRejectionReasonText');
    const draftAlert = $('evDraftAlert');

    const historyModalEl = $('evHistoryModal');
    const historyModal = historyModalEl ? new bootstrap.Modal(historyModalEl) : null;
    const historyLoading = $('evHistoryLoading');
    const historyContent = $('evHistoryContent');
    const historyEmpty = $('evHistoryEmpty');
    const historyTimeline = $('evHistoryTimeline');

    const API = {
      list:   '/api/events',
      one:    (key) => `/api/events/${encodeURIComponent(key)}`,
      del:    (key) => `/api/events/${encodeURIComponent(key)}`,
      restore:(key) => `/api/events/${encodeURIComponent(key)}/restore`,
      force:  (key) => `/api/events/${encodeURIComponent(key)}/force`
    };

    const ACTOR = { id: null, role: '', department_id: null };
    let canCreate=false, canEdit=false, canDelete=false;

    function computePermissions(){
      const r = (ACTOR?.role || '').toLowerCase();
      if(!ACTOR.department_id){
          canCreate = canEdit = canDelete = true;
      } else {
          canCreate = canEdit = canDelete = false;
          if (window.ACTOR_MENU_TREE && Array.isArray(window.ACTOR_MENU_TREE)) {
             const path = window.location.pathname.replace(/\/+$/, '') || '/';
             let myActions = [];
             for(const group of window.ACTOR_MENU_TREE) {
                if(group.children) {
                   for(const child of group.children) {
                      const childPath = (child.href || '').replace(/\/+$/, '') || '/';
                      if (path === childPath || path.endsWith(childPath)) {
                         myActions = child.actions || [];
                         break;
                      }
                   }
                }
             }
             const actionsStr = myActions.map(a => String(a).trim().toLowerCase());
             if (actionsStr.includes('add') || actionsStr.includes('create')) canCreate = true;
             if (actionsStr.includes('edit') || actionsStr.includes('update')) canEdit = true;
             if (actionsStr.includes('delete') || actionsStr.includes('remove')) canDelete = true;
          }
      }
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
      
      if (!window.ACTOR_MENU_TREE) {
        try {
          const mRes = await fetchWithTimeout('/api/my/sidebar-menus?with_actions=1', { headers: authHeaders() }, 5000);
          if (mRes.ok) {
              const mData = await mRes.json();
              window.ACTOR_MENU_TREE = mData?.tree || [];
          }
        } catch(e) {}
      }
      computePermissions();
    }

    const state = {
      filters: { q:'', status:'', featured:'', sort:'-created_at', date_from:'', date_to:'' },
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      tabs: {
        active:   { page:1, lastPage:1, items:[] },
        inactive: { page:1, lastPage:1, items:[] },
        trash:    { page:1, lastPage:1, items:[] }
      }
    };

    const getTabKey = () => {
      const a = document.querySelector('.ev-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#ev-tab-active';
      if (href === '#ev-tab-inactive') return 'inactive';
      if (href === '#ev-tab-trash') return 'trash';
      return 'active';
    };

    function buildUrl(tabKey){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state.tabs[tabKey].page));

      const q = (state.filters.q || '').trim();
      if (q) params.set('q', q);

      const s = state.filters.sort || '-created_at';
      params.set('sort', s.startsWith('-') ? s.slice(1) : s);
      params.set('direction', s.startsWith('-') ? 'desc' : 'asc');

      if (state.filters.status) params.set('status', state.filters.status);
      if (state.filters.featured !== '') params.set('featured', state.filters.featured);

      if (state.filters.date_from) params.set('date_from', state.filters.date_from);
      if (state.filters.date_to) params.set('date_to', state.filters.date_to);

      if (tabKey === 'active') params.set('active', '1');
      if (tabKey === 'inactive') params.set('active', '0');
      if (tabKey === 'trash') params.set('only_trashed', '1');

      return `${API.list}?${params.toString()}`;
    }

    function setEmpty(tabKey, show){
      const el = tabKey==='active' ? emptyActive : (tabKey==='inactive' ? emptyInactive : emptyTrash);
      if (el) el.style.display = show ? '' : 'none';
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

    function rowKey(r){
      return coalesce(r.uuid, r.id, r.slug, r.event_uuid, r.event_id);
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

      tbody.innerHTML = rows.map(r => {
        const key = rowKey(r);
        const title = coalesce(r.title, r.name, '—');
        const when = formatWhen(r);
        const loc = coalesce(r.location, r.venue, r.place, '—');
        const updated = coalesce(r.updated_at, '—');

        let actions = `
          <div class="dropdown text-end">
            <button type="button" class="btn btn-light btn-sm ev-dd-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
              <i class="fa fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end ev-dd-portal">
              <li><button type="button" class="dropdown-item" onclick="eventsModule.openModal('view', ${JSON.stringify(r).replace(/"/g, '&quot;')})"><i class="fa fa-eye"></i> View</button></li>
              <li><button type="button" class="dropdown-item" onclick="eventsModule.showHistory('events', ${r.id})"><i class="fa fa-clock-rotate-left"></i> Workflow History</button></li>`;

        if (tabKey !== 'trash' && canEdit){
          actions += `<li><button type="button" class="dropdown-item" onclick="eventsModule.openModal('edit', ${JSON.stringify(r).replace(/"/g, '&quot;')})"><i class="fa fa-pen-to-square"></i> Edit</button></li>`;
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
            <tr data-key="${esc(key)}">
              <td class="fw-semibold">${esc(title)}</td>
              <td class="col-slug"><code>${esc(r.slug || '—')}</code></td>
              <td>${esc(coalesce(r.deleted_at, '—'))}</td>
              <td class="text-end">${actions}</td>
            </tr>`;
        }

        return `
          <tr data-key="${esc(key)}">
            <td class="fw-semibold">${esc(title)}</td>
            <td><code>${esc(r.slug || '—')}</code></td>
            <td>${esc(when)}</td>
            <td>${esc(loc)}</td>
            <td>${statusBadge(r.status, !!r.draft_data)}</td>
            <td>${featuredBadge(r.is_featured_home)}</td>
            <td>${workflowBadge(r.workflow_status)}</td>
            <td>${esc(updated)}</td>
            <td class="text-end">${actions}</td>
          </tr>`;
      }).join('');

      renderPager(tabKey);
    }

    async function loadTab(tabKey){
      const tbody = (tabKey==='active' ? tbodyActive : (tabKey==='inactive' ? tbodyInactive : tbodyTrash));
      if (tbody){
        const cols = (tabKey==='trash' ? 4 : 9);
        tbody.innerHTML = `<tr><td colspan="${cols}" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;
      }

      try{
        const res = await fetchWithTimeout(buildUrl(tabKey), { headers: authHeaders() }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(js?.message || 'Failed to load');

        const items = Array.isArray(js.data) ? js.data : (Array.isArray(js.items) ? js.items : []);
        const p = js.pagination || js.meta || {};
        state.tabs[tabKey].items = items;

        state.tabs[tabKey].lastPage = parseInt(p.last_page || p.total_pages || 1, 10) || 1;

        const total = p.total ?? p.total_items ?? null;
        const info = total !== null ? `${total} result(s)` : '—';
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

    function reloadAll(){ loadTab('active'); loadTab('inactive'); loadTab('trash'); }

    // Pager clicks
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

    // Filters
    searchInput?.addEventListener('input', debounce(() => {
      state.filters.q = (searchInput.value || '').trim();
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadAll();
    }, 320));

    perPageSel?.addEventListener('change', () => {
      state.perPage = parseInt(perPageSel.value, 10) || 20;
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadAll();
    });

    filterModalEl?.addEventListener('show.bs.modal', () => {
      if (fStatus) fStatus.value = state.filters.status || '';
      if (fFeatured) fFeatured.value = (state.filters.featured ?? '');
      if (fSort) fSort.value = state.filters.sort || '-created_at';
      if (fDateFrom) fDateFrom.value = state.filters.date_from || '';
      if (fDateTo) fDateTo.value = state.filters.date_to || '';
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.status = fStatus?.value || '';
      state.filters.featured = (fFeatured?.value ?? '');
      state.filters.sort = fSort?.value || '-created_at';
      state.filters.date_from = fDateFrom?.value || '';
      state.filters.date_to = fDateTo?.value || '';

      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      filterModal && filterModal.hide();
      reloadAll();
    });

    btnReset?.addEventListener('click', () => {
      state.filters = { q:'', status:'', featured:'', sort:'-created_at', date_from:'', date_to:'' };
      state.perPage = 20;

      if (searchInput) searchInput.value = '';
      if (perPageSel) perPageSel.value = '20';

      if (fStatus) fStatus.value = '';
      if (fFeatured) fFeatured.value = '';
      if (fSort) fSort.value = '-created_at';
      if (fDateFrom) fDateFrom.value = '';
      if (fDateTo) fDateTo.value = '';

      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadAll();
    });

    document.querySelector('a[href="#ev-tab-active"]')?.addEventListener('shown.bs.tab', () => loadTab('active'));
    document.querySelector('a[href="#ev-tab-inactive"]')?.addEventListener('shown.bs.tab', () => loadTab('inactive'));
    document.querySelector('a[href="#ev-tab-trash"]')?.addEventListener('shown.bs.tab', () => loadTab('trash'));

    // ---------- RTE + rest of your logic stays as-is ----------
    const rte = {
      wrap: $('evRteWrap'),
      toolbar: document.querySelector('#evRteWrap .ev-rte-toolbar'),
      editor: $('evBodyEditor'),
      code: $('evBodyCode'),
      hidden: $('evBodyHidden'),
      mode: 'text',
      enabled: true
    };

    function ensurePreHasCode(html){
      return (html || '').replace(/<pre>([\s\S]*?)<\/pre>/gi, (m, inner) => {
        if (/<code[\s>]/i.test(inner)) return `<pre>${inner}</pre>`;
        return `<pre><code>${inner}</code></pre>`;
      });
    }

    function rteFocus(){
      try { rte.editor?.focus({ preventScroll:true }); }
      catch(_) { try { rte.editor?.focus(); } catch(__){} }
    }

    function syncRteToCode(){
      if (!rte.editor || !rte.code) return;
      if (rte.mode === 'text') rte.code.value = ensurePreHasCode(rte.editor.innerHTML || '');
    }

    function updateRteActive(){
      if (rte.mode !== 'text' || !rte.enabled) return;
      if (!rte.wrap || !rte.editor) return;

      const sel = window.getSelection?.();
      const anchor = sel?.anchorNode || null;
      const inEditor = !!(anchor && rte.editor.contains(anchor));
      if (!inEditor && document.activeElement !== rte.editor) return;

      const setCmd = (cmd, on) => {
        const b = rte.wrap.querySelector(`.ev-rte-btn[data-cmd="${cmd}"]`);
        if (b) b.classList.toggle('active', !!on);
      };

      try{
        setCmd('bold', document.queryCommandState('bold'));
        setCmd('italic', document.queryCommandState('italic'));
        setCmd('underline', document.queryCommandState('underline'));
        setCmd('insertUnorderedList', document.queryCommandState('insertUnorderedList'));
        setCmd('insertOrderedList', document.queryCommandState('insertOrderedList'));

        let fb = (document.queryCommandValue('formatBlock') || '').toString().toLowerCase().trim();
        fb = fb.replace(/[<>]/g,'');
        rte.wrap.querySelectorAll('.ev-rte-btn[data-block]').forEach(btn => {
          const blk = (btn.getAttribute('data-block') || '').toLowerCase().trim();
          btn.classList.toggle('active', !!blk && fb === blk);
        });
      }catch(_){}
    }

    function setRteMode(mode){
      rte.mode = (mode === 'code') ? 'code' : 'text';
      rte.wrap?.classList.toggle('mode-code', rte.mode === 'code');

      rte.wrap?.querySelectorAll('.ev-rte-tabs .tab').forEach(t => {
        t.classList.toggle('active', t.dataset.mode === rte.mode);
      });

      const disable = (rte.mode === 'code') || !rte.enabled;
      rte.wrap?.querySelectorAll('.ev-rte-toolbar .ev-rte-btn').forEach(b => {
        b.disabled = disable;
        b.style.opacity = disable ? '0.55' : '';
        b.style.pointerEvents = disable ? 'none' : '';
      });

      if (rte.mode === 'code'){
        rte.code.value = ensurePreHasCode(rte.editor.innerHTML || '');
        setTimeout(()=>{ try{ rte.code?.focus(); }catch(_){ } }, 0);
      } else {
        rte.editor.innerHTML = ensurePreHasCode(rte.code.value || '');
        setTimeout(()=>{ rteFocus(); updateRteActive(); }, 0);
      }
    }

    rte.toolbar?.addEventListener('pointerdown', (e) => { e.preventDefault(); });

    rte.editor?.addEventListener('input', () => {
      syncRteToCode();
      updateRteActive();
    });

    ['mouseup','keyup','click'].forEach(ev => rte.editor?.addEventListener(ev, updateRteActive));
    document.addEventListener('selectionchange', () => {
      if (document.activeElement === rte.editor) updateRteActive();
    });

    document.addEventListener('click', (e) => {
      const tab = e.target.closest('#evRteWrap .ev-rte-tabs .tab');
      if (tab){ setRteMode(tab.dataset.mode); return; }

      const btn = e.target.closest('#evRteWrap .ev-rte-toolbar .ev-rte-btn');
      if (!btn || rte.mode !== 'text' || !rte.enabled) return;

      rteFocus();

      const block = btn.getAttribute('data-block');
      const insert = btn.getAttribute('data-insert');
      const cmd = btn.getAttribute('data-cmd');

      if (block){
        try{ document.execCommand('formatBlock', false, `<${block}>`); }catch(_){}
        syncRteToCode();
        updateRteActive();
        return;
      }

      if (insert === 'code'){
        const sel = window.getSelection();
        const hasSel = sel && sel.rangeCount && !sel.isCollapsed && sel.toString().trim();
        if (hasSel){
          document.execCommand('insertHTML', false, `<code>${esc(sel.toString())}</code>`);
        } else {
          document.execCommand('insertHTML', false, `<code></code>&#8203;`);
        }
        syncRteToCode();
        updateRteActive();
        return;
      }

      if (insert === 'pre'){
        const sel = window.getSelection();
        const hasSel = sel && sel.rangeCount && !sel.isCollapsed && sel.toString().trim();
        if (hasSel){
          document.execCommand('insertHTML', false, `<pre><code>${esc(sel.toString())}</code></pre>`);
        } else {
          document.execCommand('insertHTML', false, `<pre><code></code></pre>&#8203;`);
        }
        syncRteToCode();
        updateRteActive();
        return;
      }

      if (cmd){
        try{ document.execCommand(cmd, false, null); }catch(_){}
        syncRteToCode();
        updateRteActive();
      }
    });

    function setRteEnabled(on){
      rte.enabled = !!on;
      if (rte.editor) rte.editor.setAttribute('contenteditable', on ? 'true' : 'false');
      if (rte.code) rte.code.disabled = !on;

      if (!on && rte.wrap){
        rte.wrap.querySelectorAll('.ev-rte-btn.active').forEach(b => b.classList.remove('active'));
      }
      if (on) setTimeout(updateRteActive, 0);
    }

    let coverObjectUrl = null;

    function clearCover(revoke=true){
      if (revoke && coverObjectUrl){
        try{ URL.revokeObjectURL(coverObjectUrl); }catch(_){}
      }
      coverObjectUrl = null;

      if (coverPreview){
        coverPreview.style.display = 'none';
        coverPreview.removeAttribute('src');
      }
      if (coverEmpty) coverEmpty.style.display = '';
      if (coverMeta){ coverMeta.style.display = 'none'; coverMeta.textContent = '—'; }
      if (btnOpenCover){ btnOpenCover.style.display = 'none'; btnOpenCover.onclick = null; }
    }

    function setCover(url, metaText=''){
      const u = normalizeUrl(url);
      if (!u){ clearCover(true); return; }
      if (coverPreview){
        coverPreview.style.display = '';
        coverPreview.src = u;
      }
      if (coverEmpty) coverEmpty.style.display = 'none';
      if (coverMeta){
        coverMeta.style.display = metaText ? '' : 'none';
        coverMeta.textContent = metaText || '';
      }
      if (btnOpenCover){
        btnOpenCover.style.display = '';
        btnOpenCover.onclick = () => window.open(u, '_blank', 'noopener');
      }
    }

    inCover?.addEventListener('change', () => {
      const f = inCover.files?.[0];
      if (!f) { clearCover(true); return; }
      if (coverObjectUrl){
        try{ URL.revokeObjectURL(coverObjectUrl); }catch(_){}
      }
      coverObjectUrl = URL.createObjectURL(f);
      setCover(coverObjectUrl, `${f.name || 'cover'} • ${bytes(f.size)}`);
    });

    inAttachments?.addEventListener('change', () => {
      const files = Array.from(inAttachments.files || []);
      if (!files.length){
        if (attInfo) attInfo.style.display = 'none';
        if (attText) attText.textContent = '—';
        return;
      }
      if (attInfo) attInfo.style.display = '';
      if (attText) attText.textContent = `${files.length} selected`;
    });

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
      itemKey.value = '';
      itemId.value = '';
      slugDirty = false;
      settingSlug = false;

      if (rte.editor) rte.editor.innerHTML = '';
      if (rte.code) rte.code.value = '';
      if (rte.hidden) rte.hidden.value = '';
      setRteMode('text');
      setRteEnabled(true);

      if (attInfo) attInfo.style.display = 'none';
      if (attText) attText.textContent = '—';
      clearCover(true);

      itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
        if (el.id === 'evItemKey' || el.id === 'evItemId') return;
        if (el.type === 'file') el.disabled = false;
        else if (el.tagName === 'SELECT') el.disabled = false;
        else el.readOnly = false;
      });

      if (saveBtn) saveBtn.style.display = '';
      if (itemForm){
        itemForm.dataset.mode = 'edit';
        itemForm.dataset.intent = 'create';
      }
    }

    function fillFormFromItem(r, viewOnly=false){
      const key = rowKey(r);
      itemKey.value = key;
      itemId.value = coalesce(r.id, '') || '';

      inTitle.value = coalesce(r.title, r.name, '');
      inSlug.value = coalesce(r.slug, '');

      inSortOrder.value = String(parseInt(coalesce(r.sort_order, 0), 10) || 0);
      inStatus.value = (coalesce(r.status, (r.active ? 'published' : 'draft'), 'draft') || 'draft').toString();
      inFeatured.value = String((r.is_featured_home ?? r.is_featured ?? r.featured ?? 0) ? 1 : 0);

      const date = coalesce(r.event_date, r.date, toLocalDate(r.starts_at), toLocalDate(r.start_at), toLocalDate(r.event_at));
      inDate.value = date ? String(date) : '';

      const st = coalesce(r.start_time, toLocalTime(r.starts_at), toLocalTime(r.start_at));
      const en = coalesce(r.end_time, toLocalTime(r.ends_at), toLocalTime(r.end_at));
      inStart.value = st ? String(st).slice(0,5) : '';
      inEnd.value = en ? String(en).slice(0,5) : '';

      inLocation.value = coalesce(r.location, r.venue, r.place, '');
      inRegLink.value = coalesce(r.registration_link, r.register_url, r.registration_url, r.link, '');

      const bodyHtml = coalesce(r.body, r.description, r.details, r.body_html, '');
      if (rte.editor) rte.editor.innerHTML = ensurePreHasCode(bodyHtml || '');
      syncRteToCode();
      setRteMode('text');
      setTimeout(updateRteActive, 0);

      const coverUrl = coalesce(r.cover_image_url, r.cover_url, r.cover_image, r.banner_url, r.banner, '');
      if (coverUrl){
        clearCover(true);
        setCover(coverUrl, '');
      } else {
        clearCover(true);
      }

      slugDirty = true;

      if (viewOnly){
        itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
          if (el.id === 'evItemKey' || el.id === 'evItemId') return;
          if (el.type === 'file') el.disabled = true;
          else if (el.tagName === 'SELECT') el.disabled = true;
          else el.readOnly = true;
        });
        setRteEnabled(false);
        if (saveBtn) saveBtn.style.display = 'none';
        itemForm.dataset.mode = 'view';
        itemForm.dataset.intent = 'view';
      } else {
        setRteEnabled(true);
        if (saveBtn) saveBtn.style.display = '';
        itemForm.dataset.mode = 'edit';
        itemForm.dataset.intent = 'edit';
      }
    }

    function findRowByKey(key){
      const all = [
        ...(state.tabs.active.items || []),
        ...(state.tabs.inactive.items || []),
        ...(state.tabs.trash.items || [])
      ];
      return all.find(x => String(rowKey(x)) === String(key)) || null;
    }

    inTitle?.addEventListener('input', debounce(() => {
      if (itemForm?.dataset.mode === 'view') return;
      if (itemKey.value) return;
      if (slugDirty) return;
      const next = slugify(inTitle.value);
      settingSlug = true;
      inSlug.value = next;
      settingSlug = false;
    }, 120));

    inSlug?.addEventListener('input', () => {
      if (itemKey.value) return;
      if (settingSlug) return;
      slugDirty = !!(inSlug.value || '').trim();
    });

    function setHistoryState(stateName){
      if (historyLoading) historyLoading.style.display = stateName === 'loading' ? '' : 'none';
      if (historyContent) historyContent.style.display = stateName === 'content' ? '' : 'none';
      if (historyEmpty) historyEmpty.style.display = stateName === 'empty' ? '' : 'none';
    }

    function normalizeHistoryItems(payload){
      if (Array.isArray(payload)) return payload;
      if (Array.isArray(payload?.data)) return payload.data;
      if (Array.isArray(payload?.items)) return payload.items;
      if (Array.isArray(payload?.history)) return payload.history;
      if (Array.isArray(payload?.logs)) return payload.logs;
      if (Array.isArray(payload?.workflow_history)) return payload.workflow_history;
      if (Array.isArray(payload?.data?.history)) return payload.data.history;
      if (Array.isArray(payload?.data?.logs)) return payload.data.logs;
      if (Array.isArray(payload?.data?.workflow_history)) return payload.data.workflow_history;
      return [];
    }

    function renderHistory(items){
      if (!historyTimeline) return;
      historyTimeline.innerHTML = (items || []).map((item) => {
        const when = esc(coalesce(item.created_at, item.updated_at, item.acted_at, item.action_at, item.date, item.timestamp, '—'));
        const action = esc(coalesce(item.action, item.status, item.workflow_status, item.type, 'Updated'));
        const author = esc(coalesce(item.actor_name, item.user_name, item.author, item.created_by_name, item.updated_by_name, item.actor, 'System'));
        const comment = coalesce(item.comment, item.reason, item.notes, item.rejection_reason, item.remarks, '');
        return `
          <li class="timeline-item">
            <div class="timeline-marker"></div>
            <div class="timeline-content">
              <div class="timeline-date">${when}</div>
              <div class="timeline-title">${action}</div>
              <div class="timeline-author">${author}</div>
              ${comment ? `<div class="timeline-comment">${esc(comment)}</div>` : ''}
            </div>
          </li>`;
      }).join('');
    }

    function updateWorkflowAlerts(row){
      const workflow = String(coalesce(row?.workflow_status, '')).toLowerCase();
      const rejectionReason = coalesce(
        row?.rejection_reason,
        row?.reject_reason,
        row?.checker_comment,
        row?.approver_comment,
        row?.comment,
        row?.remarks,
        ''
      );
      const showRejected = workflow === 'rejected' || !!String(rejectionReason || '').trim();
      if (rejectionAlert) rejectionAlert.style.display = showRejected ? '' : 'none';
      if (rejectionReasonText) rejectionReasonText.textContent = rejectionReason || 'No rejection reason available.';

      const hasPendingDraft = !!(row?.draft_data || row?.pending_draft || row?.has_pending_draft || row?.draft);
      if (draftAlert) draftAlert.style.display = hasPendingDraft ? '' : 'none';
    }

    let currentHistoryEventId = null;

    async function showHistory(moduleName='events', id=null){
      const historyId = id || currentHistoryEventId || itemId?.value || null;
      if (!historyId){
        err('History is not available for this event');
        return;
      }

      currentHistoryEventId = historyId;
      setHistoryState('loading');
      historyModal && historyModal.show();

      const urls = [
        `/api/${moduleName}/${encodeURIComponent(historyId)}/workflow-history`,
        `/api/${moduleName}/${encodeURIComponent(historyId)}/history`
      ];

      let items = [];
      let lastError = null;

      for (const url of urls){
        try{
          const res = await fetchWithTimeout(url, { headers: authHeaders() }, 15000);
          if (!res.ok) {
            lastError = new Error(`Failed with status ${res.status}`);
            continue;
          }
          const js = await res.json().catch(()=> ({}));
          items = normalizeHistoryItems(js);
          if (items.length) break;
        }catch(ex){
          lastError = ex;
        }
      }

      if (items.length){
        renderHistory(items);
        setHistoryState('content');
        return;
      }

      if (lastError && lastError.name !== 'AbortError') {
        console.warn('Workflow history could not be loaded:', lastError);
      }
      renderHistory([]);
      setHistoryState('empty');
    }

    async function openModal(mode='add', row=null){
      const modalMode = String(mode || 'add').toLowerCase();

      if (modalMode === 'add'){
        if (!canCreate) return;
        resetForm();
        currentHistoryEventId = null;
        updateWorkflowAlerts({});
        if (itemTitle) itemTitle.textContent = 'Add Event';
        if (itemForm){
          itemForm.dataset.mode = 'edit';
          itemForm.dataset.intent = 'create';
        }
        itemModal && itemModal.show();
        return;
      }

      const seedRow = (row && typeof row === 'object') ? row : (findRowByKey(row) || { id: row, uuid: row, slug: row });
      const key = rowKey(seedRow);
      if (!key){
        err('Unable to open this event');
        return;
      }

      showLoading(true);
      try{
        const res = await fetchWithTimeout(API.one(key), { headers: authHeaders() }, 15000);
        const js = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(js?.message || 'Failed to load event');

        const fullRow = js?.data || js?.item || js?.event || seedRow;
        resetForm();
        fillFormFromItem(fullRow, modalMode === 'view');
        updateWorkflowAlerts(fullRow);
        currentHistoryEventId = coalesce(fullRow?.id, seedRow?.id, null);

        if (itemTitle) itemTitle.textContent = modalMode === 'view' ? 'View Event' : 'Edit Event';
        if (itemForm){
          itemForm.dataset.mode = modalMode === 'view' ? 'view' : 'edit';
          itemForm.dataset.intent = modalMode === 'view' ? 'view' : 'edit';
        }
        itemModal && itemModal.show();
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed to load event'));
      }finally{
        showLoading(false);
      }
    }

    window.eventsModule = {
      openModal,
      showHistory
    };

    window.viewEventHistoryFromAlert = function(){
      showHistory('events', currentHistoryEventId || itemId?.value || null);
    };

    btnAdd?.addEventListener('click', () => {
      if (!canCreate) return;
      window.eventsModule?.openModal('add');
    });

    itemModalEl?.addEventListener('hidden.bs.modal', () => {
      if (coverObjectUrl){
        try{ URL.revokeObjectURL(coverObjectUrl); }catch(_){}
        coverObjectUrl = null;
      }
    });

    // Row actions
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;

      const tr = btn.closest('tr');
      const key = tr?.dataset?.key;
      const act = btn.dataset.action;
      if (!key) return;

      const row = findRowByKey(key);

      if (act === 'delete'){
        if (!canDelete) return;

        const conf = await Swal.fire({
          title: 'Delete this event?',
          text: 'This will move the item to Trash.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(API.del(key), { method: 'DELETE', headers: authHeaders() }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Delete failed');

          ok('Moved to trash');
          await Promise.all([loadTab('active'), loadTab('inactive'), loadTab('trash')]);
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'restore'){
        const conf = await Swal.fire({
          title: 'Restore this event?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Restore'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(API.restore(key), { method: 'POST', headers: authHeaders() }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Restore failed');

          ok('Restored');
          await Promise.all([loadTab('trash'), loadTab('active'), loadTab('inactive')]);
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
          const res = await fetchWithTimeout(API.force(key), { method: 'DELETE', headers: authHeaders() }, 15000);
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

    // Submit create/edit (kept)
    itemForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (saving) return;
      saving = true;

      try{
        if (itemForm.dataset.mode === 'view') return;

        const intent = itemForm.dataset.intent || 'create';
        const isEdit = intent === 'edit' && !!itemKey.value;

        if (isEdit && !canEdit) return;
        if (!isEdit && !canCreate) return;

        const title = (inTitle.value || '').trim();
        const slug  = (inSlug.value || '').trim();

        const status = (inStatus.value || 'draft').trim();
        const featured = (inFeatured.value || '0').trim();
        const sortOrder = String(parseInt(inSortOrder.value || '0', 10) || 0);

        const rawBody = (rte.mode === 'code') ? (rte.code.value || '') : (rte.editor.innerHTML || '');
        const cleanBody = ensurePreHasCode(rawBody).trim();
        if (rte.hidden) rte.hidden.value = cleanBody;

        if (!title){ err('Title is required'); inTitle.focus(); return; }
        if (!cleanBody){ err('Description is required'); rteFocus(); return; }

        const fd = new FormData();
        fd.append('title', title);
        if (slug) fd.append('slug', slug);

        fd.append('status', status);
        fd.append('is_featured_home', featured === '1' ? '1' : '0');
        fd.append('sort_order', sortOrder);

        const d = (inDate.value || '').trim();
        const st = (inStart.value || '').trim();
        const en = (inEnd.value || '').trim();
        if (d) fd.append('event_date', d);
        if (st) fd.append('start_time', st);
        if (en) fd.append('end_time', en);
        if (d && st) fd.append('starts_at', `${d}T${st}`);
        if (d && en) fd.append('ends_at', `${d}T${en}`);

        const loc = (inLocation.value || '').trim();
        if (loc) fd.append('location', loc);

        const link = (inRegLink.value || '').trim();
        if (link) {
          fd.append('registration_link', link);
          fd.append('registration_url', link);
        }

        fd.append('body', cleanBody);
        fd.append('description', cleanBody);

        const cover = inCover.files?.[0] || null;
        if (cover) fd.append('cover_image', cover);

        Array.from(inAttachments.files || []).forEach(f => fd.append('attachments[]', f));

        const url = isEdit ? API.one(itemKey.value) : API.list;
        if (isEdit) fd.append('_method', 'PUT'); // ✅ your backend supports PUT

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

        state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
        await Promise.all([loadTab('active'), loadTab('inactive'), loadTab('trash')]);
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        saving = false;
        setBtnLoading(saveBtn, false);
        showLoading(false);
      }
    });

    // Init
    (async () => {
      showLoading(true);
      try{
        await fetchMe();
        await Promise.all([loadTab('active'), loadTab('inactive'), loadTab('trash')]);
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
