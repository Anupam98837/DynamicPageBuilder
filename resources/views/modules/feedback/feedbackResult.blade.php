{{-- resources/views/modules/feedbacks/manageFeedbackQuestions.blade.php --}}
@section('title','Feedback Results')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Feedback Results (Manage) – same UI DNA
 * + Detail modal shows screenshot-like percentage matrix
 * ========================= */

.fq-wrap{padding:14px 4px}

/* Toolbar panel */
.fq-toolbar.panel{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:16px;
  box-shadow:var(--shadow-2);
  padding:12px;
}

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

.table-responsive > .table{ width:max-content; min-width:1200px; }
.table-responsive th, .table-responsive td{ white-space:nowrap; }

/* Tabs */
.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface)
}

/* Empty */
.empty{color:var(--muted-color)}
.pill{
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 10px;border-radius:999px;
  background:color-mix(in oklab, var(--primary-color) 10%, transparent);
  color:var(--primary-color);
  border:1px solid color-mix(in oklab, var(--primary-color) 18%, var(--line-soft));
  font-size:12px;font-weight:700;
}
.pill i{opacity:.85}

/* Clickable row */
.tr-click{cursor:pointer}
.tr-click:active{transform:translateY(.5px)}

/* Loading overlay */
#globalLoading.loading-overlay{ display:none !important; }
#globalLoading.loading-overlay.is-show{ display:flex !important; }

/* Detail modal head */
.detail-head{
  display:flex; align-items:flex-start; justify-content:space-between;
  gap:14px;
}
.detail-meta{display:flex; flex-wrap:wrap; gap:8px;}
.detail-meta .chip{
  display:inline-flex; align-items:center; gap:8px;
  padding:6px 10px; border-radius:999px;
  border:1px solid var(--line-strong);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  font-size:12px;
  color:var(--ink);
}
.detail-meta .chip i{opacity:.75}

/* Key-value info */
.kv{
  display:grid;
  grid-template-columns: 160px 1fr;
  gap:6px 12px;
  font-size:13px;
}
.kv .k{color:var(--muted-color)}
.kv .v{color:var(--ink); font-weight:700}

/* Screenshot-like matrix */
.matrix-wrap{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:auto;
  background:var(--surface);
  box-shadow:var(--shadow-2);
}
.matrix{
  width:max-content;
  min-width:100%;
  border-collapse:collapse;
}
.matrix th, .matrix td{
  border:1px solid var(--line-soft);
  padding:10px 10px;
  font-size:13px;
  vertical-align:top;
}
.matrix thead th{
  background:color-mix(in oklab, var(--surface) 90%, var(--page-hover));
  font-weight:800;
  color:var(--ink);
  text-align:center;
  white-space:nowrap;
}
.matrix .qcol{
  min-width:520px;
  max-width:720px;
  text-align:left;
}
.matrix td{
  text-align:center;
  font-weight:800;
}
.matrix td.qtext{
  text-align:left;
  font-weight:700;
  color:var(--ink);
}
.matrix .avgrow td{
  background:color-mix(in oklab, var(--primary-color) 6%, transparent);
}
.matrix .submeta{
  display:block;
  margin-top:6px;
  font-size:12px;
  color:var(--muted-color);
  font-weight:600;
}

/* Responsive toolbar */
@media (max-width: 768px){
  .fq-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .fq-toolbar .position-relative{min-width:100% !important}
  .toolbar-buttons{display:flex;gap:8px;flex-wrap:wrap}
  .toolbar-buttons .btn{flex:1;min-width:120px}
  .kv{grid-template-columns: 1fr;}
}
</style>
@endpush

