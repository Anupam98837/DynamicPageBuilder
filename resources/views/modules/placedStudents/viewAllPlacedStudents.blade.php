{{-- resources/views/landing/placed-students.blade.php --}}

{{-- (optional) FontAwesome for icons used below; remove if already included in header --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

<style>
/* =========================================================
  Public Placed Students (Cards)
  - Card size: 247px (W) x 329px (H)
  - Department filter from URL:
      /{department-slug}/alumni/  => filtered
      /alumni/ (or no dept segment) => all
========================================================= */

:root{
  --psp-card-w: 247px;
  --psp-card-h: 329px;
  --psp-radius: 18px;

  /* fallbacks if theme vars missing */
  --psp-ink: var(--ink, #111827);
  --psp-muted: var(--muted-color, #64748b);
  --psp-surface: var(--surface, #ffffff);
  --psp-line: var(--line-strong, rgba(15,23,42,.14));
  --psp-line-soft: var(--line-soft, rgba(15,23,42,.10));
  --psp-hover: var(--page-hover, rgba(2,6,23,.04));
  --psp-primary: var(--primary-color, #9E363A);
  --psp-shadow: var(--shadow-2, 0 10px 24px rgba(0,0,0,.08));
  --psp-accent: var(--primary-color, #9E363A);
}

.psp-page{
  max-width: 1180px;
  margin: 18px auto 44px;
  padding: 0 10px;
}

/* Header */
.psp-head{
  display:flex;
  align-items:flex-end;
  justify-content:space-between;
  gap:12px;
  flex-wrap:wrap;
  margin-bottom: 14px;
}
.psp-title{
  margin:0;
  font-weight: 900;
  letter-spacing: .2px;
  color: var(--psp-ink);
  font-size: 22px;
}
.psp-sub{
  margin: 4px 0 0;
  color: var(--psp-muted);
  font-size: 13px;
}
.psp-right{
  display:flex;
  align-items:center;
  gap:10px;
  flex-wrap:wrap;
}
.psp-search{
  position:relative;
  min-width: 280px;
  max-width: 420px;
  flex: 1 1 280px;
}
.psp-search input{
  width:100%;
  border-radius: 14px;
  padding: 11px 12px 11px 42px;
  border:1px solid var(--psp-line);
  background: var(--psp-surface);
  color: var(--psp-ink);
  outline: none;
}
.psp-search input:focus{
  border-color: color-mix(in oklab, var(--psp-primary) 40%, var(--psp-line));
  box-shadow: 0 0 0 4px color-mix(in oklab, var(--psp-primary) 20%, transparent);
}
.psp-search i{
  position:absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  opacity: .6;
  color: var(--psp-muted);
}
.psp-chip{
  border:1px solid var(--psp-line);
  background: var(--psp-surface);
  border-radius: 999px;
  padding: 10px 12px;
  color: var(--psp-ink);
  font-size: 13px;
  display:flex;
  align-items:center;
  gap:8px;
  box-shadow: var(--psp-shadow);
}
.psp-chip b{font-weight:900}

/* Grid (centered fixed card width) */
.psp-grid{
  display:grid;
  grid-template-columns: repeat(3, var(--psp-card-w));
  gap: 18px;
}
/* @media (max-width: 980px){
  .psp-grid{ grid-template-columns: repeat(2, var(--psp-card-w)); }
} */
@media (max-width: 560px){
  .psp-grid{ grid-template-columns: 1fr; justify-content: stretch; }
  .psp-card{ width: 100%; max-width: var(--psp-card-w); margin: 0 auto; }
}

/* Card */
.psp-card{
  position: relative;
  width: var(--psp-card-w);
  height: var(--psp-card-h);
  border-radius: var(--psp-radius);
  overflow:hidden;
  display:block;
  text-decoration:none !important;
  color: inherit;
  background: color-mix(in oklab, var(--psp-surface) 86%, transparent);
  border: 1px solid color-mix(in oklab, var(--psp-line) 80%, transparent);
  box-shadow: 0 12px 26px rgba(0,0,0,.10);
  transform: translateZ(0);
  transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
  cursor:pointer;
}
.psp-card:hover{
  transform: translateY(-4px);
  box-shadow: 0 18px 42px rgba(0,0,0,.16);
  border-color: color-mix(in oklab, var(--psp-primary) 28%, var(--psp-line));
}

/* image as bg */
.psp-card .bg{
  position:absolute; inset:0;
  background-size: cover;
  background-position: center;
  filter: saturate(1.02);
  transform: scale(1.0001);
}

/* slight vignette like reference */
.psp-card .vignette{
  position:absolute; inset:0;
  background:
    radial-gradient(1200px 500px at 50% -20%, rgba(255,255,255,.10), rgba(0,0,0,0) 60%),
    linear-gradient(180deg, rgba(0,0,0,.00) 28%, rgba(0,0,0,.12) 60%, rgba(0,0,0,.54) 100%);
}

/* bottom overlay text */
.psp-card .info{
  position:absolute;
  left: 14px;
  right: 14px;
  bottom: 14px;
  z-index: 2;
}
.psp-name{
  margin:0;
  font-size: 18px;
  font-weight: 900;
  line-height: 1.1;
  color: #fff;
  text-shadow: 0 6px 16px rgba(0,0,0,.35);
}
.psp-pass{
  margin: 6px 0 0;
  font-size: 14px;
  color: rgba(255,255,255,.86);
  text-shadow: 0 6px 16px rgba(0,0,0,.35);
}

/* top “pill” */
.psp-pill{
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
}

/* Placeholder (no image) */
.psp-placeholder{
  position:absolute; inset:0;
  display:grid; place-items:center;
  background:
    radial-gradient(800px 360px at 20% 10%, color-mix(in oklab, var(--psp-primary) 25%, transparent), transparent 60%),
    radial-gradient(900px 400px at 80% 90%, color-mix(in oklab, var(--psp-primary) 18%, transparent), transparent 60%),
    linear-gradient(180deg, color-mix(in oklab, var(--psp-surface) 92%, transparent), color-mix(in oklab, var(--psp-surface) 82%, transparent));
}
.psp-initials{
  width: 86px; height: 86px;
  border-radius: 24px;
  display:grid; place-items:center;
  font-weight: 900;
  font-size: 28px;
  color: var(--psp-primary);
  background: color-mix(in oklab, var(--psp-primary) 14%, transparent);
  border: 1px solid color-mix(in oklab, var(--psp-primary) 30%, var(--psp-line-soft));
}

/* Loading / Empty */
.psp-state{
  border:1px solid var(--psp-line);
  border-radius: 16px;
  background: var(--psp-surface);
  box-shadow: var(--psp-shadow);
  padding: 18px;
  color: var(--psp-muted);
  text-align:center;
}
.psp-spinner{
  width: 42px; height: 42px;
  border-radius: 50%;
  border: 4px solid rgba(148,163,184,.30);
  border-top: 4px solid var(--psp-primary);
  margin: 0 auto 10px;
  animation: pspSpin 1s linear infinite;
}
@keyframes pspSpin{to{transform:rotate(360deg)}}

/* Pagination (bottom middle) */
.psp-pagination{
  display:flex;
  justify-content:center;
  margin-top: 18px;
}
.psp-pagination .pager{
  display:flex;
  gap: 8px;
  flex-wrap:wrap;
  align-items:center;
  justify-content:center;
  padding: 10px;
}
.psp-pagebtn{
  border:1px solid var(--psp-line);
  background: var(--psp-surface);
  color: var(--psp-ink);
  border-radius: 12px;
  padding: 9px 12px;
  font-size: 13px;
  font-weight: 900;
  box-shadow: var(--psp-shadow);
  cursor:pointer;
  user-select:none;
}
.psp-pagebtn:hover{ background: var(--psp-hover); }
.psp-pagebtn[disabled]{ opacity:.55; cursor:not-allowed; }
.psp-pagebtn.active{
  background: color-mix(in oklab, var(--psp-primary) 14%, transparent);
  border-color: color-mix(in oklab, var(--psp-primary) 35%, var(--psp-line));
  color: var(--psp-primary);
}

    .psp-title i {
color: var(--psp-accent);
}
</style>

<div class="psp-page">

  <div class="psp-head">
    <div>
      <h1 class="psp-title" id="pspTitle"><i class="fa-solid fa-bullhorn"></i> Placed Students</h1>
      <div class="psp-sub" id="pspSub">Explore our recent placements.</div>
    </div>

    <div class="psp-right">
      <div class="psp-search">
        <i class="fa fa-magnifying-glass"></i>
        <input id="pspSearch" type="search" placeholder="Search by name / department / year…">
      </div>

      <div class="psp-chip" title="Department filter">
        <i class="fa-solid fa-filter" style="opacity:.75"></i>
        <span id="pspDeptChip"><b>All</b></span>
      </div>

      <div class="psp-chip" title="Total results">
        <i class="fa-solid fa-users" style="opacity:.75"></i>
        <span id="pspCount">—</span>
      </div>
    </div>
  </div>

  <div id="pspGrid" class="psp-grid" style="display:none;"></div>

  <div id="pspState" class="psp-state">
    <div class="psp-spinner"></div>
    Loading placed students…
  </div>

  <div class="psp-pagination">
    <div id="pspPager" class="pager" style="display:none;"></div>
  </div>

</div>
<script>
(() => {
  if (window.__PLACED_STUDENTS_PUBLIC_INIT__) return;
  window.__PLACED_STUDENTS_PUBLIC_INIT__ = true;

  const $ = (id) => document.getElementById(id);

  // ✅ Always use Laravel app base URL (local => http://127.0.0.1:8000)
  const APP_ORIGIN = @json(url('/'));

  // profile route base
  const PROFILE_PATH = '/user/profile/';

  // Public endpoints (absolute -> always hits :8000)
  const LIST_ENDPOINTS = [
    APP_ORIGIN + '/api/placed-students/public',
    APP_ORIGIN + '/api/placed-students'
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

  // URL department filter:
  // https://msit.edu.in/business-administration/alumni/
  function readDepartmentFromUrl(){
    const parts = window.location.pathname.split('/').filter(Boolean);
    const idx = parts.findIndex(p => p.toLowerCase() === 'alumni');
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
    if (!n) return 'PS';
    const parts = n.split(/\s+/).filter(Boolean).slice(0,2);
    return parts.map(p => p[0].toUpperCase()).join('');
  }

  function resolveName(item){
    return String(
      pick(item, ['user_name','student_name','name']) ||
      pick(item?.user, ['name','full_name','username']) ||
      'Student'
    );
  }

  function resolveYear(item){
    const y =
      pick(item, ['passout_year','pass_out_year','passing_year','graduation_year','year']) ||
      pick(item?.user, ['passout_year','passing_year','graduation_year','year']) || '';
    if (y) return String(y);

    const od = pick(item, ['offer_date','placed_at','created_at']) || '';
    const m = String(od).match(/^(\d{4})/);
    return m ? m[1] : '';
  }

  function resolveDepartmentName(item){
    return String(
      pick(item, ['department_name','department_title']) ||
      pick(item?.department, ['name','title']) ||
      ''
    );
  }

  function resolveImage(item){
    const img =
      pick(item, ['image_url','photo_url','profile_image_url']) ||
      pick(item, ['user_image_url','user_image']) ||
      pick(item?.user, ['image_url','photo_url','image']) ||
      pick(item, ['photo','image']) || '';
    return normalizeUrl(img);
  }

  // ✅ MOST IMPORTANT FIX:
  // Build identifier for /user/profile/{identifier}
  // Priority: user_uuid / user.uuid => uuid-looking => item.uuid => user_id
  function resolveProfileIdentifier(item){
    const candidates = [
      pick(item, ['user_uuid','userUuid','profile_uuid','profileUuid','user_profile_uuid','userProfileUuid']),
      pick(item?.user, ['uuid','user_uuid']),
      pick(item, ['uuid']),
      (item?.user_id ?? ''),
      (item?.user?.id ?? '')
    ]
      .map(v => String(v ?? '').trim())
      .filter(Boolean);

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

    // dept filter from URL
    if (state.departmentSlug) params.set('department', state.departmentSlug);

    params.set('sort', 'created_at');
    params.set('direction', 'desc');

    return base + (base.includes('?') ? '&' : '?') + params.toString();
  }

  function render(items){
    const grid = $('pspGrid');
    const st = $('pspState');
    const count = $('pspCount');
    const sub = $('pspSub');

    if (!grid || !st) return;

    if (!items.length){
      grid.style.display = 'none';
      st.style.display = '';
      st.innerHTML = `
        <div style="font-size:34px;opacity:.6;margin-bottom:6px;"><i class="fa-regular fa-face-frown"></i></div>
        No placed students found.
      `;
      if (count) count.textContent = '0';
      if (sub) sub.textContent = state.departmentSlug
        ? 'No records found for this department.'
        : 'No records match your search.';
      return;
    }

    if (count) count.textContent = String(state.total || items.length);
    if (sub) sub.textContent = 'Click any card to view the student profile.';

    st.style.display = 'none';
    grid.style.display = '';
    grid.innerHTML = items.map(it => {
      const name = resolveName(it);
      const yr = resolveYear(it);
      const deptName = resolveDepartmentName(it);
      const img = resolveImage(it);

      const identifier = resolveProfileIdentifier(it);
      const href = buildProfileUrl(identifier);

      const passLine = yr ? `${esc(yr)} Pass Out` : (deptName ? esc(deptName) : 'Placed Student');
      const pill = deptName ? `<div class="psp-pill">${esc(deptName)}</div>` : '';

      if (!img){
        return `
          <a class="psp-card" href="${esc(href)}" data-profile="${esc(href)}" aria-label="${esc(name)} profile">
            <div class="psp-placeholder">
              <div class="psp-initials">${esc(initials(name))}</div>
            </div>
            ${pill}
            <div class="vignette"></div>
            <div class="info">
              <p class="psp-name">${esc(name)}</p>
              <p class="psp-pass">${passLine}</p>
            </div>
          </a>
        `;
      }

      return `
        <a class="psp-card" href="${esc(href)}" data-profile="${esc(href)}" aria-label="${esc(name)} profile">
          <div class="bg" style="background-image:url('${esc(img)}')"></div>
          ${pill}
          <div class="vignette"></div>
          <div class="info">
            <p class="psp-name">${esc(name)}</p>
            <p class="psp-pass">${passLine}</p>
          </div>
        </a>
      `;
    }).join('');
  }

  function renderPager(){
    const pager = $('pspPager');
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
      const cls = active ? 'psp-pagebtn active' : 'psp-pagebtn';
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
    const st = $('pspState');
    const grid = $('pspGrid');

    if (st){
      st.style.display = '';
      st.innerHTML = `<div class="psp-spinner"></div>Loading placed students…`;
    }
    if (grid) grid.style.display = 'none';

    const urls = LIST_ENDPOINTS.map(base => buildListUrl(base));
    const res = await tryFetchList(urls);

    if (!res.ok){
      if (st){
        st.style.display = '';
        st.innerHTML = `
          <div style="font-size:34px;opacity:.6;margin-bottom:6px;"><i class="fa-solid fa-triangle-exclamation"></i></div>
          Could not load placed students.
        `;
      }
      const pager = $('pspPager');
      if (pager) pager.style.display = 'none';
      return;
    }

    const items = res.items || [];
    const p = toPagination(res.js);
    state.total = p.total || items.length;
    state.lastPage = p.last || 1;
    state.page = p.cur || state.page;

    render(items);
    renderPager();
  }

  document.addEventListener('DOMContentLoaded', () => {
    // dept from URL
    state.departmentSlug = readDepartmentFromUrl();

    const deptChip = $('pspDeptChip');
    const title = $('pspTitle');
    if (deptChip){
      deptChip.innerHTML = state.departmentSlug
        ? `<b>${esc(titleCaseFromSlug(state.departmentSlug))}</b>`
        : `<b>All</b>`;
    }
    if (title && state.departmentSlug){
      title.textContent = `Placed Students — ${titleCaseFromSlug(state.departmentSlug)}`;
    }

    // ✅ Force navigation on card click (in case any global script blocks anchors)
    document.addEventListener('click', (e) => {
      const card = e.target.closest('.psp-card');
      if (!card) return;
      const url = card.getAttribute('data-profile') || card.getAttribute('href') || '';
      if (!url || url === '#') return;
      e.preventDefault();
      window.location.href = url;
    });

    // search (debounced)
    const search = $('pspSearch');
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
      const b = e.target.closest('button.psp-pagebtn[data-page]');
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
