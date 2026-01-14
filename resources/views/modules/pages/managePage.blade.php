{{-- resources/views/modules/managePages.blade.php --}}

@section('title','Manage Pages')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* =========================
 * Manage Pages - Admin UI (reference-aligned)
 * Fixes:
 *  ✅ Action dropdown opens (not clipped / not blocked)
 *  ✅ Pagination works reliably (same approach as Manage Users)
 *  ✅ Results meta shows correct range
 * ========================= */

.mp-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible;padding:0 4px}

/* Tabs */
.mp-tabs.nav-tabs{border-color:var(--line-strong)}
.mp-tabs .nav-link{color:var(--ink)}
.mp-tabs .nav-link.active{
  background:var(--surface);
  border-color:var(--line-strong) var(--line-strong) var(--surface);
}

/* Card/Table */
.mp-card{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.mp-card .card-body{overflow:visible}
.mp-table{--bs-table-bg:transparent}
.mp-table thead th{
  font-weight:650;
  color:var(--muted-color);
  font-size:13px;
  border-bottom:1px solid var(--line-strong);
  background:var(--surface);
}
.mp-table thead.sticky-top{z-index:3}
.mp-table tbody tr{border-top:1px solid var(--line-soft)}
.mp-table tbody tr:hover{background:var(--page-hover)}
.mp-muted{color:var(--muted-color)}
.mp-small{font-size:12.5px}

/* Horizontal scroll (like reference) */
.mp-table-responsive{
  display:block;
  width:100%;
  max-width:100%;
  overflow-x:auto !important;
  overflow-y:visible !important;
  -webkit-overflow-scrolling:touch;
  position:relative;
  z-index:0; /* ✅ helps stacking contexts */
}
.mp-table-responsive > table{width:max-content; min-width:1050px;}
.mp-table-responsive th,.mp-table-responsive td{white-space:nowrap;}

/* Toolbar */
.mp-toolbar{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  padding:12px 12px;
}
.mp-toolbar .mp-search{min-width:280px; position:relative;}
.mp-toolbar .mp-search input{padding-left:40px;}
.mp-toolbar .mp-search i{
  position:absolute; left:12px; top:50%;
  transform:translateY(-50%); opacity:.6;
}
.mp-toolbar .form-control,.mp-toolbar .form-select{height:40px;border-radius:12px}

/* Department filter width */
#mpDept{min-width:220px}
@media (max-width:768px){
  #mpDept{min-width:160px}
  .mp-toolbar .mp-row{flex-direction:column; align-items:stretch !important;}
  .mp-toolbar .mp-search{min-width:100%;}
  .mp-toolbar .mp-actions{display:flex; gap:8px; flex-wrap:wrap;}
  .mp-toolbar .mp-actions .btn{flex:1; min-width:150px;}
}

/* Badges */
.badge-success{background:#16a34a!important}
.badge-secondary{background:#64748b!important}

/* ✅ Dropdown (fixed like reference) */
.mp-table-responsive .dropdown{position:relative; z-index:5;} /* ✅ */
.mp-dd-toggle{border-radius:10px}
.dropdown-menu{
  border-radius:12px;
  border:1px solid var(--line-strong);
  box-shadow:var(--shadow-2);
  min-width:230px;
  z-index:999999; /* ✅ stronger */
}
.dropdown-menu.show{display:block !important}
.dropdown-item{display:flex;align-items:center;gap:.6rem}
.dropdown-item i{width:16px;text-align:center}
.dropdown-item.text-danger{color:var(--danger-color) !important}

/* Loading overlay */
.mp-loading{
  position:fixed; inset:0;
  background:rgba(0,0,0,.45);
  display:none;
  align-items:center;
  justify-content:center;
  z-index:9999;
  backdrop-filter:blur(2px);
}
.mp-loading .box{
  background:var(--surface);
  padding:18px 20px;
  border-radius:14px;
  display:flex;
  align-items:center;
  gap:12px;
  box-shadow:0 10px 26px rgba(0,0,0,.3);
}
.mp-spin{
  width:38px;height:38px;border-radius:50%;
  border:4px solid rgba(148,163,184,.3);
  border-top:4px solid var(--primary-color);
  animation:mpSpin 1s linear infinite;
}
@keyframes mpSpin{to{transform:rotate(360deg)}}
</style>
@endpush

@section('content')
<div class="mp-wrap">

  {{-- Global Loading --}}
  <div id="mpLoading" class="mp-loading" aria-hidden="true">
    <div class="box">
      <div class="mp-spin"></div>
      <div class="mp-small">Loading…</div>
    </div>
  </div>

  {{-- Top Toolbar (applies to active tab filters) --}}
  <div class="mp-toolbar mb-3">
    <div class="d-flex align-items-center justify-content-between gap-2 mp-row">
      <div class="d-flex align-items-center flex-wrap gap-2">
        <div class="mp-search">
          <i class="fa fa-search"></i>
          <input id="mpQ" class="form-control" placeholder="Search title / slug" />
        </div>

        <select id="mpStatus" class="form-select" style="width:160px">
          <option value="">All Status</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>

        {{-- ✅ Department filter --}}
        <select id="mpDept" class="form-select">
          <option value="">All Departments</option>
        </select>

        <button id="mpBtnFilter" class="btn btn-outline-primary">
          <i class="fa fa-sliders me-1"></i> Filter
        </button>
        <button id="mpBtnReset" class="btn btn-light">
          <i class="fa fa-rotate-left me-1"></i> Reset
        </button>
      </div>

      <div class="mp-actions">
        <button id="mpBtnCreate" class="btn btn-primary">
          <i class="fa fa-plus me-1"></i> New Page
        </button>
      </div>
    </div>
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs mp-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#mpTabActive" role="tab" aria-selected="true">
        <i class="fa-solid fa-file-lines me-2"></i>Pages
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#mpTabArchived" role="tab" aria-selected="false">
        <i class="fa-solid fa-box-archive me-2"></i>Archived
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#mpTabBin" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Bin
      </a>
    </li>
  </ul>

  <div class="tab-content">

    {{-- ACTIVE --}}
    <div class="tab-pane fade show active" id="mpTabActive" role="tabpanel">
      <div class="card mp-card">
        <div class="card-body p-0">
          <div class="mp-table-responsive">
            <table class="table mp-table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th>Slug</th>
                  <th>Shortcode</th>
                  <th>Status</th>
                  <th>Published</th>
                  <th class="text-end" style="width:108px;">Actions</th>
                </tr>
              </thead>
              <tbody id="mpRowsActive">
                <tr><td colspan="6" class="text-center mp-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="mpEmptyActive" class="p-4 text-center" style="display:none;">
            <i class="fa fa-file-lines mb-2" style="font-size:32px;opacity:.6;"></i>
            <div class="mp-muted">No pages found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="mp-small mp-muted" id="mpMetaActive">—</div>
            <nav><ul id="mpPagerActive" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ARCHIVED --}}
    <div class="tab-pane fade" id="mpTabArchived" role="tabpanel">
      <div class="card mp-card">
        <div class="card-body p-0">
          <div class="mp-table-responsive">
            <table class="table mp-table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th>Slug</th>
                  <th>Archived At</th>
                  <th class="text-end" style="width:108px;">Actions</th>
                </tr>
              </thead>
              <tbody id="mpRowsArchived">
                <tr><td colspan="4" class="text-center mp-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="mpEmptyArchived" class="p-4 text-center" style="display:none;">
            <i class="fa fa-box-archive mb-2" style="font-size:32px;opacity:.6;"></i>
            <div class="mp-muted">No archived pages.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="mp-small mp-muted" id="mpMetaArchived">—</div>
            <nav><ul id="mpPagerArchived" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- BIN --}}
    <div class="tab-pane fade" id="mpTabBin" role="tabpanel">
      <div class="card mp-card">
        <div class="card-body p-0">
          <div class="mp-table-responsive">
            <table class="table mp-table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>Title</th>
                  <th>Deleted At</th>
                  <th class="text-end" style="width:108px;">Actions</th>
                </tr>
              </thead>
              <tbody id="mpRowsBin">
                <tr><td colspan="3" class="text-center mp-muted" style="padding:38px;">Loading…</td></tr>
              </tbody>
            </table>
          </div>

          <div id="mpEmptyBin" class="p-4 text-center" style="display:none;">
            <i class="fa fa-trash-can mb-2" style="font-size:32px;opacity:.6;"></i>
            <div class="mp-muted">Bin is empty.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="mp-small mp-muted" id="mpMetaBin">—</div>
            <nav><ul id="mpPagerBin" class="pagination mb-0"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
