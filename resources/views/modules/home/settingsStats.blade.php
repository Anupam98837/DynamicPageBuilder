{{-- resources/views/modules/home/settingsStats.blade.php --}}
@section('title','Stats Settings')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* =========================
  Stats Settings (Admin)
  - reference-inspired (rewritten)
  - no Quick Read section
========================= */

.st-wrap{max-width:1140px;margin:16px auto 42px;padding:0 4px;overflow:visible}

/* Cards */
.st-card{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.st-card .card-header{
  background:transparent;
  border-bottom:1px solid var(--line-soft);
}
.st-title{margin:0;font-weight:800}
.st-help{font-size:12.5px;color:var(--muted-color)}
.st-small{font-size:12.5px}

/* Chips */
.st-chip{
  display:inline-flex;align-items:center;gap:8px;
  padding:8px 10px;border-radius:999px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 90%, transparent);
  font-size:12.5px;
}
.st-chip i{opacity:.75}

/* Tabs */
.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface);
}
.tab-content,.tab-pane{overflow:visible}

/* Table shell */
.st-table.card{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.st-table .card-body{overflow:visible}
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

/* Items builder (stats_items_json) */
.st-repeater{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.st-repeater-top{
  display:flex;align-items:center;justify-content:space-between;
  gap:10px;padding:10px 12px;border-bottom:1px solid var(--line-soft);
}
.st-repeater-body{padding:12px}
.st-row{
  display:grid;
  grid-template-columns: 1.1fr .85fr 1.05fr .55fr auto;
  gap:10px;
  align-items:center;
  padding:10px;
  border:1px dashed color-mix(in oklab, var(--line-strong) 70%, transparent);
  border-radius:12px;
  background:color-mix(in oklab, var(--surface) 96%, transparent);
  margin-bottom:10px;
}
.st-row:last-child{margin-bottom:0}
.st-icon-btn{
  width:38px;height:38px;border-radius:12px;
  display:inline-flex;align-items:center;justify-content:center;
}

/* Metadata JSON box */
.st-json{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.st-json-top{
  display:flex;align-items:center;justify-content:space-between;
  gap:10px;padding:10px 12px;border-bottom:1px solid var(--line-soft);
}
.st-json textarea{
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

/* Preview box (background image) */
.st-preview{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.st-preview .top{
  display:flex;align-items:center;justify-content:space-between;
  gap:10px;padding:10px 12px;border-bottom:1px solid var(--line-soft);
}
.st-preview .body{padding:12px;}
.st-preview img{
  width:100%;max-height:280px;object-fit:cover;
  border-radius:12px;border:1px solid var(--line-soft);
  background:#fff;
}
.st-preview-empty{
  padding:12px;border:1px dashed var(--line-soft);
  border-radius:12px;color:var(--muted-color);font-size:12.5px;
}
.st-preview-meta{font-size:12.5px;color:var(--muted-color);margin-top:10px}

/* Dropdown safety */
.st-wrap .dropdown{position:relative}
.st-wrap .dd-toggle{border-radius:10px}
.st-wrap .dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:230px;
  z-index:99999;
}
.st-wrap .dropdown-menu.show{display:block !important}
.st-wrap .dropdown-item{display:flex;align-items:center;gap:.6rem}
.st-wrap .dropdown-item i{width:16px;text-align:center}
.st-wrap .dropdown-item.text-danger{color:var(--danger-color) !important}

/* Loading overlay */
.st-loading{
  position:fixed;inset:0;
  background:rgba(0,0,0,.45);
  display:flex;justify-content:center;align-items:center;
  z-index:9999;
  backdrop-filter:blur(2px);
}
.st-loading-card{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;flex-direction:column;align-items:center;gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,.3);
}
.st-spinner{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,.3);
  border-top:4px solid var(--primary-color);
  animation:stSpin 1s linear infinite;
}
@keyframes stSpin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

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
  animation:stSpin 1s linear infinite;
}

/* Responsive */
@media (max-width: 768px){
  .st-row{grid-template-columns:1fr;}
  .st-row .st-icon-btn{width:100%}
}
</style>
@endpush

@section('content')
<div class="st-wrap">

  {{-- Global Loading --}}
  <div id="stLoading" class="st-loading" style="display:none;">
    <div class="st-loading-card">
      <div class="st-spinner"></div>
      <div class="st-small">Loading…</div>
    </div>
  </div>

  {{-- Header --}}
  <div class="card st-card mb-3">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div>
        <div class="d-flex align-items-center gap-2">
          <i class="fa-solid fa-chart-column" style="opacity:.75;"></i>
          <h5 class="m-0 fw-bold">Stats Settings</h5>
        </div>
        <div class="st-help mt-1">
          Manage stats: <code>background_image_url</code> (required), <code>stats_items_json</code> and optional <code>metadata</code>.
          <b>Slug is required</b> and will be auto-generated.
        </div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <span class="st-chip"><i class="fa-solid fa-shield-halved"></i> Admin module</span>
        <span class="st-chip"><i class="fa-solid fa-image"></i> Background</span>
        <span class="st-chip"><i class="fa-solid fa-brackets-curly"></i> JSON</span>
      </div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#st-tab-current" role="tab" aria-selected="true">
        <i class="fa-solid fa-sliders me-2"></i>Current
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#st-tab-versions" role="tab" aria-selected="false">
        <i class="fa-solid fa-layer-group me-2"></i>Versions
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#st-tab-trash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Trash
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- ===================== CURRENT ===================== --}}
    <div class="tab-pane fade show active" id="st-tab-current" role="tabpanel">
      <div class="row g-3">
        <div class="col-12">
          <div class="card st-card">
            <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
              <div>
                <div class="st-title"><i class="fa-solid fa-gear me-2"></i>Current (latest)</div>
                <div class="st-help mt-1">
                  Loads the latest updated record. You can update it, or save as a new version.
                </div>
              </div>

              <div class="d-flex gap-2 flex-wrap" id="stCurrentControls" style="display:none;">
                <button type="button" class="btn btn-light" id="stBtnReload">
                  <i class="fa fa-rotate me-1"></i>Reload
                </button>
                <button type="button" class="btn btn-primary" id="stBtnSaveCurrent">
                  <i class="fa fa-floppy-disk me-1"></i>Save Current
                </button>
              </div>
            </div>

            <div class="card-body">
              <input type="hidden" id="stCurrentUuid">
              <input type="hidden" id="stCurrentId">

              <div class="row g-3">

                <div class="col-12">
                  <label class="form-label">Section Title (saved in metadata)</label>
                  <input id="st_section_title" class="form-control" placeholder="e.g., Our Achievements">
                  <div class="st-help mt-1">Your table does not have a title column, so this is stored in <code>metadata.section_title</code>.</div>
                </div>

                {{-- ✅ Slug (required, auto-generated) --}}
                <div class="col-12">
                  <label class="form-label">Slug <span class="text-danger">*</span></label>
                  <input id="st_slug" class="form-control" placeholder="auto-generated" readonly>
                  <div class="st-help mt-1">
                    Auto-generated from Section Title. Must be unique. (You don’t type here.)
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label">Section Subtitle (saved in metadata)</label>
                  <input id="st_section_subtitle" class="form-control" placeholder="e.g., Numbers that reflect our growth">
                  <div class="st-help mt-1">Stored in <code>metadata.section_subtitle</code>.</div>
                </div>

                {{-- ✅ Background image (REQUIRED in DB/API) --}}
                <div class="col-12">
                  <div class="row g-3">
                    <div class="col-lg-6">
                      <label class="form-label">Background Image URL/Path <span class="text-danger">*</span></label>
                      <input id="st_background_image_url" class="form-control" placeholder="e.g., depy_uploads/stats/bg.jpg or https://...">
                      <div class="st-help mt-1">
                        Required field (<code>background_image_url</code>). If you upload a file, we auto-fill a reasonable path.
                      </div>

                      <div class="mt-3">
                        <label class="form-label">Upload Background Image (optional)</label>
                        <input type="file" id="st_background_image_file" class="form-control" accept="image/*">
                        <div class="st-help mt-1">Opens file picker (Windows/Mac) and shows preview. Upload is sent with save request.</div>
                      </div>
                    </div>

                    <div class="col-lg-6">
                      <div class="st-preview">
                        <div class="top">
                          <div class="fw-semibold"><i class="fa fa-image me-2"></i>Background Preview</div>
                          <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-light btn-sm" id="stBtnOpenBg" style="display:none;">
                              <i class="fa fa-up-right-from-square me-1"></i>Open
                            </button>
                          </div>
                        </div>
                        <div class="body">
                          <img id="stBgPreview" src="" alt="Preview" style="display:none;">
                          <div id="stBgEmpty" class="st-preview-empty">No background image selected.</div>
                          <div class="st-preview-meta" id="stBgMeta" style="display:none;">—</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="st_is_active">
                    <label class="form-check-label" for="st_is_active"><b>Status: Published</b></label>
                  </div>
                  <div class="st-help mt-1">Maps to DB/API field <code>status</code>: <code>published</code> / <code>draft</code>.</div>
                </div>

                {{-- Items builder (stats_items_json) --}}
                <div class="col-12">
                  <div class="st-repeater">
                    <div class="st-repeater-top">
                      <div class="fw-semibold">
                        <i class="fa-solid fa-list-check me-2"></i>Stats Items (<code>stats_items_json</code>) — Label + Value + Icon + Sort
                      </div>
                      <div class="d-flex gap-2 flex-wrap" id="stItemsControls" style="display:none;">
                        <button type="button" class="btn btn-light btn-sm" id="stBtnAddItem">
                          <i class="fa fa-plus me-1"></i>Add Item
                        </button>
                        <button type="button" class="btn btn-light btn-sm" id="stBtnClearItems">
                          <i class="fa fa-eraser me-1"></i>Clear
                        </button>
                      </div>
                    </div>

                    <div class="st-repeater-body">
                      <div id="stItemsList"></div>

                      <div id="stItemsEmpty" class="text-center text-muted py-3" style="display:none;">
                        <i class="fa-regular fa-circle-plus me-1"></i>No items yet. Click <b>Add Item</b>.
                      </div>
                    </div>
                  </div>

                  <div class="st-help mt-2">
                    Stored as JSON array in <code>stats_items_json</code>, like:
                    <code>[{"label":"Students Placed","value":"250+","icon_class":"fa-solid fa-user-graduate","sort_order":1}]</code>
                  </div>
                </div>

                {{-- Metadata JSON --}}
                <div class="col-12">
                  <div class="st-json">
                    <div class="st-json-top">
                      <div class="fw-semibold"><i class="fa-solid fa-database me-2"></i>Metadata (<code>metadata</code>)</div>
                      <div class="d-flex gap-2 flex-wrap" id="stMetaControls" style="display:none;">
                        <button type="button" class="btn btn-light btn-sm" id="stBtnMetaPretty">
                          <i class="fa fa-align-left me-1"></i>Pretty
                        </button>
                        <button type="button" class="btn btn-light btn-sm" id="stBtnMetaValidate">
                          <i class="fa fa-circle-check me-1"></i>Validate
                        </button>
                      </div>
                    </div>
                    <textarea id="st_metadata_json" spellcheck="false" placeholder='{"note":"Any extra data goes here"}'></textarea>
                  </div>
                  <div class="st-help mt-2">
                    Optional JSON stored in <code>metadata</code>. Keep it valid JSON.
                  </div>
                </div>

                <div class="col-12">
                  <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                    <div class="st-help">
                      <span class="me-2"><i class="fa-regular fa-id-card me-1"></i><span id="stCurrentIdentity">—</span></span>
                      <span class="me-2"><i class="fa-regular fa-clock me-1"></i><span id="stCurrentUpdated">—</span></span>
                    </div>
                    <div class="d-flex gap-2 flex-wrap" id="stCurrentExtra" style="display:none;">
                      <button type="button" class="btn btn-outline-primary" id="stBtnSaveAsNew">
                        <i class="fa fa-code-branch me-1"></i>Save as New Version
                      </button>
                    </div>
                  </div>
                </div>

              </div>{{-- row --}}
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ===================== VERSIONS ===================== --}}
    <div class="tab-pane fade" id="st-tab-versions" role="tabpanel">

      {{-- Toolbar (pagination NOT here) --}}
      <div class="card st-card mb-3">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
          <div class="d-flex align-items-center flex-wrap gap-2">
            <div class="position-relative" style="min-width:280px;">
              <input id="stSearch" type="search" class="form-control ps-5" placeholder="Search by uuid/slug…">
              <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
            </div>

            <button id="stBtnFilter" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#stFilterModal">
              <i class="fa fa-sliders me-1"></i>Filter
            </button>

            <button id="stBtnReset" class="btn btn-light">
              <i class="fa fa-rotate-left me-1"></i>Reset
            </button>
          </div>

          <div class="d-flex gap-2 flex-wrap" id="stWriteControls" style="display:none;">
            {{-- intentionally empty --}}
          </div>
        </div>
      </div>

      {{-- Table --}}
      <div class="card st-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:240px;">UUID</th>
                  <th style="width:220px;">Slug</th>
                  <th style="width:140px;">Items</th>
                  <th style="width:160px;">Status</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:140px;">By</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="stTbodyVersions">
                <tr><td colspan="7" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="stEmptyVersions" class="p-4 text-center" style="display:none;">
            <i class="fa-solid fa-layer-group mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No versions found.</div>
          </div>

          {{-- Footer (per-page + pagination lives here) --}}
          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="stInfoVersions">—</div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
              <div class="d-flex align-items-center gap-2">
                <label class="text-muted small mb-0">Per Page</label>
                <select id="stPerPage" class="form-select" style="width:96px;">
                  <option>10</option>
                  <option selected>20</option>
                  <option>50</option>
                  <option>100</option>
                </select>
              </div>

              <nav><ul id="stPagerVersions" class="pagination mb-0"></ul></nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- ===================== TRASH ===================== --}}
    <div class="tab-pane fade" id="st-tab-trash" role="tabpanel">

      <div class="card st-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:240px;">UUID</th>
                  <th style="width:220px;">Slug</th>
                  <th style="width:170px;">Deleted At</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:140px;">By</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="stTbodyTrash">
                <tr><td colspan="6" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="stEmptyTrash" class="p-4 text-center" style="display:none;">
            <i class="fa fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>Trash is empty.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="stInfoTrash">—</div>
            <nav><ul id="stPagerTrash" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="stFilterModal" tabindex="-1" aria-hidden="true">
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
            <select id="stModalActive" class="form-select">
              <option value="">All</option>
              <option value="1">Published only</option>
              <option value="0">Draft only</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="stModalSort" class="form-select">
              <option value="-updated_at">Recently Updated</option>
              <option value="updated_at">Oldest Updated</option>
              <option value="-created_at">Newest Created</option>
              <option value="created_at">Oldest Created</option>
              <option value="slug">Slug A-Z</option>
              <option value="-slug">Slug Z-A</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="stBtnApplyFilters" class="btn btn-primary">
          <i class="fa fa-check me-1"></i>Apply Filters
        </button>
      </div>
    </div>
  </div>
