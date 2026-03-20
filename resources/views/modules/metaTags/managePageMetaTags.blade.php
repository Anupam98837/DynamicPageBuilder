{{-- resources/views/modules/metaTags/managePageMetaTags.blade.php --}}
@php
  use Illuminate\Support\Facades\DB;

  // page identifier comes from editor url: ?uuid=....
  $pageUuid = trim((string) request()->query('uuid', ''));

  $page = null;
  if ($pageUuid !== '') {
    $q = DB::table('pages')->select('id','uuid','title','page_title');
    // soft delete safe
    try { if (\Illuminate\Support\Facades\Schema::hasColumn('pages','deleted_at')) $q->whereNull('deleted_at'); } catch (\Throwable $e) {}

    // uuid match first
    $page = $q->where('uuid', $pageUuid)->first();

    // fallback if numeric passed
    if (!$page && preg_match('/^\d+$/', $pageUuid)) {
      $page = $q->where('id', (int)$pageUuid)->first();
    }
  }

  $lockedPageId    = $page->id ?? null;
  $lockedPageTitle = ($page->page_title ?? '') ?: (($page->title ?? '') ?: 'Untitled Page');

  $mtUid = 'mtg_' . \Illuminate\Support\Str::random(8);

  $apiMetaTypes        = url('/api/meta-tags/types');
  $apiMetaList         = url('/api/meta-tags');       // GET ?page_id=
  $apiMetaBulkSave     = url('/api/meta-tags/bulk');  // POST {page_id, tags[]}
  $apiMetaDeleteById   = url('/api/meta-tags');       // DELETE /{id}
@endphp

