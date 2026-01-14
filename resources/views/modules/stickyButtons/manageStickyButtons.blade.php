{{-- resources/views/modules/home/manageStickyButtons.blade.php --}}
@section('title','Sticky Buttons')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================================================
  Sticky Buttons (Admin)
  - Loads ALL contact-info items
  - You SELECT which ones to store
  - Saves a SINGLE configuration record (create once, then update)
  - UI DNA: similar shell + status pill + sticky actions
========================================================= */

.sb-wrap{max-width:1200px;margin:16px auto 44px;padding:0 6px;overflow:visible}

/* Page shell */
.sb-shell{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:18px;
  box-shadow:var(--shadow-2);
  overflow:hidden;
}
.sb-shell-h{
  display:flex;align-items:flex-start;justify-content:space-between;gap:10px;
  padding:14px 14px;
  border-bottom:1px solid var(--line-soft);
}
.sb-shell-title{display:flex;align-items:center;gap:10px}
.sb-shell-title i{opacity:.85}
.sb-shell-sub{color:var(--muted-color);font-size:12.5px;margin-top:4px}
.sb-shell-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}

/* Status pill */
.sb-pill{
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 10px;
  border-radius:999px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  font-size:12.5px;
  color:var(--ink);
}
.sb-pill .dot{width:8px;height:8px;border-radius:999px;background:var(--muted-color)}
.sb-pill.ok .dot{background:var(--success-color)}
.sb-pill.warn .dot{background:var(--warning-color, #f59e0b)}
.sb-pill.err .dot{background:var(--danger-color)}

/* Body */
.sb-shell-b{padding:14px 14px}

/* Loading overlay */
.loading-overlay{
  position:fixed; inset:0;
  background:rgba(0,0,0,.45);
  display:flex; align-items:center; justify-content:center;
  z-index:9999;
  backdrop-filter:blur(2px);
}
.loading-spinner{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,.3);
}
.spinner{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,.3);
  border-top:4px solid var(--primary-color);
  animation:spin 1s linear infinite;
}
@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

/* Button loading */
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
  animation:spin 1s linear infinite;
}

.sb-help{font-size:12px;color:var(--muted-color)}

/* Boxes */
.box{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:hidden;
}
.box .box-h{
  display:flex;align-items:center;justify-content:space-between;gap:10px;
  padding:10px 12px;border-bottom:1px solid var(--line-soft);
}
.box .box-b{padding:12px}

