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
        .hallienz-side{
            border-radius: 18px;
            overflow: hidden;
            background: var(--surface, #fff);
            border: 1px solid var(--line-strong, #e6c8ca);
            box-shadow: var(--shadow-2, 0 8px 22px rgba(0,0,0,.08));
        }
        .hallienz-side__head{
            background: var(--primary-color, #9E363A);
            color: #fff;
            font-weight: 700;
            padding: 14px 16px;
            font-size: 20px;
            letter-spacing: .2px;
        }
        .hallienz-side__list{
            margin: 0;
            padding: 6px 0 0;
            list-style: none;
            border-bottom: 0.5rem solid #9E363A;
        }
        .hallienz-side__item{ position: relative; }

        .hallienz-side__row{ display:flex; align-items:stretch; }

        .hallienz-side__link{
            flex: 1 1 auto;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            text-decoration: none;
            color: #0b5ed7;
            border-bottom: 1px dotted rgba(0,0,0,.18);
            transition: background .25s ease, color .25s ease;
            min-width: 0;
        }
        .hallienz-side__link:hover{
            background: rgba(158, 54, 58, .06);
            color: var(--primary-color, #9E363A);
        }
        .hallienz-side__link.active{
            background: rgba(158, 54, 58, .10);
            color: var(--primary-color, #9E363A);
            font-weight: 700;
        }
        .hallienz-side__text{
            display:block;
            overflow:hidden;
            text-overflow:ellipsis;
            white-space:nowrap;
        }

        .hallienz-side__toggle{
            flex: 0 0 auto;
            width: 44px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            border: none;
            background: transparent;
            color: rgba(0,0,0,.55);
            border-bottom: 1px dotted rgba(0,0,0,.18);
            transition: background .25s ease, color .25s ease, transform .25s ease;
            cursor:pointer;
        }
        .hallienz-side__toggle:hover{
            background: rgba(158, 54, 58, .06);
            color: var(--primary-color, #9E363A);
        }
        .hallienz-side__toggle i{ transition: transform .22s ease; }
        .hallienz-side__item.open > .hallienz-side__row .hallienz-side__toggle i{ transform: rotate(90deg); }

        .hallienz-side__children{
            list-style:none;
            margin: 0;
            padding: 0;
            display:none;
            border-bottom: 1px dotted rgba(0,0,0,.18);
            background: rgba(158, 54, 58, .03);
        }
        .hallienz-side__item.open > .hallienz-side__children{ display:block; }

        .hallienz-side__children .hallienz-side__link{
            border-bottom: 1px dotted rgba(0,0,0,.14);
            font-size: 14px;
        }
        .hallienz-side__children .hallienz-side__toggle{
            border-bottom: 1px dotted rgba(0,0,0,.14);
        }

        /* ===== Content Card ===== */
        .dp-card{
            border-radius: 18px;
            background: var(--surface, #fff);
            border: 1px solid var(--line-strong, #e6c8ca);
            box-shadow: var(--shadow-2, 0 8px 22px rgba(0,0,0,.08));
            padding: 18px;
        }
        .dp-title{ font-weight: 800; margin: 0 0 12px; color: var(--ink, #111); }
        .dp-muted{ color: var(--muted-color, #6b7280); font-size: 13px; margin-bottom: 12px; }
        .dp-loading{ padding: 28px 0; text-align: center; color: var(--muted-color, #6b7280); }

        .dp-iframe{
            border:1px solid rgba(0,0,0,.1);
            border-radius:12px;
            overflow:hidden;
        }
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
            <aside class="col-lg-3 d-none" id="sidebarCol" aria-label="Page Sidebar">
                <div class="hallienz-side">
                    <div class="hallienz-side__head" id="sidebarHeading">The Hallienz</div>
                    <ul class="hallienz-side__list" id="submenuList"></ul>
                </div>
            </aside>

            {{-- Content --}}
            <section class="col-12" id="contentCol">
                <div class="dp-card">
                    <div class="dp-loading" id="pageLoading">
                        <div class="spinner-border" role="status" aria-label="Loading"></div>
                        <div class="mt-2" id="loadingText">Loading page…</div>
                    </div>

                    {{-- fallback error box (for non-404 errors too) --}}
                    <div id="pageError" class="alert alert-danger d-none mb-0"></div>

                    {{-- ✅ Not Found Partial Holder (NEW) --}}
                    <div id="pageNotFoundWrap" class="d-none">
                        @include('partials.pageNotFound')
                    </div>

                    <div id="pageWrap" class="d-none">
                        <div class="dp-muted" id="pageMeta"></div>
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
    // ✅ auth cache + global API auth injection (fixes "stuck at loading")
    // ------------------------------------------------------------------
    const TOKEN = (localStorage.getItem('token') || sessionStorage.getItem('token') || '');
    const ROLE  = (sessionStorage.getItem('role') || localStorage.getItem('role') || '');

    window.__AUTH_CACHE__ = window.__AUTH_CACHE__ || { token: TOKEN, role: ROLE };

    // Patch fetch to auto attach Authorization for any /api/* calls (modules use fetch)
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
            const res = await origFetch(input, init);
            return res;
        };
    })();

    // Patch XMLHttpRequest to auto attach Authorization (axios/jQuery often use XHR)
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
    // DOM refs
    // ------------------------------------------------------------------
    const elLoading   = document.getElementById('pageLoading');
    const elError     = document.getElementById('pageError');
    const elNotFound  = document.getElementById('pageNotFoundWrap'); // ✅ NEW
    const elWrap      = document.getElementById('pageWrap');
    const elTitle     = document.getElementById('pageTitle');
    const elMeta      = document.getElementById('pageMeta');
    const elHtml      = document.getElementById('pageHtml');
    const elLoadingText = document.getElementById('loadingText');

    const sidebarCol  = document.getElementById('sidebarCol');
    const contentCol  = document.getElementById('contentCol');
    const submenuList = document.getElementById('submenuList');
    const sidebarHead = document.getElementById('sidebarHeading');

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

    function showNotFound(slug){ // ✅ NEW
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
        const t = withTimeout(20000); // 20s timeout
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
        } finally {
            t.cancel();
        }
    }

    function cleanPathSegments(){
        return window.location.pathname.replace(/^\/+|\/+$/g,'').split('/').filter(Boolean);
    }

    // ✅ NEW: submenu is stored in PATH like: /page/test-page&submenu=test?d-uuid
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

        const first = strip(segs[0] || '');

        if (segs.length === 1 && first.toLowerCase() === 'test') return '';
        if (segs.length >= 2 && first.toLowerCase() === 'test') return strip(segs[1]);

        const lastRaw = segs[segs.length - 1] || '';
        const last = strip(lastRaw);

        if (last.toLowerCase() === 'test') return '';
        return last;
    }

    function pick(obj, keys){
        for (const k of keys){
            if (obj && obj[k] !== undefined && obj[k] !== null) return obj[k];
        }
        return null;
    }

    function boolYes(v){
        if (v === true || v === 1) return true;
        const s = String(v ?? '').toLowerCase().trim();
        return (s === 'yes' || s === 'y' || s === 'true' || s === '1');
    }

    function toLowerSafe(v){
        return String(v ?? '').toLowerCase().trim();
    }

    function safeCssEscape(s){
        try { return CSS.escape(s); } catch(e){ return String(s).replace(/["\\]/g, '\\$&'); }
    }

    // ------------------------------------------------------------------
    // ✅ Dept token + mapping helpers (uses /api/public/departments)
    // ------------------------------------------------------------------
    function isDeptToken(x){
        return /^d-[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(String(x || '').trim());
    }
    function deptTokenFromUuid(uuid){
        const u = String(uuid || '').trim();
        if (!u) return '';
        return u.startsWith('d-') ? u : ('d-' + u);
    }

    // Read existing ?d-uuid from current URL (works even if it is stored as a key)
    function readDeptTokenFromUrl(){
        try{
            const usp = new URLSearchParams(window.location.search || '');
            for (const [k, v] of usp.entries()){
                if (isDeptToken(k)) return k;               // ?d-uuid (as key)
                if (k === 'd' && isDeptToken(v)) return v;  // (optional) ?d=d-uuid
            }
        }catch(e){}

        const raw = String(window.location.search || '').replace(/^\?/, '').split('&').filter(Boolean);
        for (const part of raw){
            const decoded = decodeURIComponent(part);
            if (isDeptToken(decoded)) return decoded;
        }
        return '';
    }

    // ✅ query builder: dept token must be FIRST => "?d-uuid&foo=bar"
    // (submenu is NOT kept here anymore)
    function buildSearchWithDept(params, deptToken){
        // remove any dept token keys
        for (const k of Array.from(params.keys())){
            if (isDeptToken(k)) params.delete(k);
        }

        // remove any legacy submenu query param
        params.delete('submenu');

        // remove optional legacy d= token
        if (params.get('d') && isDeptToken(params.get('d'))) params.delete('d');

        const normal = params.toString(); // key=value params
        const t = String(deptToken || '').trim();

        const parts = [];
        if (t && isDeptToken(t)) parts.push(encodeURIComponent(t)); // ✅ first => "?d-uuid"
        if (normal) parts.push(normal);                             // then => "&foo=bar"

        return parts.length ? ('?' + parts.join('&')) : '';
    }

    // ✅ Push state:
    // URL becomes: /page/<slug>&submenu=<submenu>?d-<uuid>
    function pushUrlStateSubmenu(submenuSlug, deptTokenMaybe){
        const u = new URL(window.location.href);

        // 1) PATH: store submenu in pathname
        let path = stripSubmenuFromPath(u.pathname);
        const s = String(submenuSlug || '').trim();
        if (s) path += '&submenu=' + encodeURIComponent(s);

        // 2) QUERY: dept token first + keep other params (except submenu/dept tokens)
        const params = new URLSearchParams(u.search);

        const deptTokenFinal = (deptTokenMaybe && isDeptToken(deptTokenMaybe))
            ? deptTokenMaybe
            : readDeptTokenFromUrl();

        const search = buildSearchWithDept(params, deptTokenFinal);
        const nextUrl = path + search + u.hash;

        window.history.pushState({}, '', nextUrl);
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
        // cache
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

    // ✅ Resolve page (public)
    async function resolvePublicPage(slug){
        const raw = String(slug || '').trim();
        if (!raw) return null;

        const u = new URL(API_BASE + '/public/pages/resolve', window.location.origin);
        u.searchParams.set('slug', raw);

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

    /* ==============================
     * HTML injection helpers
     * ============================== */

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

        // Block common duplicates that break your header theme when injected last
        if (h.includes('bootstrap')) return true;
        if (h.includes('font-awesome') || h.includes('fontawesome')) return true;
        if (h.includes('/assets/css/common/main.css')) return true;

        // Block common CDNs (module should not re-add global libs)
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

            // Skip duplicates + external libs
            if (isBlockedStyleHref(href)) return;

            // Allow same-origin styles only (relative URLs are same-origin)
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

        // Block duplicates that are already loaded globally in test.blade.php
        if (s.includes('bootstrap')) return true;
        if (s.includes('sweetalert2')) return true;

        // Block common CDNs
        if (s.includes('cdn.jsdelivr.net')) return true;
        if (s.includes('cdnjs.cloudflare.com')) return true;

        return false;
    };

    const scripts = tpl.content.querySelectorAll('script');

    runWithDomReadyShim(() => {
        scripts.forEach((oldScript) => {
            const src = oldScript.getAttribute('src') || '';

            // If script has src, enforce safe rules
            if (src){
                if (isBlockedScriptSrc(src)) return;

                // Allow same-origin scripts only
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


    /* ==============================
     * Submenu AJAX loader
     * ============================== */

    async function loadSubmenuRightContent(submenuSlug, pageScope){
        const sslug = String(submenuSlug || '').trim();
        if (!sslug) return;

        showLoading('Loading submenu…');

        const u = new URL(API_BASE + '/public/page-submenus/render', window.location.origin);
        u.searchParams.set('slug', sslug);

        if (pageScope?.page_id) u.searchParams.set('page_id', pageScope.page_id);
        else if (pageScope?.page_slug) u.searchParams.set('page_slug', pageScope.page_slug);

        const r = await fetchJsonWithStatus(u.toString());

        if (!r.ok) {
            const msg = (r.data && (r.data.message || r.data.error))
                ? (r.data.message || r.data.error)
                : ('Load failed: ' + r.status);

            if ((r.status === 401 || r.status === 403) && !TOKEN) {
                showError(msg + ' (Login token missing)');
            } else {
                showError(msg);
            }
            return;
        }

        const payload = r.data || {};
        const type = payload.type;

        elTitle.textContent = payload.title || 'Dynamic Page';
        elMeta.textContent = [
            payload?.meta?.submenu_slug ? ('Submenu: ' + payload.meta.submenu_slug) : null,
            payload?.meta?.page_slug ? ('Page: ' + payload.meta.page_slug) : null,
            payload?.meta?.includable ? ('View: ' + payload.meta.includable) : null,
        ].filter(Boolean).join(' • ');

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

            // ✅ store department_id from tree node + optional uuid if backend sends it
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

                // ✅ resolve dept uuid from /api/public/departments and push: /...&submenu=... ?d-uuid
                const deptId = parseInt(a.dataset.deptId || '0', 10);
                const deptUuid =
                    (a.dataset.deptUuid || '').trim() ||
                    (deptId > 0 ? (window.__DP_DEPT_ID_UUID_MAP__?.[String(deptId)] || '') : '');

                const deptToken = deptTokenFromUuid(deptUuid);

                // keep dept token if exists; add if we have it
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

    async function loadSidebarIfAny(page, currentLower){
        const submenuExists = boolYes(pick(page, ['submenu_exists']));
        if (!submenuExists) {
            sidebarCol.classList.add('d-none');
            contentCol.className = 'col-12';
            return;
        }

        const pageId   = pick(page, ['id']);
        const pageSlug = pick(page, ['slug']);

        const treeUrl = new URL(API_BASE + '/public/page-submenus/tree', window.location.origin);
        if (pageId) treeUrl.searchParams.set('page_id', pageId);
        else if (pageSlug) treeUrl.searchParams.set('page_slug', pageSlug);

        const r = await fetchJsonWithStatus(treeUrl.toString());
        if (!r.ok) {
            sidebarCol.classList.add('d-none');
            contentCol.className = 'col-12';
            return;
        }

        const nodes = normalizeTree(r.data);
        if (!nodes.length) {
            sidebarCol.classList.add('d-none');
            contentCol.className = 'col-12';
            return;
        }

        sidebarCol.classList.remove('d-none');
        contentCol.className = 'col-12 col-lg-9';

        submenuList.innerHTML = '';
        sidebarHead.textContent = 'The Hallienz';

        // Top link = reload page html
        const topLi = document.createElement('li');
        topLi.className = 'hallienz-side__item';

        const topRow = document.createElement('div');
        topRow.className = 'hallienz-side__row';

        const topA = document.createElement('a');
        topA.className = 'hallienz-side__link';
        topA.style.paddingLeft = '14px';
        topA.href = 'javascript:void(0)';

        const activeSlug = String(pick(page, ['slug']) || currentLower || '').trim();
        topA.innerHTML = '<span class="hallienz-side__text">Home – ' + (pick(page, ['title']) || 'Page') + '</span>';

        topA.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            clearModuleAssets();

            document.querySelectorAll('.hallienz-side__link.active').forEach(x => x.classList.remove('active'));
            topA.classList.add('active');

            const slugCandidate = activeSlug || currentLower;
            if (!slugCandidate) return;

            showLoading('Loading page…');

            const page2 = await resolvePublicPage(slugCandidate);
            if (!page2) {
                showNotFound(slugCandidate);
                return;
            }

            const title = pick(page2, ['title']) || slugCandidate;
            const shortcode = pick(page2, ['shortcode']);
            const slug = pick(page2, ['slug']);

            elTitle.textContent = title;
            elMeta.textContent = [
                slug ? ('Slug: ' + slug) : null,
                shortcode ? ('Shortcode: ' + shortcode) : null
            ].filter(Boolean).join(' • ');

            const html = pick(page2, ['content_html']) || '';
            setInnerHTMLWithScripts(elHtml, html || '<p class="text-muted mb-0">No content_html returned from pages resolve API.</p>');

            elLoading.classList.add('d-none');
            elWrap.classList.remove('d-none');
            if (elNotFound) elNotFound.classList.add('d-none');
            hideError();

            // ✅ remove submenu from PATH but keep dept token (?d-uuid)
            pushUrlStateSubmenu('', null);
        });

        topRow.appendChild(topA);
        topLi.appendChild(topRow);
        submenuList.appendChild(topLi);

        renderTree(nodes, currentLower, submenuList, 0);
    }

    async function init(){
        hideError();

        const slugCandidate = getSlugCandidate();
        const currentLower = toLowerSafe(slugCandidate);

        if (!slugCandidate) {
            elLoading.classList.add('d-none');
            showError("No page slug provided. Use /test?slug=about-us (or /test/about-us if your route supports it).");
            return;
        }

        // ✅ preload department map (id -> uuid) using /api/public/departments
        await loadPublicDepartmentsMap();

        showLoading('Loading page…');

        const page = await resolvePublicPage(slugCandidate);
        if (!page) {
            showNotFound(slugCandidate);
            return;
        }

        window.__DP_PAGE_SCOPE__ = {
            page_id: pick(page, ['id']) || null,
            page_slug: pick(page, ['slug']) || null
        };

        const title = pick(page, ['title']) || slugCandidate;
        const shortcode = pick(page, ['shortcode']);
        const slug = pick(page, ['slug']);

        elTitle.textContent = title;
        elMeta.textContent = [
            slug ? ('Slug: ' + slug) : null,
            shortcode ? ('Shortcode: ' + shortcode) : null,
            !TOKEN ? 'Token: missing (modules may fail to load)' : null,
            !ROLE ? 'Role: missing (some modules may hide UI)' : null,
        ].filter(Boolean).join(' • ');

        const html = pick(page, ['content_html']) || '';
        setInnerHTMLWithScripts(elHtml, html || '<p class="text-muted mb-0">No content_html returned from pages resolve API.</p>');

        elLoading.classList.add('d-none');
        elWrap.classList.remove('d-none');
        if (elNotFound) elNotFound.classList.add('d-none');

        await loadSidebarIfAny(page, currentLower);

        // ✅ auto-load submenu from PATH: /...&submenu=xxx?d-uuid
        let submenuSlug = (readSubmenuFromPathname() || '').trim();

        // (optional backward compat: old ?submenu=xxx)
        if (!submenuSlug){
            const qs = new URLSearchParams(window.location.search);
            submenuSlug = (qs.get('submenu') || '').trim();
        }

        if (submenuSlug) {
            const link = document.querySelector('.hallienz-side__link[data-submenu-slug="' + safeCssEscape(submenuSlug) + '"]');
            if (link) {
                document.querySelectorAll('.hallienz-side__link.active').forEach(x => x.classList.remove('active'));
                link.classList.add('active');
            }
            await loadSubmenuRightContent(submenuSlug, window.__DP_PAGE_SCOPE__);
        }
    }

    init().catch((e) => {
        console.error(e);
        showError(e?.message || 'Something went wrong.');
    });

})();
</script>


@stack('scripts')
</body>
</html>
