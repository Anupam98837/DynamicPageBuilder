{{-- resources/views/modules/enquiry/manageCourseEnquirySettings.blade.php --}}
@section('title','Manage Enquiry Course Order')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}"/>

<style>
/* ===== Shell (same baseline as reference) ===== */
.dept-wrap{max-width:1140px;margin:16px auto 40px;overflow:visible}
.panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2);padding:14px}

/* Toolbar */
.mfa-toolbar .form-control{height:40px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)}
.mfa-toolbar .form-select{height:40px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface)}
.mfa-toolbar .btn{height:40px;border-radius:12px}
.mfa-toolbar .btn-light{background:var(--surface);border:1px solid var(--line-strong)}
.mfa-toolbar .btn-primary{background:var(--primary-color);border:none}

/* Table Card */
.table-wrap.card{position:relative;border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:visible;z-index:1}
.table-wrap .card-body{overflow:visible}
.table-responsive{overflow-x:auto;overflow-y:visible;-webkit-overflow-scrolling:touch;}
.table-responsive::-webkit-scrollbar{height:8px}
.table-responsive::-webkit-scrollbar-thumb{background:color-mix(in oklab, var(--muted-color) 25%, transparent);border-radius:999px}
.table-responsive::-webkit-scrollbar-track{background:color-mix(in oklab, var(--muted-color) 8%, transparent);border-radius:999px}

.table{--bs-table-bg:transparent; min-width:980px;}
.table thead th{font-weight:600;color:var(--muted-color);font-size:13px;border-bottom:1px solid var(--line-strong);background:var(--surface)}
.table thead.sticky-top{z-index:3}
.table tbody tr{border-top:1px solid var(--line-soft)}
.table tbody tr:hover{background:var(--page-hover)}

/* UUID cell */
.uuid-cell{display:flex;align-items:center;gap:8px;flex-wrap:nowrap;justify-content:flex-start;}
.uuid-pill{font-size:12px;padding:3px 8px;border-radius:999px;border:1px solid var(--line-strong);background:color-mix(in oklab, var(--muted-color) 10%, transparent);color:var(--ink);white-space:nowrap;flex:0 0 auto;}
.uuid-copy-btn{height:28px;border-radius:10px;padding:0 10px;display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--line-strong);background:var(--surface);flex:0 0 auto;}
.uuid-copy-btn i{font-size:13px;opacity:.9}
.uuid-copy-btn:hover{background:var(--page-hover)}

