{{-- resources/views/modules/departments/managePlacementNotices.blade.php --}}
@section('title','Placement Notices')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Placement Notices (Admin)
 * Reference-inspired layout (not copied)
 * ========================= */

.pn-wrap{padding:14px 4px}

/* Tabs */
.pn-tabs.nav-tabs{border-color:var(--line-strong)}
.pn-tabs .nav-link{color:var(--ink)}
.pn-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface);
}
.tab-content,.tab-pane{overflow:visible}

/* Toolbar */
.pn-toolbar.panel{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:16px;
  box-shadow:var(--shadow-2);
  padding:12px 12px;
}

/* Card/Table shell */
.pn-card{
  position:relative;
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.pn-card .card-body{overflow:visible}
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
td .fw-semibold{color:var(--ink)}

/* Slug column */
th.pn-col-slug, td.pn-col-slug{width:190px;max-width:190px}
td.pn-col-slug code{
  display:inline-block;
  max-width:180px;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
  vertical-align:bottom;
}

/* Department column (ellipsis) */
th.pn-col-dept, td.pn-col-dept{width:170px;max-width:170px}
td.pn-col-dept .pn-dept-text{
  display:inline-block;
  max-width:162px;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
  vertical-align:bottom;
}

/* Dropdown safety */
.pn-card .dropdown{position:relative}
.pn-card .dd-toggle{border-radius:10px}
/* ✅ keep specific toggle class too (manual bootstrap toggle like reference page) */
.pn-card .pn-dd-toggle{border-radius:10px}
.pn-card .dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:230px;
  z-index:99999; /* ✅ match reference: ensure it isn't behind anything */
}
.pn-card .dropdown-menu.show{display:block !important}
.pn-card .dropdown-item{display:flex;align-items:center;gap:.6rem}
.pn-card .dropdown-item i{width:16px;text-align:center}
.pn-card .dropdown-item.text-danger{color:var(--danger-color) !important}

