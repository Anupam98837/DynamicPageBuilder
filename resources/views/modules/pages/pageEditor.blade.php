{{-- resources/views/modules/pages/createPage.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Page</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

  <style>
    body.bg-light{ background: var(--bg, #f6f7fb) !important; }

    .page-wrap{max-width:1250px;margin:0 auto 50px;padding:0 10px}
    .topbar-shell{position:sticky;top:0;z-index:1200;width:100%;padding:0;background:transparent;}
    .blog-topbar{
      width:100%;
      background:rgb(255, 255, 255);
      backdrop-filter: blur(12px);
      border-bottom:1px solid rgba(0,0,0,.07);
      padding:16px 0;
      box-shadow: 0 4px 20px rgba(0,0,0,.03);
    }
    html.theme-dark .blog-topbar{background:rgba(18,18,18,.85);border-bottom:1px solid rgba(255,255,255,.1);}
    .blog-topbar-inner{max-width:1250px;margin:0 auto;padding:0 10px;}

    .top-title{
      font-weight:800;
      letter-spacing:.2px;
      background: linear-gradient(135deg, var(--primary-color, #951eaa) 0%, #6a11cb 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .mini-help{font-size:.92rem;color:#6b7280;opacity:.85;}
    html.theme-dark .mini-help{color:rgba(255,255,255,.7)}

    .saving-indicator{
      display:inline-flex;align-items:center;gap:8px;font-size:.9rem;padding:6px 12px;
      background: rgba(255,255,255,.7);
      border-radius: 20px;border: 1px solid rgba(0,0,0,.05);
      transition: all .2s ease;
    }
    html.theme-dark .saving-indicator{background: rgba(255,255,255,.08);border-color: rgba(255,255,255,.1);}
    .saving-indicator .icon{animation:pulse 1.5s infinite;font-size:.85rem;}
    @keyframes pulse{0%{opacity:.5}50%{opacity:1}100%{opacity:.5}}
    .saving-indicator.saving{background: rgba(13, 110, 253, .1);border-color: rgba(13, 110, 253, .2);color: #0d6efd;}
    .saving-indicator.saved{background: rgba(25, 135, 84, .1);border-color: rgba(25, 135, 84, .2);color: #198754;}
    .saving-indicator.error{background: rgba(220, 53, 69, .1);border-color: rgba(220, 53, 69, .2);color: #dc3545;}

    .cardx{
      background: var(--surface, #fff);
      border: 1px solid var(--line-strong, rgba(0,0,0,.08));
      border-radius: 18px;
      box-shadow: var(--shadow-2, 0 10px 30px rgba(0,0,0,.06));
      overflow:hidden;
      margin-top: 18px;
    }
    .cardx .head{
      padding:16px 20px;
      display:flex;align-items:center;justify-content:space-between;
      border-bottom:1px solid var(--line-strong, rgba(0,0,0,.08));
      background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(255,255,255,.95));
    }
    html.theme-dark .cardx .head{background: linear-gradient(180deg, rgba(22,22,22,.95), rgba(22,22,22,.9));}
    .cardx .body{ padding:24px 20px; }

    .form-section{ margin-bottom: 28px; }
    .form-section-title{
      font-size: 1.1rem;font-weight: 600;color: var(--text-primary, #111827);
      margin-bottom: 16px;padding-bottom: 8px;border-bottom: 2px solid rgba(149, 30, 170, .15);
      display:flex;align-items:center;gap:10px;
    }
    .form-section-title i{ color: var(--primary-color, #951eaa); opacity: .9; }
    html.theme-dark .form-section-title{ color: rgba(255,255,255,.95); }

    .form-label{font-weight: 500;color: var(--text-primary, #111827);margin-bottom: 8px;font-size: .95rem;}
    html.theme-dark .form-label{ color: rgba(255,255,255,.9); }

    .form-control, .form-select{
      padding: 10px 14px;border-radius: 12px;border: 1px solid var(--line-strong, #e5e7eb);
      background: var(--surface, #fff);transition: all .2s ease;font-size: .95rem;
    }
    .form-control:focus, .form-select:focus{
      border-color: var(--primary-color, #951eaa);
      box-shadow: 0 0 0 3px rgba(149, 30, 170, .1);
      background: var(--surface, #fff);
    }
    html.theme-dark .form-control, html.theme-dark .form-select{
      background: rgba(255,255,255,.05);
      border-color: rgba(255,255,255,.15);
      color: rgba(255,255,255,.9);
    }

    #page-editor-wrap{width:100%}
    #page-editor-wrap .ce-editor,
    #page-editor-wrap .ce-editor__holder,
    #page-editor-wrap .ce-editor__redactor{width:100% !important;max-width:100% !important}

    .editor-shell{
      border: 1px solid var(--line-strong,#e5e7eb);
      border-radius: 18px;
      overflow: hidden;
      background: var(--surface, #fff);
      margin-top: 16px;
      box-shadow: 0 4px 16px rgba(0,0,0,.04);
    }
    .editor-top{
      display:flex; align-items:center; justify-content:space-between;
      padding:14px 20px;
      border-bottom:1px solid var(--line-strong,#e5e7eb);
      background: linear-gradient(180deg, rgba(0,0,0,.02), rgba(0,0,0,.01));
    }
    html.theme-dark .editor-top{background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));}
    .editor-top .left{display:flex; align-items:center; gap:12px;font-weight:600;color: var(--text-primary, #111827);}
    .editor-top .right{display:flex; align-items:center; gap:10px;}
    .icon-btn{
      width:42px;height:42px;border-radius:12px;border:1px solid var(--line-strong,#e5e7eb);
      background: var(--surface,#fff);display:inline-flex;align-items:center;justify-content:center;
      cursor:pointer;transition: all .2s ease;color: var(--text-secondary, #6b7280);
    }
    .icon-btn:hover{
      transform: translateY(-2px);
      box-shadow: var(--shadow-2, 0 8px 18px rgba(0,0,0,.1));
      border-color: var(--primary-color, #951eaa);
      color: var(--primary-color, #951eaa);
      background: rgba(149, 30, 170, .05);
    }
    html.theme-dark .icon-btn{background: rgba(255,255,255,.08);border-color: rgba(255,255,255,.15);}

    .editor-fullscreen{
      position: fixed; inset: 12px; z-index: 20000;
      margin: 0 !important; border-radius: 18px;
      box-shadow: 0 24px 80px rgba(0,0,0,.35);
      display:flex;flex-direction:column;
      background: var(--surface,#fff);
      border:1px solid var(--line-strong,#e5e7eb);
    }
    .editor-fullscreen .editor-top{ border-radius:18px 18px 0 0; }
    .editor-fullscreen .editor-body{ flex:1; overflow:auto; padding:20px; }
    .editor-normal .editor-body{ padding:20px; }
    .editor-fullscreen #page-editor-wrap .ce-editor__redactor{ min-height: calc(100vh - 180px) !important; }
    .editor-normal #page-editor-wrap .ce-editor__redactor{ min-height: 450px; }

    .fs-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:19999;display:none;backdrop-filter: blur(4px);}
    .fs-backdrop.show{display:block;}

    .top-actions{display:flex;gap:12px;align-items:center;flex-wrap:wrap;justify-content:flex-end;}
    @media (max-width: 768px){.blog-topbar{padding:12px 0;}.top-actions{gap:8px;}.cardx .body{padding:20px 16px;}}

    .field-hint{font-size: .85rem;color: #6b7280;margin-top: 6px;opacity: .8;display:flex;align-items:center;gap:6px;}
    .field-hint i{font-size:.8rem;}
    .required-star{color:#dc3545;margin-left:4px;}

    .side-panel{
      background: linear-gradient(180deg, rgba(249, 250, 251, .9), rgba(249, 250, 251, .95));
      border-radius: 16px;
      padding: 20px;
      border: 1px solid rgba(0,0,0,.05);
    }
    html.theme-dark .side-panel{background: linear-gradient(180deg, rgba(30, 30, 30, .9), rgba(30, 30, 30, .95));border-color: rgba(255,255,255,.08);}

    .swal2-container.swal-below-topbar{padding-top: 92px !important;align-items:flex-start !important;}
    @media (max-width: 768px){.swal2-container.swal-below-topbar{padding-top: 80px !important;}}
  </style>
</head>

<body class="bg-light">
  <div id="fsBackdrop" class="fs-backdrop" aria-hidden="true"></div>

  <div class="topbar-shell">
    <div class="blog-topbar">
      <div class="blog-topbar-inner">
        <div class="d-flex align-items-start justify-content-between gap-3">
          <div class="pe-2">
            <div class="top-title h4 mb-1" id="pageTitle">Create Page</div>
            <div class="mini-help" id="pageSub">Write page details and save.</div>
          </div>

          <div class="top-actions">
            <button class="btn btn-outline-secondary btn-sm" id="btnBack" type="button" title="Back to Manage Pages">
              <i class="fa-solid fa-arrow-left me-1"></i> Back
            </button>

            <span id="savingIndicator" class="saving-indicator saved">
              <i class="fas fa-cloud icon"></i>
              <span class="text">Ready</span>
            </span>

            <button class="btn btn-outline-secondary btn-sm" id="btnPreview" type="button">
              <i class="fa-solid fa-eye me-1"></i> Preview
            </button>

            <button class="btn btn-primary btn-sm" id="btnSave" type="button">
              <i class="fa-solid fa-save me-1"></i> Save
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-wrap">
    <div class="cardx">
      <div class="head">
        <div class="d-flex align-items-center gap-2">
          <i class="fa-solid fa-file-circle-check" style="color: var(--primary-color, #951eaa);"></i>
          <strong id="modeBadge">Create Mode</strong>
        </div>

        <div class="d-flex align-items-center gap-2">
          <button type="button" class="btn btn-sm btn-outline-info" id="bodyInstructionsBtn" title="Instructions">
            <i class="fa-solid fa-circle-info"></i> Help
          </button>
        </div>
      </div>

      <div class="body">
        @csrf
        <input type="hidden" id="pageIdentifier" />

        <!-- PAGE DETAILS -->
        <div class="form-section">
          <div class="form-section-title">
            <i class="fa-solid fa-gear"></i>
            Page Details
          </div>

          <div class="row g-4">
            <div class="col-lg-8">
              <div class="row g-4">

                <div class="col-md-8">
                  <label class="form-label">Title <span class="required-star">*</span></label>
                  <input type="text" id="pageTitleInput" class="form-control" placeholder="e.g., About Us" required>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Page Type</label>
                  <select id="pageType" class="form-select">
                    <option value="page">page</option>
                    <option value="landing">landing</option>
                    <option value="custom">custom</option>
                  </select>
                  <div class="field-hint">You can keep it “page”</div>
                </div>

                <div class="col-md-8">
                  <label class="form-label">Slug</label>
                  <input type="text" id="pageSlug" class="form-control" placeholder="auto-generated-if-empty">
                  <div class="field-hint"><i class="fa-solid fa-link"></i> URL-friendly version of title</div>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Shortcode</label>
                  <input type="text" id="pageShortcode" class="form-control" placeholder="auto-generated-if-empty">
                  <div class="field-hint">Used for embedding</div>
                </div>

                <div class="col-12">
                  <label class="form-label">Meta Description</label>
                  <textarea id="metaDescription" class="form-control" rows="3" placeholder="SEO meta description (max 255)"></textarea>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Layout Key</label>
                  <input type="text" id="layoutKey" class="form-control" placeholder="e.g., default-layout">
                </div>

                <div class="col-md-6">
                  <label class="form-label">Includable ID</label>
                  <input type="text" id="includableId" class="form-control" placeholder="optional unique includable id">
                </div>

              </div>
            </div>

            <!-- SIDE -->
            <div class="col-lg-4">
              <div class="side-panel h-100">

                <div class="mb-3">
                  <label class="form-label">Status</label>
                  <select id="pageStatus" class="form-select">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Published At</label>
                  <input type="datetime-local" id="publishedAt" class="form-control">
                  <div class="field-hint">Leave empty to keep unpublished</div>
                </div>

                {{-- ✅ Department dropdown (instead of number input) --}}
                <div class="mb-3">
                  <label class="form-label">Department</label>
                  <select id="departmentId" class="form-select">
                    <option value="">— Select Department (optional) —</option>
                  </select>
                  <div class="field-hint">
                    <i class="fa-solid fa-building-columns"></i>
                    Loaded from <code>/api/departments</code>
                  </div>
                </div>

                <div class="mb-0">
                  <label class="form-label">Submenu Exists</label>
                  <select id="submenuExists" class="form-select">
                    <option value="no">no</option>
                    <option value="yes">yes</option>
                  </select>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- CONTENT -->
        <div class="form-section">
          <div class="form-section-title">
            <i class="fa-solid fa-pen-nib"></i>
            Page Content
          </div>

          <div id="editorContainer" class="editor-shell editor-normal">
            <div class="editor-top">
              <div class="left">
                <i class="fa-solid fa-keyboard"></i>
                <span>Rich Text Editor</span>
                <span class="mini-help ms-2">Write your content here</span>
              </div>

              <div class="right">
                <button type="button" class="icon-btn" id="btnEditorFullscreen" title="Fullscreen editor">
                  <i class="fa-solid fa-expand"></i>
                </button>
              </div>
            </div>

            <div class="editor-body">
              <div id="page-editor-wrap">
                {{-- ✅ Reuse same editor include --}}
                @include('modules.pages.editor')
              </div>
              <textarea id="pageContentHtml" class="d-none"></textarea>
            </div>
          </div>

          <div class="field-hint mt-2">
            <i class="fa-solid fa-lightbulb"></i>
            Use fullscreen for distraction-free editing.
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
  function getToken(){ return sessionStorage.getItem('token') || localStorage.getItem('token'); }
  function getQueryParam(key){ const u=new URL(window.location.href); return u.searchParams.get(key); }

  function swalLoadingBelowTopbar(title='Loading…', text='Please wait...'){
    return Swal.fire({
      title, text,
      allowOutsideClick:false,
      didOpen:()=>Swal.showLoading(),
      customClass:{ container:'swal-below-topbar' }
    });
  }

  async function apiJson(url,{method='GET',body=null,headers={}}={}){
    const token=getToken();
    if(!token) throw new Error('NO_TOKEN');

    const base={
      Accept:'application/json',
      Authorization:`Bearer ${token}`,
      'X-Requested-With':'XMLHttpRequest'
    };

    if(body && typeof body==='object' && !(body instanceof FormData)){
      base['Content-Type']='application/json';
      body=JSON.stringify(body);
    }

    if(['PUT','PATCH','DELETE'].includes(method)){
      base['X-HTTP-Method-Override']=method;
      method='POST';
    }

    const res=await fetch(url,{method,headers:{...base,...headers},body});
    const ct=res.headers.get('content-type')||'';
    let data;

    if(ct.includes('application/json')){
      try{ data=await res.json(); }catch{ data={}; }
    } else {
      const text=await res.text();
      console.warn('[apiJson] NON-JSON BODY', text.slice(0,200));
      throw new Error('NON_JSON_RESPONSE_POSSIBLE_AUTH ('+res.status+')');
    }

    if(!res.ok){
      const err=new Error(data.message||data.error||('HTTP '+res.status));
      err.status=res.status; err.payload=data; throw err;
    }
    return data;
  }

  /* ==========================
   * ✅ Departments dropdown loader
   * ========================== */
  async function loadDepartmentsForPageEditor(){
    const sel = document.getElementById('departmentId');
    if(!sel) return;

    // keep usable default option
    sel.innerHTML = `<option value="">— Select Department (optional) —</option>`;

    try{
      const json = await apiJson('/api/departments?per_page=200', { method:'GET' });
      const list = Array.isArray(json.data) ? json.data : (Array.isArray(json.departments) ? json.departments : []);

      list.forEach(d=>{
        const id = d?.id;
        if(id === null || id === undefined) return;

        const label = d?.title || d?.name || d?.slug || `Department #${id}`;
        const opt = document.createElement('option');
        opt.value = String(id);
        opt.textContent = label;
        sel.appendChild(opt);
      });
    }catch(err){
      console.error('Departments load failed', err);
      // don't break the page; keep the default option
    }
  }

  function initPageEditor(){
    return new Promise((resolve, reject) => {
      try{
        if(!window.CEBuilder){
          let tries = 0;
          const t = setInterval(() => {
            tries++;
            if(window.CEBuilder){
              clearInterval(t);
              initPageEditor().then(resolve).catch(reject);
            } else if(tries > 40){
              clearInterval(t);
              resolve({ skipped:true, reason:'CEBuilder not found' });
            }
          }, 100);
          return;
        }

        if(typeof window.CEBuilder.init === 'function'){
          const p = window.CEBuilder.init();
          if(p && typeof p.then === 'function') return p.then(()=>resolve({ok:true})).catch(reject);
          return resolve({ok:true});
        }

        if(typeof window.CEBuilder.create === 'function'){
          const p = window.CEBuilder.create();
          if(p && typeof p.then === 'function') return p.then(()=>resolve({ok:true})).catch(reject);
          return resolve({ok:true});
        }

        return resolve({ok:true});
      }catch(e){ reject(e); }
    });
  }

  function setBodyHTML(html){
    html = html || '';
    if(window.CEBuilder){
      if(typeof window.CEBuilder.setHTML === 'function') window.CEBuilder.setHTML(html);
      else if(window.CEBuilder.editor && typeof window.CEBuilder.editor.setHTML === 'function') window.CEBuilder.editor.setHTML(html);
    }
    $('#pageContentHtml').val(html);
  }
  function getBodyHTML(){
    if(window.CEBuilder){
      if(typeof window.CEBuilder.getHTML === 'function') return (window.CEBuilder.getHTML() || '').trim();
      if(window.CEBuilder.editor && typeof window.CEBuilder.editor.getHTML === 'function') return (window.CEBuilder.editor.getHTML() || '').trim();
    }
    return ($('#pageContentHtml').val() || '').trim();
  }

  let lastSaveTime = 0;
  function setSavingState(state) {
    const indicator = $('#savingIndicator');
    indicator.removeClass('saving saved error');

    if (state === 'saving') {
      indicator.addClass('saving');
      indicator.find('.icon').attr('class', 'fas fa-spinner fa-spin icon');
      indicator.find('.text').text('Saving...');
    } else if (state === 'saved') {
      indicator.addClass('saved');
      indicator.find('.icon').attr('class', 'fas fa-check-circle icon');
      const timeStr = lastSaveTime ? new Date(lastSaveTime).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}) : null;
      indicator.find('.text').text(timeStr ? `Saved at ${timeStr}` : 'All changes saved');
    } else if (state === 'error') {
      indicator.addClass('error');
      indicator.find('.icon').attr('class', 'fas fa-exclamation-triangle icon');
      indicator.find('.text').text('Error saving');
    } else {
      indicator.addClass('saved');
      indicator.find('.icon').attr('class', 'fas fa-cloud icon');
      indicator.find('.text').text('Ready to save');
    }
  }

  let mode='create';
  function setMode(m){
    mode=m;

    if(mode==='edit'){
      $('#pageTitle').text('Edit Page');
      $('#modeBadge').text('Edit Mode');
      $('#pageSub').text('Editing existing page (loaded via ?uuid=...)');
      $('#btnPreview').html('<i class="fa-solid fa-up-right-from-square me-1"></i> Preview');
    } else {
      $('#pageTitle').text('Create Page');
      $('#modeBadge').text('Create Mode');
      $('#pageSub').text('Write page details and save.');
      $('#btnPreview').html('<i class="fa-solid fa-eye me-1"></i> Preview');
    }
  }

  function buildPreviewUrl(){
    const slug = ($('#pageSlug').val() || '').trim();
    if(!slug) return '';
    // If you have a public render route, change it here:
    return `/pages/view/${encodeURIComponent(slug)}?mode=test`;
  }

  function previewDirectNewTab(){
    const url = buildPreviewUrl();
    if(!url){
      Swal.fire({
        icon:'info',
        title:'Slug missing',
        text:'Slug is required for direct preview.',
        confirmButtonColor: 'var(--primary-color, #951eaa)',
        customClass:{ container:'swal-below-topbar' }
      });
      $('#pageSlug').focus();
      return;
    }
    window.open(url, '_blank', 'noopener');
  }

  async function loadPage(identifier){
    swalLoadingBelowTopbar('Loading…','Fetching page data...');
    try{
      const json = await apiJson(`/api/pages/${encodeURIComponent(identifier)}`, { method:'GET' });
      const p = json.data || json.page || json;

      $('#pageIdentifier').val(p.uuid || p.id || p.slug || identifier);

      $('#pageTitleInput').val(p.title || '');
      $('#pageSlug').val(p.slug || '');
      $('#pageShortcode').val(p.shortcode || '');
      $('#pageType').val(p.page_type || 'page');

      $('#metaDescription').val(p.meta_description || '');
      $('#layoutKey').val(p.layout_key || '');
      $('#includableId').val(p.includable_id || '');

      $('#pageStatus').val(p.status || 'Active');

      // ✅ set select value
      $('#departmentId').val(p.department_id ?? '');
      $('#submenuExists').val(p.submenu_exists || 'no');

      // published_at -> datetime-local (YYYY-MM-DDTHH:mm)
      if(p.published_at){
        const dt = String(p.published_at).replace(' ', 'T').slice(0,16);
        $('#publishedAt').val(dt);
      } else {
        $('#publishedAt').val('');
      }

      setBodyHTML(p.content_html || '');

      Swal.close();
      setSavingState('ready');
    }catch(err){
      Swal.close();
      handleApiError('Failed to load page', err);
    }
  }

  async function savePage(){
    const title = $('#pageTitleInput').val().trim();
    if(!title){
      Swal.fire({
        icon: 'error',
        title: 'Missing Title',
        text: 'Page title is required',
        confirmButtonColor: 'var(--primary-color, #951eaa)',
        customClass:{ container:'swal-below-topbar' }
      });
      $('#pageTitleInput').focus();
      return;
    }

    // Convert datetime-local -> standard string (Laravel can parse)
    const publishedAtVal = ($('#publishedAt').val() || '').trim();
    const published_at = publishedAtVal ? (publishedAtVal.replace('T',' ') + ':00') : null;

    const payload = {
      title,
      slug: ($('#pageSlug').val().trim() || null),
      shortcode: ($('#pageShortcode').val().trim() || null),
      page_type: $('#pageType').val() || 'page',
      content_html: getBodyHTML() || null,

      includable_id: ($('#includableId').val().trim() || null),
      layout_key: ($('#layoutKey').val().trim() || null),
      meta_description: ($('#metaDescription').val().trim() || null),

      status: $('#pageStatus').val() || 'Active',
      published_at,

      // ✅ dropdown -> number or null
      department_id: ($('#departmentId').val() ? Number($('#departmentId').val()) : null),
      submenu_exists: $('#submenuExists').val() || 'no',
    };

    const identifier = ($('#pageIdentifier').val() || '').trim();

    setSavingState('saving');
    swalLoadingBelowTopbar('Saving Page...','Please wait while we save your changes');

    try{
      let url = '/api/pages';
      let method = 'POST';

      if(mode === 'edit' && identifier){
        url = `/api/pages/${encodeURIComponent(identifier)}`;
        method = 'PUT';
      }

      const json = await apiJson(url, { method, body: payload });
      const saved = json.data || null;

      lastSaveTime = Date.now();
      setSavingState('saved');

      if(mode === 'create' && saved && (saved.uuid || saved.id || saved.slug)){
        const newId = saved.uuid || saved.id || saved.slug;
        Swal.close();
        window.location.href = `/pages/create?uuid=${encodeURIComponent(newId)}`;
        return;
      }

      Swal.fire({
        icon: 'success',
        title: 'Saved Successfully',
        text: json.message || 'Your page has been saved',
        confirmButtonColor: 'var(--primary-color, #951eaa)',
        customClass:{ container:'swal-below-topbar' }
      });

    }catch(err){
      setSavingState('error');
      Swal.close();
      handleApiError('Failed to save page', err);
    }
  }

  async function previewModal(){
    const title = $('#pageTitleInput').val().trim() || 'Page Preview';
    const html  = getBodyHTML();

    if(!html.trim()){
      Swal.fire({
        icon: 'info',
        title: 'No Content',
        text: 'Add some content to preview',
        confirmButtonColor: 'var(--primary-color, #951eaa)',
        customClass:{ container:'swal-below-topbar' }
      });
      return;
    }

    Swal.fire({
      title: '',
      html: `
        <div style="display:flex;gap:8px;justify-content:center;margin-bottom:12px;flex-wrap:wrap">
          <button class="device-btn active" data-device="desktop"
            style="border:1px solid var(--line-strong);border-radius:10px;padding:8px 14px;background:var(--surface);color:var(--text-primary)">
            <i class="fa-solid fa-desktop me-1"></i>Desktop
          </button>
          <button class="device-btn" data-device="tablet"
            style="border:1px solid var(--line-strong);border-radius:10px;padding:8px 14px;background:var(--surface);color:var(--text-primary)">
            <i class="fa-solid fa-tablet-screen-button me-1"></i>Tablet
          </button>
          <button class="device-btn" data-device="mobile"
            style="border:1px solid var(--line-strong);border-radius:10px;padding:8px 14px;background:var(--surface);color:var(--text-primary)">
            <i class="fa-solid fa-mobile-screen-button me-1"></i>Mobile
          </button>
        </div>

        <div style="text-align:center;margin:6px 0 14px;">
          <div style="font-weight:800;font-size:1.25rem;color:var(--ink,#111827)">${escapeHtml(title)}</div>
          <div style="font-size:.9rem;color:var(--muted-color,#6b7280);margin-top:2px;">Preview (${mode === 'edit' ? 'Edit' : 'Create'} Mode)</div>
        </div>

        <div class="preview-container desktop" style="border:1px solid var(--line-strong);border-radius:14px;overflow:hidden">
          <iframe id="previewFrame" sandbox style="width:100%;height:70vh;border:0"
            srcdoc="${buildPreviewSrcdoc(html)}"></iframe>
        </div>

        <style>
          .preview-container.tablet iframe{max-width: 820px; margin:0 auto; display:block;}
          .preview-container.mobile iframe{max-width: 375px; margin:0 auto; display:block;}
          .device-btn.active{outline:2px solid var(--primary-color, #951eaa);background: rgba(149, 30, 170, .1) !important;}
        </style>
      `,
      width: '95%',
      showCloseButton: true,
      showConfirmButton: false,
      customClass: { container: 'swal-below-topbar' },
      didOpen: () => {
        const container = Swal.getHtmlContainer();
        container.querySelectorAll('.device-btn').forEach(btn => {
          btn.addEventListener('click', e => {
            container.querySelectorAll('.device-btn').forEach(b => b.classList.remove('active'));
            e.currentTarget.classList.add('active');
            const device = e.currentTarget.dataset.device;
            const wrap = container.querySelector('.preview-container');
            wrap.className = 'preview-container ' + device;
          });
        });
      }
    });
  }

  function escapeHtml(str){
    return String(str || '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }
  function buildPreviewSrcdoc(contentHtml){
    const body = String(contentHtml || '');
    const doc =
`<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;padding:20px;max-width:900px;margin:0 auto;line-height:1.65}
    img{max-width:100%;height:auto}
    a{color:#951eaa;font-weight:700}
  </style>
</head>
<body>${body}</body>
</html>`;
    return escapeHtml(doc);
  }

  function handleApiError(context,err){
    console.error(context,err);
    let msg=err.message||context;

    if(err.status===401) msg='Unauthorized – please login again.';
    else if(err.status===403) msg='Forbidden – check role/token.';
    else if(msg.startsWith('NON_JSON_RESPONSE_POSSIBLE_AUTH')) msg='Server returned non-JSON (likely login HTML). Auth failed.';
    else if(msg==='NO_TOKEN') msg='No auth token found. Please login again.';

    if(err.payload && err.payload.errors){
      const flat = Object.values(err.payload.errors).flat().slice(0,8).join('<br>');
      msg = flat || msg;
    }

    Swal.fire({
      icon: 'error',
      title: 'Error',
      html: msg,
      confirmButtonColor: 'var(--primary-color, #951eaa)',
      customClass:{ container:'swal-below-topbar' }
    }).then(()=>{
      if(msg.includes('login again') || msg.includes('No auth token')) location.href='/';
    });
  }

  function setEditorFullscreen(on){
    const c = document.getElementById('editorContainer');
    const b = document.getElementById('fsBackdrop');
    const btn = document.getElementById('btnEditorFullscreen');
    const icon = btn ? btn.querySelector('i') : null;
    if(!c) return;

    if(on){
      b && b.classList.add('show');
      c.classList.remove('editor-normal');
      c.classList.add('editor-fullscreen');
      if(icon){ icon.className = 'fa-solid fa-compress'; btn.title = 'Exit fullscreen editor'; }
      document.body.style.overflow = 'hidden';
    } else {
      b && b.classList.remove('show');
      c.classList.remove('editor-fullscreen');
      c.classList.add('editor-normal');
      if(icon){ icon.className = 'fa-solid fa-expand'; btn.title = 'Fullscreen editor'; }
      document.body.style.overflow = '';
    }
  }

  $(function(){
    const t=getToken();
    if(!t){
      Swal.fire({
        icon: 'warning',
        title: 'Auth Required',
        text: 'Session expired. Please login again.',
        confirmButtonColor: 'var(--primary-color, #951eaa)',
        customClass:{ container:'swal-below-topbar' }
      }).then(()=>location.href='/');
      return;
    }

    // ✅ load departments into dropdown
    loadDepartmentsForPageEditor();

    // Back to manage pages
    $('#btnBack').on('click', function(){
      window.location.href = '/pages/manage';
    });

    $('#btnEditorFullscreen').on('click', function(){
      const isOn = document.getElementById('editorContainer')?.classList.contains('editor-fullscreen');
      setEditorFullscreen(!isOn);
    });
    $(document).on('keydown', function(e){
      if(e.key === 'Escape'){
        const isOn = document.getElementById('editorContainer')?.classList.contains('editor-fullscreen');
        if(isOn) setEditorFullscreen(false);
      }
    });
    $('#fsBackdrop').on('click', function(){ setEditorFullscreen(false); });

    // Auto slug from title (only when slug empty)
    $('#pageTitleInput').on('blur', function(){
      const title = $(this).val().trim();
      const slugInput = $('#pageSlug');
      if(title && (!slugInput.val().trim() || slugInput.val().trim() === 'auto-generated-if-empty')){
        const slug = title.toLowerCase()
          .replace(/[^\w\s-]/g, '')
          .replace(/\s+/g, '-')
          .replace(/--+/g, '-');
        slugInput.val(slug);
      }
    });

    initPageEditor().then(() => {
      const q = getQueryParam('uuid'); // identifier could be uuid/id/slug
      if(q){
        setMode('edit');
        // wait small tick so dropdown loads; still safe even if not loaded yet
        setTimeout(()=> loadPage(q), 50);
      } else {
        setMode('create');
        setBodyHTML('');
        setSavingState('ready');
      }
    }).catch(err=>{
      console.error('Editor init failed', err);
      const q = getQueryParam('uuid');
      if(q){ setMode('edit'); loadPage(q); } else { setMode('create'); }
      setSavingState('ready');
    });

    $('#btnSave').on('click', savePage);

    // Preview behavior:
    // - edit: open new tab if you have public route
    // - create: modal preview
    $('#btnPreview').on('click', function(){
      if(mode === 'edit') return previewDirectNewTab(); // change to previewModal() if you want always modal
      return previewModal();
    });

    $('#bodyInstructionsBtn').on('click',()=>{
      Swal.fire({
        icon:'info',
        title:'Page Editor Guide',
        html:`<div style="text-align:left">
                <ul style="padding-left:20px;margin-bottom:0;">
                  <li><b>Title:</b> required</li>
                  <li><b>Slug:</b> can be auto-generated</li>
                  <li><b>Shortcode:</b> auto if empty</li>
                  <li><b>Published At:</b> leave empty to keep unpublished</li>
                  <li><b>Status:</b> Active/Inactive</li>
                  <li><b>Department:</b> optional, loaded from Departments</li>
                  <li><b>Includable ID:</b> must be unique if used</li>
                </ul>
              </div>`,
        width:700,
        showCloseButton:true,
        showConfirmButton:false,
        confirmButtonColor: 'var(--primary-color, #951eaa)',
        customClass:{ container:'swal-below-topbar' }
      });
    });

    setSavingState('ready');
  });
  </script>
</body>
</html>
