{{-- resources/views/modules/home/settingsAlumniSpeak.blade.php --}}
@section('title','Alumni Speak Settings')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

<style>
/* =========================
  Alumni Speak Settings (Admin)
  - reference-inspired UI (rewritten)
  - uses common/main.css tokens
========================= */

.as-wrap{padding:14px 4px;max-width:1140px;margin:16px auto 40px;overflow:visible}

/* Cards */
.as-card{
  border:1px solid var(--line-strong);
  border-radius:16px;
  background:var(--surface);
  box-shadow:var(--shadow-2);
  overflow:visible;
}
.as-card .card-header{
  background:transparent;
  border-bottom:1px solid var(--line-soft);
}
.as-title{margin:0;font-weight:800}
.as-helper{font-size:12.5px;color:var(--muted-color)}
.as-small{font-size:12.5px}

/* Chips */
.as-chip{
  display:inline-flex;align-items:center;gap:8px;
  padding:8px 10px;border-radius:999px;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 90%, transparent);
  font-size:12.5px;
}
.as-chip i{opacity:.75}

/* iFrame / embed builder */
.embed-row{
  border:1px solid var(--line-strong);
  border-radius:14px;
  padding:12px;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.embed-row:hover{background:var(--page-hover)}

.embed-row .handle{
  width:34px;height:34px;border-radius:10px;
  display:grid;place-items:center;
  border:1px solid var(--line-soft);
  background:color-mix(in oklab, var(--surface) 90%, transparent);
  cursor:grab;
  user-select:none;
  -webkit-user-select:none;
  touch-action:none;
}
.embed-row .handle:active{cursor:grabbing}
.embed-row .handle i{opacity:.65}

.embed-meta{
  font-size:12.5px;color:var(--muted-color);
  display:flex;flex-wrap:wrap;gap:10px;align-items:center;
}

.embed-thumb{
  width:100%;
  border-radius:12px;
  border:1px solid var(--line-soft);
  overflow:hidden;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
}
.embed-thumb img{display:block;width:100%;height:auto}
.embed-preview{
  border-radius:12px;
  border:1px solid var(--line-soft);
  overflow:hidden;
  background:#000;
}
.embed-preview iframe{display:block;width:100%;aspect-ratio:16/9;border:0}

/* Sortable drag classes */
.as-sort-ghost{opacity:.35}
.as-sort-chosen{box-shadow:var(--shadow-2)}
.as-sort-chosen .handle{cursor:grabbing}
.as-sort-drag{opacity:.85}

/* JSON editor (kept for metadata only) */
.json-box{
  border:1px solid var(--line-strong);
  border-radius:14px;
  background:color-mix(in oklab, var(--surface) 92%, transparent);
  overflow:hidden;
}
.json-box .json-top{
  display:flex;align-items:center;justify-content:space-between;
  gap:10px;
  padding:10px 12px;
  border-bottom:1px solid var(--line-soft);
}
.json-box textarea{
  width:100%;
  min-height:210px;
  border:0;outline:0;
  padding:12px;
  background:transparent;
  color:var(--ink);
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  font-size:12.5px;
  line-height:1.45;
  resize:vertical;
}

/* Loading overlay */
.loading-overlay{
  position:fixed;inset:0;
  background:rgba(0,0,0,0.45);
  display:flex;justify-content:center;align-items:center;
  z-index:9999;
  backdrop-filter:blur(2px)
}
.loading-spinner{
  background:var(--surface);
  padding:20px 22px;
  border-radius:14px;
  display:flex;flex-direction:column;align-items:center;gap:10px;
  box-shadow:0 10px 26px rgba(0,0,0,0.3)
}
.spinner{
  width:40px;height:40px;border-radius:50%;
  border:4px solid rgba(148,163,184,0.3);
  border-top:4px solid var(--primary-color);
  animation:spin 1s linear infinite
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
  animation:spin 1s linear infinite
}

/* Responsive */
@media (max-width: 768px){
  .as-actions{flex-direction:column;align-items:stretch !important}
  .as-actions .btn{width:100%}
}
</style>
@endpush

@section('content')
<div class="as-wrap">

  {{-- Loading Overlay --}}
  <div id="globalLoading" class="loading-overlay" style="display:none;">
    <div class="loading-spinner">
      <div class="spinner"></div>
      <div class="as-small">Loading…</div>
    </div>
  </div>

  {{-- Header --}}
  <div class="card as-card mb-3">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div>
        <div class="d-flex align-items-center gap-2">
          <i class="fa-solid fa-video" style="opacity:.75;"></i>
          <h5 class="m-0 fw-bold">Alumni Speak Settings</h5>
        </div>
        <div class="as-helper mt-1">
          Save <b>iframe_urls_json</b> (array of embeds). Metadata is separate and optional.
        </div>
        <div class="as-helper mt-1">
          <i class="fa-regular fa-circle-question me-1"></i>
          Tip: Use drag & drop (handle) or the up/down arrows to reorder.
        </div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <span class="as-chip"><i class="fa-solid fa-shield-halved"></i> Admin module</span>
        <span class="as-chip"><i class="fa-solid fa-house"></i> Home section</span>
      </div>
    </div>
  </div>

  <div class="row g-3">
    {{-- Form (full width; Quick Preview removed) --}}
    <div class="col-12">
      <div class="card as-card">
        <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
          <div>
            <div class="as-title"><i class="fa-solid fa-gear me-2"></i>Live Content</div>
            <div class="as-helper mt-1">Single page form. Save embeds into <b>iframe_urls_json</b>.</div>
          </div>

          <div class="d-flex gap-2 flex-wrap as-actions" id="formControls" style="display:none;">
            <button type="button" class="btn btn-light" id="btnDefaults">
              <i class="fa fa-wand-magic-sparkles me-1"></i>Defaults
            </button>
            <button type="button" class="btn btn-outline-primary" id="btnReload">
              <i class="fa fa-rotate me-1"></i>Reload
            </button>
            <button type="button" class="btn btn-primary" id="btnSave">
              <i class="fa fa-floppy-disk me-1"></i>Save
            </button>
          </div>
        </div>

        <div class="card-body">
          <input type="hidden" id="currentUuid">
          <input type="hidden" id="currentId">

          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="title" placeholder="e.g., Alumni Speak">
              <div class="as-helper mt-1">Required by schema.</div>
            </div>

            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea class="form-control" id="description" rows="3" placeholder="Short paragraph for the Alumni Speak section…"></textarea>
              <div class="as-helper mt-1">Optional.</div>
            </div>

            {{-- Embeds Builder --}}
            <div class="col-12">
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <div class="fw-semibold">
                  <i class="fa-solid fa-code me-2"></i>iFrame URLs / Embed Codes (iframe_urls_json)
                </div>
                <div class="d-flex gap-2 flex-wrap" id="embedTools" style="display:none;">
                  <button type="button" class="btn btn-outline-primary btn-sm" id="btnAddEmbed">
                    <i class="fa fa-plus me-1"></i>Add Embed
                  </button>
                  <button type="button" class="btn btn-light btn-sm" id="btnClearEmbeds">
                    <i class="fa fa-eraser me-1"></i>Clear
                  </button>
                </div>
              </div>

              <div id="embedsList" class="d-flex flex-column gap-2">
                <div class="text-muted as-small" id="embedEmptyHint" style="padding:12px 6px;">
                  No embeds yet. Click <b>Add Embed</b> to add a YouTube iframe code or URL.
                </div>
              </div>

              <div class="as-helper mt-2">
                Stored as JSON array in <code>iframe_urls_json</code>. Each item can be:
                <code>{"title":"…","url":"…","iframe":"…"}</code>
              </div>
            </div>

            {{-- Advanced (flags, publish, metadata) --}}
            <div class="col-12">
              <div class="accordion" id="asAdvancedAccordion">
                <div class="accordion-item" style="background:transparent;border:1px solid var(--line-strong);border-radius:14px;overflow:hidden;">
                  <h2 class="accordion-header" id="asAdvHead">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#asAdvBody">
                      <i class="fa-solid fa-sliders me-2"></i>Advanced (UI flags, status, schedule, metadata)
                    </button>
                  </h2>
                  <div id="asAdvBody" class="accordion-collapse collapse" data-bs-parent="#asAdvancedAccordion">
                    <div class="accordion-body">

                      <div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label">Slug (optional)</label>
                          <input type="text" class="form-control" id="slug" placeholder="auto-generated if empty">
                          <div class="as-helper mt-1">If blank, backend generates from title.</div>
                        </div>

                        <div class="col-md-6">
                          <label class="form-label">Status</label>
                          <select class="form-select" id="status">
                            <option value="draft">draft</option>
                            <option value="published">published</option>
                            <option value="archived">archived</option>
                          </select>
                        </div>

                        <div class="col-md-4">
                          <label class="form-label">Sort Order</label>
                          <input type="number" class="form-control" id="sort_order" min="0" step="1" value="0">
                        </div>

                        <div class="col-md-4">
                          <label class="form-label">Scroll Latency (ms)</label>
                          <input type="number" class="form-control" id="scroll_latency_ms" min="0" step="1" value="3000">
                        </div>

                        {{-- ✅ Department dropdown (names only) --}}
                        <div class="col-md-4">
                          <label class="form-label">Department (optional)</label>
                          <select class="form-select" id="department_id" data-value="">
                            <option value="">Loading departments…</option>
                          </select>
                          <div class="as-helper mt-1">Optional. Leave blank for global (all departments).</div>
                        </div>

                        <div class="col-12">
                          <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox" id="auto_scroll" checked>
                              <label class="form-check-label" for="auto_scroll">Auto scroll</label>
                            </div>
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox" id="loop" checked>
                              <label class="form-check-label" for="loop">Loop</label>
                            </div>
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox" id="show_arrows" checked>
                              <label class="form-check-label" for="show_arrows">Show arrows</label>
                            </div>
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox" id="show_dots" checked>
                              <label class="form-check-label" for="show_dots">Show dots</label>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <label class="form-label">Publish At (optional)</label>
                          <input type="datetime-local" class="form-control" id="publish_at">
                        </div>

                        <div class="col-md-6">
                          <label class="form-label">Expire At (optional)</label>
                          <input type="datetime-local" class="form-control" id="expire_at">
                        </div>

                        <div class="col-12">
                          <div class="json-box">
                            <div class="json-top">
                              <div class="fw-semibold"><i class="fa-solid fa-database me-2"></i>metadata (optional)</div>
                              <div class="d-flex gap-2 flex-wrap" id="metaTools" style="display:none;">
                                <button type="button" class="btn btn-light btn-sm" id="btnMetaPretty">
                                  <i class="fa fa-align-left me-1"></i>Pretty
                                </button>
                              </div>
                            </div>
                            <textarea id="metadata_json" spellcheck="false" placeholder='Example: {"theme":"dark","note":"extra ui settings"}'></textarea>
                          </div>
                          <div class="as-helper mt-2">
                            This is separate from <b>iframe_urls_json</b>. Leave empty if you don’t need it.
                          </div>
                        </div>

                      </div>

                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Identity --}}
            <div class="col-12">
              <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <div class="as-helper">
                  <span class="me-2"><i class="fa-regular fa-id-card me-1"></i><span id="identityText">—</span></span>
                  <span class="me-2"><i class="fa-regular fa-clock me-1"></i><span id="updatedText">—</span></span>
                </div>
                <div class="as-helper">
                  <span class="me-2"><i class="fa-regular fa-user me-1"></i><span id="byText">—</span></span>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Toasts --}}
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
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

