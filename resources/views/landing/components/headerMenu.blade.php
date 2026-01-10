<!-- views/modules/header/header.blade.php -->

<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    /* =========================================================
       Dynamic Header Menu (Public) - Mega Column Flyout
       - L1 dropdown opens under parent
       - Child menus render as NEW BLOCK/COLUMN beside parent column
       - Hovering an item updates the next column content (top-aligned)
       - Supports deep levels via more columns
       - Desktop: ✅ 1280px max + horizontal scroll + right arrow
       - Mobile: hamburger -> offcanvas sidebar
       ========================================================= */

    * { margin:0; padding:0; box-sizing:border-box; }

    :root{
        /* ✅ Hard cap (requested) */
        --menu-max-w: 1280px;
    }

    /* Navbar Container */
    .dynamic-navbar{
        background: var(--primary-color, #9E363A);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
        width: 100%;
        overflow: visible;
    }

    .navbar-container{
        display:flex;
        align-items:stretch;
        justify-content:flex-start;          /* ✅ start from left */
        width:100%;
        position:relative;
        overflow: visible;

        /* ✅ restricted to 1280 everywhere */
        max-width: var(--menu-max-w);
        margin: 0 auto;
        padding: 0 10px;
    }

    .menu-row{
        flex: 1 1 auto;
        display:flex;
        justify-content:flex-start;          /* ✅ start from left */
        align-items:stretch;
        min-width: 0;

        /* ✅ horizontal scroll (don’t overflow screen) */
        width: 100%;
        max-width: var(--menu-max-w);
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;

        /* ✅ keep room for scroll arrow overlay so text doesn’t hide under it */
        padding-right: 44px;

        /* scrollbar */
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,.25) rgba(0,0,0,.12);
    }

    /* ✅ VERY THIN horizontal scrollbar (requested) */
    .menu-row::-webkit-scrollbar{ height: 3px; }
    .menu-row::-webkit-scrollbar-thumb{
        background: rgba(255,255,255,.25);
        border-radius: 10px;
    }
    .menu-row::-webkit-scrollbar-track{
        background: rgba(0,0,0,.12);
        border-radius: 10px;
    }

    /* ✅ Scroll arrows (desktop only) */
    .menu-scroll-btn{
        position:absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 34px;
        height: 34px;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,.22);
        background: rgba(255,255,255,.10);
        color:#fff;
        display:none;              /* shown via JS when overflow */
        align-items:center;
        justify-content:center;
        cursor:pointer;
        z-index: 11000;
        box-shadow: 0 10px 22px rgba(0,0,0,.22);
        transition: transform .18s ease, background .18s ease, opacity .18s ease;
        user-select:none;
        backdrop-filter: blur(2px);
    }
    .menu-scroll-btn:hover{ transform: translateY(-50%) translateY(-1px); background: rgba(255,255,255,.14); }
    .menu-scroll-btn:active{ transform: translateY(-50%) translateY(0px); }
    .menu-scroll-btn:focus{
        outline:none;
        box-shadow: 0 0 0 3px rgba(201,75,80,.35), 0 10px 22px rgba(0,0,0,.22);
    }

    .menu-scroll-prev{ left: 6px; }
    .menu-scroll-next{ right: 6px; }  /* ✅ right arrow requested */

    /* Small fade so it looks natural */
    .menu-scroll-fade-right{
        position:absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 54px;
        pointer-events:none;
        background: linear-gradient(90deg, rgba(158,54,58,0.0), rgba(158,54,58,0.75));
        border-radius: 0;
        display:none; /* shown via JS when overflow */
        z-index: 10500;
    }

    .menu-scroll-fade-left{
        position:absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 34px;
        pointer-events:none;
        background: linear-gradient(270deg, rgba(158,54,58,0.0), rgba(158,54,58,0.65));
        display:none; /* shown via JS when scrolled right */
        z-index: 10500;
    }

    /* Hamburger (mobile only) */
    .menu-toggle{
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
    .menu-toggle:hover{ transform: translateY(-1px); opacity:.95; }
    .menu-toggle:focus{
        outline:none;
        box-shadow: 0 0 0 3px rgba(201,75,80,.35);
        border-radius: 12px;
    }
    .burger{
        width: 22px;
        height: 16px;
        position: relative;
        display: inline-block;
    }
    .burger::before, .burger::after, .burger span{
        content:"";
        position:absolute;
        left:0; right:0;
        height:2px;
        background:#fff;
        border-radius:2px;
        opacity:.95;
        transition: transform .25s ease, opacity .25s ease;
    }
    .burger::before{ top:0; }
    .burger span{ top:7px; }
    .burger::after{ bottom:0; }

    /* Menu List - single row */
    .navbar-nav{
        display:flex;
        flex-direction:row;
        flex-wrap:nowrap;
        list-style:none;
        margin:0;
        padding:0;
        align-items:stretch;
        justify-content:flex-start;  /* ✅ start from left */
        min-width:0;
        width: max-content;          /* scroll inside menu-row */
    }

    .nav-item{
        position: relative;
        margin:0;
        display:flex;
        flex: 0 0 auto;
        min-width: 0;
    }

    .nav-link{
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

    .navbar-nav.compact .nav-link{ font-size:.85rem; padding:.75rem .8rem; }
    .navbar-nav.very-compact .nav-link{ font-size:.8rem; padding:.75rem .55rem; }
    .navbar-nav.ultra-compact .nav-link{ font-size:.75rem; padding:.75rem .45rem; }

    .nav-link:hover,
    .nav-link.active{
        background-color: var(--secondary-color, #6B2528);
        color:#fff !important;
    }

    /* =========================================================
       MEGA DROPDOWN
       ========================================================= */

    .dynamic-navbar .dropdown-menu{
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

        /* ✅ cap dropdown to 1280 and viewport */
        max-width: min(var(--menu-max-w), calc(100vw - 20px));

        opacity: 0;
        visibility: hidden;
        transform: translateY(8px);
        pointer-events: none;
        transition: opacity .25s ease, transform .25s ease, visibility .25s ease;
    }

    .dynamic-navbar .dropdown-menu.show{
        opacity:1;
        visibility:visible;
        transform: translateY(0);
        pointer-events:auto;
    }

    @media (min-width: 992px){
        .nav-item.has-dropdown:hover > .dropdown-menu{
            opacity:1;
            visibility:visible;
            transform: translateY(0);
            pointer-events:auto;
        }
    }

    .dynamic-navbar .mega-panel{
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

    .dynamic-navbar .mega-col{
        width: 270px;
        min-width: 270px;
        display:flex;
        flex-direction:column;
        padding: 8px;
        position: relative;
        margin-top: 0;
        align-self: flex-start;
    }

    .dynamic-navbar .mega-col:not([data-col="0"])::before{
        content:"";
        position:absolute;
        left:0;
        top:0;
        bottom:0;
        width:1px;
        background: rgba(255,255,255,0.14);
    }

    .dynamic-navbar .mega-list{
        list-style:none;
        margin:0;
        padding: 4px;
        max-height: calc(100vh - 180px);
        overflow:auto;
    }

    .dynamic-navbar .mega-list::-webkit-scrollbar{ width: 8px; height: 8px; }
    .dynamic-navbar .mega-list::-webkit-scrollbar-thumb{
        background: rgba(255,255,255,.20);
        border-radius: 10px;
    }
    .dynamic-navbar .mega-list::-webkit-scrollbar-track{
        background: rgba(0,0,0,.10);
        border-radius: 10px;
    }

    .dynamic-navbar .dropdown-item{
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

    .dynamic-navbar .dropdown-item:hover{
        background: rgba(255,255,255,0.10);
        outline-color: rgba(255,255,255,0.10);
        transform: translateX(2px);
    }

    .dynamic-navbar .dropdown-item.is-active{
        background: rgba(255,255,255,0.13);
        outline: 1px solid rgba(255,255,255,0.16);
        position: relative;
    }

    .dynamic-navbar .dropdown-item.is-active::before{
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

    .dynamic-navbar .dropdown-item.has-children::after{
        content:'›';
        font-size: 1.2rem;
        font-weight: 700;
        line-height: 1;
        color: rgba(255,255,255,0.9);
        margin-left: 10px;
        flex: 0 0 auto;
        transition: transform .25s ease, opacity .25s ease;
    }

    .dynamic-navbar .dropdown-item.has-children:hover::after{
        transform: translateX(2px);
        opacity: .95;
    }

    /* =========================================================
       ✅ DROPDOWN PORTAL (prevents clipping by horizontal scroller)
       ========================================================= */
    .mega-portal{
        position: fixed;
        inset: 0;
        pointer-events: none;
        z-index: 12000;
    }
    .mega-portal .dropdown-menu{ pointer-events: auto; }

    .dynamic-navbar .dropdown-menu.is-portaled{
        position: fixed !important;
        top: 0;
        left: 0;
        right: auto;
    }

    /* =========================================================
       OFFCANVAS (mobile)
       ========================================================= */
    .dynamic-navbar.use-offcanvas .menu-row{ display:none; }
    .dynamic-navbar.use-offcanvas .menu-toggle{ display:flex; }

    @media (max-width: 991.98px){
        .menu-row{ display:none; }
        .menu-toggle{ display:flex; }
        .menu-scroll-btn, .menu-scroll-fade-right, .menu-scroll-fade-left{ display:none !important; }
    }

    .dynamic-offcanvas{
        --bs-offcanvas-width: 340px;
        background: var(--secondary-color, #6B2528);
        color:#fff;
    }
    .dynamic-offcanvas .offcanvas-header{
        border-bottom: 1px solid rgba(255,255,255,.15);
        padding: 14px 16px;
    }
    .dynamic-offcanvas .offcanvas-title{
        font-weight:700;
        letter-spacing:.2px;
        color:#fff;
        margin:0;
    }
    .dynamic-offcanvas .offcanvas-body{
        padding: 12px 10px 18px;
    }
    .offcanvas-menu{ list-style:none; margin:0; padding:0; }

    .oc-row{
        display:flex;
        align-items:center;
        gap: 8px;
        border-radius: 12px;
        padding: 8px 10px;
        transition: background .25s ease, transform .25s ease;
        will-change: transform;
    }
    .oc-row:hover{ background: rgba(255,255,255,.08); transform: translateX(1px); }

    .oc-link{
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
    .oc-link.active{
        background: rgba(255,255,255,.14);
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.18);
    }

    .oc-toggle{
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
    .oc-toggle:hover{ transform: translateY(-1px); background: rgba(255,255,255,.10); }
    .oc-toggle:focus{
        outline:none;
        box-shadow: 0 0 0 3px rgba(201,75,80,.35);
    }
    .oc-caret{
        width:0; height:0;
        border-left: 6px solid #fff;
        border-top: 5px solid transparent;
        border-bottom: 5px solid transparent;
        opacity: .9;
        transform: rotate(0deg);
        transition: transform .25s ease, opacity .25s ease;
    }
    .oc-toggle[aria-expanded="true"] .oc-caret{ transform: rotate(90deg); }
    .oc-sub{
        list-style:none;
        margin: 4px 0 6px;
        padding: 0 0 0 14px;
        border-left: 1px dashed rgba(255,255,255,.25);
    }

    /* =========================================================
       LOADING OVERLAY
       ========================================================= */
    .menu-loading-overlay{
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
    .menu-loading-overlay.show{ display: flex; }

    .menu-loading-card{
        background: var(--secondary-color, #6B2528);
        border: 1px solid rgba(255,255,255,.16);
        border-radius: 16px;
        box-shadow: 0 18px 50px rgba(0,0,0,.35);
        color: #fff;
        padding: 16px 18px;
        min-width: 260px;
        max-width: 92vw;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .menu-loading-text{
        display:flex;
        flex-direction:column;
        gap: 2px;
        line-height: 1.2;
    }
    .menu-loading-text strong{ font-size: 1rem; }
    .menu-loading-text small{ opacity: .85; font-size: .85rem; }

    /* ✅ Guard against Bootstrap overriding mega menu dropdown positioning */
.dynamic-navbar .navbar-nav .dropdown-menu{
  position: absolute !important;
  inset: auto !important;
}

/* keep your portal mode working */
.dynamic-navbar .dropdown-menu.is-portaled{
  position: fixed !important;
}

</style>

<!-- LOADING OVERLAY -->
<div id="menuLoadingOverlay" class="menu-loading-overlay" aria-hidden="true">
    @include('partials.overlay')
</div>

<!-- Navbar HTML -->
<nav class="dynamic-navbar" id="dynamicNavbar">
    <div class="navbar-container">

        <!-- ✅ fades + arrows (desktop only) -->
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

            this.nodeById = new Map();
            this.childrenById = new Map();

            this.currentSlug = this.getCurrentSlug();
            this.activePathIds = [];
            this.activePathNodes = [];

            this.loadingEl = document.getElementById('menuLoadingOverlay');

            // portal meta
            this.portalMeta = new Map();
            this.portalBound = false;

            // ✅ scroller refs
            this.menuRowEl = null;
            this.btnNext = null;
            this.btnPrev = null;
            this.fadeRight = null;
            this.fadeLeft = null;

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
                    this.setupMenuScroller();     // ✅ keep arrows in sync
                }, 150);
            });
        }

        getCurrentSlug() {
            const path = window.location.pathname || '';
            if (path === '/' || path === '') return '__HOME__';
            if (path.startsWith('/page/')) return path.replace('/page/', '').replace(/^\/+/, '');
            return '';
        }

        async loadMenu() {
            this.showLoading('Loading menu…');

            try {
                const response = await fetch(`${this.apiBase}/tree`, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                const data = await response.json();

                if (data.success && data.data) {
                    this.menuData = data.data;

                    this.buildNodeMaps(this.menuData);

                    this.activePathNodes = (this.currentSlug && this.currentSlug !== '__HOME__')
                        ? this.getActivePathNodes(this.menuData, this.currentSlug)
                        : [];
                    this.activePathIds = this.activePathNodes.map(n => n.id);

                    this.renderMenu();
                    this.renderOffcanvasMenu();

                    setTimeout(() => {
                        this.resetMenuRowStart();   // ✅ always start from left (Home visible)
                        this.adjustMenuSizing();
                        this.toggleOverflowMode();
                        this.bindMegaGuards();
                        this.setupDesktopDropdownPortal();
                        this.setupMenuScroller();   // ✅ show right arrow when overflow
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
                    const nodeSlug = (n.slug || n.page_slug || '');
                    if (nodeSlug === target) return [n];

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

        /* ✅ Always start from left so Home is default visible */
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
                e.preventDefault();
                this.forceCloseOffcanvas();
                setTimeout(() => { window.location.href = link.href; }, 120);
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
                li.dataset.slug = item.slug;

                const a = document.createElement('a');
                a.className = 'nav-link';
                a.href = this.getMenuItemUrl(item);
                a.textContent = item.title;

                if (item.page_url && item.page_url.startsWith('http')) {
                    a.addEventListener('click', (e) => {
                        e.preventDefault();
                        window.open(item.page_url, '_blank');
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

            // ✅ ensure Home is visible after re-render
            this.resetMenuRowStart();
        }

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

            const sorted = [...items].sort((a,b) => (a.position||0) - (b.position||0));

            sorted.forEach(item => {
                const li = document.createElement('li');
                li.dataset.id = item.id;
                li.dataset.slug = item.slug;

                const a = document.createElement('a');
                a.className = 'dropdown-item';
                a.href = this.getMenuItemUrl(item);
                a.textContent = item.title;

                a.dataset.mid = String(item.id);
                a.dataset.col = String(colIndex);

                const hasChildren = item.children && item.children.length > 0;
                if (hasChildren) a.classList.add('has-children');

                if (item.page_url && item.page_url.startsWith('http')) {
                    a.addEventListener('click', (e) => {
                        e.preventDefault();
                        window.open(item.page_url, '_blank');
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

            const pad = 10;
            const rect = menu.getBoundingClientRect();

            if (rect.right > (window.innerWidth - pad)) {
                menu.style.left = 'auto';
                menu.style.right = '0';
            }
        }

        /* ✅ Desktop portal for dropdown (prevents clipping by menuRow overflow) */
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

            dropdown.classList.add('is-portaled', 'show');
            requestAnimationFrame(() => this.positionPortaledDropdown(anchorLi, dropdown));
        }

        unportalizeDropdown(dropdown) {
            const meta = this.portalMeta.get(dropdown);
            if (!meta || !meta.anchor || !meta.placeholder) return;

            dropdown.classList.remove('show', 'is-portaled');
            dropdown.style.removeProperty('top');
            dropdown.style.removeProperty('left');
            dropdown.style.removeProperty('right');

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
            const aRect = anchorLi.getBoundingClientRect();

            const pad = 10;

            dropdown.style.top = `${Math.round(navRect.bottom)}px`;
            dropdown.style.left = `${Math.round(Math.max(pad, aRect.left))}px`;
            dropdown.style.right = 'auto';

            const r = dropdown.getBoundingClientRect();
            if (r.right > (window.innerWidth - pad)) {
                dropdown.style.left = 'auto';
                dropdown.style.right = `${pad}px`;
            }

            const r2 = dropdown.getBoundingClientRect();
            if (r2.left < pad) {
                dropdown.style.right = 'auto';
                dropdown.style.left = `${pad}px`;
            }
        }

        /* ✅ Scroll arrows logic (right arrow when too many menus) */
        setupMenuScroller() {
            if (window.innerWidth < 992) return;

            this.menuRowEl = document.getElementById('menuRow');
            this.btnNext = document.getElementById('menuScrollNext');
            this.btnPrev = document.getElementById('menuScrollPrev');
            this.fadeRight = document.getElementById('menuFadeRight');
            this.fadeLeft = document.getElementById('menuFadeLeft');

            if (!this.menuRowEl || !this.btnNext || !this.btnPrev) return;

            const update = () => {
                const row = this.menuRowEl;
                const maxScroll = Math.max(0, (row.scrollWidth || 0) - (row.clientWidth || 0));
                const hasOverflow = maxScroll > 2;

                const atStart = (row.scrollLeft || 0) <= 1;
                const atEnd = (row.scrollLeft || 0) >= (maxScroll - 1);

                // show/hide
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

        applyDepartmentUuid(url, deptUuid) {
            deptUuid = (deptUuid || '').trim();
            if (!deptUuid) return url;
            if (!url || url === '#') return url;

            if (url.includes(deptUuid)) return url;

            try {
                const u = new URL(url, window.location.origin);
                if (u.origin !== window.location.origin) return url;
            } catch (e) {}

            const sep = url.includes('?') ? '&' : '?';
            return `${url}${sep}${encodeURIComponent(deptUuid)}`;
        }

        getMenuItemUrl(item) {
            let url = '#';

            if (item.page_url && item.page_url.trim() !== '') {
                url = item.page_url.startsWith('http')
                    ? item.page_url
                    : `{{ url('') }}${item.page_url}`;
            } else if (item.page_slug && item.page_slug.trim() !== '') {
                url = `{{ url('/page') }}/${item.page_slug}`;
            } else if (item.slug) {
                url = `{{ url('/page') }}/${item.slug}`;
            }

            url = this.applyDepartmentUuid(url, item.department_uuid);
            return url;
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

            const href = link.getAttribute('href') || '#';

            if (item.page_url && item.page_url.startsWith('http')) {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    window.open(item.page_url, '_blank');
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
