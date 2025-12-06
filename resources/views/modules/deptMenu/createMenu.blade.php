{{-- resources/views/modules/departments/createMenu.blade.php --}}
@section('title','Create Department Menu')

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
<div id="dmRoot" class="sm-wrap">
  <div class="sm card shadow-2" style="position:relative">
    <div class="dim" id="busy"><div class="spin" aria-label="Working…"></div></div>

    <div class="card-header">
      <div class="sm-head">
        <i class="fa-solid fa-sitemap"></i>
        <strong>Create Department Menu</strong>
        <span class="hint text-muted" id="hint"></span>
      </div>
    </div>

    <div class="card-body">
      <div class="row g-3">

        {{-- Department --}}
        <div class="col-12 col-md-6">
          <label class="form-label">Department <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-building-columns"></i></span>
            <select class="form-select" id="departmentKey">
              <option value="">— Select department —</option>
            </select>
          </div>
          <div class="err" data-for="department"></div>
        </div>

        {{-- Title --}}
        <div class="col-12 col-md-6">
          <label class="form-label">Menu Title <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-heading"></i></span>
            <input type="text" class="form-control" id="title" maxlength="150" placeholder="e.g., Computer Science">
          </div>
          <div class="err" data-for="title"></div>
        </div>

        {{-- Description --}}
        <div class="col-12">
          <label class="form-label">Description <span class="pill ms-1">optional</span></label>
          <textarea class="form-control" id="description" rows="3" placeholder="Short internal note (optional)"></textarea>
          <div class="err" data-for="description"></div>
        </div>

        {{-- Slug + Auto --}}
        <div class="col-12 col-md-6">
          <div class="switch-inline mb-1">
            <label class="form-label mb-0">Slug (URL path)</label>
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
          <div class="err" data-for="slug"></div>
        </div>

        {{-- Code --}}
        <div class="col-12 col-md-6">
          <label class="form-label">Code <span class="pill ms-1">optional</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-barcode"></i></span>
            <input type="text" class="form-control" id="code" maxlength="24" placeholder="Leave empty to auto-generate">
          </div>
          <div class="err" data-for="code"></div>
        </div>

        {{-- Parent picker --}}
        <div class="col-12 col-md-8">
          <label class="form-label">Parent</label>
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
            <div class="col-12 switch-inline">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="is_default">
              </div>
              <label class="form-check-label" for="is_default">Default among siblings</label>
            </div>
          </div>
        </div>
      </div>

      <div class="divider-soft"></div>

      <div class="d-flex justify-content-between">
        <a href="javascript:history.back()" class="btn btn-light"><i class="fa-solid fa-arrow-left-long"></i> Back</a>
        <div class="btn-group">
          <button class="btn btn-secondary" type="button" id="btnReset">
            <span class="label"><i class="fa-regular fa-trash-can"></i> Reset</span>
            <span class="spinner-border d-none" role="status" aria-hidden="true"></span>
          </button>
          <button class="btn btn-primary" type="button" id="btnCreate">
            <span class="label"><i class="fa-solid fa-plus"></i> Create Menu</span>
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
          <h6 class="modal-title"><i class="fa-solid fa-diagram-project me-2"></i>Pick Parent</h6>
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
            <div id="treeEmpty" class="tree-empty" style="display:none">No menus found.</div>
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
    dept: byId('departmentKey'),
    title: byId('title'),
    desc: byId('description'),
    slug: byId('slug'),
    slugAuto: byId('slugAuto'),
    btnRegen: byId('btnRegen'),
    code: byId('code'),
    parentId: byId('parent_id'),
    parentBadge: byId('parentBadge'),
    active: byId('active'),
    isDefault: byId('is_default'),
    btnCreate: byId('btnCreate'),
    btnReset: byId('btnReset'),
    // modal
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

  /* ---------- utilities ---------- */
  function showBusy(on){ busy.classList.toggle('show', !!on); }
  function showError(field, msg){
    const el = document.querySelector(`.err[data-for="${field}"]`);
    if (!el) return;
    el.textContent = msg || '';
    el.style.display = msg ? 'block' : 'none';
  }
  function clearErrors(){ document.querySelectorAll('.err').forEach(e=>{ e.textContent=''; e.style.display='none'; }); }
  function esc(s=''){ return String(s).replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }
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

  // button lock/spinner helpers
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

  /* ---------- departments list ---------- */
  async function loadDepartments(){
    let rows = [];
    try {
      const j = await fetchJSON('/api/departments?status=active&sort=asc');
      rows = Array.isArray(j.data) ? j.data : (Array.isArray(j) ? j : []);
    } catch {
      try {
        const j2 = await fetchJSON('/api/departments/all?status=active&sort=asc');
        rows = Array.isArray(j2.data) ? j2.data : (Array.isArray(j2) ? j2 : []);
      } catch { /* ignore */ }
    }
    els.dept.innerHTML = '<option value="">— Select department —</option>' +
      rows.map(d=>{
        const key = d.uuid || d.id;
        const name = d.name || d.title || ('Dept #'+(d.id||''));
        return `<option value="${esc(String(key))}">${esc(name)}</option>`;
      }).join('');

    // Preselect via ?department / ?dept
    const qs = new URL(location.href).searchParams;
    const pre = (qs.get('department') || qs.get('dept') || '').trim();
    if (pre) {
      const opt = Array.from(els.dept.options).find(o => o.value===pre || o.textContent.trim().toLowerCase()===pre.toLowerCase());
      if (opt) opt.selected = true;
    }
  }

  /* ---------- slug auto ---------- */
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

  /* ---------- parent selector (modern tree) ---------- */
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

  // build a clean nested list with toggles and "Use" buttons
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
      node.dataset.open = (depth<=1 ? '1' : '0'); // expand first two levels by default

      const toggle = document.createElement('div');
      toggle.className = 'toggle';
      toggle.innerHTML = '<i class="fa-solid fa-chevron-right tiny"></i>';
      if (!n.children || !n.children.length) toggle.style.visibility = 'hidden';

      const title = document.createElement('div');
      title.className = 'tree-title';
      title.textContent = n.title || '-';

      const meta = document.createElement('div');
      meta.className = 'tree-meta';
      meta.textContent = ` /${n.slug || ''}` + (n.is_default ? ' • default' : '') + (n.active ? ' • active' : ' • inactive');

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
      // reveal ancestors of matches
      els.treeRoot.querySelectorAll('.tree-node').forEach(nd=>{
        const title = (nd.querySelector('.tree-title')?.textContent || '').toLowerCase();
        const meta  = (nd.querySelector('.tree-meta')?.textContent || '').toLowerCase();
        const match = !q || title.includes(q) || meta.includes(q);
        nd.parentElement.style.display = match ? '' : 'none';
      });

      if (q){
        // open all to show matches in context
        els.treeRoot.querySelectorAll('.tree-node').forEach(nd => nd.dataset.open = '1');
      }
    };
  }

  async function loadTree(){
    els.treeRoot.innerHTML = '';
    els.treeEmpty.style.display='none';
    els.treeLoader.classList.add('show');
    setBtnBusy(els.btnReloadTree, true);
    try{
      const deptKey = els.dept.value.trim();
      if (!deptKey) throw new Error('Select a department first');
      const j = await fetchJSON(`/api/departments/${encodeURIComponent(deptKey)}/menus/tree?only_active=0`);
      renderTree(Array.isArray(j.data) ? j.data : []);
    }catch(e){
      els.treeEmpty.style.display='block';
      els.treeRoot.innerHTML='';
    }finally{
      els.treeLoader.classList.remove('show');
      setBtnBusy(els.btnReloadTree, false);
    }
  }

  els.btnPickParent.addEventListener('click', ()=>{
    if (!els.dept.value.trim()){ err('Select a department first'); els.dept.focus(); return; }
    loadTree();
    els.parentModal.show();
  });
  els.btnReloadTree.addEventListener('click', loadTree);

  /* ---------- Create (one-click, idempotent via client nonce) ---------- */
  function makeNonce(){
    // 18-char: UI + timestamp + 5 random base36 to stay under 24
    return 'UI' + Date.now().toString(36).toUpperCase() + Math.random().toString(36).slice(2,7).toUpperCase();
  }

  const createMenu = oneFlight(async function(){
    clearErrors();

    const dept = els.dept.value.trim();
    if (!dept){ showError('department','Department is required'); els.dept.focus(); return; }
    if (!els.title.value.trim()){ showError('title','Title is required'); els.title.focus(); return; }

    // use provided code OR a nonce to prevent accidental duplicate rows on double-fire
    const codeToSend = (els.code.value.trim() || makeNonce()).slice(0,24);

    const payload = {
      title: els.title.value.trim(),
      description: els.desc.value.trim() || null,
      slug: els.slug.value.trim() || undefined,
      code: codeToSend,
      parent_id: els.parentId.value ? parseInt(els.parentId.value,10) : null,
      is_default: !!els.isDefault.checked,
      active: !!els.active.checked
    };

    setBtnBusy(els.btnCreate, true, '<i class="fa-solid fa-plus"></i> Creating…');
    showBusy(true);
    try{
      const r = await fetch(`/api/departments/${encodeURIComponent(dept)}/menus`, {
        method:'POST', headers, body: JSON.stringify(payload)
      });
      const ct = (r.headers.get('content-type')||'').toLowerCase();
      const json = ct.includes('application/json') ? await r.json() : { message: await r.text() };

      if (r.ok){
        ok('Menu created');
        setTimeout(()=>{ location.href = '/department/menu/manage'; }, 600);
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
      setBtnBusy(els.btnCreate, false, '<i class="fa-solid fa-plus"></i> Create Menu');
    }
  });

  els.btnCreate.addEventListener('click', createMenu);

  // Disable Enter spam
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
    els.title.value=''; els.desc.value='';
    els.slug.value=''; els.code.value='';
    els.active.checked=true; els.isDefault.checked=false;
    setParent('', 'Self (Root)');
    setTimeout(()=> setBtnBusy(els.btnReset, false, '<i class="fa-regular fa-trash-can"></i> Reset'), 120);
  });

  // init
  (async function init(){
    document.getElementById('hint').textContent = '';
    els.slug.value = '';
    els.slugAuto.checked = true;
    els.slug.disabled = true;
    maybeUpdateSlug();
    await loadDepartments();
  })();
})();
</script>
@endpush
