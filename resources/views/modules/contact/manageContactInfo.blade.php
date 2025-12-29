{{-- resources/views/modules/contact/manageContactInfo.blade.php --}}
@section('title','Contact Info')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
  /* =========================
   * Contact Info - Admin UI
   * ========================= */

  /* Tabs */
  .ci-tabs.nav-tabs{border-color:var(--line-strong)}
  .ci-tabs .nav-link{color:var(--ink)}
  .ci-tabs .nav-link.active{
    background:var(--surface);
    border-color:var(--line-strong) var(--line-strong) var(--surface);
  }

  /* Card/Table */
  .ci-card{
    border:1px solid var(--line-strong);
    border-radius:16px;
    background:var(--surface);
    box-shadow:var(--shadow-2);
    overflow:visible;
  }
  .ci-card .card-body{overflow:visible}
  .ci-table{--bs-table-bg:transparent}
  .ci-table thead th{
    font-weight:650;
    color:var(--muted-color);
    font-size:13px;
    border-bottom:1px solid var(--line-strong);
    background:var(--surface);
  }
  .ci-table thead.sticky-top{z-index:3}
  .ci-table tbody tr{border-top:1px solid var(--line-soft)}
  .ci-table tbody tr:hover{background:var(--page-hover)}
  .ci-muted{color:var(--muted-color)}
  .ci-small{font-size:12.5px}

  /* Horizontal scroll */
  .table-responsive{
    display:block;
    width:100%;
    max-width:100%;
    overflow-x:auto !important;
    overflow-y:visible !important;
    -webkit-overflow-scrolling:touch;
    position:relative;
  }
  .table-responsive > table{width:max-content; min-width:1100px;}
  .table-responsive th,.table-responsive td{white-space:nowrap;}

  /* Dropdown - keep high z-index */
  .table-responsive .dropdown{position:relative}
  .ci-dd-toggle{border-radius:10px}
  .dropdown-menu{
    border-radius:12px;
    border:1px solid var(--line-strong);
    box-shadow:var(--shadow-2);
    min-width:230px;
    z-index:99999; /* ✅ higher to avoid being behind */
  }
  /* ✅ safety: if any global css forces dropdown-menu hidden, ensure .show wins */
  .dropdown-menu.show{display:block !important}

  .dropdown-item{display:flex;align-items:center;gap:.6rem}
  .dropdown-item i{width:16px;text-align:center}
  .dropdown-item.text-danger{color:var(--danger-color) !important}

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
  .badge-soft-warning{
    background:color-mix(in oklab, var(--warning-color, #f59e0b) 14%, transparent);
    color:var(--warning-color, #f59e0b)
  }

  /* Loading overlay */
  .ci-loading{
    position:fixed; inset:0;
    background:rgba(0,0,0,.45);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:9999;
    backdrop-filter:blur(2px);
  }
  .ci-loading .box{
    background:var(--surface);
    padding:18px 20px;
    border-radius:14px;
    display:flex;
    align-items:center;
    gap:12px;
    box-shadow:0 10px 26px rgba(0,0,0,.3);
  }
  .ci-spin{
    width:38px;height:38px;border-radius:50%;
    border:4px solid rgba(148,163,184,.3);
    border-top:4px solid var(--primary-color);
    animation:ciSpin 1s linear infinite;
  }
  @keyframes ciSpin{to{transform:rotate(360deg)}}

  /* Toolbar */
  .ci-toolbar{
    border:1px solid var(--line-strong);
    border-radius:16px;
    background:var(--surface);
    box-shadow:var(--shadow-2);
    padding:12px 12px;
  }
  .ci-toolbar .ci-search{
    min-width:280px;
    position:relative;
  }
  .ci-toolbar .ci-search input{padding-left:40px;}
  .ci-toolbar .ci-search i{
    position:absolute; left:12px; top:50%;
    transform:translateY(-50%); opacity:.6;
  }
  @media (max-width: 768px){
    .ci-toolbar .ci-row{flex-direction:column; align-items:stretch !important;}
    .ci-toolbar .ci-search{min-width:100%;}
    .ci-toolbar .ci-actions{display:flex; gap:8px; flex-wrap:wrap;}
    .ci-toolbar .ci-actions .btn{flex:1; min-width:140px;}
  }

  /* Icon preview */
  .ci-icon-preview{
    display:inline-flex; align-items:center; justify-content:center;
    width:34px; height:34px;
    border-radius:12px;
    border:1px solid var(--line-soft);
    background:color-mix(in oklab, var(--surface) 92%, transparent);
  }

  /* Modal helper */
  .ci-help{font-size:12.5px;color:var(--muted-color)}
  .ci-code{
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size:12.5px;
  }
</style>
@endpush

@section('content')
<div class="crs-wrap">

  {{-- Global Loading --}}
  <div id="ciLoading" class="ci-loading" aria-hidden="true">
    <div class="box">
      <div class="ci-spin"></div>
      <div class="ci-small">Loading…</div>
    </div>
  </div>

  {{-- Top Toolbar (applies to current tab) --}}
  <div class="ci-toolbar mb-3">
    <div class="d-flex align-items-center justify-content-between gap-2 ci-row">
      <div class="d-flex align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <label class="ci-small ci-muted mb-0">Per Page</label>
          <select id="ciPerPage" class="form-select" style="width:96px;">
            <option>10</option>
            <option selected>20</option>
            <option>50</option>
            <option>100</option>
          </select>
        </div>

        <div class="ci-search">
          <i class="fa fa-search"></i>
          <input id="ciSearch" type="search" class="form-control" placeholder="Search by name / key / value / type…">
        </div>

        <button id="ciBtnFilter" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#ciFilterModal">
          <i class="fa fa-sliders me-1"></i>Filter
        </button>

        <button id="ciBtnReset" class="btn btn-light">
          <i class="fa fa-rotate-left me-1"></i>Reset
        </button>
      </div>

      <div class="ci-actions" id="ciWriteControls" style="display:none;">
        <button id="ciBtnAdd" type="button" class="btn btn-primary">
          <i class="fa fa-plus me-1"></i> Add Contact Info
        </button>
      </div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs ci-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#ciTabActive" role="tab" aria-selected="true">
        <i class="fa-solid fa-circle-check me-2"></i>Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#ciTabInactive" role="tab" aria-selected="false">
        <i class="fa-solid fa-circle-pause me-2"></i>Inactive
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#ciTabTrash" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Trash
      </a>
    </li>
  </ul>

  <div class="tab-content">

    {{-- ACTIVE --}}
    <div class="tab-pane fade show active" id="ciTabActive" role="tabpanel">
      <div class="card ci-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table ci-table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:70px;">Icon</th>
                  <th>Name</th>
                  <th style="width:130px;">Type</th>
                  <th style="width:170px;">Key</th>
                  <th>Value</th>
                  <th style="width:110px;">Featured</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:180px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="ciTbodyActive">
                <tr><td colspan="9" class="text-center ci-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="ciEmptyActive" class="p-4 text-center" style="display:none;">
            <i class="fa fa-circle-check mb-2" style="font-size:32px;opacity:.6;"></i>
            <div class="ci-muted">No active contact info found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="ci-small ci-muted" id="ciInfoActive">—</div>
            <nav><ul id="ciPagerActive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- INACTIVE --}}
    <div class="tab-pane fade" id="ciTabInactive" role="tabpanel">
      <div class="card ci-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table ci-table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:70px;">Icon</th>
                  <th>Name</th>
                  <th style="width:130px;">Type</th>
                  <th style="width:170px;">Key</th>
                  <th>Value</th>
                  <th style="width:110px;">Featured</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:180px;">Updated</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="ciTbodyInactive">
                <tr><td colspan="9" class="text-center ci-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="ciEmptyInactive" class="p-4 text-center" style="display:none;">
            <i class="fa fa-circle-pause mb-2" style="font-size:32px;opacity:.6;"></i>
            <div class="ci-muted">No inactive contact info found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="ci-small ci-muted" id="ciInfoInactive">—</div>
            <nav><ul id="ciPagerInactive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- TRASH --}}
    <div class="tab-pane fade" id="ciTabTrash" role="tabpanel">
      <div class="card ci-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table ci-table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Name</th>
                  <th style="width:130px;">Type</th>
                  <th style="width:170px;">Key</th>
                  <th style="width:200px;">Deleted</th>
                  <th style="width:110px;">Sort</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="ciTbodyTrash">
                <tr><td colspan="6" class="text-center ci-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="ciEmptyTrash" class="p-4 text-center" style="display:none;">
            <i class="fa fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div class="ci-muted">Trash is empty.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="ci-small ci-muted" id="ciInfoTrash">—</div>
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
            <label class="form-label">Type</label>
            <select id="ciModalType" class="form-select">
              <option value="">All</option>
              <option value="contact">Contact</option>
              <option value="social">Social</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Key</label>
            <input id="ciModalKey" class="form-control" placeholder="email / phone / whatsapp / address / website / linkedin …">
            <div class="ci-help mt-1">Optional exact match (leave empty for all).</div>
          </div>

          <div class="col-12">
            <label class="form-label">Featured</label>
            <select id="ciModalFeatured" class="form-select">
              <option value="">Any</option>
              <option value="1">Featured only</option>
              <option value="0">Not featured</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Sort By</label>
            <select id="ciModalSort" class="form-select">
              <option value="sort_order">Sort Order ↑</option>
              <option value="-sort_order">Sort Order ↓</option>
              <option value="name">Name A-Z</option>
              <option value="-name">Name Z-A</option>
              <option value="updated_at">Updated (Oldest)</option>
              <option value="-updated_at" selected>Updated (Newest)</option>
              <option value="created_at">Created (Oldest)</option>
              <option value="-created_at">Created (Newest)</option>
              <option value="key">Key A-Z</option>
              <option value="-key">Key Z-A</option>
              <option value="type">Type A-Z</option>
              <option value="-type">Type Z-A</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="ciBtnApplyFilters" type="button">
          <i class="fa fa-check me-1"></i>Apply
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Add/Edit/View Modal --}}
<div class="modal fade" id="ciItemModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form class="modal-content" id="ciItemForm" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title" id="ciItemModalTitle">Add Contact Info</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="ciUuid">
        <input type="hidden" id="ciId">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Type</label>
            <select id="ciType" class="form-select">
              <option value="contact">Contact</option>
              <option value="social">Social</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select id="ciStatus" class="form-select">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Key <span class="text-danger">*</span></label>
            <input id="ciKey" class="form-control" required maxlength="60" placeholder="email / phone / whatsapp / address / website / linkedin …">
          </div>

          <div class="col-md-6">
            <label class="form-label">Display Name <span class="text-danger">*</span></label>
            <input id="ciName" class="form-control" required maxlength="120" placeholder="Admissions Office / Official LinkedIn …">
          </div>

          <div class="col-md-8">
            <label class="form-label">Value <span class="text-danger">*</span></label>
            <input id="ciValue" class="form-control" required maxlength="255" placeholder="example@email.com / +91… / https://…">
            <div class="ci-help mt-1">For social/website, you can paste full URL.</div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Sort Order</label>
            <input id="ciSortOrder" type="number" class="form-control" min="0" value="0">
          </div>

          <div class="col-md-8">
            <label class="form-label">Icon Class (FontAwesome)</label>
            <div class="input-group">
              <span class="input-group-text">
                <span class="ci-icon-preview" id="ciIconPreview"><i class="fa-solid fa-icons"></i></span>
              </span>
              <input id="ciIconClass" class="form-control" maxlength="120" placeholder="fa-solid fa-phone / fa-brands fa-whatsapp …">
            </div>
            <div class="ci-help mt-1">Optional. Leave blank if you don’t want an icon.</div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Featured on Home</label>
            <select id="ciFeatured" class="form-select">
              <option value="0">No</option>
              <option value="1">Yes</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Metadata (JSON, optional)</label>
            <textarea id="ciMetadata" class="form-control ci-code" rows="5" placeholder='{"target":"_blank","note":"Shown in footer"}'></textarea>
            <div class="d-flex align-items-center justify-content-between mt-2">
              <div class="ci-help">Tip: Use JSON if you want extra flags (e.g., <code>target</code>, <code>group</code> etc.).</div>
              <button type="button" id="ciBtnFormatJson" class="btn btn-light btn-sm">
                <i class="fa fa-wand-magic-sparkles me-1"></i>Format JSON
              </button>
            </div>
          </div>

          <div class="col-12">
            <div class="d-flex align-items-center justify-content-between border rounded-3 p-2" style="border-color:var(--line-soft) !important;">
              <div class="ci-small ci-muted">
                <span class="me-2"><i class="fa fa-link me-1"></i>Action URL:</span>
                <span id="ciActionUrlText">—</span>
              </div>
              <button type="button" id="ciBtnOpenLink" class="btn btn-outline-primary btn-sm" style="display:none;">
                <i class="fa fa-up-right-from-square me-1"></i>Open
              </button>
            </div>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="ciSaveBtn" type="submit">
          <i class="fa fa-floppy-disk me-1"></i>Save
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
  if (window.__CONTACT_INFO_MODULE_INIT__) return;
  window.__CONTACT_INFO_MODULE_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=320) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  function safeIconClass(cls){
    return (cls || '').toString().trim().replace(/[^a-z0-9\-\s_]/gi,'');
  }

  function badgeType(type){
    const t = (type || '').toString().toLowerCase();
    if (t === 'social') return `<span class="badge badge-soft-primary">Social</span>`;
    return `<span class="badge badge-soft-muted">Contact</span>`;
  }

  function badgeFeatured(v){
    return v ? `<span class="badge badge-soft-primary">Yes</span>` : `<span class="badge badge-soft-muted">No</span>`;
  }

  async function fetchWithTimeout(url, opts={}, ms=15000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try { return await fetch(url, { ...opts, signal: ctrl.signal }); }
    finally { clearTimeout(t); }
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const loadingEl = $('ciLoading');
    const showLoading = (v) => { if (loadingEl) loadingEl.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('ciToastOk');
    const toastErrEl = $('ciToastErr');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { const el=$('ciToastOkText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('ciToastErrText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = (json=false) => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json',
      ...(json ? { 'Content-Type': 'application/json' } : {})
    });

    // permissions
    const ACTOR = { role: '' };
    let canCreate=false, canEdit=false, canDelete=false;

    function computePermissions(){
      const r = (ACTOR.role || '').toLowerCase();
      const createDeleteRoles = ['admin','super_admin','director','principal'];
      const writeRoles = ['admin','super_admin','director','principal','hod','faculty','technical_assistant','it_person'];
      canCreate = createDeleteRoles.includes(r);
      canDelete = createDeleteRoles.includes(r);
      canEdit   = writeRoles.includes(r);
      const wc = $('ciWriteControls');
      if (wc) wc.style.display = canCreate ? '' : 'none';
    }

    async function fetchMe(){
      try{
        const meRes = await fetchWithTimeout('/api/users/me', { headers: authHeaders(false) }, 8000);
        if (meRes.ok){
          const js = await meRes.json().catch(()=> ({}));
          const role = js?.data?.role || js?.role;
          if (role) ACTOR.role = String(role).toLowerCase();
        }
      }catch(_){}
      if (!ACTOR.role){
        ACTOR.role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
      }
      computePermissions();
    }

    // elements
    const perPageSel = $('ciPerPage');
    const searchInput = $('ciSearch');
    const btnReset = $('ciBtnReset');
    const btnApplyFilters = $('ciBtnApplyFilters');

    const modalType = $('ciModalType');
    const modalKey = $('ciModalKey');
    const modalFeatured = $('ciModalFeatured');
    const modalSort = $('ciModalSort');
    const filterModalEl = $('ciFilterModal');
    const filterModal = filterModalEl ? new bootstrap.Modal(filterModalEl) : null;

    const tbodyA = $('ciTbodyActive');
    const tbodyI = $('ciTbodyInactive');
    const tbodyT = $('ciTbodyTrash');

    const emptyA = $('ciEmptyActive');
    const emptyI = $('ciEmptyInactive');
    const emptyT = $('ciEmptyTrash');

    const pagerA = $('ciPagerActive');
    const pagerI = $('ciPagerInactive');
    const pagerT = $('ciPagerTrash');

    const infoA = $('ciInfoActive');
    const infoI = $('ciInfoInactive');
    const infoT = $('ciInfoTrash');

    // item modal
    const itemModalEl = $('ciItemModal');
    const itemModal = itemModalEl ? new bootstrap.Modal(itemModalEl) : null;
    const itemModalTitle = $('ciItemModalTitle');
    const itemForm = $('ciItemForm');
    const saveBtn = $('ciSaveBtn');

    const ciUuid = $('ciUuid');
    const ciType = $('ciType');
    const ciStatus = $('ciStatus');
    const ciKey = $('ciKey');
    const ciName = $('ciName');
    const ciValue = $('ciValue');
    const ciSortOrder = $('ciSortOrder');
    const ciIconClass = $('ciIconClass');
    const ciFeatured = $('ciFeatured');
    const ciMetadata = $('ciMetadata');

    const iconPreview = $('ciIconPreview');
    const btnFormatJson = $('ciBtnFormatJson');

    const actionUrlText = $('ciActionUrlText');
    const btnOpenLink = $('ciBtnOpenLink');

    // state
    const state = {
      perPage: parseInt(perPageSel?.value || '20', 10) || 20,
      filters: { q:'', type:'', key:'', featured:'', sort:'-updated_at' },
      tabs: {
        active:   { page: 1, lastPage: 1, items: [] },
        inactive: { page: 1, lastPage: 1, items: [] },
        trash:    { page: 1, lastPage: 1, items: [] },
      }
    };

    const getTabKey = () => {
      const a = document.querySelector('.ci-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#ciTabActive';
      if (href === '#ciTabInactive') return 'inactive';
      if (href === '#ciTabTrash') return 'trash';
      return 'active';
    };

    function buildUrl(tabKey){
      const params = new URLSearchParams();
      params.set('per_page', String(state.perPage));
      params.set('page', String(state.tabs[tabKey].page));

      const q = (state.filters.q || '').trim();
      if (q) params.set('q', q);

      if (state.filters.type) params.set('type', state.filters.type);
      if (state.filters.key) params.set('key', state.filters.key);
      if (state.filters.featured !== '') params.set('featured', state.filters.featured);

      const s = state.filters.sort || '-updated_at';
      params.set('sort', s.startsWith('-') ? s.slice(1) : s);
      params.set('direction', s.startsWith('-') ? 'desc' : 'asc');

      if (tabKey === 'active') params.set('status', 'active');
      if (tabKey === 'inactive') params.set('status', 'inactive');
      if (tabKey === 'trash') params.set('only_trashed', '1');

      return `/api/contact-info?${params.toString()}`;
    }

    function setEmpty(tabKey, show){
      const el = tabKey==='active' ? emptyA : (tabKey==='inactive' ? emptyI : emptyT);
      if (el) el.style.display = show ? '' : 'none';
    }

    function renderPager(tabKey){
      const pager = tabKey==='active' ? pagerA : (tabKey==='inactive' ? pagerI : pagerT);
      if (!pager) return;

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

      pager.innerHTML = html;
    }

    // ✅ FIX: action dropdown toggler rendered WITHOUT data-bs-toggle
    // We will manually control it with Popper "fixed" strategy so it won't get clipped by table scroll.
    function rowActions(tabKey){
      let html = `
        <div class="dropdown text-end">
          <button type="button" class="btn btn-light btn-sm ci-dd-toggle"
            aria-expanded="false" title="Actions">
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button type="button" class="dropdown-item" data-action="view"><i class="fa fa-eye"></i> View</button></li>`;

      if (tabKey !== 'trash' && canEdit){
        html += `<li><button type="button" class="dropdown-item" data-action="edit"><i class="fa fa-pen-to-square"></i> Edit</button></li>`;
      }

      if (tabKey !== 'trash'){
        html += `<li><button type="button" class="dropdown-item" data-action="toggleFeatured"><i class="fa fa-star"></i> Toggle Featured</button></li>`;
        if (canDelete){
          html += `<li><hr class="dropdown-divider"></li>
                   <li><button type="button" class="dropdown-item text-danger" data-action="delete"><i class="fa fa-trash"></i> Delete</button></li>`;
        }
      } else {
        html += `<li><hr class="dropdown-divider"></li>
                 <li><button type="button" class="dropdown-item" data-action="restore"><i class="fa fa-rotate-left"></i> Restore</button></li>`;
        if (canDelete){
          html += `<li><button type="button" class="dropdown-item text-danger" data-action="force"><i class="fa fa-skull-crossbones"></i> Delete Permanently</button></li>`;
        }
      }

      html += `</ul></div>`;
      return html;
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
          const name = r.name || '—';
          const type = r.type || 'contact';
          const key = r.key || '—';
          const deleted = r.deleted_at || '—';
          const sortOrder = r.sort_order ?? 0;

          return `
            <tr data-uuid="${esc(uuid)}">
              <td class="fw-semibold">${esc(name)}</td>
              <td>${badgeType(type)}</td>
              <td><code>${esc(key)}</code></td>
              <td>${esc(String(deleted))}</td>
              <td>${esc(String(sortOrder))}</td>
              <td class="text-end">${rowActions(tabKey)}</td>
            </tr>
          `;
        }).join('');
        renderPager(tabKey);
        return;
      }

      tbody.innerHTML = rows.map(r => {
        const uuid = r.uuid || '';
        const name = r.name || '—';
        const type = r.type || 'contact';
        const key = r.key || '—';
        const value = r.value || '—';
        const iconClass = safeIconClass(r.icon_class || '');
        const featured = !!(r.is_featured_home ?? 0);
        const sortOrder = r.sort_order ?? 0;
        const updated = r.updated_at || '—';
        const actionUrl = r.action_url || '';

        const iconHtml = iconClass
          ? `<span class="ci-icon-preview" title="${esc(iconClass)}"><i class="${esc(iconClass)}"></i></span>`
          : `<span class="ci-icon-preview" title="No icon"><i class="fa-regular fa-circle"></i></span>`;

        const valueHtml = actionUrl
          ? `<div class="fw-semibold">${esc(value)}</div>
             <div class="ci-small ci-muted">
               <i class="fa fa-link me-1"></i>
               <a href="${esc(actionUrl)}" target="_blank" rel="noopener" class="text-decoration-underline">${esc(actionUrl)}</a>
             </div>`
          : `<div class="fw-semibold">${esc(value)}</div>`;

        return `
          <tr data-uuid="${esc(uuid)}">
            <td>${iconHtml}</td>
            <td class="fw-semibold">${esc(name)}</td>
            <td>${badgeType(type)}</td>
            <td><code>${esc(key)}</code></td>
            <td>${valueHtml}</td>
            <td>${badgeFeatured(featured)}</td>
            <td>${esc(String(sortOrder))}</td>
            <td>${esc(String(updated))}</td>
            <td class="text-end">${rowActions(tabKey)}</td>
          </tr>
        `;
      }).join('');

      renderPager(tabKey);
    }

    async function loadTab(tabKey){
      const tbody = tabKey==='active' ? tbodyA : (tabKey==='inactive' ? tbodyI : tbodyT);
      if (tbody){
        const cols = tabKey==='trash' ? 6 : 9;
        tbody.innerHTML = `<tr><td colspan="${cols}" class="text-center ci-muted" style="padding:38px;">Loading…</td></tr>`;
      }

      try{
        const res = await fetchWithTimeout(buildUrl(tabKey), { headers: authHeaders(false) }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(js?.message || 'Failed to load');

        const items = Array.isArray(js.data) ? js.data : [];
        const p = js.pagination || {};
        state.tabs[tabKey].items = items;
        state.tabs[tabKey].lastPage = parseInt(p.last_page || 1, 10) || 1;

        const infoTxt = p.total ? `${p.total} result(s)` : '—';
        if (tabKey==='active' && infoA) infoA.textContent = infoTxt;
        if (tabKey==='inactive' && infoI) infoI.textContent = infoTxt;
        if (tabKey==='trash' && infoT) infoT.textContent = infoTxt;

        renderTable(tabKey);
      }catch(e){
        state.tabs[tabKey].items = [];
        state.tabs[tabKey].lastPage = 1;
        renderTable(tabKey);
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    function reloadCurrent(){ loadTab(getTabKey()); }

    // pager click
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

    // filters
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

    filterModalEl?.addEventListener('show.bs.modal', () => {
      if (modalType) modalType.value = state.filters.type || '';
      if (modalKey) modalKey.value = state.filters.key || '';
      if (modalFeatured) modalFeatured.value = (state.filters.featured ?? '');
      if (modalSort) modalSort.value = state.filters.sort || '-updated_at';
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.type = modalType?.value || '';
      state.filters.key = (modalKey?.value || '').trim();
      state.filters.featured = (modalFeatured?.value ?? '');
      state.filters.sort = modalSort?.value || '-updated_at';
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      filterModal && filterModal.hide();
      reloadCurrent();
    });

    btnReset?.addEventListener('click', () => {
      state.perPage = 20;
      state.filters = { q:'', type:'', key:'', featured:'', sort:'-updated_at' };
      if (perPageSel) perPageSel.value = '20';
      if (searchInput) searchInput.value = '';
      if (modalType) modalType.value = '';
      if (modalKey) modalKey.value = '';
      if (modalFeatured) modalFeatured.value = '';
      if (modalSort) modalSort.value = '-updated_at';
      state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
      reloadCurrent();
    });

    // tabs load
    document.querySelector('a[href="#ciTabActive"]')?.addEventListener('shown.bs.tab', () => loadTab('active'));
    document.querySelector('a[href="#ciTabInactive"]')?.addEventListener('shown.bs.tab', () => loadTab('inactive'));
    document.querySelector('a[href="#ciTabTrash"]')?.addEventListener('shown.bs.tab', () => loadTab('trash'));

    // ---------- ✅ ACTION DROPDOWN FIX (the requested fix) ----------
    // Many admin tables are inside overflow containers; dropdown can "open" but be clipped / look like not opening.
    // We manually toggle Bootstrap Dropdown with Popper strategy "fixed" so it renders above scroll/overflow properly.
    function closeAllDropdownsExcept(exceptToggle){
      document.querySelectorAll('.ci-dd-toggle').forEach(t => {
        if (t === exceptToggle) return;
        try{
          const inst = bootstrap.Dropdown.getInstance(t);
          inst && inst.hide();
        }catch(_){}
      });
    }

    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('.ci-dd-toggle');
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
            // ensure viewport boundary + fixed positioning (escapes overflow clipping)
            mods.push({ name:'preventOverflow', options:{ boundary:'viewport', padding:8 } });
            mods.push({ name:'flip', options:{ boundary:'viewport', padding:8 } });
            return { ...base, strategy:'fixed', modifiers: mods };
          }
        });
        inst.toggle();
      }catch(_){
        // fallback: try native data-api if any
      }
    });

    // allow clicking elsewhere to close dropdowns
    document.addEventListener('click', () => {
      closeAllDropdownsExcept(null);
    }, { capture: true });

    // ---------- item modal ----------
    let saving = false;

    function setViewMode(viewOnly){
      [ciType,ciStatus,ciKey,ciName,ciValue,ciSortOrder,ciIconClass,ciFeatured,ciMetadata].forEach(el => {
        if (!el) return;
        if (viewOnly) el.setAttribute('disabled','disabled');
        else el.removeAttribute('disabled');
      });
      if (saveBtn) saveBtn.style.display = viewOnly ? 'none' : '';
      if (btnFormatJson) btnFormatJson.style.display = viewOnly ? 'none' : '';
    }

    function resetForm(){
      itemForm?.reset();
      $('ciUuid').value = '';
      $('ciId').value = '';
      if (iconPreview) iconPreview.innerHTML = `<i class="fa-solid fa-icons"></i>`;
      if (actionUrlText) actionUrlText.textContent = '—';
      if (btnOpenLink){ btnOpenLink.style.display = 'none'; btnOpenLink.onclick = null; }
      setViewMode(false);
      itemForm.dataset.intent = 'create';
      itemForm.dataset.mode = 'edit';
    }

    function fillForm(row, viewOnly=false){
      $('ciUuid').value = row.uuid || '';
      $('ciId').value = row.id || '';
      ciType.value = (row.type || 'contact');
      ciStatus.value = (row.status || 'active');
      ciKey.value = row.key || '';
      ciName.value = row.name || '';
      ciValue.value = row.value || '';
      ciSortOrder.value = String(row.sort_order ?? 0);
      ciIconClass.value = row.icon_class || '';
      ciFeatured.value = String((row.is_featured_home ?? 0) ? 1 : 0);

      let meta = row.metadata ?? '';
      if (typeof meta === 'object' && meta !== null){
        try{ meta = JSON.stringify(meta, null, 2); }catch(_){}
      }
      if (typeof meta === 'string'){
        const trimmed = meta.trim();
        if (trimmed){
          try{ meta = JSON.stringify(JSON.parse(trimmed), null, 2); }catch(_){}
        }
      }
      ciMetadata.value = meta || '';

      const cls = safeIconClass(ciIconClass.value);
      if (iconPreview){
        iconPreview.innerHTML = cls ? `<i class="${esc(cls)}"></i>` : `<i class="fa-solid fa-icons"></i>`;
      }

      const aurl = row.action_url || '';
      if (actionUrlText) actionUrlText.textContent = aurl ? aurl : '—';
      if (btnOpenLink){
        if (aurl){
          btnOpenLink.style.display = '';
          btnOpenLink.onclick = () => window.open(aurl, '_blank', 'noopener');
        } else {
          btnOpenLink.style.display = 'none';
          btnOpenLink.onclick = null;
        }
      }

      setViewMode(viewOnly);
      itemForm.dataset.intent = viewOnly ? 'view' : 'edit';
      itemForm.dataset.mode = viewOnly ? 'view' : 'edit';
    }

    function findRow(uuid){
      const all = [
        ...(state.tabs.active.items || []),
        ...(state.tabs.inactive.items || []),
        ...(state.tabs.trash.items || []),
      ];
      return all.find(x => x?.uuid === uuid) || null;
    }

    // icon live preview
    ciIconClass?.addEventListener('input', debounce(() => {
      const cls = safeIconClass(ciIconClass.value);
      if (iconPreview){
        iconPreview.innerHTML = cls ? `<i class="${esc(cls)}"></i>` : `<i class="fa-solid fa-icons"></i>`;
      }
    }, 120));

    // format json
    btnFormatJson?.addEventListener('click', () => {
      const raw = (ciMetadata.value || '').trim();
      if (!raw) return;
      try{
        const parsed = JSON.parse(raw);
        ciMetadata.value = JSON.stringify(parsed, null, 2);
        ok('JSON formatted');
      }catch(_){
        err('Invalid JSON');
      }
    });

    // add
    $('ciBtnAdd')?.addEventListener('click', () => {
      if (!canCreate) return;
      resetForm();
      if (itemModalTitle) itemModalTitle.textContent = 'Add Contact Info';
      itemForm.dataset.intent = 'create';
      itemModal && itemModal.show();
    });

    // row actions (dropdown menu items)
    document.addEventListener('click', async (e) => {
      const actionBtn = e.target.closest('button[data-action]');
      if (!actionBtn) return;

      const tr = actionBtn.closest('tr');
      const uuid = tr?.dataset?.uuid || '';
      const act = actionBtn.dataset.action || '';
      if (!uuid) return;

      const row = findRow(uuid) || {};

      // close current dropdown (if any)
      const toggle = actionBtn.closest('.dropdown')?.querySelector('.ci-dd-toggle');
      if (toggle){
        try{ bootstrap.Dropdown.getInstance(toggle)?.hide(); }catch(_){}
      }

      if (act === 'view' || act === 'edit'){
        if (act === 'edit' && !canEdit) return;

        resetForm();
        if (itemModalTitle) itemModalTitle.textContent = act === 'view' ? 'View Contact Info' : 'Edit Contact Info';

        // try fresh fetch, fallback to row
        try{
          const res = await fetchWithTimeout(`/api/contact-info/${encodeURIComponent(uuid)}`, { headers: authHeaders(false) }, 12000);
          const js = await res.json().catch(()=> ({}));
          const data = js?.item || js?.data || js?.data?.item || null;
          fillForm(data || row, act === 'view');
        }catch(_){
          fillForm(row, act === 'view');
        }

        itemModal && itemModal.show();
        return;
      }

      if (act === 'toggleFeatured'){
        if (!canEdit) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/contact-info/${encodeURIComponent(uuid)}/toggle-featured`, {
            method: 'POST',
            headers: authHeaders(false)
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
          const res = await fetchWithTimeout(`/api/contact-info/${encodeURIComponent(uuid)}`, {
            method: 'DELETE',
            headers: authHeaders(false)
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
          title: 'Restore this item?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Restore'
        });
        if (!conf.isConfirmed) return;

        showLoading(true);
        try{
          const res = await fetchWithTimeout(`/api/contact-info/${encodeURIComponent(uuid)}/restore`, {
            method: 'POST',
            headers: authHeaders(false)
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
          const res = await fetchWithTimeout(`/api/contact-info/${encodeURIComponent(uuid)}/force`, {
            method: 'DELETE',
            headers: authHeaders(false)
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

    // submit
    itemForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (saving) return;
      saving = true;

      try{
        const intent = itemForm.dataset.intent || 'create';
        const isEdit = intent === 'edit' && !!ciUuid.value;

        if (isEdit && !canEdit) return;
        if (!isEdit && !canCreate) return;

        const payload = {
          type: (ciType.value || 'contact'),
          status: (ciStatus.value || 'active'),
          key: (ciKey.value || '').trim(),
          name: (ciName.value || '').trim(),
          value: (ciValue.value || '').trim(),
          icon_class: (ciIconClass.value || '').trim() || null,
          is_featured_home: (ciFeatured.value === '1') ? 1 : 0,
          sort_order: parseInt(ciSortOrder.value || '0', 10) || 0,
          metadata: null
        };

        if (!payload.key) { err('Key is required'); ciKey.focus(); return; }
        if (!payload.name) { err('Display Name is required'); ciName.focus(); return; }
        if (!payload.value) { err('Value is required'); ciValue.focus(); return; }

        const metaRaw = (ciMetadata.value || '').trim();
        if (metaRaw){
          try { payload.metadata = JSON.parse(metaRaw); }
          catch(_) { payload.metadata = metaRaw; }
        }

        const url = isEdit
          ? `/api/contact-info/${encodeURIComponent(ciUuid.value)}`
          : `/api/contact-info`;

        showLoading(true);

        const res = await fetchWithTimeout(url, {
          method: isEdit ? 'PATCH' : 'POST',
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

        ok(isEdit ? 'Updated' : 'Created');
        itemModal && itemModal.hide();

        state.tabs.active.page = state.tabs.inactive.page = state.tabs.trash.page = 1;
        await Promise.all([loadTab('active'), loadTab('inactive'), loadTab('trash')]);
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        saving = false;
        showLoading(false);
      }
    });

    // init
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
