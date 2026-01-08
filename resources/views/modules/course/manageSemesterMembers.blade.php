{{-- resources/views/modules/course/manageCourseSemesterMembers.blade.php --}}
@section('title','Semester Members')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Semester Members (Assign) – Clean, User-Friendly UI
 * ========================= */

.smm-wrap{max-width:1140px;margin:16px auto 44px;padding:0 4px;overflow:visible}

/* Toolbar panel */
.smm-toolbar.panel{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:16px;
  box-shadow:var(--shadow-2);
  padding:12px;
}

/* Cards */
.smm-card{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.smm-card .card-header{
  background:transparent;
  border-bottom:1px solid var(--line-soft);
}

/* Loading overlay */
.loading-overlay{
  position:fixed; inset:0;
  background:rgba(0,0,0,.45);
  display:none;
  justify-content:center;
  align-items:center;
  z-index:9999;
  backdrop-filter:blur(2px);
}
.loading-overlay.is-show{display:flex}
.loading-spinner{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,.3);
}
.spinner{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,.3);
  border-top:4px solid var(--primary-color);
  animation:spin 1s linear infinite;
}
@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

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

/* Improved Accordion */
.accordion-item{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:var(--surface);
  margin-bottom:10px;
}
.accordion-item:last-child{margin-bottom:0}
.accordion-button{
  background:var(--surface);
  color:var(--ink);
  font-weight:700;
  padding:14px 16px;
  font-size:15px;
}
.accordion-button:not(.collapsed){
  background:color-mix(in oklab, var(--primary-color) 8%, var(--surface));
  color:var(--ink);
  border-bottom:1px solid var(--line-soft);
}
.accordion-button:focus{
  box-shadow:0 0 0 .2rem rgba(201,75,80,.35);
}
.accordion-button::after{
  background-size:16px;
}
.accordion-body{
  background:var(--surface);
  padding:0;
}

/* Semester Groups */
.semester-group{
  border-bottom:1px solid var(--line-soft);
  padding:16px;
}
.semester-group:last-child{
  border-bottom:none;
}
.semester-header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  margin-bottom:16px;
  flex-wrap:wrap;
}
.semester-title{
  font-weight:800;
  font-size:16px;
  color:var(--ink);
  display:flex;
  align-items:center;
  gap:8px;
}
.semester-title i{
  color:var(--primary-color);
}
.semester-meta{
  font-size:13px;
  color:var(--muted-color);
  display:flex;
  align-items:center;
  gap:12px;
}

/* Section Cards */
.section-card{
  border:1px solid var(--line-soft);
  border-radius:12px;
  background:var(--surface);
  overflow:hidden;
  margin-bottom:16px;
}
.section-card:last-child{
  margin-bottom:0;
}
.section-header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding:12px 16px;
  border-bottom:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  flex-wrap:wrap;
}
.section-title{
  font-weight:700;
  font-size:14px;
  color:var(--ink);
}
.section-actions{
  display:flex;
  align-items:center;
  gap:8px;
}

/* Member Table */
.members-table-wrapper{
  overflow-x:auto;
  max-height:320px;
}
.members-table{
  width:100%;
  margin:0;
  border-collapse:separate;
  border-spacing:0;
  font-size:13px;
}
.members-table thead th{
  position:sticky;
  top:0;
  z-index:2;
  background:var(--surface);
  border-bottom:1px solid var(--line-soft);
  color:var(--muted-color);
  font-weight:800;
  padding:12px 16px;
  white-space:nowrap;
}
.members-table tbody td{
  padding:12px 16px;
  border-top:1px solid var(--line-soft);
  vertical-align:middle;
}
.members-table tbody tr:hover{
  background:var(--page-hover);
}

/* User Identity Column */
.user-identity{
  display:flex;
  align-items:center;
  gap:12px;
}
.user-avatar{
  width:36px;
  height:36px;
  border-radius:10px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  display:flex;
  align-items:center;
  justify-content:center;
  color:var(--muted-color);
  font-size:14px;
  flex-shrink:0;
}
.user-details{
  min-width:0;
}
.user-name{
  font-weight:700;
  color:var(--ink);
  margin-bottom:2px;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
  max-width:200px;
}
.user-info{
  font-size:12px;
  color:var(--muted-color);
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
  max-width:200px;
}

/* Role Badge */
.role-badge{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:6px 12px;
  border-radius:999px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  font-weight:700;
  font-size:12px;
  color:var(--ink);
  white-space:nowrap;
}

