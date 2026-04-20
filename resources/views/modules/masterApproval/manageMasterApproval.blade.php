{{-- resources/views/modules/masterApproval/manageMasterApprovals.blade.php --}}
@section('title','Master Approval')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
  /* Tabs */
  .map-tabs.nav-tabs{border-color:var(--line-strong)}
  .map-tabs .nav-link{color:var(--ink)}
  .map-tabs .nav-link.active{background:var(--surface);border-color:var(--line-strong) var(--line-strong) var(--surface);}

  /* Card/Table */
  .map-card{border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:visible;}
  .map-card .card-body{overflow:visible}
  .map-table{--bs-table-bg:transparent}
  .map-table thead th{font-weight:650;color:var(--muted-color);font-size:13px;border-bottom:1px solid var(--line-strong);background:var(--surface);}
  .map-table thead.sticky-top{z-index:3}
  .map-table tbody tr{border-top:1px solid var(--line-soft)}
  .map-table tbody tr:hover{background:var(--page-hover)}
  .map-table th, .map-table td{padding: .55rem .75rem !important; vertical-align: middle;}
  .map-title-cell{max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block;}
  .map-muted{color:var(--muted-color)}
  .map-small{font-size:12.5px}

  /* Horizontal scroll */
  .table-responsive{display:block;width:100%;max-width:100%;overflow-x:auto !important;overflow-y:visible !important;-webkit-overflow-scrolling:touch;position:relative;}
  .table-responsive > table{width:100%; min-width:auto;}
  .table-responsive th,.table-responsive td{white-space:nowrap;}

  /* Dropdown - keep high z-index */
  .table-responsive .dropdown{position:relative}
  .map-dd-toggle{border-radius:10px}
  .dropdown-menu{border-radius:12px;border:1px solid var(--line-strong);box-shadow:var(--shadow-2);min-width:240px;z-index:99999;}
  .dropdown-menu.show{display:block !important}
  .dropdown-item{display:flex;align-items:center;gap:.6rem}
  .dropdown-item i{width:16px;text-align:center}
  .dropdown-item.text-danger{color:var(--danger-color) !important}

  /* Soft badges */
  .badge-soft{display:inline-flex;align-items:center;gap:6px;padding:.35rem .55rem;border-radius:999px;font-size:12px;font-weight:600}
  .badge-soft-primary{background:color-mix(in oklab, var(--primary-color) 12%, transparent);color:var(--primary-color)}
  .badge-soft-success{background:color-mix(in oklab, var(--success-color, #16a34a) 12%, transparent);color:var(--success-color, #16a34a)}
  .badge-soft-warning{background:color-mix(in oklab, var(--warning-color, #f59e0b) 14%, transparent);color:var(--warning-color, #f59e0b)}
  .badge-soft-muted{background:color-mix(in oklab, var(--muted-color) 10%, transparent);color:var(--muted-color)}
  .badge-soft-danger{background:color-mix(in oklab, var(--danger-color) 14%, transparent);color:var(--danger-color)}

  /* Loading overlay */
  .map-loading{position:fixed; inset:0;background:rgba(0,0,0,.45);display:none;align-items:center;justify-content:center;z-index:9999;backdrop-filter:blur(2px);}
  .map-loading .box{background:var(--surface);padding:18px 20px;border-radius:14px;display:flex;align-items:center;gap:12px;box-shadow:0 10px 26px rgba(0,0,0,.3);}
  .map-spin{width:38px;height:38px;border-radius:50%;border:4px solid rgba(148,163,184,.3);border-top:4px solid var(--primary-color);animation:mapSpin 1s linear infinite;}
  @keyframes mapSpin{to{transform:rotate(360deg)}}

  /* Toolbar */
  .map-toolbar{border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);padding:12px 12px;}
  .map-toolbar .map-search{min-width:280px; position:relative;}
  .map-toolbar .map-search input{padding-left:40px;}
  .map-toolbar .map-search i{position:absolute; left:12px; top:50%;transform:translateY(-50%); opacity:.6;}
  @media (max-width: 768px){
    .map-toolbar .map-row{flex-direction:column; align-items:stretch !important;}
    .map-toolbar .map-search{min-width:100%;}
    .map-toolbar .map-actions{display:flex; gap:8px; flex-wrap:wrap;}
    .map-toolbar .map-actions .btn{flex:1; min-width:140px;}
  }

  /* View modal payload preview */
  .map-json{border:1px solid var(--line-strong);border-radius:14px;background:color-mix(in oklab, var(--surface) 92%, transparent);padding:12px;font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;font-size:12.5px;line-height:1.45;white-space:pre-wrap;word-break:break-word;max-height:360px;overflow:auto;}

  /* Timeline Styles (Sync from Manage Pages) */
  .timeline { position: relative; padding: 0; list-style: none; }
  .timeline:before { content: ''; position: absolute; top: 0; bottom: 0; left: 31px; width: 2px; background: var(--line-soft); }
  .timeline-item { position: relative; margin-bottom: 20px; }
  .timeline-marker { position: absolute; top: 0; left: 20px; width: 24px; height: 24px; border-radius: 50%; background: var(--surface); border: 2px solid var(--primary-color); z-index: 10; }
  .timeline-content { margin-left: 60px; padding: 12px 16px; background: color-mix(in oklab, var(--surface) 95%, var(--bg-body)); border: 1px solid var(--line-soft); border-radius: 12px; }
  .timeline-date { font-size: 11px; color: var(--muted-color); margin-bottom: 4px; }
  .timeline-title { font-weight: 600; font-size: 13.5px; margin-bottom: 4px; }
  .timeline-author { font-size: 12px; font-weight: 500; color: var(--ink); }
  .timeline-comment { font-size: 12.5px; color: var(--muted-color); margin-top: 6px; padding: 6px 10px; background: rgba(0,0,0,0.03); border-left: 2px solid var(--line-strong); font-style: italic; }
  .badge-history { font-size: 10px; padding: 2px 6px; border-radius: 6px; vertical-align: middle; text-transform: uppercase; font-weight: 700; }

  /* SweetAlert focus fix over modals */
  .swal2-container {
    z-index: 10000 !important;
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
  {{-- Dynamic Tabs Navbar --}}
  <ul class="nav nav-tabs map-tabs mb-3" id="mapTabNav" role="tablist">
    {{-- Populated via JS --}}
  </ul>

  <div class="tab-content">
    <div class="card map-card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table map-table table-hover table-borderless align-middle mb-0">
            <thead class="sticky-top">
              <tr>
                <th style="width:180px;">Module</th>
                <th style="width:360px;">Title</th>
                <th style="width:220px;">Department</th>
                <th style="width:220px;" id="thSharedBy">Requested By</th>
                <th style="width:220px;" id="thSharedAt">Requested At</th>
                <th style="width:140px;">Status</th>
                <th style="width:140px;">Featured</th>
                <th style="width:108px;" class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody id="mapTbodyShared">
              <tr><td colspan="8" class="text-center map-muted" style="padding:38px;">Loading…</td></tr>
            </tbody>
          </table>
        </div>

        <div id="mapEmptyShared" class="p-4 text-center" style="display:none;">
          <i class="fa fa-clock mb-2" style="font-size:32px;opacity:.6;"></i>
          <div class="map-muted">No items found.</div>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
          <div class="map-small map-muted" id="mapInfoShared">—</div>
          <nav><ul id="mapPagerShared" class="pagination mb-0"></ul></nav>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Filter Modal --}}
<div class="modal fade" id="mapFilterModal" aria-hidden="true">
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
<div class="modal fade" id="mapViewModal" aria-hidden="true">
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

{{-- Workflow History Modal --}}
<div class="modal fade" id="historyModal" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-clock-rotate-left me-2"></i>Workflow History</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
        <div id="historyLoading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
          <div class="mt-2 text-muted">Loading history…</div>
        </div>
        <div id="historyContent" style="display:none;">
          <ul class="timeline" id="historyTimeline"></ul>
        </div>
        <div id="historyEmpty" class="text-center py-4 text-muted" style="display:none;">
          <i class="fa fa-history mb-2 fs-3 opacity-50"></i>
          <div>No history found for this item.</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- Rejection Reason Modal --}}
<div class="modal fade" id="rejectReasonModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-danger"><i class="fa fa-circle-xmark me-2"></i>Rejection Reason</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="p-3 bg-light rounded-3 border">
          <div id="rejectReasonText" class="text-dark" style="font-size: 14.5px; line-height: 1.6; white-space: pre-wrap;">—</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="previewModal" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-white border-bottom-0">
        <h5 class="modal-title fw-bold text-dark"><i class="fa fa-eye me-2 text-primary"></i>Proposed Changes Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body bg-light">
        <div id="previewCardTitle" class="card shadow-sm border-0 mb-3" style="display:none;">
          <div class="card-header bg-white fw-bold">Proposed Title</div>
          <div class="card-body" id="previewTitleContent">—</div>
        </div>
        <div id="previewCardBody" class="card shadow-sm border-0" style="display:none;">
          <div class="card-header bg-white fw-bold">Proposed Content</div>
          <div class="card-body bg-white pt-4" id="previewBodyContent" style="min-height: 300px;">
            {{-- Content will be injected here --}}
          </div>
        </div>
        <div id="previewEmpty" class="text-center py-5 text-muted" style="display:none;">
          <i class="fa fa-circle-info fs-1 mb-3 opacity-25"></i>
          <p>No specific draft content found in raw data.</p>
        </div>
      </div>
      <div class="modal-footer bg-light border-top-0">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
        <div id="previewModalActions" style="display:none;">
          <button type="button" class="btn btn-danger px-4 ms-2" id="btnModalReject">
            <i class="fa fa-circle-xmark me-2"></i>Reject
          </button>
          <button type="button" class="btn btn-success px-4 ms-2" id="btnModalApprove">
            <i class="fa fa-circle-check me-2"></i>Approve
          </button>
        </div>
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

  /* Prevent Bootstrap from stealing focus from SweetAlert */
  document.addEventListener('focusin', (e) => {
    if (e.target.closest && e.target.closest('.swal2-container')) {
      e.stopImmediatePropagation();
    }
  }, true);

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
    const ws = safeString(r?.workflow_status || r?.record?.workflow_status || '').toLowerCase().trim();
    if (ws) return ws;

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
      return `<span class="badge-soft badge-soft-success" title="Approved"><i class="fa fa-circle-check me-1"></i>Approved</span>`;
    }
    if (st === 'rejected'){
      return `<span class="badge-soft badge-soft-danger" title="Rejected"><i class="fa fa-circle-xmark me-1"></i>Rejected</span>`;
    }
    if (st === 'checked'){
      return `<span class="badge-soft badge-soft-primary" title="Checked by HOD"><i class="fa fa-check-double me-1"></i>Checked</span>`;
    }
    if (st === 'pending' || st === 'pending_check'){
      return `<span class="badge-soft badge-soft-warning" title="Pending Check"><i class="fa fa-clock me-1"></i>Pending</span>`;
    }
    return `<span class="badge-soft badge-soft-muted" title="${esc(st)}"><i class="fa fa-circle-question me-1"></i>${esc(st)}</span>`;
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

    let currentPreviewUuid = null;

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
    const ACTOR = { id: null, role: '', department_id: null };
  let canAssignPrivilege = false;
    let canApprove = false;

    function computePermissions(){
      const r = (ACTOR.role || '').toLowerCase();
      // Remove static approval restrictions, allow anyone who can access this page to approve/reject
      canApprove = true;
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

    // ✅ FIX (YOUR ISSUE): hard cleanup if bootstrap backdrop gets stuck
    function cleanupStuckBackdrop(){
      // only clean if NO modal is actually open
      if (document.querySelector('.modal.show')) return;

      document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('overflow');
      document.body.style.removeProperty('paddingRight');
      document.body.style.removeProperty('padding-right');
    }
    filterModalEl?.addEventListener('hidden.bs.modal', cleanupStuckBackdrop);

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
    const viewModal = viewModalEl ? new bootstrap.Modal(viewModalEl, { focus: false }) : null;
    const previewModalEl = $('previewModal');
    const previewModal = previewModalEl ? new bootstrap.Modal(previewModalEl, { focus: false }) : null;
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
      activeTab: 'pending',
      tabs: {
        pending:  { page: 1, lastPage: 1, items: [], pagination: { page:1, per_page:20, total:0, last_page:1 } },
        approved: { page: 1, lastPage: 1, items: [], pagination: { page:1, per_page:20, total:0, last_page:1 } },
      },
      divisions: {}, // cache division configuration from API
      rawItems: []   // cache flat de-duped rows from API
    };

    const getTabKey = () => state.activeTab || 'pending';

    function buildDynamicTabs() {
      const navEl = $('mapTabNav');
      if (!navEl) return;

      let html = `
        <li class="nav-item">
          <a class="nav-link ${state.activeTab === 'pending' ? 'active' : ''}" href="#" data-tab="pending">
            <i class="fa-solid fa-clock me-2"></i>Pending (All)
          </a>
        </li>
      `;

      for (const k in state.divisions) {
        const div = state.divisions[k];
        const count = div.counts?.pending || 0;
        // Always render Pages, or other divisions with pending requests
        if (count > 0 || k === 'pages') {
          html += `
            <li class="nav-item">
              <a class="nav-link ${state.activeTab === k ? 'active' : ''}" href="#" data-tab="${esc(k)}">
                <i class="fa-solid fa-layer-group me-2"></i>${esc(div.label)}
              </a>
            </li>
          `;
        }
      }

      html += `
        <li class="nav-item">
          <a class="nav-link ${state.activeTab === 'approved' ? 'active' : ''}" href="#" data-tab="approved">
            <i class="fa-solid fa-circle-check me-2"></i>Approved
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link ${state.activeTab === 'rejected' ? 'active' : ''}" href="#" data-tab="rejected">
            <i class="fa-solid fa-circle-xmark me-2"></i>Rejected
          </a>
        </li>
      `;

      navEl.innerHTML = html;
      bindTabNavClicks();
    }

    function bindTabNavClicks() {
      document.querySelectorAll('#mapTabNav .nav-link').forEach(link => {
        link.addEventListener('click', (e) => {
          e.preventDefault();
          const tab = link.dataset.tab;
          if (!tab) return;
          state.activeTab = tab;
          
          document.querySelectorAll('#mapTabNav .nav-link').forEach(l => l.classList.remove('active'));
          link.classList.add('active');

          // Update header titles based on viewing Mode
          const thBy = $('thSharedBy');
          const thAt = $('thSharedAt');
          if (thBy) thBy.innerText = tab === 'approved' ? 'Approved By' : 'Requested By';
          if (thAt) thAt.innerText = tab === 'approved' ? 'Approved At' : 'Requested At';

          if (!state.tabs[tab]) {
            state.tabs[tab] = { page: 1, lastPage: 1, items: [], pagination: { page: 1, per_page: state.perPage, total: 0, last_page: 1 } };
          }
          loadTab(tab); 
        });
      });
    }

    function setEmpty(show){
      const el = $('mapEmptyShared');
      if (el) el.style.display = show ? '' : 'none';
      const tb = $('mapTbodyShared');
      if (tb && show) tb.innerHTML = '';
    }

    function rowActions(tabKey, row){
      const st = approvalStatus(row);
      const pendingRow = (st === 'pending' || st === 'pending_check' || st === 'checked');
      const uuid = pickUUID(row);

      let html = `
        <div class="dropdown text-end">
          <button type="button" class="btn btn-light btn-sm map-dd-toggle" aria-expanded="false" title="Actions">
            <i class="fa fa-ellipsis-vertical"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <button type="button" class="dropdown-item" data-action="preview" data-id="${esc(uuid)}">
                <i class="fa fa-desktop"></i> View/Preview
              </button>
            </li>
      `;

        /* Replaced by preview modal actions */

      const table = pickModuleKey(row);
      const recordId = row?.id || row?.record?.id || 0;

      // ✅ History button for everyone
      html += `
        <li>
          <button type="button" class="dropdown-item" onclick="showHistory('${esc(table)}', '${esc(String(recordId))}')">
            <i class="fa fa-clock-rotate-left"></i> Workflow History
          </button>
        </li>
      `;

      // ✅ Reason button if rejected
      if (st === 'rejected') {
        const reason = safeString(row?.rejected_reason || row?.record?.rejected_reason || 'No reason provided');
        html += `
          <li>
            <button type="button" class="dropdown-item" onclick="showRejectReason('${esc(reason)}')">
              <i class="fa fa-comment-dots"></i> Rejection Reason
            </button>
          </li>
        `;
      }

      html += `</ul></div>`;
      return html;
    }

    function renderTab(tabKey){
      ROW_CACHE.clear();
      const rows = state.tabs[tabKey]?.items || [];
      const tbody = $('mapTbodyShared');
      const pager = $('mapPagerShared');
      const info = $('mapInfoShared');

      if (!tbody) return;

      if (!rows.length){
        tbody.innerHTML = '';
        setEmpty(true);
        renderPager(pager, tabKey, state.tabs[tabKey]?.pagination.page || 1, state.tabs[tabKey]?.pagination.last_page || 1);
        if (info) info.textContent = infoText(state.tabs[tabKey]?.pagination, 0);
        return;
      }

      setEmpty(false);

      tbody.innerHTML = rows.map(r => {
        const uuid = pickUUID(r);
        ROW_CACHE.set(String(uuid), r);

        const moduleKey = pickModuleKey(r);
        const moduleLabel = pickModuleLabel(r);
        const title = pickTitle(r) || '—';
        const dept = pickDept(r);
        const st = approvalStatus(r);
        const featured = isFeatured(r);

        const actorText = tabKey === 'approved' 
          ? safeString(r.approved_by_name || r.approved_by?.name || pickActor(r) || '—') 
          : (pickActor(r) || '—');

        const timeText = tabKey === 'approved' 
          ? (pickApprovedAt(r) || safeString(r.updated_at || '—')) 
          : (pickRequestedAt(r) || safeString(r.created_at || '—'));

        return `
          <tr data-id="${esc(String(uuid))}" data-tab="${esc(tabKey)}">
            <td>${badgeModule(moduleLabel)}</td>
            <td>
              <div class="fw-semibold map-title-cell" title="${esc(title)}">${esc(title)}</div>
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

      // Unified Tab and Module Splits filtering
      if (tabKey === 'approved') {
        items = items.filter(x => approvalStatus(x) === 'approved');
      } else if (tabKey === 'rejected') {
        items = items.filter(x => approvalStatus(x) === 'rejected');
      } else if (tabKey === 'pending') {
        items = items.filter(x => ['pending', 'pending_check', 'checked'].includes(approvalStatus(x)));
      } else {
        // Module tab key (filter by EXACT moduleKey lookup + status should be pending usually)
        items = items.filter(x => (pickModuleKey(x) || '').toLowerCase() === tabKey.toLowerCase() && ['pending', 'pending_check', 'checked'].includes(approvalStatus(x)));
      }

      // filters
      if (state.filters.featured !== ''){
        items = items.filter(x => String(isFeatured(x) ? 1 : 0) === String(state.filters.featured));
      }
      if (state.filters.module){
        items = items.filter(x => (pickModuleKey(x) || '').toLowerCase() === state.filters.module.toLowerCase().trim());
      }
      if (state.filters.department){
        items = items.filter(x => String(x?.department?.id || x?.record?.department_id || x?.department_id || '') === String(state.filters.department));
      }

      const q = (state.filters.q || '').toLowerCase().trim();
      if (q){
        items = items.filter(x => [pickTitle(x), pickModuleKey(x), pickModuleLabel(x), pickDept(x), pickActor(x), safeString(x?.uuid)].join(' ').toLowerCase().includes(q));
      }

      // sorting
      const sk = (state.filters.sort || 'created_at').trim();
      const dir = (state.filters.direction || 'desc') === 'asc' ? 1 : -1;

      items.sort((a,b) => {
        const getVal = (x) => sk === 'title' ? pickTitle(x) : sk === 'module' ? pickModuleKey(x) : safeString(x?.created_at || x?.record?.created_at || '');
        return dir * String(getVal(a)).localeCompare(String(getVal(b)));
      });

      const total = items.length;
      const per = Math.max(1, state.perPage || 20);
      const last = Math.max(1, Math.ceil(total / per));
      const page = Math.min(Math.max(1, state.tabs[tabKey]?.page || 1), last);
      const start = (page - 1) * per;

      return { pageItems: items.slice(start, start + per), pagination: { page, per_page: per, total, last_page: last } };
    }

    async function loadTab(tabKey){
      const tbody = $('mapTbodyShared');
      if (tbody) tbody.innerHTML = `<tr><td colspan="8" class="text-center map-muted" style="padding:38px;">Loading…</td></tr>`;

      try {
        const res = await fetchWithTimeout(API.list(), { headers: authHeaders(false) }, 40000);
        const js = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(js?.message || 'Failed to load');

        ACTOR.role = safeString(js?.actor?.role || '').toLowerCase();
        computePermissions();

        // Save layout structures to caches
        state.divisions = js?.notifications?.divisions || {};
        const norm = normalizeListResponse(js, state.tabs[tabKey]?.page || 1, state.perPage);
        state.rawItems = norm.items;

        buildDynamicTabs();

        const processed = applyClientSide(tabKey, state.rawItems);
        if (!state.tabs[tabKey]) state.tabs[tabKey] = {};
        state.tabs[tabKey].items = processed.pageItems;
        state.tabs[tabKey].pagination = processed.pagination;

        renderTab(tabKey);
      } catch(e) {
        if (state.tabs[tabKey]) state.tabs[tabKey].items = [];
        renderTab(tabKey);
        err(e.message || 'Failed');
      }
    }

    // pager click
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a.page-link[data-page]');
      if (!a) return;
      e.preventDefault();

      const tab = getTabKey();
      const p = num(a.dataset.page, 1);
      if (!tab) return;
      if (p === state.tabs[tab]?.page) return;

      if (!state.tabs[tab]) state.tabs[tab] = { page: 1 };
      state.tabs[tab].page = p;

      loadTab(tab);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // filters
    searchInput?.addEventListener('input', debounce(() => {
      state.filters.q = (searchInput.value || '').trim();
      Object.keys(state.tabs).forEach(k => { if (state.tabs[k]) state.tabs[k].page = 1; });
      loadTab(getTabKey());
    }, 320));

    perPageSel?.addEventListener('change', () => {
      state.perPage = num(perPageSel.value, 20);
      Object.keys(state.tabs).forEach(k => { if (state.tabs[k]) state.tabs[k].page = 1; });
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

      Object.keys(state.tabs).forEach(k => { if (state.tabs[k]) state.tabs[k].page = 1; });

      const activeTab = getTabKey();

      let fired = false;
      const done = () => {
        if (fired) return;
        fired = true;
        cleanupStuckBackdrop();
        loadTab(activeTab);
      };

      if (filterModalEl) {
        filterModalEl.addEventListener('hidden.bs.modal', done, { once: true });
        try{
          bootstrap.Modal.getOrCreateInstance(filterModalEl).hide();
        }catch(_){
          done();
        }

        // fallback: if hidden event doesn't fire (edge cases), force cleanup + reload
        setTimeout(done, 550);
      } else {
        done();
      }
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

    function pickViewUrl(r){
      const key = (pickModuleKey(r) || '').toLowerCase().trim();
      const uuid = pickUUID(r);
      const rowSlug = r?.slug || r?.record?.slug || '';
      if (!key || !uuid) return null;
      if (key === 'pages') return `/page/${rowSlug}`;
      const urlKey = key.replace(/_/g, '-');
      return `/${urlKey}/view/${uuid}`;
    }

    window.showPreview = function(uuid){
      const row = ROW_CACHE.get(uuid);
      if (!row) return;

      const record = row?.record || row?.payload || row?.data || row || {};
      
      // 1. Try to find draft_data
      let draft = null;
      const rawDraft = record?.draft_data || null;
      if (rawDraft) {
        if (typeof rawDraft === 'string') {
          try { draft = JSON.parse(rawDraft); } catch(e){ draft = null; }
        } else {
          draft = rawDraft;
        }
      }

      // 2. Decide what to display: Drafted changes (Updates) or the Main Record (New items)
      const displayData = (draft && typeof draft === 'object') ? draft : record;

      const title = displayData.title || displayData.page_title || displayData.name || null;
      const body = displayData.content_html || displayData.content || displayData.body || displayData.description || null;

      if (previewModal && (title || body)) {
        currentPreviewUuid = uuid;

        // show modal
        const tEl = $('previewTitleContent');
        const bEl = $('previewBodyContent');
        const cT = $('previewCardTitle');
        const cB = $('previewCardBody');
        const empty = $('previewEmpty');

        const actionBox = $('previewModalActions');
        const st = approvalStatus(row);
        const isPending = ['pending', 'pending_check', 'checked'].includes(st);

        if (actionBox) {
          actionBox.style.display = isPending ? 'block' : 'none';
        }

        if (tEl) tEl.innerText = title || '—';
        if (bEl) bEl.innerHTML = body || '<p class="text-muted">No content found.</p>';
        
        if (cT) cT.style.display = title ? '' : 'none';
        if (cB) cB.style.display = body ? '' : 'none';
        if (empty) empty.style.display = 'none';

        previewModal.show();
      } else {
        // Fallback: If no draft and no content, try public link. 
        // If content is purely JSON or non-standard, show the old debug View Modal.
        const url = pickViewUrl(row);
        if (url) {
          window.open(url, '_blank');
        } else {
          openViewModal(row);
        }
      }
    };

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
        confirmButtonColor: '#16a34a',
        returnFocus: false,
        didOpen: () => {
          const input = Swal.getInput();
          if (input) {
            setTimeout(() => input.focus(), 100);
            setTimeout(() => input.focus(), 500);
          }
        }
      });
      if (!conf.isConfirmed) return;

      showLoading(true);
      try{
        const fd = new FormData();
        const row = ROW_CACHE.get(uuid);
        if (row) fd.append('division_key', pickModuleKey(row));

        const res = await fetchWithTimeout(API.approve(uuid), {
          method: 'POST',
          headers: authHeaders(false),
          body: fd
        }, 20000);

        const js = await res.json().catch(()=>({}));
        if(!res.ok || js.success === false) throw new Error(js?.message || 'Approve failed');

        ok(js?.message || 'Approved');
        await loadTab(getTabKey());
      }catch(ex){
        err(ex.message || 'Failed');
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
        confirmButtonColor: '#ef4444',
        returnFocus: false,
        didOpen: () => {
          const input = Swal.getInput();
          if (input) {
            setTimeout(() => input.focus(), 100);
            setTimeout(() => input.focus(), 500);
          }
        }
      });
      if (!conf.isConfirmed) return;

      const reason = (conf.value || '').toString().trim();

      showLoading(true);
      try{
        const fd = new FormData();
        if (reason) fd.append('reason', reason);
        const row = ROW_CACHE.get(uuid);
        if (row) fd.append('division_key', pickModuleKey(row));

        const res = await fetchWithTimeout(API.reject(uuid), {
          method: 'POST',
          headers: authHeaders(false),
          body: fd
        }, 20000);

        const js = await res.json().catch(()=>({}));
        if(!res.ok || js.success === false) throw new Error(js?.message || 'Reject failed');

        ok(js?.message || 'Rejected');
        await loadTab(getTabKey());
      }catch(ex){
        err(ex.message || 'Failed');
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

      if (act === 'view' || act === 'preview'){
        showPreview(uuid);
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

    // --- Modal Approve/Reject Hooks ---
    $('btnModalApprove')?.addEventListener('click', async () => {
      if (!currentPreviewUuid) return;
      if (!canApprove) { err('You do not have permission'); return; }
      
      const modalInst = bootstrap.Modal.getInstance($('previewModal'));
      await approveNow(currentPreviewUuid);
      modalInst?.hide();
    });

    $('btnModalReject')?.addEventListener('click', async () => {
      if (!currentPreviewUuid) return;
      if (!canApprove) { err('You do not have permission'); return; }

      const modalInst = bootstrap.Modal.getInstance($('previewModal'));
      await rejectNow(currentPreviewUuid);
      modalInst?.hide();
    });

    // init
    showLoading(true);
    try{
      await loadDepartments();
      loadTab('pending');
    }catch(ex){
      err(ex.message || 'Initialization failed');
      console.error(ex);
    }finally{
      showLoading(false);
    }
  });

  /* =========================================================
     ✅ Workflow History Logic (Sync from managePage.blade.php)
  ========================================================= */
  const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
  window.showHistory = async (table, id) => {
    historyModal.show();
    document.getElementById('historyLoading').style.display = 'block';
    document.getElementById('historyContent').style.display = 'none';
    document.getElementById('historyEmpty').style.display = 'none';
    document.getElementById('historyTimeline').innerHTML = '';

    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    try {
      const res = await fetch(`/api/master-approval/history/${table}/${id}`, {
        headers: {
          'Authorization': 'Bearer ' + token,
          'Accept': 'application/json'
        }
      });
      const j = await res.json();
      document.getElementById('historyLoading').style.display = 'none';

      if (j.success && j.data && j.data.length) {
        document.getElementById('historyTimeline').innerHTML = j.data.map(log => `
          <li class="timeline-item">
            <div class="timeline-marker"></div>
            <div class="timeline-content">
              <div class="timeline-date">${new Date(log.created_at).toLocaleString()}</div>
              <div class="timeline-title">
                Status changed to <span class="badge ${getHistoryStatusClass(log.to_status)}">${log.to_status.replace(/_/g, ' ')}</span>
              </div>
              <div class="timeline-author">Action by: ${log.user_name || 'System'} (${log.user_role || 'unknown'})</div>
              ${log.comment ? `<div class="timeline-comment">${log.comment}</div>` : ''}
            </div>
          </li>
        `).join('');
        document.getElementById('historyContent').style.display = 'block';
      } else {
        document.getElementById('historyEmpty').style.display = 'block';
      }
    } catch (e) {
      document.getElementById('historyLoading').style.display = 'none';
      document.getElementById('historyEmpty').style.display = 'block';
    }
  };

  function getHistoryStatusClass(s) {
    s = s.toLowerCase();
    if (s === 'approved') return 'badge-soft-success text-success';
    if (s === 'rejected') return 'badge-soft-danger text-danger';
    if (s === 'checked') return 'badge-soft-primary text-primary';
    if (s === 'pending_check') return 'badge-soft-warning text-warning';
    return 'badge-soft-muted text-muted';
  }

  /* =========================================================
     ✅ Rejection Reason Modal Logic
  ========================================================= */
  const rejectReasonModal = new bootstrap.Modal(document.getElementById('rejectReasonModal'));
  window.showRejectReason = (reason) => {
    document.getElementById('rejectReasonText').textContent = reason || 'No reason provided';
    rejectReasonModal.show();
  };
})();
</script>
@endpush
