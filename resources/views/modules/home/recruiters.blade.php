{{-- resources/views/modules/recruiters/manageRecruiters.blade.php --}}
@section('title','Recruiters')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =============================
  Recruiters Module (namespaced)
============================= */
.rec-wrap{padding:14px 4px}
.rec-panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2)}
.rec-toolbar{padding:12px 12px}

/* Tabs */
.nav.nav-tabs{border-color:var(--line-strong)}
.nav-tabs .nav-link{color:var(--ink)}
.nav-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface)
}
.tab-content,.tab-pane{overflow:visible}

/* Table card + dropdown safety */
.rec-table.card{
  position:relative;
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.rec-table .card-body{overflow:visible}
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
.small{font-size:12.5px}

/* Responsive horizontal scroll */
.table-responsive{
  display:block;
  width:100%;
  max-width:100%;
  overflow-x:auto !important;
  overflow-y:visible !important;
  -webkit-overflow-scrolling:touch;
  position:relative;
}
.table-responsive > .table{width:max-content;min-width:1120px}
.table-responsive th,.table-responsive td{white-space:nowrap}

/* Slug column */
th.col-slug, td.col-slug{width:190px;max-width:190px}
td.col-slug{overflow:hidden}
td.col-slug code{
  display:inline-block;
  max-width:180px;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
  vertical-align:bottom;
}

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

/* ✅ Dropdown fix (match HeroCarousel behavior) */
.rec-table .dropdown{position:relative}
.dropdown .rec-dd-toggle{border-radius:10px}
.dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:240px;
  z-index:99999; /* ✅ important */
}
.dropdown-menu.show{display:block !important}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color) !important}

/* Loading overlay */
.rec-loading{
  position:fixed;inset:0;
  background:rgba(0,0,0,.45);
  display:none;
  align-items:center;
  justify-content:center;
  z-index:9999;
  backdrop-filter:blur(2px);
}
.rec-loading .box{
  background:var(--surface);
  padding:18px 20px;
  border-radius:14px;
  display:flex;flex-direction:column;align-items:center;gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,.3);
}
.rec-loading .spin{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,0.3);
  border-top:4px solid var(--primary-color);
  animation:recSpin 1s linear infinite;
}
@keyframes recSpin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

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
  animation:recSpin 1s linear infinite;
}

/* Logo chip */
.logo-chip{
  width:42px;height:42px;border-radius:12px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 88%, var(--bg-body));
  overflow:hidden;
  display:flex;align-items:center;justify-content:center;
}
.logo-chip img{width:100%;height:100%;object-fit:cover;display:block}
.logo-chip .ph{font-weight:800;color:var(--muted-color);font-size:14px}

