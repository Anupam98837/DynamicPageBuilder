{{-- resources/views/landing/gallery-all.blade.php --}}
@include('landing.components.header')
@include('landing.components.headermenu')

<style>
  :root{
    --brand:#8f2f2f;
    --ink:#0f172a;
    --muted:#64748b;
    --bg:#ffffff;
    --card:#ffffff;
    --line: rgba(15,23,42,.10);
    --shadow: 0 10px 24px rgba(2,6,23,.08);
  }

  :root{
    --brand: var(--primary-color, var(--brand));
    --bg: var(--page-bg, var(--bg));
    --card: var(--surface, var(--card));
    --line: var(--line-soft, var(--line));
  }

  body{ background: var(--bg); }

  .gal-wrap{
    max-width: 1180px;
    margin: 18px auto 54px;
    padding: 0 12px;
  }

  .gal-head{
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: 16px;
    box-shadow: var(--shadow);
    padding: 14px 16px;
    margin-bottom: 16px;
    display:flex;
    gap: 12px;
    align-items: flex-end;
    justify-content: space-between;
    flex-wrap: wrap;
  }

  .gal-title{
    margin: 0;
    font-weight: 950;
    letter-spacing: .2px;
    color: var(--ink);
    font-size: 28px;
  }
  .gal-sub{
    margin: 6px 0 0;
    color: var(--muted);
    font-size: 14px;
  }

  .gal-tools{
    display:flex;
    gap: 10px;
    align-items:center;
    flex-wrap: wrap;
  }

  .gal-search{
    position: relative;
    min-width: 260px;
    max-width: 420px;
    flex: 1 1 260px;
  }
  .gal-search i{
    position:absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    opacity: .65;
    color: var(--muted);
  }
  .gal-search input{
    width:100%;
    border-radius: 14px;
    padding: 11px 12px 11px 42px;
    border: 1px solid var(--line);
    background: var(--card);
    color: var(--ink);
    outline: none;
  }
  .gal-search input:focus{
    border-color: rgba(201,75,80,.55);
    box-shadow: 0 0 0 4px rgba(201,75,80,.18);
  }

  .gal-chip{
    display:flex;
    align-items:center;
    gap: 8px;
    padding: 10px 12px;
    border-radius: 999px;
    border: 1px solid var(--line);
    background: var(--card);
    box-shadow: 0 8px 18px rgba(2,6,23,.06);
    color: var(--ink);
    font-size: 13px;
    font-weight: 900;
    white-space: nowrap;
  }

  /* ====== Grid like your screenshot ====== */
  .gal-grid{
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(247px, 1fr));
    gap: 26px;
    align-items: start;
  }

  /* fixed tile size requested */
  .gal-item{
    width: 247px;
    height: 329px;
    background: #fff;
    border: 1px solid rgba(2,6,23,.08);
    border-radius: 0;                /* screenshot is squared */
    box-shadow: 0 10px 24px rgba(2,6,23,.08);
    overflow: hidden;
    cursor: pointer;
    user-select: none;
    justify-self: center;            /* center inside column */
    transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
    position: relative;
  }
  .gal-item:hover{
    transform: translateY(-2px);
    box-shadow: 0 16px 34px rgba(2,6,23,.12);
    border-color: rgba(158,54,58,.22);
  }

  .gal-item img{
    width: 100%;
    height: 100%;
    object-fit: cover;
    display:block;
  }

  /* ====== Title/Description/Tags overlay (always visible) ====== */
  .gal-meta{
    position:absolute;
    left:0; right:0; bottom:0;
    padding: 10px 10px 9px;
    color: #fff;
    background: linear-gradient(180deg, rgba(2,6,23,0) 0%, rgba(2,6,23,.55) 28%, rgba(2,6,23,.82) 100%);
    pointer-events: none; /* keeps tile click working */
  }
  .gm-title{
    font-weight: 950;
    font-size: 13px;
    letter-spacing: .2px;
    line-height: 1.18;
    text-shadow: 0 2px 10px rgba(0,0,0,.35);
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }
  .gm-desc{
    margin-top: 4px;
    font-size: 12px;
    opacity: .92;
    line-height: 1.25;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-shadow: 0 2px 10px rgba(0,0,0,.35);
    min-height: calc(12px * 1.25 * 2); /* keeps consistent height */
  }
  .gm-tags{
    margin-top: 6px;
    display:flex;
    gap: 6px;
    flex-wrap: wrap;
  }
  .gm-tag{
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
  .gm-tag.more{
    background: rgba(201,75,80,.22);
    border-color: rgba(201,75,80,.35);
  }

  /* Loading / empty */
  .gal-state{
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: 16px;
    box-shadow: var(--shadow);
    padding: 18px;
    color: var(--muted);
    text-align:center;
  }

  /* Skeleton */
  .gal-skeleton{
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(247px, 1fr));
    gap: 26px;
  }
  .sk-tile{
    width: 247px;
    height: 329px;
    justify-self:center;
    border: 1px solid var(--line);
    background: #fff;
    overflow:hidden;
    position:relative;
    box-shadow: 0 10px 24px rgba(2,6,23,.08);
  }
  .sk-tile:before{
    content:'';
    position:absolute; inset:0;
    transform: translateX(-60%);
    background: linear-gradient(90deg, transparent, rgba(148,163,184,.22), transparent);
    animation: skMove 1.15s ease-in-out infinite;
  }
  @keyframes skMove{ to{ transform: translateX(60%);} }

  /* Pagination (bottom center) */
  .gal-pagination{
    display:flex;
    justify-content:center;
    margin-top: 18px;
  }
  .gal-pagination .pager{
    display:flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items:center;
    justify-content:center;
    padding: 10px;
  }
  .gal-pagebtn{
    border:1px solid var(--line);
    background: var(--card);
    color: var(--ink);
    border-radius: 12px;
    padding: 9px 12px;
    font-size: 13px;
    font-weight: 950;
    box-shadow: 0 8px 18px rgba(2,6,23,.06);
    cursor:pointer;
    user-select:none;
  }
  .gal-pagebtn:hover{ background: rgba(2,6,23,.03); }
  .gal-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
  .gal-pagebtn.active{
    background: rgba(201,75,80,.12);
    border-color: rgba(201,75,80,.35);
    color: var(--brand);
  }

  /* Lightbox */
  .lb{
    position: fixed;
    inset: 0;
    background: rgba(2,6,23,.72);
    display:none;
    align-items:center;
    justify-content:center;
    z-index: 9999;
    padding: 18px;
  }
  .lb.show{ display:flex; }

  .lb-inner{
    max-width: min(1100px, 96vw);
    max-height: min(86vh, 900px);
    background: #0b1220;
    border: 1px solid rgba(255,255,255,.12);
    box-shadow: 0 22px 60px rgba(0,0,0,.45);
    position: relative;
    display:flex;
    flex-direction: column;
    overflow:hidden;
  }
  .lb-img{
    max-width: min(1100px, 96vw);
    max-height: min(72vh, 820px);
    display:block;
    object-fit: contain;
  }

  .lb-meta{
    border-top: 1px solid rgba(255,255,255,.10);
    padding: 12px 14px 14px;
    color: rgba(255,255,255,.92);
    background: rgba(255,255,255,.02);
  }
  .lb-title{
    font-weight: 950;
    font-size: 15px;
    letter-spacing: .2px;
    color:#fff;
    margin: 0 0 6px;
  }
  .lb-desc{
    margin: 0 0 10px;
    font-size: 13px;
    line-height: 1.35;
    color: rgba(255,255,255,.86);
    white-space: pre-wrap;
  }
  .lb-tags{
    display:flex;
    gap: 8px;
    flex-wrap: wrap;
  }
  .lb-tag{
    font-size: 12px;
    font-weight: 900;
    padding: 7px 10px;
    border-radius: 999px;
    background: rgba(255,255,255,.10);
    border: 1px solid rgba(255,255,255,.14);
  }

  .lb-close{
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
  .lb-close:hover{ background: rgba(0,0,0,.55); }

  @media (max-width: 640px){
    .gal-title{ font-size: 24px; }
    .gal-item, .sk-tile{ width: 247px; height: 329px; } /* keep fixed as requested */
    .lb-img{ max-height: min(66vh, 760px); }
  }
</style>

<div class="gal-wrap"
     data-api="{{ url('/api/public/gallery') }}">

  <div class="gal-head">
    <div>
      <h1 class="gal-title">Gallery</h1>
      <div class="gal-sub" id="galSub">View all photos</div>
    </div>

    <div class="gal-tools">
      <div class="gal-search">
        <i class="fa fa-magnifying-glass"></i>
        <input id="galSearch" type="search" placeholder="Search by caption / tag / title…">
      </div>
      <div class="gal-chip" title="Total results">
        <i class="fa-solid fa-images" style="opacity:.85"></i>
        <span id="galCount">—</span>
      </div>
    </div>
  </div>

  <div id="galGrid" class="gal-grid" style="display:none;"></div>

  <div id="galSkeleton" class="gal-skeleton"></div>
  <div id="galState" class="gal-state" style="display:none;"></div>

  <div class="gal-pagination">
    <div id="galPager" class="pager" style="display:none;"></div>
  </div>
</div>

{{-- Lightbox --}}
<div id="lb" class="lb" aria-hidden="true">
  <div class="lb-inner">
    <button class="lb-close" id="lbClose" aria-label="Close">
      <i class="fa-solid fa-xmark"></i>
    </button>

    <img id="lbImg" class="lb-img" alt="Gallery image">

    {{-- Meta --}}
    <div class="lb-meta" id="lbMeta" style="display:none;">
      <div class="lb-title" id="lbTitle"></div>
      <div class="lb-desc" id="lbDesc"></div>
      <div class="lb-tags" id="lbTags"></div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

<script>
(() => {
  if (window.__PUBLIC_GALLERY_ALL__) return;
  window.__PUBLIC_GALLERY_ALL__ = true;

  const root = document.querySelector('.gal-wrap');
  if (!root) return;

  const API = root.getAttribute('data-api') || '/api/public/gallery';

  const $ = (id) => document.getElementById(id);

  const state = {
    page: 1,
    perPage: 12,
    lastPage: 1,
    total: 0,
    q: ''
  };

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
    // raw can be array or string
    let arr = [];
    if (Array.isArray(raw)){
      arr = raw.map(x => (x ?? '').toString().trim()).filter(Boolean);
    } else {
      const s = (raw ?? '').toString().trim();
      if (s){
        // supports: "tag1, tag2" or "tag1|tag2" or "#tag1 #tag2"
        if (s.includes('|')) arr = s.split('|');
        else if (s.includes(',')) arr = s.split(',');
        else arr = s.split(/\s+/);
        arr = arr.map(x => x.replace(/^#+/,'').trim()).filter(Boolean);
      }
    }
    // unique
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

    let html = shown.map(x => `<span class="gm-tag">${esc(x)}</span>`).join('');
    if (more > 0) html += `<span class="gm-tag more">+${more}</span>`;
    return html;
  }

  function showSkeleton(){
    const sk = $('galSkeleton');
    const st = $('galState');
    const grid = $('galGrid');
    const pager = $('galPager');

    if (grid) grid.style.display = 'none';
    if (pager) pager.style.display = 'none';
    if (st) st.style.display = 'none';

    if (!sk) return;
    sk.style.display = '';
    sk.innerHTML = new Array(9).fill(0).map(() => `<div class="sk-tile"></div>`).join('');
  }

  function hideSkeleton(){
    const sk = $('galSkeleton');
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

    // if your API supports these, keep; otherwise harmless
    params.set('sort', 'created_at');
    params.set('direction', 'desc');

    return API + '?' + params.toString();
  }

  function render(items){
    const grid = $('galGrid');
    const st = $('galState');
    const count = $('galCount');

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
        ''; // keep optional

      const tags = tagsFromItem(it);
      const tagsStr = tags.join('|');

      const full = normalizeUrl(img);

      // If no desc, don't reserve 2-line space with dummy text (we still keep min-height via CSS)
      const descHtml = description ? `<div class="gm-desc">${esc(description)}</div>` : `<div class="gm-desc" style="opacity:.0"></div>`;
      const tagsHtml = tags.length ? `<div class="gm-tags">${renderTagChips(tags, 3)}</div>` : `<div class="gm-tags" style="display:none;"></div>`;

      return `
        <div class="gal-item"
             data-full="${esc(full)}"
             data-title="${esc(title)}"
             data-desc="${esc(description)}"
             data-tags="${esc(tagsStr)}"
             role="button"
             tabindex="0"
             aria-label="${esc(title)}">
          <img src="${esc(full)}" alt="${esc(title)}" loading="lazy">
          <div class="gal-meta">
            <div class="gm-title">${esc(title)}</div>
            ${descHtml}
            ${tagsHtml}
          </div>
        </div>
      `;
    }).join('');
  }

  function renderPager(){
    const pager = $('galPager');
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
      const cls = active ? 'gal-pagebtn active' : 'gal-pagebtn';
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
    const meta = $('lbMeta');
    const t = $('lbTitle');
    const d = $('lbDesc');
    const tg = $('lbTags');

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
      tg.innerHTML = tags.map(x => `<span class="lb-tag">${esc(x)}</span>`).join('');
      tg.style.display = 'flex';
    } else {
      tg.innerHTML = '';
      tg.style.display = 'none';
    }

    // If desc empty, remove extra gap
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
      const st = $('galState');
      const grid = $('galGrid');
      const pager = $('galPager');

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
  const lb = $('lb');
  const lbImg = $('lbImg');
  const lbClose = $('lbClose');

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
    const search = $('galSearch');

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
      const b = e.target.closest('button.gal-pagebtn[data-page]');
      if (!b) return;
      const p = parseInt(b.dataset.page, 10);
      if (!p || Number.isNaN(p) || p === state.page) return;
      state.page = p;
      load();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // open lightbox
    document.addEventListener('click', (e) => {
      const tile = e.target.closest('.gal-item[data-full]');
      if (!tile) return;

      const src  = tile.getAttribute('data-full') || '';
      const title = tile.getAttribute('data-title') || '';
      const desc  = tile.getAttribute('data-desc') || '';
      const tags  = parseTagsStr(tile.getAttribute('data-tags') || '');

      if (src) openLB(src, { title, desc, tags });
    });

    // keyboard open / close
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeLB();

      const tile = e.target.closest?.('.gal-item[data-full]');
      if (!tile) return;

      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();

        const src  = tile.getAttribute('data-full') || '';
        const title = tile.getAttribute('data-title') || '';
        const desc  = tile.getAttribute('data-desc') || '';
        const tags  = parseTagsStr(tile.getAttribute('data-tags') || '');

        if (src) openLB(src, { title, desc, tags });
      }
    });

    lb && lb.addEventListener('click', (e) => {
      if (e.target === lb) closeLB();
    });
    lbClose && lbClose.addEventListener('click', closeLB);

    load();
  });
})();
</script>
