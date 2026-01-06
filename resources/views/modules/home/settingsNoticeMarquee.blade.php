{{-- resources/views/modules/home/settingsNoticeMarquee.blade.php --}}
@section('title','Notice Marquee Settings')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* =========================
  Notice Marquee Settings (Admin)
  - reference-inspired (Hero Carousel style)
  - updates:
    1) removed Title + Views fields (no title column / no views field in UI)
    2) added Pause on Hover toggle
    3) notice item link is optional (title required, link optional)
========================= */

.nm-wrap{padding:14px 4px;max-width:1140px;margin:16px auto 40px;overflow:visible}

/* Dropdown safety inside tables (scoped) */
.table-shell .dropdown{position:relative}
.table-shell .dd-toggle{border-radius:10px}
.nm-wrap .dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:230px;
  z-index:99999;
}
.nm-wrap .dropdown-menu.show{display:block !important}
.nm-wrap .dropdown-item{display:flex;align-items:center;gap:.6rem}
.nm-wrap .dropdown-item i{width:16px;text-align:center}
.nm-wrap .dropdown-item.text-danger{color:var(--danger-color) !important}

/* Tabs */
.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface)
}
.tab-content,.tab-pane{overflow:visible}

/* Cards */
.panel-card{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.panel-card .card-header{
  background:transparent;
  border-bottom:1px solid var(--line-soft);
}
.panel-card .card-title{margin:0;font-weight:800}
.helper{font-size:12.5px;color:var(--muted-color)}
.small{font-size:12.5px}

/* Table */
.table-shell.card{
  position:relative;
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.table-shell .card-body{overflow:visible}
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
.table-responsive{
  display:block;width:100%;
  max-width:100%;
  overflow-x:auto !important;
  overflow-y:visible !important;
  -webkit-overflow-scrolling:touch;
  position:relative;
}
.table-responsive > .table{width:max-content;min-width:1400px}
.table-responsive th,.table-responsive td{white-space:nowrap}

/* Chips */
.chip{
  display:inline-flex;align-items:center;gap:8px;
  padding:8px 10px;border-radius:999px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 90%, transparent);
  font-size:12.5px;
}
.chip i{opacity:.75}

/* Soft badges */
.badge-soft-success{
  background:color-mix(in oklab, var(--success-color) 12%, transparent);
  color:var(--success-color)
}
.badge-soft-muted{
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
  color:var(--muted-color)
}
.badge-soft-primary{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  color:var(--primary-color)
}

/* Switch */
.form-switch .form-check-input{width:2.75rem;height:1.35rem;cursor:pointer;}
.form-switch .form-check-input:focus{
  box-shadow:0 0 0 .2rem color-mix(in oklab, var(--primary-color) 25%, transparent);
  border-color:color-mix(in oklab, var(--primary-color) 40%, var(--line-strong));
}

/* Notice builder */
.nm-repeater{
  border:1px solid var(--line-strong);
  border-radius:14px;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  overflow:hidden;
}
.nm-repeater-top{
  display:flex;align-items:center;justify-content:space-between;
  gap:10px;padding:10px 12px;border-bottom:1px solid var(--line-soft);
}
.nm-repeater-body{padding:12px}
.nm-row{
  display:grid;
  grid-template-columns: 1.6fr 1.6fr .55fr auto;
  gap:10px;
  align-items:center;
  padding:10px;
  border:1px dashed color-mix(in oklab, var(--line-strong) 70%, transparent);
  border-radius:12px;
  background:color-mix(in oklab, var(--surface) 96%, transparent);
  margin-bottom:10px;
}
.nm-row:last-child{margin-bottom:0}
.nm-icon-btn{
  width:38px;height:38px;border-radius:12px;
  display:inline-flex;align-items:center;justify-content:center;
}

/* Preview */
.nm-preview{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.nm-preview-top{
  display:flex;align-items:center;justify-content:space-between;
  gap:10px;padding:10px 12px;border-bottom:1px solid var(--line-soft);
}
.nm-preview-body{padding:12px}
.nm-marquee{
  position:relative;
  overflow:hidden;
  border:1px solid var(--line-soft);
  border-radius:12px;
  background:color-mix(in oklab, var(--surface) 96%, transparent);
  padding:10px 12px;
}
.nm-marquee-track{
  display:inline-flex;
  gap:24px;
  white-space:nowrap;
  will-change:transform;
}
.nm-marquee-item{
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 10px;
  border-radius:999px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  font-size:12.5px;
}
.nm-marquee-item i{opacity:.7}

/* ✅ Pause-on-hover support (preview only) */
.nm-marquee.pause-on-hover:hover .nm-marquee-track{
  animation-play-state: paused !important;
}

/* Loading overlay */
.loading-overlay{
  position:fixed;inset:0;
  background:rgba(0,0,0,0.45);
  display:flex;justify-content:center;align-items:center;
  z-index:9999;
  backdrop-filter:blur(2px)
}
.loading-spinner{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;flex-direction:column;align-items:center;gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,0.3)
}
.spinner{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,0.3);
  border-top:4px solid var(--primary-color);
  animation:spin 1s linear infinite
}
@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

/* Button loading */
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
  .nm-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .nm-toolbar .position-relative{min-width:100% !important}
  .toolbar-buttons{display:flex;gap:8px;flex-wrap:wrap}
  .toolbar-buttons .btn{flex:1;min-width:140px}
  .nm-row{grid-template-columns:1fr;}
  .nm-row .nm-icon-btn{width:100%}
  .table-responsive > .table{min-width:1200px}
}
</style>
@endpush

