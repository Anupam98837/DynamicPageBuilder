{{-- resources/views/modules/subjects/studentSubjectAttendance.blade.php --}}
@section('title','Student Subject Attendance')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* Modern Variable Overrides / Definitions */
:root {
    --ssa-primary: #9E363A;
    --ssa-primary-soft: #ffeeeeff;
    --ssa-border: #f0e2e2ff;
    --ssa-bg-light: #fcf8f8ff;
    --ssa-header-bg: #f9f1f1ff;
    --ssa-sticky-bg: #ffffff;
}

/* ===== Shell ===== */
.ssa-wrap { max-width: 1400px; margin: 24px auto 60px; padding: 0 20px; }

.panel {
    background: #fff;
    border: 1px solid var(--ssa-border);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    padding: 20px;
    transition: all 0.3s ease;
}

.ssa-card {
    border: 1px solid var(--ssa-border);
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.ssa-card .card-header {
    background: #fff;
    border-bottom: 1px solid var(--ssa-border);
    padding: 16px 20px;
}

.loading-overlay {
    position: fixed; inset: 0;
    background: rgba(255, 255, 255, 0.7);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    backdrop-filter: blur(4px);
}
.loading-overlay.is-show { display: flex; }

/* ===== Toolbar ===== */
.ssa-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    gap: 16px; flex-wrap: wrap;
}
.ssa-toolbar .left { display: flex; flex-direction: column; gap: 4px; }
.ssa-toolbar .right { display: flex; align-items: center; gap: 12px; }

.count-badge {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 6px 14px; border-radius: 8px;
    background: var(--ssa-primary-soft);
    color: var(--ssa-primary);
    font-weight: 600; font-size: 13px;
    border: 1px solid color-mix(in srgb, var(--ssa-primary) 20%, transparent);
}

.text-mini { font-size: 13px; color: #64748b; }

.ssa-form {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-top: 20px;
}
@media(max-width: 860px){
    .ssa-form { grid-template-columns: 1fr; }
}

.ssa-field label {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 8px;
    display: block;
}
.ssa-field select {
    width: 100%;
    height: 44px;
    border-radius: 8px;
    border: 1px solid var(--ssa-border);
    background: #fff;
    color: #1e293b;
    padding: 0 12px;
    font-size: 14px;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.ssa-field select:focus {
    outline: none;
    border-color: var(--ssa-primary);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--ssa-primary) 15%, transparent);
}

/* ===== Table Styling ===== */
.ssa-table-wrap {
    border-radius: 8px;
    overflow: auto;
    max-height: 650px;
    border: 1px solid var(--ssa-border);
}
.ssa-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 14px;
}

/* Header Aesthetics */
.ssa-table thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: var(--ssa-header-bg);
    color: #334155;
    font-weight: 700;
    text-align: center;
    border-bottom: 1px solid var(--ssa-border);
    border-right: 1px solid var(--ssa-border);
    padding: 12px 8px;
    white-space: nowrap;
}

