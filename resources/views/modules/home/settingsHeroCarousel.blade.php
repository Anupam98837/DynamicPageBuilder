```blade
{{-- resources/views/modules/home/settingsHeroCarousel.blade.php --}}
@section('title','Hero Carousel Settings')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
  Hero Carousel Settings (Admin)
  - uses common/main.css tokens
========================= */

/* Dropdown safety inside tables (scoped) */
.table-shell .dropdown{position:relative}
.table-shell .dd-toggle{border-radius:10px}
.hcs-wrap .dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:230px;
  z-index:99999; /* ✅ higher to avoid being behind/footer */
}
.hcs-wrap .dropdown-menu.show{display:block !important}
.hcs-wrap .dropdown-item{display:flex;align-items:center;gap:.6rem}
.hcs-wrap .dropdown-item i{width:16px;text-align:center}
.hcs-wrap .dropdown-item.text-danger{color:var(--danger-color) !important}

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
  display:block;
  width:100%;
  max-width:100%;
  overflow-x:auto !important;
  overflow-y:visible !important;
  -webkit-overflow-scrolling:touch;
  position:relative;
}
.table-responsive > .table{width:max-content;min-width:1200px}
.table-responsive th,.table-responsive td{white-space:nowrap}

/* Chips */
.chip{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:8px 10px;
  border-radius:999px;
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
.form-switch .form-check-input{
  width: 2.75rem;
  height: 1.35rem;
  cursor:pointer;
}
.form-switch .form-check-input:focus{
  box-shadow:0 0 0 .2rem color-mix(in oklab, var(--primary-color) 25%, transparent);
  border-color:color-mix(in oklab, var(--primary-color) 40%, var(--line-strong));
}

/* JSON editor */
.json-box{
  border:1px solid var(--line-strong);
  border-radius:14px;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  overflow:hidden;
}
.json-box .json-top{
  display:flex;align-items:center;justify-content:space-between;
  gap:10px;
  padding:10px 12px;
  border-bottom:1px solid var(--line-soft);
}
.json-box textarea{
  width:100%;
  min-height:220px;
  border:0;
  outline:0;
  padding:12px;
  background:transparent;
  color:var(--ink);
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  font-size:12.5px;
  line-height:1.45;
  resize:vertical;
}

/* Loading overlay */
.loading-overlay{
  position:fixed;inset:0;
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
  .hcs-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .hcs-toolbar .position-relative{min-width:100% !important}
  .toolbar-buttons{display:flex;gap:8px;flex-wrap:wrap}
  .toolbar-buttons .btn{flex:1;min-width:140px}
}
</style>
@endpush

@section('content')
<div class="hcs-wrap">

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
          <i class="fa-solid fa-images" style="opacity:.75;"></i>
          <h5 class="m-0 fw-bold">Hero Carousel Settings</h5>
        </div>
        <div class="helper mt-1">Control autoplay, transitions, arrows/dots and other behavior for the homepage hero slider.</div>
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
      <a class="nav-link active" data-bs-toggle="tab" href="#tab-current" role="tab" aria-selected="true">
        <i class="fa-solid fa-sliders me-2"></i>Current
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-versions" role="tab" aria-selected="false">
        <i class="fa-solid fa-layer-group me-2"></i>Versions
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-trash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Trash
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- CURRENT TAB --}}
    <div class="tab-pane fade show active" id="tab-current" role="tabpanel">

      <div class="row g-3">
        {{-- Current Settings Form --}}
        <div class="col-12 col-xl-7">
          <div class="card panel-card">
            <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
              <div>
                <div class="card-title">
                  <i class="fa-solid fa-gear me-2"></i>Live Settings
                </div>
                <div class="helper mt-1">This updates the “current” slider behavior (uses <code>upsert-current</code>).</div>
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
                <div class="col-12">
                  <div class="d-flex flex-wrap gap-2" id="summaryChips">
                    <span class="chip"><i class="fa-regular fa-circle-dot"></i><span id="chipDots">—</span></span>
                    <span class="chip"><i class="fa-solid fa-arrows-left-right"></i><span id="chipArrows">—</span></span>
                    <span class="chip"><i class="fa-solid fa-forward-fast"></i><span id="chipAuto">—</span></span>
                    <span class="chip"><i class="fa-solid fa-wand-magic"></i><span id="chipTransition">—</span></span>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="autoplay">
                    <label class="form-check-label" for="autoplay"><b>Autoplay</b></label>
                  </div>
                  <div class="helper mt-1">Auto-advance slides.</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Autoplay Delay (ms)</label>
                  <input type="number" class="form-control" id="autoplay_delay_ms" min="0" max="600000" step="50" placeholder="4000">
                  <div class="helper mt-1">Time between slide changes.</div>
                </div>

                <div class="col-md-6">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="loop">
                    <label class="form-check-label" for="loop"><b>Loop</b></label>
                  </div>
                  <div class="helper mt-1">Infinite looping.</div>
                </div>

                <div class="col-md-6">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="pause_on_hover">
                    <label class="form-check-label" for="pause_on_hover"><b>Pause on hover</b></label>
                  </div>
                  <div class="helper mt-1">Stop autoplay while mouse is over slider.</div>
                </div>

                <div class="col-md-6">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_arrows">
                    <label class="form-check-label" for="show_arrows"><b>Show arrows</b></label>
                  </div>
                  <div class="helper mt-1">Prev/Next controls.</div>
                </div>

                <div class="col-md-6">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="show_dots">
                    <label class="form-check-label" for="show_dots"><b>Show dots</b></label>
                  </div>
                  <div class="helper mt-1">Pagination dots.</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Transition</label>
                  <select class="form-select" id="transition">
                    <option value="slide">Slide</option>
                    <option value="fade">Fade</option>
                  </select>
                  <div class="helper mt-1">Slide or fade animation.</div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Transition Speed (ms)</label>
                  <input type="number" class="form-control" id="transition_ms" min="0" max="600000" step="10" placeholder="450">
                  <div class="helper mt-1">Animation duration.</div>
                </div>

                <div class="col-12">
                  <div class="json-box">
                    <div class="json-top">
                      <div class="fw-semibold"><i class="fa-solid fa-brackets-curly me-2"></i>Metadata (optional JSON)</div>
                      <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-light btn-sm" id="btnMetaPretty">
                          <i class="fa fa-align-left me-1"></i>Pretty
                        </button>
                        <button type="button" class="btn btn-light btn-sm" id="btnMetaClear">
                          <i class="fa fa-eraser me-1"></i>Clear
                        </button>
                      </div>
                    </div>
                    <textarea id="metadata" spellcheck="false" placeholder='Example: {"theme":"dark","debug":true}'></textarea>
                  </div>
                  <div class="helper mt-2">Leave empty if you don’t need extra config.</div>
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

              </div>
            </div>
          </div>
        </div>

        {{-- Preview / Info --}}
        <div class="col-12 col-xl-5">
          <div class="card panel-card">
            <div class="card-header py-3">
              <div class="card-title"><i class="fa-solid fa-chart-simple me-2"></i>Quick Read</div>
              <div class="helper mt-1">A human-friendly summary of what you’ve configured.</div>
            </div>
            <div class="card-body">
              <div class="d-flex flex-column gap-2">
                <div class="d-flex justify-content-between gap-2">
                  <span class="text-muted">Autoplay</span>
                  <span id="sumAutoplay" class="badge badge-soft-muted">—</span>
                </div>
                <div class="d-flex justify-content-between gap-2">
                  <span class="text-muted">Delay</span>
                  <span id="sumDelay" class="badge badge-soft-muted">—</span>
                </div>
                <div class="d-flex justify-content-between gap-2">
                  <span class="text-muted">Loop</span>
                  <span id="sumLoop" class="badge badge-soft-muted">—</span>
                </div>
                <div class="d-flex justify-content-between gap-2">
                  <span class="text-muted">Pause on Hover</span>
                  <span id="sumHover" class="badge badge-soft-muted">—</span>
                </div>
                <div class="d-flex justify-content-between gap-2">
                  <span class="text-muted">Arrows / Dots</span>
                  <span id="sumNav" class="badge badge-soft-muted">—</span>
                </div>
                <div class="d-flex justify-content-between gap-2">
                  <span class="text-muted">Transition</span>
                  <span id="sumTransition" class="badge badge-soft-muted">—</span>
                </div>
              </div>
            </div>
          </div>

          {{-- ✅ API Reference card removed (as requested) --}}
        </div>
      </div>
    </div>

    {{-- VERSIONS TAB --}}
    <div class="tab-pane fade" id="tab-versions" role="tabpanel">

      {{-- Toolbar --}}
      <div class="row align-items-center g-2 mb-3 hcs-toolbar panel">
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
            <input id="searchInput" type="search" class="form-control ps-5" placeholder="Search by uuid or transition…">
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
                  <th style="width:110px;">Autoplay</th>
                  <th style="width:140px;">Delay (ms)</th>
                  <th style="width:90px;">Loop</th>
                  <th style="width:140px;">Pause Hover</th>
                  <th style="width:110px;">Arrows</th>
                  <th style="width:90px;">Dots</th>
                  <th style="width:120px;">Transition</th>
                  <th style="width:150px;">Speed (ms)</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:140px;">By</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="tbody-versions">
                <tr><td colspan="12" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
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

    {{-- TRASH TAB --}}
    <div class="tab-pane fade" id="tab-trash" role="tabpanel">

      <div class="card table-shell">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:240px;">UUID</th>
                  <th style="width:120px;">Transition</th>
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
            <label class="form-label">Transition</label>
            <select id="modal_transition" class="form-select">
              <option value="">All</option>
              <option value="slide">Slide</option>
              <option value="fade">Fade</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="modal_sort" class="form-select">
              <option value="-updated_at">Recently Updated</option>
              <option value="updated_at">Oldest Updated</option>
              <option value="-created_at">Newest Created</option>
              <option value="created_at">Oldest Created</option>
              <option value="transition">Transition A-Z</option>
              <option value="-transition">Transition Z-A</option>
              <option value="-autoplay_delay_ms">Delay (Desc)</option>
              <option value="autoplay_delay_ms">Delay (Asc)</option>
              <option value="-transition_ms">Speed (Desc)</option>
              <option value="transition_ms">Speed (Asc)</option>
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
        <h5 class="modal-title" id="itemModalTitle">View Settings</h5>
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

          <div class="col-md-4">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="m_autoplay">
              <label class="form-check-label" for="m_autoplay"><b>Autoplay</b></label>
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Delay (ms)</label>
            <input type="number" class="form-control" id="m_autoplay_delay_ms" min="0" max="600000" step="50">
          </div>
          <div class="col-md-4">
            <label class="form-label">Transition</label>
            <select class="form-select" id="m_transition">
              <option value="slide">Slide</option>
              <option value="fade">Fade</option>
            </select>
          </div>

          <div class="col-md-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="m_loop">
              <label class="form-check-label" for="m_loop"><b>Loop</b></label>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="m_pause_on_hover">
              <label class="form-check-label" for="m_pause_on_hover"><b>Pause on Hover</b></label>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="m_show_arrows">
              <label class="form-check-label" for="m_show_arrows"><b>Show Arrows</b></label>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="m_show_dots">
              <label class="form-check-label" for="m_show_dots"><b>Show Dots</b></label>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Transition Speed (ms)</label>
            <input type="number" class="form-control" id="m_transition_ms" min="0" max="600000" step="10">
          </div>

          <div class="col-12">
            <div class="json-box">
              <div class="json-top">
                <div class="fw-semibold"><i class="fa-solid fa-brackets-curly me-2"></i>Metadata</div>
                <button type="button" class="btn btn-light btn-sm" id="btnModalMetaPretty">
                  <i class="fa fa-align-left me-1"></i>Pretty
                </button>
              </div>
              <textarea id="m_metadata" spellcheck="false" placeholder="{}"></textarea>
            </div>
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
  if (window.__HERO_CAROUSEL_SETTINGS_INIT__) return;
  window.__HERO_CAROUSEL_SETTINGS_INIT__ = true;

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
    try{
      return await fetch(url, { ...opts, signal: ctrl.signal });
    } finally { clearTimeout(t); }
  }

  function safeJsonParse(s){
    const txt = (s || '').toString().trim();
    if (!txt) return { ok:true, val:null };
    try{
      const v = JSON.parse(txt);
      return { ok:true, val:v };
    }catch(e){
      return { ok:false, error:'Metadata must be valid JSON (or empty).' };
    }
  }

  function prettyJsonToTextarea(textareaEl){
    const raw = (textareaEl?.value || '').trim();
    if (!raw) return;
    try{
      const v = JSON.parse(raw);
      textareaEl.value = JSON.stringify(v, null, 2);
    }catch(_){}
  }

  function boolBadge(v){
    return v ? `<span class="badge badge-soft-success">Yes</span>` : `<span class="badge badge-soft-muted">No</span>`;
  }

  function humanMs(n){
    const v = Number(n);
    if (!Number.isFinite(v)) return '—';
    if (v >= 1000) return `${(v/1000).toFixed(v%1000?2:0)}s (${v}ms)`;
    return `${v}ms`;
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
      const h = {
        'Authorization': 'Bearer ' + token,
        'Accept': 'application/json'
      };
      if (json) h['Content-Type'] = 'application/json';
      return h;
    };

    // permissions
    const ACTOR = { role: '' };
    let canWrite=false, canDelete=false;

    function computePermissions(){
      const r = (ACTOR.role || '').toLowerCase();
      const writeRoles  = ['admin','super_admin','director','principal','hod','technical_assistant','it_person'];
      const deleteRoles = ['admin','super_admin','director','principal'];
      canWrite = writeRoles.includes(r);
      canDelete = deleteRoles.includes(r);

      const wc = $('writeControls');
      const cc = $('currentControls');
      const cec = $('currentExtraControls');

      if (wc) wc.style.display = canWrite ? 'flex' : 'none';
      if (cc) cc.style.display = canWrite ? 'flex' : 'none';
      if (cec) cec.style.display = canWrite ? 'flex' : 'none';

      const bsc = $('btnSaveCurrent');
      const bsn = $('btnSaveAsNew');
      if (bsc) bsc.disabled = !canWrite;
      if (bsn) bsn.disabled = !canWrite;
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

    // elements (current form)
    const currentUuid = $('currentUuid');
    const currentId = $('currentId');
    const autoplay = $('autoplay');
    const autoplayDelay = $('autoplay_delay_ms');
    const loop = $('loop');
    const pauseOnHover = $('pause_on_hover');
    const showArrows = $('show_arrows');
    const showDots = $('show_dots');
    const transition = $('transition');
    const transitionMs = $('transition_ms');
    const metadata = $('metadata');

    const chipDots = $('chipDots');
    const chipArrows = $('chipArrows');
    const chipAuto = $('chipAuto');
    const chipTransition = $('chipTransition');

    const sumAutoplay = $('sumAutoplay');
    const sumDelay = $('sumDelay');
    const sumLoop = $('sumLoop');
    const sumHover = $('sumHover');
    const sumNav = $('sumNav');
    const sumTransition = $('sumTransition');

    const currentIdentity = $('currentIdentity');
    const currentUpdated = $('currentUpdated');

    // versions tab
    const perPageSel = $('perPage');
    const searchInput = $('searchInput');
    const btnReset = $('btnReset');

    const tbodyVersions = $('tbody-versions');
    const emptyVersions = $('empty-versions');
    const pagerVersions = $('pager-versions');
    const infoVersions = $('resultsInfo-versions');

    // trash tab
    const tbodyTrash = $('tbody-trash');
    const emptyTrash = $('empty-trash');
    const pagerTrash = $('pager-trash');
    const infoTrash = $('resultsInfo-trash');

    // filter modal
    const filterModalEl = $('filterModal');
    const filterModal = filterModalEl ? new bootstrap.Modal(filterModalEl) : null;
    const modalTransition = $('modal_transition');
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

    const mAutoplay = $('m_autoplay');
    const mAutoplayDelay = $('m_autoplay_delay_ms');
    const mLoop = $('m_loop');
    const mPause = $('m_pause_on_hover');
    const mArrows = $('m_show_arrows');
    const mDots = $('m_show_dots');
    const mTransition = $('m_transition');
    const mTransitionMs = $('m_transition_ms');
    const mMetadata = $('m_metadata');

    // buttons
    const btnDefaults = $('btnDefaults');
    const btnReloadCurrent = $('btnReloadCurrent');
    const btnSaveCurrent = $('btnSaveCurrent');
    const btnSaveAsNew = $('btnSaveAsNew');
    const btnMetaPretty = $('btnMetaPretty');
    const btnMetaClear = $('btnMetaClear');
    const btnModalMetaPretty = $('btnModalMetaPretty');
    const btnNewVersion = $('btnNewVersion');

    // state
    const state = {
      filters: { q:'', transition:'', sort:'-updated_at' },
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      versions: { page:1, lastPage:1, items:[] },
      trash: { page:1, lastPage:1, items:[] },
      current: null,
    };

    const DEFAULTS = {
      autoplay: 1,
      autoplay_delay_ms: 4000,
      loop: 1,
      pause_on_hover: 1,
      show_arrows: 1,
      show_dots: 1,
      transition: 'slide',
      transition_ms: 450,
      metadata: null
    };

    function setBtnLoading(btn, loading){
      if (!btn) return;
      btn.disabled = !!loading;
      btn.classList.toggle('btn-loading', !!loading);
    }

    function setEmpty(el, show){ if (el) el.style.display = show ? '' : 'none'; }

    function updateSummaryFromForm(){
      const a = autoplay.checked;
      const d = Number(autoplayDelay.value || DEFAULTS.autoplay_delay_ms);
      const l = loop.checked;
      const h = pauseOnHover.checked;
      const ar = showArrows.checked;
      const dt = showDots.checked;
      const tr = transition.value || DEFAULTS.transition;
      const sp = Number(transitionMs.value || DEFAULTS.transition_ms);

      if (chipDots) chipDots.textContent = dt ? 'Dots: On' : 'Dots: Off';
      if (chipArrows) chipArrows.textContent = ar ? 'Arrows: On' : 'Arrows: Off';
      if (chipAuto) chipAuto.textContent = a ? `Autoplay: On (${humanMs(d)})` : 'Autoplay: Off';
      if (chipTransition) chipTransition.textContent = `Transition: ${tr} (${humanMs(sp)})`;

      if (sumAutoplay){
        sumAutoplay.className = 'badge ' + (a ? 'badge-soft-success' : 'badge-soft-muted');
        sumAutoplay.textContent = a ? 'Enabled' : 'Disabled';
      }

      if (sumDelay){
        sumDelay.className = 'badge badge-soft-primary';
        sumDelay.textContent = humanMs(d);
      }

      if (sumLoop){
        sumLoop.className = 'badge ' + (l ? 'badge-soft-success' : 'badge-soft-muted');
        sumLoop.textContent = l ? 'Enabled' : 'Disabled';
      }

      if (sumHover){
        sumHover.className = 'badge ' + (h ? 'badge-soft-success' : 'badge-soft-muted');
        sumHover.textContent = h ? 'Enabled' : 'Disabled';
      }

      if (sumNav){
        sumNav.className = 'badge badge-soft-primary';
        sumNav.textContent = `${ar ? 'Arrows' : 'No arrows'} • ${dt ? 'Dots' : 'No dots'}`;
      }

      if (sumTransition){
        sumTransition.className = 'badge badge-soft-primary';
        sumTransition.textContent = `${tr} • ${humanMs(sp)}`;
      }
    }

    function fillCurrentForm(row){
      const r = row || {};

      if (currentUuid) currentUuid.value = r.uuid || '';
      if (currentId) currentId.value = r.id || '';

      autoplay.checked = !!Number(r.autoplay ?? DEFAULTS.autoplay);
      autoplayDelay.value = String(r.autoplay_delay_ms ?? DEFAULTS.autoplay_delay_ms);

      loop.checked = !!Number(r.loop ?? DEFAULTS.loop);
      pauseOnHover.checked = !!Number(r.pause_on_hover ?? DEFAULTS.pause_on_hover);

      showArrows.checked = !!Number(r.show_arrows ?? DEFAULTS.show_arrows);
      showDots.checked = !!Number(r.show_dots ?? DEFAULTS.show_dots);

      transition.value = (r.transition || DEFAULTS.transition);
      transitionMs.value = String(r.transition_ms ?? DEFAULTS.transition_ms);

      const meta = r.metadata ?? null;
      if (meta && typeof meta === 'object') {
        metadata.value = JSON.stringify(meta, null, 2);
      } else if (typeof meta === 'string' && meta.trim()) {
        metadata.value = meta;
      } else {
        metadata.value = '';
      }

      if (currentIdentity) currentIdentity.textContent = r.uuid ? `uuid: ${r.uuid}` : '—';
      if (currentUpdated) currentUpdated.textContent = r.updated_at ? `Updated: ${r.updated_at}` : 'Updated: —';

      updateSummaryFromForm();
    }

    function readCurrentPayload(){
      const metaParsed = safeJsonParse(metadata.value);
      if (!metaParsed.ok) return { ok:false, error: metaParsed.error };

      const payload = {
        autoplay: autoplay.checked ? 1 : 0,
        autoplay_delay_ms: Number(autoplayDelay.value || DEFAULTS.autoplay_delay_ms),
        loop: loop.checked ? 1 : 0,
        pause_on_hover: pauseOnHover.checked ? 1 : 0,
        show_arrows: showArrows.checked ? 1 : 0,
        show_dots: showDots.checked ? 1 : 0,
        transition: (transition.value || DEFAULTS.transition),
        transition_ms: Number(transitionMs.value || DEFAULTS.transition_ms),
        metadata: metaParsed.val
      };

      if (payload.autoplay_delay_ms < 0 || payload.autoplay_delay_ms > 600000) return { ok:false, error:'Autoplay delay must be between 0 and 600000.' };
      if (payload.transition_ms < 0 || payload.transition_ms > 600000) return { ok:false, error:'Transition speed must be between 0 and 600000.' };

      return { ok:true, payload };
    }

    // build list urls
    function buildListUrl(kind){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state[kind].page));

      const q = (state.filters.q || '').trim();
      if (q) params.set('q', q);

      const s = state.filters.sort || '-updated_at';
      params.set('sort', s.startsWith('-') ? s.slice(1) : s);
      params.set('direction', s.startsWith('-') ? 'desc' : 'asc');

      if (state.filters.transition) params.set('q', state.filters.transition);

      if (kind === 'trash') params.set('only_trashed', '1');

      return (kind === 'trash')
        ? `/api/hero-carousel-settings/trash?${params.toString()}`
        : `/api/hero-carousel-settings?${params.toString()}`;
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

        return `
          <tr data-uuid="${esc(uuid)}">
            <td><code>${esc(uuid)}</code></td>
            <td>${boolBadge(!!Number(r.autoplay))}</td>
            <td>${esc(String(r.autoplay_delay_ms ?? '—'))}</td>
            <td>${boolBadge(!!Number(r.loop))}</td>
            <td>${boolBadge(!!Number(r.pause_on_hover))}</td>
            <td>${boolBadge(!!Number(r.show_arrows))}</td>
            <td>${boolBadge(!!Number(r.show_dots))}</td>
            <td><span class="badge badge-soft-primary">${esc(r.transition || '—')}</span></td>
            <td>${esc(String(r.transition_ms ?? '—'))}</td>
            <td>${esc(updated)}</td>
            <td>${esc(by)}</td>
            <td class="text-end">
              <div class="dropdown">
                <!-- ✅ FIX: remove data-bs-toggle, control manually like ContactInfo -->
                <button type="button"
                  class="btn btn-light btn-sm dd-toggle"
                  aria-expanded="false" title="Actions">
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
        return `
          <tr data-uuid="${esc(uuid)}">
            <td><code>${esc(uuid)}</code></td>
            <td><span class="badge badge-soft-primary">${esc(r.transition || '—')}</span></td>
            <td>${esc(r.deleted_at || '—')}</td>
            <td>${esc(r.updated_at || '—')}</td>
            <td>${esc(by)}</td>
            <td class="text-end">
              <div class="dropdown">
                <!-- ✅ same dropdown fix in trash -->
                <button type="button"
                  class="btn btn-light btn-sm dd-toggle"
                  aria-expanded="false" title="Actions">
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
        const res = await fetchWithTimeout('/api/hero-carousel-settings/current', { headers: authHeaders(false) }, 12000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }
        const js = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(js?.message || 'Failed to load current settings');

        const item = js?.item || js?.data || null;
        state.current = item;
        fillCurrentForm(item || DEFAULTS);
      }catch(e){
        fillCurrentForm(DEFAULTS);
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    async function loadList(kind){
      const tbody = (kind === 'trash') ? tbodyTrash : tbodyVersions;
      const cols = (kind === 'trash') ? 6 : 12;
      tbody.innerHTML = `<tr><td colspan="${cols}" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;

      try{
        const res = await fetchWithTimeout(buildListUrl(kind), { headers: authHeaders(false) }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to load');

        const items = Array.isArray(js.data) ? js.data : [];
        const p = js.pagination || {};

        let out = items;
        if (state.filters.transition){
          const t = state.filters.transition.toLowerCase();
          out = items.filter(x => (x.transition || '').toLowerCase() === t);
        }

        state[kind].items = out;
        state[kind].lastPage = parseInt(p.last_page || 1, 10) || 1;

        const infoEl = (kind === 'trash') ? infoTrash : infoVersions;
        if (infoEl) infoEl.textContent = (p.total ? `${p.total} result(s)` : '—');

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

    // current form live updates
    [autoplay, autoplayDelay, loop, pauseOnHover, showArrows, showDots, transition, transitionMs, metadata]
      .forEach(el => el?.addEventListener('input', debounce(updateSummaryFromForm, 80)));
    [autoplay, loop, pauseOnHover, showArrows, showDots, transition].forEach(el => el?.addEventListener('change', updateSummaryFromForm));

    // meta buttons
    btnMetaPretty?.addEventListener('click', () => prettyJsonToTextarea(metadata));
    btnMetaClear?.addEventListener('click', () => { metadata.value = ''; updateSummaryFromForm(); });
    btnModalMetaPretty?.addEventListener('click', () => prettyJsonToTextarea(mMetadata));

    // defaults / reload
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

    // save current (upsert)
    async function saveCurrent(mode){
      if (!canWrite) return;

      const read = readCurrentPayload();
      if (!read.ok){
        err(read.error || 'Invalid values');
        return;
      }

      const url = (mode === 'new')
        ? '/api/hero-carousel-settings'
        : '/api/hero-carousel-settings/upsert-current';

      setBtnLoading(btnSaveCurrent, mode !== 'new');
      setBtnLoading(btnSaveAsNew, mode === 'new');
      showLoading(true);

      try{
        const res = await fetchWithTimeout(url, {
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

        ok(mode === 'new' ? 'Saved as new version' : 'Saved current settings');

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
      const conf = await Swal.fire({
        title: 'Create a new version?',
        text: 'This will create a new settings row (useful for auditing).',
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
      state.filters = { q:'', transition:'', sort:'-updated_at' };
      state.perPage = 20;
      if (searchInput) searchInput.value = '';
      if (perPageSel) perPageSel.value = '20';
      if (modalTransition) modalTransition.value = '';
      if (modalSort) modalSort.value = '-updated_at';
      state.versions.page = 1;
      state.trash.page = 1;
      loadList('versions');
      loadList('trash');
      ok('Filters reset');
    });

    filterModalEl?.addEventListener('show.bs.modal', () => {
      if (modalTransition) modalTransition.value = state.filters.transition || '';
      if (modalSort) modalSort.value = state.filters.sort || '-updated_at';
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.transition = (modalTransition?.value || '');
      state.filters.sort = (modalSort?.value || '-updated_at');
      state.versions.page = 1;
      filterModal && filterModal.hide();
      loadList('versions');
    });

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

      mAutoplay.checked = autoplay.checked;
      mAutoplayDelay.value = autoplayDelay.value || DEFAULTS.autoplay_delay_ms;
      mLoop.checked = loop.checked;
      mPause.checked = pauseOnHover.checked;
      mArrows.checked = showArrows.checked;
      mDots.checked = showDots.checked;
      mTransition.value = transition.value || DEFAULTS.transition;
      mTransitionMs.value = transitionMs.value || DEFAULTS.transition_ms;
      mMetadata.value = (metadata.value || '').trim();

      itemForm.querySelectorAll('input,select,textarea').forEach(el => el.disabled = false);
      if (saveBtn) saveBtn.style.display = '';
      itemModal && itemModal.show();
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

      if (itemIdentity) itemIdentity.textContent = row.uuid ? `uuid: ${row.uuid}` : '—';
      if (itemUpdated) itemUpdated.textContent = row.updated_at ? `Updated: ${row.updated_at}` : 'Updated: —';
      if (itemBy) itemBy.textContent = (row.created_by_name || row.created_by_email || '—');

      mAutoplay.checked = !!Number(row.autoplay ?? 0);
      mAutoplayDelay.value = String(row.autoplay_delay_ms ?? DEFAULTS.autoplay_delay_ms);
      mLoop.checked = !!Number(row.loop ?? 0);
      mPause.checked = !!Number(row.pause_on_hover ?? 0);
      mArrows.checked = !!Number(row.show_arrows ?? 0);
      mDots.checked = !!Number(row.show_dots ?? 0);
      mTransition.value = row.transition || DEFAULTS.transition;
      mTransitionMs.value = String(row.transition_ms ?? DEFAULTS.transition_ms);

      const meta = row.metadata ?? null;
      if (meta && typeof meta === 'object') mMetadata.value = JSON.stringify(meta, null, 2);
      else if (typeof meta === 'string' && meta.trim()) mMetadata.value = meta;
      else mMetadata.value = '';

      const disable = !!viewOnly || !canWrite;
      itemForm.querySelectorAll('input,select,textarea').forEach(el => {
        if (el.id === 'itemUuid' || el.id === 'itemId') return;
        el.disabled = disable;
      });
      if (saveBtn) saveBtn.style.display = (!disable && !viewOnly) ? '' : 'none';

      itemForm.dataset.mode = viewOnly ? 'view' : 'edit';
    }

    // ---------- ✅ ACTION DROPDOWN FIX (same approach as Contact Info) ----------
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

    // close dropdowns when clicking elsewhere
    document.addEventListener('click', () => {
      closeAllDropdownsExcept(null);
    }, { capture: true });
    // ------------------------------------------------------------------------

    // row actions (versions + trash)
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;

      const tr = btn.closest('tr');
      const uuid = tr?.dataset?.uuid;
      const action = btn.dataset.action;
      if (!uuid) return;

      // close dropdown (do NOT create new instance)
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
          const res = await fetchWithTimeout(`/api/hero-carousel-settings/${encodeURIComponent(uuid)}`, {
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
          const res = await fetchWithTimeout(`/api/hero-carousel-settings/${encodeURIComponent(uuid)}/restore`, {
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
          const res = await fetchWithTimeout(`/api/hero-carousel-settings/${encodeURIComponent(uuid)}/force`, {
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

    // modal submit (edit or create)
    itemForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (!canWrite) return;

      const mode = itemForm.dataset.mode || 'edit'; // edit | create | view
      if (mode === 'view') return;

      const metaParsed = safeJsonParse(mMetadata.value);
      if (!metaParsed.ok){
        err(metaParsed.error);
        return;
      }

      const payload = {
        autoplay: mAutoplay.checked ? 1 : 0,
        autoplay_delay_ms: Number(mAutoplayDelay.value || DEFAULTS.autoplay_delay_ms),
        loop: mLoop.checked ? 1 : 0,
        pause_on_hover: mPause.checked ? 1 : 0,
        show_arrows: mArrows.checked ? 1 : 0,
        show_dots: mDots.checked ? 1 : 0,
        transition: (mTransition.value || DEFAULTS.transition),
        transition_ms: Number(mTransitionMs.value || DEFAULTS.transition_ms),
        metadata: metaParsed.val
      };

      setBtnLoading(saveBtn, true);
      showLoading(true);

      try{
        let res;

        if (mode === 'create'){
          res = await fetchWithTimeout('/api/hero-carousel-settings', {
            method: 'POST',
            headers: authHeaders(true),
            body: JSON.stringify(payload)
          }, 20000);
        } else {
          const uuid = (itemUuid.value || '').trim();
          res = await fetchWithTimeout(`/api/hero-carousel-settings/${encodeURIComponent(uuid)}`, {
            method: 'PATCH',
            headers: authHeaders(true),
            body: JSON.stringify(payload)
          }, 20000);
        }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false){
          let msg = js?.message || 'Save failed';
          if (js?.errors){
            const k = Object.keys(js.errors)[0];
            if (k && js.errors[k] && js.errors[k][0]) msg = js.errors[k][0];
          }
          throw new Error(msg);
        }

        ok(mode === 'create' ? 'Version created' : 'Version updated');
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
    document.querySelector('a[href="#tab-current"]')?.addEventListener('shown.bs.tab', () => loadCurrent());
    document.querySelector('a[href="#tab-versions"]')?.addEventListener('shown.bs.tab', () => loadList('versions'));
    document.querySelector('a[href="#tab-trash"]')?.addEventListener('shown.bs.tab', () => loadList('trash'));

    // init
    (async () => {
      showLoading(true);
      try{
        await fetchMe();
        await loadCurrent();
        await Promise.all([loadList('versions'), loadList('trash')]);
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
```
