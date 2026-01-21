{{-- resources/views/modules/feedbackPosts/manageFeedbackPostsIndex.blade.php --}}

@php
  $fpUid = 'fp_' . \Illuminate\Support\Str::random(8);

  /**
   * ✅ UPDATE THIS if your form URL is different
   * This is your existing create/edit page:
   * resources/views/modules/feedbackPosts/manageFeedbackPosts.blade.php
   */
  $fpCreateUrl   = url('/feedback/post/manage'); // 👈 change if needed
  $fpEditPattern = $fpCreateUrl . '?uuid={uuid}';

  // API base
  $apiBase   = url('/api/feedback-posts');
  $apiMe     = url('/api/users/me');
@endphp

@section('title','Manage Feedback Posts')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
  /* ===== Shell ===== */
  .fp-wrap{max-width:1200px;margin:16px auto 44px;overflow:visible}
  .panel{
    background:var(--surface);
    border:1px solid var(--line-strong);
    border-radius:16px;
    box-shadow:var(--shadow-2);
    padding:14px;
  }

  /* Toolbar */
  .fp-toolbar .form-control{height:40px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)}
  .fp-toolbar .form-select{height:40px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)}
  .fp-toolbar .btn-light{background:var(--surface);border:1px solid var(--line-strong)}
  .fp-toolbar .btn-primary{background:var(--primary-color);border:none}
  .fp-toolbar .position-relative{min-width:min(320px,100%);flex:1 1 320px}

  /* Card + table */
  .table-wrap.card{
    position:relative;
    border:1px solid var(--line-strong);
    border-radius:16px;
    background:var(--surface);
    box-shadow:var(--shadow-2);
    overflow:visible;
  }
  .table-wrap .card-body{overflow:visible}
  .table-responsive{overflow:visible !important}
  .table{--bs-table-bg:transparent}
  .table thead th{
    font-weight:700;
    color:var(--muted-color);
    font-size:13px;
    border-bottom:1px solid var(--line-strong);
    background:var(--surface);
  }
  .table thead.sticky-top{z-index:3}
  .table tbody tr{border-top:1px solid var(--line-soft)}
  .table tbody tr:hover{background:var(--page-hover)}
  .small{font-size:12.5px}

  /* Badges */
  .badge-soft{
    background:color-mix(in oklab, var(--muted-color) 12%, transparent);
    color:var(--ink);
    border:1px solid color-mix(in oklab, var(--line-soft) 70%, transparent);
    font-weight:700;
  }
  .badge-good{
    background:color-mix(in oklab, var(--success-color) 16%, transparent);
    border:1px solid color-mix(in oklab, var(--success-color) 30%, transparent);
    color:var(--success-color);
    font-weight:800;
  }
  .badge-warn{
    background:color-mix(in oklab, var(--warning-color) 18%, transparent);
    border:1px solid color-mix(in oklab, var(--warning-color) 30%, transparent);
    color:var(--warning-color);
    font-weight:800;
  }

  /* Count chip */
  .count-chip{
    display:inline-flex;align-items:center;gap:6px;
    padding:6px 12px;border-radius:999px;
    background:color-mix(in oklab, var(--primary-color) 12%, transparent);
    color:var(--primary-color);
    font-weight:900;font-size:12px;
    white-space:nowrap;
  }

  /* Loader / empty */
  .empty{color:var(--muted-color)}
  .placeholder{background:linear-gradient(90deg,#00000010,#00000005,#00000010);border-radius:8px}

  /* Actions */
  .act-btn{height:34px;border-radius:10px}

  /* Dark tweaks */
  html.theme-dark .panel,
  html.theme-dark .table-wrap.card{background:#0f172a;border-color:var(--line-strong)}
  html.theme-dark .table thead th{background:#0f172a;border-color:var(--line-strong);color:#94a3b8}
</style>
@endpush

@section('content')
<div id="{{ $fpUid }}"
     class="fp-wrap"
     data-create-url="{{ $fpCreateUrl }}"
     data-edit-pattern="{{ $fpEditPattern }}"
     data-api-base="{{ $apiBase }}"
     data-api-me="{{ $apiMe }}">

  {{-- ===== Global toolbar ===== --}}
  <div class="row align-items-center g-2 mb-3 fp-toolbar panel">
    <div class="col-12 col-xl d-flex align-items-center flex-wrap gap-2">

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0 fw-semibold">
          <i class="fa-solid fa-clipboard-list me-2"></i>Manage Feedback Posts
        </label>
        <span class="count-chip js-total-chip">—</span>
      </div>

      <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Per Page</label>
        <select class="form-select js-per-page" style="width:110px;">
          <option>10</option><option selected>20</option><option>30</option><option>50</option><option>100</option>
        </select>
      </div>

      <div class="position-relative">
        <input type="text" class="form-control ps-5 js-q" placeholder="Search title / uuid…">
        <i class="fa fa-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
      </div>

    </div>

    <div class="col-12 col-xxl-auto ms-xxl-auto d-flex justify-content-xxl-end gap-2">
      <button class="btn btn-light js-refresh"><i class="fa fa-rotate me-1"></i>Refresh</button>
      <a href="{{ $fpCreateUrl }}" class="btn btn-primary js-new">
        <i class="fa fa-plus me-1"></i>New Post
      </a>
    </div>
  </div>

  {{-- ===== Tabs ===== --}}
  @php
    $tabActive   = $fpUid.'_tab_active';
    $tabArchived = $fpUid.'_tab_archived';
    $tabBin      = $fpUid.'_tab_bin';
  @endphp

  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" href="#{{ $tabActive }}" role="tab" aria-selected="true">
        <i class="fa-solid fa-circle-check me-2"></i>Active
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#{{ $tabArchived }}" role="tab" aria-selected="false">
        <i class="fa-solid fa-box-archive me-2"></i>Archived
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#{{ $tabBin }}" role="tab" aria-selected="false">
        <i class="fa-solid fa-trash-can me-2"></i>Bin
      </a>
    </li>
  </ul>

  <div class="tab-content mb-3">

    {{-- ========== ACTIVE ========== --}}
    <div class="tab-pane fade show active" id="{{ $tabActive }}" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>POST</th>
                  <th style="width:20%;">SCOPE</th>
                  <th style="width:180px;">ASSIGNMENTS</th>
                  <th style="width:170px;">PUBLISH / EXPIRE</th>
                  <th style="width:150px;">UPDATED</th>
                  <th class="text-end" style="width:240px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody class="js-rows-active">
                <tr class="js-loader-active" style="display:none;">
                  <td colspan="6" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="js-empty-active empty p-4 text-center" style="display:none;">
            <i class="fa fa-clipboard-list mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No active feedback posts found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small js-meta-active">—</div>
            <nav style="position:relative; z-index:1;"><ul class="pagination mb-0 js-pager-active"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ========== ARCHIVED ========== --}}
    <div class="tab-pane fade" id="{{ $tabArchived }}" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>POST</th>
                  <th style="width:20%;">SCOPE</th>
                  <th style="width:180px;">ASSIGNMENTS</th>
                  <th style="width:170px;">PUBLISH / EXPIRE</th>
                  <th style="width:150px;">UPDATED</th>
                  <th class="text-end" style="width:240px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody class="js-rows-archived">
                <tr class="js-loader-archived" style="display:none;">
                  <td colspan="6" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="js-empty-archived empty p-4 text-center" style="display:none;">
            <i class="fa fa-box-archive mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No archived feedback posts found.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small js-meta-archived">—</div>
            <nav style="position:relative; z-index:1;"><ul class="pagination mb-0 js-pager-archived"></ul></nav>
          </div>
        </div>
      </div>
    </div>

    {{-- ========== BIN ========== --}}
    <div class="tab-pane fade" id="{{ $tabBin }}" role="tabpanel">
      <div class="card table-wrap">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
              <thead class="sticky-top">
                <tr>
                  <th>POST</th>
                  <th style="width:20%;">SCOPE</th>
                  <th style="width:180px;">ASSIGNMENTS</th>
                  <th style="width:170px;">DELETED AT</th>
                  <th style="width:150px;">UPDATED</th>
                  <th class="text-end" style="width:240px;">ACTIONS</th>
                </tr>
              </thead>
              <tbody class="js-rows-bin">
                <tr class="js-loader-bin" style="display:none;">
                  <td colspan="6" class="p-0">
                    <div class="p-4">
                      <div class="placeholder-wave">
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                        <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="js-empty-bin empty p-4 text-center" style="display:none;">
            <i class="fa fa-trash mb-2" style="font-size:32px; opacity:.6;"></i>
            <div>No items in Bin.</div>
          </div>

          <div class="d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
            <div class="text-muted small js-meta-bin">—</div>
            <nav style="position:relative; z-index:1;"><ul class="pagination mb-0 js-pager-bin"></ul></nav>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2100">
  <div class="toast js-ok-toast text-bg-success border-0">
    <div class="d-flex">
      <div class="toast-body js-ok-msg">Done</div>
      <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div class="toast js-err-toast text-bg-danger border-0 mt-2">
    <div class="d-flex">
      <div class="toast-body js-err-msg">Something went wrong</div>
      <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
(function(){
  const ROOT = document.getElementById(@json($fpUid));
  if(!ROOT) return;

  if(ROOT.dataset.fpInit === '1') return;
  ROOT.dataset.fpInit = '1';

  const TOKEN =
    localStorage.getItem('token') ||
    sessionStorage.getItem('token') ||
    '';

  if(!TOKEN){
    Swal.fire('Login needed','Your session expired. Please login again.','warning')
      .then(()=> location.href='/');
    return;
  }

  const API_BASE   = ROOT.dataset.apiBase;
  const API_ME     = ROOT.dataset.apiMe;
  const EDIT_PATTERN = ROOT.dataset.editPattern;

  const qs  = (sel) => ROOT.querySelector(sel);
  const esc = (s) => {
    const m = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'};
    return (s==null?'':String(s)).replace(/[&<>\"'`]/g, ch => m[ch]);
  };

  const fmtDate = (iso) => {
    if(!iso) return '-';
    const d = new Date(String(iso).replace(' ', 'T'));
    if(isNaN(d)) return esc(iso);
    return d.toLocaleString(undefined, {year:'numeric',month:'short',day:'2-digit',hour:'2-digit',minute:'2-digit'});
  };

  const editUrl = (row) => {
    const uuid = row?.uuid || row?.id;
    return EDIT_PATTERN.replace('{uuid}', encodeURIComponent(uuid));
  };

  const okToastEl  = document.querySelector('.js-ok-toast');
  const errToastEl = document.querySelector('.js-err-toast');
  const okMsgEl    = document.querySelector('.js-ok-msg');
  const errMsgEl   = document.querySelector('.js-err-msg');

  const okToast  = okToastEl  ? new bootstrap.Toast(okToastEl)  : null;
  const errToast = errToastEl ? new bootstrap.Toast(errToastEl) : null;

  const ok = (m) => {
    if(okMsgEl) okMsgEl.textContent = m || 'Done';
    okToast ? okToast.show() : console.log('[OK]', m);
  };
  const err = (m) => {
    if(errMsgEl) errMsgEl.textContent = m || 'Something went wrong';
    errToast ? errToast.show() : console.error('[ERR]', m);
  };

  async function fetchJSON(url, opts = {}){
    const res = await fetch(url, {
      cache:'no-store',
      ...opts,
      headers:{
        'Authorization': 'Bearer ' + TOKEN,
        'Accept':'application/json',
        'Cache-Control':'no-cache',
        'Pragma':'no-cache',
        ...(opts.headers || {})
      }
    });
    const j = await res.json().catch(()=> ({}));
    if(!res.ok) throw new Error(j?.error || j?.message || 'Request failed');
    return j;
  }

  function pickArray(v){
    if(Array.isArray(v)) return v;
    if(v==null) return [];
    if(typeof v === 'string'){
      try{ const d = JSON.parse(v); return Array.isArray(d) ? d : []; }catch(_){ return []; }
    }
    return [];
  }

  // ===== Permissions (only staff can toggle/delete/restore)
  const state = {
    role: '',
    canWrite: false,
    totalChip: qs('.js-total-chip'),

    q: '',
    per: 20,

    active:   { page: 1, loaded:false, dirty:true },
    archived: { page: 1, loaded:false, dirty:true },
    bin:      { page: 1, loaded:false, dirty:true },
  };

  function computePermissions(){
    const r = (state.role || '').toLowerCase();
    state.canWrite = ['admin','director','principal','hod','faculty','technical_assistant','it_person'].includes(r);
  }

  async function loadMe(){
    try{
      const j = await fetchJSON(API_ME + '?_ts=' + Date.now());
      state.role = String(j?.data?.role || j?.role || '').toLowerCase();
    }catch(_){}
    if(!state.role){
      state.role = String(localStorage.getItem('role') || sessionStorage.getItem('role') || '').toLowerCase();
    }
    computePermissions();
  }

  // ===== UI refs
  const perSel = qs('.js-per-page');
  const qInput = qs('.js-q');
  const btnRefresh = qs('.js-refresh');

  // active
  const rowsActive = qs('.js-rows-active');
  const loaderActive = qs('.js-loader-active');
  const emptyActive = qs('.js-empty-active');
  const metaActive = qs('.js-meta-active');
  const pagerActive = qs('.js-pager-active');

  // archived
  const rowsArchived = qs('.js-rows-archived');
  const loaderArchived = qs('.js-loader-archived');
  const emptyArchived = qs('.js-empty-archived');
  const metaArchived = qs('.js-meta-archived');
  const pagerArchived = qs('.js-pager-archived');

  // bin
  const rowsBin = qs('.js-rows-bin');
  const loaderBin = qs('.js-loader-bin');
  const emptyBin = qs('.js-empty-bin');
  const metaBin = qs('.js-meta-bin');
  const pagerBin = qs('.js-pager-bin');

  function clearRows(tbody, keepClass){
    Array.from(tbody.querySelectorAll('tr')).forEach(tr=>{
      if(keepClass && tr.classList.contains(keepClass)) return;
      tr.remove();
    });
  }

  function scopeText(r){
    const parts = [];
    if(r.course_id) parts.push('Course: #' + r.course_id);
    if(r.semester_id) parts.push('Sem: #' + r.semester_id);
    if(r.subject_id) parts.push('Sub: #' + r.subject_id);
    if(r.section_id) parts.push('Sec: #' + r.section_id);
    return parts.length ? parts.join(' • ') : '—';
  }

  function countsText(r){
    const q = pickArray(r.question_ids).length;
    const f = pickArray(r.faculty_ids).length;
    const s = pickArray(r.student_ids).length;
    return `${q}Q • ${f}F • ${s}S`;
  }

  function publishText(r){
    const p = r.publish_at ? fmtDate(r.publish_at) : '—';
    const e = r.expire_at ? fmtDate(r.expire_at) : '—';
    return `${p}<div class="small text-muted mt-1">→ ${e}</div>`;
  }

  function actionButtons(r, mode){
    // mode: active|archived|bin
    const can = state.canWrite;

    if(mode === 'bin'){
      return `
        <button class="btn btn-light btn-sm act-btn" data-act="restore" data-id="${esc(r.uuid || r.id)}" data-title="${esc(r.title||'')}">
          <i class="fa fa-rotate-left me-1"></i>Restore
        </button>
        ${can ? `
          <button class="btn btn-light btn-sm act-btn text-danger" data-act="force" data-id="${esc(r.uuid || r.id)}" data-title="${esc(r.title||'')}">
            <i class="fa fa-skull-crossbones me-1"></i>Delete
          </button>
        ` : ''}
      `;
    }

    const statusBtn = can ? `
      <button class="btn btn-light btn-sm act-btn" data-act="toggle" data-id="${esc(r.uuid || r.id)}" data-title="${esc(r.title||'')}">
        <i class="fa ${String(r.status||'active')==='active'?'fa-toggle-on text-success':'fa-toggle-off'}"></i>
      </button>
    ` : '';

    const deleteBtn = can ? `
      <button class="btn btn-light btn-sm act-btn text-danger" data-act="delete" data-id="${esc(r.uuid || r.id)}" data-title="${esc(r.title||'')}">
        <i class="fa fa-trash"></i>
      </button>
    ` : '';

    return `
      <a class="btn btn-light btn-sm act-btn" href="${editUrl(r)}" title="Edit">
        <i class="fa fa-pen"></i>
      </a>
      ${statusBtn}
      ${deleteBtn}
    `;
  }

  function makeRow(r, mode){
    const tr = document.createElement('tr');

    const statusBadge = String(r.status||'active') === 'active'
      ? `<span class="badge badge-good">Active</span>`
      : `<span class="badge badge-warn">Inactive</span>`;

    const subLine = r.uuid ? `<div class="small text-muted mt-1"><i class="fa fa-fingerprint me-1 opacity-75"></i>${esc(r.uuid)}</div>` : '';

    tr.innerHTML = `
      <td>
        <div class="fw-semibold">${esc(r.title || '-')} ${statusBadge}</div>
        ${r.short_title ? `<div class="small text-muted mt-1">${esc(r.short_title)}</div>` : ''}
        ${subLine}
      </td>

      <td class="small text-muted">
        ${esc(scopeText(r))}
        ${r.department_id ? `<div class="mt-1"><span class="badge badge-soft">Dept: #${esc(r.department_id)}</span></div>` : ''}
      </td>

      <td>
        <span class="badge badge-soft">${esc(countsText(r))}</span>
      </td>

      <td class="small">
        ${mode === 'bin' ? '-' : publishText(r)}
      </td>

      <td class="small">${fmtDate(r.updated_at || r.created_at)}</td>

      <td class="text-end">
        ${actionButtons(r, mode)}
      </td>
    `;
    return tr;
  }

  function buildPager(pagerEl, cur, pages, onPage){
    const li = (dis, act, label, t) =>
      `<li class="page-item ${dis?'disabled':''} ${act?'active':''}">
        <a class="page-link" href="javascript:void(0)" data-page="${t||''}">${label}</a>
      </li>`;

    let html = '';
    html += li(cur<=1,false,'Previous',cur-1);

    const w = 3;
    const s = Math.max(1, cur-w);
    const e = Math.min(pages, cur+w);

    if(s>1){
      html += li(false,false,1,1);
      if(s>2) html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
    }
    for(let i=s;i<=e;i++) html += li(false, i===cur, i, i);
    if(e<pages){
      if(e<pages-1) html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
      html += li(false,false,pages,pages);
    }
    html += li(cur>=pages,false,'Next',cur+1);

    pagerEl.innerHTML = html;
    pagerEl.querySelectorAll('a.page-link[data-page]').forEach(a=>{
      a.addEventListener('click', ()=>{
        const t = Number(a.dataset.page);
        if(!t || t===cur) return;
        onPage(Math.max(1,t));
        window.scrollTo({top:0, behavior:'smooth'});
      });
    });
  }

  async function loadList(mode){
    const per = state.per;
    const q = state.q;

    let url = '';
    let page = 1;

    // refs
    let tbody, loader, empty, meta, pager;

    if(mode === 'active'){
      page = state.active.page;
      tbody = rowsActive; loader = loaderActive; empty = emptyActive; meta = metaActive; pager = pagerActive;

      const usp = new URLSearchParams();
      usp.set('per_page', per);
      usp.set('page', page);
      usp.set('active', '1');
      if(q) usp.set('q', q);
      usp.set('sort', 'updated_at');
      usp.set('direction', 'desc');
      url = API_BASE + '?' + usp.toString();
    }

    if(mode === 'archived'){
      page = state.archived.page;
      tbody = rowsArchived; loader = loaderArchived; empty = emptyArchived; meta = metaArchived; pager = pagerArchived;

      const usp = new URLSearchParams();
      usp.set('per_page', per);
      usp.set('page', page);
      usp.set('active', '0');
      if(q) usp.set('q', q);
      usp.set('sort', 'updated_at');
      usp.set('direction', 'desc');
      url = API_BASE + '?' + usp.toString();
    }

    if(mode === 'bin'){
      page = state.bin.page;
      tbody = rowsBin; loader = loaderBin; empty = emptyBin; meta = metaBin; pager = pagerBin;

      const usp = new URLSearchParams();
      usp.set('per_page', per);
      usp.set('page', page);
      if(q) usp.set('q', q);
      url = API_BASE + '/trash?' + usp.toString();
    }

    loader.style.display = '';
    empty.style.display = 'none';
    meta.textContent = '—';
    pager.innerHTML = '';

    clearRows(tbody, loader.classList[0]);

    try{
      const j = await fetchJSON(url);
      const items = Array.isArray(j?.data) ? j.data : [];
      const pag = j?.pagination || { page: page, per_page: per, total: items.length };

      if(!items.length) empty.style.display = '';

      const frag = document.createDocumentFragment();
      items.forEach(r => frag.appendChild(makeRow(r, mode)));
      tbody.appendChild(frag);

      const total = Number(pag.total || items.length);
      const pages = Math.max(1, Math.ceil(total / Number(pag.per_page || per)));

      meta.textContent = `Showing page ${pag.page} of ${pages} — ${total} result(s)`;
      buildPager(pager, Number(pag.page||1), pages, (t)=>{
        if(mode==='active') state.active.page = t;
        if(mode==='archived') state.archived.page = t;
        if(mode==='bin') state.bin.page = t;
        loadList(mode);
      });

      // total chip (rough): active + archived total might vary; update best-effort
      if(state.totalChip){
        if(mode === 'active') state.totalChip.textContent = `Active: ${total}`;
        if(mode === 'archived') state.totalChip.textContent = `Archived: ${total}`;
        if(mode === 'bin') state.totalChip.textContent = `Bin: ${total}`;
      }

      if(mode==='active'){ state.active.loaded=true; state.active.dirty=false; }
      if(mode==='archived'){ state.archived.loaded=true; state.archived.dirty=false; }
      if(mode==='bin'){ state.bin.loaded=true; state.bin.dirty=false; }

    }catch(e){
      console.error(e);
      empty.style.display = '';
      meta.textContent = 'Failed to load';
      err(e.message || 'Load error');
    }finally{
      loader.style.display = 'none';
    }
  }

  function visibleTab(){
    const act = document.querySelector('.tab-pane.show.active');
    if(!act) return 'active';
    if(act.id.endsWith('archived')) return 'archived';
    if(act.id.endsWith('bin')) return 'bin';
    return 'active';
  }

  async function refreshVisible(){
    const tab = visibleTab();
    if(tab==='active') return loadList('active');
    if(tab==='archived') return loadList('archived');
    return loadList('bin');
  }

  // ===== actions
  ROOT.addEventListener('click', async (e)=>{
    const btn = e.target.closest('[data-act]');
    if(!btn) return;

    const act = btn.dataset.act;
    const id = btn.dataset.id;
    const title = btn.dataset.title || 'this post';
    if(!id) return;

    // block destructive actions if no permission
    if(['toggle','delete','restore','force'].includes(act) && !state.canWrite){
      err('You are not allowed to perform this action');
      return;
    }

    try{
      if(act === 'toggle'){
        // ✅ Toggle by PATCH update(status)
        // fetch current row info from DOM is hard; just switch optimistic: call update with status flip using POST? not exists.
        // We'll prompt and choose target:
        const tab = visibleTab();
        const toActive = tab === 'archived';

        const {isConfirmed} = await Swal.fire({
          icon: 'question',
          title: toActive ? 'Activate this post?' : 'Archive this post?',
          text: `"${title}" status will be updated.`,
          showCancelButton: true,
          confirmButtonText: 'Yes'
        });
        if(!isConfirmed) return;

        await fetchJSON(API_BASE + '/' + encodeURIComponent(id), {
          method: 'PATCH',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ status: toActive ? 'active' : 'inactive' })
        });

        ok('Status updated');
        // mark dirty and refresh both tabs
        state.active.dirty = true;
        state.archived.dirty = true;
        await refreshVisible();
        return;
      }

      if(act === 'delete'){
        const {isConfirmed} = await Swal.fire({
          icon:'warning',
          title:'Move to Bin?',
          html:`"${esc(title)}" will be moved to Bin.`,
          showCancelButton:true,
          confirmButtonText:'Delete',
          confirmButtonColor:'#ef4444'
        });
        if(!isConfirmed) return;

        await fetchJSON(API_BASE + '/' + encodeURIComponent(id), { method:'DELETE' });
        ok('Moved to Bin');

        state.active.dirty = true;
        state.archived.dirty = true;
        state.bin.dirty = true;
        await refreshVisible();
        return;
      }

      if(act === 'restore'){
        await fetchJSON(API_BASE + '/' + encodeURIComponent(id) + '/restore', { method:'POST' });
        ok('Restored');

        state.active.dirty = true;
        state.archived.dirty = true;
        state.bin.dirty = true;
        await refreshVisible();
        return;
      }

      if(act === 'force'){
        const {isConfirmed} = await Swal.fire({
          icon:'warning',
          title:'Delete permanently?',
          html:`This cannot be undone.<br>"${esc(title)}"`,
          showCancelButton:true,
          confirmButtonText:'Delete permanently',
          confirmButtonColor:'#dc2626'
        });
        if(!isConfirmed) return;

        await fetchJSON(API_BASE + '/' + encodeURIComponent(id) + '/force', { method:'DELETE' });
        ok('Permanently deleted');

        state.bin.dirty = true;
        await refreshVisible();
        return;
      }

    }catch(ex){
      err(ex?.message || 'Action failed');
    }
  });

  // ===== search + per page
  let qTimer;
  qInput.addEventListener('input', ()=>{
    clearTimeout(qTimer);
    qTimer = setTimeout(()=>{
      state.q = String(qInput.value || '').trim();
      state.active.page = 1; state.archived.page = 1; state.bin.page = 1;
      state.active.dirty = true; state.archived.dirty = true; state.bin.dirty = true;
      refreshVisible();
    }, 280);
  });

  perSel.addEventListener('change', ()=>{
    state.per = Math.max(5, Number(perSel.value || 20));
    state.active.page = 1; state.archived.page = 1; state.bin.page = 1;
    state.active.dirty = true; state.archived.dirty = true; state.bin.dirty = true;
    refreshVisible();
  });

  btnRefresh.addEventListener('click', ()=>{
    state.active.dirty = true;
    state.archived.dirty = true;
    state.bin.dirty = true;
    refreshVisible();
  });

  // lazy load when tab opened
  document.addEventListener('shown.bs.tab', (ev)=>{
    const href = ev?.target?.getAttribute('href') || '';
    if(href.includes('active')) loadList('active');
    if(href.includes('archived')) loadList('archived');
    if(href.includes('bin')) loadList('bin');
  });

  // init
  (async ()=>{
    await loadMe();
    state.per = Math.max(5, Number(perSel.value || 20));
    await loadList('active');
  })();

})();
</script>
@endpush
