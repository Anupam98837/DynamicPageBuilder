{{-- resources/views/test.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    {{-- ✅ Server-side meta tags (SEO + share friendly) --}}
    @include('landing.components.metaTags')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dynamic Page')</title>

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/media/images/favicon/msit_logo.jpg') }}">

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

        /* Carousel */
        .ce-carousel{position:relative;width:100%;margin:0 0 12px 0;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;background:#f3f4f6;}
        .ce-carousel-viewport{width:100%;height:260px;overflow:hidden;}
        .ce-carousel-track{display:flex;width:100%;height:100%;transform:translateX(0);transition:transform .35s ease;}
        .ce-carousel-slide{flex:0 0 100%;height:100%;}
        .ce-carousel-slide img{width:100%;height:100%;object-fit:cover;display:block;}
        .ce-carousel[data-fit="contain"] .ce-carousel-slide img{object-fit:contain;background:#fff;}
        .ce-carousel-btn{position:absolute;top:50%;transform:translateY(-50%);border:none;background:rgba(17,24,39,.55);color:#fff;width:34px;height:34px;border-radius:999px;cursor:pointer;display:flex;align-items:center;justify-content:center;}
        .ce-carousel-prev{left:10px;}
        .ce-carousel-next{right:10px;}
        .ce-carousel-dots{position:absolute;left:0;right:0;bottom:10px;display:flex;justify-content:center;gap:6px;padding:0 10px;}
        .ce-carousel-dot{width:8px;height:8px;border-radius:999px;border:0;background:rgba(255,255,255,.55);cursor:pointer;}
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

                    <div id="pageComingSoonWrap" class="d-none">
                        @include('partials.comingSoon')
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
    // ============================================================
    // Carousel initialization (kept as is)
    // ============================================================
    function parseList(raw){
        return (raw||'').split(/\\r?\\n|\\|/g).map(function(s){return (s||'').trim();}).filter(Boolean);
    }

    function getOpts(car){
        var h = parseInt(car.getAttribute('data-height') || '260', 10);
        var interval = parseInt(car.getAttribute('data-interval') || '3000', 10);
        return {
            height: Math.max(120, isNaN(h)?260:h),
            interval: Math.max(800, isNaN(interval)?3000:interval),
            autoplay: car.getAttribute('data-autoplay') === 'true',
            arrows: car.getAttribute('data-arrows') !== 'false',
            dots: car.getAttribute('data-dots') !== 'false',
            loop: car.getAttribute('data-loop') !== 'false',
            fit: (car.getAttribute('data-fit') === 'contain') ? 'contain' : 'cover'
        };
    }

    function ensure(car){
        var viewport = car.querySelector('.ce-carousel-viewport');
        var track = car.querySelector('.ce-carousel-track');
        if(!viewport){ viewport=document.createElement('div'); viewport.className='ce-carousel-viewport'; car.insertBefore(viewport, car.firstChild); }
        if(!track){ track=document.createElement('div'); track.className='ce-carousel-track'; viewport.appendChild(track); }
        var dots = car.querySelector('.ce-carousel-dots');
        if(!dots){ dots=document.createElement('div'); dots.className='ce-carousel-dots'; car.appendChild(dots); }
        var prev = car.querySelector('.ce-carousel-prev');
        var next = car.querySelector('.ce-carousel-next');
        return {viewport:viewport, track:track, dots:dots, prev:prev, next:next};
    }

    function buildSlides(car){
        var el = ensure(car);
        var urls = parseList(car.getAttribute('data-images') || '');
        if(!urls.length){
            urls = Array.prototype.slice.call(car.querySelectorAll('.ce-carousel-slide img')).map(function(img){
                return (img.getAttribute('src')||'').trim();
            }).filter(Boolean);
        }
        if(!urls.length) urls = ['https://placehold.co/600x260'];

        el.track.innerHTML = urls.map(function(u){
            return '<div class="ce-carousel-slide"><img src="'+u.replace(/"/g,'&quot;')+'" alt="Slide"></div>';
        }).join('');

        el.dots.innerHTML = urls.map(function(_,i){
            return '<button type="button" class="ce-carousel-dot" data-idx="'+i+'" aria-label="Go to slide '+(i+1)+'"></button>';
        }).join('');

        return urls;
    }

    function stop(car){
        if(car.__t){ clearInterval(car.__t); car.__t=null; }
    }

    function go(car, idx, restart){
        var o = getOpts(car);
        var el = ensure(car);
        var slides = car.querySelectorAll('.ce-carousel-slide');
        var max = slides.length - 1;
        var i = idx;

        if(o.loop){
            if(i<0) i=max;
            if(i>max) i=0;
        }else{
            i = Math.max(0, Math.min(max, i));
        }

        car.setAttribute('data-index', String(i));
        el.track.style.transform = 'translateX(-' + (i*100) + '%)';

        var dots = car.querySelectorAll('.ce-carousel-dot');
        for(var d=0; d<dots.length; d++){
            if(d===i) dots[d].classList.add('active');
            else dots[d].classList.remove('active');
        }

        if(restart) start(car);
    }

    function start(car){
        var o = getOpts(car);
        stop(car);
        if(!o.autoplay) return;
        car.__t = setInterval(function(){
            var cur = parseInt(car.getAttribute('data-index') || '0', 10) || 0;
            go(car, cur+1, false);
        }, o.interval);
    }

    function init(car){
        var o = getOpts(car);
        var el = ensure(car);

        car.setAttribute('data-fit', o.fit);
        el.viewport.style.height = o.height + 'px';

        buildSlides(car);

        if(el.prev) el.prev.style.display = o.arrows ? '' : 'none';
        if(el.next) el.next.style.display = o.arrows ? '' : 'none';
        el.dots.style.display = o.dots ? 'flex' : 'none';

        if(!car.__bound){
            car.__bound=true;
            car.addEventListener('click', function(e){
                var p = e.target.closest && e.target.closest('.ce-carousel-prev');
                var n = e.target.closest && e.target.closest('.ce-carousel-next');
                var d = e.target.closest && e.target.closest('.ce-carousel-dot');

                if(p){ e.preventDefault(); go(car, (parseInt(car.getAttribute('data-index')||'0',10)||0)-1, true); }
                else if(n){ e.preventDefault(); go(car, (parseInt(car.getAttribute('data-index')||'0',10)||0)+1, true); }
                else if(d){ e.preventDefault(); go(car, parseInt(d.getAttribute('data-idx')||'0',10)||0, true); }
            });
            car.addEventListener('mouseenter', function(){ stop(car); });
            car.addEventListener('mouseleave', function(){ start(car); });
        }

        go(car, parseInt(car.getAttribute('data-index')||'0',10)||0, false);
        start(car);
    }

    function boot(){
        var cars = document.querySelectorAll('.ce-carousel');
        for(var i=0;i<cars.length;i++) init(cars[i]);
    }

    if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
</script>

<script>
(function(){
    const API_BASE  = @json($apiBase);
    const SITE_BASE = @json(url('/'));

    // ============================================================
    // ✅ UUID helpers for header menu identification
    // ============================================================
    
    /**
     * Check if a string is a valid UUID
     */
    function isUuid(str) {
        const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
        return uuidRegex.test(String(str || '').trim());
    }

    /**
     * Check if a parameter is a header menu UUID token (h-{uuid})
     */
    function isHeaderMenuUuidToken(str) {
        const token = String(str || '').trim();
        if (!token.startsWith('h-')) return false;
        const uuid = token.substring(2);
        return isUuid(uuid);
    }

    /**
     * Extract UUID from header menu token (h-{uuid})
     */
    function extractUuidFromHeaderToken(token) {
        const t = String(token || '').trim();
        if (!t.startsWith('h-')) return '';
        return t.substring(2);
    }

    /**
     * Create header menu token from UUID
     */
    function headerMenuTokenFromUuid(uuid) {
        const u = String(uuid || '').trim();
        if (!u) return '';
        return u.startsWith('h-') ? u : ('h-' + u);
    }

    /**
     * Read header menu UUID from URL (h-{uuid} parameter)
     */
    function readHeaderMenuUuidFromUrl() {
        try {
            const usp = new URLSearchParams(window.location.search || '');
            
            // Look for h-{uuid} parameters
            for (const [key, value] of usp.entries()) {
                // Check if the key itself is a header token
                if (isHeaderMenuUuidToken(key)) {
                    return extractUuidFromHeaderToken(key);
                }
                // Check if value is a header token
                if (isHeaderMenuUuidToken(value)) {
                    return extractUuidFromHeaderToken(value);
                }
            }
            
            // Legacy fallback: look for header_menu_id parameter
            const legacyId = usp.get('header_menu_id') || usp.get('menu_id') || usp.get('headerMenuId');
            if (legacyId) {
                return legacyId;
            }
        } catch (e) {}
        
        return '';
    }

    /**
     * Build search params with header menu UUID token
     */
    function buildSearchWithHeaderUuid(params, headerUuid) {
        // Remove any existing header tokens
        const newParams = new URLSearchParams();
        
        for (const [key, value] of params.entries()) {
            if (!isHeaderMenuUuidToken(key) && key !== 'header_menu_id' && key !== 'menu_id' && key !== 'headerMenuId') {
                newParams.append(key, value);
            }
        }
        
        // Add the new header token if provided
        if (headerUuid) {
            // Check if it's a UUID or legacy ID
            if (isUuid(headerUuid)) {
                const token = headerMenuTokenFromUuid(headerUuid);
                if (token) {
                    newParams.append(token, '1');
                }
            } else {
                // Legacy ID
                newParams.append('header_menu_id', headerUuid);
            }
        }
        
        return newParams;
    }

    // ============================================================
    // Auth cache + global API auth injection
    // ============================================================
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

    // ============================================================
    // DOM refs
    // ============================================================
    const elLoading   = document.getElementById('pageLoading');
    const elError     = document.getElementById('pageError');
    const elNotFound  = document.getElementById('pageNotFoundWrap');
    const elComingSoon= document.getElementById('pageComingSoonWrap');
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

    // ============================================================
    // Skeleton helpers
    // ============================================================
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
        if (elComingSoon) elComingSoon.classList.add('d-none');
        elWrap.classList.add('d-none');
        if (elNotFound) elNotFound.classList.add('d-none');
        elLoading.classList.remove('d-none');
    }

    function showError(msg){
        elError.textContent = msg;
        elError.classList.remove('d-none');
        if (elComingSoon) elComingSoon.classList.add('d-none');
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
        if (elComingSoon) elComingSoon.classList.add('d-none');
        elLoading.classList.add('d-none');
        elWrap.classList.add('d-none');
        if (elNotFound) elNotFound.classList.remove('d-none');
    }

    function showComingSoon(submenuSlug, payload){
        try{
            const s = String(submenuSlug || '').trim();
            const title = String(payload?.title || 'Coming Soon').trim();
            const msg = String(payload?.message || '').trim();

            const s1 = document.querySelector('[data-dp-comingsoon-slug]');
            if (s1) s1.textContent = s;

            const t1 = document.querySelector('[data-dp-comingsoon-title]');
            if (t1) t1.textContent = title;

            const m1 = document.querySelector('[data-dp-comingsoon-message]');
            if (m1 && msg) m1.textContent = msg;
        }catch(e){}

        elError.classList.add('d-none'); elError.textContent = '';
        elLoading.classList.add('d-none');
        elWrap.classList.add('d-none');
        if (elNotFound) elNotFound.classList.add('d-none');
        if (elComingSoon) elComingSoon.classList.remove('d-none');
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

    function normalizeExternalUrl(raw){
        const s0 = String(raw || '').trim();
        if (!s0) return '';
        const low = s0.toLowerCase();

        const bad = ['null','undefined','#','0','about:blank'];
        if (bad.includes(low)) return '';
        if (low.startsWith('javascript:')) return '';

        try{
            return new URL(s0, window.location.origin).toString();
        }catch(e){
            try{
                if (/^[\w.-]+\.[a-z]{2,}([\/?#]|$)/i.test(s0)){
                    return new URL('https://' + s0).toString();
                }
            }catch(e2){}
            return '';
        }
    }

    // ============================================================
    // Smart Sticky Columns
    // ============================================================
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

    // ============================================================
    // Dept helpers (kept)
    // ============================================================
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
        const newParams = new URLSearchParams();
        
        for (const [key, value] of params.entries()) {
            if (!isDeptToken(key) && key !== 'd' && key !== 'department_uuid') {
                newParams.append(key, value);
            }
        }
        
        if (deptToken && isDeptToken(deptToken)) {
            newParams.append(deptToken, '1');
        }
        
        return newParams;
    }

    /**
     * Push URL state with submenu and header UUID
     */
    function pushUrlStateSubmenu(submenuSlug, headerUuid, deptTokenMaybe){
        const u = new URL(window.location.href);

        let path = stripSubmenuFromPath(u.pathname);
        const s = String(submenuSlug || '').trim();
        if (s) path += '&submenu=' + encodeURIComponent(s);

        // Build params with header UUID
        let params = new URLSearchParams(u.search);
        
        // Handle header UUID from scope if available
        const headerUuidFinal = headerUuid || 
            (window.__DP_PAGE_SCOPE__?.header_menu_uuid) || 
            readHeaderMenuUuidFromUrl();
        
        // Build search with header UUID
        params = buildSearchWithHeaderUuid(params, headerUuidFinal);
        
        // Add department token
        const deptTokenFinal = (deptTokenMaybe && isDeptToken(deptTokenMaybe))
            ? deptTokenMaybe
            : readDeptTokenFromUrl();
            
        const finalParams = buildSearchWithDept(params, deptTokenFinal);
        
        const search = finalParams.toString() ? '?' + finalParams.toString() : '';
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

    async function resolvePublicPage(slug, headerUuid = ''){
        const raw = String(slug || '').trim();
        if (!raw) return null;

        const u = new URL(API_BASE + '/public/pages/resolve', window.location.origin);
        u.searchParams.set('slug', raw);

        // Add header UUID if present
        if (headerUuid) {
            if (isUuid(headerUuid)) {
                u.searchParams.set('header_uuid', headerUuid);
            } else {
                u.searchParams.set('header_menu_id', headerUuid);
            }
        }

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

        const scripts = Array.from(tpl.content.querySelectorAll('script'));
        scripts.forEach(s => s.remove());

        el.appendChild(tpl.content);

        runWithDomReadyShim(() => {
            scripts.forEach((oldScript) => {
                const s = document.createElement('script');
                for (const attr of oldScript.attributes) s.setAttribute(attr.name, attr.value);
                s.textContent = oldScript.textContent || '';
                document.body.appendChild(s);
            });
        });
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

    /**
     * Parse tree scope from API response
     */
    function parseTreeScope(treeBody, requestedHeaderUuid) {
        const scope = (treeBody && typeof treeBody === 'object' && treeBody.scope) ? treeBody.scope : {};
        
        // The API returns the effective header_menu_id (numeric) in scope
        const effectiveId = parseInt(scope?.header_menu_id ?? 0, 10) || 0;
        const requestedId = parseInt(scope?.requested_header_menu_id ?? 0, 10) || 0;
        
        // Store the UUID if available in the response
        const headerUuid = scope?.header_menu_uuid || '';
        
        return { 
            effectiveId, 
            requestedId, 
            headerUuid,
            raw: scope || {} 
        };
    }

    /**
     * Load submenu content - FIXED VERSION
     */
    async function loadSubmenuRightContent(submenuSlug, pageScope, preOpenedWin = null){
        const sslug = String(submenuSlug || '').trim();
        if (!sslug) {
            try{ if (preOpenedWin && !preOpenedWin.closed) preOpenedWin.close(); }catch(e){}
            return;
        }

        showLoading('Loading submenu…');

        const u = new URL(API_BASE + '/public/page-submenus/render', window.location.origin);
        u.searchParams.set('slug', sslug);

        // CRITICAL FIX: Always pass header_menu_id if available
        // First try to get from pageScope (set by loadSidebarIfAny)
        let headerMenuId = null;
        
        if (pageScope) {
            // Try different possible locations for header_menu_id
            headerMenuId = pageScope.header_menu_id || 
                          pageScope.requested_header_menu_id || 
                          pageScope.effective_header_menu_id;
        }
        
        // If not in scope, try to read from URL
        if (!headerMenuId) {
            const urlParams = new URLSearchParams(window.location.search);
            headerMenuId = urlParams.get('header_menu_id') || 
                          urlParams.get('menu_id') || 
                          urlParams.get('headerMenuId');
        }
        
        // If still no header_menu_id, try to get it from the page scope's requested value
        if (!headerMenuId && window.__DP_PAGE_SCOPE__) {
            headerMenuId = window.__DP_PAGE_SCOPE__.requested_header_menu_id || 
                          window.__DP_PAGE_SCOPE__.header_menu_id;
        }
        
        // Add header_menu_id to request if we have it
        if (headerMenuId && parseInt(headerMenuId) > 0) {
            u.searchParams.set('header_menu_id', String(headerMenuId));
        }

        // Also try to get header UUID
        const headerUuid = pageScope?.header_menu_uuid || readHeaderMenuUuidFromUrl();
        if (headerUuid && isUuid(headerUuid)) {
            u.searchParams.set('header_uuid', headerUuid);
        }

        if (pageScope?.page_id) u.searchParams.set('page_id', pageScope.page_id);
        else if (pageScope?.page_slug) u.searchParams.set('page_slug', pageScope.page_slug);

        const r = await fetchJsonWithStatus(u.toString());

        if (!r.ok) {
            try{ if (preOpenedWin && !preOpenedWin.closed) preOpenedWin.close(); }catch(e){}
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

        if (type === 'coming_soon') {
            try{ if (preOpenedWin && !preOpenedWin.closed) preOpenedWin.close(); }catch(e){}
            clearModuleAssets();
            showComingSoon(sslug, payload);
            scheduleStickyUpdate();
            return;
        }

        if (type === 'includable') {
            try{ if (preOpenedWin && !preOpenedWin.closed) preOpenedWin.close(); }catch(e){}
            if (elComingSoon) elComingSoon.classList.add('d-none');
            injectModuleStyles(payload?.assets?.styles || '');

            const out = payload.html || '';
            setInnerHTMLWithScripts(
                elHtml,
                out ? out : '<p class="text-muted mb-0">No HTML returned from includable section.</p>'
            );

            injectModuleScripts(payload?.assets?.scripts || '');
        }
        else if (type === 'page') {
            try{ if (preOpenedWin && !preOpenedWin.closed) preOpenedWin.close(); }catch(e){}
            if (elComingSoon) elComingSoon.classList.add('d-none');
            clearModuleAssets();
            const out = payload.html || '';
            setInnerHTMLWithScripts(
                elHtml,
                out ? out : '<p class="text-muted mb-0">No HTML returned from page content.</p>'
            );
        }
        else if (type === 'url') {
            if (elComingSoon) elComingSoon.classList.add('d-none');
            clearModuleAssets();

            const rawUrl = payload.url || payload.link || payload.href || '';
            const safeUrl = normalizeExternalUrl(rawUrl) || (rawUrl ? rawUrl : 'about:blank');

            let opened = false;
            try{
                if (preOpenedWin && !preOpenedWin.closed && safeUrl && safeUrl !== 'about:blank'){
                    preOpenedWin.location.href = safeUrl;
                    opened = true;
                } else if (safeUrl && safeUrl !== 'about:blank'){
                    const w = window.open(safeUrl, '_blank', 'noopener,noreferrer');
                    opened = !!w;
                }
            }catch(e){}

            setInnerHTMLWithScripts(elHtml, `
              <div class="alert alert-info mb-0">
                ${opened ? 'Opened link in a new tab.' : 'Popup blocked. Please open the link:'}
                <a href="${safeUrl}" target="_blank" rel="noopener noreferrer" class="ms-1">Open link</a>
              </div>
            `);
        }
        else {
            try{ if (preOpenedWin && !preOpenedWin.closed) preOpenedWin.close(); }catch(e){}
            if (elComingSoon) elComingSoon.classList.add('d-none');
            clearModuleAssets();
            setInnerHTMLWithScripts(elHtml, '<p class="text-muted mb-0">Unknown content type.</p>');
        }

        elLoading.classList.add('d-none');
        elWrap.classList.remove('d-none');
        if (elNotFound) elNotFound.classList.add('d-none');
        if (elComingSoon) elComingSoon.classList.add('d-none');
        hideError();

        scheduleStickyUpdate();
    }

    /**
     * Render sidebar tree
     */
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

            const nodeLink = String(pick(node, ['link','url','href','external_url','externalLink','page_link','page_url']) || '').trim();
            if (nodeLink) a.dataset.submenuLink = nodeLink;

            const nodeTypeHint = String(pick(node, ['type','content_type','submenu_type','render_type']) || '').toLowerCase().trim();
            if (nodeTypeHint === 'url') a.dataset.submenuType = 'url';

            const nodeDeptId = parseInt(pick(node, ['department_id','dept_id']) || 0, 10);
            if (nodeDeptId > 0) a.dataset.deptId = String(nodeDeptId);

            const nodeDeptUuid = String(pick(node, ['department_uuid','dept_uuid']) || '').trim();
            if (nodeDeptUuid) a.dataset.deptUuid = nodeDeptUuid;

            // Store header UUID if available in the node
            const nodeHeaderUuid = String(pick(node, ['header_menu_uuid', 'menu_uuid']) || '').trim();
            if (nodeHeaderUuid) a.dataset.headerUuid = nodeHeaderUuid;

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

                const directLinkRaw = (a.dataset.submenuLink || '').trim();
                const directLink = normalizeExternalUrl(directLinkRaw);
                if (directLink){
                    try { window.open(directLink, '_blank', 'noopener,noreferrer'); } catch(err) {}

                    document.querySelectorAll('.hallienz-side__link.active').forEach(x => x.classList.remove('active'));
                    a.classList.add('active');
                    scheduleStickyUpdate();
                    return;
                }

                const raw = (a.dataset.submenuSlug || '').trim();
                const bad = ['null','undefined','#','0'];
                const sslug = (raw && !bad.includes(raw.toLowerCase())) ? raw : '';

                if (!sslug) {
                    if (hasChildren) {
                        li.classList.toggle('open');
                        scheduleStickyUpdate();
                        return;
                    }

                    document.querySelectorAll('.hallienz-side__link.active').forEach(x => x.classList.remove('active'));
                    a.classList.add('active');

                    clearModuleAssets();
                    showComingSoon('', {
                        title: String(title || 'Coming Soon'),
                        message: 'This section is coming soon.'
                    });
                    scheduleStickyUpdate();
                    return;
                }

                document.querySelectorAll('.hallienz-side__link.active').forEach(x => x.classList.remove('active'));
                a.classList.add('active');

                let preWin = null;
                try{
                    const hinted = String(a.dataset.submenuType || '').toLowerCase().trim();
                    if (hinted === 'url'){
                        preWin = window.open('about:blank', '_blank', 'noopener,noreferrer');
                    }
                }catch(e2){}

                await loadSubmenuRightContent(sslug, window.__DP_PAGE_SCOPE__ || null, preWin);

                const deptId = parseInt(a.dataset.deptId || '0', 10);
                const deptUuid =
                    (a.dataset.deptUuid || '').trim() ||
                    (deptId > 0 ? (window.__DP_DEPT_ID_UUID_MAP__?.[String(deptId)] || '') : '');

                const deptToken = deptTokenFromUuid(deptUuid);
                
                // Get header UUID from node or scope
                const headerUuid = a.dataset.headerUuid || window.__DP_PAGE_SCOPE__?.header_menu_uuid || '';
                
                pushUrlStateSubmenu(sslug, headerUuid, deptToken);
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

    /**
     * Filter tree by header menu UUID
     */
    function filterTreeByHeaderUuid(nodes, headerUuid) {
        if (!headerUuid || !Array.isArray(nodes)) return nodes || [];

        const out = [];

        nodes.forEach(n => {
            const nodeHeaderUuid = String(pick(n, ['header_menu_uuid', 'menu_uuid']) || '').trim();
            const kids = filterTreeByHeaderUuid(normalizeChildren(n), headerUuid);

            // Keep if node matches UUID or has matching children
            const keepByUuid = (!nodeHeaderUuid || nodeHeaderUuid === headerUuid);

            if (keepByUuid || kids.length) {
                const cloned = Object.assign({}, n);

                if (kids.length) {
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

    /**
     * Load sidebar with UUID support
     */
    async function loadSidebarIfAny(page) {
        showSidebarSkeleton();

        const pageId   = pick(page, ['id']);
        const pageSlug = pick(page, ['slug']);

        // Get header identifier from URL (UUID or legacy ID)
        const headerFromUrl = readHeaderMenuUuidFromUrl();

        if (!pageId && !pageSlug && !headerFromUrl) {
            hideSidebarSkeleton();
            sidebarCol.classList.add('d-none');
            sidebarCol.classList.remove('dp-side-preload');
            contentCol.className = 'col-12';
            scheduleStickyUpdate();
            return { hasSidebar: false, firstSubmenuSlug: '' };
        }

        const treeUrl = new URL(API_BASE + '/public/page-submenus/tree', window.location.origin);

        // Send header identifier if present
        if (headerFromUrl) {
            if (isUuid(headerFromUrl)) {
                treeUrl.searchParams.set('header_uuid', headerFromUrl);
            } else {
                treeUrl.searchParams.set('header_menu_id', headerFromUrl);
            }
        }

        if (pageId) treeUrl.searchParams.set('page_id', pageId);
        else if (pageSlug) treeUrl.searchParams.set('page_slug', pageSlug);

        const r = await fetchJsonWithStatus(treeUrl.toString());

        if (!r.ok) {
            hideSidebarSkeleton();
            sidebarCol.classList.add('d-none');
            sidebarCol.classList.remove('dp-side-preload');
            contentCol.className = 'col-12';
            scheduleStickyUpdate();
            return { hasSidebar: false, firstSubmenuSlug: '' };
        }

        const body = r.data || {};
        const scopeParsed = parseTreeScope(body, headerFromUrl);

        // Store in global scope
        const headerUuidEffective = scopeParsed.headerUuid || (isUuid(headerFromUrl) ? headerFromUrl : '');
        const headerIdEffective = scopeParsed.effectiveId || (!isUuid(headerFromUrl) ? parseInt(headerFromUrl) : 0);

        window.__DP_PAGE_SCOPE__ = window.__DP_PAGE_SCOPE__ || {};
        if (!window.__DP_PAGE_SCOPE__.page_id && pageId) window.__DP_PAGE_SCOPE__.page_id = pageId;
        if (!window.__DP_PAGE_SCOPE__.page_slug && pageSlug) window.__DP_PAGE_SCOPE__.page_slug = pageSlug;

        window.__DP_PAGE_SCOPE__.requested_header_menu_uuid = (isUuid(headerFromUrl) ? headerFromUrl : null) || null;
        window.__DP_PAGE_SCOPE__.header_menu_uuid = headerUuidEffective || null;
        window.__DP_PAGE_SCOPE__.requested_header_menu_id = (!isUuid(headerFromUrl) ? parseInt(headerFromUrl) : null) || null;
        window.__DP_PAGE_SCOPE__.header_menu_id = headerIdEffective || null;

        let nodes = normalizeTree(body);

        // Filter using UUID if available
        if (headerUuidEffective) {
            nodes = filterTreeByHeaderUuid(nodes, headerUuidEffective);
        }

        if (!nodes.length) {
            hideSidebarSkeleton();
            sidebarCol.classList.add('d-none');
            sidebarCol.classList.remove('dp-side-preload');
            contentCol.className = 'col-12';
            scheduleStickyUpdate();
            return { hasSidebar: false, firstSubmenuSlug: '' };
        }

        sidebarCol.classList.remove('d-none');
        sidebarCol.classList.remove('dp-side-preload');
        contentCol.className = 'col-12 col-lg-9';

        submenuList.innerHTML = '';

        const pageTitle = pick(page, ['title']) || 'Menu';
        sidebarHead.textContent = pageTitle;

        renderTree(nodes, '', submenuList, 0);

        const firstSubmenuSlug = findFirstSubmenuSlug(nodes);

        hideSidebarSkeleton();
        scheduleStickyUpdate();

        return { hasSidebar: true, firstSubmenuSlug };
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
                              e.target.closest('a[href*="h-"]') ||
                              e.target.closest('a[href*="header_menu_id"]');

            if (headerLink) {
                e.preventDefault();

                let menuUuid = headerLink.getAttribute('data-menu-uuid') ||
                              headerLink.getAttribute('data-header-uuid');

                if (!menuUuid) {
                    const href = headerLink.getAttribute('href') || '';
                    const url = new URL(href, window.location.origin);
                    
                    // Look for h-{uuid} parameter
                    for (const [key, value] of url.searchParams.entries()) {
                        if (isHeaderMenuUuidToken(key)) {
                            menuUuid = extractUuidFromHeaderToken(key);
                            break;
                        }
                        if (isHeaderMenuUuidToken(value)) {
                            menuUuid = extractUuidFromHeaderToken(value);
                            break;
                        }
                    }
                    
                    // Legacy fallback
                    if (!menuUuid) {
                        const legacyId = url.searchParams.get('header_menu_id');
                        if (legacyId) {
                            menuUuid = legacyId;
                        }
                    }
                }

                if (menuUuid) {
                    const currentUrl = new URL(window.location);
                    
                    // Clear existing header params
                    const newParams = buildSearchWithHeaderUuid(currentUrl.searchParams, menuUuid);
                    
                    currentUrl.search = newParams.toString();
                    currentUrl.searchParams.delete('submenu');
                    window.location.href = currentUrl.toString();
                }
            }
        });
    }

    async function init(){
        hideError();
        setupStickyObservers();

        resetSidebarPreloadState();

        const slugCandidate = getSlugCandidate();
        const currentLower = toLowerSafe(slugCandidate);

        if (!slugCandidate) {
            elLoading.classList.add('d-none');
            showError("No page slug provided. Use /link/page/<slug>  OR  /page/<slug>  OR  ?slug=about-us");
            hideSidebarSkeleton();
            sidebarCol.classList.add('d-none');
            sidebarCol.classList.remove('dp-side-preload');
            contentCol.className = 'col-12';
            scheduleStickyUpdate();
            return;
        }

        await loadPublicDepartmentsMap();

        showLoading('Loading page…');

        const headerFromUrl = readHeaderMenuUuidFromUrl();
        const page = await resolvePublicPage(slugCandidate, headerFromUrl);

        if (!page) {
            showNotFound(slugCandidate);
            hideSidebarSkeleton();
            sidebarCol.classList.add('d-none');
            sidebarCol.classList.remove('dp-side-preload');
            contentCol.className = 'col-12';
            scheduleStickyUpdate();
            return;
        }

        window.__DP_PAGE_SCOPE__ = {
            page_id: pick(page, ['id']) || null,
            page_slug: pick(page, ['slug']) || null,
            header_menu_uuid: (isUuid(headerFromUrl) ? headerFromUrl : null) || null,
            requested_header_menu_uuid: (isUuid(headerFromUrl) ? headerFromUrl : null) || null,
            header_menu_id: (!isUuid(headerFromUrl) ? parseInt(headerFromUrl) : null) || null,
            requested_header_menu_id: (!isUuid(headerFromUrl) ? parseInt(headerFromUrl) : null) || null
        };

        elTitle.textContent = pick(page, ['title']) || slugCandidate;
        setMeta('');

        const html = pick(page, ['content_html']) || '';
        setInnerHTMLWithScripts(elHtml, html || '<p class="text-muted mb-0">No content_html returned from pages resolve API.</p>');

        elLoading.classList.add('d-none');
        elWrap.classList.remove('d-none');
        if (elNotFound) elNotFound.classList.add('d-none');
        if (elComingSoon) elComingSoon.classList.add('d-none');

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