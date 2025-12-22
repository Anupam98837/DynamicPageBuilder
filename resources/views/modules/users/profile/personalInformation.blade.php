{{-- resources/views/modules/user/managePersonalInformation.blade.php --}}
@section('title','Personal Information')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Page shell (EduPro-ish)
 * ========================= */
.pi-wrap{max-width:1180px;margin:16px auto 40px;}
.pi-card{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.pi-card .card-header{
  background:transparent;
  border-bottom:1px solid var(--line-strong);
  padding:14px 16px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
}
.pi-title{display:flex;align-items:center;gap:10px;font-weight:700;color:var(--ink);}
.pi-sub{font-size:12.5px;color:var(--muted-color);margin-top:2px}
.pi-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}
.pi-card .card-body{padding:16px;overflow:visible}
.pi-card .card-footer{
  background:transparent;
  border-top:1px solid var(--line-strong);
  padding:14px 16px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  flex-wrap:wrap;
}

/* =========================
 * Inline loader overlay
 * ========================= */
.inline-loader{
  position:fixed; inset:0;
  background:rgba(0,0,0,0.45);
  display:none; justify-content:center; align-items:center;
  z-index:9999; backdrop-filter:blur(2px);
}
.inline-loader.show{display:flex}
.inline-loader .loader-card{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex; flex-direction:column; align-items:center; gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,0.3);
}
.inline-loader .spinner-border{ width:1.5rem;height:1.5rem; }

/* =========================
 * Qualifications (tags)
 * ========================= */
.tags-box{
  border:1px solid var(--line-strong);
  border-radius:14px;
  padding:10px 10px;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.tag-input-row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.tag-input{flex:1;min-width:240px;}
.tags{margin-top:10px;display:flex;flex-wrap:wrap;gap:8px;}
.tag{
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 10px;border-radius:999px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--primary-color) 10%, transparent);
  color:var(--ink);font-size:12.5px;
}
.tag .x{
  border:0;background:transparent;color:var(--muted-color);
  cursor:pointer;padding:0 2px;
}
.tag .x:hover{color:var(--danger-color)}
.rte-help{font-size:12px;color:var(--muted-color);margin-top:6px}

/* =========================
 * Custom Rich Text Editor
 * ========================= */
.rte-row{margin-bottom:16px;}            /* ✅ 1 editor = 1 row */
.rte-wrap{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:var(--surface);
}
.rte-toolbar{
  display:flex;gap:6px;align-items:center;flex-wrap:wrap;
  padding:8px 8px;border-bottom:1px solid var(--line-strong);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.rte-btn{
  border:1px solid var(--line-soft);
  background:transparent;
  color:var(--ink);
  padding:7px 9px;
  border-radius:10px;
  line-height:1;
  cursor:pointer;
  display:inline-flex; align-items:center; justify-content:center;
  gap:6px;
}
.rte-btn:hover{background:var(--page-hover)}
.rte-sep{width:1px;height:24px;background:var(--line-soft);margin:0 4px}
.rte-editor{
  min-height:140px;
  padding:12px 12px;
  outline:none;
}
.rte-editor:empty:before{content:attr(data-placeholder);color:var(--muted-color);}
.rte-editor h1{font-size:20px;margin:8px 0}
.rte-editor h2{font-size:18px;margin:8px 0}
.rte-editor h3{font-size:16px;margin:8px 0}
.rte-editor ul, .rte-editor ol{padding-left:22px}
.rte-editor p{margin:0 0 10px}

/* =========================
 * Form tweaks
 * ========================= */
.form-label{font-weight:600;color:var(--ink)}
.form-control{border-radius:12px}
.small-muted{font-size:12.5px;color:var(--muted-color)}
.badge-soft{
  background:color-mix(in oklab, var(--muted-color) 12%, transparent);
  color:var(--muted-color);
  border:1px solid var(--line-soft);
  border-radius:999px;
  padding:4px 10px;
  font-size:12px;
}
.btn-loading{position:relative;color:transparent !important}
.btn-loading::after{
  content:'';
  position:absolute;
  width:16px;height:16px;
  top:50%;left:50%;
  margin:-8px 0 0 -8px;
  border:2px solid transparent;
  border-top:2px solid currentColor;
  border-radius:50%;
  animation:spin 1s linear infinite
}
@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}
</style>
@endpush

