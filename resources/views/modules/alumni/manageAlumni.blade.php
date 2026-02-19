{{-- resources/views/modules/alumni/manageAlumni.blade.php --}}
@section('title','Alumni')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Alumni - Admin UI
 * (Following Placed Students module UI)
 * ========================= */

.al-wrap{max-width:1200px;margin:16px auto 40px;padding:0 6px;overflow:visible}

/* Tabs */
.al-tabs.nav-tabs{border-color:var(--line-strong)}
.al-tabs .nav-link{color:var(--ink)}
.al-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface);
}
.tab-content,.tab-pane{overflow:visible}

/* Toolbar */
.al-toolbar.panel{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:16px;
  box-shadow:var(--shadow-2);
  padding:12px;
}
.al-toolbar .form-select,.al-toolbar .form-control{border-radius:12px}

/* Table card */
.al-table.card{
  position:relative;
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.al-table .card-body{overflow:visible}
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
.al-table .dropdown{position:relative}
.al-table .dd-toggle{border-radius:10px}
.al-table .dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:230px;
  z-index:99999;
}
.al-table .dropdown-menu.show{display:block !important}
.al-table .dropdown-item{display:flex;align-items:center;gap:.6rem}
.al-table .dropdown-item i{width:16px;text-align:center}
.al-table .dropdown-item.text-danger{color:var(--danger-color) !important}

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
.al-loading{
  position:fixed;inset:0;
  background:rgba(0,0,0,.45);
  display:flex;justify-content:center;align-items:center;
  z-index:9999;
  backdrop-filter:blur(2px);
}
.al-loading .box{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;flex-direction:column;align-items:center;gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,0.3);
}
.al-loading .spin{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,0.3);
  border-top:4px solid var(--primary-color);
  animation:alspin 1s linear infinite;
}
@keyframes alspin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

/* Button loading */
.btn-loading{position:relative;color:transparent !important}
.btn-loading::after{
  content:'';
  position:absolute;top:50%;left:50%;
  width:16px;height:16px;margin:-8px 0 0 -8px;
  border:2px solid transparent;border-top:2px solid currentColor;
  border-radius:50%;
  animation:alspin 1s linear infinite;
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
  .al-toolbar .row{row-gap:12px}
  .al-toolbar .al-actions{display:flex;gap:8px;flex-wrap:wrap}
  .al-toolbar .al-actions .btn{flex:1;min-width:140px}
}
</style>
@endpush