(() => {
  if (window.__MANAGE_PAGES_INIT__) return;
  window.__MANAGE_PAGES_INIT__ = true;

  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token');
  if (!TOKEN) { location.href='/'; return; }

  const $ = (id) => document.getElementById(id);

  const showLoading = (v) => {
    const el = $('mpLoading');
    if (el) el.style.display = v ? 'flex' : 'none';
  };

  const esc = (s) => (s||'').toString().replace(/[&<>"']/g,m=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
  }[m]));

  const badge = (s) =>
    `<span class="badge ${String(s)==='Active'?'badge-success':'badge-secondary'}">${esc(s||'-')}</span>`;

  const api = async (url, opt={}) => {
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), 15000);
    try{
      const r = await fetch(url,{
        ...opt,
        signal: ctrl.signal,
        headers:{
          'Authorization':'Bearer '+TOKEN,
          'Accept':'application/json',
          ...(opt.headers||{})
        }
      });

      const ct = r.headers.get('content-type') || '';
      let j = {};
      if(ct.includes('application/json')) j = await r.json().catch(()=>({}));
      else j = { message: await r.text().catch(()=> '') };

      if(!r.ok){
        const msg = j?.message || j?.error || ('HTTP ' + r.status);
        const e = new Error(msg);
        e.status = r.status;
        e.payload = j;
        throw e;
      }
      return j;
    } finally {
      clearTimeout(t);
    }
  };

  const state = {
    active:   { page: 1, lastPage: 1, total: 0, perPage: 10 },
    archived: { page: 1, lastPage: 1, total: 0, perPage: 10 },
    bin:      { page: 1, lastPage: 1, total: 0, perPage: 10 },
    departments: []
  };

  /* ================== PAGINATION NORMALIZER (same concept as Manage Users) ================== */
  function pickPagination(j){
    const pag = j?.pagination || j?.meta || j?.paginate || j?.page || {};
    const current = parseInt(pag.current_page ?? pag.currentPage ?? j?.current_page ?? 1, 10) || 1;
    const last    = parseInt(pag.last_page ?? pag.lastPage ?? j?.last_page ?? 1, 10) || 1;
    const per     = parseInt(pag.per_page ?? pag.perPage ?? j?.per_page ?? 10, 10) || 10;
    const total   = parseInt(pag.total ?? j?.total ?? 0, 10) || 0;

    const from = (pag.from !== undefined && pag.from !== null) ? parseInt(pag.from,10) : null;
    const to   = (pag.to   !== undefined && pag.to   !== null) ? parseInt(pag.to,10)   : null;

    return { current_page: current, last_page: Math.max(1,last), per_page: per, total, from, to };
  }

  function computeMetaText(pg, rowsLen){
    const total = (pg.total && pg.total > 0) ? pg.total : rowsLen;
    if (total <= 0) return '0 item(s)';

    const from = (pg.from && pg.from > 0) ? pg.from : ((pg.current_page - 1) * pg.per_page + 1);
    const to   = (pg.to   && pg.to   > 0) ? pg.to   : Math.min(total, (pg.current_page - 1) * pg.per_page + rowsLen);

    return `Showing ${from}–${to} of ${total}`;
  }

  /* ================== DEPARTMENTS ================== */
  function deptLabel(d){
    return d?.title || d?.name || d?.slug || (d?.id ? `Department #${d.id}` : 'Department');
  }

  async function loadDepartments(){
    const deptSel = $('mpDept');
    if(!deptSel) return;

    try{
      const j = await api('/api/departments?per_page=200');
      const arr = Array.isArray(j.data) ? j.data : (Array.isArray(j.departments) ? j.departments : []);
      state.departments = arr || [];

      let html = `<option value="">All Departments</option>`;
      state.departments.forEach(d=>{
        const id = d?.id;
        if(id === undefined || id === null) return;
        html += `<option value="${esc(String(id))}">${esc(deptLabel(d))}</option>`;
      });
      deptSel.innerHTML = html;
    }catch(e){
      console.warn('Departments load failed:', e);
      deptSel.innerHTML = `<option value="">All Departments</option>`;
    }
  }

  /* ================== PAGINATION RENDER (same behavior as Manage Users) ================== */
  function renderPager(which){
    const st = state[which];
    const pager = which === 'active' ? $('mpPagerActive') : (which === 'archived' ? $('mpPagerArchived') : $('mpPagerBin'));
    if (!pager) return;

    const page = st.page || 1;
    const totalPages = st.lastPage || 1;

    const item = (p, label, dis=false, act=false) => {
      if (dis) return `<li class="page-item disabled"><span class="page-link">${label}</span></li>`;
      if (act) return `<li class="page-item active"><span class="page-link">${label}</span></li>`;
      return `<li class="page-item"><a class="page-link" href="#" data-page="${p}" data-which="${which}">${label}</a></li>`;
    };

    let html = '';
    html += item(Math.max(1, page-1), 'Previous', page<=1);

    const start = Math.max(1, page-2);
    const end   = Math.min(totalPages, page+2);
    for (let p=start; p<=end; p++) html += item(p, String(p), false, p===page);

    html += item(Math.min(totalPages, page+1), 'Next', page>=totalPages);

    pager.innerHTML = (totalPages <= 1) ? '' : html;
  }

  /* ================== ✅ DROPDOWN FIX (Bootstrap + Fallback, no silent failure) ================== */
  function closeAllDropdownsExcept(exceptToggle){
    document.querySelectorAll('.mp-dd-toggle').forEach(t => {
      if (t === exceptToggle) return;

      // Bootstrap close if available
      if (window.bootstrap?.Dropdown){
        try{
          const inst = window.bootstrap.Dropdown.getInstance(t);
          inst && inst.hide();
        }catch(_){}
        return;
      }

      // Fallback close
      const menu = t.closest('.dropdown')?.querySelector('.dropdown-menu');
      if (menu) menu.classList.remove('show');
    });
  }

  // Close dropdowns when clicking outside, but do NOT hijack pagination click.
  document.addEventListener('pointerdown', (e) => {
    if (e.target.closest('a.page-link[data-page]')) return;
    if (e.target.closest('.mp-dd-toggle')) return;
    if (e.target.closest('.dropdown-menu')) return;
    closeAllDropdownsExcept(null);
  }, { capture:true });

  // Dropdown toggle (works even if bootstrap is missing/overridden)
  document.addEventListener('click', (e) => {
    const toggle = e.target.closest('.mp-dd-toggle');
    if (!toggle) return;

    e.preventDefault();
    e.stopPropagation();

    closeAllDropdownsExcept(toggle);

    // ✅ Use Bootstrap if present
    if (window.bootstrap?.Dropdown){
      try{
        const inst = window.bootstrap.Dropdown.getOrCreateInstance(toggle, {
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
        return;
      }catch(_){}
    }

    // ✅ Fallback toggle
    const menu = toggle.closest('.dropdown')?.querySelector('.dropdown-menu');
    if (menu) menu.classList.toggle('show');
  });

  /* ================== EMPTY HELPERS ================== */
  function setEmpty(which, show){
    const el = which === 'active' ? $('mpEmptyActive') : (which === 'archived' ? $('mpEmptyArchived') : $('mpEmptyBin'));
    if (el) el.style.display = show ? '' : 'none';
  }

  /* ================== PAGINATION CLICK ================== */
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a.page-link[data-page]');
    if (!a) return;

    e.preventDefault();
    e.stopPropagation();

    const which = a.dataset.which;
    const p = parseInt(a.dataset.page, 10);
    if (!which || Number.isNaN(p)) return;

    const maxP = state[which]?.lastPage || 1;
    const nextP = Math.min(Math.max(1, p), maxP);

    if (state[which].page === nextP) return;
    state[which].page = nextP;

    if (which === 'active') loadActive();
    if (which === 'archived') loadArchived();
    if (which === 'bin') loadBin();

    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  /* ================== ACTIVE ================== */
  async function loadActive(){
    const q = $('mpQ')?.value || '';
    const status = $('mpStatus')?.value || '';
    const dept = $('mpDept')?.value || '';

    const params = new URLSearchParams();
    if (q) params.set('q', q);
    if(status) params.set('status', status);
    if(dept) params.set('department_id', dept);
    params.set('page', String(state.active.page));

    const tbody = $('mpRowsActive');
    if (tbody) tbody.innerHTML = `<tr><td colspan="6" class="text-center mp-muted" style="padding:38px;">Loading…</td></tr>`;
    setEmpty('active', false);

    try{
      showLoading(true);
      const j = await api(`/api/pages?${params.toString()}`);

      const rows = Array.isArray(j.data) ? j.data : [];
      const pg = pickPagination(j);

      state.active.page = pg.current_page;
      state.active.lastPage = pg.last_page;
      state.active.perPage = pg.per_page || state.active.perPage;
      state.active.total = pg.total || rows.length;

      if(!rows.length){
        if (tbody) tbody.innerHTML = '';
        setEmpty('active', true);
        $('mpMetaActive').textContent = '0 page(s)';
        renderPager('active');
        return;
      }

      const html = rows.map(r=>{
        const slug = (r.slug || '');
        const viewUrl = `/page/${encodeURIComponent(slug)}`;
        const editKey = (r.uuid || r.id || '');

        return `
          <tr>
            <td class="fw-semibold">${esc(r.title || '—')}</td>
            <td><a target="_blank" href="${viewUrl}">/${esc(slug)}</a></td>
            <td><code>${esc(r.shortcode || '-')}</code></td>
            <td>${badge(r.status || '-')}</td>
            <td>${esc(r.published_at || '-')}</td>
            <td class="text-end">
              <div class="dropdown">
                <button type="button" class="btn btn-sm btn-primary mp-dd-toggle" aria-expanded="false" title="Actions">
                  <i class="fa fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" target="_blank" href="${viewUrl}">
                      <i class="fa fa-eye me-1"></i> View
                    </a>
                  </li>
                  <li>
                    <button type="button" class="dropdown-item" onclick="editPage('${esc(String(editKey))}')">
                      <i class="fa fa-edit me-1"></i> Edit
                    </button>
                  </li>
                  <li>
                    <button type="button" class="dropdown-item" onclick="archivePage('${esc(String(r.id))}')">
                      <i class="fa fa-box-archive me-1"></i> Archive
                    </button>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <button type="button" class="dropdown-item text-danger" onclick="deletePage('${esc(String(r.id))}')">
                      <i class="fa fa-trash me-1"></i> Delete
                    </button>
                  </li>
                </ul>
              </div>
            </td>
          </tr>
        `;
      }).join('');

      if (tbody) tbody.innerHTML = html;

      $('mpMetaActive').textContent = computeMetaText(pg, rows.length);
      renderPager('active');
    }catch(e){
      console.error('Active load failed', e);
      if (tbody) tbody.innerHTML = '';
      setEmpty('active', true);
      $('mpMetaActive').textContent = `Failed to load`;
      renderPager('active');
      if(e.status === 401 || e.status === 403) location.href='/';
    }finally{
      showLoading(false);
    }
  }

  /* ================== ARCHIVED ================== */
  async function loadArchived(){
    const params = new URLSearchParams({ page: String(state.archived.page) });

    const tbody = $('mpRowsArchived');
    if (tbody) tbody.innerHTML = `<tr><td colspan="4" class="text-center mp-muted" style="padding:38px;">Loading…</td></tr>`;
    setEmpty('archived', false);

    try{
      showLoading(true);
      const j = await api(`/api/pages/archived?${params.toString()}`);

      const rows = Array.isArray(j.data) ? j.data : [];
      const pg = pickPagination(j);

      state.archived.page = pg.current_page;
      state.archived.lastPage = pg.last_page;
      state.archived.perPage = pg.per_page || state.archived.perPage;
      state.archived.total = pg.total || rows.length;

      if(!rows.length){
        if (tbody) tbody.innerHTML = '';
        setEmpty('archived', true);
        $('mpMetaArchived').textContent = '0 archived page(s)';
        renderPager('archived');
        return;
      }

      const html = rows.map(r=>{
        return `
          <tr>
            <td class="fw-semibold">${esc(r.title || '—')}</td>
            <td>/${esc(r.slug || '')}</td>
            <td>${esc(r.updated_at || '-')}</td>
            <td class="text-end">
              <div class="dropdown">
                <button type="button" class="btn btn-sm btn-primary mp-dd-toggle" aria-expanded="false" title="Actions">
                  <i class="fa fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <button type="button" class="dropdown-item text-success" onclick="restorePage('${esc(String(r.id))}')">
                      <i class="fa fa-box-open me-1"></i> Unarchive
                    </button>
                  </li>
                </ul>
              </div>
            </td>
          </tr>
        `;
      }).join('');

      if (tbody) tbody.innerHTML = html;

      $('mpMetaArchived').textContent = computeMetaText(pg, rows.length);
      renderPager('archived');
    }catch(e){
      console.error('Archived load failed', e);
      if (tbody) tbody.innerHTML = '';
      setEmpty('archived', true);
      $('mpMetaArchived').textContent = `Failed to load`;
      renderPager('archived');
      if(e.status === 401 || e.status === 403) location.href='/';
    }finally{
      showLoading(false);
    }
  }

  /* ================== BIN ================== */
  async function loadBin(){
    const params = new URLSearchParams({ page: String(state.bin.page) });

    const tbody = $('mpRowsBin');
    if (tbody) tbody.innerHTML = `<tr><td colspan="3" class="text-center mp-muted" style="padding:38px;">Loading…</td></tr>`;
    setEmpty('bin', false);

    try{
      showLoading(true);
      const j = await api(`/api/pages/trash?${params.toString()}`);

      const rows = Array.isArray(j.data) ? j.data : [];
      const pg = pickPagination(j);

      state.bin.page = pg.current_page;
      state.bin.lastPage = pg.last_page;
      state.bin.perPage = pg.per_page || state.bin.perPage;
      state.bin.total = pg.total || rows.length;

      if(!rows.length){
        if (tbody) tbody.innerHTML = '';
        setEmpty('bin', true);
        $('mpMetaBin').textContent = '0 item(s)';
        renderPager('bin');
        return;
      }

      const html = rows.map(r=>{
        return `
          <tr>
            <td class="fw-semibold">${esc(r.title || '—')}</td>
            <td>${esc(r.deleted_at || '-')}</td>
            <td class="text-end">
              <div class="dropdown">
                <button type="button" class="btn btn-sm btn-primary mp-dd-toggle" aria-expanded="false" title="Actions">
                  <i class="fa fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <button type="button" class="dropdown-item" onclick="restorePage('${esc(String(r.id))}')">
                      <i class="fa fa-undo me-1"></i> Restore
                    </button>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <button type="button" class="dropdown-item text-danger" onclick="forceDeletePage('${esc(String(r.id))}')">
                      <i class="fa fa-trash me-1"></i> Delete Permanently
                    </button>
                  </li>
                </ul>
              </div>
            </td>
          </tr>
        `;
      }).join('');

      if (tbody) tbody.innerHTML = html;

      $('mpMetaBin').textContent = computeMetaText(pg, rows.length);
      renderPager('bin');
    }catch(e){
      console.error('Trash load failed', e);
      if (tbody) tbody.innerHTML = '';
      setEmpty('bin', true);
      $('mpMetaBin').textContent = `Failed to load`;
      renderPager('bin');
      if(e.status === 401 || e.status === 403) location.href='/';
    }finally{
      showLoading(false);
    }
  }

  /* ================== ACTIONS (kept same endpoints/behavior) ================== */
  window.editPage = (uuid) => { location.href = `/pages/create?uuid=${encodeURIComponent(uuid)}`; };

  window.archivePage = (id) => {
    Swal.fire({
      title:'Archive page?',
      icon:'question',
      showCancelButton:true,
      confirmButtonText:'Archive'
    }).then(r=>{
      if(r.isConfirmed){
        api(`/api/pages/${encodeURIComponent(id)}/archive`,{method:'POST'})
          .then(()=>{ loadActive(); Swal.fire('Archived','','success'); })
          .catch(e=>Swal.fire('Error', e.message || 'Failed', 'error'));
      }
    });
  };

  window.deletePage = (id) => {
    Swal.fire({
      title:'Move to bin?',
      icon:'warning',
      showCancelButton:true,
      confirmButtonText:'Delete'
    }).then(r=>{
      if(r.isConfirmed){
        api(`/api/pages/${encodeURIComponent(id)}`,{method:'DELETE'})
          .then(()=>{ loadActive(); loadArchived(); Swal.fire('Deleted','','success'); })
          .catch(e=>Swal.fire('Error', e.message || 'Failed', 'error'));
      }
    });
  };

  window.restorePage = (id) =>
    api(`/api/pages/${encodeURIComponent(id)}/restore`,{method:'POST'})
      .then(()=>{ loadArchived(); loadBin(); loadActive(); })
      .catch(e=>Swal.fire('Error', e.message || 'Failed', 'error'));

  window.forceDeletePage = (id) => {
    Swal.fire({
      title:'Delete permanently?',
      icon:'error',
      showCancelButton:true,
      confirmButtonText:'Delete Forever'
    }).then(r=>{
      if(r.isConfirmed){
        api(`/api/pages/${encodeURIComponent(id)}/force`,{method:'DELETE'})
          .then(()=>{ loadBin(); Swal.fire('Deleted','','success'); })
          .catch(e=>Swal.fire('Error', e.message || 'Failed', 'error'));
      }
    });
  };

  /* ================== EVENTS ================== */
  $('mpBtnFilter')?.addEventListener('click', () => {
    state.active.page = 1;
    loadActive();
  });

  $('mpBtnReset')?.addEventListener('click', () => {
    state.active.page = 1;
    const q = $('mpQ');
    const status = $('mpStatus');
    const dept = $('mpDept');
    if(q) q.value = '';
    if(status) status.value = '';
    if(dept) dept.value = '';
    loadActive();
  });

  $('mpQ')?.addEventListener('keydown', (e)=>{
    if(e.key === 'Enter'){
      state.active.page = 1;
      loadActive();
    }
  });

  $('mpBtnCreate')?.addEventListener('click', () => {
    location.href='/pages/create';
  });

  document.querySelector('a[href="#mpTabArchived"]')
    ?.addEventListener('shown.bs.tab', () => { state.archived.page = 1; loadArchived(); });

  document.querySelector('a[href="#mpTabBin"]')
    ?.addEventListener('shown.bs.tab', () => { state.bin.page = 1; loadBin(); });

  /* ================== INIT ================== */
  (async () => {
    await loadDepartments();
    loadActive();
  })();
})();
</script>
@endpush
