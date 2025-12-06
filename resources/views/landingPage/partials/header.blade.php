{{-- resources/views/landing/modules/header.blade.php --}}
@push('styles')
<style>
    /* ===== Wrapper ===== */
    .msit-header{
        font-family: var(--font-sans);
        color: var(--text-color);
        background: var(--bg-body);
    }

    /* ===== Top info bar ===== */
    .msit-topbar{
        background: var(--secondary-color);
        color:#fff;
        font-size: var(--fs-13);
        padding:4px 0;
    }
    .msit-topbar .top-left span{
        display:inline-flex;
        align-items:center;
        gap:6px;
        margin-right:16px;
        white-space:nowrap;
    }
    .msit-topbar i{ font-size:11px; }
    .msit-topbar .top-right a{
        color:#fee2e2;
        font-size: var(--fs-12);
        text-decoration:none;
        margin-left:10px;
    }
    .msit-topbar .top-right a:hover{
        color:#fff;
        text-decoration:underline;
    }

    /* ===== Brand bar (logo + college name) ===== */
    .msit-brandbar{
        background:#ffffff;
        border-bottom:1px solid var(--line-strong);
        padding:12px 0;
    }
    .msit-logo-wrap{
        display:flex;
        align-items:center;
    }
    .msit-logo{
        height:78px;
        width:auto;
    }
    .msit-brand-text h1{
        font-family: var(--font-head);
        font-size:1.7rem;
        text-transform:uppercase;
        margin:0 0 2px;
        color:var(--ink);
    }
    .msit-brand-text p{
        margin:0;
        font-size:.9rem;
        color:var(--muted-color);
    }
    @media (max-width: 767.98px){
        .msit-logo{ height:54px; }
        .msit-brand-text h1{ font-size:1.2rem; }
        .msit-brand-text p{ font-size:.8rem; }
    }

    /* Right side logos area (optional) */
    .msit-brand-right{
        display:flex;
        align-items:center;
        gap:10px;
    }
    .msit-brand-banner{
        height:52px;
        width:auto;
        border-radius:10px;
        object-fit:contain;
    }

    /* ===== Main nav (desktop) ===== */
    .msit-mainnav{
        background: var(--primary-color);
        color:#fff;
    }
    .msit-mainnav .container{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        position:relative;
    }

    .msit-menu{
        list-style:none;
        margin:0;
        padding:0;
        display:flex;
        align-items:center;
        gap:4px;
    }
    .msit-menu-item{
        position:relative;
    }
    .msit-menu-link{
        display:block;
        padding:9px 14px;
        color:#fff;
        font-weight:500;
        font-size: var(--fs-14);
        text-decoration:none;
        border-bottom:3px solid transparent;
        white-space:nowrap;
    }
    .msit-menu-link:hover,
    .msit-menu-link.active{
        background:rgba(0,0,0,.08);
        border-bottom-color:#facc15; /* yellow underline like live site */
    }

    /* Submenu */
    .msit-submenu{
        display:none;
        position:absolute;
        left:0;
        top:100%;
        min-width:230px;
        background: var(--secondary-color);
        list-style:none;
        margin:0;
        padding:4px 0;
        z-index:1030;
        box-shadow:var(--shadow-2);
    }
    .msit-menu-item:hover > .msit-submenu{
        display:block;
    }
    .msit-submenu-item{}
    .msit-submenu-link{
        display:block;
        padding:8px 14px;
        color:#fff;
        font-size:var(--fs-14);
        text-decoration:none;
        white-space:nowrap;
    }
    .msit-submenu-link:hover{
        background:rgba(0,0,0,.18);
    }

    /* ===== Mobile trigger (hamburger) ===== */
    .msit-mobile-hamburger{
        border:none;
        background:transparent;
        padding:7px 0;
        display:flex;
        flex-direction:column;
        gap:4px;
    }
    .msit-mobile-hamburger span{
        width:22px;
        height:2px;
        background:#fff;
        border-radius:999px;
    }
    @media (min-width: 992px){
        .msit-mobile-hamburger{ display:none; }
    }
    @media (max-width: 991.98px){
        .msit-menu{
            display:none; /* hide desktop menu on mobile */
        }
    }

    /* ===== Mobile slide-in menu ===== */
    .msit-mobile-nav{
        position:fixed;
        inset:0 auto 0 0;
        width:280px;
        max-width:80vw;
        background:#ffffff;
        transform:translateX(-100%);
        transition:transform .22s ease;
        z-index:1060;
        box-shadow:var(--shadow-2);
        display:flex;
        flex-direction:column;
    }
    .msit-mobile-nav.show{
        transform:translateX(0);
    }
    .msit-mobile-head{
        display:flex;
        align-items:center;
        justify-content:space-between;
        padding:10px 14px;
        border-bottom:1px solid var(--line-strong);
    }
    .msit-mobile-logo{
        height:42px;
        width:auto;
    }
    .msit-mobile-title{
        font-family:var(--font-head);
        font-weight:600;
        font-size:var(--fs-15);
    }
    .msit-mobile-close{
        border:none;
        background:transparent;
        padding:4px 6px;
        font-size:18px;
        color:var(--muted-color);
    }
    .msit-mobile-body{
        padding:8px 14px 16px;
        overflow-y:auto;
        flex:1 1 auto;
    }

    .msit-mobile-item{
        border-bottom:1px solid var(--line-soft);
        padding:7px 0;
    }
    .msit-mobile-row{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:8px;
    }
    .msit-mobile-link{
        color:var(--text-color);
        font-weight:500;
        font-size:var(--fs-14);
        text-decoration:none;
    }
    .msit-mobile-toggle-btn{
        border:none;
        background:transparent;
        padding:4px;
        color:var(--muted-color);
        font-size:12px;
    }
    .msit-mobile-children{
        display:none;
        padding-top:4px;
        padding-left:10px;
    }
    .msit-mobile-item.open .msit-mobile-children{
        display:block;
    }
    .msit-mobile-child-link{
        display:block;
        padding:3px 0;
        font-size:var(--fs-13);
        color:var(--muted-color);
        text-decoration:none;
    }
    .msit-mobile-child-link:hover{
        color:var(--accent-color);
    }

    .msit-mobile-overlay{
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.32);
        opacity:0;
        visibility:hidden;
        transition:opacity .22s ease;
        z-index:1055;
    }
    .msit-mobile-overlay.show{
        opacity:1;
        visibility:visible;
    }
    body.mobile-nav-open{
        overflow:hidden;
    }
