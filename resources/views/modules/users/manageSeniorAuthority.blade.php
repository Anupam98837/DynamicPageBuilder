{{-- resources/views/modules/user/manageAdmins.blade.php --}}
@section('title','Manage Admins')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* Dropdowns inside table */
.table-wrap .dropdown{position:relative}
.dropdown [data-bs-toggle="dropdown"]{border-radius:10px}
.dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:220px;z-index:1085}
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

/* Badges */
.badge-soft-primary{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  color:var(--primary-color)
}
.badge-soft-success{
  background:color-mix(in oklab, var(--success-color) 12%, transparent);
  color:var(--success-color)
}
.badge-soft-danger{
  background:color-mix(in oklab, var(--danger-color) 12%, transparent);
  color:var(--danger-color)
}

/* Loading overlay */
.loading-overlay{
  position:fixed;
  top:0;left:0;width:100%;height:100%;
  background:rgba(0,0,0,0.45);
  display:flex;
  justify-content:center;
  align-items:center;
  z-index:9999;
  backdrop-filter:blur(2px)
}
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
  .musers-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .musers-toolbar .position-relative{min-width:100% !important}
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

/* ✅ Horizontal scroll on small screens */
.table-responsive{
  display:block;
  width:100%;
  max-width:100%;
  overflow-x:auto !important;
  overflow-y:visible !important;
  -webkit-overflow-scrolling:touch;
}
.table-responsive > .table{
  width:max-content;
  min-width:980px;
}
.table-responsive th,
.table-responsive td{
  white-space:nowrap;
}
@media (max-width: 576px){
  .table-responsive > .table{ min-width:920px; }
}
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
        <i class="fa-solid fa-user-check me-2"></i>Active Admins
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-inactive" role="tab" aria-selected="false">
        <i class="fa-solid fa-user-slash me-2"></i>Inactive Admins
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

          {{-- ✅ IMPORT (same option like other manage pages) --}}
          <button id="btnImportAdmins" class="btn btn-outline-primary" style="display:none;">
            <i class="fa fa-file-import me-1"></i>Import CSV
          </button>
          {{-- Hidden file input for import --}}
          <input id="importAdminsFile" type="file" accept=".csv,text/csv" style="display:none;" />

          {{-- ✅ EXPORT --}}
          <button id="btnExportAdmins" class="btn btn-outline-success">
            <i class="fa fa-file-csv me-1"></i>Export CSV
          </button>
        </div>

        <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
          <div id="writeControls" style="display:none;">
            <button type="button" class="btn btn-primary" id="btnAddUser">
              <i class="fa fa-plus me-1"></i> Add Admin
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
                  <th style="width:82px;">Status</th>
                  <th style="width:74px;">Avatar</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th style="width:220px;">Role</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="usersTbody-active">
                <tr>
                  <td colspan="7" class="text-center text-muted" style="padding:38px;">Loading…</td>
                </tr>
              </tbody>
            </table>
          </div>

          {{-- Empty --}}
          <div id="empty-active" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-users-gear mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No active admins found for current filters.</div>
          </div>

          {{-- Footer --}}
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
                  <th style="width:82px;">Status</th>
                  <th style="width:74px;">Avatar</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th style="width:220px;">Role</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="usersTbody-inactive">
                <tr>
                  <td colspan="7" class="text-center text-muted" style="padding:38px;">Loading…</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div id="empty-inactive" class="empty p-4 text-center" style="display:none;">
            <i class="fa fa-user-slash mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No inactive admins found.</div>
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
        <h5 class="modal-title"><i class="fa fa-sliders me-2"></i>Filter Admins</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Role</label>
            {{-- ✅ Options: All + Admin + Super Admin + Director + Principal --}}
            <select id="modal_role" class="form-select">
              <option value="">All </option>
              <option value="admin" style="display:none">Admin</option>
              <option value="super_admin">Super Admin</option>
              <option value="director">Director</option>
              <option value="principal">Principal</option>
            </select>
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

