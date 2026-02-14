{{-- resources/views/modules/home/settingsCenterIframes.blade.php --}}
@section('title','Center Iframes Settings')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* =========================
  Center Iframes Settings (Admin)
  - reference-inspired, rewritten
========================= */

.ci-wrap{max-width:1140px;margin:16px auto 42px;padding:0 4px;overflow:visible}

/* Cards */
.ci-card{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.ci-card .card-header{
  background:transparent;
  border-bottom:1px solid var(--line-soft);
}
.ci-title{margin:0;font-weight:800}
.ci-help{font-size:12.5px;color:var(--muted-color)}
.ci-small{font-size:12.5px}

/* Chips */
.ci-chip{
  display:inline-flex;align-items:center;gap:8px;
  padding:8px 10px;border-radius:999px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 90%, transparent);
  font-size:12.5px;
}
.ci-chip i{opacity:.75}

/* Tabs */
.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface);
}
.tab-content,.tab-pane{overflow:visible}

/* Table shell */
.ci-table.card{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.ci-table .card-body{overflow:visible}
.table{--bs-table-bg:transparent}
.table thead th{
  font-weight:600;
  color:var(--muted-color);
  font-size:13px;
  border-bottom:1px solid var(--line-strong);
  background:var(--surface);
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
.table-responsive > .table{width:max-content;min-width:1100px}
.table-responsive th,.table-responsive td{white-space:nowrap}

/* Soft badges */
.badge-soft-success{
  background:color-mix(in oklab, var(--success-color) 12%, transparent);
  color:var(--success-color);
}
.badge-soft-muted{
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
  color:var(--muted-color);
}
.badge-soft-primary{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  color:var(--primary-color);
}
.badge-soft-warning{
  background:color-mix(in oklab, var(--warning-color) 14%, transparent);
  color:var(--warning-color);
}

/* Switch */
.form-switch .form-check-input{
  width:2.75rem;height:1.35rem;cursor:pointer;
}
.form-switch .form-check-input:focus{
  box-shadow:0 0 0 .2rem color-mix(in oklab, var(--primary-color) 25%, transparent);
  border-color:color-mix(in oklab, var(--primary-color) 40%, var(--line-strong));
}

/* Buttons builder */
.ci-repeater{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.ci-repeater-top{
  display:flex;align-items:center;justify-content:space-between;
  gap:10px;padding:10px 12px;border-bottom:1px solid var(--line-soft);
}
.ci-repeater-body{padding:12px}
.ci-row{
  display:grid;
  grid-template-columns: 1.05fr 1.35fr .55fr auto;
  gap:10px;
  align-items:center;
  padding:10px;
  border:1px dashed color-mix(in oklab, var(--line-strong) 70%, transparent);
  border-radius:12px;
  background:color-mix(in oklab, var(--surface) 96%, transparent);
  margin-bottom:10px;
}
.ci-row:last-child{margin-bottom:0}
.ci-icon-btn{
  width:38px;height:38px;border-radius:12px;
  display:inline-flex;align-items:center;justify-content:center;
}

/* Metadata JSON box */
.ci-json{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.ci-json-top{
  display:flex;align-items:center;justify-content:space-between;
  gap:10px;padding:10px 12px;border-bottom:1px solid var(--line-soft);
}
.ci-json textarea{
  width:100%;
  min-height:220px;
  border:0;outline:0;
  padding:12px;
  background:transparent;
  color:var(--ink);
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  font-size:12.5px;line-height:1.45;
  resize:vertical;
}

/* Dropdown safety */
.ci-wrap .dropdown{position:relative}
.ci-wrap .dd-toggle{border-radius:10px}
.ci-wrap .dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:230px;
  z-index:99999;
}
.ci-wrap .dropdown-menu.show{display:block !important}
.ci-wrap .dropdown-item{display:flex;align-items:center;gap:.6rem}
.ci-wrap .dropdown-item i{width:16px;text-align:center}
.ci-wrap .dropdown-item.text-danger{color:var(--danger-color) !important}

/* Loading overlay */
.ci-loading{
  position:fixed;inset:0;
  background:rgba(0,0,0,.45);
  display:flex;justify-content:center;align-items:center;
  z-index:9999;
  backdrop-filter:blur(2px);
}
.ci-loading-card{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;flex-direction:column;align-items:center;gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,.3);
}
.ci-spinner{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,.3);
  border-top:4px solid var(--primary-color);
  animation:ciSpin 1s linear infinite;
}
@keyframes ciSpin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

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
  animation:ciSpin 1s linear infinite;
}

/* Responsive */
@media (max-width: 768px){
  .ci-row{grid-template-columns:1fr;}
  .ci-row .ci-icon-btn{width:100%}
}
</style>
@endpush

