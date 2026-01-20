{{-- resources/views/modules/course/manageCourses.blade.php --}}
@section('title','Courses')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Courses (Manage) – UI/UX inspired by Announcements
 * ========================= */

/* Dropdowns inside table */
.table-wrap .dropdown{position:relative}
.table-responsive .dropdown{position:relative} /* ✅ same as reference page */

.dropdown .dd-toggle{border-radius:10px}
.dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:230px;
  z-index:99999; /* ✅ higher to avoid being behind / clipped feeling */
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

/* ✅ UUID column smaller + ellipsis (replaces Code/Slug) */
th.col-code, td.col-code{width:320px;max-width:320px}
td.col-code{overflow:hidden}
td.col-code code{
  display:inline-block;
  max-width:250px;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
  vertical-align:bottom;
}

/* ✅ UUID cell layout + copy button */
.uuid-cell{
  display:flex;
  align-items:center;
  gap:8px;
}
.uuid-copy{
  border-radius:10px;
  padding:6px 10px;
  line-height:1;
  display:inline-flex;
  align-items:center;
  justify-content:center;
}
.uuid-copy i{font-size:14px;opacity:.85}

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
.badge-soft-danger{
  background:color-mix(in oklab, var(--danger-color) 12%, transparent);
  color:var(--danger-color)
}

/* Loading overlay */
/* .loading-overlay{
  position:fixed;
  top:0;left:0;width:100%;height:100%;
  background:rgba(0,0,0,0.45);
  display:flex;
  justify-content:center;
  align-items:center;
  z-index:9999;
  backdrop-filter:blur(2px)
} */
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
  .crs-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .crs-toolbar .position-relative{min-width:100% !important}
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

/* Horizontal scroll */
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
  min-width:1180px;
}
.table-responsive th,
.table-responsive td{
  white-space:nowrap;
}
@media (max-width: 576px){
  .table-responsive > .table{ min-width:1120px; }
}

/* =========================
 * RTE (lightweight)
 * ========================= */
.rte-help{font-size:12px;color:var(--muted-color);margin-top:6px}
.rte-row{margin-bottom:14px;}
.rte-wrap{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:var(--surface);
}
.rte-toolbar{
  display:flex;
  align-items:center;
  gap:6px;
  flex-wrap:wrap;
  padding:8px;
  border-bottom:1px solid var(--line-strong);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.rte-btn{
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
.rte-btn:hover{background:var(--page-hover)}
.rte-btn.active{
  background:color-mix(in oklab, var(--primary-color) 14%, transparent);
  border-color:color-mix(in oklab, var(--primary-color) 35%, var(--line-soft));
}
.rte-sep{width:1px;height:24px;background:var(--line-soft);margin:0 4px}

.rte-tabs{
  margin-left:auto;
  display:flex;
  border:1px solid var(--line-soft);
  border-radius:0;
  overflow:hidden;
}
.rte-tabs .tab{
  border:0;
  border-right:1px solid var(--line-soft);
  border-radius:0;
  padding:7px 12px;
  font-size:12px;
  cursor:pointer;
  background:transparent;
  color:var(--ink);
  line-height:1;
  user-select:none;
}
.rte-tabs .tab:last-child{border-right:0}
.rte-tabs .tab.active{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  font-weight:700;
}
.rte-area{position:relative}
.rte-editor{
  min-height:220px;
  padding:12px 12px;
  outline:none;
}
.rte-editor:empty:before{content:attr(data-placeholder);color:var(--muted-color);}
.rte-editor b, .rte-editor strong{font-weight:800}
.rte-editor i, .rte-editor em{font-style:italic}
.rte-editor u{text-decoration:underline}
.rte-editor h1{font-size:20px;margin:8px 0}
.rte-editor h2{font-size:18px;margin:8px 0}
.rte-editor h3{font-size:16px;margin:8px 0}
.rte-editor ul, .rte-editor ol{padding-left:22px}
.rte-editor p{margin:0 0 10px}
.rte-editor a{color:var(--primary-color);text-decoration:underline}
.rte-editor code{
  padding:2px 6px;
  border-radius:0;
  background:color-mix(in oklab, var(--muted-color) 14%, transparent);
  border:1px solid var(--line-soft);
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  font-size:12.5px;
}
.rte-editor pre{
  padding:10px 12px;
  border-radius:0;
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
  border:1px solid var(--line-soft);
  overflow:auto;
  margin:8px 0;
}
.rte-editor pre code{border:0;background:transparent;padding:0;display:block;white-space:pre;}
.rte-code{
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
.rte-wrap.mode-code .rte-editor{display:none;}
.rte-wrap.mode-code .rte-code{display:block;}

/* Cover preview box */
.cover-box{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:var(--bg-soft, color-mix(in oklab, var(--surface) 88%, var(--bg-body)));
}
.cover-box .cover-top{
  display:flex;align-items:center;justify-content:space-between;
  gap:10px;
  padding:10px 12px;
  border-bottom:1px solid var(--line-soft);
}
.cover-box .cover-body{padding:12px;}
.cover-box img{
  width:100%;
  max-height:260px;
  object-fit:cover;
  border-radius:12px;
  border:1px solid var(--line-soft);
  background:#fff;
}
.cover-meta{font-size:12.5px;color:var(--muted-color);margin-top:10px}
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
    <a class="nav-link active" data-bs-toggle="tab" href="#tab-published" role="tab" aria-selected="true">
      <i class="fa-solid fa-circle-check me-2"></i>Published
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#tab-draft" role="tab" aria-selected="false">
      <i class="fa-solid fa-pen-to-square me-2"></i>Draft
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#tab-archived" role="tab" aria-selected="false">
      <i class="fa-solid fa-box-archive me-2"></i>Archived
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#tab-bin" role="tab" aria-selected="false">
      <i class="fa-solid fa-trash-can me-2"></i>Bin
    </a>
  </li>
</ul>


  <div class="tab-content mb-3">

    {{-- ACTIVE TAB --}}
    <div class="tab-pane fade show active" id="tab-published" role="tabpanel">

      {{-- Toolbar --}}
      <div class="row align-items-center g-2 mb-3 crs-toolbar panel">
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
            <input id="searchInput" type="search" class="form-control ps-5" placeholder="Search by title, code, slug or UUID…">
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
              <i class="fa fa-plus me-1"></i> Add Course
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
                  <th>Title</th>
                  <th class="col-code">UUID</th>
                  <th style="width:170px;">Department</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:140px;">Level</th>
                  <th style="width:120px;">Duration</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-published">
                <tr><td colspan="8" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-published" class="empty p-4 text-center" style="display:none;">
            <i class="fa-solid fa-graduation-cap mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No active courses found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo-published">—</div>
            <nav><ul id="pager-published" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- INACTIVE TAB --}}
    <div class="tab-pane fade" id="tab-draft" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th class="col-code">UUID</th>
                  <th style="width:170px;">Department</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:140px;">Level</th>
                  <th style="width:120px;">Duration</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-draft">
                <tr><td colspan="8" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-draft" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-circle-pause mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No inactive courses found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo-draft">—</div>
            <nav><ul id="pager-draft" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ARCHIVED TAB --}}
