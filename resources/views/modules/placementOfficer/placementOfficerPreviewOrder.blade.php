{{-- resources/views/modules/placement/placementOfficerPreviewOrder.blade.php --}}
@section('title','Placement Officer Preview Order')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
  Placement Officer Preview Order (Admin)
  - Dept select -> Assigned/Unassigned tabs
  - Drag reorder + Up/Down controls
========================= */

.popo-wrap{max-width:1200px;margin:18px auto 48px;padding:0 12px;overflow:visible}
.popo-wrap .tab-content,.popo-wrap .tab-pane{overflow:visible}

/* Cards */
.popo-card{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.popo-card .card-header{
  background:transparent;
  border-bottom:1px solid var(--line-soft);
}
.popo-title{margin:0;font-weight:800}
.popo-helper{font-size:12.5px;color:var(--muted-color)}
.popo-small{font-size:12.5px}

/* Chips */
.popo-chip{
  display:inline-flex;align-items:center;gap:8px;
  padding:8px 10px;border-radius:999px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 90%, transparent);
  font-size:12.5px;
}
.popo-chip i{opacity:.75}

/* Tabs */
.popo-wrap .nav.nav-tabs{border-color:var(--line-strong)}
.popo-wrap .nav-tabs .nav-link{color:var(--ink)}
.popo-wrap .nav-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface);
}