/* Picker */
.sb-toolbar{
  display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;
  margin-bottom:10px;
}
.sb-toolbar .form-control{border-radius:999px}
.sb-meta{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.sb-count{
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 10px;border-radius:999px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  font-size:12px;
  color:var(--ink);
}
.sb-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));
  gap:12px;
}
.sb-card{
  border:1px solid var(--line-strong);
  border-radius:14px;
  background:var(--surface);
  box-shadow:var(--shadow-1);
  overflow:hidden;
  cursor:pointer;
  display:flex;
  flex-direction:column;
  min-height:126px;
  transition:transform .12s ease, box-shadow .12s ease, border-color .12s ease;
}
.sb-card:hover{transform:translateY(-1px);box-shadow:var(--shadow-2)}
.sb-card.is-checked{
  border-color:color-mix(in oklab, var(--primary-color) 70%, var(--line-strong));
  box-shadow:0 8px 18px rgba(0,0,0,.10);
}
.sb-card-top{
  display:flex;align-items:center;justify-content:space-between;
  padding:8px 10px;
  border-bottom:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.sb-check{
  display:flex;align-items:center;gap:8px;
  font-size:12.5px;color:var(--muted-color);
}
.sb-check input{transform:scale(1.05)}
.sb-badge{
  font-size:11px;
  padding:3px 8px;
  border-radius:999px;
  border:1px solid var(--line-soft);
  color:var(--muted-color);
}
.sb-badge.off{border-color:color-mix(in oklab, var(--danger-color) 35%, var(--line-soft)); color:var(--danger-color)}
.sb-card-body{padding:10px;display:flex;flex-direction:column;gap:8px}
.sb-title{display:flex;align-items:center;gap:10px}
.sb-ico{
  width:34px;height:34px;border-radius:12px;
  display:inline-flex;align-items:center;justify-content:center;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  color:var(--ink);
}
.sb-name{font-weight:800;font-size:13px;color:var(--ink);line-height:1.15}
.sb-val{font-size:12.5px;color:var(--muted-color);word-break:break-word}
.sb-foot{display:flex;gap:8px;flex-wrap:wrap;margin-top:auto}
.sb-chip{
  font-size:11px;padding:3px 8px;border-radius:999px;
  border:1px solid var(--line-soft);
  color:var(--muted-color);
}

/* Preview */
.sb-preview-wrap{
  border:1px dashed var(--line-soft);
  border-radius:16px;
  padding:12px;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.sb-preview{
  display:flex;flex-direction:column;gap:10px;
  align-items:flex-end;
}
.sb-preview-btn{
  display:inline-flex;align-items:center;gap:10px;
  padding:10px 12px;
  border-radius:999px;
  border:1px solid var(--line-strong);
  background:var(--surface);
  box-shadow:var(--shadow-1);
  max-width:100%;
}
.sb-preview-btn i{opacity:.9}
.sb-preview-btn .txt{
  font-size:12.5px;
  color:var(--ink);
  max-width:240px;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}
.sb-empty{
  padding:12px;
  border:1px dashed var(--line-soft);
  border-radius:12px;
  color:var(--muted-color);
  font-size:12.5px;
}

@media (max-width: 768px){
  .sb-shell-h{flex-direction:column}
  .sb-shell-actions{justify-content:flex-start}
  .sb-grid{grid-template-columns:1fr}
  .sb-preview{align-items:stretch}
  .sb-preview-btn{justify-content:center}
}
</style>
@endpush

@section('content')
<div class="sb-wrap">

  {{-- Loading Overlay --}}
  <div id="sbLoading" class="loading-overlay" style="display:none;">
    <div class="loading-spinner">
      <div class="spinner"></div>
      <div class="small">Loading…</div>
    </div>
  </div>

  <div class="sb-shell">
    <div class="sb-shell-h">
      <div>
        <div class="sb-shell-title">
          <i class="fa-solid fa-grip-vertical"></i>
          <div>
            <div class="fw-semibold">Sticky Buttons Settings</div>
            <div class="sb-shell-sub">
              Loads <b>all</b> Contact Info. You pick which to show as Sticky Buttons. Saves a <b>single</b> configuration record.
            </div>
          </div>
        </div>

        <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
          <span id="sbStatusPill" class="sb-pill warn">
            <span class="dot"></span>
            <span id="sbStatusText">Not loaded</span>
          </span>
          <span class="sb-pill">
            <i class="fa-regular fa-clock"></i>
            <span id="sbUpdatedText">Updated: —</span>
          </span>
        </div>
      </div>

      <div class="sb-shell-actions">
        <button type="button" class="btn btn-light" id="sbBtnReload">
          <i class="fa fa-arrows-rotate me-1"></i>Reload
        </button>
        <button type="button" class="btn btn-outline-primary" id="sbBtnReset">
          <i class="fa fa-rotate-left me-1"></i>Reset
        </button>
        <button type="button" class="btn btn-primary" id="sbBtnSaveTop" style="display:none;">
          <i class="fa fa-floppy-disk me-1"></i>Save
        </button>
      </div>
    </div>

    <div class="sb-shell-b">
      <form id="sbForm" autocomplete="off">
        <input type="hidden" id="sbUuid">
        <input type="hidden" id="sbId">

        <div class="row g-3">
          {{-- LEFT: Picker --}}
          <div class="col-lg-7">
            <div class="box">
              <div class="box-h">
                <div class="fw-semibold">
                  <i class="fa-solid fa-address-book me-2"></i>Contact Info Picker
                </div>
                <div class="d-flex gap-2 align-items-center">
                  <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" id="sbEnabled">
                    <label class="form-check-label small text-muted" for="sbEnabled">Enabled</label>
                  </div>
                  <button type="button" class="btn btn-light btn-sm" id="sbReloadContacts">
                    <i class="fa fa-arrows-rotate me-1"></i>Reload
                  </button>
                </div>
              </div>
              <div class="box-b">

                <div class="sb-toolbar">
                  <input id="sbSearch" class="form-control" placeholder="Search contact info…">
                  <div class="sb-meta">
                    <span class="sb-count">
                      <i class="fa-regular fa-square-check"></i>
                      <span id="sbSelectedCount">Selected: 0</span>
                    </span>
                  </div>
                </div>

                <div id="sbContactsError" class="text-danger small" style="display:none;"></div>
                <div id="sbContactsGrid" class="sb-grid"></div>

                <div class="sb-help mt-2">
                  Saved as <code>contact_info_ids_json</code> = <code>contact_info.id[]</code>.
                </div>
              </div>
            </div>
          </div>

          {{-- RIGHT: Preview --}}
          <div class="col-lg-5">
            <div class="box">
              <div class="box-h">
                <div class="fw-semibold">
                  <i class="fa-solid fa-eye me-2"></i>Preview (Selected Only)
                </div>
              </div>
              <div class="box-b">
                <div class="sb-preview-wrap">
                  <div id="sbPreview" class="sb-preview"></div>
                  <div id="sbPreviewEmpty" class="sb-empty" style="display:none;">
                    No Contact Info selected yet.
                  </div>
                </div>
                <div class="sb-help mt-2">
                  This is only a preview for admin. Frontend will use the saved selection.
                </div>
              </div>
            </div>

            <div class="mt-3 d-flex flex-wrap gap-2 justify-content-end">
              <button type="button" class="btn btn-light" id="sbBtnResetBottom">
                <i class="fa fa-rotate-left me-1"></i>Reset
              </button>
              <button type="submit" class="btn btn-primary" id="sbSaveBtn" style="display:none;">
                <i class="fa fa-floppy-disk me-1"></i> Save
              </button>
            </div>
            <div class="sb-help mt-2">
              First save creates the record. Next saves update the same record.
            </div>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="sbToastSuccess" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="sbToastSuccessText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="sbToastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="sbToastErrorText">Something went wrong</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
  if (window.__STICKY_BUTTONS_MODULE_INIT__) return;
  window.__STICKY_BUTTONS_MODULE_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=250) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  async function fetchWithTimeout(url, opts={}, ms=15000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try{ return await fetch(url, { ...opts, signal: ctrl.signal }); }
    finally{ clearTimeout(t); }
  }

  // Simple icon mapper (fallback if your contact-info doesn't have icon_class)
  function iconFor(item){
    const t = (item?.type || item?.contact_type || item?.key || '').toString().toLowerCase();
    const v = (item?.value || item?.contact_value || item?.email || item?.phone || '').toString().toLowerCase();

    if (item?.icon_class) return item.icon_class;

    if (t.includes('whatsapp') || v.includes('wa.me')) return 'fa-brands fa-whatsapp';
    if (t.includes('phone') || t.includes('mobile') || t.includes('call')) return 'fa-solid fa-phone';
    if (t.includes('email') || v.includes('@')) return 'fa-solid fa-envelope';
    if (t.includes('location') || t.includes('address') || t.includes('map')) return 'fa-solid fa-location-dot';
    if (t.includes('facebook')) return 'fa-brands fa-facebook';
    if (t.includes('instagram')) return 'fa-brands fa-instagram';
    if (t.includes('linkedin')) return 'fa-brands fa-linkedin';
    if (t.includes('youtube')) return 'fa-brands fa-youtube';
    if (t.includes('twitter') || t.includes('x')) return 'fa-brands fa-x-twitter';
    return 'fa-solid fa-link';
  }

  function displayName(item){
    return (item?.title || item?.label || item?.name || item?.key || 'Contact').toString().trim();
  }
  function displayValue(item){
    return (item?.value || item?.contact_value || item?.email || item?.phone || item?.url || '').toString().trim();
  }
  function isActive(item){
    const v = item?.is_active ?? item?.active ?? item?.status ?? 1;
    // supports 1/0, true/false, '1'/'0', 'active'/'inactive'
    if (typeof v === 'string'){
      const s = v.toLowerCase().trim();
      if (s === 'inactive' || s === '0' || s === 'no') return false;
      if (s === 'active' || s === '1' || s === 'yes') return true;
    }
    return !!Number(v);
  }

  function safeArray(v){
    if (Array.isArray(v)) return v;
    if (typeof v === 'string'){
      try{
        const j = JSON.parse(v);
        return Array.isArray(j) ? j : [];
      }catch(_){ return []; }
    }
    return [];
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const loadingEl = $('sbLoading');
    const showLoading = (v) => { if (loadingEl) loadingEl.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('sbToastSuccess');
    const toastErrEl = $('sbToastError');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { const el=$('sbToastSuccessText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('sbToastErrorText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = (json=false) => {
      const h = {
        'Authorization': 'Bearer ' + token,
        'Accept': 'application/json'
      };
      if (json) h['Content-Type'] = 'application/json';
      return h;
    };

    // ----- Permissions (same pattern)
    const ACTOR = { role: '' };
    let canCreate=false, canEdit=false;
    function computePermissions(){
      const r = (ACTOR.role || '').toLowerCase();
      const createRoles = ['admin','super_admin','director','principal'];
      const writeRoles  = ['admin','super_admin','director','principal','hod','faculty','technical_assistant','it_person'];
      canCreate = createRoles.includes(r);
      canEdit   = writeRoles.includes(r);
    }
    async function fetchMe(){
      try{
        const res = await fetchWithTimeout('/api/users/me', { headers: authHeaders() }, 8000);
        if (res.ok){
          const js = await res.json().catch(()=> ({}));
          const role = js?.data?.role || js?.role;
          if (role) ACTOR.role = String(role).toLowerCase();
        }
      }catch(_){}
      if (!ACTOR.role){
        ACTOR.role = (sessionStorage.getItem('role') || localStorage.getItem('role') || '').toLowerCase();
      }
      computePermissions();
    }

    // ----- Refs
    const form = $('sbForm');
    const saveBtn = $('sbSaveBtn');
    const saveBtnTop = $('sbBtnSaveTop');
    const btnReload = $('sbBtnReload');
    const btnResetTop = $('sbBtnReset');
    const btnResetBottom = $('sbBtnResetBottom');

    const statusPill = $('sbStatusPill');
    const statusText = $('sbStatusText');
    const updatedText = $('sbUpdatedText');

    const sbUuid = $('sbUuid');
    const sbId = $('sbId');

    const enabledEl = $('sbEnabled');

    const contactsGrid = $('sbContactsGrid');
    const contactsErr  = $('sbContactsError');
    const contactsReloadBtn = $('sbReloadContacts');
    const searchEl = $('sbSearch');
    const selectedCountEl = $('sbSelectedCount');

    const previewEl = $('sbPreview');
    const previewEmptyEl = $('sbPreviewEmpty');

    // ---- API endpoints
    const API = {
      stickyIndex: '/api/sticky-buttons',              // list/create
      stickyCurrent: '/api/sticky-buttons/current',    // fallback (returns { item })
      contactIndex: '/api/contact-info'                // list all contact-info
    };

    const state = {
      currentItem: null,
      saving: false,

      contactsLoaded: false,
      contactsLoading: false,
      contactOptions: [],

      selectedIds: [],
    };

    function setBtnLoading(btn, loading){
      if (!btn) return;
      btn.disabled = !!loading;
      btn.classList.toggle('btn-loading', !!loading);
    }

    function setStatus(kind, text){
      if (!statusPill || !statusText) return;
      statusPill.classList.remove('ok','warn','err');
      statusPill.classList.add(kind);
      statusText.textContent = text || '—';
    }

    function setUpdated(s){
      if (!updatedText) return;
      updatedText.textContent = 'Updated: ' + (s || '—');
    }

    function showContactsError(msg){
      if (!contactsErr) return;
      contactsErr.style.display = msg ? '' : 'none';
      contactsErr.textContent = msg || '';
    }

    function normalizeIdsFromButtons(v){
      const arr = safeArray(v);
      const out = [];
      for (const b of arr){
        if (b && typeof b === 'object'){
          const id = b.contact_info_id ?? b.id ?? b.contact_id ?? null;
          if (id !== null && /^\d+$/.test(String(id))) out.push(Number(id));
        }
      }
      return Array.from(new Set(out));
    }

    function updateSelectedCount(){
      if (!selectedCountEl) return;
      selectedCountEl.textContent = 'Selected: ' + (state.selectedIds?.length || 0);
    }

    function getSearchTerm(){
      return (searchEl?.value || '').trim().toLowerCase();
    }

    function renderPreview(){
      if (!previewEl || !previewEmptyEl) return;

      const map = new Map(state.contactOptions.map(c => [Number(c.id), c]));
      const selected = (state.selectedIds || []).map(Number).filter(id => map.has(id));
      previewEl.innerHTML = selected.map(id => {
        const item = map.get(id);
        const ico = iconFor(item);
        const name = displayName(item);
        const val = displayValue(item);
        const txt = val ? `${name}: ${val}` : name;

        return `
          <div class="sb-preview-btn" title="${esc(txt)}">
            <i class="${esc(ico)}"></i>
            <span class="txt">${esc(txt)}</span>
          </div>
        `;
      }).join('');

      const has = selected.length > 0;
      previewEmptyEl.style.display = has ? 'none' : '';
    }

    function renderContacts(){
      if (!contactsGrid) return;

      const term = getSearchTerm();
      const selected = new Set((state.selectedIds || []).map(Number));

      const list = (state.contactOptions || []).filter(c => {
        if (!term) return true;
        const a = displayName(c).toLowerCase();
        const b = displayValue(c).toLowerCase();
        const t = (c?.type || c?.contact_type || c?.key || '').toString().toLowerCase();
        return a.includes(term) || b.includes(term) || t.includes(term);
      });

      contactsGrid.innerHTML = list.map(c => {
        const id = Number(c.id);
        const checked = selected.has(id);
        const ico = iconFor(c);
        const nm = displayName(c) || ('#' + id);
        const vv = displayValue(c);
        const typ = (c?.type || c?.contact_type || c?.key || '—').toString();
        const active = isActive(c);

        return `
          <label class="sb-card ${checked ? 'is-checked' : ''}" data-card="${id}">
            <div class="sb-card-top">
              <div class="sb-check">
                <input type="checkbox" data-cid="${id}" ${checked ? 'checked' : ''}/>
                <span>Pick</span>
              </div>
              <span class="sb-badge ${active ? '' : 'off'}">${active ? 'Active' : 'Inactive'}</span>
            </div>

            <div class="sb-card-body">
              <div class="sb-title">
                <span class="sb-ico"><i class="${esc(ico)}"></i></span>
                <div class="sb-name">${esc(nm)}</div>
              </div>
              <div class="sb-val">${esc(vv || '—')}</div>
              <div class="sb-foot">
                <span class="sb-chip">#${id}</span>
                <span class="sb-chip">${esc(typ)}</span>
              </div>
            </div>
          </label>
        `;
      }).join('');

      contactsGrid.querySelectorAll('input[type="checkbox"][data-cid]').forEach(cb => {
        cb.addEventListener('change', () => {
          const id = Number(cb.getAttribute('data-cid'));
          if (!Number.isFinite(id)) return;

          const set = new Set((state.selectedIds || []).map(Number));
          if (cb.checked) set.add(id);
          else set.delete(id);

          state.selectedIds = Array.from(set);
          updateSelectedCount();
          renderPreview();

          const card = contactsGrid.querySelector(`[data-card="${id}"]`);
          if (card) card.classList.toggle('is-checked', cb.checked);
        });
      });

      updateSelectedCount();
      renderPreview();
    }

    async function fetchContacts(force=false){
      if (state.contactsLoading) return;
      if (state.contactsLoaded && !force) return;

      state.contactsLoading = true;
      showContactsError('');

      const tryUrls = [
        API.contactIndex + '?per_page=500&page=1&sort=updated_at&direction=desc',
        API.contactIndex,
      ];

      try{
        let lastErr = null;
        for (const url of tryUrls){
          try{
            const res = await fetchWithTimeout(url, { headers: authHeaders() }, 15000);
            if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }
            if (!res.ok){ lastErr = new Error('Failed to load contact-info'); continue; }

            const js = await res.json().catch(()=> ({}));
            if (js.success === false){ lastErr = new Error(js?.message || 'Failed to load contact-info'); continue; }

            const arr = Array.isArray(js.data) ? js.data : (Array.isArray(js) ? js : []);
            state.contactOptions = arr;
            state.contactsLoaded = true;
            renderContacts();
            return;
          }catch(ex){
            lastErr = ex;
          }
        }
        throw lastErr || new Error('Failed to load contact-info');
      }catch(ex){
        showContactsError(ex?.name === 'AbortError' ? 'Contact-info request timed out' : (ex.message || 'Failed to load contact-info'));
      }finally{
        state.contactsLoading = false;
      }
    }

    async function fetchSingleton(){
      // primary: index list (latest 1)
      const params = new URLSearchParams();
      params.set('per_page','1');
      params.set('page','1');
      params.set('sort','updated_at');
      params.set('direction','desc');

      // attempt index
      try{
        const res = await fetchWithTimeout(`${API.stickyIndex}?${params.toString()}`, { headers: authHeaders() }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return null; }
        const js = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(js?.message || 'Failed to load');

        const arr = Array.isArray(js.data) ? js.data : [];
        return arr[0] || null;
      }catch(_){
        // fallback current (controller returns { item })
        try{
          const res2 = await fetchWithTimeout(API.stickyCurrent, { headers: authHeaders() }, 15000);
          if (res2.status === 401 || res2.status === 403) { window.location.href = '/'; return null; }
          const js2 = await res2.json().catch(()=> ({}));
          if (!res2.ok || js2.success === false) return null;
          return js2.item || null;
        }catch(__){
          return null;
        }
      }
    }

    function setFormEnabled(on){
      const inputs = form?.querySelectorAll('input,textarea,button,select') || [];
      inputs.forEach(el => {
        if (el.id === 'sbBtnReload' || el.id === 'sbBtnReset' || el.id === 'sbBtnResetBottom') return;

        if (!on){
          const t = (el.type || '').toLowerCase();
          if (el.tagName === 'BUTTON' || t === 'checkbox' || t === 'radio' || el.tagName === 'SELECT') el.disabled = true;
          else el.readOnly = true;
        }else{
          if (el.tagName === 'BUTTON' || el.tagName === 'SELECT') el.disabled = false;
          else { el.readOnly = false; el.disabled = false; }
        }
      });

      if (saveBtn) saveBtn.style.display = on ? '' : 'none';
      if (saveBtnTop) saveBtnTop.style.display = on ? '' : 'none';
    }

    function resetToBlank(){
      sbUuid.value = '';
      sbId.value = '';
      state.currentItem = null;

      enabledEl.checked = true; // default enabled
      state.selectedIds = [];
      updateSelectedCount();
      renderContacts();

      setUpdated('—');
      setStatus('warn', 'Not saved yet');

      setFormEnabled(!!canCreate);
    }

    function fillFromItem(item){
      state.currentItem = item || null;

      sbUuid.value = item?.uuid || '';
      sbId.value = item?.id || '';

      // ✅ server stores selection inside buttons_json -> { contact_info_id, ... }
      state.selectedIds = normalizeIdsFromButtons(item?.buttons_json);

      // ✅ enabled uses status (active/inactive)
      enabledEl.checked = String(item?.status ?? 'active') === 'active';

      renderContacts();

      setUpdated(item?.updated_at || '—');
      setStatus('ok', 'Loaded');

      setFormEnabled(!!canEdit);
    }

    async function reload(){
      showLoading(true);
      try{
        setStatus('warn', 'Loading…');
        await fetchContacts(false);

        const item = await fetchSingleton();
        if (item) fillFromItem(item);
        else resetToBlank();
      }catch(ex){
        setStatus('err', 'Load failed');
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        showLoading(false);
      }
    }

    function resetToCurrent(){
      if (state.currentItem) fillFromItem(state.currentItem);
      else resetToBlank();
    }

    btnReload?.addEventListener('click', reload);
    btnResetTop?.addEventListener('click', resetToCurrent);
    btnResetBottom?.addEventListener('click', resetToCurrent);

    contactsReloadBtn?.addEventListener('click', () => fetchContacts(true));
    searchEl?.addEventListener('input', debounce(() => renderContacts(), 140));

    saveBtnTop?.addEventListener('click', () => form?.requestSubmit?.() || saveBtn?.click());

    form?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (state.saving) return;

      const isEdit = !!sbUuid.value;
      if (isEdit && !canEdit) return;
      if (!isEdit && !canCreate) return;

      // ✅ payload matches controller:
      // - contact_info_ids OR buttons_json is required
      // - status active/inactive
      const payload = {
        contact_info_ids: (state.selectedIds || []).map(Number),
        status: enabledEl.checked ? 'active' : 'inactive',
      };

      // small guard: prevent empty save
      if (!payload.contact_info_ids.length){
        err('Please select at least 1 Contact Info to save.');
        return;
      }

      const url = isEdit
        ? `${API.stickyIndex}/${encodeURIComponent(sbUuid.value)}`
        : `${API.stickyIndex}`;

      state.saving = true;
      showLoading(true);
      setBtnLoading(saveBtn, true);
      setBtnLoading(saveBtnTop, true);

      try{
        const res = await fetchWithTimeout(url, {
          method: isEdit ? 'PUT' : 'POST',
          headers: authHeaders(true),
          body: JSON.stringify(payload)
        }, 20000);

        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false){
          let msg = js?.message || 'Save failed';
          if (js?.errors){
            const k = Object.keys(js.errors)[0];
            if (k && js.errors[k] && js.errors[k][0]) msg = js.errors[k][0];
          }
          throw new Error(msg);
        }

        ok(isEdit ? 'Updated' : 'Created');
        await reload();
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        state.saving = false;
        setBtnLoading(saveBtn, false);
        setBtnLoading(saveBtnTop, false);
        showLoading(false);
      }
    });

    // Init
    (async () => {
      showLoading(true);
      try{
        await fetchMe();
        await fetchContacts(false);
        await reload();
      }catch(ex){
        setStatus('err', 'Initialization failed');
        err(ex?.message || 'Initialization failed');
      }finally{
        showLoading(false);
      }
    })();
  });
})();
</script>

@endpush