<div class="tab-pane fade" id="tab-archived" role="tabpanel">
  <div class="card table-wrap">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-borderless align-middle mb-0">
          <thead class="sticky-top">
            <tr>
              <th>Title</th>
              <th class="col-code">UUID</th>
              <th style="width:170px;">Department</th>
              <th style="width:120px;">Status</th>
              <th style="width:140px;">Level</th>
              <th style="width:120px;">Duration</th>
              <th style="width:170px;">Updated</th>
              <th style="width:108px;" class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody id="tbody-archived">
            <tr><td colspan="8" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
          </tbody>
        </table>
      </div>

      <div id="empty-archived" class="empty p-4 text-center" style="display:none;">
        <i class="fa-solid fa-box-archive mb-2" style="font-size:32px;opacity:.6;"></i>
        <div>No archived courses found.</div>
      </div>

      <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
        <div class="text-muted small" id="resultsInfo-archived">—</div>
        <nav><ul id="pager-archived" class="pagination mb-0"></ul></nav>
      </div>
    </div>
  </div>
</div>


    {{-- TRASH TAB --}}
    <div class="tab-pane fade" id="tab-bin" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th class="col-code">UUID</th>
                  <th style="width:170px;">Department</th>
                  <th style="width:150px;">Deleted</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-bin">
                <tr><td colspan="5" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-bin" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>Trash is empty.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo-bin">—</div>
            <nav><ul id="pager-bin" class="pagination mb-0"></ul></nav>
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
  <option value="">(Use tabs)</option>
  <option value="published">Published</option>
  <option value="draft">Draft</option>
  <option value="archived">Archived</option>
</select>
<div class="form-text">Status is managed by the tabs (controller supports: draft/published/archived).</div>

            <div class="form-text">If your Course API uses different status values, adjust these options.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Department</label>
            <select id="modal_department" class="form-select">
              <option value="">All</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Level</label>
            <select id="modal_level" class="form-select">
              <option value="">Any</option>
              <option value="ug">UG</option>
              <option value="pg">PG</option>
              <option value="diploma">Diploma</option>
              <option value="phd">PhD</option>
              <option value="certificate">Certificate</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="modal_sort" class="form-select">
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="-updated_at">Recently Updated</option>
              <option value="title">Title A-Z</option>
              <option value="-title">Title Z-A</option>
              <option value="code">Code A-Z</option>
              <option value="-code">Code Z-A</option>
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
        <h5 class="modal-title" id="itemModalTitle">Add Course</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="itemUuid">
        <input type="hidden" id="itemId">

        <div class="row g-3">
          <div class="col-lg-6">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input class="form-control" id="title" required maxlength="255" placeholder="e.g., BCA (Bachelor of Computer Applications)">
              </div>

              <div class="col-md-6">
                <label class="form-label">Code (optional)</label>
                <input class="form-control" id="code" maxlength="80" placeholder="e.g., BCA">
              </div>

              <div class="col-md-6">
                <label class="form-label">Slug (optional)</label>
                <input class="form-control" id="slug" maxlength="160" placeholder="bca">
                <div class="form-text">Auto-generated from title until you edit this field manually.</div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Department</label>
                <select id="department_id" class="form-select">
                  <option value="">Select department</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Level</label>
                <select id="level" class="form-select">
                  <option value="ug">UG</option>
                  <option value="pg">PG</option>
                  <option value="diploma">Diploma</option>
                  <option value="phd">PhD</option>
                  <option value="certificate">Certificate</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Duration (optional)</label>
                <input class="form-control" id="duration" maxlength="60" placeholder="e.g., 3 Years / 6 Semesters">
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select" id="status">
  <option value="published">Published</option>
  <option value="draft">Draft</option>
  <option value="archived">Archived</option>
</select>
              </div>

              <div class="col-12">
                <label class="form-label">Cover Image (optional)</label>
                <input type="file" class="form-control" id="cover_image" accept="image/*">
                <div class="form-text">Upload an image (optional).</div>
              </div>

              <div class="col-12">
                <label class="form-label">Attachments (optional)</label>
                <input type="file" class="form-control" id="attachments" multiple>
                <div class="form-text">Optional multiple attachments (syllabus/brochure etc.).</div>
                <div class="small text-muted mt-2" id="currentAttachmentsInfo" style="display:none;">
                  <i class="fa fa-paperclip me-1"></i>
                  <span id="currentAttachmentsText">—</span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            {{-- RTE for Description --}}
            <div class="rte-row">
              <label class="form-label">Description (HTML allowed) <span class="text-danger">*</span></label>

              <div class="rte-wrap" id="descWrap">
                <div class="rte-toolbar" data-for="description">
                  <button type="button" class="rte-btn" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
                  <button type="button" class="rte-btn" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
                  <button type="button" class="rte-btn" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>

                  <span class="rte-sep"></span>

                  <button type="button" class="rte-btn" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
                  <button type="button" class="rte-btn" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>

                  <span class="rte-sep"></span>

                  <button type="button" class="rte-btn" data-block="h1" title="Heading 1">H1</button>
                  <button type="button" class="rte-btn" data-block="h2" title="Heading 2">H2</button>
                  <button type="button" class="rte-btn" data-block="h3" title="Heading 3">H3</button>

                  <span class="rte-sep"></span>

                  <button type="button" class="rte-btn" data-insert="pre" title="Code Block"><i class="fa fa-code"></i></button>
                  <button type="button" class="rte-btn" data-insert="code" title="Inline Code"><i class="fa fa-terminal"></i></button>

                  <span class="rte-sep"></span>

                  <button type="button" class="rte-btn" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>

                  <div class="rte-tabs">
                    <button type="button" class="tab active" data-mode="text">Text</button>
                    <button type="button" class="tab" data-mode="code">Code</button>
                  </div>
                </div>

                <div class="rte-area">
                  <div id="descEditor" class="rte-editor" contenteditable="true" data-placeholder="Write course description…"></div>
                  <textarea id="descCode" class="rte-code" spellcheck="false" autocomplete="off" autocapitalize="off" autocorrect="off"
                    placeholder="HTML code…"></textarea>
                </div>
              </div>

              <div class="rte-help">Use <b>Text</b> for rich editing or switch to <b>Code</b> to paste HTML.</div>
              <input type="hidden" id="description" name="description">
            </div>

            {{-- Cover preview --}}
            <div class="cover-box mt-3">
              <div class="cover-top">
                <div class="fw-semibold">
                  <i class="fa fa-image me-2"></i>Cover Preview
                </div>
                <div class="d-flex align-items-center gap-2">
                  <button type="button" class="btn btn-light btn-sm" id="btnOpenCover" style="display:none;">
                    <i class="fa fa-up-right-from-square me-1"></i>Open
                  </button>
                </div>
              </div>
              <div class="cover-body">
                <img id="coverPreview" src="" alt="Cover preview" style="display:none;">
                <div id="coverEmpty" class="text-muted small" style="padding:12px;border:1px dashed var(--line-soft);border-radius:12px;">
                  No cover selected.
                </div>
                <div class="cover-meta" id="coverMeta" style="display:none;">—</div>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