@section('content')
<div class="fq-wrap">

  {{-- Loading Overlay --}}
  <div id="globalLoading" class="loading-overlay" style="display:none;">
    @include('partials.overlay')
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#tab-posts" role="tab" aria-selected="true">
        <i class="fa-solid fa-chart-simple me-2"></i>Feedback Posts
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-help" role="tab" aria-selected="false">
        <i class="fa-solid fa-circle-question me-2"></i>Help
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- POSTS TAB --}}
    <div class="tab-pane fade show active" id="tab-posts" role="tabpanel">

      {{-- Toolbar --}}
      <div class="row align-items-center g-2 mb-3 fq-toolbar panel">
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

          <div class="position-relative" style="min-width:320px;">
            <input id="searchInput" type="search" class="form-control ps-5" placeholder="Search by post / dept / course / subject…">
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
          <div class="toolbar-buttons">
            <button id="btnRefresh" class="btn btn-primary">
              <i class="fa fa-rotate me-1"></i> Refresh
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
                  <th style="width:340px;">Feedback Post</th>
                  <th style="width:210px;">Department</th>
                  <th style="width:210px;">Course</th>
                  <th style="width:170px;">Semester</th>
                  <th style="width:210px;">Subject</th>
                  <th style="width:140px;">Section</th>
                  <th style="width:170px;">Publish</th>
                  <th style="width:170px;">Expire</th>
                  <th style="width:130px;" class="text-end">Action</th>
                </tr>
              </thead>
              <tbody id="tbody-posts">
                <tr><td colspan="9" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-posts" class="empty p-4 text-center" style="display:none;">
            <i class="fa-solid fa-chart-simple mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No feedback results found for the current filters.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo-posts">—</div>
            <nav><ul id="pager-posts" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- HELP TAB --}}
    <div class="tab-pane fade" id="tab-help" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body">
          <div class="fw-bold mb-2"><i class="fa fa-circle-info me-2"></i>How this page works</div>
          <ul class="mb-0 text-muted">
            <li>This page shows aggregated results per <b>Feedback Post</b>.</li>
            <li>Click any row (or the eye button) to open the detailed view with <b>rating % distribution</b>.</li>
            <li>Use filters to narrow by Department/Course/Semester/Subject/Section and (optional) Academic Year / Year.</li>
          </ul>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-sliders me-2"></i>Filter Feedback Results</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">

          <div class="col-md-6">
            <label class="form-label">Department</label>
            <select id="f_department" class="form-select">
              <option value="">All</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Course</label>
            <select id="f_course" class="form-select">
              <option value="">All</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Semester</label>
            <select id="f_semester" class="form-select">
              <option value="">All</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Subject</label>
            <select id="f_subject" class="form-select">
              <option value="">All</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Section</label>
            <select id="f_section" class="form-select">
              <option value="">All</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Academic Year</label>
            <input id="f_academic_year" class="form-control" placeholder="e.g. 2025-26">
          </div>

          <div class="col-md-3">
            <label class="form-label">Year</label>
            <input id="f_year" class="form-control" inputmode="numeric" placeholder="e.g. 2026">
          </div>

        </div>

        <div class="alert alert-light mt-3 mb-0" style="border:1px dashed var(--line-soft);border-radius:14px;">
          <div class="small text-muted">
            <i class="fa fa-circle-info me-1"></i>
            Lists are auto-built from the latest loaded results. Hit <b>Refresh</b> after changing filters.
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

