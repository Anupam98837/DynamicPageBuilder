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
        .hallienz-side__list{ margin: 0; padding: 6px 0 0; list-style: none; border-bottom: 0.5rem solid #9E363A;}
        .hallienz-side__item{ position: relative; }
        .hallienz-side__link{
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 14px;
            text-decoration: none;
            color: #0b5ed7;
            border-bottom: 1px dotted rgba(0,0,0,.18);
            transition: background .25s ease, color .25s ease;
        }
        .hallienz-side__link:hover{
            background: rgba(158, 54, 58, .06);
            color: var(--primary-color, #9E363A);
        }
        .hallienz-side__link.active{
            background: rgba(158, 54, 58, .08);
            color: var(--primary-color, #9E363A);
            font-weight: 700;
        }
        .hallienz-side__right{
            display:inline-flex;
            align-items:center;
            gap: 8px;
            opacity:.75;
            flex-shrink:0;
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

        /* Footer */
        .footer{ background:#f8f9fa; padding:2rem 0; margin-top:3rem; }
    </style>
</head>
<body>

{{-- Header --}}
    @include('modules.header.header')

<main class="page-content">
    <div class="container">
        <div class="row g-4 align-items-start" id="dpRow">
            {{-- Sidebar (hidden unless submenu exists) --}}
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
                        <div class="mt-2">Loading page…</div>
                    </div>

                    <div id="pageError" class="alert alert-danger d-none mb-0"></div>

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
    const API_BASE = @json($apiBase);

    const elLoading   = document.getElementById('pageLoading');
    const elError     = document.getElementById('pageError');
    const elWrap      = document.getElementById('pageWrap');
    const elTitle     = document.getElementById('pageTitle');
    const elMeta      = document.getElementById('pageMeta');
    const elHtml      = document.getElementById('pageHtml');

    const sidebarCol  = document.getElementById('sidebarCol');
    const contentCol  = document.getElementById('contentCol');
    const submenuList = document.getElementById('submenuList');
    const sidebarHead = document.getElementById('sidebarHeading');

    function showError(msg){
        elError.textContent = msg;
        elError.classList.remove('d-none');
        elLoading.classList.add('d-none');
        elWrap.classList.add('d-none');
    }
    function hideError(){
        elError.classList.add('d-none');
        elError.textContent = '';
    }

    async function fetchJsonWithStatus(url){
        const res = await fetch(url, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });

        let data = null;
        try { data = await res.json(); } catch(e) {}

        return { ok: res.ok, status: res.status, data };
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

    // ✅ Resolve PAGE (pages table), not submenu
    async function resolvePublicPage(slug){
        const raw = String(slug || '').trim();
        if (!raw) return null;

        const u = new URL(API_BASE + '/public/pages/resolve', window.location.origin);
        u.searchParams.set('slug', raw);

        const r = await fetchJsonWithStatus(u.toString());

        if (r.ok) {
            return r.data?.page || null;
        }

        if (r.status === 404) return null;

        const msg = (r.data && (r.data.message || r.data.error)) ? (r.data.message || r.data.error) : ('Resolve failed: ' + r.status);
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

    function makeHref(node){
        const base = @json(url('/'));
        const slug = pick(node, ['slug','page_slug','selfslug','self_slug']);
        const shortcode = pick(node, ['shortcode','page_shortcode']);
        const val = slug || shortcode;
        if (!val) return 'javascript:void(0)';
        return base.replace(/\/+$/,'') + '/' + String(val).replace(/^\/+/,'');
    }

    function isActiveNode(node, current){
        const slug = (pick(node, ['slug','page_slug','selfslug','self_slug']) || '').toString().toLowerCase();
        const shortcode = (pick(node, ['shortcode','page_shortcode']) || '').toString().toLowerCase();
        return (slug && slug === current) || (shortcode && shortcode === current);
    }

    function renderNodes(nodes, currentLower, parentUl){
        nodes.forEach((node) => {
            const li = document.createElement('li');
            li.className = 'hallienz-side__item';

            const a = document.createElement('a');
            a.className = 'hallienz-side__link';
            a.href = makeHref(node);

            const title = pick(node, ['title','name','label']) || 'Untitled';
            a.appendChild(document.createTextNode(title));

            const right = document.createElement('span');
            right.className = 'hallienz-side__right';

            const children = normalizeTree(pick(node, ['children','nodes','items','submenus']));
            if (children.length){
                const ico = document.createElement('i');
                ico.className = 'fa-solid fa-chevron-right';
                right.appendChild(ico);
            }

            a.appendChild(right);

            if (isActiveNode(node, currentLower)) a.classList.add('active');

            li.appendChild(a);
            parentUl.appendChild(li);
        });
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

        const topLi = document.createElement('li');
        topLi.className = 'hallienz-side__item';
        const topA = document.createElement('a');
        topA.className = 'hallienz-side__link';

        const base = @json(url('/'));
        const activeSlug = (pick(page, ['slug']) || currentLower || '').toString();
        topA.href = base.replace(/\/+$/,'') + '/' + activeSlug.replace(/^\/+/,'');
        topA.textContent = 'Home – ' + (pick(page, ['title']) || 'Page');

        topLi.appendChild(topA);
        submenuList.appendChild(topLi);

        sidebarHead.textContent = 'The hallienz';

        renderNodes(nodes, currentLower, submenuList);
    }

    async function init(){
        hideError();

        const slugCandidate = getSlugCandidate();
        const currentLower = (slugCandidate || '').toString().toLowerCase();

        if (!slugCandidate) {
            elLoading.classList.add('d-none');
            showError("No page slug provided. Use /test?slug=about-us (or /test/about-us if your route supports it).");
            return;
        }

        // ✅ resolve page
        const page = await resolvePublicPage(slugCandidate);
        if (!page) {
            showError('Page not found for: ' + slugCandidate);
            return;
        }

        // render content
        const title = pick(page, ['title']) || slugCandidate;
        const shortcode = pick(page, ['shortcode']);
        const slug = pick(page, ['slug']);

        elTitle.textContent = title;
        elMeta.textContent = [
            slug ? ('Slug: ' + slug) : null,
            shortcode ? ('Shortcode: ' + shortcode) : null
        ].filter(Boolean).join(' • ');

        const html = pick(page, ['content_html']) || '';
        elHtml.innerHTML = html || '<p class="text-muted mb-0">No content_html returned from pages resolve API.</p>';

        elLoading.classList.add('d-none');
        elWrap.classList.remove('d-none');

        await loadSidebarIfAny(page, currentLower);
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
