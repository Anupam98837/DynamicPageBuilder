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
    transition:transform .12s ease, box-shadow .12s ease;
  }
  .tree-node:hover{transform:translateY(-1px);box-shadow:var(--shadow-1)}
  .tree-node.is-selected{
    border-color:color-mix(in oklab, var(--accent-color) 45%, var(--line-strong));
    box-shadow:0 0 0 3px rgba(201,75,80,.14);
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
        <strong id="pageTitle">Create Page Submenu</strong>
        <span class="hint text-muted" id="hint"></span>
      </div>
    </div>

    <div class="card-body">

      {{-- ===== BELONGS TO PAGE (NOW OPTIONAL + HIDDEN) ===== --}}
      <div class="section-label">Belongs To Page</div>
      <div class="row g-3">

        {{-- ✅ Page ID is now OPTIONAL + hidden from UI --}}
        <div class="col-12 col-md-4 d-none" id="pageIdWrap" style="display:none!important;">
          <label class="form-label">Page ID <span class="pill ms-1">optional</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-file-lines"></i></span>
            <select class="form-select" id="page_id">
              <option value="">Loading pages…</option>
            </select>
          </div>
          <small class="text-muted tiny">
            Optional. (Legacy) Not used for parent picking anymore.
          </small>
          <div class="err" data-for="page_id"></div>
        </div>

        {{-- ✅ Header Menu Parent dropdown → ✅ REPLACED with Button + Modal Tree (TREE API) --}}
        <div class="col-12 col-md-4">
          <label class="form-label">Header Menu (Parent) <span class="text-danger">*</span></label>

          <div class="d-flex flex-wrap align-items-center gap-2">
            <span id="headerMenuBadge" class="badge-soft">Not selected</span>

            <button class="btn btn-light pick-parent-btn" type="button" id="btnPickHeaderMenu">
              <i class="fa-solid fa-bars me-1"></i>Choose from tree
            </button>

            <button class="btn btn-outline-danger btn-sm" type="button" id="btnClearHeaderMenu">
              <i class="fa-solid fa-xmark me-1"></i>Clear
            </button>
          </div>

          {{-- ✅ hidden real value (same field name / id used in API payload) --}}
          <input type="hidden" id="header_menu_id" value="">

          <small class="text-muted tiny">
            Select the <b>header menu item</b> where this submenu will appear.
          </small>
          <div class="err" data-for="header_menu_id"></div>
        </div>

        {{-- ✅ Department dropdown --}}
        <div class="col-12 col-md-4">
          <label class="form-label">Department <span class="pill ms-1">optional</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-building-columns"></i></span>
            <select class="form-select" id="department_id">
              <option value="">Loading departments…</option>
            </select>
          </div>
          <small class="text-muted tiny" id="deptHint">
            Department is optional.
          </small>
          <div class="err" data-for="department_id"></div>
        </div>

        <div class="col-12">
          <label class="form-label">Page Slug <span class="pill ms-1">optional</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-link"></i></span>
            <input type="text" class="form-control" id="belongs_page_slug" maxlength="200" placeholder="(optional) just for reference">
          </div>
          <small class="text-muted tiny">
            Optional reference only.
          </small>
        </div>
      </div>

      <div class="divider-soft my-3"></div>

      {{-- ===== SUBMENU SECTION ===== --}}
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
          <small class="text-muted tiny" id="parentHint">
            Choose a parent from <b>all existing page submenus</b> and nest under it (optional).
          </small>
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

      {{-- ===== DESTINATION SECTION ===== --}}
      <div class="section-label">Destination</div>
      <div class="text-muted tiny mb-2">
        Note: Choose <b>only one</b> destination option. When you fill/select one, the others will be disabled automatically.
        Clear the selected option to enable the rest.
      </div>

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
        <div class="col-12 col-md-6">
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

        {{-- Includable Path --}}
        <div class="col-12 col-md-6">
          <label class="form-label">Includable Path <span class="pill ms-1">optional</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-puzzle-piece"></i></span>
            <select class="form-select" id="includable_path">
              <option value="">Loading modules…</option>
            </select>
          </div>
          <small class="text-muted tiny">
            Select an existing Blade view path (example: <code>modules.pageEditor.pageEditor</code>).
          </small>
          <div class="err" data-for="includable_path"></div>
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
            <span class="label" id="btnCreateLabel"><i class="fa-solid fa-plus"></i> Create Submenu</span>
            <span class="spinner-border d-none" role="status" aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- ✅ Header Menu Picker Modal (TREE API) --}}
  <div class="modal fade" id="headerMenuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title"><i class="fa-solid fa-bars me-2"></i>Pick Header Menu</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="d-flex justify-content-between align-items-center mb-2 modal-tools">
            <div class="d-flex align-items-center gap-2">
              <button class="btn btn-light btn-sm" type="button" id="btnReloadHeaderTree">
                <span class="label"><i class="fa-solid fa-rotate"></i> Reload</span>
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
              </button>
            </div>
            <div class="input-group" style="max-width: 340px;">
              <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
              <input type="text" class="form-control" id="headerTreeSearch" placeholder="Search by title…">
            </div>
          </div>

          <div class="tree-wrap">
            <div class="tree-loader" id="headerTreeLoader">
              <div class="spin me-2"></div><span class="text-muted">Loading tree…</span>
            </div>
            <div id="headerTreeEmpty" class="tree-empty" style="display:none">No header menus found.</div>
            <div id="headerTreeRoot" class="tree"></div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
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

    // ✅ header menu (hidden input now)
    headerMenuId: byId('header_menu_id'),
    headerMenuBadge: byId('headerMenuBadge'),
    btnPickHeaderMenu: byId('btnPickHeaderMenu'),
    btnClearHeaderMenu: byId('btnClearHeaderMenu'),
    headerMenuModal: new bootstrap.Modal(byId('headerMenuModal')),
    headerTreeRoot: byId('headerTreeRoot'),
    headerTreeSearch: byId('headerTreeSearch'),
    headerTreeLoader: byId('headerTreeLoader'),
    headerTreeEmpty: byId('headerTreeEmpty'),
    btnReloadHeaderTree: byId('btnReloadHeaderTree'),

    // department
    deptId: byId('department_id'),

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
    includablePath: byId('includable_path'),

    // parent
    parentId: byId('parent_id'),
    parentBadge: byId('parentBadge'),
    active: byId('active'),

    // actions
    btnCreate: byId('btnCreate'),
    btnCreateLabel: byId('btnCreateLabel'),
    btnReset: byId('btnReset'),

    // header text
    pageTitle: byId('pageTitle'),
    hint: byId('hint'),

    // submenu-parent modal + tree
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

  // ✅ update if your manage route differs
  const afterSaveRedirect = '/page/submenu/manage';

  /* ============================
     ✅ EDIT MODE
     URL example: /page-submenus/create?edit=12
  ============================ */
  const usp = new URLSearchParams(window.location.search || '');
  const EDIT_ID_RAW = (usp.get('edit') || '').trim();
  const IS_EDIT = EDIT_ID_RAW !== '' && !isNaN(Number(EDIT_ID_RAW));
  const EDIT_ID = IS_EDIT ? Number(EDIT_ID_RAW) : null;

  // legacy (hidden)
  const PAGE_ID_RAW = (usp.get('page_id') || usp.get('page') || usp.get('pid') || '').trim();
  const PAGE_ID_FROM_QUERY = (PAGE_ID_RAW !== '' && !isNaN(Number(PAGE_ID_RAW))) ? Number(PAGE_ID_RAW) : null;

  let initialData = null;

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
    if (!r.ok) throw new Error(j?.message || j?.error || ('HTTP '+r.status));
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
  function selectedPageSlug(){
    const opt = els.pageId?.options?.[els.pageId.selectedIndex];
    return (opt && opt.getAttribute('data-slug')) ? opt.getAttribute('data-slug') : '';
  }

  function ensurePageOption(id, title, slug){
    const existing = Array.from(els.pageId.options).find(o => String(o.value) === String(id));
    if (existing) return;

    const t = (title || '').toString().trim();
    const s = (slug || '').toString().trim();
    const label = (t ? t : (s ? ('/' + s) : 'Untitled page')) + (t && s ? ('  •  /' + s) : '');

    const opt = document.createElement('option');
    opt.value = String(id);
    opt.setAttribute('data-slug', s);
    opt.textContent = label;
    els.pageId.appendChild(opt);
  }

  function ensureDeptOption(id, title){
    const existing = Array.from(els.deptId.options).find(o => String(o.value) === String(id));
    if (existing) return;

    const opt = document.createElement('option');
    opt.value = String(id);
    opt.textContent = (title || ('Department #' + id));
    els.deptId.appendChild(opt);
  }

  function getDepartmentId(){
    const v = parseInt(String(els.deptId.value||'').trim(), 10);
    return Number.isFinite(v) && v > 0 ? v : 0;
  }

  function getHeaderMenuId(){
    const v = parseInt(String(els.headerMenuId.value||'').trim(), 10);
    return Number.isFinite(v) && v > 0 ? v : 0;
  }

  /* ============================
     ✅ Header Menu Picker (TREE API) — dropdown replaced
  ============================ */
  function setHeaderMenu(id, label){
    els.headerMenuId.value = id ? String(id) : '';
    els.headerMenuBadge.textContent = id ? `#${id}: ${label || '-'}` : 'Not selected';

    // changing header menu changes submenu parent scope -> reset parent submenu safely
    setParent('', 'Self (Root)');
    updateParentPickerAvailability();
  }

  els.btnClearHeaderMenu.addEventListener('click', ()=>{
    setHeaderMenu('', '');
    ok('Header menu cleared.');
  });

  function closeHeaderPicker(){
    try { els.headerMenuModal.hide(); } catch {}
    els.headerTreeLoader.classList.remove('show');
    setTimeout(()=>{
      document.querySelectorAll('.modal-backdrop').forEach(b=>b.remove());
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('padding-right');
    }, 50);
  }
  byId('headerMenuModal').addEventListener('hidden.bs.modal', closeHeaderPicker);

  function renderHeaderTree(nodes){
    els.headerTreeRoot.innerHTML = '';
    if (!nodes || !nodes.length){
      els.headerTreeEmpty.style.display = 'block';
      return;
    }
    els.headerTreeEmpty.style.display = 'none';

    const ul = document.createElement('ul');
    ul.className = 'm-0 p-0';

    function makeNode(n, depth=0){
      const li = document.createElement('li');

      const node = document.createElement('div');
      node.className = 'tree-node';
      node.dataset.open = (depth<=1 ? '1' : '0');

      if (els.headerMenuId.value && String(n.id) === String(els.headerMenuId.value)) {
        node.classList.add('is-selected');
      }

      const toggle = document.createElement('div');
      toggle.className = 'toggle';
      toggle.innerHTML = '<i class="fa-solid fa-chevron-right tiny"></i>';
      if (!n.children || !n.children.length) toggle.style.visibility = 'hidden';

      const title = document.createElement('div');
      title.className = 'tree-title';
      title.textContent = n.title || '-';

      const meta = document.createElement('div');
      meta.className = 'tree-meta';
      const slugText  = n.slug ? '/' + n.slug : '';
      const pageSlug  = n.page_slug ? ' • page: /' + n.page_slug : '';
      const pageUrl   = n.page_url ? ' • url: ' + n.page_url : '';
      const statusText = n.active ? ' • active' : ' • inactive';
      const deptText = (n.department_id === null || n.department_id === undefined) ? ' • dept: global' : (' • dept: #' + n.department_id);
      meta.textContent = slugText + pageSlug + pageUrl + statusText + deptText;

      const actions = document.createElement('div');
      actions.className = 'tree-actions';

      const pickBtn = document.createElement('button');
      pickBtn.type = 'button';
      pickBtn.className = 'btn btn-sm btn-outline-primary';
      pickBtn.innerHTML = '<span class="label"><i class="fa-regular fa-circle-check me-1"></i>Select</span><span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>';

      pickBtn.addEventListener('click', ()=>{
        setBtnBusy(pickBtn, true);
        setHeaderMenu(n.id, n.title || '-');
        setTimeout(()=>{ setBtnBusy(pickBtn, false); closeHeaderPicker(); }, 120);
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

      toggle.addEventListener('click', (e)=>{
        e.stopPropagation();
        const open = node.dataset.open === '1';
        node.dataset.open = open ? '0' : '1';
      });

      return li;
    }

    nodes.forEach(n => ul.appendChild(makeNode(n, 0)));
    els.headerTreeRoot.appendChild(ul);

    els.headerTreeSearch.value = '';
    els.headerTreeSearch.oninput = function(){
      const q = this.value.trim().toLowerCase();
      els.headerTreeRoot.querySelectorAll('.tree-node').forEach(nd=>{
        const t = (nd.querySelector('.tree-title')?.textContent || '').toLowerCase();
        const m = (nd.querySelector('.tree-meta')?.textContent || '').toLowerCase();
        const match = !q || t.includes(q) || m.includes(q);
        nd.parentElement.style.display = match ? '' : 'none';
      });

      if (q){
        els.headerTreeRoot.querySelectorAll('.tree-node').forEach(nd => nd.dataset.open = '1');
      }
    };
  }

  async function loadHeaderMenuTree(){
    els.headerTreeRoot.innerHTML = '';
    els.headerTreeEmpty.style.display='none';
    els.headerTreeLoader.classList.add('show');
    setBtnBusy(els.btnReloadHeaderTree, true);

    try{
      const j = await fetchJSON('/api/header-menus/tree?only_active=0');
      renderHeaderTree(Array.isArray(j.data) ? j.data : []);
    }catch(e){
      console.error(e);
      els.headerTreeEmpty.style.display='block';
      els.headerTreeRoot.innerHTML='';
    }finally{
      els.headerTreeLoader.classList.remove('show');
      setBtnBusy(els.btnReloadHeaderTree, false);
    }
  }

  els.btnPickHeaderMenu.addEventListener('click', ()=>{
    loadHeaderMenuTree();
    els.headerMenuModal.show();
  });
  els.btnReloadHeaderTree.addEventListener('click', loadHeaderMenuTree);

  /* ---------- FIX: Parent picker should be available only when header menu selected ---------- */
  function updateParentPickerAvailability(){
    const hm = getHeaderMenuId();
    const ok = hm > 0;

    els.btnPickParent.disabled = !ok;
    els.btnPickParent.title = ok
      ? 'Choose parent submenu'
      : 'Select Header Menu first to load available parents';
  }

  /* ---------- pages dropdown (legacy) ---------- */
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
  function extractPagesArray(j){
    if (Array.isArray(j?.data)) return j.data;
    if (Array.isArray(j?.pages)) return j.pages;
    if (Array.isArray(j?.items)) return j.items;
    if (Array.isArray(j?.data?.data)) return j.data.data;
    return [];
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

      const label = (title ? title : (slug ? ('/' + slug) : 'Untitled page')) + (title && slug ? ('  •  /' + slug) : '');

      const opt = document.createElement('option');
      opt.value = String(id);
      opt.setAttribute('data-slug', slug);
      opt.textContent = label;
      els.pageId.appendChild(opt);
    });

    els.pageId.disabled = false;
  }

  async function loadPagesDropdown(){
    setPagesDropdownStateLoading();

    const candidates = [
      '/api/page-submenus/pages?_ts=' + Date.now(),
      '/api/page-submenus/pages'
    ];

    for (const url of candidates){
      try{
        const j = await fetchJSON(url);
        const arr = extractPagesArray(j);
        if (Array.isArray(arr) && arr.length){
          setPagesDropdownOptions(arr);
          return;
        }
      }catch(e){}
    }

    setPagesDropdownStateError();
  }

  /* ---------- departments dropdown ---------- */
  function setDeptDropdownStateLoading(){
    els.deptId.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = 'Loading departments…';
    els.deptId.appendChild(opt);
    els.deptId.disabled = true;
  }
  function setDeptDropdownStateError(){
    els.deptId.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = 'Unable to load departments';
    els.deptId.appendChild(opt);
    els.deptId.disabled = false;
  }
  function extractDepartmentsArray(j){
    if (Array.isArray(j?.data)) return j.data;
    if (Array.isArray(j?.departments)) return j.departments;
    if (Array.isArray(j?.items)) return j.items;
    if (Array.isArray(j?.data?.data)) return j.data.data;
    return [];
  }
  function setDeptDropdownOptions(depts){
    els.deptId.innerHTML = '';

    const opt0 = document.createElement('option');
    opt0.value = '';
    opt0.textContent = 'Select a department…';
    els.deptId.appendChild(opt0);

    (depts||[]).forEach(d=>{
      const id = parseInt(d?.id ?? d?.department_id, 10);
      if (!Number.isFinite(id) || id <= 0) return;

      const title = (d?.title ?? d?.name ?? d?.department_title ?? '').toString().trim() || ('Department #' + id);

      const opt = document.createElement('option');
      opt.value = String(id);
      opt.textContent = title;
      els.deptId.appendChild(opt);
    });

    els.deptId.disabled = false;
  }

  async function loadDepartmentsDropdown(){
    setDeptDropdownStateLoading();

    const candidates = [
      '/api/departments?limit=2000&_ts=' + Date.now(),
      '/api/departments?per_page=2000&_ts=' + Date.now(),
      '/api/departments?_ts=' + Date.now(),
      '/api/public/departments?limit=2000&_ts=' + Date.now(),
      '/api/public/departments?_ts=' + Date.now(),
    ];

    for (const url of candidates){
      try{
        const j = await fetchJSON(url);
        const arr = extractDepartmentsArray(j);
        if (Array.isArray(arr) && arr.length){
          setDeptDropdownOptions(arr);
          return;
        }
      }catch(e){}
    }

    setDeptDropdownStateError();
  }

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

  /* ---------- destination single-select lock ---------- */
  const DEST_KEYS = ['pageUrl','includablePath','pageSlug','pageShortcode'];
  function getDestValues(){
    return {
      pageUrl: (els.pageUrl.value || '').trim(),
      includablePath: (els.includablePath.value || '').trim(),
      pageSlug: (els.pageSlug.value || '').trim(),
      pageShortcode: (els.pageShortcode.value || '').trim(),
    };
  }
  function pickActiveDestKey(preferKey){
    const v = getDestValues();
    if (preferKey && v[preferKey]) return preferKey;
    for (const k of DEST_KEYS){
      if (v[k]) return k;
    }
    return '';
  }
  function syncDestinationLocks(preferKey){
    const v = getDestValues();
    const activeKey = pickActiveDestKey(preferKey);

    els.pageUrl.disabled = false;
    els.includablePath.disabled = false;
    els.pageSlug.disabled = false;
    els.pageShortcode.disabled = false;

    if (!activeKey) return activeKey;

    if (activeKey !== 'pageUrl') els.pageUrl.disabled = true;
    if (activeKey !== 'includablePath') els.includablePath.disabled = true;
    if (activeKey !== 'pageSlug') els.pageSlug.disabled = true;
    if (activeKey !== 'pageShortcode') els.pageShortcode.disabled = true;

    return activeKey;
  }

  els.pageUrl.addEventListener('input', ()=> syncDestinationLocks('pageUrl'));
  els.pageSlug.addEventListener('input', ()=> syncDestinationLocks('pageSlug'));
  els.pageShortcode.addEventListener('input', ()=> syncDestinationLocks('pageShortcode'));
  els.includablePath.addEventListener('change', ()=> syncDestinationLocks('includablePath'));

  /* ---------- includable paths dropdown ---------- */
  const INCLUDABLE_PATHS_FALLBACK = [];

  function normalizeIncludableItem(x){
    if (typeof x === 'string') {
      const v = x.trim();
      return v ? { label: v, value: v } : null;
    }
    if (x && typeof x === 'object'){
      const value = String(x.value ?? x.path ?? x.view ?? '').trim();
      if (!value) return null;
      const label = String(x.label ?? value).trim();
      return { label, value };
    }
    return null;
  }

  function extractIncludablesArray(j){
    if (Array.isArray(j?.data)) return j.data;
    if (Array.isArray(j?.modules)) return j.modules;
    if (Array.isArray(j?.items)) return j.items;
    if (Array.isArray(j?.data?.data)) return j.data.data;
    return [];
  }

  function setIncludableOptions(list){
    const current = (els.includablePath.value || '').trim();
    els.includablePath.innerHTML = '';

    const opt0 = document.createElement('option');
    opt0.value = '';
    opt0.textContent = 'Select module…';
    els.includablePath.appendChild(opt0);

    (list || [])
      .map(normalizeIncludableItem)
      .filter(Boolean)
      .forEach(({label, value})=>{
        const opt = document.createElement('option');
        opt.value = value;
        opt.textContent = label;
        els.includablePath.appendChild(opt);
      });

    if (current){
      els.includablePath.value = current;
    }
  }

  async function loadIncludablePaths(){
    els.includablePath.disabled = true;
    els.includablePath.innerHTML = '<option value="">Loading modules…</option>';

    const candidates = [
      '/api/page-submenus/includables?_ts=' + Date.now(),
      '/api/page-submenus/includables'
    ];

    for (const url of candidates){
      try{
        const j = await fetchJSON(url);
        const arr = extractIncludablesArray(j);

        if (Array.isArray(arr) && arr.length){
          setIncludableOptions(arr);
          els.includablePath.disabled = false;
          return;
        }
      }catch(e){}
    }

    setIncludableOptions(INCLUDABLE_PATHS_FALLBACK);
    els.includablePath.disabled = false;

    if (!INCLUDABLE_PATHS_FALLBACK.length){
      err('Modules list API not found. Add paths in INCLUDABLE_PATHS_FALLBACK or create /api/page-submenus/includables.');
    }
  }

  /* ---------- parent selector (submenu parent tree modal) ---------- */
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

      toggle.addEventListener('click', (e)=>{
        e.stopPropagation();
        const open = node.dataset.open === '1';
        node.dataset.open = open ? '0' : '1';
      });

      return li;
    }

    nodes.forEach(n => ul.appendChild(makeNode(n, 0)));
    els.treeRoot.appendChild(ul);

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

  /* ✅ Parent picker shows ALL page submenus inside selected Header Menu scope */
  async function loadTree(){
    els.treeRoot.innerHTML = '';
    els.treeEmpty.style.display='none';
    els.treeLoader.classList.add('show');
    setBtnBusy(els.btnReloadTree, true);

    try{
      const headerMenuId = getHeaderMenuId();
      const pageId = getPageId(); // optional (hidden legacy)

      if (!headerMenuId){
        els.treeLoader.classList.remove('show');
        setBtnBusy(els.btnReloadTree, false);
        err('Select Header Menu first to load parent submenus');
        return;
      }

      // if pageId is empty -> show only page_id = NULL items (global for that header menu)
      const pageScope = (pageId > 0) ? String(pageId) : 'null';

      const url =
        `/api/page-submenus?per_page=2000&sort=position&direction=asc` +
        `&header_menu_id=${headerMenuId}` +
        `&page_id=${pageScope}` +
        `&_ts=${Date.now()}`;

      const j = await fetchJSON(url);
      let items = Array.isArray(j?.data) ? j.data : [];

      // in edit mode: prevent selecting self as parent
      if (IS_EDIT && EDIT_ID){
        items = items.filter(x => String(x.id) !== String(EDIT_ID));
      }

      // build tree from flat list
      const map = new Map();
      items.forEach(it=>{
        map.set(it.id, { ...it, children: [] });
      });

      const roots = [];
      map.forEach(node=>{
        const pid = node.parent_id ? Number(node.parent_id) : 0;
        if (pid > 0 && map.has(pid)){
          map.get(pid).children.push(node);
        } else {
          roots.push(node);
        }
      });

      // sort by position inside tree
      function sortTree(arr){
        arr.sort((a,b)=>(Number(a.position||0) - Number(b.position||0)) || (Number(a.id||0) - Number(b.id||0)));
        arr.forEach(n=>{
          if (Array.isArray(n.children)) sortTree(n.children);
        });
      }
      sortTree(roots);

      renderTree(roots);

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

    if (!getHeaderMenuId()){
      showError('header_menu_id', 'Select Header Menu first');
      els.btnPickHeaderMenu?.focus();
      return;
    }

    loadTree();
    els.parentModal.show();
  });

  els.btnReloadTree.addEventListener('click', loadTree);

  /* ---------- Create or Update ---------- */
  const saveSubmenu = oneFlight(async function(){
    clearErrors();

    // header menu required
    const headerMenuId = getHeaderMenuId();
    if (!headerMenuId){
      showError('header_menu_id','Header Menu (Parent) is required');
      els.btnPickHeaderMenu.focus();
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

    const activeDestKey = syncDestinationLocks();
    const v = getDestValues();

    const deptId = getDepartmentId();
    const parentIdSafe = els.parentId.value ? parseInt(els.parentId.value,10) : null;

    const payload = {
      // legacy optional
      page_id: (getPageId() > 0) ? getPageId() : null,

      header_menu_id: headerMenuId,
      department_id: deptId > 0 ? deptId : null,

      title: els.title.value.trim(),
      description: els.desc.value.trim() || null,
      slug: els.slug.value.trim() || undefined,
      parent_id: parentIdSafe,
      active: !!els.active.checked,

      page_slug:       (activeDestKey === 'pageSlug')       ? (v.pageSlug || null) : null,
      page_shortcode:  (activeDestKey === 'pageShortcode')  ? (v.pageShortcode || null) : null,
      page_url:        (activeDestKey === 'pageUrl')        ? (v.pageUrl || null) : null,
      includable_path: (activeDestKey === 'includablePath') ? (v.includablePath || null) : null
    };

    const url = IS_EDIT
      ? ('/api/page-submenus/' + encodeURIComponent(EDIT_ID))
      : '/api/page-submenus';

    const method = IS_EDIT ? 'PUT' : 'POST';

    setBtnBusy(els.btnCreate, true, IS_EDIT
      ? '<i class="fa-solid fa-floppy-disk"></i> Saving…'
      : '<i class="fa-solid fa-plus"></i> Creating…'
    );
    showBusy(true);

    try{
      const r = await fetch(url, { method, headers, body: JSON.stringify(payload) });

      const ct = (r.headers.get('content-type')||'').toLowerCase();
      const json = ct.includes('application/json') ? await r.json() : { message: await r.text() };

      if (r.ok){
        ok(json.message || (IS_EDIT ? 'Submenu updated.' : 'Submenu created.'));
        setTimeout(()=>{ location.href = afterSaveRedirect; }, 600);

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
      setBtnBusy(els.btnCreate, false, IS_EDIT
        ? '<i class="fa-solid fa-floppy-disk"></i> Save Changes'
        : '<i class="fa-solid fa-plus"></i> Create Submenu'
      );
    }
  });

  els.btnCreate.addEventListener('click', saveSubmenu);

  document.addEventListener('keydown', (e)=>{
    if (e.key === 'Enter' && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')){
      e.preventDefault();
      els.btnCreate.focus();
      return false;
    }
  });

  /* ============================
     ✅ Load selected submenu & populate (EDIT mode)
  ============================ */
  async function loadForEdit(){
    if (!IS_EDIT) return;

    els.pageTitle.textContent = 'Edit Page Submenu';
    els.btnCreateLabel.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes';
    els.hint.textContent = 'Editing selected submenu from Manage page.';

    els.slugAuto.checked = false;
    els.slug.disabled = false;
    els.btnRegen.disabled = false;

    showBusy(true);
    clearErrors();

    try{
      const j = await fetchJSON('/api/page-submenus/' + encodeURIComponent(EDIT_ID));
      const m = (j && typeof j === 'object' && 'data' in j) ? j.data : j;
      initialData = m || null;

      if (!m) throw new Error('No data found');

      if (m.page_id){
        ensurePageOption(m.page_id, m.page_title || m.page_name || '', m.belongs_page_slug || m.page_slug || '');
        els.pageId.value = String(m.page_id);
      }

      const guessBelongsSlug =
        m.belongs_page_slug ||
        m.belongs_slug ||
        m.page_parent_slug ||
        m.page_slug ||
        selectedPageSlug() ||
        '';
      if (guessBelongsSlug) els.belongsPageSlug.value = String(guessBelongsSlug);

      // ✅ set header menu (from edit data) - badge + hidden value
      const hm = parseInt(m.header_menu_id ?? m.headerMenuId ?? 0, 10);
      if (Number.isFinite(hm) && hm > 0){
        const label =
          (m.header_menu_title || m.header_title || m.headerMenuTitle || '').toString().trim()
          || ('Header Menu #' + hm);
        setHeaderMenu(hm, label);
      } else {
        setHeaderMenu('', '');
      }

      const subDept = parseInt(m.department_id ?? 0, 10);
      if (Number.isFinite(subDept) && subDept > 0){
        ensureDeptOption(subDept, m.department_title || '');
        els.deptId.value = String(subDept);
      }

      els.title.value = m.title || '';
      els.desc.value  = m.description || '';
      els.slug.value  = m.slug || '';

      els.pageSlug.value       = m.page_slug || '';
      els.pageShortcode.value  = m.page_shortcode || '';
      els.pageUrl.value        = m.page_url || '';
      els.includablePath.value = m.includable_path || '';

      els.active.checked = !!m.active;

      const parentLabel =
        m.parent_title ||
        (m.parent && m.parent.title) ||
        (m.parent_id ? ('#' + m.parent_id) : 'Self (Root)');

      setParent(m.parent_id || '', parentLabel);

      syncDestinationLocks();
      updateParentPickerAvailability();

    }catch(e){
      console.error(e);
      err(e.message || 'Failed to load submenu');
    }finally{
      showBusy(false);
    }
  }

  /* ---------- reset ---------- */
  els.btnReset.addEventListener('click', async ()=>{
    setBtnBusy(els.btnReset, true, '<i class="fa-regular fa-trash-can"></i> Resetting…');
    clearErrors();

    if (IS_EDIT && initialData){
      if (initialData.page_id){
        ensurePageOption(initialData.page_id, initialData.page_title || '', initialData.page_slug || '');
        els.pageId.value = String(initialData.page_id);
      } else {
        els.pageId.value = '';
      }

      els.belongsPageSlug.value = initialData.belongs_page_slug || initialData.page_slug || selectedPageSlug() || '';

      // ✅ header menu reset
      const hm = parseInt(initialData.header_menu_id ?? 0, 10);
      if (hm > 0){
        const label =
          (initialData.header_menu_title || initialData.header_title || '').toString().trim()
          || ('Header Menu #' + hm);
        setHeaderMenu(hm, label);
      } else {
        setHeaderMenu('', '');
      }

      const subDept = parseInt(initialData.department_id ?? 0, 10);
      els.deptId.value = subDept > 0 ? String(subDept) : '';

      els.title.value = initialData.title || '';
      els.desc.value  = initialData.description || '';
      els.slug.value  = initialData.slug || '';

      els.pageSlug.value       = initialData.page_slug || '';
      els.pageShortcode.value  = initialData.page_shortcode || '';
      els.pageUrl.value        = initialData.page_url || '';
      els.includablePath.value = initialData.includable_path || '';

      els.active.checked = !!initialData.active;

      const parentLabel =
        initialData.parent_title ||
        (initialData.parent && initialData.parent.title) ||
        (initialData.parent_id ? ('#' + initialData.parent_id) : 'Self (Root)');

      setParent(initialData.parent_id || '', parentLabel);

      els.slugAuto.checked = false;
      els.slug.disabled = false;
      els.btnRegen.disabled = false;

      syncDestinationLocks();
      updateParentPickerAvailability();

    } else {
      els.belongsPageSlug.value='';

      if (PAGE_ID_FROM_QUERY){
        ensurePageOption(PAGE_ID_FROM_QUERY, '', '');
        els.pageId.value = String(PAGE_ID_FROM_QUERY);
      } else {
        els.pageId.value='';
      }

      // ✅ clear header menu selection
      setHeaderMenu('', '');

      els.deptId.value='';

      els.title.value='';
      els.desc.value='';
      els.slug.value='';
      els.pageSlug.value='';
      els.pageShortcode.value='';
      els.pageUrl.value='';
      els.includablePath.value='';
      els.active.checked=true;
      setParent('', 'Self (Root)');

      els.slugAuto.checked = true;
      els.slug.disabled = true;
      els.btnRegen.disabled = true;
      maybeUpdateSlug();

      syncDestinationLocks();
      updateParentPickerAvailability();
    }

    setTimeout(()=> setBtnBusy(els.btnReset, false, '<i class="fa-regular fa-trash-can"></i> Reset'), 120);
  });

  /* ---------- init ---------- */
  (async function init(){
    els.hint.textContent = 'Header menu is selected from full tree (parents + children). Parent submenu picker is scoped under selected header menu.';

    els.slug.value = '';
    els.slugAuto.checked = true;
    els.slug.disabled = true;
    els.btnRegen.disabled = true;
    maybeUpdateSlug();

    // default header menu state
    setHeaderMenu('', '');

    await loadIncludablePaths();
    syncDestinationLocks();

    await loadDepartmentsDropdown();
    await loadPagesDropdown();

    if (!IS_EDIT && PAGE_ID_FROM_QUERY){
      ensurePageOption(PAGE_ID_FROM_QUERY, '', '');
      els.pageId.value = String(PAGE_ID_FROM_QUERY);

      if (!els.belongsPageSlug.value.trim()){
        const s = selectedPageSlug();
        if (s) els.belongsPageSlug.value = s;
      }
    }

    updateParentPickerAvailability();
    await loadForEdit();

  })();

})();
</script>
@endpush
