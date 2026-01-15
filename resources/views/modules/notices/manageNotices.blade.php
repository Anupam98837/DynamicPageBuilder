{{-- resources/views/modules/notice/manageNotices.blade.php --}}
@section('title','Notices')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Notices UI (same structure reference, rewritten)
 * ========================= */

/* Dropdown safety inside table */
.table-responsive .dropdown{position:relative}
.nt-dd-toggle{border-radius:10px}
.dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:230px;
  z-index:99999;
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
  background:var(--surface);
}
.table thead.sticky-top{z-index:3}
.table tbody tr{border-top:1px solid var(--line-soft)}
.table tbody tr:hover{background:var(--page-hover)}
.small{font-size:12.5px}

/* Slug column */
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
  color:var(--success-color)
}
.badge-soft-warning{
  background:color-mix(in oklab, var(--warning-color, #f59e0b) 14%, transparent);
  color:var(--warning-color, #f59e0b)
}
.badge-soft-muted{
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
  color:var(--muted-color)
}

/* Global loading overlay */
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
  display:flex;flex-direction:column;align-items:center;gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,.3)
}
.spinner{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,0.3);
  border-top:4px solid var(--primary-color);
  animation:spin 1s linear infinite
}
@keyframes spin{to{transform:rotate(360deg)}}

/* Button spinner */
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

/* Toolbar responsive */
@media (max-width:768px){
  .nt-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .nt-toolbar .position-relative{min-width:100% !important}
  .toolbar-buttons{display:flex;gap:8px;flex-wrap:wrap}
  .toolbar-buttons .btn{flex:1;min-width:120px}
}

/* Horizontal scroll */
.table-responsive{
  display:block;width:100%;
  overflow-x:auto !important;
  overflow-y:visible !important;
  -webkit-overflow-scrolling:touch;
  position:relative;
}
.table-responsive > .table{width:max-content;min-width:1120px}
.table-responsive th,.table-responsive td{white-space:nowrap}

/* Cover thumbnail in table */
.thumb{
  width:46px;height:34px;border-radius:10px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
  overflow:hidden;
  display:flex;align-items:center;justify-content:center;
}
.thumb img{width:100%;height:100%;object-fit:cover;display:block}
.thumb i{opacity:.55}

/* =========================
 * RTE (single toolbar, stable caret)
 * ========================= */