@section('content')
<div class="nm-wrap">

  {{-- Loading Overlay --}}
  <div id="globalLoading" class="loading-overlay" style="display:none;">
    <div class="loading-spinner">
      <div class="spinner"></div>
      <div class="small">Loading…</div>
    </div>
  </div>

  {{-- Header --}}
  <div class="card panel-card mb-3">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div>
        <div class="d-flex align-items-center gap-2">
          <i class="fa-solid fa-bullhorn" style="opacity:.75;"></i>
          <h5 class="m-0 fw-bold">Notice Marquee Settings</h5>
        </div>
        <div class="helper mt-1">Manage status, notice items, and frontend marquee behavior (speed, pause on hover, direction, looping, publish/expire).</div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <span class="chip"><i class="fa-solid fa-shield-halved"></i> Admin module</span>
        <span class="chip"><i class="fa-solid fa-clock-rotate-left"></i> Versioned</span>
      </div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#nm-tab-current" role="tab" aria-selected="true">
        <i class="fa-solid fa-sliders me-2"></i>Current
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#nm-tab-versions" role="tab" aria-selected="false">
        <i class="fa-solid fa-layer-group me-2"></i>Versions
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#nm-tab-trash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Trash
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- ===================== CURRENT ===================== --}}
    <div class="tab-pane fade show active" id="nm-tab-current" role="tabpanel">

      <div class="row g-3">
        {{-- Current Settings Form --}}
        <div class="col-12 col-xl-7">
          <div class="card panel-card">
            <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
              <div>
                <div class="card-title">
                  <i class="fa-solid fa-gear me-2"></i>Live Settings
                </div>
                <div class="helper mt-1">Updates the latest record (behavior + items). Use “Save as New Version” for auditing.</div>
              </div>

              <div class="d-flex gap-2 flex-wrap" id="currentControls" style="display:none;">
                <button type="button" class="btn btn-light" id="btnDefaults">
                  <i class="fa fa-wand-magic-sparkles me-1"></i>Defaults
                </button>
                <button type="button" class="btn btn-outline-primary" id="btnReloadCurrent">
                  <i class="fa fa-rotate me-1"></i>Reload
                </button>
                <button type="button" class="btn btn-primary" id="btnSaveCurrent">
                  <i class="fa fa-floppy-disk me-1"></i>Save Current
                </button>
              </div>
            </div>

            <div class="card-body">
              <input type="hidden" id="currentUuid">
              <input type="hidden" id="currentId">

              <div class="row g-3">

                {{-- Summary chips --}}
                <div class="col-12">
                  <div class="d-flex flex-wrap gap-2" id="summaryChips">
                    <span class="chip"><i class="fa-regular fa-rectangle-list"></i><span id="chipItems">—</span></span>
                    <span class="chip"><i class="fa-solid fa-gauge"></i><span id="chipSpeed">—</span></span>
                    <span class="chip"><i class="fa-solid fa-arrows-left-right"></i><span id="chipDirection">—</span></span>
                    <span class="chip"><i class="fa-solid fa-hand"></i><span id="chipPause">—</span></span>
                    <span class="chip"><i class="fa-solid fa-toggle-on"></i><span id="chipStatus">—</span></span>
                  </div>
                </div>

                {{-- Status --}}
                <div class="col-md-6">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="status">
                    <label class="form-check-label" for="status"><b>Status</b></label>
                  </div>
                  <div class="helper mt-1">Sent as <code>status</code> string: <code>"1"</code> / <code>"0"</code>.</div>
                </div>

                {{-- Auto scroll --}}
                <div class="col-md-6">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="auto_scroll">
                    <label class="form-check-label" for="auto_scroll"><b>Auto Scroll</b></label>
                  </div>
                  <div class="helper mt-1">Auto scroll marquee on frontend.</div>
                </div>

                {{-- ✅ Pause on hover --}}
                <div class="col-md-6">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="pause_on_hover">
                    <label class="form-check-label" for="pause_on_hover"><b>Pause on Hover</b></label>
                  </div>
                  <div class="helper mt-1">When enabled, hovering the marquee pauses animation on frontend.</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Scroll Speed</label>
                  <input type="number" class="form-control" id="scroll_speed" min="1" max="600" step="1" placeholder="60">
                  <div class="helper mt-1">Higher = faster (recommended 30–120).</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Scroll Latency (ms)</label>
                  <input type="number" class="form-control" id="scroll_latency_ms" min="0" max="600000" step="10" placeholder="0">
                  <div class="helper mt-1">Delay before animation starts.</div>
                </div>

                <div class="col-md-6">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="loop">
                    <label class="form-check-label" for="loop"><b>Loop</b></label>
                  </div>
                  <div class="helper mt-1">Repeat continuously.</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Direction</label>
                  <select class="form-select" id="direction">
                    <option value="left">Left</option>
                    <option value="right">Right</option>
                  </select>
                  <div class="helper mt-1">Frontend direction.</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Publish At (optional)</label>
                  <input type="datetime-local" class="form-control" id="publish_at">
                  <div class="helper mt-1">Leave empty to publish immediately.</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Expire At (optional)</label>
                  <input type="datetime-local" class="form-control" id="expire_at">
                  <div class="helper mt-1">Leave empty for no expiry.</div>
                </div>

                {{-- Notice Items builder --}}
                <div class="col-12">
                  <div class="nm-repeater">
                    <div class="nm-repeater-top">
                      <div class="fw-semibold">
                        <i class="fa-solid fa-scroll me-2"></i>Notice Items (<code>notice_items_json</code>)
                      </div>
                      <div class="d-flex gap-2 flex-wrap" id="noticeControls" style="display:none;">
                        <button type="button" class="btn btn-light btn-sm" id="btnAddNotice">
                          <i class="fa fa-plus me-1"></i>Add Notice
                        </button>
                        <button type="button" class="btn btn-light btn-sm" id="btnClearNotices">
                          <i class="fa fa-eraser me-1"></i>Clear
                        </button>
                      </div>
                    </div>

                    <div class="nm-repeater-body">
                      <div id="noticesList"></div>
                      <div id="noticesEmpty" class="text-center text-muted py-3" style="display:none;">
                        <i class="fa-regular fa-circle-plus me-1"></i>No notices yet. Click <b>Add Notice</b>.
                      </div>
                    </div>
                  </div>

                  <div class="helper mt-2">
                    Stored in <code>notice_items_json</code> as an array:
                    <code>[{"title":"Admission open","url":"","sort_order":1}]</code>
                    <div class="mt-1"><b>Note:</b> <code>url</code> is optional, but <code>title</code> is required.</div>
                  </div>
                </div>

                <div class="col-12">
                  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                    <div class="helper">
                      <span class="me-2"><i class="fa-regular fa-id-card me-1"></i><span id="currentIdentity">—</span></span>
                      <span class="me-2"><i class="fa-regular fa-clock me-1"></i><span id="currentUpdated">—</span></span>
                    </div>
                    <div class="d-flex gap-2 flex-wrap" id="currentExtraControls" style="display:none;">
                      <button type="button" class="btn btn-outline-primary" id="btnSaveAsNew">
                        <i class="fa fa-code-branch me-1"></i>Save as New Version
                      </button>
                    </div>
                  </div>
                </div>

              </div>{{-- row --}}
            </div>
          </div>
        </div>

        {{-- Preview --}}
        <div class="col-12 col-xl-5">
          <div class="card panel-card">
            <div class="card-header py-3">
              <div class="card-title"><i class="fa-solid fa-display me-2"></i>Frontend Preview</div>
              <div class="helper mt-1">A quick visual preview of marquee behavior based on the current form.</div>
            </div>
            <div class="card-body">
              <div class="nm-preview">
                <div class="nm-preview-top">
                  <div class="fw-semibold"><i class="fa-solid fa-bullhorn me-2"></i><span id="pvTitle">Notice Marquee</span></div>
                  <span id="pvStatus" class="badge badge-soft-muted">—</span>
                </div>
                <div class="nm-preview-body">
                  <div class="d-flex flex-wrap gap-2 mb-2">
                    <span class="chip"><i class="fa-solid fa-gauge"></i><span id="pvSpeed">—</span></span>
                    <span class="chip"><i class="fa-solid fa-arrows-left-right"></i><span id="pvDirection">—</span></span>
                    <span class="chip"><i class="fa-solid fa-repeat"></i><span id="pvLoop">—</span></span>
                    <span class="chip"><i class="fa-solid fa-hand"></i><span id="pvPause">—</span></span>
                    <span class="chip"><i class="fa-solid fa-clock"></i><span id="pvLatency">—</span></span>
                  </div>

                  <div id="pvMarquee" class="nm-marquee" aria-label="Marquee preview">
                    <div id="marqueeTrack" class="nm-marquee-track"></div>
                  </div>

                  <div class="helper mt-2" id="pvHint">—</div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>{{-- row --}}
    </div>

    {{-- ===================== VERSIONS ===================== --}}
    <div class="tab-pane fade" id="nm-tab-versions" role="tabpanel">

      {{-- Toolbar --}}
      <div class="row align-items-center g-2 mb-3 nm-toolbar panel">
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
            <input id="searchInput" type="search" class="form-control ps-5" placeholder="Search by uuid/slug/status/direction…">
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
          <div class="toolbar-buttons" id="writeControls" style="display:none;">
            <button type="button" class="btn btn-primary" id="btnNewVersion">
              <i class="fa fa-plus me-1"></i> New Version
            </button>
          </div>
        </div>
      </div>

      {{-- Table --}}
      <div class="card table-shell">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:240px;">UUID</th>
                  <th style="width:240px;">Slug</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:120px;">Auto</th>
                  <th style="width:140px;">Pause</th>
                  <th style="width:130px;">Speed</th>
                  <th style="width:140px;">Latency</th>
                  <th style="width:110px;">Loop</th>
                  <th style="width:130px;">Direction</th>
                  <th style="width:170px;">Publish</th>
                  <th style="width:170px;">Expire</th>
                  <th style="width:120px;">Notices</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:140px;">By</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-versions">
                <tr><td colspan="15" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-versions" class="p-4 text-center" style="display:none;">
            <i class="fa-solid fa-layer-group mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No versions found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="resultsInfo-versions">—</div>
            <nav><ul id="pager-versions" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ===================== TRASH ===================== --}}
    <div class="tab-pane fade" id="nm-tab-trash" role="tabpanel">

      <div class="card table-shell">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:240px;">UUID</th>
                  <th style="width:320px;">Slug</th>
                  <th style="width:170px;">Deleted At</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:140px;">By</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-trash">
                <tr><td colspan="6" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="empty-trash" class="p-4 text-center" style="display:none;">
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
              <option value="1">Active only</option>
              <option value="0">Inactive only</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Direction</label>
            <select id="modal_direction" class="form-select">
              <option value="">All</option>
              <option value="left">Left</option>
              <option value="right">Right</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="modal_sort" class="form-select">
              <option value="-updated_at">Recently Updated</option>
              <option value="updated_at">Oldest Updated</option>
              <option value="-created_at">Newest Created</option>
              <option value="created_at">Oldest Created</option>
              <option value="-scroll_speed">Speed (Desc)</option>
              <option value="scroll_speed">Speed (Asc)</option>
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