@section('content')
<div class="al-wrap">

  {{-- Global loading --}}
  <div id="alLoading" class="al-loading" style="display:none;">
    <div class="box">
      <div class="spin"></div>
      <div class="small">Loading…</div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs al-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#al-tab-active" role="tab" aria-selected="true">
        <i class="fa-solid fa-user-graduate me-2"></i>Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#al-tab-inactive" role="tab" aria-selected="false">
        <i class="fa-solid fa-user-slash me-2"></i>Inactive
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#al-tab-trash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Trash
      </a>
    </li>
  </ul>

  <div class="tab-content">

    {{-- ACTIVE --}}
    <div class="tab-pane fade show active" id="al-tab-active" role="tabpanel">

      {{-- Toolbar --}}
      <div class="al-toolbar panel mb-3">
        <div class="row align-items-center g-2">
          <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">

            <div class="d-flex align-items-center gap-2">
              <label class="text-muted small mb-0">Per Page</label>
              <select id="alPerPage" class="form-select" style="width:96px;">
                <option>10</option>
                <option selected>20</option>
                <option>50</option>
                <option>100</option>
              </select>
            </div>

            <div class="position-relative" style="min-width:280px;">
              <input id="alSearch" type="search" class="form-control ps-5" placeholder="Search by user / company / roll / uuid…">
              <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
            </div>

            <button id="alBtnFilter" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#alFilterModal">
              <i class="fa fa-sliders me-1"></i>Filter
            </button>

            <button id="alBtnReset" class="btn btn-light">
              <i class="fa fa-rotate-left me-1"></i>Reset
            </button>
          </div>

          <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
            <div class="al-actions" id="alWriteControls" style="display:none;">
              <button type="button" class="btn btn-primary" id="alBtnAdd">
                <i class="fa fa-plus me-1"></i> Add Alumni
              </button>
            </div>
          </div>
        </div>
      </div>

      {{-- Table --}}
      <div class="card al-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Alumni</th>
                  <th>Department</th>
                  <th>Program</th>
                  <th style="width:120px;">Passing Year</th>
                  <th>Company</th>
                  <th>Role</th>
                  <th>Location</th>
                  <th style="width:110px;">Featured</th>
                  <th style="width:110px;">Verified</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="alTbodyActive">
                <tr><td colspan="12" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="alEmptyActive" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa fa-user-graduate mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No active alumni found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="alInfoActive">—</div>
            <nav><ul id="alPagerActive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- INACTIVE --}}
    <div class="tab-pane fade" id="al-tab-inactive" role="tabpanel">
      <div class="card al-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Alumni</th>
                  <th>Department</th>
                  <th>Program</th>
                  <th style="width:120px;">Passing Year</th>
                  <th>Company</th>
                  <th>Role</th>
                  <th>Location</th>
                  <th style="width:110px;">Featured</th>
                  <th style="width:110px;">Verified</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="alTbodyInactive">
                <tr><td colspan="12" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="alEmptyInactive" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa fa-user-slash mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No inactive alumni found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="alInfoInactive">—</div>
            <nav><ul id="alPagerInactive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- TRASH --}}
    <div class="tab-pane fade" id="al-tab-trash" role="tabpanel">
      <div class="card al-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Alumni</th>
                  <th>Department</th>
                  <th style="width:120px;">Passing Year</th>
                  <th style="width:150px;">Deleted</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="alTbodyTrash">
                <tr><td colspan="6" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="alEmptyTrash" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>Trash is empty.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="alInfoTrash">—</div>
            <nav><ul id="alPagerTrash" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="alFilterModal" tabindex="-1" aria-hidden="true">
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
            <input id="alFilterDepartment" class="form-control" placeholder="Enter department id / uuid / slug">
            <div class="form-text">This filters list only (store/update uses department id).</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select id="alFilterStatus" class="form-select">
              <option value="">All</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Featured</label>
            <select id="alFilterFeatured" class="form-select">
              <option value="">Any</option>
              <option value="1">Featured only</option>
              <option value="0">Not featured</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Passing Year (optional)</label>
            <input id="alFilterPassingYear" type="number" min="1900" max="2100" class="form-control" placeholder="e.g., 2022">
          </div>

          <div class="col-md-6">
            <label class="form-label">Program (optional)</label>
            <input id="alFilterProgram" class="form-control" placeholder="e.g., BCA">
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="alFilterSort" class="form-select">
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="-updated_at">Recently Updated</option>
              <option value="passing_year">Passing Year ↑</option>
              <option value="-passing_year">Passing Year ↓</option>
              <option value="admission_year">Admission Year ↑</option>
              <option value="-admission_year">Admission Year ↓</option>
              <option value="program">Program A→Z</option>
              <option value="-program">Program Z→A</option>
              <option value="current_company">Company A→Z</option>
              <option value="-current_company">Company Z→A</option>
            </select>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="alBtnApplyFilters" class="btn btn-primary">
          <i class="fa fa-check me-1"></i>Apply Filters
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Add/Edit/View Modal --}}
<div class="modal fade" id="alItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form class="modal-content" id="alItemForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="alItemModalTitle">Add Alumni</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="alIdentifier">

        <div class="row g-3">

          <div class="col-lg-6">
            <div class="row g-3">

              {{-- Optional User link --}}
              <div class="col-md-6">
                <label class="form-label">User (optional)</label>
                <select class="form-select" id="alUserId">
                  <option value="">Loading users…</option>
                </select>
                <div class="form-text">If alumni has a portal account, pick the user. Otherwise keep empty.</div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Department (optional)</label>
                <select class="form-select" id="alDepartmentId">
                  <option value="">Loading departments…</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Program</label>
                <input type="text" maxlength="120" class="form-control" id="alProgram" placeholder="e.g., BCA / BTech">
              </div>

              <div class="col-md-4">
                <label class="form-label">Specialization</label>
                <input type="text" maxlength="120" class="form-control" id="alSpecialization" placeholder="e.g., CSE">
              </div>

              <div class="col-md-4">
                <label class="form-label">Roll No</label>
                <input type="text" maxlength="60" class="form-control" id="alRollNo" placeholder="Optional">
              </div>

              <div class="col-md-6">
                <label class="form-label">Admission Year</label>
                <input type="number" min="1900" max="2100" class="form-control" id="alAdmissionYear" placeholder="e.g., 2019">
              </div>

              <div class="col-md-6">
                <label class="form-label">Passing Year</label>
                <input type="number" min="1900" max="2100" class="form-control" id="alPassingYear" placeholder="e.g., 2022">
              </div>

              <div class="col-md-6">
                <label class="form-label">Current Company</label>
                <input type="text" maxlength="160" class="form-control" id="alCompany" placeholder="e.g., Infosys">
              </div>

              <div class="col-md-6">
                <label class="form-label">Current Role Title</label>
                <input type="text" maxlength="160" class="form-control" id="alRoleTitle" placeholder="e.g., Software Engineer">
              </div>

              <div class="col-md-6">
                <label class="form-label">Industry</label>
                <input type="text" maxlength="120" class="form-control" id="alIndustry" placeholder="e.g., IT / Finance">
              </div>

              <div class="col-md-3">
                <label class="form-label">City</label>
                <input type="text" maxlength="120" class="form-control" id="alCity" placeholder="e.g., Kolkata">
              </div>

              <div class="col-md-3">
                <label class="form-label">Country</label>
                <input type="text" maxlength="120" class="form-control" id="alCountry" placeholder="e.g., India">
              </div>

              <div class="col-md-4">
                <label class="form-label">Status</label>
                <select id="alStatus" class="form-select">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Featured on Home</label>
                <select id="alFeatured" class="form-select">
                  <option value="0">No</option>
                  <option value="1">Yes</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label">Verified At (optional)</label>
                <input type="datetime-local" class="form-control" id="alVerifiedAt">
                <div class="form-text">Leave empty if not verified.</div>
              </div>

            </div>
          </div>

          <div class="col-lg-6">
            <label class="form-label">Note (HTML allowed)</label>

            <div class="rte-wrap" id="alNoteWrap">
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
                <div id="alNoteEditor" class="rte-editor" contenteditable="true" data-placeholder="Write alumni intro / achievements / remarks…"></div>
                <textarea id="alNoteCode" class="rte-code" spellcheck="false" placeholder="HTML code…"></textarea>
              </div>
            </div>

            <div class="rte-help">Use <b>Text</b> or switch to <b>Code</b> to paste HTML.</div>
            <input type="hidden" id="alNoteHidden">
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="alSaveBtn">
          <i class="fa fa-floppy-disk me-1"></i> Save
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="alToastSuccess" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="alToastSuccessText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="alToastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="alToastErrorText">Something went wrong</div>
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
  if (window.__ALUMNI_MODULE_INIT__) return;
  window.__ALUMNI_MODULE_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=300) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
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

  function ensureSelectOption(selectEl, value, label){
    if (!selectEl) return;
    const v = (value ?? '').toString();
    if (!v) return;
    const exists = Array.from(selectEl.options).some(o => o.value === v);
    if (exists) return;
    const opt = document.createElement('option');
    opt.value = v;
    opt.textContent = label ? String(label) : `#${v}`;
    selectEl.appendChild(opt);
  }

  function dbToDtLocal(v){
    const s = (v || '').toString().trim();
    if (!s) return '';
    // expected: "YYYY-MM-DD HH:MM:SS"
    const m = s.match(/^(\d{4}-\d{2}-\d{2})[ T](\d{2}:\d{2})/);
    if (!m) return '';
    return `${m[1]}T${m[2]}`;
  }

  function dtLocalToDb(v){
    const s = (v || '').toString().trim();
    if (!s) return '';
    // "YYYY-MM-DDTHH:MM"
    if (s.includes('T')){
      const [d,t] = s.split('T');
      if (!d || !t) return '';
      return `${d} ${t}:00`;
    }
    return s;
  }

  function nowDb(){
    const d = new Date();
    const pad = (n)=> String(n).padStart(2,'0');
    const yyyy = d.getFullYear();
    const mm = pad(d.getMonth()+1);
    const dd = pad(d.getDate());
    const hh = pad(d.getHours());
    const mi = pad(d.getMinutes());
    const ss = pad(d.getSeconds());
    return `${yyyy}-${mm}-${dd} ${hh}:${mi}:${ss}`;
  }

  // ---------- Main ----------
  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const authHeaders = () => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    });

    const loadingEl = $('alLoading');
    const showLoading = (v)=>{ if (loadingEl) loadingEl.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('alToastSuccess');
    const toastErrEl = $('alToastError');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;

    const ok = (m) => { const el=$('alToastSuccessText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('alToastErrorText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

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

      const wc = $('alWriteControls');
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
    const perPageSel = $('alPerPage');
    const searchInput = $('alSearch');
    const btnReset = $('alBtnReset');
    const btnApplyFilters = $('alBtnApplyFilters');

    const filterDepartment = $('alFilterDepartment');
    const filterStatus = $('alFilterStatus');
    const filterFeatured = $('alFilterFeatured');
    const filterPassingYear = $('alFilterPassingYear');
    const filterProgram = $('alFilterProgram');
    const filterSort = $('alFilterSort');

    const tbodyActive = $('alTbodyActive');
    const tbodyInactive = $('alTbodyInactive');
    const tbodyTrash = $('alTbodyTrash');

    const infoActive = $('alInfoActive');
    const infoInactive = $('alInfoInactive');
    const infoTrash = $('alInfoTrash');

    const itemModalEl = $('alItemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;
    const itemModalTitle = $('alItemModalTitle');
    const itemForm = $('alItemForm');
    const saveBtn = $('alSaveBtn');

    const alIdentifier = $('alIdentifier');

    const alUserId = $('alUserId');
    const alDepartmentId = $('alDepartmentId');

    const alProgram = $('alProgram');
    const alSpecialization = $('alSpecialization');
    const alAdmissionYear = $('alAdmissionYear');
    const alPassingYear = $('alPassingYear');
    const alRollNo = $('alRollNo');

    const alCompany = $('alCompany');
    const alRoleTitle = $('alRoleTitle');
    const alIndustry = $('alIndustry');

    const alCity = $('alCity');
    const alCountry = $('alCountry');

    const alStatus = $('alStatus');
    const alFeatured = $('alFeatured');
    const alVerifiedAt = $('alVerifiedAt');

    const btnAdd = $('alBtnAdd');

    // Note RTE
    const noteRte = initRte({
      wrapId: 'alNoteWrap',
      editorId: 'alNoteEditor',
      codeId: 'alNoteCode',
      hiddenId: 'alNoteHidden'
    });

    // Lookup caches
    const lookups = {
      usersMap: new Map(),
      usersList: [],
      deptsMap: new Map(),
      loaded: { users:false, depts:false }
    };

    // ✅ Dept auto-fill + lock state
    let deptLocked = false;

    const norm = (v)=> (v ?? '').toString().trim().toLowerCase();

    function isAlumniUser(u){
      const r = norm(u?.role || u?.role_name || u?.user_role || u?.data?.role || u?.profile?.role || '');
      return r === 'alumni';
    }

    function findUserById(id){
      const v = (id ?? '').toString();
      if (!v) return null;
      return lookups.usersList.find(u => String(u?.id) === v) || null;
    }

    function resolveUserDeptId(u){
      const did = u?.department_id ?? u?.departmentId ?? u?.department?.id ?? null;
      if (did === null || did === undefined || did === '') return '';
      return String(did);
    }

    function resolveUserDeptLabel(u){
      return (
        u?.department_title ||
        u?.department_name ||
        u?.department?.name ||
        u?.department?.title ||
        ''
      );
    }

    function setDeptLocked(on){
      deptLocked = !!on;
      if (alDepartmentId) alDepartmentId.disabled = !!on;
    }

    function applyDeptFromSelectedUser({ forceSet=false } = {}){
      if (!alDepartmentId || !alUserId) return;

      // if form is in view mode, don't interfere (view already disables all fields)
      if ((itemForm?.dataset?.mode || '') === 'view') return;

      const uid = (alUserId.value || '').trim();
      if (!uid){
        if (deptLocked){
          // unlock + clear (prevents stale dept that came from an auto-lock)
          alDepartmentId.value = '';
        }
        setDeptLocked(false);
        return;
      }

      const u = findUserById(uid);
      const userDeptId = u ? resolveUserDeptId(u) : '';

      if (userDeptId){
        // ensure option exists so it can display
        const lbl = resolveUserDeptLabel(u) || (lookups.deptsMap.get(userDeptId) || `Dept #${userDeptId}`);
        ensureSelectOption(alDepartmentId, userDeptId, lbl);

        // only force set when user is actively changed, otherwise keep existing value if already set
        if (forceSet || !(alDepartmentId.value || '').trim()){
          alDepartmentId.value = userDeptId;
        }

        setDeptLocked(true);
      } else {
        // user has no dept -> allow manual dept pick
        if (deptLocked){
          // unlock + clear (prevents stale dept that came from a previous locked user)
          alDepartmentId.value = '';
        }
        setDeptLocked(false);
      }
    }

    function setBtnLoading(btn, loading){
      if (!btn) return;
      btn.disabled = !!loading;
      btn.classList.toggle('btn-loading', !!loading);
    }

    async function loadLookups(){
      const headers = authHeaders();

      // users (optional link) — ✅ only alumni users
      setSelectLoading(alUserId, 'Loading alumni users…');
      const usersRes = await tryFetchList([
        '/api/users?role=alumni&per_page=500&sort=name&direction=asc',
        '/api/users?per_page=500&sort=name&direction=asc',
        '/api/users?per_page=500',
        '/api/users'
      ], headers);

      if (usersRes.ok){
        const raw = usersRes.items || [];
        const items = raw.filter(isAlumniUser);

        lookups.usersList = items;
        lookups.usersMap.clear();
        items.forEach(u => {
          const id = u?.id;
          const nm = pickLabel(u, ['name','full_name','username','email']) || '';
          if (id != null && nm) lookups.usersMap.set(String(id), nm);
        });

        setSelectOptions(alUserId, items, {
          idKey: 'id',
          labelKeys: ['name','full_name','username','email'],
          placeholder: items.length ? 'Select an alumni user (optional)…' : '(No alumni users found)',
          keepValue: true
        });

        lookups.loaded.users = true;
      } else {
        lookups.usersList = [];
        alUserId.innerHTML = `<option value="">(Users API not reachable)</option>`;
        lookups.loaded.users = false;
      }

      // departments
      setSelectLoading(alDepartmentId, 'Loading departments…');
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
        setSelectOptions(alDepartmentId, items, {
          idKey: 'id',
          labelKeys: ['name','title','department_name'],
          placeholder: 'Select a department…',
          keepValue: true
        });
        lookups.loaded.depts = true;
      } else {
        alDepartmentId.innerHTML = `<option value="">Select a department…</option>`;
        lookups.loaded.depts = false;
      }

      // ✅ if a user is already selected (edge), apply dept lock
      applyDeptFromSelectedUser({ forceSet:false });
    }

    // ✅ when user changes, auto-fill dept + lock if user has dept
    alUserId?.addEventListener('change', () => {
      applyDeptFromSelectedUser({ forceSet:true });
    });

    // State
    const state = {
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      filters: { q: '', department: '', status: '', featured: '', passing_year:'', program:'', sort: '-created_at' },
      tabs: {
        active:   { page: 1, lastPage: 1, items: [] },
        inactive: { page: 1, lastPage: 1, items: [] },
        trash:    { page: 1, lastPage: 1, items: [] }
      }
    };

    const getTabKey = () => {
      const a = document.querySelector('.al-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#al-tab-active';
      if (href === '#al-tab-inactive') return 'inactive';
      if (href === '#al-tab-trash') return 'trash';
      return 'active';
    };

    function statusBadge(s){
      const v = (s || '').toString().toLowerCase();
      if (v === 'active') return `<span class="badge badge-soft-success">Active</span>`;
      if (v === 'inactive') return `<span class="badge badge-soft-danger">Inactive</span>`;
      return `<span class="badge badge-soft-muted">${esc(v || '—')}</span>`;
    }

    function yesNoBadge(v, yes='Yes', no='No'){
      return v ? `<span class="badge badge-soft-primary">${esc(yes)}</span>` : `<span class="badge badge-soft-muted">${esc(no)}</span>`;
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

      const passing = (state.filters.passing_year || '').toString().trim();
      if (passing) params.set('passing_year', passing);

      const prog = (state.filters.program || '').trim();
      if (prog) params.set('program', prog);

      const sort = state.filters.sort || '-created_at';
      params.set('sort', sort.startsWith('-') ? sort.slice(1) : sort);
      params.set('direction', sort.startsWith('-') ? 'desc' : 'asc');

      // tabs -> default status routing
      if (tabKey === 'active') params.set('status', 'active');
      if (tabKey === 'inactive') params.set('status', 'inactive');

      // optional override (kept to match reference behavior)
      if (tabKey !== 'trash' && state.filters.status){
        params.set('status', state.filters.status);
      }

      const base = (tabKey === 'trash') ? '/api/alumni/trash' : '/api/alumni';
      return `${base}?${params.toString()}`;
    }

    function setEmpty(tabKey, show){
      const el = tabKey==='active' ? $('alEmptyActive') : (tabKey==='inactive' ? $('alEmptyInactive') : $('alEmptyTrash'));
      if (el) el.style.display = show ? '' : 'none';
    }

    function renderPager(tabKey){
      const pagerEl = tabKey==='active' ? $('alPagerActive') : (tabKey==='inactive' ? $('alPagerInactive') : $('alPagerTrash'));
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
      const direct = r?.user_name || r?.user?.name || r?.user?.full_name || r?.name || '';
      if (direct) return String(direct);
      const id = r?.user_id;
      if (id == null || id === '') return '—';
      return lookups.usersMap.get(String(id)) || `User #${id}`;
    }

    function resolveDeptName(r){
      const direct = r?.department_title || r?.department_name || r?.department?.name || r?.department?.title || '';
      if (direct) return String(direct);
      const id = r?.department_id;
      if (id == null || id === '') return '—';
      return lookups.deptsMap.get(String(id)) || `Dept #${id}`;
    }

    function displayLocation(r){
      const city = (r?.city || '').toString().trim();
      const country = (r?.country || '').toString().trim();
      if (city && country) return `${city}, ${country}`;
      return city || country || '—';
    }

    function displayProgram(r){
      const p = (r?.program || '').toString().trim();
      const sp = (r?.specialization || '').toString().trim();
      if (p && sp) return `${p} • ${sp}`;
      return p || sp || '—';
    }

    function displayYear(v){
      if (v === null || v === undefined || v === '') return '—';
      return String(v);
    }

    function renderTable(tabKey){
      const tbody = tabKey==='active' ? $('alTbodyActive') : (tabKey==='inactive' ? $('alTbodyInactive') : $('alTbodyTrash'));
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
        const identifier = (r.uuid || r.id || '').toString();
        const userName = resolveUserName(r);
        const deptTitle = resolveDeptName(r);
        const program = displayProgram(r);
        const passYear = displayYear(r.passing_year);
        const company = (r.current_company || '—');
        const role = (r.current_role_title || '—');
        const location = displayLocation(r);

        const featured = !!(r.is_featured_home ?? 0);
        const verified = !!(r.verified_at ?? '');
        const status = r.status || '—';
        const updated = r.updated_at || '—';
        const deleted = r.deleted_at || '—';

        // dropdown actions (with fixed popper strategy)
        let actions = `
          <div class="dropdown text-end">
            <button type="button"
              class="btn btn-light btn-sm dd-toggle al-dd-toggle"
              aria-expanded="false" title="Actions">
              <i class="fa fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>`;

        if (tabKey !== 'trash'){
          if (canEdit){
            actions += `<li><button type="button" class="dropdown-item" data-action="edit"><i class="fa fa-pen-to-square"></i> Edit</button></li>`;
            actions += `<li><button type="button" class="dropdown-item" data-action="toggle_featured"><i class="fa fa-star"></i> Toggle Featured</button></li>`;
            actions += `<li><button type="button" class="dropdown-item" data-action="toggle_verified"><i class="fa fa-badge-check"></i> ${verified ? 'Unverify' : 'Mark Verified'}</button></li>`;
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
            <tr data-identifier="${esc(identifier)}" data-verified="${verified ? '1':'0'}">
              <td><div class="fw-semibold">${esc(userName)}</div></td>
              <td>${esc(deptTitle)}</td>
              <td>${esc(passYear)}</td>
              <td>${esc(deleted)}</td>
              <td>${statusBadge(status)}</td>
              <td class="text-end">${actions}</td>
            </tr>`;
        }

        return `
          <tr data-identifier="${esc(identifier)}" data-verified="${verified ? '1':'0'}">
            <td><div class="fw-semibold">${esc(userName)}</div></td>
            <td>${esc(deptTitle)}</td>
            <td>${esc(program)}</td>
            <td>${esc(passYear)}</td>
            <td>${esc(String(company || '—'))}</td>
            <td>${esc(String(role || '—'))}</td>
            <td>${esc(location)}</td>
            <td>${yesNoBadge(featured)}</td>
            <td>${yesNoBadge(verified, 'Yes', 'No')}</td>
            <td>${statusBadge(status)}</td>
            <td>${esc(String(updated))}</td>
            <td class="text-end">${actions}</td>
          </tr>`;
      }).join('');

      renderPager(tabKey);
    }

    async function loadTab(tabKey){
      const tbody = tabKey==='active' ? tbodyActive : (tabKey==='inactive' ? tbodyInactive : tbodyTrash);
      if (tbody){
        const cols = (tabKey==='trash') ? 6 : 12;
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
    $('alFilterModal')?.addEventListener('show.bs.modal', () => {
      if (filterDepartment) filterDepartment.value = state.filters.department || '';
      if (filterStatus) filterStatus.value = state.filters.status || '';
      if (filterFeatured) filterFeatured.value = (state.filters.featured ?? '');
      if (filterPassingYear) filterPassingYear.value = (state.filters.passing_year ?? '');
      if (filterProgram) filterProgram.value = (state.filters.program ?? '');
      if (filterSort) filterSort.value = state.filters.sort || '-created_at';
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.department = (filterDepartment?.value || '').trim();
      state.filters.status = (filterStatus?.value || '').trim();
      state.filters.featured = (filterFeatured?.value ?? '');
      state.filters.passing_year = (filterPassingYear?.value || '').toString().trim();
      state.filters.program = (filterProgram?.value || '').trim();
      state.filters.sort = (filterSort?.value || '-created_at');

      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      const modalEl = $('alFilterModal');
      if (modalEl) bootstrap.Modal.getInstance(modalEl)?.hide();
      loadTab(getTabKey());
    });

    btnReset?.addEventListener('click', () => {
      state.filters = { q:'', department:'', status:'', featured:'', passing_year:'', program:'', sort:'-created_at' };
      state.perPage = 20;

      if (searchInput) searchInput.value = '';
      if (perPageSel) perPageSel.value = '20';

      if (filterDepartment) filterDepartment.value = '';
      if (filterStatus) filterStatus.value = '';
      if (filterFeatured) filterFeatured.value = '';
      if (filterPassingYear) filterPassingYear.value = '';
      if (filterProgram) filterProgram.value = '';
      if (filterSort) filterSort.value = '-created_at';

      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      loadTab(getTabKey());
    });

    document.querySelector('a[href="#al-tab-active"]')?.addEventListener('shown.bs.tab', () => loadTab('active'));
    document.querySelector('a[href="#al-tab-inactive"]')?.addEventListener('shown.bs.tab', () => loadTab('inactive'));
    document.querySelector('a[href="#al-tab-trash"]')?.addEventListener('shown.bs.tab', () => loadTab('trash'));

    // ---------- ✅ ACTION DROPDOWN FIX ----------
    function closeAllDropdownsExcept(exceptToggle){
      document.querySelectorAll('.al-dd-toggle').forEach(t => {
        if (t === exceptToggle) return;
        try{
          const inst = bootstrap.Dropdown.getInstance(t);
          inst && inst.hide();
        }catch(_){}
      });
    }

    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('.al-dd-toggle');
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
      if (e.target.closest('.al-dd-toggle')) return;
      if (e.target.closest('.dropdown-menu')) return;
      closeAllDropdownsExcept(null);
    }, { capture:true });

    // Modal helpers
    let saving = false;

    function resetForm(){
      itemForm?.reset();
      alIdentifier.value = '';
      noteRte.setHtml('');
      noteRte.setEnabled(true);

      if (alUserId) alUserId.value = '';
      if (alDepartmentId) alDepartmentId.value = '';
      setDeptLocked(false);

      if (alStatus) alStatus.value = 'active';
      if (alFeatured) alFeatured.value = '0';
      if (alVerifiedAt) alVerifiedAt.value = '';

      itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
        if (el.id === 'alIdentifier') return;
        if (el.tagName === 'SELECT') el.disabled = false;
        else el.readOnly = false;
      });

      // after generic enable, re-apply dept lock rule (user is empty => dept enabled)
      applyDeptFromSelectedUser({ forceSet:false });

      if (saveBtn) saveBtn.style.display = '';
      itemForm.dataset.mode = 'edit';
      itemForm.dataset.intent = 'create';
    }

    function fillForm(r, viewOnly=false){
      const identifier = (r.uuid || r.id || '').toString();
      alIdentifier.value = identifier;

      const uid = r.user_id ?? '';
      const did = r.department_id ?? '';

      if (alUserId) alUserId.value = (uid !== null && uid !== undefined) ? String(uid) : '';
      if (alDepartmentId) alDepartmentId.value = (did !== null && did !== undefined) ? String(did) : '';

      alProgram.value = r.program ?? '';
      alSpecialization.value = r.specialization ?? '';
      alAdmissionYear.value = r.admission_year ?? '';
      alPassingYear.value = r.passing_year ?? '';
      alRollNo.value = r.roll_no ?? '';

      alCompany.value = r.current_company ?? '';
      alRoleTitle.value = r.current_role_title ?? '';
      alIndustry.value = r.industry ?? '';

      alCity.value = r.city ?? '';
      alCountry.value = r.country ?? '';

      alStatus.value = (r.status || 'active');
      alFeatured.value = String((r.is_featured_home ?? 0) ? 1 : 0);
      alVerifiedAt.value = dbToDtLocal(r.verified_at || '');

      noteRte.setHtml(r.note || '');
      noteRte.updateToolbarActive();

      if (viewOnly){
        itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
          if (el.id === 'alIdentifier') return;
          if (el.tagName === 'SELECT') el.disabled = true;
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

        // ✅ lock dept if selected user has a dept (do NOT force override existing dept on open)
        applyDeptFromSelectedUser({ forceSet:false });
      }
    }

    async function fetchOne(identifier){
      const id = encodeURIComponent(identifier);
      const tries = [
        `/api/alumni/${id}?with_trashed=1`,
        `/api/alumni/${id}`
      ];
      let lastErr = null;

      for (const url of tries){
        try{
          const res = await fetchWithTimeout(url, { headers: authHeaders() }, 12000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok) throw new Error(js?.message || 'Failed to load item');
          return js?.item || js?.data || js || null;
        }catch(e){
          lastErr = e;
        }
      }
      throw lastErr || new Error('Failed to load item');
    }

    btnAdd?.addEventListener('click', () => {
      if (!canCreate) return;
      resetForm();
      if (itemModalTitle) itemModalTitle.textContent = 'Add Alumni';
      itemForm.dataset.intent = 'create';
      itemModal && itemModal.show();
      setTimeout(()=> noteRte.updateToolbarActive(), 0);
    });

    // Row actions
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;

      const tr = btn.closest('tr');
      const identifier = tr?.dataset?.identifier;
      const act = btn.dataset.action;
      if (!identifier) return;

      // close dropdown
      const toggle = btn.closest('.dropdown')?.querySelector('.al-dd-toggle');
      if (toggle) { try { bootstrap.Dropdown.getInstance(toggle)?.hide(); } catch (_) {} }

      try{
        if (act === 'view' || act === 'edit'){
          if (act === 'edit' && !canEdit) return;

          resetForm();
          if (itemModalTitle) itemModalTitle.textContent = (act === 'view') ? 'View Alumni' : 'Edit Alumni';

          showLoading(true);
          const item = await fetchOne(identifier);
          fillForm(item || {}, act === 'view');

          itemModal && itemModal.show();
          setTimeout(()=> noteRte.updateToolbarActive(), 0);
          return;
        }

        if (act === 'toggle_featured'){
          if (!canEdit) return;
          showLoading(true);
          const res = await fetchWithTimeout(`/api/alumni/${encodeURIComponent(identifier)}/toggle-featured`, {
            method: 'PUT',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed');
          ok('Featured updated');
          await Promise.all([loadTab('active'), loadTab('inactive')]);
          return;
        }

        if (act === 'toggle_verified'){
          if (!canEdit) return;

          const isVerified = (tr?.dataset?.verified || '0') === '1';
          const conf = await Swal.fire({
            title: isVerified ? 'Unverify this alumni?' : 'Mark alumni as verified?',
            text: isVerified ? 'This will clear verified_at.' : 'This will set verified_at to current time.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: isVerified ? 'Unverify' : 'Verify'
          });
          if (!conf.isConfirmed) return;

          showLoading(true);
          const fd = new FormData();
          fd.append('_method', 'PUT');
          fd.append('verified_at', isVerified ? '' : nowDb());

          const res = await fetchWithTimeout(`/api/alumni/${encodeURIComponent(identifier)}`, {
            method: 'POST',
            headers: authHeaders(),
            body: fd
          }, 15000);

          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed');
          ok(isVerified ? 'Unverified' : 'Verified');
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
          const res = await fetchWithTimeout(`/api/alumni/${encodeURIComponent(identifier)}`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Delete failed');
          ok('Moved to trash');

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
          const res = await fetchWithTimeout(`/api/alumni/${encodeURIComponent(identifier)}/restore`, {
            method: 'PUT',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Restore failed');
          ok('Restored');

          await Promise.all([loadTab('trash'), loadTab('active'), loadTab('inactive')]);
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
          const res = await fetchWithTimeout(`/api/alumni/${encodeURIComponent(identifier)}/force`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Force delete failed');
          ok('Deleted permanently');

          await loadTab('trash');
          return;
        }

      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        showLoading(false);
      }
    });

    // Submit (create/edit) using FormData
    itemForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (saving) return;
      if (itemForm.dataset.mode === 'view') return;

      const intent = itemForm.dataset.intent || 'create';
      const isEdit = (intent === 'edit') && !!(alIdentifier.value || '').trim();

      if (isEdit && !canEdit) return;
      if (!isEdit && !canCreate) return;

      saving = true;
      setBtnLoading(saveBtn, true);
      showLoading(true);

      try{
        const fd = new FormData();

        const userId = (alUserId.value || '').trim();
        if (userId) fd.append('user_id', userId);

        const deptId = (alDepartmentId.value || '').trim();
        if (deptId) fd.append('department_id', deptId);

        const program = (alProgram.value || '').trim();
        if (program) fd.append('program', program);

        const spec = (alSpecialization.value || '').trim();
        if (spec) fd.append('specialization', spec);

        const ady = (alAdmissionYear.value || '').toString().trim();
        if (ady) fd.append('admission_year', ady);

        const pasy = (alPassingYear.value || '').toString().trim();
        if (pasy) fd.append('passing_year', pasy);

        const roll = (alRollNo.value || '').trim();
        if (roll) fd.append('roll_no', roll);

        const company = (alCompany.value || '').trim();
        if (company) fd.append('current_company', company);

        const role = (alRoleTitle.value || '').trim();
        if (role) fd.append('current_role_title', role);

        const ind = (alIndustry.value || '').trim();
        if (ind) fd.append('industry', ind);

        const city = (alCity.value || '').trim();
        if (city) fd.append('city', city);

        const country = (alCountry.value || '').trim();
        if (country) fd.append('country', country);

        fd.append('status', (alStatus.value || 'active').trim());
        fd.append('is_featured_home', (alFeatured.value || '0').trim());

        const vAt = dtLocalToDb(alVerifiedAt.value || '');
        fd.append('verified_at', vAt); // empty allowed

        fd.append('note', noteRte.getHtml() || '');

        let url = '/api/alumni';
        if (isEdit){
          url = `/api/alumni/${encodeURIComponent(alIdentifier.value)}`;
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
