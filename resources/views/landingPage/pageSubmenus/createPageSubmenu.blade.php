{{-- resources/views/modules/pages/createPageSubmenu.blade.php --}}
@section('title','Create Page Submenu')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<style>
  .sm-wrap{max-width:1100px;margin:16px auto 40px;}
  .dim{position:absolute;inset:0;background:rgba(0,0,0,.06);display:none;align-items:center;justify-content:center;z-index:10}
  .dim.show{display:flex}
  .spin{width:18px;height:18px;border:3px solid #0001;border-top-color:var(--accent-color);border-radius:50%;animation:rot 1s linear infinite}
  @keyframes rot{to{transform:rotate(360deg)}}

  .err{font-size:12px;color:var(--danger-color);display:none;margin-top:6px}
  .err:not(:empty){display:block}

  .pill{border:1px solid var(--line-strong);border-radius:999px;padding:3px 8px;font-size:12px;color:var(--muted-color)}
  .badge-soft{background:var(--t-primary);color:#0f766e;border:1px solid rgba(201,75,80,.26);border-radius:999px;padding:2px 8px;font-size:11px;font-weight:700}
  .pick-parent-btn{white-space:nowrap}

  /* Switch alignment */
  .switch-inline{display:flex;align-items:center;gap:10px}
  .switch-inline .form-check{margin:0}
  .switch-inline .form-check-label{margin:0;font-weight:600}

  /* Sections */
  .section-label{
    font-size:13px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.06em;
    color:var(--muted-color);
    margin-bottom:4px;
  }

  /* ===== Modern tree ===== */
  .tree-wrap{position:relative;min-height:140px}
  .tree-loader{
    position:absolute; inset:0; display:none; align-items:center; justify-content:center;
    background: color-mix(in oklab, var(--surface) 86%, transparent);
    z-index:2;
  }
  .tree-loader.show{display:flex}
  .tree-loader .spin{width:22px;height:22px;border-width:3px}

  .tree{--pad:12px; --rad:12px}
  .tree ul{list-style:none;margin:0;padding-left:18px;border-left:1px dashed var(--line-strong)}
  .tree li{margin:4px 0 4px 0;position:relative}
  .tree-node{
    display:flex;align-items:center;gap:10px;
    padding:8px 10px;border:1px solid var(--line-strong);border-radius:var(--rad);
    background:var(--surface);
  }
  .tree-node .toggle{
    width:24px;height:24px;border:1px solid var(--line-strong);border-radius:8px;
    display:inline-grid;place-items:center;cursor:pointer;flex:0 0 auto;
    background:color-mix(in oklab, var(--surface) 92%, var(--ink) 0%);
  }
  .tree-node .toggle i{transition:transform .18s ease}
  .tree-node[data-open="1"] .toggle i{transform:rotate(90deg)}
  .tree-title{font-weight:600}
  .tree-meta{font-size:12px;color:var(--muted-color)}
  .tree-actions{margin-left:auto;display:flex;gap:8px}
  .tree .children{margin-top:6px;display:none}
  .tree-node[data-open="1"] + .children{display:block}
  .tree-empty{padding:16px;border:1px dashed var(--line-strong);border-radius:12px;color:var(--muted-color);text-align:center}

  /* Button spinners */
  .btn .spinner-border{width:1rem;height:1rem;border-width:.18rem}

  /* Compact inputs in modal header */
  .modal-tools .input-group{height:36px}
  .modal-tools .form-control{height:36px}
</style>
@endpush

@section('content')
<div id="psmRoot" class="sm-wrap">
  <div class="sm card shadow-2" style="position:relative">
    <div class="dim" id="busy"><div class="spin" aria-label="Working…"></div></div>

    <div class="card-header">
      <div class="sm-head d-flex align-items-center gap-2">
        <i class="fa-solid fa-sitemap"></i>
        <strong>Create Page Submenu</strong>
        <span class="hint text-muted" id="hint"></span>
      </div>
    </div>

    <div class="card-body">

      {{-- ===== BELONGS TO PAGE (REQUIRED) ===== --}}
      <div class="section-label">Belongs To Page</div>
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label">Page ID <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-file-lines"></i></span>
            {{-- UPDATED: dropdown pulled from pages table (via API list) --}}
            <select class="form-select" id="page_id">
              <option value="">Loading pages…</option>
            </select>
          </div>
          <small class="text-muted tiny">
            This submenu will be created under this page (required). Parent picker tree also loads from this Page ID.
          </small>
          <div class="err" data-for="page_id"></div>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Page Slug <span class="pill ms-1">optional</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-link"></i></span>
            <input type="text" class="form-control" id="belongs_page_slug" maxlength="200" placeholder="(optional) just for reference">
          </div>
          <small class="text-muted tiny">
            Optional note/reference only. Creation uses <b>Page ID</b>.
          </small>
        </div>
      </div>

      <div class="divider-soft my-3"></div>

      {{-- ===== SUBMENU SECTION (same UI as Header Menu) ===== --}}
      <div class="section-label">Submenu</div>
      <div class="row g-3">

        {{-- Title --}}
        <div class="col-12 col-md-6">
          <label class="form-label">Submenu Title <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-heading"></i></span>
            <input type="text" class="form-control" id="title" maxlength="150" placeholder="e.g., Faculty, Placements, Labs">
          </div>
          <div class="err" data-for="title"></div>
        </div>

        {{-- Submenu Slug + Auto --}}
        <div class="col-12 col-md-6">
          <div class="switch-inline mb-1">
            <label class="form-label mb-0">Submenu Slug</label>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="slugAuto" checked>
            </div>
            <label class="form-check-label tiny text-muted" for="slugAuto">Auto from title</label>
          </div>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-link"></i></span>
            <input type="text" class="form-control" id="slug" maxlength="160" placeholder="auto-generated" disabled>
            <button class="btn btn-light" type="button" id="btnRegen" title="Regenerate from title" disabled>
              <i class="fa-solid fa-rotate"></i>
            </button>
          </div>
          <small class="text-muted tiny">
            Submenu slug is auto-generated. Fallback when no Destination URL/Slug is set.
          </small>
          <div class="err" data-for="slug"></div>
        </div>

        {{-- Description --}}
        <div class="col-12">
          <label class="form-label">Description <span class="pill ms-1">optional</span></label>
          <textarea class="form-control" id="description" rows="3" placeholder="Short internal note (optional)"></textarea>
          <div class="err" data-for="description"></div>
        </div>

        {{-- Parent picker --}}
        <div class="col-12 col-md-8">
          <label class="form-label">Parent Submenu</label>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <span id="parentBadge" class="badge-soft">Self (Root)</span>
            <button class="btn btn-light pick-parent-btn" type="button" id="btnPickParent">
              <i class="fa-solid fa-diagram-project me-1"></i>Choose parent
            </button>
            <button class="btn btn-outline-danger btn-sm" type="button" id="btnClearParent">
              <i class="fa-solid fa-xmark me-1"></i>Clear
            </button>
          </div>
          <input type="hidden" id="parent_id">
          <div class="err" data-for="parent_id"></div>
        </div>

        {{-- Switches --}}
        <div class="col-12 col-md-4">
          <div class="row g-3">
            <div class="col-12 switch-inline">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="active" checked>
              </div>
              <label class="form-check-label" for="active">Active</label>
            </div>
          </div>
        </div>
      </div>

      <div class="divider-soft my-3"></div>

      {{-- ===== DESTINATION SECTION (same fields as header menu "Page") ===== --}}
      <div class="section-label">Destination</div>
      <div class="row g-3">

        {{-- Destination Slug --}}
        <div class="col-12 col-md-6">
          <label class="form-label">Destination Page Slug <span class="pill ms-1">optional</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-link"></i></span>
            <input type="text" class="form-control" id="page_slug" maxlength="160" placeholder="e.g., placements, faculty-list">
          </div>
          <small class="text-muted tiny">
            If Destination URL is empty and Destination Slug is set, clicking will go to <code>/page-slug</code>.
          </small>
          <div class="err" data-for="page_slug"></div>
        </div>

        {{-- Destination Shortcode --}}
        <div class="col-12 col-md-6">
          <label class="form-label">Destination Page Shortcode <span class="pill ms-1">optional</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-code"></i></span>
            <input type="text" class="form-control" id="page_shortcode" maxlength="100" placeholder="For CMS embedding, e.g. placement-list">
          </div>
          <div class="err" data-for="page_shortcode"></div>
        </div>

        {{-- Destination URL --}}
        <div class="col-12">
          <label class="form-label">Destination URL <span class="pill ms-1">optional</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-globe"></i></span>
            <input type="text" class="form-control" id="page_url" maxlength="255" placeholder="https://example.com or /internal/path">
          </div>
          <small class="text-muted tiny">
            If set, this URL is used. Else if Destination Slug is set, it will use <code>/page-slug</code>.
            If both are empty, it falls back to <code>/submenu-slug</code>.
          </small>
          <div class="err" data-for="page_url"></div>
        </div>
      </div>

      <div class="divider-soft my-3"></div>

      {{-- Actions --}}
      <div class="d-flex justify-content-between">
        <a href="javascript:history.back()" class="btn btn-light">
          <i class="fa-solid fa-arrow-left-long"></i> Back
        </a>
        <div class="btn-group">
          <button class="btn btn-secondary" type="button" id="btnReset">
            <span class="label"><i class="fa-regular fa-trash-can"></i> Reset</span>
            <span class="spinner-border d-none" role="status" aria-hidden="true"></span>
          </button>
          <button class="btn btn-primary" type="button" id="btnCreate">
            <span class="label"><i class="fa-solid fa-plus"></i> Create Submenu</span>
            <span class="spinner-border d-none" role="status" aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Parent Picker Modal --}}
  <div class="modal fade" id="parentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title"><i class="fa-solid fa-diagram-project me-2"></i>Pick Parent Submenu</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="d-flex justify-content-between align-items-center mb-2 modal-tools">
            <div class="d-flex align-items-center gap-2">
              <button class="btn btn-light btn-sm" type="button" id="btnPickSelf">
                <span class="label"><i class="fa-regular fa-circle-check"></i> Select “Self (Root)”</span>
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
              </button>
              <button class="btn btn-light btn-sm" type="button" id="btnReloadTree">
                <span class="label"><i class="fa-solid fa-rotate"></i> Reload</span>
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
              </button>
            </div>
            <div class="input-group" style="max-width: 340px;">
              <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
              <input type="text" class="form-control" id="treeSearch" placeholder="Search by title…">
            </div>
          </div>

          <div class="tree-wrap">
            <div class="tree-loader" id="treeLoader">
              <div class="spin me-2"></div><span class="text-muted">Loading tree…</span>
            </div>
            <div id="treeEmpty" class="tree-empty" style="display:none">No submenus found.</div>
            <div id="treeRoot" class="tree"></div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Toasts --}}
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080">
    <div id="toastSuccess" class="toast align-items-center text-bg-success border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body" id="toastSuccessText">Done</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <div id="toastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert">
      <div class="d-flex">
        <div class="toast-body" id="toastErrorText">Something went wrong</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
  const busy = document.getElementById('busy');

  const toastSuccess = new bootstrap.Toast(document.getElementById('toastSuccess'));
  const toastError   = new bootstrap.Toast(document.getElementById('toastError'));
  const ok  = (m)=>{ document.getElementById('toastSuccessText').textContent = m||'Done'; toastSuccess.show(); };
  const err = (m)=>{ document.getElementById('toastErrorText').textContent = m||'Something went wrong'; toastError.show(); };

  const byId = (id)=>document.getElementById(id);

  const headers = (() => {
    const t = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
    if (!t) { alert('Session expired. Please login again.'); location.href='/'; }
    return { 'Authorization':'Bearer '+t, 'Accept':'application/json', 'Content-Type':'application/json' };
  })();

  const els = {
    // belongs-to page
    pageId: byId('page_id'),
    belongsPageSlug: byId('belongs_page_slug'),

    // submenu
    title: byId('title'),
    desc: byId('description'),
    slug: byId('slug'),
    slugAuto: byId('slugAuto'),
    btnRegen: byId('btnRegen'),

    // destination
    pageSlug: byId('page_slug'),
    pageShortcode: byId('page_shortcode'),
    pageUrl: byId('page_url'),

    // parent
    parentId: byId('parent_id'),
    parentBadge: byId('parentBadge'),
    active: byId('active'),

    // actions
    btnCreate: byId('btnCreate'),
    btnReset: byId('btnReset'),

    // modal + tree
    btnPickParent: byId('btnPickParent'),
    btnClearParent: byId('btnClearParent'),
    parentModal: new bootstrap.Modal(byId('parentModal')),
    treeRoot: byId('treeRoot'),
    treeSearch: byId('treeSearch'),
    treeLoader: byId('treeLoader'),
    treeEmpty: byId('treeEmpty'),
    btnPickSelf: byId('btnPickSelf'),
    btnReloadTree: byId('btnReloadTree'),
  };

  // CHANGE THIS if your manage route differs
  const afterCreateRedirect = '/page/submenus/manage';

  /* ---------- utilities ---------- */
  function showBusy(on){ busy.classList.toggle('show', !!on); }
  function showError(field, msg){
    const el = document.querySelector(`.err[data-for="${field}"]`);
    if (!el) return;
    el.textContent = msg || '';
    el.style.display = msg ? 'block' : 'none';
  }
  function clearErrors(){ document.querySelectorAll('.err').forEach(e=>{ e.textContent=''; e.style.display='none'; }); }
  function slugify(str){
    return String(str||'')
      .normalize('NFKD')
      .replace(/[\u0300-\u036f]/g,'')
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g,'-')
      .replace(/(^-|-$)/g,'');
  }
  async function fetchJSON(url){
    const r = await fetch(url, { headers });
    let j={}; try{ j=await r.json(); }catch{}
    if (!r.ok) throw new Error(j?.message || ('HTTP '+r.status));
    return j;
  }

  function setBtnBusy(btn, on, newLabel){
    if (!btn) return;
    const label = btn.querySelector('.label');
    const spin  = btn.querySelector('.spinner-border');
    if (label && typeof newLabel === 'string') label.innerHTML = newLabel;
    btn.disabled = !!on;
    if (spin) spin.classList.toggle('d-none', !on);
  }
  function oneFlight(fn){
    let inflight = false;
    return async (...args)=>{
      if (inflight) return;
      inflight = true;
      try{ return await fn(...args); }
      finally{ inflight = false; }
    };
  }

  function getPageId(){
    const v = parseInt(String(els.pageId.value||'').trim(), 10);
    return Number.isFinite(v) && v > 0 ? v : 0;
  }

  /* ---------- pages dropdown (pull from pages table) ---------- */
  function setPagesDropdownStateLoading(){
    els.pageId.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = 'Loading pages…';
    els.pageId.appendChild(opt);
    els.pageId.disabled = true;
  }
  function setPagesDropdownStateError(){
    els.pageId.innerHTML = '';
    const opt0 = document.createElement('option');
    opt0.value = '';
    opt0.textContent = 'Unable to load pages';
    els.pageId.appendChild(opt0);
    els.pageId.disabled = false;
  }
  function setPagesDropdownOptions(pages){
    els.pageId.innerHTML = '';
    const opt0 = document.createElement('option');
    opt0.value = '';
    opt0.textContent = 'Select a page…';
    els.pageId.appendChild(opt0);

    pages.forEach(p=>{
      const id = parseInt(p?.id ?? p?.page_id, 10);
      if (!Number.isFinite(id) || id <= 0) return;

      const title = (p?.title ?? p?.name ?? p?.page_title ?? '').toString().trim();
      const slug  = (p?.slug ?? p?.page_slug ?? '').toString().trim();

      const label =
        (title ? title : (slug ? ('/' + slug) : ('Page #' + id))) +
        (slug && title ? ('  •  /' + slug) : '');

      const opt = document.createElement('option');
      opt.value = String(id);
      opt.textContent = `#${id} — ${label}`;
      els.pageId.appendChild(opt);
    });

    els.pageId.disabled = false;
  }
  function extractPagesArray(j){
    // supports: {data:[...]}, {pages:[...]}, {data:{data:[...]}} (pagination), {items:[...]}
    if (Array.isArray(j?.data)) return j.data;
    if (Array.isArray(j?.pages)) return j.pages;
    if (Array.isArray(j?.items)) return j.items;
    if (Array.isArray(j?.data?.data)) return j.data.data;
    return [];
  }
  async function loadPagesDropdown(){
    setPagesDropdownStateLoading();

    // try common endpoints (use whichever exists in your project)
    const candidates = [
  '/api/page-submenus/pages?limit=500'
];


    for (const url of candidates){
      try{
        const j = await fetchJSON(url);
        const arr = extractPagesArray(j);
        if (Array.isArray(arr) && arr.length){
          setPagesDropdownOptions(arr);
          return;
        }
      }catch(e){
        // try next candidate silently
      }
    }

    setPagesDropdownStateError();
    err('Unable to load pages list for Page ID dropdown.');
  }

  // when page changes, reset parent selection (parent tree is page-specific)
  els.pageId.addEventListener('change', ()=>{
    clearErrors();
    setParent('', 'Self (Root)');
  });

  /* ---------- slug auto (submenu slug) ---------- */
  function maybeUpdateSlug(){
    if (!els.slugAuto.checked) return;
    els.slug.value = slugify(els.title.value);
  }
  els.title.addEventListener('input', maybeUpdateSlug);
  els.slugAuto.addEventListener('change', ()=>{
    const on = els.slugAuto.checked;
    els.slug.disabled = on;
    els.btnRegen.disabled = on;
    if (on) maybeUpdateSlug();
  });
  els.btnRegen.addEventListener('click', ()=>{ els.slug.value = slugify(els.title.value); });

  /* ---------- parent selector (tree) ---------- */
  function setParent(id, label){
    els.parentId.value = id || '';
    els.parentBadge.textContent = id ? `#${id}: ${label}` : 'Self (Root)';
  }
  els.btnClearParent.addEventListener('click', ()=> setParent('', 'Self (Root)'));

  function closeParentPicker(){
    try { els.parentModal.hide(); } catch {}
    els.treeLoader.classList.remove('show');
    setTimeout(()=>{
      document.querySelectorAll('.modal-backdrop').forEach(b=>b.remove());
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('padding-right');
    }, 50);
  }
  byId('parentModal').addEventListener('hidden.bs.modal', closeParentPicker);

  els.btnPickSelf.addEventListener('click', ()=>{
    setBtnBusy(els.btnPickSelf, true, '<i class="fa-regular fa-circle-check"></i> Select “Self (Root)”');
    setParent('', 'Self (Root)');
    setTimeout(()=>{ setBtnBusy(els.btnPickSelf, false); closeParentPicker(); }, 150);
  });

  function renderTree(nodes){
    els.treeRoot.innerHTML = '';
    if (!nodes || !nodes.length){
      els.treeEmpty.style.display = 'block';
      return;
    }
    els.treeEmpty.style.display = 'none';

    const ul = document.createElement('ul');
    ul.className = 'm-0 p-0';

    function makeNode(n, depth=0){
      const li = document.createElement('li');

      const node = document.createElement('div');
      node.className = 'tree-node';
      node.dataset.open = (depth<=1 ? '1' : '0');

      const toggle = document.createElement('div');
      toggle.className = 'toggle';
      toggle.innerHTML = '<i class="fa-solid fa-chevron-right tiny"></i>';
      if (!n.children || !n.children.length) toggle.style.visibility = 'hidden';

      const title = document.createElement('div');
      title.className = 'tree-title';
      title.textContent = n.title || '-';

      const meta = document.createElement('div');
      meta.className = 'tree-meta';
      const slugText   = n.slug ? '/' + n.slug : '';
      const pageSlug   = n.page_slug ? ' • dest: /' + n.page_slug : '';
      const pageUrl    = n.page_url ? ' • url: ' + n.page_url : '';
      const statusText = n.active ? ' • active' : ' • inactive';
      meta.textContent = slugText + pageSlug + pageUrl + statusText;

      const actions = document.createElement('div');
      actions.className = 'tree-actions';

      const pickBtn = document.createElement('button');
      pickBtn.type = 'button';
      pickBtn.className = 'btn btn-sm btn-outline-primary';
      pickBtn.innerHTML = '<span class="label"><i class="fa-regular fa-circle-check me-1"></i>Use as parent</span><span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>';
      pickBtn.addEventListener('click', ()=>{
        setBtnBusy(pickBtn, true);
        setParent(n.id, n.title || '-');
        setTimeout(()=>{ setBtnBusy(pickBtn, false); closeParentPicker(); }, 120);
      });

      actions.appendChild(pickBtn);

      node.appendChild(toggle);
      node.appendChild(title);
      node.appendChild(meta);
      node.appendChild(actions);

      li.appendChild(node);

      const childrenWrap = document.createElement('div');
      childrenWrap.className = 'children';
      if (n.children && n.children.length){
        const inner = document.createElement('ul');
        n.children.forEach(c => inner.appendChild(makeNode(c, depth+1)));
        childrenWrap.appendChild(inner);
      } else {
        const empty = document.createElement('div');
        empty.className = 'tiny text-muted ps-2';
        empty.textContent = 'No children';
        childrenWrap.appendChild(empty);
      }
      li.appendChild(childrenWrap);

      toggle.addEventListener('click', ()=>{
        const open = node.dataset.open === '1';
        node.dataset.open = open ? '0' : '1';
      });

      return li;
    }

    nodes.forEach(n => ul.appendChild(makeNode(n, 0)));
    els.treeRoot.appendChild(ul);

    // search
    els.treeSearch.value = '';
    els.treeSearch.oninput = function(){
      const q = this.value.trim().toLowerCase();
      els.treeRoot.querySelectorAll('.tree-node').forEach(nd=>{
        const title = (nd.querySelector('.tree-title')?.textContent || '').toLowerCase();
        const meta  = (nd.querySelector('.tree-meta')?.textContent || '').toLowerCase();
        const match = !q || title.includes(q) || meta.includes(q);
        nd.parentElement.style.display = match ? '' : 'none';
      });

      if (q){
        els.treeRoot.querySelectorAll('.tree-node').forEach(nd => nd.dataset.open = '1');
      }
    };
  }

  async function loadTree(){
    const pid = getPageId();
    if (!pid){
      showError('page_id', 'Page ID is required to load the parent tree.');
      els.pageId.focus();
      return;
    }

    els.treeRoot.innerHTML = '';
    els.treeEmpty.style.display='none';
    els.treeLoader.classList.add('show');
    setBtnBusy(els.btnReloadTree, true);

    try{
      const j = await fetchJSON('/api/page-submenus/tree?only_active=0&page_id=' + encodeURIComponent(pid));
      renderTree(Array.isArray(j.data) ? j.data : []);
    }catch(e){
      console.error(e);
      els.treeEmpty.style.display='block';
      els.treeRoot.innerHTML='';
    }finally{
      els.treeLoader.classList.remove('show');
      setBtnBusy(els.btnReloadTree, false);
    }
  }

  els.btnPickParent.addEventListener('click', ()=>{
    clearErrors();
    loadTree();
    els.parentModal.show();
  });
  els.btnReloadTree.addEventListener('click', loadTree);

  /* ---------- Create (one-click, backend is idempotent via page_id+slug) ---------- */
  const createSubmenu = oneFlight(async function(){
    clearErrors();

    const pid = getPageId();
    if (!pid){
      showError('page_id','Page ID is required');
      els.pageId.focus();
      return;
    }

    if (!els.title.value.trim()){
      showError('title','Title is required');
      els.title.focus();
      return;
    }

    if (els.slugAuto.checked){
      els.slug.value = slugify(els.title.value);
    }

    const payload = {
      page_id: pid, // REQUIRED
      title: els.title.value.trim(),
      description: els.desc.value.trim() || null,
      slug: els.slug.value.trim() || undefined,
      parent_id: els.parentId.value ? parseInt(els.parentId.value,10) : null,
      active: !!els.active.checked,

      // destination
      page_slug: els.pageSlug.value.trim() || null,
      page_shortcode: els.pageShortcode.value.trim() || null,
      page_url: els.pageUrl.value.trim() || null
    };

    setBtnBusy(els.btnCreate, true, '<i class="fa-solid fa-plus"></i> Creating…');
    showBusy(true);

    try{
      const r = await fetch('/api/page-submenus', {
        method:'POST',
        headers,
        body: JSON.stringify(payload)
      });

      const ct = (r.headers.get('content-type')||'').toLowerCase();
      const json = ct.includes('application/json') ? await r.json() : { message: await r.text() };

      if (r.ok){
        const msg =
          json.message ||
          (json.already_existed ? 'Submenu already existed, not created again.' :
          json.restored ? 'Submenu restored.' :
          'Submenu created.');
        ok(msg);

        setTimeout(()=>{ location.href = afterCreateRedirect; }, 600);
      } else if (r.status === 422){
        const errors = json.errors || {};
        Object.entries(errors).forEach(([k,v])=> showError(k, Array.isArray(v)? v[0] : String(v)));
        err(json.message || 'Please fix the highlighted fields');
      } else if (r.status === 403){
        err('Forbidden');
      } else {
        console.error('Server error', json);
        err(`Server error (${r.status})`);
      }
    }catch(ex){
      console.error(ex);
      err('Network error');
    }finally{
      showBusy(false);
      setBtnBusy(els.btnCreate, false, '<i class="fa-solid fa-plus"></i> Create Submenu');
    }
  });

  els.btnCreate.addEventListener('click', createSubmenu);

  document.addEventListener('keydown', (e)=>{
    if (e.key === 'Enter' && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')){
      e.preventDefault();
      els.btnCreate.focus();
      return false;
    }
  });

  els.btnReset.addEventListener('click', ()=>{
    setBtnBusy(els.btnReset, true, '<i class="fa-regular fa-trash-can"></i> Resetting…');
    clearErrors();

    els.pageId.value='';
    els.belongsPageSlug.value='';

    els.title.value='';
    els.desc.value='';
    els.slug.value='';
    els.pageSlug.value='';
    els.pageShortcode.value='';
    els.pageUrl.value='';
    els.active.checked=true;
    setParent('', 'Self (Root)');

    setTimeout(()=> setBtnBusy(els.btnReset, false, '<i class="fa-regular fa-trash-can"></i> Reset'), 120);
  });

  (function init(){
    document.getElementById('hint').textContent = 'Page submenus are attached to a specific page and used to build page-level navigation.';
    els.slug.value = '';
    els.slugAuto.checked = true;
    els.slug.disabled = true;
    maybeUpdateSlug();

    // load pages list for dropdown
    loadPagesDropdown();
  })();
})();
</script>
@endpush
