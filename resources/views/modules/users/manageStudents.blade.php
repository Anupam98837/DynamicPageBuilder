{{-- resources/views/modules/user/manageUsers.blade.php --}}
@section('title','Manage Users')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
.table-wrap .dropdown{position:relative}
.dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
.dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:240px;z-index:1049}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color) !important}

.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{background:var(--surface);border-color:var(--line-strong) var(--line-strong) var(--surface)}
.tab-content,.tab-pane{overflow:visible}

.musers-toolbar.panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2);padding:12px;}

.table-wrap.card{position:relative;border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:hidden;}
.table-wrap .card-body{overflow:visible;padding:0}
.table{--bs-table-bg:transparent;margin-bottom:0}
.table thead th{font-weight:600;color:var(--muted-color);font-size:13px;border-bottom:1px solid var(--line-strong);background:var(--surface);position:sticky;top:0;z-index:10;}
.table tbody tr{border-top:1px solid var(--line-soft)}
.table tbody tr:hover{background:var(--page-hover)}
td .fw-semibold{color:var(--ink)}
.small{font-size:12.5px}

.badge-soft-primary{background:color-mix(in oklab, var(--primary-color) 12%, transparent);color:var(--primary-color)}
.badge-soft-success{background:color-mix(in oklab, var(--success-color) 12%, transparent);color:var(--success-color)}
.badge-soft-danger{background:color-mix(in oklab, var(--danger-color) 12%, transparent);color:var(--danger-color)}

.loading-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.45);display:flex;justify-content:center;align-items:center;z-index:9999;backdrop-filter:blur(2px)}

.btn-loading{position:relative;color:transparent !important;pointer-events:none}
.btn-loading::after{content:'';position:absolute;width:16px;height:16px;top:50%;left:50%;margin:-8px 0 0 -8px;border:2px solid transparent;border-top:2px solid currentColor;border-radius:50%;animation:spin 1s linear infinite}
@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

@media (max-width: 768px){
  .musers-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .musers-toolbar .position-relative{min-width:100% !important}
  .toolbar-buttons{display:flex;gap:8px;flex-wrap:wrap}
  .toolbar-buttons .btn{flex:1;min-width:140px}
}

.table-responsive{display:block;width:100%;overflow-x:auto;overflow-y:visible;-webkit-overflow-scrolling:touch;}
/* ✅ updated min-width a bit for new Department column */
.table-responsive > .table{width:100%;min-width:1080px;table-layout:auto;}
.table-responsive th,
.table-responsive td{white-space:nowrap;padding:12px 16px;}
@media (max-width: 992px){
  .table-responsive > .table{min-width:1020px}
}
@media (max-width: 576px){
  .table-responsive > .table{min-width:940px}
  .table-responsive th,
  .table-responsive td{padding:10px 12px}
}

