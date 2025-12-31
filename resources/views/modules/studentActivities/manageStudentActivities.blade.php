{{-- resources/views/modules/studentActivity/manageStudentActivities.blade.php --}}
@section('title','Student Activities')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =====================
  Page shell
===================== */
.sa-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}
.sa-panel{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:16px;
  box-shadow:var(--shadow-2);
  padding:14px;
  overflow:visible;
}

/* Tabs */
.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface)
}
.tab-content,.tab-pane{overflow:visible}

/* Table card */
.sa-table.card{
  position:relative;
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.sa-table .card-body{overflow:visible}
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

/* Toolbar */
.sa-toolbar .form-control,
.sa-toolbar .form-select{
  height:40px;
  border-radius:12px;
  border:1px solid var(--line-strong);
  background:var(--surface);
}
.sa-toolbar .btn{border-radius:12px}

/* Dropdown in table */
.sa-table .dropdown{position:relative}
/* ✅ match reference: use a dedicated toggle class, manually controlled via JS */
.sa-table .sa-dd-toggle{border-radius:10px}
.sa-table .dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:240px;
  z-index:99999; /* ✅ higher + will be positioned with strategy:fixed */
}
.sa-table .dropdown-menu.show{display:block !important}
.sa-table .dropdown-item{display:flex;align-items:center;gap:.6rem}
.sa-table .dropdown-item i{width:16px;text-align:center}
.sa-table .dropdown-item.text-danger{color:var(--danger-color) !important}

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
.badge-soft-primary{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  color:var(--primary-color)
}

/* Responsive + horizontal scroll */
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
@media (max-width: 576px){ .table-responsive > .table{min-width:1120px;} }

@media (max-width: 768px){
  .sa-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .sa-toolbar .position-relative{min-width:100% !important}
  .sa-toolbar .toolbar-actions{display:flex;gap:8px;flex-wrap:wrap}
  .sa-toolbar .toolbar-actions .btn{flex:1;min-width:130px}
}

/* Loading overlay */
.sa-loading{
  position:fixed;inset:0;
  background:rgba(0,0,0,.45);
  display:none;
  align-items:center;
  justify-content:center;
  z-index:9999;
  backdrop-filter:blur(2px);
}
.sa-loading .box{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,0.3)
}
.sa-loading .spin{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,0.3);
  border-top:4px solid var(--primary-color);
  animation:saSpin 1s linear infinite
}
@keyframes saSpin{to{transform:rotate(360deg)}}

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
  animation:saSpin 1s linear infinite;
}