/* Toolbar */
.popo-toolbar{
  display:flex;flex-wrap:wrap;gap:10px;
  align-items:center;justify-content:space-between;
}
.popo-toolbar-left{display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.popo-toolbar-right{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
.popo-select{min-width:260px;}
.popo-search{min-width:260px;}
@media (max-width: 768px){
  .popo-select,.popo-search{min-width:100%}
  .popo-toolbar-right .btn{flex:1;min-width:160px}
}

/* Lists */
.popo-list{
  list-style:none;
  margin:0;
  padding:0;
  display:flex;
  flex-direction:column;
  gap:10px;
}
.popo-item{
  border:1px solid var(--line-soft);
  border-radius:14px;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  padding:10px 10px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
}
.popo-item:hover{background:var(--page-hover)}
.popo-left{
  display:flex;align-items:center;gap:10px;min-width:0;
}
.popo-handle{
  width:34px;height:34px;border-radius:10px;
  display:flex;align-items:center;justify-content:center;
  border:1px dashed var(--line-strong);
  color:var(--muted-color);
  cursor:grab;
  flex:0 0 auto;
}
.popo-handle:active{cursor:grabbing}
.popo-rank{
  width:32px;height:32px;border-radius:10px;
  display:flex;align-items:center;justify-content:center;
  background:color-mix(in oklab, var(--primary-color) 10%, transparent);
  color:var(--primary-color);
  font-weight:800;
  flex:0 0 auto;
}
.popo-avatar{
  width:38px;height:38px;border-radius:12px;
  background:color-mix(in oklab, var(--muted-color) 12%, transparent);
  display:flex;align-items:center;justify-content:center;
  color:var(--muted-color);
  flex:0 0 auto;
  overflow:hidden;
}
.popo-avatar img{width:100%;height:100%;object-fit:cover}
.popo-meta{min-width:0}
.popo-name{
  font-weight:800;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.popo-sub{
  font-size:12.5px;color:var(--muted-color);
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.popo-right{
  display:flex;align-items:center;gap:8px;flex:0 0 auto;
}
.popo-move{
  display:flex;gap:6px;
}
.popo-move .btn{
  padding:.25rem .45rem;
  border-radius:10px;
}
.popo-actions .btn{border-radius:10px}

/* Drag visuals */
.popo-item[draggable="true"]{user-select:none}
.popo-item.popo-dragging{opacity:.55;transform:scale(.995)}
.popo-item.popo-over{outline:2px solid color-mix(in oklab, var(--primary-color) 35%, transparent)}

/* Empty state */
.popo-empty{
  border:1px dashed var(--line-strong);
  border-radius:16px;
  padding:22px;
  text-align:center;
  color:var(--muted-color);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.popo-empty i{font-size:30px;opacity:.6}

/* Loading overlay */
.popo-loading{
  position:fixed;inset:0;
  background:rgba(0,0,0,0.45);
  display:flex;justify-content:center;align-items:center;
  z-index:9999;
  backdrop-filter:blur(2px)
}
.popo-loading-card{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;flex-direction:column;align-items:center;gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,0.3)
}
.popo-spinner{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,0.3);
  border-top:4px solid var(--primary-color);
  animation:popoSpin 1s linear infinite
}
@keyframes popoSpin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

/* Button loading */
.popo-btn-loading{position:relative;color:transparent !important}
.popo-btn-loading::after{
  content:'';
  position:absolute;
  width:16px;height:16px;
  top:50%;left:50%;
  margin:-8px 0 0 -8px;
  border:2px solid transparent;
  border-top:2px solid currentColor;
  border-radius:50%;
  animation:popoSpin 1s linear infinite
}
</style>
@endpush

@section('content')
<div class="popo-wrap">

  {{-- Loading Overlay --}}
  <div id="popoLoading" class="popo-loading" style="display:none;">
    <div class="popo-loading-card">
      <div class="popo-spinner"></div>
      <div class="popo-small">Loading…</div>
    </div>
  </div>

  {{-- Header --}}
  <div class="card popo-card mb-3">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div>
        <div class="d-flex align-items-center gap-2">
          <i class="fa-solid fa-list-ol" style="opacity:.75;"></i>
          <h5 class="popo-title">Placement Officer Preview Order</h5>
        </div>
        <div class="popo-helper mt-1">
          Select a department (or <strong>All Departments</strong>), then assign/unassign placement officers and reorder the “Assigned Users” list (saved as an ordered JSON array of placement officer IDs).
        </div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <span class="popo-chip"><i class="fa-solid fa-shield-halved"></i> Admin module</span>
        <span class="popo-chip"><i class="fa-solid fa-arrows-up-down-left-right"></i> Drag reorder</span>
        <span class="popo-chip"><i class="fa-solid fa-code"></i> JSON order</span>
      </div>
    </div>
  </div>

  {{-- Top Controls --}}
  <div class="card popo-card mb-3">
    <div class="card-body">
      <div class="popo-toolbar">
        <div class="popo-toolbar-left">
          <div>
            <label class="form-label mb-1">Department / Scope</label>
            <select id="deptSelect" class="form-select popo-select">
              <option value="">Loading departments…</option>
            </select>
            <div class="popo-helper mt-1">
              Choose a department-specific order or <strong>All Departments</strong> (global). Empty assignments are allowed and can be saved.
            </div>
          </div>

          <div class="d-flex flex-wrap gap-2 align-items-end">
            <span class="popo-chip"><i class="fa-solid fa-users"></i><span id="chipTotal">Total: —</span></span>
            <span class="popo-chip"><i class="fa-solid fa-user-check"></i><span id="chipAssigned">Assigned: —</span></span>
            <span class="popo-chip"><i class="fa-solid fa-user-plus"></i><span id="chipUnassigned">Unassigned: —</span></span>
          </div>
        </div>

        <div class="popo-toolbar-right" id="writeControls" style="display:none;">
          <button type="button" class="btn btn-light" id="btnReload">
            <i class="fa fa-rotate me-1"></i>Reload
          </button>
          <button type="button" class="btn btn-outline-primary" id="btnResetLocal" disabled>
            <i class="fa fa-rotate-left me-1"></i>Reset Changes
          </button>
          <button type="button" class="btn btn-primary" id="btnSave" disabled>
            <i class="fa fa-floppy-disk me-1"></i>Save Order
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#tab-assigned" role="tab" aria-selected="true">
        <i class="fa-solid fa-user-check me-2"></i>Assigned Users
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-unassigned" role="tab" aria-selected="false">
        <i class="fa-solid fa-user-plus me-2"></i>Unassigned Users
      </a>
    </li>
  </ul>

  <div class="tab-content">

    {{-- Assigned --}}
    <div class="tab-pane fade show active" id="tab-assigned" role="tabpanel">
      <div class="card popo-card">
        <div class="card-header py-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
              <div class="fw-bold"><i class="fa-solid fa-grip-lines me-2"></i>Reorder Assigned Placement Officers</div>
              <div class="popo-helper mt-1">Drag to reorder, or use ↑/↓ buttons (mobile-friendly). Removing moves to Unassigned.</div>
            </div>
            <div style="min-width:260px;">
              <input id="assignedSearch" class="form-control popo-search" type="search" placeholder="Search assigned…">
            </div>
          </div>
        </div>

        <div class="card-body">
          <div id="assignedEmpty" class="popo-empty" style="display:none;">
            <i class="fa-regular fa-folder-open mb-2"></i>
            <div class="fw-semibold">No assigned users</div>
            <div class="popo-small">Assign users from the “Unassigned Users” tab, or save empty for this scope.</div>
          </div>

          <ul id="assignedList" class="popo-list"></ul>
        </div>
      </div>
    </div>

    {{-- Unassigned --}}
    <div class="tab-pane fade" id="tab-unassigned" role="tabpanel">
      <div class="card popo-card">
        <div class="card-header py-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
              <div class="fw-bold"><i class="fa-solid fa-user-plus me-2"></i>Available Placement Officers (Unassigned)</div>
              <div class="popo-helper mt-1">Assigning will append the user to the end of Assigned list.</div>
            </div>
            <div style="min-width:260px;">
              <input id="unassignedSearch" class="form-control popo-search" type="search" placeholder="Search unassigned…">
            </div>
          </div>
        </div>

        <div class="card-body">
          <div id="unassignedEmpty" class="popo-empty" style="display:none;">
            <i class="fa-regular fa-circle-check mb-2"></i>
            <div class="fw-semibold">No unassigned users</div>
            <div class="popo-small">All eligible users are assigned.</div>
          </div>

          <ul id="unassignedList" class="popo-list"></ul>
        </div>
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

<script>
(() => {
  if (window.__PLACEMENT_OFFICER_PREVIEW_ORDER_INIT__) return;
  window.__PLACEMENT_OFFICER_PREVIEW_ORDER_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=250) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };
  const GLOBAL_KEY = '__all';

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

  function setBtnLoading(btn, loading){
    if (!btn) return;
    btn.disabled = !!loading;
    btn.classList.toggle('popo-btn-loading', !!loading);
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    // =========================
    // ✅ API MAP
    // =========================
    const API = {
      departments: '/api/departments',
      byDept: (deptKey) => `/api/placement-officer-preview-order/${encodeURIComponent(deptKey)}`,
      save: (deptKey) => `/api/placement-officer-preview-order/${encodeURIComponent(deptKey)}/save`,
      me: '/api/users/me'
    };

    // =========================
    // Permissions
    // =========================
    const ACTOR = { role: '' };
    let canWrite = false;
    const writeRoles = ['admin','super_admin','director','principal','hod','technical_assistant','it_person'];

    const authHeaders = (json=false) => {
      const h = { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' };
      if (json) h['Content-Type'] = 'application/json';
      return h;
    };

    const popoLoading = $('popoLoading');
    const showLoading = (v) => { if (popoLoading) popoLoading.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('toastSuccess');
    const toastErrEl = $('toastError');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { const el=$('toastSuccessText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('toastErrorText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    async function fetchMe(){
      try{
        const res = await fetchWithTimeout(API.me, { headers: authHeaders(false) }, 9000);
        if (res.ok){
          const js = await res.json().catch(()=> ({}));
          const role = js?.data?.role || js?.role || '';
          if (role) ACTOR.role = String(role).toLowerCase();
        }
      }catch(_){}
      if (!ACTOR.role){
        ACTOR.role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
      }
      canWrite = writeRoles.includes((ACTOR.role||'').toLowerCase());
      const wc = $('writeControls');
      if (wc) wc.style.display = canWrite ? 'flex' : 'none';
    }

    // =========================
    // Elements
    // =========================
    const deptSelect = $('deptSelect');
    const chipTotal = $('chipTotal');
    const chipAssigned = $('chipAssigned');
    const chipUnassigned = $('chipUnassigned');

    const btnReload = $('btnReload');
    const btnResetLocal = $('btnResetLocal');
    const btnSave = $('btnSave');

    const assignedSearch = $('assignedSearch');
    const unassignedSearch = $('unassignedSearch');

    const assignedList = $('assignedList');
    const unassignedList = $('unassignedList');

    const assignedEmpty = $('assignedEmpty');
    const unassignedEmpty = $('unassignedEmpty');

    // =========================
    // State
    // =========================
    const state = {
      deptKey: '',
      depts: [],
      allUsers: [],
      assignedIdsServer: [],
      assignedIdsLocal: [],
      usersById: new Map(),
      qAssigned: '',
      qUnassigned: ''
    };

    // Backend already filters to placement officer roles; keep these exclusions for safety
    const EXCLUDE_ROLES = new Set(['admin','director','student','super_admin']);

    function normDeptList(js){
      const arr = js?.data || js?.departments || js?.items || [];
      return Array.isArray(arr) ? arr : [];
    }

    function normUsers(arr){
      const a = Array.isArray(arr) ? arr : [];
      return a.map(u => ({
        id: u.id ?? u.user_id ?? u.uid,
        uuid: u.uuid ?? null,
        name: u.name ?? u.full_name ?? u.title ?? '—',
        email: u.email ?? '',
        role: (u.role ?? '').toString(),
        avatar: u.avatar_url ?? u.image_url ?? u.photo_url ?? u.image ?? null,
        meta: u
      })).filter(x => x.id !== undefined && x.id !== null);
    }

    function normDeptPayload(js){
      const root = js?.data || js || {};
      const ids =
        root.placement_officer_ids ||
        root.placementOfficerIds ||
        root.order?.placement_officer_ids ||
        root.order?.placementOfficerIds ||
        root.ids || [];

      const assigned_users = root.assigned_users || root.assigned || [];
      const unassigned_users = root.unassigned_users || root.unassigned || [];
      const users = root.users || root.all_users || [];

      return {
        ids: Array.isArray(ids) ? ids.map(x => Number(x)).filter(n => Number.isFinite(n)) : [],
        assigned_users: normUsers(assigned_users),
        unassigned_users: normUsers(unassigned_users),
        users: normUsers(users)
      };
    }

    function isDirty(){
      const a = state.assignedIdsServer || [];
      const b = state.assignedIdsLocal || [];
      if (a.length !== b.length) return true;
      for (let i=0;i<a.length;i++) if (Number(a[i]) !== Number(b[i])) return true;
      return false;
    }

    function updateButtons(){
      const hasDept = !!state.deptKey; // includes __all
      const dirty = isDirty();
      if (btnResetLocal) btnResetLocal.disabled = !(hasDept && dirty);
      if (btnSave) btnSave.disabled = !(hasDept && dirty && canWrite);
    }

    function updateChips(){
      const total = state.allUsers.length;
      const assignedSet = new Set(state.assignedIdsLocal.map(Number));
      const assigned = state.allUsers.filter(u => assignedSet.has(Number(u.id))).length;
      const unassigned = total - assigned;

      if (chipTotal) chipTotal.textContent = `Total: ${total}`;
      if (chipAssigned) chipAssigned.textContent = `Assigned: ${assigned}`;
      if (chipUnassigned) chipUnassigned.textContent = `Unassigned: ${unassigned}`;
    }

    function buildUsersMap(users){
      state.usersById = new Map();
      users.forEach(u => state.usersById.set(Number(u.id), u));
    }

    function eligibleFilter(u){
      const r = (u.role || '').toLowerCase();
      if (!r) return true;
      return !EXCLUDE_ROLES.has(r);
    }

    function getAssignedUsers(){
      const ids = state.assignedIdsLocal.map(Number);
      const q = (state.qAssigned || '').toLowerCase();
      const out = [];
      ids.forEach(id => {
        const u = state.usersById.get(id);
        if (!u) return;
        if (!eligibleFilter(u)) return;

        if (q){
          const hay = `${u.name} ${u.email} ${u.role}`.toLowerCase();
          if (!hay.includes(q)) return;
        }
        out.push(u);
      });
      return out;
    }

    function getUnassignedUsers(){
      const assignedSet = new Set(state.assignedIdsLocal.map(Number));
      const q = (state.qUnassigned || '').toLowerCase();
      return state.allUsers
        .filter(u => eligibleFilter(u))
        .filter(u => !assignedSet.has(Number(u.id)))
        .filter(u => {
          if (!q) return true;
          const hay = `${u.name} ${u.email} ${u.role}`.toLowerCase();
          return hay.includes(q);
        });
    }

    function avatarHtml(u){
      const img = u.avatar;
      if (img) return `<img src="${esc(img)}" alt="">`;
      const initials = (u.name || 'U').trim().split(/\s+/).slice(0,2).map(s=>s[0]?.toUpperCase()||'').join('');
      return `<span style="font-weight:800">${esc(initials || 'U')}</span>`;
    }

    // =========================
    // Render
    // =========================
    function renderAssigned(){
      const users = getAssignedUsers();
      assignedList.innerHTML = '';

      assignedEmpty.style.display = users.length ? 'none' : '';

      users.forEach((u, idx) => {
        const li = document.createElement('li');
        li.className = 'popo-item';
        li.setAttribute('draggable','true');
        li.dataset.id = String(u.id);

        li.innerHTML = `
          <div class="popo-left">
            <div class="popo-handle" title="Drag to reorder"><i class="fa-solid fa-grip-lines"></i></div>
            <div class="popo-rank" title="Position">${idx+1}</div>
            <div class="popo-avatar" title="User">${avatarHtml(u)}</div>
            <div class="popo-meta">
              <div class="popo-name">${esc(u.name)}</div>
              <div class="popo-sub">
                ${esc(u.email || '—')}
                ${u.role ? ` • <span class="text-muted">${esc(u.role)}</span>` : ``}
              </div>
            </div>
          </div>

          <div class="popo-right">
            <div class="popo-move">
              <button type="button" class="btn btn-light btn-sm" data-move="up" title="Move up"><i class="fa-solid fa-arrow-up"></i></button>
              <button type="button" class="btn btn-light btn-sm" data-move="down" title="Move down"><i class="fa-solid fa-arrow-down"></i></button>
            </div>
            <div class="popo-actions">
              <button type="button" class="btn btn-outline-danger btn-sm" data-action="unassign" ${canWrite ? '' : 'disabled'}>
                <i class="fa-solid fa-user-minus me-1"></i>Remove
              </button>
            </div>
          </div>
        `;

        assignedList.appendChild(li);
      });

      initDnD();
      updateChips();
      updateButtons();
    }

    function renderUnassigned(){
      const users = getUnassignedUsers();
      unassignedList.innerHTML = '';

      unassignedEmpty.style.display = users.length ? 'none' : '';

      users.forEach((u) => {
        const li = document.createElement('li');
        li.className = 'popo-item';
        li.dataset.id = String(u.id);

        li.innerHTML = `
          <div class="popo-left">
            <div class="popo-avatar">${avatarHtml(u)}</div>
            <div class="popo-meta">
              <div class="popo-name">${esc(u.name)}</div>
              <div class="popo-sub">
                ${esc(u.email || '—')}
                ${u.role ? ` • <span class="text-muted">${esc(u.role)}</span>` : ``}
              </div>
            </div>
          </div>

          <div class="popo-right">
            <button type="button" class="btn btn-primary btn-sm" data-action="assign" ${canWrite ? '' : 'disabled'}>
              <i class="fa-solid fa-user-plus me-1"></i>Assign
            </button>
          </div>
        `;

        unassignedList.appendChild(li);
      });

      updateChips();
      updateButtons();
    }

    function renderAll(){
      renderAssigned();
      renderUnassigned();
    }

    // =========================
    // Drag & Drop
    // =========================
    let dragEl = null;

    function idsFromDom(){
      const ids = Array.from(assignedList.querySelectorAll('.popo-item'))
        .map(li => Number(li.dataset.id))
        .filter(n => Number.isFinite(n));
      state.assignedIdsLocal = ids;
      updateButtons();
      updateChips();
    }

    function initDnD(){
      assignedList.querySelectorAll('.popo-item[draggable="true"]').forEach(item => {
        item.addEventListener('dragstart', (e) => {
          dragEl = item;
          item.classList.add('popo-dragging');
          e.dataTransfer.effectAllowed = 'move';
          try{ e.dataTransfer.setData('text/plain', item.dataset.id); }catch(_){}
        });

        item.addEventListener('dragend', () => {
          item.classList.remove('popo-dragging');
          assignedList.querySelectorAll('.popo-item').forEach(x => x.classList.remove('popo-over'));
          dragEl = null;
          idsFromDom();
          renderAssigned(); // refresh ranks
        });

        item.addEventListener('dragover', (e) => {
          e.preventDefault();
          e.dataTransfer.dropEffect = 'move';
          if (!dragEl || dragEl === item) return;
          item.classList.add('popo-over');

          const rect = item.getBoundingClientRect();
          const after = (e.clientY - rect.top) > (rect.height / 2);

          if (after) {
            if (item.nextSibling !== dragEl) assignedList.insertBefore(dragEl, item.nextSibling);
          } else {
            if (item !== dragEl.nextSibling) assignedList.insertBefore(dragEl, item);
          }
        });

        item.addEventListener('dragleave', () => item.classList.remove('popo-over'));
        item.addEventListener('drop', (e) => { e.preventDefault(); item.classList.remove('popo-over'); });
      });
    }

    // =========================
    // Data Loading
    // =========================
    async function loadDepartments(){
      deptSelect.innerHTML = `<option value="">Loading departments…</option>`;
      try{
        const res = await fetchWithTimeout(API.departments, { headers: authHeaders(false) }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }
        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to load departments');

        const list = normDeptList(js);
        state.depts = list;

        const deptOptions = [];
        const seen = new Set();

        list.forEach(d => {
          const key = String(d.uuid || d.id || '').trim();
          if (!key || seen.has(key)) return;
          seen.add(key);

          const name = d.name || d.title || d.department_name || 'Department';
          deptOptions.push(`<option value="${esc(key)}">${esc(name)}</option>`);
        });

        deptSelect.innerHTML = `
          <option value="">Select department / scope…</option>
          <option value="${GLOBAL_KEY}">All Departments (Global)</option>
          ${deptOptions.join('')}
        `;
      }catch(e){
        deptSelect.innerHTML = `
          <option value="">Failed to load departments</option>
          <option value="${GLOBAL_KEY}">All Departments (Global)</option>
        `;
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    async function loadDeptData(deptKey){
      state.deptKey = deptKey || '';
      state.allUsers = [];
      state.assignedIdsServer = [];
      state.assignedIdsLocal = [];
      state.usersById = new Map();
      state.qAssigned = '';
      state.qUnassigned = '';

      if (assignedSearch) assignedSearch.value = '';
      if (unassignedSearch) unassignedSearch.value = '';

      assignedList.innerHTML = '';
      unassignedList.innerHTML = '';
      assignedEmpty.style.display = 'none';
      unassignedEmpty.style.display = 'none';

      updateChips();
      updateButtons();

      if (!deptKey){
        assignedEmpty.style.display = '';
        unassignedEmpty.style.display = '';
        return;
      }

      showLoading(true);
      try{
        const res = await fetchWithTimeout(API.byDept(deptKey), { headers: authHeaders(false) }, 20000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }
        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to load department data');

        const p = normDeptPayload(js);

        const combinedUsers = (p.users && p.users.length)
          ? p.users
          : [...(p.assigned_users||[]), ...(p.unassigned_users||[])];

        const eligible = combinedUsers.filter(eligibleFilter);

        state.allUsers = eligible;
        buildUsersMap(eligible);

        const ids = (p.ids || []).map(Number).filter(n => Number.isFinite(n));
        const existingSet = new Set(eligible.map(u => Number(u.id)));
        const sanitized = ids.filter(id => existingSet.has(Number(id)));

        state.assignedIdsServer = sanitized.slice();
        state.assignedIdsLocal  = sanitized.slice();

        // fallback: infer order from assigned list if ids missing
        if (!state.assignedIdsLocal.length && (p.assigned_users||[]).length){
          const inferred = p.assigned_users
            .filter(eligibleFilter)
            .map(u => Number(u.id))
            .filter(n => Number.isFinite(n) && existingSet.has(n));
          state.assignedIdsServer = inferred.slice();
          state.assignedIdsLocal  = inferred.slice();
        }

        renderAll();
      }catch(e){
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
        assignedEmpty.style.display = '';
        unassignedEmpty.style.display = '';
      }finally{
        showLoading(false);
      }
    }

    // =========================
    // Mutations
    // =========================
    function assignUser(id){
      const n = Number(id);
      if (!Number.isFinite(n)) return;
      if (state.assignedIdsLocal.includes(n)) return;
      state.assignedIdsLocal.push(n);
      renderAll();
      ok('Assigned (not saved)');
    }

    function unassignUser(id){
      const n = Number(id);
      state.assignedIdsLocal = state.assignedIdsLocal.filter(x => Number(x) !== n);
      renderAll();
      ok('Removed (not saved)');
    }

    function moveAssigned(id, dir){
      const n = Number(id);
      const idx = state.assignedIdsLocal.findIndex(x => Number(x) === n);
      if (idx < 0) return;
      const next = dir === 'up' ? idx-1 : idx+1;
      if (next < 0 || next >= state.assignedIdsLocal.length) return;
      const tmp = state.assignedIdsLocal[idx];
      state.assignedIdsLocal[idx] = state.assignedIdsLocal[next];
      state.assignedIdsLocal[next] = tmp;
      renderAssigned();
      renderUnassigned();
    }

    async function saveOrder(){
      if (!canWrite) return;
      if (!state.deptKey) return;

      const isGlobal = state.deptKey === GLOBAL_KEY;
      const conf = await Swal.fire({
        title: 'Save placement officer order?',
        text: isGlobal
          ? 'This will update the saved JSON array for All Departments (global scope). Empty list is allowed.'
          : 'This will update the saved JSON array for the selected department. Empty list is allowed.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Save',
      });
      if (!conf.isConfirmed) return;

      showLoading(true);
      setBtnLoading(btnSave, true);

      try{
        const payload = {
          placement_officer_ids: state.assignedIdsLocal.map(Number) // can be [] now
        };

        const res = await fetchWithTimeout(API.save(state.deptKey), {
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

        ok('Saved successfully');
        await loadDeptData(state.deptKey);
      }catch(e){
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }finally{
        setBtnLoading(btnSave, false);
        showLoading(false);
      }
    }

    async function resetLocal(){
      if (!state.deptKey) return;
      const conf = await Swal.fire({
        title: 'Discard local changes?',
        text: 'This will reset to the last saved order from the server.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Discard'
      });
      if (!conf.isConfirmed) return;

      state.assignedIdsLocal = (state.assignedIdsServer || []).slice();
      renderAll();
      ok('Changes discarded');
    }

    // =========================
    // Events
    // =========================
    deptSelect?.addEventListener('change', () => {
      const v = (deptSelect.value || '').trim();
      loadDeptData(v);
    });

    btnReload?.addEventListener('click', async () => {
      if (!state.deptKey) { ok('Select a department or All Departments'); return; }
      showLoading(true);
      await loadDeptData(state.deptKey);
      showLoading(false);
      ok('Reloaded');
    });

    btnResetLocal?.addEventListener('click', resetLocal);
    btnSave?.addEventListener('click', saveOrder);

    assignedSearch?.addEventListener('input', debounce(() => {
      state.qAssigned = (assignedSearch.value || '').trim();
      renderAssigned();
    }, 220));

    unassignedSearch?.addEventListener('input', debounce(() => {
      state.qUnassigned = (unassignedSearch.value || '').trim();
      renderUnassigned();
    }, 220));

    document.addEventListener('click', (e) => {
      const btn = e.target.closest('button[data-action], button[data-move]');
      if (!btn) return;

      const li = btn.closest('.popo-item');
      const id = li?.dataset?.id;
      if (!id) return;

      const act = btn.dataset.action;
      const mv = btn.dataset.move;

      if (act === 'assign') { if (canWrite) assignUser(id); return; }
      if (act === 'unassign') { if (canWrite) unassignUser(id); return; }

      if (mv === 'up' || mv === 'down'){
        moveAssigned(id, mv);
        return;
      }
    });

    // =========================
    // Init
    // =========================
    (async () => {
      showLoading(true);
      try{
        await fetchMe();
        await loadDepartments();

        assignedEmpty.style.display = '';
        unassignedEmpty.style.display = '';
        updateChips();
        updateButtons();
      }finally{
        showLoading(false);
      }
    })();
  });
})();
</script>
@endpush