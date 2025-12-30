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

        .footer{ background:#f8f9fa; padding:2rem 0; margin-top:3rem; }
    </style>
</head>
<body>

{{-- Main Header --}}
@include('landing.components.mainHeader')

{{-- Header --}}
@include('landing.components.header')

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

<footer class="footer">
    <div class="container text-center">
        <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name', 'Institution') }}. All rights reserved.</p>
    </div>
</footer>

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
        // optional: set some text if your partial supports it
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

    function getSlugCandidate(){
        const qs = new URLSearchParams(window.location.search);
        const qSlug = qs.get('slug') || qs.get('page_slug') || qs.get('selfslug') || qs.get('shortcode');
        if (qSlug && String(qSlug).trim()) return String(qSlug).trim();

        const segs = cleanPathSegments();

        if (segs.length === 1 && segs[0].toLowerCase() === 'test') return '';
        if (segs.length >= 2 && segs[0].toLowerCase() === 'test') return segs[1];

        const last = segs[segs.length - 1] || '';
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

        // re-execute inline scripts inside html
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

        [...tpl.content.children].forEach((node) => {
            const tag = (node.tagName || '').toUpperCase();
            if (!['LINK','STYLE','META'].includes(tag)) return;

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

        const scripts = tpl.content.querySelectorAll('script');

        runWithDomReadyShim(() => {
            scripts.forEach((oldScript) => {
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

            // Helpful hint if auth is missing
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

                const url = new URL(window.location.href);
                url.searchParams.set('submenu', sslug);
                window.history.pushState({}, '', url.toString());
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

            const url = new URL(window.location.href);
            url.searchParams.delete('submenu');
            window.history.pushState({}, '', url.toString());
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

        showLoading('Loading page…');

        const page = await resolvePublicPage(slugCandidate);
        if (!page) {
            // ✅ show partial instead of error
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

        // auto-load submenu if url has ?submenu=
        const qs = new URLSearchParams(window.location.search);
        const submenuSlug = (qs.get('submenu') || '').trim();
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
