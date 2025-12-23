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
 * Course-module-like Dual Editor (Text / Code tabs)
 * - Tabs are square (no radius)
 * - Text mode shows formatted output
 * - Code mode shows raw HTML
 * - Bold/Italic/Underline works reliably (selection preserved)
 * ========================= */
.rte-row{margin-bottom:16px;}
.rte-wrap{
  border:1px solid var(--line-strong);
  border-radius:14px;
  overflow:hidden;
  background:var(--surface);
}

/* Toolbar row */
.rte-toolbar{
  display:flex;
  align-items:center;
  gap:6px;
  flex-wrap:wrap;
  padding:8px;
  border-bottom:1px solid var(--line-strong);
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
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:6px;
  user-select:none;
}
.rte-btn:hover{background:var(--page-hover)}
.rte-btn.active{
  background:color-mix(in oklab, var(--primary-color) 14%, transparent);
  border-color:color-mix(in oklab, var(--primary-color) 35%, var(--line-soft));
}
.rte-sep{width:1px;height:24px;background:var(--line-soft);margin:0 4px}

/* ✅ Text/Code as square tabs (no radius) */
.rte-tabs{
  margin-left:auto;
  display:flex;
  border:1px solid var(--line-soft);
  border-radius:0;
  overflow:hidden;
}
.rte-tabs .tab{
  border:0;
  border-right:1px solid var(--line-soft);
  border-radius:0;
  padding:7px 12px;
  font-size:12px;
  cursor:pointer;
  background:transparent;
  color:var(--ink);
  line-height:1;
  user-select:none;
}
.rte-tabs .tab:last-child{border-right:0}
.rte-tabs .tab.active{
  background:color-mix(in oklab, var(--primary-color) 12%, transparent);
  font-weight:700;
}

/* Area */
.rte-area{position:relative}
.rte-editor{
  min-height:180px;
  padding:12px 12px;
  outline:none;
}
.rte-editor:empty:before{content:attr(data-placeholder);color:var(--muted-color);}

/* keep formatting visible like course module editor */
.rte-editor b, .rte-editor strong{font-weight:800}
.rte-editor i, .rte-editor em{font-style:italic}
.rte-editor u{text-decoration:underline}
.rte-editor h1{font-size:20px;margin:8px 0}
.rte-editor h2{font-size:18px;margin:8px 0}
.rte-editor h3{font-size:16px;margin:8px 0}
.rte-editor ul, .rte-editor ol{padding-left:22px}
.rte-editor p{margin:0 0 10px}
.rte-editor a{color:var(--primary-color);text-decoration:underline}

.rte-editor code{
  padding:2px 6px;
  border-radius:0;
  background:color-mix(in oklab, var(--muted-color) 14%, transparent);
  border:1px solid var(--line-soft);
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  font-size:12.5px;
}
.rte-editor pre{
  padding:10px 12px;
  border-radius:0;
  background:color-mix(in oklab, var(--muted-color) 10%, transparent);
  border:1px solid var(--line-soft);
  overflow:auto;
  margin:8px 0;
}
.rte-editor pre code{
  border:0;background:transparent;padding:0;display:block;white-space:pre;
}

