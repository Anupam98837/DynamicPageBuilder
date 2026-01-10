{{-- resources/views/landing/placement-officers.blade.php --}}

{{-- (optional) FontAwesome for icons used below; remove if already included in header --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

<style>
/* =========================================================
  Public Placement Officers (Cards)
  - Card size: 247px (W) x 329px (H)
  - Optional Department filter from URL:
      /{department-slug}/placement-officers/  => filtered (client-side hint)
      /placement-officers/ => all
========================================================= */

:root{
  --po-card-w: 247px;
  --po-card-h: 329px;
  --po-radius: 18px;

  /* fallbacks if theme vars missing */
  --po-ink: var(--ink, #111827);
  --po-muted: var(--muted-color, #64748b);
  --po-surface: var(--surface, #ffffff);
  --po-line: var(--line-strong, rgba(15,23,42,.14));
  --po-line-soft: var(--line-soft, rgba(15,23,42,.10));
  --po-hover: var(--page-hover, rgba(2,6,23,.04));
  --po-primary: var(--primary-color, #9E363A);
  --po-shadow: var(--shadow-2, 0 10px 24px rgba(0,0,0,.08));
  --po-accent: var(--primary-color, #9E363A);
}

.po-page{
  max-width: 1180px;
  margin: 18px auto 44px;
  padding: 0 10px;
}

/* Header */
.po-head{
  display:flex;
  align-items:flex-end;
  justify-content:space-between;
  gap:12px;
  flex-wrap:wrap;
  margin-bottom: 14px;
}
.po-title{
  margin:0;
  font-weight: 900;
  letter-spacing: .2px;
  color: var(--po-ink);
  font-size: 22px;
}
.po-sub{
  margin: 4px 0 0;
  color: var(--po-muted);
  font-size: 13px;
}
.po-right{
  display:flex;
  align-items:center;
  gap:10px;
  flex-wrap:wrap;
}
.po-search{
  position:relative;
  min-width: 280px;
  max-width: 420px;
  flex: 1 1 280px;
}
.po-search input{
  width:100%;
  border-radius: 14px;
  padding: 11px 12px 11px 42px;
  border:1px solid var(--po-line);
  background: var(--po-surface);
  color: var(--po-ink);
  outline: none;
}
.po-search input:focus{
  border-color: color-mix(in oklab, var(--po-primary) 40%, var(--po-line));
  box-shadow: 0 0 0 4px color-mix(in oklab, var(--po-primary) 20%, transparent);
}
.po-search i{
  position:absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  opacity: .6;
  color: var(--po-muted);
}
.po-chip{
  border:1px solid var(--po-line);
  background: var(--po-surface);
  border-radius: 999px;
  padding: 10px 12px;
  color: var(--po-ink);
  font-size: 13px;
  display:flex;
  align-items:center;
  gap:8px;
  box-shadow: var(--po-shadow);
}
.po-chip b{font-weight:900}

/* Grid (centered fixed card width) */
.po-grid{
  display:grid;
  grid-template-columns: repeat(3, var(--po-card-w));
  gap: 18px;
}
/* @media (max-width: 980px){
  .po-grid{ grid-template-columns: repeat(2, var(--po-card-w)); }
} */
@media (max-width: 560px){
  .po-grid{ grid-template-columns: 1fr; justify-content: stretch; }
  .po-card{ width: 100%; max-width: var(--po-card-w); margin: 0 auto; }
}

/* Card */
.po-card{
  position: relative;
  width: var(--po-card-w);
  height: var(--po-card-h);
  border-radius: var(--po-radius);
  overflow:hidden;
  display:block;
  text-decoration:none !important;
  color: inherit;
  background: color-mix(in oklab, var(--po-surface) 86%, transparent);
  border: 1px solid color-mix(in oklab, var(--po-line) 80%, transparent);
  box-shadow: 0 12px 26px rgba(0,0,0,.10);
  transform: translateZ(0);
  transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
  cursor:pointer;
}
.po-card:hover{
  transform: translateY(-4px);
  box-shadow: 0 18px 42px rgba(0,0,0,.16);
  border-color: color-mix(in oklab, var(--po-primary) 28%, var(--po-line));
}

/* image as bg */
.po-card .bg{
  position:absolute; inset:0;
  background-size: cover;
  background-position: center;
  filter: saturate(1.02);
  transform: scale(1.0001);
}

/* slight vignette like reference */
.po-card .vignette{
  position:absolute; inset:0;
  background:
    radial-gradient(1200px 500px at 50% -20%, rgba(255,255,255,.10), rgba(0,0,0,0) 60%),
    linear-gradient(180deg, rgba(0,0,0,.00) 28%, rgba(0,0,0,.12) 60%, rgba(0,0,0,.54) 100%);
}

/* bottom overlay text */
.po-card .info{
  position:absolute;
  left: 14px;
  right: 14px;
  bottom: 14px;
  z-index: 2;
}
.po-name{
  margin:0;
  font-size: 18px;
  font-weight: 900;
  line-height: 1.1;
  color: #fff;
  text-shadow: 0 6px 16px rgba(0,0,0,.35);
}
.po-line2{
  margin: 6px 0 0;
  font-size: 14px;
  color: rgba(255,255,255,.86);
  text-shadow: 0 6px 16px rgba(0,0,0,.35);
  overflow:hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* top “pill” */
.po-pill{
  position:absolute;
  top: 12px;
  left: 12px;
  z-index: 2;
  padding: 7px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: .2px;
  color: #fff;
  background: rgba(0,0,0,.28);
  border: 1px solid rgba(255,255,255,.20);
  backdrop-filter: blur(6px);
  max-width: calc(100% - 24px);
  overflow:hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Placeholder (no image) */
.po-placeholder{
  position:absolute; inset:0;
  display:grid; place-items:center;
  background:
    radial-gradient(800px 360px at 20% 10%, color-mix(in oklab, var(--po-primary) 25%, transparent), transparent 60%),
    radial-gradient(900px 400px at 80% 90%, color-mix(in oklab, var(--po-primary) 18%, transparent), transparent 60%),
    linear-gradient(180deg, color-mix(in oklab, var(--po-surface) 92%, transparent), color-mix(in oklab, var(--po-surface) 82%, transparent));
}
.po-initials{
  width: 86px; height: 86px;
  border-radius: 24px;
  display:grid; place-items:center;
  font-weight: 900;
  font-size: 28px;
  color: var(--po-primary);
  background: color-mix(in oklab, var(--po-primary) 14%, transparent);
  border: 1px solid color-mix(in oklab, var(--po-primary) 30%, var(--po-line-soft));
}

/* Loading / Empty */
.po-state{
  border:1px solid var(--po-line);
  border-radius: 16px;
  background: var(--po-surface);
  box-shadow: var(--po-shadow);
  padding: 18px;
  color: var(--po-muted);
  text-align:center;
}
.po-spinner{
  width: 42px; height: 42px;
  border-radius: 50%;
  border: 4px solid rgba(148,163,184,.30);
  border-top: 4px solid var(--po-primary);
  margin: 0 auto 10px;
  animation: poSpin 1s linear infinite;
}
@keyframes poSpin{to{transform:rotate(360deg)}}

/* Pagination (bottom middle) */
.po-pagination{
  display:flex;
  justify-content:center;
  margin-top: 18px;
}
.po-pagination .pager{
  display:flex;
  gap: 8px;
  flex-wrap:wrap;
  align-items:center;
  justify-content:center;
  padding: 10px;
}
.po-pagebtn{
  border:1px solid var(--po-line);
  background: var(--po-surface);
  color: var(--po-ink);
  border-radius: 12px;
  padding: 9px 12px;
  font-size: 13px;
  font-weight: 900;
  box-shadow: var(--po-shadow);
  cursor:pointer;
  user-select:none;
}
.po-pagebtn:hover{ background: var(--po-hover); }
.po-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
.po-pagebtn.active{
  background: color-mix(in oklab, var(--po-primary) 14%, transparent);
  border-color: color-mix(in oklab, var(--po-primary) 35%, var(--po-line));
  color: var(--po-primary);
}

    .po-title i {
color: var(--po-accent);
}
</style>

<div class="po-page">

  <div class="po-head">
    <div>
      <h1 class="po-title" id="poTitle"><i class="fa-solid fa-bullhorn"></i> Placement Officers</h1>
      <div class="po-sub" id="poSub">Meet our Placement & Training team.</div>
    </div>

    <div class="po-right">
      <div class="po-search">
        <i class="fa fa-magnifying-glass"></i>
        <input id="poSearch" type="search" placeholder="Search by name / email / designation…">
      </div>

      <div class="po-chip" title="Department filter (optional)">
        <i class="fa-solid fa-filter" style="opacity:.75"></i>
        <span id="poDeptChip"><b>All</b></span>
      </div>

      <div class="po-chip" title="Total results">
        <i class="fa-solid fa-users" style="opacity:.75"></i>
        <span id="poCount">—</span>
      </div>
    </div>
  </div>

  <div id="poGrid" class="po-grid" style="display:none;"></div>

  <div id="poState" class="po-state">
    <div class="po-spinner"></div>
    Loading placement officers…
  </div>

  <div class="po-pagination">
    <div id="poPager" class="pager" style="display:none;"></div>
  </div>

</div>

<script>
(() => {
  if (window.__PLACEMENT_OFFICERS_PUBLIC_INIT__) return;
  window.__PLACEMENT_OFFICERS_PUBLIC_INIT__ = true;

  const $ = (id) => document.getElementById(id);

  // ✅ Always use Laravel app base URL (local => http://127.0.0.1:8000)
  const APP_ORIGIN = @json(url('/'));

  // profile route base
  const PROFILE_PATH = '/user/profile/';

  // Public endpoints (absolute -> always hits :8000)
  const LIST_ENDPOINTS = [
    APP_ORIGIN + '/api/public/placement-officers',  // ✅ your new api
    APP_ORIGIN + '/api/placement-officers'          // (optional fallback if you keep another route)
  ];

  const state = {
    page: 1,
    perPage: 12,
    lastPage: 1,
    total: 0,
    q: '',
    departmentSlug: ''
  };

  function esc(str){
    return (str ?? '').toString().replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  function normalizeUrl(url){
    const u = (url || '').toString().trim();
    if (!u) return '';
    if (/^(data:|blob:|https?:\/\/)/i.test(u)) return u;
    if (u.startsWith('/')) return APP_ORIGIN + u;
    return APP_ORIGIN + '/' + u;
  }

  function pick(obj, keys){
    for (const k of keys){
      const v = obj?.[k];
      if (v !== null && v !== undefined && String(v).trim() !== '') return v;
    }
    return '';
  }

  function looksLikeUuid(v){
    return /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(String(v || '').trim());
  }

  // Optional dept filter from URL:
  // /{dept}/placement-officers/
  function readDepartmentFromUrl(){
    const parts = window.location.pathname.split('/').filter(Boolean);
    const idx = parts.findIndex(p => p.toLowerCase() === 'placement-officers');
    if (idx > 0) return parts[idx - 1];
    return '';
  }

  function titleCaseFromSlug(slug){
    if (!slug) return '';
    return slug
      .replace(/[-_]+/g,' ')
      .split(' ')
      .filter(Boolean)
      .map(w => w.charAt(0).toUpperCase() + w.slice(1))
      .join(' ');
  }

  function initials(name){
    const n = (name || '').trim();
    if (!n) return 'PO';
    const parts = n.split(/\s+/).filter(Boolean).slice(0,2);
    return parts.map(p => p[0].toUpperCase()).join('');
  }

  function resolveName(item){
    return String(pick(item, ['name','user_name','full_name']) || 'Placement Officer');
  }

  function resolveEmail(item){
    return String(pick(item, ['email']) || '');
  }

  function resolveDesignation(item){
    // your API outputs "designation" from affiliation
    return String(pick(item, ['designation','affiliation','role_short_form','role']) || '');
  }

  function resolveImage(item){
    const img =
      pick(item, ['image_full_url','image_url','photo_url','profile_image_url']) ||
      pick(item, ['image']) || '';
    return normalizeUrl(img);
  }

  function resolveProfileIdentifier(item){
    const candidates = [
      pick(item, ['uuid','user_uuid']),
      (item?.id ?? '')
    ].map(v => String(v ?? '').trim()).filter(Boolean);

    const uuid = candidates.find(looksLikeUuid);
    return uuid || candidates[0] || '';
  }

  function buildProfileUrl(identifier){
    return identifier
      ? (APP_ORIGIN + PROFILE_PATH + encodeURIComponent(identifier))
      : '#';
  }

  function toItems(js){
    if (Array.isArray(js?.data)) return js.data;
    if (Array.isArray(js?.items)) return js.items;
    if (Array.isArray(js)) return js;
    if (Array.isArray(js?.data?.items)) return js.data.items;
    return [];
  }

  function toPagination(js){
    const p = js?.pagination || js?.meta || js?.data?.pagination || {};
    const total = parseInt(p.total ?? js?.total ?? 0, 10) || 0;
    const last  = parseInt(p.last_page ?? p.lastPage ?? js?.last_page ?? 1, 10) || 1;
    const cur   = parseInt(p.current_page ?? p.page ?? js?.page ?? 1, 10) || 1;
    return { total, last, cur };
  }

  async function fetchJson(url){
    const res = await fetch(url, { headers: { 'Accept':'application/json' }});
    const js = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(js?.message || 'Request failed');
    return js;
  }

  async function tryFetchList(urls){
    let lastErr = null;
    for (const u of urls){
      try{
        const js = await fetchJson(u);
        return { ok:true, used:u, js, items: toItems(js) };
      }catch(e){
        lastErr = e;
      }
    }
    return { ok:false, used:'', js:{}, items:[], error:lastErr };
  }

  function buildListUrl(base){
    const params = new URLSearchParams();
    params.set('per_page', String(state.perPage));
    params.set('page', String(state.page));
    params.set('status', 'active');

    if (state.q.trim()) params.set('q', state.q.trim());

    // (optional) if you later add backend dept filter
    if (state.departmentSlug) params.set('department', state.departmentSlug);

    params.set('sort', 'created_at');
    params.set('direction', 'desc');

    return base + (base.includes('?') ? '&' : '?') + params.toString();
  }

  // Optional client-side dept filter (non-breaking)
  function applyClientDeptFilter(items){
    if (!state.departmentSlug) return items;
    const slug = state.departmentSlug.toLowerCase();
    return (items || []).filter(it => {
      const d = (resolveDesignation(it) || '').toLowerCase();
      return d.includes(slug.replace(/[-_]+/g,' ')) || d.includes(slug);
    });
  }

  function render(items){
    const grid = $('poGrid');
    const st = $('poState');
    const count = $('poCount');
    const sub = $('poSub');

    if (!grid || !st) return;

    if (!items.length){
      grid.style.display = 'none';
      st.style.display = '';
      st.innerHTML = `
        <div style="font-size:34px;opacity:.6;margin-bottom:6px;"><i class="fa-regular fa-face-frown"></i></div>
        No placement officers found.
      `;
      if (count) count.textContent = '0';
      if (sub) sub.textContent = state.departmentSlug
        ? 'No records found for this department.'
        : 'No records match your search.';
      return;
    }

    if (count) count.textContent = String(state.total || items.length);
    if (sub) sub.textContent = 'Click any card to view the profile.';

    st.style.display = 'none';
    grid.style.display = '';
    grid.innerHTML = items.map(it => {
      const name = resolveName(it);
      const email = resolveEmail(it);
      const desig = resolveDesignation(it);
      const img = resolveImage(it);

      const identifier = resolveProfileIdentifier(it);
      const href = buildProfileUrl(identifier);

      const pill = desig ? `<div class="po-pill">${esc(desig)}</div>` : `<div class="po-pill">Placement Officer</div>`;
      const line2 = email ? esc(email) : 'Placement Cell';

      if (!img){
        return `
          <a class="po-card" href="${esc(href)}" data-profile="${esc(href)}" aria-label="${esc(name)} profile">
            <div class="po-placeholder">
              <div class="po-initials">${esc(initials(name))}</div>
            </div>
            ${pill}
            <div class="vignette"></div>
            <div class="info">
              <p class="po-name">${esc(name)}</p>
              <p class="po-line2">${line2}</p>
            </div>
          </a>
        `;
      }

      return `
        <a class="po-card" href="${esc(href)}" data-profile="${esc(href)}" aria-label="${esc(name)} profile">
          <div class="bg" style="background-image:url('${esc(img)}')"></div>
          ${pill}
          <div class="vignette"></div>
          <div class="info">
            <p class="po-name">${esc(name)}</p>
            <p class="po-line2">${line2}</p>
          </div>
        </a>
      `;
    }).join('');
  }

  function renderPager(){
    const pager = $('poPager');
    if (!pager) return;

    const last = state.lastPage || 1;
    const cur = state.page || 1;

    if (last <= 1){
      pager.style.display = 'none';
      pager.innerHTML = '';
      return;
    }

    const btn = (label, page, {disabled=false, active=false}={}) => {
      const dis = disabled ? 'disabled' : '';
      const cls = active ? 'po-pagebtn active' : 'po-pagebtn';
      return `<button class="${cls}" ${dis} data-page="${page}">${label}</button>`;
    };

    let html = '';
    html += btn('Previous', Math.max(1, cur-1), { disabled: cur<=1 });

    const win = 2;
    const start = Math.max(1, cur - win);
    const end = Math.min(last, cur + win);

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
    const st = $('poState');
    const grid = $('poGrid');

    if (st){
      st.style.display = '';
      st.innerHTML = `<div class="po-spinner"></div>Loading placement officers…`;
    }
    if (grid) grid.style.display = 'none';

    const urls = LIST_ENDPOINTS.map(base => buildListUrl(base));
    const res = await tryFetchList(urls);

    if (!res.ok){
      if (st){
        st.style.display = '';
        st.innerHTML = `
          <div style="font-size:34px;opacity:.6;margin-bottom:6px;"><i class="fa-solid fa-triangle-exclamation"></i></div>
          Could not load placement officers.
        `;
      }
      const pager = $('poPager');
      if (pager) pager.style.display = 'none';
      return;
    }

    let items = res.items || [];

    // Optional dept filter client-side (safe even if not needed)
    items = applyClientDeptFilter(items);

    const p = toPagination(res.js);
    state.total = p.total || items.length;
    state.lastPage = p.last || 1;
    state.page = p.cur || state.page;

    render(items);
    renderPager();
  }

  document.addEventListener('DOMContentLoaded', () => {
    // dept from URL (optional)
    state.departmentSlug = readDepartmentFromUrl();

    const deptChip = $('poDeptChip');
    const title = $('poTitle');
    if (deptChip){
      deptChip.innerHTML = state.departmentSlug
        ? `<b>${esc(titleCaseFromSlug(state.departmentSlug))}</b>`
        : `<b>All</b>`;
    }
    if (title && state.departmentSlug){
      title.textContent = `Placement Officers — ${titleCaseFromSlug(state.departmentSlug)}`;
    }

    // ✅ Force navigation on card click (in case any global script blocks anchors)
    document.addEventListener('click', (e) => {
      const card = e.target.closest('.po-card');
      if (!card) return;
      const url = card.getAttribute('data-profile') || card.getAttribute('href') || '';
      if (!url || url === '#') return;
      e.preventDefault();
      window.location.href = url;
    });

    // search (debounced)
    const search = $('poSearch');
    let t = null;
    if (search){
      search.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(() => {
          state.q = (search.value || '').trim();
          state.page = 1;
          load();
        }, 260);
      });
    }

    // pager click
    document.addEventListener('click', (e) => {
      const b = e.target.closest('button.po-pagebtn[data-page]');
      if (!b) return;
      const p = parseInt(b.dataset.page, 10);
      if (!p || Number.isNaN(p) || p === state.page) return;
      state.page = p;
      load();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    load();
  });

})();
</script>
