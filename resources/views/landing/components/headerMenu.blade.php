<!-- views/modules/header/header.blade.php -->

<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    .dynamic-navbar, .dynamic-navbar * { box-sizing:border-box; }

    :root{
        --menu-max-w: 1280px;
        --menu-gutter: clamp(10px, 1.4vw, 22px);

        /* ✅ root parents per column */
        --root-per-col: 5;
    }

    /* Navbar Container */
    .dynamic-navbar{background: var(--primary-color, #9E363A);box-shadow: 0 2px 4px rgba(0,0,0,0.1);position: sticky;top: 0;z-index: 1000;width: 100%;overflow: visible;padding-left: var(--menu-gutter);padding-right: var(--menu-gutter);}

    .dynamic-navbar .navbar-container {display:flex;align-items:stretch;justify-content:flex-start;width:100%;position:relative;overflow: visible;max-width: var(--menu-max-w);margin: 0 auto;}

    .dynamic-navbar .menu-row {flex: 1 1 auto;display:flex;justify-content:flex-start;align-items:stretch;min-width: 0;width: 100%;max-width: var(--menu-max-w);overflow-x: auto;overflow-y: hidden;-webkit-overflow-scrolling: touch;padding-right: 44px;scrollbar-width: none;-ms-overflow-style: none;}
    .menu-row::-webkit-scrollbar{ width:0; height:0; display:none; } /* Chrome/Safari */

    /* Scroll arrows (desktop only) */
    .menu-scroll-btn{position:absolute;top: 50%;transform: translateY(-50%);width: 34px;height: 34px;border-radius: 12px;border: 1px solid rgba(255,255,255,.22);background: rgba(255,255,255,.10);color:#fff;display:none;align-items:center;justify-content:center;cursor:pointer;z-index: 11000;box-shadow: 0 10px 22px rgba(0,0,0,.22);transition: transform .18s ease, background .18s ease, opacity .18s ease;user-select:none;backdrop-filter: blur(2px);}
    .menu-scroll-btn:hover{ transform: translateY(-50%) translateY(-1px); background: rgba(255,255,255,.14); }
    .menu-scroll-btn:active{ transform: translateY(-50%) translateY(0px); }
    .menu-scroll-btn:focus{outline:none;box-shadow: 0 0 0 3px rgba(201,75,80,.35), 0 10px 22px rgba(0,0,0,.22);}
    .menu-scroll-prev{ left: 6px; }
    .menu-scroll-next{ right: 6px; }
    .menu-scroll-fade-right{position:absolute;right: 0;top: 0;bottom: 0;width: 54px;pointer-events:none;background: linear-gradient(90deg, rgba(158,54,58,0.0), rgba(158,54,58,0.75));display:none;z-index: 10500;}
    .menu-scroll-fade-left{position:absolute;left: 0;top: 0;bottom: 0;width: 34px;pointer-events:none;background: linear-gradient(270deg, rgba(158,54,58,0.0), rgba(158,54,58,0.65));display:none;z-index: 10500;}

    /* Hamburger (mobile only) */
    .menu-toggle{display:none;align-items:center;justify-content:center;gap:.5rem;padding: .65rem .9rem;background: transparent;border: 0;color:#fff;cursor:pointer;user-select:none;transition: transform .25s ease, opacity .25s ease;flex: 0 0 auto;margin-left: 6px;}
    .menu-toggle:hover{ transform: translateY(-1px); opacity:.95; }
    .menu-toggle:focus{outline:none;box-shadow: 0 0 0 3px rgba(201,75,80,.35);border-radius: 12px;}
    .burger{width: 22px;height: 16px;position: relative;display: inline-block;}
    .burger::before, .burger::after, .burger span{content:"";position:absolute;left:0; right:0;height:2px;background:#fff;border-radius:2px;opacity:.95;transition: transform .25s ease, opacity .25s ease;}
    .burger::before{ top:0; }
    .burger span{ top:7px; }
    .burger::after{ bottom:0; }

    /* Menu List - single row */
    .dynamic-navbar .navbar-nav {display:flex;flex-direction:row;flex-wrap:nowrap;list-style:none;margin:0;padding:0;align-items:stretch;justify-content:flex-start;min-width:0;width: max-content;}
    .dynamic-navbar .nav-item {position: relative;margin:0;display:flex;flex: 0 0 auto;min-width: 0;}
    .dynamic-navbar .nav-link{display:flex;align-items:center;justify-content:center;color:#fff !important;font-weight:400 !important;font-size: 0.95rem !important;padding: 0.75rem 1.2rem;text-decoration:none;white-space: nowrap;border: none;background: transparent;cursor:pointer;width:100%;text-align:center;transition: background-color .25s ease, color .25s ease, transform .25s ease;}
    .navbar-nav.compact .nav-link{ font-size:.85rem; padding:.75rem .8rem; }
    .navbar-nav.very-compact .nav-link{ font-size:.8rem; padding:.75rem .55rem; }
    .navbar-nav.ultra-compact .nav-link{ font-size:.75rem; padding:.75rem .45rem; }
    .nav-link:hover,
    .nav-link.active{background-color: var(--secondary-color, #6B2528);color:#fff !important;}
    .dynamic-navbar .dropdown-menu{display:block;position:absolute;top: 100%;left: 0;background: transparent;padding: 0;margin: 0;z-index: 9999;overflow: visible;width: max-content;min-width: 0;max-width: min(var(--menu-max-w), calc(100vw - 20px));opacity: 0;visibility: hidden;transform: translateY(8px);pointer-events: none;transition: opacity .25s ease, transform .25s ease, visibility .25s ease;}
    .dynamic-navbar .dropdown-menu.show{opacity:1;visibility:visible;transform: translateY(0);pointer-events:auto;}

    @media (min-width: 992px){
        .nav-item.has-dropdown:hover > .dropdown-menu{opacity:1;visibility:visible;transform: translateY(0);pointer-events:auto;}
    }

    /* ✅ Full-width dropdown on hover */
    @media (min-width: 992px){
        .dynamic-navbar .dropdown-menu.dm-fullwidth{left: var(--menu-gutter) !important;right: var(--menu-gutter) !important;width: auto !important;max-width: calc(100vw - (var(--menu-gutter) * 2)) !important;}
        .dynamic-navbar .dropdown-menu.dm-fullwidth .mega-panel{width: 100%;max-width: 100% !important;}
    }

    .dynamic-navbar .mega-panel{display:flex;align-items:flex-start;gap: 0;background: var(--secondary-color, #6B2528);border: 1px solid rgba(255,255,255,0.12);border-top: 0;border-radius: 0 0 10px 10px;box-shadow: 0 12px 30px rgba(0,0,0,0.22);max-width: min(var(--menu-max-w), calc(100vw - 20px));overflow-x: auto;overflow-y: hidden;scrollbar-width: none;-ms-overflow-style: none;}
    .dynamic-navbar .mega-panel::-webkit-scrollbar{ width:0; height:0; display:none; }

    /* Root columns (5 parents per column) */
    .dynamic-navbar .mega-col{width: 270px;min-width: 270px;display:flex;flex-direction:column;padding: 8px;position: relative;align-self: flex-start;}
    .dynamic-navbar .mega-col:not([data-col="0"])::before{content:"";position:absolute;left:0;top:0;bottom:0;width:1px;background: rgba(255,255,255,0.14);}
    .dynamic-navbar .mega-list{list-style:none;margin:0;padding: 4px;max-height: calc(100vh - 180px);overflow:auto;scrollbar-width: none;-ms-overflow-style: none;}
    .dynamic-navbar .mega-list::-webkit-scrollbar{ width:0; height:0; display:none; }
    .dynamic-navbar .dropdown-item{display:flex;align-items:center;justify-content:space-between;gap: 10px;padding: .62rem .95rem;color:#fff !important;font-weight: 400;font-size: .93rem;text-decoration:none;white-space: nowrap;border: 0;background: transparent;cursor:pointer;width:100%;text-align:left;border-radius: 10px;outline: 1px solid rgba(255,255,255,0.00);transition: background-color .25s ease, transform .25s ease, outline-color .25s ease;will-change: transform;}
    .dynamic-navbar .dropdown-item:hover{background: rgba(255,255,255,0.10);outline-color: rgba(255,255,255,0.10);transform: translateX(2px);}
    .dynamic-navbar .dropdown-item.is-active{background: rgba(255,255,255,0.13);outline: 1px solid rgba(255,255,255,0.16);position: relative;}
    .dynamic-navbar .dropdown-item.is-active::before{content:"";position:absolute;left: 8px;top: 50%;transform: translateY(-50%);width: 3px;height: 18px;border-radius: 3px;background: #f1c40f;opacity: .95;}

    /* ✅ caret down because submenu opens BELOW */
    .dynamic-navbar .dropdown-item.has-children::after{content:'▾';font-size: .95rem;font-weight: 800;line-height: 1;color: rgba(255,255,255,0.95);margin-left: 10px;flex: 0 0 auto;transition: transform .22s ease, opacity .22s ease;transform: translateY(-1px);opacity: .92;}

    /* rotate caret when open */
    .dynamic-navbar li.mega-item.is-open > a.dropdown-item.has-children::after{transform: rotate(180deg) translateY(1px);opacity: 1;}
    .dynamic-navbar li.mega-item{position: relative;}
    .dynamic-navbar .mega-sub{list-style: none;margin: 6px 0 10px;padding: 6px 0 0 12px;border-left: 1px dashed rgba(255,255,255,0.25);display: none;}

    /* show submenu on open (JS adds .is-open) */
    .dynamic-navbar li.mega-item.is-open > .mega-sub{display: block;}

    /* nested item look */
    .dynamic-navbar .mega-sub .dropdown-item{padding: .52rem .85rem;font-size: .90rem;border-radius: 10px;white-space: normal;line-height: 1.15;opacity: .98;}
    .dynamic-navbar .mega-sub .dropdown-item:hover{transform: translateX(2px);}
    .mega-portal{position: fixed;inset: 0;pointer-events: none;z-index: 12000;}
    .mega-portal .dropdown-menu{ pointer-events: auto; }
    .dynamic-navbar .dropdown-menu.is-portaled{position: fixed !important;top: 0;left: 0;right: auto;}
    .dynamic-navbar.use-offcanvas .menu-row{ display:none; }
    .dynamic-navbar.use-offcanvas .menu-toggle{ display:flex; }

    @media (max-width: 991.98px){
        .menu-row{ display:none; }
        .menu-toggle{ display:flex; }
        .menu-scroll-btn, .menu-scroll-fade-right, .menu-scroll-fade-left{ display:none !important; }
    }

    .dynamic-offcanvas{--bs-offcanvas-width: 340px;background: var(--secondary-color, #6B2528);color:#fff;}
    .dynamic-offcanvas .offcanvas-header{border-bottom: 1px solid rgba(255,255,255,.15);padding: 14px 16px;}
    .dynamic-offcanvas .offcanvas-title{font-weight:700;letter-spacing:.2px;color:#fff;margin:0;}
    .dynamic-offcanvas .offcanvas-body{padding: 12px 10px 18px;}
    .offcanvas-menu{ list-style:none; margin:0; padding:0; }

    .oc-row{display:flex;align-items:center;gap: 8px;border-radius: 12px;padding: 8px 10px;transition: background .25s ease, transform .25s ease;will-change: transform;}
    .oc-row:hover{ background: rgba(255,255,255,.08); transform: translateX(1px); }

    .oc-link{flex: 1 1 auto;color: #fff !important;text-decoration:none;font-size: .95rem;line-height: 1.2;padding: 6px 8px;border-radius: 10px;white-space: normal;word-break: break-word;transition: background .25s ease, opacity .25s ease;}
    .oc-link.active{background: rgba(255,255,255,.14);box-shadow: inset 0 0 0 1px rgba(255,255,255,.18);}

    .oc-toggle{flex: 0 0 auto;width: 34px;height: 34px;border-radius: 10px;border: 1px solid rgba(255,255,255,.18);background: rgba(255,255,255,.08);color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;transition: transform .25s ease, background .25s ease, border-color .25s ease;}
    .oc-toggle:hover{ transform: translateY(-1px); background: rgba(255,255,255,.10); }
    .oc-toggle:focus{outline:none;box-shadow: 0 0 0 3px rgba(201,75,80,.35);}
    .oc-caret{width:0; height:0;border-left: 6px solid #fff;border-top: 5px solid transparent;border-bottom: 5px solid transparent;opacity: .9;transform: rotate(0deg);transition: transform .25s ease, opacity .25s ease;}
    .oc-toggle[aria-expanded="true"] .oc-caret{ transform: rotate(90deg); }
    .oc-sub{list-style:none;margin: 4px 0 6px;padding: 0 0 0 14px;border-left: 1px dashed rgba(255,255,255,.25);}

    .menu-loading-overlay{position: fixed;inset: 0;background: rgba(10, 10, 10, 0.35);backdrop-filter: blur(2px);display: none;align-items: center;justify-content: center;z-index: 20000;padding: 18px;}
    .menu-loading-overlay.show{ display: flex; }

    .menu-loading-card{background: var(--secondary-color, #6B2528);border: 1px solid rgba(255,255,255,.16);border-radius: 16px;box-shadow: 0 18px 50px rgba(0,0,0,.35);color: #fff;padding: 16px 18px;min-width: 260px;max-width: 92vw;display: flex;align-items: center;gap: 12px;}
    .menu-loading-text{display:flex;flex-direction:column;gap: 2px;line-height: 1.2;}
    .menu-loading-text strong{ font-size: 1rem; }
    .menu-loading-text small{ opacity: .85; font-size: .85rem; }

    /* ✅ Guard against Bootstrap overriding mega menu dropdown positioning */
    .dynamic-navbar .navbar-nav .dropdown-menu{position: absolute !important;inset: auto !important;}

    .dynamic-navbar .dropdown-menu.is-portaled{position: fixed !important;}

    /* Add to your existing CSS */

    /* Prevent Bootstrap from overriding portal positioning */
    .dynamic-navbar .dropdown-menu.is-portaled {position: fixed !important;z-index: 12001 !important; /* Higher than portal */margin-top: 0 !important;margin-left: 0 !important;}

    /* Ensure mega panel doesn't get Bootstrap's dropdown styles */
    .dynamic-navbar .mega-panel {background: var(--secondary-color, #6B2528) !important;border: 1px solid rgba(255,255,255,0.12) !important;border-radius: 0 0 10px 10px !important;padding: 0 !important;}

    /* Improve scroll arrow visibility */
    .menu-scroll-btn {z-index: 11001 !important; /* Above everything */}

    /* Ensure active states are visible */
    .nav-link.active {background-color: var(--secondary-color, #6B2528) !important;color: #fff !important;font-weight: 500 !important;}
</style>

<!-- LOADING OVERLAY -->
<div id="menuLoadingOverlay" class="menu-loading-overlay" aria-hidden="true">
    @include('partials.overlay')
</div>

<!-- Navbar HTML -->
<nav class="dynamic-navbar" id="dynamicNavbar">
    <div class="navbar-container">

        <!-- fades + arrows (desktop only) -->
        <div class="menu-scroll-fade-left" id="menuFadeLeft" aria-hidden="true"></div>
        <div class="menu-scroll-fade-right" id="menuFadeRight" aria-hidden="true"></div>

        <button class="menu-scroll-btn menu-scroll-prev" id="menuScrollPrev" type="button" aria-label="Scroll menu left">‹</button>
        <button class="menu-scroll-btn menu-scroll-next" id="menuScrollNext" type="button" aria-label="Scroll menu right">›</button>

        <div class="menu-row" id="menuRow">
            <ul class="navbar-nav" id="mainMenuContainer">
                <!-- Menu items will be loaded here -->
            </ul>
        </div>

        <!-- Hamburger (mobile) -->
        <button class="menu-toggle" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#menuOffcanvas"
                aria-controls="menuOffcanvas" aria-label="Open menu">
            <span class="burger"><span></span></span>
        </button>
    </div>

    <!-- Portal layer for mega dropdowns -->
    <div class="mega-portal" id="megaPortal" aria-hidden="true"></div>
</nav>

<!-- Offcanvas Sidebar -->
<div class="offcanvas offcanvas-start dynamic-offcanvas" tabindex="-1" id="menuOffcanvas" aria-labelledby="menuOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="menuOffcanvasLabel">Menu</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="offcanvas-menu" id="offcanvasMenuList">
            <!-- Sidebar menu will be rendered here -->
        </ul>
    </div>
</div>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    class DynamicMenu {
        constructor() {
            this.apiBase = '{{ url("/api/public/header-menus") }}';
            this.menuData = null;

            // Departments API (for department_id -> uuid mapping)
            this.apiDepartmentsPublic = '{{ url("/api/public/departments") }}';
            this.apiDepartments       = '{{ url("/api/departments") }}';
            this.deptUuidById = new Map();

            this.nodeById = new Map();
            this.childrenById = new Map();

            this.currentSlug = this.getCurrentSlug();
            this.activePathIds = [];
            this.activePathNodes = [];

            this.loadingEl = document.getElementById('menuLoadingOverlay');

            // portal meta
            this.portalMeta = new Map();
            this.portalBound = false;

            // scroller refs
            this.menuRowEl = null;
            this.btnNext = null;
            this.btnPrev = null;
            this.fadeRight = null;
            this.fadeLeft = null;

            // ✅ show only 5 root parents per column
            this.rootPerCol = 5;

            // ✅ prevents scroll arrows showing before menu is rendered
            this.menuReady = false;

            this.init();
        }

        init() {
            this.loadMenu();
            this.setupResizeListener();

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 992) this.forceCloseOffcanvas();
            });
        }

        showLoading(message = 'Loading menu…') {
            if (!this.loadingEl) return;
            const strong = this.loadingEl.querySelector('.menu-loading-text strong');
            if (strong) strong.textContent = message;
            this.loadingEl.classList.add('show');
            this.loadingEl.setAttribute('aria-hidden', 'false');
        }

        hideLoading() {
            if (!this.loadingEl) return;
            this.loadingEl.classList.remove('show');
            this.loadingEl.setAttribute('aria-hidden', 'true');
        }

        setupResizeListener() {
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    this.adjustMenuSizing();
                    this.toggleOverflowMode();
                    this.bindMegaGuards();
                    this.setupDesktopDropdownPortal();
                    this.repositionOpenPortaled();
                    this.setupMenuScroller();
                    this.bindWheelToHorizontalScroll();
                }, 150);
            });
        }

        getCurrentSlug() {
            const path = window.location.pathname || '';
            if (path === '/' || path === '') return '__HOME__';
            if (path.startsWith('/page/')) return path.replace('/page/', '').replace(/^\/+/, '');
            return '';
        }

        async fetchJson(url) {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
            const txt = await res.text();
            let data = null;
            try { data = txt ? JSON.parse(txt) : null; } catch(e) {}
            return { ok: res.ok, status: res.status, data };
        }

        normalizeDepartmentsPayload(payload) {
            let data = payload;
            if (data && typeof data === 'object' && data.success !== undefined) data = data.data;

            let items = [];
            if (Array.isArray(data)) items = data;
            else if (data && Array.isArray(data.items)) items = data.items;
            else if (data && Array.isArray(data.data)) items = data.data;

            return (items || []).filter(d => d && d.id !== undefined && d.id !== null);
        }

        buildDeptMap(depts) {
            this.deptUuidById.clear();
            (depts || []).forEach(d => {
                const id = Number(d.id);
                const uuid = (d.uuid ?? d.department_uuid ?? d.dept_uuid ?? '').toString().trim();
                if (id && uuid) this.deptUuidById.set(id, uuid);
            });
        }

        getItemDeptUuid(item) {
            const direct =
                (item?.department_uuid ?? item?.dept_uuid ?? '') ||
                (item?.department?.uuid ?? item?.department?.department_uuid ?? item?.department?.dept_uuid ?? '');
            const directTrim = (direct || '').toString().trim();
            if (directTrim) return directTrim;

            const did = item?.department_id;
            if (did !== undefined && did !== null) {
                const mapped = this.deptUuidById.get(Number(did));
                if (mapped) return mapped;
            }
            return '';
        }

        applyDepartmentUuid(url, deptUuid) {
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

        getRootHeaderMenuId(item) {
            if (!item || !item.id) return 0;

            let cur = item;
            let safety = 0;

            while (cur && cur.parent_id && safety < 20) {
                const p = this.nodeById.get(Number(cur.parent_id));
                if (!p) break;
                cur = p;
                safety++;
            }
            return Number(cur?.id || item.id || 0);
        }

        applyHeaderMenuId(url, headerMenuId) {
            headerMenuId = Number(headerMenuId || 0);
            if (!headerMenuId) return url;
            if (!url || url === '#') return url;

            let u;
            try {
                u = new URL(url, window.location.origin);
            } catch (e) {
                const sep = url.includes('?') ? '&' : '?';
                const hashIndex = url.indexOf('#');
                if (hashIndex !== -1) {
                    const base = url.slice(0, hashIndex);
                    const hash = url.slice(hashIndex);
                    return `${base}${sep}header_menu_id=${headerMenuId}${hash}`;
                }
                return `${url}${sep}header_menu_id=${headerMenuId}`;
            }

            if (u.origin !== window.location.origin) return url;

            const raw = (u.search || '').replace(/^\?/, '');
            const parts = raw ? raw.split('&').filter(Boolean) : [];

            const kept = parts.filter(p => {
                const key = (p.split('=')[0] || '').trim();
                if (!key) return false;
                if (key === 'header_menu_id') return false;
                return true;
            });

            const token = `header_menu_id=${headerMenuId}`;
            const newSearch = kept.length ? (`?${kept.join('&')}&${token}`) : (`?${token}`);

            return `${u.origin}${u.pathname}${newSearch}${u.hash || ''}`;
        }

hardNavigate(e, href, openNewTab = false) {
    if (!href || href === '#') return;

    // allow ctrl/cmd click new tabs
    if (e && (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button === 1)) return;

    e?.preventDefault?.();
    e?.stopPropagation?.();

    if (openNewTab) {
        window.open(href, '_blank');
        return;
    }

    try {
        const target = new URL(href, window.location.origin);
        const current = new URL(window.location.href);
        
        // Normalize URL parameters for comparison
        const normalizeSearch = (search) => {
            const params = new URLSearchParams(search);
            const sorted = Array.from(params.entries()).sort();
            return new URLSearchParams(sorted).toString();
        };
        
        const targetSearch = normalizeSearch(target.search);
        const currentSearch = normalizeSearch(current.search);
        
        // Same page, different params -> reload
        if (target.pathname === current.pathname && targetSearch !== currentSearch) {
            window.location.href = target.href;
            return;
        }
        
        // Different page or same everything -> navigate
        window.location.href = target.href;
    } catch (error) {
        // Fallback for malformed URLs
        window.location.href = href;
    }
}

/* =========================================================
   ✅ FINAL REDIRECTION RULE (YOUR LATEST)
   1) page_url (link)  ✅ FIRST
   2) page_slug
   3) slug (menu slug)
   4) shortcode ✅ ONLY IF NOTHING ELSE
   + fallback to first descendant using SAME priority
   ========================================================= */

getNodeLink(item){
    return (item?.page_url ?? item?.link ?? item?.url ?? item?.href ?? '').toString().trim();
}
getNodePageSlug(item){
    return (item?.page_slug ?? '').toString().trim();
}
getNodeMenuSlug(item){
    return (item?.slug ?? '').toString().trim();
}
getNodeShortcode(item){
    return (item?.shortcode ?? item?.page_shortcode ?? item?.short_code ?? '').toString().trim();
}

resolveLinkUrl(rawUrl){
    rawUrl = (rawUrl || '').toString().trim();
    if (!rawUrl) return '';
    try{
        return new URL(rawUrl, window.location.href).href;
    }catch(e){
        return rawUrl;
    }
}

isSameOrigin(url){
    try{
        return new URL(url, window.location.href).origin === window.location.origin;
    }catch(e){
        return false;
    }
}

isSpecialProtocol(url){
    return /^(mailto:|tel:|sms:|whatsapp:)/i.test((url || '').toString().trim());
}

/* ✅ Priority search (new order):
   link -> page_slug -> menu_slug -> shortcode
*/
findBestTarget(item){
    if (!item) return { type:'', value:'' };

    const link = this.getNodeLink(item);
    if (link) return { type:'link', value: link };

    const ps = this.getNodePageSlug(item);
    if (ps) return { type:'page_slug', value: ps };

    const ms = this.getNodeMenuSlug(item);
    if (ms) return { type:'menu_slug', value: ms };

    const sc = this.getNodeShortcode(item);
    if (sc) return { type:'shortcode', value: sc };

    // fallback: find first child that has any valid target (same priority)
    const kids = Array.isArray(item.children) ? item.children : [];
    if (!kids.length) return { type:'', value:'' };

    const sortedKids = [...kids].sort((a,b) => (a.position||0) - (b.position||0));
    for (const child of sortedKids){
        const found = this.findBestTarget(child);
        if (found && found.value) return found;
    }
    return { type:'', value:'' };
}

normalizeInternalPageUrl(identifier){
    identifier = (identifier || '').toString().trim();
    if (!identifier) return '#';
    return `{{ url('/page') }}/${encodeURIComponent(identifier)}`;
}

getMenuItemUrl(item){
    let url = '#';
    const target = this.findBestTarget(item);

    // ✅ 1) LINK FIRST preference (internal OR external)
    if (target.type === 'link'){
        url = this.resolveLinkUrl(target.value) || '#';

        // ✅ external/special => no mutation
        if (this.isSpecialProtocol(url)) return url;
        if (!this.isSameOrigin(url)) return url;
    }

    // ✅ 2) page_slug
    else if (target.type === 'page_slug'){
        url = this.normalizeInternalPageUrl(target.value);
    }

    // ✅ 3) menu slug
    else if (target.type === 'menu_slug'){
        url = this.normalizeInternalPageUrl(target.value);
    }

    // ✅ 4) shortcode ONLY IF NOTHING ELSE
    else if (target.type === 'shortcode'){
        url = this.normalizeInternalPageUrl(target.value);
    }

    if (!url || url === '#') return '#';

    // ✅ Only mutate same-origin URLs with dept/header_menu_id
    if (this.isSameOrigin(url)) {
        const deptUuid = this.getItemDeptUuid(item);
        url = this.applyDepartmentUuid(url, deptUuid);

        // ✅ CHANGE: send CLICKED item's id (not root parent id)
        const clickedHeaderId = Number(item?.id || 0);
        url = this.applyHeaderMenuId(url, clickedHeaderId);
    }

    return url;
}


        async loadMenu() {
            this.showLoading('Loading menu…');

            // ✅ hide arrows until menu renders
            this.menuReady = false;

            try {
                const [menuRes, deptPublicRes] = await Promise.all([
                    this.fetchJson(`${this.apiBase}/tree`),
                    this.fetchJson(this.apiDepartmentsPublic),
                ]);

                if (!menuRes.ok) throw new Error(`HTTP error! status: ${menuRes.status}`);

                let deptList = [];
                if (deptPublicRes.ok) {
                    deptList = this.normalizeDepartmentsPayload(deptPublicRes.data);
                } else {
                    const deptRes = await this.fetchJson(this.apiDepartments);
                    if (deptRes.ok) deptList = this.normalizeDepartmentsPayload(deptRes.data);
                }
                this.buildDeptMap(deptList);

                const data = menuRes.data;

                if (data && data.success && data.data) {
                    this.menuData = data.data;

                    this.buildNodeMaps(this.menuData);

                    this.activePathNodes = (this.currentSlug && this.currentSlug !== '__HOME__')
                        ? this.getActivePathNodes(this.menuData, this.currentSlug)
                        : [];
                    this.activePathIds = this.activePathNodes.map(n => n.id);

                    this.renderMenu();
                    this.renderOffcanvasMenu();

                    // ✅ now menu is rendered
                    this.menuReady = true;

                    setTimeout(() => {
                        this.resetMenuRowStart();
                        this.adjustMenuSizing();
                        this.toggleOverflowMode();
                        this.bindMegaGuards();
                        this.setupDesktopDropdownPortal();
                        this.setupMenuScroller();
                        this.bindWheelToHorizontalScroll();
                        this.highlightActiveMenu();
                    }, 50);
                } else {
                    this.showError();
                }
            } catch (error) {
                console.error('Error loading menu:', error);
                this.showError();
            } finally {
                this.hideLoading();
            }
        }

        buildNodeMaps(items) {
            this.nodeById.clear();
            this.childrenById.clear();

            const walk = (nodes) => {
                for (const n of nodes || []) {
                    this.nodeById.set(n.id, n);
                    this.childrenById.set(n.id, (n.children && n.children.length) ? n.children : []);
                    if (n.children && n.children.length) walk(n.children);
                }
            };
            walk(items || []);
        }

        getActivePathNodes(items, slug) {
            if (!slug || !items) return [];

            const dfs = (nodes, target) => {
                for (const n of nodes) {
                    const keys = [
  (n.page_slug ?? ''),
  (n.slug ?? ''),
  (n.shortcode ?? n.page_shortcode ?? n.short_code ?? '')
].map(x => (x || '').toString().trim()).filter(Boolean);

if (keys.includes(target)) return [n];


                    if (n.children && n.children.length) {
                        const res = dfs(n.children, target);
                        if (res.length) return [n, ...res];
                    }
                }
                return [];
            };
            return dfs(items, slug);
        }

        adjustMenuSizing() {
            if (window.innerWidth < 992) return;

            const container = document.getElementById('mainMenuContainer');
            const row = document.getElementById('menuRow');
            if (!container || !row) return;

            const navItems = container.querySelectorAll(':scope > .nav-item');
            const itemCount = navItems.length;
            if (!itemCount) return;

            container.classList.remove('compact', 'very-compact', 'ultra-compact');

            const rowWidth = row.offsetWidth || row.clientWidth || 0;
            const estimatedItemWidth = rowWidth / itemCount;

            if (estimatedItemWidth < 90) container.classList.add('ultra-compact');
            else if (estimatedItemWidth < 110) container.classList.add('very-compact');
            else if (estimatedItemWidth < 140) container.classList.add('compact');
        }

        toggleOverflowMode() {
            const nav = document.getElementById('dynamicNavbar');
            if (!nav) return;

            const isMobile = window.innerWidth < 992;
            nav.classList.toggle('use-offcanvas', isMobile);

            if (!isMobile) this.forceCloseOffcanvas();
        }

        forceCloseOffcanvas() {
            const ocEl = document.getElementById('menuOffcanvas');
            if (!ocEl) return;

            const inst = bootstrap.Offcanvas.getInstance(ocEl) || new bootstrap.Offcanvas(ocEl);
            try { inst.hide(); } catch(e){}

            document.querySelectorAll('.offcanvas-backdrop').forEach(b => b.remove());
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            ocEl.classList.remove('show');
            ocEl.removeAttribute('style');
            ocEl.setAttribute('aria-hidden', 'true');
        }

        resetMenuRowStart() {
            const row = document.getElementById('menuRow');
            if (!row) return;
            row.scrollLeft = 0;
        }

        buildHomeNavItem() {
            const li = document.createElement('li');
            li.className = 'nav-item';
            li.dataset.id = 'home_static';
            li.dataset.slug = '__HOME__';

            const a = document.createElement('a');
            a.className = 'nav-link';
            a.href = '{{ url("/") }}';
            a.innerHTML = `Home`;

            if (this.currentSlug === '__HOME__') a.classList.add('active');

            // ✅ hard navigate (keeps slug correct always)
            a.addEventListener('click', (e) => this.hardNavigate(e, a.href, false));

            li.appendChild(a);
            return li;
        }

        buildHomeOffcanvasItem() {
            const li = document.createElement('li');

            const row = document.createElement('div');
            row.className = 'oc-row';

            const link = document.createElement('a');
            link.className = 'oc-link';
            link.href = '{{ url("/") }}';
            link.innerHTML = `<i class="fa-solid fa-house me-2"></i>Home`;

            if (this.currentSlug === '__HOME__') link.classList.add('active');

            link.addEventListener('click', (e) => {
                this.forceCloseOffcanvas();
                this.hardNavigate(e, link.href, false);
            });

            row.appendChild(link);
            li.appendChild(row);

            return li;
        }

        renderMenu() {
            const container = document.getElementById('mainMenuContainer');
            container.innerHTML = '';

            container.appendChild(this.buildHomeNavItem());

            if (!this.menuData || !this.menuData.length) return;

            const sortedItems = [...this.menuData].sort((a,b) => (a.position||0) - (b.position||0));

            sortedItems.forEach(item => {
                const li = document.createElement('li');
                const hasChildren = item.children && item.children.length > 0;
                li.className = `nav-item ${hasChildren ? 'has-dropdown' : ''}`;
                li.dataset.id = item.id;
                li.dataset.slug = (item.slug || item.page_slug || '');

                const a = document.createElement('a');
                a.className = 'nav-link';
                a.href = this.getMenuItemUrl(item);
                a.textContent = item.title;

                // external link
                if (item.page_url && item.page_url.startsWith('http')) {
                    a.addEventListener('click', (e) => this.hardNavigate(e, a.href, true));
                } else {
                    // ✅ internal hard navigate (fix slug staying same)
                    a.addEventListener('click', (e) => this.hardNavigate(e, a.href, false));
                }

                li.appendChild(a);

                if (hasChildren) {
                    const activeSlice = (this.activePathNodes.length && this.activePathNodes[0].id === item.id)
                        ? this.activePathNodes.slice(1)
                        : [];
                    this.addMegaMenu(li, item.children, activeSlice);
                }

                container.appendChild(li);
            });

            this.resetMenuRowStart();
        }

        /* =========================================================
           ✅ NEW MEGA MENU RENDERING:
           - Root children are split into columns of 5 items each
           - On hover, children open BELOW their parent (nested list)
           ========================================================= */

        chunkArray(arr, size) {
            const out = [];
            for (let i = 0; i < arr.length; i += size) out.push(arr.slice(i, i + size));
            return out;
        }

        buildMegaItem(item, level = 0) {
            const li = document.createElement('li');
            li.className = 'mega-item';
            li.dataset.id = item.id;
            li.dataset.slug = (item.slug || item.page_slug || '');

            const a = document.createElement('a');
            a.className = 'dropdown-item';
            a.href = this.getMenuItemUrl(item);
            a.textContent = item.title;

            const hasChildren = item.children && item.children.length > 0;
            if (hasChildren) a.classList.add('has-children');

            // external link
            if (item.page_url && item.page_url.startsWith('http')) {
                a.addEventListener('click', (e) => this.hardNavigate(e, a.href, true));
            } else {
                // ✅ internal hard navigate (fix slug staying same)
                a.addEventListener('click', (e) => this.hardNavigate(e, a.href, false));
            }

            li.appendChild(a);

            // active highlight (leaf)
            if (this.currentSlug && li.dataset.slug && (li.dataset.slug === this.currentSlug)) {
                a.classList.add('is-active');
            }

            // nested submenu (below parent)
            if (hasChildren) {
                const ul = document.createElement('ul');
                ul.className = 'mega-sub';

                const kidsSorted = [...item.children].sort((x,y) => (x.position||0) - (y.position||0));
                kidsSorted.forEach(child => {
                    ul.appendChild(this.buildMegaItem(child, level + 1));
                });

                li.appendChild(ul);
            }

            return li;
        }

        renderRootMegaColumns(panel, rootChildren) {
            panel.innerHTML = '';

            const sorted = [...(rootChildren || [])].sort((a,b) => (a.position||0) - (b.position||0));

            // ✅ chunk into columns of 5
            const chunks = this.chunkArray(sorted, this.rootPerCol);

            chunks.forEach((group, idx) => {
                const col = document.createElement('div');
                col.className = 'mega-col';
                col.dataset.col = String(idx);

                const ul = document.createElement('ul');
                ul.className = 'mega-list';

                group.forEach(item => {
                    ul.appendChild(this.buildMegaItem(item, 0));
                });

                col.appendChild(ul);
                panel.appendChild(col);
            });
        }

        closeSiblingsAtSameLevel(liEl) {
            if (!liEl) return;
            const parent = liEl.parentElement;
            if (!parent) return;

            parent.querySelectorAll(':scope > li.mega-item.is-open').forEach(sib => {
                if (sib !== liEl) sib.classList.remove('is-open');
            });
        }

        bindNestedHover(panel) {
            if (!panel || panel.dataset.nestedBound === '1') return;
            panel.dataset.nestedBound = '1';

            // open submenu on hover (below parent)
            panel.addEventListener('mouseover', (e) => {
                if (window.innerWidth < 992) return;

                const a = e.target.closest('a.dropdown-item.has-children');
                if (!a) return;

                const li = a.closest('li.mega-item');
                if (!li) return;

                this.closeSiblingsAtSameLevel(li);
                li.classList.add('is-open');
            });

            // close submenu when leaving the item
            panel.addEventListener('mouseout', (e) => {
                if (window.innerWidth < 992) return;

                const li = e.target.closest('li.mega-item');
                if (!li) return;

                const to = e.relatedTarget;
                if (to && li.contains(to)) return;

                const id = Number(li.dataset.id || 0);
                if (id && this.activePathIds.includes(id)) return;

                li.classList.remove('is-open');
            });
        }

        expandActivePath(panel, activeNodesFromHere = []) {
            if (!panel || !activeNodesFromHere || !activeNodesFromHere.length) return;

            activeNodesFromHere.forEach(n => {
                const li = panel.querySelector(`li.mega-item[data-id="${n.id}"]`);
                if (!li) return;

                li.classList.add('is-open');

                const a = li.querySelector(':scope > a.dropdown-item');
                if (a) a.classList.add('is-active');
            });
        }

        addMegaMenu(parentLi, children, activeNodesFromHere = []) {
            const dropdown = document.createElement('div');
            dropdown.className = 'dropdown-menu';

            const panel = document.createElement('div');
            panel.className = 'mega-panel';
            dropdown.appendChild(panel);

            this.renderRootMegaColumns(panel, children);
            this.expandActivePath(panel, activeNodesFromHere);
            this.bindNestedHover(panel);

            parentLi.appendChild(dropdown);
        }

        bindMegaGuards() {
            if (window.innerWidth < 992) return;

            const root = document.getElementById('mainMenuContainer');
            if (!root) return;

            root.querySelectorAll(':scope > .nav-item.has-dropdown').forEach(li => {
                li.addEventListener('mouseenter', () => {
                    requestAnimationFrame(() => this.guardMega(li));
                });
            });
        }

        guardMega(li) {
            const menu = li.querySelector(':scope > .dropdown-menu');
            if (!menu) return;
            menu.style.left = '0';
            menu.style.right = 'auto';
        }

        /* Desktop portal for dropdown */
        ensurePortal() { return document.getElementById('megaPortal'); }

        setupDesktopDropdownPortal() {
            if (window.innerWidth < 992) {
                this.restoreAllPortaled();
                return;
            }

            const portal = this.ensurePortal();
            const root = document.getElementById('mainMenuContainer');
            const row = document.getElementById('menuRow');
            if (!portal || !root) return;

            if (!this.portalBound) {
                this.portalBound = true;
                window.addEventListener('scroll', () => this.repositionOpenPortaled(), { passive: true });
                if (row) row.addEventListener('scroll', () => this.repositionOpenPortaled(), { passive: true });
            }

            root.querySelectorAll(':scope > .nav-item.has-dropdown').forEach(li => {
                if (li.dataset.portalBound === '1') return;
                li.dataset.portalBound = '1';

                const dropdown = li.querySelector(':scope > .dropdown-menu');
                if (!dropdown) return;

                let closeTimer = null;

                const open = () => {
                    clearTimeout(closeTimer);
                    this.portalizeDropdown(li, dropdown);
                };

                const scheduleClose = () => {
                    clearTimeout(closeTimer);
                    closeTimer = setTimeout(() => {
                        this.unportalizeDropdown(dropdown);
                    }, 140);
                };

                li.addEventListener('mouseenter', open);
                li.addEventListener('mouseleave', scheduleClose);

                dropdown.addEventListener('mouseenter', () => clearTimeout(closeTimer));
                dropdown.addEventListener('mouseleave', scheduleClose);
            });

            this.bindFullWidthHoverJquery();
        }

        portalizeDropdown(anchorLi, dropdown) {
            const portal = this.ensurePortal();
            if (!portal) return;

            this.portalMeta.forEach((meta, dm) => {
                if (dm !== dropdown && dm.classList.contains('is-portaled') && dm.classList.contains('show')) {
                    this.unportalizeDropdown(dm);
                }
            });

            if (!this.portalMeta.has(dropdown)) {
                const ph = document.createElement('span');
                ph.className = 'dropdown-placeholder';
                ph.style.display = 'none';
                anchorLi.appendChild(ph);
                this.portalMeta.set(dropdown, { anchor: anchorLi, placeholder: ph });
            } else {
                this.portalMeta.get(dropdown).anchor = anchorLi;
            }

            if (dropdown.parentElement !== portal) portal.appendChild(dropdown);

            dropdown.classList.add('is-portaled', 'show', 'dm-fullwidth');
            requestAnimationFrame(() => this.positionPortaledDropdown(anchorLi, dropdown));
        }

        unportalizeDropdown(dropdown) {
            const meta = this.portalMeta.get(dropdown);
            if (!meta || !meta.anchor || !meta.placeholder) return;

            dropdown.classList.remove('show', 'is-portaled', 'dm-fullwidth');
            dropdown.style.removeProperty('top');
            dropdown.style.removeProperty('left');
            dropdown.style.removeProperty('right');
            dropdown.style.removeProperty('width');
            dropdown.style.removeProperty('max-width');

            try { meta.anchor.insertBefore(dropdown, meta.placeholder); }
            catch(e){ meta.anchor.appendChild(dropdown); }
        }

        restoreAllPortaled() {
            this.portalMeta.forEach((meta, dm) => {
                if (dm && dm.classList.contains('is-portaled')) this.unportalizeDropdown(dm);
            });
        }

        repositionOpenPortaled() {
            if (window.innerWidth < 992) return;

            this.portalMeta.forEach((meta, dm) => {
                if (!dm || !meta || !meta.anchor) return;
                if (dm.classList.contains('is-portaled') && dm.classList.contains('show')) {
                    this.positionPortaledDropdown(meta.anchor, dm);
                }
            });
        }

        positionPortaledDropdown(anchorLi, dropdown) {
            const nav = document.getElementById('dynamicNavbar');
            if (!nav || !anchorLi || !dropdown) return;

            const navRect = nav.getBoundingClientRect();
            const gutter = this.getMenuGutterPx();
            const pad = Math.max(8, gutter);

            dropdown.style.top = `${Math.round(navRect.bottom)}px`;
            dropdown.style.left = `${pad}px`;
            dropdown.style.right = `${pad}px`;
            dropdown.style.width = 'auto';
            dropdown.style.maxWidth = `calc(100vw - ${(pad * 2)}px)`;
        }

        getMenuGutterPx() {
            try {
                const v = getComputedStyle(document.documentElement).getPropertyValue('--menu-gutter') || '';
                const n = parseFloat(v);
                return Number.isFinite(n) ? n : 14;
            } catch (e) {
                return 14;
            }
        }

        setupMenuScroller() {
            if (window.innerWidth < 992) return;

            this.menuRowEl = document.getElementById('menuRow');
            this.btnNext = document.getElementById('menuScrollNext');
            this.btnPrev = document.getElementById('menuScrollPrev');
            this.fadeRight = document.getElementById('menuFadeRight');
            this.fadeLeft = document.getElementById('menuFadeLeft');

            if (!this.menuRowEl || !this.btnNext || !this.btnPrev) return;

            const hideAll = () => {
                this.btnNext.style.display = 'none';
                this.btnPrev.style.display = 'none';
                if (this.fadeRight) this.fadeRight.style.display = 'none';
                if (this.fadeLeft)  this.fadeLeft.style.display  = 'none';
            };

            const update = () => {
                // ✅ do not show scroll UI until menu is rendered
                if (!this.menuReady) {
                    hideAll();
                    return;
                }

                const row = this.menuRowEl;

                const maxScroll = Math.max(0, (row.scrollWidth || 0) - (row.clientWidth || 0));
                const hasOverflow = maxScroll > 2;

                if (!hasOverflow) {
                    hideAll();
                    return;
                }

                const atStart = (row.scrollLeft || 0) <= 1;
                const atEnd = (row.scrollLeft || 0) >= (maxScroll - 1);

                this.btnNext.style.display = (!atEnd) ? 'flex' : 'none';
                this.btnPrev.style.display = (!atStart) ? 'flex' : 'none';

                if (this.fadeRight) this.fadeRight.style.display = (!atEnd) ? 'block' : 'none';
                if (this.fadeLeft)  this.fadeLeft.style.display  = (!atStart) ? 'block' : 'none';
            };

            if (!this.menuRowEl.dataset.scrollerBound) {
                this.menuRowEl.dataset.scrollerBound = '1';

                this.menuRowEl.addEventListener('scroll', () => requestAnimationFrame(update), { passive: true });

                this.btnNext.addEventListener('click', () => {
                    const row = this.menuRowEl;
                    const step = Math.max(240, Math.floor(row.clientWidth * 0.65));
                    row.scrollBy({ left: step, behavior: 'smooth' });
                });

                this.btnPrev.addEventListener('click', () => {
                    const row = this.menuRowEl;
                    const step = Math.max(240, Math.floor(row.clientWidth * 0.65));
                    row.scrollBy({ left: -step, behavior: 'smooth' });
                });

                window.addEventListener('resize', () => requestAnimationFrame(update), { passive: true });
            }

            update();
        }

        bindWheelToHorizontalScroll() {
            if (window.innerWidth < 992) return;

            const row = document.getElementById('menuRow');
            if (!row || row.dataset.wheelBound === '1') return;
            row.dataset.wheelBound = '1';

            $('#menuRow').on('wheel', function(e){
                const oe = e.originalEvent || e;
                const el = this;

                const maxScroll = (el.scrollWidth || 0) - (el.clientWidth || 0);
                if (maxScroll <= 2) return;

                if (!oe.shiftKey && Math.abs(oe.deltaY) > 0) {
                    el.scrollLeft += oe.deltaY;
                    e.preventDefault();
                }
            });
        }

        bindFullWidthHoverJquery() {
            if (window.innerWidth < 992) return;

            const self = this;
            const $root = $('#mainMenuContainer');

            $root.off('mouseenter.dmfull mouseleave.dmfull', '> .nav-item.has-dropdown');

            $root.on('mouseenter.dmfull', '> .nav-item.has-dropdown', function(){
                const dm = this.querySelector(':scope > .dropdown-menu');
                if (!dm) return;
                dm.classList.add('dm-fullwidth');
                if (dm.classList.contains('is-portaled') && dm.classList.contains('show')) {
                    self.positionPortaledDropdown(this, dm);
                }
            });

            $root.on('mouseleave.dmfull', '> .nav-item.has-dropdown', function(){
                const dm = this.querySelector(':scope > .dropdown-menu');
                if (!dm) return;
                dm.classList.add('dm-fullwidth');
            });
        }

        highlightActiveMenu() {
            document.querySelectorAll('.nav-link.active, .dropdown-item.active')
                .forEach(link => link.classList.remove('active'));

            if (this.currentSlug === '__HOME__') {
                const homeLink = document.querySelector(`.nav-item[data-slug="__HOME__"] > a.nav-link`);
                if (homeLink) homeLink.classList.add('active');
                return;
            }

            if (this.activePathNodes && this.activePathNodes.length) {
                const top = this.activePathNodes[0];
                if (top) {
                    const topLink = document.querySelector(`[data-id="${top.id}"] > a.nav-link`);
                    if (topLink) topLink.classList.add('active');
                }
            }
        }

        showError() {
            const container = document.getElementById('mainMenuContainer');
            if (container) container.innerHTML = '';
            const off = document.getElementById('offcanvasMenuList');
            if (off) off.innerHTML = '';
        }

        renderOffcanvasMenu() {
            const root = document.getElementById('offcanvasMenuList');
            if (!root) return;

            root.innerHTML = '';
            root.appendChild(this.buildHomeOffcanvasItem());

            if (!this.menuData || this.menuData.length === 0) return;

            const sortedItems = [...this.menuData].sort((a,b) => (a.position||0) - (b.position||0));
            sortedItems.forEach(item => root.appendChild(this.createOffcanvasItem(item, 0)));
        }

        createOffcanvasItem(item, level) {
            const li = document.createElement('li');
            const hasChildren = item.children && item.children.length > 0;

            const row = document.createElement('div');
            row.className = 'oc-row';
            row.style.paddingLeft = `${Math.min(level, 7) * 12 + 10}px`;

            const link = document.createElement('a');
            link.className = 'oc-link';
            link.href = this.getMenuItemUrl(item);
            link.textContent = item.title;

            const slug = (item.slug || item.page_slug || '');
            if (this.currentSlug && slug && (slug === this.currentSlug)) link.classList.add('active');

            // external
            if (item.page_url && item.page_url.startsWith('http')) {
                link.addEventListener('click', (e) => {
                    this.forceCloseOffcanvas();
                    this.hardNavigate(e, link.href, true);
                });
            } else {
                link.addEventListener('click', (e) => {
                    this.forceCloseOffcanvas();
                    this.hardNavigate(e, link.href, false);
                });
            }

            row.appendChild(link);

            if (!hasChildren) {
                li.appendChild(row);
                return li;
            }

            const collapseId = `oc_collapse_${item.id}`;
            const shouldExpand = this.activePathIds.includes(item.id);

            const toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'oc-toggle';
            toggle.setAttribute('data-bs-toggle', 'collapse');
            toggle.setAttribute('data-bs-target', `#${collapseId}`);
            toggle.setAttribute('aria-controls', collapseId);
            toggle.setAttribute('aria-expanded', shouldExpand ? 'true' : 'false');

            const caret = document.createElement('span');
            caret.className = 'oc-caret';
            toggle.appendChild(caret);

            row.appendChild(toggle);
            li.appendChild(row);

            const collapse = document.createElement('div');
            collapse.className = `collapse ${shouldExpand ? 'show' : ''}`;
            collapse.id = collapseId;

            const ul = document.createElement('ul');
            ul.className = 'oc-sub';

            const sortedKids = [...item.children].sort((a,b) => (a.position||0) - (b.position||0));
            sortedKids.forEach(child => ul.appendChild(this.createOffcanvasItem(child, level + 1)));

            collapse.appendChild(ul);
            li.appendChild(collapse);

            return li;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        window.dynamicMenu = new DynamicMenu();
    });
</script>