{{-- View/Edit Modal --}}
<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form class="modal-content" id="itemForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="itemModalTitle">View</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="itemUuid">
        <input type="hidden" id="itemId">

        <div class="row g-3">
          <div class="col-12">
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <span class="chip"><i class="fa-regular fa-id-badge"></i><span id="itemIdentity">—</span></span>
              <span class="chip"><i class="fa-regular fa-clock"></i><span id="itemUpdated">—</span></span>
              <span class="chip"><i class="fa-regular fa-user"></i><span id="itemBy">—</span></span>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="m_status">
              <label class="form-check-label" for="m_status"><b>Status</b></label>
            </div>
            <div class="helper mt-1">Sent as <code>status</code> string: <code>"1"</code> / <code>"0"</code>.</div>
          </div>

          <div class="col-md-6">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="m_auto_scroll">
              <label class="form-check-label" for="m_auto_scroll"><b>Auto Scroll</b></label>
            </div>
          </div>

          {{-- ✅ Pause on hover (modal) --}}
          <div class="col-md-6">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="m_pause_on_hover">
              <label class="form-check-label" for="m_pause_on_hover"><b>Pause on Hover</b></label>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Scroll Speed</label>
            <input type="number" class="form-control" id="m_scroll_speed" min="1" max="600" step="1">
          </div>

          <div class="col-md-6">
            <label class="form-label">Scroll Latency (ms)</label>
            <input type="number" class="form-control" id="m_scroll_latency_ms" min="0" max="600000" step="10">
          </div>

          <div class="col-md-6">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="m_loop">
              <label class="form-check-label" for="m_loop"><b>Loop</b></label>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Direction</label>
            <select class="form-select" id="m_direction">
              <option value="left">Left</option>
              <option value="right">Right</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Publish At</label>
            <input type="datetime-local" class="form-control" id="m_publish_at">
          </div>

          <div class="col-md-6">
            <label class="form-label">Expire At</label>
            <input type="datetime-local" class="form-control" id="m_expire_at">
          </div>

          <div class="col-12">
            <div class="nm-repeater">
              <div class="nm-repeater-top">
                <div class="fw-semibold"><i class="fa-solid fa-scroll me-2"></i>Notice Items (<code>notice_items_json</code>)</div>
                <div class="d-flex gap-2 flex-wrap" id="modalNoticeControls" style="display:none;">
                  <button type="button" class="btn btn-light btn-sm" id="btnAddNoticeModal">
                    <i class="fa fa-plus me-1"></i>Add Notice
                  </button>
                  <button type="button" class="btn btn-light btn-sm" id="btnClearNoticesModal">
                    <i class="fa fa-eraser me-1"></i>Clear
                  </button>
                </div>
              </div>
              <div class="nm-repeater-body">
                <div id="noticesListModal"></div>
                <div id="noticesEmptyModal" class="text-center text-muted py-3" style="display:none;">
                  No notices.
                </div>
              </div>
            </div>
            <div class="helper mt-2">Link is optional; title is required.</div>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" id="saveBtn">
          <i class="fa fa-floppy-disk me-1"></i> Save Changes
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
  if (window.__NOTICE_MARQUEE_SETTINGS_V3__) return;
  window.__NOTICE_MARQUEE_SETTINGS_V3__ = true;

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
    try{ return await fetch(url, { ...opts, signal: ctrl.signal }); }
    finally{ clearTimeout(t); }
  }

  // ✅ backend expects string for status
  function statusFromSwitch(checked){ return checked ? '1' : '0'; }

  function normalizeBool(v, fallback=false){
    if (v === null || v === undefined || v === '') return fallback;
    if (typeof v === 'boolean') return v;
    if (typeof v === 'number') return v > 0;
    const s = String(v).toLowerCase().trim();
    if (['1','true','yes','y','on','enabled','active'].includes(s)) return true;
    if (['0','false','no','n','off','disabled','inactive'].includes(s)) return false;
    return fallback;
  }

  function normalizeInt(v, fallback=0){
    const n = Number(v);
    return Number.isFinite(n) ? n : fallback;
  }

  function statusBadge(val){
    const on = normalizeBool(val, false);
    return on
      ? `<span class="badge badge-soft-success">active</span>`
      : `<span class="badge badge-soft-muted">inactive</span>`;
  }

  function safeDir(v){
    const s = String(v || '').toLowerCase().trim();
    return (s === 'right') ? 'right' : 'left';
  }

  function toDatetimeLocal(val){
    if (!val) return '';
    const s = String(val).trim();
    if (s.includes('T')) return s.slice(0,16);
    if (s.includes(' ')) return s.replace(' ', 'T').slice(0,16);
    return s.slice(0,16);
  }

  function fromDatetimeLocal(val){
    const s = (val || '').toString().trim();
    return s ? s.replace('T', ' ') + ':00' : null;
  }

  function normalizeNotices(any){
    if (any == null) return [];
    let v = any;

    if (typeof v === 'string'){
      const t = v.trim();
      if (!t) return [];
      try{ v = JSON.parse(t); }catch(_){ return []; }
    }

    if (Array.isArray(v)){
      return v.map((x, i) => ({
        title: (x?.title ?? x?.text ?? x?.message ?? '').toString().trim(),
        url: (x?.url ?? x?.link ?? x?.href ?? '').toString().trim(),
        sort_order: Number.isFinite(Number(x?.sort_order)) ? Number(x.sort_order) : (i + 1),
      })).filter(x => x.title || x.url);
    }

    if (typeof v === 'object'){
      return Object.keys(v).map((k, i) => ({
        title: k.toString().trim(),
        url: (v[k] ?? '').toString().trim(),
        sort_order: i + 1,
      })).filter(x => x.title || x.url);
    }

    return [];
  }

  function makeNoticesEditor(cfg){
    const listEl   = $(cfg.listId);
    const emptyEl  = $(cfg.emptyId);
    const addBtn   = $(cfg.addBtnId);
    const clearBtn = $(cfg.clearBtnId);

    function rowTpl(idx, n){
      const title = esc(n?.title || '');
      const url   = esc(n?.url || '');
      const so    = esc((n?.sort_order ?? (idx+1)).toString());
      return `
        <div class="nm-row" data-idx="${idx}">
          <div>
            <label class="form-label mb-1">Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control nm-n-title" placeholder="e.g., Admission open for 2026" value="${title}">
          </div>
          <div>
            <label class="form-label mb-1">Link (optional)</label>
            <input type="text" class="form-control nm-n-url" placeholder="https://… or /path" value="${url}">
          </div>
          <div>
            <label class="form-label mb-1">Sort</label>
            <input type="number" class="form-control nm-n-sort" min="1" step="1" value="${so}">
          </div>
          <div class="d-flex gap-2 align-items-end">
            <button type="button" class="btn btn-light nm-icon-btn nm-n-remove" title="Remove">
              <i class="fa fa-trash"></i>
            </button>
          </div>
        </div>
      `;
    }

    function setEmptyState(){
      const has = (listEl?.querySelectorAll('.nm-row')?.length || 0) > 0;
      if (emptyEl) emptyEl.style.display = has ? 'none' : '';
    }

    function readFromRows(){
      const rows = Array.from(listEl?.querySelectorAll('.nm-row') || []);
      const arr = rows.map((r, i) => {
        const t = (r.querySelector('.nm-n-title')?.value ?? '').toString().trim();
        const u = (r.querySelector('.nm-n-url')?.value ?? '').toString().trim();
        const soRaw = r.querySelector('.nm-n-sort')?.value ?? '';
        const so = Number.isFinite(Number(soRaw)) && Number(soRaw) > 0 ? Number(soRaw) : (i + 1);
        return { title: t, url: u, sort_order: so };
      }).filter(x => x.title || x.url);

      arr.sort((a,b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
      return arr;
    }

    function sync(){
      setEmptyState();
      if (typeof cfg.onChange === 'function') cfg.onChange(readFromRows());
    }

    function setNotices(notices){
      const arr = normalizeNotices(notices);
      if (listEl) listEl.innerHTML = arr.map((n,i)=>rowTpl(i,n)).join('');
      setEmptyState();
      if (typeof cfg.onChange === 'function') cfg.onChange(readFromRows());
    }

    function addOne(){
      const current = readFromRows();
      current.push({ title:'', url:'', sort_order:(current.length + 1) });
      if (listEl) listEl.innerHTML = current.map((n,i)=>rowTpl(i,n)).join('');
      sync();
      const last = listEl?.querySelector('.nm-row:last-child .nm-n-title');
      last && last.focus();
    }

    function clearAll(){
      if (listEl) listEl.innerHTML = '';
      setEmptyState();
      if (typeof cfg.onChange === 'function') cfg.onChange([]);
    }

    listEl?.addEventListener('input', debounce(sync, 120));
    listEl?.addEventListener('click', (e) => {
      const rm = e.target.closest('.nm-n-remove');
      if (!rm) return;
      rm.closest('.nm-row')?.remove();
      sync();
    });

    addBtn?.addEventListener('click', addOne);
    clearBtn?.addEventListener('click', clearAll);

    return { setNotices, readFromRows, clearAll };
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const globalLoading = $('globalLoading');
    const showLoading = (v) => { if (globalLoading) globalLoading.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('toastSuccess');
    const toastErrEl = $('toastError');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;

    const ok = (m) => { const el=$('toastSuccessText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('toastErrorText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = (json=false) => {
      const h = { 'Authorization':'Bearer ' + token, 'Accept':'application/json' };
      if (json) h['Content-Type'] = 'application/json';
      return h;
    };

    // ========= API =========
    const API = {
      base:    '/api/notice-marquee',
      trash:   '/api/notice-marquee/trash',
      current: '/api/notice-marquee/current'
    };

    // permissions
    const ACTOR = { role:'' };
    let canWrite=false, canDelete=false;

    function computePermissions(){
      const r = (ACTOR.role || '').toLowerCase();
      const writeRoles  = ['admin','super_admin','director','principal','hod','technical_assistant','it_person'];
      const deleteRoles = ['admin','super_admin','director','principal'];

      canWrite = writeRoles.includes(r);
      canDelete = deleteRoles.includes(r);

      $('writeControls') && ($('writeControls').style.display = canWrite ? 'flex' : 'none');
      $('currentControls') && ($('currentControls').style.display = canWrite ? 'flex' : 'none');
      $('currentExtraControls') && ($('currentExtraControls').style.display = canWrite ? 'flex' : 'none');
      $('noticeControls') && ($('noticeControls').style.display = canWrite ? 'flex' : 'none');
      $('modalNoticeControls') && ($('modalNoticeControls').style.display = canWrite ? 'flex' : 'none');

      $('btnSaveCurrent') && ($('btnSaveCurrent').disabled = !canWrite);
      $('btnSaveAsNew') && ($('btnSaveAsNew').disabled = !canWrite);
    }

    async function fetchMe(){
      try{
        const res = await fetchWithTimeout('/api/users/me', { headers: authHeaders(false) }, 8000);
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

    function setBtnLoading(btn, loading){
      if (!btn) return;
      btn.disabled = !!loading;
      btn.classList.toggle('btn-loading', !!loading);
    }

    function setEmpty(el, show){ if (el) el.style.display = show ? '' : 'none'; }

    // defaults
    const DEFAULTS = {
      status: '1',
      auto_scroll: 1,
      pause_on_hover: 1,
      scroll_speed: 60,
      scroll_latency_ms: 0,
      loop: 1,
      direction: 'left',
      publish_at: null,
      expire_at: null,
      notice_items_json: []
    };

    // current elements
    const currentUuid = $('currentUuid');
    const currentId = $('currentId');
    const status = $('status');
    const autoScroll = $('auto_scroll');
    const pauseOnHover = $('pause_on_hover');
    const scrollSpeed = $('scroll_speed');
    const scrollLatency = $('scroll_latency_ms');
    const loop = $('loop');
    const direction = $('direction');
    const publishAt = $('publish_at');
    const expireAt = $('expire_at');
    const currentIdentity = $('currentIdentity');
    const currentUpdated = $('currentUpdated');

    // chips + preview
    const chipItems = $('chipItems');
    const chipSpeed = $('chipSpeed');
    const chipDirection = $('chipDirection');
    const chipPause = $('chipPause');
    const chipStatus = $('chipStatus');

    const pvStatus = $('pvStatus');
    const pvSpeed = $('pvSpeed');
    const pvDirection = $('pvDirection');
    const pvLoop = $('pvLoop');
    const pvPause = $('pvPause');
    const pvLatency = $('pvLatency');
    const pvHint = $('pvHint');
    const marqueeTrack = $('marqueeTrack');
    const pvMarquee = $('pvMarquee');

    // editors
    const noticesEditor = makeNoticesEditor({
      listId: 'noticesList',
      emptyId: 'noticesEmpty',
      addBtnId: 'btnAddNotice',
      clearBtnId: 'btnClearNotices',
      onChange: () => updatePreview(),
    });

    // versions
    const perPageSel = $('perPage');
    const searchInput = $('searchInput');
    const btnReset = $('btnReset');

    const tbodyVersions = $('tbody-versions');
    const emptyVersions = $('empty-versions');
    const pagerVersions = $('pager-versions');
    const infoVersions = $('resultsInfo-versions');

    // trash
    const tbodyTrash = $('tbody-trash');
    const emptyTrash = $('empty-trash');
    const pagerTrash = $('pager-trash');
    const infoTrash = $('resultsInfo-trash');

    // filter
    const filterModalEl = $('filterModal');
    const filterModal = filterModalEl ? new bootstrap.Modal(filterModalEl) : null;
    const modalStatus = $('modal_status');
    const modalDirection = $('modal_direction');
    const modalSort = $('modal_sort');
    const btnApplyFilters = $('btnApplyFilters');

    // item modal
    const itemModalEl = $('itemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;
    const itemModalTitle = $('itemModalTitle');
    const itemForm = $('itemForm');
    const saveBtn = $('saveBtn');

    const itemUuid = $('itemUuid');
    const itemId = $('itemId');
    const itemIdentity = $('itemIdentity');
    const itemUpdated = $('itemUpdated');
    const itemBy = $('itemBy');

    const mStatus = $('m_status');
    const mAutoScroll = $('m_auto_scroll');
    const mPauseOnHover = $('m_pause_on_hover');
    const mScrollSpeed = $('m_scroll_speed');
    const mScrollLatency = $('m_scroll_latency_ms');
    const mLoop = $('m_loop');
    const mDirection = $('m_direction');
    const mPublishAt = $('m_publish_at');
    const mExpireAt = $('m_expire_at');

    const modalNoticesEditor = makeNoticesEditor({
      listId: 'noticesListModal',
      emptyId: 'noticesEmptyModal',
      addBtnId: 'btnAddNoticeModal',
      clearBtnId: 'btnClearNoticesModal',
      onChange: () => {},
    });

    const btnDefaults = $('btnDefaults');
    const btnReloadCurrent = $('btnReloadCurrent');
    const btnSaveCurrent = $('btnSaveCurrent');
    const btnSaveAsNew = $('btnSaveAsNew');
    const btnNewVersion = $('btnNewVersion');

    // state
    const state = {
      filters: { q:'', status:'', direction:'', sort:'-updated_at' },
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      versions: { page:1, lastPage:1, items:[] },
      trash: { page:1, lastPage:1, items:[] },
      current: null,
    };

    function firstItemFromResponse(js){
      const item = js?.item || (js?.data && !Array.isArray(js.data) ? js.data : null);
      if (item) return item;
      const arr = Array.isArray(js?.data) ? js.data : (Array.isArray(js?.items) ? js.items : []);
      return arr[0] || null;
    }

    function updatePreview(){
      const isOn = !!status?.checked;
      const items = noticesEditor.readFromRows();
      const sp = normalizeInt(scrollSpeed?.value, DEFAULTS.scroll_speed);
      const lat = normalizeInt(scrollLatency?.value, DEFAULTS.scroll_latency_ms);
      const dir = safeDir(direction?.value || DEFAULTS.direction);
      const lp = !!loop?.checked;
      const auto = !!autoScroll?.checked;
      const poh = !!pauseOnHover?.checked;

      if (chipItems) chipItems.textContent = `Items: ${items.length}`;
      if (chipSpeed) chipSpeed.textContent = `Speed: ${sp}`;
      if (chipDirection) chipDirection.textContent = `Dir: ${dir}`;
      if (chipPause) chipPause.textContent = poh ? 'Pause: On' : 'Pause: Off';
      if (chipStatus) chipStatus.textContent = isOn ? 'Status: On' : 'Status: Off';

      if (pvStatus){
        pvStatus.className = 'badge ' + (isOn ? 'badge-soft-success' : 'badge-soft-muted');
        pvStatus.textContent = isOn ? 'Active' : 'Inactive';
      }
      if (pvSpeed) pvSpeed.textContent = `Speed ${sp}`;
      if (pvDirection) pvDirection.textContent = `Direction ${dir}`;
      if (pvLoop) pvLoop.textContent = lp ? 'Loop On' : 'Loop Off';
      if (pvPause) pvPause.textContent = poh ? 'Pause On Hover' : 'No Pause';
      if (pvLatency) pvLatency.textContent = `${lat}ms latency`;

      if (pvMarquee){
        pvMarquee.classList.toggle('pause-on-hover', poh);
      }

      if (pvHint){
        const pauseHint = poh ? 'Hover will pause.' : 'Hover will not pause.';
        pvHint.textContent = auto
          ? `Auto scroll enabled. ${pauseHint} Animation uses speed/latency/direction.`
          : `Auto scroll disabled. ${pauseHint} Frontend may render as static list.`;
      }

      // marquee track items (duplicate for loop feel)
      const showItems = (items.length ? items : [{title:'No notices yet', url:'', sort_order:1}])
        .map(x => ({ title:(x.title||'').trim(), url:(x.url||'').trim() }))
        .filter(x => x.title || x.url);

      const makeItem = (x) => `
        <span class="nm-marquee-item">
          <i class="fa-regular fa-circle-dot"></i>
          <span>${esc(x.title || '—')}</span>
        </span>
      `;

      if (marqueeTrack){
        marqueeTrack.innerHTML = (lp ? showItems.concat(showItems) : showItems).map(makeItem).join('');
        marqueeTrack.style.animation = '';
        marqueeTrack.style.transform = 'translateX(0)';
      }

      if (!auto || !isOn || !marqueeTrack) return;

      const duration = Math.max(6, Math.min(60, 3600 / Math.max(1, sp)));
      const from = (dir === 'left') ? '0%' : '-50%';
      const to   = (dir === 'left') ? '-50%' : '0%';

      const kfName = 'nmMove_' + Math.random().toString(16).slice(2);
      const styleId = 'nm_dyn_kf';
      let styleEl = document.getElementById(styleId);
      if (!styleEl){
        styleEl = document.createElement('style');
        styleEl.id = styleId;
        document.head.appendChild(styleEl);
      }
      styleEl.textContent = `
        @keyframes ${kfName}{
          0%{ transform: translateX(${from}); }
          100%{ transform: translateX(${to}); }
        }
      `;
      marqueeTrack.style.animation = `${kfName} ${duration}s linear ${Math.max(0, lat)}ms infinite`;
    }

    function fillCurrentForm(row){
      const r = row || {};

      if (currentUuid) currentUuid.value = r.uuid || '';
      if (currentId) currentId.value = r.id || '';

      status.checked = normalizeBool(r.status, true);
      autoScroll.checked = normalizeBool(r.auto_scroll, true);
      pauseOnHover.checked = normalizeBool(r.pause_on_hover, true);

      scrollSpeed.value = String(r.scroll_speed ?? DEFAULTS.scroll_speed);
      scrollLatency.value = String(r.scroll_latency_ms ?? DEFAULTS.scroll_latency_ms);
      loop.checked = normalizeBool(r.loop, true);
      direction.value = safeDir(r.direction ?? DEFAULTS.direction);

      publishAt.value = toDatetimeLocal(r.publish_at);
      expireAt.value = toDatetimeLocal(r.expire_at);

      const notices = normalizeNotices(r.notice_items_json ?? r.notices_json ?? r.notice_items ?? r.items_json ?? r.items ?? []);
      noticesEditor.setNotices(notices);

      if (currentIdentity){
        const parts = [];
        if (r.uuid) parts.push(`uuid: ${r.uuid}`);
        if (r.slug) parts.push(`slug: ${r.slug}`);
        currentIdentity.textContent = parts.length ? parts.join(' • ') : '—';
      }
      if (currentUpdated) currentUpdated.textContent = r.updated_at ? `Updated: ${r.updated_at}` : 'Updated: —';

      updatePreview();
    }

    function readCurrentPayload(){
      const items = noticesEditor.readFromRows();
      for (const n of items){
        if (!n.title) return { ok:false, error:'Each notice must have a title (remove empty rows).' };
        // ✅ link is optional (can be empty), controller must allow it
        if ((n.url || '').length > 500) return { ok:false, error:'Notice link must be max 500 characters.' };
      }

      const payload = {
        status: statusFromSwitch(!!status?.checked),
        auto_scroll: autoScroll.checked ? 1 : 0,
        pause_on_hover: pauseOnHover.checked ? 1 : 0,
        scroll_speed: normalizeInt(scrollSpeed.value, DEFAULTS.scroll_speed),
        scroll_latency_ms: normalizeInt(scrollLatency.value, DEFAULTS.scroll_latency_ms),
        loop: loop.checked ? 1 : 0,
        direction: safeDir(direction.value),
        publish_at: fromDatetimeLocal(publishAt.value),
        expire_at: fromDatetimeLocal(expireAt.value),
        notice_items_json: items.map(x => ({
          title: (x.title || '').trim(),
          url: (x.url || '').trim(), // optional
          sort_order: normalizeInt(x.sort_order, 1),
        })),
      };

      if (payload.scroll_speed < 1 || payload.scroll_speed > 600) return { ok:false, error:'Scroll speed must be between 1 and 600.' };
      if (payload.scroll_latency_ms < 0 || payload.scroll_latency_ms > 600000) return { ok:false, error:'Scroll latency must be between 0 and 600000.' };

      return { ok:true, payload };
    }

    function buildListUrl(kind){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state[kind].page));

      const q = (state.filters.q || '').trim();
      if (q) params.set('q', q);

      const s = state.filters.sort || '-updated_at';
      params.set('sort', s.startsWith('-') ? s.slice(1) : s);
      params.set('direction', s.startsWith('-') ? 'desc' : 'asc');

      if (state.filters.status !== ''){
        params.set('status', String(state.filters.status));
      }
      if (state.filters.direction){
        params.set('direction_filter', state.filters.direction);
      }

      return (kind === 'trash')
        ? `${API.trash}?${params.toString()}`
        : `${API.base}?${params.toString()}`;
    }

    function renderPager(kind){
      const pagerEl = (kind === 'trash') ? pagerTrash : pagerVersions;
      if (!pagerEl) return;

      const st = state[kind];
      const page = st.page;
      const totalPages = st.lastPage || 1;

      const item = (p, label, dis=false, act=false) => {
        if (dis) return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
        if (act) return `<li class="page-item active"><span class="page-link">${label}</span></li>`;
        return `<li class="page-item"><a class="page-link" href="#" data-page="${p}" data-kind="${kind}">${label}</a></li>`;
      };

      let html = '';
      html += item(Math.max(1, page-1), 'Previous', page<=1);
      const start = Math.max(1, page-2), end = Math.min(totalPages, page+2);
      for (let p=start; p<=end; p++) html += item(p, p, false, p===page);
      html += item(Math.min(totalPages, page+1), 'Next', page>=totalPages);

      pagerEl.innerHTML = html;
    }

    function noticesCount(r){
      const arr = normalizeNotices(r?.notice_items_json ?? r?.notices_json ?? r?.notice_items ?? r?.items ?? []);
      return arr.length;
    }

    function renderVersions(){
      const rows = state.versions.items || [];
      if (!rows.length){
        tbodyVersions.innerHTML = '';
        setEmpty(emptyVersions, true);
        renderPager('versions');
        return;
      }
      setEmpty(emptyVersions, false);

      tbodyVersions.innerHTML = rows.map(r => {
        const uuid = r.uuid || '';
        const by = r.created_by_name || r.created_by_email || '—';
        const updated = r.updated_at || '—';

        const stOn = normalizeBool(r.status, false);
        const auto = normalizeBool(r.auto_scroll, false);
        const poh = normalizeBool(r.pause_on_hover, false);
        const lp = normalizeBool(r.loop, false);

        const dir = safeDir(r.direction);
        const sp = (r.scroll_speed ?? '—');
        const lat = (r.scroll_latency_ms ?? '—');
        const slug = r.slug || '—';

        return `
          <tr data-uuid="${esc(uuid)}">
            <td><code>${esc(uuid)}</code></td>
            <td>${esc(String(slug))}</td>
            <td>${statusBadge(stOn)}</td>
            <td>${auto ? `<span class="badge badge-soft-success">Yes</span>` : `<span class="badge badge-soft-muted">No</span>`}</td>
            <td>${poh ? `<span class="badge badge-soft-success">Yes</span>` : `<span class="badge badge-soft-muted">No</span>`}</td>
            <td>${esc(String(sp))}</td>
            <td>${esc(String(lat))}</td>
            <td>${lp ? `<span class="badge badge-soft-success">Yes</span>` : `<span class="badge badge-soft-muted">No</span>`}</td>
            <td><span class="badge badge-soft-primary">${esc(dir)}</span></td>
            <td>${esc(r.publish_at || '—')}</td>
            <td>${esc(r.expire_at || '—')}</td>
            <td><span class="badge badge-soft-primary">${noticesCount(r)}</span></td>
            <td>${esc(updated)}</td>
            <td>${esc(by)}</td>
            <td class="text-end">
              <div class="dropdown">
                <button type="button" class="btn btn-light btn-sm dd-toggle" aria-expanded="false" title="Actions">
                  <i class="fa fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>
                  ${canWrite ? `<li><button type="button" class="dropdown-item" data-action="edit"><i class="fa fa-pen-to-square"></i> Edit</button></li>` : ``}
                  ${canDelete ? `<li><hr class="dropdown-divider"></li>
                    <li><button type="button" class="dropdown-item text-danger" data-action="delete"><i class="fa fa-trash"></i> Delete</button></li>` : ``}
                </ul>
              </div>
            </td>
          </tr>
        `;
      }).join('');

      renderPager('versions');
    }

    function renderTrash(){
      const rows = state.trash.items || [];
      if (!rows.length){
        tbodyTrash.innerHTML = '';
        setEmpty(emptyTrash, true);
        renderPager('trash');
        return;
      }
      setEmpty(emptyTrash, false);

      tbodyTrash.innerHTML = rows.map(r => {
        const uuid = r.uuid || '';
        const by = r.created_by_name || r.created_by_email || '—';
        const slug = r.slug || '—';
        return `
          <tr data-uuid="${esc(uuid)}">
            <td><code>${esc(uuid)}</code></td>
            <td>${esc(String(slug))}</td>
            <td>${esc(r.deleted_at || '—')}</td>
            <td>${esc(r.updated_at || '—')}</td>
            <td>${esc(by)}</td>
            <td class="text-end">
              <div class="dropdown">
                <button type="button" class="btn btn-light btn-sm dd-toggle" aria-expanded="false" title="Actions">
                  <i class="fa fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><button type="button" class="dropdown-item" data-action="restore"><i class="fa fa-rotate-left"></i> Restore</button></li>
                  ${canDelete ? `<li><button type="button" class="dropdown-item text-danger" data-action="force"><i class="fa fa-skull-crossbones"></i> Delete Permanently</button></li>` : ``}
                </ul>
              </div>
            </td>
          </tr>
        `;
      }).join('');

      renderPager('trash');
    }

    async function loadCurrent(){
      try{
        let res = await fetchWithTimeout(API.current, { headers: authHeaders(false) }, 12000);
        if (res.status === 404) throw new Error('no-current');
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        let js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to load current');
        let item = firstItemFromResponse(js);

        if (!item){
          const params = new URLSearchParams();
          params.set('per_page','1');
          params.set('page','1');
          params.set('sort','updated_at');
          params.set('direction','desc');
          res = await fetchWithTimeout(`${API.base}?${params.toString()}`, { headers: authHeaders(false) }, 12000);
          js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to load current');
          item = firstItemFromResponse(js);
        }

        state.current = item || null;
        fillCurrentForm(item || DEFAULTS);
      }catch(e){
        fillCurrentForm(DEFAULTS);
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    async function loadList(kind){
      const tbody = (kind === 'trash') ? tbodyTrash : tbodyVersions;
      const cols = (kind === 'trash') ? 6 : 15;
      tbody.innerHTML = `<tr><td colspan="${cols}" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;

      try{
        const res = await fetchWithTimeout(buildListUrl(kind), { headers: authHeaders(false) }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }
        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to load');

        const items = Array.isArray(js.data) ? js.data : (Array.isArray(js.items) ? js.items : []);
        const p = js.pagination || js.meta || {};

        let out = items;
        if (state.filters.direction){
          const d = state.filters.direction.toLowerCase();
          out = out.filter(x => safeDir(x?.direction) === d);
        }

        state[kind].items = out;
        state[kind].lastPage = parseInt(p.last_page || p.lastPage || 1, 10) || 1;

        const total = (p.total ?? out.length);
        const infoEl = (kind === 'trash') ? infoTrash : infoVersions;
        if (infoEl) infoEl.textContent = `${total} result(s)`;

        if (kind === 'trash') renderTrash();
        else renderVersions();
      }catch(e){
        state[kind].items = [];
        state[kind].lastPage = 1;
        if (kind === 'trash') renderTrash();
        else renderVersions();
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    async function sendJsonWithFallback(url, payload, preferredMethod){
      const methods = preferredMethod === 'PATCH' ? ['PATCH','PUT'] : ['PUT','PATCH'];
      let lastErr = null;

      for (const m of methods){
        try{
          const res = await fetchWithTimeout(url, {
            method: m,
            headers: authHeaders(true),
            body: JSON.stringify(payload)
          }, 20000);

          if (res.status === 405) continue;

          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false){
            let msg = js?.message || 'Save failed';
            if (js?.errors){
              const k = Object.keys(js.errors)[0];
              if (k && js.errors[k] && js.errors[k][0]) msg = js.errors[k][0];
            }
            throw new Error(msg);
          }
          return { ok:true, js };
        }catch(ex){
          lastErr = ex;
        }
      }
      return { ok:false, error: lastErr?.message || 'Save failed' };
    }

    // defaults
    btnDefaults?.addEventListener('click', () => {
      fillCurrentForm(DEFAULTS);
      ok('Defaults applied (not saved).');
    });

    btnReloadCurrent?.addEventListener('click', async () => {
      showLoading(true);
      await loadCurrent();
      showLoading(false);
      ok('Reloaded current settings');
    });

    // live preview updates
    [status, autoScroll, pauseOnHover, scrollSpeed, scrollLatency, loop, direction, publishAt, expireAt]
      .forEach(el => el?.addEventListener('input', debounce(updatePreview, 80)));
    [status, autoScroll, pauseOnHover, loop, direction].forEach(el => el?.addEventListener('change', updatePreview));

    async function saveCurrent(mode){
      if (!canWrite) return;

      const read = readCurrentPayload();
      if (!read.ok){ err(read.error || 'Invalid values'); return; }

      const uuid = (currentUuid?.value || '').trim();
      const isCreate = (mode === 'new') || (!uuid);

      setBtnLoading(btnSaveCurrent, mode !== 'new');
      setBtnLoading(btnSaveAsNew, mode === 'new');
      showLoading(true);

      try{
        if (isCreate){
          const res = await fetchWithTimeout(API.base, {
            method: 'POST',
            headers: authHeaders(true),
            body: JSON.stringify(read.payload)
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

          ok(mode === 'new' ? 'Saved as new version' : 'Created');
        } else {
          const out = await sendJsonWithFallback(`${API.base}/${encodeURIComponent(uuid)}`, read.payload, 'PATCH');
          if (!out.ok) throw new Error(out.error || 'Update failed');
          ok('Saved current settings');
        }

        await loadCurrent();
        await Promise.all([loadList('versions'), loadList('trash')]);
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        setBtnLoading(btnSaveCurrent, false);
        setBtnLoading(btnSaveAsNew, false);
        showLoading(false);
      }
    }

    btnSaveCurrent?.addEventListener('click', () => saveCurrent('upsert'));
    btnSaveAsNew?.addEventListener('click', async () => {
      if (!canWrite) return;
      const conf = await Swal.fire({
        title: 'Create a new version?',
        text: 'This saves the current form as a new DB row.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Create Version'
      });
      if (!conf.isConfirmed) return;
      saveCurrent('new');
    });

    // versions toolbar
    searchInput?.addEventListener('input', debounce(() => {
      state.filters.q = (searchInput.value || '').trim();
      state.versions.page = 1;
      loadList('versions');
    }, 320));

    perPageSel?.addEventListener('change', () => {
      state.perPage = parseInt(perPageSel.value, 10) || 20;
      state.versions.page = 1;
      state.trash.page = 1;
      loadList('versions');
      loadList('trash');
    });

    btnReset?.addEventListener('click', () => {
      state.filters = { q:'', status:'', direction:'', sort:'-updated_at' };
      state.perPage = 20;

      if (searchInput) searchInput.value = '';
      if (perPageSel) perPageSel.value = '20';
      if (modalStatus) modalStatus.value = '';
      if (modalDirection) modalDirection.value = '';
      if (modalSort) modalSort.value = '-updated_at';

      state.versions.page = 1;
      state.trash.page = 1;
      loadList('versions');
      loadList('trash');
      ok('Filters reset');
    });

    filterModalEl?.addEventListener('show.bs.modal', () => {
      if (modalStatus) modalStatus.value = state.filters.status || '';
      if (modalDirection) modalDirection.value = state.filters.direction || '';
      if (modalSort) modalSort.value = state.filters.sort || '-updated_at';
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.status = (modalStatus?.value ?? '');
      state.filters.direction = (modalDirection?.value ?? '');
      state.filters.sort = (modalSort?.value ?? '-updated_at');
      state.versions.page = 1;
      filterModal && filterModal.hide();
      loadList('versions');
    });

    // pager click
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a.page-link[data-page]');
      if (!a) return;
      e.preventDefault();
      const kind = a.dataset.kind;
      const p = parseInt(a.dataset.page, 10);
      if (!kind || Number.isNaN(p)) return;
      if (p === state[kind].page) return;
      state[kind].page = p;
      loadList(kind);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    function findRow(kind, uuid){
      return (state[kind].items || []).find(x => x?.uuid === uuid) || null;
    }

    function fillModalFromRow(r, viewOnly){
      const row = r || {};
      itemUuid.value = row.uuid || '';
      itemId.value = row.id || '';

      if (itemIdentity){
        const parts = [];
        if (row.uuid) parts.push(`uuid: ${row.uuid}`);
        if (row.slug) parts.push(`slug: ${row.slug}`);
        itemIdentity.textContent = parts.length ? parts.join(' • ') : '—';
      }
      if (itemUpdated) itemUpdated.textContent = row.updated_at ? `Updated: ${row.updated_at}` : 'Updated: —';
      if (itemBy) itemBy.textContent = (row.created_by_name || row.created_by_email || '—');

      mStatus.checked = normalizeBool(row.status, true);
      mAutoScroll.checked = normalizeBool(row.auto_scroll, true);
      mPauseOnHover.checked = normalizeBool(row.pause_on_hover, true);

      mScrollSpeed.value = String(row.scroll_speed ?? DEFAULTS.scroll_speed);
      mScrollLatency.value = String(row.scroll_latency_ms ?? DEFAULTS.scroll_latency_ms);
      mLoop.checked = normalizeBool(row.loop, true);
      mDirection.value = safeDir(row.direction ?? DEFAULTS.direction);

      mPublishAt.value = toDatetimeLocal(row.publish_at);
      mExpireAt.value = toDatetimeLocal(row.expire_at);

      const notices = normalizeNotices(row.notice_items_json ?? row.notices_json ?? row.notice_items ?? row.items ?? []);
      modalNoticesEditor.setNotices(notices);

      const disable = !!viewOnly || !canWrite;
      itemForm.querySelectorAll('input,select,textarea').forEach(el => {
        if (el.id === 'itemUuid' || el.id === 'itemId') return;
        el.disabled = disable;
      });
      itemForm.querySelectorAll('.nm-n-title,.nm-n-url,.nm-n-sort').forEach(el => el.disabled = disable);
      $('btnAddNoticeModal') && ($('btnAddNoticeModal').disabled = disable);
      $('btnClearNoticesModal') && ($('btnClearNoticesModal').disabled = disable);
      itemForm.querySelectorAll('.nm-n-remove').forEach(btn => btn.disabled = disable);

      if (saveBtn) saveBtn.style.display = (!disable && !viewOnly) ? '' : 'none';
      itemForm.dataset.mode = viewOnly ? 'view' : 'edit';
    }

    // new version button -> open modal prefilled with current
    btnNewVersion?.addEventListener('click', () => {
      if (!canWrite) return;

      itemForm.dataset.mode = 'create';
      if (itemModalTitle) itemModalTitle.textContent = 'New Version (Create)';

      itemUuid.value = '';
      itemId.value = '';
      if (itemIdentity) itemIdentity.textContent = '—';
      if (itemUpdated) itemUpdated.textContent = '—';
      if (itemBy) itemBy.textContent = '—';

      mStatus.checked = !!status.checked;
      mAutoScroll.checked = !!autoScroll.checked;
      mPauseOnHover.checked = !!pauseOnHover.checked;

      mScrollSpeed.value = scrollSpeed.value || DEFAULTS.scroll_speed;
      mScrollLatency.value = scrollLatency.value || DEFAULTS.scroll_latency_ms;
      mLoop.checked = !!loop.checked;
      mDirection.value = direction.value || DEFAULTS.direction;

      mPublishAt.value = publishAt.value || '';
      mExpireAt.value = expireAt.value || '';

      modalNoticesEditor.setNotices(noticesEditor.readFromRows());

      itemForm.querySelectorAll('input,select,textarea').forEach(el => {
        if (el.id === 'itemUuid' || el.id === 'itemId') return;
        el.disabled = false;
      });
      if (saveBtn) saveBtn.style.display = '';
      itemModal && itemModal.show();
    });

    // ---------- dropdown manual toggle ----------
    function closeAllDropdownsExcept(exceptToggle){
      document.querySelectorAll('.dd-toggle').forEach(t => {
        if (t === exceptToggle) return;
        try{ bootstrap.Dropdown.getInstance(t)?.hide(); }catch(_){}
      });
    }

    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('.dd-toggle');
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

    document.addEventListener('click', () => closeAllDropdownsExcept(null), { capture:true });
    // ------------------------------------------

    // row actions
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;

      const tr = btn.closest('tr');
      const uuid = tr?.dataset?.uuid;
      const action = btn.dataset.action;
      if (!uuid) return;

      const toggle = btn.closest('.dropdown')?.querySelector('.dd-toggle');
      if (toggle) { try { bootstrap.Dropdown.getInstance(toggle)?.hide(); } catch (_) {} }

      if (action === 'view' || action === 'edit'){
        const row = findRow('versions', uuid) || findRow('trash', uuid);
        if (itemModalTitle) itemModalTitle.textContent = (action === 'view') ? 'View Version' : 'Edit Version';
        fillModalFromRow(row || {}, action === 'view');
        itemModal && itemModal.show();
        return;
      }

      if (action === 'delete'){
        if (!canDelete) return;
        const conf = await Swal.fire({
          title: 'Delete this version?',
          text: 'This will move it to Trash.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`${API.base}/${encodeURIComponent(uuid)}`, {
            method: 'DELETE',
            headers: authHeaders(false)
          }, 15000);

          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Delete failed');

          ok('Moved to trash');
          await Promise.all([loadList('versions'), loadList('trash')]);
          await loadCurrent();
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      if (action === 'restore'){
        const conf = await Swal.fire({
          title: 'Restore this item?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Restore'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`${API.base}/${encodeURIComponent(uuid)}/restore`, {
            method: 'POST',
            headers: authHeaders(false)
          }, 15000);

          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Restore failed');

          ok('Restored');
          await Promise.all([loadList('trash'), loadList('versions')]);
          await loadCurrent();
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      if (action === 'force'){
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
        try{
          const res = await fetchWithTimeout(`${API.base}/${encodeURIComponent(uuid)}/force`, {
            method: 'DELETE',
            headers: authHeaders(false)
          }, 15000);

          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Force delete failed');

          ok('Deleted permanently');
          await loadList('trash');
          await loadCurrent();
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }
    });

    // modal submit (edit/create)
    itemForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (!canWrite) return;

      const mode = itemForm.dataset.mode || 'edit';
      if (mode === 'view') return;

      const items = modalNoticesEditor.readFromRows();
      for (const n of items){
        if (!n.title){ err('Each notice must have a title (remove empty rows).'); return; }
        if ((n.url || '').length > 500){ err('Notice link must be max 500 characters.'); return; }
      }

      const payload = {
        status: statusFromSwitch(!!mStatus.checked),
        auto_scroll: mAutoScroll.checked ? 1 : 0,
        pause_on_hover: mPauseOnHover.checked ? 1 : 0,
        scroll_speed: normalizeInt(mScrollSpeed.value, DEFAULTS.scroll_speed),
        scroll_latency_ms: normalizeInt(mScrollLatency.value, DEFAULTS.scroll_latency_ms),
        loop: mLoop.checked ? 1 : 0,
        direction: safeDir(mDirection.value),
        publish_at: fromDatetimeLocal(mPublishAt.value),
        expire_at: fromDatetimeLocal(mExpireAt.value),
        notice_items_json: items.map(x => ({
          title: (x.title || '').trim(),
          url: (x.url || '').trim(), // optional
          sort_order: normalizeInt(x.sort_order, 1),
        })),
      };

      if (payload.scroll_speed < 1 || payload.scroll_speed > 600){ err('Scroll speed must be between 1 and 600.'); return; }
      if (payload.scroll_latency_ms < 0 || payload.scroll_latency_ms > 600000){ err('Scroll latency must be between 0 and 600000.'); return; }

      setBtnLoading(saveBtn, true);
      showLoading(true);

      try{
        if (mode === 'create'){
          const res = await fetchWithTimeout(API.base, {
            method: 'POST',
            headers: authHeaders(true),
            body: JSON.stringify(payload)
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

          ok('Version created');
        } else {
          const uuid = (itemUuid.value || '').trim();
          if (!uuid){ err('Missing uuid'); return; }

          const out = await sendJsonWithFallback(`${API.base}/${encodeURIComponent(uuid)}`, payload, 'PATCH');
          if (!out.ok) throw new Error(out.error || 'Update failed');

          ok('Version updated');
        }

        itemModal && itemModal.hide();
        await Promise.all([loadList('versions'), loadList('trash')]);
        await loadCurrent();
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        setBtnLoading(saveBtn, false);
        showLoading(false);
      }
    });

    // tab load hooks
    document.querySelector('a[href="#nm-tab-current"]')?.addEventListener('shown.bs.tab', () => loadCurrent());
    document.querySelector('a[href="#nm-tab-versions"]')?.addEventListener('shown.bs.tab', () => loadList('versions'));
    document.querySelector('a[href="#nm-tab-trash"]')?.addEventListener('shown.bs.tab', () => loadList('trash'));

    // init
    (async () => {
      showLoading(true);
      try{
        await fetchMe();
        await loadCurrent();
        await Promise.all([loadList('versions'), loadList('trash')]);
        updatePreview();
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
