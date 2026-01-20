{{-- views/landing/components/topHeaderMenu.blade.php --}}

{{-- Bootstrap 5 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
{{-- FontAwesome (icons for contacts) --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

<style>
    /* =========================================================
       ✅ FIXED (requested)
       1) 125% zoom: keep LEFT/RIGHT gap (side gutter always)
       2) Last dropdown never hidden: full-width mega dropdown (desktop)
       3) Mouse wheel scroll horizontal when menus overflow (desktop)
       4) When page is visited: active menu should be highlighted
       ========================================================= */

    :root{
        --menu-max-w: 1280px; /* ✅ Hard cap (requested) */
        --menu-gutter: clamp(10px, 1.4vw, 22px); /* ✅ side gap for zoom */
    }

    /* Navbar Container */
    #thmNavbar{
        background: var(--primary-color, #9E363A);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
        width: 100%;
        overflow: visible;

        /* ✅ keep left/right gap always (zoom safe) */
        padding-left: var(--menu-gutter);
        padding-right: var(--menu-gutter);
    }

    #thmNavbar, #thmNavbar *{ box-sizing:border-box; }

    #thmNavbar .navbar-container{
        display:flex;
        align-items:stretch;
        justify-content:flex-start;
        width:100%;
        position:relative;
        overflow: visible;

        max-width: var(--menu-max-w);
        margin: 0 auto;
    }

    #thmNavbar .menu-row{
        flex: 1 1 auto;
        display:flex;
        justify-content:flex-start;
        align-items:stretch;
        min-width: 0;

        width: 100%;
        max-width: var(--menu-max-w);
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;

        padding-right: 44px;

        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,.25) rgba(0,0,0,.12);
    }

    #thmNavbar .menu-row::-webkit-scrollbar{ height: 3px; }
    #thmNavbar .menu-row::-webkit-scrollbar-thumb{
        background: rgba(255,255,255,.25);
        border-radius: 10px;
    }
    #thmNavbar .menu-row::-webkit-scrollbar-track{
        background: rgba(0,0,0,.12);
        border-radius: 10px;
    }

    /* Scroll arrows (desktop only) */
    #thmNavbar .menu-scroll-btn{
        position:absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 34px;
        height: 34px;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,.22);
        background: rgba(255,255,255,.10);
        color:#fff;
        display:none;
        align-items:center;
        justify-content:center;
        cursor:pointer;
        z-index: 11000;
        box-shadow: 0 10px 22px rgba(0,0,0,.22);
        transition: transform .18s ease, background .18s ease, opacity .18s ease;
        user-select:none;
        backdrop-filter: blur(2px);
    }
    #thmNavbar .menu-scroll-btn:hover{ transform: translateY(-50%) translateY(-1px); background: rgba(255,255,255,.14); }
    #thmNavbar .menu-scroll-btn:active{ transform: translateY(-50%) translateY(0); }
    #thmNavbar .menu-scroll-btn:focus{
        outline:none;
        box-shadow: 0 0 0 3px rgba(201,75,80,.35), 0 10px 22px rgba(0,0,0,.22);
    }

    #thmNavbar .menu-scroll-prev{ left: 6px; }
    #thmNavbar .menu-scroll-next{ right: 6px; }

    #thmNavbar .menu-scroll-fade-right{
        position:absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 54px;
        pointer-events:none;
        background: linear-gradient(90deg, rgba(158,54,58,0.0), rgba(158,54,58,0.75));
        display:none;
        z-index: 10500;
    }

    #thmNavbar .menu-scroll-fade-left{
        position:absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 34px;
        pointer-events:none;
        background: linear-gradient(270deg, rgba(158,54,58,0.0), rgba(158,54,58,0.65));
        display:none;
        z-index: 10500;
    }

    /* Hamburger (mobile only) */
    #thmNavbar .menu-toggle{
        display:none;
        align-items:center;
        justify-content:center;
        gap:.5rem;
        padding: .65rem .9rem;
        background: transparent;
        border: 0;
        color:#fff;
        cursor:pointer;
        user-select:none;
        transition: transform .25s ease, opacity .25s ease;
        flex: 0 0 auto;
        margin-left: 6px;
    }
    #thmNavbar .menu-toggle:hover{ transform: translateY(-1px); opacity:.95; }
    #thmNavbar .menu-toggle:focus{
        outline:none;
        box-shadow: 0 0 0 3px rgba(201,75,80,.35);
        border-radius: 12px;
    }
    #thmNavbar .burger{
        width: 22px;
        height: 16px;
        position: relative;
        display: inline-block;
    }
    #thmNavbar .burger::before,
    #thmNavbar .burger::after,
    #thmNavbar .burger span{
        content:"";
        position:absolute;
        left:0; right:0;
        height:2px;
        background:#fff;
        border-radius:2px;
        opacity:.95;
        transition: transform .25s ease, opacity .25s ease;
    }
    #thmNavbar .burger::before{ top:0; }
    #thmNavbar .burger span{ top:7px; }
    #thmNavbar .burger::after{ bottom:0; }

    /* Menu List - single row */
    #thmNavbar .navbar-nav{
        display:flex;
        flex-direction:row;
        flex-wrap:nowrap;
        list-style:none;
        margin:0;
        padding:0;
        align-items:stretch;
        justify-content:flex-start;
        min-width:0;
        width: max-content;
    }

    #thmNavbar .nav-item{
        position: relative;
        margin:0;
        display:flex;
        flex: 0 0 auto;
        min-width: 0;
    }

    #thmNavbar .nav-link{
        display:flex;
        align-items:center;
        justify-content:center;
        color:#fff !important;
        font-weight:400 !important;
        font-size: 0.95rem !important;
        padding: 0.75rem 1.2rem;
        text-decoration:none;
        white-space: nowrap;
        border: none;
        background: transparent;
        cursor:pointer;
        width:100%;
        text-align:center;
        transition: background-color .25s ease, color .25s ease, transform .25s ease;
    }

    #thmNavbar .navbar-nav.compact .nav-link{ font-size:.85rem; padding:.75rem .8rem; }
    #thmNavbar .navbar-nav.very-compact .nav-link{ font-size:.8rem; padding:.75rem .55rem; }
    #thmNavbar .navbar-nav.ultra-compact .nav-link{ font-size:.75rem; padding:.75rem .45rem; }

    #thmNavbar .nav-link:hover,
    #thmNavbar .nav-link.active{
        background-color: var(--secondary-color, #6B2528);
        color:#fff !important;
    }

    /* ✅ Contacts */
    #thmNavbar .nav-item.nav-contact .nav-link{
        gap: .55rem;
        padding: .75rem .95rem;
        justify-content:flex-start;
    }
    #thmNavbar .nav-item.nav-contact .nav-link i{ opacity:.95; }
    #thmNavbar .nav-item.nav-contact.is-last .nav-link{
        box-shadow: inset -1px 0 0 rgba(255,255,255,.18);
        margin-right: 6px;
    }

    /* =========================================================
       MEGA DROPDOWN
       ========================================================= */

    #thmNavbar .dropdown-menu{
        display:block;
        position:absolute;
        top: 100%;
        left: 0;
        background: transparent;
        padding: 0;
        margin: 0;
        z-index: 9999;
        overflow: visible;

        width: max-content;
        min-width: 0;

        max-width: min(var(--menu-max-w), calc(100vw - 20px));

        opacity: 0;
        visibility: hidden;
        transform: translateY(8px);
        pointer-events: none;
        transition: opacity .25s ease, transform .25s ease, visibility .25s ease;
    }

    #thmNavbar .dropdown-menu.show{
        opacity:1;
        visibility:visible;
        transform: translateY(0);
        pointer-events:auto;
    }

    @media (min-width: 992px){
        #thmNavbar .nav-item.has-dropdown:hover > .dropdown-menu{
            opacity:1;
            visibility:visible;
            transform: translateY(0);
            pointer-events:auto;
        }
    }

    /* ✅ NEW: Full-width dropdown on hover (fix last dropdown hidden at zoom) */
    @media (min-width: 992px){
        #thmNavbar .dropdown-menu.dm-fullwidth{
            left: var(--menu-gutter) !important;
            right: var(--menu-gutter) !important;
            width: auto !important;
            max-width: calc(100vw - (var(--menu-gutter) * 2)) !important;
        }
        #thmNavbar .dropdown-menu.dm-fullwidth .mega-panel{
            width: 100%;
            max-width: 100% !important;
        }
    }

    #thmNavbar .mega-panel{
        display:inline-flex;
        align-items:stretch;
        gap: 0;
        background: var(--secondary-color, #6B2528);
        border: 1px solid rgba(255,255,255,0.12);
        border-top: 0;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 12px 30px rgba(0,0,0,0.22);

        max-width: min(var(--menu-max-w), calc(100vw - 20px));
        overflow-x: auto;
        overflow-y: hidden;

        position: relative;
        will-change: transform;
        transition: box-shadow .25s ease;
    }

    #thmNavbar .mega-col{
        width: 270px;
        min-width: 270px;
        display:flex;
        flex-direction:column;
        padding: 8px;
        position: relative;
        margin-top: 0;
        align-self: flex-start;
    }

    #thmNavbar .mega-col:not([data-col="0"])::before{
        content:"";
        position:absolute;
        left:0;
        top:0;
        bottom:0;
        width:1px;
        background: rgba(255,255,255,0.14);
    }

    #thmNavbar .mega-list{
        list-style:none;
        margin:0;
        padding: 4px;
        max-height: calc(100vh - 180px);
        overflow:auto;
    }

    #thmNavbar .mega-list::-webkit-scrollbar{ width: 8px; height: 8px; }
    #thmNavbar .mega-list::-webkit-scrollbar-thumb{
        background: rgba(255,255,255,.20);
        border-radius: 10px;
    }
    #thmNavbar .mega-list::-webkit-scrollbar-track{
        background: rgba(0,0,0,.10);
        border-radius: 10px;
    }

    #thmNavbar .dropdown-item{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;

        padding: .62rem .95rem;
        color:#fff !important;
        font-weight: 400;
        font-size: .93rem;
        text-decoration:none;
        white-space: nowrap;

        border: 0;
        background: transparent;
        cursor:pointer;
        width:100%;
        text-align:left;
        border-radius: 10px;

        outline: 1px solid rgba(255,255,255,0.00);
        transition: background-color .25s ease, transform .25s ease, outline-color .25s ease;
        will-change: transform;
    }

    #thmNavbar .dropdown-item:hover{
        background: rgba(255,255,255,0.10);
        outline-color: rgba(255,255,255,0.10);
        transform: translateX(2px);
    }

    #thmNavbar .dropdown-item.is-active{
        background: rgba(255,255,255,0.13);
        outline: 1px solid rgba(255,255,255,0.16);
        position: relative;
    }

    #thmNavbar .dropdown-item.is-active::before{
        content:"";
        position:absolute;
        left: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 3px;
        height: 18px;
        border-radius: 3px;
        background: #f1c40f;
        opacity: .95;
    }

    #thmNavbar .dropdown-item.has-children::after{
        content:'›';
        font-size: 1.2rem;
        font-weight: 700;
        line-height: 1;
        color: rgba(255,255,255,0.9);
        margin-left: 10px;
        flex: 0 0 auto;
        transition: transform .25s ease, opacity .25s ease;
    }

    #thmNavbar .dropdown-item.has-children:hover::after{
        transform: translateX(2px);
        opacity: .95;
    }

    /* Dropdown Portal */
    #thmPortal{
        position: fixed;
        inset: 0;
        pointer-events: none;
        z-index: 12000;
    }
    #thmPortal .dropdown-menu{ pointer-events: auto; }

    #thmNavbar .dropdown-menu.is-portaled{
        position: fixed !important;
        top: 0;
        left: 0;
        right: auto;
    }

    /* OFFCANVAS (mobile) */
    #thmNavbar.use-offcanvas .menu-row{ display:none; }
    #thmNavbar.use-offcanvas .menu-toggle{ display:flex; }

    @media (max-width: 991.98px){
        #thmNavbar .menu-row{ display:none; }
        #thmNavbar .menu-toggle{ display:flex; }
        #thmNavbar .menu-scroll-btn,
        #thmNavbar .menu-scroll-fade-right,
        #thmNavbar .menu-scroll-fade-left{ display:none !important; }
    }

    #thmOffcanvas.dynamic-offcanvas{
        --bs-offcanvas-width: 340px;
        background: var(--secondary-color, #6B2528);
        color:#fff;
    }
    #thmOffcanvas.dynamic-offcanvas .offcanvas-header{
        border-bottom: 1px solid rgba(255,255,255,.15);
        padding: 14px 16px;
    }
    #thmOffcanvas.dynamic-offcanvas .offcanvas-title{
        font-weight:700;
        letter-spacing:.2px;
        color:#fff;
        margin:0;
    }
    #thmOffcanvas.dynamic-offcanvas .offcanvas-body{
        padding: 12px 10px 18px;
    }
    #thmOffcanvas .offcanvas-menu{ list-style:none; margin:0; padding:0; }

    #thmOffcanvas .oc-row{
        display:flex;
        align-items:center;
        gap: 8px;
        border-radius: 12px;
        padding: 8px 10px;
        transition: background .25s ease, transform .25s ease;
        will-change: transform;
    }
    #thmOffcanvas .oc-row:hover{ background: rgba(255,255,255,.08); transform: translateX(1px); }

    #thmOffcanvas .oc-link{
        flex: 1 1 auto;
        color: #fff !important;
        text-decoration:none;
        font-size: .95rem;
        line-height: 1.2;
        padding: 6px 8px;
        border-radius: 10px;
        white-space: normal;
        word-break: break-word;
        transition: background .25s ease, opacity .25s ease;
    }
    #thmOffcanvas .oc-link.active{
        background: rgba(255,255,255,.14);
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.18);
    }

    #thmOffcanvas .oc-toggle{
        flex: 0 0 auto;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid rgba(255,255,255,.18);
        background: rgba(255,255,255,.08);
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        cursor:pointer;
        transition: transform .25s ease, background .25s ease, border-color .25s ease;
    }
    #thmOffcanvas .oc-toggle:hover{ transform: translateY(-1px); background: rgba(255,255,255,.10); }
    #thmOffcanvas .oc-toggle:focus{
        outline:none;
        box-shadow: 0 0 0 3px rgba(201,75,80,.35);
    }
    #thmOffcanvas .oc-caret{
        width:0; height:0;
        border-left: 6px solid #fff;
        border-top: 5px solid transparent;
        border-bottom: 5px solid transparent;
        opacity: .9;
        transform: rotate(0deg);
        transition: transform .25s ease, opacity .25s ease;
    }
    #thmOffcanvas .oc-toggle[aria-expanded="true"] .oc-caret{ transform: rotate(90deg); }
    #thmOffcanvas .oc-sub{
        list-style:none;
        margin: 4px 0 6px;
        padding: 0 0 0 14px;
        border-left: 1px dashed rgba(255,255,255,.25);
    }

    /* Loading Overlay */
    #thmLoadingOverlay.menu-loading-overlay{
        position: fixed;
        inset: 0;
        background: rgba(10, 10, 10, 0.35);
        backdrop-filter: blur(2px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 20000;
        padding: 18px;
    }
    #thmLoadingOverlay.menu-loading-overlay.show{ display: flex; }

    /* Guard against Bootstrap overriding dropdown positioning */
    #thmNavbar .navbar-nav .dropdown-menu{
      position: absolute !important;
      inset: auto !important;
    }
    #thmNavbar .dropdown-menu.is-portaled{
      position: fixed !important;
    }

    /* ✅ Show Top Header Menu only on 992px and above */