{{-- Add/Edit/View User Modal --}}
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" id="userForm">
      <div class="modal-header">
        <h5 class="modal-title" id="userModalTitle">Add Admin</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="userUuid"/>
        <input type="hidden" id="editingUserId"/>

        <div class="row g-3">
          <div class="col-md-12">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input class="form-control" id="userName" required maxlength="190" placeholder="John Doe">
          </div>

          <div class="col-md-6">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="userEmail" required maxlength="255" placeholder="john.doe@example.com">
          </div>

          <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input class="form-control" id="userPhone" maxlength="32" placeholder="+91 99999 99999">
          </div>

          <div class="col-md-6">
            <label class="form-label">Role <span class="text-danger">*</span></label>
            <select class="form-select" id="userRole" required>
              <option value="admin">Admin</option>
              <option value="super_admin" style="display:none">Super Admin</option>
              <option value="director">Director</option>
              <option value="principal">Principal</option>
            </select>
          </div>

          {{-- Department --}}
          <div class="col-md-6">
            <label class="form-label" for="userDepartment">Department </label>
            <select class="form-select" id="userDepartment" name="department_id">
              <option value="" selected disabled>Select Department</option>
            </select>
            <div class="invalid-feedback">Please select a department.</div>
            <div class="form-text">Loaded from <code>/api/departments</code></div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select class="form-select" id="userStatus">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Password <span class="text-danger" id="passwordRequired">*</span></label>
            <input type="password" class="form-control" id="userPassword" placeholder="••••••••">
            <div class="form-text" id="passwordHelp">Enter password for new user</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="userPasswordConfirmation" placeholder="••••••••">
          </div>

          <div class="col-12" id="currentPasswordRow" style="display:none;">
            <label class="form-label">Current Password (required when changing your own password)</label>
            <input type="password" class="form-control" id="userCurrentPassword" placeholder="Current password">
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

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="saveUserBtn">
          <i class="fa fa-floppy-disk me-1"></i> Save
        </button>
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
      autoClose: btn.getAttribute('data-bs-auto-close') || undefined,
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
  function showGlobalLoading(show) { if (globalLoading) globalLoading.style.display = show ? 'flex' : 'none'; }

  function authHeaders(extra = {}) {
    return Object.assign({ 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }, extra);
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

  function debounce(fn, ms = 350) {
    let t;
    return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
  }

  function fixImageUrl(url) {
    if (!url) return null;
    if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('//')) return url;
    if (url.startsWith('/')) return url;
    return '/' + url.replace(/^\/+/, '');
  }

  // ✅ Role sets for this manage page
  const ADMIN_GROUP = new Set(['admin']);
  const SUPER_ADMIN_GROUP = new Set(['super_admin','superadmin','super-admin']);
  const DIRECTOR_GROUP = new Set(['director']);
  const PRINCIPAL_GROUP = new Set(['principal']);

  // ✅ Allowed roles on this page
  const ALLOWED = new Set([
    ...ADMIN_GROUP,
    ...SUPER_ADMIN_GROUP,
    ...DIRECTOR_GROUP,
    ...PRINCIPAL_GROUP
  ]);

  const ROLE_LABEL = {
    admin: 'Admin',
    super_admin: 'Super Admin',
    superadmin: 'Super Admin',
    'super-admin': 'Super Admin',
    director: 'Director',
    principal: 'Principal',
  };
  const roleLabel = v => ROLE_LABEL[(v || '').toLowerCase()] || (v || '');

  // Toasts
  const toastOk = new bootstrap.Toast(document.getElementById('toastSuccess'));
  const toastErr = new bootstrap.Toast(document.getElementById('toastError'));
  const okTxt = document.getElementById('toastSuccessText');
  const errTxt = document.getElementById('toastErrorText');
  const ok = m => { okTxt.textContent = m || 'Done'; toastOk.show(); };
  const err = m => { errTxt.textContent = m || 'Something went wrong'; toastErr.show(); };

  // DOM refs
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
  const filterModalEl = document.getElementById('filterModal');
  const filterModal = new bootstrap.Modal(filterModalEl);

  const writeControls = document.getElementById('writeControls');
  const btnAdd = document.getElementById('btnAddUser');
  const btnExportAdmins = document.getElementById('btnExportAdmins');

  // ✅ Import refs
  const btnImportAdmins = document.getElementById('btnImportAdmins');
  const importAdminsFile = document.getElementById('importAdminsFile');

  // Modal + form
  const userModalEl = document.getElementById('userModal');
  const userModal = new bootstrap.Modal(userModalEl);
  const form = document.getElementById('userForm');
  const modalTitle = document.getElementById('userModalTitle');
  const saveBtn = document.getElementById('saveUserBtn');

  const uuidInput = document.getElementById('userUuid');
  const editingUserIdInput = document.getElementById('editingUserId');
  const nameInput = document.getElementById('userName');
  const emailInput = document.getElementById('userEmail');
  const phoneInput = document.getElementById('userPhone');
  const roleInput = document.getElementById('userRole');
  const deptInput = document.getElementById('userDepartment');
  const statusInput = document.getElementById('userStatus');
  const pwdInput = document.getElementById('userPassword');
  const pwd2Input = document.getElementById('userPasswordConfirmation');
  const currentPwdInput = document.getElementById('userCurrentPassword');
  const currentPwdRow = document.getElementById('currentPasswordRow');
  const pwdReq = document.getElementById('passwordRequired');
  const pwdHelp = document.getElementById('passwordHelp');
  const altEmailInput = document.getElementById('userAltEmail');
  const altPhoneInput = document.getElementById('userAltPhone');
  const waInput = document.getElementById('userWhatsApp');
  const addrInput = document.getElementById('userAddress');
  const imageInput = document.getElementById('userImage');
  const imgPrev = document.getElementById('imagePreview');

  // Actor & permissions
  const ACTOR = { id: null, role: '' };
  let canCreate = false, canEdit = false, canDelete = false;

  const state = {
    items: [],
    q: '',
    roleFilter: '', // '' => All
    sort: '-created_at',
    perPage: 10,
    page: { active: 1, inactive: 1 },
    total: { active: 0, inactive: 0 },
    totalPages: { active: 1, inactive: 1 },

    departments: [],
    departmentsLoaded: false,
  };

  function computePermissions() {
    const r = (ACTOR.role || '').toLowerCase();
    const createDeleteRoles = ['super_admin','superadmin','super-admin','admin','director','principal'];
    const writeRoles = ['super_admin','superadmin','super-admin','admin','director','principal'];

    canCreate = createDeleteRoles.includes(r);
    canDelete = createDeleteRoles.includes(r);
    canEdit = writeRoles.includes(r);

    if (writeControls) writeControls.style.display = canCreate ? 'flex' : 'none';

    // ✅ Import button visibility (same gating as create)
    if (btnImportAdmins) btnImportAdmins.style.display = canCreate ? '' : 'none';
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

  // Cleanup modal backdrops
  function cleanupModalBackdrops() {
    if (!document.querySelector('.modal.show')) {
      document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('padding-right');
      document.body.style.removeProperty('overflow');
    }
  }
  document.addEventListener('hidden.bs.modal', () => setTimeout(cleanupModalBackdrops, 80));

  // Departments
  function deptName(d) {
    return d?.name || d?.title || d?.department_name || d?.dept_name || d?.slug || (d?.id ? `Department #${d.id}` : 'Department');
  }

  function renderDepartmentsOptions() {
    if (!deptInput) return;
    const current = (deptInput.value || '').toString();
    let html = `<option value="" selected disabled>Select Department</option>`;
    (state.departments || []).forEach(d => {
      const id = d?.id ?? d?.value ?? d?.department_id;
      if (id === undefined || id === null || id === '') return;
      html += `<option value="${escapeHtml(String(id))}">${escapeHtml(deptName(d))}</option>`;
    });
    deptInput.innerHTML = html;
    if (current) deptInput.value = current;
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

  function rolesParamForFilter() {
    // All
    if (!state.roleFilter) return 'admin,super_admin,superadmin,super-admin,director,principal';

    if (state.roleFilter === 'admin') return 'admin';
    if (state.roleFilter === 'super_admin') return 'super_admin,superadmin,super-admin';
    if (state.roleFilter === 'director') return 'director';
    if (state.roleFilter === 'principal') return 'principal';

    return 'admin,super_admin,superadmin,super-admin,director,principal';
  }

  async function loadUsers(showOverlay = true) {
    try {
      if (showOverlay) showGlobalLoading(true);

      const params = new URLSearchParams();
      if (state.q) params.set('q', state.q);

      // ✅ Always restrict to allowed roles server-side if endpoint supports "roles"
      params.set('roles', rolesParamForFilter());

      const url = '/api/users' + (params.toString() ? ('?' + params.toString()) : '');
      const res = await fetch(url, { headers: authHeaders() });
      if (handleAuthStatus(res, 'You are not allowed to view users.')) return;

      const js = await res.json().catch(() => ({}));
      if (!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load users');

      const all = Array.isArray(js.data) ? js.data : [];

      // ✅ hard filter safety
      let filtered = all.filter(u => ALLOWED.has((u?.role || '').toLowerCase()));

      // ✅ apply roleFilter locally too
      if (state.roleFilter === 'admin') {
        filtered = filtered.filter(u => ADMIN_GROUP.has((u?.role || '').toLowerCase()));
      } else if (state.roleFilter === 'super_admin') {
        filtered = filtered.filter(u => SUPER_ADMIN_GROUP.has((u?.role || '').toLowerCase()));
      } else if (state.roleFilter === 'director') {
        filtered = filtered.filter(u => DIRECTOR_GROUP.has((u?.role || '').toLowerCase()));
      } else if (state.roleFilter === 'principal') {
        filtered = filtered.filter(u => PRINCIPAL_GROUP.has((u?.role || '').toLowerCase()));
      }

      state.items = filtered;

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
      } else if (sortKey === 'created_at') {
        av = (av || '').toString();
        bv = (bv || '').toString();
      }
      if (av === bv) return 0;
      return av > bv ? dir : -dir;
    });
  }

  function recomputeAndRender() {
    const lists = { active: [], inactive: [] };

    state.items.forEach(u => {
      const rr = (u?.role || '').toLowerCase();
      if (!ALLOWED.has(rr)) return;

      const st = (u.status || 'active').toLowerCase();
      if (st === 'inactive') lists.inactive.push(u);
      else lists.active.push(u);
    });

    const activeSorted = sortUsers(lists.active);
    const inactiveSorted = sortUsers(lists.inactive);

    ['active', 'inactive'].forEach(tab => {
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
    if (infoEl) infoEl.textContent = '—';
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
            <li><button type="button" class="dropdown-item" data-action="view">
              <i class="fa fa-eye"></i> View
            </button></li>`;

      if (canEdit) {
        actionHtml += `<li><button type="button" class="dropdown-item" data-action="edit">
          <i class="fa fa-pen-to-square"></i> Edit
        </button></li>`;
      }
      if (canDelete) {
        actionHtml += `
          <li><hr class="dropdown-divider"></li>
          <li><button type="button" class="dropdown-item text-danger" data-action="delete">
            <i class="fa fa-trash"></i> Delete
          </button></li>`;
      }
      actionHtml += `</ul></div>`;

      return `
        <tr data-uuid="${escapeHtml(row.uuid)}" data-id="${escapeHtml(row.id)}">
          <td>${statusCell}</td>
          <td style="position:relative">${avatarImg}${avatarFallback}</td>
          <td class="fw-semibold">${escapeHtml(row.name || '')}</td>
          <td>${row.email ? `<a href="mailto:${escapeHtml(row.email)}">${escapeHtml(row.email)}</a>` : '<span class="text-muted">—</span>'}</td>
          <td>${row.phone_number ? escapeHtml(row.phone_number) : '<span class="text-muted">—</span>'}</td>
          <td>
            <span class="badge badge-soft-primary">
              <i class="fa fa-user-shield me-1"></i>${escapeHtml(roleLabel(role))}
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

  // Search
  const onSearch = debounce(() => {
    state.q = searchInput.value.trim();
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

  // Filter modal show
  filterModalEl.addEventListener('show.bs.modal', () => {
    modalRole.value = state.roleFilter || '';
    modalSort.value = state.sort || '-created_at';
  });

  // Apply filters
  btnApplyFilters.addEventListener('click', () => {
    state.roleFilter = modalRole.value || '';
    state.sort = modalSort.value || '-created_at';
    state.page.active = 1;
    state.page.inactive = 1;
    filterModal.hide();
    loadUsers();
  });

  // Reset
  btnReset.addEventListener('click', () => {
    state.q = '';
    state.roleFilter = '';
    state.sort = '-created_at';
    state.perPage = 10;
    state.page.active = 1;
    state.page.inactive = 1;

    searchInput.value = '';
    perPageSel.value = '10';
    modalRole.value = '';
    modalSort.value = '-created_at';

    loadUsers();
  });

  // ✅ Export CSV (respects current role filter + search)
  btnExportAdmins?.addEventListener('click', async () => {
    try {
      showGlobalLoading(true);

      const params = new URLSearchParams();
      const q = (searchInput?.value || '').trim();
      if (q) params.set('q', q);

      params.set('roles', rolesParamForFilter());

      const url = '/api/users/export-csv' + (params.toString() ? ('?' + params.toString()) : '');
      const res = await fetch(url, { headers: authHeaders() });
      if (handleAuthStatus(res, 'You are not allowed to export admins.')) return;
      if (!res.ok) {
        const txt = await res.text().catch(() => '');
        throw new Error(txt || 'Export failed');
      }

      const blob = await res.blob();
      const dispo = res.headers.get('Content-Disposition') || '';
      const match = dispo.match(/filename="([^"]+)"/i);
      const filename = match?.[1] || ('admins_export_' + new Date().toISOString().slice(0,19).replace(/[:T]/g,'-') + '.csv');

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

  // ✅ Import CSV (same behavior style; uses /api/users/import-csv)
  btnImportAdmins?.addEventListener('click', async () => {
    if (!canCreate) return;

    const { isConfirmed } = await Swal.fire({
      title: 'Import Admins (CSV)',
      html: `
        <div class="text-start" style="font-size:13px;line-height:1.4">
          <div class="mb-2">
            Upload a <b>.csv</b> file to create/update admin users.
          </div>
          <div class="mb-2 text-muted">
            Tip: keep role values within: <code>admin</code>, <code>super_admin</code>, <code>director</code>, <code>principal</code>
          </div>
          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="swUpdateExisting" checked>
            <label class="form-check-label" for="swUpdateExisting">Update existing users (match by email/uuid if supported)</label>
          </div>
        </div>
      `,
      icon: 'info',
      showCancelButton: true,
      confirmButtonText: 'Choose CSV',
      cancelButtonText: 'Cancel'
    });

    if (!isConfirmed) return;

    // stash updateExisting preference for this selection
    const updateExisting = document.getElementById('swUpdateExisting')?.checked ? '1' : '0';
    importAdminsFile.dataset.update_existing = updateExisting;

    importAdminsFile.value = '';
    importAdminsFile.click();
  });

  importAdminsFile?.addEventListener('change', async () => {
    const file = importAdminsFile.files?.[0];
    if (!file) return;

    const name = (file.name || '').toLowerCase();
    if (!name.endsWith('.csv')) {
      err('Please choose a .csv file');
      importAdminsFile.value = '';
      return;
    }

    // small safety size check (optional)
    const maxMb = 10;
    if (file.size > maxMb * 1024 * 1024) {
      err(`CSV too large (max ${maxMb}MB)`);
      importAdminsFile.value = '';
      return;
    }

    const updateExisting = importAdminsFile.dataset.update_existing || '1';

    const conf = await Swal.fire({
      title: 'Confirm Import',
      text: `Import "${file.name}" ?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Import'
    });
    if (!conf.isConfirmed) {
      importAdminsFile.value = '';
      return;
    }

    try {
      showGlobalLoading(true);

      const fd = new FormData();
      fd.append('file', file);

      // optional hints (backend may ignore safely)
      fd.append('scope', 'admins');
      fd.append('allowed_roles', 'admin,super_admin,superadmin,super-admin,director,principal');
      fd.append('update_existing', updateExisting);

      // If your backend supports applying current filter/search while importing (usually not needed), skip.
      const res = await fetch('/api/users/import-csv', {
        method: 'POST',
        headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
        body: fd
      });
      if (handleAuthStatus(res, 'You are not allowed to import admins.')) return;

      // Try JSON first
      const ct = (res.headers.get('Content-Type') || '').toLowerCase();
      if (ct.includes('application/json')) {
        const js = await res.json().catch(() => ({}));
        if (!res.ok || js.success === false) {
          throw new Error(js.error || js.message || 'Import failed');
        }

        // show summary if present
        const summary = js.summary || js.data?.summary;
        if (summary && typeof summary === 'object') {
          await Swal.fire({
            title: 'Import Complete',
            icon: 'success',
            html: `
              <div class="text-start" style="font-size:13px;line-height:1.5">
                <div><b>Created:</b> ${escapeHtml(String(summary.created ?? summary.inserted ?? 0))}</div>
                <div><b>Updated:</b> ${escapeHtml(String(summary.updated ?? 0))}</div>
                <div><b>Skipped:</b> ${escapeHtml(String(summary.skipped ?? 0))}</div>
                <div><b>Failed:</b> ${escapeHtml(String(summary.failed ?? summary.errors ?? 0))}</div>
              </div>
            `
          });
        } else {
          ok('CSV imported');
        }

        await loadUsers(false);
        return;
      }

      // If backend returns a file (e.g., error report CSV), download it
      if (!res.ok) {
        const txt = await res.text().catch(() => '');
        throw new Error(txt || 'Import failed');
      }
      const blob = await res.blob();
      const dispo = res.headers.get('Content-Disposition') || '';
      const match = dispo.match(/filename="([^"]+)"/i);
      const filename = match?.[1] || ('import_result_' + new Date().toISOString().slice(0,19).replace(/[:T]/g,'-') + '.csv');

      const a = document.createElement('a');
      const u = window.URL.createObjectURL(blob);
      a.href = u;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(u);

      ok('Import processed (downloaded result)');
      await loadUsers(false);
    } catch (ex) {
      err(ex.message);
    } finally {
      showGlobalLoading(false);
      importAdminsFile.value = '';
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
      text: willActive ? 'Activate this user?' : 'Deactivate this user?',
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
      if (handleAuthStatus(res, 'You are not allowed to update user status.')) return;

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

  // Row actions
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const tr = btn.closest('tr');
    const uuid = tr?.dataset?.uuid;
    const id = tr?.dataset?.id;
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
      window.location.href = `/user-privileges/manage?user_uuid=${encodeURIComponent(uuid)}&user_id=${encodeURIComponent(id || '')}`;
      return;
    }

    if (act === 'view') {
      setSpin(true);
      openEdit(uuid, id, true).finally(() => setSpin(false));
    } else if (act === 'edit') {
      if (!canEdit) return;
      setSpin(true);
      openEdit(uuid, id, false).finally(() => setSpin(false));
    } else if (act === 'delete') {
      if (!canDelete) return;
      Swal.fire({
        title: 'Delete user?',
        text: 'This will soft delete the user (status to inactive).',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#ef4444'
      }).then(async r => {
        if (!r.isConfirmed) return;
        try {
          setSpin(true);
          showGlobalLoading(true);
          const res = await fetch(`/api/users/${encodeURIComponent(uuid)}`, {
            method: 'DELETE',
            headers: authHeaders()
          });
          if (handleAuthStatus(res, 'You are not allowed to delete users.')) return;

          const js = await res.json().catch(() => ({}));
          if (!res.ok || js.success === false) throw new Error(js.error || js.message || 'Delete failed');

          ok('User deleted');
          await loadUsers(false);
        } catch (ex) {
          err(ex.message);
        } finally {
          setSpin(false);
          showGlobalLoading(false);
        }
      });
    }

    const toggle = btn.closest('.dropdown')?.querySelector('.dd-toggle');
    if (toggle) { try { bootstrap.Dropdown.getOrCreateInstance(toggle).hide(); } catch (_) {} }
  });

  // Add user
  btnAdd?.addEventListener('click', () => {
    if (!canCreate) return;
    resetForm();
    modalTitle.textContent = 'Add Admin';
    pwdReq.style.display = 'inline';
    pwdHelp.textContent = 'Enter password for new user';
    currentPwdRow.style.display = 'none';
    form.dataset.mode = 'edit';
    userModal.show();
  });

  // Image preview
  imageInput.addEventListener('input', () => {
    const url = imageInput.value.trim();
    if (!url) { imgPrev.style.display = 'none'; imgPrev.src = ''; return; }
    imgPrev.src = fixImageUrl(url) || url;
    imgPrev.style.display = 'block';
  });

  function resetForm() {
    form.reset();
    uuidInput.value = '';
    editingUserIdInput.value = '';
    imgPrev.src = '';
    imgPrev.style.display = 'none';
    saveBtn.style.display = '';
    Array.from(form.querySelectorAll('input,select,textarea')).forEach(el => {
      el.disabled = false;
      el.readOnly = false;
    });
    statusInput.value = 'active';
    if (deptInput) deptInput.value = '';
    if (roleInput) roleInput.value = 'admin';
    pwdReq.style.display = 'inline';
    pwdHelp.textContent = 'Enter password for new user';
    currentPwdRow.style.display = 'none';
    currentPwdInput.value = '';
    form.dataset.mode = 'edit';
  }

  async function openEdit(uuid, id, viewOnly = false) {
    showGlobalLoading(true);
    try {
      if (!state.departmentsLoaded) await loadDepartments(false);

      const res = await fetch(`/api/users/${encodeURIComponent(uuid)}`, { headers: authHeaders() });
      if (handleAuthStatus(res, 'You are not allowed to view this user.')) return;

      const js = await res.json().catch(() => ({}));
      if (!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to fetch user');

      const u = js.data || {};
      resetForm();

      const rr = (u.role || '').toLowerCase();

      let safeRole = 'admin';
      if (SUPER_ADMIN_GROUP.has(rr)) safeRole = 'super_admin';
      else if (DIRECTOR_GROUP.has(rr)) safeRole = 'director';
      else if (PRINCIPAL_GROUP.has(rr)) safeRole = 'principal';
      else if (ADMIN_GROUP.has(rr)) safeRole = 'admin';

      uuidInput.value = u.uuid || '';
      editingUserIdInput.value = u.id || '';
      nameInput.value = u.name || '';
      emailInput.value = u.email || '';
      phoneInput.value = u.phone_number || '';
      altEmailInput.value = u.alternative_email || '';
      altPhoneInput.value = u.alternative_phone_number || '';
      waInput.value = u.whatsapp_number || '';
      addrInput.value = u.address || '';
      if (roleInput) roleInput.value = safeRole;
      statusInput.value = u.status || 'active';

      if (deptInput) deptInput.value = (u.department_id !== undefined && u.department_id !== null) ? String(u.department_id) : '';

      imageInput.value = u.image || '';
      if (u.image) { imgPrev.src = fixImageUrl(u.image) || u.image; imgPrev.style.display = 'block'; }

      const isSelf = ACTOR.id && (parseInt(ACTOR.id, 10) === parseInt(u.id || 0, 10));
      currentPwdRow.style.display = (isSelf && !viewOnly) ? '' : 'none';

      modalTitle.textContent = viewOnly ? 'View Admin' : 'Edit Admin';
      saveBtn.style.display = viewOnly ? 'none' : '';

      Array.from(form.querySelectorAll('input,select,textarea')).forEach(el => {
        if (el.id === 'userUuid' || el.id === 'editingUserId') return;
        if (viewOnly) {
          if (el.tagName === 'SELECT') el.disabled = true;
          else el.readOnly = true;
        } else {
          el.disabled = false;
          el.readOnly = false;
        }
      });

      pwdReq.style.display = 'none';
      pwdHelp.textContent = 'Leave blank to keep current password';

      form.dataset.mode = viewOnly ? 'view' : 'edit';
      userModal.show();
    } catch (ex) {
      err(ex.message);
    } finally {
      showGlobalLoading(false);
    }
  }

  function setButtonLoading(button, loading) {
    if (!button) return;
    if (loading) { button.disabled = true; button.classList.add('btn-loading'); }
    else { button.disabled = false; button.classList.remove('btn-loading'); }
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (form.dataset.mode === 'view') return;
    if (!canEdit && !uuidInput.value) return;

    const isEdit = !!uuidInput.value;

    if (!nameInput.value.trim()) { nameInput.focus(); return; }
    if (!emailInput.value.trim()) { emailInput.focus(); return; }

    const roleVal = (roleInput?.value || '').toLowerCase();
    if (!ALLOWED.has(roleVal)) { err('Invalid role'); return; }

    if (!isEdit) {
      if (!pwdInput.value.trim()) { err('Password is required for new users'); pwdInput.focus(); return; }
      if (pwdInput.value.trim() !== pwd2Input.value.trim()) { err('Passwords do not match'); pwd2Input.focus(); return; }
    } else {
      if (pwdInput.value.trim() && pwdInput.value.trim() !== pwd2Input.value.trim()) { err('Passwords do not match'); pwd2Input.focus(); return; }
    }

    const payload = {};
    payload.name = nameInput.value.trim();
    payload.email = emailInput.value.trim();
    if (phoneInput.value.trim()) payload.phone_number = phoneInput.value.trim();
    if (altEmailInput.value.trim()) payload.alternative_email = altEmailInput.value.trim();
    if (altPhoneInput.value.trim()) payload.alternative_phone_number = altPhoneInput.value.trim();
    if (waInput.value.trim()) payload.whatsapp_number = waInput.value.trim();
    if (addrInput.value.trim()) payload.address = addrInput.value.trim();

    payload.role = roleVal;

    if (statusInput.value) payload.status = statusInput.value;
    if (imageInput.value.trim()) payload.image = imageInput.value.trim();

    if (deptInput) {
      const depVal = (deptInput.value || '').toString().trim();
      payload.department_id = depVal ? (parseInt(depVal, 10) || null) : null;
    }

    if (!isEdit) payload.password = pwdInput.value.trim();

    const url = isEdit ? `/api/users/${encodeURIComponent(uuidInput.value)}` : '/api/users';
    const method = isEdit ? 'PUT' : 'POST';

    try {
      setButtonLoading(saveBtn, true);
      showGlobalLoading(true);

      const res = await fetch(url, {
        method,
        headers: { ...authHeaders({ 'Content-Type': 'application/json' }) },
        body: JSON.stringify(payload)
      });
      if (handleAuthStatus(res, 'You are not allowed to save users.')) return;

      const js = await res.json().catch(() => ({}));
      if (!res.ok || js.success === false) {
        let msg = js.error || js.message || 'Save failed';
        if (js.errors) {
          const k = Object.keys(js.errors)[0];
          if (k && js.errors[k] && js.errors[k][0]) msg = js.errors[k][0];
        }
        throw new Error(msg);
      }

      if (isEdit && pwdInput.value.trim()) {
        const pwPayload = { password: pwdInput.value.trim(), password_confirmation: pwd2Input.value.trim() };
        const isSelf = ACTOR.id && (parseInt(ACTOR.id, 10) === parseInt(editingUserIdInput.value || '0', 10));
        if (isSelf) {
          if (!currentPwdInput.value.trim()) throw new Error('Current password is required to change your own password');
          pwPayload.current_password = currentPwdInput.value.trim();
        }

        const res2 = await fetch(`/api/users/${encodeURIComponent(uuidInput.value)}/password`, {
          method: 'PATCH',
          headers: { ...authHeaders({ 'Content-Type': 'application/json' }) },
          body: JSON.stringify(pwPayload)
        });
        if (handleAuthStatus(res2, 'You are not allowed to change passwords.')) return;

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

      userModal.hide();
      ok(isEdit ? 'User updated' : 'User created');
      await loadUsers(false);
    } catch (ex) {
      err(ex.message);
    } finally {
      setButtonLoading(saveBtn, false);
      showGlobalLoading(false);
    }
  });

  document.querySelector('a[href="#tab-active"]')?.addEventListener('shown.bs.tab', () => recomputeAndRender());
  document.querySelector('a[href="#tab-inactive"]')?.addEventListener('shown.bs.tab', () => recomputeAndRender());

  // Init
  (async () => {
    showGlobalLoading(true);
    await fetchMe();
    await loadDepartments(false);
    await loadUsers(false);
    showGlobalLoading(false);
  })();
});
</script>
@endpush
