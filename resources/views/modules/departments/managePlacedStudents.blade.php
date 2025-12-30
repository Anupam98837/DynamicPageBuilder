{{-- resources/views/modules/placed-students/managePlacedStudents.blade.php --}}
@section('title','Placed Students')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Placed Students - Admin UI
 * ========================= */

.ps-wrap{max-width:1200px;margin:16px auto 40px;padding:0 6px;overflow:visible}

/* Tabs */
.ps-tabs.nav-tabs{border-color:var(--line-strong)}
.ps-tabs .nav-link{color:var(--ink)}
.ps-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface);
}
.tab-content,.tab-pane{overflow:visible}

/* Toolbar */
.ps-toolbar.panel{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:16px;
  box-shadow:var(--shadow-2);
  padding:12px;
}
.ps-toolbar .form-select,.ps-toolbar .form-control{border-radius:12px}

/* Table card */
.ps-table.card{
  position:relative;
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.ps-table .card-body{overflow:visible}
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
td .fw-semibold{color:var(--ink)}
.small{font-size:12.5px}

/* ✅ dropdown safe */
.ps-table .dropdown{position:relative}
.ps-table .dd-toggle{border-radius:10px}
.ps-table .dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:230px;
  z-index:99999; /* ✅ match reference page (avoid being behind footer/containers) */
}
.ps-table .dropdown-menu.show{display:block !important}
.ps-table .dropdown-item{display:flex;align-items:center;gap:.6rem}
.ps-table .dropdown-item i{width:16px;text-align:center}
.ps-table .dropdown-item.text-danger{color:var(--danger-color) !important}

/* ✅ Horizontal scroll (keep dropdown visible vertically) */
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
.table-responsive th,.table-responsive td{white-space:nowrap}
@media (max-width: 576px){
  .table-responsive > .table{min-width:1120px}
}

/* Badges */
.badge-soft-success{
  background:color-mix(in oklab, var(--success-color) 12%, transparent);
  color:var(--success-color);
}
.badge-soft-danger{
  background:color-mix(in oklab, var(--danger-color) 12%, transparent);
  color:var(--danger-color);
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

/* Loading overlay */
.ps-loading{
  position:fixed;inset:0;
  background:rgba(0,0,0,.45);
  display:flex;justify-content:center;align-items:center;
  z-index:9999;
  backdrop-filter:blur(2px);
}
.ps-loading .box{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;flex-direction:column;align-items:center;gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,0.3);
}
.ps-loading .spin{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,0.3);
  border-top:4px solid var(--primary-color);
  animation:psspin 1s linear infinite;
}
@keyframes psspin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

/* Button loading */
.btn-loading{position:relative;color:transparent !important}
.btn-loading::after{
  content:'';
  position:absolute;top:50%;left:50%;
  width:16px;height:16px;margin:-8px 0 0 -8px;
  border:2px solid transparent;border-top:2px solid currentColor;
  border-radius:50%;
  animation:psspin 1s linear infinite;
}