(() => {
  if (window.__COURSES_MODULE_INIT__) return;
  window.__COURSES_MODULE_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms = 300) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };

  // =========================
  // ✅ API Map (edit here if your routes differ)
  // =========================
  const API = {
    me:          () => '/api/users/me',
    departments: () => '/api/departments',
    list:        () => '/api/courses',
    create:      () => '/api/courses',
    update:      (id) => `/api/courses/${encodeURIComponent(id)}`,         // PATCH via _method
    remove:      (id) => `/api/courses/${encodeURIComponent(id)}`,         // DELETE (soft delete -> bin)
    restore:     (id) => `/api/courses/${encodeURIComponent(id)}/restore`, // POST
    force:       (id) => `/api/courses/${encodeURIComponent(id)}/force`    // DELETE (permanent)
  };

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

  function fmtDate(dt){
    if (!dt) return '—';
    const d = new Date(dt);
    if (Number.isNaN(d.getTime())) return '—';
    return d.toLocaleString();
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

  // ✅ department id must be integer
  function isIntString(v){
    return typeof v === 'string' && /^\d+$/.test(v.trim());
  }
  function resolveDepartmentId(valueOrUuid, departments){
    const v = (valueOrUuid ?? '').toString().trim();
    if (!v) return '';
    if (isIntString(v)) return v;

    const found = (departments || []).find(d => String(d?.uuid) === v);
    const id = found?.id;
    return (id !== null && id !== undefined) ? String(id) : '';
  }

  // ✅ robust level extraction (controller uses program_level)
  function getLevelFromRow(r){
    let v =
      r?.program_level ??
      r?.level ??
      r?.course_level ??
      r?.courseLevel ??
      r?.course_level_code ??
      r?.level_code ??
      r?.level_name ??
      r?.course_level_name ??
      r?.program_level ??
      r?.meta?.level ??
      r?.meta?.course_level ??
      r?.metadata?.level ??
      r?.metadata?.course_level ??
      '';

    if (v && typeof v === 'object'){
      v = v.code || v.slug || v.value || v.name || v.title || v.label || '';
    }
    return (v ?? '').toString().trim();
  }

  // ✅ duration from controller: duration_value + duration_unit
  function getDurationFromRow(r){
    let v =
      r?.duration ??
      r?.duration_text ??
      r?.course_duration ??
      r?.courseDuration ??
      r?.duration_label ??
      r?.meta?.duration ??
      r?.metadata?.duration ??
      '';

    if (!v) {
      const dv = r?.duration_value ?? r?.durationValue ?? null;
      const du = r?.duration_unit ?? r?.durationUnit ?? '';
      if (dv !== null && dv !== undefined && String(dv).trim() !== '') {
        v = `${dv} ${du || ''}`.trim();
      }
    }

    if (v && typeof v === 'object'){
      v = v.text || v.label || v.value || v.name || '';
    }
    return (v ?? '').toString().trim();
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const globalLoading = $('globalLoading');
    const showLoading = (v) => { if (globalLoading) globalLoading.style.display = v ? 'flex' : 'none'; };

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

    const perPageSel = $('perPage');
    const searchInput = $('searchInput');
    const btnReset = $('btnReset');
    const btnApplyFilters = $('btnApplyFilters');
    const writeControls = $('writeControls');
    const btnAddItem = $('btnAddItem');

    // ✅ NEW TAB DOM IDS
    const tbodyPublished = $('tbody-published');
    const tbodyDraft     = $('tbody-draft');
    const tbodyArchived  = $('tbody-archived');
    const tbodyBin       = $('tbody-bin');

    const emptyPublished = $('empty-published');
    const emptyDraft     = $('empty-draft');
    const emptyArchived  = $('empty-archived');
    const emptyBin       = $('empty-bin');

    const pagerPublished = $('pager-published');
    const pagerDraft     = $('pager-draft');
    const pagerArchived  = $('pager-archived');
    const pagerBin       = $('pager-bin');

    const infoPublished  = $('resultsInfo-published');
    const infoDraft      = $('resultsInfo-draft');
    const infoArchived   = $('resultsInfo-archived');
    const infoBin        = $('resultsInfo-bin');

    const filterModalEl = $('filterModal');
    const filterModal = filterModalEl ? new bootstrap.Modal(filterModalEl) : null;
    const modalStatus = $('modal_status'); // optional: used to jump to a tab
    const modalSort = $('modal_sort');
    const modalDepartment = $('modal_department');
    const modalLevel = $('modal_level');

    const itemModalEl = $('itemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;
    const itemModalTitle = $('itemModalTitle');
    const itemForm = $('itemForm');
    const saveBtn = $('saveBtn');

    const itemUuid = $('itemUuid');
    const itemId = $('itemId');
    const titleInput = $('title');
    const codeInput = $('code');
    const slugInput = $('slug');
    const deptSel = $('department_id');
    const levelSel = $('level');
    const durationInput = $('duration');
    const statusSel = $('status');

    const coverInput = $('cover_image');
    const attachmentsInput = $('attachments');

    const currentAttachmentsInfo = $('currentAttachmentsInfo');
    const currentAttachmentsText = $('currentAttachmentsText');

    const coverPreview = $('coverPreview');
    const coverEmpty = $('coverEmpty');
    const coverMeta = $('coverMeta');
    const btnOpenCover = $('btnOpenCover');

    // ---------- permissions ----------
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
        const res = await fetchWithTimeout(API.me(), { headers: authHeaders() }, 8000);
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

    // ---------- state ----------
    const state = {
      filters: { q:'', department:'', level:'', sort:'-created_at' },
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      tabs: {
        published: { page:1, lastPage:1, items:[] },
        draft:     { page:1, lastPage:1, items:[] },
        archived:  { page:1, lastPage:1, items:[] },
        bin:       { page:1, lastPage:1, items:[] }
      },
      departments: []
    };

    const getTabKey = () => {
      const a = document.querySelector('.nav-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#tab-published';
      if (href === '#tab-draft') return 'draft';
      if (href === '#tab-archived') return 'archived';
      if (href === '#tab-bin') return 'bin';
      return 'published';
    };

    function showTab(tabKey){
      const map = {
        published: '#tab-published',
        draft:     '#tab-draft',
        archived:  '#tab-archived',
        bin:       '#tab-bin'
      };
      const sel = map[tabKey] || '#tab-published';
      const a = document.querySelector(`a[href="${sel}"]`);
      if (!a) { loadTab(tabKey); return; }
      const already = a.classList.contains('active');
      try { bootstrap.Tab.getOrCreateInstance(a).show(); } catch(_) {}
      if (already) loadTab(tabKey);
    }

    function buildUrl(tabKey){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state.tabs[tabKey].page));

      const q = (state.filters.q || '').trim();
      if (q) params.set('q', q);

      const s = state.filters.sort || '-created_at';
      params.set('sort', s.startsWith('-') ? s.slice(1) : s);
      params.set('direction', s.startsWith('-') ? 'desc' : 'asc');

      if (state.filters.department) params.set('department', state.filters.department);

      // ✅ controller expects program_level (NOT level)
      if (state.filters.level) params.set('program_level', state.filters.level);

      // ✅ strict tab filters: prevents “same course in multiple tabs”
      if (tabKey === 'published') params.set('status', 'published');
      if (tabKey === 'draft')     params.set('status', 'draft');
      if (tabKey === 'archived')  params.set('status', 'archived');

      // ✅ bin = soft deleted
      if (tabKey === 'bin') params.set('only_trashed', '1');

      return `${API.list()}?${params.toString()}`;
    }

    function tabEls(tabKey){
      return {
        tbody: (tabKey==='published') ? tbodyPublished :
               (tabKey==='draft') ? tbodyDraft :
               (tabKey==='archived') ? tbodyArchived : tbodyBin,
        empty: (tabKey==='published') ? emptyPublished :
               (tabKey==='draft') ? emptyDraft :
               (tabKey==='archived') ? emptyArchived : emptyBin,
        pager: (tabKey==='published') ? pagerPublished :
               (tabKey==='draft') ? pagerDraft :
               (tabKey==='archived') ? pagerArchived : pagerBin,
        info:  (tabKey==='published') ? infoPublished :
               (tabKey==='draft') ? infoDraft :
               (tabKey==='archived') ? infoArchived : infoBin
      };
    }

    function setEmpty(tabKey, show){
      const { empty } = tabEls(tabKey);
      if (empty) empty.style.display = show ? '' : 'none';
    }

    function statusBadge(status){
      const s = (status || '').toString().toLowerCase().trim();
      if (s === 'published') return `<span class="badge badge-soft-success">Published</span>`;
      if (s === 'draft') return `<span class="badge badge-soft-warning">Draft</span>`;
      if (s === 'archived') return `<span class="badge badge-soft-muted">Archived</span>`;
      if (!s) return `<span class="badge badge-soft-muted">—</span>`;
      return `<span class="badge badge-soft-muted">${esc(s)}</span>`;
    }

    function levelBadge(level){
      const s = (level || '').toString().toLowerCase().trim();
      const label = s ? s.toUpperCase() : '—';
      if (['ug','pg','phd'].includes(s)) return `<span class="badge badge-soft-primary">${esc(label)}</span>`;
      if (s === 'diploma') return `<span class="badge badge-soft-success">DIPLOMA</span>`;
      if (s === 'certificate') return `<span class="badge badge-soft-warning">CERT</span>`;
      return `<span class="badge badge-soft-muted">${esc(label)}</span>`;
    }

    function renderPager(tabKey){
      const { pager } = tabEls(tabKey);
      if (!pager) return;

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

      pager.innerHTML = html;
    }

    function deptNameFromRow(r){
      if (r?.department_title) return r.department_title;
      const d = r.department || r.dept || null;
      if (typeof d === 'string') return d;
      if (d && typeof d === 'object') return d.title || d.name || d.department_name || '—';

      const id = r.department_id || r.dept_id || '';
      if (!id) return '—';

      const found = state.departments.find(x => String(x.id) === String(id) || String(x.uuid) === String(id));
      return found ? (found.title || found.name || '—') : '—';
    }

    function codeSlug(r){
      const code = r.code || r.course_code || '';
      const slug = r.slug || r.course_slug || '';
      return (code || slug || '—');
    }

    // =========================
    // ✅ ACTION DROPDOWN FIX (popper fixed)
    // =========================
    function dropdownInstance(toggle){
      return bootstrap.Dropdown.getOrCreateInstance(toggle, {
        autoClose: true,
        popperConfig: (def) => {
          const base = def || {};
          const mods = Array.isArray(base.modifiers) ? base.modifiers.slice() : [];
          mods.push({ name:'preventOverflow', options:{ boundary:'viewport', padding:8 } });
          mods.push({ name:'flip', options:{ boundary:'viewport', padding:8 } });
          return { ...base, strategy:'fixed', modifiers: mods };
        }
      });
    }

    function closeAllDropdownsExcept(exceptToggle){
      document.querySelectorAll('.dd-toggle').forEach(t => {
        if (t === exceptToggle) return;
        try{ bootstrap.Dropdown.getInstance(t)?.hide(); }catch(_){}
      });
    }

    // Toggle on click
    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('.dd-toggle');
      if (!toggle) return;
      if (!toggle.closest('.table-wrap')) return;

      e.preventDefault();
      e.stopPropagation();

      closeAllDropdownsExcept(toggle);

      try{
        dropdownInstance(toggle).toggle();
      }catch(_){}
    });

    // Close when clicking outside
    document.addEventListener('click', (e) => {
      if (e.target.closest('.dropdown')) return;
      closeAllDropdownsExcept(null);
    }, { capture:true });

    // =========================
    // ✅ NEW: Copy UUID button handler
    // =========================
    async function copyTextToClipboard(text){
      const t = (text || '').toString();
      if (!t) return false;

      // modern
      try{
        if (navigator.clipboard && window.isSecureContext){
          await navigator.clipboard.writeText(t);
          return true;
        }
      }catch(_){}

      // fallback
      try{
        const ta = document.createElement('textarea');
        ta.value = t;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.top = '-1000px';
        ta.style.left = '-1000px';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        const okCopy = document.execCommand('copy');
        document.body.removeChild(ta);
        return !!okCopy;
      }catch(_){
        return false;
      }
    }

    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('.btn-copy-uuid');
      if (!btn) return;

      e.preventDefault();
      e.stopPropagation();

      const uuid = btn.dataset.uuid || '';
      const done = await copyTextToClipboard(uuid);
      if (done) ok('UUID copied');
      else err('Copy failed');
    });

    function renderTable(tabKey){
      const { tbody } = tabEls(tabKey);
      if (!tbody) return;

      const rows = state.tabs[tabKey].items || [];

      if (!rows.length){
        tbody.innerHTML = '';
        setEmpty(tabKey, true);
        renderPager(tabKey);
        return;
      }
      setEmpty(tabKey, false);

      const isBin = tabKey === 'bin';

      const html = rows.map(r => {
        const uuidRaw = String(r.uuid || r.id || r.identifier || '');
        const uuidEsc = esc(uuidRaw || '—');

        const title = esc(r.title || r.name || '—');
        const dept = esc(deptNameFromRow(r));
        const status = (r.status || '').toString().toLowerCase().trim();

        const lvl = getLevelFromRow(r);
        const dur = getDurationFromRow(r);

        const updated = fmtDate(r.updated_at || r.updatedAt || r.created_at || r.createdAt);
        const deleted = fmtDate(r.deleted_at || r.deletedAt);

        // ✅ UUID cell with copy button (replaces Code/Slug)
        const uuidCell = uuidRaw
          ? `
            <div class="uuid-cell">
              <code title="${uuidEsc}">${uuidEsc}</code>
              <button type="button" class="btn btn-sm btn-light uuid-copy btn-copy-uuid" data-uuid="${esc(uuidRaw)}" title="Copy UUID">
                <i class="fa-regular fa-copy"></i>
              </button>
            </div>
          `
          : `<span class="text-muted small">—</span>`;

        const actions = (() => {
          if (isBin){
            return `
              <div class="dropdown">
                <button class="btn btn-sm btn-light dd-toggle" type="button" aria-expanded="false">
                  <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  <button type="button" class="dropdown-item" data-action="view">
                    <i class="fa-regular fa-eye"></i> View
                  </button>
                  <button type="button" class="dropdown-item" data-action="restore">
                    <i class="fa-solid fa-rotate-left"></i> Restore
                  </button>
                  ${canDelete ? `
                    <div class="dropdown-divider"></div>
                    <button type="button" class="dropdown-item text-danger" data-action="force">
                      <i class="fa-solid fa-trash-can"></i> Delete Permanently
                    </button>
                  ` : ``}
                </div>
              </div>
            `;
          }

          // non-bin tabs
          const moveBtns = [];
          if (canEdit && status !== 'published'){
            moveBtns.push(`
              <button type="button" class="dropdown-item" data-action="mark_published">
                <i class="fa-solid fa-circle-check"></i> Move to Published
              </button>
            `);
          }
          if (canEdit && status !== 'draft'){
            moveBtns.push(`
              <button type="button" class="dropdown-item" data-action="mark_draft">
                <i class="fa-solid fa-pen-to-square"></i> Move to Draft
              </button>
            `);
          }
          if (canEdit && status !== 'archived'){
            moveBtns.push(`
              <button type="button" class="dropdown-item" data-action="mark_archived">
                <i class="fa-solid fa-box-archive"></i> Move to Archived
              </button>
            `);
          }

          return `
            <div class="dropdown">
              <button class="btn btn-sm btn-light dd-toggle" type="button" aria-expanded="false">
                <i class="fa-solid fa-ellipsis-vertical"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end">
                <button type="button" class="dropdown-item" data-action="view">
                  <i class="fa-regular fa-eye"></i> View
                </button>
                ${canEdit ? `
                  <button type="button" class="dropdown-item" data-action="edit">
                    <i class="fa-regular fa-pen-to-square"></i> Edit
                  </button>
                ` : ``}
                ${moveBtns.length ? `<div class="dropdown-divider"></div>${moveBtns.join('')}` : ``}
                ${canDelete ? `
                  <div class="dropdown-divider"></div>
                  <button type="button" class="dropdown-item text-danger" data-action="delete">
                    <i class="fa-regular fa-trash-can"></i> Move to Bin
                  </button>
                ` : ``}
              </div>
            </div>
          `;
        })();

        if (isBin){
          return `
            <tr data-uuid="${esc(uuidRaw)}">
              <td><div class="fw-semibold">${title}</div></td>
              <td class="col-code">${uuidCell}</td>
              <td>${dept}</td>
              <td>${esc(deleted)}</td>
              <td class="text-end">${actions}</td>
            </tr>
          `;
        }

        return `
          <tr data-uuid="${esc(uuidRaw)}">
            <td><div class="fw-semibold">${title}</div></td>
            <td class="col-code">${uuidCell}</td>
            <td>${dept}</td>
            <td>${statusBadge(status)}</td>
            <td>${levelBadge(lvl)}</td>
            <td>${esc(dur || '—')}</td>
            <td>${esc(updated)}</td>
            <td class="text-end">${actions}</td>
          </tr>
        `;
      }).join('');

      tbody.innerHTML = html;
      renderPager(tabKey);
    }

    async function loadTab(tabKey){
      const { tbody, info } = tabEls(tabKey);

      if (tbody){
        const cols = (tabKey === 'bin') ? 5 : 8;
        tbody.innerHTML = `<tr><td colspan="${cols}" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;
      }

      try{
        const res = await fetchWithTimeout(buildUrl(tabKey), { headers: authHeaders() }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(js?.message || 'Failed to load');

        const items = Array.isArray(js.data) ? js.data : (Array.isArray(js) ? js : []);
        const p = js.pagination || js.meta || {};
        state.tabs[tabKey].items = items;
        state.tabs[tabKey].lastPage = parseInt(p.last_page || p.total_pages || 1, 10) || 1;

        const total = p.total ?? p.total_items ?? null;
        if (info) info.textContent = (total !== null) ? `${total} result(s)` : '—';

        renderTable(tabKey);
      }catch(e){
        state.tabs[tabKey].items = [];
        state.tabs[tabKey].lastPage = 1;
        renderTable(tabKey);
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    function reloadCurrent(){ loadTab(getTabKey()); }

    // ---------- pager ----------
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a.page-link[data-page]');
      if (!a) return;
      e.preventDefault();

      const tab = a.dataset.tab;
      const p = parseInt(a.dataset.page, 10);
      if (!tab || Number.isNaN(p)) return;
      if (!state.tabs[tab]) return;

      if (p === state.tabs[tab].page) return;
      state.tabs[tab].page = p;
      loadTab(tab);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // ---------- filters ----------
    searchInput?.addEventListener('input', debounce(() => {
      state.filters.q = (searchInput.value || '').trim();
      state.tabs.published.page = state.tabs.draft.page = state.tabs.archived.page = state.tabs.bin.page = 1;
      reloadCurrent();
    }, 320));

    perPageSel?.addEventListener('change', () => {
      state.perPage = parseInt(perPageSel.value, 10) || 20;
      state.tabs.published.page = state.tabs.draft.page = state.tabs.archived.page = state.tabs.bin.page = 1;
      reloadCurrent();
    });

    filterModalEl?.addEventListener('show.bs.modal', () => {
      // status filter is used as “jump to tab” (optional)
      if (modalStatus) modalStatus.value = '';
      if (modalSort) modalSort.value = state.filters.sort || '-created_at';
      if (modalDepartment) modalDepartment.value = state.filters.department || '';
      if (modalLevel) modalLevel.value = state.filters.level || '';
    });

    btnApplyFilters?.addEventListener('click', () => {
      const jump = (modalStatus?.value || '').trim().toLowerCase(); // published/draft/archived (optional)

      state.filters.sort = modalSort?.value || '-created_at';
      state.filters.department = modalDepartment?.value || '';
      state.filters.level = modalLevel?.value || '';

      state.tabs.published.page = state.tabs.draft.page = state.tabs.archived.page = state.tabs.bin.page = 1;

      filterModal && filterModal.hide();

      if (jump && ['published','draft','archived'].includes(jump)){
        showTab(jump);
      } else {
        reloadCurrent();
      }
    });

    btnReset?.addEventListener('click', () => {
      state.filters = { q:'', department:'', level:'', sort:'-created_at' };
      state.perPage = 20;

      if (searchInput) searchInput.value = '';
      if (perPageSel) perPageSel.value = '20';
      if (modalStatus) modalStatus.value = '';
      if (modalDepartment) modalDepartment.value = '';
      if (modalLevel) modalLevel.value = '';
      if (modalSort) modalSort.value = '-created_at';

      state.tabs.published.page = state.tabs.draft.page = state.tabs.archived.page = state.tabs.bin.page = 1;
      reloadCurrent();
    });

    document.querySelector('a[href="#tab-published"]')?.addEventListener('shown.bs.tab', () => loadTab('published'));
    document.querySelector('a[href="#tab-draft"]')?.addEventListener('shown.bs.tab', () => loadTab('draft'));
    document.querySelector('a[href="#tab-archived"]')?.addEventListener('shown.bs.tab', () => loadTab('archived'));
    document.querySelector('a[href="#tab-bin"]')?.addEventListener('shown.bs.tab', () => loadTab('bin'));

    // ---------- Departments ----------
    function fillDeptSelects(){
      const opts = state.departments.map(d => {
        const id = d?.id;
        const name = d?.title || d?.name || d?.department_name || '—';
        if (id === null || id === undefined || String(id).trim() === '') return '';
        return `<option value="${esc(String(id))}">${esc(String(name))}</option>`;
      }).join('');

      if (deptSel) deptSel.innerHTML = `<option value="">Select department</option>${opts}`;
      if (modalDepartment) modalDepartment.innerHTML = `<option value="">All</option>${opts}`;
    }

    async function loadDepartments(){
      try{
        const res = await fetchWithTimeout(API.departments(), { headers: authHeaders() }, 12000);
        if (!res.ok) return;
        const js = await res.json().catch(()=> ({}));
        const rows = Array.isArray(js.data) ? js.data : (Array.isArray(js) ? js : []);
        state.departments = rows || [];
        fillDeptSelects();
      }catch(_){}
    }

    // ---------- RTE ----------
    const rte = {
      wrap: $('descWrap'),
      toolbar: document.querySelector('#descWrap .rte-toolbar'),
      editor: $('descEditor'),
      code: $('descCode'),
      hidden: $('description'),
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
      const markerId = 'rte_caret_' + Math.random().toString(16).slice(2);
      document.execCommand('insertHTML', false, html + `<span id="${markerId}">\u200b</span>`);
      const marker = document.getElementById(markerId);
      if (marker) placeCaretAtMarker(marker);
    }

    function syncRteToCode(){
      if (!rte.editor || !rte.code) return;
      if (rte.mode === 'text') rte.code.value = ensurePreHasCode(rte.editor.innerHTML || '');
    }

    function setRteMode(mode){
      rte.mode = (mode === 'code') ? 'code' : 'text';
      rte.wrap?.classList.toggle('mode-code', rte.mode === 'code');

      rte.wrap?.querySelectorAll('.rte-tabs .tab').forEach(t => {
        t.classList.toggle('active', t.dataset.mode === rte.mode);
      });

      const disable = (rte.mode === 'code') || !rte.enabled;
      rte.wrap?.querySelectorAll('.rte-toolbar .rte-btn').forEach(b => {
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
      if (!rte.toolbar || rte.mode !== 'text') return;
      const set = (cmd, on) => {
        const b = rte.toolbar.querySelector(`.rte-btn[data-cmd="${cmd}"]`);
        if (b) b.classList.toggle('active', !!on);
      };
      try{
        set('bold', document.queryCommandState('bold'));
        set('italic', document.queryCommandState('italic'));
        set('underline', document.queryCommandState('underline'));
      }catch(_){}
    }

    rte.toolbar?.addEventListener('pointerdown', (e) => { e.preventDefault(); });
    rte.editor?.addEventListener('input', () => { syncRteToCode(); updateToolbarActive(); });
    ['mouseup','keyup','click'].forEach(ev => rte.editor?.addEventListener(ev, updateToolbarActive));
    document.addEventListener('selectionchange', () => {
      if (document.activeElement === rte.editor) updateToolbarActive();
    });

    document.addEventListener('click', (e) => {
      const tab = e.target.closest('#descWrap .rte-tabs .tab');
      if (tab){ setRteMode(tab.dataset.mode); return; }

      const btn = e.target.closest('#descWrap .rte-toolbar .rte-btn');
      if (!btn || rte.mode !== 'text' || !rte.enabled) return;

      rteFocus();

      const block = btn.getAttribute('data-block');
      const insert = btn.getAttribute('data-insert');
      const cmd = btn.getAttribute('data-cmd');

      if (block){
        try{ document.execCommand('formatBlock', false, `<${block}>`); }catch(_){}
        syncRteToCode(); updateToolbarActive();
        return;
      }

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
      if (rte.editor) rte.editor.setAttribute('contenteditable', on ? 'true' : 'false');
      if (rte.code) rte.code.disabled = !on;

      const disable = (rte.mode === 'code') || !rte.enabled;
      rte.wrap?.querySelectorAll('.rte-toolbar .rte-btn').forEach(b => {
        b.disabled = disable;
        b.style.opacity = disable ? '0.55' : '';
        b.style.pointerEvents = disable ? 'none' : '';
      });
      rte.wrap?.querySelectorAll('.rte-tabs .tab').forEach(t => {
        t.style.pointerEvents = on ? '' : 'none';
        t.style.opacity = on ? '' : '0.7';
      });
    }

    // ---------- cover preview ----------
    let coverObjectUrl = null;

    function clearCoverPreview(revoke=true){
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

    function setCoverPreview(url, metaText=''){
      const u = normalizeUrl(url);
      if (!u){ clearCoverPreview(true); return; }

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

    coverInput?.addEventListener('change', () => {
      const f = coverInput.files?.[0];
      if (!f) { clearCoverPreview(true); return; }

      if (coverObjectUrl){
        try{ URL.revokeObjectURL(coverObjectUrl); }catch(_){}
      }
      coverObjectUrl = URL.createObjectURL(f);
      setCoverPreview(coverObjectUrl, `${f.name || 'cover'} • ${bytes(f.size)}`);
    });

    attachmentsInput?.addEventListener('change', () => {
      const files = Array.from(attachmentsInput.files || []);
      if (!files.length){
        if (currentAttachmentsInfo) currentAttachmentsInfo.style.display = 'none';
        if (currentAttachmentsText) currentAttachmentsText.textContent = '—';
        return;
      }
      if (currentAttachmentsInfo) currentAttachmentsInfo.style.display = '';
      if (currentAttachmentsText) currentAttachmentsText.textContent = `${files.length} selected`;
    });

    // ---------- modal helpers ----------
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

      if (rte.editor) rte.editor.innerHTML = '';
      if (rte.code) rte.code.value = '';
      if (rte.hidden) rte.hidden.value = '';
      setRteMode('text');
      setRteEnabled(true);

      if (currentAttachmentsInfo) currentAttachmentsInfo.style.display = 'none';
      if (currentAttachmentsText) currentAttachmentsText.textContent = '—';

      clearCoverPreview(true);

      itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
        if (el.id === 'itemUuid' || el.id === 'itemId') return;
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

    function fillFormFromRow(r, viewOnly=false){
      itemUuid.value = r.uuid || r.id || r.identifier || '';
      itemId.value = r.id || '';

      titleInput.value = r.title || r.name || '';
      codeInput.value = r.code || r.course_code || '';
      slugInput.value = r.slug || r.course_slug || '';

      const rawDid = r.department_id || r.dept_id || r.department?.id || r.department?.uuid || '';
      const did = resolveDepartmentId(String(rawDid || ''), state.departments);
      if (deptSel) deptSel.value = did ? String(did) : '';

      const lvl = getLevelFromRow(r) || 'ug';
      levelSel.value = lvl.toString().toLowerCase();

      durationInput.value = getDurationFromRow(r) || '';

      // ✅ status is ONLY: draft/published/archived
      const st = (r.status || '').toString().toLowerCase().trim();
      statusSel.value = (st === 'published' || st === 'draft' || st === 'archived') ? st : 'draft';

      const descHtml = (r.body ?? r.description ?? r.description_html ?? r.about ?? '') || '';
      if (rte.editor) rte.editor.innerHTML = ensurePreHasCode(descHtml);
      syncRteToCode();
      setRteMode('text');

      const coverUrl = r.cover_image_url || r.cover_url || r.cover_image || r.image_url || '';
      if (coverUrl){
        const meta = r.cover_original_name ? `${r.cover_original_name}${r.cover_file_size ? ' • ' + bytes(r.cover_file_size) : ''}` : '';
        clearCoverPreview(true);
        setCoverPreview(coverUrl, meta);
      } else {
        clearCoverPreview(true);
      }

      let atts = r.attachments || r.attachments_json || r.attachments_list || null;
      if (typeof atts === 'string') { try{ atts = JSON.parse(atts); }catch(_){ atts=null; } }
      atts = Array.isArray(atts) ? atts : [];

      if (atts.length){
        if (currentAttachmentsInfo) currentAttachmentsInfo.style.display = '';
        if (currentAttachmentsText) currentAttachmentsText.textContent = `${atts.length} file(s) attached`;
      } else {
        if (currentAttachmentsInfo) currentAttachmentsInfo.style.display = 'none';
        if (currentAttachmentsText) currentAttachmentsText.textContent = '—';
      }

      slugDirty = true;

      if (viewOnly){
        itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
          if (el.id === 'itemUuid' || el.id === 'itemId') return;
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

    function findRowByUuid(uuid){
      const all = [
        ...(state.tabs.published.items || []),
        ...(state.tabs.draft.items || []),
        ...(state.tabs.archived.items || []),
        ...(state.tabs.bin.items || []),
      ];
      return all.find(x => String(x?.uuid || x?.id || x?.identifier) === String(uuid)) || null;
    }

    // auto slug while creating
    titleInput?.addEventListener('input', debounce(() => {
      if (itemForm?.dataset.mode === 'view') return;
      if (itemUuid.value) return;
      if (slugDirty) return;
      const next = slugify(titleInput.value);
      settingSlug = true;
      slugInput.value = next;
      settingSlug = false;
    }, 120));

    slugInput?.addEventListener('input', () => {
      if (itemUuid.value) return;
      if (settingSlug) return;
      slugDirty = !!(slugInput.value || '').trim();
    });

    btnAddItem?.addEventListener('click', () => {
      if (!canCreate) return;
      resetForm();
      if (itemModalTitle) itemModalTitle.textContent = 'Add Course';
      itemForm.dataset.intent = 'create';
      itemModal && itemModal.show();
    });

    itemModalEl?.addEventListener('hidden.bs.modal', () => {
      if (coverObjectUrl){ try{ URL.revokeObjectURL(coverObjectUrl); }catch(_){ } coverObjectUrl=null; }
    });

    // ✅ NEW: status update helper (draft/published/archived)
    async function updateStatus(uuid, status){
      const fd = new FormData();
      fd.append('_method', 'PATCH');
      fd.append('status', status); // controller accepts only these

      showLoading(true);
      try{
        const res = await fetchWithTimeout(API.update(uuid), {
          method: 'POST',
          headers: authHeaders(),
          body: fd
        }, 15000);

        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Update failed');

        ok(`Moved to ${status}`);
        await Promise.all([
          loadTab('published'),
          loadTab('draft'),
          loadTab('archived'),
          loadTab('bin')
        ]);
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        showLoading(false);
      }
    }

    // ---------- row actions ----------
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
      if (toggle) { try { dropdownInstance(toggle).hide(); } catch (_) {} }

      if (act === 'view' || act === 'edit'){
        if (act === 'edit' && !canEdit) return;
        resetForm();
        if (itemModalTitle) itemModalTitle.textContent = act === 'view' ? 'View Course' : 'Edit Course';
        fillFormFromRow(row || {}, act === 'view');
        itemModal && itemModal.show();
        return;
      }

      // ✅ status moves
      if (act === 'mark_published'){
        if (!canEdit) return;
        const conf = await Swal.fire({
          title: 'Move to Published?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Move'
        });
        if (!conf.isConfirmed) return;
        await updateStatus(uuid, 'published');
        return;
      }

      if (act === 'mark_draft'){
        if (!canEdit) return;
        const conf = await Swal.fire({
          title: 'Move to Draft?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Move'
        });
        if (!conf.isConfirmed) return;
        await updateStatus(uuid, 'draft');
        return;
      }

      if (act === 'mark_archived'){
        if (!canEdit) return;
        const conf = await Swal.fire({
          title: 'Move to Archived?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Move'
        });
        if (!conf.isConfirmed) return;
        await updateStatus(uuid, 'archived');
        return;
      }

      // soft delete -> bin
      if (act === 'delete'){
        if (!canDelete) return;
        const conf = await Swal.fire({
          title: 'Delete this course?',
          text: 'This will move the item to Bin.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(API.remove(uuid), {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);

          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Delete failed');

          ok('Moved to bin');
          await Promise.all([loadTab('published'), loadTab('draft'), loadTab('archived'), loadTab('bin')]);
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      // restore from bin
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
          const res = await fetchWithTimeout(API.restore(uuid), {
            method: 'POST',
            headers: authHeaders()
          }, 15000);

          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Restore failed');

          ok('Restored');
          await Promise.all([loadTab('bin'), loadTab('published'), loadTab('draft'), loadTab('archived')]);
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      // permanent delete
      if (act === 'force'){
        if (!canDelete) return;

        const conf = await Swal.fire({
          title: 'Delete permanently?',
          text: 'This cannot be undone (files may be removed).',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete Permanently',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(API.force(uuid), {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);

          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Force delete failed');

          ok('Deleted permanently');
          await loadTab('bin');
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }
    });

    // ---------- submit (create/edit) ----------
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

        const title = (titleInput.value || '').trim();
        const code = (codeInput.value || '').trim();
        const slug = (slugInput.value || '').trim();

        const deptRaw = (deptSel?.value || '').trim();
        const deptId = resolveDepartmentId(deptRaw, state.departments);

        const level = (levelSel?.value || 'ug').trim();
        const durationText = (durationInput.value || '').trim();

        // ✅ status is ONLY: draft/published/archived (no active/inactive)
        const statusToSend = (statusSel?.value || 'draft').trim().toLowerCase();

        const rawDesc = (rte.mode === 'code') ? (rte.code.value || '') : (rte.editor.innerHTML || '');
        const cleanDesc = ensurePreHasCode(rawDesc).trim();
        if (rte.hidden) rte.hidden.value = cleanDesc;

        if (!title){ err('Title is required'); titleInput.focus(); return; }
        if (!cleanDesc){ err('Description is required'); rteFocus(); return; }

        const fd = new FormData();
        fd.append('title', title);
        if (code) fd.append('code', code);
        if (slug) fd.append('slug', slug);

        if (deptId) fd.append('department_id', String(parseInt(deptId, 10)));

        // ✅ controller expects program_level
        fd.append('program_level', level);

        // ✅ duration_value + duration_unit (best-effort; won’t break if empty)
        if (durationText){
          const m = durationText.match(/(\d+)/);
          if (m) fd.append('duration_value', m[1]);
          let unit = 'months';
          if (/year/i.test(durationText)) unit = 'years';
          else if (/semester/i.test(durationText)) unit = 'semesters';
          else if (/month/i.test(durationText)) unit = 'months';
          fd.append('duration_unit', unit);
        }

        fd.append('status', statusToSend);
        fd.append('body', cleanDesc);

        const cover = coverInput?.files?.[0] || null;
        if (cover) fd.append('cover_image', cover);

        Array.from(attachmentsInput?.files || []).forEach(f => fd.append('attachments[]', f));

        const url = isEdit ? API.update(itemUuid.value) : API.create();
        if (isEdit) fd.append('_method', 'PATCH');

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

        state.tabs.published.page = state.tabs.draft.page = state.tabs.archived.page = state.tabs.bin.page = 1;
        await Promise.all([loadTab('published'), loadTab('draft'), loadTab('archived'), loadTab('bin')]);
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
        await loadDepartments();

        // initial loads (safe)
        await Promise.all([
          loadTab('published'),
          loadTab('draft'),
          loadTab('archived'),
          loadTab('bin')
        ]);
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