.rte-help{font-size:12px;color:var(--muted-color);margin-top:6px}
.rte-row{margin-bottom:14px}
.rte-wrap{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:var(--surface);
}
.rte-toolbar{
  display:flex;align-items:center;gap:6px;flex-wrap:wrap;
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
  display:inline-flex;align-items:center;justify-content:center;gap:6px;
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
.rte-editor:empty:before{content:attr(data-placeholder);color:var(--muted-color)}

.rte-editor b,.rte-editor strong{font-weight:800}
.rte-editor i,.rte-editor em{font-style:italic}
.rte-editor u{text-decoration:underline}
.rte-editor h1{font-size:20px;margin:8px 0}
.rte-editor h2{font-size:18px;margin:8px 0}
.rte-editor h3{font-size:16px;margin:8px 0}
.rte-editor ul,.rte-editor ol{padding-left:22px}
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
.rte-editor pre code{border:0;background:transparent;padding:0;display:block;white-space:pre}

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
.rte-wrap.mode-code .rte-editor{display:none}
.rte-wrap.mode-code .rte-code{display:block}

/* Cover preview */
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
.cover-box .cover-body{padding:12px}
.cover-box img{
  width:100%;
  max-height:260px;
  object-fit:cover;
  border-radius:12px;
  border:1px solid var(--line-soft);
  background:#fff;
}
.cover-meta{font-size:12.5px;color:var(--muted-color);margin-top:10px}

/* Small helper row (department load state) */
.dept-hint{
  font-size:12px;
  color:var(--muted-color);
  margin-top:6px;
  display:flex;
  align-items:center;
  gap:8px;
}
.dept-dot{
  width:8px;height:8px;border-radius:99px;
  background:var(--muted-color);
  opacity:.6;
}
.dept-dot.ok{background:var(--success-color);opacity:.9}
.dept-dot.bad{background:var(--danger-color);opacity:.9}
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
        <i class="fa-solid fa-circle-check me-2"></i>Active
      </a>
    </li>

    {{-- ✅ CHANGED: Inactive -> Draft --}}
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-draft" role="tab" aria-selected="false">
        <i class="fa-solid fa-pen-to-square me-2"></i>Draft
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
      <div class="row align-items-center g-2 mb-3 nt-toolbar panel">
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

          <div class="toolbar-buttons">
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
              <i class="fa fa-plus me-1"></i> Add Notice
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
                  <th style="width:70px;">Cover</th>
                  <th>Title</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:150px;">Publish At</th>
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

          <div id="empty-active" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-circle-info mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No active notices found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo-active">—</div>
            <nav><ul id="pager-active" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ✅ DRAFT TAB (was INACTIVE) --}}
    <div class="tab-pane fade" id="tab-draft" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:70px;">Cover</th>
                  <th>Title</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:150px;">Publish At</th>
                  <th style="width:110px;">Sort</th>
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
            <i class="fa-solid fa-pen-to-square mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No draft notices found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo-draft">—</div>
            <nav><ul id="pager-draft" class="pagination mb-0"></ul></nav>
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
                  <th style="width:70px;">Cover</th>
                  <th>Title</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:150px;">Deleted</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-trash">
                <tr><td colspan="6" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
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
            <label class="form-label">Status</label>
            <select id="modal_status" class="form-select">
              <option value="">All</option>
              <option value="published">Published</option>
              <option value="draft">Draft</option>
              <option value="archived">Archived</option>
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
              <option value="sort_order">Sort Order ↑</option>
              <option value="-sort_order">Sort Order ↓</option>
              <option value="-publish_at">Publish At (Desc)</option>
              <option value="publish_at">Publish At (Asc)</option>
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
        <h5 class="modal-title" id="itemModalTitle">Add Notice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="itemUuid">
        <input type="hidden" id="itemId">

        <div class="row g-3">
          <div class="col-lg-6">
            <div class="row g-3">

              {{-- ✅ CHANGED: Title first --}}
              <div class="col-12">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input class="form-control" id="title" required maxlength="255" placeholder="e.g., Semester Exam Notice">
              </div>

              {{-- ✅ CHANGED: Department dropdown after Title --}}
              <div class="col-12">
                <label class="form-label">Department</label>
                <select class="form-select" id="department_id">
                  <option value="">General (No Department)</option>
                </select>
                <div class="dept-hint">
                  <span id="deptDot" class="dept-dot"></span>
                  <span id="deptHintText">Loading departments…</span>
                </div>
              </div>

              <div class="col-md-8">
                <label class="form-label">Slug (optional)</label>
                <input class="form-control" id="slug" maxlength="160" placeholder="semester-exam-notice">
                <div class="form-text">Auto-generated from title until you edit this field manually.</div>
              </div>

              <div class="col-md-4">
                <label class="form-label">Sort Order</label>
                <input type="number" class="form-control" id="sort_order" min="0" max="1000000" value="0">
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select" id="status">
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="archived">Archived</option>
                </select>
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
                <label class="form-label">Cover Image (optional)</label>
                <input type="file" class="form-control" id="cover_image" accept="image/*">
                <div class="form-text">Upload an image (optional).</div>
              </div>

              <div class="col-12">
                <label class="form-label">Attachments (optional)</label>
                <input type="file" class="form-control" id="attachments" multiple>
                <div class="form-text">Optional multiple attachments.</div>
                <div class="small text-muted mt-2" id="currentAttachmentsInfo" style="display:none;">
                  <i class="fa fa-paperclip me-1"></i>
                  <span id="currentAttachmentsText">—</span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            {{-- RTE for Body (HTML allowed) --}}
            <div class="rte-row">
              <label class="form-label">Body (HTML allowed) <span class="text-danger">*</span></label>

              <div class="rte-wrap" id="bodyWrap">
                <div class="rte-toolbar" data-for="body">
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
                  <div id="bodyEditor" class="rte-editor" contenteditable="true" data-placeholder="Write notice content…"></div>
                  <textarea id="bodyCode" class="rte-code" spellcheck="false" autocomplete="off" autocapitalize="off" autocorrect="off"
                    placeholder="HTML code…"></textarea>
                </div>
              </div>

              <div class="rte-help">Use <b>Text</b> for rich editing or switch to <b>Code</b> to paste HTML.</div>
              <input type="hidden" id="body" name="body">
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
  if (window.__NOTICES_MODULE_INIT__) return;
  window.__NOTICES_MODULE_INIT__ = true;

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
    try{ return await fetch(url, { ...opts, signal: ctrl.signal }); }
    finally{ clearTimeout(t); }
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

    // Elements
    const perPageSel = $('perPage');
    const searchInput = $('searchInput');
    const btnReset = $('btnReset');
    const btnApplyFilters = $('btnApplyFilters');
    const writeControls = $('writeControls');
    const btnAddItem = $('btnAddItem');

    const tbodyActive = $('tbody-active');
    const tbodyDraft = $('tbody-draft');  // ✅ changed
    const tbodyTrash = $('tbody-trash');

    const emptyActive = $('empty-active');
    const emptyDraft = $('empty-draft');  // ✅ changed
    const emptyTrash = $('empty-trash');

    const pagerActive = $('pager-active');
    const pagerDraft = $('pager-draft');  // ✅ changed
    const pagerTrash = $('pager-trash');

    const infoActive = $('resultsInfo-active');
    const infoDraft = $('resultsInfo-draft'); // ✅ changed
    const infoTrash = $('resultsInfo-trash');

    const filterModalEl = $('filterModal');
    const filterModal = filterModalEl ? new bootstrap.Modal(filterModalEl) : null;
    const modalStatus = $('modal_status');
    const modalSort = $('modal_sort');

    const itemModalEl = $('itemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;
    const itemModalTitle = $('itemModalTitle');
    const itemForm = $('itemForm');
    const saveBtn = $('saveBtn');

    const itemUuid = $('itemUuid');
    const itemId = $('itemId');
    const titleInput = $('title');
    const slugInput = $('slug');
    const sortOrderInput = $('sort_order');
    const statusSel = $('status');
    const publishAtInput = $('publish_at');
    const expireAtInput = $('expire_at');
    const coverInput = $('cover_image');
    const attachmentsInput = $('attachments');

    // Department dropdown refs
    const departmentSel = $('department_id');
    const deptDot = $('deptDot');
    const deptHintText = $('deptHintText');

    const currentAttachmentsInfo = $('currentAttachmentsInfo');
    const currentAttachmentsText = $('currentAttachmentsText');

    const coverPreview = $('coverPreview');
    const coverEmpty = $('coverEmpty');
    const coverMeta = $('coverMeta');
    const btnOpenCover = $('btnOpenCover');

    // Permissions
    const ACTOR = { role: '' };
    let canCreate=false, canEdit=false, canDelete=false;
    // Add publishing permissions
    let canPublish = false;

    function computePermissions(){
      const r = (ACTOR.role || '').toLowerCase();
      const createDeleteRoles = ['admin','super_admin','director','principal'];
      const writeRoles = ['admin','super_admin','director','principal','hod','faculty','technical_assistant','it_person'];

      canCreate = createDeleteRoles.includes(r);
      canDelete = createDeleteRoles.includes(r);
      canEdit   = writeRoles.includes(r);
      canPublish = createDeleteRoles.includes(r);  // Only these roles can publish

      if (writeControls) writeControls.style.display = canCreate ? 'flex' : 'none';
      updatePublishOption();
    }

    function updatePublishOption(){
      if (!statusSel) return;
      const publishOption = statusSel.querySelector('option[value="published"]');
      if (publishOption){
        publishOption.style.display = canPublish ? '' : 'none';
        if (!canPublish && statusSel.value === 'published'){
          statusSel.value = 'draft';
        }
      }
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

    // State
    const state = {
      filters: { q:'', status:'', sort:'-created_at' },
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      departments: [],
      tabs: {
        active: { page:1, lastPage:1, items:[] },
        draft:  { page:1, lastPage:1, items:[] }, // ✅ changed
        trash:  { page:1, lastPage:1, items:[] }
      }
    };

    const getTabKey = () => {
      const a = document.querySelector('.nav-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#tab-active';
      if (href === '#tab-draft') return 'draft'; // ✅ changed
      if (href === '#tab-trash') return 'trash';
      return 'active';
    };

    // ✅ CHANGED: draft tab is driven by status=draft (so switching status to draft moves it here)
    function buildUrl(tabKey){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state.tabs[tabKey].page));

      const q = (state.filters.q || '').trim();
      if (q) params.set('q', q);

      const s = state.filters.sort || '-created_at';
      params.set('sort', s.startsWith('-') ? s.slice(1) : s);
      params.set('direction', s.startsWith('-') ? 'desc' : 'asc');

      if (tabKey === 'draft') {
        params.set('status', 'draft');
      } else if (state.filters.status) {
        params.set('status', state.filters.status);
      }

      if (tabKey === 'active') params.set('active', '1');
      if (tabKey === 'draft')  params.set('active', '0');
      if (tabKey === 'trash')  params.set('only_trashed', '1');

      return `/api/notices?${params.toString()}`;
    }

    function setEmpty(tabKey, show){
      const el =
        tabKey==='active' ? emptyActive :
        (tabKey==='draft' ? emptyDraft : emptyTrash);
      if (el) el.style.display = show ? '' : 'none';
    }

    function statusBadge(status){
      const s = (status || '').toString().toLowerCase();
      if (s === 'published') return `<span class="badge badge-soft-success">Published</span>`;
      if (s === 'draft') return `<span class="badge badge-soft-warning">Draft</span>`;
      if (s === 'archived') return `<span class="badge badge-soft-muted">Archived</span>`;
      return `<span class="badge badge-soft-muted">${esc(s || '—')}</span>`;
    }

    function coverThumb(url){
      const u = normalizeUrl(url);
      if (!u) return `<div class="thumb" title="No cover"><i class="fa-regular fa-image"></i></div>`;
      return `<div class="thumb" title="Cover"><img src="${esc(u)}" alt="cover"></div>`;
    }

    function renderPager(tabKey){
      const pagerEl =
        tabKey === 'active' ? pagerActive :
        (tabKey === 'draft' ? pagerDraft : pagerTrash);
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
      const tbody =
        tabKey==='active' ? tbodyActive :
        (tabKey==='draft' ? tbodyDraft : tbodyTrash);

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
        const status = (r.status || (r.active ? 'published' : 'draft') || '').toString().toLowerCase();
        const publishAt = r.publish_at || '—';
        const updated = r.updated_at || '—';
        const deleted = r.deleted_at || '—';
        const sortOrder = (r.sort_order ?? 0);
        const coverUrl = r.cover_image_url || r.cover_url || r.cover_image || '';

        // Build action menu
        let actions = `
          <div class="dropdown text-end">
            <button type="button"
              class="btn btn-light btn-sm nt-dd-toggle"
              aria-expanded="false" title="Actions">
              <i class="fa fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>`;

        if (canEdit && tabKey !== 'trash'){
          actions += `<li><button type="button" class="dropdown-item" data-action="edit"><i class="fa fa-pen-to-square"></i> Edit</button></li>`;

          const statusLower = (status || '').toString().toLowerCase();
          if (canPublish && statusLower !== 'published'){
            actions += `<li><button type="button" class="dropdown-item" data-action="make-publish"><i class="fa fa-circle-check"></i> Make Published</button></li>`;
          } else if (statusLower === 'published' && canPublish) {
            actions += `<li><button type="button" class="dropdown-item" data-action="mark-draft"><i class="fa fa-circle-pause"></i> Mark as Draft</button></li>`;
          }
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
              <td>${coverThumb(coverUrl)}</td>
              <td class="fw-semibold">${esc(title)}</td>
              <td class="col-slug"><code>${esc(slug)}</code></td>
              <td>${esc(deleted)}</td>
              <td>${esc(String(sortOrder))}</td>
              <td class="text-end">${actions}</td>
            </tr>`;
        }

        return `
          <tr data-uuid="${esc(uuid)}">
            <td>${coverThumb(coverUrl)}</td>
            <td class="fw-semibold">${esc(title)}</td>
            <td class="col-slug"><code>${esc(slug)}</code></td>
            <td>${statusBadge(status)}</td>
            <td>${esc(String(publishAt))}</td>
            <td>${esc(String(sortOrder))}</td>
            <td>${esc(String(updated))}</td>
            <td class="text-end">${actions}</td>
          </tr>`;
      }).join('');

      renderPager(tabKey);
    }

    async function loadTab(tabKey){
      const tbody =
        tabKey==='active' ? tbodyActive :
        (tabKey==='draft' ? tbodyDraft : tbodyTrash);

      if (tbody){
        const cols = (tabKey==='trash') ? 6 : 8;
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

        const totalText = (p.total ? `${p.total} result(s)` : '—');
        if (tabKey === 'active' && infoActive) infoActive.textContent = totalText;
        if (tabKey === 'draft' && infoDraft) infoDraft.textContent = totalText;
        if (tabKey === 'trash' && infoTrash) infoTrash.textContent = totalText;

        renderTable(tabKey);
      }catch(e){
        state.tabs[tabKey].items = [];
        state.tabs[tabKey].lastPage = 1;
        renderTable(tabKey);
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    function reloadCurrent(){ loadTab(getTabKey()); }

    // Departments
    function deptLabel(d){
      return (d?.title || d?.name || d?.department_title || d?.department_name || d?.label || '').toString().trim()
        || `Department #${d?.id ?? ''}`;
    }

    function setDeptHint(stateKey, text){
      if (deptHintText) deptHintText.textContent = text || '';
      if (deptDot){
        deptDot.classList.remove('ok','bad');
        if (stateKey === 'ok') deptDot.classList.add('ok');
        else if (stateKey === 'bad') deptDot.classList.add('bad');
      }
    }

    function hydrateDeptSelect(selectedValue=''){
      if (!departmentSel) return;

      const options = [];
      options.push(`<option value="">General (No Department)</option>`);

      (state.departments || []).forEach(d => {
        const id = (d?.id ?? d?.department_id ?? '').toString();
        if (!id) return;
        const name = deptLabel(d);
        options.push(`<option value="${esc(id)}">${esc(name)}</option>`);
      });

      departmentSel.innerHTML = options.join('');
      departmentSel.value = (selectedValue ?? '').toString();
    }

    // ✅ FIXED: Create modal department stuck loading
    // - Cache departments in state.departments
    // - Re-hydrate instantly on Create modal open/reset
    // - Respect View mode (keep disabled)
    async function loadDepartments(selected='', force=false){
      if (!departmentSel) return;

      // If already loaded and not forcing, just hydrate (no fetch)
      if (!force && Array.isArray(state.departments) && state.departments.length){
        hydrateDeptSelect(selected);
        departmentSel.disabled = (itemForm?.dataset.mode === 'view');
        setDeptHint('ok', `${state.departments.length} department(s) loaded`);
        return;
      }

      departmentSel.innerHTML = `<option value="">Loading departments…</option>`;
      departmentSel.disabled = true;
      setDeptHint('', 'Loading departments…');

      try{
        const res = await fetchWithTimeout('/api/departments', {
          headers: {
            ...authHeaders(),
            'X-UI-Mode': 'dropdown',
            'X-Dropdown': '1'
          }
        }, 15000);

        const js = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(js?.message || 'Failed to load departments');

        const list = Array.isArray(js.data) ? js.data : [];
        state.departments = list;

        hydrateDeptSelect(selected);

        // enable unless view mode
        departmentSel.disabled = (itemForm?.dataset.mode === 'view');

        setDeptHint('ok', `${list.length} department(s) loaded`);
      }catch(ex){
        state.departments = [];
        departmentSel.innerHTML = `<option value="">General (No Department)</option>`;
        departmentSel.disabled = (itemForm?.dataset.mode === 'view') ? true : false;
        setDeptHint('bad', ex?.name === 'AbortError' ? 'Department load timed out' : (ex.message || 'Failed to load departments'));
      }
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
      loadTab(tab);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Filters
    searchInput?.addEventListener('input', debounce(() => {
      state.filters.q = (searchInput.value || '').trim();
      state.tabs.active.page = state.tabs.draft.page = state.tabs.trash.page = 1;
      reloadCurrent();
    }, 320));

    perPageSel?.addEventListener('change', () => {
      state.perPage = parseInt(perPageSel.value, 10) || 20;
      state.tabs.active.page = state.tabs.draft.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    filterModalEl?.addEventListener('show.bs.modal', () => {
      if (!modalStatus || !modalSort) return;
      modalStatus.value = state.filters.status || '';
      modalSort.value = state.filters.sort || '-created_at';
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.status = modalStatus?.value || '';
      state.filters.sort = modalSort?.value || '-created_at';
      state.tabs.active.page = state.tabs.draft.page = state.tabs.trash.page = 1;
      filterModal && filterModal.hide();
      reloadCurrent();
    });

    btnReset?.addEventListener('click', () => {
      state.filters = { q:'', status:'', sort:'-created_at' };
      state.perPage = 20;

      if (searchInput) searchInput.value = '';
      if (perPageSel) perPageSel.value = '20';
      if (modalStatus) modalStatus.value = '';
      if (modalSort) modalSort.value = '-created_at';

      state.tabs.active.page = state.tabs.draft.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    document.querySelector('a[href="#tab-active"]')?.addEventListener('shown.bs.tab', () => loadTab('active'));
    document.querySelector('a[href="#tab-draft"]')?.addEventListener('shown.bs.tab', () => loadTab('draft'));
    document.querySelector('a[href="#tab-trash"]')?.addEventListener('shown.bs.tab', () => loadTab('trash'));

    // ---------- ACTION DROPDOWN FIX ----------
    function closeAllDropdownsExcept(exceptToggle){
      document.querySelectorAll('.nt-dd-toggle').forEach(t => {
        if (t === exceptToggle) return;
        try{
          const inst = bootstrap.Dropdown.getInstance(t);
          inst && inst.hide();
        }catch(_){}
      });
    }

    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('.nt-dd-toggle');
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

    document.addEventListener('click', (e) => {
      if (e.target.closest('.nt-dd-toggle')) return;
      if (e.target.closest('.dropdown-menu')) return;
      closeAllDropdownsExcept(null);
    }, { capture:true });
    // ----------------------------------------

    // -------- RTE --------
    const rte = {
      wrap: $('bodyWrap'),
      toolbar: document.querySelector('#bodyWrap .rte-toolbar'),
      editor: $('bodyEditor'),
      code: $('bodyCode'),
      hidden: $('body'),
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
      const tab = e.target.closest('#bodyWrap .rte-tabs .tab');
      if (tab){ setRteMode(tab.dataset.mode); return; }

      const btn = e.target.closest('#bodyWrap .rte-toolbar .rte-btn');
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

    // -------- cover preview --------
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

    // -------- modal helpers --------
    let saving = false;
    let slugDirty = false;
    let settingSlug = false;

    function setBtnLoading(btn, loading){
      if (!btn) return;
      btn.disabled = !!loading;
      btn.classList.toggle('btn-loading', !!loading);
    }

    function normalizeAttachments(r){
      let a = r?.attachments || r?.attachments_json || r?.attachments_list || null;
      if (typeof a === 'string') { try{ a = JSON.parse(a); }catch(_){ a=null; } }
      return Array.isArray(a) ? a : [];
    }

    function resetForm(){
      itemForm?.reset();
      itemUuid.value = '';
      itemId.value = '';

      slugDirty = false;
      settingSlug = false;

      // ✅ FIXED: don't leave department stuck at "Loading..."
      // If already loaded, hydrate instantly; else fetch.
      if (Array.isArray(state.departments) && state.departments.length){
        hydrateDeptSelect('');
        departmentSel.disabled = false;
        setDeptHint('ok', `${state.departments.length} department(s) loaded`);
      } else {
        if (departmentSel){
          departmentSel.innerHTML = `<option value="">Loading departments…</option>`;
          departmentSel.value = '';
          departmentSel.disabled = true;
          setDeptHint('', 'Loading departments…');
        }
        // fetch fresh
        loadDepartments('', true);
      }

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

    function toLocal(s){
      if (!s) return '';
      const t = String(s).replace(' ', 'T');
      return t.length >= 16 ? t.slice(0,16) : t;
    }

    function fillFormFromRow(r, viewOnly=false){
      itemUuid.value = r.uuid || '';
      itemId.value = r.id || '';

      const deptId = (r.department_id ?? r?.department?.id ?? r?.department?.department_id ?? '').toString();

      titleInput.value = r.title || '';
      slugInput.value = r.slug || '';
      sortOrderInput.value = String(r.sort_order ?? 0);

      statusSel.value = (r.status || (r.active ? 'published' : 'draft') || 'draft');
      publishAtInput.value = toLocal(r.publish_at);
      expireAtInput.value = toLocal(r.expire_at);

      const bodyHtml = (r.body ?? r.body_html ?? r.body_content ?? '') || '';
      if (rte.editor) rte.editor.innerHTML = ensurePreHasCode(bodyHtml);
      syncRteToCode();
      setRteMode('text');

      const coverUrl = r.cover_image_url || r.cover_url || r.cover_image || '';
      if (coverUrl){
        const meta = r.cover_original_name ? `${r.cover_original_name}${r.cover_file_size ? ' • ' + bytes(r.cover_file_size) : ''}` : '';
        clearCoverPreview(true);
        setCoverPreview(coverUrl, meta);
      } else {
        clearCoverPreview(true);
      }

      const atts = normalizeAttachments(r);
      if (atts.length){
        if (currentAttachmentsInfo) currentAttachmentsInfo.style.display = '';
        if (currentAttachmentsText) currentAttachmentsText.textContent = `${atts.length} file(s) attached`;
      } else {
        if (currentAttachmentsInfo) currentAttachmentsInfo.style.display = 'none';
        if (currentAttachmentsText) currentAttachmentsText.textContent = '—';
      }

      slugDirty = true;

      // ✅ Load departments and set selected
      loadDepartments(deptId);

      if (!viewOnly) {
        setTimeout(() => updatePublishOption(), 50);
      }

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
        ...(state.tabs.active.items || []),
        ...(state.tabs.draft.items || []),
        ...(state.tabs.trash.items || []),
      ];
      return all.find(x => x?.uuid === uuid) || null;
    }

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
      if (itemModalTitle) itemModalTitle.textContent = 'Add Notice';
      itemForm.dataset.intent = 'create';
      itemModal && itemModal.show();
    });

    itemModalEl?.addEventListener('hidden.bs.modal', () => {
      if (coverObjectUrl){ try{ URL.revokeObjectURL(coverObjectUrl); }catch(_){ } coverObjectUrl=null; }
    });

    // ✅ helper: publish/activate from draft -> published (moves to Active tab)
    async function setNoticeStatus(uuid, nextStatus){
      showLoading(true);
      try{
        const fd = new FormData();
        fd.append('_method', 'PATCH');
        fd.append('status', nextStatus);

        const res = await fetchWithTimeout(`/api/notices/${encodeURIComponent(uuid)}`, {
          method: 'POST',
          headers: authHeaders(),
          body: fd
        }, 20000);

        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Update failed');

        ok(nextStatus === 'published' ? 'Published' : 'Updated');
        state.tabs.active.page = state.tabs.draft.page = state.tabs.trash.page = 1;
        await Promise.all([loadTab('active'), loadTab('draft'), loadTab('trash')]);
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        showLoading(false);
      }
    }

    // Row actions
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;

      const tr = btn.closest('tr');
      const uuid = tr?.dataset?.uuid;
      const act = btn.dataset.action;
      if (!uuid) return;

      const row = findRowByUuid(uuid);

      const toggle = btn.closest('.dropdown')?.querySelector('.nt-dd-toggle');
      if (toggle) { try { bootstrap.Dropdown.getInstance(toggle)?.hide(); } catch (_) {} }

      if (act === 'view' || act === 'edit'){
        if (act === 'edit' && !canEdit) return;
        resetForm();
        if (itemModalTitle) itemModalTitle.textContent = act === 'view' ? 'View Notice' : 'Edit Notice';
        fillFormFromRow(row || {}, act === 'view');
        itemModal && itemModal.show();
        return;
      }

      if (act === 'activate' || act === 'publish'){
        const conf = await Swal.fire({
          title: (act === 'publish') ? 'Publish this notice?' : 'Activate this notice?',
          text: 'This will move the notice to Active.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'OK',
        });
        if (!conf.isConfirmed) return;

        await setNoticeStatus(uuid, 'published');
        return;
      }

      if (act === 'make-publish'){
        if (!canPublish) return;

        const conf = await Swal.fire({
          title: 'Publish this notice?',
          text: 'This will make the notice visible to the public.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Publish',
          confirmButtonColor: '#10b981'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const fd = new FormData();
          fd.append('status', 'published');
          fd.append('_method', 'PATCH');

          const res = await fetchWithTimeout(`/api/notices/${encodeURIComponent(uuid)}`, {
            method: 'POST',
            headers: authHeaders(),
            body: fd
          }, 15000);

          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Publish failed');

          ok('Notice published successfully');
          await Promise.all([loadTab('active'), loadTab('draft'), loadTab('trash')]);
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'mark-draft'){
        if (!canPublish) return;

        const conf = await Swal.fire({
          title: 'Mark as Draft?',
          text: 'This will hide the notice from the public.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Mark as Draft',
          confirmButtonColor: '#f59e0b'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const fd = new FormData();
          fd.append('status', 'draft');
          fd.append('_method', 'PATCH');

          const res = await fetchWithTimeout(`/api/notices/${encodeURIComponent(uuid)}`, {
            method: 'POST',
            headers: authHeaders(),
            body: fd
          }, 15000);

          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Update failed');

          ok('Marked as draft');
          await Promise.all([loadTab('active'), loadTab('draft'), loadTab('trash')]);
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
          title: 'Delete this notice?',
          text: 'This will move the item to Trash.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/notices/${encodeURIComponent(uuid)}`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Delete failed');

          ok('Moved to trash');
          await Promise.all([loadTab('active'), loadTab('draft'), loadTab('trash')]);
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
          const res = await fetchWithTimeout(`/api/notices/${encodeURIComponent(uuid)}/restore`, {
            method: 'POST',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Restore failed');

          ok('Restored');
          await Promise.all([loadTab('trash'), loadTab('active'), loadTab('draft')]);
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
          text: 'This cannot be undone (files will be removed).',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete Permanently',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/notices/${encodeURIComponent(uuid)}/force`, {
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

    // Submit create/edit
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
        const slug = (slugInput.value || '').trim();
        const status = (statusSel.value || 'draft').trim();
        const sortOrder = String(parseInt(sortOrderInput.value || '0', 10) || 0);

        const rawBody = (rte.mode === 'code') ? (rte.code.value || '') : (rte.editor.innerHTML || '');
        const cleanBody = ensurePreHasCode(rawBody).trim();
        if (rte.hidden) rte.hidden.value = cleanBody;

        if (!title){ err('Title is required'); titleInput.focus(); return; }
        if (!cleanBody){ err('Body is required'); rteFocus(); return; }

        const fd = new FormData();

        const deptId = (departmentSel?.value || '').toString().trim();
        if (deptId) fd.append('department_id', deptId);

        fd.append('title', title);
        if (slug) fd.append('slug', slug);
        fd.append('status', status);
        fd.append('sort_order', sortOrder);
        if ((publishAtInput.value || '').trim()) fd.append('publish_at', publishAtInput.value);
        if ((expireAtInput.value || '').trim()) fd.append('expire_at', expireAtInput.value);
        fd.append('body', cleanBody);

        const cover = coverInput.files?.[0] || null;
        if (cover) fd.append('cover_image', cover);
        Array.from(attachmentsInput.files || []).forEach(f => fd.append('attachments[]', f));

        const url = isEdit
          ? `/api/notices/${encodeURIComponent(itemUuid.value)}`
          : `/api/notices`;

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

        state.tabs.active.page = state.tabs.draft.page = state.tabs.trash.page = 1;
        await Promise.all([loadTab('active'), loadTab('draft'), loadTab('trash')]);
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
        await loadDepartments();
        await Promise.all([loadTab('active'), loadTab('draft'), loadTab('trash')]);
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
