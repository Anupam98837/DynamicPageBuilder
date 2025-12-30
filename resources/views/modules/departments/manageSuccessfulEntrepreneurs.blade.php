{{-- resources/views/modules/departments/manageSuccessfulEntrepreneurs.blade.php --}}
@section('title','Successful Entrepreneurs')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Successful Entrepreneurs - Admin UI
 * (reference-based, not copied)
 * ========================= */

.se-wrap{max-width:1200px;margin:16px auto 42px;padding:0 6px;overflow:visible}

/* Dropdown safety inside tables */
.se-table-wrap .dropdown{position:relative}
.se-dd-toggle{border-radius:10px}
.se-table-wrap .dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:230px;
  z-index:99999; /* ✅ match reference page: keep above table/footer */
}
.se-table-wrap .dropdown-menu.show{display:block !important}
.se-table-wrap .dropdown-item{display:flex;align-items:center;gap:.6rem}
.se-table-wrap .dropdown-item i{width:16px;text-align:center}
.se-table-wrap .dropdown-item.text-danger{color:var(--danger-color) !important}

/* Tabs */
.se-tabs.nav-tabs{border-color:var(--line-strong)}
.se-tabs .nav-link{color:var(--ink)}
.se-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface);
}
.tab-content,.tab-pane{overflow:visible}

/* Toolbar */
.se-toolbar.panel{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:16px;
  box-shadow:var(--shadow-2);
  padding:12px 12px;
}

/* Table Card */
.se-table-wrap.card{
  position:relative;
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.se-table-wrap .card-body{overflow:visible}
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

/* “Slug” column */
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

/* Person cell */
.person{
  display:flex;align-items:center;gap:10px;min-width:280px;
}
.person .avatar{
  width:42px;height:42px;border-radius:12px;flex:0 0 auto;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
  overflow:hidden;
  display:flex;align-items:center;justify-content:center;
}
.person .avatar img{width:100%;height:100%;object-fit:cover}
.person .meta{min-width:0}
.person .meta .name{font-weight:700;line-height:1.2}
.person .meta .sub{font-size:12.5px;color:var(--muted-color);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:280px}

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
.se-loading{
  position:fixed;inset:0;
  background:rgba(0,0,0,0.45);
  display:flex;
  justify-content:center;
  align-items:center;
  z-index:9999;
  backdrop-filter:blur(2px)
}
.se-loading .box{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,0.3)
}
.se-spinner{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,0.3);
  border-top:4px solid var(--primary-color);
  animation:seSpin 1s linear infinite
}
@keyframes seSpin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

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
  animation:seSpin 1s linear infinite
}

/* Responsive toolbar */
@media (max-width: 768px){
  .se-toolbar .row{gap:12px}
  .se-toolbar .tool-actions{display:flex;gap:8px;flex-wrap:wrap}
  .se-toolbar .tool-actions .btn{flex:1;min-width:140px}
}

/* Horizontal scroll (keep dropdown visible vertically) */
.table-responsive{
  display:block;
  width:100%;
  max-width:100%;
  overflow-x:auto !important;
  overflow-y:visible !important;
  -webkit-overflow-scrolling:touch;
  position:relative;
}
.table-responsive > .table{width:max-content;min-width:1180px;}
.table-responsive th,.table-responsive td{white-space:nowrap;}
@media (max-width: 576px){
  .table-responsive > .table{min-width:1120px;}
}

/* =========================
 * Mini RTE (Description)
 * ========================= */
