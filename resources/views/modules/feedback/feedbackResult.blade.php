{{-- resources/views/modules/feedbacks/manageFeedbackQuestions.blade.php --}}
@section('title','Feedback Results')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Feedback Results (Manage) – same UI DNA
 * ONLY CHANGES (as requested):
 * ✅ Grade system only (NO % anywhere)
 *   - Matrix shows COUNT of grades (5..1)
 *   - "Overall %" / "Average %" removed, replaced with Total + Avg Grade info
 * ✅ NEW CHANGE (as requested):
 *   - Faculty tab buttons (fac-tabbtn) show ONLY `name_short_form` from API (fallback to faculty_name if missing)
 * ✅ NEW CHANGE (as requested now):
 *   - Remove "Total ratings: X"
 *   - Remove ALL totals of every columns (no totals row counts)
 *   - Keep ONLY Avg. Grade
 *   - Apply same removal for exports (CSV/PDF)
 * ✅ NEW CHANGE (as requested now):
 *   - Make the lower (avg row) `.qtext` full 100% width
 *   - Remove all other td's from the lower portion (avg row has ONLY one td with colspan)
 * Everything else unchanged
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

.table-responsive > .table{ width:max-content; min-width:1100px; }
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

/* Faculty tabs (inside detail modal) */
.fac-tabsbar{
  display:flex;
  gap:8px;
  flex-wrap:wrap;
  padding:10px;
  border:1px solid var(--line-strong);
  background:var(--surface);
  border-radius:14px;
  box-shadow:var(--shadow-2);
}
.fac-tabbtn{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:8px 10px;
  border-radius:999px;
  border:1px solid var(--line-strong);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  color:var(--ink);
  font-weight:800;
  font-size:12.5px;
  cursor:pointer;
  transition:transform .08s ease, background .12s ease, border-color .12s ease;
  user-select:none;
  max-width: 100%;
}
.fac-tabbtn:active{transform:translateY(.5px)}
.fac-tabbtn i{opacity:.85}
.fac-tabbtn .nm{
  display:inline-block;
  max-width: 240px;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}
.fac-tabbtn.active{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  border-color:color-mix(in oklab, var(--primary-color) 30%, var(--line-strong));
  color:var(--primary-color);
}

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
/* ✅ ensure avg-row single cell truly behaves like full-width content */
.matrix .avgrow td.qtext{
  width:100%;
  text-align:center;
  white-space:normal;
}
.matrix .submeta{
  display:block;
  margin-top:6px;
  font-size:12px;
  color:var(--muted-color);
  font-weight:600;
}

/* ✅ Column colors (5..1) */
:root{
  --rate-5: #1f8a3b;      /* green */
  --rate-4: #2f7bbf;      /* blue */
  --rate-3: #c08a00;      /* amber */
  --rate-2: #c45a1a;      /* orange */
  --rate-1: #b3262e;      /* red */
}

.matrix thead th.col5{ background:color-mix(in oklab, var(--rate-5) 16%, var(--surface)); }
.matrix thead th.col4{ background:color-mix(in oklab, var(--rate-4) 16%, var(--surface)); }
.matrix thead th.col3{ background:color-mix(in oklab, var(--rate-3) 16%, var(--surface)); }
.matrix thead th.col2{ background:color-mix(in oklab, var(--rate-2) 16%, var(--surface)); }
.matrix thead th.col1{ background:color-mix(in oklab, var(--rate-1) 16%, var(--surface)); }

.matrix td.col5{ background:color-mix(in oklab, var(--rate-5) 10%, transparent); }
.matrix td.col4{ background:color-mix(in oklab, var(--rate-4) 10%, transparent); }
.matrix td.col3{ background:color-mix(in oklab, var(--rate-3) 10%, transparent); }
.matrix td.col2{ background:color-mix(in oklab, var(--rate-2) 10%, transparent); }
.matrix td.col1{ background:color-mix(in oklab, var(--rate-1) 10%, transparent); }

/* keep average row tint, but still allow subtle column colors */
.matrix .avgrow td.col5{ background:color-mix(in oklab, var(--rate-5) 12%, color-mix(in oklab, var(--primary-color) 6%, transparent)); }
.matrix .avgrow td.col4{ background:color-mix(in oklab, var(--rate-4) 12%, color-mix(in oklab, var(--primary-color) 6%, transparent)); }
.matrix .avgrow td.col3{ background:color-mix(in oklab, var(--rate-3) 12%, color-mix(in oklab, var(--primary-color) 6%, transparent)); }
.matrix .avgrow td.col2{ background:color-mix(in oklab, var(--rate-2) 12%, color-mix(in oklab, var(--primary-color) 6%, transparent)); }
.matrix .avgrow td.col1{ background:color-mix(in oklab, var(--rate-1) 12%, color-mix(in oklab, var(--primary-color) 6%, transparent)); }

