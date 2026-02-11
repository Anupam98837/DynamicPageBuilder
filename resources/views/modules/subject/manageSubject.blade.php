{{-- resources/views/modules/course/manageSubjects.blade.php --}}
@section('title','Subjects')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* Dropdowns inside table */
.table-wrap .dropdown{position:relative}
.dropdown .dd-toggle{border-radius:10px}
.dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:230px;z-index:5000}
.dropdown-menu.show{display:block !important}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color) !important}

/* Tabs */
.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{background:var(--surface);border-color:var(--line-strong) var(--line-strong) var(--surface)}
.tab-content,.tab-pane{overflow:visible}

/* Shell */
.subj-wrap{padding:14px 4px}

/* Toolbar panel */
.subj-toolbar.panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2);padding:12px;}

/* Table Card */
.table-wrap.card{position:relative;border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:visible;}
.table-wrap .card-body{overflow:visible}
.table{--bs-table-bg:transparent}
.table thead th{font-weight:600;color:var(--muted-color);font-size:13px;border-bottom:1px solid var(--line-strong);background:var(--surface)}
.table thead.sticky-top{z-index:3}
.table tbody tr{border-top:1px solid var(--line-soft)}
.table tbody tr:hover{background:var(--page-hover)}
td .fw-semibold{color:var(--ink)}
.small{font-size:12.5px}

