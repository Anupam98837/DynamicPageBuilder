{{-- resources/views/modules/whyUs/manageWhyUs.blade.php --}}
@section('title','Why Us')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Why Us (Admin)
 * Reference-inspired structure (rewritten)
 * ========================= */

/* Shell */
.wu-wrap{padding:14px 4px;max-width:1140px;margin:16px auto 40px;overflow:visible}

/* Tabs */
.wu-tabs.nav-tabs{border-color:var(--line-strong)}
.wu-tabs .nav-link{color:var(--ink)}
.wu-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface)
}
.tab-content,.tab-pane{overflow:visible}

/* Toolbar */
.wu-toolbar.panel{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:16px;
  box-shadow:var(--shadow-2);
  padding:12px;
}

/* Table card */
.wu-table.card{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.wu-table .card-body{overflow:visible}
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
td .fw-semibold{color:var(--ink)}
.small{font-size:12.5px}

/* Responsive horizontal scroll */
.table-responsive{
  display:block;
  width:100%;
  max-width:100%;
  overflow-x:auto !important;
  overflow-y:visible !important;
  -webkit-overflow-scrolling:touch;
  position:relative; /* ✅ helps dropdown positioning like reference */
}
.table-responsive > .table{width:max-content;min-width:1080px}
.table-responsive th,.table-responsive td{white-space:nowrap}
@media (max-width:576px){ .table-responsive > .table{min-width:1020px} }

/* Dropdown safety inside table */
.wu-table .dropdown{position:relative}
.wu-dd-toggle{border-radius:10px}
.dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:230px;
  z-index:99999; /* ✅ match reference - avoid behind/clip */
}
.dropdown-menu.show{display:block !important}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color) !important}

/* Badges */
.badge-soft-success{
  background:color-mix(in oklab, var(--success-color) 12%, transparent);
  color:var(--success-color)
}
.badge-soft-warning{
  background:color-mix(in oklab, var(--warning-color, #f59e0b) 14%, transparent);
  color:var(--warning-color, #f59e0b)
}
.badge-soft-muted{
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
  color:var(--muted-color)
}
.badge-soft-primary{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  color:var(--primary-color)
}

/* Loading overlay */
.wu-loading{
  position:fixed;inset:0;
  background:rgba(0,0,0,0.45);
  display:flex;align-items:center;justify-content:center;
  z-index:9999;
  backdrop-filter:blur(2px);
}
.wu-loading .box{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;flex-direction:column;align-items:center;
  gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,0.3);
}
.wu-spin{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,0.3);
  border-top:4px solid var(--primary-color);
  animation:wuSpin 1s linear infinite;
}
@keyframes wuSpin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

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
  animation:wuSpin 1s linear infinite;
}