.se-rte-help{font-size:12px;color:var(--muted-color);margin-top:6px}
.se-rte-wrap{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:var(--surface);
}
.se-rte-toolbar{
  display:flex;align-items:center;gap:6px;flex-wrap:wrap;
  padding:8px;
  border-bottom:1px solid var(--line-strong);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.se-rte-btn{
  border:1px solid var(--line-soft);
  background:transparent;
  color:var(--ink);
  padding:7px 9px;
  border-radius:10px;
  line-height:1;
  cursor:pointer;
  display:inline-flex;align-items:center;justify-content:center;
  user-select:none;
}
.se-rte-btn:hover{background:var(--page-hover)}
.se-rte-btn.active{
  background:color-mix(in oklab, var(--primary-color) 14%, transparent);
  border-color:color-mix(in oklab, var(--primary-color) 35%, var(--line-soft));
}
.se-rte-sep{width:1px;height:24px;background:var(--line-soft);margin:0 4px}
.se-rte-tabs{
  margin-left:auto;display:flex;
  border:1px solid var(--line-soft);
  border-radius:0;
  overflow:hidden;
}
.se-rte-tabs .tab{
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
.se-rte-tabs .tab:last-child{border-right:0}
.se-rte-tabs .tab.active{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  font-weight:700;
}
.se-rte-area{position:relative}
.se-rte-editor{
  min-height:210px;
  padding:12px 12px;
  outline:none;
}
.se-rte-editor:empty:before{content:attr(data-placeholder);color:var(--muted-color);}
.se-rte-editor b,.se-rte-editor strong{font-weight:800}
.se-rte-editor i,.se-rte-editor em{font-style:italic}
.se-rte-editor u{text-decoration:underline}
.se-rte-editor ul,.se-rte-editor ol{padding-left:22px}
.se-rte-editor p{margin:0 0 10px}
.se-rte-editor a{color:var(--primary-color);text-decoration:underline}
.se-rte-editor code{
  padding:2px 6px;border-radius:0;
  background:color-mix(in oklab, var(--muted-color) 14%, transparent);
  border:1px solid var(--line-soft);
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  font-size:12.5px;
}
.se-rte-editor pre{
  padding:10px 12px;border-radius:0;
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
  border:1px solid var(--line-soft);
  overflow:auto;margin:8px 0;
}
.se-rte-editor pre code{border:0;background:transparent;padding:0;display:block;white-space:pre;}
.se-rte-code{
  display:none;width:100%;
  min-height:210px;
  padding:12px 12px;
  border:0;outline:none;
  resize:vertical;
  background:transparent;
  color:var(--ink);
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  font-size:12.5px;line-height:1.45;
}
.se-rte-wrap.mode-code .se-rte-editor{display:none;}
.se-rte-wrap.mode-code .se-rte-code{display:block;}

/* Image preview box */
.preview-box{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:color-mix(in oklab, var(--surface) 88%, var(--bg-body));
}
.preview-box .top{
  display:flex;align-items:center;justify-content:space-between;
  gap:10px;
  padding:10px 12px;
  border-bottom:1px solid var(--line-soft);
}
.preview-box .body{padding:12px;}
.preview-box img{
  width:100%;
  max-height:220px;
  object-fit:cover;
  border-radius:12px;
  border:1px solid var(--line-soft);
  background:#fff;
}
.preview-meta{font-size:12.5px;color:var(--muted-color);margin-top:10px}

/* Social links */
.social-row{
  display:flex;gap:10px;align-items:center;
}
.social-row .form-control{min-width:220px}
.social-row .btn{border-radius:10px}
</style>
@endpush

@section('content')
<div class="se-wrap">

  {{-- Global Loading --}}
  <div id="seLoading" class="se-loading" style="display:none;">
    <div class="box">
      <div class="se-spinner"></div>
      <div class="small">Loading…</div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs se-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#se-tab-active" role="tab" aria-selected="true">
        <i class="fa-solid fa-seedling me-2"></i>Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#se-tab-inactive" role="tab" aria-selected="false">
        <i class="fa-solid fa-circle-pause me-2"></i>Inactive
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#se-tab-trash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Trash
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- ACTIVE --}}
    <div class="tab-pane fade show active" id="se-tab-active" role="tabpanel">

      {{-- Toolbar --}}
      <div class="se-toolbar panel mb-3">
        <div class="row g-2 align-items-center">
          <div class="col-12 col-lg">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <div class="d-flex align-items-center gap-2">
                <label class="text-muted small mb-0">Per Page</label>
                <select id="sePerPage" class="form-select" style="width:96px;">
                  <option>10</option>
                  <option selected>20</option>
                  <option>50</option>
                  <option>100</option>
                </select>
              </div>

              <div class="position-relative" style="min-width:280px;">
                <input id="seSearch" type="search" class="form-control ps-5" placeholder="Search by name, company, slug…">
                <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
              </div>

              <button id="seBtnFilter" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#seFilterModal">
                <i class="fa fa-sliders me-1"></i>Filter
              </button>

              <button id="seBtnReset" class="btn btn-light">
                <i class="fa fa-rotate-left me-1"></i>Reset
              </button>
            </div>
          </div>

          <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
            <div class="tool-actions" id="seWriteControls" style="display:none;">
              <button type="button" class="btn btn-primary" id="seBtnAdd">
                <i class="fa fa-plus me-1"></i> Add Entrepreneur
              </button>
            </div>
          </div>
        </div>
      </div>

      {{-- Table --}}
      <div class="card se-table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Person</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:220px;">Company</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:120px;">Featured</th>
                  <th style="width:150px;">Publish At</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:120px;">Views</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="seTbodyActive">
                <tr><td colspan="10" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="seEmptyActive" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa-solid fa-seedling mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No active entrepreneurs found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="seInfoActive">—</div>
            <nav><ul id="sePagerActive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- INACTIVE --}}
    <div class="tab-pane fade" id="se-tab-inactive" role="tabpanel">
      <div class="card se-table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Person</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:220px;">Company</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:120px;">Featured</th>
                  <th style="width:150px;">Publish At</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:120px;">Views</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="seTbodyInactive">
                <tr><td colspan="10" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="seEmptyInactive" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa fa-circle-pause mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No inactive entrepreneurs found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="seInfoInactive">—</div>
            <nav><ul id="sePagerInactive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- TRASH --}}
    <div class="tab-pane fade" id="se-tab-trash" role="tabpanel">
      <div class="card se-table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
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
              <tbody id="seTbodyTrash">
                <tr><td colspan="5" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="seEmptyTrash" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>Trash is empty.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="seInfoTrash">—</div>
            <nav><ul id="sePagerTrash" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="seFilterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-sliders me-2"></i>Filter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Featured</label>
            <select id="seF_featured" class="form-select">
              <option value="">Any</option>
              <option value="1">Featured only</option>
              <option value="0">Not featured</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="seF_sort" class="form-select">
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="-updated_at">Recently Updated</option>
              <option value="name">Name A-Z</option>
              <option value="-name">Name Z-A</option>
              <option value="sort_order">Sort Order ↑</option>
              <option value="-sort_order">Sort Order ↓</option>
              <option value="-publish_at">Publish At (Desc)</option>
              <option value="publish_at">Publish At (Asc)</option>
              <option value="-views_count">Views (High → Low)</option>
              <option value="views_count">Views (Low → High)</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Inactive bucket</label>
            <select id="seF_inactiveMode" class="form-select">
              <option value="draft+archived" selected>Draft + Archived</option>
              <option value="draft">Draft only</option>
              <option value="archived">Archived only</option>
            </select>
            <div class="form-text">Active tab always shows <b>Published</b>. This controls what appears in <b>Inactive</b>.</div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="seBtnApplyFilters" class="btn btn-primary">
          <i class="fa fa-check me-1"></i>Apply Filters
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Add/Edit/View Modal --}}
<div class="modal fade" id="seItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form class="modal-content" id="seItemForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="seItemModalTitle">Add Entrepreneur</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="seItemUuid">
        <input type="hidden" id="seItemId">

        <div class="row g-3">
          {{-- Left --}}
          <div class="col-lg-6">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input class="form-control" id="seName" required maxlength="120" placeholder="e.g., John Doe">
              </div>

              <div class="col-md-8">
                <label class="form-label">Slug (optional)</label>
                <input class="form-control" id="seSlug" maxlength="160" placeholder="john-doe">
                <div class="form-text">Auto-generated from name until you edit manually.</div>
              </div>

              <div class="col-md-4">
                <label class="form-label">Sort Order</label>
                <input type="number" class="form-control" id="seSortOrder" min="0" max="1000000" value="0">
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select" id="seStatus">
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="archived">Archived</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Featured on Home</label>
                <select class="form-select" id="seFeatured">
                  <option value="0">No</option>
                  <option value="1">Yes</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Publish At</label>
                <input type="datetime-local" class="form-control" id="sePublishAt">
              </div>

              <div class="col-md-6">
                <label class="form-label">Expire At</label>
                <input type="datetime-local" class="form-control" id="seExpireAt">
              </div>

              <div class="col-12">
                <label class="form-label">Title/Designation (optional)</label>
                <input class="form-control" id="seTitle" maxlength="255" placeholder="e.g., Founder & CEO">
              </div>

              <div class="col-12">
                <label class="form-label">Company Name</label>
                <input class="form-control" id="seCompanyName" maxlength="255" placeholder="e.g., Acme Ventures">
              </div>

              <div class="col-md-6">
                <label class="form-label">Industry</label>
                <input class="form-control" id="seIndustry" maxlength="120" placeholder="e.g., FinTech">
              </div>

              <div class="col-md-6">
                <label class="form-label">Founded Year</label>
                <input type="number" class="form-control" id="seFoundedYear" min="1800" max="2500" placeholder="e.g., 2020">
              </div>

              <div class="col-md-6">
                <label class="form-label">Achievement Date</label>
                <input type="date" class="form-control" id="seAchievementDate">
              </div>

              <div class="col-md-6">
                <label class="form-label">Company Website</label>
                <input class="form-control" id="seCompanyWebsite" maxlength="255" placeholder="https://example.com">
              </div>

              <div class="col-12">
                <label class="form-label">Highlights (optional)</label>
                <textarea class="form-control" id="seHighlights" rows="3" placeholder="Short key achievements…"></textarea>
              </div>

              <div class="col-12">
                <label class="form-label d-flex align-items-center justify-content-between">
                  <span>Social Links (optional)</span>
                  <button type="button" class="btn btn-light btn-sm" id="seAddSocial">
                    <i class="fa fa-plus me-1"></i>Add link
                  </button>
                </label>
                <div id="seSocialWrap" class="d-flex flex-column gap-2"></div>
                <div class="form-text">Stored as JSON array (label + url).</div>
              </div>

            </div>
          </div>

          {{-- Right --}}
          <div class="col-lg-6">
            {{-- Description RTE --}}
            <div class="mb-3">
              <label class="form-label">Description (HTML allowed) <span class="text-danger">*</span></label>

              <div class="se-rte-wrap" id="seDescWrap">
                <div class="se-rte-toolbar">
                  <button type="button" class="se-rte-btn" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
                  <button type="button" class="se-rte-btn" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
                  <button type="button" class="se-rte-btn" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>

                  <span class="se-rte-sep"></span>

                  <button type="button" class="se-rte-btn" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
                  <button type="button" class="se-rte-btn" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>

                  <span class="se-rte-sep"></span>

                  <button type="button" class="se-rte-btn" data-insert="pre" title="Code Block"><i class="fa fa-code"></i></button>
                  <button type="button" class="se-rte-btn" data-insert="code" title="Inline Code"><i class="fa fa-terminal"></i></button>

                  <span class="se-rte-sep"></span>

                  <button type="button" class="se-rte-btn" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>

                  <div class="se-rte-tabs">
                    <button type="button" class="tab active" data-mode="text">Text</button>
                    <button type="button" class="tab" data-mode="code">Code</button>
                  </div>
                </div>

                <div class="se-rte-area">
                  <div id="seDescEditor" class="se-rte-editor" contenteditable="true" data-placeholder="Write entrepreneur story…"></div>
                  <textarea id="seDescCode" class="se-rte-code" spellcheck="false" autocomplete="off" autocapitalize="off" autocorrect="off"
                    placeholder="HTML code…"></textarea>
                </div>
              </div>

              <div class="se-rte-help">Use <b>Text</b> for rich editing or switch to <b>Code</b> to paste HTML.</div>
              <input type="hidden" id="seDescription">
            </div>

            {{-- Photo Upload + Preview --}}
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Photo (optional)</label>
                <div class="d-flex gap-2 flex-wrap">
                  <input type="file" class="form-control" id="sePhoto" accept="image/*">
                  <button type="button" class="btn btn-outline-danger" id="sePhotoRemove" style="display:none;">
                    <i class="fa fa-trash me-1"></i>Remove Photo
                  </button>
                </div>
              </div>

              <div class="col-12">
                <div class="preview-box">
                  <div class="top">
                    <div class="fw-semibold"><i class="fa fa-image me-2"></i>Photo Preview</div>
                    <button type="button" class="btn btn-light btn-sm" id="sePhotoOpen" style="display:none;">
                      <i class="fa fa-up-right-from-square me-1"></i>Open
                    </button>
                  </div>
                  <div class="body">
                    <img id="sePhotoPreview" src="" alt="Photo preview" style="display:none;">
                    <div id="sePhotoEmpty" class="text-muted small" style="padding:12px;border:1px dashed var(--line-soft);border-radius:12px;">
                      No photo selected.
                    </div>
                    <div class="preview-meta" id="sePhotoMeta" style="display:none;">—</div>
                  </div>
                </div>
              </div>

              {{-- Company Logo Upload + Preview --}}
              <div class="col-12">
                <label class="form-label">Company Logo (optional)</label>
                <div class="d-flex gap-2 flex-wrap">
                  <input type="file" class="form-control" id="seLogo" accept="image/*">
                  <button type="button" class="btn btn-outline-danger" id="seLogoRemove" style="display:none;">
                    <i class="fa fa-trash me-1"></i>Remove Logo
                  </button>
                </div>
              </div>

              <div class="col-12">
                <div class="preview-box">
                  <div class="top">
                    <div class="fw-semibold"><i class="fa fa-building me-2"></i>Logo Preview</div>
                    <button type="button" class="btn btn-light btn-sm" id="seLogoOpen" style="display:none;">
                      <i class="fa fa-up-right-from-square me-1"></i>Open
                    </button>
                  </div>
                  <div class="body">
                    <img id="seLogoPreview" src="" alt="Logo preview" style="display:none;">
                    <div id="seLogoEmpty" class="text-muted small" style="padding:12px;border:1px dashed var(--line-soft);border-radius:12px;">
                      No logo selected.
                    </div>
                    <div class="preview-meta" id="seLogoMeta" style="display:none;">—</div>
                  </div>
                </div>
              </div>

            </div>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="seSaveBtn">
          <i class="fa fa-floppy-disk me-1"></i> Save
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="seToastOk" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="seToastOkText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="seToastErr" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="seToastErrText">Something went wrong</div>
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
  if (window.__SUCCESSFUL_ENTREPRENEURS_INIT__) return;
  window.__SUCCESSFUL_ENTREPRENEURS_INIT__ = true;

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

  function toLocalDateTime(s){
    if (!s) return '';
    const t = String(s).replace(' ', 'T');
    return t.length >= 16 ? t.slice(0,16) : t;
  }

  function toLocalDate(s){
    if (!s) return '';
    const t = String(s).slice(0,10);
    return /^\d{4}-\d{2}-\d{2}$/.test(t) ? t : '';
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

    const loading = $('seLoading');
    const showLoading = (v) => { if (loading) loading.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('seToastOk');
    const toastErrEl = $('seToastErr');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { $('seToastOkText').textContent = m || 'Done'; toastOk && toastOk.show(); };
    const err = (m) => { $('seToastErrText').textContent = m || 'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = () => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    });

    // Controls
    const perPageSel = $('sePerPage');
    const searchInput = $('seSearch');
    const btnReset = $('seBtnReset');
    const btnApplyFilters = $('seBtnApplyFilters');
    const writeControls = $('seWriteControls');
    const btnAdd = $('seBtnAdd');

    // Filter modal fields
    const fFeatured = $('seF_featured');
    const fSort = $('seF_sort');
    const fInactiveMode = $('seF_inactiveMode');

    // Table bodies
    const tbodyActive = $('seTbodyActive');
    const tbodyInactive = $('seTbodyInactive');
    const tbodyTrash = $('seTbodyTrash');

    // Empty states
    const emptyActive = $('seEmptyActive');
    const emptyInactive = $('seEmptyInactive');
    const emptyTrash = $('seEmptyTrash');

    // Pagers
    const pagerActive = $('sePagerActive');
    const pagerInactive = $('sePagerInactive');
    const pagerTrash = $('sePagerTrash');

    // Info
    const infoActive = $('seInfoActive');
    const infoInactive = $('seInfoInactive');
    const infoTrash = $('seInfoTrash');

    // Modal
    const itemModalEl = $('seItemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;
    const itemModalTitle = $('seItemModalTitle');
    const itemForm = $('seItemForm');
    const saveBtn = $('seSaveBtn');

    // Form fields
    const itemUuid = $('seItemUuid');
    const itemId = $('seItemId');

    const nameInput = $('seName');
    const slugInput = $('seSlug');
    const sortOrderInput = $('seSortOrder');
    const statusSel = $('seStatus');
    const featuredSel = $('seFeatured');
    const publishAtInput = $('sePublishAt');
    const expireAtInput = $('seExpireAt');

    const titleInput = $('seTitle');
    const companyNameInput = $('seCompanyName');
    const industryInput = $('seIndustry');
    const foundedYearInput = $('seFoundedYear');
    const achievementDateInput = $('seAchievementDate');
    const companyWebsiteInput = $('seCompanyWebsite');
    const highlightsInput = $('seHighlights');

    const photoInput = $('sePhoto');
    const logoInput = $('seLogo');

    const photoRemoveBtn = $('sePhotoRemove');
    const logoRemoveBtn = $('seLogoRemove');

    const photoPreview = $('sePhotoPreview');
    const photoEmpty = $('sePhotoEmpty');
    const photoMeta = $('sePhotoMeta');
    const photoOpen = $('sePhotoOpen');

    const logoPreview = $('seLogoPreview');
    const logoEmpty = $('seLogoEmpty');
    const logoMeta = $('seLogoMeta');
    const logoOpen = $('seLogoOpen');

    const addSocialBtn = $('seAddSocial');
    const socialWrap = $('seSocialWrap');

    // RTE (Description)
    const rte = {
      wrap: $('seDescWrap'),
      editor: $('seDescEditor'),
      code: $('seDescCode'),
      hidden: $('seDescription'),
      mode: 'text',
      enabled: true
    };

    /* =========================
     * Permissions (same role logic)
     * ========================= */
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

    /* =========================
     * State
     * ========================= */
    const state = {
      filters: {
        q: '',
        featured: '',
        sort: '-created_at',
        inactiveMode: 'draft+archived',
      },
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      tabs: {
        active: { page:1, lastPage:1, items:[] },
        inactive: { page:1, lastPage:1, items:[], all:[] }, // client-paged
        trash:  { page:1, lastPage:1, items:[] }
      }
    };

    const getTabKey = () => {
      const a = document.querySelector('.se-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#se-tab-active';
      if (href === '#se-tab-inactive') return 'inactive';
      if (href === '#se-tab-trash') return 'trash';
      return 'active';
    };

    function setBtnLoading(btn, loading){
      if (!btn) return;
      btn.disabled = !!loading;
      btn.classList.toggle('btn-loading', !!loading);
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

    function setEmpty(tabKey, show){
      const el = tabKey==='active' ? emptyActive : (tabKey==='inactive' ? emptyInactive : emptyTrash);
      if (el) el.style.display = show ? '' : 'none';
    }

    function buildUrl(tabKey){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state.tabs[tabKey].page));

      const q = (state.filters.q || '').trim();
      if (q) params.set('q', q);

      // sort / direction from one select value like "-created_at"
      const s = state.filters.sort || '-created_at';
      params.set('sort', s.startsWith('-') ? s.slice(1) : s);
      params.set('direction', s.startsWith('-') ? 'desc' : 'asc');

      if (state.filters.featured !== '') params.set('featured', state.filters.featured);

      // tab constraints
      if (tabKey === 'active') params.set('status', 'published');
      if (tabKey === 'trash') params.set('only_trashed', '1');

      return `/api/successful-entrepreneurs?${params.toString()}`;
    }

    function personCell(r){
      const photo = r.photo_full_url || r.photo_url || '';
      const name = r.name || '—';
      const sub = (r.title || r.company_name || r.industry || '').toString().trim();
      const avatar = photo
        ? `<img src="${esc(normalizeUrl(photo))}" alt="photo">`
        : `<i class="fa fa-user" style="opacity:.55;"></i>`;

      return `
        <div class="person">
          <div class="avatar">${avatar}</div>
          <div class="meta">
            <div class="name">${esc(name)}</div>
            <div class="sub">${esc(sub || '—')}</div>
          </div>
        </div>
      `;
    }

    function renderPagerServer(tabKey){
      const pagerEl = tabKey === 'active' ? pagerActive : (tabKey === 'trash' ? pagerTrash : null);
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

    function renderPagerClientInactive(){
      if (!pagerInactive) return;

      const st = state.tabs.inactive;
      const page = st.page;
      const totalPages = st.lastPage || 1;

      const item = (p, label, dis=false, act=false) => {
        if (dis) return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
        if (act) return `<li class="page-item active"><span class="page-link">${label}</span></li>`;
        return `<li class="page-item"><a class="page-link" href="#" data-page="${p}" data-tab="inactive">${label}</a></li>`;
      };

      let html = '';
      html += item(Math.max(1, page-1), 'Previous', page<=1);
      const start = Math.max(1, page-2), end = Math.min(totalPages, page+2);
      for (let p=start; p<=end; p++) html += item(p, p, false, p===page);
      html += item(Math.min(totalPages, page+1), 'Next', page>=totalPages);

      pagerInactive.innerHTML = html;
    }

    function sortKeyValue(r, key){
      const v = r?.[key];
      if (v === null || v === undefined) return '';
      return typeof v === 'number' ? v : String(v).toLowerCase();
    }

    function sortArray(items){
      const s = state.filters.sort || '-created_at';
      const dir = s.startsWith('-') ? -1 : 1;
      const key = s.startsWith('-') ? s.slice(1) : s;

      const isNumeric = ['views_count','sort_order','id','founded_year'].includes(key);

      const out = [...items].sort((a,b) => {
        const av = sortKeyValue(a, key);
        const bv = sortKeyValue(b, key);
        if (isNumeric){
          const an = Number(av||0), bn = Number(bv||0);
          return (an - bn) * dir;
        }
        if (av < bv) return -1 * dir;
        if (av > bv) return  1 * dir;
        return 0;
      });

      return out;
    }

    function renderTable(tabKey){
      if (tabKey === 'active'){
        const rows = state.tabs.active.items || [];
        if (!tbodyActive) return;

        if (!rows.length){
          tbodyActive.innerHTML = '';
          setEmpty('active', true);
          renderPagerServer('active');
          return;
        }
        setEmpty('active', false);

        tbodyActive.innerHTML = rows.map(r => {
          const uuid = r.uuid || '';
          const slug = r.slug || '—';
          const company = (r.company_name || '—');
          const status = r.status || '—';
          const featured = !!(r.is_featured_home ?? 0);
          const publishAt = r.publish_at || '—';
          const sortOrder = (r.sort_order ?? 0);
          const views = (r.views_count ?? 0);
          const updated = r.updated_at || '—';

          // ✅ FIX: use manual dropdown toggle like reference page (no data-bs-toggle)
          const actions = `
            <div class="dropdown text-end">
              <button type="button" class="btn btn-light btn-sm se-dd-toggle"
                aria-expanded="false" title="Actions">
                <i class="fa fa-ellipsis-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>
                ${canEdit ? `<li><button type="button" class="dropdown-item" data-action="edit"><i class="fa fa-pen-to-square"></i> Edit</button></li>` : ``}
                <li><button type="button" class="dropdown-item" data-action="toggleFeatured"><i class="fa fa-star"></i> Toggle Featured</button></li>
                ${canDelete ? `<li><hr class="dropdown-divider"></li>
                  <li><button type="button" class="dropdown-item text-danger" data-action="delete"><i class="fa fa-trash"></i> Delete</button></li>` : ``}
              </ul>
            </div>`;

          return `
            <tr data-uuid="${esc(uuid)}">
              <td>${personCell(r)}</td>
              <td class="col-slug"><code>${esc(slug)}</code></td>
              <td>${esc(company)}</td>
              <td>${statusBadge(status)}</td>
              <td>${featuredBadge(featured)}</td>
              <td>${esc(String(publishAt))}</td>
              <td>${esc(String(sortOrder))}</td>
              <td>${esc(String(views))}</td>
              <td>${esc(String(updated))}</td>
              <td class="text-end">${actions}</td>
            </tr>
          `;
        }).join('');

        renderPagerServer('active');
        return;
      }

      if (tabKey === 'inactive'){
        const page = state.tabs.inactive.page;
        const per = state.perPage;
        const all = state.tabs.inactive.all || [];
        const total = all.length;

        const totalPages = Math.max(1, Math.ceil(total / per));
        state.tabs.inactive.lastPage = totalPages;
        if (page > totalPages) state.tabs.inactive.page = 1;

        const start = (state.tabs.inactive.page - 1) * per;
        const slice = all.slice(start, start + per);
        state.tabs.inactive.items = slice;

        if (!tbodyInactive) return;

        if (!slice.length){
          tbodyInactive.innerHTML = '';
          setEmpty('inactive', true);
          renderPagerClientInactive();
          return;
        }
        setEmpty('inactive', false);

        tbodyInactive.innerHTML = slice.map(r => {
          const uuid = r.uuid || '';
          const slug = r.slug || '—';
          const company = (r.company_name || '—');
          const status = r.status || '—';
          const featured = !!(r.is_featured_home ?? 0);
          const publishAt = r.publish_at || '—';
          const sortOrder = (r.sort_order ?? 0);
          const views = (r.views_count ?? 0);
          const updated = r.updated_at || '—';

          const actions = `
            <div class="dropdown text-end">
              <button type="button" class="btn btn-light btn-sm se-dd-toggle"
                aria-expanded="false" title="Actions">
                <i class="fa fa-ellipsis-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>
                ${canEdit ? `<li><button type="button" class="dropdown-item" data-action="edit"><i class="fa fa-pen-to-square"></i> Edit</button></li>` : ``}
                <li><button type="button" class="dropdown-item" data-action="toggleFeatured"><i class="fa fa-star"></i> Toggle Featured</button></li>
                ${canDelete ? `<li><hr class="dropdown-divider"></li>
                  <li><button type="button" class="dropdown-item text-danger" data-action="delete"><i class="fa fa-trash"></i> Delete</button></li>` : ``}
              </ul>
            </div>`;

          return `
            <tr data-uuid="${esc(uuid)}">
              <td>${personCell(r)}</td>
              <td class="col-slug"><code>${esc(slug)}</code></td>
              <td>${esc(company)}</td>
              <td>${statusBadge(status)}</td>
              <td>${featuredBadge(featured)}</td>
              <td>${esc(String(publishAt))}</td>
              <td>${esc(String(sortOrder))}</td>
              <td>${esc(String(views))}</td>
              <td>${esc(String(updated))}</td>
              <td class="text-end">${actions}</td>
            </tr>
          `;
        }).join('');

        renderPagerClientInactive();
        return;
      }

      if (tabKey === 'trash'){
        const rows = state.tabs.trash.items || [];
        if (!tbodyTrash) return;

        if (!rows.length){
          tbodyTrash.innerHTML = '';
          setEmpty('trash', true);
          renderPagerServer('trash');
          return;
        }
        setEmpty('trash', false);

        tbodyTrash.innerHTML = rows.map(r => {
          const uuid = r.uuid || '';
          const slug = r.slug || '—';
          const deleted = r.deleted_at || '—';
          const sortOrder = (r.sort_order ?? 0);

          const actions = `
            <div class="dropdown text-end">
              <button type="button" class="btn btn-light btn-sm se-dd-toggle"
                aria-expanded="false" title="Actions">
                <i class="fa fa-ellipsis-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><button type="button" class="dropdown-item" data-action="restore"><i class="fa fa-rotate-left"></i> Restore</button></li>
                ${canDelete ? `<li><button type="button" class="dropdown-item text-danger" data-action="force"><i class="fa fa-skull-crossbones"></i> Delete Permanently</button></li>` : ``}
              </ul>
            </div>`;

          return `
            <tr data-uuid="${esc(uuid)}">
              <td>${personCell(r)}</td>
              <td class="col-slug"><code>${esc(slug)}</code></td>
              <td>${esc(String(deleted))}</td>
              <td>${esc(String(sortOrder))}</td>
              <td class="text-end">${actions}</td>
            </tr>
          `;
        }).join('');

        renderPagerServer('trash');
      }
    }

    async function loadActive(){
      tbodyActive.innerHTML = `<tr><td colspan="10" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;
      try{
        const res = await fetchWithTimeout(buildUrl('active'), { headers: authHeaders() }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href='/'; return; }
        const js = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(js?.message || 'Failed to load');

        const items = Array.isArray(js.data) ? js.data : [];
        const p = js.pagination || {};
        state.tabs.active.items = items;
        state.tabs.active.lastPage = parseInt(p.last_page || 1, 10) || 1;
        if (infoActive) infoActive.textContent = (p.total ? `${p.total} result(s)` : '—');

        renderTable('active');
      }catch(ex){
        state.tabs.active.items = [];
        state.tabs.active.lastPage = 1;
        renderTable('active');
        err(ex?.name==='AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }
    }

    async function loadTrash(){
      tbodyTrash.innerHTML = `<tr><td colspan="5" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;
      try{
        const res = await fetchWithTimeout(buildUrl('trash'), { headers: authHeaders() }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href='/'; return; }
        const js = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(js?.message || 'Failed to load');

        const items = Array.isArray(js.data) ? js.data : [];
        const p = js.pagination || {};
        state.tabs.trash.items = items;
        state.tabs.trash.lastPage = parseInt(p.last_page || 1, 10) || 1;
        if (infoTrash) infoTrash.textContent = (p.total ? `${p.total} result(s)` : '—');

        renderTable('trash');
      }catch(ex){
        state.tabs.trash.items = [];
        state.tabs.trash.lastPage = 1;
        renderTable('trash');
        err(ex?.name==='AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }
    }

    // Inactive: fetch (draft and/or archived) and client-page them
    async function loadInactive(){
      tbodyInactive.innerHTML = `<tr><td colspan="10" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;
      try{
        const q = (state.filters.q || '').trim();
        const mode = state.filters.inactiveMode || 'draft+archived';

        const s = state.filters.sort || '-created_at';
        const sort = s.startsWith('-') ? s.slice(1) : s;
        const direction = s.startsWith('-') ? 'desc' : 'asc';

        const base = new URLSearchParams();
        base.set('per_page', '200'); // enough for admin lists; pagination handled client-side for inactive
        base.set('page', '1');
        base.set('sort', sort);
        base.set('direction', direction);
        if (q) base.set('q', q);
        if (state.filters.featured !== '') base.set('featured', state.filters.featured);

        const wantsDraft = (mode === 'draft' || mode === 'draft+archived');
        const wantsArchived = (mode === 'archived' || mode === 'draft+archived');

        const reqs = [];
        if (wantsDraft){
          const p = new URLSearchParams(base.toString());
          p.set('status', 'draft');
          reqs.push(fetchWithTimeout(`/api/successful-entrepreneurs?${p.toString()}`, { headers: authHeaders() }, 15000));
        }
        if (wantsArchived){
          const p = new URLSearchParams(base.toString());
          p.set('status', 'archived');
          reqs.push(fetchWithTimeout(`/api/successful-entrepreneurs?${p.toString()}`, { headers: authHeaders() }, 15000));
        }

        const responses = await Promise.all(reqs);
        for (const r of responses){
          if (r.status === 401 || r.status === 403) { window.location.href='/'; return; }
        }

        const payloads = await Promise.all(responses.map(r => r.json().catch(()=> ({}))));
        responses.forEach((r, i) => { if (!r.ok) throw new Error(payloads[i]?.message || 'Failed to load'); });

        const allItems = payloads.flatMap(p => Array.isArray(p.data) ? p.data : []);
        const uniq = new Map();
        for (const it of allItems){
          if (it?.uuid) uniq.set(it.uuid, it);
        }

        const merged = Array.from(uniq.values());
        const sorted = sortArray(merged);

        state.tabs.inactive.all = sorted;
        state.tabs.inactive.page = 1;

        if (infoInactive) infoInactive.textContent = `${sorted.length} result(s)`;
        renderTable('inactive');
      }catch(ex){
        state.tabs.inactive.all = [];
        state.tabs.inactive.items = [];
        state.tabs.inactive.page = 1;
        state.tabs.inactive.lastPage = 1;
        renderTable('inactive');
        err(ex?.name==='AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }
    }

    function reloadCurrent(){
      const tab = getTabKey();
      if (tab === 'active') return loadActive();
      if (tab === 'inactive') return loadInactive();
      return loadTrash();
    }

    /* =========================
     * Pagers (click)
     * ========================= */
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a.page-link[data-page]');
      if (!a) return;
      e.preventDefault();

      const tab = a.dataset.tab;
      const p = parseInt(a.dataset.page, 10);
      if (!tab || Number.isNaN(p)) return;

      if (tab === 'inactive'){
        if (p === state.tabs.inactive.page) return;
        state.tabs.inactive.page = p;
        renderTable('inactive');
      } else {
        if (p === state.tabs[tab].page) return;
        state.tabs[tab].page = p;
        (tab === 'active') ? loadActive() : loadTrash();
      }

      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    /* =========================
     * Filters
     * ========================= */
    searchInput?.addEventListener('input', debounce(() => {
      state.filters.q = (searchInput.value || '').trim();
      state.tabs.active.page = 1;
      state.tabs.trash.page = 1;
      state.tabs.inactive.page = 1;
      reloadCurrent();
    }, 320));

    perPageSel?.addEventListener('change', () => {
      state.perPage = parseInt(perPageSel.value, 10) || 20;
      state.tabs.active.page = 1;
      state.tabs.trash.page = 1;
      state.tabs.inactive.page = 1;
      reloadCurrent();
    });

    $('seFilterModal')?.addEventListener('show.bs.modal', () => {
      fFeatured.value = (state.filters.featured ?? '');
      fSort.value = (state.filters.sort ?? '-created_at');
      fInactiveMode.value = (state.filters.inactiveMode ?? 'draft+archived');
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.featured = (fFeatured.value ?? '');
      state.filters.sort = (fSort.value ?? '-created_at');
      state.filters.inactiveMode = (fInactiveMode.value ?? 'draft+archived');

      state.tabs.active.page = 1;
      state.tabs.trash.page = 1;
      state.tabs.inactive.page = 1;

      bootstrap.Modal.getInstance($('seFilterModal'))?.hide();
      reloadCurrent();
    });

    btnReset?.addEventListener('click', () => {
      state.filters = { q:'', featured:'', sort:'-created_at', inactiveMode:'draft+archived' };
      state.perPage = 20;
      if (searchInput) searchInput.value = '';
      if (perPageSel) perPageSel.value = '20';

      state.tabs.active.page = 1;
      state.tabs.trash.page = 1;
      state.tabs.inactive.page = 1;

      reloadCurrent();
    });

    document.querySelector('a[href="#se-tab-active"]')?.addEventListener('shown.bs.tab', () => loadActive());
    document.querySelector('a[href="#se-tab-inactive"]')?.addEventListener('shown.bs.tab', () => loadInactive());
    document.querySelector('a[href="#se-tab-trash"]')?.addEventListener('shown.bs.tab', () => loadTrash());

    /* =========================
     * ✅ ACTION DROPDOWN FIX (from reference page behavior)
     * - Manual toggling + Popper fixed strategy to avoid overflow clipping
     * ========================= */
    function closeAllDropdownsExcept(exceptToggle){
      document.querySelectorAll('.se-dd-toggle').forEach(t => {
        if (t === exceptToggle) return;
        try{
          const inst = bootstrap.Dropdown.getInstance(t);
          inst && inst.hide();
        }catch(_){}
      });
    }

    // toggle click (manual)
    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('.se-dd-toggle');
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

    // click anywhere else closes open dropdowns
    document.addEventListener('click', () => {
      closeAllDropdownsExcept(null);
    }, { capture:true });

    /* =========================
     * Social links UI
     * ========================= */
    function socialRow(label='', url=''){
      const id = 'sl_' + Math.random().toString(16).slice(2);
      const row = document.createElement('div');
      row.className = 'social-row';
      row.dataset.rowId = id;
      row.innerHTML = `
        <input class="form-control" data-field="label" maxlength="60" placeholder="Label (e.g., LinkedIn)" value="${esc(label)}">
        <input class="form-control" data-field="url" maxlength="255" placeholder="URL" value="${esc(url)}">
        <button type="button" class="btn btn-light" data-action="removeSocial"><i class="fa fa-xmark"></i></button>
      `;
      return row;
    }

    addSocialBtn?.addEventListener('click', () => {
      if (!socialWrap) return;
      socialWrap.appendChild(socialRow());
    });

    socialWrap?.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-action="removeSocial"]');
      if (!btn) return;
      const row = btn.closest('.social-row');
      row && row.remove();
    });

    function readSocialLinks(){
      const out = [];
      socialWrap?.querySelectorAll('.social-row').forEach(row => {
        const label = (row.querySelector('[data-field="label"]')?.value || '').trim();
        const url = (row.querySelector('[data-field="url"]')?.value || '').trim();
        if (label || url) out.push({ label, url });
      });
      return out;
    }

    function setSocialLinks(arr){
      if (!socialWrap) return;
      socialWrap.innerHTML = '';
      (Array.isArray(arr) ? arr : []).forEach(it => {
        socialWrap.appendChild(socialRow(it?.label || '', it?.url || ''));
      });
    }

    /* =========================
     * RTE (Description) - cursor safe
     * ========================= */
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

    function placeCaretAfter(marker){
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
      const markerId = 'se_caret_' + Math.random().toString(16).slice(2);
      document.execCommand('insertHTML', false, html + `<span id="${markerId}">\u200b</span>`);
      const marker = document.getElementById(markerId);
      if (marker) placeCaretAfter(marker);
    }

    function syncRteToCode(){
      if (!rte.editor || !rte.code) return;
      if (rte.mode === 'text') rte.code.value = ensurePreHasCode(rte.editor.innerHTML || '');
    }

    function setRteMode(mode){
      rte.mode = (mode === 'code') ? 'code' : 'text';
      rte.wrap?.classList.toggle('mode-code', rte.mode === 'code');

      rte.wrap?.querySelectorAll('.se-rte-tabs .tab').forEach(t => {
        t.classList.toggle('active', t.dataset.mode === rte.mode);
      });

      const disable = (rte.mode === 'code') || !rte.enabled;
      rte.wrap?.querySelectorAll('.se-rte-toolbar .se-rte-btn').forEach(b => {
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
      if (rte.mode !== 'text') return;
      const set = (cmd, on) => {
        const b = rte.wrap?.querySelector(`.se-rte-btn[data-cmd="${cmd}"]`);
        if (b) b.classList.toggle('active', !!on);
      };
      try{
        set('bold', document.queryCommandState('bold'));
        set('italic', document.queryCommandState('italic'));
        set('underline', document.queryCommandState('underline'));
      }catch(_){}
    }

    rte.wrap?.querySelector('.se-rte-toolbar')?.addEventListener('pointerdown', (e) => { e.preventDefault(); });

    rte.editor?.addEventListener('input', () => { syncRteToCode(); updateToolbarActive(); });
    ['mouseup','keyup','click'].forEach(ev => rte.editor?.addEventListener(ev, updateToolbarActive));
    document.addEventListener('selectionchange', () => {
      if (document.activeElement === rte.editor) updateToolbarActive();
    });

    document.addEventListener('click', (e) => {
      const tab = e.target.closest('#seDescWrap .se-rte-tabs .tab');
      if (tab){ setRteMode(tab.dataset.mode); return; }

      const btn = e.target.closest('#seDescWrap .se-rte-toolbar .se-rte-btn');
      if (!btn || rte.mode !== 'text' || !rte.enabled) return;

      rteFocus();

      const insert = btn.getAttribute('data-insert');
      const cmd = btn.getAttribute('data-cmd');

      if (insert === 'code'){
        const sel = window.getSelection();
        const hasSel = sel && sel.rangeCount && !sel.isCollapsed && sel.toString().trim();
        if (hasSel){
          document.execCommand('insertHTML', false, `<code>${esc(sel.toString())}</code>`);
        } else {
          insertHtmlWithCaret('<code></code>');
        }
        syncRteToCode(); updateToolbarActive();
        return;
      }

      if (insert === 'pre'){
        const sel = window.getSelection();
        const hasSel = sel && sel.rangeCount && !sel.isCollapsed && sel.toString().trim();
        if (hasSel){
          document.execCommand('insertHTML', false, `<pre><code>${esc(sel.toString())}</code></pre>`);
        } else {
          insertHtmlWithCaret('<pre><code></code></pre>');
        }
        syncRteToCode(); updateToolbarActive();
        return;
      }

      if (cmd){
        try{ document.execCommand(cmd, false, null); }catch(_){}
        syncRteToCode(); updateToolbarActive();
      }
    });

    function setRteEnabled(on){
      rte.enabled = !!on;
      rte.editor?.setAttribute('contenteditable', on ? 'true' : 'false');
      if (rte.code) rte.code.disabled = !on;

      const disable = (rte.mode === 'code') || !rte.enabled;
      rte.wrap?.querySelectorAll('.se-rte-toolbar .se-rte-btn').forEach(b => {
        b.disabled = disable;
        b.style.opacity = disable ? '0.55' : '';
        b.style.pointerEvents = disable ? 'none' : '';
      });

      rte.wrap?.querySelectorAll('.se-rte-tabs .tab').forEach(t => {
        t.style.pointerEvents = on ? '' : 'none';
        t.style.opacity = on ? '' : '0.7';
      });
    }

    /* =========================
     * Preview helpers (photo/logo)
     * ========================= */
    let photoObjUrl = null, logoObjUrl = null;
    let markPhotoRemove = false, markLogoRemove = false;

    function clearPreview(kind, revoke=true){
      if (kind === 'photo'){
        if (revoke && photoObjUrl){ try{ URL.revokeObjectURL(photoObjUrl); }catch(_){ } }
        photoObjUrl = null;
        if (photoPreview){ photoPreview.style.display='none'; photoPreview.removeAttribute('src'); }
        photoEmpty && (photoEmpty.style.display='');
        photoMeta && (photoMeta.style.display='none');
        photoOpen && (photoOpen.style.display='none');
        return;
      }
      if (kind === 'logo'){
        if (revoke && logoObjUrl){ try{ URL.revokeObjectURL(logoObjUrl); }catch(_){ } }
        logoObjUrl = null;
        if (logoPreview){ logoPreview.style.display='none'; logoPreview.removeAttribute('src'); }
        logoEmpty && (logoEmpty.style.display='');
        logoMeta && (logoMeta.style.display='none');
        logoOpen && (logoOpen.style.display='none');
      }
    }

    function setPreview(kind, url, metaText=''){
      const u = normalizeUrl(url);
      if (!u){ clearPreview(kind, true); return; }

      if (kind === 'photo'){
        photoEmpty && (photoEmpty.style.display='none');
        if (photoPreview){ photoPreview.style.display=''; photoPreview.src=u; }
        if (photoMeta){ photoMeta.style.display = metaText ? '' : 'none'; photoMeta.textContent = metaText || ''; }
        if (photoOpen){
          photoOpen.style.display='';
          photoOpen.onclick = () => window.open(u, '_blank', 'noopener');
        }
      } else {
        logoEmpty && (logoEmpty.style.display='none');
        if (logoPreview){ logoPreview.style.display=''; logoPreview.src=u; }
        if (logoMeta){ logoMeta.style.display = metaText ? '' : 'none'; logoMeta.textContent = metaText || ''; }
        if (logoOpen){
          logoOpen.style.display='';
          logoOpen.onclick = () => window.open(u, '_blank', 'noopener');
        }
      }
    }

    photoInput?.addEventListener('change', () => {
      const f = photoInput.files?.[0];
      if (!f){ return; }
      markPhotoRemove = false;
      if (photoObjUrl){ try{ URL.revokeObjectURL(photoObjUrl); }catch(_){ } }
      photoObjUrl = URL.createObjectURL(f);
      setPreview('photo', photoObjUrl, `${f.name || 'photo'} • ${bytes(f.size)}`);
      photoRemoveBtn && (photoRemoveBtn.style.display='');
    });

    logoInput?.addEventListener('change', () => {
      const f = logoInput.files?.[0];
      if (!f){ return; }
      markLogoRemove = false;
      if (logoObjUrl){ try{ URL.revokeObjectURL(logoObjUrl); }catch(_){ } }
      logoObjUrl = URL.createObjectURL(f);
      setPreview('logo', logoObjUrl, `${f.name || 'logo'} • ${bytes(f.size)}`);
      logoRemoveBtn && (logoRemoveBtn.style.display='');
    });

    photoRemoveBtn?.addEventListener('click', () => {
      markPhotoRemove = true;
      if (photoInput) photoInput.value = '';
      clearPreview('photo', true);
      photoRemoveBtn.style.display='none';
    });

    logoRemoveBtn?.addEventListener('click', () => {
      markLogoRemove = true;
      if (logoInput) logoInput.value = '';
      clearPreview('logo', true);
      logoRemoveBtn.style.display='none';
    });

    itemModalEl?.addEventListener('hidden.bs.modal', () => {
      if (photoObjUrl){ try{ URL.revokeObjectURL(photoObjUrl); }catch(_){ } photoObjUrl=null; }
      if (logoObjUrl){ try{ URL.revokeObjectURL(logoObjUrl); }catch(_){ } logoObjUrl=null; }
    });

    /* =========================
     * Modal / Form
     * ========================= */
    let saving = false;
    let slugDirty = false;
    let settingSlug = false;

    function resetForm(){
      itemForm?.reset();
      itemUuid.value = '';
      itemId.value = '';

      slugDirty = false;
      settingSlug = false;

      // social
      setSocialLinks([]);

      // RTE
      if (rte.editor) rte.editor.innerHTML = '';
      if (rte.code) rte.code.value = '';
      if (rte.hidden) rte.hidden.value = '';
      setRteMode('text');
      setRteEnabled(true);

      // previews
      markPhotoRemove = false;
      markLogoRemove = false;
      clearPreview('photo', true);
      clearPreview('logo', true);
      photoRemoveBtn && (photoRemoveBtn.style.display='none');
      logoRemoveBtn && (logoRemoveBtn.style.display='none');

      // enable fields
      itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
        if (el.id === 'seItemUuid' || el.id === 'seItemId') return;
        if (el.type === 'file') el.disabled = false;
        else if (el.tagName === 'SELECT') el.disabled = false;
        else el.readOnly = false;
      });
      setRteEnabled(true);
      if (saveBtn) saveBtn.style.display = '';

      itemForm.dataset.mode = 'edit';
      itemForm.dataset.intent = 'create';
    }

    function fillForm(r, viewOnly=false){
      itemUuid.value = r.uuid || '';
      itemId.value = r.id || '';

      nameInput.value = r.name || '';
      slugInput.value = r.slug || '';
      sortOrderInput.value = String(r.sort_order ?? 0);
      statusSel.value = r.status || 'draft';
      featuredSel.value = String((r.is_featured_home ?? 0) ? 1 : 0);

      publishAtInput.value = toLocalDateTime(r.publish_at);
      expireAtInput.value = toLocalDateTime(r.expire_at);

      titleInput.value = r.title || '';
      companyNameInput.value = r.company_name || '';
      industryInput.value = r.industry || '';
      foundedYearInput.value = (r.founded_year ?? '') === null ? '' : String(r.founded_year ?? '');
      achievementDateInput.value = toLocalDate(r.achievement_date);
      companyWebsiteInput.value = r.company_website_url || '';
      highlightsInput.value = r.highlights || '';

      // description (required)
      const html = (r.description || '').toString();
      rte.editor.innerHTML = ensurePreHasCode(html);
      syncRteToCode();
      setRteMode('text');

      // social links
      let social = r.social_links_json ?? null;
      if (typeof social === 'string'){ try{ social = JSON.parse(social); }catch(_){ social = null; } }
      setSocialLinks(Array.isArray(social) ? social : []);

      // photo / logo existing preview
      const pUrl = r.photo_full_url || r.photo_url || '';
      if (pUrl){
        setPreview('photo', pUrl, '');
        photoRemoveBtn && (photoRemoveBtn.style.display='');
      } else {
        clearPreview('photo', true);
        photoRemoveBtn && (photoRemoveBtn.style.display='none');
      }

      const lUrl = r.company_logo_full_url || r.company_logo_url || '';
      if (lUrl){
        setPreview('logo', lUrl, '');
        logoRemoveBtn && (logoRemoveBtn.style.display='');
      } else {
        clearPreview('logo', true);
        logoRemoveBtn && (logoRemoveBtn.style.display='none');
      }

      slugDirty = true;

      if (viewOnly){
        itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
          if (el.id === 'seItemUuid' || el.id === 'seItemId') return;
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

    function findRow(uuid){
      const a = state.tabs.active.items || [];
      const i = state.tabs.inactive.all || [];
      const t = state.tabs.trash.items || [];
      return a.find(x => x?.uuid === uuid) || i.find(x => x?.uuid === uuid) || t.find(x => x?.uuid === uuid) || null;
    }

    nameInput?.addEventListener('input', debounce(() => {
      if (itemForm?.dataset.mode === 'view') return;
      if (itemUuid.value) return;
      if (slugDirty) return;
      const next = slugify(nameInput.value);
      settingSlug = true;
      slugInput.value = next;
      settingSlug = false;
    }, 120));

    slugInput?.addEventListener('input', () => {
      if (itemUuid.value) return;
      if (settingSlug) return;
      slugDirty = !!(slugInput.value || '').trim();
    });

    btnAdd?.addEventListener('click', () => {
      if (!canCreate) return;
      resetForm();
      itemModalTitle.textContent = 'Add Entrepreneur';
      itemForm.dataset.intent = 'create';
      itemModal?.show();
    });

    /* =========================
     * Row actions (View/Edit/Delete/Restore/Force/ToggleFeatured)
     * ========================= */
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;

      const tr = btn.closest('tr');
      const uuid = tr?.dataset?.uuid;
      const action = btn.dataset.action;
      if (!uuid) return;

      const row = findRow(uuid);

      // close dropdown
      const toggle = btn.closest('.dropdown')?.querySelector('.se-dd-toggle');
      if (toggle) { try { bootstrap.Dropdown.getInstance(toggle)?.hide(); } catch (_) {} }

      if (action === 'view' || action === 'edit'){
        if (action === 'edit' && !canEdit) return;
        resetForm();
        itemModalTitle.textContent = (action === 'view') ? 'View Entrepreneur' : 'Edit Entrepreneur';
        fillForm(row || {}, action === 'view');
        itemModal?.show();
        return;
      }

      if (action === 'toggleFeatured'){
        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/successful-entrepreneurs/${encodeURIComponent(uuid)}/toggle-featured`, {
            method: 'POST',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed');

          ok('Featured updated');
          await Promise.all([loadActive(), loadInactive(), loadTrash()]);
        }catch(ex){
          err(ex?.name==='AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      if (action === 'delete'){
        if (!canDelete) return;

        const conf = await Swal.fire({
          title: 'Delete this entrepreneur?',
          text: 'This will move the item to Trash.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/successful-entrepreneurs/${encodeURIComponent(uuid)}`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Delete failed');

          ok('Moved to trash');
          await Promise.all([loadActive(), loadInactive(), loadTrash()]);
        }catch(ex){
          err(ex?.name==='AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      if (action === 'restore'){
        const conf = await Swal.fire({
          title: 'Restore this item?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Restore'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/successful-entrepreneurs/${encodeURIComponent(uuid)}/restore`, {
            method: 'POST',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Restore failed');

          ok('Restored');
          await Promise.all([loadTrash(), loadActive(), loadInactive()]);
        }catch(ex){
          err(ex?.name==='AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      if (action === 'force'){
        if (!canDelete) return;

        const conf = await Swal.fire({
          title: 'Delete permanently?',
          text: 'This cannot be undone (photo/logo files will be removed).',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete Permanently',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/successful-entrepreneurs/${encodeURIComponent(uuid)}/force`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Force delete failed');

          ok('Deleted permanently');
          await loadTrash();
        }catch(ex){
          err(ex?.name==='AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }
    });

    /* =========================
     * Save (Create/Update)
     * ========================= */
    itemForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (saving) return;
      if (itemForm.dataset.mode === 'view') return;

      const intent = itemForm.dataset.intent || 'create';
      const isEdit = (intent === 'edit') && !!itemUuid.value;

      if (isEdit && !canEdit) return;
      if (!isEdit && !canCreate) return;

      saving = true;
      setBtnLoading(saveBtn, true);
      showLoading(true);

      try{
        const name = (nameInput.value || '').trim();
        if (!name){ err('Name is required'); nameInput.focus(); return; }

        const rawDesc = (rte.mode === 'code') ? (rte.code.value || '') : (rte.editor.innerHTML || '');
        const description = ensurePreHasCode(rawDesc).trim();
        if (!description){ err('Description is required'); rteFocus(); return; }

        const fd = new FormData();
        fd.append('name', name);

        const slug = (slugInput.value || '').trim();
        if (slug) fd.append('slug', slug);

        fd.append('sort_order', String(parseInt(sortOrderInput.value || '0', 10) || 0));
        fd.append('status', (statusSel.value || 'draft').trim());
        fd.append('is_featured_home', (featuredSel.value || '0') === '1' ? '1' : '0');

        if ((publishAtInput.value || '').trim()) fd.append('publish_at', publishAtInput.value);
        if ((expireAtInput.value || '').trim()) fd.append('expire_at', expireAtInput.value);

        const title = (titleInput.value || '').trim();
        if (title) fd.append('title', title);

        const companyName = (companyNameInput.value || '').trim();
        if (companyName) fd.append('company_name', companyName);

        const industry = (industryInput.value || '').trim();
        if (industry) fd.append('industry', industry);

        const website = (companyWebsiteInput.value || '').trim();
        if (website) fd.append('company_website_url', website);

        const foundedYear = (foundedYearInput.value || '').trim();
        if (foundedYear !== '') fd.append('founded_year', foundedYear);

        const ach = (achievementDateInput.value || '').trim();
        if (ach) fd.append('achievement_date', ach);

        const highlights = (highlightsInput.value || '').trim();
        if (highlights) fd.append('highlights', highlights);

        fd.append('description', description);

        const socials = readSocialLinks();
        if (socials.length) fd.append('social_links_json', JSON.stringify(socials));

        // file inputs
        const photo = photoInput.files?.[0] || null;
        if (photo) fd.append('photo', photo);

        const logo = logoInput.files?.[0] || null;
        if (logo) fd.append('company_logo', logo);

        // remove flags (update supports)
        if (isEdit){
          if (markPhotoRemove) fd.append('photo_remove', '1');
          if (markLogoRemove) fd.append('company_logo_remove', '1');
        }

        let url = '/api/successful-entrepreneurs';
        if (isEdit){
          url = `/api/successful-entrepreneurs/${encodeURIComponent(itemUuid.value)}`;
          // ✅ IMPORTANT: your API route supports PUT, not PATCH
          fd.append('_method', 'PUT');
        }

        const res = await fetchWithTimeout(url, {
          method: 'POST', // method override handles PUT on edit
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
        itemModal?.hide();

        // refresh all tabs because status can change
        state.tabs.active.page = 1;
        state.tabs.trash.page = 1;
        state.tabs.inactive.page = 1;

        await Promise.all([loadActive(), loadInactive(), loadTrash()]);
      }catch(ex){
        err(ex?.name==='AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        saving = false;
        setBtnLoading(saveBtn, false);
        showLoading(false);
      }
    });

    /* =========================
     * Initial load
     * ========================= */
    (async () => {
      showLoading(true);
      try{
        await fetchMe();
        await Promise.all([loadActive(), loadInactive(), loadTrash()]);
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
