{{-- resources/views/modules/departments/manageDepartment.blade.php --}}
@section('title','Manage Departments')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* ===== Shell ===== */
.dept-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}
.panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2);padding:14px}

/* Toolbar */
.mfa-toolbar .form-control{height:40px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)}
.mfa-toolbar .form-select{height:40px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)}
.mfa-toolbar .btn{height:40px;border-radius:12px}
.mfa-toolbar .btn-light{background:var(--surface);border:1px solid var(--line-strong)}
.mfa-toolbar .btn-primary{background:var(--primary-color);border:none}

/* Tabs */
.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{background:var(--surface);border-color:var(--line-strong) var(--line-strong) var(--surface)}
.tab-content,.tab-pane{overflow:visible}

/* Table Card */
.table-wrap.card{position:relative;border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:visible;z-index:1}
.table-wrap .card-body{overflow:visible}

/* ✅ SCROLLER FIX */
.table-responsive{overflow-x:auto;overflow-y:visible;-webkit-overflow-scrolling:touch;}
.table-responsive::-webkit-scrollbar{height:8px}
.table-responsive::-webkit-scrollbar-thumb{background:color-mix(in oklab, var(--muted-color) 25%, transparent);border-radius:999px}
.table-responsive::-webkit-scrollbar-track{background:color-mix(in oklab, var(--muted-color) 8%, transparent);border-radius:999px}

.table{--bs-table-bg:transparent; min-width:1080px;}
.table thead th{font-weight:600;color:var(--muted-color);font-size:13px;border-bottom:1px solid var(--line-strong);background:var(--surface)}
.table thead.sticky-top{z-index:3}
.table tbody tr{border-top:1px solid var(--line-soft)}
.table tbody tr:hover{background:var(--page-hover)}

/* Cell text */
td .fw-semibold{color:var(--ink)}
.small{font-size:12.5px}

/* ✅ UUID column (UPDATED)
   - UUID fully visible (no wrap/truncation)
   - Copy button ALWAYS beside UUID (no below)
*/
.uuid-cell{display:flex;align-items:center;gap:8px;flex-wrap:nowrap;              /* ✅ no wrap -> copy stays beside */justify-content:flex-start;}
.uuid-pill{font-size:12px;padding:3px 8px;border-radius:999px;border:1px solid var(--line-strong);background:color-mix(in oklab, var(--muted-color) 10%, transparent);color:var(--ink);white-space:nowrap;            /* ✅ full UUID on one line */flex:0 0 auto;                 /* ✅ don’t shrink/truncate */}
.uuid-copy-btn{height:28px;border-radius:10px;padding:0 10px;display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--line-strong);background:var(--surface);flex:0 0 auto;}
.uuid-copy-btn i{font-size:13px;opacity:.9}
.uuid-copy-btn:hover{background:var(--page-hover)}

/* ✅ Description clamp */
.dep-desc{max-width:420px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.25rem;}

/* Badges */
.table .badge.badge-success{background:var(--success-color)!important;color:#fff!important}
.table .badge.badge-warning{background:var(--warning-color)!important;color:#0b1324!important}
.table .badge.badge-secondary{background:#64748b!important;color:#fff!important}
.badge-soft{background:color-mix(in oklab, var(--muted-color) 12%, transparent);color:var(--ink)}

/* Sorting */
.sortable{cursor:pointer;white-space:nowrap}
.sortable .caret{display:inline-block;margin-left:.35rem;opacity:.65}
.sortable.asc .caret::after{content:"▲";font-size:.7rem}
.sortable.desc .caret::after{content:"▼";font-size:.7rem}

/* Row cues */
tr.is-inactive td{background:color-mix(in oklab, var(--muted-color) 6%, transparent)}
tr.is-deleted td{background:color-mix(in oklab, var(--danger-color) 6%, transparent)}

/* Dropdowns inside table */
.table-wrap .dropdown{position:relative;z-index:6}

/* ✅ UPDATED: use dedicated toggle class (like reference module) */
.dep-dd-toggle{border-radius:10px}

/* ✅ UPDATED: higher z-index + show must win (like reference module) */
.table-wrap .dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:220px;z-index:99999; /* ✅ higher to avoid being behind / clipped feeling */}
.dropdown-menu.show{display:block !important}

.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color)!important}