/* Badges */
.badge-soft{background:color-mix(in oklab, var(--muted-color) 12%, transparent);color:var(--ink)}
.badge-success{background:var(--success-color)!important;color:#fff!important}
.badge-warning{background:var(--warning-color)!important;color:#0b1324!important}
.badge-danger{background:var(--danger-color)!important;color:#fff!important}

/* Drag */
.drag-handle{cursor:grab;display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:10px;border:1px solid var(--line-strong);background:var(--surface)}
.drag-handle:hover{background:var(--page-hover)}
.drag-handle:active{cursor:grabbing}
.drag-ghost{opacity:.55}
.drag-chosen{background:color-mix(in oklab, var(--primary-color) 10%, transparent)}

/* Featured toggle polish */
.form-check.form-switch .form-check-input{cursor:pointer}
.form-check-input:focus{border-color:var(--primary-color);box-shadow:0 0 0 .2rem rgba(158,54,58,.25)}

/* Row cues */
.is-inactive td{background:color-mix(in oklab, var(--muted-color) 6%, transparent)}

/* Loading overlay (same pattern) */
.dep-loading-overlay{position:fixed;inset:0;width:100%;height:100%;background:rgba(0,0,0,.42);display:none;justify-content:center;align-items:center;z-index:9999;backdrop-filter:blur(2px)}
.dep-loading-overlay.is-visible{display:flex}

/* Empty */
.empty{color:var(--muted-color)}

/* Responsive toolbar */
@media (max-width:768px){
  .mfa-toolbar .d-flex{flex-direction:column;gap:12px!important}
  .mfa-toolbar .position-relative{min-width:100%!important}
  .toolbar-buttons{display:flex;gap:8px;flex-wrap:wrap}
  .toolbar-buttons .btn{flex:1;min-width:140px}
}

/* Dark tweaks */
html.theme-dark .panel,
html.theme-dark .table-wrap.card{background:#0f172a;border-color:var(--line-strong)}
html.theme-dark .table thead th{background:#0f172a;border-color:var(--line-strong);color:#94a3b8}
html.theme-dark .uuid-pill{background:rgba(148,163,184,.08)}
html.theme-dark .uuid-copy-btn{background:#0f172a}
html.theme-dark .drag-handle{background:#0f172a}
</style>
@endpush

@section('content')
<div class="dept-wrap">

  {{-- Loading Overlay --}}
  <div id="deo_globalLoading" class="dep-loading-overlay" aria-hidden="true">
    @include('partials.overlay')
  </div>

  {{-- Toolbar --}}
  <div class="row align-items-center g-2 mb-3 mfa-toolbar panel">
    <div class="col-md-7 d-flex align-items-center flex-wrap gap-2">
      <div class="position-relative flex-grow-1" style="min-width:220px;">
        <input id="deo_q" type="text" class="form-control ps-5" placeholder="Search in list (title / slug / program)...">
        <i class="fa fa-search position-absolute" style="left:12px; top:50%; transform:translateY(-50%); opacity:.6;"></i>
      </div>

      <div class="d-flex align-items-center gap-2 ms-md-1">
        <div class="form-check form-switch d-inline-flex align-items-center gap-2 mb-0">
          <input class="form-check-input" type="checkbox" id="deo_include_inactive">
          <label class="form-check-label small text-muted" for="deo_include_inactive">Include inactive</label>
        </div>

        <span id="deo_dirtyBadge" class="badge badge-warning text-uppercase" style="display:none;">
          Unsaved
        </span>
      </div>
    </div>

    <div class="col-md-5 d-flex justify-content-md-end mt-2 mt-md-0">
      <div class="toolbar-buttons d-flex gap-2">
        <button id="deo_btnReload" class="btn btn-light">
          <i class="fa fa-rotate me-1"></i>Reload
        </button>
        <button id="deo_btnReset" class="btn btn-light">
          <i class="fa fa-rotate-left me-1"></i>Reset
        </button>
        <button id="deo_btnSave" class="btn btn-primary" disabled>
          <i class="fa fa-save me-1"></i>Save Order
        </button>
      </div>
    </div>

    <div class="col-12 pt-1">
      <div class="small text-muted">
        <i class="fa-solid fa-hand-pointer me-1"></i>
        Drag rows to set the enquiry dropdown order. Turn <b>Featured</b> ON to pin a course above non-featured ones (within featured group, drag order still applies).
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="card table-wrap">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="deo_table" class="table table-hover table-borderless align-middle mb-0">
          <thead class="sticky-top">
            <tr>
              <th style="width:56px;">MOVE</th>
              <th style="width:90px;">ORDER</th>
              <th style="width:130px;">FEATURED</th>
              <th>COURSE</th>
              <th style="width:340px;">CUSTOM DISPLAY NAME</th>
              <th style="width:140px;">LEVEL</th>
              <th style="width:140px;">TYPE</th>
              <th style="width:280px;">UUID</th>
              <th style="width:110px;">STATUS</th>
            </tr>
          </thead>
          <tbody id="deo_rows">
            <tr id="deo_loaderRow" style="display:none;">
              <td colspan="12" class="p-0">
                <div class="p-4">
                  <div class="placeholder-wave">
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                    <div class="placeholder col-12 mb-2" style="height:18px;"></div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div id="deo_empty" class="empty p-4 text-center" style="display:none;">
        <i class="fa fa-graduation-cap mb-2" style="font-size:32px; opacity:.6;"></i>
        <div>No courses found.</div>
      </div>

      <div class="p-3 border-top" style="border-color:var(--line-strong)!important;">
        <div class="small text-muted" id="deo_meta">—</div>
      </div>
    </div>
  </div>

</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="deo_okToast" class="toast text-bg-success border-0" role="status" aria-live="polite">
    <div class="d-flex">
      <div id="deo_okMsg" class="toast-body">Done</div>
      <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
  <div id="deo_errToast" class="toast text-bg-danger border-0 mt-2" role="alert" aria-live="assertive">
    <div class="d-flex">
      <div id="deo_errMsg" class="toast-body">Something went wrong</div>
      <button class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
(function(){
  if (window.__COURSE_ENQUIRY_ORDER_INIT__) return;
  window.__COURSE_ENQUIRY_ORDER_INIT__ = true;

  /* ===== Auth ===== */
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  if (!TOKEN) {
    Swal.fire('Login required', 'Your session has expired. Please login again.', 'warning')
      .then(()=> location.href = '/');
    return;
  }

  const API_BASE = '/api';
  const SETTINGS_INDEX_ENDPOINT = API_BASE + '/course-enquiry-settings';
  const BULK_UPSERT_ENDPOINT    = SETTINGS_INDEX_ENDPOINT + '/bulk-upsert';

  /* ===== Toast helpers ===== */
  const okToastEl  = document.getElementById('deo_okToast');
  const errToastEl = document.getElementById('deo_errToast');
  const okToast  = new bootstrap.Toast(okToastEl,  { delay: 2200, autohide: true });
  const errToast = new bootstrap.Toast(errToastEl, { delay: 2600, autohide: true });
  const ok  = (m)=>{ errToast.hide(); document.getElementById('deo_okMsg').textContent  = m || 'Done'; okToast.show(); };
  const err = (m)=>{ okToast.hide();  document.getElementById('deo_errMsg').textContent = m || 'Something went wrong'; errToast.show(); };

  /* ===== DOM refs ===== */
  const loaderRow       = document.getElementById('deo_loaderRow');
  const emptyEl         = document.getElementById('deo_empty');
  const metaEl          = document.getElementById('deo_meta');
  const qEl             = document.getElementById('deo_q');
  const includeInactive = document.getElementById('deo_include_inactive');
  const btnReload       = document.getElementById('deo_btnReload');
  const btnReset        = document.getElementById('deo_btnReset');
  const btnSave         = document.getElementById('deo_btnSave');
  const dirtyBadge      = document.getElementById('deo_dirtyBadge');
  const globalLoader    = document.getElementById('deo_globalLoading');

  /* ===== State ===== */
  let originalSnapshot = []; 
  let isDirty = false;
  let sortableInstances = [];

  /* ===== Utils ===== */
  function getAuthHeaders(isJson = true){
    const h = { 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' };
    if (isJson) h['Content-Type'] = 'application/json';
    return h;
  }

  function escapeHtml(s){
    const map={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;','`':'&#96;'};
    return (s==null?'':String(s)).replace(/[&<>"'`]/g,ch=>map[ch]);
  }

  async function copyText(text){
    const val = (text || '').trim();
    if (!val) return;
    try{
      if (navigator.clipboard && window.isSecureContext){
        await navigator.clipboard.writeText(val);
      } else {
        const ta = document.createElement('textarea');
        ta.value = val;
        ta.setAttribute('readonly','readonly');
        ta.style.position='fixed';
        ta.style.left='-9999px';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        ta.remove();
      }
      ok('UUID copied');
    } catch(e){
      err('Failed to copy UUID');
    }
  }

  let _loadCount = 0, _hideTimer = null;
  function showGlobalLoading(show){
    _loadCount += show ? 1 : -1;
    if (_loadCount < 0) _loadCount = 0;
    if (show) {
      if (_hideTimer) { clearTimeout(_hideTimer); _hideTimer = null; }
      globalLoader.classList.add('is-visible');
      document.body.classList.add('overflow-hidden');
    } else {
      _hideTimer = setTimeout(() => {
        if (_loadCount === 0) {
          globalLoader.classList.remove('is-visible');
          document.body.classList.remove('overflow-hidden');
        }
      }, 120);
    }
  }

  function markDirty(dirty){
    isDirty = !!dirty;
    dirtyBadge.style.display = isDirty ? '' : 'none';
    btnSave.disabled = !isDirty;
  }

  function showLoader(show){
    loaderRow.style.display = show ? '' : 'none';
  }

  function clearRows(){
    const table = document.getElementById('deo_table');
    if (table) {
      table.querySelectorAll('tbody').forEach(tb => tb.remove());
    }
  }

  function normalizeSortOrder(v){
    const n = Number(v);
    if (!Number.isFinite(n)) return 999999;
    return n;
  }

  function getRowText(tr){
    return (tr.dataset.search || '').toLowerCase();
  }

  function applySearch(){
    const term = (qEl.value || '').trim().toLowerCase();
    const all = document.querySelectorAll('#deo_table tr[data-course-id]');
    if (!term) {
      all.forEach(tr => { tr.classList.remove('table-secondary'); tr.style.opacity = ''; });
      metaEl.textContent = `Total: ${all.length} course(s)`;
      return;
    }

    let hit = 0;
    all.forEach(tr => {
      const ok = getRowText(tr).includes(term);
      tr.style.opacity = ok ? '' : '0.35';
      tr.classList.toggle('table-secondary', ok);
      if (ok) hit++;
    });
    metaEl.textContent = `Matched: ${hit} / ${all.length}`;
  }

  function ensureSortable(){
    if (sortableInstances.length > 0) {
      sortableInstances.forEach(s => s.destroy());
      sortableInstances = [];
    }

    const table = document.getElementById('deo_table');
    if (!table) return;

    const tbodys = table.querySelectorAll('tbody[data-section]');
    tbodys.forEach(tb => {
      const s = new Sortable(tb, {
        handle: '.drag-handle',
        animation: 160,
        ghostClass: 'drag-ghost',
        chosenClass: 'drag-chosen',
        draggable: 'tr[data-course-id]',
        group: { name: 'section_items', pull: false, put: false }, // lock items inside their section
        onEnd: () => {
          recalcOrderBadges();
          markDirty(true);
          applySearch();
        }
      });
      sortableInstances.push(s);
    });
  }

  function recalcOrderBadges(){
    const table = document.getElementById('deo_table');
    if (!table) return;

    // Inside Sortable.js multiple list, elements stay inside their tbodys.
    // Index mapping sequentially sequentially through all tr across multiple content lists.
    let absoluteIdx = 1;
    const allTrs = Array.from(table.querySelectorAll('tr[data-course-id]'));
    allTrs.forEach((tr) => {
      const badge = tr.querySelector('[data-order-badge]');
      if (badge) badge.textContent = String(absoluteIdx++);
    });
  }

  function buildBulkPayload(){
    const items = [];
    const table = document.getElementById('deo_table');
    if (!table) return { items: [] };

    const trs = Array.from(table.querySelectorAll('tr[data-course-id]'));
    trs.forEach((tr, idx) => {
      const courseId = Number(tr.dataset.courseId || 0);
      const featured = tr.querySelector('.feat-toggle')?.checked ? true : false;
      const customName = tr.querySelector('.custom-name-input')?.value.trim();
      items.push({
        course: courseId,               
        sort_order: idx + 1,              
        featured: featured,
        custom_name: customName || null
      });
    });
    return { items };
  }

  function render(list){
    clearRows();
    const table = document.getElementById('deo_table');
    if (!table) return;

    if (!Array.isArray(list) || list.length === 0) {
      emptyEl.style.display = '';
      metaEl.textContent = 'Total: 0';
      return;
    }

    emptyEl.style.display = 'none';

    // Sort absolutely first for inner mapping order presets
    list = list.slice().sort((a,b)=>{
      const fa = Number(a.featured || 0);
      const fb = Number(b.featured || 0);
      if (fa !== fb) return fb - fa;

      const sa = normalizeSortOrder(a.sort_order);
      const sb = normalizeSortOrder(b.sort_order);
      if (sa !== sb) return sa - sb;

      return String(a.title || '').localeCompare(String(b.title || ''), undefined, { sensitivity:'base' });
    });

    // Categorization logic
    const groups = {
      'B.Tech in': [],
      'AICTE Bachelor Degree': [],
      'M.Tech in': [],
      'MCA': [],
      'MBA': [],
      'Other Courses': []
    };

    list.forEach(r => {
      const t = (r.title || '').toUpperCase();
      const approvals = (r.approvals || '').toUpperCase();
      const level = (r.program_level || '').toLowerCase();

      if (t.includes('M.TECH') || t.includes('M. TECH')) {
        groups['M.Tech in'].push(r);
      } else if (t.includes('MCA')) {
        groups['MCA'].push(r);
      } else if (t.includes('MBA')) {
        groups['MBA'].push(r);
      } else if (t.includes('BCA') || t.includes('BBA')) {
        groups['AICTE Bachelor Degree'].push(r);
      } else if (level === 'ug') {
        groups['B.Tech in'].push(r);
      } else {
        groups['Other Courses'].push(r);
      }
    });

    let globalIndex = 0;
    for (const [title, items] of Object.entries(groups)) {
      if (items.length > 0) {
        const tbody = document.createElement('tbody');
        tbody.dataset.section = title;
        
        // Header row
        const headerTr = document.createElement('tr');
        headerTr.style.background = 'rgba(158, 54, 58, 0.04)';
        headerTr.style.fontWeight = '600';
        headerTr.innerHTML = `<td colspan="12" style="padding: 10px 14px; font-size: 13.5px; color: var(--primary-color); border-bottom: 2px solid var(--line-strong);"><i class="fa fa-folder-open me-2"></i> ${title}</td>`;
        tbody.appendChild(headerTr);

        items.forEach(r => {
          const tr = document.createElement('tr');
          tr.dataset.courseId = String(r.id || '');
          tr.dataset.search = [
            r.title || '',
            r.slug || '',
            r.program_level || '',
            r.program_type || ''
          ].join(' ').toLowerCase();

          if (r.active === false) tr.classList.add('is-inactive');

          const uuid = r.uuid ? escapeHtml(r.uuid) : '';
          const statusHtml = (r.active === false)
            ? `<span class="badge badge-warning text-uppercase">Inactive</span>`
            : `<span class="badge badge-success text-uppercase">Active</span>`;

          tr.innerHTML = `
            <td>
              <button type="button" class="drag-handle" title="Drag to reorder">
                <i class="fa-solid fa-grip-vertical" style="opacity:.8;"></i>
              </button>
            </td>
            <td>
              <span class="badge badge-soft" data-order-badge>${++globalIndex}</span>
            </td>
            <td>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input feat-toggle" type="checkbox" ${r.featured ? 'checked' : ''}>
                <label class="form-check-label small text-muted">Yes</label>
              </div>
            </td>
            <td>
              <div class="fw-semibold">${escapeHtml(r.title || '')}</div>
              ${r.slug ? `<div class="text-muted small">Slug: ${escapeHtml(r.slug)}</div>` : `<div class="text-muted small">—</div>`}
            </td>
            <td>
              <input type="text" class="form-control form-control-sm custom-name-input" 
                     value="${escapeHtml(r.custom_name || '')}" 
                     placeholder="Original if blank..."
                      style="width:100%; min-width:300px; max-width:340px; border-radius:8px; height:32px;">
            </td>
            <td>${r.program_level ? escapeHtml(r.program_level) : `<span class="text-muted">—</span>`}</td>
            <td>${r.program_type ? escapeHtml(r.program_type) : `<span class="text-muted">—</span>`}</td>
            <td>
              ${uuid ? `
                <div class="uuid-cell">
                  <code class="uuid-pill font-monospace">${uuid}</code>
                  <button type="button" class="uuid-copy-btn" data-copy="${uuid}" title="Copy UUID">
                    <i class="fa-regular fa-copy"></i>
                  </button>
                </div>
              ` : `<span class="text-muted">—</span>`}
            </td>
            <td>${statusHtml}</td>
          `;
          tbody.appendChild(tr);
        });

        table.appendChild(tbody);
      }
    }

    ensureSortable();
    applySearch();

    metaEl.textContent = `Total: ${list.length} course(s)`;
  }

  async function fetchList(){
    showLoader(true);
    showGlobalLoading(true);
    markDirty(false);

    try{
      const params = new URLSearchParams();
      params.set('include_inactive', includeInactive.checked ? '1' : '0');

      const res = await fetch(`${SETTINGS_INDEX_ENDPOINT}?${params.toString()}`, {
        headers: getAuthHeaders(false)
      });

      const j = await res.json().catch(()=> ({}));
      if (!res.ok) throw new Error(j?.message || j?.error || 'Failed to load');

      const list = Array.isArray(j.data) ? j.data : [];
      originalSnapshot = list.map(x => ({
        id: x.id, uuid: x.uuid, title: x.title, slug: x.slug,
        program_level: x.program_level, program_type: x.program_type,
        active: x.active,
        sort_order: x.sort_order,
        featured: x.featured,
        custom_name: x.custom_name
      }));

      render(list);
    } catch(e){
      console.error(e);
      err(e?.message || 'Failed to load');
      clearRows();
      emptyEl.style.display = '';
      metaEl.textContent = e?.message || 'Failed to load';
    } finally {
      showLoader(false);
      showGlobalLoading(false);
    }
  }

  async function resetToServer(){
    if (isDirty) {
      const { isConfirmed } = await Swal.fire({
        icon: 'question',
        title: 'Discard changes?',
        text: 'You have unsaved changes. Reset will discard them.',
        showCancelButton: true,
        confirmButtonText: 'Yes, discard',
      });
      if (!isConfirmed) return;
    }
    qEl.value = '';
    await fetchList();
  }

  async function saveBulk(){
    if (!isDirty) return;

    const payload = buildBulkPayload();

    showGlobalLoading(true);
    btnSave.disabled = true;

    try{
      const res = await fetch(BULK_UPSERT_ENDPOINT, {
        method: 'POST',
        headers: getAuthHeaders(true),
        body: JSON.stringify(payload)
      });

      const j = await res.json().catch(()=> ({}));
      if (!res.ok) throw new Error(j?.message || j?.error || 'Save failed');

      ok('Order saved');
      markDirty(false);

      await fetchList();
    } catch(e){
      console.error(e);
      err(e?.message || 'Save failed');
      btnSave.disabled = false;
    } finally {
      showGlobalLoading(false);
    }
  }

  /* ===== Events ===== */

  document.addEventListener('click', (e)=>{
    const btn = e.target.closest('[data-copy]');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    copyText(btn.getAttribute('data-copy') || '');
  });

  document.addEventListener('change', (e)=>{
    const t = e.target.closest('.feat-toggle');
    if (!t) return;
    recalcOrderBadges();     
    markDirty(true);
    applySearch();
  });

  document.addEventListener('input', (e)=>{
    if (e.target.closest('.custom-name-input')) {
      markDirty(true);
    }
  });

  let searchTimer;
  qEl.addEventListener('input', ()=>{
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applySearch, 120);
  });

  includeInactive.addEventListener('change', ()=>{
    resetToServer();
  });

  btnReload.addEventListener('click', ()=> fetchList());
  btnReset.addEventListener('click', ()=> resetToServer());
  btnSave.addEventListener('click', ()=> saveBulk());

  fetchList();

})();
</script>
@endpush