/* Empty State */
.empty-state{
  text-align:center;
  padding:40px 20px;
}
.empty-state i{
  font-size:48px;
  color:var(--muted-color);
  margin-bottom:16px;
  opacity:0.6;
}
.empty-state .title{
  font-weight:700;
  color:var(--ink);
  margin-bottom:8px;
}
.empty-state .subtitle{
  font-size:14px;
  color:var(--muted-color);
}

/* Filters */
.section-filters{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding:12px 16px;
  border-bottom:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  flex-wrap:wrap;
}
.filter-left{
  display:flex;
  align-items:center;
  gap:12px;
  flex-wrap:wrap;
}
.filter-right{
  display:flex;
  align-items:center;
  gap:8px;
}
.search-box{
  position:relative;
  min-width:200px;
}
.search-box input{
  padding-left:36px;
}
.search-box i{
  position:absolute;
  left:12px;
  top:50%;
  transform:translateY(-50%);
  color:var(--muted-color);
}
.count-badge{
  display:inline-flex;
  align-items:center;
  gap:4px;
  padding:4px 10px;
  border-radius:999px;
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  color:var(--primary-color);
  font-weight:700;
  font-size:12px;
}

/* Modal Styles */
.modal-user-list{
  max-height:400px;
  overflow-y:auto;
}
.modal-user-item{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:16px;
  padding:12px 16px;
  border-bottom:1px solid var(--line-soft);
}
.modal-user-item:last-child{
  border-bottom:none;
}
.modal-user-item:hover{
  background:var(--page-hover);
}
.modal-user-info{
  display:flex;
  align-items:center;
  gap:12px;
  min-width:0;
}
.modal-user-avatar{
  width:40px;
  height:40px;
  border-radius:10px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  display:flex;
  align-items:center;
  justify-content:center;
  color:var(--muted-color);
  flex-shrink:0;
}
.modal-user-details{
  min-width:0;
}
.modal-user-name{
  font-weight:700;
  color:var(--ink);
  margin-bottom:2px;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.modal-user-email{
  font-size:12px;
  color:var(--muted-color);
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.form-switch .form-check-input{
  cursor:pointer;
}

/* Responsive */
@media (max-width: 768px){
  .smm-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .smm-toolbar .position-relative{min-width:100% !important}
  .section-header{flex-direction:column;align-items:stretch;gap:12px}
  .section-actions{justify-content:flex-start}
  .semester-header{flex-direction:column;align-items:stretch;gap:12px}
  .semester-meta{justify-content:flex-start}
  .filter-left, .filter-right{justify-content:flex-start}
  .search-box{min-width:100%}
  .members-table thead th,
  .members-table tbody td{padding:10px 12px}
}
</style>
@endpush

@section('content')
<div class="smm-wrap">

  {{-- Loading Overlay --}}
  <div id="globalLoading" class="loading-overlay">
    <div class="loading-spinner">
      <div class="spinner"></div>
      <div class="text-muted small">Loading…</div>
    </div>
  </div>

  {{-- Toolbar --}}
  <div class="row align-items-center g-2 mb-3 smm-toolbar panel">
    <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">
      <div class="search-box" style="min-width:320px;">
        <input id="searchGroups" type="search" class="form-control" placeholder="Search courses, semesters, or sections…">
        <i class="fa fa-search"></i>
      </div>

      <button id="btnRefresh" class="btn btn-light">
        <i class="fa fa-rotate me-1"></i>Refresh
      </button>
    </div>

    <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
      <div id="writeControls" style="display:none;">
        <button type="button" class="btn btn-primary" id="btnAssign">
          <i class="fa fa-user-plus me-1"></i> Assign Members
        </button>
      </div>
    </div>
  </div>

  {{-- Main Card --}}
  <div class="card smm-card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="fw-semibold">
        <i class="fa fa-users me-2"></i>Semester Members
      </div>
      <div class="small text-muted" id="summaryText">—</div>
    </div>

    <div class="card-body p-0">
      <div id="emptyState" class="empty-state" style="display:none;">
        <i class="fa fa-users"></i>
        <div class="title">No Member Assignments Found</div>
        <div class="subtitle">Click "Assign Members" to get started</div>
      </div>

      <div class="accordion" id="coursesAccordion">
        <!-- Accordion items will be inserted here by JavaScript -->
      </div>
    </div>
  </div>

</div>

{{-- Assign Members Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" id="assignForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-user-plus me-2"></i><span id="assignModalTitle">Assign Members</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="ctxKey" value="">

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label">Course</label>
            <select id="course_id" class="form-select">
              <option value="">Select Course (Optional)</option>
            </select>
            <div class="form-text small">Filters available semesters</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Semester <span class="text-danger">*</span></label>
            <select id="semester_id" class="form-select" required>
              <option value="">Select Semester</option>
            </select>
            <div class="form-text small">Required for member assignment</div>
          </div>

          <div class="col-md-12">
            <label class="form-label">Section</label>
            <select id="section_id" class="form-select">
              <option value="">All Sections (Optional)</option>
            </select>
            <div class="form-text small">Assign to specific section or all sections</div>
          </div>
        </div>

        {{-- User Selection --}}
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <div class="fw-semibold">
              <i class="fa fa-list-check me-2"></i>Select Users
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="count-badge" id="selectedCount">0 selected</span>
              <button type="button" class="btn btn-light btn-sm" id="btnSelectAll">
                <i class="fa fa-check-double me-1"></i>Select All
              </button>
              <button type="button" class="btn btn-light btn-sm" id="btnClearAll">
                <i class="fa fa-xmark me-1"></i>Clear All
              </button>
            </div>
          </div>
          
          <div class="card-body p-0">
            <div class="p-3 border-bottom">
              <div class="search-box">
                <input id="userSearch" type="search" class="form-control" placeholder="Search users by name, email, or role…">
                <i class="fa fa-search"></i>
              </div>
            </div>
            
            <div class="modal-user-list" id="usersList">
              <div class="text-center text-muted p-5">
                <i class="fa fa-users mb-3" style="font-size:32px;opacity:.6;"></i>
                <div>Select a semester to view users</div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="saveAssignBtn">
          <i class="fa fa-floppy-disk me-1"></i> Save Assignments
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
  if (window.__SEMESTER_MEMBERS_ASSIGN_PAGE__) return;
  window.__SEMESTER_MEMBERS_ASSIGN_PAGE__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=320) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  // =========================
  // API Map
  // =========================
  const API = {
    me:           () => '/api/users/me',
    users:        () => '/api/users',
    courses:      () => '/api/courses',
    semesters:    () => '/api/course-semesters',
    sections:     () => '/api/course-semester-sections',
    membersIndex: () => '/api/semester-members',
    bulkImport:   () => '/api/semester-members/bulk-import',
  };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  async function fetchWithTimeout(url, opts={}, ms=15000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try{ return await fetch(url, { ...opts, signal: ctrl.signal }); }
    finally{ clearTimeout(t); }
  }

  function showLoading(on){
    const el = $('globalLoading');
    if (!el) return;
    el.classList.toggle('is-show', !!on);
  }

  // Toasts
  const toastOk = $('toastSuccess') ? new bootstrap.Toast($('toastSuccess')) : null;
  const toastErr = $('toastError') ? new bootstrap.Toast($('toastError')) : null;
  const ok = (m) => { $('toastSuccessText').textContent = m || 'Done'; toastOk && toastOk.show(); };
  const err = (m) => { $('toastErrorText').textContent = m || 'Something went wrong'; toastErr && toastErr.show(); };

  // =========================
  // State
  // =========================
  const ACTOR = { role:'' };
  let canWrite = false;

  const state = {
    courses: [],
    semesters: [],
    sections: [],
    users: [],
    // key => { course_id, semester_id, section_id, user_ids:Set<number> }
    assigned: new Map(),
    q: '',
  };

  // =========================
  // Permissions
  // =========================
  function computePermissions(){
    const r = (ACTOR.role || '').toLowerCase();
    const writeRoles = ['admin','director','principal','hod','faculty','technical_assistant','it_person'];
    canWrite = writeRoles.includes(r);
    $('writeControls').style.display = canWrite ? 'flex' : 'none';
  }

  function authHeaders(token, extra={}){
    return Object.assign({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    }, extra);
  }

  async function fetchMe(token){
    try{
      const res = await fetchWithTimeout(API.me(), { headers: authHeaders(token) }, 8000);
      if (res.ok){
        const js = await res.json().catch(()=> ({}));
        const role = js?.data?.role || js?.role || '';
        ACTOR.role = String(role || '').toLowerCase();
      }
    }catch(_){}
    if (!ACTOR.role){
      ACTOR.role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
    }
    computePermissions();
  }

  // =========================
  // Normalizers
  // =========================
  function idNum(v){
    const n = parseInt(String(v ?? '').trim(), 10);
    return Number.isFinite(n) ? n : null;
  }

  function buildKey(course_id, semester_id, section_id){
    const c = course_id ? String(course_id) : '0';
    const s = semester_id ? String(semester_id) : '0';
    const sec = section_id ? String(section_id) : '0';
    return `${c}|${s}|${sec}`;
  }

  function labelCourse(c){
    return (c?.title || c?.name || c?.course_title || `Course #${c?.id}` || 'Course').toString();
  }
  
  function labelSemester(s){
    const t = (s?.title || s?.semester_title || `Semester #${s?.id}` || 'Semester').toString();
    const no = (s?.semester_no ?? s?.no ?? '') ? ` (Semester ${s.semester_no ?? s.no})` : '';
    return `${t}${no}`;
  }
  
  function labelSection(sec){
    return (sec?.title || sec?.section_title || `Section #${sec?.id}` || 'All Sections').toString();
  }

  function getCourseById(id){ return state.courses.find(x => String(x?.id) === String(id)) || null; }
  function getSemesterById(id){ return state.semesters.find(x => String(x?.id) === String(id)) || null; }
  function getSectionById(id){ return state.sections.find(x => String(x?.id) === String(id)) || null; }
  function getUserById(id){ return state.users.find(u => String(u?.id) === String(id)) || null; }
  function userRole(u){ return String(u?.role || '').trim(); }

  // =========================
  // Load reference data
  // =========================
  async function loadCourses(token){
    try{
      const res = await fetchWithTimeout(API.courses(), { headers: authHeaders(token) }, 15000);
      const js = await res.json().catch(()=> ({}));
      if (!res.ok) return;
      state.courses = Array.isArray(js.data) ? js.data : (Array.isArray(js) ? js : []);
    }catch(_){}
  }

  async function loadSemesters(token){
    try{
      const url = `${API.semesters()}?per_page=500&page=1&sort=updated_at&direction=desc`;
      const res = await fetchWithTimeout(url, { headers: authHeaders(token) }, 15000);
      const js = await res.json().catch(()=> ({}));
      if (!res.ok) return;
      state.semesters = Array.isArray(js.data) ? js.data : (Array.isArray(js) ? js : []);
    }catch(_){}
  }

  async function loadSections(token){
    try{
      const url = `${API.sections()}?per_page=500&page=1&sort=updated_at&direction=desc`;
      const res = await fetchWithTimeout(url, { headers: authHeaders(token) }, 20000);
      const js = await res.json().catch(()=> ({}));
      if (!res.ok) return;
      state.sections = Array.isArray(js.data) ? js.data : (Array.isArray(js) ? js : []);
    }catch(_){}
  }

  async function loadUsers(token){
    try{
      const res = await fetchWithTimeout(API.users(), { headers: authHeaders(token) }, 20000);
      const js = await res.json().catch(()=> ({}));
      if (!res.ok) return;
      const arr = Array.isArray(js.data) ? js.data : [];
      state.users = arr.filter(u => String(u?.status || 'active').toLowerCase() !== 'inactive');
    }catch(_){}
  }

  // =========================
  // Assignments
  // =========================
  function normalizeAssignments(payload){
    const rows = Array.isArray(payload?.data) ? payload.data : (Array.isArray(payload) ? payload : []);
    const map = new Map();

    rows.forEach(r => {
      const course_id = idNum(r?.course_id ?? r?.course?.id) || null;
      const semester_id = idNum(r?.semester_id ?? r?.semester?.id) || null;
      const section_id = idNum(r?.section_id ?? r?.section?.id) || null;
      if (!semester_id) return;

      const key = buildKey(course_id, semester_id, section_id);
      if (!map.has(key)){
        map.set(key, { course_id, semester_id, section_id, user_ids: new Set() });
      }
      const entry = map.get(key);

      const uid = idNum(r?.user_id ?? r?.user?.id);
      if (uid) entry.user_ids.add(uid);

      const userIdsArr = Array.isArray(r?.user_ids) ? r.user_ids : null;
      if (userIdsArr) userIdsArr.forEach(x => { const n=idNum(x); if(n) entry.user_ids.add(n); });

      const usersArr = Array.isArray(r?.users) ? r.users : null;
      if (usersArr) usersArr.forEach(u => { const n=idNum(u?.id); if(n) entry.user_ids.add(n); });
    });

    state.assigned = map;
  }

  async function loadAssignments(token){
    try{
      const res = await fetchWithTimeout(API.membersIndex(), { headers: authHeaders(token) }, 20000);
      const js = await res.json().catch(()=> ({}));
      if (!res.ok) throw new Error(js?.message || 'Failed to load assignments');
      normalizeAssignments(js);
    }catch(e){
      state.assigned = new Map();
    }
  }

  // =========================
  // Render - Clean Accordion Structure
  // =========================
  function matchesSearch(text){
    const q = (state.q || '').trim().toLowerCase();
    if (!q) return true;
    return String(text || '').toLowerCase().includes(q);
  }

  function summarize(){
    let groups = 0;
    let members = 0;
    state.assigned.forEach(v => {
      groups++;
      members += v.user_ids.size;
    });
    $('summaryText').textContent = groups ? `${groups} groups • ${members} total members` : 'No assignments yet';
  }

  function getRoleColor(role){
    const roles = {
      'admin': 'danger',
      'teacher': 'primary',
      'student': 'success',
      'faculty': 'warning',
      'staff': 'info'
    };
    const r = (role || '').toLowerCase();
    return roles[r] || 'muted';
  }

  function renderMemberTable(userIds){
    const users = userIds
      .map(uid => getUserById(uid))
      .filter(u => u)
      .sort((a,b) => String(a.name).localeCompare(String(b.name)));

    if (!users.length) {
      return `
        <div class="text-center text-muted p-4">
          <i class="fa fa-users mb-2" style="font-size:24px;opacity:.6;"></i>
          <div>No members assigned to this section</div>
        </div>
      `;
    }

    return `
      <div class="members-table-wrapper">
        <table class="members-table">
          <thead>
            <tr>
              <th style="min-width:220px;">Member</th>
              <th style="min-width:120px;">Role</th>
              <th style="min-width:120px;">Contact</th>
              <th style="min-width:80px;">ID</th>
            </tr>
          </thead>
          <tbody>
            ${users.map(u => {
              const roleColor = getRoleColor(u.role);
              return `
                <tr>
                  <td>
                    <div class="user-identity">
                      <div class="user-avatar">
                        ${u.name ? u.name.charAt(0).toUpperCase() : 'U'}
                      </div>
                      <div class="user-details">
                        <div class="user-name">${esc(u.name || 'Unnamed User')}</div>
                        <div class="user-info">${esc(u.email || 'No email')}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge badge-soft-${roleColor}">
                      ${esc(u.role || '—')}
                    </span>
                  </td>
                  <td>
                    <div class="text-muted small">
                      ${esc(u.email || '—')}
                      ${u.phone_number ? `<br>${esc(u.phone_number)}` : ''}
                    </div>
                  </td>
                  <td>
                    <span class="text-muted mono">#${esc(String(u.id))}</span>
                  </td>
                </tr>
              `;
            }).join('')}
          </tbody>
        </table>
      </div>
    `;
  }

  function renderAccordion(){
    const root = $('coursesAccordion');
    const empty = $('emptyState');
    if (!root) return;

    summarize();

    // Build course-based structure
    const courseMap = new Map();
    const showEmpty = state.assigned.size === 0;

    if (showEmpty){
      root.innerHTML = '';
      empty.style.display = '';
      return;
    }
    
    empty.style.display = 'none';

    // Organize data by course
    state.assigned.forEach((group, key) => {
      const course = group.course_id ? getCourseById(group.course_id) : null;
      const courseId = course?.id || 0;
      const courseName = course ? labelCourse(course) : 'Unassigned Courses';
      
      if (!courseMap.has(courseId)) {
        courseMap.set(courseId, {
          course,
          courseName,
          semesters: new Map()
        });
      }
      
      const courseData = courseMap.get(courseId);
      const semester = group.semester_id ? getSemesterById(group.semester_id) : null;
      const semesterId = semester?.id || 0;
      const semesterName = semester ? labelSemester(semester) : `Semester #${group.semester_id}`;
      
      if (!courseData.semesters.has(semesterId)) {
        courseData.semesters.set(semesterId, {
          semester,
          semesterName,
          sections: []
        });
      }
      
      const semesterData = courseData.semesters.get(semesterId);
      const section = group.section_id ? getSectionById(group.section_id) : null;
      const sectionName = labelSection(section);
      
      semesterData.sections.push({
        key,
        section,
        sectionName,
        userIds: Array.from(group.user_ids)
      });
    });

    // Build accordion HTML
    let accordionIndex = 0;
    const accordionHTML = Array.from(courseMap.entries())
      .filter(([courseId, courseData]) => {
        // Apply search filter
        const courseText = courseData.courseName.toLowerCase();
        const semesterText = Array.from(courseData.semesters.values())
          .map(s => s.semesterName.toLowerCase())
          .join(' ');
        return matchesSearch(courseText + ' ' + semesterText);
      })
      .map(([courseId, courseData]) => {
        const accordionId = `courseAccordion${accordionIndex++}`;
        const collapseId = `courseCollapse${accordionIndex}`;
        
        // Build semesters for this course
        const semesterHTML = Array.from(courseData.semesters.entries())
          .map(([semesterId, semesterData]) => {
            const totalMembers = semesterData.sections.reduce((sum, section) => sum + section.userIds.length, 0);
            
            return `
              <div class="semester-group">
                <div class="semester-header">
                  <div class="semester-title">
                    <i class="fa fa-layer-group"></i>
                    ${esc(semesterData.semesterName)}
                  </div>
                  <div class="semester-meta">
                    <span class="count-badge">
                      <i class="fa fa-users"></i> ${totalMembers} members
                    </span>
                    <span class="text-muted">${semesterData.sections.length} section(s)</span>
                  </div>
                </div>
                
                ${semesterData.sections.map(section => {
                  const editBtn = canWrite ? `
                    <button type="button" class="btn btn-outline-primary btn-sm edit-section-btn" 
                      data-key="${esc(section.key)}">
                      <i class="fa fa-pen-to-square me-1"></i> Edit Members
                    </button>
                  ` : `
                    <button type="button" class="btn btn-light btn-sm" disabled>
                      <i class="fa fa-lock me-1"></i> Read Only
                    </button>
                  `;
                  
                  return `
                    <div class="section-card">
                      <div class="section-header">
                        <div class="section-title">
                          <i class="fa fa-folder me-2"></i>
                          ${esc(section.sectionName)}
                          <span class="badge badge-soft-primary ms-2">${section.userIds.length} members</span>
                        </div>
                        <div class="section-actions">
                          ${editBtn}
                        </div>
                      </div>
                      <div class="section-body">
                        ${renderMemberTable(section.userIds)}
                      </div>
                    </div>
                  `;
                }).join('')}
              </div>
            `;
          }).join('');

        return `
          <div class="accordion-item">
            <h2 class="accordion-header" id="${accordionId}">
              <button class="accordion-button ${accordionIndex === 1 ? '' : 'collapsed'}" 
                type="button" data-bs-toggle="collapse" 
                data-bs-target="#${collapseId}" 
                aria-expanded="${accordionIndex === 1 ? 'true' : 'false'}" 
                aria-controls="${collapseId}">
                <i class="fa fa-graduation-cap me-2"></i>
                ${esc(courseData.courseName)}
                <span class="badge badge-soft-primary ms-2">
                  ${courseData.semesters.size} semester(s)
                </span>
              </button>
            </h2>
            <div id="${collapseId}" 
              class="accordion-collapse collapse ${accordionIndex === 1 ? 'show' : ''}" 
              aria-labelledby="${accordionId}" 
              data-bs-parent="#coursesAccordion">
              <div class="accordion-body">
                ${semesterHTML || '<div class="text-muted p-4">No semesters found</div>'}
              </div>
            </div>
          </div>
        `;
      }).join('');

    root.innerHTML = accordionHTML || '<div class="text-muted p-4">No matching courses found</div>';
  }

  // =========================
  // Modal Functions
  // =========================
  function fillCourseSelect(){
    const sel = $('course_id');
    if (!sel) return;
    const opts = state.courses.map(c => {
      const id = c?.id;
      if (id === null || id === undefined) return '';
      return `<option value="${esc(String(id))}">${esc(labelCourse(c))}</option>`;
    }).join('');
    sel.innerHTML = `<option value="">Select Course (Optional)</option>${opts}`;
  }

  function semestersForCourse(courseId){
    const cid = String(courseId || '').trim();
    if (!cid) return state.semesters || [];
    return (state.semesters || []).filter(s => String(s?.course_id ?? s?.course?.id ?? '') === cid);
  }

  function sectionsForSemester(semesterId){
    const sid = String(semesterId || '').trim();
    if (!sid) return [];
    return (state.sections || []).filter(sec => String(sec?.semester_id ?? sec?.semester?.id ?? '') === sid);
  }

  function fillSemesterSelect(courseId, keep=''){
    const sel = $('semester_id');
    if (!sel) return;
    const rows = semestersForCourse(courseId);
    const opts = rows.map(s => `<option value="${esc(String(s.id))}">${esc(labelSemester(s))}</option>`).join('');
    sel.innerHTML = `<option value="">Select Semester</option>${opts}`;
    if (keep && rows.some(s => String(s.id) === String(keep))) sel.value = String(keep);
  }

  function fillSectionSelect(semesterId, keep=''){
    const sel = $('section_id');
    if (!sel) return;
    const rows = sectionsForSemester(semesterId);
    const opts = rows.map(sec => `<option value="${esc(String(sec.id))}">${esc(labelSection(sec))}</option>`).join('');
    sel.innerHTML = `<option value="">All Sections</option>${opts}`;
    if (keep && rows.some(sec => String(sec.id) === String(keep))) sel.value = String(keep);
  }

  function getAssignedSet(course_id, semester_id, section_id){
    const key = buildKey(course_id, semester_id, section_id);
    const entry = state.assigned.get(key);
    return entry ? new Set(entry.user_ids) : new Set();
  }

  function updateSelectedCount(){
    const box = $('selectedCount');
    if (!box) return;
    const checked = document.querySelectorAll('#usersList input[type="checkbox"][data-user-id]:checked').length;
    box.textContent = `${checked} selected`;
  }

  function renderUsersList(preCheckedSet){
    const list = $('usersList');
    if (!list) return;

    const semesterId = $('semester_id').value || '';
    if (!semesterId){
      list.innerHTML = `
        <div class="text-center text-muted p-5">
          <i class="fa fa-users mb-3" style="font-size:32px;opacity:.6;"></i>
          <div>Select a semester to view users</div>
        </div>
      `;
      updateSelectedCount();
      return;
    }

    const q = String($('userSearch').value || '').trim().toLowerCase();
    const users = (state.users || []).filter(u => {
      if (!q) return true;
      const hay = `${u?.name||''} ${u?.email||''} ${u?.phone_number||''} ${u?.slug||''} ${u?.role||''}`.toLowerCase();
      return hay.includes(q);
    });

    if (!users.length){
      list.innerHTML = `
        <div class="text-center text-muted p-5">
          <i class="fa fa-search mb-3" style="font-size:32px;opacity:.6;"></i>
          <div>No users found matching your search</div>
        </div>
      `;
      updateSelectedCount();
      return;
    }

    list.innerHTML = users.map(u => {
      const id = u?.id;
      const name = u?.name || '—';
      const email = u?.email || '';
      const role = (u?.role || '').toString();
      const initials = name.split(' ').map(n => n.charAt(0)).join('').toUpperCase().substring(0, 2);
      const on = preCheckedSet.has(parseInt(String(id), 10));

      return `
        <div class="modal-user-item">
          <div class="modal-user-info">
            <div class="modal-user-avatar">${initials}</div>
            <div class="modal-user-details">
              <div class="modal-user-name">${esc(name)}</div>
              <div class="modal-user-email">${esc(email || 'No email')} • ${esc(role || 'No role')}</div>
            </div>
          </div>
          <div class="form-check form-switch m-0">
            <input class="form-check-input" type="checkbox" data-user-id="${esc(String(id))}" ${on ? 'checked' : ''}>
          </div>
        </div>
      `;
    }).join('');

    updateSelectedCount();
  }

  let modalInitialSet = new Set();

  function openAssignModal(context){
    const modal = new bootstrap.Modal($('assignModal'));
    $('assignModalTitle').textContent = context?.semester_id ? 'Edit Members' : 'Assign Members';

    const cId = context?.course_id ? String(context.course_id) : '';
    const sId = context?.semester_id ? String(context.semester_id) : '';
    const secId = context?.section_id ? String(context.section_id) : '';

    $('course_id').value = cId || '';
    fillSemesterSelect(cId, sId);
    $('semester_id').value = sId || '';
    fillSectionSelect(sId, secId);
    $('section_id').value = secId || '';

    modalInitialSet = getAssignedSet(cId || null, sId || null, secId || null);

    $('userSearch').value = '';
    renderUsersList(modalInitialSet);
    $('ctxKey').value = buildKey(cId || null, sId || null, secId || null);

    modal.show();
  }

  // =========================
  // Save assignment
  // =========================
  async function saveAssignment(token){
    const semester_id = $('semester_id').value ? parseInt($('semester_id').value, 10) : null;
    const section_id = $('section_id').value ? parseInt($('section_id').value, 10) : null;

    if (!semester_id){
      err('Semester is required');
      $('semester_id').focus();
      return;
    }

    const checked = Array.from(document.querySelectorAll('#usersList input[type="checkbox"]:checked'))
      .map(x => parseInt(x.dataset.userId, 10))
      .filter(n => Number.isFinite(n));

    const currentSet = new Set(checked);
    const add_user_ids = [];
    const remove_user_ids = [];

    modalInitialSet.forEach(id => { if (!currentSet.has(id)) remove_user_ids.push(id); });
    currentSet.forEach(id => { if (!modalInitialSet.has(id)) add_user_ids.push(id); });

    const payload = {
      course_semester_id: semester_id,
      section_id,
      user_ids: checked,
      add_user_ids,
      remove_user_ids,
      sync: true,
      replace: true,
      detach_missing: true,
    };

    showLoading(true);
    try{
      const res = await fetchWithTimeout(API.bulkImport(), {
        method: 'POST',
        headers: authHeaders(token, { 'Content-Type':'application/json' }),
        body: JSON.stringify(payload)
      }, 25000);

      const js = await res.json().catch(()=> ({}));
      if (res.status === 401){ window.location.href = '/'; return; }
      if (!res.ok || js.success === false) throw new Error(js?.message || js?.error || 'Save failed');

      ok('Members updated successfully');

      await loadAssignments(token);
      renderAccordion();

      bootstrap.Modal.getInstance($('assignModal'))?.hide();
    }catch(e){
      err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Save failed'));
    }finally{
      showLoading(false);
    }
  }

  // =========================
  // Event Listeners
  // =========================
  document.addEventListener('DOMContentLoaded', async () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token){ window.location.href = '/'; return; }

    // Course change
    $('course_id').addEventListener('change', () => {
      fillSemesterSelect($('course_id').value, '');
      fillSectionSelect('', '');
      modalInitialSet = new Set();
      renderUsersList(modalInitialSet);
    });

    // Semester change
    $('semester_id').addEventListener('change', () => {
      const cId = $('course_id').value || null;
      const sId = $('semester_id').value || null;

      // Auto-select course if not set
      if (!cId && sId){
        const sem = getSemesterById(parseInt(sId, 10));
        const autoCid = sem?.course_id ?? sem?.course?.id ?? null;
        if (autoCid){
          $('course_id').value = String(autoCid);
          fillSemesterSelect(String(autoCid), String(sId));
        }
      }

      fillSectionSelect($('semester_id').value, '');

      const cid = $('course_id').value || null;
      const sid = $('semester_id').value || null;
      const secid = $('section_id').value || null;

      modalInitialSet = getAssignedSet(cid ? parseInt(cid,10) : null, sid ? parseInt(sid,10) : null, secid ? parseInt(secid,10) : null);
      $('userSearch').value = '';
      renderUsersList(modalInitialSet);
    });

    // Section change
    $('section_id').addEventListener('change', () => {
      const cid = $('course_id').value || null;
      const sid = $('semester_id').value || null;
      const secid = $('section_id').value || null;

      modalInitialSet = getAssignedSet(cid ? parseInt(cid,10) : null, sid ? parseInt(sid,10) : null, secid ? parseInt(secid,10) : null);
      $('userSearch').value = '';
      renderUsersList(modalInitialSet);
    });

    // User search
    $('userSearch').addEventListener('input', debounce(() => {
      renderUsersList(modalInitialSet);
    }, 220));

    // Checkbox change
    document.addEventListener('change', (e) => {
      if (e.target.matches('#usersList input[type="checkbox"]')) {
        updateSelectedCount();
      }
    });

    // Select all users
    $('btnSelectAll').addEventListener('click', () => {
      document.querySelectorAll('#usersList input[type="checkbox"]').forEach(cb => cb.checked = true);
      updateSelectedCount();
    });

    // Clear all selections
    $('btnClearAll').addEventListener('click', () => {
      document.querySelectorAll('#usersList input[type="checkbox"]').forEach(cb => cb.checked = false);
      updateSelectedCount();
    });

    // Assign new members
    $('btnAssign').addEventListener('click', () => {
      if (!canWrite) return;
      openAssignModal({ course_id:null, semester_id:null, section_id:null });
    });

    // Edit section members
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.edit-section-btn');
      if (!btn || !canWrite) return;

      const key = btn.dataset.key || '';
      const [c,s,sec] = key.split('|');
      const course_id = (c && c !== '0') ? parseInt(c,10) : null;
      const semester_id = (s && s !== '0') ? parseInt(s,10) : null;
      const section_id = (sec && sec !== '0') ? parseInt(sec,10) : null;

      openAssignModal({ course_id, semester_id, section_id });
    });

    // Save assignments
    $('assignForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!canWrite) return;
      await saveAssignment(token);
    });

    // Search groups
    $('searchGroups').addEventListener('input', debounce(() => {
      state.q = ($('searchGroups').value || '').trim();
      renderAccordion();
    }, 250));

    // Refresh button
    $('btnRefresh').addEventListener('click', async () => {
      showLoading(true);
      try{
        await loadAssignments(token);
        renderAccordion();
        ok('Data refreshed successfully');
      } finally {
        showLoading(false);
      }
    });

    // Initialize
    showLoading(true);
    try{
      await fetchMe(token);
      await Promise.all([
        loadCourses(token),
        loadSemesters(token),
        loadSections(token),
        loadUsers(token),
        loadAssignments(token),
      ]);

      fillCourseSelect();
      fillSemesterSelect('', '');
      fillSectionSelect('', '');

      renderAccordion();
    }catch(ex){
      err(ex?.message || 'Initialization failed');
      $('emptyState').style.display = '';
    }finally{
      showLoading(false);
    }
  });

})();
</script>
@endpush