/* Columns */
th.col-code, td.col-code{width:170px;max-width:170px}
th.col-type, td.col-type{width:170px;max-width:170px}
th.col-sem,  td.col-sem{width:260px;max-width:260px}
td.col-sem{overflow:hidden}
td.col-sem .sem-sub{display:block;font-size:12.5px;color:var(--muted-color);max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

/* Badges */
.badge-soft-primary{background:color-mix(in oklab, var(--primary-color) 12%, transparent);color:var(--primary-color)}
.badge-soft-success{background:color-mix(in oklab, var(--success-color) 12%, transparent);color:var(--success-color)}
.badge-soft-muted{background:color-mix(in oklab, var(--muted-color) 10%, transparent);color:var(--muted-color)}
.badge-soft-warning{background:color-mix(in oklab, var(--warning-color, #f59e0b) 14%, transparent);color:var(--warning-color, #f59e0b)}
.badge-soft-danger{background:color-mix(in oklab, var(--danger-color) 12%, transparent);color:var(--danger-color)}

/* Button loading state */
.btn-loading{position:relative;color:transparent !important}
.btn-loading::after{content:'';position:absolute;width:16px;height:16px;top:50%;left:50%;margin:-8px 0 0 -8px;border:2px solid transparent;border-top:2px solid currentColor;border-radius:50%;animation:spin 1s linear infinite}
@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

/* Responsive toolbar */
@media (max-width: 768px){
  .subj-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .subj-toolbar .position-relative{min-width:100% !important}
}

/* Horizontal scroll */
.table-responsive > .table{width:max-content;min-width:1280px;}
.table-responsive th,
.table-responsive td{white-space:nowrap;}
@media (max-width: 576px){
  .table-responsive > .table{ min-width:1220px; }
}

/* ✅ FIX: Force global loading overlay to be controllable */
#globalLoading.loading-overlay{ display:none !important; }
#globalLoading.loading-overlay.is-show{ display:flex !important; }
</style>
@endpush

@section('content')
<div class="subj-wrap">

  {{-- Loading Overlay --}}
  <div id="globalLoading" class="loading-overlay" style="display:none;">
    @include('partials.overlay')
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#tab-active" role="tab" aria-selected="true">
        <i class="fa-solid fa-book me-2"></i>Active
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
      <div class="row align-items-center g-2 mb-3 subj-toolbar panel">
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
            <input id="searchInput" type="search" class="form-control ps-5" placeholder="Search by title / code / course / semester…">
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
              <i class="fa fa-plus me-1"></i> Add Subject
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
                  <th style="width:320px;">Subject</th>
                  <th class="col-code">Code</th>
                  <th class="col-type">Type</th>
                  <th class="col-sem">Semester</th>
                  <th style="width:220px;">Course</th>
                  <th style="width:180px;">Department</th>
                  <th style="width:110px;">Status</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-active">
                <tr><td colspan="9" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-active" class="empty p-4 text-center" style="display:none;">
            <i class="fa-solid fa-book mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No active subjects found.</div>
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
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:320px;">Subject</th>
                  <th class="col-code">Code</th>
                  <th class="col-type">Type</th>
                  <th class="col-sem">Semester</th>
                  <th style="width:220px;">Course</th>
                  <th style="width:180px;">Department</th>
                  <th style="width:110px;">Status</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-inactive">
                <tr><td colspan="9" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-inactive" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-circle-pause mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No inactive subjects found.</div>
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
                  <th style="width:320px;">Subject</th>
                  <th class="col-code">Code</th>
                  <th class="col-type">Type</th>
                  <th class="col-sem">Semester</th>
                  <th style="width:220px;">Course</th>
                  <th style="width:160px;">Deleted</th>
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
            <label class="form-label">Status</label>
            <select id="modal_status" class="form-select">
              <option value="">All</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Department</label>
            <select id="modal_department" class="form-select">
              <option value="">All</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Course</label>
            <select id="modal_course" class="form-select">
              <option value="">All</option>
            </select>
            <div class="form-text">Choosing a course will filter semesters.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Semester</label>
            <select id="modal_semester" class="form-select">
              <option value="">All</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Subject Type</label>
            <select id="modal_type" class="form-select">
              <option value="">All</option>
              <option value="theory">Theory</option>
              <option value="practical">Practical</option>
              <option value="lab">Lab</option>
              <option value="project">Project</option>
              <option value="seminar">Seminar</option>
              <option value="tutorial">Tutorial</option>
              <option value="elective">Elective</option>
              <option value="workshop">Workshop</option>
              <option value="viva">Viva</option>
              <option value="internship">Internship</option>
              <option value="__other__">Other…</option>
            </select>
            <div class="form-text">Choose Other to enter a custom type.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="modal_sort" class="form-select">
              <option value="-updated_at">Recently Updated</option>
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="title">Title A-Z</option>
              <option value="-title">Title Z-A</option>
              <option value="subject_code">Code (Asc)</option>
              <option value="-subject_code">Code (Desc)</option>
              <option value="subject_type">Type (Asc)</option>
              <option value="-subject_type">Type (Desc)</option>
              <option value="status">Status (Asc)</option>
              <option value="-status">Status (Desc)</option>
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
        <h5 class="modal-title" id="itemModalTitle">Add Subject</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="itemUuid">
        <input type="hidden" id="itemId">

        <div class="row g-3">

          <div class="col-lg-6">
            <div class="row g-3">

              {{-- ✅ REQUIRED: Course --}}
              <div class="col-md-6">
                <label class="form-label">Course <span class="text-danger">*</span></label>
                <select id="course_id" class="form-select" required>
                  <option value="">Select course</option>
                </select>
                <div class="form-text">Course will filter semesters.</div>
              </div>

              {{-- ✅ REQUIRED: Department --}}
              <div class="col-md-6">
                <label class="form-label">Department <span class="text-danger">*</span></label>
                <select id="department_id" class="form-select" required>
                  <option value="">Select department</option>
                </select>
              </div>

              {{-- ✅ REQUIRED: Semester --}}
              <div class="col-12">
                <label class="form-label">Semester <span class="text-danger">*</span></label>
                <select id="semester_id" class="form-select" required>
                  <option value="">Select semester</option>
                </select>
              </div>

              <div class="col-md-7">
                <label class="form-label">Subject Title <span class="text-danger">*</span></label>
                <input class="form-control" id="title" required maxlength="255" placeholder="e.g., Data Structures">
              </div>

              <div class="col-md-5">
                <label class="form-label">Subject Code <span class="text-danger">*</span></label>
                <input class="form-control" id="subject_code" required maxlength="60" placeholder="e.g., CS301">
              </div>

              {{-- ✅ UPDATED: Subject Type dropdown + Other --}}
              <div class="col-md-6">
                <label class="form-label">Subject Type <span class="text-danger">*</span></label>
                  <select class="form-select" id="subject_type" required>
                    <option value="" disabled selected>Select type</option>

                    <option value="compulsory">Compulsory</option>
                    <option value="optional">Optional</option>

                    <option value="__other__">Other…</option>
                  </select>

                <div class="form-text">Choose Other to type custom value.</div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select" id="status">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Publish At (optional)</label>
                <input type="datetime-local" class="form-control" id="publish_at">
              </div>

              <div class="col-md-6">
                <label class="form-label">Credits (optional)</label>
                <input class="form-control" id="credits" inputmode="numeric" placeholder="e.g., 4">
              </div>

              <div class="col-12">
                <label class="form-label">Description (optional)</label>
                <textarea class="form-control" id="description" rows="7" placeholder="Optional HTML/text..."></textarea>
                <div class="form-text">HTML allowed.</div>
              </div>

              <div class="col-12">
                <label class="form-label">Metadata (JSON) (optional)</label>
                <textarea class="form-control" id="metadata" rows="6" placeholder='{"note":"Shown in frontend"}'></textarea>
                <div class="form-text">Must be valid JSON, otherwise saved as null.</div>
              </div>

            </div>
          </div>

          <div class="col-lg-6">
            <div class="alert alert-light mb-0" style="border:1px dashed var(--line-soft);border-radius:14px;">
              <div class="fw-semibold mb-1"><i class="fa fa-circle-info me-1"></i> Notes</div>
              <div class="small text-muted">
                - Course + Semester + Department are <b>required</b>.<br/>
                - Subject Type supports presets + custom “Other…”.<br/>
                - Status supports <b>status</b> and legacy <b>active/is_active/isActive</b>.
              </div>
            </div>

            <div class="mt-3">
              <div class="meta-box" style="border:1px solid var(--line-strong);border-radius:14px;background:var(--surface);overflow:hidden;">
                <div class="meta-top" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;border-bottom:1px solid var(--line-soft);">
                  <div class="fw-semibold"><i class="fa fa-brackets-curly me-2"></i>Quick Meta Preview</div>
                  <button type="button" class="btn btn-light btn-sm" id="btnFormatMeta">
                    <i class="fa fa-wand-magic-sparkles me-1"></i>Format
                  </button>
                </div>
                <div class="p-2">
                  <input class="form-control" id="meta_preview" placeholder="Auto (from metadata JSON)" readonly>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
(() => {
  if (window.__SUBJECTS_MODULE_INIT__) return;
  window.__SUBJECTS_MODULE_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=300) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  // =========================
  // ✅ API Map (Subjects)
  // =========================
  const API = {
    me:           () => '/api/users/me',
    departments:  () => '/api/departments',
    courses:      () => '/api/courses',
    semesters:    () => '/api/course-semesters?per_page=200&page=1&sort=updated_at&direction=desc',

    list:         () => '/api/subjects',
    trashList:    () => '/api/subjects/trash',

    create:       () => '/api/subjects',
    update:       (id) => `/api/subjects/${encodeURIComponent(id)}`,
    remove:       (id) => `/api/subjects/${encodeURIComponent(id)}`,
    restore:      (id) => `/api/subjects/${encodeURIComponent(id)}/restore`,
    force:        (id) => `/api/subjects/${encodeURIComponent(id)}/force`,
    toggle:       (id) => `/api/subjects/${encodeURIComponent(id)}`
  };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
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

  function isIntString(v){
    return typeof v === 'string' && /^\d+$/.test(v.trim());
  }

  function resolveId(valueOrUuid, rows){
    const v = (valueOrUuid ?? '').toString().trim();
    if (!v) return '';
    if (isIntString(v)) return v;
    const found = (rows || []).find(r => String(r?.uuid) === v);
    const id = found?.id;
    return (id !== null && id !== undefined) ? String(id) : '';
  }

  function prettyDate(s){
    const v = (s ?? '').toString().trim();
    return v || '—';
  }

  function safeJsonParse(str){
    try{
      const o = JSON.parse(str);
      return { ok:true, value:o };
    }catch(e){
      return { ok:false, value:null };
    }
  }

  // =========================
  // ✅ Dropdown handling (manual)
  // =========================
  function hideAllDropdowns(exceptEl=null){
    document.querySelectorAll('.dd-toggle').forEach(t => {
      if (exceptEl && t === exceptEl) return;
      try { bootstrap.Dropdown.getInstance(t)?.hide(); } catch(_){}
    });
  }

  function getDropdownInstance(toggleEl){
    return bootstrap.Dropdown.getOrCreateInstance(toggleEl, {
      boundary: 'viewport',
      popperConfig: { strategy: 'fixed' }
    });
  }

  function bindGlobalDropdownClosers(){
    window.addEventListener('resize', () => hideAllDropdowns(), { passive:true });
    window.addEventListener('scroll', () => hideAllDropdowns(), { passive:true, capture:true });
    document.querySelectorAll('.table-responsive').forEach(w => {
      w.addEventListener('scroll', () => hideAllDropdowns(), { passive:true });
    });
    document.addEventListener('click', (e) => {
      const inside = e.target.closest('.dropdown');
      const isToggle = e.target.closest('.dd-toggle');
      if (!inside && !isToggle) hideAllDropdowns();
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const globalLoading = $('globalLoading');
    const showLoading = (v) => {
      if (!globalLoading) return;
      globalLoading.classList.toggle('is-show', !!v);
    };

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

    const filterModalEl = $('filterModal');
    const filterModal = filterModalEl ? new bootstrap.Modal(filterModalEl) : null;

    // ✅ FIX: hard cleanup for stuck modal backdrop (only when no modal is open)
    function cleanupBootstrapModalArtifacts(){
      const anyOpen = !!document.querySelector('.modal.show');
      const backdrops = document.querySelectorAll('.modal-backdrop');
      if (!anyOpen){
        backdrops.forEach(b => b.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
        document.body.style.removeProperty('overflow');
      } else {
        // if something else is open, only remove extras (rare)
        if (backdrops.length > 1){
          for (let i = 1; i < backdrops.length; i++) backdrops[i].remove();
        }
      }
    }

    function safeHideModal(modalInstance){
      try{ modalInstance?.hide(); }catch(_){}
      // allow bootstrap fade transition to end, then cleanup any orphan backdrop
      setTimeout(cleanupBootstrapModalArtifacts, 350);
    }

    // ensure backdrop never stays after closing Filter modal (including close button / ESC)
    filterModalEl?.addEventListener('hidden.bs.modal', cleanupBootstrapModalArtifacts);

    const modalStatus = $('modal_status');
    const modalSort = $('modal_sort');
    const modalDepartment = $('modal_department');
    const modalCourse = $('modal_course');
    const modalSemester = $('modal_semester');
    const modalType = $('modal_type');

    const itemModalEl = $('itemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;
    const itemModalTitle = $('itemModalTitle');
    const itemForm = $('itemForm');
    const saveBtn = $('saveBtn');

    const itemUuid = $('itemUuid');
    const itemId = $('itemId');

    const courseSel = $('course_id');
    const deptSel = $('department_id');
    const semesterSel = $('semester_id');

    const titleInput = $('title');
    const subjectCodeInput = $('subject_code');
    const subjectTypeSel = $('subject_type'); // ✅ now SELECT
    const statusSel = $('status');
    const publishAtInput = $('publish_at');
    const creditsInput = $('credits');
    const descInput = $('description');
    const metaText = $('metadata');
    const metaPreview = $('meta_preview');
    const btnFormatMeta = $('btnFormatMeta');

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
      filters: {
        q:'',
        status:'',
        department_id:'',
        course_id:'',
        course_semester_id:'',
        subject_type:'',
        sort:'-updated_at'
      },
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      tabs: {
        active:   { page:1, lastPage:1, items:[] },
        inactive: { page:1, lastPage:1, items:[] },
        trash:    { page:1, lastPage:1, items:[] }
      },
      departments: [],
      courses: [],
      semesters: []
    };

    const getTabKey = () => {
      const a = document.querySelector('.nav-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#tab-active';
      if (href === '#tab-inactive') return 'inactive';
      if (href === '#tab-trash') return 'trash';
      return 'active';
    };

    function buildUrl(tabKey){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state.tabs[tabKey].page));

      const q = (state.filters.q || '').trim();
      if (q) params.set('q', q);

      const s = state.filters.sort || '-updated_at';
      params.set('sort', s.startsWith('-') ? s.slice(1) : s);
      params.set('direction', s.startsWith('-') ? 'desc' : 'asc');

      if (tabKey !== 'trash') {
        let status = (state.filters.status || '').trim();
        if (!status) status = (tabKey === 'inactive') ? 'inactive' : 'active';
        params.set('status', status);
        params.set('active', status === 'active' ? '1' : '0');
      }

      if (state.filters.course_id) params.set('course_id', state.filters.course_id);
      if (state.filters.course_semester_id) params.set('course_semester_id', state.filters.course_semester_id);
      if (state.filters.department_id) params.set('department_id', state.filters.department_id);
      if (state.filters.subject_type) params.set('subject_type', state.filters.subject_type);

      const base = (tabKey === 'trash') ? API.trashList() : API.list();
      return `${base}?${params.toString()}`;
    }

    function setEmpty(tabKey, show){
      const el = tabKey==='active' ? emptyActive : (tabKey==='inactive' ? emptyInactive : emptyTrash);
      if (el) el.style.display = show ? '' : 'none';
    }

    function statusBadge(status){
      const s = (status || '').toString().toLowerCase().trim();
      if (s === 'active') return `<span class="badge badge-soft-success">Active</span>`;
      if (s === 'inactive') return `<span class="badge badge-soft-warning">Inactive</span>`;
      if (!s) return `<span class="badge badge-soft-muted">—</span>`;
      return `<span class="badge badge-soft-muted">${esc(s)}</span>`;
    }

    function typeBadge(t){
      const s = (t || '').toString().trim();
      if (!s) return `<span class="badge badge-soft-muted">—</span>`;
      const lower = s.toLowerCase();
      if (lower.includes('theory')) return `<span class="badge badge-soft-primary">${esc(s)}</span>`;
      if (lower.includes('practical') || lower.includes('lab')) return `<span class="badge badge-soft-success">${esc(s)}</span>`;
      if (lower.includes('project') || lower.includes('seminar')) return `<span class="badge badge-soft-warning">${esc(s)}</span>`;
      return `<span class="badge badge-soft-muted">${esc(s)}</span>`;
    }

    function semesterLabel(s){
      if (!s) return '—';
      const title = s?.title || '—';
      const no = s?.semester_no ?? '';
      return [title, (no ? `No.${no}` : '')].filter(Boolean).join(' ');
    }

    function semestersForCourse(courseId){
      const cid = (courseId || '').toString().trim();
      if (!cid) return (state.semesters || []);
      return (state.semesters || []).filter(s => String(s?.course_id ?? '') === cid);
    }

    function applySemesterFilterToSelect(selectEl, courseId, keepSelectedId=''){
      if (!selectEl) return;
      const keep = (keepSelectedId || '').toString().trim() || (selectEl.value || '').toString().trim();
      const rows = semestersForCourse(courseId);

      const opts = rows.map(s => {
        const id = s?.id;
        if (id === null || id === undefined || String(id).trim() === '') return '';
        return `<option value="${esc(String(id))}">${esc(semesterLabel(s))}</option>`;
      }).join('');

      const isFilterModal = selectEl === modalSemester;
      selectEl.innerHTML = `${isFilterModal ? `<option value="">All</option>` : `<option value="">Select semester</option>`}${opts}`;

      if (keep && rows.some(s => String(s?.id) === String(keep))) selectEl.value = keep;
      else selectEl.value = '';
    }

    // ✅ Subject type custom helper
    function hasOption(selectEl, value){
      return !!selectEl?.querySelector(`option[value="${CSS.escape(String(value))}"]`);
    }

    function addCustomTypeOption(selectEl, customVal){
      if (!selectEl) return;
      const v = (customVal || '').toString().trim();
      if (!v) return;

      if (hasOption(selectEl, v)) {
        selectEl.value = v;
        return;
      }

      // insert before "__other__" if exists else append
      const otherOpt = selectEl.querySelector('option[value="__other__"]');
      const opt = document.createElement('option');
      opt.value = v;
      opt.textContent = v;

      if (otherOpt) selectEl.insertBefore(opt, otherOpt);
      else selectEl.appendChild(opt);

      selectEl.value = v;
    }

    async function handleOtherTypeSelect(selectEl){
      if (!selectEl) return;
      if (selectEl.value !== '__other__') return;

      const prev = selectEl.dataset.prevValue || '';
      const res = await Swal.fire({
        title: 'Enter custom subject type',
        input: 'text',
        inputPlaceholder: 'e.g., Skill / Minor / MOOC',
        inputValue: '',
        showCancelButton: true,
        confirmButtonText: 'Add',
        cancelButtonText: 'Cancel',
        inputValidator: (v) => {
          if (!v || !v.trim()) return 'Type cannot be empty';
          if (v.trim().length > 50) return 'Max 50 characters';
          return null;
        }
      });

      if (!res.isConfirmed){
        selectEl.value = prev || '';
        return;
      }

      addCustomTypeOption(selectEl, res.value.trim());
    }

    function bindOtherTypeBehavior(selectEl){
      if (!selectEl) return;
      selectEl.addEventListener('focus', () => {
        selectEl.dataset.prevValue = selectEl.value || '';
      });
      selectEl.addEventListener('change', async () => {
        if (selectEl.value === '__other__') {
          await handleOtherTypeSelect(selectEl);
        }
      });
    }

    bindOtherTypeBehavior(subjectTypeSel);
    bindOtherTypeBehavior(modalType);

    // ---------- pager ----------
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

    // ---------- rendering helpers ----------
    function findCourseName(courseId, row=null){
      const cid = (courseId ?? '').toString();
      if (!cid) {
        const nested = row?.course?.title || row?.course_title || '';
        return nested || '—';
      }
      const found = state.courses.find(x => String(x.id) === cid);
      return found ? (found.title || found.name || '—') : (row?.course?.title || row?.course_title || '—');
    }

    function findDeptName(deptId, row=null){
      const did = (deptId ?? '').toString();
      if (!did) return row?.department?.title || row?.department_title || '—';
      const found = state.departments.find(x => String(x.id) === did);
      return found ? (found.title || found.name || '—') : (row?.department?.title || row?.department_title || '—');
    }

    function findSemesterById(courseSemesterId){
      const sid = (courseSemesterId ?? '').toString();
      if (!sid) return null;
      return state.semesters.find(x => String(x.id) === sid) || null;
    }

    function renderTable(tabKey){
      const tbody = tabKey==='active' ? tbodyActive : (tabKey==='inactive' ? tbodyInactive : tbodyTrash);
      const rows = state.tabs[tabKey].items || [];
      if (!tbody) return;

      hideAllDropdowns();

      if (!rows.length){
        tbody.innerHTML = '';
        setEmpty(tabKey, true);
        renderPager(tabKey);
        return;
      }
      setEmpty(tabKey, false);

      tbody.innerHTML = rows.map(r => {
        const uuid = r.uuid || r.id || '';
        const title = (r.title || '—').toString();
        const code = (r.subject_code || r.code || '—').toString();
        const type = (r.subject_type || r.type || '').toString();
        const status = (r.status || '').toString();

        const csId = r.course_semester_id ?? r.semester_id ?? null;

        let semTitle = r.course_semester?.title || r.semester_title || '';
        let semNo = r.course_semester?.semester_no ?? r.semester_no ?? '';

        const semObj = (!semTitle && csId) ? findSemesterById(csId) : null;
        if (!semTitle && semObj) semTitle = semObj.title || '—';
        if (!semNo && semObj) semNo = semObj.semester_no ?? '';

        const courseName = findCourseName(r.course_id ?? null, r);
        const deptName = findDeptName(r.department_id ?? null, r);

        const updated = prettyDate(r.updated_at || r.created_at || '');
        const deleted = prettyDate(r.deleted_at || '');

        const semSub = [
          semNo ? ('No. ' + semNo) : '',
          csId ? ('ID: ' + csId) : ''
        ].filter(Boolean).join(' • ');

        let actions = `
          <div class="dropdown text-end">
            <button type="button" class="btn btn-light btn-sm dd-toggle" data-dd="1" aria-expanded="false" title="Actions">
              <i class="fa fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">`;

        actions += `<li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>`;

        if (canEdit && tabKey !== 'trash'){
          actions += `<li><button type="button" class="dropdown-item" data-action="edit"><i class="fa fa-pen-to-square"></i> Edit</button></li>`;
          if (tabKey === 'active'){
            actions += `<li><button type="button" class="dropdown-item" data-action="mark_inactive"><i class="fa fa-circle-pause"></i> Mark Inactive</button></li>`;
          } else if (tabKey === 'inactive'){
            actions += `<li><button type="button" class="dropdown-item" data-action="mark_active"><i class="fa fa-circle-check"></i> Mark Active</button></li>`;
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
              <td class="fw-semibold">${esc(title)}</td>
              <td class="col-code"><code>${esc(code)}</code></td>
              <td class="col-type">${typeBadge(type)}</td>
              <td class="col-sem">
                <span class="fw-semibold">${esc(semTitle || '—')}</span>
                <span class="sem-sub">${esc(semSub || '—')}</span>
              </td>
              <td>${esc(courseName || '—')}</td>
              <td>${esc(deleted)}</td>
              <td class="text-end">${actions}</td>
            </tr>`;
        }

        return `
          <tr data-uuid="${esc(uuid)}">
            <td class="fw-semibold">${esc(title)}</td>
            <td class="col-code"><code>${esc(code)}</code></td>
            <td class="col-type">${typeBadge(type)}</td>
            <td class="col-sem">
              <span class="fw-semibold">${esc(semTitle || '—')}</span>
              <span class="sem-sub">${esc(semSub || '—')}</span>
            </td>
            <td>${esc(courseName || '—')}</td>
            <td>${esc(deptName || '—')}</td>
            <td>${statusBadge(status)}</td>
            <td>${esc(updated)}</td>
            <td class="text-end">${actions}</td>
          </tr>`;
      }).join('');

      renderPager(tabKey);
    }

    async function loadTab(tabKey){
      const tbody = tabKey==='active' ? tbodyActive : (tabKey==='inactive' ? tbodyInactive : tbodyTrash);
      if (tbody){
        const cols = (tabKey==='trash') ? 7 : 9;
        tbody.innerHTML = `<tr><td colspan="${cols}" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;
      }

      try{
        const url = buildUrl(tabKey);
        const res = await fetchWithTimeout(url, { headers: authHeaders() }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(js?.message || 'Failed to load');

        const items = Array.isArray(js.data) ? js.data : [];
        const p = js.pagination || {};
        state.tabs[tabKey].items = items;
        state.tabs[tabKey].lastPage = parseInt(p.last_page || 1, 10) || 1;

        const total = p.total ?? null;
        const infoTxt = total !== null ? `${total} result(s)` : '—';
        if (tabKey === 'active' && infoActive) infoActive.textContent = infoTxt;
        if (tabKey === 'inactive' && infoInactive) infoInactive.textContent = infoTxt;
        if (tabKey === 'trash' && infoTrash) infoTrash.textContent = infoTxt;

        renderTable(tabKey);
      }catch(e){
        state.tabs[tabKey].items = [];
        state.tabs[tabKey].lastPage = 1;
        renderTable(tabKey);
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    function reloadCurrent(){ loadTab(getTabKey()); }

    // ---------- events: pager ----------
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

    filterModalEl?.addEventListener('show.bs.modal', () => {
      if (modalStatus) modalStatus.value = state.filters.status || '';
      if (modalSort) modalSort.value = state.filters.sort || '-updated_at';
      if (modalDepartment) modalDepartment.value = state.filters.department_id || '';
      if (modalCourse) modalCourse.value = state.filters.course_id || '';
      if (modalType) modalType.value = state.filters.subject_type || '';

      applySemesterFilterToSelect(modalSemester, modalCourse?.value || '', state.filters.course_semester_id || '');
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.status = modalStatus?.value || '';
      state.filters.sort = modalSort?.value || '-updated_at';
      state.filters.department_id = modalDepartment?.value || '';
      state.filters.course_id = modalCourse?.value || '';
      state.filters.course_semester_id = modalSemester?.value || '';
      state.filters.subject_type = (modalType?.value || '').trim();

      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;

      // ✅ FIX: close modal safely + cleanup stuck backdrop
      safeHideModal(filterModal);

      reloadCurrent();
    });

    btnReset?.addEventListener('click', () => {
      state.filters = { q:'', status:'', department_id:'', course_id:'', course_semester_id:'', subject_type:'', sort:'-updated_at' };
      state.perPage = 20;
      if (searchInput) searchInput.value = '';
      if (perPageSel) perPageSel.value = '20';
      if (modalStatus) modalStatus.value = '';
      if (modalDepartment) modalDepartment.value = '';
      if (modalCourse) modalCourse.value = '';
      if (modalSemester) modalSemester.value = '';
      if (modalType) modalType.value = '';
      if (modalSort) modalSort.value = '-updated_at';

      applySemesterFilterToSelect(modalSemester, '', '');

      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    document.querySelector('a[href="#tab-active"]')?.addEventListener('shown.bs.tab', () => loadTab('active'));
    document.querySelector('a[href="#tab-inactive"]')?.addEventListener('shown.bs.tab', () => loadTab('inactive'));
    document.querySelector('a[href="#tab-trash"]')?.addEventListener('shown.bs.tab', () => loadTab('trash'));

    // ---------- selects data ----------
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

    function fillCourseSelects(){
      const opts = state.courses.map(c => {
        const id = c?.id;
        const name = c?.title || c?.name || c?.course_title || '—';
        if (id === null || id === undefined || String(id).trim() === '') return '';
        return `<option value="${esc(String(id))}">${esc(String(name))}</option>`;
      }).join('');
      if (courseSel) courseSel.innerHTML = `<option value="">Select course</option>${opts}`;
      if (modalCourse) modalCourse.innerHTML = `<option value="">All</option>${opts}`;
    }

    async function loadDepartments(){
      try{
        const res = await fetchWithTimeout(API.departments(), { headers: authHeaders() }, 12000);
        if (!res.ok) return;
        const js = await res.json().catch(()=> ({}));
        state.departments = Array.isArray(js.data) ? js.data : (Array.isArray(js) ? js : []);
        fillDeptSelects();
      }catch(_){}
    }

    async function loadCourses(){
      try{
        const res = await fetchWithTimeout(API.courses(), { headers: authHeaders() }, 12000);
        if (!res.ok) return;
        const js = await res.json().catch(()=> ({}));
        state.courses = Array.isArray(js.data) ? js.data : (Array.isArray(js) ? js : []);
        fillCourseSelects();
      }catch(_){}
    }

    async function loadSemesters(){
      try{
        const res = await fetchWithTimeout(API.semesters(), { headers: authHeaders() }, 15000);
        if (!res.ok) return;
        const js = await res.json().catch(()=> ({}));
        state.semesters = Array.isArray(js.data) ? js.data : [];
        applySemesterFilterToSelect(semesterSel, '', '');
        applySemesterFilterToSelect(modalSemester, '', '');
      }catch(_){}
    }

    // course -> semester filtering (modal + form)
    courseSel?.addEventListener('change', () => applySemesterFilterToSelect(semesterSel, courseSel.value || '', ''));
    modalCourse?.addEventListener('change', () => applySemesterFilterToSelect(modalSemester, modalCourse.value || '', modalSemester?.value || ''));

    // ---------- meta preview ----------
    function updateMetaPreview(){
      if (!metaPreview) return;
      const raw = (metaText?.value || '').trim();
      if (!raw){ metaPreview.value = ''; return; }
      const parsed = safeJsonParse(raw);
      if (!parsed.ok){ metaPreview.value = 'Invalid JSON'; return; }
      const obj = parsed.value;
      if (!obj || typeof obj !== 'object'){ metaPreview.value = '—'; return; }
      const k = Object.keys(obj)[0];
      if (!k){ metaPreview.value = '—'; return; }
      const v = obj[k];
      metaPreview.value = `${k}: ${typeof v === 'string' ? v : JSON.stringify(v)}`.slice(0, 120);
    }
    metaText?.addEventListener('input', debounce(updateMetaPreview, 120));
    btnFormatMeta?.addEventListener('click', () => {
      const raw = (metaText?.value || '').trim();
      if (!raw) return;
      const parsed = safeJsonParse(raw);
      if (!parsed.ok){ err('Metadata JSON is invalid'); return; }
      metaText.value = JSON.stringify(parsed.value, null, 2);
      updateMetaPreview();
      ok('Formatted');
    });

    // ---------- modal helpers ----------
    let saving = false;

    function setBtnLoading(btn, loading){
      if (!btn) return;
      btn.disabled = !!loading;
      btn.classList.toggle('btn-loading', !!loading);
    }

    function resetForm(){
      itemForm?.reset();
      itemUuid.value = '';
      itemId.value = '';
      applySemesterFilterToSelect(semesterSel, '', '');
      if (metaText) metaText.value = '';
      if (metaPreview) metaPreview.value = '';

      // ✅ reset type (keep base list intact)
      if (subjectTypeSel) subjectTypeSel.value = '';

      itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
        if (el.id === 'itemUuid' || el.id === 'itemId') return;
        if (el.tagName === 'SELECT') el.disabled = false;
        else el.readOnly = false;
      });
      if (saveBtn) saveBtn.style.display = '';
      if (itemForm){
        itemForm.dataset.mode = 'edit';
        itemForm.dataset.intent = 'create';
      }
    }

    function fillFormFromRow(r, viewOnly=false){
      itemUuid.value = r.uuid || r.id || '';
      itemId.value = r.id || '';

      const courseId = resolveId((r.course_id ?? r.course?.id ?? '').toString(), state.courses) || '';
      if (courseSel) courseSel.value = courseId;

      const semId = resolveId((r.course_semester_id ?? r.semester_id ?? r.course_semester?.id ?? '').toString(), state.semesters) || '';
      applySemesterFilterToSelect(semesterSel, courseSel?.value || '', semId);
      if (semesterSel) semesterSel.value = semId || semesterSel.value || '';

      if (deptSel) deptSel.value = resolveId((r.department_id ?? r.department?.id ?? '').toString(), state.departments) || '';

      if (titleInput) titleInput.value = (r.title || '').toString();
      if (subjectCodeInput) subjectCodeInput.value = (r.subject_code || r.code || '').toString();

      // ✅ if custom type, auto add to dropdown
      const t = (r.subject_type || r.type || '').toString().trim();
      if (subjectTypeSel){
        if (t && !hasOption(subjectTypeSel, t)) addCustomTypeOption(subjectTypeSel, t);
        else subjectTypeSel.value = t || '';
      }

      if (statusSel) statusSel.value = ((r.status || 'active').toString().toLowerCase().trim() === 'inactive') ? 'inactive' : 'active';

      const pub = (r.publish_at ?? '')?.toString?.() || '';
      if (publishAtInput) publishAtInput.value = pub ? pub.replace(' ', 'T').slice(0,16) : '';

      if (creditsInput) creditsInput.value = (r.credits ?? r.credit ?? '').toString();
      if (descInput) descInput.value = (r.description ?? '').toString();

      let m = r.metadata ?? null;
      if (typeof m === 'string') {
        const parsed = safeJsonParse(m);
        if (parsed.ok) m = parsed.value;
      }
      if (m && typeof m === 'object') metaText.value = JSON.stringify(m, null, 2);
      else metaText.value = '';
      updateMetaPreview();

      if (viewOnly){
        itemForm?.querySelectorAll('input,select,textarea').forEach(el => {
          if (el.id === 'itemUuid' || el.id === 'itemId') return;
          if (el.tagName === 'SELECT') el.disabled = true;
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

    function findRowByUuid(uuid){
      const all = [
        ...(state.tabs.active.items || []),
        ...(state.tabs.inactive.items || []),
        ...(state.tabs.trash.items || []),
      ];
      return all.find(x => String(x?.uuid || x?.id) === String(uuid)) || null;
    }

    btnAddItem?.addEventListener('click', () => {
      if (!canCreate) return;
      resetForm();
      if (itemModalTitle) itemModalTitle.textContent = 'Add Subject';
      itemForm.dataset.intent = 'create';
      itemModal && itemModal.show();
    });

    async function toggleActive(uuid, makeActive){
      const fd = new FormData();
      fd.append('_method', 'PATCH');
      fd.append('status', makeActive ? 'active' : 'inactive');
      const v = makeActive ? '1' : '0';
      fd.append('active', v);
      fd.append('is_active', v);
      fd.append('isActive', v);

      showLoading(true);
      try{
        const res = await fetchWithTimeout(API.toggle(uuid), { method:'POST', headers: authHeaders(), body: fd }, 15000);
        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Update failed');
        ok(makeActive ? 'Marked active' : 'Marked inactive');
        await Promise.all([loadTab('active'), loadTab('inactive'), loadTab('trash')]);
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        showLoading(false);
      }
    }

    // dropdown toggle
    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('button.dd-toggle[data-dd="1"]');
      if (!toggle) return;
      if (e.target.closest('button[data-action]')) return;

      e.preventDefault();
      e.stopPropagation();

      hideAllDropdowns(toggle);
      const inst = getDropdownInstance(toggle);
      inst.toggle();
    });

    // row actions
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;

      const tr = btn.closest('tr');
      const uuid = tr?.dataset?.uuid;
      const act = btn.dataset.action;
      if (!uuid) return;

      const toggle = btn.closest('.dropdown')?.querySelector('.dd-toggle');
      if (toggle) { try { getDropdownInstance(toggle).hide(); } catch (_) {} }

      const row = findRowByUuid(uuid);

      if (act === 'view' || act === 'edit'){
        if (act === 'edit' && !canEdit) return;
        resetForm();
        if (itemModalTitle) itemModalTitle.textContent = act === 'view' ? 'View Subject' : 'Edit Subject';
        fillFormFromRow(row || {}, act === 'view');
        itemModal && itemModal.show();
        return;
      }

      if (act === 'mark_inactive'){
        if (!canEdit) return;
        const conf = await Swal.fire({ title:'Mark this subject inactive?', icon:'question', showCancelButton:true, confirmButtonText:'Mark Inactive' });
        if (!conf.isConfirmed) return;
        await toggleActive(uuid, false);
        return;
      }

      if (act === 'mark_active'){
        if (!canEdit) return;
        const conf = await Swal.fire({ title:'Mark this subject active?', icon:'question', showCancelButton:true, confirmButtonText:'Mark Active' });
        if (!conf.isConfirmed) return;
        await toggleActive(uuid, true);
        return;
      }

      if (act === 'delete'){
        if (!canDelete) return;
        const conf = await Swal.fire({
          title:'Delete this subject?',
          text:'This will move the item to Trash.',
          icon:'warning',
          showCancelButton:true,
          confirmButtonText:'Delete',
          confirmButtonColor:'#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(API.remove(uuid), { method:'DELETE', headers: authHeaders() }, 15000);
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
        const conf = await Swal.fire({ title:'Restore this item?', icon:'question', showCancelButton:true, confirmButtonText:'Restore' });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(API.restore(uuid), { method:'POST', headers: authHeaders() }, 15000);
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
          title:'Delete permanently?',
          text:'This cannot be undone.',
          icon:'warning',
          showCancelButton:true,
          confirmButtonText:'Delete Permanently',
          confirmButtonColor:'#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(API.force(uuid), { method:'DELETE', headers: authHeaders() }, 15000);
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

    // submit create/edit
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

        let courseId = (courseSel?.value || '').trim();
        let courseSemesterId = (semesterSel?.value || '').trim();
        const deptId = (deptSel?.value || '').trim();

        const title = (titleInput?.value || '').trim();
        const subjectCode = (subjectCodeInput?.value || '').trim();
        const subjectType = (subjectTypeSel?.value || '').trim();

        const statusUi = (statusSel?.value || 'active').trim().toLowerCase();
        const pub = (publishAtInput?.value || '').trim();
        const credits = (creditsInput?.value || '').trim();
        const description = (descInput?.value || '').trim();

        if (!title){ err('Subject title is required'); titleInput.focus(); return; }
        if (!subjectCode){ err('Subject code is required'); subjectCodeInput.focus(); return; }

        // ✅ REQUIRED checks (as you requested)
        if (!courseId){ err('Course is required'); courseSel.focus(); return; }
        if (!courseSemesterId){ err('Semester is required'); semesterSel.focus(); return; }
        if (!deptId){ err('Department is required'); deptSel.focus(); return; }

        let metaToSend = null;
        const metaRaw = (metaText?.value || '').trim();
        if (metaRaw){
          const parsed = safeJsonParse(metaRaw);
          if (!parsed.ok){ err('Metadata must be valid JSON'); metaText.focus(); return; }
          metaToSend = parsed.value;
        }

        const fd = new FormData();

        fd.append('course_id', String(parseInt(courseId, 10)));
        fd.append('course_semester_id', String(parseInt(courseSemesterId, 10)));
        fd.append('department_id', String(parseInt(deptId, 10)));

        fd.append('title', title);
        fd.append('subject_code', subjectCode);

        if (subjectType) fd.append('subject_type', subjectType);

        if (pub) fd.append('publish_at', pub.replace('T',' ')+':00');
        if (credits) fd.append('credits', credits);
        if (description) fd.append('description', description);

        fd.append('status', statusUi);
        const activeVal = (statusUi === 'inactive') ? '0' : '1';
        fd.append('active', activeVal);
        fd.append('is_active', activeVal);
        fd.append('isActive', activeVal);

        if (metaToSend !== null) fd.append('metadata', JSON.stringify(metaToSend));

        const url = isEdit ? API.update(itemUuid.value) : API.create();
        if (isEdit) fd.append('_method', 'PATCH');

        setBtnLoading(saveBtn, true);
        showLoading(true);

        const res = await fetchWithTimeout(url, { method:'POST', headers: authHeaders(), body: fd }, 20000);
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

    // init
    (async () => {
      showLoading(true);
      try{
        bindGlobalDropdownClosers();
        await fetchMe();
        await Promise.all([loadDepartments(), loadCourses(), loadSemesters()]);
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
