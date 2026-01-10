{{-- resources/views/modules/home/manageFooterComponents.blade.php --}}
@section('title','Footer Components')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Footer Components (Admin)
 * Single-record Form
 * ========================= */

.fc-wrap{max-width:1200px;margin:16px auto 44px;padding:0 6px;overflow:visible}

/* Shell */
.fc-shell{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:18px;
  box-shadow:var(--shadow-2);
  overflow:hidden;
}
.fc-head{
  display:flex;align-items:flex-start;justify-content:space-between;gap:10px;
  padding:14px 14px;border-bottom:1px solid var(--line-soft);
}
.fc-title{display:flex;gap:10px;align-items:center}
.fc-title i{opacity:.85}
.fc-sub{color:var(--muted-color);font-size:12.5px;margin-top:4px}
.fc-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}
.fc-body{padding:14px 14px}

/* Pills */
.fc-pill{
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 10px;border-radius:999px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  font-size:12.5px;color:var(--ink);
}
.fc-pill .dot{width:8px;height:8px;border-radius:999px;background:var(--muted-color)}
.fc-pill.ok .dot{background:var(--success-color)}
.fc-pill.warn .dot{background:var(--warning-color, #f59e0b)}
.fc-pill.err .dot{background:var(--danger-color)}

/* Overlay */
.fc-loading{
  position:fixed; inset:0;
  background:rgba(0,0,0,.45);
  display:none; align-items:center; justify-content:center;
  z-index:9999;
  backdrop-filter:blur(2px);
}
.fc-loading-card{
  background:var(--surface);
  padding:20px 22px;border-radius:14px;
  display:flex;flex-direction:column;align-items:center;gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,.3);
}
.fc-spinner{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,.3);
  border-top:4px solid var(--primary-color);
  animation:fcspin 1s linear infinite;
}
@keyframes fcspin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}

/* Button loading */
.fc-btn-loading{position:relative;color:transparent !important}
.fc-btn-loading::after{
  content:'';
  position:absolute;
  width:16px;height:16px;
  top:50%;left:50%;
  margin:-8px 0 0 -8px;
  border:2px solid transparent;
  border-top:2px solid currentColor;
  border-radius:50%;
  animation:fcspin 1s linear infinite;
}

.fc-help{font-size:12px;color:var(--muted-color)}

/* Cards */
.fc-box{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:hidden;
}
.fc-box-h{
  display:flex;align-items:center;justify-content:space-between;gap:10px;
  padding:10px 12px;border-bottom:1px solid var(--line-soft);
}
.fc-box-b{padding:12px}

/* Image preview */
.fc-img{
  width:100%;
  max-height:220px;
  object-fit:contain;
  border-radius:12px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, #ffffff 90%, transparent);
  padding:10px;
}
html.theme-dark .fc-img{
  background:color-mix(in oklab, var(--surface) 85%, transparent);
}

/* Repeaters */
.fc-repeater{display:flex;flex-direction:column;gap:10px}
.fc-row{
  border:1px solid var(--line-soft);
  border-radius:14px;
  padding:10px;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.fc-row-top{display:flex;gap:10px;align-items:center;justify-content:space-between}
.fc-row-grid-2{
  display:grid;
  grid-template-columns: minmax(0,1fr) minmax(0,1fr);
  gap:10px;
  margin-top:10px;
}
.fc-row-grid-3{
  display:grid;
  grid-template-columns: minmax(0,1fr) minmax(0,1fr) 140px;
  gap:10px;
  margin-top:10px;
  align-items:center;
}
.fc-row-grid-link{
  display:grid;
  grid-template-columns: minmax(0,1fr) minmax(0,1fr);
  gap:10px;
  margin-top:10px;
  align-items:center;
}

/* Child menu checklist */
.fc-childlist{
  display:grid;
  grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));
  gap:8px;
  margin-top:10px;
}
.fc-childitem{
  border:1px solid var(--line-soft);
  border-radius:12px;
  padding:8px 10px;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  display:flex;gap:10px;align-items:flex-start;
}
.fc-childitem input{transform:scale(1.05);margin-top:2px}

/* Max blocks helper */
.fc-limit-note{
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 10px;border-radius:999px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  font-size:12px;color:var(--ink);
}

