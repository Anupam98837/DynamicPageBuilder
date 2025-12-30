{{-- resources/views/modules/home/manageHeaderComponents.blade.php --}}
@section('title','Header Components')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
 * Header Components (Admin)
 * Single-record Form
 * ========================= */

.hc-wrap{max-width:1200px;margin:16px auto 44px;padding:0 6px;overflow:visible}

/* Page shell */
.hc-shell{
  background:var(--surface);
  border:1px solid var(--line-strong);
  border-radius:18px;
  box-shadow:var(--shadow-2);
  overflow:hidden;
}
.hc-shell-h{
  display:flex;align-items:flex-start;justify-content:space-between;gap:10px;
  padding:14px 14px;
  border-bottom:1px solid var(--line-soft);
}
.hc-shell-title{display:flex;align-items:center;gap:10px}
.hc-shell-title i{opacity:.85}
.hc-shell-sub{color:var(--muted-color);font-size:12.5px;margin-top:4px}
.hc-shell-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}

/* Status pill */
.hc-pill{
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 10px;
  border-radius:999px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  font-size:12.5px;
  color:var(--ink);
}
.hc-pill .dot{width:8px;height:8px;border-radius:999px;background:var(--muted-color)}
.hc-pill.ok .dot{background:var(--success-color)}
.hc-pill.warn .dot{background:var(--warning-color, #f59e0b)}
.hc-pill.err .dot{background:var(--danger-color)}

/* Body */
.hc-shell-b{padding:14px 14px}

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

.hc-help{font-size:12px;color:var(--muted-color)}

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
.img-preview{
  width:100%;
  max-height:220px;
  object-fit:cover;
  border-radius:12px;
  border:1px solid var(--line-soft);
  background:#fff;
}

/* Repeaters */
.repeater{display:flex;flex-direction:column;gap:10px}
.rep-row{
  border:1px solid var(--line-soft);
  border-radius:14px;
  padding:10px;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.rep-row .rep-top{display:flex;gap:10px;align-items:center;justify-content:space-between}

/* ✅ Cleaner alignment for Affiliation rows */
.rep-row .rep-grid-file{
  display:grid;
  grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr) 120px;
  gap:12px;
  margin-top:10px;
  align-items:center; /* ✅ align controls nicely */
}
.rep-row .rep-grid-file > div{
  min-width:0;
  display:flex;
  flex-direction:column;
}
.rep-row .rep-grid-file .form-label{margin-bottom:6px}

/* ✅ Make inputs consistent + NOT oversized */
.rep-row .rep-grid-file .form-control{border-radius:14px}

/* file input */
.rep-row .rep-grid-file input[type="file"].form-control{
  padding:6px 10px;
  min-height:46px;     /* ✅ reduced */
  font-size:14px;
}

/* caption input */
.rep-row .rep-grid-file input[type="text"].form-control{
  min-height:46px;     /* ✅ match file input */
  padding:6px 10px;
}

/* file selector button sizing */
.rep-row .rep-grid-file input[type="file"].form-control::file-selector-button{
  padding:6px 10px;
  border-radius:12px;
}

/* ✅ Per-row preview (small + aligned) */
.rep-preview-col{gap:0}
.rep-thumb-wrap{
  display:flex;
  align-items:center;
  justify-content:flex-end;
  width:100%;
}
.rep-thumb{
  width:100%;
  height:56px;         /* ✅ smaller */
  object-fit:contain;
  border-radius:12px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, #ffffff 90%, transparent);
  padding:6px;
}
html.theme-dark .rep-thumb{
  background:color-mix(in oklab, var(--surface) 85%, transparent);
}

/* =========================
 * ✅ Partner Logos (Recruiters Picker)
 * ========================= */
.hc-rec-toolbar{
  display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;
  margin-bottom:10px;
}
.hc-rec-toolbar .form-control{border-radius:999px}
.hc-rec-meta{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.hc-rec-count{
  display:inline-flex;align-items:center;gap:8px;
  padding:6px 10px;border-radius:999px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  font-size:12px;
  color:var(--ink);
}
.hc-rec-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill, minmax(145px, 1fr));
  gap:12px;
}
.hc-rec-card{
  border:1px solid var(--line-strong);
  border-radius:14px;
  background:var(--surface);
  box-shadow:var(--shadow-1);
  overflow:hidden;
  cursor:pointer;
  display:flex;
  flex-direction:column;
  min-height:146px;
  transition:transform .12s ease, box-shadow .12s ease, border-color .12s ease;
}
.hc-rec-card:hover{
  transform:translateY(-1px);
  box-shadow:var(--shadow-2);
}
.hc-rec-card.is-checked{
  border-color:color-mix(in oklab, var(--primary-color) 70%, var(--line-strong));
  box-shadow:0 8px 18px rgba(0,0,0,.10);
}
.hc-rec-top{
  display:flex;align-items:center;justify-content:space-between;
  padding:8px 10px;
  border-bottom:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.hc-rec-check{
  display:flex;align-items:center;gap:8px;
  font-size:12.5px;color:var(--muted-color);
}
.hc-rec-check input{transform:scale(1.05)}
.hc-rec-body{padding:10px;display:flex;flex-direction:column;gap:10px}
.hc-rec-img{
  width:100%;
  height:68px;
  object-fit:contain;
  border-radius:12px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, #ffffff 90%, transparent);
  padding:6px;
}
html.theme-dark .hc-rec-img{
  background:color-mix(in oklab, var(--surface) 85%, transparent);
}
.hc-rec-title{
  font-weight:700;
  font-size:12.5px;
  color:var(--ink);
  line-height:1.2;
  min-height:32px;
  display:-webkit-box;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
  overflow:hidden;
}

/* Responsive */
@media (max-width: 768px){
  .hc-shell-h{flex-direction:column}
  .hc-shell-actions{justify-content:flex-start}

  .rep-row .rep-grid-file{grid-template-columns:1fr}
  .rep-thumb-wrap{justify-content:flex-start}
  .rep-preview-col{max-width:220px}
}
</style>
@endpush

@section('content')
<div class="hc-wrap">

  {{-- Loading Overlay --}}
  <div id="hcLoading" class="loading-overlay" style="display:none;">
    <div class="loading-spinner">
      <div class="spinner"></div>
      <div class="small">Loading…</div>
    </div>
  </div>

  <div class="hc-shell">
    <div class="hc-shell-h">
      <div>
        <div class="hc-shell-title">
          <i class="fa-solid fa-rectangle-list"></i>
          <div>
            <div class="fw-semibold">Header Component Settings</div>
            <div class="hc-shell-sub">
              This module stores a <b>single</b> header configuration. Save once, then update the same record.
            </div>
          </div>
        </div>

        <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
          <span id="hcStatusPill" class="hc-pill warn">
            <span class="dot"></span>
            <span id="hcStatusText">Not loaded</span>
          </span>
          <span class="hc-pill">
            <i class="fa-regular fa-clock"></i>
            <span id="hcUpdatedText">Updated: —</span>
          </span>
        </div>
      </div>

      <div class="hc-shell-actions">
        <button type="button" class="btn btn-light" id="hcBtnReload">
          <i class="fa fa-arrows-rotate me-1"></i>Reload
        </button>
        <button type="button" class="btn btn-outline-primary" id="hcBtnReset">
          <i class="fa fa-rotate-left me-1"></i>Reset
        </button>
        <button type="button" class="btn btn-primary" id="hcBtnSaveTop" style="display:none;">
          <i class="fa fa-floppy-disk me-1"></i>Save
        </button>
      </div>
    </div>

    <div class="hc-shell-b">
      <form id="hcForm" autocomplete="off">
        <input type="hidden" id="hcUuid">
        <input type="hidden" id="hcId">

        <div class="row g-3">
          {{-- LEFT --}}
          <div class="col-lg-5">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Header Text <span class="text-danger">*</span></label>
                <input id="hcHeaderText" class="form-control" maxlength="255" required placeholder="e.g., W3Techiez">
              </div>

              <div class="col-12">
                <label class="form-label">Slug (optional)</label>
                <input id="hcSlug" class="form-control" maxlength="160" placeholder="w3techiez-header">
                <div class="hc-help">Auto-generated from header text until you edit slug manually.</div>
              </div>

              <div class="col-12">
                <label class="form-label">Admission Link URL (optional)</label>
                <input id="hcAdmissionLink" class="form-control" maxlength="255" placeholder="https://example.com/admission">
                <div class="hc-help">Leave empty if you don’t want the admission button/link.</div>
              </div>

              <div class="col-12">
                <div class="box">
                  <div class="box-h">
                    <div class="fw-semibold"><i class="fa-solid fa-rotate me-2"></i>Rotating Text Lines</div>
                    <button type="button" class="btn btn-light btn-sm" id="hcAddRotateLine">
                      <i class="fa fa-plus me-1"></i>Add Line
                    </button>
                  </div>
                  <div class="box-b">
                    <div id="hcRotateList" class="repeater"></div>
                    <div class="hc-help mt-2">Saved as <code>rotating_text_json</code> (array).</div>
                  </div>
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">Metadata (optional JSON)</label>
                <textarea id="hcMetadata" class="form-control" rows="6" placeholder='{"theme":"dark","note":"optional"}'></textarea>
                <div class="hc-help">Must be valid JSON if provided. Leave empty to store NULL.</div>
              </div>

              {{-- Partner Logos (Recruiters picker) --}}
              <div class="col-12">
                <div class="box">
                  <div class="box-h">
                    <div class="fw-semibold"><i class="fa-solid fa-handshake me-2"></i>Partner Logos (Recruiters)</div>
                    <button type="button" class="btn btn-light btn-sm" id="hcReloadRecruiters">
                      <i class="fa fa-arrows-rotate me-1"></i>Reload
                    </button>
                  </div>
                  <div class="box-b">
                    <div class="hc-rec-toolbar">
                      <input id="hcPartnerSearch" class="form-control" placeholder="Search recruiters…">
                      <div class="hc-rec-meta">
                        <span class="hc-rec-count">
                          <i class="fa-regular fa-square-check"></i>
                          <span id="hcPartnerSelectedCount">Selected: 0</span>
                        </span>
                      </div>
                    </div>

                    <div id="hcRecruitersHint" class="hc-help mb-2">
                      Select recruiters to show as partner logos. Saved as <code>partner_logos_json</code> = <code>recruiters.id[]</code>.
                    </div>

                    <div id="hcRecruitersError" class="text-danger small" style="display:none;"></div>
                    <div id="hcPartnerRecruitersGrid" class="hc-rec-grid"></div>

                    <div class="hc-help mt-2">
                      This section reads logos from the <code>recruiters</code> table (by <code>logo_url</code>). No uploads here.
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

          {{-- RIGHT --}}
          <div class="col-lg-7">
            <div class="row g-3">

              {{-- Primary Logo --}}
              <div class="col-12">
                <div class="box">
                  <div class="box-h">
                    <div class="fw-semibold"><i class="fa fa-image me-2"></i>Primary Logo <span class="text-danger">*</span></div>
                    <button type="button" class="btn btn-light btn-sm" id="hcOpenPrimary" style="display:none;">
                      <i class="fa fa-up-right-from-square me-1"></i>Open
                    </button>
                  </div>
                  <div class="box-b">
                    <div class="row g-2">
                      <div class="col-md-8">
                        <label class="form-label">URL / Path</label>
                        <input id="hcPrimaryUrl" class="form-control" maxlength="255" placeholder="assets/... or https://...">
                        <div class="hc-help">Upload will override this field.</div>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Upload</label>
                        <input id="hcPrimaryFile" type="file" class="form-control" accept="image/*">
                      </div>
                      <div class="col-12">
                        <img id="hcPrimaryPreview" class="img-preview" style="display:none;" alt="Primary logo preview">
                        <div id="hcPrimaryEmpty" class="text-muted small" style="padding:12px;border:1px dashed var(--line-soft);border-radius:12px;">
                          No primary logo selected.
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Secondary Logo + Badge --}}
              <div class="col-12">
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="box">
                      <div class="box-h">
                        <div class="fw-semibold"><i class="fa fa-image me-2"></i>Secondary Logo <span class="text-danger">*</span></div>
                        <button type="button" class="btn btn-light btn-sm" id="hcOpenSecondary" style="display:none;">
                          <i class="fa fa-up-right-from-square me-1"></i>Open
                        </button>
                      </div>
                      <div class="box-b">
                        <label class="form-label">URL / Path</label>
                        <input id="hcSecondaryUrl" class="form-control" maxlength="255" placeholder="assets/... or https://...">
                        <div class="hc-help mt-1">Upload overrides.</div>
                        <div class="mt-2">
                          <input id="hcSecondaryFile" type="file" class="form-control" accept="image/*">
                        </div>
                        <div class="mt-2">
                          <img id="hcSecondaryPreview" class="img-preview" style="display:none;" alt="Secondary logo preview">
                          <div id="hcSecondaryEmpty" class="text-muted small" style="padding:12px;border:1px dashed var(--line-soft);border-radius:12px;">
                            No secondary logo selected.
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="box">
                      <div class="box-h">
                        <div class="fw-semibold"><i class="fa fa-certificate me-2"></i>Admission Badge <span class="text-danger">*</span></div>
                        <button type="button" class="btn btn-light btn-sm" id="hcOpenBadge" style="display:none;">
                          <i class="fa fa-up-right-from-square me-1"></i>Open
                        </button>
                      </div>
                      <div class="box-b">
                        <label class="form-label">URL / Path</label>
                        <input id="hcBadgeUrl" class="form-control" maxlength="255" placeholder="assets/... or https://...">
                        <div class="hc-help mt-1">Upload overrides.</div>
                        <div class="mt-2">
                          <input id="hcBadgeFile" type="file" class="form-control" accept="image/*">
                        </div>
                        <div class="mt-2">
                          <img id="hcBadgePreview" class="img-preview" style="display:none;" alt="Badge preview">
                          <div id="hcBadgeEmpty" class="text-muted small" style="padding:12px;border:1px dashed var(--line-soft);border-radius:12px;">
                            No badge selected.
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Affiliation logos (file rows, per-row preview, NO text under file input) --}}
              <div class="col-12">
                <div class="box">
                  <div class="box-h">
                    <div class="fw-semibold"><i class="fa-solid fa-award me-2"></i>Affiliation Logos</div>
                    <button type="button" class="btn btn-light btn-sm" id="hcAddAffil">
                      <i class="fa fa-plus me-1"></i>Add
                    </button>
                  </div>
                  <div class="box-b">
                    <div id="hcAffilList" class="repeater"></div>
                    <div class="hc-help mt-2">
                      Existing logos are preserved via <code>affiliation_logos_json</code>.
                      New uploads are sent as <code>affiliation_logos[]</code>.
                    </div>
                  </div>
                </div>
              </div>

              {{-- Bottom Save --}}
              <div class="col-12">
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                  <button type="button" class="btn btn-light" id="hcBtnResetBottom">
                    <i class="fa fa-rotate-left me-1"></i>Reset
                  </button>
                  <button type="submit" class="btn btn-primary" id="hcSaveBtn" style="display:none;">
                    <i class="fa fa-floppy-disk me-1"></i> Save
                  </button>
                </div>
                <div class="hc-help mt-2">
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
  <div id="hcToastSuccess" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="hcToastSuccessText">Done</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
  <div id="hcToastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body" id="hcToastErrorText">Something went wrong</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
  if (window.__HEADER_COMPONENTS_SINGLETON_INIT__) return;
  window.__HEADER_COMPONENTS_SINGLETON_INIT__ = true;

  const $ = (id) => document.getElementById(id);
  const debounce = (fn, ms=300) => { let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  function slugify(s){
    return (s || '')
      .toString()
      .normalize('NFKD').replace(/[\u0300-\u036f]/g,'')
      .trim().toLowerCase()
      .replace(/['"`]/g,'')
      .replace(/[^a-z0-9]+/g,'-')
      .replace(/-+/g,'-')
      .replace(/^-|-$/g,'');
  }

  function normalizeUrl(u){
    const s = (u || '').toString().trim();
    if (!s) return '';
    if (/^(data:|blob:|https?:\/\/)/i.test(s)) return s;
    if (s.startsWith('/')) return window.location.origin + s;
    return window.location.origin + '/' + s;
  }

  async function fetchWithTimeout(url, opts={}, ms=15000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try{ return await fetch(url, { ...opts, signal: ctrl.signal }); }
    finally{ clearTimeout(t); }
  }

  const PLACEHOLDER_SVG = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
    <svg xmlns="http://www.w3.org/2000/svg" width="420" height="220" viewBox="0 0 420 220">
      <rect width="420" height="220" fill="#f2f4f7"/>
      <rect x="22" y="22" width="376" height="176" rx="18" fill="#ffffff" stroke="#e5e7eb"/>
      <g fill="#9aa3af">
        <path d="M130 142h160v14H130z"/>
        <path d="M150 112h120v12H150z"/>
        <path d="M183 66h54c10 0 18 8 18 18v14h-90V84c0-10 8-18 18-18z"/>
      </g>
      <text x="210" y="178" text-anchor="middle" font-family="Arial" font-size="14" fill="#6b7280">
        No Logo
      </text>
    </svg>
  `);

  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const loadingEl = $('hcLoading');
    const showLoading = (v) => { if (loadingEl) loadingEl.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('hcToastSuccess');
    const toastErrEl = $('hcToastError');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { const el=$('hcToastSuccessText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('hcToastErrorText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = () => ({
      'Authorization': 'Bearer ' + token,
      'Accept': 'application/json'
    });

    // Permissions
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

    // Refs
    const form = $('hcForm');
    const saveBtn = $('hcSaveBtn');
    const saveBtnTop = $('hcBtnSaveTop');
    const btnReload = $('hcBtnReload');
    const btnResetTop = $('hcBtnReset');
    const btnResetBottom = $('hcBtnResetBottom');

    const statusPill = $('hcStatusPill');
    const statusText = $('hcStatusText');
    const updatedText = $('hcUpdatedText');

    const hcUuid = $('hcUuid');
    const hcId = $('hcId');

    const headerText = $('hcHeaderText');
    const slugInput = $('hcSlug');
    const admissionLink = $('hcAdmissionLink');
    const metadataInput = $('hcMetadata');

    const rotateList = $('hcRotateList');
    const addRotateLine = $('hcAddRotateLine');

    const primaryUrl = $('hcPrimaryUrl');
    const primaryFile = $('hcPrimaryFile');
    const primaryPreview = $('hcPrimaryPreview');
    const primaryEmpty = $('hcPrimaryEmpty');
    const openPrimary = $('hcOpenPrimary');

    const secondaryUrl = $('hcSecondaryUrl');
    const secondaryFile = $('hcSecondaryFile');
    const secondaryPreview = $('hcSecondaryPreview');
    const secondaryEmpty = $('hcSecondaryEmpty');
    const openSecondary = $('hcOpenSecondary');

    const badgeUrl = $('hcBadgeUrl');
    const badgeFile = $('hcBadgeFile');
    const badgePreview = $('hcBadgePreview');
    const badgeEmpty = $('hcBadgeEmpty');
    const openBadge = $('hcOpenBadge');

    // Recruiters picker refs
    const recruitersGrid = $('hcPartnerRecruitersGrid');
    const recruitersErr  = $('hcRecruitersError');
    const recruitersReloadBtn = $('hcReloadRecruiters');
    const partnerSearch = $('hcPartnerSearch');
    const partnerSelectedCount = $('hcPartnerSelectedCount');

    // Affiliation repeater refs
    const affilList = $('hcAffilList');
    const addAffil = $('hcAddAffil');

    const state = {
      currentItem: null,
      slugDirty: false,
      settingSlug: false,
      saving: false,
      blobs: { primary:null, secondary:null, badge:null },

      recruitersLoaded: false,
      recruitersLoading: false,
      recruiterOptions: [],
      partnerRecruiterIds: [],
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

    function revokeBlobs(){
      for (const k of ['primary','secondary','badge']){
        if (state.blobs[k]) { try{ URL.revokeObjectURL(state.blobs[k]); }catch(_){ } }
        state.blobs[k] = null;
      }
    }

    function setPreview(which, url){
      const map = {
        primary: { img: primaryPreview, empty: primaryEmpty, open: openPrimary },
        secondary: { img: secondaryPreview, empty: secondaryEmpty, open: openSecondary },
        badge: { img: badgePreview, empty: badgeEmpty, open: openBadge },
      };
      const ref = map[which];
      if (!ref) return;

      const u = (url || '').toString().trim();
      if (!u){
        ref.img && (ref.img.style.display = 'none');
        ref.img && ref.img.removeAttribute('src');
        ref.empty && (ref.empty.style.display = '');
        if (ref.open){ ref.open.style.display = 'none'; ref.open.onclick = null; }
        return;
      }
      const full = normalizeUrl(u);
      ref.img && (ref.img.style.display = '');
      ref.img && (ref.img.src = full);
      ref.empty && (ref.empty.style.display = 'none');
      if (ref.open){
        ref.open.style.display = '';
        ref.open.onclick = () => window.open(full, '_blank', 'noopener');
      }
    }

    function bindFilePreview(which, inputEl){
      inputEl?.addEventListener('change', () => {
        const f = inputEl.files?.[0];
        if (!f) return;
        if (state.blobs[which]) { try{ URL.revokeObjectURL(state.blobs[which]); }catch(_){ } }
        state.blobs[which] = URL.createObjectURL(f);
        setPreview(which, state.blobs[which]);
      });
    }
    bindFilePreview('primary', primaryFile);
    bindFilePreview('secondary', secondaryFile);
    bindFilePreview('badge', badgeFile);

    primaryUrl?.addEventListener('input', debounce(()=> {
      if (primaryFile?.files?.length) return;
      setPreview('primary', primaryUrl.value);
    }, 150));
    secondaryUrl?.addEventListener('input', debounce(()=> {
      if (secondaryFile?.files?.length) return;
      setPreview('secondary', secondaryUrl.value);
    }, 150));
    badgeUrl?.addEventListener('input', debounce(()=> {
      if (badgeFile?.files?.length) return;
      setPreview('badge', badgeUrl.value);
    }, 150));

    // Repeaters
    function repRotateRow(value=''){
      const div = document.createElement('div');
      div.className = 'rep-row';
      div.innerHTML = `
        <div class="rep-top">
          <div class="fw-semibold small text-muted">Line</div>
          <button type="button" class="btn btn-light btn-sm" data-remove="1">
            <i class="fa fa-xmark"></i>
          </button>
        </div>
        <div class="mt-2">
          <input class="form-control" data-rotate="1" maxlength="255"
            placeholder="e.g., Admissions Open for 2026" value="${esc(value)}">
        </div>
      `;
      div.querySelector('[data-remove]')?.addEventListener('click', () => div.remove());
      return div;
    }

    /**
     * Affiliation row:
     * ✅ preview on right
     * ✅ removed helper text under file input
     */
    function repFileLogoRow(item={ path:'', caption:'' }){
      const div = document.createElement('div');
      div.className = 'rep-row';

      const p = (item?.path ?? item?.url ?? '').toString().trim();
      const c = (item?.caption ?? '').toString().trim();
      const previewBase = p ? normalizeUrl(p) : PLACEHOLDER_SVG;

      div.innerHTML = `
        <div class="rep-top">
          <div class="fw-semibold small text-muted">Logo Item</div>
          <button type="button" class="btn btn-light btn-sm" data-remove="1" title="Remove">
            <i class="fa fa-xmark"></i>
          </button>
        </div>

        <div class="rep-grid-file">
          <div>
            <label class="form-label">Choose Logo</label>
            <input type="file" class="form-control" data-file="1" accept="image/*">
            <input type="hidden" data-existing="1" value="${esc(p)}">
          </div>

          <div>
            <label class="form-label">Caption (optional)</label>
            <input class="form-control" data-caption="1" maxlength="255"
              placeholder="e.g., AICTE / NAAC" value="${esc(c)}">
          </div>

          <div class="rep-preview-col">
            <label class="form-label">Preview</label>
            <div class="rep-thumb-wrap">
              <img class="rep-thumb" data-preview="1" src="${esc(previewBase)}" alt="Logo preview" loading="lazy">
            </div>
          </div>
        </div>
      `;

      const removeBtn = div.querySelector('[data-remove]');
      const fileEl = div.querySelector('input[type="file"][data-file]');
      const imgEl = div.querySelector('img[data-preview]');
      let blobUrl = null;

      const setImg = (src) => { if (imgEl) imgEl.src = src || PLACEHOLDER_SVG; };

      fileEl?.addEventListener('change', () => {
        const f = fileEl.files?.[0] || null;

        if (blobUrl) { try{ URL.revokeObjectURL(blobUrl); }catch(_){ } blobUrl = null; }

        if (!f){
          setImg(previewBase);
          return;
        }

        blobUrl = URL.createObjectURL(f);
        setImg(blobUrl);
      });

      removeBtn?.addEventListener('click', () => {
        if (blobUrl) { try{ URL.revokeObjectURL(blobUrl); }catch(_){ } blobUrl = null; }
        div.remove();
      });

      return div;
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
    function safeObject(v){
      if (!v) return null;
      if (typeof v === 'object') return v;
      if (typeof v === 'string'){
        try{ return JSON.parse(v); }catch(_){ return null; }
      }
      return null;
    }

    function normalizeRecruiterIds(v){
      const arr = safeArray(v);
      const out = [];
      for (const item of arr){
        if (typeof item === 'number' && Number.isFinite(item)) out.push(item);
        else if (typeof item === 'string' && item.trim() !== '' && /^\d+$/.test(item.trim())) out.push(Number(item.trim()));
        else if (item && typeof item === 'object') {
          const id = item.id ?? item.recruiter_id ?? null;
          if (id !== null && /^\d+$/.test(String(id))) out.push(Number(id));
        }
      }
      return Array.from(new Set(out));
    }

    function setFormEnabled(on){
      const inputs = form?.querySelectorAll('input,textarea,button,select') || [];
      inputs.forEach(el => {
        if (el.id === 'hcBtnReload' || el.id === 'hcBtnReset' || el.id === 'hcBtnResetBottom') return;

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

      if (addRotateLine) addRotateLine.disabled = !on;
      if (addAffil) addAffil.disabled = !on;
      if (recruitersReloadBtn) recruitersReloadBtn.disabled = !on;
    }

    // Recruiters picker helpers
    function showRecruitersError(msg){
      if (!recruitersErr) return;
      recruitersErr.style.display = msg ? '' : 'none';
      recruitersErr.textContent = msg || '';
    }

    function updateSelectedCount(){
      if (!partnerSelectedCount) return;
      partnerSelectedCount.textContent = 'Selected: ' + (state.partnerRecruiterIds?.length || 0);
    }

    function getSearchTerm(){
      return (partnerSearch?.value || '').trim().toLowerCase();
    }

    function renderRecruiters(){
      if (!recruitersGrid) return;

      const term = getSearchTerm();
      const selected = new Set((state.partnerRecruiterIds || []).map(Number));

      const list = (state.recruiterOptions || []).filter(r => {
        if (!term) return true;
        const t = (r.title || '').toLowerCase();
        const s = (r.slug || '').toLowerCase();
        return t.includes(term) || s.includes(term);
      });

      recruitersGrid.innerHTML = list.map(r => {
        const id = Number(r.id);
        const checked = selected.has(id);
        const img = (r.logo_full_url || '').trim() || PLACEHOLDER_SVG;

        return `
          <label class="hc-rec-card ${checked ? 'is-checked' : ''}" data-card="${id}">
            <div class="hc-rec-top">
              <div class="hc-rec-check">
                <input type="checkbox" data-rec-id="${id}" ${checked ? 'checked' : ''}/>
                <span>Pick</span>
              </div>
              <span class="text-muted small">#${id}</span>
            </div>

            <div class="hc-rec-body">
              <img class="hc-rec-img" src="${esc(img)}" alt="${esc(r.title || 'Recruiter')}" loading="lazy"/>
              <div class="hc-rec-title">${esc(r.title || 'Recruiter')}</div>
            </div>
          </label>
        `;
      }).join('');

      recruitersGrid.querySelectorAll('input[type="checkbox"][data-rec-id]').forEach(cb => {
        cb.addEventListener('change', () => {
          const id = Number(cb.getAttribute('data-rec-id'));
          if (!Number.isFinite(id)) return;

          const set = new Set((state.partnerRecruiterIds || []).map(Number));
          if (cb.checked) set.add(id);
          else set.delete(id);

          state.partnerRecruiterIds = Array.from(set);
          updateSelectedCount();

          const card = recruitersGrid.querySelector(`[data-card="${id}"]`);
          if (card) card.classList.toggle('is-checked', cb.checked);
        });
      });
    }

    async function fetchRecruiters(force=false){
      if (state.recruitersLoading) return;
      if (state.recruitersLoaded && !force) return;

      state.recruitersLoading = true;
      showRecruitersError('');
      try{
        const res = await fetchWithTimeout('/api/header-components/recruiter-options?only_active=1', {
          headers: authHeaders()
        }, 15000);

        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) {
          throw new Error(js?.message || 'Failed to load recruiters');
        }

        state.recruiterOptions = Array.isArray(js.data) ? js.data : [];
        state.recruitersLoaded = true;

        renderRecruiters();
      }catch(ex){
        showRecruitersError(ex?.name === 'AbortError' ? 'Recruiters request timed out' : (ex.message || 'Failed to load recruiters'));
      }finally{
        state.recruitersLoading = false;
      }
    }

    function resetToBlank(){
      revokeBlobs();
      form?.reset();

      hcUuid.value = '';
      hcId.value = '';
      state.currentItem = null;
      state.slugDirty = false;
      state.settingSlug = false;

      rotateList.innerHTML = '';
      affilList.innerHTML = '';

      rotateList.appendChild(repRotateRow(''));
      affilList.appendChild(repFileLogoRow({}));

      state.partnerRecruiterIds = [];
      updateSelectedCount();
      renderRecruiters();

      setPreview('primary','');
      setPreview('secondary','');
      setPreview('badge','');

      if (primaryFile) primaryFile.value = '';
      if (secondaryFile) secondaryFile.value = '';
      if (badgeFile) badgeFile.value = '';

      setUpdated('—');
      setStatus('warn', 'Not saved yet');

      setFormEnabled(!!canCreate);
    }

    function fillFromItem(item){
      state.currentItem = item || null;

      hcUuid.value = item?.uuid || '';
      hcId.value = item?.id || '';

      headerText.value = item?.header_text || '';
      slugInput.value = item?.slug || '';
      admissionLink.value = item?.admission_link_url || '';

      const metaObj = safeObject(item?.metadata);
      metadataInput.value = metaObj ? JSON.stringify(metaObj, null, 2) : '';

      rotateList.innerHTML = '';
      const rot = safeArray(item?.rotating_text_json);
      if (rot.length) rot.forEach(v => rotateList.appendChild(repRotateRow(String(v ?? ''))));
      else rotateList.appendChild(repRotateRow(''));

      state.partnerRecruiterIds = normalizeRecruiterIds(item?.partner_logos_json || item?.partner_recruiter_ids || []);
      updateSelectedCount();
      renderRecruiters();

      affilList.innerHTML = '';
      const affs = safeArray(item?.affiliation_logos_json);
      if (affs.length) affs.forEach(v => affilList.appendChild(repFileLogoRow(v)));
      else affilList.appendChild(repFileLogoRow({}));

      primaryUrl.value = item?.primary_logo_url || '';
      secondaryUrl.value = item?.secondary_logo_url || '';
      badgeUrl.value = item?.admission_badge_url || '';

      setPreview('primary', item?.primary_logo_full_url || item?.primary_logo_url || '');
      setPreview('secondary', item?.secondary_logo_full_url || item?.secondary_logo_url || '');
      setPreview('badge', item?.admission_badge_full_url || item?.admission_badge_url || '');

      if (primaryFile) primaryFile.value = '';
      if (secondaryFile) secondaryFile.value = '';
      if (badgeFile) badgeFile.value = '';

      state.slugDirty = true;

      setUpdated(item?.updated_at || '—');
      setStatus('ok', 'Loaded');

      setFormEnabled(!!canEdit);
    }

    async function fetchSingleton(){
      const params = new URLSearchParams();
      params.set('per_page', '1');
      params.set('page', '1');
      params.set('sort', 'updated_at');
      params.set('direction', 'desc');

      const res = await fetchWithTimeout(`/api/header-components?${params.toString()}`, { headers: authHeaders() }, 15000);
      if (res.status === 401 || res.status === 403) { window.location.href = '/'; return null; }
      const js = await res.json().catch(()=> ({}));
      if (!res.ok) throw new Error(js?.message || 'Failed to load');

      const arr = Array.isArray(js.data) ? js.data : [];
      return arr[0] || null;
    }

    headerText?.addEventListener('input', debounce(() => {
      if (hcUuid.value) return;
      if (state.slugDirty) return;
      const next = slugify(headerText.value);
      state.settingSlug = true;
      slugInput.value = next;
      state.settingSlug = false;
    }, 120));

    slugInput?.addEventListener('input', () => {
      if (hcUuid.value) return;
      if (state.settingSlug) return;
      state.slugDirty = !!(slugInput.value || '').trim();
    });

    addRotateLine?.addEventListener('click', () => rotateList.appendChild(repRotateRow('')));
    addAffil?.addEventListener('click', () => affilList.appendChild(repFileLogoRow({})));

    partnerSearch?.addEventListener('input', debounce(() => renderRecruiters(), 120));
    recruitersReloadBtn?.addEventListener('click', () => fetchRecruiters(true));

    async function reload(){
      showLoading(true);
      try{
        setStatus('warn', 'Loading…');
        await fetchRecruiters(false);

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
      revokeBlobs();
      if (state.currentItem) fillFromItem(state.currentItem);
      else resetToBlank();
    }

    btnReload?.addEventListener('click', reload);
    btnResetTop?.addEventListener('click', resetToCurrent);
    btnResetBottom?.addEventListener('click', resetToCurrent);

    saveBtnTop?.addEventListener('click', () => form?.requestSubmit?.() || saveBtn?.click());

    function gatherFileLogoRows(containerEl){
      const rows = Array.from(containerEl?.querySelectorAll('.rep-row') || []);
      const keepExisting = [];
      const files = [];
      const captions = [];

      rows.forEach(r => {
        const existing = (r.querySelector('input[data-existing]')?.value || '').trim();
        const cap = (r.querySelector('input[data-caption]')?.value || '').trim();
        const f = r.querySelector('input[type="file"][data-file]')?.files?.[0] || null;

        if (f){
          files.push(f);
          captions.push(cap || '');
        } else if (existing){
          const obj = { path: existing };
          if (cap) obj.caption = cap;
          keepExisting.push(obj);
        }
      });

      return { keepExisting, files, captions };
    }

    form?.addEventListener('submit', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (state.saving) return;

      const isEdit = !!hcUuid.value;
      if (isEdit && !canEdit) return;
      if (!isEdit && !canCreate) return;

      const ht = (headerText.value || '').trim();
      if (!ht){ err('Header text is required'); headerText.focus(); return; }

      const fd = new FormData();
      fd.append('header_text', ht);

      const slug = (slugInput.value || '').trim();
      if (slug) fd.append('slug', slug);

      const adm = (admissionLink.value || '').trim();
      if (adm) fd.append('admission_link_url', adm);

      fd.append('rotating_text_json', JSON.stringify(
        Array.from(rotateList?.querySelectorAll('input[data-rotate]') || [])
          .map(i => (i.value || '').trim()).filter(Boolean)
      ));

      const metaRaw = (metadataInput.value || '').trim();
      if (metaRaw){
        try{
          const metaObj = JSON.parse(metaRaw);
          fd.append('metadata', JSON.stringify(metaObj));
        }catch(_){
          err('Metadata must be valid JSON');
          metadataInput.focus();
          return;
        }
      }

      const pUrl = (primaryUrl.value || '').trim();
      const sUrl = (secondaryUrl.value || '').trim();
      const bUrl = (badgeUrl.value || '').trim();

      const pFile = primaryFile?.files?.[0] || null;
      const sFile = secondaryFile?.files?.[0] || null;
      const bFile = badgeFile?.files?.[0] || null;

      if (!isEdit){
        if (!pUrl && !pFile){ err('Primary logo is required (URL/path or upload)'); primaryUrl.focus(); return; }
        if (!sUrl && !sFile){ err('Secondary logo is required (URL/path or upload)'); secondaryUrl.focus(); return; }
        if (!bUrl && !bFile){ err('Admission badge is required (URL/path or upload)'); badgeUrl.focus(); return; }
      }

      if (pUrl) fd.append('primary_logo_url', pUrl);
      if (sUrl) fd.append('secondary_logo_url', sUrl);
      if (bUrl) fd.append('admission_badge_url', bUrl);

      if (pFile) fd.append('primary_logo', pFile);
      if (sFile) fd.append('secondary_logo', sFile);
      if (bFile) fd.append('admission_badge', bFile);

      fd.append('partner_logos_json', JSON.stringify((state.partnerRecruiterIds || []).map(Number)));

      const affils = gatherFileLogoRows(affilList);
      fd.append('affiliation_logos_json', JSON.stringify(affils.keepExisting));
      affils.files.forEach((f, idx) => {
        fd.append('affiliation_logos[]', f);
        fd.append('affiliation_logos_captions[]', affils.captions[idx] || '');
      });

      const url = isEdit
        ? `/api/header-components/${encodeURIComponent(hcUuid.value)}`
        : `/api/header-components`;

      if (isEdit) fd.append('_method', 'PUT');

      state.saving = true;
      showLoading(true);
      setBtnLoading(saveBtn, true);
      setBtnLoading(saveBtnTop, true);

      try{
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

    // Init
    (async () => {
      showLoading(true);
      try{
        await fetchMe();
        await fetchRecruiters(false);
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