/* Editor (simple RTE) */
.wu-rte{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:var(--surface);
}
.wu-rte .bar{
  display:flex;align-items:center;gap:6px;flex-wrap:wrap;
  padding:8px;
  border-bottom:1px solid var(--line-strong);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.wu-rte .btn-rte{
  border:1px solid var(--line-soft);
  background:transparent;
  color:var(--ink);
  padding:7px 9px;
  border-radius:10px;
  line-height:1;
  cursor:pointer;
  display:inline-flex;align-items:center;justify-content:center;
}
.wu-rte .btn-rte:hover{background:var(--page-hover)}
.wu-rte .btn-rte.active{
  background:color-mix(in oklab, var(--primary-color) 14%, transparent);
  border-color:color-mix(in oklab, var(--primary-color) 35%, var(--line-soft));
}
.wu-rte .sep{width:1px;height:24px;background:var(--line-soft);margin:0 4px}

.wu-rte .mode{
  margin-left:auto;display:flex;overflow:hidden;
  border:1px solid var(--line-soft);
}
.wu-rte .mode button{
  border:0;background:transparent;color:var(--ink);
  padding:7px 12px;font-size:12px;cursor:pointer;
  border-right:1px solid var(--line-soft);
}
.wu-rte .mode button:last-child{border-right:0}
.wu-rte .mode button.active{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  font-weight:700;
}

.wu-rte .area{position:relative}
.wu-editor{
  min-height:200px;
  padding:12px;
  outline:none;
}
.wu-editor:empty:before{content:attr(data-placeholder);color:var(--muted-color);}
.wu-code{
  display:none;
  width:100%;
  min-height:200px;
  padding:12px;
  border:0;
  outline:none;
  resize:vertical;
  background:transparent;
  color:var(--ink);
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace;
  font-size:12.5px;
  line-height:1.45;
}
.wu-rte.is-code .wu-editor{display:none}
.wu-rte.is-code .wu-code{display:block}

.wu-icon-preview{
  display:flex;align-items:center;gap:10px;
  border:1px dashed var(--line-soft);
  border-radius:14px;
  padding:10px 12px;
  background:color-mix(in oklab, var(--surface) 90%, transparent);
}
.wu-icon-preview i{font-size:22px;color:var(--primary-color)}
</style>
@endpush

@section('content')
<div class="wu-wrap">

  {{-- Loading Overlay --}}
  <div id="wuGlobalLoading" class="wu-loading" style="display:none;">
    <div class="box">
      <div class="wu-spin"></div>
      <div class="small">Loading…</div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav wu-tabs nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#wu-tab-active" role="tab" aria-selected="true">
        <i class="fa-solid fa-circle-check me-2"></i>Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#wu-tab-inactive" role="tab" aria-selected="false">
        <i class="fa-solid fa-circle-minus me-2"></i>Inactive
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#wu-tab-trash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Trash
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- ACTIVE TAB --}}
    <div class="tab-pane fade show active" id="wu-tab-active" role="tabpanel">

      {{-- Toolbar --}}
      <div class="row align-items-center g-2 mb-3 wu-toolbar panel">
        <div class="col-12 col-lg d-flex align-items-center flex-wrap gap-2">

          <div class="d-flex align-items-center gap-2">
            <label class="text-muted small mb-0">Per Page</label>
            <select id="wuPerPage" class="form-select" style="width:96px;">
              <option>10</option>
              <option selected>20</option>
              <option>50</option>
              <option>100</option>
            </select>
          </div>

          <div class="position-relative" style="min-width:280px;">
            <input id="wuSearch" type="search" class="form-control ps-5" placeholder="Search by title / subtitle…">
            <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
          </div>

          <button class="btn btn-outline-primary" id="wuBtnFilter" data-bs-toggle="modal" data-bs-target="#wuFilterModal">
            <i class="fa fa-sliders me-1"></i>Filter
          </button>

          <button class="btn btn-light" id="wuBtnReset">
            <i class="fa fa-rotate-left me-1"></i>Reset
          </button>
        </div>

        <div class="col-12 col-lg-auto ms-lg-auto d-flex justify-content-lg-end">
          <div id="wuWriteControls" style="display:none;">
            <button type="button" class="btn btn-primary" id="wuBtnAdd">
              <i class="fa fa-plus me-1"></i> Add Why Us
            </button>
          </div>
        </div>
      </div>

      {{-- Table --}}
      <div class="card wu-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th>Subtitle</th>
                  <th style="width:120px;">Icon</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:120px;">Featured</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="wuTbodyActive">
                <tr><td colspan="8" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="wuEmptyActive" class="p-4 text-center" style="display:none;">
            <i class="fa-solid fa-circle-check mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No active items found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="wuInfoActive">—</div>
            <nav><ul id="wuPagerActive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- INACTIVE TAB --}}
    <div class="tab-pane fade" id="wu-tab-inactive" role="tabpanel">
      <div class="card wu-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th>Subtitle</th>
                  <th style="width:120px;">Icon</th>
                  <th style="width:120px;">Status</th>
                  <th style="width:120px;">Featured</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:170px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="wuTbodyInactive">
                <tr><td colspan="8" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="wuEmptyInactive" class="p-4 text-center" style="display:none;">
            <i class="fa-solid fa-circle-minus mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>No inactive items found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="wuInfoInactive">—</div>
            <nav><ul id="wuPagerInactive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- TRASH TAB --}}
    <div class="tab-pane fade" id="wu-tab-trash" role="tabpanel">
      <div class="card wu-table">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th style="width:140px;">Deleted</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="wuTbodyTrash">
                <tr><td colspan="4" class="text-center text-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="wuEmptyTrash" class="p-4 text-center" style="display:none;">
            <i class="fa-solid fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div>Trash is empty.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small" id="wuInfoTrash">—</div>
            <nav><ul id="wuPagerTrash" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="wuFilterModal" tabindex="-1" aria-hidden="true">
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
            <select id="wuModalStatus" class="form-select">
              <option value="">All</option>
              <option value="published">Published</option>
              <option value="draft">Draft</option>
              <option value="archived">Archived</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="wuModalSort" class="form-select">
              <option value="-created_at">Newest First</option>
              <option value="created_at">Oldest First</option>
              <option value="-updated_at">Recently Updated</option>
              <option value="title">Title A-Z</option>
              <option value="-title">Title Z-A</option>
              <option value="sort_order">Sort Order ↑</option>
              <option value="-sort_order">Sort Order ↓</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Featured</label>
            <select id="wuModalFeatured" class="form-select">
              <option value="">Any</option>
              <option value="1">Featured only</option>
              <option value="0">Not featured</option>
            </select>
          </div>
        </div>
        <div class="form-text mt-3">
          If your WhyUs API uses different query keys, update them in the JS section (<code>buildUrl()</code>).
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="wuBtnApplyFilters" class="btn btn-primary">
          <i class="fa fa-check me-1"></i>Apply Filters
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Add/Edit/View Modal --}}
<div class="modal fade" id="wuItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form class="modal-content" id="wuItemForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="wuItemModalTitle">Add Why Us</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="wuUuid">
        <input type="hidden" id="wuId">

        <div class="row g-3">
          <div class="col-lg-6">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input class="form-control" id="wuTitle" required maxlength="255" placeholder="e.g., Industry Expert Mentors">
              </div>

              <div class="col-12">
                <label class="form-label">Subtitle (optional)</label>
                <input class="form-control" id="wuSubtitle" maxlength="255" placeholder="e.g., Learn from real-world professionals">
              </div>

              <div class="col-md-6">
                <label class="form-label">Icon class (optional)</label>
                <input class="form-control" id="wuIcon" maxlength="120" placeholder="fa-solid fa-graduation-cap">
                <div class="form-text">Use FontAwesome class names (e.g., <code>fa-solid fa-star</code>).</div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Sort Order</label>
                <input type="number" class="form-control" id="wuSortOrder" min="0" max="1000000" value="0">
              </div>

              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select" id="wuStatus">
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="archived">Archived</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label">Featured on Home</label>
                <select class="form-select" id="wuFeatured">
                  <option value="0">No</option>
                  <option value="1">Yes</option>
                </select>
              </div>

              <div class="col-12">
                <div class="wu-icon-preview" id="wuIconPreviewBox">
                  <i class="fa-regular fa-circle-question" id="wuIconPreview"></i>
                  <div class="small text-muted">
                    <div class="fw-semibold" style="color:var(--ink)">Icon Preview</div>
                    <div id="wuIconPreviewText">Type an icon class to preview</div>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <div class="col-lg-6">
            <label class="form-label">Description (HTML allowed) <span class="text-danger">*</span></label>

            <div class="wu-rte" id="wuRteWrap">
              <div class="bar" id="wuRteBar">
                <button type="button" class="btn-rte" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
                <button type="button" class="btn-rte" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
                <button type="button" class="btn-rte" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>
                <span class="sep"></span>
                <button type="button" class="btn-rte" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
                <button type="button" class="btn-rte" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>
                <span class="sep"></span>
                <button type="button" class="btn-rte" data-block="h2" title="Heading 2">H2</button>
                <button type="button" class="btn-rte" data-block="h3" title="Heading 3">H3</button>
                <span class="sep"></span>
                <button type="button" class="btn-rte" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>

                <div class="mode">
                  <button type="button" class="active" data-mode="text">Text</button>
                  <button type="button" data-mode="code">Code</button>
                </div>
              </div>

              <div class="area">
                <div id="wuEditor" class="wu-editor" contenteditable="true" data-placeholder="Write why-us description…"></div>
                <textarea id="wuCode" class="wu-code" spellcheck="false" autocomplete="off" autocapitalize="off" autocorrect="off"
                  placeholder="HTML code…"></textarea>
              </div>
            </div>

            <div class="form-text mt-2">Use <b>Text</b> to edit normally, or switch to <b>Code</b> to paste HTML.</div>

            {{-- hidden field (we’ll keep both keys in request to be safe) --}}
            <input type="hidden" id="wuBody" name="body">
            <input type="hidden" id="wuDescription" name="description">
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" id="wuSaveBtn">
          <i class="fa fa-floppy-disk me-1"></i> Save
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="wuToastOk" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="wuToastOkText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="wuToastErr" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="wuToastErrText">Something went wrong</div>
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
  if (window.__WHYUS_MODULE_INIT__) return;
  window.__WHYUS_MODULE_INIT__ = true;

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

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    /* =========================
     * ✅ API Endpoints (edit if your routes differ)
     * ========================= */
    const API = {
      list:   '/api/why-us',
      create: '/api/why-us',
      update: (uuid) => `/api/why-us/${encodeURIComponent(uuid)}`,          // PATCH via POST + _method=PATCH
      del:    (uuid) => `/api/why-us/${encodeURIComponent(uuid)}`,          // DELETE
      restore:(uuid) => `/api/why-us/${encodeURIComponent(uuid)}/restore`,  // POST
      force:  (uuid) => `/api/why-us/${encodeURIComponent(uuid)}/force`,    // DELETE
      me:     '/api/users/me'
    };

    const loadingEl = $('wuGlobalLoading');
    const showLoading = (v) => { if (loadingEl) loadingEl.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('wuToastOk');
    const toastErrEl = $('wuToastErr');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { const el=$('wuToastOkText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('wuToastErrText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = () => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    });

    /* =========================
     * Permissions (same idea)
     * ========================= */
    const ACTOR = { role: '' };
    let canCreate=false, canEdit=false, canDelete=false;

    function computePermissions(){
      const r = (ACTOR.role || '').toLowerCase();
      const createDeleteRoles = ['admin','super_admin','director','principal'];
      const writeRoles = ['admin','super_admin','director','principal','hod','faculty','technical_assistant','it_person'];
      canCreate = createDeleteRoles.includes(r);
      canDelete = createDeleteRoles.includes(r);
      canEdit   = writeRoles.includes(r);
      const wc = $('wuWriteControls');
      if (wc) wc.style.display = canCreate ? 'flex' : 'none';
    }

    async function fetchMe(){
      try{
        const res = await fetchWithTimeout(API.me, { headers: authHeaders() }, 8000);
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

    /* =========================
     * State
     * ========================= */
    const state = {
      filters: { q:'', status:'', featured:'', sort:'-created_at' },
      perPage: parseInt(($('wuPerPage')?.value || '20'), 10) || 20,
      tabs: {
        active:   { page:1, lastPage:1, items:[] },
        inactive: { page:1, lastPage:1, items:[] },
        trash:    { page:1, lastPage:1, items:[] }
      }
    };

    const getTabKey = () => {
      const a = document.querySelector('.wu-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#wu-tab-active';
      if (href === '#wu-tab-inactive') return 'inactive';
      if (href === '#wu-tab-trash') return 'trash';
      return 'active';
    };

    function buildUrl(tabKey){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state.tabs[tabKey].page));

      const q = (state.filters.q || '').trim();
      if (q) params.set('q', q);

      const s = state.filters.sort || '-created_at';
      params.set('sort', s.startsWith('-') ? s.slice(1) : s);
      params.set('direction', s.startsWith('-') ? 'desc' : 'asc');

      if (state.filters.status) params.set('status', state.filters.status);
      if (state.filters.featured !== '') params.set('featured', state.filters.featured);

      // Tabs
      if (tabKey === 'active') params.set('active', '1');
      if (tabKey === 'inactive') params.set('active', '0');
      if (tabKey === 'trash') params.set('only_trashed', '1');

      return `${API.list}?${params.toString()}`;
    }

    function badgeStatus(status){
      const s = (status || '').toString().toLowerCase();
      if (s === 'published') return `<span class="badge badge-soft-success">Published</span>`;
      if (s === 'draft') return `<span class="badge badge-soft-warning">Draft</span>`;
      if (s === 'archived') return `<span class="badge badge-soft-muted">Archived</span>`;
      return `<span class="badge badge-soft-muted">${esc(s || '—')}</span>`;
    }

    function badgeFeatured(v){
      return v ? `<span class="badge badge-soft-primary">Yes</span>` : `<span class="badge badge-soft-muted">No</span>`;
    }

    function setEmpty(tabKey, show){
      const map = {
        active: $('wuEmptyActive'),
        inactive: $('wuEmptyInactive'),
        trash: $('wuEmptyTrash')
      };
      if (map[tabKey]) map[tabKey].style.display = show ? '' : 'none';
    }

    function renderPager(tabKey){
      const pagerEl = tabKey==='active' ? $('wuPagerActive') : (tabKey==='inactive' ? $('wuPagerInactive') : $('wuPagerTrash'));
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

    function renderTable(tabKey){
      const tbody = tabKey==='active' ? $('wuTbodyActive') : (tabKey==='inactive' ? $('wuTbodyInactive') : $('wuTbodyTrash'));
      const rows = state.tabs[tabKey].items || [];
      if (!tbody) return;

      if (!rows.length){
        tbody.innerHTML = '';
        setEmpty(tabKey, true);
        renderPager(tabKey);
        return;
      }
      setEmpty(tabKey, false);

      tbody.innerHTML = rows.map(r => {
        const uuid = r.uuid || '';
        const title = r.title || '—';
        const subtitle = r.subtitle || r.sub_title || r.short_text || '—';
        const icon = r.icon || r.icon_class || r.icon_name || '';
        const status = r.status || (r.active ? 'published' : 'draft');
        const featured = !!(r.is_featured_home ?? r.featured ?? 0);
        const sortOrder = (r.sort_order ?? 0);
        const updated = r.updated_at || '—';
        const deleted = r.deleted_at || '—';

        const iconCell = icon
          ? `<span class="d-inline-flex align-items-center gap-2"><i class="${esc(icon)}"></i><span class="small text-muted">${esc(icon)}</span></span>`
          : `<span class="text-muted small">—</span>`;

        // ✅ FIX: like reference page, remove data-bs-toggle and manually control dropdown (fixed strategy)
        let actions = `
          <div class="dropdown text-end">
            <button type="button" class="btn btn-light btn-sm wu-dd-toggle"
              aria-expanded="false" title="Actions">
              <i class="fa fa-ellipsis-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>`;

        if (canEdit && tabKey !== 'trash'){
          actions += `<li><button type="button" class="dropdown-item" data-action="edit"><i class="fa fa-pen-to-square"></i> Edit</button></li>`;
        }

        if (tabKey !== 'trash'){
          if (canDelete){
            actions += `<li><hr class="dropdown-divider"></li>
              <li><button type="button" class="dropdown-item text-danger" data-action="delete"><i class="fa fa-trash"></i> Delete</button></li>`;
          }
        } else {
          actions += `<li><hr class="dropdown-divider"></li>
            <li><button type="button" class="dropdown-item" data-action="restore"><i class="fa fa-rotate-left"></i> Restore</button></li>`;
          if (canDelete){
            actions += `<li><button type="button" class="dropdown-item text-danger" data-action="force"><i class="fa fa-skull-crossbones"></i> Delete Permanently</button></li>`;
          }
        }

        actions += `</ul></div>`;

        if (tabKey === 'trash'){
          return `
            <tr data-uuid="${esc(uuid)}">
              <td class="fw-semibold">${esc(title)}</td>
              <td>${esc(String(deleted))}</td>
              <td>${esc(String(sortOrder))}</td>
              <td class="text-end">${actions}</td>
            </tr>`;
        }

        return `
          <tr data-uuid="${esc(uuid)}">
            <td class="fw-semibold">${esc(title)}</td>
            <td>${esc(String(subtitle || '—'))}</td>
            <td>${iconCell}</td>
            <td>${badgeStatus(status)}</td>
            <td>${badgeFeatured(featured)}</td>
            <td>${esc(String(sortOrder))}</td>
            <td>${esc(String(updated))}</td>
            <td class="text-end">${actions}</td>
          </tr>`;
      }).join('');

      renderPager(tabKey);
    }

    async function loadTab(tabKey){
      const tbody = tabKey==='active' ? $('wuTbodyActive') : (tabKey==='inactive' ? $('wuTbodyInactive') : $('wuTbodyTrash'));
      if (tbody){
        const cols = (tabKey==='trash') ? 4 : 8;
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
        state.tabs[tabKey].lastPage = parseInt(p.last_page || p.total_pages || 1, 10) || 1;

        const total = p.total ?? p.count ?? '';
        if (tabKey === 'active' && $('wuInfoActive')) $('wuInfoActive').textContent = total ? `${total} result(s)` : '—';
        if (tabKey === 'inactive' && $('wuInfoInactive')) $('wuInfoInactive').textContent = total ? `${total} result(s)` : '—';
        if (tabKey === 'trash' && $('wuInfoTrash')) $('wuInfoTrash').textContent = total ? `${total} result(s)` : '—';

        renderTable(tabKey);
      }catch(e){
        state.tabs[tabKey].items = [];
        state.tabs[tabKey].lastPage = 1;
        renderTable(tabKey);
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    function reloadCurrent(){ loadTab(getTabKey()); }

    /* =========================
     * Pager click
     * ========================= */
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

    /* =========================
     * Filters / search
     * ========================= */
    $('wuSearch')?.addEventListener('input', debounce(() => {
      state.filters.q = ($('wuSearch').value || '').trim();
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    }, 320));

    $('wuPerPage')?.addEventListener('change', () => {
      state.perPage = parseInt($('wuPerPage').value, 10) || 20;
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    const filterModalEl = $('wuFilterModal');
    const filterModal = filterModalEl ? new bootstrap.Modal(filterModalEl) : null;

    filterModalEl?.addEventListener('show.bs.modal', () => {
      $('wuModalStatus').value = state.filters.status || '';
      $('wuModalSort').value = state.filters.sort || '-created_at';
      $('wuModalFeatured').value = (state.filters.featured ?? '');
    });

    $('wuBtnApplyFilters')?.addEventListener('click', () => {
      state.filters.status = $('wuModalStatus')?.value || '';
      state.filters.sort = $('wuModalSort')?.value || '-created_at';
      state.filters.featured = ($('wuModalFeatured')?.value ?? '');
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      filterModal && filterModal.hide();
      reloadCurrent();
    });

    $('wuBtnReset')?.addEventListener('click', () => {
      state.filters = { q:'', status:'', featured:'', sort:'-created_at' };
      state.perPage = 20;

      if ($('wuSearch')) $('wuSearch').value = '';
      if ($('wuPerPage')) $('wuPerPage').value = '20';

      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    document.querySelector('a[href="#wu-tab-active"]')?.addEventListener('shown.bs.tab', () => loadTab('active'));
    document.querySelector('a[href="#wu-tab-inactive"]')?.addEventListener('shown.bs.tab', () => loadTab('inactive'));
    document.querySelector('a[href="#wu-tab-trash"]')?.addEventListener('shown.bs.tab', () => loadTab('trash'));

    /* =========================
     * ✅ ACTION DROPDOWN FIX (same approach as Contact Info)
     * ========================= */
    function closeAllDropdownsExcept(exceptToggle){
      document.querySelectorAll('.wu-dd-toggle').forEach(t => {
        if (t === exceptToggle) return;
        try{
          const inst = bootstrap.Dropdown.getInstance(t);
          inst && inst.hide();
        }catch(_){}
      });
    }

    // Toggle dropdown manually with Popper "fixed" strategy (escapes overflow clipping)
    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('.wu-dd-toggle');
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

    // Clicking elsewhere closes dropdowns (capture so it works consistently)
    document.addEventListener('click', () => {
      closeAllDropdownsExcept(null);
    }, { capture: true });

    /* =========================
     * Modal + RTE
     * ========================= */
    const itemModalEl = $('wuItemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;

    const form = $('wuItemForm');
    const saveBtn = $('wuSaveBtn');

    const fUuid = $('wuUuid');
    const fId = $('wuId');
    const fTitle = $('wuTitle');
    const fSubtitle = $('wuSubtitle');
    const fIcon = $('wuIcon');
    const fSort = $('wuSortOrder');
    const fStatus = $('wuStatus');
    const fFeatured = $('wuFeatured');

    const iconPrev = $('wuIconPreview');
    const iconPrevText = $('wuIconPreviewText');

    const rte = {
      wrap: $('wuRteWrap'),
      bar: $('wuRteBar'),
      editor: $('wuEditor'),
      code: $('wuCode'),
      hiddenBody: $('wuBody'),
      hiddenDesc: $('wuDescription'),
      mode: 'text',
      enabled: true
    };

    const setBtnLoading = (btn, loading) => {
      if (!btn) return;
      btn.disabled = !!loading;
      btn.classList.toggle('btn-loading', !!loading);
    };

    function rteFocus(){
      try { rte.editor?.focus({ preventScroll:true }); }
      catch(_) { try { rte.editor?.focus(); } catch(__){} }
    }

    function syncToHidden(){
      const html = (rte.mode === 'code') ? (rte.code.value || '') : (rte.editor.innerHTML || '');
      const v = (html || '').trim();
      if (rte.hiddenBody) rte.hiddenBody.value = v;
      if (rte.hiddenDesc) rte.hiddenDesc.value = v;
    }

    function setRteMode(mode){
      rte.mode = (mode === 'code') ? 'code' : 'text';
      rte.wrap?.classList.toggle('is-code', rte.mode === 'code');

      rte.wrap?.querySelectorAll('.mode button').forEach(b => b.classList.toggle('active', b.dataset.mode === rte.mode));

      const disable = (rte.mode === 'code') || !rte.enabled;
      rte.wrap?.querySelectorAll('.btn-rte').forEach(b => {
        b.disabled = disable;
        b.style.opacity = disable ? '0.55' : '';
        b.style.pointerEvents = disable ? 'none' : '';
      });

      if (rte.mode === 'code'){
        rte.code.value = rte.editor.innerHTML || '';
        setTimeout(()=>{ try{ rte.code.focus(); }catch(_){ } }, 0);
      } else {
        rte.editor.innerHTML = rte.code.value || '';
        setTimeout(()=>{ rteFocus(); }, 0);
      }
      syncToHidden();
    }

    function updateToolbarActive(){
      if (!rte.bar || rte.mode !== 'text') return;
      const set = (cmd, on) => {
        const b = rte.bar.querySelector('.btn-rte[data-cmd="'+cmd+'"]');
        if (b) b.classList.toggle('active', !!on);
      };
      try{
        set('bold', document.queryCommandState('bold'));
        set('italic', document.queryCommandState('italic'));
        set('underline', document.queryCommandState('underline'));
      }catch(_){}
    }

    rte.bar?.addEventListener('pointerdown', (e) => { e.preventDefault(); });

    rte.editor?.addEventListener('input', () => { syncToHidden(); updateToolbarActive(); });
    ['mouseup','keyup','click'].forEach(ev => rte.editor?.addEventListener(ev, updateToolbarActive));
    document.addEventListener('selectionchange', () => {
      if (document.activeElement === rte.editor) updateToolbarActive();
    });

    document.addEventListener('click', (e) => {
      const modeBtn = e.target.closest('#wuRteBar .mode button');
      if (modeBtn){ setRteMode(modeBtn.dataset.mode); return; }

      const btn = e.target.closest('#wuRteBar .btn-rte');
      if (!btn || rte.mode !== 'text' || !rte.enabled) return;

      rteFocus();

      const block = btn.getAttribute('data-block');
      const cmd = btn.getAttribute('data-cmd');

      if (block){
        try{ document.execCommand('formatBlock', false, '<'+block+'>'); }catch(_){}
        syncToHidden(); updateToolbarActive();
        return;
      }
      if (cmd){
        try{ document.execCommand(cmd, false, null); }catch(_){}
        syncToHidden(); updateToolbarActive();
      }
    });

    function setRteEnabled(on){
      rte.enabled = !!on;
      if (rte.editor) rte.editor.setAttribute('contenteditable', on ? 'true' : 'false');
      if (rte.code) rte.code.disabled = !on;

      const disable = (rte.mode === 'code') || !rte.enabled;
      rte.wrap?.querySelectorAll('.btn-rte').forEach(b => {
        b.disabled = disable;
        b.style.opacity = disable ? '0.55' : '';
        b.style.pointerEvents = disable ? 'none' : '';
      });
      rte.wrap?.querySelectorAll('.mode button').forEach(b => {
        b.style.pointerEvents = on ? '' : 'none';
        b.style.opacity = on ? '' : '0.7';
      });
    }

    function resetForm(){
      form?.reset();
      fUuid.value = ''; fId.value = '';
      if (rte.editor) rte.editor.innerHTML = '';
      if (rte.code) rte.code.value = '';
      if (rte.hiddenBody) rte.hiddenBody.value = '';
      if (rte.hiddenDesc) rte.hiddenDesc.value = '';
      setRteMode('text');
      setRteEnabled(true);

      if (iconPrev){ iconPrev.className = 'fa-regular fa-circle-question'; }
      if (iconPrevText){ iconPrevText.textContent = 'Type an icon class to preview'; }

      form.dataset.mode = 'edit';
      form.dataset.intent = 'create';
      if (saveBtn) saveBtn.style.display = '';
    }

    function fillFormFromRow(r, viewOnly=false){
      fUuid.value = r.uuid || '';
      fId.value = r.id || '';

      fTitle.value = r.title || '';
      fSubtitle.value = r.subtitle || r.sub_title || r.short_text || '';
      fIcon.value = r.icon || r.icon_class || r.icon_name || '';

      fSort.value = String(r.sort_order ?? 0);
      fStatus.value = (r.status || (r.active ? 'published' : 'draft') || 'draft');
      fFeatured.value = String((r.is_featured_home ?? r.featured ?? 0) ? 1 : 0);

      const html = (r.body ?? r.description ?? r.content ?? '') || '';
      if (rte.editor) rte.editor.innerHTML = html;
      if (rte.code) rte.code.value = html;
      syncToHidden();
      setRteMode('text');

      // icon preview
      const cls = (fIcon.value || '').trim();
      if (iconPrev){
        iconPrev.className = cls ? cls : 'fa-regular fa-circle-question';
      }
      if (iconPrevText){
        iconPrevText.textContent = cls ? cls : 'Type an icon class to preview';
      }

      if (viewOnly){
        form?.querySelectorAll('input,select,textarea').forEach(el => {
          if (el.id === 'wuUuid' || el.id === 'wuId') return;
          if (el.tagName === 'SELECT') el.disabled = true;
          else el.readOnly = true;
        });
        setRteEnabled(false);
        if (saveBtn) saveBtn.style.display = 'none';
        form.dataset.mode = 'view';
        form.dataset.intent = 'view';
      } else {
        form?.querySelectorAll('input,select,textarea').forEach(el => {
          if (el.id === 'wuUuid' || el.id === 'wuId') return;
          if (el.tagName === 'SELECT') el.disabled = false;
          else el.readOnly = false;
        });
        setRteEnabled(true);
        if (saveBtn) saveBtn.style.display = '';
        form.dataset.mode = 'edit';
        form.dataset.intent = 'edit';
      }
    }

    function findRow(uuid){
      const all = [
        ...(state.tabs.active.items || []),
        ...(state.tabs.inactive.items || []),
        ...(state.tabs.trash.items || [])
      ];
      return all.find(x => x?.uuid === uuid) || null;
    }

    // icon live preview
    fIcon?.addEventListener('input', debounce(() => {
      const cls = (fIcon.value || '').trim();
      if (iconPrev) iconPrev.className = cls ? cls : 'fa-regular fa-circle-question';
      if (iconPrevText) iconPrevText.textContent = cls ? cls : 'Type an icon class to preview';
    }, 120));

    // open add
    $('wuBtnAdd')?.addEventListener('click', () => {
      if (!canCreate) return;
      resetForm();
      if ($('wuItemModalTitle')) $('wuItemModalTitle').textContent = 'Add Why Us';
      form.dataset.intent = 'create';
      itemModal && itemModal.show();
    });

    /* =========================
     * Row actions
     * ========================= */
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;

      const tr = btn.closest('tr');
      const uuid = tr?.dataset?.uuid;
      const act = btn.dataset.action;
      if (!uuid) return;

      const row = findRow(uuid);

      // close dropdown
      const toggle = btn.closest('.dropdown')?.querySelector('.wu-dd-toggle');
      if (toggle) { try { bootstrap.Dropdown.getInstance(toggle)?.hide(); } catch (_) {} }

      if (act === 'view' || act === 'edit'){
        if (act === 'edit' && !canEdit) return;
        resetForm();
        if ($('wuItemModalTitle')) $('wuItemModalTitle').textContent = act === 'view' ? 'View Why Us' : 'Edit Why Us';
        fillFormFromRow(row || {}, act === 'view');
        itemModal && itemModal.show();
        return;
      }

      if (act === 'delete'){
        if (!canDelete) return;

        const conf = await Swal.fire({
          title: 'Delete this item?',
          text: 'This will move it to Trash.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Delete',
          confirmButtonColor: '#ef4444'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(API.del(uuid), { method:'DELETE', headers: authHeaders() }, 15000);
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
          title: 'Restore this item?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Restore'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(API.restore(uuid), { method:'POST', headers: authHeaders() }, 15000);
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
          const res = await fetchWithTimeout(API.force(uuid), { method:'DELETE', headers: authHeaders() }, 15000);
          const js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Force delete failed');
          ok('Deleted permanently');
          await loadTab('trash');
        }catch(ex){
          err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
        }finally{
          showLoading(false);
        }
      }
    });

    /* =========================
     * Submit create/edit
     * ========================= */
    let saving = false;

    form?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (saving) return;
      saving = true;

      try{
        if (form.dataset.mode === 'view') return;

        const intent = form.dataset.intent || 'create';
        const isEdit = intent === 'edit' && !!fUuid.value;

        if (isEdit && !canEdit) return;
        if (!isEdit && !canCreate) return;

        const title = (fTitle.value || '').trim();
        const subtitle = (fSubtitle.value || '').trim();
        const icon = (fIcon.value || '').trim();
        const status = (fStatus.value || 'draft').trim();
        const featured = (fFeatured.value || '0').trim();
        const sortOrder = String(parseInt(fSort.value || '0', 10) || 0);

        const html = (rte.mode === 'code') ? (rte.code.value || '') : (rte.editor.innerHTML || '');
        const body = (html || '').trim();

        syncToHidden();

        if (!title){ err('Title is required'); fTitle.focus(); return; }
        if (!body){ err('Description is required'); rteFocus(); return; }

        const fd = new FormData();
        fd.append('title', title);
        if (subtitle) fd.append('subtitle', subtitle);
        if (icon) fd.append('icon', icon);

        fd.append('status', status);
        fd.append('is_featured_home', featured === '1' ? '1' : '0');
        fd.append('sort_order', sortOrder);

        // keep both keys (many backends use either "body" or "description")
        fd.append('body', body);
        fd.append('description', body);

        const url = isEdit ? API.update(fUuid.value) : API.create;
        if (isEdit) fd.append('_method', 'PATCH');

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

    /* =========================
     * Init
     * ========================= */
    (async () => {
      showLoading(true);
      try{
        await fetchMe();
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
