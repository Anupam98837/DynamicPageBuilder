{{-- resources/views/modules/technical_assistant/technicalAssistantPreviewOrder.blade.php --}}
@section('title','Technical Assistant Preview Order')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
  Technical Assistant Preview Order (Admin)
  - Same UI as Faculty Preview Order
  - Dept select -> Assigned/Unassigned tabs
  - Drag reorder + Up/Down controls
========================= */

.fpo-wrap{max-width:1200px;margin:18px auto 48px;padding:0 12px;overflow:visible}
.fpo-wrap .tab-content,.fpo-wrap .tab-pane{overflow:visible}

/* Cards */
.fpo-card{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.fpo-card .card-header{
  background:transparent;
  border-bottom:1px solid var(--line-soft);
}
.fpo-title{margin:0;font-weight:800}
.fpo-helper{font-size:12.5px;color:var(--muted-color)}
.fpo-small{font-size:12.5px}

/* Chips */
.fpo-chip{
  display:inline-flex;align-items:center;gap:8px;
  padding:8px 10px;border-radius:999px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 90%, transparent);
  font-size:12.5px;
}
.fpo-chip i{opacity:.75}

/* Tabs */
.fpo-wrap .nav.nav-tabs{border-color:var(--line-strong)}
.fpo-wrap .nav-tabs .nav-link{color:var(--ink)}
.fpo-wrap .nav-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface);
}

