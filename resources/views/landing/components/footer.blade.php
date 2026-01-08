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
    /* =========================================================
       Public Footer (Render) – UI exactly like screenshot
       ========================================================= */

    :root{
      --ft-bg: var(--primary-color, #9E363A);
      --ft-ink: #ffffff;
      --ft-ink-soft: rgba(255,255,255,.88);
      --ft-rule: rgba(255,255,255,.70);

      --ft-accent: #E2B13C;        /* golden */
      --ft-accent-2: #F2C94C;

      --ft-max: 1280px;

      --ft-link-size: 18px;
      --ft-link-gap: 26px;

      --ft-social-size: 56px;
      --ft-social-icon: 30px;
    }

    .ft-wrap, .ft-wrap *{ box-sizing:border-box; }

    .ft-wrap{
      width:100%;
      background:var(--ft-bg);
      color:var(--ft-ink);
    }

    .ft-inner{
      max-width:var(--ft-max);
      margin:0 auto;
      padding:22px 16px 18px;
    }

    .ft-rule{
      height:0;
      border:none;
      border-top:2px solid var(--ft-rule);
      margin:18px 0;
      opacity:1;
    }

    /* ===== Inline link rows ===== */
    .ft-inline-links{
      display:flex;
      flex-wrap:wrap;
      justify-content:center;
      gap:12px var(--ft-link-gap);
      padding:6px 0;
    }
    .ft-inline-links.ft-left{ justify-content:flex-start; }

    .ft-link{
      color:var(--ft-ink);
      text-decoration:none;
      font-size:var(--ft-link-size);
      line-height:1.25;
      white-space:nowrap;
      border-bottom:2px solid transparent;
      padding-bottom:2px;
      transition:opacity .12s ease, border-color .12s ease;
    }
    .ft-link:hover{
      opacity:.95;
      border-color:var(--ft-accent);
      text-decoration:none;
    }

    /* ===== Menu blocks ===== */
    .ft-blocks{ display:flex; flex-direction:column; gap:22px; }

    .ft-block-title{
      font-weight:800;
      font-size:32px;
      margin:0 0 14px 0;
      letter-spacing:.2px;
      position:relative;
      display:inline-block;
    }
    .ft-block-title::after{
      content:"";
      position:absolute;
      left:0;
      bottom:-6px;
      width:78px;
      height:4px;
      background:var(--ft-accent);
      border-radius:6px;
    }

    .ft-block-grid{
      display:grid;
      grid-template-columns:repeat(5, minmax(0, 1fr));
      gap:18px 40px;
    }

    .ft-block-item{
      color:var(--ft-ink);
      text-decoration:none;
      font-size:20px;
      line-height:1.35;
      opacity:.98;
      transition:opacity .12s ease, transform .12s ease;
    }
    .ft-block-item:hover{
      opacity:1;
      transform:translateY(-1px);
      text-decoration:none;
    }

    /* ===== Brand row ===== */
    .ft-brand-row{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:18px;
      padding:6px 0;
    }

    .ft-brand-left{
      display:flex;
      align-items:center;
      gap:14px;
      min-width:0;
    }

    .ft-brand-logo{
      width:92px;
      height:92px;
      object-fit:contain;
      border-radius:999px;
      background:rgba(255,255,255,.12);
      padding:8px;
    }

    .ft-brand-text{ min-width:0; display:flex; flex-direction:column; gap:4px; }

    .ft-brand-title{
      font-weight:800;
      font-size:30px;
      line-height:1.15;
      margin:0;
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
    }

    .ft-brand-rotate{
      color:var(--ft-ink-soft);
      font-size:20px;
      line-height:1.25;
      margin:0;
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
      transition:opacity .18s ease, transform .18s ease;
    }
    .ft-brand-rotate.is-fading{ opacity:0; transform:translateY(-2px); }

    /* ===== Social buttons ===== */
    .ft-social{
      display:flex;
      align-items:center;
      gap:14px;
      flex:0 0 auto;
    }
    .ft-social-btn{
      width:var(--ft-social-size);
      height:var(--ft-social-size);
      display:inline-flex;
      align-items:center;
      justify-content:center;
      border:2px solid var(--ft-accent);
      color:var(--ft-accent-2);
      text-decoration:none;
      background:transparent;
      transition:transform .12s ease, background .12s ease, filter .12s ease;
    }
    .ft-social-btn i{ font-size:var(--ft-social-icon); line-height:1; }
    .ft-social-btn:hover{
      transform:translateY(-1px);
      background:rgba(255,255,255,.08);
      filter:drop-shadow(0 8px 18px rgba(0,0,0,.12));
      text-decoration:none;
    }

    /* ===== Bottom row ===== */
    .ft-bottom-row{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:16px;
    }

    .ft-bottom-links{
      display:flex;
      flex-wrap:wrap;
      gap:12px 22px;
      align-items:center;
    }

    .ft-copy{
      color:var(--ft-ink-soft);
      font-size:18px;
      line-height:1.25;
      text-align:right;
      white-space:nowrap;
    }

    .ft-address{
      margin-top:10px;
      color:var(--ft-ink);
      font-size:18px;
      line-height:1.35;
      opacity:.98;
    }

    /* ===== Skeleton ===== */
    .ft-skel{
      background:linear-gradient(90deg, rgba(255,255,255,.10), rgba(255,255,255,.18), rgba(255,255,255,.10));
      background-size:200% 100%;
      animation:ft-skel 1.1s ease-in-out infinite;
      border-radius:10px;
    }
    @keyframes ft-skel{ 0%{ background-position:200% 0; } 100%{ background-position:-200% 0; } }
    .ft-skel-title{ height:38px; width:220px; }
    .ft-skel-grid{ height:120px; width:100%; border-radius:14px; }

    @media (max-width: 1100px){
      .ft-block-grid{ grid-template-columns:repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 780px){
      .ft-inline-links{ justify-content:flex-start; }
      .ft-block-grid{ grid-template-columns:repeat(2, minmax(0, 1fr)); gap:14px 22px; }
      .ft-block-title{ font-size:28px; }
      .ft-brand-row{ flex-direction:column; align-items:flex-start; }
      .ft-copy{ text-align:left; white-space:normal; }
    }
    @media (prefers-reduced-motion: reduce){
      .ft-brand-rotate{ transition:none !important; }
    }
  </style>
@endonce

<footer
  class="ft-wrap"
  id="ftBar"
  data-endpoint="{{ url('/api/footer-components') }}"
  data-header-endpoint="{{ url('/api/header-components') }}"
  data-menu-endpoint="{{ url('/api/header-menus') }}"
>
  <div class="ft-inner">

    {{-- Section 1 (top links) --}}
    <div id="ftTopLinks" class="ft-inline-links ft-skel" style="min-height:24px;"></div>

    <hr class="ft-rule" id="ftRule1">

    {{-- Section 2 (menu blocks: max 4) --}}
    <div id="ftBlocks" class="ft-blocks">
      <div class="ft-skel ft-skel-title"></div>
      <div class="ft-skel ft-skel-grid mt-2"></div>
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

  const PLACEHOLDER_LOGO = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
    <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120">
      <rect width="120" height="120" rx="60" fill="rgba(255,255,255,.14)"/>
      <circle cx="60" cy="52" r="18" fill="rgba(255,255,255,.35)"/>
      <path d="M28 98c8-18 22-26 32-26s24 8 32 26" fill="none" stroke="rgba(255,255,255,.45)" stroke-width="6" stroke-linecap="round"/>
    </svg>
  `);

  function normalizeUrl(u){
    const s = (u || '').toString().trim();
    if (!s) return '';
    if (/^(data:|blob:|https?:\/\/|mailto:|tel:)/i.test(s)) return s;
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

    container.innerHTML = clean.map((it) => {
      const hasUrl = !!it.url && it.url !== '#';
      const href = hasUrl ? normalizeUrl(it.url) : 'javascript:void(0)';

      let extra = '';
      if (hasUrl){
        const isExternal = /^https?:\/\//i.test(href) && !href.startsWith(window.location.origin);
        if (isExternal) extra = ` target="_blank" rel="noopener"`;
      } else {
        extra = ` style="pointer-events:none;opacity:.9"`;
      }

      return `<a class="ft-link" href="${href}"${extra}>${escapeHtml(it.title)}</a>`;
    }).join('');

    return clean.length;
  }

  /* ===== Section 2: Header menus ===== */

  function pickList(js){
    if (Array.isArray(js?.data)) return js.data;
    if (Array.isArray(js?.items)) return js.items;
    if (Array.isArray(js)) return js;
    return [];
  }

  // ✅ fetch header menus WITH children (tries multiple endpoints/shapes)
  async function fetchHeaderMenus(){
    // You can set on #ftBar:
    // data-header-menus-endpoint="/api/header-menus"
    // data-menu-endpoint="/api/header-menus" (fallback)
    const base =
      els.bar?.getAttribute('data-header-menus-endpoint') ||
      els.bar?.getAttribute('data-menu-endpoint') ||
      '/api/header-menus';

    const b = base.replace(/\/+$/,'');

    const candidates = [
      // common patterns
      b + '/options?include_children=1&only_active=1',
      b + '/options?include_children=1',
      b + '?include_children=1&only_active=1&per_page=200',
      b + '?include_children=1&per_page=200',
      b + '?per_page=200',
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
        list.forEach(m => {
          const id = Number(m?.id ?? m?.header_menu_id);
          if (!Number.isFinite(id) || id <= 0) return;

          // normalize title + children keys
          const title = (m?.title ?? m?.name ?? m?.label ?? m?.menu_title ?? '').toString().trim();

          const children =
            safeArray(m?.children) ||
            safeArray(m?.submenus) ||
            safeArray(m?.childs) ||
            safeArray(m?.items) ||
            [];

          map.set(id, { ...m, id, title, children });
        });

        if (map.size) return map;
      }catch(_){}
    }

    return new Map();
  }

  function childHref(ch){
    const u = (ch?.url_full ?? ch?.url ?? ch?.link ?? ch?.href ?? ch?.path ?? ch?.full_url ?? '').toString().trim();
    if (u) return normalizeUrl(u);

    const slug = (ch?.slug ?? ch?.page_slug ?? '').toString().trim();
    if (slug) return normalizeUrl('/' + slug.replace(/^\/+/,''));

    return 'javascript:void(0)';
  }

  function renderSection2(container, section2Blocks, menuMap, titleOverride){
    if (!container) return 0;
    removeSkel(container);
    container.innerHTML = '';

    const blocks = safeArray(section2Blocks);

    // supports BOTH shapes:
    // A) stored tree: [{id,title,submenus:[...]}]
    // B) your current API: [{header_menu_id:3, child_ids:[]}]
    const normalized = blocks
      .slice(0, 4)
      .map(b => {
        // A) already has title + submenus/children
        const hasTitle = (b?.title || b?.menu_title || b?.name || b?.label);
        const hasKids  = Array.isArray(b?.submenus) || Array.isArray(b?.children) || Array.isArray(b?.childs);

        if (hasTitle && hasKids){
          const title = (b.title || b.menu_title || b.name || b.label || '').toString().trim();
          const kids = safeArray(b.submenus ?? b.children ?? b.childs ?? []);
          return { title, kids, headerMenuId: Number(b?.id ?? b?.header_menu_id ?? 0), childIdSet: null };
        }

        // B) id-only block
        const headerMenuId = Number(b?.header_menu_id ?? b?.menu_id ?? b?.id ?? 0);
        const childIds = safeArray(b?.child_ids ?? b?.children_ids ?? b?.submenu_ids ?? []);
        const childIdSet = new Set(childIds.map(x => Number(x)).filter(n => Number.isFinite(n)));
        return { title: '', kids: null, headerMenuId, childIdSet };
      })
      .filter(b => Number.isFinite(b.headerMenuId) && b.headerMenuId > 0);

    if (!normalized.length) return 0;

    normalized.forEach((b, idx) => {
      const menu = menuMap.get(b.headerMenuId) || null;

      // title priority:
      // 1) stored title (tree)
      // 2) section2_title_override (if ONLY one block selected)
      // 3) menu.title from header menus endpoint
      // 4) fallback
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

        const href = childHref(ch);

        const a = document.createElement('a');
        a.className = 'ft-block-item';
        a.textContent = label;
        a.href = href;

        if (/^https?:\/\//i.test(href) && !href.startsWith(window.location.origin)){
          a.target = '_blank';
          a.rel = 'noopener';
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

  // ✅ uses icon class from API (x.icon) and creates boxes dynamically
  function renderSocial(container, socials){
    if (!container) return 0;
    removeSkel(container);

    // IMPORTANT: remove any static boxes in HTML
    container.innerHTML = '';

    const list = Array.isArray(socials) ? socials : [];
    const clean = list
      .map(x => {
        const url = (x?.url_full ?? x?.url ?? x?.link ?? x?.href ?? '').toString().trim();
        const platform = (x?.platform ?? x?.title ?? x?.label ?? '').toString().trim();
        const icon = (x?.icon ?? '').toString().trim(); // <-- from API
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
      // ✅ use API icon if provided; else fallback by platform
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

    // ✅ SECTION 2 (FIXED: resolve header menu title + children via header menus endpoint)
    const s2blocks = safeArray(
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

    // ✅ SOCIALS (FIXED: use API icon class, boxes dynamic)
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
        a.href = normalizeUrl(it.url);
        const isExternal = /^https?:\/\//i.test(a.href) && !a.href.startsWith(window.location.origin);
        if (isExternal){ a.target = '_blank'; a.rel = 'noopener'; }
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

    // ✅ dev hints (won’t break anything)
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
