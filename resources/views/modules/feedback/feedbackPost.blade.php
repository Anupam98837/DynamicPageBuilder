{{-- resources/views/modules/feedbackPosts/manageFeedbackPosts.blade.php --}}
@section('title','Feedback Post')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Feedback Post – Assign UI (Questions + Faculty + Students)
 * + Course/Sem/Subject/Section dependency
 * + Question picker filters by group (but shows individual questions)
 * + Edit mode if URL has uuid/id/post
 * ========================= */

.fbp-wrap{max-width:1140px;margin:16px auto 44px;padding:0 4px;overflow:visible}

/* Panels / Cards */
.fbp-panel{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:16px;
  box-shadow:var(--shadow-2);
  padding:12px;
}
.fbp-card{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.fbp-card .card-header{
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

/* Count badge + chips */
.count-badge{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:6px 12px;
  border-radius:999px;
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  color:var(--primary-color);
  font-weight:800;
  font-size:12px;
  white-space:nowrap;
}
.chip-row{display:flex;flex-wrap:wrap;gap:8px}
.chip{
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 10px;border-radius:999px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  font-weight:700;font-size:12px;color:var(--ink);
}
.chip .x{
  border:none;background:transparent;color:var(--muted-color);
  cursor:pointer; padding:0; line-height:1;
}
.chip .x:hover{color:var(--danger-color)}

/* Modal list */
.modal-user-list{max-height:420px;overflow-y:auto}
.modal-user-item{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:16px;
  padding:12px 16px;
  border-bottom:1px solid var(--line-soft);
}
.modal-user-item:last-child{border-bottom:none}
.modal-user-item:hover{background:var(--page-hover)}
.modal-user-info{
  display:flex;align-items:center;gap:12px;min-width:0;
}
.modal-user-avatar{
  width:40px;height:40px;border-radius:10px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  display:flex;align-items:center;justify-content:center;
  color:var(--muted-color);flex-shrink:0;
  font-weight:900;
}
.modal-user-details{min-width:0}
.modal-user-name{
  font-weight:800;color:var(--ink);
  margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.modal-user-email{
  font-size:12px;color:var(--muted-color);
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.form-switch .form-check-input{cursor:pointer}

/* Search box */
.search-box{position:relative}
.search-box input{padding-left:36px}
.search-box i{
  position:absolute;left:12px;top:50%;
  transform:translateY(-50%);
  color:var(--muted-color);
}

/* Picker filters row */
.picker-filters{
  display:flex;
  align-items:center;
  gap:10px;
  flex-wrap:wrap;
}
.picker-filters .form-select{
  min-width:220px;
}

/* Attendance badge inside modal (optional UI helper) */
.att-badge{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:3px 10px;
  border-radius:999px;
  font-size:12px;
  font-weight:800;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 88%, transparent);
  color:var(--ink);
  white-space:nowrap;
}
.att-badge.good{
  background:color-mix(in oklab, #22c55e 16%, transparent);
  color:#1b7f3b;
  border-color:color-mix(in oklab, #22c55e 28%, var(--line-soft));
}
.att-badge.warn{
  background:color-mix(in oklab, #f59e0b 16%, transparent);
  color:#875300;
  border-color:color-mix(in oklab, #f59e0b 28%, var(--line-soft));
}
.att-badge.bad{
  background:color-mix(in oklab, #ef4444 16%, transparent);
  color:#8a1f1f;
  border-color:color-mix(in oklab, #ef4444 28%, var(--line-soft));
}

/* Responsive */
@media (max-width: 768px){
  .fbp-panel .d-flex{flex-direction:column;gap:12px !important}
  .picker-filters .form-select{min-width:100%}
}
</style>
@endpush

@section('content')
<div class="fbp-wrap">

  {{-- Loading Overlay --}}
  <div id="globalLoading" class="loading-overlay">
    <div class="loading-spinner">
      <div class="spinner"></div>
      <div class="text-muted small">Loading…</div>
    </div>
  </div>

  {{-- Toolbar --}}
  <div class="fbp-panel mb-3">
    <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between">
      <div class="d-flex align-items-center flex-wrap gap-2">
        <div class="fw-semibold">
          <i class="fa fa-clipboard-list me-2"></i>Feedback Post
        </div>
        <span class="count-badge" id="modeBadge">Create</span>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button id="btnRefresh" class="btn btn-light">
          <i class="fa fa-rotate me-1"></i>Refresh
        </button>
        <button id="btnSave" class="btn btn-primary">
          <i class="fa fa-floppy-disk me-1"></i>Save
        </button>
      </div>
    </div>
    <div class="small text-muted mt-2" id="summaryText">—</div>
  </div>

  {{-- Form Card --}}
  <div class="card fbp-card mb-3">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="fw-semibold"><i class="fa fa-pen-to-square me-2"></i>Basic Details</div>
      <div class="small text-muted">Fill details → set scope → select questions → select faculty → select students</div>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Title <span class="text-danger">*</span></label>
          <input id="title" type="text" class="form-control" placeholder="e.g., Semester Feedback Form" required>
        </div>

        <div class="col-md-3">
          <label class="form-label">Publish At</label>
          <input id="publish_at" type="datetime-local" class="form-control">
        </div>

        <div class="col-md-3">
          <label class="form-label">Expire At</label>
          <input id="expire_at" type="datetime-local" class="form-control">
        </div>

        <div class="col-md-12">
          <label class="form-label">Description (optional)</label>
          <textarea id="description" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
        </div>
      </div>

      <hr class="my-4">

      {{-- Dependency scope --}}
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Course (optional)</label>
          <select id="course_id" class="form-select">
            <option value="">— Select Course —</option>
          </select>
          <div class="form-text">Selecting a course will load semesters.</div>
        </div>

        <div class="col-md-3">
          <label class="form-label">Semester (optional)</label>
          {{-- ✅ must remain enabled (nullable, but not disabled) --}}
          <select id="semester_id" class="form-select">
            <option value="">— Select Semester —</option>
          </select>
          <div class="form-text">Selecting a semester will load subjects.</div>
        </div>

        <div class="col-md-3">
          <label class="form-label">Subject (optional)</label>
          {{-- ✅ must remain enabled (nullable, but not disabled) --}}
          <select id="subject_id" class="form-select">
            <option value="">— Select Subject —</option>
          </select>
          <div class="form-text">Selecting a subject will filter students and show subject attendance.</div>
        </div>

        <div class="col-md-3">
          <label class="form-label">Section (optional)</label>
          {{-- ✅ must remain enabled (nullable, but not disabled) --}}
          <select id="section_id" class="form-select">
            <option value="">— Select Section —</option>
          </select>
          <div class="form-text">If your system has sections per subject/semester.</div>
        </div>

        <div class="col-md-3">
          <label class="form-label">Academic Year (optional)</label>
          <input id="academic_year" type="text" class="form-control" placeholder="e.g., 2025-26">
        </div>

        <div class="col-md-3">
          <label class="form-label">Year (optional)</label>
          <input id="year" type="number" class="form-control" placeholder="e.g., 2026" min="1900" max="2500">
        </div>
      </div>

      <hr class="my-4">

      {{-- Selection buttons --}}
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <button type="button" class="btn btn-outline-primary" id="btnPickQuestions">
          <i class="fa fa-list-check me-1"></i>Select Questions
        </button>
        <button type="button" class="btn btn-outline-primary" id="btnPickFaculty">
          <i class="fa fa-chalkboard-user me-1"></i>Select Faculty
        </button>
        <button type="button" class="btn btn-outline-primary" id="btnPickStudents">
          <i class="fa fa-user-graduate me-1"></i>Select Students
        </button>

        <span class="ms-auto text-muted small">
          Questions: <b id="qCount">0</b> • Faculty: <b id="fCount">0</b> • Students: <b id="sCount">0</b>
        </span>
      </div>

      {{-- Selected chips --}}
      <div class="mt-3">
        <div class="small text-muted mb-2">Selected Questions</div>
        <div class="chip-row" id="chipsQuestions"></div>
      </div>

      <div class="mt-3">
        <div class="small text-muted mb-2">Selected Faculty</div>
        <div class="chip-row" id="chipsFaculty"></div>
      </div>

      <div class="mt-3">
        <div class="small text-muted mb-2">Selected Students</div>
        <div class="chip-row" id="chipsStudents"></div>
      </div>

      <div class="mt-3 small text-muted">
        <i class="fa fa-circle-info me-1"></i>
        Faculty selected here will be automatically applied to all selected questions (no per-question control).
      </div>
    </div>
  </div>

</div>

{{-- Picker Modal (reused for Questions / Faculty / Students) --}}
<div class="modal fade" id="pickModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" id="pickForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fa fa-list-check me-2"></i><span id="pickTitle">Select</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="pickMode" value="">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
          <div class="picker-filters flex-grow-1">
            <div class="search-box flex-grow-1" style="min-width:240px;">
              <input id="pickSearch" type="search" class="form-control" placeholder="Search…">
              <i class="fa fa-search"></i>
            </div>

            {{-- Only used for questions --}}
            <select id="pickGroup" class="form-select" style="display:none;">
              <option value="">All Groups</option>
            </select>

            {{-- ✅ NEW: Only used for students (attendance filter) --}}
            <select id="pickAttendance" class="form-select" style="display:none;">
              <option value="">All Attendance</option>
              <option value="90">≥ 90%</option>
              <option value="80">≥ 80%</option>
              <option value="75">≥ 75%</option>
              <option value="60">≥ 60%</option>
              <option value="50">≥ 50%</option>
              <option value="lt60">&lt; 60%</option>
              <option value="na">No Data</option>
            </select>
          </div>

          <div class="d-flex align-items-center gap-2">
            <span class="count-badge" id="pickSelectedCount">0 selected</span>
            <button type="button" class="btn btn-light btn-sm" id="btnPickSelectAll">
              <i class="fa fa-check-double me-1"></i>Select All
            </button>
            <button type="button" class="btn btn-light btn-sm" id="btnPickClearAll">
              <i class="fa fa-xmark me-1"></i>Clear All
            </button>
          </div>
        </div>

        <div class="modal-user-list" id="pickList">
          <div class="text-center text-muted p-5">
            <i class="fa fa-list mb-3" style="font-size:32px;opacity:.6;"></i>
            <div>Loading…</div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="pickApplyBtn">
          <i class="fa fa-check me-1"></i>Apply
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
  if (window.__FEEDBACK_POST_MANAGE__) return;
  window.__FEEDBACK_POST_MANAGE__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=320) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  // =========================
  // API endpoints
  // =========================
  const API = {
    me:            () => '/api/users/me',
    users:         () => '/api/users',
    questions:     () => '/api/feedback-questions/current',

    // feedback posts
    postShow:      (idOrUuid) => `/api/feedback-posts/${idOrUuid}`,
    postCreate:    () => '/api/feedback-posts',
    postUpdate:    (idOrUuid) => `/api/feedback-posts/${idOrUuid}`,

    // dependency scope
    courses:       () => '/api/courses',

    semestersCandidates: (courseId) => ([
      `/api/course-semesters?per_page=200&page=1&course_id=${encodeURIComponent(courseId)}`,
      `/api/course-semesters?course_id=${encodeURIComponent(courseId)}`,
      `/api/semesters?per_page=200&page=1&course_id=${encodeURIComponent(courseId)}`,
      `/api/semesters?course_id=${encodeURIComponent(courseId)}`,
    ]),

    subjectsCandidates: (semesterId, courseId='') => {
        const sid = encodeURIComponent(semesterId);
        const cid = courseId ? encodeURIComponent(courseId) : '';

        const list = [];

        // ✅ BEST: filtered current endpoint (depends on backend fix)
        if (cid) {
          list.push(`/api/subjects/current?course_id=${cid}&semester_id=${sid}`);
          list.push(`/api/subjects/current?per_page=200&page=1&course_id=${cid}&semester_id=${sid}`);
        }

        // fallback
        list.push(`/api/subjects/current?semester_id=${sid}`);
        list.push(`/api/subjects?per_page=200&page=1&semester_id=${sid}`);
        list.push(`/api/subjects?semester_id=${sid}`);

        return list;
      },



    // ✅ /api/course-semester-sections/current?semester_id=&course_id=
    sectionsCurrent: (semesterId, courseId='') => {
      const qs = new URLSearchParams();
      if (semesterId) qs.set('semester_id', semesterId);
      if (courseId) qs.set('course_id', courseId);
      return `/api/course-semester-sections/current?${qs.toString()}`;
    },

    // ✅ UPDATED: students filtered by academics + optional subject
    // GET /api/student-academic-details/by-academics?course_id=&semester_id=&section_id=&subject_id=
    studentsByAcademics: ({ course_id='', semester_id='', section_id='', subject_id='' }={}) => {
      const qs = new URLSearchParams();
      if (course_id) qs.set('course_id', course_id);
      if (semester_id) qs.set('semester_id', semester_id);

      if (subject_id) qs.set('subject_id', subject_id); // ✅ NEW
      if (section_id) qs.set('section_id', section_id);

      qs.set('per_page', '500');
      return `/api/student-academic-details/by-academics?${qs.toString()}`;
    },
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
    $('globalLoading')?.classList.toggle('is-show', !!on);
  }

  const toastOk  = $('toastSuccess') ? new bootstrap.Toast($('toastSuccess')) : null;
  const toastErr = $('toastError') ? new bootstrap.Toast($('toastError')) : null;
  const ok  = (m) => { $('toastSuccessText').textContent = m || 'Done'; toastOk && toastOk.show(); };
  const err = (m) => { $('toastErrorText').textContent = m || 'Something went wrong'; toastErr && toastErr.show(); };

  function authHeaders(token, extra={}){
    return Object.assign({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    }, extra);
  }

  function idNum(v){
    const n = parseInt(String(v ?? '').trim(), 10);
    return Number.isFinite(n) ? n : null;
  }

  function initials(name){
    const t = String(name || '').trim();
    if (!t) return '—';
    return t.split(' ').map(x => x.charAt(0)).join('').toUpperCase().substring(0,2);
  }

  function pickArray(v){
    if (Array.isArray(v)) return v;
    if (v === null || v === undefined) return [];
    if (typeof v === 'string'){
      try{
        const d = JSON.parse(v);
        return Array.isArray(d) ? d : [];
      }catch(_){ return []; }
    }
    return [];
  }

  // ✅ normalize list response from Laravel (plain array / {data:[]} / paginator {data:{data:[]}})
  function normalizeList(js){
    if (!js) return null;
    if (Array.isArray(js)) return js;
    if (Array.isArray(js.data)) return js.data;
    if (js.data && Array.isArray(js.data.data)) return js.data.data;
    if (Array.isArray(js.items)) return js.items;
    if (Array.isArray(js.result)) return js.result;
    return null;
  }

  function uniqBy(arr, keyFn){
    const out = [];
    const seen = new Set();
    (arr || []).forEach(x => {
      const k = keyFn(x);
      if (k === null || k === undefined || k === '') return;
      const kk = String(k);
      if (seen.has(kk)) return;
      seen.add(kk);
      out.push(x);
    });
    return out;
  }

  function fmtPct(v){
    if (v === null || v === undefined || v === '') return '';
    const n = Number(v);
    if (!Number.isFinite(n)) return '';
    return `${Math.round(n)}%`;
  }

  function attClass(n){
    const v = Number(n);
    if (!Number.isFinite(v)) return '';
    if (v >= 75) return 'good';
    if (v >= 60) return 'warn';
    return 'bad';
  }

  // =========================
  // Page state
  // =========================
  const token = () => (sessionStorage.getItem('token') || localStorage.getItem('token') || '');
  const urlParams = new URLSearchParams(window.location.search);
  const editId = urlParams.get('uuid') || urlParams.get('id') || urlParams.get('post') || '';

  const state = {
    actorRole: '',
    canWrite: false,

    questions: [],
    users: [],

    // ✅ students from academics filter API (now includes attendance fields)
    studentsAcad: [],

    courses: [],
    semesters: [],
    subjects: [],
    sections: [],

    selectedQuestionIds: new Set(),
    selectedFacultyIds: new Set(),
    selectedStudentIds: new Set(),

    req: { sem: 0, sub: 0, sec: 0, stu: 0 }
  };

  function computePermissions(){
    const r = (state.actorRole || '').toLowerCase();
    const writeRoles = ['admin','director','principal','hod','faculty','technical_assistant','it_person'];
    state.canWrite = writeRoles.includes(r);

    $('btnSave').disabled = !state.canWrite;
    $('modeBadge').textContent = editId ? (state.canWrite ? 'Edit' : 'View') : (state.canWrite ? 'Create' : 'View');

    const lock = !state.canWrite;
    [
      'title','description','publish_at','expire_at',
      'course_id','semester_id','subject_id','section_id','academic_year','year',
      'btnPickQuestions','btnPickFaculty','btnPickStudents'
    ].forEach(id => { if ($(id)) $(id).disabled = lock; });
  }

  async function loadMe(){
    try{
      const res = await fetchWithTimeout(API.me(), { headers: authHeaders(token()) }, 8000);
      if (res.ok){
        const js = await res.json().catch(()=> ({}));
        state.actorRole = String(js?.data?.role || js?.role || '').toLowerCase();
      }
    }catch(_){}
    if (!state.actorRole){
      state.actorRole = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
    }
    computePermissions();
  }

  // =========================
  // Load base data
  // =========================
  async function loadQuestions(){
    const res = await fetchWithTimeout(API.questions(), { headers: authHeaders(token()) }, 20000);
    const js = await res.json().catch(()=> ({}));
    if (!res.ok) throw new Error(js?.message || 'Failed to load questions');
    state.questions = normalizeList(js) || [];
  }

  async function loadUsers(){
    const res = await fetchWithTimeout(API.users(), { headers: authHeaders(token()) }, 20000);
    const js = await res.json().catch(()=> ({}));
    if (!res.ok) throw new Error(js?.message || 'Failed to load users');
    const arr = normalizeList(js) || [];
    state.users = arr.filter(u => String(u?.status || 'active').toLowerCase() !== 'inactive');
  }

  /**
   * ✅ LOAD STUDENTS BY ACADEMICS + OPTIONAL SUBJECT
   * - Requires course + semester
   * - If subject selected => will filter by subject and show subject attendance
   * - Section (optional) filters further
   */
  async function loadStudentsByAcademics(){
    const reqId = ++state.req.stu;

    const courseId   = ($('course_id')?.value || '').trim();
    const semesterId = ($('semester_id')?.value || '').trim();
    const subjectId  = ($('subject_id')?.value || '').trim(); // ✅ NEW
    const sectionId  = ($('section_id')?.value || '').trim();

    // ✅ Must have course + semester, else empty
    if (!courseId || !semesterId){
      state.studentsAcad = [];
      return;
    }

    const url = API.studentsByAcademics({
      course_id: courseId,
      semester_id: semesterId,
      subject_id: subjectId, // ✅ NEW
      section_id: sectionId
    });

    try{
      const res = await fetchWithTimeout(url, { headers: authHeaders(token()) }, 25000);
      const js = await res.json().catch(()=> ({}));
      if (reqId !== state.req.stu) return;

      if (!res.ok) {
        state.studentsAcad = [];
        return;
      }

      // ✅ BACKEND returns:
      // data: [{ id,name,email, academic_details:{ course_title, semester_title, section_title, attendance_percentage, subject_attendance } }]
      const rows = normalizeList(js) || [];

      const normalized = rows.map(r => {
        const uid = idNum(r?.id ?? r?.user_id);
        if (!uid) return null;

        const ad = r?.academic_details || {};
        const name = String(r?.name || r?.student_name || 'Student');
        const email = String(r?.email || r?.student_email || '');

        const course = String(ad?.course_title || r?.course_title || '');
        const sem    = String(ad?.semester_title || r?.semester_title || '');
        const sec    = String(ad?.section_title || r?.section_title || '');

        const overallAtt = (ad?.attendance_percentage !== null && ad?.attendance_percentage !== undefined)
          ? Number(ad?.attendance_percentage)
          : null;

        // ✅ subject attendance only meaningful when subject_id passed
        const subjectAtt = (ad?.subject_attendance !== null && ad?.subject_attendance !== undefined)
          ? Number(ad?.subject_attendance)
          : null;

        // which attendance to show/filter: prefer subjectAtt when available
        const effectiveAtt = Number.isFinite(subjectAtt) ? subjectAtt : (Number.isFinite(overallAtt) ? overallAtt : null);

        return {
          id: uid,
          student_name: name,
          student_email: email,
          course_title: course,
          semester_title: sem,
          section_title: sec,

          attendance_overall: overallAtt,
          attendance_subject: subjectAtt,
          attendance_effective: effectiveAtt,

          _raw: r
        };
      }).filter(Boolean);

      state.studentsAcad = uniqBy(normalized, x => x.id);
    }catch(_){
      if (reqId !== state.req.stu) return;
      state.studentsAcad = [];
    }
  }

  function facultyUsers(){
    return (state.users || []).filter(u => String(u?.role || '').toLowerCase() === 'faculty');
  }

  function studentUsers(){
    return (state.studentsAcad || []).slice();
  }

  function qLabel(q){
    return String(q?.question_title || q?.title || q?.name || `Question #${q?.id}` || 'Question');
  }

  function qGroup(q){
    return String(q?.group_title || q?.group || '').trim();
  }

  function userLabel(u){
    return String(u?.name || u?.full_name || 'User');
  }

  function studentLabel(s){
    return String(s?.student_name || s?.name || 'Student');
  }

  function studentSubLine(s){
    const email = String(s?.student_email || '');
    const parts = [];

    if (email) parts.push(email);

    const course = String(s?.course_title || '').trim();
    const sem    = String(s?.semester_title || '').trim();
    const sec    = String(s?.section_title || '').trim();

    const acad = [];
    if (course) acad.push(`Course: ${course}`);
    if (sem) acad.push(`Sem: ${sem}`);
    if (sec) acad.push(`Sec: ${sec}`);

    if (acad.length) parts.push(acad.join(' • '));

    // ✅ Attendance line
    const eff = s?.attendance_effective;
    if (eff !== null && eff !== undefined && Number.isFinite(Number(eff))){
      const subjectChosen = !!String($('subject_id')?.value || '').trim();
      parts.push(subjectChosen ? `Subject Att: ${fmtPct(eff)}` : `Attendance: ${fmtPct(eff)}`);
    } else {
      parts.push('Attendance: —');
    }

    return parts.filter(Boolean).join(' • ');
  }

  // =========================
  // Dependency dropdown helpers (always enabled; never disabled)
  // =========================
  function setSelectOptions(selId, rows, labelKeys=['title','name'], keepPlaceholder=true){
    const sel = $(selId);
    if (!sel) return;

    const curr = String(sel.value || '');
    const ph = keepPlaceholder ? sel.querySelector('option[value=""]') : null;

    sel.innerHTML = '';
    if (keepPlaceholder){
      const o = document.createElement('option');
      o.value = '';
      o.textContent = ph?.textContent || '— Select —';
      sel.appendChild(o);
    }

    (rows || []).forEach(r => {
      const id = idNum(r?.id);
      if (!id) return;

      const label =
        (labelKeys.map(k => r?.[k]).find(Boolean)) ||
        r?.label ||
        r?.code ||
        (`#${id}`);

      const opt = document.createElement('option');
      opt.value = String(id);
      opt.textContent = String(label);
      sel.appendChild(opt);
    });

    if (curr){
      const match = sel.querySelector(`option[value="${CSS.escape(curr)}"]`);
      if (match) sel.value = curr;
    }
  }

  async function safeFetchList(url){
    try{
      const res = await fetchWithTimeout(url, { headers: authHeaders(token()) }, 20000);
      const js = await res.json().catch(()=> ({}));
      if (!res.ok) return null;
      return normalizeList(js);
    }catch(_){
      return null;
    }
  }

  async function fetchFirstWorking(urls){
    for (const u of (urls || [])){
      const arr = await safeFetchList(u);
      if (Array.isArray(arr)) return arr;
    }
    return null;
  }

  async function loadCourses(){
    const arr = await safeFetchList(API.courses());
    if (!arr) return;
    state.courses = uniqBy(arr, r => idNum(r?.id));
    setSelectOptions('course_id', state.courses, ['title','name','course_name','course_title']);
  }

  async function loadSemesters(courseId){
    const reqId = ++state.req.sem;

    state.semesters = [];
    state.subjects = [];
    state.sections = [];

    setSelectOptions('semester_id', [], ['title','name'], true);
    setSelectOptions('subject_id', [], ['title','name'], true);
    setSelectOptions('section_id', [], ['title','name'], true);

    if (!courseId) return;

    const raw = await fetchFirstWorking(API.semestersCandidates(courseId));
    if (reqId !== state.req.sem) return;
    if (!raw) return;

    const filtered = (raw || []).filter(r => {
      const c = String(r?.course_id ?? r?.courseId ?? '');
      return !c || c === String(courseId);
    });

    const normalized = filtered.map(r => {
      const sid = idNum(r?.semester_id ?? r?.sem_id ?? r?.semesterId ?? r?.id);
      const title =
        r?.semester_title ??
        r?.semester_name ??
        r?.title ??
        r?.name ??
        r?.code ??
        (sid ? `Semester #${sid}` : '');
      return { id: sid, title };
    }).filter(x => x.id);

    const unique = uniqBy(normalized, x => x.id);

    state.semesters = unique;
    setSelectOptions('semester_id', unique, ['title','name','semester_title','semester_name','code']);
  }

  async function loadSubjects(semesterId){
    const reqId = ++state.req.sub;

    state.subjects = [];
    state.sections = [];

    setSelectOptions('subject_id', [], ['title','name'], true);
    setSelectOptions('section_id', [], ['title','name'], true);

    if (!semesterId) return;

    const courseId = ($('course_id')?.value || '').trim();

    // ✅ Fetch using course+semester priority
    const raw = await fetchFirstWorking(API.subjectsCandidates(semesterId, courseId));
    if (reqId !== state.req.sub) return;
    if (!raw) return;

    // ✅ Normalize + filter strictly for selected course+semester if backend returns extra
    const normalized = raw.map(r => {
      const id = idNum(r?.subject_id ?? r?.id);
      if (!id) return null;

      const title =
        r?.subject_title ??
        r?.subject_name ??
        r?.title ??
        r?.name ??
        (id ? `Subject #${id}` : '');

      const code = String(r?.subject_code || r?.code || '').trim();
      const type = String(r?.subject_type || r?.type || '').trim();

      // ✅ Show in bracket: (CODE • TYPE)
      const extra = [code, type].filter(Boolean).join(' • ');
      const finalTitle = extra ? `${title} (${extra})` : title;

      return {
        id,
        title: finalTitle,
        _course_id: r?.course_id ?? r?.courseId ?? null,
        _semester_id: r?.semester_id ?? r?.semesterId ?? null,
      };
    }).filter(Boolean);

    // ✅ HARD FILTER (only if backend returns these ids)
    const filtered = normalized.filter(x => {
      const okCourse = !courseId || !x._course_id || String(x._course_id) === String(courseId);
      const okSem    = !semesterId || !x._semester_id || String(x._semester_id) === String(semesterId);
      return okCourse && okSem;
    });

    const unique = uniqBy(filtered, x => x.id);

    state.subjects = unique;

    // ✅ Fill dropdown with our formatted title
    setSelectOptions('subject_id', unique, ['title']);
  }


  async function loadSections(){
    const reqId = ++state.req.sec;

    state.sections = [];
    setSelectOptions('section_id', [], ['title','name'], true);

    const semesterId = ($('semester_id')?.value || '').trim();
    const courseId   = ($('course_id')?.value || '').trim();

    if (!semesterId) return;

    const url = API.sectionsCurrent(semesterId, courseId);

    let js = null;
    try{
      const res = await fetchWithTimeout(url, { headers: authHeaders(token()) }, 20000);
      js = await res.json().catch(()=> ({}));
      if (!res.ok) return;
    }catch(e){
      console.error('sections fetch failed', e);
      return;
    }

    if (reqId !== state.req.sec) return;

    const rows = Array.isArray(js?.data) ? js.data : (Array.isArray(js) ? js : []);
    if (!rows.length) return;

    const normalized = rows.map(r => {
      const id = idNum(r?.id ?? r?.section_id);
      const title =
        r?.title ??
        r?.section_title ??
        r?.name ??
        r?.section_name ??
        r?.code ??
        r?.section_code ??
        (id ? `Section #${id}` : '');
      return { id, title };
    }).filter(x => x.id);

    const unique = uniqBy(normalized, x => x.id);
    state.sections = unique;

    setSelectOptions('section_id', unique, ['title','name'], true);
  }

  // =========================
  // Hydrate edit
  // =========================
  async function loadPostIfEdit(){
    if (!editId) return;

    const res = await fetchWithTimeout(API.postShow(editId), { headers: authHeaders(token()) }, 20000);
    const js = await res.json().catch(()=> ({}));
    if (!res.ok) throw new Error(js?.message || 'Failed to load post');

    let d = js?.data;
    if (Array.isArray(d)) d = d[0];
    if (!d) d = js?.data || js;

    $('title').value       = d?.title || '';
    $('description').value = d?.description || '';
    $('publish_at').value  = (d?.publish_at || '').replace(' ', 'T').substring(0,16);
    $('expire_at').value   = (d?.expire_at || '').replace(' ', 'T').substring(0,16);

    const courseId   = d?.course_id ? String(d.course_id) : '';
    const semesterId = d?.semester_id ? String(d.semester_id) : '';
    const subjectId  = d?.subject_id ? String(d.subject_id) : '';
    const sectionId  = d?.section_id ? String(d.section_id) : '';

    $('academic_year').value = d?.academic_year ?? '';
    $('year').value = (d?.year ?? '') !== null ? String(d?.year ?? '') : '';

    if (courseId){
      $('course_id').value = courseId;
      await loadSemesters(courseId);
    }
    if (semesterId){
      $('semester_id').value = semesterId;
      await loadSubjects(semesterId);
      await loadSections();
    }
    if (subjectId){
      $('subject_id').value = subjectId;
    }
    if (sectionId){
      $('section_id').value = sectionId;
    }

    pickArray(d?.question_ids).forEach(x => { const n=idNum(x); if(n) state.selectedQuestionIds.add(n); });
    pickArray(d?.faculty_ids).forEach(x => { const n=idNum(x); if(n) state.selectedFacultyIds.add(n); });
    pickArray(d?.student_ids).forEach(x => { const n=idNum(x); if(n) state.selectedStudentIds.add(n); });

    // ✅ after scope hydrated, load students list accordingly (now includes subject)
    await loadStudentsByAcademics();
  }

  // =========================
  // Chips
  // =========================
  function chipHTML(text, onRemove){
    return `
      <span class="chip">
        ${esc(text)}
        <button type="button" class="x" ${onRemove ? `data-remove="${esc(onRemove)}"` : ''} aria-label="remove">
          <i class="fa fa-xmark"></i>
        </button>
      </span>
    `;
  }

  function renderChips(){
    const qChips = [];
    state.selectedQuestionIds.forEach(qid => {
      const q = state.questions.find(x => String(x?.id) === String(qid));
      qChips.push(chipHTML(q ? (qLabel(q)) : `Question #${qid}`, `q:${qid}`));
    });
    $('chipsQuestions').innerHTML = qChips.length ? qChips.join('') : `<span class="text-muted small">None</span>`;

    const fChips = [];
    state.selectedFacultyIds.forEach(uid => {
      const u = state.users.find(x => String(x?.id) === String(uid));
      fChips.push(chipHTML(u ? userLabel(u) : `Faculty #${uid}`, `f:${uid}`));
    });
    $('chipsFaculty').innerHTML = fChips.length ? fChips.join('') : `<span class="text-muted small">None</span>`;

    const sChips = [];
    state.selectedStudentIds.forEach(uid => {
      const s = state.studentsAcad.find(x => String(x?.id) === String(uid));
      sChips.push(chipHTML(s ? studentLabel(s) : `Student #${uid}`, `s:${uid}`));
    });
    $('chipsStudents').innerHTML = sChips.length ? sChips.join('') : `<span class="text-muted small">None</span>`;

    $('qCount').textContent = state.selectedQuestionIds.size;
    $('fCount').textContent = state.selectedFacultyIds.size;
    $('sCount').textContent = state.selectedStudentIds.size;

    const courseTxt = $('course_id')?.value ? `course=${$('course_id').value}` : 'course=—';
    const semTxt    = $('semester_id')?.value ? `sem=${$('semester_id').value}` : 'sem=—';
    const subTxt    = $('subject_id')?.value ? `sub=${$('subject_id').value}` : 'sub=ALL';
    const secTxt    = $('section_id')?.value ? `sec=${$('section_id').value}` : 'sec=ALL';

    $('summaryText').textContent =
      `Selected: ${state.selectedQuestionIds.size} question(s), ${state.selectedFacultyIds.size} faculty, ${state.selectedStudentIds.size} student(s) • Student filter: ${courseTxt}, ${semTxt}, ${subTxt}, ${secTxt}`;
  }

  // =========================
  // ✅ Picker modal (KEEP selection across search/filter)
  // =========================
  let pickerSelected = new Set();

  function updatePickSelectedCount(){
    $('pickSelectedCount').textContent = `${pickerSelected.size} selected`;
  }

  function getPickGroupValue(){
    const el = $('pickGroup');
    if (!el || el.style.display === 'none') return '';
    return String(el.value || '').trim();
  }

  function getPickAttendanceValue(){
    const el = $('pickAttendance');
    if (!el || el.style.display === 'none') return '';
    return String(el.value || '').trim();
  }

  function passAttendanceFilter(it){
    if (it._mode !== 'students') return true;

    const f = getPickAttendanceValue();
    if (!f) return true;

    const att = it.att_effective;
    const has = Number.isFinite(Number(att));

    if (f === 'na') return !has;
    if (f === 'lt60') return has && Number(att) < 60;

    const min = Number(f);
    if (!Number.isFinite(min)) return true;
    return has && Number(att) >= min;
  }

  function renderPickList(items){
    const q = String($('pickSearch').value || '').trim().toLowerCase();
    const g = getPickGroupValue();

    const list = items.filter(it => {
      if (it._mode === 'questions' && g){
        if (String(it.group || '') !== String(g)) return false;
      }
      if (!passAttendanceFilter(it)) return false;

      if (!q) return true;
      const hay = String(it._search || '').toLowerCase();
      return hay.includes(q);
    });

    const root = $('pickList');
    if (!list.length){
      root.innerHTML = `
        <div class="text-center text-muted p-5">
          <i class="fa fa-search mb-3" style="font-size:32px;opacity:.6;"></i>
          <div>No results</div>
        </div>
      `;
      updatePickSelectedCount();
      return;
    }

    root.innerHTML = list.map(it => {
      const on = pickerSelected.has(it.id);

      let rightMeta = '';
      if (it._mode === 'students'){
        const pct = fmtPct(it.att_effective);
        const cls = attClass(it.att_effective);
        rightMeta = pct ? `<span class="att-badge ${cls}"><i class="fa fa-chart-line"></i>${esc(pct)}</span>` : `<span class="att-badge"><i class="fa fa-chart-line"></i>—</span>`;
      }

      return `
        <div class="modal-user-item">
          <div class="modal-user-info">
            <div class="modal-user-avatar">${esc(it.avatar || initials(it.title))}</div>
            <div class="modal-user-details">
              <div class="modal-user-name">${esc(it.title)}</div>
              <div class="modal-user-email">${esc(it.sub || '')}</div>
            </div>
          </div>

          <div class="d-flex align-items-center gap-2">
            ${rightMeta}
            <div class="form-check form-switch m-0">
              <input class="form-check-input" type="checkbox" data-id="${esc(String(it.id))}" ${on ? 'checked' : ''}>
            </div>
          </div>
        </div>
      `;
    }).join('');

    updatePickSelectedCount();
  }

  function fillQuestionGroups(){
    const el = $('pickGroup');
    if (!el) return;

    const groups = Array.from(new Set((state.questions || []).map(q => qGroup(q)).filter(Boolean)))
      .sort((a,b)=> String(a).localeCompare(String(b)));

    const curr = String(el.value || '');
    el.innerHTML = `<option value="">All Groups</option>` + groups.map(g => `<option value="${esc(g)}">${esc(g)}</option>`).join('');
    if (curr) el.value = curr;
  }

  function openPicker(mode){
    const modal = new bootstrap.Modal($('pickModal'));
    $('pickMode').value = mode;
    $('pickSearch').value = '';

    let title = 'Select';
    let items = [];
    let pre = new Set();

    // reset special filters visibility
    $('pickGroup').style.display = 'none';
    $('pickAttendance').style.display = 'none';
    $('pickGroup').value = '';
    $('pickAttendance').value = '';

    if (mode === 'questions'){
      $('pickGroup').style.display = '';
      fillQuestionGroups();
      title = 'Select Questions (Filter by Group)';
      items = (state.questions || []).map(q => ({
        _mode: 'questions',
        id: idNum(q?.id),
        title: qLabel(q),
        group: qGroup(q),
        sub: qGroup(q) ? `Group: ${qGroup(q)}` : '',
        avatar: 'Q',
        _search: `${qLabel(q)} ${qGroup(q)}`.trim()
      })).filter(x => x.id);

      pre = new Set(state.selectedQuestionIds);
    }

    if (mode === 'faculty'){
      title = 'Select Faculty';
      items = facultyUsers().map(u => ({
        _mode: 'faculty',
        id: idNum(u?.id),
        title: String(u?.name || u?.full_name || 'Faculty'),
        sub: `${u?.email||'No email'} • ${u?.role||''}`,
        avatar: initials(u?.name),
        _search: `${u?.name||''} ${u?.email||''} ${u?.role||''}`.trim()
      })).filter(x => x.id);

      pre = new Set(state.selectedFacultyIds);
    }

    if (mode === 'students'){
      // ✅ Must have course + semester
      const courseId   = ($('course_id')?.value || '').trim();
      const semesterId = ($('semester_id')?.value || '').trim();

      if (!courseId || !semesterId){
        Swal.fire({
          icon: 'info',
          title: 'Select Course & Semester first',
          text: 'Students are loaded based on academics (course + semester, section and subject if selected).',
          confirmButtonText: 'OK'
        });
        return;
      }

      $('pickAttendance').style.display = ''; // ✅ show attendance filter for students

      const subjectChosen = !!String($('subject_id')?.value || '').trim();
      const sectionId  = ($('section_id')?.value || '').trim();

      title = subjectChosen
        ? (sectionId ? 'Select Students (Subject-wise + Section)' : 'Select Students (Subject-wise)')
        : (sectionId ? 'Select Students (Course + Semester + Section)' : 'Select Students (Course + Semester)');

      items = studentUsers().map(s => ({
        _mode: 'students',
        id: idNum(s?.id),
        title: studentLabel(s),
        sub: studentSubLine(s),
        avatar: initials(studentLabel(s)),
        att_effective: (s?.attendance_effective ?? null),
        _search: `${studentLabel(s)} ${studentSubLine(s)}`.trim()
      })).filter(x => x.id);

      pre = new Set(state.selectedStudentIds);
    }

    $('pickTitle').textContent = title;

    // ✅ LIVE selection set in modal (persist across search/filter)
    pickerSelected = new Set(pre);

    $('pickModal').dataset.items = JSON.stringify(items);
    renderPickList(items);

    modal.show();
  }

  // =========================
  // Save Post
  // =========================
  function validate(){
    const t = String($('title').value || '').trim();
    if (!t) return 'Title is required';
    if (!state.selectedQuestionIds.size) return 'Please select at least 1 question';
    if (!state.selectedFacultyIds.size) return 'Please select faculty';
    if (!state.selectedStudentIds.size) return 'Please select students';
    return '';
  }

  function buildQuestionFacultyPayload(){
    const out = {};
    const fac = Array.from(state.selectedFacultyIds);
    state.selectedQuestionIds.forEach(qid => {
      out[qid] = fac.length ? { faculty_ids: fac } : null;
    });
    return out;
  }

  async function savePost(){
    if (!state.canWrite) return;

    const msg = validate();
    if (msg){ err(msg); return; }

    const payload = {
      title: String($('title').value || '').trim(),
      description: String($('description').value || '').trim() || null,
      publish_at: $('publish_at').value ? $('publish_at').value.replace('T', ' ') + ':00' : null,
      expire_at: $('expire_at').value ? $('expire_at').value.replace('T', ' ') + ':00' : null,

      course_id: $('course_id').value ? parseInt($('course_id').value, 10) : null,
      semester_id: $('semester_id').value ? parseInt($('semester_id').value, 10) : null,
      subject_id: $('subject_id').value ? parseInt($('subject_id').value, 10) : null,
      section_id: $('section_id').value ? parseInt($('section_id').value, 10) : null,
      academic_year: String($('academic_year').value || '').trim() || null,
      year: $('year').value ? parseInt($('year').value, 10) : null,

      question_ids: Array.from(state.selectedQuestionIds),
      faculty_ids: Array.from(state.selectedFacultyIds),
      student_ids: Array.from(state.selectedStudentIds),

      question_faculty: buildQuestionFacultyPayload(),
    };

    showLoading(true);
    try{
      const url = editId ? API.postUpdate(editId) : API.postCreate();
      const method = editId ? 'PUT' : 'POST';

      const res = await fetchWithTimeout(url, {
        method,
        headers: authHeaders(token(), { 'Content-Type':'application/json' }),
        body: JSON.stringify(payload)
      }, 25000);

      const js = await res.json().catch(()=> ({}));
      if (res.status === 401){ window.location.href='/'; return; }
      if (!res.ok || js.success === false) throw new Error(js?.message || js?.error || 'Save failed');

      ok('Feedback post saved');

      const newId = js?.data?.uuid || js?.data?.id || js?.uuid || js?.id;
      if (!editId && newId){
        const u = new URL(window.location.href);
        u.searchParams.set('uuid', newId);
        u.searchParams.delete('id');
        u.searchParams.delete('post');
        window.location.href = u.toString();
        return;
      }
    }catch(e){
      err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Save failed'));
    }finally{
      showLoading(false);
    }
  }

  // =========================
  // Events
  // =========================
  function bindChipRemove(){
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-remove]');
      if (!btn) return;
      const code = btn.dataset.remove || '';
      const [type, id] = code.split(':');
      const n = idNum(id);
      if (!n) return;

      if (type === 'q') state.selectedQuestionIds.delete(n);
      if (type === 'f') state.selectedFacultyIds.delete(n);
      if (type === 's') state.selectedStudentIds.delete(n);

      renderChips();
    });
  }

  document.addEventListener('DOMContentLoaded', async () => {
    if (!token()){ window.location.href='/'; return; }

    bindChipRemove();

    // ✅ Search rerender MUST NOT wipe selection
    $('pickSearch').addEventListener('input', debounce(() => {
      const items = JSON.parse($('pickModal').dataset.items || '[]');
      renderPickList(items);
    }, 200));

    $('pickGroup').addEventListener('change', () => {
      const items = JSON.parse($('pickModal').dataset.items || '[]');
      renderPickList(items);
    });

    $('pickAttendance').addEventListener('change', () => {
      const items = JSON.parse($('pickModal').dataset.items || '[]');
      renderPickList(items);
    });

    // ✅ Select All (visible list only) -> add to pickerSelected
    $('btnPickSelectAll').addEventListener('click', () => {
      const items = JSON.parse($('pickModal').dataset.items || '[]');
      document.querySelectorAll('#pickList input[type="checkbox"][data-id]').forEach(cb => {
        cb.checked = true;
        const id = idNum(cb.dataset.id);
        if (id) pickerSelected.add(id);
      });
      updatePickSelectedCount();
      renderPickList(items);
    });

    // ✅ Clear All (visible list only)
    $('btnPickClearAll').addEventListener('click', () => {
      const items = JSON.parse($('pickModal').dataset.items || '[]');
      document.querySelectorAll('#pickList input[type="checkbox"][data-id]').forEach(cb => {
        cb.checked = false;
        const id = idNum(cb.dataset.id);
        if (id) pickerSelected.delete(id);
      });
      updatePickSelectedCount();
      renderPickList(items);
    });

    // ✅ When toggling checkbox, update pickerSelected
    document.addEventListener('change', (e) => {
      const cb = e.target.closest('#pickList input[type="checkbox"][data-id]');
      if (!cb) return;

      const id = idNum(cb.dataset.id);
      if (!id) return;

      if (cb.checked) pickerSelected.add(id);
      else pickerSelected.delete(id);

      updatePickSelectedCount();
    });

    // ✅ Apply uses pickerSelected (not DOM)
    $('pickForm').addEventListener('submit', (e) => {
      e.preventDefault();

      const mode = $('pickMode').value;
      const finalSet = new Set(pickerSelected);

      if (mode === 'questions') state.selectedQuestionIds = finalSet;
      if (mode === 'faculty')   state.selectedFacultyIds  = finalSet;
      if (mode === 'students')  state.selectedStudentIds  = finalSet;

      renderChips();
      bootstrap.Modal.getInstance($('pickModal'))?.hide();
    });

    // open pickers
    $('btnPickQuestions').addEventListener('click', () => openPicker('questions'));
    $('btnPickFaculty').addEventListener('click', () => openPicker('faculty'));

    $('btnPickStudents').addEventListener('click', async () => {
      showLoading(true);
      try{
        await loadStudentsByAcademics(); // ✅ subject aware now
      }finally{
        showLoading(false);
      }
      openPicker('students');
    });

    // dependency bindings
    $('course_id').addEventListener('change', async () => {
      const courseId = $('course_id').value || '';
      $('semester_id').value = '';
      $('subject_id').value = '';
      $('section_id').value = '';
      await loadSemesters(courseId);
      await loadSections();

      await loadStudentsByAcademics();
      renderChips();
    });

    $('semester_id').addEventListener('change', async () => {
      const semesterId = $('semester_id').value || '';
      $('subject_id').value = '';
      $('section_id').value = '';
      await loadSubjects(semesterId);
      await loadSections();

      await loadStudentsByAcademics();
      renderChips();
    });

    // ✅ subject change now refreshes student list (subject-wise filter)
    $('subject_id').addEventListener('change', async () => {
      $('section_id').value = '';
      await loadStudentsByAcademics(); // ✅ now subject affects output
      renderChips();
    });

    $('section_id').addEventListener('change', async () => {
      await loadStudentsByAcademics();
      renderChips();
    });

    // toolbar
    $('btnSave').addEventListener('click', savePost);
    $('btnRefresh').addEventListener('click', async () => {
      showLoading(true);
      try{
        await loadCourses();
        await Promise.all([loadQuestions(), loadUsers()]);

        state.selectedQuestionIds = new Set();
        state.selectedFacultyIds  = new Set();
        state.selectedStudentIds  = new Set();

        await loadStudentsByAcademics();
        await loadPostIfEdit();
        renderChips();
        ok('Refreshed');
      }finally{
        showLoading(false);
      }
    });

    // init
    showLoading(true);
    try{
      await loadMe();

      ['semester_id','subject_id','section_id'].forEach(id => { if ($(id)) $(id).disabled = false; });

      await loadCourses();
      await Promise.all([loadQuestions(), loadUsers()]);

      await loadStudentsByAcademics();
      await loadPostIfEdit();

      renderChips();
    }catch(ex){
      err(ex?.message || 'Initialization failed');
    }finally{
      showLoading(false);
    }
  });
})();
</script>
@endpush