@section('content')
<div class="pi-wrap">

  <div id="inlineLoader" class="inline-loader">
    <div class="loader-card">
      <div class="spinner-border" role="status" aria-hidden="true"></div>
      <p class="small text-muted mb-0">Loading…</p>
    </div>
  </div>

  <div class="card pi-card">
    <div class="card-header">
      <div>
        <div class="pi-title">
          <i class="fa fa-id-card-clip"></i>
          <div>
            <div>Personal Information</div>
            <div class="pi-sub" id="modeText">Loading…</div>
          </div>
        </div>
      </div>

      <div class="pi-actions">
        <span class="badge-soft" id="statusBadge">—</span>
        <button type="button" class="btn btn-light" id="btnReset">
          <i class="fa fa-rotate-left me-1"></i> Reset
        </button>
      </div>
    </div>

    <div class="card-body">
      <form id="piForm" autocomplete="off">

        {{-- Qualifications (tags) --}}
        <div class="mb-3">
          <label class="form-label">Qualification (Tags)</label>
          <div class="tags-box">
            <div class="tag-input-row">
              <input id="qualInput" class="form-control tag-input" placeholder="Type qualification and press Enter (e.g., B.Tech, M.Tech, PhD)">
              <button type="button" class="btn btn-outline-primary" id="btnAddQual">
                <i class="fa fa-plus me-1"></i> Add
              </button>
            </div>
            <div class="tags" id="qualTags"></div>
            <div class="rte-help">Tip: Press <b>Enter</b> to add. Click × to remove.</div>
          </div>
        </div>

        {{-- ✅ 1 editor = 1 row (FULL WIDTH) --}}
        <div class="rte-row">
          <label class="form-label">Affiliation</label>
          <div class="rte-wrap">
            <div class="rte-toolbar" data-for="affiliation">
              <button type="button" class="rte-btn" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
              <button type="button" class="rte-btn" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
              <button type="button" class="rte-btn" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
              <button type="button" class="rte-btn" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-h="h1" title="Heading 1">H1</button>
              <button type="button" class="rte-btn" data-h="h2" title="Heading 2">H2</button>
              <button type="button" class="rte-btn" data-h="h3" title="Heading 3">H3</button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>
            </div>
            <div id="affiliationEditor" class="rte-editor" contenteditable="true" data-placeholder="Write affiliation…"></div>
          </div>
        </div>

        <div class="rte-row">
          <label class="form-label">Specification</label>
          <div class="rte-wrap">
            <div class="rte-toolbar" data-for="specification">
              <button type="button" class="rte-btn" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
              <button type="button" class="rte-btn" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
              <button type="button" class="rte-btn" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
              <button type="button" class="rte-btn" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-h="h1" title="Heading 1">H1</button>
              <button type="button" class="rte-btn" data-h="h2" title="Heading 2">H2</button>
              <button type="button" class="rte-btn" data-h="h3" title="Heading 3">H3</button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>
            </div>
            <div id="specificationEditor" class="rte-editor" contenteditable="true" data-placeholder="Write specification…"></div>
          </div>
        </div>

        <div class="rte-row">
          <label class="form-label">Experience</label>
          <div class="rte-wrap">
            <div class="rte-toolbar" data-for="experience">
              <button type="button" class="rte-btn" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
              <button type="button" class="rte-btn" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
              <button type="button" class="rte-btn" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
              <button type="button" class="rte-btn" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-h="h1" title="Heading 1">H1</button>
              <button type="button" class="rte-btn" data-h="h2" title="Heading 2">H2</button>
              <button type="button" class="rte-btn" data-h="h3" title="Heading 3">H3</button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>
            </div>
            <div id="experienceEditor" class="rte-editor" contenteditable="true" data-placeholder="Write experience…"></div>
          </div>
        </div>

        <div class="rte-row">
          <label class="form-label">Interest</label>
          <div class="rte-wrap">
            <div class="rte-toolbar" data-for="interest">
              <button type="button" class="rte-btn" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
              <button type="button" class="rte-btn" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
              <button type="button" class="rte-btn" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
              <button type="button" class="rte-btn" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-h="h1" title="Heading 1">H1</button>
              <button type="button" class="rte-btn" data-h="h2" title="Heading 2">H2</button>
              <button type="button" class="rte-btn" data-h="h3" title="Heading 3">H3</button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>
            </div>
            <div id="interestEditor" class="rte-editor" contenteditable="true" data-placeholder="Write interest…"></div>
          </div>
        </div>

        <div class="rte-row">
          <label class="form-label">Administration</label>
          <div class="rte-wrap">
            <div class="rte-toolbar" data-for="administration">
              <button type="button" class="rte-btn" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
              <button type="button" class="rte-btn" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
              <button type="button" class="rte-btn" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
              <button type="button" class="rte-btn" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-h="h1" title="Heading 1">H1</button>
              <button type="button" class="rte-btn" data-h="h2" title="Heading 2">H2</button>
              <button type="button" class="rte-btn" data-h="h3" title="Heading 3">H3</button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>
            </div>
            <div id="administrationEditor" class="rte-editor" contenteditable="true" data-placeholder="Write administration…"></div>
          </div>
        </div>

        <div class="rte-row">
          <label class="form-label">Research Project</label>
          <div class="rte-wrap">
            <div class="rte-toolbar" data-for="research_project">
              <button type="button" class="rte-btn" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
              <button type="button" class="rte-btn" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
              <button type="button" class="rte-btn" data-cmd="underline" title="Underline"><i class="fa fa-underline"></i></button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-cmd="insertUnorderedList" title="Bullets"><i class="fa fa-list-ul"></i></button>
              <button type="button" class="rte-btn" data-cmd="insertOrderedList" title="Numbering"><i class="fa fa-list-ol"></i></button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-h="h1" title="Heading 1">H1</button>
              <button type="button" class="rte-btn" data-h="h2" title="Heading 2">H2</button>
              <button type="button" class="rte-btn" data-h="h3" title="Heading 3">H3</button>
              <span class="rte-sep"></span>
              <button type="button" class="rte-btn" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>
            </div>
            <div id="research_projectEditor" class="rte-editor" contenteditable="true" data-placeholder="Write research project…"></div>
          </div>
        </div>

        {{-- Hidden fields (HTML payload sent to API) --}}
        <input type="hidden" id="affiliation" name="affiliation">
        <input type="hidden" id="specification" name="specification">
        <input type="hidden" id="experience" name="experience">
        <input type="hidden" id="interest" name="interest">
        <input type="hidden" id="administration" name="administration">
        <input type="hidden" id="research_project" name="research_project">
      </form>
    </div>

    <div class="card-footer">
      <div class="small-muted">
        <span class="me-2"><i class="fa fa-circle-info me-1"></i> First time: <b>Save</b> creates. Next time: <b>Save</b> updates (no new entry).</span>
      </div>

      <div class="d-flex gap-8" style="display:flex;gap:10px;flex-wrap:wrap;">
        <button type="button" class="btn btn-outline-danger" id="btnDelete">
          <i class="fa fa-trash me-1"></i> Delete
        </button>
        <button type="submit" class="btn btn-primary" id="btnSave" form="piForm">
          <i class="fa fa-floppy-disk me-1"></i> Save
        </button>
      </div>
    </div>
  </div>

  {{-- Toasts --}}
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080">
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

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
  if (!token) { window.location.href = '/'; return; }

  const inlineLoader = document.getElementById('inlineLoader');
  const showInlineLoading = (show)=> inlineLoader?.classList.toggle('show', !!show);
  const authHeaders = (extra={}) => Object.assign({ 'Authorization': 'Bearer ' + token }, extra);

  const toastOk = new bootstrap.Toast(document.getElementById('toastSuccess'));
  const toastErr = new bootstrap.Toast(document.getElementById('toastError'));
  const okTxt = document.getElementById('toastSuccessText');
  const errTxt = document.getElementById('toastErrorText');
  const ok = (m)=>{ okTxt.textContent = m || 'Done'; toastOk.show(); };
  const err = (m)=>{ errTxt.textContent = m || 'Something went wrong'; toastErr.show(); };

  const modeText = document.getElementById('modeText');
  const statusBadge = document.getElementById('statusBadge');

  const btnSave = document.getElementById('btnSave');
  const btnDelete = document.getElementById('btnDelete');
  const btnReset = document.getElementById('btnReset');

  const form = document.getElementById('piForm');

  // Qualifications tags
  const qualInput = document.getElementById('qualInput');
  const btnAddQual = document.getElementById('btnAddQual');
  const qualTags = document.getElementById('qualTags');

  // Editors
  const editors = {
    affiliation: document.getElementById('affiliationEditor'),
    specification: document.getElementById('specificationEditor'),
    experience: document.getElementById('experienceEditor'),
    interest: document.getElementById('interestEditor'),
    administration: document.getElementById('administrationEditor'),
    research_project: document.getElementById('research_projectEditor'),
  };

  // Hidden fields (html)
  const hidden = {
    affiliation: document.getElementById('affiliation'),
    specification: document.getElementById('specification'),
    experience: document.getElementById('experience'),
    interest: document.getElementById('interest'),
    administration: document.getElementById('administration'),
    research_project: document.getElementById('research_project'),
  };

  function setButtonLoading(button, loading){
    if(!button) return;
    button.disabled = !!loading;
    button.classList.toggle('btn-loading', !!loading);
  }

  // --- state ---
  let currentUser = { id:null, uuid:'', role:'' };
  let hasRow = false;
  let lastServerData = null;

  const state = { qualification: [] };

  function sanitizeTag(s){ return (s ?? '').toString().replace(/\s+/g,' ').trim(); }
  function uniqLower(arr){
    const seen = new Set();
    const out = [];
    for(const x of arr){
      const key = (x||'').toLowerCase();
      if(!key || seen.has(key)) continue;
      seen.add(key);
      out.push(x);
    }
    return out;
  }

  function escapeHtml(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
  }

  function renderTags(){
    qualTags.innerHTML = '';
    if(!state.qualification.length){
      qualTags.innerHTML = '<span class="small-muted">No qualifications added.</span>';
      return;
    }
    state.qualification.forEach((t, idx)=>{
      const span = document.createElement('span');
      span.className = 'tag';
      span.innerHTML = `
        <span>${escapeHtml(t)}</span>
        <button type="button" class="x" title="Remove" data-idx="${idx}"><i class="fa fa-xmark"></i></button>
      `;
      qualTags.appendChild(span);
    });
  }

  function addTag(raw){
    const t = sanitizeTag(raw);
    if(!t) return;
    state.qualification = uniqLower([...state.qualification, t]);
    renderTags();
    qualInput.value = '';
    qualInput.focus();
  }

  qualTags.addEventListener('click', (e)=>{
    const btn = e.target.closest('button.x[data-idx]');
    if(!btn) return;
    const idx = parseInt(btn.dataset.idx, 10);
    if(Number.isNaN(idx)) return;
    state.qualification.splice(idx, 1);
    renderTags();
  });

  btnAddQual.addEventListener('click', ()=> addTag(qualInput.value));
  qualInput.addEventListener('keydown', (e)=>{
    if(e.key === 'Enter'){
      e.preventDefault();
      addTag(qualInput.value);
    }
    if(e.key === 'Backspace' && !qualInput.value && state.qualification.length){
      state.qualification.pop();
      renderTags();
    }
  });

  // =========================
  // RTE actions (execCommand)
  // =========================
  let activeEditor = null;

  Object.values(editors).forEach(ed=>{
    ed.addEventListener('focus', ()=>{ activeEditor = ed; });
    ed.addEventListener('click', ()=>{ activeEditor = ed; });
    ed.addEventListener('keyup', ()=>{ activeEditor = ed; });
  });

  function wrapSelectionAsHeading(tag){
    if(!activeEditor) return;
    activeEditor.focus();
    const sel = window.getSelection();
    if(!sel || sel.rangeCount === 0) return;

    const range = sel.getRangeAt(0);
    if(!activeEditor.contains(range.commonAncestorContainer)) return;

    const txt = sel.toString();
    if(!txt.trim()){
      document.execCommand('insertHTML', false, `<${tag}>Heading</${tag}><p></p>`);
      return;
    }
    document.execCommand('formatBlock', false, tag.toUpperCase());
  }

  document.querySelectorAll('.rte-toolbar').forEach(tb=>{
    tb.addEventListener('mousedown', (e)=>{ e.preventDefault(); });

    tb.addEventListener('click', (e)=>{
      const btn = e.target.closest('button.rte-btn');
      if(!btn) return;

      const cmd = btn.getAttribute('data-cmd');
      const h = btn.getAttribute('data-h');

      const key = tb.getAttribute('data-for');
      if(key && editors[key]) activeEditor = editors[key];

      if(!activeEditor) return;
      activeEditor.focus();

      if(h){ wrapSelectionAsHeading(h); return; }

      if(cmd){
        try{ document.execCommand(cmd, false, null); }
        catch(ex){ console.error('execCommand failed', cmd, ex); }
      }
    });
  });

  // =========================
  // API helpers
  // =========================
  async function fetchMe(){
    const res = await fetch('/api/users/me', { headers: authHeaders() });
    const js = await res.json().catch(()=>({}));
    if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load current user');
    if(!js.data || !js.data.uuid) throw new Error('Current user UUID missing from /api/users/me');
    currentUser = { id: js.data.id || null, uuid: js.data.uuid, role: (js.data.role||'').toLowerCase() };
  }

  async function fetchPersonalInfo(){
    const res = await fetch(`/api/users/${encodeURIComponent(currentUser.uuid)}/personal-info`, { headers: authHeaders() });
    const js = await res.json().catch(()=>({}));
    if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Failed to load personal info');
    return js.data || null;
  }

  async function createPersonalInfo(payload){
    const res = await fetch(`/api/users/${encodeURIComponent(currentUser.uuid)}/personal-info`, {
      method: 'POST',
      headers: authHeaders({ 'Content-Type':'application/json' }),
      body: JSON.stringify(payload)
    });
    const js = await res.json().catch(()=>({}));
    if(!res.ok || js.success === false) {
      const msg = js.error || js.message || 'Create failed';
      const e = new Error(msg);
      e.status = res.status;
      throw e;
    }
    return js.data || null;
  }

  async function updatePersonalInfo(payload){
    const res = await fetch(`/api/users/${encodeURIComponent(currentUser.uuid)}/personal-info`, {
      method: 'PUT',
      headers: authHeaders({ 'Content-Type':'application/json' }),
      body: JSON.stringify(payload)
    });
    const js = await res.json().catch(()=>({}));
    if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Update failed');
    return js.data || null;
  }

  async function deletePersonalInfo(){
    const res = await fetch(`/api/users/${encodeURIComponent(currentUser.uuid)}/personal-info`, {
      method:'DELETE',
      headers: authHeaders()
    });
    const js = await res.json().catch(()=>({}));
    if(!res.ok || js.success === false) throw new Error(js.error || js.message || 'Delete failed');
    return true;
  }

  // =========================
  // Fill / Reset
  // =========================
  function htmlOrEmpty(v){
    const s = (v ?? '').toString().trim();
    return s ? s : '';
  }

  function setEditorHtml(key, html){
    editors[key].innerHTML = htmlOrEmpty(html);
  }

  function collectPayload(){
    Object.keys(editors).forEach(k=>{
      hidden[k].value = (editors[k].innerHTML || '').trim();
    });

    const payload = {
      qualification: state.qualification.slice(),
      affiliation: hidden.affiliation.value || null,
      specification: hidden.specification.value || null,
      experience: hidden.experience.value || null,
      interest: hidden.interest.value || null,
      administration: hidden.administration.value || null,
      research_project: hidden.research_project.value || null,
    };

    Object.keys(payload).forEach(k=>{
      if(typeof payload[k] === 'string'){
        const t = payload[k].replace(/<br\s*\/?>/gi,'').replace(/&nbsp;/gi,' ').trim();
        if(!t) payload[k] = null;
      }
    });

    return payload;
  }

  function applyServerData(d){
    lastServerData = d;

    hasRow = !!(d && d.id);

    // ✅ IMPORTANT FIX: support both keys + string JSON
    let q = d?.qualification ?? d?.qualifications ?? [];
    if (typeof q === 'string') {
      try { q = JSON.parse(q); } catch(e){ q = []; }
    }
    state.qualification = Array.isArray(q) ? q.filter(Boolean).map(sanitizeTag) : [];
    state.qualification = uniqLower(state.qualification);
    renderTags();

    setEditorHtml('affiliation', d?.affiliation);
    setEditorHtml('specification', d?.specification);
    setEditorHtml('experience', d?.experience);
    setEditorHtml('interest', d?.interest);
    setEditorHtml('administration', d?.administration);
    setEditorHtml('research_project', d?.research_project);

    if(hasRow){
      modeText.textContent = 'Edit mode: data already exists for this user.';
      statusBadge.textContent = 'EDIT';
      btnDelete.disabled = false;
    }else{
      modeText.textContent = 'Create mode: first save will create the entry.';
      statusBadge.textContent = 'NEW';
      btnDelete.disabled = true;
    }
  }

  function resetToLast(){
    if(lastServerData) applyServerData(lastServerData);
  }

  btnReset.addEventListener('click', ()=>{
    resetToLast();
    ok('Reset to last saved data');
  });

  // =========================
  // Save / Delete
  // =========================
  form.addEventListener('submit', async (e)=>{
    e.preventDefault();

    const payload = collectPayload();
    if(!Array.isArray(payload.qualification)) payload.qualification = [];

    setButtonLoading(btnSave, true);
    showInlineLoading(true);
    try{
      let saved = null;

      if(!hasRow){
        try{
          saved = await createPersonalInfo(payload);
          ok('Personal information created');
        }catch(ex){
          // fallback to update if API returns 409
          if(ex && ex.status === 409){
            saved = await updatePersonalInfo(payload);
            ok('Personal information updated');
          }else{
            throw ex;
          }
        }
      }else{
        saved = await updatePersonalInfo(payload);
        ok('Personal information updated');
      }

      applyServerData(saved || payload);
    }catch(ex){
      err(ex.message || 'Save failed');
      console.error(ex);
    }finally{
      setButtonLoading(btnSave, false);
      showInlineLoading(false);
    }
  });

  btnDelete.addEventListener('click', async ()=>{
    if(!hasRow){
      err('Nothing to delete yet');
      return;
    }

    const conf = await Swal.fire({
      title: 'Delete personal information?',
      text: 'This will soft delete the record.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Delete',
      confirmButtonColor: '#ef4444'
    });
    if(!conf.isConfirmed) return;

    setButtonLoading(btnDelete, true);
    showInlineLoading(true);
    try{
      await deletePersonalInfo();
      ok('Deleted');

      const fresh = await fetchPersonalInfo();
      applyServerData(fresh);
    }catch(ex){
      err(ex.message || 'Delete failed');
      console.error(ex);
    }finally{
      setButtonLoading(btnDelete, false);
      showInlineLoading(false);
    }
  });

  // =========================
  // Init
  // =========================
  (async ()=>{
    showInlineLoading(true);
    try{
      await fetchMe();
      const data = await fetchPersonalInfo();
      applyServerData(data);
    }catch(ex){
      err(ex.message || 'Failed to load');
      console.error(ex);
    }finally{
      showInlineLoading(false);
    }
  })();
});
</script>
@endpush
