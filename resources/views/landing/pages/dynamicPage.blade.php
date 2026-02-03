{{-- resources/views/test.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dynamic Page')</title>

    {{-- Bootstrap + Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>

    {{-- Common UI --}}
    <link rel="stylesheet" href="{{ asset('assets/css/common/main.css') }}">

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .page-content { padding: 2rem 0; min-height: 70vh; }

        /* ===== Sidebar (hallienz-ish) ===== */
        .hallienz-side{border-radius: 18px;overflow: hidden;background: var(--surface, #fff);border: 1px solid var(--line-strong, #e6c8ca);box-shadow: var(--shadow-2, 0 8px 22px rgba(0,0,0,.08));}
        .hallienz-side__head{background: var(--primary-color, #9E363A);color: #fff;font-weight: 700;padding: 14px 16px;font-size: 20px;letter-spacing: .2px;}
        .hallienz-side__list{margin: 0;padding: 6px 0 0;list-style: none;border-bottom: 0.5rem solid #9E363A;}
        .hallienz-side__item{ position: relative; }

        .hallienz-side__row{ display:flex; align-items:stretch; }

        .hallienz-side__link{flex: 1 1 auto;display: flex;align-items: center;gap: 12px;padding: 10px 14px;text-decoration: none;color: #0b5ed7;border-bottom: 1px dotted rgba(0,0,0,.18);transition: background .25s ease, color .25s ease;min-width: 0;}
        .hallienz-side__link:hover{background: rgba(158, 54, 58, .06);color: var(--primary-color, #9E363A);}
        .hallienz-side__link.active{background: rgba(158, 54, 58, .10);color: var(--primary-color, #9E363A);font-weight: 700;}
        .hallienz-side__text{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}

        .hallienz-side__toggle{flex: 0 0 auto;width: 44px;display:inline-flex;align-items:center;justify-content:center;border: none;background: transparent;color: rgba(0,0,0,.55);border-bottom: 1px dotted rgba(0,0,0,.18);transition: background .25s ease, color .25s ease, transform .25s ease;cursor:pointer;}
        .hallienz-side__toggle:hover{background: rgba(158, 54, 58, .06);color: var(--primary-color, #9E363A);}
        .hallienz-side__toggle i{ transition: transform .22s ease; }
        .hallienz-side__item.open > .hallienz-side__row .hallienz-side__toggle i{ transform: rotate(90deg); }

        .hallienz-side__children{list-style:none;margin: 0;padding: 0;display:none;border-bottom: 1px dotted rgba(0,0,0,.18);background: rgba(158, 54, 58, .03);}
        .hallienz-side__item.open > .hallienz-side__children{ display:block; }

        .hallienz-side__children .hallienz-side__link{border-bottom: 1px dotted rgba(0,0,0,.14);font-size: 14px;}
        .hallienz-side__children .hallienz-side__toggle{border-bottom: 1px dotted rgba(0,0,0,.14);}

        @media (hover:hover) and (pointer:fine){
            .hallienz-side__item:hover .hallienz-side__children{display:block;}
            .hallienz-side__item:hover > .hallienz-side__row .hallienz-side__toggle i{transform: rotate(90deg);}
        }

        /* ===== Content Card ===== */
        .dp-card{border-radius: 18px;background: var(--surface, #fff);border: 1px solid var(--line-strong, #e6c8ca);box-shadow: var(--shadow-2, 0 8px 22px rgba(0,0,0,.08));padding: 18px;}
        .dp-title{font-weight: 800;margin: 0 0 12px;color: var(--ink, #111);text-align: center;}
        .dp-muted{ color: var(--muted-color, #6b7280); font-size: 13px; margin-bottom: 12px; }
        .dp-loading{ padding: 28px 0; text-align: center; color: var(--muted-color, #6b7280); }
        .dp-iframe{border:1px solid rgba(0,0,0,.1);border-radius:12px;overflow:hidden;}

        :root{ --dp-sticky-top: 16px; }

        @media (min-width: 992px){
            .dp-sticky{position: sticky;top: var(--dp-sticky-top, 16px);z-index: 2;}
        }

        @media (max-width: 991.98px){
            #sidebarCol.dp-side-preload{ display:none !important; }
        }

        .dp-skel-wrap{ padding: 12px 12px 14px; border-bottom: 0.5rem solid #9E363A; background: rgba(158, 54, 58, .02); }
        .dp-skel-stack{ display:grid; gap: 10px; }
        .dp-skel-line{position: relative;height: 14px;border-radius: 12px;overflow: hidden;background: rgba(0,0,0,.08);}
        .dp-skel-line.sm{ height: 12px; }
        .dp-skel-line.lg{ height: 18px; }
        .dp-skel-line::after{content:"";position:absolute;inset:0;transform: translateX(-120%);background: linear-gradient(90deg, transparent, rgba(255,255,255,.65), transparent);animation: dpShimmer 1.15s infinite;}

        @keyframes dpShimmer{
            0%{ transform: translateX(-120%); }
            100%{ transform: translateX(120%); }
        }

        @media (prefers-reduced-motion: reduce){
            .dp-skel-line::after{ animation: none; }
        }

        html.theme-dark .dp-skel-wrap{ background: rgba(255,255,255,.03); }
        html.theme-dark .dp-skel-line{ background: rgba(255,255,255,.10); }
        html.theme-dark .dp-skel-line::after{background: linear-gradient(90deg, transparent, rgba(255,255,255,.18), transparent);}
    </style>
</head>
<body>

{{-- Top Header --}}
@include('landing.components.topHeaderMenu')

{{-- Main Header --}}
@include('landing.components.header')

{{-- Header --}}
@include('landing.components.headerMenu')

<main class="page-content">
    <div class="container">
        <div class="row g-4 align-items-start" id="dpRow">
            {{-- Sidebar --}}
            {{-- ✅ Reserve space on desktop to avoid full-width → shrink.
                Starts in "preload" mode (skeleton), then:
                - if sidebar exists → render tree & show
                - if no sidebar → hide column & expand content --}}
            <aside class="col-12 col-lg-3 dp-side-preload" id="sidebarCol" aria-label="Page Sidebar">
                <div class="hallienz-side" id="sidebarCard">
                    <div class="hallienz-side__head" id="sidebarHeading">Menu</div>

                    {{-- ✅ Real list (hidden until loaded) --}}
                    <ul class="hallienz-side__list d-none" id="submenuList"></ul>

                    {{-- ✅ Skeleton list while page submenus are loading --}}
                    <div id="submenuSkeleton" class="dp-skel-wrap" aria-hidden="true">
                        <div class="dp-skel-stack">
                            <div class="dp-skel-line lg" style="width:72%;"></div>
                            <div class="dp-skel-line" style="width:92%;"></div>
                            <div class="dp-skel-line" style="width:84%;"></div>
                            <div class="dp-skel-line" style="width:88%;"></div>
                            <div class="dp-skel-line" style="width:78%;"></div>
                            <div class="dp-skel-line sm" style="width:60%;"></div>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Content --}}
            {{-- ✅ Start as col-lg-9 so width doesn't jump on desktop --}}
            <section class="col-12 col-lg-9" id="contentCol">
                <div class="dp-card" id="contentCard">
                    <div class="dp-loading" id="pageLoading">
                        <div class="spinner-border" role="status" aria-label="Loading"></div>
                        <div class="mt-2" id="loadingText">Loading page…</div>
                    </div>

                    <div id="pageError" class="alert alert-danger d-none mb-0"></div>

                    <div id="pageNotFoundWrap" class="d-none">
                        @include('partials.pageNotFound')
                    </div>

                    <div id="pageWrap" class="d-none">
                        <div class="dp-muted d-none" id="pageMeta"></div>

                        <h1 class="dp-title" id="pageTitle">Dynamic Page</h1>
                        <div id="pageHtml"></div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>

{{-- Footer --}}
@include('landing.components.footer')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@php
  $apiBase = rtrim(url('/api'), '/');
@endphp

<script>
(function(){
    const API_BASE  = @json($apiBase);
    const SITE_BASE = @json(url('/'));

    // ------------------------------------------------------------------
    // ✅ auth cache + global API auth injection
    // ------------------------------------------------------------------
    const TOKEN = (localStorage.getItem('token') || sessionStorage.getItem('token') || '');
    const ROLE  = (sessionStorage.getItem('role') || localStorage.getItem('role') || '');

    window.__AUTH_CACHE__ = window.__AUTH_CACHE__ || { token: TOKEN, role: ROLE };

    (function patchFetch(){
        const origFetch = window.fetch;
        window.fetch = async function(input, init = {}){
            try{
                const url = (typeof input === 'string') ? input : (input?.url || '');
                const isApi = String(url).includes('/api/');
                if (isApi && TOKEN){
                    init.headers = init.headers || {};
                    if (init.headers instanceof Headers){
                        if (!init.headers.get('Authorization')) init.headers.set('Authorization', 'Bearer ' + TOKEN);
                        if (!init.headers.get('Accept')) init.headers.set('Accept', 'application/json');
                    } else {
                        if (!init.headers.Authorization) init.headers.Authorization = 'Bearer ' + TOKEN;
                        if (!init.headers.Accept) init.headers.Accept = 'application/json';
                    }
                }
            } catch(e){}
            return await origFetch(input, init);
        };
    })();

    (function patchXHR(){
        const origOpen = XMLHttpRequest.prototype.open;
        const origSend = XMLHttpRequest.prototype.send;

        XMLHttpRequest.prototype.open = function(method, url){
            this.__dp_url = url;
            return origOpen.apply(this, arguments);
        };

        XMLHttpRequest.prototype.send = function(){
            try{
                const url = String(this.__dp_url || '');
                const isApi = url.includes('/api/');
                if (isApi && TOKEN){
                    try { this.setRequestHeader('Authorization', 'Bearer ' + TOKEN); } catch(e){}
                    try { this.setRequestHeader('Accept', 'application/json'); } catch(e){}
                }
            } catch(e){}
            return origSend.apply(this, arguments);
        };
    })();

    // ------------------------------------------------------------------
    // ✅ read header_menu_id from URL query (?header_menu_id=1)
    // ------------------------------------------------------------------
    function readHeaderMenuIdFromUrl(){
        try{
            const qs = new URLSearchParams(window.location.search || '');
            const v =
                qs.get('header_menu_id') ||
                qs.get('menu_id') ||
                qs.get('headerMenuId') ||
                qs.get('header_menu') ||
                qs.get('headerMenu');
            return parseInt(v || '0', 10) || 0;
        }catch(e){
            return 0;
        }
    }

    // ------------------------------------------------------------------
    // DOM refs
    // ------------------------------------------------------------------
    const elLoading   = document.getElementById('pageLoading');
    const elError     = document.getElementById('pageError');
    const elNotFound  = document.getElementById('pageNotFoundWrap');
    const elWrap      = document.getElementById('pageWrap');
    const elTitle     = document.getElementById('pageTitle');
    const elMeta      = document.getElementById('pageMeta');
    const elHtml      = document.getElementById('pageHtml');
    const elLoadingText = document.getElementById('loadingText');

    const sidebarCol  = document.getElementById('sidebarCol');
    const contentCol  = document.getElementById('contentCol');
    const submenuList = document.getElementById('submenuList');
    const sidebarHead = document.getElementById('sidebarHeading');
    const submenuSkeleton = document.getElementById('submenuSkeleton');

    const sidebarCard = document.getElementById('sidebarCard') || (sidebarCol ? sidebarCol.querySelector('.hallienz-side') : null);
    const contentCard = document.getElementById('contentCard') || (contentCol ? contentCol.querySelector('.dp-card') : null);

    /* =========================================================
       ✅ Skeleton helpers (only affects sidebar preload visuals)
    ========================================================= */
    function showSidebarSkeleton(){
        try{
            if (submenuSkeleton) submenuSkeleton.classList.remove('d-none');
            if (submenuList) submenuList.classList.add('d-none');
        }catch(e){}
    }

    function hideSidebarSkeleton(){
        try{
            if (submenuSkeleton) submenuSkeleton.classList.add('d-none');
            if (submenuList) submenuList.classList.remove('d-none');
        }catch(e){}
    }

    function resetSidebarPreloadState(){
        // reserve desktop space immediately so content doesn't jump
        try{
            if (sidebarCol){
                sidebarCol.classList.remove('d-none');
                sidebarCol.classList.add('dp-side-preload');
            }
            if (contentCol){
                contentCol.className = 'col-12 col-lg-9';
            }
            showSidebarSkeleton();
        }catch(e){}
    }

    function setMeta(text){
        const t = String(text || '').trim();
        if (!t){
            elMeta.textContent = '';
            elMeta.classList.add('d-none');
            return;
        }
        elMeta.textContent = t;
        elMeta.classList.remove('d-none');
    }

    function showLoading(msg){
        elLoadingText.textContent = msg || 'Loading…';
        elError.classList.add('d-none'); elError.textContent = '';
        elWrap.classList.add('d-none');
        if (elNotFound) elNotFound.classList.add('d-none');
        elLoading.classList.remove('d-none');
    }

    function showError(msg){
        elError.textContent = msg;
        elError.classList.remove('d-none');
        elLoading.classList.add('d-none');
        elWrap.classList.add('d-none');
        if (elNotFound) elNotFound.classList.add('d-none');
    }

    function showNotFound(slug){
        try{
            const slot = document.querySelector('[data-dp-notfound-slug]');
            if (slot) slot.textContent = slug || '';
        } catch(e){}

        elError.classList.add('d-none'); elError.textContent = '';
        elLoading.classList.add('d-none');
        elWrap.classList.add('d-none');
        if (elNotFound) elNotFound.classList.remove('d-none');
    }

    function hideError(){
        elError.classList.add('d-none');
        elError.textContent = '';
    }

    function withTimeout(ms){
        const ctrl = new AbortController();
        const id = setTimeout(() => ctrl.abort(new Error('timeout')), ms);
        return { ctrl, cancel: () => clearTimeout(id) };
    }

    async function fetchJsonWithStatus(url){
        const t = withTimeout(20000);
        try{
            const res = await fetch(url, {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
                signal: t.ctrl.signal
            });

            let data = null;
            try { data = await res.json(); } catch(e) {}

            return { ok: res.ok, status: res.status, data };
        } catch(e){
            return { ok:false, status: 0, data: { error: e?.message || 'Network error' } };
        } finally { t.cancel(); }
    }

    function cleanPathSegments(){
        return window.location.pathname.replace(/^\/+|\/+$/g,'').split('/').filter(Boolean);
    }

    function stripSubmenuFromPath(pathname){
        return String(pathname || '').replace(/&submenu=[^\/?#]*/g, '');
    }
    function readSubmenuFromPathname(){
        const p = String(window.location.pathname || '');
        const m = p.match(/&submenu=([^\/?#]+)/);
        return m ? decodeURIComponent(m[1]) : '';
    }

    function getSlugCandidate(){
        const qs = new URLSearchParams(window.location.search);
        const qSlug = qs.get('slug') || qs.get('page_slug') || qs.get('selfslug') || qs.get('shortcode');
        if (qSlug && String(qSlug).trim()) return String(qSlug).trim();

        const segs = cleanPathSegments();
        const strip = (s) => String(s || '').split('&submenu=')[0];

        const idx = segs.findIndex(x => String(x || '').toLowerCase() === 'page');
        if (idx !== -1 && segs[idx + 1]) return strip(segs[idx + 1]);

        const last = strip(segs[segs.length - 1] || '');
        return last || '';
    }

    function pick(obj, keys){
        for (const k of keys){
            if (obj && obj[k] !== undefined && obj[k] !== null) return obj[k];
        }
        return null;
    }

    function toLowerSafe(v){
        return String(v ?? '').toLowerCase().trim();
    }

    function safeCssEscape(s){
        try { return CSS.escape(s); } catch(e){ return String(s).replace(/["\\]/g, '\\$&'); }
    }

    // ------------------------------------------------------------------
    // ✅ Smart Sticky Columns
    // ------------------------------------------------------------------
    let __dpStickyRaf = 0;
    let __dpStickyRO  = null;

    function dpDebounce(fn, ms){
        let t = null;
        return function(){
            const args = arguments;
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), ms);
        };
    }

    function isDesktopSticky(){
        try { return window.matchMedia('(min-width: 992px)').matches; } catch(e){ return window.innerWidth >= 992; }
    }

    function resetStickyMode(){
        if (sidebarCard) sidebarCard.classList.remove('dp-sticky');
        if (contentCard) contentCard.classList.remove('dp-sticky');

        if (sidebarCol) sidebarCol.style.minHeight = '';
        if (contentCol) contentCol.style.minHeight = '';
    }

    function computeStickyTop(){
        let top = 16;

        try{
            const nodes = Array.from(document.querySelectorAll('.fixed-top, .sticky-top, header, nav'));
            const used = new Set();
            let sum = 0;

            nodes.forEach((el) => {
                if (!el || used.has(el)) return;

                const st = window.getComputedStyle(el);
                const pos = (st.position || '').toLowerCase();
                if (pos !== 'fixed' && pos !== 'sticky') return;

                const topVal = parseFloat(st.top || '0');
                if (isNaN(topVal) || topVal > 2) return;

                const h = Math.max(0, el.getBoundingClientRect().height || 0);
                if (h > 0 && h < 220) sum += h;

                used.add(el);
            });

            top += Math.min(sum, 220);
        }catch(e){}

        document.documentElement.style.setProperty('--dp-sticky-top', top + 'px');
    }

    function updateStickyMode(){
        if (!isDesktopSticky()){
            resetStickyMode();
            return;
        }
        if (!sidebarCol || sidebarCol.classList.contains('d-none') || !sidebarCard || !contentCard){
            resetStickyMode();
            return;
        }

        computeStickyTop();

        sidebarCol.style.minHeight = '';
        contentCol.style.minHeight = '';

        const sH = Math.ceil(sidebarCard.getBoundingClientRect().height || 0);
        const cH = Math.ceil(contentCard.getBoundingClientRect().height || 0);

        resetStickyMode();

        const THRESH = 40;
        if (!sH || !cH || Math.abs(sH - cH) < THRESH) return;

        if (sH > cH){
            contentCol.style.minHeight = sH + 'px';
            contentCard.classList.add('dp-sticky');
        } else {
            sidebarCol.style.minHeight = cH + 'px';
            sidebarCard.classList.add('dp-sticky');
        }
    }

    function scheduleStickyUpdate(){
        cancelAnimationFrame(__dpStickyRaf);
        __dpStickyRaf = requestAnimationFrame(updateStickyMode);
    }

    function setupStickyObservers(){
        if (__dpStickyRO) return;
        if (!('ResizeObserver' in window)) return;

        __dpStickyRO = new ResizeObserver(() => scheduleStickyUpdate());
        try{
            if (sidebarCard) __dpStickyRO.observe(sidebarCard);
            if (contentCard) __dpStickyRO.observe(contentCard);
        }catch(e){}
    }

    window.addEventListener('resize', dpDebounce(scheduleStickyUpdate, 120));
    window.addEventListener('load', () => scheduleStickyUpdate());

    // ------------------------------------------------------------------
    // Dept helpers (kept)
    // ------------------------------------------------------------------
    function isDeptToken(x){
        return /^d-[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(String(x || '').trim());
    }
    function deptTokenFromUuid(uuid){
        const u = String(uuid || '').trim();
        if (!u) return '';
        return u.startsWith('d-') ? u : ('d-' + u);
    }

    function readDeptTokenFromUrl(){
        try{
            const usp = new URLSearchParams(window.location.search || '');
            for (const [k, v] of usp.entries()){
                if (isDeptToken(k)) return k;
                if (k === 'd' && isDeptToken(v)) return v;
            }
        }catch(e){}

        const raw = String(window.location.search || '').replace(/^\?/, '').split('&').filter(Boolean);
        for (const part of raw){
            const decoded = decodeURIComponent(part);
            if (isDeptToken(decoded)) return decoded;
        }
        return '';
    }

    function buildSearchWithDept(params, deptToken){
        for (const k of Array.from(params.keys())){
            if (isDeptToken(k)) params.delete(k);
        }
        params.delete('submenu');
        if (params.get('d') && isDeptToken(params.get('d'))) params.delete('d');

        const normal = params.toString();
        const t = String(deptToken || '').trim();

        const parts = [];
        if (t && isDeptToken(t)) parts.push(encodeURIComponent(t));
        if (normal) parts.push(normal);

        return parts.length ? ('?' + parts.join('&')) : '';
    }

    /**
     * ✅ IMPORTANT CHANGE (for new publicTree response):
     * - Keep REQUESTED header_menu_id in URL (so header highlights stay correct)
     * - Use EFFECTIVE header_menu_id (from API scope) for all API calls (render/tree)
     */
    function pushUrlStateSubmenu(submenuSlug, deptTokenMaybe){
        const u = new URL(window.location.href);

        let path = stripSubmenuFromPath(u.pathname);
        const s = String(submenuSlug || '').trim();
        if (s) path += '&submenu=' + encodeURIComponent(s);

        const params = new URLSearchParams(u.search);

        // keep requested header menu id in URL if present.
        // If missing, set it from requested_header_menu_id (fallback to effective, then url).
        const hmRequested =
            (window.__DP_PAGE_SCOPE__ && parseInt(window.__DP_PAGE_SCOPE__.requested_header_menu_id || 0, 10)) ||
            readHeaderMenuIdFromUrl() ||
            (window.__DP_PAGE_SCOPE__ && parseInt(window.__DP_PAGE_SCOPE__.header_menu_id || 0, 10)) ||
            0;

        if (hmRequested > 0 && !params.get('header_menu_id')) {
            params.set('header_menu_id', String(hmRequested));
        }

        const deptTokenFinal = (deptTokenMaybe && isDeptToken(deptTokenMaybe))
            ? deptTokenMaybe
            : readDeptTokenFromUrl();

        const search = buildSearchWithDept(params, deptTokenFinal);
        window.history.pushState({}, '', path + search + u.hash);
    }

    function normalizeDepartmentsPayload(body){
        if (!body) return [];
        const root = (body && typeof body === 'object' && 'data' in body) ? body.data : body;

        if (Array.isArray(root)) return root;
        if (Array.isArray(root?.data)) return root.data;
        if (Array.isArray(root?.departments)) return root.departments;
        if (Array.isArray(root?.items)) return root.items;
        if (Array.isArray(root?.data?.data)) return root.data.data;
        return [];
    }

    async function loadPublicDepartmentsMap(){
        if (window.__DP_DEPT_ID_UUID_MAP__ && Object.keys(window.__DP_DEPT_ID_UUID_MAP__).length) {
            return window.__DP_DEPT_ID_UUID_MAP__;
        }

        const u = new URL(API_BASE + '/public/departments', window.location.origin);
        u.searchParams.set('_ts', Date.now());

        const r = await fetchJsonWithStatus(u.toString());
        if (!r.ok) {
            window.__DP_DEPT_ID_UUID_MAP__ = window.__DP_DEPT_ID_UUID_MAP__ || {};
            return window.__DP_DEPT_ID_UUID_MAP__;
        }

        const list = normalizeDepartmentsPayload(r.data);
        const map = {};

        list.forEach(d => {
            const id = parseInt(d?.id ?? d?.department_id ?? 0, 10);
            const uuid = String(d?.uuid ?? d?.department_uuid ?? '').trim();
            if (id > 0 && uuid) map[String(id)] = uuid;
        });

        window.__DP_DEPT_ID_UUID_MAP__ = map;
        return map;
    }

    async function resolvePublicPage(slug, headerMenuId = 0){
        const raw = String(slug || '').trim();
        if (!raw) return null;

        const u = new URL(API_BASE + '/public/pages/resolve', window.location.origin);
        u.searchParams.set('slug', raw);

        // requested header menu (keep)
        const hm = parseInt(headerMenuId || 0, 10);
        if (hm > 0) u.searchParams.set('header_menu_id', String(hm));

        const r = await fetchJsonWithStatus(u.toString());
        if (r.ok) return r.data?.page || null;
        if (r.status === 404) return null;

        const msg = (r.data && (r.data.message || r.data.error))
            ? (r.data.message || r.data.error)
            : ('Resolve failed: ' + r.status);

        throw new Error(msg);
    }

    function normalizeTree(treeData){
        if (!treeData) return [];
        if (Array.isArray(treeData)) return treeData;

        const arr = pick(treeData, ['tree','items','data','submenus','children','menu']);
        if (Array.isArray(arr)) return arr;

        if (treeData.data && Array.isArray(treeData.data.items)) return treeData.data.items;
        if (treeData.data && Array.isArray(treeData.data)) return treeData.data;

        return [];
    }

    function normalizeChildren(node){
        const c = pick(node, ['children','nodes','items','submenus']);
        return normalizeTree(c);
    }

    function setInnerHTMLWithScripts(el, html){
        el.innerHTML = '';
        const tpl = document.createElement('template');
        tpl.innerHTML = String(html || '');

        const scripts = tpl.content.querySelectorAll('script');
        scripts.forEach((oldScript) => {
            const s = document.createElement('script');
            for (const attr of oldScript.attributes) s.setAttribute(attr.name, attr.value);
            s.textContent = oldScript.textContent || '';
            oldScript.replaceWith(s);
        });

        el.appendChild(tpl.content);
    }

    function clearModuleAssets(){
        document.querySelectorAll('[data-dp-asset="style"]').forEach(n => n.remove());
        document.querySelectorAll('[data-dp-asset="script"]').forEach(n => n.remove());
    }

    function injectModuleStyles(stylesHtml){
        document.querySelectorAll('[data-dp-asset="style"]').forEach(n => n.remove());

        const tpl = document.createElement('template');
        tpl.innerHTML = String(stylesHtml || '');

        const SITE_ORIGIN = (() => {
            try { return new URL(SITE_BASE, window.location.origin).origin; }
            catch(e){ return window.location.origin; }
        })();

        const isSameOrigin = (url) => {
            try {
                const u = new URL(url, window.location.href);
                return u.origin === SITE_ORIGIN;
            } catch(e){
                return false;
            }
        };

        const isBlockedStyleHref = (href) => {
            const h = String(href || '').toLowerCase();
            if (h.includes('bootstrap')) return true;
            if (h.includes('font-awesome') || h.includes('fontawesome')) return true;
            if (h.includes('/assets/css/common/main.css')) return true;
            if (h.includes('cdn.jsdelivr.net')) return true;
            if (h.includes('cdnjs.cloudflare.com')) return true;
            return false;
        };

        [...tpl.content.children].forEach((node) => {
            const tag = (node.tagName || '').toUpperCase();
            if (!['LINK','STYLE','META'].includes(tag)) return;

            if (tag === 'LINK'){
                const rel = String(node.getAttribute('rel') || '').toLowerCase();
                const href = node.getAttribute('href') || '';
                if (rel !== 'stylesheet') return;
                if (!href) return;
                if (isBlockedStyleHref(href)) return;
                if (/^https?:\/\//i.test(href) && !isSameOrigin(href)) return;
            }

            node.setAttribute('data-dp-asset', 'style');
            document.head.appendChild(node);
        });
    }

    function runWithDomReadyShim(fn){
        const origAdd = document.addEventListener;

        document.addEventListener = function(type, listener, options){
            if (type === 'DOMContentLoaded' && document.readyState !== 'loading') {
                try { listener.call(document, new Event('DOMContentLoaded')); } catch(e){ console.error(e); }
                return;
            }
            return origAdd.call(document, type, listener, options);
        };

        try { fn(); } finally { document.addEventListener = origAdd; }
    }

    function injectModuleScripts(scriptsHtml){
        document.querySelectorAll('[data-dp-asset="script"]').forEach(n => n.remove());

        const tpl = document.createElement('template');
        tpl.innerHTML = String(scriptsHtml || '');

        const SITE_ORIGIN = (() => {
            try { return new URL(SITE_BASE, window.location.origin).origin; }
            catch(e){ return window.location.origin; }
        })();

        const isSameOrigin = (url) => {
            try {
                const u = new URL(url, window.location.href);
                return u.origin === SITE_ORIGIN;
            } catch(e){
                return false;
            }
        };

        const isBlockedScriptSrc = (src) => {
            const s = String(src || '').toLowerCase();
            if (s.includes('bootstrap')) return true;
            if (s.includes('sweetalert2')) return true;
            if (s.includes('cdn.jsdelivr.net')) return true;
            if (s.includes('cdnjs.cloudflare.com')) return true;
            return false;
        };

        const scripts = tpl.content.querySelectorAll('script');

        runWithDomReadyShim(() => {
            scripts.forEach((oldScript) => {
                const src = oldScript.getAttribute('src') || '';
                if (src){
                    if (isBlockedScriptSrc(src)) return;
                    if (/^https?:\/\//i.test(src) && !isSameOrigin(src)) return;
                }

                const s = document.createElement('script');
                for (const attr of oldScript.attributes) s.setAttribute(attr.name, attr.value);
                s.textContent = oldScript.textContent || '';
                s.setAttribute('data-dp-asset', 'script');
                document.body.appendChild(s);
            });
        });
    }

    // ------------------------------------------------------------------
    // ✅ NEW helper: read effective/requested header_menu_id from publicTree scope
    // ------------------------------------------------------------------
    function parseTreeScope(treeBody, fallbackRequested){
        const scope = (treeBody && typeof treeBody === 'object' && treeBody.scope) ? treeBody.scope : {};
        const effective = parseInt(scope?.header_menu_id ?? 0, 10) || 0;

        // new backend sends requested_header_menu_id, but if not present, use fallbackRequested
        const requested =
            parseInt(scope?.requested_header_menu_id ?? 0, 10) ||
            parseInt(scope?.requestedHeaderMenuId ?? 0, 10) ||
            parseInt(fallbackRequested || 0, 10) ||
            0;

        return { effective, requested, raw: scope || {} };
    }

    /* ==============================
     * ✅ Submenu loader (uses EFFECTIVE header_menu_id)
     * ============================== */
    async function loadSubmenuRightContent(submenuSlug, pageScope){
        const sslug = String(submenuSlug || '').trim();
        if (!sslug) return;

        showLoading('Loading submenu…');

        const u = new URL(API_BASE + '/public/page-submenus/render', window.location.origin);
        u.searchParams.set('slug', sslug);

        // ✅ always pass EFFECTIVE header_menu_id (falls back to requested only if effective missing)
        const hmEffective =
            parseInt(pageScope?.header_menu_id || 0, 10) ||
            parseInt(pageScope?.requested_header_menu_id || 0, 10) ||
            readHeaderMenuIdFromUrl();

        if (hmEffective > 0) u.searchParams.set('header_menu_id', String(hmEffective));

        if (pageScope?.page_id) u.searchParams.set('page_id', pageScope.page_id);
        else if (pageScope?.page_slug) u.searchParams.set('page_slug', pageScope.page_slug);

        const r = await fetchJsonWithStatus(u.toString());

        if (!r.ok) {
            const msg = (r.data && (r.data.message || r.data.error))
                ? (r.data.message || r.data.error)
                : ('Load failed: ' + r.status);

            showError(msg);
            scheduleStickyUpdate();
            return;
        }

        const payload = r.data || {};
        const type = payload.type;

        elTitle.textContent = payload.title || 'Dynamic Page';
        setMeta('');

        if (type === 'includable') {
            injectModuleStyles(payload?.assets?.styles || '');

            const out = payload.html || '';
            setInnerHTMLWithScripts(
                elHtml,
                out ? out : '<p class="text-muted mb-0">No HTML returned from includable section.</p>'
            );

            injectModuleScripts(payload?.assets?.scripts || '');
        }
        else if (type === 'page') {
            clearModuleAssets();
            const out = payload.html || '';
            setInnerHTMLWithScripts(
                elHtml,
                out ? out : '<p class="text-muted mb-0">No HTML returned from page content.</p>'
            );
        }
        else if (type === 'url') {
            clearModuleAssets();
            const url = payload.url || '';
            const safeUrl = url ? url : 'about:blank';

            const iframe = `
              <div class="mb-2 d-flex gap-2 flex-wrap">
                <a class="btn btn-sm btn-outline-primary" href="${safeUrl}" target="_blank" rel="noopener">
                  Open link in new tab
                </a>
              </div>
              <div class="dp-iframe">
                <iframe src="${safeUrl}" style="width:100%; height:75vh; border:0;" loading="lazy"></iframe>
              </div>
            `;
            setInnerHTMLWithScripts(elHtml, iframe);
        }
        else {
            clearModuleAssets();
            setInnerHTMLWithScripts(elHtml, '<p class="text-muted mb-0">Unknown content type.</p>');
        }

        elLoading.classList.add('d-none');
        elWrap.classList.remove('d-none');
        if (elNotFound) elNotFound.classList.add('d-none');
        hideError();

        scheduleStickyUpdate();
    }

    /* ==============================
     * Sidebar renderer (recursive)
     * ============================== */
    function renderTree(nodes, currentLower, parentUl, level = 0){
        let anyActiveInThisList = false;

        nodes.forEach((node) => {
            const li = document.createElement('li');
            li.className = 'hallienz-side__item';

            const children = normalizeChildren(node);
            const hasChildren = children.length > 0;

            const row = document.createElement('div');
            row.className = 'hallienz-side__row';

            const a = document.createElement('a');
            a.className = 'hallienz-side__link';
            a.href = 'javascript:void(0)';

            const nodeSlug = String(pick(node, ['slug']) || '').trim();
            a.dataset.submenuSlug = nodeSlug;
            a.setAttribute('data-submenu-slug', nodeSlug);

            const nodeDeptId = parseInt(pick(node, ['department_id','dept_id']) || 0, 10);
            if (nodeDeptId > 0) a.dataset.deptId = String(nodeDeptId);

            const nodeDeptUuid = String(pick(node, ['department_uuid','dept_uuid']) || '').trim();
            if (nodeDeptUuid) a.dataset.deptUuid = nodeDeptUuid;

            const basePad = 14;
            const indent = Math.min(54, level * 14);
            a.style.paddingLeft = (basePad + indent) + 'px';

            const title = pick(node, ['title','name','label']) || 'Untitled';
            const text = document.createElement('span');
            text.className = 'hallienz-side__text';
            text.textContent = title;
            a.appendChild(text);

            a.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();

                const sslug = (a.dataset.submenuSlug || '').trim();
                if (!sslug) return;

                document.querySelectorAll('.hallienz-side__link.active').forEach(x => x.classList.remove('active'));
                a.classList.add('active');

                await loadSubmenuRightContent(sslug, window.__DP_PAGE_SCOPE__ || null);

                const deptId = parseInt(a.dataset.deptId || '0', 10);
                const deptUuid =
                    (a.dataset.deptUuid || '').trim() ||
                    (deptId > 0 ? (window.__DP_DEPT_ID_UUID_MAP__?.[String(deptId)] || '') : '');

                const deptToken = deptTokenFromUuid(deptUuid);
                pushUrlStateSubmenu(sslug, deptToken);
            });

            row.appendChild(a);

            let childUl = null;
            let childHasActive = false;

            if (hasChildren){
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'hallienz-side__toggle';
                btn.setAttribute('aria-label', 'Toggle children');
                btn.setAttribute('aria-expanded', 'false');

                const ico = document.createElement('i');
                ico.className = 'fa-solid fa-chevron-right';
                btn.appendChild(ico);

                childUl = document.createElement('ul');
                childUl.className = 'hallienz-side__children';

                childHasActive = renderTree(children, currentLower, childUl, level + 1);

                if (childHasActive){
                    li.classList.add('open');
                    btn.setAttribute('aria-expanded', 'true');
                }

                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const open = li.classList.toggle('open');
                    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                });

                row.appendChild(btn);
            }

            li.appendChild(row);
            if (childUl) li.appendChild(childUl);
            parentUl.appendChild(li);

            if (childHasActive) anyActiveInThisList = true;
        });

        return anyActiveInThisList;
    }

    function findFirstSubmenuSlug(nodes){
        const stack = Array.isArray(nodes) ? [...nodes] : [];
        while (stack.length){
            const n = stack.shift();
            const s = String(pick(n, ['slug']) || '').trim();
            if (s) return s;

            const children = normalizeChildren(n);
            if (children.length) stack.unshift(...children);
        }
        return '';
    }

    function filterTreeByHeaderMenuId(nodes, headerMenuId){
        const hid = parseInt(headerMenuId || 0, 10);
        if (!hid || !Array.isArray(nodes)) return nodes || [];

        const out = [];

        nodes.forEach(n => {
            const nodeMenuId = parseInt(pick(n, ['header_menu_id','menu_id','headerMenuId']) || 0, 10);
            const kids = filterTreeByHeaderMenuId(normalizeChildren(n), hid);

            const keepById = (!nodeMenuId || nodeMenuId === hid);

            if (keepById || kids.length){
                const cloned = Object.assign({}, n);

                if (kids.length){
                    cloned.children = kids;
                    cloned.submenus = kids;
                    cloned.items = kids;
                    cloned.nodes = kids;
                }

                out.push(cloned);
            }
        });

        return out;
    }

    // ✅ UPDATED: loadSidebarIfAny now respects backend fallback scope.header_menu_id
    async function loadSidebarIfAny(page){
        // ✅ show sidebar skeleton while tree loads
        showSidebarSkeleton();

        const pageId   = pick(page, ['id']);
        const pageSlug = pick(page, ['slug']);

        const headerMenuFromPage = parseInt(pick(page, ['header_menu_id','headerMenuId','menu_id']) || 0, 10);
        const headerMenuFromUrl  = readHeaderMenuIdFromUrl();

        // This is the REQUESTED menu id (keep from URL if present)
        const headerMenuRequested = headerMenuFromUrl > 0 ? headerMenuFromUrl : headerMenuFromPage;

        if (!pageId && !pageSlug && !headerMenuRequested){
            // no sidebar possible
            hideSidebarSkeleton();
            sidebarCol.classList.add('d-none');
            sidebarCol.classList.remove('dp-side-preload');
            contentCol.className = 'col-12';
            scheduleStickyUpdate();
            return { hasSidebar:false, firstSubmenuSlug:'' };
        }

        const treeUrl = new URL(API_BASE + '/public/page-submenus/tree', window.location.origin);

        // ✅ always send REQUESTED header_menu_id (server will fallback if needed)
        if (headerMenuRequested > 0) treeUrl.searchParams.set('header_menu_id', String(headerMenuRequested));

        if (pageId) treeUrl.searchParams.set('page_id', pageId);
        else if (pageSlug) treeUrl.searchParams.set('page_slug', pageSlug);

        const r = await fetchJsonWithStatus(treeUrl.toString());

        if (!r.ok) {
            // hide sidebar on failure, expand content
            hideSidebarSkeleton();
            sidebarCol.classList.add('d-none');
            sidebarCol.classList.remove('dp-side-preload');
            contentCol.className = 'col-12';
            scheduleStickyUpdate();
            return { hasSidebar:false, firstSubmenuSlug:'' };
        }

        const body = r.data || {};
        const scopeParsed = parseTreeScope(body, headerMenuRequested);

        // ✅ EFFECTIVE header_menu_id (parent/grandparent fallback result)
        const headerMenuEffective = scopeParsed.effective || headerMenuRequested || 0;

        // ✅ store both in global scope:
        // - requested_header_menu_id stays as the user's selected menu (URL)
        // - header_menu_id becomes the effective menu used for API calls (render/tree result)
        window.__DP_PAGE_SCOPE__ = window.__DP_PAGE_SCOPE__ || {};
        if (!window.__DP_PAGE_SCOPE__.page_id && pageId) window.__DP_PAGE_SCOPE__.page_id = pageId;
        if (!window.__DP_PAGE_SCOPE__.page_slug && pageSlug) window.__DP_PAGE_SCOPE__.page_slug = pageSlug;

        window.__DP_PAGE_SCOPE__.requested_header_menu_id = scopeParsed.requested || headerMenuRequested || null;
        window.__DP_PAGE_SCOPE__.header_menu_id = headerMenuEffective || null;

        let nodes = normalizeTree(body);

        // (safe) filter using EFFECTIVE menu id (so requested=10 still shows effective=7)
        nodes = filterTreeByHeaderMenuId(nodes, headerMenuEffective);

        if (!nodes.length) {
            // no sidebar items: hide column & expand content
            hideSidebarSkeleton();
            sidebarCol.classList.add('d-none');
            sidebarCol.classList.remove('dp-side-preload');
            contentCol.className = 'col-12';
            scheduleStickyUpdate();
            return { hasSidebar:false, firstSubmenuSlug:'' };
        }

        // sidebar exists: show & render
        sidebarCol.classList.remove('d-none');
        sidebarCol.classList.remove('dp-side-preload');
        contentCol.className = 'col-12 col-lg-9';

        submenuList.innerHTML = '';

        const pageTitle = pick(page, ['title']) || 'Menu';
        sidebarHead.textContent = pageTitle;

        renderTree(nodes, '', submenuList, 0);

        const firstSubmenuSlug = findFirstSubmenuSlug(nodes);

        // ✅ switch from skeleton to real list
        hideSidebarSkeleton();

        scheduleStickyUpdate();

        return { hasSidebar:true, firstSubmenuSlug };
    }

    function openAncestorsOfLink(linkEl){
        try{
            let node = linkEl?.closest('.hallienz-side__item');
            while(node){
                node.classList.add('open');
                node = node.parentElement?.closest?.('.hallienz-side__item') || null;
            }
        }catch(e){}
    }

    function setupHeaderMenuClicks() {
        document.addEventListener('click', function(e) {
            const headerLink = e.target.closest('a[data-header-menu]') ||
                              e.target.closest('a[href*="header_menu_id"]');

            if (headerLink) {
                e.preventDefault();

                let menuId = headerLink.getAttribute('data-menu-id') ||
                           headerLink.getAttribute('data-header-menu-id');

                if (!menuId) {
                    const href = headerLink.getAttribute('href') || '';
                    const url = new URL(href, window.location.origin);
                    menuId = url.searchParams.get('header_menu_id');
                }

                if (menuId) {
                    const currentUrl = new URL(window.location);
                    currentUrl.searchParams.set('header_menu_id', menuId);
                    currentUrl.searchParams.delete('submenu');
                    window.location.href = currentUrl.toString();
                }
            }
        });
    }

    async function init(){
        hideError();
        setupStickyObservers();

        // ✅ reset layout preload state every init (including popstate)
        resetSidebarPreloadState();

        const slugCandidate = getSlugCandidate();
        const currentLower = toLowerSafe(slugCandidate);

        if (!slugCandidate) {
            elLoading.classList.add('d-none');
            showError("No page slug provided. Use /link/page/<slug>  OR  /page/<slug>  OR  ?slug=about-us");
            // no sidebar when slug missing
            hideSidebarSkeleton();
            sidebarCol.classList.add('d-none');
            sidebarCol.classList.remove('dp-side-preload');
            contentCol.className = 'col-12';
            scheduleStickyUpdate();
            return;
        }

        await loadPublicDepartmentsMap();

        showLoading('Loading page…');

        // Requested header menu id comes from URL (keep)
        const headerMenuFromUrl = readHeaderMenuIdFromUrl();
        const page = await resolvePublicPage(slugCandidate, headerMenuFromUrl);

        if (!page) {
            showNotFound(slugCandidate);
            // no sidebar if page not found
            hideSidebarSkeleton();
            sidebarCol.classList.add('d-none');
            sidebarCol.classList.remove('dp-side-preload');
            contentCol.className = 'col-12';
            scheduleStickyUpdate();
            return;
        }

        // Store requested in scope (effective will be updated after publicTree response)
        const headerMenuRequested =
            headerMenuFromUrl > 0
                ? headerMenuFromUrl
                : (parseInt(pick(page, ['header_menu_id','headerMenuId','menu_id']) || 0, 10) || 0);

        window.__DP_PAGE_SCOPE__ = {
            page_id: pick(page, ['id']) || null,
            page_slug: pick(page, ['slug']) || null,

            // start both as requested; loadSidebarIfAny will overwrite header_menu_id with effective fallback
            header_menu_id: headerMenuRequested || null,
            requested_header_menu_id: headerMenuRequested || null
        };

        elTitle.textContent = pick(page, ['title']) || slugCandidate;
        setMeta('');

        const html = pick(page, ['content_html']) || '';
        setInnerHTMLWithScripts(elHtml, html || '<p class="text-muted mb-0">No content_html returned from pages resolve API.</p>');

        elLoading.classList.add('d-none');
        elWrap.classList.remove('d-none');
        if (elNotFound) elNotFound.classList.add('d-none');

        await loadSidebarIfAny(page);

        let submenuSlug = (readSubmenuFromPathname() || '').trim();
        if (!submenuSlug){
            const qs = new URLSearchParams(window.location.search);
            submenuSlug = (qs.get('submenu') || '').trim();
        }

        if (submenuSlug) {
            const link = document.querySelector('.hallienz-side__link[data-submenu-slug="' + safeCssEscape(submenuSlug) + '"]');
            if (link) {
                document.querySelectorAll('.hallienz-side__link.active').forEach(x => x.classList.remove('active'));
                link.classList.add('active');
                openAncestorsOfLink(link);
            }
            await loadSubmenuRightContent(submenuSlug, window.__DP_PAGE_SCOPE__);
        }

        scheduleStickyUpdate();
    }

    init().catch((e) => {
        console.error(e);
        showError(e?.message || 'Something went wrong.');
        // fail safe: hide sidebar preload
        hideSidebarSkeleton();
        sidebarCol.classList.add('d-none');
        sidebarCol.classList.remove('dp-side-preload');
        contentCol.className = 'col-12';
        scheduleStickyUpdate();
    });

    document.addEventListener('DOMContentLoaded', function() {
        setupHeaderMenuClicks();
        scheduleStickyUpdate();
    });

    window.addEventListener('popstate', function() {
        init().catch((e) => {
            console.error(e);
            showError(e?.message || 'Something went wrong.');
            // fail safe: hide sidebar preload
            hideSidebarSkeleton();
            sidebarCol.classList.add('d-none');
            sidebarCol.classList.remove('dp-side-preload');
            contentCol.className = 'col-12';
            scheduleStickyUpdate();
        });
    });

})();
</script>

@stack('scripts')
</body>
</html>