{{-- ✅ Drag & Drop Sort --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
(() => {
  if (window.__ALUMNI_SPEAK_SETTINGS_INIT__) return;
  window.__ALUMNI_SPEAK_SETTINGS_INIT__ = true;

  const $ = (id) => document.getElementById(id);

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  async function fetchWithTimeout(url, opts={}, ms=15000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try{
      return await fetch(url, { ...opts, signal: ctrl.signal });
    } finally { clearTimeout(t); }
  }

  function safeJsonParse(txt, fallback=null, errMsg='JSON must be valid.'){
    const s = (txt || '').toString().trim();
    if (!s) return { ok:true, val: fallback };
    try{ return { ok:true, val: JSON.parse(s) }; }
    catch(e){ return { ok:false, error: errMsg }; }
  }

  function prettyTextarea(el){
    const raw = (el?.value || '').trim();
    if (!raw) return;
    try{ el.value = JSON.stringify(JSON.parse(raw), null, 2); }catch(_){}
  }

  // ---------- Embed helpers ----------
  function extractIframeSrc(input){
    const s = (input || '').toString().trim();
    if (!s) return '';
    const m = s.match(/<iframe[^>]*\s+src\s*=\s*["']([^"']+)["'][^>]*>/i);
    return m ? (m[1] || '').trim() : '';
  }

  function youtubeIdFromUrl(url){
    const u = (url || '').toString();
    if (!u) return '';
    let m = u.match(/youtu\.be\/([a-zA-Z0-9_-]{6,})/);
    if (m) return m[1];
    m = u.match(/[?&]v=([a-zA-Z0-9_-]{6,})/);
    if (m) return m[1];
    m = u.match(/\/embed\/([a-zA-Z0-9_-]{6,})/);
    if (m) return m[1];
    m = u.match(/youtube-nocookie\.com\/embed\/([a-zA-Z0-9_-]{6,})/);
    if (m) return m[1];
    return '';
  }

  function toNoCookieEmbedUrl(urlOrSrc){
    const src = (urlOrSrc || '').toString().trim();
    if (!src) return '';
    const vid = youtubeIdFromUrl(src);
    if (vid) return `https://www.youtube-nocookie.com/embed/${vid}`;
    return src;
  }

  function buildIframeHtml(embedUrl){
    const u = (embedUrl || '').toString().trim();
    if (!u) return '';
    return `<iframe src="${u}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>`;
  }

  function thumbUrlForYoutube(vid){
    if (!vid) return '';
    return `https://i.ytimg.com/vi/${vid}/hqdefault.jpg`;
  }

  // ---------- Embeds builder ----------
  function normalizeEmbeds(v){
    if (Array.isArray(v)) return v;
    if (typeof v === 'string'){
      const parsed = safeJsonParse(v, [], 'iframe_urls_json must be valid JSON.');
      if (parsed.ok && Array.isArray(parsed.val)) return parsed.val;
      return [];
    }
    return [];
  }

  function toEmbedModel(x){
    const title = (x?.title ?? x?.label ?? '').toString();
    const raw   = (x?.iframe ?? x?.iframe_code ?? x?.html ?? x?.code ?? x?.embed ?? x?.url ?? x?.src ?? '').toString();
    const url   = (x?.url ?? x?.src ?? '').toString();
    return { title, raw, url };
  }

  function readEmbedsFromRows(){
    const list = $('embedsList');
    const rows = list ? Array.from(list.querySelectorAll('[data-embed-row="1"]')) : [];
    return rows.map(row => {
      const t = (row.querySelector('input[data-field="title"]')?.value ?? '').toString().trim();
      const raw = (row.querySelector('textarea[data-field="embed"]')?.value ?? '').toString().trim();
      if (!t && !raw) return null;

      const iframeSrc = extractIframeSrc(raw);
      const urlGuess = iframeSrc || raw;
      const embedUrl = toNoCookieEmbedUrl(urlGuess);

      const vid = youtubeIdFromUrl(embedUrl);
      const provider = vid ? 'youtube' : '';

      const iframeHtml = iframeSrc
        ? raw
        : buildIframeHtml(embedUrl);

      return {
        title: t,
        url: embedUrl || urlGuess || '',
        iframe: iframeHtml || '',
        provider: provider || null,
        video_id: vid || null
      };
    }).filter(Boolean);
  }

  function ensureEmptyState(){
    const list = $('embedsList');
    if (!list) return;
    const rows = list.querySelectorAll('[data-embed-row="1"]');
    const hint = list.querySelector('#embedEmptyHint');

    if (!rows.length){
      list.innerHTML = `<div class="text-muted as-small" id="embedEmptyHint" style="padding:12px 6px;">
        No embeds yet. Click <b>Add Embed</b> to add a YouTube iframe code or URL.
      </div>`;
    } else if (hint){
      hint.remove();
    }
  }

  function embedRowHtml(seed){
    const m = toEmbedModel(seed || {});
    const title = esc(m.title || '');
    const raw   = esc(m.raw || m.url || '');

    return `
      <div class="embed-row" data-embed-row="1">
        <div class="d-flex gap-2 align-items-start">
          <div class="handle" title="Drag to reorder">
            <i class="fa-solid fa-grip-vertical"></i>
          </div>

          <div class="flex-grow-1">
            <div class="row g-2">
              <div class="col-md-5">
                <label class="form-label mb-1">Video Title (optional)</label>
                <input type="text" class="form-control" data-field="title" placeholder="e.g., Alumni Story" value="${title}">
              </div>
              <div class="col-md-7">
                <label class="form-label mb-1">YouTube iframe code or URL</label>
                <textarea class="form-control" data-field="embed" rows="3" placeholder="Paste <iframe …></iframe> OR a YouTube URL">${raw}</textarea>
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-2 align-items-center">
              <button type="button" class="btn btn-light btn-sm" data-action="up" title="Move up">
                <i class="fa fa-arrow-up"></i>
              </button>
              <button type="button" class="btn btn-light btn-sm" data-action="down" title="Move down">
                <i class="fa fa-arrow-down"></i>
              </button>

              <button type="button" class="btn btn-outline-secondary btn-sm" data-action="preview">
                <i class="fa fa-image me-1"></i>Preview
              </button>

              <button type="button" class="btn btn-outline-danger btn-sm ms-auto" data-action="remove">
                <i class="fa fa-trash me-1"></i>Remove
              </button>
            </div>

            <div class="mt-2 embed-meta">
              <span><i class="fa-regular fa-circle-check me-1"></i><span data-meta="status">Waiting…</span></span>
              <a href="#" class="text-decoration-none" data-meta="open" target="_blank" rel="noopener" style="display:none;">
                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Open
              </a>
            </div>

            <div class="mt-2" data-preview-wrap style="display:none;">
              <div class="embed-thumb" data-thumb style="display:none;">
                <img alt="Preview thumbnail" />
              </div>

              <div class="embed-preview mt-2" data-iframe style="display:none;">
                <iframe></iframe>
              </div>

              <div class="d-flex gap-2 flex-wrap mt-2">
                <button type="button" class="btn btn-outline-primary btn-sm" data-action="load-iframe" style="display:none;">
                  <i class="fa-brands fa-youtube me-1"></i>Load iframe preview
                </button>
                <button type="button" class="btn btn-light btn-sm" data-action="hide-preview">
                  <i class="fa fa-eye-slash me-1"></i>Hide
                </button>
              </div>

              <div class="as-helper mt-2">
                Note: Loading iframe preview may show browser console warnings (Safari privacy / tracking prevention). This does not affect saving.
              </div>
            </div>

          </div>
        </div>
      </div>
    `;
  }

  // ✅ Drag & drop init (fix)
  let __sortable = null;
  function initSortable(){
    const list = $('embedsList');
    if (!list) return;

    if (__sortable && __sortable.destroy){
      try{ __sortable.destroy(); }catch(_){}
      __sortable = null;
    }

    if (typeof Sortable === 'undefined') return;

    __sortable = new Sortable(list, {
      handle: '.handle',
      draggable: '[data-embed-row="1"]',
      animation: 150,
      ghostClass: 'as-sort-ghost',
      chosenClass: 'as-sort-chosen',
      dragClass: 'as-sort-drag',
      forceFallback: true,      // ✅ Safari-friendly
      fallbackOnBody: true,
      onEnd: () => {
        ensureEmptyState();
        refreshRowMeta();
      }
    });
  }

  function renderEmbeds(arr){
    const list = $('embedsList');
    if (!list) return;

    const out = (arr || []).map(toEmbedModel);
    if (!out.length){
      list.innerHTML = `<div class="text-muted as-small" id="embedEmptyHint" style="padding:12px 6px;">
        No embeds yet. Click <b>Add Embed</b> to add a YouTube iframe code or URL.
      </div>`;
      initSortable();
      return;
    }

    list.innerHTML = out.map(m => embedRowHtml(m)).join('');
    ensureEmptyState();
    refreshRowMeta();
    initSortable();
  }

  function addEmbedRow(seed={title:'', raw:''}){
    const list = $('embedsList');
    if (!list) return;

    const hint = list.querySelector('#embedEmptyHint');
    if (hint) hint.remove();

    const wrap = document.createElement('div');
    wrap.innerHTML = embedRowHtml(seed);
    list.appendChild(wrap.firstElementChild);

    ensureEmptyState();
    refreshRowMeta();
    initSortable();
  }

  function moveRow(row, dir){
    const list = $('embedsList');
    if (!list) return;

    const all = Array.from(list.querySelectorAll('[data-embed-row="1"]'));
    const idx = all.indexOf(row);
    if (idx < 0) return;

    const to = dir === 'up' ? idx - 1 : idx + 1;
    if (to < 0 || to >= all.length) return;

    if (dir === 'up') list.insertBefore(row, all[to]);
    else list.insertBefore(all[to], row);

    refreshRowMeta();
  }

  function refreshRowMeta(){
    const list = $('embedsList');
    if (!list) return;

    const rows = Array.from(list.querySelectorAll('[data-embed-row="1"]'));
    rows.forEach(row => {
      const raw = (row.querySelector('textarea[data-field="embed"]')?.value ?? '').toString().trim();
      const src = extractIframeSrc(raw) || raw;
      const embedUrl = toNoCookieEmbedUrl(src);
      const vid = youtubeIdFromUrl(embedUrl);

      const statusEl = row.querySelector('[data-meta="status"]');
      const openEl = row.querySelector('[data-meta="open"]');

      if (!raw){
        if (statusEl) statusEl.textContent = 'Empty';
        if (openEl) openEl.style.display = 'none';
        return;
      }

      if (vid){
        if (statusEl) statusEl.textContent = `YouTube ✓ (id: ${vid})`;
        if (openEl){
          openEl.href = embedUrl;
          openEl.style.display = '';
        }

        const thumbWrap = row.querySelector('[data-thumb]');
        const img = thumbWrap ? thumbWrap.querySelector('img') : null;
        if (thumbWrap && img){
          img.src = thumbUrlForYoutube(vid);
        }
      } else {
        if (statusEl) statusEl.textContent = 'Embed URL ✓';
        if (openEl){
          openEl.href = embedUrl || '#';
          openEl.style.display = embedUrl ? '' : 'none';
        }
      }

      const loadBtn = row.querySelector('button[data-action="load-iframe"]');
      if (loadBtn) loadBtn.style.display = embedUrl ? '' : 'none';
    });
  }

  // ---------- Main ----------
  document.addEventListener('DOMContentLoaded', () => {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    if (!token) { window.location.href = '/'; return; }

    const globalLoading = $('globalLoading');
    const showLoading = (v) => { if (globalLoading) globalLoading.style.display = v ? 'flex' : 'none'; };

    const toastOkEl = $('toastSuccess');
    const toastErrEl = $('toastError');
    const toastOk = toastOkEl ? new bootstrap.Toast(toastOkEl) : null;
    const toastErr = toastErrEl ? new bootstrap.Toast(toastErrEl) : null;
    const ok = (m) => { const el=$('toastSuccessText'); if(el) el.textContent=m||'Done'; toastOk && toastOk.show(); };
    const err = (m) => { const el=$('toastErrorText'); if(el) el.textContent=m||'Something went wrong'; toastErr && toastErr.show(); };

    const authHeaders = (json=false) => {
      const h = { 'Authorization': 'Bearer ' + token, 'Accept':'application/json' };
      if (json) h['Content-Type'] = 'application/json';
      return h;
    };

    const API = {
      list:   '/api/alumni-speaks',
      show:   (id) => `/api/alumni-speaks/${encodeURIComponent(id)}`,
      store:  '/api/alumni-speaks',
      update: (id) => `/api/alumni-speaks/${encodeURIComponent(id)}`,
      departments: '/api/departments', // ✅ for department dropdown
    };

    // permissions
    const ACTOR = { role: '' };
    let canWrite = false;

    function computePermissions(){
      const r = (ACTOR.role || '').toLowerCase();
      const writeRoles = ['admin','director','principal','hod','faculty','technical_assistant','it_person','super_admin'];
      canWrite = writeRoles.includes(r);

      const fc = $('formControls');
      const et = $('embedTools');
      const mt = $('metaTools');

      if (fc) fc.style.display = canWrite ? 'flex' : 'none';
      if (et) et.style.display = canWrite ? 'flex' : 'none';
      if (mt) mt.style.display = canWrite ? 'flex' : 'none';

      $('btnSave') && ($('btnSave').disabled = !canWrite);
      $('btnDefaults') && ($('btnDefaults').disabled = !canWrite);
    }

    async function fetchMe(){
      try{
        const res = await fetchWithTimeout('/api/users/me', { headers: authHeaders(false) }, 8000);
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

    function setBtnLoading(btn, loading){
      if (!btn) return;
      btn.disabled = !!loading;
      btn.classList.toggle('btn-loading', !!loading);
    }

    function toDatetimeLocal(val){
      if (!val) return '';
      const s = String(val);
      if (s.includes('T') && s.length >= 16) return s.slice(0,16);
      const d = new Date(s);
      if (isNaN(d.getTime())) return '';
      const pad = (n) => String(n).padStart(2,'0');
      return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }

    // ✅ Load departments into dropdown (names only)
    async function loadDepartments(){
      const sel = $('department_id');
      if (!sel) return;

      const prev = (sel.value || sel.dataset.value || '').toString();
      sel.innerHTML = `<option value="">Loading departments…</option>`;

      try{
        const res = await fetchWithTimeout(API.departments, { headers: authHeaders(false) }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }

        const js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to load departments');

        // support: array | {data:[...]} | {data:{data:[...]}} | {items:[...]}
        let rows = [];
        if (Array.isArray(js?.data)) rows = js.data;
        else if (Array.isArray(js?.data?.data)) rows = js.data.data;
        else if (Array.isArray(js?.items)) rows = js.items;
        else if (Array.isArray(js)) rows = js;

        rows = (rows || []).filter(d => {
          if (!d || typeof d !== 'object') return false;
          if (d.is_active === 0 || d.is_active === false) return false;
          if (d.status && String(d.status).toLowerCase() === 'inactive') return false;
          return true;
        });

        rows.sort((a,b) => {
          const an = (a?.name ?? a?.title ?? a?.department_name ?? a?.slug ?? '').toString().trim();
          const bn = (b?.name ?? b?.title ?? b?.department_name ?? b?.slug ?? '').toString().trim();
          return an.localeCompare(bn);
        });

        const opts = [`<option value="">Select a department (optional)</option>`];

        rows.forEach(d => {
          const id = d?.id ?? d?.department_id ?? null; // value must stay numeric id
          const name = (d?.name ?? d?.title ?? d?.department_name ?? d?.slug ?? '').toString().trim();
          if (id === null || id === undefined) return;
          if (!name) return;
          // ✅ show ONLY the name in option text
          opts.push(`<option value="${esc(id)}">${esc(name)}</option>`);
        });

        if (opts.length === 1){
          opts.push(`<option value="" disabled>(No departments found)</option>`);
        }

        sel.innerHTML = opts.join('');

        // restore selection if any
        if (prev){
          sel.value = String(prev);
          sel.dataset.value = String(prev);
        }
      }catch(ex){
        sel.innerHTML = `<option value="">Select a department (optional)</option>`;
        err(ex?.name === 'AbortError' ? 'Departments request timed out' : (ex.message || 'Failed to load departments'));
      }
    }

    function fillForm(row){
      const r = row || {};

      $('currentUuid').value = r.uuid || '';
      $('currentId').value = r.id || '';

      $('title').value = r.title || '';
      $('description').value = r.description || '';

      $('slug').value = r.slug || '';
      $('status').value = r.status || 'draft';
      $('sort_order').value = (r.sort_order ?? 0);
      $('scroll_latency_ms').value = (r.scroll_latency_ms ?? 3000);

      // ✅ department dropdown value (supports department_id or nested department.id)
      const depSel = $('department_id');
      const depId =
        (r.department_id ?? (r.department && r.department.id) ?? (r.department && r.department.department_id) ?? '');
      if (depSel){
        depSel.dataset.value = (depId ?? '').toString();
        depSel.value = (depId ?? '').toString();
      }

      $('auto_scroll').checked = (r.auto_scroll ?? 1) ? true : false;
      $('loop').checked = (r.loop ?? 1) ? true : false;
      $('show_arrows').checked = (r.show_arrows ?? 1) ? true : false;
      $('show_dots').checked = (r.show_dots ?? 1) ? true : false;

      $('publish_at').value = toDatetimeLocal(r.publish_at);
      $('expire_at').value  = toDatetimeLocal(r.expire_at);

      const embedsRaw =
        r.iframe_urls_json ??
        r.iframe_urls ??
        r.iframes ??
        (r.metadata && r.metadata.iframes) ??
        [];

      renderEmbeds(normalizeEmbeds(embedsRaw));

      const metaTa = $('metadata_json');
      if (metaTa){
        metaTa.value = r.metadata ? JSON.stringify(r.metadata, null, 2) : '';
      }

      const identity = $('identityText');
      const updated = $('updatedText');
      const by = $('byText');

      if (identity){
        const idTxt = r.id ? `id: ${r.id}` : '';
        const uuTxt = r.uuid ? `uuid: ${r.uuid}` : '';
        identity.textContent = (idTxt && uuTxt) ? `${idTxt} • ${uuTxt}` : (idTxt || uuTxt || '—');
      }
      if (updated) updated.textContent = r.updated_at ? `Updated: ${r.updated_at}` : 'Updated: —';
      if (by) by.textContent = (r.created_by_name || r.created_by_email || '—');

      refreshRowMeta();
    }

    function readPayload(){
      const title = ($('title').value || '').trim();
      if (!title) return { ok:false, error:'Title is required.' };

      const embeds = readEmbedsFromRows();
      const iframe_urls_json = Array.isArray(embeds) ? embeds : [];

      const metaTxt = ($('metadata_json')?.value || '').trim();
      let metadata = null;
      if (metaTxt){
        const parsed = safeJsonParse(metaTxt, null, 'metadata must be valid JSON.');
        if (!parsed.ok) return { ok:false, error: parsed.error };
        metadata = parsed.val;
      }

      const payload = {
        // ✅ still sends department_id as number (backend expects same)
        department_id: ($('department_id').value || '').trim() ? Number($('department_id').value) : null,

        title: title,
        slug: ($('slug').value || '').trim() || null,
        description: ($('description').value || '').trim() || null,

        iframe_urls_json: iframe_urls_json,

        auto_scroll: $('auto_scroll').checked ? 1 : 0,
        scroll_latency_ms: Number($('scroll_latency_ms').value || 3000),
        loop: $('loop').checked ? 1 : 0,
        show_arrows: $('show_arrows').checked ? 1 : 0,
        show_dots: $('show_dots').checked ? 1 : 0,

        sort_order: Number($('sort_order').value || 0),
        status: ($('status').value || 'draft').trim(),

        publish_at: ($('publish_at').value || '').trim() || null,
        expire_at:  ($('expire_at').value || '').trim() || null,

        metadata: metadata
      };

      if (payload.department_id === null) delete payload.department_id;
      if (!payload.slug) delete payload.slug;
      if (!payload.description) delete payload.description;
      if (!payload.publish_at) delete payload.publish_at;
      if (!payload.expire_at) delete payload.expire_at;
      if (payload.metadata === null) delete payload.metadata;

      return { ok:true, payload };
    }

    async function loadCurrent(){
      showLoading(true);
      try{
        const sp = new URLSearchParams(window.location.search);
        const identifier = (sp.get('identifier') || sp.get('id') || sp.get('uuid') || sp.get('slug') || '').trim();

        let res, js;

        if (identifier){
          res = await fetchWithTimeout(API.show(identifier), { headers: authHeaders(false) }, 15000);
          if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }
          js = await res.json().catch(()=> ({}));
          if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to load');
          fillForm(js?.item || js?.data || js || {});
          return;
        }

        const url = API.list + '?per_page=1&sort=updated_at&direction=desc';
        res = await fetchWithTimeout(url, { headers: authHeaders(false) }, 15000);
        if (res.status === 401 || res.status === 403) { window.location.href = '/'; return; }
        js = await res.json().catch(()=> ({}));
        if (!res.ok || js.success === false) throw new Error(js?.message || 'Failed to load');

        const item = (js?.data && js.data[0]) ? js.data[0] : null;
        if (item) fillForm(item);
        else fillForm({
          title: '',
          description: '',
          iframe_urls_json: [],
          status: 'draft',
          sort_order: 0,
          auto_scroll: 1,
          scroll_latency_ms: 3000,
          loop: 1,
          show_arrows: 1,
          show_dots: 1,
          metadata: null
        });
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Failed to load'));
        fillForm({
          title: '',
          description: '',
          iframe_urls_json: [],
          status: 'draft',
          sort_order: 0,
          auto_scroll: 1,
          scroll_latency_ms: 3000,
          loop: 1,
          show_arrows: 1,
          show_dots: 1,
          metadata: null
        });
      }finally{
        showLoading(false);
      }
    }

    async function save(){
      if (!canWrite) return;

      const read = readPayload();
      if (!read.ok){ err(read.error); return; }

      setBtnLoading($('btnSave'), true);
      showLoading(true);

      try{
        const id = ($('currentUuid').value || $('currentId').value || '').trim();
        const isUpdate = !!id;

        const url = isUpdate ? API.update(id) : API.store;
        const method = isUpdate ? 'PUT' : 'POST';

        const res = await fetchWithTimeout(url, {
          method: method,
          headers: authHeaders(true),
          body: JSON.stringify(read.payload)
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

        ok('Saved');
        const item = js?.item || js?.data || null;
        if (item) fillForm(item);
        else await loadCurrent();
      }catch(ex){
        err(ex?.name === 'AbortError' ? 'Request timed out' : (ex.message || 'Save failed'));
      }finally{
        setBtnLoading($('btnSave'), false);
        showLoading(false);
      }
    }

    // -------- UI events --------
    $('btnAddEmbed')?.addEventListener('click', () => addEmbedRow());

    $('btnClearEmbeds')?.addEventListener('click', async () => {
      const conf = await Swal.fire({
        title: 'Clear all embeds?',
        text: 'This will remove all embed rows (not saved yet).',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Clear',
        confirmButtonColor: '#ef4444'
      });
      if (!conf.isConfirmed) return;
      renderEmbeds([]);
      ok('Embeds cleared (not saved).');
    });

    $('btnMetaPretty')?.addEventListener('click', () => prettyTextarea($('metadata_json')));

    $('embedsList')?.addEventListener('click', async (e) => {
      const btn = e.target.closest('button[data-action]');
      if (!btn) return;

      const row = btn.closest('[data-embed-row="1"]');
      if (!row) return;

      const action = btn.dataset.action;

      if (action === 'remove'){
        const conf = await Swal.fire({
          title: 'Remove this embed?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Remove'
        });
        if (!conf.isConfirmed) return;

        row.remove();
        ensureEmptyState();
        refreshRowMeta();
        initSortable();
        return;
      }

      if (action === 'up' || action === 'down'){
        moveRow(row, action);
        return;
      }

      if (action === 'preview'){
        const wrap = row.querySelector('[data-preview-wrap]');
        if (!wrap) return;

        wrap.style.display = (wrap.style.display === 'none' || !wrap.style.display) ? '' : 'none';

        const raw = (row.querySelector('textarea[data-field="embed"]')?.value ?? '').toString().trim();
        const src = extractIframeSrc(raw) || raw;
        const embedUrl = toNoCookieEmbedUrl(src);
        const vid = youtubeIdFromUrl(embedUrl);

        const thumb = row.querySelector('[data-thumb]');
        const iframeBox = row.querySelector('[data-iframe]');
        const loadBtn = row.querySelector('button[data-action="load-iframe"]');

        if (thumb && vid){
          thumb.style.display = '';
          const img = thumb.querySelector('img');
          if (img) img.src = thumbUrlForYoutube(vid);
        } else if (thumb){
          thumb.style.display = 'none';
        }

        if (iframeBox) iframeBox.style.display = 'none';
        if (loadBtn) loadBtn.style.display = embedUrl ? '' : 'none';
        return;
      }

      if (action === 'load-iframe'){
        const raw = (row.querySelector('textarea[data-field="embed"]')?.value ?? '').toString().trim();
        const src = extractIframeSrc(raw) || raw;
        const embedUrl = toNoCookieEmbedUrl(src);
        if (!embedUrl) return;

        const iframeBox = row.querySelector('[data-iframe]');
        const ifr = iframeBox ? iframeBox.querySelector('iframe') : null;
        if (ifr){
          ifr.src = embedUrl;
          iframeBox.style.display = '';
        }
        return;
      }

      if (action === 'hide-preview'){
        const wrap = row.querySelector('[data-preview-wrap]');
        if (wrap) wrap.style.display = 'none';
        const iframeBox = row.querySelector('[data-iframe]');
        const ifr = iframeBox ? iframeBox.querySelector('iframe') : null;
        if (ifr) ifr.src = 'about:blank';
        return;
      }
    });

    $('embedsList')?.addEventListener('input', (e) => {
      const inRow = e.target.closest('[data-embed-row="1"]');
      if (!inRow) return;
      refreshRowMeta();
    });

    $('btnDefaults')?.addEventListener('click', () => {
      $('title').value = 'Alumni Speak';
      $('description').value = 'Stories, journeys, and experiences from our alumni community.';
      renderEmbeds([{ title:'Alumni Talk', url:'https://www.youtube.com/watch?v=dQw4w9WgXcQ' }]);

      $('status').value = 'draft';
      $('sort_order').value = 0;

      $('auto_scroll').checked = true;
      $('loop').checked = true;
      $('show_arrows').checked = true;
      $('show_dots').checked = true;
      $('scroll_latency_ms').value = 3000;

      // leave department blank by default
      $('department_id') && ($('department_id').value = '');

      ok('Defaults applied (not saved).');
      refreshRowMeta();
    });

    $('btnReload')?.addEventListener('click', async () => {
      await loadDepartments(); // ✅ keeps dropdown fresh
      await loadCurrent();
      ok('Reloaded');
    });

    $('btnSave')?.addEventListener('click', save);

    // init
    (async () => {
      showLoading(true);
      try{
        await fetchMe();
        await loadDepartments(); // ✅ load department dropdown before filling form
        await loadCurrent();
      }catch(ex){
        err(ex?.message || 'Initialization failed');
      }finally{
        showLoading(false);
      }
    })();
  });
})();
</script>
@endpush