.wiz-steps{display:flex;gap:10px;flex-wrap:wrap}
.wiz-step{display:flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid var(--line-strong);border-radius:14px;background:var(--surface);transition:all .2s ease;}
.wiz-step .num{width:28px;height:28px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:900;background:color-mix(in oklab, var(--primary-color) 12%, transparent);color:var(--primary-color);}
.wiz-step.active{border-color:color-mix(in oklab, var(--primary-color) 40%, var(--line-strong));box-shadow:var(--shadow-2);}
.wiz-step.active .num{background:var(--primary-color);color:#fff}
.wiz-step .lbl{font-weight:800}
.wiz-step .sub{font-size:12px;color:var(--muted-color);line-height:1.1}

.wiz-pane{display:none}
.wiz-pane.active{display:block}

.wiz-hint{padding:10px 12px;border:1px dashed var(--line-strong);border-radius:14px;background:color-mix(in oklab, var(--surface) 88%, transparent);color:var(--muted-color);font-size:12.5px;}

.modal{z-index:1055}
.modal-backdrop{z-index:1050}

.table .dropdown-menu{position:absolute;z-index:1049}

.form-control.is-invalid,
.form-select.is-invalid{border-color:var(--danger-color)}
.invalid-feedback{display:block;color:var(--danger-color);font-size:12px;margin-top:4px}
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
        <i class="fa-solid fa-user-check me-2"></i>Active Users
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-inactive" role="tab" aria-selected="false">
        <i class="fa-solid fa-user-slash me-2"></i>Inactive Users
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">
    {{-- ACTIVE TAB --}}
    <div class="tab-pane fade show active" id="tab-active" role="tabpanel">
      {{-- Toolbar --}}
      <div class="row align-items-center g-2 mb-3 musers-toolbar panel">
        <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per Page</label>
            <select id="perPage" class="form-select" style="width:96px;">
              <option>10</option>
              <option>20</option>
              <option>50</option>
              <option>100</option>
            </select>
          </div>

          <div class="position-relative" style="min-width:280px;">
            <input id="searchInput" type="search" class="form-control ps-5" placeholder="Search by name, email or phone…">
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>

          <button id="btnFilter" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="fa fa-sliders me-1"></i>Filter
          </button>

          <button id="btnReset" class="btn btn-light">
            <i class="fa fa-rotate-left me-1"></i>Reset
          </button>

          <button id="btnExportStudents" class="btn btn-outline-success">
            <i class="fa fa-file-csv me-1"></i>Export CSV
          </button>

          {{-- ✅ UPDATED: Import CSV button - now opens modal --}}
          <button id="btnImportStudents" class="btn btn-outline-primary" style="display:none;" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fa fa-file-import me-1"></i>Import CSV
          </button>

        </div>

        <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
          <div id="writeControls" style="display:none;" class="toolbar-buttons">
            <button type="button" class="btn btn-primary" id="btnAddStudent">
              <i class="fa fa-plus me-1"></i> Add Student
            </button>
          </div>
        </div>
      </div>

      {{-- Table --}}
      <div class="card table-wrap">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle">
              <thead>
                <tr>
                  <th style="width:82px;">Status</th>
                  <th style="width:74px;">Avatar</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  {{-- ✅ NEW --}}
                  <th>Department</th>
                  <th style="width:200px;">Role</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="usersTbody-active">
                <tr>
                  {{-- ✅ colspan updated --}}
                  <td colspan="8" class="text-center text-muted" style="padding:38px;">Loading…</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="empty-active" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-users mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No active users found for current filters.</div>
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
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle">
              <thead>
                <tr>
                  <th style="width:82px;">Status</th>
                  <th style="width:74px;">Avatar</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  {{-- ✅ NEW --}}
                  <th>Department</th>
                  <th style="width:200px;">Role</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="usersTbody-inactive">
                <tr>
                  {{-- ✅ colspan updated --}}
                  <td colspan="8" class="text-center text-muted" style="padding:38px;">Loading…</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="empty-inactive" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-user-slash mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No inactive users found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo-inactive">—</div>
            <nav><ul id="pager-inactive" class="pagination mb-0"></ul></nav>
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
        <h5 class="modal-title"><i class="fa fa-sliders me-2"></i>Filter Users</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Role</label>
            <select id="modal_role" class="form-select">
              <option value="student">Student</option>
            </select>
            <div class="form-text">This page shows only students.</div>
          </div>

          {{-- ✅ NEW: Department filter --}}
          <div class="col-12">
            <label class="form-label">Department</label>
            <select id="modal_department" class="form-select">
              <option value="">All Departments</option>
            </select>
            <div class="form-text">Loaded from <code>/api/departments</code></div>
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="modal_sort" class="form-select">
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="name">Name A-Z</option>
              <option value="-name">Name Z-A</option>
              <option value="email">Email A-Z</option>
              <option value="-email">Email Z-A</option>
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

{{-- ✅ NEW: Import CSV Modal (with Academic Details) --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-file-import me-2"></i>Import Students (CSV)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <div class="alert alert-info">
              <i class="fa fa-circle-info me-1"></i>
              <strong>Import CSV files with student basic details and academic information.</strong>
              <div class="small mt-1">All imported users will have the <code>student</code> role.</div>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Import Mode <span class="text-danger">*</span></label>
            <select id="importMode" class="form-select">
              <option value="create">Create Only (skip existing)</option>
              <option value="upsert">Create or Update (by email)</option>
            </select>
            <div class="form-text">
              <strong>Create Only:</strong> Skips rows where email already exists.<br>
              <strong>Create or Update:</strong> Updates existing students by email, creates new ones.
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">CSV File <span class="text-danger">*</span></label>
            <input type="file" id="importCsvFile" class="form-control" accept=".csv,text/csv">
            <div class="form-text">
              Maximum file size: 5MB. Required columns for basic import:
              <code>name, email, phone_number, department_id, status, password</code>
            </div>
          </div>

          <div class="col-12">
            <div class="card border">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fa fa-table me-1"></i> Download CSV Templates</h6>
              </div>
              <div class="card-body">
                <div class="row g-2">
                  <div class="col-md-6">
                    <button type="button" class="btn btn-outline-primary w-100" id="btnDownloadTemplate">
                      <i class="fa fa-download me-1"></i> Basic Template
                    </button>
                    <div class="small text-muted mt-1">Basic student fields only</div>
                  </div>
                  <div class="col-md-6">
                    <button type="button" class="btn btn-outline-success w-100" id="btnDownloadTemplateWithAcademic">
                      <i class="fa fa-download me-1"></i> With Academic Details
                    </button>
                    <div class="small text-muted mt-1">Includes academic fields (course, semester, etc.)</div>
                  </div>
                </div>

                {{-- CSV Field Guide --}}
                <div class="mt-3">
                  <h6 class="small fw-bold mb-2">CSV Field Guide:</h6>
                  <div class="row g-2">
                    <div class="col-md-6">
                      <div class="border rounded p-2 bg-light">
                        <h6 class="small fw-bold mb-1">Basic Fields:</h6>
                        <ul class="small mb-0" style="list-style:none;padding-left:0">
                          <li><code>name</code> - Full name (required)</li>
                          <li><code>email</code> - Email address (required, unique)</li>
                          <li><code>phone_number</code> - Primary phone</li>
                          <li><code>department_id</code> - Department ID</li>
                          <li><code>status</code> - active/inactive</li>
                          <li><code>password</code> - Password for new users</li>
                          <li><code>alternative_email</code> - Alternate email</li>
                          <li><code>alternative_phone_number</code> - Alternate phone</li>
                          <li><code>whatsapp_number</code> - WhatsApp number</li>
                          <li><code>address</code> - Full address</li>
                          <li><code>image</code> - Avatar image path/URL</li>
                        </ul>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="border rounded p-2 bg-light">
                        <h6 class="small fw-bold mb-1">Academic Fields:</h6>
                        <ul class="small mb-0" style="list-style:none;padding-left:0">
                          <li><code>course_id</code> - Course ID</li>
                          <li><code>semester_id</code> - Semester ID</li>
                          <li><code>section_id</code> - Section ID</li>
                          <li><code>academic_year</code> - Academic year</li>
                          <li><code>year</code> - Year (e.g., 2026)</li>
                          <li><code>acad_status</code> - active/inactive/passed-out</li>
                          <li><code>roll_no</code> - Roll number</li>
                          <li><code>registration_no</code> - Registration number</li>
                          <li><code>admission_no</code> - Admission number</li>
                          <li><code>admission_date</code> - YYYY-MM-DD</li>
                          <li><code>batch</code> - Batch year</li>
                          <li><code>session</code> - Session (e.g., 2025-2029)</li>
                          <li><code>attendance_percentage</code> - 0-100</li>
                          <li><code>metadata</code> - Optional JSON data</li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12">
            <div class="alert alert-warning small">
              <i class="fa fa-exclamation-triangle me-1"></i>
              <strong>Note:</strong> Academic details will only be imported if the student user is created/updated successfully.
              Ensure <code>department_id</code> and <code>course_id</code> exist in your system.
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="btnDoImport">
          <i class="fa fa-upload me-1"></i> Import CSV
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ✅ Student Wizard Modal (Step-up form: Basic -> Academic) --}}
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <form class="modal-content" id="userForm" novalidate>
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-1" id="userModalTitle">Add Student</h5>
          <div class="text-muted small" id="userModalSub">Step-up form: Basic Details → Academic Details</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="userUuid"/>
        <input type="hidden" id="editingUserId"/>

        <div class="wiz-steps mb-3">
          <div class="wiz-step active" id="wizStep1" role="button" tabindex="0">
            <div class="num">1</div>
            <div>
              <div class="lbl">Basic Details</div>
              <div class="sub">Profile, Department, Status</div>
            </div>
          </div>
          <div class="wiz-step" id="wizStep2" role="button" tabindex="0">
            <div class="num">2</div>
            <div>
              <div class="lbl">Academic Details</div>
              <div class="sub">Course, Sem, Section, Roll etc.</div>
            </div>
          </div>
        </div>

        <div class="wiz-hint mb-3" id="wizHint">
          <i class="fa fa-circle-info me-1"></i>
          Fill student basic details first. Then continue to add academic details (you can also add/update academic details later from the 3-dot menu).
        </div>

        {{-- STEP 1 --}}
        <div class="wiz-pane active" id="wizPane1">
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label">Full Name <span class="text-danger">*</span></label>
              <input class="form-control" id="userName" required maxlength="190" placeholder="John Doe">
              <div class="invalid-feedback">Full name is required.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="userEmail" required maxlength="255" placeholder="john.doe@example.com">
              <div class="invalid-feedback">Valid email is required.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input class="form-control" id="userPhone" maxlength="32" placeholder="+91 99999 99999">
            </div>

            <div class="col-md-6">
              <label class="form-label">Role <span class="text-danger">*</span></label>
              <select class="form-select" id="userRole" required>
                <option value="student">Student</option>
              </select>
              <div class="form-text">This page manages students only.</div>
            </div>

            <div class="col-md-6" style="display:none">
              <label class="form-label" for="userDepartment">Department </label>
              <select class="form-select" id="userDepartment" name="department_id">
                <option value="" selected disabled>Select Department</option>
              </select>
              <div class="invalid-feedback">Please select a department.</div>
              <div class="form-text">Loaded from <code>/api/departments</code></div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Status<span class="text-danger">*</span></label>
              <select class="form-select" id="userStatus">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Password <span class="text-danger" id="passwordRequired">*</span></label>
              <input type="password" class="form-control" id="userPassword" placeholder="••••••••" autocomplete="new-password">
              <div class="form-text" id="passwordHelp">Enter password for new student</div>
              <div class="invalid-feedback" id="passwordInvalid">Password is required for new students.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Confirm Password</label>
              <input type="password" class="form-control" id="userPasswordConfirmation" placeholder="••••••••" autocomplete="new-password">
              <div class="invalid-feedback" id="passwordConfirmInvalid">Passwords do not match.</div>
            </div>

            <div class="col-12" id="currentPasswordRow" style="display:none;">
              <label class="form-label">Current Password (required when changing your own password)</label>
              <input type="password" class="form-control" id="userCurrentPassword" placeholder="Current password" autocomplete="current-password">
            </div>

            <div class="col-md-6">
              <label class="form-label">Alt. Email</label>
              <input type="email" class="form-control" id="userAltEmail" maxlength="255" placeholder="alt@example.com">
            </div>

            <div class="col-md-6">
              <label class="form-label">Alt. Phone</label>
              <input class="form-control" id="userAltPhone" maxlength="32" placeholder="+91 88888 88888">
            </div>

            <div class="col-md-6">
              <label class="form-label">WhatsApp</label>
              <input class="form-control" id="userWhatsApp" maxlength="32" placeholder="+91 77777 77777">
            </div>

            <div class="col-md-12">
              <label class="form-label">Address</label>
              <textarea class="form-control" id="userAddress" rows="2" placeholder="Street, City, State, ZIP"></textarea>
            </div>

            <div class="col-md-12">
              <label class="form-label">Image URL / Path (optional)</label>
              <input type="text" class="form-control" id="userImage" placeholder="/storage/users/john.jpg or https://…">
              <div class="mt-2 d-flex align-items-center gap-2">
                <img id="imagePreview" alt="Preview"
                     style="width:48px;height:48px;border-radius:10px;object-fit:cover;display:none;border:1px solid var(--line-strong);">
                <small class="text-muted">Used for avatar display; upload via your media manager and paste the path here.</small>
              </div>
            </div>
          </div>
        </div>

        {{-- STEP 2 --}}
        <div class="wiz-pane" id="wizPane2">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div class="d-flex align-items-center gap-2">
              <span class="badge badge-soft-primary" id="acadModeBadge">
                <i class="fa fa-graduation-cap me-1"></i>Academic Details
              </span>
              <span class="text-muted small" id="acadForStudentLabel">—</span>
            </div>
            <div class="text-muted small">API: <code>/api/student-academic-details</code></div>
          </div>

          <div class="row g-3">
            <input type="hidden" id="acadRecordId">
            <input type="hidden" id="acadUserId">

            <div class="col-md-6">
              <label class="form-label">Department <span class="text-danger">*</span></label>
              <select id="acad_department_id" class="form-select" required>
                <option value="">Select Department</option>
              </select>
              <div class="form-text">Auto-filled from student department (you can change if needed).</div>
              <div class="invalid-feedback">Department is required.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Course <span class="text-danger">*</span></label>
              <select id="acad_course_id" class="form-select" required>
                <option value="">Select Course</option>
              </select>
              <div class="form-text">Loaded from <code>/api/courses</code> (fallback supported).</div>
              <div class="invalid-feedback">Course is required.</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Semester</label>
              <select id="acad_semester_id" class="form-select">
                <option value="">Select Semester (optional)</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Section</label>
              <select id="acad_section_id" class="form-select">
                <option value="">Select Section (optional)</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Academic Year</label>
              <input id="acad_academic_year" class="form-control" maxlength="20" placeholder="e.g. 2025-26">
            </div>

            <div class="col-md-4">
              <label class="form-label">Year</label>
              <input id="acad_year" type="number" class="form-control" min="1900" max="2200" placeholder="e.g. 2026">
            </div>

            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select id="acad_status" class="form-select">
                <option value="" disabled>Select an option</option>
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
                <option value="passed-out">Passed-out</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Roll No</label>
              <input id="acad_roll_no" class="form-control" maxlength="60" placeholder="Roll No">
            </div>

            <div class="col-md-4">
              <label class="form-label">Registration No</label>
              <input id="acad_registration_no" class="form-control" maxlength="80" placeholder="Registration No">
            </div>

            <div class="col-md-4">
              <label class="form-label">Admission No</label>
              <input id="acad_admission_no" class="form-control" maxlength="80" placeholder="Admission No">
            </div>

            <div class="col-md-4">
              <label class="form-label">Admission Date</label>
              <input id="acad_admission_date" type="date" class="form-control">
            </div>

            <div class="col-md-4">
              <label class="form-label">Batch</label>
              <input id="acad_batch" class="form-control" maxlength="40" placeholder="e.g. 2025">
            </div>

            <div class="col-md-4">
              <label class="form-label">Session</label>
              <input id="acad_session" class="form-control" maxlength="40" placeholder="e.g. 2025-2029">
            </div>

            <div class="col-md-4">
              <label class="form-label">Attendance (%)</label>
              <input id="acad_attendance_percentage"
                     type="number"
                     class="form-control"
                     min="0"
                     max="100"
                     step="0.01"
                     placeholder="e.g. 87.50">
              <div class="form-text">Optional (0 to 100)</div>
              <div class="invalid-feedback">Attendance must be between 0 and 100.</div>
            </div>

            <div class="col-12">
              <label class="form-label">Metadata (optional JSON)</label>
              <textarea id="acad_metadata" class="form-control" rows="2" placeholder='{"note":"optional"}'></textarea>
              <div class="form-text">Leave blank if you don't need it.</div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <div class="d-flex w-100 justify-content-between gap-2 flex-wrap">
          <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="btnWizardCancel">Cancel</button>
            <button type="button" class="btn btn-outline-secondary" id="btnWizardBack" style="display:none;">
              <i class="fa fa-arrow-left me-1"></i> Back
            </button>
          </div>

          <div class="d-flex align-items-center gap-2 ms-auto">
            <button type="button" class="btn btn-outline-primary" id="btnWizardSkipAcademic" style="display:none;">
              Skip Academic
            </button>
            <button type="button" class="btn btn-primary" id="btnWizardNext">
              Continue <i class="fa fa-arrow-right ms-1"></i>
            </button>
            <button type="button" class="btn btn-primary" id="btnWizardFinish" style="display:none;">
              <i class="fa fa-floppy-disk me-1"></i> Save & Finish
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080">
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// delegated dropdown toggle (safe)
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.dd-toggle');
  if (!btn) return;
  try {
    const inst = bootstrap.Dropdown.getOrCreateInstance(btn, {
      autoClose: btn.getAttribute('data-bs-auto-close') || 'outside',
      boundary: btn.getAttribute('data-bs-boundary') || 'viewport'
    });
    inst.toggle();
  } catch (ex) {
    console.error('Dropdown toggle error', ex);
  }
});