/* Export modal helpers */
.export-pills{
  display:flex; flex-wrap:wrap; gap:8px;
  padding:10px;
  border:1px dashed var(--line-soft);
  border-radius:14px;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.export-pill{
  display:inline-flex; align-items:center; gap:8px;
  padding:8px 10px;
  border:1px solid var(--line-strong);
  border-radius:999px;
  background:var(--surface);
  font-weight:800;
  font-size:12.5px;
  color:var(--ink);
}
.export-pill input{ transform:translateY(1px); }
.export-pill i{opacity:.85}

/* Responsive toolbar */
@media (max-width: 768px){
  .fq-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .fq-toolbar .position-relative{min-width:100% !important}
  .toolbar-buttons{display:flex;gap:8px;flex-wrap:wrap}
  .toolbar-buttons .btn{flex:1;min-width:120px}
  .kv{grid-template-columns: 1fr;}
  .fac-tabbtn .nm{max-width: 180px;}
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
                  <th style="width:360px;">Feedback Post</th>
                  <th style="width:210px;">Department</th>
                  <th style="width:210px;">Course</th>
                  <th style="width:170px;">Semester</th>
                  <th style="width:250px;">Subject</th>
                  <th style="width:140px;">Section</th>
                  <th style="width:170px;">Publish</th>
                  {{-- ✅ Removed Expire --}}
                  <th style="width:170px;" class="text-end">Action</th>
                </tr>
              </thead>
              <tbody id="tbody-posts">
                <tr><td colspan="8" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
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
            <li>Click any row (or the eye button) to open the detailed view with <b>grade distribution</b> (Overall) and <b>faculty-wise breakdown</b> tabs.</li>
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
            {{-- ✅ Removed UUID in details --}}
          </div>

          <div class="detail-meta">
            <span class="chip"><i class="fa fa-calendar"></i> Publish: <span id="detailPublish">—</span></span>
            {{-- ✅ Removed Expire chip --}}
            <span class="chip"><i class="fa fa-users"></i> Participated: <b id="detailParticipated">0</b></span>
          </div>
        </div>

        <div class="kv mb-3">
          <div class="k">Department</div><div class="v" id="detailDept">—</div>
          <div class="k">Course</div><div class="v" id="detailCourse">—</div>
          <div class="k">Semester</div><div class="v" id="detailSem">—</div>
          <div class="k">Subject</div><div class="v" id="detailSub">—</div>
          <div class="k">Subject Code</div><div class="v" id="detailSubCode">—</div>
          <div class="k">Section</div><div class="v" id="detailSec">—</div>
          <div class="k">Academic Year</div><div class="v" id="detailAcadYear">—</div>
          <div class="k">Year</div><div class="v" id="detailYear">—</div>
        </div>

        <div class="mb-3" id="detailDescWrap" style="display:none;">
          <div class="fw-semibold mb-1"><i class="fa fa-align-left me-2"></i>Description</div>
          <div class="p-3" style="border:1px solid var(--line-strong);border-radius:14px;background:var(--surface);" id="detailDesc">—</div>
        </div>

        {{-- ✅ NEW: Attendance % filter (in modal, top of faculty/overall tabs) --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="pill"><i class="fa-solid fa-filter"></i>Attendance Filter</span>
            <div class="input-group" style="max-width:260px;">
              <span class="input-group-text"><i class="fa-solid fa-percent"></i></span>
              <input id="attMin" type="number" class="form-control" min="0" max="100" step="1" placeholder="Min attendance (e.g. 75)">
              <button id="btnAttApply" class="btn btn-outline-primary" type="button" title="Apply">
                <i class="fa fa-check"></i>
              </button>
              <button id="btnAttClear" class="btn btn-light" type="button" title="Clear">
                <i class="fa fa-rotate-left"></i>
              </button>
            </div>
          </div>
          <div class="small text-muted">
            Only students with attendance <b>&ge;</b> this percentage will be included.
          </div>
        </div>

        {{-- Faculty Tabs (auto) --}}
        <div id="detailFacultyTabs" class="fac-tabsbar mb-2" style="display:none;"></div>

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
          <div class="fw-semibold" id="detailMatrixTitle"><i class="fa fa-table me-2"></i>Question-wise Grade Distribution</div>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" id="btnExport" class="btn btn-outline-primary">
              <i class="fa-solid fa-file-export me-1"></i>Export
            </button>
            <div class="position-relative" style="min-width:320px;">
              <input id="detailSearch" type="search" class="form-control ps-5" placeholder="Search question…">
              <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
            </div>
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

{{-- Export Modal --}}
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title"><i class="fa-solid fa-file-export me-2"></i>Export Feedback Result</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="fw-bold mb-1" id="exportPostTitle">—</div>
        <div class="text-muted small mb-3" id="exportPostSub">—</div>

        <div class="fw-semibold mb-2"><i class="fa fa-square-check me-2"></i>Select what to export</div>
        <div id="exportTargets" class="export-pills">
          {{-- filled by JS --}}
        </div>

        <div class="alert alert-light mt-3 mb-0" style="border:1px dashed var(--line-soft);border-radius:14px;">
          <div class="small text-muted">
            <i class="fa fa-circle-info me-1"></i>
            ✅ CSV: Top academic details first, then blocks: <b>Overall</b>, then selected <b>Faculty</b> blocks (same format).<br>
            PDF is generated as pages: <b>Overall first</b>, then selected faculties.<br>
            <b>Note:</b> Exports use <b>grade counts</b> (NO percentages).
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="btnDoCsv" class="btn btn-outline-primary">
          <i class="fa-solid fa-file-csv me-1"></i>Export CSV
        </button>
        <button type="button" id="btnDoPdf" class="btn btn-primary">
          <i class="fa-solid fa-file-pdf me-1"></i>Export PDF
        </button>
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

{{-- PDF libs (client-side) --}}
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>

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

  function safeText(s){ return (s ?? '').toString().trim(); }

  /* =========================
   * ✅ Grade helpers (NO %)
   * ========================= */
  function normalizeCountMap(counts){
    const c = counts || {};
    const get = (k) => {
      const v = (c[k] ?? c[String(k)] ?? c[Number(k)] ?? 0);
      const n = Number(v);
      return Number.isFinite(n) ? n : 0;
    };
    return { '5': get(5), '4': get(4), '3': get(3), '2': get(2), '1': get(1) };
  }

  function computeAvgGradeFromCounts(counts){
    const c = normalizeCountMap(counts || {});
    const total = (c['5'] + c['4'] + c['3'] + c['2'] + c['1']);
    if (!total) return { avg: null, total: 0 };
    const sum = (5*c['5']) + (4*c['4']) + (3*c['3']) + (2*c['2']) + (1*c['1']);
    return { avg: Math.round((sum/total) * 100) / 100, total };
  }

  function downloadBlob(filename, mime, content){
    const blob = new Blob([content], { type: mime });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  }

  function csvEscape(v){
    const s = (v ?? '').toString();
    if (/[",\n\r]/.test(s)) return `"${s.replace(/"/g,'""')}"`;
    return s;
  }

  function nowStamp(){
    const d = new Date();
    const pad = (n)=> String(n).padStart(2,'0');
    return `${d.getFullYear()}${pad(d.getMonth()+1)}${pad(d.getDate())}_${pad(d.getHours())}${pad(d.getMinutes())}`;
  }

  function slugify(s){
    return (s||'')
      .toString()
      .toLowerCase()
      .replace(/[^a-z0-9]+/g,'-')
      .replace(/(^-|-$)/g,'')
      .slice(0,40) || 'feedback';
  }

  function facultyShortName(fullName){
    let s = (fullName ?? '').toString().trim();
    if (!s) return '';

    s = s.replace(/\s*\([^)]*\)\s*/g, ' ').trim();
    s = s.split(',')[0].trim();

    let parts = s.split(/\s+/).filter(Boolean);

    const drop = new Set([
      'ad','addl','additional','adv','advocate',
      'dr','dr.','prof','prof.','asst','asst.','assistant','assoc','assoc.','associate',
      'mr','mr.','mrs','mrs.','ms','ms.','miss','sir','madam',
      'sri','shri','smt','kumari',
      'er','er.','eng','eng.',
      'rev','rev.','fr','fr.'
    ]);

    const cleanKey = (w) => (w || '').toString().toLowerCase().replace(/\./g,'').trim();

    while (parts.length && drop.has(cleanKey(parts[0]))){
      parts.shift();
    }

    const tailDrop = new Set([
      'phd','ph.d','mtech','m.tech','btech','b.tech','me','m.e','be','b.e',
      'mba','mca','msc','m.sc','bsc','b.sc','ma','m.a','ba','b.a',
      'msw','bcom','b.com','mcom','m.com','bba','b.ed','m.ed','bed','med'
    ]);
    while (parts.length && tailDrop.has(cleanKey(parts[parts.length-1]))){
      parts.pop();
    }

    if (!parts.length) return '';
    if (parts.length === 1) return parts[0];

    const particles = new Set(['de','del','della','da','di','la','le','van','von','bin','ibn','al','der','den']);

    let start = parts.length - 1;
    while (start - 1 >= 0 && particles.has(parts[start - 1].toLowerCase())){
      start--;
    }

    const lastTokens = parts.slice(start);
    const firstTokens = parts.slice(0, start);

    const initialOf = (token) => {
      const segs = token.split(/[-]/).filter(Boolean);
      const letters = segs.map(seg => {
        const c = (seg || '').toString().replace(/[^A-Za-z]/g,'');
        return c ? c[0].toUpperCase() : '';
      }).filter(Boolean);
      return letters.join('');
    };

    const initials = firstTokens.map(initialOf).filter(Boolean);
    const lastName = lastTokens.join(' ');

    const out = (initials.length ? initials.join(' ') + ' ' : '') + lastName;
    return out.trim() || s;
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
    const detailPublish   = $('detailPublish');
    const detailParticipated = $('detailParticipated');

    const detailDept = $('detailDept');
    const detailCourse = $('detailCourse');
    const detailSem = $('detailSem');
    const detailSub = $('detailSub');
    const detailSubCode = $('detailSubCode');
    const detailSec = $('detailSec');
    const detailAcadYear = $('detailAcadYear');
    const detailYear = $('detailYear');

    const detailDescWrap = $('detailDescWrap');
    const detailDesc = $('detailDesc');

    const detailFacultyTabs = $('detailFacultyTabs');
    const detailMatrixTitle = $('detailMatrixTitle');

    const detailQuestions = $('detailQuestions');
    const detailSearch = $('detailSearch');

    // ✅ NEW: Attendance filter controls (detail modal)
    const attMin = $('attMin');
    const btnAttApply = $('btnAttApply');
    const btnAttClear = $('btnAttClear');

    // Export modal fields
    const btnExport = $('btnExport');
    const exportModalEl = $('exportModal');
    const exportModal = exportModalEl ? new bootstrap.Modal(exportModalEl) : null;
    const exportPostTitle = $('exportPostTitle');
    const exportPostSub = $('exportPostSub');
    const exportTargets = $('exportTargets');
    const btnDoCsv = $('btnDoCsv');
    const btnDoPdf = $('btnDoPdf');

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
        year: '',
        min_attendance: '' // ✅ NEW
      },
      rawHierarchy: [],
      postIndex: new Map(),
      flatPosts: [],
      total: 0,

      // detail tabs
      lastDetailPostKey: null,
      activeFacultyId: 0,          // "0" means Overall
      activeFacultyName: 'Overall',
      availableFaculty: [],        // [{id,name,short}]

      // last opened post cached for export
      lastDetailCtx: null,
      lastDetailPost: null,
      lastDetailQuestions: [],
    };

    function clampAttendance(v){
      const s = (v ?? '').toString().trim();
      if (s === '') return '';
      const n = Number(s);
      if (!Number.isFinite(n)) return '';
      const c = Math.max(0, Math.min(100, Math.round(n)));
      return String(c);
    }

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

      // ✅ NEW: Attendance filter passed to API (>=)
      if (f.min_attendance !== '') p.set('min_attendance', f.min_attendance);

      return p.toString();
    }

    function setLoadingRow(){
      if (!tbody) return;
      tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;
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
      const subTitleSet = new Map();
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
              const rawSubName = sub?.subject_name ?? '';
              const subCode = sub?.subject_code ?? '';
              const subNameOnly = (rawSubName ?? '').toString().trim();
              const subCodeOnly = (subCode ?? '').toString().trim();
              const subLabel = subNameOnly || subCodeOnly;
              if (subId !== null && subId !== undefined && subId !== '') subSet.set(String(subId), String(subLabel || ('Subject #' + subId)));
              if (subId !== null && subId !== undefined && subId !== '') subTitleSet.set(String(subId), String(subCodeOnly || ''));

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
                    subject_code: sub?.subject_code ?? null,
                    subject_name: subNameOnly ?? null,

                    section_id: sec?.section_id ?? null,
                    section_name: sec?.section_name ?? null,
                  };

                  const key = String(postId);
                  state.postIndex.set(key, { ctx, post });

                  state.flatPosts.push({
                    key,
                    post_id: postId,

                    // keep uuid in data (not displayed)
                    uuid: post?.feedback_post_uuid ?? '',

                    title: post?.title ?? '—',
                    short_title: post?.short_title ?? '',
                    publish_at: post?.publish_at ?? '',
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

      const fillSel = (sel, map, titleMap=null) => {
        if (!sel) return;
        const cur = sel.value || '';
        sel.innerHTML = `<option value="">All</option>` + Array.from(map.entries())
          .sort((a,b)=>String(a[1]).localeCompare(String(b[1])))
          .map(([id,name]) => {
            const t = titleMap ? (titleMap.get(String(id)) || '') : '';
            return `<option value="${esc(id)}"${t ? ` title="${esc(t)}"` : ''}>${esc(name)}</option>`;
          }).join('');
        if (cur) sel.value = cur;
      };

      fillSel(fDept, deptSet);
      fillSel(fCourse, courseSet);
      fillSel(fSem, semSet);
      fillSel(fSub, subSet, subTitleSet);
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
          r.ctx?.subject_name, r.ctx?.subject_code,
          r.ctx?.section_name
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

        const subName = (r.ctx?.subject_name ?? '').toString().trim();
        const subCode = (r.ctx?.subject_code ?? '').toString().trim();
        const subHtml = subCode
          ? `<div>${esc(subName || '—')}</div><div class="small text-muted mt-1"><i class="fa-solid fa-tag me-1"></i>${esc(subCode)}</div>`
          : `${esc(subName || '—')}`;

        const sec = (r.ctx?.section_name ?? '—') || '—';

        const title = (r.title || '—').toString();
        const st = (r.short_title || '').toString().trim();
        const subtitle = st ? `<div class="small text-muted mt-1"><i class="fa-regular fa-note-sticky me-1"></i>${esc(st)}</div>` : '';

        return `
          <tr class="tr-click" data-post="${esc(r.key)}" title="Click to view details">
            <td>
              <div class="fw-semibold">${esc(title)}</div>
              ${subtitle}
            </td>
            <td>${esc(d)}</td>
            <td>${esc(c)}</td>
            <td>${esc(s)}</td>
            <td>${subHtml}</td>
            <td>${esc(sec)}</td>
            <td>${esc(prettyDate(r.publish_at))}</td>
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

    /* ===========================
     * Detail: faculty tabs helpers
     * =========================== */

    // ✅ CHANGED: capture name_short_form from API and use it for tabs display
    function collectFacultyFromQuestions(questions){
      const map = new Map(); // id -> { name, short }

      (questions || []).forEach(q => {
        (Array.isArray(q.faculty) ? q.faculty : []).forEach(f => {
          const id = Number(f?.faculty_id);
          if (!Number.isFinite(id)) return;

          const name = (f?.faculty_name ?? '').toString().trim() || ('Faculty #' + id);

          // requested key: name_short_form (fallbacks are harmless)
          const short =
            (f?.name_short_form ?? f?.faculty_name_short_form ?? f?.short_name ?? f?.short_form ?? '')
              .toString().trim();

          const key = String(id);
          const prev = map.get(key);

          if (!prev){
            map.set(key, { name, short });
          } else {
            // keep best values without breaking
            const nextName = prev.name || name;
            const nextShort = (prev.short || short);
            map.set(key, { name: nextName, short: nextShort });
          }
        });
      });

      if (!map.has('0')) map.set('0', { name: 'Overall', short: 'Overall' });

      const out = [];
      out.push({ id: '0', name: (map.get('0')?.name || 'Overall'), short: (map.get('0')?.short || 'Overall') });

      Array.from(map.entries())
        .filter(([id]) => id !== '0')
        .sort((a,b)=>{
          const aa = (a[1]?.name || '').toString();
          const bb = (b[1]?.name || '').toString();
          return aa.localeCompare(bb);
        })
        .forEach(([id,obj]) => out.push({ id, name: obj?.name || ('Faculty #' + id), short: obj?.short || '' }));

      return out;
    }

    function renderFacultyTabs(){
      if (!detailFacultyTabs) return;

      const list = Array.isArray(state.availableFaculty) ? state.availableFaculty : [];
      if (list.length <= 1){
        detailFacultyTabs.style.display = 'none';
        return;
      }

      detailFacultyTabs.style.display = '';
      detailFacultyTabs.innerHTML = list.map(f => {
        const active = String(f.id) === String(state.activeFacultyId);
        const isOverall = String(f.id) === '0';

        const fullName = String(f.name || '');
        // ✅ Requested: show ONLY name_short_form on tab button (fallback to full name if missing)
        const displayName = isOverall ? 'Overall' : (String(f.short || '').trim() || fullName || ('Faculty #' + f.id));

        return `
          <button type="button"
            class="fac-tabbtn ${active ? 'active' : ''}"
            data-fid="${esc(String(f.id))}"
            data-fname="${esc(String(f.name || ''))}">
            <i class="fa-solid ${isOverall ? 'fa-star' : 'fa-user-tie'}"></i>
            <span class="nm" title="${esc(fullName || displayName)}">${esc(displayName)}</span>
          </button>
        `;
      }).join('');
    }

    function facultyRowForQuestion(q, fid){
      const arr = Array.isArray(q?.faculty) ? q.faculty : [];
      return arr.find(x => String(x?.faculty_id) === String(fid)) || null;
    }

    /* ===========================
     * ✅ Matrix renderer (COUNTS)
     * =========================== */
    function renderMatrixHtml({ questions, mode, fid, facName }){
      const rowCounts = [];

      // keep for avg-grade computation only (NOT displayed as totals)
      const totalCounts = {'5':0,'4':0,'3':0,'2':0,'1':0};

      const rowsHtml = (questions || []).map((q, idx) => {
        const qTitle = (q.question_title || '—').toString();
        const searchable = (qTitle || '').toLowerCase();

        let dist = null;
        if (mode === 'overall'){
          dist = q.distribution || null; // {counts,total,avg}
        } else {
          const f = facultyRowForQuestion(q, fid);
          dist = (f && f.distribution) ? f.distribution : null;
        }

        const counts = dist ? normalizeCountMap(dist.counts || {}) : {'5':0,'4':0,'3':0,'2':0,'1':0};
        const total = dist ? Number(dist.total || 0) : 0;

        // accumulate totals ONLY for avg grade (not for showing totals)
        ['5','4','3','2','1'].forEach(k => totalCounts[k] += Number(counts[k] || 0));

        rowCounts.push({
          idx: idx+1,
          question: qTitle,
          counts,
          total
        });

        const cell = (k) => total ? esc(String(counts[k] ?? 0)) : '—';

        return `
          <tr data-qrow="1" data-qsearch="${esc(searchable)}">
            <td class="qtext">${esc((idx+1) + '. ' + qTitle)}</td>
            <td class="col5">${cell('5')}</td>
            <td class="col4">${cell('4')}</td>
            <td class="col3">${cell('3')}</td>
            <td class="col2">${cell('2')}</td>
            <td class="col1">${cell('1')}</td>
          </tr>
        `;
      }).join('');

      const agg = computeAvgGradeFromCounts(totalCounts);
      const avgGrade = agg.avg;
      const totalRatings = agg.total; // kept for internal compatibility (not displayed/used)

      // ✅ CHANGED (NOW): avg row has ONLY ONE td and spans all columns => `.qtext` becomes full 100%
      const avgRowHtml = `
        <tr class="avgrow">
          <td class="qtext" colspan="6">
            <b>Avg grade:</b>
            ${avgGrade !== null ? `<b>${esc(String(avgGrade))}</b> / 5` : '—'}
            <span class="submeta">This is based on all submitted ratings.</span>
          </td>
        </tr>
      `;

      const html = `
        <div class="matrix-wrap">
          <table class="matrix">
            <thead>
              <tr>
                <th class="qcol">Question</th>
                <th class="col5">Outstanding [5]</th>
                <th class="col4">Excellent [4]</th>
                <th class="col3">Good [3]</th>
                <th class="col2">Fair [2]</th>
                <th class="col1">Not Satisfactory [1]</th>
              </tr>
            </thead>
            <tbody>
              ${rowsHtml}
              ${avgRowHtml}
            </tbody>
          </table>
        </div>
      `;

      return { html, rowCounts, totalCounts, avgGrade, totalRatings };
    }

    function renderDetail(postKey){
      const found = state.postIndex.get(String(postKey));
      if (!found) return;

      state.lastDetailPostKey = String(postKey);

      const ctx = found.ctx || {};
      const post = found.post || {};

      state.lastDetailCtx = ctx;
      state.lastDetailPost = post;

      const postName = (post.title || '—').toString();

      if (detailTitle) detailTitle.innerHTML = `<i class="fa fa-eye me-2"></i>${esc(postName)}`;
      if (detailPostName) detailPostName.textContent = postName;
      if (detailPublish) detailPublish.textContent = prettyDate(post.publish_at);

      if (detailDept) detailDept.textContent = ctx.department_name ?? '—';
      if (detailCourse) detailCourse.textContent = ctx.course_name ?? '—';
      if (detailSem) detailSem.textContent = ctx.semester_name ?? '—';
      if (detailSub) detailSub.textContent = (ctx.subject_name ?? '—') || '—';
      if (detailSubCode) detailSubCode.textContent = (ctx.subject_code ?? '—') || '—';
      if (detailSec) detailSec.textContent = ctx.section_name ?? '—';

      if (detailAcadYear) detailAcadYear.textContent = (post.academic_year ?? '—') || '—';
      if (detailYear) detailYear.textContent = (post.year ?? '—') || '—';

      if (detailParticipated) detailParticipated.textContent = String(post.participated_students ?? 0);

      // ✅ keep modal input in sync with current filter
      if (attMin) attMin.value = (state.filters.min_attendance ?? '');

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
      state.lastDetailQuestions = questions;

      if (!questions.length){
        if (detailMatrixTitle) detailMatrixTitle.innerHTML = `<i class="fa fa-table me-2"></i>Question-wise Grade Distribution`;
        if (detailFacultyTabs) detailFacultyTabs.style.display = 'none';
        detailQuestions.innerHTML = `<div class="text-center text-muted" style="padding:22px;">No question ratings found for this post.</div>`;
        return;
      }

      state.availableFaculty = collectFacultyFromQuestions(questions);

      if (!state.availableFaculty.find(x => String(x.id) === String(state.activeFacultyId))){
        state.activeFacultyId = 0;
        state.activeFacultyName = 'Overall';
      } else {
        const f = state.availableFaculty.find(x => String(x.id) === String(state.activeFacultyId));
        state.activeFacultyName = f?.name || 'Overall';
      }

      renderFacultyTabs();

      const fid = String(state.activeFacultyId);

      const resetDetailSearch = () => {
        if (detailSearch) detailSearch.value = '';
      };

      if (fid === '0'){
        if (detailMatrixTitle) detailMatrixTitle.innerHTML =
          `<i class="fa fa-table me-2"></i>Question-wise Grade Distribution <span class="pill ms-2"><i class="fa fa-star"></i>Overall</span>`;

        const { html } = renderMatrixHtml({ questions, mode: 'overall', fid: '0', facName: 'Overall' });
        detailQuestions.innerHTML = html;
        resetDetailSearch();
        return;
      }

      const facName = state.activeFacultyName || 'Faculty';
      if (detailMatrixTitle){
        detailMatrixTitle.innerHTML =
          `<i class="fa fa-table me-2"></i>Question-wise Grade Distribution <span class="pill ms-2"><i class="fa fa-user-tie"></i>${esc(facName)}</span>`;
      }

      const { html } = renderMatrixHtml({ questions, mode: 'faculty', fid, facName });
      detailQuestions.innerHTML = html;
      resetDetailSearch();
    }

    // Detail search
    detailSearch?.addEventListener('input', debounce(() => {
      const q = (detailSearch.value || '').toLowerCase().trim();
      const nodes = detailQuestions?.querySelectorAll('tr[data-qrow="1"]') || [];
      nodes.forEach(tr => {
        const hay = (tr.getAttribute('data-qsearch') || '').toLowerCase();
        tr.style.display = (!q || hay.includes(q)) ? '' : 'none';
      });
    }, 200));

    /* ===========================
     * Export (CSV/PDF) - COUNTS
     * =========================== */

    function buildBasicMetaRows(post, ctx){
      return [
        ['Feedback Post', safeText(post?.title)],
        ['Department', safeText(ctx?.department_name)],
        ['Course', safeText(ctx?.course_name)],
        ['Semester', safeText(ctx?.semester_name)],
        ['Subject', safeText(ctx?.subject_name)],
        ['Subject Code', safeText(ctx?.subject_code)],
        ['Section', safeText(ctx?.section_name)],
        ['Academic Year', safeText(post?.academic_year)],
        ['Year', safeText(post?.year)],
        ['Publish', safeText(post?.publish_at)],
        ['Participated', String(post?.participated_students ?? 0)],
      ];
    }

    function exportModalFill(){
      if (!exportTargets) return;

      const post = state.lastDetailPost || {};
      const ctx  = state.lastDetailCtx || {};

      if (exportPostTitle) exportPostTitle.textContent = safeText(post.title) || '—';
      if (exportPostSub) exportPostSub.textContent =
        `${safeText(ctx.department_name) || '—'} / ${safeText(ctx.course_name) || '—'} / ${safeText(ctx.subject_code) || '—'} / ${safeText(ctx.subject_name) || '—'}`;

      const list = Array.isArray(state.availableFaculty) ? state.availableFaculty : [{id:'0',name:'Overall',short:'Overall'}];

      const curActive = String(state.activeFacultyId || '0');
      exportTargets.innerHTML = list.map(f => {
        const isOverall = String(f.id) === '0';
        const checked = isOverall || (!isOverall && String(f.id) === curActive);

        const fullName = String(f.name || '');
        // keep export label aligned (uses short if present, else fallback)
        const displayName = isOverall ? 'Overall' : (String(f.short || '').trim() || fullName || ('Faculty #' + f.id));

        return `
          <label class="export-pill" title="${esc(fullName || displayName)}">
            <input type="checkbox" class="form-check-input m-0" data-fid="${esc(String(f.id))}" ${checked ? 'checked' : ''}>
            <i class="fa-solid ${isOverall ? 'fa-star' : 'fa-user-tie'}"></i>
            <span>${esc(displayName)}</span>
          </label>
        `;
      }).join('');
    }

    function getSelectedExportTargets(){
      const nodes = exportTargets?.querySelectorAll('input[type="checkbox"][data-fid]') || [];
      const selected = [];
      nodes.forEach(ch => {
        if (!ch.checked) return;
        selected.push(String(ch.getAttribute('data-fid')));
      });
      const list = Array.isArray(state.availableFaculty) ? state.availableFaculty : [];
      const ordered = [];
      if (selected.includes('0')) ordered.push('0');
      list.filter(x => String(x.id) !== '0')
        .forEach(x => { if (selected.includes(String(x.id))) ordered.push(String(x.id)); });
      return ordered;
    }

    function buildExportMatrixForTarget(fid){
      const questions = Array.isArray(state.lastDetailQuestions) ? state.lastDetailQuestions : [];

      const isOverall = String(fid) === '0';
      const facObj = state.availableFaculty.find(x => String(x.id) === String(fid));
      const facName = isOverall
        ? 'Overall'
        : (facObj?.name || ('Faculty #' + fid));

      const facShort = isOverall
        ? 'Overall'
        : (String(facObj?.short || '').trim() || facName);

      const matrix = renderMatrixHtml({
        questions,
        mode: isOverall ? 'overall' : 'faculty',
        fid: String(fid),
        facName
      });

      return { facName, facShort, isOverall, matrix };
    }

    function doExportCsv(){
      const selected = getSelectedExportTargets();
      if (!selected.length){
        err('Select at least one target (Overall/Faculty)');
        return;
      }

      const post = state.lastDetailPost || {};
      const ctx  = state.lastDetailCtx || {};

      const metaRows = buildBasicMetaRows(post, ctx);

      const lines = [];

      lines.push([ 'Academic Details', '' ].map(csvEscape).join(','));
      metaRows.forEach(([k,v]) => {
        lines.push([k, v ?? '—'].map(csvEscape).join(','));
      });

      lines.push('');
      lines.push('');

      const ordered = [];
      if (selected.includes('0')) ordered.push('0');
      selected.filter(x => x !== '0').forEach(x => ordered.push(x));

      const tableHeader = [
        'Q.No',
        'Question',
        'Outstanding [5] (Count)',
        'Excellent [4] (Count)',
        'Good [3] (Count)',
        'Fair [2] (Count)',
        'Not Satisfactory [1] (Count)'
      ];

      ordered.forEach((fid, idx) => {
        const { facShort, isOverall, matrix } = buildExportMatrixForTarget(fid);
        const sheetLabel = isOverall ? 'Overall' : `Faculty: ${facShort}`;

        lines.push([sheetLabel].map(csvEscape).join(','));
        lines.push(tableHeader.map(csvEscape).join(','));

        (matrix.rowCounts || []).forEach(r => {
          const c = normalizeCountMap(r.counts || {});
          lines.push([
            String(r.idx),
            r.question,
            String(c['5'] ?? 0),
            String(c['4'] ?? 0),
            String(c['3'] ?? 0),
            String(c['2'] ?? 0),
            String(c['1'] ?? 0),
          ].map(csvEscape).join(','));
        });

        // ✅ CHANGED: No totals / no ratings. Keep ONLY Avg Grade.
        const avg = matrix.avgGrade;
        lines.push([
          '',
          `Avg Grade: ${avg !== null ? avg : '—'}/5`,
          '',
          '',
          '',
          '',
          '',
        ].map(csvEscape).join(','));

        if (idx !== ordered.length - 1){
          lines.push('');
          lines.push('');
        }
      });

      const fname = `feedback_export_${slugify(post?.title)}_${nowStamp()}.csv`;
      downloadBlob(fname, 'text/csv;charset=utf-8', lines.join('\n'));
      ok('CSV exported');
    }

    function doExportPdf(){
      const selected = getSelectedExportTargets();
      if (!selected.length){
        err('Select at least one target (Overall/Faculty)');
        return;
      }

      const post = state.lastDetailPost || {};
      const ctx  = state.lastDetailCtx || {};

      const title = safeText(post.title) || 'Feedback Result';
      const metaRows = buildBasicMetaRows(post, ctx);

      const { jsPDF } = (window.jspdf || {});
      if (!jsPDF){
        err('PDF library not loaded');
        return;
      }

      const doc = new jsPDF({ orientation:'landscape', unit:'pt', format:'a4' });
      const pageW = doc.internal.pageSize.getWidth();
      const margin = 32;

      function addHeaderBlock(pageTitle){
        doc.setFont('helvetica','bold');
        doc.setFontSize(14);
        doc.text(pageTitle, margin, 36);

        doc.setFont('helvetica','normal');
        doc.setFontSize(9);

        let x = margin, y = 58;
        const colGap = 280;
        const rowH = 12;

        metaRows.forEach((kv, i) => {
          const col = (i % 2);
          const row = Math.floor(i / 2);
          const xx = x + (col * colGap);
          const yy = y + (row * rowH);
          doc.setFont('helvetica','bold');
          doc.text(String(kv[0]) + ':', xx, yy);
          doc.setFont('helvetica','normal');
          doc.text(String(kv[1] ?? '—'), xx + 90, yy);
        });

        doc.setDrawColor(200);
        doc.line(margin, 112, pageW - margin, 112);
      }

      function addMatrixTable(matrix, sheetLabel){
        const head = [['Question','Outstanding [5]','Excellent [4]','Good [3]','Fair [2]','Not Satisfactory [1]']];

        const body = (matrix.rowCounts || []).map(r => {
          const c = normalizeCountMap(r.counts || {});
          const q = `${r.idx}. ${r.question}`;
          return [q, String(c['5']), String(c['4']), String(c['3']), String(c['2']), String(c['1'])];
        });

        // ✅ CHANGED: No totals / no ratings. Keep ONLY Avg Grade.
        const avg = matrix.avgGrade;
        body.push([
          `Avg Grade: ${avg !== null ? avg : '—'}/5`,
          '',
          '',
          '',
          '',
          '',
        ]);

        doc.setFont('helvetica','bold');
        doc.setFontSize(11);
        doc.text(sheetLabel, margin, 138);

        doc.autoTable({
          startY: 150,
          head,
          body,
          theme: 'grid',
          styles: { font: 'helvetica', fontSize: 9, cellPadding: 6, overflow: 'linebreak' },
          headStyles: { fontStyle: 'bold' },
          columnStyles: {
            0: { cellWidth: 420 },
            1: { halign:'center' },
            2: { halign:'center' },
            3: { halign:'center' },
            4: { halign:'center' },
            5: { halign:'center' },
          },
          margin: { left: margin, right: margin },
          didParseCell: (data) => {
            // highlight last row (avg row)
            if (data.section === 'body' && data.row.index === body.length - 1){
              data.cell.styles.fillColor = [245,245,245];
              data.cell.styles.fontStyle = 'bold';
            }
          }
        });
      }

      const ordered = [];
      if (selected.includes('0')) ordered.push('0');
      selected.filter(x => x !== '0').forEach(x => ordered.push(x));

      ordered.forEach((fid, idx) => {
        if (idx > 0) doc.addPage();

        const target = buildExportMatrixForTarget(fid);
        const sheetLabel = target.isOverall ? 'Overall' : `Faculty: ${target.facShort}`;

        addHeaderBlock(title);
        addMatrixTable(target.matrix, sheetLabel);
      });

      const fname = `feedback_export_${slugify(post?.title)}_${nowStamp()}.pdf`;
      doc.save(fname);
      ok('PDF exported');
    }

    btnExport?.addEventListener('click', () => {
      if (!state.lastDetailPostKey){
        err('Open a feedback post first');
        return;
      }
      exportModalFill();
      exportModal && exportModal.show();
    });

    btnDoCsv?.addEventListener('click', () => {
      try{
        doExportCsv();
        exportModal && exportModal.hide();
      }catch(ex){
        err(ex?.message || 'CSV export failed');
      }
    });

    btnDoPdf?.addEventListener('click', () => {
      try{
        doExportPdf();
        exportModal && exportModal.hide();
      }catch(ex){
        err(ex?.message || 'PDF export failed');
      }
    });

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
        if (tbody) tbody.innerHTML = '';
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

    // Faculty tabs click (detail modal)
    document.addEventListener('click', (e) => {
      const b = e.target.closest('#detailFacultyTabs .fac-tabbtn[data-fid]');
      if (!b) return;

      const fid = b.dataset.fid;
      const fname = b.dataset.fname || 'Faculty';

      state.activeFacultyId = Number(fid || 0);
      state.activeFacultyName = fname;

      detailFacultyTabs?.querySelectorAll('.fac-tabbtn').forEach(x => {
        x.classList.toggle('active', x === b);
      });

      if (state.lastDetailPostKey) renderDetail(state.lastDetailPostKey);
    });

    // Row click / view click
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-action="view"][data-post]');
      const tr = e.target.closest('tr[data-post]');
      const postKey = btn?.dataset?.post || tr?.dataset?.post;
      if (!postKey) return;
      if (btn) e.preventDefault();

      state.activeFacultyId = 0;
      state.activeFacultyName = 'Overall';

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
        year: '',
        min_attendance: '' // ✅ NEW
      };

      if (fDept) fDept.value = '';
      if (fCourse) fCourse.value = '';
      if (fSem) fSem.value = '';
      if (fSub) fSub.value = '';
      if (fSec) fSec.value = '';
      if (fAcad) fAcad.value = '';
      if (fYear) fYear.value = '';

      // ✅ NEW
      if (attMin) attMin.value = '';

      loadResults();
    });

    btnRefresh?.addEventListener('click', () => loadResults());

    // ✅ NEW: Attendance filter apply/clear (modal)
    async function applyAttendanceFromModal(){
      const val = clampAttendance(attMin ? attMin.value : '');
      state.filters.min_attendance = val;
      if (attMin) attMin.value = val;

      const keepPost = state.lastDetailPostKey ? String(state.lastDetailPostKey) : null;

      await loadResults();

      if (keepPost && state.postIndex.has(keepPost)){
        renderDetail(keepPost);
      } else if (keepPost) {
        // if post disappears due to filter, keep modal open but show friendly message
        state.lastDetailPostKey = null;
        if (detailFacultyTabs) detailFacultyTabs.style.display = 'none';
        if (detailMatrixTitle) detailMatrixTitle.innerHTML = `<i class="fa fa-table me-2"></i>Question-wise Grade Distribution`;
        if (detailQuestions) detailQuestions.innerHTML =
          `<div class="text-center text-muted" style="padding:22px;">No results for this post under current attendance filter.</div>`;
      }
    }

    btnAttApply?.addEventListener('click', () => { applyAttendanceFromModal(); });

    btnAttClear?.addEventListener('click', () => {
      if (attMin) attMin.value = '';
      state.filters.min_attendance = '';
      applyAttendanceFromModal();
    });

    attMin?.addEventListener('keydown', (e) => {
      if (e.key === 'Enter'){
        e.preventDefault();
        applyAttendanceFromModal();
      }
    });

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