</style>
@endpush

<header class="msit-header">
    {{-- ===== Top contact bar ===== --}}
    <div class="msit-topbar">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="top-left d-flex flex-wrap align-items-center">
                <span><i class="fa-solid fa-phone"></i> +91-XXXXXXXXXX</span>
                <span><i class="fa-solid fa-envelope"></i> info@msit.edu.in</span>
                <span><i class="fa-solid fa-location-dot"></i> Kolkata, West Bengal</span>
            </div>
            <div class="top-right d-none d-md-block">
                {{-- Small quick links strip like live site (static for now) --}}
                <a href="#">IQAC</a>
                <a href="#">R&amp;D</a>
                <a href="#">NBA</a>
                <a href="#">MOOCs</a>
                <a href="#">ARIIA</a>
                <a href="#">IIC</a>
                <a href="#">Anti Ragging</a>
            </div>
        </div>
    </div>

    {{-- ===== Brand bar ===== --}}
    <div class="msit-brandbar">
        <div class="container d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ url('/') }}" class="msit-logo-wrap">
                    {{-- change logo path as per your project --}}
                    <img src="{{ asset('assets/media/images/msit-logo.png') }}" alt="MSIT Logo" class="msit-logo">
                </a>
                <div class="msit-brand-text">
                    <h1>Meghnad Saha Institute of Technology</h1>
                    <p>Recognized for Excellence in Technical Education and Research</p>
                </div>
            </div>

            <div class="msit-brand-right d-none d-lg-flex">
                {{-- Optional: partner logos / admission banner --}}
                {{-- <img src="{{ asset('assets/media/images/admission-banner.png') }}" class="msit-brand-banner" alt="Admission 2025"> --}}
            </div>
        </div>
    </div>

    {{-- ===== Main navigation bar ===== --}}
    <nav class="msit-mainnav">
        <div class="container">
            {{-- Desktop menu --}}
            <ul class="msit-menu" id="msitMainMenu">
                {{-- Filled by JS; has static fallback in script --}}
            </ul>

            {{-- Mobile hamburger --}}
            <button type="button" class="msit-mobile-hamburger d-lg-none" id="msitMobileToggle" aria-label="Toggle navigation">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>

    {{-- ===== Mobile slide-in navigation ===== --}}
    <div class="msit-mobile-nav" id="msitMobileNav">
        <div class="msit-mobile-head">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ asset('assets/media/images/msit-logo.png') }}" alt="MSIT Logo" class="msit-mobile-logo">
                <span class="msit-mobile-title">MSIT</span>
            </div>
            <button type="button" class="msit-mobile-close" id="msitMobileClose">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="msit-mobile-body" id="msitMobileMenu">
            {{-- Filled by JS --}}
        </div>
    </div>
    <div class="msit-mobile-overlay" id="msitMobileOverlay"></div>