document.addEventListener('DOMContentLoaded', function () {
  const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
  if (!token) { window.location.href = '/'; return; }

  const globalLoading = document.getElementById('globalLoading');
  function showGlobalLoading(show) {
    if (!globalLoading) return;
    globalLoading.style.display = show ? 'flex' : 'none';
  }

  function authHeaders(extra = {}) {
    return Object.assign({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    }, extra);
  }

  function handleAuthStatus(res, forbiddenMessage) {
    if (res.status === 401) { window.location.href = '/'; return true; }
    if (res.status === 403) { throw new Error(forbiddenMessage || 'You are not allowed to perform this action.'); }
    return false;
  }

  function escapeHtml(str) {
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[s]));
  }

  function debounce(fn, ms = 350) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }

  function fixImageUrl(url) {
    if (!url) return null;
    if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('//')) return url;
    if (url.startsWith('/')) return url;
    return '/' + url.replace(/^\/+/, '');
  }

  // Toasts
  const toastOk = new bootstrap.Toast(document.getElementById('toastSuccess'));
  const toastErr = new bootstrap.Toast(document.getElementById('toastError'));
  const okTxt = document.getElementById('toastSuccessText');
  const errTxt = document.getElementById('toastErrorText');
  const ok = m => { okTxt.textContent = m || 'Done'; toastOk.show(); };
  const err = m => { errTxt.textContent = m || 'Something went wrong'; toastErr.show(); };

  // DOM refs (list)
  const tbodyActive = document.getElementById('usersTbody-active');
  const tbodyInactive = document.getElementById('usersTbody-inactive');
  const emptyActive = document.getElementById('empty-active');
  const emptyInactive = document.getElementById('empty-inactive');
  const pagerActive = document.getElementById('pager-active');
  const pagerInactive = document.getElementById('pager-inactive');
  const infoActive = document.getElementById('resultsInfo-active');
  const infoInactive = document.getElementById('resultsInfo-inactive');

  const perPageSel = document.getElementById('perPage');
  const searchInput = document.getElementById('searchInput');
  const btnApplyFilters = document.getElementById('btnApplyFilters');
  const btnReset = document.getElementById('btnReset');
  const modalRole = document.getElementById('modal_role');
  const modalSort = document.getElementById('modal_sort');
  // ✅ NEW
  const modalDept = document.getElementById('modal_department');

  const filterModalEl = document.getElementById('filterModal');
  const filterModal = new bootstrap.Modal(filterModalEl);

  const writeControls = document.getElementById('writeControls');
  const btnAddStudent = document.getElementById('btnAddStudent');
  const btnExportStudents = document.getElementById('btnExportStudents');

  // ✅ Import refs
  const btnImportStudents = document.getElementById('btnImportStudents');
  const importModalEl = document.getElementById('importModal');
  const importModal = importModalEl ? new bootstrap.Modal(importModalEl) : null;
  const importFile = document.getElementById('importCsvFile');
  const importMode = document.getElementById('importMode');
  const btnDoImport = document.getElementById('btnDoImport');
  const btnDownloadTemplate = document.getElementById('btnDownloadTemplate');
  const btnDownloadTemplateWithAcademic = document.getElementById('btnDownloadTemplateWithAcademic');

  // ✅ Import endpoints (tries multiple to FIX "template upload not inserting")
  const IMPORT_APIS = [
    '/api/users/import-csv'
  ];

  // Wizard modal refs
  const userModalEl = document.getElementById('userModal');
  const userModal = new bootstrap.Modal(userModalEl);
  const form = document.getElementById('userForm');
  const modalTitle = document.getElementById('userModalTitle');
  const modalSub = document.getElementById('userModalSub');

  const wizStep1 = document.getElementById('wizStep1');
  const wizStep2 = document.getElementById('wizStep2');
  const wizPane1 = document.getElementById('wizPane1');
  const wizPane2 = document.getElementById('wizPane2');
  const wizHint = document.getElementById('wizHint');

  const btnWizardBack = document.getElementById('btnWizardBack');
  const btnWizardNext = document.getElementById('btnWizardNext');
  const btnWizardFinish = document.getElementById('btnWizardFinish');
  const btnWizardSkipAcademic = document.getElementById('btnWizardSkipAcademic');

  // Basic inputs
  const uuidInput = document.getElementById('userUuid');
  // ✅ kept internally for system-required relations (not exposed in URL/query)
  const editingUserIdInput = document.getElementById('editingUserId');

  const nameInput = document.getElementById('userName');
  const emailInput = document.getElementById('userEmail');
  const phoneInput = document.getElementById('userPhone');
  const roleInput = document.getElementById('userRole');
  const deptInput = document.getElementById('userDepartment');
  const statusInput = document.getElementById('userStatus');

  const pwdReq = document.getElementById('passwordRequired');
  const pwdHelp = document.getElementById('passwordHelp');
  const currentPwdInput = document.getElementById('userCurrentPassword');
  const currentPwdRow = document.getElementById('currentPasswordRow');

  const altEmailInput = document.getElementById('userAltEmail');
  const altPhoneInput = document.getElementById('userAltPhone');
  const waInput = document.getElementById('userWhatsApp');
  const addrInput = document.getElementById('userAddress');
  const imageInput = document.getElementById('userImage');
  const imgPrev = document.getElementById('imagePreview');

  // Academic inputs
  const acadModeBadge = document.getElementById('acadModeBadge');
  const acadForStudentLabel = document.getElementById('acadForStudentLabel');
  const acadRecordId = document.getElementById('acadRecordId');
  const acadUserId = document.getElementById('acadUserId');

  const acadDept = document.getElementById('acad_department_id');
  const acadCourse = document.getElementById('acad_course_id');
  const acadSemester = document.getElementById('acad_semester_id');
  const acadSection = document.getElementById('acad_section_id');

  const acadAcademicYear = document.getElementById('acad_academic_year');
  const acadYear = document.getElementById('acad_year');
  const acadStatus = document.getElementById('acad_status');
  const acadRoll = document.getElementById('acad_roll_no');
  const acadReg = document.getElementById('acad_registration_no');
  const acadAdm = document.getElementById('acad_admission_no');
  const acadAdmDate = document.getElementById('acad_admission_date');
  const acadBatch = document.getElementById('acad_batch');
  const acadSession = document.getElementById('acad_session');
  const acadAttendance = document.getElementById('acad_attendance_percentage');

  // ✅ Remove metadata json usage everywhere (not needed)
  const acadMeta = document.getElementById('acad_metadata'); // will be hidden + ignored

  const ROLE_LABEL = { student: 'Student', students: 'Student' };
  const roleLabel = v => ROLE_LABEL[(v || '').toLowerCase()] || (v || '');

  // Actor & permissions
  const ACTOR = { id: null, role: '' };
  let canCreate = false;
  let canEdit = false;
  let canDelete = false;

  const state = {
    items: [],
    q: '',
    sort: '-created_at',
    perPage: 10,
    page: { active: 1, inactive: 1 },
    total: { active: 0, inactive: 0 },
    totalPages: { active: 1, inactive: 1 },
    // ✅ NEW: department filter (string id)
    deptFilter: '',
    departments: [],
    departmentsLoaded: false,
    courses: [],
    coursesLoaded: false,
    semestersByCourse: new Map(),
    sectionsBySemester: new Map()
  };

  const wizard = {
    step: 1,
    mode: 'create',        // create | edit | view
    academicOnly: false,   // opened from 3-dot "Academic details"
    user: { id: null, uuid: null, email: '' },
    academic: { id: null, mode: 'create' } // create | update
  };

  function computePermissions() {
    const r = (ACTOR.role || '').toLowerCase();
    const createDeleteRoles = ['admin', 'director', 'principal'];
    const writeRoles = ['admin', 'director', 'principal', 'hod'];

    canCreate = createDeleteRoles.includes(r);
    canDelete = createDeleteRoles.includes(r);
    canEdit = writeRoles.includes(r);

    if (writeControls) writeControls.style.display = canCreate ? 'flex' : 'none';

    // ✅ Import button visibility (same gating as create)
    if (btnImportStudents) btnImportStudents.style.display = canCreate ? '' : 'none';
  }

  async function fetchMe() {
    try {
      const res = await fetch('/api/users/me', { headers: authHeaders() });
      if (handleAuthStatus(res, 'You are not allowed to access your profile.')) return;

      const js = await res.json().catch(() => ({}));
      if (js && js.success && js.data) {
        ACTOR.id = js.data.id || null;
        ACTOR.role = (js.data.role || '').toLowerCase();
      } else {
        ACTOR.role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
      }
      computePermissions();
    } catch (e) {
      console.error('Failed to fetch /me', e);
    }
  }

  // Backdrop cleanup (stuck backdrop fix)
  function cleanupModalBackdrops() {
    if (!document.querySelector('.modal.show')) {
      document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('padding-right');
      document.body.style.removeProperty('overflow');
    }
  }
  document.addEventListener('hidden.bs.modal', () => setTimeout(cleanupModalBackdrops, 80));

  // ✅ Guide: red mark required + replace *_id -> *_uuid + remove metadata everywhere
  function markImportGuideRequired() {
    // ✅ Basic required always
    const reqBasic = new Set(['name', 'email', 'department_id', 'status']);

    // ✅ Password required ONLY in create mode
    const modeNow = (document.getElementById('importMode')?.value || 'create');
    if (modeNow === 'create') reqBasic.add('password');

    // ✅ Academic required list (course_uuid is required if academic is being used)
    const reqAcad = new Set(['course_uuid']);

    const guide = document.querySelector('#importModal .card-body');
    if (!guide) return;

    const lis = guide.querySelectorAll('li');
    lis.forEach(li => {
      const code = li.querySelector('code');
      if (!code) return;

      let key = (code.textContent || '').trim().toLowerCase();

      // ✅ remove metadata item from guide
      if (key === 'metadata') {
        li.remove();
        return;
      }

      // ✅ Replace *_id => *_uuid (academic import security fix)
      if (key === 'course_id') {
        li.innerHTML = `<code>course_uuid</code> - Course UUID`;
        key = 'course_uuid';
      }
      if (key === 'semester_id') {
        li.innerHTML = `<code>semester_uuid</code> - Semester UUID`;
        key = 'semester_uuid';
      }
      if (key === 'section_id') {
        li.innerHTML = `<code>section_uuid</code> - Section UUID`;
        key = 'section_uuid';
      }

      const box = li.closest('.col-md-6');
      const boxTitle = box?.querySelector('h6')?.textContent?.toLowerCase() || '';
      const inBasicBox = boxTitle.includes('basic');
      const inAcadBox = boxTitle.includes('academic');

      // ✅ add red star
      if ((inBasicBox && reqBasic.has(key)) || (inAcadBox && reqAcad.has(key))) {
        if (!li.querySelector('.req-star')) {
          const star = document.createElement('span');
          star.className = 'req-star text-danger fw-bold ms-1';
          star.textContent = '*';
          li.appendChild(star);
        }
      }
    });

    // ✅ Update NOTE text: course_uuid (backend will map uuid -> id)
    const warn = document.querySelector('#importModal .alert.alert-warning');
    if (warn && warn.innerHTML) {
      warn.innerHTML = warn.innerHTML
        .replace(/course_id/gi, 'course_uuid')
        .replace(/Course ID/gi, 'Course UUID');
    }

    // ✅ Hide metadata field on Academic step UI (not needed)
    if (acadMeta) {
      const wrap = acadMeta.closest('.col-12') || acadMeta.parentElement;
      if (wrap) wrap.style.display = 'none';
      acadMeta.value = '';
    }
  }

  // Run once now, and also when import modal opens / mode changes (password required star update)
  markImportGuideRequired();
  importModalEl?.addEventListener('shown.bs.modal', markImportGuideRequired);
  importMode?.addEventListener('change', markImportGuideRequired);

  // ==========================
  // ✅ Import helpers (FIX: template re-upload not inserting)
  // ==========================

  function downloadCsv(filename, csvText) {
    const blob = new Blob([csvText], { type: 'text/csv;charset=UTF-8' });
    const a = document.createElement('a');
    const u = URL.createObjectURL(blob);
    a.href = u;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(u);
  }

  // Lightweight CSV parser for header validation (supports quotes)
  function parseCsv(text) {
    const out = [];
    let row = [];
    let cur = '';
    let i = 0;
    let inQuotes = false;

    // normalize line endings
    text = (text || '').replace(/\r\n/g, '\n').replace(/\r/g, '\n');

    while (i < text.length) {
      const ch = text[i];

      if (ch === '"') {
        // escaped quote
        if (inQuotes && text[i + 1] === '"') {
          cur += '"';
          i += 2;
          continue;
        }
        inQuotes = !inQuotes;
        i++;
        continue;
      }

      if (!inQuotes && ch === ',') {
        row.push(cur);
        cur = '';
        i++;
        continue;
      }

      if (!inQuotes && ch === '\n') {
        row.push(cur);
        out.push(row);
        row = [];
        cur = '';
        i++;
        continue;
      }

      cur += ch;
      i++;
    }

    // last cell
    if (cur.length || row.length) {
      row.push(cur);
      out.push(row);
    }

    return out;
  }

  function normalizeHeader(h) {
    return (h || '')
      .toString()
      .replace(/^\uFEFF/, '') // ✅ remove BOM
      .trim()
      .toLowerCase()
      .replace(/\s+/g, '_')
      .replace(/[^\w_]/g, '');
  }

  async function validateImportFile(file, mode) {
    const txt = await file.text();
    const rows = parseCsv(txt);
    const headerRow = rows[0] || [];
    const headers = headerRow.map(normalizeHeader).filter(Boolean);

    if (!headers.length) {
      throw new Error('CSV header row is missing or invalid.');
    }

    // Required headers (create requires password)
    const requiredAlways = ['name', 'email', 'department_id', 'status'];
    const requiredCreateOnly = ['password'];

    // ✅ Detect "academic template" by presence of any academic columns
    const academicSignals = [
      'course_uuid','semester_uuid','section_uuid',
      'academic_year','year','acad_status','roll_no','registration_no',
      'admission_no','admission_date','batch','session','attendance_percentage'
    ];
    const hasAcademic = headers.some(h => academicSignals.includes(h));

    const missing = [];
    requiredAlways.forEach(k => { if (!headers.includes(k)) missing.push(k); });
    if ((mode || 'create') === 'create') {
      requiredCreateOnly.forEach(k => { if (!headers.includes(k)) missing.push(k); });
    }

    // ✅ If academic fields are present, course_uuid becomes required
    if (hasAcademic && !headers.includes('course_uuid')) {
      missing.push('course_uuid');
    }

    if (missing.length) {
      throw new Error(`Missing required column(s): ${missing.join(', ')}`);
    }

    // at least 1 data row
    const dataRows = rows.slice(1).filter(r => r.some(c => (c || '').trim() !== ''));
    if (!dataRows.length) {
      throw new Error('CSV has no data rows. Please add at least one student row.');
    }

    return { headers, dataRowsCount: dataRows.length, hasAcademic };
  }

  function templateCsvBasic() {
    const headers = [
      'name','email','phone_number','department_id','status','password',
      'alternative_email','alternative_phone_number','whatsapp_number','address','image'
    ];
    const sample = [
      'John Doe','john.doe@example.com','+91 99999 99999','1','active','Pass@1234',
      'alt@example.com','+91 88888 88888','+91 77777 77777','Kolkata, WB','/storage/users/john.jpg'
    ];
    return headers.join(',') + '\r\n' + sample.map(v => `"${String(v).replace(/"/g,'""')}"`).join(',');
  }

  function templateCsvWithAcademic() {
    const headers = [
      // user basic fields
      'name','email','phone_number','department_id','status','password',
      'alternative_email','alternative_phone_number','whatsapp_number','address','image',
      // academic fields (✅ UUID columns + metadata removed)
      'course_uuid','semester_uuid','section_uuid','academic_year','year','acad_status',
      'roll_no','registration_no','admission_no','admission_date','batch','session','attendance_percentage'
    ];

    // ✅ sample UUIDs (replace with real UUIDs from your DB)
    const sample = [
      // user basic
      'John Doe','john.doe@example.com','+91 99999 99999','1','active','Pass@1234',
      'alt@example.com','+91 88888 88888','+91 77777 77777','Kolkata, WB','/storage/users/john.jpg',
      // academic (uuid strings)
      '11111111-1111-1111-1111-111111111111',
      '22222222-2222-2222-2222-222222222222',
      '33333333-3333-3333-3333-333333333333',
      '2025-26','2026','active','BCA-12','REG-123','ADM-456','2025-08-01','2025','2025-2029','87.5'
    ];

    return headers.join(',') + '\r\n' + sample.map(v => `"${String(v).replace(/"/g,'""')}"`).join(',');
  }

  async function runImportCsv(file, mode) {
    if (!file) throw new Error('Please choose a CSV file.');

    // ✅ Validate file structure FIRST (prevents "upload same template but not inserting" confusion)
    const v = await validateImportFile(file, mode);

    const fd = new FormData();
    // ✅ Send multiple common keys to match backend expectations
    fd.append('file', file);
    fd.append('csv', file);
    fd.append('import_file', file);
    fd.append('csv_file', file);

    fd.append('role', 'student');
    fd.append('mode', mode || 'create');
    fd.append('import_mode', mode || 'create'); // backend alternate
    fd.append('upsert', (mode === 'upsert') ? '1' : '0'); // backend alternate

    // ✅ helpful flags for backend (ignored if not used)
    fd.append('has_academic', v.hasAcademic ? '1' : '0');
    fd.append('academic_uuid_mode', v.hasAcademic ? '1' : '0');

    let lastErr = null;

    for (const api of IMPORT_APIS) {
      try {
        const res = await fetch(api, {
          method: 'POST',
          headers: authHeaders(), // DO NOT set Content-Type for FormData
          body: fd
        });

        if (res.status === 404) {
          lastErr = new Error(`Import route not found: ${api}`);
          continue;
        }

        if (res.status === 405) {
          const allow = res.headers.get('Allow') || res.headers.get('allow') || '';
          lastErr = new Error(`Import route exists (${api}) but POST not allowed. Allowed: ${allow || 'unknown'}`);
          continue;
        }

        if (res.status >= 500) {
          // ✅ IMPORTANT: show backend crash message instead of trying other routes
          const txt = await res.text().catch(() => '');
          throw new Error(txt || `Server error (500) on ${api}`);
        }

        if (handleAuthStatus(res, 'You are not allowed to import students.')) return null;

        const ct = (res.headers.get('content-type') || '').toLowerCase();

        if (ct.includes('application/json')) {
          const js = await res.json().catch(() => null);
          if (!res.ok || js?.success === false) {
            let msg = js?.error || js?.message || 'Import failed';
            if (js?.errors) {
              const k = Object.keys(js.errors)[0];
              if (k && js.errors[k] && js.errors[k][0]) msg = js.errors[k][0];
            }
            throw new Error(msg);
          }
          // success
          return js;
        } else {
          // non-json fallback
          const txt = await res.text().catch(() => '');
          if (!res.ok) throw new Error(txt || 'Import failed');
          return { success: true, message: txt || 'Imported' };
        }
      } catch (e) {
        lastErr = e;
        // try next endpoint
      }
    }

    throw lastErr || new Error('Import failed');
  }

  // ✅ Download template buttons
  btnDownloadTemplate?.addEventListener('click', () => {
    downloadCsv('students_basic_template.csv', templateCsvBasic());
    ok('Basic template downloaded');
  });

  btnDownloadTemplateWithAcademic?.addEventListener('click', () => {
    downloadCsv('students_with_academic_template.csv', templateCsvWithAcademic());
    ok('Template with academic details downloaded');
  });

  // ✅ Import CSV
  btnDoImport?.addEventListener('click', async () => {
    if (!canCreate) return;

    try {
      const file = importFile?.files?.[0] || null;
      if (!file) { err('Please choose a CSV file.'); return; }

      const isCsv = (file.type || '').includes('csv') || (file.name || '').toLowerCase().endsWith('.csv');
      if (!isCsv) { err('Please select a CSV file.'); return; }

      // optional size gate (5MB)
      if (file.size > 5 * 1024 * 1024) { err('File too large. Max 5MB.'); return; }

      const mode = importMode?.value || 'create';

      showGlobalLoading(true);
      const js = await runImportCsv(file, mode);

      const msg =
        js?.message ||
        (js?.data?.message) ||
        'Import completed';

      ok(msg);

      // Optional: show failed rows if provided by backend
      const failed = js?.data?.failed || js?.failed || null;
      if (Array.isArray(failed) && failed.length) {
        const preview = failed.slice(0, 8).map((x, i) => {
          const row = x?.row ?? (i + 1);
          const reason = x?.error || x?.message || 'Failed';
          return `Row ${row}: ${reason}`;
        }).join('\n');

        await Swal.fire({
          title: 'Imported with some issues',
          icon: 'warning',
          text: preview + (failed.length > 8 ? `\n… and ${failed.length - 8} more` : ''),
        });
      }

      try { importModal?.hide(); } catch (_) {}
      await loadUsers(false);
    } catch (ex) {
      err(ex.message);
    } finally {
      showGlobalLoading(false);
    }
  });

  // ==========================
  // Departments
  // ==========================
  function deptName(d) {
    return d?.name || d?.title || d?.department_name || d?.dept_name || d?.slug || (d?.id ? `Department #${d.id}` : 'Department');
  }

  // ✅ NEW: normalize department id from user row
  function rowDeptId(u) {
    const v =
      u?.department_id ??
      u?.departmentId ??
      u?.dept_id ??
      u?.department?.id ??
      u?.department?.department_id ??
      u?.department?.dept_id ??
      null;

    const n = parseInt(v, 10);
    return Number.isFinite(n) && n > 0 ? n : null;
  }

  // ✅ NEW: department label for table
  function rowDeptLabel(u) {
    const direct =
      (typeof u?.department_name === 'string' && u.department_name.trim()) ? u.department_name.trim() :
      (typeof u?.department?.name === 'string' && u.department.name.trim()) ? u.department.name.trim() :
      (typeof u?.department?.title === 'string' && u.department.title.trim()) ? u.department.title.trim() :
      '';

    if (direct) return direct;

    const id = rowDeptId(u);
    if (id && state.departmentsLoaded && Array.isArray(state.departments)) {
      const d = state.departments.find(x => {
        const did = parseInt((x?.id ?? x?.value ?? x?.department_id ?? x?.dept_id ?? ''), 10);
        return Number.isFinite(did) && did === id;
      });
      if (d) return deptName(d);
    }

    return id ? `Department #${id}` : '—';
  }

  function renderDepartmentsOptions() {
    // Basic dept
    if (deptInput) {
      const current = (deptInput.value || '').toString();
      let html = `<option value="" selected disabled>Select Department</option>`;
      (state.departments || []).forEach(d => {
        const id = d?.id ?? d?.value ?? d?.department_id;
        if (id === undefined || id === null || id === '') return;
        html += `<option value="${escapeHtml(String(id))}">${escapeHtml(deptName(d))}</option>`;
      });
      deptInput.innerHTML = html;
      if (current && current !== 'null') deptInput.value = current;
    }

    // Academic dept
    if (acadDept) {
      const curA = (acadDept.value || '').toString();
      let aHtml = `<option value="">Select Department</option>`;
      (state.departments || []).forEach(d => {
        const id = d?.id ?? d?.value ?? d?.department_id;
        if (id === undefined || id === null || id === '') return;
        aHtml += `<option value="${escapeHtml(String(id))}">${escapeHtml(deptName(d))}</option>`;
      });
      acadDept.innerHTML = aHtml;
      if (curA && curA !== 'null') acadDept.value = curA;
    }

    // ✅ NEW: Filter modal dept
    if (modalDept) {
      const curF = (modalDept.value || '').toString();
      let fHtml = `<option value="">All Departments</option>`;
      (state.departments || []).forEach(d => {
        const id = d?.id ?? d?.value ?? d?.department_id;
        if (id === undefined || id === null || id === '') return;
        fHtml += `<option value="${escapeHtml(String(id))}">${escapeHtml(deptName(d))}</option>`;
      });
      modalDept.innerHTML = fHtml;
      // keep selection if any
      if (curF && curF !== 'null') modalDept.value = curF;
    }
  }

  async function loadDepartments(showOverlay = false) {
    try {
      if (showOverlay) showGlobalLoading(true);

      const res = await fetch('/api/departments', { headers: authHeaders() });
      if (handleAuthStatus(res, 'You are not allowed to load departments.')) return;

      const js = await res.json().catch(() => ({}));
      if (!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load departments');

      let arr = [];
      if (Array.isArray(js.data)) arr = js.data;
      else if (Array.isArray(js?.data?.data)) arr = js.data.data;
      else if (Array.isArray(js.departments)) arr = js.departments;
      else if (Array.isArray(js)) arr = js;

      state.departments = arr;
      state.departmentsLoaded = true;
      renderDepartmentsOptions();
    } catch (e) {
      console.error('Failed to load departments', e);
      state.departments = [];
      state.departmentsLoaded = false;
      renderDepartmentsOptions();
    } finally {
      if (showOverlay) showGlobalLoading(false);
    }
  }

  // ==========================
  // Courses / Semesters / Sections (fallback supported)
  // ==========================
  async function fetchListFromAny(urls = []) {
    for (const url of urls) {
      try {
        const res = await fetch(url, { headers: authHeaders() });
        if (handleAuthStatus(res, 'You are not allowed.')) return [];
        const js = await res.json().catch(() => ({}));
        if (!res.ok || js.success === false) continue;

        let arr = [];
        if (Array.isArray(js.data)) arr = js.data;
        else if (Array.isArray(js?.data?.data)) arr = js.data.data;
        else if (Array.isArray(js.items)) arr = js.items;
        else if (Array.isArray(js)) arr = js;

        if (Array.isArray(arr)) return arr;
      } catch (_) {}
    }
    return [];
  }

  function courseLabel(x){ return x?.title || x?.name || x?.course_title || x?.slug || (x?.id ? `Course #${x.id}` : 'Course'); }
  function semLabel(x){ return x?.title || x?.name || x?.semester_title || x?.slug || (x?.id ? `Semester #${x.id}` : 'Semester'); }
  function secLabel(x){ return x?.title || x?.name || x?.section_title || x?.slug || (x?.id ? `Section #${x.id}` : 'Section'); }

  async function loadCourses() {
    if (state.coursesLoaded) return;
    const arr = await fetchListFromAny(['/api/courses','/api/course','/api/public/courses']);
    state.courses = arr || [];
    state.coursesLoaded = true;

    if (acadCourse) {
      const cur = acadCourse.value || '';
      let html = `<option value="">Select Course</option>`;
      (state.courses || []).forEach(c => {
        const id = c?.id ?? c?.value ?? c?.course_id;
        if (id === undefined || id === null || id === '') return;
        html += `<option value="${escapeHtml(String(id))}">${escapeHtml(courseLabel(c))}</option>`;
      });
      acadCourse.innerHTML = html;
      if (cur && cur !== 'null') acadCourse.value = cur;
    }
  }

  function renderSemesters(sems) {
    if (!acadSemester) return;
    const cur = acadSemester.value || '';
    let html = `<option value="">Select Semester (optional)</option>`;
    (sems || []).forEach(s => {
      const id = s?.id ?? s?.value ?? s?.semester_id;
      if (id === undefined || id === null || id === '') return;
      html += `<option value="${escapeHtml(String(id))}">${escapeHtml(semLabel(s))}</option>`;
    });
    acadSemester.innerHTML = html;
    if (cur && cur !== 'null') acadSemester.value = cur;
  }

  function renderSections(secs) {
    if (!acadSection) return;
    const cur = acadSection.value || '';
    let html = `<option value="">Select Section (optional)</option>`;
    (secs || []).forEach(s => {
      const id = s?.id ?? s?.value ?? s?.section_id;
      if (id === undefined || id === null || id === '') return;
      html += `<option value="${escapeHtml(String(id))}">${escapeHtml(secLabel(s))}</option>`;
    });
    acadSection.innerHTML = html;
    if (cur && cur !== 'null') acadSection.value = cur;
  }

  async function loadSemestersByCourse(courseId) {
    if (!courseId) { renderSemesters([]); renderSections([]); return []; }
    const key = String(courseId);
    if (state.semestersByCourse.has(key)) {
      const cached = state.semestersByCourse.get(key) || [];
      renderSemesters(cached);
      return cached;
    }
    const arr = await fetchListFromAny([
      `/api/courses/${encodeURIComponent(courseId)}/semesters`,
    ]);
    state.semestersByCourse.set(key, arr || []);
    renderSemesters(arr || []);
    return arr || [];
  }

  async function loadSectionsBySemester(semesterId) {
    if (!semesterId) { renderSections([]); return []; }
    const key = String(semesterId);
    if (state.sectionsBySemester.has(key)) {
      const cached = state.sectionsBySemester.get(key) || [];
      renderSections(cached);
      return cached;
    }
    const arr = await fetchListFromAny([
      `/api/course-semester-sections?semester_id=${encodeURIComponent(semesterId)}`,
      `/api/course_semester_sections?semester_id=${encodeURIComponent(semesterId)}`,
      `/api/course-semesters/${encodeURIComponent(semesterId)}/sections`,
      `/api/course_semesters/${encodeURIComponent(semesterId)}/sections`,
    ]);
    state.sectionsBySemester.set(key, arr || []);
    renderSections(arr || []);
    return arr || [];
  }

  acadCourse?.addEventListener('change', async () => {
    try {
      showGlobalLoading(true);
      await loadSemestersByCourse(acadCourse.value || '');
      renderSections([]);
    } finally {
      showGlobalLoading(false);
    }
  });
  acadSemester?.addEventListener('change', async () => {
    try {
      showGlobalLoading(true);
      await loadSectionsBySemester(acadSemester.value || '');
    } finally {
      showGlobalLoading(false);
    }
  });

  // ==========================
  // Users list
  // ==========================
  async function loadUsers(showOverlay = true) {
    try {
      if (showOverlay) showGlobalLoading(true);

      const params = new URLSearchParams();
      if (state.q) params.set('q', state.q);
      params.set('role', 'student');

      const url = '/api/users' + (params.toString() ? ('?' + params.toString()) : '');
      const res = await fetch(url, { headers: authHeaders() });
      if (handleAuthStatus(res, 'You are not allowed to view users.')) return;

      const js = await res.json().catch(() => ({}));
      if (!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load users');

      const all = Array.isArray(js.data) ? js.data : [];
      state.items = all.filter(u => {
        const r = (u?.role || '').toLowerCase();
        return r === 'student' || r === 'students';
      });

      state.page.active = 1;
      state.page.inactive = 1;
      recomputeAndRender();
    } catch (e) {
      err(e.message);
      console.error(e);
    } finally {
      if (showOverlay) showGlobalLoading(false);
    }
  }

  function sortUsers(arr) {
    const sortKey = state.sort.startsWith('-') ? state.sort.slice(1) : state.sort;
    const dir = state.sort.startsWith('-') ? -1 : 1;
    return arr.slice().sort((a, b) => {
      let av = a[sortKey], bv = b[sortKey];
      if (sortKey === 'name' || sortKey === 'email') {
        av = (av || '').toString().toLowerCase();
        bv = (bv || '').toString().toLowerCase();
      } else {
        av = (av || '').toString();
        bv = (bv || '').toString();
      }
      if (av === bv) return 0;
      return av > bv ? dir : -dir;
    });
  }

  function recomputeAndRender() {
    const lists = { active: [], inactive: [] };
    const depFilter = (state.deptFilter || '').toString().trim();

    state.items.forEach(u => {
      const rr = (u.role || '').toLowerCase();
      if (!(rr === 'student' || rr === 'students')) return;

      // ✅ Department filter apply (both tabs)
      if (depFilter) {
        const did = rowDeptId(u);
        if (!did || String(did) !== depFilter) return;
      }

      const st = (u.status || 'active').toLowerCase();
      if (st === 'inactive') lists.inactive.push(u);
      else lists.active.push(u);
    });

    const activeSorted = sortUsers(lists.active);
    const inactiveSorted = sortUsers(lists.inactive);

    ['active','inactive'].forEach(tab => {
      const full = tab === 'active' ? activeSorted : inactiveSorted;
      const total = full.length;
      const per = state.perPage || 10;
      const pages = Math.max(1, Math.ceil(total / per));

      state.total[tab] = total;
      state.totalPages[tab] = pages;
      if (state.page[tab] > pages) state.page[tab] = pages;

      const start = (state.page[tab] - 1) * per;
      const rows = full.slice(start, start + per);

      renderTable(tab, rows);
      renderPager(tab);
      renderInfo(tab);

      const emptyEl = tab === 'active' ? emptyActive : emptyInactive;
      if (emptyEl) emptyEl.style.display = total === 0 ? '' : 'none';
    });
  }

  function renderInfo(tab) {
    const infoEl = tab === 'active' ? infoActive : infoInactive;
    if (!infoEl) return;
    infoEl.textContent = '—'; // requested: no counts
  }

  function renderTable(tab, rows) {
    const tbody = tab === 'active' ? tbodyActive : tbodyInactive;
    if (!tbody) return;

    if (!rows.length) { tbody.innerHTML = ''; return; }

    tbody.innerHTML = rows.map(row => {
      const role = (row.role || '').toLowerCase();
      const active = (row.status || 'active').toLowerCase() === 'active';

      const imgUrl = fixImageUrl(row.image);
      const avatarImg = imgUrl
        ? `<img src="${escapeHtml(imgUrl)}" alt="avatar"
                 style="width:40px;height:40px;border-radius:10px;object-fit:cover;border:1px solid var(--line-strong);"
                 loading="lazy"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">`
        : '';
      const avatarFallback =
        `<div style="width:40px;height:40px;border-radius:10px;border:1px solid var(--line-strong);
                     display:${imgUrl ? 'none' : 'flex'};align-items:center;justify-content:center;color:#9aa3b2;">—</div>`;

      const statusCell = canEdit
        ? `<div class="form-check form-switch m-0">
             <input class="form-check-input js-toggle" type="checkbox" ${active ? 'checked' : ''} title="Toggle Active">
           </div>`
        : `<span class="badge ${active ? 'badge-soft-success' : 'badge-soft-danger'}">${active ? 'Active' : 'Inactive'}</span>`;

      let actionHtml = `
        <div class="dropdown text-end" data-bs-display="static">
          <button type="button" class="btn btn-light btn-sm dd-toggle"
                  data-bs-toggle="dropdown" data-bs-auto-close="outside"
                  data-bs-boundary="viewport" aria-expanded="false" title="Actions">
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <button type="button" class="dropdown-item" data-action="profile">
                <i class="fa fa-user"></i> Profile
              </button>
            </li>
            <li>
              <button type="button" class="dropdown-item" data-action="assign_privilege">
                <i class="fa fa-key"></i> Assign Privilege
              </button>
            </li>
            <li>
              <button type="button" class="dropdown-item" data-action="acad">
                <i class="fa fa-graduation-cap"></i> Add/Update Academic Details
              </button>
            </li>
            <li>
              <button type="button" class="dropdown-item" data-action="view">
                <i class="fa fa-eye"></i> View
              </button>
            </li>`;

      if (canEdit) {
        actionHtml += `
            <li>
              <button type="button" class="dropdown-item" data-action="edit">
                <i class="fa fa-pen-to-square"></i> Edit
              </button>
            </li>`;
      }
      if (canDelete) {
        actionHtml += `
            <li><hr class="dropdown-divider"></li>
            <li>
              <button type="button" class="dropdown-item text-danger" data-action="delete">
                <i class="fa fa-trash"></i> Delete
              </button>
            </li>`;
      }
      actionHtml += `</ul></div>`;

      // ✅ Security change: DO NOT expose numeric IDs in dataset
      const deptLabel = rowDeptLabel(row);
      const deptCell = (deptLabel === '—')
        ? `<span class="text-muted">—</span>`
        : escapeHtml(deptLabel);

      return `
        <tr data-uuid="${escapeHtml(row.uuid)}"
            data-email="${escapeHtml(row.email || '')}">
          <td>${statusCell}</td>
          <td style="position:relative">${avatarImg}${avatarFallback}</td>
          <td class="fw-semibold">${escapeHtml(row.name || '')}</td>
          <td>${row.email ? `<a href="mailto:${escapeHtml(row.email)}">${escapeHtml(row.email)}</a>` : '<span class="text-muted">—</span>'}</td>
          <td>${row.phone_number ? escapeHtml(row.phone_number) : '<span class="text-muted">—</span>'}</td>
          {{-- ✅ NEW: Department column --}}
          <td>${deptCell}</td>
          <td>
            <span class="badge badge-soft-primary">
              <i class="fa fa-user-shield me-1"></i>${escapeHtml(roleLabel(role || 'student'))}
            </span>
          </td>
          <td class="text-end">${actionHtml}</td>
        </tr>`;
    }).join('');
  }

  function renderPager(tab) {
    const pager = tab === 'active' ? pagerActive : pagerInactive;
    if (!pager) return;

    const page = state.page[tab];
    const totalPages = state.totalPages[tab];

    const item = (p, label, dis = false, act = false) => {
      if (dis) return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
      if (act) return `<li class="page-item active"><span class="page-link">${label}</span></li>`;
      return `<li class="page-item"><a class="page-link" href="#" data-page="${p}" data-tab="${tab}">${label}</a></li>`;
    };

    let html = '';
    html += item(Math.max(1, page - 1), 'Previous', page <= 1);
    const st = Math.max(1, page - 2);
    const en = Math.min(totalPages, page + 2);
    for (let p = st; p <= en; p++) html += item(p, p, false, p === page);
    html += item(Math.min(totalPages, page + 1), 'Next', page >= totalPages);

    pager.innerHTML = html;
  }

  document.addEventListener('click', e => {
    const a = e.target.closest('a.page-link[data-page]');
    if (!a) return;
    e.preventDefault();
    const p = parseInt(a.dataset.page, 10);
    const tab = a.dataset.tab;
    if (!tab || Number.isNaN(p)) return;
    if (p === state.page[tab]) return;
    state.page[tab] = p;
    recomputeAndRender();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // Search (server-side q)
  const onSearch = debounce(() => {
    state.q = (searchInput.value || '').trim();
    state.page.active = 1;
    state.page.inactive = 1;
    loadUsers();
  }, 320);
  searchInput.addEventListener('input', onSearch);

  // Per page
  perPageSel.addEventListener('change', () => {
    state.perPage = parseInt(perPageSel.value, 10) || 10;
    state.page.active = 1;
    state.page.inactive = 1;
    recomputeAndRender();
  });

  // Filter modal show -> sync
  filterModalEl.addEventListener('show.bs.modal', () => {
    modalRole.value = 'student';
    modalSort.value = state.sort || '-created_at';
    // ✅ NEW
    if (modalDept) modalDept.value = state.deptFilter || '';
  });

  btnApplyFilters.addEventListener('click', () => {
    state.sort = modalSort.value || '-created_at';
    // ✅ NEW
    state.deptFilter = (modalDept?.value || '').toString();

    state.page.active = 1;
    state.page.inactive = 1;
    filterModal.hide();
    loadUsers();
  });

  btnReset.addEventListener('click', () => {
    state.q = '';
    state.sort = '-created_at';
    state.perPage = 10;
    state.page.active = 1;
    state.page.inactive = 1;
    // ✅ NEW
    state.deptFilter = '';

    searchInput.value = '';
    perPageSel.value = '10';
    modalRole.value = 'student';
    modalSort.value = '-created_at';
    if (modalDept) modalDept.value = '';

    loadUsers();
  });

  // Export Students CSV
  btnExportStudents?.addEventListener('click', async () => {
    try {
      showGlobalLoading(true);
      const url = '/api/users/export-csv?role=student';
      const res = await fetch(url, { headers: authHeaders() });
      if (handleAuthStatus(res, 'You are not allowed to export students.')) return;

      if (!res.ok) {
        const txt = await res.text().catch(() => '');
        throw new Error(txt || 'Export failed');
      }

      const blob = await res.blob();
      const dispo = res.headers.get('Content-Disposition') || '';
      const match = dispo.match(/filename="([^"]+)"/i);
      const filename = match?.[1] || ('students_export_' + new Date().toISOString().slice(0,19).replace(/[:T]/g,'-') + '.csv');

      const a = document.createElement('a');
      const u = window.URL.createObjectURL(blob);
      a.href = u;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(u);

      ok('CSV exported');
    } catch (ex) {
      err(ex.message);
    } finally {
      showGlobalLoading(false);
    }
  });

  // Toggle active/inactive
  document.addEventListener('change', async (e) => {
    const sw = e.target.closest('.js-toggle');
    if (!sw) return;
    if (!canEdit) { sw.checked = !sw.checked; return; }

    const tr = sw.closest('tr');
    const uuid = tr?.dataset?.uuid;
    if (!uuid) return;

    const willActive = sw.checked;
    const conf = await Swal.fire({
      title: 'Confirm',
      text: willActive ? 'Activate this student?' : 'Deactivate this student?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes'
    });
    if (!conf.isConfirmed) { sw.checked = !willActive; return; }

    showGlobalLoading(true);
    try {
      const res = await fetch(`/api/users/${encodeURIComponent(uuid)}`, {
        method: 'PATCH',
        headers: { ...authHeaders({ 'Content-Type': 'application/json' }) },
        body: JSON.stringify({ status: willActive ? 'active' : 'inactive' })
      });
      if (handleAuthStatus(res, 'You are not allowed to update status.')) return;

      const js = await res.json().catch(() => ({}));
      if (!res.ok || js.success === false) throw new Error(js.error || js.message || 'Status update failed');

      ok('Status updated');
      await loadUsers(false);
    } catch (ex) {
      err(ex.message);
      sw.checked = !willActive;
    } finally {
      showGlobalLoading(false);
    }
  });

  // ==========================
  // Wizard helpers
  // ==========================
  function clearInvalids(scopeEl) {
    if (!scopeEl) return;
    scopeEl.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
  }

  function setDisabledAll(scopeEl, disabled) {
    if (!scopeEl) return;
    scopeEl.querySelectorAll('input,select,textarea,button').forEach(el => {
      const id = el.id || '';
      if (id === 'btnWizardCancel' || id === 'btnWizardBack' || id === 'btnWizardNext' || id === 'btnWizardFinish' || id === 'btnWizardSkipAcademic') return;
      if (el.tagName === 'BUTTON') return;
      if (disabled) {
        if (el.tagName === 'SELECT') el.disabled = true;
        else el.readOnly = true;
      } else {
        if (el.tagName === 'SELECT') el.disabled = false;
        else el.readOnly = false;
      }
    });
  }

  function setStepActive(step) {
    wizard.step = step;

    wizStep1.classList.toggle('active', step === 1);
    wizStep2.classList.toggle('active', step === 2);

    wizPane1.classList.toggle('active', step === 1);
    wizPane2.classList.toggle('active', step === 2);

    // Buttons
    btnWizardBack.style.display = (step === 2 && !wizard.academicOnly) ? '' : 'none';
    btnWizardNext.style.display = (step === 1 && wizard.mode !== 'view' && !wizard.academicOnly) ? '' : (step === 1 && wizard.mode === 'view' && !wizard.academicOnly ? '' : 'none');
    btnWizardFinish.style.display = (step === 2 && wizard.mode !== 'view') ? '' : 'none';
    btnWizardSkipAcademic.style.display = (step === 2 && wizard.mode !== 'view') ? '' : 'none';

    // Hint
    if (wizard.academicOnly) {
      wizHint.innerHTML = `<i class="fa fa-circle-info me-1"></i> You are adding/updating academic details for this student.`;
    } else {
      wizHint.innerHTML = `<i class="fa fa-circle-info me-1"></i> Fill student basic details first. Then continue to add academic details (you can also add/update academic details later from the 3-dot menu).`;
    }
  }

  function resetWizard() {
    wizard.step = 1;
    wizard.mode = 'create';
    wizard.academicOnly = false;
    wizard.user = { id: null, uuid: null, email: '' };
    wizard.academic = { id: null, mode: 'create' };

    // clear hidden ids
    uuidInput.value = '';
    editingUserIdInput.value = '';

    acadRecordId.value = '';
    acadUserId.value = '';

    // clear fields
    form.reset();
    clearInvalids(form);

    // force role student
    if (roleInput) roleInput.value = 'student';

    // image preview
    imgPrev.style.display = 'none';
    imgPrev.src = '';

    // reset select defaults
    if (statusInput) statusInput.value = 'active';
    if (acadSemester) acadSemester.innerHTML = `<option value="">Select Semester (optional)</option>`;
    if (acadSection) acadSection.innerHTML = `<option value="">Select Section (optional)</option>`;

    // password UI default (create)
    if (pwdReq) pwdReq.style.display = 'inline';
    if (pwdHelp) pwdHelp.textContent = 'Enter password for new student';
    currentPwdRow.style.display = 'none';
    currentPwdInput.value = '';

    // academic labels
    acadForStudentLabel.textContent = '—';
    acadModeBadge.innerHTML = `<i class="fa fa-graduation-cap me-1"></i>Academic Details`;

    // ✅ remove metadata from form (not needed)
    if (acadMeta) acadMeta.value = '';

    setDisabledAll(form, false);
    setStepActive(1);
  }

  imageInput?.addEventListener('input', () => {
    const url = (imageInput.value || '').trim();
    if (!url) { imgPrev.style.display = 'none'; imgPrev.src = ''; return; }
    imgPrev.src = fixImageUrl(url) || url;
    imgPrev.style.display = 'block';
  });

  function setModalTitle() {
    if (wizard.academicOnly) {
      modalTitle.textContent = 'Academic Details';
      modalSub.textContent = 'Add/Update student academic details';
      return;
    }
    const isCreate = !uuidInput.value; // if no uuid yet, it's a create
    if (isCreate) {
      modalTitle.textContent = 'Add Student';
      modalSub.textContent = 'Step-up form: Basic Details → Academic Details';
    } else if (wizard.mode === 'edit') {
      modalTitle.textContent = 'Edit Student';
      modalSub.textContent = 'Update basic details, then update academic details';
    } else {
      modalTitle.textContent = 'View Student';
      modalSub.textContent = 'Read-only view';
    }
  }

  function setButtonLoading(button, loading) {
    if (!button) return;
    if (loading) { button.disabled = true; button.classList.add('btn-loading'); }
    else { button.disabled = false; button.classList.remove('btn-loading'); }
  }

  function validateStep1() {
    clearInvalids(wizPane1);

    let okk = true;

    if (!nameInput.value.trim()) { nameInput.classList.add('is-invalid'); okk = false; }
    if (!emailInput.value.trim() || !emailInput.checkValidity()) { emailInput.classList.add('is-invalid'); okk = false; }

    const pwdEl = document.getElementById('userPassword');
    const pwd2El = document.getElementById('userPasswordConfirmation');
    const password = (pwdEl?.value || '').trim();
    const password2 = (pwd2El?.value || '').trim();

    if (wizard.mode === 'create') {
      if (!password) { pwdEl?.classList.add('is-invalid'); okk = false; }
      if (password && password2 && password !== password2) { pwd2El?.classList.add('is-invalid'); okk = false; }
      if (password && !password2) { pwd2El?.classList.add('is-invalid'); okk = false; }
    } else {
      if (password && password2 && password !== password2) { pwd2El?.classList.add('is-invalid'); okk = false; }
    }

    return okk;
  }

  function validateStep2() {
    clearInvalids(wizPane2);
    let okk = true;
    if (!acadDept.value) { acadDept.classList.add('is-invalid'); okk = false; }
    if (!acadCourse.value) { acadCourse.classList.add('is-invalid'); okk = false; }

    // Validate attendance percentage if provided
    if (acadAttendance && acadAttendance.value !== '') {
      const v = parseFloat(acadAttendance.value);
      if (Number.isNaN(v) || v < 0 || v > 100) {
        acadAttendance.classList.add('is-invalid');
        okk = false;
      }
    }

    return okk;
  }

  function fillAcademicFromUserDept() {
    const depVal = (deptInput.value || '').toString();
    if (acadDept && depVal) acadDept.value = depVal;
  }

  async function fetchUser(uuid) {
    const res = await fetch(`/api/users/${encodeURIComponent(uuid)}`, { headers: authHeaders() });
    if (handleAuthStatus(res, 'You are not allowed to view this user.')) return null;
    const js = await res.json().catch(() => ({}));
    if (!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to fetch user');
    return js.data || null;
  }

  async function findAcademicRecordByEmail(userId, email) {
    if (!email) return null;
    const params = new URLSearchParams();
    params.set('q', email);
    params.set('per_page', '50');

    const res = await fetch('/api/student-academic-details?' + params.toString(), { headers: authHeaders() });
    if (handleAuthStatus(res, 'You are not allowed to load academic details.')) return null;

    const js = await res.json().catch(() => ({}));
    if (!res.ok || js.success === false) return null;

    const data = js.data || {};
    const rows = Array.isArray(data.data) ? data.data : (Array.isArray(data) ? data : []);
    const uid = parseInt(userId || 0, 10);
    const rec = (rows || []).find(x => parseInt(x?.user_id || 0, 10) === uid);
    return rec || null;
  }

  function fillAcademicForm(rec) {
    acadRecordId.value = rec?.id ? String(rec.id) : '';
    acadUserId.value = rec?.user_id ? String(rec.user_id) : (wizard.user.id ? String(wizard.user.id) : '');

    acadDept.value = rec?.department_id ? String(rec.department_id) : (deptInput.value || '');
    acadCourse.value = rec?.course_id ? String(rec.course_id) : '';

    acadAcademicYear.value = rec?.academic_year || '';
    acadYear.value = rec?.year ?? '';
    acadStatus.value = rec?.status || '';
    acadRoll.value = rec?.roll_no || '';
    acadReg.value = rec?.registration_no || '';
    acadAdm.value = rec?.admission_no || '';
    acadAdmDate.value = rec?.admission_date ? String(rec.admission_date).slice(0,10) : '';
    acadBatch.value = rec?.batch || '';
    acadSession.value = rec?.session || '';
    if (acadAttendance) {
      acadAttendance.value =
        (rec?.attendance_percentage !== null && rec?.attendance_percentage !== undefined)
          ? String(rec.attendance_percentage)
          : '';
    }

    // ✅ metadata removed
    if (acadMeta) acadMeta.value = '';

    const semId = rec?.semester_id ? String(rec.semester_id) : '';
    const secId = rec?.section_id ? String(rec.section_id) : '';

    (async () => {
      try {
        showGlobalLoading(true);
        if (acadCourse.value) {
          await loadSemestersByCourse(acadCourse.value);
          acadSemester.value = semId;
          if (acadSemester.value) {
            await loadSectionsBySemester(acadSemester.value);
            acadSection.value = secId;
          } else {
            renderSections([]);
          }
        }
      } finally {
        showGlobalLoading(false);
      }
    })();
  }

  function setWizardReadOnly(viewOnly) {
    setDisabledAll(form, !!viewOnly);
  }

  // ==========================
  // Save basic (create/update) + optional password patch
  // ==========================
  async function saveBasicStep() {
    const pwdEl  = document.getElementById('userPassword');
    const pwd2El = document.getElementById('userPasswordConfirmation');
    const password  = (pwdEl?.value || '').trim();
    const password2 = (pwd2El?.value || '').trim();

    const payload = {
      name:  nameInput.value.trim(),
      email: emailInput.value.trim(),
      role:  'student',
      status: statusInput.value || 'active'
    };

    if (phoneInput.value.trim())     payload.phone_number = phoneInput.value.trim();
    if (altEmailInput.value.trim())  payload.alternative_email = altEmailInput.value.trim();
    if (altPhoneInput.value.trim())  payload.alternative_phone_number = altPhoneInput.value.trim();
    if (waInput.value.trim())        payload.whatsapp_number = waInput.value.trim();
    if (addrInput.value.trim())      payload.address = addrInput.value.trim();
    if (imageInput.value.trim())     payload.image = imageInput.value.trim();

    const depVal = (deptInput.value || '').toString().trim();
    if (depVal) {
      const depId = Number(depVal);
      if (!Number.isInteger(depId) || depId <= 0) {
        throw new Error('Invalid Department selected. Please refresh and select again.');
      }
      payload.department_id = depId;
    }

    const isCreate = !uuidInput.value;

    if (isCreate) {
      if (!password) throw new Error('Password is required for new student');
      if (!password2) throw new Error('Confirm password is required for new student');
      if (password !== password2) throw new Error('Passwords do not match');
      payload.password = password;
    }

    if (isCreate) {
      const res = await fetch('/api/users', {
        method: 'POST',
        headers: { ...authHeaders({ 'Content-Type': 'application/json' }) },
        body: JSON.stringify(payload)
      });
      if (handleAuthStatus(res, 'You are not allowed to create students.')) return null;

      const js = await res.json().catch(() => ({}));
      if (!res.ok || js.success === false) {
        let msg = js.error || js.message || 'Create failed';
        if (js.errors) {
          const k = Object.keys(js.errors)[0];
          if (k && js.errors[k] && js.errors[k][0]) msg = js.errors[k][0];
        }
        throw new Error(msg);
      }

      const created = js.data || null;

      if (created?.uuid) uuidInput.value = created.uuid;
      if (created?.id) editingUserIdInput.value = String(created.id);

      wizard.mode = 'edit';
      wizard.user.id = created?.id || wizard.user.id;
      wizard.user.uuid = created?.uuid || wizard.user.uuid;
      wizard.user.email = created?.email || wizard.user.email;

      if (created?.id) acadUserId.value = String(created.id);

      if (pwdReq) pwdReq.style.display = 'none';
      if (pwdHelp) pwdHelp.textContent = 'Leave blank to keep current password';

      return created;
    }

    const uUuid = uuidInput.value;
    if (!uUuid) throw new Error('User UUID missing. Please close and re-open the modal.');

    const res = await fetch(`/api/users/${encodeURIComponent(uUuid)}`, {
      method: 'PUT',
      headers: { ...authHeaders({ 'Content-Type': 'application/json' }) },
      body: JSON.stringify(payload)
    });
    if (handleAuthStatus(res, 'You are not allowed to update students.')) return null;

    const js = await res.json().catch(() => ({}));
    if (!res.ok || js.success === false) {
      let msg = js.error || js.message || 'Update failed';
      if (js.errors) {
        const k = Object.keys(js.errors)[0];
        if (k && js.errors[k] && js.errors[k][0]) msg = js.errors[k][0];
      }
      throw new Error(msg);
    }

    if (password) {
      if (!password2) throw new Error('Confirm password is required');
      if (password !== password2) throw new Error('Passwords do not match');

      const pwPayload = { password, password_confirmation: password2 };

      const isSelf =
        ACTOR.id &&
        (parseInt(ACTOR.id, 10) === parseInt(editingUserIdInput.value || '0', 10));

      if (isSelf) {
        if (!currentPwdInput.value.trim()) {
          throw new Error('Current password is required to change your own password');
        }
        pwPayload.current_password = currentPwdInput.value.trim();
      }

      const res2 = await fetch(`/api/users/${encodeURIComponent(uUuid)}/password`, {
        method: 'PATCH',
        headers: { ...authHeaders({ 'Content-Type': 'application/json' }) },
        body: JSON.stringify(pwPayload)
      });
      if (handleAuthStatus(res2, 'You are not allowed to change passwords.')) return null;

      const js2 = await res2.json().catch(() => ({}));
      if (!res2.ok || js2.success === false) {
        let msg2 = js2.error || js2.message || 'Password update failed';
        if (js2.errors) {
          const k2 = Object.keys(js2.errors)[0];
          if (k2 && js2.errors[k2] && js2.errors[k2][0]) msg2 = js2.errors[k2][0];
        }
        throw new Error(msg2);
      }
    }

    const updated = js.data || null;
    if (updated?.id) editingUserIdInput.value = String(updated.id);
    if (updated?.uuid) uuidInput.value = updated.uuid;
    if (updated?.id) acadUserId.value = String(updated.id);

    wizard.mode = 'edit';
    wizard.user.id = updated?.id || wizard.user.id;
    wizard.user.uuid = updated?.uuid || wizard.user.uuid;
    wizard.user.email = updated?.email || wizard.user.email;

    return updated;
  }

  // ==========================
  // Save academic (create/update)  ✅ metadata removed
  // ==========================
  async function saveAcademicStep() {
    const payload = {
      user_id: parseInt(acadUserId.value || wizard.user.id || 0, 10),
      department_id: parseInt(acadDept.value || 0, 10),
      course_id: parseInt(acadCourse.value || 0, 10),
      semester_id: acadSemester.value ? (parseInt(acadSemester.value, 10) || null) : null,
      section_id: acadSection.value ? (parseInt(acadSection.value, 10) || null) : null,
      academic_year: acadAcademicYear.value.trim() || null,
      year: acadYear.value ? (parseInt(acadYear.value, 10) || null) : null,
      roll_no: acadRoll.value.trim() || null,
      registration_no: acadReg.value.trim() || null,
      admission_no: acadAdm.value.trim() || null,
      admission_date: acadAdmDate.value || null,
      batch: acadBatch.value.trim() || null,
      session: acadSession.value.trim() || null,
      attendance_percentage: (acadAttendance && acadAttendance.value !== '')
        ? Math.max(0, Math.min(100, parseFloat(acadAttendance.value)))
        : null,
      status: acadStatus.value || null
    };

    if (!payload.user_id) throw new Error('User ID missing. Please save basic details first.');
    if (!payload.department_id) throw new Error('Academic Department is required.');
    if (!payload.course_id) throw new Error('Course is required.');

    const recId = acadRecordId.value ? parseInt(acadRecordId.value, 10) : 0;
    const isUpdate = !!recId;

    const res = await fetch(
      isUpdate ? `/api/student-academic-details/${encodeURIComponent(recId)}` : '/api/student-academic-details',
      {
        method: isUpdate ? 'PUT' : 'POST',
        headers: { ...authHeaders({ 'Content-Type': 'application/json' }) },
        body: JSON.stringify(payload)
      }
    );
    if (handleAuthStatus(res, 'You are not allowed to save academic details.')) return null;

    const js = await res.json().catch(() => ({}));
    if (!res.ok || js.success === false) {
      let msg = js.error || js.message || (isUpdate ? 'Academic update failed' : 'Academic create failed');
      if (js.errors) {
        const k = Object.keys(js.errors)[0];
        if (k && js.errors[k] && js.errors[k][0]) msg = js.errors[k][0];
      }
      throw new Error(msg);
    }

    return js.data || null;
  }

  // ==========================
  // Open wizard modes
  // ==========================
  async function openCreateWizard() {
    resetWizard();
    wizard.mode = 'create';
    wizard.academicOnly = false;

    pwdReq.style.display = 'inline';
    pwdHelp.textContent = 'Enter password for new student';
    currentPwdRow.style.display = 'none';

    setModalTitle();
    setWizardReadOnly(false);

    roleInput.value = 'student';

    if (!state.departmentsLoaded) await loadDepartments(false);
    await loadCourses();

    userModal.show();
    setStepActive(1);
  }

  async function openEditWizard(uuid, viewOnly = false) {
    resetWizard();
    wizard.mode = viewOnly ? 'view' : 'edit';
    wizard.academicOnly = false;

    setModalTitle();

    pwdReq.style.display = 'none';
    pwdHelp.textContent = viewOnly ? '—' : 'Leave blank to keep current password';

    if (!state.departmentsLoaded) await loadDepartments(false);
    await loadCourses();

    showGlobalLoading(true);
    try {
      const u = await fetchUser(uuid);
      if (!u) return;

      wizard.user.id = u.id || null;
      wizard.user.uuid = u.uuid || uuid;
      wizard.user.email = u.email || '';

      uuidInput.value = wizard.user.uuid || '';
      editingUserIdInput.value = u.id || '';

      nameInput.value = u.name || '';
      emailInput.value = u.email || '';
      phoneInput.value = u.phone_number || '';
      altEmailInput.value = u.alternative_email || '';
      altPhoneInput.value = u.alternative_phone_number || '';
      waInput.value = u.whatsapp_number || '';
      addrInput.value = u.address || '';
      statusInput.value = u.status || 'active';
      deptInput.value = (u.department_id !== undefined && u.department_id !== null) ? String(u.department_id) : '';
      roleInput.value = 'student';

      imageInput.value = u.image || '';
      if (u.image) { imgPrev.src = fixImageUrl(u.image) || u.image; imgPrev.style.display = 'block'; }

      const isSelf = ACTOR.id && (parseInt(ACTOR.id, 10) === parseInt(u.id || 0, 10));
      currentPwdRow.style.display = (!viewOnly && isSelf) ? '' : 'none';

      acadForStudentLabel.textContent = u.email ? `For: ${u.email}` : '—';

      setWizardReadOnly(viewOnly);

      userModal.show();
      setStepActive(1);
    } finally {
      showGlobalLoading(false);
    }
  }

  // ✅ Security: open academic using ONLY UUID (no DOM numeric id exposure)
  async function openAcademicOnlyWizard(uuid, email) {
    resetWizard();
    wizard.mode = 'edit';
    wizard.academicOnly = true;
    wizard.step = 2;

    wizard.user.uuid = uuid;
    wizard.user.id = null;
    wizard.user.email = email || '';

    uuidInput.value = uuid || '';
    editingUserIdInput.value = '';

    pwdReq.style.display = 'none';
    pwdHelp.textContent = '—';
    currentPwdRow.style.display = 'none';

    setModalTitle();

    if (!state.departmentsLoaded) await loadDepartments(false);
    await loadCourses();

    showGlobalLoading(true);
    try {
      const u = await fetchUser(uuid);
      if (u) {
        wizard.user.id = u.id || null;
        wizard.user.email = u.email || wizard.user.email;

        // store internal id only (not exposed in table dataset)
        editingUserIdInput.value = wizard.user.id ? String(wizard.user.id) : '';
        acadUserId.value = wizard.user.id ? String(wizard.user.id) : '';

        acadForStudentLabel.textContent = wizard.user.email ? `For: ${wizard.user.email}` : '—';

        deptInput.value = (u.department_id !== undefined && u.department_id !== null) ? String(u.department_id) : '';
        fillAcademicFromUserDept();
      }

      const rec = await findAcademicRecordByEmail(wizard.user.id, wizard.user.email);
      if (rec) {
        wizard.academic.mode = 'update';
        wizard.academic.id = rec.id;
        acadModeBadge.innerHTML = `<i class="fa fa-pen-to-square me-1"></i>Update Academic Details`;
        fillAcademicForm(rec);
      } else {
        wizard.academic.mode = 'create';
        wizard.academic.id = null;
        acadModeBadge.innerHTML = `<i class="fa fa-plus me-1"></i>Add Academic Details`;
        acadRecordId.value = '';
        fillAcademicFromUserDept();
      }

      userModal.show();
      setStepActive(2);

      setWizardReadOnly(false);
      setDisabledAll(wizPane1, true);
    } finally {
      showGlobalLoading(false);
    }
  }

  wizStep1?.addEventListener('click', () => {
    if (wizard.academicOnly) return;
    if (wizard.step === 1) return;
    if (wizard.mode === 'view') { setStepActive(1); return; }
    setStepActive(1);
  });

  wizStep2?.addEventListener('click', async () => {
    if (wizard.academicOnly) return;
    if (wizard.step === 2) return;

    if (wizard.mode === 'create' || wizard.mode === 'edit') {
      const okk = validateStep1();
      if (!okk) return;
      try {
        setButtonLoading(btnWizardNext, true);
        showGlobalLoading(true);

        const u = await saveBasicStep();
        if (!u) return;

        wizard.user.id = u.id || wizard.user.id;
        wizard.user.uuid = u.uuid || uuidInput.value;
        wizard.user.email = u.email || emailInput.value.trim();

        uuidInput.value = wizard.user.uuid || '';
        editingUserIdInput.value = wizard.user.id || '';

        acadUserId.value = wizard.user.id ? String(wizard.user.id) : '';
        fillAcademicFromUserDept();
        acadForStudentLabel.textContent = wizard.user.email ? `For: ${wizard.user.email}` : '—';

        const rec = await findAcademicRecordByEmail(wizard.user.id, wizard.user.email);
        if (rec) {
          wizard.academic.mode = 'update';
          wizard.academic.id = rec.id;
          acadModeBadge.innerHTML = `<i class="fa fa-pen-to-square me-1"></i>Update Academic Details`;
          fillAcademicForm(rec);
        } else {
          wizard.academic.mode = 'create';
          wizard.academic.id = null;
          acadModeBadge.innerHTML = `<i class="fa fa-plus me-1"></i>Add Academic Details`;
          acadRecordId.value = '';
        }

        setStepActive(2);
      } catch (ex) {
        err(ex.message);
      } finally {
        setButtonLoading(btnWizardNext, false);
        showGlobalLoading(false);
      }
    } else {
      setStepActive(2);
    }
  });

  btnWizardBack?.addEventListener('click', () => setStepActive(1));

  btnWizardNext?.addEventListener('click', async () => {
    if (wizard.academicOnly) return;
    if (wizard.step !== 1) return;

    if (wizard.mode === 'view') {
      setStepActive(2);
      return;
    }

    const okk = validateStep1();
    if (!okk) return;

    try {
      setButtonLoading(btnWizardNext, true);
      showGlobalLoading(true);

      const u = await saveBasicStep();
      if (!u) return;

      wizard.user.id = u.id || wizard.user.id;
      wizard.user.uuid = u.uuid || uuidInput.value;
      wizard.user.email = u.email || emailInput.value.trim();

      uuidInput.value = wizard.user.uuid || '';
      editingUserIdInput.value = wizard.user.id || '';

      acadUserId.value = wizard.user.id ? String(wizard.user.id) : '';
      fillAcademicFromUserDept();
      acadForStudentLabel.textContent = wizard.user.email ? `For: ${wizard.user.email}` : '—';

      const rec = await findAcademicRecordByEmail(wizard.user.id, wizard.user.email);
      if (rec) {
        wizard.academic.mode = 'update';
        wizard.academic.id = rec.id;
        acadModeBadge.innerHTML = `<i class="fa fa-pen-to-square me-1"></i>Update Academic Details`;
        fillAcademicForm(rec);
      } else {
        wizard.academic.mode = 'create';
        wizard.academic.id = null;
        acadModeBadge.innerHTML = `<i class="fa fa-plus me-1"></i>Add Academic Details`;
        acadRecordId.value = '';
      }

      setStepActive(2);
    } catch (ex) {
      err(ex.message);
    } finally {
      setButtonLoading(btnWizardNext, false);
      showGlobalLoading(false);
    }
  });

  btnWizardSkipAcademic?.addEventListener('click', async () => {
    userModal.hide();
    ok('Saved');
    await loadUsers(false);
  });

  btnWizardFinish?.addEventListener('click', async () => {
    if (wizard.step !== 2) return;

    const okk = validateStep2();
    if (!okk) return;

    try {
      setButtonLoading(btnWizardFinish, true);
      showGlobalLoading(true);

      const rec = await saveAcademicStep();
      if (!rec) return;

      ok('Student saved');
      userModal.hide();
      await loadUsers(false);
    } catch (ex) {
      err(ex.message);
    } finally {
      setButtonLoading(btnWizardFinish, false);
      showGlobalLoading(false);
    }
  });

  btnAddStudent?.addEventListener('click', async () => {
    if (!canCreate) return;
    try {
      showGlobalLoading(true);
      if (!state.departmentsLoaded) await loadDepartments(false);
      await loadCourses();
      await openCreateWizard();
    } finally {
      showGlobalLoading(false);
    }
  });

  // Row actions
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const tr = btn.closest('tr');
    const uuid = tr?.dataset?.uuid;
    const email = tr?.dataset?.email || '';
    if (!uuid) return;

    const act = btn.dataset.action;

    const setSpin = (on) => {
      if (on) {
        btn.disabled = true;
        btn.dataset._old = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
      } else {
        btn.disabled = false;
        if (btn.dataset._old) btn.innerHTML = btn.dataset._old;
      }
    };

    if (act === 'profile') {
      window.open(`/user/profile/${encodeURIComponent(uuid)}`, '_blank', 'noopener');
      return;
    }

    if (act === 'assign_privilege') {
      // ✅ Security: only uuid in query
      window.location.href = `/user-privileges/manage?user_uuid=${encodeURIComponent(uuid)}`;
      return;
    }

    if (act === 'acad') {
      setSpin(true);
      openAcademicOnlyWizard(uuid, email).finally(() => setSpin(false));
      return;
    }

    if (act === 'view') {
      setSpin(true);
      openEditWizard(uuid, true).finally(() => setSpin(false));
      return;
    }

    if (act === 'edit') {
      if (!canEdit) return;
      setSpin(true);
      openEditWizard(uuid, false).finally(() => setSpin(false));
      return;
    }

    if (act === 'delete') {
      if (!canDelete) return;
      Swal.fire({
        title: 'Delete student?',
        text: 'This will soft delete the student (status to inactive).',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#ef4444'
      }).then(async r => {
        if (!r.isConfirmed) return;
        try {
          setSpin(true);
          showGlobalLoading(true);
          const res = await fetch(`/api/users/${encodeURIComponent(uuid)}`, { method: 'DELETE', headers: authHeaders() });
          if (handleAuthStatus(res, 'You are not allowed to delete students.')) return;

          const js = await res.json().catch(() => ({}));
          if (!res.ok || js.success === false) throw new Error(js.error || js.message || 'Delete failed');

          ok('Student deleted');
          await loadUsers(false);
        } catch (ex) {
          err(ex.message);
        } finally {
          setSpin(false);
          showGlobalLoading(false);
        }
      });
      return;
    }

    const toggle = btn.closest('.dropdown')?.querySelector('.dd-toggle');
    if (toggle) { try { bootstrap.Dropdown.getOrCreateInstance(toggle).hide(); } catch (_) {} }
  });

  deptInput?.addEventListener('change', () => {
    if (wizard.step === 2) fillAcademicFromUserDept();
  });

  // ==========================
  // Init
  // ==========================
  (async () => {
    showGlobalLoading(true);
    await fetchMe();
    await loadDepartments(false);
    await loadCourses();
    await loadUsers(false);
    showGlobalLoading(false);
  })();
});
</script>
@endpush