/* Toolbar */
.fpo-toolbar{
  display:flex;flex-wrap:wrap;gap:10px;
  align-items:center;justify-content:space-between;
}
.fpo-toolbar-left{display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.fpo-toolbar-right{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
.fpo-select{min-width:260px;}
.fpo-search{min-width:260px;}
@media (max-width: 768px){
  .fpo-select,.fpo-search{min-width:100%}
  .fpo-toolbar-right .btn{flex:1;min-width:160px}
}

/* Lists */
.fpo-list{
  list-style:none;
  margin:0;
  padding:0;
  display:flex;
  flex-direction:column;
  gap:10px;
}
.fpo-item{
  border:1px solid var(--line-soft);
  border-radius:14px;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  padding:10px 10px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
}
.fpo-item:hover{background:var(--page-hover)}
.fpo-left{display:flex;align-items:center;gap:10px;min-width:0;}
.fpo-handle{
  width:34px;height:34px;border-radius:10px;
  display:flex;align-items:center;justify-content:center;
  border:1px dashed var(--line-strong);
  color:var(--muted-color);
  cursor:grab;
  flex:0 0 auto;
}
.fpo-handle:active{cursor:grabbing}
.fpo-rank{
  width:32px;height:32px;border-radius:10px;
  display:flex;align-items:center;justify-content:center;
  background:color-mix(in oklab, var(--primary-color) 10%, transparent);
  color:var(--primary-color);
  font-weight:800;
  flex:0 0 auto;
}
.fpo-avatar{
  width:38px;height:38px;border-radius:12px;
  background:color-mix(in oklab, var(--muted-color) 12%, transparent);
  display:flex;align-items:center;justify-content:center;
  color:var(--muted-color);
  flex:0 0 auto;
  overflow:hidden;
}
.fpo-avatar img{width:100%;height:100%;object-fit:cover}
.fpo-meta{min-width:0}
.fpo-name{
  font-weight:800;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.fpo-sub{
  font-size:12.5px;color:var(--muted-color);
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.fpo-right{display:flex;align-items:center;gap:8px;flex:0 0 auto;}
.fpo-move{display:flex;gap:6px;}
.fpo-move .btn{padding:.25rem .45rem;border-radius:10px;}
.fpo-actions .btn{border-radius:10px}

/* Drag visuals */
.fpo-item[draggable="true"]{user-select:none}
.fpo-item.fpo-dragging{opacity:.55;transform:scale(.995)}
.fpo-item.fpo-over{outline:2px solid color-mix(in oklab, var(--primary-color) 35%, transparent)}

/* Empty state */
.fpo-empty{
  border:1px dashed var(--line-strong);
  border-radius:16px;
  padding:22px;
  text-align:center;
  color:var(--muted-color);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.fpo-empty i{font-size:30px;opacity:.6}

/* Loading overlay */
.fpo-loading{
  position:fixed;inset:0;
  background:rgba(0,0,0,0.45);
  display:flex;justify-content:center;align-items:center;
  z-index:9999;
  backdrop-filter:blur(2px)
}
.fpo-loading-card{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;flex-direction:column;align-items:center;gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,0.3)
}
.fpo-spinner{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,0.3);
  border-top:4px solid var(--primary-color);
  animation:fpoSpin 1s linear infinite
}
@keyframes fpoSpin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

/* Button loading */
.fpo-btn-loading{position:relative;color:transparent !important}
.fpo-btn-loading::after{
  content:'';
  position:absolute;
  width:16px;height:16px;
  top:50%;left:50%;
  margin:-8px 0 0 -8px;
  border:2px solid transparent;
  border-top:2px solid currentColor;
  border-radius:50%;
  animation:fpoSpin 1s linear infinite
}
</style>
@endpush

@section('content')
<div class="fpo-wrap">

  {{-- Loading Overlay --}}
  <div id="fpoLoading" class="fpo-loading" style="display:none;">
    <div class="fpo-loading-card">
      <div class="fpo-spinner"></div>
      <div class="fpo-small">Loading…</div>
    </div>
  </div>

  {{-- Header --}}
  <div class="card fpo-card mb-3">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div>
        <div class="d-flex align-items-center gap-2">
          <i class="fa-solid fa-list-ol" style="opacity:.75;"></i>
          <h5 class="fpo-title">Technical Assistant Preview Order</h5>
        </div>
        <div class="fpo-helper mt-1">
          Select a department, then assign/unassign technical assistants and reorder the “Assigned Users” list (saved as an ordered JSON array of technical assistant IDs).
        </div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <span class="fpo-chip"><i class="fa-solid fa-shield-halved"></i> Admin module</span>
        <span class="fpo-chip"><i class="fa-solid fa-arrows-up-down-left-right"></i> Drag reorder</span>
        <span class="fpo-chip"><i class="fa-solid fa-code"></i> JSON order</span>
      </div>
    </div>
  </div>

  {{-- Top Controls --}}
  <div class="card fpo-card mb-3">
    <div class="card-body">
      <div class="fpo-toolbar">
        <div class="fpo-toolbar-left">
          <div>
            <label class="form-label mb-1">Department</label>
            <select id="deptSelect" class="form-select fpo-select">
              <option value="">Loading departments…</option>
            </select>
            <div class="fpo-helper mt-1">Users are loaded for the selected department.</div>
          </div>

          <div class="d-flex flex-wrap gap-2 align-items-end">
            <span class="fpo-chip"><i class="fa-solid fa-users"></i><span id="chipTotal">Total: —</span></span>
            <span class="fpo-chip"><i class="fa-solid fa-user-check"></i><span id="chipAssigned">Assigned: —</span></span>
            <span class="fpo-chip"><i class="fa-solid fa-user-plus"></i><span id="chipUnassigned">Unassigned: —</span></span>
          </div>
        </div>

        <div class="fpo-toolbar-right" id="writeControls" style="display:none;">
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
      <div class="card fpo-card">
        <div class="card-header py-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
              <div class="fw-bold"><i class="fa-solid fa-grip-lines me-2"></i>Reorder Assigned Technical Assistants</div>
              <div class="fpo-helper mt-1">Drag to reorder, or use ↑/↓ buttons (mobile-friendly). Removing moves to Unassigned.</div>
            </div>
            <div style="min-width:260px;">
              <input id="assignedSearch" class="form-control fpo-search" type="search" placeholder="Search assigned…">
            </div>
          </div>
        </div>

        <div class="card-body">
          <div id="assignedEmpty" class="fpo-empty" style="display:none;">
            <i class="fa-regular fa-folder-open mb-2"></i>
            <div class="fw-semibold">No assigned users</div>
            <div class="fpo-small">Assign users from the “Unassigned Users” tab.</div>
          </div>

          <ul id="assignedList" class="fpo-list"></ul>
        </div>
      </div>
    </div>

    {{-- Unassigned --}}
    <div class="tab-pane fade" id="tab-unassigned" role="tabpanel">
      <div class="card fpo-card">
        <div class="card-header py-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
              <div class="fw-bold"><i class="fa-solid fa-user-plus me-2"></i>Available Technical Assistants (Unassigned)</div>
              <div class="fpo-helper mt-1">Assigning will append the user to the end of Assigned list.</div>
            </div>
            <div style="min-width:260px;">
              <input id="unassignedSearch" class="form-control fpo-search" type="search" placeholder="Search unassigned…">
            </div>
          </div>
        </div>

        <div class="card-body">
          <div id="unassignedEmpty" class="fpo-empty" style="display:none;">
            <i class="fa-regular fa-circle-check mb-2"></i>
            <div class="fw-semibold">No unassigned users</div>
            <div class="fpo-small">All eligible users are assigned.</div>
          </div>

          <ul id="unassignedList" class="fpo-list"></ul>
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
  if (window.__TA_PREVIEW_ORDER_INIT__) return;
  window.__TA_PREVIEW_ORDER_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=250) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

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

  async function safeJson(res){
    const ct = (res.headers.get('content-type') || '').toLowerCase();
    if (ct.includes('application/json')) return await res.json().catch(()=> ({}));
    const txt = await res.text().catch(()=> '');
    return { __non_json: true, __text: txt, __content_type: ct };
  }

  function setBtnLoading(btn, loading){
    if (!btn) return;
    btn.disabled = !!loading;
    btn.classList.toggle('fpo-btn-loading', !!loading);
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    // =========================
    // ✅ API MAP (TechnicalAssistantPreviewOrderController)
    // =========================
    const API = {
      departments: '/api/departments',
      byDept: (deptKey) => `/api/technical-assistant-preview-order/${encodeURIComponent(deptKey)}`,
      save: (deptKey) => `/api/technical-assistant-preview-order/${encodeURIComponent(deptKey)}/save`,
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

    const fpoLoading = $('fpoLoading');
    const showLoading = (v) => { if (fpoLoading) fpoLoading.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('toastSuccess');
    const toastErrEl = $('toastError');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;

    const ok = (m) => { const el=$('toastSuccessText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('toastErrorText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    function handleAuth(res, js){
      // ✅ redirect only on 401 (expired/invalid token)
      if (res.status === 401){
        window.location.href = '/';
        return true;
      }
      // ✅ do NOT redirect on 403 (permissions); just show toast
      if (res.status === 403){
        err(js?.message || 'Forbidden: you do not have permission.');
        return true;
      }
      // ✅ if API returned HTML (often due to middleware redirect), show error instead of breaking
      if (js && js.__non_json){
        err('Unexpected response (not JSON). Check API middleware/auth for this route.');
        return true;
      }
      return false;
    }

    async function fetchMe(){
      try{
        const res = await fetchWithTimeout(API.me, { headers: authHeaders(false) }, 9000);
        const js = await safeJson(res);
        if (handleAuth(res, js)) return;

        if (res.ok){
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

    // extra safety (backend already filters to TA roles)
    const EXCLUDE_ROLES = new Set(['admin','director','student','students','super_admin']);

    function normDeptList(js){
      const arr = js?.data || js?.departments || js?.items || [];
      return Array.isArray(arr) ? arr : [];
    }

    function normUsers(arr){
      const a = Array.isArray(arr) ? arr : [];
      return a.map(u => ({
        id: u.id ?? u.user_id ?? u.uid,
        uuid: u.uuid ?? u.user_uuid ?? null,
        name: u.name ?? u.full_name ?? u.title ?? '—',
        email: u.email ?? '',
        role: (u.role ?? u.user_role ?? '').toString(),
        avatar: u.avatar_url ?? u.photo_url ?? u.image_url ?? u.image ?? null,
        meta: u
      })).filter(x => x.id !== undefined && x.id !== null);
    }

    function uniqById(list){
      const map = new Map();
      (list || []).forEach(u => {
        const id = Number(u?.id);
        if (!Number.isFinite(id)) return;
        if (!map.has(id)) map.set(id, u);
      });
      return Array.from(map.values());
    }

    function eligibleFilter(u){
      const r = (u.role || '').toLowerCase();
      if (!r) return true;
      return !EXCLUDE_ROLES.has(r);
    }

    function normDeptPayload(js){
      // Controller returns:
      // { success:true, order:{ technical_assistant_ids:[] }, assigned:[], unassigned:[] }
      const root = js?.data || js || {};
      const order = root.order || {};
      const ids = order.technical_assistant_ids || root.technical_assistant_ids || root.assigned_ids || [];

      const assigned = normUsers(root.assigned || root.assigned_users || []);
      const unassigned = normUsers(root.unassigned || root.unassigned_users || []);

      return {
        ids: Array.isArray(ids) ? ids.map(x => Number(x)).filter(n => Number.isFinite(n)) : [],
        assigned,
        unassigned
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
      const hasDept = !!state.deptKey;
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
      if (u.avatar) return `<img src="${esc(u.avatar)}" alt="">`;
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
        li.className = 'fpo-item';
        li.setAttribute('draggable','true');
        li.dataset.id = String(u.id);

        li.innerHTML = `
          <div class="fpo-left">
            <div class="fpo-handle" title="Drag to reorder"><i class="fa-solid fa-grip-lines"></i></div>
            <div class="fpo-rank" title="Position">${idx+1}</div>
            <div class="fpo-avatar" title="User">${avatarHtml(u)}</div>
            <div class="fpo-meta">
              <div class="fpo-name">${esc(u.name)}</div>
              <div class="fpo-sub">
                ${esc(u.email || '—')}
                ${u.role ? ` • <span class="text-muted">${esc(u.role)}</span>` : ``}
              </div>
            </div>
          </div>

          <div class="fpo-right">
            <div class="fpo-move">
              <button type="button" class="btn btn-light btn-sm" data-move="up" title="Move up"><i class="fa-solid fa-arrow-up"></i></button>
              <button type="button" class="btn btn-light btn-sm" data-move="down" title="Move down"><i class="fa-solid fa-arrow-down"></i></button>
            </div>
            <div class="fpo-actions">
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
        li.className = 'fpo-item';
        li.dataset.id = String(u.id);

        li.innerHTML = `
          <div class="fpo-left">
            <div class="fpo-avatar">${avatarHtml(u)}</div>
            <div class="fpo-meta">
              <div class="fpo-name">${esc(u.name)}</div>
              <div class="fpo-sub">
                ${esc(u.email || '—')}
                ${u.role ? ` • <span class="text-muted">${esc(u.role)}</span>` : ``}
              </div>
            </div>
          </div>

          <div class="fpo-right">
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
      const ids = Array.from(assignedList.querySelectorAll('.fpo-item'))
        .map(li => Number(li.dataset.id))
        .filter(n => Number.isFinite(n));
      state.assignedIdsLocal = ids;
      updateButtons();
      updateChips();
    }

    function initDnD(){
      assignedList.querySelectorAll('.fpo-item[draggable="true"]').forEach(item => {
        item.addEventListener('dragstart', (e) => {
          dragEl = item;
          item.classList.add('fpo-dragging');
          e.dataTransfer.effectAllowed = 'move';
          try{ e.dataTransfer.setData('text/plain', item.dataset.id); }catch(_){}
        });

        item.addEventListener('dragend', () => {
          item.classList.remove('fpo-dragging');
          assignedList.querySelectorAll('.fpo-item').forEach(x => x.classList.remove('fpo-over'));
          dragEl = null;
          idsFromDom();
          renderAssigned();
        });

        item.addEventListener('dragover', (e) => {
          e.preventDefault();
          e.dataTransfer.dropEffect = 'move';
          if (!dragEl || dragEl === item) return;
          item.classList.add('fpo-over');

          const rect = item.getBoundingClientRect();
          const after = (e.clientY - rect.top) > (rect.height / 2);

          if (after) {
            if (item.nextSibling !== dragEl) assignedList.insertBefore(dragEl, item.nextSibling);
          } else {
            if (item !== dragEl.nextSibling) assignedList.insertBefore(dragEl, item);
          }
        });

        item.addEventListener('dragleave', () => item.classList.remove('fpo-over'));
        item.addEventListener('drop', (e) => { e.preventDefault(); item.classList.remove('fpo-over'); });
      });
    }

    // =========================
    // Data Loading
    // =========================
    async function loadDepartments(){
      if (!deptSelect) return;
      deptSelect.innerHTML = `<option value="">Loading departments…</option>`;

      try{
        const res = await fetchWithTimeout(API.departments, { headers: authHeaders(false) }, 15000);
        const js = await safeJson(res);
        if (handleAuth(res, js)) return;

        if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to load departments');

        const list = normDeptList(js);
        state.depts = list;

        if (!list.length){
          deptSelect.innerHTML = `<option value="">No departments found</option>`;
          return;
        }

        // ✅ store BOTH uuid + id (fallback)
        deptSelect.innerHTML =
          `<option value="">Select department…</option>` +
          list.map(d => {
            const uuid = d.uuid ?? '';
            const id   = d.id ?? '';
            const value = uuid || id; // primary (same as faculty)
            const name = d.name || d.title || d.department_name || 'Department';
            return `<option value="${esc(value)}" data-id="${esc(id)}" data-uuid="${esc(uuid)}">${esc(name)}</option>`;
          }).join('');
      }catch(e){
        deptSelect.innerHTML = `<option value="">Failed to load departments</option>`;
        err(e?.name === 'AbortError' ? 'Request timed out' : (e.message || 'Failed'));
      }
    }

    async function loadDeptData(deptKey, fallbackKey=null){
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
        const js = await safeJson(res);

        // auth / permissions / non-json handling
        if (res.status === 401){
          window.location.href = '/';
          return;
        }
        if (res.status === 403){
          // ✅ do not redirect; just show error
          err(js?.message || 'Forbidden: you do not have permission.');
          assignedEmpty.style.display = '';
          unassignedEmpty.style.display = '';
          return;
        }

        // ✅ fallback if controller expects a different key (uuid vs id)
        if ((res.status === 404 || res.status === 422) && fallbackKey && fallbackKey !== deptKey){
          await loadDeptData(fallbackKey, null);
          return;
        }

        if (js && js.__non_json){
          throw new Error('Unexpected response (not JSON). Check API middleware/auth for this route.');
        }

        if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to load department data');

        const p = normDeptPayload(js);

        const combined = uniqById([...(p.assigned||[]), ...(p.unassigned||[])]);
        const eligible = combined.filter(eligibleFilter);

        state.allUsers = eligible;
        buildUsersMap(eligible);

        const existingSet = new Set(eligible.map(u => Number(u.id)));

        const sanitized = (p.ids || []).filter(id => existingSet.has(Number(id)));

        state.assignedIdsServer = sanitized.slice();
        state.assignedIdsLocal  = sanitized.slice();

        if (!state.assignedIdsLocal.length && (p.assigned||[]).length){
          const inferred = (p.assigned||[])
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

      const conf = await Swal.fire({
        title: 'Save technical assistant order?',
        text: 'This will update the saved JSON array for the selected department.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Save',
      });
      if (!conf.isConfirmed) return;

      showLoading(true);
      setBtnLoading(btnSave, true);

      try{
        const payload = { technical_assistant_ids: state.assignedIdsLocal.map(Number) };

        const res = await fetchWithTimeout(API.save(state.deptKey), {
          method: 'POST',
          headers: authHeaders(true),
          body: JSON.stringify(payload)
        }, 20000);

        const js = await safeJson(res);

        if (res.status === 401){ window.location.href = '/'; return; }
        if (res.status === 403){ err(js?.message || 'Forbidden: you do not have permission.'); return; }
        if (js && js.__non_json) throw new Error('Unexpected response (not JSON). Check API middleware/auth for this route.');

        if (!res.ok || js.success === false){
          let msg = js?.message || 'Save failed';
          if (js?.errors){
            const k = Object.keys(js.errors)[0];
            if (k && js.errors[k] && js.errors[k][0]) msg = js.errors[k][0];
          }
          throw new Error(msg);
        }

        ok('Saved successfully');

        // reload with same key (no fallback needed)
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

    // ✅ capture-phase handler to stop any global scripts/form auto-submit
    deptSelect?.addEventListener('change', (e) => {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();

      const opt = deptSelect.options[deptSelect.selectedIndex];
      const v = (deptSelect.value || '').trim();

      // fallback: if value is uuid and controller expects id (or vice versa)
      const fallbackKey = (opt?.dataset?.id || '').trim() || (opt?.dataset?.uuid || '').trim() || null;

      loadDeptData(v, (fallbackKey && fallbackKey !== v) ? fallbackKey : null);
    }, true);

    // also guard if select sits inside a form anywhere (rare, but safe)
    const maybeForm = deptSelect?.closest('form');
    if (maybeForm){
      maybeForm.addEventListener('submit', (e) => {
        e.preventDefault();
        e.stopPropagation();
      });
    }

    btnReload?.addEventListener('click', async () => {
      if (!state.deptKey) { ok('Select a department'); return; }
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

      const li = btn.closest('.fpo-item');
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
