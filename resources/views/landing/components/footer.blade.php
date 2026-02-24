{{-- resources/views/landing/components/footer.blade.php --}}
{{-- Usage: @include('landing.components.footer') --}}
{{-- Public render component: reads FooterComponent singleton + populates UI like your screenshot --}}

@once
  {{-- ✅ IMPORTANT FIX:
      DO NOT hard-include Bootstrap CSS here (it loads AFTER your header CSS and overrides .navbar-nav -> breaks header menu).
      Instead, we "ensure" Bootstrap + FontAwesome only if missing, and we PREPEND them into <head> so header styles still win.
  --}}
  <script>
    (function () {
      function hasStylesheet(testFn){
        return Array.from(document.querySelectorAll('link[rel="stylesheet"]')).some(l => {
          const href = (l.getAttribute('href') || '').toLowerCase();
          return testFn(href);
        });
      }

      function prependStylesheet(href, id){
        try{
          if (id && document.getElementById(id)) return;
          const head = document.head || document.getElementsByTagName('head')[0];
          if (!head) return;

          const link = document.createElement('link');
          link.rel = 'stylesheet';
          link.href = href;
          if (id) link.id = id;

          // ✅ insert BEFORE everything so it never overrides your component CSS
          head.insertBefore(link, head.firstChild);
        }catch(e){}
      }

      // Bootstrap (any version) check
      const hasBootstrap = hasStylesheet((href) =>
        href.includes('bootstrap') && href.endsWith('.css')
      );
      if (!hasBootstrap){
        prependStylesheet(
          'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
          'ftBootstrapCss'
        );
      }

      // FontAwesome check
      const hasFA = hasStylesheet((href) =>
        href.includes('font-awesome') || href.includes('fontawesome') || href.includes('/all.min.css')
      );
      if (!hasFA){
        prependStylesheet(
          'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
          'ftFontAwesomeCss'
        );
      }
    })();
  </script>

  <style>
    :root{
      --ft-bg: var(--primary-color, #9E363A);
      --ft-ink: #ffffff;
      --ft-ink-soft: rgba(255,255,255,.88);
      --ft-rule: rgba(255,255,255,.70);

      --ft-accent: #E2B13C;        /* golden */
      --ft-accent-2: #F2C94C;

      --ft-max: 1280px;

      /* Match header-ish sizing (nav-link ~ .95rem, dropdown-item ~ .93rem) */
      --ft-link-size: .95rem;
      --ft-link-gap: 1.35rem;

      --ft-dept-title: 1.08rem;     /* dept title size */
      --ft-dept-item:  .93rem;      /* child item size */

      --ft-social-size: 35px;
      --ft-social-icon: 17px;
    }

    .ft-wrap, .ft-wrap *{ box-sizing:border-box; }
    .ft-wrap{width:100%;background:var(--ft-bg);color:var(--ft-ink);font-family: var(--font-sans, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, 'Noto Sans', sans-serif);}
    .ft-inner{max-width:var(--ft-max);margin:0 auto;padding:22px 16px 18px;}
    .ft-rule{height:0;border:none;border-top:1px solid var(--ft-rule);margin:18px 0;opacity:1;}

    /* ===== Inline link rows ===== */
    .ft-inline-links{display:flex;flex-wrap:wrap;justify-content:center;gap:10px var(--ft-link-gap);padding:6px 0;}
    .ft-inline-links.ft-left{ justify-content:flex-start; }
    .ft-link{color:var(--ft-ink);text-decoration:none;font-size:var(--ft-link-size);line-height:1.25;white-space:nowrap;border-bottom:2px solid transparent;padding-bottom:2px;transition:opacity .12s ease, border-color .12s ease;}
    .ft-link:hover{color:var(--ft-accent-2);border-color:var(--ft-accent);text-decoration:none;}
    .ft-blocks{display:grid;grid-template-columns:repeat(4, minmax(0, 1fr));gap: 18px 34px;align-items:start;}
    .ft-block{min-width:0;}
    .ft-block-title{font-family: var(--font-head, var(--font-sans, inherit));font-weight:700;font-size:var(--ft-dept-title);margin:0 0 12px 0;letter-spacing:.15px;position:relative;display:inline-block;line-height:1.2;}
    .ft-block-title::after{content:"";position:absolute;left:0;bottom:-6px;width:56px;height:3px;background:var(--ft-accent);border-radius:6px;}

    /* ✅ child menus in a column */
    .ft-block-grid{display:flex;flex-direction:column;gap: 10px;margin-top: 10px;}
    .ft-block-item{color:var(--ft-ink);text-decoration:none;font-size:var(--ft-dept-item);line-height:1.25;opacity:.98;transition:opacity .12s ease, transform .12s ease;white-space:normal;word-break:break-word;}
    .ft-block-item:hover{opacity:1;transform:translateX(2px);text-decoration:none;}

    /* ===== Brand row ===== */
    .ft-brand-row{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:6px 0;}
    .ft-brand-left{display:flex;align-items:center;gap:14px;min-width:0;}
    /* ✅ FIXED: Increased logo size, used object-fit:cover, border-radius:50%, reduced padding, added flex-shrink:0 */
    .ft-brand-logo{width:120px;height:120px;object-fit:cover;border-radius:50%;background:rgba(255,255,255,.12);padding:6px;flex-shrink:0;}
    .ft-brand-text{ min-width:0; display:flex; flex-direction:column; gap:4px; }
    .ft-brand-title{font-family: var(--font-head, var(--font-sans, inherit));font-weight:800;font-size:1.35rem;line-height:1.15;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .ft-brand-rotate{color:var(--ft-ink-soft);font-size:.98rem;line-height:1.25;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;transition:opacity .18s ease, transform .18s ease;}
    .ft-brand-rotate.is-fading{ opacity:0; transform:translateY(-2px); }

    /* ===== Social buttons ===== */
    .ft-social{display:flex;align-items:center;gap:14px;flex:0 0 auto;}
    .ft-social-btn{width:var(--ft-social-size);height:var(--ft-social-size);display:inline-flex;align-items:center;justify-content:center;border:2px solid var(--ft-accent-2);color:var(--ft-accent-2);text-decoration:none;background:transparent;transition:transform .12s ease, background .12s ease, filter .12s ease;}
    .ft-social-btn i{ font-size:var(--ft-social-icon); line-height:1; }
    .ft-social-btn:hover{background:var(--ft-accent-2);filter:drop-shadow(0 8px 18px rgba(0,0,0,.12));text-decoration:none;}

    /* ===== Bottom row ===== */
    .ft-bottom-row{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;}
    .ft-bottom-links{display:flex;flex-wrap:wrap;gap:10px 18px;align-items:center;}
    .ft-copy{color:var(--ft-ink-soft);font-size:.95rem;line-height:1.25;text-align:right;white-space:nowrap;}
    .ft-address{margin-top:10px;color:var(--ft-ink);font-size:.95rem;line-height:1.35;opacity:.98;}

    /* ===== Skeleton ===== */
    .ft-skel{background:linear-gradient(90deg, rgba(255,255,255,.10), rgba(255,255,255,.18), rgba(255,255,255,.10));background-size:200% 100%;animation:ft-skel 1.1s ease-in-out infinite;border-radius:10px;}
    @keyframes ft-skel{ 0%{ background-position:200% 0; } 100%{ background-position:-200% 0; } }
    .ft-skel-title{ height:32px; width:220px; }
    .ft-skel-grid{ height:120px; width:100%; border-radius:14px; }

    /* ✅ Responsive: still desktop shows 4 in a row */
    @media (max-width: 1100px){
      .ft-blocks{ grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px 26px; }
    }
    @media (max-width: 780px){
      .ft-inline-links{ justify-content:flex-start; }
      .ft-blocks{ grid-template-columns:1fr; gap:14px; }
      .ft-copy{ text-align:left; white-space:normal; }
    }
    @media (prefers-reduced-motion: reduce){
      .ft-brand-rotate{ transition:none !important; }
    }
    @media (max-width: 780px){
      .ft-inline-links{ justify-content:flex-start; }
      .ft-blocks{ grid-template-columns:1fr; gap:14px; }
      .ft-brand-row{
        flex-direction:column;
        align-items:center;
        text-align:center;
      }

      .ft-brand-left{
        flex-direction:column;
        align-items:center;
        justify-content:center;
        width:100%;
        gap:10px;
      }

      .ft-brand-text{
        align-items:center;
        width:100%;
      }

      .ft-brand-title,
      .ft-brand-rotate{
        text-align:center;
        white-space:normal;
        overflow:visible;
        text-overflow:clip;
      }
      .ft-brand-rotate{
        margin-top:8px;
      }

      .ft-social{
        width:100%;
        justify-content:center;
        margin-top:8px;
      }
      .ft-copy{ text-align:left; white-space:normal; }
    }
  </style>
@endonce

<footer
  class="ft-wrap"
  id="ftBar"
  data-endpoint="{{ url('/api/footer-components') }}"
  data-header-endpoint="{{ url('/api/header-components') }}"
  data-menu-endpoint="{{ url('/api/public/header-menus/tree') }}"
  data-public-departments-endpoint="{{ url('/api/public/departments') }}"
  data-departments-endpoint="{{ url('/api/departments') }}"
  data-site-base="{{ url('/') }}"
  data-page-base="{{ url('/page') }}"
>
  <div class="ft-inner">

    {{-- Section 1 (top links) --}}
    <div id="ftTopLinks" class="ft-inline-links ft-skel" style="min-height:24px;"></div>

    <hr class="ft-rule" id="ftRule1">

    {{-- Section 2 (menu blocks: max 4) --}}
    <div id="ftBlocks" class="ft-blocks">
      <div class="ft-block">
        <div class="ft-skel ft-skel-title"></div>
        <div class="ft-skel ft-skel-grid mt-2"></div>
      </div>
      <div class="ft-block d-none d-md-block">
        <div class="ft-skel ft-skel-title"></div>
        <div class="ft-skel ft-skel-grid mt-2"></div>
      </div>
      <div class="ft-block d-none d-md-block">
        <div class="ft-skel ft-skel-title"></div>
        <div class="ft-skel ft-skel-grid mt-2"></div>
      </div>
      <div class="ft-block d-none d-md-block">
        <div class="ft-skel ft-skel-title"></div>
        <div class="ft-skel ft-skel-grid mt-2"></div>
      </div>
    </div>

    <hr class="ft-rule" id="ftRule2">

    {{-- Section 3 (middle links) --}}
    <div id="ftMidLinks" class="ft-inline-links ft-left ft-skel" style="min-height:24px;"></div>

    <hr class="ft-rule" id="ftRule3">

    {{-- Section 4 (brand + socials) --}}
    <div class="ft-brand-row" id="ftBrandRow">
      <div class="ft-brand-left">
        <img id="ftBrandLogo" class="ft-brand-logo ft-skel" alt="Brand logo"/>
        <div class="ft-brand-text">
          <div id="ftBrandTitle" class="ft-brand-title ft-skel" style="height:34px;width:520px;max-width:72vw;"></div>
          <div id="ftBrandRotate" class="ft-brand-rotate ft-skel" style="height:24px;width:520px;max-width:72vw;"></div>
        </div>
      </div>

      <div id="ftSocial" class="ft-social"></div>
    </div>

    <hr class="ft-rule" id="ftRule4">

    {{-- Section 5 (bottom links + copyright) --}}
    <div class="ft-bottom-row">
      <div id="ftBottomLinks" class="ft-bottom-links"></div>
      <div id="ftCopyright" class="ft-copy"></div>
    </div>

    <div id="ftAddress" class="ft-address" style="display:none;"></div>
  </div>
</footer>

@once
<script>
(() => {
  if (window.__PUBLIC_FOOTER_SINGLETON__) return;
  window.__PUBLIC_FOOTER_SINGLETON__ = true;

  const $ = (id) => document.getElementById(id);

  const els = {
    bar: $('ftBar'),

    topLinks: $('ftTopLinks'),
    rule1: $('ftRule1'),

    blocks: $('ftBlocks'),
    rule2: $('ftRule2'),

    midLinks: $('ftMidLinks'),
    rule3: $('ftRule3'),

    brandLogo: $('ftBrandLogo'),
    brandTitle: $('ftBrandTitle'),
    brandRotate: $('ftBrandRotate'),
    social: $('ftSocial'),
    rule4: $('ftRule4'),

    bottomLinks: $('ftBottomLinks'),
    copyright: $('ftCopyright'),
    address: $('ftAddress'),
  };

  /* ===== ✅ SITE_BASE and PAGE_BASE from Blade (always absolute) ===== */
  const SITE_BASE = (els.bar?.getAttribute('data-site-base') || window.location.origin).replace(/\/+$/, '');
  const PAGE_BASE = (els.bar?.getAttribute('data-page-base') || (SITE_BASE + '/page')).replace(/\/+$/, '');

  /* ===== ✅ Known direct routes (non /page/ routes from web.php) ===== */
  const KNOWN_DIRECT_ROUTES = [
    '/', '/login', '/dashboard',
    '/announcements', '/achievements', '/notices', '/student-activities',
    '/gallery', '/gallery/all-images', '/our-recruiters', '/success-stories',
    '/courses', '/events', '/faculty-members', '/technical-assistants',
    '/placement-officers', '/placed-students', '/alumni', '/program-toppers',
    '/tp-cell', '/placement-notices', '/statistics', '/contact-us', '/enquiry',
    '/career-notices', '/why-us', '/scholarships',
  ];

  /**
   * ✅ Check if a path matches a known direct route (not /page/{slug})
   */
  function isKnownDirectRoute(path) {
    const p = String(path || '').replace(/\/+$/, '').toLowerCase();
    if (!p || p === '/') return true;

    // Exact match
    if (KNOWN_DIRECT_ROUTES.includes(p)) return true;

    // Routes with UUID params (e.g. /courses/view/{uuid}, /department/view/{uuid})
    if (/^\/(courses|department|curriculum-syllabus|announcements|achievements|notices|student-activities|gallery|placed-students|placement-notices|successful-entrepreneurs|career-notices|why-us|scholarships|success-stories)\/view\/[^\/]+$/i.test(p)) return true;

    // /user/ routes
    if (p.startsWith('/user/')) return true;
    // /department/ routes
    if (p.startsWith('/department/')) return true;

    return false;
  }

  const PLACEHOLDER_LOGO = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
    <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120">
      <rect width="120" height="120" rx="60" fill="rgba(255,255,255,.14)"/>
      <circle cx="60" cy="52" r="18" fill="rgba(255,255,255,.35)"/>
      <path d="M28 98c8-18 22-26 32-26s24 8 32 26" fill="none" stroke="rgba(255,255,255,.45)" stroke-width="6" stroke-linecap="round"/>
    </svg>
  `);

  /* ===== Department + Menu UUID map (for same behavior as header) ===== */
  const __ftDeptUuidById = new Map();
  let __ftDeptMapLoaded = false;

  function normalizeUrl(u){
    const s = (u || '').toString().trim();
    if (!s) return '';
    if (/^(data:|blob:|https?:\/\/|mailto:|tel:|sms:|whatsapp:)/i.test(s)) return s;
    if (s.startsWith('/')) return window.location.origin + s;
    return window.location.origin + '/' + s;
  }

  function decodeHtmlEntities(str){
    try{
      const t = document.createElement('textarea');
      t.innerHTML = str;
      return t.value;
    }catch(e){
      return str;
    }
  }

  function safeArray(v){
    if (Array.isArray(v)) return v;
    if (typeof v === 'string'){
      const s = v.trim();
      if (!s) return [];
      try{
        const j = JSON.parse(s);
        return Array.isArray(j) ? j : [];
      }catch(_){
        try{
          const s2 = decodeHtmlEntities(s);
          const j2 = JSON.parse(s2);
          return Array.isArray(j2) ? j2 : [];
        }catch(__){ return []; }
      }
    }
    return [];
  }

  function safeObject(v){
    if (!v) return null;
    if (typeof v === 'object') return v;
    if (typeof v === 'string'){
      try{ return JSON.parse(v); }catch(_){
        try{ return JSON.parse(decodeHtmlEntities(v)); }catch(__){ return null; }
      }
    }
    return null;
  }

  function removeSkel(el){
    if (!el) return;
    el.classList.remove('ft-skel');
  }

  function setImg(imgEl, src){
    if (!imgEl) return;
    const u = normalizeUrl(src);
    imgEl.src = u || PLACEHOLDER_LOGO;
    removeSkel(imgEl);
  }

  function setText(el, txt){
    if (!el) return;
    el.textContent = (txt || '').toString();
    removeSkel(el);
  }

  /* ===== Rotating tagline ===== */
  let rotateTimer = null;
  function startRotate(lines){
    if (rotateTimer) { clearInterval(rotateTimer); rotateTimer = null; }
    const el = els.brandRotate;
    removeSkel(el);

    const arr = Array.isArray(lines) ? lines.map(x => (x ?? '').toString().trim()).filter(Boolean) : [];
    if (!arr.length){
      el.textContent = '';
      return;
    }

    let idx = 0;
    el.textContent = arr[0];

    if (arr.length === 1) return;

    rotateTimer = setInterval(() => {
      idx = (idx + 1) % arr.length;
      el.classList.add('is-fading');
      setTimeout(() => {
        el.textContent = arr[idx];
        el.classList.remove('is-fading');
      }, 180);
    }, 2600);
  }

  function escapeHtml(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  /* ===== ✅ hardNavigate (same approach as header menu) ===== */
  function hardNavigate(e, href, openNewTab) {
    if (!href || href === '#') return;

    // allow ctrl/cmd click new tabs
    if (e && (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button === 1)) return;

    e?.preventDefault?.();
    e?.stopPropagation?.();

    if (openNewTab) {
      window.open(href, '_blank', 'noopener,noreferrer');
      return;
    }

    try {
      // ✅ CRITICAL: resolve relative to ORIGIN, not current href
      const target = new URL(href, window.location.origin);
      window.location.href = target.href;
    } catch (error) {
      window.location.href = href;
    }
  }

  function renderInlineLinks(container, links, alignLeft=false){
    if (!container) return 0;
    removeSkel(container);
    container.classList.toggle('ft-left', !!alignLeft);

    const list = Array.isArray(links) ? links : [];
    const clean = list
      .map((x) => {
        const title = (x?.title ?? x?.label ?? x?.name ?? '').toString().trim();
        const url = (x?.url_full ?? x?.url ?? x?.link ?? x?.href ?? x?.path ?? '').toString().trim();
        if (!title) return null;
        return { title, url };
      })
      .filter(Boolean);

    container.innerHTML = '';

    clean.forEach((it) => {
      const hasUrl = !!it.url && it.url !== '#';
      const a = document.createElement('a');
      a.className = 'ft-link';
      a.textContent = it.title;

      if (hasUrl) {
        const href = normalizeUrl(it.url);
        a.href = href;

        const isExternal = /^https?:\/\//i.test(href) && !href.startsWith(window.location.origin);
        if (isExternal) {
          a.target = '_blank';
          a.rel = 'noopener';
        } else {
          // ✅ internal links use hardNavigate to prevent 404 issues
          a.addEventListener('click', (e) => hardNavigate(e, href, false));
        }
      } else {
        a.href = 'javascript:void(0)';
        a.style.pointerEvents = 'none';
        a.style.opacity = '.9';
      }

      container.appendChild(a);
    });

    return clean.length;
  }

  /* ===== Section 2: Header menus ===== */

  function pickList(js){
    if (Array.isArray(js?.data)) return js.data;
    if (Array.isArray(js?.items)) return js.items;
    if (Array.isArray(js)) return js;
    return [];
  }

  function firstNonEmptyArray(...candidates){
    for (const c of candidates){
      const arr = safeArray(c);
      if (arr.length) return arr;
    }
    return [];
  }

  function normalizeDepartmentsPayload(payload){
    let data = payload;
    if (data && typeof data === 'object' && data.success !== undefined) data = data.data;

    if (Array.isArray(data)) return data;
    if (Array.isArray(data?.items)) return data.items;
    if (Array.isArray(data?.data)) return data.data;
    return [];
  }

  function buildDeptMap(list){
    __ftDeptUuidById.clear();
    (list || []).forEach(d => {
      const id = Number(d?.id);
      const uuid = (d?.uuid ?? d?.department_uuid ?? d?.dept_uuid ?? '').toString().trim();
      if (id && uuid) __ftDeptUuidById.set(id, uuid);
    });
    __ftDeptMapLoaded = true;
  }

  async function ensureDeptMap(){
    if (__ftDeptMapLoaded) return;

    const publicEp = els.bar?.getAttribute('data-public-departments-endpoint') || '/api/public/departments';
    const privateEp = els.bar?.getAttribute('data-departments-endpoint') || '/api/departments';

    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    const headers = { 'Accept': 'application/json' };
    if (token) headers['Authorization'] = 'Bearer ' + token;

    try{
      // 1) try public
      let res = await fetch(publicEp, { headers: { 'Accept':'application/json' } });
      let js = await res.json().catch(() => ({}));

      if (res.ok){
        buildDeptMap(normalizeDepartmentsPayload(js));
        return;
      }

      // 2) fallback private (if auth exists)
      res = await fetch(privateEp, { headers });
      js = await res.json().catch(() => ({}));

      if (res.ok){
        buildDeptMap(normalizeDepartmentsPayload(js));
        return;
      }
    }catch(_){}

    // no hard fail
    buildDeptMap([]);
  }

  /**
   * ✅ FIXED: fetch header menus WITH children
   *
   * CRITICAL CHANGE: Try the base /tree endpoint FIRST (not /options).
   * The /tree endpoint returns complete data including page_slug.
   * The /options endpoint may omit page_slug, causing fallback to menu slug.
   */
  async function fetchHeaderMenus(){
    const base =
      els.bar?.getAttribute('data-header-menus-endpoint') ||
      els.bar?.getAttribute('data-menu-endpoint') ||
      '/api/public/header-menus/tree';

    const b = base.replace(/\/+$/,'');

    // ✅ FIXED ORDER: Try base /tree endpoints FIRST (they include page_slug),
    //    then fall back to /options endpoints (which may omit page_slug).
    const candidates = [
      b + '?include_children=1&only_active=1&per_page=200',
      b + '?include_children=1&per_page=200',
      b + '?per_page=200',
      b,
      b + '/options?include_children=1&only_active=1',
      b + '/options?include_children=1',
      b + '/options',
    ];

    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    const headers = { 'Accept': 'application/json' };
    if (token) headers['Authorization'] = 'Bearer ' + token;

    for (const url of candidates){
      try{
        const res = await fetch(url, { headers });
        const js = await res.json().catch(() => ({}));
        if (!res.ok) continue;

        const list = pickList(js);
        if (!list.length) continue;

        const map = new Map();

        /**
         * ✅ FIXED: Recursively index ALL menu items (parents + children at all depths)
         * into the map so that any item can be looked up by ID with full data
         * including page_slug, page_url, slug, department_uuid, etc.
         */
        function indexMenuTree(items) {
          (items || []).forEach(m => {
            const id = Number(m?.id ?? m?.header_menu_id);
            if (!Number.isFinite(id) || id <= 0) return;

            const title = (m?.title ?? m?.name ?? m?.label ?? m?.menu_title ?? '').toString().trim();
            const children = firstNonEmptyArray(m?.children, m?.submenus, m?.childs, m?.items);

            map.set(id, { ...m, id, title, children });

            // Recurse into children so they're also indexed with full data
            if (children.length) {
              indexMenuTree(children);
            }
          });
        }

        indexMenuTree(list);

        if (map.size) return map;
      }catch(_){}
    }

    return new Map();
  }

  /* ===== Footer submenu link resolution (same style as header) ===== */

  function getItemDeptUuid(item){
    const direct =
      (item?.department_uuid ?? item?.dept_uuid ?? '') ||
      (item?.department?.uuid ?? item?.department?.department_uuid ?? item?.department?.dept_uuid ?? '');
    const directTrim = (direct || '').toString().trim();
    if (directTrim) return directTrim;

    const did = item?.department_id;
    if (did !== undefined && did !== null) {
      const mapped = __ftDeptUuidById.get(Number(did));
      if (mapped) return mapped;
    }
    return '';
  }

  function getItemMenuUuid(item){
    const uuid = (item?.uuid ?? item?.menu_uuid ?? '').toString().trim();
    return uuid || '';
  }

  function applyDepartmentUuid(url, deptUuid) {
    deptUuid = (deptUuid || '').toString().trim();
    if (!deptUuid) return url;
    if (!url || url === '#') return url;

    let u;
    try {
      u = new URL(url, window.location.origin);
    } catch (e) {
      const token = `d-${deptUuid}`;
      const sep = url.includes('?') ? '&' : '?';
      return `${url}${sep}${token}`;
    }

    if (u.origin !== window.location.origin) return url;

    const token = `d-${deptUuid}`;
    const raw = (u.search || '').replace(/^\?/, '');
    const parts = raw ? raw.split('&').filter(Boolean) : [];

    const kept = parts.filter(p => {
      const key = (p.split('=')[0] || '').trim();
      if (!key) return false;
      if (key === 'department_uuid') return false;
      if (key.startsWith('d-')) return false;
      return true;
    });

    const newSearch = kept.length ? (`?${kept.join('&')}&${token}`) : (`?${token}`);
    return `${u.origin}${u.pathname}${newSearch}${u.hash || ''}`;
  }

  function applyHeaderMenuHUuid(url, hUuid) {
    hUuid = (hUuid || '').toString().trim();
    if (!hUuid) return url;
    if (!url || url === '#') return url;

    let u;
    try {
      u = new URL(url, window.location.origin);
    } catch (e) {
      const sep = url.includes('?') ? '&' : '?';
      return `${url}${sep}h-${hUuid}`;
    }

    if (u.origin !== window.location.origin) return url;

    const raw = (u.search || '').replace(/^\?/, '');
    const parts = raw ? raw.split('&').filter(Boolean) : [];

    const kept = parts.filter(p => {
      const key = (p.split('=')[0] || '').trim();
      if (!key) return false;
      if (key === 'header_menu_id') return false;
      if (/^h-[a-zA-Z0-9_-]+$/.test(key)) return false;
      return true;
    });

    const token = `h-${hUuid}`;
    const newSearch = kept.length ? `?${kept.join('&')}&${token}` : `?${token}`;
    return `${u.origin}${u.pathname}${newSearch}${u.hash || ''}`;
  }

  function getNodeLink(item){
    return (item?.page_url ?? item?.link ?? item?.url ?? item?.href ?? '').toString().trim();
  }
  function getNodePageSlug(item){
    return (item?.page_slug ?? '').toString().trim();
  }
  function getNodeMenuSlug(item){
    return (item?.slug ?? '').toString().trim();
  }
  function getNodeShortcode(item){
    return (item?.shortcode ?? item?.page_shortcode ?? item?.short_code ?? '').toString().trim();
  }

  /**
   * ✅ FIXED: Resolve link URL always relative to SITE ORIGIN, not current href.
   * This prevents 404 errors when navigating from /page/x to another link.
   */
  function resolveLinkUrl(rawUrl){
    rawUrl = (rawUrl || '').toString().trim();
    if (!rawUrl) return '';

    // Already absolute
    if (/^https?:\/\//i.test(rawUrl)) return rawUrl;

    // Special protocols
    if (/^(mailto:|tel:|sms:|whatsapp:|data:|blob:)/i.test(rawUrl)) return rawUrl;

    // ✅ CRITICAL FIX: Always resolve relative to ORIGIN, not current page href
    try {
      if (rawUrl.startsWith('/')) {
        return window.location.origin + rawUrl;
      }
      return window.location.origin + '/' + rawUrl;
    } catch(e) {
      return rawUrl;
    }
  }

  function isSameOrigin(url){
    try{
      return new URL(url, window.location.origin).origin === window.location.origin;
    }catch(e){
      return false;
    }
  }

  function isSpecialProtocol(url){
    return /^(mailto:|tel:|sms:|whatsapp:)/i.test((url || '').toString().trim());
  }

  /**
   * ✅ Determine if a link value is a direct route or should go through /page/{slug}
   */
  function isDirectLink(rawUrl) {
    const s = (rawUrl || '').toString().trim();
    if (!s) return false;

    // Absolute external URLs are always direct
    if (/^https?:\/\//i.test(s)) return true;

    // Special protocols
    if (/^(mailto:|tel:|sms:|whatsapp:)/i.test(s)) return true;

    // If it already starts with /page/ treat as direct (already has /page prefix)
    if (s.toLowerCase().startsWith('/page/')) return true;

    // Normalize to a path for route matching
    let path = s;
    if (!path.startsWith('/')) path = '/' + path;

    // Remove query strings and hashes for matching
    const cleanPath = path.split('?')[0].split('#')[0].replace(/\/+$/, '').toLowerCase();

    // Check against known routes
    if (isKnownDirectRoute(cleanPath)) return true;

    // Check if it looks like a path with segments (e.g. /courses/view/xxx)
    // These are likely direct routes, not page slugs
    if (cleanPath.split('/').filter(Boolean).length >= 2) return true;

    return false;
  }

  /**
   * ✅ FIXED: Priority for resolving the best navigation target from a menu item.
   *
   * Priority order:
   *   1. page_url (link) — if it exists and is not empty / "#"
   *   2. page_slug        — navigate to /page/{page_slug}
   *   3. slug (menu_slug) — fallback to /page/{slug}
   *   4. shortcode        — fallback to /page/{shortcode}
   *   5. First descendant with any valid target
   *
   * KEY FIX: link="#" is SKIPPED (treated as empty), so page_slug takes over.
   * This ensures items like "About Us" (slug="about-us", page_slug="about-us-3")
   * navigate to /page/about-us-3 instead of /page/about-us.
   */
  function findBestTarget(item){
    if (!item) return { type:'', value:'' };

    // 1. Check page_url / link first — but SKIP if it's just "#"
    const link = getNodeLink(item);
    if (link && link !== '#') return { type:'link', value: link };

    // 2. Check page_slug (THIS IS THE KEY — use page_slug before menu slug)
    const ps = getNodePageSlug(item);
    if (ps) return { type:'page_slug', value: ps };

    // 3. Fallback to menu slug
    const ms = getNodeMenuSlug(item);
    if (ms) return { type:'menu_slug', value: ms };

    // 4. Fallback to shortcode
    const sc = getNodeShortcode(item);
    if (sc) return { type:'shortcode', value: sc };

    // 5. Fallback: find first child that has any valid target (same priority)
    const kids = Array.isArray(item.children) ? item.children : [];
    if (!kids.length) return { type:'', value:'' };

    const sortedKids = [...kids].sort((a,b) => (a.position||0) - (b.position||0));
    for (const child of sortedKids){
      const found = findBestTarget(child);
      if (found && found.value) return found;
    }
    return { type:'', value:'' };
  }

  /**
   * ✅ Build internal page URL using absolute PAGE_BASE from Blade
   */
  function normalizeInternalPageUrl(identifier){
    identifier = (identifier || '').toString().trim();
    if (!identifier) return '#';
    return PAGE_BASE + '/' + encodeURIComponent(identifier);
  }

  /**
   * ✅ FIXED: Main URL builder for footer menu items.
   *
   * Rules:
   *  - If target is a "link" (page_url):
   *      - If it's external or special protocol => use as-is
   *      - If it matches a known direct route => resolve as direct: /{link}
   *      - If it already contains /page/ => resolve as direct (already prefixed)
   *      - Otherwise => treat as a dynamic page: /page/{link}
   *  - If target is page_slug => /page/{page_slug}
   *  - If target is menu_slug / shortcode => /page/{slug}
   *  - Then append d-{uuid} and h-{uuid} for same-origin URLs
   */
  function getFooterMenuItemUrl(item){
    let url = '#';
    const target = findBestTarget(item);

    if (target.type === 'link'){
      const rawLink = target.value;

      // Special protocol => return directly
      if (isSpecialProtocol(rawLink)) return rawLink;

      // External absolute URL => return directly
      if (/^https?:\/\//i.test(rawLink) && !rawLink.startsWith(window.location.origin)) {
        return rawLink;
      }

      // ✅ Determine if this is a direct route or a dynamic page slug
      if (isDirectLink(rawLink)) {
        // Direct route: resolve as /{link}
        url = resolveLinkUrl(rawLink) || '#';
      } else {
        // Not a known direct route => treat as dynamic page: /page/{link}
        url = normalizeInternalPageUrl(rawLink);
      }
    }
    else if (target.type === 'page_slug'){
      url = normalizeInternalPageUrl(target.value);
    }
    else if (target.type === 'menu_slug'){
      url = normalizeInternalPageUrl(target.value);
    }
    else if (target.type === 'shortcode'){
      url = normalizeInternalPageUrl(target.value);
    }

    if (!url || url === '#') return '#';

    // ✅ Only mutate same-origin URLs with dept/header_menu_id
    if (isSameOrigin(url)) {
      const deptUuid = getItemDeptUuid(item);
      url = applyDepartmentUuid(url, deptUuid);

      const hUuid = getItemMenuUuid(item);
      url = applyHeaderMenuHUuid(url, hUuid);
    }

    return url;
  }

  function renderSection2(container, section2Blocks, menuMap, titleOverride){
    if (!container) return 0;
    removeSkel(container);
    container.innerHTML = '';

    const blocks = safeArray(section2Blocks);

    const normalized = blocks
      .slice(0, 4)
      .map(b => {
        const hasTitle = (b?.title || b?.menu_title || b?.name || b?.label);
        const hasKids  = Array.isArray(b?.submenus) || Array.isArray(b?.children) || Array.isArray(b?.childs);

        if (hasTitle && hasKids){
          const title = (b.title || b.menu_title || b.name || b.label || '').toString().trim();
          const kids = safeArray(b.submenus ?? b.children ?? b.childs ?? []);
          return { title, kids, headerMenuId: Number(b?.id ?? b?.header_menu_id ?? 0), childIdSet: null };
        }

        const headerMenuId = Number(b?.header_menu_id ?? b?.menu_id ?? b?.id ?? 0);
        const childIds = safeArray(b?.child_ids ?? b?.children_ids ?? b?.submenu_ids ?? []);
        const childIdSet = new Set(childIds.map(x => Number(x)).filter(n => Number.isFinite(n)));
        return { title: '', kids: null, headerMenuId, childIdSet };
      })
      .filter(b => Number.isFinite(b.headerMenuId) && b.headerMenuId > 0);

    if (!normalized.length) return 0;

    normalized.forEach((b) => {
      const menu = menuMap.get(b.headerMenuId) || null;

      const resolvedTitle =
        (b.title || '').trim() ||
        ((titleOverride && normalized.length === 1) ? String(titleOverride).trim() : '') ||
        ((menu?.title || menu?.name || menu?.label || '').toString().trim()) ||
        'Menu';

      let kids = b.kids;
      if (!kids){
        const rawKids = safeArray(menu?.children ?? menu?.submenus ?? menu?.childs ?? []);
        if (b.childIdSet && b.childIdSet.size){
          kids = rawKids.filter(ch => {
            const id = Number(ch?.id);
            return Number.isFinite(id) && b.childIdSet.has(id);
          });
        } else {
          kids = rawKids;
        }
      }

      /**
       * ✅ FIXED: Enrich each child item with full data from menuMap.
       * The footer's section2 blocks may only store IDs/basic info.
       * The menuMap (from /tree endpoint) has the full item including page_slug.
       * Merge them so getFooterMenuItemUrl sees page_slug correctly.
       *
       * Priority: menuMap's page_slug is authoritative (from the /tree API).
       * If the child already has page_slug, keep it. Otherwise fill from menuMap.
       */
      kids = (kids || []).map(ch => {
        const childId = Number(ch?.id);
        if (childId && menuMap.has(childId)) {
          const fullItem = menuMap.get(childId);
          // Merge: start with full API data, overlay with child-specific data,
          // but always prefer the non-empty page_slug from API tree
          return {
            ...fullItem,
            ...ch,
            page_slug: fullItem.page_slug || ch.page_slug || '',
            page_url: ch.page_url !== undefined ? ch.page_url : fullItem.page_url,
            department_uuid: ch.department_uuid || fullItem.department_uuid || '',
            department_id: ch.department_id ?? fullItem.department_id,
          };
        }
        return ch;
      });

      const wrap = document.createElement('div');
      wrap.className = 'ft-block';

      const h = document.createElement('div');
      h.className = 'ft-block-title';
      h.textContent = resolvedTitle;

      const grid = document.createElement('div');
      grid.className = 'ft-block-grid';

      (kids || []).forEach(ch => {
        const label = (ch?.title || ch?.name || ch?.label || 'Menu').toString().trim();
        if (!label) return;

        // ✅ FIXED: use same URL resolution as header — page_slug takes priority over slug
        const href = getFooterMenuItemUrl(ch);

        const a = document.createElement('a');
        a.className = 'ft-block-item';
        a.textContent = label;
        a.href = href || 'javascript:void(0)';

        if (!href || href === '#') {
          a.href = 'javascript:void(0)';
          a.style.pointerEvents = 'none';
          a.style.opacity = '.9';
        } else if (/^https?:\/\//i.test(a.href) && !a.href.startsWith(window.location.origin)){
          a.target = '_blank';
          a.rel = 'noopener';
        } else if (isSpecialProtocol(a.href)) {
          // mailto/tel — let browser handle naturally
        } else {
          // ✅ CRITICAL: use hardNavigate for all internal links
          a.addEventListener('click', (e) => hardNavigate(e, a.href, false));
        }

        grid.appendChild(a);
      });

      wrap.appendChild(h);
      wrap.appendChild(grid);
      container.appendChild(wrap);
    });

    return normalized.length;
  }

  /* ===== Socials ===== */
  function iconFromPlatform(p){
    const s = (p || '').toString().toLowerCase().trim();
    if (s.includes('youtube')) return 'fa-brands fa-youtube';
    if (s.includes('linkedin')) return 'fa-brands fa-linkedin-in';
    if (s.includes('facebook')) return 'fa-brands fa-facebook-f';
    if (s.includes('instagram')) return 'fa-brands fa-instagram';
    if (s.includes('twitter') || s.includes('x.com') || s === 'x') return 'fa-brands fa-x-twitter';
    if (s.includes('github')) return 'fa-brands fa-github';
    return 'fa-solid fa-link';
  }

  function renderSocial(container, socials){
    if (!container) return 0;
    removeSkel(container);
    container.innerHTML = '';

    const list = Array.isArray(socials) ? socials : [];
    const clean = list
      .map(x => {
        const url = (x?.url_full ?? x?.url ?? x?.link ?? x?.href ?? '').toString().trim();
        const platform = (x?.platform ?? x?.title ?? x?.label ?? '').toString().trim();
        const icon = (x?.icon ?? '').toString().trim();
        if (!url) return null;
        return { url, platform, icon };
      })
      .filter(Boolean);

    clean.forEach(s => {
      const a = document.createElement('a');
      a.className = 'ft-social-btn';
      a.href = normalizeUrl(s.url);
      a.target = '_blank';
      a.rel = 'noopener';
      a.setAttribute('aria-label', s.platform || 'social');

      const i = document.createElement('i');
      i.className = s.icon ? s.icon : iconFromPlatform(s.platform);
      a.appendChild(i);

      container.appendChild(a);
    });

    return clean.length;
  }

  function pickLatestItem(js){
    const arr = Array.isArray(js?.data) ? js.data : [];
    return arr[0] || null;
  }

  async function fetchLatestFooter(){
    const base = els.bar?.getAttribute('data-endpoint') || '/api/footer-components';
    const qs = new URLSearchParams({ per_page:'1', page:'1', sort:'updated_at', direction:'desc' });

    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    const headers = { 'Accept': 'application/json' };
    if (token) headers['Authorization'] = 'Bearer ' + token;

    const res = await fetch(base.replace(/\/+$/,'') + '?' + qs.toString(), { headers });
    const js = await res.json().catch(() => ({}));
    const item = pickLatestItem(js);
    return { res, js, item };
  }

  async function fetchLatestHeader(){
    const base = els.bar?.getAttribute('data-header-endpoint') || '/api/header-components';
    const qs = new URLSearchParams({ per_page:'1', page:'1', sort:'updated_at', direction:'desc' });

    const token = sessionStorage.getItem('token') || localStorage.getItem('token') || '';
    const headers = { 'Accept': 'application/json' };
    if (token) headers['Authorization'] = 'Bearer ' + token;

    const res = await fetch(base.replace(/\/+$/,'') + '?' + qs.toString(), { headers });
    const js = await res.json().catch(() => ({}));
    return pickLatestItem(js);
  }

  function toggleRule(ruleEl, show){
    if (!ruleEl) return;
    ruleEl.style.display = show ? '' : 'none';
  }

  /* ===== Main render ===== */
  async function renderFooter(){
    const { res, item } = await fetchLatestFooter();

    if (!res.ok || !item){
      removeSkel(els.topLinks); if (els.topLinks) els.topLinks.innerHTML = '';
      if (els.blocks) els.blocks.innerHTML = '';
      removeSkel(els.midLinks); if (els.midLinks) els.midLinks.innerHTML = '';
      setImg(els.brandLogo, '');
      setText(els.brandTitle, '');
      setText(els.brandRotate, '');
      if (els.social) els.social.innerHTML = '';
      if (els.bottomLinks) els.bottomLinks.innerHTML = '';
      if (els.copyright) els.copyright.textContent = '';
      if (els.address) { els.address.style.display = 'none'; els.address.textContent = ''; }
      return;
    }

    const meta = safeObject(item?.metadata) || {};

    // ✅ SECTION 1
    const s1 = safeArray(item?.section1_menu ?? item?.section1_menu_json ?? meta?.section1_menu ?? meta?.section1_menu_json ?? []);
    const topCount = renderInlineLinks(els.topLinks, s1, false);
    toggleRule(els.rule1, true);

    // ✅ build dept map first (so footer submenu links can append d-{uuid} like header)
    await ensureDeptMap();

    // ✅ SECTION 2 (resolve header menu title + children via header menus endpoint)
    const s2blocks = safeArray(
      item?.section2_header_menus_resolved ??
      meta?.section2_header_menus_resolved ??
      item?.section2_header_menu_json ??
      item?.section2_header_menus ??
      meta?.section2_header_menu_json ??
      meta?.section2_header_menus ??
      []
    );

    const menuMap = await fetchHeaderMenus();
    const blockCount = renderSection2(els.blocks, s2blocks, menuMap, item?.section2_title_override ?? null);
    toggleRule(els.rule2, true);

    // ✅ SECTION 3
    const s3 = safeArray(item?.section3_menu ?? item?.section3_menu_json ?? meta?.section3_menu ?? meta?.section3_menu_json ?? []);
    renderInlineLinks(els.midLinks, s3, true);
    toggleRule(els.rule3, true);

    // ✅ SECTION 4 (brand)
    const sameAsHeader = !!(item?.same_as_header ?? item?.is_same_as_header ?? false);

    let brandLogo = (item?.brand_logo_full_url || item?.brand_logo_url || '').toString().trim();
    let brandTitle = (item?.brand_title || item?.footer_title || '').toString().trim();
    let rotateLines = safeArray(item?.rotating_text_json ?? []);

    if (sameAsHeader){
      try{
        const h = await fetchLatestHeader();
        if (h){
          brandLogo = (h?.primary_logo_full_url || h?.primary_logo_url || brandLogo).toString().trim();
          brandTitle = (h?.header_text || brandTitle).toString().trim();
          rotateLines = safeArray(h?.rotating_text_json || rotateLines);
        }
      }catch(_){}
    }

    setImg(els.brandLogo, brandLogo);
    setText(els.brandTitle, brandTitle);
    startRotate(rotateLines);

    // ✅ SOCIALS
    const socials = safeArray(item?.social_links ?? item?.social_links_json ?? []);
    renderSocial(els.social, socials);
    toggleRule(els.rule4, true);

    // ✅ SECTION 5
    const s5 = safeArray(item?.section5_menu ?? item?.section5_menu_json ?? meta?.section5_menu ?? meta?.section5_menu_json ?? []);

    if (els.bottomLinks) els.bottomLinks.innerHTML = '';

    const cleanS5 = (Array.isArray(s5) ? s5 : [])
      .map(x => {
        const title = (x?.title ?? x?.label ?? x?.name ?? '').toString().trim();
        const url = (x?.url_full ?? x?.url ?? x?.link ?? x?.href ?? '').toString().trim();
        if (!title) return null;
        return { title, url };
      })
      .filter(Boolean);

    cleanS5.forEach((it, idx) => {
      const a = document.createElement('a');
      a.className = 'ft-link';
      a.textContent = it.title;

      const hasUrl = !!it.url && it.url !== '#';
      if (hasUrl){
        const href = normalizeUrl(it.url);
        a.href = href;
        const isExternal = /^https?:\/\//i.test(href) && !href.startsWith(window.location.origin);
        if (isExternal){
          a.target = '_blank';
          a.rel = 'noopener';
        } else {
          // ✅ internal links use hardNavigate
          a.addEventListener('click', (e) => hardNavigate(e, href, false));
        }
      } else {
        a.href = 'javascript:void(0)';
        a.style.pointerEvents = 'none';
        a.style.opacity = '.9';
      }

      els.bottomLinks && els.bottomLinks.appendChild(a);

      if (idx < cleanS5.length - 1){
        const sep = document.createElement('span');
        sep.textContent = '|';
        sep.style.opacity = '.85';
        sep.style.margin = '0 4px';
        els.bottomLinks && els.bottomLinks.appendChild(sep);
      }
    });

    const copy = (item?.copyright_text || meta?.copyright_text || meta?.copyright || '').toString().trim();
    if (els.copyright){
      els.copyright.textContent = copy || '';
      removeSkel(els.copyright);
    }

    const address = (item?.address_text || meta?.address_text || meta?.address || meta?.address_line || '').toString().trim();
    if (els.address){
      if (address){
        els.address.style.display = '';
        els.address.textContent = address;
      } else {
        els.address.style.display = 'none';
        els.address.textContent = '';
      }
    }

    if (!blockCount){
      console.warn('[Footer] Section2: No header menu blocks rendered. Check: (1) footer row section2_header_menu_json, (2) #ftBar data-header-menus-endpoint, (3) header menus API returns children.');
      console.warn('[Footer] Section2 raw:', s2blocks);
      console.warn('[Footer] HeaderMenu map size:', menuMap.size);
    }
    if (!topCount){
      console.warn('[Footer] Section1 rendered empty. Raw:', s1);
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    renderFooter().catch((e) => console.warn('[Footer] render error', e));
  });
})();
</script>
@endonce