@section('content')
<div class="ci-wrap">

  {{-- Global Loading --}}
  <div id="ciLoading" class="ci-loading" style="display:none;">
    <div class="ci-loading-card">
      <div class="ci-spinner"></div>
      <div class="ci-small">Loading…</div>
    </div>
  </div>

  {{-- Header --}}
  <div class="card ci-card mb-3">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div>
        <div class="d-flex align-items-center gap-2">
          <i class="fa-solid fa-display" style="opacity:.75;"></i>
          <h5 class="m-0 fw-bold">Center Iframes Settings</h5>
        </div>
        <div class="ci-help mt-1">
          Manage center iframe entries, their buttons (<code>buttons_json</code>) and <code>metadata</code>.
        </div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <span class="ci-chip"><i class="fa-solid fa-shield-halved"></i> Admin module</span>
        <span class="ci-chip"><i class="fa-solid fa-database"></i> Metadata JSON</span>
      </div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#ci-tab-current" role="tab" aria-selected="true">
        <i class="fa-solid fa-sliders me-2"></i>Current
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#ci-tab-versions" role="tab" aria-selected="false">
        <i class="fa-solid fa-layer-group me-2"></i>Versions
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#ci-tab-trash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Trash
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- ===================== CURRENT ===================== --}}
    <div class="tab-pane fade show active" id="ci-tab-current" role="tabpanel">

      <div class="row g-3">
        <div class="col-12">
          <div class="card ci-card">
            <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
              <div>
                <div class="ci-title"><i class="fa-solid fa-gear me-2"></i>Current (latest)</div>
                <div class="ci-help mt-1">
                  Loads the latest updated record from <code>/api/center-iframes</code>. You can update it, or save as a new version.
                </div>
              </div>

              <div class="d-flex gap-2 flex-wrap" id="ciCurrentControls" style="display:none;">
                <button type="button" class="btn btn-light" id="ciBtnReload">
                  <i class="fa fa-rotate me-1"></i>Reload
                </button>
                <button type="button" class="btn btn-primary" id="ciBtnSaveCurrent">
                  <i class="fa fa-floppy-disk me-1"></i>Save Current
                </button>
              </div>
            </div>

            <div class="card-body">
              <input type="hidden" id="ciCurrentUuid">
              <input type="hidden" id="ciCurrentId">

              <div class="row g-3">

                <div class="col-12">
                  <label class="form-label">Title <span class="text-danger">*</span></label>
                  <input id="ci_title" class="form-control" placeholder="e.g., Our Center / Admission Desk">
                  <div class="ci-help mt-1">Required (DB: <code>title</code> NOT NULL).</div>
                </div>

                <div class="col-12">
                  <label class="form-label">Iframe URL <span class="text-danger">*</span></label>
                  <input id="ci_iframe_url" class="form-control" placeholder="https://… or /path">
                  <div class="ci-help mt-1">Required (DB: <code>iframe_url</code> NOT NULL).</div>
                </div>

                <div class="col-12">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="ci_is_active">
                    <label class="form-check-label" for="ci_is_active"><b>Status: Active</b></label>
                  </div>
                  <div class="ci-help mt-1">Maps to DB field <code>status</code>: <code>active</code> / <code>inactive</code>.</div>
                </div>

                {{-- Buttons builder (buttons_json) --}}
                <div class="col-12">
                  <div class="ci-repeater">
                    <div class="ci-repeater-top">
                      <div class="fw-semibold">
                        <i class="fa-solid fa-link me-2"></i>Buttons (<code>buttons_json</code>) — Text + URL + Sort Order
                      </div>
                      <div class="d-flex gap-2 flex-wrap" id="ciButtonsControls" style="display:none;">
                        <button type="button" class="btn btn-light btn-sm" id="ciBtnAddBtn">
                          <i class="fa fa-plus me-1"></i>Add Button
                        </button>
                        <button type="button" class="btn btn-light btn-sm" id="ciBtnClearBtns">
                          <i class="fa fa-eraser me-1"></i>Clear
                        </button>
                      </div>
                    </div>

                    <div class="ci-repeater-body">
                      <div id="ciButtonsList"></div>

                      <div id="ciButtonsEmpty" class="text-center text-muted py-3" style="display:none;">
                        <i class="fa-regular fa-circle-plus me-1"></i>No buttons yet. Click <b>Add Button</b>.
                      </div>
                    </div>
                  </div>

                  <div class="ci-help mt-2">
                    Stored as JSON array in <code>buttons_json</code>, like:
                    <code>[{"text":"Apply Now","url":"https://…","sort_order":1}]</code>
                  </div>
                </div>

                {{-- Metadata JSON --}}
                <div class="col-12">
                  <div class="ci-json">
                    <div class="ci-json-top">
                      <div class="fw-semibold"><i class="fa-solid fa-database me-2"></i>Metadata (<code>metadata</code>)</div>
                      <div class="d-flex gap-2 flex-wrap" id="ciMetaControls" style="display:none;">
                        <button type="button" class="btn btn-light btn-sm" id="ciBtnMetaPretty">
                          <i class="fa fa-align-left me-1"></i>Pretty
                        </button>
                        <button type="button" class="btn btn-light btn-sm" id="ciBtnMetaValidate">
                          <i class="fa fa-circle-check me-1"></i>Validate
                        </button>
                      </div>
                    </div>
                    <textarea id="ci_metadata_json" spellcheck="false" placeholder='{"note":"Any extra data goes here"}'></textarea>
                  </div>
                  <div class="ci-help mt-2">
                    Optional JSON stored in DB column <code>metadata</code>. Keep it valid JSON (object/array/value).
                  </div>
                </div>

                <div class="col-12">
                  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                    <div class="ci-help">
                      <span class="me-2"><i class="fa-regular fa-id-card me-1"></i><span id="ciCurrentIdentity">—</span></span>
                      <span class="me-2"><i class="fa-regular fa-clock me-1"></i><span id="ciCurrentUpdated">—</span></span>
                    </div>
                    <div class="d-flex gap-2 flex-wrap" id="ciCurrentExtra" style="display:none;">
                      <button type="button" class="btn btn-outline-primary" id="ciBtnSaveAsNew">
                        <i class="fa fa-code-branch me-1"></i>Save as New Version
                      </button>
                    </div>
                  </div>
                </div>

              </div>{{-- row --}}
            </div>
          </div>
        </div>
      </div>{{-- row --}}
    </div>

    {{-- ===================== VERSIONS ===================== --}}
    <div class="tab-pane fade" id="ci-tab-versions" role="tabpanel">

      {{-- Toolbar (pagination NOT here) --}}
      <div class="card ci-card mb-3">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
          <div class="d-flex align-items-center flex-wrap gap-2">
            <div class="position-relative" style="min-width:280px;">
              <input id="ciSearch" type="search" class="form-control ps-5" placeholder="Search by uuid/title/url…">
              <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
            </div>

            <button id="ciBtnFilter" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#ciFilterModal">
              <i class="fa fa-sliders me-1"></i>Filter
            </button>

            <button id="ciBtnReset" class="btn btn-light">
              <i class="fa fa-rotate-left me-1"></i>Reset
            </button>
          </div>

          <div class="d-flex gap-2 flex-wrap" id="ciWriteControls" style="display:none;">
            {{-- intentionally empty --}}
          </div>
        </div>
      </div>

      {{-- Table --}}
      <div class="card ci-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:240px;">UUID</th>
                  <th style="width:240px;">Title</th>
                  <th style="width:420px;">Iframe URL</th>
                  <th style="width:120px;">Buttons</th>
                  <th style="width:140px;">Status</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:140px;">By</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="ciTbodyVersions">
                <tr><td colspan="8" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="ciEmptyVersions" class="p-4 text-center" style="display:none;">
            <i class="fa-solid fa-layer-group mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No versions found.</div>
          </div>

          {{-- Footer (per-page + pagination lives here) --}}
          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="ciInfoVersions">—</div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
              <div class="d-flex align-items-center gap-2">
                <label class="text-muted small mb-0">Per Page</label>
                <select id="ciPerPage" class="form-select" style="width:96px;">
                  <option>10</option>
                  <option selected>20</option>
                  <option>50</option>
                  <option>100</option>
                </select>
              </div>

              <nav><ul id="ciPagerVersions" class="pagination mb-0"></ul></nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ===================== TRASH ===================== --}}
    <div class="tab-pane fade" id="ci-tab-trash" role="tabpanel">

      <div class="card ci-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:240px;">UUID</th>
                  <th style="width:320px;">Title</th>
                  <th style="width:170px;">Deleted At</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:140px;">By</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="ciTbodyTrash">
                <tr><td colspan="6" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="ciEmptyTrash" class="p-4 text-center" style="display:none;">
            <i class="fa fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>Trash is empty.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="ciInfoTrash">—</div>
            <nav><ul id="ciPagerTrash" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="ciFilterModal" tabindex="-1" aria-hidden="true">
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
            <select id="ciModalActive" class="form-select">
              <option value="">All</option>
              <option value="1">Active only</option>
              <option value="0">Inactive only</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="ciModalSort" class="form-select">
              <option value="-updated_at">Recently Updated</option>
              <option value="updated_at">Oldest Updated</option>
              <option value="-created_at">Newest Created</option>
              <option value="created_at">Oldest Created</option>
              <option value="title">Title A-Z</option>
              <option value="-title">Title Z-A</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="ciBtnApplyFilters" class="btn btn-primary">
          <i class="fa fa-check me-1"></i>Apply Filters
        </button>
      </div>
    </div>
  </div>