/* Empty & loader */
.empty{color:var(--muted-color)}
.placeholder{background:linear-gradient(90deg, #00000010, #00000005, #00000010);border-radius:8px}

/* Modals */
.modal-content{border-radius:16px;border:1px solid var(--line-strong);background:var(--surface)}
.modal-header{border-bottom:1px solid var(--line-strong)}
.modal-footer{border-top:1px solid var(--line-strong)}
.form-control,.form-select{border-radius:12px;border:1px solid var(--line-strong);background:#fff}
html.theme-dark .form-control,html.theme-dark .form-select{background:#0f172a;color:#e5e7eb;border-color:var(--line-strong)}
.modal-title i{opacity:.9}

/* Dark tweaks */
html.theme-dark .panel,
html.theme-dark .table-wrap.card,
html.theme-dark .modal-content{background:#0f172a;border-color:var(--line-strong)}
html.theme-dark .table thead th{background:#0f172a;border-color:var(--line-strong);color:#94a3b8}
html.theme-dark .table tbody tr{border-color:var(--line-soft)}
html.theme-dark .dropdown-menu{background:#0f172a;border-color:var(--line-strong)}
html.theme-dark .uuid-pill{background:rgba(148,163,184,.08)}
html.theme-dark .uuid-copy-btn{background:#0f172a}

/* Loading overlay */
.dep-loading-overlay{position:fixed;inset:0;width:100%;height:100%;background:rgba(0,0,0,.42);display:none;justify-content:center;align-items:center;z-index:9999;backdrop-filter:blur(2px)}
.dep-loading-overlay.is-visible{display:flex}
.dep-loading{background:var(--surface);padding:22px 24px;border-radius:14px;box-shadow:0 12px 30px rgba(0,0,0,.25);display:flex;flex-direction:column;align-items:center;gap:10px;min-width:220px}
.dep-ring{width:44px;height:44px;border:4px solid #e5e7eb;border-top-color:var(--primary-color);border-radius:50%;animation:dep-spin 1s linear infinite}
@keyframes dep-spin{to{transform:rotate(360deg)}}

/* Responsive toolbar */
@media (max-width:768px){
  .mfa-toolbar .d-flex{flex-direction:column;gap:12px!important}
  .mfa-toolbar .position-relative{min-width:100%!important}
  .toolbar-buttons{display:flex;gap:8px;flex-wrap:wrap}
  .toolbar-buttons .btn{flex:1;min-width:120px}
}

/* Button loading state */
.btn-loading{position:relative;pointer-events:none}
.btn-loading .btn-label{visibility:hidden}
.btn-loading::after{content:'';position:absolute;width:16px;height:16px;top:50%;left:50%;margin-left:-8px;margin-top:-8px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:dep-spin 1s linear infinite}

/* Inputs & buttons polish */
.form-label{font-weight:500;margin-bottom:8px}
.form-control:focus,.form-select:focus{border-color:var(--primary-color);box-shadow:0 0 0 .2rem rgba(158,54,58,.25)}
.btn-primary{transition:all .2s}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 8px rgba(158,54,58,.35)}
</style>
@endpush

@section('content')
<div class="dept-wrap">

  {{-- Loading Overlay --}}
  <div id="dep_globalLoading" class="dep-loading-overlay" aria-hidden="true">
    @include('partials.overlay')
  </div>

  {{-- ================= Tabs ================= --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#tab-dept-active" role="tab" aria-selected="true">
        <i class="fa-solid fa-building-columns me-2"></i>Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-dept-inactive" role="tab" aria-selected="false">
        <i class="fa-solid fa-building-circle-xmark me-2"></i>Inactive
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-dept-bin" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Bin
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- ========== TAB: Active Departments ========== --}}
    <div class="tab-pane fade show active" id="tab-dept-active" role="tabpanel">
      {{-- Toolbar --}}
      <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
        <div class="col-md-6 d-flex align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per page</label>
            <select id="dep_per_page" class="form-select" style="width:96px;">
              <option>10</option>
              <option selected>20</option>
              <option>30</option>
              <option>50</option>
              <option>100</option>
            </select>
          </div>

          <div class="position-relative flex-grow-1" style="min-width:200px;">
            <input id="dep_q" type="text" class="form-control ps-5" placeholder="Search by title, slug, short name, type...">
            <i class="fa fa-search position-absolute" style="left:12px; top:50%; transform:translateY(-50%); opacity:.6;"></i>
          </div>
        </div>

        <div class="col-md-6 d-flex justify-content-md-end mt-2 mt-md-0">
          <div class="toolbar-buttons d-flex gap-2">
            <button id="dep_btnFilter" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#dep_filterModal">
              <i class="fa fa-filter me-1"></i>Filter
            </button>
            <button id="dep_btnReset" class="btn btn-light">
              <i class="fa fa-rotate-left me-1"></i>Reset
            </button>
            <button id="dep_btnCreate" class="btn btn-primary">
              <i class="fa fa-plus me-1"></i>New Department
            </button>
          </div>
        </div>
      </div>

      {{-- Table --}}
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top" style="z-index:2;">
                <tr>
                  <th class="sortable" data-col="title">DEPARTMENT <span class="caret"></span></th>
                  <th style="width:320px;">UUID</th> {{-- ✅ widened for full UUID --}}
                  <th class="sortable" data-col="short_name" style="width:150px;">SHORT NAME <span class="caret"></span></th>
                  <th style="width:180px;">TYPE</th>
                  <th>DESCRIPTION</th>
                  <th style="width:130px;">STATUS</th>
                  <th class="sortable" data-col="created_at" style="width:170px;">CREATED <span class="caret"></span></th>
                  <th class="text-end" style="width:108px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="dep_rows-active">
                <tr id="dep_loaderRow-active" style="display:none;">
                  <td colspan="8" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          {{-- Empty --}}
          <div id="dep_empty-active" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-building-columns mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No active departments found.</div>
          </div>

          {{-- Pagination --}}
          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="dep_metaTxt-active">—</div>
            <nav><ul id="dep_pager-active" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ========== TAB: Inactive Departments ========== --}}
    <div class="tab-pane fade" id="tab-dept-inactive" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>DEPARTMENT</th>
                  <th style="width:320px;">UUID</th>
                  <th style="width:150px;">SHORT NAME</th>
                  <th style="width:180px;">TYPE</th>
                  <th>DESCRIPTION</th>
                  <th style="width:130px;">STATUS</th>
                  <th style="width:170px;">CREATED</th>
                  <th class="text-end" style="width:108px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="dep_rows-inactive">
                <tr id="dep_loaderRow-inactive" style="display:none;">
                  <td colspan="8" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="dep_empty-inactive" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-building-circle-xmark mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No inactive departments.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="dep_metaTxt-inactive">—</div>
            <nav><ul id="dep_pager-inactive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ========== TAB: Bin (Soft Deleted) ========== --}}
    <div class="tab-pane fade" id="tab-dept-bin" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>DEPARTMENT</th>
                  <th style="width:320px;">UUID</th>
                  <th style="width:150px;">SHORT NAME</th>
                  <th style="width:180px;">TYPE</th>
                  <th>DESCRIPTION</th>
                  <th style="width:170px;">DELETED AT</th>
                  <th class="text-end" style="width:140px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="dep_rows-bin">
                <tr id="dep_loaderRow-bin" style="display:none;">
                  <td colspan="7" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="dep_empty-bin" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-trash-can mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>Bin is empty.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="dep_metaTxt-bin">—</div>
            <nav><ul id="dep_pager-bin" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.tab-content -->
</div>

{{-- ================= Filter Modal ================= --}}
<div class="modal fade" id="dep_filterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-filter me-2"></i>Filter Departments</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="dep_modal_sort" class="form-select">
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="title">Title A-Z</option>
              <option value="-title">Title Z-A</option>
              <option value="short_name">Short Name A-Z</option>
              <option value="-short_name">Short Name Z-A</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="dep_btnApplyFilters" class="btn btn-primary">
          <i class="fa fa-check me-1"></i>Apply Filters
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ================= Create/Edit Department Modal ================= --}}
<div class="modal fade" id="departmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="dep_modalTitle" class="modal-title">
          <i class="fa fa-building-columns me-2"></i>Create Department
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="departmentForm">
          @csrf
          <input type="hidden" id="dep_mode" value="create">
          <input type="hidden" id="dep_key" value="">

          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Department Title <span class="text-danger">*</span></label>
              <input type="text" id="dep_title" name="title" class="form-control" maxlength="150"
                     placeholder="e.g., Computer Science and Engineering" required>
            </div>

            <div class="col-md-4">
              <label class="form-label">Short Name</label>
              <input type="text" id="dep_short_name" name="short_name" class="form-control" maxlength="60"
                     placeholder="e.g., CSE, ECE, EE">
            </div>

            <div class="col-md-4">
              <label class="form-label">Department Type</label>
              <input type="text" id="dep_department_type" name="department_type" class="form-control" maxlength="60"
                     placeholder="e.g., UG, PG, Core, Allied">
            </div>

            <div class="col-md-4">
              <label class="form-label">Slug (optional)</label>
              <input type="text" id="dep_slug" name="slug" class="form-control" maxlength="160"
                     placeholder="e.g., cse, ece, bca">
              <div class="form-text">If left blank, slug will be generated from title.</div>
            </div>

            <div class="col-md-4">
              <label class="form-label d-block">&nbsp;</label>
              <div class="form-check form-switch d-inline-flex align-items-center gap-2">
                <input class="form-check-input" type="checkbox" id="dep_active" checked>
                <label class="form-check-label" for="dep_active">Active</label>
              </div>
            </div>

            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea id="dep_description" name="description" class="form-control" rows="4"
                        placeholder="Write department description..."></textarea>
              <div class="form-text">You can add plain text (or HTML if needed).</div>
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button id="dep_btnSave" class="btn btn-primary">
          <span class="btn-label">
            <i class="fa fa-save me-1"></i>Save Department
          </span>
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ================= View Department Modal ================= --}}
<div class="modal fade" id="viewDepartmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-eye me-2"></i>Department Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="viewDepartmentContent"></div>
      <div class="modal-footer">
        <button class="btn btn-primary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="dep_okToast" class="toast text-bg-success border-0" role="status" aria-live="polite">
    <div class="d-flex">
      <div id="dep_okMsg" class="toast-body">Done</div>
      <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
  <div id="dep_errToast" class="toast text-bg-danger border-0 mt-2" role="alert" aria-live="assertive">
    <div class="d-flex">
      <div id="dep_errMsg" class="toast-body">Something went wrong</div>
      <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  // ✅ prevent double init (safe)
  if (window.__DEPARTMENT_MODULE_INIT__) return;
  window.__DEPARTMENT_MODULE_INIT__ = true;

  /* ===== Auth ===== */
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const ROLE  = (localStorage.getItem('role') || sessionStorage.getItem('role') || '').toLowerCase();
  if (!TOKEN) {
    Swal.fire('Login required', 'Your session has expired. Please login again.', 'warning')
      .then(()=> location.href = '/');
    return;
  }

  const API_BASE = '/api';
  const DEPT_ENDPOINT       = API_BASE + '/departments';
  const DEPT_TRASH_ENDPOINT = API_BASE + '/departments-trash';

  /* ===== Toast helpers ===== */
  const okToastEl  = document.getElementById('dep_okToast');
  const errToastEl = document.getElementById('dep_errToast');
  const okToast  = new bootstrap.Toast(okToastEl,  { delay: 2200, autohide: true });
  const errToast = new bootstrap.Toast(errToastEl, { delay: 2600, autohide: true });
  const ok  = (m)=>{ errToast.hide(); document.getElementById('dep_okMsg').textContent  = m || 'Done'; okToast.show(); };
  const err = (m)=>{ okToast.hide();  document.getElementById('dep_errMsg').textContent = m || 'Something went wrong'; errToast.show(); };

  /* ===== DOM refs ===== */
  const q            = document.getElementById('dep_q');
  const perPageSel   = document.getElementById('dep_per_page');
  const btnReset     = document.getElementById('dep_btnReset');
  const btnCreate    = document.getElementById('dep_btnCreate');
  const btnSave      = document.getElementById('dep_btnSave');
  const globalLoader = document.getElementById('dep_globalLoading');

  const deptModalEl  = document.getElementById('departmentModal');
  const deptModal    = new bootstrap.Modal(deptModalEl);
  const viewModal    = new bootstrap.Modal(document.getElementById('viewDepartmentModal'));

  // ✅ NEW (FIX): get a stable instance + cleanup leftover backdrop after Filter modal closes
  const filterModalEl = document.getElementById('dep_filterModal');
  const filterModal   = filterModalEl ? bootstrap.Modal.getOrCreateInstance(filterModalEl) : null;

  // ✅ NEW (FIX): cleanup helper (only when no other modal is open)
  function cleanupModalArtifactsIfSafe(){
    if (document.querySelector('.modal.show')) return; // another modal is still open
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('padding-right');
  }

  if (filterModalEl){
    filterModalEl.addEventListener('hidden.bs.modal', () => {
      cleanupModalArtifactsIfSafe();
    });
  }

  deptModalEl.addEventListener('hidden.bs.modal', () => {
    document.querySelectorAll('.modal-backdrop.show').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('padding-right');
  });

  const tabs = {
    active:  { rows:'#dep_rows-active',   loader:'#dep_loaderRow-active',   empty:'#dep_empty-active',   meta:'#dep_metaTxt-active',   pager:'#dep_pager-active' },
    inactive:{ rows:'#dep_rows-inactive', loader:'#dep_loaderRow-inactive', empty:'#dep_empty-inactive', meta:'#dep_metaTxt-inactive', pager:'#dep_pager-inactive' },
    bin:     { rows:'#dep_rows-bin',      loader:'#dep_loaderRow-bin',      empty:'#dep_empty-bin',      meta:'#dep_metaTxt-bin',      pager:'#dep_pager-bin' }
  };

  let sort = '-created_at';
  const state = { active:{page:1}, inactive:{page:1}, bin:{page:1} };

  /* ===== Utils ===== */
  function getAuthHeaders(isJson = true){
    const h = { 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' };
    if (isJson) h['Content-Type'] = 'application/json';
    return h;
  }
  function escapeHtml(s){ const map={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'}; return (s==null?'':String(s)).replace(/[&<>"'`]/g,ch=>map[ch]); }
  function decodeHtml(s){ const t=document.createElement('textarea'); t.innerHTML = s==null?'':String(s); return t.value; }
  function fmtDate(iso){ if(!iso) return '-'; const d = new Date(iso); if(isNaN(d)) return escapeHtml(iso); return d.toLocaleString(undefined,{year:'numeric',month:'short',day:'2-digit',hour:'2-digit',minute:'2-digit'}); }
  function parseSortKey(key){ let dir='asc'; let col=key||'created_at'; if(col.startsWith('-')){dir='desc'; col=col.slice(1)||'created_at'} return { col, dir }; }
  function stripTags(html){ return (html==null?'':String(html)).replace(/<[^>]*>/g,' ').replace(/\s+/g,' ').trim(); }
  function truncate(s, n=120){ const t = stripTags(s); return t.length>n ? t.slice(0,n-1)+'…' : t; }

  function badgeForRow(row){
    if (row.deleted_at) return `<span class="badge badge-danger text-uppercase">Deleted</span>`;
    return row.active ? `<span class="badge badge-success text-uppercase">Active</span>`
                      : `<span class="badge badge-warning text-uppercase">Inactive</span>`;
  }

  async function copyText(text){
    const val = (text || '').trim();
    if (!val) return;
    try{
      if (navigator.clipboard && window.isSecureContext){
        await navigator.clipboard.writeText(val);
      } else {
        const ta = document.createElement('textarea');
        ta.value = val;
        ta.setAttribute('readonly','readonly');
        ta.style.position='fixed';
        ta.style.left='-9999px';
        ta.style.top='-9999px';
        document.body.appendChild(ta);
        ta.focus(); ta.select();
        document.execCommand('copy');
        ta.remove();
      }
      ok('UUID copied');
    } catch(e){
      console.error('Copy failed', e);
      err('Failed to copy UUID');
    }
  }

  let _loadCount = 0, _hideTimer = null;
  function showGlobalLoading(show){
    _loadCount += show ? 1 : -1;
    if (_loadCount < 0) _loadCount = 0;
    if (show) {
      if (_hideTimer) { clearTimeout(_hideTimer); _hideTimer = null; }
      globalLoader.classList.add('is-visible');
      document.body.classList.add('overflow-hidden');
    } else {
      _hideTimer = setTimeout(() => {
        if (_loadCount === 0) {
          globalLoader.classList.remove('is-visible');
          document.body.classList.remove('overflow-hidden');
        }
      }, 120);
    }
  }

  function setButtonLoading(btn, loading){
    if (!btn) return;
    if (loading) { btn.disabled = true; btn.classList.add('btn-loading'); }
    else { btn.disabled = false; btn.classList.remove('btn-loading'); }
  }

  function showLoader(scope, show){
    const el = document.querySelector(tabs[scope].loader);
    if (el) el.style.display = show ? '' : 'none';
  }

  /* ===== Query params ===== */
  function buildQuery(scope){
    const params = new URLSearchParams();
    const page   = state[scope].page || 1;
    const per    = Number(perPageSel?.value || 20);
    params.set('page', page); params.set('per_page', per);
    const { col, dir } = parseSortKey(sort);
    if (scope !== 'bin') { params.set('sort', col); params.set('direction', dir); }
    const search = q.value.trim(); if (search) params.set('q', search);
    if (scope === 'active') params.set('active','1');
    else if (scope === 'inactive') params.set('active','0');
    return params.toString();
  }

  function applyFromURL(){
    const url = new URL(location.href);
    const g = (k)=> url.searchParams.get(k) || '';
    const qVal=g('q'), per=g('per_page'), sortVal=g('sort'), dir=g('direction');
    if (qVal) q.value = qVal;
    if (per) perPageSel.value = per;
    if (sortVal) sort = (dir && dir.toLowerCase()==='asc') ? sortVal : ('-' + sortVal);
    document.getElementById('dep_modal_sort').value = sort;
    syncSortHeaders();
  }

  function syncSortHeaders(){
    document.querySelectorAll('#tab-dept-active th.sortable').forEach(th=>{
      th.classList.remove('asc','desc');
      const col = th.dataset.col;
      if (sort === col) th.classList.add('asc');
      if (sort === '-' + col) th.classList.add('desc');
    });
  }

  function pushURL(scope){
    if (scope !== 'active') return;
    const params = new URLSearchParams(buildQuery('active'));
    history.replaceState(null, '', location.pathname + '?' + params.toString());
  }

  /* ===== Render ===== */
  function rowActions(scope, r){
    const nameAttr = escapeHtml(r.title || ''); const idAttr = r.id;

    // ✅ IMPORTANT FIX:
    // Button rendered WITHOUT data-bs-toggle, and handled manually (fixed popper strategy)
    if (scope === 'bin') {
      return `
        <div class="dropdown text-end">
          <button type="button" class="btn btn-light btn-sm dep-dd-toggle"
                  aria-expanded="false" title="Actions">
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button class="dropdown-item" data-act="restore" data-id="${idAttr}" data-name="${nameAttr}">
              <i class="fa fa-rotate-left"></i> Restore</button></li>
            <li><button class="dropdown-item text-danger" data-act="forceDelete" data-id="${idAttr}" data-name="${nameAttr}">
              <i class="fa fa-trash-can"></i> Delete Permanently</button></li>
          </ul>
        </div>`;
    }

    const isActive = !!r.active;
    const toggleAct = isActive ? 'deactivate' : 'activate';
    const toggleIcon = isActive ? 'fa-toggle-off' : 'fa-toggle-on';
    const toggleLabel = isActive ? 'Mark Inactive' : 'Mark Active';

    return `
      <div class="dropdown text-end">
        <button type="button" class="btn btn-light btn-sm dep-dd-toggle"
                aria-expanded="false" title="Actions">
          <i class="fa fa-ellipsis-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><button class="dropdown-item" data-act="view" data-id="${idAttr}" data-name="${nameAttr}">
            <i class="fa fa-eye"></i> View Details</button></li>
          <li><button class="dropdown-item" data-act="edit" data-id="${idAttr}" data-name="${nameAttr}">
            <i class="fa fa-pen-to-square"></i> Edit</button></li>
          <li><hr class="dropdown-divider"></li>
          <li><button class="dropdown-item" data-act="${toggleAct}" data-id="${idAttr}" data-name="${nameAttr}">
            <i class="fa ${toggleIcon}"></i> ${toggleLabel}</button></li>
          <li><button class="dropdown-item text-danger" data-act="softDelete" data-id="${idAttr}" data-name="${nameAttr}">
            <i class="fa fa-trash"></i> Move to Bin</button></li>
        </ul>
      </div>`;
  }

  function uuidCell(uuid){
    if (!uuid) return `<span class="text-muted">—</span>`;
    const safe = escapeHtml(uuid);
    return `
      <div class="uuid-cell">
        <code class="uuid-pill font-monospace">${safe}</code>
        <button type="button" class="uuid-copy-btn" data-copy="${safe}" title="Copy UUID">
          <i class="fa-regular fa-copy"></i>
        </button>
      </div>
    `;
  }

  function renderRows(scope, items){
    const rowsEl = document.querySelector(tabs[scope].rows);
    rowsEl.querySelectorAll('tr:not([id^="dep_loaderRow"])').forEach(tr=>tr.remove());
    const frag = document.createDocumentFragment();

    items.forEach(r=>{
      const tr = document.createElement('tr');
      if (r.deleted_at) tr.classList.add('is-deleted');
      else if (!r.active) tr.classList.add('is-inactive');

      const shortName = r.short_name ? escapeHtml(r.short_name) : `<span class="text-muted">—</span>`;
      const deptType  = r.department_type ? escapeHtml(r.department_type) : `<span class="text-muted">—</span>`;
      const desc      = r.description ? escapeHtml(truncate(r.description, 140)) : `<span class="text-muted">—</span>`;

      if (scope === 'bin') {
        tr.innerHTML = `
          <td>
            <div class="fw-semibold">${escapeHtml(r.title || '')}</div>
            ${r.slug ? `<div class="text-muted small">Slug: ${escapeHtml(r.slug)}</div>` : ''}
          </td>
          <td>${uuidCell(r.uuid)}</td>
          <td>${shortName}</td>
          <td>${deptType}</td>
          <td><div class="text-muted small dep-desc">${desc}</div></td>
          <td>${fmtDate(r.deleted_at)}</td>
          <td class="text-end">${rowActions(scope, r)}</td>
        `;
      } else {
        tr.innerHTML = `
          <td>
            <div class="fw-semibold">${escapeHtml(r.title || '')}</div>
            ${r.slug ? `<div class="text-muted small">Slug: ${escapeHtml(r.slug)}</div>` : ''}
          </td>
          <td>${uuidCell(r.uuid)}</td>
          <td>${shortName}</td>
          <td>${deptType}</td>
          <td><div class="text-muted small dep-desc">${desc}</div></td>
          <td>${badgeForRow(r)}</td>
          <td>${fmtDate(r.created_at)}</td>
          <td class="text-end">${rowActions(scope, r)}</td>
        `;
      }

      frag.appendChild(tr);
    });

    rowsEl.appendChild(frag);
  }

  function renderPager(scope, pagination){
    const pagerEl = document.querySelector(tabs[scope].pager);
    const metaEl  = document.querySelector(tabs[scope].meta);
    const total   = Number(pagination.total || 0);
    const per     = Number(pagination.per_page || Number(perPageSel.value || 20));
    const current = Number(pagination.page || 1);
    const totalPages = Math.max(1, Math.ceil(total / per));

    function li(disabled, active, label, target){
      const cls = ['page-item', disabled?'disabled':'', active?'active':''].filter(Boolean).join(' ');
      const href = disabled ? '#' : 'javascript:void(0)';
      return `<li class="${cls}"><a class="page-link" href="${href}" data-page="${target || ''}">${label}</a></li>`;
    }

    let html = '';
    html += li(current <= 1, false, 'Previous', current - 1);
    const span = 3;
    const start = Math.max(1, current - span);
    const end   = Math.min(totalPages, current + span);
    if (start > 1){ html += li(false,false,1,1); if (start > 2) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`; }
    for (let p = start; p <= end; p++) html += li(false, p===current, p, p);
    if (end < totalPages){ if (end < totalPages - 1) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`; html += li(false,false,totalPages,totalPages); }
    html += li(current >= totalPages, false, 'Next', current + 1);

    pagerEl.innerHTML = html;
    pagerEl.querySelectorAll('a.page-link[data-page]').forEach(a=>{
      a.addEventListener('click', ()=>{
        const target = Number(a.dataset.page);
        if (!target || target === state[scope].page) return;
        state[scope].page = Math.max(1, target);
        load(scope);
      });
    });

    metaEl.textContent = `Showing page ${current} of ${totalPages} — ${total} result(s)`;
  }

  async function load(scope){
    showLoader(scope, true);
    const emptyEl = document.querySelector(tabs[scope].empty);
    const rowsEl  = document.querySelector(tabs[scope].rows);
    const metaEl  = document.querySelector(tabs[scope].meta);
    emptyEl.style.display = 'none';
    rowsEl.querySelectorAll('tr:not([id^="dep_loaderRow"])').forEach(tr => tr.remove());
    if (scope === 'active') pushURL('active');

    try {
      const url = (scope === 'bin')
        ? (DEPT_TRASH_ENDPOINT + '?' + buildQuery(scope))
        : (DEPT_ENDPOINT      + '?' + buildQuery(scope));

      const res  = await fetch(url, { headers: getAuthHeaders() });
      const json = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(json?.message || json?.error || 'Failed to load departments');

      const items = Array.isArray(json.data) ? json.data : [];
      const pagination = json.pagination || { page: state[scope].page, per_page: Number(perPageSel.value || 20), total: items.length };
      if (!items.length) emptyEl.style.display = '';
      renderRows(scope, items);
      renderPager(scope, pagination);
    } catch (e) {
      console.error('Load error', scope, e);
      emptyEl.style.display = '';
      if (metaEl) metaEl.textContent = e?.message || 'Failed to load departments';
      err(e?.message || 'Failed to load departments');
    } finally {
      showLoader(scope, false);
      if (scope === 'active') syncSortHeaders();
    }
  }

  async function openView(id){
    showGlobalLoading(true);
    try {
      const res = await fetch(`${DEPT_ENDPOINT}/${encodeURIComponent(id)}?with_trashed=1`, { headers: getAuthHeaders() });
      const j = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(j?.message || j?.error || 'Failed to load');

      const d = j.department || j.data || j;
      const html = `
        <div class="row g-3">
          <div class="col-12"><strong>Title:</strong><br>${escapeHtml(d.title || '-')}</div>
          <div class="col-md-6"><strong>Short Name:</strong><br>${escapeHtml(d.short_name || '-')}</div>
          <div class="col-md-6"><strong>Department Type:</strong><br>${escapeHtml(d.department_type || '-')}</div>
          <div class="col-12"><strong>Description:</strong><br><div class="small text-muted">${escapeHtml(stripTags(d.description || '-') || '-')}</div></div>
          <div class="col-md-6"><strong>Slug:</strong><br>${escapeHtml(d.slug || '-')}</div>
          <div class="col-md-6"><strong>Status:</strong><br>${badgeForRow(d)}</div>
          <div class="col-md-6"><strong>Created At:</strong><br>${fmtDate(d.created_at)}</div>
          ${d.deleted_at ? `<div class="col-md-6"><strong>Deleted At:</strong><br>${fmtDate(d.deleted_at)}</div>` : ''}
          ${d.uuid ? `<div class="col-md-6"><strong>UUID:</strong><br><span class="small">${escapeHtml(d.uuid)}</span></div>` : ''}
        </div>`;
      document.getElementById('viewDepartmentContent').innerHTML = html;
      viewModal.show();
    } catch (e) { err(e?.message || 'Failed to load details'); }
    finally { showGlobalLoading(false); }
  }

  function openCreate(){
    document.getElementById('dep_mode').value = 'create';
    document.getElementById('dep_key').value  = '';
    document.getElementById('dep_modalTitle').innerHTML = `<i class="fa fa-building-columns me-2"></i>Create Department`;
    document.getElementById('departmentForm').reset();
    document.getElementById('dep_active').checked = true;
    deptModal.show();
  }

  async function openEdit(id){
    showGlobalLoading(true);
    try {
      const res = await fetch(`${DEPT_ENDPOINT}/${encodeURIComponent(id)}`, { headers: getAuthHeaders() });
      const j = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(j?.message || j?.error || 'Failed to load');

      const d = j.department || j.data || j;
      document.getElementById('dep_mode').value = 'edit';
      document.getElementById('dep_key').value  = d.id;
      document.getElementById('dep_modalTitle').innerHTML = `<i class="fa fa-building-columns me-2"></i>Edit Department`;
      document.getElementById('dep_title').value            = d.title || '';
      document.getElementById('dep_slug').value             = d.slug || '';
      document.getElementById('dep_short_name').value       = d.short_name || '';
      document.getElementById('dep_department_type').value  = d.department_type || '';
      document.getElementById('dep_description').value      = stripTags(d.description || '');
      document.getElementById('dep_active').checked         = !!d.active;
      deptModal.show();
    } catch (e) { err(e?.message || 'Failed to load department'); }
    finally { showGlobalLoading(false); }
  }

  async function saveDepartment(){
    const form = document.getElementById('departmentForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    const mode   = document.getElementById('dep_mode').value;
    const key    = document.getElementById('dep_key').value;

    const title  = document.getElementById('dep_title').value.trim();
    const slug   = document.getElementById('dep_slug').value.trim();
    const shortName = document.getElementById('dep_short_name').value.trim();
    const deptType  = document.getElementById('dep_department_type').value.trim();
    const desc      = document.getElementById('dep_description').value.trim();
    const active = document.getElementById('dep_active').checked;

    const payload = { title, active };
    if (slug) payload.slug = slug;
    if (shortName) payload.short_name = shortName;
    if (deptType) payload.department_type = deptType;
    if (desc) payload.description = desc;

    let url = DEPT_ENDPOINT, method = 'POST';
    if (mode === 'edit' && key) { url = `${DEPT_ENDPOINT}/${encodeURIComponent(key)}`; method = 'PUT'; }

    setButtonLoading(btnSave, true);
    showGlobalLoading(true);
    try {
      const res = await fetch(url, { method, headers: getAuthHeaders(true), body: JSON.stringify(payload) });
      const j = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(j?.message || j?.error || 'Save failed');

      ok('Department saved');
      deptModal.hide();
      await Promise.all([load('active'), load('inactive')]);
    } catch (e) {
      err(e?.message || 'Save failed');
    } finally {
      setButtonLoading(btnSave, false);
      showGlobalLoading(false);
    }
  }

  /* ===== Sorting only Active tab ===== */
  document.querySelectorAll('#tab-dept-active th.sortable').forEach(th=>{
    th.addEventListener('click', ()=>{
      const col = th.dataset.col;
      if (sort === col) sort = '-' + col;
      else if (sort === '-' + col) sort = col;
      else sort = (col === 'created_at') ? '-created_at' : col;
      document.getElementById('dep_modal_sort').value = sort;
      state.active.page = 1; syncSortHeaders(); load('active');
    });
  });

  let searchTimer;
  q.addEventListener('input', ()=>{
    clearTimeout(searchTimer);
    searchTimer = setTimeout(()=>{
      state.active.page = state.inactive.page = state.bin.page = 1;
      load('active'); load('inactive'); load('bin');
    }, 350);
  });

  perPageSel.addEventListener('change', ()=>{
    state.active.page = state.inactive.page = state.bin.page = 1;
    load('active'); load('inactive'); load('bin');
  });

  btnReset.addEventListener('click', ()=>{
    q.value=''; perPageSel.value='20'; sort='-created_at';
    document.getElementById('dep_modal_sort').value = sort;
    state.active.page = state.inactive.page = state.bin.page = 1;
    load('active'); load('inactive'); load('bin');
  });

  document.getElementById('dep_btnApplyFilters').addEventListener('click', ()=>{
    sort = document.getElementById('dep_modal_sort').value || '-created_at';
    state.active.page = 1;

    // ✅ FIX: hide using stable instance + ensure stray backdrop is cleared
    if (filterModal) filterModal.hide();
    else bootstrap.Modal.getOrCreateInstance(document.getElementById('dep_filterModal'))?.hide();
    setTimeout(cleanupModalArtifactsIfSafe, 350);

    load('active');
  });

  btnCreate.addEventListener('click', openCreate);
  btnSave.addEventListener('click', (e)=>{ e.preventDefault(); saveDepartment(); });

  /* ✅ Copy UUID click */
  document.addEventListener('click', (e)=>{
    const btn = e.target.closest('[data-copy]');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    const uuid = btn.getAttribute('data-copy') || '';
    copyText(uuid);
  });

  /* ============================
   ✅ DROPDOWN FIX (from reference page)
   - Manual toggle with Popper "fixed" strategy
   - Works even inside table overflow containers
  ============================ */
  function closeAllDeptDropdownsExcept(exceptToggle){
    document.querySelectorAll('.dep-dd-toggle').forEach(t => {
      if (exceptToggle && t === exceptToggle) return;
      try{
        const inst = bootstrap.Dropdown.getInstance(t);
        inst && inst.hide();
      }catch(_){}
    });
  }

  document.addEventListener('click', (e) => {
    const toggle = e.target.closest('.dep-dd-toggle');
    if (!toggle) return;

    e.preventDefault();
    e.stopPropagation();

    closeAllDeptDropdownsExcept(toggle);

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
    } catch(ex){
      console.error('Department dropdown toggle failed', ex);
    }
  });

  // close dropdowns on outside click (not inside dropdown)
  document.addEventListener('click', (e) => {
    if (e.target.closest('.dropdown')) return;
    closeAllDeptDropdownsExcept(null);
  }, { capture: true });

  /* actions */
  document.addEventListener('click', async (e)=>{
    const item = e.target.closest('.dropdown-item[data-act]');
    if (!item) return;

    const act  = item.dataset.act;
    const id   = item.dataset.id;
    const name = decodeHtml(item.dataset.name || '');

    async function toggleActive(id, name){
      const { isConfirmed } = await Swal.fire({
        icon: 'question',
        title: 'Change status?',
        html: `<b>${escapeHtml(name || 'This department')}</b> will toggle between active/inactive.`,
        showCancelButton: true,
        confirmButtonText: 'Yes, toggle'
      });
      if (!isConfirmed) return;

      showGlobalLoading(true);
      try {
        const res = await fetch(`${DEPT_ENDPOINT}/${encodeURIComponent(id)}/toggle-active`, { method:'PATCH', headers: getAuthHeaders() });
        const j = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(j?.message || j?.error || 'Toggle failed');
        ok('Status updated');
        await Promise.all([load('active'), load('inactive')]);
      } catch (e) { err(e?.message || 'Toggle failed'); }
      finally { showGlobalLoading(false); }
    }

    async function softDelete(id, name){
      const { isConfirmed } = await Swal.fire({
        icon:'warning', title:'Move to Bin?',
        html:`<b>${escapeHtml(name || 'This department')}</b> will be moved to Bin (soft delete).`,
        showCancelButton:true, confirmButtonText:'Move to Bin', confirmButtonColor:'#ef4444'
      });
      if (!isConfirmed) return;

      showGlobalLoading(true);
      try {
        const res = await fetch(`${DEPT_ENDPOINT}/${encodeURIComponent(id)}`, { method:'DELETE', headers: getAuthHeaders() });
        const j = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(j?.message || j?.error || 'Delete failed');
        ok('Moved to Bin');
        await Promise.all([load('active'), load('inactive'), load('bin')]);
      } catch (e) { err(e?.message || 'Delete failed'); }
      finally { showGlobalLoading(false); }
    }

    async function restore(id, name){
      const { isConfirmed } = await Swal.fire({
        icon:'question', title:'Restore department?',
        html:`<b>${escapeHtml(name || 'This department')}</b> will be restored.`,
        showCancelButton:true, confirmButtonText:'Restore', confirmButtonColor:'#10b981'
      });
      if (!isConfirmed) return;

      showGlobalLoading(true);
      try {
        const res = await fetch(`${DEPT_ENDPOINT}/${encodeURIComponent(id)}/restore`, { method:'POST', headers: getAuthHeaders() });
        const j = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(j?.message || j?.error || 'Restore failed');
        ok('Department restored');
        await Promise.all([load('active'), load('inactive'), load('bin')]);
      } catch (e) { err(e?.message || 'Restore failed'); }
      finally { showGlobalLoading(false); }
    }

    async function forceDelete(id, name){
      const { isConfirmed } = await Swal.fire({
        icon:'warning', title:'Delete permanently?',
        html:`This will <b>permanently delete</b> the department.<br><br><b>${escapeHtml(name || 'This department')}</b>`,
        showCancelButton:true, confirmButtonText:'Delete Permanently', confirmButtonColor:'#b91c1c'
      });
      if (!isConfirmed) return;

      showGlobalLoading(true);
      try {
        const res = await fetch(`${DEPT_ENDPOINT}/${encodeURIComponent(id)}/force`, { method:'DELETE', headers: getAuthHeaders() });
        const j = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(j?.message || j?.error || 'Delete failed');
        ok('Department deleted permanently');
        await load('bin');
      } catch (e) { err(e?.message || 'Delete failed'); }
      finally { showGlobalLoading(false); }
    }

    if (act === 'view') openView(id);
    else if (act === 'edit') openEdit(id);
    else if (act === 'activate' || act === 'deactivate') toggleActive(id, name);
    else if (act === 'softDelete') softDelete(id, name);
    else if (act === 'restore') restore(id, name);
    else if (act === 'forceDelete') forceDelete(id, name);

    // ✅ UPDATED: close the dropdown after clicking an action
    const dd = item.closest('.dropdown');
    if (dd) {
      const btn = dd.querySelector('.dep-dd-toggle');
      if (btn) {
        try { bootstrap.Dropdown.getInstance(btn)?.hide(); } catch(_){}
      }
    }
  });

  document.querySelector('a[href="#tab-dept-active"]').addEventListener('shown.bs.tab', ()=> load('active'));
  document.querySelector('a[href="#tab-dept-inactive"]').addEventListener('shown.bs.tab', ()=> load('inactive'));
  document.querySelector('a[href="#tab-dept-bin"]').addEventListener('shown.bs.tab', ()=> load('bin'));

  applyFromURL();
  load('active');
  load('inactive');
})();
</script>
@endpush
