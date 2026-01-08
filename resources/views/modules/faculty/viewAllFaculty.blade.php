{{-- resources/views/landing/faculty-members.blade.php --}}
@include('landing.components.header')
@include('landing.components.headermenu')

<style>
  :root{
    --brand:#8f2f2f;
    --brand-2:#7a2626;
    --ink:#0f172a;
    --muted:#64748b;
    --bg:#f6f7fb;
    --card:#ffffff;
    --line: rgba(15,23,42,.10);
    --shadow: 0 10px 24px rgba(2,6,23,.08);
    --radius: 14px;
  }

  /* If your theme tokens exist, use them */
  :root{
    --brand: var(--primary-color, var(--brand));
    --brand-2: var(--secondary-color, var(--brand-2));
    --ink: var(--ink, var(--ink));
    --muted: var(--muted-color, var(--muted));
    --bg: var(--page-bg, var(--bg));
    --card: var(--surface, var(--card));
    --line: var(--line-soft, var(--line));
  }

  body{ background: var(--bg); }

  .fac-page{
    max-width: 1180px;
    margin: 18px auto 50px;
    padding: 0 12px;
  }

  .fac-header{
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: 18px;
    box-shadow: var(--shadow);
    padding: 16px 16px 14px;
    margin-bottom: 16px;
  }

  .fac-header-top{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap: 12px;
    flex-wrap: wrap;
  }

  .fac-title{
    margin:0;
    font-weight: 950;
    letter-spacing: .2px;
    color: var(--ink);
    font-size: 32px;
  }

  .fac-sub{
    margin: 6px 0 0;
    color: var(--muted);
    font-size: 14px;
  }

  .fac-tools{
    display:flex;
    align-items:center;
    gap: 10px;
    flex-wrap: wrap;
  }

  .fac-search{
    position: relative;
    min-width: 280px;
    max-width: 460px;
    flex: 1 1 280px;
  }
  .fac-search input{
    width:100%;
    border-radius: 14px;
    padding: 11px 12px 11px 42px;
    border: 1px solid var(--line);
    background: var(--card);
    color: var(--ink);
    outline: none;
  }
  .fac-search input:focus{
    border-color: rgba(201,75,80,.55);
    box-shadow: 0 0 0 4px rgba(201,75,80,.18);
  }
  .fac-search i{
    position:absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    opacity: .65;
    color: var(--muted);
  }

  .fac-chip{
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
  }

  /* List */
  .fac-list{
    display: grid;
    grid-template-columns: 1fr;
    gap: 18px;
  }

  /* Card (screenshot-like) */
  .fac-card{
    position: relative;
    background: #f8f9fa;
    border: 1px solid rgba(2,6,23,.08);
    border-radius: 14px;
    box-shadow: var(--shadow);
    padding: 18px;
    cursor: pointer;
    user-select: none;
    transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
    outline: none;
  }
  .fac-card:hover{
    transform: translateY(-2px);
    box-shadow: 0 18px 42px rgba(2,6,23,.12);
    border-color: rgba(158,54,58,.30);
  }
  .fac-card:focus-visible{
    box-shadow: 0 0 0 4px rgba(201,75,80,.22), 0 18px 42px rgba(2,6,23,.12);
    border-color: rgba(201,75,80,.55);
  }

  .fac-row{
    display:flex;
    gap: 16px;
    align-items:flex-start;
  }

  .fac-avatar{
    width: 76px;
    height: 76px;
    border-radius: 999px;
    flex: 0 0 76px;
    overflow: hidden;
    border: 3px solid #fff;
    box-shadow: 0 10px 22px rgba(2,6,23,.12);
    background: radial-gradient(140px 140px at 30% 20%,
      rgba(201,75,80,.16),
      transparent 60%),
      linear-gradient(180deg, rgba(0,0,0,.03), rgba(0,0,0,.06));
    position: relative;
    display:grid;
    place-items:center;
  }
  .fac-avatar img{
    width:100%;
    height:100%;
    object-fit: cover;
    display:block;
  }
  .fac-initial{
    position:absolute;
    inset:0;
    display:grid;
    place-items:center;
    font-weight: 950;
    color: rgba(158,54,58,.95);
    font-size: 20px;
    letter-spacing:.5px;
  }
  .fac-avatar.has-img .fac-initial{ opacity:0; pointer-events:none; }

  .fac-main{ flex: 1 1 auto; min-width: 0; }

  .fac-name{
       font-size: 1.3rem;
    font-weight: bold;
    color: #2c3e50;
    margin: 0;
    text-transform: uppercase;
}

  .fac-desig{
        font-size: 1rem;
    color: #34495e;
    margin: 0;
    font-weight: 500;
}



  .fac-meta{
    display: grid;
    grid-template-columns: 1fr;
    gap: 6px;
    margin-top: 6px;
  }
  .fac-meta .line{
       font-size: 1rem;
    color: #34495e;
    margin: 0;
    font-weight: 500;
}

  .fac-meta b{ font-weight: 950; }
  .fac-meta span{ color: #334155; }

  .fac-links{
    margin-top: 10px;
    display:flex;
    flex-wrap: wrap;
    gap: 10px 14px;
    align-items:center;
  }
  .fac-links a{
    color: #1d4ed8;
    text-decoration: none;
    font-weight: 800;
    font-size: 14px;
    word-break: break-word;
  }
  .fac-links a:hover{ text-decoration: underline; }
.fac-social{
  margin-top: 14px;
  display:flex;
  gap: 12px;
  flex-wrap: wrap;
  opacity: 1 !important;
}

.fac-social a{
  width: 44px;
  height: 44px;
  border-radius: 999px;
  display:flex;
  align-items:center;
  justify-content:center;

  background: #8f2f2f !important; /* exact like screenshot */
  color:#fff !important;
  text-decoration:none !important;

  border: 0;
  box-shadow: 0 10px 18px rgba(143,47,47,.22);
}

.fac-social a i{
  color:#fff !important;
  font-size: 16px;
  line-height: 1;
}

  .fac-social{
    margin-top: 14px;
    display:flex;
    gap: 10px;
    flex-wrap: wrap;
  }
  .fac-social a{
    width: 42px;
    height: 42px;
    border-radius: 999px;
    display:grid;
    place-items:center;
    background: var(--brand);
    color:#fff;
    border: 1px solid rgba(255,255,255,.18);
    box-shadow: 0 12px 22px rgba(143,47,47,.18);
    transition: transform .14s ease, filter .14s ease;
    text-decoration:none;
  }
  .fac-social a:hover{
    transform: translateY(-1px);
    filter: brightness(1.06);
  }

  /* Loading skeleton */
  .fac-skeleton{
    display:grid;
    gap: 18px;
  }
  .sk-card{
    border: 1px solid var(--line);
    border-radius: 14px;
    background: var(--card);
    box-shadow: var(--shadow);
    padding: 18px;
    position: relative;
    overflow: hidden;
  }
  .sk-card:before{
    content:'';
    position:absolute; inset:0;
    transform: translateX(-60%);
    background: linear-gradient(90deg, transparent, rgba(148,163,184,.22), transparent);
    animation: skMove 1.15s ease-in-out infinite;
  }
  @keyframes skMove{ to{ transform: translateX(60%); } }

  .sk-row{ display:flex; gap:16px; align-items:flex-start; }
  .sk-avatar{ width:76px; height:76px; border-radius:999px; background: rgba(148,163,184,.22); flex:0 0 76px; }
  .sk-lines{ flex:1; display:grid; gap:10px; }
  .sk-line{ height:14px; border-radius:10px; background: rgba(148,163,184,.22); width: 70%; }
  .sk-line.sm{ width: 48%; }
  .sk-line.xs{ width: 38%; }
  .sk-line.lg{ width: 86%; }

  /* Empty / error */
  .fac-state{
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: 16px;
    box-shadow: var(--shadow);
    padding: 18px;
    color: var(--muted);
    text-align:center;
  }

  /* Pagination */
  .fac-pagination{
    display:flex;
    justify-content:center;
    margin-top: 18px;
  }
  .fac-pagination .pager{
    display:flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items:center;
    justify-content:center;
    padding: 10px;
  }
  .fac-pagebtn{
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
  .fac-pagebtn:hover{ background: rgba(2,6,23,.03); }
  .fac-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
  .fac-pagebtn.active{
    background: rgba(201,75,80,.12);
    border-color: rgba(201,75,80,.35);
    color: var(--brand);
  }

  @media (max-width: 640px){
    .fac-title{ font-size: 26px; }
    .fac-row{ gap: 12px; }
    .fac-avatar{ width: 64px; height: 64px; flex-basis: 64px; }
    .sk-avatar{ width:64px; height:64px; flex-basis:64px; }
    .fac-name{ font-size: 18px; }
    .fac-desig{ font-size: 14px; }
  }
  /* Disabled social icons (still show same row) */
.fac-social .soc{
  width: 42px;
  height: 42px;
  border-radius: 999px;
  display:grid;
  place-items:center;
  background: var(--brand);
  color:#fff;
  border: 1px solid rgba(255,255,255,.18);
  box-shadow: 0 12px 22px rgba(143,47,47,.18);
  text-decoration:none;
  transition: transform .14s ease, filter .14s ease, opacity .14s ease;
}
.fac-social .soc:hover{ transform: translateY(-1px); filter: brightness(1.06); }

.fac-social .soc.is-disabled{
  opacity: .35;
  box-shadow: none;
  cursor: default;
  transform: none !important;
  filter: none !important;
}

</style>

<div class="fac-page"
     data-api="{{ url('/api/public/faculty') }}"
     data-profile-base="{{ url('/user/profile') }}/">

  {{-- Header --}}
  <div class="fac-header">
    <div class="fac-header-top">
      <div>
        <h1 class="fac-title">Faculty Members</h1>
        <div class="fac-sub" id="facSub">Click any faculty card to view the profile.</div>
      </div>

      <div class="fac-tools">
        <div class="fac-search">
          <i class="fa fa-magnifying-glass"></i>
          <input id="facSearch" type="search" placeholder="Search by name / specialization / qualification…">
        </div>
        <div class="fac-chip" title="Total results">
          <i class="fa-solid fa-users" style="opacity:.85"></i>
          <span id="facCount">—</span>
        </div>
      </div>
    </div>
  </div>

  {{-- List --}}
  <div id="facList" class="fac-list" style="display:none;"></div>

  {{-- Skeleton --}}
  <div id="facSkeleton" class="fac-skeleton"></div>

  {{-- Empty/Error --}}
  <div id="facState" class="fac-state" style="display:none;"></div>

  {{-- Pagination --}}
  <div class="fac-pagination">
    <div id="facPager" class="pager" style="display:none;"></div>
  </div>

</div>

{{-- FontAwesome (if not already in header) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

<script>
(() => {
  if (window.__PUBLIC_FACULTY_PAGE__) return;
  window.__PUBLIC_FACULTY_PAGE__ = true;

  const root = document.querySelector('.fac-page');
  if (!root) return;

  const $ = (id) => document.getElementById(id);

  const API_LIST = root.getAttribute('data-api') || '/api/public/faculty';
  const PROFILE_BASE = root.getAttribute('data-profile-base') || (window.location.origin + '/user/profile/');

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

  function initials(name){
    const n = (name || '').trim();
    if (!n) return 'FM';
    const parts = n.split(/\s+/).filter(Boolean).slice(0,2);
    return parts.map(p => p[0].toUpperCase()).join('');
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

  function decodeMaybeJson(v){
    if (v == null) return null;
    if (Array.isArray(v) || typeof v === 'object') return v;
    try { return JSON.parse(String(v)); } catch(e){ return null; }
  }

  function truncate(v, max=140){
    const s = (v ?? '').toString().trim();
    if (!s) return '';
    return s.length > max ? (s.slice(0, max).trim() + '…') : s;
  }

  function getProfileUrl(userUuid){
    if (!userUuid) return '#';
    return PROFILE_BASE + encodeURIComponent(userUuid);
  }

  function formatQualification(q){
    const arr = Array.isArray(q) ? q : (decodeMaybeJson(q) || null);
    if (!arr) return '';
    if (arr.every(x => typeof x === 'string')) return arr.join(', ');
    const bits = arr.map(x => x?.title || x?.degree || x?.name).filter(Boolean);
    return bits.length ? bits.join(', ') : '';
  }

  function metaLine(label, value){
    const v = (value || '').toString().trim();
    if (!v) return '';
    return `<div class="line"><b>${esc(label)}:</b> <span>${esc(v)}</span></div>`;
  }
function iconForPlatform(platform){
  const p = (platform || '').toLowerCase().trim();
  if (p.includes('linkedin')) return 'fa-brands fa-linkedin-in';
  if (p.includes('google') || p.includes('scholar')) return 'fa-solid fa-graduation-cap';
  if (p.includes('researchgate')) return 'fa-brands fa-researchgate';
  if (p === 'facebook' || p.includes('fb')) return 'fa-brands fa-facebook-f';
  if (p.includes('instagram') || p.includes('insta')) return 'fa-brands fa-instagram';
  if (p === 'x' || p.includes('twitter')) return 'fa-brands fa-x-twitter';
  if (p.includes('github')) return 'fa-brands fa-github';
  if (p.includes('youtube')) return 'fa-brands fa-youtube';
  return 'fa-solid fa-link';
}

function normalizeFaIcon(icon){
  const i = (icon || '').trim();
  if (!i) return '';
  // if they saved only "fa-linkedin-in"
  if (i.startsWith('fa-') && !i.includes('fa-solid') && !i.includes('fa-brands') && !i.includes('fa-regular')) {
    return 'fa-brands ' + i;
  }
  return i;
}

function buildSocialFromItem(item){
  const arr = Array.isArray(item?.socials) ? item.socials : [];
  const html = arr.map(s => {
    const url = (s?.url || '').toString().trim();
    if (!url) return '';

    const icon = normalizeFaIcon(s?.icon) || iconForPlatform(s?.platform);
    const title = (s?.platform || 'Link').toString();

    return `
      <a href="${esc(normalizeUrl(url))}" target="_blank" rel="noopener"
         title="${esc(title)}" data-stop-card="1">
        <i class="${esc(icon)}"></i>
      </a>
    `;
  }).join('');

  return html ? `<div class="fac-social">${html}</div>` : '';
}


  function showSkeleton(){
    const sk = $('facSkeleton');
    const st = $('facState');
    const list = $('facList');
    const pager = $('facPager');

    if (list) list.style.display = 'none';
    if (pager) pager.style.display = 'none';
    if (st) st.style.display = 'none';

    if (!sk) return;
    sk.style.display = '';
    sk.innerHTML = new Array(6).fill(0).map(() => `
      <div class="sk-card">
        <div class="sk-row">
          <div class="sk-avatar"></div>
          <div class="sk-lines">
            <div class="sk-line lg"></div>
            <div class="sk-line sm"></div>
            <div class="sk-line"></div>
            <div class="sk-line"></div>
            <div class="sk-line xs"></div>
          </div>
        </div>
      </div>
    `).join('');
  }

  function hideSkeleton(){
    const sk = $('facSkeleton');
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

  function buildListUrl(){
    const params = new URLSearchParams();
    params.set('page', String(state.page));
    params.set('per_page', String(state.perPage));
    params.set('status', 'active');
    if (state.q.trim()) params.set('q', state.q.trim());
    params.set('sort', 'created_at');
    params.set('direction', 'desc');
    return API_LIST + '?' + params.toString();
  }

  function render(items){
    const list = $('facList');
    const st = $('facState');
    const count = $('facCount');
    const sub = $('facSub');

    if (!list || !st) return;

    if (!items.length){
      list.style.display = 'none';
      st.style.display = '';
      st.innerHTML = `
        <div style="font-size:34px;opacity:.6;margin-bottom:6px;"><i class="fa-regular fa-face-frown"></i></div>
        No faculty found.
      `;
      if (count) count.textContent = '0';
      if (sub) sub.textContent = 'No records match your search.';
      return;
    }

    if (count) count.textContent = String(state.total || items.length);
    if (sub) sub.textContent = 'Click any faculty card to view the profile.';

    st.style.display = 'none';
    list.style.display = '';

    list.innerHTML = items.map(it => {
      const userUuid = pick(it, ['user_uuid','uuid']); // from your API normalizer
      const name = pick(it, ['name','user_name']) || 'Faculty';

      const desig =
        pick(it, ['designation']) ||
        (decodeMaybeJson(it?.metadata)?.designation || '') ||
        (decodeMaybeJson(it?.metadata)?.role_title || '') ||
        'Faculty Member';

      const qualification = formatQualification(it?.qualification);
      const specification = truncate(pick(it, ['specification']), 160);
      const experience    = truncate(pick(it, ['experience']), 160);

      // optional extra details (auto-hide if empty)
      const interest       = truncate(pick(it, ['interest']), 160);
      const administration = truncate(pick(it, ['administration']), 160);
      const research       = truncate(pick(it, ['research_project']), 160);

      // image
      const imgRaw = pick(it, ['image_full_url','image']);
      const img = normalizeUrl(imgRaw);

      // email/website can come from API OR metadata
      const meta = decodeMaybeJson(it?.metadata) || {};
      const email = pick(it, ['email']) || (meta.email || '');
      const website = pick(it, ['website']) || (meta.website || '');

      const href = getProfileUrl(userUuid);
      const ini = initials(name);

      return `
        <article class="fac-card" tabindex="0" role="link"
                 data-href="${esc(href)}"
                 aria-label="${esc(name)} profile">
          <div class="fac-row">
            <div class="fac-avatar">
              <div class="fac-initial">${esc(ini)}</div>
              ${img ? `
                <img src="${esc(img)}" alt="${esc(name)}"
                     onload="this.parentElement.classList.add('has-img')"
                     onerror="this.remove(); this.parentElement.classList.remove('has-img')">
              ` : ``}
            </div>

            <div class="fac-main">
              <h3 class="fac-name">${esc(name)}</h3>
              <div class="fac-desig">${esc(desig)}</div>

              <div class="fac-meta">
                ${metaLine('Qualification', qualification)}
                ${metaLine('Specification', specification)}
                ${metaLine('Experience', experience)}
                ${metaLine('Interest', interest)}
                ${metaLine('Administration', administration)}
                ${metaLine('Research Project', research)}
              </div>

              <div class="fac-links">
                ${email ? `<div><b>Email:</b> <a data-stop-card="1" href="mailto:${esc(email)}">${esc(email)}</a></div>` : ``}
                ${website ? `<div><b>Website:</b> <a data-stop-card="1" href="${esc(normalizeUrl(website))}" target="_blank" rel="noopener">${esc(website)}</a></div>` : ``}
              </div>

${buildSocialFromItem(it)}
            </div>
          </div>
        </article>
      `;
    }).join('');
  }

  function renderPager(){
    const pager = $('facPager');
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
      const cls = active ? 'fac-pagebtn active' : 'fac-pagebtn';
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

  async function load(){
    showSkeleton();

    try{
      const js = await fetchJson(buildListUrl());
      const items = Array.isArray(js?.data) ? js.data : [];
      const p = js?.pagination || {};
      state.total = parseInt(p.total ?? items.length, 10) || items.length;
      state.lastPage = parseInt(p.last_page ?? 1, 10) || 1;
      state.page = parseInt(p.page ?? state.page, 10) || state.page;

      hideSkeleton();
      render(items);
      renderPager();

    }catch(e){
      hideSkeleton();
      const st = $('facState');
      const list = $('facList');
      const pager = $('facPager');

      if (list) list.style.display = 'none';
      if (pager) pager.style.display = 'none';

      if (st){
        st.style.display = '';
        st.innerHTML = `
          <div style="font-size:34px;opacity:.6;margin-bottom:6px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
          </div>
          Could not load faculty.
          <div style="margin-top:8px;font-size:12.5px;opacity:.9;">
            API: <b>${esc(API_LIST)}</b>
          </div>
        `;
      }
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    const search = $('facSearch');

    // Search (debounced)
    let t = null;
    search && search.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(() => {
        state.q = (search.value || '').trim();
        state.page = 1;
        load();
      }, 260);
    });

    // Pagination click
    document.addEventListener('click', (e) => {
      const b = e.target.closest('button.fac-pagebtn[data-page]');
      if (!b) return;
      const p = parseInt(b.dataset.page, 10);
      if (!p || Number.isNaN(p) || p === state.page) return;
      state.page = p;
      load();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Card click -> profile (robust)
    document.addEventListener('click', (e) => {
      if (e.target.closest('[data-stop-card="1"]')) return;

      const card = e.target.closest('.fac-card[data-href]');
      if (!card) return;

      const href = card.getAttribute('data-href') || '#';
      if (!href || href === '#') return;

      window.location.href = href;
    });

    // Keyboard open (Enter/Space)
    document.addEventListener('keydown', (e) => {
      const card = e.target.closest?.('.fac-card[data-href]');
      if (!card) return;

      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        const href = card.getAttribute('data-href') || '#';
        if (href && href !== '#') window.location.href = href;
      }
    });

    load();
  });

})();
</script>