/* Code textarea */
.rte-code{
  display:none;
  width:100%;
  min-height:180px;
  padding:12px 12px;
  border:0;
  outline:none;
  resize:vertical;
  background:transparent;
  color:var(--ink);
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  font-size:12.5px;
  line-height:1.45;
}
.rte-wrap.mode-code .rte-editor{display:none;}
.rte-wrap.mode-code .rte-code{display:block;}

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

        {{-- RTE blocks (Dual mode) --}}
        @php
          $rtes = [
            ['key'=>'affiliation',      'label'=>'Affiliation',      'ph'=>'Write affiliation…'],
            ['key'=>'specification',    'label'=>'Specification',    'ph'=>'Write specification…'],
            ['key'=>'experience',       'label'=>'Experience',       'ph'=>'Write experience…'],
            ['key'=>'interest',         'label'=>'Interest',         'ph'=>'Write interest…'],
            ['key'=>'administration',   'label'=>'Administration',   'ph'=>'Write administration…'],
            ['key'=>'research_project', 'label'=>'Research Project', 'ph'=>'Write research project…'],
          ];
        @endphp

        @foreach($rtes as $r)
          <div class="rte-row" data-rte="{{ $r['key'] }}">
            <label class="form-label">{{ $r['label'] }}</label>

            <div class="rte-wrap" id="{{ $r['key'] }}Wrap">
              <div class="rte-toolbar" data-for="{{ $r['key'] }}">
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

                <button type="button" class="rte-btn" data-cmd="formatBlock" data-val="pre" title="Code Block"><i class="fa fa-code"></i></button>
                <button type="button" class="rte-btn" data-cmd="insertHTML" data-val="<code>code</code>" title="Inline Code"><i class="fa fa-terminal"></i></button>

                <span class="rte-sep"></span>

                <button type="button" class="rte-btn" data-cmd="removeFormat" title="Clear"><i class="fa fa-eraser"></i></button>

                <div class="rte-tabs">
                  <button type="button" class="tab active" data-mode="text">Text</button>
                  <button type="button" class="tab" data-mode="code">Code</button>
                </div>
              </div>

              <div class="rte-area">
                <div id="{{ $r['key'] }}Editor" class="rte-editor" contenteditable="true" data-placeholder="{{ $r['ph'] }}"></div>
                <textarea id="{{ $r['key'] }}Code" class="rte-code" spellcheck="false" autocomplete="off" autocapitalize="off" autocorrect="off"
                  placeholder="HTML code…"></textarea>
              </div>
            </div>
          </div>
        @endforeach

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
(function(){
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

  function setButtonLoading(button, loading){
    if(!button) return;
    button.disabled = !!loading;
    button.classList.toggle('btn-loading', !!loading);
  }

  // =========================
  // Tags (Qualification) - Delegation (works after swaps)
  // =========================
  const state = { qualification: [] };
  let currentUser = { id:null, uuid:'', role:'' };
  let hasRow = false;
  let lastServerData = null;

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
    const qualTags = document.getElementById('qualTags');
    if(!qualTags) return;
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
    const qualInput = document.getElementById('qualInput');
    if(qualInput){
      qualInput.value = '';
      qualInput.focus();
    }
  }

  document.addEventListener('click', (e) => {
    const rm = e.target.closest('#qualTags button.x[data-idx]');
    if (rm) {
      const idx = parseInt(rm.dataset.idx, 10);
      if (!Number.isNaN(idx)) {
        state.qualification.splice(idx, 1);
        renderTags();
      }
      return;
    }

    const addBtn = e.target.closest('#btnAddQual');
    if (addBtn) {
      e.preventDefault();
      addTag(document.getElementById('qualInput')?.value);
      return;
    }
  });

  document.addEventListener('keydown', (e) => {
    const inp = e.target.closest('#qualInput');
    if (!inp) return;

    if (e.key === 'Enter') {
      e.preventDefault();
      e.stopPropagation();
      addTag(inp.value);
    }

    if (e.key === 'Backspace' && !inp.value && state.qualification.length) {
      state.qualification.pop();
      renderTags();
    }
  });

  // =========================
  // Course-module-like Dual Editor
  // =========================
  const rteKeys = ['affiliation','specification','experience','interest','administration','research_project'];
  const rte = {};            // { key: {wrap, editor, code, mode} }
  const savedRange = {};     // { key: Range }

  function htmlOrEmpty(v){
    const s = (v ?? '').toString().trim();
    return s ? s : '';
  }

  // keep <pre> content safe (and consistent)
  function ensureWrappedInPreCode(html){
    return (html || '').replace(/<pre>([\s\S]*?)<\/pre>/gi, (m, inner)=>{
      if(/<code[\s>]/i.test(inner)) return `<pre>${inner}</pre>`;
      return `<pre><code>${inner}</code></pre>`;
    });
  }

  function saveSelectionFor(key){
    const o = rte[key];
    if(!o || o.mode !== 'text') return;
    const sel = window.getSelection();
    if(!sel || sel.rangeCount === 0) return;
    const range = sel.getRangeAt(0);
    if(!o.editor.contains(range.commonAncestorContainer)) return;
    savedRange[key] = range.cloneRange();
  }

  function restoreSelectionFor(key){
    const o = rte[key];
    if(!o || o.mode !== 'text' || !savedRange[key]) return false;
    const sel = window.getSelection();
    if(!sel) return false;
    o.editor.focus();
    sel.removeAllRanges();
    sel.addRange(savedRange[key]);
    return true;
  }

  function updateToolbarActive(key){
    const o = rte[key];
    if(!o || o.mode !== 'text') return;

    const tb = o.wrap.querySelector('.rte-toolbar');
    if(!tb) return;

    const setActive = (cmd, on)=>{
      const b = tb.querySelector(`.rte-btn[data-cmd="${cmd}"]`);
      if(b) b.classList.toggle('active', !!on);
    };

    try{
      setActive('bold', document.queryCommandState('bold'));
      setActive('italic', document.queryCommandState('italic'));
      setActive('underline', document.queryCommandState('underline'));
    }catch(_){}
  }

  function registerRTE(key){
    const wrap = document.getElementById(key+'Wrap');
    const editor = document.getElementById(key+'Editor');
    const code = document.getElementById(key+'Code');
    if(!wrap || !editor || !code) return;

    rte[key] = { wrap, editor, code, mode:'text' };

    // selection tracking
    editor.addEventListener('focus', ()=> { window.__ACTIVE_RTE__ = key; });
    ['click','mouseup','keyup','input'].forEach(ev=>{
      editor.addEventListener(ev, ()=>{
        saveSelectionFor(key);
        updateToolbarActive(key);
      });
    });
    editor.addEventListener('blur', ()=> saveSelectionFor(key));

    // sync editor -> code
    const syncToCode = ()=>{
      if(rte[key].mode === 'text'){
        code.value = ensureWrappedInPreCode(editor.innerHTML || '');
      }
    };
    editor.addEventListener('input', syncToCode);
    editor.addEventListener('blur', syncToCode);

    // sync code -> editor while typing in code mode
    code.addEventListener('input', ()=>{
      if(rte[key].mode === 'code'){
        editor.innerHTML = ensureWrappedInPreCode(code.value || '');
      }
    });
  }

  rteKeys.forEach(registerRTE);

  function setMode(key, mode){
    const o = rte[key];
    if(!o) return;
    o.mode = (mode === 'code') ? 'code' : 'text';
    o.wrap.classList.toggle('mode-code', o.mode === 'code');

    // activate tabs
    o.wrap.querySelectorAll('.rte-tabs .tab').forEach(t=>{
      t.classList.toggle('active', t.dataset.mode === o.mode);
    });

    // disable formatting buttons in code mode
    o.wrap.querySelectorAll('.rte-toolbar .rte-btn').forEach(btn=>{
      btn.disabled = (o.mode === 'code');
      btn.style.opacity = (o.mode === 'code') ? '0.55' : '';
      btn.style.pointerEvents = (o.mode === 'code') ? 'none' : '';
    });

    if(o.mode === 'code'){
      o.code.value = ensureWrappedInPreCode(o.editor.innerHTML || '');
      setTimeout(()=>o.code.focus(), 0);
    }else{
      o.editor.innerHTML = ensureWrappedInPreCode(o.code.value || '');
      setTimeout(()=>{
        o.editor.focus();
        saveSelectionFor(key);
        updateToolbarActive(key);
      }, 0);
    }
  }

  function wrapSelectionAsHeading(tag, editorEl){
    editorEl.focus();
    const sel = window.getSelection();
    if(!sel || sel.rangeCount === 0) return;
    const range = sel.getRangeAt(0);
    if(!editorEl.contains(range.commonAncestorContainer)) return;

    const txt = sel.toString();
    if(!txt.trim()){
      // If nothing selected, just insert empty heading at cursor
      document.execCommand('insertHTML', false, `<${tag}></${tag}>`);
      // Move cursor inside the new heading
      const headings = editorEl.getElementsByTagName(tag);
      if(headings.length > 0){
        const lastHeading = headings[headings.length - 1];
        const range = document.createRange();
        range.selectNodeContents(lastHeading);
        range.collapse(true);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
      }
    }else{
      // Wrap selected text with heading tag
      const fragment = range.extractContents();
      const heading = document.createElement(tag);
      heading.appendChild(fragment);
      range.insertNode(heading);
      
      // Restore selection
      const newRange = document.createRange();
      newRange.selectNodeContents(heading);
      newRange.collapse(false); // Move to end
      sel.removeAllRanges();
      sel.addRange(newRange);
    }
    
    // Clean up any empty <p> tags
    editorEl.innerHTML = editorEl.innerHTML.replace(/<p>\s*<\/p>/gi, '');
  }

  // ✅ KEY FIX: preserve selection while clicking toolbar (so B/I/U works)
  document.addEventListener('pointerdown', (e)=>{
    if(e.target.closest('.rte-toolbar')) e.preventDefault();
  });

  document.addEventListener('selectionchange', ()=>{
    const key = window.__ACTIVE_RTE__;
    if(key && rte[key] && rte[key].mode === 'text'){
      saveSelectionFor(key);
      updateToolbarActive(key);
    }
  });

  document.addEventListener('click', (e)=>{
    // tabs
    const tab = e.target.closest('.rte-tabs .tab');
    if(tab){
      const wrap = tab.closest('.rte-wrap');
      if(!wrap) return;
      const key = wrap.id.replace('Wrap','');
      setMode(key, tab.dataset.mode);
      return;
    }

    // toolbar button
    const btn = e.target.closest('.rte-toolbar .rte-btn');
    if(!btn) return;

    const tb = btn.closest('.rte-toolbar');
    const key = tb?.getAttribute('data-for');
    if(!key || !rte[key]) return;
    if(rte[key].mode === 'code') return;

    const editorEl = rte[key].editor;

    // ✅ restore selection BEFORE exec
    if(!restoreSelectionFor(key)) editorEl.focus();

    const cmd = btn.getAttribute('data-cmd');
    const val = btn.getAttribute('data-val');
    const h = btn.getAttribute('data-h');

    if(h){
      wrapSelectionAsHeading(h, editorEl);
      editorEl.innerHTML = ensureWrappedInPreCode(editorEl.innerHTML || '');
      rte[key].code.value = ensureWrappedInPreCode(editorEl.innerHTML||'');
      saveSelectionFor(key);
      updateToolbarActive(key);
      return;
    }

    if(cmd === 'insertHTML' && val){
      if(val === '<code>code</code>'){
        // For inline code, wrap selection
        const sel = window.getSelection();
        if(sel && sel.rangeCount > 0 && !sel.isCollapsed){
          const range = sel.getRangeAt(0);
          const selectedText = range.toString();
          if(selectedText.trim()){
            document.execCommand('insertHTML', false, `<code>${selectedText}</code>`);
          }
        }else{
          document.execCommand('insertHTML', false, val);
        }
      }else{
        document.execCommand('insertHTML', false, val);
      }
      editorEl.innerHTML = ensureWrappedInPreCode(editorEl.innerHTML || '');
      rte[key].code.value = ensureWrappedInPreCode(editorEl.innerHTML||'');
      saveSelectionFor(key);
      updateToolbarActive(key);
      return;
    }

    if(cmd === 'formatBlock' && val === 'pre'){
      // For code blocks, wrap selection
      const sel = window.getSelection();
      if(sel && sel.rangeCount > 0){
        const range = sel.getRangeAt(0);
        const selectedText = range.toString();
        if(selectedText.trim()){
          document.execCommand('insertHTML', false, `<pre><code>${selectedText}</code></pre>`);
        }else{
          document.execCommand('insertHTML', false, '<pre><code></code></pre>');
        }
      }
      editorEl.innerHTML = ensureWrappedInPreCode(editorEl.innerHTML || '');
      rte[key].code.value = ensureWrappedInPreCode(editorEl.innerHTML||'');
      saveSelectionFor(key);
      updateToolbarActive(key);
      return;
    }

    if(cmd){
      try{ 
        // For standard formatting commands, use execCommand directly
        document.execCommand(cmd, false, null); 
      }
      catch(ex){ console.error('execCommand failed', cmd, ex); }
      editorEl.innerHTML = ensureWrappedInPreCode(editorEl.innerHTML || '');
      rte[key].code.value = ensureWrappedInPreCode(editorEl.innerHTML||'');
      saveSelectionFor(key);
      updateToolbarActive(key);
    }
  });

  // hidden inputs
  const hidden = {
    affiliation: document.getElementById('affiliation'),
    specification: document.getElementById('specification'),
    experience: document.getElementById('experience'),
    interest: document.getElementById('interest'),
    administration: document.getElementById('administration'),
    research_project: document.getElementById('research_project'),
  };

  function collectPayload(){
    rteKeys.forEach(k=>{
      const o = rte[k];
      if(!o || !hidden[k]) return;
      const html = (o.mode === 'code') ? (o.code.value || '') : (o.editor.innerHTML || '');
      hidden[k].value = (ensureWrappedInPreCode(html) || '').trim();
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

    // qualification (supports json string)
    let q = d?.qualification ?? d?.qualifications ?? [];
    if (typeof q === 'string') { try { q = JSON.parse(q); } catch(e){ q = []; } }
    state.qualification = Array.isArray(q) ? q.filter(Boolean).map(sanitizeTag) : [];
    state.qualification = uniqLower(state.qualification);
    renderTags();

    // editors
    rteKeys.forEach(k=>{
      const o = rte[k];
      if(!o) return;
      o.editor.innerHTML = ensureWrappedInPreCode(htmlOrEmpty(d?.[k]));
      o.code.value = ensureWrappedInPreCode(o.editor.innerHTML || '');
      setMode(k, 'text');
      updateToolbarActive(k);
    });

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

  btnReset?.addEventListener('click', ()=>{
    if(lastServerData) applyServerData(lastServerData);
    ok('Reset to last saved data');
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
  // Save / Delete
  // =========================
  form?.addEventListener('submit', async (e)=>{
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

  btnDelete?.addEventListener('click', async ()=>{
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
})();
</script>
@endpush