{{-- Detail Modal --}}
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="detailTitle"><i class="fa fa-eye me-2"></i>Feedback Post Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">

        <div class="detail-head mb-3">
          <div>
            <div class="fw-bold" id="detailPostName">—</div>
            <div class="text-muted small" id="detailPostUuid">—</div>
          </div>

          <div class="detail-meta">
            <span class="chip"><i class="fa fa-calendar"></i> Publish: <span id="detailPublish">—</span></span>
            <span class="chip"><i class="fa fa-hourglass-end"></i> Expire: <span id="detailExpire">—</span></span>
            <span class="chip"><i class="fa fa-users"></i> Participated: <b id="detailParticipated">0</b></span>
            <span class="chip"><i class="fa fa-star"></i> Out of: <b>5</b></span>
          </div>
        </div>

        <div class="kv mb-3">
          <div class="k">Department</div><div class="v" id="detailDept">—</div>
          <div class="k">Course</div><div class="v" id="detailCourse">—</div>
          <div class="k">Semester</div><div class="v" id="detailSem">—</div>
          <div class="k">Subject</div><div class="v" id="detailSub">—</div>
          <div class="k">Section</div><div class="v" id="detailSec">—</div>
          <div class="k">Academic Year</div><div class="v" id="detailAcadYear">—</div>
          <div class="k">Year</div><div class="v" id="detailYear">—</div>
        </div>

        <div class="mb-3" id="detailDescWrap" style="display:none;">
          <div class="fw-semibold mb-1"><i class="fa fa-align-left me-2"></i>Description</div>
          <div class="p-3" style="border:1px solid var(--line-strong);border-radius:14px;background:var(--surface);" id="detailDesc">—</div>
        </div>

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
          <div class="fw-semibold"><i class="fa fa-table me-2"></i>Question-wise Rating Distribution (%)</div>
          <div class="position-relative" style="min-width:320px;">
            <input id="detailSearch" type="search" class="form-control ps-5" placeholder="Search question…">
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>
        </div>

        <div id="detailQuestions">
          <div class="text-center text-muted" style="padding:22px;">—</div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>

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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
(() => {
  if (window.__FEEDBACK_RESULTS_MODULE_INIT__) return;
  window.__FEEDBACK_RESULTS_MODULE_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=300) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  // ✅ API called by this page
  const API = {
    results: (params) => `/api/feedback-results${params ? ('?' + params) : ''}`,
  };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  async function fetchWithTimeout(url, opts={}, ms=20000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try{
      return await fetch(url, { ...opts, signal: ctrl.signal });
    } finally {
      clearTimeout(t);
    }
  }

  function prettyDate(s){
    const v = (s ?? '').toString().trim();
    return v ? v : '—';
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const globalLoading = $('globalLoading');
    const showLoading = (v) => globalLoading?.classList.toggle('is-show', !!v);

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

    const perPageSel  = $('perPage');
    const searchInput = $('searchInput');
    const btnReset    = $('btnReset');
    const btnRefresh  = $('btnRefresh');
    const btnApply    = $('btnApplyFilters');

    const tbody = $('tbody-posts');
    const empty = $('empty-posts');
    const pager = $('pager-posts');
    const info  = $('resultsInfo-posts');

    // Filters modal fields
    const fDept   = $('f_department');
    const fCourse = $('f_course');
    const fSem    = $('f_semester');
    const fSub    = $('f_subject');
    const fSec    = $('f_section');
    const fAcad   = $('f_academic_year');
    const fYear   = $('f_year');

    const filterModalEl = $('filterModal');
    const filterModal   = filterModalEl ? new bootstrap.Modal(filterModalEl) : null;

    // Detail modal fields
    const detailModalEl = $('detailModal');
    const detailModal   = detailModalEl ? new bootstrap.Modal(detailModalEl) : null;

    const detailTitle     = $('detailTitle');
    const detailPostName  = $('detailPostName');
    const detailPostUuid  = $('detailPostUuid');
    const detailPublish   = $('detailPublish');
    const detailExpire    = $('detailExpire');
    const detailParticipated = $('detailParticipated');

    const detailDept = $('detailDept');
    const detailCourse = $('detailCourse');
    const detailSem = $('detailSem');
    const detailSub = $('detailSub');
    const detailSec = $('detailSec');
    const detailAcadYear = $('detailAcadYear');
    const detailYear = $('detailYear');

    const detailDescWrap = $('detailDescWrap');
    const detailDesc = $('detailDesc');

    const detailQuestions = $('detailQuestions');
    const detailSearch = $('detailSearch');

    // State
    const state = {
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      page: 1,
      q: '',
      filters: {
        department_id: '',
        course_id: '',
        semester_id: '',
        subject_id: '',
        section_id: '',
        academic_year: '',
        year: ''
      },
      rawHierarchy: [],
      postIndex: new Map(),
      flatPosts: [],
      total: 0,
    };

    function buildParams(){
      const p = new URLSearchParams();
      const f = state.filters;

      if (f.department_id) p.set('department_id', f.department_id);
      if (f.course_id) p.set('course_id', f.course_id);
      if (f.semester_id) p.set('semester_id', f.semester_id);
      if (f.subject_id) p.set('subject_id', f.subject_id);
      if (f.section_id) p.set('section_id', f.section_id);
      if (f.academic_year) p.set('academic_year', f.academic_year);
      if (f.year) p.set('year', f.year);

      return p.toString();
    }

    function setLoadingRow(){
      if (!tbody) return;
      tbody.innerHTML = `<tr><td colspan="9" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;
    }

    function setEmpty(show){
      if (empty) empty.style.display = show ? '' : 'none';
    }

    function renderPager(){
      if (!pager) return;
      const totalPages = Math.max(1, Math.ceil(state.total / state.perPage));
      const page = Math.min(state.page, totalPages);

      const item = (p, label, dis=false, act=false) => {
        if (dis) return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
        if (act) return `<li class="page-item active"><span class="page-link">${label}</span></li>`;
        return `<li class="page-item"><a class="page-link" href="#" data-page="${p}">${label}</a></li>`;
      };

      let html = '';
      html += item(Math.max(1, page-1), 'Previous', page<=1);
      const start = Math.max(1, page-2), end = Math.min(totalPages, page+2);
      for (let p=start; p<=end; p++) html += item(p, p, false, p===page);
      html += item(Math.min(totalPages, page+1), 'Next', page>=totalPages);

      pager.innerHTML = html;
    }

    // Flatten hierarchy and build filter dropdowns
    function rebuildFromHierarchy(){
      state.postIndex.clear();
      state.flatPosts = [];

      const deptSet = new Map();
      const courseSet = new Map();
      const semSet = new Map();
      const subSet = new Map();
      const secSet = new Map();

      (state.rawHierarchy || []).forEach(dept => {
        const dId = dept?.department_id ?? '';
        const dName = dept?.department_name ?? '';
        if (dId !== null && dId !== undefined && dId !== '') deptSet.set(String(dId), String(dName || ('Dept #' + dId)));

        (dept?.courses || []).forEach(course => {
          const cId = course?.course_id ?? '';
          const cName = course?.course_name ?? '';
          if (cId !== null && cId !== undefined && cId !== '') courseSet.set(String(cId), String(cName || ('Course #' + cId)));

          (course?.semesters || []).forEach(sem => {
            const sId = sem?.semester_id ?? '';
            const sName = sem?.semester_name ?? '';
            if (sId !== null && sId !== undefined && sId !== '') semSet.set(String(sId), String(sName || ('Semester #' + sId)));

            (sem?.subjects || []).forEach(sub => {
              const subId = sub?.subject_id ?? '';
              const subName = sub?.subject_name ?? '';
              if (subId !== null && subId !== undefined && subId !== '') subSet.set(String(subId), String(subName || ('Subject #' + subId)));

              (sub?.sections || []).forEach(sec => {
                const secId = sec?.section_id ?? '';
                const secName = sec?.section_name ?? '';
                if (secId !== null && secId !== undefined && secId !== '') secSet.set(String(secId), String(secName || ('Section #' + secId)));

                (sec?.feedback_posts || []).forEach(post => {
                  const postId = post?.feedback_post_id;
                  if (!postId) return;

                  const ctx = {
                    department_id: dept?.department_id ?? null,
                    department_name: dept?.department_name ?? null,
                    course_id: course?.course_id ?? null,
                    course_name: course?.course_name ?? null,
                    semester_id: sem?.semester_id ?? null,
                    semester_name: sem?.semester_name ?? null,
                    subject_id: sub?.subject_id ?? null,
                    subject_name: sub?.subject_name ?? null,
                    section_id: sec?.section_id ?? null,
                    section_name: sec?.section_name ?? null,
                  };

                  const key = String(postId);
                  state.postIndex.set(key, { ctx, post });

                  state.flatPosts.push({
                    key,
                    post_id: postId,
                    uuid: post?.feedback_post_uuid ?? '',
                    title: post?.title ?? '—',
                    short_title: post?.short_title ?? '',
                    publish_at: post?.publish_at ?? '',
                    expire_at: post?.expire_at ?? '',
                    description: post?.description ?? '',
                    academic_year: post?.academic_year ?? '',
                    year: post?.year ?? '',
                    participated_students: post?.participated_students ?? 0,
                    ctx
                  });
                });
              });
            });
          });
        });
      });

      const fillSel = (sel, map) => {
        if (!sel) return;
        const cur = sel.value || '';
        sel.innerHTML = `<option value="">All</option>` + Array.from(map.entries())
          .sort((a,b)=>String(a[1]).localeCompare(String(b[1])))
          .map(([id,name]) => `<option value="${esc(id)}">${esc(name)}</option>`).join('');
        if (cur) sel.value = cur;
      };

      fillSel(fDept, deptSet);
      fillSel(fCourse, courseSet);
      fillSel(fSem, semSet);
      fillSel(fSub, subSet);
      fillSel(fSec, secSet);

      state.total = state.flatPosts.length;
    }

    function getFilteredRows(){
      const q = (state.q || '').toLowerCase().trim();
      if (!q) return state.flatPosts;

      return state.flatPosts.filter(r => {
        const parts = [
          r.title, r.short_title, r.uuid,
          r.ctx?.department_name, r.ctx?.course_name, r.ctx?.semester_name,
          r.ctx?.subject_name, r.ctx?.section_name
        ].map(x => (x ?? '').toString().toLowerCase());
        return parts.some(p => p.includes(q));
      });
    }

    function renderTable(){
      const all = getFilteredRows();
      state.total = all.length;

      const totalPages = Math.max(1, Math.ceil(state.total / state.perPage));
      if (state.page > totalPages) state.page = totalPages;

      const start = (state.page - 1) * state.perPage;
      const pageRows = all.slice(start, start + state.perPage);

      if (info) info.textContent = `${state.total} result(s)`;

      if (!pageRows.length){
        if (tbody) tbody.innerHTML = '';
        setEmpty(true);
        renderPager();
        return;
      }

      setEmpty(false);

      tbody.innerHTML = pageRows.map(r => {
        const d = r.ctx?.department_name ?? '—';
        const c = r.ctx?.course_name ?? '—';
        const s = r.ctx?.semester_name ?? '—';
        const sub = r.ctx?.subject_name ?? '—';
        const sec = (r.ctx?.section_name ?? '—') || '—';

        const title = (r.title || '—').toString();
        const st = (r.short_title || '').toString().trim();
        const subtitle = st ? `<div class="small text-muted mt-1"><i class="fa-regular fa-note-sticky me-1"></i>${esc(st)}</div>` : '';

        return `
          <tr class="tr-click" data-post="${esc(r.key)}" title="Click to view details">
            <td>
              <div class="fw-semibold">${esc(title)}</div>
              <div class="small text-muted">${esc(r.uuid || '—')}</div>
              ${subtitle}
            </td>
            <td>${esc(d)}</td>
            <td>${esc(c)}</td>
            <td>${esc(s)}</td>
            <td>${esc(sub)}</td>
            <td>${esc(sec)}</td>
            <td>${esc(prettyDate(r.publish_at))}</td>
            <td>${esc(prettyDate(r.expire_at))}</td>
            <td class="text-end">
              <button type="button" class="btn btn-light btn-sm" data-action="view" data-post="${esc(r.key)}">
                <i class="fa fa-eye"></i>
              </button>
            </td>
          </tr>
        `;
      }).join('');

      renderPager();
    }

    function renderDetail(postKey){
      const found = state.postIndex.get(String(postKey));
      if (!found) return;

      const ctx = found.ctx || {};
      const post = found.post || {};

      const postName = (post.title || '—').toString();
      const postUuid = (post.feedback_post_uuid || '').toString();

      if (detailTitle) detailTitle.innerHTML = `<i class="fa fa-eye me-2"></i>${esc(postName)}`;
      if (detailPostName) detailPostName.textContent = postName;
      if (detailPostUuid) detailPostUuid.textContent = postUuid ? `UUID: ${postUuid}` : '—';
      if (detailPublish) detailPublish.textContent = prettyDate(post.publish_at);
      if (detailExpire) detailExpire.textContent = prettyDate(post.expire_at);

      if (detailDept) detailDept.textContent = ctx.department_name ?? '—';
      if (detailCourse) detailCourse.textContent = ctx.course_name ?? '—';
      if (detailSem) detailSem.textContent = ctx.semester_name ?? '—';
      if (detailSub) detailSub.textContent = ctx.subject_name ?? '—';
      if (detailSec) detailSec.textContent = ctx.section_name ?? '—';

      if (detailAcadYear) detailAcadYear.textContent = (post.academic_year ?? '—') || '—';
      if (detailYear) detailYear.textContent = (post.year ?? '—') || '—';

      if (detailParticipated) detailParticipated.textContent = String(post.participated_students ?? 0);

      const desc = (post.description ?? '').toString().trim();
      if (detailDescWrap && detailDesc){
        if (desc){
          detailDescWrap.style.display = '';
          detailDesc.innerHTML = desc;
        } else {
          detailDescWrap.style.display = 'none';
          detailDesc.innerHTML = '';
        }
      }

      const questions = Array.isArray(post.questions) ? post.questions : [];
      if (!questions.length){
        detailQuestions.innerHTML = `<div class="text-center text-muted" style="padding:22px;">No question ratings found for this post.</div>`;
        return;
      }

      // Build overall average based on TOTAL counts across all questions
      const overallCounts = {5:0,4:0,3:0,2:0,1:0};
      questions.forEach(q => {
        const dist = q.distribution || {};
        const c = dist.counts || {};
        [5,4,3,2,1].forEach(s => overallCounts[s] += Number(c[String(s)] || 0));
      });
      const overallTotal = [5,4,3,2,1].reduce((a,s)=>a+overallCounts[s], 0);
      const overallPct = {};
      [5,4,3,2,1].forEach(s => {
        overallPct[s] = overallTotal ? Math.round((overallCounts[s]*100)/overallTotal) : 0;
      });

      // Render table
      const rowsHtml = questions.map((q, idx) => {
        const qTitle = (q.question_title || '—').toString();
        const dist = q.distribution || {};
        const pct = dist.percent || {};
        const searchable = (qTitle || '').toLowerCase();

        return `
          <tr data-qrow="1" data-qsearch="${esc(searchable)}">
            <td class="qtext">
              ${esc((idx+1) + '. ' + qTitle)}
              ${q.group_title ? `<span class="submeta"><i class="fa-solid fa-layer-group me-1"></i>${esc(q.group_title)}</span>` : ``}
            </td>
            <td>${esc((pct['5'] ?? 0) + '%')}</td>
            <td>${esc((pct['4'] ?? 0) + '%')}</td>
            <td>${esc((pct['3'] ?? 0) + '%')}</td>
            <td>${esc((pct['2'] ?? 0) + '%')}</td>
            <td>${esc((pct['1'] ?? 0) + '%')}</td>
          </tr>
        `;
      }).join('');

      const tableHtml = `
        <div class="matrix-wrap">
          <table class="matrix">
            <thead>
              <tr>
                <th class="qcol">Question</th>
                <th>Outstanding [5]</th>
                <th>Excellent [4]</th>
                <th>Good [3]</th>
                <th>Fair [2]</th>
                <th>Not Satisfactory [1]</th>
              </tr>
            </thead>
            <tbody>
              ${rowsHtml}
              <tr class="avgrow">
                <td class="qtext"><b>Average</b></td>
                <td>${esc(overallPct[5] + '%')}</td>
                <td>${esc(overallPct[4] + '%')}</td>
                <td>${esc(overallPct[3] + '%')}</td>
                <td>${esc(overallPct[2] + '%')}</td>
                <td>${esc(overallPct[1] + '%')}</td>
              </tr>
            </tbody>
          </table>
        </div>
      `;

      detailQuestions.innerHTML = tableHtml;
    }

    // Detail search (filters table rows)
    detailSearch?.addEventListener('input', debounce(() => {
      const q = (detailSearch.value || '').toLowerCase().trim();
      const nodes = detailQuestions?.querySelectorAll('tr[data-qrow="1"]') || [];
      nodes.forEach(tr => {
        const hay = (tr.getAttribute('data-qsearch') || '').toLowerCase();
        tr.style.display = (!q || hay.includes(q)) ? '' : 'none';
      });
    }, 200));

    async function loadResults(){
      setLoadingRow();
      showLoading(true);

      try{
        const qs = buildParams();
        const url = API.results(qs);

        const res = await fetchWithTimeout(url, { headers: authHeaders() }, 25000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to load');

        state.rawHierarchy = Array.isArray(js.data) ? js.data : [];
        rebuildFromHierarchy();
        renderTable();

      }catch(ex){
        tbody.innerHTML = '';
        setEmpty(true);
        renderPager();
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        showLoading(false);
      }
    }

    // Pager click
    document.addEventListener('click', (e) => {
      const a = e.target.closest('#pager-posts a.page-link[data-page]');
      if (!a) return;
      e.preventDefault();
      const p = parseInt(a.dataset.page, 10);
      if (!Number.isFinite(p)) return;
      state.page = p;
      renderTable();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Row click / view click
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-action="view"][data-post]');
      const tr = e.target.closest('tr[data-post]');
      const postKey = btn?.dataset?.post || tr?.dataset?.post;
      if (!postKey) return;
      if (btn) e.preventDefault();

      renderDetail(postKey);
      if (detailSearch) detailSearch.value = '';
      detailModal && detailModal.show();
    });

    // Search, perPage
    searchInput?.addEventListener('input', debounce(() => {
      state.q = (searchInput.value || '').trim();
      state.page = 1;
      renderTable();
    }, 250));

    perPageSel?.addEventListener('change', () => {
      state.perPage = parseInt(perPageSel.value, 10) || 20;
      state.page = 1;
      renderTable();
    });

    // Filter apply/reset/refresh
    btnApply?.addEventListener('click', () => {
      state.filters.department_id = (fDept?.value || '').trim();
      state.filters.course_id = (fCourse?.value || '').trim();
      state.filters.semester_id = (fSem?.value || '').trim();
      state.filters.subject_id = (fSub?.value || '').trim();
      state.filters.section_id = (fSec?.value || '').trim();
      state.filters.academic_year = (fAcad?.value || '').trim();
      state.filters.year = (fYear?.value || '').trim();

      state.page = 1;
      filterModal && filterModal.hide();
      loadResults();
    });

    btnReset?.addEventListener('click', () => {
      state.page = 1;
      state.q = '';
      if (searchInput) searchInput.value = '';
      if (perPageSel) perPageSel.value = '20';
      state.perPage = 20;

      state.filters = {
        department_id: '',
        course_id: '',
        semester_id: '',
        subject_id: '',
        section_id: '',
        academic_year: '',
        year: ''
      };

      if (fDept) fDept.value = '';
      if (fCourse) fCourse.value = '';
      if (fSem) fSem.value = '';
      if (fSub) fSub.value = '';
      if (fSec) fSec.value = '';
      if (fAcad) fAcad.value = '';
      if (fYear) fYear.value = '';

      loadResults();
    });

    btnRefresh?.addEventListener('click', () => loadResults());

    // Init
    (async () => {
      showLoading(true);
      try{
        await loadResults();
        ok('Loaded feedback results');
      }catch(_){}
      finally{ showLoading(false); }
    })();
  });
})();
</script>
@endpush
