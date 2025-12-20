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
       - Overflow fallback: hamburger -> offcanvas sidebar
       ========================================================= */

    /* Reset & Base (kept) */
    * { margin:0; padding:0; box-sizing:border-box; }

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
        justify-content:center;
        width:100%;
        position:relative;
        overflow: visible;
    }

    .menu-row{
        flex: 0 0 auto;
        display:flex;
        justify-content:center;
        align-items:stretch;
        overflow: visible;
        min-width: 0;
    }

    /* Hamburger (mobile + overflow) */
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
        justify-content:center;
        width:auto;
        min-width:0;
    }

    .nav-item{
        position: relative; /* anchor dropdown under this item */
        margin:0;
        display:flex;
        flex: 0 0 auto;
        min-width: 0;
    }

    /* Top Level Links */
    .nav-link{
        display:flex;
        align-items:center;
        justify-content:center;
        color:#fff !important;
        font-weight:400;
        font-size: 0.95rem;
        padding: 0.75rem 1.2rem;
        text-decoration:none;
        white-space: nowrap;
        border: none;
        background: transparent;
        cursor:pointer;
        width:100%;
        text-align:center;

        /* nicer hover */
        transition: background-color .25s ease, color .25s ease, transform .25s ease;
    }

    /* Dynamic sizing classes */
    .navbar-nav.compact .nav-link{ font-size:.85rem; padding:.75rem .8rem; }
    .navbar-nav.very-compact .nav-link{ font-size:.8rem; padding:.75rem .55rem; }
    .navbar-nav.ultra-compact .nav-link{ font-size:.75rem; padding:.75rem .45rem; }

    .nav-link:hover,
    .nav-link.active{
        background-color: var(--secondary-color, #6B2528);
        color:#fff !important;
    }

    /* =========================================================
       MEGA DROPDOWN (Column blocks like your screenshot)
       FIX: background width should be only based on columns count
       ========================================================= */

    /* Dropdown container under top-level item */
    .dynamic-navbar .dropdown-menu{
        /* IMPORTANT: keep it "block" always (absolute), animate via opacity/visibility */
        display:block;
        position:absolute;
        top: 100%;
        left: 0;

        background: transparent; /* columns carry background */
        padding: 0;
        margin: 0;

        z-index: 9999;
        overflow: visible;

        /* FIX: shrink-wrap to content instead of full row width */
        width: max-content;
        min-width: 0;
        max-width: calc(100vw - 20px);

        /* animation */
        opacity: 0;
        visibility: hidden;
        transform: translateY(8px);
        pointer-events: none;
        transition: opacity .25s ease, transform .25s ease, visibility .25s ease;
    }

    /* open state (desktop hover OR .show) */
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

    /* The mega panel */
    .dynamic-navbar .mega-panel{
        /* FIX: inline-flex so width becomes only columns width */
        display:inline-flex;
        align-items:stretch;
        gap: 0; /* NO GAP between columns */
        background: var(--secondary-color, #6B2528);
        border: 1px solid rgba(255,255,255,0.12);
        border-top: 0;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 12px 30px rgba(0,0,0,0.22);

        /* if too wide, cap + allow horizontal scroll */
        max-width: calc(100vw - 20px);
        overflow-x: auto;
        overflow-y: hidden;

        position: relative; /* needed for staggered columns calc */
        will-change: transform;
        transition: box-shadow .25s ease;
    }

    /* Each column block */
    .dynamic-navbar .mega-col{
        width: 270px;
        min-width: 270px;
        display:flex;
        flex-direction:column;
        padding: 8px;
        position: relative;

        /* child columns will be staggered by inline margin-top */
        margin-top: 0;
        align-self: flex-start;
    }

    /* divider line between columns (stagger-aware) */
    .dynamic-navbar .mega-col:not([data-col="0"])::before{
        content:"";
        position:absolute;
        left:0;
        top:0;
        bottom:0;
        width:1px;
        background: rgba(255,255,255,0.14);
    }

    /* scroll list inside column */
    .dynamic-navbar .mega-list{
        list-style:none;
        margin:0;
        padding: 4px;
        max-height: calc(100vh - 180px);
        overflow:auto;
    }

    /* Scrollbar subtle */
    .dynamic-navbar .mega-list::-webkit-scrollbar{ width: 8px; height: 8px; }
    .dynamic-navbar .mega-list::-webkit-scrollbar-thumb{
        background: rgba(255,255,255,.20);
        border-radius: 10px;
    }
    .dynamic-navbar .mega-list::-webkit-scrollbar-track{
        background: rgba(0,0,0,.10);
        border-radius: 10px;
    }

    /* Items */
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

        /* nicer hover */
        transition: background-color .25s ease, transform .25s ease, outline-color .25s ease;
        will-change: transform;
    }

    .dynamic-navbar .dropdown-item:hover{
        background: rgba(255,255,255,0.10);
        outline-color: rgba(255,255,255,0.10);
        transform: translateX(2px);
    }

    /* Active/selected within a column */
    .dynamic-navbar .dropdown-item.is-active{
        background: rgba(255,255,255,0.13);
        outline: 1px solid rgba(255,255,255,0.16);
        position: relative;
    }

    /* small yellow indicator like reference site */
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

    /* Arrow indicator for items with children */
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
       OVERFLOW MODE -> OFFCANVAS
       ========================================================= */
    .dynamic-navbar.use-offcanvas .menu-row{ display:none; }
    .dynamic-navbar.use-offcanvas .menu-toggle{ display:flex; }

    @media (max-width: 991.98px){
        .menu-row{ display:none; }
        .menu-toggle{ display:flex; }
    }

    /* Offcanvas styling */
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
</style>

<!-- Navbar HTML -->
<div class="d-flex justify-content-center">
    <a href="/dashboard" class="w3-brand">
        <img style="width:auto;height:5rem;" id="logo" src="{{ asset('/assets/media/images/web/logo.png') }}" alt="MSIT Home Builder">
    </a>
</div>

<nav class="dynamic-navbar" id="dynamicNavbar">
    <div class="navbar-container">
        <div class="menu-row" id="menuRow">
            <ul class="navbar-nav" id="mainMenuContainer">
                <!-- Menu items will be loaded here -->
            </ul>
        </div>

        <!-- Hamburger (mobile + overflow fallback) -->
        <button class="menu-toggle" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#menuOffcanvas"
                aria-controls="menuOffcanvas" aria-label="Open menu">
            <span class="burger"><span></span></span>
        </button>
    </div>
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

            // maps for mega columns
            this.nodeById = new Map();        // id -> node
            this.childrenById = new Map();    // id -> children[]

            this.currentSlug = this.getCurrentSlug();
            this.activePathIds = [];
            this.activePathNodes = [];

            this.init();
        }

        init() {
            this.loadMenu();
            this.setupResizeListener();

            // If offcanvas is open and viewport becomes desktop, force-close it.
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 992) this.forceCloseOffcanvas();
            });
        }

        setupResizeListener() {
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    this.adjustMenuSizing();
                    this.toggleOverflowMode();
                    this.bindMegaGuards();
                }, 150);
            });
        }

        getCurrentSlug() {
            const path = window.location.pathname || '';
            if (path.startsWith('/page/')) return path.replace('/page/', '').replace(/^\/+/, '');
            return '';
        }

        async loadMenu() {
            try {
                const response = await fetch(`${this.apiBase}/tree`);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                const data = await response.json();

                if (data.success && data.data) {
                    this.menuData = data.data;

                    // Build maps for quick access
                    this.buildNodeMaps(this.menuData);

                    // Active path (for offcanvas expand + mega prefill)
                    this.activePathNodes = this.getActivePathNodes(this.menuData, this.currentSlug);
                    this.activePathIds = this.activePathNodes.map(n => n.id);

                    this.renderMenu();
                    this.renderOffcanvasMenu();

                    setTimeout(() => {
                        this.adjustMenuSizing();
                        this.toggleOverflowMode();
                        this.bindMegaGuards();
                        if (this.currentSlug) this.highlightActiveMenu();
                    }, 50);
                } else {
                    this.showError();
                }
            } catch (error) {
                console.error('Error loading menu:', error);
                this.showError();
            }
        }

        /* -------------------- Maps -------------------- */
        buildNodeMaps(items) {
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

        /* -------------------- Desktop sizing -------------------- */
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

        /* -------------------- Overflow -> offcanvas fallback -------------------- */
        toggleOverflowMode() {
            const nav = document.getElementById('dynamicNavbar');
            const row = document.getElementById('menuRow');
            const menu = document.getElementById('mainMenuContainer');
            if (!nav || !row || !menu) return;

            const isMobile = window.innerWidth < 992;
            let shouldOffcanvas = isMobile;

            if (!isMobile) {
                nav.classList.remove('use-offcanvas');
                const available = row.clientWidth || 0;
                const needed = menu.scrollWidth || 0;
                shouldOffcanvas = needed > (available + 6);
            }

            nav.classList.toggle('use-offcanvas', shouldOffcanvas);

            // if we are not in offcanvas mode anymore (desktop/full screen), hide the sidebar immediately
            if (!shouldOffcanvas) {
                this.forceCloseOffcanvas();
            }
        }

        forceCloseOffcanvas() {
            const ocEl = document.getElementById('menuOffcanvas');
            if (!ocEl) return;

            const inst = bootstrap.Offcanvas.getInstance(ocEl) || new bootstrap.Offcanvas(ocEl);
            try { inst.hide(); } catch(e){}

            // Hard cleanup for any leftover backdrop/scroll lock
            document.querySelectorAll('.offcanvas-backdrop').forEach(b => b.remove());
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            ocEl.classList.remove('show');
            ocEl.removeAttribute('style');
            ocEl.setAttribute('aria-hidden', 'true');
        }

        /* -------------------- Render top row -------------------- */
        renderMenu() {
            const container = document.getElementById('mainMenuContainer');
            container.innerHTML = '';

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

                // external links
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
        }

        /* -------------------- Mega dropdown builder -------------------- */

        // Compute stagger offset so child column starts aligned with hovered parent submenu item
        getAnchorTop(panel, anchorEl) {
            if (!panel || !anchorEl) return 0;

            const panelRect = panel.getBoundingClientRect();
            const aRect = anchorEl.getBoundingClientRect();

            // relative Y inside panel
            let top = (aRect.top - panelRect.top);

            // small adjustment so the block aligns nicely with item padding
            top = Math.max(0, top - 4);

            // clamp so it doesn't go too far down
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

            // Column 0
            this.renderMegaColumn(panel, 0, children, 0);

            // Prefill mega columns if current slug is inside this subtree
            if (activeNodesFromHere && activeNodesFromHere.length) {
                this.prefillMega(panel, children, activeNodesFromHere);
            }

            parentLi.appendChild(dropdown);

            // Hover delegation (desktop)
            dropdown.addEventListener('mousemove', (e) => {
                if (window.innerWidth < 992) return;
                const link = e.target.closest('a.dropdown-item[data-mid]');
                if (!link) return;

                const col = parseInt(link.dataset.col || '0', 10);
                const id = parseInt(link.dataset.mid || '0', 10);
                if (!id) return;

                // set active in this column
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
            // ensure column exists
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

            // apply stagger alignment (only for deeper columns)
            col.style.marginTop = (colIndex > 0 && alignTopPx > 0) ? `${alignTopPx}px` : '0px';

            // wipe deeper columns
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

                // meta
                a.dataset.mid = String(item.id);
                a.dataset.col = String(colIndex);

                const hasChildren = item.children && item.children.length > 0;
                if (hasChildren) a.classList.add('has-children');

                // external links
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
            // Column 0 already contains rootChildren
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

        /* Keep mega dropdown in viewport */
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

            // reset
            menu.style.left = '0';
            menu.style.right = 'auto';

            const pad = 10;
            const rect = menu.getBoundingClientRect();

            // If right overflows, align dropdown to right edge of parent
            if (rect.right > (window.innerWidth - pad)) {
                menu.style.left = 'auto';
                menu.style.right = '0';
            }
        }

        /* -------------------- URL + Active highlight -------------------- */
        getMenuItemUrl(item) {
            if (item.page_url && item.page_url.trim() !== '') {
                return item.page_url.startsWith('http') ? item.page_url : `{{ url('') }}${item.page_url}`;
            } else if (item.page_slug && item.page_slug.trim() !== '') {
                return `{{ url('/page') }}/${item.page_slug}`;
            } else if (item.slug) {
                return `{{ url('/page') }}/${item.slug}`;
            }
            return '#';
        }

        highlightActiveMenu() {
            document.querySelectorAll('.nav-link.active, .dropdown-item.active').forEach(link => link.classList.remove('active'));

            if (this.activePathNodes && this.activePathNodes.length) {
                const top = this.activePathNodes[0];
                if (top) {
                    const topLink = document.querySelector(`[data-id="${top.id}"] > a.nav-link`);
                    if (topLink) topLink.classList.add('active');
                }
            }

            if (window.location.pathname === '/' || window.location.pathname === '') {
                document.querySelectorAll('[data-slug="home"] > a').forEach(a => a.classList.add('active'));
            }
        }

        showError() {
            const container = document.getElementById('mainMenuContainer');
            if (container) container.innerHTML = '';
            const off = document.getElementById('offcanvasMenuList');
            if (off) off.innerHTML = '';
        }

        refresh() {
            this.loadMenu();
        }

        /* -------------------- Offcanvas rendering -------------------- */
        renderOffcanvasMenu() {
            const root = document.getElementById('offcanvasMenuList');
            if (!root) return;

            root.innerHTML = '';
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

            // External links
            if (item.page_url && item.page_url.startsWith('http')) {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    window.open(item.page_url, '_blank');
                    this.forceCloseOffcanvas();
                });
            } else {
                // FIX: make sidebar link actually navigate
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