<style>
  .mtg-wrap{max-width:1200px;margin:0 auto;padding:0 2px;overflow:visible}
  .mtg-panel{background:var(--surface);border:1px solid var(--line-strong);border-radius:16px;box-shadow:var(--shadow-2);padding:12px;}
  .mtg-card{border:1px solid var(--line-strong);border-radius:16px;background:var(--surface);box-shadow:var(--shadow-2);overflow:visible;}
  .mtg-card .card-header{background:transparent;border-bottom:1px solid var(--line-soft)}
  .loading-overlay{position:fixed; inset:0;background:rgba(0,0,0,.45);display:none;justify-content:center;align-items:center;z-index:9999;backdrop-filter:blur(2px)}
  .loading-overlay.is-show{display:flex}
  .count-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:999px;background:color-mix(in oklab, var(--primary-color) 12%, transparent);color:var(--primary-color);font-weight:900;font-size:12px;white-space:nowrap}
  .text-mini{font-size:12px;color:var(--muted-color)}
  .mtg-toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
  .mtg-toolbar .left{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
  .mtg-toolbar .right{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
  .mtg-formrow{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end}
  .mtg-formrow .fg{min-width:240px;flex:1}
  .mtg-formrow label{font-weight:900;font-size:12px;color:var(--muted-color);margin-bottom:6px}
  .mtg-formrow input, .mtg-formrow select{width:100%;border:1px solid var(--line-strong);background:var(--surface);color:var(--ink);border-radius:12px;padding:10px 12px;outline:none;}
  .mtg-formrow input[readonly], .mtg-formrow select[disabled]{opacity:.85; cursor:not-allowed;}
  .mtg-table-wrap{border:1px solid var(--line-soft);border-radius:14px;overflow:auto;max-width:100%}
  .mtg-table{width:100%;min-width:1080px;margin:0}
  .mtg-table thead th{position:sticky;top:0;background:var(--surface);z-index:3;border-bottom:1px solid var(--line-strong);font-size:12px;text-transform:uppercase;letter-spacing:.04em}
  .mtg-table th,.mtg-table td{vertical-align:top;padding:12px 12px;border-bottom:1px solid var(--line-soft)}
  .mtg-table tbody tr:hover{background:var(--page-hover)}
  .mtg-table select, .mtg-table textarea{width:100%;border:1px solid var(--line-strong);background:var(--surface);color:var(--ink);border-radius:12px;padding:10px 12px;outline:none;}
  .mtg-table textarea.js-content{min-height:110px;height:110px;resize:vertical;line-height:1.35;white-space:pre-wrap;overflow-wrap:anywhere;}
  .code-pill{display:flex;align-items:center;gap:8px;border:1px dashed var(--line-soft);border-radius:12px;padding:8px 10px;background:color-mix(in oklab, var(--primary-color) 6%, var(--surface));max-width:100%;}
  .code-pill code{font-family:ui-monospace, Menlo, Monaco, Consolas, "Courier New", monospace;font-size:12px;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1;min-width:0;}
  .code-copy{width:30px;height:30px;border-radius:10px;border:1px solid var(--line-strong);background:var(--surface);cursor:pointer;flex:0 0 auto;}
  .mtg-row-actions{display:flex;gap:8px;align-items:center;justify-content:flex-end}
  .icon-btn{display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:12px;border:1px solid var(--line-strong);background:var(--surface);color:var(--ink);box-shadow:var(--shadow-sm);cursor:pointer;transition:transform .15s ease;}
  .icon-btn:hover{transform:translateY(-1px)}
  .icon-btn.danger{border-color:rgba(239,68,68,.45)}
  .empty-state{text-align:center;padding:42px 20px}
  .empty-state i{font-size:48px;color:var(--muted-color);margin-bottom:16px;opacity:.6}
  .empty-state .title{font-weight:950;color:var(--ink);margin-bottom:8px}
  .empty-state .subtitle{font-size:14px;color:var(--muted-color)}
  .row-error{background: rgba(239,68,68,.08) !important;}
  .row-error td{border-bottom-color: rgba(239,68,68,.30) !important;}
  .row-error .mtg-err{color:#b91c1c;font-weight:900;font-size:12px;margin-top:6px}
  .id-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:999px;background:rgba(16,185,129,.14);color:#059669;border:1px solid rgba(16,185,129,.35);font-weight:900;font-size:12px;white-space:nowrap;}
  .mtg-alert{border:1px solid rgba(239,68,68,.25);background:rgba(239,68,68,.07);border-radius:14px;padding:12px 12px;color:var(--ink);}
</style>

<div class="mtg-wrap" id="{{ $mtUid }}">

  <div id="{{ $mtUid }}_globalLoading" class="loading-overlay">
    @include('partials.overlay')
  </div>

  @if(request()->query('uuid') && !$lockedPageId)
    <div class="mtg-alert mb-3">
      <div class="fw-bold"><i class="fa fa-triangle-exclamation me-1"></i> Page not found</div>
      <div class="text-mini mt-1">
        No page found for <b>?uuid={{ request()->query('uuid') }}</b>. Save page / open edit link with valid uuid.
      </div>
    </div>
  @endif

  <div class="mtg-panel mb-3">
    <div class="mtg-toolbar">
      <div class="left">
        <div class="fw-semibold"><i class="fa fa-tags me-2"></i>Meta Tags Manager</div>
        <span class="count-badge" id="{{ $mtUid }}_tagBadge">—</span>
      </div>
      <div class="right">
        <button id="{{ $mtUid }}_btnAddRow" class="btn btn-light">
          <i class="fa fa-plus me-1"></i>Add tag
        </button>
        <button id="{{ $mtUid }}_btnSaveAll" class="btn btn-primary">
          <i class="fa fa-floppy-disk me-1"></i>Save all
        </button>
      </div>
    </div>

    <div class="mtg-formrow mt-3">
      <div class="fg" style="min-width:320px;">
        <label>Page (auto from editor URL)</label>
        <select id="{{ $mtUid }}_pageIdSelect" disabled>
          @if($lockedPageId)
            <option value="{{ $lockedPageId }}" selected>#{{ $lockedPageId }} — {{ $lockedPageTitle }}</option>
          @else
            <option value="">— Save page / open with ?uuid=... —</option>
          @endif
        </select>
        <div class="text-mini mt-2">
          Only <b>page_id</b> is used. To manage another page, open that page editor.
        </div>
      </div>

      <div class="fg" style="min-width:320px;">
        <label>Page link (disabled)</label>
        <input id="{{ $mtUid }}_pageLink" type="text" value="" readonly placeholder="(not used)" />
        <div class="text-mini mt-2">Not used. Tags are stored only by <b>page_id</b>.</div>
      </div>

      <div class="fg" style="min-width:260px;max-width:360px;">
        <label>Quick presets</label>
        <select id="{{ $mtUid }}_presetSelect" @if(!$lockedPageId) disabled @endif>
          <option value="">— Select preset to add —</option>
          <option value="seo_basic">SEO Basic (description + robots)</option>
          <option value="social_basic">Social Basic (og:title/desc + twitter card)</option>
          <option value="charset_viewport">Charset + Viewport</option>
        </select>
        <div class="text-mini mt-2">Adds multiple tags at once for this page.</div>
      </div>
    </div>

    <div class="text-mini mt-3" id="{{ $mtUid }}_summaryText">—</div>
  </div>

  <div class="card mtg-card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="fw-semibold"><i class="fa fa-layer-group me-2"></i>Tags for this Page</div>
      <div class="small text-muted">Meta Tag Type → Attribute → Content. Charset disables Attribute and defaults Content to UTF-8 (editable).</div>
    </div>

    <div class="card-body">
      <div id="{{ $mtUid }}_emptyState" class="empty-state">
        <i class="fa fa-circle-info"></i>
        <div class="title">No tags loaded</div>
        <div class="subtitle">
          @if($lockedPageId)
            Tags auto-load for this page. Click <b>Add tag</b> to start.
          @else
            Save the page first, then reopen in edit mode (<b>?uuid=...</b>).
          @endif
        </div>
      </div>

      <div id="{{ $mtUid }}_tableWrap" class="mtg-table-wrap" style="display:none;">
        <table class="table mtg-table">
          <thead>
            <tr>
              <th style="width:200px;">Meta Tag Type</th>
              <th style="width:260px;">Attribute</th>
              <th style="min-width:440px;">Content</th>
              <th style="width:360px;">Preview</th>
              <th style="width:170px;text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody id="{{ $mtUid }}_tbodyRows"></tbody>
        </table>
      </div>

      <div class="text-mini mt-3">
        <i class="fa fa-shield-halved me-1" style="opacity:.8"></i>
        Saved tags are stored against <b>page_id</b> (FK).
      </div>
    </div>
  </div>

  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
    <div id="{{ $mtUid }}_toastSuccess" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="{{ $mtUid }}_toastSuccessText">Done</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
    <div id="{{ $mtUid }}_toastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="{{ $mtUid }}_toastErrorText">Something went wrong</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>

</div>

<script>
(() => {
  const UID = @json($mtUid);

  const LOCKED_PAGE_ID = @json($lockedPageId);

  const API = {
    types: () => @json($apiMetaTypes),
    list: (pageId) => @json($apiMetaList) + '?page_id=' + encodeURIComponent(String(pageId || '')),
    saveBulk: () => @json($apiMetaBulkSave),
    delete: (id) => @json($apiMetaDeleteById) + '/' + encodeURIComponent(String(id)),
  };

  const $ = (suffix) => document.getElementById(`${UID}_${suffix}`);
  function cleanStr(v){ return (v === null || v === undefined) ? '' : String(v).trim(); }
  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }
  function normalizeList(js){
    if (!js) return [];
    if (Array.isArray(js)) return js;
    if (Array.isArray(js.data)) return js.data;
    if (Array.isArray(js.types)) return js.types;
    if (js.data && Array.isArray(js.data.data)) return js.data.data;
    if (Array.isArray(js.items)) return js.items;
    if (Array.isArray(js.tags)) return js.tags;
    return [];
  }

  const token = () => (sessionStorage.getItem('token') || localStorage.getItem('token') || '');
  function authHeaders(extra={}){
    return Object.assign({ 'Authorization': 'Bearer ' + token(), 'Accept': 'application/json' }, extra);
  }
  async function fetchWithTimeout(url, opts={}, ms=15000){
    const ctrl = new AbortController();
    const t = setTimeout(()=>ctrl.abort(), ms);
    try{ return await fetch(url, { ...opts, signal: ctrl.signal }); }
    finally{ clearTimeout(t); }
  }
  function showLoading(on){ $(`globalLoading`)?.classList.toggle('is-show', !!on); }

  const toastOkEl  = $(`toastSuccess`);
  const toastErrEl = $(`toastError`);
  const toastOk  = (window.bootstrap && toastOkEl) ? new bootstrap.Toast(toastOkEl) : null;
  const toastErr = (window.bootstrap && toastErrEl) ? new bootstrap.Toast(toastErrEl) : null;
  const ok  = (m) => { $(`toastSuccessText`).textContent = m || 'Done'; toastOk && toastOk.show(); };
  const err = (m) => { $(`toastErrorText`).textContent = m || 'Something went wrong'; toastErr && toastErr.show(); };

  const FALLBACK_TYPE_DEFS = [
    { typeKey:'charset',   label:'Charset',                attrName:'charset',   attributes:[] },

    { typeKey:'standard',  label:'Standard (name)',        attrName:'name',      attributes:[
      'description','keywords','robots','googlebot','bingbot',
      'viewport','theme-color','color-scheme','referrer','format-detection',
      'application-name','generator',
      'apple-mobile-web-app-title','apple-mobile-web-app-capable','apple-mobile-web-app-status-bar-style',
      'mobile-web-app-capable',
      'google-site-verification','msvalidate.01','yandex-verification','baidu-site-verification',
      'facebook-domain-verification','p:domain_verify','norton-safeweb-site-verification',
      'author','publisher','copyright','rating','distribution','revisit-after','language',
      'HandheldFriendly','MobileOptimized',
      'msapplication-TileColor','msapplication-config','msapplication-navbutton-color','msapplication-starturl'
    ]},

    { typeKey:'opengraph', label:'Open Graph (property)',  attrName:'property',  attributes:[
      'og:title','og:description','og:url','og:type','og:site_name','og:locale',
      'og:locale:alternate','og:determiner','og:updated_time','og:see_also',

      'og:image','og:image:secure_url','og:image:url',
      'og:image:type','og:image:width','og:image:height','og:image:alt',

      'og:video','og:video:secure_url','og:video:type','og:video:width','og:video:height',
      'og:audio','og:audio:secure_url','og:audio:type',

      'fb:app_id','fb:admins',

      'article:published_time','article:modified_time','article:expiration_time',
      'article:author','article:section','article:tag',

      'product:price:amount','product:price:currency'
    ]},

    { typeKey:'twitter',   label:'Twitter (name)',         attrName:'name',      attributes:[
      'twitter:card','twitter:title','twitter:description','twitter:site','twitter:creator',

      'twitter:image','twitter:image:alt',

      'twitter:url','twitter:domain',

      'twitter:player','twitter:player:width','twitter:player:height',

      'twitter:app:name:iphone','twitter:app:id:iphone','twitter:app:url:iphone',
      'twitter:app:name:ipad','twitter:app:id:ipad','twitter:app:url:ipad',
      'twitter:app:name:googleplay','twitter:app:id:googleplay','twitter:app:url:googleplay',

      'twitter:label1','twitter:data1','twitter:label2','twitter:data2'
    ]},

    { typeKey:'http',      label:'HTTP-Equiv',             attrName:'http-equiv',attributes:[
      'refresh','content-security-policy','x-ua-compatible',
      'cache-control','expires','pragma','default-style'
    ]},
  ];

  const state = {
    typeDefs: [...FALLBACK_TYPE_DEFS],
    pageId: LOCKED_PAGE_ID ? String(LOCKED_PAGE_ID) : '',
    rows: [],
    loadedOnce: false
  };

  function randKey(){ return Math.random().toString(16).slice(2) + '_' + Date.now().toString(16); }
  function getTypeDef(typeKey){ return state.typeDefs.find(t => String(t.typeKey) === String(typeKey)) || null; }

  function normalizeTypeKey(rawType, rawAttr){
    const t = cleanStr(rawType).toLowerCase();
    const a = cleanStr(rawAttr).toLowerCase();

    if (t === 'charset' || a === 'charset') return 'charset';
    if (t === 'standard') return 'standard';
    if (t === 'opengraph' || t === 'open_graph' || t === 'og') return 'opengraph';
    if (t === 'twitter') return 'twitter';
    if (t === 'http' || t === 'http-equiv' || t === 'http_equiv') return 'http';
    if (t === 'name') return (a.startsWith('twitter:') ? 'twitter' : 'standard');
    if (t === 'property') return 'opengraph';
    if (a.startsWith('og:') || a.startsWith('fb:') || a.startsWith('article:') || a.startsWith('product:')) return 'opengraph';
    if (a.startsWith('twitter:')) return 'twitter';

    return t || 'standard';
  }

  function buildMetaPreview(row){
    const def = getTypeDef(row.typeKey);
    if (!def) return '';
    const content = (row.content ?? '').toString().trim();
    if (def.attrName === 'charset' || row.typeKey === 'charset'){
      return `<meta charset="${esc(content || 'UTF-8')}">`;
    }
    const attrVal = (row.attribute ?? '').toString().trim();
    if (!attrVal) return '';
    return `<meta ${def.attrName}="${esc(attrVal)}" content="${esc(content)}">`;
  }

  function updateTopSummary(){
    const cnt = state.rows.length;
    $(`tagBadge`).textContent = cnt ? `Tags: ${cnt}` : '—';
    const pid = cleanStr(state.pageId);
    $(`summaryText`).textContent = pid
      ? (state.loadedOnce ? `Loaded ${cnt} tag(s) for Page ID: ${pid}` : `Ready to manage tags for Page ID: ${pid}`)
      : 'Save the page first, then reopen in edit mode (?uuid=...).';
  }

  function syncEmptyState(){
    const hasRows = state.rows.length > 0;
    $(`emptyState`).style.display = hasRows ? 'none' : '';
    $(`tableWrap`).style.display = hasRows ? '' : 'none';
  }

  function renderRows(){
    const tb = $(`tbodyRows`);
    tb.innerHTML = state.rows.map((row) => {
      const def = getTypeDef(row.typeKey);
      const isCharset = (row.typeKey === 'charset' || def?.attrName === 'charset');
      const attrs = def ? (def.attributes || []) : [];
      const preview = buildMetaPreview(row);

      const rowAttr = cleanStr(row.attribute);
      const hasAttrInList = rowAttr && attrs.includes(rowAttr);
      const customOpt = (!isCharset && rowAttr && !hasAttrInList)
        ? `<option value="${esc(rowAttr)}" selected>${esc(rowAttr)}</option>`
        : '';

      const errMsg = row._err ? `<div class="mtg-err"><i class="fa fa-triangle-exclamation me-1"></i>${esc(row._err)}</div>` : '';

      return `
        <tr class="${row._err ? 'row-error' : ''}" data-k="${esc(row._k)}">
          <td>
            <select class="js-type" data-k="${esc(row._k)}">
              ${state.typeDefs.map(t => `<option value="${esc(t.typeKey)}" ${String(t.typeKey)===String(row.typeKey) ? 'selected' : ''}>${esc(t.label)}</option>`).join('')}
            </select>
            ${row.id ? `<div class="mt-2"><span class="id-badge"><i class="fa fa-check"></i>ID #${esc(String(row.id))}</span></div>` : ``}
          </td>

          <td>
            <select class="js-attr" data-k="${esc(row._k)}" ${isCharset ? 'disabled' : ''}>
              <option value="">— Select attribute —</option>
              ${customOpt}
              ${attrs.map(a => `<option value="${esc(a)}" ${String(a)===String(rowAttr) ? 'selected' : ''}>${esc(a)}</option>`).join('')}
            </select>
          </td>

          <td>
            <textarea class="js-content" data-k="${esc(row._k)}" placeholder="${isCharset ? 'UTF-8' : 'Enter content'}">${esc(row.content ?? '')}</textarea>
            ${errMsg}
          </td>

          <td>
            <div class="code-pill">
              <code class="js-preview" data-k="${esc(row._k)}">${esc(preview || '—')}</code>
              <button class="code-copy js-copy" type="button" data-k="${esc(row._k)}" title="Copy">
                <i class="fa-regular fa-copy"></i>
              </button>
            </div>
            <div class="text-mini mt-2">Attribute name: <b>${esc(def?.attrName || '—')}</b></div>
          </td>

          <td>
            <div class="mtg-row-actions">
              <button class="icon-btn js-dup" type="button" data-k="${esc(row._k)}" title="Duplicate row"><i class="fa fa-clone"></i></button>
              ${row.id ? `
                <button class="icon-btn danger js-del-db" type="button" data-k="${esc(row._k)}" title="Delete from database"><i class="fa fa-trash"></i></button>
              ` : `
                <button class="icon-btn danger js-del-local" type="button" data-k="${esc(row._k)}" title="Remove row"><i class="fa fa-xmark"></i></button>
              `}
            </div>
          </td>
        </tr>
      `;
    }).join('');

    syncEmptyState();
    updateTopSummary();
  }

  function addRow(partial={}){
    const row = Object.assign({
      _k: randKey(),
      id: null,
      typeKey: 'standard',
      attribute: 'description',
      content: '',
      _err: '',
    }, partial);

    if (row.typeKey === 'charset' && !cleanStr(row.content)){
      row.content = 'UTF-8';
      row.attribute = '';
    }

    state.rows.push(row);
    renderRows();
  }

  function duplicateRow(k){
    const r = state.rows.find(x => x._k === k);
    if (!r) return;
    addRow({ id:null, typeKey:r.typeKey, attribute:r.attribute, content:r.content });
  }

  function removeLocalRow(k){
    state.rows = state.rows.filter(x => x._k !== k);
    renderRows();
  }

  function mapServerTagToRow(t){
    const rawAttr = cleanStr(t.attribute ?? t.tag_attribute ?? t.attr_value ?? t.key ?? '');
    const rawType = cleanStr(t.tag_type ?? t.type ?? t.meta_tag_type ?? t.typeKey ?? t.key ?? '');
    const typeKey = normalizeTypeKey(rawType, rawAttr);

    let attribute = cleanStr(t.attribute ?? t.tag_attribute ?? t.attr_value ?? t.key ?? '');
    let content   = cleanStr(t.content ?? t.tag_attribute_value ?? t.value ?? t.charset ?? '');

    if (typeKey === 'charset'){
      attribute = '';
      if (!content) content = 'UTF-8';
    }

    return { _k: randKey(), id: t.id ?? null, typeKey, attribute, content, _err:'' };
  }

  async function loadTypes(){
    try{
      const res = await fetchWithTimeout(API.types(), { headers: authHeaders() }, 15000);
      const js = await res.json().catch(()=> ({}));
      const list = normalizeList(js);

      if (Array.isArray(list) && list.length){
        state.typeDefs = list.map(t => ({
          typeKey: t.typeKey ?? t.key ?? t.type ?? t.tag_type ?? 'standard',
          label: t.label ?? t.name ?? 'Type',
          attrName: t.attrName ?? t.attr_name ?? t.attribute_name ?? (String((t.typeKey || t.key || 'standard')) === 'charset' ? 'charset' : 'name'),
          attributes: Array.isArray(t.attributes) ? t.attributes : [],
        }));
      } else {
        state.typeDefs = [...FALLBACK_TYPE_DEFS];
      }
    }catch(_){
      state.typeDefs = [...FALLBACK_TYPE_DEFS];
    }
  }

  async function loadTags(){
    const pid = cleanStr(state.pageId);
    if (!pid) { state.rows=[]; state.loadedOnce=false; renderRows(); return; }

    showLoading(true);
    try{
      const res = await fetchWithTimeout(API.list(pid), { headers: authHeaders() }, 20000);
      const js = await res.json().catch(()=> ({}));
      if (!res.ok) throw new Error(js?.message || 'Failed to load tags');

      const list = normalizeList(js);
      state.rows = (list || []).map(mapServerTagToRow);
      state.loadedOnce = true;
      renderRows();
    }catch(ex){
      err(ex?.message || 'Load failed');
    }finally{
      showLoading(false);
    }
  }

  function validateAll(){
    state.rows.forEach(r => r._err = '');
    const pid = cleanStr(state.pageId);
    if (!pid) { err('Page ID missing. Save the page first.'); return false; }
    if (!state.rows.length) { err('Add at least one meta tag.'); return false; }

    let bad = null;
    state.rows.forEach(r => {
      const def = getTypeDef(r.typeKey);
      const isCharset = (r.typeKey === 'charset' || def?.attrName === 'charset');
      if (isCharset) {
        if (!cleanStr(r.content)) { r._err='Charset value required'; bad ||= r._k; }
        return;
      }
      if (!cleanStr(r.attribute)) { r._err='Select an attribute'; bad ||= r._k; }
      else if (!cleanStr(r.content)) { r._err='Content is required'; bad ||= r._k; }
    });

    renderRows();
    if (bad){ err('Fix highlighted rows.'); return false; }
    return true;
  }

  function buildPayload(){
    return {
      page_id: parseInt(state.pageId, 10),
      page_link: null,
      tags: state.rows.map(r => ({
        id: r.id || null,
        tag_type: cleanStr(r.typeKey),
        attribute: cleanStr(r.attribute),
        content: cleanStr(r.content),
      }))
    };
  }

  async function saveAll(){
    if (!validateAll()) return;

    showLoading(true);
    try{
      const res = await fetchWithTimeout(API.saveBulk(), {
        method:'POST',
        headers: authHeaders({ 'Content-Type':'application/json' }),
        body: JSON.stringify(buildPayload())
      }, 25000);

      const js = await res.json().catch(()=> ({}));
      if (!res.ok || js?.success === false) throw new Error(js?.message || 'Save failed');

      ok('Saved');
      await loadTags();
    }catch(ex){
      err(ex?.message || 'Save failed');
    }finally{
      showLoading(false);
    }
  }

  async function deleteRowFromDb(k){
    const r = state.rows.find(x => x._k === k);
    if (!r || !r.id){ removeLocalRow(k); return; }

    showLoading(true);
    try{
      const res = await fetchWithTimeout(API.delete(r.id), { method:'DELETE', headers: authHeaders() }, 20000);
      const js = await res.json().catch(()=> ({}));
      if (!res.ok || js?.success === false) throw new Error(js?.message || 'Delete failed');

      ok('Deleted');
      state.rows = state.rows.filter(x => x._k !== k);
      renderRows();
    }catch(ex){
      err(ex?.message || 'Delete failed');
    }finally{
      showLoading(false);
    }
  }

  function applyPreset(preset){
    if (!cleanStr(state.pageId)) { err('Save page first.'); $(`presetSelect`).value=''; return; }
    if (preset === 'seo_basic'){
      addRow({ typeKey:'standard', attribute:'description', content:'' });
      addRow({ typeKey:'standard', attribute:'robots', content:'index,follow' });
      ok('Added SEO preset');
    } else if (preset === 'social_basic'){
      addRow({ typeKey:'opengraph', attribute:'og:title', content:'' });
      addRow({ typeKey:'opengraph', attribute:'og:description', content:'' });
      addRow({ typeKey:'twitter', attribute:'twitter:card', content:'summary_large_image' });
      ok('Added Social preset');
    } else if (preset === 'charset_viewport'){
      addRow({ typeKey:'charset', content:'UTF-8' });
      addRow({ typeKey:'standard', attribute:'viewport', content:'width=device-width, initial-scale=1' });
      ok('Added Charset+Viewport preset');
    }
    $(`presetSelect`).value = '';
  }

  function copyToClipboard(text){
    const t = (text ?? '').toString();
    if (!t.trim()) return;
    if (navigator.clipboard?.writeText){
      navigator.clipboard.writeText(t).then(()=>ok('Copied')).catch(()=>err('Copy failed'));
      return;
    }
    const ta=document.createElement('textarea'); ta.value=t; document.body.appendChild(ta); ta.select();
    try{ document.execCommand('copy'); ok('Copied'); } catch(_){ err('Copy failed'); }
    document.body.removeChild(ta);
  }

  function bindUI(){
    $(`btnAddRow`).addEventListener('click', () => addRow());
    $(`btnSaveAll`).addEventListener('click', saveAll);

    $(`presetSelect`)?.addEventListener('change', (e) => {
      const v = e.target.value; if (!v) return;
      applyPreset(v);
    });

    document.addEventListener('change', (e) => {
      const typeSel = e.target.closest(`#${CSS.escape(UID)} select.js-type[data-k]`);
      if (typeSel){
        const k = typeSel.dataset.k;
        const r = state.rows.find(x => x._k === k);
        if (!r) return;

        r.typeKey = typeSel.value;
        const def = getTypeDef(r.typeKey);
        const isCharset = (r.typeKey === 'charset' || def?.attrName === 'charset');

        if (isCharset){ r.attribute=''; if(!cleanStr(r.content)) r.content='UTF-8'; }
        else {
          const list = def?.attributes || [];
          const cur = cleanStr(r.attribute);
          r.attribute = list.includes(cur) ? cur : (list[0] || '');
        }
        r._err='';
        renderRows();
        return;
      }

      const attrSel = e.target.closest(`#${CSS.escape(UID)} select.js-attr[data-k]`);
      if (attrSel){
        const k = attrSel.dataset.k;
        const r = state.rows.find(x => x._k === k);
        if (!r) return;
        r.attribute = attrSel.value;
        r._err='';
        renderRows();
      }
    });

    document.addEventListener('input', (e) => {
      const ta = e.target.closest(`#${CSS.escape(UID)} textarea.js-content[data-k]`);
      if (!ta) return;
      const k = ta.dataset.k;
      const r = state.rows.find(x => x._k === k);
      if (!r) return;

      r.content = ta.value;
      r._err = '';

      const code = document.querySelector(`#${CSS.escape(UID)} code.js-preview[data-k="${CSS.escape(k)}"]`);
      if (code) code.textContent = buildMetaPreview(r) || '—';
    });

    document.addEventListener('click', (e) => {
      const copyBtn = e.target.closest(`#${CSS.escape(UID)} button.js-copy[data-k]`);
      if (copyBtn){
        const k = copyBtn.dataset.k;
        const r = state.rows.find(x => x._k === k);
        if (!r) return;
        copyToClipboard(buildMetaPreview(r));
        return;
      }
      const dupBtn = e.target.closest(`#${CSS.escape(UID)} button.js-dup[data-k]`);
      if (dupBtn){ duplicateRow(dupBtn.dataset.k); return; }
      const delLocalBtn = e.target.closest(`#${CSS.escape(UID)} button.js-del-local[data-k]`);
      if (delLocalBtn){ removeLocalRow(delLocalBtn.dataset.k); return; }
      const delDbBtn = e.target.closest(`#${CSS.escape(UID)} button.js-del-db[data-k]`);
      if (delDbBtn){ deleteRowFromDb(delDbBtn.dataset.k); return; }
    });
  }

  document.addEventListener('DOMContentLoaded', async () => {
    if (!token()) return;

    try{
      await loadTypes();
      bindUI();
      updateTopSummary();
      syncEmptyState();

      const hasPid = !!cleanStr(state.pageId);
      $(`btnAddRow`).disabled = !hasPid;
      $(`btnSaveAll`).disabled = !hasPid;

      if (hasPid) await loadTags();
    }catch(_){}
  });
})();
</script>