/* Badges */
.pn-badge-primary{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  color:var(--primary-color)
}
.pn-badge-success{
  background:color-mix(in oklab, var(--success-color) 12%, transparent);
  color:var(--success-color)
}
.pn-badge-muted{
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
  color:var(--muted-color)
}
.pn-badge-warning{
  background:color-mix(in oklab, var(--warning-color, #f59e0b) 14%, transparent);
  color:var(--warning-color, #f59e0b)
}

/* Responsive scroll (keep dropdown visible vertically) */
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
.table-responsive th,.table-responsive td{white-space:nowrap}
@media (max-width:576px){
  .table-responsive > .table{min-width:1160px}
}

/* Footer stacking */
.pn-footer{
  position:relative;
  z-index:1;
}

/* Loading overlay */
.pn-loading{
  position:fixed; inset:0;
  background:rgba(0,0,0,.45);
  display:none;
  align-items:center;
  justify-content:center;
  z-index:9999;
  backdrop-filter:blur(2px);
}
.pn-loading .box{
  background:var(--surface);
  padding:18px 20px;
  border-radius:14px;
  box-shadow:0 10px 26px rgba(0,0,0,.3);
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:10px;
}
.pn-spinner{
  width:40px;height:40px;
  border-radius:50%;
  border:4px solid rgba(148,163,184,.3);
  border-top:4px solid var(--primary-color);
  animation:pnspin 1s linear infinite;
}
@keyframes pnspin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

/* Toolbar responsive */
@media (max-width:768px){
  .pn-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .pn-toolbar .pn-search{min-width:100% !important}
  .pn-actions{display:flex;gap:8px;flex-wrap:wrap}
  .pn-actions .btn{flex:1;min-width:140px}
}

/* Mini editor (contenteditable) */
.pn-rte-help{font-size:12px;color:var(--muted-color);margin-top:6px}
.pn-rte{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:var(--surface);
}
.pn-rtebar{
  display:flex;align-items:center;gap:6px;flex-wrap:wrap;
  padding:8px;
  border-bottom:1px solid var(--line-strong);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.pn-rbtn{
  border:1px solid var(--line-soft);
  background:transparent;
  color:var(--ink);
  padding:7px 9px;
  border-radius:10px;
  line-height:1;
  cursor:pointer;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:6px;
  user-select:none;
}
.pn-rbtn:hover{background:var(--page-hover)}
.pn-rbtn.active{
  background:color-mix(in oklab, var(--primary-color) 14%, transparent);
  border-color:color-mix(in oklab, var(--primary-color) 35%, var(--line-soft));
}
.pn-rsep{width:1px;height:24px;background:var(--line-soft);margin:0 4px}
.pn-rtabs{
  margin-left:auto;
  display:flex;
  border:1px solid var(--line-soft);
  overflow:hidden;
}
.pn-rtabs .tab{
  border:0;
  border-right:1px solid var(--line-soft);
  background:transparent;
  color:var(--ink);
  padding:7px 12px;
  font-size:12px;
  cursor:pointer;
  user-select:none;
}
.pn-rtabs .tab:last-child{border-right:0}
.pn-rtabs .tab.active{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  font-weight:700;
}
.pn-rtearea{position:relative}
.pn-editor{
  min-height:220px;
  padding:12px 12px;
  outline:none;
}
.pn-editor:empty:before{content:attr(data-placeholder);color:var(--muted-color)}
.pn-editor b,.pn-editor strong{font-weight:800}
.pn-editor i,.pn-editor em{font-style:italic}
.pn-editor u{text-decoration:underline}
.pn-editor h1{font-size:20px;margin:8px 0}
.pn-editor h2{font-size:18px;margin:8px 0}
.pn-editor h3{font-size:16px;margin:8px 0}
.pn-editor ul,.pn-editor ol{padding-left:22px}
.pn-editor p{margin:0 0 10px}
.pn-editor a{color:var(--primary-color);text-decoration:underline}
.pn-editor code{
  padding:2px 6px;
  background:color-mix(in oklab, var(--muted-color) 14%, transparent);
  border:1px solid var(--line-soft);
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  font-size:12.5px;
}
.pn-editor pre{
  padding:10px 12px;
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
  border:1px solid var(--line-soft);
  overflow:auto;
  margin:8px 0;
}
.pn-editor pre code{border:0;background:transparent;padding:0;display:block;white-space:pre}

.pn-code{
  display:none;
  width:100%;
  min-height:220px;
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
.pn-rte.mode-code .pn-editor{display:none}
.pn-rte.mode-code .pn-code{display:block}

/* Banner preview box */
.pn-preview{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:color-mix(in oklab, var(--surface) 88%, var(--bg-body));
}
.pn-preview .top{
  display:flex;align-items:center;justify-content:space-between;gap:10px;
  padding:10px 12px;
  border-bottom:1px solid var(--line-soft);
}
.pn-preview .body{padding:12px}
.pn-preview img{
  width:100%;
  max-height:260px;
  object-fit:cover;
  border-radius:12px;
  border:1px solid var(--line-soft);
  background:#fff;
}

/* =========================
 * Department picker (NEW)
 * ========================= */
.pn-dept-box{
  border:1px solid var(--line-strong);
  border-radius:14px;
  padding:10px 12px;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.pn-dept-head{
  display:flex;align-items:center;justify-content:space-between;gap:10px;
}
.pn-dept-chips{
  margin-top:10px;
  display:flex;
  flex-wrap:wrap;
  gap:8px;
}
.pn-chip{
  display:inline-flex;
  align-items:center;
  gap:8px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--primary-color) 10%, transparent);
  color:var(--ink);
  padding:6px 10px;
  border-radius:999px;
  font-size:12.5px;
}
.pn-chip .x{
  width:22px;height:22px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  border-radius:999px;
  border:1px solid var(--line-soft);
  background:transparent;
  color:var(--muted-color);
  cursor:pointer;
}
.pn-chip .x:hover{
  background:color-mix(in oklab, var(--danger-color) 12%, transparent);
  color:var(--danger-color);
  border-color:color-mix(in oklab, var(--danger-color) 28%, var(--line-soft));
}
.pn-dept-empty{
  margin-top:10px;
  padding:10px 12px;
  border:1px dashed var(--line-soft);
  border-radius:12px;
  color:var(--muted-color);
  font-size:13px;
}
.pn-dept-list{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
}
.pn-dept-list .top{
  padding:10px 12px;
  border-bottom:1px solid var(--line-soft);
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
}
.pn-dept-list .body{
  max-height:360px;
  overflow:auto;
  padding:8px 10px;
}
.pn-dept-row{
  display:flex;
  align-items:center;
  gap:10px;
  padding:9px 10px;
  border-radius:12px;
}
.pn-dept-row:hover{background:var(--page-hover)}
.pn-dept-row .title{
  font-weight:600;
  color:var(--ink);
}

/* =========================
 * ✅ FIX: Stacked modals (Dept picker above Item modal)
 * ========================= */
#pnItemModal{z-index:1055}
#pnDeptPickerModal{z-index:1070}   /* higher than pnItemModal */
</style>
@endpush

@section('content')
<div class="pn-wrap">

  {{-- Global loading --}}
  <div id="pnLoading" class="pn-loading">
    <div class="box">
      <div class="pn-spinner"></div>
      <div class="small">Loading…</div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav pn-tabs nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#pn-tab-active" role="tab" aria-selected="true">
        <i class="fa-solid fa-briefcase me-2"></i>Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#pn-tab-inactive" role="tab" aria-selected="false">
        <i class="fa-solid fa-circle-pause me-2"></i>Inactive
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#pn-tab-trash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Trash
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- ACTIVE --}}
    <div class="tab-pane fade show active" id="pn-tab-active" role="tabpanel">

      {{-- Toolbar --}}
      <div class="row align-items-center g-2 mb-3 pn-toolbar panel">
        <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">

          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per Page</label>
            <select id="pnPerPage" class="form-select" style="width:96px;">
              <option>10</option>
              <option selected>20</option>
              <option>50</option>
              <option>100</option>
            </select>
          </div>

          <div class="position-relative pn-search" style="min-width:280px;">
            <input id="pnSearch" type="search" class="form-control ps-5" placeholder="Search by title, slug, role…">
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>

          <button id="pnBtnFilter" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#pnFilterModal">
            <i class="fa fa-sliders me-1"></i>Filter
          </button>

          <button id="pnBtnReset" class="btn btn-light">
            <i class="fa fa-rotate-left me-1"></i>Reset
          </button>
        </div>

        <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
          <div id="pnWriteControls" style="display:none;" class="pn-actions">
            <button type="button" class="btn btn-primary" id="pnBtnAdd">
              <i class="fa fa-plus me-1"></i> Add Notice
            </button>
          </div>
        </div>
      </div>

      {{-- Table --}}
      <div class="card pn-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th class="pn-col-slug">Slug</th>
                  <th class="pn-col-dept">Departments</th>
                  <th style="width:190px;">Recruiter</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:120px;">Featured</th>
                  <th style="width:150px;">Last Date</th>
                  <th style="width:150px;">Publish At</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="pnTbodyActive">
                <tr><td colspan="11" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="pnEmptyActive" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa-solid fa-briefcase mb-2" style="font-size:34px;opacity:.6;"></i>
            <div>No active notices found.</div>
          </div>

          <div class="pn-footer d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="pnInfoActive">—</div>
            <nav><ul id="pnPagerActive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- INACTIVE --}}
    <div class="tab-pane fade" id="pn-tab-inactive" role="tabpanel">
      <div class="card pn-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th class="pn-col-slug">Slug</th>
                  <th class="pn-col-dept">Departments</th>
                  <th style="width:190px;">Recruiter</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:120px;">Featured</th>
                  <th style="width:150px;">Last Date</th>
                  <th style="width:150px;">Publish At</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="pnTbodyInactive">
                <tr><td colspan="11" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="pnEmptyInactive" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa-solid fa-circle-pause mb-2" style="font-size:34px;opacity:.6;"></i>
            <div>No inactive notices found.</div>
          </div>

          <div class="pn-footer d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="pnInfoInactive">—</div>
            <nav><ul id="pnPagerInactive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- TRASH --}}
    <div class="tab-pane fade" id="pn-tab-trash" role="tabpanel">
      <div class="card pn-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th class="pn-col-slug">Slug</th>
                  <th class="pn-col-dept">Departments</th>
                  <th style="width:190px;">Recruiter</th>
                  <th style="width:150px;">Deleted</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="pnTbodyTrash">
                <tr><td colspan="7" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="pnEmptyTrash" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa-solid fa-trash-can mb-2" style="font-size:34px;opacity:.6;"></i>
            <div>Trash is empty.</div>
          </div>

          <div class="pn-footer d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="pnInfoTrash">—</div>
            <nav><ul id="pnPagerTrash" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="pnFilterModal" tabindex="-1" aria-hidden="true">
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
            <select id="pnFilterDept" class="form-select">
              <option value="">All</option>
            </select>
            <div class="form-text">Filters notices that include the selected department.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Status</label>
            <select id="pnFilterStatus" class="form-select">
              <option value="">Auto (by tab)</option>
              <option value="published">Published</option>
              <option value="draft">Draft</option>
              <option value="archived">Archived</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Sort</label>
            <select id="pnFilterSort" class="form-select">
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="-updated_at">Recently Updated</option>
              <option value="title">Title A-Z</option>
              <option value="-title">Title Z-A</option>
              <option value="sort_order">Sort Order ↑</option>
              <option value="-sort_order">Sort Order ↓</option>
              <option value="-last_date_to_apply">Last Date (Desc)</option>
              <option value="last_date_to_apply">Last Date (Asc)</option>
              <option value="-publish_at">Publish At (Desc)</option>
              <option value="publish_at">Publish At (Asc)</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Featured</label>
            <select id="pnFilterFeatured" class="form-select">
              <option value="">Any</option>
              <option value="1">Featured only</option>
              <option value="0">Not featured</option>
            </select>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="pnApplyFilters" class="btn btn-primary">
          <i class="fa fa-check me-1"></i>Apply
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Select Departments Modal (NEW) --}}
<div class="modal fade" id="pnDeptPickerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-building-columns me-2"></i>Select Departments</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="pn-dept-list">
          <div class="top">
            <div class="position-relative" style="flex:1;min-width:240px;">
              <input id="pnDeptSearch" type="search" class="form-control ps-5" placeholder="Search departments...">
              <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
            </div>
            <div class="small text-muted" id="pnDeptCount">—</div>
          </div>
          <div class="body" id="pnDeptList">
            <div class="text-muted small p-3">Loading…</div>
          </div>
        </div>
        <div class="form-text mt-2">Selected departments will be stored in <code>department_ids[]</code>. Leave empty for “Global”.</div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="pnDeptApply">
          <i class="fa fa-check me-1"></i>Apply Selection
        </button>
      </div>

    </div>
  </div>
</div>