/* Toggle */
.fc-toggle{
  display:inline-flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end;
}
.fc-switch{
  display:inline-flex;align-items:center;gap:10px;
  padding:6px 10px;border-radius:999px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.fc-switch input{width:42px;height:22px;appearance:none;cursor:pointer;border-radius:999px;position:relative;outline:none;
  background:color-mix(in oklab, var(--muted-color) 35%, transparent);
  border:1px solid var(--line-soft);
  transition:all .15s ease;
}
.fc-switch input::after{
  content:'';
  position:absolute;top:50%;left:3px;transform:translateY(-50%);
  width:16px;height:16px;border-radius:999px;
  background:var(--surface);
  border:1px solid var(--line-soft);
  transition:all .15s ease;
  box-shadow:0 6px 14px rgba(0,0,0,.12);
}
.fc-switch input:checked{
  background:color-mix(in oklab, var(--primary-color) 55%, transparent);
  border-color:color-mix(in oklab, var(--primary-color) 40%, var(--line-soft));
}
.fc-switch input:checked::after{left:22px}
.fc-switch .lbl{font-size:12.5px;color:var(--ink);font-weight:700}

/* Disabled look inside section 4 */
.fc-disabled-note{
  padding:10px 12px;
  border:1px dashed var(--line-soft);
  border-radius:12px;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  color:var(--muted-color);
  font-size:12.5px;
}

/* Responsive */
@media (max-width: 768px){
  .fc-head{flex-direction:column}
  .fc-actions{justify-content:flex-start}
  .fc-row-grid-2,.fc-row-grid-3,.fc-row-grid-link{grid-template-columns:1fr}
}
</style>
@endpush

@section('content')
<div class="fc-wrap">

  {{-- Loading --}}
  <div id="fcLoading" class="fc-loading">
    <div class="fc-loading-card">
      <div class="fc-spinner"></div>
      <div class="small">Loading…</div>
    </div>
  </div>

  <div class="fc-shell">
    <div class="fc-head">
      <div>
        <div class="fc-title">
          <i class="fa-solid fa-grip"></i>
          <div>
            <div class="fw-semibold">Footer Component Settings</div>
            <div class="fc-sub">
              This module stores a <b>single</b> footer configuration. Save once, then update the same record.
            </div>
          </div>
        </div>

        <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
          <span id="fcStatusPill" class="fc-pill warn">
            <span class="dot"></span>
            <span id="fcStatusText">Not loaded</span>
          </span>
          <span class="fc-pill">
            <i class="fa-regular fa-clock"></i>
            <span id="fcUpdatedText">Updated: —</span>
          </span>
        </div>
      </div>

      <div class="fc-actions">
        <button type="button" class="btn btn-light" id="fcBtnReload">
          <i class="fa fa-arrows-rotate me-1"></i>Reload
        </button>
        <button type="button" class="btn btn-outline-primary" id="fcBtnReset">
          <i class="fa fa-rotate-left me-1"></i>Reset
        </button>
        <button type="button" class="btn btn-primary" id="fcBtnSaveTop" style="display:none;">
          <i class="fa fa-floppy-disk me-1"></i>Save
        </button>
      </div>
    </div>

    <div class="fc-body">
      <form id="fcForm" autocomplete="off">
        <input type="hidden" id="fcUuid">
        <input type="hidden" id="fcId">

        <div class="row g-3">
          {{-- LEFT --}}
          <div class="col-lg-5">
            <div class="row g-3">

              {{-- Section 1: Link JSON array (title + link) --}}
              <div class="col-12">
                <div class="fc-box">
                  <div class="fc-box-h">
                    <div class="fw-semibold"><i class="fa-solid fa-link me-2"></i>Section 1: Link JSON Array</div>
                    <button type="button" class="btn btn-light btn-sm" id="fcAddS1Link">
                      <i class="fa fa-plus me-1"></i>Add Link
                    </button>
                  </div>
                  <div class="fc-box-b">
                    <div id="fcSection1Links" class="fc-repeater"></div>
                    <div class="fc-help mt-2">
                      Saved as <code>section_1_links_json</code> (array of objects: <code>{title, url}</code>).
                    </div>
                  </div>
                </div>
              </div>

              {{-- Section 2: Menu blocks (max 4) --}}
              <div class="col-12">
                <div class="fc-box">
                  <div class="fc-box-h">
                    <div class="fw-semibold"><i class="fa-solid fa-layer-group me-2"></i>Section 2: Menu Blocks</div>
                    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                      <span class="fc-limit-note" title="Maximum 4 blocks">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span id="fcBlockCountText">0 / 4</span>
                      </span>
                      <button type="button" class="btn btn-light btn-sm" id="fcAddMenuBlock">
                        <i class="fa fa-plus me-1"></i>Add Block
                      </button>
                    </div>
                  </div>
                  <div class="fc-box-b">
                    <div class="fc-help mb-2">
                      Add a block title (e.g., “Department”), pick a Header Menu, then choose which submenus to include.
                      Saved as <code>section_2_menu_blocks_json</code> (array). Max 4 blocks.
                      <br>
                      <b>Rule:</b> A Header Menu can be used <b>only once</b> across all blocks.
                    </div>

                    <div id="fcMenuBlocks" class="fc-repeater"></div>

                    <div class="fc-help mt-2">
                      Header menu options are loaded via <code>/api/header-menus</code> fallback chain (see JS).
                    </div>
                  </div>
                </div>
              </div>

              {{-- Metadata --}}
              <div class="col-12">
                <label class="form-label">Metadata (optional JSON)</label>
                <textarea id="fcMetadata" class="form-control" rows="6" placeholder='{"note":"optional"}'></textarea>
                <div class="fc-help">Must be valid JSON if provided. Leave empty to store NULL.</div>
              </div>

            </div>
          </div>

          {{-- RIGHT --}}
          <div class="col-lg-7">
            <div class="row g-3">

              {{-- Section 3: Link JSON array (title + link) --}}
              <div class="col-12">
                <div class="fc-box">
                  <div class="fc-box-h">
                    <div class="fw-semibold"><i class="fa-solid fa-link me-2"></i>Section 3: Link JSON Array</div>
                    <button type="button" class="btn btn-light btn-sm" id="fcAddS3Link">
                      <i class="fa fa-plus me-1"></i>Add Link
                    </button>
                  </div>
                  <div class="fc-box-b">
                    <div id="fcSection3Links" class="fc-repeater"></div>
                    <div class="fc-help mt-2">
                      Saved as <code>section_3_links_json</code> (array of objects: <code>{title, url}</code>).
                    </div>
                  </div>
                </div>
              </div>

              {{-- ✅ Address Text (NEW) --}}
              <div class="col-12">
                <div class="fc-box">
                  <div class="fc-box-h">
                    <div class="fw-semibold"><i class="fa-solid fa-location-dot me-2"></i>Address Text</div>
                  </div>
                  <div class="fc-box-b">
                    <label class="form-label">Address</label>
                    <textarea id="fcAddressText" class="form-control" rows="4"
                      placeholder="e.g., Meghnad Saha Institute of Technology, Techno India Group, Kolkata, West Bengal"></textarea>
                    <div class="fc-help mt-2">
                      Saved as <code>address_text</code>.
                    </div>
                  </div>
                </div>
              </div>

              {{-- Section 4: Brand (same-as-header toggle + logo + rotating + socials) --}}
              <div class="col-12">
                <div class="fc-box">
                  <div class="fc-box-h">
                    <div class="fw-semibold"><i class="fa-solid fa-id-badge me-2"></i>Section 4: Brand + Social</div>

                    <div class="fc-toggle">
                      <span class="fc-switch" title="Use Header Components data for footer branding">
                        <span class="lbl">Same as Header</span>
                        <input id="fcSameAsHeader" type="checkbox" aria-label="Same as Header">
                      </span>

                      <button type="button" class="btn btn-light btn-sm" id="fcOpenBrandLogo" style="display:none;">
                        <i class="fa fa-up-right-from-square me-1"></i>Open
                      </button>
                      <button type="button" class="btn btn-outline-danger btn-sm" id="fcClearBrandLogo">
                        <i class="fa-regular fa-trash-can me-1"></i>Clear
                      </button>
                    </div>
                  </div>

                  <div class="fc-box-b">
                    <div id="fcSameAsHeaderNote" class="fc-disabled-note mb-2" style="display:none;">
                      <i class="fa-solid fa-circle-info me-1"></i>
                      “Same as Header” is ON — Footer Title / Logo / Rotating Texts are locked and will be stored from Header Components:
                      <code>primary_logo_url → brand_logo_url</code>, <code>header_text → brand_title</code>, <code>rotating_text_json → rotating_text_json</code>.
                    </div>

                    <div class="row g-2">
                      <div class="col-12">
                        <label class="form-label">Footer Title (optional)</label>
                        <input id="fcBrandTitle" class="form-control" maxlength="255" placeholder="e.g., MSIT Home Builder">
                      </div>

                      <div class="col-md-8">
                        <label class="form-label">Logo URL / Path (optional)</label>
                        <input id="fcBrandLogoUrl" class="form-control" maxlength="255" placeholder="assets/... or https://...">
                        <div class="fc-help mt-1">Upload overrides URL/path.</div>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Upload Logo</label>
                        <input id="fcBrandLogoFile" type="file" class="form-control" accept="image/*">
                      </div>

                      <div class="col-12">
                        <img id="fcBrandLogoPreview" class="fc-img" style="display:none;" alt="Footer logo preview">
                        <div id="fcBrandLogoEmpty" class="text-muted small" style="padding:12px;border:1px dashed var(--line-soft);border-radius:12px;">
                          No footer logo selected.
                        </div>
                      </div>

                      {{-- Rotating texts --}}
                      <div class="col-12">
                        <div class="fc-box" style="box-shadow:none">
                          <div class="fc-box-h">
                            <div class="fw-semibold"><i class="fa-solid fa-rotate me-2"></i>Rotating Texts</div>
                            <button type="button" class="btn btn-light btn-sm" id="fcAddRotate">
                              <i class="fa fa-plus me-1"></i>Add
                            </button>
                          </div>
                          <div class="fc-box-b">
                            <div id="fcRotateList" class="fc-repeater"></div>
                            <div class="fc-help mt-2">Saved as <code>section_4_rotating_text_json</code> (array).</div>
                          </div>
                        </div>
                      </div>

                      {{-- Social links --}}
                      <div class="col-12">
                        <div class="fc-box" style="box-shadow:none">
                          <div class="fc-box-h">
                            <div class="fw-semibold"><i class="fa-solid fa-share-nodes me-2"></i>Social Links</div>
                            <button type="button" class="btn btn-light btn-sm" id="fcAddSocial">
                              <i class="fa fa-plus me-1"></i>Add
                            </button>
                          </div>
                          <div class="fc-box-b">
                            <div id="fcSocialList" class="fc-repeater"></div>
                            <div class="fc-help mt-2">
                              Saved as <code>section_4_social_links_json</code> (array of objects).
                            </div>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              </div>

              {{-- Section 5: Bottom links + copyright (links only) --}}
              <div class="col-12">
                <div class="fc-box">
                  <div class="fc-box-h">
                    <div class="fw-semibold"><i class="fa-regular fa-circle-down me-2"></i>Section 5: Bottom Bar</div>
                    <button type="button" class="btn btn-light btn-sm" id="fcAddBottomLink">
                      <i class="fa fa-plus me-1"></i>Add Link
                    </button>
                  </div>
                  <div class="fc-box-b">
                    <div id="fcBottomLinks" class="fc-repeater"></div>

                    <div class="mt-2">
                      <label class="form-label">Copyright Text</label>
                      <input id="fcCopyright" class="form-control" maxlength="255" placeholder="© 2026 Your Institute. All rights reserved.">
                    </div>

                    <div class="fc-help mt-2">
                      Saved as <code>section_5_links_json</code> (array of objects: <code>{title, url}</code>) and <code>section_5_copyright_text</code>.
                    </div>
                  </div>
                </div>
              </div>

              {{-- Bottom Save --}}
              <div class="col-12">
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                  <button type="button" class="btn btn-light" id="fcBtnResetBottom">
                    <i class="fa fa-rotate-left me-1"></i>Reset
                  </button>
                  <button type="submit" class="btn btn-primary" id="fcSaveBtn" style="display:none;">
                    <i class="fa fa-floppy-disk me-1"></i> Save
                  </button>
                </div>
                <div class="fc-help mt-2">
                  This form saves a single record. After the first save, future saves will update the same record.
                </div>
              </div>

            </div>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

{{-- Toasts --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="fcToastSuccess" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="fcToastSuccessText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="fcToastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="fcToastErrorText">Something went wrong</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
  if (window.__FOOTER_COMPONENTS_SINGLETON_INIT__) return;
  window.__FOOTER_COMPONENTS_SINGLETON_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=250) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  /* =========================
   * Endpoints
   * ========================= */
  const API = {
    footerIndex:   '/api/footer-components',
    footerStore:   '/api/footer-components',
    footerUpdate:  (uuid) => `/api/footer-components/${encodeURIComponent(uuid)}`,

    // Header menus
    headerMenuCandidates: [
      '/api/public/header-menus/tree/options?include_children=1&only_active=1',
      '/api/public/header-menus/tree/options?include_children=1',
      '/api/public/header-menus/tree?include_children=1&only_active=1',
      '/api/public/header-menus/tree?include_children=1',
      '/api/public/header-menus/tree?only_active=1',
      '/api/public/header-menus/tree',
    ],

    // Header components singleton (for Section 4 same-as-header)
    headerComponentsIndex: '/api/header-components',

    me: '/api/users/me',
  };

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

  function normalizeUrl(u){
    const s = (u || '').toString().trim();
    if (!s) return '';
    if (/^(data:|blob:|https?:\/\/)/i.test(s)) return s;
    if (s.startsWith('/')) return window.location.origin + s;
    return window.location.origin + '/' + s;
  }

  function safeArray(v){
    if (Array.isArray(v)) return v;
    if (typeof v === 'string'){
      try{ const j = JSON.parse(v); return Array.isArray(j) ? j : []; }catch(_){ return []; }
    }
    return [];
  }
  function safeObject(v){
    if (!v) return null;
    if (typeof v === 'object') return v;
    if (typeof v === 'string'){
      try{ return JSON.parse(v); }catch(_){ return null; }
    }
    return null;
  }
  function normalizeIdList(v){
    const arr = safeArray(v);
    const out = [];
    for (const x of arr){
      if (typeof x === 'number' && Number.isFinite(x)) out.push(x);
      else if (typeof x === 'string' && /^\d+$/.test(x.trim())) out.push(Number(x.trim()));
      else if (x && typeof x === 'object'){
        const id = x.id ?? x.page_id ?? x.menu_id ?? null;
        if (id !== null && /^\d+$/.test(String(id))) out.push(Number(id));
      }
    }
    return Array.from(new Set(out));
  }
  function safeJsonParse(s, fallback){
    try{ return JSON.parse(s); }catch(_){ return fallback; }
  }

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const loadingEl = $('fcLoading');
    const showLoading = (v) => { if (loadingEl) loadingEl.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('fcToastSuccess');
    const toastErrEl = $('fcToastError');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { const el=$('fcToastSuccessText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('fcToastErrorText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = () => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    });

    /* =========================
     * Permissions
     * ========================= */
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
        const res = await fetchWithTimeout(API.me, { headers: authHeaders() }, 8000);
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

    /* =========================
     * Refs
     * ========================= */
    const form = $('fcForm');
    const saveBtn = $('fcSaveBtn');
    const saveBtnTop = $('fcBtnSaveTop');

    const btnReload = $('fcBtnReload');
    const btnResetTop = $('fcBtnReset');
    const btnResetBottom = $('fcBtnResetBottom');

    const statusPill = $('fcStatusPill');
    const statusText = $('fcStatusText');
    const updatedText = $('fcUpdatedText');

    const fcUuid = $('fcUuid');
    const fcId = $('fcId');

    const metaInput = $('fcMetadata');

    // Section 1 / 3 / 5 link lists
    const s1Links = $('fcSection1Links');
    const s3Links = $('fcSection3Links');
    const bottomLinks = $('fcBottomLinks');

    const addS1Link = $('fcAddS1Link');
    const addS3Link = $('fcAddS3Link');
    const addBottomLink = $('fcAddBottomLink');

    // Section 2
    const menuBlocks = $('fcMenuBlocks');
    const addMenuBlock = $('fcAddMenuBlock');
    const blockCountText = $('fcBlockCountText');

    // ✅ Address text (NEW)
    const addressText = $('fcAddressText');

    // Section 4 toggle
    const sameAsHeader = $('fcSameAsHeader');
    const sameAsHeaderNote = $('fcSameAsHeaderNote');

    // Brand
    const brandTitle = $('fcBrandTitle');
    const brandLogoUrl = $('fcBrandLogoUrl');
    const brandLogoFile = $('fcBrandLogoFile');
    const brandLogoPreview = $('fcBrandLogoPreview');
    const brandLogoEmpty = $('fcBrandLogoEmpty');
    const openBrandLogo = $('fcOpenBrandLogo');
    const clearBrandLogo = $('fcClearBrandLogo');

    const rotateList = $('fcRotateList');
    const addRotate = $('fcAddRotate');

    const socialList = $('fcSocialList');
    const addSocial = $('fcAddSocial');

    const copyrightText = $('fcCopyright');

    const state = {
      currentItem: null,
      saving: false,

      menusLoaded: false,
      menusLoading: false,
      menuOptions: [],
      menuMap: new Map(),

      brandLogoBlob: null,

      headerSingleton: null,
      headerLoading: false,
      sameAsHeaderApplied: false,
    };

    function setBtnLoading(btn, loading){
      if (!btn) return;
      btn.disabled = !!loading;
      btn.classList.toggle('fc-btn-loading', !!loading);
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

    function setFormEnabled(on){
      const inputs = form?.querySelectorAll('input,textarea,button,select') || [];
      inputs.forEach(el => {
        if (el.id === 'fcBtnReload' || el.id === 'fcBtnReset' || el.id === 'fcBtnResetBottom') return;

        if (!on){
          const t = (el.type || '').toLowerCase();
          if (el.tagName === 'BUTTON' || t === 'file' || t === 'checkbox' || t === 'radio' || el.tagName === 'SELECT') {
            el.disabled = true;
          } else {
            el.readOnly = true;
          }
        } else {
          if (el.tagName === 'BUTTON' || el.tagName === 'SELECT') {
            el.disabled = false;
          } else {
            el.readOnly = false;
            el.disabled = false;
          }
        }
      });

      if (saveBtn) saveBtn.style.display = on ? '' : 'none';
      if (saveBtnTop) saveBtnTop.style.display = on ? '' : 'none';

      // keep Section 4 lock consistent
      applySameAsHeaderUI(!!sameAsHeader?.checked);
    }

    /* =========================
     * Link row (title + url)
     * ========================= */
    function repLinkRow(item={ title:'', url:'' }){
      const div = document.createElement('div');
      div.className = 'fc-row';

      const title = (item?.title ?? item?.label ?? '').toString().trim();
      const url = (item?.url ?? item?.link ?? '').toString().trim();

      div.innerHTML = `
        <div class="fc-row-top">
          <div class="fw-semibold small text-muted">Link</div>
          <button type="button" class="btn btn-light btn-sm" data-remove="1"><i class="fa fa-xmark"></i></button>
        </div>

        <div class="fc-row-grid-link">
          <div>
            <label class="form-label">Title</label>
            <input class="form-control" data-link-title maxlength="140" placeholder="e.g., About Us" value="${esc(title)}">
          </div>
          <div>
            <label class="form-label">URL</label>
            <input class="form-control" data-link-url maxlength="255" placeholder="/about or https://..." value="${esc(url)}">
          </div>
        </div>
      `;
      div.querySelector('[data-remove]')?.addEventListener('click', () => div.remove());
      return div;
    }

    /* =========================
     * Rotating / Social
     * ========================= */
    function repRotateRow(value=''){
      const div = document.createElement('div');
      div.className = 'fc-row';
      div.innerHTML = `
        <div class="fc-row-top">
          <div class="fw-semibold small text-muted">Line</div>
          <button type="button" class="btn btn-light btn-sm" data-remove="1"><i class="fa fa-xmark"></i></button>
        </div>
        <div class="mt-2">
          <input class="form-control" data-rotate="1" maxlength="255"
            placeholder="e.g., Admissions Open for 2026" value="${esc(value)}">
        </div>
      `;
      div.querySelector('[data-remove]')?.addEventListener('click', () => {
        if (sameAsHeader?.checked) return; // locked
        div.remove();
      });
      return div;
    }

    function repSocialRow(item={ platform:'', url:'', icon:'' }){
      const div = document.createElement('div');
      div.className = 'fc-row';

      const platform = (item?.platform ?? item?.name ?? '').toString().trim();
      const url = (item?.url ?? item?.link ?? '').toString().trim();
      const icon = (item?.icon ?? item?.icon_class ?? '').toString().trim();

      div.innerHTML = `
        <div class="fc-row-top">
          <div class="fw-semibold small text-muted">Social</div>
          <button type="button" class="btn btn-light btn-sm" data-remove="1"><i class="fa fa-xmark"></i></button>
        </div>

        <div class="fc-row-grid-3">
          <div>
            <label class="form-label">Platform</label>
            <input class="form-control" data-social-platform maxlength="80" placeholder="Facebook / LinkedIn" value="${esc(platform)}">
          </div>
          <div>
            <label class="form-label">URL</label>
            <input class="form-control" data-social-url maxlength="255" placeholder="https://..." value="${esc(url)}">
          </div>
          <div>
            <label class="form-label">Icon (optional)</label>
            <input class="form-control" data-social-icon maxlength="80" placeholder="fa-brands fa-linkedin" value="${esc(icon)}">
          </div>
        </div>
      `;
      div.querySelector('[data-remove]')?.addEventListener('click', () => div.remove());
      return div;
    }

    /* =========================
     * Header menus loading
     * ========================= */
    function extractMenuList(js){
      if (!js) return [];
      if (Array.isArray(js)) return js;
      if (Array.isArray(js.data)) return js.data;
      if (Array.isArray(js.items)) return js.items;
      if (Array.isArray(js.menus)) return js.menus;
      return [];
    }
    function getMenuChildren(menu){
      const m = menu || {};
      const kids =
        m.children ??
        m.child_menus ??        // ✅ very common
        m.childMenus ??         // ✅ camelCase
        m.childs ??
        m.submenus ??
        m.sub_menus ??
        m.items ??
        m.nodes ??
        m.menu_children ??
        m.children_json ??
        m.submenu_json ??
        [];

      // ✅ allow children to arrive as JSON string
      return safeArray(kids);
    }

    async function fetchMenus(force=false){
      if (state.menusLoading) return;
      if (state.menusLoaded && !force) return;

      state.menusLoading = true;
      try{
        let lastErr = null;
        let payload = null;

        for (const url of API.headerMenuCandidates){
          try{
            const res = await fetchWithTimeout(url, { headers: authHeaders() }, 15000);
            if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }
            const js = await res.json().catch(()=> ({}));
            if (!res.ok || js.success === false) throw new Error(js?.message || 'Bad response');
            const arr = extractMenuList(js);
            if (Array.isArray(arr) && arr.length){
              payload = arr;
              break;
            }
          }catch(ex){
            lastErr = ex;
          }
        }

        if (!payload){
          throw new Error(lastErr?.message || 'Header menus not found (check API endpoint)');
        }

        state.menuMap.clear();

        const normalized = payload.map(m => {
          const id = Number(m.id);
          const title = m.title ?? m.name ?? m.label ?? ('Menu #' + id);
          const childrenRaw = getMenuChildren(m);
          const children = childrenRaw.map(c => ({
            id: c.id ?? c.menu_id ?? c.page_id ?? c.child_id ?? null,
            title: c.title ?? c.name ?? c.label ?? c.menu_title ?? ('Item #' + (c.id ?? c.menu_id ?? c.page_id ?? '')),
            slug: c.slug ?? c.menu_slug ?? ''
          })).filter(x => x.id != null);

          return { id, title, children };
        }).filter(x => Number.isFinite(x.id));

        normalized.forEach(m => state.menuMap.set(Number(m.id), m));
        state.menuOptions = normalized;
        state.menusLoaded = true;
      }catch(_){
        state.menuOptions = [];
        state.menuMap.clear();
      }finally{
        state.menusLoading = false;
      }
    }

    /* =========================
     * Header components singleton (for same-as-header)
     * ========================= */
    async function fetchHeaderSingleton(force=false){
      if (state.headerLoading) return state.headerSingleton;
      if (state.headerSingleton && !force) return state.headerSingleton;

      state.headerLoading = true;
      try{
        const params = new URLSearchParams();
        params.set('per_page','1');
        params.set('page','1');
        params.set('sort','updated_at');
        params.set('direction','desc');

        const res = await fetchWithTimeout(`${API.headerComponentsIndex}?${params.toString()}`, {
          headers: authHeaders()
        }, 15000);

        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return null; }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to load Header Components');

        const arr = Array.isArray(js.data) ? js.data : [];
        state.headerSingleton = arr[0] || null;
        return state.headerSingleton;
      }catch(_){
        state.headerSingleton = null;
        return null;
      }finally{
        state.headerLoading = false;
      }
    }

    /* =========================
     * Section 2 blocks (UNIQUE MENUS)
     * ========================= */
    function getSelectedMenuIds(){
      const sels = Array.from(menuBlocks?.querySelectorAll('select[data-mb-menu]') || []);
      const ids = sels
        .map(s => (s.value || '').trim())
        .filter(v => /^\d+$/.test(v))
        .map(v => Number(v));
      return ids;
    }

    function isMenuUsedElsewhere(selectEl, menuIdStr){
      const v = (menuIdStr || '').trim();
      if (!/^\d+$/.test(v)) return false;
      const id = Number(v);
      const sels = Array.from(menuBlocks?.querySelectorAll('select[data-mb-menu]') || []);
      return sels.some(s => s !== selectEl && Number((s.value || 0)) === id);
    }

    function enforceUniqueSelections({silent=false} = {}){
      const sels = Array.from(menuBlocks?.querySelectorAll('select[data-mb-menu]') || []);
      const seen = new Set();
      let hadDup = false;

      sels.forEach(sel => {
        const v = (sel.value || '').trim();
        if (!/^\d+$/.test(v)) return;
        const id = Number(v);

        if (seen.has(id)){
          hadDup = true;
          // clear duplicate selection
          sel.value = '';

          const row = sel.closest('.fc-row');
          if (row){
            const childIdsHidden = row.querySelector('input[data-mb-childids]');
            if (childIdsHidden) childIdsHidden.value = JSON.stringify([]);
            const kidsWrap = row.querySelector('[data-mb-childrenwrap]');
            if (kidsWrap) kidsWrap.style.display = 'none';
            const kidsEl = row.querySelector('[data-mb-children]');
            if (kidsEl) kidsEl.innerHTML = '';
          }
        } else {
          seen.add(id);
        }
      });

      if (hadDup && !silent){
        err('Duplicate Header Menu detected. Each menu can be used only once. Duplicates were cleared.');
      }
      return hadDup;
    }

    function refreshMenuSelectOptions(){
      const sels = Array.from(menuBlocks?.querySelectorAll('select[data-mb-menu]') || []);
      const selectedAll = getSelectedMenuIds();
      const selectedSet = new Set(selectedAll);

      sels.forEach(sel => {
        const currentRaw = (sel.value || '').trim();
        const currentId = /^\d+$/.test(currentRaw) ? Number(currentRaw) : null;

        const exclude = new Set(selectedSet);
        if (currentId !== null) exclude.delete(currentId);

        // rebuild options (keep same <select> + listeners)
        sel.innerHTML = '';
        sel.appendChild(new Option('— Select —', ''));

        (state.menuOptions || []).forEach(m => {
          const mid = Number(m.id);
          if (exclude.has(mid)) return;
          const opt = new Option(m.title || ('Menu #' + mid), String(mid));
          if (currentId !== null && mid === currentId) opt.selected = true;
          sel.appendChild(opt);
        });

        // keep a missing selection visible (if menu removed from API)
        if (currentId !== null && !(state.menuOptions || []).some(m => Number(m.id) === currentId)){
          const opt = new Option(`(Missing menu #${currentId})`, String(currentId));
          opt.selected = true;
          sel.appendChild(opt);
        }
      });
    }

    function updateBlockCount(){
      const cnt = menuBlocks?.querySelectorAll('.fc-row')?.length || 0;
      if (blockCountText) blockCountText.textContent = `${cnt} / 4`;

      const used = new Set(getSelectedMenuIds());
      const total = (state.menuOptions || []).length;
      const noMoreMenus = total > 0 && used.size >= total;

      if (addMenuBlock){
        addMenuBlock.disabled = (cnt >= 4) || noMoreMenus;
        addMenuBlock.title = noMoreMenus ? 'All header menus are already used in blocks' : 'Add Block';
      }
    }

    function repMenuBlockRow(block={ title:'', header_menu_id:'', child_ids:[] }){
      const div = document.createElement('div');
      div.className = 'fc-row';

      const title = (block?.title ?? block?.header_title ?? '').toString().trim();
      const menuId = String(block?.header_menu_id ?? block?.menu_id ?? '').trim();
      const childIds = normalizeIdList(block?.child_ids ?? block?.submenu_ids ?? block?.children_ids ?? []);

      div.innerHTML = `
        <div class="fc-row-top">
          <div class="fw-semibold small text-muted">Menu Block</div>
          <button type="button" class="btn btn-light btn-sm" data-remove="1"><i class="fa fa-xmark"></i></button>
        </div>

        <div class="fc-row-grid-2">
          <div>
            <label class="form-label">Block Title</label>
            <input class="form-control" data-mb-title maxlength="255" placeholder="e.g., Department" value="${esc(title)}">
          </div>

          <div>
            <label class="form-label">Header Menu</label>
            <select class="form-select" data-mb-menu>
              <option value="">— Select —</option>
              ${state.menuOptions.map(m => `<option value="${esc(m.id)}" ${String(m.id)===menuId?'selected':''}>${esc(m.title || ('Menu #' + m.id))}</option>`).join('')}
            </select>
            <div class="fc-help mt-1 ${state.menuOptions.length ? '' : 'text-danger'}">
              ${state.menuOptions.length ? 'Select a menu to load submenus below.' : 'Header menu options not loaded. Check API endpoints.'}
            </div>
          </div>
        </div>

        <div class="mt-2" data-mb-childrenwrap style="display:none;">
          <div class="fc-help">Choose submenus to include:</div>
          <div class="fc-childlist" data-mb-children></div>
          <div class="fc-help mt-2" data-mb-nochildren style="display:none;">No child menus found for selected menu.</div>
        </div>

        <input type="hidden" data-mb-childids value="${esc(JSON.stringify(childIds))}">
      `;

      const removeBtn = div.querySelector('[data-remove]');
      const menuSel = div.querySelector('select[data-mb-menu]');
      const kidsWrap = div.querySelector('[data-mb-childrenwrap]');
      const kidsEl = div.querySelector('[data-mb-children]');
      const noKids = div.querySelector('[data-mb-nochildren]');
      const childIdsHidden = div.querySelector('input[data-mb-childids]');

      function renderKids(){
        const mid = (menuSel?.value || '').trim();
        const entry = mid ? state.menuMap.get(Number(mid)) : null;
        const kids = entry?.children || [];

        const selected = new Set(normalizeIdList(safeJsonParse(childIdsHidden.value || '[]', [])));

        if (!mid){
          kidsWrap && (kidsWrap.style.display = 'none');
          return;
        }
        kidsWrap && (kidsWrap.style.display = '');

        if (!kids.length){
          kidsEl && (kidsEl.innerHTML = '');
          noKids && (noKids.style.display = '');
          kidsWrap && (kidsWrap.style.display = '');
          return;
        }

        noKids && (noKids.style.display = 'none');
        kidsEl.innerHTML = kids.map(k => {
          const id = Number(k.id);
          const checked = selected.has(id);
          const t = k.title || ('Item #' + id);
          const s = k.slug ? ('/' + k.slug) : '';
          return `
            <label class="fc-childitem">
              <input type="checkbox" data-kid="${id}" ${checked?'checked':''}>
              <div style="min-width:0">
                <div class="fw-semibold" style="font-size:12.5px;line-height:1.2">${esc(t)}</div>
                <div class="small" style="color:var(--muted-color)">${esc(s)}</div>
              </div>
            </label>
          `;
        }).join('');

        kidsEl.querySelectorAll('input[type="checkbox"][data-kid]').forEach(cb => {
          cb.addEventListener('change', () => {
            const id = Number(cb.getAttribute('data-kid'));
            const set = new Set(normalizeIdList(safeJsonParse(childIdsHidden.value || '[]', [])));
            if (cb.checked) set.add(id); else set.delete(id);
            childIdsHidden.value = JSON.stringify(Array.from(set));
          });
        });
      }

      menuSel?.addEventListener('change', () => {
        const chosen = (menuSel.value || '').trim();

        // ✅ enforce unique header menus across blocks
        if (chosen && isMenuUsedElsewhere(menuSel, chosen)){
          menuSel.value = '';
          childIdsHidden.value = JSON.stringify([]);
          renderKids();
          refreshMenuSelectOptions();
          updateBlockCount();
          err('This Header Menu is already used in another block. Please choose a different menu.');
          return;
        }

        childIdsHidden.value = JSON.stringify([]);
        renderKids();

        // ✅ refresh dropdown options everywhere
        refreshMenuSelectOptions();
        updateBlockCount();
      });

      removeBtn?.addEventListener('click', () => {
        div.remove();
        refreshMenuSelectOptions();
        updateBlockCount();
      });

      if ((menuSel?.value || '').trim()) renderKids();

      return div;
    }

    /* =========================
     * Brand logo preview
     * ========================= */
    function revokeBrandBlob(){
      if (state.brandLogoBlob) { try{ URL.revokeObjectURL(state.brandLogoBlob); }catch(_){ } }
      state.brandLogoBlob = null;
    }

    function setBrandPreview(url){
      const u = (url || '').toString().trim();
      if (!u){
        brandLogoPreview && (brandLogoPreview.style.display='none');
        brandLogoPreview && brandLogoPreview.removeAttribute('src');
        brandLogoEmpty && (brandLogoEmpty.style.display='');
        if (openBrandLogo){ openBrandLogo.style.display='none'; openBrandLogo.onclick=null; }
        return;
      }
      const full = normalizeUrl(u);
      brandLogoPreview && (brandLogoPreview.style.display='');
      brandLogoPreview && (brandLogoPreview.src = full);
      brandLogoEmpty && (brandLogoEmpty.style.display='none');
      if (openBrandLogo){
        openBrandLogo.style.display='';
        openBrandLogo.onclick = () => window.open(full,'_blank','noopener');
      }
    }

    brandLogoFile?.addEventListener('change', () => {
      if (sameAsHeader?.checked) return; // locked
      const f = brandLogoFile.files?.[0] || null;
      if (!f) return;
      revokeBrandBlob();
      state.brandLogoBlob = URL.createObjectURL(f);
      setBrandPreview(state.brandLogoBlob);
    });

    brandLogoUrl?.addEventListener('input', debounce(() => {
      if (sameAsHeader?.checked) return; // locked
      if (brandLogoFile?.files?.length) return;
      setBrandPreview(brandLogoUrl.value);
    }, 120));

    clearBrandLogo?.addEventListener('click', () => {
      if (sameAsHeader?.checked) return; // locked
      revokeBrandBlob();
      if (brandLogoFile) brandLogoFile.value = '';
      if (brandLogoUrl) brandLogoUrl.value = '';
      setBrandPreview('');
    });

    /* =========================
     * Same-as-header UI + apply data
     * ========================= */
    function setSection4Disabled(disabled){
      // lock/unlock: Footer Title, Logo URL, Upload Logo, Rotating Texts (plus their buttons)
      if (brandTitle) brandTitle.disabled = !!disabled;
      if (brandLogoUrl) brandLogoUrl.disabled = !!disabled;
      if (brandLogoFile) brandLogoFile.disabled = !!disabled;

      if (clearBrandLogo) clearBrandLogo.disabled = !!disabled;

      if (addRotate) addRotate.disabled = !!disabled;

      // rotate inputs + remove buttons
      (rotateList?.querySelectorAll('input[data-rotate]') || []).forEach(i => i.disabled = !!disabled);
      (rotateList?.querySelectorAll('button[data-remove]') || []).forEach(b => b.disabled = !!disabled);

      // keep socials editable always
    }

    function applySameAsHeaderUI(on){
      if (sameAsHeaderNote) sameAsHeaderNote.style.display = on ? '' : 'none';
      setSection4Disabled(!!on);
    }

    async function applySameAsHeaderData(){
      const h = await fetchHeaderSingleton(false);
      if (!h) throw new Error('Header Components not found. Please save Header Components once first.');

      // Mapping:
      // primary_logo_url (header) -> brand_logo_url (footer)
      // header_text (header)      -> brand_title (footer)
      // rotating_text_json        -> rotating_text_json (footer)
      const logoUrl = (h.primary_logo_url || '').toString().trim();
      const logoFull = (h.primary_logo_full_url || logoUrl).toString().trim();
      const title = (h.header_text || '').toString().trim();
      const rot = safeArray(h.rotating_text_json);

      // set values
      brandTitle.value = title;
      brandLogoUrl.value = logoUrl;

      // clear local upload
      revokeBrandBlob();
      if (brandLogoFile) brandLogoFile.value = '';

      // preview
      setBrandPreview(logoFull || logoUrl || '');

      // replace rotate list
      rotateList.innerHTML = '';
      if (rot.length) rot.forEach(v => rotateList.appendChild(repRotateRow(String(v ?? ''))));
      else rotateList.appendChild(repRotateRow(''));

      state.sameAsHeaderApplied = true;
    }

    sameAsHeader?.addEventListener('change', async () => {
      const on = !!sameAsHeader.checked;
      applySameAsHeaderUI(on);

      if (!on) return;

      // When turning ON, fetch header data and apply (with loading)
      showLoading(true);
      try{
        await applySameAsHeaderData();
      }catch(ex){
        // revert toggle if failed
        sameAsHeader.checked = false;
        applySameAsHeaderUI(false);
        err(ex?.message || 'Failed to load Header Components');
      }finally{
        showLoading(false);
      }
    });

    /* =========================
     * Gather helpers
     * ========================= */
    function gatherLinkList(containerEl){
      const rows = Array.from(containerEl?.querySelectorAll('.fc-row') || []);
      return rows.map(r => {
        const title = (r.querySelector('input[data-link-title]')?.value || '').trim();
        const url = (r.querySelector('input[data-link-url]')?.value || '').trim();
        const obj = {};
        if (title) obj.title = title;
        if (url) obj.url = url;
        return obj;
      }).filter(x => x.title || x.url);
    }

    function gatherMenuBlocks(){
      const rows = Array.from(menuBlocks?.querySelectorAll('.fc-row') || []);
      return rows.map(r => {
        const title = (r.querySelector('input[data-mb-title]')?.value || '').trim();
        const menuId = (r.querySelector('select[data-mb-menu]')?.value || '').trim();
        const childIdsRaw = r.querySelector('input[data-mb-childids]')?.value || '[]';
        const childIds = normalizeIdList(safeJsonParse(childIdsRaw, []));

        const obj = {};
        if (title) obj.title = title;
        if (menuId) obj.header_menu_id = Number(menuId);
        obj.child_ids = childIds;

        return obj;
      }).filter(x => x.title || x.header_menu_id || (x.child_ids && x.child_ids.length));
    }

    function gatherRotate(){
      return Array.from(rotateList?.querySelectorAll('input[data-rotate]') || [])
        .map(i => (i.value || '').trim())
        .filter(Boolean);
    }

    function gatherSocial(){
      const rows = Array.from(socialList?.querySelectorAll('.fc-row') || []);
      return rows.map(r => {
        const platform = (r.querySelector('input[data-social-platform]')?.value || '').trim();
        const url = (r.querySelector('input[data-social-url]')?.value || '').trim();
        const icon = (r.querySelector('input[data-social-icon]')?.value || '').trim();
        const obj = {};
        if (platform) obj.platform = platform;
        if (url) obj.url = url;
        if (icon) obj.icon = icon;
        return obj;
      }).filter(x => x.platform || x.url || x.icon);
    }

    /* =========================
     * Singleton fetch + fill
     * ========================= */
    async function fetchFooterSingleton(){
      const params = new URLSearchParams();
      params.set('per_page','1');
      params.set('page','1');
      params.set('sort','updated_at');
      params.set('direction','desc');

      const res = await fetchWithTimeout(`${API.footerIndex}?${params.toString()}`, { headers: authHeaders() }, 15000);
      if (res.status === 401 || res.status === 403) { window.location.href = '/'; return null; }

      const js = await res.json().catch(()=> ({}));
      if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to load');

      // ✅ handle both non-paginated and paginated shapes
      const arr =
        (Array.isArray(js.data) ? js.data :
        (Array.isArray(js?.data?.data) ? js.data.data :
        (Array.isArray(js.items) ? js.items :
        (Array.isArray(js?.items?.data) ? js.items.data : []))));

      return arr[0] || null;
    }

    function resetToBlank(){
      revokeBrandBlob();
      form?.reset();

      fcUuid.value = '';
      fcId.value = '';
      state.currentItem = null;
      state.sameAsHeaderApplied = false;

      // default: toggle OFF
      if (sameAsHeader) sameAsHeader.checked = false;
      applySameAsHeaderUI(false);

      // Section 1
      s1Links.innerHTML = '';
      s1Links.appendChild(repLinkRow({ title:'About', url:'/about' }));

      // Section 2
      menuBlocks.innerHTML = '';
      menuBlocks.appendChild(repMenuBlockRow({}));
      enforceUniqueSelections({silent:true});
      refreshMenuSelectOptions();
      updateBlockCount();

      // Section 3
      s3Links.innerHTML = '';
      s3Links.appendChild(repLinkRow({ title:'Contact', url:'/contact' }));

      // ✅ Address
      if (addressText) addressText.value = '';

      // Section 4
      setBrandPreview('');
      rotateList.innerHTML = '';
      rotateList.appendChild(repRotateRow(''));

      socialList.innerHTML = '';
      socialList.appendChild(repSocialRow({ platform:'Facebook', url:'', icon:'fa-brands fa-facebook' }));

      // Section 5
      bottomLinks.innerHTML = '';
      bottomLinks.appendChild(repLinkRow({ title:'Privacy Policy', url:'/privacy' }));
      copyrightText.value = '';

      setUpdated('—');
      setStatus('warn', 'Not saved yet');

      setFormEnabled(!!canCreate);
    }

    function fillFromItem(item){
      state.currentItem = item || null;
      state.sameAsHeaderApplied = false;

      fcUuid.value = item?.uuid || '';
      fcId.value = item?.id || '';

      function toBool(v){
        if (v === true || v === 1) return true;
        if (v === false || v === 0 || v == null) return false;
        if (typeof v === 'string'){
          const s = v.trim().toLowerCase();
          return (s === '1' || s === 'true' || s === 'yes' || s === 'on');
        }
        return false;
      }

      // toggle from API (support multiple possible keys)
      const flag = toBool(
        item?.same_as_header ??
        item?.section_4_same_as_header ??
        item?.section4_same_as_header ??
        item?.is_same_as_header
      );
      if (sameAsHeader) sameAsHeader.checked = flag;

      // Section 1/3 links
      const s1 = safeArray(item?.section1_menu_json ?? item?.section_1_links_json ?? item?.section1_links_json ?? item?.links_section_1_json ?? []);
      s1Links.innerHTML = '';
      if (s1.length) s1.forEach(x => s1Links.appendChild(repLinkRow(x)));
      else s1Links.appendChild(repLinkRow({}));

      const s3 = safeArray(item?.section3_menu_json ?? item?.section_3_links_json ?? item?.section3_links_json ?? item?.links_section_3_json ?? []);
      s3Links.innerHTML = '';
      if (s3.length) s3.forEach(x => s3Links.appendChild(repLinkRow(x)));
      else s3Links.appendChild(repLinkRow({}));

      // Section 2 blocks
      const blocks = safeArray(item?.section2_header_menu_json ?? item?.section_2_menu_blocks_json ?? item?.section2_menu_blocks_json ?? item?.menu_blocks_json ?? []);
      menuBlocks.innerHTML = '';
      if (blocks.length) blocks.slice(0,4).forEach(b => menuBlocks.appendChild(repMenuBlockRow(b)));
      else menuBlocks.appendChild(repMenuBlockRow({}));

      // ✅ enforce unique menus + refresh options
      enforceUniqueSelections({silent:true});
      refreshMenuSelectOptions();
      updateBlockCount();

      // metadata
      const metaObj = safeObject(item?.metadata ?? item?.metadata_json);
      metaInput.value = metaObj ? JSON.stringify(metaObj, null, 2) : '';

      // ✅ Address
      if (addressText){
        addressText.value = (item?.address_text ?? item?.address ?? '').toString();
      }

      // brand values from footer record (may be overridden if same-as-header ON)
      brandTitle.value = item?.section_4_title ?? item?.brand_title ?? item?.footer_title ?? '';
      brandLogoUrl.value = item?.section_4_logo_url ?? item?.brand_logo_url ?? item?.footer_logo_url ?? '';
      if (brandLogoFile) brandLogoFile.value = '';

      const logoFull =
        item?.section_4_logo_full_url ??
        item?.brand_logo_full_url ??
        item?.footer_logo_full_url ??
        item?.section_4_logo_url ??
        item?.brand_logo_url ??
        item?.footer_logo_url ??
        '';
      setBrandPreview(logoFull);

      rotateList.innerHTML = '';
      const rot = safeArray(item?.rotating_text_json ?? item?.section_4_rotating_text_json ?? item?.brand_rotating_text_json ?? item?.footer_rotating_text_json ?? []);
      if (rot.length) rot.forEach(v => rotateList.appendChild(repRotateRow(String(v ?? ''))));
      else rotateList.appendChild(repRotateRow(''));

      socialList.innerHTML = '';
      const socials = safeArray(item?.social_links_json ?? item?.section_4_social_links_json ?? item?.brand_social_links_json ?? item?.footer_social_links_json ?? []);
      if (socials.length) socials.forEach(s => socialList.appendChild(repSocialRow(s)));
      else socialList.appendChild(repSocialRow({}));

      // section 5 links
      bottomLinks.innerHTML = '';
      const bl = safeArray(item?.section5_menu_json ?? item?.section_5_links_json ?? item?.bottom_links_json ?? item?.footer_bottom_links_json ?? []);
      if (bl.length) bl.forEach(x => bottomLinks.appendChild(repLinkRow(x)));
      else bottomLinks.appendChild(repLinkRow({}));

      copyrightText.value =
        item?.section_5_copyright_text ??
        item?.copyright_text ??
        item?.footer_copyright_text ??
        '';

      setUpdated(item?.updated_at || '—');
      setStatus('ok', 'Loaded');

      setFormEnabled(!!canEdit);

      // if flag ON, apply header data now (and lock)
      applySameAsHeaderUI(!!sameAsHeader?.checked);
    }

    function resetToCurrent(){
      revokeBrandBlob();
      if (state.currentItem) fillFromItem(state.currentItem);
      else resetToBlank();
    }

    async function reload(){
      showLoading(true);
      try{
        setStatus('warn','Loading…');

        await fetchMenus(false);

        const item = await fetchFooterSingleton();
        if (item) fillFromItem(item);
        else resetToBlank();

        // if same-as-header is ON after fill, fetch+apply header data (this ensures stored copy stays in sync)
        if (sameAsHeader?.checked){
          await applySameAsHeaderData();
          applySameAsHeaderUI(true);
        }

        // ✅ ensure unique menus + dropdown refresh after menus load
        enforceUniqueSelections({silent:true});
        refreshMenuSelectOptions();
        updateBlockCount();
      }catch(ex){
        setStatus('err','Load failed');
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed'));
      }finally{
        showLoading(false);
      }
    }

    /* =========================
     * Bind buttons
     * ========================= */
    btnReload?.addEventListener('click', reload);
    btnResetTop?.addEventListener('click', resetToCurrent);
    btnResetBottom?.addEventListener('click', resetToCurrent);
    saveBtnTop?.addEventListener('click', () => form?.requestSubmit?.() || saveBtn?.click());

    addS1Link?.addEventListener('click', () => s1Links.appendChild(repLinkRow({})));
    addS3Link?.addEventListener('click', () => s3Links.appendChild(repLinkRow({})));
    addBottomLink?.addEventListener('click', () => bottomLinks.appendChild(repLinkRow({})));

    addMenuBlock?.addEventListener('click', () => {
      const cnt = menuBlocks?.querySelectorAll('.fc-row')?.length || 0;
      if (cnt >= 4) return;

      // ✅ If menus are loaded and all are already used, do nothing
      const used = new Set(getSelectedMenuIds());
      const total = (state.menuOptions || []).length;
      if (total > 0 && used.size >= total){
        err('All header menus are already used. Remove a block or change a selection to add another.');
        return;
      }

      menuBlocks.appendChild(repMenuBlockRow({}));

      // ✅ update options everywhere so used menus disappear
      enforceUniqueSelections({silent:true});
      refreshMenuSelectOptions();
      updateBlockCount();
    });

    addRotate?.addEventListener('click', () => {
      if (sameAsHeader?.checked) return; // locked
      rotateList.appendChild(repRotateRow(''));
    });

    addSocial?.addEventListener('click', () => socialList.appendChild(repSocialRow({})));

    /* =========================
     * Submit
     * ========================= */
    form?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (state.saving) return;

      const isEdit = !!fcUuid.value;
      if (isEdit && !canEdit) return;
      if (!isEdit && !canCreate) return;

      // ✅ final uniqueness guard
      if (enforceUniqueSelections({silent:true})){
        refreshMenuSelectOptions();
        updateBlockCount();
        err('Please ensure each Menu Block uses a unique Header Menu.');
        return;
      }

      showLoading(true);

      try{
        // If Same-as-header ON: refresh header and force mapped values before saving
        if (sameAsHeader?.checked){
          await applySameAsHeaderData(); // ensures it stores header values
          applySameAsHeaderUI(true);
        }

        const fd = new FormData();

        // Section 1/3: title+url json arrays
        fd.append('section1_menu_json', JSON.stringify(gatherLinkList(s1Links)));
        fd.append('section3_menu_json', JSON.stringify(gatherLinkList(s3Links)));

        // Section 2: max 4 blocks
        fd.append('section2_header_menu_json', JSON.stringify(gatherMenuBlocks().slice(0,4)));

        // ✅ Address text
        fd.append('address_text', (addressText?.value || '').toString().trim());

        // Metadata
        const metaRaw = (metaInput.value || '').trim();
        if (metaRaw){
          try{
            const metaObj = JSON.parse(metaRaw);
            fd.append('metadata', JSON.stringify(metaObj));
          }catch(_){
            err('Metadata must be valid JSON');
            metaInput.focus();
            return;
          }
        }

        fd.append('same_as_header', sameAsHeader?.checked ? '1' : '0');

        // Section 4 (Brand)
        const bt = (brandTitle.value || '').trim();
        if (bt) fd.append('brand_title', bt);

        const logoUrl = (brandLogoUrl.value || '').trim();
        const logoFile = (sameAsHeader?.checked ? null : (brandLogoFile?.files?.[0] || null));

        // store URL/path (header uses primary_logo_url -> footer brand_logo_url)
        if (logoUrl) fd.append('brand_logo_url', logoUrl);

        // upload only if not same-as-header
        if (logoFile) fd.append('brand_logo', logoFile);

        // rotating texts (header rotating_text_json -> footer rotating_text_json)
        fd.append('rotating_text_json', JSON.stringify(gatherRotate()));

        // socials always editable
        fd.append('social_links_json', JSON.stringify(gatherSocial()));

        // Section 5: links only
        fd.append('section5_menu_json', JSON.stringify(gatherLinkList(bottomLinks)));
        const cp = (copyrightText.value || '').trim();
        if (!cp){
          err('Copyright Text is required');
          return;
        }
        fd.append('copyright_text', cp);

        const url = isEdit ? API.footerUpdate(fcUuid.value) : API.footerStore;
        if (isEdit) fd.append('_method','PUT');

        state.saving = true;
        setBtnLoading(saveBtn, true);
        setBtnLoading(saveBtnTop, true);

        const res = await fetchWithTimeout(url, {
          method: 'POST',
          headers: authHeaders(),
          body: fd
        }, 25000);

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

    /* =========================
     * Init
     * ========================= */
    (async () => {
      showLoading(true);
      try{
        await fetchMe();
        await fetchMenus(false);
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
