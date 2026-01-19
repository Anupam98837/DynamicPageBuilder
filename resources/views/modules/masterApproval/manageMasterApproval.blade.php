{{-- resources/views/modules/masterApproval/manageMasterApprovals.blade.php --}}
@section('title','Master Approval')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
  /* =========================
   * Master Approval - Admin UI
   * (Same theme system as Achievements reference)
   * ========================= */

  /* Tabs */
  .map-tabs.nav-tabs{border-color:var(--line-strong)}
  .map-tabs .nav-link{color:var(--ink)}
  .map-tabs .nav-link.active{
    background:var(--surface);
    border-color:var(--line-strong) var(--line-strong) var(--surface);
  }

  /* Card/Table */
  .map-card{
    border:1px solid var(--line-strong);
    border-radius:16px;
    background:var(--surface);
    box-shadow:var(--shadow-2);
    overflow:visible;
  }
  .map-card .card-body{overflow:visible}
  .map-table{--bs-table-bg:transparent}
  .map-table thead th{
    font-weight:650;
    color:var(--muted-color);
    font-size:13px;
    border-bottom:1px solid var(--line-strong);
    background:var(--surface);
  }
  .map-table thead.sticky-top{z-index:3}
  .map-table tbody tr{border-top:1px solid var(--line-soft)}
  .map-table tbody tr:hover{background:var(--page-hover)}
  .map-muted{color:var(--muted-color)}
  .map-small{font-size:12.5px}

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
  .table-responsive > table{width:max-content; min-width:1280px;}
  .table-responsive th,.table-responsive td{white-space:nowrap;}

  /* Dropdown - keep high z-index */
  .table-responsive .dropdown{position:relative}
  .map-dd-toggle{border-radius:10px}
  .dropdown-menu{
    border-radius:12px;
    border:1px solid var(--line-strong);
    box-shadow:var(--shadow-2);
    min-width:240px;
    z-index:99999;
  }
  .dropdown-menu.show{display:block !important}
  .dropdown-item{display:flex;align-items:center;gap:.6rem}
  .dropdown-item i{width:16px;text-align:center}
  .dropdown-item.text-danger{color:var(--danger-color) !important}

  /* Soft badges */
  .badge-soft{
    display:inline-flex;align-items:center;gap:6px;
    padding:.35rem .55rem;border-radius:999px;font-size:12px;font-weight:600
  }
  .badge-soft-primary{
    background:color-mix(in oklab, var(--primary-color) 12%, transparent);
    color:var(--primary-color)
  }
  .badge-soft-success{
    background:color-mix(in oklab, var(--success-color, #16a34a) 12%, transparent);
    color:var(--success-color, #16a34a)
  }
  .badge-soft-warning{
    background:color-mix(in oklab, var(--warning-color, #f59e0b) 14%, transparent);
    color:var(--warning-color, #f59e0b)
  }
  .badge-soft-muted{
    background:color-mix(in oklab, var(--muted-color) 10%, transparent);
    color:var(--muted-color)
  }
  .badge-soft-danger{
    background:color-mix(in oklab, var(--danger-color) 14%, transparent);
    color:var(--danger-color)
  }

  /* Loading overlay */
  .map-loading{
    position:fixed; inset:0;
    background:rgba(0,0,0,.45);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:9999;
    backdrop-filter:blur(2px);
  }
  .map-loading .box{
    background:var(--surface);
    padding:18px 20px;
    border-radius:14px;
    display:flex;
    align-items:center;
    gap:12px;
    box-shadow:0 10px 26px rgba(0,0,0,.3);
  }
  .map-spin{
    width:38px;height:38px;border-radius:50%;
    border:4px solid rgba(148,163,184,.3);
    border-top:4px solid var(--primary-color);
    animation:mapSpin 1s linear infinite;
  }
  @keyframes mapSpin{to{transform:rotate(360deg)}}

  /* Toolbar */
  .map-toolbar{
    border:1px solid var(--line-strong);
    border-radius:16px;
    background:var(--surface);
    box-shadow:var(--shadow-2);
    padding:12px 12px;
  }
  .map-toolbar .map-search{min-width:280px; position:relative;}
  .map-toolbar .map-search input{padding-left:40px;}
  .map-toolbar .map-search i{
    position:absolute; left:12px; top:50%;
    transform:translateY(-50%); opacity:.6;
  }
  @media (max-width: 768px){
    .map-toolbar .map-row{flex-direction:column; align-items:stretch !important;}
    .map-toolbar .map-search{min-width:100%;}
    .map-toolbar .map-actions{display:flex; gap:8px; flex-wrap:wrap;}
    .map-toolbar .map-actions .btn{flex:1; min-width:140px;}
  }

  /* View modal payload preview */
  .map-json{
    border:1px solid var(--line-strong);
    border-radius:14px;
    background:color-mix(in oklab, var(--surface) 92%, transparent);
    padding:12px;
    font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size:12.5px;
    line-height:1.45;
    white-space:pre-wrap;
    word-break:break-word;
    max-height:360px;
    overflow:auto;
  }
</style>
@endpush

@section('content')
<div class="crs-wrap">

  {{-- Global Loading --}}
  <div id="mapLoading" class="map-loading" aria-hidden="true">
    <div class="box">
      <div class="map-spin"></div>
      <div class="map-small">Loading…</div>
    </div>
  </div>

  {{-- Top Toolbar --}}
  <div class="map-toolbar mb-3">
    <div class="d-flex align-items-center justify-content-between gap-2 map-row">
      <div class="d-flex align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <label class="map-small map-muted mb-0">Per Page</label>
          <select id="mapPerPage" class="form-select" style="width:96px;">
            <option>10</option>
            <option selected>20</option>
            <option>50</option>
            <option>100</option>
          </select>
        </div>

        <div class="map-search">
          <i class="fa fa-search"></i>
          <input id="mapSearch" type="search" class="form-control" placeholder="Search by title / module / department / user…">
        </div>

        <button id="mapBtnFilter" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#mapFilterModal">
          <i class="fa fa-sliders me-1"></i>Filter
        </button>

        <button id="mapBtnReset" class="btn btn-light">
          <i class="fa fa-rotate-left me-1"></i>Reset
        </button>
      </div>

      <div class="map-actions">
        <span class="badge-soft badge-soft-muted">
          <i class="fa fa-shield"></i> Master Approval
        </span>
      </div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs map-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#mapTabPending" role="tab" aria-selected="true">
        <i class="fa-solid fa-clock me-2"></i>Pending
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#mapTabApproved" role="tab" aria-selected="false">
        <i class="fa-solid fa-circle-check me-2"></i>Approved
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#mapTabRejected" role="tab" aria-selected="false">
        <i class="fa-solid fa-circle-xmark me-2"></i>Rejected
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#mapTabAll" role="tab" aria-selected="false">
        <i class="fa-solid fa-layer-group me-2"></i>All
      </a>
    </li>
  </ul>

  <div class="tab-content">

    {{-- PENDING --}}
    <div class="tab-pane fade show active" id="mapTabPending" role="tabpanel">
      <div class="card map-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table map-table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:180px;">Module</th>
                  <th style="width:360px;">Title</th>
                  <th style="width:220px;">Department</th>
                  <th style="width:220px;">Requested By</th>
                  <th style="width:220px;">Requested At</th>
                  <th style="width:140px;">Status</th>
                  <th style="width:140px;">Featured</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="mapTbodyPending">
                <tr><td colspan="8" class="text-center map-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="mapEmptyPending" class="p-4 text-center" style="display:none;">
            <i class="fa fa-clock mb-2" style="font-size:32px;opacity:.6;"></i>
            <div class="map-muted">No pending approvals.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="map-small map-muted" id="mapInfoPending">—</div>
            <nav><ul id="mapPagerPending" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- APPROVED --}}
    <div class="tab-pane fade" id="mapTabApproved" role="tabpanel">
      <div class="card map-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table map-table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:180px;">Module</th>
                  <th style="width:360px;">Title</th>
                  <th style="width:220px;">Department</th>
                  <th style="width:220px;">Approved By</th>
                  <th style="width:220px;">Approved At</th>
                  <th style="width:140px;">Status</th>
                  <th style="width:140px;">Featured</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="mapTbodyApproved">
                <tr><td colspan="8" class="text-center map-muted" style="padding:38px;">Click Approved tab to load…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="mapEmptyApproved" class="p-4 text-center" style="display:none;">
            <i class="fa fa-circle-check mb-2" style="font-size:32px;opacity:.6;"></i>
            <div class="map-muted">No approved items.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="map-small map-muted" id="mapInfoApproved">—</div>
            <nav><ul id="mapPagerApproved" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- REJECTED --}}
    <div class="tab-pane fade" id="mapTabRejected" role="tabpanel">
      <div class="card map-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table map-table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:180px;">Module</th>
                  <th style="width:360px;">Title</th>
                  <th style="width:220px;">Department</th>
                  <th style="width:220px;">Rejected By</th>
                  <th style="width:220px;">Rejected At</th>
                  <th style="width:140px;">Status</th>
                  <th style="width:140px;">Featured</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="mapTbodyRejected">
                <tr><td colspan="8" class="text-center map-muted" style="padding:38px;">Click Rejected tab to load…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="mapEmptyRejected" class="p-4 text-center" style="display:none;">
            <i class="fa fa-circle-xmark mb-2" style="font-size:32px;opacity:.6;"></i>
            <div class="map-muted">No rejected items.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="map-small map-muted" id="mapInfoRejected">—</div>
            <nav><ul id="mapPagerRejected" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ALL --}}
    <div class="tab-pane fade" id="mapTabAll" role="tabpanel">
      <div class="card map-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table map-table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th style="width:180px;">Module</th>
                  <th style="width:360px;">Title</th>
                  <th style="width:220px;">Department</th>
                  <th style="width:220px;">Actor</th>
                  <th style="width:220px;">Updated At</th>
                  <th style="width:140px;">Status</th>
                  <th style="width:140px;">Featured</th>
                  <th style="width:108px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="mapTbodyAll">
                <tr><td colspan="8" class="text-center map-muted" style="padding:38px;">Click All tab to load…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="mapEmptyAll" class="p-4 text-center" style="display:none;">
            <i class="fa fa-layer-group mb-2" style="font-size:32px;opacity:.6;"></i>
            <div class="map-muted">No records found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="map-small map-muted" id="mapInfoAll">—</div>
            <nav><ul id="mapPagerAll" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="mapFilterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-sliders me-2"></i>Filter Approvals</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">

          <div class="col-12">
            <label class="form-label">Department</label>
            <select id="mapModalDept" class="form-select">
              <option value="">All</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Module</label>
            <select id="mapModalModule" class="form-select">
              <option value="">All</option>
              <option value="achievements">Achievements</option>
              <option value="announcements">Announcements</option>
              <option value="notices">Notices</option>
              <option value="student_activities">Student Activities</option>
              <option value="placement_notices">Placement Notice</option>
              <option value="scholarships">Scholarships</option>
              <option value="why_us">Why MSIT</option>
              <option value="career_notices">Career At MSIT</option>
            </select>
            <div class="form-text map-small map-muted">If your API uses different module keys, just update these values.</div>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Featured</label>
            <select id="mapModalFeatured" class="form-select">
              <option value="">Any</option>
              <option value="1">Featured only</option>
              <option value="0">Not featured</option>
            </select>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Sort</label>
            <select id="mapModalSort" class="form-select">
              <option value="created_at">Created At</option>
              <option value="updated_at">Updated At</option>
              <option value="title">Title</option>
              <option value="module">Module</option>
              <option value="id">ID</option>
            </select>
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label">Direction</label>
            <select id="mapModalDir" class="form-select">
              <option value="desc">Desc</option>
              <option value="asc">Asc</option>
            </select>
          </div>

          <div class="col-12">
            <div class="form-text">
              Tabs control the approval status. Filters apply to every tab.
            </div>
          </div>

        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="mapBtnApplyFilters" type="button">
          <i class="fa fa-check me-1"></i>Apply
        </button>
      </div>
    </div>
  </div>