{{-- Add/Edit/View Modal --}}
<div class="modal fade" id="pnItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form class="modal-content" id="pnItemForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="pnItemTitle">Add Placement Notice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="pnUuid">
        <input type="hidden" id="pnId">

        {{-- NEW: department ids holder --}}
        <input type="hidden" id="pnDepartmentIds" value="">

        <div class="row g-3">

          {{-- ✅ Title at top --}}
          <div class="col-12">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input class="form-control" id="pnTitleInput" required maxlength="255" placeholder="e.g., TCS Off-campus Drive 2026">
          </div>

          {{-- ✅ Select Departments button at top (after title) --}}
          <div class="col-12">
            <div class="pn-dept-box">
              <div class="pn-dept-head">
                <div>
                  <div class="fw-semibold"><i class="fa fa-building-columns me-2"></i>Departments</div>
                  <div class="small text-muted">Select one or more departments (optional). Leave empty for Global.</div>
                </div>

                <button type="button" class="btn btn-outline-primary" id="pnPickDepts">
                  <i class="fa fa-square-check me-1"></i> Select Departments
                </button>
              </div>

              <div id="pnDeptChips" class="pn-dept-chips" style="display:none;"></div>
              <div id="pnDeptEmpty" class="pn-dept-empty">No departments selected (Global).</div>
            </div>
          </div>

          {{-- Left --}}
          <div class="col-lg-6">
            <div class="row g-3">

              {{-- ✅ Recruiter dropdown (instead of recruiter_id input) --}}
              <div class="col-md-6">
                <label class="form-label">Recruiter</label>
                <select class="form-select" id="pnRecruiter">
                  <option value="">Select recruiter (optional)</option>
                </select>
                <div class="form-text">Shows recruiter names only.</div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Slug (optional)</label>
                <input class="form-control" id="pnSlugInput" maxlength="160" placeholder="tcs-off-campus-drive-2026">
                <div class="form-text">Auto-generated from title until you edit manually.</div>
              </div>

              <div class="col-md-4">
                <label class="form-label">Sort Order</label>
                <input type="number" class="form-control" id="pnSortOrder" min="0" value="0">
              </div>

              <div class="col-md-8">
                <label class="form-label">Role / Position</label>
                <input class="form-control" id="pnRoleTitle" maxlength="255" placeholder="Software Engineer / Intern">
              </div>

              <div class="col-md-6">
                <label class="form-label">CTC</label>
                <input type="number" step="0.01" class="form-control" id="pnCTC" placeholder="e.g., 4.5">
              </div>

              <div class="col-md-6">
                <label class="form-label">Last Date to Apply</label>
                <input type="date" class="form-control" id="pnLastDate">
              </div>

              <div class="col-md-6">
                <label class="form-label">Apply URL</label>
                <input class="form-control" id="pnApplyUrl" maxlength="255" placeholder="https://...">
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select" id="pnStatus">
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="archived">Archived</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Featured on Home</label>
                <select class="form-select" id="pnFeatured">
                  <option value="0">No</option>
                  <option value="1">Yes</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Publish At</label>
                <input type="datetime-local" class="form-control" id="pnPublishAt">
              </div>

              <div class="col-md-6">
                <label class="form-label">Expire At</label>
                <input type="datetime-local" class="form-control" id="pnExpireAt">
              </div>

              <div class="col-12">
                <label class="form-label">Eligibility (optional)</label>
                <textarea class="form-control" id="pnEligibility" rows="3" placeholder="e.g., B.Tech CSE/IT, CGPA >= 7.0, no active backlogs"></textarea>
              </div>

              <div class="col-12">
                <label class="form-label">Banner Image (optional)</label>
                <input type="file" class="form-control" id="pnBannerFile" accept="image/*">
                <div class="form-text">Upload an image or use Banner URL below.</div>
              </div>

              <div class="col-12">
                <label class="form-label">Banner Image URL (optional)</label>
                <input class="form-control" id="pnBannerUrl" maxlength="255" placeholder="/depy_uploads/placement_notices/... OR https://...">
              </div>

            </div>
          </div>

          {{-- Right --}}
          <div class="col-lg-6">

            <div class="mb-2">
              <label class="form-label">Description (HTML allowed)</label>

              <div class="pn-rte" id="pnDescWrap">
                <div class="pn-rtebar" data-for="desc">
                  <button type="button" class="pn-rbtn" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
                  <button type="button" class="pn-rbtn" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
                  <button type="button" class="pn-rbtn" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>

                  <span class="pn-rsep"></span>

                  <button type="button" class="pn-rbtn" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
                  <button type="button" class="pn-rbtn" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>

                  <span class="pn-rsep"></span>

                  <button type="button" class="pn-rbtn" data-block="h2" title="Heading">H2</button>
                  <button type="button" class="pn-rbtn" data-block="h3" title="Subheading">H3</button>

                  <span class="pn-rsep"></span>

                  <button type="button" class="pn-rbtn" data-insert="pre" title="Code Block"><i class="fa fa-code"></i></button>
                  <button type="button" class="pn-rbtn" data-insert="code" title="Inline Code"><i class="fa fa-terminal"></i></button>

                  <span class="pn-rsep"></span>

                  <button type="button" class="pn-rbtn" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>

                  <div class="pn-rtabs">
                    <button type="button" class="tab active" data-mode="text">Text</button>
                    <button type="button" class="tab" data-mode="code">Code</button>
                  </div>
                </div>

                <div class="pn-rtearea">
                  <div id="pnDescEditor" class="pn-editor" contenteditable="true" data-placeholder="Write notice description…"></div>
                  <textarea id="pnDescCode" class="pn-code" spellcheck="false" placeholder="HTML code…"></textarea>
                </div>
              </div>

              <div class="pn-rte-help">Tip: Use Text for rich editing, Code to paste HTML directly.</div>
              <input type="hidden" id="pnDescription">
            </div>

            <div class="pn-preview mt-3">
              <div class="top">
                <div class="fw-semibold"><i class="fa fa-image me-2"></i>Banner Preview</div>
                <div class="d-flex align-items-center gap-2">
                  <button type="button" class="btn btn-light btn-sm" id="pnOpenBanner" style="display:none;">
                    <i class="fa fa-up-right-from-square me-1"></i>Open
                  </button>
                </div>
              </div>
              <div class="body">
                <img id="pnBannerPreview" src="" alt="Banner preview" style="display:none;">
                <div id="pnBannerEmpty" class="text-muted small" style="padding:12px;border:1px dashed var(--line-soft);border-radius:12px;">
                  No banner selected.
                </div>
                <div class="small text-muted mt-2" id="pnBannerMeta" style="display:none;">—</div>
              </div>
            </div>

          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="pnSaveBtn">
          <i class="fa fa-floppy-disk me-1"></i> Save
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="pnToastOk" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="pnToastOkText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="pnToastErr" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="pnToastErrText">Something went wrong</div>
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
  if (window.__PLACEMENT_NOTICES_UI__) return;
  window.__PLACEMENT_NOTICES_UI__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=320) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  const esc = (str) => (str ?? '').toString().replace(/[&<>"']/g, s => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]));

  const slugify = (s) => (s || '')
    .toString()
    .normalize('NFKD').replace(/[\u0300-\u036f]/g,'')
    .trim().toLowerCase()
    .replace(/['"`]/g,'')
    .replace(/[^a-z0-9]+/g,'-')
    .replace(/-+/g,'-')
    .replace(/^-|-$/g,'');

  const bytes = (n) => {
    const b = Number(n || 0);
    if (!b) return '—';
    const u = ['B','KB','MB','GB'];
    let i=0, v=b;
    while (v>=1024 && i<u.length-1){ v/=1024; i++; }
    return `${v.toFixed(i?1:0)} ${u[i]}`;
  };

  const normalizeUrl = (url) => {
    const u = (url || '').toString().trim();
    if (!u) return '';
    if (/^(data:|blob:|https?:\/\/)/i.test(u)) return u;
    if (u.startsWith('/')) return window.location.origin + u;
    return window.location.origin + '/' + u;
  };

  async function safeJson(res){
    try { return await res.json(); } catch(_){ return {}; }
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

    const loading = $('pnLoading');
    const showLoading = (v) => { if (loading) loading.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('pnToastOk');
    const toastErrEl = $('pnToastErr');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { const el=$('pnToastOkText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('pnToastErrText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = () => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    });

    /* =========================
     * ✅ FIX: Proper stacked modals (z-index + backdrops)
     * Makes pnDeptPickerModal render ABOVE pnItemModal.
     * ========================= */
    document.addEventListener('show.bs.modal', (ev) => {
      const modalEl = ev.target;
      // number of already-open modals
      const openCount = document.querySelectorAll('.modal.show').length;
      const zIndex = 1055 + (openCount * 20);
      modalEl.style.zIndex = zIndex;

      // once backdrop is in DOM, bump the latest one too
      setTimeout(() => {
        const backdrops = document.querySelectorAll('.modal-backdrop:not(.pn-stack)');
        const bd = backdrops[backdrops.length - 1];
        if (bd) {
          bd.style.zIndex = zIndex - 5;
          bd.classList.add('pn-stack');
        }
      }, 0);
    });

    document.addEventListener('hidden.bs.modal', () => {
      // if any modal still open, keep body locked like Bootstrap expects
      if (document.querySelectorAll('.modal.show').length) {
        document.body.classList.add('modal-open');
      }
    });
    /* ========================= */

    // permissions
    const ACTOR = { role: '' };
    let canCreate=false, canEdit=false, canDelete=false;

    function computePermissions(){
      const r = (ACTOR.role || '').toLowerCase();
      const full = ['admin','super_admin','director','principal'];
      const write = ['admin','super_admin','director','principal','hod','faculty','technical_assistant','it_person'];
      canCreate = full.includes(r);
      canDelete = full.includes(r);
      canEdit   = write.includes(r);
      const wc = $('pnWriteControls');
      if (wc) wc.style.display = canCreate ? 'flex' : 'none';
    }

    async function fetchMe(){
      try{
        const res = await fetchWithTimeout('/api/users/me', { headers: authHeaders() }, 8000);
        if (res.ok){
          const js = await safeJson(res);
          const role = js?.data?.role || js?.role;
          if (role) ACTOR.role = String(role).toLowerCase();
        }
      }catch(_){}
      if (!ACTOR.role){
        ACTOR.role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
      }
      computePermissions();
    }

    // UI refs
    const perPageSel = $('pnPerPage');
    const searchInput = $('pnSearch');
    const btnReset = $('pnBtnReset');
    const btnApplyFilters = $('pnApplyFilters');

    const tbodyActive = $('pnTbodyActive');
    const tbodyInactive = $('pnTbodyInactive');
    const tbodyTrash = $('pnTbodyTrash');

    const emptyActive = $('pnEmptyActive');
    const emptyInactive = $('pnEmptyInactive');
    const emptyTrash = $('pnEmptyTrash');

    const pagerActive = $('pnPagerActive');
    const pagerInactive = $('pnPagerInactive');
    const pagerTrash = $('pnPagerTrash');

    const infoActive = $('pnInfoActive');
    const infoInactive = $('pnInfoInactive');
    const infoTrash = $('pnInfoTrash');

    const filterDept = $('pnFilterDept');
    const filterStatus = $('pnFilterStatus');
    const filterSort = $('pnFilterSort');
    const filterFeatured = $('pnFilterFeatured');

    const itemModalEl = $('pnItemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;

    const deptPickerEl = $('pnDeptPickerModal');
    const deptPickerModal = deptPickerEl ? new bootstrap.Modal(deptPickerEl) : null;

    const itemTitle = $('pnItemTitle');
    const itemForm = $('pnItemForm');
    const saveBtn = $('pnSaveBtn');

    const pnUuid = $('pnUuid');
    const pnId = $('pnId');

    // form fields
    const deptIdsHidden = $('pnDepartmentIds');
    const deptBtn = $('pnPickDepts');
    const deptChips = $('pnDeptChips');
    const deptEmpty = $('pnDeptEmpty');

    const deptSearch = $('pnDeptSearch');
    const deptList = $('pnDeptList');
    const deptCount = $('pnDeptCount');
    const deptApply = $('pnDeptApply');

    const recruiterSel = $('pnRecruiter');
    const titleInput = $('pnTitleInput');
    const slugInput = $('pnSlugInput');
    const sortOrderInput = $('pnSortOrder');
    const roleTitleInput = $('pnRoleTitle');
    const ctcInput = $('pnCTC');
    const lastDateInput = $('pnLastDate');
    const applyUrlInput = $('pnApplyUrl');
    const statusSel = $('pnStatus');
    const featuredSel = $('pnFeatured');
    const publishAtInput = $('pnPublishAt');
    const expireAtInput = $('pnExpireAt');
    const eligibilityInput = $('pnEligibility');

    const bannerFile = $('pnBannerFile');
    const bannerUrl = $('pnBannerUrl');

    const bannerPreview = $('pnBannerPreview');
    const bannerEmpty = $('pnBannerEmpty');
    const bannerMeta = $('pnBannerMeta');
    const openBanner = $('pnOpenBanner');

    // ---------- state ----------
    const state = {
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      filters: { q:'', dept:'', status:'', featured:'', sort:'-created_at' },
      tabs: {
        active:   { page:1, lastPage:1, items:[] },
        inactive: { page:1, lastPage:1, items:[] },
        trash:    { page:1, lastPage:1, items:[] }
      },

      // lookups
      departments: [],   // [{id,title,slug,uuid}]
      deptMap: {},       // id -> {id,title,...}
      recruiters: [],    // [{id,name}]
      recruiterMap: {},  // id -> {id,name}

      // form selection
      selectedDeptIds: []
    };

    const getTabKey = () => {
      const a = document.querySelector('.pn-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#pn-tab-active';
      if (href === '#pn-tab-inactive') return 'inactive';
      if (href === '#pn-tab-trash') return 'trash';
      return 'active';
    };

    // ---------- lookups ----------
    function buildDeptMap(list){
      const map = {};
      (list || []).forEach(d => { if (d && d.id != null) map[String(d.id)] = d; });
      state.deptMap = map;
    }

    function buildRecruiterMap(list){
      const map = {};
      (list || []).forEach(r => { if (r && r.id != null) map[String(r.id)] = r; });
      state.recruiterMap = map;
    }

    async function loadDepartments(){
      // prefer controller lookups (returns departments list)
      const endpoints = [
        '/api/placement-notices?per_page=1',
        '/api/departments?per_page=300',
        '/api/departments'
      ];

      for (const url of endpoints){
        try{
          const res = await fetchWithTimeout(url, { headers: authHeaders() }, 15000);
          if (!res.ok) continue;
          const js = await safeJson(res);

          let arr = [];
          if (url.startsWith('/api/placement-notices')) {
            arr = Array.isArray(js?.lookups?.departments) ? js.lookups.departments : [];
          } else {
            arr =
              (Array.isArray(js?.data) ? js.data :
               Array.isArray(js?.departments) ? js.departments :
               Array.isArray(js) ? js : []);
          }

          if (!arr.length) continue;

          const opts = arr.map(d => {
            const id = d.id ?? d.department_id;
            const title = d.title ?? d.name ?? d.department_title ?? (id ? ('Department #' + id) : '');
            if (!id) return null;
            return { id: String(id), title: String(title), slug: d.slug ?? null, uuid: d.uuid ?? null };
          }).filter(Boolean);

          state.departments = opts;
          buildDeptMap(opts);

          // filter select
          if (filterDept){
            filterDept.innerHTML = `<option value="">All</option>` + opts.map(o =>
              `<option value="${esc(o.id)}">${esc(o.title)}</option>`
            ).join('');
          }

          // dept picker list
          renderDeptPickerList();
          return;
        }catch(_){}
      }

      // if nothing loaded, keep empty state
      state.departments = [];
      buildDeptMap([]);
      renderDeptPickerList();
    }

    async function loadRecruiters(){
      const endpoints = [
        '/api/recruiters?per_page=500',
        '/api/recruiters'
      ];

      for (const url of endpoints){
        try{
          const res = await fetchWithTimeout(url, { headers: authHeaders() }, 15000);
          if (!res.ok) continue;
          const js = await safeJson(res);

          const arr =
            (Array.isArray(js?.data) ? js.data :
             Array.isArray(js?.recruiters) ? js.recruiters :
             Array.isArray(js) ? js : []);

          if (!arr.length) continue;

          const list = arr.map(r => {
            const id = r.id ?? r.recruiter_id;
            const name = r.name ?? r.recruiter_name ?? r.title ?? r.company_name ?? '';
            if (!id) return null;
            return { id: String(id), name: String(name || ('Recruiter #' + id)) };
          }).filter(Boolean);

          state.recruiters = list;
          buildRecruiterMap(list);

          if (recruiterSel){
            recruiterSel.innerHTML =
              `<option value="">Select recruiter (optional)</option>` +
              list.map(x => `<option value="${esc(x.id)}">${esc(x.name)}</option>`).join('');
          }
          return;
        }catch(_){}
      }

      state.recruiters = [];
      buildRecruiterMap([]);
      if (recruiterSel){
        recruiterSel.innerHTML = `<option value="">Select recruiter (optional)</option>`;
      }
    }

    // ---------- departments picker UI ----------
    function setSelectedDeptIds(ids){
      const clean = (ids || [])
        .map(x => parseInt(x, 10))
        .filter(x => Number.isFinite(x) && x > 0);

      // unique
      const uniq = Array.from(new Set(clean));
      state.selectedDeptIds = uniq;
      if (deptIdsHidden) deptIdsHidden.value = uniq.length ? JSON.stringify(uniq) : '';
      renderDeptChips();
    }

    function renderDeptChips(){
      const ids = state.selectedDeptIds || [];
      if (!deptChips || !deptEmpty) return;

      if (!ids.length){
        deptChips.style.display = 'none';
        deptChips.innerHTML = '';
        deptEmpty.style.display = '';
        return;
      }

      deptEmpty.style.display = 'none';
      deptChips.style.display = 'flex';

      deptChips.innerHTML = ids.map(id => {
        const d = state.deptMap[String(id)];
        const title = d?.title ? d.title : `#${id}`;
        return `
          <span class="pn-chip" data-id="${esc(String(id))}">
            <span class="t">${esc(title)}</span>
            <button type="button" class="x" title="Remove" aria-label="Remove department">
              <i class="fa fa-xmark"></i>
            </button>
          </span>
        `;
      }).join('');
    }

    deptChips?.addEventListener('click', (e) => {
      const x = e.target.closest('.pn-chip .x');
      if (!x) return;
      const chip = e.target.closest('.pn-chip');
      const id = parseInt(chip?.dataset?.id || '', 10);
      if (!id) return;
      setSelectedDeptIds((state.selectedDeptIds || []).filter(v => v !== id));
      renderDeptPickerList(deptSearch?.value || '');
    });

    function renderDeptPickerList(query=''){
      if (!deptList) return;

      const q = (query || '').trim().toLowerCase();
      const all = state.departments || [];

      const filtered = !q ? all : all.filter(d => (d.title || '').toLowerCase().includes(q));
      const selected = new Set((state.selectedDeptIds || []).map(String));

      if (!filtered.length){
        deptList.innerHTML = `<div class="text-muted small p-3">No departments found.</div>`;
        if (deptCount) deptCount.textContent = `0 / ${all.length}`;
        return;
      }

      deptList.innerHTML = filtered.map(d => {
        const checked = selected.has(String(d.id)) ? 'checked' : '';
        const inputId = `pnDeptCk_${String(d.id).replace(/[^a-z0-9_]/gi,'_')}`;
        return `
          <label class="pn-dept-row" for="${esc(inputId)}">
            <input class="form-check-input m-0" type="checkbox" id="${esc(inputId)}" data-id="${esc(d.id)}" ${checked}>
            <span class="title">${esc(d.title)}</span>
          </label>
        `;
      }).join('');

      if (deptCount) deptCount.textContent = `${filtered.length} / ${all.length}`;
    }

    deptSearch?.addEventListener('input', debounce(() => {
      renderDeptPickerList(deptSearch.value || '');
    }, 120));

    deptBtn?.addEventListener('click', () => {
      if (!deptPickerModal) return;
      if (deptSearch) deptSearch.value = '';
      renderDeptPickerList('');
      deptPickerModal.show();
    });

    deptApply?.addEventListener('click', () => {
      const checks = Array.from(deptList?.querySelectorAll('input[type="checkbox"][data-id]') || []);
      const picked = checks.filter(c => c.checked).map(c => parseInt(c.dataset.id, 10)).filter(Boolean);
      setSelectedDeptIds(picked);
      bootstrap.Modal.getInstance(deptPickerEl)?.hide();
    });

    // ---------- query ----------
    function getSortParts(){
      const s = state.filters.sort || '-created_at';
      return {
        sort: s.startsWith('-') ? s.slice(1) : s,
        direction: s.startsWith('-') ? 'desc' : 'asc'
      };
    }

    function autoStatusForTab(tabKey){
      if (tabKey === 'trash') return '';
      if (state.filters.status) return state.filters.status;
      return tabKey === 'active' ? 'published' : 'draft';
    }

    function buildUrl(tabKey){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state.tabs[tabKey].page));

      const q = (state.filters.q || '').trim();
      if (q) params.set('q', q);

      const { sort, direction } = getSortParts();
      params.set('sort', sort);
      params.set('direction', direction);

      if (state.filters.featured !== '') params.set('featured', state.filters.featured);
      if (state.filters.dept) params.set('department', state.filters.dept);

      if (tabKey === 'trash') return `/api/placement-notices/trash?${params.toString()}`;

      const st = autoStatusForTab(tabKey);
      if (st) params.set('status', st);

      return `/api/placement-notices?${params.toString()}`;
    }

    // ---------- render helpers ----------
    function badgeStatus(status){
      const s = (status || '').toString().toLowerCase();
      if (s === 'published') return `<span class="badge pn-badge-success">Published</span>`;
      if (s === 'draft') return `<span class="badge pn-badge-warning">Draft</span>`;
      if (s === 'archived') return `<span class="badge pn-badge-muted">Archived</span>`;
      return `<span class="badge pn-badge-muted">${esc(s || '—')}</span>`;
    }
    function badgeFeatured(v){
      return v ? `<span class="badge pn-badge-primary">Yes</span>` : `<span class="badge pn-badge-muted">No</span>`;
    }

    function deptText(r){
      const deps = Array.isArray(r?.departments) ? r.departments : [];
      if (deps.length){
        const names = deps.map(d => d?.title).filter(Boolean);
        if (names.length) return names.join(', ');
      }
      const ids = Array.isArray(r?.department_ids) ? r.department_ids : [];
      if (ids.length) return ids.join(', ');
      return 'Global';
    }

    function recruiterText(r){
      const name = r?.recruiter_name || '';
      if (name) return name;
      if (r?.recruiter_id) return `#${r.recruiter_id}`;
      return '—';
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

      if (tabKey === 'trash'){
        tbody.innerHTML = rows.map(r => {
          const uuid = r.uuid || '';
          const title = r.title || '—';
          const slug = r.slug || '—';
          const deleted = r.deleted_at || '—';
          const sortOrder = (r.sort_order ?? 0);

          const actions = `
            <div class="dropdown text-end">
              <button type="button" class="btn btn-light btn-sm dd-toggle pn-dd-toggle"
                aria-expanded="false" title="Actions">
                <i class="fa fa-ellipsis-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><button type="button" class="dropdown-item" data-action="view" data-uuid="${esc(uuid)}"><i class="fa fa-eye"></i> View</button></li>
                <li><hr class="dropdown-divider"></li>
                <li><button type="button" class="dropdown-item" data-action="restore" data-uuid="${esc(uuid)}"><i class="fa fa-rotate-left"></i> Restore</button></li>
                ${canDelete ? `<li><button type="button" class="dropdown-item text-danger" data-action="force" data-uuid="${esc(uuid)}"><i class="fa fa-skull-crossbones"></i> Delete Permanently</button></li>` : ``}
              </ul>
            </div>`;

          return `
            <tr data-uuid="${esc(uuid)}">
              <td class="fw-semibold">${esc(title)}</td>
              <td class="pn-col-slug"><code>${esc(slug)}</code></td>
              <td class="pn-col-dept"><span class="pn-dept-text" title="${esc(deptText(r))}">${esc(deptText(r))}</span></td>
              <td>${esc(recruiterText(r))}</td>
              <td>${esc(String(deleted))}</td>
              <td>${esc(String(sortOrder))}</td>
              <td class="text-end">${actions}</td>
            </tr>`;
        }).join('');

        renderPager(tabKey);
        return;
      }

      tbody.innerHTML = rows.map(r => {
        const uuid = r.uuid || '';
        const title = r.title || '—';
        const slug = r.slug || '—';
        const status = r.status || '—';
        const featured = !!(r.is_featured_home ?? 0);
        const lastDate = r.last_date_to_apply || '—';
        const publishAt = r.publish_at || '—';
        const updated = r.updated_at || '—';
        const sortOrder = (r.sort_order ?? 0);

        const actions = `
          <div class="dropdown text-end">
            <button type="button" class="btn btn-light btn-sm dd-toggle pn-dd-toggle"
              aria-expanded="false" title="Actions">
              <i class="fa fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><button type="button" class="dropdown-item" data-action="view" data-uuid="${esc(uuid)}"><i class="fa fa-eye"></i> View</button></li>
              ${canEdit ? `<li><button type="button" class="dropdown-item" data-action="edit" data-uuid="${esc(uuid)}"><i class="fa fa-pen-to-square"></i> Edit</button></li>` : ``}
              ${canEdit ? `<li><button type="button" class="dropdown-item" data-action="toggle_featured" data-uuid="${esc(uuid)}"><i class="fa fa-star"></i> Toggle Featured</button></li>` : ``}
              ${r.apply_url ? `<li><a class="dropdown-item" href="${esc(r.apply_url)}" target="_blank" rel="noopener"><i class="fa fa-up-right-from-square"></i> Open Apply Link</a></li>` : ``}
              ${canDelete ? `<li><hr class="dropdown-divider"></li>
                <li><button type="button" class="dropdown-item text-danger" data-action="delete" data-uuid="${esc(uuid)}"><i class="fa fa-trash"></i> Move to Trash</button></li>` : ``}
            </ul>
          </div>`;

        return `
          <tr data-uuid="${esc(uuid)}">
            <td class="fw-semibold">${esc(title)}</td>
            <td class="pn-col-slug"><code>${esc(slug)}</code></td>
            <td class="pn-col-dept"><span class="pn-dept-text" title="${esc(deptText(r))}">${esc(deptText(r))}</span></td>
            <td>${esc(recruiterText(r))}</td>
            <td>${badgeStatus(status)}</td>
            <td>${badgeFeatured(featured)}</td>
            <td>${esc(String(lastDate))}</td>
            <td>${esc(String(publishAt))}</td>
            <td>${esc(String(sortOrder))}</td>
            <td>${esc(String(updated))}</td>
            <td class="text-end">${actions}</td>
          </tr>`;
      }).join('');

      renderPager(tabKey);
    }

    // ---------- load ----------
    async function loadTab(tabKey){
      const tbody = tabKey==='active' ? tbodyActive : (tabKey==='inactive' ? tbodyInactive : tbodyTrash);
      if (tbody){
        const cols = (tabKey==='trash') ? 7 : 11;
        tbody.innerHTML = `<tr><td colspan="${cols}" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;
      }

      try{
        const res = await fetchWithTimeout(buildUrl(tabKey), { headers: authHeaders() }, 18000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        const js = await safeJson(res);
        if (!res.ok) throw new Error(js?.message || 'Failed to load');

        // ✅ auto-refresh departments lookup from API if provided
        if (Array.isArray(js?.lookups?.departments) && js.lookups.departments.length) {
          const opts = js.lookups.departments.map(d => ({
            id: String(d.id),
            title: String(d.title || d.name || ('Department #' + d.id)),
            slug: d.slug ?? null,
            uuid: d.uuid ?? null
          }));
          state.departments = opts;
          buildDeptMap(opts);
          if (filterDept){
            const cur = filterDept.value || '';
            filterDept.innerHTML = `<option value="">All</option>` + opts.map(o =>
              `<option value="${esc(o.id)}">${esc(o.title)}</option>`
            ).join('');
            filterDept.value = cur;
          }
          renderDeptPickerList(deptSearch?.value || '');
          renderDeptChips();
        }

        const items = Array.isArray(js.data) ? js.data : [];
        const p = js.pagination || js.meta || {};
        state.tabs[tabKey].items = items;
        state.tabs[tabKey].lastPage = parseInt(p.last_page || p.total_pages || 1, 10) || 1;

        const totalTxt = p.total ? `${p.total} result(s)` : '—';
        if (tabKey === 'active' && infoActive) infoActive.textContent = totalTxt;
        if (tabKey === 'inactive' && infoInactive) infoInactive.textContent = totalTxt;
        if (tabKey === 'trash' && infoTrash) infoTrash.textContent = totalTxt;

        renderTable(tabKey);
      }catch(e){
        state.tabs[tabKey].items = [];
        state.tabs[tabKey].lastPage = 1;
        renderTable(tabKey);
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    function reloadCurrent(){ loadTab(getTabKey()); }

    // ---------- pager clicks ----------
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

    // ---------- filters ----------
    searchInput?.addEventListener('input', debounce(() => {
      state.filters.q = (searchInput.value || '').trim();
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    }, 320));

    perPageSel?.addEventListener('change', () => {
      state.perPage = parseInt(perPageSel.value, 10) || 20;
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    $('pnFilterModal')?.addEventListener('show.bs.modal', () => {
      filterDept.value = state.filters.dept || '';
      filterStatus.value = state.filters.status || '';
      filterSort.value = state.filters.sort || '-created_at';
      filterFeatured.value = (state.filters.featured ?? '');
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.dept = filterDept.value || '';
      state.filters.status = filterStatus.value || '';
      state.filters.sort = filterSort.value || '-created_at';
      state.filters.featured = (filterFeatured.value ?? '');
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      bootstrap.Modal.getInstance($('pnFilterModal'))?.hide();
      reloadCurrent();
    });

    btnReset?.addEventListener('click', () => {
      state.filters = { q:'', dept:'', status:'', featured:'', sort:'-created_at' };
      state.perPage = 20;
      searchInput.value = '';
      perPageSel.value = '20';
      filterDept.value = '';
      filterStatus.value = '';
      filterSort.value = '-created_at';
      filterFeatured.value = '';
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    document.querySelector('a[href="#pn-tab-active"]')?.addEventListener('shown.bs.tab', () => loadTab('active'));
    document.querySelector('a[href="#pn-tab-inactive"]')?.addEventListener('shown.bs.tab', () => loadTab('inactive'));
    document.querySelector('a[href="#pn-tab-trash"]')?.addEventListener('shown.bs.tab', () => loadTab('trash'));

    // ---------- ✅ ACTION DROPDOWN FIX ----------
    function closeAllPnDropdownsExcept(exceptToggle){
      document.querySelectorAll('.pn-dd-toggle').forEach(t => {
        if (exceptToggle && t === exceptToggle) return;
        try { bootstrap.Dropdown.getInstance(t)?.hide(); } catch(_){}
      });
    }

    document.addEventListener('click', (e) => {
      if (e.target.closest('.pn-card .dropdown')) return;
      closeAllPnDropdownsExcept(null);
    }, { capture:true });

    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('.pn-dd-toggle');
      if (!toggle) return;

      e.preventDefault();
      e.stopPropagation();

      closeAllPnDropdownsExcept(toggle);

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

    // ---------- mini editor ----------
    const rte = {
      wrap: $('pnDescWrap'),
      bar: $('pnDescWrap')?.querySelector('.pn-rtebar'),
      editor: $('pnDescEditor'),
      code: $('pnDescCode'),
      hidden: $('pnDescription'),
      mode: 'text',
      enabled: true
    };

    const ensurePreHasCode = (html) => (html || '').replace(/<pre>([\s\S]*?)<\/pre>/gi, (m, inner) => {
      if (/<code[\s>]/i.test(inner)) return `<pre>${inner}</pre>`;
      return `<pre><code>${inner}</code></pre>`;
    });

    function rteFocus(){
      try { rte.editor?.focus({ preventScroll:true }); }
      catch(_) { try { rte.editor?.focus(); } catch(__){} }
    }

    function placeCaretAtMarker(marker){
      const sel = window.getSelection();
      if (!sel || !marker) return;
      const range = document.createRange();
      range.setStartAfter(marker);
      range.collapse(true);
      sel.removeAllRanges();
      sel.addRange(range);
      marker.remove();
    }

    function insertHtmlWithCaret(html){
      rteFocus();
      const markerId = 'pn_caret_' + Math.random().toString(16).slice(2);
      document.execCommand('insertHTML', false, html + `<span id="${markerId}">\u200b</span>`);
      const marker = document.getElementById(markerId);
      if (marker) placeCaretAtMarker(marker);
    }

    function syncEditorToCode(){
      if (!rte.editor || !rte.code) return;
      if (rte.mode === 'text') rte.code.value = ensurePreHasCode(rte.editor.innerHTML || '');
    }

    function setRteMode(mode){
      rte.mode = (mode === 'code') ? 'code' : 'text';
      rte.wrap?.classList.toggle('mode-code', rte.mode === 'code');

      rte.wrap?.querySelectorAll('.pn-rtabs .tab').forEach(t => {
        t.classList.toggle('active', t.dataset.mode === rte.mode);
      });

      const disable = (rte.mode === 'code') || !rte.enabled;
      rte.wrap?.querySelectorAll('.pn-rbtn').forEach(b => {
        b.disabled = disable;
        b.style.opacity = disable ? '0.55' : '';
        b.style.pointerEvents = disable ? 'none' : '';
      });

      if (rte.mode === 'code'){
        rte.code.value = ensurePreHasCode(rte.editor.innerHTML || '');
        setTimeout(()=>{ try{ rte.code?.focus(); }catch(_){ } }, 0);
      } else {
        rte.editor.innerHTML = ensurePreHasCode(rte.code.value || '');
        setTimeout(()=>{ rteFocus(); }, 0);
      }
    }

    function updateToolbarActive(){
      if (!rte.bar || rte.mode !== 'text') return;
      const set = (cmd, on) => {
        const b = rte.bar.querySelector(`.pn-rbtn[data-cmd="${cmd}"]`);
        if (b) b.classList.toggle('active', !!on);
      };
      try{
        set('bold', document.queryCommandState('bold'));
        set('italic', document.queryCommandState('italic'));
        set('underline', document.queryCommandState('underline'));
      }catch(_){}
    }

    rte.bar?.addEventListener('pointerdown', (e) => e.preventDefault());
    rte.editor?.addEventListener('input', () => { syncEditorToCode(); updateToolbarActive(); });
    ['mouseup','keyup','click'].forEach(ev => rte.editor?.addEventListener(ev, updateToolbarActive));
    document.addEventListener('selectionchange', () => {
      if (document.activeElement === rte.editor) updateToolbarActive();
    });

    document.addEventListener('click', (e) => {
      const tab = e.target.closest('#pnDescWrap .pn-rtabs .tab');
      if (tab){ setRteMode(tab.dataset.mode); return; }

      const btn = e.target.closest('#pnDescWrap .pn-rbtn');
      if (!btn || rte.mode !== 'text' || !rte.enabled) return;

      rteFocus();

      const block = btn.getAttribute('data-block');
      const insert = btn.getAttribute('data-insert');
      const cmd = btn.getAttribute('data-cmd');

      if (block){
        try{ document.execCommand('formatBlock', false, `<${block}>`); }catch(_){}
        syncEditorToCode(); updateToolbarActive();
        return;
      }

      if (insert === 'code'){
        const sel = window.getSelection();
        const hasSel = sel && sel.rangeCount && !sel.isCollapsed && sel.toString().trim();
        if (hasSel) document.execCommand('insertHTML', false, `<code>${esc(sel.toString())}</code>`);
        else insertHtmlWithCaret('<code></code>');
        syncEditorToCode(); updateToolbarActive();
        return;
      }

      if (insert === 'pre'){
        const sel = window.getSelection();
        const hasSel = sel && sel.rangeCount && !sel.isCollapsed && sel.toString().trim();
        if (hasSel) document.execCommand('insertHTML', false, `<pre><code>${esc(sel.toString())}</code></pre>`);
        else insertHtmlWithCaret('<pre><code></code></pre>');
        syncEditorToCode(); updateToolbarActive();
        return;
      }

      if (cmd){
        try{ document.execCommand(cmd, false, null); }catch(_){}
        syncEditorToCode(); updateToolbarActive();
      }
    });

    function setRteEnabled(on){
      rte.enabled = !!on;
      rte.editor?.setAttribute('contenteditable', on ? 'true' : 'false');
      if (rte.code) rte.code.disabled = !on;

      const disable = (rte.mode === 'code') || !rte.enabled;
      rte.wrap?.querySelectorAll('.pn-rbtn').forEach(b => {
        b.disabled = disable;
        b.style.opacity = disable ? '0.55' : '';
        b.style.pointerEvents = disable ? 'none' : '';
      });
      rte.wrap?.querySelectorAll('.pn-rtabs .tab').forEach(t => {
        t.style.pointerEvents = on ? '' : 'none';
        t.style.opacity = on ? '' : '0.7';
      });
    }

    // ---------- banner preview ----------
    let bannerObjectUrl = null;

    function clearBannerPreview(revoke=true){
      if (revoke && bannerObjectUrl){
        try{ URL.revokeObjectURL(bannerObjectUrl); }catch(_){}
      }
      bannerObjectUrl = null;

      bannerPreview.style.display = 'none';
      bannerPreview.removeAttribute('src');
      bannerEmpty.style.display = '';
      bannerMeta.style.display = 'none';
      bannerMeta.textContent = '—';
      openBanner.style.display = 'none';
      openBanner.onclick = null;
    }

    function setBannerPreview(url, metaText=''){
      const u = normalizeUrl(url);
      if (!u){ clearBannerPreview(true); return; }

      bannerPreview.style.display = '';
      bannerPreview.src = u;
      bannerEmpty.style.display = 'none';

      bannerMeta.style.display = metaText ? '' : 'none';
      bannerMeta.textContent = metaText || '';

      openBanner.style.display = '';
      openBanner.onclick = () => window.open(u, '_blank', 'noopener');
    }

    bannerFile?.addEventListener('change', () => {
      const f = bannerFile.files?.[0];
      if (!f){ clearBannerPreview(true); return; }

      if (bannerObjectUrl){
        try{ URL.revokeObjectURL(bannerObjectUrl); }catch(_){}
      }
      bannerObjectUrl = URL.createObjectURL(f);
      setBannerPreview(bannerObjectUrl, `${f.name || 'banner'} • ${bytes(f.size)}`);
    });

    bannerUrl?.addEventListener('input', debounce(() => {
      if (bannerFile.files?.length) return;
      const u = (bannerUrl.value || '').trim();
      if (!u) { clearBannerPreview(true); return; }
      setBannerPreview(u, 'From URL');
    }, 200));

    itemModalEl?.addEventListener('hidden.bs.modal', () => {
      if (bannerObjectUrl){
        try{ URL.revokeObjectURL(bannerObjectUrl); }catch(_){}
        bannerObjectUrl = null;
      }
    });

    // ---------- modal logic ----------
    let saving = false;
    let slugDirty = false;
    let settingSlug = false;

    function setBtnLoading(btn, loading){
      btn.disabled = !!loading;
      btn.classList.toggle('btn-loading', !!loading);
      if (loading) btn.style.color = 'transparent';
      else btn.style.color = '';
    }

    function resetForm(){
      itemForm.reset();
      pnUuid.value = '';
      pnId.value = '';
      slugDirty = false;
      settingSlug = false;

      // departments selection reset
      setSelectedDeptIds([]);

      rte.editor.innerHTML = '';
      rte.code.value = '';
      rte.hidden.value = '';
      setRteMode('text');
      setRteEnabled(true);

      clearBannerPreview(true);

      itemForm.querySelectorAll('input,select,textarea,button').forEach(el => {
        if (!el || !el.id) return;
        if (el.id === 'pnUuid' || el.id === 'pnId' || el.id === 'pnDepartmentIds') return;
        if (el.type === 'file') el.disabled = false;
        else if (el.tagName === 'SELECT') el.disabled = false;
        else if (el.tagName === 'BUTTON') el.disabled = false;
        else el.readOnly = false;
      });

      saveBtn.style.display = '';
      itemForm.dataset.mode = 'edit';
      itemForm.dataset.intent = 'create';
    }

    function toLocalDateTime(s){
      if (!s) return '';
      const t = String(s).replace(' ', 'T');
      return t.length >= 16 ? t.slice(0,16) : t;
    }

    async function fetchOne(uuid){
      const url = `/api/placement-notices/${encodeURIComponent(uuid)}?with_trashed=1`;
      const res = await fetchWithTimeout(url, { headers: authHeaders() }, 15000);
      const js = await safeJson(res);
      if (!res.ok) throw new Error(js?.message || 'Failed to fetch item');
      return js?.item || js?.data || null;
    }

    function fillForm(r, viewOnly=false){
      pnUuid.value = r.uuid || '';
      pnId.value = r.id || '';

      // departments array
      const ids = Array.isArray(r.department_ids) ? r.department_ids : (
        Array.isArray(r.departments) ? r.departments.map(d => d?.id).filter(Boolean) : []
      );
      setSelectedDeptIds(ids);

      // recruiter dropdown
      recruiterSel.value = r.recruiter_id ? String(r.recruiter_id) : '';

      titleInput.value = r.title || '';
      slugInput.value = r.slug || '';
      sortOrderInput.value = String(r.sort_order ?? 0);

      roleTitleInput.value = r.role_title || '';
      ctcInput.value = (r.ctc ?? '') === null ? '' : String(r.ctc ?? '');
      lastDateInput.value = r.last_date_to_apply || '';
      applyUrlInput.value = r.apply_url || '';

      statusSel.value = (r.status || 'draft');
      featuredSel.value = String((r.is_featured_home ?? 0) ? 1 : 0);

      publishAtInput.value = toLocalDateTime(r.publish_at);
      expireAtInput.value = toLocalDateTime(r.expire_at);

      eligibilityInput.value = r.eligibility || '';

      const desc = (r.description ?? '') || '';
      rte.editor.innerHTML = ensurePreHasCode(desc);
      syncEditorToCode();
      setRteMode('text');

      const banner = r.banner_image_full_url || r.banner_image_url || '';
      bannerUrl.value = r.banner_image_url || '';
      if (banner){
        clearBannerPreview(true);
        setBannerPreview(banner, 'Saved banner');
      } else {
        clearBannerPreview(true);
      }

      slugDirty = true;

      if (viewOnly){
        itemForm.querySelectorAll('input,select,textarea,button').forEach(el => {
          if (!el || !el.id) return;
          if (el.id === 'pnUuid' || el.id === 'pnId' || el.id === 'pnOpenBanner') return;
          if (el.type === 'file') el.disabled = true;
          else if (el.tagName === 'SELECT') el.disabled = true;
          else if (el.tagName === 'BUTTON') el.disabled = true;
          else el.readOnly = true;
        });
        // keep close button active (bootstrap)
        itemModalEl.querySelectorAll('.btn-close').forEach(b => b.disabled = false);

        setRteEnabled(false);
        saveBtn.style.display = 'none';
        itemForm.dataset.mode = 'view';
        itemForm.dataset.intent = 'view';
      } else {
        setRteEnabled(true);
        saveBtn.style.display = '';
        itemForm.dataset.mode = 'edit';
        itemForm.dataset.intent = 'edit';
      }
    }

    titleInput?.addEventListener('input', debounce(() => {
      if (itemForm.dataset.mode === 'view') return;
      if (pnUuid.value) return;
      if (slugDirty) return;
      const next = slugify(titleInput.value);
      settingSlug = true;
      slugInput.value = next;
      settingSlug = false;
    }, 120));

    slugInput?.addEventListener('input', () => {
      if (pnUuid.value) return;
      if (settingSlug) return;
      slugDirty = !!(slugInput.value || '').trim();
    });

    $('pnBtnAdd')?.addEventListener('click', () => {
      if (!canCreate) return;
      resetForm();
      itemTitle.textContent = 'Add Placement Notice';
      itemForm.dataset.intent = 'create';
      itemModal.show();
    });

    // ---------- row actions ----------
    async function closeDropdownFrom(el){
      const toggle = el.closest('.dropdown')?.querySelector('.pn-dd-toggle');
      if (toggle){
        try{ bootstrap.Dropdown.getOrCreateInstance(toggle).hide(); }catch(_){}
      }
    }

    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action][data-uuid]');
      if (!btn) return;

      const uuid = btn.dataset.uuid;
      const act = btn.dataset.action;
      if (!uuid) return;

      await closeDropdownFrom(btn);

      if (act === 'view' || act === 'edit'){
        if (act === 'edit' && !canEdit) return;

        showLoading(true);
        try{
          const item = await fetchOne(uuid);
          resetForm();
          itemTitle.textContent = act === 'view' ? 'View Placement Notice' : 'Edit Placement Notice';
          fillForm(item || {}, act === 'view');
          itemModal.show();
        }catch(ex){
          err(ex?.message || 'Failed');
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'toggle_featured'){
        if (!canEdit) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/placement-notices/${encodeURIComponent(uuid)}/toggle-featured`, {
            method: 'PATCH',
            headers: authHeaders()
          }, 15000);
          const js = await safeJson(res);
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed');
          ok('Updated featured');
          await Promise.all([loadTab('active'), loadTab('inactive')]);
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
          title: 'Move to Trash?',
          text: 'You can restore it later from Trash.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/placement-notices/${encodeURIComponent(uuid)}`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);
          const js = await safeJson(res);
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Delete failed');

          ok('Moved to trash');
          await Promise.all([loadTab('active'), loadTab('inactive'), loadTab('trash')]);
        }catch(ex){
          err(ex?.message || 'Failed');
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'restore'){
        const conf = await Swal.fire({
          title: 'Restore this notice?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Restore'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/placement-notices/${encodeURIComponent(uuid)}/restore`, {
            method: 'PATCH',
            headers: authHeaders()
          }, 15000);
          const js = await safeJson(res);
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Restore failed');

          ok('Restored');
          await Promise.all([loadTab('trash'), loadTab('active'), loadTab('inactive')]);
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
          text: 'This cannot be undone.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete Permanently',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/placement-notices/${encodeURIComponent(uuid)}/force`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);
          const js = await safeJson(res);
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

    // ---------- submit ----------
    itemForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (saving) return;

      try{
        if (itemForm.dataset.mode === 'view') return;

        const intent = itemForm.dataset.intent || 'create';
        const isEdit = (intent === 'edit') && !!pnUuid.value;

        if (isEdit && !canEdit) return;
        if (!isEdit && !canCreate) return;

        const title = (titleInput.value || '').trim();
        if (!title){ err('Title is required'); titleInput.focus(); return; }

        const descHtml = (rte.mode === 'code') ? (rte.code.value || '') : (rte.editor.innerHTML || '');
        const descClean = ensurePreHasCode(descHtml).trim();
        rte.hidden.value = descClean;

        const fd = new FormData();

        // ✅ departments array
        const deptIds = (state.selectedDeptIds || []).map(x => parseInt(x, 10)).filter(Boolean);
        deptIds.forEach(id => fd.append('department_ids[]', String(id)));

        // ✅ recruiter dropdown
        if (recruiterSel.value) fd.append('recruiter_id', recruiterSel.value);

        fd.append('title', title);
        if ((slugInput.value || '').trim()) fd.append('slug', slugInput.value.trim());

        if ((roleTitleInput.value || '').trim()) fd.append('role_title', roleTitleInput.value.trim());
        if ((ctcInput.value || '').trim()) fd.append('ctc', ctcInput.value.trim());
        if ((eligibilityInput.value || '').trim()) fd.append('eligibility', eligibilityInput.value.trim());
        if ((applyUrlInput.value || '').trim()) fd.append('apply_url', applyUrlInput.value.trim());
        if ((lastDateInput.value || '').trim()) fd.append('last_date_to_apply', lastDateInput.value.trim());

        fd.append('status', (statusSel.value || 'draft').trim());
        fd.append('is_featured_home', (featuredSel.value === '1') ? '1' : '0');
        fd.append('sort_order', String(parseInt(sortOrderInput.value || '0', 10) || 0));

        if ((publishAtInput.value || '').trim()) fd.append('publish_at', publishAtInput.value.trim());
        if ((expireAtInput.value || '').trim()) fd.append('expire_at', expireAtInput.value.trim());

        if (descClean) fd.append('description', descClean);

        const file = bannerFile.files?.[0] || null;
        if (file) {
          fd.append('banner_image', file);
        } else if ((bannerUrl.value || '').trim()) {
          fd.append('banner_image_url', bannerUrl.value.trim());
        }

        let url = '/api/placement-notices';
        if (isEdit){
          url = `/api/placement-notices/${encodeURIComponent(pnUuid.value)}`;
          fd.append('_method', 'PUT');
        }

        saving = true;
        setBtnLoading(saveBtn, true);
        showLoading(true);

        const res = await fetchWithTimeout(url, {
          method: 'POST',
          headers: authHeaders(),
          body: fd
        }, 22000);

        const js = await safeJson(res);
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

    // ---------- init ----------
    (async () => {
      showLoading(true);
      try{
        await fetchMe();
        await Promise.all([loadDepartments(), loadRecruiters()]);
        renderDeptChips();
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