</header>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mainMenu       = document.getElementById('msitMainMenu');
    const mobileMenu     = document.getElementById('msitMobileMenu');
    const mobileNav      = document.getElementById('msitMobileNav');
    const mobileOverlay  = document.getElementById('msitMobileOverlay');
    const mobileToggle   = document.getElementById('msitMobileToggle');
    const mobileCloseBtn = document.getElementById('msitMobileClose');

    /* ---------- helpers ---------- */

    function computeHref(item){
        const pageUrl  = item.page_url  || '';
        const pageSlug = item.page_slug || '';
        const slug     = item.slug      || '';

        if (pageUrl.trim() !== '') {
            return pageUrl;                                       // explicit URL
        }
        if (pageSlug.trim() !== '') {
            return '/page/' + pageSlug.replace(/^\/+/, '');       // /page/{page_slug}
        }
        if (slug.trim() !== '') {
            return '/page/' + slug.replace(/^\/+/, '');                // /{slug}
        }
        return '#';
    }

    function isInternal(href){
        return href.startsWith('/') && !href.startsWith('//');
    }

    function setActiveClass(link){
        const href = link.getAttribute('href') || '';
        if (!isInternal(href)) return;

        const current = window.location.pathname.replace(/\/+$/,'');
        const target  = href.replace(/\/+$/,'');
        if (current === target) {
            link.classList.add('active');
        }
    }

    /* ---------- Desktop menu build ---------- */
    function buildDesktopMenu(tree){
        mainMenu.innerHTML = '';

        if (!Array.isArray(tree) || !tree.length){
            // Fallback static items if API fails
            const fallback = [
                { title:'Home',       slug:'' },
                { title:'Institute',  slug:'institute' },
                { title:'Academic',   slug:'academic' },
                { title:'Department', slug:'department' },
                { title:'Admission',  slug:'admission' },
                { title:'Library',    slug:'library' },
                { title:'Placement',  slug:'placement' },
                { title:"Student's Corner", slug:'students-corner' },
                { title:'Notice',     slug:'notice' },
                { title:'Alumni',     slug:'alumni' },
                { title:'Gallery',    slug:'gallery' },
                { title:'NIRF',       slug:'nirf' },
                { title:'AICTE',      slug:'aicte' },
                { title:'Careers',    slug:'careers' },
                { title:'Contact Us', slug:'contact' },
            ];

            fallback.forEach(item=>{
                const li = document.createElement('li');
                li.className = 'msit-menu-item';
                const a  = document.createElement('a');
                a.className = 'msit-menu-link';
                a.textContent = item.title;
                a.href = item.slug ? ('/'+item.slug) : '/';
                li.appendChild(a);
                setActiveClass(a);
                mainMenu.appendChild(li);
            });
            return;
        }

        tree.forEach(parent=>{
            const li = document.createElement('li');
            li.className = 'msit-menu-item';

            const a = document.createElement('a');
            a.className = 'msit-menu-link';
            a.textContent = parent.title || '-';
            a.href = computeHref(parent);
            li.appendChild(a);

            const hasChildren = Array.isArray(parent.children) && parent.children.length > 0;
            if (hasChildren){
                const ul = document.createElement('ul');
                ul.className = 'msit-submenu';

                parent.children.forEach(child=>{
                    const cli = document.createElement('li');
                    cli.className = 'msit-submenu-item';

                    const ca = document.createElement('a');
                    ca.className = 'msit-submenu-link';
                    ca.textContent = child.title || '-';
                    ca.href = computeHref(child);

                    cli.appendChild(ca);
                    ul.appendChild(cli);

                    setActiveClass(ca);
                });

                li.appendChild(ul);
            }

            setActiveClass(a);
            mainMenu.appendChild(li);
        });
    }

    /* ---------- Mobile menu build ---------- */
    function buildMobileMenu(tree){
        mobileMenu.innerHTML = '';

        if (!Array.isArray(tree) || !tree.length){
            // If we have no tree but desktop used fallback, we can ignore mobile,
            // the mobile will still work fine because user can scroll and see content.
            return;
        }

        tree.forEach(parent=>{
            const item = document.createElement('div');
            item.className = 'msit-mobile-item';

            const row = document.createElement('div');
            row.className = 'msit-mobile-row';

            const link = document.createElement('a');
            link.className = 'msit-mobile-link';
            link.textContent = parent.title || '-';
            link.href = computeHref(parent);

            row.appendChild(link);

            const hasChildren = Array.isArray(parent.children) && parent.children.length > 0;
            let toggleBtn = null;

            if (hasChildren){
                toggleBtn = document.createElement('button');
                toggleBtn.type = 'button';
                toggleBtn.className = 'msit-mobile-toggle-btn';
                toggleBtn.innerHTML = '<i class="fa-solid fa-chevron-down"></i>';
                row.appendChild(toggleBtn);
            }

            item.appendChild(row);

            if (hasChildren){
                const box = document.createElement('div');
                box.className = 'msit-mobile-children';

                parent.children.forEach(child=>{
                    const ca = document.createElement('a');
                    ca.className = 'msit-mobile-child-link';
                    ca.textContent = child.title || '-';
                    ca.href = computeHref(child);
                    box.appendChild(ca);
                });

                item.appendChild(box);

                toggleBtn.addEventListener('click', function(){
                    const open = item.classList.toggle('open');
                    this.querySelector('i').style.transform = open ? 'rotate(180deg)' : '';
                });
            }

            mobileMenu.appendChild(item);
        });
    }

    /* ---------- Mobile open/close ---------- */
    function openMobile(){
        mobileNav.classList.add('show');
        mobileOverlay.classList.add('show');
        document.body.classList.add('mobile-nav-open');
    }
    function closeMobile(){
        mobileNav.classList.remove('show');
        mobileOverlay.classList.remove('show');
        document.body.classList.remove('mobile-nav-open');
    }

    mobileToggle?.addEventListener('click', openMobile);
    mobileCloseBtn?.addEventListener('click', closeMobile);
    mobileOverlay?.addEventListener('click', closeMobile);

    mobileMenu.addEventListener('click', function(e){
        if (e.target.tagName === 'A'){
            closeMobile();
        }
    });

    /* ---------- Fetch menu tree (API) ---------- */
    const headers = { 'Accept': 'application/json' };
    const token = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
    if (token){
        headers['Authorization'] = 'Bearer ' + token; // works with your checkRole middleware
    }

    fetch('/api/header-menus/tree?only_active=1', { headers })
        .then(r => {
            if (!r.ok) throw r;
            return r.json();
        })
        .then(json => {
            const tree = Array.isArray(json.data) ? json.data : [];
            buildDesktopMenu(tree);
            buildMobileMenu(tree);
        })
        .catch(err => {
            console.error('Failed to load header menu tree', err);
            // build only fallback desktop menu
            buildDesktopMenu([]);
        });
});
</script>
@endpush