/* Mini RTE (description) */
.rc-rte{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:var(--surface);
}
.rc-rte .bar{
  display:flex;align-items:center;gap:6px;flex-wrap:wrap;
  padding:8px;border-bottom:1px solid var(--line-strong);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.rc-rte .btnx{
  border:1px solid var(--line-soft);
  background:transparent;
  color:var(--ink);
  padding:7px 9px;
  border-radius:10px;
  line-height:1;
  cursor:pointer;
  display:inline-flex;align-items:center;justify-content:center;
}
.rc-rte .btnx:hover{background:var(--page-hover)}
.rc-rte .btnx.active{
  background:color-mix(in oklab, var(--primary-color) 14%, transparent);
  border-color:color-mix(in oklab, var(--primary-color) 35%, var(--line-soft));
}
.rc-rte .mode{
  margin-left:auto;display:flex;border:1px solid var(--line-soft);border-radius:12px;overflow:hidden
}
.rc-rte .mode button{
  border:0;background:transparent;color:var(--ink);
  padding:7px 12px;font-size:12px;cursor:pointer
}
.rc-rte .mode button.active{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  font-weight:800;
}
.rc-rte .area{position:relative}
.rc-rte .editor{min-height:220px;padding:12px;outline:none}
.rc-rte .editor:empty:before{content:attr(data-placeholder);color:var(--muted-color)}
.rc-rte textarea{
  display:none;width:100%;min-height:220px;padding:12px;border:0;outline:none;resize:vertical;
  background:transparent;color:var(--ink);
  font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  font-size:12.5px;line-height:1.45;
}
.rc-rte.code .editor{display:none}
.rc-rte.code textarea{display:block}

/* Logo preview box */
.logo-preview{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:color-mix(in oklab, var(--surface) 88%, var(--bg-body));
}
.logo-preview .top{
  display:flex;align-items:center;justify-content:space-between;gap:10px;
  padding:10px 12px;border-bottom:1px solid var(--line-soft)
}
.logo-preview .body{padding:12px}
.logo-preview img{
  width:100%;
  max-height:220px;
  object-fit:contain;
  border-radius:12px;
  border:1px solid var(--line-soft);
  background:#fff;
}
.logo-preview .meta{font-size:12.5px;color:var(--muted-color);margin-top:10px}

/* ✅ Job Roles builder (like Success Stories -> Social Links) */
.rec-roles{
  border:1px solid var(--line-strong);
  border-radius:14px;
  padding:12px;
  background:var(--surface);
}
.rec-role-row{
  display:grid;
  grid-template-columns: 1.6fr 1fr auto;
  gap:10px;
  align-items:center;
  margin-bottom:10px;
}
.rec-role-row:last-child{margin-bottom:0}
@media (max-width: 992px){
  .rec-role-row{grid-template-columns:1fr 1fr}
  .rec-role-row .span2{grid-column: span 2}
}

/* Mobile toolbar */
@media (max-width: 768px){
  .rec-toolbar .d-flex{flex-direction:column;gap:12px !important}
  .rec-toolbar .position-relative{min-width:100% !important}
  .rec-toolbar .btn{flex:1;min-width:120px}
}
</style>
@endpush

@section('content')
<div class="rec-wrap">

  {{-- Loading Overlay --}}
  <div id="recLoading" class="rec-loading" aria-live="polite" aria-busy="true">
    <div class="box">
      <div class="spin"></div>
      <div class="small">Loading…</div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#rec-tab-active" role="tab" aria-selected="true">
        <i class="fa-solid fa-building me-2"></i>Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#rec-tab-inactive" role="tab" aria-selected="false">
        <i class="fa-solid fa-circle-pause me-2"></i>Inactive
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#rec-tab-trash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Trash
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- ACTIVE TAB --}}
    <div class="tab-pane fade show active" id="rec-tab-active" role="tabpanel">

      {{-- Toolbar --}}
      <div class="rec-panel rec-toolbar mb-3">
        <div class="d-flex align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per Page</label>
            <select id="recPerPage" class="form-select" style="width:96px;">
              <option>10</option>
              <option selected>20</option>
              <option>50</option>
              <option>100</option>
            </select>
          </div>

          <div class="position-relative" style="min-width:280px;">
            <input id="recSearch" type="search" class="form-control ps-5" placeholder="Search recruiters by title/slug…">
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>

          <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#recFilterModal">
            <i class="fa fa-sliders me-1"></i>Filter
          </button>

          <button id="recReset" class="btn btn-light">
            <i class="fa fa-rotate-left me-1"></i>Reset
          </button>

          <div class="ms-auto d-flex gap-2 align-items-center" id="recWriteControls" style="display:none;">
            <button type="button" class="btn btn-primary" id="recAddBtn">
              <i class="fa fa-plus me-1"></i> Add Recruiter
            </button>
          </div>
        </div>
      </div>

      {{-- Table --}}
      <div class="card rec-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:64px;">Logo</th>
                  <th>Company</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:260px;">Department</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:120px;">Featured</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:120px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="recTbodyActive">
                <tr><td colspan="9" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="recEmptyActive" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa-solid fa-building mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No active recruiters found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="recInfoActive">—</div>
            <nav><ul id="recPagerActive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- INACTIVE TAB --}}
    <div class="tab-pane fade" id="rec-tab-inactive" role="tabpanel">
      <div class="card rec-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:64px;">Logo</th>
                  <th>Company</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:260px;">Department</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:120px;">Featured</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:120px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="recTbodyInactive">
                <tr><td colspan="9" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="recEmptyInactive" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa-solid fa-circle-pause mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No inactive recruiters found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="recInfoInactive">—</div>
            <nav><ul id="recPagerInactive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- TRASH TAB --}}
    <div class="tab-pane fade" id="rec-tab-trash" role="tabpanel">
      <div class="card rec-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Company</th>
                  <th class="col-slug">Slug</th>
                  <th style="width:260px;">Department</th>
                  <th style="width:160px;">Deleted</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:120px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="recTbodyTrash">
                <tr><td colspan="6" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="recEmptyTrash" class="p-4 text-center text-muted" style="display:none;">
            <i class="fa-solid fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>Trash is empty.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="recInfoTrash">—</div>
            <nav><ul id="recPagerTrash" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="recFilterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-sliders me-2"></i>Recruiter Filters</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Department</label>
            <select id="recFDepartment" class="form-select">
              <option value="">All Departments</option>
              {{-- options filled via JS --}}
            </select>
            <div class="form-text">Choose a department (optional).</div>
          </div>

          <div class="col-12">
            <label class="form-label">Featured</label>
            <select id="recFFeatured" class="form-select">
              <option value="">Any</option>
              <option value="1">Featured only</option>
              <option value="0">Not featured</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Sort</label>
            <select id="recFSort" class="form-select">
              <option value="sort_order">Sort Order ↑</option>
              <option value="-sort_order">Sort Order ↓</option>
              <option value="-updated_at">Recently Updated</option>
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="title">Title A–Z</option>
              <option value="-title">Title Z–A</option>
            </select>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="recApplyFilters" class="btn btn-primary">
          <i class="fa fa-check me-1"></i>Apply
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Add/Edit/View Modal --}}
<div class="modal fade" id="recItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form class="modal-content" id="recForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="recModalTitle">Add Recruiter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="recUuid">
        <input type="hidden" id="recId">

        <div class="row g-3">
          <div class="col-lg-6">
            <div class="row g-3">

              <div class="col-12">
                <label class="form-label">Company / Recruiter Name <span class="text-danger">*</span></label>
                <input class="form-control" id="recTitle" required maxlength="255" placeholder="e.g., Infosys">
              </div>

              <div class="col-md-8">
                <label class="form-label">Slug (optional)</label>
                <input class="form-control" id="recSlug" maxlength="160" placeholder="infosys">
                <div class="form-text">Auto-generated from title unless you edit it.</div>
              </div>

              <div class="col-md-4">
                <label class="form-label">Sort Order</label>
                <input type="number" class="form-control" id="recSortOrder" min="0" max="1000000" value="0">
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select" id="recStatus">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Featured on Home</label>
                <select class="form-select" id="recFeatured">
                  <option value="0">No</option>
                  <option value="1">Yes</option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Department</label>
                <select class="form-select" id="recDepartment">
                  <option value="">— Select Department —</option>
                  {{-- options filled via JS --}}
                </select>
                <div class="form-text">
                  Shows <b>department name</b> but submits the <b>department id</b>.
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Logo Upload (optional)</label>
                <input type="file" class="form-control" id="recLogoFile" accept="image/*,.svg">
              </div>

              <div class="col-md-6">
                <label class="form-label">Logo URL/Path (optional)</label>
                <input class="form-control" id="recLogoUrl" maxlength="255" placeholder="depy_uploads/... OR https://...">
              </div>

              <div class="col-12 d-flex align-items-center gap-2">
                <input class="form-check-input" type="checkbox" id="recLogoRemove">
                <label class="form-check-label" for="recLogoRemove">
                  Remove existing logo
                </label>
              </div>

              {{-- ✅ Job Roles Builder (Role + CTC) --}}
              <div class="col-12">
                <label class="form-label">Job Roles (optional)</label>
                <div class="rec-roles">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="small text-muted">Add roles like: <b>Software Engineer</b> • <b>9 LPA</b></div>
                    <button type="button" class="btn btn-light btn-sm" id="recAddRoleBtn">
                      <i class="fa fa-plus me-1"></i>Add Role
                    </button>
                  </div>
                  <div id="recRolesWrap"></div>
                </div>

                {{-- hidden JSON holder (kept for internal use/debug) --}}
                <input type="hidden" id="recJobRolesJson" value="">
                <div class="form-text">
                  Saved as JSON array in backend:
                  <code>[{"role":"...","ctc":"..."}]</code>
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">Metadata JSON (optional)</label>
                <textarea class="form-control" id="recMeta" rows="4" placeholder='{"campus":"MSIT","year":2025}'></textarea>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <label class="form-label">Description (HTML allowed)</label>

            <div class="rc-rte" id="recRte">
              <div class="bar">
                <button type="button" class="btnx" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
                <button type="button" class="btnx" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
                <button type="button" class="btnx" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>
                <span style="width:1px;height:24px;background:var(--line-soft);margin:0 4px"></span>
                <button type="button" class="btnx" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
                <button type="button" class="btnx" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>
                <span style="width:1px;height:24px;background:var(--line-soft);margin:0 4px"></span>
                <button type="button" class="btnx" data-cmd="createLink" title="Link"><i class="fa fa-link"></i></button>
                <button type="button" class="btnx" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>

                <div class="mode">
                  <button type="button" class="active" data-mode="text">Text</button>
                  <button type="button" data-mode="code">Code</button>
                </div>
              </div>

              <div class="area">
                <div id="recDescEditor" class="editor" contenteditable="true" data-placeholder="Write recruiter description…"></div>
                <textarea id="recDescCode" spellcheck="false" placeholder="HTML code…"></textarea>
              </div>
            </div>

            <input type="hidden" id="recDescription">

            <div class="logo-preview mt-3">
              <div class="top">
                <div class="fw-semibold"><i class="fa fa-image me-2"></i>Logo Preview</div>
                <button type="button" class="btn btn-light btn-sm" id="recOpenLogo" style="display:none;">
                  <i class="fa fa-up-right-from-square me-1"></i>Open
                </button>
              </div>
              <div class="body">
                <img id="recLogoPreview" src="" alt="Logo preview" style="display:none;">
                <div id="recLogoEmpty" class="text-muted small" style="padding:12px;border:1px dashed var(--line-soft);border-radius:12px;">
                  No logo selected.
                </div>
                <div class="meta" id="recLogoMeta" style="display:none;">—</div>
              </div>
            </div>

            <div class="form-text mt-2">
              Tip: You can upload a logo OR set a logo URL/path. Upload will override the URL.
            </div>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="recSaveBtn">
          <i class="fa fa-floppy-disk me-1"></i> Save
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="recToastOk" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="recToastOkText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="recToastErr" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="recToastErrText">Something went wrong</div>
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
  if (window.__RECRUITERS_MODULE_INIT__) return;
  window.__RECRUITERS_MODULE_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=300) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  function slugify(s){
    return (s || '')
      .toString()
      .normalize('NFKD').replace(/[\u0300-\u036f]/g,'')
      .trim().toLowerCase()
      .replace(/['"`]/g,'')
      .replace(/[^a-z0-9]+/g,'-')
      .replace(/-+/g,'-')
      .replace(/^-|-$/g,'');
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

  async function fetchWithTimeout(url, opts={}, ms=15000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try{
      return await fetch(url, { ...opts, signal: ctrl.signal });
    } finally {
      clearTimeout(t);
    }
  }

  function asJsonString(val){
    const v = (val || '').toString().trim();
    if (!v) return '';
    try{
      const obj = JSON.parse(v);
      return JSON.stringify(obj);
    }catch(_){
      return v;
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const loadingEl = $('recLoading');
    const showLoading = (v) => { if (loadingEl) loadingEl.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('recToastOk');
    const toastErrEl = $('recToastErr');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { const el=$('recToastOkText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('recToastErrText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = () => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    });

    /* ========= Permissions ========= */
    const ACTOR = { role: '' };
    let canCreate=false, canEdit=false, canDelete=false;

    function computePermissions(){
      const r = (ACTOR.role || '').toLowerCase();
      const createDeleteRoles = ['admin','super_admin','director','principal'];
      const writeRoles = ['admin','super_admin','director','principal','hod','faculty','technical_assistant','it_person'];

      canCreate = createDeleteRoles.includes(r);
      canDelete = createDeleteRoles.includes(r);
      canEdit   = writeRoles.includes(r);

      const wc = $('recWriteControls');
      if (wc) wc.style.display = canCreate ? 'flex' : 'none';
    }

    async function fetchMe(){
      try{
        const res = await fetchWithTimeout('/api/users/me', { headers: authHeaders() }, 8000);
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

    /* ========= Elements ========= */
    const perPageSel = $('recPerPage');
    const searchInput = $('recSearch');
    const resetBtn = $('recReset');

    const tbodyA = $('recTbodyActive');
    const tbodyI = $('recTbodyInactive');
    const tbodyT = $('recTbodyTrash');

    const emptyA = $('recEmptyActive');
    const emptyI = $('recEmptyInactive');
    const emptyT = $('recEmptyTrash');

    const pagerA = $('recPagerActive');
    const pagerI = $('recPagerInactive');
    const pagerT = $('recPagerTrash');

    const infoA = $('recInfoActive');
    const infoI = $('recInfoInactive');
    const infoT = $('recInfoTrash');

    const fDept = $('recFDepartment');
    const fFeatured = $('recFFeatured');
    const fSort = $('recFSort');
    const applyFiltersBtn = $('recApplyFilters');

    const addBtn = $('recAddBtn');

    // item modal
    const itemModalEl = $('recItemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;
    const modalTitle = $('recModalTitle');
    const form = $('recForm');
    const saveBtn = $('recSaveBtn');

    const recUuid = $('recUuid');
    const recId = $('recId');

    const titleEl = $('recTitle');
    const slugEl = $('recSlug');
    const sortOrderEl = $('recSortOrder');
    const statusEl = $('recStatus');
    const featuredEl = $('recFeatured');
    const deptSelEl = $('recDepartment');

    const logoFileEl = $('recLogoFile');
    const logoUrlEl = $('recLogoUrl');
    const logoRemoveEl = $('recLogoRemove');

    // ✅ Job Roles builder elements
    const rolesWrap = $('recRolesWrap');
    const addRoleBtn = $('recAddRoleBtn');
    const jobRolesJsonEl = $('recJobRolesJson');

    const metaEl = $('recMeta');

    // mini RTE
    const rteWrap = $('recRte');
    const rteEditor = $('recDescEditor');
    const rteCode = $('recDescCode');
    const descHidden = $('recDescription');

    // logo preview
    const logoPreview = $('recLogoPreview');
    const logoEmpty = $('recLogoEmpty');
    const logoMeta = $('recLogoMeta');
    const openLogoBtn = $('recOpenLogo');
    let logoObjectUrl = null;

    function setBtnLoading(btn, loading){
      if (!btn) return;
      btn.disabled = !!loading;
      btn.classList.toggle('btn-loading', !!loading);
    }

    function setLogoPreview(url, metaText=''){
      const u = normalizeUrl(url);
      if (!u){
        if (logoPreview){ logoPreview.style.display='none'; logoPreview.removeAttribute('src'); }
        if (logoEmpty) logoEmpty.style.display='';
        if (logoMeta){ logoMeta.style.display='none'; logoMeta.textContent='—'; }
        if (openLogoBtn){ openLogoBtn.style.display='none'; openLogoBtn.onclick=null; }
        return;
      }
      if (logoPreview){ logoPreview.style.display=''; logoPreview.src=u; }
      if (logoEmpty) logoEmpty.style.display='none';
      if (logoMeta){ logoMeta.style.display = metaText ? '' : 'none'; logoMeta.textContent = metaText || ''; }
      if (openLogoBtn){
        openLogoBtn.style.display = '';
        openLogoBtn.onclick = () => window.open(u, '_blank', 'noopener');
      }
    }

    function clearLogoObjectUrl(){
      if (logoObjectUrl){
        try{ URL.revokeObjectURL(logoObjectUrl); }catch(_){}
        logoObjectUrl = null;
      }
    }

    /* ========= Departments (id -> name map) ========= */
    const depts = { list: [], map: {}, loaded: false };

    function normDeptName(d){
      return d?.name || d?.title || d?.department_name || d?.department_title || d?.label || d?.dept_name || '—';
    }
    function deptIdOf(d){ return d?.id ?? d?.department_id ?? null; }

    function applyDeptOptions(){
      const opts = depts.list
        .filter(d => deptIdOf(d) != null)
        .map(d => ({ id: String(deptIdOf(d)), name: String(normDeptName(d) || '—') }))
        .sort((a,b) => a.name.localeCompare(b.name));

      depts.map = {};
      opts.forEach(o => { depts.map[o.id] = o.name; });

      if (fDept){
        const curr = fDept.value || '';
        fDept.innerHTML = `<option value="">All Departments</option>` + opts.map(o =>
          `<option value="${esc(o.id)}">${esc(o.name)}</option>`
        ).join('');
        fDept.value = curr;
      }

      if (deptSelEl){
        const curr = deptSelEl.value || '';
        deptSelEl.innerHTML = `<option value="">— Select Department —</option>` + opts.map(o =>
          `<option value="${esc(o.id)}">${esc(o.name)}</option>`
        ).join('');
        deptSelEl.value = curr;
      }
    }

    async function loadDepartments(){
      const candidates = [
        '/api/departments?per_page=500',
        '/api/departments',
        '/api/department',
        '/api/departments/all',
        '/api/departments/list'
      ];

      for (const url of candidates){
        try{
          const res = await fetchWithTimeout(url, { headers: authHeaders() }, 12000);
          if (!res.ok) continue;

          const js = await res.json().catch(()=> ({}));
          const arr =
            (Array.isArray(js?.data) && js.data) ||
            (Array.isArray(js?.departments) && js.departments) ||
            (Array.isArray(js?.items) && js.items) ||
            (Array.isArray(js) && js) ||
            [];

          if (!Array.isArray(arr) || !arr.length) continue;

          depts.list = arr;
          depts.loaded = true;
          applyDeptOptions();
          return;
        }catch(_){}
      }

      depts.list = [];
      depts.map = {};
      depts.loaded = false;

      if (fDept) fDept.innerHTML = `<option value="">All Departments</option>`;
      if (deptSelEl) deptSelEl.innerHTML = `<option value="">— Select Department —</option>`;
    }

    function getDeptNameFromRow(row){
      const embedded = row?.department || row?.dept || null;
      const embeddedName = embedded ? normDeptName(embedded) : '';
      const directName = row?.department_name || row?.department_title || row?.dept_name || row?.dept_title || '';
      const id =
        row?.department_id ??
        row?.dept_id ??
        embedded?.id ??
        embedded?.department_id ??
        null;

      if (directName) return String(directName);
      if (embeddedName && embeddedName !== '—') return String(embeddedName);
      if (id != null && depts.map[String(id)]) return depts.map[String(id)];
      return '—';
    }

    /* ========= State ========= */
    const state = {
      filters: { q:'', departmentId:'', featured:'', sort:'sort_order' },
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      tabs: {
        active:   { page:1, lastPage:1, items:[] },
        inactive: { page:1, lastPage:1, items:[] },
        trash:    { page:1, lastPage:1, items:[] }
      }
    };

    const getTabKey = () => {
      const a = document.querySelector('.nav-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#rec-tab-active';
      if (href === '#rec-tab-inactive') return 'inactive';
      if (href === '#rec-tab-trash') return 'trash';
      return 'active';
    };

    function buildUrl(tabKey){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state.tabs[tabKey].page));

      const q = (state.filters.q || '').trim();
      if (q) params.set('q', q);

      const deptId = (state.filters.departmentId || '').trim();
      if (deptId) params.set('department', deptId);

      if (state.filters.featured !== '') params.set('featured', state.filters.featured);

      const s = state.filters.sort || 'sort_order';
      params.set('sort', s.startsWith('-') ? s.slice(1) : s);
      params.set('direction', s.startsWith('-') ? 'desc' : 'asc');

      if (tabKey === 'active') params.set('status', 'active');
      if (tabKey === 'inactive') params.set('status', 'inactive');
      if (tabKey === 'trash') params.set('only_trashed', '1');

      return `/api/recruiters?${params.toString()}`;
    }

    function badgeStatus(status){
      const s = (status || '').toString().toLowerCase();
      if (s === 'active') return `<span class="badge badge-soft-success">Active</span>`;
      if (s === 'inactive') return `<span class="badge badge-soft-muted">Inactive</span>`;
      return `<span class="badge badge-soft-muted">${esc(s || '—')}</span>`;
    }

    function badgeFeatured(v){
      return v ? `<span class="badge badge-soft-primary">Yes</span>` : `<span class="badge badge-soft-muted">No</span>`;
    }

    function logoCell(row){
      const url = row?.logo_url_full || row?.logo_url || row?.logo || '';
      if (url){
        return `<div class="logo-chip"><img src="${esc(normalizeUrl(url))}" alt="logo"></div>`;
      }
      const t = (row?.title || 'R').toString().trim();
      const letter = t ? t[0].toUpperCase() : 'R';
      return `<div class="logo-chip"><span class="ph">${esc(letter)}</span></div>`;
    }

    function setEmpty(tabKey, show){
      const el = tabKey==='active' ? emptyA : (tabKey==='inactive' ? emptyI : emptyT);
      if (el) el.style.display = show ? '' : 'none';
    }

    function renderPager(tabKey){
      const pagerEl = tabKey === 'active' ? pagerA : (tabKey === 'inactive' ? pagerI : pagerT);
      if (!pagerEl) return;

      const st = state.tabs[tabKey];
      const page = st.page;
      const totalPages = st.lastPage || 1;

      const item = (p, label, dis=false, act=false) => {
        if (dis) return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
        if (act) return `<li class="page-item active"><span class="page-link">${label}</span></li>`;
        return `<li class="page-item"><a class="page-link" href="#" data-page="${p}" data-tab="${tabKey}">${label}</a></li>`;
      };

      let html = '';
      html += item(Math.max(1, page-1), 'Previous', page<=1);
      const start = Math.max(1, page-2), end = Math.min(totalPages, page+2);
      for (let p=start; p<=end; p++) html += item(p, p, false, p===page);
      html += item(Math.min(totalPages, page+1), 'Next', page>=totalPages);

      pagerEl.innerHTML = html;
    }

    /* ✅ dropdown markup uses .rec-dd-toggle (no data-bs-toggle) */
    function rowActions(tabKey){
      if (tabKey === 'trash'){
        return `
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>
            <li><hr class="dropdown-divider"></li>
            <li><button type="button" class="dropdown-item" data-action="restore"><i class="fa fa-rotate-left"></i> Restore</button></li>
            ${canDelete ? `<li><button type="button" class="dropdown-item text-danger" data-action="force"><i class="fa fa-skull-crossbones"></i> Delete Permanently</button></li>` : ``}
          </ul>`;
      }

      return `
        <ul class="dropdown-menu dropdown-menu-end">
          <li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>
          ${canEdit ? `<li><button type="button" class="dropdown-item" data-action="edit"><i class="fa fa-pen-to-square"></i> Edit</button></li>` : ``}
          <li><button type="button" class="dropdown-item" data-action="toggleFeatured"><i class="fa fa-star"></i> Toggle Featured</button></li>
          ${canDelete ? `<li><hr class="dropdown-divider"></li><li><button type="button" class="dropdown-item text-danger" data-action="delete"><i class="fa fa-trash"></i> Delete</button></li>` : ``}
        </ul>`;
    }

    function renderTable(tabKey){
      const tbody = tabKey==='active' ? tbodyA : (tabKey==='inactive' ? tbodyI : tbodyT);
      const rows = state.tabs[tabKey].items || [];
      if (!tbody) return;

      if (!rows.length){
        tbody.innerHTML = '';
        setEmpty(tabKey, true);
        renderPager(tabKey);
        return;
      }
      setEmpty(tabKey, false);

      if (tabKey === 'trash'){
        tbody.innerHTML = rows.map(r => {
          const uuid = r.uuid || '';
          const title = r.title || '—';
          const slug = r.slug || '—';
          const deptName = getDeptNameFromRow(r);
          const deleted = r.deleted_at || '—';
          const sortOrder = (r.sort_order ?? 0);

          return `
            <tr data-uuid="${esc(uuid)}">
              <td class="fw-semibold">${esc(title)}</td>
              <td class="col-slug"><code>${esc(slug)}</code></td>
              <td>${esc(deptName)}</td>
              <td>${esc(deleted)}</td>
              <td>${esc(String(sortOrder))}</td>
              <td class="text-end">
                <div class="dropdown">
                  <button type="button" class="btn btn-light btn-sm rec-dd-toggle" aria-expanded="false" title="Actions">
                    <i class="fa fa-ellipsis-vertical"></i>
                  </button>
                  ${rowActions('trash')}
                </div>
              </td>
            </tr>`;
        }).join('');

        renderPager(tabKey);
        return;
      }

      tbody.innerHTML = rows.map(r => {
        const uuid = r.uuid || '';
        const title = r.title || '—';
        const slug = r.slug || '—';
        const deptName = getDeptNameFromRow(r);
        const status = r.status || '—';
        const featured = !!(r.is_featured_home ?? r.featured ?? 0);
        const sortOrder = (r.sort_order ?? 0);
        const updated = r.updated_at || '—';

        return `
          <tr data-uuid="${esc(uuid)}">
            <td>${logoCell(r)}</td>
            <td class="fw-semibold">${esc(title)}</td>
            <td class="col-slug"><code>${esc(slug)}</code></td>
            <td>${esc(deptName)}</td>
            <td>${badgeStatus(status)}</td>
            <td>${badgeFeatured(featured)}</td>
            <td>${esc(String(sortOrder))}</td>
            <td>${esc(String(updated))}</td>
            <td class="text-end">
              <div class="dropdown">
                <button type="button" class="btn btn-light btn-sm rec-dd-toggle" aria-expanded="false" title="Actions">
                  <i class="fa fa-ellipsis-vertical"></i>
                </button>
                ${rowActions(tabKey)}
              </div>
            </td>
          </tr>`;
      }).join('');

      renderPager(tabKey);
    }

    async function loadTab(tabKey){
      const tbody = tabKey==='active' ? tbodyA : (tabKey==='inactive' ? tbodyI : tbodyT);
      if (tbody){
        const cols = (tabKey==='trash') ? 6 : 9;
        tbody.innerHTML = `<tr><td colspan="${cols}" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>`;
      }

      try{
        const res = await fetchWithTimeout(buildUrl(tabKey), { headers: authHeaders() }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(js?.message || 'Failed to load');

        const items = Array.isArray(js.data) ? js.data : [];
        const p = js.pagination || js.meta || {};

        state.tabs[tabKey].items = items;
        state.tabs[tabKey].lastPage = parseInt(p.last_page || 1, 10) || 1;

        const infoText = (p.total ? `${p.total} result(s)` : '—');
        if (tabKey === 'active' && infoA) infoA.textContent = infoText;
        if (tabKey === 'inactive' && infoI) infoI.textContent = infoText;
        if (tabKey === 'trash' && infoT) infoT.textContent = infoText;

        renderTable(tabKey);
      }catch(e){
        state.tabs[tabKey].items = [];
        state.tabs[tabKey].lastPage = 1;
        renderTable(tabKey);
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    function reloadCurrent(){ loadTab(getTabKey()); }

    /* ========= Pager click ========= */
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a.page-link[data-page]');
      if (!a) return;
      e.preventDefault();
      const tab = a.dataset.tab;
      const p = parseInt(a.dataset.page, 10);
      if (!tab || Number.isNaN(p)) return;
      if (p === state.tabs[tab].page) return;
      state.tabs[tab].page = p;
      loadTab(tab);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    /* ========= Filters ========= */
    searchInput?.addEventListener('input', debounce(() => {
      state.filters.q = (searchInput.value || '').trim();
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    }, 320));

    perPageSel?.addEventListener('change', () => {
      state.perPage = parseInt(perPageSel.value, 10) || 20;
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    $('recFilterModal')?.addEventListener('show.bs.modal', () => {
      if (fDept) fDept.value = state.filters.departmentId || '';
      if (fFeatured) fFeatured.value = (state.filters.featured ?? '');
      if (fSort) fSort.value = state.filters.sort || 'sort_order';
    });

    applyFiltersBtn?.addEventListener('click', () => {
      state.filters.departmentId = (fDept?.value || '').trim();
      state.filters.featured = (fFeatured?.value ?? '');
      state.filters.sort = (fSort?.value || 'sort_order');
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      bootstrap.Modal.getInstance($('recFilterModal'))?.hide();
      reloadCurrent();
    });

    resetBtn?.addEventListener('click', () => {
      state.filters = { q:'', departmentId:'', featured:'', sort:'sort_order' };
      state.perPage = 20;
      if (searchInput) searchInput.value = '';
      if (perPageSel) perPageSel.value = '20';
      if (fDept) fDept.value = '';
      if (fFeatured) fFeatured.value = '';
      if (fSort) fSort.value = 'sort_order';
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    document.querySelector('a[href="#rec-tab-active"]')?.addEventListener('shown.bs.tab', () => loadTab('active'));
    document.querySelector('a[href="#rec-tab-inactive"]')?.addEventListener('shown.bs.tab', () => loadTab('inactive'));
    document.querySelector('a[href="#rec-tab-trash"]')?.addEventListener('shown.bs.tab', () => loadTab('trash'));

    /* ========= Mini RTE ========= */
    const rte = { mode:'text', enabled:true };

    function syncDescToHidden(){
      const html = (rte.mode === 'code') ? (rteCode.value || '') : (rteEditor.innerHTML || '');
      if (descHidden) descHidden.value = (html || '').trim();
    }

    function setRteMode(mode){
      rte.mode = (mode === 'code') ? 'code' : 'text';
      rteWrap?.classList.toggle('code', rte.mode === 'code');

      rteWrap?.querySelectorAll('.mode button').forEach(b => {
        b.classList.toggle('active', b.dataset.mode === rte.mode);
      });

      const disableBar = (rte.mode === 'code') || !rte.enabled;
      rteWrap?.querySelectorAll('.bar .btnx').forEach(b => {
        b.disabled = disableBar;
        b.style.opacity = disableBar ? '0.55' : '';
        b.style.pointerEvents = disableBar ? 'none' : '';
      });

      if (rte.mode === 'code'){
        rteCode.value = rteEditor.innerHTML || '';
        setTimeout(()=>{ try{ rteCode.focus(); }catch(_){ } }, 0);
      }else{
        rteEditor.innerHTML = rteCode.value || '';
        setTimeout(()=>{ try{ rteEditor.focus({preventScroll:true}); }catch(_){ try{ rteEditor.focus(); }catch(__){} } }, 0);
      }
      syncDescToHidden();
    }

    function updateRteActive(){
      if (rte.mode !== 'text') return;
      const set = (cmd, on) => {
        const b = rteWrap?.querySelector(`.btnx[data-cmd="${cmd}"]`);
        if (b) b.classList.toggle('active', !!on);
      };
      try{
        set('bold', document.queryCommandState('bold'));
        set('italic', document.queryCommandState('italic'));
        set('underline', document.queryCommandState('underline'));
      }catch(_){}
    }

    rteWrap?.querySelector('.bar')?.addEventListener('pointerdown', (e) => e.preventDefault());
    rteEditor?.addEventListener('input', () => { syncDescToHidden(); updateRteActive(); });
    rteCode?.addEventListener('input', () => syncDescToHidden());
    ['mouseup','keyup','click'].forEach(ev => rteEditor?.addEventListener(ev, updateRteActive));
    document.addEventListener('selectionchange', () => {
      if (document.activeElement === rteEditor) updateRteActive();
    });

    document.addEventListener('click', (e) => {
      const modeBtn = e.target.closest('#recRte .mode button');
      if (modeBtn){ setRteMode(modeBtn.dataset.mode); return; }

      const btn = e.target.closest('#recRte .btnx');
      if (!btn || rte.mode !== 'text' || !rte.enabled) return;

      try{ rteEditor.focus({preventScroll:true}); }catch(_){ try{ rteEditor.focus(); }catch(__){} }

      const cmd = btn.getAttribute('data-cmd');
      if (cmd === 'createLink'){
        const url = prompt('Enter URL (https://...)');
        if (url) { try{ document.execCommand('createLink', false, url); }catch(_){ } }
      } else {
        try{ document.execCommand(cmd, false, null); }catch(_){ }
      }
      syncDescToHidden();
      updateRteActive();
    });

    function setRteEnabled(on){
      rte.enabled = !!on;
      if (rteEditor) rteEditor.setAttribute('contenteditable', on ? 'true' : 'false');
      if (rteCode) rteCode.disabled = !on;

      const disableBar = (rte.mode === 'code') || !rte.enabled;
      rteWrap?.querySelectorAll('.bar .btnx').forEach(b => {
        b.disabled = disableBar;
        b.style.opacity = disableBar ? '0.55' : '';
        b.style.pointerEvents = disableBar ? 'none' : '';
      });
      rteWrap?.querySelectorAll('.mode button').forEach(b => {
        b.style.pointerEvents = on ? '' : 'none';
        b.style.opacity = on ? '' : '0.7';
      });
    }

    /* ========= ✅ Job Roles Builder (Role + CTC) ========= */
    function roleRowTpl(data={}, viewOnly=false){
      const role = (data?.role ?? data?.title ?? '').toString();
      const ctc  = (data?.ctc  ?? data?.package ?? data?.salary ?? '').toString();

      const dis = viewOnly ? 'disabled' : '';
      const ro  = viewOnly ? 'readonly' : '';

      return `
        <div class="rec-role-row" data-role-row>
          <input class="form-control" placeholder="Role (e.g., Software Engineer)" value="${esc(role)}" ${ro}>
          <input class="form-control" placeholder="CTC (e.g., 9 LPA)" value="${esc(ctc)}" ${ro}>
          <button type="button" class="btn btn-light btn-sm" data-remove-role ${dis}>
            <i class="fa fa-xmark"></i>
          </button>
        </div>
      `;
    }

    function getRolesFromUI(){
      const rows = Array.from(rolesWrap?.querySelectorAll('[data-role-row]') || []);
      const out = [];
      rows.forEach(row => {
        const inputs = row.querySelectorAll('input');
        const role = (inputs[0]?.value || '').trim();
        const ctc  = (inputs[1]?.value || '').trim();
        if (!role && !ctc) return;
        out.push({ role: role || null, ctc: ctc || null });
      });
      return out;
    }

    function setRolesUI(arr, viewOnly=false){
      if (!rolesWrap) return;
      rolesWrap.innerHTML = '';
      const list = Array.isArray(arr) ? arr : [];
      if (!list.length){
        rolesWrap.innerHTML = roleRowTpl({}, viewOnly);
        return;
      }
      rolesWrap.innerHTML = list.map(x => roleRowTpl(x, viewOnly)).join('');
    }

    addRoleBtn?.addEventListener('click', () => {
      if (!rolesWrap) return;
      rolesWrap.insertAdjacentHTML('beforeend', roleRowTpl({}));
    });

    document.addEventListener('click', (e) => {
      const rm = e.target.closest('[data-remove-role]');
      if (!rm) return;
      const row = rm.closest('[data-role-row]');
      if (!row) return;

      const all = rolesWrap?.querySelectorAll('[data-role-row]') || [];
      if (all.length <= 1){
        row.querySelectorAll('input').forEach(i => i.value = '');
        return;
      }
      row.remove();
    });

    /* ========= Modal helpers ========= */
    let saving = false;
    let slugDirty = false;
    let settingSlug = false;

    async function fetchOne(identifier){
      const res = await fetchWithTimeout(`/api/recruiters/${encodeURIComponent(identifier)}`, { headers: authHeaders() }, 12000);
      const js = await res.json().catch(()=> ({}));
      if (!res.ok) throw new Error(js?.message || 'Failed to load recruiter');
      return js?.item || js?.data || js;
    }

    function resetForm(){
      form?.reset();
      recUuid.value = '';
      recId.value = '';
      slugDirty = false;
      settingSlug = false;

      if (rteEditor) rteEditor.innerHTML = '';
      if (rteCode) rteCode.value = '';
      if (descHidden) descHidden.value = '';
      setRteMode('text');
      setRteEnabled(true);

      // ✅ reset roles builder
      setRolesUI([], false);
      if (jobRolesJsonEl) jobRolesJsonEl.value = '';
      if (addRoleBtn) addRoleBtn.style.display = '';

      clearLogoObjectUrl();
      setLogoPreview('', '');

      applyDeptOptions();
      if (deptSelEl) deptSelEl.value = '';

      form?.querySelectorAll('input,select,textarea').forEach(el => {
        if (el.id === 'recUuid' || el.id === 'recId') return;
        if (el.type === 'file') el.disabled = false;
        else if (el.tagName === 'SELECT') el.disabled = false;
        else el.readOnly = false;
      });

      if (saveBtn) saveBtn.style.display = '';
      form.dataset.mode = 'edit';
      form.dataset.intent = 'create';
    }

    function fillForm(row, viewOnly=false){
      recUuid.value = row.uuid || '';
      recId.value = row.id || '';

      titleEl.value = row.title || '';
      slugEl.value = row.slug || '';
      sortOrderEl.value = String(row.sort_order ?? 0);
      statusEl.value = (row.status || 'active');
      featuredEl.value = String((row.is_featured_home ?? row.featured ?? 0) ? 1 : 0);

      const deptId =
        row.department_id ??
        row?.department?.id ??
        row?.dept_id ??
        null;

      applyDeptOptions();
      if (deptSelEl) deptSelEl.value = (deptId != null) ? String(deptId) : '';

      // ✅ job roles -> builder
      let jr = row.job_roles_json ?? row.job_roles ?? null;
      if (typeof jr === 'string'){
        const s = jr.trim();
        if (s){
          try{ jr = JSON.parse(s); }catch(_){ jr = []; }
        } else {
          jr = [];
        }
      }
      const jrArr = Array.isArray(jr) ? jr : [];
      setRolesUI(jrArr, viewOnly);
      if (jobRolesJsonEl){
        try{ jobRolesJsonEl.value = JSON.stringify(jrArr); }catch(_){ jobRolesJsonEl.value = ''; }
      }
      if (addRoleBtn) addRoleBtn.style.display = viewOnly ? 'none' : '';

      if (metaEl){
        const md = row.metadata ?? row.meta ?? null;
        metaEl.value = (md && typeof md === 'object') ? JSON.stringify(md, null, 2) : (typeof md === 'string' ? md : '');
      }

      const desc = row.description || '';
      if (rteEditor) rteEditor.innerHTML = desc;
      if (rteCode) rteCode.value = desc;
      if (descHidden) descHidden.value = desc;

      const logo = row?.logo_url_full || row?.logo_url || row?.logo || '';
      setLogoPreview(logo, logo ? 'Current logo' : '');

      slugDirty = true;

      if (viewOnly){
        form?.querySelectorAll('input,select,textarea').forEach(el => {
          if (el.id === 'recUuid' || el.id === 'recId') return;
          if (el.type === 'file') el.disabled = true;
          else if (el.tagName === 'SELECT') el.disabled = true;
          else el.readOnly = true;
        });
        setRteEnabled(false);
        if (saveBtn) saveBtn.style.display = 'none';
        form.dataset.mode = 'view';
        form.dataset.intent = 'view';
      }else{
        setRteEnabled(true);
        if (saveBtn) saveBtn.style.display = '';
        form.dataset.mode = 'edit';
        form.dataset.intent = 'edit';
      }
    }

    // auto-slug (create only)
    titleEl?.addEventListener('input', debounce(() => {
      if (form?.dataset.mode === 'view') return;
      if (recUuid.value) return;
      if (slugDirty) return;
      const next = slugify(titleEl.value);
      settingSlug = true;
      slugEl.value = next;
      settingSlug = false;
    }, 120));

    slugEl?.addEventListener('input', () => {
      if (recUuid.value) return;
      if (settingSlug) return;
      slugDirty = !!(slugEl.value || '').trim();
    });

    // logo preview interactions
    logoFileEl?.addEventListener('change', () => {
      const f = logoFileEl.files?.[0];
      if (!f){
        clearLogoObjectUrl();
        const u = (logoUrlEl.value || '').trim();
        setLogoPreview(u, u ? 'Logo URL' : '');
        return;
      }
      clearLogoObjectUrl();
      logoObjectUrl = URL.createObjectURL(f);
      setLogoPreview(logoObjectUrl, `${f.name || 'logo'} • ${bytes(f.size)}`);
    });

    logoUrlEl?.addEventListener('input', debounce(() => {
      const u = (logoUrlEl.value || '').trim();
      if (logoFileEl.files?.length) return;
      setLogoPreview(u, u ? 'Logo URL' : '');
    }, 260));

    itemModalEl?.addEventListener('hidden.bs.modal', () => {
      clearLogoObjectUrl();
    });

    addBtn?.addEventListener('click', () => {
      if (!canCreate) return;
      resetForm();
      if (modalTitle) modalTitle.textContent = 'Add Recruiter';
      form.dataset.intent = 'create';
      itemModal && itemModal.show();
    });

    /* =========================================================
       ✅ ACTION DROPDOWN FIX (same pattern as HeroCarousel)
    ========================================================= */
    function closeAllDropdownsExcept(exceptToggle){
      document.querySelectorAll('.rec-dd-toggle').forEach(t => {
        if (t === exceptToggle) return;
        try{
          const inst = bootstrap.Dropdown.getInstance(t);
          inst && inst.hide();
        }catch(_){}
      });
    }

    document.addEventListener('click', () => {
      closeAllDropdownsExcept(null);
    }, { capture: true });

    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('.rec-dd-toggle');
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
    /* ===================== end dropdown fix ===================== */

    // row actions (menu items)
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;

      const tr = btn.closest('tr');
      const uuid = tr?.dataset?.uuid;
      const act = btn.dataset.action;
      if (!uuid) return;

      // close dropdown safely
      const toggle = btn.closest('.dropdown')?.querySelector('.rec-dd-toggle');
      if (toggle) { try { bootstrap.Dropdown.getInstance(toggle)?.hide(); } catch (_) {} }

      if (act === 'view' || act === 'edit'){
        if (act === 'edit' && !canEdit) return;

        resetForm();
        if (modalTitle) modalTitle.textContent = (act === 'view') ? 'View Recruiter' : 'Edit Recruiter';

        showLoading(true);
        try{
          const item = await fetchOne(uuid);
          fillForm(item || {}, act === 'view');
          itemModal && itemModal.show();
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'toggleFeatured'){
        if (!canEdit) return;
        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/recruiters/${encodeURIComponent(uuid)}/toggle-featured`, {
            method: 'PATCH',
            headers: { ...authHeaders() }
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Toggle failed');

          ok('Updated');
          await Promise.all([loadTab('active'), loadTab('inactive')]);
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'delete'){
        if (!canDelete) return;

        const conf = await Swal.fire({
          title: 'Delete this recruiter?',
          text: 'This will move it to Trash.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/recruiters/${encodeURIComponent(uuid)}`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Delete failed');

          ok('Moved to trash');
          await Promise.all([loadTab('active'), loadTab('inactive'), loadTab('trash')]);
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'restore'){
        const conf = await Swal.fire({
          title: 'Restore this recruiter?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Restore'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/recruiters/${encodeURIComponent(uuid)}/restore`, {
            method: 'POST',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Restore failed');

          ok('Restored');
          await Promise.all([loadTab('trash'), loadTab('active'), loadTab('inactive')]);
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }

      if (act === 'force'){
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
          const res = await fetchWithTimeout(`/api/recruiters/${encodeURIComponent(uuid)}/force`, {
            method: 'DELETE',
            headers: authHeaders()
          }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Force delete failed');

          ok('Deleted permanently');
          await loadTab('trash');
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
        return;
      }
    });

    // submit (create/edit)
    form?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (saving) return;
      saving = true;

      try{
        if (form.dataset.mode === 'view') return;

        const intent = form.dataset.intent || 'create';
        const isEdit = (intent === 'edit') && !!recUuid.value;

        if (isEdit && !canEdit) return;
        if (!isEdit && !canCreate) return;

        const title = (titleEl.value || '').trim();
        if (!title){ err('Company name is required'); titleEl.focus(); return; }

        syncDescToHidden();
        const description = (descHidden.value || '').trim();

        const deptId = (deptSelEl?.value || '').trim();

        const fd = new FormData();
        fd.append('title', title);

        const slug = (slugEl.value || '').trim();
        if (slug) fd.append('slug', slug);

        fd.append('status', (statusEl.value || 'active').trim());
        fd.append('is_featured_home', (featuredEl.value || '0').trim() === '1' ? '1' : '0');
        fd.append('sort_order', String(parseInt(sortOrderEl.value || '0', 10) || 0));

        if (description) fd.append('description', description);

        // ✅ Job roles from UI -> JSON array
        const roles = getRolesFromUI();
        if (roles.length){
          fd.append('job_roles_json', JSON.stringify(roles));
          if (jobRolesJsonEl) jobRolesJsonEl.value = JSON.stringify(roles);
        } else {
          // allow clearing on edit
          if (isEdit) fd.append('job_roles_json', '[]');
          if (jobRolesJsonEl) jobRolesJsonEl.value = '';
        }

        const md = asJsonString(metaEl?.value || '');
        if (md) fd.append('metadata', md);

        // Create: use department route when selected
        let url = '/api/recruiters';
        if (!isEdit && deptId){
          url = `/api/recruiters/department/${encodeURIComponent(deptId)}`;
        }
        // Edit: send department_id
        if (isEdit && deptId){
          fd.append('department_id', deptId);
        }

        if (logoRemoveEl && logoRemoveEl.checked){
          fd.append('logo_remove', '1');
        }

        const logoUrl = (logoUrlEl.value || '').trim();
        if (logoUrl) fd.append('logo_url', logoUrl);

        const logoFile = logoFileEl.files?.[0] || null;
        if (logoFile) fd.append('logo', logoFile);

        if (isEdit){
          url = `/api/recruiters/${encodeURIComponent(recUuid.value)}`;
          fd.append('_method', 'PATCH');
        }

        setBtnLoading(saveBtn, true);
        showLoading(true);

        const res = await fetchWithTimeout(url, {
          method: 'POST',
          headers: authHeaders(),
          body: fd
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

        ok(isEdit ? 'Updated' : 'Created');
        itemModal && itemModal.hide();

        state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
        await Promise.all([loadTab('active'), loadTab('inactive'), loadTab('trash')]);

      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        saving = false;
        setBtnLoading(saveBtn, false);
        showLoading(false);
      }
    });

    /* ========= Init ========= */
    (async () => {
      showLoading(true);
      try{
        await fetchMe();
        await loadDepartments(); // ✅ so table shows only department names

        // ✅ ensure roles builder has at least one row on first open
        setRolesUI([], false);

        await Promise.all([loadTab('active'), loadTab('inactive'), loadTab('trash')]);
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
