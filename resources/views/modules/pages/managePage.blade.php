{{-- resources/views/modules/managePages.blade.php --}}
@extends('pages.users.layout.structure')

@section('title','Manage Pages')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

@push('styles')
<style>
/* ===== SAME CSS AS MODULES (UNCHANGED) ===== */
.cm-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}
.mfa-toolbar .form-control,.mfa-toolbar .form-select{height:40px;border-radius:12px}
.table-wrap.card{border-radius:16px}
.table thead th{font-size:13px;color:var(--muted-color)}
.badge-success{background:#16a34a!important}
.badge-secondary{background:#64748b!important}
.empty{color:var(--muted-color)}
.icon-btn{height:34px;border-radius:10px}
.dropdown-menu{border-radius:12px;min-width:220px}
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
      <div class="panel mb-3 d-flex gap-2 align-items-center">
        <input id="q" class="form-control" placeholder="Search title / slug">
        <select id="status" class="form-select" style="width:160px">
          <option value="">All</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
        <button id="btnFilter" class="btn btn-light">Filter</button>
      </div>

      <div class="card table-wrap">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
             <th>Title</th>
<th>Slug</th>
<th>Shortcode</th> <!-- NEW -->
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

  const state = { active: 1, archived: 1, bin: 1 };

  const api = (url, opt={}) =>
    fetch(url,{
      ...opt,
      headers:{
        'Authorization':'Bearer '+TOKEN,
        'Accept':'application/json',
        ...(opt.headers||{})
      }
    }).then(r=>r.json());

  const esc = s => (s||'').replace(/[&<>"']/g,m=>({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
  }[m]));

  const badge = s =>
    `<span class="badge ${s==='Active'?'badge-success':'badge-secondary'}">${s}</span>`;

  /* ================= ACTIVE ================= */

  function loadActive(){
    const q = document.getElementById('q').value;
    const status = document.getElementById('status').value;

    api(`/api/pages?q=${encodeURIComponent(q)}&status=${status}&page=${state.active}`)
    .then(j=>{
      const rows = document.getElementById('rows-active');
      rows.innerHTML = '';

      if(!j.data.length){
        rows.innerHTML =
          '<tr><td colspan="5" class="text-center empty">No pages found</td></tr>';
        return;
      }

      j.data.forEach(r=>{
        rows.innerHTML += `
        <tr>
          <td>${esc(r.title)}</td>
<td><a target="_blank" href="/page/${esc(r.slug)}">/${esc(r.slug)}</a></td>
<td><code>${esc(r.shortcode || '-')}</code></td> <!-- NEW -->
<td>${badge(r.status)}</td>
<td>${r.published_at || '-'}</td>

          <td class="text-end">
            <div class="dropdown">
              <button class="btn btn-sm btn-primary" data-bs-toggle="dropdown">
                <i class="fa fa-ellipsis-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item" target="_blank" href="/page/${esc(r.slug)}">
                    <i class="fa fa-eye me-1"></i> View
                  </a>
                </li>
                <li>
                  <button class="dropdown-item" onclick="editPage('${r.id}')">
                    <i class="fa fa-edit me-1"></i> Edit
                  </button>
                </li>
                <li>
                  <button class="dropdown-item" onclick="archivePage('${r.id}')">
                    <i class="fa fa-box-archive me-1"></i> Archive
                  </button>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <button class="dropdown-item text-danger" onclick="deletePage('${r.id}')">
                    <i class="fa fa-trash me-1"></i> Delete
                  </button>
                </li>
              </ul>
            </div>
          </td>
        </tr>`;
      });

      document.getElementById('meta-active').textContent =
        `${j.pagination.total} page(s)`;
    });
  }

  /* ================= ARCHIVED ================= */

  function loadArchived(){
  const params = new URLSearchParams({
    page: state.archived
  });

  api(`/api/pages/archived?${params.toString()}`)
    .then(j=>{
      const rows = document.getElementById('rows-archived');
      rows.innerHTML = '';

      if(!j || !Array.isArray(j.data) || !j.data.length){
        rows.innerHTML =
          '<tr><td colspan="4" class="text-center empty">No archived pages</td></tr>';
        return;
      }

      j.data.forEach(r=>{
        rows.innerHTML += `
        <tr>
          <td>${esc(r.title)}</td>
          <td>/${esc(r.slug)}</td>
          <td>${r.updated_at || '-'}</td>
          <td class="text-end">
  <div class="dropdown">
    <button class="btn btn-sm btn-primary" data-bs-toggle="dropdown">
      <i class="fa fa-ellipsis-vertical"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li>
        <button class="dropdown-item text-success"
          onclick="restorePage('${r.id}')">
          <i class="fa fa-box-open me-1"></i> Unarchive
        </button>
      </li>
    </ul>
  </div>
</td>

        </tr>`;
      });

      document.getElementById('meta-archived').textContent =
        `${j.pagination.total} archived page(s)`;
    });
}

  /* ================= BIN ================= */

  function loadBin(){
  api(`/api/pages/trash?page=${state.bin}`)
  .then(j=>{
    const rows = document.getElementById('rows-bin');
    rows.innerHTML = '';

    if(!j || !Array.isArray(j.data) || !j.data.length){
      rows.innerHTML =
        '<tr><td colspan="3" class="text-center empty">Bin is empty</td></tr>';
      return;
    }

    j.data.forEach(r=>{
      rows.innerHTML += `
      <tr>
        <td>${esc(r.title)}</td>
        <td>${r.deleted_at}</td>
        <td class="text-end">
  <div class="dropdown">
    <button class="btn btn-sm btn-primary" data-bs-toggle="dropdown">
      <i class="fa fa-ellipsis-vertical"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li>
        <button class="dropdown-item"
          onclick="restorePage('${r.id}')">
          <i class="fa fa-undo me-1"></i> Restore
        </button>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li>
        <button class="dropdown-item text-danger"
          onclick="forceDeletePage('${r.id}')">
          <i class="fa fa-trash me-1"></i> Delete Permanently
        </button>
      </li>
    </ul>
  </div>
</td>

      </tr>`;
    });

    document.getElementById('meta-bin').textContent =
      `${j.pagination.total} item(s)`;
  })
  .catch(err=>{
    console.error('Trash load failed', err);
  });
}

  /* ================= ACTIONS ================= */

window.editPage = id =>
  location.href = `/pages/create?id=${id}`;

  window.archivePage = id => {
    Swal.fire({
      title:'Archive page?',
      icon:'question',
      showCancelButton:true,
      confirmButtonText:'Archive'
    }).then(r=>{
      if(r.isConfirmed){
        api(`/api/pages/${id}/archive`,{method:'POST'})
        .then(()=>{ loadActive(); Swal.fire('Archived','','success'); });
      }
    });
  };

  window.deletePage = id => {
    Swal.fire({
      title:'Move to bin?',
      icon:'warning',
      showCancelButton:true,
      confirmButtonText:'Delete'
    }).then(r=>{
      if(r.isConfirmed){
        api(`/api/pages/${id}`,{method:'DELETE'})
        .then(()=>{ loadActive(); loadArchived(); Swal.fire('Deleted','','success'); });
      }
    });
  };

  window.restorePage = id =>
    api(`/api/pages/${id}/restore`,{method:'POST'})
    .then(()=>{ loadArchived(); loadBin(); loadActive(); });

  window.forceDeletePage = id => {
    Swal.fire({
      title:'Delete permanently?',
      icon:'error',
      showCancelButton:true,
      confirmButtonText:'Delete Forever'
    }).then(r=>{
      if(r.isConfirmed){
        api(`/api/pages/${id}/force`,{method:'DELETE'})
        .then(()=>{ loadBin(); Swal.fire('Deleted','','success'); });
      }
    });
  };

  /* ================= EVENTS ================= */

  document.getElementById('btnFilter').onclick = () => {
    state.active = 1;
    loadActive();
  };

  document.getElementById('btnCreate').onclick =
    () => location.href='/pages/create';

  document.querySelector('a[href="#tab-archived"]')
    .addEventListener('shown.bs.tab', loadArchived);

  document.querySelector('a[href="#tab-bin"]')
    .addEventListener('shown.bs.tab', loadBin);

  loadActive();
})();
</script>

@endpush