</div>

{{-- View/Edit Modal --}}
<div class="modal fade" id="ciItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form class="modal-content" id="ciItemForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="ciItemModalTitle">View</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="ciItemUuid">
        <input type="hidden" id="ciItemId">

        <div class="row g-3">
          <div class="col-12">
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <span class="ci-chip"><i class="fa-regular fa-id-badge"></i><span id="ciItemIdentity">—</span></span>
              <span class="ci-chip"><i class="fa-regular fa-clock"></i><span id="ciItemUpdated">—</span></span>
              <span class="ci-chip"><i class="fa-regular fa-user"></i><span id="ciItemBy">—</span></span>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input id="ci_m_title" class="form-control" placeholder="Title">
          </div>

          <div class="col-12">
            <label class="form-label">Iframe URL <span class="text-danger">*</span></label>
            <input id="ci_m_iframe_url" class="form-control" placeholder="https://… or /path">
          </div>

          <div class="col-12">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="ci_m_is_active">
              <label class="form-check-label" for="ci_m_is_active"><b>Status: Active</b></label>
            </div>
            <div class="ci-help mt-1">Maps to <code>status</code>: <code>active</code> / <code>inactive</code>.</div>
          </div>

          <div class="col-12">
            <div class="ci-repeater">
              <div class="ci-repeater-top">
                <div class="fw-semibold"><i class="fa-solid fa-link me-2"></i>Buttons (<code>buttons_json</code>)</div>
                <div class="d-flex gap-2 flex-wrap" id="ciModalButtonsControls" style="display:none;">
                  <button type="button" class="btn btn-light btn-sm" id="ciBtnAddBtnModal">
                    <i class="fa fa-plus me-1"></i>Add Button
                  </button>
                  <button type="button" class="btn btn-light btn-sm" id="ciBtnClearBtnsModal">
                    <i class="fa fa-eraser me-1"></i>Clear
                  </button>
                </div>
              </div>
              <div class="ci-repeater-body">
                <div id="ciButtonsListModal"></div>
                <div id="ciButtonsEmptyModal" class="text-center text-muted py-3" style="display:none;">
                  No buttons.
                </div>
              </div>
            </div>
          </div>

          <div class="col-12">
            <div class="ci-json">
              <div class="ci-json-top">
                <div class="fw-semibold"><i class="fa-solid fa-database me-2"></i>Metadata (<code>metadata</code>)</div>
                <div class="d-flex gap-2 flex-wrap" id="ciModalMetaControls" style="display:none;">
                  <button type="button" class="btn btn-light btn-sm" id="ciBtnMetaPrettyModal">
                    <i class="fa fa-align-left me-1"></i>Pretty
                  </button>
                  <button type="button" class="btn btn-light btn-sm" id="ciBtnMetaValidateModal">
                    <i class="fa fa-circle-check me-1"></i>Validate
                  </button>
                </div>
              </div>
              <textarea id="ci_m_metadata_json" spellcheck="false" placeholder='{"note":"Optional metadata"}'></textarea>
            </div>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" id="ciSaveBtn">
          <i class="fa fa-floppy-disk me-1"></i> Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="ciToastOk" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="ciToastOkText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="ciToastErr" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="ciToastErrText">Something went wrong</div>
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
  if (window.__CENTER_IFRAMES_SETTINGS_INIT__) return;
  window.__CENTER_IFRAMES_SETTINGS_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=300) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  // ✅ FIX: hard cleanup for orphaned modal backdrops (Bootstrap sometimes leaves them behind)
  function cleanupModalBackdrop(){
    // Only cleanup if no modal is currently shown
    if (document.querySelector('.modal.show')) return;

    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('padding-right');
    document.body.style.removeProperty('overflow');
  }

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

  function safeJsonParse(raw, label='JSON'){
    const txt = (raw || '').toString().trim();
    if (!txt) return { ok:true, val:null }; // allow null for optional metadata
    try{
      const v = JSON.parse(txt);
      return { ok:true, val:v };
    }catch(_){
      return { ok:false, error:`${label} must be valid JSON.` };
    }
  }

  function prettyJson(textareaEl){
    const raw = (textareaEl?.value || '').trim();
    if (!raw) return;
    try{
      const v = JSON.parse(raw);
      textareaEl.value = JSON.stringify(v, null, 2);
    }catch(_){}
  }

  function statusFromSwitch(checked){
    return checked ? 'active' : 'inactive';
  }

  function statusBadge(val){
    const s = (val ?? '').toString().toLowerCase().trim();
    if (s === 'active') return `<span class="badge badge-soft-success">active</span>`;
    if (s === 'inactive') return `<span class="badge badge-soft-muted">inactive</span>`;
    if (!s) return `<span class="badge badge-soft-muted">—</span>`;
    return `<span class="badge badge-soft-warning">${esc(s)}</span>`;
  }

  function normalizeButtons(any){
    // Accept: array of {text,url,sort_order} OR {title,link} OR JSON string OR map
    if (any == null) return [];
    let v = any;

    if (typeof v === 'string'){
      const p = safeJsonParse(v, 'Buttons');
      if (!p.ok) return [];
      v = p.val;
    }

    if (Array.isArray(v)){
      return v.map((x, i) => ({
        text: (x?.text ?? x?.title ?? x?.label ?? '').toString().trim(),
        url: (x?.url ?? x?.link ?? '').toString().trim(),
        sort_order: Number.isFinite(Number(x?.sort_order)) ? Number(x.sort_order) : (i + 1),
      })).filter(x => x.text || x.url);
    }

    if (typeof v === 'object'){
      // map => { "Apply":"https://..." }
      return Object.keys(v).map((k, i) => ({
        text: k.toString().trim(),
        url: (v[k] ?? '').toString().trim(),
        sort_order: i + 1,
      })).filter(x => x.text || x.url);
    }

    return [];
  }

  function makeButtonsEditor(cfg){
    const listEl   = $(cfg.listId);
    const emptyEl  = $(cfg.emptyId);
    const addBtn   = $(cfg.addBtnId);
    const clearBtn = $(cfg.clearBtnId);

    function rowTpl(idx, b){
      const text = esc(b?.text || '');
      const url  = esc(b?.url || '');
      const so   = esc((b?.sort_order ?? (idx+1)).toString());
      return `
        <div class="ci-row" data-idx="${idx}">
          <div>
            <label class="form-label mb-1">Button Text</label>
            <input type="text" class="form-control ci-btn-text" placeholder="e.g., Apply Now" value="${text}">
          </div>
          <div>
            <label class="form-label mb-1">Button URL</label>
            <input type="text" class="form-control ci-btn-url" placeholder="https://… or /path" value="${url}">
          </div>
          <div>
            <label class="form-label mb-1">Sort</label>
            <input type="number" class="form-control ci-btn-sort" min="1" step="1" value="${so}">
          </div>
          <div class="d-flex gap-2 align-items-end">
            <button type="button" class="btn btn-light ci-icon-btn ci-btn-remove" title="Remove">
              <i class="fa fa-trash"></i>
            </button>
          </div>
        </div>
      `;
    }

    function setEmptyState(){
      const has = (listEl?.querySelectorAll('.ci-row')?.length || 0) > 0;
      if (emptyEl) emptyEl.style.display = has ? 'none' : '';
    }

    function readFromRows(){
      const rows = Array.from(listEl?.querySelectorAll('.ci-row') || []);
      const arr = rows.map((r, i) => {
        const t = (r.querySelector('.ci-btn-text')?.value ?? '').toString().trim();
        const u = (r.querySelector('.ci-btn-url')?.value ?? '').toString().trim();
        const soRaw = r.querySelector('.ci-btn-sort')?.value ?? '';
        const so = Number.isFinite(Number(soRaw)) && Number(soRaw) > 0 ? Number(soRaw) : (i + 1);
        return { text: t, url: u, sort_order: so };
      }).filter(x => x.text || x.url);

      // normalize sort
      arr.sort((a,b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
      return arr;
    }

    function sync(){
      setEmptyState();
      if (typeof cfg.onChange === 'function') cfg.onChange(readFromRows());
    }

    function setButtons(btns){
      const arr = normalizeButtons(btns);
      if (listEl) listEl.innerHTML = arr.map((b,i)=>rowTpl(i,b)).join('');
      setEmptyState();
      if (typeof cfg.onChange === 'function') cfg.onChange(readFromRows());
    }

    function addOne(){
      const current = readFromRows();
      current.push({ text:'', url:'', sort_order: (current.length + 1) });
      if (listEl) listEl.innerHTML = current.map((b,i)=>rowTpl(i,b)).join('');
      sync();
      const last = listEl?.querySelector('.ci-row:last-child .ci-btn-text');
      last && last.focus();
    }

    function clearAll(){
      if (listEl) listEl.innerHTML = '';
      setEmptyState();
      if (typeof cfg.onChange === 'function') cfg.onChange([]);
    }

    listEl?.addEventListener('input', debounce(sync, 120));
    listEl?.addEventListener('click', (e) => {
      const rm = e.target.closest('.ci-btn-remove');
      if (!rm) return;
      const row = rm.closest('.ci-row');
      row?.remove();
      sync();
    });

    addBtn?.addEventListener('click', addOne);
    clearBtn?.addEventListener('click', clearAll);

    return { setButtons, readFromRows, clearAll };
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const loading = $('ciLoading');
    const showLoading = (v) => { if (loading) loading.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('ciToastOk');
    const toastErrEl = $('ciToastErr');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;

    const ok = (m) => { const el=$('ciToastOkText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('ciToastErrText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = (json=false) => {
      const h = { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' };
      if (json) h['Content-Type'] = 'application/json';
      return h;
    };

    // ========= API (FIXED to match your routes; removes /current and /trash paths) =========
    const API = {
      base:  '/api/center-iframes',
      trash: '/api/center-iframes-trash',
    };
    // ====================================================================================

    // permissions
    const ACTOR = { role: '' };
    let canWrite=false, canDelete=false;

    function computePermissions(){
      const r = (ACTOR.role || '').toLowerCase();
      const writeRoles  = ['admin','director','principal','hod','faculty','technical_assistant','it_person','super_admin'];
      const deleteRoles = ['admin','director','principal','super_admin'];

      canWrite = writeRoles.includes(r);
      canDelete = deleteRoles.includes(r);

      $('ciCurrentControls') && ($('ciCurrentControls').style.display = canWrite ? 'flex' : 'none');
      $('ciCurrentExtra') && ($('ciCurrentExtra').style.display = canWrite ? 'flex' : 'none');

      $('ciButtonsControls') && ($('ciButtonsControls').style.display = canWrite ? 'flex' : 'none');
      $('ciMetaControls') && ($('ciMetaControls').style.display = canWrite ? 'flex' : 'none');

      $('ciModalButtonsControls') && ($('ciModalButtonsControls').style.display = canWrite ? 'flex' : 'none');
      $('ciModalMetaControls') && ($('ciModalMetaControls').style.display = canWrite ? 'flex' : 'none');

      $('ciWriteControls') && ($('ciWriteControls').style.display = canWrite ? 'flex' : 'none');

      const saveCurrentBtn = $('ciBtnSaveCurrent');
      if (saveCurrentBtn) saveCurrentBtn.disabled = !canWrite;
      const saveAsNewBtn = $('ciBtnSaveAsNew');
      if (saveAsNewBtn) saveAsNewBtn.disabled = !canWrite;
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

    // ===== Current elements =====
    const curUuid = $('ciCurrentUuid');
    const curId = $('ciCurrentId');
    const curTitle = $('ci_title');
    const curUrl = $('ci_iframe_url');
    const curActive = $('ci_is_active');
    const curIdentity = $('ciCurrentIdentity');
    const curUpdated = $('ciCurrentUpdated');
    const curMeta = $('ci_metadata_json');

    // Buttons editor (current)
    const curButtonsEditor = makeButtonsEditor({
      listId: 'ciButtonsList',
      emptyId: 'ciButtonsEmpty',
      addBtnId: 'ciBtnAddBtn',
      clearBtnId: 'ciBtnClearBtns',
      onChange: () => {}, // Quick Preview removed
    });

    // metadata controls
    $('ciBtnMetaPretty')?.addEventListener('click', () => prettyJson(curMeta));
    $('ciBtnMetaValidate')?.addEventListener('click', () => {
      const p = safeJsonParse(curMeta?.value || '', 'Metadata JSON');
      if (!p.ok) err(p.error);
      else ok('Metadata JSON looks valid');
    });

    // ===== Versions/Trash elements =====
    const perPageSel = $('ciPerPage');
    const searchInput = $('ciSearch');
    const btnReset = $('ciBtnReset');

    const tbodyVersions = $('ciTbodyVersions');
    const emptyVersions = $('ciEmptyVersions');
    const pagerVersions = $('ciPagerVersions');
    const infoVersions = $('ciInfoVersions');

    const tbodyTrash = $('ciTbodyTrash');
    const emptyTrash = $('ciEmptyTrash');
    const pagerTrash = $('ciPagerTrash');
    const infoTrash = $('ciInfoTrash');

    // filter modal
    const filterModalEl = $('ciFilterModal');
    const filterModal = filterModalEl ? bootstrap.Modal.getOrCreateInstance(filterModalEl) : null;
    const modalActive = $('ciModalActive');
    const modalSort = $('ciModalSort');
    const btnApplyFilters = $('ciBtnApplyFilters');

    // ✅ FIX: ensure cleanup happens after modal closes
    filterModalEl?.addEventListener('hidden.bs.modal', cleanupModalBackdrop);

    // item modal
    const itemModalEl = $('ciItemModal');
    const itemModal = itemModalEl ? bootstrap.Modal.getOrCreateInstance(itemModalEl) : null;
    // ✅ also protect item modal, just in case
    itemModalEl?.addEventListener('hidden.bs.modal', cleanupModalBackdrop);

    const itemModalTitle = $('ciItemModalTitle');
    const itemForm = $('ciItemForm');
    const saveBtn = $('ciSaveBtn');

    const itemUuid = $('ciItemUuid');
    const itemId = $('ciItemId');
    const itemIdentity = $('ciItemIdentity');
    const itemUpdated = $('ciItemUpdated');
    const itemBy = $('ciItemBy');

    const mTitle = $('ci_m_title');
    const mUrl = $('ci_m_iframe_url');
    const mActive = $('ci_m_is_active');
    const mMeta = $('ci_m_metadata_json');

    const modalButtonsEditor = makeButtonsEditor({
      listId: 'ciButtonsListModal',
      emptyId: 'ciButtonsEmptyModal',
      addBtnId: 'ciBtnAddBtnModal',
      clearBtnId: 'ciBtnClearBtnsModal',
      onChange: () => {},
    });

    // modal metadata controls
    $('ciBtnMetaPrettyModal')?.addEventListener('click', () => prettyJson(mMeta));
    $('ciBtnMetaValidateModal')?.addEventListener('click', () => {
      const p = safeJsonParse(mMeta?.value || '', 'Metadata JSON');
      if (!p.ok) err(p.error);
      else ok('Metadata JSON looks valid');
    });

    // state
    const state = {
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      filters: { q:'', active:'', sort:'-updated_at' },
      versions: { page:1, lastPage:1, items:[] },
      trash: { page:1, lastPage:1, items:[] }
    };

    function setEmpty(el, show){ if (el) el.style.display = show ? '' : 'none'; }

    function buildListUrl(kind){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state[kind].page));

      const q = (state.filters.q || '').trim();
      if (q) params.set('q', q);

      const s = state.filters.sort || '-updated_at';
      params.set('sort', s.startsWith('-') ? s.slice(1) : s);
      params.set('direction', s.startsWith('-') ? 'desc' : 'asc');

      // Status filter (also sends is_active for backward compatibility)
      if (state.filters.active !== ''){
        const isActive = String(state.filters.active) === '1';
        params.set('status', isActive ? 'active' : 'inactive');
        params.set('is_active', isActive ? '1' : '0');
      }

      if (kind === 'trash'){
        // Your route is GET /center-iframes-trash
        return `${API.trash}?${params.toString()}`;
      }
      return `${API.base}?${params.toString()}`;
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

    function buttonsCountFromRow(r){
      const btns = normalizeButtons(r?.buttons_json ?? r?.buttons ?? r?.metadata?.buttons ?? []);
      return btns.length;
    }

    function rowStatus(r){
      // prefer `status`, fallback to old boolean fields
      const s = (r?.status ?? '').toString().trim();
      if (s) return s;
      const b = !!Number(r?.is_active ?? r?.active ?? 0);
      return b ? 'active' : 'inactive';
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
        const t = r.title || '—';
        const url = r.iframe_url || r.url || r.iframe_src || '—';
        const btnCount = buttonsCountFromRow(r);
        const st = rowStatus(r);

        return `
          <tr data-uuid="${esc(uuid)}">
            <td><code>${esc(uuid)}</code></td>
            <td>${esc(t)}</td>
            <td title="${esc(url)}">${esc(url)}</td>
            <td><span class="badge badge-soft-primary">${btnCount}</span></td>
            <td>${statusBadge(st)}</td>
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
        const t = r.title || '—';
        return `
          <tr data-uuid="${esc(uuid)}">
            <td><code>${esc(uuid)}</code></td>
            <td>${esc(t)}</td>
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
      // FIX: no /current endpoint in your routes; we load latest item from index
      try{
        const params = new URLSearchParams();
        params.set('per_page', '1');
        params.set('page', '1');
        params.set('sort', 'updated_at');
        params.set('direction', 'desc');

        const res = await fetchWithTimeout(`${API.base}?${params.toString()}`, { headers: authHeaders(false) }, 12000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to load current');

        const items = Array.isArray(js.data) ? js.data : (Array.isArray(js.items) ? js.items : []);
        const item = items[0] || null;

        if (!item){
          // empty state
          if (curUuid) curUuid.value = '';
          if (curId) curId.value = '';
          if (curTitle) curTitle.value = '';
          if (curUrl) curUrl.value = '';
          if (curActive) curActive.checked = true; // default active
          if (curMeta) curMeta.value = '';

          curButtonsEditor.setButtons([]);

          if (curIdentity) curIdentity.textContent = '—';
          if (curUpdated) curUpdated.textContent = 'Updated: —';
          return;
        }

        if (curUuid) curUuid.value = item?.uuid || '';
        if (curId) curId.value = item?.id || '';

        if (curTitle) curTitle.value = item?.title ?? '';
        if (curUrl) curUrl.value = item?.iframe_url ?? item?.url ?? '';

        const st = rowStatus(item);
        if (curActive) curActive.checked = (st === 'active');

        const btns = normalizeButtons(item?.buttons_json ?? item?.buttons ?? item?.metadata?.buttons ?? []);
        curButtonsEditor.setButtons(btns);

        // metadata
        const meta = item?.metadata ?? null;
        if (curMeta){
          curMeta.value = meta == null ? '' : JSON.stringify(meta, null, 2);
        }

        if (curIdentity) curIdentity.textContent = item?.uuid ? `uuid: ${item.uuid}` : '—';
        if (curUpdated) curUpdated.textContent = item?.updated_at ? `Updated: ${item.updated_at}` : 'Updated: —';
      }catch(e){
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    async function loadList(kind){
      const tbody = (kind === 'trash') ? tbodyTrash : tbodyVersions;
      const cols = (kind === 'trash') ? 6 : 8;
      tbody.innerHTML = `<tr><td colspan="${cols}" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;

      try{
        const res = await fetchWithTimeout(buildListUrl(kind), { headers: authHeaders(false) }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to load');

        const items = Array.isArray(js.data) ? js.data : (Array.isArray(js.items) ? js.items : []);
        const p = js.pagination || js.meta || {};

        state[kind].items = items;
        state[kind].lastPage = parseInt(p.last_page || p.lastPage || 1, 10) || 1;

        const total = p.total ?? items.length;
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

    function readCurrentPayload(){
      const title = (curTitle?.value || '').trim();
      const url = (curUrl?.value || '').trim();

      if (!title) return { ok:false, error:'Title is required.' };
      if (!url) return { ok:false, error:'Iframe URL is required.' };

      const buttons = curButtonsEditor.readFromRows();
      for (const b of buttons){
        if (!b.text || !b.url) return { ok:false, error:'Each button must have text and url (or remove empty ones).' };
      }

      const metaParsed = safeJsonParse(curMeta?.value || '', 'Metadata JSON');
      if (!metaParsed.ok) return { ok:false, error: metaParsed.error };

      return {
        ok:true,
        payload: {
          title: title,
          iframe_url: url,
          status: statusFromSwitch(!!curActive?.checked),
          buttons_json: buttons,
          metadata: metaParsed.val
        }
      };
    }

    async function saveCurrent(mode){
      if (!canWrite) return;

      const rd = readCurrentPayload();
      if (!rd.ok){ err(rd.error || 'Invalid values'); return; }

      const uuid = (curUuid?.value || '').trim();
      const isCreate = (mode === 'new') || (!uuid);

      const endpoint = isCreate ? API.base : `${API.base}/${encodeURIComponent(uuid)}`;
      const method = isCreate ? 'POST' : 'PATCH';

      const btnMain = $('ciBtnSaveCurrent');
      const btnNew  = $('ciBtnSaveAsNew');

      setBtnLoading(btnMain, mode !== 'new');
      setBtnLoading(btnNew, mode === 'new');
      showLoading(true);

      try{
        const res = await fetchWithTimeout(endpoint, {
          method,
          headers: authHeaders(true),
          body: JSON.stringify(rd.payload)
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

        ok(isCreate ? (mode === 'new' ? 'Saved as new version' : 'Created') : 'Updated');
        await loadCurrent();
        await Promise.all([loadList('versions'), loadList('trash')]);
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        setBtnLoading(btnMain, false);
        setBtnLoading(btnNew, false);
        showLoading(false);
      }
    }

    $('ciBtnSaveCurrent')?.addEventListener('click', () => saveCurrent('upsert'));
    $('ciBtnSaveAsNew')?.addEventListener('click', async () => {
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

    $('ciBtnReload')?.addEventListener('click', async () => {
      showLoading(true);
      await loadCurrent();
      showLoading(false);
      ok('Reloaded');
    });

    // ===== Search / filters / per page =====
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
      state.filters = { q:'', active:'', sort:'-updated_at' };
      state.perPage = 20;

      if (searchInput) searchInput.value = '';
      if (perPageSel) perPageSel.value = '20';
      if (modalActive) modalActive.value = '';
      if (modalSort) modalSort.value = '-updated_at';

      state.versions.page = 1;
      state.trash.page = 1;
      loadList('versions');
      loadList('trash');
      ok('Filters reset');
    });

    filterModalEl?.addEventListener('show.bs.modal', () => {
      if (modalActive) modalActive.value = state.filters.active;
      if (modalSort) modalSort.value = state.filters.sort;
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.active = (modalActive?.value ?? '');
      state.filters.sort = (modalSort?.value ?? '-updated_at');
      state.versions.page = 1;

      // ✅ FIX: hide modal using the live instance + cleanup any leftover backdrop
      if (filterModalEl){
        bootstrap.Modal.getOrCreateInstance(filterModalEl).hide();
        // after fade transition
        setTimeout(cleanupModalBackdrop, 250);
      }

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

    function fillModalFromRow(row, viewOnly){
      const r = row || {};
      itemUuid.value = r.uuid || '';
      itemId.value = r.id || '';

      if (itemIdentity) itemIdentity.textContent = r.uuid ? `uuid: ${r.uuid}` : '—';
      if (itemUpdated) itemUpdated.textContent = r.updated_at ? `Updated: ${r.updated_at}` : 'Updated: —';
      if (itemBy) itemBy.textContent = (r.created_by_name || r.created_by_email || '—');

      mTitle.value = r.title ?? '';
      mUrl.value = r.iframe_url ?? r.url ?? '';
      mActive.checked = (rowStatus(r) === 'active');

      const btns = normalizeButtons(r.buttons_json ?? r.buttons ?? r.metadata?.buttons ?? []);
      modalButtonsEditor.setButtons(btns);

      const meta = r.metadata ?? null;
      if (mMeta){
        mMeta.value = meta == null ? '' : JSON.stringify(meta, null, 2);
      }

      const disable = !!viewOnly || !canWrite;
      itemForm.querySelectorAll('input,textarea').forEach(el => {
        if (el.id === 'ciItemUuid' || el.id === 'ciItemId') return;
        el.disabled = disable;
      });
      if (saveBtn) saveBtn.style.display = (!disable && !viewOnly) ? '' : 'none';

      itemForm.dataset.mode = viewOnly ? 'view' : 'edit';
    }

    // dropdown toggle fix
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

    // modal submit (edit only)
    itemForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (!canWrite) return;

      const mode = itemForm.dataset.mode || 'edit';
      if (mode === 'view') return;

      const uuid = (itemUuid.value || '').trim();
      if (!uuid){ err('Missing uuid'); return; }

      const title = (mTitle.value || '').trim();
      const url = (mUrl.value || '').trim();
      if (!title) { err('Title is required.'); return; }
      if (!url) { err('Iframe URL is required.'); return; }

      const buttons = modalButtonsEditor.readFromRows();
      for (const b of buttons){
        if (!b.text || !b.url) { err('Each button must have text and url (or remove empty ones).'); return; }
      }

      const metaParsed = safeJsonParse(mMeta?.value || '', 'Metadata JSON');
      if (!metaParsed.ok){ err(metaParsed.error); return; }

      const payload = {
        title: title,
        iframe_url: url,
        status: statusFromSwitch(!!mActive.checked),
        buttons_json: buttons,
        metadata: metaParsed.val
      };

      setBtnLoading(saveBtn, true);
      showLoading(true);

      try{
        const res = await fetchWithTimeout(`${API.base}/${encodeURIComponent(uuid)}`, {
          method: 'PATCH',
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

        ok('Version updated');
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

    // tab hooks
    document.querySelector('a[href="#ci-tab-current"]')?.addEventListener('shown.bs.tab', () => loadCurrent());
    document.querySelector('a[href="#ci-tab-versions"]')?.addEventListener('shown.bs.tab', () => loadList('versions'));
    document.querySelector('a[href="#ci-tab-trash"]')?.addEventListener('shown.bs.tab', () => loadList('trash'));

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