@media (max-width: 991.98px){
  #thmNavbar,
  #thmOffcanvas,
  #thmLoadingOverlay,
  #thmPortal{
    display: none !important;
  }
}

</style>

<!-- LOADING OVERLAY -->
<div id="thmLoadingOverlay" class="menu-loading-overlay" aria-hidden="true">
    @include('partials.overlay')
</div>

<!-- Navbar HTML -->
<nav class="dynamic-navbar" id="thmNavbar">
    <div class="navbar-container">

        <div class="menu-scroll-fade-left" id="thmFadeLeft" aria-hidden="true"></div>
        <div class="menu-scroll-fade-right" id="thmFadeRight" aria-hidden="true"></div>

        <button class="menu-scroll-btn menu-scroll-prev" id="thmScrollPrev" type="button" aria-label="Scroll menu left">‹</button>
        <button class="menu-scroll-btn menu-scroll-next" id="thmScrollNext" type="button" aria-label="Scroll menu right">›</button>

        <div class="menu-row" id="thmMenuRow">
            <ul class="navbar-nav" id="thmMainMenuContainer">
                <!-- ✅ Contacts (2) + Menus will be loaded here -->
            </ul>
        </div>

        <!-- Hamburger (mobile) -->
        <button class="menu-toggle" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#thmOffcanvas"
                aria-controls="thmOffcanvas" aria-label="Open menu">
            <span class="burger"><span></span></span>
        </button>
    </div>

    <!-- Portal layer for mega dropdowns -->
    <div class="mega-portal" id="thmPortal" aria-hidden="true"></div>