</div>

{{-- View Modal --}}
<div class="modal fade" id="mapViewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content" id="mapViewModalContent">
      <div class="modal-header">
        <h5 class="modal-title" id="mapViewTitle">Approval Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Module</label>
            <div class="form-control" id="mapViewModule" readonly></div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Department</label>
            <div class="form-control" id="mapViewDept" readonly></div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <div class="form-control" id="mapViewStatus" readonly></div>
          </div>

          <div class="col-12">
            <label class="form-label">Title</label>
            <div class="form-control" id="mapViewItemTitle" readonly></div>
          </div>

          <div class="col-12">
            <label class="form-label">Payload / Data (JSON)</label>
            <div class="map-json" id="mapViewPayload">{}</div>
            <div class="form-text">This shows what the API returns for approval review.</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="mapToastOk" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="mapToastOkText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="mapToastErr" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="mapToastErrText">Something went wrong</div>
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
  if (window.__MASTER_APPROVAL_PAGE_INIT__) return;
  window.__MASTER_APPROVAL_PAGE_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=320) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  const esc = (str) => (str ?? '').toString().replace(/[&<>"']/g, s => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[s]));

  const num = (v, d=0) => {
    const n = parseInt(String(v ?? ''), 10);
    return Number.isFinite(n) ? n : d;
  };

  async function fetchWithTimeout(url, opts={}, ms=15000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try { return await fetch(url, { ...opts, signal: ctrl.signal }); }
    finally { clearTimeout(t); }
  }

  function safeString(v){
    return (v === null || v === undefined) ? '' : String(v);
  }

  /* =========================================================
    ✅ FIX A (MAIN): Approve/Reject permission must come from
    YOUR /api/master-approval overview response (actor.role)
    Because /api/users/me may not exist in your project.
  ========================================================= */
  function normalizeListResponse(js, fallbackPage=1, fallbackPer=20){
    // overview shapes (your API)
    const tabs = js?.tabs || {};
    const pendingItems  = (Array.isArray(tabs?.not_approved?.items) && tabs.not_approved.items) || [];
    const approvedItems = (Array.isArray(tabs?.approved?.items) && tabs.approved.items) || [];
    const rejectedItems = (Array.isArray(tabs?.rejected?.items) && tabs.rejected.items) || [];
    const requestsItems = (Array.isArray(js?.requests?.items) && js.requests.items) || [];

    // union list (de-duped)
    let items = [...pendingItems, ...approvedItems, ...rejectedItems, ...requestsItems];

    const seen = new Set();
    items = items.filter(it => {
      const k = (it?.uuid || it?.id || '') + '|' + (it?.division?.key || '');
      if (seen.has(k)) return false;
      seen.add(k);
      return true;
    });

    // pagination (client-side default)
    const page = num(fallbackPage, 1);
    const per_page = num(fallbackPer, 20);
    const total = items.length;
    const last_page = Math.max(1, Math.ceil((total || 1) / (per_page || 1)));

    return { items, pagination: { page, per_page, total, last_page } };
  }

  function pickModuleKey(r){
    return safeString(
      r?.division?.key ||
      r?.module ||
      r?.type ||
      r?.resource ||
      r?.entity ||
      r?.table_name ||
      r?.content_type ||
      r?.model ||
      ''
    );
  }
  function pickModuleLabel(r){
    const key = pickModuleKey(r);
    return safeString(r?.division?.label || key || '');
  }

  function pickUUID(r){
    return safeString(r?.uuid || r?.record?.uuid || r?.id || '');
  }

  function pickTitle(r){
    return safeString(
      r?.title ||
      r?.item_title ||
      r?.record_title ||
      r?.record?.title ||
      r?.payload?.title ||
      r?.payload?.name ||
      r?.data?.title ||
      ''
    );
  }

  function pickDept(r){
    return safeString(
      r?.department_title ||
      r?.department_name ||
      r?.department?.title ||
      r?.department?.name ||
      r?.record?.department_title ||
      r?.payload?.department_title ||
      ''
    );
  }

  function pickActor(r){
    return safeString(
      r?.creator_name ||
      r?.creator?.name ||
      r?.creator?.email ||
      r?.requested_by_name ||
      r?.requested_by?.name ||
      r?.actor_name ||
      r?.actor?.name ||
      r?.user_name ||
      r?.user?.name ||
      r?.approved_by_name ||
      r?.rejected_by_name ||
      ''
    );
  }

  function pickRequestedAt(r){
    return safeString(r?.requested_at || r?.created_at || r?.request_time || '');
  }

  function pickApprovedAt(r){
    return safeString(r?.approved_at || r?.approval_time || r?.updated_at || '');
  }

  function pickRejectedAt(r){
    return safeString(r?.rejected_at || r?.rejection_time || r?.updated_at || '');
  }

  function isFeatured(r){
    const v = r?.is_featured_home ?? r?.record?.is_featured_home ?? r?.payload?.is_featured_home ?? r?.data?.is_featured_home ?? 0;
    return ((+v) === 1) || v === true;
  }

  // ✅ status: supports your flag system (request_for_approval / is_approved)
  function approvalStatus(r){
    const s = safeString(r?.approval_status || r?.approvalState || r?.approval_state || r?.state).toLowerCase().trim();
    if (['pending','approved','rejected'].includes(s)) return s;

    const req = r?.request_for_approval ?? r?.record?.request_for_approval ?? r?.payload?.request_for_approval ?? 0;
    const ok  = r?.is_approved ?? r?.record?.is_approved ?? r?.payload?.is_approved ?? 0;

    if ((+ok) === 1) return 'approved';
    if ((+req) === 1 && (+ok) === 0) return 'pending';

    const rej = r?.is_rejected ?? r?.rejected ?? 0;
    if ((+rej) === 1) return 'rejected';

    return 'unknown';
  }

  function badgeStatus(st){
    if (st === 'approved'){
      return `<span class="badge-soft badge-soft-success"><i class="fa fa-circle-check"></i> Approved</span>`;
    }
    if (st === 'rejected'){
      return `<span class="badge-soft badge-soft-danger"><i class="fa fa-circle-xmark"></i> Rejected</span>`;
    }
    if (st === 'pending'){
      return `<span class="badge-soft badge-soft-warning"><i class="fa fa-clock"></i> Pending</span>`;
    }
    return `<span class="badge-soft badge-soft-muted"><i class="fa fa-circle-question"></i> ${esc(st)}</span>`;
  }

  function badgeFeatured(on){
    return on
      ? `<span class="badge-soft badge-soft-primary"><i class="fa fa-star"></i> Yes</span>`
      : `<span class="badge-soft badge-soft-muted"><i class="fa fa-star"></i> No</span>`;
  }

  function badgeDept(name){
    if (name){
      return `<span class="badge-soft badge-soft-primary"><i class="fa fa-building"></i> ${esc(name)}</span>`;
    }
    return `<span class="badge-soft badge-soft-muted"><i class="fa fa-globe"></i> Global</span>`;
  }

  function badgeModule(label){
    const t = (label || '').trim();
    if (!t) return `<span class="badge-soft badge-soft-muted"><i class="fa fa-layer-group"></i> Unknown</span>`;
    return `<span class="badge-soft badge-soft-primary"><i class="fa fa-cube"></i> ${esc(t)}</span>`;
  }

  function renderPager(pagerEl, tabKey, page, totalPages){
    if(!pagerEl) return;

    const item=(p,label,dis=false,act=false)=>{
      if(dis) return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
      if(act) return `<li class="page-item active"><span class="page-link">${label}</span></li>`;
      return `<li class="page-item"><a class="page-link" href="#" data-page="${p}" data-tab="${tabKey}">${label}</a></li>`;
    };

    let html='';
    html += item(Math.max(1,page-1),'Previous',page<=1);
    const st=Math.max(1,page-2), en=Math.min(totalPages,page+2);
    for(let p=st;p<=en;p++) html += item(p,p,false,p===page);
    html += item(Math.min(totalPages,page+1),'Next',page>=totalPages);
    pagerEl.innerHTML = html;
  }

  function infoText(p, shown){
    const total = num(p?.total, 0);
    const page = num(p?.page, 1);
    const per  = num(p?.per_page, 20);
    if (!total) return '0 result(s)';
    const from = (page-1)*per + 1;
    const to   = (page-1)*per + (shown||0);
    return `Showing ${from} to ${to} of ${total} entries`;
  }

  // cache rows so View/Approve/Reject always has the right uuid
  const ROW_CACHE = new Map();

  document.addEventListener('DOMContentLoaded', async () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const loadingEl = $('mapLoading');
    const showLoading = (v) => { if (loadingEl) loadingEl.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('mapToastOk');
    const toastErrEl = $('mapToastErr');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { const el=$('mapToastOkText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('mapToastErrText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = (withJson=false) => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...(withJson ? { 'Content-Type': 'application/json' } : {})
    });

    const API = {
      departments: '/api/departments',
      list: () => '/api/master-approval',
      approve: (uuid) => `/api/master-approval/${encodeURIComponent(uuid)}/approve`,
      reject:  (uuid) => `/api/master-approval/${encodeURIComponent(uuid)}/reject`,
    };

    // ✅ permission state (computed from overview actor.role)
    const ACTOR = { role: '' };
    let canApprove = false;

    function computePermissions(){
      const r = (ACTOR.role || '').toLowerCase();
      // keep strict for approve/reject (safe)
      const approveRoles = ['admin','super_admin','director','principal'];
      canApprove = approveRoles.includes(r);
    }

    const perPageSel = $('mapPerPage');
    const searchInput = $('mapSearch');
    const btnReset = $('mapBtnReset');
    const btnApplyFilters = $('mapBtnApplyFilters');

    const modalDept = $('mapModalDept');
    const modalModule = $('mapModalModule');
    const modalFeatured = $('mapModalFeatured');
    const modalSort = $('mapModalSort');
    const modalDir = $('mapModalDir');
    const filterModalEl = $('mapFilterModal');
    const filterModal = filterModalEl ? new bootstrap.Modal(filterModalEl) : null;

    const tbP = $('mapTbodyPending');
    const tbA = $('mapTbodyApproved');
    const tbR = $('mapTbodyRejected');
    const tbAll = $('mapTbodyAll');

    const emptyP = $('mapEmptyPending');
    const emptyA = $('mapEmptyApproved');
    const emptyR = $('mapEmptyRejected');
    const emptyAll = $('mapEmptyAll');

    const pagerP = $('mapPagerPending');
    const pagerA = $('mapPagerApproved');
    const pagerR = $('mapPagerRejected');
    const pagerAll = $('mapPagerAll');

    const infoP = $('mapInfoPending');
    const infoA = $('mapInfoApproved');
    const infoR = $('mapInfoRejected');
    const infoAll = $('mapInfoAll');

    const viewModalEl = $('mapViewModal');
    const viewModal = viewModalEl ? new bootstrap.Modal(viewModalEl) : null;
    const vTitle = $('mapViewTitle');
    const vModule = $('mapViewModule');
    const vDept = $('mapViewDept');
    const vStatus = $('mapViewStatus');
    const vItemTitle = $('mapViewItemTitle');
    const vPayload = $('mapViewPayload');

    async function loadDepartments(){
      if(!modalDept) return;

      const res = await fetchWithTimeout(API.departments, { headers: authHeaders(false) }, 15000);
      const js = await res.json().catch(()=>({}));
      if(!res.ok) throw new Error(js.message || 'Failed to load departments');

      const rows = Array.isArray(js.data) ? js.data : (Array.isArray(js) ? js : []);
      const items = rows.filter(d => !d.deleted_at);

      const label = (d) => {
        const t = (d?.title || '').toString().trim();
        const n = (d?.name || '').toString().trim();
        return t || n || (`Department #${d?.id ?? ''}`.trim());
      };

      modalDept.innerHTML = `<option value="">All</option>` + items.map(d =>
        `<option value="${esc(String(d.id))}">${esc(label(d))}</option>`
      ).join('');
    }

    const state = {
      perPage: num(perPageSel?.value, 20),
      filters: { q:'', department:'', module:'', featured:'', sort:'created_at', direction:'desc' },
      tabs: {
        pending:  { page: 1, lastPage: 1, items: [], pagination: { page:1, per_page:20, total:0, last_page:1 } },
        approved: { page: 1, lastPage: 1, items: [], pagination: { page:1, per_page:20, total:0, last_page:1 } },
        rejected: { page: 1, lastPage: 1, items: [], pagination: { page:1, per_page:20, total:0, last_page:1 } },
        all:      { page: 1, lastPage: 1, items: [], pagination: { page:1, per_page:20, total:0, last_page:1 } },
      },
      approvedLoaded: false,
      rejectedLoaded: false,
      allLoaded: false
    };

    const getTabKey = () => {
      const a = document.querySelector('.map-tabs .nav-link.active');
      const href = a?.getAttribute('href') || '#mapTabPending';
      if (href === '#mapTabApproved') return 'approved';
      if (href === '#mapTabRejected') return 'rejected';
      if (href === '#mapTabAll') return 'all';
      return 'pending';
    };

    function setEmpty(tabKey, show){
      const el = tabKey==='pending' ? emptyP : tabKey==='approved' ? emptyA : tabKey==='rejected' ? emptyR : emptyAll;
      if (el) el.style.display = show ? '' : 'none';
    }

    function rowActions(tabKey, row){
      const st = approvalStatus(row);
      const pendingRow = (st === 'pending');
      const uuid = pickUUID(row);

      let html = `
        <div class="dropdown text-end">
          <button
            type="button"
            class="btn btn-light btn-sm map-dd-toggle"
            aria-expanded="false"
            title="Actions"
          >
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <button type="button" class="dropdown-item" data-action="view" data-id="${esc(uuid)}">
                <i class="fa fa-eye"></i> View
              </button>
            </li>
      `;

      if (pendingRow){
        html += `
          <li>
            <button type="button" class="dropdown-item" data-action="approve" data-id="${esc(uuid)}" ${canApprove ? '' : 'disabled'}>
              <i class="fa fa-circle-check"></i> Approve
            </button>
          </li>
          <li>
            <button type="button" class="dropdown-item text-danger" data-action="reject" data-id="${esc(uuid)}" ${canApprove ? '' : 'disabled'}>
              <i class="fa fa-circle-xmark"></i> Reject
            </button>
          </li>
        `;
      }

      html += `</ul></div>`;
      return html;
    }

    function renderTab(tabKey){
      ROW_CACHE.clear();

      const rows = state.tabs[tabKey].items || [];
      const tbody =
        tabKey==='pending' ? tbP :
        tabKey==='approved' ? tbA :
        tabKey==='rejected' ? tbR : tbAll;

      const pager =
        tabKey==='pending' ? pagerP :
        tabKey==='approved' ? pagerA :
        tabKey==='rejected' ? pagerR : pagerAll;

      const info =
        tabKey==='pending' ? infoP :
        tabKey==='approved' ? infoA :
        tabKey==='rejected' ? infoR : infoAll;

      if (!tbody) return;

      if (!rows.length){
        tbody.innerHTML = '';
        setEmpty(tabKey, true);
        renderPager(pager, tabKey, state.tabs[tabKey].pagination.page, state.tabs[tabKey].pagination.last_page);
        if (info) info.textContent = infoText(state.tabs[tabKey].pagination, 0);
        return;
      }

      setEmpty(tabKey, false);

      tbody.innerHTML = rows.map(r => {
        const uuid = pickUUID(r);
        ROW_CACHE.set(String(uuid), r);

        const moduleKey = pickModuleKey(r);
        const moduleLabel = pickModuleLabel(r);
        const title = pickTitle(r) || '—';
        const dept = pickDept(r);
        const st = approvalStatus(r);
        const featured = isFeatured(r);

        const actorText =
          tabKey === 'approved' ? safeString(r.approved_by_name || r.approved_by?.name || pickActor(r) || '—') :
          tabKey === 'rejected' ? safeString(r.rejected_by_name || r.rejected_by?.name || pickActor(r) || '—') :
          (pickActor(r) || '—');

        const timeText =
          tabKey === 'approved' ? (pickApprovedAt(r) || safeString(r.updated_at || '—')) :
          tabKey === 'rejected' ? (pickRejectedAt(r) || safeString(r.updated_at || '—')) :
          tabKey === 'pending'  ? (pickRequestedAt(r) || safeString(r.created_at || '—')) :
          (safeString(r.updated_at || r.created_at || '—'));

        return `
          <tr data-id="${esc(String(uuid))}" data-tab="${esc(tabKey)}">
            <td>
              ${badgeModule(moduleLabel)}
              <div class="map-small map-muted">${esc(moduleKey)}</div>
            </td>
            <td>
              <div class="fw-semibold">${esc(title)}</div>
              <div class="map-small map-muted">${esc(String(uuid))}</div>
            </td>
            <td>${badgeDept(dept)}</td>
            <td>${esc(actorText)}</td>
            <td>${esc(timeText)}</td>
            <td>${badgeStatus(st)}</td>
            <td>${badgeFeatured(featured)}</td>
            <td class="text-end">${rowActions(tabKey, r)}</td>
          </tr>
        `;
      }).join('');

      renderPager(pager, tabKey, state.tabs[tabKey].pagination.page, state.tabs[tabKey].pagination.last_page);
      if (info) info.textContent = infoText(state.tabs[tabKey].pagination, rows.length);
    }

    function applyClientSide(tabKey, allItems){
      let items = Array.isArray(allItems) ? allItems.slice() : [];

      // tab filter safety
      if (tabKey !== 'all'){
        const want = tabKey === 'pending' ? 'pending' : tabKey;
        items = items.filter(x => approvalStatus(x) === want);
      }

      // featured filter
      if (state.filters.featured !== ''){
        const want = String(state.filters.featured);
        items = items.filter(x => String(isFeatured(x) ? 1 : 0) === want);
      }

      // module filter uses KEY
      if (state.filters.module){
        const want = state.filters.module.toLowerCase().trim();
        items = items.filter(x => (pickModuleKey(x) || '').toLowerCase() === want);
      }

      // dept filter
      if (state.filters.department){
        const dep = String(state.filters.department);
        items = items.filter(x => String(x?.department?.id || x?.record?.department_id || x?.department_id || '') === dep);
      }

      // search filter
      const q = (state.filters.q || '').toLowerCase().trim();
      if (q){
        items = items.filter(x => {
          const hay = [
            pickTitle(x),
            pickModuleKey(x),
            pickModuleLabel(x),
            pickDept(x),
            pickActor(x),
            safeString(x?.slug),
            safeString(x?.uuid),
            safeString(x?.record?.uuid),
          ].join(' ').toLowerCase();
          return hay.includes(q);
        });
      }

      // sorting
      const sk = (state.filters.sort || 'created_at').trim();
      const dir = (state.filters.direction || 'desc') === 'asc' ? 1 : -1;

      const getSortVal = (x) => {
        if (sk === 'title') return pickTitle(x);
        if (sk === 'module') return pickModuleKey(x);
        if (sk === 'updated_at') return safeString(x?.updated_at || x?.record?.updated_at || x?.created_at || '');
        if (sk === 'id') return num(x?.id || x?.record?.id, 0);
        return safeString(x?.created_at || x?.record?.created_at || x?.updated_at || '');
      };

      items.sort((a,b) => {
        const av = getSortVal(a);
        const bv = getSortVal(b);
        if (typeof av === 'number' && typeof bv === 'number') return dir * (av - bv);
        return dir * String(av).localeCompare(String(bv));
      });

      // pagination (client)
      const total = items.length;
      const per = Math.max(1, state.perPage || 20);
      const last = Math.max(1, Math.ceil((total || 1) / per));
      const page = Math.min(Math.max(1, state.tabs[tabKey].page || 1), last);

      const start = (page - 1) * per;
      const pageItems = items.slice(start, start + per);

      return {
        pageItems,
        pagination: { page, per_page: per, total, last_page: last }
      };
    }

    async function loadTab(tabKey){
      const tbody =
        tabKey==='pending' ? tbP :
        tabKey==='approved' ? tbA :
        tabKey==='rejected' ? tbR : tbAll;

      if (tbody){
        tbody.innerHTML = `<tr><td colspan="8" class="text-center map-muted" style="padding:38px;">Loading…</td></tr>`;
      }

      try{
        const res = await fetchWithTimeout(API.list(), { headers: authHeaders(false) }, 20000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(js?.message || js?.error || 'Failed to load');

        // ✅ FIX A: permissions from js.actor.role (present in your response)
        const roleFromApi = safeString(js?.actor?.role || '').toLowerCase();
        if (roleFromApi){
          ACTOR.role = roleFromApi;
        }else{
          // fallback only if API did not provide (rare)
          ACTOR.role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
        }
        computePermissions();

        const norm = normalizeListResponse(js, state.tabs[tabKey].page, state.perPage);
        const processed = applyClientSide(tabKey, norm.items);

        state.tabs[tabKey].items = processed.pageItems;
        state.tabs[tabKey].pagination = processed.pagination;
        state.tabs[tabKey].page = processed.pagination.page;
        state.tabs[tabKey].lastPage = processed.pagination.last_page;

        renderTab(tabKey);
      }catch(e){
        state.tabs[tabKey].items = [];
        state.tabs[tabKey].pagination = { page:1, per_page:state.perPage, total:0, last_page:1 };
        renderTab(tabKey);
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
        console.error('MasterApproval load error:', e);
      }
    }

    // pager click
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a.page-link[data-page][data-tab]');
      if (!a) return;
      e.preventDefault();

      const tab = a.dataset.tab;
      const p = num(a.dataset.page, 1);
      if (!tab) return;
      if (p === state.tabs[tab].page) return;

      state.tabs[tab].page = p;
      loadTab(tab);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // filters
    searchInput?.addEventListener('input', debounce(() => {
      state.filters.q = (searchInput.value || '').trim();
      Object.keys(state.tabs).forEach(k => state.tabs[k].page = 1);
      loadTab(getTabKey());
    }, 320));

    perPageSel?.addEventListener('change', () => {
      state.perPage = num(perPageSel.value, 20);
      Object.keys(state.tabs).forEach(k => state.tabs[k].page = 1);
      loadTab(getTabKey());
    });

    filterModalEl?.addEventListener('show.bs.modal', () => {
      if (modalDept) modalDept.value = state.filters.department || '';
      if (modalModule) modalModule.value = state.filters.module || '';
      if (modalFeatured) modalFeatured.value = (state.filters.featured ?? '');
      if (modalSort) modalSort.value = state.filters.sort || 'created_at';
      if (modalDir) modalDir.value = state.filters.direction || 'desc';
    });

    btnApplyFilters?.addEventListener('click', () => {
      state.filters.department = (modalDept?.value || '').trim();
      state.filters.module = (modalModule?.value || '').trim();
      state.filters.featured = (modalFeatured?.value ?? '');
      state.filters.sort = modalSort?.value || 'created_at';
      state.filters.direction = modalDir?.value || 'desc';

      Object.keys(state.tabs).forEach(k => state.tabs[k].page = 1);

      filterModal && filterModal.hide();
      loadTab(getTabKey());
    });

    btnReset?.addEventListener('click', () => {
      state.perPage = 20;
      state.filters = { q:'', department:'', module:'', featured:'', sort:'created_at', direction:'desc' };

      if (perPageSel) perPageSel.value = '20';
      if (searchInput) searchInput.value = '';
      if (modalDept) modalDept.value = '';
      if (modalModule) modalModule.value = '';
      if (modalFeatured) modalFeatured.value = '';
      if (modalSort) modalSort.value = 'created_at';
      if (modalDir) modalDir.value = 'desc';

      Object.keys(state.tabs).forEach(k => state.tabs[k].page = 1);

      loadTab(getTabKey());
    });

    // tabs
    document.querySelector('a[href="#mapTabPending"]')?.addEventListener('shown.bs.tab', () => loadTab('pending'));
    document.querySelector('a[href="#mapTabApproved"]')?.addEventListener('shown.bs.tab', () => {
      state.approvedLoaded = true;
      loadTab('approved');
    });
    document.querySelector('a[href="#mapTabRejected"]')?.addEventListener('shown.bs.tab', () => {
      state.rejectedLoaded = true;
      loadTab('rejected');
    });
    document.querySelector('a[href="#mapTabAll"]')?.addEventListener('shown.bs.tab', () => {
      state.allLoaded = true;
      loadTab('all');
    });

    /* =========================================================
      ✅ FIX B: Dropdown click handling was closing too early
      So we ignore outside-close when click is inside dropdown menu.
    ========================================================= */
    function closeAllDropdownsExcept(exceptToggle){
      document.querySelectorAll('.map-dd-toggle').forEach(t => {
        if (t === exceptToggle) return;
        try{
          const inst = bootstrap.Dropdown.getInstance(t);
          inst && inst.hide();
        }catch(_){}
      });
    }

    document.addEventListener('click', (e) => {
      const toggle = e.target.closest('.map-dd-toggle');
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

    // close when clicking OUTSIDE dropdown/menu, but NOT when clicking menu items
    document.addEventListener('click', (e) => {
      if (e.target.closest('.dropdown-menu')) return; // ✅ important
      closeAllDropdownsExcept(null);
    }, { capture: true });

    // ---- View / Approve / Reject ----
    function openViewModal(data){
      const m = pickModuleLabel(data) || '—';
      const t = pickTitle(data) || '—';
      const d = pickDept(data) || 'Global';
      const st = approvalStatus(data);

      if (vTitle) vTitle.textContent = 'Approval Details';
      if (vModule) vModule.textContent = m;
      if (vItemTitle) vItemTitle.textContent = t;
      if (vDept) vDept.textContent = d;
      if (vStatus) vStatus.textContent = st;

      const payload = (data?.record || data?.payload || data?.data || data || {});
      let pretty = '{}';
      try{ pretty = JSON.stringify(payload, null, 2); }catch(_){ pretty = String(payload); }
      if (vPayload) vPayload.textContent = pretty;

      viewModal && viewModal.show();
    }

    async function approveNow(uuid){
      const conf = await Swal.fire({
        title: 'Approve this request?',
        text: 'This will mark the item as Approved.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Approve',
        confirmButtonColor: '#16a34a'
      });
      if (!conf.isConfirmed) return;

      showLoading(true);
      try{
        // ✅ FIX C: send as FormData (most compatible with Laravel)
        const fd = new FormData();

        const res = await fetchWithTimeout(API.approve(uuid), {
          method: 'POST',
          headers: authHeaders(false), // no JSON content-type
          body: fd
        }, 20000);

        const js = await res.json().catch(()=>({}));
        if(!res.ok || js.success === false) throw new Error(js?.message || js?.error || 'Approve failed');

        ok(js?.message || 'Approved');

        // refresh current + others if loaded
        await loadTab('pending');
        if (state.approvedLoaded) await loadTab('approved');
        if (state.allLoaded) await loadTab('all');
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        showLoading(false);
      }
    }

    async function rejectNow(uuid){
      const conf = await Swal.fire({
        title: 'Reject this request?',
        input: 'textarea',
        inputLabel: 'Reason (optional)',
        inputPlaceholder: 'Write rejection reason…',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Reject',
        confirmButtonColor: '#ef4444'
      });
      if (!conf.isConfirmed) return;

      const reason = (conf.value || '').toString().trim();

      showLoading(true);
      try{
        const fd = new FormData();
        if (reason) fd.append('reason', reason);

        const res = await fetchWithTimeout(API.reject(uuid), {
          method: 'POST',
          headers: authHeaders(false), // no JSON content-type
          body: fd
        }, 20000);

        const js = await res.json().catch(()=>({}));
        if(!res.ok || js.success === false) throw new Error(js?.message || js?.error || 'Reject failed');

        ok(js?.message || 'Rejected');

        await loadTab('pending');
        if (state.rejectedLoaded) await loadTab('rejected');
        if (state.allLoaded) await loadTab('all');
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        showLoading(false);
      }
    }

    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;

      e.preventDefault();

      const act = btn.dataset.action || '';
      const uuid = (btn.dataset.id || '').trim();
      if (!uuid) return;

      // close dropdown nicely
      const toggle = btn.closest('.dropdown')?.querySelector('.map-dd-toggle');
      if (toggle){ try{ bootstrap.Dropdown.getInstance(toggle)?.hide(); }catch(_){ } }

      if (act === 'view'){
        const row = ROW_CACHE.get(uuid) || {};
        openViewModal(row);
        return;
      }

      if (act === 'approve'){
        if (!canApprove){ err('You do not have permission'); return; }
        await approveNow(uuid);
        return;
      }

      if (act === 'reject'){
        if (!canApprove){ err('You do not have permission'); return; }
        await rejectNow(uuid);
        return;
      }
    });

    // init
    showLoading(true);
    try{
      await loadDepartments();
      await loadTab('pending');
    }catch(ex){
      err(ex.message || 'Initialization failed');
      console.error(ex);
    }finally{
      showLoading(false);
    }
  });
})();
</script>
@endpush