/* RTE (note) */
.rte-help{font-size:12px;color:var(--muted-color);margin-top:6px}
.rte-wrap{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:var(--surface);
}
.rte-toolbar{
  display:flex;align-items:center;gap:6px;flex-wrap:wrap;
  padding:8px;border-bottom:1px solid var(--line-strong);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.rte-btn{
  border:1px solid var(--line-soft);
  background:transparent;color:var(--ink);
  padding:7px 9px;border-radius:10px;line-height:1;
  cursor:pointer;display:inline-flex;align-items:center;justify-content:center;
  gap:6px;user-select:none;
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
  padding:7px 12px;font-size:12px;
  cursor:pointer;background:transparent;color:var(--ink);
  line-height:1;user-select:none;
}
.rte-tabs .tab:last-child{border-right:0}
.rte-tabs .tab.active{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  font-weight:700;
}
.rte-area{position:relative}
.rte-editor{min-height:180px;padding:12px 12px;outline:none}
.rte-editor:empty:before{content:attr(data-placeholder);color:var(--muted-color)}
.rte-code{
  display:none;width:100%;min-height:180px;padding:12px 12px;
  border:0;outline:none;resize:vertical;background:transparent;color:var(--ink);
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace;
  font-size:12.5px;line-height:1.45;
}
.rte-wrap.mode-code .rte-editor{display:none;}
.rte-wrap.mode-code .rte-code{display:block;}

/* Small screens toolbar */
@media (max-width: 768px){
  .ps-toolbar .row{row-gap:12px}
  .ps-toolbar .ps-actions{display:flex;gap:8px;flex-wrap:wrap}
  .ps-toolbar .ps-actions .btn{flex:1;min-width:140px}
}
</style>
@endpush

@section('content')
<div class="ps-wrap">

  {{-- Global loading --}}
  <div id="psLoading" class="ps-loading" style="display:none;">
    <div class="box">
      <div class="spin"></div>
      <div class="small">Loading…</div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs ps-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#ps-tab-active" role="tab" aria-selected="true">
        <i class="fa-solid fa-user-check me-2"></i>Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#ps-tab-inactive" role="tab" aria-selected="false">
        <i class="fa-solid fa-user-xmark me-2"></i>Inactive
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#ps-tab-trash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Trash
      </a>
    </li>
  </ul>

  <div class="tab-content">

    {{-- ACTIVE --}}
    <div class="tab-pane fade show active" id="ps-tab-active" role="tabpanel">

      {{-- Toolbar --}}
      <div class="ps-toolbar panel mb-3">
        <div class="row align-items-center g-2">
          <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">

            <div class="d-flex align-items-center gap-2">
              <label class="text-muted small mb-0">Per Page</label>
              <select id="psPerPage" class="form-select" style="width:96px;">
                <option>10</option>
                <option selected>20</option>
                <option>50</option>
                <option>100</option>
              </select>
            </div>

            <div class="position-relative" style="min-width:280px;">
              <input id="psSearch" type="search" class="form-control ps-5" placeholder="Search by user / note / uuid…">
              <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
            </div>

            <button id="psBtnFilter" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#psFilterModal">
              <i class="fa fa-sliders me-1"></i>Filter
            </button>

            <button id="psBtnReset" class="btn btn-light">
              <i class="fa fa-rotate-left me-1"></i>Reset
            </button>
          </div>

          <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
            <div class="ps-actions" id="psWriteControls" style="display:none;">
              <button type="button" class="btn btn-primary" id="psBtnAdd">
                <i class="fa fa-plus me-1"></i> Add Placed Student
              </button>
            </div>
          </div>
        </div>
      </div>

      {{-- Table --}}
      <div class="card ps-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>User</th>
                  <th>Department</th>
                  <th>Role</th>
                  <th style="width:110px;">CTC (LPA)</th>
                  <th style="width:130px;">Offer Date</th>
                  <th style="width:130px;">Joining Date</th>
                  <th style="width:110px;">Featured</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:90px;">Sort</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="psTbodyActive">
                <tr><td colspan="11" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="psEmptyActive" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa fa-user-check mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No active placed students found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="psInfoActive">—</div>
            <nav><ul id="psPagerActive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- INACTIVE --}}
    <div class="tab-pane fade" id="ps-tab-inactive" role="tabpanel">
      <div class="card ps-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>User</th>
                  <th>Department</th>
                  <th>Role</th>
                  <th style="width:110px;">CTC (LPA)</th>
                  <th style="width:130px;">Offer Date</th>
                  <th style="width:130px;">Joining Date</th>
                  <th style="width:110px;">Featured</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:90px;">Sort</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="psTbodyInactive">
                <tr><td colspan="11" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="psEmptyInactive" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa fa-user-xmark mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No inactive placed students found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="psInfoInactive">—</div>
            <nav><ul id="psPagerInactive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- TRASH --}}
    <div class="tab-pane fade" id="ps-tab-trash" role="tabpanel">
      <div class="card ps-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>User</th>
                  <th>Department</th>
                  <th style="width:150px;">Deleted</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:90px;">Sort</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="psTbodyTrash">
                <tr><td colspan="6" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="psEmptyTrash" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>Trash is empty.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="psInfoTrash">—</div>
            <nav><ul id="psPagerTrash" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="psFilterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-sliders me-2"></i>Filter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">

          <div class="col-12">
            <label class="form-label">Department (optional)</label>
            <input id="psFilterDepartment" class="form-control" placeholder="Enter department id / uuid / slug">
            <div class="form-text">This filters list only (store/update uses department id).</div>
          </div>

          <div class="col-12">
            <label class="form-label">Status</label>
            <select id="psFilterStatus" class="form-select">
              <option value="">All</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="verified">Verified</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Featured</label>
            <select id="psFilterFeatured" class="form-select">
              <option value="">Any</option>
              <option value="1">Featured only</option>
              <option value="0">Not featured</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="psFilterSort" class="form-select">
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="-updated_at">Recently Updated</option>
              <option value="offer_date">Offer Date (Asc)</option>
              <option value="-offer_date">Offer Date (Desc)</option>
              <option value="joining_date">Joining Date (Asc)</option>
              <option value="-joining_date">Joining Date (Desc)</option>
              <option value="ctc">CTC (Asc)</option>
              <option value="-ctc">CTC (Desc)</option>
              <option value="sort_order">Sort Order ↑</option>
              <option value="-sort_order">Sort Order ↓</option>
            </select>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="psBtnApplyFilters" class="btn btn-primary">
          <i class="fa fa-check me-1"></i>Apply Filters
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Add/Edit/View Modal --}}
<div class="modal fade" id="psItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form class="modal-content" id="psItemForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="psItemModalTitle">Add Placed Student</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="psUuid">
        <input type="hidden" id="psId">

        <div class="row g-3">

          <div class="col-lg-6">
            <div class="row g-3">

              {{-- User (dropdown) --}}
              <div class="col-md-6">
                <label class="form-label">User <span class="text-danger">*</span></label>
                <select class="form-select" id="psUserId" required>
                  <option value="">Loading users…</option>
                </select>
                <div class="form-text">Options are loaded from Users API (value = user id, label = name).</div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Department (optional)</label>
                <select class="form-select" id="psDepartmentId">
                  <option value="">Loading departments…</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Placement Notice (optional)</label>
                <select class="form-select" id="psPlacementNoticeId">
                  <option value="">Loading notices…</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select id="psStatus" class="form-select">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="verified">Verified</option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Role Title (optional)</label>
                <input type="text" maxlength="255" class="form-control" id="psRoleTitle" placeholder="e.g., Software Engineer">
              </div>

              <div class="col-md-4">
                <label class="form-label">CTC (LPA)</label>
                <input type="number" step="0.01" min="0" class="form-control" id="psCtc" placeholder="e.g., 6.50">
              </div>

              <div class="col-md-4">
                <label class="form-label">Offer Date</label>
                <input type="date" class="form-control" id="psOfferDate">
              </div>

              <div class="col-md-4">
                <label class="form-label">Joining Date</label>
                <input type="date" class="form-control" id="psJoiningDate">
              </div>

              <div class="col-md-6">
                <label class="form-label">Featured on Home</label>
                <select id="psFeatured" class="form-select">
                  <option value="0">No</option>
                  <option value="1">Yes</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Sort Order</label>
                <input type="number" min="0" class="form-control" id="psSortOrder" value="0">
              </div>

              <div class="col-12">
                <label class="form-label">Offer Letter URL/Path (optional)</label>
                <input type="text" maxlength="255" class="form-control" id="psOfferLetterUrl" placeholder="e.g., depy_uploads/placed_students/global/file.pdf or https://…">
                <div class="form-text">You can either set URL/path OR upload a file.</div>
              </div>

              <div class="col-12">
                <label class="form-label">Offer Letter File (optional)</label>
                <input type="file" class="form-control" id="psOfferLetterFile">
                <div class="small text-muted mt-2" id="psOfferLetterCurrent" style="display:none;">
                  <i class="fa fa-paperclip me-1"></i>
                  <a href="#" target="_blank" rel="noopener" id="psOfferLetterLink">Open</a>
                  <button type="button" class="btn btn-light btn-sm ms-2" id="psBtnOfferRemove" style="display:none;">
                    <i class="fa fa-xmark me-1"></i>Remove
                  </button>
                </div>
              </div>

            </div>
          </div>

          <div class="col-lg-6">
            <label class="form-label">Note (HTML allowed)</label>

            <div class="rte-wrap" id="psNoteWrap">
              <div class="rte-toolbar">
                <button type="button" class="rte-btn" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
                <button type="button" class="rte-btn" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
                <button type="button" class="rte-btn" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>

                <span class="rte-sep"></span>

                <button type="button" class="rte-btn" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
                <button type="button" class="rte-btn" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>

                <span class="rte-sep"></span>

                <button type="button" class="rte-btn" data-insert="code" title="Inline Code"><i class="fa fa-terminal"></i></button>
                <button type="button" class="rte-btn" data-insert="pre" title="Code Block"><i class="fa fa-code"></i></button>

                <span class="rte-sep"></span>

                <button type="button" class="rte-btn" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>

                <div class="rte-tabs">
                  <button type="button" class="tab active" data-mode="text">Text</button>
                  <button type="button" class="tab" data-mode="code">Code</button>
                </div>
              </div>

              <div class="rte-area">
                <div id="psNoteEditor" class="rte-editor" contenteditable="true" data-placeholder="Write notes / remarks…"></div>
                <textarea id="psNoteCode" class="rte-code" spellcheck="false" placeholder="HTML code…"></textarea>
              </div>
            </div>

            <div class="rte-help">Use <b>Text</b> or switch to <b>Code</b> to paste HTML.</div>
            <input type="hidden" id="psNoteHidden">
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="psSaveBtn">
          <i class="fa fa-floppy-disk me-1"></i> Save
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="psToastSuccess" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="psToastSuccessText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="psToastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="psToastErrorText">Something went wrong</div>
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
  if (window.__PLACED_STUDENTS_MODULE_INIT__) return;
  window.__PLACED_STUDENTS_MODULE_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=300) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
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
    try { return await fetch(url, { ...opts, signal: ctrl.signal }); }
    finally { clearTimeout(t); }
  }

  // ---------- RTE (note) with ACTIVE STATE ----------
  function initRte({ wrapId, editorId, codeId, hiddenId }){
    const wrap = $(wrapId);
    const editor = $(editorId);
    const code = $(codeId);
    const hidden = $(hiddenId);

    let mode = 'text';
    let enabled = true;

    const toolbar = wrap?.querySelector('.rte-toolbar') || null;

    function rteFocus(){
      try{ editor?.focus({ preventScroll:true }); }
      catch(_){ try{ editor?.focus(); }catch(__){} }
    }

    function syncToCode(){
      if (!editor || !code) return;
      if (mode === 'text') code.value = editor.innerHTML || '';
    }

    function syncToHidden(){
      if (!hidden) return;
      hidden.value = (mode === 'code') ? (code.value || '') : (editor.innerHTML || '');
    }

    function setBtnActive(cmd, on){
      if (!toolbar) return;
      const b = toolbar.querySelector(`.rte-btn[data-cmd="${cmd}"]`);
      if (b) b.classList.toggle('active', !!on);
    }

    function updateToolbarActive(){
      if (!toolbar || mode !== 'text') return;
      try{
        setBtnActive('bold', document.queryCommandState('bold'));
        setBtnActive('italic', document.queryCommandState('italic'));
        setBtnActive('underline', document.queryCommandState('underline'));
        setBtnActive('insertUnorderedList', document.queryCommandState('insertUnorderedList'));
        setBtnActive('insertOrderedList', document.queryCommandState('insertOrderedList'));
      }catch(_){}
    }

    function setMode(m){
      mode = (m === 'code') ? 'code' : 'text';
      wrap?.classList.toggle('mode-code', mode === 'code');

      wrap?.querySelectorAll('.rte-tabs .tab').forEach(t => {
        t.classList.toggle('active', t.dataset.mode === mode);
      });

      const disableBtns = (mode === 'code') || !enabled;
      wrap?.querySelectorAll('.rte-toolbar .rte-btn').forEach(b => {
        b.disabled = disableBtns;
        b.style.opacity = disableBtns ? '0.55' : '';
        b.style.pointerEvents = disableBtns ? 'none' : '';
      });

      if (mode === 'code'){
        code.value = editor.innerHTML || '';
        setTimeout(()=>{ try{ code?.focus(); }catch(_){ } }, 0);
      } else {
        editor.innerHTML = code.value || '';
        setTimeout(()=>{ rteFocus(); updateToolbarActive(); }, 0);
      }

      syncToHidden();
      updateToolbarActive();
    }

    function setEnabled(on){
      enabled = !!on;
      if (editor) editor.setAttribute('contenteditable', on ? 'true' : 'false');
      if (code) code.disabled = !on;

      const disableBtns = (mode === 'code') || !enabled;
      wrap?.querySelectorAll('.rte-toolbar .rte-btn').forEach(b => {
        b.disabled = disableBtns;
        b.style.opacity = disableBtns ? '0.55' : '';
        b.style.pointerEvents = disableBtns ? 'none' : '';
      });
      wrap?.querySelectorAll('.rte-tabs .tab').forEach(t => {
        t.style.pointerEvents = on ? '' : 'none';
        t.style.opacity = on ? '' : '0.7';
      });

      updateToolbarActive();
    }

    function insertHtmlWithCaret(html){
      rteFocus();
      const markerId = 'rte_marker_' + Math.random().toString(16).slice(2);
      document.execCommand('insertHTML', false, html + `<span id="${markerId}">\u200b</span>`);
      const marker = document.getElementById(markerId);
      if (marker){
        const sel = window.getSelection();
        const range = document.createRange();
        range.setStartAfter(marker);
        range.collapse(true);
        sel.removeAllRanges();
        sel.addRange(range);
        marker.remove();
      }
      syncToCode();
      syncToHidden();
      updateToolbarActive();
    }

    toolbar?.addEventListener('pointerdown', (e)=>e.preventDefault());

    editor?.addEventListener('input', ()=>{ syncToCode(); syncToHidden(); updateToolbarActive(); });
    ['mouseup','keyup','click'].forEach(ev => editor?.addEventListener(ev, updateToolbarActive));
    document.addEventListener('selectionchange', () => {
      if (document.activeElement === editor) updateToolbarActive();
    });

    document.addEventListener('click', (e) => {
      const tab = e.target.closest(`#${wrapId} .rte-tabs .tab`);
      if (tab){ setMode(tab.dataset.mode); return; }

      const btn = e.target.closest(`#${wrapId} .rte-toolbar .rte-btn`);
      if (!btn || mode !== 'text' || !enabled) return;

      rteFocus();

      const insert = btn.getAttribute('data-insert');
      const cmd = btn.getAttribute('data-cmd');

      if (insert === 'code'){ insertHtmlWithCaret('<code></code>'); return; }
      if (insert === 'pre'){ insertHtmlWithCaret('<pre><code></code></pre>'); return; }

      if (cmd){
        try{ document.execCommand(cmd, false, null); }catch(_){}
        syncToCode(); syncToHidden(); updateToolbarActive();
      }
    });

    return {
      setMode,
      setEnabled,
      setHtml(html){
        editor.innerHTML = html || '';
        code.value = html || '';
        setMode('text');
        syncToHidden();
        updateToolbarActive();
      },
      getHtml(){
        syncToHidden();
        return hidden.value || '';
      },
      updateToolbarActive
    };
  }

  // ---------- Lookup helpers (dropdown options) ----------
  function toArrayFromResponse(js){
    if (Array.isArray(js)) return js;
    if (Array.isArray(js?.data)) return js.data;
    if (Array.isArray(js?.items)) return js.items;
    if (Array.isArray(js?.data?.items)) return js.data.items;
    return [];
  }

  function pickLabel(item, fallbacks){
    for (const k of fallbacks){
      const v = item?.[k];
      if (typeof v === 'string' && v.trim()) return v.trim();
    }
    if (item?.user?.name) return String(item.user.name);
    if (item?.department?.name) return String(item.department.name);
    return '';
  }

  async function tryFetchList(endpoints, headers, timeoutMs=15000){
    for (const url of endpoints){
      try{
        const res = await fetchWithTimeout(url, { headers }, timeoutMs);
        if (!res.ok) continue;
        const js = await res.json().catch(()=> ({}));
        const arr = toArrayFromResponse(js);
        if (arr.length) return { ok:true, items:arr, used:url };
      }catch(_){}
    }
    return { ok:false, items:[], used:'' };
  }

  function setSelectOptions(selectEl, items, { idKey='id', labelKeys=['name','title'], placeholder='—', keepValue=true } = {}){
    if (!selectEl) return;
    const current = keepValue ? (selectEl.value || '') : '';

    let opts = `<option value="">${esc(placeholder)}</option>`;
    opts += items.map(it => {
      const id = it?.[idKey];
      if (id === undefined || id === null || id === '') return '';
      const label = pickLabel(it, labelKeys) || `#${id}`;
      return `<option value="${esc(String(id))}">${esc(label)}</option>`;
    }).join('');

    selectEl.innerHTML = opts;

    if (keepValue && current !== ''){
      const found = Array.from(selectEl.options).some(o => o.value === current);
      if (found) selectEl.value = current;
    }
  }

  function setSelectLoading(selectEl, msg){
    if (!selectEl) return;
    selectEl.innerHTML = `<option value="">${esc(msg || 'Loading…')}</option>`;
  }

  // ---------- Main ----------
  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const authHeaders = () => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    });

    const loadingEl = $('psLoading');
    const showLoading = (v)=>{ if (loadingEl) loadingEl.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('psToastSuccess');
    const toastErrEl = $('psToastError');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;

    const ok = (m) => { const el=$('psToastSuccessText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('psToastErrorText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

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

      const wc = $('psWriteControls');
      if (wc) wc.style.display = canCreate ? 'flex' : 'none';
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

    // Elements
    const perPageSel = $('psPerPage');
    const searchInput = $('psSearch');
    const btnReset = $('psBtnReset');
    const btnApplyFilters = $('psBtnApplyFilters');

    const filterDepartment = $('psFilterDepartment');
    const filterStatus = $('psFilterStatus');
    const filterFeatured = $('psFilterFeatured');
    const filterSort = $('psFilterSort');

    const tbodyActive = $('psTbodyActive');
    const tbodyInactive = $('psTbodyInactive');
    const tbodyTrash = $('psTbodyTrash');

    const infoActive = $('psInfoActive');
    const infoInactive = $('psInfoInactive');
    const infoTrash = $('psInfoTrash');

    const itemModalEl = $('psItemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;
    const itemModalTitle = $('psItemModalTitle');
    const itemForm = $('psItemForm');
    const saveBtn = $('psSaveBtn');

    const psUuid = $('psUuid');
    const psId = $('psId');

    const psUserId = $('psUserId');
    const psDepartmentId = $('psDepartmentId');
    const psPlacementNoticeId = $('psPlacementNoticeId');

    const psRoleTitle = $('psRoleTitle');
    const psCtc = $('psCtc');
    const psOfferDate = $('psOfferDate');
    const psJoiningDate = $('psJoiningDate');
    const psStatus = $('psStatus');
    const psFeatured = $('psFeatured');
    const psSortOrder = $('psSortOrder');
    const psOfferLetterUrl = $('psOfferLetterUrl');
    const psOfferLetterFile = $('psOfferLetterFile');

    const psOfferLetterCurrent = $('psOfferLetterCurrent');
    const psOfferLetterLink = $('psOfferLetterLink');
    const psBtnOfferRemove = $('psBtnOfferRemove');

    const btnAdd = $('psBtnAdd');

    // Note RTE
    const noteRte = initRte({
      wrapId: 'psNoteWrap',
      editorId: 'psNoteEditor',
      codeId: 'psNoteCode',
      hiddenId: 'psNoteHidden'
    });

    // Lookup caches for names
    const lookups = {
      usersMap: new Map(),
      usersList: [],
      deptsMap: new Map(),
      noticesMap: new Map(),
      loaded: { users:false, depts:false, notices:false }
    };

    // used users (to remove from dropdown once placed)
    const usedUserIds = new Set();

    function setBtnLoading(btn, loading){
      if (!btn) return;
      btn.disabled = !!loading;
      btn.classList.toggle('btn-loading', !!loading);
    }

    // build User dropdown excluding used users (but allow current selection for edit/view)
    function rebuildUserSelect(allowUserId=''){
      if (!psUserId) return;

      const allow = (allowUserId ?? '').toString().trim();
      const all = Array.isArray(lookups.usersList) ? lookups.usersList : [];

      const filtered = all.filter(u => {
        const id = (u?.id ?? '').toString();
        if (!id) return false;
        if (allow && id === allow) return true;
        return !usedUserIds.has(id);
      });

      if (!lookups.loaded.users){
        psUserId.innerHTML = `<option value="">(Users API not reachable)</option>`;
        return;
      }

      setSelectOptions(psUserId, filtered, {
        idKey: 'id',
        labelKeys: ['name','full_name','username'],
        placeholder: 'Select a user…',
        keepValue: true
      });
    }

    // best-effort: rebuild used users by fetching placed-students in bulk
    async function refreshUsedUsers(){
      usedUserIds.clear();
      const headers = authHeaders();

      const endpoints = [
        '/api/placed-students?per_page=5000&page=1',
        '/api/placed-students?per_page=5000&page=1&status=active',
        '/api/placed-students?per_page=5000&page=1&status=inactive',
        '/api/placed-students?per_page=5000&page=1&status=verified',
        '/api/placed-students?per_page=5000&page=1&only_trashed=1'
      ];

      for (const url of endpoints){
        try{
          const res = await fetchWithTimeout(url, { headers }, 15000);
          if (!res.ok) continue;
          const js = await res.json().catch(()=> ({}));
          const items = Array.isArray(js?.data) ? js.data : (Array.isArray(js?.items) ? js.items : []);
          items.forEach(r => {
            const uid = r?.user_id;
            if (uid !== null && uid !== undefined && uid !== '') usedUserIds.add(String(uid));
          });
        }catch(_){}
      }
    }

    async function loadLookups(){
      const headers = authHeaders();

      // users
      setSelectLoading(psUserId, 'Loading users…');
      const usersRes = await tryFetchList([
        '/api/users?per_page=500&sort=name&direction=asc',
        '/api/users?per_page=500',
        '/api/users'
      ], headers);

      if (usersRes.ok){
        const items = usersRes.items;
        lookups.usersList = items;

        lookups.usersMap.clear();
        items.forEach(u => {
          const id = u?.id;
          const nm = pickLabel(u, ['name','full_name','username']) || '';
          if (id != null && nm) lookups.usersMap.set(String(id), nm);
        });

        lookups.loaded.users = true;
      } else {
        lookups.usersList = [];
        psUserId.innerHTML = `<option value="">(Users API not reachable)</option>`;
        lookups.loaded.users = false;
      }

      // departments
      setSelectLoading(psDepartmentId, 'Loading departments…');
      const deptsRes = await tryFetchList([
        '/api/departments?per_page=500&sort=name&direction=asc',
        '/api/departments?per_page=500',
        '/api/departments'
      ], headers);

      if (deptsRes.ok){
        const items = deptsRes.items;
        lookups.deptsMap.clear();
        items.forEach(d => {
          const id = d?.id;
          const nm = pickLabel(d, ['name','title','department_name']) || '';
          if (id != null && nm) lookups.deptsMap.set(String(id), nm);
        });
        setSelectOptions(psDepartmentId, items, {
          idKey: 'id',
          labelKeys: ['name','title','department_name'],
          placeholder: 'Select a department…',
          keepValue: true
        });
        lookups.loaded.depts = true;
      } else {
        psDepartmentId.innerHTML = `<option value="">Select a department…</option>`;
        lookups.loaded.depts = false;
      }

      // placement notices
      setSelectLoading(psPlacementNoticeId, 'Loading notices…');
      const noticesRes = await tryFetchList([
        '/api/placement-notices?per_page=500&sort=created_at&direction=desc',
        '/api/placement-notices?per_page=500',
        '/api/placement-notices',
        '/api/placement-notice?per_page=500',
        '/api/placement-notice'
      ], headers);

      if (noticesRes.ok){
        const items = noticesRes.items;
        lookups.noticesMap.clear();
        items.forEach(n => {
          const id = n?.id;
          const nm = pickLabel(n, ['title','name','notice_title','company_name']) || '';
          if (id != null && nm) lookups.noticesMap.set(String(id), nm);
        });
        setSelectOptions(psPlacementNoticeId, items, {
          idKey: 'id',
          labelKeys: ['title','name','notice_title','company_name'],
          placeholder: 'Select a placement notice…',
          keepValue: true
        });
        lookups.loaded.notices = true;
      } else {
        psPlacementNoticeId.innerHTML = `<option value="">Select a placement notice…</option>`;
        lookups.loaded.notices = false;
      }
    }

    // State
    const state = {
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      filters: { q: '', department: '', status: '', featured: '', sort: '-created_at' },
      tabs: {
        active:   { page: 1, lastPage: 1, items: [] },
        inactive: { page: 1, lastPage: 1, items: [] },
        trash:    { page: 1, lastPage: 1, items: [] }
      }
    };

    const getTabKey = () => {
      const a = document.querySelector('.ps-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#ps-tab-active';
      if (href === '#ps-tab-inactive') return 'inactive';
      if (href === '#ps-tab-trash') return 'trash';
      return 'active';
    };

    function statusBadge(s){
      const v = (s || '').toString().toLowerCase();
      if (v === 'active') return `<span class="badge badge-soft-success">Active</span>`;
      if (v === 'inactive') return `<span class="badge badge-soft-danger">Inactive</span>`;
      if (v === 'verified') return `<span class="badge badge-soft-warning">Verified</span>`;
      return `<span class="badge badge-soft-muted">${esc(v || '—')}</span>`;
    }

    function featuredBadge(v){
      return v ? `<span class="badge badge-soft-primary">Yes</span>` : `<span class="badge badge-soft-muted">No</span>`;
    }

    function money(v){
      if (v === null || v === undefined || v === '') return '—';
      const n = Number(v);
      if (Number.isNaN(n)) return esc(String(v));
      return n.toFixed(2);
    }

    function buildListUrl(tabKey){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state.tabs[tabKey].page));

      const q = (state.filters.q || '').trim();
      if (q) params.set('q', q);

      const dept = (state.filters.department || '').trim();
      if (dept) params.set('department', dept);

      const featured = (state.filters.featured ?? '');
      if (featured !== '') params.set('featured', featured);

      const sort = state.filters.sort || '-created_at';
      params.set('sort', sort.startsWith('-') ? sort.slice(1) : sort);
      params.set('direction', sort.startsWith('-') ? 'desc' : 'asc');

      if (tabKey === 'active') params.set('status', 'active');
      if (tabKey === 'inactive') params.set('status', 'inactive');
      if (tabKey === 'trash') params.set('only_trashed', '1');

      if (tabKey !== 'trash' && state.filters.status){
        params.set('status', state.filters.status);
      }

      return `/api/placed-students?${params.toString()}`;
    }

    function setEmpty(tabKey, show){
      const el = tabKey==='active' ? $('psEmptyActive') : (tabKey==='inactive' ? $('psEmptyInactive') : $('psEmptyTrash'));
      if (el) el.style.display = show ? '' : 'none';
    }

    function renderPager(tabKey){
      const pagerEl = tabKey==='active' ? $('psPagerActive') : (tabKey==='inactive' ? $('psPagerInactive') : $('psPagerTrash'));
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

    function resolveUserName(r){
      const direct = r?.user_name || r?.user?.name || r?.user?.full_name || r?.student_name || '';
      if (direct) return String(direct);
      const id = r?.user_id;
      if (id == null) return '—';
      return lookups.usersMap.get(String(id)) || `User #${id}`;
    }

    function resolveDeptName(r){
      const direct = r?.department_title || r?.department_name || r?.department?.name || r?.department?.title || '';
      if (direct) return String(direct);
      const id = r?.department_id;
      if (id == null || id === '') return '—';
      return lookups.deptsMap.get(String(id)) || `Dept #${id}`;
    }

    function renderTable(tabKey){
      const tbody = tabKey==='active' ? $('psTbodyActive') : (tabKey==='inactive' ? $('psTbodyInactive') : $('psTbodyTrash'));
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
        const userName = resolveUserName(r);
        const deptTitle = resolveDeptName(r);
        const role = r.role_title || '—';
        const ctc = money(r.ctc);
        const offerDate = r.offer_date || '—';
        const joinDate = r.joining_date || '—';
        const featured = !!(r.is_featured_home ?? 0);
        const status = r.status || '—';
        const sort = (r.sort_order ?? 0);
        const updated = r.updated_at || '—';
        const deleted = r.deleted_at || '—';
        const offerLink = r.offer_letter_full_url || r.offer_letter_url || '';

        // ✅ FIX (like Contact Info): render toggle WITHOUT data-bs-toggle and handle via JS + Popper fixed strategy
        let actions = `
          <div class="dropdown text-end">
            <button type="button"
              class="btn btn-light btn-sm dd-toggle ps-dd-toggle"
              aria-expanded="false" title="Actions">
              <i class="fa fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>`;

        if (offerLink){
          actions += `<li><a class="dropdown-item" href="${esc(normalizeUrl(offerLink))}" target="_blank" rel="noopener">
              <i class="fa fa-file-arrow-up"></i> Open Offer Letter
            </a></li>`;
        }

        if (tabKey !== 'trash'){
          if (canEdit){
            actions += `<li><button type="button" class="dropdown-item" data-action="edit"><i class="fa fa-pen-to-square"></i> Edit</button></li>`;
            actions += `<li><button type="button" class="dropdown-item" data-action="toggle_featured"><i class="fa fa-star"></i> Toggle Featured</button></li>`;
          }
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
              <td><div class="fw-semibold">${esc(userName)}</div></td>
              <td>${esc(deptTitle)}</td>
              <td>${esc(deleted)}</td>
              <td>${statusBadge(status)}</td>
              <td>${esc(String(sort))}</td>
              <td class="text-end">${actions}</td>
            </tr>`;
        }

        return `
          <tr data-uuid="${esc(uuid)}">
            <td><div class="fw-semibold">${esc(userName)}</div></td>
            <td>${esc(deptTitle)}</td>
            <td>${esc(role)}</td>
            <td>${esc(ctc)}</td>
            <td>${esc(String(offerDate))}</td>
            <td>${esc(String(joinDate))}</td>
            <td>${featuredBadge(featured)}</td>
            <td>${statusBadge(status)}</td>
            <td>${esc(String(sort))}</td>
            <td>${esc(String(updated))}</td>
            <td class="text-end">${actions}</td>
          </tr>`;
      }).join('');

      renderPager(tabKey);
    }

    async function loadTab(tabKey){
      const tbody = tabKey==='active' ? tbodyActive : (tabKey==='inactive' ? tbodyInactive : tbodyTrash);
      if (tbody){
        const cols = (tabKey==='trash') ? 6 : 11;
        tbody.innerHTML = `<tr><td colspan="${cols}" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;
      }

      try{
        const res = await fetchWithTimeout(buildListUrl(tabKey), { headers: authHeaders() }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(js?.message || 'Failed to load');

        const items = Array.isArray(js.data) ? js.data : [];
        const p = js.pagination || {};
        state.tabs[tabKey].items = items;
        state.tabs[tabKey].lastPage = parseInt(p.last_page || 1, 10) || 1;

        const msg = p.total ? `${p.total} result(s)` : '—';
        if (tabKey==='active' && infoActive) infoActive.textContent = msg;
        if (tabKey==='inactive' && infoInactive) infoInactive.textContent = msg;
        if (tabKey==='trash' && infoTrash) infoTrash.textContent = msg;

        renderTable(tabKey);
      }catch(e){
        state.tabs[tabKey].items = [];
        state.tabs[tabKey].lastPage = 1;
        renderTable(tabKey);
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

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

    // Search / per page
    searchInput?.addEventListener('input', debounce(() => {
      state.filters.q = (searchInput.value || '').trim();
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      loadTab(getTabKey());
    }, 320));

    perPageSel?.addEventListener('change', () => {
      state.perPage = parseInt(perPageSel.value, 10) || 20;
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      loadTab(getTabKey());
    });

    // Filter modal set/reset/apply
    $('psFilterModal')?.addEventListener('show.bs.modal', () => {
      if (filterDepartment) filterDepartment.value = state.filters.department || '';
      if (filterStatus) filterStatus.value = state.filters.status || '';
      if (filterFeatured) filterFeatured.value = (state.filters.featured ?? '');
      if (filterSort) filterSort.value = state.filters.sort || '-created_at';
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.department = (filterDepartment?.value || '').trim();
      state.filters.status = (filterStatus?.value || '').trim();
      state.filters.featured = (filterFeatured?.value ?? '');
      state.filters.sort = (filterSort?.value || '-created_at');

      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      const modalEl = $('psFilterModal');
      if (modalEl) bootstrap.Modal.getInstance(modalEl)?.hide();
      loadTab(getTabKey());
    });

    btnReset?.addEventListener('click', () => {
      state.filters = { q:'', department:'', status:'', featured:'', sort:'-created_at' };
      state.perPage = 20;

      if (searchInput) searchInput.value = '';
      if (perPageSel) perPageSel.value = '20';

      if (filterDepartment) filterDepartment.value = '';
      if (filterStatus) filterStatus.value = '';
      if (filterFeatured) filterFeatured.value = '';
      if (filterSort) filterSort.value = '-created_at';

      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      loadTab(getTabKey());
    });

    document.querySelector('a[href="#ps-tab-active"]')?.addEventListener('shown.bs.tab', () => loadTab('active'));
    document.querySelector('a[href="#ps-tab-inactive"]')?.addEventListener('shown.bs.tab', () => loadTab('inactive'));
    document.querySelector('a[href="#ps-tab-trash"]')?.addEventListener('shown.bs.tab', () => loadTab('trash'));

    // ---------- ✅ ACTION DROPDOWN FIX (from reference page behavior) ----------
    // Use Bootstrap Dropdown programmatically with Popper strategy "fixed" so it won't be clipped by table overflow/footer.
    function closeAllDropdownsExcept(exceptToggle){
      document.querySelectorAll('.ps-dd-toggle').forEach(t => {
        if (t === exceptToggle) return;
        try{
          const inst = bootstrap.Dropdown.getInstance(t);
          inst && inst.hide();
        }catch(_){}
      });
    }

    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('.ps-dd-toggle');
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

    // close dropdowns on outside click (but allow clicks inside menu items to go through)
    document.addEventListener('click', (e) => {
      if (e.target.closest('.ps-dd-toggle')) return;
      if (e.target.closest('.dropdown-menu')) return;
      closeAllDropdownsExcept(null);
    }, { capture:true });

    // Modal helpers
    let saving = false;
    let offerRemoveRequested = false;

    function resetForm(){
      itemForm?.reset();
      psUuid.value = '';
      psId.value = '';
      offerRemoveRequested = false;

      noteRte.setHtml('');
      noteRte.setEnabled(true);

      rebuildUserSelect('');

      if (psDepartmentId) psDepartmentId.value = '';
      if (psPlacementNoticeId) psPlacementNoticeId.value = '';

      if (psOfferLetterCurrent) psOfferLetterCurrent.style.display = 'none';
      if (psOfferLetterLink) psOfferLetterLink.href = '#';
      if (psBtnOfferRemove) psBtnOfferRemove.style.display = 'none';
      if (psOfferLetterFile) psOfferLetterFile.value = '';
      if (psOfferLetterUrl) psOfferLetterUrl.value = '';

      itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
        if (el.id === 'psUuid' || el.id === 'psId') return;
        if (el.type === 'file') el.disabled = false;
        else if (el.tagName === 'SELECT') el.disabled = false;
        else el.readOnly = false;
      });

      if (saveBtn) saveBtn.style.display = '';
      itemForm.dataset.mode = 'edit';
      itemForm.dataset.intent = 'create';
    }

    function fillForm(r, viewOnly=false){
      psUuid.value = r.uuid || '';
      psId.value = r.id || '';

      const uid = r.user_id ?? '';
      const did = r.department_id ?? '';
      const pnid = r.placement_notice_id ?? '';

      rebuildUserSelect(uid !== null && uid !== undefined ? String(uid) : '');

      if (psUserId) psUserId.value = uid !== null && uid !== undefined ? String(uid) : '';
      if (psDepartmentId) psDepartmentId.value = did !== null && did !== undefined ? String(did) : '';
      if (psPlacementNoticeId) psPlacementNoticeId.value = pnid !== null && pnid !== undefined ? String(pnid) : '';

      psRoleTitle.value = r.role_title ?? '';
      psCtc.value = r.ctc ?? '';
      psOfferDate.value = r.offer_date ?? '';
      psJoiningDate.value = r.joining_date ?? '';
      psStatus.value = (r.status || 'active');
      psFeatured.value = String((r.is_featured_home ?? 0) ? 1 : 0);
      psSortOrder.value = (r.sort_order ?? 0);

      const offer = r.offer_letter_full_url || r.offer_letter_url || '';
      psOfferLetterUrl.value = (r.offer_letter_url || '');

      if (offer){
        if (psOfferLetterCurrent) psOfferLetterCurrent.style.display = '';
        if (psOfferLetterLink) psOfferLetterLink.href = normalizeUrl(offer);
        if (psBtnOfferRemove) psBtnOfferRemove.style.display = viewOnly ? 'none' : '';
      } else {
        if (psOfferLetterCurrent) psOfferLetterCurrent.style.display = 'none';
        if (psBtnOfferRemove) psBtnOfferRemove.style.display = 'none';
      }

      noteRte.setHtml(r.note || '');
      noteRte.updateToolbarActive();

      if (viewOnly){
        itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
          if (el.id === 'psUuid' || el.id === 'psId') return;
          if (el.type === 'file') el.disabled = true;
          else if (el.tagName === 'SELECT') el.disabled = true;
          else el.readOnly = true;
        });
        noteRte.setEnabled(false);
        if (saveBtn) saveBtn.style.display = 'none';
        itemForm.dataset.mode = 'view';
        itemForm.dataset.intent = 'view';
      } else {
        noteRte.setEnabled(true);
        if (saveBtn) saveBtn.style.display = '';
        itemForm.dataset.mode = 'edit';
        itemForm.dataset.intent = 'edit';
      }
    }

    async function fetchOne(uuid, withTrashed=true){
      const url = `/api/placed-students/${encodeURIComponent(uuid)}${withTrashed ? '?with_trashed=1' : ''}`;
      const res = await fetchWithTimeout(url, { headers: authHeaders() }, 12000);
      const js = await res.json().catch(()=> ({}));
      if (!res.ok) throw new Error(js?.message || 'Failed to load item');
      return js?.item || js?.data || null;
    }

    btnAdd?.addEventListener('click', () => {
      if (!canCreate) return;
      resetForm();
      if (itemModalTitle) itemModalTitle.textContent = 'Add Placed Student';
      itemForm.dataset.intent = 'create';
      itemModal && itemModal.show();
      setTimeout(()=> noteRte.updateToolbarActive(), 0);
    });

    psBtnOfferRemove?.addEventListener('click', () => {
      offerRemoveRequested = true;
      if (psOfferLetterUrl) psOfferLetterUrl.value = '';
      if (psOfferLetterFile) psOfferLetterFile.value = '';
      if (psOfferLetterCurrent) psOfferLetterCurrent.style.display = 'none';
      if (psBtnOfferRemove) psBtnOfferRemove.style.display = 'none';
      ok('Offer letter will be removed on save');
    });

    // Row actions
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;

      const tr = btn.closest('tr');
      const uuid = tr?.dataset?.uuid;
      const act = btn.dataset.action;
      if (!uuid) return;

      // close dropdown
      const toggle = btn.closest('.dropdown')?.querySelector('.ps-dd-toggle');
      if (toggle) { try { bootstrap.Dropdown.getInstance(toggle)?.hide(); } catch (_) {} }

      try{
        if (act === 'view' || act === 'edit'){
          if (act === 'edit' && !canEdit) return;

          resetForm();
          if (itemModalTitle) itemModalTitle.textContent = (act === 'view') ? 'View Placed Student' : 'Edit Placed Student';

          showLoading(true);
          const item = await fetchOne(uuid, true);
          fillForm(item || {}, act === 'view');

          itemModal && itemModal.show();
          setTimeout(()=> noteRte.updateToolbarActive(), 0);
          return;
        }

        if (act === 'toggle_featured'){
          if (!canEdit) return;
          showLoading(true);
          const res = await fetchWithTimeout(`/api/placed-students/${encodeURIComponent(uuid)}/toggle-featured`, {
            method: 'PUT',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed');
          ok('Featured updated');
          await Promise.all([loadTab('active'), loadTab('inactive')]);
          return;
        }

        if (act === 'delete'){
          if (!canDelete) return;
          const conf = await Swal.fire({
            title: 'Delete this record?',
            text: 'This will move the record to Trash.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#ef4444'
          });
          if (!conf.isConfirmed) return;

          showLoading(true);
          const res = await fetchWithTimeout(`/api/placed-students/${encodeURIComponent(uuid)}`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Delete failed');
          ok('Moved to trash');

          await refreshUsedUsers();
          rebuildUserSelect('');

          await Promise.all([loadTab('active'), loadTab('inactive'), loadTab('trash')]);
          return;
        }

        if (act === 'restore'){
          const conf = await Swal.fire({
            title: 'Restore this record?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Restore'
          });
          if (!conf.isConfirmed) return;

          showLoading(true);
          const res = await fetchWithTimeout(`/api/placed-students/${encodeURIComponent(uuid)}/restore`, {
            method: 'PUT',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Restore failed');
          ok('Restored');

          await refreshUsedUsers();
          rebuildUserSelect('');

          await Promise.all([loadTab('trash'), loadTab('active'), loadTab('inactive')]);
          return;
        }

        if (act === 'force'){
          if (!canDelete) return;
          const conf = await Swal.fire({
            title: 'Delete permanently?',
            text: 'This cannot be undone (offer letter file will be removed).',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete Permanently',
            confirmButtonColor: '#ef4444'
          });
          if (!conf.isConfirmed) return;

          showLoading(true);
          const res = await fetchWithTimeout(`/api/placed-students/${encodeURIComponent(uuid)}/force`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Force delete failed');
          ok('Deleted permanently');

          await refreshUsedUsers();
          rebuildUserSelect('');

          await loadTab('trash');
          return;
        }

      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        showLoading(false);
      }
    });

    // Submit (create/edit) using FormData (supports file)
    itemForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (saving) return;
      if (itemForm.dataset.mode === 'view') return;

      const intent = itemForm.dataset.intent || 'create';
      const isEdit = (intent === 'edit') && !!(psUuid.value || '').trim();

      if (isEdit && !canEdit) return;
      if (!isEdit && !canCreate) return;

      const userId = (psUserId.value || '').trim();
      if (!userId){
        err('User is required');
        psUserId.focus();
        return;
      }

      saving = true;
      setBtnLoading(saveBtn, true);
      showLoading(true);

      try{
        const fd = new FormData();
        fd.append('user_id', userId);

        const deptId = (psDepartmentId.value || '').trim();
        if (deptId) fd.append('department_id', deptId);

        const pnId = (psPlacementNoticeId.value || '').trim();
        if (pnId) fd.append('placement_notice_id', pnId);

        const role = (psRoleTitle.value || '').trim();
        if (role) fd.append('role_title', role);

        const ctc = (psCtc.value || '').trim();
        if (ctc !== '') fd.append('ctc', ctc);

        const offerDate = (psOfferDate.value || '').trim();
        if (offerDate) fd.append('offer_date', offerDate);

        const joiningDate = (psJoiningDate.value || '').trim();
        if (joiningDate) fd.append('joining_date', joiningDate);

        fd.append('status', (psStatus.value || 'active').trim());
        fd.append('is_featured_home', (psFeatured.value || '0').trim());
        fd.append('sort_order', String(parseInt(psSortOrder.value || '0', 10) || 0));

        fd.append('note', noteRte.getHtml() || '');

        const offerUrl = (psOfferLetterUrl.value || '').trim();
        if (offerUrl) fd.append('offer_letter_url', offerUrl);

        const file = psOfferLetterFile?.files?.[0] || null;
        if (file) fd.append('offer_letter_file', file);

        if (offerRemoveRequested) fd.append('offer_letter_remove', '1');

        let url = '/api/placed-students';
        if (isEdit){
          url = `/api/placed-students/${encodeURIComponent(psUuid.value)}`;
          fd.append('_method', 'PUT');
        }

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

        await refreshUsedUsers();
        rebuildUserSelect('');

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

        await loadLookups();

        await refreshUsedUsers();
        rebuildUserSelect('');

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