</nav>

<!-- Offcanvas Sidebar -->
<div class="offcanvas offcanvas-start dynamic-offcanvas" tabindex="-1" id="thmOffcanvas" aria-labelledby="thmOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="thmOffcanvasLabel">Menu</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="offcanvas-menu" id="thmOffcanvasMenuList">
            <!-- Sidebar will be rendered here -->
        </ul>
    </div>
</div>

{{-- ✅ jQuery added (for smooth wheel->horizontal scroll like your header) --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
(() => {
    // ✅ prevent double init (if included multiple times)
    if (window.__TOP_HEADER_MENU_INIT__) return;
    window.__TOP_HEADER_MENU_INIT__ = true;

    class TopHeaderMenu {
        constructor() {
            // ✅ APIs
            this.apiMenus = @json(url('/api/public/top-header-menus'));

            // ✅ Primary: selected 2 (your "contact-info")
            this.apiContactsPrimary  = @json(url('/api/public/top-header-menus/contact-info'));

            // ✅ Fallback: list all contact infos (we will pick first 2 if primary fails/empty)
            this.apiContactsFallback = @json(url('/api/public/top-header-menus/contact-infos'));

            // ✅ Departments (for d-uuid appending)
            this.apiDepartmentsPublic = @json(url('/api/public/departments'));
            this.apiDepartments       = @json(url('/api/departments'));

            this.menuTree = [];
            this.contacts = [];

            this.nodeById = new Map();
            this.childrenById = new Map();

            this.deptUuidById = new Map();

            this.currentSlug = this.getCurrentSlug();
            this.currentPath = this.normPath(window.location.pathname || '/');

            this.activePathIds = [];
            this.activePathNodes = [];

            this.loadingEl = document.getElementById('thmLoadingOverlay');

            // portal meta
            this.portalMeta = new Map();
            this.portalBound = false;

            // scroller refs
            this.menuRowEl = null;
            this.btnNext = null;
            this.btnPrev = null;
            this.fadeRight = null;
            this.fadeLeft = null;

            this.init();
        }

        /* ---------------------------
         * Basics
         * --------------------------- */
        $(id){ return document.getElementById(id); }

        showLoading(message = 'Loading…') {
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

        normPath(p){
            p = (p || '/').toString().trim();
            if (!p.startsWith('/')) p = '/' + p;
            if (p.length > 1 && p.endsWith('/')) p = p.slice(0, -1);
            return p;
        }

        toUrlObject(url){
            try { return new URL(url, window.location.origin); }
            catch(e){ return null; }
        }

        getCurrentSlug() {
            const path = window.location.pathname || '';
            if (path === '/' || path === '') return '__HOME__';
            if (path.startsWith('/page/')) return path.replace('/page/', '').replace(/^\/+/, '');
            return '';
        }

        async fetchJson(url) {
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json' },
                cache: 'no-store'
            });
            const txt = await res.text();
            let data = null;
            try { data = txt ? JSON.parse(txt) : null; } catch(e){}
            return { ok: res.ok, status: res.status, data };
        }

        init() {
            this.loadAll();
            this.setupResizeListener();

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 992) this.forceCloseOffcanvas();
            });
        }

        setupResizeListener() {
            let t;
            window.addEventListener('resize', () => {
                clearTimeout(t);
                t = setTimeout(() => {
                    this.adjustMenuSizing();
                    this.toggleOverflowMode();
                    this.bindMegaGuards();
                    this.setupDesktopDropdownPortal();
                    this.repositionOpenPortaled();
                    this.setupMenuScroller();
                    this.bindWheelToHorizontalScroll(); // ✅ NEW
                }, 150);
            });
        }

        /* ---------------------------
         * Load Contacts + Menus + Departments
         * --------------------------- */
        async loadAll() {
            this.showLoading('Loading top header…');

            try {
                const [contactsPrimaryRes, menusRes, deptPublicRes] = await Promise.all([
                    this.fetchJson(this.apiContactsPrimary),
                    this.fetchJson(this.apiMenus),
                    this.fetchJson(this.apiDepartmentsPublic),
                ]);

                // ✅ departments: public -> fallback
                let deptList = [];
                if (deptPublicRes.ok) {
                    deptList = this.normalizeDepartmentsPayload(deptPublicRes.data);
                } else {
                    const deptRes = await this.fetchJson(this.apiDepartments);
                    if (deptRes.ok) deptList = this.normalizeDepartmentsPayload(deptRes.data);
                }
                this.buildDeptMap(deptList);

                // ✅ contacts: primary -> fallback
                let pickedContacts = this.normalizeContactsPayload(contactsPrimaryRes.data);

                if (!contactsPrimaryRes.ok || !pickedContacts.length) {
                    const contactsFallbackRes = await this.fetchJson(this.apiContactsFallback);
                    const fallbackContacts = this.normalizeContactsPayload(contactsFallbackRes.data);
                    if (fallbackContacts.length) pickedContacts = fallbackContacts;
                }
                this.contacts = (pickedContacts || []).slice(0, 2);

                this.menuTree = this.normalizeMenusPayload(menusRes.data);
                this.buildNodeMaps(this.menuTree);

                // ✅ active path (parent chain)
                this.activePathNodes = this.getActivePathNodes(this.menuTree);
                this.activePathIds = this.activePathNodes.map(n => n.id);

                this.renderMenu();
                this.renderOffcanvasMenu();

                setTimeout(() => {
                    this.resetMenuRowStart();
                    this.adjustMenuSizing();
                    this.toggleOverflowMode();
                    this.bindMegaGuards();
                    this.setupDesktopDropdownPortal();
                    this.setupMenuScroller();
                    this.bindWheelToHorizontalScroll(); // ✅ NEW
                    this.highlightActivePath();         // ✅ NEW (active on visit)
                }, 50);

            } catch (e) {
                console.error('TopHeaderMenu load error:', e);
                this.showError();
            } finally {
                this.hideLoading();
            }
        }

        /* ---------------------------
         * ✅ Departments normalization + mapping
         * --------------------------- */
        normalizeDepartmentsPayload(payload){
            let data = payload;
            if (data && typeof data === 'object' && data.success !== undefined) data = data.data;

            let items = [];
            if (Array.isArray(data)) items = data;
            else if (data && Array.isArray(data.items)) items = data.items;
            else if (data && Array.isArray(data.data)) items = data.data;

            return (items || []).filter(d => d && (d.id !== undefined && d.id !== null));
        }

        buildDeptMap(depts){
            this.deptUuidById.clear();
            (depts || []).forEach(d => {
                const id = Number(d.id);
                const uuid = (d.uuid ?? d.department_uuid ?? d.dept_uuid ?? '').toString().trim();
                if (id && uuid) this.deptUuidById.set(id, uuid);
            });
        }

        /* =========================================================
         * CONTACT NORMALIZATION
         * ========================================================= */
        normalizeContactsPayload(payload) {
            const root = (payload && typeof payload === 'object' && payload.success !== undefined)
                ? (payload.data ?? payload)
                : payload;

            const pickArr = (d) => {
                if (!d) return [];
                if (Array.isArray(d)) return d;

                if (Array.isArray(d.data)) return d.data;
                if (Array.isArray(d.items)) return d.items;
                if (Array.isArray(d.contacts)) return d.contacts;
                if (Array.isArray(d.contact_infos)) return d.contact_infos;

                if (d.data && typeof d.data === 'object') return pickArr(d.data);

                if (d.phone || d.email) return [d.phone, d.email].filter(Boolean);

                if (d.primary_contact || d.secondary_contact) return [d.primary_contact, d.secondary_contact].filter(Boolean);
                if (d.first || d.second) return [d.first, d.second].filter(Boolean);
                if (d.primary || d.secondary) return [d.primary, d.secondary].filter(Boolean);

                const keys = Object.keys(d || {});
                if (keys.length && keys.every(k => /^\d+$/.test(k))) return Object.values(d);

                const vals = Object.values(d || {}).filter(v => v && typeof v === 'object' && !Array.isArray(v));
                const likely = vals.filter(v =>
                    ('value' in v) || ('label' in v) || ('title' in v) || ('name' in v) ||
                    ('phone' in v) || ('email' in v) || ('url' in v) || ('href' in v) || ('type' in v) || ('key' in v)
                );
                if (likely.length >= 1) return likely;

                return [];
            };

            const arr = pickArr(root);

            const flattened = (arr || []).map(x => {
                if (!x || typeof x !== 'object') return x;
                if (x.contact_info && typeof x.contact_info === 'object') return x.contact_info;
                return x;
            });

            return flattened
                .map((c) => this.normalizeContact(c))
                .filter(Boolean);
        }

        normalizeContact(c) {
            if (!c || typeof c !== 'object') return null;

            const key = (c.key ?? c.contact_key ?? c.kind ?? '').toString().trim().toLowerCase();
            const rawType = (c.type ?? c.contact_type ?? '').toString().trim().toLowerCase();

            const label = (c.name ?? c.label ?? c.title ?? c.key ?? '').toString().trim();

            let value = (c.value ?? c.info ?? c.text ?? c.content ?? '').toString().trim();
            if (!value) value = label;

            const url = (c.url ?? c.href ?? '').toString().trim();
            const icon = (c.icon_class ?? c.icon ?? '').toString().trim();

            const typeGuess = this.normalizeContactType(key || rawType || '', value);

            const display = (value || label || '').toString().trim();
            if (!display && !url) return null;

            return { key, label, value: display, type: typeGuess, url, icon, raw: c };
        }

        normalizeContactType(t, value='') {
            const type = (t || '').toString().toLowerCase().trim();
            const v = (value || '').toString().toLowerCase();

            if (['phone','mobile','tel','telephone','call'].includes(type)) return 'phone';
            if (['email','mail'].includes(type)) return 'email';
            if (['address','location','map','maps'].includes(type)) return 'address';
            if (['whatsapp','wa'].includes(type)) return 'whatsapp';
            if (['website','web','url','link'].includes(type)) return 'website';

            if (v.includes('@')) return 'email';
            if (v.replace(/[^\d+]/g,'').length >= 8) return 'phone';

            return type || '';
        }

        /* ---------------------------
         * Menus normalization
         * --------------------------- */
        normalizeMenusPayload(payload) {
            let data = payload;
            if (data && typeof data === 'object' && data.success !== undefined) data = data.data;

            let items = [];
            if (Array.isArray(data)) items = data;
            else if (data && Array.isArray(data.items)) items = data.items;
            else if (data && Array.isArray(data.data)) items = data.data;

            items = (items || []).filter(it => {
                if (!it) return false;
                if (it.deleted_at) return false;
                const s = (it.status ?? '').toString().toLowerCase();
                if (s && !['active','published','public','enabled'].includes(s)) return false;
                const active = (it.is_active ?? it.active ?? 1);
                return (active === 1 || active === true || active === '1');
            });

            const hasChildren = items.some(it => Array.isArray(it.children));
            if (hasChildren) {
                const sortTree = (nodes) => {
                    nodes.sort((a,b) => (a.position||0) - (b.position||0));
                    nodes.forEach(n => Array.isArray(n.children) && sortTree(n.children));
                    return nodes;
                };
                return sortTree(items);
            }

            const hasParent = items.some(it => it.parent_id !== undefined && it.parent_id !== null);
            if (!hasParent) return items.sort((a,b) => (a.position||0) - (b.position||0));

            const byId = new Map();
            items.forEach(it => { it.children = []; byId.set(it.id, it); });

            const roots = [];
            items.forEach(it => {
                const pid = it.parent_id;
                if (pid && byId.has(pid)) byId.get(pid).children.push(it);
                else roots.push(it);
            });

            const sortTree = (nodes) => {
                nodes.sort((a,b) => (a.position||0) - (b.position||0));
                nodes.forEach(n => n.children && n.children.length && sortTree(n.children));
            };
            sortTree(roots);
            return roots;
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

        /* ---------------------------
         * Active match
         * --------------------------- */
        itemSlug(item){
            return (item?.slug || item?.page_slug || '').toString().trim();
        }

        itemUrl(item){
            return (
                item?.url ||
                item?.page_url ||
                item?.link ||
                item?.href ||
                ''
            ).toString().trim();
        }

        isItemActive(item){
            const slug = this.itemSlug(item);
            if (this.currentSlug && slug && slug === this.currentSlug) return true;

            const u = this.itemUrl(item);
            if (!u) return false;

            const obj = this.toUrlObject(
                u.startsWith('http') ? u : (u.startsWith('/') ? (window.location.origin + u) : (window.location.origin + '/' + u))
            );
            if (!obj) return false;

            if (obj.origin !== window.location.origin) return false;
            return this.normPath(obj.pathname) === this.currentPath;
        }

        getActivePathNodes(items) {
            const dfs = (nodes) => {
                for (const n of (nodes || [])) {
                    if (this.isItemActive(n)) return [n];
                    if (n.children && n.children.length) {
                        const res = dfs(n.children);
                        if (res.length) return [n, ...res];
                    }
                }
                return [];
            };
            return dfs(items || []);
        }

        /* ✅ NEW: Highlight active parent + leaf on visit */
        highlightActivePath(){
            const root = this.$('thmMainMenuContainer');
            if (!root) return;

            root.querySelectorAll('.nav-link.active').forEach(a => a.classList.remove('active'));
            root.querySelectorAll('.dropdown-item.is-active').forEach(a => a.classList.remove('is-active'));

            if (!this.activePathNodes || !this.activePathNodes.length) return;

            const top = this.activePathNodes[0];
            const topLink = root.querySelector(`.nav-item[data-id="${top.id}"] > a.nav-link`);
            if (topLink) topLink.classList.add('active');

            // mark leaf inside dropdown if already rendered
            this.activePathNodes.slice(1).forEach(n => {
                const dd = root.querySelector(`a.dropdown-item[data-mid="${n.id}"]`);
                if (dd) dd.classList.add('is-active');
            });
        }

        /* ---------------------------
         * Desktop sizing / modes
         * --------------------------- */
        adjustMenuSizing() {
            if (window.innerWidth < 992) return;

            const container = this.$('thmMainMenuContainer');
            const row = this.$('thmMenuRow');
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
            const nav = this.$('thmNavbar');
            if (!nav) return;

            const isMobile = window.innerWidth < 992;
            nav.classList.toggle('use-offcanvas', isMobile);
            if (!isMobile) this.forceCloseOffcanvas();
        }

        forceCloseOffcanvas() {
            const ocEl = this.$('thmOffcanvas');
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
            const row = this.$('thmMenuRow');
            if (!row) return;
            row.scrollLeft = 0;
        }

        /* ---------------------------
         * Contacts builders
         * --------------------------- */
        guessContactType(c){
            const t = (c.type || c.key || '').toLowerCase();
            if (t) return this.normalizeContactType(t, c.value || '');
            const v = (c.value || '').toLowerCase();
            if (v.includes('@')) return 'email';
            if (v.replace(/[^\d+]/g,'').length >= 8) return 'phone';
            return '';
        }

        sanitizePhone(val){
            let s = (val || '').toString().trim();
            if (!s) return '';
            s = s.replace(/[^\d+]/g,'');
            if (s.startsWith('+')) return '+' + s.slice(1).replace(/[^\d]/g,'');
            return s.replace(/[^\d]/g,'');
        }

        contactHref(c){
            const explicit = (c.url || '').trim();
            if (explicit) return explicit;

            const type = this.guessContactType(c);
            const val = (c.value || '').trim();
            if (!val) return '#';

            if (type === 'email') return `mailto:${val}`;
            if (type === 'phone') return `tel:${this.sanitizePhone(val)}`;

            if (type === 'address') {
                return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(val)}`;
            }

            if (type === 'whatsapp') {
                const phone = this.sanitizePhone(val).replace('+','');
                return phone ? `https://wa.me/${phone}` : '#';
            }

            if (type === 'website') {
                if (/^https?:\/\//i.test(val)) return val;
                return `https://${val.replace(/^\/+/, '')}`;
            }

            return '#';
        }

        contactIcon(c){
            const i = (c.icon || '').trim();
            if (i) return i;

            const type = this.guessContactType(c);
            if (type === 'email') return 'fa-solid fa-envelope';
            if (type === 'phone') return 'fa-solid fa-phone';
            if (type === 'address') return 'fa-solid fa-location-dot';
            if (type === 'whatsapp') return 'fa-brands fa-whatsapp';
            if (type === 'website') return 'fa-solid fa-globe';
            return 'fa-solid fa-circle-info';
        }

        buildContactNavItem(c, idx, isLast=false){
            const li = document.createElement('li');
            li.className = `nav-item nav-contact ${isLast ? 'is-last' : ''}`;
            li.dataset.kind = 'contact';
            li.dataset.index = String(idx);

            const a = document.createElement('a');
            a.className = 'nav-link contact-link';

            const href = this.contactHref(c);
            a.href = href;

            const type = this.guessContactType(c);
            if (['address','whatsapp','website'].includes(type) || /^https?:\/\//i.test(href)) {
                a.target = '_blank';
                a.rel = 'noopener';
            }

            const icon = document.createElement('i');
            icon.className = this.contactIcon(c);

            const span = document.createElement('span');
            span.textContent = (c.value || c.label || '').toString();

            a.appendChild(icon);
            a.appendChild(span);

            if (!href || href === '#') a.addEventListener('click', (e) => e.preventDefault());

            li.appendChild(a);
            return li;
        }

        buildContactOffcanvasItem(c){
            const li = document.createElement('li');

            const row = document.createElement('div');
            row.className = 'oc-row';

            const link = document.createElement('a');
            link.className = 'oc-link';

            const href = this.contactHref(c);
            link.href = href;

            const type = this.guessContactType(c);
            if (['address','whatsapp','website'].includes(type) || /^https?:\/\//i.test(href)) {
                link.target = '_blank';
                link.rel = 'noopener';
            }

            const icon = this.contactIcon(c);
            const text = (c.value || c.label || '').toString();
            link.innerHTML = `<i class="${icon} me-2"></i>${this.escapeHtml(text)}`;

            link.addEventListener('click', (e) => {
                if (!href || href === '#') { e.preventDefault(); return; }
                this.forceCloseOffcanvas();
            });

            row.appendChild(link);
            li.appendChild(row);

            return li;
        }

        escapeHtml(str){
            return (str ?? '').toString().replace(/[&<>"']/g, s => ({
                '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
            }[s]));
        }

        /* ---------------------------
         * Dept uuid resolver + append d-uuid
         * --------------------------- */
        getItemDeptUuid(item){
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

        getMenuItemUrl(item) {
            let url = '#';

            if (item.url && item.url.toString().trim() !== '') {
                const u = item.url.toString().trim();
                url = u.startsWith('http') ? u : (u.startsWith('/') ? (`{{ url('') }}` + u) : (`{{ url('') }}/${u}`));
            } else if (item.page_url && item.page_url.trim() !== '') {
                url = item.page_url.startsWith('http')
                    ? item.page_url
                    : `{{ url('') }}${item.page_url}`;
            } else if (item.page_slug && item.page_slug.trim() !== '') {
                url = `{{ url('/page') }}/${item.page_slug}`;
            } else if (item.slug) {
                url = `{{ url('/page') }}/${item.slug}`;
            }

            const deptUuid = this.getItemDeptUuid(item);
            url = this.applyDepartmentUuid(url, deptUuid);

            return url;
        }

        renderMenu() {
            const container = this.$('thmMainMenuContainer');
            if (!container) return;
            container.innerHTML = '';

            // ✅ Contacts first (2)
            if (this.contacts && this.contacts.length) {
                const c0 = this.contacts[0] ? this.buildContactNavItem(this.contacts[0], 0, false) : null;
                const c1 = this.contacts[1] ? this.buildContactNavItem(this.contacts[1], 1, true) : null;
                if (c0) container.appendChild(c0);
                if (c1) container.appendChild(c1);
            }

            if (!this.menuTree || !this.menuTree.length) {
                this.resetMenuRowStart();
                return;
            }

            const sortedItems = [...this.menuTree].sort((a,b) => (a.position||0) - (b.position||0));

            sortedItems.forEach(item => {
                const li = document.createElement('li');
                const hasChildren = item.children && item.children.length > 0;
                li.className = `nav-item ${hasChildren ? 'has-dropdown' : ''}`;
                li.dataset.id = item.id;
                li.dataset.slug = (item.slug || '');

                const a = document.createElement('a');
                a.className = 'nav-link';
                a.href = this.getMenuItemUrl(item);
                a.textContent = item.title || 'Menu';

                // ✅ mark leaf active instantly
                if (this.isItemActive(item)) a.classList.add('active');

                if (item.page_url && item.page_url.startsWith('http')) {
                    a.addEventListener('click', (e) => {
                        e.preventDefault();
                        window.open(a.href, '_blank');
                    });
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

        /* ---------------------------
         * Mega menu
         * --------------------------- */
        getAnchorTop(panel, anchorEl) {
            if (!panel || !anchorEl) return 0;

            const panelRect = panel.getBoundingClientRect();
            const aRect = anchorEl.getBoundingClientRect();

            let top = (aRect.top - panelRect.top);
            top = Math.max(0, top - 4);

            const minVisible = 140;
            const availableBelow = window.innerHeight - panelRect.top - 20;
            const maxTop = Math.max(0, availableBelow - minVisible);
            top = Math.min(top, maxTop);

            return top;
        }

        addMegaMenu(parentLi, children, activeNodesFromHere = []) {
            const dropdown = document.createElement('div');
            dropdown.className = 'dropdown-menu';

            const panel = document.createElement('div');
            panel.className = 'mega-panel';
            dropdown.appendChild(panel);

            this.renderMegaColumn(panel, 0, children, 0);

            if (activeNodesFromHere && activeNodesFromHere.length) {
                this.prefillMega(panel, children, activeNodesFromHere);
            }

            parentLi.appendChild(dropdown);

            dropdown.addEventListener('mousemove', (e) => {
                if (window.innerWidth < 992) return;
                const link = e.target.closest('a.dropdown-item[data-mid]');
                if (!link) return;

                const col = parseInt(link.dataset.col || '0', 10);
                const id = parseInt(link.dataset.mid || '0', 10);
                if (!id) return;

                this.setActiveInColumn(panel, col, id);

                const kids = this.childrenById.get(id) || [];
                if (kids.length) {
                    const offsetTop = this.getAnchorTop(panel, link);
                    this.renderMegaColumn(panel, col + 1, kids, offsetTop);
                } else {
                    this.clearMegaColumns(panel, col + 1);
                }
            });
        }

        renderMegaColumn(panel, colIndex, items, alignTopPx = 0) {
            let col = panel.querySelector(`.mega-col[data-col="${colIndex}"]`);
            if (!col) {
                col = document.createElement('div');
                col.className = 'mega-col';
                col.dataset.col = String(colIndex);

                const ul = document.createElement('ul');
                ul.className = 'mega-list';
                col.appendChild(ul);

                panel.appendChild(col);
            }

            col.style.marginTop = (colIndex > 0 && alignTopPx > 0) ? `${alignTopPx}px` : '0px';
            this.clearMegaColumns(panel, colIndex + 1);

            const ul = col.querySelector('.mega-list');
            ul.innerHTML = '';

            const sorted = [...(items || [])].sort((a,b) => (a.position||0) - (b.position||0));

            sorted.forEach(item => {
                const li = document.createElement('li');
                li.dataset.id = item.id;
                li.dataset.slug = item.slug;

                const a = document.createElement('a');
                a.className = 'dropdown-item';
                a.href = this.getMenuItemUrl(item);
                a.textContent = item.title || 'Menu';

                a.dataset.mid = String(item.id);
                a.dataset.col = String(colIndex);

                const hasChildren = item.children && item.children.length > 0;
                if (hasChildren) a.classList.add('has-children');

                if (item.page_url && item.page_url.startsWith('http')) {
                    a.addEventListener('click', (e) => {
                        e.preventDefault();
                        window.open(a.href, '_blank');
                    });
                }

                li.appendChild(a);
                ul.appendChild(li);
            });
        }

        clearMegaColumns(panel, startIndex) {
            const cols = Array.from(panel.querySelectorAll('.mega-col'));
            cols.forEach(c => {
                const idx = parseInt(c.dataset.col || '0', 10);
                if (idx >= startIndex) c.remove();
            });
        }

        setActiveInColumn(panel, colIndex, id) {
            const col = panel.querySelector(`.mega-col[data-col="${colIndex}"]`);
            if (!col) return;

            col.querySelectorAll('a.dropdown-item.is-active').forEach(a => a.classList.remove('is-active'));

            const a = col.querySelector(`a.dropdown-item[data-mid="${id}"]`);
            if (a) a.classList.add('is-active');
        }

        prefillMega(panel, rootChildren, activeNodesFromHere) {
            let currentCol = 0;

            for (let i = 0; i < activeNodesFromHere.length; i++) {
                const node = activeNodesFromHere[i];
                if (!node || !node.id) break;

                this.setActiveInColumn(panel, currentCol, node.id);

                const kids = this.childrenById.get(node.id) || [];
                if (!kids.length) break;

                const anchorEl = panel.querySelector(`.mega-col[data-col="${currentCol}"] a.dropdown-item[data-mid="${node.id}"]`);
                const offsetTop = this.getAnchorTop(panel, anchorEl);

                currentCol += 1;
                this.renderMegaColumn(panel, currentCol, kids, offsetTop);
            }
        }

        bindMegaGuards() {
            if (window.innerWidth < 992) return;

            const root = this.$('thmMainMenuContainer');
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

            // ✅ keep safe, but full-width dropdown solves all edge clipping
            menu.style.left = '0';
            menu.style.right = 'auto';
        }

        /* ---------------------------
         * ✅ Desktop dropdown portal (no clipping + fullwidth)
         * --------------------------- */
        ensurePortal() { return this.$('thmPortal'); }

        setupDesktopDropdownPortal() {
            if (window.innerWidth < 992) {
                this.restoreAllPortaled();
                return;
            }

            const portal = this.ensurePortal();
            const root = this.$('thmMainMenuContainer');
            const row = this.$('thmMenuRow');
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

            // ✅ ensure full-width on hover (safety)
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

            // ✅ make full-width dropdown
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

        getMenuGutterPx(){
            try{
                const v = getComputedStyle(document.documentElement).getPropertyValue('--menu-gutter') || '';
                const n = parseFloat(v);
                return Number.isFinite(n) ? n : 14;
            }catch(e){ return 14; }
        }

        // ✅ always position dropdown full-width under navbar
        positionPortaledDropdown(anchorLi, dropdown) {
            const nav = this.$('thmNavbar');
            if (!nav || !dropdown) return;

            const navRect = nav.getBoundingClientRect();
            const pad = Math.max(8, this.getMenuGutterPx());

            dropdown.style.top = `${Math.round(navRect.bottom)}px`;
            dropdown.style.left = `${pad}px`;
            dropdown.style.right = `${pad}px`;
            dropdown.style.width = 'auto';
            dropdown.style.maxWidth = `calc(100vw - ${(pad * 2)}px)`;
        }

        /* ---------------------------
         * Scroll arrows logic
         * --------------------------- */
        setupMenuScroller() {
            if (window.innerWidth < 992) return;

            this.menuRowEl = this.$('thmMenuRow');
            this.btnNext = this.$('thmScrollNext');
            this.btnPrev = this.$('thmScrollPrev');
            this.fadeRight = this.$('thmFadeRight');
            this.fadeLeft = this.$('thmFadeLeft');

            if (!this.menuRowEl || !this.btnNext || !this.btnPrev) return;

            const update = () => {
                const row = this.menuRowEl;
                const maxScroll = Math.max(0, (row.scrollWidth || 0) - (row.clientWidth || 0));
                const hasOverflow = maxScroll > 2;

                const atStart = (row.scrollLeft || 0) <= 1;
                const atEnd = (row.scrollLeft || 0) >= (maxScroll - 1);

                this.btnNext.style.display = (hasOverflow && !atEnd) ? 'flex' : 'none';
                this.btnPrev.style.display = (hasOverflow && !atStart) ? 'flex' : 'none';

                if (this.fadeRight) this.fadeRight.style.display = (hasOverflow && !atEnd) ? 'block' : 'none';
                if (this.fadeLeft)  this.fadeLeft.style.display  = (hasOverflow && !atStart) ? 'block' : 'none';
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

        /* ✅ NEW: Wheel scroll -> horizontal on desktop overflow */
        bindWheelToHorizontalScroll(){
            if (window.innerWidth < 992) return;

            const row = this.$('thmMenuRow');
            if (!row || row.dataset.wheelBound === '1') return;
            row.dataset.wheelBound = '1';

            $('#thmMenuRow').on('wheel', function(e){
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

        /* ✅ NEW: keep dropdown full-width while hovering */
        bindFullWidthHoverJquery(){
            if (window.innerWidth < 992) return;

            const self = this;
            const $root = $('#thmMainMenuContainer');

            $root.off('mouseenter.thmfull mouseleave.thmfull', '> .nav-item.has-dropdown');

            $root.on('mouseenter.thmfull', '> .nav-item.has-dropdown', function(){
                const dm = this.querySelector(':scope > .dropdown-menu');
                if (!dm) return;
                dm.classList.add('dm-fullwidth');
                if (dm.classList.contains('is-portaled') && dm.classList.contains('show')) {
                    self.positionPortaledDropdown(this, dm);
                }
            });

            $root.on('mouseleave.thmfull', '> .nav-item.has-dropdown', function(){
                const dm = this.querySelector(':scope > .dropdown-menu');
                if (!dm) return;
                dm.classList.add('dm-fullwidth');
            });
        }

        /* ---------------------------
         * Offcanvas render
         * --------------------------- */
        renderOffcanvasMenu() {
            const root = this.$('thmOffcanvasMenuList');
            if (!root) return;

            root.innerHTML = '';

            if (this.contacts && this.contacts.length) {
                this.contacts.slice(0,2).forEach(c => root.appendChild(this.buildContactOffcanvasItem(c)));
            }

            if (!this.menuTree || this.menuTree.length === 0) return;

            const sortedItems = [...this.menuTree].sort((a,b) => (a.position||0) - (b.position||0));
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
            link.textContent = item.title || 'Menu';

            if (this.isItemActive(item)) link.classList.add('active');

            const href = link.getAttribute('href') || '#';

            if (item.page_url && item.page_url.startsWith('http')) {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    window.open(link.href, '_blank');
                    this.forceCloseOffcanvas();
                });
            } else {
                link.addEventListener('click', (e) => {
                    if (href && href !== '#') {
                        e.preventDefault();
                        this.forceCloseOffcanvas();
                        setTimeout(() => { window.location.href = href; }, 120);
                    }
                });
            }

            row.appendChild(link);

            if (!hasChildren) {
                li.appendChild(row);
                return li;
            }

            const collapseId = `thm_oc_${item.id}`;
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

        showError() {
            const container = this.$('thmMainMenuContainer');
            if (container) container.innerHTML = '';
            const off = this.$('thmOffcanvasMenuList');
            if (off) off.innerHTML = '';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        window.topHeaderMenu = new TopHeaderMenu();
    });
})();
</script>