</div>

{{-- View/Edit Modal --}}
<div class="modal fade" id="stItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form class="modal-content" id="stItemForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="stItemModalTitle">View</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="stItemUuid">
        <input type="hidden" id="stItemId">

        <div class="row g-3">
          <div class="col-12">
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <span class="st-chip"><i class="fa-regular fa-id-badge"></i><span id="stItemIdentity">—</span></span>
              <span class="st-chip"><i class="fa-regular fa-clock"></i><span id="stItemUpdated">—</span></span>
              <span class="st-chip"><i class="fa-regular fa-user"></i><span id="stItemBy">—</span></span>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Section Title (metadata)</label>
            <input id="st_m_section_title" class="form-control" placeholder="Title">
          </div>

          {{-- ✅ Slug (required, auto-generated) --}}
          <div class="col-12">
            <label class="form-label">Slug <span class="text-danger">*</span></label>
            <input id="st_m_slug" class="form-control" placeholder="auto-generated" readonly>
            <div class="st-help mt-1">Auto-generated from Section Title.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Section Subtitle (metadata)</label>
            <input id="st_m_section_subtitle" class="form-control" placeholder="Subtitle">
          </div>

          {{-- ✅ Background image required --}}
          <div class="col-12">
            <div class="row g-3">
              <div class="col-lg-6">
                <label class="form-label">Background Image URL/Path <span class="text-danger">*</span></label>
                <input id="st_m_background_image_url" class="form-control" placeholder="e.g., depy_uploads/stats/bg.jpg or https://...">

                <div class="mt-3">
                  <label class="form-label">Upload Background Image (optional)</label>
                  <input type="file" id="st_m_background_image_file" class="form-control" accept="image/*">
                </div>
              </div>

              <div class="col-lg-6">
                <div class="st-preview">
                  <div class="top">
                    <div class="fw-semibold"><i class="fa fa-image me-2"></i>Background Preview</div>
                    <div class="d-flex align-items-center gap-2">
                      <button type="button" class="btn btn-light btn-sm" id="stBtnOpenBgModal" style="display:none;">
                        <i class="fa fa-up-right-from-square me-1"></i>Open
                      </button>
                    </div>
                  </div>
                  <div class="body">
                    <img id="stBgPreviewModal" src="" alt="Preview" style="display:none;">
                    <div id="stBgEmptyModal" class="st-preview-empty">No background image selected.</div>
                    <div class="st-preview-meta" id="stBgMetaModal" style="display:none;">—</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="st_m_is_active">
              <label class="form-check-label" for="st_m_is_active"><b>Status: Published</b></label>
            </div>
            <div class="st-help mt-1">Maps to <code>status</code>: <code>published</code> / <code>draft</code>.</div>
          </div>

          <div class="col-12">
            <div class="st-repeater">
              <div class="st-repeater-top">
                <div class="fw-semibold"><i class="fa-solid fa-list-check me-2"></i>Items (<code>stats_items_json</code>)</div>
                <div class="d-flex gap-2 flex-wrap" id="stModalItemsControls" style="display:none;">
                  <button type="button" class="btn btn-light btn-sm" id="stBtnAddItemModal">
                    <i class="fa fa-plus me-1"></i>Add Item
                  </button>
                  <button type="button" class="btn btn-light btn-sm" id="stBtnClearItemsModal">
                    <i class="fa fa-eraser me-1"></i>Clear
                  </button>
                </div>
              </div>
              <div class="st-repeater-body">
                <div id="stItemsListModal"></div>
                <div id="stItemsEmptyModal" class="text-center text-muted py-3" style="display:none;">
                  No items.
                </div>
              </div>
            </div>
          </div>

          <div class="col-12">
            <div class="st-json">
              <div class="st-json-top">
                <div class="fw-semibold"><i class="fa-solid fa-database me-2"></i>Metadata (<code>metadata</code>)</div>
                <div class="d-flex gap-2 flex-wrap" id="stModalMetaControls" style="display:none;">
                  <button type="button" class="btn btn-light btn-sm" id="stBtnMetaPrettyModal">
                    <i class="fa fa-align-left me-1"></i>Pretty
                  </button>
                  <button type="button" class="btn btn-light btn-sm" id="stBtnMetaValidateModal">
                    <i class="fa fa-circle-check me-1"></i>Validate
                  </button>
                </div>
              </div>
              <textarea id="st_m_metadata_json" spellcheck="false" placeholder='{"note":"Optional metadata"}'></textarea>
            </div>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary" id="stSaveBtn">
          <i class="fa fa-floppy-disk me-1"></i> Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="stToastOk" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="stToastOkText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="stToastErr" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="stToastErrText">Something went wrong</div>
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
  if (window.__STATS_SETTINGS_INIT__) return;
  window.__STATS_SETTINGS_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=300) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  function bytes(n){
    const b = Number(n || 0);
    if (!b) return '—';
    const u = ['B','KB','MB','GB'];
    let i=0, v=b;
    while (v>=1024 && i<u.length-1){ v/=1024; i++; }
    return `${v.toFixed(i?1:0)} ${u[i]}`;
  }

  function normalizeUrl(url){
    const u = (url || '').toString().trim();
    if (!u) return '';
    if (/^(data:|blob:|https?:\/\/)/i.test(u)) return u;
    if (u.startsWith('/')) return window.location.origin + u;
    return window.location.origin + '/' + u;
  }

  // ✅ Slugify helper (auto generates required slug)
  function slugify(input){
    const s = (input ?? '').toString().trim().toLowerCase();
    if (!s) return '';
    const ascii = s.normalize('NFKD').replace(/[\u0300-\u036f]/g, '');
    return ascii
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-+|-+$/g, '');
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

  // ✅ status mapping aligned with stats.status default 'draft'
  function statusFromSwitch(checked){
    return checked ? 'published' : 'draft';
  }

  function statusBadge(val){
    const s = (val ?? '').toString().toLowerCase().trim();
    if (s === 'published') return `<span class="badge badge-soft-success">published</span>`;
    if (s === 'draft') return `<span class="badge badge-soft-warning">draft</span>`;
    if (s === 'archived') return `<span class="badge badge-soft-muted">archived</span>`;
    if (!s) return `<span class="badge badge-soft-muted">—</span>`;
    return `<span class="badge badge-soft-muted">${esc(s)}</span>`;
  }

  function normalizeItems(any){
    if (any == null) return [];
    let v = any;

    if (typeof v === 'string'){
      const p = safeJsonParse(v, 'Items');
      if (!p.ok) return [];
      v = p.val;
    }

    if (Array.isArray(v)){
      return v.map((x, i) => ({
        label: (x?.label ?? x?.title ?? x?.name ?? '').toString().trim(),
        value: (x?.value ?? x?.number ?? x?.count ?? '').toString().trim(),
        icon_class: (x?.icon_class ?? x?.icon ?? x?.iconClass ?? '').toString().trim(),
        sort_order: Number.isFinite(Number(x?.sort_order)) ? Number(x.sort_order) : (i + 1),
      })).filter(x => x.label || x.value || x.icon_class);
    }

    if (typeof v === 'object'){
      return Object.keys(v).map((k, i) => ({
        label: k.toString().trim(),
        value: (v[k] ?? '').toString().trim(),
        icon_class: '',
        sort_order: i + 1,
      })).filter(x => x.label || x.value);
    }

    return [];
  }

  function makeItemsEditor(cfg){
    const listEl   = $(cfg.listId);
    const emptyEl  = $(cfg.emptyId);
    const addBtn   = $(cfg.addBtnId);
    const clearBtn = $(cfg.clearBtnId);

    function rowTpl(idx, it){
      const label = esc(it?.label || '');
      const value = esc(it?.value || '');
      const icon  = esc(it?.icon_class || '');
      const so    = esc((it?.sort_order ?? (idx+1)).toString());

      return `
        <div class="st-row" data-idx="${idx}">
          <div>
            <label class="form-label mb-1">Label</label>
            <input type="text" class="form-control st-it-label" placeholder="e.g., Students Placed" value="${label}">
          </div>

          <div>
            <label class="form-label mb-1">Value</label>
            <input type="text" class="form-control st-it-value" placeholder="e.g., 250+" value="${value}">
          </div>

          <div>
            <label class="form-label mb-1">Icon Class (optional)</label>
            <input type="text" class="form-control st-it-icon" placeholder="e.g., fa-solid fa-user-graduate" value="${icon}">
          </div>

          <div>
            <label class="form-label mb-1">Sort</label>
            <input type="number" class="form-control st-it-sort" min="1" step="1" value="${so}">
          </div>

          <div class="d-flex gap-2 align-items-end">
            <button type="button" class="btn btn-light st-icon-btn st-it-remove" title="Remove">
              <i class="fa fa-trash"></i>
            </button>
          </div>
        </div>
      `;
    }

    function setEmptyState(){
      const has = (listEl?.querySelectorAll('.st-row')?.length || 0) > 0;
      if (emptyEl) emptyEl.style.display = has ? 'none' : '';
    }

    function readFromRows(){
      const rows = Array.from(listEl?.querySelectorAll('.st-row') || []);
      const arr = rows.map((r, i) => {
        const label = (r.querySelector('.st-it-label')?.value ?? '').toString().trim();
        const value = (r.querySelector('.st-it-value')?.value ?? '').toString().trim();
        const icon  = (r.querySelector('.st-it-icon')?.value ?? '').toString().trim();
        const soRaw = r.querySelector('.st-it-sort')?.value ?? '';
        const so = Number.isFinite(Number(soRaw)) && Number(soRaw) > 0 ? Number(soRaw) : (i + 1);
        return { label, value, icon_class: icon, sort_order: so };
      }).filter(x => x.label || x.value || x.icon_class);

      arr.sort((a,b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
      return arr;
    }

    function sync(){
      setEmptyState();
      if (typeof cfg.onChange === 'function') cfg.onChange(readFromRows());
    }

    function setItems(items){
      const arr = normalizeItems(items);
      if (listEl) listEl.innerHTML = arr.map((it,i)=>rowTpl(i,it)).join('');
      setEmptyState();
      if (typeof cfg.onChange === 'function') cfg.onChange(readFromRows());
    }

    function addOne(){
      const current = readFromRows();
      current.push({ label:'', value:'', icon_class:'', sort_order: (current.length + 1) });
      if (listEl) listEl.innerHTML = current.map((it,i)=>rowTpl(i,it)).join('');
      sync();
      const last = listEl?.querySelector('.st-row:last-child .st-it-label');
      last && last.focus();
    }

    function clearAll(){
      if (listEl) listEl.innerHTML = '';
      setEmptyState();
      if (typeof cfg.onChange === 'function') cfg.onChange([]);
    }

    listEl?.addEventListener('input', debounce(sync, 120));
    listEl?.addEventListener('click', (e) => {
      const rm = e.target.closest('.st-it-remove');
      if (!rm) return;
      const row = rm.closest('.st-row');
      row?.remove();
      sync();
    });

    addBtn?.addEventListener('click', addOne);
    clearBtn?.addEventListener('click', clearAll);

    return { setItems, readFromRows, clearAll };
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const loading = $('stLoading');
    const showLoading = (v) => { if (loading) loading.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('stToastOk');
    const toastErrEl = $('stToastErr');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;

    const ok = (m) => { const el=$('stToastOkText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('stToastErrText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = (json=false) => {
      const h = { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' };
      if (json) h['Content-Type'] = 'application/json';
      return h;
    };

    // ========= API (update paths if your Stats routes differ) =========
    const API = {
      base:  '/api/stats',
      trash: '/api/stats-trash',
    };
    // ================================================================

    // permissions
    const ACTOR = { role: '' };
    let canWrite=false, canDelete=false;

    function computePermissions(){
      const r = (ACTOR.role || '').toLowerCase();
      const writeRoles  = ['admin','director','principal','hod','faculty','technical_assistant','it_person','super_admin'];
      const deleteRoles = ['admin','director','principal','super_admin'];

      canWrite = writeRoles.includes(r);
      canDelete = deleteRoles.includes(r);

      $('stCurrentControls') && ($('stCurrentControls').style.display = canWrite ? 'flex' : 'none');
      $('stCurrentExtra') && ($('stCurrentExtra').style.display = canWrite ? 'flex' : 'none');

      $('stItemsControls') && ($('stItemsControls').style.display = canWrite ? 'flex' : 'none');
      $('stMetaControls') && ($('stMetaControls').style.display = canWrite ? 'flex' : 'none');

      $('stModalItemsControls') && ($('stModalItemsControls').style.display = canWrite ? 'flex' : 'none');
      $('stModalMetaControls') && ($('stModalMetaControls').style.display = canWrite ? 'flex' : 'none');

      $('stWriteControls') && ($('stWriteControls').style.display = canWrite ? 'flex' : 'none');

      const saveCurrentBtn = $('stBtnSaveCurrent');
      if (saveCurrentBtn) saveCurrentBtn.disabled = !canWrite;
      const saveAsNewBtn = $('stBtnSaveAsNew');
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

    function rowStatus(r){
      const s = (r?.status ?? '').toString().trim();
      return s || 'draft';
    }

    function itemsCountFromRow(r){
      const items = normalizeItems(
        r?.stats_items_json ??
        r?.stats_items ??
        r?.items_json ??
        r?.items ??
        r?.metadata?.stats_items_json ??
        []
      );
      return items.length;
    }

    function getRowSlug(r){
      return (r?.slug ?? r?.section_slug ?? '').toString().trim();
    }

    // Since your table doesn't have title/subtitle columns, use metadata for UI:
    function getRowTitle(r){
      return (
        r?.title ??
        r?.section_title ??
        r?.heading ??
        r?.name ??
        r?.metadata?.section_title ??
        r?.metadata?.title ??
        '—'
      );
    }
    function getRowSubtitle(r){
      return (
        r?.subtitle ??
        r?.section_subtitle ??
        r?.subheading ??
        r?.metadata?.section_subtitle ??
        r?.metadata?.subtitle ??
        ''
      );
    }

    function getRowBg(r){
      return (r?.background_image_url ?? r?.background_image ?? r?.bg_image_url ?? '').toString().trim();
    }

    // ===== Current elements =====
    const curUuid = $('stCurrentUuid');
    const curId = $('stCurrentId');

    const curSectionTitle = $('st_section_title');
    const curSlug = $('st_slug');
    const curSectionSubtitle = $('st_section_subtitle');
    const curActive = $('st_is_active');

    const curBgUrl = $('st_background_image_url');
    const curBgFile = $('st_background_image_file');

    const curBgPreview = $('stBgPreview');
    const curBgEmpty = $('stBgEmpty');
    const curBgMeta = $('stBgMeta');
    const curBtnOpenBg = $('stBtnOpenBg');

    const curIdentity = $('stCurrentIdentity');
    const curUpdated = $('stCurrentUpdated');

    const curMeta = $('st_metadata_json');

    const curItemsEditor = makeItemsEditor({
      listId: 'stItemsList',
      emptyId: 'stItemsEmpty',
      addBtnId: 'stBtnAddItem',
      clearBtnId: 'stBtnClearItems',
      onChange: () => {}, // no preview
    });

    // ✅ auto-generate slug on title input
    const syncSlugFromTitle = debounce(() => {
      if (!curSlug) return;
      const base = (curSectionTitle?.value || '').trim();
      curSlug.value = slugify(base);
    }, 80);
    curSectionTitle?.addEventListener('input', syncSlugFromTitle);

    // metadata controls
    $('stBtnMetaPretty')?.addEventListener('click', () => prettyJson(curMeta));
    $('stBtnMetaValidate')?.addEventListener('click', () => {
      const p = safeJsonParse(curMeta?.value || '', 'Metadata JSON');
      if (!p.ok) err(p.error);
      else ok('Metadata JSON looks valid');
    });

    // ===== Background preview logic (Current) =====
    let bgObjectUrl = null;
    function clearBgPreview(revoke=true){
      if (revoke && bgObjectUrl){
        try{ URL.revokeObjectURL(bgObjectUrl); }catch(_){}
      }
      bgObjectUrl = null;
      if (curBgPreview){ curBgPreview.style.display='none'; curBgPreview.removeAttribute('src'); }
      if (curBgEmpty) curBgEmpty.style.display='';
      if (curBgMeta){ curBgMeta.style.display='none'; curBgMeta.textContent='—'; }
      if (curBtnOpenBg){ curBtnOpenBg.style.display='none'; curBtnOpenBg.onclick=null; }
    }

    function setBgPreview(url, metaText=''){
      const u = normalizeUrl(url);
      if (!u){ clearBgPreview(true); return; }
      if (curBgPreview){ curBgPreview.style.display=''; curBgPreview.src=u; }
      if (curBgEmpty) curBgEmpty.style.display='none';
      if (curBgMeta){ curBgMeta.style.display = metaText ? '' : 'none'; curBgMeta.textContent = metaText || ''; }
      if (curBtnOpenBg){
        curBtnOpenBg.style.display = '';
        curBtnOpenBg.onclick = () => window.open(u, '_blank', 'noopener');
      }
    }

    curBgFile?.addEventListener('change', () => {
      const f = curBgFile.files?.[0];
      if (!f) return;
      if (bgObjectUrl){ try{ URL.revokeObjectURL(bgObjectUrl); }catch(_){ } }
      bgObjectUrl = URL.createObjectURL(f);
      setBgPreview(bgObjectUrl, `${f.name || 'image'} • ${bytes(f.size)}`);

      // ✅ IMPORTANT: background_image_url is REQUIRED in validation.
      // If user didn't type it, auto-fill a reasonable path.
      if (curBgUrl && !(curBgUrl.value || '').trim()){
        const safeName = (f.name || 'background.jpg').replace(/\s+/g,'-');
        curBgUrl.value = `depy_uploads/stats/${safeName}`;
      }
    });

    curBgUrl?.addEventListener('input', debounce(() => {
      const v = (curBgUrl.value || '').trim();
      if (!v) { clearBgPreview(false); return; }
      setBgPreview(v, 'Using URL/path');
    }, 250));

    // ===== Versions/Trash elements =====
    const perPageSel = $('stPerPage');
    const searchInput = $('stSearch');
    const btnReset = $('stBtnReset');

    const tbodyVersions = $('stTbodyVersions');
    const emptyVersions = $('stEmptyVersions');
    const pagerVersions = $('stPagerVersions');
    const infoVersions = $('stInfoVersions');

    const tbodyTrash = $('stTbodyTrash');
    const emptyTrash = $('stEmptyTrash');
    const pagerTrash = $('stPagerTrash');
    const infoTrash = $('stInfoTrash');

    // filter modal
    const filterModalEl = $('stFilterModal');
    const filterModal = filterModalEl ? new bootstrap.Modal(filterModalEl) : null;
    const modalActive = $('stModalActive');
    const modalSort = $('stModalSort');
    const btnApplyFilters = $('stBtnApplyFilters');

    // item modal
    const itemModalEl = $('stItemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;
    const itemModalTitle = $('stItemModalTitle');
    const itemForm = $('stItemForm');
    const saveBtn = $('stSaveBtn');

    const itemUuid = $('stItemUuid');
    const itemId = $('stItemId');
    const itemIdentity = $('stItemIdentity');
    const itemUpdated = $('stItemUpdated');
    const itemBy = $('stItemBy');

    const mSectionTitle = $('st_m_section_title');
    const mSlug = $('st_m_slug');
    const mSectionSubtitle = $('st_m_section_subtitle');
    const mActive = $('st_m_is_active');
    const mMeta = $('st_m_metadata_json');

    const mBgUrl = $('st_m_background_image_url');
    const mBgFile = $('st_m_background_image_file');
    const mBgPreview = $('stBgPreviewModal');
    const mBgEmpty = $('stBgEmptyModal');
    const mBgMeta = $('stBgMetaModal');
    const mBtnOpen = $('stBtnOpenBgModal');

    const modalItemsEditor = makeItemsEditor({
      listId: 'stItemsListModal',
      emptyId: 'stItemsEmptyModal',
      addBtnId: 'stBtnAddItemModal',
      clearBtnId: 'stBtnClearItemsModal',
      onChange: () => {},
    });

    // ✅ auto-generate modal slug on modal title input
    const syncModalSlugFromTitle = debounce(() => {
      if (!mSlug) return;
      const base = (mSectionTitle?.value || '').trim();
      mSlug.value = slugify(base);
    }, 80);
    mSectionTitle?.addEventListener('input', syncModalSlugFromTitle);

    // modal metadata controls
    $('stBtnMetaPrettyModal')?.addEventListener('click', () => prettyJson(mMeta));
    $('stBtnMetaValidateModal')?.addEventListener('click', () => {
      const p = safeJsonParse(mMeta?.value || '', 'Metadata JSON');
      if (!p.ok) err(p.error);
      else ok('Metadata JSON looks valid');
    });

    // ===== Background preview logic (Modal) =====
    let bgObjectUrlModal = null;
    function clearBgPreviewModal(revoke=true){
      if (revoke && bgObjectUrlModal){
        try{ URL.revokeObjectURL(bgObjectUrlModal); }catch(_){}
      }
      bgObjectUrlModal = null;
      if (mBgPreview){ mBgPreview.style.display='none'; mBgPreview.removeAttribute('src'); }
      if (mBgEmpty) mBgEmpty.style.display='';
      if (mBgMeta){ mBgMeta.style.display='none'; mBgMeta.textContent='—'; }
      if (mBtnOpen){ mBtnOpen.style.display='none'; mBtnOpen.onclick=null; }
    }
    function setBgPreviewModal(url, metaText=''){
      const u = normalizeUrl(url);
      if (!u){ clearBgPreviewModal(true); return; }
      if (mBgPreview){ mBgPreview.style.display=''; mBgPreview.src=u; }
      if (mBgEmpty) mBgEmpty.style.display='none';
      if (mBgMeta){ mBgMeta.style.display = metaText ? '' : 'none'; mBgMeta.textContent = metaText || ''; }
      if (mBtnOpen){
        mBtnOpen.style.display = '';
        mBtnOpen.onclick = () => window.open(u, '_blank', 'noopener');
      }
    }

    mBgFile?.addEventListener('change', () => {
      const f = mBgFile.files?.[0];
      if (!f) return;
      if (bgObjectUrlModal){ try{ URL.revokeObjectURL(bgObjectUrlModal); }catch(_){ } }
      bgObjectUrlModal = URL.createObjectURL(f);
      setBgPreviewModal(bgObjectUrlModal, `${f.name || 'image'} • ${bytes(f.size)}`);

      // required field auto-fill
      if (mBgUrl && !(mBgUrl.value || '').trim()){
        const safeName = (f.name || 'background.jpg').replace(/\s+/g,'-');
        mBgUrl.value = `depy_uploads/stats/${safeName}`;
      }
    });

    mBgUrl?.addEventListener('input', debounce(() => {
      const v = (mBgUrl.value || '').trim();
      if (!v) { clearBgPreviewModal(false); return; }
      setBgPreviewModal(v, 'Using URL/path');
    }, 250));

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

      if (state.filters.active !== ''){
        const isPub = String(state.filters.active) === '1';
        params.set('status', isPub ? 'published' : 'draft');
      }

      return (kind === 'trash') ? `${API.trash}?${params.toString()}` : `${API.base}?${params.toString()}`;
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
        const slug = getRowSlug(r) || '—';
        const itemsCount = itemsCountFromRow(r);
        const st = rowStatus(r);

        return `
          <tr data-uuid="${esc(uuid)}">
            <td><code>${esc(uuid)}</code></td>
            <td><code>${esc(slug)}</code></td>
            <td><span class="badge badge-soft-primary">${itemsCount}</span></td>
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
        const slug = getRowSlug(r) || '—';

        return `
          <tr data-uuid="${esc(uuid)}">
            <td><code>${esc(uuid)}</code></td>
            <td><code>${esc(slug)}</code></td>
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
          if (curUuid) curUuid.value = '';
          if (curId) curId.value = '';
          if (curSectionTitle) curSectionTitle.value = '';
          if (curSlug) curSlug.value = '';
          if (curSectionSubtitle) curSectionSubtitle.value = '';
          if (curBgUrl) curBgUrl.value = '';
          if (curBgFile) curBgFile.value = '';
          clearBgPreview(true);

          if (curActive) curActive.checked = false;
          if (curMeta) curMeta.value = '';
          curItemsEditor.setItems([]);

          if (curIdentity) curIdentity.textContent = '—';
          if (curUpdated) curUpdated.textContent = 'Updated: —';
          return;
        }

        if (curUuid) curUuid.value = item?.uuid || '';
        if (curId) curId.value = item?.id || '';

        const title = getRowTitle(item) === '—' ? '' : (getRowTitle(item) || '');
        if (curSectionTitle) curSectionTitle.value = title;

        const slug = getRowSlug(item) || slugify(title);
        if (curSlug) curSlug.value = slug;

        if (curSectionSubtitle) curSectionSubtitle.value = getRowSubtitle(item) || '';

        // bg
        const bg = getRowBg(item);
        if (curBgUrl) curBgUrl.value = bg;
        if (curBgFile) curBgFile.value = '';
        if (bg) setBgPreview(bg, 'Current background');
        else clearBgPreview(true);

        if (curActive) curActive.checked = (rowStatus(item) === 'published');

        const normalized = normalizeItems(
          item?.stats_items_json ??
          item?.stats_items ??
          item?.items_json ??
          item?.items ??
          item?.metadata?.stats_items_json ??
          []
        );
        curItemsEditor.setItems(normalized);

        const meta = item?.metadata ?? null;
        if (curMeta) curMeta.value = meta == null ? '' : JSON.stringify(meta, null, 2);

        if (curIdentity) curIdentity.textContent = item?.uuid ? `uuid: ${item.uuid}` : '—';
        if (curUpdated) curUpdated.textContent = item?.updated_at ? `Updated: ${item.updated_at}` : 'Updated: —';
      }catch(e){
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    async function loadList(kind){
      const tbody = (kind === 'trash') ? tbodyTrash : tbodyVersions;
      const cols = (kind === 'trash') ? 6 : 7;
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
      const sectionTitle = (curSectionTitle?.value || '').trim();

      // ✅ always keep slug in sync (required)
      const computed = slugify(sectionTitle);
      if (curSlug) curSlug.value = computed || (curSlug.value || '').trim();

      const slug = (curSlug?.value || '').trim();
      if (!slug) return { ok:false, error:'Slug is required (auto-generated from Section Title).' };

      // ✅ REQUIRED in DB/API
      let bgUrl = (curBgUrl?.value || '').trim();
      const bgFile = curBgFile?.files?.[0] || null;

      // if file chosen but url empty, auto-fill (already done on change, but keep safe)
      if (!bgUrl && bgFile){
        const safeName = (bgFile.name || 'background.jpg').replace(/\s+/g,'-');
        bgUrl = `depy_uploads/stats/${safeName}`;
        if (curBgUrl) curBgUrl.value = bgUrl;
      }
      if (!bgUrl) return { ok:false, error:'Background Image URL is required (background_image_url).' };

      const sectionSubtitle = (curSectionSubtitle?.value || '').trim();

      const items = curItemsEditor.readFromRows();
      const metaParsed = safeJsonParse(curMeta?.value || '', 'Metadata JSON');
      if (!metaParsed.ok) return { ok:false, error: metaParsed.error };

      // merge title/subtitle into metadata (since table has no columns for them)
      const meta = (metaParsed.val && typeof metaParsed.val === 'object' && !Array.isArray(metaParsed.val))
        ? { ...metaParsed.val }
        : {};
      if (sectionTitle) meta.section_title = sectionTitle;
      if (sectionSubtitle) meta.section_subtitle = sectionSubtitle;

      return {
        ok:true,
        payload: {
          slug: slug,
          background_image_url: bgUrl,
          status: statusFromSwitch(!!curActive?.checked),
          stats_items_json: items,
          metadata: Object.keys(meta).length ? meta : null
        },
        files: { background_image_file: bgFile }
      };
    }

    async function saveCurrent(mode){
      if (!canWrite) return;

      const rd = readCurrentPayload();
      if (!rd.ok){ err(rd.error || 'Invalid values'); return; }

      const uuid = (curUuid?.value || '').trim();
      const isCreate = (mode === 'new') || (!uuid);

      const endpoint = isCreate ? API.base : `${API.base}/${encodeURIComponent(uuid)}`;

      const btnMain = $('stBtnSaveCurrent');
      const btnNew  = $('stBtnSaveAsNew');

      setBtnLoading(btnMain, mode !== 'new');
      setBtnLoading(btnNew, mode === 'new');
      showLoading(true);

      try{
        const hasFile = !!rd.files?.background_image_file;

        let res, js;

        if (hasFile){
          // ✅ multipart with file upload
          const fd = new FormData();
          fd.append('slug', rd.payload.slug);
          fd.append('background_image_url', rd.payload.background_image_url);
          fd.append('status', rd.payload.status);
          fd.append('stats_items_json', JSON.stringify(rd.payload.stats_items_json || []));
          if (rd.payload.metadata != null) fd.append('metadata', JSON.stringify(rd.payload.metadata));
          fd.append('background_image_file', rd.files.background_image_file);

          if (!isCreate){
            fd.append('_method', 'PATCH'); // method spoof
          }

          res = await fetchWithTimeout(endpoint, {
            method: 'POST',
            headers: authHeaders(false),
            body: fd
          }, 20000);

          js = await res.json().catch(()=> ({}));
        } else {
          // ✅ JSON (no file)
          const method = isCreate ? 'POST' : 'PATCH';
          res = await fetchWithTimeout(endpoint, {
            method,
            headers: authHeaders(true),
            body: JSON.stringify(rd.payload)
          }, 20000);
          js = await res.json().catch(()=> ({}));
        }

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

    $('stBtnSaveCurrent')?.addEventListener('click', () => saveCurrent('upsert'));
    $('stBtnSaveAsNew')?.addEventListener('click', async () => {
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

    $('stBtnReload')?.addEventListener('click', async () => {
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

    function fillModalFromRow(row, viewOnly){
      const r = row || {};
      itemUuid.value = r.uuid || '';
      itemId.value = r.id || '';

      if (itemIdentity) itemIdentity.textContent = r.uuid ? `uuid: ${r.uuid}` : '—';
      if (itemUpdated) itemUpdated.textContent = r.updated_at ? `Updated: ${r.updated_at}` : 'Updated: —';
      if (itemBy) itemBy.textContent = (r.created_by_name || r.created_by_email || '—');

      const title = getRowTitle(r) === '—' ? '' : (getRowTitle(r) || '');
      mSectionTitle.value = title;

      const slug = getRowSlug(r) || slugify(title);
      if (mSlug) mSlug.value = slug;

      mSectionSubtitle.value = getRowSubtitle(r) || '';

      // bg
      const bg = getRowBg(r);
      if (mBgUrl) mBgUrl.value = bg;
      if (mBgFile) mBgFile.value = '';
      if (bg) setBgPreviewModal(bg, 'Current background');
      else clearBgPreviewModal(true);

      mActive.checked = (rowStatus(r) === 'published');

      const items = normalizeItems(
        r?.stats_items_json ??
        r?.stats_items ??
        r?.items_json ??
        r?.items ??
        r?.metadata?.stats_items_json ??
        []
      );
      modalItemsEditor.setItems(items);

      const meta = r.metadata ?? null;
      if (mMeta) mMeta.value = meta == null ? '' : JSON.stringify(meta, null, 2);

      const disable = !!viewOnly || !canWrite;
      itemForm.querySelectorAll('input,textarea').forEach(el => {
        if (el.id === 'stItemUuid' || el.id === 'stItemId') return;
        if (el.id === 'st_m_slug') { el.readOnly = true; return; }
        // file should be disabled in view
        if (el.type === 'file'){ el.disabled = disable; return; }
        el.disabled = disable;
      });
      if (saveBtn) saveBtn.style.display = (!disable && !viewOnly) ? '' : 'none';

      itemForm.dataset.mode = viewOnly ? 'view' : 'edit';
    }

    // ---------- Dropdown toggle fix ----------
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
    // ---------------------------------------

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

      const title = (mSectionTitle.value || '').trim();

      // ✅ ensure required slug exists (auto)
      if (mSlug) mSlug.value = slugify(title) || (mSlug.value || '').trim();
      const slug = (mSlug?.value || '').trim();
      if (!slug){ err('Slug is required (auto-generated from Section Title).'); return; }

      // ✅ required bg url
      let bgUrl = (mBgUrl?.value || '').trim();
      const bgFile = mBgFile?.files?.[0] || null;
      if (!bgUrl && bgFile){
        const safeName = (bgFile.name || 'background.jpg').replace(/\s+/g,'-');
        bgUrl = `depy_uploads/stats/${safeName}`;
        if (mBgUrl) mBgUrl.value = bgUrl;
      }
      if (!bgUrl){ err('Background Image URL is required (background_image_url).'); return; }

      const items = modalItemsEditor.readFromRows();

      const metaParsed = safeJsonParse(mMeta?.value || '', 'Metadata JSON');
      if (!metaParsed.ok){ err(metaParsed.error); return; }

      // merge title/subtitle into metadata
      const meta = (metaParsed.val && typeof metaParsed.val === 'object' && !Array.isArray(metaParsed.val))
        ? { ...metaParsed.val }
        : {};
      if (title) meta.section_title = title;
      const sub = (mSectionSubtitle.value || '').trim();
      if (sub) meta.section_subtitle = sub;

      const payload = {
        slug: slug,
        background_image_url: bgUrl,
        status: statusFromSwitch(!!mActive.checked),
        stats_items_json: items,
        metadata: Object.keys(meta).length ? meta : null
      };

      setBtnLoading(saveBtn, true);
      showLoading(true);

      try{
        let res, js;
        if (bgFile){
          const fd = new FormData();
          fd.append('_method', 'PATCH');
          fd.append('slug', payload.slug);
          fd.append('background_image_url', payload.background_image_url);
          fd.append('status', payload.status);
          fd.append('stats_items_json', JSON.stringify(payload.stats_items_json || []));
          if (payload.metadata != null) fd.append('metadata', JSON.stringify(payload.metadata));
          fd.append('background_image_file', bgFile);

          res = await fetchWithTimeout(`${API.base}/${encodeURIComponent(uuid)}`, {
            method: 'POST',
            headers: authHeaders(false),
            body: fd
          }, 20000);

          js = await res.json().catch(()=> ({}));
        } else {
          res = await fetchWithTimeout(`${API.base}/${encodeURIComponent(uuid)}`, {
            method: 'PATCH',
            headers: authHeaders(true),
            body: JSON.stringify(payload)
          }, 20000);

          js = await res.json().catch(()=> ({}));
        }

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

    itemModalEl?.addEventListener('hidden.bs.modal', () => {
      clearBgPreviewModal(true);
    });

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