.ssa-table thead tr.h1 th { background: #f8fafc; font-size: 15px; color: #1e293b; }
.ssa-table thead tr.h2 th { font-size: 13px; background: #fff; }
.ssa-table thead tr.h3 th { font-size: 12px; background: var(--ssa-bg-light); }

/* Sticky Left Columns */
.sticky-col {
    position: sticky;
    left: 0;
    z-index: 11 !important;
    background: var(--ssa-sticky-bg) !important;
    border-right: 2px solid var(--ssa-border) !important;
}
.sticky-col-2 {
    position: sticky;
    left: 70px; /* Adjusted based on Roll No width */
    z-index: 11 !important;
    background: var(--ssa-sticky-bg) !important;
    border-right: 2px solid var(--ssa-border) !important;
}

.ssa-table thead .sticky-col,
.ssa-table thead .sticky-col-2 {
    z-index: 12 !important;
}

.ssa-table tbody td {
    padding: 10px 12px;
    border-bottom: 1px solid var(--ssa-border);
    border-right: 1px solid var(--ssa-border);
    background: #fff;
    vertical-align: middle;
}

.ssa-table tbody tr:hover td {
    background: #f1f5f9 !important;
}

/* Widths */
.col-roll { width: 70px; text-align: center; font-weight: 700; color: #64748b; }
.col-name { min-width: 220px; font-weight: 600; color: #1e293b; }
.sub-col { min-width: 160px; }

/* Components in Cells */
.sub-head {
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.sub-head .code {
    background: #e2e8f0;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.sub-head .all {
    display: flex; align-items: center; gap: 6px; font-size: 11px;
}

.cellbox {
    display: flex; align-items: center; justify-content: center; gap: 10px;
}
.att-input {
    width: 80px;
    height: 36px;
    border-radius: 6px;
    border: 1px solid var(--ssa-border);
    text-align: center;
    font-weight: 700;
    color: var(--ssa-primary);
    transition: all 0.2s;
}
.att-input:focus {
    border-color: var(--ssa-primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    outline: none;
}

.cell-na {
    font-size: 11px;
    font-weight: 700;
    color: #94a3b8;
    background: #f1f5f9;
    padding: 4px 12px;
    border-radius: 4px;
    border: 1px dashed #cbd5e1;
}

/* Inputs */
input[type="checkbox"] {
    width: 18px; height: 18px;
    cursor: pointer;
    accent-color: var(--ssa-primary);
}

/* Error States */
.cell-error { background: #fff1f2 !important; }
.cell-error .att-input { border-color: #f43f5e !important; background: #fff !important; }

/* Empty state */
.empty-state {
    text-align: center; padding: 60px 20px;
    color: #64748b;
}
.empty-state i { font-size: 40px; margin-bottom: 16px; opacity: 0.5; }
.empty-state .title { font-size: 18px; font-weight: 700; color: #334155; }
</style>
@endpush

@section('content')
<div class="ssa-wrap">

  <div id="globalLoading" class="loading-overlay">
    <div class="spinner-border text-danger" role="status"></div>
  </div>

  {{-- Top Toolbar Panel --}}
  <div class="panel mb-4">
    <div class="ssa-toolbar">
      <div class="left">
        <h5 class="mb-0 fw-bold"><i class="fa fa-clipboard-user text-danger me-2"></i>Student Subject Attendance</h5>
        <div class="text-mini" id="hintText">
          Select parameters to manage student subject mapping and current attendance percentages.
        </div>
      </div>

      <div class="right">
        <span class="count-badge" id="badgeSummary">
            <i class="fa fa-chart-simple"></i> —
        </span>
        <button id="btnRefresh" class="btn btn-outline-secondary px-3">
          <i class="fa fa-arrows-rotate"></i>
        </button>
        <button id="btnSave" class="btn btn-primary px-4 fw-bold">
          <i class="fa fa-cloud-arrow-up me-2"></i>Save Changes
        </button>
      </div>
    </div>

    <div class="ssa-form">
      <div class="ssa-field">
        <label for="courseSelect">Course</label>
        <select id="courseSelect">
          <option value="">Loading courses...</option>
        </select>
      </div>

      <div class="ssa-field">
        <label for="semesterSelect">Semester</label>
        <select id="semesterSelect" disabled>
          <option value="">Select a course first</option>
        </select>
      </div>
    </div>

    <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
        <div class="text-mini" id="infoLine">Ready to load.</div>
        <div class="small text-muted">
            <i class="fa fa-info-circle me-1"></i> Data is auto-saved locally during your session.
        </div>
    </div>
  </div>

  {{-- Table Card --}}
  <div class="ssa-card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div class="fw-bold text-secondary"><i class="fa fa-list-check me-2"></i>Attendance Matrix</div>
      <div class="badge bg-light text-dark border fw-normal">Toggle checkboxes to include subjects</div>
    </div>

    <div class="card-body p-0">
      <div id="emptyState" class="empty-state">
        <i class="fa fa-layer-group"></i>
        <div class="title">No Selection Made</div>
        <div class="subtitle">Please select a course and semester to view the attendance grid.</div>
      </div>

      <div id="tableRoot" style="display:none;"></div>
    </div>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
  if (window.__STUDENT_SUBJECT_ATTENDANCE_V1__) return;
  window.__STUDENT_SUBJECT_ATTENDANCE_V1__ = true;

  const $ = (id) => document.getElementById(id);

  const API = {
    courses: () => '/api/courses',
    semestersByCourse: (courseId) => `/api/courses/${encodeURIComponent(courseId)}/semesters`,
    subjectsByScope: (deptId, courseId, semesterId) =>
      `/api/subjects/current?department_id=${encodeURIComponent(deptId)}&course_id=${encodeURIComponent(courseId)}&semester_id=${encodeURIComponent(semesterId)}`,
    studentsByScope: (courseId, semesterId) =>
      `/api/student-academic-details/by-academics?course_id=${encodeURIComponent(courseId)}&semester_id=${encodeURIComponent(semesterId)}`,
    mappingCurrent: (departmentId, courseId, semesterId) =>
      `/api/student-subjects/current?department_id=${encodeURIComponent(departmentId)}&course_id=${encodeURIComponent(courseId)}&semester_id=${encodeURIComponent(semesterId)}`,
    store: () => '/api/student-subjects',
    update: (idOrUuid) => `/api/student-subjects/${encodeURIComponent(idOrUuid)}`,
  };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  function idNum(v){
    const n = parseInt(String(v ?? '').trim(), 10);
    return Number.isFinite(n) ? n : null;
  }

  function pickArray(v){
    if (Array.isArray(v)) return v;
    if (v === null || v === undefined) return [];
    if (typeof v === 'string'){
      try{ const d = JSON.parse(v); return Array.isArray(d) ? d : []; }catch(_){ return []; }
    }
    return [];
  }

  function normalizeList(js){
    if (!js) return [];
    if (Array.isArray(js)) return js;
    if (Array.isArray(js.data)) return js.data;
    if (js.data && Array.isArray(js.data.data)) return js.data.data;
    if (Array.isArray(js.items)) return js.items;
    return [];
  }

  const token = () => (sessionStorage.getItem('token') || localStorage.getItem('token') || '');
  function authHeaders(extra={}){
    return Object.assign({
      'Authorization': 'Bearer ' + token(),
      'Accept': 'application/json'
    }, extra);
  }

  async function fetchWithTimeout(url, opts={}, ms=15000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try{ return await fetch(url, { ...opts, signal: ctrl.signal }); }
    finally{ clearTimeout(t); }
  }

  function showLoading(on){ $('globalLoading')?.classList.toggle('is-show', !!on); }

  const toastOk  = $('toastSuccess') ? new bootstrap.Toast($('toastSuccess')) : null;
  const toastErr = $('toastError') ? new bootstrap.Toast($('toastError')) : null;
  const ok  = (m) => { $('toastSuccessText').textContent = m || 'Done'; toastOk && toastOk.show(); };
  const err = (m) => { $('toastErrorText').textContent = m || 'Something went wrong'; toastErr && toastErr.show(); };

  /**
   * ✅ Page State
   */
  const state = {
    courses: [],
    semesters: [],
    subjects: [],
    students: [],
    mapping: null,
    departmentId: null,
    selectedCourseId: null,
    selectedSemesterId: null,
    matrix: new Map(),
    saving: false,
  };

  function courseLabel(c){ return String(c?.title || c?.name || c?.course_title || `Course #${c?.id}`); }
  function semesterLabel(s){
    if (s?.semester_no !== undefined && s?.semester_no !== null && String(s.semester_no).trim() !== ''){
      return `Semester ${String(s.semester_no)}`;
    }
    return s?.title || s?.name || `Semester #${s?.id}`;
  }
  function subjectTitle(sub){ return String(sub?.title || sub?.name || sub?.subject_title || `Subject #${sub?.id}`); }
  function subjectCode(sub){ return String(sub?.subject_code || sub?.code || sub?.paper_code || '').trim(); }
  function studentName(st){ return String(st?.name || st?.student_name || st?.full_name || 'Student'); }
  function studentRoll(st){ return String(st?.roll_no ?? st?.academic_details?.roll_no ?? '').trim(); }

  function resolveDepartmentIdFromCourse(course){
    return idNum(course?.department_id) || idNum(course?.department?.id) || idNum(course?.dept_id);
  }

  function keyOf(studentId, subjectId){ return String(studentId) + '_' + String(subjectId); }

  function getCell(studentId, subjectId){
    const k = keyOf(studentId, subjectId);
    if (!state.matrix.has(k)) state.matrix.set(k, { checked:false, attendance:'' });
    return state.matrix.get(k);
  }

  function setCell(studentId, subjectId, patch){
    const cell = getCell(studentId, subjectId);
    state.matrix.set(keyOf(studentId, subjectId), Object.assign({}, cell, patch));
  }

  function clearAllErrors(){
    document.querySelectorAll('.cell-error').forEach(x => x.classList.remove('cell-error'));
  }

  function updateSummaryBadge(){
    let selected = 0;
    state.students.forEach(st => {
      const sid = idNum(st?.student_id ?? st?.id);
      state.subjects.forEach(sub => {
        const subid = idNum(sub?.id);
        if (getCell(sid, subid).checked) selected++;
      });
    });
    $('badgeSummary').innerHTML = `<i class="fa fa-user-graduate me-1"></i> ${state.students.length} • <i class="fa fa-book me-1"></i> ${state.subjects.length} • <i class="fa fa-check-to-slot me-1"></i> ${selected}`;
  }

  function setEmpty(on){
    $('emptyState').style.display = on ? '' : 'none';
    $('tableRoot').style.display = on ? 'none' : '';
  }

  /**
   * ✅ Render Table
   */
  function renderTable(){
    if (!state.selectedCourseId || !state.selectedSemesterId){
      setEmpty(true);
      $('tableRoot').innerHTML = '';
      $('infoLine').textContent = 'Please select parameters.';
      $('semesterSelect').disabled = !state.selectedCourseId;
      return;
    }

    if (!state.students.length || !state.subjects.length){
      setEmpty(false);
      $('tableRoot').innerHTML = `<div class="empty-state"><i class="fa fa-circle-exclamation text-warning"></i><div class="title">Data Not Available</div><div class="subtitle">Check if students and subjects are mapped to this semester.</div></div>`;
      updateSummaryBadge();
      return;
    }

    setEmpty(false);
    const headColSpan = state.subjects.length;

    $('tableRoot').innerHTML = `
      <div class="ssa-table-wrap">
        <table class="ssa-table">
          <thead>
            <tr class="h1">
              <th class="sticky-col col-roll" rowspan="3">Roll No.</th>
              <th class="sticky-col-2 col-name" rowspan="3">Student Name</th>
              <th colspan="${headColSpan}" class="text-center py-3">Subject Wise Attendance (%)</th>
            </tr>
            <tr class="h2">
              ${state.subjects.map(s => `<th class="sub-col">${esc(subjectTitle(s))}</th>`).join('')}
            </tr>
            <tr class="h3">
              ${state.subjects.map(s => {
                const subid = idNum(s?.id);
                return `
                  <th class="sub-col">
                    <div class="sub-head">
                      <span class="code">${esc(subjectCode(s) || 'N/A')}</span>
                      <div class="all">
                        <input type="checkbox" class="chk-sub-all" data-subid="${esc(String(subid))}">
                        <label>Select All</label>
                      </div>
                    </div>
                  </th>`;
              }).join('')}
            </tr>
          </thead>
          <tbody>
            ${state.students.map(st => {
              const sid = idNum(st?.student_id ?? st?.id);
              return `
                <tr data-sid="${esc(String(sid))}">
                  <td class="sticky-col col-roll">${esc(studentRoll(st) || '—')}</td>
                  <td class="sticky-col-2 col-name">${esc(studentName(st))}</td>
                  ${state.subjects.map(sub => {
                    const subid = idNum(sub?.id);
                    const cell = getCell(sid, subid);
                    return `
                      <td class="sub-col" data-cell="1" data-sid="${esc(String(sid))}" data-subid="${esc(String(subid))}">
                        <div class="cellbox">
                          <input type="checkbox" class="chk-cell" data-sid="${esc(String(sid))}" data-subid="${esc(String(subid))}" ${cell.checked ? 'checked' : ''}>
                          <input type="number" class="att-input" min="0" max="100" step="0.01" placeholder="%" 
                            value="${esc(cell.attendance)}" style="${cell.checked ? '' : 'display:none'}"
                            data-sid="${esc(String(sid))}" data-subid="${esc(String(subid))}">
                          ${!cell.checked ? `<span class="cell-na">NA</span>` : ``}
                        </div>
                      </td>`;
                  }).join('')}
                </tr>`;
            }).join('')}
          </tbody>
        </table>
      </div>
    `;

    syncAllHeaderCheckboxes();
    updateSummaryBadge();
  }

  function syncAllHeaderCheckboxes(){
    document.querySelectorAll('.chk-sub-all').forEach(h => {
      const subid = idNum(h.dataset.subid);
      let total = state.students.length, on = 0;
      state.students.forEach(st => { if (getCell(idNum(st?.student_id ?? st?.id), subid).checked) on++; });
      h.indeterminate = (on > 0 && on < total);
      h.checked = (total > 0 && on === total);
    });
  }

  function applyExistingMapping(mapping){
    state.matrix.clear();
    pickArray(mapping?.subject_json).forEach(item => {
      const sid = idNum(item?.student_id), subid = idNum(item?.subject_id);
      if (sid && subid) setCell(sid, subid, { checked: true, attendance: String(item?.current_attendance ?? '') });
    });
  }

  async function loadCourses(){
    const res = await fetchWithTimeout(API.courses(), { headers: authHeaders() });
    state.courses = normalizeList(await res.json());
    $('courseSelect').innerHTML = `<option value="">Select Course</option>` + state.courses.map(c => `<option value="${esc(String(c.id))}">${esc(courseLabel(c))}</option>`).join('');
  }

  async function loadSemesters(courseId){
    const res = await fetchWithTimeout(API.semestersByCourse(courseId), { headers: authHeaders() });
    state.semesters = normalizeList(await res.json());
    $('semesterSelect').innerHTML = `<option value="">Select Semester</option>` + state.semesters.map(s => `<option value="${esc(String(s.id))}">${esc(semesterLabel(s))}</option>`).join('');
    $('semesterSelect').disabled = false;
  }

  async function loadAll(){
    if (!state.selectedCourseId || !state.selectedSemesterId) return;
    showLoading(true);
    try {
      const resSt = await fetchWithTimeout(API.studentsByScope(state.selectedCourseId, state.selectedSemesterId), { headers: authHeaders() });
      state.students = normalizeList(await resSt.json()).filter(x => x?.has_academic_details).map(x => ({
        ...x, student_id: idNum(x?.student_id) || idNum(x?.user_id) || idNum(x?.id),
        dept_id: idNum(x?.academic_details?.department_id)
      }));
      state.departmentId = state.students[0]?.dept_id || resolveDepartmentIdFromCourse(state.courses.find(c => idNum(c.id) === state.selectedCourseId));
      
      const resSub = await fetchWithTimeout(API.subjectsByScope(state.departmentId, state.selectedCourseId, state.selectedSemesterId), { headers: authHeaders() });
      state.subjects = normalizeList(await resSub.json());

      const resMap = await fetchWithTimeout(API.mappingCurrent(state.departmentId, state.selectedCourseId, state.selectedSemesterId), { headers: authHeaders() });
      state.mapping = normalizeList(await resMap.json())[0] || null;
      if (state.mapping) applyExistingMapping(state.mapping); else state.matrix.clear();

      renderTable();
    } catch(ex) { err(ex.message); } finally { showLoading(false); }
  }

  function validate(){
    clearAllErrors();
    let first = null, errs = 0;
    state.students.forEach(st => {
      const sid = idNum(st?.student_id ?? st?.id);
      state.subjects.forEach(sub => {
        const subid = idNum(sub?.id), cell = getCell(sid, subid);
        if (cell.checked){
          const n = Number(cell.attendance);
          if (cell.attendance.trim() === '' || isNaN(n) || n < 0 || n > 100){
            errs++;
            const td = document.querySelector(`td[data-sid="${sid}"][data-subid="${subid}"]`);
            td?.classList.add('cell-error');
            if(!first) first = td;
          }
        }
      });
    });
    if(errs){ first?.scrollIntoView({ behavior:'smooth', block:'center' }); return false; }
    return true;
  }

  async function save(){
    if (state.saving || !validate()) return;
    const payload = {
      department_id: state.departmentId,
      course_id: state.selectedCourseId,
      semester_id: state.selectedSemesterId,
      subject_json: [], status: 'active'
    };
    state.students.forEach(st => {
      const sid = idNum(st?.student_id ?? st?.id);
      state.subjects.forEach(sub => {
        const subid = idNum(sub?.id), cell = getCell(sid, subid);
        if(cell.checked) payload.subject_json.push({ student_id: sid, subject_id: subid, current_attendance: Number(cell.attendance) });
      });
    });

    if(!payload.subject_json.length) return err('Select at least one subject.');
    state.saving = true; showLoading(true);
    try {
      const isUpd = !!(state.mapping?.uuid || state.mapping?.id);
      const res = await fetchWithTimeout(isUpd ? API.update(state.mapping.uuid || state.mapping.id) : API.store(), {
        method: isUpd ? 'PATCH' : 'POST',
        headers: authHeaders({ 'Content-Type':'application/json' }),
        body: JSON.stringify(payload)
      });
      if(!res.ok) throw new Error('Save failed');
      ok('Data saved successfully');
      await loadAll();
    } catch(ex) { err(ex.message); } finally { state.saving = false; showLoading(false); }
  }

  function bindEvents(){
    $('courseSelect').addEventListener('change', async (e) => {
      state.selectedCourseId = idNum(e.target.value);
      state.selectedSemesterId = null;
      renderTable();
      if (state.selectedCourseId) await loadSemesters(state.selectedCourseId);
    });
    $('semesterSelect').addEventListener('change', async (e) => {
      state.selectedSemesterId = idNum(e.target.value);
      await loadAll();
    });
    $('btnRefresh').addEventListener('click', loadAll);
    $('btnSave').addEventListener('click', save);

    document.addEventListener('change', (e) => {
      const h = e.target.closest('.chk-sub-all');
      if (h) {
        const sid_list = state.students.map(st => idNum(st?.student_id ?? st?.id));
        sid_list.forEach(sid => setCell(sid, idNum(h.dataset.subid), { checked: h.checked }));
        renderTable();
      }
      const c = e.target.closest('.chk-cell');
      if (c) {
        setCell(idNum(c.dataset.sid), idNum(c.dataset.subid), { checked: c.checked });
        renderTable();
      }
    });

    document.addEventListener('input', (e) => {
      const inp = e.target.closest('.att-input');
      if (inp) {
        setCell(idNum(inp.dataset.sid), idNum(inp.dataset.subid), { checked: true, attendance: inp.value });
        updateSummaryBadge();
      }
    });
  }

  document.addEventListener('DOMContentLoaded', async () => {
    if (!token()) { window.location.href='/'; return; }
    bindEvents();
    showLoading(true);
    try { await loadCourses(); } finally { showLoading(false); }
  });
})();
</script>
@endpush