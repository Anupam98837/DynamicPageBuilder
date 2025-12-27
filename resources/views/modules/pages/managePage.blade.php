{{-- resources/views/modules/managePages.blade.php --}}
@extends('pages.users.layout.structure')

@section('title','Manage Pages')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

@push('styles')
<style>
/* ===== SAME CSS AS MODULES (UNCHANGED-ish) ===== */
.cm-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}
.mfa-toolbar .form-control,.mfa-toolbar .form-select{height:40px;border-radius:12px}
.table-wrap.card{border-radius:16px}
.table thead th{font-size:13px;color:var(--muted-color)}
.badge-success{background:#16a34a!important}
.badge-secondary{background:#64748b!important}
.empty{color:var(--muted-color)}
.icon-btn{height:34px;border-radius:10px}
.dropdown-menu{border-radius:12px;min-width:220px}

/* tiny helpers */
#dept{min-width:220px}
@media (max-width:768px){
  #dept{min-width:160px}
}
</style>
@endpush

@section('content')
<div class="cm-wrap">

  {{-- Toolbar --}}
  <div class="panel mb-3 d-flex align-items-center justify-content-between">
    <div class="fw-semibold">My Pages</div>
    <button id="btnCreate" class="btn btn-primary">
      <i class="fa fa-plus me-1"></i> New Page
    </button>
  </div>

  {{-- Tabs --}}
  <ul class="nav nav-tabs mb-3">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#tab-active">Pages</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-archived">Archived</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#tab-bin">Bin</a>
    </li>
  </ul>

  <div class="tab-content">

    {{-- ACTIVE PAGES TAB --}}
    <div class="tab-pane fade show active" id="tab-active">
      <div class="panel mb-3 d-flex gap-2 align-items-center flex-wrap">
        <input id="q" class="form-control" placeholder="Search title / slug" style="min-width:220px">
        <select id="status" class="form-select" style="width:160px">
          <option value="">All Status</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>

        {{-- ✅ NEW: Department filter --}}
        <select id="dept" class="form-select">
          <option value="">All Departments</option>
        </select>

        <button id="btnFilter" class="btn btn-light">Filter</button>
        <button id="btnReset" class="btn btn-outline-secondary">Reset</button>
      </div>

      <div class="card table-wrap">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Title</th>
              <th>Slug</th>
              <th>Shortcode</th>
              <th>Status</th>
              <th>Published</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody id="rows-active"></tbody>
        </table>
      </div>

      <div id="meta-active" class="small text-muted mt-2"></div>
      <ul id="pager-active" class="pagination mt-2"></ul>
    </div>

    {{-- ARCHIVED PAGES TAB --}}
    <div class="tab-pane fade" id="tab-archived">
      <div class="card table-wrap">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Title</th>
              <th>Slug</th>
              <th>Archived At</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody id="rows-archived"></tbody>
        </table>
      </div>

      <div id="meta-archived" class="small text-muted mt-2"></div>
      <ul id="pager-archived" class="pagination mt-2"></ul>
    </div>

    {{-- BIN TAB --}}
    <div class="tab-pane fade" id="tab-bin">
      <div class="card table-wrap">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Title</th>
              <th>Deleted At</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody id="rows-bin"></tbody>
        </table>
      </div>

      <div id="meta-bin" class="small text-muted mt-2"></div>
      <ul id="pager-bin" class="pagination mt-2"></ul>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token');
  if(!TOKEN) location.href='/';

  const state = {
    active: 1, archived: 1, bin: 1,
    departments: [] // ✅ NEW
  };

  const api = (url, opt={}) =>
    fetch(url,{
      ...opt,
      headers:{
        'Authorization':'Bearer '+TOKEN,
        'Accept':'application/json',
        ...(opt.headers||{})
      }
    }).then(async r=>{
      // try to parse JSON safely
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
    });

  const esc = s => (s||'').toString().replace(/[&<>"']/g,m=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
  }[m]));

  const badge = s =>
    `<span class="badge ${s==='Active'?'badge-success':'badge-secondary'}">${esc(s)}</span>`;

  /* ================== ✅ DEPARTMENTS (NEW) ================== */
  function deptLabel(d){
    return d?.title || d?.name || d?.slug || (d?.id ? `Department #${d.id}` : 'Department');
  }

  async function loadDepartments(){
    const deptSel = document.getElementById('dept');
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
      // keep only default
      deptSel.innerHTML = `<option value="">All Departments</option>`;
    }
  }

  /* ================= ACTIVE ================= */

  async function loadActive(){
    const q = document.getElementById('q')?.value || '';
    const status = document.getElementById('status')?.value || '';
    const dept = document.getElementById('dept')?.value || '';

    const params = new URLSearchParams();
    params.set('q', q);
    if(status) params.set('status', status);
    params.set('page', String(state.active));

    // ✅ NEW: department filter param (common patterns)
    // Prefer "department_id", fallback can be "department"
    if(dept) params.set('department_id', dept);

    try{
      const j = await api(`/api/pages?${params.toString()}`);

      const rows = document.getElementById('rows-active');
      rows.innerHTML = '';

      if(!j.data || !j.data.length){
        rows.innerHTML = '<tr><td colspan="6" class="text-center empty">No pages found</td></tr>';
        document.getElementById('meta-active').textContent = `0 page(s)`;
        return;
      }

      j.data.forEach(r=>{
        const slug = (r.slug || '');
        const viewUrl = `/page/${encodeURIComponent(slug)}`;

        // ✅ IMPORTANT: edit by uuid (fallback to id)
        const editKey = (r.uuid || r.id || '');

        rows.innerHTML += `
        <tr>
          <td>${esc(r.title)}</td>
          <td><a target="_blank" href="${viewUrl}">/${esc(slug)}</a></td>
          <td><code>${esc(r.shortcode || '-')}</code></td>
          <td>${badge(r.status || '-')}</td>
          <td>${esc(r.published_at || '-')}</td>

          <td class="text-end">
            <div class="dropdown">
              <button class="btn btn-sm btn-primary" data-bs-toggle="dropdown">
                <i class="fa fa-ellipsis-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item" target="_blank" href="${viewUrl}">
                    <i class="fa fa-eye me-1"></i> View
                  </a>
                </li>
                <li>
                  <button class="dropdown-item" onclick="editPage('${esc(String(editKey))}')">
                    <i class="fa fa-edit me-1"></i> Edit
                  </button>
                </li>
                <li>
                  <button class="dropdown-item" onclick="archivePage('${esc(String(r.id))}')">
                    <i class="fa fa-box-archive me-1"></i> Archive
                  </button>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <button class="dropdown-item text-danger" onclick="deletePage('${esc(String(r.id))}')">
                    <i class="fa fa-trash me-1"></i> Delete
                  </button>
                </li>
              </ul>
            </div>
          </td>
        </tr>`;
      });

      document.getElementById('meta-active').textContent =
        `${j.pagination?.total ?? j.data.length} page(s)`;
    }catch(e){
      console.error('Active load failed', e);
      const rows = document.getElementById('rows-active');
      rows.innerHTML = '<tr><td colspan="6" class="text-center empty">Failed to load pages</td></tr>';
      document.getElementById('meta-active').textContent = `—`;
      if(e.status === 401 || e.status === 403) location.href='/';
    }
  }

  /* ================= ARCHIVED ================= */

  async function loadArchived(){
    const params = new URLSearchParams({ page: state.archived });

    try{
      const j = await api(`/api/pages/archived?${params.toString()}`);
      const rows = document.getElementById('rows-archived');
      rows.innerHTML = '';

      if(!Array.isArray(j.data) || !j.data.length){
        rows.innerHTML = '<tr><td colspan="4" class="text-center empty">No archived pages</td></tr>';
        document.getElementById('meta-archived').textContent = `0 archived page(s)`;
        return;
      }

      j.data.forEach(r=>{
        rows.innerHTML += `
        <tr>
          <td>${esc(r.title)}</td>
          <td>/${esc(r.slug)}</td>
          <td>${esc(r.updated_at || '-')}</td>
          <td class="text-end">
            <div class="dropdown">
              <button class="btn btn-sm btn-primary" data-bs-toggle="dropdown">
                <i class="fa fa-ellipsis-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <button class="dropdown-item text-success" onclick="restorePage('${esc(String(r.id))}')">
                    <i class="fa fa-box-open me-1"></i> Unarchive
                  </button>
                </li>
              </ul>
            </div>
          </td>
        </tr>`;
      });

      document.getElementById('meta-archived').textContent =
        `${j.pagination?.total ?? j.data.length} archived page(s)`;
    }catch(e){
      console.error('Archived load failed', e);
      if(e.status === 401 || e.status === 403) location.href='/';
    }
  }

  /* ================= BIN ================= */

  async function loadBin(){
    try{
      const j = await api(`/api/pages/trash?page=${state.bin}`);
      const rows = document.getElementById('rows-bin');
      rows.innerHTML = '';

      if(!Array.isArray(j.data) || !j.data.length){
        rows.innerHTML = '<tr><td colspan="3" class="text-center empty">Bin is empty</td></tr>';
        document.getElementById('meta-bin').textContent = `0 item(s)`;
        return;
      }

      j.data.forEach(r=>{
        rows.innerHTML += `
        <tr>
          <td>${esc(r.title)}</td>
          <td>${esc(r.deleted_at || '-')}</td>
          <td class="text-end">
            <div class="dropdown">
              <button class="btn btn-sm btn-primary" data-bs-toggle="dropdown">
                <i class="fa fa-ellipsis-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <button class="dropdown-item" onclick="restorePage('${esc(String(r.id))}')">
                    <i class="fa fa-undo me-1"></i> Restore
                  </button>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <button class="dropdown-item text-danger" onclick="forceDeletePage('${esc(String(r.id))}')">
                    <i class="fa fa-trash me-1"></i> Delete Permanently
                  </button>
                </li>
              </ul>
            </div>
          </td>
        </tr>`;
      });

      document.getElementById('meta-bin').textContent =
        `${j.pagination?.total ?? j.data.length} item(s)`;
    }catch(e){
      console.error('Trash load failed', e);
      if(e.status === 401 || e.status === 403) location.href='/';
    }
  }

  /* ================= ACTIONS ================= */

  // ✅ FIXED: redirect by uuid (the create/edit page reads ?uuid=...)
  window.editPage = (uuid) => {
    location.href = `/pages/create?uuid=${encodeURIComponent(uuid)}`;
  };

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

  /* ================= EVENTS ================= */

  document.getElementById('btnFilter').onclick = () => {
    state.active = 1;
    loadActive();
  };

  document.getElementById('btnReset').onclick = () => {
    state.active = 1;
    const q = document.getElementById('q');
    const status = document.getElementById('status');
    const dept = document.getElementById('dept');
    if(q) q.value = '';
    if(status) status.value = '';
    if(dept) dept.value = '';
    loadActive();
  };

  // quick reload on enter
  document.getElementById('q')?.addEventListener('keydown', (e)=>{
    if(e.key === 'Enter'){
      state.active = 1;
      loadActive();
    }
  });

  document.getElementById('btnCreate').onclick = () => {
    location.href='/pages/create';
  };

  document.querySelector('a[href="#tab-archived"]')
    .addEventListener('shown.bs.tab', loadArchived);

  document.querySelector('a[href="#tab-bin"]')
    .addEventListener('shown.bs.tab', loadBin);

  // init
  (async () => {
    await loadDepartments(); // ✅ NEW
    loadActive();
  })();
})();
</script>
@endpush
