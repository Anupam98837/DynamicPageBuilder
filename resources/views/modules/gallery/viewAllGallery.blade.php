{{-- resources/views/landing/gallery-all.blade.php --}}
<style>
  /* =========================================================
    ✅ Gallery All (Scoped / No :root / No global body rules)
    - All CSS + vars are scoped to .gxa-wrap to prevent conflicts
    - Pinterest-style masonry using CSS Grid + auto-rows + JS row-span
    - Lightbox (click view) kept as-is (only IDs/classes renamed)
  ========================================================= */

  .gxa-wrap{
    /* ✅ scoped design tokens (NO :root) */
    --gxa-brand: var(--primary-color, #8f2f2f);
    --gxa-ink: #0f172a;
    --gxa-muted: #64748b;
    --gxa-bg: var(--page-bg, #ffffff);
    --gxa-card: var(--surface, #ffffff);
    --gxa-line: var(--line-soft, rgba(15,23,42,.10));
    --gxa-shadow: 0 10px 24px rgba(2,6,23,.08);

    max-width: 1320px; /* ✅ a little broader than reference */
    margin: 18px auto 54px;
    padding: 0 12px;
    background: transparent;
    position: relative;
    overflow: visible;
  }

  /* Header */
  .gxa-head{
    background: var(--gxa-card);
    border: 1px solid var(--gxa-line);
    border-radius: 16px;
    box-shadow: var(--gxa-shadow);
    padding: 14px 16px;
    margin-bottom: 16px;

    display:flex;
    gap: 12px;
    align-items: flex-end;
    justify-content: space-between;
    flex-wrap: wrap;
  }

  .gxa-title{
    margin: 0;
    font-weight: 950;
    letter-spacing: .2px;
    color: var(--gxa-ink);
    font-size: 28px;
  }
  .gxa-sub{
    margin: 6px 0 0;
    color: var(--gxa-muted);
    font-size: 14px;
  }

  .gxa-tools{
    display:flex;
    gap: 10px;
    align-items:center;
    flex-wrap: wrap;
  }

  .gxa-search{
    position: relative;
    min-width: 300px;
    max-width: 520px;
    flex: 1 1 300px;
  }
  .gxa-search i{
    position:absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    opacity: .65;
    color: var(--gxa-muted);
  }
  .gxa-search input{
    width:100%;
    border-radius: 14px;
    padding: 11px 12px 11px 42px;
    border: 1px solid var(--gxa-line);
    background: var(--gxa-card);
    color: var(--gxa-ink);
    outline: none;
  }
  .gxa-search input:focus{
    border-color: rgba(201,75,80,.55);
    box-shadow: 0 0 0 4px rgba(201,75,80,.18);
  }

  .gxa-chip{
    display:flex;
    align-items:center;
    gap: 8px;
    padding: 10px 12px;
    border-radius: 999px;
    border: 1px solid var(--gxa-line);
    background: var(--gxa-card);
    box-shadow: 0 8px 18px rgba(2,6,23,.06);
    color: var(--gxa-ink);
    font-size: 13px;
    font-weight: 900;
    white-space: nowrap;
  }

  /* =========================================================
    ✅ Pinterest-style Masonry
    - Grid with tiny auto-rows
    - JS computes grid-row-end span per card after images load
  ========================================================= */
  .gxa-grid{
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    grid-auto-rows: 10px;      /* ✅ masonry base row */
    gap: 18px;
    align-items: start;
  }

  .gxa-item{
    position: relative;
    overflow: hidden;
    border-radius: 16px;
    background: #fff;
    border: 1px solid rgba(2,6,23,.08);
    box-shadow: 0 10px 24px rgba(2,6,23,.08);
    cursor: pointer;
    user-select: none;
    transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
    will-change: transform;
  }
  .gxa-item:hover{
    transform: translateY(-2px);
    box-shadow: 0 16px 34px rgba(2,6,23,.12);
    border-color: rgba(158,54,58,.22);
  }

  .gxa-item img{
    width: 100%;
    height: auto;           /* ✅ natural heights (Pinterest feel) */
    display:block;
  }

  /* overlay meta (always visible like your existing) */
  .gxa-meta{
    position:absolute;
    left:0; right:0; bottom:0;
    padding: 10px 10px 9px;
    color: #fff;
    background: linear-gradient(180deg, rgba(2,6,23,0) 0%, rgba(2,6,23,.55) 28%, rgba(2,6,23,.82) 100%);
    pointer-events: none; /* keep tile click working */
  }
  .gxa-meta__title{
    font-weight: 950;
    font-size: 13px;
    letter-spacing: .2px;
    line-height: 1.18;
    text-shadow: 0 2px 10px rgba(0,0,0,.35);
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }
  .gxa-meta__desc{
    margin-top: 4px;
    font-size: 12px;
    opacity: .92;
    line-height: 1.25;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-shadow: 0 2px 10px rgba(0,0,0,.35);
  }
  .gxa-meta__tags{
    margin-top: 6px;
    display:flex;
    gap: 6px;
    flex-wrap: wrap;
  }
  .gxa-tag{
    font-size: 11px;
    font-weight: 950;
    padding: 5px 8px;
    border-radius: 999px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.18);
    backdrop-filter: blur(6px);
    max-width: 100%;
    overflow:hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .gxa-tag.more{
    background: rgba(201,75,80,.22);
    border-color: rgba(201,75,80,.35);
  }

  /* Loading / empty */
  .gxa-state{
    background: var(--gxa-card);
    border: 1px solid var(--gxa-line);
    border-radius: 16px;
    box-shadow: var(--gxa-shadow);
    padding: 18px;
    color: var(--gxa-muted);
    text-align:center;
  }

  /* Skeleton (also masonry) */
  .gxa-skeleton{
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    grid-auto-rows: 10px;
    gap: 18px;
  }
  .gxa-sk{
    border-radius: 16px;
    border: 1px solid var(--gxa-line);
    background: #fff;
    overflow:hidden;
    position:relative;
    box-shadow: 0 10px 24px rgba(2,6,23,.08);
  }
  .gxa-sk:before{
    content:'';
    position:absolute; inset:0;
    transform: translateX(-60%);
    background: linear-gradient(90deg, transparent, rgba(148,163,184,.22), transparent);
    animation: gxaSkMove 1.15s ease-in-out infinite;
  }
  @keyframes gxaSkMove{ to{ transform: translateX(60%);} }

  /* Pagination */
  .gxa-pagination{
    display:flex;
    justify-content:center;
    margin-top: 18px;
  }
  .gxa-pagination .gxa-pager{
    display:flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items:center;
    justify-content:center;
    padding: 10px;
  }
  .gxa-pagebtn{
    border:1px solid var(--gxa-line);
    background: var(--gxa-card);
    color: var(--gxa-ink);
    border-radius: 12px;
    padding: 9px 12px;
    font-size: 13px;
    font-weight: 950;
    box-shadow: 0 8px 18px rgba(2,6,23,.06);
    cursor:pointer;
    user-select:none;
  }
  .gxa-pagebtn:hover{ background: rgba(2,6,23,.03); }
  .gxa-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
  .gxa-pagebtn.active{
    background: rgba(201,75,80,.12);
    border-color: rgba(201,75,80,.35);
    color: var(--gxa-brand);
  }

  /* Lightbox (kept same behavior, renamed classes/ids) */
  .gxa-lb{
    position: fixed;
    inset: 0;
    background: rgba(2,6,23,.72);
    display:none;
    align-items:center;
    justify-content:center;
    z-index: 9999;
    padding: 18px;
  }
  .gxa-lb.show{ display:flex; }

  .gxa-lb__inner{
    max-width: min(1100px, 96vw);
    max-height: min(86vh, 900px);
    background: #0b1220;
    border: 1px solid rgba(255,255,255,.12);
    box-shadow: 0 22px 60px rgba(0,0,0,.45);
    position: relative;
    display:flex;
    flex-direction: column;
    overflow:hidden;
    border-radius: 14px;
  }
  .gxa-lb__img{
    max-width: min(1100px, 96vw);
    max-height: min(72vh, 820px);
    display:block;
    object-fit: contain;
  }

  .gxa-lb__meta{
    border-top: 1px solid rgba(255,255,255,.10);
    padding: 12px 14px 14px;
    color: rgba(255,255,255,.92);
    background: rgba(255,255,255,.02);
  }
  .gxa-lb__title{
    font-weight: 950;
    font-size: 15px;
    letter-spacing: .2px;
    color:#fff;
    margin: 0 0 6px;
  }
  .gxa-lb__desc{
    margin: 0 0 10px;
    font-size: 13px;
    line-height: 1.35;
    color: rgba(255,255,255,.86);
    white-space: pre-wrap;
  }
  .gxa-lb__tags{
    display:flex;
    gap: 8px;
    flex-wrap: wrap;
  }
  .gxa-lb__tag{
    font-size: 12px;
    font-weight: 900;
    padding: 7px 10px;
    border-radius: 999px;
    background: rgba(255,255,255,.10);
    border: 1px solid rgba(255,255,255,.14);
  }

  .gxa-lb__close{
    position:absolute;
    top: 10px;
    right: 10px;
    width: 40px;
    height: 40px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.18);
    background: rgba(0,0,0,.35);
    color:#fff;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    z-index: 5;
  }
  .gxa-lb__close:hover{ background: rgba(0,0,0,.55); }

  @media (max-width: 640px){
    .gxa-title{ font-size: 24px; }
    .gxa-search{ min-width: 220px; }
    .gxa-lb__img{ max-height: min(66vh, 760px); }
  }
</style>

<div class="gxa-wrap" data-api="{{ url('/api/public/gallery') }}">
  <div class="gxa-head">
    <div>
      <h1 class="gxa-title">Gallery</h1>
      <div class="gxa-sub" id="gxaSub">View all photos</div>
    </div>

    <div class="gxa-tools">
      <div class="gxa-search">
        <i class="fa fa-magnifying-glass"></i>
        <input id="gxaSearch" type="search" placeholder="Search by caption / tag / title…">
      </div>
      <div class="gxa-chip" title="Total results">
        <i class="fa-solid fa-images" style="opacity:.85"></i>
        <span id="gxaCount">—</span>
      </div>
    </div>
  </div>

  <div id="gxaGrid" class="gxa-grid" style="display:none;"></div>

  <div id="gxaSkeleton" class="gxa-skeleton"></div>
  <div id="gxaState" class="gxa-state" style="display:none;"></div>

  <div class="gxa-pagination">
    <div id="gxaPager" class="gxa-pager" style="display:none;"></div>
  </div>
</div>

{{-- Lightbox --}}
<div id="gxaLb" class="gxa-lb" aria-hidden="true">
  <div class="gxa-lb__inner">
    <button class="gxa-lb__close" id="gxaLbClose" aria-label="Close">
      <i class="fa-solid fa-xmark"></i>
    </button>

    <img id="gxaLbImg" class="gxa-lb__img" alt="Gallery image">

    {{-- Meta --}}
    <div class="gxa-lb__meta" id="gxaLbMeta" style="display:none;">
      <div class="gxa-lb__title" id="gxaLbTitle"></div>
      <div class="gxa-lb__desc" id="gxaLbDesc"></div>
      <div class="gxa-lb__tags" id="gxaLbTags"></div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

<script>
(() => {
  if (window.__LANDING_GALLERY_ALL__) return;
  window.__LANDING_GALLERY_ALL__ = true;

  const root = document.querySelector('.gxa-wrap');
  if (!root) return;

  const API = root.getAttribute('data-api') || '/api/public/gallery';
  const $ = (id) => document.getElementById(id);

  const state = { page: 1, perPage: 12, lastPage: 1, total: 0, q: '' };
  let activeController = null;

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  function normalizeUrl(url){
    const u = (url || '').toString().trim();
    if (!u) return '';
    if (/^(data:|blob:|https?:\/\/)/i.test(u)) return u;
    if (u.startsWith('/')) return window.location.origin + u;
    return window.location.origin + '/' + u;
  }

  function pick(obj, keys){
    for (const k of keys){
      const v = obj?.[k];
      if (v !== null && v !== undefined && String(v).trim() !== '') return v;
    }
    return '';
  }

  function normalizeTags(raw){
    let arr = [];
    if (Array.isArray(raw)){
      arr = raw.map(x => (x ?? '').toString().trim()).filter(Boolean);
    } else {
      const s = (raw ?? '').toString().trim();
      if (s){
        if (s.includes('|')) arr = s.split('|');
        else if (s.includes(',')) arr = s.split(',');
        else arr = s.split(/\s+/);
        arr = arr.map(x => x.replace(/^#+/,'').trim()).filter(Boolean);
      }
    }
    const seen = new Set();
    const out = [];
    for (const t of arr){
      const key = t.toLowerCase();
      if (seen.has(key)) continue;
      seen.add(key);
      out.push(t);
    }
    return out;
  }

  function tagsFromItem(it){
    const raw =
      it?.tags ??
      it?.tag_list ??
      it?.keywords ??
      it?.categories ??
      it?.tag ??
      it?.meta?.tags ??
      it?.attachment?.tags;

    return normalizeTags(raw);
  }

  function renderTagChips(tags, max=3){
    const t = Array.isArray(tags) ? tags.filter(Boolean) : [];
    if (!t.length) return '';
    const shown = t.slice(0, max);
    const more = t.length - shown.length;

    let html = shown.map(x => `<span class="gxa-tag">${esc(x)}</span>`).join('');
    if (more > 0) html += `<span class="gxa-tag more">+${more}</span>`;
    return html;
  }

  /* =========================================================
    ✅ Masonry helper (Pinterest feel)
    - Compute row span per item (grid-auto-rows technique)
  ========================================================= */
  function applyMasonry(){
    const grid = $('gxaGrid');
    if (!grid) return;

    const style = window.getComputedStyle(grid);
    const rowH = parseInt(style.getPropertyValue('grid-auto-rows'), 10) || 10;
    const gap  = parseInt(style.getPropertyValue('grid-row-gap'), 10) || 18;

    const items = grid.querySelectorAll('.gxa-item');
    items.forEach((item) => {
      // reset to measure natural height
      item.style.gridRowEnd = 'auto';
      const h = item.getBoundingClientRect().height;
      const span = Math.ceil((h + gap) / (rowH + gap));
      item.style.gridRowEnd = `span ${Math.max(1, span)}`;
    });
  }

  function showSkeleton(){
    const sk = $('gxaSkeleton');
    const st = $('gxaState');
    const grid = $('gxaGrid');
    const pager = $('gxaPager');

    if (grid) grid.style.display = 'none';
    if (pager) pager.style.display = 'none';
    if (st) st.style.display = 'none';

    if (!sk) return;
    sk.style.display = '';

    // ✅ varied skeleton heights for masonry feel
    const heights = [170, 260, 210, 320, 190, 280, 240, 360, 200, 300, 230, 340];
    sk.innerHTML = heights.map(h => `<div class="gxa-sk" style="height:${h}px"></div>`).join('');
  }

  function hideSkeleton(){
    const sk = $('gxaSkeleton');
    if (!sk) return;
    sk.style.display = 'none';
    sk.innerHTML = '';
  }

  async function fetchJson(url){
    if (activeController) activeController.abort();
    activeController = new AbortController();

    const res = await fetch(url, {
      headers: { 'Accept':'application/json' },
      signal: activeController.signal
    });

    const js = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(js?.message || 'Request failed');
    return js;
  }

  function buildUrl(){
    const params = new URLSearchParams();
    params.set('page', String(state.page));
    params.set('per_page', String(state.perPage));
    if (state.q.trim()) params.set('q', state.q.trim());
    params.set('sort', 'created_at');
    params.set('direction', 'desc');
    return API + '?' + params.toString();
  }

  function render(items){
    const grid = $('gxaGrid');
    const st = $('gxaState');
    const count = $('gxaCount');

    if (!grid || !st) return;

    if (!items.length){
      grid.style.display = 'none';
      st.style.display = '';
      st.innerHTML = `
        <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
          <i class="fa-regular fa-face-frown"></i>
        </div>
        No images found.
      `;
      if (count) count.textContent = '0';
      return;
    }

    if (count) count.textContent = String(state.total || items.length);

    st.style.display = 'none';
    grid.style.display = '';

    grid.innerHTML = items.map(it => {
      const img =
        pick(it, ['image_url','image_full_url','url','src','image']) ||
        (it?.attachment?.url ?? '');

      const title =
        pick(it, ['title','name','alt']) ||
        pick(it, ['caption']) ||
        'Gallery Image';

      const description =
        pick(it, ['description','desc','summary','details']) ||
        (it?.meta?.description ?? '') ||
        '';

      const tags = tagsFromItem(it);
      const tagsStr = tags.join('|');
      const full = normalizeUrl(img);

      const descHtml = description
        ? `<div class="gxa-meta__desc">${esc(description)}</div>`
        : `<div class="gxa-meta__desc" style="opacity:0;"></div>`;

      const tagsHtml = tags.length
        ? `<div class="gxa-meta__tags">${renderTagChips(tags, 3)}</div>`
        : `<div class="gxa-meta__tags" style="display:none;"></div>`;

      return `
        <div class="gxa-item"
             data-full="${esc(full)}"
             data-title="${esc(title)}"
             data-desc="${esc(description)}"
             data-tags="${esc(tagsStr)}"
             role="button"
             tabindex="0"
             aria-label="${esc(title)}">
          <img src="${esc(full)}" alt="${esc(title)}" loading="lazy">
          <div class="gxa-meta">
            <div class="gxa-meta__title">${esc(title)}</div>
            ${descHtml}
            ${tagsHtml}
          </div>
        </div>
      `;
    }).join('');

    // ✅ masonry after initial paint
    requestAnimationFrame(() => applyMasonry());

    // ✅ masonry again as images load (important for natural heights)
    const imgs = grid.querySelectorAll('img');
    imgs.forEach(img => {
      if (img.complete) return;
      img.addEventListener('load', () => applyMasonry(), { once: true });
      img.addEventListener('error', () => applyMasonry(), { once: true });
    });
  }

  function renderPager(){
    const pager = $('gxaPager');
    if (!pager) return;

    const last = state.lastPage || 1;
    const cur  = state.page || 1;

    if (last <= 1){
      pager.style.display = 'none';
      pager.innerHTML = '';
      return;
    }

    const btn = (label, page, {disabled=false, active=false}={}) => {
      const dis = disabled ? 'disabled' : '';
      const cls = active ? 'gxa-pagebtn active' : 'gxa-pagebtn';
      return `<button class="${cls}" ${dis} data-page="${page}">${label}</button>`;
    };

    let html = '';
    html += btn('Previous', Math.max(1, cur-1), { disabled: cur<=1 });

    const win = 2;
    const start = Math.max(1, cur - win);
    const end   = Math.min(last, cur + win);

    if (start > 1){
      html += btn('1', 1, { active: cur===1 });
      if (start > 2) html += `<span style="opacity:.6;padding:0 4px;">…</span>`;
    }

    for (let p=start; p<=end; p++){
      html += btn(String(p), p, { active: p===cur });
    }

    if (end < last){
      if (end < last - 1) html += `<span style="opacity:.6;padding:0 4px;">…</span>`;
      html += btn(String(last), last, { active: cur===last });
    }

    html += btn('Next', Math.min(last, cur+1), { disabled: cur>=last });

    pager.innerHTML = html;
    pager.style.display = 'flex';
  }

  function setLightboxMeta({title='', desc='', tags=[]}){
    const meta = $('gxaLbMeta');
    const t = $('gxaLbTitle');
    const d = $('gxaLbDesc');
    const tg = $('gxaLbTags');

    if (!meta || !t || !d || !tg) return;

    const hasTitle = (title || '').trim().length > 0;
    const hasDesc  = (desc || '').trim().length > 0;
    const hasTags  = Array.isArray(tags) && tags.length > 0;

    if (!hasTitle && !hasDesc && !hasTags){
      meta.style.display = 'none';
      t.textContent = '';
      d.textContent = '';
      tg.innerHTML = '';
      return;
    }

    meta.style.display = '';
    t.textContent = (title || '').trim();
    d.textContent = (desc || '').trim();

    if (hasTags){
      tg.innerHTML = tags.map(x => `<span class="gxa-lb__tag">${esc(x)}</span>`).join('');
      tg.style.display = 'flex';
    } else {
      tg.innerHTML = '';
      tg.style.display = 'none';
    }

    d.style.display = hasDesc ? '' : 'none';
  }

  async function load(){
    showSkeleton();

    try{
      const js = await fetchJson(buildUrl());

      const items = Array.isArray(js?.data) ? js.data : (Array.isArray(js) ? js : []);
      const p = js?.pagination || {};
      state.total = parseInt(p.total ?? items.length, 10) || items.length;
      state.lastPage = parseInt(p.last_page ?? 1, 10) || 1;
      state.page = parseInt(p.page ?? state.page, 10) || state.page;

      hideSkeleton();
      render(items);
      renderPager();

    }catch(e){
      hideSkeleton();
      const st = $('gxaState');
      const grid = $('gxaGrid');
      const pager = $('gxaPager');

      if (grid) grid.style.display = 'none';
      if (pager) pager.style.display = 'none';

      if (st){
        st.style.display = '';
        st.innerHTML = `
          <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
          </div>
          Could not load gallery.
          <div style="margin-top:8px;font-size:12.5px;opacity:.9;">
            API: <b>${esc(API)}</b>
          </div>
        `;
      }
    }
  }

  // Lightbox
  const lb = $('gxaLb');
  const lbImg = $('gxaLbImg');
  const lbClose = $('gxaLbClose');

  function openLB(src, meta){
    if (!lb || !lbImg) return;
    lbImg.src = src;
    setLightboxMeta(meta || {});
    lb.classList.add('show');
    lb.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeLB(){
    if (!lb || !lbImg) return;
    lb.classList.remove('show');
    lb.setAttribute('aria-hidden', 'true');
    lbImg.src = '';
    setLightboxMeta({ title:'', desc:'', tags:[] });
    document.body.style.overflow = '';
  }

  function parseTagsStr(s){
    const raw = (s || '').toString().trim();
    if (!raw) return [];
    return raw.split('|').map(x => (x || '').trim()).filter(Boolean);
  }

  document.addEventListener('DOMContentLoaded', () => {
    const search = $('gxaSearch');

    // search (debounced)
    let t = null;
    search && search.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(() => {
        state.q = (search.value || '').trim();
        state.page = 1;
        load();
      }, 260);
    });

    // pagination click
    document.addEventListener('click', (e) => {
      const b = e.target.closest('button.gxa-pagebtn[data-page]');
      if (!b) return;
      const p = parseInt(b.dataset.page, 10);
      if (!p || Number.isNaN(p) || p === state.page) return;
      state.page = p;
      load();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // open lightbox
    document.addEventListener('click', (e) => {
      const tile = e.target.closest('.gxa-item[data-full]');
      if (!tile) return;

      const src   = tile.getAttribute('data-full') || '';
      const title = tile.getAttribute('data-title') || '';
      const desc  = tile.getAttribute('data-desc') || '';
      const tags  = parseTagsStr(tile.getAttribute('data-tags') || '');

      if (src) openLB(src, { title, desc, tags });
    });

    // keyboard open / close
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeLB();

      const tile = e.target.closest?.('.gxa-item[data-full]');
      if (!tile) return;

      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();

        const src   = tile.getAttribute('data-full') || '';
        const title = tile.getAttribute('data-title') || '';
        const desc  = tile.getAttribute('data-desc') || '';
        const tags  = parseTagsStr(tile.getAttribute('data-tags') || '');

        if (src) openLB(src, { title, desc, tags });
      }
    });

    // close by clicking backdrop
    lb && lb.addEventListener('click', (e) => {
      if (e.target === lb) closeLB();
    });
    lbClose && lbClose.addEventListener('click', closeLB);

    // ✅ keep masonry responsive
    window.addEventListener('resize', () => {
      // small debounce
      clearTimeout(window.__gxaResizeT);
      window.__gxaResizeT = setTimeout(() => applyMasonry(), 80);
    });

    load();
  });
})();
</script>