/* =========================
  Mini RTE (stable caret)
========================= */
.rte-help{font-size:12px;color:var(--muted-color);margin-top:6px}
.rte-box{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:var(--surface);
}
.rte-bar{
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
.rte-modes{
  margin-left:auto;
  display:flex;
  border:1px solid var(--line-soft);
  border-radius:0;
  overflow:hidden;
}
.rte-modes button{
  border:0;border-right:1px solid var(--line-soft);
  border-radius:0;
  padding:7px 12px;
  font-size:12px;
  cursor:pointer;
  background:transparent;
  color:var(--ink);
  line-height:1;
}
.rte-modes button:last-child{border-right:0}
.rte-modes button.active{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  font-weight:800;
}
.rte-area{position:relative}
.rte-editor{
  min-height:220px;
  padding:12px;
  outline:none;
}
.rte-editor:empty:before{content:attr(data-placeholder);color:var(--muted-color)}
.rte-code{
  display:none;
  width:100%;
  min-height:220px;
  padding:12px;
  border:0;
  outline:none;
  resize:vertical;
  background:transparent;
  color:var(--ink);
  font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
  font-size:12.5px;
  line-height:1.45;
}
.rte-box.mode-code .rte-editor{display:none}
.rte-box.mode-code .rte-code{display:block}

/* Cover preview */
.cover-box{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:color-mix(in oklab, var(--surface) 88%, var(--bg-body));
}
.cover-box .top{
  display:flex;align-items:center;justify-content:space-between;gap:10px;
  padding:10px 12px;
  border-bottom:1px solid var(--line-soft);
}
.cover-box .body{padding:12px}
.cover-box img{
  width:100%;
  max-height:260px;
  object-fit:cover;
  border-radius:12px;
  border:1px solid var(--line-soft);
  background:#fff;
}
.cover-empty{
  padding:12px;
  border:1px dashed var(--line-soft);
  border-radius:12px;
  color:var(--muted-color);
  font-size:12.5px;
}
</style>
@endpush

@section('content')
<div class="sa-wrap">

  {{-- Global loading --}}
  <div id="saLoading" class="sa-loading">
    @include('partials.overlay')
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#sa-tab-active" role="tab" aria-selected="true">
        <i class="fa-solid fa-bolt me-2"></i>Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#sa-tab-inactive" role="tab" aria-selected="false">
        <i class="fa-solid fa-circle-pause me-2"></i>Inactive
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#sa-tab-trash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Trash
      </a>
    </li>
  </ul>

  {{-- Toolbar (shared) --}}
  <div class="row align-items-center g-2 mb-3 sa-toolbar sa-panel">
    <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">
      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Per Page</label>
        <select id="saPerPage" class="form-select" style="width:96px;">
          <option>10</option>
          <option selected>20</option>
          <option>50</option>
          <option>100</option>
        </select>
      </div>

      <div class="position-relative" style="min-width:280px;">
        <input id="saSearch" type="search" class="form-control ps-5" placeholder="Search by title or slug…">
        <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
      </div>

      <button id="saBtnFilter" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#saFilterModal">
        <i class="fa fa-sliders me-1"></i>Filter
      </button>

      <button id="saBtnReset" class="btn btn-light">
        <i class="fa fa-rotate-left me-1"></i>Reset
      </button>
    </div>

    <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
      <div class="toolbar-actions" id="saWriteControls" style="display:none;">
        <button type="button" class="btn btn-primary" id="saBtnAdd">
          <i class="fa fa-plus me-1"></i>Add Student Activity
        </button>
      </div>
    </div>
  </div>

  <div class="tab-content mb-3">

    {{-- ACTIVE --}}
    <div class="tab-pane fade show active" id="sa-tab-active" role="tabpanel">
      <div class="card sa-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:190px;">Department</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:120px;">Featured</th>
                  <th style="width:160px;">Publish At</th>
                  <th style="width:110px;">Views</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:110px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="saTbodyActive">
                <tr><td colspan="9" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="saEmptyActive" class="p-4 text-center" style="display:none;">
            <i class="fa-solid fa-bolt mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No active student activities found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="saInfoActive">—</div>
            <nav><ul id="saPagerActive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- INACTIVE --}}
    <div class="tab-pane fade" id="sa-tab-inactive" role="tabpanel">
      <div class="card sa-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:190px;">Department</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:120px;">Featured</th>
                  <th style="width:160px;">Publish At</th>
                  <th style="width:110px;">Views</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:110px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="saTbodyInactive">
                <tr><td colspan="9" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="saEmptyInactive" class="p-4 text-center" style="display:none;">
            <i class="fa-solid fa-circle-pause mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No inactive student activities found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="saInfoInactive">—</div>
            <nav><ul id="saPagerInactive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- TRASH --}}
    <div class="tab-pane fade" id="sa-tab-trash" role="tabpanel">
      <div class="card sa-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:220px;">Department</th>
                  <th style="width:180px;">Deleted</th>
                  <th style="width:110px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="saTbodyTrash">
                <tr><td colspan="5" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="saEmptyTrash" class="p-4 text-center" style="display:none;">
            <i class="fa-solid fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>Trash is empty.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="saInfoTrash">—</div>
            <nav><ul id="saPagerTrash" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="saFilterModal" tabindex="-1" aria-hidden="true">
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
            <select id="saModalStatus" class="form-select">
              <option value="">(Tab default)</option>
              <option value="published">Published</option>
              <option value="draft">Draft</option>
              <option value="archived">Archived</option>
            </select>
            <div class="form-text">If you leave this as “Tab default”, Active = Published and Inactive = Draft.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="saModalSort" class="form-select">
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="title">Title A-Z</option>
              <option value="-title">Title Z-A</option>
              <option value="-publish_at">Publish At (Desc)</option>
              <option value="publish_at">Publish At (Asc)</option>
              <option value="-expire_at">Expire At (Desc)</option>
              <option value="expire_at">Expire At (Asc)</option>
              <option value="-views_count">Most Viewed</option>
              <option value="views_count">Least Viewed</option>
              <option value="-id">ID (Desc)</option>
              <option value="id">ID (Asc)</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Featured</label>
            <select id="saModalFeatured" class="form-select">
              <option value="">Any</option>
              <option value="1">Featured only</option>
              <option value="0">Not featured</option>
            </select>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="saBtnApplyFilters" class="btn btn-primary">
          <i class="fa fa-check me-1"></i>Apply
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Add/Edit/View Modal --}}
<div class="modal fade" id="saItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form class="modal-content" id="saItemForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="saItemModalTitle">Add Student Activity</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="saUuid">
        <input type="hidden" id="saId">
        <input type="hidden" id="saCoverRemove" value="0">

        <div class="row g-3">
          <div class="col-lg-6">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input class="form-control" id="saTitle" required maxlength="255" placeholder="e.g., Tech Fest 2025 Highlights">
              </div>

              <div class="col-12">
                <label class="form-label">Slug (optional)</label>
                <input class="form-control" id="saSlug" maxlength="160" placeholder="tech-fest-2025-highlights">
                <div class="form-text">Auto-generated from title until you edit this field manually.</div>
              </div>

              {{-- ✅ Department (added) --}}
              <div class="col-12">
                <label class="form-label">Department <span class="text-danger">*</span></label>
                <select class="form-select" id="saDepartmentId" required>
                  <option value="">Loading departments…</option>
                </select>
                <div class="form-text">Select the department (dropdown shows only the department name).</div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select" id="saStatus">
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="archived">Archived</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Featured on Home</label>
                <select class="form-select" id="saFeatured">
                  <option value="0">No</option>
                  <option value="1">Yes</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Publish At</label>
                <input type="datetime-local" class="form-control" id="saPublishAt">
              </div>

              <div class="col-md-6">
                <label class="form-label">Expire At</label>
                <input type="datetime-local" class="form-control" id="saExpireAt">
              </div>

              <div class="col-12">
                <label class="form-label">Cover Image (optional)</label>
                <input type="file" class="form-control" id="saCover" accept="image/*">
                <div class="form-text">Upload an image (optional).</div>
              </div>

              <div class="col-12">
                <label class="form-label">Attachments (optional)</label>
                <input type="file" class="form-control" id="saAttachments" multiple>
                <div class="form-text">Optional multiple attachments.</div>
                <div class="small text-muted mt-2" id="saCurrentAttachmentsInfo" style="display:none;">
                  <i class="fa fa-paperclip me-1"></i><span id="saCurrentAttachmentsText">—</span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            {{-- RTE --}}
            <div class="mb-2">
              <label class="form-label">Body (HTML allowed) <span class="text-danger">*</span></label>

              <div class="rte-box" id="saRteBox">
                <div class="rte-bar">
                  <button type="button" class="rte-btn" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
                  <button type="button" class="rte-btn" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
                  <button type="button" class="rte-btn" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>

                  <span class="rte-sep"></span>

                  <button type="button" class="rte-btn" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
                  <button type="button" class="rte-btn" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>

                  <span class="rte-sep"></span>

                  <button type="button" class="rte-btn" data-block="h2" title="Heading">H2</button>
                  <button type="button" class="rte-btn" data-block="h3" title="Subheading">H3</button>

                  <span class="rte-sep"></span>

                  <button type="button" class="rte-btn" data-insert="pre" title="Code Block"><i class="fa fa-code"></i></button>
                  <button type="button" class="rte-btn" data-insert="code" title="Inline Code"><i class="fa fa-terminal"></i></button>

                  <span class="rte-sep"></span>

                  <button type="button" class="rte-btn" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>

                  <div class="rte-modes">
                    <button type="button" class="active" data-mode="text">Text</button>
                    <button type="button" data-mode="code">Code</button>
                  </div>
                </div>

                <div class="rte-area">
                  <div id="saBodyEditor" class="rte-editor" contenteditable="true" data-placeholder="Write student activity content…"></div>
                  <textarea id="saBodyCode" class="rte-code" spellcheck="false" placeholder="HTML code…"></textarea>
                </div>
              </div>

              <div class="rte-help">Use <b>Text</b> for rich editing or <b>Code</b> to paste HTML.</div>
              <input type="hidden" id="saBody">
            </div>

            {{-- Cover preview --}}
            <div class="cover-box mt-3">
              <div class="top">
                <div class="fw-semibold"><i class="fa fa-image me-2"></i>Cover Preview</div>
                <div class="d-flex align-items-center gap-2">
                  <button type="button" class="btn btn-light btn-sm" id="saBtnOpenCover" style="display:none;">
                    <i class="fa fa-up-right-from-square me-1"></i>Open
                  </button>
                  <button type="button" class="btn btn-outline-danger btn-sm" id="saBtnRemoveCover" style="display:none;">
                    <i class="fa fa-trash me-1"></i>Remove
                  </button>
                </div>
              </div>
              <div class="body">
                <img id="saCoverPreview" src="" alt="Cover preview" style="display:none;">
                <div id="saCoverEmpty" class="cover-empty">No cover selected.</div>
                <div class="text-muted small mt-2" id="saCoverMeta" style="display:none;">—</div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="saSaveBtn">
          <i class="fa fa-floppy-disk me-1"></i>Save
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="saToastOk" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="saToastOkText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="saToastErr" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="saToastErrText">Something went wrong</div>
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
  if (window.__STUDENT_ACTIVITIES_MODULE_INIT__) return;
  window.__STUDENT_ACTIVITIES_MODULE_INIT__ = true;

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

  function toLocal(s){
    if (!s) return '';
    const t = String(s).replace(' ', 'T');
    return t.length >= 16 ? t.slice(0,16) : t;
  }

  function ensurePreHasCode(html){
    return (html || '').replace(/<pre>([\s\S]*?)<\/pre>/gi, (m, inner) => {
      if (/<code[\s>]/i.test(inner)) return `<pre>${inner}</pre>`;
      return `<pre><code>${inner}</code></pre>`;
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const loadingEl = $('saLoading');
    const showLoading = (v) => { if (loadingEl) loadingEl.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('saToastOk');
    const toastErrEl = $('saToastErr');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { const el=$('saToastOkText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('saToastErrText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = () => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    });

    // Toolbar controls
    const perPageSel = $('saPerPage');
    const searchInput = $('saSearch');
    const btnReset = $('saBtnReset');
    const btnApplyFilters = $('saBtnApplyFilters');
    const writeControls = $('saWriteControls');
    const btnAdd = $('saBtnAdd');

    // Filter modal
    const filterModalEl = $('saFilterModal');
    const filterModal = filterModalEl ? new bootstrap.Modal(filterModalEl) : null;
    const modalStatus = $('saModalStatus');
    const modalSort = $('saModalSort');
    const modalFeatured = $('saModalFeatured');

    // Tables
    const tbodyActive = $('saTbodyActive');
    const tbodyInactive = $('saTbodyInactive');
    const tbodyTrash = $('saTbodyTrash');

    const emptyActive = $('saEmptyActive');
    const emptyInactive = $('saEmptyInactive');
    const emptyTrash = $('saEmptyTrash');

    const pagerActive = $('saPagerActive');
    const pagerInactive = $('saPagerInactive');
    const pagerTrash = $('saPagerTrash');

    const infoActive = $('saInfoActive');
    const infoInactive = $('saInfoInactive');
    const infoTrash = $('saInfoTrash');

    // Item modal
    const itemModalEl = $('saItemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;
    const itemModalTitle = $('saItemModalTitle');
    const itemForm = $('saItemForm');
    const saveBtn = $('saSaveBtn');

    const fUuid = $('saUuid');
    const fId = $('saId');
    const fCoverRemove = $('saCoverRemove');

    const fTitle = $('saTitle');
    const fSlug = $('saSlug');

    // ✅ Department dropdown (added)
    const fDepartmentId = $('saDepartmentId');

    const fStatus = $('saStatus');
    const fFeatured = $('saFeatured');
    const fPublishAt = $('saPublishAt');
    const fExpireAt = $('saExpireAt');
    const fCover = $('saCover');
    const fAttachments = $('saAttachments');

    const currentAttachmentsInfo = $('saCurrentAttachmentsInfo');
    const currentAttachmentsText = $('saCurrentAttachmentsText');

    const btnOpenCover = $('saBtnOpenCover');
    const btnRemoveCover = $('saBtnRemoveCover');
    const coverPreview = $('saCoverPreview');
    const coverEmpty = $('saCoverEmpty');
    const coverMeta = $('saCoverMeta');

    // RTE
    const rte = {
      box: $('saRteBox'),
      bar: document.querySelector('#saRteBox .rte-bar'),
      editor: $('saBodyEditor'),
      code: $('saBodyCode'),
      hidden: $('saBody'),
      mode: 'text',
      enabled: true
    };

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

    // =========================
    // ✅ Departments (added)
    // =========================
    let departmentsLoaded = false;
    let departments = []; // [{id, name}]
    let pendingDeptId = '';

    function pickDeptName(d){
      return (d?.name ?? d?.title ?? d?.department_name ?? d?.department_title ?? d?.label ?? '').toString().trim();
    }
    function pickDeptId(d){
      const v = (d?.id ?? d?.department_id ?? d?.dept_id ?? d?.value);
      return (v === 0 || v === '0' || v) ? String(v) : '';
    }

    function setDeptSelectOptions(list){
      if (!fDepartmentId) return;

      const selected = (fDepartmentId.value || '').toString();
      const hasList = Array.isArray(list) && list.length;

      const opts = [];
      opts.push(`<option value="">Select department…</option>`);
      if (hasList){
        for (const d of list){
          const id = String(d.id);
          const name = (d.name || '').toString();
          if (!id || !name) continue;
          opts.push(`<option value="${esc(id)}">${esc(name)}</option>`);
        }
      } else {
        opts.push(`<option value="" disabled>(Unable to load departments)</option>`);
      }

      fDepartmentId.innerHTML = opts.join('');

      // re-apply selection if any
      if (pendingDeptId){
        fDepartmentId.value = String(pendingDeptId);
        pendingDeptId = '';
      } else if (selected){
        fDepartmentId.value = selected;
      }
    }

    function extractDepartmentsFromResponse(js){
      // supports shapes: {data:[]}, {data:{data:[]}}, {departments:[]}, [] etc.
      let arr = [];
      if (Array.isArray(js)) arr = js;
      else if (Array.isArray(js?.data)) arr = js.data;
      else if (Array.isArray(js?.data?.data)) arr = js.data.data;
      else if (Array.isArray(js?.departments)) arr = js.departments;
      else if (Array.isArray(js?.items)) arr = js.items;
      else arr = [];

      const out = [];
      const seen = new Set();
      for (const d of arr){
        const id = pickDeptId(d);
        const name = pickDeptName(d);
        if (!id || !name) continue;
        if (seen.has(id)) continue;
        seen.add(id);
        out.push({ id, name });
      }
      return out;
    }

    async function loadDepartments(){
      if (departmentsLoaded) return;
      if (!fDepartmentId) { departmentsLoaded = true; return; }

      // keep UI stable
      fDepartmentId.innerHTML = `<option value="">Loading departments…</option>`;
      fDepartmentId.disabled = true;

      const urls = [
        '/api/departments?per_page=500&page=1',
        '/api/departments',
      ];

      for (const url of urls){
        try{
          const res = await fetchWithTimeout(url, { headers: authHeaders() }, 12000);
          if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }
          if (!res.ok) continue;

          const js = await res.json().catch(()=> ({}));
          const list = extractDepartmentsFromResponse(js);
          if (list.length){
            departments = list;
            departmentsLoaded = true;
            setDeptSelectOptions(departments);
            fDepartmentId.disabled = false;
            return;
          }
        }catch(_){
          // try next url
        }
      }

      // failed
      departmentsLoaded = true;
      departments = [];
      setDeptSelectOptions([]);
      fDepartmentId.disabled = false; // keep selectable (shows fallback option)
    }

    function setDepartmentValue(id){
      if (!fDepartmentId) return;
      const v = (id === 0 || id === '0' || id) ? String(id) : '';
      if (departmentsLoaded){
        fDepartmentId.value = v;
      } else {
        pendingDeptId = v;
      }
    }

    // State
    const state = {
      filters: { q:'', status:'', featured:'', sort:'-created_at' },
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      tabs: {
        active:   { page:1, lastPage:1, items:[] },
        inactive: { page:1, lastPage:1, items:[] },
        trash:    { page:1, lastPage:1, items:[] }
      }
    };

    function getTabKey(){
      const a = document.querySelector('.nav-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#sa-tab-active';
      if (href === '#sa-tab-inactive') return 'inactive';
      if (href === '#sa-tab-trash') return 'trash';
      return 'active';
    }

    function defaultStatusForTab(tabKey){
      if (tabKey === 'active') return 'published';
      if (tabKey === 'inactive') return 'draft';
      return '';
    }

    function buildUrl(tabKey){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state.tabs[tabKey].page));

      const q = (state.filters.q || '').trim();
      if (q) params.set('q', q);

      // sort + direction
      const s = state.filters.sort || '-created_at';
      params.set('sort', s.startsWith('-') ? s.slice(1) : s);
      params.set('direction', s.startsWith('-') ? 'desc' : 'asc');

      if (tabKey === 'trash'){
        params.set('only_trashed', '1');
      } else {
        const st = (state.filters.status || '').trim() || defaultStatusForTab(tabKey);
        if (st) params.set('status', st);
      }

      if (state.filters.featured !== '') params.set('featured', state.filters.featured);

      return `/api/student-activities?${params.toString()}`;
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

    function findRowByUuid(uuid){
      const all = [
        ...(state.tabs.active.items || []),
        ...(state.tabs.inactive.items || []),
        ...(state.tabs.trash.items || []),
      ];
      return all.find(x => x?.uuid === uuid) || null;
    }

    // ✅ Same pattern as reference (Contact Info):
    // action toggle has NO data-bs-toggle; we manually open with Popper strategy:fixed
    function rowActions(tabKey, status){
      let menu = `
        <div class="dropdown text-end">
          <button type="button"
            class="btn btn-light btn-sm sa-dd-toggle"
            aria-expanded="false" title="Actions">
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>`;

      if (tabKey !== 'trash' && canEdit){
        menu += `<li><button type="button" class="dropdown-item" data-action="edit"><i class="fa fa-pen-to-square"></i> Edit</button></li>`;
        menu += `<li><button type="button" class="dropdown-item" data-action="toggleFeatured"><i class="fa fa-star"></i> Toggle Featured</button></li>`;

        const s = (status || '').toLowerCase();
        if (s !== 'published'){
          menu += `<li><button type="button" class="dropdown-item" data-action="markPublished"><i class="fa fa-circle-check"></i> Mark Published</button></li>`;
        } else {
          menu += `<li><button type="button" class="dropdown-item" data-action="markDraft"><i class="fa fa-circle-pause"></i> Mark Draft</button></li>`;
        }
      }

      if (tabKey !== 'trash'){
        if (canDelete){
          menu += `<li><hr class="dropdown-divider"></li>
            <li><button type="button" class="dropdown-item text-danger" data-action="delete"><i class="fa fa-trash"></i> Delete</button></li>`;
        }
      } else {
        menu += `<li><hr class="dropdown-divider"></li>
          <li><button type="button" class="dropdown-item" data-action="restore"><i class="fa fa-rotate-left"></i> Restore</button></li>`;
        if (canDelete){
          menu += `<li><button type="button" class="dropdown-item text-danger" data-action="force"><i class="fa fa-skull-crossbones"></i> Delete Permanently</button></li>`;
        }
      }

      menu += `</ul></div>`;
      return menu;
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
        const uuid = r.uuid || '';
        const title = r.title || '—';
        const slug = r.slug || '—';
        const dept = r.department_title || '—';
        const status = (r.status || '').toString();
        const featured = !!(r.is_featured_home ?? 0);
        const publishAt = r.publish_at || '—';
        const updated = r.updated_at || '—';
        const views = (r.views_count ?? 0);
        const deleted = r.deleted_at || '—';

        const menu = rowActions(tabKey, status);

        if (tabKey === 'trash'){
          return `
            <tr data-uuid="${esc(uuid)}">
              <td class="fw-semibold">${esc(title)}</td>
              <td class="col-slug"><code>${esc(slug)}</code></td>
              <td>${esc(dept)}</td>
              <td>${esc(String(deleted))}</td>
              <td class="text-end">${menu}</td>
            </tr>`;
        }

        return `
          <tr data-uuid="${esc(uuid)}">
            <td class="fw-semibold">${esc(title)}</td>
            <td class="col-slug"><code>${esc(slug)}</code></td>
            <td>${esc(dept)}</td>
            <td>${statusBadge(status)}</td>
            <td>${featuredBadge(featured)}</td>
            <td>${esc(String(publishAt))}</td>
            <td>${esc(String(views))}</td>
            <td>${esc(String(updated))}</td>
            <td class="text-end">${menu}</td>
          </tr>`;
      }).join('');

      renderPager(tabKey);
    }

    async function loadTab(tabKey){
      const tbody = tabKey==='active' ? tbodyActive : (tabKey==='inactive' ? tbodyInactive : tbodyTrash);
      if (tbody){
        const cols = (tabKey==='trash') ? 5 : 9;
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

        const label = (p.total ? `${p.total} result(s)` : '—');
        if (tabKey === 'active' && infoActive) infoActive.textContent = label;
        if (tabKey === 'inactive' && infoInactive) infoInactive.textContent = label;
        if (tabKey === 'trash' && infoTrash) infoTrash.textContent = label;

        renderTable(tabKey);
      }catch(e){
        state.tabs[tabKey].items = [];
        state.tabs[tabKey].lastPage = 1;
        renderTable(tabKey);
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    function reloadCurrent(){ loadTab(getTabKey()); }

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
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    }, 320));

    perPageSel?.addEventListener('change', () => {
      state.perPage = parseInt(perPageSel.value, 10) || 20;
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    filterModalEl?.addEventListener('show.bs.modal', () => {
      if (!modalStatus || !modalSort || !modalFeatured) return;
      modalStatus.value = state.filters.status || '';
      modalSort.value = state.filters.sort || '-created_at';
      modalFeatured.value = (state.filters.featured ?? '');
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.status = modalStatus?.value || '';
      state.filters.sort = modalSort?.value || '-created_at';
      state.filters.featured = (modalFeatured?.value ?? '');
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      filterModal && filterModal.hide();
      reloadCurrent();
    });

    btnReset?.addEventListener('click', () => {
      state.filters = { q:'', status:'', featured:'', sort:'-created_at' };
      state.perPage = 20;
      if (searchInput) searchInput.value = '';
      if (perPageSel) perPageSel.value = '20';
      if (modalStatus) modalStatus.value = '';
      if (modalFeatured) modalFeatured.value = '';
      if (modalSort) modalSort.value = '-created_at';
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    // Tab switched
    document.querySelector('a[href="#sa-tab-active"]')?.addEventListener('shown.bs.tab', () => loadTab('active'));
    document.querySelector('a[href="#sa-tab-inactive"]')?.addEventListener('shown.bs.tab', () => loadTab('inactive'));
    document.querySelector('a[href="#sa-tab-trash"]')?.addEventListener('shown.bs.tab', () => loadTab('trash'));

    // =========================
    // ✅ ACTION DROPDOWN FIX (same idea as reference page)
    // =========================
    function closeAllDropdownsExcept(exceptToggle){
      document.querySelectorAll('.sa-dd-toggle').forEach(t => {
        if (t === exceptToggle) return;
        try{
          const inst = bootstrap.Dropdown.getInstance(t);
          inst && inst.hide();
        }catch(_){}
      });
    }

    // toggle dropdown manually with Popper "fixed" strategy (escapes overflow containers)
    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('.sa-dd-toggle');
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

    // click anywhere else closes open dropdowns (capture like reference)
    document.addEventListener('click', () => {
      closeAllDropdownsExcept(null);
    }, { capture: true });

    // =========================
    // RTE
    // =========================
    function rteFocus(){
      try { rte.editor?.focus({ preventScroll:true }); }
      catch(_) { try { rte.editor?.focus(); } catch(__){} }
    }

    function syncEditorToCode(){
      if (!rte.editor || !rte.code) return;
      if (rte.mode === 'text') rte.code.value = ensurePreHasCode(rte.editor.innerHTML || '');
    }

    function setRteMode(mode){
      rte.mode = (mode === 'code') ? 'code' : 'text';
      rte.box?.classList.toggle('mode-code', rte.mode === 'code');

      rte.box?.querySelectorAll('.rte-modes button').forEach(b => {
        b.classList.toggle('active', b.dataset.mode === rte.mode);
      });

      const disableBtns = (rte.mode === 'code') || !rte.enabled;
      rte.box?.querySelectorAll('.rte-btn').forEach(b => {
        b.disabled = disableBtns;
        b.style.opacity = disableBtns ? '0.55' : '';
        b.style.pointerEvents = disableBtns ? 'none' : '';
      });

      if (rte.mode === 'code'){
        rte.code.value = ensurePreHasCode(rte.editor.innerHTML || '');
        setTimeout(()=>{ try{ rte.code?.focus(); }catch(_){ } }, 0);
      } else {
        rte.editor.innerHTML = ensurePreHasCode(rte.code.value || '');
        setTimeout(()=>{ rteFocus(); }, 0);
      }
    }

    function updateActiveBtns(){
      if (!rte.bar || rte.mode !== 'text') return;
      const set = (cmd, on) => {
        const b = rte.bar.querySelector(`.rte-btn[data-cmd="${cmd}"]`);
        if (b) b.classList.toggle('active', !!on);
      };
      try{
        set('bold', document.queryCommandState('bold'));
        set('italic', document.queryCommandState('italic'));
        set('underline', document.queryCommandState('underline'));
      }catch(_){}
    }

    rte.bar?.addEventListener('pointerdown', (e) => { e.preventDefault(); });

    rte.editor?.addEventListener('input', () => { syncEditorToCode(); updateActiveBtns(); });
    ['mouseup','keyup','click'].forEach(ev => rte.editor?.addEventListener(ev, updateActiveBtns));
    document.addEventListener('selectionchange', () => {
      if (document.activeElement === rte.editor) updateActiveBtns();
    });

    document.addEventListener('click', (e) => {
      const modeBtn = e.target.closest('#saRteBox .rte-modes button');
      if (modeBtn){ setRteMode(modeBtn.dataset.mode); return; }

      const btn = e.target.closest('#saRteBox .rte-btn');
      if (!btn || rte.mode !== 'text' || !rte.enabled) return;

      rteFocus();

      const block = btn.getAttribute('data-block');
      const insert = btn.getAttribute('data-insert');
      const cmd = btn.getAttribute('data-cmd');

      if (block){
        try{ document.execCommand('formatBlock', false, `<${block}>`); }catch(_){}
        syncEditorToCode(); updateActiveBtns();
        return;
      }

      if (insert === 'code'){
        const sel = window.getSelection();
        const txt = sel && !sel.isCollapsed ? sel.toString() : '';
        if (txt.trim()){
          document.execCommand('insertHTML', false, `<code>${esc(txt)}</code>`);
        } else {
          document.execCommand('insertHTML', false, `<code>\u200b</code>`);
        }
        syncEditorToCode(); updateActiveBtns();
        return;
      }

      if (insert === 'pre'){
        const sel = window.getSelection();
        const txt = sel && !sel.isCollapsed ? sel.toString() : '';
        if (txt.trim()){
          document.execCommand('insertHTML', false, `<pre><code>${esc(txt)}</code></pre>`);
        } else {
          document.execCommand('insertHTML', false, `<pre><code>\u200b</code></pre>`);
        }
        syncEditorToCode(); updateActiveBtns();
        return;
      }

      if (cmd){
        try{ document.execCommand(cmd, false, null); }catch(_){}
        syncEditorToCode(); updateActiveBtns();
      }
    });

    function setRteEnabled(on){
      rte.enabled = !!on;
      if (rte.editor) rte.editor.setAttribute('contenteditable', on ? 'true' : 'false');
      if (rte.code) rte.code.disabled = !on;

      const disableBtns = (rte.mode === 'code') || !rte.enabled;
      rte.box?.querySelectorAll('.rte-btn').forEach(b => {
        b.disabled = disableBtns;
        b.style.opacity = disableBtns ? '0.55' : '';
        b.style.pointerEvents = disableBtns ? 'none' : '';
      });
      rte.box?.querySelectorAll('.rte-modes button').forEach(b => {
        b.style.pointerEvents = on ? '' : 'none';
        b.style.opacity = on ? '' : '0.7';
      });
    }

    // =========================
    // Cover preview
    // =========================
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
      if (btnRemoveCover){ btnRemoveCover.style.display = 'none'; btnRemoveCover.onclick = null; }
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
      if (btnRemoveCover){
        btnRemoveCover.style.display = '';
      }
    }

    fCover?.addEventListener('change', () => {
      const f = fCover.files?.[0];
      if (!f) { clearCoverPreview(true); return; }

      // selecting a new cover implies NOT removing existing
      if (fCoverRemove) fCoverRemove.value = '0';

      if (coverObjectUrl){
        try{ URL.revokeObjectURL(coverObjectUrl); }catch(_){}
      }
      coverObjectUrl = URL.createObjectURL(f);
      setCoverPreview(coverObjectUrl, `${f.name || 'cover'} • ${bytes(f.size)}`);
      if (btnRemoveCover) btnRemoveCover.style.display = '';
    });

    btnRemoveCover?.addEventListener('click', () => {
      // mark remove + clear preview
      if (fCoverRemove) fCoverRemove.value = '1';
      if (fCover) fCover.value = '';
      clearCoverPreview(true);
      ok('Cover will be removed on save');
    });

    fAttachments?.addEventListener('change', () => {
      const files = Array.from(fAttachments.files || []);
      if (!files.length){
        if (currentAttachmentsInfo) currentAttachmentsInfo.style.display = 'none';
        if (currentAttachmentsText) currentAttachmentsText.textContent = '—';
        return;
      }
      if (currentAttachmentsInfo) currentAttachmentsInfo.style.display = '';
      if (currentAttachmentsText) currentAttachmentsText.textContent = `${files.length} selected`;
    });

    // =========================
    // Modal helpers
    // =========================
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
      fUuid.value = '';
      fId.value = '';
      if (fCoverRemove) fCoverRemove.value = '0';

      // ✅ reset department (added)
      if (fDepartmentId){
        if (!departmentsLoaded){
          pendingDeptId = '';
          fDepartmentId.innerHTML = `<option value="">Loading departments…</option>`;
        } else {
          setDeptSelectOptions(departments);
        }
        fDepartmentId.value = '';
      }

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
        if (el.id === 'saUuid' || el.id === 'saId' || el.id === 'saCoverRemove') return;
        if (el.type === 'file') el.disabled = false;
        else if (el.tagName === 'SELECT') el.disabled = false;
        else el.readOnly = false;
      });

      if (saveBtn) saveBtn.style.display = '';
      itemForm.dataset.mode = 'edit';
      itemForm.dataset.intent = 'create';
    }

    function normalizeAttachments(r){
      let a = r?.attachments || r?.attachments_json || null;
      if (typeof a === 'string') { try{ a = JSON.parse(a); }catch(_){ a=null; } }
      return Array.isArray(a) ? a : [];
    }

    function fillFormFromRow(r, viewOnly=false){
      fUuid.value = r.uuid || '';
      fId.value = r.id || '';
      if (fCoverRemove) fCoverRemove.value = '0';

      fTitle.value = r.title || '';
      fSlug.value = r.slug || '';

      // ✅ set department (added)
      const deptId = r.department_id || r?.department?.id || r?.departmentId || '';
      setDepartmentValue(deptId);

      fStatus.value = (r.status || 'draft');
      fFeatured.value = String((r.is_featured_home ?? 0) ? 1 : 0);

      fPublishAt.value = toLocal(r.publish_at);
      fExpireAt.value = toLocal(r.expire_at);

      const bodyHtml = (r.body ?? '') || '';
      if (rte.editor) rte.editor.innerHTML = ensurePreHasCode(bodyHtml);
      syncEditorToCode();
      setRteMode('text');

      const coverUrl = r.cover_image_url || r.cover_image || '';
      if (coverUrl){
        clearCoverPreview(true);
        setCoverPreview(coverUrl, '');
        if (btnRemoveCover) btnRemoveCover.style.display = viewOnly ? 'none' : '';
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

      if (viewOnly){
        itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
          if (el.id === 'saUuid' || el.id === 'saId' || el.id === 'saCoverRemove') return;
          if (el.type === 'file') el.disabled = true;
          else if (el.tagName === 'SELECT') el.disabled = true;
          else el.readOnly = true;
        });
        setRteEnabled(false);
        if (saveBtn) saveBtn.style.display = 'none';
        if (btnRemoveCover) btnRemoveCover.style.display = 'none';
        itemForm.dataset.mode = 'view';
        itemForm.dataset.intent = 'view';
      } else {
        setRteEnabled(true);
        if (saveBtn) saveBtn.style.display = '';
        itemForm.dataset.mode = 'edit';
        itemForm.dataset.intent = 'edit';
      }
    }

    fTitle?.addEventListener('input', debounce(() => {
      if (itemForm?.dataset.mode === 'view') return;
      if (fUuid.value) return;
      if (slugDirty) return;
      const next = slugify(fTitle.value);
      settingSlug = true;
      fSlug.value = next;
      settingSlug = false;
    }, 120));

    fSlug?.addEventListener('input', () => {
      if (fUuid.value) return;
      if (settingSlug) return;
      slugDirty = !!(fSlug.value || '').trim();
    });

    btnAdd?.addEventListener('click', async () => {
      if (!canCreate) return;
      resetForm();

      // ✅ ensure departments are loaded before opening (added)
      await loadDepartments();

      if (itemModalTitle) itemModalTitle.textContent = 'Add Student Activity';
      itemForm.dataset.intent = 'create';
      itemModal && itemModal.show();
    });

    itemModalEl?.addEventListener('hidden.bs.modal', () => {
      if (coverObjectUrl){ try{ URL.revokeObjectURL(coverObjectUrl); }catch(_){ } coverObjectUrl=null; }
    });

    // =========================
    // Row actions
    // =========================
    async function updateStatus(uuid, status){
      showLoading(true);
      try{
        const fd = new FormData();
        fd.append('_method', 'PUT');
        fd.append('status', status);

        const res = await fetchWithTimeout(`/api/student-activities/${encodeURIComponent(uuid)}`, {
          method: 'POST',
          headers: authHeaders(),
          body: fd
        }, 15000);

        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Update failed');

        ok('Status updated');
        await Promise.all([loadTab('active'), loadTab('inactive')]);
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        showLoading(false);
      }
    }

    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;

      const tr = btn.closest('tr');
      const uuid = tr?.dataset?.uuid;
      const act = btn.dataset.action;
      if (!uuid) return;

      // close dropdown (bootstrap)
      const toggle = btn.closest('.dropdown')?.querySelector('.sa-dd-toggle');
      if (toggle) { try { bootstrap.Dropdown.getOrCreateInstance(toggle).hide(); } catch (_) {} }

      const row = findRowByUuid(uuid) || {};

      if (act === 'view' || act === 'edit'){
        if (act === 'edit' && !canEdit) return;

        // ✅ ensure departments are loaded before filling (added)
        await loadDepartments();

        resetForm();
        if (itemModalTitle) itemModalTitle.textContent = (act === 'view') ? 'View Student Activity' : 'Edit Student Activity';
        fillFormFromRow(row, act === 'view');
        itemModal && itemModal.show();
        return;
      }

      if (act === 'toggleFeatured'){
        if (!canEdit) return;
        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/student-activities/${encodeURIComponent(uuid)}/toggle-featured`, {
            method: 'POST',
            headers: authHeaders()
          }, 15000);

          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Toggle failed');

          ok('Featured updated');
          await Promise.all([loadTab('active'), loadTab('inactive')]);
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'markPublished'){ if (!canEdit) return; await updateStatus(uuid, 'published'); return; }
      if (act === 'markDraft'){ if (!canEdit) return; await updateStatus(uuid, 'draft'); return; }

      if (act === 'delete'){
        if (!canDelete) return;
        const conf = await Swal.fire({
          title: 'Delete this student activity?',
          text: 'This will move the item to Trash.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/student-activities/${encodeURIComponent(uuid)}`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);

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
          title: 'Restore this item?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Restore'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/student-activities/${encodeURIComponent(uuid)}/restore`, {
            method: 'POST',
            headers: authHeaders()
          }, 15000);

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
          text: 'This cannot be undone (files will be removed).',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete Permanently',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/student-activities/${encodeURIComponent(uuid)}/force`, {
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

    // =========================
    // Submit (create/edit)
    // =========================
    itemForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (saving) return;
      saving = true;

      try{
        if (itemForm.dataset.mode === 'view') return;

        const intent = itemForm.dataset.intent || 'create';
        const isEdit = intent === 'edit' && !!fUuid.value;

        if (isEdit && !canEdit) return;
        if (!isEdit && !canCreate) return;

        const title = (fTitle.value || '').trim();
        const slug = (fSlug.value || '').trim();

        // ✅ department id (added)
        const deptId = (fDepartmentId?.value || '').toString().trim();

        const status = (fStatus.value || 'draft').trim();
        const featured = (fFeatured.value || '0').trim();

        const rawBody = (rte.mode === 'code') ? (rte.code.value || '') : (rte.editor.innerHTML || '');
        const cleanBody = ensurePreHasCode(rawBody).trim();
        if (rte.hidden) rte.hidden.value = cleanBody;

        if (!title){ err('Title is required'); fTitle.focus(); return; }
        if (!deptId){ err('Department is required'); fDepartmentId?.focus(); return; }
        if (!cleanBody){ err('Body is required'); rteFocus(); return; }

        const fd = new FormData();
        fd.append('title', title);
        if (slug) fd.append('slug', slug);

        // ✅ send department_id (added)
        fd.append('department_id', deptId);

        fd.append('status', status);
        fd.append('is_featured_home', featured === '1' ? '1' : '0');
        if ((fPublishAt.value || '').trim()) fd.append('publish_at', fPublishAt.value);
        if ((fExpireAt.value || '').trim()) fd.append('expire_at', fExpireAt.value);
        fd.append('body', cleanBody);

        // cover remove
        if (isEdit && (fCoverRemove?.value === '1')) fd.append('cover_image_remove', '1');

        // cover upload
        const cover = fCover.files?.[0] || null;
        if (cover) fd.append('cover_image', cover);

        // attachments upload
        Array.from(fAttachments.files || []).forEach(f => fd.append('attachments[]', f));

        let url = '/api/student-activities';
        if (isEdit){
          url = `/api/student-activities/${encodeURIComponent(fUuid.value)}`;
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

        // ✅ preload departments once (added)
        await loadDepartments();

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
